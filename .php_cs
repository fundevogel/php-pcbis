<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude([
      'languages'
      'example',
      'dist'
    ])
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder)
;
