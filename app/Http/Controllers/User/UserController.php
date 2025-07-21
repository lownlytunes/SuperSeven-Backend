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
        $validated = $request->validated();

        $user = auth()->user();

        $user->update([
            'first_name' => $validated['first_name'],
            'mid_name' => $validated['mid_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'contact_num' => $validated['contact_no'],
            'address' => $validated['address'],
        ]);

        return $this->sendResponse('User updated successfully.', new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
       $validated = $request->validated();

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->sendResponse('Password updated successfully.', new UserResource($user));
    }
}
