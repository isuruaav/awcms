<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('course_qualifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('qualification');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['course_id', 'sort_order']);
        });

        $courses = [
            'Advanced Certificate in Applied Modern Information and Communication Technology for Signals Young Officers',
            'Advanced Certificate in Intermediate Leadership and Management',
            'Certificate in Leadership and Management',
            'Basic Certificate in Information and Communication Technology (45 Days)',
            'Certificate in Information and Communication Technology Level - I (29 Days)',
            'Certificate in Information and Communication Technology Level - II',
            'Certificate in Information and Communication Technology Level - III',
            'Signalman to Lance Corporal Promotion Course',
            'Lance Corporal to Corporal Promotion Course',
            'Corporal to Sergeant Promotion Course (50 Days)',
            'Advance Certificate in Leadership and Management',
            'Advance Certificate in Field Communication Technology for Regimental Signal Officers (ACFCT - RSO)',
            'Advance Certificate for Instructors in Communication Technology (ACICT)',
            'Certificate in Field Communication Technology for Other Arms (CFCT)',
            'Certificate in Electronic Warfare',
            'Certificate in Applied Computer Technology (CACT)',
        ];

        $now = now();
        DB::table('courses')->insert(array_map(
            static fn (string $name, int $index): array => [
                'name' => $name,
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $courses,
            array_keys($courses),
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('course_qualifications');
        Schema::dropIfExists('courses');
    }
};
