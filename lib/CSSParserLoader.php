<?php

require_once dirname(__FILE__).'/autoload/DirectoriesAutoloader.php';
  
class CSSParserLoader
{
	static protected
		$paths = null,
    $cache_dir = null;
    
	static public function register()
  {
    self::setupPaths();
    $autoloader = DirectoriesAutoloader::getInstance(self::$cache_dir);
    foreach(self::$paths as $dir)
    {
      $autoloader->addDirectory($dir);
    }
    spl_autoload_register(array($autoloader, 'autoload'));
  }
  
  static protected function setupPaths()
  {
    if(is_null(self::$paths))
    {
      self::$paths = array(
        __DIR__.'/CSS',
        __DIR__.'/vendor',
        __DIR__.'/parser',
        __DIR__.'/lexer',
        __DIR__.'/util',
      );
    }
    if(is_null(self::$cache_dir))
    {
      self::$cache_dir = dirname(__FILE__).'/cache/autoload.cache.php';
    }   
  }
  
}
