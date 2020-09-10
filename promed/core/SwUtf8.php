<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SwUtf8 extends CI_Utf8 {
    
    // --------------------------------------------------------------------

    /**
     * Overload for clean string for environments with
     * an incorrect iconv
     *
     * @access    public
     * @param    string
     * @return    string
     */
    function clean_string($str)
    {
        /*if ($this->_is_ascii($str) === FALSE)
		{
			$str = @mb_convert_encoding('UTF-8', 'UTF-8//IGNORE', $str);
		}*/

		return $str;
    }

} 