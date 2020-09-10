<?php defined('BASEPATH') or die('No direct script access allowed');

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('PersonRegisterBase_model.php');
/**
 * Модель объектов "Запись регистра по орфанным заболеваниям"
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Александр Пермяков
 * @version      03.2015
 *
 * @property string $сode № регистровой записи. Целое число, 13
 *
 * @property-read pmMediaData_Model $pmMediaData_Model
 * @property-read EvnNotifyRegister_model $EvnNotifyRegister_model
 */
class PersonRegisterProf_model extends PersonRegisterBase_model
{
	protected $_personRegisterTypeSysNick = 'prof'; // всегда перекрывать
	protected $_userGroupCode = 'ProfRegistry'; // можно не перекрывать, если задано стандартно, например "OrphanRegistry" для типа регистра "orphan"
	protected $_PersonRegisterType_id = 47; // если не для всех регионов, то нельзя перекрывать
	//protected $_exportLimit = 3145728; // 3 Мб, рекомендуется создавать файлы не больше 2-3 Мб, но не более 8

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct($this->_personRegisterTypeSysNick);
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['label'] = 'Запись регистра по профессиональным заболеваниям';
		$arr['diag_id']['save'] = 'trim|required';
		$arr['isresist']['save'] = '';
		return $arr;
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		if ($this->isNewRecord && empty($this->EvnNotifyBase_id)) {
			/*
			 * При добавлении записи регистра из формы «Регистр по проф заболеваниям»
			 * (ручной ввод новой записи регистра без извещения)
			 * автоматически создавать «Направление на включение в регистр»
			 */
			$this->load->model('EvnNotifyRegister_model');
			// могут быть региональные модели типа ufa_EvnNotifyRegister_model, наследующие EvnNotifyRegister_model
			$className = get_class($this->EvnNotifyRegister_model);
			/**
			 * @var EvnNotifyRegister_model $instance
			 */
			$instance = new $className($this->personRegisterTypeSysNick, 1);
			$res = $instance->doSave(array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_CREATE,
				'PersonRegister_id' => null,
				'EvnNotifyRegister_id' => null,
				'Morbus_id' => null,
				'MorbusType_id' => $this->MorbusType_id,
				'Diag_id' => $this->Diag_id,
				'MedPersonal_id' => $this->MedPersonal_iid,
				'Lpu_did' => $this->Lpu_iid,
				'EvnNotifyRegister_setDate' => $this->setDate->format('Y-m-d'),
				'Person_id' => $this->Person_id,
				'Server_id' => $this->personData['Server_id'],
				'PersonEvn_id' => $this->personData['PersonEvn_id'],
				'Lpu_oid' => $this->personData['LpuAttachOsn_id'],//МО основного прикрепления пациента
			), false);
			if (!empty($res['Error_Msg'])) {
				// отменяем сохранение записи регистра
				throw new Exception($res['Error_Msg'], 500);
			}
			if (empty($res['EvnNotifyRegister_id'])) {
				throw new Exception('Ошибка создания направления на включение в регистр', 500);
			}
			$this->setAttribute('evnnotifybase_id', $res['EvnNotifyRegister_id']);
		}
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 *
	 * Если сохранение выполняется внутри транзакции,
	 * то при запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		return true;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getPersonData()
	{
		if ( empty($this->_personData) ) {
			if ( empty($this->Person_id) ) {
				throw new Exception('Нужно указать человека');
			}
			$this->_personData = $this->getFirstRowFromQuery('
				select top 1
				ps.Person_BirthDay,
				PS.Server_id,
				PS.PersonEvn_id,
				PC.Lpu_id as LpuAttachOsn_id
				from v_PersonState PS with (nolock)
				left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
				where PS.Person_id = :Person_id
			', array('Person_id'=>$this->Person_id)
			);
			if (empty($this->_personData)) {
				throw new Exception('Человек не найден');
			}
		}
		return $this->_personData;
	}

	/**
	 * Обновление МО создания направления на включение в регистр
	 * @param int $id Идентификатор записи регистра
	 * @param mixed $value
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	function updateLpuDid($id, $value, $isAllowTransaction = true)
	{
		if (empty($this->promedUserId) || empty($this->sessionParams)) {
			throw new Exception('Параметры не были установлены', 500);
		}
		if (empty($id)) {
			throw new Exception('Не указан ключ объекта', 500);
		}
		if (empty($value)) {
			throw new Exception('Не указано МО создания направления на включение в регистр', 400);
		}
		$data = array();
		$data[$this->primaryKey(true)] = $id;
		$this->setAttributes($data);
		$this->load->model('EvnNotifyRegister_model');
		$className = get_class($this->EvnNotifyRegister_model);
		/**
		 * @var EvnNotifyRegister_model $instance
		 */
		$instance = new $className($this->personRegisterTypeSysNick, 1);
		if (empty($this->EvnNotifyBase_id)) {
			return $instance->doSave(array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_CREATE,
				'PersonRegister_id' => $this->id,
				'MorbusType_id' => $this->MorbusType_id,
				'Diag_id' => $this->Diag_id,
				'MedPersonal_id' => $this->MedPersonal_iid,
				'Lpu_did' => $value,
				'EvnNotifyRegister_setDate' => $this->setDate->format('Y-m-d'),
				'Person_id' => $this->Person_id,
				'Server_id' => $this->personData['Server_id'],
				'PersonEvn_id' => $this->personData['PersonEvn_id'],
				'Lpu_oid' => $this->personData['LpuAttachOsn_id'],//МО основного прикрепления пациента
			), $isAllowTransaction);
		} else {
			return $instance->doSave(array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_UPDATE,
				'PersonRegister_id' => $this->id,
				'EvnNotifyRegister_id' => $this->EvnNotifyBase_id,
				'Lpu_did' => $value,
			), $isAllowTransaction);
		}
	}

	/**
	 * Обновление значения МО, в которой пациенту впервые установлен диагноз орфанного заболевания, направления на включение в регистр
	 * @param int $id Идентификатор записи регистра
	 * @param mixed $value
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	function updateLpuOid($id, $value, $isAllowTransaction = true)
	{
		if (empty($this->promedUserId) || empty($this->sessionParams)) {
			throw new Exception('Параметры не были установлены', 500);
		}
		if (empty($id)) {
			throw new Exception('Не указан ключ объекта', 500);
		}
		if (empty($value)) {
			throw new Exception('Не указано МО, в которой пациенту впервые установлен диагноз проф заболевания', 400);
		}
		$data = array();
		$data[$this->primaryKey(true)] = $id;
		$this->setAttributes($data);
		$this->load->model('EvnNotifyRegister_model');
		$className = get_class($this->EvnNotifyRegister_model);
		/**
		 * @var EvnNotifyRegister_model $instance
		 */
		$instance = new $className($this->personRegisterTypeSysNick, 1);
		if (empty($this->EvnNotifyBase_id)) {
			return $instance->doSave(array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_CREATE,
				'PersonRegister_id' => $this->id,
				'EvnNotifyRegister_id' => null,
				'Morbus_id' => null,
				'MorbusType_id' => $this->MorbusType_id,
				'Diag_id' => $this->Diag_id,
				'MedPersonal_id' => $this->MedPersonal_iid,
				'Lpu_did' => $this->Lpu_iid,
				'EvnNotifyRegister_setDate' => $this->setDate->format('Y-m-d'),
				'Person_id' => $this->Person_id,
				'Server_id' => $this->personData['Server_id'],
				'PersonEvn_id' => $this->personData['PersonEvn_id'],
				'Lpu_oid' => $value,
			), $isAllowTransaction);
		} else {
			$data = array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_UPDATE,
				'PersonRegister_id' => $this->id,
				'EvnNotifyRegister_id' => $this->EvnNotifyBase_id,
				'Lpu_oid' => $value,
			);
			$instance->applyData($data);
			$data['Lpu_did'] = $instance->Lpu_id;
			$instance->setParams($data);
			return $instance->doSave(array(), $isAllowTransaction);
		}
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case 'export':
				$rules['ExportType']['rules'] = 'trim|required';
				$rules['Lpu_eid'] = array(
					'field' => 'Lpu_eid',
					'label' => 'МО',
					'rules' => 'trim',
					'type' => 'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров, переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		if (in_array($this->scenario, array('export'))) {
			$this->_params['ExportType'] = isset($data['ExportType']) ? $data['ExportType'] : null ;
			$this->_params['ExportDate'] = isset($data['ExportDate']) ? $data['ExportDate'] : null ;
		}
	}

	/**
	 * Получение данных раздела "Сведения об инвалидности" формы просмотра записи регистра
	 */
	function getPersonPrivilegeInvAllViewData($data)
	{
		if (empty($data['Person_id'])) {
			throw new Exception('Не передан идентификатор человека');
		}
		$params = array(
			'Person_id'=>$data['Person_id'],
		);
		$query = "
			declare @curDate datetime = CAST(dbo.tzGetDate() as date);
			SELECT
				PP.PersonPrivilege_id
				,PT.PrivilegeType_Code
				,PT.PrivilegeType_Name
				,CONVERT(varchar,PP.PersonPrivilege_begDate,104) as PersonPrivilege_begDate
				,CONVERT(varchar,PP.PersonPrivilege_endDate,104) as PersonPrivilege_endDate
				,v_YesNo.YesNo_Name as PersonRefuse_IsRefuse_Name
			FROM
				v_PersonPrivilege PP WITH (NOLOCK)
				inner join v_PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id and PT.PrivilegeType_Code in ('83','82','81','84')
				outer apply (
					select top 1 PR.PersonRefuse_IsRefuse from PersonRefuse PR WITH (NOLOCK)
					where PR.Person_id = PP.Person_id and PR.PersonRefuse_Year = year(@curDate)
					order by PR.PersonRefuse_IsRefuse desc
				) refuse
				left join v_YesNo WITH (NOLOCK) on v_YesNo.YesNo_id = ISNULL(refuse.PersonRefuse_IsRefuse, 1)
			WHERE
				PP.Person_id = :Person_id
			ORDER BY
				PP.PersonPrivilege_begDate ASC
		";
		//throw new Exception(getDebugSQL($query, $params));
		$result = $this->db->query($query, $params);
		if (false == is_object($result)) {
			throw new Exception('Ошибка БД');
		}
		return $result->result('array');
	}
	/**
	 * Получение данных для формы просмотра записи регистра
	 */
	function getViewData($data)
	{
		if (empty($data['PersonRegister_id'])) {
			throw new Exception('Не передан идентификатор записи регистра');
		}
		$queryParams = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonRegisterType_id' => $this->PersonRegisterType_id,
		);
		$query = "
			SELECT TOP 1
				case when PR.PersonRegisterOutCause_id is null then 1 else 0 end as accessType
				,PR.PersonRegister_id
				,PR.Person_id
				,PRT.PersonRegisterType_id
				,PRT.PersonRegisterType_SysNick
				,PR.PersonRegister_Code
				,OutCause.PersonRegisterOutCause_id
				,OutCause.PersonRegisterOutCause_Name
				,DNS.Diag_id
				,DNS.Diag_Code
				,DNS.Diag_FullName as Diag_Name
				,convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate
				,convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				,RTRIM(PS.Person_SurName) as Person_Surname
				,RTRIM(PS.Person_FirName) as Person_Firname
				,RTRIM(PS.Person_SecName) as Person_Secname
				,convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday
				,RTRIM(PS.Person_Snils) as Person_Snils
				,RTRIM(v_Sex.Sex_Name) as Sex_Name
				,RTRIM(v_SocStatus.SocStatus_Name) as SocStatus_Name
				,RTRIM(isnull([UAddress].Address_Nick, [UAddress].Address_Address)) as Person_RAddress
				,RTRIM(isnull([PAddress].Address_Nick, [PAddress].Address_Address)) as Person_PAddress
				,CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(Polis.Polis_Ser) END as Polis_Ser
				,CASE WHEN PolisType.PolisType_Code = 4 then RTRIM(ps.Person_EdNum) ELSE RTRIM(Polis.Polis_Num) END AS Polis_Num
				,convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate
				,convert(varchar(10), Polis.Polis_endDate, 104) as Polis_endDate
				,RTRIM(PO.Org_Name) as OrgSmo_Name
				,RTRIM([Document].Document_Num) as Document_Num
				,RTRIM([Document].Document_Ser) as Document_Ser
				,convert(varchar(10), [Document].Document_begDate, 104) as Document_begDate
				,RTRIM(DO.Org_Name) as OrgDep_Name
				,RTRIM(PJ.Org_Name) as Person_Job
				,RTRIM(PP.Post_Name) as Person_Post
				,pcard.PersonCard_id
				,CASE WHEN (pcard.PersonCard_endDate IS NOT NULL)
					THEN isnull(RTRIM(LpuAttach.Lpu_Nick), '') + ' (Прикрепление неактуально. Дата открепления: '+isnull(convert(varchar(10), pcard.PersonCard_endDate, 104), '')+')'
					ELSE RTRIM(LpuAttach.Lpu_Nick)
				end as LpuAttach_Nick
				,convert(varchar(10), pcard.LpuRegion_Name, 104) as LpuRegion_Name
				,convert(varchar(10), pcard.PersonCard_begDate, 104) as PersonCard_begDate
				,ENR.EvnNotifyRegister_id
				,LpuO.Lpu_id as Lpu_oid
				,LpuO.Lpu_Nick as LpuO_Name
				,LpuD.Lpu_id as Lpu_did
				,LpuD.Lpu_Nick as LpuD_Name
			FROM
				v_PersonRegister PR WITH (NOLOCK)
				inner join v_PersonRegisterType PRT WITH (NOLOCK) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = PR.Person_id
				left join v_Diag DNS WITH (NOLOCK) on DNS.Diag_id = PR.Diag_id
				left join v_PersonRegisterOutCause OutCause WITH (NOLOCK) on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_Sex WITH (NOLOCK) on v_Sex.Sex_id = PS.Sex_id
				left join v_SocStatus WITH (NOLOCK) on v_SocStatus.SocStatus_id = PS.SocStatus_id
				left join [Address] [UAddress] WITH (NOLOCK) on [UAddress].Address_id = PS.UAddress_id
				left join [Address] [PAddress] WITH (NOLOCK) on [PAddress].Address_id = PS.PAddress_id
				left join [Polis] WITH (NOLOCK) on [Polis].[Polis_id] = [PS].[Polis_id]
				left join [PolisType] WITH (NOLOCK) on [PolisType].[PolisType_id] = [Polis].[PolisType_id]
				left join [OrgSmo] WITH (NOLOCK) on [OrgSmo].[OrgSmo_id] = [Polis].[OrgSmo_id]
				left join [Org] [PO] WITH (NOLOCK) on [PO].[Org_id] = [OrgSmo].[Org_id]
				left join [Document] WITH (NOLOCK) on [Document].[Document_id] = [PS].[Document_id]
				left join [OrgDep] WITH (NOLOCK) on [OrgDep].[OrgDep_id] = [Document].[OrgDep_id]
				left join [Org] [DO] WITH (NOLOCK) on [DO].[Org_id] = [OrgDep].[Org_id]
				left join [v_Job] [Job] WITH (NOLOCK) on [Job].[Job_id] = [PS].[Job_id]
				left join [Org] [PJ] WITH (NOLOCK) on [PJ].[Org_id] = [Job].[Org_id]
				left join [Post] [PP] WITH (NOLOCK) on [PP].[Post_id] = [Job].[Post_id]
				outer apply (select top 1
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc WITH (NOLOCK)
					where pc.Person_id = PS.Person_id and pc.LpuAttachType_id = 1
					order by PersonCard_begDate desc
				) as pcard
				left join v_Lpu LpuAttach WITH (NOLOCK) on LpuAttach.Lpu_id = PS.Lpu_id
				left join v_EvnNotifyRegister ENR WITH (NOLOCK) on ENR.EvnNotifyRegister_id = PR.EvnNotifyBase_id
					and ENR.NotifyType_id = 1
				left join v_Lpu LpuO WITH (NOLOCK) on LpuO.Lpu_id = ENR.Lpu_oid
				left join v_Lpu LpuD WITH (NOLOCK) on LpuD.Lpu_id = ENR.Lpu_id
			WHERE
				PR.PersonRegister_id = :PersonRegister_id and PR.PersonRegisterType_id = :PersonRegisterType_id
		";
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception('Ошибка БД');
		}
		$tmp = $result->result('array');
		if (empty($tmp)) {
			throw new Exception('Не удалось загрузить данные записи регистра');
		}
		if (!is_array($tmp)) {
			throw new Exception('Этого сообщения пользователь не должен был увидеть 1', 500);
		}
		if (empty($tmp[0]['Person_id'])) {
			throw new Exception('Этого сообщения пользователь не должен был увидеть 2', 500);
		}
		$this->load->model('pmMediaData_Model');
		$tmp[0]['PersonPhotoThumbName'] = $this->pmMediaData_Model->getPersonPhotoThumbName(array(
			'Person_id' => $tmp[0]['Person_id'],
		));
		return $tmp;
	}

	/**
	 * Имя шаблона для экспорта записей регистра этого типа
	 * @return string
	 */
	function getExportTemplateName()
	{
		return "orph_register";
	}

	/**
	 * Запрос данных для выгрузки в федеральный регистр регионального сегмента
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	protected function _loadExportData($data)
	{
		if ( false == swPersonRegister::isAllow($this->personRegisterTypeSysNick) ) {
			throw new Exception('Работа с данным типом регистра недоступна!');
		}
		$this->setScenario('export');
		$this->setParams($data);

		if (false == $this->isAllowScenario()) {
			throw new Exception('Действие «Выгрузка в федеральный регистр» не доступно');
		}
		if (empty($this->_params['ExportType'])) {
			$this->_params['ExportType'] = 2;
		}
		if (false == in_array($this->_params['ExportType'], array(1,2))) {
			throw new Exception('Неправильный тип выгрузки');
		}
		if (empty($this->_params['ExportDate'])) {
			$this->_params['ExportDate'] = $this->currentDT->format('Y-m-d') ;
		}
		if ($this->_params['ExportDate'] != $this->currentDT->format('Y-m-d')) {
			throw new Exception('Дата выгрузки должна быть равна текущей дате');
		}
		$params = array();
		$params['PersonRegisterType_id'] = $this->PersonRegisterType_id;
		if (2 == $this->_params['ExportType']) {
			// Изменения
			$listId = $this->loadChangedPersonRegisterIdList($this->PersonRegisterType_id);
			if (empty($listId)) {
				return array();
			}
			$listId = implode(',', $listId);
			$filter = "PR.PersonRegister_id in ({$listId})";
		} else {
			// все
			$filter = "PR.PersonRegisterType_id = :PersonRegisterType_id";
		}

		$query = "
			with PR as (
				select
					PR.PersonRegister_id,
					PR.Person_id,
					PR.Diag_id,
					PRD.MorbusType_id
				from v_PersonRegister PR with (nolock)
				inner join PersonRegisterDiag PRD with (nolock) on PRD.PersonRegisterType_id = PR.PersonRegisterType_id and PRD.Diag_id = PR.Diag_id
				where
					{$filter}
			),
			ER as (
				select  /* Рецепты не отоваренные */
					isnull(ER.Drug_id,ER.Drug_rlsid) as ID,
					convert(varchar(10), ER.EvnRecept_setDT, 104) as DATE_ISSUE,
					convert(varchar(10), ER.EvnRecept_otpDT, 104) as DATE_DISP,
					isnull(D.Drug_Name,DRls.Drug_Name) as DESCR,
					PR.Person_id,
					PR.MorbusType_id
				from v_EvnRecept ER with (nolock)
					inner join PersonRegisterDiag PRD with (nolock) on PRD.Diag_id = ER.Diag_id
						and PRD.PersonRegisterType_id = :PersonRegisterType_id
					inner join PR on PR.Person_id = ER.Person_id AND PR.MorbusType_id = PRD.MorbusType_id /* только по заболеванию, по которому человек включен в федеральный регистр */
					left join rls.v_Drug DRls with (nolock) on DRls.Drug_id = ER.Drug_rlsid
					left join v_Drug D with (nolock) on D.Drug_id = ER.Drug_id
				where
					not exists (select top 1 RO.ReceptOtov_id from ReceptOtov RO with (nolock) where RO.EvnRecept_id = ER.EvnRecept_id)
				union all
				select  /* Рецепты отоваренные */
					coalesce(ER.Drug_cid,ReceptOtovDop.Drug_rlsid,ER.Drug_id) as ID,
					convert(varchar(10), ER.EvnRecept_setDT, 104) as DATE_ISSUE,
					convert(varchar(10), ER.EvnRecept_otpDate, 104) as DATE_DISP,
					isnull(DRls.Drug_Name,D.Drug_Name) as DESCR,
					PR.Person_id,
					PR.MorbusType_id
				from ReceptOtov ER with (nolock)
					inner join PersonRegisterDiag PRD with (nolock) on PRD.Diag_id = ER.Diag_id
						and PRD.PersonRegisterType_id = :PersonRegisterType_id
					inner join PR on PR.Person_id = ER.Person_id AND PR.MorbusType_id = PRD.MorbusType_id /* только по заболеванию, по которому человек включен в федеральный регистр */
					left join ReceptOtovDop with (nolock) on ReceptOtovDop.ReceptOtov_id = ER.ReceptOtov_id
					left join rls.v_Drug DRls with (nolock) on DRls.Drug_id = isnull(ER.Drug_cid,ReceptOtovDop.Drug_rlsid)
					left join v_Drug D with (nolock) on D.Drug_id = ER.Drug_id
				where
					1=1
			)

            select
                RTRIM(PS.Person_SurName) as S_SURNAME,
                RTRIM(PS.Person_FirName) as S_NAME,
                RTRIM(PS.Person_SecName) as S_PATRONYMIC,
                convert(varchar(10), PS.Person_Birthday, 104) as D_DATE,
                DocTP.DocumentType_Code as DOCUM_CODE,
                RTRIM(PS.Document_Ser + ' ' + PS.Document_Num ) as DOCUM_SERIA_NUMBER,
                dbo.GetRegion() as TERR,
                PS.Person_Snils as SNILS,
                os.Orgsmo_f002smocod as INS_COMP,
                PS.Polis_Num as INSURANCE_NUMBER,
                ps.sex_id as SEX,
                VOD.OrgDep_Nick as WHO_GAVES_DOCS,
                VKLS.KLStreet_Name as STREET,
                VPUA.Address_Corpus as HOUSE_BLOCK,
                VPUA.Address_House as HOUSE_NUM,
                VPUA.Address_Flat as APPARTAMENT_NUM,
                ISNULL(VPST.PersonSprTerrDop_Code, VPST2.PersonSprTerrDop_Code) as KLADR_DISTRICT,
                VPUA.KLCity_id as KLADR_CITY,
                ISNULL(INVALID.INVALID, 'Нет') as INVALID,
                Lpu.ORG_OKPO as MU_OKPO,
                Diag.Diag_Code as DIAGNOZ,
				STUFF(
					(
					SELECT
						ER.ID,
						ER.DATE_ISSUE,
						ER.DATE_DISP,
						ER.DESCR
					FROM ER
					WHERE ER.Person_id = PR.Person_id and ER.MorbusType_id = PR.MorbusType_id
					FOR XML PATH ('ITEM')
					), 1, 1, '<'
				) as DRUGS,
                PR.PersonRegister_id
			from
				PR with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = PR.Person_id
                left join v_Document Doc with (nolock) on Doc.Document_id = PS.Document_id
                left join v_DocumentType DocTP with (nolock) on Doc.DocumentType_id = DocTP.DocumentType_id
                left join v_OrgDep VOD with (nolock) on Doc.OrgDep_id = VOD.OrgDep_id
                left join v_Polis p with (nolock) on p.Polis_id = ps.Polis_id
                left join v_OrgSmo os with (nolock) on os.OrgSmo_id = p.OrgSmo_id
                left join PersonUAddress PUA with (nolock) on PS.UAddress_id = PUA.UAddress_id
                left join v_PersonUAddress VPUA with (nolock) on PUA.PersonUAddress_id = VPUA.PersonUAddress_id
                left join v_KLStreet VKLS with (nolock) on VKLS.KLStreet_id = VPUA.KLStreet_id
                left join v_PersonInfo VPI with (nolock) on VPI.Person_id = PS.Person_id
                left join v_PersonSprTerrDop VPST with (nolock) on VPI.UPersonSprTerrDop_id = VPST.PersonSprTerrDop_id
                left join v_PersonSprTerrDop VPST2 with (nolock) on VPI.PPersonSprTerrDop_id = VPST.PersonSprTerrDop_id
                left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
                left join v_Lpu_all Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
                left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
                outer apply (
                    select top 1
                        case
                            when VPP.PrivilegeType_Code = '81' and (VPP.PersonPrivilege_endDate is null or VPP.PersonPrivilege_endDate >= dbo.tzGetDate()) then 'III гр.'
                            when VPP.PrivilegeType_Code = '82' and (VPP.PersonPrivilege_endDate is null or VPP.PersonPrivilege_endDate >= dbo.tzGetDate()) then 'II гр.'
                            when VPP.PrivilegeType_Code = '83' and (VPP.PersonPrivilege_endDate is null or VPP.PersonPrivilege_endDate >= dbo.tzGetDate()) then 'I гр.'
                            when VPP.PrivilegeType_Code = '84' and (VPP.PersonPrivilege_endDate is null or VPP.PersonPrivilege_endDate >= dbo.tzGetDate()) then 'Ребенок-инвалид'
                            when VPP.PrivilegeType_Code is not null and VPP.PersonPrivilege_endDate is not null and VPP.PersonPrivilege_endDate < dbo.tzGetDate() then 'Снята'
                        end as INVALID
                        from v_PersonPrivilege VPP with (nolock)
                        where
                            VPP.Person_id = PS.Person_id
                            and VPP.PrivilegeType_Code in ('81','82','83','84')
                    order by VPP.PersonPrivilege_endDate, VPP.PersonPrivilege_updDT desc
                ) as INVALID
        ";
		//echo getDebugSQL($query, $params); die();
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$tmp = $result->result('array');
		} else {
			throw new Exception('Ошибка запроса к БД 05');
		}
		$response = array();
		foreach ($tmp as $row) {
			// исключаем записи, в которых нет обязательных данных для выгрузки
			if (empty($row['S_SURNAME'])
				|| empty($row['S_NAME'])
				|| empty($row['S_PATRONYMIC'])
				|| empty($row['D_DATE'])
				|| empty($row['DOCUM_CODE'])
				|| empty($row['DOCUM_SERIA_NUMBER'])
				|| empty($row['TERR'])
				|| empty($row['SNILS'])
				|| empty($row['INS_COMP'])
				|| empty($row['INSURANCE_NUMBER'])
			) {
				$err = $row['S_SURNAME'] . ' ' . $row['S_NAME'] . ' ' . $row['S_PATRONYMIC'] . ', ' . $row['D_DATE']; // . ' ' . $row['PersonRegister_id']
				if (empty($row['S_SURNAME'])) {
					$err .= '.<br/>Не заполнена Фамилия';
				}
				if (empty($row['S_NAME'])) {
					$err .= '.<br/>Не заполнено Имя';
				}
				if (empty($row['S_PATRONYMIC'])) {
					$err .= '.<br/>Не заполнено Отчество';
				}
				if (empty($row['D_DATE'])) {
					$err .= '.<br/>Не заполнена Дата рождения';
				}
				if (empty($row['DOCUM_CODE'])) {
					$err .= '.<br/>Не заполнен Код типа документа';
				}
				if (empty($row['DOCUM_SERIA_NUMBER'])) {
					$err .= '.<br/>Не заполнены серия и номер документа';
				}
				if (empty($row['TERR'])) {
					$err .= '.<br/>Не заполнен Код субъкта РФ';
				}
				if (empty($row['SNILS'])) {
					$err .= '.<br/>Не заполнен СНИЛС';
				}
				if (empty($row['INS_COMP'])) {
					$err .= '.<br/>Не заполнен код страховой компании';
				}
				if (empty($row['INSURANCE_NUMBER'])) {
					$err .= '.<br/>Не заполнен номер страхового полиса';
				}
				$this->_saveResponse['ExportErrorArray'][] = array(
					'Text' => $err,
					'Time' => date('H:i:s'),
				);
				continue;
			}
			if (empty($response[$row['PersonRegister_id']])) {
				// (SUBSTRING(PS.Person_Snils, 1,3) + '-' + SUBSTRING(PS.Person_Snils,4,3) + '-' + SUBSTRING(PS.Person_Snils,7,3)  + '-' + SUBSTRING(PS.Person_Snils, 10,2)) as SNILS,
				if (!empty($row['SNILS'])) {
					$snils = preg_replace('/[^0-9]/', '', $row['SNILS']); //удаление всего кроме цифр
					if (strlen($snils) >= 11) {
						$row['ins_account_num'] = substr($snils, 0, 3).'-'.substr($snils, 3, 3).'-'.substr($snils, 6, 3).'-'.substr($snils, 9);
					}
				}
				$response[$row['PersonRegister_id']] = $row;
				//Делаем записи о выгрузке
				$this->_insertPersonRegisterExport($row['PersonRegister_id'], $this->_params['ExportType'], $this->promedUserId);
			}
		}
		return $response;
	}
}