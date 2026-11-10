<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Javaabu\Manifest\Enums\NpmScopes;
use Javaabu\Manifest\Manifest;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ManifestCheckTest extends TestCase
{
    /**
     * What javaabu/forms' Blade assets and the Vite build need; permissions' Blade script adds nothing
     */
    protected array $blade_app = [
        '@javaabu/onu' => '0.2.1',
        'flatpickr' => '4.6.13',
        'laravel-vite-plugin' => '2.0.1',
        'tailwindcss' => '4.1.12',
        'tom-select' => '2.4.3',
        'vite' => '7.1.3',
    ];

    protected function tearDown(): void
    {
        File::delete(base_path('package.json'));
        File::deleteDirectory(base_path('node_modules'));

        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $installed
     */
    protected function app(array $installed): void
    {
        File::put(base_path('package.json'), json_encode([
            'devDependencies' => (object) array_map(fn ($version) => str_starts_with($version, 'github:') ? $version : "^{$version}", $installed),
        ]));

        foreach ($installed as $name => $version) {
            File::ensureDirectoryExists(base_path("node_modules/{$name}"));
            File::put(base_path("node_modules/{$name}/package.json"), json_encode(['name' => $name, 'version' => str_starts_with($version, 'github:') ? '0.1.0' : $version]));
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function check(array $options = []): array
    {
        Artisan::call('manifest:check', ['--json' => true, ...$options]);

        return collect(json_decode(Artisan::output(), true))->keyBy('name')->all();
    }

    /**
     * The rows permissions is one of the packages asking for
     *
     * @return array<string, array<string, mixed>>
     */
    protected function rowsNeededByPermissions(): array
    {
        return collect($this->check())
            ->filter(fn (array $row) => in_array('permissions', $row['needed_by'], true))
            ->all();
    }

    #[Test]
    public function a_blade_app_is_not_asked_for_react_by_permissions(): void
    {
        $this->app($this->blade_app);

        // javaabu/forms' Blade assets are checked too and need react for its Thaana keyboard
        // (Javaabu/onu#62); only what permissions asks for is under test here
        $rows = $this->rowsNeededByPermissions();

        foreach (['react', 'react-dom', '@inertiajs/react', '@javaabu/forms'] as $name) {
            $this->assertArrayNotHasKey($name, $rows);
        }

        foreach ($rows as $name => $row) {
            $this->assertEquals('ok', $row['status'], $name);
        }
    }

    #[Test]
    public function an_inertia_app_is_asked_for_what_the_react_pages_need(): void
    {
        $this->app([...$this->blade_app, '@inertiajs/react' => '2.1.2']);

        $rows = $this->check();

        foreach (['@javaabu/forms', 'react', 'react-dom'] as $name) {
            $this->assertEquals('missing', $rows[$name]['status'], $name);
            $this->assertContains('pages', $rows[$name]['scopes'], $name);
            $this->assertContains('permissions', $rows[$name]['needed_by'], $name);
        }

        // rows merge every package's scopes (javaabu/forms asks for react in assets), so check
        // permissions' own declaration: the React packages are for the pages only
        $package = app(Manifest::class)->package('permissions');
        $for_assets = [...$package->npmDependencies(NpmScopes::ASSETS), ...$package->npmPeerDependencies(NpmScopes::ASSETS)];
        $for_pages = [...$package->npmDependencies(NpmScopes::PAGES), ...$package->npmPeerDependencies(NpmScopes::PAGES)];

        foreach (['@javaabu/forms', 'react', 'react-dom', '@inertiajs/react'] as $name) {
            $this->assertArrayNotHasKey($name, $for_assets, $name);
            $this->assertArrayHasKey($name, $for_pages, $name);
        }

        $this->assertEquals(['^2.0 || ^3.0'], $rows['@inertiajs/react']['constraints']);
        $this->assertContains('pages', $rows['@javaabu/onu']['scopes']);
    }

    #[Test]
    public function an_inertia_app_with_the_react_packages_passes(): void
    {
        $this->app([
            ...$this->blade_app,
            '@inertiajs/react' => '2.1.2',
            '@javaabu/forms' => 'github:Javaabu/react-forms#next-gen',
            'react' => '19.1.1',
            'react-dom' => '19.1.1',
        ]);

        $this->artisan('manifest:check')->assertSuccessful();
    }
}
