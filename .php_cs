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
    ->notPath('lib/fpdi')
    ->notPath('lib/makefont')
    ->notPath('lib/flourish/f')
    ->notPath('lib/phpqrcode')
    ->notPath('lib/Mobile_Detect.php')
    ->notPath('lib/minify')
    ->notPath('lib/font')
    ->notPath('tests/_support/_generated')
    ->notPath('src/Lib/Flourish')
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



