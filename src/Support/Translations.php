<?php

namespace Javaabu\Permissions\Support;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\LocaleInput;
use Javaabu\Translatable\Translatable;
use Spatie\Permission\PermissionRegistrar;

/**
 * Optional javaabu/translatable support, for role descriptions
 *
 * Every call is safe without translatable installed.
 */
class Translations
{
    /** The role fields that can be translated */
    public const array FIELDS = ['description'];

    public static function enabled(): bool
    {
        return class_exists(Translatable::class) && Translatable::enabled();
    }

    public static function supports(object|string|null $model, ?string $field = null): bool
    {
        return class_exists(Translatable::class) && Translatable::supports($model, $field);
    }

    /**
     * Whether the role's description (or, for a new role, the role class's) is translated
     */
    public static function supportsRole(?Model $role = null): bool
    {
        return static::supports($role ?? app(PermissionRegistrar::class)->getRoleClass(), 'description');
    }

    /**
     * Expand the description rules into one rule per locale when the role is translated
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    public static function rules(?Model $role, array $rules): array
    {
        if (! static::supportsRole($role)) {
            return $rules;
        }

        return Translatable::rules($role ?? app(PermissionRegistrar::class)->getRoleClass(), $rules);
    }

    /**
     * The description in every locale, keyed by input name: `['description_en' => …, 'description_dv' => …]`
     *
     * @return array<string, mixed>
     */
    public static function values(?Model $role): array
    {
        if (! $role || ! static::supportsRole($role)) {
            return [];
        }

        return Translatable::values($role, static::FIELDS);
    }

    /**
     * What the role form needs to render one description input per locale, or null when
     * descriptions aren't translated
     *
     * @return array{values: array<string, mixed>, inputs: array<string, array<int, array<string, mixed>>>}|null
     */
    public static function form(?Model $role): ?array
    {
        if (! static::supportsRole($role)) {
            return null;
        }

        $model = $role ?? app(PermissionRegistrar::class)->getRoleClass();

        return [
            'values' => static::values($role),
            'inputs' => [
                'description' => Translatable::inputs('description', $model)
                    ->map(fn (LocaleInput $input) => [
                        ...$input->toArray(),
                        'label' => $input->label(__('Description')),
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    }
}
