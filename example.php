<?php
const LISTENING_IP = '192.168.1.100';

$dir = getcwd();

require $dir.'/GenericException.php';
require $dir.'/ProgrammerException.php';
require $dir.'/WebServerException.php';
require $dir.'/Grammar.php';
require $dir.'/Request.php';
require $dir.'/Response.php';
require $dir.'/WebServer.php';

try {
  WebServer(
    /**
     * Called on each request.
     *
     * @param Request  $req Request object.
     * @param Response $res Response object.
     *
     * @return void
     */
    function ($req, $res) {
      $res->setContentType('text/html');
      $path = $req->getPath();

      if ($path === '/') {
        $res->setStatusCode(Response::STATUS_OK);
        $res->setContent('This is the homepage!');
      }
      else {
        $res->setStatusCode(Response::STATUS_NOT_FOUND);
        $res->setContent(str_pad(sprintf('Not found (%s)!', $path), 2048));
      }
    }
  )->listen(1337, LISTENING_IP);
}
catch (Exception $e) {
  print $e."\n";
}
