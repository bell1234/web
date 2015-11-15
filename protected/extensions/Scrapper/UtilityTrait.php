<?php

trait UtilityTrait
{
  public function getLastJsonError()
  {
    if(! function_exists('json_last_error_msg'))
    {
      switch (json_last_error())
      {
        case JSON_ERROR_NONE:
          $_e = 'No errors';
          break;
        case JSON_ERROR_DEPTH:
          $_e = 'Maximum stack depth exceeded';
          break;
        case JSON_ERROR_STATE_MISMATCH:
          $_e = 'Underflow or the modes mismatch';
          break;
        case JSON_ERROR_CTRL_CHAR:
          $_e = 'Unexpected control character found';
          break;
        case JSON_ERROR_SYNTAX:
          $_e = 'Syntax error, malformed JSON';
          break;
        case JSON_ERROR_UTF8:
          $_e = 'Malformed UTF-8 characters, possibly incorrectly encoded';
          break;
        case JSON_ERROR_RECURSION:
          $_e = 'One or more recursive references in the value to be encoded';
          break;
        case JSON_ERROR_INF_OR_NAN:
          $_e = 'One or more NAN or INF values in the value to be encoded';
          break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
          $_e = 'A value of a type that cannot be encoded was given';
          break;
        default:
          $_e = 'Unknown error';
      }
      return $_e;
    }

    return json_last_error_msg();
  }

  private function timerRead($name) {
    //global $this->timers;
    if (isset($this->timers[$name]['start'])) {
      $stop = microtime(TRUE);
      $diff = round(($stop - $this->timers[$name]['start']) * 1000, 2);
      if (isset($this->timers[$name]['time'])) {
        $diff += $this->timers[$name]['time'];
      }
      return $diff;
    }
    return $this->timers[$name]['time'];
  }
  private function timerstart($name) {
    //global $this->timers;
    $this->timers[$name]['start'] = microtime(TRUE);
    $this->timers[$name]['count'] = isset($this->timers[$name]['count']) ? ++$this->timers[$name]['count'] : 1;
  }

  /*
   * Method for sending HTTP Request
   */
  protected function extendedHttpRequest($url, array $options = array()) {
//    $result = new stdClass();
      $result = array();
      $result = (object)$result;
    // Parse the URL and make sure we can handle the schema.
    $uri = @parse_url($url);
    if ($uri == FALSE) {
      $result->error = 'unable to parse URL';
      $result->code = -1001;
      return $result;
    }
    if (!isset($uri['scheme'])) {
      $result->error = 'missing schema';
      $result->code = -1002;
      return $result;
    }
    $this->timerstart(__FUNCTION__);
    // Merge the default options.
    $options += array(
      'headers' => array(),
      'method' => 'GET',
      'data' => NULL,
      'max_redirects' => 3,
      'timeout' => 30.0,
      'context' => NULL,
    );
    // Merge the default headers.
    $options['headers'] += array(
      'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36',
    );
    // stream_socket_client() requires timeout to be a float.
    $options['timeout'] = (float) $options['timeout'];
    // Use a proxy if one is defined and the host is not on the excluded list.
    $proxy_server = '';
    if ($proxy_server && _drupal_http_use_proxy($uri['host'])) {
      // Set the scheme so we open a socket to the proxy server.
      $uri['scheme'] = 'proxy';
      // Set the path to be the full URL.
      $uri['path'] = $url;
      // Since the URL is passed as the path, we won't use the parsed query.
      unset($uri['query']);
      // Add in username and password to Proxy-Authorization header if needed.
      if ($proxy_username = '') {
        $proxy_password = '';
        $options['headers']['Proxy-Authorization'] = 'Basic ' . base64_encode($proxy_username . (!empty($proxy_password) ? ":" . $proxy_password : ''));
      }
      // Some proxies reject requests with any User-Agent headers, while others
      // require a specific one.
      $proxy_user_agent = '';
      // The default value matches neither condition.
      if ($proxy_user_agent === NULL) {
        unset($options['headers']['User-Agent']);
      }
      elseif ($proxy_user_agent) {
        $options['headers']['User-Agent'] = $proxy_user_agent;
      }
    }
    switch ($uri['scheme']) {
      case 'proxy':
        // Make the socket connection to a proxy server.
        $socket = 'tcp://' . $proxy_server . ':' . 8080;
        // The Host header still needs to match the real request.
        $options['headers']['Host'] = $uri['host'];
        $options['headers']['Host'] .= isset($uri['port']) && $uri['port'] != 80 ? ':' . $uri['port'] : '';
        break;
      case 'http':
      case 'feed':
        $port = isset($uri['port']) ? $uri['port'] : 80;
        $socket = 'tcp://' . $uri['host'] . ':' . $port;
        // RFC 2616: "non-standard ports MUST, default ports MAY be included".
        // We don't add the standard port to prevent from breaking rewrite rules
        // checking the host that do not take into account the port number.
        $options['headers']['Host'] = $uri['host'] . ($port != 80 ? ':' . $port : '');
        break;
      case 'https':
        // Note: Only works when PHP is compiled with OpenSSL support.
        $port = isset($uri['port']) ? $uri['port'] : 443;
        $socket = 'ssl://' . $uri['host'] . ':' . $port;
        $options['headers']['Host'] = $uri['host'] . ($port != 443 ? ':' . $port : '');
        break;
      default:
        $result->error = 'invalid schema ' . $uri['scheme'];
        $result->code = -1003;
        return $result;
    }
    if (empty($options['context'])) {
      $fp = @stream_socket_client($socket, $errno, $errstr, $options['timeout']);
    }
    else {
      // Create a stream with context. Allows verification of a SSL certificate.
      $fp = @stream_socket_client($socket, $errno, $errstr, $options['timeout'], STREAM_CLIENT_CONNECT, $options['context']);
    }
    // Make sure the socket opened properly.
    if (!$fp) {
      // When a network error occurs, we use a negative number so it does not
      // clash with the HTTP status codes.
      $result->code = -$errno;
      $result->error = trim($errstr) ? trim($errstr) : 'Error opening socket ' . $socket;
      // Mark that this request failed. This will trigger a check of the web
      // server's ability to make outgoing HTTP requests the next time that
      // requirements checking is performed.
      // See system_requirements().
      //variable_set('drupal_http_request_fails', TRUE);
      return $result;
    }
    // Construct the path to act on.
    $path = isset($uri['path']) ? $uri['path'] : '/';
    if (isset($uri['query'])) {
      $path .= '?' . $uri['query'];
    }
    // Only add Content-Length if we actually have any content or if it is a POST
    // or PUT request. Some non-standard servers get confused by Content-Length in
    // at least HEAD/GET requests, and Squid always requires Content-Length in
    // POST/PUT requests.
    $content_length = strlen($options['data']);
    if ($content_length > 0 || $options['method'] == 'POST' || $options['method'] == 'PUT') {
      $options['headers']['Content-Length'] = $content_length;
    }
    // If the server URL has a user then attempt to use basic authentication.
    if (isset($uri['user'])) {
      $options['headers']['Authorization'] = 'Basic ' . base64_encode($uri['user'] . (isset($uri['pass']) ? ':' . $uri['pass'] : ''));
    }
    // If the database prefix is being used by SimpleTest to run the tests in a copied
    // database then set the user-agent header to the database prefix so that any
    // calls to other Drupal pages will run the SimpleTest prefixed database. The
    // user-agent is used to ensure that multiple testing sessions running at the
    // same time won't interfere with each other as they would if the database
    // prefix were stored statically in a file or database variable.
    $test_info = &$GLOBALS['drupal_test_info'];
    if (!empty($test_info['test_run_id'])) {
      $options['headers']['User-Agent'] = drupal_generate_test_ua($test_info['test_run_id']);
    }
    $request = $options['method'] . ' ' . $path . " HTTP/1.0\r\n";
    foreach ($options['headers'] as $name => $value) {
      $request .= $name . ': ' . trim($value) . "\r\n";
    }
    $request .= "\r\n" . $options['data'];
    $result->request = $request;
    // Calculate how much time is left of the original timeout value.
    $timeout = $options['timeout'] - $this->timerRead(__FUNCTION__) / 1000;
    if ($timeout > 0) {
      stream_set_timeout($fp, floor($timeout), floor(1000000 * fmod($timeout, 1)));
      fwrite($fp, $request);
    }
    // Fetch response. Due to PHP bugs like http://bugs.php.net/bug.php?id=43782
    // and http://bugs.php.net/bug.php?id=46049 we can't rely on feof(), but
    // instead must invoke stream_get_meta_data() each iteration.
    $info = stream_get_meta_data($fp);
    $alive = !$info['eof'] && !$info['timed_out'];
    $response = '';
    while ($alive) {
      // Calculate how much time is left of the original timeout value.
      $timeout = $options['timeout'] - $this->timerRead(__FUNCTION__) / 1000;
      if ($timeout <= 0) {
        $info['timed_out'] = TRUE;
        break;
      }
      stream_set_timeout($fp, floor($timeout), floor(1000000 * fmod($timeout, 1)));
      $chunk = fread($fp, 1024);
      $response .= $chunk;
      $info = stream_get_meta_data($fp);
      $alive = !$info['eof'] && !$info['timed_out'] && $chunk;
    }
    fclose($fp);
    if ($info['timed_out']) {
      $result->code = 504;
      $result->error = 'request timed out';
      return $result;
    }
    // Parse response headers from the response body.
    // Be tolerant of malformed HTTP responses that separate header and body with
    // \n\n or \r\r instead of \r\n\r\n.
    list($response, $result->data) = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
    $response = preg_split("/\r\n|\n|\r/", $response);
    // Parse the response status line.
    list($protocol, $code, $status_message) = explode(' ', trim(array_shift($response)), 3);
    $result->protocol = $protocol;
    $result->status_message = $status_message;
    $result->headers = array();
    // Parse the response headers.
    while ($line = trim(array_shift($response))) {
      list($name, $value) = explode(':', $line, 2);
      $name = strtolower($name);
      if (isset($result->headers[$name]) && $name == 'set-cookie') {
        // RFC 2109: the Set-Cookie response header comprises the token Set-
        // Cookie:, followed by a comma-separated list of one or more cookies.
        $result->headers[$name] .= ',' . trim($value);
      }
      else {
        $result->headers[$name] = trim($value);
      }
    }
    $responses = array(
      100 => 'Continue',
      101 => 'Switching Protocols',
      200 => 'OK',
      201 => 'Created',
      202 => 'Accepted',
      203 => 'Non-Authoritative Information',
      204 => 'No Content',
      205 => 'Reset Content',
      206 => 'Partial Content',
      300 => 'Multiple Choices',
      301 => 'Moved Permanently',
      302 => 'Found',
      303 => 'See Other',
      304 => 'Not Modified',
      305 => 'Use Proxy',
      307 => 'Temporary Redirect',
      400 => 'Bad Request',
      401 => 'Unauthorized',
      402 => 'Payment Required',
      403 => 'Forbidden',
      404 => 'Not Found',
      405 => 'Method Not Allowed',
      406 => 'Not Acceptable',
      407 => 'Proxy Authentication Required',
      408 => 'Request Time-out',
      409 => 'Conflict',
      410 => 'Gone',
      411 => 'Length Required',
      412 => 'Precondition Failed',
      413 => 'Request Entity Too Large',
      414 => 'Request-URI Too Large',
      415 => 'Unsupported Media Type',
      416 => 'Requested range not satisfiable',
      417 => 'Expectation Failed',
      500 => 'Internal Server Error',
      501 => 'Not Implemented',
      502 => 'Bad Gateway',
      503 => 'Service Unavailable',
      504 => 'Gateway Time-out',
      505 => 'HTTP Version not supported',
    );
    // RFC 2616 states that all unknown HTTP codes must be treated the same as the
    // base code in their class.
    if (!isset($responses[$code])) {
      $code = floor($code / 100) * 100;
    }
    $result->code = $code;
    switch ($code) {
      case 200: // OK
      case 304: // Not modified
        break;
      case 301: // Moved permanently
      case 302: // Moved temporarily
      case 307: // Moved temporarily
        $location = $result->headers['location'];
        $options['timeout'] -= $this->timerRead(__FUNCTION__) / 1000;
        if ($options['timeout'] <= 0) {
          $result->code = HTTP_REQUEST_TIMEOUT;
          $result->error = 'request timed out';
        }
        elseif ($options['max_redirects']) {
          // Redirect to the new location.
          $options['max_redirects']--;
          $result = $this->extendedHttpRequest($location, $options);
          $result->redirect_code = $code;
        }
        if (!isset($result->redirect_url)) {
          $result->redirect_url = $location;
        }
        break;
      default:
        $result->error = $status_message;
    }
    return $result;
  }

  public function sendCurlRequest($url, $options)
  {
    $ch = curl_init();

    $options['method'] = $options['method']?:'GET';

    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST,  $options['method']);

    if($options['method'] != 'GET')
    {
      curl_setopt( $ch, CURLOPT_POST, true );
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $options['body']);
    }

    curl_setopt( $ch, CURLOPT_HTTPHEADER, $options['headers']);
    curl_setopt( $ch, CURLOPT_COOKIEJAR, './cookie');
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    $result = curl_exec( $ch );
    $info = curl_getinfo($ch);

    return $result;
  }
}
