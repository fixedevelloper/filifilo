<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyStatusInDeliveriesTable extends Migration
{
    /**
     * Exécute la migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Modifier la colonne 'status' en ajoutant 'current'
            $table->enum('status', ['assigned', 'in_delivery', 'delivered', 'current'])
                ->default('current') // La valeur par défaut reste 'assigned'
                ->change();
        });
    }

    /**
     * Annule la migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deliveries', function (Blueprint $table) {

            // Modifier à nouveau la colonne 'status' pour enlever 'current'
            $table->enum('status', ['assigned', 'in_delivery', 'delivered'])
                ->default('assigned') // Remet la valeur par défaut sur 'assigned'
                ->change();
        });
    }
}
