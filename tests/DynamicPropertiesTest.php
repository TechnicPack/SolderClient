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
            'id' => 1,
            'extra' => 'stuff',
        ];

        $mod = new Mod($props);

        $this->assertTrue(property_exists($mod, 'id'));
        $this->assertFalse(property_exists($mod, 'extra'));

        $this->assertEquals(1, $mod->id);
    }

    public function testModpack()
    {
        $props = [
            'id' => 1,
            'extra' => 'stuff',
        ];

        $modpack = new Modpack($props);

        $this->assertTrue(property_exists($modpack, 'id'));
        $this->assertFalse(property_exists($modpack, 'extra'));

        $this->assertEquals(1, $modpack->id);
    }
}
