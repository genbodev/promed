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

class EvnDirectionCrio_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных направления на перенос эмбрионов для редактирования
	 */
	public function loadEvnDirectionCrioEditForm($data) {
		return $this->queryResult("
			select top 1
				EDC.EvnDirectionCrio_id,
				EDC.Person_id,
				EDC.PersonEvn_id,
				EDC.Server_id,
				EDC.EvnDirectionCrio_Num,
				convert(varchar(10), EDC.EvnDirectionCrio_setDate, 104) as EvnDirectionCrio_setDate,
				EDC.Diag_id,
				EDC.Org_id,
				EDC.EvnDirectionCrio_NumVKMZ,
				convert(varchar(10), EDC.EvnDirectionCrio_VKMZDate, 104) as EvnDirectionCrio_VKMZDate,
				EDC.EvnDirectionCrio_CommentVKMZ,
				convert(varchar(10), EDC.EvnDirectionCrio_GiveDate, 104) as EvnDirectionCrio_GiveDate,
				EDC.EvnDirectionCrio_Comment,
				EDC.Lpu_id,
				EDC.MedPersonal_id,
				EDC.MedStaffFact_id
			from v_EvnDirectionCrio EDC with(nolock)
			where EDC.EvnDirectionCrio_id = :EvnDirectionCrio_id
		", array(
			'EvnDirectionCrio_id' => $data['EvnDirectionCrio_id']
		));
	}

	/**
	 * Сохранение направления на перенос эмбрионов
	 */
	public function saveEvnDirectionCrio($data) {
		$params = $data;

		$Polis_id = $this->getFirstResultFromQuery("
			select top 1 ps.Polis_id
			from v_PersonState ps with (nolock)
				inner join v_Polis pls on pls.Polis_id = ps.Polis_id
			where ps.Person_id = :Person_id
				and (pls.Polis_endDate is null or pls.Polis_endDate > dbo.tzGetDate()) 
		", $params);

		if ($Polis_id === false) {
			return array(array('Error_Msg' => 'У пациента отсутствует действующий полис ОМС. Сохранение направления невозможно.'));
		}

		if (empty($params['EvnDirectionCrio_Num'])) {
			$response = $this->getEvnDirectionCrioNumber($params);
			if (is_array($response) && isset($response[0]['EvnDirection_Num'])) {
				$params['EvnDirectionCrio_Num'] = (int)$response[0]['EvnDirection_Num'];
			}
		}

		$params['Lpu_did'] = $this->getFirstResultFromQuery('select top 1 Lpu_id from v_Lpu_all with (nolock) where Org_id = :Org_id', $params);

		if ( $params['Lpu_did'] === false ) {
			$params['Lpu_did'] = null;
		}

		$procedure = "p_EvnDirectionCrio_ins";

		if (!empty($params['EvnDirectionCrio_id'])) {
			$procedure = "p_EvnDirectionCrio_upd";
		}

		return $this->queryResult("
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDirectionCrio_id;
			exec {$procedure}
				@EvnDirectionCrio_id = @Res output,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@EvnDirectionCrio_Num = :EvnDirectionCrio_Num,
				@EvnDirectionCrio_setDT = :EvnDirectionCrio_setDate,
				@DirType_id = 28,
				@EvnStatus_id = 10,
				@Diag_id = :Diag_id,
				@Lpu_id = :Lpu_id,
				@Lpu_sid = :Lpu_id,
				@Org_id = :Org_id,
				@Lpu_did = :Lpu_did,
				@EvnDirectionCrio_NumVKMZ = :EvnDirectionCrio_NumVKMZ,
				@EvnDirectionCrio_VKMZDate = :EvnDirectionCrio_VKMZDate,
				@EvnDirectionCrio_CommentVKMZ = :EvnDirectionCrio_CommentVKMZ,
				@EvnDirectionCrio_GiveDate = :EvnDirectionCrio_GiveDate,
				@EvnDirectionCrio_Comment = :EvnDirectionCrio_Comment,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDirectionCrio_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $params);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnDirectionCrioNumber($data) {
		$query = "
			declare @EvnDirectionCrio_Num bigint;
			exec xp_GenpmID @ObjectName = 'EvnDirectionCrio', @Lpu_id = :Lpu_id, @ObjectID = @EvnDirectionCrio_Num output;
			select @EvnDirectionCrio_Num as EvnDirectionCrio_Num;
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
	public function getEvnDirectionCrioFields($data) {
		return $this->getFirstRowFromQuery("
			select top 1
				D.Diag_Code,
				RTRIM(ISNULL(EDC.EvnDirectionCrio_Num, '')) as EvnDirectionCrio_Num,
				convert(varchar(10), EDC.EvnDirectionCrio_setDT, 104) as EvnDirectionCrio_setDate,
				CASE WHEN PT.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(PLS.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PT.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(PLS.Polis_Num, '')) END AS Polis_Num,
				PS.Person_Snils,
				DT.DocumentType_Name,
				DC.Document_Ser,
				DC.Document_Num,
				convert(varchar(10), DC.Document_begDate, 104) as Document_begDate,
				RTRIM(COALESCE(PAddr.Address_Address, UAddr.Address_Address, '')) as Person_Address,
				dbo.Age2(PS.Person_Birthday, EDC.EvnDirectionCrio_setDT) as Person_Age,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(ISNULL(PS.Person_Surname, '')) as Person_Surname,
				RTRIM(ISNULL(PS.Person_Firname, '')) as Person_Firname,
				RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Secname,
				O.Org_Name as Lpu_Name
			from v_EvnDirectionCrio EDC with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EDC.Person_id
				inner join v_Diag D with (nolock) on D.Diag_id = EDC.Diag_id
				inner join v_Polis PLS with (nolock) on PLS .Polis_id = PS.Polis_id
				inner join v_Org O with (nolock) on O.Org_id = EDC.Org_id
				left join PolisType PT with (nolock) on PT.PolisType_id = PLS.PolisType_id
				left join v_Document DC with (nolock) on DC.Document_id = PS.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = DC.DocumentType_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
			where EDC.EvnDirectionCrio_id = :EvnDirectionCrio_id
		", $data);
	}
}