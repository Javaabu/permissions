<?php

namespace Javaabu\Permissions\Models\Translatable;

use Javaabu\Permissions\Models\Role as BaseRole;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

/**
 * A role whose description can be translated with javaabu/translatable
 *
 * Point `permission.models.role` at this class (or a subclass of it) to use it. PHP only
 * loads it when it's referenced, so it's safe to ship without translatable installed.
 */
class Role extends BaseRole implements JsonTranslatable
{
    use IsJsonTranslatable;

    /** @var array<int, string> */
    protected array $translatable = ['description'];

    /**
     * Keep morph types (activity log subjects, morph maps) pointing at the base role
     */
    public function getMorphClass(): string
    {
        $morph_class = parent::getMorphClass();

        return $morph_class === static::class
            ? (new BaseRole)->getMorphClass()
            : $morph_class;
    }
}
