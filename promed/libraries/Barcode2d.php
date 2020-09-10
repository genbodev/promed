<?php
	include(APPPATH.'libraries/barcode/BCGpdf417.Barcode2d.php');
	include(APPPATH.'libraries/barcode/BCGDrawing.php');
	include_once(APPPATH.'libraries/barcode/BCGColor.php');
	
	class Barcode2d extends BCGpdf417 {
		function __construct() {
			parent::__construct();
		}
	}
?>