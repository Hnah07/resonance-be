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
        Schema::table('users', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['longitude', 'latitude']);

            // Add new columns if they don't exist
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable();
            }

            // Change country to country_id
            if (Schema::hasColumn('users', 'country')) {
                // First, create the new column
                $table->foreignUuid('country_id')->nullable()->after('city')->constrained('countries');

                // Migrate existing data
                DB::statement('UPDATE users SET country_id = (SELECT id FROM countries WHERE name = users.country LIMIT 1)');

                // Drop the old column
                $table->dropColumn('country');
            } else if (!Schema::hasColumn('users', 'country_id')) {
                $table->foreignUuid('country_id')->nullable()->after('city')->constrained('countries');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['country_id']);
            $table->dropColumn(['city', 'country_id']);

            // Add back old columns
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('country')->nullable();
        });
    }
};
