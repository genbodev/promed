<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnUslugaAbstract_model.php');

/**
 * EvnUslugaTelemed_model - Модель "Оказание телемедицинской услуги"
 *
 * Это "фиктивный" объект.
 * Услуга должна иметь связь с электронным (не системным) направлением.
 * В реестры услуга уходить не должна.
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      11.2014
 *
 * @property int $UslugaTelemedResultType_id
 * @property int $Diag_id
 *
 * @property EvnMediaFiles_model $EvnMediaFiles_model
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 */
class EvnUslugaTelemed_model extends EvnUslugaAbstract_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnUslugaTelemed_id';
		$arr['pid']['alias'] = 'EvnUslugaTelemed_pid';
		$arr['pid']['save'] = 'trim';
		$arr['rid']['alias'] = 'EvnUslugaTelemed_rid';
		$arr['setdate']['alias'] = 'EvnUslugaTelemed_setDate';
		$arr['settime']['alias'] = 'EvnUslugaTelemed_setTime';
		$arr['disdt']['alias'] = 'EvnUslugaTelemed_disDT';
		$arr['diddt']['alias'] = 'EvnUslugaTelemed_didDT';
		$arr['iscito']['alias'] = 'EvnUslugaTelemed_isCito';
		$arr['ismodern']['alias'] = 'EvnUslugaTelemed_IsModern';
		$arr['result']['alias'] = 'EvnUslugaTelemed_Result';
		$arr['kolvo']['alias'] = 'EvnUslugaTelemed_Kolvo';
		$arr['price']['alias'] = 'EvnUslugaTelemed_Price';
		$arr['summa']['alias'] = 'EvnUslugaTelemed_Summa';
		$arr['evndirection_id']['save'] = 'trim|required';
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Диагноз',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['diagsetphase_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetPhase_id',
			'label' => 'Состояние пациента',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['deseasetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DeseaseType_id',
			'label' => 'Характер',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['mes_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Mes_id',
			'label' => 'МЭС',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['uslugatelemedresulttype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaTelemedResultType_id',
			'label' => 'Результат',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['medstafffact_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_id',
			'label' => 'Место работы врача, выполнившего услугу',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['uslugacomplex_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplex_id',
			'label' => 'Услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['indexrep'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUslugaTelemed_IndexRep',
			'label' => 'Признак повторной подачи',
			'save' => 'trim',
			'type' => 'int',
		);
		$arr['indexrepinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUslugaTelemed_IndexRepInReg',
		);
		$arr['ispaid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnUslugaTelemed_IsPaid',
		);
		return $arr;
	}
	
	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()	{
		$all = parent::_getSaveInputRules();
		$all['MedPersonalNotPromed_Description'] = array(
			'field' => 'MedPersonalNotPromed_Description',
			'rules' => 'trim',
			'type' => 'string',
		);
		$all['MedSpec_id'] = array(
			'field' => 'MedSpec_id',
			'type' => 'id'
		);
		return $all;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 160;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnUslugaTelemed';
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_DELETE,
		));
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data) {
		parent::setParams($data);
		$this->_params['MedPersonalNotPromed_Description'] = $data['MedPersonalNotPromed_Description'] ?? null;
		$this->_params['MedSpec_id'] = $data['MedSpec_id'] ?? null;
	}

	/**
	 * Получение данных о телемед. услуге
	 */
	function getEvnUslugaTelemedViewData($data) {
		$accessType = 'EUT.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params =  array(
			'EvnUslugaTelemed_id' => $data['EvnUslugaTelemed_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= ' and (EUT.MedPersonal_id is null or EUT.MedPersonal_id = MSF.MedPersonal_id) and EUT.LpuSection_uid = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}
		
		$selectPersonData = "PS.Person_SurName+' '+PS.Person_FirName+' '+isnull(PS.Person_SecName,'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null 
					then ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'') 
					else peh.PersonEncrypHIV_Encryp 
				end as Person_Fio,
				null as Person_Birthday,";
		}

		$query = "
			SELECT top 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EUT.EvnUslugaTelemed_id,
				EUT.EvnUslugaTelemed_pid,
				ED.EvnDirection_id,
				EUT.Person_id,
				EUT.PersonEvn_id,
				EUT.Server_id,
				EUT.Usluga_id,
				EUT.UslugaComplex_id,
				EUT.EvnUslugaTelemed_isCito,
				EUT.EvnUslugaTelemed_Kolvo,
				EUT.PayType_id,
				EUT.Lpu_id,
				EUT.LpuSection_uid,
				EUT.MedPersonal_id as MedStaffFact_uid,
				DLpuSection.Lpu_id as Lpu_did,
				ED.MedPersonal_id as MedStaffFact_did,
				EUT.MedPersonal_sid as MedStaffFact_sid,
				EUT.MedStaffFact_id,
				{$selectPersonData}
				D.Diag_id,
				isnull(D.Diag_Code,'') as Diag_Code,
				isnull(D.Diag_Name,'') as Diag_Name,
				EUT.DiagSetPhase_id,
				isnull(UC.UslugaComplex_Code,'') as UslugaComplex_Code,
				isnull(UC.UslugaComplex_Name,'') as UslugaComplex_Name,
				d.Diag_Name as Usluga_Name,
				EUT.EvnUslugaTelemed_id as Usluga_Number,
				ULpu.Lpu_Nick,
				ULpu.Lpu_Name,
				ULpu.UAddress_Address as Lpu_Address,
				ULpuSection.LpuSection_Code,
				ULpuSection.LpuSection_Name,
				convert(varchar(10), EUT.EvnUslugaTelemed_setDT, 104) as EvnUslugaTelemed_setDate,
				ISNULL(EUT.EvnUslugaTelemed_setTime, '') as EvnUslugaTelemed_setTime,
				MP.Person_SurName + ' ' + LEFT(MP.Person_FirName, 1)  + '. ' + ISNULL(LEFT(MP.Person_SecName, 1) + '.', '') as MedPersonal_Fin,
				--case when DLpuSection.LpuSection_Code is not null then DLpuSection.LpuSection_Code else ISNULL(DOrg.Org_Code,'') end as DirectSubject_Code,
				--case when DLpuSection.LpuSection_Name is not null then DLpuSection.LpuSection_Name else ISNULL(DOrg.Org_Nick,'') end as DirectSubject_Name,
				DLpuSection.LpuSection_Code as DirectSubject_Code,
                DLpuSection.LpuSection_Name as DirectSubject_Name,
                DOrg.Org_Code as OrgDirectSubject_Code,
                DOrg.Org_Nick as OrgDirectSubject_Name,
				ED.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
				DMedPersonal.Person_SurName + ' ' + LEFT(DMedPersonal.Person_FirName, 1)  + '. ' + ISNULL(LEFT(DMedPersonal.Person_SecName, 1) + '.', '') as MedPersonalDirect_Fin,
				case when EvnLabRequest.EvnLabRequest_id is null then 0 else 1 end as isLab,
				UTRT.UslugaTelemedResultType_Name,
				EX.EvnXml_IsSigned,
				EX.EvnXml_id,
				LSP.LpuSectionProfile_Name
			FROM v_EvnUslugaTelemed EUT with (nolock)
				left join v_EvnXml EX (nolock) on EX.Evn_id = EUT.EvnUslugaTelemed_id
				left join v_UslugaTelemedResultType UTRT (nolock) on UTRT.UslugaTelemedResultType_id = EUT.UslugaTelemedResultType_id
				left join v_Person_all PS with (nolock) on EUT.Person_id = PS.Person_id AND EUT.PersonEvn_id = PS.PersonEvn_id AND EUT.Server_id = PS.Server_id
				left join v_EvnDirection_all ED with (nolock) on EUT.EvnDirection_id = ED.EvnDirection_id and ED.DirFailType_id is null
				left join v_LpuSectionProfile LSP (nolock) on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_EvnLabRequest EvnLabRequest with (nolock) on EvnLabRequest.EvnDirection_id = ED.EvnDirection_id
				outer apply (select top 1 * from v_EvnLabSample with (nolock) where EvnLabRequest_id = EvnLabRequest.EvnLabRequest_id) as EvnLabSample
				left join v_MedService MS with (nolock) on EvnLabRequest.MedService_id = MS.MedService_id
				left join v_Lpu ULpu with (nolock) on isnull(MS.Lpu_id,EUT.Lpu_id) = ULpu.Lpu_id
				left join v_LpuSection ULpuSection with (nolock) on isnull(MS.LpuSection_id,EUT.LpuSection_uid) = ULpuSection.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = isNull(EvnLabSample.MedPersonal_aid,EUT.MedPersonal_id) AND MP.Lpu_id = isnull(MS.Lpu_id,EUT.Lpu_id)
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUT.UslugaComplex_id
				left join v_Diag D with (nolock) on EUT.Diag_id = D.Diag_id
				left join v_LpuSection DLpuSection with (nolock) on ED.LpuSection_id = DLpuSection.LpuSection_id
				left join v_Lpu DLpu (nolock) on DLpu.Lpu_id = ED.Lpu_sid
				left join v_Org DOrg with (nolock) on DLpu.Org_id = DOrg.Org_id
				left join v_MedPersonal DMedPersonal with (nolock) on ED.MedPersonal_id = DMedPersonal.MedPersonal_id AND isnull(DLpuSection.Lpu_id,ED.Lpu_sid) = DMedPersonal.Lpu_id
				{$join_msf}
				{$joinPersonEncrypHIV}
			WHERE
				EUT.EvnUslugaTelemed_id = :EvnUslugaTelemed_id
		";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if ($this->regionNick != 'kz' && empty($this->UslugaComplex_id)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
		) {
			throw new Exception('Не указана услуга');
		}
		if (empty($this->EvnDirection_id)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
		) {
			throw new Exception('Не указано направление');
		}
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			$ed = $this->getFirstRowFromQuery('
				select
					ed.EvnDirection_id,
					ed.EvnDirection_rid,
					ed.personevn_id,
					ed.person_id,
					ed.server_id,
					ed.diag_id,
					ed.morbus_id,
					isnull(ed.EvnDirection_IsCito,1) as EvnDirection_IsCito
				from v_EvnDirection ed with (nolock)
				where ed.EvnDirection_id = :EvnDirection_id
					and isnull(ed.EvnDirection_IsAuto,1) = 1
			', array('EvnDirection_id'=>$this->EvnDirection_id));
			if (false == is_array($ed)) {
				throw new Exception('Не указано электронное направление');
			}
			// привязка к ТАП/КВС
			$this->setAttribute('pid', $ed['EvnDirection_rid']);
			// остальное тождественно данным из направления
			$this->setAttribute('iscito', $ed['EvnDirection_IsCito']);
			/*$this->setAttribute('personevn_id', $ed['personevn_id']);
			$this->setAttribute('person_id', $ed['person_id']);
			$this->setAttribute('server_id', $ed['server_id']);*/
			$this->setAttribute('morbus_id', $ed['morbus_id']);
			if (empty($this->Diag_id)) {
				$this->setAttribute('diag_id', $ed['diag_id']);
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		$this->setAttribute('isvizitcode', 1);

		/*if ( $this->regionNick == 'ekb' ) {
			$query = "select top 1 UslugaComplex_id from v_UslugaComplex with (nolock) where UslugaComplex_Code = 'A23.30.099.005'";
			$uslugacomplex_id = $this->getFirstResultFromQuery($query);
			$this->setAttribute('uslugacomplex_id', $uslugacomplex_id);
		}*/

		$this->Lpu_id = $this->getFirstResultFromQuery("
			select Lpu_sid from v_EvnDirection_all where EvnDirection_id = :EvnDirection_id
		", array('EvnDirection_id' => $this->EvnDirection_id));
		if (empty($this->Lpu_id)) {
			throw new Exception('Ошибка при получении идентификатора направившего МО');
		}

		if ( $this->isNewRecord && !empty($this->EvnDirection_id) ) {
			// переводим в статус “Обслужено”
			$this->load->model('EvnDirectionAll_model');
			$this->EvnDirectionAll_model->setStatus(array(
				'Evn_id' => $this->EvnDirection_id,
				'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
				'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
				'pmUser_id' => $this->promedUserId,
			));
		}
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result) {
		if ($this->regionNick == 'msk') {
			$MedPersonalNotPromed_id = $this->getFirstResultFromQuery("
				select top 1 MedPersonalNotPromed_id 
				from r50.MedPersonalNotPromed (nolock)
				where EvnUslugaTelemed_id = ?
			", [$this->id], true);
			
			$proc = empty($MedPersonalNotPromed_id) ? 'r50.p_MedPersonalNotPromed_ins' : 'r50.p_MedPersonalNotPromed_upd';

			$this->execCommonSP($proc, [
				'MedPersonalNotPromed_id' => ['value' => $MedPersonalNotPromed_id, 'out' => true, 'type' => 'bigint'],
				'MedPersonalNotPromed_Description' => $this->_params['MedPersonalNotPromed_Description'],
				'MedSpec_id' => $this->_params['MedSpec_id'],
				'EvnUslugaTelemed_id' => $this->id,
				'pmUser_id' => $this->promedUserId
			], 'array_assoc');
		}
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		// Возвращаем направлению предыдущий статус
		if (!empty($this->EvnDirection_id)) {
			$this->load->model('EvnDirectionAll_model');
			$this->EvnDirectionAll_model->rollbackStatus(array(
				'Evn_id' => $this->EvnDirection_id,
				'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
				'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
				'pmUser_id' => $this->promedUserId,
			));
		}
	}

	/**
	 * Загрузка формы редактирования случая оказания телемедицинской услуги
	 */
	function loadEditForm($data)
	{
		$region_nick = getRegionNick();
		$this->setScenario(self::SCENARIO_LOAD_EDIT_FORM);
		$this->setParams($data);
		$this->setAttributes(array(
			'EvnUslugaTelemed_id' => $data['EvnUslugaTelemed_id']
		));
		$this->_validate();

		$recept_kardio_exists = false;
		if ($region_nick == 'perm') {
			$query = "
				declare
					@ReceptForm_id bigint;
					
				set @ReceptForm_id = (select top 1 ReceptForm_id from v_ReceptForm with (nolock) where ReceptForm_Code = '148 (к)');
				
				select top 1
					er.EvnRecept_id
				from
					v_EvnRecept er with (nolock)
				where
					er.EvnRecept_pid = :EvnUslugaTelemed_id and
					er.ReceptForm_id = @ReceptForm_id;
			";
			$recept_data = $this->getFirstRowFromQuery($query, array('EvnUslugaTelemed_id' => $data['EvnUslugaTelemed_id']));
			if (!empty($recept_data['EvnRecept_id'])) {
				$recept_kardio_exists = true;
			}
		}
		
		$mpnp = false;
		if ($region_nick == 'msk') {
			$query = "
				select 
					MedPersonalNotPromed_Description,
					MedSpec_id
				from
					r50.MedPersonalNotPromed (nolock)
				where
					EvnUslugaTelemed_id = :EvnUslugaTelemed_id
			";
			$mpnp = $this->getFirstRowFromQuery($query, [
				'EvnUslugaTelemed_id' => $data['EvnUslugaTelemed_id']
			]);
			if ($mpnp !== false) {
				$MedPersonalNotPromed_Description = $mpnp['MedPersonalNotPromed_Description'];
				$MedSpec_id = $mpnp['MedSpec_id'];
			}
		}

		return array(array(
			'accessType' => 'edit',
			'EvnUslugaTelemed_id' => $this->id,
			'EvnDirection_id' => $this->EvnDirection_id,
			'PersonEvn_id' => $this->PersonEvn_id,
			'Person_id' => $this->Person_id,
			'Server_id' => $this->Server_id,
			'EvnUslugaTelemed_setDate' => $this->setDate,
			'EvnUslugaTelemed_setTime' => $this->setTime,
			'UslugaPlace_id' => $this->UslugaPlace_id,
			'Lpu_uid' => $this->Lpu_uid,
			'Org_uid' => $this->Org_uid,
			'LpuSection_uid' => $this->LpuSection_uid,
			'MedPersonal_id' => $this->MedPersonal_id,
			'MedStaffFact_id' => $this->MedStaffFact_id,
			'UslugaComplex_id' => $this->UslugaComplex_id,
			'PayType_id' => $this->PayType_id,
			'Diag_id' => $this->Diag_id,
			'DiagSetPhase_id' => $this->DiagSetPhase_id,
			'DeseaseType_id' => $this->DeseaseType_id,
			'Mes_id' => $this->Mes_id,
			'UslugaTelemedResultType_id' => $this->UslugaTelemedResultType_id,
			'EvnUslugaTelemed_IsPaid' => $this->ispaid,
			'EvnUslugaTelemed_IndexRep' => (!empty($this->indexrep) ? $this->indexrep : 0),
			'EvnUslugaTelemed_IndexRepInReg' => (!empty($this->indexrepinreg) ? $this->indexrepinreg : 1),
			'MedPersonalNotPromed_Description' => $MedPersonalNotPromed_Description ?? '',
			'MedSpec_id' => $MedSpec_id ?? null,
			'isNotForSystem' => $mpnp ? 2 : null,
			'EvnReceptKardio_Exists' => $recept_kardio_exists ? 1 : 0
		));
	}

	/**
	 * В настоящее время пациент находится на ...
	 */
	function getThePatientIsBeingTreatedHtml($id)
	{
		$query = "
			select top 1
			v_Evn.EvnClass_SysNick,
			v_Evn.Evn_setDT,
			v_LpuSection.LpuSection_Name
			from v_EvnUslugaTelemed EUT with (nolock)
			left join v_EvnDirection with (nolock) on v_EvnDirection.EvnDirection_id = EUT.EvnDirection_id
			left join v_Evn with (nolock) on v_Evn.Evn_id = v_EvnDirection.EvnDirection_pid
			left join EvnSection with (nolock) on EvnSection.EvnSection_id = v_Evn.Evn_id
			left join v_LpuSection with (nolock) on v_LpuSection.LpuSection_id = EvnSection.LpuSection_id
			where EUT.EvnUslugaTelemed_id = :id
		";
		$res = $this->db->query($query, array(
			'id' => $id,
		));
		if ( false == is_object($res) ) {
			return '';
		}
		$response = $res->result('array');
		if ( empty($response) ) {
			return '';
		}
		if ( 'EvnSection' == $response[0]['EvnClass_SysNick'] && isset($response[0]['LpuSection_Name']) ) {
			return 'В настоящее время пациент находится на стационарном лечении (отделение '. $response[0]['LpuSection_Name'] .').';
		}
		if ( $response[0]['Evn_setDT'] instanceof DateTime ) {
			return 'В настоящее время пациент на амбулаторном лечении  с ' . $response[0]['Evn_setDT']->format('d.m.Y') .'.';
		}
		return 'В настоящее время пациент находится на лечении ...';
	}

	/**
	 * Удаление случаев оказания телемедицинской услуги по направлению
	 */
	function unExec($data)
	{
		$query = "
			select
			EUT.EvnUslugaTelemed_id,
			DOC.EvnXml_id,
			ER.EvnRecept_id,
			EMD.EvnMediaData_FilePath,
			EMD.EvnMediaData_id
			from v_EvnUslugaTelemed EUT with (nolock)
			left join v_EvnXml DOC with (nolock) on DOC.Evn_id = EUT.EvnUslugaTelemed_id
			left join v_EvnRecept ER with (nolock) on ER.EvnRecept_pid = EUT.EvnUslugaTelemed_id
			left join v_EvnMediaData EMD with (nolock) on EMD.Evn_id = EUT.EvnUslugaTelemed_id
			where EUT.EvnDirection_id = :EvnDirection_id
		";
		try {
			$this->isAllowTransaction = false;
			$this->setParams($data);
			$res = $this->db->query($query, array(
				'EvnDirection_id' => $data['id'],
			));
			if ( false == is_object($res) ) {
				throw new Exception('Не удалось получить список случаев оказания телемедицинской услуги по направлению');
			}
			$response = $res->result('array');
			$evnUslugaIdList = array();
			$evnXmlIdList = array();
			$evnReceptIdList = array();
			$evnMediaDataIdList = array();
			$evnMediaDataFilePathList = array();
			foreach ($response as $row) {
				if (!in_array($row['EvnUslugaTelemed_id'], $evnUslugaIdList)) {
					$evnUslugaIdList[] = $row['EvnUslugaTelemed_id'];
				}
				if (isset($row['EvnXml_id']) && !in_array($row['EvnXml_id'], $evnXmlIdList)) {
					$evnXmlIdList[] = $row['EvnXml_id'];
				}
				if (isset($row['EvnRecept_id']) && !in_array($row['EvnRecept_id'], $evnReceptIdList)) {
					$evnReceptIdList[] = $row['EvnRecept_id'];
				}
				if (isset($row['EvnMediaData_id']) && !in_array($row['EvnMediaData_id'], $evnMediaDataIdList)) {
					$evnMediaDataIdList[] = $row['EvnMediaData_id'];
					$evnMediaDataFilePathList[] = $row['EvnMediaData_FilePath'];
				}
			}
			$this->isAllowTransaction = true;
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}
			// удаляем записи о добавленных файлах
			$this->load->model('EvnMediaFiles_model');
			foreach ($evnMediaDataIdList as $id) {
				$response = $this->EvnMediaFiles_model->deleteEvnMediaData(array(
					'EvnMediaData_id' => $id,
					'session' => $this->sessionParams,
				));
				if (empty($response)) {
					throw new Exception('Ошибка при удаления файла', 500);
				}
				if (!empty($response[0]['Error_Msg'])) {
					throw new Exception($response[0]['Error_Msg'], $response[0]['Error_Code']);
				}
			}
			// удаляем добавленные документы
			$this->load->library('swXmlTemplate');
			foreach ($evnXmlIdList as $id) {
				$response = swXmlTemplate::getEvnXmlModelInstance()->doDelete(array(
					'EvnXml_id' => $id,
					'session' => $this->sessionParams,
				), false);
				if (!empty($response['Error_Msg'])) {
					throw new Exception($response['Error_Msg'], $response['Error_Code']);
				}
			}
			// удаляем услуги
			$className = get_class($this);
			foreach ($evnUslugaIdList as $id) {
				/**
				 * @var EvnUslugaTelemed_model $instance
				 */
				$instance = new $className();
				$instance->doDelete(array(
					$this->primaryKey(true) => $id,
					'session' => $this->sessionParams,
				), false);
			}
			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}

			$cnt_files = count($evnMediaDataFilePathList);
			if ( $cnt_files > 0 ) {
				// удаляем файлы
				$upload_path = $this->EvnMediaFiles_model->getUploadPath();
				if (@is_dir($upload_path) && is_writable($upload_path)) {
					$cnt_err = 0;
					foreach ($evnMediaDataFilePathList as $file_path) {
						$file_name = $upload_path . $file_path;
						if (file_exists($file_name) && !unlink($file_name)) {
							$cnt_err++;
						}
					}
					if ($cnt_err) {
						$this->_saveResponse['Alert_Msg'] = "Всего файлов: {$cnt_files}. Не удалось удалить: {$cnt_err}";
					}
				} else {
					$this->_saveResponse['Alert_Msg'] = 'Файлы не удалены. Директория, в которую были загружены файлы, не существует или не имеет прав на запись';
				}
			}

			$this->_saveResponse[$this->primaryKey(true).'_list'] = $evnUslugaIdList;
			$this->_saveResponse['EvnXml_id_list'] = $evnXmlIdList;
			$this->_saveResponse['EvnMediaData_id_list'] = $evnMediaDataIdList;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		$this->_onDelete();
		return $this->_saveResponse;
	}

	/**
	 * Загрузка грида в АРМ Центра удалённой конслуьтации
	 */
	function loadWorkPlaceGrid($data)
	{
		$region = getRegionNick();
		$queryParams = array(
			'MedService_id' => $data['MedService_id'],
			'begDate' => $data['begDate'],
			'endDate' => $data['endDate'],
		);
		$filters = array(
			'MedService_id' => 'ED.MedService_id = :MedService_id',
			'begDate' => '(EUT.EvnUslugaTelemed_setDT is null OR cast(EUT.EvnUslugaTelemed_setDT as date) >= :begDate)',
			'endDate' => '(EUT.EvnUslugaTelemed_setDT is null OR cast(EUT.EvnUslugaTelemed_setDT as date) <= :endDate)',
		);

		$limit = 100;
		if($region != 'kz') {
			$limit = 500;
			$filters[] = "
				(
					(ED.EvnDirection_failDT is not null and ED.EvnDirection_setDT between :begDate and :endDate)
					or
					(EUT.EvnUslugaTelemed_id is not null and ED.EvnDirection_setDT between :begDate and :endDate)
					or
					(ED.EvnDirection_failDT is null and EUT.EvnUslugaTelemed_id is null)
				)
			";
		} else {
			$filters['failDT'] = "ED.EvnDirection_failDT is null";
		}

		if(!empty($data['LpuCombo_id'])) {
			$filters['LpuCombo_id'] = 'v_Lpu.Lpu_id = :LpuCombo_id';
			$queryParams['LpuCombo_id'] = $data['LpuCombo_id'];
		}
		// Добавил фильтр по диагнозу с и по https://redmine.swan.perm.ru/issues/97651
		if ( !empty($data['Diag_Code_From']) ) {
			$filters['Diag_Code_From'] = 'd.Diag_Code >= :Diag_Code_From';
			$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
		}
		if ( !empty($data['Diag_Code_To']) ) {
			$filters['Diag_Code_To'] = 'd.Diag_Code <= :Diag_Code_To';
			$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
		}
		
		if ( !empty($data['EvnDirection_Num']) ) {
			$filters['EvnDirection_Num'] = 'ED.EvnDirection_Num = :EvnDirection_Num';
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}
		if ( !empty($data['Person_BirthDay']) ) {
			$filters['Person_BirthDay'] = 'cast(PS.Person_BirthDay as date) = :Person_BirthDay';
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if ( !empty($data['Person_SurName']) ) {
			$filters['Person_SurName'] = 'PS.Person_SurName like :Person_SurName';
			$queryParams['Person_SurName'] = $data['Person_SurName'] . '%';
		}
		if ( !empty($data['Person_FirName']) ) {
			$filters['Person_FirName'] = 'PS.Person_FirName like :Person_FirName';
			$queryParams['Person_FirName'] = $data['Person_FirName'] . '%';
		}
		if ( !empty($data['Person_SecName']) ) {
			$filters['Person_SecName'] = 'PS.Person_SecName like :Person_SecName';
			$queryParams['Person_SecName'] = $data['Person_SecName'] . '%';
		}
		if ( !empty($data['RemoteConsultCause_id']) ) {
			$filters['RemoteConsultCause_id'] = 'ED.RemoteConsultCause_id = :RemoteConsultCause_id';
			$queryParams['RemoteConsultCause_id'] = $data['RemoteConsultCause_id'];
		}

		$query_tpl = "
			SELECT top {limit}
				ED.EvnClass_id,
				d.Diag_FullName,
				ED.EvnDirection_id,
				ED.EvnDirection_pid,
				ED.EvnDirection_rid,
				r.EvnClass_SysNick as RootEvnClass_SysNick,
				EUT.EvnUslugaTelemed_id,
				--case when EUT.EvnUslugaTelemed_id is not null then 1 else 0 end as hasEvnUslugaTelemed,
				case 
					when ED.EvnDirection_failDT is not null
						then 3
					when EUT.EvnUslugaTelemed_id is not null
						then 2
					when EUT.EvnUslugaTelemed_id is null
						then 1
				end as evndirection_group,
				CF.ConsultingForm_Name,
				ED.Diag_id,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id,
				ED.EvnDirection_Num,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				v_Lpu.Lpu_Nick,
				v_Lpu.Lpu_id,
				pp.RiskType_id,
				mp.Person_Fin,
				(PS.Person_SurName + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName,'')) as Person_FIO,
				case when 2 = isnull(ED.EvnDirection_IsCito,1) then 'true' else 'false' end as EvnDirection_IsCito,
				RCC.RemoteConsultCause_Name,
				LSP.LpuSectionProfile_Name,
				UTRT.UslugaTelemedResultType_Name,
				consForm.ConsultationForm_Code,
				consForm.ConsultationForm_Name,
				isnull(isDel.isDel,'0') as isDel
				{add_fields},
				case 
					when consForm.ConsultationForm_Code is null
						then ''
					when consForm.ConsultationForm_Code = 1
						then convert(varchar(10), DATEADD(day,14,ED.EvnDirection_insDT), 104) + ' ' + convert(varchar(5), ED.EvnDirection_insDT, 108)
					when consForm.ConsultationForm_Code = 2
						then convert(varchar(10), DATEADD(day,1,ED.EvnDirection_insDT), 104) + ' ' + convert(varchar(5), ED.EvnDirection_insDT, 108)
					when consForm.ConsultationForm_Code = 3
						then convert(varchar(10), DATEADD(hour,2,ED.EvnDirection_insDT), 104) + ' ' + convert(varchar(5), dateadd(hour,2,ED.EvnDirection_insDT), 108)
				end as deadLine,
				case 
					when consForm.ConsultationForm_Code is null
						then 99999999
					when consForm.ConsultationForm_Code = 1
						then abs(datediff(hour, DATEADD(day,14,ED.EvnDirection_insDT), CURRENT_TIMESTAMP))
					when consForm.ConsultationForm_Code = 2
						then abs(datediff(hour, DATEADD(day,1,ED.EvnDirection_insDT), CURRENT_TIMESTAMP))
					when consForm.ConsultationForm_Code = 3
						then abs(datediff(hour, DATEADD(hour,2,ED.EvnDirection_insDT), CURRENT_TIMESTAMP))
				end as deadDiff,
				DT.DirType_Code,
				ED.EvnQueue_id,
				ED.EvnQueue_id,
				ED.TimeTableStac_id,
				ED.TimeTableGraf_id,
				ED.TimeTableMedService_id,
				ESC.EvnStatusCause_Name,
				case
					when ESC.EvnStatusCause_Name is not null
						then ESC.EvnStatusCause_Name
					else 
						UTRT.UslugaTelemedResultType_Name
				end as evndirection_result,
				ex.EvnXml_id,
				ex.EvnXml_IsSigned,
				convert(varchar(10), ex.EvnXml_signDT, 104) as EvnXml_signDT,
				pu.pmUser_Name as pmUser_signName
			FROM
				v_EvnDirection ED with (nolock)
				left join v_PersonPregnancy pp (nolock) on pp.Person_id = ed.Person_id
				LEFT JOIN ConsultingForm CF with (nolock) on CF.ConsultingForm_id = ED.ConsultingForm_id
				LEFT JOIN v_Evn r with (nolock) on r.Evn_id = ED.EvnDirection_rid
				LEFT JOIN v_PersonState PS with (nolock) on PS.Person_id = ED.Person_id
				outer apply(
					select top 1 1 as isDel
					from v_lpu lp with(nolock) 
					inner join v_pmUserCache pm with(nolock) on pm.lpu_id = lp.lpu_id
					where pm.pmuser_id = ed.pmuser_insid
				) isDel
				outer apply (
					select top 1
						EUT.EvnUslugaTelemed_id,
						EUT.EvnUslugaTelemed_setDT,
						EUT.UslugaTelemedResultType_id
					from v_EvnUslugaTelemed EUT with (nolock)
					where EUT.EvnDirection_id = ED.EvnDirection_id
				) EUT
				left join v_EvnXml ex (nolock) on ex.Evn_id = EUT.EvnUslugaTelemed_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = ex.pmUser_signID
				left join v_UslugaTelemedResultType UTRT (nolock) on UTRT.UslugaTelemedResultType_id = EUT.UslugaTelemedResultType_id
				LEFT JOIN v_LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				LEFT JOIN v_RemoteConsultCause RCC with (nolock) on RCC.RemoteConsultCause_id = ED.RemoteConsultCause_id
				LEFT JOIN v_Lpu with (nolock) on v_Lpu.Lpu_id = ED.Lpu_sid
				LEFT JOIN v_MedPersonal mp with (nolock) on mp.MedPersonal_id = ED.MedPersonal_id and mp.Lpu_id = ED.Lpu_sid
				LEFT JOIN v_Diag d with (nolock) on ED.Diag_id = d.Diag_id
				LEFT JOIN v_ConsultationForm consForm on consForm.ConsultationForm_id = ED.ConsultationForm_id
				LEFT JOIN v_DirType DT with(nolock) on DT.DirType_id = ED.DirType_id
				LEFT JOIN v_EvnStatus ES with(nolock) on ES.EvnStatus_id = ED.EvnStatus_id
				outer apply (
					select top 1
						esh.EvnStatusCause_id,
						esh.pmUser_updID
					from
						v_EvnStatusHistory esh with (nolock)
					where
						esh.Evn_id = ed.EvnDirection_id
						and esh.EvnStatus_id = ed.EvnStatus_id
						and esh.EvnStatusCause_id is not null
					order by
						esh.EvnStatusHistory_id desc
				) esh
				left join v_EvnStatusCause esc (nolock) on esc.EvnStatusCause_id = esh.EvnStatusCause_id
			WHERE 
				{filters}
		";
		if ( empty($data['EvnDirection_id']) ) {
			$query = strtr($query_tpl, array(
				'{limit}' => $limit,
				'{add_fields}' => '',
				'{filters}' => implode(' AND ', $filters),
			));
			$query .= "
			ORDER BY
				deadDiff,
				EvnDirection_failDT,
				EvnUslugaTelemed_id,
				isnull(ED.EvnDirection_isCito,1) desc,
				ED.EvnDirection_setDT
				";
			// echo getDebugSQL($query, $queryParams); 
			// exit;
			$res = $this->db->query($query, $queryParams);
			if ( is_object($res) ) {
				return $res->result('array');
			} else {
				return false;
			}
		} else {
			$queryParams['EvnDirection_id'] = $data['EvnDirection_id'];
			$query = strtr($query_tpl, array(
				'{limit}' => 1,
				'{add_fields}' => ',3 as first_sort
				,ED.EvnDirection_setDT as second_sort',
				'{filters}' => 'ED.EvnDirection_id = :EvnDirection_id',
			));
			$query .= "
			union all
			";
			$filters['EvnDirection_id'] = 'ED.EvnDirection_id <> :EvnDirection_id';
			$query .= strtr($query_tpl, array(
				'{limit}' => $limit-1,
				'{add_fields}' => ',isnull(ED.EvnDirection_isCito,1) as first_sort
				,ED.EvnDirection_setDT as second_sort',
				'{filters}' => implode(' AND ', $filters),
			));
			$query .= "
			ORDER BY
				first_sort desc,
				second_sort";
			
			$res = $this->db->query($query, $queryParams);
			
			if ( is_object($res) ) {
				return $res->result('array');
			} else {
				return false;
			}
		}
		
	}
		
	/**
	 * Создание уведомления
	 */
	function saveNotice($data, $response) {
		$query = "						
			select top 1 
				UC.UslugaComplex_Code, UC.UslugaComplex_Name, PA.Person_Fio
			from 
				v_EvnUslugaTelemed EU with(nolock)
				left join v_Person_all PA with(nolock) on PA.Person_id = EU.Person_id and PA.PersonEvn_id = EU.PersonEvn_id
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
			where 
				EU.EvnUslugaTelemed_id = :EvnUslugaTelemed_id
		";
		
		$info = $this->db->query($query, array(
			'EvnUslugaTelemed_id' => $response['EvnUslugaTelemed_id']
		));
		
		if ( is_object($info) ) {
			$info = $info->result('array')[0];
		} else return false;
		
		$noticeData = array(
			'pmUser_id' => $this->promedUserId,
			'Lpu_rid' => $this->Lpu_id,
			'MedPersonal_rid' => $data['MedPersonal_id'],
			'Evn_id' => $response['EvnUslugaTelemed_id'],
			'type' => 1,
			'autotype' => 1,
			'title' => 'Выполнение услуги',
			'text' => 'Услуга "'.$info['UslugaComplex_Code'].' '.$info['UslugaComplex_Name']. '", назначеннная пациенту ' .$info['Person_Fio'].', выполнена'
		);
		$this->load->model('Messages_model', 'Messages_model');
		$noticeResponse = $this->Messages_model->autoMessage($noticeData);
	}
	
	/**
	 * Получить данные связанного направления (из арм диагностики)
	 */
	function loadParentEvnDirection($data) {
		$queryParams = array();
		$queryParams['EvnDirection_id'] = $data['EvnDirection_id'];
		$query ="
			select
				E.Evn_id,
				E.EvnClass_SysNick,
				R.MedService_id,
				UC.UslugaComplex_Name,
				EUP.EvnUslugaPar_id,
				EUP.Lpu_id
			from v_Evn E
			left join v_EvnUslugaPar EUP on EUP.EvnUslugaPar_id = E.Evn_id
			left join v_UslugaComplex UC on UC.UslugaComplex_id = EUP.UslugaComplex_id
			left join passport.MedProductCardResource CR on CR.MedProductCard_id = EUP.MedProductCard_id
			left join v_Resource R on R.Resource_id = CR.Resource_id
			where E.Evn_id = :EvnDirection_id
		";
		//~ echo getDebugSQL($query, $queryParams);exit;
		$res = $this->db->query($query, $queryParams);
			
		if ( is_object($res) ) {
			$associatedResearches = $this->checkAssociatedResearches($res->result('array'));
			
			if(!empty($associatedResearches)) {
				return $associatedResearches;
			}

			return $res->result('array');			
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка рецептов для формы редактирования услуги
	 */
	function loadReceptKardioPanel($data) {
		$query = "
			declare
				@ReceptForm_id bigint;
	
			set @ReceptForm_id = (select top 1 ReceptForm_id from v_ReceptForm with (nolock) where ReceptForm_Code = '148 (к)' order by ReceptForm_id);
			select
				er.EvnRecept_id,
				er.EvnRecept_Ser,
				er.EvnRecept_Num,
				isnull(er.EvnRecept_IsSigned, 1) as EvnRecept_IsSigned,
				isnull(er.EvnRecept_IsPrinted, 1) as EvnRecept_IsPrinted,
				rt.ReceptType_Code,
				coalesce(dcm.DrugComplexMnn_RusName, am.RUSNAME,'') as Drug_Name,
				convert(varchar(10), er.EvnRecept_setDT, 104) as EvnRecept_setDate,
				cast(er.EvnRecept_Kolvo as float) as EvnRecept_Kolvo
			from
				v_EvnRecept er (nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = er.Drug_rlsid
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = er.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_Actmatters am with (nolock) on am.ACTMATTERS_ID = dcmn.Actmatters_id
				left join v_ReceptType rt with (nolock) on rt.ReceptType_id = er.ReceptType_id
			where
				er.EvnRecept_pid = :EvnRecept_pid and
				er.ReceptForm_id = @ReceptForm_id and
				er.ReceptRemoveCauseType_id is null
		";
		$recept_list = $this->queryResult($query, array(
			'EvnRecept_pid' => $data['Evn_id']
		));
		return $recept_list;
	}

	/**
	 * Возвращает информацияю о том, указаны ли для данной услуги файлы
	 */
	function getEvnMediaDataExists($data) {
		$emd_exists = false;
		$query = "
			select top 1
				emd.EvnMediaData_id
			from
				EvnMediaData emd with (nolock)
			where
				emd.Evn_id = :EvnUslugaTelemed_id;
		";
		$emd_data = $this->getFirstRowFromQuery($query, $data);
		if (!empty($emd_data['EvnMediaData_id'])) {
			$emd_exists = true;
		}
		return $emd_exists;
	}

	/**
	 * Возвращаем study_uid и ip PACS-сервера
	 */
	public function checkAssociatedResearches($data) {
		$checkViewerDigiPacs = $this->queryResult("
			SELECT DataStorage_Value as \"DataStorage_Value\"
			FROM DataStorage
			WHERE Lpu_id = :Lpu_id and DataStorage_Name = 'digiPacsAddress'
		", [
			'Lpu_id' => $data[0]['Lpu_id']
		]);

		if(empty($checkViewerDigiPacs) || empty($checkViewerDigiPacs[0]['DataStorage_Value'])) {
			return $data;
		}
		$data[0]['PACS_ip_vip'] = $checkViewerDigiPacs[0]['DataStorage_Value'];

		$response = $this->queryResult("
			SELECT 
				Study_uid as \"Study_uid\"
			FROM EvnUslugaParAssociatedResearches
			WHERE EvnUslugaPar_id = :EvnUslugaPar_id
		", [
			'EvnUslugaPar_id' => $data[0]['EvnUslugaPar_id']
		]);

		if(empty($response)) {
			return $data;
		}
		$data[0]['Study_uid'] = $response[0]['Study_uid'];

		return $data;
	}
}