<?php
//
// By: Cloudmanic Labs, LLC 
// Web: http://cloudmanic.com/skyclerk
// Date: 4/4/2013
//

namespace Cloudmanic\Api;

class Oauth
{
	private $client_id = '';
	private $client_secret = '';
	private $client_redirect = '';
	private $client_scopes = '';
	private $client_env = 'production';
	private $_access_token = null;
	private $_error = '';
	private $_base = '';

	//
	// Constructor.
	//
	public function __construct($options)
	{
		// Setup vars.
		foreach($options AS $key => $row) 
		{
			if(isset($this->{$key})) 
			{
				$this->{$key} = $row;
			}
		}
		
		// Setup base url.
		if($this->client_env == 'local')
		{
			$this->_base = 'http://accounts.cloudmanic.dev/';
		} else
		{
			$this->_base = 'https://accounts.cloudmanic.com/';			
		}
	}

	//
	// Get User.
	//
	public function get_user()
	{
		// We just have an access token first.
		if(is_null($this->_access_token))
		{
			return false;
		}
		
		// Make request.
		$q = array(
			'access_token' => $this->_access_token,
			'auto_create' => 1,
			'type' => 'website',
			'format' => 'json'
		);
		$url = $this->_base . 'api/v1/me/profile?' . http_build_query($q);		
		$d = json_decode(file_get_contents($url), TRUE);
		
		// Make sure there were no errors.
		if($d['status'] != 1)
		{
			return false;
		}
		
		// Return user object.
		return $d['data'];
	}
	
	//
	// Get Auth Url.
	//
	public function url_authorize()
	{
		return $this->_base . '/oauth/authorize';
	}

	//
	// Get Access Token Url.
	//
	public function url_access_token()
	{
		return $this->_base . 'oauth/access_token';
	}
	
	//
	// Get the access token.
	//
	public function get_access_token()
	{
		// Authorize / Then get the access token.
		if(! isset($_GET['code']))
		{
			$this->authorize();
		} else
		{
			$this->_access_token = $this->request_access_token($_GET['code']);
			return $this->_access_token;
		}
	}
	
	//
	// Get the access token.
	//
	public function request_access_token($code)
	{
		$params = array(
			'client_id' => $this->client_id,
			'redirect_uri' => $this->client_redirect,
			'client_secret' => $this->client_secret,
			'code' => $code,	
			'grant_type' => 'authorization_code',
			'type' => 'web_server'
		);
		
		// Build the request URL.
		$url = $this->url_access_token() . '?' .http_build_query($params);

		// POST the data to the server.
		$postdata = http_build_query($params);
		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);
		$context = stream_context_create($opts);
		$response = file_get_contents($this->url_access_token(), false, $context);

		// Parse the response.
		$params = json_decode($response, true);

		// Make sure there were no errors.
		if($params['status'] && isset($params['access_token']))
		{
			return $params['access_token'];
		} else
		{
			$this->_error = $params;
		}
		
		// If we made it here something went wrong.
		return $params;	
	}
	
	//
	// Authorize.
	//
	public function authorize()
	{
		// Set state we we know if this was the correct request.
		$state = md5(uniqid(rand(), true));
		setcookie('cloudmanic_authorize_state', $state);
		
		// Set the query params
		$params = array(
			'client_id' => $this->client_id,
			'redirect_uri' => $this->client_redirect,
			'state' => $state,
			'scope' => is_array($this->client_scopes) ? implode(',', $this->client_scopes) : $this->client_scopes,
			'response_type' => 'code',
		);
		
		header('Location: ' . $this->url_authorize() . '?' . http_build_query($params));
		exit;
	}
}

/* End File */