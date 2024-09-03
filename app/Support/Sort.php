<?php

class Sort
{
    //$input = array(
    //	array('i'=> 6,'qwe'=>'6'),
    //	array('i'=> 5,'qwe'=>'5'),
    //	array('i'=> 3,'qwe'=>'3'),
    //	array('i'=> 1,'qwe'=>'1'),
    //	array('i'=> 8,'qwe'=>'8'),
    //	array('i'=> 7,'qwe'=>'7'),
    //	array('i'=> 2,'qwe'=>'2'),
    //	array('i'=> 4,'qwe'=>'4'));
    public function shell_sort($arr, $key)
    {
        $len = count($arr);
        $gap = floor($len / 2);
        while ($gap > 0) {
            for ($i = $gap; $i < $len; $i++) {
                $temp = $arr[$i];
                $j = $i;
                while ($j >= $gap && $arr[$j - $gap][$key] > $temp[$key]) {
                    $arr[$j] = $arr[$j - $gap];
                    $j -= $gap;
                }
                $arr[$j] = $temp;
            }
            $gap = floor($gap / 2);
        }

        return $arr;
    }
    // 1, 2, 3, 4, 5, 6, 7, 8
    //print_r(shell_sort($input,'i'));

}
