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
        Tools::$cols = 20;
        Tools::$rows = 20;
        $step = 2;
        // Координаты для ботов
        $coordAnt1 = array(20,20);
        $coordAnt2 = array(1,17);
        $mapAnt1 = Tools::createNum($coordAnt1[0], $coordAnt1[1]);
        $mapAnt2 = Tools::createNum($coordAnt2[0], $coordAnt2[1]);
        // Координаты еды
        $coordFood1 = array(1,15);
        $coordFood2 = array(2,15);
        $mapFood1 = Tools::createNum($coordFood1[0], $coordFood1[1]);
        $mapFood2 = Tools::createNum($coordFood2[0], $coordFood2[1]);

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

        Tools::$food [$mapFood1] = array($coordFood1[0], $coordFood1[1]);

        // Определим зону приоритета
        $prioritiZone1 = $ant1->getPrioritiZone();
        $zone = array (
            358 => '18:18',
            359 => '18:19',
            360 => '18:20',
            341 => '18:1',
            342 => '18:2',
            378 => '19:18',
            379 => '19:19',
            380 => '19:20',
            361 => '19:1',
            362 => '19:2',
            398 => '20:18',
            399 => '20:19',
            400 => '20:20',
            381 => '20:1',
            382 => '20:2',
            18 => '1:18',
            19 => '1:19',
            20 => '1:20',
            1 => '1:1',
            2 => '1:2',
            38 => '2:18',
            39 => '2:19',
            40 => '2:20',
            21 => '2:1',
            22 => '2:2',
        );
        $this->assertEquals($prioritiZone1, $zone);
//        var_export($prioritiZone1);
//        die();

    }
}

$botTest = new BotTest();
$botTest->run();