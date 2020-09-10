<?php
isset($_GET['s']) or die;//���� �������� �� �������, ����� �����������
//strlen($_GET['s'])<=13 or die('>13 digits');//������ ���� ������ 13 ��������

$s = $_GET['s'];

if (!empty($_GET['barcodeFormat'])) {
	$format = $_GET['barcodeFormat'];
} else {
	$format = 128;
}

// Including all required classes
require_once('class/BCGFontFile.php');
require_once('class/BCGColor.php');
require_once('class/BCGDrawing.php');

//Including the barcode technology
//require_once('class/BCGean13.barcode.php');//���13

//https://redmine.swan.perm.ru/issues/58135 - ������ �������� �����-����� � ���13 �� Code128/Code39

switch($format){
	case 39:
		require_once('class/BCGcode39.barcode.php');//Code39
	break;
	default:
		require_once('class/BCGcode128.barcode.php');//Code128
	break;
}

// Loading Font
if (!isset($_GET['disableBarcodeText'])) {
	$font = new BCGFontFile(__DIR__ . '/class/font/Arial.ttf', 11);
} else {
	$font = 0;
}

// The arguments are R, G, B for color.
$color_black = new BCGColor(0, 0, 0);
$color_white = new BCGColor(255, 255, 255);


$drawException = null;

try {
    //$code = new BCGean13();
    $code = $format==39?(new BCGcode39()):(new BCGcode128());
	if ($format==128) {
		$code->setStart(null); // �������������� �����
	}
    $code->setScale(1); // Resolution
    $code->setThickness(30); // Thickness
    ($s == '0000000000000')?($code->setForegroundColor($color_white)):($code->setForegroundColor($color_black)); // Color of bars
    $code->setBackgroundColor($color_white); // Color of spaces
    $code->setFont($font); // Font (or 0)
    $code->parse($s); // Text
} catch(Exception $exception) {
    $drawException = $exception;
}

/* Here is the list of the arguments
1 - Filename (empty : display on screen)
2 - Background color */
$drawing = new BCGDrawing('', $color_white);
if($drawException) {
    $drawing->drawException($drawException);
} else {
	$drawing->setBarcode($code);
	$drawing->draw();
	
	$k = 0.2645833333333; // �� � ����� �������
	$wp = (!empty($_GET['width']))?intval((int)$_GET['width']/$k):0; // ������ ��������
	$l = (!empty($_GET['left']))?intval((int)$_GET['left']/$k):0; // ������ �����
	$r = (!empty($_GET['right']))?intval((int)$_GET['right']/$k):0; // ������ �����
	// �� ���������� ��������� top, bottom, left, right
	$drawing->frame($wp, 0);
}

// Header that says it is an image (remove it if you save the barcode to a file)
header('Content-Type: image/png');

// Draw (or save) the image into PNG format.
$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
?>