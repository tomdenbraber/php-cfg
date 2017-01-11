<?php
namespace PHPCfg\Visitor;

use \PHPCfg\Operand;
use \PHPCfg\Block;
use \PHPCfg\Op;
use \PhpParser;

class OperandAstNodeLinkerTest extends \PHPUnit_Framework_TestCase {

	/** @var  OperandAstNodeLinker */
	private $operand_ast_node_linker;

	public function setUp() {
		$this->operand_ast_node_linker = new OperandAstNodeLinker();
	}

	public function testWithAssignOp() {
		$var_exp = $this->getMock(PhpParser\Node\Expr::class);
		$plus_exp = $this->getMock(PhpParser\Node\Expr::class);
		$ast_binary_op = new PhpParser\Node\Expr\AssignOp\Plus($var_exp, $plus_exp);

		$left_operand = $this->getMock(Operand::class);
		$right_operand = $this->getMock(Operand::class);
		$block = $this->getMock(Block::class);

		$cfg_assign_op = new Op\Expr\Assign($left_operand, $right_operand);
		$cfg_assign_op->var = $left_operand;

		$cfg_plus_op = new Op\Expr\BinaryOp\Plus($left_operand, $right_operand);
		$cfg_plus_op->right = $right_operand;

		$cfg_assign_op->linkAstNode($ast_binary_op);
		$cfg_plus_op->linkAstNode($ast_binary_op);

		$left_operand->expects($this->once())
			->method('linkAstNode')
			->with($var_exp);

		$right_operand->expects($this->once())
			->method('linkAstNode')
			->with($plus_exp);

		$this->operand_ast_node_linker->enterOp($cfg_assign_op, $block);
		$this->operand_ast_node_linker->enterOp($cfg_plus_op, $block);
	}
}