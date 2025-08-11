<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\TransporterPositionUpdated;

class SimulateTransporterMovement extends Command
{
    protected $signature = 'simulate:transporter {orderId}';
    protected $description = 'Simule le mouvement du transporteur pour un ordre';

    public function handle()
    {
        $orderId = $this->argument('orderId');

        // 📍 Liste de points (latitude, longitude) sur un trajet fictif
        $positions = [
            [4.0511, 9.7679],
            [4.0520, 9.7685],
            [4.0532, 9.7690],
            [4.0540, 9.7700],
            [4.0555, 9.7705],
            [4.0567, 9.7710],
        ];

        foreach ($positions as $pos) {
            broadcast(new TransporterPositionUpdated([
                'transporterId'=>7,
                'lat'=>$pos[0],
                'lng'=>$pos[1]]));
            $this->info("Position envoyée : {$pos[0]}, {$pos[1]}");
            sleep(2); // Pause 2 secondes pour simuler un déplacement
        }

        $this->info('Simulation terminée 🚚');
    }
}
