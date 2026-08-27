<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name', 180)->default('Official Website');
            $table->string('site_tagline', 255)->nullable();
            $table->string('theme_family', 40)->default('army-unit');
            $table->foreignId('logo_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('favicon_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->text('address')->nullable();
            $table->string('phone_primary', 50)->nullable();
            $table->string('phone_secondary', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->string('commander_name', 180)->nullable();
            $table->string('commander_title', 180)->nullable();
            $table->text('commander_message')->nullable();
            $table->foreignId('commander_image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('primary_color', 20)->default('#166534');
            $table->string('accent_color', 20)->default('#ca8a04');
            $table->text('footer_text')->nullable();
            $table->string('default_seo_title', 255)->nullable();
            $table->string('default_seo_description', 320)->nullable();
            $table->boolean('maintenance_mode')->default(false)->index();
            $table->text('maintenance_message')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
