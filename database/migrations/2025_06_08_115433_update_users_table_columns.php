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
            // Drop old columns if they exist
            if (Schema::hasColumn('users', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('users', 'latitude')) {
                $table->dropColumn('latitude');
            }

            // Add new columns if they don't exist
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable();
            }
        });

        // Create country_id column first
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'country_id')) {
                $table->foreignUuid('country_id')->nullable()->after('city')->constrained('countries');
            }
        });

        // Now migrate the data if country column exists
        if (Schema::hasColumn('users', 'country')) {
            DB::statement('UPDATE users SET country_id = (SELECT id FROM countries WHERE name = users.country LIMIT 1)');

            // Drop the old column after data migration
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('country');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop new columns
            if (Schema::hasColumn('users', 'country_id')) {
                $table->dropForeign(['country_id']);
                $table->dropColumn('country_id');
            }
            if (Schema::hasColumn('users', 'city')) {
                $table->dropColumn('city');
            }

            // Add back old columns
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('country')->nullable();
        });
    }
};
