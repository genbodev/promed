<?php
require('BCGColor.php');
require('BCGDrawing.php');
require('BCGpdf417.barcode2d.php');

// Aztec Part
$code = new BCGpdf417();
$code->setScale(3);
$code->setColumn(1);
$code->setErrorLevel(2);
$code->setTruncated(false);
$code->setMargin(true);
$code->parse('PDF 417');

// Drawing Part
$color_black = new BCGColor(0,0,0);
$color_white = new BCGColor(255,255,255);
$drawing = new BCGDrawing('', $color_white);
$drawing->setBarcode($code);
$drawing->draw();

header('Content-Type: image/png');

$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
?>