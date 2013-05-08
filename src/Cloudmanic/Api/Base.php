<?php
//
// By: Cloudmanic Labs, LLC 
// Web: http://cloudmanic.com/skyclerk
// Date: 4/15/2013
//

namespace Cloudmanic\Api;

class Base
{
	public $accounts_host = 'https://accounts.cloudmanic.com';
	public $request_url = '';
	public $response = '';
	public $raw_response = '';
	public $request_data = array();
	public $access_token = '';
	public $account_id = '';
	public $error = array();

	//
	// Constructor.
	//
	public function __construct($host)
	{
		$this->set_api_host($host);
	}

	// ----------------------------- Setters ----------------------------- //

	//
	// Set the access token.
	//
	public function set_access_token($token)
	{
		$this->access_token = trim($token);
	}
	
	//
	// Set the account id.
	//
	public function set_account_id($id)
	{
		$this->account_id = trim($id);
	}

	//
	// Set request data.
	//
	public function set_data($key, $value)
	{
		$this->request_data[$key] = $value;
	}
	
	//
	// Set order.
	//
	public function set_order($order, $sort = 'desc')
	{
		$this->set_data('order', $order);
		$this->set_data('sort', $sort);
	}
	
	//
	// Set API host.
	//
	public function set_api_host($host)
	{
		$this->apihost = $host;
	}
	
	//
	// Set Accounts host.
	//
	public function set_accounts_host($host)
	{
		$this->accounts_host = $host;
	}
	
	// ----------------------- Non-API Getters --------------------------- //
	
	//
	// Return the raw response.
	//
	public function get_raw_response()
	{
		return $this->raw_response;
	}
	
	//
	// Return the error messages.
	//
	public function get_error()
	{
		return $this->error;
	}
	
	//
	// Get API host.
	//
	public function get_api_host()
	{
		return $this->apihost;
	}	

	// ----------------------- Generic API Requests ----------------------- //

	//
	// Get entries.
	//
	public function get($object)
	{
		$this->request_url = $this->apihost . '/' . $object;
		return $this->request('get');
	} 

	//
	// Get data by id entry.
	//
	public function get_by_id($object, $id)
	{
		$this->request_url = $this->apihost . '/' . $object . '/id/' . $id;
		return $this->request('get');
	} 
	
	//
	// Insert entry.
	//
	public function create($object)
	{
		$this->request_url = $this->apihost . '/' . $object . '/create';
		return $this->request('post');
	} 
	
	//
	// Delete entry.
	//
	public function delete($object, $id)
	{
		$this->set_data('Id', $id);
		$this->request_url = $this->apihost . '/' . $object . '/delete';
		return self::request('post');	
	}
	
	//
	// All data since a particular time entries.
	//
	public function since($date)
	{
		$this->set_data('since', $date);
		$this->request_url = $this->apihost . '/data/since';
		return $this->request('get');
	} 
	
	// ----------------------- Accounts API Requests ----------------------- //

	//
	// Get the account
	//
	public function get_account_profile()
	{
		$this->request_url = $this->accounts_host . '/api/v1/accounts/profile';
		return $this->request('get');
	} 
	
	//
	// Update the account
	//
	public function update_account()
	{
		$this->request_url = $this->accounts_host . '/api/v1/accounts/update';
		return $this->request('post');
	} 

	// ----------------- Curl Functions -------------------- //

	//
	// Make request to Server
	//
	public function request($type)
	{
		// Reset error.
		$this->error = array();

		// Set post / get requests we have to send with every request.
		$this->request_data['access_token'] = $this->access_token;
		$this->request_data['account_id'] = $this->account_id;
		$this->request_data['format'] = 'json';

		// Is this a get request? If so tack on the params.
		if($type == 'get')
		{
			$this->request_url = $this->request_url . '?' . http_build_query($this->request_data);
		}

		// Setup request.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
		
		// Is this a post requests?
		if($type == 'post')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request_data);
		}
		
		// Send and decode the request.
		$this->raw_response = curl_exec($ch);
		$this->response = json_decode($this->raw_response, TRUE);
		$this->request_data = array();
		$this->request_url = '';
		curl_close($ch);
		
		// Make sure status was returned
		if(! isset($this->response['status']))
		{
			$this->error[] = array('error' => 'Request failed', 'field' => 'N/A');
			return 0;
		}
		
		// Check for any errors.
		if($this->response['status'] == 0)
		{
			$this->error = $this->response['errors'];
			return false;
		}

		return $this->response;
	}
}

/* End File */