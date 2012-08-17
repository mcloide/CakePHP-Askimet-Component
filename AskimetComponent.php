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
	 * constant to define the current version of the component
	 * @const COMPONENT_VERSION
	 */
	const COMPONENT_VERSION = '1.0';

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
	 * @param array $params The necessary params to perform the request
	 * @param[key] Askimet Api Key
	 *
	 * @return boolean
	 */
	public function verify_key($params) {
		$response = $this->post($params, static::VERIFY_KEY_METHOD);
		return (!empty($response) && $response == 'valid');
	}

	/**
	 * Will verify if a given comment is spam
	 *
	 * @param array $params The necessary params to provide the post
	 * @param[key] Askimet API key - required
	 * @param[permalink] The link where the comment / message was posted to
	 * @param[comment_author] The name of the author
	 * @param[comment_author_email] The email of the comment author
	 * @param[comment_author_url] The author url
	 * @param[comment_content] The content for the comment
	 * @return array $result
	 * @returnParam $result[spam]
	 * @optionalReturnParam $result[error]
	 */
	public function comment_check($params) {
		$response = $this->post($params, static::COMMENT_CHECK_METHOD);
		$result = array();
		switch($response) {
			case 'true':
				$result = array('spam' => true);
				break;
			case 'false':
				$result = array('spam' => false);
				break;
			default:
				if (!empty($params['debug'])) {
					$response = $this->post($params, static::COMMENT_CHECK_METHOD, true);
				}
				$result = array('spam' => false, 'error' => $response);
				break;
		}
		return $result;
	}

	/**
	 * Will submit a given comment as spam - use the same params as comment check
	 *
	 * @param array $params The necessary params to provide the post
	 * @param[key] Askimet API key - required
	 * @param[permalink] The link where the comment / message was posted to
	 * @param[comment_author] The name of the author
	 * @param[comment_author_email] The email of the comment author
	 * @param[comment_author_url] The author url
	 * @param[comment_content] The content for the comment
	 * @return string $result
	 */
	public function submit_spam($params) {
		$response = $this->post($params, static::SUBMIT_SPAM_METHOD);
		return ($response);
	}

	/**
	 * Will submit a given comment as ham (not spam) - use the same params as comment check
	 *
	 * @param array $params The necessary params to provide the post
	 * @param[key] Askimet API key - required
	 * @param[permalink] The link where the comment / message was posted to
	 * @param[comment_author] The name of the author
	 * @param[comment_author_email] The email of the comment author
	 * @param[comment_author_url] The author url
	 * @param[comment_content] The content for the comment
	 * @return string $result
	 */
	public function submit_ham($params) {
		$response = $this->post($params, static::SUBMIT_HAM_METHOD);
		return ($response);
	}

	/**
	 * Will construct the url params for the askimet api calls correctly based on an array
	 *
	 * @param $params An array with parameters on key => value format
	 * @return string $request
	 */
	protected function build_curl_post_params($params) {
		$request = '';
		if (!empty($params) && is_array($params)) {
			$count = 0;
			foreach ($params as $key => $value) {
				$value = urlencode($value);

				$request .= (!empty($count)) ? '&' : '';
				$request .= "{$key}={$value}";
				++$count;
			}
		}
		return $request;
	}

	/**
	 * Will format the post params to work in according to the method passed
	 *
	 * @param array $params
	 * @return array $formatted
	 */
	protected function format_post_params($params, $method) {
		$required = $formatted = array();
		switch($method) {
			case static::VERIFY_KEY_METHOD:
					$required = array('key', 'blog');
				break;
			case static::COMMENT_CHECK_METHOD:
			case static::SUBMIT_HAM_METHOD:
			case static::SUBMIT_SPAM_METHOD:
					$required = array('key', 'blog', 'user_agent', 'user_ip', 'referrer', 'permalink', 'comment_type', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content');
				break;
		}

		foreach ($required as $index => $param) {
			switch ($param) {
				case 'blog':
					$formatted[$param] = $this->url;
					break;
				case 'user_agent':
					$formatted[$param] = $this->get_ua();
					break;
				case 'referrer':
					$formatted[$param] = isset($_SERVER['HTTP_REFERER']) ?  $_SERVER['HTTP_REFERER'] : '/';
					break;
				case 'user_ip':
					$formatted[$param] = $_SERVER['REMOTE_ADDR'];
					break;
				case 'comment_type':
					$formatted[$param] = $this->set_comment_type((isset($params[$param])) ? $params[$param] : '');
					break;
				default:
					$formatted[$param] = (isset($params[$param])) ? $params[$param] : '';
					break;
			}
		}

		return $formatted;
	}

	/**
	 * Will post the request to the Askimet API
	 *
	 * @param array $params
	 * @param string $method
	 * @optionalParam boolean $forceHeaders
	 * @return string
	 */
	protected function post($params, $method, $forceHeaders = false) {
		$params = $this->format_post_params($params, $method);
		$postUrl = $this->construc_post_url($params['key'], $method);
		$post = $this->build_curl_post_params($params);

		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, $postUrl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

		if ($forceHeaders) {
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
		}
	
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);

		if (!curl_errno($ch)) {
			curl_close($ch);
			return $response;
		}

		return false;
	}

	/**
	 * Will get a given comment type and return the correct type for it.
	 *
	 * @param string $type The comment type
	 * @return string
	 */
	protected function set_comment_type($type) {
		$validTypes = array('blank', 'comment', 'trackback', 'pingback', 'custom');
		if (!empty($type) && in_array($type, $validTypes)) {
			return $type;
		}

		return 'blank';
	}

	/**
	 * Will build the user agent in accordance with Askimet Api
	 *
	 * @return string
	 */
	public function get_ua() {
		return 'CakePHP/' . Configure::read('Cake.version') . ' | Askimet Component/' . static::COMPONENT_VERSION;
	}

	/**
	 * Will construct the post url for the askimet api
	 *
	 * @param string $key The api key
	 * @param string $method The method being posted
	 *
	 * @return string
	 */
	protected function construc_post_url($key, $method) {
		return $key . '.' . static::BASE_URL . '/' . static::API_VERSION . '/' . $method;
	}
}