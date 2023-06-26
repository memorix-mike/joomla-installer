<?php
require(__DIR__ . '/../../../vendor/autoload.php');
require(__DIR__ . '/../../functions/_loader.php');
require(__DIR__ . '/../../functions/_global.php');

view('layout.header', compact('metaData'));

//
if($menu->home == 1) {
    view('homepage');
}
elseif($menu->component == 'com_content') {
    switch($menu->alias) {
        case 'categorieen':
            view('articles.single');
            break;

        default:
            view('error');
    };
}
else {
    switch($menu->component) {
        case 'com_newsfeeds':
        case 'com_users':
            view('pages.default');
            break;

        default:
            view('error');
    }
}

view('layout.footer', compact('metaData'));
