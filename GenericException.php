<?php
class GenericException extends Exception {
  public function __construct($message = '', $code = 0, Exception $previous = NULL) {
    $args = func_get_args();
    $message = array_shift($args);
    $this->message = sprintf($message, $args);
  }

  public function __toString() {
    return $this->message;
  }
}
