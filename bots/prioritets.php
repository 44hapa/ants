<?php
/**
 * Created by PhpStorm.
 * User: guru
 * Date: 11.02.14
 * Time: 22:24
 */

class Prioritets
{

    private $list = array();

    /**
     * @var Prioritets
     */
    private static $instance;

    /**Предположительные координаты
     * @var array
     */
    private $assumptionNext = array();

    private function __construct()
    {

    }


    public function fixCollision(){



//        $list['цель1'] = array(
//            'бот1' => 'расстояние1',
//            'бот2' => 'расстояние1',
//            'бот3' => 'расстояние1',
//            'бот4' => 'расстояние2',
//        );
//        $list['цель2'] = array(
//            'бот1' => 'расстояние1',
//            'бот2' => 'расстояние3',
//            'бот3' => 'расстояние3',
//            'бот4' => 'расстояние3',
//        );
    }


    public function determinateBests()
    {
//        Tools::logger("Line : " . __LINE__ . "\n");
        foreach ($this->list as $mapKeyPrior => $antKeyAndDist){
//            Tools::logger("Line : " . __LINE__ . "\n");
            // Если притендент только один - присваиваемся и скипуем
            if (count($antKeyAndDist) == 1){
                $ant = Bots::getInstance()->getByKey(key($antKeyAndDist));
                $ant->gol = $mapKeyPrior;
                continue;
            }
//            Tools::logger("Line : " . __LINE__ . "\n");
            // Ближайший (самая маленькая дистанция)
            $best = min($antKeyAndDist);
            // Массив ближайших
            $bestArray = array_keys($antKeyAndDist, $best);
            if (count($bestArray) == 1){
                $ant = Bots::getInstance()->getByKey(key($antKeyAndDist));
                $ant->gol = $mapKeyPrior;
                continue;
            }
//            Tools::logger("Line : " . __LINE__ . "\n");
            // Если ближайших много, определим есть ли у них еще цели.
            // Используем того, у кого целей меньше.
            foreach ($bestArray as $antKey => $dist){
                $ant = Bots::getInstance()->getByKey($antKey);
                $bestArray[$antKey] = count($ant->prioritiPoints);
            }
//            Tools::logger("Line : " . __LINE__ . "\n");
            $best = min($bestArray);
            $bestArray = array_keys($antKeyAndDist, $best);
            if (count($bestArray) == 1){
                $ant = Bots::getInstance()->getByKey(key($bestArray));
                $ant->gol = $mapKeyPrior;
                continue;
            }
//            Tools::logger("Line : " . __LINE__ . "\n");

            // Если целей одинаковое кол-во.
            $ant = Bots::getInstance()->getByKey(key($bestArray));
            $ant->gol = $mapKeyPrior;
            Tools::logger("Line : " . __LINE__ . "\n");
        }
    }



    public function getList()
    {
        return $this->list;
    }

    public function add(Bot $ant)
    {
        foreach($ant->prioritiPoints as $mapKye => $distance){
            $this->list[$mapKye][$ant->currentCoord] = $distance;
        }
        return $mapKye;
    }


    public function clear()
    {
        $this->list = array();
    }


    /**
     * @param int $key
     * @return array
     */
    public function getByKey($key)
    {
        return $this->list[$key];
    }

    /**
     * @return Prioritets
     */
    static public function getInstance()
    {
        if (!self::$instance){
            self::$instance = new self;
        }
        return self::$instance;
    }
}