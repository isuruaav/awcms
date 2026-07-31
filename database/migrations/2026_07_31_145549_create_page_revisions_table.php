<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'page_revisions',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('page_id')
                    ->constrained('pages')
                    ->cascadeOnDelete();

                $table->unsignedInteger(
                    'revision_number',
                );

                $table->string('title');
                $table->string('slug');

                $table
                    ->text('excerpt')
                    ->nullable();

                $table
                    ->longText('content')
                    ->nullable();

                $table
                    ->json('blocks')
                    ->nullable();

                $table
                    ->string('status', 30)
                    ->index();

                $table
                    ->string('change_summary')
                    ->nullable();

                $table
                    ->char('snapshot_hash', 64)
                    ->index();

                $table
                    ->unsignedInteger(
                        'restored_from_revision_number',
                    )
                    ->nullable();

                $table
                    ->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('created_at');

                $table->unique([
                    'page_id',
                    'revision_number',
                ]);

                $table->index([
                    'page_id',
                    'created_at',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'page_revisions',
        );
    }
};
