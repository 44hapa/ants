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
require_once 'parent.php';

class BotTest extends Test
{

    public function testGetPrioritiZone()
    {
        // Зададим размеры карты
        Tools::$cols = 20; // количество клеток по горизонтали
        Tools::$rows = 20; // количество клеток по вертикали
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

        Tools::$food [$mapFood1] = array('row' => $coordFood1['row'], 'col' => $coordFood1['col']);

        // Определим зону приоритета
        $prioritiZone1 = $ant1->getPrioritiZone();

        $zone = array (
            357 => '17:17',
            358 => '17:18',
            359 => '17:19',
            340 => '17:0',
            341 => '17:1',
            377 => '18:17',
            378 => '18:18',
            379 => '18:19',
            360 => '18:0',
            361 => '18:1',
            397 => '19:17',
            398 => '19:18',
            399 => '19:19',
            380 => '19:0',
            381 => '19:1',
            17 => '0:17',
            18 => '0:18',
            19 => '0:19',
            0 => '0:0',
            1 => '0:1',
            37 => '1:17',
            38 => '1:18',
            39 => '1:19',
            20 => '1:0',
            21 => '1:1',
        );
        $this->assertEquals($prioritiZone1, $zone);
//        var_export($prioritiZone1);
//        die();

    }
}

$botTest = new BotTest();
$botTest->run();