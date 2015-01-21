<?php
/**
 * Created by PhpStorm.
 * User: guru
 * Date: 13.02.14
 * Time: 23:56
 */

require_once '../bots/tools.php';
require_once '../bots/bot.php';
require_once '../bots/bots.php';
require_once '../bots/steamer.php';
require_once 'parent.php';

class BotTest extends Test
{

    public function testGetPrioritiZone()
    {
        // Зададим размеры карты
        Steamer::$cols = 20; // количество клеток по горизонтали
        Steamer::$rows = 20; // количество клеток по вертикали
        $step = 2;
        // Координаты для ботов
        $coordAnt1 = array('row' => 19,'col' => 19);
        $coordAnt2 = array('row' => 1,'col' => 17);
        $mapAnt1 = Tools::createNum($coordAnt1['row'], $coordAnt1['col']);
        $mapAnt2 = Tools::createNum($coordAnt2['row'], $coordAnt2['col']);

        // Координаты еды
        $coordFood1 = array('row' => 1,'col' => 15);
        $coordFood2 = array('row' => 2,'col' => 15);
        $mapFood1 = Tools::createNum($coordFood1['row'], $coordFood1['col']);
        $mapFood2 = Tools::createNum($coordFood2['row'], $coordFood2['col']);

        $ant1 = new Bot();
        $ant2 = new Bot();

        // Поместим на карту
        $ant1->currentCoord = $mapAnt1;
        $ant2->currentCoord = $mapAnt2;
        // Зададим расстояние приоритета
        $ant1->stepPrior = $step;
        $ant2->stepPrior = $step;
        // Засунем в БОТлист
        $botList = Bots::getInstance();
        $botList->add($ant1);
        $botList->add($ant2);

        Steamer::$food [$mapFood1] = array('row' => $coordFood1['row'], 'col' => $coordFood1['col']);

        // Определим зону приоритета
        $prioritiZone1 = $ant1->getPrioritiZone();

        $zone = array(
            357 => 'col=17: row=17',
            377 => 'col=17: row=18',
            397 => 'col=17: row=19',
            17 => 'col=17: row=0',
            37 => 'col=17: row=1',
            358 => 'col=18: row=17',
            378 => 'col=18: row=18',
            398 => 'col=18: row=19',
            18 => 'col=18: row=0',
            38 => 'col=18: row=1',
            359 => 'col=19: row=17',
            379 => 'col=19: row=18',
            399 => 'col=19: row=19',
            19 => 'col=19: row=0',
            39 => 'col=19: row=1',
            340 => 'col=0: row=17',
            360 => 'col=0: row=18',
            380 => 'col=0: row=19',
            0 => 'col=0: row=0',
            20 => 'col=0: row=1',
            341 => 'col=1: row=17',
            361 => 'col=1: row=18',
            381 => 'col=1: row=19',
            1 => 'col=1: row=0',
            21 => 'col=1: row=1',
        );
        $this->assertEquals($prioritiZone1, $zone);
//        var_export($prioritiZone1);
//        die();

    }


    public function testFillAndReturnPrioritiPoints()
    {
        //Настроим карту
        // Зададим размеры карты
        Steamer::$cols = 50; // количество клеток по горизонтали
        Steamer::$rows = 50; // количество клеток по вертикали
        $step = 2;
        // Координаты для ботов
        $coordAnt1 = array('row' => 19,'col' => 19);
        $mapAnt1 = Tools::createNum($coordAnt1['row'], $coordAnt1['col']);

        // Координаты дома
        $home = array('row' => 1,'col' => 15);
        $mapHome = Tools::createNum($home['row'], $home['col']);
        // Зальем карту
        $maxCel = Steamer::$rows * Steamer::$cols;
        Steamer::$staticMap = array_pad(array(0), $maxCel -1, UNSEEN);
        //Поместим дом на карту
        Steamer::$home = array($mapHome);
        $ant1 = new Bot();


        // Поместим на карту
        $ant1->currentCoord = $mapAnt1;
        // Зададим расстояние приоритета
        $ant1->stepPrior = $step;
        // Засунем в БОТлист
        $botList = Bots::getInstance();
        $botList->add($ant1);

        dd($ant1->fillAndReturnPrioritiPoints());

//        Steamer::$food [$mapFoыod1] = array('row' => $coordFood1['row'], 'col' => $coordFood1['col']);

    }
}

$botTest = new BotTest();
$botTest->run($argv);