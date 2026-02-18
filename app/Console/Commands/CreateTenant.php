<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name} {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant and its database schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $schemaName = strtolower(preg_replace('/[^a-z0-9_]/', '', $name));

        $this->info("Creating tenant: {$name} ({$domain})");

        // 1. Create Schema in PostgreSQL
        try {
            DB::statement("CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\"");
            $this->info("Schema '{$schemaName}' created successfully.");
        }
        catch (\Exception $e) {
            $this->error("Failed to create schema: " . $e->getMessage());
            return 1;
        }

        // 2. Save Tenant record
        $tenant = Tenant::create([
            'name' => $name,
            'domain' => $domain,
            'database_name' => $schemaName,
        ]);

        $this->info("Tenant record created with ID: {$tenant->id}");

        // 3. (Optional) Run migrations for this tenant
        if ($this->confirm('Do you want to run migrations for this tenant?', true)) {
            // This would require a more complex migration runner for schemas
            // For now, we suggest manual or dynamic runner
            $this->info("Run: php artisan tenant:migrate {$domain}");
        }

        return 0;
    }
}
