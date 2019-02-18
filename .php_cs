<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('docker')
    ->exclude('lang')
    ->exclude('fonts')
    ->exclude('chat')
    ->exclude('images')
    ->exclude('light')
    ->exclude('scripts')
    ->exclude('js')
    ->exclude('vendor')
    ->notPath('tmp')
    ->notPath('lib/font')
    ->notPath('tests/_support/_generated')
    ->notPath('src/Lib/Flourish')
    ->notPath('cache')
    ->notPath('client')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'none'],
        'phpdoc_align' => ['tags' => []],
        'trailing_comma_in_multiline_array' => false,
        'yoda_style' => null
    ])
    ->setIndent("\t")
    ->setFinder($finder)
;

?>



