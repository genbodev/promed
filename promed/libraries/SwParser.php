<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2009, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * swParser Class
 *
 * Расширение стандартного файла парсера с добавленной функцией
 *
 * @package		Promed
 * @subpackage	Libraries
 * @category	Parser
 * @author		SWAN Dev Team
 */
class SwParser extends CI_Parser {

	/**
	 *  Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template view,
	 * replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	public function parse($template, $data, $return = FALSE, $ConvertToUtf8 = FALSE, $headerUtf8 = FALSE)
	{
		$CI =& get_instance();
		$template = $CI->load->view($template, $data, TRUE, $ConvertToUtf8, $headerUtf8);

		return $this->_parse($template, $data, $return);
	}

	 /**
	 *  Parse a template from string
	 *
	 * Parses pseudo-variables contained in the specified template,
	 * replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	function parse_from_string($template, $data, $return = FALSE)
	{
		$CI =& get_instance();		
		
		if ($template == '')
		{
			return FALSE;
		}
		
		foreach ($data as $key => $val)
		{
			if (is_array($val))
			{
				$template = $this->_parse_pair($key, $val, $template);		
			}
			else
			{
				$template = $this->_parse_single($key, (string)$val, $template);
			}
		}
		
		if ($return == FALSE)
		{
			$CI->output->append_output($template);
		}
		
		return $template;
	}

	function _parse_single_ext($key, $val, $string, $altTag = '', $outputEmptyTags = false)
	{
		if ( empty($altTag) ) {
			$altTag = $key;
		}

		if ( !is_object($val) && ($outputEmptyTags === true || (is_array($outputEmptyTags) && in_array($key, $outputEmptyTags)) || strlen($val)>0) ) {
			if (strpos($val, '>') > 0 || strpos($val, '<') > 0) {
				$val = "<![CDATA[" . $val . "]]>";
			}
			if (strpos($string, ">".$this->l_delim.$key.$this->r_delim."<")>0) {
				return str_replace($this->l_delim.$key.$this->r_delim, $val, $string);
			} else {
				return str_replace($this->l_delim.$key.$this->r_delim, '<'.$altTag.'>'.htmlspecialchars_decode($val).'</'.$altTag.'>', $string);
			}
		}
		else {
			return str_replace($this->l_delim.$key.$this->r_delim, '', $string);
		}
	}
	function _parse_pair_ext($variable, $data, $string, $altKeys = array(), $outputEmptyTags = false)
	{
		if (FALSE === ($match = $this->_match_pair($string, $variable)))
		{
			return $string;
		}

		$str = '';
		foreach ($data as $row)
		{
			$temp = $match['1'];
			foreach ($row as $key => $val)
			{
				if ( array_key_exists($key, $altKeys) ) {
					$altTag = $altKeys[$key];
				}
				else {
					$altTag = '';
				}

				if ( ! is_array($val))
				{
					$temp = $this->_parse_single_ext($key, $val, $temp, $altTag, $outputEmptyTags);
				}
				else
				{
					$temp = $this->_parse_pair_ext($key, $val, $temp, $altKeys, $outputEmptyTags);
				}
			}
			
			$str .= $temp;
		}
		
		return str_replace($match['0'], $str, $string);
	}
	
	function parse_ext($template, $data, $return = FALSE, $notnull = FALSE, $altKeys = array(), $outputEmptyTags = false)
	{
		$CI =& get_instance();
		$template = $CI->load->view($template, $data, TRUE);
		
		if ($template == '')
		{
			return FALSE;
		}
		
		foreach ($data as $key => $val)
		{
			if ( array_key_exists($key, $altKeys) ) {
				$altTag = $altKeys[$key];
			}
			else {
				$altTag = '';
			}

			if (is_array($val))
			{
				$template = $this->_parse_pair_ext($key, $val, $template, $altKeys, $outputEmptyTags);
			}
			else
			{
				$template = $this->_parse_single_ext($key, (string)$val, $template, $altTag, $outputEmptyTags);
			}
		}
		
		if ($return == FALSE)
		{
			$CI->output->append_output($template);
		}
		
		return $template;
	}

}
// END SwParser Class

/* End of file SwParser.php */