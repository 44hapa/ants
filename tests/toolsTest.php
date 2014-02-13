<?php

require_once '../bots/tools.php';
require_once 'parent.php';
class ToolsTest extends Test
{

    public function clear()
    {
        Tools::$cols = 0;
        Tools::$rows = 0;
    }

    public function tQestCreateCoordinate()
    {
        Tools::$cols = 19;
        Tools::$rows = 38;

        $coordinat = Tools::createCoordinate(20);
        $this->assertEquals($coordinat, array(2,1));
    }

    public function testCreateNum()
    {
        Tools::$cols = 21;
        Tools::$rows = 45;

        $num = Tools::createNum(15, 17);
//        $this->assertEquals(311, $num);


        $num = Tools::createNum(1, 0);
        $coordinat = Tools::createCoordinate(21);

        print_r($num);
        echo "\n";
        print_r($coordinat);

        die();

        $coordinat = Tools::createCoordinate(331);
        print_r($coordinat);
        die();


    }

    public function testDistance()
    {
        Tools::$cols = 51;
        Tools::$rows = 32;
        $a = array(1,1);
        $b = array(30,3);
        $aNum = Tools::createNum($a[0], $a[1]);
        $bNum = Tools::createNum($b[0], $b[1]);
        
        $dist = Tools::distance($a[0], $a[1], $b[0], $b[1]);
        $distNum = Tools::mapDistance($aNum, $bNum);

        $this->assertEquals($dist, $distNum);
        
        $a = array(1,1);
        $b = array(3,28);
        $aNum = Tools::createNum($a[0], $a[1]);
        $bNum = Tools::createNum($b[0], $b[1]);

        $dist = Tools::distance($a[0], $a[1], $b[0], $b[1]);
        $distNum = Tools::mapDistance($aNum, $bNum);
        $this->assertEquals($dist, $distNum);
    }

}

$ToolsTest = new ToolsTest();
$ToolsTest->run();