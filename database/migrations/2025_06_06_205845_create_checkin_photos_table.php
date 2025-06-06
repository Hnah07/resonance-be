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
        Schema::create('checkin_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('checkin_id')->constrained('checkins')->onDelete('cascade');
            $table->string('url');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkin_photos');
    }
};
