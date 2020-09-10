<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SwURI extends CI_URI{
	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->config =& load_class('Config', 'core');

		$this->_permitted_uri_chars = $this->config->item('permitted_uri_chars');

		// If it's a CLI request, ignore the configuration
		if (is_cli())
		{
			if (ENVIRONMENT == 'testing'){
				$uri = '';
			} else {
				$uri = $this->_parse_argv();
			}
		}
		else
		{
			$protocol = $this->config->item('uri_protocol');
			empty($protocol) && $protocol = 'REQUEST_URI';

			switch ($protocol)
			{
				case 'AUTO': // For BC purposes only
				case 'REQUEST_URI':
					$uri = $this->_parse_request_uri();
					break;
				case 'QUERY_STRING':
					$uri = $this->_parse_query_string();
					break;
				case 'PATH_INFO':
				default:
					$uri = isset($_SERVER[$protocol])
						? $_SERVER[$protocol]
						: $this->_parse_request_uri();
					break;
			}
		}

		$this->_set_uri_string($uri);

		log_message('info', 'URI Class Initialized');
	}
}
