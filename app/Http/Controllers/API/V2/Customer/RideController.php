<?php


namespace App\Http\Controllers\API\V2\Customer;




use App\Helpers\api\Helpers;
use App\Helpers\TarifCourseService;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RideController
{

    public function calculerTarif(Request $request)
    {
        $distance = $request->input('distance'); // km
        $duree = $request->input('time'); // minutes

        $service = new TarifCourseService();

        $types = ['classic', 'confort', 'vip', 'moto'];
        $tarifs = [];

        foreach ($types as $type) {
            $tarifs[] = [
                'type' => ucfirst($type),
                'time' => round($duree) . ' min',
                'price' => number_format($service->calculerPrix($distance, $duree, $type), 0, '', ' ') . ' FCFA'
            ];
        }

        return Helpers::success(['vehicles' => $tarifs]);
    }
    /**
     * Enregistrer une nouvelle course.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        // ✅ 1️⃣ Validation des données
        $validated = $request->validate([
            'driver_id' => 'nullable|exists:users,id',
            'payment_method_id' => 'nullable',
            'amount' => 'required|numeric|min:0',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'dropoff_latitude' => 'required|numeric',
            'dropoff_longitude' => 'required|numeric',
            'type_course' => [
                'required',
                function ($attribute, $value, $fail) {
                    $allowed = ['classic', 'confort', 'vip', 'moto'];
                    if (!in_array(strtolower($value), array_map('strtolower', $allowed))) {
                        $fail("Le champ $attribute doit être l'un de : " . implode(', ', $allowed));
                    }
                },
            ],
            'instructions' => 'nullable|string|max:500',
        ]);


        try {
            // ✅ 2️⃣ Génération d’une référence unique
            $reference = 'RIDE-' . strtoupper(Str::random(8));

            // ✅ 3️⃣ Création de la course
            $ride = Ride::create([
                'reference' => $reference,
                'customer_id' => $user->customer->id,
                'driver_id' => $validated['driver_id'] ?? null,
                'payment_method_id' => $validated['payment_method_id']==0 ? null : $validated['payment_method_id'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'amount' => $validated['amount'],
                'pickup_latitude' => $validated['pickup_latitude'],
                'pickup_longitude' => $validated['pickup_longitude'],
                'dropoff_latitude' => $validated['dropoff_latitude'],
                'dropoff_longitude' => $validated['dropoff_longitude'],
                'type_course' => $validated['type_course'],
                'instructions' => $validated['instructions'] ?? null,
            ]);

            // ✅ 4️⃣ Réponse standardisée
            return Helpers::success([
                'message' => 'Course enregistrée avec succès.',
                'ride' => $ride
            ]);

        } catch (\Exception $e) {
            return Helpers::error('Erreur lors de la création de la course : ' . $e->getMessage());
        }
    }
    public function getRide($id)
    {
        // 🔍 Recherche de la course
        $ride = Ride::with(['driver', 'paymentMethod'])->find($id);

        if (!$ride) {
            return response()->json([
                'success' => false,
                'message' => 'Course introuvable.'
            ], 404);
        }

        // ✅ Construction de la réponse
        $response = [
            'driver_name' => $ride->driver->name ?? '',
            'driver_phone' => $ride->driver->phone ?? '',
            'driver_id' => $ride->driver_id,
            'payment_method_id' => $ride->payment_method_id,
            'amount' => (int) $ride->amount,
            'pickup_latitude' => (double) $ride->pickup_latitude,
            'pickup_longitude' => (double) $ride->pickup_longitude,
            'dropoff_latitude' => (double) $ride->dropoff_latitude,
            'dropoff_longitude' => (double) $ride->dropoff_longitude,
            'type_course' => $ride->type_course,
            'instructions' => $ride->instructions ?? ''
        ];

        return response()->json([
            'success' => true,
            'data' => $response
        ], 200);
    }
}
