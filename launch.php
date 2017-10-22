<?php
define('BASE_ROOT', __DIR__);
require_once BASE_ROOT . '/vendor/autoload.php'; // set up autoloading
$di = new \Slim\Container();
$di['pdo'] = function($di) {
  $dsn = sprintf("mysql:dbname=%s;unix_socket=%s;charset=utf8mb4",
    $di['config']['db.name'],
    $di['config']['db.sock']);
  $pdo = new \PDO(
    $dsn,
    $di['config']['db.user'],
    $di['config']['db.pass'],
    array(
      \PDO::ATTR_PERSISTENT => false,
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    )
  );
  return $pdo;
};
$app = new \Slim\App($di);
/**
 * RESTful API with the following conventions:
 *           Action         Collection(e.g. /projects)  Item(e.g. /projects{id})
 *   POST    Create         201, link to item           404
 *   GET     Read           200, list of ids            200 if found, 404 if not
 *   PUT     Update/Replace 404                         200 or 404
 *   DELETE  Delete         404                         200 or 404
 *
 * Parents hold the children, so for example we can have the following paths:
 *   /folders
 *   /folders/5
 */
$app->group('/api', function() {
  $this->group('/folders', function() {
    $this->get('', function($request, $response) {
      $response->getBody()->write('List of folders');
      return $response;
    });
    $this->post('', function($request, $response) {
      $response->getBody()->write('Create folder');
      return $response;
    });
    $this->group('/{folder_id:[0-9]+}', function() {
      $this->get('', function($request, $response, $args) {
        $response->getBody()->write('Folder with id ' . $args['folder_id']);
        return $response;
      });
      $this->put('', function($request, $response, $args) {
        $response->getBody()->write('Update folder with id ' . $args['folder_id']);
        return $response;
      });
      $this->delete('', function($request, $response, $args) {
        $response->getBody()->write('Delete folder with id ' . $args['folder_id']);
        return $response;
      });
    });
  });
});
$app->run();
