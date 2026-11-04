<?php

namespace Javaabu\Permissions\Tests\Feature;

use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TranslationsTest extends TestCase
{
    #[Test]
    public function every_string_in_the_source_has_an_english_and_a_dhivehi_line(): void
    {
        $en = json_decode(file_get_contents(__DIR__ . '/../../lang/en.json'), true);
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);

        $keys = $this->sourceKeys();

        $this->assertNotEmpty($keys);

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $en, "lang/en.json is missing [{$key}]");
            $this->assertArrayHasKey($key, $dv, "lang/dv.json is missing [{$key}]");
            $this->assertMatchesRegularExpression('/[\x{0780}-\x{07BF}]/u', $dv[$key], "[{$key}] isn't in Thaana");
        }

        $this->assertEqualsCanonicalizing(array_keys($en), array_keys($dv));
    }

    #[Test]
    public function the_package_strings_are_shown_in_dhivehi(): void
    {
        $this->app->setLocale('dv');

        $this->assertEquals('ރޯލް އުފެއްދިއްޖެ', __('Role created'));
        $this->assertEquals("'އެޑިޓަރ' އާ ގުޅޭ ރޯލްތައް", __('Roles matching \':search\'', ['search' => 'އެޑިޓަރ']));
    }

    /** @return array<int, string> */
    protected function sourceKeys(): array
    {
        $keys = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../src'));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            preg_match_all("/__\\('((?:[^'\\\\]|\\\\.)+)'/", file_get_contents($file->getPathname()), $matches);

            foreach ($matches[1] as $key) {
                $keys[] = stripslashes($key);
            }
        }

        return array_values(array_unique($keys));
    }
}
