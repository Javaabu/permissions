<?php

namespace Javaabu\Permissions\Tests\Feature;

use Javaabu\Manifest\Manifest;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FrontendAssetsTest extends TestCase
{
    #[Test]
    public function it_registers_its_pages_and_script_with_manifest(): void
    {
        $manifest = app(Manifest::class);

        $this->assertTrue($manifest->has('permissions::roles'));

        $this->artisan('manifest:list', ['package' => 'permissions'])->assertSuccessful();
    }

    #[Test]
    public function every_page_the_controller_renders_exists(): void
    {
        foreach (['Index', 'Create', 'Edit', 'Show'] as $page) {
            $this->assertFileExists(__DIR__ . "/../../resources/js/Pages/Roles/{$page}.tsx");
            $this->assertFileExists(__DIR__ . '/../../resources/views/roles/' . strtolower($page) . '.blade.php');
        }
    }
}
