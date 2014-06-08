<?php
class WebServer {
  private $socket = null;
  private $max_clients = 20;
  private $max_body_size = 2097152; // 2 MiB
  private $callback_on_request;
  private $clients = array();

  const SSL_VERSION_3_0 = 'SSL-3.0';
  const TLS_VERSION_1_0 = 'TLS-1.0';
  const TLS_VERSION_1_1 = 'TLS-1.1';
  const TLS_VERSION_1_2 = 'TLS-1.2';

  const SSL_MESSAGE_TYPE_CLIENT_HELLO = 'ClientHello';

  private static $ssl_tls_versions = array(
    '30' => 'SSL-3.0',
    '31' => 'TLS-1.0',
    '32' => 'TLS-1.1',
    '33' => 'TLS-1.2',
  );

  public function __construct($callback_on_request) {
    if (!is_callable($callback_on_request)) {
      throw new ProgrammerException('Argument 1 must be a valid callback.');
    }

    $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    $this->callback_on_request = $callback_on_request;
  }

  public function setMaximumClients($n) {
    $n = (int)$n;

    if (!$n || $n < 0) {
      throw new ProgrammerException('Maximum clients must be integer of at least 1.');
    }

    $this->max_clients = $n;

    return $this;
  }

  public function setMaximumBodySize($n) {
    $n = (int)$n;

    if (!$n || $n < 0) {
      throw new ProgrammerException('Maximum body size accepted must be integer of at least 1 and should be a power of 2.');
    }

    $this->max_body_size = $n;

    return $this;
  }

  private function log($message) {
    $args = func_get_args();
    $message = array_shift($args);
    fprintf(STDOUT, $message, $args);
  }

  /**
   * Should be called last.
   *
   * @param integer $port
   * @param string $address
   */
  public function listen($port, $address = 'localhost') {
    $success = socket_bind($this->socket, $address, $port);

    if (!$success) {
      throw new WebServerException('Could not bind socket.');
    }

    $success = socket_listen($this->socket);

    if (!$success) {
      throw new WebServerException('Could not listen on %s:%d', $listening_address, $port);
    }

    while (!feof(STDIN)) {
      if (count($this->clients) === $this->max_clients) {
        $this->log('Too many clients connected.');
        continue;
      }

      $client_id = uniqid('WebServer::'.count($this->clients), true);
      $client = socket_accept($this->socket);
      $this->clients[$client_id] = $client;

      $input = socket_read($client, $this->max_body_size);

//       if ('0x'.bin2hex($input[0]) == 0x16) { // SSL handshake
//         $version_major = ord($input[1]);
//         $version_minor = ord($input[2]);
//
//         $version = self::$ssl_tls_versions[$version_major.$version_minor];
//
//         $length = unpack('n*', substr($input, 3, 2));
//         $length = (int)$length[1];
//
//         $message_type = ord($input[5]);
//         $message_length = unpack('n*', substr($input, 6, 3));
//         $message_length = (int)$message_length[1];
//
//         $message_data = substr($input, 9, $message_length - 1);
//
//         $server_hello  = str_pad(pack('n*', '22'), 3, "\0");
//         $server_hello .= pack('n*', mt_rand());
//         $server_hello .= pack('n*', '31').chr(0).chr(1);
//         $server_hello .= pack('n*', '2').str_pad(pack('n*', 0), 3, "\0");
//
// //         $change_cipher_spec  = str_pad(pack('H*', '14'), 3, "\0");
// //         $change_cipher_spec .= pack('n*', '31').chr(0).chr(1);
// //         $change_cipher_spec .= pack('n*', '1');
// //
//         socket_write($client, $server_hello);
//         continue;
//       }

      $request = new Request($input);
      $response = new Response();

      call_user_func($this->callback_on_request, $request, $response);

      $output = join("\r\n", array_merge($response->getHeaders(), array(
        '',
        $response->getContent().chr(0),
      )));

      socket_write($client, $output);
      socket_close($client);

      unset($this->clients[$client_id]);
    }

    return $this;
  }

  public function __destruct() {
    if ($this->socket !== null) {
      socket_close($this->socket);
    }
  }
}

function WebServer($arg) {
  return new WebServer($arg);
}
