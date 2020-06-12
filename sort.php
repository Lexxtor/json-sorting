#!/usr/bin/env php
<?php

$address = $argv[1] ?? null;

set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});


try {
    // приём и разбор JSON
    $teams = json_decode(file_get_contents($address), true, 512, JSON_THROW_ON_ERROR);

    // сортировка
    usort($teams, function($a, $b) {
        return $a['scores'] < $b['scores'];
    });

    // проставление рангов
    foreach ($teams as $key => &$team) {
        if ($key == 0) {
            $rank = 1;
        } elseif ($teams[$key]['scores'] != $teams[$key-1]['scores']) {
            $rank++;
        }
        $team['rank'] = $rank;
    }

    // вывод
    fwrite(STDOUT, json_encode($teams));

} catch (Exception $e) {
    // обработка ошибок
    if ($e instanceof JsonException) {
        fwrite(STDERR, 'Ошибка парсинга JSON.');
    }
    else {
        if (stripos($e->getMessage(), 'Undefined index') === 0) {
            fwrite(STDERR, 'Ошибка: неправильный формат JSON.');
        }
        else {
            fwrite(STDERR, $e->getMessage());
        }
    }
}