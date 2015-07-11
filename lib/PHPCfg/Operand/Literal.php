<?php

namespace PHPCfg\Operand;

use PHPCfg\Operand;

class Literal implements Operand {
    public $value;
    public $ops = [];
    
    public $usages = [];

    public function __construct($value) {
        $this->value = $value;
    }
}