<?php

/*
 * This file is part of PHP-CFG, a Control flow graph implementation for PHP
 *
 * @copyright 2015 Anthony Ferrara. All rights reserved
 * @license MIT See LICENSE at the root of the project for more info
 */

namespace PHPCfg\Visitor;

use PHPCfg\Block;
use PHPCfg\Op;
use PHPCfg\AbstractVisitor;
use PHPCfg\Op\Stmt\TraitUse;
use Symfony\Component\Console\Exception\LogicException;

class TraitToClassLinker extends AbstractVisitor {
	/** @var Op\Stmt\Class_ $currentClass */
	private $currentClass = null;

	public function enterOp(Op $op, Block $block) {
		if ($op instanceof Op\Stmt\Class_) {
			$this->currentClass = $op;
		} else if ($op instanceof TraitUse) {
			if ($this->currentClass === null) {
				throw new LogicException("Cannot have a trait-use when no class is registered.");
			} else {
				foreach ($op->traits as $trait_lit) {
					$this->currentClass->addUse($trait_lit);
				}
			}
		}

	}

	public function leaveOp(Op $op, Block $block) {
		if ($op instanceof Op\Stmt\Class_) {
			$this->currentClass = null;
		}
	}
}