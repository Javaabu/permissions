<?php

namespace Javaabu\Permissions\Commands;

use Illuminate\Console\Command;
use Javaabu\Permissions\Sync\PermissionSyncer;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync
                            {--guard=* : Only sync these guards}
                            {--prune : Delete permissions in registered groups that are no longer registered}';

    protected $description = 'Create or update the registered permissions and roles, and grant the default permissions';

    public function handle(PermissionSyncer $syncer): int
    {
        $this->info('Syncing permissions...');

        $result = $syncer->sync($this->option('guard') ?: null, (bool) $this->option('prune'));

        foreach ($result->created_permissions as $permission) {
            $this->line("Created permission <comment>{$permission}</comment>");
        }

        foreach ($result->updated_permissions as $permission) {
            $this->line("Updated permission <comment>{$permission}</comment>");
        }

        foreach ($result->pruned_permissions as $permission) {
            $this->line("Deleted permission <comment>{$permission}</comment>");
        }

        foreach ($result->created_roles as $role) {
            $this->line("Created role <comment>{$role}</comment>");
        }

        foreach ($result->skipped_grants as $role) {
            $this->warn("Skipped grants to <comment>{$role}</comment>: the role doesn't exist");
        }

        $this->comment(sprintf(
            'Done: %d permissions created, %d updated, %d deleted; %d roles created.',
            count($result->created_permissions),
            count($result->updated_permissions),
            count($result->pruned_permissions),
            count($result->created_roles),
        ));

        return self::SUCCESS;
    }
}
