<?php
declare(strict_types=1);

/**
 * get_template_directory
 * return the template directory folder
 *
 * @return string
 */
function get_template_directory() {

    return $dir = getcwd() . '/templates/' . getenv('TEMPLATE_NAME');
}
