<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Task;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Services\AssetsService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttachmentRequest\AddAttachmentRequest;

class AttachmentController extends Controller
{
    protected $assetsService;

    public function __construct(AssetsService $assetsService)
    {
        $this->assetsService = $assetsService;
    }
    /**
     * upload attachment related to task 
     * @param \App\Http\Requests\AttachmentRequest\AddAttachmentRequest $request
     * @param mixed $task_id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function upload(AddAttachmentRequest $request, $task_id)
    {
        $request->validated();
        $file = $request->file('attachment');
        $attachableType = 'App\\Models\\Task';
        $attachableId = $task_id;
        $user_id = Auth::user()->id;

        try {
            $fileAttaschment = $this->assetsService->storeAttachment($file, $attachableType, $attachableId, $user_id);
            $task = Task::find($task_id);
            $task = $task->load('attachments');
            return parent::successResponse('Task', new TaskResource($task), 'File added successfully' . $fileAttaschment['message'], 201);
        } catch (Exception $e) {
        $statusCode = is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600 ? $e->getCode() : 500;
        return response()->json(['error' => $e->getMessage()], $statusCode);
    }
    }

}
