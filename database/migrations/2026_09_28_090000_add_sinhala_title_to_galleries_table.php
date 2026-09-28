<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table): void {
            $table->string('title_si', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table): void {
            $table->dropColumn('title_si');
        });
    }
};
