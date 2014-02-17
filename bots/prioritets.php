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

        Tools::logger("#######Весь список приоритетов НАЧАЛО######");
        Tools::logger($this->list);
        Tools::logger("#######Весь список приоритетов КОНЕЦ######");

        // $mapKeyPrior - координата приоритета
        // $antKeyAndDist [координата муравья][дистанция]
        foreach ($this->list as $mapKeyPrior => $antKeyAndDist){
//            Tools::logger('Итерируем приоритет');
            // Если притендент всего однин - смотрим ближе
            if (count($antKeyAndDist) == 1){
                $ant = Bots::getInstance()->getByKey(key($antKeyAndDist));
                // Если дистанция до текущего приоритета маньше - ставим его
                Tools::logger("Текущая Достанция до еды : " . Tools::mapDistance($ant->gol, $ant->currentCoord));
                Tools::logger("Предлагаемая Достанция до еды : " . reset($antKeyAndDist));
                if (empty($ant->gol) || Tools::mapDistance($ant->gol, $ant->currentCoord) > reset($antKeyAndDist)){
                    // Присваиваем приоритет этому единственному муравью
                    Tools::logger("old " . implode(' : ', Tools::createCoordinate($ant->gol)) . " new "  . implode(' : ', Tools::createCoordinate($mapKeyPrior)));
                    $ant->setGol($mapKeyPrior);
                }
//                Tools::logger("DIST " . current($antKeyAndDist));
//                Tools::logger("mapKeyPrior " . implode(Tools::createCoordinate($mapKeyPrior ), ':'));
//                Tools::logger(" {1} Ant:[" . implode(Tools::createCoordinate($ant->currentCoord ), ':'). "] = EDA:" . implode(Tools::createCoordinate($ant->gol),':' ));
                // Т.к. муравей единственный, то в любом случае кончаем итерацию, переходя к следующему приоритету.
                continue;
            }
            // Если притендент не один

            // Вычислим сколько еще приоритетов у муравьев, нацеленых на текущий
            $antsCountPriority = array();
            foreach($antKeyAndDist as $antKey => $dist){
//                Tools::logger('Итерация ботов в приоритете');
                $ant = Bots::getInstance()->getByKey($antKey);
                // Количество точек приоритета
                $countPriorityPoints = count($ant->prioritiPoints);
                $antsCountPriority[$countPriorityPoints][$antKey] = $dist;
            }

            foreach($antsCountPriority as $countPriorityPoints => $antKeyDist){
//                Tools::logger($antKeyDist);
                $bestDist = 1000;
                $bestAnt = null;
                foreach($antKeyAndDist as $antKey => $dist)
                {
                    $ant = Bots::getInstance()->getByKey($antKey);
//                    if (empty($ant->gol) || Tools::mapDistance($ant->gol, $ant->currentCoord) > $dist){
                    // Дадим приоритет тому, у кого его еще нет и расстояние до которого ближе
                    if (empty($ant->gol) && $bestDist > $dist){
                        $bestDist = $dist;
                        $bestAnt = $ant;

                    }
                }
                // Если в данном наборе приотритетов нашелся тот, у которого нет цели, и расстояние до цели лучшее - присвоим ему цель.
                if (!empty($bestAnt)){
                    $bestAnt->setGol($mapKeyPrior);
//                    Tools::logger(" {2} Ant:[" . implode(Tools::createCoordinate($bestAnt->currentCoord ), ':'). "] = EDA:" . implode(Tools::createCoordinate($bestAnt->gol),':' ));
                    break;
                }
            }
        }
    }

    public function getList()
    {
        return $this->list;
    }

    public function add(Bot $ant)
    {
        Tools::logger("В приоритеты добавляем бота " . implode(':', Tools::createCoordinate($ant->currentCoord)));
        if (empty($ant->prioritiPoints))
        {
            Tools::logger("У этого бота нет приоритетных точек " . implode(':', Tools::createCoordinate($ant->currentCoord)));
        }

        foreach($ant->prioritiPoints as $mapKye => $distance){
            // [координата цели] [координата муравья] = рассояние
            $this->list[$mapKye][$ant->currentCoord] = $distance;
        }
        return;
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