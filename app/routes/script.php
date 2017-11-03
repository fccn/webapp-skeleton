<?php
/*
* handles requests to script, loads twig script templates
*/

#use Psr\Http\Message\ServerRequestInterface;
#use Psr\Http\Message\ResponseInterface;

/*
* Load an external js library from vendor or node_modules
*/
$app->get('/lib/{libname}', function ($request, $response, $args) {
    $contents = $this->loader->load($args['libname']);
    $new_resp = $response->withHeader('Content-type', 'application/javascript');
    $body = $new_resp->getBody();
    if(!empty($contents)){
      $this->logger->debug("GET script/lib - writing contents to response body");
      $body->write($contents);
    }else{
      $this->logger->error("GET script/lib - library <$args[libname]> not found");
      //send not found
      return $new_resp->withStatus(404);
    }
    return $new_resp;
})->setName('ext_libs');
