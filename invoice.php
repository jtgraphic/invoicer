<?php

$template = file_get_contents('invoice.txt');

$data = json_decode(file_get_contents('data.json'), TRUE);

$loop_pattern = '/{#([A-Za-z0-9_]+)}(.*){\/[A-Za-z0-9_]+}/s';
$token_pattern = '/{([A-Za-z0-9_]+(?:\|[0-9]+)?)}/';

preg_match_all($loop_pattern, $template, $matches);

$first = TRUE;

foreach ($matches[1] as $index => $loop_key) {
    preg_match_all($token_pattern, $matches[2][$index], $loop_matches);
    $matches[2][$index] = trim($matches[2][$index]);
    
    foreach ($data[$loop_key] as $row) {
        $working_row = $matches[2][$index];

        foreach ($loop_matches[1] as $key) {
            $key_data = explode('|', $key);

            $value = $row[$key_data[0]];
            $value = str_pad($value, $key_data[1]);

            $working_row = str_replace('{'.$key.'}', $value, $working_row);
        }

        if (!$first) {
            $output .= "\n";
        }

        $output .= $working_row;

        $first = FALSE;
    }
}

$output = str_replace('$', '\$', $output);

$template = preg_replace($loop_pattern, $output, $template);

preg_match_all($token_pattern, $template, $matches);

foreach ($matches[1] as $key) {
    if (!isset($data[$key])) {
        $data[$key] = '';
    }

    $template = str_replace('{'.$key.'}', $data[$key], $template);
}

echo $template;

echo "\n";
