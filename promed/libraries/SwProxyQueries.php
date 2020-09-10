<?php if ( !defined( 'BASEPATH' ) )	exit( 'No direct script access allowed' );

/**
 * @name        swProxyQueries
 * @author      Demin Dmitry
 */
class SwProxyQueries {

	/**
	 * @var object Proxy instance
	 */
	protected $_proxy;

    /**
	 * @var resource A cURL handle
	 */
    protected $ch;

    /**
	 * @var array Settings
	 */
    protected $config = array();

	/**
	 * @const int Default HTTP port
	 */
	const DEFAULT_HTTP_PORT = 80;

	/**
	 * @const int Default HTTPS port
	 */
	const DEFAULT_HTTPS_POST = 443;

	/**
	 * Returns the static proxy of this class
	 * It is provided for invoking class-level methods (something similar to static class methods.)
	 */
	public static function proxy( $className=__CLASS__ ){
		if ( !isset( self::$_proxy ) ) {
			self::$_proxy = new $className(null);
		}
		self::$_proxy;
	}

    /**
     * New proxy instance
     */
    public function init( $config=array() ){
		if ( !sizeof( $config ) ) {
			die("Please provide a valid configuration.");
		}
		$this->config = $config;
		unset( $config );

		if ( !isset( $this->config['http_port'] ) ) {
			$this->config['http_port'] = self::DEFAULT_HTTP_PORT;
		}
		if ( !isset( $this->config['https_port'] ) ) {
			$this->config['https_port'] = self::DEFAULT_HTTPS_PORT;
		}

		$this->_initCurl();
    }

	/**
	 * Init cURL
	 */
	protected function _initCurl(){
        $this->ch = curl_init();
		@curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $this->ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $this->ch, CURLOPT_HEADER, true );
		curl_setopt( $this->ch, CURLOPT_TIMEOUT, $this->config[ "timeout" ] );
		curl_setopt( $this->ch, CURLOPT_USERAGENT, "PHP Proxy" );
		if ( !empty( $this->config[ 'http_auth_user' ] ) && !empty( $this->config[ 'http_auth_passwd' ] ) ) {
			curl_setopt( $this->ch, CURLOPT_USERPWD, "{$this->config[ 'http_auth_user' ]}:{$this->config[ 'http_auth_passwd' ]}" );
			curl_setopt( $this->ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY );
		}
	}

	/**
	 * Set cookies for the current request
	 *
	 * @param type $cookies
	 */
	public function setCookies( $cookies, $new=false ){
		// Refresh cookie
		if ( $new ) {
			curl_setopt( $this->ch, CURLOPT_COOKIESESSION, $new );
		}

		$cookie_string = '';
		foreach( $cookies as $k => $v ) {
			$cookie_string .= $k.'='.$v.';';
		}

		// Set cookie
		curl_setopt( $this->ch, CURLOPT_COOKIE, $cookie_string );
	}

    /**
     * Forward the current request to this url
     *
     * @param string $url URL to forward
	 * @param bool $output Force output
	 * @return HTML or array( info, headers, body )
     */
    public function forward( $url='', $output=true ){

		// Build correct url
		if ( isset( $_SERVER[ "HTTPS" ] ) && $_SERVER[ "HTTPS" ] == 'on' ) {
			$url = 'https://'.$this->config[ "server" ].':'.$this->config[ "https_port" ].'/'.ltrim( $url, '/' );
		} else {
			$url = 'http://'.$this->config[ "server" ].':'.$this->config[ "http_port" ].'/'.ltrim( $url, '/' );
		}

		// Set url
		curl_setopt( $this->ch, CURLOPT_URL, $url );

		// Forward request headers
		$headers = $this->getRequestHeaders();

		// Remove cookie info
		if ( isset( $headers['Cookie'] ) ) {
			// @todo Remove session_id only
			unset( $headers['Cookie'] );
		}

		// Set headers for request
		$this->setRequestHeaders( $headers );

		// Forward _POST
		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" ) {
			if ( in_array( $this->getContentType( $headers ), array( 'application/x-www-form-urlencoded', 'multipart/form-data' ) ) ) {
				$this->setPost( $_POST );
			} else {
				// just grab the raw post data
				$fp = fopen( 'php://input', 'r' );
				$post = stream_get_contents( $fp );
				fclose( $fp );
				$this->setPost( $post );
			}
		} elseif ( $_SERVER[ "REQUEST_METHOD" ] == "HEAD" ) {
			curl_setopt( $this->ch, CURLOPT_NOBODY, true );
		}


		// Bag with no sending PHPSESSID (@link http://stackoverflow.com/questions/15627217/curl-not-passing-phpsessid)
		session_write_close();

		// Execute
		$data = curl_exec( $this->ch );

		session_start();

		$info = curl_getinfo( $this->ch );

		// Extract response from headers
		$body = $info[ "size_download" ] ? substr( $data, $info[ "header_size" ], $info[ "size_download" ] ) : "";

		// Extract headers
		$headers = substr( $data, 0, $info[ "header_size" ] );
		if ( substr_count( $headers, "\r\n\r\n" ) > 1 ) {
			$headers = explode( "\r\n\r\n", $headers, -1 );
			$headers = $headers[ sizeof( $headers )-1 ]."\r\n\r\n";
		}

		// Extract cookie
		$cookies = array();
		preg_match( '/^Set-Cookie:\s*([^;]*)/mi', $data, $m );
		if ( isset( $m[ 1 ] ) ) {
			parse_str( $m[ 1 ], $cookies );
		}

		// Close connection
		curl_close( $this->ch );

		// test;
//		$hs = $info[ 'header_size' ];
//		$headers = substr( $data, 0, $hs );
//		$headers = explode("\r\n\r\n",$headers,-1);
//		$headers = $headers[ sizeof() ]
//		echo '<pre>'; var_dump( $headers ); exit;
//		$body = substr( $data, $hs );
		//echo '<Pre>';var_dump( $hs, $hr, $this->gunzip($b) ); exit;

		if ( $output ) {
			// Forward response headers
			$this->outputResponseHeaders( $headers );

			// Output html
			echo $body;
		} else {
			return array(
				'info' => $info,
				'headers' => $headers,
				'cookies' => $cookies,
				'body' => $body,
			);
		}
	}

	/**
     *  Get the content-type of the request
     */
    public static function getContentType( $headers ) {
		foreach( $headers as $name => $value ){
			if ( 'content-type' == strtolower( $name ) ) {
				$parts = explode( ';', $value );
				return strtolower( $parts[ 0 ] );
			}
		}
		return null;
	}

	/**
     * Get the headers of the current request
     */
    public static function getRequestHeaders() {
		// Use native getallheaders function
		if ( function_exists( 'getallheaders' ) ) {
			return getallheaders();
		}

		// Fallback
		$headers = '';
		foreach( $_SERVER as $name => $value ){
			if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}

		return $headers;
	}

	/**
     * Pass the request headers to cURL
     *
     * @param array $request
     */
    public function setRequestHeaders( $request ) {
		// headers to strip
		$strip = array( "Content-Length", "Host" );

		$headers = array();
		foreach( $request as $key => $value ){
			if ( $key && !in_array( $key, $strip ) ) {
				$headers[] = "$key: $value";
			}
		}

		curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $headers );
	}

	/**
     * Pass the cURL response headers to the user
     *
     * @param array $response
     */
    public function outputResponseHeaders( $response, $strip=array() ) {
		// Headers to strip
		$strip = array_merge( array( "Transfer-Encoding" ), $strip );

		// split headers into an array
		$headers = explode( "\n", $response );

		// process response headers
		foreach( $headers as &$header ){
			// skip empty headers
			if ( !$header ) {
				continue;
			}

			// get header key
			$pos = strpos( $header, ":" );
			$key = substr( $header, 0, $pos );

			// modify redirects
			if ( strtolower( $key ) == "location" ) {
				$base_url = $_SERVER[ "HTTP_HOST" ];
				$base_url .= rtrim( str_replace( basename( $_SERVER[ "SCRIPT_NAME" ] ), "", $_SERVER[ "SCRIPT_NAME" ] ), "/" );

				// replace ports and forward url
				$header = str_replace( ":".$this->config[ "http_port" ], "", $header );
				$header = str_replace( ":".$this->config[ "https_port" ], "", $header );
				$header = str_replace( $this->config[ "server" ], $base_url, $header );
			}

			// set headers
			if ( !in_array( $key, $strip ) ) {
				header( $header, FALSE );
			}
		}
	}

	/**
     * Set POST values including FILES support
     *
     * @param array $post
     */
    public function setPost( $post ) {
		// file upload support
		if ( sizeof( $_FILES ) ) {
			foreach( $_FILES as $key => $file ){
				$parts = pathinfo( $file[ "tmp_name" ] );
				$name = $parts[ "dirname" ]."/".$file[ "name" ];
				rename( $file[ "tmp_name" ], $name );
				$post[ $key ] = "@".$name;
			}
		} else if ( is_array( $post ) ) {
			$post = http_build_query( $post );
		}

		curl_setopt( $this->ch, CURLOPT_POST, 1 );
		curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $post );
	}

	/**
	 * Remeber cookies
	 *
	 * @param array $cookies COOKIE
	 * @return void
	 */
	public function rememberCookies( $cookies=array() ){
		// @todo Update cookies with domain and sesssion live time
		if ( sizeof( $cookies ) ) {
			if ( !isset( $_SESSION[ $this->config['session_key'] ]['cookies'] ) ) {
				$_SESSION[ $this->config['session_key'] ]['cookies'] = array();
			}
			$_SESSION[ $this->config['session_key'] ]['cookies'] = array_merge( $_SESSION[ $this->config['session_key'] ]['cookies'], $cookies );
		}
	}

	/**
	 * Return cookies
	 *
	 * @return array
	 */
	public function restoreCookies(){
		if ( isset( $_SESSION[ $this->config['session_key'] ]['cookies'] ) ) {
			return $_SESSION[ $this->config['session_key'] ]['cookies'];
		} else {
			return array();
		}
	}

	/**
	 * Декодирует строку
	 *
	 * @param gzip $zipped
	 * @return string
	 */
	public function gunzip( $zipped ) {
		$offset = 0;
		if ( substr( $zipped, 0, 2 ) == "\x1f\x8b" ) {
			$offset = 2;
		}
		if ( substr( $zipped, $offset, 1 ) == "\x08" ) {
			# file_put_contents("tmp.gz", substr($zipped, $offset - 2));
			return gzinflate( substr( $zipped, $offset + 8 ) );
		}
		return "Unknown Format";
	}

	/**
	 * Сжимает строку используя формат данных DEFLATE
	 *
	 * @param string $unzipped
	 * @return gzip encoded
	 */
	public function gzip( $unzipped ) {
		return gzencode( $unzipped );
	}

}