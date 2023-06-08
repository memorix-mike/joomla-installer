<?php
foreach(glob(__DIR__ . '/*.php') as $filename) {
    include_once($filename);
}

env();
