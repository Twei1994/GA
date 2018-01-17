<?php
class GA {
    // 算法参数
    private $target;  // 目标字符串
    private $len;     // 目标字符串长度
    private $hp;      // 交叉概率
    private $mp;      // 编译概率
    private $map;     // 种群
    private $num;     // 种群个数
    private $bestStr; // 每组最优字符串
    private $bestFit; // 每组最优适应力

    // 构造方法，初始化参数
    public function __construct($target = "Tang", $hp = 0.9, $mp = 0.01, $num = 1000) {
        if (empty($target)) {
            throw new Exception("empty string as target");
        }
        $this->target  = $target;
        $this->len     = strlen($target);
        $this->hp      = $hp;
        $this->mp      = $mp;
        $this->map     = array();
        $this->num     = $num;
        $this->bestStr = array();
        $this->bestFit = array();
    }

    // 获取类参数方法
    public function getTarget() {
        return $this->target;
    }

    public function getLen() {
        return $this->len;
    }

    public function getMap() {
        return $this->map;
    }

    public function getBestStr() {
        $bestStr = $this->bestStr;
        foreach ($bestStr as $key => $value) {
            $bestStr[$key] = $this->binToStr($value);
        }
        return $bestStr;
    }

    public function getBestFit() {
        return $this->bestFit;
    }

    // 生成初始种群
    public function initialize() {
        $num = $this->num;
        for ($i = 0; $i < $num; $i++) {
            $temp = $this->genRandStr();
            $this->map[$temp] = $this->getFitness($temp, 1);
        }
        asort($this->map);
        $this->setBest();
    }

    // 交叉和变异n代
    public function breed($n) {
        $groupNum = floor($this->num/2);
        $len = $this->len;
        for ($i = 0; $i < $n; $i++) {
            $map = $this->map;
            $childMap = array();
            for ($j = 0; $j < $groupNum; $j++) {
                $first = key($map);
                next($map);
                $second = key($map);
                $children = $this->hybird($first, $second);
                foreach ($children as $child) {
                    $child = str_pad($child, 6*$len, 0, STR_PAD_LEFT);
                    $temp = $this->mutation($child);
                    $childMap[$temp] = $this->getFitness($temp, 1);
                }
            }
            asort($childMap);
            $this->map = $childMap;
            unset($map);
            unset($childMap);
            $this->setBest();
            if (end($this->bestFit) == 0) {
                break;
            }
        }
    }

    // 生成随机二进制字符串
    private function genRandStr() {
        $res = '';
        for ($i = 0; $i < $this->len; $i++) {
            $res .= str_pad(decbin(rand(0, 51)), 6, 0, STR_PAD_LEFT);
        }
        return $res;
    }

    // 计算适应力函数
    private function getFitness($bin, $index = 1) {
        switch ($index) {
            case 1:
                return $this->getFitness1($bin);
                break;
            case 2:
                return $this->getFitness2($bin);
                break;
            default:
                throw new Exception("wrong input param");
                break;
        }
    }

    // 汉明距离
    private function getFitness1($bin) {
        $target = $this->target;
        $sum = 0;
        $targetBin = $this->strToBin($target);
        for ($i = 0; $i < strlen($bin); $i++) {
            if ($targetBin[$i] xor $bin[$i]) {
                $sum++;
            }
        }
        return $sum;
    }

    private function getFitness2($bin) {
        $sum = 0;
        for ($i = 0; $i < strlen($bin); $i += 6) {
            $subTar = substr($this->strToBin($this->target), $i, 6);
            $subBin = substr($bin, $i, 6);
            $sum += abs(bindec($subBin) - bindec($subTar));
        }
        return $sum;
    }

    // 英文字符串转成二进制字符串
    private function strToBin($str) {
        $letterIndex = array_flip(array_merge(range('A', 'Z'), range('a', 'z')));
        $bin = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $bin .= str_pad(decbin($letterIndex[$str[$i]]), 6, 0,STR_PAD_LEFT);
        }
        return $bin;
    }

    // 二进制字符串转成英文字符串
    private function binToStr($bin) {
        $indexLetter = array_merge(range('A', 'Z'), range('a', 'z'));
        $str = '';
        for ($i = 0; $i < strlen($bin); $i += 6) {
            $temp = substr($bin, $i, 6);
            $str .= $indexLetter[bindec($temp)];
        }
        return $str;
    }

    // 保存当前最优值
    private function setBest() {
        $this->bestFit[] = current($this->map);
        $this->bestStr[] = key($this->map);
    }

    // 交叉
    private function hybird($father, $mother) {
        $epsilon = 0.00000000000001;
        if (($this->hp - lcg_value()) > $epsilon) {
            $f1 = substr($father, 0, floor(strlen($father)/2));
            $f2 = substr($father, floor(strlen($father)/2));
            $m1 = substr($mother, 0, floor(strlen($mother)/2));
            $m2 = substr($mother, floor(strlen($mother)/2));
            return array($f1.$m2, $m1.$f2);
        }else{
            return array($father, $mother);
        }
    }

    // 变异
    private function mutation($bin) {
        $epsilon = 0.00000000000001;
        if (($this->mp - lcg_value()) > $epsilon) {
            $n = rand(0, 23);
            $bin[$n] = 1 - $bin[$n];
        }
        return $bin;
    }
}

try {
    $target = $_POST["target"];
    if (empty($target)) {
        $target = "Tang";
    }
    $test = new GA($target);
    $test->initialize();
    $test->breed(100);
    echo json_encode($test->getBestStr());
} catch (Exception $e) {
    echo $e->getMessage();
    die;
}