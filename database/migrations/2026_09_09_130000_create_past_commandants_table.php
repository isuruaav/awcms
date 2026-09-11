<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('past_commandants', function (Blueprint $table): void {
            $table->id();
            $table->string('name_en', 180);
            $table->string('name_si', 220)->nullable();
            $table->date('from_date');
            $table->date('to_date');
            $table->foreignId('image_media_id')
                ->nullable()
                ->constrained('media_assets')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['to_date', 'from_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('past_commandants');
    }
};
