<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Services\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AuthRequest\LoginRequest;

class AuthController extends Controller
{
    protected $authService;

    /**
     * Constructor to inject the AuthService.
     *
     * @param \App\Http\Services\AuthService $authService Auth service to handle authentication logic
     */
    public function __construct(AuthService $authService){
        // Inject AuthService
        $this->authService = $authService;
        // Middleware to protect the logout and profile routes, ensuring only authenticated users can access them
        $this->middleware('auth')->only('logout','profile','refresh');
    }
    /**
     * Login the user with provided credentials.
     *
     * @param LoginRequest $request A request containing the login credentials
     * @return \Illuminate\Http\JsonResponse Returns the JWT token or an error response if credentials are invalid
     */

    public function login(LoginRequest $request)
    {
        // Extract credentials from the request
        $credentials = $request->only(['email', 'password']);

        // Attempt to log the user in via the AuthService
        $data = $this->authService->attemptLogin($credentials);

        // If login fails, return an error response
        if (!$data) {
            return parent::errorResponse('Invalid email or password', 401);
        }

        // Wrap the user with UserResource if login succeeds
        $data['user'] = new UserResource($data['user']);

        // Return success response with the token and user details
        return parent::successResponse('Login Successful', $data, 'User logged in successfully', 200);
    }
    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request A request instance containing the authenticated user
     * @return \Illuminate\Http\JsonResponse Returns the authenticated user's profile data
     */
    public function profile(Request $request)
    {
        // Fetch the authenticated user from the request
        $user = Auth::user();

        // Return the user's profile as JSON
        return parent::successResponse('User',  new UserResource($user), "User Info Retrived Successfully", 200);
    }

    /**
     * Logout the user by invalidating their token.
     *
     * @return \Illuminate\Http\JsonResponse Returns a success message after logout
     */
    public function logout(Request $request)
    {
        // Invalidate the user's token via the AuthService
        $user = $this->authService->logout($request);
        // Return a success message
        return parent::successResponse('User', $user, "Successfully logged out", 200);
    }

    public function refresh()
    {
        return parent::successResponse('Token', Auth::refresh(), "Token refreshed successfully", 200);
    }
}
