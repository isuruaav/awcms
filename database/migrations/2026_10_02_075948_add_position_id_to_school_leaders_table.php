<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_leaders', function (Blueprint $table): void {
            $table->foreignId('position_id')
                ->nullable()
                ->after('id')
                ->constrained('school_leadership_positions')
                ->nullOnDelete();

            $table->index([
                'position_id',
                'start_date',
            ]);
        });

        $positions = DB::table(
            'school_leadership_positions',
        )
            ->pluck(
                'id',
                'key',
            );

        foreach ($positions as $key => $positionId) {
            DB::table('school_leaders')
                ->where(
                    'role_key',
                    $key,
                )
                ->update([
                    'position_id' => $positionId,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('school_leaders', function (Blueprint $table): void {
            $table->dropForeign([
                'position_id',
            ]);

            $table->dropIndex([
                'position_id',
                'start_date',
            ]);

            $table->dropColumn(
                'position_id',
            );
        });
    }
};
