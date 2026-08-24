<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'news',
            function (Blueprint $table): void {
                $table->timestamp(
                    'changes_requested_at',
                )
                    ->nullable()
                    ->after(
                        'submitted_at',
                    );

                $table->foreignId(
                    'changes_requested_by',
                )
                    ->nullable()
                    ->after(
                        'submitted_by',
                    )
                    ->constrained(
                        'users',
                    )
                    ->nullOnDelete();

                $table->string(
                    'change_request_note',
                    1000,
                )
                    ->nullable()
                    ->after(
                        'changes_requested_by',
                    );
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'news',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'changes_requested_by',
                ]);

                $table->dropColumn([
                    'changes_requested_at',
                    'changes_requested_by',
                    'change_request_note',
                ]);
            },
        );
    }
};
