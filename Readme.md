# Picturae Joomla Tool
***

This Joomla tool handles a set-up of a Docker container and creates a fresh install of Joomla V4 or updates the current version to the latest version.

***

## Requirements ##

|              |       Version |
|:-------------|--------------:|
| **PHP**      | ``7.4 - 8.2`` |
| **Composer** |      ``V2.0`` |
| **Node**     |     ``V16.0`` |

## Installation ##
1. Rename ``.env.example`` to ``.env``
2. Enter the correct project information in the ``.env`` file.
3. Run ``` $ docker ... ```

This command will create a new Docker container with pre-defined information.
Docker will automatically run ``$ composer install`` to install this installer.

## Run the installer ##
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
