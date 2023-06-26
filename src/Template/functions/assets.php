<?php
function asset(string $assetName): string {

    static $manifest;

    if (
        !isset($manifest)
        && file_exists(get_template_directory() . '/dist/manifest.json')
    ) {

        $manifest = file_get_contents(get_template_directory() .'/dist/manifest.json');
        $manifest = json_decode($manifest, true);
    }
    if (!isset($manifest['resources/' . $assetName])) {
        return $assetName;
    }

    return  '/dist/' .  $manifest['resources/' . $assetName]['file'];
}
