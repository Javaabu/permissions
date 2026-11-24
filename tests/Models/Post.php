<?php

namespace Javaabu\Permissions\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Post extends Model
{
    protected $guarded = [];

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function isOwnedBy(Model $user): bool
    {
        return $this->author_type === $user->getMorphClass()
            && (int) $this->author_id === (int) $user->getKey();
    }
}
