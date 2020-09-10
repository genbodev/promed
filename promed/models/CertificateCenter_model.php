<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CertificateCenter_model - модель для работы с центрами сертификации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ServiceNSI
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      15.11.2019
 */

class CertificateCenter_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Проверка центра сертификации
	 */
	function checkCertificateCenter($data) {
		$resp = $this->queryResult("
			select top 1
				CC.CertificateCenter_id
			from
				v_CertificateCenter CC (nolock)
				cross apply (
					select top 1
						CCSH.CertificateCenterStatus_id
					from
						v_CertificateCenterStatusHist CCSH (nolock)
					where
						CCSH.CertificateCenter_id = CC.CertificateCenter_id
					order by
						CCSH.CertificateCenterStatusHist_begDate desc
				) CCSH
			where
				CC.CertificateCenter_INN = :CertificateCenter_INN
				and CC.CertificateCenter_Ogrn = :CertificateCenter_Ogrn
				and CCSH.CertificateCenterStatus_id = 2
		", [
			'CertificateCenter_INN' => $data['CertificateCenter_INN'],
			'CertificateCenter_Ogrn' => $data['CertificateCenter_Ogrn']
		]);

		if (!empty($resp[0]['CertificateCenter_id'])) {
			return true;
		}

		return false;
	}
}