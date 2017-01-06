# Webapp Skeleton
Slim + Twig + Idiorm + SAML2 Skeleton app with login, applog and localization

## Objective
Create a basic Slim Micro PHP Framework with Twig, Idiorm and SAML2 to bootstrap a web application with the following components:
- Site stylesheet definition uses less and grunt tasks to generate css.
- Data models for User and Application Logs
- Localization and Translations prepared for Twig
- Singleton File Logger using Monolog

## Project Structure
/
- app (application libs and core files)
- conf (application configurations)
  - apache (apache config files)
- html (holds html assets)
  - assets (use this folder to store images, icons and fonts)
  - css (location of the css stylesheet originated from compiling less. do not place css stylesheets here, use the less folder for that)
- js (use this folder to store application specific javascripts)
- less (template for the page stylesheet)
- locale (translations)
- models (php classes for database models)
- templates (twig templates for the webpages)
- utilities (programs and utilities)

## Install
### Prepare for development environment
1. Install this code tree on a directory at your choice
1. Validate that all the needed PHP extensions are installed
1. Configure the project to your own settings
  1. Change application name in app config files *package.json* and *composer.json*
  1. Add project specific dependencies to *package.json* and *composer.json*
  1. Create and customize configuration file in *app/config.php* using *app/config-dist.php* as reference
  1. Customize *html/.htaccess* file
    1. Define application environment in **SetEnv SLIM_MODE**
  1. Create an apache configuration file (.conf) using *conf/apache/webapp.conf.sample* as reference
    1. Define port, server name and home variables in this file
1. Install composer
  1. Go to [composer download page](https://getcomposer.org/download/) and folow the instructions to download composer.
  1. On the project root run **./composer.phar install** to install composer dependencies.
1. Install npm
  1. On CentOS run **yum instal npm** to install npm
  1. Run **npm install -g grunt-cli** to install grunt client
  1. Run **npm install** to install grunt and required project dependencies
1. Run grunt init tasks
  1. run **grunt bootstrap** for preparing app css styles
  1. run **grunt&** for listening for changes in less folder and updating application stylesheet
1. Configure apache using the configuration file in *conf/apache/webapp.conf*
1. Create and configure database for application. Define dabase access in *app/config.php*.
  1. Create tables for models defined in *models* folder. Script in *utils/db/gen_mysql.php* shows how this can be done with the sample models for **User** and **AppLog**. The code in *utils/db/gen_mysql.php* is directed for MySQL databases.
1. start coding your app
  1. Use the templates folder for developing the webpages. Use twig as templating engine.
  1. Use the less folder for developing the site style.
  1. In *html/assets/ico* folder there is a set of favicons that can be changed with the site brand. Use [favicon generator](http://www.favicon-generator.org/) to generate the required files from a single image.
  1. If you need debug info, set the environment variable "deploy_mode" to "development".

### Prepare for deployment
1. Install project dependencies
  1. Run **./composer.phar update** to install composer dependencies
  1. Run **npm install** to install npm dependencies
  1. Run **grunt dist** to prepare final stylesheet for
1. Configure SimpleSAML authentication
