<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('label', 150);
            $table->string('url', 2048)->nullable();
            $table->timestamps();

            $table->unique(['menu_item_id', 'locale']);
            $table->index('locale');
        });

        DB::table('menu_items')
            ->select(['id', 'label', 'url', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(200, static function ($items): void {
                /** @var Collection<int, object{id: int, label: string, url: string|null, created_at: mixed, updated_at: mixed}> $items */
                $translations = [];

                foreach ($items as $item) {
                    $translations[] = [
                        'menu_item_id' => $item->id,
                        'locale' => 'en',
                        'label' => $item->label,
                        'url' => $item->url,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }

                if ($translations !== []) {
                    DB::table('menu_item_translations')->insert($translations);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_translations');
    }
};
