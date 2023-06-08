# Picturae Joomla Tool
***
[![for-the-badge](http://poser.pugx.org/picturae-mike/joomla-installer/v?style=for-the-badge)](https://packagist.org/packages/picturae-mike/joomla-installer) 
[![for-the-badge](http://poser.pugx.org/picturae-mike/joomla-installer/license?style=for-the-badge)](https://packagist.org/packages/picturae-mike/joomla-installer) 

This Joomla tool handles a set-up of a Docker container and creates a fresh install of Joomla V4 or updates the current version to the latest version.

***

## Requirements ##

|              |   Version |
|:-------------|----------:|
| **PHP**      | ``> 8.0`` |
| **Composer** |  ``V2.0`` |
| **Node**     | ``V16.0`` |

## Installation ##
1. Rename ``.env.example`` to ``.env``
2. Enter the correct project information in the ``.env`` file.
3. Run ``` $ docker ... ```

This command will create a new Docker container with pre-defined information.
Docker will automatically run ``$ composer install`` to install this installer.

## Run the installer (manually) ##
```php vendor/bin/console picturae:install```

## Installation flow ##
1. Install or update
   1. **Install**
      1. Download the latest version of Joomla ``V4.*``
      2. Unzip the downloaded package
      3. Install the downloaded package with Joomla-CLI
      4. Remove the Joomla installation files
      5. Move the Joomla installation to the ``./public`` folder of the project
      6. Install NPM
      7. Install Vite for asset-building
   2. **Update**
      1. Update the current version with Joomla-CLI 
2. Finalizing, ``$ docker up`` and serve the website under ``https://website-name.test`` 

## Building the assets (test) ##
```cd src/templates/${TEMPLATE_FOLDER}```

```npm install```

```npm run build```


## Template stucture ##
If this is a new install of the website, this script also creates a ``starter-template`` with a basic setup.
The information for this starter-temlate will be fetched from the .env file as used during installation.

```
/${BUILD_TARGET}
   /administrator
   /components
   /language
   /modules
   /plugins
   /templates
      /${TEMPLATE_NAME}
         ...
         ... // front-end structure for Vite.js
```

In order to make the starter-template work with the Joomla installation,
we create a ``symlink`` from ``./public/templates`` to 
``./${BUILD_TARGET}/templates/${TEMPLATE_NAME}``.


## Available packages for Joomla 4 ##
No packages available (yet).
