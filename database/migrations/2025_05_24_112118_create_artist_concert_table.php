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
        Schema::create('artist_concert', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('concert_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('artist_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_concert');
    }
};
