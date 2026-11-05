<?php

namespace Javaabu\Permissions\Listeners\Concerns;

trait LogsChanges
{
    /**
     * Put the before and after values where the installed activitylog expects them:
     * `attribute_changes` on spatie/laravel-activitylog v5, `properties` on v4
     */
    protected function withChanges(mixed $log, array $attributes, array $old): mixed
    {
        $changes = ['attributes' => $attributes, 'old' => $old];

        if (method_exists($log, 'withChanges')) {
            return $log->withChanges($changes);
        }

        return $log->withProperties($changes);
    }
}
