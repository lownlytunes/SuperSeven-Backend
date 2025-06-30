<?php

namespace App\Http\Controllers\User\Employee;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AddUserFormRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\Collections\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Employee;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeAccountController extends BaseController
{
    public function getEmployees(PaginateRequest $request)
    {
        $employees = User::has('employee')->when(isset($request->search), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $this->searchCallback($query, $request, ['first_name', 'mid_name', 'last_name', 'email']);
            })
                ->when(isset($request->filters), function ($query) use ($request) {
                    $query->where(function ($subquery) use ($request) {
                        $this->filterCallback($subquery, $request, $this->getFilterEmployeeData($request->filters));
                    });
                });
        });

        $paginated = $employees->paginate(self::PER_PAGE);

        return $this->sendResponse('Employees retrieved successfully.', new UserCollection($paginated));
    }

    public function addUser(AddUserFormRequest $request)
    {
        $request->validated();

        DB::beginTransaction();
        try {

            $firstName = ucfirst(str_replace(' ', '', trim($request->first_name)));
            $lastName = strtolower(str_replace(' ', '', trim($request->last_name)));
            $rawPassword = $firstName . $lastName . '12345';

            $hashedPassword = Hash::make($rawPassword);

            $employee = User::create([
                'first_name' => $request->first_name,
                'mid_name' => $request->mid_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'contact_num' => $request->contact_no,
                'address' => $request->address,
                'status' => User::STATUS_ACTIVE,
            ]);

            Employee::create([
                'user_id' => $employee->id,
                'employee_type' => $request->user_type,
            ]);

            DB::commit();
            return $this->sendResponse('User created successfully.', new UserResource($employee));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function updateUser(int $id, UpdateAccountRequest $request)
    {
        $request->validated();

        $user = User::has('employee')->find($id);

        if (!$user) {
            return $this->sendError('User not found.', 404);
        }

        DB::beginTransaction();
        try {

            $user->update([
                'first_name'=> $request->first_name,
                'mid_name'=> $request->mid_name,
                'last_name'=> $request->last_name,
                'email'=> $request->email,
                'contact_num'=> $request->contact_no,
                'address'=> $request->address,
                'status' => $request->status ? 'active' : 'disabled'
            ]);

            $user->employee->update([
                'employee_type' => $request->user_type,
            ]);

            $user->refresh();
            DB::commit();
            return $this->sendResponse('User updated successfully.', new UserResource($user));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    private function getFilterEmployeeData($filters = [])
    {
        return [
            'user_type' => [
                'type' => 'relationship',
                'model' => 'employee',
                'callback' => function ($subquery) use ($filters) {
                    $subquery->where('employee_type', $filters['user_type'] ?? null);
                }
            ],
        ];
    }
}
