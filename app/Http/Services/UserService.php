<?php

namespace App\Http\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class UserService
{
    /**
     * Retrieve paginated list of users.
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function listUser()
    {
        return User::paginate();
    }

    /**
     * Create a new user with the provided inputs.
     *
     * @param array $fieldInputs
     * @return \App\Models\User
     * @throws Exception
     */
    public function createUser(array $fieldInputs)
    {
        try {
            $user = User::create([
                'name' => $fieldInputs["name"],
                'email' => $fieldInputs["email"],
                'password' => bcrypt($fieldInputs['password']),
                'role_id' => $fieldInputs["role_id"] ?? 2,
            ]);
            return $user;
        } catch (Exception $e) {
            if ($e->getCode() === '23000') {
                throw new Exception('This email is already taken.');
            }
            Log::error('Error Creating User' . $e->getMessage());
            throw new Exception('There is something wrong with the server');
        }
    }

    /**
     * Retrieve the specified user.
     *
     * @param User $User
     * @return User
     * @throws Exception
     */
    public function getUser(User $User)
    {
        try {
            return $User;
        } catch (Exception $e) {
            Log::error('Error retrieving User: ' . $e->getMessage());
            throw new Exception('Error retrieving User.');
        }
    }

    /**
     * Update the specified user's details.
     *
     * @param array $fieldInputs
     * @param User $User
     * @return User
     * @throws Exception
     */
    public function updateUser(array $fieldInputs, $User)
    {
        try {
            $User->update(array_filter($fieldInputs));
            return $User;
        } catch (Exception $e) {
            Log::error('Error updating User: ' . $e->getMessage());
            throw new Exception('Error Updating User.');
        }
    }

    /**
     * Delete the specified user.
     *
     * @param User $User
     * @return void
     * @throws Exception
     */
    public function deleteUser($User)
    {
        try {
            $User->delete();
        } catch (Exception $e) {
            Log::error('Error deleting User: ' . $e->getMessage());
            throw new Exception('Error deleting User.');
        }
    }
}
