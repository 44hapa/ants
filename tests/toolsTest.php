<?php

require_once '../bots/tools.php';
require_once '../bots/bot.php';
require_once 'parent.php';
class ToolsTest extends Test
{

    public function clear()
    {
        Tools::$cols = 0;
        Tools::$rows = 0;
    }

    public function testCreateCoordinate()
    {
        Tools::$cols = 19;
        Tools::$rows = 38;

        $coordinat = Tools::createCoordinate(20);
        $this->assertEquals($coordinat, array('row' => 1, 'col' => 1));

        $coordinat = Tools::createCoordinate(0);
        $this->assertEquals($coordinat, array('row' => 0, 'col' => 0));

        $coordinat = Tools::createCoordinate(21);
        $this->assertEquals($coordinat, array('row' => 1, 'col' => 2));
    }

    public function testCreateNum()
    {
        Tools::$cols = 21;
        Tools::$rows = 45;

        $num = Tools::createNum(15, 17);
        $this->assertEquals(332, $num);

        $num = Tools::createNum(1, 0);
        $this->assertEquals(21, $num);
    }

    public function testDistance()
    {
        Tools::$cols = 51;
        Tools::$rows = 32;
        $a = array('col' => 1,'row' => 1);
        $b = array('col' => 30,'row' => 3);

        $aNum = Tools::createNum($a['row'], $a['col']);
        $bNum = Tools::createNum($b['row'], $b['col']);

        $dist = Tools::distance($a['row'], $a['col'], $b['row'], $b['col']);
        $distNum = Tools::mapDistance($aNum, $bNum);

        $this->assertEquals($dist, $distNum);
        
        $a = array('col' => 1,'row' => 1);
        $b = array('col' => 3,'row' => 28);

        $aNum = Tools::createNum($a['row'], $a['col']);
        $bNum = Tools::createNum($b['row'], $b['col']);

        $dist = Tools::distance($a['row'], $a['col'], $b['row'], $b['col']);
        $distNum = Tools::mapDistance($aNum, $bNum);
        $this->assertEquals($dist, $distNum);
    }


    public function testCreateDirection()
    {
        // w - запад (col)
        // e - восток (col)
        // n - север (row)
        // s - юг (row)

        Tools::$cols = 10;
        Tools::$rows = 10;
        $bot = array('row' => 1, 'col' => 1);

        // Еда расположена нормально
        $food1 = array('row' => 2,'col' => 2);
        $botNum = Tools::createNum($bot['row'], $bot['col']);
        $food1Num = Tools::createNum($food1['row'], $food1['col']);

        $ant = new Bot();
        $ant->currentCoord = $botNum;
//        $ant->gol = $food1Num;

        $dir = Tools::createDirection($ant, $food1Num);
        $this->assertEquals(array('row' => 's', 'col' => 'e'), $dir);

        // Зеркало по иксу (col) - должны переместится на запад (w)
        $food2 = array(1,9);
        $food2Num = Tools::createNum($food2[0], $food2[1]);
        $dir = Tools::createDirection($ant, $food2Num);
        $this->assertEquals(array('row' => 0, 'col' => 'w'), $dir);


        // Зеркало по икс и игрек
        $food3 = array(8,8);
        $food3Num = Tools::createNum($food3[0], $food3[1]);
        $dir = Tools::createDirection($ant, $food3Num);
        $this->assertEquals(array('row' => 'n', 'col' => 'w'), $dir);
    }


    public function testNextStep()
    {
        // w - запад col
        // e - восток col
        // n - север row
        // s - юг row

        Tools::$cols = 11;
        Tools::$rows = 11;
        $bot = array('col' => 0, 'row' => 0);

        $next = Tools::nextStep($bot['col'], $bot['row'], array('col' => 0, 'row' => 's'));
        $this->assertEquals(array('col' => 0, 'row' => 1), $next);

        $next = Tools::nextStep($bot['col'], $bot['row'], array('col' => 0, 'row' => 'n'));
        $this->assertEquals(array('col' => 0, 'row' => 10), $next);


        $bot2 = array('col' => 9, 'row' => 10);

        $next = Tools::nextStep($bot2['col'], $bot2['row'], array('col' => 'w', 'row' => 0));
        $this->assertEquals(array('col' => 8, 'row' => 10), $next);

        $next = Tools::nextStep($bot2['col'], $bot2['row'], array('col' => 'e', 'row' => 0));
        $this->assertEquals(array('col' => 10, 'row' => 10), $next);

        $bot3 = array('col' => 5, 'row' => 5);

        $next = Tools::nextStep($bot3['col'], $bot3['row'], array('col' => 'w', 'row' => 's'));
        $this->assertEquals(array('col' => 4, 'row' => 6), $next);

        $next = Tools::nextStep($bot3['col'], $bot3['row'], array('col' => 'e', 'row' => 'n'));
        $this->assertEquals(array('col' => 6, 'row' => 4), $next);

        $bot4 = array('col' => 10, 'row' => 10);

        $next = Tools::nextStep($bot4['col'], $bot4['row'], array('col' => 'w', 'row' => 's'));
        $this->assertEquals(array('col' => 9, 'row' => 0), $next);

        $next = Tools::nextStep($bot4['col'], $bot4['row'], array('col' => 'e', 'row' => 'n'));
        $this->assertEquals(array('col' => 0, 'row' => 9), $next);
    }
}

$ToolsTest = new ToolsTest();
$ToolsTest->run();