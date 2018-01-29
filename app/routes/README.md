# Additional routes

Add your routing configuration to this folder in PHP files. The files are imported from **app/routes.php**, providing direct access to the Slim3 *$app* variable.
To add a route just create a PHP file with the following contents:
```php
<? php

#controller action inside a function
$app->get('/my/url', function (Request $request, Response $response, array $args) {
  #code to handle this route....
};

#controller as an invokable class
$app->get('/another/url/{param}', MyControllerAction::class);

#handle routes inside a group
$app->group('/path',function(){

  #controller of url under /path
  $app->get('/some/url', function (Request $request, Response $response, array $args) {
    #code to handle this route....
  };

});

#other routes you might what to add...

```
