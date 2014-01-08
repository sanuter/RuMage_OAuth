<?php
/**
 * Bitbucket strategy for Opauth
 * based on https://confluence.atlassian.com/display/BITBUCKET/oauth+Endpoint
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @copyright    Copyright © 2012 FancyGuy Technologies (http://www.fancyguy.com)
 * @link         http://opauth.org
 * @package      Opauth.BitbucketStrategy
 * @license      MIT License
 */

/**
 * Bitbucket strategy for Opauth
 * based on https://confluence.atlassian.com/display/BITBUCKET/oauth+Endpoint
 * 
 * @package			Opauth.Bitbucket
 */
class BitbucketStrategy extends OpauthStrategy{
	
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = array('key', 'secret');
		
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'method'		=> 'POST',
		'oauth_callback'	=> '{complete_url_to_strategy}oauth_callback',
		'authenticate_url'	=> 'https://bitbucket.org/!api/1.0/oauth/authenticate',
		'request_token_url'	=> 'https://bitbucket.org/!api/1.0/oauth/request_token',
		'access_token_url'	=> 'https://bitbucket.org/!api/1.0/oauth/access_token',
		'bitbucket_profile_url'	=> 'https://api.bitbucket.org/1.0/user',
		
                // From tmhOAuth
                'user_token'			=> '',
                'user_secret'			=> '',
                'use_ssl'			=> true,
                'debug'				=> false,
                'force_nonce'			=> false,
                'nonce'				=> false, // used for checking signatures. leave as false for auto
                'force_timestamp'		=> false,
                'timestamp'			=> false, // used for checking signatures. leave as false for auto
                'oauth_version'			=> '1.0',
                'curl_connecttimeout'		=> 30,
                'curl_timeout'			=> 10,
                'curl_ssl_verifypeer'		=> false,
                'curl_followlocation'		=> false, // whether to follow redirects or not
                'curl_proxy'			=> false, // really you don't want to use this if you are using streaming
                'curl_proxyuserpwd'		=> false, // format username:password for proxy, if required
                'is_streaming'			=> false,
                'streaming_eol'			=> "\r\n",
                'streaming_metrics_interval'	=> 60,
                'as_header'			=> true,
	);
	
	public function __construct($strategy, $env) {
		parent::__construct($strategy, $env);
		
		$this->strategy['consumer_key'] = $this->strategy['key'];
		$this->strategy['consumer_secret'] = $this->strategy['secret'];
		
		if (!class_exists('tmhOAuth')) {
			require dirname(__FILE__) . '/Vendor/tmhOAuth/tmhOAuth.php';
		}
		
		$this->tmhOAuth = new tmhOAuth($this->strategy);
		if ($this->tmhOAuth->config['curl_ssl_verifyhost'] && $this->tmhOAuth->config['curl_ssl_verifypeer']) {
			$ssl = '+SSL';
		} else {
			$ssl = '-SSL';
		}
		$this->tmhOAuth->config['user_agent'] = 'tmhOAuth ' . tmhOAuth::VERSION . $ssl . ' - //github.com/fancyguy/opauth-bitbucket';
	}
	
	/**
	 * Auth request
	 */
	public function request(){
		$params = array(
			'oauth_callback'	=> $this->strategy['oauth_callback'],
			'consumer_key'		=> $this->strategy['consumer_key'],
		);
		
		$results = $this->_request('POST', $this->strategy['request_token_url'], $params);
		
		if ($results !== false && !empty($results['oauth_token']) && !empty($results['oauth_token_secret'])) {
			session_start();
			$_SESSION['_opauth_bitbucket'] = $results;
			
			$this->_authenticate($results['oauth_token']);
		}
	}
	
	public function oauth_callback() {
		session_start();
		$session = $_SESSION['_opauth_bitbucket'];
		unset($_SESSION['_opauth_bitbucket']);
		
		if ($_REQUEST['oauth_token'] == $session['oauth_token']) {
			$this->tmhOAuth->config['user_token'] = $session['oauth_token'];
			$this->tmhOAuth->config['user_secret'] = $session['oauth_token_secret'];
			
			$params = array(
				'oauth_verifier' => $_REQUEST['oauth_verifier'],
			);
			
			$results = $this->_request('POST', $this->strategy['access_token_url'], $params);
			
			if ($results !== false && !empty($results['oauth_token']) && !empty($results['oauth_token_secret'])) {
				$credentials = $this->_verify_credentials($results['oauth_token'], $results['oauth_token_secret']);
				
				if (!empty($credentials['user']['username'])) {
					$this->auth = array(
						'provider'	=> 'bitbucket',
						'uid'		=> $credentials['user']['username'],
						'info'		=> array(),
						'credentials'	=> array(
							'token'		=> $results['oauth_token'],
							'secret'	=> $results['oauth_token_secret'],
						),
						'raw'		=> $credentials
						
					);
					
					$this->mapProfile($credentials, 'user.username', 'info.nickname');
					$this->mapProfile($credentials, 'user.first_name', 'info.first_name');
					$this->mapProfile($credentials, 'user.last_name', 'info.last_name');
					$this->mapProfile($credentials, 'user.avatar', 'info.image');
					
					$this->callback();
				} else {
					$error = array(
						'code'		=> 'bitbucket.user_failed',
						'message'	=> 'Unable to obtain user info.',
						'raw'		=> $credentials,
					);
				}
			} else {
				$error = array(
					'code'		=> 'access_denied',
					'message'	=> 'User denied access.',
					'raw'		=> $_GET
				);
				
				$this->errorCallback($error);
			}
		}
	}
	
	private function _authenticate($oauth_token) {
		$params = array(
			'oauth_token'	=> $oauth_token
		);
		
		$this->clientGet($this->strategy['authenticate_url'], $params);
	}
	
	private function _verify_credentials($user_token, $user_token_secret) {
		$this->tmhOAuth->config['user_token'] = $user_token;
		$this->tmhOAuth->config['user_secret'] = $user_token_secret;
		
		$response = $this->_request('GET', $this->strategy['bitbucket_profile_url'], array(), true);
		
		if (!empty($response['user'])) {
			return array('user' => $response['user']);
		}
		
		return false;
	}
	
        /**
         * Wrapper of tmhOAuth's request() with Opauth's error handling.
         * 
         * request():
         * Make an HTTP request using this library. This method doesn't return anything.
         * Instead the response should be inspected directly.
         *
         * @param string $method the HTTP method being used. e.g. POST, GET, HEAD etc
         * @param string $url the request URL without query string parameters
         * @param array $params the request parameters as an array of key=value pairs
         * @param string $useauth whether to use authentication when making the request. Default true.
         * @param string $multipart whether this request contains multipart data. Default false
         */	
	private function _request($method, $url, $params = array(), $json_result = false, $useauth = true, $multipart = false) {
		$code = $this->tmhOAuth->request($method, $url, $params, $useauth, $multipart);
		
		if ($code == 200) {
			if (strpos($url, 'json') !== false || $json_result) {
				$response = json_decode($this->tmhOAuth->response['response'], true);
			} else {
				$response = $this->tmhOAuth->extract_params($this->tmhOAuth->response['response']);
			}
			
			return $response;
		} else {
			$error = array(
				'code'	=> $code,
				'raw'	=> $this->tmhOAuth->response['response'],
			);
			
			$this->errorCallback($error);
			
			return false;
		}
	}
	
}