<?php
/**
* Utils - модель для вспомогательных операций
* 1. Объединение записей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      15.07.2009
*/

class Utils_model extends swModel
{
	/**
	 *	Конструктор
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Проверка, что все объединяемые участки имеют одинаковый тип
	 * не позволяем объединять участки разного типа
	 * ВОП позволяем объединять с любым участком, такой вот чит
	 *
	 * @param Array $records Записи пришедшие с клиента
	 */
	function CheckRegionType ($records) {
		$LpuRegions = array();
		foreach ($records as $record) {
			$LpuRegions[] = $record['Record_id'];
		}
		$query = "
			select 
				distinct LpuRegionType_id, 
				case when (LpuRegionType_id=4) then -1 else LpuRegionType_id end as field_sort
			from v_LpuRegion (nolock)
			where
				LpuRegion_id in (".implode(',', $LpuRegions).") --and LpuRegionType_id != 4
			order by
				field_sort
		";
		$result = $this->db->query($query);
		$response = $result->result('array');
		if ((count($response) == 1) || ((count($response) == 2) && ($response[0]['LpuRegionType_id']==4)))
			return true;
		else
			return false;
	}
	
	
	/**
	 * Проверка, что все объединяемые отделения имеют одинаковый уровень. 
	 * не позволяем объединять отделения с подотделениями
	 *
	 * @param Array $mainrec Главная запись
	 * @param Array $record Запись двойник
	 */
	function CheckSectionLevelEqual ($mainrec, $record) {
		$query = "
			select 
				LpuSection_pid
			from LpuSection (nolock)
			where
				LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query(
			$query,
			array(
				'LpuSection_id' => $mainrec['Record_id']
			)
		);
		$response = $result->result('array');
		if (is_array($response) && count($response) == 1)
			$pid1 = $response[0]['LpuSection_pid'];
		$result = $this->db->query(
			$query,
			array(
				'LpuSection_id' => $record['Record_id']
			)
		);
		$response = $result->result('array');
		if (is_array($response) && count($response) == 1)
			$pid2 = $response[0]['LpuSection_pid'];
		return 
			($pid1 == '' && $pid2 == '') || // оба простые отделения
			($pid1 != '' && $pid2 != ''); // оба подотделения
	}
	
	/**
	* Проверка является ли организация здешней (Server_id = 0)
	*/
	function isOurOrg($record)
        {
		$query = "
			select
				Server_id
			from
				v_Org (nolock)
			where
				Org_id = :Org_id
		";
		$result = $this->db->query($query, array(
			'Org_id' => $record['Record_id']
		));
		$response = $result->result('array');
		if($response[0]['Server_id'] == 0)
			return true;
		else
			return false;
	}
	
	/**
	* Проводит объединение записей в заданной таблице
	*/
	function doRecordUnion($data)
	{
		$records = $data['Records'];
		
		// Для участков отдельная логика, 
		// сначала проверяем, что они имеют одинаковый тип
		if ($data['Table'] == 'LpuRegion' ) {
			if (!$this->CheckRegionType($records)) {
				return array(0 => array('Error_Msg' => 'Объединяемые участки должны иметь одинаковый тип.'));
			}
		}
		//Определяем главную запись, 
		foreach ($records as $record)
		{
			if ($record['IsMainRec']==1) {
				//сохраняем ее отдельно
				$mainrec = $record;
				break;
			}
		}
		//Проходим по всем остальным записям
		foreach ($records as $record)
		{
			//Если это не главная запись
			if ($record['IsMainRec']!=1) {
				
				if ($data['Table'] == 'LpuSection' ) {
					// Проверка равенства уровня для отделений. Подотделения нельзя объединять с отделениями.
					if (!$this->CheckSectionLevelEqual($mainrec, $record)) {
						return array(0 => array('Error_Msg' => 'Нельзя объединять отделения с подотделениями.'));
					}
				}
				
				if ( $data['Table'] == 'MedPersonal' ) {
					// для медперсонала используем отдельную хранимку
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec xp_MedpersonalMerge
							@Medpersonal_id = :MedPersonal_id,
							@Medpersonal_did = :MedPersonal_did,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query(
						$query,
						array(
							'MedPersonal_id' => $mainrec['Record_id'],
							'MedPersonal_did' => $record['Record_id'],
							'pmUser_id' => $data['pmUser_id']
						)
					);
				}
				else {
					if ( $data['Table'] == 'MedStaffFact' ) {
						// Для мест работы врача используем дополнительную логику, удаляем из таблицы MedPersonalDay данные по объединяемой записи
						// Изменено на хранимку в связи с задачей #5798, так как прямое удаление выдаёт ошибку
						$query = "
							exec p_MedPersonalDay_delByMedPersonal @MedStaffFact_id = :MedStaffFact_id
						";
						$result = $this->db->query(
							$query,
							array(
								'MedStaffFact_id' => $record['Record_id']
							)
						);
					}

					// Если объект МО, то статус 4. В очереди, иначе 1. Новое 
					if (in_array($data['Table'], array('LpuBuilding', 'LpuUnit', 'LpuSection', 'LpuRegion', 'MedService',
						'Contragent',
						'Org',
						'OrgAnatom',
						'OrgBank',
						'OrgDep',
						'OrgFarmacy',
						'OrgFarmacyIndex',
						'OrgFarmacyOmsSprTerr',
						'OrgFarmacyPerson',
						'OrgHead',
						'OrgHeadPost',
						'OrgInfo',
						'OrgInfoType',
						'OrgLicence',
						'OrgLpuUfa',
						'OrgMilitary',
						'OrgProducer',
						'OrgRSchet',
						'OrgRSchetKBK',
						'OrgRSchetType',
						'OrgServiceTerr',
						'OrgServiceType',
						'OrgSMO',
						'OrgSmoData',
						'OrgSMOFilial',
						'OrgSmoLink',
						'OrgSmoMeasures',
						'OrgStac',
						'OrgStruct',
						'OrgStructLevelType',
						'OrgType',
						'OrgUnion',
						'OrgWorkPeriod'
						))) {
						$ObjectMergeStatus_id = 4;
					} else {
						$ObjectMergeStatus_id = 1;
					}
					
					if( $data['Table'] == 'Org' ) {
						if(getRegionNick() != 'kareliya' && ($record['OrgType_id'] == '11' || $mainrec['OrgType_id'] == '11')) {
							return array(0 => array('Error_Msg' => 'Запрещено объединять медицинские организации.'));
						}

						//Нельзя объединять организации разных типов
						$query = "
							select
								ISNULL(OT.OrgType_SysNick, '') as OrgType_SysNick
							from
								v_Org O (nolock)
								left join v_OrgType OT with (nolock) on O.OrgType_id = OT.OrgType_id
							where
								Org_id in ({$mainrec['Record_id']}, {$record['Record_id']})
						";

						$result = $this->queryResult($query, array());

						if (is_array($result) && count($result) == 2){
							if ($result[0]['OrgType_SysNick'] != $result[1]['OrgType_SysNick']){
								return array(0 => array('Error_Msg' => 'Нельзя объединять организации разных типов.'));
							}
						} else {
							return array(0 => array('Error_Msg' => 'Ошибка при проверке типов объединяемых организаций.'));
						}

						switch($result[0]['OrgType_SysNick']){
							case 'anatom':
								$data['Table_sub'] = 'OrgAnatom';
							break;
							case 'farm':
								$data['Table_sub'] = 'OrgFarmacy';
							break;
							case 'bank':
								$data['Table_sub'] = 'OrgBank';
							break;
							case 'smo':
								$data['Table_sub'] = 'OrgSMO';
							break;
							/*case 'military':
								$data['Table_sub'] = 'OrgMilitary';
							break;*/
							default:
								$data['Table_sub'] = 'Org';
							break;
						}

						if ($data['Table_sub'] != 'Org') {
							//Получаем идентификаторы из вновь определённой таблицы, если это не org
							$mainrec_sub['Record_id'] = $this->getFirstResultFromQuery("select top 1 {$data['Table_sub']}_id from {$data['Table_sub']} where Org_id = {$mainrec['Record_id']}");
							$record_sub['Record_id'] = $this->getFirstResultFromQuery("select top 1 {$data['Table_sub']}_id from {$data['Table_sub']} where Org_id = {$record['Record_id']}");

							//Если нашлись такие организации в побочных таблицах то объединяем их
							if (!empty($mainrec_sub['Record_id']) && !empty($record_sub['Record_id'])) {
								//return array(0 => array('Error_Msg' => 'Одна из организаций отсутствует в зависимой таблице. Объединение невозможно. Обратитесь к разработчикам программы.'));

								//Вызываем хранимую процедуру для объединения на сервере с заданными параметрами
								$query = "
									declare
										@ErrCode int,
										@ErrMessage varchar(4000);
									exec od.p_ObjectMerge_ins
										@ObjectMerge_id = :ObjectMerge_id,
										@ObjectMerge_Name = :Table,
										@ObjectMergeStatus_id = :ObjectMergeStatus_id,
										@Object_id = :Object_id,
										@Object_did = :Object_did,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
									select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								";

								$result = $this->db->query(
									$query,
									array(
										'ObjectMerge_id' => NULL,
										'Table' => $data['Table_sub'],
										'ObjectMergeStatus_id' => $ObjectMergeStatus_id,
										'Object_id' => $mainrec_sub['Record_id'],
										'Object_did' => $record_sub['Record_id'],
										'pmUser_id' => $data['pmUser_id']
									)
								);

								//Если возвращается ошибка, то выдаем пользователю и выходим
								if (!is_object($result))
								{
									return array(0 => array('Error_Msg' => 'Ошибка при попытке объединения записей в побочной таблице.'));
								}

								$response = $result->result('array');

								if (!is_array($response) || count($response) == 0)
								{
									return array(0 => array('Error_Msg' => 'Ошибка при объединении записей'));
								}
								else if (!empty($response[0]['Error_Msg']))
								{
									return $response;
								}
							}
						}
					}
					
					//Вызываем хранимую процедуру для объединения на сервере с заданными параметрами
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec od.p_ObjectMerge_ins
							@ObjectMerge_id = :ObjectMerge_id,
							@ObjectMerge_Name = :Table,
							@ObjectMergeStatus_id = :ObjectMergeStatus_id,
							@Object_id = :Object_id,
							@Object_did = :Object_did,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query(
						$query,
						array(
							'ObjectMerge_id' => NULL,
							'Table' => $data['Table'],
							'ObjectMergeStatus_id' => $ObjectMergeStatus_id,
							'Object_id' => $mainrec['Record_id'],
							'Object_did' => $record['Record_id'],
							'pmUser_id' => $data['pmUser_id']
						)
					);
				}
				
				//Если возвращается ошибка, то выдаем пользователю и выходим
				if (!is_object($result))
				{
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (объединение)'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при объединении записей'));
				}
				else if (strlen($response[0]['Error_Msg']) > 0)
				{
					return $response;
				}
			}
		}
		return array(0 => array('Object_id' => $mainrec['Record_id'], 'Error_Msg' => ''));
	}

	/**
	 * Проверка записей для объединения
	 */
	function checkRecordsForUnion($data) {
		$response = array(array('success' => true));

		$id_list = array();
		$main_record = null;
		$minor_records = array();

		foreach($data['Records'] as $record) {
			if(!empty($record['Record_id'])){
				$id_list[] = $record['Record_id'];
			}
			if ($record['IsMainRec']) {
				$main_record = $record['Record_id'];
			} else {
				$minor_records[] = $record['Record_id'];
			}
		}

		if(is_array($id_list) && count($id_list)>0){
			$id_list_str = implode(',', $id_list);

			switch($data['Table']) {
				case 'LpuSection':
					$LpuSection = $this->queryResult("
						select
							LU.LpuUnitType_id
						from
							v_LpuSection_all LS with(nolock)
							inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						where LS.LpuSection_id in ($id_list_str)
					");
					if (!is_array($LpuSection) || count($LpuSection) != 2) {
						return $this->createError('','Ошибка при получении данных выбранных отделений');
					}
					if ($LpuSection[0]['LpuUnitType_id'] != $LpuSection[1]['LpuUnitType_id']) {
						return $this->createError('','Объединяемые отделения должны быть в группах отделений одинакового типа');
					}

					//Проверка наличия записей в системе учета движения медикаментов в МО
					$query = "select top 1 count(*) from v_Contragent with(nolock) where LpuSection_id = :LpuSection_id";
					$params = array('LpuSection_id' => $minor_records[0]);
					$count = $this->getFirstResultFromQuery($query, $params);
					if ($count === false) {
						return $this->createError('','Ошибка при получении количество записей контрагентов, связанных с объединяемым отделением');
					}
					if ($count > 0) {
						return $this->createError('','Объединение отделений не возможно, т.к. присоединяемое отделение включено в систему учета движения медикаментов в МО');
					}

					$query = "
						declare @id bigint = :LpuSection_id
						select (
							select top 1 count(*) 
							from v_Contragent with(nolock) 
							where LpuSection_id = @id
						) + (
							select top 1 count(*)
							from v_Storage S with(nolock)
							where exists (
								select * from v_StorageStructLevel SSL with(nolock)
								left join v_MedService MS with(nolock) on MS.MedService_id = SSL.MedService_id
								where SSL.Storage_id = S.Storage_id 
								and @id in (SSL.LpuSection_id, MS.LpuSection_id)
							)
							and exists (
								select * from v_DocumentUc DU with(nolock)
								where S.Storage_id in (DU.Storage_sid, DU.Storage_tid)
							)
						) as cnt
					";
					$params = array('LpuSection_id' => $minor_records[0]);
					$count = $this->getFirstResultFromQuery($query, $params);
					if ($count === false) {
						return $this->createError('','Ошибка при получении количество записей, связанных с объединяемым отделением');
					}
					if ($count > 0) {
						return $this->createError('','Объединение отделений не возможно, т.к. присоединяемое отделение включено в систему учета движения медикаментов в МО');
					}
					break;
			}
		}

		return $response;
	}

	/**
	 * Получение списка связанных объектов
	 */
	function loadLinkObjectList($data) {
		$where = "";
		$params = array('Object_Name' => $data['Object_Name']);

		if (!empty($data['ColumnList'])) {
			$column_list_str = "'".implode("','", $data['ColumnList'])."'";
			$where .= " and exists(
				select * from v_Columns with(nolock)
				where Schema_Name = schema_name(fo.uid) and Table_Name = fo.name
				and Column_Name in ($column_list_str)
			)";
		}
		if (!empty($data['LinkObjectList'])) {
			$link_object_list_str = "'".implode("','", $data['LinkObjectList'])."'";
			$where .= " and fo.name in ({$link_object_list_str})";
		}

		$query = "
			select
				fo.name as LinkObject_Name,
				nc.name as LinkObject_Column,
				schema_name(fo.uid) as LinkObject_Schema
			from
				sysforeignkeys k with(nolock)
				inner join sysobjects ro with(nolock) on(ro.id = k.rkeyid)
				inner join sysobjects fo with(nolock) on(fo.id = k.fkeyid)
				left outer join syscolumns nc with(nolock) on (nc.id = k.fkeyid) and (nc.colorder = k.fkey)
			where
				ro.Name = :Object_Name
				{$where}
			order by
				LinkObject_Schema,
				LinkObject_Name,
				LinkObject_Column
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение настроек для объединения записей
	 */
	function getRecordUnionSettings($data) {
		$table = $data['Table'];
		$mainRecord = json_decode($data['mainRecord'], true);
		$minorRecord = json_decode($data['minorRecord'], true);

		$subObjectList = array();
		switch($table) {
			case 'LpuSection':
				$subObjectList = array(
					'LpuSectionTariff',
					'LpuSectionFinans',
					'LpuSectionLicence',
					'UslugaComplexPlace',
					'LpuSectionWard',
					'LpuSectionBedState',
					'UslugaComplexTariff'
				);
				break;
		}

		$settings = array();
		foreach($subObjectList as $subObject) {
			$settings[$subObject] = array(
				'title' => '',
				'description' => '',
				'hasIntersection' => false,
				'hasForeignKey' => false,
				'allowChangeMainRecord' => false
			);

			$query = '';
			switch($subObject) {
				case 'LpuSectionTariff':
					$settings[$subObject]['title'] = 'Тарифы отделения';
					$settings[$subObject]['description'] = "Если объединяемые записи полностью попадают в период действия главных записей, то копирование происходить не будет. Если есть временные промежутки, в которых нет пересечения объединяемых записей с главными, то создадутся новые записи, попадающие по датам открытия и закрытия в эти промежутки";
					$settings[$subObject]['allowChangeMainRecord'] = true;

					$query = "
						select top 1 COUNT(t1.LpuSectionTariff_id) as Count
						from v_LpuSectionTariff t1 with(nolock)
						where t1.LpuSection_id = :LpuSection1_id
						and exists (
							select t2.LpuSectionTariff_id
							from v_LpuSectionTariff t2 with(nolock)
							where t2.LpuSection_id = :LpuSection2_id
							and t2.TariffClass_id = t1.TariffClass_id
							and t2.LpuSectionTariff_setDate <= isnull(t1.LpuSectionTariff_disDate, t2.LpuSectionTariff_setDate)
							and isnull(t2.LpuSectionTariff_disDate, t1.LpuSectionTariff_setDate) >= t1.LpuSectionTariff_setDate
						)
					";
					break;

				case 'LpuSectionFinans':
					$settings[$subObject]['title'] = 'Финансирование';
					$settings[$subObject]['description'] = "Если объединяемые записи полностью попадают в период действия главных записей, то копирование происходить не будет. Если есть временные промежутки, в которых нет пересечения объединяемых записей с главными, то создадутся новые записи, попадающие по датам открытия и закрытия в эти промежутки";
					$settings[$subObject]['allowChangeMainRecord'] = true;

					$query = "
						select top 1 COUNT(t1.LpuSectionFinans_id) as Count
						from v_LpuSectionFinans t1 with(nolock)
						where t1.LpuSection_id = :LpuSection1_id
						and exists (
							select t2.LpuSectionFinans_id
							from v_LpuSectionFinans t2 with(nolock)
							where t2.LpuSection_id = :LpuSection2_id
							and t2.PayType_id = t1.PayType_id
							and t2.LpuSectionFinans_begDate <= isnull(t1.LpuSectionFinans_endDate, t2.LpuSectionFinans_begDate)
							and isnull(t2.LpuSectionFinans_endDate, t1.LpuSectionFinans_begDate) >= t1.LpuSectionFinans_begDate
						)
					";
					break;

				case 'LpuSectionLicence':
					$settings[$subObject]['title'] = 'Лицензии';
					$settings[$subObject]['description'] = "Создается на объединенном отделении единая запись, объединяющая периоды действия лицензий";
					$settings[$subObject]['allowChangeMainRecord'] = true;

					$query = "
						select top 1 COUNT(t1.LpuSectionLicence_id) as Count
						from v_LpuSectionLicence t1 with(nolock)
						where t1.LpuSection_id = :LpuSection1_id
						and exists (
							select t2.LpuSectionLicence_id
							from v_LpuSectionLicence t2 with(nolock)
							where t2.LpuSection_id = :LpuSection2_id
							and t2.LpuSectionLicence_Num = t1.LpuSectionLicence_Num
							and t2.LpuSectionLicence_begDate <= isnull(t1.LpuSectionLicence_endDate, t2.LpuSectionLicence_begDate)
							and isnull(t2.LpuSectionLicence_endDate, t1.LpuSectionLicence_begDate) >= t1.LpuSectionLicence_begDate
						)
					";
					break;

				case 'UslugaComplexPlace':
					$settings[$subObject]['title'] = 'Услуги в отделении';
					$settings[$subObject]['description'] = "Услуги с пересечениями не переносятся";
					$settings[$subObject]['allowChangeMainRecord'] = false;

					$query = "
						select top 1 COUNT(t1.UslugaComplexPlace_id) as Count
						from v_UslugaComplexPlace t1 with(nolock)
						where t1.LpuSection_id = :LpuSection1_id
						and exists (
							select t2.UslugaComplexPlace_id
							from v_UslugaComplexPlace t2 with(nolock)
							where t2.LpuSection_id = :LpuSection2_id
							and t2.UslugaComplex_id = t1.UslugaComplex_id
							and t2.UslugaComplexPlace_begDT <= isnull(t1.UslugaComplexPlace_endDT, t2.UslugaComplexPlace_begDT)
							and isnull(t2.UslugaComplexPlace_endDT, t1.UslugaComplexPlace_begDT) >= t1.UslugaComplexPlace_begDT
						)
					";
					break;

				case 'LpuSectionWard':
					$settings[$subObject]['title'] = 'Палатная структура';
					break;

				case 'LpuSectionBedState':
					$settings[$subObject]['title'] = 'Койки по профилю';
					$settings[$subObject]['description'] = "Койки по профилю переносятся с заменой профиля койки на профиль доступный в главном отделении. Если объединяемые записи полностью попадают в период действия главных записей, то копирование происходить не будет. Если есть временные промежутки, в которых нет пересечения объединяемых записей с главными, то создадутся новые записи, попадающие по датам открытия и закрытия в эти промежутки. По умолчанию за главную запись коек принимать записи главного отделения";
					$settings[$subObject]['allowChangeMainRecord'] = false;

					$query = "
						select top 1 COUNT(t1.LpuSectionBedState_id) as Count
						from v_LpuSectionBedState t1 with(nolock)
						where t1.LpuSection_id = :LpuSection1_id
						and exists (
							select t2.LpuSectionBedState_id
							from v_LpuSectionBedState t2 with(nolock)
							where t2.LpuSection_id = :LpuSection2_id
							and t2.LpuSectionBedState_begDate <= isnull(t1.LpuSectionBedState_endDate, t2.LpuSectionBedState_begDate)
							and isnull(t2.LpuSectionBedState_endDate, t1.LpuSectionBedState_begDate) >= t1.LpuSectionBedState_begDate
						)
					";
					break;

				case 'UslugaComplexTariff':
					$settings[$subObject]['title'] = 'Тариф на услугу в отделении';
					$settings[$subObject]['description'] = "Если объединяемые записи полностью попадают в период действия главных записей, то копирование происходить не будет. Если есть временные промежутки, в которых нет пересечения объединяемых записей с главными, то создадутся новые записи, попадающие по датам открытия и закрытия в эти промежутки";
					$settings[$subObject]['allowChangeMainRecord'] = true;

					$query = "
						select top 1 COUNT(t1.UslugaComplexTariff_id) as Count
						from v_UslugaComplexTariff t1 with(nolock)
						where t1.LpuSection_id = :LpuSection1_id
						and exists (
							select t2.UslugaComplexTariff_id
							from v_UslugaComplexTariff t2 with(nolock)
							where t2.LpuSection_id = :LpuSection2_id
							and t2.UslugaComplexTariff_begDate <= isnull(t1.UslugaComplexTariff_endDate, t2.UslugaComplexTariff_begDate)
							and isnull(t2.UslugaComplexTariff_endDate, t1.UslugaComplexTariff_begDate) >= t1.UslugaComplexTariff_begDate
							and t2.UslugaComplex_id = t1.UslugaComplex_id
							and t2.UslugaComplexTariffType_id = t1.UslugaComplexTariffType_id
							and isnull(t2.LpuSectionProfile_id,0) = isnull(t1.LpuSectionProfile_id,0)
							and isnull(t2.PayType_id,0) = isnull(t1.PayType_id,0)
							and isnull(t2.UslugaComplexTariff_UED,0) = isnull(t1.UslugaComplexTariff_UED,0)
							and isnull(t2.UslugaComplexTariff_UEM,0) = isnull(t1.UslugaComplexTariff_UEM,0)
							and isnull(t2.LpuUnitType_id,0) = isnull(t1.LpuUnitType_id,0)
							and isnull(t2.Sex_id,0) = isnull(t1.Sex_id,0)
							and isnull(t2.MesAgeGroup_id,0) = isnull(t1.MesAgeGroup_id,0)
							and isnull(t2.VizitClass_id,0) = isnull(t1.VizitClass_id,0)
							and isnull(t2.UslugaComplexTariff_Code,0) = isnull(t1.UslugaComplexTariff_Code,0)
							and isnull(t2.UslugaComplexTariff_Name,0) = isnull(t1.UslugaComplexTariff_Name,0)
						)
					";
					break;

				default:
					return false;
			}

			if (!empty($query)) {
				$count = $this->getFirstResultFromQuery($query, array(
					'LpuSection1_id' => $mainRecord['Record_id'],
					'LpuSection2_id' => $minorRecord['Record_id']
				));
				if ($count > 0) {
					$settings[$subObject]['hasIntersection'] = true;
				}
			}

			$resp = $this->loadLinkObjectList(array('Object_Name' => $subObject));
			if (count($resp) > 0) {
				$query_union = array();
				foreach($resp as $item) {
					$query_union[] = "
						select top 1 count(t.{$subObject}_id) as Count
						from {$item['LinkObject_Schema']}.{$item['LinkObject_Name']} t
						where exists(
							select so.{$subObject}_id
							from v_{$subObject} so with(nolock)
							where so.{$subObject}_id = t.{$subObject}_id
							and so.LpuSection_id in (:LpuSection1_id,:LpuSection2_id)
						)
					";
				}
				$query = "
					select top 1 sum(Count) as Count
					from (
						".implode("union",$query_union)."
					) t
				";
				/*echo getDebugSQL($query, array(
					'LpuSection1_id' => $mainRecord['Record_id'],
					'LpuSection2_id' => $minorRecord['Record_id']
				));exit;*/
				$count = $this->getFirstResultFromQuery($query, array(
					'LpuSection1_id' => $mainRecord['Record_id'],
					'LpuSection2_id' => $minorRecord['Record_id']
				));
				if ($count > 0) {
					$settings[$subObject]['hasForeignKey'] = true;
				}
			}
		}

		return array('success' => true, 'Error_Msg' => '', 'settings' => $settings);
	}

	/**
	 * Объединение записей с настройками
	 */
	function doRecordUnionWithSettings($data) {
		set_time_limit(0);
		$response = $this->createError('',"Отсутсвует метод для объединения объектов {$data['Table']}");
		switch($data['Table']) {
			case 'LpuSection':
				$response = $this->doLpuSectionUnion($data);
				break;
		}
		return $response;
	}

	/**
	 * Объединение отделений
	 */
	function doLpuSectionUnion($data) {
		/**
		 * При возникновении ошибок выкидывать исключения
		 */
		function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
			switch ($errno) {
				case E_NOTICE:
				case E_USER_NOTICE:
					$errors = "Notice";
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$errors = "Warning";
					break;
				case E_ERROR:
				case E_USER_ERROR:
					$errors = "Fatal Error";
					break;
				default:
					$errors = "Unknown Error";
					break;
			}

			$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
			throw new ErrorException($msg, 0, $errno, $errfile, $errline);
		}

		$table = $data['Table'];
		$mainRecord = json_decode($data['mainRecord'], true);
		$minorRecord = json_decode($data['minorRecord'], true);
		$settings = json_decode($data['settings'], true);

		$resp = $this->queryResult("
			select
				LpuSection_id,
				LpuUnit_id,
				LpuBuilding_id,
				Lpu_id,
				LpuSectionProfile_id
			from v_LpuSection with(nolock)
			where LpuSection_id in ({$mainRecord['Record_id']},{$minorRecord['Record_id']})
		");
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении данных объединяемых отделений');
		}
		$LpuSectionParams = array();
		foreach($resp as $item) {
			$key = (string)$item['LpuSection_id'];
			$LpuSectionParams[$key] = $item;
		}

		$resp = $this->loadLinkObjectList(array('Object_Name' => $table));
		$mainLinks = array();
		foreach($resp as $item) {
			$key = "{$item['LinkObject_Schema']}.{$item['LinkObject_Name']}";
			$mainLinks[$key] = $item;
		}

		$this->load->helper('RecordUnion');
		$this->beginTransaction();
		$this->isAllowTransaction = false;
		try{
			set_error_handler('exceptionErrorHandler');
			foreach($settings as $subObject => $setting) {
				$main_id = (string)$mainRecord['Record_id'];
				if (!empty($setting['selectMainRecord']) && $setting['selectMainRecord']==2) {
					$main_id = (string)$minorRecord['Record_id'];
				}
				switch($subObject) {
					case 'LpuSectionTariff':
						$query = "
							declare @bigdate date = dateadd(year, 50, dbo.tzGetDate())
							select
								LST.*,
								LpuSectionTariff_id as old_id,
								LpuSectionTariff_id as new_id,
								LpuSectionTariff_setDate as begDate,
								LpuSectionTariff_disDate as endDate,
								case when LpuSection_id = :main_id then 1 else 0 end as isMain,
								1 as RecordStatus_Code
							from v_LpuSectionTariff LST with(nolock)
							where LpuSection_id in ({$mainRecord['Record_id']},{$minorRecord['Record_id']})
							order by
								TariffClass_id,
								LpuSectionTariff_setDate,
								isnull(LpuSectionTariff_disDate,@bigdate),
								isMain desc
						";
						$records = $this->queryResult($query, array('main_id' => $main_id));
						$condition = function($currRecord, $checkRecord) {
							return ($checkRecord['isMain'] && $checkRecord['TariffClass_id'] == $currRecord['TariffClass_id']);
						};
						if ($setting['removeIntersection']) {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$scope->modifyPeriod();
									$scope->createNextPeriods();
								}
							};
						} else {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$curr['RecordStatus_Code'] = 2;
									$scope->setRecord($currIndex, $curr);
								}
							};
						}

						$linkObjects = $this->loadLinkObjectList(array(
							'Object_Name' => $subObject,
							'ColumnList' => array('LpuSection_id')
						));
						$getLinks = function($model, $curr) use($linkObjects) {
							$links = array();
							foreach($linkObjects as $linkObject) {
								$links[] = array_merge($linkObject, array(
									'new_id' => $curr['new_id'],
									'old_id' => $curr['old_id'],
									'additSet' => array('LpuSection_id' => $curr['LpuSection_id']),
								));
							}
							return $links;
						};

						$this->load->model('LpuStructure_model');
						$options = array(
							'idField' => 'LpuSectionTariff_id',
							'begDateField' => 'LpuSectionTariff_setDate',
							'endDateField' => 'LpuSectionTariff_disDate',
							'pmUser_id' => $data['pmUser_id'],
							'ins' => function($model, $curr) {
								return $model->LpuStructure_model->SaveLpuSectionTariff($curr);
							},
							'upd' => function($model, $curr) {
								return $model->LpuStructure_model->SaveLpuSectionTariff($curr);
							},
							'del' => function($model, $curr) use($data) {
								return $model->ObjectRecordDelete($data, 'LpuSectionTariff', false, $curr['LpuSectionTariff_id']);
							},
							'getLinks' => $getLinks
						);

						$mainParams = $LpuSectionParams[(string)$mainRecord['Record_id']];
						$RecordUnion = new RecordUnion($this, $records, $mainParams, $mainLinks, $condition);
						$result = $RecordUnion->processRecords($processor)->doUnion($options);
						if (!is_array($result)) {
							throw new Exception("Ошибка при переносе объекта {$subObject}");
						}
						if (!$this->isSuccessful($result)) {
							throw new Exception($result[0]['Error_Msg']);
						}
						break;

					case 'LpuSectionFinans':
						$query = "
							declare @bigdate date = dateadd(year, 50, dbo.tzGetDate())
							select
								LSF.*,
								LpuSectionFinans_id as old_id,
								LpuSectionFinans_id as new_id,
								LpuSectionFinans_begDate as begDate,
								LpuSectionFinans_endDate as endDate,
								case when LpuSection_id = :main_id then 1 else 0 end as isMain,
								1 as RecordStatus_Code
							from v_LpuSectionFinans LSF with(nolock)
							where LpuSection_id in ({$mainRecord['Record_id']},{$minorRecord['Record_id']})
							order by
								PayType_id,
								LpuSectionFinans_begDate,
								isnull(LpuSectionFinans_endDate,@bigdate),
								isMain desc
						";
						$records = $this->queryResult($query, array('main_id' => $main_id));
						$condition = function($currRecord, $checkRecord) {
							return ($checkRecord['isMain'] && $checkRecord['PayType_id'] == $currRecord['PayType_id']);
						};
						if ($setting['removeIntersection']) {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$scope->modifyPeriod();
									$scope->createNextPeriods();
								}
							};
						} else {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$curr['RecordStatus_Code'] = 2;
									$scope->setRecord($currIndex, $curr);
								}
							};
						}

						$linkObjects = $this->loadLinkObjectList(array(
							'Object_Name' => $subObject,
							'ColumnList' => array('LpuSection_id')
						));
						$getLinks = function($model, $curr) use($linkObjects) {
							$links = array();
							foreach($linkObjects as $linkObject) {
								$links[] = array_merge($linkObject, array(
									'new_id' => $curr['new_id'],
									'old_id' => $curr['old_id'],
									'additSet' => array('LpuSection_id' => $curr['LpuSection_id']),
								));
							}
							return $links;
						};

						$this->load->model('LpuStructure_model');
						$options = array(
							'idField' => 'LpuSectionFinans_id',
							'begDateField' => 'LpuSectionFinans_begDate',
							'endDateField' => 'LpuSectionFinans_endDate',
							'pmUser_id' => $data['pmUser_id'],
							'ins' => function($model, $curr) {
								return $model->LpuStructure_model->SaveLpuSectionFinans($curr);
							},
							'upd' => function($model, $curr) {
								return $model->LpuStructure_model->SaveLpuSectionFinans($curr);
							},
							'del' => function($model, $curr) use($data) {
								return $model->ObjectRecordDelete($data, 'LpuSectionFinans', false, $curr['LpuSectionFinans_id']);
							},
							'getLinks' => $getLinks
						);

						$mainParams = $LpuSectionParams[(string)$mainRecord['Record_id']];
						$RecordUnion = new RecordUnion($this, $records, $mainParams, $mainLinks, $condition);
						$result = $RecordUnion->processRecords($processor)->doUnion($options);
						if (!is_array($result)) {
							throw new Exception("Ошибка при переносе объекта {$subObject}");
						}
						if (!$this->isSuccessful($result)) {
							throw new Exception($result[0]['Error_Msg']);
						}
						break;

					case 'LpuSectionLicence':
						$query = "
							declare @bigdate date = dateadd(year, 50, dbo.tzGetDate())
							select
								LSL.*,
								LpuSectionLicence_id as old_id,
								LpuSectionLicence_id as new_id,
								LpuSectionLicence_begDate as begDate,
								LpuSectionLicence_endDate as endDate,
								case when LpuSection_id = :main_id then 1 else 0 end as isMain,
								1 as RecordStatus_Code
							from v_LpuSectionLicence LSL with(nolock)
							where LpuSection_id in ({$mainRecord['Record_id']},{$minorRecord['Record_id']})
							order by
								LpuSectionLicence_Num,
								LpuSectionLicence_begDate,
								ISNULL(LSL.LpuSectionLicence_endDate,@bigdate),
								isMain desc
						";
						$records = $this->queryResult($query, array('main_id' => $main_id));
						$condition = function($currRecord, $checkRecord) {
							return (!$checkRecord['isMain'] && $checkRecord['LpuSectionLicence_Num'] == $currRecord['LpuSectionLicence_Num']);
						};
						if ($setting['removeIntersection']) {
							$processor = function($scope, $currIndex, $curr) {
								if ($curr['isMain']) {
									$scope->unionPeriods();
								}
							};
						} else {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$curr['RecordStatus_Code'] = 2;
									$scope->setRecord($currIndex, $curr);
								}
							};
						}

						$linkObjects = $this->loadLinkObjectList(array('Object_Name' => $subObject));
						$this->load->model('LpuStructure_model');
						$options = array(
							'idField' => 'LpuSectionLicence_id',
							'begDateField' => 'LpuSectionLicence_begDate',
							'endDateField' => 'LpuSectionLicence_endDate',
							'pmUser_id' => $data['pmUser_id'],
							'ins' => function($model, $curr) {
								return $model->LpuStructure_model->SaveLpuSectionLicence($curr);
							},
							'upd' => function($model, $curr) {
								return $model->LpuStructure_model->SaveLpuSectionLicence($curr);
							},
							'del' => function($model, $curr) use($data) {
								return $model->ObjectRecordDelete($data, 'LpuSectionLicence', false, $curr['LpuSectionLicence_id']);
							},
							'getLinks' => function($model, $curr) use($linkObjects) {
								foreach($linkObjects as $linkObject) {
									$links[] = array_merge($linkObject, array(
										'new_id' => $curr['new_id'],
										'old_id' => $curr['old_id']
									));
								}
							}
						);

						$mainParams = $LpuSectionParams[(string)$mainRecord['Record_id']];
						$RecordUnion = new RecordUnion($this, $records, $mainParams, $mainLinks, $condition);
						$result = $RecordUnion->processRecords($processor)->doUnion($options);
						if (!is_array($result)) {
							throw new Exception("Ошибка при переносе объекта {$subObject}");
						}
						if (!$this->isSuccessful($result)) {
							throw new Exception($result[0]['Error_Msg']);
						}
						break;

					case 'UslugaComplexPlace':
						$query = "
							declare @bigdate date = dateadd(year, 50, dbo.tzGetDate());
							select
								UCP.*,
								UslugaComplexPlace_id as old_id,
								UslugaComplexPlace_id as new_id,
								UslugaComplexPlace_begDT as begDate,
								UslugaComplexPlace_endDT as endDate,
								case when LpuSection_id = :main_id then 1 else 0 end as isMain,
								1 as RecordStatus_Code
							from v_UslugaComplexPlace UCP with(nolock)
							where UCP.LpuSection_id in ({$mainRecord['Record_id']},{$minorRecord['Record_id']})
							order by
								UslugaComplex_id,
								UslugaComplexPlace_begDT,
								ISNULL(UCP.UslugaComplexPlace_endDT,@bigdate),
								isMain desc
						";
						$records = $this->queryResult($query, array('main_id' => $main_id));
						$condition = function($currRecord, $checkRecord) {
							return ($checkRecord['isMain'] && $checkRecord['UslugaComplex_id'] == $currRecord['UslugaComplex_id']);
						};
						if ($setting['removeIntersection']) {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$prev = $scope->getRecord($scope->getPrevIndex());
									$next = $scope->getRecord($scope->getNextIndex());

									if ($prev && (!$prev['endDate'] || $curr['begDate'] <= $prev['endDate'])) {
										$curr['RecordStatus_Code'] = 3;
									} else if ($next && (!$curr['endDate'] || $next['begDate'] <= $curr['endDate'])) {
										$curr['RecordStatus_Code'] = 3;
									} else {
										$curr['RecordStatus_Code'] = 2;
									}
									$scope->setRecord($currIndex, $curr);
								}
							};
						} else {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$curr['RecordStatus_Code'] = 2;
									$scope->setRecord($currIndex, $curr);
								}
							};
						}

						$linkObjects = $this->loadLinkObjectList(array(
							'Object_Name' => $subObject,
							'ColumnList' => array('LpuSection_id')
						));
						$getLinks = function($model, $curr) use($linkObjects) {
							$links = array();
							foreach($linkObjects as $linkObject) {
								$links[] = array_merge($linkObject, array(
									'new_id' => $curr['new_id'],
									'old_id' => $curr['old_id'],
									'additSet' => array('LpuSection_id' => $curr['LpuSection_id']),
								));
							}
							return $links;
						};

						$this->load->model('UslugaComplex_model');
						$options = array(
							'idField' => 'UslugaComplexPlace_id',
							'begDateField' => 'UslugaComplexPlace_begDate',
							'endDateField' => 'UslugaComplexPlace_endDate',
							'pmUser_id' => $data['pmUser_id'],
							'ins' => function($model, $curr) {
								return $model->UslugaComplex_model->saveUslugaComplexPlace($curr);
							},
							'upd' => function($model, $curr) {
								return $model->UslugaComplex_model->saveUslugaComplexPlace($curr);
							},
							'del' => function($model, $curr) {
								return $model->UslugaComplex_model->deleteUslugaComplexPlace($curr);
							},
							'getLinks' => $getLinks
						);

						$mainParams = $LpuSectionParams[(string)$mainRecord['Record_id']];
						$RecordUnion = new RecordUnion($this, $records, $mainParams, $mainLinks, $condition);
						$result = $RecordUnion->processRecords($processor)->doUnion($options);
						if (!is_array($result)) {
							throw new Exception("Ошибка при переносе объекта {$subObject}");
						}
						if (!$this->isSuccessful($result)) {
							throw new Exception($result[0]['Error_Msg']);
						}
						break;

					case 'LpuSectionBedState':
						$query = "
							declare @bigdate date = dateadd(year, 50, dbo.tzGetDate())
							select
								LSBS.*,
								LpuSectionBedState_id as old_id,
								LpuSectionBedState_id as new_id,
								LpuSectionBedState_begDate as begDate,
								LpuSectionBedState_endDate as endDate,
								case when LpuSection_id = :main_id then 1 else 0 end as isMain,
								1 as RecordStatus_Code
							from LpuSectionBedState LSBS with(nolock)
							where LpuSection_id in ({$mainRecord['Record_id']},{$minorRecord['Record_id']})
							order by
								LpuSectionBedState_begDate,
								ISNULL(LSBS.LpuSectionBedState_endDate,@bigdate),
								isMain desc
						";
						$records = $this->queryResult($query, array('main_id' => $main_id));
						$condition = function($currRecord, $checkRecord) {
							return $checkRecord['isMain'];
						};
						if ($setting['removeIntersection']) {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$scope->modifyPeriod();
									$scope->createNextPeriods();
								}
							};
						} else {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$curr['RecordStatus_Code'] = 2;
									$scope->setRecord($currIndex, $curr);
								}
							};
						}

						$linkObjects = $this->loadLinkObjectList(array(
							'Object_Name' => $subObject,
							'ColumnList' => array('LpuSection_id')
						));
						$getLinks = function($model, $curr) use($linkObjects) {
							$links = array();
							foreach($linkObjects as $linkObject) {
								$links[] = array_merge($linkObject, array(
									'new_id' => $curr['new_id'],
									'old_id' => $curr['old_id'],
									'additSet' => array('LpuSection_id' => $curr['LpuSection_id']),
								));
							}
							return $links;
						};

						$this->load->model('LpuStructure_model');
						$options = array(
							'idField' => 'LpuSectionBedState_id',
							'begDateField' => 'LpuSectionBedState_begDate',
							'endDateField' => 'LpuSectionBedState_endDate',
							'pmUser_id' => $data['pmUser_id'],
							'ins' => function($model, $curr) {
								return $model->LpuStructure_model->SaveLpuSectionBedState($curr);
							},
							'upd' => function($model, $curr) {
								return $model->LpuStructure_model->SaveLpuSectionBedState($curr);
							},
							'del' => function($model, $curr) use($data) {
								//Удаления операций с профилем койки
								$params = array('LpuSectionBedState_id' => $curr['LpuSectionBedState_id']);
								$query = "delete fed.LpuSectionBedStateOper where LpuSectionBedState_id = :LpuSectionBedState_id";
								$model->db->query($query, $params);

								return $model->ObjectRecordDelete($data, 'LpuSectionBedState', false, $curr['LpuSectionBedState_id']);
							},
							'getLinks' => $getLinks
						);

						$mainParams = $LpuSectionParams[(string)$mainRecord['Record_id']];
						$RecordUnion = new RecordUnion($this, $records, $mainParams, $mainLinks, $condition);
						$result = $RecordUnion->processRecords($processor)->doUnion($options);
						if (!is_array($result)) {
							throw new Exception("Ошибка при переносе объекта {$subObject}");
						}
						if (!$this->isSuccessful($result)) {
							throw new Exception($result[0]['Error_Msg']);
						}
						break;

					case 'UslugaComplexTariff':
						$query = "
							declare @bigdate date = dateadd(year, 50, dbo.tzGetDate())
							select
								UCT.*,
								UslugaComplexTariff_id as old_id,
								UslugaComplexTariff_id as new_id,
								UslugaComplexTariff_begDate as begDate,
								UslugaComplexTariff_endDate as endDate,
								case when LpuSection_id = :main_id then 1 else 0 end as isMain,
								1 as RecordStatus_Code
							from v_UslugaComplexTariff UCT with(nolock)
							where LpuSection_id in ({$mainRecord['Record_id']},{$minorRecord['Record_id']})
							order by
								UslugaComplex_id,
								UslugaComplexTariffType_id,
								LpuSectionProfile_id,
								PayType_id,
								UslugaComplexTariff_UED,
								UslugaComplexTariff_UEM,
								LpuUnitType_id,
								Sex_id,
								MesAgeGroup_id,
								VizitClass_id,
								UslugaComplexTariff_Code,
								UslugaComplexTariff_Name,
								UslugaComplexTariff_begDate,
								ISNULL(UCT.UslugaComplexTariff_endDate,@bigdate),
								isMain desc
						";
						$records = $this->queryResult($query, array('main_id' => $main_id));
						$condition = function($currRecord, $checkRecord) {
							return ($checkRecord['isMain']
								&& $checkRecord['UslugaComplex_id'] == $currRecord['UslugaComplex_id']
								&& $checkRecord['UslugaComplexTariffType_id'] == $currRecord['UslugaComplexTariffType_id']
								&& $checkRecord['PayType_id'] == $currRecord['PayType_id']
								&& $checkRecord['UslugaComplexTariff_UED'] == $currRecord['UslugaComplexTariff_UED']
								&& $checkRecord['UslugaComplexTariff_UEM'] == $currRecord['UslugaComplexTariff_UEM']
								&& $checkRecord['LpuUnitType_id'] == $currRecord['LpuUnitType_id']
								&& $checkRecord['Sex_id'] == $currRecord['Sex_id']
								&& $checkRecord['MesAgeGroup_id'] == $currRecord['MesAgeGroup_id']
								&& $checkRecord['VizitClass_id'] == $currRecord['VizitClass_id']
								&& $checkRecord['UslugaComplexTariff_Code'] == $currRecord['UslugaComplexTariff_Code']
								&& $checkRecord['UslugaComplexTariff_Name'] == $currRecord['UslugaComplexTariff_Name']
							);
						};
						if ($setting['removeIntersection']) {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$scope->modifyPeriod();
									$scope->createNextPeriods();
								}
							};
						} else {
							$processor = function($scope, $currIndex, $curr) {
								if (!$curr['isMain']) {
									$curr['RecordStatus_Code'] = 2;
									$scope->setRecord($currIndex, $curr);
								}
							};
						}

						$EvnUslugaLinkObjects = $this->loadLinkObjectList(array(
							'Object_Name' => $subObject,
							'ColumnList' => array('EvnUsluga_id')
						));
						$CmpCallCardLinkObjects = $this->loadLinkObjectList(array(
							'Object_Name' => $subObject,
							'ColumnList' => array('CmpCallCard_id')
						));
						$getLinks = function($model, $curr) use($EvnUslugaLinkObjects, $CmpCallCardLinkObjects) {
							$links = array();
							//Для обновления тарифов в объектах, имеющих поле EvnUsluga_id
							$query = "
								declare @begDT datetime = :begDateParam
								declare @endDT datetime = :endDateParam
								select EvnUsluga_id, UslugaComplexTariff_id
								from v_EvnUsluga with(nolock)
								where UslugaComplexTariff_id in (".implode(',',$curr['check_ids']).")
								and EvnUsluga_setDate between @begDT and isnull(@endDT, EvnUsluga_setDate)
							";
							$EvnUslugaList = $model->queryResult($query, $curr);
							foreach($EvnUslugaList as $EvnUsluga) {
								foreach($EvnUslugaLinkObjects as $linkObject) {
									$links[] = array_merge($linkObject, array(
										'new_id' => $curr['new_id'],
										'old_id' => $EvnUsluga['UslugaComplexTariff_id'],
										'additWhere' => array('EvnUsluga_id' => $EvnUsluga['EvnUsluga_id'])
									));
								}
							}
							//Для обновления тарифов в объектах, имеющих поле CmpCallCard_id
							$query = "
								declare @begDT datetime = :begDateParam
								declare @endDT datetime = :endDateParam
								select distinct CmpCallCard_id, UslugaComplexTariff_id
								from CmpCallCardUsluga with(nolock)
								where UslugaComplexTariff_id in (".implode(',',$curr['check_ids']).")
								and CmpCallCardUsluga_setDate between @begDT and isnull(@endDT, CmpCallCardUsluga_setDate)
							";
							$CmpCallCardList = $model->queryResult($query, $curr);
							foreach($CmpCallCardList as $CmpCallCard) {
								foreach($CmpCallCardLinkObjects as $linkObject) {
									$links[] = array_merge($linkObject, array(
										'new_id' => $curr['new_id'],
										'old_id' => $CmpCallCard['UslugaComplexTariff_id'],
										'additWhere' => array('CmpCallCard_id' => $CmpCallCard['CmpCallCard_id'])
									));
								}
							}
							return $links;
						};

						$this->load->model('UslugaComplex_model');
						$options = array(
							'idField' => 'UslugaComplexTariff_id',
							'begDateField' => 'UslugaComplexTariff_begDate',
							'endDateField' => 'UslugaComplexTariff_endDate',
							'pmUser_id' => $data['pmUser_id'],
							'ins' => function($model, $curr) {
								return $model->UslugaComplex_model->saveUslugaComplexTariff($curr);
							},
							'upd' => function($model, $curr) {
								return $model->UslugaComplex_model->saveUslugaComplexTariff($curr);
							},
							'del' => function($model, $curr) {
								return $model->UslugaComplex_model->deleteUslugaComplexTariff($curr);
							},
							'getLinks' => $getLinks
						);

						$mainParams = $LpuSectionParams[(string)$mainRecord['Record_id']];
						$RecordUnion = new RecordUnion($this, $records, $mainParams, $mainLinks, $condition);
						$result = $RecordUnion->processRecords($processor)->doUnion($options);
						if (!is_array($result)) {
							throw new Exception("Ошибка при переносе объекта {$subObject}");
						}
						if (!$this->isSuccessful($result)) {
							throw new Exception($result[0]['Error_Msg']);
						}
						break;

					default:
						//Переносаться все записи объекта
						$query = "
							declare
								@Error_Code bigint = null,
								@Error_Message varchar(4000) = '';
							set nocount on
							begin try
								update {$subObject}
								set {$table}_id = :main_id
								where {$table}_id = :minor_id
							end try
							begin catch
								set @Error_Code = error_number()
								set @Error_Message = error_message()
							end catch
							set nocount off
							select @Error_Code as Error_Code, @Error_Message as Error_Msg
						";
						$result = $this->queryResult($query, array(
							'main_id' => $mainRecord['Record_id'],
							'minor_id' => $minorRecord['Record_id']
						));

						if (!is_array($result)) {
							throw new Exception("Ошибка при переносе объекта {$subObject}");
						}
						if (!$this->isSuccessful($result)) {
							throw new Exception($result[0]['Error_Msg']);
						}
				}
			}

			//Записи, которые не были перенесены по указаным объектам, удаляются
			$deleteSubObjects = array(
				'LpuSectionTariff',
				'LpuSectionFinans',
				'LpuSectionLicence',
				'UslugaComplexPlace',
				'LpuSectionWard',
				'LpuSectionBedState',
				'UslugaComplexTariff'
			);
			$deleteQuery = "";
			foreach($deleteSubObjects as $subObject) {
				$deleteQuery .= "
					delete from {$subObject} with (rowlock) where LpuSection_id = :LpuSection_id
				";
			}
			$query = "
				declare
					@Error_Code bigint = null,
					@Error_Message varchar(4000) = '';
				set nocount on
				begin try
					{$deleteQuery}
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			$result = $this->queryResult($query, array('LpuSection_id' => $minorRecord['Record_id']));
			if (!is_array($result)) {
				throw new Exception("Ошибка при удалении объектов");
			}
			if (!$this->isSuccessful($result)) {
				throw new Exception($result[0]['Error_Msg']);
			}

			//Объединение отделений и перенос записей по остальным связанным объектам
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec od.p_ObjectMerge_ins
					@ObjectMerge_id = :ObjectMerge_id,
					@ObjectMerge_Name = :Table,
					@ObjectMergeStatus_id = :ObjectMergeStatus_id,
					@Object_id = :Object_id,
					@Object_did = :Object_did,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->queryResult($query, array(
				'ObjectMerge_id' => null,
				'Table' => $table,
				'ObjectMergeStatus_id' => 4,
				'Object_id' => $mainRecord['Record_id'],
				'Object_did' => $minorRecord['Record_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!is_array($result)) {
				throw new Exception("Ошибка при объединении отделений");
			}
			if (!$this->isSuccessful($result)) {
				throw new Exception($result[0]['Error_Msg']);
			}
			restore_error_handler();
		} catch(Exception $e) {
			restore_error_handler();
			$this->isAllowTransaction = true;
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->isAllowTransaction = true;
		$this->commitTransaction();
		return array('success' => true);
	}
	
	/**
	 * Проверка, что у объединяемых людей совпадают полисные данные.
	 *
	 * @param Array $records Записи пришедшие с клиента
	 */
	function CheckPersonPolis ($person_id1, $person_id2) {
		$query = "
			select
				1
			from v_PersonState p1 (nolock)
			inner join v_Polis pol1 (nolock) on p1.Polis_id = pol1.Polis_id
			inner join v_Polis pol2 (nolock) on
				pol1.PolisType_id = pol2.PolisType_id
				and pol1.OrgSmo_id = pol2.OrgSmo_id
				and pol1.OmsSprTerr_id = pol2.OmsSprTerr_id
				and pol1.Polis_Num = pol2.Polis_Num
				--and pol1.Polis_Ser = pol2.Polis_Ser
			inner join v_PersonState p2 (nolock) on p2.Polis_id = pol2.Polis_id
			where p1.Person_id = :Person_id1 and p2.Person_id = :Person_id2
		";
		$result = $this->db->query(
			$query,
			array(
				'Person_id1' => $person_id1,
				'Person_id2' => $person_id2,
			)
		);
		$response = $result->result('array');
		if (is_array($response) && count($response) > 0)
			return true;
		else
			return false;
	}
	/**
	 * обе записи из БДЗ (Server_pid = 0),
	 * BDZ_id в основной записи есть, в двойнике отсутствует, 
	 * ФИО ДР одинаковые,
	 * периоды действия полисов не пересекаются.
	 */
	function CheckPersonDoubleBDZ($person_id1, $person_id2, $data){
	    
	    if ($data['session']['region']['nick']=='perm'&&$person_id1['isBdz']==$person_id2['isBdz']) {
			$query = "
				select
					1
				from v_PersonState p1 with (nolock)
				inner join v_PersonState p2 with (nolock) on p2.Person_id = :Person_id2 and p1.Person_SurName = p2.Person_SurName and p1.Person_FirName = p2.Person_FirName and p1.Person_SecName = p2.Person_SecName and p1.Person_BirthDay = p2.Person_BirthDay
				inner join v_Person p1p with (nolock) on p1p.Person_id = p1.Person_id and p1p.BDZ_id is not null
				inner join v_Person p2p with (nolock) on p2p.Person_id = p2.Person_id and p2p.BDZ_id is  null
				inner join Polis pol1 (nolock) on pol1.Polis_id = p1.Polis_id
				inner join Polis pol2 (nolock) on pol2.Polis_id = p2.Polis_id
				where p1.Person_id = :Person_id1 and (pol1.Polis_begDate>=pol2.Polis_endDate or pol1.Polis_endDate<=pol2.Polis_begDate)
			";
		} else {return false;}
		$result = $this->db->query(
			$query,
			array(
				'Person_id1' => $person_id1['Person_id'],
				'Person_id2' => $person_id2['Person_id'],
			)
		);
		$response = $result->result('array');
		if (is_array($response) && count($response) > 0)
			return true;
		else
			return false;
	}
	/**
	 * Проверка, что у объединяемых людей совпадают ФИО и BDZ_id.
	 *
	 * @param Array $records Записи пришедшие с клиента
	 */
	function CheckPersonBDZ ($person_id1, $person_id2, $data) {
		// #16773
		if ($data['session']['region']['nick']=='perm') {
			// Для Перми остается проверка на равенство BDZ_id на прикладном уровне.
			$query = "
				select
					1
				from v_PersonState p1 with (nolock)
				inner join v_PersonState p2 with (nolock) on p2.Person_id = :Person_id2 and p1.Person_SurName = p2.Person_SurName and p1.Person_FirName = p2.Person_FirName and p1.Person_SecName = p2.Person_SecName
				inner join v_Person p1p with (nolock) on p1p.Person_id = p1.Person_id
				inner join v_Person p2p with (nolock) on p2p.Person_id = p2.Person_id and (p1p.BDZ_id = p2p.BDZ_id /*or (p1p.BDZ_id is null and p2p.BDZ_id is null)*/)
				where p1.Person_id = :Person_id1 and p1.Server_pid = 0 and p2.Server_pid = 0
			";
		} else {
			// На других регионах не надо, т.к. идентификатора БДЗ там нет или он другой (как BDZ_Guid) на Уфе
			$query = "
				select
					1
				from v_PersonState p1 with (nolock)
				inner join v_PersonState p2 with (nolock) on p2.Person_id = :Person_id2 and p1.Person_SurName = p2.Person_SurName and p1.Person_FirName = p2.Person_FirName and p1.Person_SecName = p2.Person_SecName
				where p1.Person_id = :Person_id1 and p1.Server_pid = 0 and p2.Server_pid = 0
			";
		}
		$result = $this->db->query(
			$query,
			array(
				'Person_id1' => $person_id1,
				'Person_id2' => $person_id2,
			)
		);
		
		if (is_object($result)) {
			$response = $result->result('array');
			if (is_array($response) && count($response) > 0)
				return true;
			else
				return false;
		}
		
		return false;
	}
	
	/**
	 * Получение идентификатора сервера человека, то есть server_pid
	 *
	 * @param Array $records Записи пришедшие с клиента
	 */
	function getPersonServer ($records) {
		$query = "
			select 
				Server_pid
			from v_PersonState (nolock)
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, array('Person_id' => $records['Person_id']))->result_array();
		if (is_array($result) && count($result) == 1)
			return $result[0]['Server_pid'];
		else
			return false;
	}

	/**
	 * Получение типа полиса человека
	 *
	 * @param Array $records Записи пришедшие с клиента
	 */
	function getPersonPolisType($records) {
		$query = "
			select top 1
				Polis.PolisType_id
			from
				v_PersonState ps (nolock)
				left join Polis (nolock) on Polis.Polis_id = ps.Polis_id 
					and Polis.Polis_begDate < dbo.tzGetDate()
					and (Polis.Polis_endDate >= dbo.tzGetDate() or Polis.Polis_endDate is null)
			where
				ps.Person_id = :Person_id 
		";
		$result = $this->db->query($query, array('Person_id' => $records['Person_id']))->result_array();
		if (is_array($result) && count($result) == 1)
			return $result[0]['PolisType_id'];
		else
			return 0;
	}
	/**
	 * Проверяем, может человек уже объединен (актуально для реестров)
	 * @param $records
	 */
	function checkExistPersons($params) {
		$ex = false;
		$query = "select count(*) as rec from v_PersonState (nolock) where Person_id in (:Person_id, :Person_did)";
		$this->textlog->add('Выполняем проверку (может быть человек уже объединен) : '.getDebugSql($query, $params).' ');
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$response = $result->result('array');
			if (is_array($response) && count($response) > 0) {
				$ex = ($response[0]['rec']==2)?true:false;
			}
		}
		return $ex;
	}

	/**
	 * Проверка специфики новорожденных у объединяемых людей
	 */
	function checkUnionPersonNewBorn($records) {
		$success = array(array('success' => true));

		$person_ids = array_map(function($record){return $record['Person_id'];}, $records);
		$person_ids_str = implode(",", $person_ids);

		$new_born_list = $this->queryResult("
			select
				PNB.PersonNewBorn_id,
				PNB.Person_id,
				PNB.BirthSpecStac_id
			from v_PersonNewBorn PNB with(nolock)
			where PNB.Person_id in ({$person_ids_str})
		");
		if (!is_array($new_born_list)) {
			return $this->createError('','Ошибка при поиске специфик новорожденных');
		}

		$new_born_count = count($new_born_list);

		if ($new_born_count == 0) {
			return $success;
		}

		$new_born_by_person = array();
		foreach($new_born_list as $new_born) {
			$new_born_by_person[$new_born['Person_id']] = $new_born;
		}

		$main_record = null;
		foreach($records as &$record) {
			$key = $record['Person_id'];
			$new_born = isset($new_born_by_person[$key])?$new_born_by_person[$key]:null;

			$record['PersonNewBorn_id'] = isset($new_born)?$new_born['PersonNewBorn_id']:null;

			if ($record['IsMainRec']) {
				$main_record = $record;
			}
		}

		if ($new_born_count >= 1 && empty($main_record['PersonNewBorn_id'])) {
			return $this->createError('','При объединении людей, один или более из которых имеет специфику новорожденного, в качестве главной записи нужно выбирать человека со спецификой новорожденного');
		}

		return $success;
	}
	
	/**
	 * Проводит объединение людей
	 */
	function doPersonUnion($data)
	{
		/*
		return array(0 => array('success' => false, 'Error_Msg' => 'Объединение людей временно приостановлено.'));
		exit;
		*/
		$this->load->library('textlog', array('file'=>'doPersonUnion.log'));
		$this->textlog->add('');
		$this->textlog->add('doPersonUnion: Запуск');
		$records = $data['Records'];
		$fromRegistry = (isset($data['fromRegistry']))?$data['fromRegistry']:false;
		$fromModeration = (isset($data['fromModeration']))?$data['fromModeration']:false;
		$procedure = ((!isset($data['PersonDoubles_id'])) || ($data['PersonDoubles_id'] <= 0)) ? 'pd.p_PersonDoubles_ins': 'pd.p_PersonDoubles_upd';
		if ($fromRegistry)
			$this->textlog->add('Объединение из реестра');
		//Определяем главную запись,
		foreach ($records as $record)
		{
			if ($record['IsMainRec']==1) {
				//сохраняем ее отдельно
				$mainrec = $record;
			}
		}
		$this->textlog->add('Главная запись (Person_id): '.$mainrec['Person_id']);
		//$this->textlog->add('Главная запись (Person_id): '.$record['Person_id']);

		$resp = $this->checkUnionPersonNewBorn($records);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		// Определяем из БДЗ ли главный двойник
		$server_pid = $this->getPersonServer($mainrec);
		$mainrec['isBdz'] = ($server_pid == 0);
		$mainrec['isPfr'] = ($server_pid == 1);
		$mainrec['PolisType_id'] = $this->getPersonPolisType($mainrec);
		if ($mainrec['isBdz']===true)
			$this->textlog->add('Главный двойник из БДЗ');
		if ($mainrec['isPfr']===true)
			$this->textlog->add('Главный двойник из ПФР');
		$this->textlog->add('Тип полиса главного двойника: '.$mainrec['PolisType_id']);
		
		$this->textlog->add('main server_pid = '.$server_pid.'');
		
		if ( !isSuperadmin() && (!$fromRegistry) && (!$fromModeration || !isLpuAdmin()) ) {
			$this->textlog->add('Цикл по записям двойников, если не суперадмин и не из реестра');
			//Проходим по всем остальным записям
			foreach ($records as $record)
			{
				//Если это не главная запись
				if ($record['IsMainRec'] != 1) {
					$server_pid = $this->getPersonServer($record);
					$record['isBdz'] = ($server_pid == 0);
					$record['isPfr'] = ($server_pid == 1);
					$record['PolisType_id'] = $this->getPersonPolisType($record);
					$this->textlog->add('record server_pid = '.$server_pid.'');
					
					if ($record['isBdz']===true)
						$this->textlog->add('Человек-двойник (Person_id='.$record['Person_id'].') из БДЗ');
					if ($record['isPfr']===true)
						$this->textlog->add('Человек-двойник (Person_id='.$record['Person_id'].') из ПФР');
					
					$this->textlog->add('Тип полиса человека-двойника (Person_id='.$record['Person_id'].'): '.$record['PolisType_id']);
					//if((($record['isBdz'] && $mainrec['isBdz'])&&))
					
					// Если главный человек из БДЗ и объединяемый из БДЗ и если не (у главного - обычный ОМС полис (хотя надо действующий по идее), а объединяемого - временное)
					// или
					// главный человек из БДЗ и объединяемый из БДЗ
					$this->textlog->add('Текущий регион (region='.$data['session']['region']['nick'].')');
					if (  ($mainrec['isPfr'] && $record['isPfr'])   && ($data['session']['region']['nick']=='perm') ) {
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Извините, но нельзя отправить на модерацию две записи из БДЗ или две записи из ПФР."');
						return array(0 => array('Error_Msg' => 'Извините, но нельзя отправить на модерацию две записи из БДЗ или две записи из ПФР.'));
					}
					
					//Добавляем всех людей в таблицу двойников
					$queryParams = array();
					$queryParams['Person_id'] = $mainrec['Person_id'];
					$queryParams['Person_did'] = $record['Person_id'];
					$queryParams['pmUser_id'] = $data['pmUser_id'];
					$queryParams['PersonDoubles_id'] = !empty($data['PersonDoubles_id']) ? $data['PersonDoubles_id']: NULL;
					$query = "select 1 from pd.PersonDoubles (nolock) where (:Person_id in (Person_id, Person_did) or :Person_did in (Person_id, Person_did)) and IsNull(PersonDoublesStatus_id,3) = 3";
					$this->textlog->add('Выполнение запроса (проверка, может этот человек уже стоит на модерации) : '.getDebugSql($query, $queryParams).' ');
					$result = $this->db->query($query, $queryParams);
					
					$response = $result->result('array');

					if (is_array($response) && count($response) > 0)
					{
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Спасибо, один из присланных двойников уже стоит в очереди на модерацию."');
						return array(0 => array('Error_Msg' => 'Спасибо, один из присланных двойников уже стоит в очереди на модерацию.'));
					}

					if ($this->checkExistPersons($queryParams)!==true) {
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Спасибо, данный двойник уже объединен."');
						return array(0 => array('Error_Msg' => 'Спасибо, данный двойник уже объединен.'));
					}
					
					$query = "
						declare
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
						set @Res = :PersonDoubles_id;
						exec {$procedure}
							@PersonDoubles_id = @Res output,
							@Person_id = :Person_id,
							@Person_did = :Person_did,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;
						select @Res as PersonDoubles_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					";
					$this->textlog->add('Выполнение запроса добавления двойника в очередь модерации: '.getDebugSql($query, $queryParams).' ');
					$result = $this->db->query($query, $queryParams);
					//Если возвращается ошибка, то выдаем пользователю и выходим
					if ( !$result )
					{
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Ошибка при выполнении запроса к базе данных (объединение)"');
						return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (объединение)'));
					}
				}
			}
			$this->textlog->add('Выход из процедуры с сообщением пользователю: "Спасибо, заявка на объединение отправлена на модерацию."');
			return array(0 => array('success' => true, 'Success_Msg' => 'Спасибо, заявка на объединение отправлена на модерацию.', 'Error_Msg' => ''));
		}
		

		$this->textlog->add('Цикл по записям двойников, если суперадмин или из реестра');
		//Проходим по всем остальным записям
		$causeNoMerge = '';
		$cntDocuments = 0;
		foreach ($records as $record)
		{

			//Если это не главная запись
			if ($record['IsMainRec'] != 1) {
				$server_pid = $this->getPersonServer($record);
				$record['isBdz'] = ($server_pid == 0);
				$record['isPfr'] = ($server_pid == 1);
				$record['PolisType_id'] = $this->getPersonPolisType($record);
				$this->textlog->add('record server_pid = '.$server_pid.'');

				if ( !$mainrec['isBdz'] && $record['isBdz']) {
					// Если есть запись из БДЗ - то она должна быть главной
					$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Главная запись обязательно должна быть из БДЗ."');
					return array(0 => array('Error_Msg' => 'Главная запись обязательно должна быть из БДЗ.'));
				}

				if ($record['isBdz']===true)
					$this->textlog->add('Человек-двойник (Person_id='.$record['Person_id'].') из БДЗ');
				if ($record['isPfr']===true)
					$this->textlog->add('Человек-двойник (Person_id='.$record['Person_id'].') из ПФР');
				$this->textlog->add('Тип полиса человека-двойника (Person_id='.$record['Person_id'].'): '.$record['PolisType_id']);

				if ( !isSuperadmin() && (!$fromRegistry) && $record['isBdz'] ) {
					$this->textlog->add('Выход из процедуры (если не суперадмин и не из реестра) с сообщением ошибки пользователю: "Двойник не может быть из БДЗ."');
					return array(0 => array('Error_Msg' => 'Двойник не может быть из БДЗ.'));
				}

				// В соответствие с задачей #3882 меняем объединение людей на перенос случаев и
				// помещение в очередь для отложенного объединения ночью
				$queryParams = array();
				$queryParams['Person_id'] = $mainrec['Person_id'];
				$queryParams['Person_did'] = $record['Person_id'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$queryParams['PersonDoubles_id'] = !empty($data['PersonDoubles_id']) ? $data['PersonDoubles_id']: NULL;
				$isUnionBDZ = $this->CheckPersonBDZ($mainrec['Person_id'], $record['Person_id'], $data); //Проверка, что у объединяемых людей совпадают ФИО и BDZ_id
				$isDoubleBDZ = $this->CheckPersonDoubleBDZ($mainrec, $record, $data); //периоды действия полисов не пересекаются
                if ($this->checkExistPersons($queryParams)!==true) {
                    $this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Спасибо, данный двойник уже объединен."');
                    return array(0 => array('Error_Msg' => 'Спасибо, данный двойник уже объединен.'));
                }
				if (!$isUnionBDZ) {
					// Проверка есть ли документы перед переносом случаев
					$query = "
						select count(v_EvnXml.EvnXml_id) as cntDocuments
						from v_Evn (nolock)
						inner join v_EvnXml (nolock) on v_EvnXml.Evn_id = v_Evn.Evn_id
						where v_Evn.Person_id = :Person_id
					";
					$result = $this->db->query($query, array(
						'Person_id' => $queryParams['Person_did']
					));
					if ( !is_object($result) ) {
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Извините, не удалось выполнить проверку наличия документов."');
						return array(0 => array('Error_Msg' => 'Извините, не удалось выполнить проверку наличия документов.'));
					}
					$response = $result->result('array');
					$cntDocuments += $response[0]['cntDocuments'];

					// Переносим случаи
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec pd.xp_PersonMergeData
							@Person_id = :Person_id,
							@Person_did = :Person_did,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					//echo getDebugSql($query, $queryParams);exit;
					$this->textlog->add('Выполнение запроса (перенос случаев): '.getDebugSql($query, $queryParams).' ');
					$result = $this->db->query($query, $queryParams);

					//Если возвращается ошибка, то выдаем пользователю и выходим
					if (!is_object($result))
					{
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Ошибка при выполнении запроса к базе данных (объединение)"');
						return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (объединение)'));
					}
				}
				if ($isDoubleBDZ||$isUnionBDZ || !$record['isBdz']) {
					$this->textlog->add('ФИО и BDZ_id одинаковы или второй человек не из БДЗ, можем объединять ');
					if ( !$fromModeration ) {
						$query = "select 1 from pd.PersonDoubles (nolock) where (:Person_id in (Person_id, Person_did) or :Person_did in (Person_id, Person_did)) and IsNull(PersonDoublesStatus_id,3) = 3";
						$this->textlog->add('Выполнение запроса (проверка, может этот человек уже стоит на модерации) : '.getDebugSql($query, $queryParams).' ');
						$result = $this->db->query($query, $queryParams);
					
						$response = $result->result('array');

						if (is_array($response) && count($response) > 0)
						{
							$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Спасибо, один из присланных двойников уже стоит в очереди на модерацию."');
							return array(0 => array('Error_Msg' => 'Спасибо, один из присланных двойников уже стоит в очереди на модерацию.'));
						}

						if ($this->checkExistPersons($queryParams)!==true) {
							$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Спасибо, данный двойник уже объединен."');
							return array(0 => array('Error_Msg' => 'Спасибо, данный двойник уже объединен.'));
						}
					}
					
					$this->beginTransaction();

					// Отмечаем, что мы объединили человека
					$query = "
						declare
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
						set @Res = :PersonDoubles_id;
						exec {$procedure}
							@PersonDoubles_id = @Res output,
							@Person_id = :Person_id,
							@Person_did = :Person_did,
							@PersonDoublesStatus_id = 3, -- https://redmine.swan.perm.ru/issues/23650
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;
						select @Res as PersonDoubles_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					";
					$this->textlog->add('Выполнение запроса (Отмечаем, что мы объединили человека): '.getDebugSql($query, $queryParams).' ');
					$result = $this->db->query($query, $queryParams);
					
					$query = "
						declare
							@CauseNoMerge int,
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec pd.xp_PersonMerge
							@Person_id = :Person_id,
							@Person_did = :Person_did,
							@CauseNoMerge = @CauseNoMerge output,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
						select @CauseNoMerge as CauseNoMerge, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$this->textlog->add('Выполнение запроса (Вызываем хранимую процедуру для объединения на сервере с заданными параметрами): '.getDebugSql($query, $queryParams).' ');
					//echo getDebugSQL($query, $queryParams);exit();
					$result = $this->db->query($query, $queryParams);

					//Если возвращается ошибка, то выдаем пользователю и выходим
					if ( !is_object($result) ) {
						$this->rollbackTransaction();
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Ошибка при выполнении запроса к базе данных (объединение)"');
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (объединение)'));
					}

					$response = $result->result('array');
					
					if (!$this->isSuccessful($response)) {
						$this->rollbackTransaction();
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: ' . $response[0]['Error_Msg']);
						return array(array('Error_Msg' => $response[0]['Error_Msg']));
					}

					if ( is_array($response) && count($response) > 0 ) {
						if ( 5 == $response[0]['CauseNoMerge'] ) {
							$causeNoMerge = 'Оба врачи. Запланировано к объединению на выходных.';
						}
					}
					
					$this->commitTransaction();
					
				} else {
					if (!($fromRegistry && $record['isBdz'] )) {
						// Помещаем в очередь
						/*$query = "
							declare
								@Res bigint,
								@ErrCode bigint,
								@ErrMsg varchar(4000);
							set @Res = null;
							exec pd.p_PersonDoublesQueue_ins
								@PersonDoublesQueue_id = @Res output,
								@Person_id = :Person_id,
								@Person_did = :Person_did,
								@PersonDoubles_Priority = 1,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output;
							select @Res as PersonDoublesQueue_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
						";
						$this->textlog->add('Выполнение запроса (помещение в очередь): '.getDebugSql($query, $queryParams).' ');
						$result = $this->db->query($query, $queryParams);*/
						
						// Отмечаем, что мы объединили человека
						$query = "
							declare
								@Res bigint,
								@ErrCode bigint,
								@ErrMsg varchar(4000);
							set @Res = :PersonDoubles_id;
							exec {$procedure}
								@PersonDoubles_id = @Res output,
								@Person_id = :Person_id,
								@Person_did = :Person_did,
								@PersonDoublesStatus_id = 3, -- https://redmine.swan.perm.ru/issues/23650
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output;
							select @Res as PersonDoubles_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
						";
						$this->textlog->add('Выполнение запроса (Отмечаем, что мы объединили человека): '.getDebugSql($query, $queryParams).' ');
						$result = $this->db->query($query, $queryParams);
						
						//На основании задачи http://redmine.swan.perm.ru/issues/21025 - у суперАдмина есть права объеденять если обе записи из БДЗ
						$query = "
							declare
								@CauseNoMerge int,
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec pd.xp_PersonMerge
								@Person_id = :Person_id,
								@Person_did = :Person_did,
								@CauseNoMerge = @CauseNoMerge output,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output
							select @CauseNoMerge as CauseNoMerge, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$this->textlog->add('Выполнение запроса (dВызываем хранимую процедуру для объединения на сервере с заданными параметрами): '.getDebugSql($query, $queryParams).' ');
						$result = $this->db->query($query, $queryParams);

						//Если возвращается ошибка, то выдаем пользователю и выходим
						if ( !is_object($result) ) {
							$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Ошибка при выполнении запроса к базе данных (объединение)"');
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (объединение)'));
						}

						$response = $result->result('array');

						if ( is_array($response) && count($response) > 0 ) {
							if ( 5 == $response[0]['CauseNoMerge'] ) {
								$causeNoMerge = 'Оба врачи. Запланировано к объединению на выходных.';
							}
						}
					} else {
						// Не помещаем в очередь на объединение, если запущено с реестров и человек (второй) из БДЗ
						$this->textlog->add('!!! Не помещаем в очередь на объединение, если запущено с реестров и человек (второй) из БДЗ и ФИО или BDZ_id не совпадают');
						$query = "select 1 from pd.PersonDoubles with(nolock) where Person_id = :Person_id and Person_did = :Person_did and IsNull(PersonDoublesStatus_id,3) = 3";
						//Добавляем этих людей в таблицу двойников
						
						$this->textlog->add('Выполнение запроса (проверка, может этот человек уже стоит на модерации) : '.getDebugSql($query, $queryParams).' ');
						$result = $this->db->query($query, $queryParams);

						$response = $result->result('array');

						if (is_array($response) && count($response) > 0)
						{
							$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Спасибо, данный двойник уже прислан на модерацию."');
							return array(0 => array('Error_Msg' => 'Спасибо, данный двойник уже прислан на модерацию.'));
						}

						if ($this->checkExistPersons($queryParams)!==true) {
							$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Спасибо, данный двойник уже объединен."');
							return array(0 => array('Error_Msg' => 'Спасибо, данный двойник уже объединен.'));
						}

						$query = "
							declare
								@Res bigint,
								@ErrCode bigint,
								@ErrMsg varchar(4000);
							set @Res = :PersonDoubles_id;
							exec {$procedure}
								@PersonDoubles_id = @Res output,
								@Person_id = :Person_id,
								@Person_did = :Person_did,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output;
							select @Res as PersonDoubles_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
						";
						$this->textlog->add('Выполнение запроса добавления двойника в очередь модерации: '.getDebugSql($query, $queryParams).' ');
						$result = $this->db->query($query, $queryParams);
						//Если возвращается ошибка, то выдаем пользователю и выходим
						if ( !$result )
						{
							$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Ошибка при выполнении запроса к базе данных (объединение)"');
							return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (объединение)'));
						}
					}
				}
				if (is_object($result))
				{
					$response = $result->result('array');

					if (!is_array($response) || count($response) == 0)
					{
						$this->textlog->add('Выход из процедуры с сообщением ошибки пользователю: "Ошибка при объединении записей по людям"');
						return array(0 => array('Error_Msg' => 'Ошибка при объединении записей по людям'));
					}
					else if (strlen($response[0]['Error_Msg']) > 0)
					{
						return $response;
					}
				}

			}
		}
		$response = array(array('Object_id' => $mainrec['Person_id'], 'Error_Msg' => '', 'Info_Msg' => ''));
		if (!empty($causeNoMerge)) {
			$response[0]['Info_Msg'] .= $causeNoMerge . '<br />';
		}
		if ($cntDocuments > 0) {
			$response[0]['Info_Msg'] .= 'Внимание: документы ЭМК (протоколы осмотров и обследований, эпикризы и т.п.) могут содержать данные предыдущего пациента. Необходима корректировка<br />';
		}
		$this->textlog->add('Выход из процедуры без ошибок.');
		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function doPersonMerge($data) {
		if ( !isSuperadmin() && !allowEditPersonEncrypHIV($data['session']) ) {
			return array(0 => array('success' => false, 'Success_Msg' => 'Нет прав для переноса случаев.'));
		}

		$records = $data['Records'];

		//Определяем главную запись,
		foreach ($records as $record)
		{
			if ($record['IsMainRec']==1) {
				//сохраняем ее отдельно
				$mainrec = $record;
				break;
			}
		}

		//Проходим по всем остальным записям
		foreach ($records as $record)
		{
			//Если это не главная запись
			if ($record['IsMainRec'] != 1) {
				//Вызываем хранимую процедуру для объединения на сервере с заданными параметрами
				$queryParams = array();
				$queryParams['Person_id'] = $mainrec['Person_id'];
				$queryParams['Person_did'] = $record['Person_id'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec pd.xp_PersonMerge
						@Person_id = :Person_id,
						@Person_did = :Person_did,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, $queryParams);
				//Если возвращается ошибка, то выдаем пользователю и выходим
				if (!is_object($result))
				{
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (перенос случаев)'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при переносе записей по людям'));
				}
				else if (strlen($response[0]['Error_Msg']) > 0)
				{
					return $response;
				}
			}
		}
		$result = $this->db->query($query, $queryParams);
		$response = array(array('Object_id' => $mainrec['Person_id'], 'Error_Msg' => ''));
		return $response;
	}
	
	/**
	 * Проводит перенос данных по событиям людей, используя хранимку xp_PersonMergeData
	 */
	function doPersonEvnTransfer($data)
	{
		if ( !isSuperadmin() && !allowEditPersonEncrypHIV($data['session']) ) {
			return array(0 => array('success' => false, 'Success_Msg' => 'Нет прав для переноса случаев.'));
		}
		
		$records = $data['Records'];
		
		//Определяем главную запись, 
		foreach ($records as $record)
		{
			if ($record['IsMainRec']==1) {
				//сохраняем ее отдельно
				$mainrec = $record;
				break;
			}
		}
		
		//Проходим по всем остальным записям
		$cntDocuments = 0;
		foreach ($records as $record)
		{
			//Если это не главная запись
			if ($record['IsMainRec'] != 1) {
				// Проверка есть ли документы перед переносом случаев
				$query = "
						select count(v_EvnXml.EvnXml_id) as cntDocuments
						from v_Evn (nolock)
						inner join v_EvnXml (nolock) on v_EvnXml.Evn_id = v_Evn.Evn_id
						where v_Evn.Person_id = :Person_id
					";
				$result = $this->db->query($query, array(
					'Person_id' => $record['Person_id']
				));
				if ( !is_object($result) ) {
					return array(0 => array('Error_Msg' => 'Извините, не удалось выполнить проверку наличия документов.'));
				}
				$response = $result->result('array');
				$cntDocuments += $response[0]['cntDocuments'];

				//Вызываем хранимую процедуру для объединения на сервере с заданными параметрами
				$queryParams = array();
				$queryParams['Person_id'] = $mainrec['Person_id'];
				$queryParams['Person_did'] = $record['Person_id'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec pd.xp_PersonMergeData
						@Person_id = :Person_id,
						@Person_did = :Person_did,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, $queryParams);
				//Если возвращается ошибка, то выдаем пользователю и выходим
				if (!is_object($result))
				{
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (перенос случаев)'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при переносе записей по людям'));
				}
				else if (strlen($response[0]['Error_Msg']) > 0)
				{
					return $response;
				}
			}
		}
		$result = $this->db->query($query, $queryParams);
		$response = array(array('Object_id' => $mainrec['Person_id'], 'Error_Msg' => ''));
		if ($cntDocuments > 0) {
			$response[0]['Info_Msg'] = 'Внимание: документы ЭМК (протоколы осмотров и обследований, эпикризы и т.п.) могут содержать данные предыдущего пациента. Необходима корректировка';
		}
		return $response;
	}
	
	
	/**
	 * Планирует объединение людей
	 */
	function doPlanPersonUnion($data)
	{
		$records = $data['Records'];

		//Определяем главную запись,
		foreach ($records as $record)
		{
			if ($record['IsMainRec']==1) {
				//сохраняем ее отдельно
				$mainrec = $record;
				break;
			}
		}

		if (empty($mainrec)) {
			return array(0 => array('Error_Msg' => 'Не определена главная запись.'));
		}
		
		//Проходим по всем остальным записям
		foreach ($records as $record)
		{
			//Если это не главная запись
			if ($record['IsMainRec'] != 1) {
				if (!isSuperadmin() && $this->getPersonServer($record) == 0 ) {
					return array(0 => array('Error_Msg' => 'Двойник не может быть из БДЗ.'));
				}
				//Вызываем хранимую процедуру для объединения на сервере с заданными параметрами
				$queryParams = array();
				$queryParams['Person_id'] = $mainrec['Person_id'];
				$queryParams['Person_did'] = $record['Person_id'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = null;
					exec pd.p_PersonDoublesQueue_ins
						@PersonDoublesQueue_id = @Res output,
						@Person_id = :Person_id,
						@Person_did = :Person_did,
						@PersonDoubles_Priority = 2,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;
					select @Res as PersonDoublesQueue_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";
				$result = $this->db->query($query, $queryParams);

				if (!$result)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при объединении записей по людям'));
				}
			}
		}

		if (empty($queryParams['Person_did'])){
			return array(0 => array('Error_Msg' => 'Не передана запись двойника.'));
		}

		$mainrec['pmUser_id'] = $data['pmUser_id'];
		$mainrec['Person_did'] = $queryParams['Person_did'];
		$query = "
			update pd.PersonDoubles
			set
				PersonDoublesStatus_id = 3,
				PersonDoubles_updDT = dbo.tzGetDate(),
				pmUser_updId = :pmUser_id
			where
				Person_id = :Person_id 
				and Person_did = :Person_did 
		";
		$result = $this->db->query($query, $mainrec);
		return array(0 => array('Object_id' => $mainrec['Person_id'], 'Error_Msg' => ''));
	}
	
	/**
	 * Общее удаление записи из таблицы
	 */
	function ObjectRecordDelete($data, $object, $obj_isEvn, $id, $scheme = "dbo")
	{
		$params = Array();
		if ($id <= 0)
		{
			return false;
		}
		$params['id'] = $id;
		$obj_isEvn = (strpos(mb_strtoupper($obj_isEvn), "TRUE")!==false) ? true : false;
		if ( (strpos(mb_strtoupper($object), "EVN")!==false && $obj_isEvn) || (in_array(mb_strtolower($object), array('registry'))) ) 
		{
			$fields = ":pmUser_id, ";
			$params['pmUser_id'] = $data['session']['pmuser_id'];
		}
		else
		{
			$fields = "";
		}
		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);

			exec {$scheme}.p_{$object}_del
				:id,
				{$fields}
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Message;
		";
		$res = $this->db->query($query, $params);
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Общее удаление записей из таблицы
	 */
	function ObjectRecordsDelete($data, $object, $obj_isEvn, $ids, $scheme = "dbo", $linkedTables)
	{
		$params = Array();
		$obj_isEvn = (strpos(mb_strtoupper($obj_isEvn), "TRUE")!==false) ? true : false;

		foreach ($ids as $id) {
			if ($id > 0)
			{

				//Если удаляем организацию надо удалить записи из связанных таблиц
				if ($object == 'Org') {
					$orgType = $this->getFirstResultFromQuery("
						select
							OT.OrgType_SysNick
						from
							Org O with (nolock)
							left join OrgType OT with (nolock) on O.OrgType_id = OT.OrgType_id
						where O.Org_id = {$id}
					");

					if (!empty($orgType)){
						switch($orgType){
							case 'bank':
								array_push($linkedTables, array('schema' => 'dbo', 'table' => 'OrgBank'));
							break;
							case 'farm':
								array_push($linkedTables, array('schema' => 'dbo', 'table' => 'OrgFarmacy'));
							break;
							case 'dep':
							case 'IE':
								array_push($linkedTables, array('schema' => 'dbo', 'table' => 'OrgDep'));
							break;
							case 'anatom':
								array_push($linkedTables, array('schema' => 'dbo', 'table' => 'OrgAnatom'));
							break;
							case 'lpu':
								array_push($linkedTables, array('schema' => 'dbo', 'table' => 'Lpu'));
							break;
							case 'smo':
								array_push($linkedTables, array('schema' => 'dbo', 'table' => 'OrgSmo'));
							break;
						}
					}

					array_push($linkedTables, array('schema' => 'fed', 'table' => 'OrgStac')); //Организации с заполненым полем "Код стационарного учреждения"
				}

				if (is_array($linkedTables) && count($linkedTables) > 0 && !empty($linkedTables[0]['table'])){

					$response = $this->deleteRecordsFromLinkedTables(array('key' => $object."_id", 'value' =>$id), $linkedTables);

					if (!$response || !empty($response['Error_Msg'])){
						if (!empty($response['Error_Msg'])){
							return array('Error_Msg' => $response['Error_Msg']);
						} else {
							return false;
						}
					}
				}

				$params['id'] = $id;
				if ((strpos(mb_strtoupper($object), "EVN")!==false && $obj_isEvn && $object != 'PersonEvnPSLocat') || in_array($object, array('LpuSectionQuote')))
				{
					$fields = ":pmUser_id, ";
					$params['pmUser_id'] = $data['session']['pmuser_id'];
				} elseif (in_array($object, array('LpuSectionWard', 'LpuSectionBedState','MorbusOnkoLink'))) {
					$fields = " @pmUser_id = :pmUser_id, ";
					$params['pmUser_id'] = $data['session']['pmuser_id'];
				} else {
					$fields = "";
				}
				$query = "
					Declare @Error_Code bigint;
					Declare @Error_Message varchar(4000);
					exec {$scheme}.p_{$object}_del
						:id, {$fields}
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select
						@Error_Code as Error_Code,
						@Error_Message as Error_Message;
				";

				//echo getDebugSQL($query, $params);die;
				$res = $this->db->query($query, $params);
				if (!is_object($res))
				{
					return false;
				}
			}
		}
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
 	* Функция вывода Sql данных из любого объекта ($data['object']) полей ($fields) с указанием любых условий по этим полям
 	* По идее должно использоваться только для списков
 	* @author Night
	*/
	function GetObjectList($data)
	{
		$isc    = array('prefix', 'c', 'm', 'method', '_dc', 'vc', 'phpsessid', 'order_by_field', 'session');
		
		if (!empty($data['Object']) && in_array($data['Object'], array('Post','v_Post'))) {
			$isc[] = "post_curid";
		}
		
		$filter = " (1 = 1)";
		$fields = "";
		$order_by_field = null;
		unset($data['LpuOrg_id']);
		unset($data['FarmacyOtdel_id']);
		unset($data['attrObjects']);
		if (!isset($data['session']['OrgFarmacy_id']))
		{
			unset($data['Mol_id']);
			unset($data['OrgFarmacy_id']);
			unset($data['Contragent_id']);
		}

		foreach ($data as $index => $row)
		{
			if (mb_strtolower($index) == 'object')
			{
				// Здесь можно сделать мапс для проведения соответствий объектов с именами таблиц или вьюх
				if (mb_substr($row,0,4) == 'lis_') {
					$object = "lis.v_" . str_replace('lis_','',$row);
				} else {
					$object = "v_" . $row;
				}
			}
			elseif (!in_array(mb_strtolower($index), $isc))
			{
				if (!empty($row)) {
					if ($index == 'Server_id')
					{
						$filter .= " and " . $index . " in (0, " . $row . ")";
					}
					else if ($index == 'isClose')
					{
						continue;
					}
					else if (($row!='null') && ($row!='not null'))
					{
						$filter .= " and " . $index . " = " . $row;
					}
					else
					{
						$filter .= " and " . $index . " is " . $row;
					}
				}

				$fields .= $index . ", ";
			}
			if ( mb_strtolower($index) == 'order_by_field' )
			{
				$order_by_field = $row;
			}
		}
		if (!isset($object)) {
			die('Не задана таблица!');
		}
		if (!empty($fields))
		{
			$fields = mb_substr($fields, 0, strlen($fields) - 2);
        }
		else
		{
			$fields = "*";
        }

		// особые варианты выборки данных
		switch (true)
		{
			case ( in_array($object, array('v_DrugDisp')) ):
				$sql = "
					SELECT distinct
						([dmnn].[DrugMnn_id] * 10) as [DrugMnnKey_id],
						[dmnn].[DrugMnn_id],
						[dmnn].[DrugMnn_Code],
						[dmnn].[DrugMnn_Name],
						[sd].[PrivilegeType_id]
					FROM SicknessDrug sd (nolock)
					left join v_DrugMnn dmnn (nolock) on sd.DrugMnn_id=dmnn.DrugMnn_id
				";
			break;
			case ( in_array($object, array('v_DrugReg', 'v_DrugFed')) ):
				$sql = "
				SELECT distinct
					([DrugMnn_id] * 10 + isnull([Drug_IsLive], 0)) as [DrugMnnKey_id],
					[DrugMnn_id],
					[DrugMnn_Code],
					[DrugMnn_Name],
					[Drug_IsLive] as [Drug_IsKEK]
				FROM " . $object. " (nolock) ";
			break;
			case ( in_array($object, array('v_Diag')) ):
				$sql = "
					SELECT distinct
						Diag.Diag_id,
						Diag.Diag_pid,
						Diag.DiagLevel_id,
						Diag.Diag_Code,
						rtrim(Diag.Diag_Name) as Diag_Name,
						".(in_array(getRegionNumber(),array(59,2))?'convert(varchar(10),Diag.Diag_endDate,104) as Diag_endDate':'null as Diag_endDate').",
						PersonAgeGroup.PersonAgeGroup_Code,
						Sex.Sex_Code,
						IsNull(IsOms.YesNo_Code, 1) as DiagFinance_IsOms, -- если пустой, то разрешен по ОМС
						IsAlien.YesNo_Code as DiagFinance_IsAlien,
						IsHC.YesNo_Code as DiagFinance_IsHealthCenter,
						ISNULL(DiagFinance.DiagFinance_IsRankin, 1) as DiagFinance_IsRankin,
						STUFF(
							(SELECT
								','+v_PersonRegisterType.PersonRegisterType_SysNick
							FROM
								v_PersonRegisterDiag WITH (nolock)
								inner join v_PersonRegisterType WITH (nolock) on v_PersonRegisterType.PersonRegisterType_id = v_PersonRegisterDiag.PersonRegisterType_id
							WHERE
								v_PersonRegisterDiag.Diag_id = Diag.Diag_id
							FOR XML PATH ('')
							), 1, 1, ''
						) as PersonRegisterType_List,
						STUFF(
							(SELECT
								','+v_MorbusType.MorbusType_SysNick
							FROM
								v_MorbusDiag WITH (nolock)
								inner join v_MorbusType WITH (nolock) on v_MorbusType.MorbusType_id = v_MorbusDiag.MorbusType_id
							WHERE
								v_MorbusDiag.Diag_id = Diag.Diag_id
							FOR XML PATH ('')
							), 1, 1, ''
						) as MorbusType_List
					FROM " . $object . " Diag with (nolock)
						left join DiagFinance with (nolock) on DiagFinance.Diag_id = Diag.Diag_id
						left join Sex with (nolock) on Sex.Sex_id = DiagFinance.Sex_id
						left join YesNo IsOms with (nolock) on IsOms.YesNo_id = DiagFinance.DiagFinance_IsOms
						left join YesNo IsHC with (nolock) on IsHC.YesNo_id = DiagFinance.DiagFinance_IsHealthCenter
						left join YesNo IsAlien with (nolock) on IsAlien.YesNo_id = DiagFinance.DiagFinance_IsAlien
						left join PersonAgeGroup with (nolock) on PersonAgeGroup.PersonAgeGroup_id = DiagFinance.PersonAgeGroup_id
					ORDER BY Diag.Diag_id
				";
			break;
			case ( in_array($object, array('v_SicknessDiag')) ):
				$sql = "
					SELECT
						[SicknessDiag].[SicknessDiag_id],
						[Sickness].[Sickness_id],
						[Sickness].[Sickness_Code],
						[Sickness].[PrivilegeType_id],
						[Sickness].[Sickness_Name],
						[SicknessDiag].[Diag_id],
						convert(varchar(10), SicknessDiag_begDT, 104) as SicknessDiag_begDT,
						convert(varchar(10), SicknessDiag_endDT, 104) as SicknessDiag_endDT
					FROM [SicknessDiag]  (nolock) 
					LEFT JOIN [Sickness] on [SicknessDiag].[Sickness_id] = [Sickness].[Sickness_id]
				";
			break;
			case ( in_array($object, array('v_OrgRSchet')) ):
				$sql = "
					SELECT
						OrgRSchet_id,
						OrgRSchet_Name,
						ISNULL(OrgRSchetType_id, 1) as OrgRSchetType_id,
						convert(varchar(10), OrgRSchet_begDate, 104) as OrgRSchet_begDate,
						convert(varchar(10), OrgRSchet_endDate, 104) as OrgRSchet_endDate
					FROM
						v_OrgRSchet (nolock)
					WHERE
						Org_id = (select top 1 Org_id from Lpu (nolock) where Lpu_id = {$data['session']['lpu_id']})
				";
			break;
			case ( in_array($object, array('v_CmpLpu')) ):
				$sql = "
					SELECT
						CL.CmpLpu_id,
						ISNULL(CL.CmpLpu_Code, '') as CmpLpu_Code,
						COALESCE(L.Lpu_Nick, CL.CmpLpu_Name, '') as CmpLpu_Name
					FROM v_CmpLpu CL with (nolock)
						left join v_Lpu L with (nolock) on L.Lpu_id = CL.Lpu_id
					ORDER BY
						CmpLpu_Name
				";
			break;
			case ( in_array($object, array('v_Lpu')) ):
				$sql = "
					SELECT distinct
						Lpu.Lpu_id,
						Lpu.Lpu_IsOblast,
						RTRIM(Lpu.Lpu_Name) as Lpu_Name,
						RTRIM(Lpu.Lpu_Nick) as Lpu_Nick,
						Lpu.Lpu_Ouz,
						Lpu.Lpu_RegNomC,
						Lpu.Lpu_RegNomC2,
						Lpu.Lpu_RegNomN2,
						Lpu.Lpu_isDMS,
						convert(varchar(10), Lpu.Lpu_DloBegDate, 104) as Lpu_DloBegDate,
						convert(varchar(10), Lpu.Lpu_DloEndDate, 104) as Lpu_DloEndDate,
						convert(varchar(10), Lpu.Lpu_BegDate, 104) as Lpu_BegDate,
						convert(varchar(10), Lpu.Lpu_EndDate, 104) as Lpu_EndDate,
						isnull(LpuLevel.LpuLevel_Code, 0) as LpuLevel_Code,
						Lpu.LpuType_Code
					FROM v_Lpu Lpu with (nolock)
						left join LpuLevel with (nolock) on LpuLevel.LpuLevel_id = Lpu.LpuLevel_id
				";
				if ( isset($order_by_field) )
				{
					$sql .= "
						ORDER BY {$order_by_field}
					";
				}
			break;
			case ( in_array($object, array('v_LpuSearch')) ):
				$sql = "
					SELECT distinct
						Lpu_id,
						Lpu_IsOblast,
						RTRIM(Lpu_Name) as Lpu_Name,
						RTRIM(Lpu_Nick) as Lpu_Nick,
						Lpu_Ouz,
						Lpu_RegNomC,
						Lpu_RegNomC2,
						Lpu_RegNomN2,
						convert(varchar(10), Lpu_DloBegDate, 104) as Lpu_DloBegDate,
						convert(varchar(10), Lpu_DloEndDate, 104) as Lpu_DloEndDate,
						convert(varchar(10), Lpu_BegDate, 104) as Lpu_BegDate,
						convert(varchar(10), Lpu_EndDate, 104) as Lpu_EndDate
					FROM v_Lpu (nolock)
				";
				if ( isset($order_by_field) )
				{
					$sql .= "
						ORDER BY {$order_by_field}
					";
				}
			break;
			case ( in_array($object, array('v_DrugRequestPeriod')) ):
				$sql = "
					SELECT 
						DrugRequestPeriod_id,
						RTRIM(DrugRequestPeriod_Name) as DrugRequestPeriod_Name,
						convert(varchar(10), DrugRequestPeriod_begDate, 104) as DrugRequestPeriod_begDate,
						convert(varchar(10), DrugRequestPeriod_endDate, 104) as DrugRequestPeriod_endDate
					FROM v_DrugRequestPeriod (nolock)
				";
			break;
			case ( in_array($object, array('v_DrugFinance')) ):
				$sql = "
					SELECT
						DrugFinance_id,
						DrugFinance_Code,
						RTRIM(DrugFinance_Name) as DrugFinance_Name,
						RTRIM(DrugFinance_SysNick) as DrugFinance_SysNick,
						convert(varchar(10), DrugFinance_begDate, 104) as DrugFinance_begDate,
						convert(varchar(10), DrugFinance_endDate, 104) as DrugFinance_endDate
					FROM v_DrugFinance (nolock)
				";
			break;
			case ( in_array($object, array('v_WhsDocumentCostItemType')) ):
				$sql = "
					SELECT
						WhsDocumentCostItemType_id,
						WhsDocumentCostItemType_Code,
						RTRIM(WhsDocumentCostItemType_Name) as WhsDocumentCostItemType_Name,
						RTRIM(WhsDocumentCostItemType_Nick) as WhsDocumentCostItemType_Nick,
						convert(varchar(10), WhsDocumentCostItemType_begDate, 104) as WhsDocumentCostItemType_begDate,
						convert(varchar(10), WhsDocumentCostItemType_endDate, 104) as WhsDocumentCostItemType_endDate,
						WhsDocumentCostItemType_IsDlo,
						DrugFinance_id,
						MorbusType_id
					FROM v_WhsDocumentCostItemType (nolock)
				";
			break;
			case ( in_array($object, array('v_SFPrehospDirect')) ):
				$sql = "
					SELECT
						PD.PrehospDirect_id as SFPrehospDirect_id,
						PD.PrehospDirect_Code as SFPrehospDirect_Code,
						PD.PrehospDirect_Name as SFPrehospDirect_Name,
						PD.PrehospDirect_SysNick as SFPrehospDirect_SysNick
					FROM v_PrehospDirect PD with (nolock)
				";
			break;
			case ( in_array($object, array('v_OMSSprTerrAddit')) ):
				$sql = "
					SELECT
					" . $fields . "
					FROM v_OMSSprTerr (nolock)
					WHERE " . $filter . "
				";
			break;
			case ( in_array($object, array('v_OrgSMO')) ):
				$sql = "
					SELECT
					OrgSMO_id,
					OrgSMO_RegNomC,
					OrgSMO_RegNomN,
					OrgSMO_Nick,
					OrgSMO_isDMS,
					KLRgn_id,
					convert(varchar(10), OrgSMO_endDate, 104) as OrgSMO_endDate
					FROM v_OrgSMO (nolock)
					WHERE " . $filter . "
				";
			break;
			case ( in_array($object, array('v_Usluga')) ):
				$sql = "
					SELECT
					us.Usluga_id,
					us.Usluga_pid,
					us.UslugaType_id,
					convert(varchar(10), us.Usluga_begDT, 104) as  Usluga_begDT,
					convert(varchar(10), us.Usluga_endDT, 104) as  Usluga_endDT,
					us.Usluga_Code,
					us.Usluga_Name,
					usc.UslugaCategory_id,
					usc.UslugaCategory_Code
					FROM v_Usluga us (nolock)
					LEFT JOIN UslugaCategory usc (nolock) on usc.UslugaCategory_id = us.UslugaCategory_id
					WHERE " . $filter . "
				";
			break;
			case ( in_array($object, array('v_PrivilegeType')) ):
				$sql = "
					SELECT
					PrivilegeType_id,
					PrivilegeType_Code,
					PrivilegeType_Name,
					ReceptDiscount_id,
					ReceptFinance_id,
					convert(varchar(10), PrivilegeType_begDate, 104) as PrivilegeType_begDate,
					convert(varchar(10), PrivilegeType_endDate, 104) as PrivilegeType_endDate
					FROM v_PrivilegeType (nolock)
					WHERE " . $filter . "
				";
			break;
			case ( in_array($object, array('v_rls_Countries')) ):
				$sql = "
					SELECT
					COUNTRIES_ID as RlsCountries_id,
					NAME as RlsCountries_Name
					FROM rls.v_COUNTRIES (nolock)
					WHERE " . $filter . "
				";
			break;
			case ( in_array($object, array('v_rls_Firms')) ):
				$sql = "
					SELECT
					FIRMS_ID as RlsFirms_id,
					FULLNAME as RlsFirms_Name
					FROM rls.v_FIRMS (nolock)
					WHERE FULLNAME != ''
				";
			break;
			case ( in_array($object, array('v_rls_Actmatters')) ):
				$sql = "
					SELECT
					ACTMATTERS_ID as RlsActmatters_id,
					RUSNAME as RlsActmatters_RusName
					FROM rls.v_ACTMATTERS (nolock)
					WHERE " . $filter . "
				";
			break;
			case ( in_array($object, array('v_rls_Desctextes')) ):
				$sql = "
					SELECT
					DESCID as RlsDesctextes_id,
					PHARMAACTIONS as RlsDesctextes_Code
					FROM rls.v_DESCTEXTES (nolock)
					WHERE 1=1 and PHARMAACTIONS IS NOT NULL and cast(cast(PHARMAACTIONS as varbinary(max)) as varchar(max)) != ''
				";
			break;
			case ( in_array($object, array('v_rls_Clspharmagroup')) ):
				$sql = "
					SELECT
					CLSPHARMAGROUP_ID as RlsPharmagroup_id,
					NAME as RlsPharmagroup_Name
					FROM rls.v_CLSPHARMAGROUP (nolock)
					WHERE " . $filter . " and CLSPHARMAGROUP_ID != 0
				";
			break;
			case ( in_array($object, array('v_rls_ClsMzPhgroup')) ):
				$sql = "
					SELECT
					CLS_MZ_PHGROUP_ID as RlsClsMzPhgroup_id,
					NAME as RlsClsMzPhgroup_Name
					FROM rls.v_CLS_MZ_PHGROUP with(nolock)
					WHERE " . $filter . " and CLS_MZ_PHGROUP_id <> 0
				";
			break;
			case ( in_array($object, array('v_rls_ClsPhGrLimp')) ):
				$sql = "
					SELECT
					CLS_PHGR_LIMP_ID as RlsClsPhGrLimp_id,
					PARENTID as RlsClsPhGrLimp_pid,
					NAME as RlsClsPhGrLimp_Name
					FROM rls.v_CLS_PHGR_LIMP (nolock)
					WHERE " . $filter . " and CLS_PHGR_LIMP_ID <> 0
				";
			break;
			case ( in_array($object, array('v_rls_Clsiic')) ):
				$sql = "
					SELECT
					CLSIIC_ID as RlsClsiic_id,
					NAME as RlsClsiic_Name
					FROM rls.v_CLSIIC (nolock)
					WHERE " . $filter . " and CLSIIC_ID != 0
				";
			break;
			case ( in_array($object, array('v_rls_Clsatc')) ):
				$sql = "
					SELECT
					CLSATC_ID as RlsClsatc_id,
					NAME as RlsClsatc_Name
					FROM rls.v_CLSATC (nolock)
					WHERE " . $filter . " and CLSATC_ID != 0
				";
			break;
			case ( in_array($object, array('v_rls_Clsdrugforms')) ):
				$sql = "
					SELECT
					CLSDRUGFORMS_ID as RlsClsdrugforms_id,
					FULLNAME as RlsClsdrugforms_Name
					FROM rls.v_CLSDRUGFORMS (nolock)
					WHERE " . $filter . " and CLSDRUGFORMS_ID != 0
				";
			break;
			case ( in_array($object, array('v_rls_Narcogroups')) ):
				$sql = "
					SELECT
					NARCOGROUPS_ID as RlsNarcogroups_id,
					NAME as RlsNarcogroups_Name
					FROM rls.v_NARCOGROUPS with(nolock)
					WHERE " . $filter . " and NARCOGROUPS_ID <> 0
				";
			break;
			case ( in_array($object, array('v_rls_Stronggroups')) ):
				$sql = "
					SELECT
					STRONGGROUPS_ID as RlsStronggroups_id,
					NAME as RlsStronggroups_Name
					FROM rls.v_STRONGGROUPS with(nolock)
					WHERE " . $filter . " and STRONGGROUPS_ID != 0
				";
			break;
			case ( in_array($object, array('v_rls_Tradenames')) ):
				$sql = "
					SELECT distinct
						RTrim(cast(TRADENAMES_ID as CHAR(10)))+RTrim(cast(ACTMATTERS.ACTMATTERS_ID as CHAR(10))) as Tradenames_id,
						TRADENAMES.TRADENAMES_ID as RlsTradenames_id,  
						TRADENAMES.NAME as RlsTorg_Name,
						ACTMATTERS.ACTMATTERS_ID as RlsSynonim_id
					FROM
						rls.v_TRADENAMES TRADENAMES (nolock)
						LEFT JOIN rls.v_PREP PREP (nolock) on PREP.TRADENAMEID = TRADENAMES.TRADENAMES_ID
						LEFT JOIN rls.PREP_ACTMATTERS PREP_ACTMATTERS (nolock) on PREP_ACTMATTERS.PREPID = PREP.Prep_id
						LEFT JOIN rls.v_ACTMATTERS ACTMATTERS (nolock) on ACTMATTERS.ACTMATTERS_ID = PREP_ACTMATTERS.MATTERID
					WHERE
						ACTMATTERS.ACTMATTERS_ID IS NOT NULL
					ORDER BY
						TRADENAMES.NAME
				";
			break;
			/*
			case ( in_array($object, array('v_Glossary')) ):
				$sql = "
					Select
						gl.Glossary_id,
						gl.GlossarySynonym_id,
						gl.GlossaryTagType_id,
						LOWER(gl.Glossary_Word) as Glossary_Word,
						isnull(gltt.GlossaryTagType_SysNick,'') as GlossaryTagType_SysNick,
						isnull(gl.Glossary_Descr,'') as Glossary_Descr,
						gl.pmUser_did
					from
						v_Glossary gl with (NOLOCK)
						LEFT JOIN v_GlossaryTagType gltt with (NOLOCK) on gl.GlossaryTagType_id = gltt.GlossaryTagType_id
					where
						gl.pmUser_did is null or gl.pmUser_did = {$data['session']['pmuser_id']} 
					order by
						gl.Glossary_Word
				";
			break;
			*/
			
			case ( in_array($object, array('v_ExpertiseNameType')) ): //вид экспертизы
				$sql = "
					select
						ExpertiseNameType_id,
						ExpertiseNameType_Name,
						ExpertiseNameType_SysNick
					from
						v_ExpertiseNameType (nolock)
				";
			break;
			
			case ( in_array($object, array('v_ExpertiseEventType')) ): //случай экспертизы
				$sql = "
					select
						ExpertiseEventType_id,
						ExpertiseEventType_Code,
						ExpertiseEventType_Name
					from
						v_ExpertiseEventType (nolock)
				";
			break;
			
			case ( in_array($object, array('v_PatientStatusType')) ): //статус пациента
				$sql = "
					select
						PatientStatusType_id,
						PatientStatusType_Name,
						PatientStatusType_SysNick
					from
						v_PatientStatusType (nolock)
				";
			break;
			
			case ( in_array($object, array('v_CauseTreatmentType')) ): //Причина обращения
				$sql = "
					select
						CauseTreatmentType_id,
						CauseTreatmentType_Name
					from
						v_CauseTreatmentType (nolock)
				";
			break;
			
			case ( in_array($object, array('v_ExpertiseNameSubjectType')) ): //Наименование предмета экспертизы
				$sql = "
					select
						ExpertiseNameSubjectType_id,
						ExpertiseNameSubjectType_Name
					from
						v_ExpertiseNameSubjectType (nolock)
				";
			break;
			
			case ( in_array($object, array('v_ExpertMedStaffType')) ): //Должность экспертов
				$sql = "
					select
						ExpertMedStaffType_id,
						ExpertMedStaffType_Name
					from
						v_ExpertMedStaffType (nolock)
				";
			break;
			
			case ( in_array($object, array('v_RecipientType')) ):
				$sql = "
					select
						RecipientType_id,
						RecipientType_Name
					from
						msg.v_RecipientType (nolock)
				";
			break;
			
			case ( in_array($object, array('v_NoticeType')) ):
				$sql = "
					select
						NoticeType_id,
						NoticeType_Name
					from
						msg.v_NoticeType (nolock)
				";
			break;
			
			case ( in_array($object, array('v_LpuBuilding')) ):
				if (!empty($data['isClose']) && $data['isClose'] == 1) {
					$filter .= " and (LpuBuilding_endDate is null or LpuBuilding_endDate > dbo.tzGetDate())";
				} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
					$filter .= " and LpuBuilding_endDate <= dbo.tzGetDate()";
				}
				$sql = "
					select
						LpuBuilding_id,
						LpuBuilding_Code,
						LpuBuilding_Name,
						convert(varchar(10), cast(LpuBuilding_begDate as datetime), 104) as LpuBuilding_begDate,
						convert(varchar(10), cast(LpuBuilding_endDate as datetime), 104) as LpuBuilding_endDate,
						Lpu_id
					from
						v_LpuBuilding (nolock)
					WHERE " . $filter . "
				";
			break;
			
			case ( in_array($object, array('v_LpuRegion')) ):
				if (!empty($data['isClose']) && $data['isClose'] == 1) {
					$filter .= " and (LpuRegion_endDate is null or LpuRegion_endDate > dbo.tzGetDate())";
				} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
					$filter .= " and LpuRegion_endDate <= dbo.tzGetDate()";
				}
				$sql = "
					select
						LpuRegion_id,
						LpuRegion_Name,
						LpuRegion_tfoms,
						LpuRegion_Descr,
						LpuRegionType_id,
						LpuSection_id,
						convert(varchar(10), LpuRegion_begDate, 104) as LpuRegion_begDate,
						convert(varchar(10), LpuRegion_endDate,  104) as LpuRegion_endDate,
						Lpu_id
					from
						v_LpuRegion (nolock)
					WHERE " . $filter . "
				";
			break;
			
			case ( in_array($object, array('v_LpuUnit')) ):
				if (!empty($data['isClose']) && $data['isClose'] == 1) {
					$filter .= " and (LpuUnit_endDate is null or LpuUnit_endDate > dbo.tzGetDate())";
				} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
					$filter .= " and LpuUnit_endDate <= dbo.tzGetDate()";
				}
				$sql = "
					select
						LpuUnit_id,
						LpuUnit_Code,
						LpuUnit_Name,
						convert(varchar(10), cast(LpuUnit_begDate as datetime), 104) as LpuUnit_begDate,
						convert(varchar(10), cast(LpuUnit_endDate as datetime), 104) as LpuUnit_endDate,
						Lpu_id,
						LpuBuilding_id
					from
						v_LpuUnit (nolock)
					WHERE " . $filter . "
				";
			break;
			
			case ( in_array($object, array('v_LpuSectionProfile')) ):
				$sql = "
					SELECT
						LpuSectionProfile_id,
						LpuSectionProfile_Code,
						LpuSectionProfile_Name,					
						convert(varchar(10), cast(LpuSectionProfile_begDT as datetime), 104) as LpuSectionProfile_begDT,
						convert(varchar(10), cast(LpuSectionProfile_endDT as datetime), 104) as LpuSectionProfile_endDT
					FROM 
						v_LpuSectionProfile (nolock)
					WHERE " . $filter . "
				";
			break;
			
			case ( in_array($object, array('v_Post')) ):
				// если задана текущая должность то ищем её без фильтра по Server_id.
				if (!empty($data['Post_curid'])) {
					$filter = "({$filter}) or Post_id = {$data['Post_curid']}";
				}
				 
				$sql = "
					SELECT
						Post_id,
						Post_Name,
						Server_id
					FROM " . $object . " (nolock)
					WHERE " . $filter . "
				";
			break;

			case ( 'v_LpuSection' == $object ):
				$filter = '(1 = 1)';
				if (!empty($data['LpuSection_id']) && is_numeric($data['LpuSection_id']) ) {
					$filter .= " and LS.LpuSection_id = {$data['LpuSection_id']}";
				} else {
					if (!empty($data['filterLpu_id']) && is_numeric($data['filterLpu_id']) ) {
						$filter .= " and LS.Lpu_id = {$data['filterLpu_id']}";
					} elseif (!empty($data['Lpu_id']) && is_numeric($data['Lpu_id']) ) {
						$filter .= " and LS.Lpu_id = {$data['Lpu_id']}";
					}
					if (!empty($data['LpuUnit_id']) && is_numeric($data['LpuUnit_id']) ) {
						$filter .= " and LS.LpuUnit_id = {$data['LpuUnit_id']}";
					}

                    if (!empty($data['LpuBuilding_id']) && is_numeric($data['LpuBuilding_id']) ) {
                        $filter .= " and LS.LpuBuilding_id = {$data['LpuBuilding_id']}";
                    }

					if (!empty($data['LpuSection_Code']) && is_numeric($data['LpuSection_Code']) ) {
						$filter .= " and LS.LpuSection_Code = {$data['LpuSection_Code']}";
					}
					if (!empty($data['LpuSection_Name']) && ($LpuSection_Name = strtr($data['LpuSection_Name'], array("'"=>'',"_"=>'','%'=>''))) ) {
						$filter .= " and LS.LpuSection_Name like '{$LpuSection_Name}%'";
					}
					if (!empty($data['isStac'])) {
						//$filter .= " and LU.LpuUnitType_SysNick in ('stac', 'dstac', 'hstac', 'pstac')";
						$filter .= " and LU.LpuUnitType_SysNick like 'stac'";
					}
					if ( !empty($data['onDate']) && ($on_date = strtotime($data['onDate'])) ) {
						$on_date = date('Y-m-d', $on_date);
						$filter .= " and LS.LpuSection_setDate < CAST('{$on_date}' as datetime)
						and (
							LS.LpuSection_disDate is null OR
							LS.LpuSection_disDate > CAST('{$on_date}' as datetime)
						)";
					}
					if ( !empty($data['LpuSection_maxSetDate']) ) {
						$date_str = ConvertDateFormat($data['LpuSection_maxSetDate'], 'Y-m-d');
						$filter .= " and (
						    LS.LpuSection_setDate is null or
						    LS.LpuSection_setDate <= CAST('{$date_str}' as datetime)
						)";
					}
					if ( !empty($data['LpuSection_minDisDate']) ) {
						$date_str = ConvertDateFormat($data['LpuSection_minDisDate'], 'Y-m-d');
						$filter .= " and (
						    LS.LpuSection_disDate is null or
						    LS.LpuSection_disDate >= CAST('{$date_str}' as datetime)
						)";
					}
					if (!empty($data['IsPolka'])) {
						$filter .= " and LS.LpuSectionProfile_SysNick like 'polka'";
					}
				}
				if (!empty($data['LpuSection_disDate']) ) {
					$filter .= " and (LS.LpuSection_disDate is null OR LS.LpuSection_disDate> '{$data['LpuSection_disDate']}')";
				}

				$sql = "
					SELECT
						LS.LpuSection_id,
						LS.LpuSection_Code,
						LS.LpuSection_Name,
						LS.LpuSectionProfile_id,
						LS.Lpu_id,
						LS.LpuBuilding_id,
						LU.LpuUnit_id,
						LU.LpuUnit_Name,
						LUT.LpuUnitType_id,
						LUT.LpuUnitType_Code,
						LUT.LpuUnitType_SysNick
					FROM
						v_LpuSection LS with (nolock)
						inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
					WHERE
						{$filter}
					ORDER BY
						cast(LS.LpuSection_Code as int)
				";
				break;

			case ( $object === 'v_LpuFilial'):

				$where = null;
				$declareSelect = null;

				if ($data['LpuBuilding_id'] != null)
				{
					$declareSelect = "
					DECLARE
						@LB_begDate datetime,
						@LB_endDate datetime;
						
					SET @LB_endDate = (
						SELECT
							LpuBuilding_endDate
						FROM
							v_LpuBuilding with (nolock)
						WHERE
							LpuBuilding_id = {$data['LpuBuilding_id']});
							
					SET @LB_begDate = (
						SELECT
							LpuBuilding_begDate
						FROM
							v_LpuBuilding with (nolock)
						WHERE
							LpuBuilding_id = {$data['LpuBuilding_id']});
						";

					$where = 'AND 
					(
						(
							(LpuFilial_begDate < @LB_endDate OR @LB_endDate IS NULL) AND 
							(LpuFilial_endDate > @LB_begDate OR LpuFilial_endDate IS NULL)
						) OR 
						
						(@LB_begDate IS NULL AND @LB_endDate IS NULL)
					)';
				}

				$sql = "
				
					$declareSelect
					SELECT
						LF.LpuFilial_id,
						LF.LpuFilial_Name,
						LF.LpuFilial_Code
					FROM
						v_LpuFilial LF with (nolock)
					WHERE
						LF.Lpu_id = {$data['Lpu_id']}
						$where
				";

				break;
			
			default:
				$sql = "
					SELECT
					" . $fields . "
					FROM " . $object . "
					WHERE " . $filter . "
				";

		}
		//print $sql;die;
		$res = $this->db->query($sql);

		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}


	/**
	*  Получение списка диагнозов
	*/
	function loadDiagList($data) {
		if ($data['search_mode'] != 4) {
			if ( ($data['search_mode'] == 0) || (($data['Diag_id'] <= 0) && (strlen($data['query']) == 0))) {
				return false;
			}
		}

		$filter = "";
		$queryParams = array();
		$joins = "";
		$selects = "";
		if (!empty($data['formMode']) && $data['formMode'] == 'DopDispQuestion') {
			$filter .= " and ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code < 'C98') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code < 'D10')) ";
		}
		if (empty($data['filterDate'])) {
			$data['filterDate'] = date('Y-m-d');
		}
		switch ( $data['search_mode'] ) {
			case 1:
				$filter .= " and Diag.Diag_id = :Diag_id";
				$queryParams['Diag_id'] = $data['Diag_id'];
				break;

			case 2:
				$filter .= " and Diag.Diag_Code LIKE :Diag_Code";
				$queryParams['Diag_Code'] = $data['query'] . "%";
				break;

			case 3:
				$filter .= " and Diag.Diag_FullName LIKE :Diag_Name";
				$queryParams['Diag_Name'] = "%" . $data['query'] . "%";
				if (!empty($data['PersonRegisterType_SysNick'])) {
					if ($data['PersonRegisterType_SysNick'] == 'nolos') {
						$filter .= " and Diag_Code not like 'E75.5'";
					}
					$filter .= " and exists(
						select top 1 v_PersonRegisterDiag.Diag_id 
						from dbo.v_PersonRegisterDiag (nolock)
						inner join dbo.v_PersonRegisterType (nolock) on v_PersonRegisterType.PersonRegisterType_id = isnull(v_PersonRegisterDiag.PersonRegisterType_id,1)
						      	  and v_PersonRegisterType.PersonRegisterType_SysNick = :PersonRegisterType_SysNick
						where v_PersonRegisterDiag.Diag_id = Diag.Diag_id
					)
					";
					$queryParams['PersonRegisterType_SysNick'] = $data['PersonRegisterType_SysNick'];
				}
				if (!empty($data['MorbusType_SysNick'])) {
					if ($data['MorbusType_SysNick'] == 'vzn') {
						$filter .= " and Diag_Code not like 'E75.5'";
					}
					$filter .= " and exists(
						select top 1 v_MorbusDiag.Diag_id
						from dbo.v_MorbusDiag (nolock)
						inner join dbo.v_MorbusType (nolock) on v_MorbusType.MorbusType_id = isnull(v_MorbusDiag.MorbusType_id,1)
								  and v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
						where v_MorbusDiag.Diag_id = Diag.Diag_id
					)
					";
					$queryParams['MorbusType_SysNick'] = $data['MorbusType_SysNick'];
				}
				if ($data['MorbusProfDiag_id'] > 0 ) {
					$MorbusProfDiag_Code = $this->getFirstResultFromQuery("SELECT MorbusProfDiag_Code FROM v_MorbusProfDiag (nolock) where MorbusProfDiag_id = :MorbusProfDiag_id", $data);
					if (!empty($MorbusProfDiag_Code)) {
						$filter .= " and exists(select top 1 MorbusProfDiag_id from v_MorbusProfDiag (nolock) where Diag_id = Diag.Diag_id and MorbusProfDiag_Code = :MorbusProfDiag_Code)";
						$queryParams['MorbusProfDiag_Code'] = $MorbusProfDiag_Code;
					}
				}
				break;

			case 4:
				$filter .= " and Diag.Diag_id in (select [sd].[Diag_id] from [v_SicknessDiag] [sd] inner join [v_Sickness] [ps] on [ps].[PrivilegeType_id] = [sd].[PrivilegeType_id] where [ps].[Sickness_id] = :PersonSickness_id)";
				$queryParams['PersonSickness_id'] = $data['PersonSickness_id'];
				break;
		}

		$diaglevelfilter = "Diag.DiagLevel_id = 4";
		
		if (!empty($data['isEvnDiagDopDispDiag']) && $data['isEvnDiagDopDispDiag'] === true) {
			$filter .= " and (Diag.Diag_Code in ('Z03.4', 'I20.9', 'I67.9', 'O24.3', 'K29.7', 'N28.8', 'A16.2', 'I64.') or left(Diag.Diag_Code, 1) = 'C')";
		}
		
		if (!empty($data['isHeredityDiag']) && $data['isHeredityDiag'] === true) {
			$filter .= " and Diag.Diag_Code in ('Z03.4', 'I64.', 'C16.9')";
		}
		
		if (!empty($data['isInfectionAndParasiteDiag']) && $data['isInfectionAndParasiteDiag'] === true) {
			$filter .= " and left(Diag.Diag_Code, 1) in ('A', 'B')";
		}
		
		if (!empty($data['withGroups']) && $data['withGroups'] == true) {
			$diaglevelfilter = "Diag.DiagLevel_id IN (3,4)";
		}

		

		if (!empty($data['filterDiag'])) {
			$data['filterDiag'] = json_decode($data['filterDiag']);
			$data['filterDiag'] = array_diff($data['filterDiag'], array(''));
			$filter .= " and Diag.Diag_id in (".implode(',',$data['filterDiag']).")";
		}

		$filter .= " and (Diag.Diag_endDate is null or Diag.Diag_endDate >= :filterDate)";
		$queryParams['filterDate'] = $data['filterDate'];

		if (!empty($data['registryType'])) {
			switch($data['registryType']){
				case 'NarkoRegistry':
					$filter .= " and Diag.Diag_Code like 'F1%'";
					break;
				case 'CrazyRegistry':
					$filter .= " and Diag.Diag_Code not like 'F1%'";
					break;
				case 'Fmba':
					$filter .= " and Diag.Diag_Code like 'Z57%'";
					break;
				default :
					$filter .= "";
					break;
			}
			
		}
		
		if (!empty($data['MKB'])) {
			$filter .= "
				and (Diag.Mkb10Cause_id is null or Diag.Mkb10Cause_id not in ".$data['MKB'].")";
		}
		if(!empty($data['isMain'])&&$data['isMain']){
			$joins.="left join Mkb10CauseLink mkbd with(nolock) on mkbd.Diag_id=Diag.Diag_id
				";
			$filter .= " 
			and (mkbd.Mkb10Cause_id!=6 or mkbd.Mkb10Cause_id is null)";
		}
		
		if(!empty($data['deathDiag'])) {
			$joins.="left join v_DeathDiag DeathDiag with(nolock) on DeathDiag.Diag_id = Diag.Diag_id";
			$selects.=",DeathDiag.DeathDiag_IsLowChance";
			$data['deathDiag'] = json_decode($data['deathDiag'], 1);
			if (isset($data['deathDiag']['Person_Age']) && $data['deathDiag']['Person_Age'] > 0) {
				$filter .= " and (isnull(DeathDiag.DeathDiag_YearFrom, 0) <= :DDPerson_Age or isnull(DeathDiag.DeathDiag_YearTo, 200) >= :DDPerson_Age)";
				$queryParams['DDPerson_Age'] = $data['deathDiag']['Person_Age'];
			}
			if (isset($data['deathDiag']['Sex_id']) && $data['deathDiag']['Sex_id'] > 0) {
				$filter .= " and (DeathDiag.Sex_id = :DDSex_id or DeathDiag.Sex_id is null)";
				$queryParams['DDSex_id'] = $data['deathDiag']['Sex_id'];
			}
		}

		if (!empty($data['checkAccessRights']) && $data['checkAccessRights']) {
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
		}

		$filterDate = "@curDate";
		if (!empty($data['filterDate'])) {
			$filterDate = ":filterDate";
			$queryParams['filterDate'] = $data['filterDate'];
		}
		
		$query = "
			declare @curDate datetime = dbo.tzGetDate();

			SELECT distinct TOP 101
				Diag.Diag_id,
				RTRIM(Diag.Diag_Code) as Diag_Code,
				RTRIM(Diag.Diag_Name) as Diag_Name,
				STUFF(
					(SELECT
						','+v_PersonRegisterType.PersonRegisterType_SysNick
					FROM
						v_PersonRegisterDiag WITH (nolock)
						inner join v_PersonRegisterType WITH (nolock) on v_PersonRegisterType.PersonRegisterType_id = v_PersonRegisterDiag.PersonRegisterType_id
					WHERE
						v_PersonRegisterDiag.Diag_id = Diag.Diag_id
					FOR XML PATH ('')
					), 1, 1, ''
				) as PersonRegisterType_List,
				MorbusType_List,
				IsNull(IsOms.YesNo_Code, 1) as DiagFinance_IsOms
				{$selects}
			FROM v_Diag Diag WITH (NOLOCK)
			LEFT JOIN v_DiagFinance DF with (nolock) on DF.Diag_id = Diag.Diag_id
			left join YesNo IsOms with (nolock) on IsOms.YesNo_id = DF.DiagFinance_IsOms
			outer apply(
			select STUFF(
					(SELECT
						','+v_MorbusType.MorbusType_SysNick
					FROM
						v_MorbusDiag WITH (nolock)
						inner join v_MorbusType WITH (nolock) on v_MorbusType.MorbusType_id = v_MorbusDiag.MorbusType_id
					WHERE
						v_MorbusDiag.Diag_id = Diag.Diag_id
					FOR XML PATH ('')
					), 1, 1, ''
				) as MorbusType_List
			)MTL
			".$joins."
			WHERE " . $diaglevelfilter . "
				" . $filter . "
				" . ( (in_array($data['search_mode'],array(2,3,4)) && in_array(getRegionNumber(),array(2,59)) )?' and cast(isnull(Diag.Diag_endDate,'.$filterDate.') as date) >= cast('.$filterDate.' as date)':'') . "
				" . ( (in_array($data['search_mode'],array(2,3,4)) && in_array(getRegionNumber(),array(2,59)) )?' and cast(isnull(Diag.Diag_begDate,'.$filterDate.') as date) <= cast('.$filterDate.' as date)':'') . "
			ORDER by Diag_Code
		";
		
		 //echo getDebugSQL($query, $queryParams); exit();
		
		$result = $this->db->query($query, $queryParams);

		if ( false == is_object($result) ) {
			return false;
		}
		return $result->result('array');
	}


	/**
	 * Загрузка списка услуг
	 */
	function loadUslugaList($data) {
		$filter = "";
		$queryParams = array();
		
		if ( isset($data['allowedCatCode']) && strlen($data['allowedCatCode']) > 0 )
		{
			$filter .= " and UslugaCategory_Code = :allowedCatCode ";
			$queryParams['allowedCatCode'] = $data['allowedCatCode'];
			if ( $data['allowedCatCode'] == 1 )				
				$filter .= "
					and (
						(left(Usluga_Code, 1) = 'A' and len(replace(Usluga_Code, '.', '')) in (8, 11))
						or (left(Usluga_Code, 1) = 'B' and len(replace(Usluga_Code, '.', '')) in (8, 9))
						or (left(Usluga_Code, 1) = 'D' and len(replace(Usluga_Code, '.', '')) in (7, 9, 10, 11))
						or (left(Usluga_Code, 1) = 'F' and len(replace(Usluga_Code, '.', '')) in (7, 9))
					)
				";
		}
		
		if ( isset($data['allowedCodeList']) && strlen($data['allowedCodeList']) > 0 )
		{
			$filter .= " and ( Usluga_Code in ({$data['allowedCodeList']}) ) ";
		}

		switch ( $data['search_mode'] ) {
			case 1:
				$filter .= " and Usluga_id = :Usluga_id";
				$queryParams['Usluga_id'] = $data['Usluga_id'];
			break;

			case 2:
				$filter .= " and Usluga_Code LIKE :Usluga_Code";
				$queryParams['Usluga_Code'] = $data['Usluga_Code'] . "%";
			break;

			case 3:
				if ( !empty($data['Usluga_Code']) ) {
					$filter .= " and Usluga_Code LIKE :Usluga_Code";
					$queryParams['Usluga_Code'] = $data['Usluga_Code'] . "%";
				}

				if ( !empty($data['Usluga_Name']) ) {
					$filter .= " and Usluga_Name LIKE :Usluga_Name";
					$queryParams['Usluga_Name'] = "%" . $data['Usluga_Name'] . "%";
				}
			break;

			default:
				return false;
				break;
		}
		/*
		* При поиске услуги по коду или наименованию (при добавлении услуги)
		* Если передан параметр Usluga_date, то выводим только те услуги, которые действительны на указанную дату оказания услуги.
		*/
		if ( isset($data['Usluga_date']) && in_array($data['search_mode'], array(2,3)))
		{
			//$filter .= " AND (Usluga_endDT is null OR cast(Usluga_endDT as date) > cast(:Usluga_date as date))";
			$filter .= " AND ( (Usluga_begDT is null OR (cast(Usluga_begDT as date) <= cast(:Usluga_date as date))) AND (Usluga_endDT is null OR cast(Usluga_endDT as date) >= cast(:Usluga_date as date)) )";
			$queryParams['Usluga_date'] = $data['Usluga_date'];
		}

		/*$query = "
			SELECT TOP 100
        		Usluga_id,
				RTRIM(Usluga_Code) as Usluga_Code,
				RTRIM(Usluga_Name) as Usluga_Name
			FROM v_Usluga with(nolock)
			LEFT JOIN UslugaCategory  with(nolock)on UslugaCategory.UslugaCategory_id = v_Usluga.UslugaCategory_id
			WHERE (1 = 1) " . $filter . "
		";*/

		$query = "
  			SELECT DISTINCT TOP 100
        		Usluga.Usluga_id,
				RTRIM(Usluga_Code) as Usluga_Code,
				RTRIM(Usluga_Name) as Usluga_Name,
				UslugaPriceList.Usluga_Price as Usluga_Price
			FROM v_Usluga Usluga with (nolock)
			LEFT JOIN UslugaCategory with (nolock) on UslugaCategory.UslugaCategory_id = Usluga.UslugaCategory_id
			outer apply (
				Select top 1 
					UslugaPriceList.UslugaPriceList_ue as Usluga_Price
				from UslugaPriceList with (nolock)
				where  UslugaPriceList.Usluga_id = Usluga.Usluga_id
				order by IsNull(UslugaPriceList_endDate, dbo.tzGetDate()) desc -- если значение даты окончания пустое, то эта строка нам подойдет
			) as UslugaPriceList
			WHERE (1=1) " . $filter . "
		";
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Аудит записей
	 * Входящие данные: $_POST['key_id'],
	 *                  $_POST['key_field']
	 * На выходе: JSON-строка
	 * Используется: компонент AuditWindow
	 */
	function getAudit ($data) {
		if ( strpos( mb_strtolower( $data['key_field'] ), '_id' ) !==false ) {
			$table_name = mb_substr($data['key_field'], 0, strpos( mb_strtolower( $data['key_field'] ), '_id' ));
		} else {
			return array(0 => array('success' => false, 'Error_Msg' => 'Неверный параметр запроса'));
		}

		$database = 'default';
		$deleted = !empty($data['deleted']);
		$prefix = '';
		$suffix = '';
		$view = 'v_';

		if ( !empty($data['schema']) ) {
			$schemaList = $this->queryResult("select [name] from sys.schemas", array());

			if ( $schemaList === false ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при получении списка допустимых схем'));
			}

			$schemaIsGood = false;
			
			foreach ( $schemaList as $row ) {
				if ( $row['name'] == $data['schema'] ) {
					$schemaIsGood = true;
					break;
				}
			}

			if ( $schemaIsGood === false ) {
				return array(array('success' => false, 'Error_Msg' => 'Указана недопустимая схема'));
			}

			$prefix = $data['schema'] . '.';
		}

		if (in_array($table_name,array('DrugNomen'))) {
			$prefix = 'rls.';
		}
		else if (in_array($table_name,array('AnalyzerTest'))) {
			$prefix = 'lis.';
		}
		else if (in_array($table_name,array('PersonDoubles'))) {
			$prefix = 'pd.';
		}
		else if (in_array($table_name,array('MOArea'))) {
			$prefix = 'fed.';
		}
		else if ($table_name == 'pmUser') {
			$table_name = 'pmUserCache';
		}
		else if (in_array($table_name,array('Amortization', 'Consumables', 'Downtime', 'MeasureFundCheck', 'MedProductCard', 'WorkData', 'TransportConnect'))) {
			$prefix = 'passport.';
		}
		elseif (in_array($table_name,array('Staff'))) {
			$data['key_field'] = 'id';
			$prefix = 'persis.';
		}
		else if (in_array($table_name,array('Registry','RegistryError','RegistryErrorTFOMS'))) {
			$config = get_config();
			$prefix = $config['regions'][getRegionNumber()]['schema'] . '.';
			$database = 'registry';
		}
		else if(in_array($table_name, array('RegisterList','RegisterListLog','RegisterListDetailLog'))) {
			$prefix = 'stg.';
			$view = '';
		}
		else if(in_array($table_name, array('vacData4Card063'))) {
			$prefix = 'vac.';
			$view = '';
		}

		$db = $this->load->database($database, true);

		//Проверяем что переданная таблица есть в базе
		$params = array();
		$query = "
			select 
				object_id
			from sys.objects (nolock)
			where
				name = :TableName and type = 'U'
		";
		$params['TableName'] = $table_name;
		if($table_name == 'vacJournalAccount') //Костылина для https://redmine.swan.perm.ru/issues/84283
		{
			$params['TableName'] = 'vac_JournalAccount';
			$table_name = 'JournalAccount';
			$prefix = 'vac.';
		}
		if(($table_name == 'ReportCatalog') || ($table_name == 'Report'))
		{
			$prefix = 'rpt.';
		}
		$result = $db->query(
			$query,
			$params
		);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if (count($res) == 0) { // таблицы нет в базе
				if($table_name != 'JournalVacFixed' && $table_name != 'PlanTuberkulin' && $table_name != 'JournalMantuFixed' && $table_name != 'vacJournalMedTapRefusal' && $table_name != 'planTmp')
				return array(0 => array('success' => false, 'Error_Msg' => 'Заданного объекта не существует в базе.'));
			}
		} else {
			return false;
		}

		$params = array(
			'ID' => $data['key_id']
		);

		$pmUserInsField = 'e.pmUser_insID';
		$pmUserUpdField = 'e.pmUser_updID';
		$InsDateField = "e.{$table_name}_insDT";
		$UpdDateField = "e.{$table_name}_updDT";
		
		if(in_array($table_name, array("Inoculation"))) {
			$prefix = 'vac.';
			$view = '';
			$pmUserInsField = $pmUserUpdField;
		}

		$addit_filter = '';
		$joins = '';
		// для PersonEvn необходимо указывать Server_id в фильтре для быстрого выполнения запроса
		if ( $table_name == 'PersonEvn' && isset($data['Server_id']) )
		{
			$addit_filter .= ' and e.Server_id = :Server_id ';
			$params['Server_id'] = $data['Server_id'];
		}
		else if ( $table_name == 'PersonCard' )
		{
			$suffix = "_all";
			$pmUserInsField = 'e.pmUserBeg_insID';
			$pmUserUpdField = 'ISNULL(e.pmUserEnd_updID, e.pmUserBeg_updID)';
			$InsDateField = "pc.PersonCard_insDT";
			$UpdDateField = "ISNULL(e.PersonCardEnd_updDT, e.PersonCardBeg_updDT)";
			$joins.="left join PersonCard PC with(nolock) on e.Person_id = PC.Person_id and e.Lpu_id = PC.Lpu_id 
                    and isnull(e.PersonCard_Code,'~') = isnull(PC.PersonCard_Code,'')
                    and e.LpuAttachType_id = PC.LpuAttachType_id
                    and cast(e.PersonCard_begDate as date) = cast(PC.PersonCard_begDate as date)
                    --and cast(PersonCard.PersonCard_begDate as date) <= cast(PC.PersonCard_endDate as date)
                    and (PC.OrgSMO_id=e.OrgSMO_id or (PC.OrgSMO_id is null and e.OrgSMO_id is null))
                    and PC.PersonCard_endDate is null
                    and (isnull(e.LpuRegion_id,0) = isnull(PC.LpuRegion_id,0) 
                    or (e.LpuRegion_id is null and PC.LpuRegion_id is not null)
                    or (e.LpuRegion_id is not null and PC.LpuRegion_id is null)
                    )
                    and isnull(e.PersonCardAttach_id,0) = isnull(PC.PersonCardAttach_id,0)";
		}
		else if ( $table_name == 'EvnDirection' )
		{
			$suffix = "_all";
		}
		else if ( $table_name == 'Registry' && $deleted == true )
		{
			$suffix = "_deleted";
		}
		
		if ( $table_name == 'PersonEvn' ) {
			$policies = "p.Polis_insDT";
			$query = "
				select
					rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as InspmUser,
					rtrim(isnull(l2.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu2.pmUser_Name,'')) + ' ('+rtrim(isnull(pu2.pmUser_login,''))+')' as UpdpmUser,
					convert(varchar(10), {$UpdDateField}, 104)+ ' ' + convert(varchar(5), {$UpdDateField}, 108) as UpdDate,
					convert(varchar(10), isnull({$policies},{$InsDateField}), 104)+ ' ' + convert(varchar(5), isnull({$policies},{$InsDateField}), 108) as InsDate
				from {$prefix}v_{$table_name}{$suffix} e (nolock)
					left join v_pmUser pu1 (nolock) on {$pmUserInsField} = pu1.pmUser_id
					left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
					left join v_pmUser pu2 (nolock) on {$pmUserUpdField} = pu2.pmUser_id
					left join v_Lpu l2 (nolock) on pu2.Lpu_id = l2.Lpu_id
					left join v_PersonPolis pp (nolock) on e.PersonEvn_id = pp.PersonPolis_id
					left join v_Polis p (nolock) on p.Polis_id = pp.Polis_id
					left join v_PersonPolisEdNum ppe (nolock) on e.PersonEvn_id = ppe.PersonPolisEdNum_id
				where
					e.{$data['key_field']} = :ID
					{$addit_filter}
			";
		}
		else {
			if($table_name == 'RegistryError') //Обрабатываем отдельно, т.к. в v_RegistryError нет RegistryError_id (https://redmine.swan.perm.ru/issues/59687)
			{
				$data['key_field'] = "RTrim(cast(e.Registry_id as char))+RTrim(cast(IsNull(e.Evn_id,0) as char))+RTrim(cast(e.RegistryErrorType_id as char)) = :ID";
				//И отдельно для Перми, т.к. в зависимости от типа реестра нужно брать разные вьюхи
				if(isset($data['registry_id']) && ($data['registry_id']>0)){
					$paramsRegType = array();
					$queryRegType = "
						select top 1 RegistryType_id
						from {$prefix}v_Registry with (nolock)
						where Registry_id = :registry_id
					";
					$paramsRegType['registry_id'] = $data['registry_id'];
					$resRegType = $db->query($queryRegType,$paramsRegType);
					if(is_object($resRegType)){
						$respRegType = $resRegType->result('array');
						switch($respRegType[0]['RegistryType_id']){
							case 1:
							case 14:
								$suffix = 'EvnPS';
								break;
							case 2:
								$suffix = '';
								break;
							case 6:
								$suffix = 'Cmp';
								break;
							case 4:
							case 5:
							case 7:
							case 9:
								$suffix = 'Disp';
								break;
							case 11:
							case 12:
								$suffix = 'Prof';
								break;
							case 15:
								$suffix = 'Par';
								break;
						}
					}
				}
			}
			else
				$data['key_field'] = "e.{$data['key_field']} = :ID";

			if($table_name == 'JournalVacFixed' || $table_name == 'JournalAccount'){
				$query = "
					select
						rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as InspmUser,
						rtrim(isnull(l2.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu2.pmUser_Name,'')) + ' ('+rtrim(isnull(pu2.pmUser_login,''))+')' as UpdpmUser,
						convert(varchar(10), ISNULL(e.vacJournalAccount_VacDateSave,e.vacJournalAccount_DatePurpose), 104) as UpdDate,
						convert(varchar(10), ISNULL(e.vacJournalAccount_VacDateSave,e.vacJournalAccount_DatePurpose), 104) as InsDate
					from vac.vac_JournalAccount e (nolock)
						left join v_pmUser pu1 (nolock) on ISNULL(e.vacJournalAccount_Vac_PmUser_id,e.vacJournalAccount_Purpose_PmUser_id) = pu1.pmUser_id
						left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
						left join v_pmUser pu2 (nolock) on ISNULL(e.vacJournalAccount_Vac_PmUser_id,e.vacJournalAccount_Purpose_PmUser_id) = pu2.pmUser_id
						left join v_Lpu l2 (nolock) on pu2.Lpu_id = l2.Lpu_id
					where
						e.vacJournalAccount_id = :ID
				";
			}
			else if ($table_name == 'JournalMantuFixed'){
				$query = "
					select
						rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as InspmUser,
						rtrim(isnull(l2.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu2.pmUser_Name,'')) + ' ('+rtrim(isnull(pu2.pmUser_login,''))+')' as UpdpmUser,
						convert(varchar(10), ISNULL(e.JournalMantu_VacDateSave,e.JournalMantu_DatePurpose), 104) as UpdDate,
						convert(varchar(10), ISNULL(e.JournalMantu_VacDateSave,e.JournalMantu_DatePurpose), 104) as InsDate
					from vac.vac_JournalMantu e (nolock)
						left join v_pmUser pu1 (nolock) on ISNULL(e.JournalMantu_vac_pmUser_id,e.JournalMantu_Purpose_Pmuser_id) = pu1.pmUser_id
						left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
						left join v_pmUser pu2 (nolock) on ISNULL(e.JournalMantu_vac_pmUser_id,e.JournalMantu_Purpose_Pmuser_id) = pu2.pmUser_id
						left join v_Lpu l2 (nolock) on pu2.Lpu_id = l2.Lpu_id
					where
						e.JournalMantu_id = :ID
				";
			}
			else if ($table_name == 'vacJournalMedTapRefusal'){
				$query = "
					select
						rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as InspmUser,
						rtrim(isnull(l2.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu2.pmUser_Name,'')) + ' ('+rtrim(isnull(pu2.pmUser_login,''))+')' as UpdpmUser,
						convert(varchar(10), e.vacJournalMedTapRefusal_updDT, 104) as UpdDate,
						convert(varchar(10), e.vacJournalMedTapRefusal__insDT, 104) as InsDate
					from vac.vac_JournalMedTapRefusal e (nolock)
						left join v_pmUser pu1 (nolock) on e.pmUser_insID = pu1.pmUser_id
						left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
						left join v_pmUser pu2 (nolock) on ISNULL(e.pmUser_updID,e.pmUser_insID) = pu2.pmUser_id
						left join v_Lpu l2 (nolock) on pu2.Lpu_id = l2.Lpu_id
					where
						e.vacJournalMedTapRefusal_id = :ID
				";
			}
			else if ($table_name == 'planTmp'){
				$query = "
					select
						rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as InspmUser,
						rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as UpdpmUser,
						convert(varchar(10), e.vac_PersonPlanFinal_insDT, 104) as UpdDate,
						convert(varchar(10), e.vac_PersonPlanFinal_insDT, 104) as InsDate
					from vac.vac_PersonPlanFinal e (nolock)
						left join v_pmUser pu1 (nolock) on e.pmUser_insID = pu1.pmUser_id
						left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
					where
						e.vac_PersonPlanFinal_id = :ID
				";
			}
			else if ($table_name == 'ReportCatalog')
			{
				$query = "
					select
						rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as InspmUser,
						rtrim(isnull(l2.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu2.pmUser_Name,'')) + ' ('+rtrim(isnull(pu2.pmUser_login,''))+')' as UpdpmUser,
						convert(varchar(10), e.ReportCatalog_updDT, 104)+ ' ' + convert(varchar(5), e.ReportCatalog_updDT, 108) as UpdDate,
						convert(varchar(10), e.ReportCatalog_insDT, 104)+ ' ' + convert(varchar(5), e.ReportCatalog_insDT, 108) as InsDate
					from rpt.ReportCatalog e (nolock)
						left join v_pmUser pu1 (nolock) on e.pmUser_insID = pu1.pmUser_id
						left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
						left join v_pmUser pu2 (nolock) on e.pmUser_updID = pu2.pmUser_id
						left join v_Lpu l2 (nolock) on pu2.Lpu_id = l2.Lpu_id
					where
						{$data['key_field']}
						{$addit_filter}
				";
			}
			else if ($table_name == 'Report')
			{
				$query = "
					select
						rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as InspmUser,
						rtrim(isnull(l2.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu2.pmUser_Name,'')) + ' ('+rtrim(isnull(pu2.pmUser_login,''))+')' as UpdpmUser,
						convert(varchar(10), e.Report_updDT, 104)+ ' ' + convert(varchar(5), e.Report_updDT, 108) as UpdDate,
						convert(varchar(10), e.Report_insDT, 104)+ ' ' + convert(varchar(5), e.Report_insDT, 108) as InsDate
					from rpt.Report e (nolock)
						left join v_pmUser pu1 (nolock) on e.pmUser_insID = pu1.pmUser_id
						left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
						left join v_pmUser pu2 (nolock) on e.pmUser_updID = pu2.pmUser_id
						left join v_Lpu l2 (nolock) on pu2.Lpu_id = l2.Lpu_id
					where
						{$data['key_field']}
						{$addit_filter}
				";
			}
			else
			{
				if($table_name == 'vac_JournalAccount') //Костылина для https://redmine.swan.perm.ru/issues/84283
				{
					$table_name = 'JournalAccount';
					$pmUserInsField = 'e.vacJournalAccount_Vac_PmUser_id';
					$pmUserUpdField = 'e.vacJournalAccount_Vac_PmUser_id';
					$InsDateField = 'e.vacJournalAccount_VacDateSave';
					$UpdDateField = 'e.vacJournalAccount_VacDateSave';
				}
				if($table_name == 'PlanTuberkulin'){
					$prefix = 'vac.';
					$view = '';
					$table_name = 'vac_PlanTuberkulin';
					$UpdDateField = 'e.PlanTuberkulin_insDT';
					$InsDateField = 'e.PlanTuberkulin_insDT';
					$pmUserInsField = 'e.pmUser_insID';
					$pmUserUpdField = 'e.pmUser_insID';
				}
				if($table_name == 'CmpCallCard'){
					$view = '';
				}
				if($table_name == 'EmergencyTeam'){
					$view = '';
				}
				$query = "
					select
						rtrim(isnull(l1.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu1.pmUser_Name,'')) + ' ('+rtrim(isnull(pu1.pmUser_login,''))+')' as InspmUser,
						rtrim(isnull(l2.Lpu_Nick,'')) + ', ' + rtrim(isnull(pu2.pmUser_Name,'')) + ' ('+rtrim(isnull(pu2.pmUser_login,''))+')' as UpdpmUser,
						convert(varchar(10), {$UpdDateField}, 104)+ ' ' + convert(varchar(5), {$UpdDateField}, 108) as UpdDate,
						convert(varchar(10), {$InsDateField}, 104)+ ' ' + convert(varchar(5), {$InsDateField}, 108) as InsDate
					from {$prefix}{$view}{$table_name}{$suffix} e (nolock)
						left join v_pmUser pu1 (nolock) on {$pmUserInsField} = pu1.pmUser_id
						left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
						left join v_pmUser pu2 (nolock) on {$pmUserUpdField} = pu2.pmUser_id
						left join v_Lpu l2 (nolock) on pu2.Lpu_id = l2.Lpu_id
						{$joins}
					where
						{$data['key_field']}
						{$addit_filter}
				";
			}
		}
        if (($table_name == 'PersonCard') && ($_SESSION['region']['nick'] == 'kareliya')){ //https://redmine.swan.perm.ru/issues/45742
            $query = "
            select
					case when PC.PersonCard_endDate is null then
						rtrim(l12.Lpu_Nick) + ', ' + rtrim(pu12.pmUser_Name)
					else
						rtrim(l1.Lpu_Nick) + ', ' + rtrim(pu1.pmUser_Name)
					end as InspmUser,

					rtrim(l2.Lpu_Nick) + ', ' + rtrim(pu2.pmUser_Name) as UpdpmUser,
					convert(varchar(10), ISNULL(e.PersonCardEnd_updDT, e.PersonCardBeg_updDT), 104)+ ' ' + convert(varchar(5), ISNULL(e.PersonCardEnd_updDT, e.PersonCardBeg_updDT), 108) as UpdDate,
					convert(varchar(10), pc.PersonCard_insDT, 104)+ ' ' + convert(varchar(5), pc.PersonCard_insDT, 108) as InsDate


				from v_PersonCard_all e (nolock)
					left join v_pmUser pu1 (nolock) on e.pmUserBeg_insID = pu1.pmUser_id
					left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
					left join v_pmUser pu2 (nolock) on ISNULL(e.pmUserEnd_updID, e.pmUserBeg_updID) = pu2.pmUser_id
					left join v_Lpu l2 (nolock) on pu2.Lpu_id = l2.Lpu_id
					left join PersonCard PC with(nolock) on e.Person_id = PC.Person_id and e.Lpu_id = PC.Lpu_id

					--left join PersonCard PC2 with(nolock) on PC2.Person_id = e.Person_id and PC2.PersonCard_id = (select top 1 PersonCard_id from PersonCard with(nolock)

                    and isnull(e.PersonCard_Code,'') = isnull(PC.PersonCard_Code,'')
                    and e.LpuAttachType_id = PC.LpuAttachType_id
                    and cast(e.PersonCard_begDate as date) = cast(PC.PersonCard_begDate as date)
                    --and cast(PersonCard.PersonCard_begDate as date) <= cast(PC.PersonCard_endDate as date)
                    and (PC.OrgSMO_id=e.OrgSMO_id or (PC.OrgSMO_id is null and e.OrgSMO_id is null))
                    and PC.PersonCard_endDate is null
                    and (isnull(e.LpuRegion_id,0) = isnull(PC.LpuRegion_id,0)
                    or (e.LpuRegion_id is null and PC.LpuRegion_id is not null)
                    or (e.LpuRegion_id is not null and PC.LpuRegion_id is null)
                    )
                    and isnull(e.PersonCardAttach_id,0) = isnull(PC.PersonCardAttach_id,0)

                    outer apply( --Если наше прикрепление закрыто, то данные о pmUser-е надо брать из таблицы, а не из вьюхи (иначе в pmUser попадет не тот юзер) - это из-за особенности хранения данных в PersonCard
						select top 1
							Pcard.PersonCard_id,
							MAX(Pcard.PersonCard_insDT) as PersonCard_insDT,
							Pcard.pmUser_insID,
							Pcard.pmUser_updID
						from PersonCard Pcard with(nolock)
						where Pcard.Person_id = e.Person_id
						and Pcard.PersonCard_insDT < e.PersonCardBeg_insDT
						group by Pcard.PersonCard_id,Pcard.pmUser_insID,Pcard.pmUser_updID,Pcard.PersonCard_insDT
						order by Pcard.PersonCard_insDT desc
					) as PC2

					left join v_pmUser pu12 (nolock) on PC2.pmUser_insID = pu12.pmUser_id
					left join v_Lpu l12 (nolock) on pu12.Lpu_id = l12.Lpu_id
					left join v_pmUser pu22 (nolock) on PC2.pmUser_updID = pu22.pmUser_id
					left join v_Lpu l22 (nolock) on pu22.Lpu_id = l22.Lpu_id

				where
					e.PersonCard_id = :ID
            ";
        }
		if ($table_name == 'vacData4Card063'){
			$query = "
            select
				rtrim(isnull(l1.Lpu_Nick+ ', ',''))  + rtrim(isnull(pu1.pmUser_Name,'')) + isnull(' ('+rtrim(pu1.pmUser_login)+')','') as InspmUser,
				rtrim(isnull(l1.Lpu_Nick+ ', ',''))  + rtrim(isnull(pu1.pmUser_Name,'')) + isnull(' ('+rtrim(pu1.pmUser_login)+')','') as UpdpmUser,
				convert(varchar(10), isnull(ac.vacJournalAccount_VacDateSave, vacJournalAccount_DateSave), 104)+ ' ' + convert(varchar(5), isnull(ac.vacJournalAccount_VacDateSave, vacJournalAccount_DateSave), 108) as UpdDate,
				convert(varchar(10), isnull(ac.vacJournalAccount_VacDateSave, vacJournalAccount_DateSave), 104)+ ' ' + convert(varchar(5), isnull(ac.vacJournalAccount_VacDateSave, vacJournalAccount_DateSave), 108) as InsDate
			from vac.vacData4Card063 e (nolock)
				left join vac.Inoculation i with (nolock)  on i.Inoculation_id = e.Inoculation_id
				left join vac.vac_JournalAccount ac  with (nolock) on ac.vacJournalAccount_id = i.vacJournalAccount_id
				left join v_pmUser pu1 (nolock) on ac.vacJournalAccount_Vac_PmUser_id = pu1.pmUser_id
				left join v_Lpu l1 (nolock) on pu1.Lpu_id = l1.Lpu_id
			where e.vacData4Card063_id = :ID
            ";
		}
		
		//echo getDebugSQL($query,$params);exit();
		$result = $db->query(
			$query,
			$params
		);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * История модерации двойников, показывает статус всех двойников,
	 * посланных на модерацию текущим пользователем или всеми пользователями МО для админа
	 * На выходе: JSON-строка
	 * Используется: компонент swPersonUnionHistoryWindow
	 */
	function getUnionHistory($data) {
		$filter = "";
		$queryParams = array();
		if (isSuperadmin()) {
			// для суперадмина фильтрация по МО с формы
			if (!empty($data['zLpu_id'])) {
				$filter .= " and l.Lpu_id = :zLpu_id";
				$queryParams['zLpu_id'] = $data['zLpu_id'];
			}
		} else if ( havingGroup('LpuAdmin') ) {
			// если администратор МО, показываем все записи по своей МО
			$filter .= " and l.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		} else {
			// иначе показываем только свои записи
			$filter .= "and pd.pmUser_insID = :pmUser_id";
			$queryParams['pmUser_id'] = $data['pmUser_id'];
		}

		if ( !empty($data['PersonDoubles_insDT_Range'][0]) ) {
			$filter .= " and cast(pd.PersonDoubles_insDT as date) >= cast(:PersonDoubles_insDT_Range_0 as datetime) ";
			$queryParams['PersonDoubles_insDT_Range_0'] = $data['PersonDoubles_insDT_Range'][0];
		}
		if ( !empty($data['PersonDoubles_insDT_Range'][1]) ) {
			$filter .= " and cast(pd.PersonDoubles_insDT as date) <= cast(:PersonDoubles_insDT_Range_1 as datetime) ";
			$queryParams['PersonDoubles_insDT_Range_1'] = $data['PersonDoubles_insDT_Range'][1];
		}

		if ( !empty($data['PersonDoubles_updDT_Range'][0]) ) {
			$filter .= " and cast(pd.PersonDoubles_updDT as date) >= cast(:PersonDoubles_updDT_Range_0 as datetime) ";
			$queryParams['PersonDoubles_updDT_Range_0'] = $data['PersonDoubles_updDT_Range'][0];
		}
		if ( !empty($data['PersonDoubles_updDT_Range'][1]) ) {
			$filter .= " and cast(pd.PersonDoubles_updDT as date) <= cast(:PersonDoubles_updDT_Range_1 as datetime) ";
			$queryParams['PersonDoubles_updDT_Range_1'] = $data['PersonDoubles_updDT_Range'][1];
		}

		if (!empty($data['pLpu_id'])) {
			$filter .= " and lp.Lpu_id = :pLpu_id";
			$queryParams['pLpu_id'] = $data['pLpu_id'];
		}

		if (!empty($data['Person_SurName'])) {
			$filter .= " and ps.Person_SurName like :Person_SurName + '%'";
			$queryParams['Person_SurName'] = $data['Person_SurName'];
		}

		if (!empty($data['Person_FirName'])) {
			$filter .= " and ps.Person_FirName like :Person_FirName + '%'";
			$queryParams['Person_FirName'] = $data['Person_FirName'];
		}

		if (!empty($data['Person_SecName'])) {
			$filter .= " and ps.Person_SecName like :Person_SecName + '%'";
			$queryParams['Person_SecName'] = $data['Person_SecName'];
		}

		if (!empty($data['Person_BirthDay'])) {
			$filter .= " and ps.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		if (!empty($data['PersonDoublesStatus'])) {
			switch($data['PersonDoublesStatus']) {
				case 1: // Объединён
					$filter .= " and pds.PersonDoublesStatus_id = 1";
					break;
				case 2: // Запланирован к объединению
					$filter .= " and pds.PersonDoublesStatus_id = 3";
					break;
				case 3: // Отказано
					$filter .= " and ISNULL(pds.PersonDoublesStatus_id, -1) NOT IN (-1,1,3)";
					break;
			}
		}
		
		$query = "
			select
				-- select
				rtrim(Person_SurName) as Person_Surname,
				rtrim(Person_FirName) as Person_Firname,
				rtrim(Person_SecName) as Person_Secname,
				CONVERT(varchar, Person_BirthDay, 104) as Person_Birthdate,
				case
					when pds.PersonDoublesStatus_id is null then 'Ожидает модерации'
					when pds.PersonDoublesStatus_id = 1 then 'Объединён'
					when pds.PersonDoublesStatus_id = 2 then 'Отказано'
					when pds.PersonDoublesStatus_id = 3 then 'Запланирован к объединению'
					when pds.PersonDoublesStatus_id = 11 then pds.PersonDoublesStatus_Name
					else 'Отказано (' + pds.PersonDoublesStatus_Name + ')'
				end as PersonDoubles_Status,
				LP.Lpu_Nick as Lpu_pNick,
				L.Lpu_Nick,
				CONVERT(varchar(10), PersonDoubles_insDT, 104) + ' ' + CONVERT(varchar(5), PersonDoubles_insDT, 108) as PersonDoubles_insDT,
				CONVERT(varchar(10), PersonDoubles_updDT, 104) + ' ' + CONVERT(varchar(5), PersonDoubles_updDT, 108) as PersonDoubles_updDT
				-- end select
			from
				-- from
				pd.PersonDoubles pd with (nolock)
				left join v_PersonState ps with (nolock) on pd.Person_id = ps.Person_id
				left join pd.PersonDoublesStatus pds with (nolock) on pds.PersonDoublesStatus_id = pd.PersonDoublesStatus_id
				left join v_Lpu lp with (nolock) on lp.Lpu_id = ps.Lpu_id
				left join v_pmUser pu with (nolock) on pu.pmUser_id = pd.pmUser_insID
				left join v_Lpu l with (nolock) on l.Lpu_id = pu.Lpu_id
				-- end from
			where
				-- where
				ps.Person_id is not null 
				{$filter}
				-- end where
			order by
				-- order by
				pd.PersonDoubles_insDT
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}

	/**
	 *	Общий метод получения данных для дерева
	 */
	function getSelectionTreeData($data) {
		$filterList = array();
		$orderBy = array();
		$queryParams = array();

        if ($data['object'] == 'SubDivision') {
            $filterList[] = "(1=1)";
            $filterList[] = "SS.Lpu_id = :Lpu_id";
            $queryParams['Lpu_id'] = $data['Lpu_id'];
            $join = "";
            $outer_apply = "";

            if (!empty($data['Sub_SysNick']) && $data['Sub_SysNick'] == 'LpuBuilding') {
                $join = "left join v_LpuBuilding J with (nolock) on J.LpuBuilding_id = SS.LpuBuilding_id";
                $outer_apply = "LpuSection";
                $data['Sub_SysNick'] = 'LpuUnit';
                $data['SubStage'] = 'LpuUnit';
                if ( !empty($data['pid']) ) {
                    $filterList[] = "
                        SS.LpuBuilding_id = :pid
                    ";
                    $queryParams['pid'] = $data['pid'];
                }
                else {
                    $filterList[] = "SS.LpuBuilding_id is null";
                }
            } else if (!empty($data['Sub_SysNick']) && $data['Sub_SysNick'] == 'LpuUnit') {
                $join = "left join v_LpuUnit J with (nolock) on J.LpuUnit_id = SS.LpuUnit_id";
                $data['SubStage'] = 'LpuSection';
                if ( !empty($data['pid']) ) {
                    $filterList[] = "
                        SS.LpuUnit_id = :pid
                    ";
                    $queryParams['pid'] = $data['pid'];
                }
                else {
                    $filterList[] = "SS.LpuUnit_id is null";
                }
            } else {
                $data['SubStage'] = 'LpuBuilding';
                $data['Sub_SysNick'] = 'LpuBuilding';
                $outer_apply = "LpuUnit";
            }

            $query = "
                select
                    SS.{$data['SubStage']}_id as id,
                    SS.{$data['SubStage']}_Code as code,
                    SS.{$data['SubStage']}_Name as name,
                    '{$data['Sub_SysNick']}' as Sub_SysNick
                    ".(!empty($outer_apply)?",isnull(tc.cnt, 0) as childrenCnt":"")."
                from
                    v_{$data['SubStage']} SS with(nolock)
                    " . $join . "
                    ". (!empty($outer_apply)?"outer apply (
                        select count(" . $data['SubStage'] . "_id) as cnt
                        from v_" . $outer_apply . " with (nolock)
                        where " . $data['SubStage'] . "_id = SS." . $data['SubStage'] . "_id
                    ) tc":"")."
                " . (count($filterList) > 0 ? "where " . implode(' and ', $filterList) : "") . "
            ";
            //echo getDebugSql($query, $queryParams); exit();
            $result = $this->db->query($query, $queryParams);

            if ( is_object($result) ) {
                return $result->result('array');
            }
            else {
                return false;
            }
        } else {
            if ( !empty($data['pid']) ) {
                $filterList[] = "t." . $data['object'] . "_pid = :pid";
                $queryParams['pid'] = $data['pid'];
            }
            else {
                $filterList[] = "t." . $data['object'] . "_pid is null";
            }

            if ( !empty($data['treeSortMode']) && preg_match("/^\d{4}$/", $data['treeSortMode']) ) {
				// Сперва по pid
				if ( substr($data['treeSortMode'], 3, 1) == '1' ) {
					$orderBy[] = "case when ISNULL(tc.cnt, 0) > 0 then 1 else 0 end asc";
				}
				else if ( substr($data['treeSortMode'], 3, 1) == '2' ) {
					$orderBy[] = "case when ISNULL(tc.cnt, 0) > 0 then 1 else 0 end desc";
				}

				if ( substr($data['treeSortMode'], 0, 1) == '1' ) {
					$orderBy[] = "t." . $data['object'] . "_id asc";
				}
				else if ( substr($data['treeSortMode'], 0, 1) == '2' ) {
					$orderBy[] = "t." . $data['object'] . "_id desc";
				}

				if ( substr($data['treeSortMode'], 1, 1) == '1' ) {
					$orderBy[] = "t." . $data['object'] . "_Code asc";
				}
				else if ( substr($data['treeSortMode'], 1, 1) == '2' ) {
					$orderBy[] = "t." . $data['object'] . "_Code desc";
				}

				if ( substr($data['treeSortMode'], 2, 1) == '1' ) {
					$orderBy[] = "t." . $data['object'] . "_Name asc";
				}
				else if ( substr($data['treeSortMode'], 2, 1) == '2' ) {
					$orderBy[] = "t." . $data['object'] . "_Name desc";
				}
			}

			if ( !empty($data['onlyActual']) ) {
				$filterList[] = "(t." . $data['object'] . "_begDate is null or t." . $data['object'] . "_begDate <= dbo.tzGetDate())";
				$filterList[] = "(t." . $data['object'] . "_endDate is null or t." . $data['object'] . "_endDate >= dbo.tzGetDate())";
			}

            $query = "
                select
                     t." . $data['object'] . "_id as id
                    ,t." . $data['object'] . "_Code as code
                    ,t." . $data['object'] . "_Name as name
                    ,null as Sub_SysNick
                    ,isnull(tc.cnt, 0) as childrenCnt
                from " . $data['scheme'] . ".v_" . $data['object'] . " t with (nolock)
                    outer apply (
                        select count(" . $data['object'] . "_id) as cnt
                        from " . $data['scheme'] . ".v_" . $data['object'] . " with (nolock)
                        where " . $data['object'] . "_pid = t." . $data['object'] . "_id
                    ) tc
                " . (count($filterList) > 0 ? "where " . implode(' and ', $filterList) : "") . "
                " . (count($orderBy) > 0 ? "order by " . implode(', ', $orderBy) : "") . "
            ";
            // echo getDebugSql($query, $queryParams); exit();
            $result = $this->db->query($query, $queryParams);

            if ( is_object($result) ) {
                return $result->result('array');
            }
            else {
                return false;
            }
        }
	}

	/**
	 *	Получение списка родительских узлов иерархической структуры
	 */
	function getParentNodeList($data) {
		$queryParams = array();
		$response = array();

        if ($data['object'] == 'SubDivision') {

            $join = '';
            $where = '';
            $select = '';

            if ($data['Sub_SysNick'] == 'LpuBuilding') {
                $select = "
                    SS.LpuBuilding_id,
                    null as LpuUnit_id,
                    null as LpuSection_id
                ";
                $where = "SS.LpuBuilding_id = :id";
            } else if ($data['Sub_SysNick'] == 'LpuUnit') {
                $join = " left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = SS.LpuBuilding_id";
                $where = "SS.LpuUnit_id = :id";
                $select = "
                    LB.LpuBuilding_id,
                    SS.LpuUnit_id,
                    null as LpuSection_id
                ";
            }  else if ($data['Sub_SysNick'] == 'LpuSection') {

                $join .= " left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = SS.LpuUnit_id";
                $join .= " left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id";
                $where = "SS.LpuSection_id = :id";
                $select = "
                    LB.LpuBuilding_id,
                    LU.LpuUnit_id,
                    SS.LpuSection_id
                ";
            }

            $query = "
                select
                    {$select}
                from
                    v_{$data['Sub_SysNick']} SS with (nolock)
                    {$join}
                where
                    {$where}
                    and SS.Lpu_id = :Lpu_id
            ";

            //echo getDebugSQL($query, array('id' => $data['id'], 'Lpu_id' => $data['Lpu_id']));die;
            $result = $this->db->query($query, array('id' => $data['id'], 'Lpu_id' => $data['Lpu_id']));

            if ( !is_object($result) ) {
                return false;
            }

            $rec = $result->result('array');

            if ( !is_array($rec) || count($rec) == 0 ) {
                $data['id'] = null;
            }
            else {

                $response[] = $rec[0]['LpuSection_id'];
                $response[] = $rec[0]['LpuUnit_id'];
                $response[] = $rec[0]['LpuBuilding_id'];
            }

        } else {
            while ( !empty($data['id']) ) {
                $query = "
                    select top 1 " . $data['object'] . "_pid as pid
                    from " . $data['scheme'] . ".v_" . $data['object'] . " with (nolock)
                    where " . $data['object'] . "_id = :id
                        and " . $data['object'] . "_pid is not null
                ";
                $queryParams['id'] = $data['id'];

                $result = $this->db->query($query, $queryParams);

                if ( !is_object($result) ) {
                    return $response;
                }

                $rec = $result->result('array');

                if ( !is_array($rec) || count($rec) == 0 ) {
                    $data['id'] = null;
                }
                else {
                    $response[] = $rec[0]['pid'];
                    $data['id'] = $rec[0]['pid'];
                }
            }
        }


		return $response;
	}

	/**
	 *	Получение в виде строки наименования объекта с указанием родительских узлов
	 */
	function getObjectNameWithPath($data) {
		$objectName = '';
        $parentNodeArray = array();
		$data['separator'] = isset($data['separator'])?$data['separator']:' / ';

        if ($data['object'] == 'SubDivision'){

            $join = '';
            $where = '';
            $select = '';

            if ($data['Sub_SysNick'] == 'LpuBuilding') {
                $select = "
                    SS.LpuBuilding_name,
                    SS.LpuBuilding_id,
                    null as LpuUnit_name,
                    null as LpuUnit_id,
                    null as LpuSection_name,
                    null as LpuSection_id
                ";
                $where = "SS.LpuBuilding_id = :id";
            } else if ($data['Sub_SysNick'] == 'LpuUnit') {
                $join = " left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = SS.LpuBuilding_id";
                $where = "SS.LpuUnit_id = :id";
                $select = "
                    LB.LpuBuilding_name,
                    LB.LpuBuilding_id,
                    SS.LpuUnit_name,
                    SS.LpuUnit_id,
                    null as LpuSection_name,
                    null as LpuSection_id
                ";
            }  else if ($data['Sub_SysNick'] == 'LpuSection') {

                $join .= " left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = SS.LpuUnit_id";
                $join .= " left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id";
                $where = "SS.LpuSection_id = :id";
                $select = "
                    LB.LpuBuilding_name,
                    LB.LpuBuilding_id,
                    LU.LpuUnit_name,
                    LU.LpuUnit_id,
                    SS.LpuSection_name,
                    SS.LpuSection_id
                ";
            }

            $query = "
                select
                    {$select}
                from
                    v_{$data['Sub_SysNick']} SS with (nolock)
                    {$join}
                where
                    {$where}
                    and SS.Lpu_id = :Lpu_id
            ";

            //echo getDebugSQL($query, array('id' => $data['id'], 'Lpu_id' => $data['Lpu_id']));die;
            $result = $this->db->query($query, array('id' => $data['id'], 'Lpu_id' => $data['Lpu_id']));

            if ( !is_object($result) ) {
                return false;
            }

            $rec = $result->result('array');

            if ( !is_array($rec) || count($rec) == 0 ) {
                $data['id'] = null;
            }
            else {

                $objectName = $rec[0]['LpuBuilding_name'];
                array_push($parentNodeArray, $rec[0]['LpuBuilding_id']);

                if (!empty($rec[0]['LpuUnit_name'])){
                        $objectName = $objectName . $data['separator'] . $rec[0]['LpuUnit_name'];
                        array_push($parentNodeArray, $rec[0]['LpuUnit_id']);
                }

                if (!empty($rec[0]['LpuSection_name'])){
                    $objectName = $objectName . $data['separator'] . $rec[0]['LpuSection_name'];
                    array_push($parentNodeArray, $rec[0]['LpuSection_id']);
                }
            }

        } else {

            while ( !empty($data['id']) ) {
                $query = "
                    select top 1
                         " . $data['object'] . "_pid as pid
                        ," . $data['object'] . "_Name as name
                    from " . $data['scheme'] . ".v_" . $data['object'] . " with (nolock)
                    where " . $data['object'] . "_id = :id
                ";

                $result = $this->db->query($query, array('id' => $data['id']));

                if ( !is_object($result) ) {
                    return false;
                }

                $rec = $result->result('array');

                if ( !is_array($rec) || count($rec) == 0 ) {
                    $data['id'] = null;
                }
                else {
                    if ( !empty($objectName) ) {
                        $objectName = $data['separator'].$objectName;
                    }

                    $objectName = $rec[0]['name'] . $objectName;
                    $data['id'] = $rec[0]['pid'];
                }
            }
        }

		return array(array('name' => $objectName, 'parentNodeArray' => $parentNodeArray));
	}


	/**
	 *	Получение данных для формы поиска
	 */
	function getObjectSearchData($data) {
		$filterList = array();
		$queryParams = array();
        $queryParams['Lpu_id'] = $data['Lpu_id'];

        if ($data['object'] == 'SubDivision') {

            if ( !empty($data['code']) ) {
                $filterList[] = "sub.code like :code";
                $queryParams['code'] = $data['code'] . '%';
            }

            if ( !empty($data['name']) ) {
                $filterList[] = "sub.name like :name";
                $queryParams['name'] = '%' . $data['name'] . '%';
            }

            $query = "
                select " . (count($filterList) == 0 ? "top 100" : "") . "
                     sub.id
                    ,sub.code
                    ,sub.name
                    ,sub.Sub_SysNick
                    , null as childCnt
                from (
                    select
                        LpuBuilding_id as id,
                        LpuBuilding_Code as code,
                        LpuBuilding_Name as name,
                        'LpuBuilding' as Sub_SysNick
                    from
                        v_LpuBuilding nolock
                    where
                        Lpu_id = :Lpu_id

                    union

                    select
                        LpuUnit_id as id,
                        LpuUnit_Code as code,
                        LpuUnit_Name as name,
                        'LpuUnit' as Sub_SysNick
                    from
                        v_LpuUnit nolock
                    where
                        Lpu_id = :Lpu_id

                    union

                    select
                        LpuSection_id as id,
                        LpuSection_Code as code,
                        LpuSection_Name as name,
                        'LpuSection' as Sub_SysNick
                    from
                        v_LpuSection nolock
                    where
                        Lpu_id = :Lpu_id

                    ) sub
                " . (count($filterList) > 0 ? "where " . implode(" and ", $filterList) : "") . "
            ";

        } else {

            if ( !empty($data['code']) ) {
                $filterList[] = "o." . $data['object'] . '_Code like :code';
                $queryParams['code'] = $data['code'] . '%';
            }

            if ( !empty($data['name']) ) {
                $filterList[] = "o." . $data['object'] . '_Name like :name';
                $queryParams['name'] = '%' . $data['name'] . '%';
            }

            if ( !empty($data['onlyActual']) ) {
                $filterList[] = "(o." . $data['object'] . "_begDate is null or o." . $data['object'] . "_begDate <= dbo.tzGetDate())";
                $filterList[] = "(o." . $data['object'] . "_endDate is null or o." . $data['object'] . "_endDate >= dbo.tzGetDate())";
            }

            // $filterList[] = 'cnt.childCnt = 0';

            $query = "
                select " . (count($filterList) == 0 ? "top 100" : "") . "
                     o." . $data['object'] . "_id as id
                    ,o." . $data['object'] . "_Code as code
                    ,o." . $data['object'] . "_Name as name
                    ,cnt.childCnt
                from " . $data['scheme'] . ".v_" . $data['object'] . " o with (nolock)
                    outer apply (
                        select count(" . $data['object'] . "_id) as childCnt
                        from " . $data['scheme'] . ".v_" . $data['object'] . " with (nolock)
                        where " . $data['object'] . "_pid = o." . $data['object'] . "_id
                    ) cnt
                " . (count($filterList) > 0 ? "where " . implode(" and ", $filterList) : "") . "
            ";
        }

        //echo getDebugSQL($query, $queryParams);die;
        $result = $this->db->query($query, $queryParams);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
	}


	/**
	 * Конвертация старых кодов в новые
	 */
	function convertMedSpecOmsCodes() {
		// Получаем данные из StructuredParams
		$query = "
			select
				sp.StructuredParams_id,
				sp.MedSpecOms_Text
			from v_StructuredParams sp with (nolock)
			where ISNULL(sp.MedSpecOms_Text, 'все') != 'все'
				and not exists (
					select top 1 StructuredParams_id
					from tmp.liza_12 with (nolock)
					where StructuredParams_id = sp.StructuredParams_id
				)
		";
		$result = $this->db->query($query);

		if ( !$result ) {
			return 'Ошибка при выполнении запроса №1 (1)';
		}

		$structuredParamsData = $result->result('array');

		if ( !is_array($structuredParamsData) || count($structuredParamsData) == 0 ) {
			return 'Ошибка при выполнении запроса №1 (2)';
		}

		// Получаем данные из tmp.liza_MedSpecOms
		$query = "
			select
				MedSpecOmsOld_Code,
				MedSpecOmsNew_Code
			from tmp.liza_MedSpecOms with (nolock)
			where Region_id = dbo.getRegion()
		";
		$result = $this->db->query($query);

		if ( !$result ) {
			return 'Ошибка при выполнении запроса №2 (1)';
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return 'Ошибка при выполнении запроса №2 (2)';
		}

		$convertMap = array();

		// Формируем таблицу соответствия
		foreach ( $response as $array ) {
			$convertMap[$array['MedSpecOmsOld_Code']] = $array['MedSpecOmsNew_Code'];
		}

		foreach ( $structuredParamsData as $array ) {
			$newList = array();
			$oldList = explode(',', $array['MedSpecOms_Text']);

			if ( count($oldList) > 0 ) {
				foreach ( $oldList as $value ) {
					if ( array_key_exists($value, $convertMap) && !empty($convertMap[$value]) ) {
						$newList[] = $convertMap[$value];
					}
				}
			}

			$MedSpecOms_Text = implode(',', $newList);

			echo '<div>', $array['MedSpecOms_Text'], ' =&gt; ', $MedSpecOms_Text, '</div>';

			$query = "
				insert into tmp.liza_12 (StructuredParams_id, MedSpecOms_Text)
				values (:StructuredParams_id, :MedSpecOms_Text)
			";
			$result = $this->db->query($query, array('StructuredParams_id' => $array['StructuredParams_id'], 'MedSpecOms_Text' => $MedSpecOms_Text));
		}

		return '<div>Успех!</div>';
	}

	/**
	 * Получение сгенерированного значения
	 */
	public function genObjectValue($data) {
		$query = "
			declare @newValue bigint;

			exec xp_GenpmID
				@ObjectName = :ObjectName,
				@Lpu_id = :Lpu_id,
				@ObjectID = @newValue output,
				@ObjectValue = :ObjectValue;

			select @newValue as newValue;
		";
		$result = $this->db->query($query, array(
			 'Lpu_id' => $data['Lpu_id']
			,'ObjectName' => $data['ObjectName']
			,'ObjectValue' => (!empty($data['ObjectValue']) ? $data['ObjectValue'] : null)
		));

		if ( !is_object($result) ) {
			return false;
		}

		$resp = $result->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			return false;
		}

		return $resp[0]['newValue'];
	}

	/**
	 * Получение описания полей из результата запроса
	 */
	public function showResultParamDescriptions($tables) {

		$query_fields = array();

		foreach ($tables as $tableName => $t_fields) {

			$fields_filter = "(";

			$params['table_name'] = "dbo.".$tableName;
			foreach ($t_fields as $field) {
				$fields_filter .= "'".$field."'".',';
				$linked_field = explode('_',$field,2);

				if (!empty($linked_field[1])) $fields_filter .= "'".$linked_field[1]."'".',';
}

			$fields_filter = rtrim($fields_filter, ',');
			$fields_filter .= ")";

			$query = "
					SELECT DISTINCT
						c.id,
						c.name AS name,
						--UPPER(t.name) + '(' + cast(c.length as varchar) + case when c.scale > 0 then ',' + cast(c.scale as varchar) else '' end + ')' as type,
						cast(cd.value as varchar(MAX)) as descr,
						cast(td.value as varchar(MAX)) as tab_descr
					FROM
						syscolumns c (nolock)
						left join sys.types t (nolock) on t.system_type_id = c.xtype
						LEFT JOIN sys.extended_properties td (nolock) ON td.major_id =  c.id AND td.minor_id = 0 AND td.name = 'MS_Description'
						LEFT JOIN sys.extended_properties cd (nolock) ON cd.major_id = c.id AND cd.minor_id = c.colid AND cd.name = 'MS_Description'
					WHERE
						c.id = OBJECT_ID(:table_name)
						and c.name in $fields_filter
				";

			$resp = $this->queryResult($query, $params);
			if (!empty($resp)) $query_fields = array_merge($query_fields,$resp);
		}

		$result = array();
		foreach ($query_fields as $query_field) {
			$result[$query_field['name']] = $query_field;
		}

		return $result;
	}

	/**
	 * Проверка, установлен ли для данной мед. службы флаг "файловая интеграция"
	 */
	function withFileIntegration($data)
	{
		$query = "
			select
				coalesce(MedService_IsFileIntegration, 0) as result
			from
				v_MedService with(nolock)
			where
				MedService_id = :MedService_id		
		";

		$result = $this->db->query($query, $data);

		$result = $result->result('array');

		if (isset($result[0]))
			return ($result[0]['result'] == 2);
		else return false;
	}

	/**
	 * Список МО
	*/
	function getLpuList($data)
	{
		$query = "
			SELECT
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Code as \"LpuSection_Code\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LS.Lpu_id as \"Lpu_id\",
				LS.LpuBuilding_id as \"LpuBuilding_id\",
				LU.LpuUnit_id as \"LpuUnit_id\",
				LUT.LpuUnitType_id as \"LpuUnitType_id\",
				LUT.LpuUnitType_Code as \"LpuUnitType_Code\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
			FROM
				v_LpuSection LS with(nolock)
				inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuUnitType LUT with(nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
			WHERE
				LS.Lpu_id = :filterLpu_id
			ORDER BY
				LS.LpuSection_Code
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public function getReplicationInfo($data) {
		$this->load->library('swCache');

		if ( $resCache = $this->swcache->get("ReplicationInfo_" . $data['db']) ) {
			return [
				'actualDT' => $resCache[0]['actualDT'], 'syncDT' => $resCache[0]['syncDT']
			];
		}

		$resp = [
			'actualDT' => null,
			'syncDT' => null,
		];

		$config = get_config();

		if (
			isset($config['dbReplicatorInfo']) && is_array($config['dbReplicatorInfo'])
			&& isset($config['dbReplicatorInfo']['enabled']) && $config['dbReplicatorInfo']['enabled'] === true
			&& isset($config['dbReplicatorInfo']['subscribers']) && is_array($config['dbReplicatorInfo']['subscribers'])
			&& in_array($data['db'], $config['dbReplicatorInfo']['subscribers'])
		) {
			// Получаем расчетные дату и время актуальности данных
			if ( !empty($config['dbReplicatorInfo']['url']) ) {
				$repDataJSON = file_get_contents($config['dbReplicatorInfo']['url']);

				if ( !empty($repDataJSON) ) {
					$repData = json_decode($repDataJSON, true);
				}

				if ( !is_array($repData) ) {
					$repData = array();
				}

				if ( isset($repData['channels']) && is_array($repData['channels']) ) {
					foreach ( $repData['channels'] as $channel ) {
						if ( !is_array($channel) || !isset($channel['id']) || $channel['id'] != 'Evn' || !isset($channel['tables']) || !is_array($channel['tables']) ) {
							continue;
						}

						foreach ( $channel['tables'] as $table ) {
							if ( !is_array($table) || !isset($table['source']) || $table['source'] != 'dbo.Evn' || !isset($table['subscribers']) || !is_array($table['subscribers']) ) {
								continue;
							}

							foreach ( $table['subscribers'] as $subscriber ) {
								if ( !is_array($subscriber) || !isset($subscriber['id']) || $subscriber['id'] != $data['db'] ) {
									continue;
								}

								if ( !empty($subscriber['estimatedEndDate']) ) {
									$resp['syncDT'] = date('d.m.Y H:i:s', strtotime($subscriber['estimatedEndDate']));
								}
							}
						}
					}
				}
			}

			if (
				isset($config['dbReplicatorInfo']['dbConnections']) && is_array($config['dbReplicatorInfo']['dbConnections'])
				&& array_key_exists($data['db'], $config['dbReplicatorInfo']['dbConnections'])
			) {
				$dbConnectionName = $config['dbReplicatorInfo']['dbConnections'][$data['db']];
			}

			if ( empty($dbConnection) ) {
				$dbConnectionName = 'default';
			}
		}
		else {
			switch ( $data['db'] ) {
				case 'registry':
					$dbConnectionName = 'registry';
					break;

				case 'report':
					$dbConnectionName = 'bdreports';
					break;

				default:
					$dbConnectionName = 'default';
					break;
			}
		}

		// Дату актуальности получаем в любом случае
		$dbConnection = $this->load->database($dbConnectionName, true);

		$queryResult = $dbConnection->query("
			declare @date datetime = dbo.tzGetDate();
			
			select max(Evn_updDT) as \"actualDT\"
			from v_Evn with (nolock)
			where Evn_updDT > @date- 1
				and Evn_updDT <  @date + 1
		");

		if ( is_object($queryResult) ) {
			$queryResponse = $queryResult->result_array();

			if ( is_array($queryResponse) && count($queryResponse) > 0 && !empty($queryResponse[0]['actualDT'])) {
				$resp['actualDT'] = $queryResponse[0]['actualDT']->format('d.m.Y H:i:s');
			}
		}

		$this->swcache->set(
			"ReplicationInfo_" . $data['db'],
			[ $resp ],
			[ 'ttl' => 300 ]
		);

		return $resp;
	}
}
