<?php
/**
 * Created by PhpStorm.
 * User: guru
 * Date: 16.02.14
 * Time: 13:23
 */

require_once '../bots/tools.php';
require_once '../bots/steamer.php';
require_once '../bots/bot.php';
require_once '../bots/bots.php';
require_once '../bots/prioritets.php';
require_once 'parent.php';

class BotsTest extends Test
{

    public function t1estSelectMove1()
    {
        // Размер карты
        Tools::$cols = 20;
        Tools::$rows = 20;

        // Заливкм карты
        $maxCel = Tools::$rows * Tools::$cols;
        Steamer::$staticMap = array_pad(array(0), $maxCel -1, UNSEEN);

        // Еда вокруг ботов
        $food1Coordinat = array('col' => 13, 'row' =>1);
        $food2Coordinat = array('col' => 16, 'row' =>1);

        $food1Num = Tools::createNum($food1Coordinat['row'], $food1Coordinat['col']);
        $food2Num = Tools::createNum($food2Coordinat['row'], $food2Coordinat['col']);

        $ant1Coordinat = array('col' => 14,'row' => 1);
        $ant2Coordinat = array('col' => 15,'row' => 1);

        $ant1Num = Tools::createNum($ant1Coordinat['row'], $ant1Coordinat['col']);
        $ant2Num = Tools::createNum($ant2Coordinat['row'], $ant2Coordinat['col']);

        // Создадим ботов
        $ant1 = new Bot();
        $ant1->coordinatColRow = $ant1Coordinat;
        $ant1->currentCoord = $ant1Num;

        $ant2 = new Bot();
        $ant2->coordinatColRow = $ant2Coordinat;
        $ant2->currentCoord = $ant2Num;


        //  Список ботов
        $antList = Bots::getInstance();
        $antList->add($ant1);
        $antList->add($ant2);

        // Добавим цели (что бы пути пересеклись).
        $ant1->setGol($food2Num);
        $ant2->setGol($food1Num);

        // Выберем направление движения
        $directionArray1 = Tools::createDirection($ant1);
        $directionArray2 = Tools::createDirection($ant2);

        // w - запад col
        // e - восток col
        // n - север row
        // s - юг row

        // Идет на право (восток)
        $this->assertEquals($directionArray1, array('row' => 0, 'col' => 'e'));
        // Идет на лево (запад)
        $this->assertEquals($directionArray2, array('row' => 0, 'col' => 'w'));


        // Определим дальнейшее поведение при выборе реального пути
        $move1 = $antList->selectMove($ant1, $directionArray1);
        $move2 = $antList->selectMove($ant2, $directionArray2);
        // Они разминулись!!!
        $this->assertEquals('e', $move1);
        $this->assertEquals('w', $move2);



    }

    public function testSelectMove2()
    {
        // Размер карты
        Tools::$cols = 20;
        Tools::$rows = 20;

        // Заливкм карты
        $maxCel = Tools::$rows * Tools::$cols;
        Steamer::$staticMap = array_pad(array(0), $maxCel -1, UNSEEN);

        //========================== СТОЛКНОВЕНИЕ ===============================

        // Еда вокруг ботов
        $food3Coordinat = array('col' => 12, 'row' =>1);
        $food4Coordinat = array('col' => 16, 'row' =>1);

        $food3Num = Tools::createNum($food3Coordinat['row'], $food3Coordinat['col']);
        $food4Num = Tools::createNum($food4Coordinat['row'], $food4Coordinat['col']);

        $ant3Coordinat = array('col' => 13,'row' => 1);
        $ant4Coordinat = array('col' => 15,'row' => 1);

        $ant3Num = Tools::createNum($ant3Coordinat['row'], $ant3Coordinat['col']);
        $ant4Num = Tools::createNum($ant4Coordinat['row'], $ant4Coordinat['col']);

        // Создадим ботов
        $ant3 = new Bot();
        $ant3->coordinatColRow = $ant3Coordinat;
        $ant3->currentCoord = $ant3Num;

        $ant4 = new Bot();
        $ant4->coordinatColRow = $ant4Coordinat;
        $ant4->currentCoord = $ant4Num;

        //  Список ботов
        $antList = Bots::getInstance();
        $antList->add($ant3);
        $antList->add($ant4);

        // Добавим цели (что бы пути пересеклись).
        $ant3->setGol($food4Num);
        $ant4->setGol($food3Num);

        // Выберем направление движения
        $directionArray3 = Tools::createDirection($ant3);
        $directionArray4 = Tools::createDirection($ant4);

        // w - запад col
        // e - восток col
        // n - север row
        // s - юг row

        // Идет на право (восток)
        $this->assertEquals($directionArray3, array('row' => 0, 'col' => 'e'));
        // Идет на лево (запад)
        $this->assertEquals($directionArray4, array('row' => 0, 'col' => 'w'));


        // Если пойдем прямо - столкновение обязательно!

        // Определим дальнейшее поведение при выборе реального пути
        $move3 = $antList->selectMove($ant3, $directionArray3);
        $move4 = $antList->selectMove($ant4, $directionArray4);


        // Первый сохраняет направление
        $this->assertEquals('e', $move3);
        // Второй уходит на север
        $this->assertEquals('n', $move4);

        // Воткнем препятствие на севере
        // Почистим "будующие координаты"
        Bots::getInstance()->clearNext();
        // Определим точку на карте севернее второго
        $mapWater1 = Tools::createNum(0, 15);

        // Поместим ее на карту
        Steamer::$staticMap[$mapWater1] = WATER;

        // Еще раз двигаем
        $move3 = $antList->selectMove($ant3, $directionArray3);
        $move4 = $antList->selectMove($ant4, $directionArray4);

        // Должен попробовать на север, обломится и все таки пойти на юг

        // w - запад col
        // e - восток col
        // n - север row
        // s - юг row

        // Они разминулись!!!
        $this->assertEquals('e', $move3);
        $this->assertEquals('s', $move4);

    }
}
// w - запад [col]
// e - восток [col]
// n - север [row]
// s - юг [row]

$botsTets = new BotsTest();
$botsTets->run($argv);