<?php
function asset(string $assetName): string {

    static $manifest;

    dd(get_template_directory());

    if (
        !isset($manifest)
        && file_exists(get_template_directory() . '/dist/manifest.json')
    ) {
        $manifest = file_get_contents(get_template_directory() .'/dist/manifest.json');
        $manifest = json_decode($manifest, true);
    }
    if (!isset($manifest[$assetName])) {
        return $assetName;
    }

    return $manifest[$assetName];
}
