<?php
$type = 'pdf417';
if (!empty($_GET['type'])) {
	$type = $_GET['type'];
}

if (empty($_GET['s'])) {
	exit();
}

$s = $_GET['s'];

switch ($type) {
	case 'ean13':
		include('barcode/ean13/barcode.php');

		/*@header('Content-Type: image/png');
		@header('Pragma: no-cache');*/

		new Barcode('ean13', $s, 2);

		break;
	case 'pdf417':
		require('barcode/BCGColor.php');
		require('barcode/BCGDrawing.php');
		include('barcode/BCGpdf417.barcode2d.php');

		@header('Content-Type: image/png');
		@header('Pragma: no-cache');

		// The arguments are R, G, B for color.
		$colorfg = new BCGColor(0, 0, 0);
		$colorbg = new BCGColor(255, 255, 255);

		$code = new BCGpdf417();
		$code->setColumn(5);
		$code->setScale(1);
		$code->setErrorLevel(3);
		$code->setColor($colorfg, $colorbg);
		$code->parse($s);

		$drawing = new BCGDrawing('', $colorfg);
		$drawing->setBarcode($code);
		$drawing->draw();

		$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
		break;
}
?>