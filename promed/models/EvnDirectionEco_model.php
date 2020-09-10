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

class EvnDirectionEco_model extends swModel {
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
			select top 1
				EDE.EvnDirectionEco_id,
				EDE.Person_id,
				EDE.PersonEvn_id,
				EDE.Server_id,
				EDE.EvnDirectionEco_Num,
				convert(varchar(10), EDE.EvnDirectionEco_setDate, 104) as EvnDirectionEco_setDate,
				EDE.Diag_id,
				EDE.Org_id,
				EDE.EvnDirectionEco_NumVKMZ,
				convert(varchar(10), EDE.EvnDirectionEco_VKMZDate, 104) as EvnDirectionEco_VKMZDate,
				EDE.EvnDirectionEco_CommentVKMZ,
				convert(varchar(10), EDE.EvnDirectionEco_GiveDate, 104) as EvnDirectionEco_GiveDate,
				EDE.EvnDirectionEco_Comment,
				EDE.Lpu_id,
				EDE.MedPersonal_id,
				EDE.MedStaffFact_id
			from v_EvnDirectionEco EDE with(nolock)
			where EDE.EvnDirectionEco_id = :EvnDirectionEco_id
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
			select top 1 ps.Polis_id
			from v_PersonState ps with (nolock)
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

		$params['Lpu_did'] = $this->getFirstResultFromQuery('select top 1 Lpu_id from v_Lpu_all with (nolock) where Org_id = :Org_id', $params);

		if ( $params['Lpu_did'] === false ) {
			$params['Lpu_did'] = null;
		}

		$procedure = "p_EvnDirectionEco_ins";

		if (!empty($params['EvnDirectionEco_id'])) {
			$procedure = "p_EvnDirectionEco_upd";
		}

		return $this->queryResult("
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDirectionEco_id;
			exec {$procedure}
				@EvnDirectionEco_id = @Res output,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@EvnDirectionEco_Num = :EvnDirectionEco_Num,
				@EvnDirectionEco_setDT = :EvnDirectionEco_setDate,
				@DirType_id = 27,
				@EvnStatus_id = 10,
				@Diag_id = :Diag_id,
				@Lpu_id = :Lpu_id,
				@Lpu_sid = :Lpu_id,
				@Org_id = :Org_id,
				@Lpu_did = :Lpu_did,
				@EvnDirectionEco_NumVKMZ = :EvnDirectionEco_NumVKMZ,
				@EvnDirectionEco_VKMZDate = :EvnDirectionEco_VKMZDate,
				@EvnDirectionEco_CommentVKMZ = :EvnDirectionEco_CommentVKMZ,
				@EvnDirectionEco_GiveDate = :EvnDirectionEco_GiveDate,
				@EvnDirectionEco_Comment = :EvnDirectionEco_Comment,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDirectionEco_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $params);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnDirectionEcoNumber($data) {
		$query = "
			declare @EvnDirectionEco_Num bigint;
			exec xp_GenpmID @ObjectName = 'EvnDirectionEco', @Lpu_id = :Lpu_id, @ObjectID = @EvnDirectionEco_Num output;
			select @EvnDirectionEco_Num as EvnDirectionEco_Num;
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
			select top 1
				D.Diag_Code,
				RTRIM(ISNULL(EDE.EvnDirectionEco_Num, '')) as EvnDirectionEco_Num,
				convert(varchar(10), EDE.EvnDirectionEco_setDT, 104) as EvnDirectionEco_setDate,
				CASE WHEN PT.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(PLS.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PT.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(PLS.Polis_Num, '')) END AS Polis_Num,
				PS.Person_Snils,
				DT.DocumentType_Name,
				DC.Document_Ser,
				DC.Document_Num,
				convert(varchar(10), DC.Document_begDate, 104) as Document_begDate,
				RTRIM(COALESCE(PAddr.Address_Address, UAddr.Address_Address, '')) as Person_Address,
				dbo.Age2(PS.Person_Birthday, EDE.EvnDirectionEco_setDT) as Person_Age,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(ISNULL(PS.Person_Surname, '')) as Person_Surname,
				RTRIM(ISNULL(PS.Person_Firname, '')) as Person_Firname,
				RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Secname,
				O.Org_Name as Lpu_Name
			from v_EvnDirectionEco EDE with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EDE.Person_id
				inner join v_Diag D with (nolock) on D.Diag_id = EDE.Diag_id
				inner join v_Polis PLS with (nolock) on PLS .Polis_id = PS.Polis_id
				inner join v_Org O with (nolock) on O.Org_id = EDE.Org_id
				left join PolisType PT with (nolock) on PT.PolisType_id = PLS.PolisType_id
				left join v_Document DC with (nolock) on DC.Document_id = PS.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = DC.DocumentType_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
			where EDE.EvnDirectionEco_id = :EvnDirectionEco_id
		", $data);
	}
}