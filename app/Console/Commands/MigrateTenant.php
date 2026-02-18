<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {domain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for tenant schemas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');

        if ($domain) {
            $tenants = Tenant::where('domain', $domain)->get();
        }
        else {
            $tenants = Tenant::all();
        }

        if ($tenants->isEmpty()) {
            $this->error("No tenants found.");
            return 1;
        }

        foreach ($tenants as $tenant) {
            $this->info("Migrating tenant: {$tenant->name} ({$tenant->domain})");

            // Switch search path
            Config::set('database.connections.pgsql.search_path', $tenant->database_name);
            DB::purge('pgsql');

            // We need to run migrations. Since Laravel's migrate command 
            // runs on the default connection, we ensure the default connection 
            // now points to the correct search path.

            $this->call('migrate', [
                '--force' => true,
            ]);

            $this->info("Migration completed for {$tenant->name}");
        }

        return 0;
    }
}
