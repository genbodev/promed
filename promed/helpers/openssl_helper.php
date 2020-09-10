<?php
/**
* openssl_helper - хелпер для работы с openssl и сертификатами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Vlasenko Dmitry
* @version      31.08.2018
*/

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Функция для получения строки сертификата, валидной для использования в фукнции openssl_x509_read
 */
function getCertificateFromString($certString, $onlyBase64 = false) {
	// проверим, есть ли заголовок
	if (substr($certString, 0, 27) != '-----BEGIN CERTIFICATE-----') {
		// проверим, возможно файл уже в base64
		$certInBase64 = str_replace("\r\n", "", $certString); // удаляем лишнее
		$certInBase64 = str_replace("\n", "", $certInBase64); // остался сертфиикат в base64
		if (base64_decode($certInBase64, true) === false) {
			$certInBase64 = base64_encode($certString); // сертификат в base64
		}
	} else {
		$certInBase64 = str_replace("-----BEGIN CERTIFICATE-----", "", $certString); // удаляем лишнее
		$certInBase64 = str_replace("-----END CERTIFICATE-----", "", $certInBase64); // удаляем лишнее
		$certInBase64 = str_replace("\r\n", "", $certInBase64); // удаляем лишнее
		$certInBase64 = str_replace("\n", "", $certInBase64); // остался сертфиикат в base64
	}

	if ($onlyBase64) {
		return $certInBase64;
	}

	return "-----BEGIN CERTIFICATE-----".PHP_EOL.implode(PHP_EOL,str_split($certInBase64, 76)).PHP_EOL."-----END CERTIFICATE-----"; // разделяем на куски по 76 символов
}

/**
 * Функция получения алгоритма из сертификата
 */
function getCertificateAlgo($certString) {
	$resp = array(
		'digestMethod' => 'http://www.w3.org/2001/04/xmldsig-more#gostr3411',
		'digestOid' => '1.2.643.2.2.9',
		'signatureMethod' => 'http://www.w3.org/2001/04/xmldsig-more#gostr34102001-gostr3411',
		'signatureOid' => '1.2.643.2.2.19'
	);

	$res = @openssl_x509_read(getCertificateFromString($certString));
	if (!empty($res)) {
		openssl_x509_export($res, $out, FALSE);
		$signature_algorithm = null;
		if (preg_match('/^\s+Public Key Algorithm:\s*(.*)\s*$/m', $out, $match)) {
			$signature_algorithm = $match[1];
		}
	}

	switch($signature_algorithm) {
		case '1.2.643.7.1.1.1.1':
		case 'GOST R 34.10-2012 with 256 bit modulus':
			$resp = array(
				'digestMethod' => 'urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-256',
				'digestOid' => '1.2.643.7.1.1.2.2',
				'signatureMethod' => 'urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-256',
				'signatureOid' => '1.2.643.7.1.1.1.1'
			);
			break;
		case '1.2.643.7.1.1.1.2':
		case 'GOST R 34.10-2012 with 512 bit modulus':
			$resp = array(
				'digestMethod' => 'urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-512',
				'digestOid' => '1.2.643.7.1.1.2.3',
				'signatureMethod' => 'urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-512',
				'signatureOid' => '1.2.643.7.1.1.1.2'
			);
			break;
	}

	return $resp;
}

/**
 * Функция для получения хэша строки
 */
function getCryptCpHash($toHash, $certString) {
	$certAlgo = getCertificateAlgo($certString);

	$CI = &get_instance();
	$openssl_path = $CI->config->item('OPENSSL_PATH');
	$openssl_conf = $CI->config->item('OPENSSL_CONF');
	if (!empty($openssl_conf)) {
		putenv("OPENSSL_CONF={$openssl_conf}");
	}
	if (empty($openssl_path)) {
		throw new Exception('Не задан путь для OpenSSL. Обратитесь к администратору.');
	}

	switch($certAlgo['digestOid']) {
		case '1.2.643.7.1.1.2.2':
			$hashMethod = 'md_gost12_256';
			break;
		case '1.2.643.7.1.1.2.3':
			$hashMethod = 'md_gost12_512';
			break;
		default:
			$hashMethod = 'md_gost94';
			break;
	}

	$path = EXPORTPATH_ROOT . "cryptcp_temp";
	if (!file_exists($path)) {
		mkdir($path);
	}
	$tempfile = $path . "/" . time() . "_" . rand(1000, 9999) . ".file";
	file_put_contents($tempfile, $toHash);
	$exec = "\"{$openssl_path}\" dgst -{$hashMethod} {$tempfile}";
	// var_dump($exec);
	$output = null;
	$return_var = null;
	exec($exec, $output, $return_var);
	@unlink($tempfile);

	if (!empty($output[0])) {
		$hash = mb_substr($output[0], mb_strpos($output[0], ')= ') + 3);
	}
	if (!empty($hash)) {
		return base64_encode(hex2bin($hash));
	} else {
		$CI->load->library('textlog', array('file' => 'OpenSSL_' . date('Y-m-d') . '.log'), 'openssl_log');
		$CI->openssl_log->add('Ошибка вычисления хэша с помощью OpenSSL');
		if (!empty($openssl_conf)) {
			$CI->openssl_log->add("OPENSSL_CONF={$openssl_conf}");
		} else {
			$CI->openssl_log->add("Не задан путь к конфигу OpenSSL (OPENSSL_CONF)");
		}
		$CI->openssl_log->add("Команда: " . $exec);
		$CI->openssl_log->add("Код завершения: " . $return_var);
		$CI->openssl_log->add("Ответ: " . print_r($output, true));
		throw new Exception('Ошибка вычисления хэша с помощью OpenSSL. Обратитесь к администратору.');
	}
}

/**
 * Проверка центра сертификации
 */
function checkCertificateCenter($cert) {
	$CI = &get_instance();
	$disableCheck = $CI->config->item('EMD_DISABLE_CERT_CENTER_CHECK');
	if (!empty($disableCheck)) {
		return true;
	}

	$res = @openssl_x509_read(getCertificateFromString($cert));
	if (!empty($res)) {
		openssl_x509_export($res, $out, FALSE);
		$ogrn = null;
		if (preg_match('/^\s+Issuer:\s*.*OGRN=([0-9]+).*\s*$/m', $out, $matches)) {
			if (!empty($matches[1])) {
				$ogrn = preg_replace('/^0*/', '', $matches[1]);
			}
		}
		if (empty($ogrn)) {
			throw new Exception('Не найден ОГРН УЦ в сертификате, невозможно проверить аккредитацию УЦ.');
		}
		$inn = null;
		if (preg_match('/^\s+Issuer:\s*.*INN=([0-9]+).*\s*$/m', $out, $matches)) {
			if (!empty($matches[1])) {
				$inn = preg_replace('/^0*/', '', $matches[1]);
			}
		}
		if (empty($inn)) {
			throw new Exception('Не найден ИНН УЦ в сертификате, невозможно проверить аккредитацию УЦ.');
		}

		$CI->load->model('CertificateCenter_model');
		return $CI->CertificateCenter_model->checkCertificateCenter([
			'CertificateCenter_INN' => $inn,
			'CertificateCenter_Ogrn' => $ogrn
		]);
	}
	return false;
}

/**
 * Проверка подписи с помощью OpenSSL
 */
function checkSignature($cert, $text, $signature, $isCades = false, $textInFile = false) {
	$CI = &get_instance();

	$openssl_path = $CI->config->item('OPENSSL_PATH');
	$openssl_conf = $CI->config->item('OPENSSL_CONF');
	if (!empty($openssl_conf)) {
		putenv("OPENSSL_CONF={$openssl_conf}");
	}

	// создаём временную папку
	$path = EXPORTPATH_ROOT . "openssl_temp";
	if (!file_exists($path)) {
		mkdir($path);
	}

	$filename = time() . "_" . rand(1000, 9999);
	$tempfile = realpath($path) . "/" . $filename . '_pubkey.key';

	$certfile = realpath($path) . "/" . $filename . '_cert.cer';
	file_put_contents($certfile, base64_decode($cert));

	if ($textInFile) {
		$datafile = $text;
	} else {
		$datafile = realpath($path) . "/" . $filename . '_data.dat';
		file_put_contents($datafile, $text);
	}

	$signfile = realpath($path) . "/" . $filename . '_sign.sig';
	file_put_contents($signfile, $signature);

	if ($isCades) {
		// проверка cades
		$exec = "\"{$openssl_path}\" cms -verify -verify_retcode -binary -in {$signfile} -inform DER -content {$datafile} -noverify -out {$tempfile}"; // для cades, $tempfile используем в качестве временного для вывода всего лишнего
		$openssl_result = exec($exec, $output, $return_var);

		if ($return_var === 0) {
			return true;
		}
	} else {
		// проверка подписи с помощью OpenSSL: openssl dgst -verify путь_к_сертификату -signature путь_к_подписи путь_к_данным
		// извлекаем публичный ключ из сертификата
		$exec = "\"{$openssl_path}\" x509 -inform DER -in {$certfile} -pubkey -noout";
		$output = null;
		exec($exec, $output);
		if (!empty($output)) {
			$pubkeystr = '';
			foreach ($output as $output_one) {
				$pubkeystr .= $output_one . PHP_EOL;
			}

			file_put_contents($tempfile, $pubkeystr);

			$exec = "\"{$openssl_path}\" dgst -verify {$tempfile} -signature {$signfile} {$datafile}";
			$openssl_result = exec($exec, $output);

			if (!empty($openssl_result) && $openssl_result == 'Verified OK') {
				return true;
			}
		}

		// удаляем за собой следы
		@unlink($certfile);
		@unlink($tempfile);
		if (!$textInFile) {
			@unlink($datafile);
		}
		@unlink($signfile);
	}

	return false;
}

?>