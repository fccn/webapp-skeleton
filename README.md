#Slim framework webapp skeleton

This skeleton application was created to quickly setup and start working on a new web application based on Slim Framework 3. This application uses the latest Slim 3 with the Twig template renderer. It uses the Monolog logger and comes with a preset of tools to speed up application development.

The skeleton application and the tools were built for Composer, which makes setting up a new application quick and easy.

## Installation

You can install this project using composer:
```
composer create-project fccn/webapp-skeleton .

```
This installs the project in the current folder and runs composer install.

After obtaining the project
1. run ```composer install``` to install composer components (if not obtained via ```composer create-project```)
1. run ```npm install``` to install grunt and additional node components
1. run ```grunt bootstrap``` to initialize the project

## Configuration


## Project folder structure

After installing the project you will find the following structure inside the project's folder:

- /app - location of application source code
- /html - html assets, web server root
 - /assets - images, icons and fonts
 - /js - local javascripts
- less - template for the page stylesheet
- lib - local libraries
- locale - translations
- templates - twig templates for the webpages
- utilities - programs and utilities
