<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Services\PageRevisionService;
use Illuminate\Console\Command;

final class BackfillPageRevisionsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature =
        'awcms:pages:backfill-revisions
        {--dry-run : Show pages without creating revisions}';

    /**
     * @var string
     */
    protected $description =
        'Create an initial revision for pages without revision history.';

    public function handle(
        PageRevisionService $revisionService,
    ): int {
        $dryRun = (bool) $this->option(
            'dry-run',
        );

        $created = 0;
        $skipped = 0;

        Page::withTrashed()
            ->orderBy('id')
            ->chunkById(
                100,
                function ($pages) use (
                    $revisionService,
                    $dryRun,
                    &$created,
                    &$skipped,
                ): void {
                    foreach ($pages as $page) {
                        if (
                            $page->revisions()
                                ->exists()
                        ) {
                            $skipped++;

                            continue;
                        }

                        if ($dryRun) {
                            $this->line(
                                sprintf(
                                    'Would create revision for page #%d: %s',
                                    $page->id,
                                    $page->title,
                                ),
                            );

                            $created++;

                            continue;
                        }

                        $revisionService->capture(
                            page: $page,
                            actor: null,
                            summary: 'Initial system revision snapshot.',
                        );

                        $created++;
                    }
                },
            );

        $this->newLine();

        $this->info(
            sprintf(
                '%s %d initial revision(s).',
                $dryRun
                    ? 'Would create'
                    : 'Created',
                $created,
            ),
        );

        $this->line(
            sprintf(
                'Skipped %d page(s) with existing revisions.',
                $skipped,
            ),
        );

        return self::SUCCESS;
    }
}
