<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonService_model - модель для работы с данными
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      ?
*/


class PersonService_model extends CI_Model {
	/**
	 * PersonService_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Некая функция
	 */
	function beginTransaction() {
		$this->db->trans_begin();
	}

	/**
	 * Некая функция
	 */
	function commitTransaction() {
		$this->db->trans_commit();
	}

	/**
	 * Некая функция
	 */
	function rollbackTransaction() {
		$this->db->trans_rollback();
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPerson($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_Person_ins
				@Person_id = @Res output,
				@Server_id = :Server_id,
				@BDZ_id = :BDZ_id,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			set nocount on;

			if ( @Res is not null and not exists(select 1 from PersonState with(nolock) where Person_id = @Res) )
				insert into PersonState (Person_id, PersonState_insDT)
				values (@Res, dbo.tzGetDate())

			select @Res as Person_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;

			set nocount off;
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'BDZ_id' => $data['bdzID']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonSurname($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			if ( exists (
					select 1
					from PersonState ps with(nolock)
						inner join Person p with(nolock) on ps.Person_id = p.Person_id
							and p.Person_id = :Person_id
							and p.Server_id = :Server_id
					where (ps.PersonSurName_SurName is null or ps.PersonSurName_SurName != :Person_Surname)
						and ps.Person_id not in (
							select e1.Person_id
							from v_PersonSurName e1 with(nolock)
								inner join v_PersonSurName e2 with(nolock) on e1.Person_id = e2.Person_id
									and e1.Server_id = 0
									and e2.Server_id = 1
									and e1.PersonSurName_insDT < e2.PersonSurName_insDT
						)
				) )
				begin
					set nocount off;

					exec p_PersonSurName_ins
						@Person_id = :Person_id,
						@Server_id = :Server_id,
						@PersonSurName_SurName = :Person_Surname,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end
			else
				set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'Person_Surname' => $data['surName']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonFirname($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			if ( exists (
					select 1
					from PersonState ps with(nolock)
						inner join Person p with(nolock) on ps.Person_id = p.Person_id
							and p.Person_id = :Person_id
							and p.Server_id = :Server_id
					where (ps.PersonFirName_FirName is null or ps.PersonFirName_FirName != :Person_Firname)
						and ps.Person_id not in (
							select e1.Person_id
							from v_PersonFirName e1 with(nolock)
								inner join v_PersonFirName e2 with(nolock) on e1.Person_id = e2.Person_id
									and e1.Server_id = 0
									and e2.Server_id = 1
									and e1.PersonFirName_insDT < e2.PersonFirName_insDT
						)
				) )
				begin
					set nocount off;

					exec p_PersonFirName_ins
						@Person_id = :Person_id,
						@Server_id = :Server_id,
						@PersonFirName_FirName = :Person_Firname,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end
			else
				set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'Person_Firname' => $data['firName']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonSecname($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			if ( exists (
					select 1
					from PersonState ps with(nolock)
						inner join Person p with(nolock) on ps.Person_id = p.Person_id
							and p.Person_id = :Person_id
							and p.Server_id = :Server_id
					where ISNULL(ps.PersonSecName_SecName, '') != ISNULL(:Person_Secname, '')
						and ps.Person_id not in (
							select e1.Person_id
							from v_PersonSecName e1 with(nolock)
								inner join v_PersonSecName e2 with(nolock) on e1.Person_id = e2.Person_id
									and e1.Server_id = 0
									and e2.Server_id = 1
									and e1.PersonSecName_insDT < e2.PersonSecName_insDT
						)
				) )
				begin
					set nocount off;

					exec p_PersonSecName_ins
						@Person_id = :Person_id,
						@Server_id = :Server_id,
						@PersonSecName_SecName = :Person_Secname,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end
			else
				set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'Person_Secname' => ( strlen($data['secName']) > 0 ? $data['secName'] : NULL )
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonBirthday($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			if ( exists (
					select 1
					from PersonState ps with(nolock)
						inner join Person p with(nolock) on ps.Person_id = p.Person_id
							and p.Person_id = :Person_id
							and p.Server_id = :Server_id
					where (ps.PersonBirthDay_BirthDay is null or ps.PersonBirthDay_BirthDay != cast(:Person_Birthday as datetime))
						and ps.Person_id not in (
							select e1.Person_id
							from v_PersonBirthDay e1 with(nolock)
								inner join v_PersonBirthDay e2 with(nolock) on e1.Person_id = e2.Person_id
									and e1.Server_id = 0
									and e2.Server_id = 1
									and e1.PersonBirthDay_insDT < e2.PersonBirthDay_insDT
						)
				) )
				begin
					set nocount off;

					exec p_PersonBirthDay_ins
						@Person_id = :Person_id,
						@Server_id = :Server_id,
						@PersonBirthDay_BirthDay = :Person_Birthday,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end
			else
				set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'Person_Birthday' => $data['birthDay']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonPolisEdNum($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			if ( exists (
					select 1
					from PersonState ps with(nolock)
						inner join Person p with(nolock) on ps.Person_id = p.Person_id
							and p.Person_id = :Person_id
							and p.Server_id = :Server_id
					where isnull(ps.PersonPolisEdNum_EdNum, '') != isnull(:Person_EdNum, '')
				) )
				begin
					set nocount off;

					exec p_PersonPolisEdNum_ins
						@Person_id = :Person_id,
						@Server_id = :Server_id,
						@PersonPolisEdNum_EdNum = :Person_EdNum,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end
			else
				set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'Person_EdNum' => ( strlen($data['edNum']) > 0 ? $data['edNum'] : NULL )
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonSex($data) {
		$query = "
			declare
				@Sex_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			set @Sex_id = (select top 1 Sex_id from Sex with(nolock) where Sex_Code = :Sex_Code);

			if ( @Sex_id is not null and exists (
					select 1
					from PersonState ps with(nolock)
						inner join Person p with(nolock) on ps.Person_id = p.Person_id
							and p.Person_id = :Person_id
							and p.Server_id = :Server_id
					where ISNULL(ps.Sex_id, 0) <> ISNULL(@Sex_id, 0)
						and ps.Person_id not in (
							select e1.Person_id
							from v_PersonSex e1 with(nolock)
								inner join v_PersonSex e2 with(nolock) on e1.Person_id = e2.Person_id
									and e1.Server_id = 0
									and e2.Server_id = 1
									and e1.PersonSex_insDT < e2.PersonSex_insDT
					)
			) )
				begin
					set nocount off;

					exec p_PersonSex_ins
						@Person_id = :Person_id,
						@Server_id = :Server_id,
						@Sex_id = @Sex_id,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end
			else
				set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'Sex_Code' => $data['sex']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonUAddress($data) {
		$query = "
			declare
				@KLRgn_id bigint,
				@KLSubRgn_id bigint,
				@KLCity_id bigint,
				@KLTown_id bigint,
				@KLStreet_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			set @KLRgn_id = (select top 1 KLArea_id from KLArea with(nolock) where Kladr_Code = LEFT(:Kladr_Code, 2) + replicate('0', 11) and KLAreaLevel_id = 1);
			set @KLSubRgn_id = (select top 1 KLArea_id from KLArea with(nolock) where Kladr_Code = LEFT(:Kladr_Code, 5) + replicate('0', 8) and KLAreaLevel_id = 2);
			set @KLCity_id = (select top 1 KLArea_id from KLArea with(nolock) where Kladr_Code = LEFT(:Kladr_Code, 8) + replicate('0', 5) and KLAreaLevel_id = 3);
			set @KLTown_id = (select top 1 KLArea_id from KLArea with(nolock) where Kladr_Code = LEFT(:Kladr_Code, 11) + replicate('0', 2) and KLAreaLevel_id = 4);

			if ( len(:Kladr_Code) = 17 )
				set @KLStreet_id = (select top 1 KLStreet_id from KLStreet with(nolock) where Kladr_Code = LEFT(:Kladr_Code, 15) + replicate('0', 2));

			if ( exists (
					select 1
					from PersonState ps with(nolock)
						inner join Person p with(nolock) on ps.Person_id = p.Person_id
							and p.Person_id = :Person_id
							and p.Server_id = :Server_id
						left join [Address] a on ps.UAddress_id = a.Address_id
					where (ISNULL(a.KLRgn_id, 0) <> ISNULL(@KLRgn_id, 0) or ISNULL(a.KLSubRgn_id, 0) <> ISNULL(@KLSubRgn_id, 0)
						or ISNULL(a.KLCity_id, 0) <> ISNULL(@KLCity_id, 0) or ISNULL(a.KLTown_id, 0) <> ISNULL(@KLTown_id, 0)
						or ISNULL(a.KLStreet_id, 0) <> ISNULL(@KLStreet_id, 0) or ISNULL(a.Address_House, '') <> ISNULL(:Address_House, 0)
						or ISNULL(a.Address_Flat, '') <> ISNULL(:Address_Flat, 0))
						and ps.Person_id not in (
							select e1.Person_id
							from v_PersonUAddress e1 with(nolock)
								inner join v_PersonUAddress e2 with(nolock) on e1.Person_id = e2.Person_id
									and e1.Server_id = 0
									and e2.Server_id = 1
									and e1.PersonUAddress_insDT < e2.PersonUAddress_insDT
						)
			) )
				begin
					set nocount off;

					exec p_PersonUAddress_ins
						@Person_id = :Person_id,
						@PersonUAddress_id = null,
						@Server_id = :Server_id,
						@PersonUAddress_Index = null,
						@PersonUAddress_Count = null,
						@PersonUAddress_insDT = null,
						@Address_id = null,
						@KLAreaType_id = null,
						@KLCountry_id = 643,
						@KLRgn_id = @KLRgn_id,
						@KLSubRgn_id = @KLSubRgn_id,
						@KLCity_id = @KLCity_id,
						@KLTown_id = @KLTown_id,
						@KLStreet_id = @KLStreet_id,
						@Address_Zip = null,
						@Address_House = :Address_House,
						@Address_Corpus = null,
						@Address_Flat = :Address_Flat,
						@Address_Address = null,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end
			else
				set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'Kladr_Code' => $data['uaddressKladr'],
			'Address_House' => $data['uaddressHome'],
			'Address_Flat' => $data['uaddressFlat']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function addPersonPolis($data) {
		$query = "
			declare
				@OMSSprTerr_id bigint,
				@OrgSmo_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set nocount on;

			set @OMSSprTerr_id = (select top 1 OMSSprTerr_id from OMSSprTerr with(nolock) where ISNULL(OMSSprTerr_Code, 0) = ISNULL(:OMSSprTerr_Code, 0) or (OMSSprTerr_Code = 1 and ISNULL(:OMSSprTerr_Code, 0) between 1 and 8));
			set @OrgSmo_id = (select top 1 OrgSmo_id from OrgSmo with(nolock) where ISNULL(OrgSmo_RegNomC, 0) = ISNULL(:OrgSmo_RegNomC, 0) and ISNULL(OrgSmo_RegNomN, 0) = ISNULL(:OrgSmo_RegNomN, 0));

			if ( exists (
					select 1
					from PersonState ps with(nolock) with(nolock)
						inner join Person p with(nolock) on ps.Person_id = p.Person_id
							and p.Person_id = :Person_id
							and p.Server_id = :Server_id
						left join Polis pol with(nolock) on pol.Polis_id = ps.Polis_id
						left join OrgSmo os with(nolock) on os.OrgSmo_id = pol.OrgSmo_id
					where ISNULL(os.OrgSmo_id, 0) <> ISNULL(@OrgSmo_id, 0)
						or ISNULL(pol.OMSSprTerr_id, 0) <> ISNULL(@OMSSprTerr_id, 0)
						or ISNULL(pol.Polis_Ser, '') <> ISNULL(:Polis_Ser, '')
						or ISNULL(pol.Polis_Num, 0) <> ISNULL(:Polis_Num, 0)
						or pol.Polis_begDate <> cast(:Polis_begDate as datetime)
			) )
				begin
					set nocount off;

					exec p_PersonPolis_ins
						@Server_id = :Server_id,
						@PersonPolis_id = null,
						@Person_id = :Person_id,
						@PersonPolis_Index = null,
						@PersonPolis_Count = null,
						@PersonPolis_insDT = null,
						@Polis_id = null,
						@PolisType_id = 1,
						@OrgSMO_id = @OrgSmo_id,
						@OmsSprTerr_id = @OMSSprTerr_id,
						@Polis_Ser = :Polis_Ser,
						@Polis_Num = :Polis_Num,
						@Polis_begDate = :Polis_begDate,
						@Polis_endDate = null,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
				end
			else
				set nocount off;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'OrgSmo_RegNomC' => $data['regNomC'],
			'OrgSmo_RegNomN' => $data['regNomN'],
			'OMSSprTerr_Code' => $data['sprTerr'],
			'Polis_Ser' => $data['polisSer'],
			'Polis_Num' => $data['polisNum'],
			'Polis_begDate' => $data['polisBegDate']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $bdzId
	 * @return array
	 */
	function checkPersonExists($bdzId) {
		$checkResult = array(
			'Error_Msg' => '',
			'Person_id' => 0
		);
		$queryParams = array();

		$query = "
			SELECT top 1 Person_id
			FROM Person with(nolock)
			WHERE BDZ_id = :BDZ_id
			ORDER BY Person_insDT desc
		;";

		$queryParams['BDZ_id'] = $bdzId;

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) ) {
				if ( $response[0]['Person_id'] > 0 ) {
					$checkResult['Person_id'] = $response[0]['Person_id'];
				}
			}
			else {
				$checkResult['Error_Msg'] = 'Ошибка при проверке наличия человека в БД';
			}
		}
		else {
			$checkResult['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка наличия человека в БД)';
		}

		return $checkResult;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function attachPersonToLpu($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonCard_ins
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@Lpu_id = :Lpu_id,
				@LpuAttachType_id = 1,
				@PersonCard_begDate = :PersonCard_begDate,
				@PersonCard_IsAttachCondit = 2,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['AttachLpu_id'],
			'PersonCard_begDate' => date('Y-m-d')
		);

		$result = $this->db->query($query, $queryParams);


		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $personId
	 * @return bool|mixed
	 */
	function getAttachLpu($personId) {
		$query = "
			declare
				@AttachLpu_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec xp_PersonAttach
				@Person_id = :Person_id,
				@LpuAttachType_id = 1,
				@Lpu_id = @AttachLpu_id output,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @AttachLpu_id as AttachLpu_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $personId
		);

		$result = $this->db->query($query, $queryParams);


		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function putPersonCardState($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonCardQueue_status
				@pmUser_id = 1,
				@PersonCardQueue_Status = :PersonCardQueue_Status,
				@PersonCardQueue_id = :PersonCardQueue_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'PersonCardQueue_id' => $data['transactCode'],
			'PersonCardQueue_Status' => $data['status']
		);

		$result = $this->db->query($query, $queryParams);


		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
