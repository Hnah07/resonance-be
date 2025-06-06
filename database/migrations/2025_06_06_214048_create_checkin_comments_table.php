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
        Schema::create('checkin_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('checkin_id')->constrained('checkins');
            $table->foreignUuid('user_id')->constrained('users');
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkin_comments');
    }
};
