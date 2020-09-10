<?php
/**
 * BCGBarcode2D.php
 *--------------------------------------------------------------------
 *
 * Base class for Barcode2D
 *
 *--------------------------------------------------------------------
 * Revision History
 * v2.00	23 apr	2008	Jean-Sébastien Goupil	New Version Update
 * v0.8		19 feb	2008	Jean-Sébastien Goupil	First Beta
 *--------------------------------------------------------------------
 * $Id: BCGBarcode2D.php,v 1.1.1.1 2008/07/10 04:27:06 jsgoupil Exp $
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
include_once('BCGColor.php');
include_once('BCGBarcode.php');

abstract class BCGBarcode2D extends BCGBarcode {
	protected $scaleX, $scaleY;			// ScaleX and Y multiplied by the scale

	protected function __construct() {
		parent::__construct();

		$this->setScaleX(1);
		$this->setScaleY(1);
	}

	/**
	 * Returns the maximal size of a barcode.
	 * [0]->width
	 * [1]->height
	 *
	 * @return int[]
	 */
	public function getMaxSize() {
		$size = parent::getMaxSize();
		return array($size[0] * $this->scaleX, $size[1] * $this->scaleY);
	}

	protected function setScaleX($scaleX) {
		$scaleX = intval($scaleX);
		if($scaleX <= 0) {
			$scaleX = 1;
		}
		$this->scaleX = $scaleX;
	}

	protected function setScaleY($scaleY) {
		$scaleY = intval($scaleY);
		if($scaleY <= 0) {
			$scaleY = 1;
		}
		$this->scaleY = $scaleY;
	}

	protected function drawPixel($im, $x, $y, $color = self::COLOR_FG) {
		$scaleX = $this->scale * $this->scaleX;
		$scaleY = $this->scale * $this->scaleY;

		$xR = ($x + $this->offsetX) * $scaleX;
		$yR = ($y + $this->offsetY) * $scaleY;
		// we always draw a rectangle
		imagefilledrectangle($im,
			$xR,
			$yR,
			$xR + $scaleX - 1,
			$yR + $scaleY - 1,
			$this->getColor($im, $color));
	}

	protected function drawRectangle($im, $x1, $y1, $x2, $y2, $color = BCGBarcode::COLOR_FG) {
		$scaleX = $this->scale * $this->scaleX;
		$scaleY = $this->scale * $this->scaleY;

		if($this->scale === 1) {
			imagerectangle($im,
				($x1 + $this->offsetX) * $scaleX,
				($y1 + $this->offsetY) * $scaleY,
				($x2 + $this->offsetX) * $scaleX,
				($y2 + $this->offsetY) * $scaleY,
				$this->getColor($im, $color));
		} else {
			imagefilledrectangle($im, ($x1 + $this->offsetX) * $scaleX, ($y1 + $this->offsetY) * $scaleY, ($x2 + $this->offsetX) * $scaleX + $scaleX - 1, ($y1 + $this->offsetY) * $scaleY + $scaleY - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, ($x1 + $this->offsetX) * $scaleX, ($y1 + $this->offsetY) * $scaleY, ($x1 + $this->offsetX) * $scaleX + $scaleX - 1, ($y2 + $this->offsetY) * $scaleY + $scaleY - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, ($x2 + $this->offsetX) * $scaleX, ($y1 + $this->offsetY) * $scaleY, ($x2 + $this->offsetX) * $scaleX + $scaleX - 1, ($y2 + $this->offsetY) * $scaleY + $scaleY - 1, $this->getColor($im, $color));
			imagefilledrectangle($im, ($x1 + $this->offsetX) * $scaleX, ($y2 + $this->offsetY) * $scaleY, ($x2 + $this->offsetX) * $scaleX + $scaleX - 1, ($y2 + $this->offsetY) * $scaleY + $scaleY - 1, $this->getColor($im, $color));
		}
	}

	protected function drawFilledRectangle($im, $x1, $y1, $x2, $y2, $color = BCGBarcode::COLOR_FG) {
		if($x1 > $x2) { // Swap
			$x1 ^= $x2 ^= $x1 ^= $x2;
		}
		if($y1 > $y2) { // Swap
			$y1 ^= $y2 ^= $y1 ^= $y2;
		}

		$scaleX = $this->scale * $this->scaleX;
		$scaleY = $this->scale * $this->scaleY;

		imagefilledrectangle($im,
			($x1 + $this->offsetX) * $scaleX,
			($y1 + $this->offsetY) * $scaleY,
			($x2 + $this->offsetX) * $scaleX + $scaleX - 1,
			($y2 + $this->offsetY) * $scaleY + $scaleY - 1,
			$this->getColor($im, $color));
	}
};
?>