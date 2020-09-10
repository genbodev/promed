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
 * CodeIgniter Email Class
 *
 * Permits email to be sent using Mail, Sendmail, or SMTP.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/email.html
 */
class SwEmail extends CI_Email {
	var	$useragent		= "PromedWeb";
	var	$mailpath		= "/usr/sbin/sendmail";	// Sendmail path
	var	$protocol		= "smtp";	// mail/sendmail/smtp
	var	$smtp_host		= SMTP_HOST;
	var	$smtp_user		= SMTP_EMAIL;
	var	$smtp_pass		= SMTP_EMAIL_PASS;
	var	$smtp_port		= SMTP_PORT;
	var	$smtp_timeout	= SMTP_TIMEOUT;
	var	$wordwrap		= TRUE;
	var	$wrapchars		= "76";
	var	$mailtype		= "text";
	var	$charset		= "utf-8";
	var	$multipart		= "mixed";
	var $alt_message	= '';
	var	$validate		= FALSE;
	var	$priority		= "3";
	var $newline		= "\r\n"; // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
	
	function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	/**
	 * Переопределение функции Get Hostname
	 * (в HELO нельзя передавать IP, поэтому добавил проверку содержит ли SERVER_NAME ip)
	 *
	 * @access	protected
	 * @return	string
	 */
	protected function _get_hostname()
	{	
		return (isset($_SERVER['SERVER_NAME']) && preg_match('/^[0-9.]*$/i',$_SERVER['SERVER_NAME']) == 0) ? $_SERVER['SERVER_NAME'] : 'promedweb';
	}

	/**
	 * отправка от имени промедвеб
	 */
	function sendPromed($to_email, $subject, $message, $from_name = '', $attachments = array())
	{
		if (empty($from_name))
		{
			$from_name = SMTP_EMAIL_DESCR;
		}
		foreach($attachments as $attachment) {
			$this->attach($attachment);
		}
		$this->smtp_user = SMTP_EMAIL;
		$this->smtp_pass = SMTP_EMAIL_PASS;
		$this->from(SMTP_EMAIL, $from_name);
		return $this->doSend($to_email, $subject, $message);
	}

	/**	
	 * отправка от имени робота регистрации
	 */
	function sendKvrachu($to_email, $subject, $message, $from_name = '', $wordwrap = true) {
		if (empty($from_name))
		{
			$from_name = KVRACHU_MAIL_DESCR;
		}
		$this->smtp_user = KVRACHU_MAIL;
		$this->smtp_pass = KVRACHU_MAIL_PASS;
		$this->wordwrap = $wordwrap === false ? false : true;
		$this->from(KVRACHU_MAIL, $from_name);
		if (defined('KVRACHU_MAIL_FOOTER')) {
			$subject = $subject . "/r/n" . KVRACHU_MAIL_FOOTER;
		}
		return $this->doSend($to_email, $subject, $message);
	}

	/**
	 * непосредственно отправка
	 */
	function doSend($to_email, $subject, $message)
	{
		$this->to($to_email);
		$this->subject($subject);
		$this->message($message);
		return $this->send();
	}
}