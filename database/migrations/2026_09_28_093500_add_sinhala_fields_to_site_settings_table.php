<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('site_name_si', 180)->nullable();
            $table->string('site_tagline_si', 255)->nullable();
            $table->text('address_si')->nullable();
            $table->string('commander_name_si', 180)->nullable();
            $table->string('commander_title_si', 180)->nullable();
            $table->text('commander_message_si')->nullable();
            $table->text('footer_text_si')->nullable();
            $table->text('maintenance_message_si')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn(['site_name_si', 'site_tagline_si', 'address_si', 'commander_name_si', 'commander_title_si', 'commander_message_si', 'footer_text_si', 'maintenance_message_si']);
        });
    }
};
