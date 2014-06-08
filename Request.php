<?php
class Request {
  const EMPTY_STRING_PLACEHOLDER = '{empty string}';

  private $headers = array();
  private $method = 'GET';
  private $path = '/';
  private $query_string = '';
  private $query = array();

  public function __construct($input_string) {
    $input = $input_string ? preg_split('/\r\n/', $input_string) : array(self::EMPTY_STRING_PLACEHOLDER);

    $this->parseHeaders($input);
  }

  public static function cast($value, $type = NULL) {
    if ($type === NULL) {
      return $value;
    }

    switch ($type) {
      case 'int':
        $value = (int)$value;
        break;
    }

    return $value;
  }

  public function get($param, $type = NULL, $default = NULL, $use_default_for_blank = TRUE) {
    if (isset($this->query[$param])) {
      if ($this->query[$param] === '' && $use_default_for_blank) {
        return $default;
      }

      return self::cast($this->query[$param], $type);
    }

    return $default;
  }

  private function parseHeaders(array $input) {
    if ($input[0] === self::EMPTY_STRING_PLACEHOLDER) {
      return;
    }

    $headers = array();
    $other_lines = array();

    foreach ($input as $header_piece) {
      if (strpos($header_piece, ':') !== FALSE) {
        $parts = explode(':', $header_piece, 2);

        if (count($parts) === 2) {
          $key = $parts[0];
          $value = $parts[1];
          $headers[strtolower($key)] = $value;
        }
        else {
          $other_lines[] = $header_piece;
        }
      }
      else {
        $other_lines[] = $header_piece;
      }
    }

    $this->headers = $headers;

    foreach ($other_lines as $line) {
      $line = trim($line);
      $matches = array();

      if (preg_match('/^(GET|POST|PUT|DELETE|LINK)(?:\s+)(\/.+)\s/', $line, $matches)) {
        $this->method = $matches[1];

        if (($pos = strpos($matches[2], '?')) !== false) {
          $this->query_string = substr($matches[2], $pos);
          $this->path = urldecode(substr($matches[2], 0, $pos));
        }
        else {
          $this->path = urldecode($matches[2]);
        }

        $query = preg_split('/\&/', substr($this->query_string, 1));
        foreach ($query as $value) {
          $values = explode('=', $value);

          if (count($values) !== 2) {
            continue;
          }

          $key = $value[0];
          $value = isset($value[1]) ? $value[1] : '';
          $this->query[$key] = urldecode($value);
        }
      }
    }
  }

  public function getMethod() {
    return $this->method;
  }

  public function getPath() {
    return $this->path;
  }

  public function __call($method, $parameters) {
    $verb = substr($method, 0, 3);
    $subject = substr($method, 3);
    $valid = array('set', 'get');
    $ret = null;

    if (!in_array($verb, $valid)) {
      return;
    }

    $subject = strtolower(Grammar::dashize($subject));

    if ($verb === 'get') {
      if (isset($this->headers[$subject])) {
        $ret = $this->headers[$subject];
      }
    }
    else {
      $ret = $this;
      $this->headers[$subject] = $parameters[0];
    }

    return $ret;
  }
}
