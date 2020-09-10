<?php
/**
* PDF_helper - хелпер для печати PDF
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Vlasenko Dmitry
* @version      ?
*/

defined('BASEPATH') or die ('No direct script access allowed');
	
/**
 * Печать PDF
 */
function print_pdf( $plugin = 'mpdf', $orient = 'landscape', $format = 'A4', $font_size = '10', $margin_left = 10, $margin_right = 10, $margin_top = 10, $margin_bottom = 10, $html = '', $output = 'document.pdf', $mode = 'I', $line_height = 1.3 )
{
	$CI = & get_instance();
	$upload_path = './'.IMPORTPATH_ROOT;
	$htmlfile = $upload_path . 'tmp' . time(). '.html';

	switch ($plugin)
	{
		case 'wkpdf':
			$CI->load->library('wkpdf');
			$pdf = new WKPDF();
			$pdf->set_tmp($htmlfile);
			$pdf->set_orientation($orient);
			$pdf->set_page_size($format);
			$pdf->set_margins($margin_bottom, $margin_left, $margin_right, $margin_top);
			$pdf->set_html($html);
			$pdf->render();
			return $pdf->output($mode, $output);
		break;

		default:			
			require_once('vendor/autoload.php');
		
			$paper_orient = '';
			if ($orient == 'landscape')
			{
				$paper_orient = '-L';
			}

			$createMPDF = function ($format, $paper_orient, $font_size, $margin_left, $margin_right, $margin_top, $margin_bottom, $line_height){
				$mpdf = new \Mpdf\Mpdf([
					'mode' => 'utf-8',		// кодировка (по умолчанию UTF-8)
					'format' => $format.$paper_orient,		// - формат документа
					'default_font_size' => $font_size,
					'margin_left' => $margin_left,
					'margin_right' => $margin_right,
					'margin_top' => $margin_top,
					'margin_bottom' => $margin_bottom,
					'orientation' => 'P',
					'useFixedNormalLineHeight' => false,
					'useFixedTextBaseline' => false,
					'adjustFontDescLineheight' => $line_height
				]);

				if (!defined('USE_UTF') || !USE_UTF) {
					$mpdf->charset_in = 'cp1251';
				}
				//$mpdf->useOnlyCoreFonts = true; //v < 7
				$mpdf->list_indent_first_level = 0;
				$mpdf->PDFA = true;
				$mpdf->PDFAauto = true;

				return $mpdf;
			};

			$mpdf = $createMPDF($format, $paper_orient, $font_size, $margin_left, $margin_right, $margin_top, $margin_bottom, $line_height);
			$mpdf->WriteHTML($html);

			//$pages = $mpdf->getPageCount(); // v6.0
			$pages = $mpdf->page; // v8.0
			$marker = stripos($html, '<marker>');

			if($marker !== false && $pages == 1) {
				unset($mpdf);
				$mpdf = $createMPDF($format, $paper_orient, $font_size, $margin_left, $margin_right, $margin_top, $margin_bottom, $line_height);
				$html = substr($html, 0, $marker);
				$mpdf->WriteHTML($html);
				return $mpdf->Output($output, $mode);
			} else {
				return $mpdf->Output($output, $mode);
			}

		break;
	}
}