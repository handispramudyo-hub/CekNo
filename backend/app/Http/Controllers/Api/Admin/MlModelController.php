<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MlModelController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $this->authorize('manage', MlModel::class);

        return response()->json(['models' => MlModel::latest()->get()]);
    }

    public function activate(Request $request, MlModel $model): JsonResponse
    {
        $this->authorize('manage', MlModel::class);

        // Retire model active sebelumnya untuk algoritma yang sama
        MlModel::where('algorithm', $model->algorithm)
            ->where('id', '!=', $model->id)
            ->update(['status' => 'retired']);

        $model->forceFill(['status' => 'active'])->save();

        return response()->json(['model' => $model]);
    }
}