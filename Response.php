<?php
class Response {
  const STATUS_OK = 200;
  const STATUS_NOT_FOUND = 404;

  private static $status_code_messages = array(
    200 => 'OK',
    404 => 'Not Found',
  );

  private $status_code = 200;
  private $content_type = 'text/plain; charset=utf-8';
  private $content = '';
  private $server_name = 'php-server';
  private $keepalive_timeout = 20;
  private $cache_control = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
  private $pragma = 'no-cache';
  private $custom_headers = array();

  public function setStatusCode($code) {
    $this->status_code = $code;
    return $this;
  }

  public function getStatusMessage() {
    return join(' ', array(
      'HTTP/1.1',
      $this->status_code,
      self::$status_code_messages[$this->status_code],
    ));
  }

  public function setContentType($content_type, $charset = 'utf-8') {
    $this->content_type = $content_type.'; charset=utf-8';
    return $this;
  }

  public function getContentType() {
    return $this->content_type;
  }

  public function setContent($content) {
    $this->content = $content;
    return $this;
  }

  public function getContent() {
    return $this->content;
  }

  public function setHeader($key, $value) {
    $this->custom_headers[$key] = urlencode($value);
    return $this;
  }

  public function getCustomHeaders($as_strings = FALSE) {
    if ($as_strings) {
      $return = array();

      foreach ($this->custom_headers as $key => $value) {
        $return[] = $key.': '.$value;
      }

      return $return;
    }

    return $this->custom_headers;
  }

  public function getHeaders() {
    return array_merge(array(
      $this->getStatusMessage(),
      'Server: '.$this->server_name,
      'Content-Type: '.$this->content_type,
      'Connection: keep-alive',
      'Keep-Alive: timeout='.$this->keepalive_timeout,
      'Cache-Control: '.$this->cache_control,
      'Pragma: '.$this->pragma,
    ), $this->getCustomHeaders(TRUE));
  }
}
