<?php
	include(APPPATH.'libraries/qbarcode/BCGFontFile.php');
	include(APPPATH.'libraries/qbarcode/BCGDrawing.php');
	include_once(APPPATH.'libraries/qbarcode/BCGColor.php');
	include_once(APPPATH.'libraries/qbarcode/BCGcode128.barcode.php');
	
	class QBarcode {
		static function getBarcode128Base64($text) {
			$font = new BCGFontFile(APPPATH . 'libraries/qbarcode/font/Arial.ttf', 18);
			$colorFront = new BCGColor(0, 0, 0);
			$colorBack = new BCGColor(255, 255, 255);

			$code = new BCGcode128();
			$code->setScale(2);
			$code->setThickness(30);
			$code->setForegroundColor($colorFront);
			$code->setBackgroundColor($colorBack);
			$code->setFont($font);
			$code->setStart(null);
			$code->setTilde(true);
			$code->parse($text);
	
			$drawing = new BCGDrawing('', $colorBack);
			$drawing->setBarcode($code);
			$drawing->draw();
	
			ob_start();
			$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
			$contents =  ob_get_contents();
			ob_end_clean();
	
			return base64_encode($contents);
		}
	}

?>
