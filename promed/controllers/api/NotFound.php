<?php

/**
 * Class NotFound
 */
class NotFound extends SwREST_Controller {
	/**
	 * NotFound constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->response(null, self::HTTP_NOT_FOUND);
	}
}