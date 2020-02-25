<?php
namespace Disqus;

/**
 * Disqus unofficial library
 * https://disqus.com/api/docs/
 */
class Disqus {

  var $key;
  var $secret;

  function __construct($key, $secret) {
    $this->key = $key;
    $this->secret = $secret;
  }

  // https://disqus.com/api/docs/requests/
  function requestAPI($res = 'listPosts', $params = '') {
    $url = 'https://disqus.com/api/3.0/'.$res.'.json?'.$params.'&api_key='.$key.'&api_secret='.$secret;
    $data = null;

    return $this->sendRequest($url, $data);
  }

  function sendRequest($url, $data, $doUpload = false) {
    if (!$doUpload) $data = http_build_query($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: '.($doUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded'),
        'Content-length: '.sizeof($data)
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Unofficial API library for Disqus.com 1.0'); // User agent
    // SSL Support.
    $cert = dirname(__FILE__) . '/cacert.pem';
    $ssl = (file_exists($cert) ? true : false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl);
    curl_setopt($ch, CURLOPT_CAINFO, $cert);
    curl_setopt($ch, CURLOPT_CAPATH, $cert);

    $server_output = curl_exec($ch);
    curl_close ($ch);

    return $server_output;
  }
}

?>
