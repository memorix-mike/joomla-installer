<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=1.0">

    <title><?php echo $metaData['sitename']; ?></title>
    <meta name="description" content="Test template for Joomla 4 Installer">
    <meta name="robots" content="index, follow">

    <link rel="canonical" href="<?php echo getenv('SITE_URL'); ?>">

    <link rel="preconnect" href="/">
    <link rel="stylesheet" href="<?php echo $metaData['template'] . asset('sass/app.scss'); ?>">
</head>
<body>
