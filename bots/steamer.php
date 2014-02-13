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

    public $turns = 0;
    public $loadtime = 0;
    public $turntime = 0;
    public $viewradius2 = 0;
    public $attackradius2 = 0;
    public $spawnradius2 = 0;


    // Карта рельефа
    public $staticMap;

    public $map;
    public $myAnts = array();
    public $enemyAnts = array();
    public $deadAnts = array();

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
        foreach ($data as $line) {
            if (strlen($line) > 0) {
                $tokens = explode(' ', $line);
                $key = $tokens[0];
                if (property_exists($this, $key)) {
                    $this->{$key} = (int) $tokens[1];
                }
                if ($key === 'rows') {
                    Tools::$rows = (int) $tokens[1];
                }
                if ($key === 'cols') {
                    Tools::$cols = (int) $tokens[1];
                }
            }
        }

        $maxCel = Tools::$rows * Tools::$cols;
        $this->staticMap = array_pad(array(0), $maxCel -1, UNSEEN);

        for ($row = 0; $row < Tools::$rows; $row++) {
            for ($col = 0; $col < Tools::$cols; $col++) {
                // Закрашивает все клетки карты землей
                $this->map[$row][$col] = LAND;
            }
        }
    }

    /** not tested */
    public function update($data)
    {
        // Почистим лист ботов
        Bots::getInstance()->clear();
//        Ants::logger(Tools::$rows);
//        Ants::logger(Tools::$cols);

        // clear ant and food data
        foreach ($this->myAnts as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        $this->myAnts = array();

        foreach ($this->enemyAnts as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        $this->enemyAnts = array();

        foreach ($this->deadAnts as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        $this->deadAnts = array();

        foreach (Tools::$food as $ant) {
            list($row, $col) = $ant;
            $this->map[$row][$col] = LAND;
        }
        Tools::$food = array();

        # update map and create new ant and food lists
        foreach ($data as $line) {
            if (strlen($line) > 0) {
                $tokens = explode(' ', $line);

                if (count($tokens) >= 3) {
                    $row = (int) $tokens[1];
                    $col = (int) $tokens[2];

                    $staticMapKey = Tools::createNum($row, $col);

//                    self::logger($this->viewradius2 . "\n");
                    // Нашли муравья
                    if ($tokens[0] == 'a') {
                        $owner = (int) $tokens[3];
                        $this->map[$row][$col] = $owner;
                        $this->staticMap[$staticMapKey] = LAND;;
                        if ($owner === 0) {
                            // Засунем бота в лист
                            $bot = new Bot();
                            $bot->currentCoord = $staticMapKey;
                            Bots::getInstance()->add($bot);
//                            Tools::logger($bot);
                            $this->myAnts [] = array($row, $col);
                        } else {
                            $this->enemyAnts [] = array($row, $col);
                        }
                    // Нашли еду
                    } elseif ($tokens[0] == 'f') {
                        $this->map[$row][$col] = FOOD;
                        $this->staticMap[$staticMapKey] = LAND;
                        Tools::$food [$staticMapKey] = array($row, $col);
                     // Нашли воду
                    } elseif ($tokens[0] == 'w') {
                        $this->map[$row][$col] = WATER;
                        $this->staticMap[$staticMapKey] = WATER;
                    // Нашли смерть
                    } elseif ($tokens[0] == 'd') {
                        $this->staticMap[$staticMapKey] = LAND;
                        $this->map[$row][$col] = DEAD;
                        $this->deadAnts [] = array($row, $col);
                    } elseif ($tokens[0] == 'h') {
                        $this->staticMap[$staticMapKey] = HOME;
                    }
                }
            }
        }
//        self::logger($this->staticMap);
//        self::logger();
    }

    public function passable($row, $col)
    {
        return $this->map[$row][$col] != WATER && $this->map[$row][$col] != MY_ANTS;
    }

    public function unoccupied($row, $col)
    {
        return in_array($this->map[$row][$col], array(LAND, DEAD, FOOD));
    }

    public function direction($row1, $col1, $row2, $col2)
    {
        $d = array();
        $row1 = $row1 % Tools::$rows;
        $row2 = $row2 % Tools::$rows;
        $col1 = $col1 % Tools::$cols;
        $col2 = $col2 % Tools::$cols;

        if ($row1 < $row2) {
            if ($row2 - $row1 >= Tools::$rows / 2) {
                $d [] = 'n';
            }
            if ($row2 - $row1 <= Tools::$rows / 2) {
                $d [] = 's';
            }
        } elseif ($row2 < $row1) {
            if ($row1 - $row2 >= Tools::$rows / 2) {
                $d [] = 's';
            }
            if ($row1 - $row2 <= Tools::$rows / 2) {
                $d [] = 'n';
            }
        }
        if ($col1 < $col2) {
            if ($col2 - $col1 >= Tools::$cols / 2) {
                $d [] = 'w';
            }
            if ($col2 - $col1 <= Tools::$cols / 2) {
                $d [] = 'e';
            }
        } elseif ($col2 < $col1) {
            if ($col1 - $col2 >= Tools::$cols / 2) {
                $d [] = 'e';
            }
            if ($col1 - $col2 <= Tools::$cols / 2) {
                $d [] = 'w';
            }
        }
        return $d;
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
