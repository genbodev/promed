<?php
  include('datamatrix_barcode/php-barcode.php');
 
  	$fontSize = 10; // GD1 in px ; GD2 in point
  	$marge = 10; // between barcode and hri in pixel
  	$x = 75;  // barcode center
  	$y = 75;  // barcode center
 	// $height = 50;  // barcode height in 1D ; module size in 2D
  	$width = 1.5;  // barcode height in 1D ; not use in 2D
  	$angle = 0; // rotation in degrees
	$type = 'datamatrix';
	$code = $_GET['s'];
  
	// ————————————————– //
	// ALLOCATE GD RESSOURCE
	// ————————————————– //
	$im = imagecreatetruecolor(150, 150);
	$black = ImageColorAllocate($im,0x00,0x00,0x00);
	$white = ImageColorAllocate($im,0xff,0xff,0xff);
	imagefilledrectangle($im, 0, 0, 151, 151, $white);

	// ————————————————– //
	// GENERATE
	// ————————————————– //
	$data = Barcode::gd($im, $black, $x, $y, $angle, $type,   array('code'=>$code), $width, $height);
	header('Content-type: image/gif');
	imagegif($im);
	imagedestroy($im);
?>