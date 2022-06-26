<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('resources')
    ->exclude('tests/fixtures')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@PSR12' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
