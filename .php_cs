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
    ->notPath('lib/Html2Text.php')
    ->notPath('lib/makefont')
    ->notPath('lib/flourish')
    ->notPath('lib/phpqrcode')
    ->notPath('lib/Mobile_Detect.php')
    ->notPath('lib/fpdf.php')
    ->notPath('lib/minify')
    ->notPath('lib/font')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'none'],
        'phpdoc_align' => ['tags' => []],
        'trailing_comma_in_multiline_array' => false
    ])
    ->setIndent("\t")
    ->setFinder($finder)
;

?>



