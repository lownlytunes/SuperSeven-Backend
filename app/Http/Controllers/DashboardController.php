<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedbackResource;
use App\Http\Resources\PackageResource;
use App\Models\Feedback;
use App\Models\Package;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function fetchPackages()
    {
        $packages = Package::where('status', Package::STATUS_ACTIVE)->get();
        return $this->sendResponse('Packages retrieved successfully.',  PackageResource::collection($packages));
    }

    public function fetchFeedbacks()
    {
        $feedbacks = Feedback::where('feedback_status', Feedback::STATUS_POSTED)->get();
        return $this->sendResponse('Feedbacks retrieved successfully.',  FeedbackResource::collection($feedbacks));
    }
}
