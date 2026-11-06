<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
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

    #[Test]
    public function a_blade_app_passes_without_installing_react(): void
    {
        $this->app($this->blade_app);

        $this->artisan('manifest:check')
            ->expectsOutputToContain('Everything the packages need is installed.')
            ->assertSuccessful();

        $rows = $this->check();

        foreach (['react', 'react-dom', '@inertiajs/react', '@javaabu/forms'] as $name) {
            $this->assertArrayNotHasKey($name, $rows);
        }
    }

    #[Test]
    public function an_inertia_app_is_asked_for_what_the_react_pages_need(): void
    {
        $this->app([...$this->blade_app, '@inertiajs/react' => '2.1.2']);

        $rows = $this->check();

        foreach (['@javaabu/forms', 'react', 'react-dom'] as $name) {
            $this->assertEquals('missing', $rows[$name]['status'], $name);
            $this->assertEquals(['pages'], $rows[$name]['scopes'], $name);
            $this->assertContains('permissions', $rows[$name]['needed_by'], $name);
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
