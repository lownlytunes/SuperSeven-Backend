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
use Illuminate\Support\Facades\Storage;

class PackageController extends BaseController
{
    public function getPackages(PaginateRequest $request)
    {
        $packages = Package::when(isset($request->search), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $this->searchCallback($query, $request, ['package_name', 'package_price']);
            });
        })
        ->where('status', '=', Package::STATUS_ACTIVE);

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
                'status' => Package::STATUS_ACTIVE,
            ]);

            if ($request->hasFile('image')) {

                // Store the image and get the file information
                $imageData =  $this->storageImage($request->file('image'), $package->id);

                $package->update([
                    'image_name' => $imageData['image_name'],
                    'image_path' => $imageData['image_path'],
                ]);
            }

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

            // Update basic fields
            $package->update([
                'package_name' => $request->package_name,
                'package_details' => $request->package_details,
                'package_price' => $request->package_price,
            ]);

            // Handle explicit image removal
            if ($request->input('remove_image') == 1 && !$request->hasFile('image')) {
                $this->deletePackageImage($package);

                $package->update([
                    'image_name' => null,
                    'image_path' => null,
                ]);
            }

            // Handle image replacement (new image upload)
            if ($request->hasFile('image') && !$request->input('remove_image')) {
                // Always delete old image first
                $this->deletePackageImage($package);

                $imageData = $this->storageImage($request->file('image'), $package->id);

                $package->update([
                    'image_name' => $imageData['image_name'],
                    'image_path' => $imageData['image_path'],
                ]);
            }

            DB::commit();
            return $this->sendResponse('Package updated successfully.', new PackageResource($package));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function setPackageInactive(int $id)
    {
        $package = Package::find($id);

        if (!$package) {
            return $this->sendError('Package not found.', 404);
        }

        DB::beginTransaction();
        try{

            $package->status = Package::STATUS_INACTIVE;

            $package->save();
            DB::commit();
            return $this->sendResponse('Package deleted successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    private function storageImage($file, int $id)
    {
        $filenameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanFilename = preg_replace('/[^\w.]+/', '_', $filenameWithoutExtension);
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $filename = $cleanFilename . '.' . $extension;
        $filepath = Package::IMAGE_PATH . '/' . $id . '/' . $filename;

        // Store the file in the public disk
        Storage::disk('public')->put($filepath, file_get_contents($file));

        return [
            'image_name' => $filename,
            'image_path' => $filepath,
            'image_mime_type' => $extension,
        ];
    }

    private function deletePackageImage(Package $package): void
    {
        if ($package->image_path && Storage::disk('public')->exists($package->image_path)) {
            Storage::disk('public')->delete($package->image_path);
        }
    }
}
