<?php /* Emacs, this is -*-php-*- */

/*
 *  pwserverd PHP binding
 *
 */

class PasswordServer
{
  var $port;
  var $host;

  function PasswordServer($port, $host)
  {
    $this->port = $port;
    $this->host = $host;
  }

  /* .. Low-level methods ................................................... */

  /* Connect to the pwserverd server */
  function connect()
  {
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($sock < 0)
      die("Unable to create socket: " 
          . socket_strerror(socket_last_error($sock)));
    
    $ret = socket_connect($sock, $this->host, $this->port);

    if (!$ret)
      die("Unable to connect to {$this->host}:{$this->port}: " 
          . socket_strerror(socket_last_error($sock)));

    return $sock;
  }

  /* Read a single line, stripped of extraneous whitespace */
  function readLine($sock)
  {
    $line = socket_read($sock, 2048, PHP_NORMAL_READ);

    if ($line === false) {
      die("socket_read() failed: "
          . socket_strerror(socket_last_error($sock)));
    }

    /* We use CR-LF, and socket_read() stops on *either* CR or LF */
    $eol = socket_read($sock, 1, PHP_NORMAL_READ);

    return trim($line);
  }

  /* Send the specified message with the specified set of parameters */
  function doMessage($message, $args)
  {
    $sock = $this->connect();

    /* Construct the request message */
    $request = "$message\r\n";
    if ($args) {
      foreach ($args as $arg => $value) {
        $pieces = preg_split("/(\r\n|\n)/", $value);
        $request .= "$arg: {$pieces[0]}\r\n";
        for ($i = 1; $i < count($pieces); $i++)
          $request .= "  : {$pieces[$i]}\r\n";
      }
    }
    $request .= "\r\n";

    socket_write($sock, $request, strlen($request));

    /* First read the status line */
    $line = $this->readLine($sock);

    if (!preg_match("/(\d+) (.*)/", $line, $matches))
      die("Bad response from pwserverd: $line");

    $status = intval($matches[1]);
    $message = $matches[2];
    $responseArgs = array();

    /* Now read any extra data that was provided */
    $lastArg = null;
    while (true) {
      $line = $this->readLine($sock);

      if (!$line)
        break;
      
      if (!preg_match("/^([^:]*):(.*)$/", $line, $matches))
        die("Bad result arg from pwserverd: $line");

      $arg = trim($matches[1]);
      $value = trim($matches[2]);

      if (!$arg) {
        if (!$lastArg)
          die("Empty arg name from pwserverd without preceding arg: $line");
        else
          $responseArgs[$lastArg] .= "\r\n" . $value;
      } else {
        $responseArgs[$arg] = $value;
      }

      $lastArg = $arg;
    }

    socket_close($sock);

    return array("statusCode" => $status,
                 "statusMessage" => $message,
                 "response" => $responseArgs);
  }

  function isSuccess($code)
  {
    return $code >= 200 && $code < 300;
  }

  /* .. High level functions ................................................ */

  /* Generate a new password */
  function generatePassword()
  {
    if (func_num_args() >= 1)
      $args = func_get_arg(0);
    else
      $args = null;

    $result = $this->doMessage ("GENERATE", $args);

    if (!$this->isSuccess($result["statusCode"])) {
      die("BEGIN failed with status " . $result["statusCode"]
          . " " . $result["statusMessage"]);
    }

    $response = $result["response"];

    return $response['Password'];
  }

  /* Check a password */
  function checkPassword($password)
  {
    if (func_num_args() >= 2)
      $args = func_get_arg(1);
    else
      $args = null;

    $result = $this->doMessage ("CHECK " . $password, $args);

    if (!$this->isSuccess($result["statusCode"])) {
      die("BEGIN failed with status " . $result["statusCode"]
          . " " . $result["statusMessage"]);
    }

    return $result["response"];
  }
}

?>
