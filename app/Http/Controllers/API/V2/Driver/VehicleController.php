<?php


namespace App\Http\Controllers\API\V2\Driver;

use App\Helpers\api\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    public function index() {
        $user = Auth::user();
        $vehicules = Vehicle::query()
            ->whereDoesntHave('drivers')
            ->latest()
            ->get();
        return Helpers::success($vehicules);
    }
    public function store(Request $request) {

        // 1️⃣ Validation des champs
        $validator = Validator::make($request->all(), [
            'brand'       => 'required|string',
            'color'       => 'required|string',
            'numberplate' => 'required|string',
            'passenger'   => 'nullable|integer|min:1',
            'type'        => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // ⚡ sécurisation image
        ]);

        if ($validator->fails()) {
            $err = $validator->errors()->first();
            return Helpers::error($err);
        }

        $validated = $validator->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('vehicules', 'public');
        }
        DB::beginTransaction();

        try {
            // ⚡ Utiliser la relation OneToOne
            $vehicule = Vehicle::Create(
                [
                    'registration' => $validated['numberplate']?? null,
                    'brand'       => $validated['brand']?? null,
                    'color'       => $validated['color'] ?? null,
                    'seats'   => $validated['passenger'] ?? 1,
                    'type'        => $validated['type'] ?? null,
                    'image'       => $imagePath,
                ]
            );


            DB::commit();

            return Helpers::success($vehicule, 'Véhicule créé avec succès ✅');
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du véhicule', [
                'message' => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
            ]);
            return Helpers::error('Une erreur est survenue lors de la création du véhicule');
        }
    }
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}
