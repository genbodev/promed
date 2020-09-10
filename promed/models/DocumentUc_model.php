<?php defined('BASEPATH') or die ('No direct script access allowed');

class DocumentUc_model extends swModel {

	/**
	 * Загрузка данных
	 */
	function load($data) {
		$query = "
			select
				du.DocumentUc_id,
				du.DocumentUc_pid,
				du.Contragent_id,
				du.DocumentUc_Num,
				du.DocumentUc_setDate,
				du.DocumentUc_didDate,
				du.DocumentUc_InvoiceNum,
				du.DocumentUc_InvoiceDate,
				du.WhsDocumentUc_id,
				du.DocumentUc_DogDate,
				du.DocumentUc_DogNum,
				du.Org_id,
				du.Contragent_sid,
				du.Storage_sid,
				du.Mol_sid,
				smol.Person_Fio as Mol_sPerson,
				du.Contragent_tid,
				du.Storage_tid,
				du.Mol_tid,
				tmol.Person_Fio as Mol_tPerson,
				du.DrugFinance_id,
				du.WhsDocumentCostItemType_id,
				du.DrugDocumentType_id,
				ddt.DrugDocumentType_Code,
				du.DrugDocumentStatus_id,
				du.EmergencyTeam_id,
				du.StorageZone_sid,
				du.StorageZone_tid,
				dds.DrugDocumentStatus_Code,
				dds.DrugDocumentStatus_Name,
				note.Note_id,
				note.Note_Text,
				recept.Name as EvnRecept_Name,
				invent.WhsDocumentUcInvent_id,
                dul.Lpu_id,
                dul.LpuBuilding_id,
				--  Читаем дату закрытия отчетного периода
				/*
				(SElect isnull(max(DrugPeriodClose_DT), '2016-06-01') DrugPeriodClose_DT  from DrugPeriodClose with (nolock)
				    where Org_id = du.Org_id and DrugPeriodClose_Sign = 2) DrugPeriodClose_DT,
				*/
				(SElect top 1 isnull(
					case when DrugPeriodClose_DT > dbo.tzGetDate() then DrugPeriodOpen_DT else DrugPeriodClose_DT end,
					'2016-06-01') DrugPeriodClose_DT  from DrugPeriodClose with (nolock)
						where Org_id = du.Org_id
				) DrugPeriodClose_DT,
				(
				    isnull(wdc.WhsDocumentClass_Name, wdt.WhsDocumentType_Name) +
				    ' № ' +
                    convert(varchar, wdu.WhsDocumentUc_Num) +
                    ' от ' +
                    convert(varchar(10), wdu.WhsDocumentUc_Date, 104)
				) as WhsDocumentUc_FullName,
                (
                    select top 1
                        '№ ' +
                        convert(varchar, du2.DocumentUc_Num) +
                        ' от ' +
                        convert(varchar, du2.DocumentUc_setDate, 104)
					from
					    v_DocumentUc du2 with(nolock)
					where
					    du.DrugDocumentType_id = 6
                        and du2.DrugDocumentType_id = du.DrugDocumentType_id
                        and du2.DocumentUc_Num = du.DocumentUc_Num
                        and du2.Org_id = du.Org_id
                        and du2.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
                        and du.DrugDocumentStatus_id = 1
                        and du2.DocumentUc_id <> du.DocumentUc_id
                        and  YEAR(du2.DocumentUc_setDate) = YEAR(du.DocumentUc_setDate)
				) as DocW, -- Поле инициализации дубля документа прихода (исполненного)
				acc_type.AccountType_id,
				du.SubAccountType_tid,
				du.SubAccountType_sid,
				case
					when du.DrugDocumentType_id = 27 or du.DrugDocumentType_id = 28
						then rtrim(isnull(p.Person_Fio,'')+' '+isnull(convert(varchar(10), p.Person_BirthDay, 104),''))
					when du.DrugDocumentType_id = 29 or du.DrugDocumentType_id = 30
						then em_team.objectName
					else ''
				end as StorageZoneLiable_ObjectName
			from
				v_DocumentUc du with (nolock)
				left join v_WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = du.WhsDocumentUc_id
				left join v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wdu.WhsDocumentType_id
				left join v_WhsDocumentClass wdc with (nolock) on wdc.WhsDocumentClass_id = wdu.WhsDocumentClass_id
				left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
				left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
				left join v_WhsDocumentUcInvent invent with (nolock) on invent.WhsDocumentUc_id = du.WhsDocumentUc_id
				left join v_Person_all p with (nolock) on p.Person_id = du.Person_id
				outer apply (
					select top 1
						DocumentStrValues_id as Note_id,
						DocumentStrValues_Values as Note_Text
					from
						v_DocumentStrValues dsv with (nolock)
						left join v_DocumentStrPropType dspt on dspt.DocumentStrPropType_id = dsv.DocumentStrPropType_id
						left join v_DocumentStrType dst on dst.DocumentStrType_id = dsv.DocumentStrType_id
					where
						dspt.DocumentStrPropType_Code = 1 and --Примечание
						dst.DocumentStrType_Code = 1 and --Документ учета DocumentUc
						dsv.Document_id = du.DocumentUc_id
				) note
				outer apply (
					select top 1
						rtrim(ltrim(isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,''))) as Person_Fio
					from
						Mol m with (nolock)
						left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = m.MedPersonal_id
						left join v_PersonState ps with (nolock) on ps.Person_id = mp.Person_id or ps.Person_id = m.Person_id
					where
						m.Mol_id = du.Mol_sid
				) smol
				outer apply (
					select top 1
						rtrim(ltrim(isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,''))) as Person_Fio
					from
						Mol m with (nolock)
						left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = m.MedPersonal_id
						left join v_PersonState ps with (nolock) on ps.Person_id = mp.Person_id or ps.Person_id = m.Person_id
					where
						m.Mol_id = du.Mol_tid
				) tmol
				outer apply (
					select top 1
						(
							er.EvnRecept_Ser+
							' № '+er.EvnRecept_Num+
							isnull(' от '+convert(varchar, er.EvnRecept_setDT, 104), '')+
							isnull(' '+ps.Person_SurName,'')+
							isnull(' '+ps.Person_FirName,'')+
							isnull(' '+ps.Person_SecName,'')
						) as Name
					from
						v_DocumentUcStr dus with (nolock)
						left join v_EvnRecept er with (nolock) on er.EvnRecept_id = dus.EvnRecept_id
						left join v_PersonState ps with (nolock) on ps.Person_id = er.Person_id
					where
						dus.DocumentUc_id = du.DocumentUc_id
				) recept
				outer apply (
				    select top 1
				        i_dul.Lpu_id,
				        i_dul.LpuBuilding_id
				    from
				        v_DocumentUcLink i_dul with (nolock)
				    where
				        i_dul.DocumentUc_id = du.DocumentUc_id
				    order by
				        i_dul.DocumentUcLink_id
				) dul
				outer apply (
				    select top 1
                        i_ds.AccountType_id
                    from
                        v_DocumentUcStr i_dus with (nolock)
                        inner join v_DrugShipmentLink i_dsl with (nolock) on i_dsl.DocumentUcStr_id = i_dus.DocumentUcStr_id
                        left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
                    where
                        i_dus.DocumentUc_id = du.DocumentUc_id and
                        i_ds.AccountType_id is not null
                    order by
                        i_ds.DrugShipment_id
				) acc_type
				outer apply (
				    select top 1
                        ltrim(rtrim( isnull(ETSC.EmergencyTeamSpec_Name+' ','')+'Бригада № '+isnull(et.EmergencyTeam_Num,'')+' / '+isnull(mp_hs.Person_Fio,'')+isnull((' '+duty.EmergencyTeamDuty_DTStartDate + ' ' + duty.EmergencyTeamDuty_DTStart +' - '),'')+isnull((duty.EmergencyTeamDuty_DTFinishDate + ' ' + duty.EmergencyTeamDuty_DTFinish),'') )) as objectName
                    from
                        v_EmergencyTeam et with (nolock)
                        left join v_MedPersonal mp_hs with (nolock) on mp_hs.MedPersonal_id = et.EmergencyTeam_HeadShift
                        LEFT JOIN v_EmergencyTeamSpec AS ETSC with (nolock) ON ETSC.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id
                        outer apply (
							select top 1
								etd.EmergencyTeamDuty_id,
								convert(varchar(20), etd.EmergencyTeamDuty_DTStart, 104) as EmergencyTeamDuty_DTStartDate,
								convert(varchar(20), etd.EmergencyTeamDuty_DTFinish, 104) as EmergencyTeamDuty_DTFinishDate,
								convert(varchar(20), etd.EmergencyTeamDuty_DTStart, 108) as EmergencyTeamDuty_DTStart,
								convert(varchar(20), etd.EmergencyTeamDuty_DTFinish, 108) as EmergencyTeamDuty_DTFinish
							from v_EmergencyTeamDuty etd with(nolock)
							where et.EmergencyTeam_id=etd.EmergencyTeam_id and etd.EmergencyTeamDuty_factEndWorkDT is null
						) duty
                    where
                         et.EmergencyTeam_id = du.EmergencyTeam_id
				) em_team
			where
				du.DocumentUc_id = :DocumentUc_id;
		";

		$dbrep = $this->db;

		$result = $dbrep->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($data) {
		$params = array();
		$filter = "(1=1)";

		if(!empty($data['DocumentUc_id']) && $data['DocumentUc_id'] > 0) {
			$filter .= " and du.DocumentUc_id = :DocumentUc_id";
			$params['DocumentUc_id'] = $data['DocumentUc_id'];
		} else {
			if (!empty($data['query'])) {
				$filter .= " and du.DocumentUc_Num like '%'+:DocumentUc_Num+'%'";
				$params['DocumentUc_Num'] = $data['query'];
			} else {
				$filter .= " and du.DocumentUc_Num is not null";
			}
		}

		$query = "
			select top 500
				du.DocumentUc_id,
				du.DocumentUc_Num
			from
				v_DocumentUc du with (nolock)
			where
				{$filter};
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка параметров хранимой процедуры
	 */
	function getStoredProcedureParamsList($sp, $schema) {
		$query = "
			select [name]
			from sys.all_parameters with(nolock)
			where [object_id] = (
				select
					top 1 [object_id]
				from
					sys.objects with(nolock)
				where
					[type_desc] = 'SQL_STORED_PROCEDURE' and [name] = :name and
					(
						:schema is null or
						[schema_id] = (select top 1 [schema_id] from sys.schemas with(nolock) where [name] = :schema)
					)
			)
			and [name] not in ('@pmUser_id', '@Error_Code', '@Error_Message', '@isReloadCount')
		";

		$queryParams = array(
			'name' => $sp,
			'schema' => $schema
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$outputData = array();
		$response = $result->result('array');

		foreach ( $response as $row ) {
			$outputData[] = str_replace('@', '', $row['name']);
		}

		return $outputData;
	}

	/**
	 * Сохранение произвольного обьекта (без повреждения предыдущих данных).
	 */
	/*function saveObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		if (!isset($data[$key_field])) {
			$data[$key_field] = null;
		}

		$action = $data[$key_field] > 0 ? "upd" : "ins";
		$proc_name = "p_{$object_name}_{$action}";
		$params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		$save_data = array();
		$query_part = "";

		//получаем существующие данные если апдейт
		if ($action == "upd") {
			$query = "
				select
					*
				from
					{$schema}.{$object_name} with (nolock)
				where
					{$key_field} = :id;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'id' => $data[$key_field]
			));
			if (is_array($result)) {
				foreach($result as $key => $value) {
					if (in_array($key, $params_list)) {
						$save_data[$key] = $value;
					}
				}
			}
		}

		foreach($data as $key => $value) {
			if (in_array($key, $params_list)) {
				$save_data[$key] = $value;
			}
		}

		foreach($save_data as $key => $value) {
			if (in_array($key, $params_list) && $key != $key_field) {
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
					$save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
				}
				$query_part .= "@{$key} = :{$key}, ";
			}
		}

		$save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

		$query = "
			declare
				@{$key_field} bigint = :{$key_field},
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @{$key_field} as {$key_field}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}


		$result = $this->getFirstRowFromQuery($query, $save_data);
		if ($result && is_array($result)) {
			if($result[$key_field] > 0) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При сохранении произошла ошибка');
		}
	}*/

	/**
	 * Копирование произвольного обьекта.
	 */
	function copyObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		if (!isset($data[$key_field])) {
			return array('Error_Message' => 'Не указано значение ключевого поля');
		}

		$proc_name = "p_{$object_name}_ins";
		$params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		$save_data = array();
		$query_part = "";

		//получаем данные оригинала
		$query = "
			select
				*
			from
				{$schema}.{$object_name} with (nolock)
			where
				{$key_field} = :id;
		";
		$result = $this->getFirstRowFromQuery($query, array(
			'id' => $data[$key_field]
		));
		if (is_array($result)) {
			foreach($result as $key => $value) {
				if (in_array($key, $params_list)) {
					$save_data[$key] = $value;
				}
			}
		}


		foreach($data as $key => $value) {
			if (in_array($key, $params_list)) {
				$save_data[$key] = $value;
			}
		}

		foreach($save_data as $key => $value) {
			if (in_array($key, $params_list) && $key != $key_field) {
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
					$save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
				}
				$query_part .= "@{$key} = :{$key}, ";
			}
		}

		$save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

		$query = "
			declare
				@{$key_field} bigint = null,
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @{$key_field} as {$key_field}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}
		$result = $this->getFirstRowFromQuery($query, $save_data);
		if ($result && is_array($result)) {
			if($result[$key_field] > 0) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При копировании произошла ошибка');
		}
	}

	/**
	 * Удаление произвольного обьекта.
	 */
	function deleteObject($object_name, $data) {
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute dbo.p_{$object_name}_del
				@{$object_name}_id = :{$object_name}_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
                //echo getDebugSQL($query, $data);// exit();
		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			if(empty($result['Error_Message'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Message' => 'При удалении произошла ошибка');
		}
	}

	/**
	 * @param $DrugShipmentLink_id
	 * @return array
	 */
	function deleteDrugShipLink($DrugShipmentLink_id)
	{
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute dbo.p_DrugShipmentLink_del
				@DrugShipmentLink_id = :DrugShipmentLink_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";

		$result = $this->getFirstRowFromQuery($query, array('DrugShipmentLink_id'=>$DrugShipmentLink_id));
		if ($result && is_array($result)) {
			if(empty($result['Error_Message'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Message' => 'При удалении произошла ошибка');
		}
	}

	/**
	 *  Формирование списка складов
	 */
	function loadStorageList($data) {
		$params = array();
		$with = array();
		$filter = "(1=1)";
		$join = "";
		$order_by = "";

		if(!empty($data['Storage_id']) && empty($data['query'])) {
			$filter .= " and Storage.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		} else {
			if(!empty($data['StorageType_id'])) {
				$filter .= " and Storage.StorageType_id = :StorageType_id";
				$params['StorageType_id'] = $data['StorageType_id'];
			}
			if(!empty($data['StorageTypeCode_List'])) {
				$filter .= " and StorageType.StorageType_Code in ({$data['StorageTypeCode_List']})";
			}
			if (!empty($data['Storage_sid'])) {
				//дочерние склады склада-поставщика
				$with[] = "storage_s_tree (Storage_id, Storage_pid) as (
					select
						i_s.Storage_id,
						i_s.Storage_pid
					from
						v_Storage i_s with (nolock)
					where
						i_s.Storage_pid = :Storage_sid 
					union all
					select
						i_s.Storage_id,
						i_s.Storage_pid
					from
						v_Storage i_s with (nolock)
						inner join storage_s_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
				)";
				$join .= " left join storage_s_tree on storage_s_tree.Storage_id = Storage.Storage_id ";
				$join .= "
					outer apply (					
						select top 1
							i_ssl.MedService_id
						from
							v_StorageStructLevel i_ssl with(nolock)
							left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id
							left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
						where
							i_ssl.Storage_id = Storage.Storage_id and
							i_mst.MedServiceType_SysNick = 'merch'
						order by
							i_ssl.StorageStructLevel_id
					) storage_s_ms
				";
				$params['Storage_sid'] = $data['Storage_sid'];
			}
			if (
				!empty($data['Org_id']) || !empty($data['Lpu_oid']) || !empty($data['LpuBuilding_id'])
				|| !empty($data['LpuUnit_id']) || !empty($data['LpuSection_id']) || !empty($data['MedService_id'])
				|| !empty($data['OrgStruct_id'])
			) {
				$struct_filter = "1=1";
                if (!empty($data['StructFilterPreset']) && !empty($data['Lpu_oid'])) { //используем готовые персеты фильтров структуры
                    switch($data['StructFilterPreset']) {
                        case 'WhsDocumentUcEdit_Storage_tid': //форма swWhsDocumentUcEditWindow, склад поставщика
							if (!empty($data['LpuSection_id']) || !empty($data['Lpu_oid'])) {
								$ls_data = $this->getFirstRowFromQuery("
										select
											LpuBuilding_id,
											LpuUnit_id	
										from
											v_LpuSection with (nolock)
										where
											LpuSection_id = :LpuSection_id;
									", array(
									'LpuSection_id' => $data['LpuSection_id']
								));

								$params['LsLpuBuilding_id'] = !empty($ls_data['LpuBuilding_id']) ? $ls_data['LpuBuilding_id'] : null;
								$params['LsLpuUnit_id'] = !empty($ls_data['LpuUnit_id']) ? $ls_data['LpuUnit_id'] : null;
								$params['LsLpu_id'] = !empty($data['Lpu_oid']) ? $data['Lpu_oid'] : null;

								$with[] = "ls_storage_list as (
									select
										i_ssl.Storage_id,
										1 as SslGroup_Type
									from
										v_StorageStructLevel i_ssl with (nolock)
										left join v_Storage i_s with (nolock) on i_s.Storage_id = i_ssl.Storage_id
									where
										i_ssl.Lpu_id = :LsLpu_id and
										i_ssl.LpuSection_id is null and
										i_s.Storage_pid is null
									union all
									select
										i_ssl.Storage_id,
										(case
											when i_s.Storage_id = i_ssl.Storage_id then 2
											else 3
										end) as SslGroup_Type
									from
										v_StorageStructLevel i_ssl with (nolock)
										left join v_Storage i_s with (nolock) on i_s.Storage_id = i_ssl.Storage_id or i_s.Storage_pid = i_ssl.Storage_id
									where
										i_ssl.LpuBuilding_id = :LsLpuBuilding_id and
										i_ssl.LpuUnit_id is null
									union all
									select
										i_ssl.Storage_id,
										(case
											when i_s.Storage_id = i_ssl.Storage_id then 2
											else 3
										end) as SslGroup_Type
									from
										v_StorageStructLevel i_ssl with (nolock)
										left join v_Storage i_s with (nolock) on i_s.Storage_id = i_ssl.Storage_id or i_s.Storage_pid = i_ssl.Storage_id
									where
										i_ssl.LpuUnit_id = :LsLpuUnit_id and
										i_ssl.LpuSection_id is null
								)";

								$join .= "
									outer apply (
										select top 1
											ls_storage_list.Storage_id,
											ls_storage_list.SslGroup_Type
										from
											ls_storage_list
										where
											ls_storage_list.Storage_id = Storage.Storage_id
										order by
											ls_storage_list.SslGroup_Type
									) ls_sl
								";

								$filter .= " and ls_sl.Storage_id is not null ";
								$order_by = "ls_sl.SslGroup_Type, Storage.Storage_Name";
							}
                            break;
                        case 'WhsDocumentUcEdit_Storage_sid':
                            break;
                        case 'EvnDrug_DocumentUcStr_Storage_id':
                        	if (!empty($data['LpuSection_id'])) { //форма swNewEvnDrugEditWindow поле склад
								if (!empty($data['LpuSection_id'])) {
									$storage_list = $this->queryResult("
										select distinct
											i_s.Storage_id,
											i_ms.MedService_id
										from
											v_StorageStructLevel i_ssl with (nolock)
											left join v_Storage i_s with (nolock) on i_s.Storage_id = i_ssl.Storage_id or i_s.Storage_pid = i_ssl.Storage_id -- склады прописаны на службе и их дочерние склады
											outer apply ( -- проверка связаны ли дочерние склады со службой с типом АРМ товароведа
												select top 1
													ii_ms.MedService_id
												from
													v_StorageStructLevel ii_ssl with (nolock)
													left join v_MedService ii_ms with (nolock) on ii_ms.MedService_id = ii_ssl.MedService_id			
													left join v_MedServiceType ii_mst with (nolock) on ii_mst.MedServiceType_id = ii_ms.MedServiceType_id
												where
													ii_ssl.Storage_id = i_s.Storage_id and
													ii_mst.MedServiceType_SysNick = 'merch'
												order by
													ii_ssl.StorageStructLevel_id
											) i_ms
										where
											i_ssl.LpuSection_id = :LpuSection_id and 
											i_s.Storage_id is not null and
											(	 
												i_s.Storage_id = i_ssl.Storage_id or
												i_ms.MedService_id is not null
											)
									", array(
										'LpuSection_id' => $data['LpuSection_id']
									));

									//дополнительная фильтрация списка складов
									$storage_id_list = array();
									if (count($storage_list) == 1 && !empty($storage_list[0]['MedService_id'])) { //если склад отделения единственный и является складом службы АРМ товаровед, то используем этот склад
										array_push($storage_id_list, $storage_list[0]['Storage_id']);
									} else { //иначе используем склады без привязки к службе АРМ товароведа
										foreach($storage_list as $storage_data) {
											if (empty($storage_data['MedService_id'])) {
												array_push($storage_id_list, $storage_data['Storage_id']);
                    }
										}
									}

									//формируем условие для фильтрации складов; если в списке складов пусто, то уловие всегда ложно
									$storage_id_str = count($storage_id_list) > 0 ? implode(",", $storage_id_list) : "0";
									$filter .= " and Storage.Storage_id in ({$storage_id_str})";
								}
							}
                            break;
						case 'DocumentUcEdit_Storage_sid_Lpu': //форма редактирования документа учета, поле "склад-поставщик", тип организации - МО
							if (!empty($data['MedService_id'])) {
								//склады, связанные со службой Товаровед
								$with[] = "ms_storage_list as (
									select distinct
										i_ssl.Storage_id
									from
										v_StorageStructLevel i_ssl with(nolock)
									where
										i_ssl.MedService_id = :MedService_id
								)";

								//дочерние склады (прямые потомки) этих складов, не связанные со службой с типом «товаровед».
								$with[] = "storage_tree as (
									select
										i_s.Storage_id
									from
										ms_storage_list i_msl with (nolock)
										left join v_Storage i_s with (nolock) on i_s.Storage_pid = i_msl.Storage_id
									where
										i_s.Storage_id is not null
								)";

								$join .= " left join ms_storage_list on ms_storage_list.Storage_id = Storage.Storage_id ";
								$join .= " left join storage_tree on storage_tree.Storage_id = Storage.Storage_id ";
								$join .= "
									outer apply (					
										select top 1
											i_ssl.MedService_id
										from
											v_StorageStructLevel i_ssl with(nolock)
											left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id
											left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
										where
											i_ssl.Storage_id = Storage.Storage_id and
											i_mst.MedServiceType_SysNick = 'merch'
										order by
											i_ssl.StorageStructLevel_id
									) storage_ms
								";
								$filter .= " and (
									ms_storage_list.Storage_id is not null or
									(
										storage_tree.Storage_id is not null and
										storage_ms.MedService_id is null
									)
								)";
								$params['MedService_id'] = $data['MedService_id'];
							}

							if (!empty($data['MolMedPersonal_id'])) { //склады связанные с МОЛ переданным из АРМ Товароведа
								$filter .= " and Storage.Storage_id in (
									select
										M.Storage_id
									from
										v_Mol M with (nolock)
									where
										M.MedPersonal_id = :MolMedPersonal_id and
										M.Storage_id is not null
								)";
								$params['MolMedPersonal_id'] = $data['MolMedPersonal_id'];
							}
							break;
						case 'DocumentUcEdit_Storage_tid_Lpu_UserOrg': //форма редактирования документа учета, поле "склад-получатель", тип организации - МО, организация является организацией пользователя
							if (!empty($data['Storage_sid'])) {
								//склады прописанные на подразделении склада-поставщика + потомки первого уровня склада поставщика + родитель склада поставщика
								$with[] = "ls_s_ssl as (
									select distinct
										i_ls_ssl.Storage_id
									from
										v_StorageStructLevel i_ssl with(nolock)
										left join v_StorageStructLevel i_ls_ssl with(nolock) on i_ls_ssl.LpuBuilding_id = i_ssl.LpuBuilding_id
									where
										i_ssl.Storage_id = :Storage_sid
									union
									select
										i_s.Storage_id
									from
										v_Storage i_s with (nolock)
									where
										i_s.Storage_pid = :Storage_sid
									union
									select
										i_s.Storage_pid
									from
										v_Storage i_s with (nolock)
									where
										i_s.Storage_id = :Storage_sid
								)";

								//формируем список складов из списков storage_s_tree и ls_s_ssl
								$with[] = "storage_list as (
									select distinct
										i_s.Storage_id
									from
										v_Storage i_s with (nolock)
										left join storage_s_tree on storage_s_tree.Storage_id = i_s.Storage_id
										left join ls_s_ssl on ls_s_ssl.Storage_id = i_s.Storage_id
										outer apply (					
											select top 1
												i_ssl.MedService_id
											from
												v_StorageStructLevel i_ssl with(nolock)
												left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id
												left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
											where
												i_ssl.Storage_id = i_s.Storage_id and
												i_mst.MedServiceType_SysNick = 'merch'
											order by
												i_ssl.StorageStructLevel_id
										) i_storage_s_ms
									where
										(
											i_storage_s_ms.MedService_id is null and
											storage_s_tree.Storage_id is not null
										) or (
											i_storage_s_ms.MedService_id is not null and
											ls_s_ssl.Storage_id is not null
										)
								)";

								//получаем потомков складов из списка storage_list (за исключением складов оделений, для них потомков не получаем)
								$with[] = "children_list as ( -- дочерние склады складов из storage_list (кроме тех, что являются складами отделения)
									select distinct
										s.Storage_id
									from
										storage_list
										outer apply (
											select top 1
												i_ssl.MedService_id
											from
												v_StorageStructLevel i_ssl with (nolock)
												left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id
												left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
											where
												i_ssl.Storage_id = storage_list.Storage_id and	
												i_ms.LpuSection_id is not null and -- служба связана с отделением
												i_mst.MedServiceType_SysNick = 'merch' -- тип службы - АРМ товаровед
										) ls_ms
										inner join v_Storage s with (nolock) on s.Storage_pid = storage_list.Storage_id
									where
										ls_ms.MedService_id is null	
								)";

								$join .= " left join storage_list on storage_list.Storage_id = Storage.Storage_id ";
								$join .= " left join children_list on children_list.Storage_id = Storage.Storage_id ";
								$filter .= " and (
									storage_list.Storage_id is not null or
									children_list.Storage_id is not null
								)";
							}

							//оставляем только склады МО
							$filter .= " and Storage.Storage_id in (
									select
										SSL.Storage_id
									from
										v_StorageStructLevel SSL with(nolock)
										left join v_Lpu L with(nolock) on L.Lpu_id = SSL.Lpu_id
									where
										SSL.Lpu_id = :Lpu_oid
								)";
							$params['Lpu_oid'] = $data['Lpu_oid'];
							break;
						case 'DocumentUcEdit_Storage_tid_Lpu': //форма редактирования документа учета, поле "склад-получатель", тип организации - МО, организация не является организацией пользователя
							//оставляем только склады МО, котрые связаны со службами с типом "АРМ Товароведа"
							$filter .= " and Storage.Storage_id in (
								select
									SSL.Storage_id
								from
									v_StorageStructLevel SSL with(nolock)
									left join v_Lpu L with(nolock) on L.Lpu_id = SSL.Lpu_id										
									left join v_MedService MS with (nolock) on MS.MedService_id = SSL.MedService_id
									left join v_MedServiceType MST with (nolock) on MST.MedServiceType_id = MS.MedServiceType_id
								where
									SSL.Lpu_id = :Lpu_oid and
									MST.MedServiceType_SysNick = 'merch'										
							)";
							$params['Lpu_oid'] = $data['Lpu_oid'];

							if (!empty($data['MolMedPersonal_id'])) { //склады связанные с МОЛ переданным из АРМ Товароведа
								$filter .= " and Storage.Storage_id in (
									select
										M.Storage_id
									from
										v_Mol M with (nolock)
									where
										M.MedPersonal_id = :MolMedPersonal_id and
										M.Storage_id is not null
								)";
								$params['MolMedPersonal_id'] = $data['MolMedPersonal_id'];
							}
							break;
                    }
                } else {
                    if (!empty($data['Org_id'])) {
                        $struct_filter .= " and isnull(SSL.Org_id, L.Org_id) = :Org_id";
                        $params['Org_id'] = $data['Org_id'];
                    }
                    if (!empty($data['Lpu_oid'])) {
                        $struct_filter .= " and SSL.Lpu_id = :Lpu_oid";
                        $params['Lpu_oid'] = $data['Lpu_oid'];
                    }
                    if (!empty($data['LpuBuilding_id'])) {
                        $struct_filter .= " and SSL.LpuBuilding_id = :LpuBuilding_id";
                        $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
                    }
                    if (!empty($data['LpuUnit_id'])) {
                        $struct_filter .= " and SSL.LpuUnit_id = :LpuUnit_id";
                        $params['LpuUnit_id'] = $data['LpuUnit_id'];
                    }
                    if (!empty($data['LpuSection_id'])) {
                        $struct_filter .= " and SSL.LpuSection_id = :LpuSection_id";
                        $params['LpuSection_id'] = $data['LpuSection_id'];
                    }
                    if (!empty($data['MedService_id'])) {
                        $struct_filter .= " and SSL.MedService_id = :MedService_id";
                        $params['MedService_id'] = $data['MedService_id'];
                    }
                    if (!empty($data['OrgStruct_id'])) {
                        $struct_filter .= " and SSL.OrgStruct_id = :OrgStruct_id";
                        $params['OrgStruct_id'] = $data['OrgStruct_id'];
                    }
                    if (isset($data['session']) && !empty($data['filterByOrgUser']) && $data['filterByOrgUser']) {
                        $OrgStructList = $this->queryResult("
                            select PW.OrgStruct_id
                            from v_pmUserCacheOrg PUO with(nolock)
                            inner join v_PersonWork PW with(nolock) on PW.pmUserCacheOrg_id = PUO.pmUserCacheOrg_id
                            where PUO.pmUserCache_id = :pmUser_id and PUO.Org_id = :Org_id
                        ", array(
                            'pmUser_id' => $data['session']['pmuser_id'],
                            'Org_id' => $data['session']['org_id'],
                        ));
                        if (!is_array($OrgStructList)) return false;
                        $get_ids = function($item){return !empty($item['OrgStruct_id'])?$item['OrgStruct_id']:0;};
                        $ids_str = !empty($OrgStructList) ? implode(",", array_map($get_ids, $OrgStructList)):"0";

                        $struct_filter .= " and isnull(SSL.OrgStruct_id,0) in ({$ids_str})";

                        $struct_filter .= " and SSL.Org_id = :Org_id_ses";
                        $params['Org_id_ses'] = $data['session']['org_id'];
                    }
                }

                if (strlen($struct_filter) > 3) {
				$filter .= " and Storage.Storage_id in (
                    select Storage_id
                    from v_StorageStructLevel SSL with(nolock)
                    left join v_Lpu L with(nolock) on L.Lpu_id = SSL.Lpu_id
                    where {$struct_filter}
                )";
			}
			}
			if (!empty($data['date'])) {
				$filter .= " and Storage.Storage_begDate <= :date";
				$filter .= " and (Storage.Storage_endDate > :date or Storage.Storage_endDate is null)";
				$params['date'] = $data['date'];
			}
			if (!empty($data['query'])) {
				$filter .= " and Storage.Storage_Name like '%'+:Storage_Name+'%'";
				$params['Storage_Name'] = $data['query'];
			}
			if (!empty($data['EvnPrescrTreatDrug_id'])) {
				$TreatDrugInfo = $this->getFirstRowFromQuery("
					select top 1 Drug_id, DrugComplexMnn_id
					from v_EvnPrescrTreatDrug with(nolock)
					where EvnPrescrTreatDrug_id = :EvnPrescrTreatDrug_id
				", $data);
				if (!is_array($TreatDrugInfo)) {
					return false;
				}

				$filter .= " and Storage.Storage_id in (
					select DOR.Storage_id
					from v_DrugOstatRegistry DOR with(nolock)
					inner join rls.v_Drug D with(nolock) on D.Drug_id = DOR.Drug_id
					where DOR.DrugOstatRegistry_Kolvo > 0
					and (D.Drug_id = :Drug_id OR D.DrugComplexMnn_id = :DrugComplexMnn_id)
				)";
				$params['Drug_id'] = $TreatDrugInfo['Drug_id'];
				$params['DrugComplexMnn_id'] = $TreatDrugInfo['DrugComplexMnn_id'];
			}
		}

		$filter .= " and (Storage.Storage_endDate > GETDATE() or Storage.Storage_endDate is null)";

		if (empty($order_by)) {
			$order_by = "Storage.StorageType_id, Storage_Name";
		}

		if (!empty($data['StorageForAptMuFirst']) && $data['StorageForAptMuFirst']) {
			$order_by = "
				case when StrucLevel.Name = 'Lpu' then 1 else 0 end desc,
				Storage.StorageType_id,
				Storage_Name
			";
		}

		if (empty($data['Storage_id']) && !empty($data['Storage_sid'])) {
			//получение склада-родителя склада-Поставщика
			$query = "
				select
					Storage_pid
				from
					v_Storage with (nolock)
				where
					Storage_id = :Storage_sid;
			";
			$params['Storage_psid'] = $this->getFirstResultFromQuery($query, array(
				'Storage_sid' => $data['Storage_sid']
			));

			$join .= "
				outer apply (
					select
						(case
							when storage_s_tree.Storage_id is not null and storage_s_ms.MedService_id is null then 1
							when Storage.Storage_id = :Storage_psid then 2
							else 3
						end) as val
				) ord
			";
			$order_by = "
				ord.val,
				Storage.Storage_Name
			";
		}

		if (!empty($data['StructFilterPreset']) && $data['StructFilterPreset'] == 'EvnDrug_DocumentUcStr_Storage_id') {
			$join .= " outer apply (
				select top 1
					i_ms.MedService_id
				from
					v_StorageStructLevel i_ssl with (nolock)
					left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id			
					left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
				where
					i_ssl.Storage_id = Storage.Storage_id and
					i_mst.MedServiceType_SysNick = 'merch'
				order by
					i_ssl.StorageStructLevel_id
			) link_by_merch ";
			$order_by = "
				link_by_merch.MedService_id asc
			";
		}

		$with_clause = implode(', ', $with);
		if (strlen($with_clause)) {
			$with_clause = "
				with {$with_clause}
			";
		}

		$query = "
			{$with_clause}
			select
				Storage.Storage_id,
				Storage.StorageType_id,
				Storage.Storage_Code,
				rtrim(Storage.Storage_Name) as Storage_Name,
				convert(varchar(10), Storage.Storage_begDate, 104) as Storage_begDate,
				convert(varchar(10), Storage.Storage_endDate, 104) as Storage_endDate,
				StSL.LpuSection_id,
				StrucLevel.Name as StorageStructLevel,
				StSL.MedService_id,
				MST.MedServiceType_SysNick,
				isnull(StSL.Org_id,t1.Org_id) as Org_id,
				adr.Address_Address as Address
			from
				v_Storage Storage with (nolock)
				left join v_StorageStructLevel StSL with (nolock) on StSL.Storage_id = Storage.Storage_id
				left join v_Lpu_all t1 with(nolock) on t1.Lpu_id = StSL.Lpu_id
				left join v_MedService MS with(nolock) on MS.MedService_id = StSL.MedService_id
				left join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
				left join v_Address adr with(nolock) on adr.Address_id = Storage.Address_id
				outer apply (
					select top 1 case
						when StSL.MedService_id is not null then 'MedService_id'
						when StSL.LpuSection_id is not null then 'LpuSection'
						when StSL.LpuUnit_id is not null then 'LpuUnit'
						when StSL.LpuBuilding_id is not null then 'LpuBuilding'
						when StSL.Lpu_id is not null then 'Lpu'
						when StSL.Org_id is not null then 'Org'
					end as Name
				) StrucLevel
				{$join}
			where
				{$filter}
			order by {$order_by}
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			return false;
		}

		if (!empty($data['EvnPrescrTreatDrug_id'])) {
			$hasCommonStorage = false;
			$hasMerchStorage = false;

			foreach($result as $item) {
				if ($item['MedServiceType_SysNick'] == 'merch') {
					$hasMerchStorage = true;
				} else {
					$hasCommonStorage = true;
				}
			}

			if ($hasCommonStorage && $hasMerchStorage) {
				//Не выводить склады товароведа
				$result = array_filter($result, function($item) {
					return $item['MedServiceType_SysNick'] != 'merch';
				});
			}
		}

		return $result;
	}


        /**
	 *  Формирование списка складов с прикрепленными МО
	 */
	function loadStorage2LpuList($data) {
		$params = array();
		$filter = "(1=1)";

		if(!empty($data['Storage_id']) && $data['Storage_id'] > 0) {
			$filter .= " and Storage.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		} else {
			if(!empty($data['StorageType_id'])) {
				$filter .= " and Storage.StorageType_id = :StorageType_id";
				$params['StorageType_id'] = $data['StorageType_id'];
			}
			if(!empty($data['StorageTypeCode_List'])) {
				$filter .= " and StorageType.StorageType_Code in ({$data['StorageTypeCode_List']})";
			}
			if (!empty($data['Org_id']) && $data['Org_id'] > 0) {
				$filter .= " and Storage.Storage_id in (select Storage_id from v_StorageStructLevel with (nolock) where Org_id = :Org_id)";
				$params['Org_id'] = $data['Org_id'];
			}
			if (!empty($data['Lpu_oid']) && $data['Lpu_oid'] > 0) {
				$filter .= " and Storage.Storage_id in (select Storage_id from v_StorageStructLevel with (nolock) where Lpu_id = :Lpu_oid)";
				$params['Lpu_oid'] = $data['Lpu_oid'];
			}
			if (!empty($data['MedService_id']) && $data['MedService_id'] > 0) {
				$filter .= " and Storage.Storage_id in (select Storage_id from v_StorageStructLevel with (nolock) where MedService_id = :MedService_id)";
				$params['MedService_id'] = $data['MedService_id'];
			}
			if (!empty($data['LpuSection_id']) && $data['LpuSection_id'] > 0) {
				$filter .= " and Storage.Storage_id in (select Storage_id from v_StorageStructLevel with (nolock) where LpuSection_id = :LpuSection_id)";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			if (!empty($data['date'])) {
				$filter .= " and Storage.Storage_begDate <= :date";
				$filter .= " and (Storage.Storage_endDate > :date or Storage.Storage_endDate is null)";
				$params['date'] = $data['date'];
			}
			if (!empty($data['query'])) {
				$filter .= " and Storage.Storage_Name like '%'+:Storage_Name+'%'";
				$params['Storage_Name'] = $data['query'];
			}
		}

		$query = "
			select
				Storage.Storage_id,
				Storage.StorageType_id,
				Storage.Storage_Code,
				rtrim(Storage.Storage_Name) as Storage_Name,
                                farm.Lpu_id,
                                farm.Lpu_Nick
			from
				v_Storage Storage with (nolock)
                                outer apply (
					Select Top 1 i.Lpu_id, lpu.Lpu_Nick from v_OrgFarmacyIndex i with (nolock)
						left join v_lpu lpu with (nolock) on lpu.Lpu_id = i.Lpu_id
						where i.Storage_id = Storage.Storage_id
				)  farm
			where
				{$filter}
			order by Storage.StorageType_id, Storage_Name;
		";

                //echo getDebugSQL($query, $params); exit();

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Загрузка списка строк документа учета
	 */
	function loadDocumentUcStrList($filter) {
        $session_data = getSessionParams();
        $doc_type_code = !empty($filter['DrugDocumentType_Code']) ? $filter['DrugDocumentType_Code'] : null;

        $select = "";
        $join = "";

        if (empty($doc_type_code)) {
            $query = "
                select top 1
                    ddt.DrugDocumentType_Code
                from
                    v_DocumentUc du with (nolock)
                    left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                where
                    du.DocumentUc_id = :DocumentUc_id;
            ";
            $doc_type_code = $this->getFirstResultFromQuery($query, array(
                'DocumentUc_id' => $filter['DocumentUc_id']
            ));
        }

        switch($doc_type_code) {
            case '6': //Приходная накладная
                $select .= "convert(varchar(10), dus.DocumentUcStr_RegDate, 104) as DocumentUcStr_RegDate, ";
                $select .= "dus.DocumentUcStr_RegPrice, ";
                break;
            case '34': //Разукомплектация: списание
                $select .= "post_dus.DocumentUcStr_id as PostDocumentUcStr_id, ";
                $select .= "post_dus.GoodsUnit_bid as PostGoodsUnit_bid, ";
                $select .= "post_dus.GoodsUnit_bName as PostGoodsUnit_bName, ";
                $select .= "post_dus.GoodsUnit_id as PostGoodsUnit_id, ";
                $select .= "post_dus.GoodsUnit_Name as PostGoodsUnit_Name, ";
                $select .= "post_dus.StorageZone_id as PostStorageZone_id, ";

                $select .= "post_dus.DocumentUcStr_Count as PostDocumentUcStr_Count, ";
                $select .= "post_dus.DocumentUcStr_EdCount as PostDocumentUcStr_EdCount, ";
                $select .= "post_dus.DocumentUcStr_Price as PostDocumentUcStr_Price, ";
                $select .= "post_dus.DocumentUcStr_Sum as PostDocumentUcStr_Sum, ";
                $select .= "post_dus.DrugNds_id as PostDrugNds_id, ";
                $select .= "isnull(isnds.YesNo_Code, 0) as PostDocumentUcStr_IsNDS, ";
                $select .= "
                    (
                        case
                            when
                                isnull(isnds.YesNo_Code, 0) = 1
                            then
                                isnull(post_dus.DocumentUcStr_Sum, 0)
                            else
                                isnull(post_dus.DocumentUcStr_Sum, 0) + isnull(post_dus.DocumentUcStr_SumNds, 0)
                        end

                    ) as PostDocumentUcStr_NdsSum, ";
                $select .= "post_dus.DocumentUcStr_SumNds as PostDocumentUcStr_SumNds, ";

                $join .= "
                    outer apply (
                        select top 1
                            i_dus.DocumentUcStr_id,
                            i_dus.GoodsUnit_id,
                            i_dus.GoodsUnit_bid,
                            i_dus.StorageZone_id,
                            i_dus.DocumentUcStr_Count,
                            i_dus.DocumentUcStr_EdCount,
                            i_dus.DocumentUcStr_Price,
                            i_dus.DocumentUcStr_Sum,
                            i_dus.DrugNds_id,
                            i_dus.DocumentUcStr_SumNds,
                            i_gu.GoodsUnit_Name,
                            i_gu_b.GoodsUnit_Name as GoodsUnit_bName
                        from
                            v_DocumentUcStr i_dus with (nolock)
                            left join v_GoodsUnit i_gu with (nolock) on i_gu.GoodsUnit_id = i_dus.GoodsUnit_id
                            left join v_GoodsUnit i_gu_b with (nolock) on i_gu_b.GoodsUnit_id = i_dus.GoodsUnit_bid
                        where
                            i_dus.DocumentUcStr_sid = dus.DocumentUcStr_id
                        order by
                            i_dus.DocumentUcStr_id
                    ) post_dus
                ";
                break;
        }

		$query = "
			select
				dus.DocumentUcStr_id,
				dus.DocumentUcStr_oid,
				(
				    o_ds.DrugShipment_Name+
				    isnull(' '+o_ds.AccountType_Name, '')
				) as DocumentUcStr_oName,
				dus.Drug_id,
				d.Drug_Name,
				dnmn.DrugNomen_Code,
				dus.DocumentUcStr_PlanKolvo,
				dus.DocumentUcStr_Count,
				dus.DocumentUcStr_EdCount,
				--dus.DocumentUcStr_RashCount,
				--dus.DocumentUcStr_RashEdCount,
				dus.DrugNds_id,
				isnull(dn.DrugNds_Code, 0) as DrugNds_Code,
				dus.DocumentUcStr_Price,
				--dus.DocumentUcStr_NdsPrice,
				dus.DocumentUcStr_Sum,
				dus.DocumentUcStr_SumNds,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(dus.DocumentUcStr_Sum, 0)
						else
							isnull(dus.DocumentUcStr_Sum, 0) + isnull(dus.DocumentUcStr_SumNds, 0)
					end
				) as DocumentUcStr_NdsSum,
				isnull(ps.PrepSeries_Ser, dus.DocumentUcStr_Ser) DocumentUcStr_Ser,
				convert(varchar(10), dus.DocumentUcStr_godnDate, 104) as DocumentUcStr_godnDate,
				convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				ps.PrepSeries_id,
				isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
				dus.DocumentUcStr_CertNum,
				convert(varchar(10), dus.DocumentUcStr_CertDate, 104) as DocumentUcStr_CertDate,
				convert(varchar(10), dus.DocumentUcStr_CertGodnDate, 104) as DocumentUcStr_CertGodnDate,
				dus.DocumentUcStr_CertOrg,
				dus.DrugLabResult_Name,
				isnull(isnds.YesNo_Code, 0) as DocumentUcStr_IsNDS,
				isnull(okei.Okei_NationSymbol, 'упак') as Okei_NationSymbol,
				sf.cnt as SavedFileCount,
				ds.DrugShipment_Name,
				ds.AccountType_Name,
				dus.DocumentUcStr_Reason,
				pers.Person_id,
				isnull(pers.Person_SurName + ' ', '') + isnull(pers.Person_FirName + ' ', '') + isnull(pers.Person_SecName,'') as Person_Fio,
				dus.DrugDocumentStatus_id,
				dds.DrugDocumentStatus_Code,
				dds.DrugDocumentStatus_Name,
				dus.StorageZone_id,
				(case
				    when
				        sz.StorageZone_id is not null
				    then
				        rtrim(isnull(sz.StorageZone_Address,'') + ' ' + isnull(sut.StorageUnitType_Name,''))
				    else
				        'Без места хранения'
				end) as StorageZone_Name,
				last_dusw.DocumentUcStorageWork_id,
				last_dusw.DocumentUcStorageWork_FactQuantity,
				last_dusw.DocumentUcStorageWork_Comment,
				gu.GoodsUnit_id,
				gu.GoodsUnit_Name,
				gu_b.GoodsUnit_id as GoodsUnit_bid,
				gu_b.GoodsUnit_Name as GoodsUnit_bName,
				isnull(dpbc.cnt, 0) as SavedBarCode_Count,
				isnull(dpbc.cnt, 0) as BarCode_Count,
				(case
				    when gu.GoodsUnit_Name = 'упаковка' then 1
				    when gpc.GoodsPackCount_Count > 0 then gpc.GoodsPackCount_Count
				    when gpc.GoodsPackCount_Count is null and d.Drug_Fas is not null then d.Drug_Fas
				    else null
				end) as GoodsPackCount_Count,
				(
                    case
                        when isnull(am.NARCOGROUPID, 0) > 0 or isnull(am.STRONGGROUPID, 0) > 0
                        then 1
                        else null
                    end
                ) as Drug_isPKU,
				{$select}
				d.Drug_Nomen as hintPackagingData,  --Данные об упаковке – это часть поля Упаковка (drug_nomen) от начала строки до данных о производителе.
				RTRIM(ISNULL(dp.DrugPrep_Name, '')) as hintTradeName, --Торговое наименование, лекарственная форма, дозировка, фасовка
				isnull(d.Drug_RegNum + ',   ', '') + isnull(convert(varchar(10), d.Drug_begDate, 104) + ', ', '') + isnull(convert(varchar(10), d.Drug_endDate, 104) +', ', '--, ') + isnull(convert(varchar(10), REG.REGCERT_insDT, 104)+', ', '') + isnull(REGISTR.regNameCauntries, '') as hintRegistrationData, --Данные о регистрации
				case
					when NOMENF.FIRMS_ID = MANUFACTURF.FIRMS_ID
					then isnull(MANUFACTURF.FULLNAME, '')
					else
						case
							when (NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)='') AND (MANUFACTURF.FULLNAME IS NULL OR rtrim(MANUFACTURF.FULLNAME)='')
								then isnull(REGISTR.regNameCauntries, '')
							when NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)=''
								then isnull(MANUFACTURF.FULLNAME, '')
							else isnull(MANUFACTURF.FULLNAME, '') + ' / ' + NOMENF.FULLNAME
						end
				end as hintPRUP,	--ПР./УП.
				FNM.NAME as FirmNames
			from
				v_DocumentUcStr dus with (nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.Actmatters_id
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_Okei okei with (nolock) on okei.Okei_id = dus.Okei_id
				left join v_PersonState pers with (nolock) on pers.Person_id = dus.Person_id
				left join v_StorageZone sz with (nolock) on sz.StorageZone_id = dus.StorageZone_id
				left join v_StorageUnitType sut with (nolock) on sut.StorageUnitType_id = sz.StorageUnitType_id
				left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = dus.DrugDocumentStatus_id
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = dus.GoodsUnit_id
				left join v_GoodsUnit gu_b with (nolock) on isnull(gu_b.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id)

				left join rls.drugprep dp with(nolock) on d.DrugPrepFas_id = dp.DrugPrepFas_id
				left join rls.PREP P with(nolock) on P.Prep_id = d.DrugPrep_id
				left join rls.REGCERT REG with(nolock) on REG.REGCERT_ID =P.REGCERTID
				left join rls.NOMEN NM with(nolock) on NM.Nomen_id = d.Drug_id

				left join rls.FIRMS NOMENF with(nolock) on NM.FIRMID = NOMENF.FIRMS_ID
				left join rls.FIRMS MANUFACTURF with(nolock) on P.FIRMID = MANUFACTURF.FIRMS_ID
				left join rls.FIRMNAMES FNM with(nolock) on FNM.FIRMNAMES_ID = MANUFACTURF.NAMEID

				outer apply (
				    select
                        count(i_dpbc.DrugPackageBarCode_id) as cnt
                    from
                        v_DrugPackageBarCode i_dpbc with (nolock)
                    where
                        i_dpbc.DocumentUcStr_id = dus.DocumentUcStr_id
				) dpbc
				outer apply(
					SELECT TOP 1
						FN.NAME + ' (' + C.NAME + ')' as regNameCauntries
					FROM
						rls.REGCERT_EXTRAFIRMS RE
						left join rls.FIRMS F with(nolock) on RE.FIRMID = F.FIRMS_ID
						left join rls.FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
						left join rls.COUNTRIES C with(nolock) on C.COUNTRIES_ID = F.COUNTID
					WHERE RE.CERTID = P.REGCERTID
				) REGISTR
				outer apply (
					select top 1
						DrugNomen_Code
					from
						rls.v_DrugNomen with (nolock)
					where
						v_DrugNomen.Drug_id = d.Drug_id
				) dnmn
				outer apply (
					select
						count(md.pmMediaData_id) as cnt
					from
						v_pmMediaData md with (nolock)
					where
						md.pmMediaData_ObjectName = 'DocumentUcStr' and
						md.pmMediaData_ObjectID = dus.DocumentUcStr_id
				) sf
				outer apply (
					select top 1
						i_dsh.DrugShipment_Name,
						i_at.AccountType_Name
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_dsh with (nolock) on i_dsh.DrugShipment_id = i_dsl.DrugShipment_id
						left join v_AccountType i_at with (nolock) on i_at.AccountType_id = i_dsh.AccountType_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_id
					order by
						i_dsl.DrugShipmentLink_id
				) ds
				outer apply (
					select top 1
						i_dsh.DrugShipment_Name,
						i_at.AccountType_Name
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_dsh with (nolock) on i_dsh.DrugShipment_id = i_dsl.DrugShipment_id
						left join v_AccountType i_at with (nolock) on i_at.AccountType_id = i_dsh.AccountType_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					order by
						i_dsl.DrugShipmentLink_id
				) o_ds
				outer apply (
					select top 1
						dusw.*
					from
						v_DocumentUcStorageWork dusw with(nolock)
					where
						dusw.DocumentUcStr_id = dus.DocumentUcStr_id
					order by
						dusw.DocumentUcStorageWork_id desc
				) last_dusw
				outer apply (
                    select top 1
                        i_gpc.GoodsPackCount_Count
                    from
                        v_GoodsPackCount i_gpc with (nolock)
                    where
                        i_gpc.GoodsUnit_id = gu.GoodsUnit_id and
                        i_gpc.DrugComplexMnn_id = d.DrugComplexMnn_id and
                        (
                            d.DrugTorg_id is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = d.DrugTorg_id
                        )
                    order by
                        i_gpc.TRADENAMES_ID desc
                ) gpc
                {$join}
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";

		$result = $this->queryResult($query, array(
			'DocumentUc_id' => $filter['DocumentUc_id'],
			'DefaultGoodsUnit_id' => $this->getDefaultGoodsUnitId()
		));

		if (!is_array($result)) {
			return false;
		}

		$query = "
			select
				dusw.DocumentUcStorageWork_id,
				dusw.DocumentUcStr_id,
				convert(varchar(10), dusw.DocumentUcStorageWork_insDT, 104) as DocumentUcStorageWork_insDT,
				convert(varchar(10), dusw.DocumentUcStorageWork_endDate, 104)+' '+convert(varchar(5), dusw.DocumentUcStorageWork_endDate, 108) as DocumentUcStorageWork_endDate,
				dusw.DocumentUcTypeWork_id,
				dusw.Person_eid,
				dusw.Post_eid,
				ePW.PersonWork_id as PersonWork_eid,
				dusw.DocumentUcStorageWork_FactQuantity,
				dusw.DocumentUcStorageWork_Comment,
				1 as RecordStatus_Code
			from
				v_DocumentUcStorageWork dusw with(nolock)
				inner join v_DocumentUcStr dus with(nolock) on dus.DocumentUcStr_id = dusw.DocumentUcStr_id
				left join v_PersonWork ePW with(nolock) on ePW.Person_id = dusw.Person_eid and ePW.Post_id = dusw.Post_eid and ePW.Org_id = :Org_id
			where
				dus.DocumentUc_id = :DocumentUc_id
			order by
				dusw.DocumentUcStorageWork_insDT
		";
		$StorageWorkList = $this->queryResult($query, array(
			'DocumentUc_id' => $filter['DocumentUc_id'],
			'Org_id' => $session_data['session']['org_id']
		));
		if (!is_array($StorageWorkList)) {
			return false;
		}
		$StorageWorkData = array();
		foreach($StorageWorkList as $item) {
			$key = $item['DocumentUcStr_id'];
			$StorageWorkData[$key][] = $item;
		}
		foreach($result as &$item) {
			$key = $item['DocumentUcStr_id'];
			if (!empty($StorageWorkData[$key])) {
				$item['DocumentUcStorageWorkData'] = json_encode($StorageWorkData[$key]);
			}
		}

		return $result;
	}

	/**
	 * Загрузка строки документа учета
	 */
	function loadDocumentUcStr($filter) {
		$query = "
			select
				dus.DocumentUcStr_id,
				dus.DrugDocumentStatus_id,
				dus.DocumentUcStr_Count,
				dus.Drug_id,
				dus.DocumentUcStr_oid
			from
				v_DocumentUcStr dus with (nolock)
			where
				dus.DocumentUcStr_id = :DocumentUcStr_id;
		";

		$result = $this->db->query($query, array(
			'DocumentUcStr_id' => $filter['DocumentUcStr_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение строк документов учета из сереализованного массива
	 */
	function saveDocumentUcStrFromJSON($data) {
		$result = array();
        $status_new_id = $this->getObjectIdByCode('DrugDocumentStatus', 1); //Новый
        $status_executed_id = $this->getObjectIdByCode('DrugDocumentStatus', 4); //Исполнен

		$this->load->model("EmergencyTeam_model4E", "et_model");

		if (!empty($data['json_str']) && $data['DocumentUc_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);

			foreach($dt as $record) {
                //получаем статус строки, если статус исполнен - то пропускаем строку
                if ($record->state != 'add' && !empty($record->DocumentUcStr_id)) {
                    $query = "
                        select
                            DrugDocumentStatus_id
                        from
                            v_DocumentUcStr with (nolock)
                        where
                            DocumentUcStr_id = :DocumentUcStr_id;
                    ";
                    $status_id = $this->getFirstResultFromQuery($query, array(
                        'DocumentUcStr_id' => $record->DocumentUcStr_id
                    ));
                    if ($status_id == $status_executed_id) {
                        continue;
                    }
                }

				switch($record->state) {
					case 'add':
					case 'edit':
						if ($data['region'] == 'ufa' && $data['orgtype'] == 'farm') {
							$series_id = null;
						} else if (!empty($record->DocumentUcStr_Ser)) {
                            $series_id = $this->savePrepSeries(array(
                                'Drug_id' => $record->Drug_id,
                                'PrepSeries_Ser' => $record->DocumentUcStr_Ser,
                                'PrepSeries_GodnDate' => !empty($record->PrepSeries_GodnDate) ? $record->PrepSeries_GodnDate : null,
                                'pmUser_id' => $data['pmUser_id']
                            ));
                        } else {
                            $series_id = null;
                        }

						$save_data = array(
							'DocumentUcStr_id' => $record->state == 'edit' ? $record->DocumentUcStr_id : null,
							'DocumentUc_id' => $data['DocumentUc_id'],
							'Drug_id' => $record->Drug_id > 0 ? $record->Drug_id : null,
							'Okei_id' => isset($record->Okei_id) && $record->Okei_id > 0 ? $record->Okei_id : null,
							'DocumentUcStr_oid' => isset($record->DocumentUcStr_oid) && $record->DocumentUcStr_oid > 0 ? $record->DocumentUcStr_oid : null,
							'DocumentUcStr_PlanKolvo' => isset($record->DocumentUcStr_PlanKolvo) && $record->DocumentUcStr_PlanKolvo > 0 ? $record->DocumentUcStr_PlanKolvo : null,
							'DocumentUcStr_Count' => isset($record->DocumentUcStr_Count) && $record->DocumentUcStr_Count > 0 ? $record->DocumentUcStr_Count : null,
							'DocumentUcStr_EdCount' => isset($record->DocumentUcStr_EdCount) && $record->DocumentUcStr_EdCount > 0 ? $record->DocumentUcStr_EdCount : null,
							'DocumentUcStr_RashCount' => isset($record->DocumentUcStr_RashCount) && $record->DocumentUcStr_RashCount > 0 ? $record->DocumentUcStr_RashCount : null,
							'DocumentUcStr_Price' => isset($record->DocumentUcStr_Price) && $record->DocumentUcStr_Price >= 0 ? $record->DocumentUcStr_Price : null,
							'DocumentUcStr_PriceR' => isset($record->DocumentUcStr_Price) && $record->DocumentUcStr_Price >= 0 ? $record->DocumentUcStr_Price : null,
							'DocumentUcStr_Sum' => isset($record->DocumentUcStr_Sum) && $record->DocumentUcStr_Sum >= 0 ? $record->DocumentUcStr_Sum : null,
							'DocumentUcStr_SumR' => isset($record->DocumentUcStr_Sum) && $record->DocumentUcStr_Sum >= 0 ? $record->DocumentUcStr_Sum : null,
							'DocumentUcStr_SumNds' => isset($record->DocumentUcStr_SumNds) && $record->DocumentUcStr_SumNds >= 0 ? $record->DocumentUcStr_SumNds : null,
							'DocumentUcStr_SumNdsR' => isset($record->DocumentUcStr_SumNds) && $record->DocumentUcStr_SumNds >= 0 ? $record->DocumentUcStr_SumNds : null,
							'DrugNds_id' => isset($record->DrugNds_id) && $record->DrugNds_id > 0 ? $record->DrugNds_id : null,
							'PrepSeries_id' => $series_id,
							'DocumentUcStr_Ser' => !empty($record->DocumentUcStr_Ser) ? $record->DocumentUcStr_Ser : null,
							'DocumentUcStr_godnDate' => !empty($record->DocumentUcStr_godnDate) ? $this->formatDate($record->DocumentUcStr_godnDate) : null,
							'DocumentUcStr_CertNum' => !empty($record->DocumentUcStr_CertNum) ? $record->DocumentUcStr_CertNum : null,
							'DocumentUcStr_CertDate' => !empty($record->DocumentUcStr_CertDate) ? $this->formatDate($record->DocumentUcStr_CertDate) : null,
							'DocumentUcStr_CertGodnDate' => !empty($record->DocumentUcStr_CertGodnDate) ? $this->formatDate($record->DocumentUcStr_CertGodnDate) : null,
							'DocumentUcStr_CertOrg' => !empty($record->DocumentUcStr_CertOrg) ? $record->DocumentUcStr_CertOrg : null,
							'DocumentUcStr_Reason' => !empty($record->DocumentUcStr_Reason) ? $record->DocumentUcStr_Reason : null,
							'DrugLabResult_Name' => !empty($record->DrugLabResult_Name) ? $record->DrugLabResult_Name : null,
							'DocumentUcStr_IsNDS' => !empty($record->DocumentUcStr_IsNDS) ? $this->getObjectIdByCode('YesNo', $record->DocumentUcStr_IsNDS) : null,
							'EmergencyTeam_id' => !empty($data['EmergencyTeam_id']) ? $data['EmergencyTeam_id'] : null,
							'Person_id' => !empty($data['Person_id']) ? $data['Person_id'] : null,
							'Lpu_id' => !empty($record->Lpu_id) ? $record->Lpu_id : null,
                            'DrugFinance_id' => !empty($record->DrugFinance_id) ? $record->DrugFinance_id : null,
                            'StorageZone_id' => !empty($record->StorageZone_id) ? $record->StorageZone_id : null,
                            'GoodsUnit_id' => !empty($record->GoodsUnit_id) ? $record->GoodsUnit_id : null,
                            'DocumentUcStr_sid' => !empty($record->DocumentUcStr_sid) ? $record->DocumentUcStr_sid : null,
                            'GoodsUnit_bid' => !empty($record->GoodsUnit_bid) ? $record->GoodsUnit_bid : null,
                            'DocumentUcStr_RegDate' => !empty($record->DocumentUcStr_RegDate) ? $record->DocumentUcStr_RegDate : null,
                            'DocumentUcStr_RegPrice' => !empty($record->DocumentUcStr_RegPrice) ? $record->DocumentUcStr_RegPrice : null,
							'pmUser_id' => $data['pmUser_id']
						);

                        //при добавлении новой строки, проставляем ей статус "Новый"
						if(!empty($data['DrugDocumentType_Code']) && $data['DrugDocumentType_Code'] == 6 && $record->state == 'add') {
							$save_data['DrugDocumentStatus_id'] = $status_new_id;
						}

						//сохранение строки докуиента учета
						if ($data['region'] == 'ufa' && $data['orgtype'] == 'farm') {
						    $response = $this->farm_saveObject('DocumentUcStr', $save_data);
						}
						else {
						    $response = $this->saveObject('DocumentUcStr', $save_data);
						}

						//подготовка данных для сохранения файлов
						if (is_array($response) && isset($response['DocumentUcStr_id']) > 0) {
                            if (!empty($data['PostDocumentUc_id'])) { //если передан идентификатор приходного документа разукомплектации то дублируем данные строки в него
                                //ищем связанную строку
                                $post_str_id = $record->state != 'add' ? (!empty($record->PostDocumentUcStr_id) ? $record->PostDocumentUcStr_id : $this->getDocRazPostStrId($data['PostDocumentUc_id'], $response['DocumentUcStr_id'])) : null;

                                $save_data['DocumentUcStr_id'] = $post_str_id;
                                $save_data['DocumentUc_id'] = $data['PostDocumentUc_id'];
                                $save_data['DocumentUcStr_sid'] = $response['DocumentUcStr_id'];
                                $save_data['GoodsUnit_bid'] = isset($record->PostGoodsUnit_bid) && $record->PostGoodsUnit_bid > 0 ? $record->PostGoodsUnit_bid : null;
                                $save_data['GoodsUnit_id'] = isset($record->PostGoodsUnit_id) && $record->PostGoodsUnit_id > 0 ? $record->PostGoodsUnit_id : null;
                                $save_data['StorageZone_id'] = isset($record->PostStorageZone_id) && $record->PostStorageZone_id > 0 ? $record->PostStorageZone_id : null;
                                $save_data['DocumentUcStr_Count'] = isset($record->PostDocumentUcStr_Count) && $record->PostDocumentUcStr_Count > 0 ? $record->PostDocumentUcStr_Count : null;
                                $save_data['DocumentUcStr_EdCount'] = isset($record->PostDocumentUcStr_EdCount) && $record->PostDocumentUcStr_EdCount > 0 ? $record->PostDocumentUcStr_EdCount : null;
                                $save_data['DocumentUcStr_Price'] = isset($record->PostDocumentUcStr_Price) && $record->PostDocumentUcStr_Price > 0 ? $record->PostDocumentUcStr_Price : null;
                                $save_data['DocumentUcStr_PriceR'] = $save_data['DocumentUcStr_Price'];
                                $save_data['DocumentUcStr_Sum'] = isset($record->PostDocumentUcStr_Sum) && $record->PostDocumentUcStr_Sum > 0 ? $record->PostDocumentUcStr_Sum : null;
                                $save_data['DocumentUcStr_SumR'] = $save_data['DocumentUcStr_Sum'];
                                $save_data['DocumentUcStr_SumNds'] = isset($record->PostDocumentUcStr_SumNds) && $record->PostDocumentUcStr_SumNds > 0 ? $record->PostDocumentUcStr_SumNds : null;
                                $save_data['DocumentUcStr_SumNdsR'] = $save_data['DocumentUcStr_SumNds'];
                                $post_response = $this->saveObject('DocumentUcStr', $save_data);
                            }

							//сохранение файлов
							if (isset($record->FileChangedData) && is_array($record->FileChangedData)) {
								if (!isset($result['file_data'])) {
									$result['file_data'] = array();
								}
								$result['file_data'][] = array(
									'DocumentUcStr_id' => $response['DocumentUcStr_id'],
									'changed_data' => $record->FileChangedData
								);
							}

							//сохранение партии
							if ($data['region'] == 'ufa' && $data['orgtype'] == 'farm') {
							    if (!empty($record->DrugShipment_Name)) {
								    $this->farm_saveLinkedDrugShipment(array(
									    'DocumentUcStr_id' => $response['DocumentUcStr_id'],
									    'DrugShipment_Name' => $record->DrugShipment_Name.'',
									    'pmUser_id' => $data['pmUser_id']
								    ));
							    }
							}
							else {
							    if (!empty($record->DrugShipment_Name)) {
								    $this->saveLinkedDrugShipment(array(
									    'DocumentUcStr_id' => $response['DocumentUcStr_id'],
									    'DrugShipment_Name' => $record->DrugShipment_Name.'',
									    'pmUser_id' => $data['pmUser_id']
								    ));
							    }
							}

                            //сохранение информации о количестве ед. списания в упаковке
                            /*if (!empty($record->GoodsUnit_id) && !empty($record->GoodsPackCount_Count) && !in_array($record->GoodsPackCount_Source, array('table', 'fixed_value'))) { //сохраняем только количество введенное самим пользователем
                                $resp = $this->saveGoodsPackCount(array(
                                    'Drug_id' => $record->Drug_id,
                                    'GoodsUnit_id' => $record->GoodsUnit_id,
                                    'GoodsPackCount_Count' => $record->GoodsPackCount_Count
                                ));
                            }*/

                            //сохранение данных о штрих-кодах
                            if (isset($record->BarCodeChangedData) && is_array($record->BarCodeChangedData)) {
                                $resp = $this->saveBarCodeChangedData(array(
                                    'DocumentUcStr_id' => $response['DocumentUcStr_id'],
                                    'changed_data' => $record->BarCodeChangedData
                                ));
                            }

							if (!empty($record->DocumentUcStorageWorkData)) {
								$StorageWorkData = json_decode($record->DocumentUcStorageWorkData, true);

								foreach($StorageWorkData as $StorageWork) {
									$StorageWork = array_merge($StorageWork, array(
										'DocumentUcStr_id' => $response['DocumentUcStr_id'],
										'pmUser_id' => $data['pmUser_id'],
									));
									if (!empty($StorageWork['DocumentUcStorageWork_endDate'])) {
										$StorageWork['DocumentUcStorageWork_endDate'] = ConvertDateTimeFormat($StorageWork['DocumentUcStorageWork_endDate'].':00');
									}
									switch($StorageWork['RecordStatus_Code']) {
										case 0:
											$StorageWork['DocumentUcStorageWork_id'] = null;
											$resp = $this->saveDocumentUcStorageWork($StorageWork);
											break;
										case 2:
											$resp = $this->saveDocumentUcStorageWork($StorageWork);
											break;
										case 3:
											$resp = $this->deleteDocumentUcStorageWork($StorageWork);
											break;
									}
									if (!$this->isSuccessful($resp)) {
										return $resp;
									}
								}
							}

							//Сохраняем укладку бригады СМП
							if (!empty($data['EmergencyTeam_id'])) {
								$save_data['EmergencyTeamDrugPackMove_Quantity'] = $save_data['DocumentUcStr_EdCount'];
								$save_data['DocumentUcStr_id'] = $response['DocumentUcStr_id'];
								$etdpm_result = $this->et_model->saveEmergencyTeamDrugPackMove($save_data);
								if (!$this->isSuccessful( $etdpm_result )) {
									return $etdpm_result;
								}
							}
						}
						break;
					case 'delete':
						//удаление строки учета
						if ($data['region'] == 'ufa' && $data['orgtype'] == 'farm') {
						    $response = $this->farm_deleteDocumentUcStr(array(
							    'DocumentUcStr_id' => $record->DocumentUcStr_id,
                                'pmUser_id' => $data['pmUser_id']
						    ));
						}
						else {
						    $response = $this->deleteDocumentUcStr(array(
							    'DocumentUcStr_id' => $record->DocumentUcStr_id,
                                'pmUser_id' => $data['pmUser_id']
						    ));
						}

						if ($response && (!isset($response['Error_Message']) || empty($response['Error_Message']))) {
							//удаление сопутствующих файлов
							$query = "
								select
									'delete' as state,
									pmMediaData_id,
									pmMediaData_FilePath
								from
									v_pmMediaData with (nolock)
								where
									pmMediaData_ObjectName = 'DocumentUcStr' and
									pmMediaData_ObjectID = :DocumentUcStr_id;
							";
							/*
                                                         echo(getDebugSQL($query, array(
								'DocumentUcStr_id' => $record->DocumentUcStr_id
							))); exit;
							*/

							$res = $this->db->query($query, array(
								'DocumentUcStr_id' => $record->DocumentUcStr_id
							));
							$res = $res->result('array');
							if (is_array($res) && count($res) > 0) {
								if (!isset($result['file_data'])) {
									$result['file_data'] = array();
								}
								$result['file_data'][] = array(
									'DocumentUcStr_id' => $record->DocumentUcStr_id,
									'changed_data' => $res
								);
							}
						}
						break;
				}
			}
		}

		return $result;
	}

	/**
	 * Получение идентификатора типа документа по коду
	 */
	function getObjectIdByCode($object_name, $code) {
		$query = "
			select top 1
				{$object_name}_id
			from
				v_{$object_name} with (nolock)
			where
				{$object_name}_Code = :code;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $result && $result > 0 ? $result : false;
	}

	/**
	 *  Получние данных серии по медикаменту и серии.
	 */
	function getPrepSeriesByDrugAndSeries($data) {
		if (!isset($data['Drug_id']) || $data['Drug_id'] <= 0 || empty($data['PrepSeries_Ser'])) {
			return array();
		}
		$query = "
			select top 1
				ps.PrepSeries_id,
				ps.PrepSeries_Ser,
				convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect
			from
				rls.v_PrepSeries ps with (nolock)
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
			where
				ps.Drug_id = :Drug_id and
				ps.PrepSeries_Ser = :PrepSeries_Ser;
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		return $result ? array($result) : array();
	}

	/**
	 *  Функция сохранения серии при сохранении документа учета. Если серии еще нет в справочнике, она добавляется туда. Иначе возвращается её идентификатор.
	 */
	function savePrepSeries($data) {
		$PrepSeries_id = isset($data['PrepSeries_id']) ? $data['PrepSeries_id'] : null;

		if (isset($data['PrepSeries_Ser']) && !empty($data['PrepSeries_Ser']) && $data['Drug_id'] > 0) {
			$query = "
				select top 1
					ps.PrepSeries_id,
					convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate
				from
					rls.v_PrepSeries ps with (nolock)
				where
					ps.Drug_id = :Drug_id and
					ps.PrepSeries_Ser = :PrepSeries_Ser
		        order by
		            ps.PrepSeries_id;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'Drug_id' => $data['Drug_id'],
				'PrepSeries_Ser' => $data['PrepSeries_Ser']
			));

			if ($result && is_array($result) && count($result) > 0 && $result['PrepSeries_id'] > 0) {
				$PrepSeries_id = $result['PrepSeries_id'];

				if (!empty($data['PrepSeries_GodnDate']) && $data['PrepSeries_GodnDate'] != $result['PrepSeries_GodnDate']) {
					//обновляем срок годности в справочнике серий
					$response = $this->saveObject('rls.PrepSeries', array(
						'PrepSeries_id' => $PrepSeries_id,
						'PrepSeries_GodnDate' => $this->formatDate($data['PrepSeries_GodnDate']),
						'pmUser_id' => $data['pmUser_id']
					));
				}
			} else {
				$query = "
					declare
						@PrepSeries_id bigint,
						@Prep_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000);

					set @Prep_id = (select top 1 DrugPrep_id from rls.Drug with (nolock) where Drug_id = :Drug_id);

					execute rls.p_PrepSeries_ins
						@PrepSeries_id = @PrepSeries_id output,
						@Prep_id = @Prep_id,
						@PrepSeries_Ser = :PrepSeries_Ser,
						@PrepSeries_GodnDate = :PrepSeries_GodnDate,
						@PackNx_Code = null,
						@PrepSeries_IsDefect = null,
						@Drug_id = :Drug_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @PrepSeries_id as PrepSeries_id;
				";
				$result = $this->getFirstResultFromQuery($query, array(
					'Drug_id' => $data['Drug_id'],
					'PrepSeries_Ser' => $data['PrepSeries_Ser'],
					'PrepSeries_GodnDate' => isset($data['PrepSeries_GodnDate']) && !empty($data['PrepSeries_GodnDate']) ? $this->formatDate($data['PrepSeries_GodnDate']) : null,
					'pmUser_id' => $data['pmUser_id']
				));
				if ($result && $result > 0) {
					$PrepSeries_id = $result;
				}
			}
		}
		return $PrepSeries_id;
	}

	/**
	 * Сохранение примечания
	 */
	function saveNote($data) {
		$action = null;
		$response = array('Error_Message' => 'Не удалось сохранить примечание');

		if (!isset($data['DocumentUc_id']) || $data['DocumentUc_id'] <= 0) {
			return $response;
		}

		if (isset($data['Note_id']) && $data['Note_id'] > 0) {
			if (!empty($data['Note_Text'])) {
				$action = 'edit';
			} else {
				$action = 'delete';
			}
		} else {
			if (!empty($data['Note_Text'])) {
				$action = 'add';
			}
		}

		switch($action) {
			case 'add':
			case 'edit':
				$response = $this->saveObject('DocumentStrValues', array(
					'DocumentStrValues_id' => isset($data['Note_id']) && $data['Note_id'] > 0 ? $data['Note_id'] : null,
					'DocumentStrPropType_id' => $this->getObjectIdByCode('DocumentStrPropType', 1), //Примечание
					'DocumentStrType_id' => $this->getObjectIdByCode('DocumentStrType', 1), //Документ учета DocumentUc
					'Document_id' => $data['DocumentUc_id'],
					'DocumentStrValues_Values' => $data['Note_Text'],
					'pmUser_id' => $data['pmUser_id']
				));
				break;
			case 'delete':
				$response = $this->deleteObject('DocumentStrValues', array(
					'DocumentStrValues_id' => $data['Note_id']
				));
				break;
		}

		return $response;
	}

	/**
	 *  Получение строк спецификации для документа учета медикаментов на основе ГК.
	 */
	function getDocumentUcStrListByWhsDocumentSupply($data) {
		$error = array(); //для сбора ошибок
		$result = array();
		$drug_data = array();

        $suppliers_ostat_control = !empty($data['options']['drugcontrol']['suppliers_ostat_control']);

        $query = "
            select top 1
                o.Okei_id,
                isnull(o.Okei_NationSymbol, 'упак') as Okei_NationSymbol
            from
                v_Okei o with (nolock)
            where
                o.Okei_Name = 'Упаковка'
            order by
                o.Okei_id;
        ";
        $okei_data = $this->getFirstRowFromQuery($query);
        if (!is_array($okei_data) || count($okei_data) < 1) {
            $okei_data = array(
                'Okei_id' => null,
                'Okei_NationSymbol' => null
            );
        }

		//получение данных о медикаментах
        if ($suppliers_ostat_control) {
            $query = "
                select
                	wds.WhsDocumentSupply_id,
                    dor.Drug_id,
                    d.Drug_Name,
                    dnmn.DrugNomen_Code,
                    o.Okei_id,
                    isnull(o.Okei_NationSymbol, 'упак') as Okei_NationSymbol,
                    d.Drug_Fas,
                    dor.DrugOstatRegistry_Kolvo as Count,
                    ps.PrepSeries_id,
                    ps.PrepSeries_Ser,
                    convert(varchar(10), ps.PrepSeries_GodnDate, 120) as PrepSeries_godnDate,
                    isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
                    cast(dor.DrugOstatRegistry_Cost as decimal (12,2)) as Price,
                    dn.DrugNds_id,
                    dn.DrugNds_Code
                from
                    v_WhsDocumentSupply wds with (nolock)
                    left join v_DrugShipment ds with (nolock) on ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                    left join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = ds.DrugShipment_id
                    left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                    left join v_Okei o on o.Okei_id = dor.Okei_id
                    left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
                    left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
                    left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
                    outer apply (
                        select top 1
                            DrugNomen_Code
                        from
                            rls.v_DrugNomen with (nolock)
                        where
                            v_DrugNomen.Drug_id = d.Drug_id
                    ) dnmn
                    outer apply (
                        select top 1
                            i_dn.DrugNds_id,
                            i_dn.DrugNds_Code
                        from
                            v_WhsDocumentSupplySpec i_wdss with (nolock)
                            left join v_DrugNds i_dn with (nolock) on i_dn.DrugNds_Code = i_wdss.WhsDocumentSupplySpec_NDS
                        where
                            i_wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id and
                            i_wdss.Drug_id = dor.Drug_id
                    ) dn
                where
                    dor.DrugOstatRegistry_Kolvo > 0 and
                    sat.SubAccountType_Code = 1 and
                    dor.Org_id = wds.Org_sid and
                    wds.WhsDocumentSupply_id = :WhsDocumentSupply_id;
            ";
        } else {
            $query = "
                select
                    wds.WhsDocumentSupply_id,
                    wdss.Drug_id,
                    d.Drug_Name,
                    dnmn.DrugNomen_Code,
                    d.Drug_Fas,
                    wdss.WhsDocumentSupplySpec_KolvoUnit as Count,
                    cast(wdss.WhsDocumentSupplySpec_PriceNDS as decimal (16,2)) as Price,
                    dn.DrugNds_id,
                    dn.DrugNds_Code
                from
                    v_WhsDocumentSupply wds with (nolock)
                    left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                    left join rls.v_Drug d with (nolock) on d.Drug_id = wdss.Drug_id
                    left join v_DrugNds dn with (nolock) on dn.DrugNds_Code = wdss.WhsDocumentSupplySpec_NDS
                    outer apply (
                        select top 1
                            DrugNomen_Code
                        from
                            rls.v_DrugNomen with (nolock)
                        where
                            v_DrugNomen.Drug_id = d.Drug_id
                    ) dnmn
                where
                    wds.WhsDocumentSupply_id = :WhsDocumentSupply_id;
            ";
        }

		$res = $this->db->query($query, array(
			'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']
		));

		if (is_object($res)) {
			$drug_data = $res->result('array');
		}

		//сбор строк спецификации
		$str_data = array();
		foreach($drug_data as $drug) {
			if (count($error) < 1) {
				$nds_koef = (100+$drug['DrugNds_Code'])/100;
                $nds_price = $drug['Price'];
                $price = round($drug['Price']/$nds_koef, 2);;

				$str_data[] = array(
					'Drug_id' => $drug['Drug_id'],
					'Drug_Name' => $drug['Drug_Name'],
					'DrugNomen_Code' => $drug['DrugNomen_Code'],
					'Okei_id' => !empty($drug['Okei_id']) ? $drug['Okei_id'] : $okei_data['Okei_id'],
					'Okei_NationSymbol' => !empty($drug['Okei_id']) ? $drug['Okei_NationSymbol'] : $okei_data['Okei_NationSymbol'],
					'DrugNds_id' => $drug['DrugNds_id'],
					'DrugNds_Code' => $drug['DrugNds_Code'],
					'DocumentUcStr_Price' => $nds_price,
					'DocumentUcStr_Count' => $drug['Count'],
					'DocumentUcStr_EdCount' => $drug['Drug_Fas'] > 0 ? $drug['Count']*$drug['Drug_Fas'] : null,
					'DocumentUcStr_Sum' => $nds_price*$drug['Count'],
					'DocumentUcStr_SumNds' => ($nds_price-$price)*$drug['Count'],
					'DocumentUcStr_NdsSum' => $nds_price*$drug['Count'],
                    'DocumentUcStr_Ser' => !empty($drug['PrepSeries_Ser']) ? $drug['PrepSeries_Ser'] : null,
                    'PrepSeries_godnDate' => !empty($drug['PrepSeries_godnDate']) ? $drug['PrepSeries_godnDate'] : null,
                    'PrepSeries_isDefect' => !empty($drug['PrepSeries_isDefect']) ? $drug['PrepSeries_isDefect'] : null,
                    'DocumentUcStr_IsNDS' => 1,
					'pmUser_id' => $data['session']['pmuser_id'],
				);
			}
		}

		$result['data'] = $str_data;
		$result['success'] = true;

		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
		}

		return $result;
	}

	/**
	 *  Получение строк спецификации для документа учета медикаментов на основе документа.
	 */
	function getDocumentUcStrListByWhsDocumentUcOrder($data) {
		$error = array(); //для сбора ошибок
		$result = array();
		$drug_data = array();

        //получение данных ед. изм.
        $query = "
            select
                o.Okei_id,
                isnull(o.Okei_NationSymbol, 'упак') as Okei_NationSymbol
            from
                v_Okei o with (nolock)
            where
                Okei_Name = 'Упаковка';
        ";
        $okei_data = $this->getFirstRowFromQuery($query);

		//получение данных о медикаментах
		$query = "
			select
                ds.WhsDocumentSupply_id,
                wduom.Drug_id,
                d.Drug_Name,
                dnmn.DrugNomen_Code,
                d.Drug_Fas,
                wduom.WhsDocumentUcOrderMaterial_Count as Count,
                ps.PrepSeries_id,
                ps.PrepSeries_Ser,
                convert(varchar(10), ps.PrepSeries_GodnDate, 120) as PrepSeries_godnDate,
                isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
                cast(wduom.WhsDocumentUcOrderMaterial_Price as decimal (12,2)) as Price,
                dsl.DocumentUcStr_id as DocumentUcStr_oid,
                dn.DrugNds_id,
                dn.DrugNds_Code
            from
                v_WhsDocumentUcOrder wduo with (nolock)
                left join v_WhsDocumentUcOrderMaterial wduom with (nolock) on wduom.WhsDocumentUcOrder_id = wduo.WhsDocumentUcOrder_id
                left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = wduom.DrugShipment_id
                left join v_DrugShipmentLink dsl with (nolock) on dsl.DrugShipment_id = wduom.DrugShipment_id
                left join rls.v_Drug d with (nolock) on d.Drug_id = wduom.Drug_id
                left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = wduom.PrepSeries_id
                left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
                left join v_DrugNds dn with (nolock) on dn.DrugNds_id = wduo.DrugNds_id
                outer apply (
                    select top 1
                        DrugNomen_Code
                    from
                        rls.v_DrugNomen with (nolock)
                    where
                        v_DrugNomen.Drug_id = d.Drug_id
                ) dnmn
			where
				wduo.WhsDocumentUc_id = :WhsDocumentUc_id;
		";
		$res = $this->db->query($query, array(
			'WhsDocumentUc_id' => $data['WhsDocumentUc_id']
		));

		if (is_object($res)) {
			$drug_data = $res->result('array');
		}

		//сбор строк спецификации
		$str_data = array();
		foreach($drug_data as $drug) {
			if (count($error) < 1) {
				$nds_koef = (100+$drug['DrugNds_Code'])/100;
				$nds_price = $drug['Price'];
				$price = round($drug['Price']/$nds_koef, 2);

				$str_data[] = array(
					'Drug_id' => $drug['Drug_id'],
					'Drug_Name' => $drug['Drug_Name'],
					'DrugNomen_Code' => $drug['DrugNomen_Code'],
					'Okei_id' => !empty($okei_data['Okei_id']) ? $okei_data['Okei_id'] : null,
					'Okei_NationSymbol' => !empty($okei_data['Okei_NationSymbol']) ? $okei_data['Okei_NationSymbol'] : null,
					'DrugNds_id' => $drug['DrugNds_id'],
					'DrugNds_Code' => $drug['DrugNds_Code'],
					'DocumentUcStr_Price' => $nds_price,
					'DocumentUcStr_Count' => $drug['Count'],
					//'DocumentUcStr_RashCount' => $drug['Count'],
					'DocumentUcStr_EdCount' => $drug['Drug_Fas'] > 0 ? $drug['Count']*$drug['Drug_Fas'] : null,
					//'DocumentUcStr_RashEdCount' => $drug['Drug_Fas'] > 0 ? $drug['Count']*$drug['Drug_Fas'] : null,
					'DocumentUcStr_Sum' => $nds_price*$drug['Count'],
					'DocumentUcStr_SumNds' => ($nds_price-$price)*$drug['Count'],
					'DocumentUcStr_NdsSum' => $nds_price*$drug['Count'],
					'DocumentUcStr_Ser' => $drug['PrepSeries_Ser'],
					'PrepSeries_godnDate' => $drug['PrepSeries_godnDate'],
					'PrepSeries_isDefect' => $drug['PrepSeries_isDefect'],
					'DocumentUcStr_IsNDS' => 1,
                    'DocumentUcStr_oid' => $drug['DocumentUcStr_oid'],
					'pmUser_id' => $data['session']['pmuser_id'],
				);
			}
		}

		$result['data'] = $str_data;
		$result['success'] = true;

		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
		}

		return $result;
	}

    /**
     *  Получение данных строки приходного документа учета по штрих-коду
     */
    function getDocumentUcStrDataByBarCode($data) {
        $query = "";

        switch($data['mode']) {
            case 'DocumentUcStrList': //данные для списка медикаментов на форме редактирования документов учета
                $query = "
                    select top 1
                        dus.DocumentUcStr_id,
                        dus.DocumentUcStr_id as DocumentUcStr_oid,
                        (
                            ds.DrugShipment_Name+
                            isnull(' '+ds.AccountType_Name, '')
                        ) as DocumentUcStr_oName,
                        dus.Drug_id,
                        d.Drug_Name,
                        dnmn.DrugNomen_Code,
                        dus_cnt.DocumentUcStr_Count,
                        dus_cnt.DocumentUcStr_EdCount,
                        dus.DrugNds_id,
                        isnull(dn.DrugNds_Code, 0) as DrugNds_Code,
                        dus.DocumentUcStr_Price,
                        dus_cnt.DocumentUcStr_Sum,
                        dus_cnt.DocumentUcStr_SumNds,
                        (
                            case
                                when
                                    isnull(isnds.YesNo_Code, 0) = 1
                                then
                                    isnull(dus_cnt.DocumentUcStr_Sum, 0)
                                else
                                    isnull(dus_cnt.DocumentUcStr_Sum, 0) + isnull(dus_cnt.DocumentUcStr_SumNds, 0)
                            end
                        ) as DocumentUcStr_NdsSum,
                        isnull(ps.PrepSeries_Ser, dus.DocumentUcStr_Ser) DocumentUcStr_Ser,
                        convert(varchar(10), dus.DocumentUcStr_godnDate, 104) as DocumentUcStr_godnDate,
                        convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
                        ps.PrepSeries_id,
                        isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
                        dus.DocumentUcStr_CertNum,
                        convert(varchar(10), dus.DocumentUcStr_CertDate, 104) as DocumentUcStr_CertDate,
                        convert(varchar(10), dus.DocumentUcStr_CertGodnDate, 104) as DocumentUcStr_CertGodnDate,
                        dus.DocumentUcStr_CertOrg,
                        dus.DrugLabResult_Name,
                        isnull(isnds.YesNo_Code, 0) as DocumentUcStr_IsNDS,
                        isnull(okei.Okei_NationSymbol, 'упак') as Okei_NationSymbol,
                        dus.DrugDocumentStatus_id,
                        --dds.DrugDocumentStatus_Code,
                        --dds.DrugDocumentStatus_Name,
                        dus.StorageZone_id,
                        rtrim(isnull(sz.StorageZone_Address,'') + ' ' + isnull(sut.StorageUnitType_Name,'')) as StorageZone_Name,
                        gu.GoodsUnit_id,
                        gu.GoodsUnit_Name,
                        (case
                            when gu.GoodsUnit_Name = 'упаковка' then 1
                            when gpc.GoodsPackCount_Count > 0 then gpc.GoodsPackCount_Count
                            when gpc.GoodsPackCount_Count is null and d.Drug_Fas is not null then d.Drug_Fas
                            else null
                        end) as GoodsPackCount_Count,
                        (case
                            when gu.GoodsUnit_Name = 'упаковка' then 'fixed_value'
                            when gpc.GoodsPackCount_Count > 0 then 'table'
                            when gpc.GoodsPackCount_Count is null and d.Drug_Fas is not null then 'drug_fas'
                            else null
                        end) as GoodsPackCount_Source,
                        dpbc.DrugPackageBarCode_id,
                        dpbc.DrugPackageBarCode_BarCode,
                        dpbc.DrugPackageBarCodeType_id,
                        dpbc.DrugPackageBarCode_GTIN,
                        dpbc.DrugPackageBarCode_SeriesNum,
                        convert(varchar(10), dpbc.DrugPackageBarCode_expDT, 104) as DrugPackageBarCode_expDT,
                        dpbc.DrugPackageBarCode_TNVED,
                        dpbc.DrugPackageBarCode_FactNum
                    from
                        v_DrugPackageBarCode dpbc with (nolock)
                        left join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dpbc.DocumentUcStr_id
                        left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
                        left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
                        left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
                        left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
                        left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
                        left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
                        left join v_Okei okei with (nolock) on okei.Okei_id = dus.Okei_id
                        left join v_StorageZone sz with (nolock) on sz.StorageZone_id = dus.StorageZone_id
                        left join v_StorageUnitType sut with (nolock) on sut.StorageUnitType_id = sz.StorageUnitType_id
                        left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = dus.DrugDocumentStatus_id
                        left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = dus.GoodsUnit_id
                        outer apply (
                            select top 1
                                DrugNomen_Code
                            from
                                rls.v_DrugNomen with (nolock)
                            where
                                v_DrugNomen.Drug_id = d.Drug_id
                        ) dnmn
                        outer apply (
                            select top 1
                                i_dsh.DrugShipment_Name,
                                i_at.AccountType_Name
                            from
                                v_DrugShipmentLink i_dsl with (nolock)
                                left join v_DrugShipment i_dsh with (nolock) on i_dsh.DrugShipment_id = i_dsl.DrugShipment_id
                                left join v_AccountType i_at with (nolock) on i_at.AccountType_id = i_dsh.AccountType_id
                            where
                                i_dsl.DocumentUcStr_id = dus.DocumentUcStr_id
                            order by
                                i_dsl.DrugShipmentLink_id
                        ) ds
                        outer apply (
                            select top 1
                                i_gpc.GoodsPackCount_Count
                            from
                                v_GoodsPackCount i_gpc with (nolock)
                            where
                                i_gpc.GoodsUnit_id = gu.GoodsUnit_id and
                                i_gpc.DrugComplexMnn_id = d.DrugComplexMnn_id and
                                (
                                    d.DrugTorg_id is null or
                                    i_gpc.TRADENAMES_ID is null or
                                    i_gpc.TRADENAMES_ID = d.DrugTorg_id
                                )
                            order by
                                i_gpc.TRADENAMES_ID desc
                        ) gpc
                        outer apply (
                            select
                                1 as DocumentUcStr_Count,
                                dus.DocumentUcStr_EdCount/isnull(dus.DocumentUcStr_Count, 1) as DocumentUcStr_EdCount,
                                dus.DocumentUcStr_Sum/isnull(dus.DocumentUcStr_Count, 1) as DocumentUcStr_Sum,
                                dus.DocumentUcStr_SumNds/isnull(dus.DocumentUcStr_Count, 1) as DocumentUcStr_SumNds
                        ) dus_cnt
                    where
                        dpbc.DrugPackageBarCode_BarCode = :DrugPackageBarCode_BarCode and
                        du.Storage_tid = :Storage_id
                    order by
                        dpbc.DrugPackageBarCode_id;
                ";
                break;
            case 'EvnReceptRlsProvide': //данные для формы отпуска рецептов
                break;
        }

        $result = null;
        if (!empty($query)) {
            $result = $this->queryResult($query, array(
                'DrugPackageBarCode_BarCode' => $data['DrugPackageBarCode_BarCode'],
                'Storage_id' => $data['Storage_id']
            ));
        }

        return is_array($result) ? $result : false;
    }

	/**
	 * Удаление строки документа.
	 */
	function delete($data) {
		$error = array(); //для сбора ошибок
		$str_arr = array();

		//получение статуса и типа документа
		$query = "
			select
				dds.DrugDocumentStatus_Code as Status_Code,
				ddt.DrugDocumentType_Code as Type_Code
			from
				v_DocumentUc du with (nolock)
				left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
				left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['id']
		));
		if (!empty($doc_data['Status_Code'])) {
			if ($doc_data['Status_Code'] == 4 || $doc_data['Status_Code'] == 14 || $doc_data['Status_Code'] == 15) {
				$error[] = "Удаление невозможно, так как документ исполнен.";
			}
		} else {
			$error[] = "Не удалось получить данные документа.";
		}

        //для документа разукомплектации удаление дочернего документа
        if (!empty($doc_data['Type_Code']) && $doc_data['Type_Code'] == 34) { //34 - Разукомплектация: списание
            $query = "
                select
                    du.DocumentUc_id
                from
                    v_DocumentUc du with (nolock)
                    left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                where
                    du.DocumentUc_pid = :DocumentUc_pid and
                    ddt.DrugDocumentType_Code = 35 -- 35 - Разукомплектация: постановка на учет
            ";
            $child_array = $this->queryList($query, array(
                'DocumentUc_pid' => $data['id']
            ));

            foreach($child_array as $child_id) {
                $del_res = $this->delete(array(
                    'id' => $child_id,
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (isset($del_res[0]) && !empty($del_res[0]['Error_Msg'])) {
                    $error[] = $del_res[0]['Error_Msg'];
                }
            }
        }

		//получение списка строк документа учета
		$query = "
			select
				dus.DocumentUcStr_id
			from
				v_DocumentUcStr dus with (nolock)
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['id']
		));
		if (is_object($result)) {
			$str_arr = $result->result('array');
		}

		$this->beginTransaction();

        if (count($error) == 0) {
            foreach($str_arr as $str) {
                $response = $this->deleteDocumentUcStr(array(
                    'DocumentUcStr_id' => $str['DocumentUcStr_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Message'])) {
                    $error[] = $response['Error_Message'];
                    break;
                }
            }
        }

        //удаление информации о связи документа с ЛПУ и подразделением
        if (count($error) == 0) {
            $response = $this->saveDocumentUcLink(array(
                'DocumentUc_id' => $data['id']
            ));
            if (!empty($response['Error_Message'])) {
                $error[] = $response['Error_Message'];
            }
        }

		if (count($error) == 0) {
			$response = $this->deleteObject('DocumentUc', array(
				'DocumentUc_id' => $data['id']
			));
			if (!empty($response['Error_Message'])) {
				$error[] = $response['Error_Message'];
			}
		}

		if (count($error) > 0) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => $error[0]));
		} else {
			$this->commitTransaction();
			return array(array('Error_Code' => null, 'Error_Msg' => null));
		}
	}

	/**
	 * Отмена обеспечения рецепта
	 */
	function canceling($data) {
		$params = array();

		$query = "
			Declare
				@DocumentUc_id bigint = :DocumentUc_id,
				@pmUser_id bigint = :pmUser_id,
				@Error_Code int,
				@Error_Message varchar(4000);

			exec p_DocumentUc_Canceling
				@DocumentUc_id = @DocumentUc_id,
				@pmUser_id = @pmUser_id,
				@Error_Code = @Error_Code,
				@Error_Message = @Error_Message;

			  select @Error_Code as Error_Code, @Error_Message as Error_Msg;

		";

		//var_dump ($data);

		//var_dump ($_SESSION);

		$params['DocumentUc_id'] = $data['DocumentUc_id'];
		$params['pmUser_id'] = $data['pmUser_id'];

		//print getDebugSQL($query, $params);exit;

		$result = $this->getFirstRowFromQuery($query, $params);
		//var_dump ($result);

		if ($result !== false) {
			if (!empty($result['Error_Msg'])) {
				return array($result);
			}
		} else {
            return array(array('Error_Msg' => "При обработке данных произошла ошибка. "));
        }
	}

	/**
	 * Удаление строки документа.
	 */
	function deleteDocumentUcStr($data) {
		$this->load->model("EmergencyTeam_model4E", "et_model");

		//Удаляем запись из укладки бригады СМП, если существует
		$etdpm_result = $this->et_model->deleteEmergencyTeamPackMoveByDocumentUcStr(array(
			'DocumentUcStr_id' => $data['DocumentUcStr_id']
		));
		if (isset($etdpm_result[0]) && !empty($etdpm_result[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return array('Error_Message' => $etdpm_result[0]['Error_Msg']);
		}

        //очистка резерва по строке документа учета
        $result = $this->removeReserve(array(
            'DocumentUcStr_id' => $data['DocumentUcStr_id'],
            'pmUser_id' => $data['pmUser_id']
        ));
        if (!empty($result['Error_Msg'])) {
            return array('Error_Message' => $result['Error_Msg']);
        }

        //удалении информации о нарядах
        $result = $this->removeStorageWork(array(
            'DocumentUcStr_id' => $data['DocumentUcStr_id']
        ));
        if (!empty($result['Error_Msg'])) {
            return array('Error_Message' => $result['Error_Msg']);
        }

        //проверка на упоминание строки в строках документа разукомплектации
        $query = "
            select
                dus.DocumentUcStr_id,
                ddt.DrugDocumentType_SysNick,
                isnull(dds_s.DrugDocumentStatus_Code, 1) as StrStatus_Code,
                isnull(dds_d.DrugDocumentStatus_Code, 1) as DocStatus_Code
            from
                v_DocumentUcStr dus with (nolock)
                left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
                left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                left join v_DrugDocumentStatus dds_s with (nolock) on dds_s.DrugDocumentStatus_id = dus.DrugDocumentStatus_id
                left join v_DrugDocumentStatus dds_d with (nolock) on dds_d.DrugDocumentStatus_id = du.DrugDocumentStatus_id
            where
                dus.DocumentUcStr_sid = :DocumentUcStr_id;
        ";
        $raz_post_array = $this->queryResult($query, array(
            'DocumentUcStr_id' => $data['DocumentUcStr_id']
        ));
        if (is_array($raz_post_array)) {
            foreach ($raz_post_array as $raz_post_data) {
                if (
                    $raz_post_data['DrugDocumentType_SysNick'] == 'DocRazPost' &&
                    (
                        $raz_post_data['DocStatus_Code'] == 1 ||
                        ($raz_post_data['DocStatus_Code'] == 3 && $raz_post_data['StrStatus_Code'] == 1)
                    )
                ) { //DocRazPost - Разукомплектация: постановка на учет; 1 - Новый; 3 - Исполнен частично.
                    $del_res = $this->deleteDocumentUcStr(array(
                        'DocumentUcStr_id' => $raz_post_data['DocumentUcStr_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!empty($del_res['Error_Message'])) {
                        return array('Error_Message' => $del_res['Error_Message']);
                    }
                } else {
                    return array('Error_Message' => 'Удаление строки документа невозможно, так как она используется в другом документе');
                }
            }
        }

		//проверка на использование данной строки в качестве партии в другом документе учета
		$query = "
			select
				count(dus.DocumentUcStr_id) as cnt
			from
				v_DocumentUcStr dus with (nolock)
			where
				dus.DocumentUcStr_oid = :DocumentUcStr_id;
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && $result['cnt'] == 0) {
			//удаление партии
			$sh_arr = array();
			$query = "
				select
					dsl.DrugShipmentLink_id,
					dsl.DrugShipment_id,
					dor.cnt as DrugOstatRegistry_Cnt
				from
					v_DrugShipmentLink dsl with (nolock)
					outer apply (
						select
							count(dor.DrugOstatRegistry_id) as cnt
						from
							v_DrugOstatRegistry dor with (nolock)
						where
							dor.DrugShipment_id = dsl.DrugShipment_id
					) dor
				where
					dsl.DocumentUcStr_id = :DocumentUcStr_id
					and not exists(
						select * from v_WhsDocumentUcInventDrug with(nolock)
						where DrugShipment_id = dsl.DrugShipment_id
					)
				order by
					dor.cnt desc;
			";

			$result = $this->queryResult($query , $data);
			if ($this->isSuccessful($result)) {
				$sh_arr = $result;
			}

			foreach($sh_arr as $sh) {
				if ($sh['DrugOstatRegistry_Cnt'] == 0) {
					$response = $this->deleteObject('DrugShipmentLink', $sh);
					if (!empty($response['Error_Message'])) {
						$this->db->trans_rollback();
						return $response;
					}
                                        //echo ('удаление партии 3');
					$response = $this->deleteObject('DrugShipment', $sh);
					if (!empty($response['Error_Message'])) {
						$this->db->trans_rollback();
						return $response;
					}
				} else {
					$this->db->trans_rollback();
					return array('Error_Message' => 'Удаление строки документа невозможно, так как связанная партия уже используется');
				}
			}

			//Удалим связь с DrugShipmentLink
			$queryShipmentLink = "
				select DrugShipmentLink_id
				from v_DrugShipmentLink with(nolock)
				where DocumentUcStr_id = :DocumentUcStr_id
			";

			$resultShipmentLink = $this->db->query($queryShipmentLink,$data);
			if(is_object($resultShipmentLink)) {
				$response = $resultShipmentLink->result('array');
				if(is_array($response) && count($response)>0) {
					$DrugShipmentLink_id = $response[0]['DrugShipmentLink_id'];
					$response = $this->deleteDrugShipLink($DrugShipmentLink_id);
				}
			}

            //удаление информации о штрих-кодах
            $query = "
                select
                    DrugPackageBarCode_id
                from
                    v_DrugPackageBarCode with (nolock)
                where
                    DocumentUcStr_id = :DocumentUcStr_id;
            ";
            $bar_code_list = $this->queryResult($query, $data);
            if (is_array($bar_code_list)) {
                foreach($bar_code_list as $bar_code) {
                    if (!empty($bar_code['DrugPackageBarCode_id'])) {
                        $response = $this->deleteObject('DrugPackageBarCode', $bar_code);
                    }
                }
            }

			$response = $this->deleteObject('DocumentUcStr', $data);
			return $response;
		} else {
			$this->db->trans_rollback();
			return array('Error_Message' => 'Удаление строки документа невозможно, так как она используется в другом документе');
		}
	}

	/**
	 * Загрузка списка медикаментов для комбо (используется при редактировании спецификации документа учета)
	 */
	public function loadDrugComboForDocumentUcStr($filter) {
		$where = array();
		$with = array();
		$join = array();

		if ($filter['Drug_id'] > 0) {
			$where[] = 'd.Drug_id = :Drug_id';
		} else {
			if (!empty($filter['DrugNomen_Code'])) {
				$where[] = 'dn.DrugNomen_Code = :DrugNomen_Code';
			}
			if ($filter['WhsDocumentUc_id'] > 0) {
				$query = "
					select
						count(Drug_id) as cnt
					from
					    v_WhsDocumentSupply i_wds with (nolock)
						left join v_WhsDocumentSupplySpec i_wdss with (nolock) on i_wdss.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id
					where
						i_wds.WhsDocumentUc_id = :WhsDocumentUc_id;
				";
				$result = $this->getFirstResultFromQuery($query, array(
					'WhsDocumentUc_id' => $filter['WhsDocumentUc_id']
				));
				if ($result && $result > 0) {
					$where[] = 'd.Drug_id in (
						select
							Drug_id
						from
                            v_WhsDocumentSupply i_wds with (nolock)
                            left join v_WhsDocumentSupplySpec i_wdss with (nolock) on i_wdss.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id
                        where
                            i_wds.WhsDocumentUc_id = :WhsDocumentUc_id
						union
						select
							i_wdssd.Drug_sid
						from
                            v_WhsDocumentSupply i_wds with (nolock)
                            left join v_WhsDocumentSupplySpec i_wdss with (nolock) on i_wdss.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id
                            left join v_WhsDocumentSupplySpecDrug i_wdssd with (nolock) on i_wdssd.WhsDocumentSupplySpec_id = i_wdss.WhsDocumentSupplySpec_id
                        where
                            i_wds.WhsDocumentUc_id = :WhsDocumentUc_id
					)';
				}
			}
			if ($filter['DocumentUc_id'] > 0) {
				$where[] = 'd.Drug_id in (
					select
						Drug_id
					from
						v_DocumentUcStr with (nolock)
					where
						DocumentUc_id = :DocumentUc_id
				)';
			}
			if (strlen($filter['query']) > 0) {
				$filter['query'] = /*'%'.*/preg_replace('/ /', '%', $filter['query']).'%';
				//$where[] = 'd.Drug_Name LIKE :query';
                $filter['query'] = preg_replace('/\'/', '', $filter['query']);
				$where[] = 'd.Drug_Name LIKE \''.$filter['query'].'\''; //по неизвестной причине, если отдавать подстроку параметром, запрос перестает работать
			}
			if ((!empty($filter['Storage_id']) && $filter['Storage_id'] > 0) || (!empty($filter['Contragent_id']) && $filter['Contragent_id'] > 0)) {
                $ost_sub_join = "";
                $ost_sub_where = "";


                if (!empty($filter['DrugShipment_setDT_max'])) {
                    $ost_sub_join .= "left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id ";
                    $ost_sub_where .= "cast(ds.DrugShipment_setDT as date) <= :DrugShipment_setDT_max and ";
                }

                if (!empty($filter['StorageZone_id']) && $filter['StorageZone_id'] > 0) {
                    $ost_sub_join .= "
                        outer apply (
							select
								sum(dsz2.DrugStorageZone_Count) as Drug_Count
							from
								v_DrugStorageZone dsz2 with (nolock)
							where
								dsz2.StorageZone_id = :StorageZone_id and
								dsz2.Drug_id = dor.Drug_id and
								isnull(dor.PrepSeries_id,0) = isnull(dsz2.PrepSeries_id,0) and
								isnull(dor.DrugShipment_id,0) = isnull(dsz2.DrugShipment_id,0)
						) drug_sz
                    ";
                    $ost_sub_where .= "drug_sz.Drug_Count > 0 and ";
                }

				$with[] = " ostat_cnt as (
					select
						dor.Drug_id,
						isnull(sum(DrugOstatRegistry_Kolvo), 0) as cnt
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						{$ost_sub_join}
					where
						dor.DrugOstatRegistry_Kolvo > 0 and
						{$ost_sub_where}
						sat.SubAccountType_Code = 1 and
						(:Storage_id is null or dor.Storage_id = :Storage_id) and
						(:Contragent_id is null or dor.Contragent_id = :Contragent_id) and
						(ISNULL(dor.DrugFinance_id,0)=0 or :DrugFinance_id is null or dor.DrugFinance_id = :DrugFinance_id) and
						(ISNULL(dor.WhsDocumentCostItemType_id,0)=0 or :WhsDocumentCostItemType_id is null or dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
					group by
						dor.Drug_id
				)";
				$join[] = "left join ostat_cnt on ostat_cnt.Drug_id = d.Drug_id";
				$where[] = 'ostat_cnt.cnt > 0';
			}
		}

		if (count($where) > 0 || count($with) > 0) {
			$q = "
				select top 100
					-- select
					d.Drug_id,
					d.Drug_Name,
					dn.DrugNomen_Code,
					coalesce(dn.PrepClass_Name, ng.NAME, sg.NAME) as PrepClass_Name,
					(
					    case
					        when isnull(am.NARCOGROUPID, 0) > 0 or isnull(am.STRONGGROUPID, 0) > 0
					        then 1
					        else null
					    end
					) as isPKU,
					d.Drug_Fas,
					rtrim(isnull(d.DrugForm_Name, '')) as DrugForm_Name,
					rtrim(isnull(d.Drug_PackName, '')) as DrugUnit_Name,

					RTRIM(ISNULL(dp.DrugPrep_Name, '')) as hintTradeName, --Торговое наименование, лекарственная форма, дозировка, фасовка
					d.Drug_Nomen as hintPackagingData,  --Данные об упаковке – это часть поля Упаковка (drug_nomen) от начала строки до данных о производителе.
					rtrim(isnull(d.Drug_RegNum + '.   ', '') + isnull(convert(varchar(10), d.Drug_begDate, 104) + ', ', '') + isnull(convert(varchar(10), d.Drug_endDate, 104) +', ', '--, ') + isnull(convert(varchar(10), REG.REGCERT_insDT, 104)+', ', '') + isnull(REGISTR.regNameCauntries, '')) as hintRegistrationData, --Данные о регистрации
					case
						when NOMENF.FIRMS_ID = MANUFACTURF.FIRMS_ID
						then isnull(MANUFACTURF.FULLNAME, '')
						else
							case
								when (NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)='') AND (MANUFACTURF.FULLNAME IS NULL OR rtrim(MANUFACTURF.FULLNAME)='')
									then isnull(REGISTR.regNameCauntries, '')
								when NOMENF.FULLNAME IS NULL OR rtrim(NOMENF.FULLNAME)=''
									then isnull(MANUFACTURF.FULLNAME, '')
								else isnull(MANUFACTURF.FULLNAME, '') + ' / ' + NOMENF.FULLNAME
							end
					end as hintPRUP,	--ПР./УП.
					rtrim(FNM.NAME) as FirmNames
					-- end select
				from
					-- from
					rls.v_Drug d with (nolock)
					left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
					left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
					left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.Actmatters_id
					left join rls.NARCOGROUPS ng with (nolock) on ng.NARCOGROUPS_ID = am.NARCOGROUPID
                    left join rls.STRONGGROUPS sg with (nolock) on sg.STRONGGROUPS_ID = am.STRONGGROUPID

					left join rls.drugprep dp with(nolock) on d.DrugPrepFas_id = dp.DrugPrepFas_id
					left join rls.PREP P with(nolock) on P.Prep_id = d.DrugPrep_id
					left join rls.REGCERT REG with(nolock) on REG.REGCERT_ID =P.REGCERTID
					left join rls.NOMEN NM with(nolock) on NM.Nomen_id = d.Drug_id

					left join rls.FIRMS NOMENF with(nolock) on NM.FIRMID = NOMENF.FIRMS_ID
					left join rls.FIRMS MANUFACTURF with(nolock) on P.FIRMID = MANUFACTURF.FIRMS_ID
					left join rls.FIRMNAMES FNM with(nolock) on FNM.FIRMNAMES_ID = MANUFACTURF.NAMEID

					outer apply(
						SELECT TOP 1
							FN.NAME + ' (' + C.NAME + ')' as regNameCauntries
						FROM
							rls.REGCERT_EXTRAFIRMS RE
							left join rls.FIRMS F with(nolock) on RE.FIRMID = F.FIRMS_ID
							left join rls.FIRMNAMES FN with(nolock) on FN.FIRMNAMES_ID = F.NAMEID
							left join rls.COUNTRIES C with(nolock) on C.COUNTRIES_ID = F.COUNTID
						WHERE RE.CERTID = P.REGCERTID
					) REGISTR
					outer apply (
						select top 1
							v_DrugNomen.DrugNomen_Code,
							v_PrepClass.PrepClass_Name
						from
							rls.v_DrugNomen with (nolock)
							left join rls.v_PrepClass with (nolock) on v_PrepClass.PrepClass_id = v_DrugNomen.PrepClass_id
						where
							v_DrugNomen.Drug_id = d.Drug_id
						order by
							DrugNomen_id
					) dn
					".join($join, ' ')."
					-- end from
				where
					-- where
					".join($where, ' and ')."
					-- end where
				order by
					-- order by
					d.Drug_Name
					-- end order by
			";

			if (count($with) > 0) {
				$with_str = join($with, ', ');
				$q = "-- addit with
					with {$with_str}
					 -- end addit with
					{$q}
				";
			}

			if (!empty($filter['limit'])) {
                $result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $filter);
                $count = $this->getFirstResultFromQuery(getCountSQLPH($q), $filter);
                if (is_object($result) && $count !== false) {
                    return array(
                        'data' => $result->result('array'),
                        'totalCount' => $count
                    );
                } else {
                    return false;
                }
            } else {
                $result = $this->db->query($q, $filter);
                if ( is_object($result) ) {
                    return $result->result('array');
                } else {
                    return false;
                }
            }
		}

		return false;
	}

	/**
	 * Вспомогательная функция преобразования формата даты
	 * Получает строку c датой в формате d.m.Y, возвращает строку с датой в формате Y-m-d
	 */
	function formatDate($date) {
		$d_str = null;
		if (!empty($date)) {
			$date = preg_replace('/\//', '.', $date);
			$d_arr = explode('.', $date);
			if (is_array($d_arr)) {
				$d_arr = array_reverse($d_arr);
			}
			if (count($d_arr) == 3) {
				$d_str = join('-', $d_arr);
			}
		}
		return $d_str;
	}

    /**
     * Вспомогательная функция преобразования строки в число
     */
    function formatNumeric($num) {
        $num_res = null;
        if (!empty($num)) {
            $num = preg_replace('/,/', '.', $num);
            if (is_numeric($num)) {
                $num_res = $num;
            }
        }
        return $num_res;
    }

	/**
	 * Получние данных МОЛ по контрагенту или складу.
	 */
	function getMolByContragentOrStorage($data) {
		if ((!isset($data['Contragent_id']) || $data['Contragent_id'] <= 0) && (!isset($data['Storage_id']) || $data['Storage_id'] <= 0)) {
			return array();
		}

        $where = "";

        if (!empty($data['Date'])) {
            $where .= "
                and (m.Mol_begDT is null or m.Mol_begDT <= :Date)
                and (m.Mol_endDT is null or m.Mol_endDT >= :Date)
            ";
        }

		$query = "
			select
				m.Mol_id,
				rtrim(ltrim(isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,''))) as Person_Fio
			from
				Mol m with (nolock)
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = m.MedPersonal_id
				left join v_PersonState ps with (nolock) on ps.Person_id = mp.Person_id or ps.Person_id = m.Person_id
			where
				(
				    m.Contragent_id = :Contragent_id or
				    m.Storage_id = :Storage_id
				)
				{$where};
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array();
		}
	}

	/**
	 *  Получение списка контрагентов
	 */
	function loadContragentList($data) {
		$params = array();
		$filter = "(1=1)";

		if (!empty($data['Contragent_id']) && $data['Contragent_id'] > 0) {
			$filter .= " and c.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		} else {
			$filter .= " and (ofr.Region_id is null or ofr.Region_id = dbo.GetRegion())";

			if (!empty($data['Lpu_id']) && $data['Lpu_id'] > 0) {
				$filter .= " and c.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			} else {
				$filter .= " and (c.Lpu_id is null or c.Lpu_id = 0)";
			}

			if (!empty($data['ContragentType_CodeList'])) {
				$filter .= " and ct.ContragentType_Code in ({$data['ContragentType_CodeList']})";
			}

			if (!empty($data['ExpDate'])) {
				$filter .= " and (ced.BegDate is null or ced.BegDate <= :ExpDate)";
				$filter .= " and (ced.EndDate is null or ced.EndDate >= :ExpDate)";
				$params['ExpDate'] = $data['ExpDate'];
			}

			if (!empty($data['query'])) {
				$filter .= " and (c.Contragent_Name like '%'+:query+'%' or  c.Contragent_Code like :query+'%')";
				$params['query'] = $data['query'];
			}
		}

		$query = "
			Select
				c.Contragent_id,
				c.ContragentType_id,
				rtrim(ct.ContragentType_Name) as ContragentType_Name,
				c.Lpu_id,
				c.Org_id,
				ot.OrgType_SysNick,
				o.Server_id as OrgServer_id,
				c.Org_pid,
				c.OrgFarmacy_id,
				c.LpuSection_id,
				c.Contragent_Code,
				RTrim(c.Contragent_Name) as Contragent_Name,
				convert(varchar(10), ced.BegDate, 104) BegDate,
				convert(varchar(10), ced.EndDate, 104) EndDate
			from
				v_Contragent c with (nolock)
				left join v_ContragentType ct with (nolock) on ct.ContragentType_id = c.ContragentType_id
				left join v_Org o with (nolock) on o.Org_id = c.Org_id
				left join v_OrgType ot with (nolock) on ot.OrgType_id = o.OrgType_id
				left join OrgFarmacy ofr with (nolock) on ofr.OrgFarmacy_id = c.OrgFarmacy_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = c.LpuSection_id
				outer apply (
					select
						 isnull(ContragentExpDates.BegDate, c.Contragent_insDT) as BegDate,
						 ContragentExpDates.EndDate
					from
						dbo.GetContragentExpDates(c.Contragent_id, c.ContragentType_id) ContragentExpDates
				) ced
			where
				{$filter}
			order by
				c.Contragent_Code, c.Contragent_Name
		";

		//print(getDebugSQL($query, $params)); die;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка контрагентов (режим "Получатель" для Крыма)
	 */
	function loadContragentKrymTList($data) {
		$params = array();
		$filter = "(1=1)";
        $session_data = getSessionParams();
        $org_id = $session_data['session']['org_id'];
		$org_filter = "";

        if (!empty($org_id)) { //фильтр по организации пользователя
            //получение информации об организации и её "родственниках"
            $query = "
                select
                    -- организация
                    o.Org_id as O_Org_id,
                    o.Org_Name as O_Org_Name,
                    ct.ContragentType_SysNick as O_ContragentType_SysNick,
                    -- родитель организации
                    p_o.Org_id as P_Org_id,
                    p_o.Org_Name as P_Org_Name,
                    p_c.ContragentType_SysNick as P_ContragentType_SysNick,
                    -- дед организации
                    pp_o.Org_id as PP_Org_id,
                    pp_o.Org_Name as PP_Org_Name,
                    pp_c.ContragentType_SysNick as PP_ContragentType_SysNick
                from
                    v_Contragent c with (nolock)
                    left join v_ContragentType ct with (nolock) on ct.ContragentType_id = c.ContragentType_id
                    left join v_Org o with (nolock) on o.Org_id = c.Org_id  -- текущая организация
                    left join v_Org p_o with (nolock) on p_o.Org_id = o.Org_pid -- родитель организации
                    left join v_Org pp_o with (nolock) on pp_o.Org_id = p_o.Org_pid -- дед организации
                    outer apply (
                        select top 1
                            i_c.Contragent_id,
                            i_ct.ContragentType_Name,
                            i_ct.ContragentType_SysNick
                        from
                            v_Contragent i_c with (nolock)
                            left join v_ContragentType i_ct with (nolock) on i_ct.ContragentType_id = i_c.ContragentType_id
                        where
                            i_c.Org_id = p_o.Org_id
                        order by
                            i_c.Contragent_id
                    ) p_c
                    outer apply (
                        select top 1
                            i_c.Contragent_id,
                            i_ct.ContragentType_Name,
                            i_ct.ContragentType_SysNick
                        from
                            v_Contragent i_c with (nolock)
                            left join v_ContragentType i_ct with (nolock) on i_ct.ContragentType_id = i_c.ContragentType_id
                        where
                            i_c.Org_id = pp_o.Org_id
                        order by
                            i_c.Contragent_id
                    ) pp_c
                where
                    c.Org_id = :Org_id;
            ";
            $org_data = $this->getFirstRowFromQuery($query, array(
                'Org_id' => $org_id
            ));

            //нельзя выбрать себя в качестве получателя
            $org_filter = " and c.Org_id != :O_Org_id";
            $params['O_Org_id'] = $org_id;

            //если контрагент пользователя - РАС, то фильтр по организации только базовый
            if ($org_data['O_ContragentType_SysNick'] == 'apt') { //если организация - аптека
                //аптеки без предков могут взаимодействовать только с РАС
                if (empty($org_data['P_Org_id'])) {
                    $org_filter = " and c.Org_id in (
                        select
                            i_c.Org_id
                        from
                            v_Contragent i_c with (nolock)
                            left join ContragentType i_ct with (nolock) on ct.ContragentType_id = i_c.ContragentType_id
                        where
                            i_ct.ContragentType_SysNick = 'store'
                    )";
                }

                //аптеки родителем которых является РАС могут взаимодействовать со своими родителями и детьми
                if ($org_data['P_ContragentType_SysNick'] == 'store') {
                    $org_filter = " and (c.Org_id = :P_Org_id or o.Org_pid = :O_Org_id)";
                    $params['P_Org_id'] = $org_data['P_Org_id'];
                    $params['O_Org_id'] = $org_id;
                }

                //аптеки дедом которых является РАС могут взаимодействовать со своими родителями и дедом
                if ($org_data['PP_ContragentType_SysNick'] == 'store') {
                    $org_filter = " and (c.Org_id = :P_Org_id or c.Org_id = :PP_Org_id)";
                    $params['P_Org_id'] = $org_data['P_Org_id'];
                    $params['PP_Org_id'] = $org_data['PP_Org_id'];
                }
            }
        }

		if (!empty($data['Contragent_id']) && $data['Contragent_id'] > 0) {
			$filter .= " and c.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		} else {
			$filter .= " and (ofr.Region_id is null or ofr.Region_id = dbo.GetRegion())";

			if (!empty($data['Lpu_id']) && $data['Lpu_id'] > 0) {
				$filter .= " and c.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			} else {
				$filter .= " and (c.Lpu_id is null or c.Lpu_id = 0)";
			}

			if (!empty($data['ContragentType_CodeList'])) {
				$filter .= " and ct.ContragentType_Code in ({$data['ContragentType_CodeList']})";
			}

			if (!empty($data['ExpDate'])) {
				$filter .= " and (ced.BegDate is null or ced.BegDate <= :ExpDate)";
				$filter .= " and (ced.EndDate is null or ced.EndDate >= :ExpDate)";
				$params['ExpDate'] = $data['ExpDate'];
			}

			if (!empty($data['query'])) {
				$filter .= " and (c.Contragent_Name like '%'+:query+'%' or  c.Contragent_Code like :query+'%')";
				$params['query'] = $data['query'];
			}

            $filter .= $org_filter;
		}

		$query = "
			Select
				c.Contragent_id,
				c.ContragentType_id,
				rtrim(ct.ContragentType_Name) as ContragentType_Name,
				c.Lpu_id,
				c.Org_id,
				ot.OrgType_SysNick,
				o.Server_id as OrgServer_id,
				c.Org_pid,
				c.OrgFarmacy_id,
				c.LpuSection_id,
				c.Contragent_Code,
				RTrim(c.Contragent_Name) as Contragent_Name,
				convert(varchar(10), ced.BegDate, 104) BegDate,
				convert(varchar(10), ced.EndDate, 104) EndDate
			from
				v_Contragent c with (nolock)
				left join v_ContragentType ct with (nolock) on ct.ContragentType_id = c.ContragentType_id
				left join v_Org o with (nolock) on o.Org_id = c.Org_id
				left join v_OrgType ot with (nolock) on ot.OrgType_id = o.OrgType_id
				left join OrgFarmacy ofr with (nolock) on ofr.OrgFarmacy_id = c.OrgFarmacy_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = c.LpuSection_id
				outer apply (
					select
						 isnull(ContragentExpDates.BegDate, c.Contragent_insDT) as BegDate,
						 ContragentExpDates.EndDate
					from
						dbo.GetContragentExpDates(c.Contragent_id, c.ContragentType_id) ContragentExpDates
				) ced
			where
				{$filter}
			order by
				c.Contragent_Code, c.Contragent_Name
		";

		//print(getDebugSQL($query, $params)); die;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка партий для комбо (используется при редактировании спецификации документа учета)
	 */
	function loadDocumentUcStrOidCombo($filter) {
		$join = array();
		$where = array();
		$ost_where = array();
		$order = array();
		$with = array();
		$join_sql = "";
		$where_sql = "";
		$ost_where_sql = "";
		$order_sql = "";
		$or_reserv = "";

		$where[] = 'ddt.DrugDocumentType_Code in (3, 6, 15, 32, 35)'; //3 - документ ввода остатков; 6 - приходная накладная; 15 - Накладная на внутреннее перемещение; 32 - Приход в отделение; 35 - Разукомплектация: постановка на учет
		$where[] = 'ost.cnt > 0';

        $filter['DefaultGoodsUnit_id'] = $this->getDefaultGoodsUnitId();
        $filter['ShowEdOst'] = $filter['isEdOstEnabled'] == 'true' ? 1 : 0;

		if (!empty($filter['DocumentUc_id']) && $filter['DocumentUc_id'] > 0) {
			$where[] = 'du.DocumentUc_id = :DocumentUc_id';
			$or_reserv = " or (
				sat.SubAccountType_Code = 2
				and dor.DrugOstatRegistry_id in (
					select top 1
	                    DrugOstatRegistry_id
	                from
	                	v_DrugOstatRegistryLink with (nolock)
	                where
	                    DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
	                    DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
				)
			)";
		}
		if (!empty($filter['Contragent_id']) && $filter['Contragent_id'] > 0) {
			$where[] = 'du.Contragent_id = :Contragent_id';
		}
		if (!empty($filter['Drug_id']) && $filter['Drug_id'] > 0) {
			$where[] = 'dus.Drug_id = :Drug_id';
		}
		if (!empty($filter['WhsDocumentUc_id']) && $filter['WhsDocumentUc_id'] > 0) {
			$where[] = 'du.WhsDocumentUc_id = :WhsDocumentUc_id';
		}
		if (!empty($filter['DrugFinance_id']) && $filter['DrugFinance_id'] > 0) {
			$where[] = 'fin_val.DrugFinance_id = :DrugFinance_id';
		}
		if (!empty($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id'] > 0) {
			$where[] = 'fin_val.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
		}
        if (!empty($filter['PrepSeries_IsDefect']) && $filter['PrepSeries_IsDefect'] > 0) {
            $ost_where[] = "isnull(ps.PrepSeries_IsDefect, 1) = :PrepSeries_IsDefect";
        }
        if (!empty($filter['DrugShipment_setDT_max'])) {
            $where[] = "cast(ds.DrugShipment_setDT as date) <= :DrugShipment_setDT_max";
        }
        if (!empty($filter['CheckGodnDate']) && $filter['CheckGodnDate'] == 'current_date') {
            $ost_where[] = "
                (
                    ps.PrepSeries_GodnDate is null or
                    ps.PrepSeries_GodnDate >= @CurrentDate
                )
            ";
        }
        if (!empty($filter['StorageZone_id'])) {
            $join[] = " outer apply(
            		select top 1
            			dsz.DrugStorageZone_id,
            			dsz.DrugStorageZone_Count,
            			dsl.DocumentUcStr_id
            		from
            		    v_DrugStorageZone dsz with (nolock)
            		where
            			dsz.Drug_id = dus.Drug_id
            			and dsz.DrugShipment_id = dsl.DrugShipment_id
            			and dsz.StorageZone_id = :StorageZone_id
            	) dsz_c ";
            $where[] = "dsz_c.DrugStorageZone_Count > 0";
        }

		$where_sql = join($where, ' and ');

		if (!empty($filter['DocumentUcStr_id']) && $filter['DocumentUcStr_id'] > 0) {
			$where_sql = "({$where_sql}) or dus.DocumentUcStr_id = :DocumentUcStr_id";
		} else {
            if (count($ost_where) > 0) {
                $ost_where_sql = ' and '.join($ost_where, ' and ');
            }
        }

        if (!empty($filter['Sort_Type'])) {
            switch($filter['Sort_Type']) {
                case 'defect_godn':
                    $join[] = "
                        outer apply (
                            select
                                datediff(month, @CurrentDate, PS.PrepSeries_GodnDate) as PrepSeries_MonthCount
                        ) psmc
                    ";
                    $join[] = "outer apply (
                        select
                            (case
                                when psmc.PrepSeries_MonthCount < 6 then psmc.PrepSeries_MonthCount
                                when isnull(isdef.YesNo_Code, 0) = 1 then 10
                                else 100
                            end) as Sort_Points
                        ) srt";
                    $order[] = "srt.Sort_Points";
                    break;
                case 'godn': //сортировка по сроку годности
                    $join[] = "
                        outer apply (
                            select
                                datediff(month, @CurrentDate, PS.PrepSeries_GodnDate) as PrepSeries_MonthCount
                        ) psmc
                    ";
                    $order[] = "psmc.PrepSeries_MonthCount";
                    break;
            }
        }

        $order[] = "dus.DocumentUcStr_id";


        if (count($join) > 0) {
            $join_sql = implode(" ", $join);
        }

        if (count($order) > 0) {
            $order_sql = implode(", ", $order);
            $order_sql = "
                order by
                    {$order_sql}
            ";
        }

		$q = "
		    declare
		        @CurrentDate date,
		        @ShowEdOst bit;

            set @CurrentDate = dbo.tzGetDate();
            set @ShowEdOst = :ShowEdOst;

			select
				dus.Drug_id,
				dus.DocumentUcStr_id,
				ds.DrugShipment_Name,
				ds.DrugShipment_id,
				at.AccountType_Name,
				(
				    '№ '+ds.DrugShipment_Name+
				    isnull(', '+ps.PrepSeries_Ser, '')+
					isnull(', '+convert(varchar(10), ps.PrepSeries_GodnDate, 104), '')+
		            (
		                case
		                    when isnull(isdef.YesNo_Code, 0) = 1 then ', забракованная серия'
		                    else ''
		                end
		            )+
					--isnull(', '+cast(dus.DocumentUcStr_PriceR as varchar), '')+
					isnull(', '+cast(dn.DrugNds_Code as varchar), '')+
					isnull(', '+cast(cast(ost.cnt as float) as varchar(20))+' '+gu_b.GoodsUnit_Nick, '')+
					(case
					    when @ShowEdOst = 1 then isnull(' / '+cast(cast(gu_ost.cnt as float) as varchar(20))+' '+gu.GoodsUnit_Nick, '')
					    else ''
					end)+
					isnull(', '+df.DrugFinance_Name, '')+
					isnull(', '+wdcit.WhsDocumentCostItemType_Name, '')+
					isnull(', '+at.AccountType_Name, '')
				) as DocumentUcStr_Name,
				dus.DocumentUcStr_Price,
				dus.DocumentUcStr_PriceR,
				dus.DrugNds_id,
				isnull(isnds.YesNo_Code, 0) as DocumentUcStr_IsNDS,
				ps.PrepSeries_Ser as DocumentUcStr_Ser,
				convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
				dus.DocumentUcStr_CertNum,
				dus.DocumentUcStr_CertOrg,
				convert(varchar(10), dus.DocumentUcStr_CertDate, 104) as DocumentUcStr_CertDate,
				convert(varchar(10), dus.DocumentUcStr_CertGodnDate, 104) as DocumentUcStr_CertGodnDate,
				dus.DrugLabResult_Name,
				dn.DrugNds_Code,
				cast(ost.cnt as float) as DocumentUcStr_OstCount,
				isnull(df.DrugFinance_Name, '') as DrugFinance_Name,
				isnull(wdcit.WhsDocumentCostItemType_Name, '') as WhsDocumentCostItemType_Name,
				gu_b.GoodsUnit_id as GoodsUnit_bid,
				isnull(gu_b.GoodsUnit_Nick, '') as GoodsUnit_bNick,
				gu.GoodsUnit_id as GoodsUnit_id,
				isnull(gu.GoodsUnit_Nick, '') as GoodsUnit_Nick,
				cast(gu_ost.cnt as float) as GoodsUnit_OstCount
			from
				v_DocumentUcStr dus with (nolock)
				inner join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
				left join v_DocumentUcStr o_dus with (nolock) on o_dus.DocumentUcStr_id = dus.DocumentUcStr_oid
				outer  apply (
					Select top 1 * from v_DocumentUc o_du with (nolock) where o_du.DocumentUc_id = isnull(o_dus.DocumentUc_id, dus.DocumentUc_id) -- если ссылки на приходную партию нет, значит текущая партия и есть приходная
				) o_du
				left join v_AccountType at with (nolock) on at.AccountType_id = ds.AccountType_id
				left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
				left join v_Contragent con_s with (nolock) on con_s.Contragent_id = du.Contragent_sid
				left join v_Lpu lpu_s with (nolock) on lpu_s.Lpu_id = con_s.Lpu_id
				left join v_Contragent con_t with (nolock) on con_t.Contragent_id = du.Contragent_tid
				left join v_Lpu lpu_t with (nolock) on lpu_t.Lpu_id = con_t.Lpu_id
				left join v_DrugNds dn on dn.DrugNds_id = dus.DrugNds_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_GoodsUnit gu_b with (nolock) on gu_b.GoodsUnit_id = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id)
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = dus.GoodsUnit_id
				outer apply (
					select (
						case
							when isnull(isnds.YesNo_Code, 0) = 1 then dus.DocumentUcStr_PriceR
							else dus.DocumentUcStr_PriceR*(1+(isnull(dn.DrugNds_Code, 0)/100.0))
						end
					) as price
				) price
				outer apply (
					select
						isnull(sum(dor.DrugOstatRegistry_Kolvo), 0) as cnt,
						max(dor.DrugFinance_id) as DrugFinance_id,
						max(dor.WhsDocumentCostItemType_id) as WhsDocumentCostItemType_id
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
					where
						(
							sat.SubAccountType_Code = 1  and
							(
								dor.Org_id is NULL or
								(ddt.DrugDocumentType_Code in (3, 6, 32, 35) and dor.Org_id = isnull(con_t.Org_id, lpu_t.Org_id)) or
								(ddt.DrugDocumentType_Code = 15 and dor.Org_id = isnull(con_s.Org_id, lpu_s.Org_id))
							) and
							dor.Drug_id = dus.Drug_id and
							(:Storage_id is null or dor.Storage_id = :Storage_id) and
							(dor.Contragent_id is null or :Contragent_id is null or dor.Contragent_id = :Contragent_id) and
							(ps.PrepSeries_Ser = dus.DocumentUcStr_Ser or ps.PrepSeries_id = dus.PrepSeries_id) and
							dor.DrugOstatRegistry_Cost = cast(price.price as Numeric(18,2)) and
							dor.DrugShipment_id = dsl.DrugShipment_id
							{$ost_where_sql}
						) {$or_reserv}
				) ost
				outer apply ( -- для ускорения рассчета, рассчет остатка в ед. списания сделан через пропорцию
                    select (
                        case
                            when gu_b.GoodsUnit_id = gu.GoodsUnit_id then ost.cnt
                            when gu_b.GoodsUnit_id <> gu.GoodsUnit_id and dus.DocumentUcStr_Count > 0 then (dus.DocumentUcStr_EdCount/dus.DocumentUcStr_Count)*ost.cnt
                            when dus.DocumentUcStr_Count = 0 then 0
                            else null
                        end
                    ) as cnt
				) gu_ost
				outer apply (
					select
						isnull(o_du.DrugFinance_id, ost.DrugFinance_id) as DrugFinance_id,
						isnull(o_du.WhsDocumentCostItemType_id, ost.WhsDocumentCostItemType_id) as WhsDocumentCostItemType_id
				) fin_val
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = fin_val.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = fin_val.WhsDocumentCostItemType_id
				{$join_sql}
			where
				{$where_sql}
			{$order_sql};
		";

		if (count($with) > 0) {
			$q = "with ".join($with, ', ').$q;
		}

		//print getDebugSQL($q, $filter);exit;
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Сохранение партии связанной со строкой документа учета
	 */
	function saveLinkedDrugShipment($data) {
        $query = "
            select
                du.DocumentUc_didDate
            from
                v_DocumentUcStr dus with (nolock)
                left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
		    where
				dus.DocumentUcStr_id = :DocumentUcStr_id;
        ";
        $doc_data = $this->getFirstRowFromQuery($query, $data);

		$query = "
			select top 1
				DrugShipment_id
			from
				v_DrugShipmentLink with (nolock)
			where
				DocumentUcStr_id = :DocumentUcStr_id
			order by
				DrugShipmentLink_id;
		";
		$ds_id = $this->getFirstResultFromQuery($query, $data);

		if ($ds_id > 0) {
			$result = $this->saveObject('DrugShipment', array(
				'DrugShipment_id' => $ds_id,
                'DrugShipment_setDT' => !empty($doc_data['DocumentUc_didDate']) ? $doc_data['DocumentUc_didDate'] : date('Y-m-j G:i:s'),
				'DrugShipment_Name' => $data['DrugShipment_Name']
			));
		} else {
            $set_name_by_id = ($data['DrugShipment_Name'] == 'set_name_by_id');

			$result = $this->saveObject('DrugShipment', array(
				'DrugShipment_setDT' => !empty($doc_data['DocumentUc_didDate']) ? $doc_data['DocumentUc_didDate'] : date('Y-m-j G:i:s'),
				'DrugShipment_Name' => $set_name_by_id ? 'tmp_'.$data['DocumentUcStr_id'] : $data['DrugShipment_Name'], //если передан флаг установки наименования по идентификатору, времнно сохраняем наименование равным идентификатору строки
				'DocumentUcStr_id' => $data['DocumentUcStr_id']
			));
			if (is_array($result) && !empty($result['DrugShipment_id'])) {
                $ds_id = $result['DrugShipment_id'];

                //переименовываем партию при необходимости
                if ($set_name_by_id) {
                    $result = $this->saveObject('DrugShipment', array(
                        'DrugShipment_id' => $ds_id,
                        'DrugShipment_Name' => $ds_id
                    ));
                }

                //сохранение связи между новой партией и строкой документа учета
				$result = $this->saveObject('DrugShipmentLink', array(
					'DrugShipment_id' => $ds_id,
					'DocumentUcStr_id' => $data['DocumentUcStr_id']
				));
			}
		}
	}

	/**
	 * Сохранение типа учета в связанной партии
	 */
	function saveLinkedDrugShipmentAccountType($data) {
        $result = array();
        $error = array();

        //получение списка связанных партий
		$query = "
			select distinct
				dsl.DrugShipment_id
			from
			    v_DocumentUcStr dus with (nolock)
				inner join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$ds_array = $this->queryResult($query, $data);

        foreach($ds_array as $ds) {
            $response = $this->saveObject('DrugShipment', array(
                'DrugShipment_id' => $ds['DrugShipment_id'],
                'AccountType_id' => $data['AccountType_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
                break;
            }
        }

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
        return $result;
	}

	/**
	 * Схранение даты выполнения документа в связанных партиях
	 */
	function saveLinkedDrugShipmentSetDT($data) {
        $result = array();
        $error = array();

        //получение списка связанных партий
		$query = "
			select distinct
				dsl.DrugShipment_id
			from
			    v_DocumentUcStr dus with (nolock)
				inner join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$ds_array = $this->queryResult($query, $data);

        foreach($ds_array as $ds) {
            $response = $this->saveObject('DrugShipment', array(
                'DrugShipment_id' => $ds['DrugShipment_id'],
                'DrugShipment_setDT' => $data['DrugShipment_setDT'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
                break;
            }
        }

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
        return $result;
	}

	/**
	 * Получение сгенерированного наименования партии
	 */
	function generateDrugShipmentName() {
		$q = "
			select
				isnull(max(cast(DrugShipment_Name as bigint)),0) + 1 as DrugShipment_Name
			from
				v_DrugShipment with (nolock)
			where
				DrugShipment_Name not like '%.%' and
				DrugShipment_Name not like '%,%' and
				DrugShipment_Name not like '%e%' and
				len(DrugShipment_Name) <= 18 and
				isnumeric(DrugShipment_Name + 'e0') = 1
		";

		$result = $this->db->query($q);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка еа существование документа с заданным номером
	 */
	function checkDocumentUcNumUnique($num) {
		$query = "
			select
				count(du.DocumentUc_id) as cnt
			from
				v_DocumentUc du with (nolock)
			where
				du.DocumentUc_Num = :DocumentUc_Num;
		";
		$cnt = $this->getFirstResultFromQuery($query, array(
			'DocumentUc_Num' => $num
		));

		return ($cnt == 0);
	}

	/**
	 * Получение сгенерированного номера для документа учета
	 */
	function generateDocumentUcNum($data) {
		$num = "";

        if (!isset($data['DrugDocumentType_id'])) {
            $data['DrugDocumentType_id'] = null;
        }

		$query = "
			select
				isnull(:DrugDocumentType_Code, (select DrugDocumentType_Code from v_DrugDocumentType with (nolock) where DrugDocumentType_id = :DrugDocumentType_id)) as DrugDocumentType_Code,
				isnull((select Contragent_Code from v_Contragent with (nolock) where Contragent_id = :Contragent_id), 0) as Contragent_Code;
		";
		$common_data = $this->getFirstRowFromQuery($query, $data);

		$where_array = array();
		$where = "";
		if (!isset($data['disable_limits'])) {
			$where_array[] = "ddt.DrugDocumentType_Code = :DrugDocumentType_Code";
			$where_array[] = "du.DocumentUc_setDate >= convert(varchar, datepart(year, dbo.tzGetDate())) + '-01-01'";
			$where_array[] = "du.DocumentUc_setDate < convert(varchar, datepart(year, dbo.tzGetDate()) + 1) + '-01-01'";
		}

		if (count($where_array) > 0) {
			$where = join(" and ", $where_array)." and ";
		}

		$query = "
			select
				isnull(max(cast(num.num as decimal(10))), 0) as max_num
			from
				v_DocumentUc du with (nolock)
				left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
				outer apply (
					select
						substring(du.DocumentUc_Num, 0, charindex('/', du.DocumentUc_Num)) as num
				) num
			where
				{$where}
				du.DocumentUc_Num like '%/%-%' and
				len(num.num) <= 10 and
				isnumeric(num.num + 'e0') = 1;
		";
		//echo getDebugSQL($query, $common_data); exit;
		$result = $this->getFirstRowFromQuery($query, $common_data);

		if (is_array($result) && isset($result['max_num'])) {
			$num = ($result['max_num']*1+1).'/'.$common_data['DrugDocumentType_Code'].'-'.$common_data['Contragent_Code'];

			//проверка списка запрещенных номеров
			if (isset($data['forbidden_num_list']) && count($data['forbidden_num_list']) > 0) {
				$num_array = $data['forbidden_num_list'];

				//если полученнй номер, в списке запрещенных, используем следующий
				for ($i = 1; $i <= count($num_array); $i++) {
					if (in_array($num, $num_array)) {
						$num = ($result['max_num']*1+1+$i).'/'.$common_data['DrugDocumentType_Code'].'-'.$common_data['Contragent_Code'];
					} else {
						break;
					}
				}
			}
		}

		if (!empty($num)) {
			return array(array('DocumentUc_Num' => $num));
		} else {
			return false;
		}
	}

	/**
	 * Установка идентификатора ГК для строк документа учета
	 */
	function setDrugShipmentSupply($data) {
		$q = "
			select
				wds.WhsDocumentSupply_id,
				ds.DrugShipment_id
			from
				v_DocumentUc du with (nolock)
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = du.WhsDocumentUc_id
				left join v_DocumentUcStr dus with (nolock) on dus.DocumentUc_id = du.DocumentUc_id
				outer apply (
					select top 1
						dsl.DrugShipment_id,
						dsh.WhsDocumentSupply_id
					from
						v_DrugShipmentLink dsl with (nolock)
						left join v_DrugShipment dsh with (nolock) on dsh.DrugShipment_id = dsl.DrugShipment_id
					where
						dsl.DocumentUcStr_id = dus.DocumentUcStr_id
					order by
						dsl.DrugShipmentLink_id
				) ds
				outer apply (
					select
						count(dor.DrugOstatRegistry_id) as cnt
					from
						v_DrugOstatRegistry dor with (nolock)
					where
						dor.DrugShipment_id = ds.DrugShipment_id
				) ost
			where
				du.DocumentUc_id = :DocumentUc_id and
				wds.WhsDocumentSupply_id is not null and
				ds.DrugShipment_id is not null and
				ds.WhsDocumentSupply_id is null and
				ost.cnt = 0;
		";
		$result = $this->db->query($q, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$result = $result->result('array');
			foreach($result as $doc_str) {
				$this->saveObject('DrugShipment', array(
					'DrugShipment_id' => $doc_str['DrugShipment_id'],
					'WhsDocumentSupply_id' => $doc_str['WhsDocumentSupply_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}
	}

	/**
	 * Исполнение документа
	 * $externalTrans - признак исполнения документа из уже запущенной ранее транзакции
	 */
	function executeDocumentUc($data,$externalTrans = false) {
		if(!$externalTrans){
			//старт транзакции
			$this->beginTransaction();
		}

		$error = array(); //для сбора ошибок при "исполнении" документа
		$result = array();

		//Проверки
		//Недопустимый статус
		if (!in_array($data['DrugDocumentStatus_Code'], array(1,11))) { //1 - Новый, 11 - частично исполнен
			$error[] = "Исполнение документа невозможно. Недопустимый статус документа: {$data['DrugDocumentStatus_Name']}.";
		}

		//В списке медикаментов есть позиции с пустой серией или сроком годности
		$query = "
			select
				count(DocumentUcStr_id) as cnt
			from
				DocumentUcStr with (nolock)
			where
				DocumentUc_id = :DocumentUc_id and
				PrepSeries_id is null
		";
		$res = $this->getFirstResultFromQuery($query, array('DocumentUc_id' => $data['DocumentUc_id']));
		if ($res > 0) {
			$error[] = "Исполнение документа невозможно, так как в списке медикаментов есть строки без серии.";
		}

        //Смена статуса документа на время исполнения
        if (count($error) < 1) {
            $response = $this->saveObject('DocumentUc', array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', '12'), //12 - На исполнении
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            }
        }

        if (!in_array($data['DrugDocumentType_Code'], array(6))) {
			$check = $this->checkDocumentStorageWork($data);
			if (!$this->isSuccessful($check)) {
				$error[] = $check[0]['Error_Msg'];
			}
		}

		//Непосредственное исполнение
		if (count($error) < 1) {
			switch($data['DrugDocumentType_Code']) {
				case 2: //Документ списания
				case 23: //Документ списания в производство
				case 25: //Списание медикаментов со склада на пациента. СМП
				case 26: //Списание медикаментов из укладки на пациента - будет исполняться только если укладка не передана на подотчет
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDokSpis($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 3: //Документ ввода остатков
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDokOst($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					$response = $this->saveDrugListToWhsDocumentSupply($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 6: //Приходная накладная
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDokNak($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 10: //Расходная накладная
					//копируем документ, превращая в накладную - создаем приходные накладные для организации-получателя
					$response = $this->createDokNakByDocumentUc($data);
					if (isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					} else {
						//перезаписали данные на вернувшийся ответ (добавлены новые партии)
						$data = $response;
					}
					$response = $this->updateDrugOstatRegistryForDocRas($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 12: //Документ оприходования
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDocOprih($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 15: //Накладная на внутреннее перемещение
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDocNVP($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 17: //Возвратная накладная (расходная)
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDocVozNakR($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 18: //Возвратная накладная (приходная)
					//Корректировка регистра остатков
					$response = $this->updateDrugOstatRegistryForDocVozNakP($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 20: // Пополнение укладки со склада
					$response = $this->updateDrugOstatRegistryForDocRealUkl($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 21: // Списание медикаментов со склада на пациента
					$response = $this->updateDrugOstatRegistryForDocRealPat($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 29: // Передача укладки
					$response = $this->updateDrugOstatRegistryForDocPeredUk($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
				case 30: // Возврат укладки
					$response = $this->updateDrugOstatRegistryForDocVozrUk($data);
					if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
						$error[] = $response[0]['Error_Msg'];
					}
					break;
                case 31: //Накладная на перемещение внутри склада
                    //Корректировка регистра остатков
                    $response = $this->updateDrugOstatRegistryForDocNPVS($data);
                    if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
                        $error[] = $response[0]['Error_Msg'];
                    }
                    break;
                case 32: //Приход в отделение
                    //Корректировка регистра остатков
                    $response = $this->updateDrugOstatRegistryForDokNakVO($data);
                    if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
                        $error[] = $response[0]['Error_Msg'];
                    }
                    break;
                case 33: //Возврат из отделения
                    //Корректировка регистра остатков
                    $response = $this->updateDrugOstatRegistryForDocVozOtd($data);
                    if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
                        $error[] = $response[0]['Error_Msg'];
                    }
                    break;
                case 34: //Разукомплектация: списание
                    //Корректировка регистра остатков
                    $response = $this->updateDrugOstatRegistryForDocRazSpis($data);
                    if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
                        $error[] = $response[0]['Error_Msg'];
                    }
                    break;
				default:
					$error[] = "Для данного типа документов не предусмотрен механизм \"исполнения\"";
					break;
			}

		}

        //копирование файлов из партии для некоторых типах расходных документов
        if (count($error) < 1 && in_array($data['DrugDocumentType_Code'], array(2,25,10,15,31,33,20,21,23))) {
            $this->copyFilesFromOid($data['DocumentUc_id']);
        }

		//смена статуса документа
		if (count($error) < 1) {
			switch ($data['DrugDocumentType_Code']) {
				case 6: //Приходная накладная
					// Получаем строки документа учета
					$drug_arr = $this->_getDocumentUcStr(array(
						'DocumentUc_id' => $data['DocumentUc_id']
					));
					if (count($drug_arr) == 0) {
						$error[] = 'Список медикаментов пуст';
						break;
					}
					$executed_count = 0;
					$new_count = 0;
					foreach ($drug_arr as $drug) {
						// Подсчитываем статусы строк
						if(empty($drug['DrugDocumentStatus_Code']) || $drug['DrugDocumentStatus_Code'] == 1){
							$new_count++;
						} else if($drug['DrugDocumentStatus_Code'] == 4){
							$executed_count++;
						}
					}
					if($executed_count == count($drug_arr)){
						$status_code = 4; //4 - Исполнен
					} else if($new_count == count($drug_arr)) {
						$status_code = 1; //1 - Новый
					} else {
						$status_code = 11; //11 - Частично исполнен
					}
					break;
				case 29: //Передача укладки
					$status_code = 14; //14 -  Подотчет действующий
					break;
                case 34: //Разукомплектация: списание
                    $status_code = 4; //4 - Исполнен
                    $post_doc_id = $this->getDocRazPostId($data['DocumentUc_id']);
                    if (!empty($post_doc_id) && count($error) < 1) {
                        //проставляем статус для документа прихода
                        $result = $this->saveObject('DocumentUc', array(
                            'DocumentUc_id' => $post_doc_id,
                            'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', $status_code),
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if(empty($result['success'])){
                            $error[] = "Не удалось сменить статус дочернего документа";
                        }
                    }
                    break;
				default:
					$status_code = 4; //4 - Исполнен
					break;
			}
			if (count($error) < 1) {
				//проставляем статус для изначального документа
				$result = $this->saveObject('DocumentUc', array(
					'DocumentUc_id' => $data['DocumentUc_id'],
					'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', $status_code),
					'pmUser_id' => $data['pmUser_id']
				));
				if(!empty($result['success'])){
					$result['DrugDocumentStatus_Code'] = $status_code;
				}
			}
		}

		if (count($error) > 0) {
			$result = array('Error_Msg' => $error[0]);
			if(!$externalTrans){
				$this->rollbackTransaction();
			}
			return $result;
		}

		if(!$externalTrans){
			//коммит транзакции
			$this->commitTransaction();
		}

		return $result;
	}

	/**
	 * Резервирование строк регистра остатков при сохранении документа списания
	 */
	function reserveDrugOstatRegistryForDokSpis($data) {
        $error = array();
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        //предварительно полностью отменяем предыдущее резервирование
        $result = $this->removeReserve(array(
            'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
            'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
            'pmUser_id' => $data['pmUser_id']
        ));
        if (!empty($result['Error_Msg'])) {
            $error[] = $result['Error_Msg'];
        }

        if (count($error) == 0) {
			//получение данных по строкам документа учета
			$query = "
				select
					dus.DocumentUcStr_id,
					dus.DocumentUcStr_Count,
					dor.DrugOstatRegistry_id,
					dor.DrugOstatRegistry_Kolvo,
					d.Drug_Name,
					ps.PrepSeries_Ser,
					convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
					ds.DrugShipment_Name
				from
					v_DocumentUcStr dus with (nolock)
					left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
					left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
					left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
					outer apply (
						select top 1
							i_dor.DrugOstatRegistry_id,
							i_dor.DrugOstatRegistry_Kolvo,
							i_dor.SubAccountType_id,
							i_dor.Okei_id,
							i_dor.DrugOstatRegistry_Cost
						from
							v_DrugOstatRegistry i_dor with (nolock)
							left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
						where
							i_dor.Drug_id = dus.Drug_id and
							i_dor.DrugShipment_id = dsl.DrugShipment_id and
							isnull(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id) and
							i_sat.SubAccountType_Code = 1 -- Доступно
						order by
							i_dor.DrugOstatRegistry_id
					) dor
				where
					dus.DocumentUc_id = :DocumentUc_id or
					dus.DocumentUcStr_id = :DocumentUcStr_id;
			";

			$result = $this->db->query($query, array(
				'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
				'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
				'DefaultGoodsUnit_id' => $default_goods_unit_id
			));

			if (is_object($result)) {
				$drug_array = $result->result('array');

				foreach($drug_array as $drug) {
					if ($drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
						//резервирование остатков
						$result = $this->moveToReserve(array(
							'DrugOstatRegistry_id' => $drug['DrugOstatRegistry_id'],
							'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
							'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
							'DefaultGoodsUnit_id' => $default_goods_unit_id,
							'pmUser_id' => $data['pmUser_id']
						));
						if (!empty($result['Error_Msg'])) {
							$error[] = $result['Error_Msg'];
							break;
						}
					} else {
						$error[] = "Не может быть зарезевирован медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада.";
						break;
					}
				}
			} else {
				$error[] = 'Не удалось получить данные о строках документа учета';
			}
		}

        $result = array();
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
		return $result;
	}

	/**
	 * Резервирование строк регистра остатков при сохранении документа списания медикаментов со склада на пациента
	 */
	function reserveDrugOstatRegistryForDokRealPat($data) {
        $error = array();
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        //предварительно полностью отменяем предыдущее резервирование
        $result = $this->removeReserve(array(
            'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
            'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
            'pmUser_id' => $data['pmUser_id']
        ));
        if (!empty($result['Error_Msg'])) {
            $error[] = $result['Error_Msg'];
        }

        if (count($error) == 0) {
			//получение данных по строкам документа учета
			$query = "
				select
					dus.DocumentUcStr_id,
					dus.DocumentUcStr_Count,
					dor.DrugOstatRegistry_id,
					dor.DrugOstatRegistry_Kolvo,
					d.Drug_Name,
					ps.PrepSeries_Ser,
					convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
					ds.DrugShipment_Name
				from
					v_DocumentUcStr dus with (nolock)
					left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
					left join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
					left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
					left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
					left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
					outer apply (
						select top 1
							(
								case
									when
										isnull(isnds.YesNo_Code, 0) = 1
									then
										isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)
									else
										cast(isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
								end
							) as DocumentUcStr_Price
						from
							v_DocumentUcStr dus2 with (nolock)
							left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus2.DocumentUcStr_IsNDS
							left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus2.DrugNds_id
						where
							dus2.DocumentUcStr_id = dus.DocumentUcStr_id
					) cost
					outer apply (
						select top 1
							i_dor.DrugOstatRegistry_id,
							i_dor.DrugOstatRegistry_Kolvo,
							i_dor.SubAccountType_id,
							i_dor.Okei_id,
							i_dor.DrugOstatRegistry_Cost
						from
							v_DrugOstatRegistry i_dor with (nolock)
							left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
						where
							i_dor.Drug_id = dus.Drug_id and
							i_dor.DrugShipment_id = dsl.DrugShipment_id and
							i_dor.DrugOstatRegistry_Cost = cost.DocumentUcStr_Price and
							i_dor.Org_id = c_sid.Org_id and
							i_dor.Contragent_id = du.Contragent_sid and
							(du.Storage_sid is null or i_dor.Storage_id = du.Storage_sid) and
							isnull(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id) and
							i_sat.SubAccountType_Code = 1 -- Доступно
						order by
							i_dor.DrugOstatRegistry_id
					) dor
				where
					dus.DocumentUc_id = :DocumentUc_id or
					dus.DocumentUcStr_id = :DocumentUcStr_id;
			";

			$result = $this->db->query($query, array(
				'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
				'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
				'DefaultGoodsUnit_id' => $default_goods_unit_id
			));

			if (is_object($result)) {
				$drug_array = $result->result('array');

				foreach($drug_array as $drug) {
					if ($drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
						//резервирование остатков
						$result = $this->moveToReserve(array(
							'DrugOstatRegistry_id' => $drug['DrugOstatRegistry_id'],
							'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
							'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!empty($result['Error_Msg'])) {
							$error[] = $result['Error_Msg'];
							break;
						}
					} else {
						$error[] = "Не может быть зарезевирован медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада.";
						break;
					}
				}
			} else {
				$error[] = 'Не удалось получить данные о строках документа учета';
			}
		}

        $result = array();
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
		return $result;
	}

	/**
	 * Резервирование строк регистра остатков при сохранении документа расходная накладная
	 */
	function reserveDrugOstatRegistryForDokRas($data) {
        $error = array();
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        //предварительно полностью отменяем предыдущее резервирование
        $result = $this->removeReserve(array(
            'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
            'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
            'pmUser_id' => $data['pmUser_id']
        ));
        if (!empty($result['Error_Msg'])) {
            $error[] = $result['Error_Msg'];
        }

        if (count($error) == 0) {
			//получение данных по строкам документа учета
			$query = "
				select
					dus.DocumentUcStr_id,
					isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
					dor.DrugOstatRegistry_id,
					dor.DrugOstatRegistry_Kolvo,
					d.Drug_Name,
					ps.PrepSeries_Ser,
					convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
					ds.DrugShipment_Name
				from
					v_DocumentUcStr dus with (nolock)
					left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
					left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
					outer apply (
						select top 1
							dss.DrugShipment_Name
						from
							v_DrugShipmentLink dsl with (nolock)
							left join v_DrugShipment dss with (nolock) on dss.DrugShipment_id = dsl.DrugShipment_id
						where
							dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					) ds
					left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
					left join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
					left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
					left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
					outer apply (
						select top 1
							i_dor.DrugOstatRegistry_id,
							i_dor.DrugOstatRegistry_Kolvo,
							i_dor.SubAccountType_id,
							i_dor.Okei_id,
							i_dor.DrugOstatRegistry_Cost
						from
							v_DrugOstatRegistry i_dor with (nolock)
							left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
							outer apply (
								select top 1
									dsl2.DrugShipment_id
								from
									v_DrugShipmentLink dsl2 with (nolock)
								where
									dsl2.DocumentUcStr_id = dus.DocumentUcStr_oid
							) ds2
							outer apply (
								select top 1
									(
										case
											when
												isnull(isnds.YesNo_Code, 0) = 1
											then
												isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)
											else
												cast(isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
										end
									) as DocumentUcStr_Price
								from
									v_DocumentUcStr dus2 with (nolock)
									left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus2.DocumentUcStr_IsNDS
									left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus2.DrugNds_id
								where
									dus2.DocumentUcStr_id = dus.DocumentUcStr_id
							) cost
						where
							i_dor.Drug_id = dus.Drug_id and
							i_dor.DrugShipment_id = ds2.DrugShipment_id and
							i_dor.PrepSeries_id = dus.PrepSeries_id and
							i_dor.Org_id = c_sid.Org_id and
							i_dor.Contragent_id = du.Contragent_sid and
							(du.Contragent_sid is null or du.Storage_sid is null or i_dor.Storage_id = du.Storage_sid) and
							i_dor.DrugOstatRegistry_Cost = cost.DocumentUcStr_Price and
							isnull(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id) and
							i_sat.SubAccountType_Code = 1 -- Доступно
						order by
							i_dor.DrugOstatRegistry_id
					) dor
				where
					dus.DocumentUc_id = :DocumentUc_id or
					dus.DocumentUcStr_id = :DocumentUcStr_id;
			";

			$result = $this->db->query($query, array(
				'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
				'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
				'DefaultGoodsUnit_id' => $default_goods_unit_id
			));

			if (is_object($result)) {
				$drug_array = $result->result('array');

				foreach($drug_array as $drug) {
					if ($drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
						//резервирование остатков
						$result = $this->moveToReserve(array(
							'DrugOstatRegistry_id' => $drug['DrugOstatRegistry_id'],
							'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
							'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!empty($result['Error_Msg'])) {
							$error[] = $result['Error_Msg'];
							break;
						}
					} else {
						$error[] = "Не может быть зарезевирован медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада.";
						break;
					}
				}
			} else {
				$error[] = 'Не удалось получить данные о строках документа учета';
			}
		}

        $result = array();
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
		return $result;
	}

	/**
	 * Резервирование строк регистра остатков при сохранении документа накладная на внутреннее перемещение - для склада, указанного в качестве склада-поставщика
	 */
	function reserveDrugOstatRegistryForDokNVP($data) {
        $error = array();
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        //предварительно полностью отменяем предыдущее резервирование
        $result = $this->removeReserve(array(
            'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
            'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
            'pmUser_id' => $data['pmUser_id']
        ));
        if (!empty($result['Error_Msg'])) {
            $error[] = $result['Error_Msg'];
        }

        if (count($error) == 0) {
			//получение данных по строкам документа учета
			$query = "
				select
					dus.DocumentUcStr_id,
					isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
					dor.DrugOstatRegistry_id,
					dor.DrugOstatRegistry_Kolvo,
					d.Drug_Name,
					ps.PrepSeries_Ser,
					convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
					ds.DrugShipment_Name
				from
					v_DocumentUcStr dus with (nolock)
					left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
					left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
					outer apply (
						select top 1
							dss.DrugShipment_Name
						from
							v_DrugShipmentLink dsl with (nolock)
							left join v_DrugShipment dss with (nolock) on dss.DrugShipment_id = dsl.DrugShipment_id
						where
							dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					) ds
					left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
					left join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
					outer apply (
						select top 1
							i_dor.DrugOstatRegistry_id,
							i_dor.DrugOstatRegistry_Kolvo,
							i_dor.SubAccountType_id,
							i_dor.Okei_id,
							i_dor.DrugOstatRegistry_Cost
						from
							v_DrugOstatRegistry i_dor with (nolock)
							left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
							left join v_Contragent c with(nolock) on c.Contragent_id = i_dor.Contragent_id
							left join v_Lpu l with(nolock) on l.Lpu_id = c.Lpu_id
							outer apply (
								select top 1
									dsl2.DrugShipment_id
								from
									v_DrugShipmentLink dsl2 with (nolock)
								where
									dsl2.DocumentUcStr_id = dus.DocumentUcStr_oid
							) ds2
							outer apply (
								select top 1
									(
										case
											when
												isnull(isnds.YesNo_Code, 0) = 1
											then
												isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)
											else
												cast(isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
										end
									) as DocumentUcStr_Price,
									case when du2.DrugFinance_id > 0 then du2.DrugFinance_id else dus2.DrugFinance_id end as DrugFinance_id
								from
									v_DocumentUcStr dus2 with (nolock)
									left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus2.DocumentUcStr_IsNDS
									left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus2.DrugNds_id
									left join v_DocumentUc du2 with (nolock) on du2.DocumentUc_id = dus2.DocumentUc_id
								where
									dus2.DocumentUcStr_id = dus.DocumentUcStr_id
							) cost
						where
							i_dor.Drug_id = dus.Drug_id and
							i_dor.DrugShipment_id = ds2.DrugShipment_id and
							i_dor.PrepSeries_id = dus.PrepSeries_id and
							coalesce(i_dor.Org_id,c.Org_id,l.Org_id,0) = ISNULL(c_sid.Org_id,0) and
							i_dor.Contragent_id = du.Contragent_sid and
							i_dor.Storage_id = du.Storage_sid and
							i_dor.DrugOstatRegistry_Kolvo > 0 and
							(cost.DocumentUcStr_Price is null or i_dor.DrugOstatRegistry_Cost = cost.DocumentUcStr_Price) and
							(cost.DrugFinance_id is null or i_dor.DrugFinance_id = cost.DrugFinance_id) and
							(du.WhsDocumentCostItemType_id is null or i_dor.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id) and
							isnull(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id) and
							i_sat.SubAccountType_Code = 1 -- Доступно
						order by
							i_dor.DrugOstatRegistry_id
					) dor
				where
					dus.DocumentUc_id = :DocumentUc_id or
					dus.DocumentUcStr_id = :DocumentUcStr_id;
			";

			$result = $this->db->query($query, array(
				'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
				'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
				'DefaultGoodsUnit_id' => $default_goods_unit_id
			));

			if (is_object($result)) {
				$drug_array = $result->result('array');

				foreach($drug_array as $drug) {
					if ($drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
						//резервирование остатков
						$result = $this->moveToReserve(array(
							'DrugOstatRegistry_id' => $drug['DrugOstatRegistry_id'],
							'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
							'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!empty($result['Error_Msg'])) {
							$error[] = $result['Error_Msg'];
							break;
						}
					} else {
						$error[] = "Не может быть зарезевирован медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада.";
						break;
					}
				}
			} else {
				$error[] = 'Не удалось получить данные о строках документа учета';
			}
		}

        $result = array();
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
		return $result;
	}

	/**
	 * Резервирование строк регистра остатков при сохранении документа возвратной накладной (расходной)
	 */
	function reserveDrugOstatRegistryForDokVozNakR($data) {
        $error = array();
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        //предварительно полностью отменяем предыдущее резервирование
        $result = $this->removeReserve(array(
            'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
            'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
            'pmUser_id' => $data['pmUser_id']
        ));
        if (!empty($result['Error_Msg'])) {
            $error[] = $result['Error_Msg'];
        }

        if (count($error) == 0) {
			//получение данных по строкам документа учета
			$query = "
				select
					dus.DocumentUcStr_id,
					isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
					dor.DrugOstatRegistry_id,
					dor.DrugOstatRegistry_Kolvo,
					d.Drug_Name,
					ps.PrepSeries_Ser,
					convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
					ds.DrugShipment_Name
				from
					v_DocumentUcStr dus with (nolock)
					left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
					left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
					outer apply (
						select top 1
							dss.DrugShipment_Name
						from
							v_DrugShipmentLink dsl with (nolock)
							left join v_DrugShipment dss with (nolock) on dss.DrugShipment_id = dsl.DrugShipment_id
						where
							dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					) ds
					left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
					left join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
					outer apply (
						select top 1
							i_dor.DrugOstatRegistry_id,
							i_dor.DrugOstatRegistry_Kolvo,
							i_dor.SubAccountType_id,
							i_dor.Okei_id,
							i_dor.DrugOstatRegistry_Cost
						from
							v_DrugOstatRegistry i_dor with (nolock)
							left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
							outer apply (
								select top 1
									dsl2.DrugShipment_id
								from
									v_DrugShipmentLink dsl2 with (nolock)
								where
									dsl2.DocumentUcStr_id = dus.DocumentUcStr_oid
							) ds2
							outer apply (
								select top 1
									(
										case
											when
												isnull(isnds.YesNo_Code, 0) = 1
											then
												isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)
											else
												cast(isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
										end
									) as DocumentUcStr_Price
								from
									v_DocumentUcStr dus2 with (nolock)
									left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus2.DocumentUcStr_IsNDS
									left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus2.DrugNds_id
								where
									dus2.DocumentUcStr_id = dus.DocumentUcStr_id
							) cost
						where
							i_dor.Drug_id = dus.Drug_id and
							i_dor.DrugShipment_id = ds2.DrugShipment_id and
							i_dor.PrepSeries_id = dus.PrepSeries_id and
							i_dor.Org_id = c_sid.Org_id and
							i_dor.Contragent_id = du.Contragent_sid and
							(du.Contragent_sid is null or du.Storage_sid is null or i_dor.Storage_id = du.Storage_sid) and
							i_dor.DrugOstatRegistry_Cost = cost.DocumentUcStr_Price and
							isnull(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id) and
							i_sat.SubAccountType_Code = 1 -- Доступно
						order by
							i_dor.DrugOstatRegistry_id
					) dor
				where
					dus.DocumentUc_id = :DocumentUc_id or
					dus.DocumentUcStr_id = :DocumentUcStr_id;
			";

			$result = $this->db->query($query, array(
				'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
				'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
				'DefaultGoodsUnit_id' => $default_goods_unit_id
			));

			if (is_object($result)) {
				$drug_array = $result->result('array');

				foreach($drug_array as $drug) {
					if ($drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
						//резервирование остатков
						$result = $this->moveToReserve(array(
							'DrugOstatRegistry_id' => $drug['DrugOstatRegistry_id'],
							'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
							'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (!empty($result['Error_Msg'])) {
							$error[] = $result['Error_Msg'];
							break;
						}
					} else {
						$error[] = "Не может быть зарезевирован медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада.";
						break;
					}
				}
			} else {
				$error[] = 'Не удалось получить данные о строках документа учета';
			}
		}

        $result = array();
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
		return $result;
	}

    /**
	 * Корректировка регистра остатков при исполнении документа списания
	 */
	function updateDrugOstatRegistryForDokSpis($data) {
		$drug_array = array();
		$doc_data = array();
		$session_data = getSessionParams();

		//получение данных документа учета
		$query = "
			select
				du.WhsDocumentUc_id,
				wdst.WhsDocumentStatusType_Code
			from
				v_DocumentUc du with (nolock)
				left join v_WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = du.WhsDocumentUc_id
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";

		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (!is_array($doc_data) || count($doc_data) < 1) {
			return array(array('Error_Msg' => 'Не удалось получить данные о документе учета.'));
		}

        //при наличии резерва списываем остатки с резерва
        if ($this->haveReserve(array('DocumentUc_id' => $data['DocumentUc_id']))) {
            //списание остатков с резерва
            $result = $this->deleteReserve(array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($result['Error_Msg'])) {
                return array(array('Error_Message' => $result['Error_Msg']));
            }

            //получаем строки документа учета
			$drug_arr = array();
			$query = "
				select
					dus.Drug_id,
					dus.PrepSeries_id,
					isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
					isnull(dus.DocumentUcStr_Price,0) as DocumentUcStr_Price,
					ds.DrugShipment_id,
					dus.StorageZone_id,
					dus.DocumentUcStr_id,
					dus.GoodsUnit_bid
				from
					v_DocumentUcStr dus with (nolock)
					outer apply (
						select top 1
							dsl.DrugShipment_id
						from
							v_DrugShipmentLink dsl with (nolock)
						where
							dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					) ds
				where
					DocumentUc_id = :DocumentUc_id;
			";
			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id']
			));
			if (is_object($result)) {
				$drug_arr = $result->result('array');
			}
			if (count($drug_arr) == 0) {
				return array(array('Error_Msg' => 'Список медикаментов пуст'));
			}

			foreach ($drug_arr as $drug) {
				if(!empty($drug['StorageZone_id'])){
                	$result = $this->_updateDrugStorageZone(array(
                		'Drug_id' => $drug['Drug_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_id' => $drug['StorageZone_id'],
                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                		'removeDrugCount' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
	                $result = $this->_commitStorageDrugMove(array(
                		'Drug_id' => $drug['Drug_id'],
                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_nid' => null,
                		'StorageZone_oid' => $drug['StorageZone_id'],
                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
                }
            }
        } else {
            //получение данных по строкам документа учета
            $query = "
                select
                    dus.DocumentUcStr_Count,
                    dor.DrugOstatRegistry_id,
                    dor.DrugOstatRegistry_Kolvo,
                    dor.Contragent_id,
                    dor.Org_id,
                    dor.Storage_id,
                    dor.DrugShipment_id,
                    dor.Drug_id,
                    dor.PrepSeries_id,
                    dor.SubAccountType_id,
                    dor.Okei_id,
                    dor.DrugOstatRegistry_Cost,
                    dor.GoodsUnit_id,
                    d.Drug_Name,
                    ps.PrepSeries_Ser,
                    convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
                    ds.DrugShipment_Name,
		    		dor.Drug_did,
		    		dus.StorageZone_id,
		    		dus.DocumentUcStr_id
                from
                    v_DocumentUcStr dus with (nolock)
                    left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
                    left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
                    left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
                    left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
                    outer apply (
                        select top 1
                            i_dor.DrugOstatRegistry_id,
                            i_dor.DrugOstatRegistry_Kolvo,
                            i_dor.Contragent_id,
                            i_dor.Org_id,
                            i_dor.Storage_id,
                            i_dor.DrugShipment_id,
                            i_dor.Drug_id,
                            i_dor.PrepSeries_id,
                            i_dor.SubAccountType_id,
                            i_dor.Okei_id,
                            i_dor.DrugOstatRegistry_Cost,
			    			i_dor.Drug_did,
			    			i_dor.GoodsUnit_id
                        from
                            v_DrugOstatRegistry i_dor with (nolock)
                            left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
                        where
                            i_dor.Drug_id = dus.Drug_id and
                            i_dor.DrugShipment_id = dsl.DrugShipment_id and
						    i_sat.SubAccountType_Code = 1 -- Доступно
                        order by
                            i_dor.DrugOstatRegistry_id
                    ) dor
                where
                    dus.DocumentUc_id = :DocumentUc_id;
            ";
			$orgtype = $session_data['session']['orgtype'];
			$region =  $session_data['session']['region']['nick'];
			if ($region == 'ufa' && $orgtype == 'farm') {
				$query = "
					select
						dus.DocumentUcStr_Count,
						dor.DrugOstatRegistry_id,
						dor.DrugOstatRegistry_Kolvo,
						dor.Contragent_id,
						dor.Org_id,
						dor.Storage_id,
						dor.DrugShipment_id,
						dor.Drug_id,
						dor.PrepSeries_id,
						dor.SubAccountType_id,
						dor.Okei_id,
						dor.DrugOstatRegistry_Cost,
						dor.GoodsUnit_id,
						d.Drug_Name,
						dus.DocumentUcStr_Ser PrepSeries_Ser,
						convert(varchar(10), dus.DocumentUcStr_godnDate, 104) as PrepSeries_GodnDate,
						ds.DrugShipment_Name,
						dor.Drug_did,
						dus.StorageZone_id,
						dus.DocumentUcStr_id
						from
						v_DocumentUcStr dus with (nolock)
						left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
						left join r2.attachMoByFarmacy a  with (nolock) on a.DocumentUcStr_id = dus.DocumentUcStr_oid
						left join dbo.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
						left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
						outer apply (
							select top 1
                                i_dor.DrugOstatRegistry_id,
                                i_dor.DrugOstatRegistry_Kolvo,
                                i_dor.Contragent_id,
                                i_dor.Org_id,
                                i_dor.Storage_id,
                                i_dor.DrugShipment_id,
                                i_dor.Drug_id,
                                i_dor.PrepSeries_id,
                                i_dor.SubAccountType_id,
                                i_dor.Okei_id,
                                i_dor.DrugOstatRegistry_Cost,
                                i_dor.Drug_did,
                                i_dor.GoodsUnit_id
                            from
                                v_DrugOstatRegistry i_dor with (nolock)
                                left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
                                outer apply(
                                    Select top 1
                                        lpu_id
                                    from
                                        v_OrgFarmacyIndex farm with (nolock)
                                    where
                                        farm.Storage_id = i_dor.Storage_id and
                                        farm.Org_id = i_dor.Org_id
                                ) farm
                            where
                                i_dor.Drug_did = dus.Drug_id and
                                i_dor.DrugShipment_id = dsl.DrugShipment_id and
                                isnull(a.Lpu_id, 0) = isnull(farm.Lpu_id, 0) and
						        i_sat.SubAccountType_Code = 1 -- Доступно
                            order by
                                i_dor.DrugOstatRegistry_id
						) dor
						where
						dus.DocumentUc_id = :DocumentUc_id;
				";
			}
			/*
			echo(getDebugSQL($query,  array(
					'DocumentUc_id' => $data['DocumentUc_id']
				))); exit;
			*/

            $result = $this->db->query($query, array(
                'DocumentUc_id' => $data['DocumentUc_id']
            ));

            if (is_object($result)) {
                $drug_array = $result->result('array');
                foreach($drug_array as $drug) {
                    if ( $drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
                        $dor_data = $drug;
                        $dor_data['pmUser_id'] = $data['pmUser_id'];
                        $dor_data['DrugOstatRegistry_Kolvo'] = $drug['DocumentUcStr_Count']*(-1);
                        $dor_data['DrugOstatRegistry_Sum'] = $drug['DrugOstatRegistry_Cost']*$drug['DocumentUcStr_Count']*(-1);

                        $query = "
                            declare
                                @ErrCode int,
                                @ErrMessage varchar(4000);
                            exec xp_DrugOstatRegistry_count
                                @Contragent_id = :Contragent_id,
                                @Org_id = :Org_id,
                                @Storage_id = :Storage_id,
                                @DrugShipment_id = :DrugShipment_id,
                                @Drug_id = :Drug_id,
								@Drug_did = :Drug_did,
                                @PrepSeries_id = :PrepSeries_id,
                                @SubAccountType_id = 1, -- субсчёт доступно
                                @Okei_id = :Okei_id,
                                @DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
                                @DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
                                @DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
                                @InnerTransaction_Disabled = 1,
                                @GoodsUnit_id = :GoodsUnit_id,
                                @pmUser_id = :pmUser_id,
                                @Error_Code = @ErrCode output,
                                @Error_Message = @ErrMessage output;
                            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                        ";
                        $result = $this->getFirstRowFromQuery($query, $dor_data);
                        if (!empty($result['Error_Msg'])) {
                            return array($result);
                        }
                    } else {
                        return array(array('Error_Msg' => "Не может быть списан медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада."));
                    }
                    if(!empty($drug['StorageZone_id'])){
	                	$result = $this->_updateDrugStorageZone(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_id' => $drug['StorageZone_id'],
	                		'DrugStorageZone_Price' => $drug['DrugOstatRegistry_Cost'],
	                		'removeDrugCount' => $drug['DrugOstatRegistry_Kolvo'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_id'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при списании медикамента с места хранения'.$result[0]['Error_Msg']);
		                }
		                $result = $this->_commitStorageDrugMove(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_nid' => null,
	                		'StorageZone_oid' => $drug['StorageZone_id'],
	                		'StorageDrugMove_Price' => $drug['DrugOstatRegistry_Cost'],
	                		'StorageDrugMove_Count' => $drug['DrugOstatRegistry_Kolvo'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_id'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента с места хранения'.$result[0]['Error_Msg']);
		                }
	                }
                }
            } else {
                return array(array('Error_Msg' => 'Не удалось получить данные о строках документа учета.'));
            }
        }

		//меняем статус акта о списании если он указан в документе учета и его статус еще не равен "действующий"
		if ($doc_data['WhsDocumentUc_id'] > 0 && $doc_data['WhsDocumentStatusType_Code'] == 1) { //1 - Новый;
			$result = $this->saveObject('WhsDocumentUc', array(
				'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
				'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 2), //2 - Действующий
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array(array());
	}

	/**
	 * Корректировка регистра остатков при исполнении документа списания медикаментов со склада на пациента
	 */
	public function updateDrugOstatRegistryForDocRealPat($data) {
		$drug_array = array();
		$doc_data = array();

		//получение данных документа учета
		$query = "
			select
				du.WhsDocumentUc_id,
				wdst.WhsDocumentStatusType_Code
			from
				v_DocumentUc du with (nolock)
				left join v_WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = du.WhsDocumentUc_id
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";

		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (!is_array($doc_data) || count($doc_data) < 1) {
			return array(array('Error_Msg' => 'Не удалось получить данные о документе учета.'));
		}

		//при наличии резерва списываем остатки с резерва
        if ($this->haveReserve(array('DocumentUc_id' => $data['DocumentUc_id']))) {
            //списание остатков с резерва
            $result = $this->deleteReserve(array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($result['Error_Msg'])) {
                return array(array('Error_Message' => $result['Error_Msg']));
            }

            //получаем строки документа учета
			$drug_arr = array();
			$query = "
				select
					dus.Drug_id,
					dus.PrepSeries_id,
					isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
					isnull(dus.DocumentUcStr_Price,0) as DocumentUcStr_Price,
					ds.DrugShipment_id,
					dus.StorageZone_id,
					dus.DocumentUcStr_id,
					dus.GoodsUnit_bid
				from
					v_DocumentUcStr dus with (nolock)
					outer apply (
						select top 1
							dsl.DrugShipment_id
						from
							v_DrugShipmentLink dsl with (nolock)
						where
							dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					) ds
				where
					DocumentUc_id = :DocumentUc_id;
			";
			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id']
			));
			if (is_object($result)) {
				$drug_arr = $result->result('array');
			}
			if (count($drug_arr) == 0) {
				return array(array('Error_Msg' => 'Список медикаментов пуст'));
			}

			foreach ($drug_arr as $drug) {
				if(!empty($drug['StorageZone_id'])){
                	$result = $this->_updateDrugStorageZone(array(
                		'Drug_id' => $drug['Drug_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_id' => $drug['StorageZone_id'],
                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                		'removeDrugCount' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
	                $result = $this->_commitStorageDrugMove(array(
                		'Drug_id' => $drug['Drug_id'],
                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_nid' => null,
                		'StorageZone_oid' => $drug['StorageZone_id'],
                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
                }
            }
        } else {
			//получение данных по строкам документа учета
			$query = "
				select
					dus.DocumentUcStr_Count,
					dor.DrugOstatRegistry_id,
					dor.DrugOstatRegistry_Kolvo,
					dor.Contragent_id,
					dor.Org_id,
					dor.Storage_id,
					dor.DrugShipment_id,
					dor.Drug_id,
					dor.PrepSeries_id,
					dor.SubAccountType_id,
					dor.Okei_id,
					dor.DrugOstatRegistry_Cost,
					dor.GoodsUnit_id,
					d.Drug_Name,
					ps.PrepSeries_Ser,
					convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
					ds.DrugShipment_Name,
					dus.StorageZone_id,
					dus.DocumentUcStr_id
				from
					v_DocumentUcStr dus with (nolock)
					left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
	                left join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
					left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
					left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
					left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
					outer apply (
                        select top 1
                            (
                                case
                                    when
                                        isnull(isnds.YesNo_Code, 0) = 1
                                    then
                                        isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)
                                    else
                                        cast(isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
                                end
                            ) as DocumentUcStr_Price
                        from
                            v_DocumentUcStr dus2 with (nolock)
                            left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus2.DocumentUcStr_IsNDS
                            left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus2.DrugNds_id
                        where
                            dus2.DocumentUcStr_id = dus.DocumentUcStr_id
                    ) cost
					outer apply (
						select top 1
							i_dor.DrugOstatRegistry_id,
							i_dor.DrugOstatRegistry_Kolvo,
							i_dor.Contragent_id,
							i_dor.Org_id,
							i_dor.Storage_id,
							i_dor.DrugShipment_id,
							i_dor.Drug_id,
							i_dor.PrepSeries_id,
							i_dor.SubAccountType_id,
							i_dor.Okei_id,
							i_dor.DrugOstatRegistry_Cost,
							i_dor.GoodsUnit_id
						from
							v_DrugOstatRegistry i_dor with (nolock)
							left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
						where
							i_dor.Drug_id = dus.Drug_id and
							i_dor.DrugShipment_id = dsl.DrugShipment_id and
							i_dor.DrugOstatRegistry_Cost = cost.DocumentUcStr_Price and
                            i_dor.Org_id = c_sid.Org_id and
                            i_dor.Contragent_id = du.Contragent_sid and
                            (du.Storage_sid is null or i_dor.Storage_id = du.Storage_sid) and
							i_sat.SubAccountType_Code = 1 -- Доступно
						order by
							i_dor.DrugOstatRegistry_id
					) dor
				where
					dus.DocumentUc_id = :DocumentUc_id
			";

			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id']
			));

			if (is_object($result)) {
				$drug_array = $result->result('array');
				foreach($drug_array as $drug) {
					if ( $drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
						$dor_data = $drug;
						$dor_data['pmUser_id'] = $data['pmUser_id'];
						$dor_data['DrugOstatRegistry_Kolvo'] = $drug['DocumentUcStr_Count']*(-1);
						$dor_data['DrugOstatRegistry_Sum'] = $drug['DrugOstatRegistry_Cost']*$drug['DocumentUcStr_Count']*(-1);
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec xp_DrugOstatRegistry_count
								@Contragent_id = :Contragent_id,
								@Org_id = :Org_id,
								@Storage_id = :Storage_id,
								@DrugShipment_id = :DrugShipment_id,
								@Drug_id = :Drug_id,
								@PrepSeries_id = :PrepSeries_id,
								@SubAccountType_id = 1, -- субсчёт доступно
								@Okei_id = :Okei_id,
								@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
								@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
								@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                            @InnerTransaction_Disabled = 1,
								@GoodsUnit_id = :GoodsUnit_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";

						$result = $this->getFirstRowFromQuery($query, $dor_data);
						if (!empty($result['Error_Msg'])) {
							return array($result);
						}
					} else {
						return array(array('Error_Msg' => "Не может быть списан медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада."));
					}
					if(!empty($drug['StorageZone_id'])){
	                	$result = $this->_updateDrugStorageZone(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_id' => $drug['StorageZone_id'],
	                		'DrugStorageZone_Price' => $drug['DrugOstatRegistry_Cost'],
	                		'removeDrugCount' => $drug['DrugOstatRegistry_Kolvo'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_id'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
		                $result = $this->_commitStorageDrugMove(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_nid' => null,
	                		'StorageZone_oid' => $drug['StorageZone_id'],
	                		'StorageDrugMove_Price' => $drug['DrugOstatRegistry_Cost'],
	                		'StorageDrugMove_Count' => $drug['DrugOstatRegistry_Kolvo'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_id'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
	                }
				}
			} else {
				return array(array('Error_Msg' => 'Не удалось получить данные о строках документа учета.'));
			}
		}

		//меняем статус акта о списании если он указан в документе учета и его статус еще не равен "действующий"
		if ($doc_data['WhsDocumentUc_id'] > 0 && $doc_data['WhsDocumentStatusType_Code'] == 1) { //1 - Новый;
			$result = $this->saveObject('WhsDocumentUc', array(
				'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
				'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 2), //2 - Действующий
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array(array());
	}

	/**
	 * Корректировка регистра остатков при исполнении документа списания медикаментов со склада на укладку
	 */
	public function updateDrugOstatRegistryForDocRealUkl($data) {
		$drug_array = array();
		$doc_data = array();

		//получение данных документа учета
		$query = "
			select
				du.WhsDocumentUc_id,
				wdst.WhsDocumentStatusType_Code
			from
				v_DocumentUc du with (nolock)
				left join v_WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = du.WhsDocumentUc_id
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";

		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (!is_array($doc_data) || count($doc_data) < 1) {
			return array(array('Error_Msg' => 'Не удалось получить данные о документе учета.'));
		}

		//получение данных по строкам документа учета
		$query = "
			select
				dus.DocumentUcStr_Count,
				dor.DrugOstatRegistry_id,
				dor.DrugOstatRegistry_Kolvo,
				dor.Contragent_id,
				dor.Org_id,
				dor.Storage_id,
				dor.DrugShipment_id,
				dor.Drug_id,
				dor.PrepSeries_id,
				dor.SubAccountType_id,
				dor.Okei_id,
				dor.DrugOstatRegistry_Cost,
				dor.GoodsUnit_id,
				d.Drug_Name,
				ps.PrepSeries_Ser,
				convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				ds.DrugShipment_Name,
				dus.StorageZone_id,
				dus.DocumentUcStr_id
			from
				v_DocumentUcStr dus with (nolock)
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
	            left join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
				outer apply (
                    select top 1
                        (
                            case
                                when
                                    isnull(isnds.YesNo_Code, 0) = 1
                                then
                                    isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)
                                else
                                    cast(isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
                            end
                        ) as DocumentUcStr_Price
                    from
                        v_DocumentUcStr dus2 with (nolock)
                        left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus2.DocumentUcStr_IsNDS
                        left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus2.DrugNds_id
                    where
                        dus2.DocumentUcStr_id = dus.DocumentUcStr_id
                ) cost
				outer apply (
					select top 1
						i_dor.DrugOstatRegistry_id,
						i_dor.DrugOstatRegistry_Kolvo,
						i_dor.Contragent_id,
						i_dor.Org_id,
						i_dor.Storage_id,
						i_dor.DrugShipment_id,
						i_dor.Drug_id,
						i_dor.PrepSeries_id,
						i_dor.SubAccountType_id,
						i_dor.Okei_id,
						i_dor.DrugOstatRegistry_Cost,
						i_dor.GoodsUnit_id
					from
						v_DrugOstatRegistry i_dor with (nolock)
						left join v_SubAccountType i_sat with (nolock) on i_sat.SubAccountType_id = i_dor.SubAccountType_id
					where
						i_dor.Drug_id = dus.Drug_id and
						i_dor.DrugShipment_id = dsl.DrugShipment_id and
                        i_dor.DrugOstatRegistry_Cost = cost.DocumentUcStr_Price and
                        i_dor.Org_id = c_sid.Org_id and
                        i_dor.Contragent_id = du.Contragent_sid and
                        (du.Storage_sid is null or i_dor.Storage_id = du.Storage_sid) and
						i_sat.SubAccountType_Code = 1 -- Доступно
					order by
						i_dor.DrugOstatRegistry_id
				) dor
			where
				dus.DocumentUc_id = :DocumentUc_id
		";

		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (is_object($result)) {
			$drug_array = $result->result('array');
			foreach($drug_array as $drug) {
				if ( $drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
					$dor_data = $drug;
					$dor_data['pmUser_id'] = $data['pmUser_id'];
					$dor_data['DrugOstatRegistry_Kolvo'] = $drug['DocumentUcStr_Count']*(-1);
					$dor_data['DrugOstatRegistry_Sum'] = $drug['DrugOstatRegistry_Cost']*$drug['DocumentUcStr_Count']*(-1);
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec xp_DrugOstatRegistry_count
							@Contragent_id = :Contragent_id,
							@Org_id = :Org_id,
							@Storage_id = :Storage_id,
							@DrugShipment_id = :DrugShipment_id,
							@Drug_id = :Drug_id,
							@PrepSeries_id = :PrepSeries_id,
							@SubAccountType_id = 1, -- субсчёт доступно
							@Okei_id = :Okei_id,
							@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
							@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
							@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
                            @InnerTransaction_Disabled = 1,
							@GoodsUnit_id = :GoodsUnit_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";

					//var_dump(getDebugSQL($query, $dor_data)); exit;

					$result = $this->getFirstRowFromQuery($query, $dor_data);
					if (!empty($result['Error_Msg'])) {
						return array($result);
					}
				} else {
					return array(array('Error_Msg' => "Не может быть списан медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада."));
				}
				if(!empty($drug['StorageZone_id'])){
                	$result = $this->_updateDrugStorageZone(array(
                		'Drug_id' => $drug['Drug_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_id' => $drug['StorageZone_id'],
                		'DrugStorageZone_Price' => $drug['DrugOstatRegistry_Cost'],
                		'removeDrugCount' => $drug['DrugOstatRegistry_Kolvo'],
                		'GoodsUnit_id' => $drug['GoodsUnit_id'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
	                $result = $this->_commitStorageDrugMove(array(
                		'Drug_id' => $drug['Drug_id'],
                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_nid' => null,
                		'StorageZone_oid' => $drug['StorageZone_id'],
                		'StorageDrugMove_Price' => $drug['DrugOstatRegistry_Cost'],
                		'StorageDrugMove_Count' => $drug['DrugOstatRegistry_Kolvo'],
                		'GoodsUnit_id' => $drug['GoodsUnit_id'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
                }
			}
		} else {
			return array(array('Error_Msg' => 'Не удалось получить данные о строках документа учета.'));
		}

		//меняем статус акта о списании если он указан в документе учета и его статус еще не равен "действующий"
		if ($doc_data['WhsDocumentUc_id'] > 0 && $doc_data['WhsDocumentStatusType_Code'] == 1) { //1 - Новый;
			$result = $this->saveObject('WhsDocumentUc', array(
				'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
				'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 2), //2 - Действующий
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array(array());
	}

	/**
	 * Корректировка регистра остатков при исполнении документа передача укладки
	 */
	public function updateDrugOstatRegistryForDocPeredUk($data) {
		$drug_array = array();
		$doc_data = array();
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        //получение данных документа учета
		$query = "
			select
				du.WhsDocumentUc_id,
				wdst.WhsDocumentStatusType_Code
			from
				v_DocumentUc du with (nolock)
				left join v_WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = du.WhsDocumentUc_id
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";

		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (!is_array($doc_data) || count($doc_data) < 1) {
			return array(array('Error_Msg' => 'Не удалось получить данные о документе учета.'));
		}

		//получение данных по строкам документа учета
		$query = "
			select
				dus.DocumentUcStr_id,
				dus.DocumentUcStr_Count,
				dor.DrugOstatRegistry_id,
				dor.DrugOstatRegistry_Kolvo,
				dor.Contragent_id,
				dor.Org_id,
				dor.Storage_id,
				dor.DrugShipment_id,
				dor.Drug_id,
				dor.PrepSeries_id,
				dor.SubAccountType_id,
				dor.Okei_id,
				dor.DrugOstatRegistry_Cost,
				d.Drug_Name,
				ps.PrepSeries_Ser,
				convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				ds.DrugShipment_Name,
				dus.StorageZone_id
			from
				v_DocumentUcStr dus with (nolock)
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
	            left join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
                outer apply (
                    select top 1
                        (
                            case
                                when
                                    isnull(isnds.YesNo_Code, 0) = 1
                                then
                                    isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)
                                else
                                    cast(isnull(isnull(dus2.DocumentUcStr_PriceR, dus2.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
                            end
                        ) as DocumentUcStr_Price
                    from
                        v_DocumentUcStr dus2 with (nolock)
                        left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus2.DocumentUcStr_IsNDS
                        left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus2.DrugNds_id
                    where
                        dus2.DocumentUcStr_id = dus.DocumentUcStr_id
                ) cost
				outer apply (
					select top 1
						i_dor.DrugOstatRegistry_id,
						i_dor.DrugOstatRegistry_Kolvo,
						i_dor.Contragent_id,
						i_dor.Org_id,
						i_dor.Storage_id,
						i_dor.DrugShipment_id,
						i_dor.Drug_id,
						i_dor.PrepSeries_id,
						i_dor.SubAccountType_id,
						i_dor.Okei_id,
						i_dor.DrugOstatRegistry_Cost
					from
						v_DrugOstatRegistry i_dor with (nolock)
					where
						i_dor.Drug_id = dus.Drug_id and
						i_dor.DrugShipment_id = dsl.DrugShipment_id and
                        i_dor.DrugOstatRegistry_Cost = cost.DocumentUcStr_Price and
                        i_dor.Org_id = c_sid.Org_id and
                        i_dor.Contragent_id = du.Contragent_sid and
                        (du.Storage_sid is null or i_dor.Storage_id = du.Storage_sid) and
                        isnull(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id) and
						i_dor.SubAccountType_id = 1
					order by
						i_dor.DrugOstatRegistry_id
				) dor
			where
				dus.DocumentUc_id = :DocumentUc_id
		";
		//var_dump(getDebugSQL($query, array('DocumentUc_id' => $data['DocumentUc_id']))); exit;

		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id'],
            'DefaultGoodsUnit_id' => $default_goods_unit_id
		));

		if (is_object($result)) {
			$drug_array = $result->result('array');
			foreach($drug_array as $drug) {
				if ( $drug['DrugOstatRegistry_Kolvo'] > 0 && $drug['DrugOstatRegistry_Kolvo'] >= $drug['DocumentUcStr_Count']) {
					//Исполнение - это в данном случае резервирование
                    $result = $this->moveToReserve(array(
                        'DrugOstatRegistry_id' => $drug['DrugOstatRegistry_id'],
                        'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
                        'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
					if (!empty($result['Error_Msg'])) {
						return array($result);
					}
				} else {
					return array(array('Error_Msg' => "Не может быть списан медикамент: {$drug['Drug_Name']}, {$drug['PrepSeries_Ser']}, {$drug['PrepSeries_GodnDate']}, № {$drug['DrugShipment_Name']} - {$drug['DocumentUcStr_Count']} недостаточно ЛП на остатках склада."));
				}
			}
		} else {
			return array(array('Error_Msg' => 'Не удалось получить данные о строках документа учета.'));
		}

		//меняем статус акта о списании если он указан в документе учета и его статус еще не равен "действующий"
		if ($doc_data['WhsDocumentUc_id'] > 0 && $doc_data['WhsDocumentStatusType_Code'] == 1) { //1 - Новый;
			$result = $this->saveObject('WhsDocumentUc', array(
				'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
				'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 2), //2 - Действующий
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array(array());
	}

	/**
	 * Корректировка регистра остатков при исполнении документа возврата укладки
	 */
	public function updateDrugOstatRegistryForDocVozrUk($data) {
		$drug_array = array();
		$doc_data = array();

		//получение данных по строкам документа учета
		$query = "
			select
				dus.DocumentUcStr_id,
				dus.DocumentUcStr_Count,
				du.Contragent_tid,
				du.Org_id as Org_tid,
				du.Storage_tid,
				dsl.DrugShipment_id,
				dus.Drug_id,
				dus.PrepSeries_id,
				dus.DocumentUcStr_PriceR as DocumentUcStr_Price,
				dus.StorageZone_id,
				dor.Okei_id,
				dor.GoodsUnit_id
			from
				v_DocumentUcStr dus with (nolock)
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
				left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				outer apply (
					select top 1
						i_dor.DrugOstatRegistry_id,
						i_dor.Okei_id,
						i_dor.GoodsUnit_id
					from
						v_DrugOstatRegistry i_dor with (nolock)
					where
						i_dor.Drug_id = dus.Drug_id
						and i_dor.DrugShipment_id = dsl.DrugShipment_id
						and i_dor.Org_id IS NOT NULL
						and i_dor.SubAccountType_id = 2
					order by
						i_dor.DrugOstatRegistry_id
				) dor
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (is_object($result)) {
			$drug_array = $result->result('array');
			foreach($drug_array as $drug) {
				$dor_data = $drug;
				$dor_data['pmUser_id'] = $data['pmUser_id'];
				$dor_data['DrugOstatRegistry_Kolvo'] = $drug['DocumentUcStr_Count'];
				$dor_data['DrugOstatRegistry_Sum'] = $drug['DocumentUcStr_Price']*$drug['DocumentUcStr_Count'];

				//начисление остатков
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_tid,
						@Org_id = :Org_tid,
						@Storage_id = :Storage_tid,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 1, -- субсчёт доступно
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DocumentUcStr_Price,
                        @InnerTransaction_Disabled = 1,
						@GoodsUnit_id = :GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->getFirstRowFromQuery($query, $dor_data);
				if (!empty($result['Error_Msg'])) {
					return array($result);
				}

				$dor_data['DrugOstatRegistry_Kolvo'] = $drug['DocumentUcStr_Count']*(-1);
				$dor_data['DrugOstatRegistry_Sum'] = $drug['DocumentUcStr_Price']*$drug['DocumentUcStr_Count']*(-1);

				//списание с резерва
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_tid,
						@Org_id = :Org_tid,
						@Storage_id = :Storage_tid,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 2, -- субсчёт Резерв
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DocumentUcStr_Price,
                        @InnerTransaction_Disabled = 1,
						@GoodsUnit_id = :GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->getFirstRowFromQuery($query, $dor_data);
				if (!empty($result['Error_Msg'])) {
					return array($result);
				}
			}
		} else {
			return array(array('Error_Msg' => 'Не удалось получить данные о строках документа учета.'));
		}

		return array(array());
	}

	/**
	 * Корректировка регистра остатков при исполнении документа ввода остатков
	 */
	function updateDrugOstatRegistryForDokOst($data) {
		$okei_id = $this->getObjectIdByCode('Okei', '778'); //778 - Упаковка

		//получение данных по строкам документа учета
		$query = "
			select
				dus.DocumentUcStr_Count,
				du.Contragent_tid,
				tcon.Org_id as Org_tid,
				du.Storage_tid,
				dsl.DrugShipment_id,
				dus.Drug_id,
				dus.PrepSeries_id,
				price.price_nds as Price,
				dus.StorageZone_id,
				dus.DocumentUcStr_id,
				dus.GoodsUnit_bid
			from
				v_DocumentUcStr dus with (nolock)
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
				left join v_Contragent tcon with (nolock) on tcon.Contragent_id = du.Contragent_tid
				left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				outer apply (
					select (
						case
							when isnull(isnds.YesNo_Code, 0) = 1 then dus.DocumentUcStr_Price
							else dus.DocumentUcStr_Price*(1+(isnull(dn.DrugNds_Code, 0)/100.0))
						end
					) as price_nds
				) price
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (is_object($result)) {
			$drug_array = $result->result('array');
			foreach($drug_array as $drug) {
				$dor_data = $drug;
				$dor_data['Okei_id'] = $okei_id;
				$dor_data['GoodsUnit_id'] = $drug['GoodsUnit_bid'];
				$dor_data['pmUser_id'] = $data['pmUser_id'];
				$dor_data['DrugOstatRegistry_Kolvo'] = $drug['DocumentUcStr_Count'];
				$dor_data['DrugOstatRegistry_Sum'] = $drug['Price']*$drug['DocumentUcStr_Count'];

				//начисление остатков
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_tid,
						@Org_id = :Org_tid,
						@Storage_id = :Storage_tid,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 1, -- субсчёт доступно
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :Price,
                        @InnerTransaction_Disabled = 1,
						@GoodsUnit_id = :GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->getFirstRowFromQuery($query, $dor_data);
				if (!empty($result['Error_Msg'])) {
					return array($result);
				}
				if(!empty($drug['StorageZone_id'])){
                	$result = $this->_updateDrugStorageZone(array(
                		'Drug_id' => $drug['Drug_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_id' => $drug['StorageZone_id'],
                		'DrugStorageZone_Price' => $drug['Price'],
                		'addDrugCount' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_id'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
	                $result = $this->_commitStorageDrugMove(array(
                		'Drug_id' => $drug['Drug_id'],
                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_oid' => null,
                		'StorageZone_nid' => $drug['StorageZone_id'],
                		'StorageDrugMove_Price' => $drug['Price'],
                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_id'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
                }
			}
		} else {
			return array(array('Error_Msg' => 'Не удалось получить данные о строках документа учета.'));
		}

		return array(array());
	}

	/**
	 * Получение данных о документе учёта
	 * @param type $data
	 */
	protected function _getDocumentUcData( $data ) {

		$rules = array(
			array( 'field' => 'DocumentUc_id' , 'label' => 'Идентификатор документа учета' , 'rules' => 'required' , 'type' => 'id' )
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false );
		if ( !$queryParams || !empty($err) )
			return $err ;

		$query = "
			select top 1
				du.DocumentUc_DogNum,
				du.WhsDocumentUc_id,
				du.DrugDocumentType_id,
				ddt.DrugDocumentType_Code,
				p_ddt.DrugDocumentType_Code as DrugDocumentType_pCode,
				du.Contragent_tid,
				c_tid.Org_id as Org_tid,
				c_sid.Org_id as Org_sid,
				du.Storage_tid,
				du.Storage_sid,
				du.DrugDocumentStatus_id
			from
				v_DocumentUc du with (nolock)
				left join v_DocumentUc p_du with (nolock) on p_du.DocumentUc_id = du.DocumentUc_pid
				inner join v_Contragent c_tid on c_tid.Contragent_id = du.Contragent_tid
				inner join v_Contragent c_sid on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
				left join v_DrugDocumentType p_ddt on p_ddt.DrugDocumentType_id = p_du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		" ;

		return $this->queryResult( $query , $queryParams );
	}

	/**
	 * Получение данных о договоре поставок
	 * @param type $data
	 */
	protected function _getWhsDocumentSupply($data) {

		$rules = array(
			array( 'field' => 'WhsDocumentUc_id' , 'label' => 'Идентификатор документа учета2' ,  'type' => 'id' , 'default' => null ),
			array( 'field' => 'WhsDocumentUc_Num' , 'label' => 'Номер договора' , 'type' => 'string', 'default' => null )
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false );
		if ( !$queryParams || !empty($err) )
			return $err ;

		$query = "
			select
				WhsDocumentSupply_id,
				WhsDocumentUc_Num,
				convert(varchar(10), WhsDocumentUc_Date, 104) as WhsDocumentUc_Date,
				Org_sid, --Поставщик
				Org_rid --Получатель
			from
				v_WhsDocumentSupply with (nolock)
			where
				WhsDocumentUc_id = :WhsDocumentUc_id or
				(:WhsDocumentUc_id is null and WhsDocumentUc_Num = :WhsDocumentUc_Num);
		";

		return $this->queryResult( $query , $queryParams );
	}

	/**
	* Получение данных учетного документа
	*/
	protected function _getDocumentUcStr($data) {

		$rules = array(
			array( 'field' => 'DocumentUc_id' , 'label' => 'Идентификатор документа учета' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'WhsDocumentSupply_id' , 'label' => 'Идентификатор договора поставок' , 'type' => 'id', 'default' => null ),
			array( 'field' => 'Org_sid' , 'label' => 'Поставщик' , 'type' => 'id', 'default' => null ),
			array( 'field' => 'Org_rid' , 'label' => 'Получатель' , 'type' => 'id', 'default' => null )
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false );
		if ( !$queryParams || !empty($err) )
			return $err ;

		$query = "
			select
				dus.Drug_id,
				dus.PrepSeries_id,
				d.Drug_Name,
				isnull(sup_spec.Okei_id, 120) as Okei_id,
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price,
				ds.DrugShipment_id,
				ds.DrugShipment_Name,
				(
				    o_ds.DrugShipment_Name+
				    isnull(' '+o_ds.AccountType_Name, '')
				) as DocumentUcStr_oName,
				(case when :WhsDocumentSupply_id is not null then :WhsDocumentSupply_id else wds.WhsDocumentSupply_id end) as WhsDocumentSupply_id,
				(case when :Org_sid is not null then :Org_sid else wds.Org_sid end) as Org_sid,
				(case when :Org_rid is not null then :Org_rid else wds.Org_rid end) as Org_rid,
				dus.DocumentUcStr_id,
				dus.DrugDocumentStatus_id,
				dds.DrugDocumentStatus_Code,
				dus.StorageZone_id,
				dus.GoodsUnit_bid,
				dus.GoodsUnit_id,
				dusw.DocumentUcStorageWork_id,
				dusw.DocumentUcStorageWork_endDate,
				isnull(dusw.DocumentUcStorageWork_FactQuantity, 0) as DocumentUcStorageWork_FactQuantity,
				dusw.DocumentUcStorageWork_Comment
			from
				v_DocumentUcStr dus with (nolock)
				left join rls.v_Drug d with(nolock) on d.Drug_id = dus.Drug_id
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = dus.DrugDocumentStatus_id
				outer apply (
					select top 1
						i_dsl.DrugShipment_id,
						i_ds.DrugShipment_Name,
						i_ds.WhsDocumentSupply_id
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_id
				) ds
				outer apply (
					select top 1
						i_dsh.DrugShipment_Name,
						i_at.AccountType_Name
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_dsh with (nolock) on i_dsh.DrugShipment_id = i_dsl.DrugShipment_id
						left join v_AccountType i_at with (nolock) on i_at.AccountType_id = i_dsh.AccountType_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					order by
						i_dsl.DrugShipmentLink_id
				) o_ds
				outer apply (
					select top 1
						WhsDocumentSupply_id,
						Org_sid,
						Org_rid
					from
						v_WhsDocumentSupply i_wds with (nolock)
					where
						i_wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
				) wds
				outer apply (
					select top 1
						wdss.Okei_id
					from
						v_WhsDocumentSupplySpec wdss with (nolock)
					where
						(
							(:WhsDocumentSupply_id is not null and wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id) or
							(wdss.WhsDocumentSupply_id is null and wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id)
						)
						and wdss.Drug_id = dus.Drug_id
				) sup_spec
				outer apply (
					select top 1
						dusw.*
					from
						v_DocumentUcStorageWork dusw with(nolock)
					where
						dusw.DocumentUcStr_id = dus.DocumentUcStr_id
					order by
						dusw.DocumentUcStorageWork_insDT desc
				) dusw
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";

		return $this->queryResult($query , $queryParams);

	}

	/**
	 * Получение данных об остатках медикаментов
	 * @param type $data
	 */
	protected function _getDrugOstatRegistryData($data) {
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

		$rules = array(
			array( 'field' => 'Org_id' , 'label' => 'Идентификатор организации' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'Drug_id' , 'label' => 'Идентификатор ЛС' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'WhsDocumentSupply_id' , 'label' => 'Идентификатор договора поставок' , 'type' => 'id', 'default' => null ),
			array( 'field' => 'PrepSeries_id' , 'label' => 'Идентификатор справочника серий выпуска ЛС' , 'type' => 'id', 'default' => null ),
			array( 'field' => 'DocumentUcStr_Price' , 'label' => 'Стоимость' , 'type' => 'float', 'default' => null ),
            array( 'field' => 'GoodsUnit_id' , 'label' => 'Ед. учета' , 'type' => 'id', 'default' => null )
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false );
		if ( !$queryParams || !empty($err) )
			return $err ;

        $queryParams['DefaultGoodsUnit_id'] = $default_goods_unit_id;

		$query = "
			select
				dor.Contragent_id,
				dor.Org_id,
				dor.Storage_id,
				dor.DrugShipment_id,
				dor.Drug_id,
				dor.PrepSeries_id,
				dor.Okei_id,
				dor.DrugOstatRegistry_Kolvo,
				dor.DrugOstatRegistry_Sum,
				dor.DrugOstatRegistry_Cost
			from
				v_DrugOstatRegistry dor with (nolock)
				left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
			where
				dor.Org_id = :Org_id and
				dor.Drug_id = :Drug_id and
				sat.SubAccountType_Code = 1 and
				dor.DrugOstatRegistry_Kolvo > 0 and
				(:WhsDocumentSupply_id is null or ds.WhsDocumentSupply_id = :WhsDocumentSupply_id) and
				(:PrepSeries_id is null or dor.PrepSeries_id = :PrepSeries_id) and
				(:DocumentUcStr_Price is null or dor.DrugOstatRegistry_Cost = :DocumentUcStr_Price) and
				isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);
		";

		return $this->queryResult($query , $queryParams);
	}

	/**
	* Метод сохранения изменений в остатках складов
	*/
	protected function _updateDrugOstatRegistry($data) {

		$rules = array(
			array( 'field' => 'Contragent_id' , 'label' => 'Идентификатор контрагента' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'Org_id' , 'label' => 'Идентификатор организации' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'Storage_id' , 'label' => 'Идентификатор склада' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'DrugShipment_id' , 'label' => 'Идентификатор партии поступления' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'Drug_id' , 'label' => 'Идентификатор медикамента' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'PrepSeries_id' , 'label' => 'идентификатор справочника серий выпуска ЛС' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'SubAccountType_id' , 'label' => 'Тип субсчета' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'Okei_id' , 'label' => 'Единицы измерения' , 'rules' => 'required' , 'type' => 'id' ),

			array( 'field' => 'DrugOstatRegistry_Kolvo' , 'label' => 'Количество упаковок на остатке' , 'rules' => 'required' , 'type' => 'float' ),
			array( 'field' => 'DrugOstatRegistry_Sum' , 'label' => 'Сумма' , 'rules' => 'required' , 'type' => 'float' ),
			array( 'field' => 'DrugOstatRegistry_Cost' , 'label' => 'Стоимость' , 'rules' => '' , 'default' => null , 'type' => 'float' ),
            array( 'field' => 'GoodsUnit_id' , 'label' => 'Единицы учета' , 'rules' => '' , 'type' => 'id' ),

			array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false );
		if ( !$queryParams || !empty($err) )
			return $err ;

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec xp_DrugOstatRegistry_count
				@Contragent_id = :Contragent_id,
				@Org_id = :Org_id,
				@Storage_id = :Storage_id,
				@DrugShipment_id = :DrugShipment_id,
				@Drug_id = :Drug_id,
				@PrepSeries_id = :PrepSeries_id,
				@SubAccountType_id = :SubAccountType_id,
				@Okei_id = :Okei_id,
				@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
				@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
				@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
                @InnerTransaction_Disabled = 1,
				@GoodsUnit_id = :GoodsUnit_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query,$queryParams);
	}

	/**
	* Метод сохранения перемещений в остатках складов
	*/
	protected function _commitStorageDrugMove($data) {
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

		$rules = array(
			array( 'field' => 'DrugShipment_id' , 'label' => 'Идентификатор партии поступления' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'Drug_id' , 'label' => 'Идентификатор медикамента' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'DocumentUcStr_id' , 'label' => 'Идентификатор строки документа' , 'rules' => '' , 'type' => 'id' ),
			array( 'field' => 'PrepSeries_id' , 'label' => 'идентификатор справочника серий выпуска ЛС' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'StorageZone_oid' , 'label' => 'идентификатор исходного место хранения' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'StorageZone_nid' , 'label' => 'идентификатор конечного место хранения' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'StorageDrugMove_Price' , 'label' => 'Цена медикамента' , 'rules' => '' , 'default' => null , 'type' => 'string' ),
			array( 'field' => 'StorageDrugMove_Count' , 'label' => 'Количество медикамента' , 'rules' => '' , 'default' => null , 'type' => 'string' ),
            array( 'field' => 'GoodsUnit_id' , 'label' => 'Ед. учета' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id')
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false );
		if ( !$queryParams || !empty($err) ) {
			return $err;
		}
		$queryParams['StorageDrugMove_setDate'] = date('Y-m-d');
		$queryParams['DefaultGoodsUnit_id'] = $default_goods_unit_id;

		$query = "
			declare
				@StorageDrugMove_id bigint,
				@GoodsUnit_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @StorageDrugMove_id = null;
			set @GoodsUnit_id = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);

			exec dbo.p_StorageDrugMove_ins
				@StorageDrugMove_id = @StorageDrugMove_id output,
				@StorageDrugMove_setDate = :StorageDrugMove_setDate,
				@Drug_id = :Drug_id,
				@DocumentUcStr_id = :DocumentUcStr_id,
				@PrepSeries_id = :PrepSeries_id,
				@StorageDrugMove_Price = :StorageDrugMove_Price,
				@StorageDrugMove_Count = :StorageDrugMove_Count,
				@DrugShipment_id = :DrugShipment_id,
				@StorageZone_oid = :StorageZone_oid,
				@StorageZone_nid = :StorageZone_nid,
				@GoodsUnit_id = @GoodsUnit_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @StorageDrugMove_id as StorageDrugMove_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSql($query, $queryParams); exit();
		return $this->queryResult($query,$queryParams);
	}

	/**
	* Метод сохранения мест размещения медикаментов в остатках складов
	*/
	protected function _updateDrugStorageZone($data) {
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

		$init_rules = array(
			array( 'field' => 'Drug_id' , 'label' => 'Идентификатор медикамента' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'StorageZone_id' , 'label' => 'Идентификатор места хранения' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'DrugShipment_id' , 'label' => 'Идентификатор партии поступления' , 'rules' => '' ,'default' => null , 'type' => 'id' ),
			array( 'field' => 'DrugStorageZone_Price' , 'label' => 'Цена медикамента' , 'rules' => '','default' => null  , 'type' => 'id' ),
			array( 'field' => 'PrepSeries_id' , 'label' => 'идентификатор справочника серий выпуска ЛС' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'GoodsUnit_id' , 'label' => 'Ед. учета' , 'rules' => '' , 'default' => null , 'type' => 'id' )
		);

		$queryParams = $this->_checkInputData( $init_rules , $data , $err , false );
		if ( !$queryParams || !empty($err) ) {
			return $err;
		}
        $queryParams['DefaultGoodsUnit_id'] = $default_goods_unit_id;

		$query = "
			select top 1
				DrugStorageZone_id,
				DrugStorageZone_Count
			from
				v_DrugStorageZone with (nolock)
			where
				Drug_id = :Drug_id and
				StorageZone_id = :StorageZone_id and
				isnull(PrepSeries_id,0) = isnull(:PrepSeries_id,0) and
				isnull(DrugShipment_id,0) = isnull(:DrugShipment_id,0) and
                isnull(GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id)
				/*and isnull(DrugStorageZone_Price,0) = isnull(:DrugStorageZone_Price,0)*/
		";
        $drug_storage_zone = $this->queryResult($query, $queryParams);
        if(empty($drug_storage_zone[0]['DrugStorageZone_id'])){

        	if(!empty($data['removeDrugCount'])){
        		return array(array('Error_Msg'=>'Ошибка - не найдено место хранения с которого нужно списать медикамент'));
        	}
        	if(empty($data['addDrugCount'])){
        		return array(array('Error_Msg'=>'Ошибка - не указано количество медикамента для зачисления на место хранения'));
        	}

        	$procedure = 'ins';
        	$data['DrugStorageZone_id'] = null;
        	$data['DrugStorageZone_Count'] = $data['addDrugCount'];
        } else {

        	$procedure = 'upd';
        	$data['DrugStorageZone_id'] = $drug_storage_zone[0]['DrugStorageZone_id'];

        	if(!empty($data['addDrugCount'])){

        		$data['DrugStorageZone_Count'] = ($drug_storage_zone[0]['DrugStorageZone_Count'] + $data['addDrugCount']);

        	} else if(!empty($data['removeDrugCount'])){

        		if($drug_storage_zone[0]['DrugStorageZone_Count'] < $data['removeDrugCount']){
	        		return array(array('Error_Msg'=>'Ошибка - недостаточно медикамента для списания с места хранения'));
	        	}

        		$data['DrugStorageZone_Count'] = ($drug_storage_zone[0]['DrugStorageZone_Count'] - $data['removeDrugCount']);

        	} else {
        		return array(array('Error_Msg'=>'Ошибка - не указано количество медикамента'));
        	}
        }

		$rules = array(
			array( 'field' => 'DrugStorageZone_id' , 'label' => 'Идентификатор места хранения медикамента' , 'rules' => '' , 'type' => 'id' ),
			array( 'field' => 'DrugShipment_id' , 'label' => 'Идентификатор партии поступления' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'Drug_id' , 'label' => 'Идентификатор медикамента' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'PrepSeries_id' , 'label' => 'идентификатор справочника серий выпуска ЛС' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'StorageZone_id' , 'label' => 'идентификатор места хранения' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'DrugStorageZone_Price' , 'label' => 'Цена медикамента' , 'rules' => '' , 'default' => null , 'type' => 'string' ),
			array( 'field' => 'DrugStorageZone_Count' , 'label' => 'Количество медикамента' , 'rules' => '' , 'default' => null , 'type' => 'float' ),
            array( 'field' => 'GoodsUnit_id' , 'label' => 'Ед. учета' , 'rules' => '' , 'default' => null , 'type' => 'id' ),
			array( 'field' => 'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id')
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false );
		if ( !$queryParams || !empty($err) ) {
			return $err;
		}
        $queryParams['DefaultGoodsUnit_id'] = $default_goods_unit_id;

		if($procedure == 'upd' && $queryParams['DrugStorageZone_Count'] == 0){
			$query = "
				declare
					@DrugStorageZone_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @DrugStorageZone_id = :DrugStorageZone_id;
				exec dbo.p_DrugStorageZone_del
					@DrugStorageZone_id = @DrugStorageZone_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		} else {
			$query = "
				declare
					@DrugStorageZone_id bigint,
					@GoodsUnit_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @DrugStorageZone_id = :DrugStorageZone_id;
				set @GoodsUnit_id = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);

				exec dbo.p_DrugStorageZone_".$procedure."
					@DrugStorageZone_id = @DrugStorageZone_id output,
					@StorageZone_id = :StorageZone_id,
					@Drug_id = :Drug_id,
					@PrepSeries_id = :PrepSeries_id,
					@DrugShipment_id = :DrugShipment_id,
					@DrugStorageZone_Price = :DrugStorageZone_Price,
					@DrugStorageZone_Count = :DrugStorageZone_Count,
					@GoodsUnit_id = @GoodsUnit_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @DrugStorageZone_id as DrugStorageZone_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		}
		//echo getDebugSql($query, $queryParams); exit();
		return $this->queryResult($query,$queryParams);
	}

	/**
	 * Проверка наряда для исполнения строки документа
	 */
	function _checkDrugStorageWork($drug) {
		if (empty($drug['DocumentUcStorageWork_id'])) {
			return true;
		}
		return (
			!empty($drug['DocumentUcStorageWork_endDate']) &&
			empty($drug['DocumentUcStorageWork_Comment']) &&
			$drug['DocumentUcStorageWork_FactQuantity'] > 0 &&
			$drug['DocumentUcStorageWork_FactQuantity'] == $drug['DocumentUcStr_Count']
		);
	}

	/**
	 * Проверка нарядов для исполнения документа
	 */
	function checkDocumentStorageWork($data) {
		$drug_list = $this->_getDocumentUcStr($data);
		if (!is_array($drug_list)) {
			return $this->createError(0, 'Ошибка при получении строк документа');
		}
		foreach($drug_list as $drug) {
			if (!$this->_checkDrugStorageWork($drug)) {
				$shipment = !empty($drug['DrugShipment_Name'])?$drug['DrugShipment_Name']:(!empty($drug['DocumentUcStr_oName'])?$drug['DocumentUcStr_oName']:'не указана');
				return $this->createError(0, "Не возможно исполнить строку с медикоментом: {$drug['Drug_Name']}, партия: {$shipment}, цена: {$drug['DocumentUcStr_Price']}");
			}
		}
		return array(array('success' => true));
	}

	/**
	 * Получение данных о документе учёта
	 * @param type $data
	 */
	protected function _getDocumentUcDataLite( $data ) {

		$rules = array(
			array( 'field' => 'DocumentUc_id' , 'label' => 'Идентификатор документа учета' , 'rules' => 'required' , 'type' => 'id' )
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false );
		if ( !$queryParams || !empty($err) )
			return $err ;

		$query = "
			select top 1
				du.DocumentUc_id,
				du.DocumentUc_DogNum,
				du.WhsDocumentUc_id,
				du.DrugDocumentType_id,
				ddt.DrugDocumentType_Code,
				p_ddt.DrugDocumentType_Code as DrugDocumentType_pCode,
				du.Contragent_tid,
				c_tid.Org_id as Org_tid,
				c_sid.Org_id as Org_sid,
				du.Storage_tid,
				du.Storage_sid,
				du.DrugDocumentStatus_id
			from
				v_DocumentUc du with (nolock)
				left join v_DocumentUc p_du with (nolock) on p_du.DocumentUc_id = du.DocumentUc_pid
				left join v_Contragent c_tid on c_tid.Contragent_id = du.Contragent_tid
				left join v_Contragent c_sid on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
				left join v_DrugDocumentType p_ddt on p_ddt.DrugDocumentType_id = p_du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		" ;

		return $this->queryResult( $query , $queryParams );
	}

	/**
	 * Корректировка регистра остатков при исполнении приходной накладной
	 */
	public function updateDrugOstatRegistryForDokNak($data) {
        $suppliers_ostat_control = !empty($data['options']['drugcontrol']['suppliers_ostat_control']);
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//Получаем данные о документе учета
		$doc_data = $this->_getDocumentUcDataLite($data);
		if (count($doc_data) == 0 || empty($doc_data[0])) {
			return $this->createError(null, 'Документа учета не найден');
		}
		$doc_data = $doc_data[0];

        if (empty($doc_data['Contragent_tid'])) {
            return $this->createError(null, 'Для документа не определен получатель');
        }

		//Получаем данные о гк
		$sup_data = $this->_getWhsDocumentSupply(array(
			'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
			'WhsDocumentUc_Num' => $doc_data['DocumentUc_DogNum']
		));
		$sup_data = (!empty($sup_data[0])) ? $sup_data[0] : array();

		// Получаем строки документа учета
		$drug_arr = $this->_getDocumentUcStr(array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'WhsDocumentSupply_id' => isset( $sup_data['WhsDocumentSupply_id'] ) ? $sup_data['WhsDocumentSupply_id'] : null ,
			'Org_sid' => isset($sup_data['Org_sid']) ? $sup_data['Org_sid'] : $doc_data['Org_sid'],
			'Org_rid' => isset($sup_data['Org_rid']) ? $sup_data['Org_rid'] : null
		));
		if (count($drug_arr) == 0) {
			return $this->createError(null, 'Список медикаментов пуст');
		}

		foreach ($drug_arr as $drug) {
			// Исполнение только неисполненных ранее строк
			if (empty($drug['DrugDocumentStatus_Code']) || $drug['DrugDocumentStatus_Code'] != 4){
				if (!$this->_checkDrugStorageWork($drug)) {
					continue;
				}

				//проверяем наличие получателя по документу в списке пунктов отпуска
	            if ($suppliers_ostat_control && (!isset($title_doc_cnt) || count($sup_data) < 1)) {
	                $query = "
						select
							count(wdt.WhsDocumentTitle_id) as cnt
						from
							v_WhsDocumentTitle wdt with (nolock)
							left join v_WhsDocumentTitleType wdtt with (nolock) on wdtt.WhsDocumentTitleType_id = wdt.WhsDocumentTitleType_id
							left join v_WhsDocumentRightRecipient wdrr with (nolock) on wdrr.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
						where
							wdt.WhsDocumentUc_id = :WhsDocumentSupply_id and
							wdtt.WhsDocumentTitleType_Code = 3 and --Приложение к ГК: список пунктов отпуска
							Org_id = :Org_id;
					" ;
	                $title_doc_cnt = $this->getFirstResultFromQuery($query, array(
	                    'Org_id' => $doc_data['Org_tid'],
	                    'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id']
	                ));
	            }

	            if ($suppliers_ostat_control && $doc_data['DrugDocumentType_pCode'] != '10' && ((isset( $title_doc_cnt ) && $title_doc_cnt > 0) || $doc_data['Org_tid'] == $drug['Org_rid'])) { //проверяем является ли получатель по документу - грузополучателем по ГК (проверка отключена для документов созданых на основе расходных накладных)
	                //ищем нужные записи в регистре и проверяем наличие необходимого количества медикамента
                    //в некоторых случаях при отстутсвии на остатках оригинальных медикаментов, допустимо списание с остатков синонимов (указанных в ГК)

                    $is_synonym = false; //признак того, что медикамент из документа учета является синонимом
                    $synonym_koef = 1; //коээфицент отношения количества синонима к количеству оригинала

	                $remove_drug_id = $drug['Drug_id']; //медикамент для списания
	                $remove_kolvo = $drug['DocumentUcStr_Count']; //количество для списания
	                $remove_price = $drug['DocumentUcStr_Price']; //цена медикамента для списания

                    $add_drug_id = $remove_drug_id; //медикамент для начисления
	                $add_kolvo = $remove_kolvo; //количество для начисления
	                $add_price = $remove_price; //цена медикамента для начисления

                    //собираем массив остатков подходящих для списания медикамента из накладной
	                $ostat_array = $this->_getDrugOstatRegistryData(array(
	                    'Org_id' => $drug['Org_sid'],
	                    'Drug_id' => $remove_drug_id,
	                    'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
	                    'PrepSeries_id' => $doc_data['Org_sid'] != $drug['Org_sid'] ? $drug['PrepSeries_id'] : null, //серия при списании учитывается только если поставщик из документа учета не является поставщиком по госконтракту
	                    'DocumentUcStr_Price' => $remove_price,
                        'GoodsUnit_id' => $drug['GoodsUnit_bid']
	                ));

                    //если остатки для списания не найдены, проверяем не является ли списываемый медикамент синонимом из ГК
                    if (count($ostat_array) == 0) {
                        //поиск оригинального медикамента в спике синонимов ГК
                        $query = "
                            select top 1
                                wdssd.WhsDocumentSupplySpecDrug_Price,
                                wdssd.WhsDocumentSupplySpecDrug_Coeff,
                                wdssd.Drug_id,
                                wdssd.WhsDocumentSupplySpecDrug_PriceSyn
                            from
                                v_WhsDocumentSupplySpec wdss with (nolock)
                                inner join v_WhsDocumentSupplySpecDrug wdssd with (nolock) on wdssd.WhsDocumentSupplySpec_id = wdss.WhsDocumentSupplySpec_id
                            where
                                wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id and
                                wdssd.Drug_sid = :Drug_sid
                            order by
                                wdssd.WhsDocumentSupplySpecDrug_id
                        ";
                        $synonym = $this->getFirstRowFromQuery($query, array(
                            'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                            'Drug_sid' => $remove_drug_id
                        ));

                        if(!empty($synonym['Drug_id'])) {
                            $is_synonym = true;
                            $remove_drug_id = $synonym['Drug_id'];
                            $remove_price = $synonym['WhsDocumentSupplySpecDrug_Price'];
                            if (!empty($synonym['WhsDocumentSupplySpecDrug_Coeff'])) { //если коэффицент не указан, считаем что он равен 1 и пересчет не требуется
                                $synonym_koef = $synonym['WhsDocumentSupplySpecDrug_Coeff'];
                                $remove_kolvo = $remove_kolvo/$synonym_koef;
                            }

                            //ищем в остатках оригинальный медикамент из ГК
                            $ostat_array = $this->_getDrugOstatRegistryData(array(
                                'Org_id' => $drug['Org_sid'],
                                'Drug_id' => $remove_drug_id,
                                'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                                'PrepSeries_id' => $doc_data['Org_sid'] != $drug['Org_sid'] ? $drug['PrepSeries_id'] : null, //серия при списании учитывается только если поставщик из документа учета не является поставщиком по госконтракту
                                'DocumentUcStr_Price' => $remove_price,
                                'GoodsUnit_id' => $drug['GoodsUnit_bid']
                            ));
                        }
                    }

	                if (count($ostat_array) == 0) {
	                	//просто пропускаем строку, не исполняя
	                	continue;
	                    //return $this->createError(0, 'На остатках поставщика недостаточно медикаментов для списания');
	                }

	                //проверка количества на остатках для списания
                    $kolvo = $remove_kolvo;
	                foreach ($ostat_array as $ostat) {
	                    if ($kolvo > 0) {
	                        $kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
	                        $kolvo -= $kol;
	                    }
	                }
	                if ($kolvo > 0) {
	                	//просто пропускаем строку, не исполняя
	                	continue;
	                    //return $this->createError(0, 'На остатках поставщика недостаточно медикаментов для списания.');
	                }

                    //редактирование регистра остатков
                    $kolvo = $remove_kolvo;
	                foreach ($ostat_array as $ostat) {
	                    if ($kolvo > 0) {
	                        //списание из остатков
	                        $remove_kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
                            $remove_sum = $remove_kol * $remove_price;

	                        $kolvo -= $remove_kol;

	                        $q_params = array(
	                            'Contragent_id' => $ostat['Contragent_id'],
	                            'Org_id' => $drug['Org_sid'],
	                            'Storage_id' => $ostat['Storage_id'],
	                            'DrugShipment_id' => $ostat['DrugShipment_id'],
	                            'Drug_id' => $ostat['Drug_id'],
	                            'PrepSeries_id' => $ostat['PrepSeries_id'],
	                            'SubAccountType_id' => 1, // -- субсчёт доступно
	                            'Okei_id' => $ostat['Okei_id'],
	                            'DrugOstatRegistry_Kolvo' => $remove_kol * (-1),
	                            'DrugOstatRegistry_Sum' => $remove_sum * (-1),
	                            'DrugOstatRegistry_Cost' => $remove_price,
                                'GoodsUnit_id' => $ostat['GoodsUnit_id'],
	                            'pmUser_id' => $data['pmUser_id']
	                        );

	                        $result = $this->_updateDrugOstatRegistry($q_params);
	                        if (!$this->isSuccessful($result)) {
	                            return $this->createError(0, 'Ошибка списания остатков');
	                        }

	                        //зачисление на остатки
                            $add_kol = $is_synonym ? $remove_kol*$synonym_koef : $remove_kol;
                            $add_sum = $add_kol * $add_price;

	                        $q_params['Contragent_id'] = $doc_data['Contragent_tid'];
	                        $q_params['Drug_id'] = $add_drug_id;
	                        $q_params['PrepSeries_id'] = $drug['PrepSeries_id'];
	                        $q_params['Org_id'] = $doc_data['Org_tid'];
	                        $q_params['Storage_id'] = $doc_data['Storage_tid'];
	                        $q_params['DrugOstatRegistry_Kolvo'] = $add_kol;
	                        $q_params['DrugOstatRegistry_Sum'] = $add_sum;
	                        $q_params['DrugOstatRegistry_Cost'] = $add_price;
	                        $q_params['GoodsUnit_id'] = $drug['GoodsUnit_bid'];
	                        $q_params['DrugShipment_id'] = $drug['DrugShipment_id'];

	                        $result = $this->_updateDrugOstatRegistry($q_params);
	                        if (!$this->isSuccessful($result)) {
	                            return $this->createError(0, 'Ошибка зачисления остатков');
	                        }

	                        if(!empty($drug['StorageZone_id'])){
                                $q_params['Drug_id'] = $add_drug_id;
	                        	$q_params['StorageZone_id'] = $drug['StorageZone_id'];
	                        	$q_params['DrugStorageZone_Price'] = $add_price;
	                        	$q_params['addDrugCount'] = $add_kolvo;
                                $q_params['GoodsUnit_id'] = $drug['GoodsUnit_bid'];
			                	$result = $this->_updateDrugStorageZone($q_params);
			                	if (!$this->isSuccessful($result)) {
				                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
				                }
				                $q_params['StorageZone_nid'] = $drug['StorageZone_id'];
				                $q_params['StorageZone_oid'] = null;
				                $q_params['StorageDrugMove_Price'] = $add_price;
	                        	$q_params['StorageDrugMove_Count'] = $add_kol;
	                        	$q_params['DocumentUcStr_id'] = $drug['DocumentUcStr_id'];
				                $result = $this->_commitStorageDrugMove($q_params);
			                	if (!$this->isSuccessful($result)) {
				                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
				                }
			                }

	                        //Проверяем на наличие на субсчете В пути
			                $queryParams = array(
			                    'Contragent_id' => $doc_data['Contragent_tid'],
			                    'Org_id' => $doc_data['Org_tid'],
			                    'Storage_id' => $doc_data['Storage_tid'],
			                    'DrugShipment_id' => $drug['DrugShipment_id'],
			                    'Drug_id' => $remove_drug_id,
			                    'PrepSeries_id' => $drug['PrepSeries_id'],
			                    'DrugOstatRegistry_Kolvo' => $remove_kolvo,
			                    'DrugOstatRegistry_Cost' => $remove_price,
			                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                                'DefaultGoodsUnit_id' => $default_goods_unit_id
			                );
			                $query = "
								select
									dor.DrugOstatRegistry_id
								from
									v_DrugOstatRegistry dor with (nolock)
									left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
								where
									dor.Org_id = :Org_id and
									dor.Drug_id = :Drug_id and
									dor.DrugShipment_id = :DrugShipment_id and
									dor.Contragent_id = :Contragent_id and
									dor.Storage_id = :Storage_id and
									sat.SubAccountType_Code = 3 and
									dor.DrugOstatRegistry_Kolvo >= :DrugOstatRegistry_Kolvo and
									dor.PrepSeries_id = :PrepSeries_id and
									dor.DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
									isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);
							";

							$result = $this->queryResult($query,$queryParams);
							if(is_array($result) && count($result)>0){
		                        //Списание с субсчета В пути
		                        $q_params['SubAccountType_id'] = 3;
		                        $q_params['DrugOstatRegistry_Kolvo'] = $remove_kol * (-1);
		                        $q_params['DrugOstatRegistry_Sum'] = $remove_sum * (-1);
		                        $result = $this->_updateDrugOstatRegistry($q_params);
		                        if (!$this->isSuccessful($result)) {
		                            return $this->createError(0, 'Ошибка списания остатков с субсчета В пути');
		                        }
		                    }
	                    }
	                }

	                $result = $this->saveObject('DocumentUcStr', array(
						'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
						'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 4), //4 - Исполнен
						'pmUser_id' => $data['pmUser_id']
					));

					if (!empty($result['Error_Msg'])) {
	                	return $this->createError(0, 'Ошибка при смене статуса строки документа.');
	                }
	            } else {
	                //создаем записи в регистре
	                $result = $this->_updateDrugOstatRegistry(array(
	                    'Contragent_id' => $doc_data['Contragent_tid'],
	                    'Org_id' => $doc_data['Org_tid'],
	                    'Storage_id' => $doc_data['Storage_tid'],
	                    'DrugShipment_id' => $drug['DrugShipment_id'],
	                    'Drug_id' => $drug['Drug_id'],
	                    'SubAccountType_id' => 1, // -- субсчёт доступно
	                    'PrepSeries_id' => $drug['PrepSeries_id'],
	                    'Okei_id' => $drug['Okei_id'],
	                    'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
	                    'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price'] * $drug['DocumentUcStr_Count'],
	                    'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
	                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                    'pmUser_id' => $data['pmUser_id']
	                ));
	                if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка создания регистра остатков');
	                }

	                if(!empty($drug['StorageZone_id'])){
	                	$result = $this->_updateDrugStorageZone(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_id' => $drug['StorageZone_id'],
	                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
	                		'addDrugCount' => $drug['DocumentUcStr_Count'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
		                $result = $this->_commitStorageDrugMove(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_oid' => null,
	                		'StorageZone_nid' => $drug['StorageZone_id'],
	                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
	                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
	                }

	                //Проверяем на наличие на субсчете В пути
	                $queryParams = array(
	                    'Contragent_id' => $doc_data['Contragent_tid'],
	                    'Org_id' => $doc_data['Org_tid'],
	                    'Storage_id' => $doc_data['Storage_tid'],
	                    'DrugShipment_id' => $drug['DrugShipment_id'],
	                    'Drug_id' => $drug['Drug_id'],
	                    'PrepSeries_id' => $drug['PrepSeries_id'],
	                    'DrugOstatRegistry_Kolvo' => ($drug['DocumentUcStr_Count']),
	                    'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
	                    'GoodsUnit_id' => $drug['GoodsUnit_id'],
                        'DefaultGoodsUnit_id' => $default_goods_unit_id
	                );
	                $query = "
						select
							dor.DrugOstatRegistry_id,
							dor.Storage_id
						from
							v_DrugOstatRegistry dor with (nolock)
							left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						where
							dor.Org_id = :Org_id and
							dor.Drug_id = :Drug_id and
							dor.DrugShipment_id = :DrugShipment_id and
							dor.Contragent_id = :Contragent_id and
							(dor.Storage_id = :Storage_id or dor.Storage_id is null) and
							sat.SubAccountType_Code = 3 and
							dor.DrugOstatRegistry_Kolvo >= :DrugOstatRegistry_Kolvo and
							dor.PrepSeries_id = :PrepSeries_id and
							dor.DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
							isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);
					";

					$result = $this->queryResult($query , $queryParams);

					if(is_array($result) && count($result)>0){
						//списываем с субсчета В пути
		                $result = $this->_updateDrugOstatRegistry(array(
		                    'Contragent_id' => $doc_data['Contragent_tid'],
		                    'Org_id' => $doc_data['Org_tid'],
		                    'Storage_id' => $result[0]['Storage_id'],
		                    'DrugShipment_id' => $drug['DrugShipment_id'],
		                    'Drug_id' => $drug['Drug_id'],
		                    'SubAccountType_id' => 3, // -- субсчёт В пути
		                    'PrepSeries_id' => $drug['PrepSeries_id'],
		                    'Okei_id' => $drug['Okei_id'],
		                    'DrugOstatRegistry_Kolvo' => ($drug['DocumentUcStr_Count'] * (-1)),
		                    'DrugOstatRegistry_Sum' => ($drug['DocumentUcStr_Price'] * $drug['DocumentUcStr_Count'] * (-1)),
		                    'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
		                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
		                    'pmUser_id' => $data['pmUser_id']
		                ));
		                if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при списании с субсчета В пути');
		                }
					}

	                $result = $this->saveObject('DocumentUcStr', array(
						'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
						'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 4), //4 - Исполнен
						'pmUser_id' => $data['pmUser_id']
					));

					if (!empty($result['Error_Msg'])) {
	                	return $this->createError(0, 'Ошибка при смене статуса строки документа.');
	                }
	            }
	        }
		}

		return array(array());
	}

	/**
	 * Корректировка регистра остатков при документа с типом "Приход в отделение"
	 */
	public function updateDrugOstatRegistryForDokNakVO($data) {
        $suppliers_ostat_control = !empty($data['options']['drugcontrol']['suppliers_ostat_control']);
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//Получаем данные о документе учета
		$doc_data = $this->_getDocumentUcDataLite($data);
		if (count($doc_data) == 0 || empty($doc_data[0])) {
			return $this->createError(null, 'Документа учета не найден');
		}
		$doc_data = $doc_data[0];

		//Получаем данные о гк
		$sup_data = $this->_getWhsDocumentSupply(array(
			'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
			'WhsDocumentUc_Num' => $doc_data['DocumentUc_DogNum']
		));
		$sup_data = (!empty($sup_data[0])) ? $sup_data[0] : array();

		// Получаем строки документа учета
		$drug_arr = $this->_getDocumentUcStr(array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'WhsDocumentSupply_id' => isset( $sup_data['WhsDocumentSupply_id'] ) ? $sup_data['WhsDocumentSupply_id'] : null ,
			'Org_sid' => isset($sup_data['Org_sid']) ? $sup_data['Org_sid'] : $doc_data['Org_sid'],
			'Org_rid' => isset($sup_data['Org_rid']) ? $sup_data['Org_rid'] : null
		));
		if (count($drug_arr) == 0) {
			return $this->createError(null, 'Список медикаментов пуст');
		}

		foreach ($drug_arr as $drug) {
			// Исполнение только неисполненных ранее строк
			if (empty($drug['DrugDocumentStatus_Code']) || $drug['DrugDocumentStatus_Code'] != 4){
				if (!$this->_checkDrugStorageWork($drug)) {
					continue;
				}

				//проверяем наличие получателя по документу в списке пунктов отпуска
	            if ($suppliers_ostat_control && (!isset($title_doc_cnt) || count($sup_data) < 1)) {
	                $query = "
						select
							count(wdt.WhsDocumentTitle_id) as cnt
						from
							v_WhsDocumentTitle wdt with (nolock)
							left join v_WhsDocumentTitleType wdtt with (nolock) on wdtt.WhsDocumentTitleType_id = wdt.WhsDocumentTitleType_id
							left join v_WhsDocumentRightRecipient wdrr with (nolock) on wdrr.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
						where
							wdt.WhsDocumentUc_id = :WhsDocumentSupply_id and
							wdtt.WhsDocumentTitleType_Code = 3 and --Приложение к ГК: список пунктов отпуска
							Org_id = :Org_id;
					" ;
	                $title_doc_cnt = $this->getFirstResultFromQuery($query, array(
	                    'Org_id' => $doc_data['Org_tid'],
	                    'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id']
	                ));
	            }

	            if ($suppliers_ostat_control && $doc_data['DrugDocumentType_pCode'] != '10' && ((isset( $title_doc_cnt ) && $title_doc_cnt > 0) || $doc_data['Org_tid'] == $drug['Org_rid'])) { //проверяем является ли получатель по документу - грузополучателем по ГК (проверка отключена для документов созданых на основе расходных накладных)
	                //ищем нужные записи в регистре и проверяем наличие необходимого количества медикамента
                    //в некоторых случаях при отстутсвии на остатках оригинальных медикаментов, допустимо списание с остатков синонимов (указанных в ГК)

                    $is_synonym = false; //признак того, что медикамент из документа учета является синонимом
                    $synonym_koef = 1; //коээфицент отношения количества синонима к количеству оригинала

	                $remove_drug_id = $drug['Drug_id']; //медикамент для списания
	                $remove_kolvo = $drug['DocumentUcStr_Count']; //количество для списания
	                $remove_price = $drug['DocumentUcStr_Price']; //цена медикамента для списания

                    $add_drug_id = $remove_drug_id; //медикамент для начисления
	                $add_kolvo = $remove_kolvo; //количество для начисления
	                $add_price = $remove_price; //цена медикамента для начисления

                    //собираем массив остатков подходящих для списания медикамента из накладной
	                $ostat_array = $this->_getDrugOstatRegistryData(array(
	                    'Org_id' => $drug['Org_sid'],
	                    'Drug_id' => $remove_drug_id,
	                    'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
	                    'PrepSeries_id' => $doc_data['Org_sid'] != $drug['Org_sid'] ? $drug['PrepSeries_id'] : null, //серия при списании учитывается только если поставщик из документа учета не является поставщиком по госконтракту
	                    'DocumentUcStr_Price' => $remove_price
	                ));

                    //если остатки для списания не найдены, проверяем не является ли списываемый медикамент синонимом из ГК
                    if (count($ostat_array) == 0) {
                        //поиск оригинального медикамента в спике синонимов ГК
                        $query = "
                            select top 1
                                wdssd.WhsDocumentSupplySpecDrug_Price,
                                wdssd.WhsDocumentSupplySpecDrug_Coeff,
                                wdssd.Drug_id,
                                wdssd.WhsDocumentSupplySpecDrug_PriceSyn
                            from
                                v_WhsDocumentSupplySpec wdss with (nolock)
                                inner join v_WhsDocumentSupplySpecDrug wdssd with (nolock) on wdssd.WhsDocumentSupplySpec_id = wdss.WhsDocumentSupplySpec_id
                            where
                                wdss.WhsDocumentSupply_id = :WhsDocumentSupply_id and
                                wdssd.Drug_sid = :Drug_sid
                            order by
                                wdssd.WhsDocumentSupplySpecDrug_id
                        ";
                        $synonym = $this->getFirstRowFromQuery($query, array(
                            'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                            'Drug_sid' => $remove_drug_id
                        ));

                        if(!empty($synonym['Drug_id'])) {
                            $is_synonym = true;
                            $remove_drug_id = $synonym['Drug_id'];
                            $remove_price = $synonym['WhsDocumentSupplySpecDrug_Price'];
                            if (!empty($synonym['WhsDocumentSupplySpecDrug_Coeff'])) { //если коэффицент не указан, считаем что он равен 1 и пересчет не требуется
                                $synonym_koef = $synonym['WhsDocumentSupplySpecDrug_Coeff'];
                                $remove_kolvo = $remove_kolvo/$synonym_koef;
                            }

                            //ищем в остатках оригинальный медикамент из ГК
                            $ostat_array = $this->_getDrugOstatRegistryData(array(
                                'Org_id' => $drug['Org_sid'],
                                'Drug_id' => $remove_drug_id,
                                'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                                'PrepSeries_id' => $doc_data['Org_sid'] != $drug['Org_sid'] ? $drug['PrepSeries_id'] : null, //серия при списании учитывается только если поставщик из документа учета не является поставщиком по госконтракту
                                'DocumentUcStr_Price' => $remove_price
                            ));
                        }
                    }

	                if (count($ostat_array) == 0) {
	                	//просто пропускаем строку, не исполняя
	                	continue;
	                    //return $this->createError(0, 'На остатках поставщика недостаточно медикаментов для списания');
	                }

	                //проверка количества на остатках для списания
                    $kolvo = $remove_kolvo;
	                foreach ($ostat_array as $ostat) {
	                    if ($kolvo > 0) {
	                        $kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
	                        $kolvo -= $kol;
	                    }
	                }
	                if ($kolvo > 0) {
	                	//просто пропускаем строку, не исполняя
	                	continue;
	                    //return $this->createError(0, 'На остатках поставщика недостаточно медикаментов для списания.');
	                }

                    //редактирование регистра остатков
                    $kolvo = $remove_kolvo;
	                foreach ($ostat_array as $ostat) {
	                    if ($kolvo > 0) {
	                        //списание из остатков
	                        $remove_kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
                            $remove_sum = $remove_kol * $remove_price;

	                        $kolvo -= $remove_kol;

	                        $q_params = array(
	                            'Contragent_id' => $ostat['Contragent_id'],
	                            'Org_id' => $drug['Org_sid'],
	                            'Storage_id' => $ostat['Storage_id'],
	                            'DrugShipment_id' => $ostat['DrugShipment_id'],
	                            'Drug_id' => $ostat['Drug_id'],
	                            'PrepSeries_id' => $ostat['PrepSeries_id'],
	                            'SubAccountType_id' => 1, // -- субсчёт доступно
	                            'Okei_id' => $ostat['Okei_id'],
	                            'DrugOstatRegistry_Kolvo' => $remove_kol * (-1),
	                            'DrugOstatRegistry_Sum' => $remove_sum * (-1),
	                            'DrugOstatRegistry_Cost' => $remove_price,
	                            'GoodsUnit_id' => $ostat['GoodsUnit_id'],
	                            'pmUser_id' => $data['pmUser_id']
	                        );

	                        $result = $this->_updateDrugOstatRegistry($q_params);
	                        if (!$this->isSuccessful($result)) {
	                            return $this->createError(0, 'Ошибка списания остатков');
	                        }

	                        //зачисление на остатки
                            $add_kol = $is_synonym ? $remove_kol*$synonym_koef : $remove_kol;
                            $add_sum = $add_kol * $add_price;

	                        $q_params['Contragent_id'] = $doc_data['Contragent_tid'];
	                        $q_params['Drug_id'] = $add_drug_id;
	                        $q_params['PrepSeries_id'] = $drug['PrepSeries_id'];
	                        $q_params['Org_id'] = $doc_data['Org_tid'];
	                        $q_params['Storage_id'] = $doc_data['Storage_tid'];
	                        $q_params['DrugOstatRegistry_Kolvo'] = $add_kol;
	                        $q_params['DrugOstatRegistry_Sum'] = $add_sum;
	                        $q_params['DrugOstatRegistry_Cost'] = $add_price;
	                        $q_params['GoodsUnit_id'] = $drug['GoodsUnit_bid'];
	                        $q_params['DrugShipment_id'] = $drug['DrugShipment_id'];

	                        $result = $this->_updateDrugOstatRegistry($q_params);
	                        if (!$this->isSuccessful($result)) {
	                            return $this->createError(0, 'Ошибка зачисления остатков');
	                        }

	                        if(!empty($drug['StorageZone_id'])){
                                $q_params['Drug_id'] = $add_drug_id;
	                        	$q_params['StorageZone_id'] = $drug['StorageZone_id'];
	                        	$q_params['DrugStorageZone_Price'] = $add_price;
	                        	$q_params['addDrugCount'] = $add_kolvo;
			                	$result = $this->_updateDrugStorageZone($q_params);
			                	if (!$this->isSuccessful($result)) {
				                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
				                }
				                $q_params['StorageZone_nid'] = $drug['StorageZone_id'];
				                $q_params['StorageZone_oid'] = null;
				                $q_params['StorageDrugMove_Price'] = $add_price;
	                        	$q_params['StorageDrugMove_Count'] = $add_kol;
	                        	$q_params['DocumentUcStr_id'] = $drug['DocumentUcStr_id'];
				                $result = $this->_commitStorageDrugMove($q_params);
			                	if (!$this->isSuccessful($result)) {
				                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
				                }
			                }

	                        //Проверяем на наличие на субсчете В пути
			                $queryParams = array(
			                    'Contragent_id' => $doc_data['Contragent_tid'],
			                    'Org_id' => $doc_data['Org_tid'],
			                    'Storage_id' => $doc_data['Storage_tid'],
			                    'DrugShipment_id' => $drug['DrugShipment_id'],
			                    'Drug_id' => $remove_drug_id,
			                    'PrepSeries_id' => $drug['PrepSeries_id'],
			                    'DrugOstatRegistry_Kolvo' => $remove_kolvo,
			                    'DrugOstatRegistry_Cost' => $remove_price,
			                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                                'DefaultGoodsUnit_id' => $default_goods_unit_id
			                );
			                $query = "
								select
									dor.DrugOstatRegistry_id
								from
									v_DrugOstatRegistry dor with (nolock)
									left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
								where
									dor.Org_id = :Org_id and
									dor.Drug_id = :Drug_id and
									dor.DrugShipment_id = :DrugShipment_id and
									dor.Contragent_id = :Contragent_id and
									dor.Storage_id = :Storage_id and
									sat.SubAccountType_Code = 3 and
									dor.DrugOstatRegistry_Kolvo >= :DrugOstatRegistry_Kolvo and
									dor.PrepSeries_id = :PrepSeries_id and
									dor.DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
									isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);
							";

							$result = $this->queryResult($query,$queryParams);
							if(is_array($result) && count($result)>0){
		                        //Списание с субсчета В пути
		                        $q_params['SubAccountType_id'] = 3;
		                        $q_params['DrugOstatRegistry_Kolvo'] = $remove_kol * (-1);
		                        $q_params['DrugOstatRegistry_Sum'] = $remove_sum * (-1);
		                        $result = $this->_updateDrugOstatRegistry($q_params);
		                        if (!$this->isSuccessful($result)) {
		                            return $this->createError(0, 'Ошибка списания остатков с субсчета В пути');
		                        }
		                    }
	                    }
	                }

	                $result = $this->saveObject('DocumentUcStr', array(
						'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
						'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 4), //4 - Исполнен
						'pmUser_id' => $data['pmUser_id']
					));

					if (!empty($result['Error_Msg'])) {
	                	return $this->createError(0, 'Ошибка при смене статуса строки документа.');
	                }
	            } else {
	                //создаем записи в регистре
	                $result = $this->_updateDrugOstatRegistry(array(
	                    'Contragent_id' => $doc_data['Contragent_tid'],
	                    'Org_id' => $doc_data['Org_tid'],
	                    'Storage_id' => $doc_data['Storage_tid'],
	                    'DrugShipment_id' => $drug['DrugShipment_id'],
	                    'Drug_id' => $drug['Drug_id'],
	                    'SubAccountType_id' => 1, // -- субсчёт доступно
	                    'PrepSeries_id' => $drug['PrepSeries_id'],
	                    'Okei_id' => $drug['Okei_id'],
	                    'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
	                    'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price'] * $drug['DocumentUcStr_Count'],
	                    'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
	                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                    'pmUser_id' => $data['pmUser_id']
	                ));
	                if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка создания регистра остатков');
	                }

	                if(!empty($drug['StorageZone_id'])){
	                	$result = $this->_updateDrugStorageZone(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_id' => $drug['StorageZone_id'],
	                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
	                		'addDrugCount' => $drug['DocumentUcStr_Count'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
		                $result = $this->_commitStorageDrugMove(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_oid' => null,
	                		'StorageZone_nid' => $drug['StorageZone_id'],
	                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
	                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
	                }

	                //Проверяем на наличие на субсчете В пути
	                $queryParams = array(
	                    'Contragent_id' => $doc_data['Contragent_tid'],
	                    'Org_id' => $doc_data['Org_tid'],
	                    'Storage_id' => $doc_data['Storage_tid'],
	                    'DrugShipment_id' => $drug['DrugShipment_id'],
	                    'Drug_id' => $drug['Drug_id'],
	                    'PrepSeries_id' => $drug['PrepSeries_id'],
	                    'DrugOstatRegistry_Kolvo' => ($drug['DocumentUcStr_Count']),
	                    'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
	                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                        'DefaultGoodsUnit_id' => $default_goods_unit_id
	                );
	                $query = "
						select
							dor.DrugOstatRegistry_id,
							dor.Storage_id
						from
							v_DrugOstatRegistry dor with (nolock)
							left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						where
							dor.Org_id = :Org_id and
							dor.Drug_id = :Drug_id and
							dor.DrugShipment_id = :DrugShipment_id and
							dor.Contragent_id = :Contragent_id and
							(dor.Storage_id = :Storage_id or dor.Storage_id is null) and
							sat.SubAccountType_Code = 3 and
							dor.DrugOstatRegistry_Kolvo >= :DrugOstatRegistry_Kolvo and
							dor.PrepSeries_id = :PrepSeries_id and
							dor.DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
							isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);
					";

					$result = $this->queryResult($query , $queryParams);

					if(is_array($result) && count($result)>0){
						//списываем с субсчета В пути
		                $result = $this->_updateDrugOstatRegistry(array(
		                    'Contragent_id' => $doc_data['Contragent_tid'],
		                    'Org_id' => $doc_data['Org_tid'],
		                    'Storage_id' => $result[0]['Storage_id'],
		                    'DrugShipment_id' => $drug['DrugShipment_id'],
		                    'Drug_id' => $drug['Drug_id'],
		                    'SubAccountType_id' => 3, // -- субсчёт В пути
		                    'PrepSeries_id' => $drug['PrepSeries_id'],
		                    'Okei_id' => $drug['Okei_id'],
		                    'DrugOstatRegistry_Kolvo' => ($drug['DocumentUcStr_Count'] * (-1)),
		                    'DrugOstatRegistry_Sum' => ($drug['DocumentUcStr_Price'] * $drug['DocumentUcStr_Count'] * (-1)),
		                    'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
		                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
		                    'pmUser_id' => $data['pmUser_id']
		                ));
		                if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при списании с субсчета В пути');
		                }
					}

	                $result = $this->saveObject('DocumentUcStr', array(
						'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
						'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 4), //4 - Исполнен
						'pmUser_id' => $data['pmUser_id']
					));

					if (!empty($result['Error_Msg'])) {
	                	return $this->createError(0, 'Ошибка при смене статуса строки документа.');
	                }
	            }
	        }
		}

		return array(array());
	}

	/**
	 * Корректировка регистра остатков при исполнении расходной накладной
	 */
	function updateDrugOstatRegistryForDocRas($data) {
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				du.DocumentUc_DogNum,
				du.WhsDocumentUc_id,
				du.DrugDocumentType_id,
				du.Contragent_tid,
				c_tid.Org_id as Org_tid,
				du.Storage_tid,
				du.Contragent_sid,
				c_sid.Org_id as Org_sid,
				du.Storage_sid,
				ddt.DrugDocumentType_Code
			from
				v_DocumentUc du with (nolock)
				inner join v_Contragent c_tid with (nolock) on c_tid.Contragent_id = du.Contragent_tid
				inner join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//при наличии резерва списываем остатки с резерва
        if ($this->haveReserve(array('DocumentUc_id' => $data['DocumentUc_id']))) {
            //списание остатков с резерва
            $result = $this->deleteReserve(array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($result['Error_Msg'])) {
                return array(array('Error_Message' => $result['Error_Msg']));
            }
            //получаем строки документа учета
			$drug_arr = array();
			$query = "
				select
					dus.Drug_id,
					dus.PrepSeries_id,
					120 as Okei_id, -- 120 - Упаковка
					isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
					(
						case
							when
								isnull(isnds.YesNo_Code, 0) = 1
							then
								isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
							else
								cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
						end
					) as DocumentUcStr_NdsPrice,
					isnull(dus.DocumentUcStr_Price,0) as DocumentUcStr_Price,
					ds.DrugShipment_id,
					dus.StorageZone_id,
					dus.DocumentUcStr_id,
					dus.GoodsUnit_bid
				from
					v_DocumentUcStr dus with (nolock)
					left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
					left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
					outer apply (
						select top 1
							dsl.DrugShipment_id
						from
							v_DrugShipmentLink dsl with (nolock)
						where
							dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					) ds
				where
					DocumentUc_id = :DocumentUc_id;
			";
			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id']
			));
			if (is_object($result)) {
				$drug_arr = $result->result('array');
			}
			if (count($drug_arr) == 0) {
				return array(array('Error_Msg' => 'Список медикаментов пуст'));
			}

			//редактируем записи в регистре
			foreach ($drug_arr as $drug) {
				if(!empty($drug['StorageZone_id'])){
                	$result = $this->_updateDrugStorageZone(array(
                		'Drug_id' => $drug['Drug_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_id' => $drug['StorageZone_id'],
                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                		'removeDrugCount' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
	                $result = $this->_commitStorageDrugMove(array(
                		'Drug_id' => $drug['Drug_id'],
                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_nid' => null,
                		'StorageZone_oid' => $drug['StorageZone_id'],
                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
                }

                //определяем партию для обрабатываемой строки
                $shipment_id = !empty($data['newDrugShipments'][$drug['DocumentUcStr_id']]) ? $data['newDrugShipments'][$drug['DocumentUcStr_id']] : $drug['DrugShipment_id'];

				//Зачисляем на субсчет В пути для огранизации-получателя
				$params = array(
					'Contragent_id' => $doc_data['Contragent_tid'],
					'Org_id' => $doc_data['Org_tid'],
					'Storage_id' => (!empty($doc_data['Contragent_tid']) && !empty($doc_data['Storage_tid'])) ? $doc_data['Storage_tid'] : null,
					'DrugShipment_id' => $shipment_id,
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'Okei_id' => $drug['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
					'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_NdsPrice']*$drug['DocumentUcStr_Count'],
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_NdsPrice'],
					'GoodsUnit_id' => $drug['GoodsUnit_bid'],
					'pmUser_id' => $data['pmUser_id']
				);
                $count = $this->getFirstResultFromQuery("
					select count(*) from DrugShipment where DrugShipment_id = :DrugShipment_id
                ", $params);
				if ($count === false) {
					return array(array('Error_Msg' => 'Ошибка при проверке партии'));
				} elseif ($count == 0) {
					return array(array('Error_Msg' => 'Документ не может быть исполнен'));
				}

				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@Storage_id = :Storage_id,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 3, -- субсчёт В пути
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                    @InnerTransaction_Disabled = 1,
						@GoodsUnit_id = :GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->getFirstRowFromQuery($query, $params);

				if ($result !== false) {
					if (!empty($result['Error_Msg'])) {
						return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
					}
				} else {
					return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
				}
			}
        } else {

			//получаем строки документа учета
			$drug_arr = array();
			$query = "
				select
					dus.Drug_id,
					dus.PrepSeries_id,
					120 as Okei_id, -- 120 - Упаковка
					isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
					(
						case
							when
								isnull(isnds.YesNo_Code, 0) = 1
							then
								isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
							else
								cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
						end
					) as DocumentUcStr_NdsPrice,
					isnull(dus.DocumentUcStr_Price,0) as DocumentUcStr_Price,
					ds.DrugShipment_id,
					dus.StorageZone_id,
					dus.DocumentUcStr_id,
					dus.GoodsUnit_bid
				from
					v_DocumentUcStr dus with (nolock)
					left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
					left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
					outer apply (
						select top 1
							dsl.DrugShipment_id
						from
							v_DrugShipmentLink dsl with (nolock)
						where
							dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					) ds
				where
					DocumentUc_id = :DocumentUc_id;
			";
			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id']
			));
			if (is_object($result)) {
				$drug_arr = $result->result('array');
			}
			if (count($drug_arr) == 0) {
				return array(array('Error_Msg' => 'Список медикаментов пуст'));
			}

			//редактируем записи в регистре
			foreach ($drug_arr as $drug) {
				$query = "
					select
						isnull(sum(DrugOstatRegistry_Kolvo), 0) as DrugOstatRegistry_Kolvo
					from
						v_DrugOstatRegistry with (nolock)
					where
						Contragent_id = :Contragent_id and
						Org_id = :Org_id and
						(:Storage_id is null or Storage_id = :Storage_id) and
						DrugShipment_id = :DrugShipment_id and
						Drug_id = :Drug_id and
						PrepSeries_id = :PrepSeries_id and
						DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
						isnull(GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);
				";
				$params = array(
					'Contragent_id' => $doc_data['Contragent_sid'],
					'Org_id' => $doc_data['Org_sid'],
					'Storage_id' => !empty($doc_data['Contragent_sid']) ? $doc_data['Storage_sid'] : null,
					'DrugShipment_id' => $drug['DrugShipment_id'],
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_NdsPrice'],
					'GoodsUnit_id' => $drug['GoodsUnit_bid'],
					'DefaultGoodsUnit_id' => $default_goods_unit_id,
				);
				$result = $this->getFirstResultFromQuery($query, $params);
				if ($result === false) {
					return array(array('Error_Msg' => 'Ошибка при получении данных регистра остатков'));
				} else if($result <= 0 || $result < $drug['DocumentUcStr_Count']*1) {
					return array(array('Error_Msg' => 'В регистре остатков недостаточно медикаментов для списания'));
				}

				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@Storage_id = :Storage_id,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 1, -- субсчёт доступно
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                    @InnerTransaction_Disabled = 1,
						@GoodsUnit_id = :GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$params = array(
					'Contragent_id' => $doc_data['Contragent_sid'],
					'Org_id' => $doc_data['Org_sid'],
					'Storage_id' => !empty($doc_data['Contragent_sid']) ? $doc_data['Storage_sid'] : null,
					'DrugShipment_id' => $drug['DrugShipment_id'],
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'Okei_id' => $drug['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count']*(-1),
					'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_NdsPrice']*$drug['DocumentUcStr_Count']*(-1),
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_NdsPrice'],
					'GoodsUnit_id' => $drug['GoodsUnit_bid'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->getFirstRowFromQuery($query, $params);

				if ($result !== false) {
					if (!empty($result['Error_Msg'])) {
						return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
					}
				} else {
					return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
				}

				if(!empty($drug['StorageZone_id'])){
                	$result = $this->_updateDrugStorageZone(array(
                		'Drug_id' => $drug['Drug_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_id' => $drug['StorageZone_id'],
                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                		'removeDrugCount' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
	                $result = $this->_commitStorageDrugMove(array(
                		'Drug_id' => $drug['Drug_id'],
                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_nid' => null,
                		'StorageZone_oid' => $drug['StorageZone_id'],
                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
	                }
                }

                //определяем партию для обрабатываемой строки
                $shipment_id = !empty($data['newDrugShipments'][$drug['DocumentUcStr_id']]) ? $data['newDrugShipments'][$drug['DocumentUcStr_id']] : $drug['DrugShipment_id'];

				//Зачисляем на субсчет В пути для огранизации-получателя
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@Storage_id = :Storage_id,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 3, -- субсчёт В пути
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                    @InnerTransaction_Disabled = 1,
						@GoodsUnit_id = :GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$params = array(
					'Contragent_id' => $doc_data['Contragent_tid'],
					'Org_id' => $doc_data['Org_tid'],
					'Storage_id' => (!empty($doc_data['Contragent_tid']) && !empty($doc_data['Storage_tid'])) ? $doc_data['Storage_tid'] : null,
					'DrugShipment_id' => $shipment_id,
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'Okei_id' => $drug['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
					'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_NdsPrice']*$drug['DocumentUcStr_Count'],
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_NdsPrice'],
					'GoodsUnit_id' => $drug['GoodsUnit_bid'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->getFirstRowFromQuery($query, $params);

				if ($result !== false) {
					if (!empty($result['Error_Msg'])) {
						return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
					}
				} else {
					return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
				}
			}
		}

		return array(array());
	}

	/**
	 * Корректировка регистра остатков при исполнении документа оприходования
	 */
	function updateDrugOstatRegistryForDocOprih($data) {
        $drug_array = array();

		//получение данных по строкам регистра остатков
		$query = "
			select
				dor.Contragent_id,
				dor.Org_id,
				dor.DrugShipment_id,
				dor.Drug_id,
				dor.PrepSeries_id,
				dor.SubAccountType_id,
				dor.Okei_id,
				dus.DocumentUcStr_Count as DrugOstatRegistry_Kolvo,
				dor.DrugOstatRegistry_Cost*dus.DocumentUcStr_Count as DrugOstatRegistry_Sum,
				dor.Storage_id,
				dor.DrugOstatRegistry_Cost,
				dor.Drug_did,
				dus.StorageZone_id,
				dus.DocumentUcStr_id
			from
				v_DocumentUcStr dus with (nolock)
				inner join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				inner join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = dsl.DrugShipment_id
				left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
			where
				sat.SubAccountType_Code = 1 and
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));

		if (is_object($result)) {
			$drug_array = $result->result('array');
        }

        //е сли строк не нашлось, значит мы имеем дело с документом сформированным сервисом, и нужно извлекать строки без привязки к остаткам
        if (count($drug_array) == 0) {
            $query = "
                declare
                    @Okei_id bigint;

                set @Okei_id = (select top 1 Okei_id from v_Okei where Okei_Code = 778); -- Упаковка

                select
                    c_t.Contragent_id,
                    c_t.Org_id,
                    dsl.DrugShipment_id,
                    dus.Drug_id,
                    dus.PrepSeries_id,
                    @Okei_id as Okei_id, --dor.Okei_id,
                    dus.DocumentUcStr_Count as DrugOstatRegistry_Kolvo,
                    price.price_nds*dus.DocumentUcStr_Count as DrugOstatRegistry_Sum,
                    du.Storage_tid as Storage_id,
                    price.price_nds as DrugOstatRegistry_Cost,
                    null as Drug_did,
                    dus.StorageZone_id,
                    dus.DocumentUcStr_id,
                    dus.GoodsUnit_bid
                from
                    v_DocumentUcStr dus with (nolock)
                    left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
                    left join v_Contragent c_t with (nolock) on c_t.Contragent_id = du.Contragent_tid
                    inner join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_id
                    left join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = dsl.DrugShipment_id
                    left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
                    left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
                    left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
                    outer apply (
                        select (
                            case
                                when isnull(isnds.YesNo_Code, 0) = 1 then dus.DocumentUcStr_Price
                                else cast(dus.DocumentUcStr_Price*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(16,4))
                            end
                        ) as price_nds
                    ) price
                where
                    dus.DocumentUcStr_oid is null and
                    dus.DocumentUc_id = :DocumentUc_id;
            ";

            $result = $this->db->query($query, array(
                'DocumentUc_id' => $data['DocumentUc_id']
            ));

            if (is_object($result)) {
                $drug_array = $result->result('array');
            }
        }

        foreach($drug_array as $drug) {
            $drug['pmUser_id'] = $data['pmUser_id'];

            //начисление остатков
            $query = "
                declare
                    @ErrCode int,
                    @ErrMessage varchar(4000);

                exec xp_DrugOstatRegistry_count
                    @Contragent_id = :Contragent_id,
                    @Org_id = :Org_id,
                    @Storage_id = :Storage_id,
                    @DrugShipment_id = :DrugShipment_id,
                    @Drug_id = :Drug_id,
                    @Drug_did = :Drug_did,
                    @PrepSeries_id = :PrepSeries_id,
                    @SubAccountType_id = 1, -- субсчёт доступно
                    @Okei_id = :Okei_id,
                    @DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
                    @DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
                    @DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
                    @InnerTransaction_Disabled = 1,
                    @GoodsUnit_id = :GoodsUnit_bid,
                    @pmUser_id = :pmUser_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;
                select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
            $result = $this->getFirstRowFromQuery($query, $drug);
            if (!empty($result['Error_Msg'])) {
                return array($result);
            }

            if(!empty($drug['StorageZone_id'])){
                $drug['DrugStorageZone_Price'] = $drug['DrugOstatRegistry_Cost'];
                $drug['addDrugCount'] = $drug['DrugOstatRegistry_Kolvo'];
                $drug['GoodsUnit_id'] = $drug['GoodsUnit_bid'];
                $result = $this->_updateDrugStorageZone($drug);
                if (!$this->isSuccessful($result)) {
                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
                }
                $drug['StorageZone_oid'] = null;
                $drug['StorageZone_nid'] = $drug['StorageZone_id'];
                $drug['StorageDrugMove_Price'] = $drug['DrugOstatRegistry_Cost'];
                $drug['StorageDrugMove_Count'] = $drug['DrugOstatRegistry_Kolvo'];
                $result = $this->_commitStorageDrugMove($drug);
                if (!$this->isSuccessful($result)) {
                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
                }
            }
        }


		return array(array());
	}


	/**
	 * Корректировка регистра остатков при исполнении накладной на внутреннее перемещение
	 */
	function updateDrugOstatRegistryForDocNVP($data) {
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				isnull(c_sid.Org_id, l_sid.Org_id) as Org_sid,
				isnull(c_tid.Org_id, l_tid.Org_id) as Org_tid,
				du.Contragent_sid,
				du.Contragent_tid,
				du.Storage_sid,
				du.Storage_tid,
				du.DrugFinance_id,
				du.WhsDocumentCostItemType_id,
				du.DocumentUc_didDate
			from
				v_DocumentUc du with (nolock)
				left join v_Contragent c_tid with (nolock) on c_tid.Contragent_id = du.Contragent_tid
				left join v_Lpu l_tid with(nolock) on l_tid.Lpu_id = c_tid.Lpu_id
				inner join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_Lpu l_sid with(nolock) on l_sid.Lpu_id = c_sid.Lpu_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$doc_data = $res[0];
			}
		}
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.DocumentUcStr_id,
				dus.Drug_id,
				dus.PrepSeries_id,
				dus.DrugFinance_id,
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price,
				ds.DrugShipment_id,
				ds.WhsDocumentSupply_id,
				ds.AccountType_id,
				ds.DrugShipment_pid,
				d.Drug_Name,
				ps.PrepSeries_Ser,
				dus.StorageZone_id,
				dus.DocumentUcStr_id,
				dus.GoodsUnit_bid
			from
				v_DocumentUcStr dus with (nolock)
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
                left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
                left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				outer apply (
					select top 1
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id,
						i_ds.AccountType_id,
						i_ds.DrugShipment_pid
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				) ds
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//запросы для создания партий
		$sh_query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_DrugShipment_ins
				@DrugShipment_id = @Res output,
				@DrugShipment_setDT = :DrugShipment_setDT,
				@DrugShipment_Name = :DrugShipment_Name,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@AccountType_id = :AccountType_id,
				@DrugShipment_pid = :DrugShipment_pid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$shl_query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_DrugShipmentLink_ins
				@DrugShipmentLink_id = @Res output,
				@DrugShipment_id = :DrugShipment_id,
				@DocumentUcStr_id = :DocumentUcStr_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipmentLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//при наличии резерва списываем остатки с резерва
        if ($this->haveReserve(array('DocumentUc_id' => $data['DocumentUc_id']))) {

            foreach ($drug_arr as $drug) {
				//создаем новую партию для строки накладной
				//$sh_id = $this->getFirstResultFromQuery($sh_query, array(
				$sh_query_compl = getDebugSQL($sh_query, array(
					'DrugShipment_setDT' => $doc_data['DocumentUc_didDate'], // дата партии соотвтествует дате выполнения родительского документа
					'DrugShipment_Name' => 'tmp_'.$drug['DocumentUcStr_id'], //временное наименование
					'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                    'AccountType_id' => $drug['AccountType_id'],
                    'DrugShipment_pid' => !empty($drug['DrugShipment_pid']) ? $drug['DrugShipment_pid'] : $drug['DrugShipment_id'], //если родительская партия сама является дочерней, то вписываем в поле идентифкатор её родителя
					'pmUser_id' => $data['pmUser_id']
				));
				$sh_id = $this->getFirstResultFromQuery($sh_query_compl);

                if ($sh_id > 0) {
                    //обновление наименования партии
                    $sh_res = $this->saveObject('DrugShipment', array(
                        'DrugShipment_id' => $sh_id,
                        'DrugShipment_Name' => $sh_id
                    ));

                    //связь партии со строкой накладной
                    $shl_id = $this->getFirstResultFromQuery($shl_query, array(
                        'DrugShipment_id' => $sh_id,
                        'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                } else {
                    return array(array('Error_Msg' => 'Ошибка создания партии'));
                }

				//ищем нужные записи в резерве
				$kolvo = $drug['DocumentUcStr_Count'];

				$query = "
					select
		                reserved_dor.Contragent_id,
						reserved_dor.Org_id,
						reserved_dor.Drug_id,
						reserved_dor.PrepSeries_id,
						reserved_dor.Okei_id,
						dorl.DrugOstatRegistryLink_Count as DrugOstatRegistry_Kolvo,
						reserved_dor.DrugOstatRegistry_Sum,
						reserved_dor.DrugOstatRegistry_Cost,
						reserved_dor.GoodsUnit_id,
						dus.DocumentUcStr_id
		            from
		                v_DocumentUcStr dus with (nolock)
		                left join v_DrugOstatRegistryLink dorl with (nolock) on
		                    dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
		                    dorl.DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
		                left join v_DrugOstatRegistry reserved_dor with (nolock) on reserved_dor.DrugOstatRegistry_id = dorl.DrugOstatRegistry_id
		            where
		                dorl.DrugOstatRegistryLink_id is not null and
		                (
	                        :DocumentUcStr_id is not null and
	                        dus.DocumentUcStr_id = :DocumentUcStr_id
		                );
				";

				$res = $this->queryResult($query, array(
		            'DocumentUcStr_id' => $drug['DocumentUcStr_id']
		        ));

				if ( is_array($res) ) {
					if (!empty($res[0]['Error_Msg'])) {
						return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
					}

					foreach ($res as $ostat) {
						if ($kolvo > 0) {
							//зачисление
							$kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
							$sum = $ostat['DrugOstatRegistry_Cost'] > 0 ? $ostat['DrugOstatRegistry_Cost']*$kol : ($ostat['DrugOstatRegistry_Sum']/$ostat['DrugOstatRegistry_Kolvo'])*$kol;

							$query = "
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);
								exec xp_DrugOstatRegistry_count
									@Contragent_id = :Contragent_id,
									@Org_id = :Org_id,
									@Storage_id = :Storage_id,
									@DrugShipment_id = :DrugShipment_id,
									@Drug_id = :Drug_id,
									@PrepSeries_id = :PrepSeries_id,
									@SubAccountType_id = 1, -- субсчёт доступно
									@Okei_id = :Okei_id,
									@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
									@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
									@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                                @InnerTransaction_Disabled = 1,
									@GoodsUnit_id = :GoodsUnit_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";

							$q_params = array(
								'Contragent_id' => $ostat['Contragent_id'],
								'Org_id' => !empty($ostat['Org_id'])?$doc_data['Org_sid']:null,
								'Storage_id' => $doc_data['Storage_tid'],
								'DrugShipment_id' => $sh_id,
								'Drug_id' => $ostat['Drug_id'],
								'PrepSeries_id' => $ostat['PrepSeries_id'],
								'Okei_id' => $ostat['Okei_id'],
								'DrugOstatRegistry_Kolvo' => $kol,
								'DrugOstatRegistry_Sum' => $sum,
								'DrugOstatRegistry_Cost' => $ostat['DrugOstatRegistry_Cost'],
								'GoodsUnit_id' => $ostat['GoodsUnit_id'],
								'pmUser_id' => $data['pmUser_id']
							);

							//зачисление
							//echo getDebugSQL($query, $q_params);exit;
							$result = $this->db->query($query, $q_params);
							if ( is_object($result) ) {
								$res = $result->result('array');
								if (!empty($res[0]['Error_Msg'])) {
									return array(0 => array('Error_Msg' => 'Ошибка зачисления остатков'));
								}
							} else {
								return array(0 => array('Error_Msg' => 'Ошибка запроса зачисления остатков'));
							}
						}
					}

					if(!empty($drug['StorageZone_id'])){
						// записи по списанию
	                	$result = $this->_updateDrugStorageZone(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_id' => $drug['StorageZone_id'],
	                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
	                		'removeDrugCount' => $drug['DocumentUcStr_Count'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
		                $result = $this->_commitStorageDrugMove(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_nid' => null,
	                		'StorageZone_oid' => $drug['StorageZone_id'],
	                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
	                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
	                }
				} else {
					return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
				}
			}
			//списание остатков с резерва
            $result = $this->deleteReserve(array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($result['Error_Msg'])) {
                return array(array('Error_Message' => $result['Error_Msg']));
            }
        } else {
			foreach ($drug_arr as $drug) {
				//создаем новую партию для строки накладной
				//$sh_id = $this->getFirstResultFromQuery($sh_query, array(
				$sh_query_compl = getDebugSQL($sh_query, array(
                    'DrugShipment_setDT' => $doc_data['DocumentUc_didDate'], // дата партии соотвтествует дате выполнения родительского документа
					'DrugShipment_Name' => 'tmp_'.$drug['DocumentUcStr_id'], //временное наименование
					'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                    'AccountType_id' => $drug['AccountType_id'],
                    'DrugShipment_pid' => !empty($drug['DrugShipment_pid']) ? $drug['DrugShipment_pid'] : $drug['DrugShipment_id'], //если родительская партия сама является дочерней, то вписываем в поле идентифкатор её родителя
					'pmUser_id' => $data['pmUser_id']
				));
				$sh_id = $this->getFirstResultFromQuery($sh_query_compl);

                if ($sh_id > 0) {
                    //обновление наименования партии
                    $sh_res = $this->saveObject('DrugShipment', array(
                        'DrugShipment_id' => $sh_id,
                        'DrugShipment_Name' => $sh_id
                    ));

                    //связь партии со строкой накладной
                    $shl_id = $this->getFirstResultFromQuery($shl_query, array(
                        'DrugShipment_id' => $sh_id,
                        'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                } else {
                    return array(array('Error_Msg' => 'Ошибка создания партии'));
                }

				//ищем нужные записи в регистре и проверяем наличие необходимого количества медикамента
				$kolvo = $drug['DocumentUcStr_Count'];

				$query = "
					select
						dor.Contragent_id,
						dor.Org_id,
						dor.Storage_id,
						dor.DrugShipment_id,
						dor.Drug_id,
						dor.PrepSeries_id,
						dor.Okei_id,
						dor.DrugOstatRegistry_Kolvo,
						dor.DrugOstatRegistry_Sum,
						dor.DrugOstatRegistry_Cost,
						dor.GoodsUnit_id
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_Contragent c with(nolock) on c.Contragent_id = dor.Contragent_id
						left join v_Lpu l with(nolock) on l.Lpu_id = c.Lpu_id
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
					where
						coalesce(dor.Org_id,c.Org_id,l.Org_id,0) = ISNULL(:Org_id,0) and
						dor.Storage_id = :Storage_id and
						dor.Drug_id = :Drug_id and
						dor.DrugShipment_id = :DrugShipment_id and
						sat.SubAccountType_Code = 1 and
						dor.DrugOstatRegistry_Kolvo > 0 and
						(:DocumentUcStr_Price is null or dor.DrugOstatRegistry_Cost = :DocumentUcStr_Price) and
						isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id) and
						(:DrugFinance_id is null or dor.DrugFinance_id = :DrugFinance_id) and
						(:WhsDocumentCostItemType_id is null or dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id);
				";

				$result = $this->db->query($query, array(
					'Org_id' => $doc_data['Org_sid'],
					'Storage_id' => $doc_data['Storage_sid'],
					'Drug_id' => $drug['Drug_id'],
					'DrugShipment_id' => $drug['DrugShipment_id'],
					'DocumentUcStr_Price' => $drug['DocumentUcStr_Price'],
					'GoodsUnit_id' => $drug['GoodsUnit_bid'],
					'DefaultGoodsUnit_id' => $default_goods_unit_id,
					'DrugFinance_id' => $doc_data['DrugFinance_id'] > 0 ? $doc_data['DrugFinance_id'] : $drug['DrugFinance_id'],
					'WhsDocumentCostItemType_id' => $doc_data['WhsDocumentCostItemType_id']
				));

				if ( is_object($result) ) {
					$res = $result->result('array');
					if (!empty($res[0]['Error_Msg'])) {
						return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
					}

					foreach ($res as $ostat) {
						if ($kolvo > 0) {
							//списание
							$kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
							$sum = $ostat['DrugOstatRegistry_Cost'] > 0 ? $ostat['DrugOstatRegistry_Cost']*$kol : ($ostat['DrugOstatRegistry_Sum']/$ostat['DrugOstatRegistry_Kolvo'])*$kol;

							$kolvo -= $kol;

							$query = "
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);
								exec xp_DrugOstatRegistry_count
									@Contragent_id = :Contragent_id,
									@Org_id = :Org_id,
									@Storage_id = :Storage_id,
									@DrugShipment_id = :DrugShipment_id,
									@Drug_id = :Drug_id,
									@PrepSeries_id = :PrepSeries_id,
									@SubAccountType_id = 1, -- субсчёт доступно
									@Okei_id = :Okei_id,
									@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
									@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
									@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                                @InnerTransaction_Disabled = 1,
									@GoodsUnit_id = :GoodsUnit_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";

							$q_params = array(
								'Contragent_id' => $ostat['Contragent_id'],
								'Org_id' => !empty($ostat['Org_id'])?$doc_data['Org_sid']:null,
								'Storage_id' => $ostat['Storage_id'],
								'DrugShipment_id' => $ostat['DrugShipment_id'],
								'Drug_id' => $ostat['Drug_id'],
								'PrepSeries_id' => $ostat['PrepSeries_id'],
								'Okei_id' => $ostat['Okei_id'],
								'DrugOstatRegistry_Kolvo' => $kol*(-1),
								'DrugOstatRegistry_Sum' => $sum*(-1),
								'DrugOstatRegistry_Cost' => $ostat['DrugOstatRegistry_Cost'],
								'GoodsUnit_id' => $ostat['GoodsUnit_id'],
								'pmUser_id' => $data['pmUser_id']
							);

							$result = $this->db->query($query, $q_params);
							if ( is_object($result) ) {
								$res = $result->result('array');
								if (!empty($res[0]['Error_Msg'])) {
									return array(0 => array('Error_Msg' => 'Ошибка списания остатков'));
								}
							} else {
								return array(0 => array('Error_Msg' => 'Ошибка запроса списания остатков'));
							}

							//зачисление
							$q_params['Storage_id'] = $doc_data['Storage_tid'];
							$q_params['DrugShipment_id'] = $sh_id; //идентификатор новой созданной партии
							$q_params['DrugOstatRegistry_Kolvo'] = $kol;
							$q_params['DrugOstatRegistry_Sum'] = $sum;
							//echo getDebugSQL($query, $q_params);exit;
							$result = $this->db->query($query, $q_params);
							if ( is_object($result) ) {
								$res = $result->result('array');
								if (!empty($res[0]['Error_Msg'])) {
									return array(0 => array('Error_Msg' => 'Ошибка зачисления остатков'));
								}
							} else {
								return array(0 => array('Error_Msg' => 'Ошибка запроса зачисления остатков'));
							}
						}
					}
					if(!empty($drug['StorageZone_id'])){
						// записи по списанию
	                	$result = $this->_updateDrugStorageZone(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_id' => $drug['StorageZone_id'],
	                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
	                		'removeDrugCount' => $drug['DocumentUcStr_Count'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
		                $result = $this->_commitStorageDrugMove(array(
	                		'Drug_id' => $drug['Drug_id'],
	                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
	                		'PrepSeries_id' => $drug['PrepSeries_id'],
	                		'DrugShipment_id' => $drug['DrugShipment_id'],
	                		'StorageZone_nid' => null,
	                		'StorageZone_oid' => $drug['StorageZone_id'],
	                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
	                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
	                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
	                		'pmUser_id' => $data['pmUser_id']
	                	));
	                	if (!$this->isSuccessful($result)) {
		                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
		                }
	                }
				}

				if ($kolvo > 0) {
	                $price_str = number_format($drug['DocumentUcStr_Price'], 2, '.', ' ');
					return array(0 => array('Error_Msg' => "На остатках поставщика недостаточно медикаментов для списания. Серия: {$drug['PrepSeries_Ser']}. Цена: {$price_str} р. Медикамент: {$drug['Drug_Name']}."));
				}
			}
		}

		return array(array());
	}


	/**
	 * Корректировка регистра остатков при исполнении накладной на перемещение внутри склада
	 */
	function updateDrugOstatRegistryForDocNPVS($data) {
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$query = "
			select
       			du.StorageZone_sid,
				du.StorageZone_tid
			from
				v_DocumentUc du with (nolock)
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
        $doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (!is_array($doc_data) || count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
        $query = "
            select
                dus.DocumentUcStr_id,
				dus.Drug_id,
				dus.PrepSeries_id,
				dus.GoodsUnit_bid,
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price,
				ds.DrugShipment_id
			from
				v_DocumentUcStr dus with (nolock)
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
                left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
				outer apply (
					select top 1
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id,
						i_ds.AccountType_id,
						i_ds.DrugShipment_pid
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				) ds
			where
				dus.DocumentUc_id = :DocumentUc_id;
        ";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//при наличии резерва списываем остатки с резерва
        if ($this->haveReserve(array('DocumentUc_id' => $data['DocumentUc_id']))) {
    		//списание остатков с резерва
            $result = $this->deleteReserve(array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($result['Error_Msg'])) {
                return array(array('Error_Message' => $result['Error_Msg']));
            }
		}

        //перемещение медикаментов между местакми хранения
        if(!empty($doc_data['StorageZone_sid']) && !empty($doc_data['StorageZone_tid'])){
            foreach($drug_arr as $drug) {
                //записи по списанию
                $result = $this->_updateDrugStorageZone(array(
                    'Drug_id' => $drug['Drug_id'],
                    'PrepSeries_id' => $drug['PrepSeries_id'],
                    'DrugShipment_id' => $drug['DrugShipment_id'],
                    'StorageZone_id' => $doc_data['StorageZone_sid'],
                    'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                    'removeDrugCount' => $drug['DocumentUcStr_Count'],
                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!$this->isSuccessful($result)) {
                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
                }

                //записи по списанию
                $result = $this->_updateDrugStorageZone(array(
                    'Drug_id' => $drug['Drug_id'],
                    'PrepSeries_id' => $drug['PrepSeries_id'],
                    'DrugShipment_id' => $drug['DrugShipment_id'],
                    'StorageZone_id' => $doc_data['StorageZone_tid'],
                    'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                    'addDrugCount' => $drug['DocumentUcStr_Count'],
                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!$this->isSuccessful($result)) {
                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
                }

                //журнал движений
                $result = $this->_commitStorageDrugMove(array(
                    'Drug_id' => $drug['Drug_id'],
                    'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                    'PrepSeries_id' => $drug['PrepSeries_id'],
                    'DrugShipment_id' => $drug['DrugShipment_id'],
                    'StorageZone_nid' => $doc_data['StorageZone_tid'],
                    'StorageZone_oid' => $doc_data['StorageZone_sid'],
                    'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
                    'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!$this->isSuccessful($result)) {
                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
                }
            }
        }

		return array(array());
	}


	/**
	 * Корректировка регистра остатков при исполнении возвратной накладной (расходной)
	 */
	function updateDrugOstatRegistryForDocVozNakR($data) {
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();
        $suppliers_ostat_control = !empty($data['options']['drugcontrol']['suppliers_ostat_control']);

		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				du.DocumentUc_DogNum,
				du.WhsDocumentUc_id,
				du.DrugDocumentType_id,
				du.Contragent_tid,
				c_tid.Org_id as Org_tid,
				isnull(is_acc.YesNo_Code, 0) as Org_tid_IsAccess,
				du.Storage_tid,
				du.Contragent_sid,
				c_sid.Org_id as Org_sid,
				du.Storage_sid,
				ddt.DrugDocumentType_Code
			from
				v_DocumentUc du with (nolock)
				inner join v_Contragent c_tid with (nolock) on c_tid.Contragent_id = du.Contragent_tid
				left join v_Org o_tid with (nolock) on o_tid.Org_id = c_tid.Org_id
				left join v_YesNo is_acc with (nolock) on is_acc.YesNo_id = o_tid.Org_IsAccess
				inner join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.Drug_id,
				dus.PrepSeries_id,
				120 as Okei_id, -- 120 - Упаковка
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price,
				ds.DrugShipment_id,
				ds.WhsDocumentSupply_id,
				wds.Org_sid,
				dus.StorageZone_id,
				isnull(dus.DocumentUcStr_Price,0) as DocumentUcStr_Price_Original,
				dus.DocumentUcStr_id,
				dus.GoodsUnit_bid
			from
				v_DocumentUcStr dus with (nolock)
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				outer apply (
					select top 1
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				) ds
				outer apply (
					select top 1
						WhsDocumentSupply_id,
						Org_sid
					from
						v_WhsDocumentSupply i_wds with (nolock)
					where
						i_wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
				) wds
			where
				DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//при наличии резерва списываем остатки с резерва
        if ($this->haveReserve(array('DocumentUc_id' => $data['DocumentUc_id']))) {
            //списание остатков с резерва
            $result = $this->deleteReserve(array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($result['Error_Msg'])) {
                return array(array('Error_Message' => $result['Error_Msg']));
            }

            foreach ($drug_arr as $drug) {
				//если поставщик по ГК равен получателю из возвртаной накладной, то начисляем на остатки поставщика остатки
				if ($suppliers_ostat_control && $doc_data['Org_tid'] == $drug['Org_sid'] && !empty($doc_data['WhsDocumentUc_id'])) {
					//ищем подходящие партии для начисления
					$query = "
						select top 1
							dor.DrugShipment_id
						from
							DrugOstatRegistry dor with (nolock)
							left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
							left join v_DrugShipmentLink dsl on dsl.DrugShipment_id = dor.DrugShipment_id
						where
							dor.SubAccountType_id = 1 and
							dor.Org_id = :Org_id and
							dor.Drug_id = :Drug_id and
							dor.DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
							isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id) and
							dor.Contragent_id is null and
							ds.WhsDocumentSupply_id = :WhsDocumentSupply_id and
							dsl.DrugShipmentLink_id is null
					";
					$sh_id = $this->getFirstResultFromQuery($query, array(
						'Org_id' => $doc_data['Org_tid'],
						'Drug_id' => $drug['Drug_id'],
						'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
						'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
						'GoodsUnit_id' => $drug['GoodsUnit_bid'],
						'DefaultGoodsUnit_id' => $default_goods_unit_id
					));
					if ($sh_id === false) {
						return array(array('Error_Msg' => 'Не удалось найти партию для начисления остатков'));
					} else if ($sh_id > 0) {
						//начисление остатков
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec xp_DrugOstatRegistry_count
								@Contragent_id = :Contragent_id,
								@Org_id = :Org_id,
								@Storage_id = :Storage_id,
								@DrugShipment_id = :DrugShipment_id,
								@Drug_id = :Drug_id,
								@PrepSeries_id = :PrepSeries_id,
								@SubAccountType_id = 1, -- субсчёт доступно
								@Okei_id = :Okei_id,
								@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
								@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
								@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
								@GoodsUnit_id = :GoodsUnit_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$params = array(
							'Contragent_id' => null,
							'Org_id' => $doc_data['Org_tid'],
							'Storage_id' => null,
							'DrugShipment_id' => $sh_id,
							'Drug_id' => $drug['Drug_id'],
							'PrepSeries_id' => $drug['PrepSeries_id'],
							'Okei_id' => $drug['Okei_id'],
							'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
							'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price']*$drug['DocumentUcStr_Count'],
							'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
							'GoodsUnit_id' => $drug['GoodsUnit_bid'],
							'pmUser_id' => $data['pmUser_id']
						);
						$result = $this->getFirstRowFromQuery($query, $params);

						if ($result !== false) {
							if (!empty($result['Error_Msg'])) {
								return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
							}
						} else {
							return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
						}
					}
				}
				if(!empty($drug['StorageZone_id'])){
					// записи по списанию
                	$result = $this->_updateDrugStorageZone(array(
                		'Drug_id' => $drug['Drug_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_id' => $drug['StorageZone_id'],
                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price_Original'],
                		'removeDrugCount' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при списании медикамента с места хранения. '.$result[0]['Error_Msg']);
	                }
	                $result = $this->_commitStorageDrugMove(array(
                		'Drug_id' => $drug['Drug_id'],
                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_nid' => null,
                		'StorageZone_oid' => $drug['StorageZone_id'],
                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price_Original'],
                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента с места хранения. '.$result[0]['Error_Msg']);
	                }
                }
			}
        } else {
			//редактируем записи в регистре
			foreach ($drug_arr as $drug) {
				$query = "
					select
						isnull(sum(DrugOstatRegistry_Kolvo), 0) as DrugOstatRegistry_Kolvo
					from
						v_DrugOstatRegistry with (nolock)
					where
						Contragent_id = :Contragent_id and
						Org_id = :Org_id and
						(:Storage_id is null or Storage_id = :Storage_id) and
						DrugShipment_id = :DrugShipment_id and
						Drug_id = :Drug_id and
						PrepSeries_id = :PrepSeries_id and
						DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
						isnull(GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);
				";
				$params = array(
					'Contragent_id' => $doc_data['Contragent_sid'],
					'Org_id' => $doc_data['Org_sid'],
					'Storage_id' => !empty($doc_data['Contragent_sid']) ? $doc_data['Storage_sid'] : null,
					'DrugShipment_id' => $drug['DrugShipment_id'],
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
					'GoodsUnit_id' => $drug['GoodsUnit_bid'],
					'DefaultGoodsUnit_id' => $default_goods_unit_id
				);
				$result = $this->getFirstResultFromQuery($query, $params);
				if ($result === false) {
					return array(array('Error_Msg' => 'Ошибка при получении данных регистра остатков'));
				} else if($result <= 0 || $result < $drug['DocumentUcStr_Count']*1) {
					return array(array('Error_Msg' => 'В регистре остатков недостаточно медикаментов для списания'));
				}

				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@Storage_id = :Storage_id,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 1, -- субсчёт доступно
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                    @InnerTransaction_Disabled = 1,
						@GoodsUnit_id = :GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$params = array(
					'Contragent_id' => $doc_data['Contragent_sid'],
					'Org_id' => $doc_data['Org_sid'],
					'Storage_id' => !empty($doc_data['Contragent_sid']) ? $doc_data['Storage_sid'] : null,
					'DrugShipment_id' => $drug['DrugShipment_id'],
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $drug['PrepSeries_id'],
					'Okei_id' => $drug['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count']*(-1),
					'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price']*$drug['DocumentUcStr_Count']*(-1),
					'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
					'GoodsUnit_id' => $drug['GoodsUnit_bid'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->getFirstRowFromQuery($query, $params);

				if ($result !== false) {
					if (!empty($result['Error_Msg'])) {
						return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
					}
				} else {
					return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
				}

				//если поставщик по ГК равен получателю из возвртаной накладной, то начисляем на остатки поставщика остатки
				if ($suppliers_ostat_control && $doc_data['Org_tid'] == $drug['Org_sid'] && !empty($doc_data['WhsDocumentUc_id'])) {
					//ищем подходящие партии для начисления
					$query = "
						select top 1
							dor.DrugShipment_id
						from
							DrugOstatRegistry dor with (nolock)
							left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
							left join v_DrugShipmentLink dsl on dsl.DrugShipment_id = dor.DrugShipment_id
						where
							dor.SubAccountType_id = 1 and
							dor.Org_id = :Org_id and
							dor.Drug_id = :Drug_id and
							dor.DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost and
							isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id) and
							dor.Contragent_id is null and
							ds.WhsDocumentSupply_id = :WhsDocumentSupply_id and
							dsl.DrugShipmentLink_id is null
					";
					$sh_id = $this->getFirstResultFromQuery($query, array(
						'Org_id' => $doc_data['Org_tid'],
						'Drug_id' => $drug['Drug_id'],
						'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
						'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                        'DefaultGoodsUnit_id' => $default_goods_unit_id,
						'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id']
					));
					if ($sh_id === false) {
						return array(array('Error_Msg' => 'Не удалось найти партию для начисления остатков'));
					} else if ($sh_id > 0) {
						//начисление остатков
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec xp_DrugOstatRegistry_count
								@Contragent_id = :Contragent_id,
								@Org_id = :Org_id,
								@Storage_id = :Storage_id,
								@DrugShipment_id = :DrugShipment_id,
								@Drug_id = :Drug_id,
								@PrepSeries_id = :PrepSeries_id,
								@SubAccountType_id = 1, -- субсчёт доступно
								@Okei_id = :Okei_id,
								@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
								@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
								@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
								@GoodsUnit_id = :GoodsUnit_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$params = array(
							'Contragent_id' => null,
							'Org_id' => $doc_data['Org_tid'],
							'Storage_id' => null,
							'DrugShipment_id' => $sh_id,
							'Drug_id' => $drug['Drug_id'],
							'PrepSeries_id' => $drug['PrepSeries_id'],
							'Okei_id' => $drug['Okei_id'],
							'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
							'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price']*$drug['DocumentUcStr_Count'],
							'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
							'GoodsUnit_id' => $drug['GoodsUnit_bid'],
							'pmUser_id' => $data['pmUser_id']
						);
						$result = $this->getFirstRowFromQuery($query, $params);

						if ($result !== false) {
							if (!empty($result['Error_Msg'])) {
								return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
							}
						} else {
							return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
						}
					}
				}

				if(!empty($drug['StorageZone_id'])){
					// записи по списанию
                	$result = $this->_updateDrugStorageZone(array(
                		'Drug_id' => $drug['Drug_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_id' => $drug['StorageZone_id'],
                		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price_Original'],
                		'removeDrugCount' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при списании медикамента с места хранения. '.$result[0]['Error_Msg']);
	                }
	                $result = $this->_commitStorageDrugMove(array(
                		'Drug_id' => $drug['Drug_id'],
                		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                		'PrepSeries_id' => $drug['PrepSeries_id'],
                		'DrugShipment_id' => $drug['DrugShipment_id'],
                		'StorageZone_nid' => null,
                		'StorageZone_oid' => $drug['StorageZone_id'],
                		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price_Original'],
                		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                		'pmUser_id' => $data['pmUser_id']
                	));
                	if (!$this->isSuccessful($result)) {
	                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента с места хранения. '.$result[0]['Error_Msg']);
	                }
                }
			}
		}

		//если получатель по возвратной накладной (расходной) имеет доступ в систему, то создаем возвратную накладную (приходную)
		if ($doc_data['Org_tid_IsAccess'] > 0) {
			$response = $this->createDocVozNakPByDocumentUc(array(
				'DocumentUc_id' => $data['DocumentUc_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (is_array($response) && count($response) > 0 && isset($response[0]['Error_Msg']) && !empty($response[0]['Error_Msg'])) {
				return array(array('Error_Msg' => $response[0]['Error_Msg']));
			}
		}

		//return array(array('Error_Msg' => 'Отладка'));

		return array(array());
	}


	/**
	 * Корректировка регистра остатков при исполнении возвратной накладной (приходной)
	 */
	function updateDrugOstatRegistryForDocVozNakP($data) {
		if (!isset($data['DocumentUc_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
		}

		//получаем данные о документе учета
		$doc_data = array();
		$query = "
			select
				du.DocumentUc_pid,
				du.DocumentUc_DogNum,
				du.WhsDocumentUc_id,
				du.DrugDocumentType_id,
				du.Contragent_tid,
				c_tid.Org_id as Org_tid,
				du.Storage_tid,
				du.Contragent_sid,
				c_sid.Org_id as Org_sid,
				du.Storage_sid
			from
				v_DocumentUc du with (nolock)
				inner join v_Contragent c_tid with (nolock) on c_tid.Contragent_id = du.Contragent_tid
				inner join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (count($doc_data) == 0) {
			return array(array('Error_Msg' => 'Документа учета не найден'));
		}

		//получаем данные о родительском документе учета
		$query = "
			declare @DocumentUc_id bigint = :DocumentUc_id; -- id возвратной накладной (расходной)

			-- id приходной накладной
			set @DocumentUc_id = (select DocumentUc_pid from DocumentUc with (nolock) where DocumentUc_id = @DocumentUc_id);

			-- id расходной накладной
			set @DocumentUc_id = (select DocumentUc_pid from DocumentUc with (nolock) where DocumentUc_id = @DocumentUc_id);

			select
				DocumentUc_id,
				Storage_sid
			from
				DocumentUc with (nolock)
			where
				DocumentUc_id = @DocumentUc_id;
		";
		$parent_doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $doc_data['DocumentUc_pid']
		));
		if (count($parent_doc_data) == 0) {
			return array(array('Error_Msg' => 'Родительский документа учета не найден'));
		}

		//получаем строки документа учета
		$drug_arr = array();
		$query = "
			select
				dus.Drug_id,
				dus.PrepSeries_id,
				120 as Okei_id, -- 120 - Упаковка
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price,
				ds.DrugShipment_id,
				ds.WhsDocumentSupply_id,
				dus.StorageZone_id,
				isnull(dus.DocumentUcStr_Price,0) as DocumentUcStr_Price_Original,
				dus.DocumentUcStr_id,
				dus.GoodsUnit_bid
			from
				v_DocumentUcStr dus with (nolock)
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				outer apply (
					select top 1
						i_p_dus.DocumentUcStr_oid
					from
						v_DocumentUcStr i_p_dus with (nolock)
					where
						i_p_dus.DocumentUc_id = :ParentDocumentUc_id and
						i_p_dus.Drug_id = dus.Drug_id and
						i_p_dus.DocumentUcStr_Price = dus.DocumentUcStr_Price and
						isnull(i_p_dus.PrepSeries_id, 0) = isnull(dus.PrepSeries_id, 0)
				) p_dus
				outer apply (
					select top 1
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = p_dus.DocumentUcStr_oid
				) ds
			where
				DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'ParentDocumentUc_id' =>  $parent_doc_data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_arr = $result->result('array');
		}
		if (count($drug_arr) == 0) {
			return array(array('Error_Msg' => 'Список медикаментов пуст'));
		}

		//редактируем записи в регистре
		foreach ($drug_arr as $drug) {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec xp_DrugOstatRegistry_count
					@Contragent_id = :Contragent_id,
					@Org_id = :Org_id,
					@Storage_id = :Storage_id,
					@DrugShipment_id = :DrugShipment_id,
					@Drug_id = :Drug_id,
					@PrepSeries_id = :PrepSeries_id,
					@SubAccountType_id = 1, -- субсчёт доступно
					@Okei_id = :Okei_id,
					@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
					@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
                    @InnerTransaction_Disabled = 1,
					@GoodsUnit_id = :GoodsUnit_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$params = array(
				'Contragent_id' => $doc_data['Contragent_tid'],
				'Org_id' => $doc_data['Org_tid'],
				'Storage_id' => $parent_doc_data['Storage_sid'],
				'DrugShipment_id' => $drug['DrugShipment_id'],
				'Drug_id' => $drug['Drug_id'],
				'PrepSeries_id' => $drug['PrepSeries_id'],
				'Okei_id' => $drug['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $drug['DocumentUcStr_Count'],
				'DrugOstatRegistry_Sum' => $drug['DocumentUcStr_Price']*$drug['DocumentUcStr_Count'],
				'DrugOstatRegistry_Cost' => $drug['DocumentUcStr_Price'],
				'GoodsUnit_id' => $drug['GoodsUnit_bid'],
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->getFirstRowFromQuery($query, $params);

			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					return array(array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
				}
			} else {
				return array(array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
			}

			if(!empty($drug['StorageZone_id'])){
				// записи по зачислению
            	$result = $this->_updateDrugStorageZone(array(
            		'Drug_id' => $drug['Drug_id'],
            		'PrepSeries_id' => $drug['PrepSeries_id'],
            		'DrugShipment_id' => $drug['DrugShipment_id'],
            		'StorageZone_id' => $drug['StorageZone_id'],
            		'DrugStorageZone_Price' => $drug['DocumentUcStr_Price_Original'],
            		'addDrugCount' => $drug['DocumentUcStr_Count'],
            		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
            		'pmUser_id' => $data['pmUser_id']
            	));
            	if (!$this->isSuccessful($result)) {
                    return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения. '.$result[0]['Error_Msg']);
                }
                $result = $this->_commitStorageDrugMove(array(
            		'Drug_id' => $drug['Drug_id'],
            		'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
            		'PrepSeries_id' => $drug['PrepSeries_id'],
            		'DrugShipment_id' => $drug['DrugShipment_id'],
            		'StorageZone_oid' => null,
            		'StorageZone_nid' => $drug['StorageZone_id'],
            		'StorageDrugMove_Price' => $drug['DocumentUcStr_Price_Original'],
            		'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
            		'GoodsUnit_id' => $drug['GoodsUnit_bid'],
            		'pmUser_id' => $data['pmUser_id']
            	));
            	if (!$this->isSuccessful($result)) {
                    return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения. '.$result[0]['Error_Msg']);
                }
            }
		}

		return array(array());
	}


    /**
     * Корректировка регистра остатков при исполнении документа возврата из отделения
     */
    function updateDrugOstatRegistryForDocVozOtd($data) {
        if (!isset($data['DocumentUc_id'])) {
            return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
        }
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        //получаем данные о документе учета
        $doc_data = array();
        $query = "
			select
				isnull(c_sid.Org_id, l_sid.Org_id) as Org_sid,
				isnull(c_tid.Org_id, l_tid.Org_id) as Org_tid,
				du.Contragent_sid,
				du.Contragent_tid,
				du.Storage_sid,
				du.Storage_tid,
				du.DrugFinance_id,
				du.WhsDocumentCostItemType_id
			from
				v_DocumentUc du with (nolock)
				left join v_Contragent c_tid with (nolock) on c_tid.Contragent_id = du.Contragent_tid
				left join v_Lpu l_tid with(nolock) on l_tid.Lpu_id = c_tid.Lpu_id
				inner join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_Lpu l_sid with(nolock) on l_sid.Lpu_id = c_sid.Lpu_id
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
        $result = $this->db->query($query, array(
            'DocumentUc_id' => $data['DocumentUc_id']
        ));
        if (is_object($result)) {
            $res = $result->result('array');
            if (count($res) > 0) {
                $doc_data = $res[0];
            }
        }
        if (count($doc_data) == 0) {
            return array(array('Error_Msg' => 'Документа учета не найден'));
        }

        //получаем строки документа учета
        $drug_arr = array();
        $query = "
			select
				dus.DocumentUcStr_id,
				dus.Drug_id,
				dus.PrepSeries_id,
				dus.DrugFinance_id,
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price,
				ds.DrugShipment_id,
				ds.WhsDocumentSupply_id,
				ds.AccountType_id,
				ds.DrugShipment_pid,
				d.Drug_Name,
				ps.PrepSeries_Ser,
				dus.StorageZone_id,
				dus.DocumentUcStr_id,
				dus.GoodsUnit_bid
			from
				v_DocumentUcStr dus with (nolock)
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
                left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
                left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
				outer apply (
					select top 1
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id,
						i_ds.AccountType_id,
						i_ds.DrugShipment_pid
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				) ds
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
        $result = $this->db->query($query, array(
            'DocumentUc_id' => $data['DocumentUc_id']
        ));
        if (is_object($result)) {
            $drug_arr = $result->result('array');
        }
        if (count($drug_arr) == 0) {
            return array(array('Error_Msg' => 'Список медикаментов пуст'));
        }

        /*//получаем стартовый номер партии
        $query = "
			select
				isnull(max(cast(DrugShipment_Name as bigint)),0) + 1 as DrugShipment_Name
			from
				v_DrugShipment with (nolock)
			where
				DrugShipment_Name not like '%.%' and
				DrugShipment_Name not like '%,%' and
				DrugShipment_Name not like '%e%' and
				len(DrugShipment_Name) <= 18 and
				isnumeric(DrugShipment_Name + 'e0') = 1
		";
        $sh_num = $this->getFirstResultFromQuery($query);

        //запросы для создания партий
        $sh_query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@datetime datetime;

			set @datetime = dbo.tzGetDate();

			exec p_DrugShipment_ins
				@DrugShipment_id = @Res output,
				@DrugShipment_setDT = @datetime,
				@DrugShipment_Name = :DrugShipment_Name,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@AccountType_id = :AccountType_id,
				@DrugShipment_pid = :DrugShipment_pid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

        $shl_query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_DrugShipmentLink_ins
				@DrugShipmentLink_id = @Res output,
				@DrugShipment_id = :DrugShipment_id,
				@DocumentUcStr_id = :DocumentUcStr_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipmentLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";*/

        //при наличии резерва списываем остатки с резерва
        if ($this->haveReserve(array('DocumentUc_id' => $data['DocumentUc_id']))) {

            foreach ($drug_arr as $drug) {
                //создаем новую партию для строки накладной
                //$sh_id = $this->getFirstResultFromQuery($sh_query, array(
                /*$sh_query_compl = getDebugSQL($sh_query, array(
                    'DrugShipment_Name' => $sh_num++,
                    'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                    'AccountType_id' => $drug['AccountType_id'],
                    'DrugShipment_pid' => !empty($drug['DrugShipment_pid']) ? $drug['DrugShipment_pid'] : $drug['DrugShipment_id'], //если родительская партия сама является дочерней, то вписываем в поле идентифкатор её родителя
                    'pmUser_id' => $data['pmUser_id']
                ));
                $sh_id = $this->getFirstResultFromQuery($sh_query_compl);

                //связь партии со строкой накладной
                $shl_id = $this->getFirstResultFromQuery($shl_query, array(
                    'DrugShipment_id' => $sh_id,
                    'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));*/

                //ищем нужные записи в резерве
                $kolvo = $drug['DocumentUcStr_Count'];

                $query = "
					select
		                reserved_dor.Contragent_id,
						reserved_dor.Org_id,
						reserved_dor.Drug_id,
						reserved_dor.PrepSeries_id,
						reserved_dor.Okei_id,
						dorl.DrugOstatRegistryLink_Count as DrugOstatRegistry_Kolvo,
						reserved_dor.DrugOstatRegistry_Sum,
						reserved_dor.DrugOstatRegistry_Cost,
						reserved_dor.GoodsUnit_id,
						dus.DocumentUcStr_id
		            from
		                v_DocumentUcStr dus with (nolock)
		                left join v_DrugOstatRegistryLink dorl with (nolock) on
		                    dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
		                    dorl.DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
		                left join v_DrugOstatRegistry reserved_dor with (nolock) on reserved_dor.DrugOstatRegistry_id = dorl.DrugOstatRegistry_id
		            where
		                dorl.DrugOstatRegistryLink_id is not null and
		                (
	                        :DocumentUcStr_id is not null and
	                        dus.DocumentUcStr_id = :DocumentUcStr_id
		                );
				";

                $res = $this->queryResult($query, array(
                    'DocumentUcStr_id' => $drug['DocumentUcStr_id']
                ));

                if ( is_array($res) ) {
                    if (!empty($res[0]['Error_Msg'])) {
                        return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
                    }

                    /*foreach ($res as $ostat) {
                        if ($kolvo > 0) {
                            //зачисление
                            $kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
                            $sum = $ostat['DrugOstatRegistry_Cost'] > 0 ? $ostat['DrugOstatRegistry_Cost']*$kol : ($ostat['DrugOstatRegistry_Sum']/$ostat['DrugOstatRegistry_Kolvo'])*$kol;

                            $query = "
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);
								exec xp_DrugOstatRegistry_count
									@Contragent_id = :Contragent_id,
									@Org_id = :Org_id,
									@Storage_id = :Storage_id,
									@DrugShipment_id = :DrugShipment_id,
									@Drug_id = :Drug_id,
									@PrepSeries_id = :PrepSeries_id,
									@SubAccountType_id = 1, -- субсчёт доступно
									@Okei_id = :Okei_id,
									@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
									@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
									@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                                @InnerTransaction_Disabled = 1,
									@GoodsUnit_id = :GoodsUnit_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";

                            $q_params = array(
                                'Contragent_id' => $ostat['Contragent_id'],
                                'Org_id' => !empty($ostat['Org_id'])?$doc_data['Org_sid']:null,
                                'Storage_id' => $doc_data['Storage_tid'],
                                'DrugShipment_id' => $sh_id,
                                'Drug_id' => $ostat['Drug_id'],
                                'PrepSeries_id' => $ostat['PrepSeries_id'],
                                'Okei_id' => $ostat['Okei_id'],
                                'DrugOstatRegistry_Kolvo' => $kol,
                                'DrugOstatRegistry_Sum' => $sum,
                                'DrugOstatRegistry_Cost' => $ostat['DrugOstatRegistry_Cost'],
                                'GoodsUnit_id' => $ostat['GoodsUnit_id'],
                                'pmUser_id' => $data['pmUser_id']
                            );

                            //зачисление
                            //echo getDebugSQL($query, $q_params);exit;
                            $result = $this->db->query($query, $q_params);
                            if ( is_object($result) ) {
                                $res = $result->result('array');
                                if (!empty($res[0]['Error_Msg'])) {
                                    return array(0 => array('Error_Msg' => 'Ошибка зачисления остатков'));
                                }
                            } else {
                                return array(0 => array('Error_Msg' => 'Ошибка запроса зачисления остатков'));
                            }
                        }
                    }*/

                    if(!empty($drug['StorageZone_id'])){
                        // записи по списанию
                        $result = $this->_updateDrugStorageZone(array(
                            'Drug_id' => $drug['Drug_id'],
                            'PrepSeries_id' => $drug['PrepSeries_id'],
                            'DrugShipment_id' => $drug['DrugShipment_id'],
                            'StorageZone_id' => $drug['StorageZone_id'],
                            'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                            'removeDrugCount' => $drug['DocumentUcStr_Count'],
                            'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if (!$this->isSuccessful($result)) {
                            return $this->createError(0, 'Ошибка при списании медикамента с места хранения'.$result[0]['Error_Msg']);
                        }
                        /*$result = $this->_commitStorageDrugMove(array(
                            'Drug_id' => $drug['Drug_id'],
                            'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                            'PrepSeries_id' => $drug['PrepSeries_id'],
                            'DrugShipment_id' => $drug['DrugShipment_id'],
                            'StorageZone_nid' => null,
                            'StorageZone_oid' => $drug['StorageZone_id'],
                            'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
                            'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                            'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if (!$this->isSuccessful($result)) {
                            return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
                        }*/
                    }
                } else {
                    return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
                }
            }
            //списание остатков с резерва
            $result = $this->deleteReserve(array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($result['Error_Msg'])) {
                return array(array('Error_Message' => $result['Error_Msg']));
            }
        } else {
            foreach ($drug_arr as $drug) {
                //создаем новую партию для строки накладной
                //$sh_id = $this->getFirstResultFromQuery($sh_query, array(
                /*$sh_query_compl = getDebugSQL($sh_query, array(
                    'DrugShipment_Name' => $sh_num++,
                    'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                    'AccountType_id' => $drug['AccountType_id'],
                    'DrugShipment_pid' => !empty($drug['DrugShipment_pid']) ? $drug['DrugShipment_pid'] : $drug['DrugShipment_id'], //если родительская партия сама является дочерней, то вписываем в поле идентифкатор её родителя
                    'pmUser_id' => $data['pmUser_id']
                ));
                $sh_id = $this->getFirstResultFromQuery($sh_query_compl);

                //связь партии со строкой накладной
                $shl_id = $this->getFirstResultFromQuery($shl_query, array(
                    'DrugShipment_id' => $sh_id,
                    'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));*/

                //ищем нужные записи в регистре и проверяем наличие необходимого количества медикамента
                $kolvo = $drug['DocumentUcStr_Count'];

                $query = "
					select
						dor.Contragent_id,
						dor.Org_id,
						dor.Storage_id,
						dor.DrugShipment_id,
						dor.Drug_id,
						dor.PrepSeries_id,
						dor.Okei_id,
						dor.DrugOstatRegistry_Kolvo,
						dor.DrugOstatRegistry_Sum,
						dor.DrugOstatRegistry_Cost,
						dor.GoodsUnit_id
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_Contragent c with(nolock) on c.Contragent_id = dor.Contragent_id
						left join v_Lpu l with(nolock) on l.Lpu_id = c.Lpu_id
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
					where
						coalesce(dor.Org_id,c.Org_id,l.Org_id,0) = ISNULL(:Org_id,0) and
						dor.Storage_id = :Storage_id and
						dor.Drug_id = :Drug_id and
						dor.DrugShipment_id = :DrugShipment_id and
						sat.SubAccountType_Code = 1 and
						dor.DrugOstatRegistry_Kolvo > 0 and
						(:DocumentUcStr_Price is null or dor.DrugOstatRegistry_Cost = :DocumentUcStr_Price) and
						isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id) and
						(:DrugFinance_id is null or dor.DrugFinance_id = :DrugFinance_id) and
						(:WhsDocumentCostItemType_id is null or dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id);
				";

                $result = $this->db->query($query, array(
                    'Org_id' => $doc_data['Org_sid'],
                    'Storage_id' => $doc_data['Storage_sid'],
                    'Drug_id' => $drug['Drug_id'],
                    'DrugShipment_id' => $drug['DrugShipment_id'],
                    'DocumentUcStr_Price' => $drug['DocumentUcStr_Price'],
                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                    'DefaultGoodsUnit_id' => $default_goods_unit_id,
                    'DrugFinance_id' => $doc_data['DrugFinance_id'] > 0 ? $doc_data['DrugFinance_id'] : $drug['DrugFinance_id'],
                    'WhsDocumentCostItemType_id' => $doc_data['WhsDocumentCostItemType_id']
                ));

                if ( is_object($result) ) {
                    $res = $result->result('array');
                    if (!empty($res[0]['Error_Msg'])) {
                        return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
                    }

                    foreach ($res as $ostat) {
                        if ($kolvo > 0) {
                            //списание
                            $kol = $ostat['DrugOstatRegistry_Kolvo'] <= $kolvo ? $ostat['DrugOstatRegistry_Kolvo'] : $kolvo;
                            $sum = $ostat['DrugOstatRegistry_Cost'] > 0 ? $ostat['DrugOstatRegistry_Cost']*$kol : ($ostat['DrugOstatRegistry_Sum']/$ostat['DrugOstatRegistry_Kolvo'])*$kol;

                            $kolvo -= $kol;

                            $query = "
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);
								exec xp_DrugOstatRegistry_count
									@Contragent_id = :Contragent_id,
									@Org_id = :Org_id,
									@Storage_id = :Storage_id,
									@DrugShipment_id = :DrugShipment_id,
									@Drug_id = :Drug_id,
									@PrepSeries_id = :PrepSeries_id,
									@SubAccountType_id = 1, -- субсчёт доступно
									@Okei_id = :Okei_id,
									@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
									@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
									@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                                @InnerTransaction_Disabled = 1,
									@GoodsUnit_id = :GoodsUnit_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";

                            $q_params = array(
                                'Contragent_id' => $ostat['Contragent_id'],
                                'Org_id' => !empty($ostat['Org_id'])?$doc_data['Org_sid']:null,
                                'Storage_id' => $ostat['Storage_id'],
                                'DrugShipment_id' => $ostat['DrugShipment_id'],
                                'Drug_id' => $ostat['Drug_id'],
                                'PrepSeries_id' => $ostat['PrepSeries_id'],
                                'Okei_id' => $ostat['Okei_id'],
                                'DrugOstatRegistry_Kolvo' => $kol*(-1),
                                'DrugOstatRegistry_Sum' => $sum*(-1),
                                'DrugOstatRegistry_Cost' => $ostat['DrugOstatRegistry_Cost'],
                                'GoodsUnit_id' => $ostat['GoodsUnit_id'],
                                'pmUser_id' => $data['pmUser_id']
                            );

                            $result = $this->db->query($query, $q_params);
                            if ( is_object($result) ) {
                                $res = $result->result('array');
                                if (!empty($res[0]['Error_Msg'])) {
                                    return array(0 => array('Error_Msg' => 'Ошибка списания остатков'));
                                }
                            } else {
                                return array(0 => array('Error_Msg' => 'Ошибка запроса списания остатков'));
                            }

                            //зачисление
                            /*$q_params['Storage_id'] = $doc_data['Storage_tid'];
                            $q_params['DrugShipment_id'] = $sh_id; //идентификатор новой созданной партии
                            $q_params['DrugOstatRegistry_Kolvo'] = $kol;
                            $q_params['DrugOstatRegistry_Sum'] = $sum;
                            //echo getDebugSQL($query, $q_params);exit;
                            $result = $this->db->query($query, $q_params);
                            if ( is_object($result) ) {
                                $res = $result->result('array');
                                if (!empty($res[0]['Error_Msg'])) {
                                    return array(0 => array('Error_Msg' => 'Ошибка зачисления остатков'));
                                }
                            } else {
                                return array(0 => array('Error_Msg' => 'Ошибка запроса зачисления остатков'));
                            }*/
                        }
                    }
                    if(!empty($drug['StorageZone_id'])){
                        // записи по списанию
                        $result = $this->_updateDrugStorageZone(array(
                            'Drug_id' => $drug['Drug_id'],
                            'PrepSeries_id' => $drug['PrepSeries_id'],
                            'DrugShipment_id' => $drug['DrugShipment_id'],
                            'StorageZone_id' => $drug['StorageZone_id'],
                            'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                            'removeDrugCount' => $drug['DocumentUcStr_Count'],
                            'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if (!$this->isSuccessful($result)) {
                            return $this->createError(0, 'Ошибка при списании медикамента с места хранения'.$result[0]['Error_Msg']);
                        }
                        /*$result = $this->_commitStorageDrugMove(array(
                            'Drug_id' => $drug['Drug_id'],
                            'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                            'PrepSeries_id' => $drug['PrepSeries_id'],
                            'DrugShipment_id' => $drug['DrugShipment_id'],
                            'StorageZone_nid' => null,
                            'StorageZone_oid' => $drug['StorageZone_id'],
                            'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
                            'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                            'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if (!$this->isSuccessful($result)) {
                            return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
                        }*/
                    }
                }

                if ($kolvo > 0) {
                    $price_str = number_format($drug['DocumentUcStr_Price'], 2, '.', ' ');
                    return array(0 => array('Error_Msg' => "На остатках поставщика недостаточно медикаментов для списания. Серия: {$drug['PrepSeries_Ser']}. Цена: {$price_str} р. Медикамент: {$drug['Drug_Name']}."));
                }
            }
        }

        return array(array());
    }


    /**
     * Корректировка регистра остатков при исполнении накладной на внутреннее перемещение
     */
    function updateDrugOstatRegistryForDocRazSpis($data) {
        if (!isset($data['DocumentUc_id'])) {
            return array(array('Error_Msg' => 'Не указан идентификатор документа учета'));
        }
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        //получаем данные о документе учета
        $doc_data = array();
        $query = "
			select
				isnull(c_sid.Org_id, l_sid.Org_id) as Org_sid,
				du.Contragent_sid,
				du.Storage_sid,
				du.DocumentUc_didDate,
				post_du.DocumentUc_id as PostDocumentUc_id
			from
				v_DocumentUc du with (nolock)
				inner join v_Contragent c_sid with (nolock) on c_sid.Contragent_id = du.Contragent_sid
				left join v_Lpu l_sid with(nolock) on l_sid.Lpu_id = c_sid.Lpu_id
				outer apply (
				    select top 1
				        i_du.DocumentUc_id
				    from
				        v_DocumentUc i_du with (nolock)
				        left join v_DrugDocumentType i_ddt with (nolock) on i_ddt.DrugDocumentType_id = i_du.DrugDocumentType_id
				    where
				        i_du.DocumentUc_pid = du.DocumentUc_id and
				        i_ddt.DrugDocumentType_Code = 35 -- 35 - Разукомплектация постановка на учет
				    order by
				        i_du.DocumentUc_id
				) post_du
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
        $result = $this->db->query($query, array(
            'DocumentUc_id' => $data['DocumentUc_id']
        ));
        if (is_object($result)) {
            $res = $result->result('array');
            if (count($res) > 0) {
                $doc_data = $res[0];
            }
        }
        if (count($doc_data) == 0) {
            return array(array('Error_Msg' => 'Документа учета не найден'));
        }

        //получаем строки документа учета
        $drug_arr = array();
        $query = "
			select
				dus.DocumentUcStr_id,
				dus.Drug_id,
				dus.PrepSeries_id,
				dus.DrugFinance_id,
				dus.StorageZone_id,
				dus.GoodsUnit_id,
				dus.GoodsUnit_bid,
				isnull(dus.DocumentUcStr_Count, 0) as DocumentUcStr_Count,
				(
					case
						when
							isnull(isnds.YesNo_Code, 0) = 1
						then
							isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)
						else
							cast(isnull(isnull(dus.DocumentUcStr_PriceR, dus.DocumentUcStr_Price), 0)*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(12,2))
					end
				) as DocumentUcStr_Price,
				post_dus.DocumentUcStr_id as PostDocumentUcStr_id,
				post_dus.GoodsUnit_id as PostGoodsUnit_id,
				post_dus.GoodsUnit_bid as PostGoodsUnit_bid,
				post_dus.StorageZone_id as PostStorageZone_id,
				post_dus.DocumentUcStr_Count as PostDocumentUcStr_Count,
                post_dus.DocumentUcStr_Price as PostDocumentUcStr_Price,
                post_dus.DocumentUcStr_Sum as PostDocumentUcStr_Sum,
                post_dus.DocumentUcStr_SumNds as PostDocumentUcStr_SumNds,
				ds.DrugShipment_id,
				ds.WhsDocumentSupply_id,
				ds.AccountType_id,
				ds.DrugShipment_pid,
				d.Drug_Name,
				ps.PrepSeries_Ser
			from
				v_DocumentUcStr dus with (nolock)
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
                left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
                left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
                outer apply (
                    select top 1
                        i_dus.DocumentUcStr_id,
                        i_dus.GoodsUnit_id,
                        i_dus.GoodsUnit_bid,
                        i_dus.StorageZone_id,
                        i_dus.DocumentUcStr_Count,
                        i_dus.DocumentUcStr_Price,
                        i_dus.DocumentUcStr_Sum,
                        i_dus.DocumentUcStr_SumNds
                    from
                        v_DocumentUcStr i_dus with (nolock)
                    where
                        i_dus.DocumentUc_id = :PostDocumentUc_id and
                        i_dus.DocumentUcStr_sid = dus.DocumentUcStr_id
                    order by
                        i_dus.DocumentUcStr_id
                ) post_dus
				outer apply (
					select top 1
						i_dsl.DrugShipment_id,
						i_ds.WhsDocumentSupply_id,
						i_ds.AccountType_id,
						i_ds.DrugShipment_pid
					from
						v_DrugShipmentLink i_dsl with (nolock)
						left join v_DrugShipment i_ds with (nolock) on i_ds.DrugShipment_id = i_dsl.DrugShipment_id
					where
						i_dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				) ds
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
        $result = $this->db->query($query, array(
            'DocumentUc_id' => $data['DocumentUc_id'],
            'PostDocumentUc_id' => $doc_data['PostDocumentUc_id']
        ));
        if (is_object($result)) {
            $drug_arr = $result->result('array');
        }
        if (count($drug_arr) == 0) {
            return array(array('Error_Msg' => 'Список медикаментов пуст'));
        }

        //запросы для создания партий
        $sh_query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_DrugShipment_ins
				@DrugShipment_id = @Res output,
				@DrugShipment_setDT = :DrugShipment_setDT,
				@DrugShipment_Name = :DrugShipment_Name,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@AccountType_id = :AccountType_id,
				@DrugShipment_pid = :DrugShipment_pid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

        $shl_query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_DrugShipmentLink_ins
				@DrugShipmentLink_id = @Res output,
				@DrugShipment_id = :DrugShipment_id,
				@DocumentUcStr_id = :DocumentUcStr_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipmentLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

        //операции с партиями
        for ($i = 0; $i < count($drug_arr); $i++) {
            //создаем новую партию для строки документа
            $sh_query_compl = getDebugSQL($sh_query, array(
                'DrugShipment_setDT' => $doc_data['DocumentUc_didDate'], // дата партии соотвтествует дате выполнения родительского документа
                'DrugShipment_Name' => 'tmp_'.$drug_arr[$i]['PostDocumentUcStr_id'], //временное наименование
                'WhsDocumentSupply_id' => $drug_arr[$i]['WhsDocumentSupply_id'],
                'AccountType_id' => $drug_arr[$i]['AccountType_id'],
                'DrugShipment_pid' => !empty($drug_arr[$i]['DrugShipment_pid']) ? $drug_arr[$i]['DrugShipment_pid'] : $drug_arr[$i]['DrugShipment_id'], //если родительская партия сама является дочерней, то вписываем в поле идентифкатор её родителя
                'pmUser_id' => $data['pmUser_id']
            ));
            $sh_id = $this->getFirstResultFromQuery($sh_query_compl);

            if ($sh_id > 0) {
                $drug_arr[$i]['PostDrugShipment_id'] = $sh_id;

                //обновление наименования партии
                $sh_res = $this->saveObject('DrugShipment', array(
                    'DrugShipment_id' => $sh_id,
                    'DrugShipment_Name' => $sh_id
                ));

                //связь партии со строкой накладной
                $shl_id = $this->getFirstResultFromQuery($shl_query, array(
                    'DrugShipment_id' => $sh_id,
                    'DocumentUcStr_id' => $drug_arr[$i]['PostDocumentUcStr_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
            } else {
                return array(array('Error_Msg' => 'Ошибка создания партии'));
            }
        }

        //операции с регистром остатков
        if ($this->haveReserve(array('DocumentUc_id' => $data['DocumentUc_id']))) { //при наличии резерва списываем остатки с резерва
            foreach ($drug_arr as $drug) {
                //ищем нужные записи в резерве
                $query = "
					select
		                reserved_dor.Contragent_id,
						reserved_dor.Org_id,
						reserved_dor.Drug_id,
						reserved_dor.PrepSeries_id,
						reserved_dor.Okei_id
		            from
		                v_DocumentUcStr dus with (nolock)
		                left join v_DrugOstatRegistryLink dorl with (nolock) on
		                    dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
		                    dorl.DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
		                left join v_DrugOstatRegistry reserved_dor with (nolock) on reserved_dor.DrugOstatRegistry_id = dorl.DrugOstatRegistry_id
		            where
		                dorl.DrugOstatRegistryLink_id is not null and
		                (
	                        :DocumentUcStr_id is not null and
	                        dus.DocumentUcStr_id = :DocumentUcStr_id
		                );
				";
                $ostat = $this->getFirstRowFromQuery($query, array(
                    'DocumentUcStr_id' => $drug['DocumentUcStr_id']
                ));

                if (is_array($ostat) && count($ostat) > 0) {
                    //зачисление
                    $query = "
                        declare
                            @ErrCode int,
                            @ErrMessage varchar(4000);
                        exec xp_DrugOstatRegistry_count
                            @Contragent_id = :Contragent_id,
                            @Org_id = :Org_id,
                            @Storage_id = :Storage_id,
                            @DrugShipment_id = :DrugShipment_id,
                            @Drug_id = :Drug_id,
                            @PrepSeries_id = :PrepSeries_id,
                            @SubAccountType_id = 1, -- субсчёт доступно
                            @Okei_id = :Okei_id,
                            @DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
                            @DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
                            @DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
                            @InnerTransaction_Disabled = 1,
                            @GoodsUnit_id = :GoodsUnit_id,
                            @pmUser_id = :pmUser_id,
                            @Error_Code = @ErrCode output,
                            @Error_Message = @ErrMessage output;
                        select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                    ";

                    $q_params = array(
                        'Contragent_id' => $ostat['Contragent_id'],
                        'Org_id' => !empty($ostat['Org_id'])?$doc_data['Org_sid']:null,
                        'Storage_id' => $doc_data['Storage_sid'],
                        'DrugShipment_id' => $drug['PostDrugShipment_id'],
                        'Drug_id' => $ostat['Drug_id'],
                        'PrepSeries_id' => $ostat['PrepSeries_id'],
                        'Okei_id' => $ostat['Okei_id'],
                        'DrugOstatRegistry_Kolvo' => $drug['PostDocumentUcStr_Count'],
                        'DrugOstatRegistry_Sum' => $drug['PostDocumentUcStr_Count']*$drug['PostDocumentUcStr_Price'],
                        'DrugOstatRegistry_Cost' => $drug['PostDocumentUcStr_Price'],
                        'GoodsUnit_id' => $drug['PostGoodsUnit_bid'],
                        'pmUser_id' => $data['pmUser_id']
                    );

                    //зачисление
                            //echo getDebugSQL($query, $q_params);exit;
                    $result = $this->db->query($query, $q_params);
                    if ( is_object($result) ) {
                        $res = $result->result('array');
                        if (!empty($res[0]['Error_Msg'])) {
                            return array(0 => array('Error_Msg' => 'Ошибка зачисления остатков'));
                        }
                    } else {
                        return array(0 => array('Error_Msg' => 'Ошибка запроса зачисления остатков'));
                    }
                } else {
                    return array(0 => array('Error_Msg' => 'Ошибка создания регистра остатков'));
                }
            }
            //списание остатков с резерва
            $result = $this->deleteReserve(array(
                'DocumentUc_id' => $data['DocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($result['Error_Msg'])) {
                return array(array('Error_Message' => $result['Error_Msg']));
            }
        } else {
            foreach ($drug_arr as $drug) {
                //ищем нужные записи в регистре и проверяем наличие необходимого количества медикамента
                $query = "
					select
						dor.Contragent_id,
						dor.Org_id,
						dor.Storage_id,
						dor.DrugShipment_id,
						dor.Drug_id,
						dor.PrepSeries_id,
						dor.Okei_id,
						dor.DrugOstatRegistry_Kolvo,
						dor.DrugOstatRegistry_Sum,
						dor.DrugOstatRegistry_Cost,
						dor.GoodsUnit_id
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_Contragent c with(nolock) on c.Contragent_id = dor.Contragent_id
						left join v_Lpu l with(nolock) on l.Lpu_id = c.Lpu_id
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
					where
						coalesce(dor.Org_id,c.Org_id,l.Org_id,0) = ISNULL(:Org_id,0) and
						dor.Storage_id = :Storage_id and
						dor.Drug_id = :Drug_id and
						dor.DrugShipment_id = :DrugShipment_id and
						sat.SubAccountType_Code = 1 and
						dor.DrugOstatRegistry_Kolvo > :DocumentUcStr_Count and
						(:DocumentUcStr_Price is null or dor.DrugOstatRegistry_Cost = :DocumentUcStr_Price) and
						isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id) and
						(:DrugFinance_id is null or dor.DrugFinance_id = :DrugFinance_id) and
						(:WhsDocumentCostItemType_id is null or dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id);
				";

                $ostat = $this->getFirstRowFromQuery($query, array(
                    'Org_id' => $doc_data['Org_sid'],
                    'Storage_id' => $doc_data['Storage_sid'],
                    'Drug_id' => $drug['Drug_id'],
                    'DrugShipment_id' => $drug['DrugShipment_id'],
                    'DocumentUcStr_Count' => $drug['DocumentUcStr_Count'],
                    'DocumentUcStr_Price' => $drug['DocumentUcStr_Price'],
                    'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                    'DefaultGoodsUnit_id' => $default_goods_unit_id,
                    'DrugFinance_id' => $doc_data['DrugFinance_id'] > 0 ? $doc_data['DrugFinance_id'] : $drug['DrugFinance_id'],
                    'WhsDocumentCostItemType_id' => $doc_data['WhsDocumentCostItemType_id']
                ));

                if (!empty($ostat['DrugOstatRegistry_Kolvo'])) {
                    //списание
                    $kol = $ostat['DocumentUcStr_Count'];
                    $sum = $ostat['DrugOstatRegistry_Cost'] > 0 ? $ostat['DrugOstatRegistry_Cost']*$kol : ($ostat['DrugOstatRegistry_Sum']/$ostat['DrugOstatRegistry_Kolvo'])*$kol;

                    $query = "
                        declare
                            @ErrCode int,
                            @ErrMessage varchar(4000);
                        exec xp_DrugOstatRegistry_count
                            @Contragent_id = :Contragent_id,
                            @Org_id = :Org_id,
                            @Storage_id = :Storage_id,
                            @DrugShipment_id = :DrugShipment_id,
                            @Drug_id = :Drug_id,
                            @PrepSeries_id = :PrepSeries_id,
                            @SubAccountType_id = 1, -- субсчёт доступно
                            @Okei_id = :Okei_id,
                            @DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
                            @DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
                            @DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
                            @InnerTransaction_Disabled = 1,
                            @GoodsUnit_id = :GoodsUnit_id,
                            @pmUser_id = :pmUser_id,
                            @Error_Code = @ErrCode output,
                            @Error_Message = @ErrMessage output;
                        select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                    ";

                    $q_params = array(
                        'Contragent_id' => $ostat['Contragent_id'],
                        'Org_id' => !empty($ostat['Org_id'])?$doc_data['Org_sid']:null,
                        'Storage_id' => $ostat['Storage_id'],
                        'DrugShipment_id' => $ostat['DrugShipment_id'],
                        'Drug_id' => $ostat['Drug_id'],
                        'PrepSeries_id' => $ostat['PrepSeries_id'],
                        'Okei_id' => $ostat['Okei_id'],
                        'DrugOstatRegistry_Kolvo' => $kol*(-1),
                        'DrugOstatRegistry_Sum' => $sum*(-1),
                        'DrugOstatRegistry_Cost' => $ostat['DrugOstatRegistry_Cost'],
                        'GoodsUnit_id' => $ostat['GoodsUnit_id'],
                        'pmUser_id' => $data['pmUser_id']
                    );

                    $result = $this->db->query($query, $q_params);
                    if ( is_object($result) ) {
                        $res = $result->result('array');
                        if (!empty($res[0]['Error_Msg'])) {
                            return array(0 => array('Error_Msg' => 'Ошибка списания остатков'));
                        }
                    } else {
                        return array(0 => array('Error_Msg' => 'Ошибка запроса списания остатков'));
                    }

                    //зачисление
                    $q_params['Storage_id'] = $doc_data['Storage_tid'];
                    $q_params['DrugShipment_id'] = $sh_id; //идентификатор новой созданной партии
                    $q_params['DrugOstatRegistry_Kolvo'] = $kol;
                    $q_params['DrugOstatRegistry_Sum'] = $sum;

                    $result = $this->getFirstRowFromQuery($query, $q_params);
                    if (is_array($result) && count($result) > 0) {
                        if (!empty($result['Error_Msg'])) {
                            return array(0 => array('Error_Msg' => 'Ошибка зачисления остатков'));
                        }
                    } else {
                        return array(0 => array('Error_Msg' => 'Ошибка запроса зачисления остатков'));
                    }
                } else {
                    $price_str = number_format($drug['DocumentUcStr_Price'], 2, '.', ' ');
                    return array(0 => array('Error_Msg' => "На остатках поставщика недостаточно медикаментов для списания. Серия: {$drug['PrepSeries_Ser']}. Цена: {$price_str} р. Медикамент: {$drug['Drug_Name']}."));
                }
            }
        }

        //операции с местами хранения
        foreach ($drug_arr as $drug) {
            if ($drug['StorageZone_id'] != $drug['PostStorageZone_id']) { //если место хранения прихода и расхода различаются, значит нужно оформить движение медикаментов
                if (!empty($drug['StorageZone_id'])) {
                    //списание
                    $result = $this->_updateDrugStorageZone(array(
                        'Drug_id' => $drug['Drug_id'],
                        'PrepSeries_id' => $drug['PrepSeries_id'],
                        'DrugShipment_id' => $drug['DrugShipment_id'],
                        'StorageZone_id' => $drug['StorageZone_id'],
                        'DrugStorageZone_Price' => $drug['DocumentUcStr_Price'],
                        'removeDrugCount' => $drug['DocumentUcStr_Count'],
                        'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!$this->isSuccessful($result)) {
                        return $this->createError(0, 'Ошибка при списании медикамента с места хранения'.$result[0]['Error_Msg']);
                    }
                    $result = $this->_commitStorageDrugMove(array(
                        'Drug_id' => $drug['Drug_id'],
                        'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
                        'PrepSeries_id' => $drug['PrepSeries_id'],
                        'DrugShipment_id' => $drug['DrugShipment_id'],
                        'StorageZone_oid' => $drug['StorageZone_id'],
                        'StorageZone_nid' => null,
                        'StorageDrugMove_Price' => $drug['DocumentUcStr_Price'],
                        'StorageDrugMove_Count' => $drug['DocumentUcStr_Count'],
                        'GoodsUnit_id' => $drug['GoodsUnit_bid'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!$this->isSuccessful($result)) {
                        return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
                    }
                }

                if (!empty($drug['PostStorageZone_id'])) {
                    //зачисление
                    $result = $this->_updateDrugStorageZone(array(
                        'Drug_id' => $drug['Drug_id'],
                        'PrepSeries_id' => $drug['PrepSeries_id'],
                        'DrugShipment_id' => $drug['PostDrugShipment_id'],
                        'StorageZone_id' => $drug['PostStorageZone_id'],
                        'DrugStorageZone_Price' => $drug['PostDocumentUcStr_Price'],
                        'addDrugCount' => $drug['PostDocumentUcStr_Count'],
                        'GoodsUnit_id' => $drug['PostGoodsUnit_bid'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!$this->isSuccessful($result)) {
                        return $this->createError(0, 'Ошибка при зачислении медикамента на место хранения'.$result[0]['Error_Msg']);
                    }
                    $result = $this->_commitStorageDrugMove(array(
                        'Drug_id' => $drug['Drug_id'],
                        'DocumentUcStr_id' => $drug['PostDocumentUcStr_id'],
                        'PrepSeries_id' => $drug['PrepSeries_id'],
                        'DrugShipment_id' => $drug['PostDrugShipment_id'],
                        'StorageZone_oid' => null,
                        'StorageZone_nid' => $drug['PostStorageZone_id'],
                        'StorageDrugMove_Price' => $drug['PostDocumentUcStr_Price'],
                        'StorageDrugMove_Count' => $drug['PostDocumentUcStr_Count'],
                        'GoodsUnit_id' => $drug['PostGoodsUnit_bid'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!$this->isSuccessful($result)) {
                        return $this->createError(0, 'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
                    }
                }
            }
        }

        //return array(array('Error_Msg' => 'Отладка'));
        return array(array());
    }


	/**
	 * Создание электронной накладной
	 */
	function createDokNakByDocumentUc($data) {
        $this->load->model('PMMediaData_model', 'PMMediaData_model');

		$new_doc_id = null;
		$error = array();
		$doc_data = array();
		$drug_data = array();

		//получение данных документа учета
		$query = "
			select
				du.WhsDocumentUc_id,
				du.Contragent_tid,
				tcon.Org_id as Org_tid,
				dul.DocumentUcLink_id,
				du.DocumentUc_didDate
			from
				v_DocumentUc du with (nolock)
				left join v_Contragent tcon with (nolock) on tcon.Contragent_id = du.Contragent_tid
				outer apply (
				    select top 1
				        i_dul.DocumentUcLink_id
				    from
				        v_DocumentUcLink i_dul with (nolock)
				    where
				        i_dul.DocumentUc_id = du.DocumentUc_id
				    order by
				        i_dul.DocumentUcLink_id
				) dul
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (!is_array($doc_data) || count($doc_data) < 1) {
			$error[] = 'Не удалось получить данные о документе учета.';
		}

		//получение списка медикаментов
		$query = "
			select
				dus.DocumentUcStr_id,
				ds.WhsDocumentSupply_id,
				ds.DrugShipment_pid,
				ds.DrugShipment_id,
				ds.AccountType_id,
				dus.Drug_id
			from
				v_DocumentUcStr dus with (nolock)
				left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
			where
				dus.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$drug_data = $result->result('array');
		}

		if (!is_array($doc_data) || count($doc_data) < 1) {
			$error[] = 'Не удалось получить данные о документе учета.';
		}

		//копирование документа учета
		if (count($error) == 0) {
			$response = $this->copyObject('DocumentUc', array(
				'DocumentUc_id' => $data['DocumentUc_id'],
				'DocumentUc_pid' => $data['DocumentUc_id'],
				'Contragent_id' => $doc_data['Contragent_tid'],
				'Org_id' => $doc_data['Org_tid'],
				'DrugDocumentType_id' => $this->getObjectIdByCode('DrugDocumentType', 6), //6 - Приходная накладная;
				'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 1), //1 - Новый;
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			} else {
				$new_doc_id = $response['DocumentUc_id'];
			}
		}

        //копирование примечаний
        if (count($error) == 0) {
            $query = "
                select
                    dsv.DocumentStrValues_id
                from
                    v_DocumentStrValues dsv with (nolock)
                    left join v_DocumentStrPropType dspt with (nolock) on dspt.DocumentStrPropType_id = dsv.DocumentStrPropType_id
                    left join v_DocumentStrType dst with (nolock) on dst.DocumentStrType_id = dsv.DocumentStrType_id
                where
                    dspt.DocumentStrPropType_Code = 1 and --Примечание
					dst.DocumentStrType_Code = 1 and --Документ учета DocumentUc
					dsv.Document_id = :DocumentUc_id;
            ";
            $note_list = $this->queryList($query, array(
                'DocumentUc_id' => $data['DocumentUc_id']
            ));
            if (is_array($note_list)) {
                foreach($note_list as $note_id) {
                    $response = $this->copyObject('DocumentStrValues', array(
                        'DocumentStrValues_id' => $note_id,
                        'Document_id' => $new_doc_id,
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                        break;
                    }
                }
            }
        }

		//запросы для создания партий
		$sh_query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_DrugShipment_ins
				@DrugShipment_id = @Res output,
				@DrugShipment_setDT = :DrugShipment_setDT,
				@DrugShipment_Name = :DrugShipment_Name,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@AccountType_id = :AccountType_id,
				@DrugShipment_pid = :DrugShipment_pid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$shl_query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_DrugShipmentLink_ins
				@DrugShipmentLink_id = @Res output,
				@DrugShipment_id = :DrugShipment_id,
				@DocumentUcStr_id = :DocumentUcStr_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipmentLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//копирование списка медикаментов
		if (count($error) == 0) {
			foreach($drug_data as $drug) {
				$response = $this->copyObject('DocumentUcStr', array(
					'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
					'DocumentUcStr_oid' => null,
					'DocumentUc_id' => $new_doc_id,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($response['Error_Msg'])) {
					$error[] = $response['Error_Msg'];
					break;
				} else if (!empty($response['DocumentUcStr_id'])) {
                    //создание партии
                    $sh_query_compl = getDebugSQL($sh_query, array( //при передаче запроса и параметров, почемуто отказывается выплдняться запрос на последней итеррации
                        'DrugShipment_setDT' => $doc_data['DocumentUc_didDate'], // дата партии соотвтествует дате выполнения родительского документа
                        'DrugShipment_Name' => 'tmp_'.$response['DocumentUcStr_id'], //временное наименование
                        'WhsDocumentSupply_id' => $drug['WhsDocumentSupply_id'],
                        'AccountType_id' => $drug['AccountType_id'],
                        'DrugShipment_pid' => !empty($drug['DrugShipment_pid']) ? $drug['DrugShipment_pid'] : $drug['DrugShipment_id'], //если родительская партия сама является дочерней, то вписываем в поле идентифкатор её родителя
                        'pmUser_id' => $data['session']['pmuser_id']
                    ));
					$sh_id = $this->getFirstResultFromQuery($sh_query_compl);

                    if ($sh_id > 0) {
                        //обновление наименования партии
                        $sh_res = $this->saveObject('DrugShipment', array(
                            'DrugShipment_id' => $sh_id,
                            'DrugShipment_Name' => $sh_id
                        ));

                        //связь партии со строкой накладной
                        $shl_id = $this->getFirstResultFromQuery($shl_query, array(
                            'DrugShipment_id' => $sh_id,
                            'DocumentUcStr_id' => $response['DocumentUcStr_id'],
                            'pmUser_id' => $data['pmUser_id']
                        ));
                    } else {
                        $error[] = 'Ошибка создания партии';
                    }

                    //копирование списка файлов
                    $query = "
                        select
                            pmd.pmMediaData_id
                        from
                            v_pmMediaData pmd with (nolock)
                        where
                            pmd.pmMediaData_ObjectName = 'DocumentUcStr' and
                            pmd.pmMediaData_ObjectID = :pmMediaData_ObjectID;
                    ";
                    $file_id_arr = $this->queryList($query, array(
                        'pmMediaData_ObjectID' => $drug['DocumentUcStr_id']
                    ));
                    $response = $this->PMMediaData_model->copypmMediaData($file_id_arr, array(
                        'pmMediaData_ObjectID' => $response['DocumentUcStr_id']
                    ));
                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                        break;
                    }
				}

				//добавляем партию в данные, чтобы использовать при зачислении на субсчет в Пути для организации-получателя
				if(!isset($data['newDrugShipments'])) {
					$data['newDrugShipments'] = array();
				}
				$data['newDrugShipments'][$drug['DocumentUcStr_id']] = $sh_id;
			}
		}

        //копирование связей документа с ЛПУ и подразделением
        if (count($error) == 0 && !empty($doc_data['DocumentUcLink_id'])) {
            $response = $this->copyObject('DocumentUcLink', array(
                'DocumentUcLink_id' => $doc_data['DocumentUcLink_id'],
                'DocumentUc_id' => $new_doc_id,
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            }
        }

		if (count($error) > 0) {
			return array(array('Error_Msg' => $error[0]));
		} else {
			return $data;
		}
	}


	/**
	 * Создание электронной возвратной накладной (приходной)
	 */
	function createDocVozNakPByDocumentUc($data) {
		$new_doc_id = null;
		$storage_id = null;
		$error = array();
		$doc_data = array();
		$drug_data = array();


		//получение данных документа учета
		$query = "
			select
				du.WhsDocumentUc_id,
				du.Contragent_tid,
				tcon.Org_id as Org_tid,
				dul.DocumentUcLink_id
			from
				v_DocumentUc du with (nolock)
				left join v_Contragent tcon with (nolock) on tcon.Contragent_id = du.Contragent_tid
				outer apply (
				    select top 1
				        i_dul.DocumentUcLink_id
				    from
				        v_DocumentUcLink i_dul with (nolock)
				    where
				        i_dul.DocumentUc_id = du.DocumentUc_id
				    order by
				        i_dul.DocumentUcLink_id
				) dul
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (!is_array($doc_data) || count($doc_data) < 1) {
			$error[] = 'Не удалось получить данные о документе учета.';
		}

		//поиск склада
		$query = "
			declare @DocumentUc_id bigint = :DocumentUc_id; -- id возвратной накладной (расходной)

			-- id приходной накладной
			set @DocumentUc_id = (select DocumentUc_pid from DocumentUc with (nolock) where DocumentUc_id = @DocumentUc_id);

			-- id расходной накладной
			set @DocumentUc_id = (select DocumentUc_pid from DocumentUc with (nolock) where DocumentUc_id = @DocumentUc_id);

			select Storage_sid as Storage_id from DocumentUc with (nolock) where DocumentUc_id = @DocumentUc_id;
		";
		$storage_id = $this->getFirstResultFromQuery($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if ($storage_id > 0) {
			//получение списка медикаментов
			$query = "
				select
					dus.DocumentUcStr_id,
					ds.WhsDocumentSupply_id
				from
					v_DocumentUcStr dus with (nolock)
					left join v_DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
					left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsl.DrugShipment_id
				where
					dus.DocumentUc_id = :DocumentUc_id;
			";
			$result = $this->db->query($query, array(
				'DocumentUc_id' => $data['DocumentUc_id']
			));
			if (is_object($result)) {
				$drug_data = $result->result('array');
			}

			if (!is_array($doc_data) || count($doc_data) < 1) {
				$error[] = 'Не удалось получить данные о документе учета.';
			}

			//копирование документа учета
			if (count($error) == 0) {
				$response = $this->copyObject('DocumentUc', array(
					'DocumentUc_id' => $data['DocumentUc_id'],
					'DocumentUc_pid' => $data['DocumentUc_id'],
					'Contragent_id' => $doc_data['Contragent_tid'],
					'Org_id' => $doc_data['Org_tid'],
					'Storage_sid' => null,
					'Storage_tid' => $storage_id,
					'DrugDocumentType_id' => $this->getObjectIdByCode('DrugDocumentType', 18), //18 - Возвратная накладная (приходная);
					'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 1), //1 - Новый;
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($response['Error_Msg'])) {
					$error[] = $response['Error_Msg'];
				} else {
					$new_doc_id = $response['DocumentUc_id'];
				}
			}

            //копирование примечаний
            if (count($error) == 0) {
                $query = "
                    select
                        dsv.DocumentStrValues_id
                    from
                        v_DocumentStrValues dsv with (nolock)
                        left join v_DocumentStrPropType dspt with (nolock) on dspt.DocumentStrPropType_id = dsv.DocumentStrPropType_id
                        left join v_DocumentStrType dst with (nolock) on dst.DocumentStrType_id = dsv.DocumentStrType_id
                    where
                        dspt.DocumentStrPropType_Code = 1 and --Примечание
                        dst.DocumentStrType_Code = 1 and --Документ учета DocumentUc
                        dsv.Document_id = :DocumentUc_id;
                ";
                $note_list = $this->queryList($query, array(
                    'DocumentUc_id' => $data['DocumentUc_id']
                ));
                if (is_array($note_list)) {
                    foreach($note_list as $note_id) {
                        $response = $this->copyObject('DocumentStrValues', array(
                            'DocumentStrValues_id' => $note_id,
                            'Document_id' => $new_doc_id,
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $error[] = $response['Error_Msg'];
                            break;
                        }
                    }
                }
            }

			//копирование списка медикаментов
			if (count($error) == 0) {
				foreach($drug_data as $drug) {
					$response = $this->copyObject('DocumentUcStr', array(
						'DocumentUcStr_id' => $drug['DocumentUcStr_id'],
						'DocumentUc_id' => $new_doc_id,
						'pmUser_id' => $data['pmUser_id']
					));
					if (!empty($response['Error_Msg'])) {
						$error[] = $response['Error_Msg'];
						break;
					}
				}
			}

            //копирование связей документа с ЛПУ и подразделением
            if (count($error) == 0 && !empty($doc_data['DocumentUcLink_id'])) {
                $response = $this->copyObject('DocumentUcLink', array(
                    'DocumentUcLink_id' => $doc_data['DocumentUcLink_id'],
                    'DocumentUc_id' => $new_doc_id,
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
		}

		if (count($error) > 0) {
			return array(array('Error_Msg' => $error[0]));
		} else {
			return array(array());
		}
	}


	/**
	 * Проверка наименование партии на уникальность
	 */
	function checkDrugShipmentName($data) {
		$query  = "
			select
				count(ds.DrugShipment_id) as cnt
			from
				v_DrugShipment ds with (nolock)
				left join v_DrugShipmentLink dsl with (nolock) on dsl.DrugShipment_id = ds.DrugShipment_id
			where
				ds.DrugShipment_Name = :DrugShipment_Name and
				(
					:DocumentUcStr_id is null or
					dsl.DocumentUcStr_id is null or
					dsl.DocumentUcStr_id <> :DocumentUcStr_id
				)
		";
		$result = $this->getFirstResultFromQuery($query, $data);

		return array(array('Check_Result' => $result == 0));
	}

	/**
	 * Сохранение списка медикаментов в ГК
	 */
	function saveDrugListToWhsDocumentSupply($data) {
		$sup_data = array();
		$spec_data = array();

		// Получение данных ГК
		$query = "
			select
				wds.WhsDocumentSupply_id,
				wdst.WhsDocumentStatusType_Code
			from
				v_WhsDocumentSupply wds with (nolock)
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
			where
				wds.WhsDocumentUc_id = :WhsDocumentUc_id;
		";
		$sup_data = $this->getFirstRowFromQuery($query, array(
			'WhsDocumentUc_id' => $data['WhsDocumentUc_id']
		));
		if (!$sup_data || count($sup_data) < 1) {
			return array(array('Error_Msg' => 'Не удалось получить данные госконтракта.'));
		}

		// Рассчет стартового номера позиции
		$query = "
			select
				isnull(max(wdss.WhsDocumentSupplySpec_PosCode), 0) as pos
			from
				v_WhsDocumentSupply wds with (nolock)
				left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
			where
				wds.WhsDocumentUc_id = :WhsDocumentUc_id and
				wdss.WhsDocumentSupplySpec_PosCode not like '%.%' and
				wdss.WhsDocumentSupplySpec_PosCode not like '%,%' and
				wdss.WhsDocumentSupplySpec_PosCode not like '%e%' and
				isnumeric(wdss.WhsDocumentSupplySpec_PosCode) = 1 and
				len(wdss.WhsDocumentSupplySpec_PosCode) <= 18;

		";
		$pos = $this->getFirstResultFromQuery($query, array(
			'WhsDocumentUc_id' => $data['WhsDocumentUc_id']
		));

		$life_persent = null;
		if (is_array($data['options']['globals']) && !empty($data['options']['globals']['farmacy_remainig_exp_date_less_2_years'])) {
			$life_persent = $data['options']['globals']['farmacy_remainig_exp_date_less_2_years'];
		}

		$query = "
			declare
				@Okei_id bigint;

			set @Okei_id = (select Okei_id from v_Okei with (nolock) where Okei_Code = 778); --Упаковка

			select
				wdss.WhsDocumentSupplySpec_id,
				isnull(wdss.WhsDocumentSupply_id, wds.WhsDocumentSupply_id) as WhsDocumentSupply_id,
				wdss.WhsDocumentSupplySpec_PosCode,
				isnull(wdss.Drug_id, dus.Drug_id) as Drug_id,
				isnull(wdss.DrugComplexMnn_id, d.DrugComplexMnn_id) as DrugComplexMnn_id,
				isnull(wdss.FIRMNAMES_id, f.NAMEID) as FIRMNAMES_id,
				wdss.WhsDocumentSupplySpec_KolvoForm,
				wdss.DRUGPACK_id,
				isnull(wdss.Okei_id, @Okei_id) as Okei_id,
				cast((isnull(wdss.WhsDocumentSupplySpec_KolvoUnit, 0)+dus.DocumentUcStr_Count) as decimal(10,4)) as WhsDocumentSupplySpec_KolvoUnit,
				wdss.WhsDocumentSupplySpec_Count,
				case when wdss.WhsDocumentSupplySpec_Price is null then price.price else wdss.WhsDocumentSupplySpec_Price end as WhsDocumentSupplySpec_Price,
				isnull(wdss.WhsDocumentSupplySpec_NDS, dn.DrugNds_Code) as WhsDocumentSupplySpec_NDS,
				case when wdss.WhsDocumentSupplySpec_PriceNDS is null
					then cast((isnull(wdss.WhsDocumentSupplySpec_KolvoUnit, 0)+dus.DocumentUcStr_Count)*price.price_nds as decimal(30,4))
					else cast((isnull(wdss.WhsDocumentSupplySpec_KolvoUnit, 0)+dus.DocumentUcStr_Count)*wdss.WhsDocumentSupplySpec_PriceNDS as decimal(12,4))
				end as WhsDocumentSupplySpec_SumNDS,
				case when wdss.WhsDocumentSupplySpec_PriceNDS is null then price.price_nds else wdss.WhsDocumentSupplySpec_PriceNDS end as WhsDocumentSupplySpec_PriceNDS,
				wdss.WhsDocumentSupplySpec_ShelfLifePersent
			from
				v_DocumentUc du with (nolock)
				outer apply (
					select
						i_dus.Drug_id,
						sum(i_dus.DocumentUcStr_Count) as DocumentUcStr_Count,
						i_dus.DocumentUcStr_Price,
						i_dus.DrugNds_id,
						i_dus.DocumentUcStr_IsNDS
					from
						v_DocumentUcStr i_dus with (nolock)
					where
						i_dus.DocumentUc_id = du.DocumentUc_id
					group by
						i_dus.Drug_id,
						i_dus.DocumentUcStr_Price,
						i_dus.DrugNds_id,
						i_dus.DocumentUcStr_IsNDS
				) dus
				left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = du.WhsDocumentUc_id
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				left join rls.PREP p with (nolock) on p.Prep_id = d.DrugPrep_id
				left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID
				outer apply (
					select (
						case
							when isnull(isnds.YesNo_Code, 0) = 0 then dus.DocumentUcStr_Price
							else cast(dus.DocumentUcStr_Price/(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(16,4))
						end
					) as price,
					(
						case
							when isnull(isnds.YesNo_Code, 0) = 1 then dus.DocumentUcStr_Price
							else cast(dus.DocumentUcStr_Price*(1+(isnull(dn.DrugNds_Code, 0)/100.0)) as decimal(16,4))
						end
					) as price_nds
				) price
				outer apply (
					select top 1
						i_wdss.WhsDocumentSupplySpec_id,
						i_wdss.WhsDocumentSupply_id,
						i_wdss.WhsDocumentSupplySpec_PosCode,
						i_wdss.Drug_id,
						i_wdss.DrugComplexMnn_id,
						i_wdss.FIRMNAMES_id,
						i_wdss.WhsDocumentSupplySpec_KolvoForm,
						i_wdss.DRUGPACK_id,
						i_wdss.Okei_id,
						i_wdss.WhsDocumentSupplySpec_KolvoUnit,
						i_wdss.WhsDocumentSupplySpec_Count,
						i_wdss.WhsDocumentSupplySpec_Price,
						i_wdss.WhsDocumentSupplySpec_NDS,
						i_wdss.WhsDocumentSupplySpec_SumNDS,
						i_wdss.WhsDocumentSupplySpec_PriceNDS,
						i_wdss.WhsDocumentSupplySpec_ShelfLifePersent
					from
						v_WhsDocumentSupplySpec i_wdss with (nolock)
					where
						i_wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id and
						i_wdss.Drug_id = dus.Drug_id and
						i_wdss.WhsDocumentSupplySpec_NDS = dn.DrugNds_Code and
						(
							(i_wdss.WhsDocumentSupplySpec_Price = dus.DocumentUcStr_Price and isnull(isnds.YesNo_Code, 0) = 0) or
							(i_wdss.WhsDocumentSupplySpec_PriceNDS = dus.DocumentUcStr_Price and isnull(isnds.YesNo_Code, 0) = 1)
						)
					order by
						i_wdss.WhsDocumentSupply_id
				) wdss
			where
				du.DocumentUc_id = :DocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id']
		));
		if (is_object($result)) {
			$spec_data = $result->result('array');
		}

		foreach($spec_data as $spec) {
			//добавляем недостающие данные в номенклатурный справочник
			$this->addNomenData('Drug', $spec['Drug_id'], $data);
			$this->addNomenData('DrugComplexMnn', $spec['DrugComplexMnn_id'], $data);

			if (empty($spec['WhsDocumentSupplySpec_PosCode'])) {
				$spec['WhsDocumentSupplySpec_PosCode'] = ++$pos;
			}
			$spec['WhsDocumentSupplySpec_ShelfLifePersent'] = $life_persent;
			$spec['pmUser_id'] = $data['pmUser_id'];

			$action = $spec['WhsDocumentSupplySpec_id'] > 0 ? 'upd' : 'ins';
			$query = "
				declare
					@WhsDocumentSupplySpec_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
				exec dbo.p_WhsDocumentSupplySpec_{$action}
					@WhsDocumentSupplySpec_id = @WhsDocumentSupplySpec_id output,
					@WhsDocumentSupply_id = :WhsDocumentSupply_id,
					@WhsDocumentSupplySpec_PosCode = :WhsDocumentSupplySpec_PosCode,
					@Drug_id = :Drug_id,
					@DrugComplexMnn_id = :DrugComplexMnn_id,
					@FIRMNAMES_id = :FIRMNAMES_id,
					@WhsDocumentSupplySpec_KolvoForm = :WhsDocumentSupplySpec_KolvoForm,
					@DRUGPACK_id = :DRUGPACK_id,
					@Okei_id = :Okei_id,
					@WhsDocumentSupplySpec_KolvoUnit = :WhsDocumentSupplySpec_KolvoUnit,
					@WhsDocumentSupplySpec_Count = :WhsDocumentSupplySpec_Count,
					@WhsDocumentSupplySpec_Price = :WhsDocumentSupplySpec_Price,
					@WhsDocumentSupplySpec_NDS = :WhsDocumentSupplySpec_NDS,
					@WhsDocumentSupplySpec_SumNDS = :WhsDocumentSupplySpec_SumNDS,
					@WhsDocumentSupplySpec_PriceNDS = :WhsDocumentSupplySpec_PriceNDS,
					@WhsDocumentSupplySpec_ShelfLifePersent = :WhsDocumentSupplySpec_ShelfLifePersent,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @WhsDocumentSupplySpec_id as WhsDocumentSupplySpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->getFirstRowFromQuery($query, $spec);
			if ($result) {
				if (!empty($result['Error_Msg'])) {
					return array($result);
				}
			} else {
				return array(array('Error_Msg' => 'При сохранении спецификации госконтракта произошла ошибка.'));
			}
		}

		if (count($spec_data) > 0) {
			//делаем пересчет суммы гк
			$query = "
				select
					sum(isnull(wdss.WhsDocumentSupplySpec_SumNDS, 0)) as supply_sum
				from
					v_WhsDocumentSupply wds with (nolock)
					left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
				where
					wds.WhsDocumentUc_id = :WhsDocumentUc_id;
			";
			$supply_sum = $this->getFirstResultFromQuery($query, $data);

			//меняем статус ГК и новую сумму
			$result = $this->saveObject('WhsDocumentUc', array(
				'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
				'WhsDocumentUc_Sum' => $supply_sum > 0 ? $supply_sum : null,
				'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 2), //2 - Действующий
				'pmUser_id' => $data['pmUser_id']
			));
		}
	}


	/**
	 * Добавление данных в номенклатурный справочник
	 * $object - наименование сущности
	 * $id - идентификатор сущности
	 *
	 * возвращает id записи из таблицы справочника
	 */
	function addNomenData($object, $id, $data) {
        $this->load->model('RlsDrug_model', 'RlsDrug_model');

		if (empty($object) || $id <= 0) {
            return null;
        }

		$code_tbl = null;
		$code_id = null;
		$query = null;

		switch($object) {
			case 'Drug':
				if(empty($code_tbl)) $code_tbl = 'DrugNomen';
			case 'TRADENAMES':
				if(empty($code_tbl)) $code_tbl = 'DrugTorgCode';
			case 'ACTMATTERS':
				if(empty($code_tbl)) $code_tbl = 'DrugMnnCode';
			case 'DrugComplexMnn':
				if(empty($code_tbl)) $code_tbl = 'DrugComplexMnnCode';

				// Ищем запись в таблице номенклатурного справочника
				$query = "
					select
						{$code_tbl}_id as code_id
					from
						rls.v_{$code_tbl} with (nolock)
					where
						{$object}_id = :id;
				";
				$result = $this->db->query($query, array('id' => $id));
				if (is_object($result)) {
					$result = $result->result('array');
					if (isset($result[0]) && $result[0]['code_id'] > 0) { //возвращаем найденый id кода
						$code_id = $result[0]['code_id'];
					} else { //добавляем запись в номенклатурный справочник
						//получаем новый код
						$query = "
							select
							    isnull(max(cast({$code_tbl}_Code as numeric(14,0))), 0)+1 as new_code
							from
							    rls.v_{$code_tbl} with (nolock)
							where
							    isnumeric(RTRIM(LTRIM({$code_tbl}_Code)) + 'e0') = 1 and
								len({$code_tbl}_Code) <= 14
						";
						$result = $this->db->query($query, array('id' => $id));
						if (is_object($result)) {
							$result = $result->result('array');
							if ($result[0]['new_code'] > 0) {
								$new_code = $result[0]['new_code'];

								if ($object == 'Drug') {
									//получаем информацию о медикаменте
									$q = "
										select
											d.Drug_Name as name,
											d.DrugTorg_Name as nick,
											d.DrugTorg_id as tradenames_id,
											dcm.ActMatters_id as actmatters_id,
											dcm.DrugComplexMnn_id as complexmnn_id,
											A.STRONGGROUPID,
											A.NARCOGROUPID,
											P.NTFRID as CLSNTFR_ID,
											d.PrepType_id
										from
											rls.v_Drug d with (nolock)
											left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
											left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
											left join rls.v_ACTMATTERS A with (nolock) on A.Actmatters_id = dcmn.ActMatters_id
											left join rls.Prep P with (nolock) on P.Prep_id = d.DrugPrep_id
										where
											Drug_id = :id;
									";
									$r = $this->db->query($q, array(
										'id' => $id
									));
									if (is_object($r)) {
										$r = $r->result('array');

										$p = array();
										$p['name'] = $r[0]['name'];
										$p['nick'] = $r[0]['nick'];
										$p['tradenames_code'] = $r[0]['tradenames_id'] > 0 ? $this->addNomenData('TRADENAMES', $r[0]['tradenames_id'], $data) : null;
										$p['actmatters_code'] = $r[0]['actmatters_id'] > 0 ? $this->addNomenData('ACTMATTERS', $r[0]['actmatters_id'], $data) : null;
										$p['complexmnn_code'] = $r[0]['complexmnn_id'] > 0 ? $this->addNomenData('DrugComplexMnn', $r[0]['complexmnn_id'], $data) : null;
										$p['PrepClass_id'] = $this->RlsDrug_model->getDrugPrepClassId(array_merge($r[0], array('Actmatters_id' => $r[0]['actmatters_id'])));
										$p['id'] = $id;
										$p['code'] = $new_code;
										$p['pmUser_id'] = $data['pmUser_id'];

										//добавляем запись в таблицу
										$q = "
											declare
												@{$code_tbl}_id bigint,
												@PrepClass_id bigint,
												@ErrCode int,
												@ErrMessage varchar(4000);

											set @PrepClass_id = (select PrepClass_id from rls.v_PrepClass with (nolock) where PrepClass_Code = 2);
											if @PrepClass_id is null set @PrepClass_id = :PrepClass_id;

											exec rls.p_{$code_tbl}_ins
												@{$code_tbl}_id = @{$code_tbl}_id output,
												@{$object}_id = :id,
												@{$code_tbl}_Code = :code,
												@DrugNomen_Name = :name,
												@DrugNomen_Nick = :nick,
												@DrugTorgCode_id = :tradenames_code,
												@DrugMnnCode_id = :actmatters_code,
												@DrugComplexMnnCode_id = :complexmnn_code,
												@PrepClass_id = @PrepClass_id,
												@Region_id = null,
												@pmUser_id = :pmUser_id,
												@Error_Code = @ErrCode output,
												@Error_Message = @ErrMessage output;
												select @{$code_tbl}_id as code_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
										";
										$r = $this->db->query($q, $p);
										if (is_object($r)) {
											$r = $r->result('array');
											$code_id = $r[0]['code_id'];
										}
									}
								} else {
									//добавляем запись в таблицу
									$q = "
										declare
											@{$code_tbl}_id bigint,
											@ErrCode int,
											@ErrMessage varchar(4000);
										exec rls.p_{$code_tbl}_ins
											@{$code_tbl}_id = @{$code_tbl}_id output,
											@{$object}_id = :id,
											@{$code_tbl}_Code = :code,
											@Region_id = null,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
											select @{$code_tbl}_id as code_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									";
									$r = $this->db->query($q, array(
										'id' => $id,
										'code' => $new_code,
										'pmUser_id' => $data['pmUser_id']
									));
									if (is_object($r)) {
										$result = $r->result('array');
										$code_id = $result[0]['code_id'];
									}

									if ($object == 'DrugComplexMnn') { //При добавлении в справочник комплексного МНН необходимо позаботится и о добавлении действующего вещества
										//получаем информацию о комплексном МНН
										$q = "
											select
												ActMatters_id as actmatters_id
											from
												rls.v_DrugComplexMnn with (nolock)
											where
												DrugComplexMnn_id = :id;
										";
										$r = $this->db->query($q, array(
											'id' => $id
										));
										if (is_object($r)) {
											$r = $r->result('array');
											if ($r[0]['actmatters_id'] > 0)
												$this->addNomenData('ACTMATTERS', $r[0]['actmatters_id'], $data);
										}
									}
								}
							}
						}
					}
				}
				break;
		}
		return $code_id;
	}

	/**
	 * Проверка на формирование инв. ведомостей. Используется при исполнении документов учета.
	 */
	function checkInventExists($data) {
		$res = array();

		$query = "
			select
				count(wdui.WhsDocumentUcInvent_id) as cnt
			from
				dbo.v_WhsDocumentUcInvent wdui with (nolock)
				left join dbo.v_WhsDocumentStatusType i_wdst with (nolock) on i_wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id
				left join v_WhsDocumentUc i_ord with (nolock) on i_ord.WhsDocumentUc_id = wdui.WhsDocumentUc_pid
				left join dbo.v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = i_ord.WhsDocumentStatusType_id
				outer apply (
					select
						count(WhsDocumentUcInvent_id) as cnt
					from
						v_WhsDocumentUcInventDrug wduid with (nolock)
					where
						wduid.WhsDocumentUcInvent_id = wdui.WhsDocumentUcInvent_id
				) as drug_list
			where
				wdst.WhsDocumentStatusType_Code = 2 and
				i_wdst.WhsDocumentStatusType_Code != 2 and
				wdui.Org_id = :Org_id and
				drug_list.cnt = 0;
		";

		$result = $this->getFirstRowFromQuery($query, array(
			'Org_id' => $data['Org_id']
		));
		if ($result && $result['cnt'] > 0) {
			$res['Error_Msg'] = "Для текущей организации не сформированы инвентаризационные ведомости. Исполнение невозможно.";
		}

		return $res;
	}

    /**
     * Проверка наличия необходимого количества медикаментов в заонах хранения для документа учета
     */
    function checkDrugStorageZoneCount($data) {
        $error = array();

        $query = "
            select
                p.StorageZone_id,
                d.Drug_Name,
                (
                    case
                        when p.StorageZone_id is not null then sz.StorageZone_Address
                        else 'Без места хранения'
                    end
                ) as StorageZone_Name,
                p.Doc_Kolvo,
                p.AvailableDOR_Kolvo,
                p.AvailableStorageZone_Kolvo,
                (case
                    when p.Doc_Kolvo > p.AvailableStorageZone_Kolvo then 1
                    when p.Doc_Kolvo > p.AvailableDOR_Kolvo then 2
                    else 0
                end) as Error_Type
            from (
                    select
                        doc.Drug_id,
                        doc.StorageZone_id,
                        doc.Doc_Kolvo,
                        (isnull(doc.Reserve_Kolvo, 0) + isnull(dor_kolvo.Kolvo, 0)) as AvailableDOR_Kolvo,
                        (
                            case
                                when doc.StorageZone_id is not null then isnull(sz_kolvo.Kolvo, 0) -- остаток на конкретном месте хранения
                                else isnull(doc.Reserve_Kolvo, 0) + isnull(dor_kolvo.Kolvo, 0) - isnull(sz_kolvo.Kolvo, 0) -- остаток на 'Без места хранения'
                            end
                        ) as AvailableStorageZone_Kolvo
                    from (
                            select
                                dus.Drug_id,
                                du.Storage_sid as Storage_id,
                                dus.PrepSeries_id,
                                price.price_nds as Price,
                                dsl.DrugShipment_id,
                                dus.StorageZone_id,
                                sum(dus.DocumentUcStr_Count) as Doc_Kolvo,
                                sum(dorl.DrugOstatRegistryLink_Count) as Reserve_Kolvo
                            from
                                v_DocumentUc du
                                left join v_DocumentUcStr dus on dus.DocumentUc_id = du.DocumentUc_id
                                left join v_DrugShipmentLink dsl on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid
                                left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
                                left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
                                outer apply (
                                    select (
                                        case
                                            when isnull(isnds.YesNo_Code, 0) = 1 then dus.DocumentUcStr_Price
                                            else dus.DocumentUcStr_Price*(1+(isnull(dn.DrugNds_Code, 0)/100.0))
                                        end
                                    ) as price_nds
                                ) price
                                outer apply ( -- резерв под документ учета
                                    select top 1
                                        i_dorl.DrugOstatRegistryLink_Count
                                    from
                                        v_DrugOstatRegistryLink i_dorl
                                    where
                                        i_dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
                                        i_dorl.DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
                                    order by
                                        i_dorl.DrugOstatRegistryLink_id
                                ) dorl
                            where
                                du.DocumentUc_id = :DocumentUc_id
                            group by
                                dus.Drug_id, dus.PrepSeries_id, price.price_nds, dus.DocumentUcStr_Price, dsl.DrugShipment_id, dus.StorageZone_id, du.Storage_sid
                        ) doc
                        outer apply ( -- содержимое мест хранения (конкретного, если есть иденификатор места хранения, или всех с определенным набором характеристик)
                            select
                                sum(i_dsz.DrugStorageZone_Count) as Kolvo
                            from
                                v_DrugStorageZone i_dsz
                                left join v_StorageZone i_sz on i_sz.StorageZone_id = i_dsz.StorageZone_id
                            where
                                (
                                    (
                                        doc.StorageZone_id is null and
                                        i_sz.Storage_id = doc.Storage_id
                                    ) or
                                    i_dsz.StorageZone_id = doc.StorageZone_id
                                ) and
                                i_dsz.Drug_id = doc.Drug_id and
                                i_dsz.PrepSeries_id = doc.PrepSeries_id and
                                i_dsz.DrugStorageZone_Price = doc.Price and
                                i_dsz.DrugShipment_id = doc.DrugShipment_id
                        ) sz_kolvo
                        outer apply ( -- свободные остатки в регистре
                            select
                                sum(i_dor.DrugOstatRegistry_Kolvo) as Kolvo
                            from
                                v_DrugOstatRegistry i_dor
                            where
                                i_dor.SubAccountType_id = 1 and -- субсчет Доступно
                                i_dor.Storage_id = doc.Storage_id and
                                i_dor.Drug_id = doc.Drug_id and
                                i_dor.PrepSeries_id = doc.PrepSeries_id and
                                i_dor.DrugOstatRegistry_Cost = doc.Price and
                                i_dor.DrugShipment_id = doc.DrugShipment_id
                        ) dor_kolvo
                ) p
                left join rls.v_Drug d on d.Drug_id = p.Drug_id
                left join v_StorageZone sz on sz.StorageZone_id = p.StorageZone_id
            where
                p.Doc_Kolvo > p.AvailableStorageZone_Kolvo or -- медикамента в документе больше чем на выбранном месте хранения
                p.Doc_Kolvo > p.AvailableDOR_Kolvo -- медикамента в документе больше чем в регистре остатков
        ";
        $str_data = $this->getFirstRowFromQuery($query, $data);

        if (!empty($str_data['Doc_Kolvo'])) {
            switch ($str_data['Error_Type']) {
                case 1:
                    $error[] = "Для медикамента {$str_data['Drug_Name']} количество по документу учета превышает остаток в месте хранения \"{$str_data['StorageZone_Name']}\"";
                    break;
                case 2:
                    $error[] = "Для медикамента {$str_data['Drug_Name']} количество по документу учета превышает количество в регистре остатков";
                    break;
                default:
                    $error[] = "Для медикамента {$str_data['Drug_Name']} зафиксирована ошибка";
                    break;
            }
        }

        $result = array();
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
        return $result;
    }

	/**
	 * Загрузка данных
	 */
	function getDocNakData($data) {
		$query = "
			select top 1
				du.DocumentUc_id,
				du.DocumentUc_pid,
				du.Contragent_id,
				du.DocumentUc_Num,
				du.DocumentUc_setDate,
				du.DocumentUc_didDate,
				du.DocumentUc_InvoiceNum,
				du.DocumentUc_InvoiceDate,
				du.WhsDocumentUc_id,
				du.DocumentUc_DogDate,
				du.DocumentUc_DogNum,
				du.Org_id,
				du.Contragent_sid,
				du.Storage_sid,
				du.Mol_sid,
				smol.Person_Fio as Mol_sPerson,
				du.Contragent_tid,
				du.Storage_tid,
				du.Mol_tid,
				tmol.Person_Fio as Mol_tPerson,
				du.DrugFinance_id,
				du.WhsDocumentCostItemType_id,
				du.DrugDocumentType_id,
				ddt.DrugDocumentType_Code,
				dds.DrugDocumentStatus_id,
				dds.DrugDocumentStatus_Code,
				dds.DrugDocumentStatus_Name,
				note.Note_id,
				note.Note_Text,
				dbo.tzGetDate() as CurrentDate
			from
				v_DocumentUc du with (nolock)
				left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
				left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
				outer apply (
					select top 1
						DocumentStrValues_id as Note_id,
						DocumentStrValues_Values as Note_Text
					from
						v_DocumentStrValues dsv with (nolock)
						left join v_DocumentStrPropType dspt on dspt.DocumentStrPropType_id = dsv.DocumentStrPropType_id
						left join v_DocumentStrType dst on dst.DocumentStrType_id = dsv.DocumentStrType_id
					where
						dspt.DocumentStrPropType_Code = 1 and --Примечание
						dst.DocumentStrType_Code = 1 and --Документ учета DocumentUc
						dsv.Document_id = du.DocumentUc_id
				) note
				outer apply (
					select top 1
						rtrim(ltrim(isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,''))) as Person_Fio
					from
						Mol m with (nolock)
						left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = m.MedPersonal_id
						left join v_PersonState ps with (nolock) on ps.Person_id = mp.Person_id or ps.Person_id = m.Person_id
					where
						m.Mol_id = du.Mol_sid
				) smol
				outer apply (
					select top 1
						rtrim(ltrim(isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,''))) as Person_Fio
					from
						Mol m with (nolock)
						left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = m.MedPersonal_id
						left join v_PersonState ps with (nolock) on ps.Person_id = mp.Person_id or ps.Person_id = m.Person_id
					where
						m.Mol_id = du.Mol_tid
				) tmol
			where
				ddt.DrugDocumentType_Code in(6, 32) and -- 6 - Приходная накладная, 32 - Приход в отделение
				dds.DrugDocumentStatus_Code = 4 and -- 4 - Исполнен
				du.DocumentUc_Num = :DocumentUc_Num and
				du.DocumentUc_setDate = :DocumentUc_setDate and
				(:Org_id is null or du.Org_id = :Org_id);
		";

		$result = $this->db->query($query, array(
			'DocumentUc_Num' => $data['DocumentUc_Num'],
			'DocumentUc_setDate' => $data['DocumentUc_setDate'],
			'Org_id' => $data['Org_id']
		));

		if (is_object($result)) {
			$doc_data = $result->result('array');

			if (count($doc_data) > 0 && $data['List'] == 1) {
				$doc_data[0]['Str_Data'] = $this->loadDocumentUcStrList(array('DocumentUc_id' => $doc_data[0]['DocumentUc_id']));

				for($i = 0; $i < count($doc_data[0]['Str_Data']); $i++) {
					$doc_data[0]['Str_Data'][$i]['DocumentUcStr_oid'] = $doc_data[0]['Str_Data'][$i]['DocumentUcStr_id'];
					$doc_data[0]['Str_Data'][$i]['DocumentUcStr_oName'] = $doc_data[0]['Str_Data'][$i]['DrugShipment_Name'];
					$doc_data[0]['Str_Data'][$i]['DrugShipment_Name'] = null;
					$doc_data[0]['Str_Data'][$i]['DrugDocumentStatus_id'] = 1; //1 - Новый
					$doc_data[0]['Str_Data'][$i]['DrugDocumentStatus_Code'] = 1; //1 - Новый
				}
			}

			if (count($doc_data) > 0) {
				$contragent_tid = $doc_data[0]['Contragent_tid'];
				$contragent_sid = $doc_data[0]['Contragent_sid'];

				$doc_data[0]['DocumentUc_pid'] = $doc_data[0]['DocumentUc_id'];
				$doc_data[0]['DocumentUc_id'] = null;
				$doc_data[0]['DocumentUc_InvoiceDate'] = $doc_data[0]['DocumentUc_setDate'];
				$doc_data[0]['DocumentUc_setDate'] = $doc_data[0]['CurrentDate'];
				$doc_data[0]['DocumentUc_didDate'] = $doc_data[0]['CurrentDate'];
				$doc_data[0]['Storage_sid'] = $doc_data[0]['Storage_tid'];
				$doc_data[0]['Storage_tid'] = null;
				$doc_data[0]['Contragent_tid'] = $contragent_sid;
				$doc_data[0]['Contragent_sid'] = $contragent_tid;
				$doc_data[0]['DrugDocumentStatus_Code'] = 1; //1 - Новый;
				$doc_data[0]['DrugDocumentStatus_id'] = $this->getObjectIdByCode('DrugDocumentStatus', $doc_data[0]['DrugDocumentStatus_Code']);
			}

			return $doc_data;
		} else {
			return false;
		}
	}

    /**
     * Загрузка списка заявок для комбо или формы поиска (используется при редактировании документа учета)
     */
    function loadWhsDocumentSpecificityList($data) {
        $where = array();
        $params = array();

        if (isset($data['WhsDocumentUc_id']) && $data['WhsDocumentUc_id'] > 0) {
            $where[] = 'wdu.WhsDocumentUc_id = :WhsDocumentUc_id';
            $params['WhsDocumentUc_id'] = $data['WhsDocumentUc_id'];
        } else {
            if (!empty($data['WhsDocumentUc_Num'])) {
                $where[] = 'wdu.WhsDocumentUc_Num like :WhsDocumentUc_Num';
                $params['WhsDocumentUc_Num'] = $data['WhsDocumentUc_Num'].'%';
            }
            if (isset($data['WhsDocumentUc_DateRange']) && count($data['WhsDocumentUc_DateRange']) == 2 && !empty($data['WhsDocumentUc_DateRange'][0])) {
                $where[] = 'wdu.WhsDocumentUc_Date between :WhsDocumentUc_Date1 and :WhsDocumentUc_Date2';
                $params['WhsDocumentUc_Date1'] = $data['WhsDocumentUc_DateRange'][0];
                $params['WhsDocumentUc_Date2'] = $data['WhsDocumentUc_DateRange'][1];
            }
            if (isset($data['DrugFinance_id']) && $data['DrugFinance_id']) {
                $where[] = 'wdu.DrugFinance_id = :DrugFinance_id';
                $params['DrugFinance_id'] = $data['DrugFinance_id'];
            }
            if (isset($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id']) {
                $where[] = 'wdu.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
                $params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
            }
            if (!empty($data['WhsDocumentType_Code'])) {
                $where[] = 'wdt.WhsDocumentType_Code = :WhsDocumentType_Code';
                $params['WhsDocumentType_Code'] = $data['WhsDocumentType_Code'];
            }
            if (!empty($data['WhsDocumentType_CodeList'])) {
                $where[] = "wdt.WhsDocumentType_Code in ({$data['WhsDocumentType_CodeList']})";
            }
            if (!empty($data['WhsDocumentStatusType_Code'])) {
                $where[] = 'wdst.WhsDocumentStatusType_Code = :WhsDocumentStatusType_Code';
                $params['WhsDocumentStatusType_Code'] = $data['WhsDocumentStatusType_Code'];
            }
            if (isset($data['query']) && strlen($data['query'])>=1 && empty($data['WhsDocumentUc_Num'])) {
                $where[] = ' wdu.WhsDocumentUc_Num like :query';
                $params['query'] = "".$data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
				    -- where
					{$where_clause}
					-- end where
			";
        }

        $query = "
			select top 250
			    -- select
                wdu.WhsDocumentUc_id,
                wdu.WhsDocumentUc_Num,
                wdu.WhsDocumentUc_Name,
                wdu.WhsDocumentType_id,
                wdt.WhsDocumentType_Code,
                convert(varchar(10), wdu.WhsDocumentUc_Date, 104) WhsDocumentUc_Date,
                wdst.WhsDocumentStatusType_Name,
                cast(wdu.WhsDocumentUc_Sum as decimal(12,2)) as WhsDocumentUc_Sum,
                df.DrugFinance_id,
                df.DrugFinance_Name,
                wdcit.WhsDocumentCostItemType_id,
                wdcit.WhsDocumentCostItemType_Name
                -- end select
            from
                -- from
                dbo.v_WhsDocumentUc wdu with (nolock)
                inner join dbo.v_WhsDocumentSpecificity wds with (nolock) on wds.WhsDocumentUc_id = wdu.WhsDocumentUc_id
                left join dbo.v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wdu.WhsDocumentType_id
                left join dbo.v_DrugFinance df with (nolock) on df.DrugFinance_id = wds.DrugFinance_id
                left join dbo.v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = wds.WhsDocumentCostItemType_id
                left join dbo.v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = ISNULL(wdu.WhsDocumentStatusType_id, 1)
                -- end from
			{$where_clause}
			order by
			    -- order by
				wdu.WhsDocumentUc_Num
				-- end order by
		";

        if (!empty($data['limit'])) {
            $result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
            $count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
            if (is_object($result) && $count !== false) {
                return array(
                    'data' => $result->result('array'),
                    'totalCount' => $count
                );
            } else {
                return false;
            }
        } else {
            $result = $this->db->query($query, $params);
            if ( is_object($result) ) {
                return $result->result('array');
            } else {
                return false;
            }
        }
    }

    /**
     * Загрузка списка заказаов на производство для комбо или формы поиска (используется при редактировании документа учета)
     */
    function loadWhsDocumentUcOrderList($data) {
        $where = array();
        $params = array();

        if (isset($data['WhsDocumentUc_id']) && $data['WhsDocumentUc_id'] > 0) {
            $where[] = 'wdu.WhsDocumentUc_id = :WhsDocumentUc_id';
            $params['WhsDocumentUc_id'] = $data['WhsDocumentUc_id'];
        } else {
            if (!empty($data['WhsDocumentUc_Num'])) {
                $where[] = 'wdu.WhsDocumentUc_Num like :WhsDocumentUc_Num';
                $params['WhsDocumentUc_Num'] = $data['WhsDocumentUc_Num'].'%';
            }
            if (isset($data['WhsDocumentUc_DateRange']) && count($data['WhsDocumentUc_DateRange']) == 2 && !empty($data['WhsDocumentUc_DateRange'][0])) {
                $where[] = 'wdu.WhsDocumentUc_Date between :WhsDocumentUc_Date1 and :WhsDocumentUc_Date2';
                $params['WhsDocumentUc_Date1'] = $data['WhsDocumentUc_DateRange'][0];
                $params['WhsDocumentUc_Date2'] = $data['WhsDocumentUc_DateRange'][1];
            }
            if (isset($data['DrugFinance_id']) && $data['DrugFinance_id']) {
                $where[] = 'wdu.DrugFinance_id = :DrugFinance_id';
                $params['DrugFinance_id'] = $data['DrugFinance_id'];
            }
            if (isset($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id']) {
                $where[] = 'wdu.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
                $params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
            }
            if (!empty($data['WhsDocumentType_Code'])) {
                $where[] = 'wdt.WhsDocumentType_Code = :WhsDocumentType_Code';
                $params['WhsDocumentType_Code'] = $data['WhsDocumentType_Code'];
            }
            if (!empty($data['WhsDocumentType_CodeList'])) {
                $where[] = "wdt.WhsDocumentType_Code in ({$data['WhsDocumentType_CodeList']})";
            }
            if (!empty($data['WhsDocumentStatusType_Code'])) {
                $where[] = 'wdst.WhsDocumentStatusType_Code = :WhsDocumentStatusType_Code';
                $params['WhsDocumentStatusType_Code'] = $data['WhsDocumentStatusType_Code'];
            }
            if (isset($data['query']) && strlen($data['query'])>=1 && empty($data['WhsDocumentUc_Num'])) {
                $where[] = ' wdu.WhsDocumentUc_Num like :query';
                $params['query'] = "".$data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
				    -- where
					{$where_clause}
					-- end where
			";
        }

        $query = "
			select top 250
			    -- select
                wdu.WhsDocumentUc_id,
                wdu.WhsDocumentUc_Num,
                wdu.WhsDocumentUc_Name,
                wdu.WhsDocumentType_id,
                wdt.WhsDocumentType_Code,
                convert(varchar(10), wdu.WhsDocumentUc_Date, 104) WhsDocumentUc_Date,
                wdst.WhsDocumentStatusType_Name,
                cast(wdu.WhsDocumentUc_Sum as decimal(12,2)) as WhsDocumentUc_Sum,
                df.DrugFinance_id,
                df.DrugFinance_Name,
                wdcit.WhsDocumentCostItemType_id,
                wdcit.WhsDocumentCostItemType_Name
                -- end select
            from
                -- from
                dbo.v_WhsDocumentUc wdu with (nolock)
                inner join dbo.v_WhsDocumentUcOrder wduo with (nolock) on wduo.WhsDocumentUc_id = wdu.WhsDocumentUc_id
                left join dbo.v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wdu.WhsDocumentType_id
                left join dbo.v_DrugFinance df with (nolock) on df.DrugFinance_id = wduo.DrugFinance_id
                left join dbo.v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = wduo.WhsDocumentCostItemType_id
                left join dbo.v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = ISNULL(wdu.WhsDocumentStatusType_id, 1)
                -- end from
			{$where_clause}
			order by
			    -- order by
				wdu.WhsDocumentUc_Num
				-- end order by
		";

        if (!empty($data['limit'])) {
            $result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
            $count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
            if (is_object($result) && $count !== false) {
                return array(
                    'data' => $result->result('array'),
                    'totalCount' => $count
                );
            } else {
                return false;
            }
        } else {
            $result = $this->db->query($query, $params);
            if ( is_object($result) ) {
                return $result->result('array');
            } else {
                return false;
            }
        }
    }

    /**
     * Загрузка списка штрих-кодов
     */
    function loadDrugPackageBarCodeList($data) {
        $where = array();
        $params = array();

        if (!empty($data['DocumentUcStr_id'])) {
            $where[] = 'dpbc.DocumentUcStr_id = :DocumentUcStr_id';
            $params['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
			select
                dpbc.DrugPackageBarCode_id,
                dpbc.DocumentUcStr_id,
                dpbc.DrugPackageBarCodeType_id,
                dpbc.DrugPackageBarCode_BarCode,
                dpbc.DrugPackageBarCode_GTIN,
                dpbc.DrugPackageBarCode_SeriesNum,
                convert(varchar(10), dpbc.DrugPackageBarCode_expDT, 104) as DrugPackageBarCode_expDT,
                dpbc.DrugPackageBarCode_TNVED,
                dpbc.DrugPackageBarCode_FactNum
            from
                dbo.v_DrugPackageBarCode dpbc with (nolock)
			{$where_clause}
			order by
				dpbc.DrugPackageBarCode_id
		";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Резервирование остатков (зависит от типа документа учета) для конкретной строки или для конкретного документа учета
     *
     * DocumentUc_id - идентификатор документа учета (необходимо указать если резерв создается для всего документа целиком)
     * DocumentUcStr_id - идентификатор строки документа учета (необходимо указать если резерв требуется создать для конкретной строки)
     * pmUser_id - идентификатор пользователя
     */
    function createReserve($data) {
        $response = array();

        if (empty($data['DrugDocumentType_Code'])) {
            $query = "
                select
                    ddt.DrugDocumentType_Code
                from
                    v_DocumentUc du with (nolock)
                    left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                where
                    DocumentUc_id = :DocumentUc_id;
            ";
            $type_code = $this->getFirstResultFromQuery($query, array(
                'DocumentUc_id' => $data['DocumentUc_id']
            ));
            if (!empty($type_code)) {
                $data['DrugDocumentType_Code'] = $type_code*1;
            } else {
                $data['DrugDocumentType_Code'] = null;
            }
        }

        switch($data['DrugDocumentType_Code']) {
            case 2: //Документ списания
            case 25: //Списание медикаментов со склада на пациента. СМП
                $response = $this->reserveDrugOstatRegistryForDokSpis($data);
                break;
            case 10: //Расходная накладная
            	$response = $this->reserveDrugOstatRegistryForDokRas($data);
                break;
            case 15: //Накладная на внутреннее перемещение (для склада, указанного в качестве склада-поставщика)
            case 31: //Накладная на перемещение внутри склада
            case 33: //Возврат из отделения
            case 34: //Разукомплектация: списание
            	$response = $this->reserveDrugOstatRegistryForDokNVP($data);
                break;
            case 17: //Возвратная накладная (расходная)
            	$response = $this->reserveDrugOstatRegistryForDokVozNakR($data);
                break;
            case 21: //Списание медикаментов со склада на пациента.
                $response = $this->reserveDrugOstatRegistryForDokRealPat($data);
                break;
            //case 11: //Реализация
        }

        /*if (empty($response['Error_Msg'])) {
            $response['Error_Msg'] = 'Отладка.';
        }*/

        return $response;
    }

    /**
     * Перемещение в резерв
     *
     * Параметры:
     * DrugOstatRegistry_id - идентификатор строки регистра, медикаменты с которой будут перенесены в резерв
     * DrugOstatRegistry_Kolvo - количество медикаментов переносимых в резерв
     * DocumentUcStr_id - идентификатор документа учета, за которым будут закреплены зарезервированные остатки
     * pmUser_id - идентификатор пользователя
     */
    function moveToReserve($data) {
        $error = array();
        $dorl_data = array();
        $dor_id = array();
		$debug = !empty($data['debug']);
		$prescrVaccination = !empty($data['prescrVaccination']); // в случае назначения вакцинации (для возврата результата) (refs #182481)

        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

        if ($debug) { //отладка
            print "<br/>-------------------------------------------------------------------------";
            print "<br/>-------------------------------------------------------------------------";
            print "<br/><br/><b>Перемещение в резерв:</b>";
            print "<br/>>> DrugOstatRegistry_id = {$data['DrugOstatRegistry_id']};";
            print "<br/>>> DrugOstatRegistry_Kolvo = {$data['DrugOstatRegistry_Kolvo']};";
            print "<br/>>> DocumentUcStr_id = {$data['DocumentUcStr_id']};";
        }

        //поиск подходящей строки резерва
        $query = "
            select
                reserved_dor.DrugOstatRegistry_id as Reserved_DrugOstatRegistry_id,
                reserved_dor.DrugOstatRegistry_Kolvo as Reserved_DrugOstatRegistry_Kolvo,
                reserved_dor.DrugOstatRegistry_Cost as Reserved_DrugOstatRegistry_Cost,
                available_dor.DrugOstatRegistry_id as Available_DrugOstatRegistry_id,
                available_dor.DrugOstatRegistry_Kolvo as Available_DrugOstatRegistry_Kolvo,
                available_dor.DrugOstatRegistry_Cost as Available_DrugOstatRegistry_Cost
            from
                v_DrugOstatRegistry available_dor with (nolock)
                outer apply (
                    select top 1
                        i_dor.DrugOstatRegistry_id,
                        i_dor.DrugOstatRegistry_Kolvo,
                        i_dor.DrugOstatRegistry_Cost
                    from
                        v_DrugOstatRegistry i_dor with (nolock)
                    where
                        i_dor.SubAccountType_id = 2 and
                        isnull(i_dor.Contragent_id, 0) = isnull(available_dor.Contragent_id, 0) and
                        isnull(i_dor.Org_id, 0) = isnull(available_dor.Org_id, 0) and
                        isnull(i_dor.DrugFinance_id, 0) = isnull(available_dor.DrugFinance_id, 0) and
                        isnull(i_dor.DrugShipment_id, 0) = isnull(available_dor.DrugShipment_id, 0) and
                        isnull(i_dor.Drug_id, 0) = isnull(available_dor.Drug_id, 0) and
                        isnull(i_dor.WhsDocumentCostItemType_id, 0) = isnull(available_dor.WhsDocumentCostItemType_id, 0) and
                        isnull(i_dor.PrepSeries_id, 0) = isnull(available_dor.PrepSeries_id, 0) and
                        isnull(i_dor.Okei_id, 0) = isnull(available_dor.Okei_id, 0) and
                        isnull(i_dor.Storage_id, 0) = isnull(available_dor.Storage_id, 0) and
                        isnull(i_dor.DrugOstatRegistry_Cost, 0) = isnull(available_dor.DrugOstatRegistry_Cost, 0) and
                        isnull(i_dor.Drug_did, 0) = isnull(available_dor.Drug_did, 0) and
                        isnull(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(available_dor.GoodsUnit_id, :DefaultGoodsUnit_id)
                ) reserved_dor
            where
                available_dor.DrugOstatRegistry_id = :DrugOstatRegistry_id;
        ";
        $dor_data = $this->getFirstRowFromQuery($query, array(
            'DrugOstatRegistry_id' => $data['DrugOstatRegistry_id'],
            'DefaultGoodsUnit_id' => $default_goods_unit_id
        ));

        if ($data['DrugOstatRegistry_Kolvo'] > $dor_data['Available_DrugOstatRegistry_Kolvo']) {
            $error[] = 'На остатках недостаточно медикаментов для резервирования';
        }

        if (count($error) == 0) {
            if (!empty($dor_data['Reserved_DrugOstatRegistry_id'])) { //редактируем найденную строку резерва
                $kolvo = $data['DrugOstatRegistry_Kolvo'];
                $kolvo += !empty($dor_data['Reserved_DrugOstatRegistry_Kolvo']) ? $dor_data['Reserved_DrugOstatRegistry_Kolvo'] : 0;
                $cost = !empty($dor_data['Reserved_DrugOstatRegistry_Cost']) ? $dor_data['Reserved_DrugOstatRegistry_Cost'] : 0;

                if ($debug) { //отладка
                    print "<br/>-------------------------------------------------------------------------";
                    print "<br/>>>>> Обновление резерва (SAT 2): ";
                    print "<br/>>>>> -- DrugOstatRegistry_id = {$dor_data['Reserved_DrugOstatRegistry_id']};";
                    print "<br/>>>>> -- DrugOstatRegistry_Kolvo = {$kolvo};";
                    print "<br/>>>>> -- DocumentUcStr_id = ".($kolvo * $cost).";";
                }

                $response = $this->saveObject('DrugOstatRegistry', array(
                    'DrugOstatRegistry_id' => $dor_data['Reserved_DrugOstatRegistry_id'],
                    'DrugOstatRegistry_Kolvo' => $kolvo,
                    'DrugOstatRegistry_Sum' => $kolvo * $cost,
                    'pmUser_id' => $data['pmUser_id']
                ));

                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                } else {
                    //проверяем не ли уже информации о резервировании
                    $query = "
                        select
                            dorl.DrugOstatRegistryLink_id,
                            dorl.DrugOstatRegistryLink_Count
                        from
                            v_DrugOstatRegistryLink dorl with (nolock)
                        where
                            dorl.DrugOstatRegistry_id = :DrugOstatRegistry_id and
                            dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
                            dorl.DrugOstatRegistryLink_TableID = :DocumentUcStr_id
                    ";
                    $dorl_data = $this->getFirstRowFromQuery($query, array(
                        'DrugOstatRegistry_id' => $dor_data['Reserved_DrugOstatRegistry_id'],
                        'DocumentUcStr_id' => $data['DocumentUcStr_id']
                    ));
                }

                $dor_id = $dor_data['Reserved_DrugOstatRegistry_id'];
            } else { //копируем строку регистра остатков меняя субсчет на "Зарезервировано", также пересчитываем количество и сумму
                $kolvo = $data['DrugOstatRegistry_Kolvo'];
                $cost = !empty($dor_data['Available_DrugOstatRegistry_Cost']) ? $dor_data['Available_DrugOstatRegistry_Cost'] : 0;

                if ($debug) { //отладка
                    print "<br/>-------------------------------------------------------------------------";
                    print "<br/>>>>> Добавление резерва (SAT 2):";
                    print "<br/>>>>> -- DrugOstatRegistry_id (copy) = {$dor_data['Available_DrugOstatRegistry_id']};";
                    print "<br/>>>>> -- DrugOstatRegistry_Kolvo = {$kolvo};";
                    print "<br/>>>>> -- DrugOstatRegistry_Sum = ".($kolvo * $cost).";";
                }

                $response = $this->copyObject('DrugOstatRegistry', array(
                    'DrugOstatRegistry_id' => $dor_data['Available_DrugOstatRegistry_id'],
                    'DrugOstatRegistry_Kolvo' => $kolvo,
                    'DrugOstatRegistry_Sum' => $kolvo * $cost,
                    'SubAccountType_id' => 2, //Зарезервированно
                    'pmUser_id' => $data['pmUser_id']
                ));

                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
                if (!empty($response['DrugOstatRegistry_id'])) {
                    $dor_id = $response['DrugOstatRegistry_id'];
                } else {
                    $error[] = 'При сохранении информации в регистре остатков произошла ошибка';
                }
            }

            //редактирование субсчета "Доступно"
            if (count($error) == 0) {
                $kolvo = $dor_data['Available_DrugOstatRegistry_Kolvo'] - $data['DrugOstatRegistry_Kolvo'];
                $cost = $dor_data['Available_DrugOstatRegistry_Cost'];

                if ($debug) { //отладка
                    print "<br/>-------------------------------------------------------------------------";
                    print "<br/>>>>> Редактирование регистра остатков (SAT 1):";
                    print "<br/>>>>> -- DrugOstatRegistry_id = {$dor_data['Available_DrugOstatRegistry_id']};";
                    print "<br/>>>>> -- DrugOstatRegistry_Kolvo = {$kolvo};";
                    print "<br/>>>>> -- DrugOstatRegistry_Sum = ".($kolvo * $cost).";";
                }

                $response = $this->saveObject('DrugOstatRegistry', array(
                    'DrugOstatRegistry_id' => $dor_data['Available_DrugOstatRegistry_id'],
                    'DrugOstatRegistry_Kolvo' => $kolvo,
                    'DrugOstatRegistry_Sum' => $kolvo * $cost,
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }

        if (count($error) == 0) { //сохраняем информацию о резервировании остатков для строки документа учета
            $dorl_id = !empty($dorl_data['DrugOstatRegistryLink_id']) ? $dorl_data['DrugOstatRegistryLink_id'] : null;
            $kolvo = !empty($dorl_data['DrugOstatRegistryLink_Count']) ? $dorl_data['DrugOstatRegistryLink_Count'] : 0;
            $kolvo += $data['DrugOstatRegistry_Kolvo'];

            if ($debug) { //отладка
                print "<br/>-------------------------------------------------------------------------";
                print "<br/>>>>> ".($dorl_id > 0 ? "Редактирование" : "Добавление")." записи о резервировании:";
                print "<br/>>>>> -- DrugOstatRegistryLink_id = {$dorl_id};";
                print "<br/>>>>> -- DrugOstatRegistry_id = {$dor_id};";
                print "<br/>>>>> -- DrugOstatRegistryLink_Count = {$kolvo};";
                print "<br/>>>>> -- DocumentUcStr_id = {$data['DocumentUcStr_id']};";
                print "<br/>";
            }

            //проверяем допустимость такого резервирования (количество в резерве не должно превышать количествов строке документа учета)
			$query = "
				select
					dus.DocumentUcStr_Count,
					isnull(dorl.cnt, 0) as DrugOstatRegistryLink_Count
				from
					v_DocumentUcStr dus with (nolock)
					outer apply (
						select
							sum(i_dorl.DrugOstatRegistryLink_Count) as cnt
						from
							v_DrugOstatRegistryLink i_dorl with (nolock)
						where
							i_dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
							i_dorl.DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
					) dorl
				where
					dus.DocumentUcStr_id = :DocumentUcStr_id;
			";
            $dorl_data = $this->getFirstRowFromQuery($query, array(
            	'DocumentUcStr_id' => $data['DocumentUcStr_id']
			));

            if (is_array($dorl_data) && $dorl_data['DrugOstatRegistryLink_Count'] + $data['DrugOstatRegistry_Kolvo'] > $dorl_data['DocumentUcStr_Count']) {
				if ($debug) { //отладка
					print "<br/>--------------------------------- Ошибка ----------------------------------------";
					print "<br/>>>>> -- DrugOstatRegistryLink_Count = {$dorl_data['DrugOstatRegistryLink_Count']};";
					print "<br/>>>>> -- DrugOstatRegistry_Kolvo = {$data['DrugOstatRegistry_Kolvo']};";
					print "<br/>>>>> -- DocumentUcStr_Count = {$dorl_data['DocumentUcStr_Count']};";
					print "<br/>";
				}
				$error[] = "Суммарное количество зарезервированого медикамента, превышает количество в документе учета. Резервирование невозможно.";
			} else {
				$response = $this->saveObject('DrugOstatRegistryLink', array(
					'DrugOstatRegistryLink_id' => $dorl_id,
					'DrugOstatRegistry_id' => $dor_id,
					'DrugOstatRegistryLink_Count' => $kolvo,
					'DrugOstatRegistryLink_TableName' => 'DocumentUcStr',
					'DrugOstatRegistryLink_TableID' => $data['DocumentUcStr_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($response['Error_Msg'])) {
					$error[] = $response['Error_Msg'];
				}
			}
        }

        $result = array();
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
		}
		elseif ($prescrVaccination) {  // (refs #182481)
			$result['Reserved_DrugOstatRegistry_id'] = $dor_data['Reserved_DrugOstatRegistry_id'];
		}
        return $result;
    }

    /**
     * Снятие резерва с возвратом зарезервированных строк на прежнее место
     *
     * Параметры:
     * DocumentUc_id - идентификатор документа учета, за которым закреплены зарезервированные остатки
     * DocumentUcStr_id - идентификатор строки документа учета, за которой закреплены зарезервированные остатки
     * pmUser_id - идентификатор пользователя
     */
    function removeReserve($data) {
        $error = array();
		$drug_list = null;
        $delete_ostat = isset($data['delete_ostat']) ? $data['delete_ostat'] : false;
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();
        $debug = !empty($data['debug']);

        if (empty($data['pmUser_id'])) {
			$data['pmUser_id'] = $this->getPromedUserId();
		}

        if ($debug) { //отладка
            print "<br/>-------------------------------------------------------------------------";
            print "<br/>-------------------------------------------------------------------------";
            print "<br/><b>Очистка резерва:</b>";
            print "<br/>>> delete_ostat = ".($delete_ostat ? "true" : "false"). "; ";
            print "<br/>>> DocumentUc_id = ".(!empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null)."; ";
            print "<br/>>> DocumentUcStr_id = {$data['DocumentUcStr_id']}; ";
        }

        //если речь идет не о перерезервировании, а об окончательном удалении остатков требуется контроль резерва по количеству в документе учета
		if ($delete_ostat) {
			$query = "
				select
					isnull(sum(dus.DocumentUcStr_Count), 0) as DocumentUcStr_Count,
					isnull(sum(dorl.cnt), 0) as DrugOstatRegistryLink_Count
				from
					v_DocumentUcStr dus with (nolock)
					outer apply (
						select
							sum(i_dorl.DrugOstatRegistryLink_Count) as cnt
						from
							v_DrugOstatRegistryLink i_dorl with (nolock)
						where
							i_dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
							i_dorl.DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
					) dorl
				where
					(
						:DocumentUc_id is not null and
						dus.DocumentUc_id = :DocumentUc_id
					) or (
						:DocumentUcStr_id is not null and
						dus.DocumentUcStr_id = :DocumentUcStr_id
					)
			";
			$dorl_data = $this->getFirstRowFromQuery($query, array(
				'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
				'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null
			));

			if (is_array($dorl_data) && $dorl_data['DrugOstatRegistryLink_Count'] != $dorl_data['DocumentUcStr_Count']) {
				$error[] = "Суммарное количество зарезервированого медикамента, не соответствует количеству в документе учета. Резервирование невозможно.";
			}
		}

        //поиск строк резерва
		if (count($error) == 0) {
			$query = "
				select
					dorl.DrugOstatRegistryLink_id,
					dorl.DrugOstatRegistryLink_Count,
					reserved_dor.DrugOstatRegistry_id as Reserved_DrugOstatRegistry_id,
					reserved_dor.DrugOstatRegistry_Cost as Reserved_DrugOstatRegistry_Cost,
					available_dor.DrugOstatRegistry_id as Available_DrugOstatRegistry_id,
					available_dor.DrugOstatRegistry_Cost as Available_DrugOstatRegistry_Cost
				from
					v_DocumentUcStr dus with (nolock)
					left join v_DrugOstatRegistryLink dorl with (nolock) on
						dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
						dorl.DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
					left join v_DrugOstatRegistry reserved_dor with (nolock) on reserved_dor.DrugOstatRegistry_id = dorl.DrugOstatRegistry_id
					outer apply (
						select top 1
							i_dor.DrugOstatRegistry_id,
							i_dor.DrugOstatRegistry_Kolvo,
							i_dor.DrugOstatRegistry_Cost
						from
							v_DrugOstatRegistry i_dor with (nolock)
						where
							i_dor.SubAccountType_id = 1 and
							isnull(i_dor.Contragent_id, 0) = isnull(reserved_dor.Contragent_id, 0) and
							isnull(i_dor.Org_id, 0) = isnull(reserved_dor.Org_id, 0) and
							isnull(i_dor.DrugFinance_id, 0) = isnull(reserved_dor.DrugFinance_id, 0) and
							isnull(i_dor.DrugShipment_id, 0) = isnull(reserved_dor.DrugShipment_id, 0) and
							isnull(i_dor.Drug_id, 0) = isnull(reserved_dor.Drug_id, 0) and
							isnull(i_dor.WhsDocumentCostItemType_id, 0) = isnull(reserved_dor.WhsDocumentCostItemType_id, 0) and
							isnull(i_dor.PrepSeries_id, 0) = isnull(reserved_dor.PrepSeries_id, 0) and
							isnull(i_dor.Okei_id, 0) = isnull(reserved_dor.Okei_id, 0) and
							isnull(i_dor.Storage_id, 0) = isnull(reserved_dor.Storage_id, 0) and
							isnull(i_dor.DrugOstatRegistry_Cost, 0) = isnull(reserved_dor.DrugOstatRegistry_Cost, 0) and
							isnull(i_dor.Drug_did, 0) = isnull(reserved_dor.Drug_did, 0) and
							isnull(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(reserved_dor.GoodsUnit_id, :DefaultGoodsUnit_id)
					) available_dor
				where
					dorl.DrugOstatRegistryLink_id is not null and
					(
						(
							:DocumentUc_id is not null and
							dus.DocumentUc_id = :DocumentUc_id
						) or (
							:DocumentUcStr_id is not null and
							dus.DocumentUcStr_id = :DocumentUcStr_id
						)
					);
			";
			// echo getDebugSql($query, array(
			// 	'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
			// 	'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
			// 	'DefaultGoodsUnit_id' => $default_goods_unit_id
			// ));die();
			$drug_list = $this->queryResult($query, array(
				'DocumentUc_id' => !empty($data['DocumentUc_id']) ? $data['DocumentUc_id'] : null,
				'DocumentUcStr_id' => !empty($data['DocumentUcStr_id']) ? $data['DocumentUcStr_id'] : null,
				'DefaultGoodsUnit_id' => $default_goods_unit_id
			));
		}


        if (count($error) == 0 && is_array($drug_list)) {
            foreach($drug_list as $drug) {
                if ($debug) { //отладка
                    print "<br/>-------------------------------------------------------------------------";
                    print "<br/>>>>> Медикамент:";
                    print "<br/>>>>> -- DrugOstatRegistryLink_Count = {$drug['DrugOstatRegistryLink_Count']};";
                }

                //уменьшение количества на резерве
                if (count($error) == 0) {
                    $kolvo = $this->getDrugOstatRegistryKolvoById($drug['Reserved_DrugOstatRegistry_id']);
                    $kolvo -= $drug['DrugOstatRegistryLink_Count'];
                    $cost = $drug['Reserved_DrugOstatRegistry_Cost'];

                    if ($kolvo >= 0) {
                        if ($debug) { //отладка
                            print "<br/>-------------------------------------------------------------------------";
                            print "<br/>>>>> Обновление резерва (SAT 2):";
                            print "<br/>>>>> -- DrugOstatRegistry_id = {$drug['Reserved_DrugOstatRegistry_id']};";
                            print "<br/>>>>> -- DrugOstatRegistry_Kolvo = {$kolvo};";
                            print "<br/>>>>> -- DrugOstatRegistry_Sum = ".($kolvo * $cost).";";
                        }

                        $response = $this->saveObject('DrugOstatRegistry', array(
                            'DrugOstatRegistry_id' => $drug['Reserved_DrugOstatRegistry_id'],
                            'DrugOstatRegistry_Kolvo' => $kolvo,
                            'DrugOstatRegistry_Sum' => $kolvo * $cost,
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $error[] = $response['Error_Msg'];
                        }
                    } else {
						if ($debug) { //отладка
                            print "<br/>----------------------------Ошибка------------------------------------------";
							print "<br/>>>>> -- kolvo = ".($kolvo).";";
							print "<br/>>>>> -- drug = ".(json_encode($drug)).";";
                        }
                        $error[] = 'В резерве недостаточное количество медикамента для списания';
                    }
                }

                //возврат зарезервированных медикаментов на субсчет 'Доступно', если не установлен флаг окончательного удаления
                if (count($error) == 0 && !$delete_ostat) {
                    $kolvo = $drug['DrugOstatRegistryLink_Count'];

                    if (!empty($drug['Available_DrugOstatRegistry_id'])) { //редактируем найденную строку на субсчете "Доступно"
                        $kolvo += $this->getDrugOstatRegistryKolvoById($drug['Available_DrugOstatRegistry_id']);
                        $cost = !empty($drug['Available_DrugOstatRegistry_Cost']) ? $drug['Available_DrugOstatRegistry_Cost'] : 0;

                        if ($debug) { //отладка
                            print "<br/>-------------------------------------------------------------------------";
                            print "<br/>>>>> Редактирование регистра остатков (SAT 1):";
                            print "<br/>>>>> -- DrugOstatRegistry_id = {$drug['Available_DrugOstatRegistry_id']};";
                            print "<br/>>>>> -- DrugOstatRegistry_Kolvo = {$kolvo};";
                            print "<br/>>>>> -- DrugOstatRegistry_Sum = ".($kolvo * $cost).";";
                        }

                        $response = $this->saveObject('DrugOstatRegistry', array(
                            'DrugOstatRegistry_id' => $drug['Available_DrugOstatRegistry_id'],
                            'DrugOstatRegistry_Kolvo' => $kolvo,
                            'DrugOstatRegistry_Sum' => $kolvo * $cost,
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $error[] = $response['Error_Msg'];
                        }
                    } else { //копируем строку регистра остатков меняя субсчет на "Доступно", также пересчитываем количество и сумму
                        $cost = !empty($drug['Reserved_DrugOstatRegistry_Cost']) ? $drug['Reserved_DrugOstatRegistry_Cost'] : 0;

                        if ($debug) { //отладка
                            print "<br/>-------------------------------------------------------------------------";
                            print "<br/>>>>> Добавление регистра остатков (SAT 1):";
                            print "<br/>>>>> -- DrugOstatRegistry_id (copy) = {$drug['Reserved_DrugOstatRegistry_id']};";
                            print "<br/>>>>> -- DrugOstatRegistry_Kolvo = {$kolvo};";
                            print "<br/>>>>> -- DrugOstatRegistry_Sum = ".($kolvo * $cost).";";
                        }

                        $response = $this->copyObject('DrugOstatRegistry', array(
                            'DrugOstatRegistry_id' => $drug['Reserved_DrugOstatRegistry_id'],
                            'DrugOstatRegistry_Kolvo' => $kolvo,
                            'DrugOstatRegistry_Sum' => $kolvo * $cost,
                            'SubAccountType_id' => 1, //Доступно
                            'pmUser_id' => $data['pmUser_id']
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $error[] = $response['Error_Msg'];
                        }
                    }
                }

                //удаление информации о резервировании
                if (count($error) == 0) {
                    if ($debug) { //отладка
                        print "<br/>-------------------------------------------------------------------------";
                        print "<br/>>>>> Удаление записи о резервировании:";
                        print "<br/>>>>> -- DrugOstatRegistryLink_id = {$drug['DrugOstatRegistryLink_id']};";
                        print "<br/>";
                    }

                    $result = $this->deleteObject('DrugOstatRegistryLink', array(
                        'DrugOstatRegistryLink_id' => $drug['DrugOstatRegistryLink_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!empty($result['Error_Msg'])) {
                        $error[0] = $result['Error_Msg'];
                        break;
                    }
                }
            }
        }

        $result = array();
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
        return $result;
    }

    /**
     * Снятие резерва с удалением остатков
     *
     * Параметры:
     * DocumentUc_id - идентификатор документа учета, за которым закреплены зарезервированные остатки
     * DocumentUcStr_id - идентификатор строки документа учета, за которой закреплены зарезервированные остатки
     * pmUser_id - идентификатор пользователя
     */
    function deleteReserve($data) {
        $data['delete_ostat'] = true;
        return $this->removeReserve($data);
    }

    /**
     * Проверка наличия резерва по идентификатору документа учета или строки документа учета
     */
    function haveReserve($data) {
        $result = false;

        if (!empty($data['DocumentUc_id']) || !empty($data['DocumentUcStr_id'])) {
            $query = !empty($data['DocumentUc_id']) ? "
                select top 1
                    DrugOstatRegistryLink_id
                from
                    v_DrugOstatRegistryLink with (nolock)
                where
                    DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
                    DrugOstatRegistryLink_TableID in (
                        select
                            DocumentUcStr_id
                        from
                            v_DocumentUcStr with (nolock)
                        where
                            DocumentUc_id = :DocumentUc_id
                    )
            " : "
                select top 1
                    DrugOstatRegistryLink_id
                from
                    v_DrugOstatRegistryLink with (nolock)
                where
                    DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
                    DrugOstatRegistryLink_TableID = :DocumentUcStr_id
            ";
            $link_id = $this->getFirstResultFromQuery($query, $data);
            if (!empty($link_id)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Получение текущего количества медикамента на конктретной позиции регистра остатков
     */
    function getDrugOstatRegistryKolvoById($dor_id) {
        $query = "
            select
                DrugOstatRegistry_Kolvo
            from
                v_DrugOstatRegistry with (nolock)
            where
                DrugOstatRegistry_id = :DrugOstatRegistry_id;
        ";
        $kolvo = $this->getFirstResultFromQuery($query, array('DrugOstatRegistry_id' => $dor_id));

        return $kolvo > 0 ? $kolvo : 0;
    }

    /**
     * Получение зарезервированного количества медикамента для строки документа
     */
    function getReservedDrugOstatForDocumentUcStr($data) {
    	if(empty($data['DocumentUcStr_id'])){
    		return 0;
    	}
        $query = "
            select
                dus.DocumentUcStr_Count
            from
                v_DocumentUcStr dus with (nolock)
                outer apply (
                	select
		                dor.DrugOstatRegistry_Kolvo
		            from
		                v_DrugOstatRegistry dor with (nolock)
		                inner join v_DrugOstatRegistryLink dorl with (nolock) on dorl.DrugOstatRegistry_id = dor.DrugOstatRegistry_id
		            where
		                dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
		                dorl.DrugOstatRegistryLink_TableID = :DocumentUcStr_id
		        ) ost
            where
                dus.DocumentUcStr_id = :DocumentUcStr_id and ost.DrugOstatRegistry_Kolvo > 0 and ost.DrugOstatRegistry_Kolvo >= dus.DocumentUcStr_Count;
        ";
        $kolvo = $this->getFirstResultFromQuery($query, array('DocumentUcStr_id' => $data['DocumentUcStr_id']));

        return $kolvo > 0 ? $kolvo : 0;
    }

    /**
     * Формирование спецификации медикаментов из остатков поставщика
     */
    function getDocumentUcStrListByDrugOstatRegistry($data) {
        $default_goods_unit_id = $this->getDefaultGoodsUnitId();
        $error = array(); //для сбора ошибок
        $result = array();
        $drug_data = array();
        $insert_sql = "";

        if (is_array($data['DrugOstatRegistryJSON'])) {
            foreach($data['DrugOstatRegistryJSON'] as $dor_data) {
                $insert_sql .= "insert into @OstatKolvo(DrugOstatRegistry_id, DrugOstatRegistry_Kolvo) values ({$dor_data->DrugOstatRegistry_id}, {$dor_data->DrugOstatRegistry_Kolvo});
                ";
            }
        }

        //получение данных о медикаментах
        $query = "
            declare @OstatKolvo table (
                DrugOstatRegistry_id bigint,
                DrugOstatRegistry_Kolvo numeric(18, 2)
            );

            set nocount on;
            {$insert_sql}
            set nocount off;

			select
				ds.WhsDocumentSupply_id,
				dor.Drug_id,
				d.Drug_Name,
				dnmn.DrugNomen_Code,
				o.Okei_id,
				isnull(o.Okei_NationSymbol, 'упак') as Okei_NationSymbol,
				d.Drug_Fas,
				ok.DrugOstatRegistry_Kolvo as Count,
				ps.PrepSeries_id,
				ps.PrepSeries_Ser,
				convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				isnull(isdef.YesNo_Code, 0) as PrepSeries_isDefect,
				isnull(isnds.YesNo_Code, 0) as DocumentUcStr_IsNDS,
				cast(dor.DrugOstatRegistry_Cost as decimal (12,2)) as Price,
				dus.DocumentUcStr_Price,
				dus.DocumentUcStr_id as DocumentUcStr_oid,
				ds.DrugShipment_Name as DocumentUcStr_oName,
                dn.DrugNds_id,
                dn.DrugNds_Code,
                gu.GoodsUnit_id as GoodsUnit_bid,
                gu.GoodsUnit_Name as GoodsUnit_bName
			from
			    @OstatKolvo ok
                left join v_DrugOstatRegistry dor with(nolock) on dor.DrugOstatRegistry_id = ok.DrugOstatRegistry_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
				left join v_DrugShipmentLink dsl with (nolock) on dsl.DrugShipment_id = dor.DrugShipment_id
				left join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = dsl.DocumentUcStr_id
				left join v_DrugNds dn with (nolock) on dn.DrugNds_id = dus.DrugNds_id
				left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
				left join v_GoodsUnit gu on gu.GoodsUnit_id = isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id)
				left join v_Okei o on o.Okei_id = dor.Okei_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
				left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = ps.PrepSeries_isDefect
                left join v_YesNo isnds with (nolock) on isnds.YesNo_id = dus.DocumentUcStr_IsNDS
				outer apply (
					select top 1
						DrugNomen_Code
					from
						rls.v_DrugNomen with (nolock)
					where
						v_DrugNomen.Drug_id = d.Drug_id
				) dnmn
		";
        $res = $this->db->query($query, array(
            'DefaultGoodsUnit_id' => $default_goods_unit_id
        ));

        if (is_object($res)) {
            $drug_data = $res->result('array');
        }

        //сбор строк спецификации
        $str_data = array();
        foreach($drug_data as $drug) {
            if (count($error) < 1) {
                $nds_koef = (100+$drug['DrugNds_Code'])/100;
                $nds_price = $drug['Price'];
                $price = round($drug['Price']/$nds_koef, 2);

                $str_data[] = array(
                    'Drug_id' => $drug['Drug_id'],
                    'Drug_Name' => $drug['Drug_Name'],
                    'DrugNomen_Code' => $drug['DrugNomen_Code'],
                    'Okei_id' => $drug['Okei_id'],
                    'Okei_NationSymbol' => $drug['Okei_NationSymbol'],
                    'DrugNds_id' => $drug['DrugNds_id'],
                    'DrugNds_Code' => $drug['DrugNds_Code'],
                    'DocumentUcStr_Price' => $drug['DocumentUcStr_Price'],
                    'DocumentUcStr_Count' => $drug['Count'],
                    //'DocumentUcStr_RashCount' => $drug['Count'],
                    'DocumentUcStr_EdCount' => $drug['Drug_Fas'] > 0 ? $drug['Count']*$drug['Drug_Fas'] : null,
                    //'DocumentUcStr_RashEdCount' => $drug['Drug_Fas'] > 0 ? $drug['Count']*$drug['Drug_Fas'] : null,
                    'DocumentUcStr_Sum' => $drug['DocumentUcStr_Price']*$drug['Count'],
                    'DocumentUcStr_SumNds' => ($nds_price-$price)*$drug['Count'],
                    'DocumentUcStr_NdsSum' => $nds_price*$drug['Count'],
                    'DocumentUcStr_Ser' => $drug['PrepSeries_Ser'],
                    'PrepSeries_GodnDate' => $drug['PrepSeries_GodnDate'],
                    'PrepSeries_isDefect' => $drug['PrepSeries_isDefect'],
                    'DocumentUcStr_IsNDS' => $drug['DocumentUcStr_IsNDS'],
                    'DocumentUcStr_oid' => $drug['DocumentUcStr_oid'],
                    'DocumentUcStr_oName' => $drug['DocumentUcStr_oName'],
                    'GoodsUnit_bid' => $drug['GoodsUnit_bid'],
                    'GoodsUnit_bName' => $drug['GoodsUnit_bName'],
                    'pmUser_id' => $data['session']['pmuser_id'],
                );
            }
        }

        $result['data'] = $str_data;
        $result['success'] = true;

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }

        return $result;
    }

    /**
     * Импорт приходной накладной из файла sst
     */
    function importDokNakFromSst($data) {
        $f_res = array(array('Error_Msg' => null));
        $this->load->model('DocumentUc_model', 'dumodel');

        $this->beginTransaction();

        $error = array(); //массив ошибок при выполнении импорта
        $log_arr = array();

        $import_count = 0;

        $doc_id = null;
        $nds_id = null;
        $nds_rate = 0;
        $doc_sum = 0;
        $doc_sum_nds = 0;

        $handler = fopen($data['FileFullName'], "rb");
        if(!$handler) {
            DieWithError("Ошибка при попытке открыть файл!");
        }

        //формируем некоторые данные для шапки документа
        $session_data = getSessionParams();
        $header_data = array(
            'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 1), //1 - Новый
            'DocumentUc_setDate' => $data['DocumentUc_setDate'],
            'DocumentUc_didDate' => $data['DocumentUc_didDate'],
            'DocumentUc_InvoiceNum' => $data['DocumentUc_InvoiceNum'],
            'DocumentUc_InvoiceDate' => $data['DocumentUc_InvoiceDate'],
            'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
            'DrugFinance_id' => $data['DrugFinance_id'],
            'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
            'Org_id' => !empty($data['Org_id']) ? $data['Org_id'] : null,
            'Lpu_id' => !empty($data['Lpu_id']) ? $data['Lpu_id'] : null,
            'Contragent_id' => !empty($data['Contragent_id']) ? $data['Contragent_id'] : null,
            'Contragent_sid' => !empty($data['Contragent_sid']) ? $data['Contragent_sid'] : null,
            'Contragent_tid' => !empty($data['Contragent_tid']) ? $data['Contragent_tid'] : null,
            'Storage_tid' => $data['Storage_tid'],
            'Mol_tid' => $data['Mol_tid'],
            'Note_id' => $data['Note_id'],
            'Note_Text' => $data['Note_Text']
        );

        if (!isset($header_data['Contragent_id']) && $session_data['Contragent_id'] > 0) {
            $header_data['Contragent_id'] = $session_data['Contragent_id'];
        }
        if (!isset($header_data['Lpu_id']) && $session_data['Lpu_id'] > 0) {
            $header_data['Lpu_id'] = $session_data['Lpu_id'];
        }
        if (isset($header_data['Contragent_sid']) && $header_data['Contragent_sid'] > 0 && !isset($data['SubAccountType_sid'])) {
            $header_data['SubAccountType_sid'] = $this->getObjectIdByCode('SubAccountType', 1); //1 - Доступно
        }
        if (isset($header_data['Contragent_tid']) && $header_data['Contragent_tid'] > 0 && !isset($data['SubAccountType_tid'])) {
            $header_data['SubAccountType_tid'] = $this->getObjectIdByCode('SubAccountType', 1); //1 - Доступно
        }
        if (empty($header_data['Contragent_id'])) {
            $error[] = "Отсутвует контрагент текущей организации пользователя";
            return false;
        }

        $import_file_type = '';
        $section = '';
        $row_num = 0;

        while(!feof($handler)) {
            if (count($error) > 0) {
                break;
            }

            $row_num++;

            $str = iconv('cp1251', 'utf-8', fgets($handler));
            if (!empty($str[0]) && $str[0] != '-') {
                if ($str[0] == '[' && strpos($str, ']') > 0) {
                    $section = strtolower(substr($str, 1, strpos($str, ']')-1));
                } else {
                    switch($section) {
                        case 'header':
                            $str_data = preg_split('/;/', $str);
                            $data_count = count($str_data)-1;

                            //определяем формат импорта
                            if (empty($import_file_type)) {
                                if ($data_count == 4) {
                                    $import_file_type = 'krym_farm';
                                }
                                if ($data_count == 8) {
                                    $import_file_type = 'krym_other_sup';
                                }
                            }

                            if (!empty($import_file_type))  {
                                switch($import_file_type) {
                                    case 'krym_farm':
                                        $pos = strpos($str_data[0], '_');
                                        if ($pos > -1) {
                                            $header_data['DocumentUc_Num'] = substr($str_data[0], $pos);
                                            $ctr_str = substr($str_data[0], 0, $pos); //в подстроке должно быть 5 цифр; первые 4 - код контрагента поставщика; последняя - код склада получателя.
                                            if (strlen($ctr_str) == 5) {
                                                $contragent_code = substr($ctr_str, 0, 4);
                                                $store_code = substr($ctr_str, 4);

                                                //определение контрагента поставщика
                                                if (!empty($contragent_code)) {
                                                    $contragent_code = $contragent_code*1;
                                                    $query = "
                                                        select top 1
                                                            c.Contragent_id
                                                        from
                                                            v_Contragent c with	(nolock)
                                                        where
                                                            c.Contragent_Code = :Contragent_Code
                                                        order by
                                                            c.Contragent_id desc;
                                                    ";
                                                    $contragent_id = $this->getFirstResultFromQuery($query, array(
                                                        'Contragent_Code' => $contragent_code
                                                    ));
                                                    if (!empty($contragent_id)) {
                                                        $header_data['Contragent_sid'] = $contragent_id;
                                                    }
                                                }

                                                //определение склада получателя
                                                if (!empty($header_data['Contragent_tid'])) {
                                                    $query = "
                                                        select top 1
                                                            s.Storage_id
                                                        from
                                                            v_Contragent c with	(nolock)
                                                            left join v_StorageStructLevel ssl with (nolock) on ssl.Org_id = c.Org_id
                                                            inner join v_Storage s with (nolock) on s.Storage_id = ssl.Storage_id
                                                        where
                                                            c.Contragent_id = :Contragent_id and
                                                            s.Storage_Code = :Storage_Code
                                                        order by
                                                            s.Storage_id desc;
                                                    ";
                                                    $store_id = $this->getFirstResultFromQuery($query, array(
                                                        'Contragent_id' => $header_data['Contragent_tid'],
                                                        'Storage_Code' => $store_code
                                                    ));
                                                    if (!empty($store_id)) {
                                                        $header_data['Storage_tid'] = $store_id;
                                                    }
                                                }
                                            }
                                        }
                                        $header_data['DocumentUc_setDate'] = strlen($str_data[1]) == 10 ? $this->formatDate($str_data[1]) : null;
                                        $header_data['DrugDocumentType_id'] = $this->getObjectIdByCode('DrugDocumentType', 6); //6 - Приходная накладная
                                        $header_data['pmUser_id'] = $data['pmUser_id'];
                                        break;
                                    case 'krym_other_sup':
                                        $header_data['DocumentUc_Num'] = !empty($str_data[0]) ? $str_data[0] : null;
                                        $header_data['DocumentUc_setDate'] = strlen($str_data[1]) == 10 ? $this->formatDate($str_data[1]) : null;
                                        $header_data['DrugDocumentType_id'] = $this->getObjectIdByCode('DrugDocumentType', 6); //6 - Приходная накладная
                                        $header_data['pmUser_id'] = $data['pmUser_id'];
                                        break;
                                }

                                //сохраняем шапку документа учета
                                $response = $this->saveObject('DocumentUc', $header_data);
                                if (!empty($response['Error_Msg'])) {
                                    $error[] = $response['Error_Msg'];
                                }
                                if (!empty($response['DocumentUc_id'])) {
                                    $doc_id = $response['DocumentUc_id'];
                                } else {
                                    $error[] = "Не удалось соххранить документ учета.";
                                }

                                //формируем заголовок для лога
                                if ($doc_id > 0) {
                                    $query = "
                                        select
                                            wdu.WhsDocumentUc_Num,
                                            wdcit.WhsDocumentCostItemType_Name,
                                            df.DrugFinance_Name,
                                            c_s.Contragent_Name as Contragent_sName
                                        from
                                            v_DocumentUc du with (nolock)
                                            left join v_WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = du.WhsDocumentUc_id
                                            left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
                                            left join v_DrugFinance df with (nolock) on df.DrugFinance_id = du.DrugFinance_id
                                            left join v_Contragent c_s with (nolock) on c_s.Contragent_id = du.Contragent_id
                                        where
                                            du.DocumentUc_id = :DocumentUc_id;
                                    ";
                                    $log_header_data = $this->getFirstRowFromQuery($query, array(
                                        'DocumentUc_id' => $doc_id
                                    ));
                                    if (is_array($log_header_data)) {
                                        $log_header = array();
                                        if (!empty($log_header_data['WhsDocumentUc_Num'])) {
                                            $log_header[] = "№ контракта: ".$log_header_data['WhsDocumentUc_Num'];
                                        }
                                        if (!empty($log_header_data['WhsDocumentCostItemType_Name'])) {
                                            $log_header[] = "Статья расхода: ".$log_header_data['WhsDocumentCostItemType_Name'];
                                        }
                                        if (!empty($log_header_data['DrugFinance_Name'])) {
                                            $log_header[] = "Источник финансирования: ".$log_header_data['DrugFinance_Name'];
                                        }
                                        if (!empty($log_header_data['Contragent_sName'])) {
                                            $log_header[] = "Поставщик: ".$log_header_data['Contragent_sName'];
                                        }
                                        $log_arr[] = join(", ", $log_header);
                                    }
                                }
                            } else {
                                $log_arr[] = $this->getImportDokNakFromSstError(1);
                            }
                            break;
                        case 'body':
                            $save_record_enabled = true; //флаг разрешения сохранения строки документа учета
                            $str_data = preg_split('/;/', $str);
                            $data_count = count($str_data)-1;

                            //проверяем соблюдение формата
                            if (!empty($import_file_type)) {
                                if (($import_file_type == 'krym_farm' && $data_count == 20) || ($import_file_type == 'krym_other_sup' && $data_count == 40)) {
                                    $save_data = array();

                                    switch($import_file_type) {
                                        case 'krym_farm':
                                            $save_data['DrugPrepFasCode_Code'] = !empty($str_data[0]) ? $str_data[0] : null;
                                            $save_data['DocumentUcStr_Count'] = !empty($str_data[4]) ? $this->formatNumeric($str_data[4]) : null;
                                            $save_data['DocumentUcStr_Price'] = !empty($str_data[5]) ? $this->formatNumeric($str_data[5]) : null;
                                            $save_data['DocumentUcStr_PriceR'] = $save_data['DocumentUcStr_Price'];
                                            $save_data['DocumentUcStr_Sum'] = !empty($save_data['DocumentUcStr_Count']) && !empty($save_data['DocumentUcStr_Price']) ? $save_data['DocumentUcStr_Count'] * $save_data['DocumentUcStr_Price'] : null;
                                            $save_data['DocumentUcStr_SumR'] = $save_data['DocumentUcStr_Sum'];
                                            $save_data['PrepSeries_GodnDate'] = strlen($str_data[9]) == 10 ? $str_data[9] : null;
                                            $save_data['DocumentUcStr_godnDate'] = strlen($str_data[9]) == 10 ? $this->formatDate($str_data[9]) : null;
                                            $save_data['DocumentUcStr_Ser'] = !empty($str_data[10]) ? $str_data[10] : null;
                                            $save_data['Drug_Ean'] = !empty($str_data[15]) ? $str_data[15] : null;
                                            $save_data['DocumentUcStr_Barcod'] = !empty($str_data[16]) ? $str_data[16] : null;
                                            $save_data['pmUser_id'] = $data['pmUser_id'];
                                            break;
                                        case 'krym_other_sup':
                                            $save_data['DrugPrepFasCode_Code'] = !empty($str_data[0]) ? $str_data[0] : null;
                                            $save_data['DocumentUcStr_Count'] = !empty($str_data[4]) ? $this->formatNumeric($str_data[4]) : null;
                                            $save_data['DocumentUcStr_Price'] = !empty($str_data[5]) ? $this->formatNumeric($str_data[5]) : null;
                                            $save_data['DocumentUcStr_RegPrice'] = !empty($str_data[6]) ? $this->formatNumeric($str_data[6]) : null;
                                            $save_data['DocumentUcStr_PriceR'] = $save_data['DocumentUcStr_Price'];
                                            $save_data['DocumentUcStr_Sum'] = !empty($save_data['DocumentUcStr_Count']) && !empty($save_data['DocumentUcStr_Price']) ? $save_data['DocumentUcStr_Count'] * $save_data['DocumentUcStr_Price'] : null;
                                            $save_data['DocumentUcStr_SumR'] = $save_data['DocumentUcStr_Sum'];
                                            $save_data['DocumentUcStr_CertNum'] = !empty($str_data[12]) ? $str_data[12] : null;
                                            $save_data['DocumentUcStr_Ser'] = !empty($str_data[13]) ? $str_data[13] : null;
                                            $save_data['PrepSeries_GodnDate'] = strlen($str_data[15]) == 10 ? $str_data[15] : null;
                                            $save_data['DocumentUcStr_godnDate'] = strlen($str_data[15]) == 10 ? $this->formatDate($str_data[15]) : null;
                                            $save_data['Drug_Ean'] = !empty($str_data[16]) ? $str_data[16] : null;
                                            $save_data['DocumentUcStr_Barcod'] = !empty($str_data[16]) ? $str_data[16] : null;
                                            $save_data['DocumentUcStr_RegDate'] = strlen($str_data[17]) == 10 ? $this->formatDate($str_data[17]) : null;
                                            $save_data['pmUser_id'] = $data['pmUser_id'];
                                            break;
                                    }

                                    $log_data = array(
                                        'Drug_Ean' => !empty($save_data['Drug_Ean']) ? $save_data['Drug_Ean'] : null,
                                        'DrugPrepFasCode_Code' => !empty($save_data['DrugPrepFasCode_Code']) ? $save_data['DrugPrepFasCode_Code'] : null,
                                        'row_num' => $row_num
                                    );

                                    //определение медикамента
                                    $drug_id = null;

                                    //поиск медикамента в ГК
                                    if (!empty($data['WhsDocumentUc_id']) && (!empty($save_data['Drug_Ean']) || !empty($save_data['DrugPrepFasCode_Code']))) {
                                        $query = "
                                            select
                                                p.Drug_id,
                                                p.PriceNds,
                                                d.Drug_Ean,
                                                dpfc.DrugPrepFasCode_Code,
                                                dn.DrugNds_id,
                                                dn.DrugNds_Code,
                                                (
                                                    case
                                                        when d.Drug_Ean = :Drug_Ean then 1
                                                        else 0
                                                    end
                                                ) as Ean_Check,
                                                (
                                                    case
                                                        when dpfc.DrugPrepFasCode_Code = :DrugPrepFasCode_Code then 1
                                                        else 0
                                                    end
                                                ) as Code_Check,
                                                (
                                                    case
                                                        when cast(p.PriceNds as numeric(16,4)) = cast(:PriceNds as numeric(16,4)) then 1
                                                        else 0
                                                    end
                                                ) as Price_Check
                                            from
                                                (
                                                    select
                                                        wdss.Drug_id,
                                                        wdss.WhsDocumentSupplySpec_PriceNDS as PriceNds,
                                                        wdss.WhsDocumentSupplySpec_NDS,
                                                        1 as ord_num
                                                    from
                                                        v_WhsDocumentSupply wds with (nolock)
                                                        left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                                                    where
                                                        wds.WhsDocumentUc_id = :WhsDocumentUc_id
                                                    union
                                                    select
                                                        wdssd.Drug_sid,
                                                        wdss.WhsDocumentSupplySpec_PriceNDS as PriceNds,
                                                        wdss.WhsDocumentSupplySpec_NDS,
                                                        2 as ord_num
                                                    from
                                                        v_WhsDocumentSupply wds
                                                        left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                                                        inner join v_WhsDocumentSupplySpecDrug wdssd with (nolock) on wdssd.WhsDocumentSupplySpec_id = wdss.WhsDocumentSupplySpec_id
                                                    where
                                                        wds.WhsDocumentUc_id = :WhsDocumentUc_id
                                                ) p
                                                left join rls.v_Drug d with (nolock) on d.Drug_id = p.Drug_id
                                                left join rls.v_DrugPrepFasCode dpfc with (nolock) on dpfc.DrugPrepFas_id = d.DrugPrepFas_id
                                                left join v_DrugNds dn with (nolock) on dn.DrugNds_Code = p.WhsDocumentSupplySpec_NDS
                                            order by
                                                p.ord_num;
                                        ";
                                        $drug_array = $this->queryResult($query, array(
                                            'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
                                            'Drug_Ean' => $save_data['Drug_Ean'],
                                            'DrugPrepFasCode_Code' => $save_data['DrugPrepFasCode_Code'],
                                            'PriceNds' => $save_data['DocumentUcStr_Price'] > 0 ? $save_data['DocumentUcStr_Price'] : 0
                                        ));

                                        $ean_check = false;
                                        $code_check = false;
                                        $price_check = false;
                                        $check_points_max = 0; //счетчик для поиска самого подходящего медикамента

                                        //проверяем наборы данных и в зависимости от сочетания результатов прохождения проверок вычисляем наиболее хороший вариант идентификации медикамента
                                        foreach($drug_array as $drug_data) {
                                            $check_points = 0;

                                            if ($drug_data['Ean_Check'] == '1') {
                                                $ean_check = true;
                                                $check_points += 100;
                                                if ($drug_data['Price_Check'] == '1') {
                                                    $price_check = true;
                                                    $check_points += 1;
                                                }
                                            }

                                            if ($drug_data['Code_Check'] == '1') {
                                                $code_check = true;
                                                $check_points += 10;
                                                if ($drug_data['Price_Check'] == '1') {
                                                    $price_check = true;
                                                    $check_points += 1;
                                                }
                                            }

                                            if ($check_points >= 10 && $check_points > $check_points_max) {
                                                $drug_id = $drug_data['Drug_id'];
                                                $nds_id = $drug_data['DrugNds_id'];
                                                if ($drug_data['DrugNds_Code'] > 0) {
                                                    $nds_rate = $drug_data['DrugNds_Code']/100;
                                                }
                                                $check_points_max = $check_points;
                                            }
                                        }

                                        //передаем сообщения об ошибках в лог
                                        if (!empty($save_data['Drug_Ean']) && !$ean_check) {
                                            $log_arr[] = $this->getImportDokNakFromSstError(3, $log_data);
                                        }
                                        if (!empty($save_data['DrugPrepFasCode_Code']) && !$code_check) {
                                            $log_arr[] = $this->getImportDokNakFromSstError(4, $log_data);
                                        }
                                        if (!empty($drug_id) && !empty($save_data['DocumentUcStr_Price']) && !$price_check) {
                                            $log_arr[] = $this->getImportDokNakFromSstError(5, $log_data);
                                            $save_record_enabled = false; //при несоответствии цены импорт строки не производится
                                        }
                                    }

                                    //поиск медикамента по ЕАН в справочнике РЛС
                                    if (empty($drug_id) && !empty($save_data['Drug_Ean'])) {
                                        $query = "
                                            select top 1
                                                d.Drug_id
                                            from
                                                rls.v_Drug d with (nolock)
                                            where
                                                d.Drug_Ean = :Drug_Ean
                                            order by
                                                d.Drug_id;
                                        ";
                                        $drug_id = $this->getFirstResultFromQuery($query, array(
                                            'Drug_Ean' => $save_data['Drug_Ean']
                                        ));
                                        if (empty($drug_id)) {
                                            //ошибка лог 6: Медикамент с кодом EAN: {$save_data['Drug_Ean']} не найден в справочнике РЛС.
                                            $log_arr[] = $this->getImportDokNakFromSstError(6, $log_data);
                                        }
                                    }

                                    if (!empty($drug_id)) {
                                        $save_data['Drug_id'] = $drug_id;
                                    } else {
                                        $log_arr[] = $this->getImportDokNakFromSstError(7, $log_data);
                                        $save_record_enabled = false;
                                    }

                                    //рассчет НДС
                                    $save_data['DrugNds_id'] = $nds_id;
                                    if (!empty($save_data['DocumentUcStr_Sum']) && $save_data['DocumentUcStr_Sum'] > 0 && $nds_rate > 0) {
                                        $save_data['DocumentUcStr_SumNds'] = $save_data['DocumentUcStr_Sum']*$nds_rate;
                                        $save_data['DocumentUcStr_SumNdsR'] = $save_data['DocumentUcStr_SumNds'];
                                        $save_data['DocumentUcStr_IsNDS'] = 2; //YesNo_id: Да = 2
                                    }

                                    //сохранение строки документа учета
                                    if ($save_record_enabled) {
                                        //сохранение серии
                                        if (!empty($save_data['DocumentUcStr_Ser'])) {
                                            $series_id = $this->savePrepSeries(array(
                                                'Drug_id' => $save_data['Drug_id'],
                                                'PrepSeries_Ser' => $save_data['DocumentUcStr_Ser'],
                                                'PrepSeries_GodnDate' => $save_data['PrepSeries_GodnDate'],
                                                'pmUser_id' => $data['pmUser_id']
                                            ));
                                            $save_data['PrepSeries_id'] = $series_id;
                                        }

                                        //сохранение строки
                                        $save_data['DocumentUc_id'] = $doc_id;
                                        $response = $this->saveObject('DocumentUcStr', $save_data);
                                        if (!empty($response['Error_Msg'])) {
                                            $error[] = $response['Error_Msg'];
                                        } else if (empty($response['DocumentUcStr_id'])) {
                                            $error[] = "Не удалось сохранить строку документа учета.";
                                        }


                                        if (!empty($response['DocumentUcStr_id'])) {
                                            //фиксируем в логе успешный импорт строки
                                            $log_arr[] = $this->getImportDokNakFromSstError(0, $log_data);

                                            //увеличиваем счетчик импортированных строкж
                                            $import_count++;

                                            //собираем суммы по всему документу учета
                                            $doc_sum += !empty($save_data['DocumentUcStr_Sum']) && $save_data['DocumentUcStr_Sum'] > 0 ? $save_data['DocumentUcStr_Sum'] : 0;
                                            $doc_sum_nds += !empty($save_data['DocumentUcStr_SumNds']) && $save_data['DocumentUcStr_SumNds'] > 0 ? $save_data['DocumentUcStr_SumNds'] : 0;
                                        }

                                        //сохранение партии
                                        //$ds_data = $this->generateDrugShipmentName();
                                        if (!empty($response['DocumentUcStr_id'])/* && !empty($ds_data[0]) && !empty($ds_data[0]['DrugShipment_Name'])*/) {
                                            $this->saveLinkedDrugShipment(array(
                                                'DocumentUcStr_id' => $response['DocumentUcStr_id'],
                                                'DrugShipment_Name' => 'set_name_by_id'/*$ds_data[0]['DrugShipment_Name']*/,
                                                'pmUser_id' => $data['pmUser_id']
                                            ));
                                        }
                                    }
                                } else {
                                    $log_arr[] = $this->getImportDokNakFromSstError(2, array(
                                        'row_num' => $row_num
                                    ));
                                }
                            }
                            break;
                    }
                }
            }
        }

        $result = array();

        if (count($error) == 0 && !empty($doc_id)) {
            //сохранение сумм в документ учета
            $response = $this->saveObject('DocumentUc', array(
                'DocumentUc_id' => $doc_id,
                'DocumentUc_Sum' => $doc_sum > 0 ? $doc_sum : null,
                'DocumentUc_SumR' => $doc_sum > 0 ? $doc_sum : null,
                'DocumentUc_SumNds' => $doc_sum_nds > 0 ? $doc_sum_nds : null,
                'DocumentUc_SumNdsR' => $doc_sum_nds > 0 ? $doc_sum_nds : null,
                'pmUser_id' => $data['pmUser_id']
            ));

            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            } else if (empty($response['DocumentUc_id'])) {
                $error[] = "Не удалось сохранить документа учета.";
            }
        }

        if (count($error) > 0) {
            $this->rollbackTransaction();
            $result['Error_Msg'] = $error[0];
        } else {
            $this->commitTransaction();
            $result['Error_Code'] = null;
            $result['Error_Msg'] = null;
            if (!empty($doc_id)) {
                $result['DocumentUc_id'] = $doc_id;
            }
            if (count($log_arr) > 0) {
                $result['success'] = false;
                $result['Protocol_Link'] = $this->getImportProtocol($log_arr, 'doknak');
            }
        }
        return array($result);
    }

    /**
     * Сообщение об ошибках при импорте накладных
     */
    function getImportDokNakFromSstError($err_code = 0, $data = array()) {
        if ($err_code > 0) {
            $err_msg = "Ошибка: ";
        } else {
            $err_msg = "Результат импорта: Ок";
        }

        switch($err_code) {
            case 1:
                $err_msg .= "Не удалось определить формат импорта.";
                break;
            case 2:
                $err_msg .= "Данные в строке не соответствуют формату импорта.";
                break;
            case 3:
                $err_msg .= "Медикамент с кодом EAN: {$data['Drug_Ean']} не найден в спецификации контракта или в списке синонимов к ней. Или удалите медикамент из накладной, или добавьте его в список синонимов к медикаментам из спецификации контракта, или выполните учет дополнительного соглашения к ГК.";
                break;
            case 4:
                $err_msg .= "Медикамент с товарным кодом: {$data['DrugPrepFasCode_Code']} не найден в спецификации контракта или в списке синонимов к ней. Или удалите медикамент из накладной, или добавьте его в список синонимов к медикаментам из спецификации контракта, или выполните учет дополнительного соглашения к ГК.";
                break;
            case 5:
                $err_msg .= "Цена не соответствует ГК";
                break;
            case 6:
                $err_msg .= "Медикамент с кодом EAN: {$data['Drug_Ean']} не найден в справочнике РЛС.";
                break;
            case 7:
                $err_msg .= "Не удалось идентифицировать медикамент.";
                break;
        }

        if (!empty($data['row_num'])) {
            if (!empty($data['DrugPrepFasCode_Code'])) {
                 $err_msg = "Код ЛС: {$data['DrugPrepFasCode_Code']} {$err_msg}";
            }
            $err_msg = "Строка {$data['row_num']}: {$err_msg}";
        }

        return $err_msg;
    }

    /**
     * Запись протокола импорта в файл
     */
    function getImportProtocol($log_array, $object_nick) {
        $link = '';

        if (empty($object_nick)) {
            $object_nick = "obj";
        }

        $out_dir = "import_{$object_nick}_".time();
        mkdir(EXPORTPATH_REGISTRY.$out_dir);

        $msg_count = 0;
        $link = EXPORTPATH_REGISTRY.$out_dir."/protocol.txt";
        $fprot = fopen($link, 'w');

        foreach($log_array as $log_msg) {
            $msg = $log_msg;
            $msg .= "\r\n\r\n";
            fwrite($fprot, $msg);
        }

        fclose($fprot);

        return $link;
    }

    /**
     * Получение списка места хранения медикамента
     */
    function loadStorageZoneCombo($data) {
    	$where = array();
    	$join = array();

        if (empty($data['StorageZone_id']) && empty($data['Storage_id']) && empty($data['Contragent_id'])) {
            return false;
        }

        if(!empty($data['StorageZone_id'])){
            $where[] = "sz.StorageZone_id = :StorageZone_id ";
        } else {
            if(!empty($data['Storage_id'])){
                $where[] = "sz.Storage_id = :Storage_id ";
            } elseif(!empty($data['Contragent_id'])){
                $join[] = "left join v_StorageStructLevel ssl with (nolock) on ssl.Storage_id = sz.Storage_id";
                $join[] = "left join v_Contragent c with (nolock) on c.Org_id = ssl.Org_id";
                $where[] = "c.Contragent_id = :Contragent_id ";
            }
        }

        $join_clause = implode(' ', $join);
        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            select top 1000
                sz.StorageZone_id,
                sz.StorageZone_Address,
                sut.StorageUnitType_Name,
                (
				    isnull(sz.StorageZone_Address, '')+
				    isnull(' / '+sut.StorageUnitType_Name, '')
				) as StorageZone_Name
            from
            	v_StorageZone sz with (nolock)
            	left join v_StorageUnitType sut with (nolock) on sut.StorageUnitType_id = sz.StorageUnitType_id
            	{$join_clause}
                {$where_clause}
        ";
        $res = $this->db->query($query, $data);

        if (is_object($res)) {
            $res = $res->result('array');
            return $res;
        } else {
        	return false;
        }
    }

    /**
     * Получение списка места хранения медикамента для конкретного медикамента
     */
    function loadStorageZoneByDrugIdCombo($data) {
    	$without_sz = array(
			array(
				'StorageZone_id' => 0,
				'StorageZone_Address' => 'Без места хранения',
				'StorageUnitType_Name' => '',
				'GoodsUnit_Name' => 'Упаковка',
				'GoodsUnit_Nick' => 'уп.',
				'DrugStorageZone_Count' => 0,
				'DrugStorageZone_Name' => 'Без места хранения'.($data['isCountEnabled'] == 'true' ? ' / 0' : '')
			)
		);

        $data['DefaultGoodsUnit_id'] = $this->getDefaultGoodsUnitId();

    	if(!empty($data['Drug_id'])){
	    	$filter = "";
			$filter2 = "";
			$fjoin = "";
			$fjoin2 = "";
			$params = array();
			$filter .= " and dor.Drug_id = :Drug_id";
			$filter2 .= " and dor2.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
			$params['DefaultGoodsUnit_id'] = $data['DefaultGoodsUnit_id'];

			if(!empty($data['Storage_id'])){
	            $filter .= " and s.Storage_id = :Storage_id ";
	            $filter2 .= " and dor2.Storage_id = :Storage_id ";
	            $params['Storage_id'] = $data['Storage_id'];
	        } else if(!empty($data['Contragent_id'])){
	            $fjoin .= "
	                left join v_StorageStructLevel ssl with (nolock) on ssl.Storage_id = s.Storage_id
	                left join v_Contragent c with (nolock) on c.Org_id = ssl.Org_id
	            ";
	            $filter .= " and c.Contragent_id = :Contragent_id ";
	            $fjoin2 .= "
	                left join v_StorageStructLevel ssl2 with (nolock) on ssl2.Storage_id = s2.Storage_id
	                left join v_Contragent c2 with (nolock) on c2.Org_id = ssl2.Org_id
	            ";
	            $filter2 .= " and c2.Contragent_id = :Contragent_id ";
	            $params['Contragent_id'] = $data['Contragent_id'];
	        }

	        if(!empty($data['DrugShipment_id'])){
	            $filter .= " and dor.DrugShipment_id = :DrugShipment_id ";
	            $filter2 .= " and dor2.DrugShipment_id = :DrugShipment_id ";
	            $params['DrugShipment_id'] = $data['DrugShipment_id'];
	        }

			$query = "
				select
					cast((isnull(drug_reg.DrugReg_Count,0) - isnull(drug_sz.Drug_Count,0)) as float) as DrugCountWsz,
					gu_b.GoodsUnit_Name,
					gu_b.GoodsUnit_Nick
				from
					v_DrugOstatRegistry dor with (nolock)
					inner join v_Storage s with (nolock) on s.Storage_id = dor.Storage_id
					left join v_GoodsUnit gu_b with (nolock) on gu_b.GoodsUnit_id = isnull(dor.GoodsUnit_id, :DefaultGoodsUnit_id)
					{$fjoin}
					outer apply (
						select
							sum(dor2.DrugOstatRegistry_Kolvo) as DrugReg_Count
						from
							v_DrugOstatRegistry dor2 with (nolock)
							inner join v_Storage s2 with (nolock) on s2.Storage_id = dor2.Storage_id
							{$fjoin2}
						where (1=1)
							{$filter2}
					) drug_reg
					outer apply (
						select
							sum(dsz2.DrugStorageZone_Count) as Drug_Count
						from
							v_DrugStorageZone dsz2 with (nolock)
							inner join v_StorageZone sz2 with (nolock) on dsz2.StorageZone_id = sz2.StorageZone_id
						where
							dsz2.Drug_id = dor.Drug_id
							and sz2.Storage_id = dor.Storage_id
							and isnull(dor.PrepSeries_id,0) = isnull(dsz2.PrepSeries_id,0)
							and isnull(dor.DrugShipment_id,0) = isnull(dsz2.DrugShipment_id,0)
					) drug_sz
				where
					(1=1)
					{$filter}
			";

			//echo getDebugSql($query, $data); exit();
			$result = $this->queryResult($query, $params);
			if(is_array($result) && !empty($result[0]['DrugCountWsz']) && $result[0]['DrugCountWsz'] > 0 ){
				$without_sz = array(
					array(
						'StorageZone_id' => 0,
						'StorageZone_Address' => 'Без места хранения',
						'StorageUnitType_Name' => '',
						'GoodsUnit_Name' => $result[0]['GoodsUnit_Name'],
						'GoodsUnit_Nick' => $result[0]['GoodsUnit_Nick'],
						'DrugStorageZone_Count' => $result[0]['DrugCountWsz'],
						'DrugStorageZone_Name' => 'Без места хранения'.($data['isCountEnabled'] == 'true' ? ' / '.$result[0]['DrugCountWsz'].' / '.$result[0]['GoodsUnit_Name'] : '')
					)
				);
			}
		}


    	$select = "";
    	$where = "1=1";
    	$inner_select = "";
    	$inner_join = "";
    	$inner_where = "";
    	$join = "";

        if(!empty($data['StorageZone_id'])){
            $where .= " and sz.StorageZone_id = :StorageZone_id ";
        }

        $inner_where .= " and :Drug_id is not null "; //без указанного медикамента подзапрос должен быть пуст
        if(!empty($data['Drug_id'])){
    		$inner_where .= " and i_dsz.Drug_id = :Drug_id ";
    	}
    	if(!empty($data['DrugShipment_id'])){
    		$inner_where .= " and i_dsz.DrugShipment_id = :DrugShipment_id ";
        }

        if(!empty($data['Storage_id'])){
            $where .= " and sz.Storage_id = :Storage_id ";
        } else if(!empty($data['Contragent_id'])){
            $join .= "
                left join v_StorageStructLevel ssl with (nolock) on ssl.Storage_id = sz.Storage_id
                left join v_Contragent c with (nolock) on c.Org_id = ssl.Org_id
            ";
            $where .= " and c.Contragent_id = :Contragent_id ";
        }

        if(!empty($data['isPKU'])){
            $where .= " and isnull(sz.StorageZone_IsPKU, 1) = 2";
        }

        if ($data['isEdOstEnabled'] == 'true') {
            $select .= ", dsz.EdOst_Count";
            $select .= ", dsz.EdOst_GoodsUnit_Nick";
            $inner_select .= ", gu_ost.EdOst_Count";
            $inner_select .= ", gu_ost.EdOst_GoodsUnit_Nick";

            $inner_join .= "
                outer apply (
                    select
                        (
                            case
                                when ii_gu_b.GoodsUnit_id = ii_gu.GoodsUnit_id then i_dsz.DrugStorageZone_Count
                                when ii_gu_b.GoodsUnit_id <> ii_gu.GoodsUnit_id and ii_dus.DocumentUcStr_Count > 0 then (ii_dus.DocumentUcStr_EdCount/ii_dus.DocumentUcStr_Count)*i_dsz.DrugStorageZone_Count
                                when ii_dus.DocumentUcStr_Count = 0 then 0
                                else null
                            end
                        ) as EdOst_Count,
                        ii_gu.GoodsUnit_Nick as EdOst_GoodsUnit_Nick
                    from
                        v_DrugShipmentLink ii_dsl with (nolock)
                        left join v_DocumentUcStr ii_dus with (nolock) on ii_dus.DocumentUcStr_id = ii_dsl.DocumentUcStr_id
                        left join v_GoodsUnit ii_gu_b with (nolock) on ii_gu_b.GoodsUnit_id = isnull(ii_dus.GoodsUnit_bid, :DefaultGoodsUnit_id)
                        left join v_GoodsUnit ii_gu with (nolock) on ii_gu.GoodsUnit_id = ii_dus.GoodsUnit_id
                    where
                        ii_dsl.DrugShipment_id = i_dsz.DrugShipment_id
                ) gu_ost
            ";
        }

        $query = "
            select
                sz.StorageZone_id,
                isnull(dsz.DrugStorageZone_Count,0) as DrugStorageZone_Count,
                (
                    isnull(sz.StorageZone_Address, '')+
                    isnull(case when isnull(sz.StorageZone_IsPKU, 1) = 2 then ' НС и ПВ' else null end, '')
                ) as StorageZone_Address,
                isnull(dsz.StorageUnitType_Name, '') as StorageUnitType_Name,
                gu.GoodsUnit_Name,
                gu.GoodsUnit_Nick,
                (
                    isnull(sz.StorageZone_Address, '')+
                    isnull(case when isnull(sz.StorageZone_IsPKU, 1) = 2 then ' НС и ПВ' else null end, '')+
                    isnull(' / '+dsz.StorageUnitType_Name, '')+
                    ".($data['isCountEnabled'] == "true" ? "' / ' + isnull(cast(dsz.DrugStorageZone_Count as varchar(20)), '0')+" : "")."
                    isnull(' / '+gu.GoodsUnit_Name, '')
                ) as DrugStorageZone_Name
                {$select}
            from
            	v_StorageZone sz with (nolock)
            	outer apply (
                    select top 1
                        i_dsz.DrugStorageZone_Count,
                        i_dsz.GoodsUnit_id,
                        i_sut.StorageUnitType_Name
                        {$inner_select}
                    from
                        v_DrugStorageZone i_dsz with (nolock)
                        left join v_StorageUnitType i_sut with (nolock) on i_sut.StorageUnitType_id = sz.StorageUnitType_id
                        {$inner_join}
                    where
                        i_dsz.StorageZone_id = sz.StorageZone_id
                        {$inner_where}
                    order by
                        i_dsz.DrugStorageZone_Count desc
                ) dsz
                left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = isnull(dsz.GoodsUnit_id, :DefaultGoodsUnit_id)
            	{$join}
            where
                {$where}
        ";
        $res = $this->db->query($query, $data);

        $combo_data = array();

        if (is_object($res)) {
            $res = $res->result('array');
            if(count($res) > 0){
                $combo_data = array_merge($res, $combo_data);
            }
        }

        if(count($without_sz) > 0){
            $combo_data = array_merge($without_sz, $combo_data);
        }

        return $combo_data;
    }

    /**
     * Возвращает код статуса документа учета по его идентификатору
     */
    function getDrugDocumentStatusCode($document_id) {
        $query = "
            select
                dds.DrugDocumentStatus_Code
            from
                v_DocumentUc du with (nolock)
                left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
            where
                du.DocumentUc_id = :DocumentUc_id;
        ";
        return $this->getFirstResultFromQuery($query, array('DocumentUc_id' => $document_id));
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadLpuCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['Lpu_id'])) {
            $where[] = 'l.Lpu_id = :Lpu_id';
            $params['Lpu_id'] = $data['Lpu_id'];
        } else {
            if (!empty($data['query'])) {
                $where[] = 'l.Lpu_Nick like :query';
                $params['query'] = "%".$data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    select top 250
		        l.Lpu_id,
		        l.Lpu_Nick
		    from
                v_Lpu l with (nolock)
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadLpuBuildingCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['LpuBuilding_id'])) {
            $where[] = 'lb.LpuBuilding_id = :LpuBuilding_id';
            $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
        } else {
            if (!empty($data['Lpu_id'])) {
                $where[] = 'lb.Lpu_id = :Lpu_id';
                $params['Lpu_id'] = $data['Lpu_id'];
            }
            if (!empty($data['query'])) {
                $where[] = 'lb.LpuBuilding_Name like :query';
                $params['query'] = "%".$data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    select top 250
		        lb.LpuBuilding_id,
		        lb.LpuBuilding_Name
		    from
                v_LpuBuilding lb with (nolock)
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadGoodsUnitCombo($data) {
        $where = array();
        $params = $data;
        //$org_s_data = array();

        if (empty($data['Drug_id'])) {
            return false;
        }

        /*if (!empty($data['Contragent_sid'])) {
            $query = "
                select
                    ot.OrgType_SysNick
                from
                    v_Contragent c with (nolock)
                    left join v_Org o with (nolock) on o.Org_id = c.Org_id
                    left loin v_OrgType ot with (nolock) on ot.OrgType_id = o.OrgType_id
                where
                    c,Contragent_id = :Contragent_id
            ";
            $org_s_data = $this->getFirstRowFromQuery($query, array(
                'Contragent_id' => $data['Contragent_sid']
            ));
        }*/

        if (!empty($data['GoodsUnit_id'])) {
            $where[] = "gu.GoodsUnit_id = @GoodsUnit_id";
        } else {
            $descr_arr = array('единицы в упаковках');
            if (!empty($data['UserOrg_Type']) && $data['UserOrg_Type'] == 'lpu') { //если организация поставщика является ММО
                $descr_arr = array('единицы в упаковках', 'единицы количества', 'лекарственная форма');
            }
            $where[] = "(gpc.GoodsUnit_id is not null and gu.GoodsUnit_Descr in ('".join("', '", $descr_arr)."'))";

            if (!empty($data['query'])) {
                $where[] = "(gu.GoodsUnit_Nick like :query or gu.GoodsUnit_Name like :query)";
                $params['query'] = $data['query']."%";
            }
        }

        $where_clause = implode(" and ", $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    declare
                @GoodsUnit_id bigint = :GoodsUnit_id,
                @Drug_id bigint = :Drug_id,
                @DrugComplexMnn_id bigint = null,
                @Tradnames_id bigint = null;

            if (@Drug_id is not null)
            begin
                select
                    @DrugComplexMnn_id = DrugComplexMnn_id,
                    @Tradnames_id = d.DrugTorg_id
                from
                    rls.v_Drug d with (nolock)
                where
                    Drug_id = @Drug_id
            end;

            select
                gu.GoodsUnit_id,
                gu.GoodsUnit_Name,
                (
                    isnull(gu.GoodsUnit_Nick, '') +
                    isnull(' / ' + gu.GoodsUnit_Name, '') +
                    isnull(' / ' + (case
                        when gu.GoodsUnit_Name = 'упаковка' then '1'
                        else cast(cast(gpc.GoodsPackCount_Count as decimal(10,0)) as varchar(10))
                    end) + ' шт. в уп.', '')
                ) as GoodsUnit_Str,
                (case
                    when gu.GoodsUnit_Name = 'упаковка' then 1
                    else gpc.GoodsPackCount_Count
                end) as GoodsPackCount_Count,
                (case
                    when gpc.GoodsPackCount_Count is not null then 'table'
                    else null
                end) as GoodsPackCount_Source
            from
                v_GoodsUnit gu with (nolock)
                outer apply (
                    select top 1
                        i_gpc.GoodsUnit_id,
                        i_gpc.GoodsPackCount_Count
                    from
                        v_GoodsPackCount i_gpc with (nolock)
                    where
                        i_gpc.GoodsUnit_id = gu.GoodsUnit_id and
                        i_gpc.DrugComplexMnn_id = @DrugComplexMnn_id and
                        (
                            @Tradnames_id is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = @Tradnames_id
                        ) and
                        (
                            i_gpc.Org_id is null or
                            isnull(i_gpc.Org_id, 0) = isnull(:UserOrg_id, 0)
                        )
                    order by
                        i_gpc.TRADENAMES_ID desc, i_gpc.Org_id desc
                ) gpc
            {$where_clause}
            union
            select
                gu.GoodsUnit_id,
                gu.GoodsUnit_Name,
                (
                    isnull(gu.GoodsUnit_Nick, '') +
                    isnull(' / ' + gu.GoodsUnit_Name, '') +
                    ' / 1 шт. в уп.'
                ) as GoodsUnit_Str,
                1 as GoodsPackCount_Count,
                'fixed_value' as GoodsPackCount_Count
            from
                v_GoodsUnit gu with (nolock)
            where
                @GoodsUnit_id is null and -- упаковка добавляется в список только если не передан id конкретной записи
                gu.GoodsUnit_Name = 'упаковка' and
                (
                    :query is null or
                    'упаковка' like :query
                )
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * Загрузка списка отделений для комбобокса (фильтры АРМ Товароведа)
	 */
	function loadLpuSectionMerchCombo($data) {
        $with = array();
        $with = array();
		$join = array();
        $where = array();
        $params = $data;

        if (!empty($data['LpuSection_id'])) {
            $where[] = "ls.LpuSection_id = :LpuSection_id";
        } else {
            if (!empty($data['Lpu_id'])) {
				$where[] = "ls.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
            }

            if (!empty($data['LpuBuilding_id'])) {
				$where[] = "ls.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }

            if (!empty($data['MedService_Storage_id'])) {
				$with[] = "storage_tree (Storage_id, Storage_pid) as (
					select
						i_s.Storage_id,
						i_s.Storage_pid
					from
						v_Storage i_s with (nolock)
					where
						i_s.Storage_id = :MedService_Storage_id
					union all
					select
						i_s.Storage_id,
						i_s.Storage_pid
					from
						v_Storage i_s with (nolock)
						inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
				)";
				$with[] = "
					ls_list as (
						select distinct
							i_p.LpuSection_id
						from (
							select
								i_ls.LpuSection_id
							from
								storage_tree i_st
								left join v_StorageStructLevel i_ssl with (nolock) on i_ssl.Storage_id = i_st.Storage_id
								left join v_LpuSection i_ls with (nolock) on i_ls.LpuBuilding_id = i_ssl.LpuBuilding_id
							where
								i_ssl.Lpu_id is not null and
								i_ssl.LpuBuilding_id is null
							union all
							select
								i_ls.LpuSection_id
							from
								storage_tree i_st
								left join v_StorageStructLevel i_ssl with (nolock) on i_ssl.Storage_id = i_st.Storage_id
								left join v_LpuSection i_ls with (nolock) on i_ls.LpuBuilding_id = i_ssl.LpuBuilding_id
							where
								i_ssl.LpuBuilding_id is not null and
								i_ssl.LpuUnit_id is null
							union all
							select
								i_ls.LpuSection_id
							from
								storage_tree i_st
								left join v_StorageStructLevel i_ssl with (nolock) on i_ssl.Storage_id = i_st.Storage_id
								left join v_LpuSection i_ls with (nolock) on i_ls.LpuUnit_id = i_ssl.LpuUnit_id
							where
								i_ssl.LpuUnit_id is not null and
								i_ssl.LpuSection_id is null
							union all
							select
								i_ssl.LpuSection_id
							from
								storage_tree i_st
								left join v_StorageStructLevel i_ssl with (nolock) on i_ssl.Storage_id = i_st.Storage_id
							where
								i_ssl.LpuSection_id is not null
						) i_p
					)
				";
				$join[] = "left join ls_list on ls_list.LpuSection_id = ls.LpuSection_id";
				$where[] = "ls_list.LpuSection_id is not null";
				$params['MedService_Storage_id'] = $data['MedService_Storage_id'];
            }

            if (!empty($data['query'])) {
                $where[] = "ls.LpuSection_Name like :query";
                $params['query'] = $data['query']."%";
            }
        }

        $with_clause = implode(", ", $with);
        if (!empty($with_clause)) {
			$with_clause = "
				with
					{$with_clause}
			";
        }
        
		$join_clause = implode(" ", $join);

        $where_clause = implode(" and ", $where);
        if (!empty($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }
        
        $query = "
			{$with_clause}
            select top 250
                ls.LpuSection_id,
                ls.LpuSection_Name,
                ls.LpuSection_Code,
                ls.LpuBuilding_id
            from
                v_LpuSection ls with (nolock)
                {$join_clause}
            {$where_clause}
            order by
            	ls.LpuSection_Name
		";
        $result = $this->queryResult($query, $params);

        return $result;
    }

	/**
	 * Загрузка списка складов для комбобокса (фильтры АРМ Товароведа)
	 */
	function loadStorageMerchCombo($data) {
        $with = array();
		$join = array();
        $where = array();
        $params = $data;

        if (!empty($data['Storage_id'])) {
            $where[] = "s.Storage_id = :Storage_id";
        } else {
        	switch ($data['Field_Name']) {
				case 'Storage_id': //поле "склад"
					if ($data['UserOrg_Type'] == 'lpu' && empty($data['Org_id']) && !empty($data['MedService_id'])) { //тип организации пользователя - МО
						$with[] = "
							ssl_list as (
								select distinct
									i_p.Storage_id
								from ( 
									select
										i_ssl.Storage_id
									from
										v_StorageStructLevel i_ssl with (nolock)									
									where
										i_ssl.MedService_id = :MedService_id
									union all
									select
										i_ssl.Storage_id
									from
										v_StorageStructLevel i_ssl with (nolock)
										left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id
										left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
									where									
										i_mst.MedServiceType_SysNick = 'merch' and
										i_ssl.Lpu_id = :Lpu_id and
										(:LpuBuilding_id is null or i_ssl.LpuBuilding_id = :LpuBuilding_id) and
										(:LpuSection_id is null or i_ssl.LpuSection_id = :LpuSection_id)
								) i_p
							)
						";
						$with[] = "storage_tree (Storage_id, Storage_pid) as (
							select
								i_s.Storage_id,
								cast(null as bigint) as Storage_pid
							from
								ssl_list i_s with (nolock)
							union all
							select
								i_s.Storage_id,
								i_s.Storage_pid
							from
								v_Storage i_s with (nolock)
								inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
						)";
						$with[] = "
							ssl_list_2 as (								
								select distinct
									i_ssl.Storage_id
								from
									storage_tree
									left join v_StorageStructLevel i_ssl with (nolock) on i_ssl.Storage_id = storage_tree.Storage_id
								where
									storage_tree.Storage_pid is null and
									i_ssl.Lpu_id = :Lpu_id and
									(:LpuBuilding_id is null or i_ssl.LpuBuilding_id = :LpuBuilding_id) and
									(:LpuSection_id is null or i_ssl.LpuSection_id = :LpuSection_id)
							)
						";
						$join[] = "left join ssl_list on ssl_list.Storage_id = s.Storage_id";
						$join[] = "left join ssl_list_2 on ssl_list_2.Storage_id = s.Storage_id";
						$where[] = "(ssl_list.Storage_id is not null or ssl_list_2.Storage_id is not null)";
					}

					if ($data['UserOrg_Type'] != 'lpu' && !empty($data['Org_id']) && empty($data['MedService_id'])) { //тип организации пользователя - не МО
						$with[] = "
							ssl_list as (
								select distinct
									i_ssl.Storage_id
								from
									v_StorageStructLevel i_ssl with (nolock)									
								where
									i_ssl.Org_id = :Org_id
							)
						";
						$join[] = "left join ssl_list on ssl_list.Storage_id = s.Storage_id";
						$where[] = "ssl_list.Storage_id is not null";
					}
					break;
				case 'Storage_sid': //поле "склад поставщика"
				case 'Storage_tid': //поле "склад получателя"
					if (!empty($data['Org_id']) || !empty($data['UserOrg_id']) || !empty($data['MedService_Storage_id'])) { //поле "склад поставщика" или "склад получателя"
						if (!empty($data['Org_id']) || !empty($data['UserOrg_id'])) {
							$with[] = "
								ssl_list as (
									select distinct
										i_ms_ssl.Storage_id
									from
										v_StorageStructLevel i_ssl with (nolock)
										left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id
										left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
										left join v_StorageStructLevel i_ms_ssl with (nolock) on i_ms_ssl.MedService_id = i_ssl.MedService_id
									where
										i_ssl.Org_id = :Org_id and
										i_mst.MedServiceType_SysNick = 'merch'
								)
							";
							if (empty($data['Org_id'])) {
								$params['Org_id'] = $data['UserOrg_id'];
							}
							$join[] = "left join ssl_list on ssl_list.Storage_id = s.Storage_id";
						}

						if (!empty($data['MedService_Storage_id'])) {
							$with[] = "ssl_ms_list as (
								select distinct
									i_s.Storage_id
								from
									v_StorageStructLevel i_ssl with (nolock)
									left join v_Storage i_s with (nolock) on i_s.Storage_pid = i_ssl.Storage_id 
								where
									i_ssl.MedService_id = :MedService_Storage_id
							)";
							$with[] = "storage_tree (Storage_id, Storage_pid) as (
								select
									i_s.Storage_id,
									cast(null as bigint) as Storage_pid
								from
									ssl_ms_list i_s with (nolock)
								union all
								select
									i_s.Storage_id,
									i_s.Storage_pid
								from
									v_Storage i_s with (nolock)
									inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
							)";
							$join[] = "left join storage_tree on storage_tree.Storage_id = s.Storage_id";
						}

						if (!empty($params['Org_id']) && !empty($params['MedService_Storage_id'])) {
							$where[] = "(ssl_list.Storage_id is not null or storage_tree.Storage_id is not null)";
						} else if (!empty($params['Org_id'])) {
							$where[] = "ssl_list.Storage_id is not null";
						} else if (!empty($params['MedService_Storage_id'])) {
							$where[] = "storage_tree.Storage_id is not null";
						}
					}
					break;
			}

            if (!empty($data['query'])) {
                $where[] = "s.Storage_Name like :query";
                $params['query'] = $data['query']."%";
            }
        }

        $with_clause = implode(", ", $with);
        if (!empty($with_clause)) {
			$with_clause = "
				with
					{$with_clause}
			";
        }
        
		$join_clause = implode(" ", $join);

        $where_clause = implode(" and ", $where);
        if (!empty($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }
        
        $query = "
			{$with_clause}
            select top 250
                s.Storage_id,
                s.Storage_Name,
                s.Storage_Code
            from
                v_Storage s with (nolock)
                {$join_clause}
            {$where_clause}
            order by
            	s.Storage_Name
		";
        $result = $this->queryResult($query, $params);

        return $result;
    }

    /**
	 * Загрузка списка МОЛ для комбобокса (фильтры АРМ Товароведа)
	 */
	function loadMolMerchCombo($data) {
        $with = array();
		$join = array();
        $where = array();
        $params = $data;

        if (!empty($data['Mol_id'])) {
            $where[] = "m.Mol_id = :Mol_id";
        } else {
			if (!empty($data['Org_id']) || !empty($data['UserOrg_id']) || !empty($data['MedService_Storage_id'])) { //поле "склад поставщика" или "склад получателя"
				if (!empty($data['Org_id']) || !empty($data['UserOrg_id'])) {
					$with[] = "
						ssl_list as (
							select distinct
								i_ms_ssl.Storage_id
							from
								v_StorageStructLevel i_ssl with (nolock)
								left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id
								left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
								left join v_StorageStructLevel i_ms_ssl with (nolock) on i_ms_ssl.MedService_id = i_ssl.MedService_id
							where
								i_ssl.Org_id = :Org_id and
								i_mst.MedServiceType_SysNick = 'merch'
						)
					";
					if (!empty($data['Org_id'])) {
						$params['Org_id'] = $data['UserOrg_id'];
					}
					$join[] = "left join ssl_list on ssl_list.Storage_id = m.Storage_id";
				}

				if (!empty($data['MedService_Storage_id'])) {
					$with[] = "ssl_ms_list as (
						select distinct
							i_s.Storage_id
						from
							v_StorageStructLevel i_ssl with (nolock)
							left join v_Storage i_s with (nolock) on i_s.Storage_pid = i_ssl.Storage_id 
						where
							i_ssl.MedService_id = :MedService_Storage_id
					)";
					$with[] = "storage_tree (Storage_id, Storage_pid) as (
						select
							i_s.Storage_id,
							cast(null as bigint) as Storage_pid
						from
							ssl_ms_list i_s with (nolock)
						union all
						select
							i_s.Storage_id,
							i_s.Storage_pid
						from
							v_Storage i_s with (nolock)
							inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
					)";
					$join[] = "left join storage_tree on storage_tree.Storage_id = m.Storage_id";
				}

				if (!empty($params['Org_id']) && !empty($params['MedService_Storage_id'])) {
					$where[] = "(ssl_list.Storage_id is not null or storage_tree.Storage_id is not null)";
				} else if (!empty($params['Org_id'])) {
					$where[] = "ssl_list.Storage_id is not null";
				} else if (!empty($params['MedService_Storage_id'])) {
					$where[] = "storage_tree.Storage_id is not null";
				}
			}

            if (!empty($data['query'])) {
                $where[] = "mp.Person_Fio like :query";
                $params['query'] = $data['query']."%";
            }
        }

        $with_clause = implode(", ", $with);
        if (!empty($with_clause)) {
			$with_clause = "
				with
					{$with_clause}
			";
        }

		$join_clause = implode(" ", $join);

        $where_clause = implode(" and ", $where);
        if (!empty($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
			{$with_clause}
            select top 250
                m.Mol_id,
                mp.Person_Fio as  Mol_Name,
                m.Mol_Code
            from
                v_Mol m with (nolock)
                left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = m.MedPersonal_id
                {$join_clause}
            {$where_clause}
            order by
            	mp.Person_Fio
		";
        $result = $this->queryResult($query, $params);

        return $result;
    }

    /**
     * Сохранение информации о связи документа с ЛПУ и подразделением
     */
    function saveDocumentUcLink($data) {
        $action = null;
        $response = array('Error_Msg' => 'Не удалось сохранить информацию о связи документа с ЛПУ и подразделением');
        $link_id = null;
        $not_empty_data = (!empty($data['Lpu_id']) || !empty($data['LpuBuilding_id']));

        if (empty($data['DocumentUc_id'])) {
            return $response;
        }

        //поиск существующей записи
        $query = "
            select top 1
                DocumentUcLink_id
            from
                v_DocumentUcLink with (nolock)
            where
                DocumentUc_id = :DocumentUc_id
            order by
                DocumentUcLink_id;
        ";
        $link_id = $this->getFirstResultFromQuery($query, array(
            'DocumentUc_id' =>  $data['DocumentUc_id']
        ));

        if (!empty($link_id)) {
            if ($not_empty_data) {
                $action = 'edit';
            } else {
                $action = 'delete';
            }
        } else {
            if ($not_empty_data) {
                $action = 'add';
            }
        }

        switch($action) {
            case 'add':
            case 'edit':
                $response = $this->saveObject('DocumentUcLink', array(
                    'DocumentUcLink_id' => $link_id,
                    'DocumentUc_id' => $data['DocumentUc_id'],
                    'Lpu_id' => $data['Lpu_id'],
                    'LpuBuilding_id' => $data['LpuBuilding_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                break;
            case 'delete':
                $response = $this->deleteObject('DocumentUcLink', array(
                    'DocumentUcLink_id' => $link_id
                ));
                break;
            default:
                $response = array();
                break;
        }

        return $response;
    }

    /**
     * Получение данных по связи строки документа и регистра остатков
     */
    function getDrugOstatRegistryLink($data) {
    	$query = "
            select top 1
                DrugOstatRegistryLink_id,
                DrugOstatRegistry_id,
                DrugOstatRegistryLink_Count
            from
                v_DrugOstatRegistryLink with (nolock)
            where
                DrugOstatRegistryLink_TableName = 'DocumentUcStr' and
                DrugOstatRegistryLink_TableID = :DocumentUcStr_id
        ";
        $link = $this->queryResult($query, array('DocumentUcStr_id' => $data['DocumentUcStr_id']));
		return $link;
    }

    /**
     * Получение данных по записи регистра остатков
     */
    function getDrugOstatRegistryById($data) {
    	$query = "
            select top 1
                *
            from
                v_DrugOstatRegistry with (nolock)
            where
                DrugOstatRegistry_id = :DrugOstatRegistry_id
        ";
        $link = $this->queryResult($query, array('DrugOstatRegistry_id' => $data['DrugOstatRegistry_id']));
		return $link;
    }

    /**
     * Корректировка остатков медикаментов в таблице связи документа учета и регистра остатков
     *
     * Используется при создании/редактировании документов Списание медикаментов из укладки на пациента
     * корректируется количество медикамента в таблице связи документа учета о передачи укладки на подотчет и регистра остатков (субсчета Резерв)
	 *
     * Будет вызываться из внешней транзакции, поэтому транзакции в методе нет, а при возврате методом ошибки изменения будут откатываться
     *
     * Не понял назначения этой функции, поэтому отключил. Кроме того, редактирование информации о связи строки документа учета с резервом, без редактирования самого резерва (строки в регистре остатков) недопустимо. Salakhov R.
     */
    /*function correctDrugOstatRegistryLink($data) {
    	$this->load->model("StorageZone_model");
    	if(
    		empty($data['StorageZone_id'])
    		|| empty($data['EmergencyTeam_id'])
    		|| empty($data['Drug_id'])
    		|| empty($data['DocumentUcStr_oid'])
    	) {
    		return array('Error_Msg'=>'Не переданы обязательные параметры для выполнения операции корректировки остатков медикаментов в укладке.');
    	}
    	// Поиск документа учета со статусом Подотчет действующий
    	$duParams = array(
    		'StorageZone_id'=> $data['StorageZone_id'],
    		'DrugDocumentType_id'=> 29, // Тип документа - Передача укладки на подотчет
    		'StorageZoneLiable_ObjectId'=> null //$data['EmergencyTeam_id'] // передаем null чтобы поиск шел без привязки к конкретной бригаде - чтобы могли списывать и другие бригады
    	);
		$du = $this->StorageZone_model->findDocumentUcWithStorageZoneLiable($duParams);
		if(!is_array($du)){
			return array('Error_Msg'=>'Ошибка при поиске документа учета, связанного с подотчетным лицом.');
		}
		if(empty($du[0]['DocumentUc_id'])){
			return array('Error_Msg'=>'Не найдены документы о передаче на подотчет.');
		}
		$du_arr = array();
		foreach ($du as $doc) {
			if(!empty($doc['DocumentUc_id'])){
				array_push($du_arr, $doc['DocumentUc_id']);
			}
		}
		$du_str = implode(',', $du_arr);
		$query = "
            select top 1
                dus.DocumentUcStr_id
            from
                v_DocumentUcStr dus with (nolock)
            where
            	dus.Drug_id = :Drug_id
            	and dus.DocumentUcStr_oid = :DocumentUcStr_oid
                and dus.DocumentUc_id in (".$du_str.");
        ";
        $dus = $this->queryResult($query, array(
            'Drug_id' => $data['Drug_id'],
            'DocumentUcStr_oid' => $data['DocumentUcStr_oid']
        ));
        if (!is_array($dus) || empty($dus[0]['DocumentUcStr_id'])) {
            return array('Error_Msg'=>'Не найдена строка документа о передаче на подотчет.');
        }
        //Данные по связи резерва и документа учета о передаче укладки на подотчет
        $link = $this->getDrugOstatRegistryLink(array('DocumentUcStr_id' => $dus[0]['DocumentUcStr_id']));
		if(empty($link[0]['DrugOstatRegistryLink_id'])){
			return array('Error_Msg'=>'Не найден резерв под медикамент из документа учета о передаче на подотчет.');
		}
		$linkParams = $link[0];
    	if(empty($data['DocumentUcStr_id'])){
    		// Если добавляем новый документ - просто вычитаем нужное количество
    		if(floatval($link[0]['DrugOstatRegistryLink_Count']) < floatval($data['DocumentUcStr_Count'])){
    			return array('Error_Msg'=>'Недостаточное количество медикамента в резерве под укладку.');
    		}
    		$linkParams['DrugOstatRegistryLink_Count'] = floatval($link[0]['DrugOstatRegistryLink_Count']) - floatval($data['DocumentUcStr_Count']);
    	} else {
    		// Загружаем старые данные по строке документа
    		// здесь придут старые данные - т.к. метод correctDrugOstatRegistryLink вызывается до обновления данных в строке
    		$docUcStr = $this->loadDocumentUcStr($data);
    		if(!is_array($docUcStr) || empty($docUcStr[0]['DocumentUcStr_id'])){
    			return array('Error_Msg'=>'Ошибка при получении данных по строке документа учета.');
    		}
    		// количество которое было до создания строки
    		$before = floatval($link[0]['DrugOstatRegistryLink_Count']) + floatval($docUcStr[0]['DocumentUcStr_Count']);
    		// сравниваем изначальное количество с тем что сохраним сейчас
    		if($before < floatval($data['DocumentUcStr_Count'])){
    			return array('Error_Msg'=>'Недостаточное количество медикамента в резерве под укладку.');
    		}
    		// из изначального количества вычитаем сохраняемое сейчас количество
    		$linkParams['DrugOstatRegistryLink_Count'] = $before - floatval($data['DocumentUcStr_Count']);
    	}

		$linkParams['DrugOstatRegistryLink_TableName'] = 'DocumentUcStr';
		$linkParams['DrugOstatRegistryLink_TableID'] = $dus[0]['DocumentUcStr_id'];
		$linkParams['pmUser_id'] = $data['pmUser_id'];
		$response = $this->saveObject('DrugOstatRegistryLink', $linkParams);
		if(!is_array($response) || !empty($response['Error_Msg'])){
			return array('Error_Msg'=>'Ошибка при обновлении данных резерва медикамента.'.(!empty($response['Error_Msg'])?$response['Error_Msg']:''));
		}
    	return array();
    }*/

    /**
     * Возврат медикаментов в укладку при удалении документа Списание медикаментов из укладки на пациента
     *
     * Используется при удалении документов Списание медикаментов из укладки на пациента
     * корректируется количество медикамента в таблице связи документа учета о передачи укладки на подотчет и регистра остатков (субсчет Резерв) - DrugOstatRegistryLink
     * корректируется количество медикамента в регистра остатков (субсчет Резерв) - DrugOstatRegistry
     * корректируется количество медикамента в укладке - DrugStorageZone
	 *
     * Будет вызываться из внешней транзакции, поэтому транзакции в методе нет, а при возврате методом ошибки изменения будут откатываться
     */
    function returnDrugsToPack($data) {
    	if(empty($data['DocumentUcStr_id'])) {
    		return array('Error_Msg'=>'Не переданы обязательные параметры для выполнения операции корректировки остатков медикаментов в укладке.');
    	}
    	$this->load->model("StorageZone_model");

    	// Получим данные по удаляемой строке
    	$docUcStr = $this->loadDocumentUcStr($data);
		if(!is_array($docUcStr) || empty($docUcStr[0]['DocumentUcStr_id']) || empty($docUcStr[0]['Drug_id']) || empty($docUcStr[0]['DocumentUcStr_oid'])){
			return array('Error_Msg'=>'Ошибка при получении данных по строке документа учета.');
		}
		$docUcStr = $docUcStr[0];

		if($docUcStr['DrugDocumentStatus_id'] == 2){
			// Сперва находим родительский документ учета для строки и берем оттуда место хранения и бригаду СМП
	    	// они нужны чтобы найти документы учета о передаче на подотчет
	    	$query = "
	            select top 1
	                du.StorageZone_sid as StorageZone_id,
	                du.EmergencyTeam_id
	            from
	                v_DocumentUcStr dus with (nolock)
	                inner join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
	            where
	            	dus.DocumentUcStr_id = :DocumentUcStr_id;
	        ";
	        $sz_et = $this->queryResult($query, array(
	            'DocumentUcStr_id' => $data['DocumentUcStr_id']
	        ));
	        if (!is_array($sz_et) || empty($sz_et[0]['StorageZone_id']) || empty($sz_et[0]['EmergencyTeam_id'])) {
	            return array('Error_Msg'=>'Не найдены данные о месте хранения и бригаде в документе учета Списание медикаментов из укладки на пациента.');
	        }

	        // Поиск документа учета о передаче на подотчет со статусом Подотчет действующий
	    	$duParams = array(
	    		'StorageZone_id'=> $sz_et[0]['StorageZone_id'],
	    		'DrugDocumentType_id'=> 29, // Тип документа - Передача укладки на подотчет
	    		'StorageZoneLiable_ObjectId'=> $sz_et[0]['EmergencyTeam_id']
	    	);
			$du = $this->StorageZone_model->findDocumentUcWithStorageZoneLiable($duParams);
			if(!is_array($du)){
				return array('Error_Msg'=>'Ошибка при поиске документа учета, связанного с подотчетным лицом.');
			}
			if(empty($du[0]['DocumentUc_id'])){
				return array('Error_Msg'=>'Не найдены документы о передаче на подотчет.');
			}

			// Ищем строки документов учета о передаче на подотчет с медикаментом и серией как у удаляемой строки
			$du_arr = array();
			foreach ($du as $doc) {
				if(!empty($doc['DocumentUc_id'])){
					array_push($du_arr, $doc['DocumentUc_id']);
				}
			}
	    	$du_str = implode(',', $du_arr);
			$query = "
	            select top 1
	                dus.DocumentUcStr_id
	            from
	                v_DocumentUcStr dus with (nolock)
	            where
	            	dus.Drug_id = :Drug_id
	            	and dus.DocumentUcStr_oid = :DocumentUcStr_oid
	                and dus.DocumentUc_id in (".$du_str.");
	        ";
	        $dus = $this->queryResult($query, array(
	            'Drug_id' => $docUcStr['Drug_id'],
	            'DocumentUcStr_oid' => $docUcStr['DocumentUcStr_oid']
	        ));
	        if (!is_array($dus) || empty($dus[0]['DocumentUcStr_id'])) {
	            return array('Error_Msg'=>'Не найдена строка документа о передаче на подотчет.');
	        }

	    	// Данные по связи резерва и документа учета о передаче укладки на подотчет
	        $link = $this->getDrugOstatRegistryLink(array('DocumentUcStr_id' => $dus[0]['DocumentUcStr_id']));
			if(empty($link[0]['DrugOstatRegistryLink_id']) || empty($link[0]['DrugOstatRegistry_id'])){
				return array('Error_Msg'=>'Не найден резерв под медикамент из документа учета о передаче на подотчет.');
			}

			// Возвращаем медикамент в таблицу связи строки и регистра
			$linkParams = $link[0];
			// Возвращаем количество из удаляемой строки
			$linkParams['DrugOstatRegistryLink_Count'] = floatval($link[0]['DrugOstatRegistryLink_Count']) + floatval($docUcStr['DocumentUcStr_Count']);
			$linkParams['DrugOstatRegistryLink_TableName'] = 'DocumentUcStr';
			$linkParams['DrugOstatRegistryLink_TableID'] = $dus[0]['DocumentUcStr_id'];
			$linkParams['pmUser_id'] = $data['pmUser_id'];
			$response = $this->saveObject('DrugOstatRegistryLink', $linkParams);
			if(!is_array($response) || !empty($response['Error_Msg'])){
				return array('Error_Msg'=>'Ошибка при обновлении данных резерва медикамента.'.(!empty($response['Error_Msg'])?$response['Error_Msg']:''));
			}

			// Получаем данные по записи регистра остатков
			$dor = $this->getDrugOstatRegistryById(array('DrugOstatRegistry_id'=>$link[0]['DrugOstatRegistry_id']));
			if(empty($dor[0]['DrugOstatRegistry_id'])){
				return array('Error_Msg'=>'Не найден резерв под медикамент из документа учета о передаче на подотчет.');
			}
			$dor = $dor[0];

			// Возвращаем медикамент в регистр остатов на субсчет Резерв
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec xp_DrugOstatRegistry_count
					@Contragent_id = :Contragent_id,
					@Org_id = :Org_id,
					@Storage_id = :Storage_id,
					@DrugShipment_id = :DrugShipment_id,
					@Drug_id = :Drug_id,
					@PrepSeries_id = :PrepSeries_id,
					@SubAccountType_id = 2, -- субсчёт Резерв
					@Okei_id = :Okei_id,
					@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
					@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
	                @InnerTransaction_Disabled = 1,
					@GoodsUnit_id = :GoodsUnit_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$params = array(
				'Contragent_id' => $dor['Contragent_id'],
				'Org_id' => $dor['Org_id'],
				'Storage_id' => $dor['Storage_id'],
				'DrugShipment_id' => $dor['DrugShipment_id'],
				'Drug_id' => $dor['Drug_id'],
				'PrepSeries_id' => $dor['PrepSeries_id'],
				'Okei_id' => $dor['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $docUcStr['DocumentUcStr_Count'],
				'DrugOstatRegistry_Sum' => $dor['DrugOstatRegistry_Cost']*$docUcStr['DocumentUcStr_Count'],
				'DrugOstatRegistry_Cost' => $dor['DrugOstatRegistry_Cost'],
				'GoodsUnit_id' => $dor['GoodsUnit_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->getFirstRowFromQuery($query, $params);

			if ($result !== false) {
				if (!empty($result['Error_Msg'])) {
					return array('Error_Msg' => 'Ошибка редактирования регистра остатков');
				}
			} else {
				return array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков');
			}

			// Ищем медикамент в укладке
			$query = "
	            select top 1
	                dsz.DrugStorageZone_id,
	                dsz.PrepSeries_id,
	                dsz.DrugStorageZone_Price,
	                dsz.DrugStorageZone_Count
	            from
	                v_DrugStorageZone dsz with (nolock)
	            where
	            	dsz.StorageZone_id = :StorageZone_id
	            	and dsz.DrugShipment_id = :DrugShipment_id
	                and dsz.Drug_id = :Drug_id;
	        ";
	        $dsz = $this->queryResult($query, array(
	            'Drug_id' => $dor['Drug_id'],
	            'DrugShipment_id' => $dor['DrugShipment_id'],
	            'StorageZone_id'=> $sz_et[0]['StorageZone_id']
	        ));
	        if (!is_array($dsz)) {
	            return array('Error_Msg'=>'Ошибка при поиске медикамента в укладке.');
	        }

	        // Возвращаем медикамент в укладку
	        $dszParams = array();
	        if(!empty($dsz[0]['DrugStorageZone_id'])){
	        	$dszParams = $dsz[0];
	        	$dszParams['DrugStorageZone_Count'] = floatval($dsz[0]['DrugStorageZone_Count']) + floatval($docUcStr['DocumentUcStr_Count']);
	        } else {
	        	$dszParams['DrugStorageZone_id'] = null;
	        	$dszParams['PrepSeries_id'] = $dor['PrepSeries_id'];
	        	$dszParams['DrugStorageZone_Price'] = $dor['DrugOstatRegistry_Cost'];
	        	$dszParams['DrugStorageZone_Count'] = $docUcStr['DocumentUcStr_Count'];
	        }
	        $dszParams['Drug_id'] = $dor['Drug_id'];
	        $dszParams['DrugShipment_id'] = $dor['DrugShipment_id'];
	        $dszParams['StorageZone_id'] = $sz_et[0]['StorageZone_id'];
	        $dszParams['pmUser_id'] = $data['pmUser_id'];
	        $response = $this->saveObject('DrugStorageZone', $dszParams);
			if(!is_array($response) || !empty($response['Error_Msg'])){
				return array('Error_Msg'=>'Ошибка при обновлении количества медикамента в укладке.'.(!empty($response['Error_Msg'])?$response['Error_Msg']:''));
			}
	    	return array();
		} elseif ($docUcStr['DrugDocumentStatus_id'] == 1) {
			//если статус строки Новый - значит это укладка не переданная под отчет - просто вернем резерв
	        $result = $this->removeReserve(array(
	            'DocumentUcStr_id' => $data['DocumentUcStr_id'],
	            'pmUser_id' => $data['pmUser_id']
	        ));
	        if (!empty($result['Error_Msg'])) {
	        	return array('Error_Msg'=>'Ошибка при удалении резерва под строку документа учета.'.(!empty($result['Error_Msg'])?$result['Error_Msg']:''));
	        }
	        return array();
		} else {
			return array('Error_Msg'=>'Ошибка при получении данных по строке документа учета.');
		}
    }

    /**
     * Списание медикаментов с укладки на пациента
     * Используется при добавлении медикаментов в карту 110у
     *
     * Если статус документа - исполнен - документы у которых места хранения переданы на подотчет
     * корректируется количество медикамента в таблице связи документа учета о передачи укладки на подотчет и регистра остатков (субсчет Резерв) - DrugOstatRegistryLink
     * корректируется количество медикамента в регистра остатков (субсчет Резерв) - DrugOstatRegistry
     * корректируется количество медикамента в укладке - DrugStorageZone
     *
     * Если статус документа - новый - документы у которых места хранения не переданы на подотчет
     * создается резерв под текущий документ
     * исполнение такого документа будет осуществляться вручную
	 *
     * Будет вызываться из внешней транзакции, поэтому транзакции в методе нет, а при возврате методом ошибки изменения будут откатываться
     */
    function removeDrugsFromPack($data) {
    	if(empty($data['DocumentUc_id'])) {
    		return array('Error_Msg'=>'Не переданы обязательные параметры для выполнения операции корректировки остатков медикаментов в укладке.');
    	}
    	$this->load->model("StorageZone_model");

        $default_goods_unit_id = $this->getDefaultGoodsUnitId();

    	$docUc = $this->_getDocumentUcDataLite($data);
    	if(empty($docUc[0]['DocumentUc_id'])){
			return array('Error_Msg'=>'Ошибка при получении данных по документу учета.');
		}

		if($docUc[0]['DrugDocumentStatus_id'] == 2){
			$docUcStrs = $this->_getDocumentUcStr($data);
			if(empty($docUcStrs[0]['DocumentUcStr_id'])){
				return array('Error_Msg'=>'Ошибка при получении данных по документу учета.');
			}
			foreach ($docUcStrs as $strData) {
				// Получим данные по строке
		    	$docUcStr = $this->loadDocumentUcStr($strData);
				if(!is_array($docUcStr) || empty($docUcStr[0]['DocumentUcStr_id']) || empty($docUcStr[0]['Drug_id']) || empty($docUcStr[0]['DocumentUcStr_oid'])){
					return array('Error_Msg'=>'Ошибка при получении данных по строке документа учета.');
				}
				$docUcStr = $docUcStr[0];

		    	// Сперва находим родительский документ учета для строки и берем оттуда место хранения и бригаду СМП
		    	// они нужны чтобы найти документы учета о передаче на подотчет
		    	$query = "
		            select top 1
		                du.StorageZone_sid as StorageZone_id,
		                du.EmergencyTeam_id
		            from
		                v_DocumentUcStr dus with (nolock)
		                inner join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
		            where
		            	dus.DocumentUcStr_id = :DocumentUcStr_id;
		        ";
		        $sz_et = $this->queryResult($query, array(
		            'DocumentUcStr_id' => $strData['DocumentUcStr_id']
		        ));
		        if (!is_array($sz_et) || empty($sz_et[0]['StorageZone_id']) || empty($sz_et[0]['EmergencyTeam_id'])) {
		            return array('Error_Msg'=>'Не найдены данные о месте хранения и бригаде в документе учета Списание медикаментов из укладки на пациента.');
		        }

		        // Поиск документа учета о передаче на подотчет со статусом Подотчет действующий
		    	$duParams = array(
		    		'StorageZone_id'=> $sz_et[0]['StorageZone_id'],
		    		'DrugDocumentType_id'=> 29, // Тип документа - Передача укладки на подотчет
		    		'StorageZoneLiable_ObjectId'=> $sz_et[0]['EmergencyTeam_id']
		    	);
				$du = $this->StorageZone_model->findDocumentUcWithStorageZoneLiable($duParams);
				if(!is_array($du)){
					return array('Error_Msg'=>'Ошибка при поиске документа учета, связанного с подотчетным лицом.');
				}
				if(empty($du[0]['DocumentUc_id'])){
					return array('Error_Msg'=>'Не найдены документы о передаче на подотчет.');
				}

				// Ищем строки документов учета о передаче на подотчет с медикаментом и серией как у строки
				$du_arr = array();
				foreach ($du as $doc) {
					if(!empty($doc['DocumentUc_id'])){
						array_push($du_arr, $doc['DocumentUc_id']);
					}
				}
		    	$du_str = implode(',', $du_arr);
				$query = "
		            select top 1
		                dus.DocumentUcStr_id
		            from
		                v_DocumentUcStr dus with (nolock)
		            where
		            	dus.Drug_id = :Drug_id
		            	and isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_bid, :DefaultGoodsUnit_id)
		            	and dus.DocumentUcStr_oid = :DocumentUcStr_oid
		                and dus.DocumentUc_id in (".$du_str.");
		        ";
		        $dus = $this->queryResult($query, array(
		            'Drug_id' => $docUcStr['Drug_id'],
		            'GoodsUnit_bid' => $docUcStr['GoodsUnit_bid'],
		            'DocumentUcStr_oid' => $docUcStr['DocumentUcStr_oid'],
					'DefaultGoodsUnit_id' => $default_goods_unit_id
		        ));
		        if (!is_array($dus) || empty($dus[0]['DocumentUcStr_id'])) {
		            return array('Error_Msg'=>'Не найдена строка документа о передаче на подотчет.');
		        }

		    	// Данные по связи резерва и документа учета о передаче укладки на подотчет
		        $link = $this->getDrugOstatRegistryLink(array('DocumentUcStr_id' => $dus[0]['DocumentUcStr_id']));
				if(empty($link[0]['DrugOstatRegistryLink_id']) || empty($link[0]['DrugOstatRegistry_id'])){
					return array('Error_Msg'=>'Не найден резерв под медикамент из документа учета о передаче на подотчет.');
				}

				// Уменьшаем количество медикамента в таблице связи строки и регистра
				$linkParams = $link[0];
				// Уменьшаем количество медикамента
				if(floatval($link[0]['DrugOstatRegistryLink_Count']) < floatval($docUcStr['DocumentUcStr_Count'])){
					return array('Error_Msg'=>'Недостаточное количество медикамента в резерве под документ учета о передаче на подотчет.');
				}
				$linkParams['DrugOstatRegistryLink_Count'] = floatval($link[0]['DrugOstatRegistryLink_Count']) - floatval($docUcStr['DocumentUcStr_Count']);
				$linkParams['DrugOstatRegistryLink_TableName'] = 'DocumentUcStr';
				$linkParams['DrugOstatRegistryLink_TableID'] = $dus[0]['DocumentUcStr_id'];
				$linkParams['pmUser_id'] = $data['pmUser_id'];
				$response = $this->saveObject('DrugOstatRegistryLink', $linkParams);
				if(!is_array($response) || !empty($response['Error_Msg'])){
					return array('Error_Msg'=>'Ошибка при обновлении данных резерва медикамента.'.(!empty($response['Error_Msg'])?$response['Error_Msg']:''));
				}

				// Получаем данные по записи регистра остатков
				$dor = $this->getDrugOstatRegistryById(array('DrugOstatRegistry_id'=>$link[0]['DrugOstatRegistry_id']));
				if(empty($dor[0]['DrugOstatRegistry_id'])){
					return array('Error_Msg'=>'Не найден резерв под медикамент из документа учета о передаче на подотчет.');
				}
				$dor = $dor[0];

				// Списываем медикамент с регистра остатов с субсчета Резерв
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec xp_DrugOstatRegistry_count
						@Contragent_id = :Contragent_id,
						@Org_id = :Org_id,
						@Storage_id = :Storage_id,
						@DrugShipment_id = :DrugShipment_id,
						@Drug_id = :Drug_id,
						@PrepSeries_id = :PrepSeries_id,
						@SubAccountType_id = 2, -- субсчёт Резерв
						@Okei_id = :Okei_id,
						@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
						@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
						@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
		                @InnerTransaction_Disabled = 1,
						@GoodsUnit_id = :GoodsUnit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$params = array(
					'Contragent_id' => $dor['Contragent_id'],
					'Org_id' => $dor['Org_id'],
					'Storage_id' => $dor['Storage_id'],
					'DrugShipment_id' => $dor['DrugShipment_id'],
					'Drug_id' => $dor['Drug_id'],
					'PrepSeries_id' => $dor['PrepSeries_id'],
					'Okei_id' => $dor['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $docUcStr['DocumentUcStr_Count']*(-1),
					'DrugOstatRegistry_Sum' => $dor['DrugOstatRegistry_Cost']*$docUcStr['DocumentUcStr_Count']*(-1),
					'DrugOstatRegistry_Cost' => $dor['DrugOstatRegistry_Cost'],
					'GoodsUnit_id' => $dor['GoodsUnit_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->getFirstRowFromQuery($query, $params);

				if ($result !== false) {
					if (!empty($result['Error_Msg'])) {
						return array('Error_Msg' => 'Ошибка редактирования регистра остатков');
					}
				} else {
					return array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков');
				}

				// Ищем медикамент в укладке
				$query = "
		            select top 1
		                dsz.DrugStorageZone_id,
		                dsz.PrepSeries_id,
		                dsz.DrugStorageZone_Price,
		                dsz.DrugStorageZone_Count
		            from
		                v_DrugStorageZone dsz with (nolock)
		            where
		            	dsz.StorageZone_id = :StorageZone_id
		            	and dsz.DrugShipment_id = :DrugShipment_id
		                and dsz.Drug_id = :Drug_id
		                and isnull(dsz.GoodsUnit_id, :DefaultGoodsUnit_id) = isnull(:GoodsUnit_id, :DefaultGoodsUnit_id);
		        ";
		        $dsz = $this->queryResult($query, array(
		            'Drug_id' => $dor['Drug_id'],
		            'DrugShipment_id' => $dor['DrugShipment_id'],
		            'StorageZone_id'=> $sz_et[0]['StorageZone_id'],
		            'GoodsUnit_id'=> $dor['GoodsUnit_id'],
		            'DefaultGoodsUnit_id' => $default_goods_unit_id
		        ));
		        if (!is_array($dsz)) {
		            return array('Error_Msg'=>'Ошибка при поиске медикамента в укладке.');
		        }

		        // Возвращаем медикамент в укладку
		        $dszParams = array();
		        if(!empty($dsz[0]['DrugStorageZone_id'])){
		        	$dszParams = $dsz[0];
		        	if(floatval($dsz[0]['DrugStorageZone_Count']) < floatval($docUcStr['DocumentUcStr_Count'])){
		        		return array('Error_Msg'=>'Недостаточное количество медикамента в укладке.');
		        	}
		        	$dszParams['DrugStorageZone_Count'] = floatval($dsz[0]['DrugStorageZone_Count']) - floatval($docUcStr['DocumentUcStr_Count']);
		        } else {
		        	$dszParams['DrugStorageZone_id'] = null;
		        	$dszParams['PrepSeries_id'] = $dor['PrepSeries_id'];
		        	$dszParams['DrugStorageZone_Price'] = $dor['DrugOstatRegistry_Cost'];
		        	$dszParams['DrugStorageZone_Count'] = $docUcStr['DocumentUcStr_Count'];
		        }
		        $dszParams['Drug_id'] = $dor['Drug_id'];
		        $dszParams['DrugShipment_id'] = $dor['DrugShipment_id'];
		        $dszParams['StorageZone_id'] = $sz_et[0]['StorageZone_id'];
		        $dszParams['pmUser_id'] = $data['pmUser_id'];
		        $response = $this->saveObject('DrugStorageZone', $dszParams);
				if(!is_array($response) || !empty($response['Error_Msg'])){
					return array('Error_Msg'=>'Ошибка при обновлении количества медикамента в укладке.'.(!empty($response['Error_Msg'])?$response['Error_Msg']:''));
				}

				// Добавляем запись в журнал перемещений по местам хранения
	            $result = $this->_commitStorageDrugMove(array(
	        		'Drug_id' => $dor['Drug_id'],
	        		'DocumentUcStr_id' => $docUcStr['DocumentUcStr_id'],
	        		'PrepSeries_id' => $dor['PrepSeries_id'],
	        		'DrugShipment_id' => $dor['DrugShipment_id'],
	        		'StorageZone_nid' => null,
	        		'StorageZone_oid' =>$sz_et[0]['StorageZone_id'],
	        		'StorageDrugMove_Price' => $dor['DrugOstatRegistry_Cost'],
	        		'StorageDrugMove_Count' => $docUcStr['DocumentUcStr_Count'],
	        		'GoodsUnit_id' => $docUcStr['GoodsUnit_bid'],
	        		'pmUser_id' => $data['pmUser_id']
	        	));
	        	if (!$this->isSuccessful($result)) {
	                return array('Error_Msg'=>'Ошибка при отметке о перемещении медикамента на место хранения'.$result[0]['Error_Msg']);
	            }
			}

	    	return array();
		} elseif ($docUc[0]['DrugDocumentStatus_id'] == 1) {
			// Проверим есть ли строки в документе
			$response = $this->_getDocumentUcStr($data);
			if(is_array($response) && count($response)>0 && empty($response['Error_Msg']) && empty($response[0]['Error_Msg'])){
				// логика такая же как при передаче укладки на подотчет - зарезервировать медикаменты
				$response = $this->updateDrugOstatRegistryForDocPeredUk($data);
				if(!empty($response[0]['Error_Msg'])){
					return array('Error_Msg'=>'Ошибка при резервировании по документ учета.'.(!empty($response[0]['Error_Msg'])?$response[0]['Error_Msg']:''));
				}
			}

			return array();
		} else {
			return array('Error_Msg'=>'Ошибка при получении данных по документу учета.');
		}
    }

    /**
     * Получение первой невыполненой работы по идентификатору документа учета
     */
    function getFirstUncompletedStorageWork($doc_id) {
        $rec = 0;
        if ($doc_id > 0) {
            $query = "
                select top 1
                    dusw.DocumentUcStorageWork_id,
                    dusw.WorkPlace_cid,
                    dusw.WorkPlace_eid,
                    dusw.Post_cid,
                    dusw.Person_cid,
                    dusw.Post_eid,
                    dusw.Person_eid,
                    dusw.DocumentUcTypeWork_id,
                    dusw.DocumentUcStr_id
                from
                    v_DocumentUcStr dus with (nolock)
                    inner join v_DocumentUcStorageWork dusw with (nolock) on dusw.DocumentUcStr_id = dus.DocumentUcStr_id
                where
                    dus.DocumentUc_id = :DocumentUc_id and
                    dusw.DocumentUcStorageWork_endDate is null
                order by
                    dusw.DocumentUcStorageWork_id;
            ";
            $rec = $this->getFirstRowFromQuery($query, array(
                'DocumentUc_id' => $doc_id
            ));
        }
        return $rec;
    }

    /**
     * Создание наряда на выполнение работ
     */
    function createDocumentUcStorageWork($data) {
        $error = array();
        $result = array();
        $str_array = array();

        //проверка наличия невыполненых работ по документу
        $usw_data = $this->getFirstUncompletedStorageWork($data['DocumentUc_id']);
        if (!empty($usw_data['DocumentUcStorageWork_id'])) {
            $error[] = "По документу есть невыполненные работы. Создание нового наряда невозможно.";
        }

        //получение данных о строках документа учета
        $query = "
            select
                dus.DocumentUcStr_id,
                dds.DrugDocumentStatus_Code
            from
                v_DocumentUcStr dus with (nolock)
                left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = dus.DrugDocumentStatus_id
            where
                dus.DocumentUc_id = :DocumentUc_id;

        ";
        $str_array = $this->queryResult($query, array(
            'DocumentUc_id' => $data['DocumentUc_id']
        ));
        if (count($str_array) == 0) {
            $error[] = "Документ не содержит информации о медикаментах. Создание наряда не возможно.";
        }

        //проверка наличия неисполнныех строк в документе учета
        if (count($error) == 0) {
            $str_exists = false;
            foreach($str_array as $str) {
                if ($str['DrugDocumentStatus_Code'] != 4) {
                    $str_exists = true;
                    break;
                }
            }
            if (!$str_exists) {
                $error[] = "Все строки в документе исполнены. Создание наряда не возможно.";
            }
        }

        //старт транзакции
        $this->beginTransaction();

        //формирование строк наряда
        if (count($error) == 0) {
            $save_data = array(
                'DocumentUcTypeWork_id' => $data['DocumentUcTypeWork_id'],
                'Person_cid' => $data['Person_cid'],
                'Post_cid' => $data['Post_cid'],
                'Person_eid' => $data['Person_eid'],
                'Post_eid' => $data['Post_eid'],
                'DocumentUcStorageWork_FactQuantity' => 0
            );
            foreach($str_array as $str) {
                if ($str['DrugDocumentStatus_Code'] != 4) { //4 - Исполнен
                    $save_data['DocumentUcStr_id'] = $str['DocumentUcStr_id'];
                    $response = $this->saveObject('DocumentUcStorageWork', $save_data);
                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                        break;
                    }
                }
            }
        }

        //откат изменений при наличии ошибок
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
            $this->rollbackTransaction();
        } else {
            //коммит транзакции
            $result['success'] = true;
            $this->commitTransaction();
        }

        return $result;
    }

    /**
     * Автоматическое создание наряда на выполнение работ на основе уже усуществующих нарядов
     */
    function autoCreateDocumentUcStorageWork($doc_id) {
        $error = array();
        $result = array();

        //получение параметров текущих работ по документу
        $usw_data = $this->getFirstUncompletedStorageWork($doc_id);

        if (!empty($usw_data['DocumentUcStorageWork_id'])) {
            //получение данных о строках документа учета для которых необходимо создать наряды
            $query = "
                select
                    dus.DocumentUcStr_id
                from
                    v_DocumentUcStr dus with (nolock)
                    left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = dus.DrugDocumentStatus_id
                    left join v_DocumentUcStorageWork dusw with (nolock) on dusw.DocumentUcStr_id = dus.DocumentUcStr_id and dusw.DocumentUcStorageWork_endDate is null
                where
                    dus.DocumentUc_id = :DocumentUc_id and
                    isnull(dds.DrugDocumentStatus_Code, 0) <> 4 and
                    dusw.DocumentUcStorageWork_id is null;
            ";
            $str_array = $this->queryResult($query, array(
                'DocumentUc_id' => $doc_id
            ));

            $save_data = array(
                'DocumentUcTypeWork_id' => $usw_data['DocumentUcTypeWork_id'],
                'Person_cid' => $usw_data['Person_cid'],
                'Post_cid' => $usw_data['Post_cid'],
                'Person_eid' => $usw_data['Person_eid'],
                'Post_eid' => $usw_data['Post_eid'],
                'DocumentUcStorageWork_FactQuantity' => null
            );
            foreach($str_array as $str) {
                $save_data['DocumentUcStr_id'] = $str['DocumentUcStr_id'];
                $response = $this->saveObject('DocumentUcStorageWork', $save_data);
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                    break;
                }
            }
        }

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
        return $result;
    }

	/**
	 * Сохранение наряда на проведение работ
	 */
    function saveDocumentUcStorageWork($data) {
		$fields = array(
			'DocumentUcStorageWork_id',
			'WorkPlace_cid',
			'WorkPlace_eid',
			'Post_cid',
			'Person_cid',
			'Post_eid',
			'Person_eid',
			'DocumentUcTypeWork_id',
			'DocumentUcStr_id',
			'WhsDocumentUcInventDrug_id',
			'DocumentUcStorageWork_FactQuantity',
			'DocumentUcStorageWork_Comment',
			'DocumentUcStorageWork_endDate',
			'pmUser_id'
		);

		$params = array();
		foreach($fields as $field) {
			if (array_key_exists($field, $data)) {
				$params[$field] = !empty($data[$field])?$data[$field]:null;
			}
		}

		$result = $this->saveObject('DocumentUcStorageWork', $params);

		return array($result);
	}

	/**
	 * Удаление наряда на проведение работ
	 */
	function deleteDocumentUcStorageWork($data) {
		$params = array('DocumentUcStorageWork_id' => $data['DocumentUcStorageWork_id']);

		$result = $this->deleteObject('DocumentUcStorageWork', $params);

		return array($result);
	}

    /**
     * Удаление информации о нарядах
     */
    function removeStorageWork($data) {
        $error = array();
        $result = array();

        if (!empty($data['DocumentUcStr_id'])) {
            $query = "
                select
                    DocumentUcStorageWork_id
                from
                    v_DocumentUcStorageWork with (nolock)
                where
                    DocumentUcStr_id = :DocumentUcStr_id;
            ";
            $str_array = $this->queryResult($query, array(
                'DocumentUcStr_id' => $data['DocumentUcStr_id']
            ));
            foreach($str_array as $str) {
                $response = $this->deleteObject('DocumentUcStorageWork', array(
                    'DocumentUcStorageWork_id' => $str['DocumentUcStorageWork_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                    break;
                }
            }
        }

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }
        return $result;
    }

	/**
	*	checkPrepSeries
	*/
	function checkPrepSeries($data)
	{
		$res = array();
		$res['success'] = true;
		$res['checkResult'] = 0;
		$query = "
			select top 1 Drug_id
			from v_DocumentUcStr (nolock)
			where DocumentUcStr_Ser = :DocumentUcStr_Ser
		";
		//echo getDebugSQL($query,$data);die;
		$result = $this->db->query($query,$data);

		if(is_object($result))
		{
			$result = $result->result('array');
			if(is_array($result) && count($result) > 0)
				$res['checkResult'] = 0;
		}

		return $res;
	}

    /**
     * Сохранение информации о количестве ед. списания в упаковке
     */
    function saveGoodsPackCount($data) {
        $result = array();
        $error = array();
        $need_save = true; //флаг отображающий необходимость сохранения конкретной позиции

        if (!empty($data['Drug_id'])) {
            $query = "
                select
                    DrugComplexMnn_id
                from
                    rls.v_Drug
                where
                    Drug_id = :Drug_id
            ";
            $drug_data = $this->getFirstRowFromQuery($query, array(
                'Drug_id' => $data['Drug_id']
            ));
        }

        $query = "
            select
                GoodsUnit_Name
            from
                v_GoodsUnit with (nolock)
            where
                GoodsUnit_id = :GoodsUnit_id;
        ";
        $gu_name = $this->getFirstResultFromQuery($query, array(
            'GoodsUnit_id' => $data['GoodsUnit_id']
        ));

        if ($gu_name == 'упаковка') { //сохранять данные для упаковок не имеет смысла
            $need_save = false;
        }

        if (empty($drug_data['DrugComplexMnn_id']) || empty($data['GoodsUnit_id']) || empty($data['GoodsPackCount_Count'])) {
            $error[] = 'Не указаны параметры';
        }

        if ($need_save && count($error) == 0) {
            //проверка наличия информации по данной позиции в справочнике
            $query = "
                select
                    count(GoodsPackCount_id) as cnt
                from
                    v_GoodsPackCount with (nolock)
                where
                    DrugComplexMnn_id = :DrugComplexMnn_id and
                    GoodsUnit_id = :GoodsUnit_id
            ";
            $cnt = $this->getFirstResultFromQuery($query, array(
                'DrugComplexMnn_id' => $drug_data['DrugComplexMnn_id'],
                'GoodsUnit_id' => $data['GoodsUnit_id']
            ));
            if ($cnt > 0) {
                $need_save = false;
            }
        }

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        } else if ($need_save) {
            $result = $this->saveObject('GoodsPackCount', array(
                'DrugComplexMnn_id' => $drug_data['DrugComplexMnn_id'],
                'GoodsPackCount_Count' => $data['GoodsPackCount_Count'],
                'GoodsUnit_id' => $data['GoodsUnit_id']
            ));
        }

        return $result;
    }

    /**
     * Сохранение изменений в списке штрих-кодов для строки документа учета
     */
    function saveBarCodeChangedData($data) {
        $error = array();
        $result = array();

        foreach($data['changed_data'] as $record) {
            switch($record->state) {
                case 'add':
                case 'edit':
                    $save_data = array(
                        'DrugPackageBarCode_id' => $record->state == 'edit' ? $record->DrugPackageBarCode_id : null,
                        'DocumentUcStr_id' => $data['DocumentUcStr_id'],
                        'DrugPackageBarCode_BarCode' => !empty($record->DrugPackageBarCode_BarCode) ? $record->DrugPackageBarCode_BarCode : null,
                        'DrugPackageBarCodeType_id' => !empty($record->DrugPackageBarCodeType_id) ? $record->DrugPackageBarCodeType_id : null,
                        'DrugPackageBarCode_GTIN' => !empty($record->DrugPackageBarCode_GTIN) ? $record->DrugPackageBarCode_GTIN : null,
                        'DrugPackageBarCode_SeriesNum' => !empty($record->DrugPackageBarCode_SeriesNum) ? $record->DrugPackageBarCode_SeriesNum : null,
                        'DrugPackageBarCode_expDT' => !empty($record->DrugPackageBarCode_expDT) ? $this->formatDate($record->DrugPackageBarCode_expDT) : null,
                        'DrugPackageBarCode_TNVED' => !empty($record->DrugPackageBarCode_TNVED) ? $record->DrugPackageBarCode_TNVED : null,
                        'DrugPackageBarCode_FactNum' => !empty($record->DrugPackageBarCode_FactNum) ? $record->DrugPackageBarCode_FactNum : null
                    );
                    $resp = $this->saveObject('DrugPackageBarCode', $save_data);
                    break;
                case 'delete':
                    $resp = $this->deleteObject('DrugPackageBarCode', array(
                        'DrugPackageBarCode_id' => $record->DrugPackageBarCode_id
                    ));
                    break;
            }
            if (!empty($resp['Error_Msg'])) {
                $error[] = $resp['Error_Msg'];
            }
        }

        if (count($error) > 0 ) {
            $result['Error_Msg'] = $error[0];
        } else {
            $result['success'] = true;
        }
        return $result;
    }

    /**
     * Получение идентификатора документа разукомплектации (постановка на учет) по идентификатору документа разукомплектации (списание). Если такого документа нет, то он создается.
     */
    function getDocRazPostId($spis_doc_id, $create_if_not_exists = false) {
        $post_doc_id = null;

        //получиение идентификатора типа "Разукомплектация: постановка на учет"
        $query = "
            select
                ddt.DrugDocumentType_id
            from
                v_DrugDocumentType ddt with (nolock)
            where
                ddt.DrugDocumentType_SysNick = 'DocRazPost' -- Разукомплектация: постановка на учет
        ";
        $type_id = $this->getFirstResultFromQuery($query);

        $query = "
            select top 1
                du.DocumentUc_id
            from
                v_DocumentUc du with (nolock)
            where
                du.DocumentUc_pid = :DocumentUc_pid and
                du.DrugDocumentType_id = :DrugDocumentType_id
            order by
                du.DocumentUc_id;
        ";
        $post_doc_id = $this->getFirstResultFromQuery($query, array(
            'DocumentUc_pid' => $spis_doc_id,
            'DrugDocumentType_id' => $type_id
        ));

        if (empty($post_doc_id) && $create_if_not_exists && !empty($type_id)) { //если документа еще нет, то создаем его
            //шапка документа - полная копия расходного документа учета
            $response = $this->copyObject('DocumentUc', array(
                'DocumentUc_id' => $spis_doc_id,
                'DrugDocumentType_id' => $type_id
            ));
        }

        return $post_doc_id > 0 ? $post_doc_id : null;
    }

    /**
     * Получение идентификатора строки документа разукомплектации (постановка на учет) по идентификаторам документа разукомплектации (постановка на учет) и строки документа разукомплектации (списание)
     */
    function getDocRazPostStrId($post_doc_id, $spis_str_id) {
        $post_str_id = null;

        $query = "
            select top 1
                dus.DocumentUcStr_id
            from
                v_DocumentUcStr dus with (nolock)
            where
                dus.DocumentUcStr_sid = :DocumentUcStr_sid and
                (
                    :DocumentUc_id is null or
                    dus.DocumentUc_id = :DocumentUc_id
                )
            order by
                dus.DocumentUcStr_id;
        ";
        $post_str_id = $this->getFirstResultFromQuery($query, array(
            'DocumentUc_id' => $post_doc_id,
            'DocumentUcStr_sid' => $spis_str_id
        ));

        return $post_str_id > 0 ? $post_str_id : null;
    }


    /**
     * Получение идентификатора ед. изм. по умолчанию
     */
    function getDefaultGoodsUnitId() {
        $query = "
            select top 1
                GoodsUnit_id
            from
                v_GoodsUnit with (nolock)
            where
                GoodsUnit_Name = 'упаковка'
            order by
                GoodsUnit_id;
        ";
        $goods_unit_id = $this->getFirstResultFromQuery($query);
        return !empty($goods_unit_id) ? $goods_unit_id : null;
    }

    /**
     * Копирование файлов из партий в сторки документа учета.
     */
    function copyFilesFromOid($document_id) {
        $this->load->model('PMMediaData_model', 'PMMediaData_model');

        $error = array();
        $result = array();

        $query = "
            select
                DocumentUcStr_id,
                DocumentUcStr_oid
            from
                v_DocumentUcStr with (nolock)
            where
                DocumentUc_id = :DocumentUc_id and
                DocumentUcStr_oid is not null;
        ";
        $str_arr = $this->queryResult($query, array(
            'DocumentUc_id' => $document_id
        ));

        if (is_array($str_arr)) {
            foreach($str_arr as $str_data) {
                $query = "
                    select
                        pmd.pmMediaData_id
                    from
                        v_pmMediaData pmd with (nolock)
                    where
                        pmd.pmMediaData_ObjectName = 'DocumentUcStr' and
                        pmd.pmMediaData_ObjectID = :pmMediaData_ObjectID;
                ";
                $file_id_arr = $this->queryList($query, array(
                    'pmMediaData_ObjectID' => $str_data['DocumentUcStr_oid']
                ));
                $response = $this->PMMediaData_model->copypmMediaData($file_id_arr, array(
                    'pmMediaData_ObjectID' => $str_data['DocumentUcStr_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                    break;
                }
            }
        }

        if (count($error) > 0 ) {
            $result['Error_Msg'] = $error[0];
        } else {
            $result['success'] = true;
        }
        return $result;
    }
}
?>
