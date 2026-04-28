<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyTriggers extends Command
{
    protected $signature   = 'db:apply-triggers';
    protected $description = 'Apply PostgreSQL trigger definitions from database/sql/stock_cache_trigger.sql';

    public function handle(): int
    {
        $path = database_path('sql/stock_cache_trigger.sql');

        if (! file_exists($path)) {
            $this->error("SQL file not found: {$path}");
            return self::FAILURE;
        }

        $sql = file_get_contents($path);

        if ($sql === false || trim($sql) === '') {
            $this->error('SQL file is empty or unreadable.');
            return self::FAILURE;
        }

        DB::unprepared($sql);

        $this->info('Triggers applied successfully from: ' . $path);
        return self::SUCCESS;
    }
}
