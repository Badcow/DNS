<?php

$header = <<<'EOF'
This file is part of Badcow DNS Library.

(c) Samuel Williams <sam@badcow.co>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/lib')
    ->in(__DIR__.'/tests')
    ->exclude(__DIR__.'/tests/Resources')
    ->exclude(__DIR__.'/tests/Parser/Resources')
;

$config = new PhpCsFixer\Config();
$config->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'header_comment' => ['header' => $header],
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'void_return' => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder)
;

return $config;