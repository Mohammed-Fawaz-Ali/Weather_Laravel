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
        if (!Schema::hasTable('search_histories')) {
            Schema::create('search_histories', function (Blueprint $table) {
                $table->id();
                $table->string('city');
                $table->string('country')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->timestamps();
                $table->index('city');
                $table->index('created_at');
            });

            return;
        }

        Schema::table('search_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('search_histories', 'city')) {
                $table->string('city')->default('');
            }

            if (!Schema::hasColumn('search_histories', 'country')) {
                $table->string('country')->nullable();
            }

            if (!Schema::hasColumn('search_histories', 'ip_address')) {
                $table->ipAddress('ip_address')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};