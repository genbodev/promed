<?php
$patterns = [
    [
        '/(with\s*)?\(\s*nolock\s*\)|\(\s*rowlock\s8\)/is', ''
    ],
    [
        '/outer apply/is', 'LEFT JOIN LATERAL'
    ],
    [
        '/cross apply/is', 'INNER JOIN LATERAL'
    ],
    [
        '/isnull/is', 'COALESCE'
    ],
    [
        '/convert\s*\(([^\,]+)\,\s*([^\,]+)\,\s*126\s*\)/is', 'to_char($2, \'YYYY-MM-DD"T"HH24:MI:SS\')'
    ],
    [
        '/convert\s*\(([^\,]+)\,\s*([^\,]+)\,\s*104\s*\)/is', 'to_char($2, \'DD.MM.YYYY\')'
    ],
    [
        '/convert\s*\(([^\,]+)\,\s*([^\,]+)\,\s*108\s*\)/is', 'to_char($2, \'HH24:MI:SS\')'
    ],
    [
        '/convert\s*\(([^\,]+)\,\s*([^\,]+)\,\s*120\s*\)/is', 'to_char($2, \'YYYY-MM-DD HH24:MI:SS\')'
    ],
    [
        '/convert\s*\(([^\,]+)\,\s*([^\,]+)\,\s*112\s*\)/is', 'to_char($2, \'YYYYMMDD\')'
    ],
    [
        '/(\+)\s{0,}\'/is', '||\''
    ],
    [
        '/\'\s{0,}(\+)/is', '\'||'
    ],
    [
        '/(len)\(/is', 'length('
    ],
    [
        '/date_part\(\s*([^\,]+)\,\s*(.*)\)/is', 'date_part(\'$1\', $2)'
    ],
    [
        '/datediff\(\s*([^\,]+)\,\s*(.*)\)/is', 'datediff(\'$1\', $2)'
    ],
    [
        '/year\(\s*([^\,]+)\)/is', 'date_part(\'year\', $1)'
    ],
    [
        '/month\(\s*([^\,]+)\)/is', 'date_part(\'month\', $1)'
    ],
    [
        '/day\(\s*([^\,]+)\)/is', 'date_part(\'day\', $1)'
    ],
    [
        '/\((select )(?:top (\d+))([^\)]+)?\)/is', '($1$3 limit $2)'
    ],
    [
        '/(left\s+join\s+lateral\s+\((?:[^\)])+?\)\s*[\w]+?\s)/is', '$1 on true'
    ],
    [
        '/(inner\s+join\s+lateral\s+\((?:[^\)])+?\)\s*[\w]+?\s)/is', '$1 on true'
    ],
    [
        '/\[/', ''
    ],
    [
        '/\]/', ''
    ]
];
$argv;

$models_dir = __DIR__ . '/promed/models/';

if (count($argv) < 2) {
    print_r('To low arguments');
    die();
}

if (!file_exists($models_dir . $argv[1])) {
    print_r('File ' . $models_dir . $argv[1] . ' doesnt exists!');
    die();
}

$source = fopen($models_dir . $argv[1], "r");

$new_content = '';

while(($line = fgets($source)) !== false) {

    foreach ($patterns as $pattern) {
        if (preg_match($pattern[0], $line)) {


            echo trim($line) . PHP_EOL;
            $replace = preg_replace($pattern[0], $pattern[1], $line) . PHP_EOL;
            echo trim($replace) . PHP_EOL;
            echo 'Confirm replace? (enter or n)' . PHP_EOL;
            $handle = fopen ("php://stdin","r");
            if(trim(fgets($handle)) != 'n'){
                $line = $replace;
                echo 'OK' . PHP_EOL;
            }
            fclose($handle);
        }
    }
    $new_content .= $line;
}

if (isset($argv[2])) {
    file_put_contents($models_dir. $argv[2], $new_content);
}
else {
    file_put_contents($models_dir. $argv[1], $new_content);
}

fclose($source);




