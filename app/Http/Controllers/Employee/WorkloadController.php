<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\UpdateWorkloadRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\Collections\WorkloadCollection;
use App\Http\Resources\WorkloadResource;
use App\Models\Booking;
use App\Services\WorkloadService;
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
        $user = auth()->user();

        $workloads = Booking::with('customer', 'employees')
            ->when(isset($request->search), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $this->searchCallback($query, $request, ['event_name', 'customer.first_name', 'customer.last_name']);
                });
            })->when(isset($request->filters), function ($query) use ($request) {
                $query->where(function ($subquery) use ($request) {
                    $this->filterCallback($subquery, $request, $this->workloadService->getFilterWorkloadData($request->filters));
                });
            })->whereHas('employees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->orderBy('deliverable_status');

        $paginated = $workloads->paginate(self::PER_PAGE);

        return $this->sendResponse('Workloads retrieved successfully.', new WorkloadCollection($paginated));
    }

    public function viewWorkload(int $id)
    {
        $user = auth()->user();

        $workload = Booking::with('customer', 'employees')->where('id', $id)
            ->whereHas('employees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();

        if (!$workload) {
            return $this->sendError('Workload not found.', 404);
        }

        return $this->sendResponse('Workload retrieved successfully.', new WorkloadResource($workload));
    }

    public function updateWorkload(int $id, UpdateWorkloadRequest $request)
    {
        $user = auth()->user();

        $user->load('employee');

        $booking = Booking::where('id', $id)
            ->whereHas('employees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();

        if (!$booking) {
            return $this->sendError('Workload not found.', 404);
        }

        DB::beginTransaction();
        try {
            $booking->employees()->updateExistingPivot($user->id, [
                'workload_status' => $request->workload_status,
                'date_uploaded' => now()
            ]);

            $this->workloadService->notifyStatusChange($booking, $booking->deliverable_status, $request->workload_status, $user->full_name);

            DB::commit();
            return $this->sendResponse('Workload updated successfully.', new WorkloadResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }
}
