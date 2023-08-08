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
        Schema::create('user_weather_forecast_item', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('weather_forecast_item_id');
            $table->unsignedInteger('display_order');

            $table->primary(['user_id', 'weather_forecast_item_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();;
            $table->foreign('weather_forecast_item_id')->references('id')->on('weather_forecast_items')->cascadeOnDelete();;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_weather_forecast_item');
    }
};
