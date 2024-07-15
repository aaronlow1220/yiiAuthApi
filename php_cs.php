<?php

declare(strict_types=1);
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in(__DIR__)
    ->exclude(['runtime', 'vendor']);

return (new Config())
    ->setRules([
        '@PhpCsFixer' => true,
        'class_definition' => ['single_item_single_line' => true],
        'ordered_imports' => ['case_sensitive' => true],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
        'phpdoc_separation' => ['skip_unlisted_annotations' => true],
        'phpdoc_no_empty_return' => false,
        'phpdoc_trim' => false,
        'phpdoc_order' => true,
        'phpdoc_param_order' => true,
        'no_superfluous_phpdoc_tags' => false,
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'phpdoc_align' => ['align' => 'left'],
        'single_line_comment_style' => false,
        'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder);
