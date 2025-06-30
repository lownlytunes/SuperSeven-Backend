<?php

namespace App\Http\Controllers\User\Customer;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AddUserFormRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\Collections\CustomerCollection;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerAccountController extends BaseController
{
    public function getCustomers(PaginateRequest $request)
    {
        $customer = User::has('customer')->when(isset($request->search), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $this->searchCallback($query, $request, ['first_name', 'mid_name', 'last_name', 'email']);
            })
                ->when(isset($request->filters), function ($query) use ($request) {
                    $query->where(function ($subquery) use ($request) {
                        $this->filterCallback($subquery, $request, $this->getFilterCustomerData($request->filters));
                    });
                });
        });

        $paginated = $customer->paginate(self::PER_PAGE);

        return $this->sendResponse('Customers retrieved successfully.', new CustomerCollection($paginated));
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

            $customer = User::create([
                'first_name' => $request->first_name,
                'mid_name' => $request->mid_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'contact_num' => $request->contact_no,
                'address' => $request->address,
                'status' => User::STATUS_ACTIVE,
            ]);

            Customer::create([
                'user_id' => $customer->id,
                'customer_type' => $request->user_type,
            ]);

            DB::commit();
            return $this->sendResponse('User created successfully.', new UserResource($customer));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function updateUser(int $id, UpdateAccountRequest $request)
    {
        $request->validated();

        $user = User::has('customer')->find($id);

        if (!$user) {
            return $this->sendError('User not found.', 404);
        }

        DB::beginTransaction();
        try {

            $user->update([
                'first_name' => $request->first_name,
                'mid_name' => $request->mid_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'contact_num' => $request->contact_no,
                'address' => $request->address,
                'status' => $request->status ? 'active' : 'disabled'
            ]);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    private function getFilterCustomerData($filters = [])
    {
        return [
            'user_type' => [
                'type' => 'relationship',
                'model' => 'customer',
                'callback' => function ($subquery) use ($filters) {
                    $subquery->where('customer_type', $filters['user_type'] ?? null);
                }
            ],
        ];
    }
}
