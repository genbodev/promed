<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusOrphan_model - модель для MorbusOrphan
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Пермяков Александр
 * @version      10.2012
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 */
class MorbusOrphan_model extends swPgModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;

	private $entityFields = [
		'MorbusOrphan' =>[
			'Morbus_id',
            'Lpu_id'
		],
		'Morbus' => [//allow Deleted
			'MorbusBase_id',
            'Evn_pid', //Учетный документ, в рамках которого добавлено заболевание
            'Diag_id',
            'MorbusKind_id',
            'Morbus_Name',
            'Morbus_Nick',
            'Morbus_disDT',
            'Morbus_setDT',
            'MorbusResult_id'
		],
		'MorbusBase' => [//allow Deleted
			'Person_id',
            'Evn_pid',
            'MorbusType_id',
            'MorbusBase_setDT',
            'MorbusBase_disDT',
            'MorbusResult_id'
		]
	];

	protected $_MorbusType_id = 6;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'orphan';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * @return string
	 */
	function getGroupRegistry()
	{
		return 'Orphan';
	}

	/**
	 * Удаление данных специфик заболевания заведенных из регистра, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeletePersonRegister
	 * @param PersonRegister_model $model
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление записи регистра
	 */
	public function onBeforeDeletePersonRegister(PersonRegister_model $model, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых нет ссылки на Evn
		// если таковых разделов нет, то этот метод можно убрать
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusOrphan';
	}

	/**
	 * Удаление данных специфик заболевания заведенных в учетном документе, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeleteEvn
	 * @param EvnAbstract_model $evn
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление учетного документа
	 */
	public function onBeforeDeleteEvn(EvnAbstract_model $evn, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых есть ссылка на Evn
		// если таковых нет, то этот метод можно убрать
	}

	/**
	 * Сохранение специфики
	 * author Alexander Permyakov aka Alexpm
	 * return array Идентификаторы объектов, которые были обновлены или ошибка
	 * comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	private function updateMorbusSpecific($data) {
		$err_arr = [];
		$entity_saved_arr = [];
		$not_edit_fields = [
		    'Evn_pid',
            'Person_id',
            'MorbusOrphan_id',
            'Morbus_id',
            'MorbusBase_id',
            'MorbusType_id',
            'Morbus_setDT',
            'Morbus_disDT',
            'MorbusBase_setDT',
            'MorbusBase_disDT'
        ];
		if(isset($data['field_notedit_list']) && is_array($data['field_notedit_list']))
		{
			$not_edit_fields = array_merge($not_edit_fields,$data['field_notedit_list']);
		}
		foreach($this->entityFields as $entity => $l_arr) {
			$allow_save = false;
			foreach($data as $key => $value) {
				if(in_array($key, $l_arr) && !in_array($key, $not_edit_fields))
				{
					$allow_save = true;
					break;
				}
			}

			if( $allow_save && !empty($data[$entity.'_id']) )
			{
				$q = 'select '. implode(', ',$l_arr . ' as "'.$l_arr.'"') .' from dbo.v_'. $entity .' where '. $entity .'_id = :'. $entity .'_id limit 1';
				$p = [$entity.'_id' => $data[$entity.'_id']];
				$r = $this->db->query($q, $data);
				if (is_object($r))
				{
					$result = $r->result('array');
					if( empty($result) || !is_array($result[0]) || count($result[0]) == 0 )
					{
						$err_arr[] = 'Получение данных '. $entity .' По идентификатору '. $data[$entity.'_id'] .' данные не получены';
						continue;
					}
					foreach($result[0] as $key => $value) {
						if (is_object($value) && $value instanceof DateTime)
						{
							$value = $value->format('Y-m-d H:i:s');
						}
						//в $data[$key] может быть null
						$p[$key] = array_key_exists($key, $data)?$data[$key]:$value;
						// ситуация, когда пользователь удалил какое-то значение
						$p[$key] = (empty($p[$key]) || $p[$key]=='0')?null:$p[$key];
					}
				}
				else
				{
					$err_arr[] = 'Получение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
				$field_str = '';
				foreach($l_arr as $key) {
					$field_str .= '
						'. $key .' := :'. $key .',';
				}
				$q = "
					select
						{$entity}_id as \"{$entity}_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_{$entity}_upd(
						{$entity}_id := :{$entity}_id,
						pmUser_id := :pmUser_id
					)
				";
				$p['pmUser_id'] = $data['pmUser_id'];
				//if($entity == 'MorbusBase') { echo getDebugSQL($q, $p); break; }
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$result = $r->result('array');
					if( !empty($result[0]['Error_Msg']) )
					{
						$err_arr[] = 'Сохранение данных '. $entity .' '. $result[0]['Error_Msg'];
						continue;
					}
					$entity_saved_arr[$entity .'_id'] = $data[$entity.'_id'];
				} else {
					$err_arr[] = 'Сохранение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
			}
			else
			{
				continue;
			}
		}
		if (!empty($data['Evn_pid']) && !empty($data['Morbus_id'])) {
			$this->load->library('swMorbus');
			$tmp = swMorbus::updateMorbusIntoEvn([
				'Evn_id' => $data['Evn_pid'],
				'Morbus_id' => $data['Morbus_id'],
				'session' => $data['session'],
				'mode' => 'onAfterSaveMorbusSpecific',
			]);
			if (isset($tmp['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($tmp['Error_Msg']);
			}
		}
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />',$err_arr) : null;
		return [$entity_saved_arr];
	}

	/**
	 * Проверка обязательных параметров специфики
	 *
	 * @params Mode
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 */
	private function checkParams($data)
	{
		if( empty($data['Mode']) )
		{
			throw new Exception('Не указан режим сохранения');
		}
		$check_fields_list = [];
		$fields = [
		    'Diag_id' => 'Идентификатор диагноза',
            'Person_id' => 'Идентификатор человека',
            'Evn_pid' => 'Идентификатор движения/посещения',
            'pmUser_id' => 'Идентификатор пользователя',
            'Morbus_id' => 'Идентификатор заболевания',
            'MorbusOrphan_id' => 'Идентификатор специфики заболевания',
            'Morbus_setDT' => 'Дата заболевания',
            'Lpu_id' => 'ЛПУ, в которой впервые установлен диагноз орфанного заболевания'
		];
		switch ($data['Mode']) {
			case 'personregister_viewform':
				$check_fields_list = ['MorbusOrphan_id','Morbus_id','Person_id','pmUser_id'];//,'Diag_id'
				$data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = ['MorbusOrphan_id','Morbus_id','Evn_pid','pmUser_id']; //'Diag_id','Person_id',
				break;
			default:
				throw new Exception('Указан неправильный режим сохранения');
				break;
		}
		$errors = [];
		foreach($check_fields_list as $field) {
			if( empty($data[$field]) )
			{
				$errors[] = 'Не указан '. $fields[$field];
			}
		}
		if( count($errors) > 0 )
		{
			throw new Exception(implode('<br />',$errors));
		}
		return $data;
	}

	/**
	 * Сохранение специфики заболевания
	 * Обязательные параметры:
	 * 1) Evn_pid или Person_id
	 * 2) pmUser_id
	 * 3) Mode
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 * @author Alexander Permyakov aka Alexpm
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	function saveMorbusSpecific($data) {
		try {
			$data = $this->checkParams($data);

			$data['Evn_aid'] = null;
			if ($data['Mode'] == 'personregister_viewform' || $data['Evn_pid'] == $data['Evn_aid']) {
				// Если редактирование происходит из актуального учетного документа или из панели просмотра в форме записи регистра, то сохраняем данные
				return $this->updateMorbusSpecific($data);
			} else {
				//Ничего не сохраняем
				throw new Exception('Данные не были сохранены, т.к. данный учетный документ не является актуальным для данного заболевания.');
			}
		} catch (Exception $e) {
			return [['Error_Msg' => 'Сохранение специфики заболевания. <br />'. $e->getMessage()]];
		}
	}

	/**
	 * Создание специфики заболевания
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) ||empty($data['Person_id'])
			|| empty($data['Morbus_id']) || empty($data['Diag_id']) || empty($data['Morbus_setDT'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], ['onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify'])
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = [];
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];
		$queryParams['Lpu_id'] = isset($data['Lpu_id'])?$data['Lpu_id']:$this->sessionParams['lpu_id'];

		$res = $this->queryResult("
			select
				{$tableName}_id as \"{$tableName}_id\"
			from v_{$tableName}
			where Morbus_id = :Morbus_id
			limit 1
		", $queryParams);
		if (isset($res[0]) && isset($res[0]["{$tableName}_id"])) {
			$query = "
				select
					1 as \"IsCreate\",
					:{$tableName}_id as \"{$tableName}_id\"
			";
		} else {
			$queryParams["{$tableName}_id"] = $res[0]["{$tableName}_id"];
			$query = "
				select
					{$tableName}_id as \"{$tableName}_id\",
					case when 1 {$tableName}_id is null
						then 2
						else 1
					end as \"IsCreate\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_{$tableName}_ins(
					{$tableName}_id := {$tableName}_id,
					Morbus_id := :Morbus_id,
					Lpu_id := :Lpu_id,
					pmUser_id := :pmUser_id
				)
			";
		}
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка БД', 500);
		}
		$resp = $result->result('array');
		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception($resp[0]['Error_Msg'], 500);
		}
		if (empty($resp[0][$tableName . '_id'])) {
			throw new Exception("Не удалось создать объект {$tableName}", 500);
		}
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];
		return $this->_saveResponse;
	}

	/**
	 * Проверка на наличие в системе записи регистра с пустым атрибутом «Дата исключения из регистра»
	 * на данного человека с указанной «Датой смерти»
	 */
	function checkPersonDead($data)
	{
		$query = "select
			 ps.Server_id as \"Server_id\",
			 ps.Person_id as \"Person_id\",
			 ps.PersonEvn_id as \"PersonEvn_id\",
			 ps.Person_SurName as \"Person_SurName\",
			 ps.Person_FirName as \"Person_FirName\",
			 ps.Person_SecName as \"Person_SecName\",
			 DATEDIFF('SECOND', '01.01.1970', ps.Person_BirthDay) as \"Person_BirthDay\"
			from v_PersonState ps
			inner join v_PersonRegister pr on pr.Person_id = ps.Person_id
			inner join v_MorbusType mt on pr.MorbusType_id = mt.MorbusType_id
			where 
				ps.Person_deadDT is not null 
				and pr.PersonRegister_disDate is null
				and mt.MorbusType_SysNick = 'orphan'
				and ps.Person_id = ?
			limit 1
		";
		//echo getDebugSQL($query, array($data['Person_id']));die;
		$result = $this->db->query($query, [$data['Person_id']]);
		if(is_object($result)){
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 ) {
				return $response;
			} else	{
				return false;
			}
		}
		else
			return false;
	}

	/**
	 *  Получение списка пользователей с группой «Регистр по орфанным заболеваниям»
	 */
	function getUsersOrphan($data)
	{
		$query = "
		select 
			PMUser_id as \"PMUser_id\" 
		from 
			v_pmUserCache
		where 
			pmUser_groups like '%\"orphan\"%'
			and Lpu_id = ?";
		$result = $this->db->query($query, [$data['Lpu_id']]);
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 ) {
			return $response;
		} else	{
			return false;
		}
	}


	/**
	 *	Экспорт регионального сегмента регистра по орфанным заболеваниям
	 */
	function exportMorbusOrph($data)
	{
		$filter = "(1=1)";

		if (isset($data['ExportType']) && $data['ExportType'] == 2) {
			$filter .= ' and ( PS.PersonState_updDT > LE.lastExport
                         or DocTP.DocumentType_updDT > LE.lastExport
                         or os.OrgSMO_updDT > LE.lastExport
                         or VOD.OrgDep_updDT > LE.lastExport
                         or VKLS.KLStreet_updDT > LE.lastExport
                         or VPUA.PersonUAddress_updDT > LE.lastExport
                         or INVALID.PersonPrivilege_updDT > LE.lastExport
                         or PC.PersonCard_updDT > LE.lastExport
                         or M.Morbus_updDT > LE.lastExport
                         or VER.EvnRecept_updDT > LE.lastExport
                         or VED.EvnDrug_updDT > LE.lastExport
                         or MaxEvnRec.EvnRecept_updDT > LE.lastExport
            )';
		}

		$query = "
            select
                RTRIM(PS.Person_SurName) as \"S_SURNAME\",
                RTRIM(PS.Person_FirName) as \"S_NAME\",
                RTRIM(PS.Person_SecName) as \"S_PATRONYMIC\",
                to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"D_DATE\",
                DocTP.DocumentType_Code as \"DOCUM_CODE\",
                RTRIM(PS.Document_Ser || ' ' || PS.Document_Num ) as \"DOCUM_SERIA_NUMBER\",
                PR.PersonRegister_id as \"PersonRegister_id\",
                dbo.GetRegion() as \"TERR\",
                (SUBSTRING(PS.Person_Snils, 1,3)
                	|| '-' || SUBSTRING(PS.Person_Snils,4,3)
                	|| '-' || SUBSTRING(PS.Person_Snils,7,3)
                	|| '-' || SUBSTRING(PS.Person_Snils, 10,2)
                ) as \"SNILS\",
                os.Orgsmo_f002smocod as \"INS_COMP\",
                PS.Polis_Num as \"INSURANCE_NUMBER\",
                ps.sex_id as \"SEX\",
                VOD.OrgDep_Nick as \"WHO_GAVES_DOCS\",
                VKLS.KLStreet_Name as \"STREET\",
                VPUA.Address_Corpus as \"HOUSE_BLOCK\",
                VPUA.Address_House as \"HOUSE_NUM\",
                VPUA.Address_Flat as \"APPARTAMENT_NUM\",
                coalesce(VPST.PersonSprTerrDop_Code, VPST2.PersonSprTerrDop_Code) as \"KLADR_DISTRICT\",
                VPUA.KLCity_id as \"KLADR_CITY\",
                coalesce(INVALID.INVALID, 'Нет') as \"INVALID\",
                Lpu.ORG_OKPO as \"MU_OKPO\",
                Diag.Diag_Code as \"DIAGNOZ\",
                Diag.Diag_id as \"Diag_id\",
                VER.PersonEvn_id as \"PersonEvn_id\",
                PS.Person_id as \"Person_id\",
                -- Непонятные поля. пока оставляем пустыми
                null as \"MEDINST\",
                null as \"DOP_DATA\",
                null as \"DATE_INPUT\",
                null as \"DATE_OUTPUT\",
                null as \"FED_REG\"
			-- end select
			from
            -- from
                v_PersonState PS
                left join lateral(
                    select MAX(PersonRegisterExport_updDT) as lastExport
                    from PersonRegisterExport
                ) as LE on true
                inner join lateral(
                    select
                        PR.PersonRegister_id,
                        PR.Morbus_id,
                        PersonRegisterOutCause_id,
                        EvnNotifyBase_id
                    from
                        v_PersonRegister PR
                    where
                        PR.Person_id = PS.Person_id
                        and PR.MorbusType_id = :MorbusType_id
                        and (PR.PersonRegister_disDate is null or (PR.PersonRegister_disDate > LE.lastExport))
                    limit 1
                ) as PR on true
                inner join v_Morbus M on M.Morbus_id = PR.Morbus_id
                left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
                left join v_EvnNotifyOrphan EN on EN.EvnNotifyOrphan_id = PR.EvnNotifyBase_id
                left join v_MorbusOrphan MO on MO.Morbus_id = M.Morbus_id
                left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
                left join v_Lpu_all Lpu on Lpu.Lpu_id = PC.Lpu_id
                left join v_Diag Diag on Diag.Diag_id = M.Diag_id
                left join v_Document Doc on Doc.Document_id = PS.Document_id
                left join v_DocumentType DocTP on Doc.DocumentType_id = DocTP.DocumentType_id
                left join v_Polis p on p.Polis_id = ps.Polis_id
                left join v_OrgSmo os on os.OrgSmo_id = p.OrgSmo_id
                left join PersonUAddress PUA on PS.UAddress_id = PUA.UAddress_id
                left join v_PersonUAddress VPUA on PUA.PersonUAddress_id = VPUA.PersonUAddress_id
                left join v_KLStreet VKLS on VKLS.KLStreet_id = VPUA.KLStreet_id
                left join v_OrgDep VOD on Doc.OrgDep_id = VOD.OrgDep_id
                left join lateral(
                    select
                        EvnRecept_updDT,
                        PersonEvn_id
                    from
                        v_EvnRecept ER
                    where
                        Person_id = PS.Person_id
                        and Diag_id = Diag.Diag_id
                        and Lpu_id = :Lpu_id
                    limit 1
                ) as VER on true
                left join v_EvnDrug VED on VED.PersonEvn_id = VER.PersonEvn_id
                left join v_PersonInfo VPI on VPI.Person_id = PS.Person_id
                left join v_PersonSprTerrDop VPST on VPI.UPersonSprTerrDop_id = VPST.PersonSprTerrDop_id
                left join v_PersonSprTerrDop VPST2 on VPI.PPersonSprTerrDop_id = VPST.PersonSprTerrDop_id
                left join lateral(
                    select
                        VPP.PersonPrivilege_updDT,
                        case
                            when VPP.PrivilegeType_Code = '81' and (VPP.PersonPrivilege_endDate is null or VPP.PersonPrivilege_endDate >= dbo.tzGetDate()) then 'III гр.'
                            when VPP.PrivilegeType_Code = '82' and (VPP.PersonPrivilege_endDate is null or VPP.PersonPrivilege_endDate >= dbo.tzGetDate()) then 'II гр.'
                            when VPP.PrivilegeType_Code = '83' and (VPP.PersonPrivilege_endDate is null or VPP.PersonPrivilege_endDate >= dbo.tzGetDate()) then 'I гр.'
                            when VPP.PrivilegeType_Code = '84' and (VPP.PersonPrivilege_endDate is null or VPP.PersonPrivilege_endDate >= dbo.tzGetDate()) then 'Ребенок-инвалид'
                            when VPP.PrivilegeType_Code is not null and VPP.PersonPrivilege_endDate is not null and VPP.PersonPrivilege_endDate < dbo.tzGetDate() then 'Снята'
                        end as INVALID
                        from  v_PersonPrivilege VPP
                        left join v_PersonState VPS on PS.Person_id = VPP.Person_id
                        where
                            VPP.PrivilegeType_Code in ('81','82','83','84')
                            and VPS.Person_id = PS.Person_id
                    order by VPP.PersonPrivilege_endDate, VPP.PersonPrivilege_updDT desc
                    limit 1
                ) as INVALID on true
                left join lateral(
                    select
                        Max(EvnRecept_updDT) as EvnRecept_updDT
                    from
                        v_EvnRecept ER
                    where
                        Person_id = PS.Person_id
                        and Diag_id = Diag.Diag_id
                        and Lpu_id = :Lpu_id
                    limit 1
                ) as MaxEvnRec on true
            -- end from
			where
            -- where
			    {$filter}
            -- where
        ";

		$data['MorbusType_id'] = $this->getMorbusTypeId();
		//echo getDebugSQL($query, $data); die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$PERSON = $result->result('array');
		}
		else {
			return false;
		}

		if (count($PERSON) <= 0) {
			return [
				'Error_Code' => 1, 'Error_Msg' => 'Список выгрузки пуст!'
            ];
		}

		//Для каждого найденного пациента получаем список препаратов, выписанных  по случаю
		foreach ($PERSON as $key => $value) {
			if (!empty($value['PersonEvn_id'])) {

				$query = "
                    select
                        ER.Drug_id as \"ID\",
                        to_char(ER.EvnRecept_SetDate, 'dd.mm.yyyy') as \"DATE_ISSUE\",
                        VD.Drug_Name as \"DESCR\",
                        to_char(ER.EvnRecept_otpDT, 'dd.mm.yyyy') as \"DATE_DISP\"
                    from
                        v_EvnRecept ER
                        left join v_Drug VD on VD.Drug_id = ER.Drug_id
                        left join v_EvnDrug VED on VED.PersonEvn_id = ER.PersonEvn_id
                    where
                        ER.Person_id = :Person_id
                        and ER.Lpu_id = :Lpu_id
                        and ER.Diag_id = :Diag_id
                ";

				$result = $this->db->query($query, ['PersonEvn_id' => $value['PersonEvn_id'], 'Diag_id' => $value['Diag_id'], 'Lpu_id' => $data['Lpu_id'], 'Person_id' => $value['Person_id']]);

				if ( is_object($result) ) {
					$PERSON[$key]['ITEM'] = [];
					$res = $result->result('array');
					if (count($res) > 0) {
						foreach ($res as $row) {
							array_push($PERSON[$key]['ITEM'], $row);
						}
					} else {
						$PERSON[$key]['ITEM'] = [];
						array_push($PERSON[$key]['ITEM'], ['ID' => '', 'DATE_ISSUE' => '', 'DESCR' => '', 'DATE_DISP' => '']);
					}
				}
				else {
					return false;
				}
			} else {
				$PERSON[$key]['ITEM'] = [];
				array_push($PERSON[$key]['ITEM'], ['ID' => '', 'DATE_ISSUE' => '', 'DESCR' => '', 'DATE_DISP' => '']);
			}
		}

		//Делаем записи о выгрузке
		for ($i = 0; $i < count($PERSON); $i++) {

			$queryParams = [
				'PersonRegisterExport_id' => null,
				'PersonRegister_id' => $PERSON[$i]['PersonRegister_id'],
				'PersonRegisterExportType_id' => $data['ExportType'],
				'pmUser_id' => $data['pmUser_id']
			];

			$query = "
				select
					PersonRegisterExport_id as \"PersonRegisterExport_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from dbo.p_PersonRegisterExport_ins(
					PersonRegisterExport_id := :PersonRegisterExport_id,
					PersonRegisterExportType_id := :PersonRegisterExportType_id,
					PersonRegister_id := :PersonRegister_id,
					PersonRegisterExport_setDate := dbo.tzGetDate(),
					pmUser_id := :pmUser_id
				)
			";

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return false;
			}
		}

		$data['PERSON'] = $PERSON;

		return $data;
	}


}
