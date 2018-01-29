# Additional middlewares

Add your middlewares to this folder in PHP files. The files are imported from **app/middleware.php**, providing direct access to the Slim3 $app variable.
To add middleware just create a PHP file with the following contents:
```php
<? php
$app->add(new MySpecializedMiddleware());

```
