<?php

define('HOME', 'HOME');
define('MY_ANT', 'MY_ANT');
define('ANTS', 'ANTS');
define('DEAD', 'DEAD');
define('LAND', 'LAND');
define('FOOD', 'FOOD');
define('WATER', 'WATER');
define('UNSEEN', 'UNSEEN');

/*
 * w - вода
 * h - мураейник (0 - мой)
 * a - муравей
 * f - еда
 * d - мертвый муравей
 * 
 */

class Steamer
{

    static public $turns = 0;
    static public $loadtime = 0;
    static public $turntime = 0;
    static public $rows = 0;
    static public $cols = 0;
    static public $viewradius2 = 0;
    static public $attackradius2 = 0;
    static public $spawnradius2 = 0;


    // Карта рельефа
    static public $staticMap;
    static public $map;

    static public $water = array();
    static public $food = array();
    static public $myAnts = array();
    static public $enemyAnts = array();
    static public $land = array();
    static public $deadAnts = array();
    static public $enemyHome = array();
    static public $home = array();

    //вода, еда, мои боты, враги боты, земля, трупы, враги дома, мои дома - 8 массивов

    static public function issueOrder($aRow, $aCol, $direction)
    {
        printf("o %s %s %s\n", $aRow, $aCol, $direction);
        flush();
    }

    public function finishTurn()
    {
        echo("go\n");
        flush();
    }

    public function setup($data)
    {
         Tools::logger($data);
        foreach ($data as $line) {
            if (strlen($line) > 0) {
                $tokens = explode(' ', $line);
                $key = $tokens[0];
                if (property_exists($this, $key)) {
                    Tools::logger('property_exists: ' .$key);
                    self::${$key} = (int) $tokens[1];
                }
            }
        }
        
        $maxCel = Steamer::$rows * Steamer::$cols;
        self::$staticMap = array_pad(array(0), $maxCel, UNSEEN);
    }

    public function update($data)
    {

        Tools::logger('DATA>>>>');

        Tools::logger($data);

        Tools::logger('DATA<<<<<');
/*
 * Что бы хотелось
 * Иметь отдельные массивы для ВСЕГО (вода, еда, мои боты, враги боты, земля, трупы, враги дома, мои дома - 8 массивов)
 * В статичную мапу запихать ВСЕ (т.е. смержить) + невидимые клетки
 * На каждой итерации чистить все, кроме воды, и домов (3 массива)
 * 
 */

        // Почистим лист ботов
        Bots::getInstance()->clear();

//        self::$water = array();
        self::$food = array();
        self::$myAnts = array();
        self::$enemyAnts = array();
//        self::$land = array();
        self::$deadAnts = array();
//        self::$enemyHome = array();
//        self::$home = array();

        Tools::$food = array();

        # update map and create new ant and food lists
        foreach ($data as $line) {
            if (strlen($line) > 0) {
                $tokens = explode(' ', $line);

                if (count($tokens) >= 3) {
                    $row = (int) $tokens[1];
                    $col = (int) $tokens[2];

                    $staticMapKey = Tools::createNum($row, $col);

                    // Нашли муравья
                    if ($tokens[0] == 'a') {
                        $owner = (int) $tokens[3]; // Если равно нулю (0) - то хозяин Я
                        $this->map[$row][$col] = $owner;
                        self::$staticMap[$staticMapKey] = LAND;
                        if ($owner === 0) {
                            // Если я зашел в чужой дом - он стал моим :)
                            if (in_array($staticMapKey, self::$enemyHome)) {
                                self::$home[] = $staticMapKey;
                                $unsetKey = array_search($staticMapKey, self::$enemyHome);
                                unset(self::$enemyHome[$unsetKey]);
                                 Tools::$defaultGoal = null;
                            }

                            // Засунем бота в лист

                            // Тут надо порулить прошлыми координатами и нестоящими..
                            $botNumber = Bots::getInstance()->getBotByLastPreviousCoordinatOrNew($staticMapKey);

                            $bot = new Bot();
                            $bot->currentCoord = $staticMapKey;
                            $bot->coordinatColRow = array('row' => $row, 'col' => $col);
                            $bot->number = $botNumber;

                            Bots::getInstance()->add($bot);
//                            Tools::logger($bot);
                            $this->myAnts [] = array($row, $col);
                        } else {
                            Tools::logger('Враг $row, $col ' . "$row, $col");
                            $this->enemyAnts [] = array($row, $col);
                        }
                    // Нашли еду
                    } elseif ($tokens[0] == 'f') {
                        $this->map[$row][$col] = FOOD;
                        self::$staticMap[$staticMapKey] = LAND;
                        Tools::$food[$staticMapKey] = array($row, $col);
                     // Нашли воду
                    } elseif ($tokens[0] == 'w') {
                        $this->map[$row][$col] = WATER;
                        self::$staticMap[$staticMapKey] = WATER;
                    // Нашли смерть
                    } elseif ($tokens[0] == 'd') {
                        // Удалим мертвеца
                        $botDeadNum = Bots::getInstance()->getBotByLastPreviousCoordinatOrNew($staticMapKey);
                        Bots::getInstance()->removeBotFromPreviousCoordinats($botDeadNum);
                        Tools::logger("В координатах[$row, $col :: $staticMapKey] умер бот. Его номер [$botDeadNum] . В живых осталось ботов " . count(Bots::getInstance()->getPreviousCoordinats()));
                        self::$staticMap[$staticMapKey] = LAND;
                        $this->map[$row][$col] = DEAD;
                        $this->deadAnts [] = array($row, $col);
                    } elseif ($tokens[0] == 'h') {
                        // В первой итерации определим наши дома
                        self::$staticMap[$staticMapKey] = HOME;
                        if (Tools::$turn == 0) {
                            self::$home[] = $staticMapKey;
                        }
                        if (!in_array($staticMapKey, self::$home)) {
                            self::$enemyHome[] = $staticMapKey;
                            // цель по умолчанию
                            Tools::$defaultGoal = $staticMapKey;
                        }
                        
                    }
                }
            }
        }
    }

    public static function run($bot)
    {
        $ants = new Steamer();
        $inputData = array();

        while (true) {
            $current_line = fgets(STDIN, 1024);
            $current_line = trim($current_line);
            if ($current_line === 'ready') {
                // Выполняется единожды, при первой загрузке карты
                $ants->setup($inputData);
                $ants->finishTurn();
                $inputData = array();
            } elseif ($current_line === 'go') {
                Tools::logger();
                Tools::$turn++;
                // Выполняется каждый раз, после окончания передачи порции параметров текущего шага.
                $ants->update($inputData);

                $bot->doTurn($ants);
                $ants->finishTurn();
                $inputData = array();
            } else {
                // Массив всех входных данных
                $inputData [] = $current_line;
            }
        }
    }

}
