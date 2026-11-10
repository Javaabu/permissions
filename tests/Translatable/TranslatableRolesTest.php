<?php

namespace Javaabu\Permissions\Tests\Translatable;

use Inertia\ServiceProvider as InertiaServiceProvider;
use Inertia\Testing\AssertableInertia;
use Javaabu\Permissions\Models\Translatable\Role;
use Javaabu\Permissions\Support\Translations;
use Javaabu\Permissions\Tests\Http\InteractsWithRoleRoutes;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\TestCase;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Testing\InteractsWithLanguages;
use Javaabu\Translatable\TranslatableServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('translatable')]
class TranslatableRolesTest extends TestCase
{
    use InteractsWithDatabase;
    use InteractsWithLanguages;
    use InteractsWithRoleRoutes;

    protected bool $flush_registry = false;

    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), TranslatableServiceProvider::class, InertiaServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('permission.models.role', Role::class);
        $app['config']->set('permissions.guards', ['web', 'web_admin']);
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLanguages(['en', 'dv']);
        $this->setUpRoleRoutes();
    }

    #[Test]
    public function it_creates_a_role_with_a_description_in_each_language(): void
    {
        $this->actingAs($this->admin(), 'web_admin')
            ->postJson('/admin/roles', [
                'name' => 'editor',
                'description_en' => 'Editors',
                'description_dv' => 'ލިޔުންތައް ބަދަލުކުރާ މީހުން',
            ])
            ->assertCreated()
            ->assertJsonPath('data.description', 'Editors')
            ->assertJsonPath('data.translations.description_dv', 'ލިޔުންތައް ބަދަލުކުރާ މީހުން');

        $role = Role::findByName('editor', 'web_admin');

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('ލިޔުންތައް ބަދަލުކުރާ މީހުން', $role->translate('description', 'dv', false));

        app()->setLocale('dv');
        Languages::setCurrentLocale('dv');

        $this->assertEquals('ލިޔުންތައް ބަދަލުކުރާ މީހުން', $role->fresh()->translated_description);
    }

    #[Test]
    public function the_primary_description_is_required_and_the_others_are_not(): void
    {
        $this->actingAs($this->admin(), 'web_admin')
            ->postJson('/admin/roles', ['name' => 'editor', 'description_dv' => 'ލިޔުންތައް ބަދަލުކުރާ މީހުން'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['description_en'])
            ->assertJsonMissingValidationErrors(['description_dv', 'description']);

        $this->actingAs($this->admin(), 'web_admin')
            ->postJson('/admin/roles', ['name' => 'editor', 'description_en' => 'Editors'])
            ->assertCreated();
    }

    #[Test]
    public function the_blade_form_has_an_input_per_language(): void
    {
        $role = Role::query()->create(['name' => 'editor', 'description' => 'Editors', 'guard_name' => 'web_admin']);
        $role->addTranslation('dv', 'description', 'ލިޔުންތައް ބަދަލުކުރާ މީހުން')->save();

        $content = $this->actingAs($this->admin(), 'web_admin')
            ->get("/admin/roles/{$role->id}/edit")
            ->assertOk()
            ->assertSee('name="description_en"', false)
            ->assertSee('name="description_dv"', false)
            ->assertSee('ލިޔުންތައް ބަދަލުކުރާ މީހުން')
            ->assertDontSee('name="description"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/<input[^>]*name="description_dv"[^>]*dir="rtl"|<input[^>]*dir="rtl"[^>]*name="description_dv"/', $content);
    }

    #[Test]
    public function the_inertia_form_gets_the_inputs_and_values(): void
    {
        config()->set('permissions.views', 'inertia');

        $this->actingAs($this->admin(), 'web_admin')
            ->get('/admin/roles/create')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('permissions::Roles/Create')
                ->has('translations.inputs.description', 2)
                ->where('translations.inputs.description.0.name', 'description_en')
                ->where('translations.inputs.description.0.primary', true)
                ->where('translations.inputs.description.1.name', 'description_dv')
                ->where('translations.inputs.description.1.thaana', true)
                ->where('translations.inputs.description.1.direction', 'rtl')
                ->where('translations.values', []));
    }

    #[Test]
    public function it_uses_the_plain_description_with_translatable_switched_off(): void
    {
        $this->withoutTranslatable(function () {
            $this->assertFalse(Translations::supportsRole());

            $this->actingAs($this->admin(), 'web_admin')
                ->postJson('/admin/roles', ['name' => 'viewer', 'description' => 'ބަލާ މީހުން'])
                ->assertCreated()
                ->assertJsonPath('data.description', 'ބަލާ މީހުން')
                ->assertJsonMissingPath('data.translations');

            $this->actingAs($this->admin(), 'web_admin')
                ->get('/admin/roles/create')
                ->assertOk()
                ->assertSee('name="description"', false)
                ->assertDontSee('name="description_dv"', false);
        });
    }
}
