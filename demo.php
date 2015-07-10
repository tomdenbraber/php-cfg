<?php

require "vendor/autoload.php";

$astTraverser = new PhpParser\NodeTraverser;
$astTraverser->addVisitor(new PhpParser\NodeVisitor\NameResolver);
$parser = new PHPCfg\Parser(new PhpParser\Parser(new PhpParser\Lexer), $astTraverser);

$declarations = new PHPCfg\Visitor\DeclarationFinder;
$calls = new PHPCfg\Visitor\CallFinder;
$variables = new PHPCfg\Visitor\VariableFinder;

$traverser = new PHPCfg\Traverser;

$traverser->addVisitor($declarations);
$traverser->addVisitor($calls);
$traverser->addVisitor(new PHPCfg\Visitor\Simplifier);
$traverser->addVisitor(new PHPCfg\Visitor\VariableDagComputer);
$traverser->addVisitor($variables);

$code = <<<'EOF'
<?php
function foo($a) {
	$b = $a + 1;
	$a = $b + 1;
}
EOF;


$block = $parser->parse($code, __FILE__);
$traverser->traverse($block);

$dumper = new PHPCfg\Dumper;
echo $dumper->dump($block);

$scanner = new PHPSQLiScanner\Scanner;

$scanner->scan($calls);