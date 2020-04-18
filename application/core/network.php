<?php
namespace Framework;

/*
 * Sites who're based on this framework can exchange data:
    - DNS, - Malicious visitors\bots, - Malware reports, - Proxy\VPNs, etc..
 **/
class Network {

  var $server = 'chat.freenode.net';
  var $port = 6667; // SSL: 6697 (freenode)

  var $chan = '#ilcapoframework';
  var $nick;

  $socket = NULL;

  function __construct()
  {
    set_time_limit(0);

    $nick = $this->nick;
    $chan = this->$channel;

    socket = fsockopen($this->server, $this->port);
    fputs($socket,"USER $nick $nick $nick $nick :$nick\n");
    fputs($socket,"NICK $nick\n");
    fputs($socket,"JOIN ".$chan."\n");

    $this->socket = $socket;

  }

  function connection()
  {

    //while(1) {
      while($data = fgets($socket)) {
        echo nl2br($data);
        flush();
        $ex = explode(' ', $data);
        $rawcmd = explode(':', $ex[3]);
        $oneword = explode('<br>', $rawcmd);
        $channel = $ex[2];
        $nicka = explode('@', $ex[0]);
        $nickb = explode('!', $nicka[0]);
        $nickc = explode(':', $nickb[0]);
        $host = $nicka[1];
        $nick = $nickc[1];
        if($ex[0] == "PING"){
            fputs($socket, "PONG ".$ex[1]."\n");
        }
          $args = NULL; for ($i = 4; $i < count($ex); $i++) { $args .= $ex[$i] . ' '; }
          if ($rawcmd[1] == "!sayit") {
              fputs($socket, "PRIVMSG ".$channel." :".$args." \n");
          }
        elseif ($rawcmd[1] == "!md5") {
            fputs($socket, "PRIVMSG ".$channel." :MD5 ".md5($args)."\n");
        }
      }
    //}
    set_time_limit(30);
  }
}

?>
