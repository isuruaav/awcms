<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->string('name_si')->nullable()->after('name');
        });

        $translations = [
            'Advanced Certificate in Applied Modern Information and Communication Technology for Signals Young Officers' => 'සංඥා බලකායේ තරුණ නිලධාරීන් සඳහා නවීන තොරතුරු හා සන්නිවේදන තාක්ෂණය ප්‍රායෝගිකව භාවිත කිරීම පිළිබඳ උසස් සහතිකපත්‍ර පාඨමාලාව',
            'Advanced Certificate in Intermediate Leadership and Management' => 'අතරමැදි නායකත්වය හා කළමනාකරණය පිළිබඳ උසස් සහතිකපත්‍ර පාඨමාලාව',
            'Certificate in Leadership and Management' => 'නායකත්වය හා කළමනාකරණය පිළිබඳ සහතිකපත්‍ර පාඨමාලාව',
            'Basic Certificate in Information and Communication Technology (45 Days)' => 'තොරතුරු හා සන්නිවේදන තාක්ෂණය පිළිබඳ මූලික සහතිකපත්‍ර පාඨමාලාව (දින 45)',
            'Certificate in Information and Communication Technology Level - I (29 Days)' => 'තොරතුරු හා සන්නිවේදන තාක්ෂණය පිළිබඳ සහතිකපත්‍ර පාඨමාලාව - I මට්ටම (දින 29)',
            'Certificate in Information and Communication Technology Level - II' => 'තොරතුරු හා සන්නිවේදන තාක්ෂණය පිළිබඳ සහතිකපත්‍ර පාඨමාලාව - II මට්ටම',
            'Certificate in Information and Communication Technology Level - III' => 'තොරතුරු හා සන්නිවේදන තාක්ෂණය පිළිබඳ සහතිකපත්‍ර පාඨමාලාව - III මට්ටම',
            'Signalman to Lance Corporal Promotion Course' => 'සංඥා සෙබළ සිට ලාන්ස් කෝප්‍රල් නිලයට උසස් කිරීමේ පාඨමාලාව',
            'Lance Corporal to Corporal Promotion Course' => 'ලාන්ස් කෝප්‍රල් සිට කෝප්‍රල් නිලයට උසස් කිරීමේ පාඨමාලාව',
            'Corporal to Sergeant Promotion Course (50 Days)' => 'කෝප්‍රල් සිට සැරයන් නිලයට උසස් කිරීමේ පාඨමාලාව (දින 50)',
            'Advance Certificate in Leadership and Management' => 'නායකත්වය හා කළමනාකරණය පිළිබඳ උසස් සහතිකපත්‍ර පාඨමාලාව',
            'Advance Certificate in Field Communication Technology for Regimental Signal Officers (ACFCT - RSO)' => 'රෙජිමේන්තු සංඥා නිලධාරීන් සඳහා ක්ෂේත්‍ර සන්නිවේදන තාක්ෂණය පිළිබඳ උසස් සහතිකපත්‍ර පාඨමාලාව (ACFCT - RSO)',
            'Advance Certificate for Instructors in Communication Technology (ACICT)' => 'සන්නිවේදන තාක්ෂණ උපදේශකවරුන් සඳහා උසස් සහතිකපත්‍ර පාඨමාලාව (ACICT)',
            'Certificate in Field Communication Technology for Other Arms (CFCT)' => 'අනෙකුත් හමුදා අංශ සඳහා ක්ෂේත්‍ර සන්නිවේදන තාක්ෂණය පිළිබඳ සහතිකපත්‍ර පාඨමාලාව (CFCT)',
            'Certificate in Electronic Warfare' => 'විද්‍යුත් සංග්‍රාම ක්‍රමවේද පිළිබඳ සහතිකපත්‍ර පාඨමාලාව',
            'Certificate in Applied Computer Technology (CACT)' => 'ප්‍රායෝගික පරිගණක තාක්ෂණය පිළිබඳ සහතිකපත්‍ර පාඨමාලාව (CACT)',
        ];

        foreach ($translations as $english => $sinhala) {
            DB::table('courses')->where('name', $english)->update(['name_si' => $sinhala]);
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('name_si');
        });
    }
};
