<?php

namespace TechnicPack\SolderClient\Tests;

use PHPUnit\Framework\TestCase;
use TechnicPack\SolderClient\Resources\Build;
use TechnicPack\SolderClient\Resources\Mod;
use TechnicPack\SolderClient\Resources\Modpack;

class DynamicPropertiesTest extends TestCase
{
    public function testMod()
    {
        $props = [
            'name' => 'foo',
            'extra' => 'stuff',
        ];

        $mod = new Mod($props);

        $this->assertTrue(property_exists($mod, 'name'));
        $this->assertFalse(property_exists($mod, 'extra'));

        $this->assertSame('foo', $mod->name);
    }

    public function testModpack()
    {
        $props = [
            'name' => 'foo',
            'extra' => 'stuff',
        ];

        $modpack = new Modpack($props);

        $this->assertTrue(property_exists($modpack, 'name'));
        $this->assertFalse(property_exists($modpack, 'extra'));

        $this->assertSame('foo', $modpack->name);
    }
}
