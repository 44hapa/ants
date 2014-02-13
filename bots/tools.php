<?php

class Tools
{

    static public $turn = 0;
    static public $rows = 0;
    static public $cols = 0;
    static public $food = array();

    static public $AIM = array(
        'n' => array(-1, 0),
        'e' => array(0, 1),
        's' => array(1, 0),
        'w' => array(0, -1));
    public $RIGHT = array(
        'n' => 'e',
        'e' => 's',
        's' => 'w',
        'w' => 'n');
    public $LEFT = array(
        'n' => 'w',
        'e' => 'n',
        's' => 'e',
        'w' => 's');
    public $BEHIND = array(
        'n' => 's',
        's' => 'n',
        'e' => 'w',
        'w' => 'e'
    );

    static public function createNum($row, $col)
    {
        return $row * self::$cols + $col;
//        return $row * self::$cols - (self::$cols - $col);
        return ($row + 1) * self::$cols - (self::$cols - $col + 1);
    }

    static public function createCoordinate($num)
    {
        $row = (int)($num/self::$cols);
        $col = $num - $row * self::$cols;
        return array($row, $col);

        //=======
        $row = $num / self::$cols ;
        if (is_int($row)){
            $col = self::$cols;
            return array($row - 1, $col + 1);
        }
        $col = $num - (int) $row * self::$cols;
        $row = (int) $row + 1;
        return array($row - 1, $col + 1);
    }

    static public function distance($row1, $col1, $row2, $col2)
    {
        $dRow = abs($row1 - $row2);
        $dCol = abs($col1 - $col2);

        $dRow = min($dRow, self::$rows - $dRow);
        $dCol = min($dCol, self::$cols - $dCol);

        return sqrt($dRow * $dRow + $dCol * $dCol);
    }

    static public function mapDistance($mapNum1, $mapNum2)
    {
        $coordinat1 = self::createCoordinate($mapNum1);
        $coordinat2 = self::createCoordinate($mapNum2);

        return self::distance($coordinat1[0], $coordinat1[1], $coordinat2[0], $coordinat2[1]);
    }

    static public function destination($row, $col, $direction)
    {
        list($dRow, $dCol) = self::$AIM[$direction];
        $nRow = ($row + $dRow) % self::$rows;
        $nCol = ($col + $dCol) % self::$cols;
        if ($nRow < 0)
            $nRow += self::$rows;
        if ($nCol < 0)
            $nCol += self::$cols;
        return array($nRow, $nCol);
    }

    public static function logger($params = null)
    {
        $handle = fopen('./../game_logs/antlog', "a+");
        fwrite($handle, print_r("\nturn:[" .Tools::$turn ."]", true));
//        fwrite($handle, "turn:[" .Tools::$turn ."]");
        if (!$params) {
            fwrite($handle, print_r("==============================\n", true));
        } else {
            fwrite($handle, print_r($params, true));
        }
        fclose($handle);
    }

}