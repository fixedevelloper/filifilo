<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods');
            $table->enum('status', ['pending','preparation','in_delivery','delivered','cancelled'])->default('pending');
            $table->enum('payment_status', ['pending','paid','failed'])->default('pending');
            $table->decimal('amount', 10,2);
            $table->double('pickup_latitude');
            $table->double('pickup_longitude');
            $table->double('dropoff_latitude');
            $table->double('dropoff_longitude');
            $table->enum('type_course',['classic','confort','vip','moto'])->default('classic');
            $table->text('instructions')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
