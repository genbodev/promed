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

class CertificateCenter_model extends swPgModel {
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
			select CC.CertificateCenter_id as \"CertificateCenter_id\"
            from v_CertificateCenter CC
                 INNER JOIN LATERAL
                 (
                   select CCSH.CertificateCenterStatus_id
                   from v_CertificateCenterStatusHist CCSH
                   where CCSH.CertificateCenter_id = CC.CertificateCenter_id
                   order by CCSH.CertificateCenterStatusHist_begDate desc
                   limit 1
                 ) CCSH ON true
            where CC.CertificateCenter_INN =:CertificateCenter_INN and
                  CC.CertificateCenter_Ogrn =:CertificateCenter_Ogrn and
                  CCSH.CertificateCenterStatus_id = 2
            LIMIT 1
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