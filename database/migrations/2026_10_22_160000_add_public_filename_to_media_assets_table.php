<?php

use App\Services\MediaPublicFilename;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_assets', function (Blueprint $table): void {
            $table->string('public_filename')->nullable()->unique();
        });

        $filenames = app(MediaPublicFilename::class);

        DB::table('media_assets')
            ->select(['id', 'original_name', 'stored_name'])
            ->eachById(function (object $media) use ($filenames): void {
                $name = (string) ($media->original_name ?? $media->stored_name ?? 'file');

                DB::table('media_assets')->where('id', $media->id)->update([
                    'public_filename' => $filenames->unique($name),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table): void {
            $table->dropUnique(['public_filename']);
            $table->dropColumn('public_filename');
        });
    }
};
