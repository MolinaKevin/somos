<?php

namespace App\Console\Commands;

use App\Models\L10n;
use Illuminate\Console\Command;

class RestoreTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:restore-translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore the original translations from the backup directory.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        L10n::restoreTranslations();

        $this->info('Translations have been successfully restored.');
        return Command::SUCCESS;
    }
}
