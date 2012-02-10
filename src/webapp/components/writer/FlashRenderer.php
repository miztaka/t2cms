<?php

class Writer_FlashRenderer
{
  protected
    $url,
    $flash,
    $replaces;
  
  public static function make($url, $replaces = array())
  {
    return new self($url, $replaces);
  }
  
  public function __construct($url, $replaces = array())
  {
    $this->url = $url;
    $this->flash = file_get_contents($this->url, TRUE);
    $this->replaces = array();
    $this->set($replaces);
  }
  
  public function set()
  {
    $num_args = func_num_args();
    $args = func_get_args();
    if ($num_args >= 2) {
      $this->replaces[$args[0]] = $args[1];
    } else if ($num_args >= 1 && is_array($args[0])) {
      foreach ($args[0] as $key => $value) {
        $this->set($key, $value);
      }
    }
    return $this;
  }

  public function render()
  {
    if (count($this->replaces) > 0) {
      $this->flash = self::rewrite($this->flash, $this->replaces);
    }
    header('Content-Length: '.strlen($this->flash));
    header('Content-Type: application/x-shockwave-flash');
    echo $this->flash;
  }
  
  protected static function rewrite($flash, $replaces)
  {
    $flash = self::decode($flash);
    $vars = array();
    foreach ($replaces as $key => $value) {
      //$value = mb_convert_encoding($value, 'SJIS', 'UTF-8');
      $vars[] = sprintf("\x96%s\x00%s\x00\x96%s\x00%s\x00\x1d",
        pack('v', strlen($key) + 2),
        $key,
        pack('v', strlen($value) + 2),
        $value);
    }
    $vars = implode('', $vars);
    $vars = sprintf("\x3f\x03%s%s\x00",
      pack('V', strlen($vars) + 1),
      $vars);
    $length = (ord($flash[8]) >> 1) + 5;
    $length = ceil((((8 - ($length & 7)) & 7) + $length) / 8) + 17;
    $header = substr($flash, 0, $length);
    $flash  = substr($header, 0, 4).
      pack('V', strlen($flash) + strlen($vars)).
      substr($header, 8).
      $vars.
      substr($flash, $length);
    return self::encode($flash);
  }
  
  protected static function decode($flash) {
      $first8 = substr($flash, 0, 8);
      $next = substr($flash, 8);
      
      $buff = "";
      $buff .= str_replace('CWS', 'FWS', $first8);
      $buff .= gzuncompress($next);
      return $buff;
  }
  
  protected static function encode($flash) {
      $first8 = substr($flash, 0, 8);
      $next = substr($flash, 8);
      
      $buff = "";
      $buff .= str_replace('FWS', 'CWS', $first8);
      $buff .= gzcompress($next);
      return $buff;
  }
}

?>