<?php
//
// By: Cloudmanic Labs, LLC 
// Web: http://cloudmanic.com/skyclerk
// Date: 4/15/2013
//

namespace Cloudmanic\Api;

class Skyclerk
{
	public static $apihost = 'https://skcylerk.cloudmanic.com/api/v2';
	private static $i = null;
	private static $_objects = array('ledger');

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
	// Get entries.
	//
	public static function ledger_get()
	{
		self::instance()->request_url = self::instance()->apihost . '/api/v2/ledger/get';
		return self::instance()->request('get');
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