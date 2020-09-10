<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * UslugaComplexMedService_model - Модель для работы с услугами на службе
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2018 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      08.2018
 */
class Lis_UslugaComplexMedService_model extends SwPgModel {
	/**
	 * Конструктор объекта
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Сохранение для postgre
	*/
	function doSaveUslugaComplexMedService($data) {
		// проверка на дубли
		$filter = "";

		$query = "
			select
				uslugacomplexmedservice_id as \"UslugaComplexMedService_id\",
				uslugacomplexmedservice_pid as \"UslugaComplexMedService_pid\",
				medservice_id as \"MedService_id\",
				uslugacomplex_id as \"UslugaComplex_id\",
				uslugacomplexmedservice_begdt as \"UslugaComplexMedService_begDT\",
				uslugacomplexmedservice_enddt as \"UslugaComplexMedService_endDT\",
				refsample_id as \"RefSample_id\",
				lpuequipment_id as \"LpuEquipment_id\",
				uslugacomplexmedservice_time as \"UslugaComplexMedService_Time\",
				uslugacomplex_name as \"UslugaComplex_name\",
				uslugacomplexmedservice_isportalrec as \"UslugaComplexMedService_IsPortalRec\",
				uslugacomplexmedservice_ispay as \"UslugaComplexMedService_IsPay\",
				uslugacomplexmedservice_iselectronicqueue as \"UslugaComplexMedService_IsElectronicQueue\",
				uslugacomplexmedservice_insdt as \"UslugaComplexMedService_insDT\",
				pmuser_insid as \"pmUser_insID\",
				uslugacomplexmedservice_upddt as \"UslugaComplexMedService_updDT\",
				pmuser_updid as \"pmUser_updID\"
			from
				v_UslugaComplexMedService
			where
				UslugaComplexMedService_id = :UslugaComplexMedService_id
			limit 1
		";

		if (!empty($data['UslugaComplexMedService_id'])) {
			$filter .= " and ucms.UslugaComplexMedService_id <> :UslugaComplexMedService_id";
		}
		
		$res = $this->queryResult($query, $data);

		if (empty($res)) {
			$res = [];
		} else {
			$res = $res[0];
		}
		$data = array_merge($res, $data);

		$resp = $this->queryResult("
			select
				ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from
				v_UslugaComplexMedService ucms
			where
				ucms.UslugaComplex_id = :UslugaComplex_id
				and ucms.MedService_id = :MedService_id
				and coalesce(ucms.UslugaComplexMedService_pid, 0) = coalesce(CAST(:UslugaComplexMedService_pid as bigint), 0)
				{$filter}
			limit 1	
		", $data);
		if (!empty($resp[0]['UslugaComplexMedService_id'])) {
			throw new Exception('Обнаружено дублирование услуги на службе, сохранение невозможно', 400);
		}

		if ($data['UslugaComplexMedService_id'])
			$procedure = 'p_UslugaComplexMedService_upd';
		else $procedure = 'p_UslugaComplexMedService_ins';

		if (!empty($data['UslugaComplexMedService_IsSeparateSample'])
			&& ($data['UslugaComplexMedService_IsSeparateSample'] == "true" || $data['UslugaComplexMedService_IsSeparateSample'] == 2))
			$data['UslugaComplexMedService_IsSeparateSample'] = 2;
		else
			$data['UslugaComplexMedService_IsSeparateSample'] = 1;

		$begDT = ($data['UslugaComplexMedService_begDT'] == '@curDT')
			? "UslugaComplexMedService_begDT := dbo.tzgetdate(),"
			: "UslugaComplexMedService_begDT := :UslugaComplexMedService_begDT,";
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\",
				UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from {$procedure}(
				UslugaComplexMedService_id := :UslugaComplexMedService_id,
				MedService_id := :MedService_id,
				UslugaComplex_id := :UslugaComplex_id,
				UslugaComplexMedService_IsSeparateSample := :UslugaComplexMedService_IsSeparateSample,
				{$begDT}
				UslugaComplexMedService_endDT := :UslugaComplexMedService_endDT,
				RefSample_id := :RefSample_id,
				UslugaComplexMedService_pid := :UslugaComplexMedService_pid,
				LpuEquipment_id := :LpuEquipment_id,
				pmUser_id := :pmUser_id
			)	
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result('array');
			if ($result[0]) {
				$result = $result[0];
				if ($result['Error_Msg']) {
					throw new Exception($result['Error_Msg'], $result['Error_Code']);
				} else {
					return $result;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}