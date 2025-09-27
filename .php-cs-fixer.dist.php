<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 * @see https://cs.symfony.com/doc/ruleSets/
 * @see https://cs.symfony.com/doc/rules/
 */

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/build/php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@PHP8x2Migration:risky' => true,
        '@PHP8x2Migration' => true,
        // symfony
        'array_indentation' => true,
        'class_attributes_separation' => true,
        'whitespace_after_comma_in_array' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'type_declaration_spaces' => true,
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arrays', 'match', 'arguments', 'parameters']],
        'no_blank_lines_after_phpdoc' => true,
        'object_operator_without_whitespace' => true,
        'binary_operator_spaces' => true,
        'phpdoc_scalar' => true,
        'no_trailing_comma_in_singleline' => true,
        'single_quote' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_unused_imports' => true,
        'yoda_style' => ['equal' => true, 'identical' => true, 'less_and_greater' => null],
        'standardize_not_equals' => true,
        'concat_space' => ['spacing' => 'one'],
        'linebreak_after_opening_tag' => true,
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => ['import_classes' => true],
        // symfony:risky
        'no_alias_functions' => true,
        'self_accessor' => true,
        // contrib
        'not_operator_with_successor_space' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const']], // @PSR12 sort_algorithm: none
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->append([__FILE__])
            ->exclude(['vendor', 'tools', 'build']),
    )
;
