<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    /**
     * Update the specified resource in storage.
     *
     */
    public function updateCurrent(UpdateUserRequest $request)
    {
        $user = auth()->user();
        $user->update($request->validated());
        return $this->sendResponse('User updated successfully.', new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $request->validated();

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->sendResponse('Password updated successfully.', new UserResource($user));
    }
}
