<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('name_si', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->foreignId('course_category_id')
                ->nullable()
                ->after('name_si')
                ->constrained('course_categories')
                ->nullOnDelete();
            $table->index(['course_category_id', 'sort_order']);
        });

        $categories = [
            ['name' => 'ICT & Computing', 'name_si' => 'තොරතුරු තාක්ෂණය', 'sort_order' => 1],
            ['name' => 'Leadership & Management', 'name_si' => 'නායකත්වය හා කළමනාකරණය', 'sort_order' => 2],
            ['name' => 'Promotion Courses', 'name_si' => 'උසස් කිරීමේ පාඨමාලා', 'sort_order' => 3],
            ['name' => 'Signals & Field Communications', 'name_si' => 'සංඥා හා ක්ෂේත්‍ර සන්නිවේදනය', 'sort_order' => 4],
            ['name' => 'Other Courses', 'name_si' => 'වෙනත් පාඨමාලා', 'sort_order' => 5],
        ];

        $now = now();
        foreach ($categories as &$category) {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }
        unset($category);
        DB::table('course_categories')->insert($categories);

        $ids = DB::table('course_categories')->pluck('id', 'name');
        $courses = DB::table('courses')->select(['id', 'name'])->get();
        foreach ($courses as $course) {
            $categoryName = match (true) {
                str_contains($course->name, 'Leadership') => 'Leadership & Management',
                str_contains($course->name, 'Promotion Course') => 'Promotion Courses',
                str_contains($course->name, 'Information and Communication Technology'),
                str_contains($course->name, 'Applied Computer Technology') => 'ICT & Computing',
                str_contains($course->name, 'Communication Technology'),
                str_contains($course->name, 'Electronic Warfare') => 'Signals & Field Communications',
                default => 'Other Courses',
            };

            DB::table('courses')->where('id', $course->id)->update([
                'course_category_id' => $ids[$categoryName],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex(['course_category_id', 'sort_order']);
            $table->dropConstrainedForeignId('course_category_id');
        });

        Schema::dropIfExists('course_categories');
    }
};
