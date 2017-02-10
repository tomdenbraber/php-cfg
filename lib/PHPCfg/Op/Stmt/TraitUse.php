<?php
namespace PHPCfg\Op\Stmt;

use PHPCfg\Op\Stmt;
use PhpCfg\Operand;

class TraitUse extends Stmt {

	/** @var Operand\Literal[] */
	public $traits;

	public function __construct(array $traits, array $attributes = []) {
		parent::__construct($attributes);
		$this->traits = $traits;
	}

	public function getSubBlocks() {
		return [];
	}

	public function getVariableNames() {
		return ['traits'];
	}
}