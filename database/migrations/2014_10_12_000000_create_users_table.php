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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            //$table->string('name');
            $table->string('email', 150)->unique();
            //$table->timestamp('email_verified_at')->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->rememberToken();
            $table->boolean('is_test_user')->default(false);
            //$table->timestamps();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
