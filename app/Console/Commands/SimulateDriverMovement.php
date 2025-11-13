<?php


namespace App\Console\Commands;

use App\Models\Driver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateDriverMovement extends Command
{
    protected $signature = 'driver:simulate {driver_id} {--interval=3}';
    protected $description = 'Simule le déplacement d’un chauffeur et envoie les nouvelles positions à l’API';

    public function handle()
    {
        $driverId = $this->argument('driver_id');
        $interval = (int) $this->option('interval'); // secondes
        $driver=Driver::query()->find($driverId);
        // Position initiale (ex : Douala)
        $latitude = 4.0131375;
        $longitude = 9.749234399999999;

        $this->info("🚕 Simulation du chauffeur #$driverId (toutes les $interval secondes)");

        while (true) {
            // Variation aléatoire (déplacement léger)
            $latitude += (rand(-5, 5) / 10000.0);
            $longitude += (rand(-5, 5) / 10000.0);

            // Appel de l’API
            $response = Http::post('http://localhost:8000/api/driver/location', [
                'driver_id' => $driverId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'device_id'=>$driver->device_id
            ]);

            if ($response->successful()) {
                $this->info("✅ Nouvelle position envoyée : $latitude, $longitude");
            } else {
                $this->error("❌ Erreur : " . $response->status());
            }

            sleep($interval);
        }
    }
}

