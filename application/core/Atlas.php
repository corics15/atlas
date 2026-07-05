<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Atlas
{
    protected static $CI;

    public static function init()
    {
      if (self::$CI === null)
      {
        self::$CI =& get_instance();
      }
    }

    public static function CI()
    {
      self::init();

      return self::$CI;
    }
}