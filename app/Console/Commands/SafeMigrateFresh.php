<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Wrapper around migrate:fresh that requires explicit confirmation
 * and automatically backs up the database first.
 */
class SafeMigrateFresh extends Command
{
    protected $signature   = 'db:fresh {--seed : Run seeders after migration}';
    protected $description = 'Safe migrate:fresh — backs up DB first and asks for confirmation';

    public function handle(): int
    {
        $this->warn('⚠️  This will DROP all tables and recreate them.');
        $this->warn('   All existing data will be permanently deleted.');
        $this->newLine();

        if (! $this->confirm('Are you sure? Type yes to continue')) {
            $this->info('Aborted.');
            return 0;
        }

        // Auto-backup before wiping
        $this->info('Creating backup...');
        $dir  = storage_path('backups/db');
        @mkdir($dir, 0755, true);
        $file = $dir . '/pre_fresh_' . now()->format('YmdHis') . '.sql';

        $socket = config('database.connections.mysql.unix_socket');
        $db     = config('database.connections.mysql.database');
        $user   = config('database.connections.mysql.username');
        $cmd    = "/Applications/XAMPP/xamppfiles/bin/mysqldump"
            . ($socket ? " --socket={$socket}" : '')
            . " -u {$user} {$db} > {$file}";

        exec($cmd, $out, $code);

        if ($code === 0) {
            $this->info("✓ Backup saved: {$file}");
        } else {
            $this->warn('Backup failed — proceeding anyway.');
        }

        $this->newLine();
        $this->call('migrate:fresh', $this->option('seed') ? ['--seed' => true] : []);

        return 0;
    }
}
