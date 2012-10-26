<?php
//
// By: Cloudmanic Labs, LLC 
// Web: http://cloudmanic.com/skyclerk
// Date: 9/28/2012
//

namespace Cloudmanic\Api;

class Skyclerk
{
	private static $_apihost = 'https://skyclerk.cloudmanic.com/api/v2';
	private static $_request_url = '';
	private static $_response = '';
	private static $_raw_response = '';
	private static $_request_data = array();
	private static $_access_token = '';
	private static $_account_id = '';
	private static $_error = array();

	// ----------------------------- Setters ----------------------------- //

	//
	// Set the access token.
	//
	public static function set_access_token($token)
	{
		self::$_access_token = trim($token);
	}
	
	//
	// Set the account id.
	//
	public static function set_account_id($id)
	{
		self::$_account_id = trim($id);
	}

	//
	// Set request data.
	//
	public static function set_data($key, $value)
	{
		self::$_request_data[$key] = $value;
	}
	
	//
	// Set order.
	//
	public static function set_order($order, $sort = 'desc')
	{
		self::set_data('order', $order);
		self::set_data('sort', $sort);
	}
	
	//
	// Set API host.
	//
	public static function set_api_host($host)
	{
		self::$_apihost = $host;
	}
	
	// ----------------------- Non-API Getters --------------------------- //
	
	//
	// Return the raw response.
	//
	public static function get_raw_response()
	{
		return self::$_raw_response;
	}
	
	//
	// Return the error messages.
	//
	public static function get_error()
	{
		return self::$_error;
	}
	
	//
	// Get API host.
	//
	public static function get_api_host()
	{
		return self::$_apihost;
	}

	// ----------------------- Ledger API Requests ----------------------- //

	//
	// Get ledger by id entry.
	//
	public static function ledger_get_by_id($id)
	{
		self::$_request_url = self::$_apihost . '/ledger/get/id/' . $id;
		return self::_request('get');
	} 

	//
	// Get ledger entries.
	//
	public static function ledger_get()
	{
		self::$_request_url = self::$_apihost . '/ledger/get';
		return self::_request('get');
	} 

	//
	// Insert ledger entry.
	//
	public static function ledger_create()
	{
		self::$_request_url = self::$_apihost . '/ledger/create';
		return self::_request('post');
	} 

	//
	// Delete ledger entry.
	//
	public static function ledger_delete($id)
	{
		self::set_data('Id', $id);
		self::$_request_url = self::$_apihost . '/ledger/delete';
		return self::_request('post');	
	}

	// ----------------- Contacts API Requests ---------------- //
	
	//
	// Insert contact entry.
	//
	public static function contact_create()
	{
		self::$_request_url = self::$_apihost . '/contacts/create';
		return self::_request('post');	
	}
	
	// ----------------- Admin Functions ---------------------- //
	
	//
	// Clear all the data in the account. You must set the confirm
	// data post element to "confirm=yes-clear-my-account".
	//
	public static function admin_clear_account()
	{
		self::$_request_url = self::$_apihost . '/admin/account_clear';
		return self::_request('post');
	}
	
	// ----------------- Import API Requests ---------------- //
	
	//
	// Import all ledger entries by a file path.
	//
	public static function import_ledger_by_file($path)
	{
		// Make sure the path is correct.
		if(! file_exists($path))
		{
			self::$_error[] = array('error' => 'File not found.', 'field' => 'N/A');
			return 0;
		}
		
		self::set_data('Filedata', '@' . $path);
		self::$_request_url = self::$_apihost . '/import/ledger';
		return self::_request('post');
	}

	// ----------------- Private Functions -------------------- //

	//
	// Make request to SkyLedger
	//
	private static function _request($type)
	{
		// Reset error.
		self::$_error = array();

		// Set post / get requests we have to send with every request.
		self::$_request_data['access_token'] = self::$_access_token;
		self::$_request_data['account_id'] = self::$_account_id;
		self::$_request_data['format'] = 'json';

		// Is this a get request? If so tack on the params.
		if($type == 'get')
		{
			self::$_request_url = self::$_request_url . '?' . http_build_query(self::$_request_data);
		}

		// Setup request.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$_request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		// Is this a post requests?
		if($type == 'post')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, self::$_request_data);
		}
		
		// Send and decode the request.
		self::$_raw_response = curl_exec($ch);
		self::$_response = json_decode(self::$_raw_response, TRUE);
		self::$_request_data = array();
		self::$_request_url = '';
		curl_close($ch);
		
		// Make sure status was returned
		if(! isset(self::$_response['status']))
		{
			self::$_error[] = array('error' => 'Request failed', 'field' => 'N/A');
			return 0;
		}
		
		// Check for any errors.
		if(self::$_response['status'] == 0)
		{
			self::$_error = self::$_response['errors'];
			return false;
		}

		return self::$_response;
	}
}

/* End File */