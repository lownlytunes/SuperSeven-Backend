<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddAddonRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\UpdateAddonRequest;
use App\Http\Resources\AddonResource;
use App\Http\Resources\Collections\AddonCollection;
use App\Models\AddOn;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddonController extends BaseController
{
    public function getAddons(PaginateRequest $request)
    {
        $addon = Addon::when(isset($request->search), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $this->searchCallback($query, $request, ['add_on_name', 'add_on_price']);
            });
        });

        $paginated = $addon->paginate(self::PER_PAGE);

        return $this->sendResponse('Addons retrieved successfully.', new AddonCollection($paginated));
    }

    public function addAddon(AddAddonRequest $request)
    {
        $request->validated();

        DB::beginTransaction();
        try {

            $addon = AddOn::create([
                'add_on_name' => $request->add_on_name,
                'add_on_details' => $request->add_on_details,
                'add_on_price' => $request->add_on_price,
            ]);

            DB::commit();
            return $this->sendResponse('Addon created successfully.', new AddonResource($addon));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function updateAddon(int $id, UpdateAddonRequest $request)
    {
        $request->validated();

        $addon = Addon::find($id);

        if (!$addon) {
            return $this->sendError('Addon not found.', 404);
        }

        DB::beginTransaction();
        try {

            $addon->update([
                'add_on_name' => $request->add_on_name,
                'add_on_details' => $request->add_on_details,
                'add_on_price' => $request->add_on_price,
            ]);

            DB::commit();
            return $this->sendResponse('Addon updated successfully.', new AddonResource($addon));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function deleteAddon(int $id)
    {
        $addon = Addon::find($id);

        if (!$addon) {
            return $this->sendError('Addon not found',404);
        }

        DB::beginTransaction();
        try {

            $addon->delete();
            DB::commit();
            return $this->sendResponse('Addon deleted successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }
}
