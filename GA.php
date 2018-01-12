<?php
set_time_limit(0);
header("Content-type:text/plain");
// 参数
$target = 'Tang';
$targetBin = strToBin($target);
$len = strlen($target);
$bestFit = array();
$bestStr = array();

// 初始化
$map = array();
for ($i = 0; $i < 100; $i++) {
    $temp = genRandStr($len);
//    $map[$temp] = getFitness($target, binToStr($temp));
//    $map[$temp] = getFitness2($targetBin, $temp);
    $map[$temp] = getFitness3($targetBin, $temp);
}
asort($map);
$bestFit[] = current($map);
$bestStr[] = key($map);

// 循环
for ($j = 0; $j < 1000; $j++) {
    reset($map);
    $childMap = array();
    for ($i = 0; $i < 50; $i++) {
        $first = key($map);
        next($map);
        $second = key($map);
        $children = hybird($first, $second);
        foreach ($children as $child) {
            $child = str_pad($child, 24, 0, STR_PAD_LEFT);
            $temp = mutation($child);
//            $childMap[$temp] = getFitness($target, binToStr($temp));
//            $childMap[$temp] = getFitness2($targetBin, $temp);
            $childMap[$temp] = getFitness3($targetBin, $temp);
        }
    }
    asort($childMap);
    $map = $childMap;
    $bestFit[] = current($map);
    $bestStr[] = key($map);
    if (end($bestFit) == 0) {
        break;
    }
}
//var_dump(end($bestStr));
//var_dump(binToStr(end($bestStr)));
foreach ($bestStr as $item) {
    var_dump(binToStr($item));
}

// 生成24位的二进制字符串
function genRandStr($len) {
    $res = '';
    for ($i = 0; $i < $len; $i++) {
        $res .= str_pad(decbin(rand(0, 51)), 6, 0, STR_PAD_LEFT);
    }
    return $res;
}

// 计算fitness
function getFitness($target, $str) {
    $letterIndex = array_flip(array_merge(range('A', 'Z'), range('a', 'z')));
    $sum = 0;
    $len = strlen($target);
    for ($i = 0; $i < $len; $i++) {
        $sum += abs($letterIndex[$target[$i]] - $letterIndex[$str[$i]]);
    }
    return $sum;
}

function getFitness2($targetBin, $bin) {
    $sum = 0;
    for ($i = 0; $i < strlen($bin); $i += 6) {
        $subTar = substr($targetBin, $i, 6);
        $subBin = substr($bin, $i, 6);
        $sum += abs(bindec($subBin) - bindec($subTar));
    }
    return $sum;
}

// 汉明距离
function getFitness3($targetBin, $bin) {
    $sum = 0;
    for ($i = 0; $i < strlen($bin); $i++) {
        if ($targetBin[$i] xor $bin[$i]) {
            $sum++;
        }
    }
    return $sum;
}

// 二进制转字符串
function binToStr($bin) {
    $indexLetter = array_merge(range('A', 'Z'), range('a', 'z'));
    $res = '';
    for ($i = 0; $i < strlen($bin); $i += 6) {
        $temp = substr($bin, $i, 6);
        $res .= $indexLetter[bindec($temp)];
    }
    return $res;
}

// 字符串转二进制
function strToBin($str) {
    $letterIndex = array_flip(array_merge(range('A', 'Z'), range('a', 'z')));
    $res = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $res .= str_pad(decbin($letterIndex[$str[$i]]), 6, 0,STR_PAD_LEFT);
    }
    return $res;
}

// 杂交
function hybird($father, $mother) {
    $hp = 0.9;
    $epsilon = 0.00000000000001;
    if (($hp - lcg_value()) > $epsilon) {
        $f1 = substr($father, 0, floor(strlen($father)/2));
        $f2 = substr($father, floor(strlen($father)/2));
        $m1 = substr($mother, 0, floor(strlen($mother)/2));
        $m2 = substr($mother, floor(strlen($mother)/2));
        return array($f1.$m2, $m1.$f2);
    }else{
        return array($father, $mother);
    }
}

// 按概率变异
function mutation($str) {
    $mp = 0.01;
    $epsilon = 0.00000000000001;
    if (($mp - lcg_value()) > $epsilon) {
        $n = rand(0, 23);
        $str[$n] = 1 - $str[$n];
    }
    return $str;
}