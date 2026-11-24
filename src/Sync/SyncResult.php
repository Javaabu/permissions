<?php

namespace Javaabu\Permissions\Sync;

class SyncResult
{
    /** @var array<int, string> `guard:name` */
    public array $created_permissions = [];

    /** @var array<int, string> `guard:name` */
    public array $updated_permissions = [];

    /** @var array<int, string> `guard:name` */
    public array $pruned_permissions = [];

    /** @var array<int, string> `guard:name` */
    public array $created_roles = [];

    /** @var array<int, string> `guard:name` */
    public array $updated_roles = [];

    /** @var array<int, string> `guard:role`, for grants to roles that don't exist */
    public array $skipped_grants = [];
}
