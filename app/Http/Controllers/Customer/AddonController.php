<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\AddonResource;
use App\Models\AddOn;
use Illuminate\Http\Request;

class AddonController extends BaseController
{
    public function getAddons(Request $request)
    {
        $addons = AddOn::orderBy('add_on_name', 'asc')->get();

        return $this->sendResponse('Addons retrieved successfully.', AddonResource::collection($addons));
    }
}
