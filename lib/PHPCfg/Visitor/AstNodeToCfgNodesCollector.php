<?php
namespace PHPCfg\Visitor;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PHPCfg\Operand;

use PhpParser\Node;

class AstNodeToCfgNodesCollector extends AbstractVisitor {
	/** @var \SplObjectStorage */
	private $ast_to_ops;

	/** @var \SplObjectStorage */
	private $ast_to_operands;

	public function __construct() {
		$this->ast_to_ops = new \SplObjectStorage;
		$this->ast_to_operands = new \SplObjectStorage;
	}

	public function enterOp(Op $op, Block $block) {
		$this->tryLinkOpToAST($op);

		/** @var string $variable_name */
		foreach ($op->getVariableNames() as $variable_name) {
			if ($op->$variable_name instanceof Operand) {
				$this->tryLinkOperandToAST($op->$variable_name);
			} else if (is_array($op->$variable_name)) {
				foreach ($op->$variable_name as $idx => $actual_var) {
					$this->tryLinkOperandToAST($actual_var);
				}
			}
		}
	}

	/**
	 * @return \SplObjectStorage
	 */
	public function getLinkedOps() {
		return $this->ast_to_ops;
	}

	/**
	 * @return \SplObjectStorage
	 */
	public function getLinkedOperands() {
		return $this->ast_to_operands;
	}

	private function linkOpToASTNode(Op $op, Node $ast_node) {
		if ($this->ast_to_ops->contains($ast_node) === false) {
			$this->ast_to_ops[$ast_node] = array();
		}

		$linked_ops = $this->ast_to_ops[$ast_node];
		if (in_array($op, $linked_ops, true) === false) {
			$linked_ops[] = $op;
			$this->ast_to_ops[$ast_node] = $linked_ops;
		}
	}

	private function linkOperandToASTNode(Operand $operand, Node $ast_node) {
		if ($this->ast_to_operands->contains($ast_node) === false) {
			$this->ast_to_operands[$ast_node] = array();
		}

		$linked_operands = $this->ast_to_operands[$ast_node];
		if (in_array($operand, $linked_operands, true) === false) {
			$linked_operands[] = $operand;
			$this->ast_to_operands[$ast_node] = $linked_operands;
		}
	}

	private function tryLinkOpToAST(Op $op) {
		if ($op->getAstNode() !== null) {
			$this->linkOpToASTNode($op, $op->getAstNode());
		}
	}

	private function tryLinkOperandToAST(Operand $operand) {
		foreach ($operand->getAstNodes() as $ast_node) {
			$this->linkOperandToASTNode($operand, $ast_node);
		}
	}
}