<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddWorkloadRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\Collections\WorkloadCollection;
use App\Http\Resources\AddEmployeeWorkloadResource;
use App\Http\Resources\WorkloadResource;
use App\Services\WorkloadService;
use App\Models\Booking;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkloadController extends BaseController
{
    private WorkloadService $workloadService;

    public function __construct(WorkloadService $workloadService)
    {
        $this->workloadService = $workloadService;
    }

    public function getWorkloads(PaginateRequest $request)
    {
        $workloads = Booking::with('customer', 'employees')
            ->when(isset($request->search), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $this->searchCallback($query, $request, ['event_name', 'customer.first_name', 'customer.last_name']);
                });
            })->when(isset($request->filters), function ($query) use ($request) {
                $query->where(function ($subquery) use ($request) {
                    $this->filterCallback($subquery, $request, $this->getFilterWorkloadData($request->filters));
                });
            })->where('booking_status', Booking::STATUS_APPROVED)
            ->orderBy('deliverable_status');

        $paginated = $workloads->paginate(self::PER_PAGE);

        return $this->sendResponse('Workloads retrieved successfully.', new WorkloadCollection($paginated));
    }

    public function viewWorkload(int $id)
    {
        $workload = Booking::with('customer', 'employees')->where('id', $id)->first();

        if (!$workload) {
            return $this->sendError('Workload not found.', 404);
        }

        return $this->sendResponse('Workload retrieved successfully.', new WorkloadResource($workload));
    }

    public function assignWorkload(int $id, AddWorkloadRequest $request)
    {
        $request->validated();

        $booking = Booking::where('id', $id)->first();

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        DB::beginTransaction();
        try {

            $userIds = $request->input('user_id', []);
            $existingUserIds = $booking->employees()->pluck('users.id')->toArray();

            $booking->employees()->detach(array_diff($existingUserIds, $userIds));
            $booking->employees()->attach(array_diff($userIds, $existingUserIds), 
                [
                    'workload_status' => Booking::STATUS_PENDING
                ]);

            $booking->deliverable_status = $request->deliverable_status;
            $booking->link = $request->link;

            if ($booking->sent_completed_mail == false && $request->input('deliverable_status') == Booking::STATUS_COMPLETED) {
                
                $booking->sent_completed_mail = true;
                $booking->completion_date = now();

                $this->workloadService->sendCompletedMailToClient($booking, $booking->customer->email);
            }

            $booking->save();

            DB::commit();
            return $this->sendResponse('Workload assigned successfully.', new WorkloadResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function getAvailableEmployee(int $id)
    {
        $employee = User::with('employee')
            ->whereHas('employee', function ($query) {
                $query->whereIn('employee_type', [
                    User::PHOTOGRAPHER_TYPE,
                    User::EDITOR_TYPE
                ]);
            })
            ->get();

        return $this->sendResponse('Available employees retrieved successfully.', AddEmployeeWorkloadResource::collection($employee));
    }

    private function getFilterWorkloadData($filter = [])
    {
        return [
            'deliverable_status' => [
                'type' => 'raw',
                'condition' => 'deliverable_status = ' . $filter['deliverable_status'],
            ]
        ];
    }
}
