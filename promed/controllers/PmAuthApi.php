<?php

/**
 * @class PmAuthApi
 *
 * Working with current authenticated user data
 *
 * @author Demin Dmitry
 * @since 08.2014
 */

class PmAuthApi extends swController {

	/**
	 * @var bool
	 */
	public $NeedCheckLogin = true;

	/**
	 * Output current _SESSION data in JSON format
	 *
	 * @return JSON
	 */
	public function getSession(){
		$json = $_SESSION;
		echo json_encode( $json );
	}

}

