<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the EventSquare seed (database/eventsquare-seed.sql).
     */
    public function run(): void
    {
        // Optional schema name from environment (e.g., DB_SCHEMA=lbaw2536)
        $schema = env('DB_SCHEMA');

        if ($schema !== null) {
            // Expose schema name to SQL via app.schema (used in DO block)
            DB::statement("SELECT set_config('app.schema', ?, false)", [$schema]);
        }

        // Load and execute EventSquare SQL seed
        $path = database_path('eventsquare-seed.sql');
        $sql  = file_get_contents($path);

        DB::unprepared($sql);

        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('\"user\"', 'id_user'),
                (SELECT COALESCE(MAX(id_user), 1) FROM \"user\")
            )
        ");

        $this->command?->info('Database seeded using EventSquare schema: ' . ($schema ?? 'lbaw2536'));
    }
}
