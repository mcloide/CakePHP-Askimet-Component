<?php
/**
 * Askimet Component
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled with this
 * package in the file LICENSE. It is also available through the world-wide-web
 * at this URL: http://www.opensource.org/licenses/bsd-license
 *
 * @category   Components
 * @package    CakePHP
 * @subpackage PHP
 * @copyright  Copyright (c) 2012 Mcloide (http://www.mcloide.com)
 * @license    http://www.opensource.org/licenses/bsd-license    New BSD License
 * @version    1.0
 */

/**
 * AskimetComponent class
 *
 * This component is used to verify and post a spam to askimet
 * using The Askimet API and cURL
 * http://akismet.com/development/
 *
 * @category   Components
 * @package    CakePHP
 * @subpackage PHP
 * @copyright  Copyright (c) 2012 Mcloide (http://www.mcloide.com)
 * @license    http://www.opensource.org/licenses/bsd-license    New BSD License
 */
class AskimetComponent extends Component {

	/**
	 * constant to define the base api rest request url
	 * @const BASE_URL
	 */
	const BASE_URL = 'rest.akismet.com';

	/**
	 * constant to define the base api version
	 * @const API_VERSION
	 */
	const API_VERSION = '1.1';

	/**
	 * constant to define the method of key verification
	 * @const VERIFY_KEY_METHOD
	 */
	const VERIFY_KEY_METHOD = 'verify-key';

	/**
	 * constant to define the method for comment check
	 * @const COMMENT_CHECK_METHOD
	 */
	const COMMENT_CHECK_METHOD = 'comment-check';

	/**
	 * constant to define the method for spam submission
	 * @const SUBMIT_SPAM_METHOD
	 */
	const SUBMIT_SPAM_METHOD = 'submit-spam';

	/**
	 * constant to define the method for ham submission
	 * @const SUBMIT_HAM_METHOD
	 */
	const SUBMIT_HAM_METHOD = 'submit-ham';

	/**
	 * The site domain (askiment blog url)
	 * @var $url
	 */
	protected $url = '';

    /**
     * start up component
     *
     * @param object $controller
     * @access public
     */
    public function startUp($controller) {
		$this->url = 'http://' . $_SERVER['SERVER_NAME'];
        $this->controller = $controller;
    }

    /**
     * Initialize component
     *
     * @param array $options Array of internal variables to set on instantiation
     * @access public
     */
    public function initialize(&$controller, $settings = array()) {
        $this->_set($settings);
    }

	/**
	 * Will verify if the given set key / domain are valid on askimet
	 *
	 * @return boolean
	 */
	public function verify_key ($params) {
		$url = urlencode($this->url);
		$postParams = array(
			'key' => $params['api_key'],
			'blog' => $url
		);

		$response = $this->post_to_askimet($postParams, static::VERIFY_KEY_METHOD);
		return (!empty($response) && $response == 'valid');
	}

	public function comment_check($params) {
		$postParams = array(
			'key' => $params['api_key'],
			'blog' => urlencode($this->url),
			'user_agent' => urlencode($_SERVER['HTTP_USER_AGENT']),
			'user_ip' => urlencode($_SERVER['REMOTE_ADDR']),
			'referrer' => urlencode($params['referrer']),
			'permalink' => urlencode($params['permalink']),
			'comment_type' => urlencode($this->setCommentType($params['comment_type'])),
			'comment_author' => urlencode($params['comment_author']),
			'comment_author_email' => urlencode($params['comment_author_email']),
			'comment_author_url' => (!empty($params['comment_author_url'])) ? urlencode($params['comment_author_url']) : '',
			'comment_content' => urlencode($params['comment_content'])
		);

		$response = $this->post_to_askimet($postParams, static::COMMENT_CHECK_METHOD);
		$result = array();
		switch($response) {
			case 'true':
				$result = array('spam' => true);
				break;
			case 'false':
				$result = array('spam' => false);
				break;
			default:
				if ($params['debug']) {
					$response = $this->post_to_askimet($postParams, static::COMMENT_CHECK_METHOD, true);
				}
				$result = array('spam' => false, 'error' => $response);
				break;
		}
		return $result;
	}

	public function submit_spam($params) {
		$postParams = array(
			'key' => $params['api_key'],
			'blog' => urlencode($this->url),
			'user_agent' => urlencode($_SERVER['HTTP_USER_AGENT']),
			'user_ip' => urlencode($_SERVER['REMOTE_ADDR']),
			'referrer' => urlencode($_SERVER['HTTP_REFERER']),
			'permalink' => urlencode($params['permalink']),
			'comment_type' => urlencode($this->setCommentType($params['comment_type'])),
			'comment_author' => urlencode($params['comment_author']),
			'comment_author_email' => urlencode($params['comment_author_email']),
			'comment_author_url' => (!empty($params['comment_author_url'])) ? urlencode($params['comment_author_url']) : '',
			'comment_content' => urlencode($params['comment_contnet'])
		);

		$response = $this->post_to_askimet($params, static::SUBMIT_SPAM_METHOD);
		return ($response);
	}

	public function submit_ham($params) {
		$postParams = array(
			'key' => $params['api_key'],
			'blog' => urlencode($this->url),
			'user_agent' => urlencode($_SERVER['HTTP_USER_AGENT']),
			'user_ip' => urlencode($_SERVER['REMOTE_ADDR']),
			'referrer' => urlencode($params['referrer']),
			'permalink' => urlencode($params['permalink']),
			'comment_type' => urlencode($this->setCommentType($params['comment_type'])),
			'comment_author' => urlencode($params['comment_author']),
			'comment_author_email' => urlencode($params['comment_author_email']),
			'comment_author_url' => (!empty($params['comment_author_url'])) ? urlencode($params['comment_author_url']) : '',
			'comment_content' => urlencode($params['comment_contnet'])
		);

		$response = $this->post_to_askimet($postParams, static::SUBMIT_HAM_METHOD);
		return ($response);
	}

	/**
	 * Will construct the url params for the askimet api calls correctly based on an array
	 *
	 * @param $params An array with parameters on key => value format
	 * @return string $request
	 */
	protected function construct_request_params ($params) {
		$request = '';
		if (!empty($params) && is_array($params)) {
			$count = 0;
			foreach ($params as $key => $value) {
				$request .= (!empty($count)) ? '&' : '';
				$request .= "{$key}={$value}";
				++$count;
			}
		}
		return $request;
	}

	protected function post_to_askimet($params, $method, $forceHeaders = false) {
		$postUrl = static::BASE_URL . '/' . static::API_VERSION . '/' . $method;
		$post = $this->construct_request_params($params);

		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, $postUrl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

		if ($forceHeaders) {
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
		}
	
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$res = curl_exec($ch);


		if (curl_errno($ch)) {
			return false;
		} else {
			curl_close($ch);
			return $res;
		}
	}

	protected function setCommentType($type) {
		$validTypes = array('blank', 'comment', 'trackback', 'pingback', 'custom');
		if (!empty($type) && in_array($type, $validTypes)) {
			return $type;
		}
	}
}