<?php

namespace App\Console\Commands;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncRolePermissionTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-role-permission-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (RoleEnum::cases() as $case) {
            Role::upsert([
                'name' => $case->value,
                'guard_name' => 'api'
            ], ['guard_name', 'name']);
        }

        foreach (PermissionEnum::cases() as $case) {
            Permission::upsert([
                'name' => $case->value,
                'guard_name' => 'api'
            ], ['guard_name', 'name']);
        }

        $user = User::whereEmail('testnuri@gmail.com')->first();

        if ($user) {
            $user->assignRole([RoleEnum::B2B_PANEL, RoleEnum::BACKEND_DEVELOPER]);
        }
    }
}
