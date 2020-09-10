<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Ufa_EvnPLDispTeenInspection_model - модель для работы с талонами по периодическим осмотрам несовершеннолетних (Уфа)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Valery Bondarev
 * @version      01.2020
 */
require_once(APPPATH . 'models/_pgsql/EvnPLDispTeenInspection_model.php');

class Ufa_EvnPLDispTeenInspection_model extends EvnPLDispTeenInspection_model
{
	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Проверка на возможность добавления осмотра человеку
	 */
	function checkEvnPLDispTeenInspectionCanBeSaved($data, $mode)
	{
		if (in_array($data['DispClass_id'], array(9, 10))) {
			// При сохранении карты осмотра, если на дату начала медицинского осмотра пациенту меньше 3 лет и он прикреплен НЕ к текущей МО, выводить сообщение: «Дети младше 3-х лет должны проходить профилактический осмотр по месту основного прикрепления. ОК». При нажатии «ОК», сообщение закрывать, сохранение карты отменить.
			$age = $this->getFirstResultFromQuery("select dbo.Age2(Person_BirthDay, :EvnPLDispTeenInspection_setDate) as \"Person_BirthDay\" from v_PersonState where Person_id = :Person_id", $data);
			if (!empty($age) && $age < 3) {
				$sql = "
					SELECT
						count(PersonCard_id) as \"count\"
					FROM v_PersonCard_all
					WHERE
						Person_id = :Person_id
						and Lpu_id " . getLpuIdFilter($data) . "
						and LpuAttachType_id = 1
						and PersonCard_begDate <= :EvnPLDispTeenInspection_setDate
						and cast(COALESCE(PersonCard_endDate, '2030-01-01') as date) >= :EvnPLDispTeenInspection_setDate
				";
				$res = $this->db->query($sql, $data);

				if (!is_object($res)) {
					return 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')';
				}

				$sel = $res->result('array');

				if (is_array($sel) && count($sel) > 0 && empty($sel[0]['count'])) {
					return 'Дети младше 3-х лет должны проходить профилактический осмотр по месту основного прикрепления';
				}
			}
		}

		return '';
	}
}
