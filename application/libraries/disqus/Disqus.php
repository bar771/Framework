<?php
namespace Disqus;

/**
 ****************************
 * Disqus unofficial library
 ****************************
 *
 * Disqus official docs:
 * https://disqus.com/api/docs/
 *
 * Forums:
 * https://disqus.com/api/docs/forums/
 */
class Disqus {

  var $key = '';
  var $secret = '';

  function __construct($key, $secret) {
    $this->key = $key;
    $this->secret = $secret;
  }

  // https://disqus.com/api/docs/requests/
  function requestAPI($res = 'listPosts', $getData = '', $postData = '') {
    $url = 'https://disqus.com/api/3.0/'.$res.'.json?'.$getData.'&api_key='.$this->key.'&api_secret='.$this->secret;

    return json_decode($this->sendRequest($url, $postData));
  }

  // https://disqus.com/api/docs/forums/listCategories/
  function getForumCategories()
  {
    return $this->requestAPI('listCategories', '');
  }

  // https://disqus.com/api/docs/forums/listPosts/
  function getForumPosts()
  {
    return $this->requestAPI('listPosts', '');
  }

  // https://disqus.com/api/docs/forums/listThreads/
  function getForumThreads()
  {
    return $this->requestAPI('listThreads', '');
  }

  private function sendRequest($url, $data, $doUpload = false) {
    if (!$doUpload) $data = http_build_query($data);
    // Initialize cURL.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // POST parameters.
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: '.($doUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded'),
        'Content-length: '.sizeof($data)
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // User agent
    curl_setopt($ch, CURLOPT_USERAGENT, 'Unofficial API library for Disqus.com 1.0');
    // SSL Support.
    $cert = dirname(__FILE__) . '/cacert.pem';
    $ssl = (file_exists($cert) ? true : false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl);
    curl_setopt($ch, CURLOPT_CAINFO, $cert);
    curl_setopt($ch, CURLOPT_CAPATH, $cert);
    // Server response.
    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
  }
}

?>
