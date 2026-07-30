<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_active')
                ->default(true)
                ->index()
                ->after('email_verified_at');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('remember_token');

            $table->string('last_login_ip', 45)
                ->nullable()
                ->after('last_login_at');

            $table->foreignId('created_by')
                ->nullable()
                ->after('last_login_ip')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');

            $table->dropColumn([
                'is_active',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
