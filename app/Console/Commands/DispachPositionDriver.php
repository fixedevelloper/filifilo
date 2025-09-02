<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class DispachPositionDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dispach-position-driver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders=Order::query()->where('status');
    }
}
