<?php

namespace App\Http\Services;

use Exception;
use App\Models\Attachment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AssetsService
{
    protected $apiKey;

    /**
     * Constructor to initialize the API key from the environment variable.
     *
     * This method sets up the API key required to interact with the VirusTotal API.
     */
    public function __construct()
    {
        $this->apiKey = env('VIRUSTOTAL_API_KEY'); // Retrieve the VirusTotal API key from .env file
    }

    /**
     * Scans a file using the VirusTotal API.
     *
     * This method uploads a file to the VirusTotal API for scanning and then checks the scan status.
     * Once the scan is completed, it retrieves the scan results.
     *
     * @param string $filePath The path to the file to be scanned.
     * @return array The scan result data returned by VirusTotal.
     * @throws Exception If the file cannot be scanned or an error occurs during the scan.
     */
    public function scanFile($filePath)
    {
        $url = 'https://www.virustotal.com/api/v3/files';

        // Upload the file to VirusTotal for scanning
        $response = Http::withHeaders([
            'x-apikey' => $this->apiKey, // Add the API key to the request headers
        ])->attach('file', fopen($filePath, 'r'), basename($filePath))->post($url);

        // Check if the file was uploaded successfully
        if ($response->successful()) {
            // Extract the analysis ID from the response to use for polling the scan result
            $analysisId = $response->json()['data']['id'];
            return $this->pollScanResult($analysisId); // Poll the scan result using the analysis ID
        } else {
            // Log the error details if the file upload failed
            Log::error('VirusTotal API error:', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            throw new Exception('Failed to scan file: ' . $response->body(), $response->status());
        }
    }

    /**
     * Polls the scan result from the VirusTotal API until the scan is completed.
     *
     * This method repeatedly checks the status of the file scan using the analysis ID,
     * waiting up to a maximum number of attempts (10) before throwing an exception.
     *
     * @param string $analysisId The analysis ID returned after uploading the file for scanning.
     * @return array The scan result data returned by VirusTotal.
     * @throws Exception If the scan could not complete within the maximum attempts or if there was an issue with the scan.
     */
    public function pollScanResult($analysisId)
    {
        $url = "https://www.virustotal.com/api/v3/analyses/{$analysisId}";
        $maxAttempts = 10;
        $attempt = 0;

        // Poll every 10 seconds for the scan result until it is completed or the maximum attempts are reached
        do {
            sleep(10); // Wait 10 seconds before re-checking

            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey, // Add the API key to the request headers
            ])->get($url);

            $scanResult = $response->json();

            // Check if the scan has been completed
            if (isset($scanResult['data']['attributes']['status']) && $scanResult['data']['attributes']['status'] === 'completed') {
                return $scanResult; // Return the completed scan result
            }

            $attempt++; // Increment the attempt counter
        } while ($attempt < $maxAttempts); // Retry until maximum attempts are reached

        throw new Exception('Scan timeout or failed to complete after polling.');
    }

    /**
     * Stores the attachment after scanning the file for viruses.
     *
     * This method first scans the file for viruses using VirusTotal, and if the file is clean,
     * it validates and stores the file in the local storage. Then, it creates an entry in the database.
     *
     * @param mixed $file The file to be uploaded.
     * @param string $attachableType The type of the entity that the attachment is related to.
     * @param int $attachableId The ID of the entity that the attachment is related to.
     * @param int $user_id The ID of the user uploading the file.
     * @return array An array containing the stored attachment object and a message.
     * @throws Exception If the file contains a virus or fails to meet validation requirements.
     * @throws FileException If the file is of an unsupported type.
     */
    public function storeAttachment($file, $attachableType, $attachableId, $user_id)
    {
        $message = '';

        // Scan the file to check for any viruses using VirusTotal
        $scanResult = $this->scanFile($file->getPathname());

        // Check if the scan result indicates any malicious content
        if (isset($scanResult['data']['attributes']['stats'])) {
            $maliciousCount = $scanResult['data']['attributes']['stats']['malicious'] ?? 0;
            if ($maliciousCount > 0) {
                throw new Exception('File contains a virus!', 400); // Throw an exception if the file is malicious
            } else {
                $message = 'Scan completed successfully, no virus found :)'; // Message if the file is clean
            }
        }

        // Validate the file for any potentially dangerous characters or invalid types
        $originalName = $file->getClientOriginalName();
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        // Prevent directory traversal or invalid file names
        if (!$extension || strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
            throw new Exception(trans('general.notAllowedAction'), 403); // Reject invalid file names
        }

        // Allowed MIME types for file uploads
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $mime_type = $file->getClientMimeType();
        if (!in_array($mime_type, $allowedMimeTypes)) {
            throw new FileException(trans('general.invalidFileType'), 403); // Reject unsupported file types
        }

        // Generate a random file name and store the file locally
        $fileName = Str::random(32);
        $filePath = "attachments/{$fileName}.{$extension}";
        $fileContent = file_get_contents($file);

        if ($fileContent === false || !Storage::disk('local')->put($filePath, $fileContent)) {
            throw new Exception(trans('general.failedToStoreFile'), 500); // Handle file storage failure
        }

        // Create an entry for the attachment in the database
        $attachment = Attachment::create([
            'name' => $originalName,
            'path' => $filePath,
            // 'mime_type' => $mime_type,
            'attachable_id' => $attachableId,
            'attachable_type' => $attachableType,
            'user_id' => $user_id,
        ]);

        // Return the attachment object and the result message
        return ['attachment' => $attachment, 'message' => $message];
    }
}
