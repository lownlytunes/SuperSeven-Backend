<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends BaseController
{
    public function getPackages(Request $request)
    {
        $packages = Package::orderBy('package_name', 'asc')->get();

        return $this->sendResponse('Packages retrieved successfully.', PackageResource::collection($packages));
    }
}
