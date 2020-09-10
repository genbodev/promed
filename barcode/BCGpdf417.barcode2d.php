<?php
/**
 * BCGpdf417.Barcode2d.php
 *--------------------------------------------------------------------
 *
 * Class to create PDF417 barcode
 *
 *--------------------------------------------------------------------
 * Revision History
 * v2.01	15 jul	2008	Jean-S�bastien Goupil	Fix bugs
 * v2.00	23 apr	2008	Jean-S�bastien Goupil	New Version Update
 * v0.8		19 feb	2008	Jean-S�bastien Goupil	First Beta
 *--------------------------------------------------------------------
 * $Id: BCGpdf417.Barcode2d.php,v 1.3 2008/07/16 04:50:28 jsgoupil Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
include_once('BCGpdf417_table.php');
include_once('BCGpdf417_rscoef.php');
include_once('BCGBarcode2D.php');

define('PDF417_UNKNOWN',0);			// Not supported yet
define('PDF417_TM',	1);			// Text Mode, 2char per keyword
define('PDF417_NM',	2);			// Numeric Mode, 2.9num per keyword
define('PDF417_BM',	3);			// Binary Mode, 1.2byte per keyword
class BCGpdf417 extends BCGBarcode2D {
	// Current mode in Text
	const CHAR_MAJ = 0;			// Capital Letters
	const CHAR_MIN = 1;			// Lower Case
	const CHAR_MIX = 2;			// Mix Letters
	const CHAR_PON = 3;			// Ponctuation

	// Static constant variables (initialize)
	private $TEXT_HT		= NULL; // Char: Horizontal Tab
	private $TEXT_LF		= NULL; // Char: Line Feed
	private $TEXT_CR		= NULL; // Char: Carriage Feed
	private $TEXT_MIN		= NULL; // Switch to Text->lower case
	private $TEXT_MAJ		= NULL; // Switch to Text->capital letters
	private $TEXT_MIX		= NULL; // Switch to Text->mix letters
	private $TEXT_PON		= NULL; // Switch to Text->ponctuation
	private $TEXT_T_MAJ		= NULL; // Switch to Text->capital letters for next char only
	private $TEXT_T_PON		= NULL; // Switch to Text->ponctuation for next char only
	private $TEXT_allowed		= NULL; // All characters allowed in PDF417
	private $TEXT_sub		= NULL; // Array containing the char to switch
	private $TEXT_code		= NULL; // Array containing all the char for "capital", "lower", "mix", and "pon"
	private $METHOD			= NULL; // Array of method available to create PDF417 (PDF417_TM, PDF417_NM, PDF417_BM)

	private $pdf417_table		= NULL; // Table containing all the keywords to create the PDF417
	private $pdf417_rscoef		= NULL; // Table containing coefficient numbers instead of doing complex math

	protected $text;			// Saved Text
	protected $column, $errorlevel;		// Column number and error level asked
	protected $data, $errorCode;
	protected $truncated;
	protected $margin;

	/**
	 * Constructor to create PDF417 barcode.
	 *
	 * This method calls other public functions to set default values
	 */
	public function __construct() {
		global $pdf417_table, $pdf417_rscoef;

		if($this->pdf417_rscoef === NULL) {
			$this->initialize($pdf417_table, $pdf417_rscoef);
		}

		parent::__construct();
		$this->setScaleX(3);
		$this->setScaleY(5);

		$this->setColumn(-1);
		$this->setErrorLevel(-1);
		$this->setTruncated(false);
		$this->setMargin(true);
	}

	/**
	 * Displays the left and right margin.
	 *
	 * @param bool $margin
	 */
	public function setMargin($margin = true) {
		$this->margin = (bool)$margin;
	}

	/**
	 * Truncates the barcode.
	 *
	 * By truncating, you will remove the right part
	 * of the barcode, but it will be more difficult
	 * to read it.
	 *
	 * @pacam bool $truncated
	 */
	public function setTruncated($truncated = true) {
		$this->truncated = (bool)$truncated;
	}

	/**
	 * Saves the data column number.
	 *
	 * The value must be between 1 and 30,
	 * if the value -1 is set, the column number will
	 * be calculated automatically.
	 *
	 * @param int $column
	 */
	public function setColumn($column = -1) {
		if($column <= 0 || $column > 30) {
			$this->column = -1;
		} else {
			$this->column = intval($column);
		}
	}

	/**
	 * Saves the error level.
	 *
	 * The value must be between 0 and 8,
	 * if the value -1 is set, the error level will
	 * be calculated automatically.
	 *
	 * @param int $errorlevel
	 */
	public function setErrorLevel($errorlevel = -1) {
		if($errorlevel < -1 || $errorlevel > 8) {
			$this->errorlevel = -1;
		} else {
			$this->errorlevel = intval($errorlevel);
		}
	}

	/**
	 * There are several options available to encode text.
	 *  1. If you don't specify by default the method of compaction, one will be calculated automatically. The argument should be text only
	 *  2. If you put the text into an array, it means you want to add more than one encoding.
	 *  3. To put a special encoding, you may write something like that : array(ENCODING, 'text')
	 *  4. If you have more than one encoding as specified in 2, write this : array(array(ENCODING1, 'text1'), array(ENCODING2, 'text2'));
	 *  5. You can insert into this master table a text (without encoding), refer to number 1 : array(array(ENCODING, 'text1'), 'text auto');
	 *
	 * @param mixed $text
	 */
	public function parse($text) {
		// Here, we format correctly what the user gives.
		if(!is_array($text)) {
			$text = $this->parseUnknownText($text);
		}

		// This loop checks for UnknownText AND clear wrong character if they are not allowed in the table
		$encVal = null;
		$save = array();
		foreach($text as $key1 => $val1) {			// We take each value
			if (!empty($encVal)) {
				$tmp = $this->{'setParse' . $this->METHOD[$encVal]}($val1);
				$this->text .= $tmp;
				$save = array_merge($save, array(array($encVal, $tmp)));
				$encVal = null;
				continue;
			}
			if(!is_array($val1)) {					// This is not a table
				if(is_string($val1)) {				// If it's a string, parse as unknown
					$tmp = $this->parseUnknownText($val1);
					foreach($tmp as $key2 => $val2) $this->text .= $val2[1];
					$save = array_merge($save, $tmp);
				} else {
					// it's the case of "array(ENCODING, 'text')"
					// We got ENCODING in $val1, calling 'each' again will get 'text' in $val2
					$encVal = $val1;
					continue;
				}
			} else {						// The method is specified
				// $val1[0] = ENCODING
				// $val1[1] = 'text'
				$value = isset($val1[1]) ? $val1[1] : '';	// If data available
				$tmp = $this->{'setParse' . $this->METHOD[$val1[0]]}($value);
				$this->text .= $tmp;
				$save = array_merge($save, array(array($val1[0], $tmp)));
			}
		}

		// We start parsing, $data will contains keyword numbers
		$data = array();
		$c = count($save);
		for($i = 0; $i < $c; $i++) {
			$data = array_merge($data, $this->{'parse' . $this->METHOD[$save[$i][0]]}($save[$i][1]));
		}
		// We remove the 900 if there is only that
		if($c === 1 && $data[0] === 900) {
			unset($data[0]);
			$data = array_values($data);
		}

		$this->setData($data);
	}

	public function draw(&$im) {
		if($this->errorCode === -1) {
			$this->drawError($im, 'Error in Security Level');
			return ;
		}

		if($this->column <= 0) {
			$this->drawError($im, 'Error in Column Number');
			return ;
		}

		$size = $this->calculSize();
		$totalLine = intval(count($this->data) / $this->column);
		$restartXPosition = 0;
		if($this->margin === true) {
			$restartXPosition = 2;
			$this->drawFilledRectangle($im, 0, 0, 1, $totalLine - 1, BCGBarcode::COLOR_BG);
			$this->drawFilledRectangle($im, $size[0] - 2, 0, $size[0] - 1, $totalLine - 1, BCGBarcode::COLOR_BG);
		}

		for($i = 0; $i < $totalLine; $i++) {
			$line = $i % 3;

			$x = $restartXPosition;
			$this->drawModules($im, $x, $i, 130728);								// Start Module
			$this->drawModules($im, $x, $i, $this->pdf417_table[$line][$this->getLeftMC($i, $totalLine)]);		// Left Module
			for($j = 0; $j < $this->column; $j++) {
				$this->drawModules($im, $x, $i, $this->pdf417_table[$line][$this->data[$this->column * $i + $j]]);// DATA
			}

			if($this->truncated === false) {
				$this->drawModules($im, $x, $i, $this->pdf417_table[$line][$this->getRightMC($i, $totalLine)]);		// Right Module
				$this->drawModules($im, $x, $i, 260649);								// End Module
			} else {
				// Just draw 1 single pixel
				$this->drawPixel($im, $x, $i, BCGBarcode::COLOR_FG);
			}

		}
	}

	public function getMaxSize() {
		$size = parent::getMaxSize();
		$pdfsize = $this->calculSize();

		$w = $size[0] + $pdfsize[0] * $this->scaleX * $this->scale;
		$h = $size[1] + $pdfsize[1] * $this->scaleY * $this->scale;
		return array($w, $h);
	}

	/**
	 * Initializes all variables before the use of the class.
	 *
	 * @param array $table	PDF Table of Keywords (930*3)
	 * @param array $coef	Coefficient of the multiplication for error calculation
	 */
	private function initialize($table, $coef) {
		$this->TEXT_HT		= chr(  9);
		$this->TEXT_LF		= chr( 10);
		$this->TEXT_CR		= chr( 13);
		$this->TEXT_MAJ		= chr(128);
		$this->TEXT_MIN 	= chr(129);
		$this->TEXT_MIX		= chr(130);
		$this->TEXT_PON		= chr(131);
		$this->TEXT_T_MAJ	= chr(132);
		$this->TEXT_T_PON	= chr(133);

		// Text by table allowed
		$this->TEXT_code	= array(
			'ABCDEFGHIJKLMNOPQRSTUVWXYZ ' . $this->TEXT_MIN . $this->TEXT_MIX . $this->TEXT_T_PON,
			'abcdefghijklmnopqrstuvwxyz ' . $this->TEXT_T_MAJ . $this->TEXT_MIX . $this->TEXT_T_PON,
			'0123456789&' . $this->TEXT_CR . $this->TEXT_HT . ',:#-.$/+%*=^' . $this->TEXT_PON . ' ' . $this->TEXT_MIN . $this->TEXT_MAJ . $this->TEXT_T_PON,
			';<>@[\]_`~!' . $this->TEXT_CR . $this->TEXT_HT . ',:' . $this->TEXT_LF . '-.$/"|*()?{}\'' . $this->TEXT_MAJ
		);

		// All text allowed
		$this->TEXT_allowed	= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789&,:;#-.$/+%*=^<>@[]{}()\_`~!"\'|? ' . $this->TEXT_HT . $this->TEXT_LF . $this->TEXT_CR;

		// Substitution key
		$this->TEXT_sub		= array(
						self::CHAR_MAJ => $this->TEXT_MAJ,
						self::CHAR_MIN => $this->TEXT_MIN,
						self::CHAR_MIX => $this->TEXT_MIX,
						self::CHAR_PON => $this->TEXT_PON
					);

		// Method available
		$this->METHOD		= array(PDF417_TM => 'Text', PDF417_NM => 'Number', PDF417_BM => 'Byte');

		$this->pdf417_table	= $table;
		$this->pdf417_rscoef	= $coef;
	}

	/**
	 * Removes not allowed char in $text with table "Text".
	 *
	 * @param string $text
	 */
	private function setParseText($text) {
		$tmp = preg_quote($this->TEXT_allowed, '/');
		return preg_replace('/[^' . $tmp . ']/', '', $text);		// We allow only $this->TEXT_allowed
	}

	/**
	 * Removes not allowed char in $text with table "Number".
	 *
	 * @param string $text
	 */
	private function setParseNumber($text) {
		return preg_replace('/[^0-9]/', '', $text);
	}

	/**
	 * Removes not allowed char in $text with table "Byte".
	 *
	 * @param string $text
	 */
	private function setParseByte($text) {
		// Full ASCII table allowed
		return $text;
	}

	/**
	 * Parse unknown text.
	 *
	 * The parse will try to make some blocks of 5 or more numeric compaction. After,
	 * it will create text or byte compaction depending on the text received.
	 *
	 * A bug has been found in is_numeric(), if the first char is a space, the value is
	 * considered anyway as numeric.
	 *
	 * @param string $text
	 */
	private function parseUnknownText($text) {
		// If we have 5 or more following number, we pass to NumberMethod
		// PHPBug is_numeric()  #
		$number = 0;
		$c = strlen($text);
		$tmp = array();

		$text_T = '';
		$text_B = '';
		for($i = 0; $i < $c; $i++) {
			// Do we have 5 numbers in a row?
			// We add a 1 in front of the number because we don't want to loose the '0'
			$number_str = substr($text, $i, 5);
			$number = strval(intval('1' . $number_str));
			if(strlen($number) === 6 && strpos(strtolower($number_str), 'e') === false){	// Check PHP Bug; второе условие необходимо так как в PHP 7.1 изменилось поведение intval
				$j = $i + 5;
				while(isset($text[$j]) && is_numeric($text[$j])) {	// We search how many numbers we can use
					$j++;
				}
				// We save the previous
				if(!empty($text_T)) {
					$tmp[] = array(PDF417_TM, $text_T);
					$text_T = '';
				} elseif(!empty($text_B)) {
					$tmp[] = array(PDF417_BM, $text_B);
					$text_B = '';
				}
				$tmp[] = array(PDF417_NM, substr($text, $i, $j - $i));
				$i = $j - 1;
			} else {
				$inarray = strpos($this->TEXT_allowed, $text[$i]);
				if($inarray === false && ord($text[$i]) <= 255) {	// If it's not text but binary
					// We save the previous
					if(!empty($text_T)) {
						$tmp[] = array(PDF417_TM, $text_T);
						$text_T = '';
					}
					$text_B .= $text[$i];
				} elseif($inarray >= 0) {				// If it's text
					// We save the previous
					if(!empty($text_B)) {
						$tmp[] = array(PDF417_BM, $text_B);
						$text_B = '';
					}
					$text_T .= $text[$i];
				}
			}
		}
		// We save the rest
		if(!empty($text_T)) {
			$tmp[] = array(PDF417_TM, $text_T);
		} elseif(!empty($text_B)) {
			$tmp[] = array(PDF417_BM, $text_B);
		}

		return $tmp;
	}

	/**
	 * Parses Text.
	 *
	 * The method will try to optimize the code receive in argument to transfer
	 * it into keywords.
	 * The $text value should be clean and all characters are supposed to be allowed.
	 *
	 * @param string $text
	 * @return array Keywords
	 */
	private function parseText($text) {
		$encoded = '';
		$current = 0;
		$c = strlen($text);
		for($i = 0; $i < $c; $i++) {
			$encoded .= $this->optimizeMax($current, substr($text, $i)) . $text[$i];
		}
		if((strlen($encoded) % 2) === 1) {	// DATA Odd
			// We add Padding
			if($current === self::CHAR_MAJ || $current === self::CHAR_MIN || $current === self::CHAR_MIX) {
				$encoded .= $this->TEXT_T_PON;
			} else {
				$encoded .= $this->TEXT_MAJ;
			}
		}
		return array_merge(array(900), $this->encodedForm($encoded, 0));
	}

	/**
	 * Parses Numeric.
	 *
	 * The method transforms numerical string into keywords.
	 * The $text value should be clean and all characters are supposed to be allowed.
	 *
	 * @param string $text
	 * @return array Keywords
	 */
	private function parseNumber($text) {
		$counter = 0;
		$data = array();
		// Group by 44 data
		while(strlen(substr($text, $counter * 44)) > 0) {
			$number = '1' . substr($text, $counter * 44, 44);
			$data = array_merge($data, array_reverse($this->getModulo($number)));
			$counter++;
		}
		// We merge and take the rest
		$data = array_merge(array(902), $data, array_reverse($this->getModulo(substr($text, $counter * 44))));
		return $data;
	}

	/**
	 * Parses Byte.
	 *
	 * The method transforms byte string into keywords.
	 * The $text value should be clean and all characters are supposed to be allowed.
	 *
	 * @param string $text
	 * @return array Keywords
	 */
	private function parseByte($text) {
		$data = array();
		$s = 0;
		$counter = 0;

		// We use the BC library since the number can become really big
		// We loop until we don't have any 6chars group left
		while(intval(strlen(substr($text, $counter * 6)) / 6) >= 1) {
			$c = $counter * 6 + 6;
			// Calculates $s
			for($i = $counter * 6; $i < $c; $i++) {
				// We do $s = $t[0] * 256^5 + $t[1] * 256^4 + $t[2] * 256^3 + $t[3] * 256^2 + $t[4] * 256^1 + $t[5]
				$s = bcadd($s, bcmul(ord($text[$i]), bcpow(256, 5 - $i)));
			}
			$counter++;
			$data = array_merge($data, array_reverse($this->getModulo($s)));
		}

		// If we still have some data left
		$c = strlen(substr($text, $counter * 6));
		for($i = 0; $i < $c; $i++) {
			$data[] = ord($text[$counter * 6 + $i]);
		}

		// If the number is dividable by 6, we use 924, otherwise we use 901, 913 if we have only 1 number
		$code = ((strlen($text) % 6) === 0) ? 924 : ((strlen($text) === 1) ? 913 : 901);
		return array_merge(array($code), $data);
	}

	/**
	 * Saves data into the classes.
	 *
	 * This method will save data, calculate real column number
	 * (if -1 was selected), the real error level (if -1 was
	 * selected)... It will add Padding to the end and generate
	 * the error codes.
	 *
	 * @param array $data
	 */
	private function setData($data) {
		$this->data = $data;

		if($this->errorlevel === -1) {
			$this->errorlevel = $this->calculLevel($this->data);
		}
		$this->errorCode = array_fill(0, pow(2, $this->errorlevel + 1), 0);
		$this->getColumn();							// Calculates the column number
		$this->addPad();							// Adds the padding to the data
		$this->data = array_merge(array(count($this->data) + 1), $this->data);	// Sets the keyword at the start
		$this->errorCode = $this->calculError($this->data, $this->errorlevel);	// Computes the errorCode
		$this->data = array_merge($this->data, $this->errorCode);		// Appends the errorCode to the data
	}

	/**
	 * Calculates the number of column
	 */
	private function getColumn() {
		if($this->column == -1) {
			$this->column = intval((count($this->data) + 1 + count($this->errorCode)) / 50) + 1;
		}
	}

	/**
	 * Adds padding to data
	 */
	private function addPad() {
		$padding = ($this->column - (count($this->data) + 1 + count($this->errorCode)) % $this->column) % $this->column;
		if($padding > 0) {
			$pad = array_fill(0, $padding, 900);
		} else {
			$pad = array();
		}

		$this->data = array_merge($this->data, $pad);
	}

	/**
	 * Gets Modulo 900.
	 *
	 * This method gets modulo 900 of the $s value. The method allows
	 * the using of infinite lenght of number.
	 *
	 * @param string $text
	 * @return array Keywords
	 */
	private function getModulo($s) {
		$tmp = array();
		while(bccomp($s, '0') > 0) {
			$tmp[] = bcmod($s, 900);
			$s = bcdiv($s, 900, 0);
		}
		return $tmp;
	}

	/**
	 * Encodes a Keyword for TEXT compaction.
	 *
	 * @param string $encoded	Encoded text (with special chars)
	 * @param int $current		Current pointer pointing to column of type of data (CAPS, minus, ...)
	 * @return array
	 */
	private function encodedForm($encoded, $current = 0) {
		$last_current = -1;
		$c = strlen($encoded);
		$final = array();
		// We encode Char1*30 + Char2
		for($i = 0; $i < $c; $i += 2) {
			$tmp1 = $this->getCharEncoded($current, $encoded[$i]);
			$tmp2 = $this->getCharEncoded($current, $encoded[$i + 1]);

			$final[] = $tmp1 * 30 + $tmp2;
		}
		return $final;
	}

	/**
	 * Returns the value of the text table.
	 *
	 * It will modify the $current value to save in which column
	 * the encoding text is.
	 *
	 * @param int $current
	 * @param string $char
	 * @param int
	 */
	private function getCharEncoded(&$current, $char) {
		static $temporary = -1;

		$val = strpos($this->TEXT_code[$current], $char);
		// Do we really switch?
		if(($tmp_current = array_search($char, $this->TEXT_sub)) !== false) {
			$current = $tmp_current;
			$temporary = -1;
		}
		// Or is it a temporary switch? // TODO What happens when 2 T_X following?
		elseif($char === $this->TEXT_T_PON || $char === $this->TEXT_T_MAJ) {
			// We will return to the current later
			$temporary = $current;
			$current = ($char === $this->TEXT_T_PON) ? self::CHAR_PON : self::CHAR_MAJ;
			return $val;
		}
		if($temporary !== -1) {
			$current = $temporary;
			$temporary = -1;
		}

		return $val;
	}

	/**
	 * Analyzes a char if it is a special value.
	 *
	 * The function will return the index number of the special value
	 *
	 * @param string $char
	 */
	private function analyzeChar($char) {
		if(strpos($this->TEXT_code[1], $char) !== false) {
			return self::CHAR_MIN;
		} elseif(strpos($this->TEXT_code[0], $char) !== false) {
			return self::CHAR_MAJ;
		} elseif(strpos($this->TEXT_code[3], $char) !== false) {
			return self::CHAR_PON;
		} elseif(strpos($this->TEXT_code[2], $char) !== false) {
			return self::CHAR_MIX;
		} else {
			return -1;
		}
	}

	/**
	 * This method optimizes $string depending on the next chars...
	 * it will return a T_MAJ or T_PON or a MIN, MAJ, MIX or PON in order
	 * to save the number of keywords used. It won't return anything if it's
	 * not used for.
	 *
	 * The $current value is the sub before we enter the function.
	 * The $charCurrentAnalyzed is the sub of the first char
	 * The $charNextAnalyzed is the sub of the second char
	 *
	 * @return mixed
	 */
	private function optimizeMax(&$current, $string) {
		$charExists = true;
		$charCurrentAnalyzed = $this->analyzeChar($string[0]);
		$charNextAnalyzed = NULL;
		for($counter = 1; isset($string[$counter]) && $string[$counter] === ' '; $counter++);

		if($charCurrentAnalyzed === $current) {							// We are in the good sub
			return '';
		}
		if($counter === 1 && !isset($string[1]) && $charCurrentAnalyzed === self::CHAR_PON) {	// The next char doesn't exist
			return $this->TEXT_T_PON;
		} elseif(isset($string[1])) {
			$charNextAnalyzed = $this->analyzeChar($string[1]);
			if($charNextAnalyzed === $current) {						// We have only one letter different
				if($current === self::CHAR_MIN && $charCurrentAnalyzed === self::CHAR_MAJ) {// We can only return T_MAJ or T_PON
					return $this->TEXT_T_MAJ;
				} elseif(($current === self::CHAR_MAJ || $current === self::CHAR_MIN || $current === self::CHAR_MIX) && $charCurrentAnalyzed === self::CHAR_PON) {
					return $this->TEXT_T_PON;
				}
			}
		} else {
			if($current === self::CHAR_MIN && $charCurrentAnalyzed === self::CHAR_MAJ) {	// We don't have the second char
				return $this->TEXT_T_MAJ;
			}
		}

		// SPECIAL means that we were supposed to go to MIX not to PON. because the current and next is a MIX
		if(($current === self::CHAR_MAJ || $current === self::CHAR_MIN) && $charNextAnalyzed === self::CHAR_MIX && strpos($this->TEXT_code[2], $string[0]) !== false) {
			$current = self::CHAR_MIX;
			return $this->TEXT_MIX;
		}

		// Switching completly
		if($current === self::CHAR_MAJ) {
			$current = $charCurrentAnalyzed;						// We switch current
			if($charCurrentAnalyzed === self::CHAR_MIN || $charCurrentAnalyzed === self::CHAR_MIX) {
				return $this->TEXT_sub[$charCurrentAnalyzed];
			} else {
				return $this->TEXT_MIX . $this->TEXT_PON;
			}
		} elseif($current === self::CHAR_MIN) {
			$current = $charCurrentAnalyzed;						// We switch current
			if($charCurrentAnalyzed === self::CHAR_MIX) {
				return $this->TEXT_MIX;
			} else {
				return $this->TEXT_MIX . $this->TEXT_sub[$charCurrentAnalyzed];
			}
		} elseif($current === self::CHAR_MIX) {
			$current = $charCurrentAnalyzed;						// We switch current
			return $this->TEXT_sub[$charCurrentAnalyzed];
		} elseif($current === self::CHAR_PON) {
			$current = $charCurrentAnalyzed;						// We switch current
			if($charCurrentAnalyzed === self::CHAR_MAJ) {
				return $this->TEXT_MAJ;
			} else {
				return $this->TEXT_MAJ . $this->TEXT_sub[$charCurrentAnalyzed];
			}
		}
	}

	private function calculError($data, &$errorlevel) {
		$maxMC = array(925, 923, 919, 911, 895, 863, 799, 671, 415);
		$c = count($data);

		if($c > $maxMC[$errorlevel] || $errorlevel < 0) {				// There is an error, there is to many keywords
			return -1;
		}

		$coef = $this->pdf417_rscoef[$errorlevel];

		$k = pow(2, $errorlevel + 1);
		$r = array_fill(0, $k, 0);

		// STARTING OF REED-SALOMON
		// r : return
		// tmp : temp value
		// coef : coef value ((x - 3)(x - pow(3,2))(x - pow(3,3)).....(x - pow(3,k))
		// c : Number of element
		// data : data table
		// k : pow(2,$errorlevel+1)
		for($i = 0; $i < $c; $i++) {
			$tmp = ($data[$i] + $r[$k - 1]) % 929;
			for($j = $k - 1; $j >= 0; $j--) {
				if($j === 0) {
					$r[$j] = (929 - ($tmp * $coef[$j]) % 929) % 929;
				} else {
					$r[$j] = ($r[$j - 1] + 929 - ($tmp * $coef[$j]) % 929) % 929;
				}
			}
		}
		for($j = 0; $j < $k; $j++) {
			if($r[$j] !== 0) {
				$r[$j] = 929 - $r[$j];
			}
		}

		return array_reverse($r);
	}

	private function calculLevel($data){
		$c = count($data);
		if($c <= 40) {
			return 2;
		} elseif($c <= 160) {
			return 3;
		} elseif($c <= 320) {
			return 4;
		} elseif($c <= 863) {
			return 5;
		} elseif($c <= 895) {
			return 4;
		} elseif($c <= 911) {
			return 3;
		} elseif($c <= 919) {
			return 2;
		} elseif($c <= 923) {
			return 1;
		} elseif($c <= 925) {
			return 0;
		}
	}

	private function calculSize() {
		$width = 0;
		if($this->margin === true) {
			$width += 2 + 2;				// Left and Right Space
		}

		$width += $this->column * 17;				// Columns
		$width += 2 * 17;					// Left and Right Column
		$width += (17 + 18);					// Start and Stop Column

		if($this->truncated === true) {
			$width -= 17 * 2;				// We remove the last 2 columns
		}

		// Check empty data
		$c = count($this->data);
		if($c === 0) {
			$height = 1;
		} else {
			$height = intval(count($this->data) / $this->column);
		}
		return array($width, $height);
	}

	private function getLeftMC($line, $totalLine) {
		return intval($line / 3) * 30 + $this->{'sideMC' . (($line % 3) + 1)}($totalLine);
	}

	private function getRightMC($line, $totalLine){
		return intval($line / 3) * 30 + $this->{'sideMC' . ((((($line % 3) + 1) + 1) % 3) + 1)}($totalLine);
	}

	private function sideMC1($totalLine) {
		return intval(($totalLine - 1) / 3);
	}

	private function sideMC2($totalLine) {
		return ($this->errorlevel * 3) + ($totalLine - 1) % 3;
	}

	private function sideMC3($totalLine) {
		return $this->column - 1;
	}

	private function drawModules($im, &$x, $y, $val) {
		$current = 0;
		// We search for the binary value
		$draw = array_fill(0, 9, 0);
		$bin = decbin($val);
		$c = strlen($bin);
		for($i = 0; $i < $c; $i++) {
			if($bin[$i] === '0' && ($current % 2) === 0) {
				$current++;
			} elseif($bin[$i] === '1' && ($current % 2) === 1) {
				$current++;
			}

			$draw[$current]++;
		}

		for($i = 0; $i < 9; $i++) {
			if($draw[$i] === 0) {
				continue;
			}
			$color = BCGBarcode::COLOR_FG;
			if(($i % 2) === 1) {
				$color = BCGBarcode::COLOR_BG;
			}
			$this->drawFilledRectangle($im, $x, $y, $x + $draw[$i] - 1, $y, $color);
			$x += $draw[$i];
		}
	}
};
?>