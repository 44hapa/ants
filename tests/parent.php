<?php
//_egg_logo_guid();
class Test
{

    public function assertEquals($one, $to)
    {
        if ($one !== $to) {
            throw new Exception("\033[31m" . print_r($one, true) . " !== " . print_r($to, true) . "\033[0m");
        }
        return true;
    }


    static public function testQer(){
    }

    public function run($param)
    {
        if (isset($param[1])) {
            $methods = array($param[1]);
        }else{
            $methods = (get_class_methods($this));
        }

        foreach ($methods as $method) {
            if (false !== strpos($method, 'test')) {
                $this->clear();
                $this->$method();
            }
        }
        echo "\n\033[32mGOOD\033[0m\n";
    }

    public function clear()
    {
        
    }
}
