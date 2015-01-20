<?php
/**
 * Created by PhpStorm.
 * User: guru
 * Date: 13.02.14
 * Time: 20:48
 */

require_once '../bots/tools.php';
require_once '../bots/bot.php';
require_once '../bots/bots.php';
require_once '../bots/prioritets.php';
require_once 'parent.php';
class PrioritetsTest extends Test
{

    public function testDeterminateBests()
    {

        // Зададим размеры карты
        Steamer::$cols = 30;
        Steamer::$rows = 30;
        // Координаты для ботов
        $coordAnt1 = array(1,13);
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
        // Засунем в БОТлист
        $botList = Bots::getInstance();
        $botList->add($ant1);
        $botList->add($ant2);

        Tools::$food [$mapFood1] = array($coordFood1[0], $coordFood1[1]);

        // Определим приоритетные точки;
        $ant1->fillAndReturnPrioritiPoints();
        $ant2->fillAndReturnPrioritiPoints();
//print_r($botList->getList());
//print_r($ant1);
//        die();
        // Добавимся в лист приоритетов
        $prioritets = Prioritets::getInstance();
        $prioritets->add($ant1);
        $prioritets->add($ant2);

        // Раздадиим приоритеты муравьям
        $prioritets->determinateBests();
//        die();
//        print_r($botList);
//        print_r($ant1);
//        die();

//        var_dump($prioritets->getList());
//        echo "\n==============================\n";
//        echo "\n==============================\n";


//        $ant1->prioritiZone = null;
//        $ant2->prioritiZone = null;
//        var_dump($ant1);
//        var_dump($ant2);
//        die();

    }

}

$PrioritetsTest = new PrioritetsTest();
$PrioritetsTest->run($argv);