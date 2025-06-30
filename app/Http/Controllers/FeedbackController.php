<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Http\Resources\Collections\FeedbackCollection;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends BaseController
{
    public function getFeedbacks(PaginateRequest $request)
    {
        $feedbacks = Feedback::when(isset($request->search), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $this->searchCallback($query, $request, ['booking.event_name', 'user.first_name', 'user.last_name']);
            });
        })->when(isset($request->filters), function ($query) use ($request) {
            $query->where(function ($subquery) use ($request) {
                $this->filterCallback($subquery, $request, $this->getFilterFeedbackData());
            });
        })->orderBy('feedback_date');

        $paginated = $feedbacks->paginate(self::PER_PAGE);

        return $this->sendResponse('Feedbacks retrieved successfully.', new FeedbackCollection($paginated));
    }

    public function viewFeedback(int $id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return $this->sendError('Feedback not found.', 404);
        }

        return $this->sendResponse('Feedback retrieved successfully.', new FeedbackResource($feedback));
    }

    public function markFeedbackAsPosted(int $id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return $this->sendError('Feedback not found.', 404);
        }

        DB::beginTransaction();
        try {

            $feedback->feedback_status = Feedback::STATUS_POSTED;
            $feedback->save();
            DB::commit();
            return $this->sendResponse('Feedback marked as posted successfully.', new FeedbackResource($feedback));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function markFeedBackAsUnposted(int $id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return $this->sendError('Feedback not found.',404);
        }

        DB::beginTransaction();
        try {
            $feedback->feedback_status = Feedback::STATUS_UNPOSTED;
            $feedback->save();

            DB::commit();
            return $this->sendResponse('Feedback marked as unposted successfully.', new FeedbackResource($feedback));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    private function getFilterFeedbackData()
    {
        return [
            'posted' => [
                'type' => 'or',
                'condition' => "feedback_status = " . Feedback::STATUS_POSTED,
            ],
            'unposted' => [
                'type' => 'or',
                'condition' => "feedback_status = " . Feedback::STATUS_UNPOSTED,
            ],
            'pending' => [
                'type' => 'or',
                'condition' => "feedback_status = " . Feedback::STATUS_PENDING,
            ],
        ];
    }
}
