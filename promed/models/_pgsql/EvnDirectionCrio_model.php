<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionCrio_model - модель для с работы с направлениями на перенос эмбрионов
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

class EvnDirectionCrio_model extends SwPgModel
{
    protected $dateTimeFormat104 = "'dd.mm.yyyy'";
    /**
     * Получение данных направления на перенос эмбрионов для редактирования
     *
     * @param $data
     * @return array|false
     */
	public function loadEvnDirectionCrioEditForm($data) {
	    $query = "
			select
				EDC.EvnDirectionCrio_id as \"EvnDirectionCrio_id\",
				EDC.Person_id as \"Person_id\",
				EDC.PersonEvn_id as \"PersonEvn_id\",
				EDC.Server_id as \"Server_id\",
				EDC.EvnDirectionCrio_Num as \"EvnDirectionCrio_Num\",
				to_char(EDC.EvnDirectionCrio_setDate, {$this->dateTimeFormat104}) as \"EvnDirectionCrio_setDate\",
				EDC.Diag_id as \"Diag_id\",
				EDC.Org_id as \"Org_id\",
				EDC.EvnDirectionCrio_NumVKMZ as \"EvnDirectionCrio_NumVKMZ\",
				to_char(EDC.EvnDirectionCrio_VKMZDate, {$this->dateTimeFormat104}) as \"EvnDirectionCrio_VKMZDate\",
				EDC.EvnDirectionCrio_CommentVKMZ as \"EvnDirectionCrio_CommentVKMZ\",
				to_char(EDC.EvnDirectionCrio_GiveDate, {$this->dateTimeFormat104}) as \"EvnDirectionCrio_GiveDate\",
				EDC.EvnDirectionCrio_Comment as \"EvnDirectionCrio_Comment\",
				EDC.Lpu_id as \"Lpu_id\",
				EDC.MedPersonal_id as \"MedPersonal_id\",
				EDC.MedStaffFact_id as \"MedStaffFact_id\"
			from
			    v_EvnDirectionCrio EDC
			where 
			    EDC.EvnDirectionCrio_id = :EvnDirectionCrio_id
			limit 1
		";

		return $this->queryResult($query, [
			'EvnDirectionCrio_id' => $data['EvnDirectionCrio_id']
		]);
	}

    /**
     * Сохранение направления на перенос эмбрионов
     *
     * @param array $data
     * @return array|false
     * @throws Exception
     */
	public function saveEvnDirectionCrio($data)
    {
		$params = $data;
		$query = "
			select
			    ps.Polis_id as \"Polis_id\"
			from
			    v_PersonState ps
				inner join v_Polis pls on pls.Polis_id = ps.Polis_id
			where
			    ps.Person_id = :Person_id
            and
                (pls.Polis_endDate is null or pls.Polis_endDate > dbo.tzGetDate())
			limit 1 
		";
		$Polis_id = $this->getFirstResultFromQuery($query, $params);

		if ($Polis_id === false) {
			throw new Exception('У пациента отсутствует действующий полис ОМС. Сохранение направления невозможно.');
		}

		if (empty($params['EvnDirectionCrio_Num'])) {
			$response = $this->getEvnDirectionCrioNumber($params);
			if (is_array($response) && isset($response[0]['EvnDirection_Num'])) {
				$params['EvnDirectionCrio_Num'] = (int)$response[0]['EvnDirection_Num'];
			}
		}

		$query = "
            select
                Lpu_id as \"Lpu_id\"
            from
                v_Lpu_all
            where
                Org_id = :Org_id
            limit 1
        ";
		$params['Lpu_did'] = $this->getFirstResultFromQuery($query, $params);

		if ( $params['Lpu_did'] === false ) {
			$params['Lpu_did'] = null;
		}

		$procedure = "p_EvnDirectionCrio_ins";

		if (!empty($params['EvnDirectionCrio_id'])) {
			$procedure = "p_EvnDirectionCrio_upd";
		}

		$query = "
			select
			    EvnDirectionCrio_id as \"EvnDirectionCrio_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$procedure}
			(
				EvnDirectionCrio_id := :EvnDirectionCrio_id,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				EvnDirectionCrio_Num := :EvnDirectionCrio_Num,
				EvnDirectionCrio_setDT := :EvnDirectionCrio_setDate,
				DirType_id := 28,
				EvnStatus_id := 10,
				Diag_id := :Diag_id,
				Lpu_id := :Lpu_id,
				Lpu_sid := :Lpu_id,
				Org_id := :Org_id,
				Lpu_did := :Lpu_did,
				EvnDirectionCrio_NumVKMZ := :EvnDirectionCrio_NumVKMZ,
				EvnDirectionCrio_VKMZDate := :EvnDirectionCrio_VKMZDate,
				EvnDirectionCrio_CommentVKMZ := :EvnDirectionCrio_CommentVKMZ,
				EvnDirectionCrio_GiveDate := :EvnDirectionCrio_GiveDate,
				EvnDirectionCrio_Comment := :EvnDirectionCrio_Comment,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id
			)
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnDirectionCrioNumber($data) {
		$query = "
		    select
		        ObjectID as \"EvnDirectionCrio_Num\"
		    from xp_GenpmID
		    (
		        ObjectName := 'EvnDirectionCrio',
		        Lpu_id := :Lpu_id
		    )
		";
		$result = $this->db->query($query, ['Lpu_id' => $data['Lpu_id']]);

		if ( !is_object($result) ) {
            return false;
		}

        return $result->result('array');
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnDirectionCrioFields($data)
    {

	    $query = "
			select
				D.Diag_Code as \"Diag_Code\",
				rtrim(coalesce(EDC.EvnDirectionCrio_Num, '')) as \"EvnDirectionCrio_Num\",
				to_char(EDC.EvnDirectionCrio_setDT, {$this->dateTimeFormat104}) as \"EvnDirectionCrio_setDate\",
				CASE WHEN PT.PolisType_Code = 4 then '' ELSE rtrim(coalesce (PLS.Polis_Ser, '')) END as \"Polis_Ser\",
				CASE WHEN PT.PolisType_Code = 4 then coalesce(rtrim(PS.Person_EdNum), '') ELSE rtrim(coalesce(PLS.Polis_Num, '')) END as \"Polis_Num\",
				PS.Person_Snils as \"Person_Snils\",
				DT.DocumentType_Name as \"DocumentType_Name\",
				DC.Document_Ser as \"Document_Ser\",
				DC.Document_Num as \"Document_Num\",
				to_char(DC.Document_begDate, {$this->dateTimeFormat104}) as \"Document_begDate\",
				rtrim(COALESCE(PAddr.Address_Address, UAddr.Address_Address, '')) as \"Person_Address\",
				dbo.Age2(PS.Person_Birthday, EDC.EvnDirectionCrio_setDT) as \"Person_Age\",
				to_char(PS.Person_Birthday, {$this->dateTimeFormat104}) as \"Person_Birthday\",
				RTRIM(ISNULL(PS.Person_Surname, '')) as \"Person_Surname\",
				RTRIM(ISNULL(PS.Person_Firname, '')) as \"Person_Firname\",
				RTRIM(ISNULL(PS.Person_Secname, '')) as \"Person_Secname\",
				O.Org_Name as \"Lpu_Name\"
			from v_EvnDirectionCrio EDC
			    inner join v_PersonState PS on PS.Person_id = EDC.Person_id
				inner join v_Diag D on D.Diag_id = EDC.Diag_id
				inner join v_Polis PLS on PLS .Polis_id = PS.Polis_id
				inner join v_Org O on O.Org_id = EDC.Org_id
				left join PolisType PT on PT.PolisType_id = PLS.PolisType_id
				left join v_Document DC on DC.Document_id = PS.Document_id
				left join v_DocumentType DT on DT.DocumentType_id = DC.DocumentType_id
				left join Address PAddr on PAddr.Address_id = PS.PAddress_id
				left join Address UAddr on UAddr.Address_id = PS.UAddress_id
			where
			    EDC.EvnDirectionCrio_id = :EvnDirectionCrio_id
			limit 1
		";

		return $this->getFirstRowFromQuery($query, $data);
	}
}