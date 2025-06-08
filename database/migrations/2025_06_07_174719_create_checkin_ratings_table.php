<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkin_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('checkin_id')->constrained('checkins')->onDelete('cascade');
            $table->decimal('rating', 2, 1)->nullable();
            $table->timestamps();
        });

        // Add check constraint to ensure rating is between 0.5 and 5 and in 0.5 increments
        DB::statement('ALTER TABLE checkin_ratings ADD CONSTRAINT checkin_ratings_rating_check CHECK (rating IS NULL OR (rating >= 0.5 AND rating <= 5.0 AND MOD(rating * 10, 5) = 0))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkin_ratings');
    }
};
