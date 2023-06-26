<?php
declare(strict_types=1);

/**
 * view
 * Includes template files with variables.
 *
 * @param string $name
 * @param array $variables
 * @param bool $return
 * @return void
 */
function view(string $name, array $variables = [], bool $return = false)
{
    $output = null;
    $views_dir = 'resources' . DIRECTORY_SEPARATOR . 'views';
    $paths = explode('.', $name);

    if($views_dir == $paths[0]) {
        unset($paths[0]);
    }

    array_unshift($paths, $views_dir);

    $path = implode(DIRECTORY_SEPARATOR, $paths);
    $path = get_template_directory() . DIRECTORY_SEPARATOR . $path . '.php';

    if(file_exists($path)) {
        if($return) {
            return include $path;
        }

        extract($variables);

        ob_start();
        include($path);
        $buffer = ob_get_clean();

        $search = [
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];

        $output = preg_replace($search, $replace, $buffer);
        $output = $buffer;
    }
    else {
        $output = 'Missing template file ' . $path;
    }

    echo $output;
}
