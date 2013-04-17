<?php
//
// By: Cloudmanic Labs, LLC 
// Web: http://cloudmanic.com/skyclerk
// Date: 9/28/2012
//

namespace Cloudmanic\Api;

class Evermanic
{
	public static $apihost = 'https://evermnic.cloudmanic.com/api/v1';
	private static $i = null;
	private static $_objects = array('profiles');

	//
	// Instance ...
	//
	public static function instance()
	{
		if(is_null(static::$i))
		{
			static::$i = new Base(static::$apihost);
		}
        
		return static::$i;
	}

	//
	// Call static......
	//
	public static function __callStatic($method, $args)
	{
		// Break up the method.
		$parts = explode('_', $method);
		$object = $parts[0];
		
		// Is this a method that needs an object?
		if(in_array($object, static::$_objects))
		{
			$function = str_ireplace($object . '_', '', $method);
			$args = array_merge(array($object), $args);			
		} else
		{
			$function = $method;
		}
	
		return call_user_func_array(array(static::instance(), $function), $args);
	}
}

/* End File */