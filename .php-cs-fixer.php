<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->notPath('bootstrap.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'php_unit_test_class_requires_covers' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php-cs-fixer.cache');
