<?php

class Timer
{
	var $start = null;
	var $stop = null;

	/**
	 * Timer constructor.
	 */
	function __construct()
	{

	}

	/**
	 * start
	 */
	function start()
	{
		$this->start = $this->getmicrotime();
	}

	/**
	 * @param int $decimalPlaces
	 * @return float
	 */
	function fetch($decimalPlaces = 3)
	{
		$this->stop = $this->getmicrotime();
		return round(($this->getmicrotime() - $this->start), $decimalPlaces);
	}

	/**
	 * @return float
	 */
	function getmicrotime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	 * @return null
	 */
	function getStartTime()
	{
		return $this->start;
	}

	/**
	 * @return null
	 */
	function getStopTime()
	{
		return $this->stop;
	}
}

class DebugSoapServer extends SoapServer
{
	/**
	 * Timer object
	 *
	 * @var Timer
	 */
	private $debugTimer = null;

	/**
	 * Array with all debug values
	 *
	 * @var array
	 */
	protected $soapDebug = array();

	/**
	 * Constructor
	 *
	 * @param mixed $wsdl
	 * @param array [optional] $options
	 */
	public function __construct($wsdl, $options = null)
	{
		$this->debugTimer = new Timer();
		$this->debugTimer->start();
		return parent::__construct($wsdl, $options);
	}

	/**
	 * Store a named value in the debug array.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	private function setDebugValue($name, $value)
	{
		$this->soapDebug[$name] = $value;
	}

	/**
	 * Returns a value from the debug values.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getDebugValue($name)
	{
		if (array_key_exists($name, $this->soapDebug)) {
			return $this->soapDebug[$name];
		}

		return false;
	}

	/**
	 * Returns all debug values as array.
	 *
	 * @return array
	 */
	public function getAllDebugValues()
	{
		return $this->soapDebug;
	}

	/**
	 * Collect some debuging values and handle the soap request.
	 *
	 * @param string $request
	 * @return void
	 */
	public function handle($request = null)
	{
		// store the remote ip-address
		$this->setDebugValue('RemoteAddress', $_SERVER['REMOTE_ADDR']);

		// check variable HTTP_RAW_POST_DATA
		if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents('php://input');
		}

		// check input param
		if (is_null($request)) {
			$request = $GLOBALS['HTTP_RAW_POST_DATA'];
		}

		// get soap namespace identifier
		if (preg_match('/:Envelope[^>]*xmlns:([^=]*)="urn:NAMESPACEOFMYWEBSERVICE"/im',
			$request, $matches)) {
			$soapNameSpace = $matches[1];

			// grab called method from soap request
			$pattern = '/<' . $soapNameSpace . ':([^\/> ]*)/im';
			if (preg_match($pattern, $request, $matches)) {
				$this->setDebugValue('MethodName', $matches[1]);
			}
		}

		// anonymize passwords
		$modifiedRequest = preg_replace('/(]*>)([^<]*)()/im',
			'$1' . str_repeat('X', 8) . '$3', $request);

		// store the request string
		$this->setDebugValue('RequestString', $this->formatXmlString($request));

		// store the request headers
		if (function_exists('apache_request_headers')) {
			$this->setDebugValue('RequestHeader', serialize(apache_request_headers()));
		}

		// start output buffering
		/*ob_flush();
		ob_start();*/


		// finaly call SoapServer::handle() - store result
		$result = parent::handle($request);

		//die();

		// stop debug timer and store values
		$this->setDebugValue('CallDuration', $this->debugTimer->fetch(5));

		// store the response string
		$this->setDebugValue('ResponseString', $this->formatXmlString(ob_get_contents()));
		//var_dump($this);
		// flush buffer
		ob_flush();

		// store the response headers
		if (function_exists('apache_response_headers')) {
			$this->setDebugValue('ResponseHeader', serialize(apache_response_headers()));
		}


		// store additional timer values
		$this->setDebugValue('CallStartTime', $this->debugTimer->getStartTime());
		$this->setDebugValue('CallStopTime', $this->debugTimer->getStopTime());

		// return stored soap-call result
		return $result;
	}

	/**
	 * @param $xmlString
	 * @return string
	 */
	function beautify($xmlString)
	{
		$outputString = "";
		$previousBitIsCloseTag = false;
		$indentLevel = 0;
		$bits = explode("<", $xmlString);

		foreach ($bits as $bit) {

			$bit = trim($bit);
			if (!empty($bit)) {

				if ($bit[0] == "/") {
					$isCloseTag = true;
				} else {
					$isCloseTag = false;
				}

				if (strstr($bit, "/>")) {
					$prefix = "\n" . str_repeat(" ", $indentLevel);
					$previousBitIsSimplifiedTag = true;
				} else {
					if (!$previousBitIsCloseTag and $isCloseTag) {
						if ($previousBitIsSimplifiedTag) {
							$indentLevel--;
							$prefix = "\n" . str_repeat(" ", $indentLevel);

						} else {
							$prefix = "";
							$indentLevel--;
						}
					}
					if ($previousBitIsCloseTag and !$isCloseTag) {
						$prefix = "\n" . str_repeat(" ", $indentLevel);
						$indentLevel++;
					}
					if ($previousBitIsCloseTag and $isCloseTag) {
						$indentLevel--;
						$prefix = "\n" . str_repeat(" ", $indentLevel);
					}
					if (!$previousBitIsCloseTag and !$isCloseTag) {
						{
							$prefix = "\n" . str_repeat(" ", $indentLevel);
							$indentLevel++;
						}
					}
					$previousBitIsSimplifiedTag = false;
				}

				$outputString .= $prefix . "<" . $bit;

				$previousBitIsCloseTag = $isCloseTag;
			}
		}
		return $outputString;
	}

	/**
	 * @param $xml
	 * @return string
	 */
	function formatXmlString($xml)
	{
		// add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
		$xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

		// now indent the tags
		$token = strtok($xml, "\n");
		$result = ''; // holds formatted version as it is built
		$pad = 0; // initial indent
		$matches = array(); // returns from preg_matches()

		// scan each line and adjust indent based on opening/closing tags
		while ($token !== false) :

			// test for the various tag states

			// 1. open and closing tags on same line - no change
			if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) {
				$indent = 0;
				// 2. closing tag - outdent now
			} elseif (preg_match('/^<\/\w/', $token, $matches)) {
				$pad--;
				$indent = 0;
				// 3. opening tag - don't pad this one, only subsequent tags
			} elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
				$indent = 1;
				// 4. no indentation needed
			} else {
				$indent = 0;
			}

			// pad the line with the required number of leading spaces
			$line = str_pad($token, strlen($token) + $pad, ' ', STR_PAD_LEFT);
			$result .= $line . "\n"; // add to the cumulative result, with linefeed
			$token = strtok("\n"); // get the next token
			$pad += $indent; // update the pad size for subsequent lines
		endwhile;

		return $result;
	}
}

?>