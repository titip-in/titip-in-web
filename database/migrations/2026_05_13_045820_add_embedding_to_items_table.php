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
        Schema::table('jastip_listings', function (Blueprint $table) {
            $table->vector('embedding', 3072)->nullable()->index();
        });

        Schema::table('preloved_listings', function (Blueprint $table) {
            $table->vector('embedding', 3072)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jastip_listings', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });

        Schema::table('preloved_listings', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });
    }
};
