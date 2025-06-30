<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddPackageRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Resources\Collections\PackageCollection;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends BaseController
{
    public function getPackages(PaginateRequest $request)
    {
        $packages = Package::when(isset($request->search), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $this->searchCallback($query, $request, ['package_name', 'package_price']);
            });
        });

        $paginated = $packages->paginate(self::PER_PAGE);

        return $this->sendResponse('Packages retrieved successfully.', new PackageCollection($paginated));
    }

    public function addPackage(AddPackageRequest $request)
    {
        $request->validated();

        DB::beginTransaction();
        try {

            $package = Package::create([
                'package_name' => $request->package_name,
                'package_details' => $request->package_details,
                'package_price' => $request->package_price,
            ]);

            DB::commit();
            return $this->sendResponse('Package created successfully.', new PackageResource($package));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function updatePackage(int $id, UpdatePackageRequest $request)
    {
        $request->validated();

        $package = Package::find($id);

        if (!$package) {
            return $this->sendError('Package not found.', 404);
        }

        DB::beginTransaction();
        try {

            $package->update([
                'package_name' => $request->package_name,
                'package_details' => $request->package_details,
                'package_price' => $request->package_price,
            ]);

            DB::commit();
            return $this->sendResponse('Package updated successfully.', new PackageResource($package));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function deletePackage(int $id)
    {
        $package = Package::find($id);
        if (!$package) {
            return $this->sendError('Package not found.', 404);
        }

        DB::beginTransaction();
        try{
            $package->delete();
            DB::commit();
            return $this->sendResponse('Package deleted successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }
}
