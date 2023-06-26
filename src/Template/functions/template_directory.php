<?php
declare(strict_types=1);

/**
 * get_template_directory
 * return the template directory folder
 *
 * @return string
 */
function get_template_directory() {
    return $_SERVER['DOCUMENT_ROOT'] . '/templates/' . getenv('TEMPLATE_NAME');
}

function get_public_template_path() {
    return $_SERVER['DOCUMENT_ROOT'] . '/templates/' . getenv('TEMPLATE_NAME');
}
