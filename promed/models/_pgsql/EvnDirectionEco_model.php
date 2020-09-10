<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionEco_model - модель для с работы с направлениями на ЭКО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Direction
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Stanislav Bykov (savage@swan-it.ru)
 * @version			06.06.2019
 */

class EvnDirectionEco_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных направления на ЭКО для редактирования
	 */
	public function loadEvnDirectionEcoEditForm($data) {
		return $this->queryResult("
			select
				EDE.EvnDirectionEco_id as \"EvnDirectionEco_id\",
				EDE.Person_id as \"Person_id\",
				EDE.PersonEvn_id as \"PersonEvn_id\",
				EDE.Server_id as \"Server_id\",
				EDE.EvnDirectionEco_Num as \"EvnDirectionEco_Num\",
				to_char(EDE.EvnDirectionEco_setDate, 'dd.mm.yyyy') as \"EvnDirectionEco_setDate\",
				EDE.Diag_id as \"Diag_id\",
				EDE.Org_id as \"Org_id\",
				EDE.EvnDirectionEco_NumVKMZ as \"EvnDirectionEco_NumVKMZ\",
				to_char(EDE.EvnDirectionEco_VKMZDate, 'dd.mm.yyyy') as \"EvnDirectionEco_VKMZDate\",
				EDE.EvnDirectionEco_CommentVKMZ as \"EvnDirectionEco_CommentVKMZ\",
				to_char(EDE.EvnDirectionEco_GiveDate, 'dd.mm.yyyy') as \"EvnDirectionEco_GiveDate\",
				EDE.EvnDirectionEco_Comment as \"EvnDirectionEco_Comment\",
				EDE.Lpu_id as \"Lpu_id\",
				EDE.MedPersonal_id as \"MedPersonal_id\",
				EDE.MedStaffFact_id as \"MedStaffFact_id\"
			from v_EvnDirectionEco EDE
			where EDE.EvnDirectionEco_id = :EvnDirectionEco_id
			limit 1
		", array(
			'EvnDirectionEco_id' => $data['EvnDirectionEco_id']
		));
	}

	/**
	 * Сохранение направления на ЭКО
	 */
	public function saveEvnDirectionEco($data) {
		$params = $data;

		$Polis_id = $this->getFirstResultFromQuery("
			select
				ps.Polis_id as \"Polis_id\"
			from v_PersonState ps
				inner join v_Polis pls on pls.Polis_id = ps.Polis_id
			where ps.Person_id = :Person_id
				and (pls.Polis_endDate is null or pls.Polis_endDate > dbo.tzGetDate()) 
		", $params);

		if ($Polis_id === false) {
			return array(array('Error_Msg' => 'У пациента отсутствует действующий полис ОМС. Сохранение направления невозможно.'));
		}

		if (empty($params['EvnDirectionEco_Num'])) {
			$response = $this->getEvnDirectionEcoNumber($params);
			if (is_array($response) && isset($response[0]['EvnDirection_Num'])) {
				$params['EvnDirectionEco_Num'] = (int)$response[0]['EvnDirection_Num'];
			}
		}

		$params['Lpu_did'] = $this->getFirstResultFromQuery('select Lpu_id as "Lpu_id" from v_Lpu_all where Org_id = :Org_id limit 1', $params);

		if ( $params['Lpu_did'] === false ) {
			$params['Lpu_did'] = null;
		}

		$procedure = "p_EvnDirectionEco_ins";

		if (!empty($params['EvnDirectionEco_id'])) {
			$procedure = "p_EvnDirectionEco_upd";
		}

		return $this->queryResult("
			select
				EvnDirectionEco_id as \"EvnDirectionEco_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				EvnDirectionEco_id := :EvnDirectionEco_id,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				EvnDirectionEco_Num := :EvnDirectionEco_Num,
				EvnDirectionEco_setDT := :EvnDirectionEco_setDate,
				DirType_id := 27,
				EvnStatus_id := 10,
				Diag_id := :Diag_id,
				Lpu_id := :Lpu_id,
				Lpu_sid := :Lpu_id,
				Org_id := :Org_id,
				Lpu_did := :Lpu_did,
				EvnDirectionEco_NumVKMZ := :EvnDirectionEco_NumVKMZ,
				EvnDirectionEco_VKMZDate := :EvnDirectionEco_VKMZDate,
				EvnDirectionEco_CommentVKMZ := :EvnDirectionEco_CommentVKMZ,
				EvnDirectionEco_GiveDate := :EvnDirectionEco_GiveDate,
				EvnDirectionEco_Comment := :EvnDirectionEco_Comment,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id
			)
		", $params);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnDirectionEcoNumber($data) {
		$query = "
			select
				ObjectID as \"EvnDirectionEco_Num\"
			from xp_GenpmID(
				ObjectName := 'EvnDirectionEco',
				Lpu_id := :Lpu_id
			)
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnDirectionEcoFields($data) {
		return $this->getFirstRowFromQuery("
			select
				D.Diag_Code as \"Diag_Code\",
				RTRIM(coalesce(EDE.EvnDirectionEco_Num, '')) as \"EvnDirectionEco_Num\",
				to_char(EDE.EvnDirectionEco_setDT, 'dd.mm.yyyy') as \"EvnDirectionEco_setDate\",
				CASE WHEN PT.PolisType_Code = 4
					then ''
					ELSE RTRIM(coalesce(PLS.Polis_Ser, ''))
				END as \"Polis_Ser\",
				CASE WHEN PT.PolisType_Code = 4
					then coalesce(RTRIM(PS.Person_EdNum), '')
					ELSE RTRIM(coalesce(PLS.Polis_Num, ''))
				END AS \"Polis_Num\",
				PS.Person_Snils as \"Person_Snils\",
				DT.DocumentType_Name as \"DocumentType_Name\",
				DC.Document_Ser as \"Document_Ser\",
				DC.Document_Num as \"Document_Num\",
				to_char(DC.Document_begDate, 'dd.mm.yyyy') as \"Document_begDate\",
				RTRIM(COALESCE(PAddr.Address_Address, UAddr.Address_Address, '')) as \"Person_Address\",
				dbo.Age2(PS.Person_Birthday, EDE.EvnDirectionEco_setDT) as \"Person_Age\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				RTRIM(coalesce(PS.Person_Surname, '')) as \"Person_Surname\",
				RTRIM(coalesce(PS.Person_Firname, '')) as \"Person_Firname\",
				RTRIM(coalesce(PS.Person_Secname, '')) as \"Person_Secname\",
				O.Org_Name as \"Lpu_Name\"
			from v_EvnDirectionEco EDE
				inner join v_PersonState PS on PS.Person_id = EDE.Person_id
				inner join v_Diag D on D.Diag_id = EDE.Diag_id
				inner join v_Polis PLS on PLS .Polis_id = PS.Polis_id
				inner join v_Org O on O.Org_id = EDE.Org_id
				left join PolisType PT on PT.PolisType_id = PLS.PolisType_id
				left join v_Document DC on DC.Document_id = PS.Document_id
				left join v_DocumentType DT on DT.DocumentType_id = DC.DocumentType_id
				left join Address PAddr on PAddr.Address_id = PS.PAddress_id
				left join Address UAddr on UAddr.Address_id = PS.UAddress_id
			where EDE.EvnDirectionEco_id = :EvnDirectionEco_id
			limit 1
		", $data);
	}
}