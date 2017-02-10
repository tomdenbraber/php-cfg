<?php
namespace PHPCfg\Visitor;

use PHPCfg\Block;
use PHPCfg\Op;
use PHPCfg\AbstractVisitor;
use PHPCfg\Operand;
use PhpParser\Node;

class OperandAstNodeLinker extends AbstractVisitor {

	public function enterOp(Op $op, Block $block) {
		/** @var Node $ast_node */
		$ast_node = $op->getAstNode();

		if ($ast_node === null) return;

		$sub_node_mapper = array();
		$discard_var_names = array("result"); //no need to try, there is no result in Node\Expr
		if ($ast_node instanceof Node\Expr\AssignOp) {
			$sub_node_mapper["right"] = "expr"; // use the righthandside (if existing) to link to the expression
			$discard_var_names[] = "expr"; //discard the expression of the assignop, this is handled by the rhs
			$discard_var_names[] = "left"; //discard the left hand expression of binaryops, handled by assignop
		} else if ($ast_node instanceof Node\Expr\PostDec || $ast_node instanceof Node\Expr\PostInc ||
			$ast_node instanceof Node\Expr\PreDec || $ast_node instanceof Node\Expr\PreInc)
		{
			$sub_node_mapper["left"] = "var";
			$discard_var_names[] = "right";
		}

		foreach ($op->getVariableNames() as $var_name) {
			if (in_array($var_name, $discard_var_names, true) === true) {
				continue;
			}

			$cfg_var_name = $var_name;
			$ast_var_name = isset($sub_node_mapper[$var_name]) === false ? $var_name : $sub_node_mapper[$var_name];
			$sub_node_names = $ast_node->getSubNodeNames();

			if (($pos = array_search($ast_var_name, $sub_node_names, true)) !== false) {
				$cfg_entry = $op->$cfg_var_name;
				$ast_entry = $ast_node->{$sub_node_names[$pos]};
				if ($cfg_entry !== null && $ast_entry !== null) {
					$this->linkOperandToASTNode($cfg_entry, $ast_entry);
				}
			}
		}
	}

	/**
	 * @param Operand[] $operands
	 * @param Node[] $ast_nodes
	 * @throws \LogicException
	 * @throws \RuntimeException
	 */
	private function linkOperandsToAstNodes(array $operands, array $ast_nodes) {
		while (empty($operands) === false && empty($ast_nodes) === false) {
			$operand = array_pop($operands);
			$ast_node = array_pop($ast_nodes);
			$this->linkOperandToASTNode($operand, $ast_node);
		}
	}

	/**
	 * @param Operand|Operand[] $cfg_entry
	 * @param Node|Node[] $ast_entry
	 * @throws \RuntimeException
	 * @throws \LogicException
	 */
	private function linkOperandToASTNode($cfg_entry, $ast_entry) {
		if ($cfg_entry instanceof Operand && is_object($ast_entry)) {
			$cfg_entry->linkAstNode($ast_entry);
		} else if (is_array($cfg_entry) && is_array($ast_entry)) {
			$this->linkOperandsToAstNodes($cfg_entry, $ast_entry);
		} else if (is_object($cfg_entry) && is_array($ast_entry)) {
			//assuming that the cfg entry should be connected to each entry
			foreach ($ast_entry as $ast_node) {
				$this->linkOperandToASTNode($cfg_entry, $ast_node);
			}
		} else if (is_object($cfg_entry) && is_scalar($ast_entry)) {
			// no need to do anything
		} else {
			throw new \RuntimeException("Wrong type(s): " . gettype($cfg_entry) . " and/or " . gettype($ast_entry) . "\n");
		}
	}
}