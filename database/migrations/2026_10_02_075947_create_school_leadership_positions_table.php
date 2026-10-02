<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_leadership_positions', function (Blueprint $table): void {
            $table->id();

            $table->string('key', 100)->unique();

            $table->string('name_en', 180);
            $table->string('name_si', 220);

            $table->string('rank_group', 30);

            $table->unsignedSmallInteger('sort_order')
                ->default(100);

            $table->boolean('show_on_home')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index([
                'is_active',
                'sort_order',
            ]);
        });

        DB::table('school_leadership_positions')->insert([
            [
                'key' => 'commandant',
                'name_en' => 'The Commandant',
                'name_si' => 'සේනාවිධායක',
                'rank_group' => 'commissioned',
                'sort_order' => 1,
                'show_on_home' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'chief_instructor',
                'name_en' => 'The Chief Instructor',
                'name_si' => 'ප්‍රධාන උපදේශක',
                'rank_group' => 'commissioned',
                'sort_order' => 2,
                'show_on_home' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'adjutant',
                'name_en' => 'Adjutant',
                'name_si' => 'අජුටන්ට්',
                'rank_group' => 'commissioned',
                'sort_order' => 3,
                'show_on_home' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'warrant_officer',
                'name_en' => 'Warrant Officer School of Signals',
                'name_si' => 'සංඥා පාසලේ වෝරන්ට් නිලධාරී',
                'rank_group' => 'other_ranks',
                'sort_order' => 4,
                'show_on_home' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'school_leadership_positions',
        );
    }
};
