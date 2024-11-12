<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $UserService;

    /**
     * UserController constructor.
     * Initializes the UserService dependency.
     *
     * @param UserService $UserService
     */
    public function __construct(UserService $UserService)
    {
        $this->UserService = $UserService;
    }

    /**
     * Display a listing of the users.
     * Calls the listUser method from UserService to get paginated users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = $this->UserService->listUser();
        if ($users->isEmpty()) {
            return parent::errorResponse("No User Found", 404);
        }
        return parent::successResponse(
            'users',
            UserResource::collection($users)->response()->getData(true), // response with metadata
            "Users retrieved successfully",
            200
        );
    }

    /**
     * Store a newly created user in storage.
     * Calls the createUser method in UserService with validated data.
     *
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $fieldInputs = $request->validated();
            $User = $this->UserService->createUser($fieldInputs);
            return parent::successResponse('User', new UserResource($User), "User Created Successfully", 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified user.
     *
     * @param User $User
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $User)
    {
        return parent::successResponse('user', new UserResource($User), "User retrieved successfully", 200);
    }

    /**
     * Update the specified user in storage.
     * Calls the updateUser method in UserService with validated data.
     *
     * @param UpdateUserRequest $request
     * @param User $User
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, User $User)
    {
        $fieldInputs = $request->validated();
        $User = $this->UserService->updateUser($fieldInputs, $User);
        return parent::successResponse('user', new UserResource($User), "User Updated Successfully", 200);
    }

    /**
     * Remove the specified user from storage.
     * Calls the deleteUser method in UserService.
     *
     * @param User $User
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $User)
    {
        $this->UserService->deleteUser($User);
        return parent::successResponse('user', null, "User Deleted Successfully", 200);
    }
}
