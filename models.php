<?php

$models = [];

$patterns = [
    '/from\s+([^\s]+)|join\s+([^\s]+)/im',
    '/join\s+([^\s]+)/im',
];

$models_dir = __DIR__ . '/promed/models/';

if (count($argv) < 2) {
    print_r('To low arguments');
    die();
}

if (!file_exists($models_dir . $argv[1])) {
    print_r('File ' . $models_dir . $argv[1] . ' doesnt exists!');
    die();
}

$source = file_get_contents($models_dir . $argv[1]);

preg_match_all('/from\s+([^\s|^\(|^=]+)|join\s+([^\s|^\(|^=]+)/im', $source, $matches);

array_filter($matches[1], function($k, $v) {
    return !in_array($v, ['', '--', '.=', '=', 'where']);
}, ARRAY_FILTER_USE_BOTH);

array_filter($matches[2], function($k, $v) {
    return !in_array($v, ['', '--', '.=', '=', 'where']);
}, ARRAY_FILTER_USE_BOTH);

$models = array_unique(array_merge($matches[1], $matches[2]));

foreach ($models as $i) {
    echo $i . PHP_EOL;
}