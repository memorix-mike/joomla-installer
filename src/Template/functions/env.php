<?php
declare(strict_types=1);

/**
 * env
 * Environment variables, based on a given key
 *
 * @return void
 */
function env(): void {

    $lines = file(getcwd() . '/../.env');

    foreach ($lines as $line) {
        $line = str_replace("\n",'', $line);
        if(!empty($line)) {

            [$key, $value]  = explode('=', $line, 2);
            $key            = trim($key);
            $value          = trim($value);

            putenv(sprintf('%s=%s', $key, $value));

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
