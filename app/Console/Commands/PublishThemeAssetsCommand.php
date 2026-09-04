<?php

namespace App\Console\Commands;

use App\Services\ThemeAssetPublisher;
use Illuminate\Console\Command;
use RuntimeException;

final class PublishThemeAssetsCommand extends Command
{
    /** @var string */
    protected $signature = 'awcms:theme:publish-assets {theme : Installed theme slug}';

    /** @var string */
    protected $description = 'Safely publish an installed AWCMS theme assets directory.';

    public function handle(ThemeAssetPublisher $publisher): int
    {
        $theme = trim(
            $this->argument('theme'),
        );

        if ($theme === '') {
            $this->error('A valid theme slug is required.');

            return self::FAILURE;
        }

        try {
            $published = $publisher->publish($theme);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Published %d asset file(s) for theme [%s].',
            $published,
            $theme,
        ));

        return self::SUCCESS;
    }
}
