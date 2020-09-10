<?php
/**
* Модель - Электронный паспорт здоровья
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Andrew Markoff 
* @version      10.09.2009
*/
class EPH_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Функция чтения списка документов ЭМК пациента, для которых может быть загружена форма просмотра
	 * На выходе: JSON-строка
	 * Используется: 
	function loadEmkDoc($data) {
		if(empty($data['Evn_rid']) && empty($data['EvnXml_id']))
		{
			return array();
		}
		switch ($data['filterDoc']) {
			case 'emk':
				// Все документы в рамках ЭМК пациента, для которых может быть загружена форма просмотра
				$filters = '(1 = 1)';	
				break;
			default:
				// evn Все документы в рамках случая
				$filters = 'doc.Evn_rid = @Evn_rid';	
				break;
		}
		$query = "
		declare
			@Person_id bigint = :Person_id,
			@Evn_rid bigint = :Evn_rid,
			@EvnXml_id bigint = :EvnXml_id;

		if ( ISNULL(@Person_id,0) = 0 and @Evn_rid is not null )
		begin
			select @Person_id = Evn.Person_id from v_Evn Evn with (nolock) where Evn.Evn_id = @Evn_rid
		end
			
		if ( ( ISNULL(@Person_id,0) = 0 or ISNULL(@Evn_rid,0) = 0 ) and @EvnXml_id is not null )
		begin
			select @Person_id = Evn.Person_id, @Evn_rid = Evn.Evn_rid from v_EvnXml doc with (nolock)
			inner join v_Evn Evn with (nolock) on Evn.Evn_id = doc.Evn_id
			where doc.EvnXml_id = @EvnXml_id
		end

		select 
			doc.objectCode + '_' + CAST(isnull(doc.objectId,0) as varchar) as item_key,
			doc.objectCode,
			doc.objectKey,
			doc.objectId,
			doc.parentObjectCode,
			doc.parentKey,
			doc.parentId,
			CONVERT(varchar,doc.objectDate,104) as objectDate,
			doc.objectType,
			doc.objectName,
			doc.Evn_rid, --для фильтра по событию
			doc.Lpu_id, --для фильтра по ГУЗам
			doc.Diag_id	--для фильтра по ГУЗам
		from (
			select
				'SignalInformationAll' as objectCode
				,'Person_id' as objectKey
				,@Person_id as objectId
				,'Person' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,null as objectDate
				,'Сигнальная информация' as objectType
				,'Сигнальная информация' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			union all
			select top 1
				'PersonMedHistory' as objectCode
				,'Person_id' as objectKey
				,doc.Person_id as objectId
				,'SignalInformationAll' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,doc.PersonMedHistory_setDT as objectDate
				,'Сигнальная информация' as objectType
				,'Анамнез жизни' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			from v_PersonMedHistory doc with (nolock)
			where doc.Person_id = @Person_id
			union all
			select top 1
				'BloodData' as objectCode
				,'BloodData_id' as objectKey
				,@Person_id as objectId
				,'SignalInformationAll' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,doc.PersonBloodGroup_setDT as objectDate
				,'Сигнальная информация' as objectType
				,'Группа крови и Rh-фактор' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			from v_PersonBloodGroup doc with (nolock)
			where doc.Person_id = @Person_id
			union all
			select
				'AllergHistory' as objectCode
				,'PersonAllergicReaction_id' as objectKey
				,@Person_id as objectId
				,'SignalInformationAll' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,doc.PersonAllergicReaction_setDT as objectDate
				,'Сигнальная информация' as objectType
				,'Аллергологический анамнез' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			from v_PersonAllergicReaction doc with (nolock)
			where doc.Person_id = @Person_id
			union all
			select
				'ExpertHistory' as objectCode
				,'ExpertHistory_id' as objectKey
				,@Person_id as objectId
				,'SignalInformationAll' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,null as objectDate
				,'Сигнальная информация' as objectType
				,'Экспертный анамнез и льготы' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			union all
			select
				'PersonDispInfo' as objectCode
				,'PersonDispInfo_id' as objectKey
				,@Person_id as objectId
				,'SignalInformationAll' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,null as objectDate
				,'Сигнальная информация' as objectType
				,'Диспансерный учет' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			union all
			select
				'DiagList' as objectCode
				,'Diag_id' as objectKey
				,@Person_id as objectId
				,'SignalInformationAll' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,null as objectDate
				,'Сигнальная информация' as objectType
				,'Список уточненных диагнозов' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			union all
			select
				'SurgicalList' as objectCode
				,'EvnUslugaOper_id' as objectKey
				,@Person_id as objectId
				,'SignalInformationAll' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,null as objectDate
				,'Сигнальная информация' as objectType
				,'Список оперативных вмешательств' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			union all
			select
				'Anthropometry' as objectCode
				,'Person_id' as objectKey
				,@Person_id as objectId
				,'SignalInformationAll' as parentObjectCode
				,'Person_id' as parentKey
				,@Person_id as parentId
				,null as objectDate
				,'Сигнальная информация' as objectType
				,'Антропометрические данные' as objectName
				,null as Evn_rid
				,null as Lpu_id
				,null as Diag_id
			union all
			select
				'MorbusOnkoVizitPLDop' as objectCode
				,'MorbusOnkoVizitPLDop_id' as objectKey
				,doc.MorbusOnkoVizitPLDop_id as objectId
				,EvnClass.EvnClass_SysNick as parentObjectCode
				,'EvnVizitPL_id' as parentKey
				,doc.EvnVizit_id as parentId
				,doc.MorbusOnkoVizitPLDop_setDT as objectDate
				,Evn.EvnClass_Name as objectType
				,'Талон дополнений больного ЗНО' as objectName
				,Evn.EvnVizitPL_rid as Evn_rid
				,Evn.Lpu_id
				,doc.Diag_id
			from v_MorbusOnkoVizitPLDop doc with (nolock)
			inner join v_EvnVizitPL Evn with (nolock) on Evn.EvnVizitPL_id = doc.EvnVizit_id
			inner join EvnClass with (nolock) on EvnClass.EvnClass_id = Evn.EvnClass_id
			where Evn.Person_id = @Person_id
			union all
			select
				'MorbusOnkoLeave' as objectCode
				,'MorbusOnkoLeave_id' as objectKey
				,doc.MorbusOnkoLeave_id as objectId
				,'EvnSection' as parentObjectCode
				,'EvnSection_id' as parentKey
				,doc.EvnSection_id as parentId
				,doc.MorbusOnkoLeave_insDT as objectDate
				,Evn.EvnClass_Name as objectType
				,'Выписка из медицинской карты стационарного больного злокачественным новообразованием' as objectName
				,Evn.EvnSection_rid as Evn_rid
				,Evn.Lpu_id
				,doc.Diag_id
			from v_MorbusOnkoLeave doc with (nolock)
			inner join v_EvnSection Evn with (nolock) on Evn.EvnSection_id = doc.EvnSection_id
			where Evn.Person_id = @Person_id
			union all
			select
				EvnClass.EvnClass_SysNick as objectCode
				,EvnClass.EvnClass_SysNick +'_id' as objectKey
				,doc.EvnPL_id as objectId
				,'Person' as parentObjectCode
				,'Person_id' as parentKey
				,doc.Person_id as parentId
				,doc.EvnPL_setDT as objectDate
				,doc.EvnClass_Name as objectType
				,'Случай амбулаторно-поликлинического лечения № '+ doc.EvnPL_NumCard as objectName
				,doc.EvnPL_rid as Evn_rid
				,doc.Lpu_id
				,doc.Diag_id
			from v_EvnPL doc with (nolock)
			inner join EvnClass with (nolock) on EvnClass.EvnClass_id = doc.EvnClass_id
			where doc.Person_id = @Person_id
			union all
			select
				EvnClass.EvnClass_SysNick as objectCode
				,EvnClass.EvnClass_SysNick +'_id' as objectKey
				,doc.EvnPS_id as objectId
				,'Person' as parentObjectCode
				,'Person_id' as parentKey
				,doc.Person_id as parentId
				,doc.EvnPS_setDT as objectDate
				,doc.EvnClass_Name as objectType
				,'Случай стационарного лечения № '+ doc.EvnPS_NumCard as objectName
				,doc.EvnPS_rid as Evn_rid
				,doc.Lpu_id
				,doc.Diag_id
			from v_EvnPS doc with (nolock)
			inner join EvnClass with (nolock) on EvnClass.EvnClass_id = doc.EvnClass_id
			where doc.Person_id = @Person_id
			union all
			select
				EvnClass.EvnClass_SysNick as objectCode
				,EvnClass.EvnClass_SysNick +'_id' as objectKey
				,doc.EvnSection_id as objectId
				,EvnClassRoot.EvnClass_SysNick as parentObjectCode
				,EvnClassRoot.EvnClass_SysNick +'_id' as parentKey
				,Evn.Evn_id as parentId
				,doc.EvnSection_setDT as objectDate
				,EvnClassRoot.EvnClass_Name as objectType
				,EvnClass.EvnClass_Name as objectName
				,doc.EvnSection_rid as Evn_rid
				,doc.Lpu_id
				,doc.Diag_id
			from v_EvnSection doc with (nolock)
			inner join EvnClass with (nolock) on EvnClass.EvnClass_id = doc.EvnClass_id
			inner join v_Evn Evn with (nolock) on Evn.Evn_id = doc.EvnSection_rid
			inner join EvnClass EvnClassRoot with (nolock) on EvnClassRoot.EvnClass_id = Evn.EvnClass_id
			where doc.Person_id = @Person_id
			union all
			select
				EvnClass.EvnClass_SysNick as objectCode
				,EvnClass.EvnClass_SysNick +'_id' as objectKey
				,doc.EvnVizitPL_id as objectId
				,EvnClassRoot.EvnClass_SysNick as parentObjectCode
				,EvnClassRoot.EvnClass_SysNick +'_id' as parentKey
				,Evn.Evn_id as parentId
				,doc.EvnVizitPL_setDT as objectDate
				,EvnClassRoot.EvnClass_Name as objectType
				,EvnClass.EvnClass_Name as objectName
				,doc.EvnVizitPL_rid as Evn_rid
				,doc.Lpu_id
				,doc.Diag_id
			from v_EvnVizitPL doc with (nolock)
			inner join EvnClass with (nolock) on EvnClass.EvnClass_id = doc.EvnClass_id
			inner join v_Evn Evn with (nolock) on Evn.Evn_id = doc.EvnVizitPL_rid
			inner join EvnClass EvnClassRoot with (nolock) on EvnClassRoot.EvnClass_id = Evn.EvnClass_id
			where doc.Person_id = @Person_id
			union all
			select
				'EvnReceptView' as objectCode
				,EvnClass.EvnClass_SysNick +'_id' as objectKey
				,doc.EvnRecept_id as objectId
				,EvnClassRoot.EvnClass_SysNick as parentObjectCode
				,EvnClassRoot.EvnClass_SysNick +'_id' as parentKey
				,Evn.Evn_id as parentId
				,doc.EvnRecept_setDT as objectDate
				,EvnClassRoot.EvnClass_Name as objectType
				,EvnClass.EvnClass_Name + ' ' + doc.EvnRecept_Ser + ' ' + doc.EvnRecept_Num as objectName
				,doc.EvnRecept_rid as Evn_rid
				,doc.Lpu_id
				,doc.Diag_id
			from v_EvnRecept doc with (nolock)
			inner join EvnClass with (nolock) on EvnClass.EvnClass_id = doc.EvnClass_id
			left join v_Evn Evn with (nolock) on Evn.Evn_id = doc.EvnRecept_pid
			left join EvnClass EvnClassRoot with (nolock) on EvnClassRoot.EvnClass_id = Evn.EvnClass_id
			where doc.Person_id = @Person_id
			union all
			select
				EvnClass.EvnClass_SysNick as objectCode
				,EvnClass.EvnClass_SysNick +'_id' as objectKey
				,doc.EvnUslugaPar_id as objectId
				,EvnClassRoot.EvnClass_SysNick as parentObjectCode
				,EvnClassRoot.EvnClass_SysNick +'_id' as parentKey
				,Evn.Evn_id as parentId
				,doc.EvnUslugaPar_setDT as objectDate
				,isnull(EvnClassRoot.EvnClass_Name,EvnClass.EvnClass_Name) as objectType
				,isnull(UC.UslugaComplex_Name,EvnClass.EvnClass_Name) as objectName
				,doc.EvnUslugaPar_rid as Evn_rid
				,doc.Lpu_id
				,null as Diag_id
			from v_EvnUslugaPar doc with (nolock)
			inner join EvnClass with (nolock) on EvnClass.EvnClass_id = doc.EvnClass_id
			left join v_Evn Evn with (nolock) on Evn.Evn_id = doc.EvnUslugaPar_pid
			left join EvnClass EvnClassRoot with (nolock) on EvnClassRoot.EvnClass_id = Evn.EvnClass_id
			left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = doc.UslugaComplex_id
			where doc.Person_id = @Person_id and doc.EvnUslugaPar_setDT is not null
			union all
			select
				'FreeDocumentView' as objectCode
				,'FreeDocument_id' as objectKey
				,doc.EvnXml_id as objectId
				,EvnClassRoot.EvnClass_SysNick as parentObjectCode
				,EvnClassRoot.EvnClass_SysNick +'_id' as parentKey
				,isnull(PL.EvnVizitPL_id,ES.EvnSection_id) as parentId
				,doc.EvnXml_updDT as objectDate
				,'Документ в свободной форме' as objectType
				,doc.EvnXml_Name as objectName
				,isnull(PL.EvnVizitPL_rid,ES.EvnSection_rid) as Evn_rid
				,isnull(PL.Lpu_id,ES.Lpu_id) as Lpu_id
				,isnull(PL.Diag_id,ES.Diag_id) as Diag_id
			from v_EvnXml doc with (nolock)
			left join v_EvnVizitPL PL with (nolock) on PL.EvnVizitPL_id = doc.Evn_id
			left join v_EvnSection ES with (nolock) on ES.EvnSection_id = doc.Evn_id
			inner join EvnClass EvnClassRoot with (nolock) on EvnClassRoot.EvnClass_id = isnull(PL.EvnClass_id,ES.EvnClass_id)
			where doc.XmlType_id = 2 and isnull(PL.Person_id,ES.Person_id) = @Person_id
		) doc
		where
			{$filters}
		order by
			doc.objectDate desc
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		} else {
			return false;
		}
	}
	 */

	/**
	* Методы для ЭПЗ ($data);
	* 
	*/
	
	/**
	 * Загрузка списка диагнозов, документы с которыми должны быть недоступны для просмотра врачом согласно его должности
	 * @return array
	 * @throws Exception
	 */
	function loadListNotViewDiag($data)
	{
		$toReturn = array();

		// сначала получаем ProfileDiagGroup_id, соответствующие должности врача
		$sql = '
			select
				PDPM.ProfileDiagGroup_id
			from
				v_ProfileDiagFrmpPost PDPM with (nolock)
				inner join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :MedStaffFact_id AND PDPM.FrmpPost_id = MSF.Post_id
		';
		$res = $this->db->query($sql, $data);
		if ( !is_object($res) )
		{
			throw new Exception('Ошибка запроса групп диагнозов, доступных для просмотра врачом согласно его должности', 500);
		}
		$response_arr = $res->result('array');

		if(count($response_arr) > 0)
		{
			$ProfileDiagGroup_arr = array();
			foreach($response_arr as $row)
			{
				$ProfileDiagGroup_arr[] = $row['ProfileDiagGroup_id'];
			}
		}

		/*
		терапевт не должен видеть диагнозы с ProfileDiagGroup_id 1-5 установленных в чужих ЛПУ
		пульмонолог не должен видеть диагнозы с ProfileDiagGroup_id 1-4 установленных в чужих ЛПУ (т.е. с ProfileDiagGroup_id != 5)
		Врач-психиатр-нарколог не должен видеть диагнозы с ProfileDiagGroup_id 3-5 установленных в чужих ЛПУ (т.е. с ProfileDiagGroup_id not in (1,2))
		
		будут получены те диагнозы (пациента), к которым врач данной должности не имеет доступа
		*/

		// Проверяем наличие данных в кэше
		$this->load->library('swCache', array('use' => 'memcache'));
		$cacheObject = 'cacheProfileDiag';
		if ($response = $this->swcache->get($cacheObject)) {
			// Прочитали из кэша
		} else {
			$sql = "
				Select
					D.Diag_id,
					PD.ProfileDiagGroup_id
				from
					v_ProfileDiag PD with (nolock)
					inner join v_Diag D with (nolock) on PD.Diag_id = case when D.DiagLevel_id = 3 then D.Diag_pid else D.Diag_id end
			";
			$res = $this->db->query($sql, $data);
			if (!is_object($res)) {
				throw new Exception('Ошибка запроса списка диагнозов, недоступных для просмотра врачом согласно его должности', 500);
			}
			$response = $res->result('array');

			// на час кэшируем данные
			$this->swcache->set($cacheObject, $response, array('ttl' => 3600)); // кэшируем на час
		}

		if (!empty($response) && is_array($response)) {
			foreach ($response as $row) {
				if (empty($ProfileDiagGroup_arr) || !in_array($row['ProfileDiagGroup_id'], $ProfileDiagGroup_arr)) { // фильтрация
					$toReturn[] = $row['Diag_id'];
				}
			}
		}

		return $toReturn;
	}
	
	/**
	 * Загрузка списка ЛПУ с особым статусом
	 * @return array
	 * @throws Exception
	 */
	function loadListVipLpu()
	{
		$sql = "
			select Lpu_id from v_Lpu with (nolock) where Lpu_IsSecret = 2
		";
		//echo getDebugSQL($sql);

		$res = $this->db->query($sql);
		if ( !is_object($res) )
		{
			throw new Exception('Ошибка запроса списка ЛПУ с особым статусом', 500);
		}
		return $res->result('array');
	}

	/**
	 * Возваращает список нод
	 */
	function GetDeathSvidNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and DeathSvid.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
	
		$sql = "
		select 
			DeathSvid_id, 
			'Свидетельство о смерти №' as EvnClass_Name,
			rtrim(DeathSvid_Ser) as DeathSvid_Ser,
			rtrim(DeathSvid_Num) as DeathSvid_Num,
			rtrim(DeathCause_Name) as DeathCause_Name,
			RTrim(IsNull(convert(varchar,cast(DeathSvid_DeathDate as datetime),104),'')) as DeathSvid_DeathDate,
			rtrim(MP.Person_FIO) as MedPersonal_FIO
		from DeathSvid with (nolock)
		left join DeathCause  with (nolock) on DeathSvid.DeathCause_id = DeathCause.DeathCause_id
		outer apply (
			select top 1
				Person_FIO
			from
				v_MedPersonal with (nolock)
			where
				v_MedPersonal.MedPersonal_id = DeathSvid.MedPersonal_id
		) as MP
		where {$filter}
		--order by DeathSvid_DeathDate";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
			return false;
	}
	
	/**
	* Возвращает стационарные случаи лечения в дерево ЭМК
	* для возвращения только законченных случаев нужно раскомментировать соответствующую строку запроса
	*/
	function GetEvnPSNodeList($data)
	{
		$params = array(
			'Lpu_id' => empty($data['Lpu_id'])?$data['session']['lpu_id']:$data['Lpu_id'] ,
			'Person_id' => $data['Person_id']
		);
		$addQuery = '';
		$filter = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EPS.EvnPS_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EPS.EvnPS_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EPS.EvnPS_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}
		
		$withSurgery_select = '';
		$withSurgery_from = '';
		
		if (!empty($data['Diag_id']))
		{
			$filter .= ' and EPS.Diag_id = :Diag_id';
			$params['Diag_id'] = $data['Diag_id'];
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and cast(EPS.EvnPS_setDT as date) >= cast(:Beg_EvnDate as date)";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and cast(EPS.EvnPS_setDT as date) <= cast(:End_EvnDate as date)";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}

		$accessType = 'EPS.Lpu_id = :Lpu_id';
		//врач может удалить, если КВС создано в его ЛПУ, КВС добавлена им
		$deleteAccess = 'ISNULL(EPS.EvnDirection_id,0) = 0 and EPS.Lpu_id = :Lpu_id and EPS.pmUser_insID = :pmUser_id';
		$params['pmUser_id'] = $data['pmUser_id'];

		if ( $data['session']['region']['nick'] != 'ufa' ) 
		{
			//Везде кроме Уфы закрыта возможность редактировать закрытый случай АПЛ #5033
			$accessType .= ' AND EPS.EvnPS_disDT is null';
		}
		
		if (isset($data['ARMType'])&&($data['ARMType']=='headBrigMobile')) {
			$withSurgery_select = ', EU_Surg_Count.withSurgery';
			$withSurgery_from = "outer apply (
					select CASE WHEN COUNT(EU.EvnUsluga_id) = 0 THEN 1 ELSE 2 END AS withSurgery
					from v_EvnUsluga EU with (nolock)
					where 
						EPS.EvnPS_id IN (EU.EvnUsluga_pid,EU.EvnUsluga_rid) AND 
						EU.EvnClass_SysNick = 'EvnUslugaOper'
					) as EU_Surg_Count";
		}

		if ( isset($data['user_LpuUnitType_SysNick']) && in_array($data['user_LpuUnitType_SysNick'],array('polka','stac','dstac','hstac','pstac','parka')) )
		{
			//врач может редактировать, если он работает в стационаре
			$accessType .= " and :user_LpuUnitType_SysNick in ('stac','dstac','hstac','pstac')";
			//врач может удалить, если он работает в стационаре
			$deleteAccess .= " and :user_LpuUnitType_SysNick in ('stac','dstac','hstac','pstac')";
			$params['user_LpuUnitType_SysNick'] = $data['user_LpuUnitType_SysNick'];
		}
		//$deleteAccess = '(1 = 1)';

		if (!empty($data['filter'])) {
			$filter_arr = json_decode($data['filter'], true);
			if (!empty($filter_arr['EvnPS'])) {
				$filter .= " and EPS.EvnPS_id = :EvnPS_id";
				$params['EvnPS_id'] = $filter_arr['EvnPS'];
			}
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;

		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EPS.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";
		
		$params['CurMedPersonal_id'] = (isset($_SESSION['medpersonal_id']))?$_SESSION['medpersonal_id']:0;
		$params['CurLpuSection_id'] = (isset($_SESSION['CurLpuSection_id']))?$_SESSION['CurLpuSection_id']:0;

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				case when EPS.EvnPS_disDT is null then 1 else 2 end as EvnPS_IsFinish,
				EPS.EvnPS_id,
				EPS.pmUser_insID,
				EPS.pmUser_updID,
				case when ESMP.EvnSection_id is not null then 1 else 0 end as IsThis_MedPersonal,
				isnull(D.Diag_Code,'') as Diag_Code, -- основной диагноз последнего движения или приемного отделения
				isnull(D.Diag_Name,'') as Diag_Name,
				D.Diag_id,
				EPS.Lpu_id,
				ISNULL(Lpu.Lpu_Nick,'') as Lpu_Name,
				EPS.EvnPS_NumCard as EvnPS_NumCard,
				case when {$deleteAccess} then 'enabled' else 'disabled' end as 'deleteAccess',
				EPS.PrehospType_id,
				case when LT.LeaveType_Name is not null then LT.LeaveType_Name when EPS.PrehospWaifRefuseCause_id is not null then 'Отказ' else 'Находится на госпитализации' end as hosp_state,
				ISNULL(convert(varchar,EPS.EvnPS_setDT,104),'') as date_beg,
				ISNULL(convert(varchar,EPS.EvnPS_disDT,104),'') as date_end,
				convert(varchar,EvnPS_setDT,104)+' '+ EvnPS_setTime as sortDate,
				case when exists(
					select *
					from v_EvnSection ES with(nolock)
					inner join v_DiagFinance DF with(nolock) on DF.Diag_id = ES.Diag_id
					where ES.EvnSection_pid = EPS.EvnPS_id
					and DF.DiagFinance_IsRankin = 2
				) then 2 else 1 end as DiagFinance_IsRankin
				{$withSurgery_select}
				{$addQuery}
			from
				v_EvnPS EPS with (nolock)
				{$withSurgery_from}
				left join v_Lpu Lpu with (nolock) on EPS.Lpu_id = Lpu.Lpu_id
				left join v_EvnSection ESLAST with (nolock) on EPS.EvnPS_id = ESLAST.EvnSection_pid and ESLAST.EvnSection_Index = (ESLAST.EvnSection_Count - 1)
				left join v_Diag D with (nolock) on isnull(ESLAST.Diag_id,EPS.Diag_pid) = D.Diag_id
				left join v_LeaveType LT with (nolock) on EPS.LeaveType_id = LT.LeaveType_id
				outer apply (
					select top 1 EvnSection_id from v_EvnSection with (nolock)
					where EvnSection_pid = EPS.EvnPS_id
					and MedPersonal_id = :CurMedPersonal_id".(($params['CurLpuSection_id']>0)?" and LpuSection_id = :CurLpuSection_id":"")."
				) ESMP
				--left join v_PrehospWaifRefuseCause PWRC with (nolock) on EPS.PrehospWaifRefuseCause_id = PWRC.PrehospWaifRefuseCause_id
			where 
				EPS.Person_id = :Person_id
				{$filter}
			--order by EPS.EvnPS_setDT
		";
		//echo getDebugSql($sql, $params); exit;
		//sql_log_message('error', 'GetEvnPSNodeList: ', getDebugSql($sql, $params));

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return $res->result('array');
			//return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	* Возвращает данные для ноды движения по отделениям
	*/
	function GetEvnSectionNodeList($data)
	{
		$params = array(
			'EvnPS_id' => $data['EvnPS_id']
		);
		$filter = '';
		if (!empty($data['Diag_id']))
		{
			$filter .= ' and EvnSection.Diag_id = :Diag_id';
			$params['Diag_id'] = $data['Diag_id'];
		}
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and cast(EvnSection.EvnSection_setDT as date) >= cast(:Beg_EvnDate as date)";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and cast(EvnSection.EvnSection_setDT as date) <= cast(:End_EvnDate as date)";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		
		if($needAccessFilter){
			$lpuFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnSection.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		if ( !empty($_SESSION['medpersonal_id']) || !empty($_SESSION['CurLpuSection_id']) ) {
			$IsThis_MedPersonal = "case when EvnSection.MedPersonal_id = " . (!empty($_SESSION['medpersonal_id']) ? $_SESSION['medpersonal_id'] : 0) . " and EvnSection.LpuSection_id = " . (!empty($_SESSION['CurLpuSection_id']) ? $_SESSION['CurLpuSection_id'] : 0) . " then 1 else 0 end as IsThis_MedPersonal,";
		}
		else {
			$IsThis_MedPersonal = "0 as IsThis_MedPersonal,";
		}

		// Оптимизация
		// @task https://redmine.swan.perm.ru/issues/122043

		// 1. Сперва тянем идешники движений
		$response = $this->queryResult("
			select
				EvnSection.EvnSection_id,
				EvnDirection.EvnDirection_id,
				MOL.MorbusOnkoLeave_id,
				EUP.EvnUslugaPar_id
			from 
				v_EvnSection EvnSection with (nolock)
				left join v_LpuSection LS with(nolock) on EvnSection.LpuSection_id = LS.LpuSection_id
				left join v_Diag Diag with (nolock) on EvnSection.Diag_id = Diag.Diag_id
				outer apply (
					select top 1 EvnDirection_id
					from v_EvnDirection_all with (nolock)
					where EvnDirection_pid = EvnSection.EvnSection_id
				) EvnDirection
				outer apply (
					select top 1 MorbusOnkoLeave_id
					from v_MorbusOnkoLeave with (nolock)
					where EvnSection_id = EvnSection.EvnSection_id
				) MOL
				outer apply (
					select top 1 EvnUslugaPar_id
					from v_EvnUslugaPar with (nolock)
					where EvnUslugaPar_pid = EvnSection.EvnSection_id
				) EUP
			where 
				EvnSection.EvnSection_pid = :EvnPS_id
				{$filter}
		", $params);

		if ( $response === false || !is_array($response) || count($response) == 0 ) {
			return false;
		}

		$evnSectionChildrenCountList = array();
		$evnSectionList = array();
		$evnSectionWithDirectionList = array();

		foreach ( $response as $row ) {
			$evnSectionList[] = $row['EvnSection_id'];
			$evnSectionChildrenCountList[$row['EvnSection_id']] = 0;

			if ( !empty($row['MorbusOnkoLeave_id']) || !empty($row['EvnUslugaPar_id']) ) {
				$evnSectionChildrenCountList[$row['EvnSection_id']]++;
			}
			// Если количество дочерних событий уже больше 0, то не включаем движение в уточняющий запрос по направлениям
			else if ( !empty($row['EvnDirection_id']) && !in_array($row['EvnSection_id'], $evnSectionWithDirectionList) ) {
				$evnSectionWithDirectionList[] = $row['EvnSection_id'];
			} else if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$resp = $this->lis->GET('EvnDirection/Count', [
					'EvnDirection_pid' => $row['EvnSection_id'],
					'status' => 'active'
				], 'single');
				if (!$this->isSuccessful($resp)) {
					return false;
				}
				$evnSectionChildrenCountList[$row['EvnSection_id']] += $resp['Count'];
			}
		}

		// 2. Тянем данные по направлениям, если они есть
		if ( count($evnSectionWithDirectionList) > 0 ) {
			$response = $this->queryResult("
				select
					ED.EvnDirection_id,
					ED.EvnDirection_pid as EvnSection_id
				from 
					v_EvnDirection_all ED with (nolock)
					left join v_EvnQueue EQ with(nolock) on EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_recDT is null
				where
					ED.EvnDirection_pid in (" . implode(",", $evnSectionWithDirectionList) . ") 
					and ED.EvnDirection_failDT is null
					and ED.DirFailType_id is null
					and isnull(ED.EvnStatus_id, 0) not in (12,13)
					and EQ.EvnQueue_failDT is null
			", $params);

			if ( $response !== false && is_array($response) && count($response) > 0 ) {
				foreach ( $response as $row ) {
					$evnSectionChildrenCountList[$row['EvnSection_id']]++;
				}
			}
		}

		// 3. Основной запрос
		$response = $this->queryResult("
			select
				EvnSection.EvnSection_id,
				EvnSection.Diag_id,
				EvnSection.pmUser_insID,
				EvnSection.pmUser_updID,
				EvnSection.MedPersonal_id,
				{$IsThis_MedPersonal}
				ISNULL(Diag.Diag_Code,'') as  Diag_Code,
				ISNULL(Diag.Diag_Name,'') as  Diag_Name,
				EvnSection.Lpu_id,
				ISNULL(Lpu.Lpu_Nick,'') as Lpu_Name,
				ISNULL(LpuSection.LpuSection_Name,'') as LpuSection_Name,
				ISNULL(convert(varchar,EvnSection.EvnSection_setDT,104),'') as date_beg,
				ISNULL(convert(varchar,EvnSection.EvnSection_disDT,104),'') as date_end,
				convert(varchar,EvnSection.EvnSection_setDT,104)+' '+EvnSection.EvnSection_setTime as sortDate
				,ISNULL(MP.Person_Fio, '') as MedPersonal_Fio
				,0 as ChildrensCount
			from 
				v_EvnSection EvnSection with (nolock)
				inner join v_LpuSection LpuSection with (nolock) on EvnSection.LpuSection_id = LpuSection.LpuSection_id
				left join v_Lpu Lpu with (nolock) on EvnSection.Lpu_id = Lpu.Lpu_id
				left join v_Diag Diag with (nolock) on EvnSection.Diag_id = Diag.Diag_id
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = EvnSection.MedPersonal_id
				) MP
			where 
				EvnSection.EvnSection_id in (" . implode(",", $evnSectionList) . ")
		");
		//echo getDebugSql($sql, $params); exit;

		if ( $response === false || !is_array($response) || count($response) == 0 ) {
			return false;
		}

		// 4. Плюсуем данные о количестве дочерних событий к ChildrensCount
		foreach ( $response as $key => $row ) {
			$response[$key]['ChildrensCount'] += $evnSectionChildrenCountList[$row['EvnSection_id']];
		}

		return swFilterResponse::filterNotViewDiag($response, $data);
	}

	/**
	 * Возваращает список нод
	 */
	function GetBirthSvidNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and BirthSvid.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}				
	
		$sql = "
		select 
			BirthSvid_id, 
			'Мед. свид-во о рождении:' as EvnClass_Name,
			rtrim(BirthSvid_Ser) as BirthSvid_Ser,
			rtrim(BirthSvid_Num) as BirthSvid_Num,
			RTrim(IsNull(convert(varchar,cast(BirthSvid_GiveDate as datetime),104),'')) as BirthSvid_GiveDate,
			rtrim(Lpu_Nick) as Lpu_Nick,
			rtrim(BirthPlace_Name) as BirthPlace_Name
		from BirthSvid with (nolock)
		inner join v_Lpu with (nolock) on v_Lpu.Lpu_id = BirthSvid.Lpu_id
		left join BirthPlace with (nolock) on BirthPlace.BirthPlace_id = BirthSvid.BirthPlace_id
		where {$filter}
		--order by BirthSvid_GiveDate";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnPLDispAdultNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();
		$addQuery = '';
		$addJoin = '';
		$with = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EPLD.EvnPLDisp_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EPLD.EvnPLDisp_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EPLD.EvnPLDisp_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EPLD.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EPLD.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else
		{
			$params['Lpu_id'] = 0;
		}

		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EPLD.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EPLD.EvnPLDisp_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EPLD.EvnPLDisp_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}

		$accessType = 'EPLD.Lpu_id = :Lpu_id';

		if ( isset($data['user_LpuUnitType_SysNick']) && in_array($data['user_LpuUnitType_SysNick'],array('polka','stac','dstac','hstac','pstac','parka')) )
		{
			//врач может редактировать, удалить, если он работает в поликлинике
			$accessType .= " and :user_LpuUnitType_SysNick = 'polka'";
			$params['user_LpuUnitType_SysNick'] = $data['user_LpuUnitType_SysNick'];
		}
		
		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getRevertAccessRightsDiagFilter('Diag_Code');
			if ($diagFilter) {
				$with = "
					with DiagList as (
						select Diag_id
						from v_Diag with (nolock)
						where {$diagFilter}
					)
				";
				//$addQuery .= ",isdiagaccess.EvnUslugaDispDop_id as Diag_id ";
				$addJoin .= "outer apply (
					select top 1 EUDD.EvnUslugaDispDop_id
					from v_EvnUslugaDispDop EUDD (nolock)
					where EUDD.EvnUslugaDispDop_rid = EPLD.EvnPLDisp_id
						and exists (
							select top 1 Diag_id from DiagList where Diag_id = EUDD.Diag_id
							union all
							select top 1 Diag_id from v_EvnDiagDopDisp with (nolock) where EvnDiagDopDisp_pid = EUDD.EvnUslugaDispDop_id and Diag_id in (select Diag_id from DiagList)
						)
				) as isdiagaccess ";
				$filter .= " and (isdiagaccess.EvnUslugaDispDop_id is null or DC.DispClass_Code not in (19,26)) ";
				
				//$addQuery .= ", isdirdiagaccess.Diag_id ";
				$addJoin .= "outer apply (
					select top 1 Diag_id
					from v_EvnDirection (nolock)
					where EvnDirection_pid = EPLD.EvnPLDisp_id
						and Diag_id in (select Diag_id from DiagList)
				) as isdirdiagaccess ";
				$filter .= " and (isdirdiagaccess.Diag_id is null or DC.DispClass_Code not in (19,26)) ";
			}
		}
		$userGroups = array();
		if (!empty($data['session']['groups']) && is_string($data['session']['groups'])) {
			$userGroups = explode('|', $data['session']['groups']);
		}
		if (!count(array_uintersect($userGroups, array('DrivingCommissionReg','DrivingCommissionOphth','DrivingCommissionPsych','DrivingCommissionPsychNark','DrivingCommissionTherap'), "strcasecmp")) && $this->regionNick == 'perm') {
			$filter .= " and DC.DispClass_Code != 26 ";
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";
		
		$sql = "
			{$with}

			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EPLD.Lpu_id,
				EPLD.EvnPLDisp_id,
				EPLD.EvnPLDisp_pid,
				EPLD.EvnPLDisp_rid,
				EPLD.EvnClass_id,
				RTrim(EPLD.EvnClass_Name) as EvnClass_Name,
				IsNull(convert(varchar,EPLD.EvnPLDisp_setDT,104),'') as EvnPLDisp_setDT,
				convert(varchar,EPLD.EvnPLDisp_setDT,104)+' '+EPLD.EvnPLDisp_setTime as sortDate,
				RTrim(IsNull(convert(varchar,cast(EPLD.EvnPLDisp_disDate as datetime),104),'')) as EvnPLDisp_disDT,
				RTrim(IsNull(convert(varchar,cast(EPLD.EvnPLDisp_didDate as datetime),104),'')) as EvnPLDisp_didDT,
				coalesce(epldd13.EvnPLDispDop13_IsEndStage, epldp.EvnPLDispProf_IsEndStage, EPLD.EvnPLDisp_IsFinish) as EvnPLDisp_IsFinish,
				isnull(EPLD.EvnPLDisp_VizitCount,0) as EvnPLDisp_VizitCount,
				RTrim(Lpu.Lpu_Nick) as Lpu_Nick,
				DC.DispClass_Name,
				EPLD.DispClass_id,
				case
					when exists (select top 1 EvnDirection_id from v_EvnDirection with (nolock) where EvnDirection_pid = EPLD.EvnPLDisp_id and EPLD.DispClass_id in(19,26)) then 1
					else 0
				end as ChildrensCount
				{$addQuery}
			from v_EvnPLDisp EPLD with (nolock)
				inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLD.DispClass_id
				left join v_EvnPLDispDop13 epldd13 with (nolock) on epldd13.EvnPLDispDop13_id = EPLD.EvnPLDisp_id
				left join v_EvnPLDispProf epldp with (nolock) on epldp.EvnPLDispProf_id = EPLD.EvnPLDisp_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=EPLD.Lpu_id
				{$addJoin}
			where {$filter}
				and DC.DispClass_Code in (1,2,5,19,26)
			--order by EPLD.EvnPLDisp_setDT desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			$arr = swFilterResponse::filterNotViewDiag($res->result('array'), $data);
			//$arr = $res->result('array');
			return $arr;
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnPLDispChildNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EPLD.EvnPLDisp_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EPLD.EvnPLDisp_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EPLD.EvnPLDisp_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EPLD.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EPLD.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else
		{
			$params['Lpu_id'] = 0;
		}

		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EPLD.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EPLD.EvnPLDisp_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EPLD.EvnPLDisp_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}

		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1)";
		}*/
		
		$accessType = 'EPLD.Lpu_id = :Lpu_id';

		if ( isset($data['user_LpuUnitType_SysNick']) && in_array($data['user_LpuUnitType_SysNick'],array('polka','stac','dstac','hstac','pstac','parka')) )
		{
			//врач может редактировать, удалить, если он работает в поликлинике
			$accessType .= " and :user_LpuUnitType_SysNick = 'polka'";
			$params['user_LpuUnitType_SysNick'] = $data['user_LpuUnitType_SysNick'];
		}
		$sql = "
		select
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EPLD.Lpu_id,
			EPLD.EvnPLDisp_id,
			EPLD.EvnPLDisp_pid,
			EPLD.EvnPLDisp_rid,
			EPLD.EvnClass_id,
			RTrim(EPLD.EvnClass_Name) as EvnClass_Name,
			IsNull(convert(varchar,EPLD.EvnPLDisp_setDT,104),'') as EvnPLDisp_setDT,
			convert(varchar,EPLD.EvnPLDisp_setDT,104)+' '+EPLD.EvnPLDisp_setTime as sortDate,
			RTrim(IsNull(convert(varchar,cast(EPLD.EvnPLDisp_disDate as datetime),104),'')) as EvnPLDisp_disDT,
			RTrim(IsNull(convert(varchar,cast(EPLD.EvnPLDisp_didDate as datetime),104),'')) as EvnPLDisp_didDT,
			EPLD.EvnPLDisp_IsFinish,
			isnull(EPLD.EvnPLDisp_VizitCount,0) as EvnPLDisp_VizitCount,
			RTrim(Lpu.Lpu_Nick) as Lpu_Nick,
			DC.DispClass_Name,
			EPLD.DispClass_id
			{$addQuery}
		from v_EvnPLDisp EPLD with (nolock)
		inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLD.DispClass_id
		left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=EPLD.Lpu_id
		where {$filter}
			and DC.DispClass_Code in (3,4,6,7,8,9,10,11,12)
		--order by EPLD.EvnPLDisp_setDT desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			$arr = swFilterResponse::filterNotViewDiag($res->result('array'), $data);
			//$arr = $res->result('array');
			return $arr;
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnPLDispScreenNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EPLDS.EvnPLDispScreen_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EPLDS.EvnPLDispScreen_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EPLDS.EvnPLDispScreen_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EPLDS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EPLDS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else
		{
			$params['Lpu_id'] = 0;
		}

		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EPLDS.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EPLDS.EvnPLDispScreen_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EPLDS.EvnPLDispScreen_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1)";
		}*/

		$accessType = 'EPLDS.Lpu_id = :Lpu_id';

		if ( isset($data['user_LpuUnitType_SysNick']) && in_array($data['user_LpuUnitType_SysNick'],array('polka','stac','dstac','hstac','pstac','parka')) )
		{
			//врач может редактировать, удалить, если он работает в поликлинике
			$accessType .= " and :user_LpuUnitType_SysNick = 'polka'";
			$params['user_LpuUnitType_SysNick'] = $data['user_LpuUnitType_SysNick'];
		}
		$sql = "
		select
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EPLDS.Lpu_id,
			EPLDS.EvnPLDispScreen_id,
			EPLDS.EvnPLDispScreen_pid,
			EPLDS.EvnPLDispScreen_rid,
			EPLDS.EvnClass_id,
			RTrim(EPLDS.EvnClass_Name) as EvnClass_Name,
			IsNull(convert(varchar,EPLDS.EvnPLDispScreen_setDT,104),'') as EvnPLDispScreen_setDT,
			convert(varchar,EPLDS.EvnPLDispScreen_setDT,104)+' '+EPLDS.EvnPLDispScreen_setTime as sortDate,
			RTrim(IsNull(convert(varchar,cast(EPLDS.EvnPLDispScreen_disDate as datetime),104),'')) as EvnPLDispScreen_disDT,
			RTrim(IsNull(convert(varchar,cast(EPLDS.EvnPLDispScreen_didDate as datetime),104),'')) as EvnPLDispScreen_didDT,
			EPLDS.EvnPLDispScreen_IsFinish,
			isnull(EPLDS.EvnPLDispScreen_VizitCount,0) as EvnPLDispScreen_VizitCount,
			RTrim(Lpu.Lpu_Nick) as Lpu_Nick,
			DC.DispClass_Name
			{$addQuery}
		from v_EvnPLDispScreen EPLDS with (nolock)
		inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLDS.DispClass_id
		left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=EPLDS.Lpu_id
		where {$filter}
		--order by EPLDS.EvnPLDispScreen_setDT desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			$arr = swFilterResponse::filterNotViewDiag($res->result('array'), $data);
			//$arr = $res->result('array');
			return $arr;
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnPLDispScreenChildNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EPLDS.EvnPLDispScreenChild_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EPLDS.EvnPLDispScreenChild_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EPLDS.EvnPLDispScreenChild_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EPLDS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EPLDS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else
		{
			$params['Lpu_id'] = 0;
		}

		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EPLDS.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EPLDS.EvnPLDispScreenChild_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EPLDS.EvnPLDispScreenChild_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1)";
		}*/

		$accessType = 'EPLDS.Lpu_id = :Lpu_id';

		if ( isset($data['user_LpuUnitType_SysNick']) && in_array($data['user_LpuUnitType_SysNick'],array('polka','stac','dstac','hstac','pstac','parka')) )
		{
			//врач может редактировать, удалить, если он работает в поликлинике
			$accessType .= " and :user_LpuUnitType_SysNick = 'polka'";
			$params['user_LpuUnitType_SysNick'] = $data['user_LpuUnitType_SysNick'];
		}
		$sql = "
		select
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EPLDS.Lpu_id,
			EPLDS.EvnPLDispScreenChild_id,
			EPLDS.EvnPLDispScreenChild_pid,
			EPLDS.EvnPLDispScreenChild_rid,
			EPLDS.EvnClass_id,
			RTrim(EPLDS.EvnClass_Name) as EvnClass_Name,
			IsNull(convert(varchar,EPLDS.EvnPLDispScreenChild_setDT,104),'') as EvnPLDispScreenChild_setDT,
			convert(varchar,EPLDS.EvnPLDispScreenChild_setDT,104)+' '+EPLDS.EvnPLDispScreenChild_setTime as sortDate,
			RTrim(IsNull(convert(varchar,cast(EPLDS.EvnPLDispScreenChild_disDate as datetime),104),'')) as EvnPLDispScreenChild_disDT,
			RTrim(IsNull(convert(varchar,cast(EPLDS.EvnPLDispScreenChild_didDate as datetime),104),'')) as EvnPLDispScreenChild_didDT,
			EPLDS.EvnPLDispScreenChild_IsFinish,
			isnull(EPLDS.EvnPLDispScreenChild_VizitCount,0) as EvnPLDispScreenChild_VizitCount,
			RTrim(Lpu.Lpu_Nick) as Lpu_Nick,
			DC.DispClass_Name
			{$addQuery}
		from v_EvnPLDispScreenChild EPLDS with (nolock)
		inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLDS.DispClass_id
		left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=EPLDS.Lpu_id
		where {$filter}
		--order by EPLDS.EvnPLDispScreenChild_setDT desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			$arr = swFilterResponse::filterNotViewDiag($res->result('array'), $data);
			//$arr = $res->result('array');
			return $arr;
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnPLNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$addQuery = "";
		$filterList = array("(1 = 1)");
		$params = array();

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(Evn.Evn_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filterList[] = "ISNULL(Evn.Evn_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filterList[] = "ISNULL(Evn.Evn_IsArchive, 1) = 2";
			}
		}

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0)) {
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id'])) {
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else {
			$params['Lpu_id'] = 0;
		}

		if ( !empty($data['Person_id']) ) {
			$filterList[] = "Evn.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		if ( !empty($data['Diag_id']) ) {
			$filterList[] = 'EvnDiagPLOsn.Diag_id = :Diag_id';
			$params['Diag_id'] = $data['Diag_id'];
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filterList[] = "Evn.Evn_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filterList[] = "Evn.Evn_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}

		$diagFilter = getAccessRightsDiagFilter('EvnDiagPLOsn.Diag_Code');
		if (!empty($diagFilter)) {
			$filterList[] = $diagFilter;
		}

		$lpuFilter = getAccessRightsLpuFilter('Evn.Lpu_id');
		if ( !empty($lpuFilter) ) {
			$filterList[] = $lpuFilter;
		}

		$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LpuBuilding.LpuBuilding_id');
		if (!empty($lpuBuildingFilter)) {
			$filterList[] = $lpuBuildingFilter;
		}

		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filterList[] = " (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1)";
		}*/
		$filterList[] = " 
		( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";

		$params['CurMedPersonal_id'] = (isset($_SESSION['medpersonal_id']))?$_SESSION['medpersonal_id']:0;
		$params['CurLpuSection_id'] = (isset($_SESSION['CurLpuSection_id']))?$_SESSION['CurLpuSection_id']:0;

		$accessType = 'Evn.Lpu_id = :Lpu_id';
		if ( isset($data['user_LpuUnitType_SysNick']) && in_array($data['user_LpuUnitType_SysNick'],array('polka','stac','dstac','hstac','pstac','parka')) )
		{
			//врач может редактировать, удалить, если он работает в поликлинике
			$accessType .= " and :user_LpuUnitType_SysNick = 'polka'";
			$params['user_LpuUnitType_SysNick'] = $data['user_LpuUnitType_SysNick'];
		}
		$sql = "
			select 
				case when {$accessType} then 'edit' else 'view' end as accessType,
				Evn.Lpu_id,
				EvnPL.Diag_id,
				--ISNULL(Evn.Evn_IsSigned,1) as EvnPL_IsSigned,
				Evn.Evn_pid as EvnPL_pid,
				Evn.Evn_id as EvnPL_id,
				Evn.pmUser_insID,
				Evn.pmUser_updID,
				Evn.EvnClass_id,
				RTrim(EC.EvnClass_Name) as EvnClass_Name,
				RTrim(EvnDiagPLOsn.Diag_Code) as Diag_Code,
				RTrim(EvnDiagPLOsn.Diag_Name) as Diag_Name,
				convert(varchar, Evn.Evn_setDT, 104) as EvnPL_setDT,/* равно дате первого посещения */
				convert(varchar, Evn.Evn_disDT, 104) as EvnPL_disDT,/* равно дате последнего посещения */
				convert(varchar, Evn.Evn_setDT,104)+' '+ convert(varchar(8), Evn.Evn_setDT,108) as sortDate,
				EvnPLBase.EvnPLBase_VizitCount as EvnVizit_Count,
				EvnPLBase_IsFinish as EvnPL_IsFinish,
				RTrim(case when EvnPLBase.EvnPLBase_IsFinish = 2 then ResultClass_Name else '' end) as ResultClass_Name,
				case when EvnPLBase.EvnPLBase_IsFinish = 2 then 'folder-ok16' else 'folder-notok16' end as iconCls,
				RTrim(Lpu_Nick) as Lpu_Nick,
				ISNULL(ResultClass.ResultClass_Name,'') as ResultClass_Name,
				EvnPLBase.EvnPLBase_VizitCount as ChildrensCount
				,LpuUnit.LpuUnitSet_id
				,case when EVMP.EvnVizitPL_id is not null then 1 else 0 end as IsThis_MedPersonal
				,case when hasNapravlNaUdalKonsult.EvnDirection_id is not null then 2 else 1 end as IsNapravlNaUdalKonsult
				{$addQuery}
			from 
				EvnPL EvnPL with (nolock)
				inner join EvnPLBase with (nolock) on EvnPLBase.Evn_id = EvnPL.EvnPL_id
				inner join Evn with (nolock) on Evn.Evn_id = EvnPL.EvnPL_id
				inner join EvnClass EC with (nolock) on EC.EvnClass_id = Evn.EvnClass_id
				outer apply (
					Select top 1 * from v_Diag as EvnDiagPLOsn with (nolock) where EvnDiagPLOsn.Diag_id = EvnPL.Diag_id
				) EvnDiagPLOsn
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = Evn.Lpu_id
				left join v_ResultClass ResultClass with (nolock) on ResultClass.ResultClass_id = EvnPL.ResultClass_id
				outer apply (
					select top 1 EvnVizitPL_id from v_EvnVizitPL with (nolock)
					where EvnVizitPL_pid = EvnPL.EvnPL_id
						and Lpu_id = Evn.Lpu_id
						and MedPersonal_id = :CurMedPersonal_id
						and LpuSection_id = :CurLpuSection_id
				) EVMP
				outer apply (
					select top 1 
						v_ED.EvnDirection_id as EvnDirection_id
					from 
						v_EvnVizitPL with (nolock)
						outer apply (
							select top 1 
								v_EvnDirection.EvnDirection_id as EvnDirection_id
							from
								v_EvnDirection with (nolock)
								inner join v_DirType with (nolock) ON 
									v_DirType.DirType_id = v_EvnDirection.DirType_id AND
									v_DirType.DirType_Code = 13
							where 
								v_EvnDirection.EvnDirection_pid = v_EvnVizitPL.EvnVizitPL_id
						) v_ED
					where 
						v_EvnVizitPL.EvnVizitPL_pid = EvnPL.EvnPL_id and v_EvnVizitPL.Lpu_id = Evn.Lpu_id
				) hasNapravlNaUdalKonsult
				left join LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = EvnPL.LpuSection_id
				left join LpuUnit LpuUnit with (nolock) on LpuSection.LpuUnit_id = LpuUnit.LpuUnit_id
				left join LpuBuilding LpuBuilding with (nolock) on LpuUnit.LpuBuilding_id = LpuBuilding.LpuBuilding_id
			where " . implode(" and ", $filterList) . "
				and ISNULL(Evn.Evn_deleted, 1) = 1
				and Evn.EvnClass_id = 3
			--order by EvnPL_setDT
		";
		
		
		/*echo getDebugSql($sql, $params);
		exit;*/
		/*
			EVPL.EvnVizitPL_id as LastEvnVizitPL_id,
			EVPL.MedPersonal_id,
			EVPL.LpuSection_id,
			EVPL.pmUser_insID,
			convert(varchar,cast(EVPL.EvnVizitPL_setDT as datetime),104) as EvnVizitPL_setDate,
		outer apply (
			select top 1
				EvnVizitPL_id,
				LpuSection_id,
				MedPersonal_id,
				EvnVizitPL_setDT,
				pmUser_insID
			from
				v_EvnVizitPL with (nolock)
			where
				EvnVizitPL_pid = EvnPL.EvnPL_id
			order by
				EvnVizitPL_setDT desc
		) EVPL
		*/
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return $res->result('array');
			//return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnPLStomNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$addQuery = "";
		$params = array();

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnPLStom.EvnPLStom_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnPLStom.EvnPLStom_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnPLStom.EvnPLStom_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EvnPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EvnPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else
		{
			$params['Lpu_id'] = 0;
		}
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and EvnPL.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and EvnPL.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnPLStom.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EvnPLStom.EvnPLStom_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EvnPLStom.EvnPLStom_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('EvnDiagPLOsn.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnPLStom.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1)";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";
		
		$params['CurMedPersonal_id'] = (isset($_SESSION['medpersonal_id']))?$_SESSION['medpersonal_id']:0;
		$params['CurLpuSection_id'] = (isset($_SESSION['CurLpuSection_id']))?$_SESSION['CurLpuSection_id']:0;

		$accessType = 'EvnPLStom.Lpu_id = :Lpu_id';
		/*if ( !in_array($data['session']['region']['nick'], array('ufa', 'kareliya')) )
		{
			//Везде кроме Уфы и Карелии закрыта возможность редактировать закрытый случай АПЛ refs #5033 #41780
			$accessType .= ' AND ISNULL(EvnPLStom.EvnPLStom_IsFinish,1) != 2';
		}*/
		$sql = "
		select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnPLStom.Diag_id,
			EvnPLStom.EvnPLStom_pid,
			EvnPLStom.Lpu_id,
			EvnPLStom_id,
			EvnPLStom.EvnClass_id,
			RTrim(EvnPLStom.EvnClass_Name) as EvnClass_Name,
			RTrim(EvnDiagPLOsn.Diag_Code) as Diag_Code,
			RTrim(EvnDiagPLOsn.Diag_Name) as Diag_Name,
			convert(varchar, EvnPLStom_setDT, 104) as EvnPLStom_setDT,/* равно дате первого посещения */
			convert(varchar, EvnPLStom_disDT, 104) as EvnPLStom_disDT,/* равно дате последнего посещения */
			convert(varchar,EvnPLStom_setDT,104)+' '+ EvnPLStom_setTime as sortDate,
			EvnPLStom.EvnPLStom_VizitCount as EvnPLStom_VizitCount,
			EvnPLStom_IsFinish,
			RTrim(case when EvnPLStom_IsFinish = 2 then ResultClass_Name else '' end) as ResultClass_Name,
			RTrim(Lpu_Nick) as Lpu_Nick,
			EvnPLStom.EvnPLStom_VizitCount as ChildrensCount
			,case when EVMP.EvnVizitPLStom_id is not null then 1 else 0 end as IsThis_MedPersonal
			{$addQuery}
		from v_EvnPLStom EvnPLStom with (nolock)
		left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=EvnPLStom.Lpu_id
		left join v_ResultClass ResultClass with (nolock) on ResultClass.ResultClass_id=EvnPLStom.ResultClass_id
		left join v_Diag as EvnDiagPLOsn with (nolock) on EvnDiagPLOsn.Diag_id = EvnPLStom.Diag_id
		outer apply (
			select top 1 EvnVizitPLStom_id from v_EvnVizitPLStom with (nolock)
			where EvnVizitPLStom_pid = EvnPLStom.EvnPLStom_id
			and MedPersonal_id = :CurMedPersonal_id ". (($params['CurLpuSection_id']>0)?" and LpuSection_id = :CurLpuSection_id":"") . "
		) EVMP
		where {$filter}
		--order by EvnPLStom_setDate";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return $res->result('array');
			//return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	
	
	
	/**
	 * Возваращает список нод
	 */
	function GetCmpCloseCard($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when 1=1/*ISNULL(CLC.CmpCloseCard_IsArchive, 1) = 1*/ then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and 1=1/*ISNULL(CLC.CmpCloseCard_IsArchive, 1) = 1*/";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and 1=0/*ISNULL(CLC.CmpCloseCard_IsArchive, 1) = 2*/";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EvnPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EvnPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else {
			$params['Lpu_id'] = 0;
		}
		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and CC.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		$vippersonfilter = " vPer.Person_id = :Person_id ";

		// оффлайн данные для набора персонов
		if (!empty($data['person_in'])) {
			$filter .= " and CC.Person_id in ({$data['person_in']}) ";
			$addQuery .= " ,CC.Person_id ";

			$vippersonfilter = " vPer.Person_id in ({$data['person_in']}) ";
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and CLC.AcceptTime >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and CLC.AcceptTime <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
	
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1)";
		}*/
		// 05.04.2019 А.И.Г.
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where {$vippersonfilter} and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";
		
		$accessType = 'CLC.Lpu_id = :Lpu_id';

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('CLC.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('CLC.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		$schema = '';
		if($this->regionNick == 'ufa'){
			$schema = 'dbo.';
		}
		$sql = "
		select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			CLC.CmpCloseCard_id, 
			'Вызов скорой помощи' as Name, 
			CC.Lpu_id,
			CLC.CmpCallCard_id,			
			CLC.Day_num,			
			CLC.Year_num,
			CLC.Day_num,
			CLC.Year_num,
			CLC.CmpCloseCard_DayNumPr,
			CLC.CmpCloseCard_YearNumPr,
			RTrim(IsNull(convert(varchar,cast(CLC.AcceptTime as datetime),104),'')) as AcceptTime,
			RTrim(Lpu.Lpu_Nick) as Lpu_Nick
			{$addQuery}
		from {$schema}v_CmpCloseCard CLC with (nolock)
		left join v_CmpCallCard CC with (nolock) on CC.CmpCallCard_id=CLC.CmpCallCard_id
		left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=CC.Lpu_id
		left join v_Diag Diag with (nolock) on Diag.Diag_id = CLC.Diag_id
		where {$filter}
		--order by CLC.AcceptTime";
		//echo getDebugSQL($sql, $params);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}
	
	 /**
	 * Возваращает список нод
	 */
	function GetCmpCallCard($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(CC.CmpCallCard_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(CC.CmpCallCard_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(CC.CmpCallCard_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		$filter .= " and isnull(CC.CmpCallCard_IsReceivedInPPD,1) <> 2";

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EvnPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EvnPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else {
			$params['Lpu_id'] = 0;
		}
		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and CC.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		$vippersonfilter = " vPer.Person_id = :Person_id ";

		// оффлайн данные для набора персонов
		if (!empty($data['person_in'])) {
			$filter .= " and CC.Person_id in ({$data['person_in']}) ";
			$addQuery .= " ,CC.Person_id ";

			$vippersonfilter = " vPer.Person_id in ({$data['person_in']}) ";
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and CC.CmpCallCard_prmDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and CC.CmpCallCard_prmDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where {$vippersonfilter} and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";
		
		$filter.= " ";
		$accessType = 'CC.Lpu_id = :Lpu_id';

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;

		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('DiagU.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$diagFilter = getAccessRightsDiagFilter('DiagS.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('CC.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('CC.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		$sql = "
		select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			CC.CmpCallCard_id, 
			'Вызов скорой помощи' as Name, 
			CC.Lpu_id,
			CC.CmpCallCard_Numv as Day_num,
			CC.CmpCallCard_Ngod as Year_num,
			CC.CmpCallCard_NumvPr,
			CC.CmpCallCard_NgodPr,
			convert(varchar(10), CC.CmpCallCard_prmDT, 104) as objectSetDate,
			convert(varchar(5), CC.CmpCallCard_prmDT, 108) as objectSetTime,
			convert(varchar(10),CC.CmpCallCard_prmDT,104) as AcceptTime,
			RTrim(Lpu.Lpu_Nick) as Lpu_Nick,
			DiagU.Diag_Code,
			DiagU.Diag_Name
			{$addQuery}
		from v_CmpCallCard CC with (nolock)
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=CC.Lpu_id
			left join v_Diag DiagU with (nolock) on DiagU.Diag_id = CC.Diag_uid
			left join v_Diag DiagS with (nolock) on DiagS.Diag_id = CC.Diag_sid
		where {$filter}
		--order by CC.CmpCallCard_prmDT";
		//echo getDebugSQL($sql, $params); exit;
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}
	
	/**
	 * Возваращает список нод
	 */
	function GetEvnPLDispDopNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		$addQuery = "";

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnPLDispDop.EvnPLDispDop_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnPLDispDop.EvnPLDispDop_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnPLDispDop.EvnPLDispDop_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and EvnPLDispDop.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnPLDispDop.Lpu_id=".$data['session']['lpu_id'];
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and EvnPLDispDop.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and EvnPLDispDop.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnPLDispDop.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EvnPLDispDop.EvnPLDispDop_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EvnPLDispDop.EvnPLDispDop_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		/*if(isset($data['session']['lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			$params['Lpu_id'] = $data['session']['lpu_id'];
			$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		
		$sql = "
			select 
				--EvnPLDispDop.Diag_id,
				EvnPLDispDop.EvnPLDispDop_pid,
				EvnPLDispDop.Lpu_id,
				EvnPLDispDop_id, 
				EvnPLDispDop.EvnClass_id,
				RTrim(EvnPLDispDop.EvnClass_Name) as EvnClass_Name,
				RTrim(IsNull(convert(varchar,cast(EvnPLDispDop_setDate as datetime),104),'')) as EvnPLDispDop_setDT, 
				RTrim(IsNull(convert(varchar,cast(EvnPLDispDop_disDate as datetime),104),'')) as EvnPLDispDop_disDT, 
				EvnPLDispDop_VizitCount as EvnVizitDispDop_Count,
				RTrim(Lpu_Nick) as Lpu_Nick
				{$addQuery}
			from v_EvnPLDispDop EvnPLDispDop with (nolock)
			inner join v_Lpu Lpu with (nolock) on
				Lpu.Lpu_id=EvnPLDispDop.Lpu_id
			where {$filter}";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetBegPersonPrivilegeNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and (PersonPrivilege.Lpu_id=".$data['Lpu_id']." or PersonPrivilege.Lpu_id is null)";
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and (PersonPrivilege.Lpu_id=".$data['session']['lpu_id']." or PersonPrivilege.Lpu_id is null)";
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and PersonPrivilege.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and PersonPrivilege.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and PersonPrivilege.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and PersonPrivilege.PersonPrivilege_begDate >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and PersonPrivilege.PersonPrivilege_begDate <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		// $data = getSessionParams();
		if (isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa')
		{
			$identification_privilege = "case when (PrivilegeType.ReceptFinance_id = 2)
			then
				'lgot-region16'
			else 
				'lgot-federal16'
			end as iconCls,";
		}
		else
		{
			$identification_privilege = "case when isnumeric(PersonPrivilege.PrivilegeType_Code) = 1 and PersonPrivilege.PrivilegeType_Code < 250
			then 'lgot-federal16'
			else 
				case when isnumeric(PersonPrivilege.PrivilegeType_Code) = 1 and PersonPrivilege.PrivilegeType_Code > 500
				then 'lgot-local16'
				else 'lgot-region16'
				end
			end as iconCls,";
		}
		$sql = "
			select 
			PersonPrivilege_id, 
			PersonPrivilege.PrivilegeType_id,
			PersonPrivilege.PersonRefuse_IsRefuse,
			{$identification_privilege}
			RTrim(IsNull(convert(varchar,cast(PersonPrivilege_endDate as datetime),104),'')) as PersonPrivilege_endDT, 
			RTrim(PersonPrivilege.PrivilegeType_Name) as PrivilegeType_Name,
			RTrim(IsNull(convert(varchar,cast(PersonPrivilege_begDate as datetime),104),'')) as PersonPrivilege_begDT--, 
			--RTrim(Lpu_Nick) as Lpu_Nick
			from v_PersonPrivilege PersonPrivilege with (nolock)
			left join v_PrivilegeType PrivilegeType with (nolock) on PrivilegeType.PrivilegeType_id=PersonPrivilege.PrivilegeType_id
			--left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=PersonPrivilege.Lpu_id
			where {$filter}
			group by PersonPrivilege_id, PersonPrivilege.PrivilegeType_id, PersonPrivilege.PersonRefuse_IsRefuse, PersonPrivilege.PrivilegeType_Code, PersonPrivilege.PrivilegeType_Name, PrivilegeType.ReceptFinance_id, PersonPrivilege_endDate, PersonPrivilege_begDate--, Lpu_Nick
			--order by PersonPrivilege_begDate";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEndPersonPrivilegeNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and (PersonPrivilege.Lpu_id=".$data['Lpu_id']." or PersonPrivilege.Lpu_id is null)";
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and (PersonPrivilege.Lpu_id=".$data['session']['lpu_id']." or PersonPrivilege.Lpu_id is null)";
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and PersonPrivilege.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and PersonPrivilege.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and PersonPrivilege.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and PersonPrivilege.PersonPrivilege_endDate >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and PersonPrivilege.PersonPrivilege_endDate <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		if (isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa')
		{
			$identification_privilege = "case when (PrivilegeType.ReceptFinance_id = 2)
			then
				'lgot-region16'
			else 
				'lgot-federal16'
			end as iconCls,";
		}
		else
		{
			$identification_privilege = "case when isnumeric(PersonPrivilege.PrivilegeType_Code) = 1 and PersonPrivilege.PrivilegeType_Code < 250
			then 'lgot-federal16'
			else 
				case when isnumeric(PersonPrivilege.PrivilegeType_Code) = 1 and PersonPrivilege.PrivilegeType_Code > 500
				then 'lgot-local16'
				else 'lgot-region16'
				end
			end as iconCls,";
		}
		$sql = "
			select 
			PersonPrivilege_id, 
			PersonPrivilege.PrivilegeType_id,
			PersonPrivilege.PersonRefuse_IsRefuse,
			{$identification_privilege}
			RTrim(IsNull(convert(varchar,cast(PersonPrivilege_endDate as datetime),104),'')) as PersonPrivilege_endDT, 
			RTrim(PersonPrivilege.PrivilegeType_Name) as PrivilegeType_Name,
			RTrim(IsNull(convert(varchar,cast(PersonPrivilege_begDate as datetime),104),'')) as PersonPrivilege_begDT--, 
			--RTrim(Lpu_Nick) as Lpu_Nick
			from v_PersonPrivilege PersonPrivilege with (nolock)
			left join v_PrivilegeType PrivilegeType  with (nolock) on PrivilegeType.PrivilegeType_id=PersonPrivilege.PrivilegeType_id
			--left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=PersonPrivilege.Lpu_id
			where {$filter} and PersonPrivilege_endDate is not null
			group by PersonPrivilege_id, PersonPrivilege.PrivilegeType_id, PersonPrivilege.PersonRefuse_IsRefuse, PersonPrivilege.PrivilegeType_Code, PersonPrivilege.PrivilegeType_Name, PrivilegeType.ReceptFinance_id, PersonPrivilege_endDate, PersonPrivilege_begDate--, Lpu_Nick
			--order by PersonPrivilege_endDate";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetBegPersonDispNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and (PersonDisp.Lpu_id=".$data['Lpu_id']." or PersonDisp.Lpu_id is null)";
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and (PersonDisp.Lpu_id=".$data['session']['lpu_id']." or PersonDisp.Lpu_id is null)";
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and PersonDisp.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and PersonDisp.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and PersonDisp.Person_id=".$data['Person_id'];
		}
		
		if ((isset($data['EvnVizitDispDop_id'])) && ($data['EvnVizitDispDop_id']>0))
		{
			// Как Тарас сделает 
			//$filter .= " and PersonDisp.PersonDisp_pid=".$data['EvnVizitDispDop_id'];
			$filter .= " and 1=0";
		}
		
		if ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0))
		{
			// Как Тарас сделает 
			//$filter .= " and PersonDisp.PersonDisp_pid=".$data['EvnVizitPL_id'];
			$filter .= " and 1=0";
		}
		
		$sql = "
			Select 
				PersonDisp.Lpu_id,
				PersonDisp.Diag_id,
				--PersonDisp.Person_id as PersonDisp_pid, 
				PersonDisp_id, 
				RTrim(IsNull(convert(varchar,cast(PersonDisp_begDate as datetime),104),'')) as PersonDisp_begDT, 
				RTrim(IsNull(Diag.Diag_Code,'')) as Diag_Code,
				RTrim(IsNull(Diag.Diag_Name,'')) as Diag_Name,
				RTrim(Lpu_Nick) as Lpu_Nick, 
				RTrim(LpuUnit.LpuUnit_Name) as LpuUnit_Name,
				RTrim(LpuSection.LpuSection_Name) as LpuSection_Name,
				RTrim(MedPersonal.Person_FIO) as MedPersonal_FIO,
				RTrim(IsNull(convert(varchar,cast(PersonDisp_NextDate as datetime),104),'')) as PersonDisp_NextDate
				from v_PersonDisp PersonDisp with (nolock)
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=PersonDisp.Lpu_id
				left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = PersonDisp.LpuSection_id
				left join v_LpuUnit LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
				left join v_MedPersonal MedPersonal with (nolock) on MedPersonal.MedPersonal_id = PersonDisp.MedPersonal_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = PersonDisp.Diag_id
				where {$filter}
				group by PersonDisp.Lpu_id, PersonDisp.Diag_id,PersonDisp_id, PersonDisp.PersonDisp_begDate, Diag_Code, Diag_Name, Lpu_Nick, LpuUnit_Name, LpuSection_Name, MedPersonal.Person_FIO, PersonDisp_NextDate
				--order by PersonDisp_begDate";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEndPersonDispNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and (PersonDisp.Lpu_id=".$data['Lpu_id']." or PersonDisp.Lpu_id is null)";
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and (PersonDisp.Lpu_id=".$data['session']['lpu_id']." or PersonDisp.Lpu_id is null)";
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and PersonDisp.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and PersonDisp.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and PersonDisp.Person_id=".$data['Person_id'];
		}
		
		if ((isset($data['EvnVizitDispDop_id'])) && ($data['EvnVizitDispDop_id']>0))
		{
			// Как Тарас сделает 
			//$filter .= " and PersonDisp.PersonDisp_pid=".$data['EvnVizitDispDop_id'];
			$filter .= " and 1=0";
		}
		
		if ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0))
		{
			// Как Тарас сделает 
			//$filter .= " and PersonDisp.PersonDisp_pid=".$data['EvnVizitPL_id'];
			$filter .= " and 1=0";
		}
		
		
		$sql = "
			Select 
				PersonDisp.Lpu_id,
				PersonDisp.Diag_id,
				--PersonDisp.Person_id as PersonDisp_pid, 
				PersonDisp_id, 
				RTrim(IsNull(convert(varchar,cast(PersonDisp_endDate as datetime),104),'')) as PersonDisp_endDT, 
				RTrim(IsNull(Diag.Diag_Code,'')) as Diag_Code,
				RTrim(IsNull(Diag.Diag_Name,'')) as Diag_Name,
				RTrim(Lpu_Nick) as Lpu_Nick, 
				RTrim(LpuUnit.LpuUnit_Name) as LpuUnit_Name,
				RTrim(LpuSection.LpuSection_Name) as LpuSection_Name,
				RTrim(MedPersonal.Person_FIO) as MedPersonal_FIO,
				RTrim(DispOutType_Name) as DispOutType_Name 
				from v_PersonDisp PersonDisp with (nolock)
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=PersonDisp.Lpu_id
				left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = PersonDisp.LpuSection_id
				left join v_LpuUnit LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
				left join v_MedPersonal MedPersonal with (nolock) on MedPersonal.MedPersonal_id = PersonDisp.MedPersonal_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = PersonDisp.Diag_id
				left join v_DispOutType DispOutType with (nolock) on DispOutType.DispOutType_id = PersonDisp.DispOutType_id
				where {$filter} and PersonDisp_endDate is not null
				group by PersonDisp.Lpu_id, PersonDisp.Diag_id,PersonDisp_id, PersonDisp.PersonDisp_endDate, Diag_Code, Diag_Name, Lpu_Nick, LpuUnit_Name, LpuSection_Name, MedPersonal.Person_FIO, DispOutType_Name
				--order by PersonDisp_endDate";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnVizitPLNodeList($data)
	{
		// Фильтры: EvnPL_id, Lpu_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EvnVizit.Lpu_id=".$data['Lpu_id'];
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EvnVizit.Lpu_id=".$data['session']['lpu_id'];
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else
		{
			$params['Lpu_id'] = 0;
		}

		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and EvnVizit.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and EvnVizit.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['EvnPL_id'])) && ($data['EvnPL_id']>0))
		{
			$filter .= " and EvnVizit.EvnVizitPL_pid = :EvnPL_id ";
			$params['EvnPL_id'] = $data['EvnPL_id'];
			if($this->getRegionNick() == 'kareliya'){
				$accessType = '
					exists(
						select 
							EvnVizitR.EvnVizitPL_id 
						from 
							v_EvnVizitPL EvnVizitR with (nolock)
							left join r10.v_RegistryData RD ON RD.Evn_sid = EvnVizitR.EvnVizitPL_id
							left join v_Registry R with (nolock) on RD.Registry_id = R.Registry_id
						where
							EvnVizitR.EvnVizitPL_id = EvnVizit.EvnVizitPL_id
							and 
								(
									ISNULL(EvnVizitR.EvnVizitPL_IsInReg, 1)!= 2
									or R.RegistryStatus_id=3
									or R.RegistryStatus_id=4 AND ISNULL(RD.RegistryData_IsPaid, 1)!=2
								)
					)';
			}else {
				$accessType = 'not exists(select EvnVizitR.EvnVizitPL_id from v_EvnVizitPL EvnVizitR with (nolock) where EvnVizitR.EvnVizitPL_pid = :EvnPL_id and EvnVizitR.EvnVizitPL_IsInReg = 2)';
			}
		} else if ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id']>0))
		{
			$filter .= " and EvnVizit.EvnVizitPL_pid = :EvnPL_id ";
			$params['EvnPL_id'] = $data['EvnPLStom_id'];
			$accessType = 'not exists(select EvnVizitR.EvnVizitPL_id from v_EvnVizitPL EvnVizitR with (nolock) where EvnVizitR.EvnVizitPL_pid = :EvnPL_id and EvnVizitR.EvnVizitPL_IsInReg = 2)';
		} else {
			$accessType = 'ISNULL(EvnVizit.EvnVizitPL_IsInReg, 1) = 1';
		}
		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnVizit.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter) {
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnVizit.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LpuUnit.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		
		$accessType .= ' and EvnVizit.Lpu_id = :Lpu_id';
		$add_join = '';
		if (isset($data['user_MedStaffFact_id']))
		{
			//врач может редактировать, если посещение создано в его ЛПУ, оно не оплачено, оно создано им, в его отделении и случай АПЛ не закончен
			$accessType .= ' and EvnVizit.MedPersonal_id = MSF.MedPersonal_id and EvnVizit.LpuSection_id = MSF.LpuSection_id';
			$add_join .= 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		if ( in_array($data['session']['region']['nick'], array('pskov', 'ufa', 'ekb')) ) {
			$add_join .= "
				outer apply (
					select top 1
						t1.EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid,
						t2.UslugaComplex_Code,
						t2.UslugaComplex_Name
					from
						v_EvnUslugaCommon t1 with (nolock)
						left join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUslugaCommon_pid = EvnVizit.EvnVizitPL_id
						and ISNULL(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
				) EU
			";
		}

        $params['session_medpersonal'] = '';
        $params['session_curlpusection'] = '';

        if((isset($_SESSION['CurLpuSection_id']))&&(!empty($_SESSION['CurLpuSection_id'])))
            $params['session_curlpusection'] = $_SESSION['CurLpuSection_id'];

        if((isset($_SESSION['medpersonal_id']))&&(!empty($_SESSION['medpersonal_id'])))
            $params['session_medpersonal'] = $_SESSION['medpersonal_id'];

		$sql = "
		Select
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnVizit.Diag_id,
			EvnVizit.Lpu_id,
			--ISNULL(EvnVizit.EvnVizitPL_IsSigned,1) as EvnVizitPL_IsSigned,
			EvnVizit.EvnVizitPL_pid,
			EvnVizit.EvnVizitPL_id,
			EvnVizit.pmUser_insID,
			EvnVizit.pmUser_updID,
			EvnVizit.MedPersonal_id,
			case when EvnVizit.MedPersonal_id = :session_medpersonal and EvnVizit.LpuSection_id = :session_curlpusection then 1 else 0 end as IsThis_MedPersonal,
			RTrim(EvnVizit.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnVizitPL_setDate as datetime),104),'')) as EvnVizitPL_setDT, 
			EvnVizitPL_setTime, 
			convert(varchar,EvnVizitPL_setDT,104)+' '+EvnVizitPL_setTime as sortDate,
			RTrim(LpuUnit.LpuUnit_Name) as LpuUnit_Name,
			RTrim(LpuSection.LpuSection_Name) as LpuSection_Name,
			RTrim(MedPersonal.Person_FIO) as MedPersonal_FIO,
			EvnVizit.Diag_id,
			EvnVizit.LpuSection_id,
			EvnVizit.LpuSectionProfile_id,
			RTrim(LpuSectionProfile.LpuSectionProfile_Code) AS LpuSectionProfile_Code,
			EvnVizit.MedPersonal_id,
			IsNull(convert(varchar,cast(EvnVizitPL_setDate as datetime),104),'') +' / ' + isnull(LpuSection.LpuSection_Name,'') + ' / ' + isnull(MedPersonal.Person_FIO,'') as Evn_Name,
			RTrim(IsNull(Diag.Diag_Code,'')) as Diag_Code,
			RTrim(IsNull(Diag.Diag_Name,'')) as Diag_Name,
			ServiceType.ServiceType_id,
			RTrim(IsNull(ServiceType.ServiceType_Name,'')) as ServiceType_Name,
			VizitType.VizitType_id,
			RTrim(IsNull(VizitType.VizitType_Name,'')) as VizitType_Name,
			RTrim(IsNull(VizitType.VizitType_SysNick,'')) as VizitType_SysNick,
			PayType.PayType_id,
			RTrim(IsNull(PayType.PayType_Name,'')) as PayType_Name,
			case
				when exists(select top 1 Evn_id from v_Evn with (nolock) where Evn_pid = EvnVizit.EvnVizitPL_id) then 1
				when exists(select top 1 EvnXml_id from EvnXml with (nolock) where Evn_id = EvnVizit.EvnVizitPL_id and XmlType_id = 2) then 1
				when exists(select top 1 MorbusOnkoVizitPLDop_id from v_MorbusOnkoVizitPLDop with (nolock) where EvnVizit_id = EvnVizit.EvnVizitPL_id) then 1
			else 0 end as ChildrensCount
			-- Услуга
			" .
			(in_array($data['session']['region']['nick'], array('pskov', 'ufa')) ?
				",EU.EvnUslugaCommon_id, EU.UslugaComplex_uid, EU.UslugaComplex_Code, EU.UslugaComplex_Name "
				:
				",NULL as EvnUslugaCommon_id, NULL as UslugaComplex_uid, NULL as UslugaComplex_Code, NULL as UslugaComplex_Name")
			. "

		from v_EvnVizitPL EvnVizit with (nolock)
		left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = EvnVizit.LpuSection_id
		left join LpuSectionProfile LpuSectionProfile with (nolock) on LpuSectionProfile.LpuSectionProfile_id=EvnVizit.LpuSectionProfile_id
		left join v_LpuUnit LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
		left join v_MedPersonal MedPersonal with (nolock) on MedPersonal.MedPersonal_id = EvnVizit.MedPersonal_id and MedPersonal.Lpu_id = EvnVizit.Lpu_id
		left join v_ServiceType ServiceType with (nolock) on ServiceType.ServiceType_id = EvnVizit.ServiceType_id
		left join v_VizitType VizitType with (nolock) on VizitType.VizitType_id = EvnVizit.VizitType_id
		left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnVizit.PayType_id
		left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnVizit.Diag_id
		{$add_join}
		where {$filter} 
		-- не стоматка
		and EvnVizit.EvnClass_id != 13
		--order by EvnVizitPL_setDate";
		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql, $params);
		if ( !is_object($res) ) {
			return false;
			//return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}

		$response = $res->result('array');

		if ($this->getRegionNick() == 'vologda') {
			foreach ($response as $key => $resp) {

				$this->load->model('Registry_model', 'Reg_model');
				$registryData = $this->Reg_model->checkEvnAccessInRegistry($resp, 'delete');

				$response[$key]['accessForDel'] = 'yes';

				if (is_array($registryData)) {
					if (!empty($registryData['Error_Msg'])) {
						$response[$key]['accessForDel'] = 'no';
					}
				}

			}
		}
		
		/*
		// еще один вариант оптимизации для подсчета количества дочерних объектов
		if ( !is_array($response) ) {
			return false;
		}

		$childrenCountQueryList = array(
			'select Evn_id as id, Evn_pid as pid from v_Evn with (nolock) where Evn_pid in ({idList})',
			'select EvnXml_id as id, Evn_id as pid from v_EvnXml with (nolock) where Evn_id in ({idList})',
			'select MorbusOnkoVizitPLDop_id as id, EvnVizit_id as pid from v_MorbusOnkoVizitPLDop with (nolock) where EvnVizit_id in ({idList})',

		);

		$childrenCount = array();
		$idList = array();

		foreach ( $response as $key => $row ) {
			$idList[] = $row['EvnVizitPL_id'];
			$childrenCount[$row['EvnVizitPL_id']] = 0;
		}

		if ( count($idList) > 0 ) {
			foreach ( $childrenCountQueryList as $query ) {
				$query = str_replace('{idList}', implode(',', $idList), $query);
				$res = $this->queryResult($query, array());

				if ( is_array($res) && count($res) > 0 ) {
					foreach ( $res as $row ) {
						$childrenCount[$row['pid']]++;
					}

					foreach ( $idList as $key => $EvnVizitPL_id ) {
						if ( $childrenCount[$EvnVizitPL_id] ) {
							unset($idList[$key]);
						}
					}
				}

				if ( count($idList) == 0 ) {
					break;
				}
			}

			foreach ( $response as $key => $row ) {
				if ( !in_array($row['EvnVizitPL_id'], $idList) ) {
					$response[$key]['ChildrensCount'] = 1;
				}
			}
		}*/

		return $response;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnVizitPLStomNodeList($data)
	{
		// Фильтры: EvnPL_id, Lpu_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and EvnVizit.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnVizit.Lpu_id=".$data['session']['lpu_id'];
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and EvnVizit.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and EvnVizit.Server_id=".$data['session']['server_id'];
		}
		*/
		
		if ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id']>0))
		{
			$filter .= " and EvnVizit.EvnVizitPLStom_pid = ".$data['EvnPLStom_id'];
		}
		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnVizit.Person_id=".$data['Person_id'];
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnVizit.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LpuUnit.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		$joinQuery = '';
		if ( in_array($data['session']['region']['nick'], array('pskov', 'ufa', 'ekb')) ) {
			$joinQuery .= "
				outer apply (
					select top 1
						t1.EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid,
						t2.UslugaComplex_Code,
						t2.UslugaComplex_Name
					from
						v_EvnUslugaCommon t1 with (nolock)
						left join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUslugaCommon_pid = EvnVizit.EvnVizitPLStom_id
						and ISNULL(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
				) EU
			";
		}
        $params['session_medpersonal'] = '';
        $params['session_curlpusection'] = '';

        if((isset($_SESSION['CurLpuSection_id']))&&(!empty($_SESSION['CurLpuSection_id'])))
            $params['session_curlpusection'] = $_SESSION['CurLpuSection_id'];

        if((isset($_SESSION['medpersonal_id']))&&(!empty($_SESSION['medpersonal_id'])))
            $params['session_medpersonal'] = $_SESSION['medpersonal_id'];
		$sql = "
		Select 
			EvnVizit.Diag_id,
			EvnVizit.EvnVizitPLStom_pid,
			EvnVizit.Lpu_id,
			EvnVizit.EvnVizitPLStom_id,
			RTrim(EvnVizit.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnVizitPLStom_setDate as datetime),104),'')) as EvnVizitPLStom_setDT,
			EvnVizit.EvnVizitPLStom_setTime,
			convert(varchar,EvnVizitPLStom_setDate,104)+' '+ EvnVizitPLStom_setTime as sortDate,
			RTrim(LpuUnit.LpuUnit_Name) as LpuUnit_Name,
			RTrim(LpuSection.LpuSection_Name) as LpuSection_Name,
			EvnVizit.LpuSectionProfile_id,
			RTrim(LpuSectionProfile.LpuSectionProfile_Code) AS LpuSectionProfile_Code,
			RTrim(MedPersonal.Person_FIO) as MedPersonal_FIO,
			ServiceType.ServiceType_id,
			1 as IsThis_MedPersonal,
			RTrim(IsNull(ServiceType.ServiceType_Name,'')) as ServiceType_Name,
			VizitType.VizitType_id,
			RTrim(IsNull(VizitType.VizitType_Name,'')) as VizitType_Name,
			PayType.PayType_id,
			RTrim(IsNull(PayType.PayType_Name,'')) as PayType_Name,
			EvnVizitPLStom_Uet,
			case when EvnVizit.MedPersonal_id = :session_medpersonal and EvnVizit.LpuSection_id = :session_curlpusection then 1 else 0 end as IsThis_MedPersonal,
			case
				when exists (select top 1 Evn_id from v_Evn with (nolock) where Evn_pid = EvnVizit.EvnVizitPLStom_id) then 1
				else 0
			end as ChildrensCount
			-- Услуга
			" .
			(in_array($data['session']['region']['nick'], array('pskov', 'ufa')) ?
				",EU.EvnUslugaCommon_id, EU.UslugaComplex_uid, EU.UslugaComplex_Code, EU.UslugaComplex_Name "
				:
				",NULL as EvnUslugaCommon_id, NULL as UslugaComplex_uid, NULL as UslugaComplex_Code, NULL as UslugaComplex_Name ")
			. "
		from v_EvnVizitPLStom EvnVizit with (nolock)
		left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = EvnVizit.LpuSection_id
		left join LpuSectionProfile LpuSectionProfile with (nolock) on LpuSectionProfile.LpuSectionProfile_id=EvnVizit.LpuSectionProfile_id
		left join v_LpuUnit LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
		left join v_MedPersonal MedPersonal with (nolock) on MedPersonal.MedPersonal_id = EvnVizit.MedPersonal_id and MedPersonal.Lpu_id = EvnVizit.Lpu_id
		left join v_ServiceType ServiceType with (nolock) on ServiceType.ServiceType_id = EvnVizit.ServiceType_id
		left join v_VizitType VizitType with (nolock) on VizitType.VizitType_id = EvnVizit.VizitType_id
		left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnVizit.PayType_id
		left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnVizit.Diag_id
		{$joinQuery}
		where {$filter}
		--order by EvnVizitPLStom_setDate";

		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
		{
			return $res->result('array');
			//return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnVizitPLFreeDocumentNodeList($data)
	{
		$params = array();
		$filter = "(1=1) ";		
		
		if ((isset($data['object_id'])) && ($data['object_id']>0)) {
			$filter .= " and EvnXml.Evn_id=".$data['object_id']." and XmlType_id = 2";
		}
		
		$sql = "
		Select 
			EvnXml_id as FreeDocument_id,
			ISNULL(EvnXml_Name, 'Без названия') as EvnXml_Name,
			RTrim(IsNull(convert(varchar,EvnXml_insDT,104),'')) as EvnXml_insDT,
			RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name
		from EvnXml with (nolock)
			left join pmUserCache with (nolock) on pmUserCache.pmUser_id = EvnXml.pmUser_insID
		where {$filter}
		--order by EvnXml_insDT";

		$res = $this->db->query($sql);
		if (is_object($res)) {
			return $res->result('array');
		} else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnDiagPLStomNodeList($data)
	{
		$params = array();
		$filter = "(1=1) ";
		
		if ((isset($data['EvnVizitPLStom_id'])) && ($data['EvnVizitPLStom_id']>0))
		{
			$filter .= " and EDPLS.EvnDiagPLStom_pid = ".$data['EvnVizitPLStom_id'];
		}
		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EDPLS.Person_id=".$data['Person_id'];
		}
		
		$sql = "
		Select 
			EDPLS.Diag_id,
			EDPLS.EvnDiagPLStom_pid,
			EDPLS.Lpu_id,
			EDPLS.EvnDiagPLStom_id,
			RTrim(EDPLS.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnDiagPLStom_setDate as datetime),104),'')) as EvnDiagPLStom_setDT,
			Diag.Diag_id,
			RTRIM(ISNULL(Diag.Diag_Code, '')) as Diag_Code,
			RTRIM(ISNULL(Diag.Diag_Name, '')) as Diag_Name,
			DT.DeseaseType_id,
			RTRIM(ISNULL(DT.DeseaseType_Name, '')) as DeseaseType_Name,
			case
				when exists (select top 1 Evn_id from v_Evn with (nolock) where Evn_pid = EDPLS.EvnDiagPLStom_id) then 1
				else 0
			end as ChildrensCount
		from v_EvnDiagPLStom EDPLS with (nolock)
			left join Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
			left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
		where {$filter}
		--order by EvnDiagPLStom_setDate";

		$res = $this->db->query($sql);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnVizitDispDopNodeList($data)
	{
		// Фильтры: EvnPLDispDop_id, Lpu_id, Server_id
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and EvnVizit.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnVizit.Lpu_id=".$data['session']['lpu_id'];
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and EvnVizit.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and EvnVizit.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['EvnPLDispDop_id'])) && ($data['EvnPLDispDop_id']>0))
		{
			$filter .= " and EvnVizit.EvnVizitDispDop_pid=".$data['EvnPLDispDop_id'];
		}
		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnVizit.Person_id=".$data['Person_id'];
		}
		
		$sql = "
		Select 
			EvnVizit.Diag_id,
			EvnVizit.EvnVizitDispDop_pid,
			EvnVizit.Lpu_id,
			EvnVizit.EvnVizitDispDop_id,
			RTrim(EvnVizit.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnVizitDispDop_setDate as datetime),104),'')) as EvnVizitDispDop_setDT, 
			RTrim(LpuUnit.LpuUnit_Name) as LpuUnit_Name,
			RTrim(LpuSection.LpuSection_Name) as LpuSection_Name,
			RTrim(MedPersonal.Person_FIO) as MedPersonal_FIO,
			RTrim(IsNull(Diag.Diag_Code,'')) as Diag_Code,
			RTrim(IsNull(Diag.Diag_Name,'')) as Diag_Name,
			EvnVizit.DopDispSpec_id,
			RTrim(DopDispSpec.DopDispSpec_Name) as DopDispSpec_Name
			from v_EvnVizitDispDop EvnVizit with (nolock)
			left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = EvnVizit.LpuSection_id
			left join v_LpuUnit LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
			left join v_MedPersonal MedPersonal with (nolock) on MedPersonal.MedPersonal_id = EvnVizit.MedPersonal_id and MedPersonal.Lpu_id = EvnVizit.Lpu_id
			left join DopDispSpec with (nolock) on DopDispSpec.DopDispSpec_id = EvnVizit.DopDispSpec_id
			left join EvnVizitDisp with (nolock) on EvnVizitDisp.EvnVizit_id = EvnVizit.EvnVizitDispDop_id
			left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnVizitDisp.Diag_id
			where {$filter}
			--order by EvnVizitDispDop_setDate";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnReceptNodeList($data)
	{
		// Фильтры: EvnVizitPL_id, Lpu_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnRecept.EvnRecept_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnRecept.EvnRecept_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnRecept.EvnRecept_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and EvnRecept.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnRecept.Lpu_id=".$data['session']['lpu_id'];
		}*/
		
		if ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0))
		{
			$filter .= " and EvnRecept.EvnRecept_pid = :EvnVizit_id";
			$params['EvnVizit_id'] = $data['EvnVizitPL_id'];
		}
		if ((isset($data['Person_id'])) && ($data['Person_id'] > 0)) {
			$filter .= " and EvnRecept.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
			//Видимость VIP -- АртамоновИ.Г. 26.02.2018
			
			$filter .= "
				and (
					EvnRecept.Lpu_id = :Lpu_id or
					not EXISTS(Select top 1 1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and EvnRecept.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null)
				)";
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EvnRecept.EvnRecept_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EvnRecept.EvnRecept_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		$accessType = 'EvnRecept.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params['Lpu_id'] = $data['session']['lpu_id'];
		if (isset($data['user_MedStaffFact_id']))
		{
			$accessType .= ' and EvnRecept.MedPersonal_id = MSF.MedPersonal_id and EvnRecept.LpuSection_id = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("EvnRecept.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnRecept.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
		}
		if ($this->getRegionNick() == 'ufa') {
			$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';		
			$filter .= " and exists (select top 1 PersonPrivilege_id from v_PersonPrivilege with (nolock) where PrivilegeType_id = EvnRecept.PrivilegeType_id and Person_id = EvnRecept.Person_id {$lpuFilter})";
		}

		$sql = "
		Select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnRecept.Diag_id,
			EvnRecept.EvnRecept_pid,
			EvnRecept.Lpu_id,
			EvnRecept_id, 
			RTrim(EvnRecept.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnRecept_setDate as datetime),104),'')) as EvnRecept_setDT, 
			RTrim(EvnRecept_Ser) as EvnRecept_Ser,
			RTrim(EvnRecept_Num) as EvnRecept_Num,
			EvnRecept.Drug_id,
			CASE
				when isnull(EvnRecept.Drug_rlsid, EvnRecept.DrugComplexMnn_id) is not null
				then coalesce(rlsDrugComplexMnn.DrugComplexMnn_RusName,rlsActmatters.RUSNAME,'')
				else isnull(DrugMnn.DrugMnn_Name,Drug.Drug_Name)
			END as Drug_Name,
			cast(EvnRecept_Kolvo as float) as EvnRecept_Kolvo
			{$addQuery}
			from v_EvnRecept EvnRecept with (nolock)
			left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnRecept.Diag_id
			left join Drug with (nolock) on Drug.Drug_id = EvnRecept.Drug_id
			left join DrugMnn with (NOLOCK) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
			left join rls.Drug rlsDrug with (NOLOCK) on rlsDrug.Drug_id = EvnRecept.Drug_rlsid
			left join rls.DrugComplexMnn rlsDrugComplexMnn with (NOLOCK) on rlsDrugComplexMnn.DrugComplexMnn_id = isnull(rlsDrug.DrugComplexMnn_id, EvnRecept.DrugComplexMnn_id)
			left join rls.DrugComplexMnnName MnnName with (NOLOCK) on MnnName.DrugComplexMnnName_id = rlsDrugComplexMnn.DrugComplexMnnName_id
			left join rls.Actmatters rlsActmatters with (NOLOCK) on rlsActmatters.Actmatters_id = MnnName.Actmatters_id
			{$join_msf}
			where {$filter}
			--order by EvnRecept_setDate
		";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnReceptGeneralNodeList($data)
	{
		// Фильтры: EvnVizitPL_id, Lpu_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnRecept.EvnReceptGeneral_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnRecept.EvnReceptGeneral_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnRecept.EvnReceptGeneral_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and EvnRecept.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnRecept.Lpu_id=".$data['session']['lpu_id'];
		}*/
		
		if ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0))
		{
			$filter .= " and EvnRecept.EvnReceptGeneral_pid = :EvnVizit_id";
			$params['EvnVizit_id'] = $data['EvnVizitPL_id'];
		}
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnRecept.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EvnRecept.EvnReceptGeneral_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EvnRecept.EvnReceptGeneral_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		$accessType = 'EvnRecept.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params['Lpu_id'] = $data['session']['lpu_id'];
		if (isset($data['user_MedStaffFact_id']))
		{
			$accessType .= ' and EvnRecept.MedPersonal_id = MSF.MedPersonal_id and EvnRecept.LpuSection_id = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("EvnRecept.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnRecept.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
		}

		if ($this->getRegionNick() == 'ufa') {
			$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
			$filter .= " and exists (select top 1 PersonPrivilege_id from v_PersonPrivilege with (nolock) where PrivilegeType_id = EvnRecept.PrivilegeType_id and Person_id = EvnRecept.Person_id {$lpuFilter})";
		}

		$sql = "
		Select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnRecept.Diag_id,
			EvnRecept.EvnReceptGeneral_pid,
			EvnRecept.Lpu_id,
			EvnRecept.EvnReceptGeneral_id, 
			RTrim(EvnRecept.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnReceptGeneral_begDate as datetime),104),'')) as EvnReceptGeneral_setDT, 
			RTrim(EvnReceptGeneral_Ser) as EvnReceptGeneral_Ser,
			RTrim(EvnReceptGeneral_Num) as EvnReceptGeneral_Num,
			EvnRecept.Drug_id,
			Drugs.DrugNames as Drug_Name,
			null as EvnReceptGeneral_Kolvo
			{$addQuery}
			from v_EvnReceptGeneral EvnRecept with (nolock)
			left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnRecept.Diag_id
			outer apply(
				select STUFF((
							select
								', '+DCMN.DrugComplexMnnName_Name+'('+cast(cast(ERGDL.EvnReceptGeneralDrugLink_Kolvo as float) as varchar)+')'
							from v_EvnReceptGeneralDrugLink ERGDL with (nolock)
							left join v_EvnCourseTreatDrug ECTD (nolock) on ECTD.EvnCourseTreatDrug_id = ERGDL.EvnCourseTreatDrug_id
							left join rls.v_DrugComplexMnn DCM (nolock) on DCM.DrugComplexMnn_id = ECTD.DrugComplexMnn_id
							left join rls.DrugComplexMnnName DCMN (nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
							where ERGDL.EvnReceptGeneral_id = EvnRecept.EvnReceptGeneral_id
						FOR XML PATH ('')),1,1,'') as DrugNames
			) as Drugs
			{$join_msf}
			where {$filter}
			--order by EvnReceptGeneral_setDate
		";
		$res = $this->db->query($sql, $params);
		//echo getDebugSQL($sql,$params);die;
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnReceptWithNoVizitNodeList($data)
	{
		// Фильтры: EvnVizitPL_id, Lpu_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnRecept.EvnRecept_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnRecept.EvnRecept_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnRecept.EvnRecept_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and EvnRecept.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnRecept.Lpu_id=".$data['session']['lpu_id'];
		}*/
		
		$filter .= " and EvnRecept.EvnRecept_pid is null";
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnRecept.Person_id=".$data['Person_id'];
			$params['Person_id'] = $data['Person_id'];
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EvnRecept.EvnRecept_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EvnRecept.EvnRecept_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		$accessType = 'EvnRecept.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params['Lpu_id'] = $data['session']['lpu_id'];
		if (isset($data['user_MedStaffFact_id']))
		{
			$accessType .= ' and EvnRecept.MedPersonal_id = MSF.MedPersonal_id and EvnRecept.LpuSection_id = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("EvnRecept.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnRecept.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
		}
		if ($this->getRegionNick() == 'ufa') {			
			$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';		
			$filter .= " and exists (select top 1 PersonPrivilege_id from v_PersonPrivilege with (nolock) where PrivilegeType_id = EvnRecept.PrivilegeType_id and Person_id = EvnRecept.Person_id {$lpuFilter})";
		}

		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";
		
		$sql = "
		Select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnRecept.Diag_id,
			EvnRecept.EvnRecept_pid,
			EvnRecept.Lpu_id,
			EvnRecept_id, 
			RTrim(EvnRecept.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnRecept_setDate as datetime),104),'')) as EvnRecept_setDT, 
			RTrim(EvnRecept_Ser) as EvnRecept_Ser,
			RTrim(EvnRecept_Num) as EvnRecept_Num,
			EvnRecept.Drug_id,
			CASE
				when isnull(EvnRecept.Drug_rlsid, EvnRecept.DrugComplexMnn_id) is not null
				then coalesce(rlsDrugComplexMnn.DrugComplexMnn_RusName,rlsActmatters.RUSNAME,'')
				else isnull(DrugMnn.DrugMnn_Name,Drug.Drug_Name)
			END as Drug_Name,
			cast(EvnRecept_Kolvo as float) as EvnRecept_Kolvo
			{$addQuery}
			from v_EvnRecept EvnRecept with (nolock)
			left join v_Diag Diag with (NOLOCK) on Diag.Diag_id = EvnRecept.Diag_id
			left join Drug with (NOLOCK) on Drug.Drug_id = EvnRecept.Drug_id
			left join DrugMnn with (NOLOCK) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
			left join rls.Drug rlsDrug with (NOLOCK) on rlsDrug.Drug_id = EvnRecept.Drug_rlsid
			left join rls.DrugComplexMnn rlsDrugComplexMnn with (NOLOCK) on rlsDrugComplexMnn.DrugComplexMnn_id = isnull(rlsDrug.DrugComplexMnn_id, EvnRecept.DrugComplexMnn_id)
			left join rls.DrugComplexMnnName MnnName with (NOLOCK) on MnnName.DrugComplexMnnName_id = rlsDrugComplexMnn.DrugComplexMnnName_id
			left join rls.Actmatters rlsActmatters with (NOLOCK) on rlsActmatters.Actmatters_id = MnnName.Actmatters_id
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnRecept.Lpu_id
			{$join_msf}
			where {$filter}
			--order by EvnRecept_setDate
		";
		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnReceptGeneralWithNoVizitNodeList($data)
	{
		// Фильтры: EvnVizitPL_id, Lpu_id, Server_id
		$params = array();
		$filter = "(1=1) ";
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnRecept.EvnReceptGeneral_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnRecept.EvnReceptGeneral_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnRecept.EvnReceptGeneral_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and EvnRecept.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnRecept.Lpu_id=".$data['session']['lpu_id'];
		}*/
		
		$filter .= " and EvnRecept.EvnReceptGeneral_pid is null";
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnRecept.Person_id=".$data['Person_id'];
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and EvnRecept.EvnReceptGeneral_setDT >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and EvnRecept.EvnReceptGeneral_setDT <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		$accessType = 'EvnRecept.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params['Lpu_id'] = $data['session']['lpu_id'];
		if (isset($data['user_MedStaffFact_id']))
		{
			$accessType .= ' and EvnRecept.MedPersonal_id = MSF.MedPersonal_id and EvnRecept.LpuSection_id = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("EvnRecept.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnRecept.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
		}
		if ($this->getRegionNick() == 'ufa') {
			$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
			$filter .= " and exists (select top 1 PersonPrivilege_id from v_PersonPrivilege with (nolock) where PrivilegeType_id = EvnRecept.PrivilegeType_id and Person_id = EvnRecept.Person_id {$lpuFilter})";
		}

		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";

		$sql = "
		Select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnRecept.Diag_id,
			EvnRecept.EvnReceptGeneral_pid,
			EvnRecept.Lpu_id,
			EvnReceptGeneral_id, 
			RTrim(EvnRecept.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnReceptGeneral_setDate as datetime),104),'')) as EvnReceptGeneral_setDT, 
			RTrim(EvnReceptGeneral_Ser) as EvnReceptGeneral_Ser,
			RTrim(EvnReceptGeneral_Num) as EvnReceptGeneral_Num,
			EvnRecept.Drug_id,
			CASE
				when isnull(EvnRecept.Drug_rlsid, EvnRecept.DrugComplexMnn_id) is not null
				then coalesce(rlsDrugComplexMnn.DrugComplexMnn_RusName,rlsActmatters.RUSNAME,'')
				else isnull(DrugMnn.DrugMnn_Name,Drug.Drug_Name)
			END as Drug_Name,
			cast(EvnReceptGeneral_Kolvo as float) as EvnReceptGeneral_Kolvo
			{$addQuery}
			from v_EvnReceptGeneral EvnRecept with (nolock)
			left join v_Diag Diag with (NOLOCK) on Diag.Diag_id = EvnRecept.Diag_id
			left join Drug with (NOLOCK) on Drug.Drug_id = EvnRecept.Drug_id
			left join DrugMnn with (NOLOCK) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
			left join rls.Drug rlsDrug with (NOLOCK) on rlsDrug.Drug_id = EvnRecept.Drug_rlsid
			left join rls.DrugComplexMnn rlsDrugComplexMnn with (NOLOCK) on rlsDrugComplexMnn.DrugComplexMnn_id = isnull(rlsDrug.DrugComplexMnn_id, EvnRecept.DrugComplexMnn_id)
			left join rls.DrugComplexMnnName MnnName with (NOLOCK) on MnnName.DrugComplexMnnName_id = rlsDrugComplexMnn.DrugComplexMnnName_id
			left join rls.Actmatters rlsActmatters with (NOLOCK) on rlsActmatters.Actmatters_id = MnnName.Actmatters_id
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnRecept.Lpu_id
			{$join_msf}
			where {$filter}
			--order by EvnReceptGeneral_setDate
		";
		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	 * Возвращает список направлений
	 * В зависимости от фильтров:
	 * системные и/или электронные
	 * по типу направления
	 * с привязкой к событию или без
	 */
	function GetEvnDirectionNodeList($data, $excepts = array())
	{
		$filter = "(1=1) ";
		$params = array();
		$addQuery = '';

		$except_ids = array();
		foreach($excepts as $except) {
			if (!empty($except['EvnDirection_id'])) {
				$except_ids[] = $except['EvnDirection_id'];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$filter .= " and EvnDirection.EvnDirection_id not in ({$except_ids})";
		}

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnDirection.EvnDirection_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnDirection.EvnDirection_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnDirection.EvnDirection_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}
		// только не обслуженные, т.е. те направления, назначения по которым не выполнены
		// @todo уточнить определение не обслуженных направлений
		$filterWithoutService = '
		(
			isnull(EvnDirection.EvnStatus_id,0) not in (15) 
			OR exists (
				select top 1 epd.EvnDirection_id from v_EvnPrescrDirection epd with (nolock)
				inner join v_EvnPrescr EP with (NOLOCK) on epd.EvnPrescr_id = EP.EvnPrescr_id
				where EvnDirection.EvnDirection_id = epd.EvnDirection_id
				and isnull(EP.EvnPrescr_IsExec, 1) = 1
			)
		)';
		// только не обслуженные электронные направления без признака "создано автоматически".
		$filterOnlyEl = '(isnull(EvnDirection.EvnDirection_IsAuto, 1) = 1 and '.$filterWithoutService.')';
		// необходимо отображение как направлений, привязанных к случаю - не отмененные, системные и электронные
		// так и непривязанных - только не обслуженные и не отмененные электронные направления без признака "создано автоматически".
		$filterWithParentOrWithout = '(EvnDirection.EvnDirection_pid is not null OR '.$filterOnlyEl.')';
		// основной фильтр
		switch (true) {
			//привязанные к случаю направления - не отмененные, системные и электронные
			case ((isset($data['EvnSection_id'])) && ($data['EvnSection_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnDirection_pid';
				$params['EvnDirection_pid'] = $data['EvnSection_id'];
				break;
			case ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnDirection_pid';
				$params['EvnDirection_pid'] = $data['EvnVizitPL_id'];
				break;
			case ((isset($data['EvnVizitDispDop_id'])) && ($data['EvnVizitDispDop_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnDirection_pid';
				$params['EvnDirection_pid'] = $data['EvnVizitDispDop_id'];
				break;
			case ((isset($data['EvnVizitPLStom_id'])) && ($data['EvnVizitPLStom_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnDirection_pid';
				$params['EvnDirection_pid'] = $data['EvnVizitPLStom_id'];
				break;
			case ((isset($data['EvnPS_id'])) && ($data['EvnPS_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnPS_id';
				$params['EvnPS_id'] = $data['EvnPS_id'];
				break;
			case ((isset($data['EvnPL_id'])) && ($data['EvnPL_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_rid=:EvnDirection_rid';
				$params['EvnDirection_rid'] = $data['EvnPL_id'];
				break;
			case ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_rid=:EvnDirection_rid';
				$params['EvnDirection_rid'] = $data['EvnPLStom_id'];
				break;
			case ((isset($data['EvnPLDispMigrant_id'])) && ($data['EvnPLDispMigrant_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_rid=:EvnDirection_rid';
				$params['EvnDirection_rid'] = $data['EvnPLDispMigrant_id'];
				break;
			case ((isset($data['EvnPLDispDriver_id'])) && ($data['EvnPLDispDriver_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_rid=:EvnDirection_rid';
				$params['EvnDirection_rid'] = $data['EvnPLDispDriver_id'];
				break;

			case (!empty($data['Person_id']) && $data['Person_id']>0 && !empty($data['DirType_id']) && $data['DirType_id']>0 && !empty($data['type']) && 1==$data['type']):
				// с фильтром по типу направления при отображении дерева в ЭМК в виде "по событиям"
				$filter .= ' and EvnDirection.Person_id = :Person_id and EvnDirection.DirType_id=:DirType_id and '. $filterWithParentOrWithout;
				$params['DirType_id'] = $data['DirType_id'];
				$params['Person_id'] = $data['Person_id'];
				break;

			case (!empty($data['Person_id']) && $data['Person_id']>0 && !empty($data['type']) && 1==$data['type']):
				// При отображении дерева в ЭМК в виде "по событиям" только с фильтром по человеку
				$filter .= ' and EvnDirection.Person_id = :Person_id and '.$filterWithParentOrWithout;
				$params['Person_id'] = $data['Person_id'];
				break;

			default:
				// только с фильтром по человеку
				if (empty($data['Person_id']))
				{
					return array();
				}
				// только не обслуженные и не отмененные электронные направления без признака "создано автоматически".
				$filter .= ' and EvnDirection.Person_id = :Person_id and EvnDirection.EvnDirection_pid is null and '. $filterOnlyEl;
				$params['Person_id'] = $data['Person_id'];
				break;
		}

		// В дереве в принципе не отображать отмененные/отклоненные, не надо перегружать дерево
		$filter .= " and EvnDirection.EvnDirection_failDT is null
		and EvnDirection.DirFailType_id is null
		and isnull(EvnDirection.EvnStatus_id,0) not in (12,13)
		and EQ.EvnQueue_failDT is null
		";
		
		/*if(isset($data['session']['lpu_id'])){
			$params['Lpu_id'] = $data['session']['lpu_id'];
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		$params['Lpu_id'] = $data['session']['lpu_id'];
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = EvnDirection.Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
		}
		$sql = "
			Select 
				EvnDirection.Lpu_id,
				EvnDirection.Diag_id,
				EvnDirection.EvnDirection_pid,
				EvnDirection.EvnDirection_id,
				EvnDirection.EvnDirection_Num, 
				DirType.DirType_id,
				Rtrim(DirType.DirType_Name) as DirType_Name,
				RTrim(IsNull(convert(varchar,cast(EvnDirection.EvnDirection_setDate as datetime),104),'')) as EvnDirection_setDT, 
				RTrim(Lpu.Lpu_Nick) as Lpu_Nick,
				LpuUnit.LpuUnit_id,
				Rtrim(LpuUnit.LpuUnit_Name) as LpuUnit_Name,
				LpuSectionProfile.LpuSectionProfile_id,
				RTrim(LpuSectionProfile.LpuSectionProfile_Code) +'.'+ RTrim(LpuSectionProfile.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				case 
					when ttms.TimetableMedService_id is not null
						then convert(varchar(10), ttms.TimetableMedService_begTime, 104)+' '+convert(varchar(5), ttms.TimetableMedService_begTime, 108)
					when ttg.TimetableGraf_id is not null
						then convert(varchar(10), ttg.TimetableGraf_begTime, 104)+' '+convert(varchar(5), ttg.TimetableGraf_begTime, 108)
					when tts.TimetableStac_id is not null and ttp.TimetablePar_id is null
						then convert(varchar(10), tts.TimetableStac_setDate, 104)
					when (ttp.TimetablePar_id is not null and tts.TimetableStac_id is null)
						then convert(varchar(10), ttp.TimetablePar_begTime, 104)+' '+convert(varchar(5), ttp.TimetablePar_begTime, 108)
					when (ttp.TimetablePar_id is not null and tts.TimetableStac_id is not null)
						then convert(varchar(10), ttp.TimetablePar_begTime, 104)+' '+convert(varchar(5), ttp.TimetablePar_begTime, 108) +'На койку:'+convert(varchar(10), tts.TimetableStac_setDate, 104)
					when EVK.EvnVK_id is not null
						then convert(varchar(10), EVK.EvnVK_setDate, 104)
					when EQ.EvnQueue_id is not null
						then case when EUP.EvnUslugaPar_setDT is null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'') else convert(varchar(10), EUP.EvnUslugaPar_setDT, 104)+' '+convert(varchar(5), EUP.EvnUslugaPar_setDT, 108) end
					when coalesce(TTG.TimetableGraf_id, TTMS.TimetableMedService_id, TTS.TimetableStac_id, EQ.EvnQueue_id) is null
						then 'Направление выписано ' + convert(varchar(10), EvnDirection.EvnDirection_setDT, 104)
					else ''
				end as RecDate,
				convert(varchar(10), EvnDirection.EvnDirection_statusDate, 104) as EvnDirection_statusDate,
				EvnDirection.EvnStatus_id,
				EvnStatus.EvnStatus_Name
				{$addQuery}
			from v_EvnDirection_all EvnDirection with (nolock)
			left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnDirection.Diag_id
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=EvnDirection.Lpu_did
			left join v_LpuUnit LpuUnit with (nolock) on LpuUnit.LpuUnit_id=EvnDirection.LpuUnit_did
			left join v_DirType DirType with (nolock) on DirType.DirType_id=EvnDirection.DirType_id
			left join v_LpuSectionProfile LpuSectionProfile with (nolock) on LpuSectionProfile.LpuSectionProfile_id=EvnDirection.LpuSectionProfile_id
			outer apply (
				Select top 1 TimetableGraf_id, MedStaffFact_id, TimetableGraf_begTime from v_TimetableGraf_lite TTG with (nolock) where TTG.EvnDirection_id = EvnDirection.EvnDirection_id
			) TTG
			 -- службы и параклиника
			outer apply (
				Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = EvnDirection.EvnDirection_id
			) TTMS
			 -- стац
			outer apply (
				Select top 1 TimetableStac_id, LpuSection_id, TimetableStac_setDate from v_TimetableStac_lite TTS with (nolock) where TTS.EvnDirection_id = EvnDirection.EvnDirection_id
			) TTS
			left join TimetablePar ttp with (nolock) on EvnDirection.TimetablePar_id = ttp.TimetablePar_id
			 -- очередь
			outer apply (
				Select top 1
					EQ.EvnQueue_id,
					EQ.EvnQueue_failDT,
					EQ.EvnQueue_setDate
				from 
					v_EvnQueue EQ (nolock)
				where EQ.EvnDirection_id = EvnDirection.EvnDirection_id
					and EQ.EvnQueue_recDT is null
			) EQ
			-- заказанная услуга для параклиники
			outer apply(
				select top 1
					EvnUslugaPar_setDT
				from
					v_EvnUslugaPar EUP with (NOLOCK)
				where
					EvnDirection_id = EvnDirection.EvnDirection_id
			) EUP
			-- назначение на ВК в очереди
			outer apply(
				select top 1
					EVK.EvnVK_id,
					Evn_setDT as EvnVK_setDate
				from
					EvnVK EVK with(nolock)
					inner join Evn with(nolock) on EVK.EvnVK_id = Evn.Evn_id and Evn.Evn_deleted = 1 
					inner join v_EvnPrescrVK EPVK with(nolock) on EPVK.EvnPrescrVK_id = EVK.EvnPrescrVK_id
				where
					EPVK.EvnQueue_id = EQ.EvnQueue_id
			) EVK
			left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = EvnDirection.EvnStatus_id
			where {$filter}
			--order by EvnDirection.EvnDirection_setDate DESC
		";
		//throw new Exception(getDebugSQL($sql, $params));
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возвращает список направлений
	 * В зависимости от фильтров:
	 * системные и/или электронные
	 * по типу направления
	 * с привязкой к событию или без
	 */
	function GetEvnDirectionNodeListLis($data) {
		$this->load->swapi('lis');
		$resp = $this->lis->GET('EvnDirection/NodeList', $data, 'list');
		if (!$this->isSuccessful($resp)) {
			return false;
		}
		if (empty($resp)) {
			return array();
		}

		$rows = [];
		foreach($resp as $item) {
			$fields = [];
			foreach($item as $key => $value) {
				switch(true) {
					case ($value === null):
						$fields[] = "null as {$key}";
						break;
					case is_string($value):
						$fields[] = "'{$value}' as {$key}";
						break;
					default:
						$fields[] = "{$value} as {$key}";
						break;
				}
			}
			$rows[] = "select ".implode(",", $fields);
		}
		$rows = implode("\nunion\n", $rows);

		$filter = '1=1';
		$params = array();

		$params['Lpu_id'] = $data['session']['lpu_id'];
		$filter .= "
		and ( row_list.Lpu_id = :Lpu_id or
			not EXISTS(Select * from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = row_list.Person_id and row_list.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null)
		)";

		$query = "
			with row_list as (
				{$rows}
			)
			select
				row_list.*
			from row_list
			where {$filter}
		";

		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if ($resp === false) {
			return false;
		}

		return swFilterResponse::filterNotViewDiag($resp, $data);
	}

	/**
	 * Установка фильтров
	 */
	private function filterCommonEvnUsluga($data, &$from_clause, &$where_clause)
	{
		if ( in_array($data['session']['region']['nick'], array('ufa', 'pskov', 'ekb')) ) {
			$where_clause .= "
				and ISNULL(EvnUsluga.EvnUsluga_IsVizitCode, 1) = 1
			";
		}
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnUslugaNodeList($data)
	{
		// Фильтры: EvnVizitPL_id, EvnPL_id, Person_id
		$params = array();
		$where_clause = "(1=1) ";
		$where_clause_t = "(1=1) ";
		$from_clause = '';
		if (!empty($data['Person_id'])) {
			$params['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['EvnVizitPL_id'])) {
			$params['Evn_id'] = $data['EvnVizitPL_id'];
		}
		if (!empty($data['EvnPL_id'])) {
			$params['Evn_id'] = $data['EvnPL_id'];
		}
		if (!empty($data['EvnPS_id'])) {
			$params['Evn_id'] = $data['EvnPS_id'];
		}
		if (isset($params['Evn_id'])) {
			$where_clause .= " and EvnUsluga.EvnUsluga_pid = :Evn_id";
			$where_clause_t .= " and EvnUsluga.EvnUslugaTelemed_pid = :Evn_id";
		} else if (isset($params['Person_id'])) {
			$where_clause .= " and EvnUsluga.Person_id = :Person_id";
			$where_clause_t .= " and EvnUsluga.Person_id = :Person_id";
		} else {
			return false;
		}
		
		$where_clause .= " and EvnUsluga.EvnClass_SysNick not in ('EvnUslugaPar','EvnUslugaTelemed')";
		$where_clause .= " and EvnUsluga.EvnUsluga_setDT is not null";

		$this->filterCommonEvnUsluga($data, $from_clause, $where_clause);

		$sql = "
		SELECT top 100
			EvnUsluga.EvnUsluga_pid,
			null as Diag_id,
			EvnUsluga.Lpu_id,
			EvnUsluga.EvnClass_SysNick,
			v_Evn.EvnClass_SysNick as Parent_EvnClass_SysNick,
			EvnUsluga.EvnUsluga_id,
			RTrim(EvnUsluga.EvnClass_Name) as EvnClass_Name,
			convert(varchar,EvnUsluga.EvnUsluga_setDate,104) as EvnUsluga_setDT,
			Usluga.Usluga_id,
			ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code,
			ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name,
			PayType.PayType_SysNick,
			EvnUsluga.EvnUsluga_Kolvo
			from v_EvnUsluga EvnUsluga with (nolock)
			left join v_Evn with (nolock) on v_Evn.Evn_id = EvnUsluga.EvnUsluga_pid
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EvnUsluga.Usluga_id
			left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EvnUsluga.UslugaComplex_id
			{$from_clause}
			left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnUsluga.PayType_id
			where {$where_clause}
		";
		/*
		union all
		SELECT top 100
			EvnUsluga.EvnUslugaTelemed_pid as EvnUsluga_pid,
			EvnUsluga.Diag_id,
			EvnUsluga.Lpu_id,
			'EvnUslugaTelemed' as EvnClass_SysNick,
			v_Evn.EvnClass_SysNick as Parent_EvnClass_SysNick,
			EvnUsluga.EvnUslugaTelemed_id as EvnUsluga_id,
			RTrim(EvnUsluga.EvnClass_Name) as EvnClass_Name,
			convert(varchar,EvnUsluga.EvnUslugaTelemed_setDate,104) as EvnUsluga_setDT,
			null as Usluga_id,
			v_Diag.Diag_Code as Usluga_Code,
			v_Diag.Diag_Name as Usluga_Name,
			PayType.PayType_SysNick,
			EvnUsluga.EvnUslugaTelemed_Kolvo as EvnUsluga_Kolvo
			from v_EvnUslugaTelemed EvnUsluga with (nolock)
			left join v_Evn with (nolock) on v_Evn.Evn_id = EvnUsluga.EvnUslugaTelemed_pid
			left join v_Diag with (nolock) on v_Diag.Diag_id = EvnUsluga.Diag_id
			left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnUsluga.PayType_id
			where {$where_clause_t}
		*/
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnUslugaStomNodeList($data)
	{
		// Фильтры: EvnVizitPLStom_id, EvnPLStom_id
		$params = array();
		$where_clause = "(1=1) ";
		$from_clause = '';
		if (!empty($data['EvnVizitPLStom_id'])) {
			$params['Evn_id'] = $data['EvnVizitPLStom_id'];
		}
		if (!empty($data['EvnPLStom_id'])) {
			$params['Evn_id'] = $data['EvnPLStom_id'];
		}
		if (isset($params['Evn_id'])) {
			$where_clause .= " and EvnUsluga.EvnUsluga_pid = :Evn_id";
		} else {
			return false;
		}

		$this->filterCommonEvnUsluga($data, $from_clause, $where_clause);

		$where_clause .= " and EvnUsluga.EvnUsluga_setDT is not null";

		$sql = "
		SELECT top 100
			EvnUsluga.EvnUsluga_pid as EvnUslugaStom_pid,
			--EvnUsluga.Diag_id,
			EvnUsluga.Lpu_id,
			EvnUsluga.EvnClass_SysNick,
			v_Evn.EvnClass_SysNick as Parent_EvnClass_SysNick,
			EvnUsluga.EvnUsluga_id as EvnUslugaStom_id,
			RTrim(EvnUsluga.EvnClass_Name) as EvnClass_Name,
			convert(varchar,EvnUsluga.EvnUsluga_setDate,104) as EvnUslugaStom_setDT,
			Usluga.Usluga_id,
			ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code,
			ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name,
			PayType.PayType_SysNick,
			EvnUsluga.EvnUsluga_Kolvo as EvnUslugaStom_Kolvo
			from v_EvnUsluga EvnUsluga with (nolock)
			left join v_Evn with (nolock) on v_Evn.Evn_id = EvnUsluga.EvnUsluga_pid
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EvnUsluga.Usluga_id
			left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EvnUsluga.UslugaComplex_id
			{$from_clause}
			left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnUsluga.PayType_id
			where {$where_clause}
			--order by EvnUsluga.EvnUsluga_setDate
			";
		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnUslugaDispDopNodeList($data)
	{
		// Фильтры: EvnVizitDispDop_id, Lpu_id, Server_id
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and EvnUslugaDispDop.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnUslugaDispDop.Lpu_id=".$data['session']['lpu_id'];
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and EvnUslugaDispDop.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and EvnUslugaDispDop.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['EvnVizitDispDop_id'])) && ($data['EvnVizitDispDop_id']>0))
		{
			$filter .= " and EvnUslugaDispDop.EvnUslugaDispDop_pid=".$data['EvnVizitDispDop_id'];
		}
		
		if ((isset($data['EvnPLDispDop_id'])) && ($data['EvnPLDispDop_id']>0))
		{
			$filter .= " and EvnUslugaDispDop.EvnUslugaDispDop_rid=".$data['EvnPLDispDop_id'];
		}
		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and EvnUslugaDispDop.Person_id=".$data['Person_id'];
		}
		
		$sql = "
		Select 
			EvnUslugaDispDop_id, 
			RTrim(EvnUslugaDispDop.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnUslugaDispDop_setDate as datetime),104),'')) as EvnUslugaDispDop_setDT, 
			Usluga.Usluga_id,
			RTrim(Usluga.Usluga_Code) as Usluga_Code,
			RTrim(Usluga.Usluga_Name) as Usluga_Name,
			PayType.PayType_SysNick,
			EvnUslugaDispDop_Kolvo
			from v_EvnUslugaDispDop EvnUslugaDispDop with (nolock)
			left join Usluga with (nolock) on Usluga.Usluga_id = EvnUslugaDispDop.Usluga_id
			left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnUslugaDispDop.PayType_id
			where {$filter}
			--order by EvnUslugaDispDop_setDate
			";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnUslugaParNodeList($data, $excepts = array())
	{
		// Фильтры: Person_id, Lpu_id, Server_id
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$data['Lpu_id']$filter .= " and EvnUslugaPar.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnUslugaPar.Lpu_id=".$data['session']['lpu_id'];
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and EvnUslugaPar.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and EvnUslugaPar.Server_id=".$data['session']['server_id'];
		}
		*/

		$accessType = 'EvnUslugaPar.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params =  array(
			'Lpu_id' => (empty($data['Lpu_id'])?$data['session']['lpu_id']:$data['Lpu_id'])
		);
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= ' and EvnUslugaPar.MedPersonal_id = MSF.MedPersonal_id and EvnUslugaPar.LpuSection_uid = MSF.LpuSection_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		} else if (isset($data['user_MedStaffFact_id'])) {
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		if (isset($params['user_MedStaffFact_id'])) {
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
		}

		$filter = '(1=1) ';
		$filterWith = '';
		$addQuery = '';
		$filter .= ' and ISNULL(ED.DirType_id, 0) != 11 ';

		$except_ids = array();
		foreach($excepts as $except) {
			if (!empty($except['EvnUslugaPar_id'])) {
				$except_ids[] = $except['EvnUslugaPar_id'];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$filter .= " and EvnUslugaPar.EvnUslugaPar_id not in ({$except_ids})";
		}
		
		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnUslugaPar.EvnUslugaPar_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnUslugaPar.EvnUslugaPar_IsArchive, 1) = 1";
				$filterWith .= " and ISNULL(EvnUslugaPar.Evn_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnUslugaPar.EvnUslugaPar_IsArchive, 1) = 2";
				$filterWith .= " and ISNULL(EvnUslugaPar.Evn_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}
		
		

		switch (true) {
			case ((isset($data['EvnSection_id'])) && ($data['EvnSection_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid=:EvnSection_id';
				$filterWith .= " and EvnUslugaPar.Evn_pid=:EvnSection_id";
				$params['EvnSection_id'] = $data['EvnSection_id'];
				break;
			case ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid=:EvnVizitPL_id';
				$filterWith .= " and EvnUslugaPar.Evn_pid=:EvnVizitPL_id";
				$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
				break;
			case ((isset($data['EvnVizitPLStom_id'])) && ($data['EvnVizitPLStom_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid=:EvnVizitPLStom_id';
				$filterWith .= " and EvnUslugaPar.Evn_pid=:EvnVizitPLStom_id";
				$params['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
				break;
			case ((isset($data['EvnPS_id'])) && ($data['EvnPS_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid=:EvnPS_id';
				$filterWith .= " and EvnUslugaPar.Evn_pid=:EvnPS_id";
				$params['EvnPS_id'] = $data['EvnPS_id'];
				break;
			case ((isset($data['EvnPL_id'])) && ($data['EvnPL_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_rid=:EvnPL_id';
				$filterWith .= " and EvnUslugaPar.Evn_rid=:EvnPL_id";
				$params['EvnPL_id'] = $data['EvnPL_id'];
				break;
			case ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id']>0)):
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_rid=:EvnPLStom_id';
				$filterWith .= " and EvnUslugaPar.Evn_rid=:EvnPLStom_id";
				$params['EvnPLStom_id'] = $data['EvnPLStom_id'];
				break;
			case ((isset($data['EvnPLDispMigrant_id'])) && ($data['EvnPLDispMigrant_id']>0)):
				$filter .= ' and (EvnUP.Evn_id = :EvnPLDispMigrant_id OR EvnDP.Evn_id = :EvnPLDispMigrant_id)';
				$params['EvnPLDispMigrant_id'] = $data['EvnPLDispMigrant_id'];
				break;
			case ((isset($data['EvnPLDispDriver_id'])) && ($data['EvnPLDispDriver_id']>0)):
				$filter .= ' and (EvnUP.Evn_id = :EvnPLDispDriver_id OR EvnDP.Evn_id = :EvnPLDispDriver_id)';
				$params['EvnPLDispDriver_id'] = $data['EvnPLDispDriver_id'];
				break;
			case (!empty($data['type']) && 1==$data['type']):
				// При отображении дерева в ЭМК в виде "по событиям"
				// необходимо отображение всех результатов исследований,
				// в том числе и введенных в рамках конкретных случаев #33176
				break;
			default:
				$filter .= ' and EvnUslugaPar.EvnUslugaPar_pid is null and ((EvnUP.Evn_id is null and EvnDP.Evn_id is null) or (ISNULL(EvnUP.EvnClass_id, 0) <> 189 and ISNULL(EvnDP.EvnClass_id, 0) <> 189))';
				$filterWith .= " and EvnUslugaPar.Evn_pid is null";
				break;
		}
		
		// todo: Вообще не уверен, что этот параметр не должен быть обязательным, поэтому по идее $filterWith должен быть всегда
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= ' and EvnUslugaPar.Person_id=:Person_id';
			$filterWith .= ' and EvnUslugaPar.Person_id=:Person_id';
			$params['Person_id'] = $data['Person_id'];
		}

		$filter .= ' and EvnUslugaPar.EvnUslugaPar_setDate is not null';

		$filter .= " and ISNULL(EvnUP.EvnClass_SysNick, '') != 'EvnUslugaPar'"; // тесты проб не надо отображать
		$filterAccessRights = getAccessRightsTestFilter('UslugaComplex.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UT.UslugaComplex_id', false, true);

		$existEvnSection = "";
		if (!empty($params['user_MedStaffFact_id'])) {
			$existEvnSection = "exists (
				select top 1 
					es.EvnSection_id 
				from 
					v_EvnSection es (nolock) 
				where 
					es.EvnSection_id = EvnUslugaPar.EvnUslugaPar_pid 
					and es.EvnSection_setDT <= EvnUslugaPar.EvnUslugaPar_setDT 
					and (es.EvnSection_disDT is null or es.EvnSection_disDT >= EvnUslugaPar.EvnUslugaPar_setDT) 
					and es.MedStaffFact_id = :user_MedStaffFact_id
			) or ";
		}
		$filter .= " and (
			{$existEvnSection} (".((!empty($filterAccessRights))?$filterAccessRights."and UCp.UslugaComplex_id is null)":'1=1)');

		if (!empty($params['user_MedStaffFact_id'])){
			$filter .= " or ED.MedPersonal_id = MSF.MedPersonal_id ";
		}

		$filter .= " )";
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		$filter .= " 
		and (  Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";
		
		$with = "";
		
		if (strlen($filterWith)>0) {
			$with = 
			"
			with EUP as (
			Select 
				EvnUslugaPar.Lpu_id,
				EvnUslugaPar.Evn_id as EvnUslugaPar_id
				from v_Evn EvnUslugaPar with (nolock)
				where 
					EvnUslugaPar.Evn_setDate is not null
					".$filterWith."
					and EvnClass_id = 47
			)
			";
			$filter .= "
				and exists (Select top 1 * from EUP where EvnUslugaPar.EvnUslugaPar_id = EUP.EvnUslugaPar_id )";
		}

		$sql = "
		{$with}
		Select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnUslugaPar.Lpu_id,
			EvnUslugaPar.EvnUslugaPar_id,
			RTrim(EvnUslugaPar.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnUslugaPar_setDate as datetime),104),'')) as EvnUslugaPar_setDT, 
			RTrim(IsNull(convert(varchar,EvnUslugaPar_setDate,104),''))+' '+RTrim(IsNull(EvnUslugaPar_setTime,'')) as sortDate,
			Usluga.Usluga_id,
			isnull(UslugaComplex.UslugaComplex_Code, Usluga.Usluga_Code) as Usluga_Code,
			coalesce(ucms.UslugaComplex_Name, UslugaComplex.UslugaComplex_Name, Usluga.Usluga_Name) as Usluga_Name,
			PayType.PayType_SysNick
			,isnull(MP.Person_FIO,'') as MedPersonal_Fio
			,isnull(LS.LpuSection_Name,'') as LpuSection_Name
			,isnull(Lpu.Lpu_Nick,'') as Lpu_Name
			,case
				when exists (
					select vMST.MedServiceType_SysNick
					from dbo.v_MedService (nolock) vMS
					inner join dbo.v_MedServiceType (nolock) vMST on vMST.MedServiceType_id = vMS.MedServiceType_id
					where vMS.MedService_id = ELS.MedService_id and vMST.MedServiceType_SysNick = 'microbiolab'
				) THEN 2
				ELSE 1
			end as isMicroLab
			{$addQuery}
			from v_EvnUslugaPar EvnUslugaPar with (nolock)
			left join v_EvnDirection_all EvD with (nolock) on EvnUslugaPar.EvnDirection_id = EvD.EvnDirection_id
			left join v_Evn EvnUP with (nolock) on EvnUP.Evn_id = EvnUslugaPar.EvnUslugaPar_pid
			left join v_Evn EvnDP with (nolock) on EvnDP.Evn_id = EvD.EvnDirection_pid
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EvnUslugaPar.Usluga_id
			left join v_UslugaComplex UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = EvnUslugaPar.UslugaComplex_id
			left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnUslugaPar.PayType_id
			left join v_EvnLabSample ELS with (nolock) on ELS.EvnLabSample_id = EvnUslugaPar.EvnLabSample_id
			left join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EvnUslugaPar.EvnDirection_id
			left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = ELS.MedService_id and UCMS.UslugaComplex_id = UslugaComplex.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
			outer apply (
				select top 1 MP.Person_FIO from v_MedStaffFact MP with (nolock)
				where MP.LpuSection_id = isnull(EvnUslugaPar.LpuSection_uid,ELS.LpuSection_aid)
					AND MP.MedPersonal_id = isnull(EvnUslugaPar.MedPersonal_id,ELS.MedPersonal_aid)
			) MP
			outer apply (
				select top 1
					UT.UslugaComplex_id
				from
					v_UslugaTest UT with (nolock)
				where
					UT.UslugaTest_pid = EvnUslugaPar.EvnUslugaPar_id
					".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
			) as UCp
			left join v_LpuSection LS with (nolock) on LS.LpuSection_id = isnull(EvnUslugaPar.LpuSection_uid,ELS.LpuSection_aid)
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(EvnUslugaPar.Lpu_uid,ELS.Lpu_aid,EvnUslugaPar.Lpu_id)
			left join v_EvnDirection_all ED on ED.EvnDirection_id = EvnUslugaPar.EvnDirection_id
			{$join_msf}
			where {$filter}
			--order by EvnUslugaPar_setDate
		";

		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * @param array $data
	 * @param array $excepts
	 * @return array|bool
	 */
	function GetEvnUslugaParNodeListLis($data, $excepts = array())
	{
		$data['except_ids'] = array();
		foreach($excepts as $except) {
			if (!empty($except['EvnUslugaPar_id'])) {
				$data['except_ids'][] = $except['EvnUslugaPar_id'];
			}
		}

		$this->load->swapi('lis');
		$resp = $this->lis->GET('EvnUsluga/ParNodeList', $data, 'list');
		if (!$this->isSuccessful($resp)) {
			return false;
		}
		if (empty($resp)) {
			return array();
		}

		$rows = [];
		foreach($resp as $item) {
			$fields = [];
			foreach($item as $key => $value) {
				switch(true) {
					case ($value === null):
						$fields[] = "null as {$key}";
						break;
					case is_string($value):
						$fields[] = "'{$value}' as {$key}";
						break;
					default:
						$fields[] = "{$value} as {$key}";
						break;
				}
			}
			$rows[] = "select ".implode(",", $fields);
		}
		$rows = implode("\nunion\n", $rows);

		$params =  [];
		$filter = "1=1";

		if (isset($data['Person_id']) && $data['Person_id'] > 0) {
			$params['Person_id'] = $data['Person_id'];
		}

		$accessType = 'row_list.Lpu_id = :Lpu_id';
		$params['Lpu_id'] = (empty($data['Lpu_id'])?$data['session']['lpu_id']:$data['Lpu_id']);

		$join_msf = '';
		if (isset($data['session']['CurMedStaffFact_id'])) {
			$accessType .= ' and row_list.MedPersonal_id = MSF.MedPersonal_id and row_list.LpuSection_uid = MSF.LpuSection_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		} else if (isset($data['user_MedStaffFact_id'])) {
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		if (isset($params['user_MedStaffFact_id'])) {
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
		}

		$filterAccessRights = getAccessRightsTestFilter('row_list.UslugaComplex_id');

		$existEvnSection = "";
		if (!empty($params['user_MedStaffFact_id'])) {
			$existEvnSection = "exists (
				select * from v_EvnSection es 
				where es.EvnSection_id = row_list.EvnUslugaPar_pid and es.EvnSection_setDT <= row_list.EvnUslugaPar_setDT 
				and (es.EvnSection_disDT is null or es.EvnSection_disDT >= row_list.EvnUslugaPar_setDT) and es.MedStaffFact_id = :user_MedStaffFact_id
			) or ";
		}
		$filter .= " and (
			{$existEvnSection} (".((!empty($filterAccessRights))?$filterAccessRights."and row_list.UCp_UslugaComplex_id is null)":'1=1)');

		if (!empty($params['user_MedStaffFact_id'])){
			$filter .= " or row_list.ED_MedPersonal_id = MSF.MedPersonal_id ";
		}

		$filter .= " )";

		$filter .= " 
		and (  row_list.Lpu_id = :Lpu_id or
			not EXISTS(Select * from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and row_list.Lpu_id = vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";

		$query = "
			with row_list as (
				{$rows}
			)
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				coalesce(MP.Person_FIO,'') as MedPersonal_Fio,
				row_list.*
			from row_list
			outer apply (
				select top 1 MP.Person_FIO from v_MedStaffFact MP
				where MP.LpuSection_id = coalesce(row_list.LpuSection_uid,row_list.LpuSection_aid)
					AND MP.MedPersonal_id = coalesce(row_list.MedPersonal_id,row_list.MedPersonal_aid)
			) MP
			{$join_msf}
			where {$filter}
		";

		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if ($resp === false) {
			return false;
		}

		return swFilterResponse::filterNotViewDiag($resp, $data);
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnUslugaCommonNodeList($data)
	{

		$accessType = 'EvnUslugaCommon.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params =  array(
			'Lpu_id' => (empty($data['Lpu_id'])?$data['session']['lpu_id']:$data['Lpu_id'])
		);
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= ' and EvnUslugaCommon.MedPersonal_id = MSF.MedPersonal_id and EvnUslugaCommon.LpuSection_uid = MSF.LpuSection_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		} else if (isset($data['user_MedStaffFact_id'])) {
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		if (isset($params['user_MedStaffFact_id'])) {
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
		}

		$filter = '(1=1) ';
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnUslugaCommon.EvnUslugaCommon_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnUslugaCommon.EvnUslugaCommon_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnUslugaCommon.EvnUslugaCommon_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		switch (true) {
			case ((isset($data['EvnSection_id'])) && ($data['EvnSection_id']>0)):
				$filter .= ' and EvnUslugaCommon.EvnUslugaCommon_pid=:EvnSection_id';
				$params['EvnSection_id'] = $data['EvnSection_id'];
				break;
			case ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0)):
				$filter .= ' and EvnUslugaCommon.EvnUslugaCommon_pid=:EvnVizitPL_id';
				$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
				break;
			case ((isset($data['EvnVizitPLStom_id'])) && ($data['EvnVizitPLStom_id']>0)):
				$filter .= ' and EvnUslugaCommon.EvnUslugaCommon_pid=:EvnVizitPLStom_id';
				$params['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
				break;
			case ((isset($data['EvnPS_id'])) && ($data['EvnPS_id']>0)):
				$filter .= ' and EvnUslugaCommon.EvnUslugaCommon_pid=:EvnPS_id';
				$params['EvnPS_id'] = $data['EvnPS_id'];
				break;
			case ((isset($data['EvnPL_id'])) && ($data['EvnPL_id']>0)):
				$filter .= ' and EvnUslugaCommon.EvnUslugaCommon_rid=:EvnPL_id';
				$params['EvnPL_id'] = $data['EvnPL_id'];
				break;
			case ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id']>0)):
				$filter .= ' and EvnUslugaCommon.EvnUslugaCommon_rid=:EvnPLStom_id';
				$params['EvnPLStom_id'] = $data['EvnPLStom_id'];
				break;
			case (!empty($data['type']) && 1==$data['type']):
				// При отображении дерева в ЭМК в виде "по событиям"
				// необходимо отображение всех результатов исследований,
				// в том числе и введенных в рамках конкретных случаев #33176
				break;
			default:
				$filter .= ' and EvnUslugaCommon.EvnUslugaCommon_pid is null';
				break;
		}

		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= ' and EvnUslugaCommon.Person_id=:Person_id';
			$params['Person_id'] = $data['Person_id'];
		}

		$filter .= ' and EvnUslugaCommon.EvnUslugaCommon_setDate is not null';

		$filter .= " and ISNULL(Evn.EvnClass_SysNick, '') != 'EvnUslugaCommon'"; // тесты проб не надо отображать
		$filterAccessRights = getAccessRightsTestFilter('UslugaComplex.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UslugaComplex_id', false, true);

		$existEvnSection = "";
		if (!empty($params['user_MedStaffFact_id'])) {
			$existEvnSection = "exists (
				select top 1 es.EvnSection_id from v_EvnSection es (nolock) where es.EvnSection_id = EvnUslugaCommon.EvnUslugaCommon_pid and es.EvnSection_setDT <= EvnUslugaCommon.EvnUslugaCommon_setDT and (es.EvnSection_disDT is null or es.EvnSection_disDT >= EvnUslugaCommon.EvnUslugaCommon_setDT) and es.MedStaffFact_id = :user_MedStaffFact_id
			) or ";
		}
		$filter .= " and (
			{$existEvnSection} (".((!empty($filterAccessRights))?$filterAccessRights."and UCp.UslugaComplex_id is null)":'1=1)');

		if (!empty($params['user_MedStaffFact_id'])){
			$filter .= " or ED.MedPersonal_id = MSF.MedPersonal_id ";
		}

		$filter .= " )";
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/

		$sql = "
		Select
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnUslugaCommon.Lpu_id,
			EvnUslugaCommon.EvnUslugaCommon_id,
			RTrim(EvnUslugaCommon.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnUslugaCommon_setDate as datetime),104),'')) as EvnUslugaCommon_setDT,
			RTrim(IsNull(convert(varchar,EvnUslugaCommon_setDate,104),''))+' '+RTrim(IsNull(EvnUslugaCommon_setTime,'')) as sortDate,
			Usluga.Usluga_id,
			isnull(UslugaComplex.UslugaComplex_Code, Usluga.Usluga_Code) as Usluga_Code,
			coalesce(ucms.UslugaComplex_Name, UslugaComplex.UslugaComplex_Name, Usluga.Usluga_Name) as Usluga_Name,
			PayType.PayType_SysNick
			,isnull(MP.Person_FIO,'') as MedPersonal_Fio
			,isnull(LS.LpuSection_Name,'') as LpuSection_Name
			,isnull(Lpu.Lpu_Nick,'') as Lpu_Name
			{$addQuery}
			from v_EvnUslugaCommon EvnUslugaCommon with (nolock)
			left join v_Evn Evn with (nolock) on Evn.Evn_id = EvnUslugaCommon.EvnUslugaCommon_pid
			left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EvnUslugaCommon.EvnDirection_id
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EvnUslugaCommon.Usluga_id
			left join v_UslugaComplex UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = EvnUslugaCommon.UslugaComplex_id
			left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnUslugaCommon.PayType_id
			left join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EvnUslugaCommon.EvnDirection_id
			left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = ELR.MedService_id and UCMS.UslugaComplex_id = UslugaComplex.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
			outer apply (
				select top 1 
					Lpu_aid, 
					LpuSection_aid, 
					MedPersonal_aid 
				from 
					v_EvnLabSample with (nolock) 
				where 
					EvnLabRequest_id = ELR.EvnLabRequest_id
			) ELS
			outer apply (
				select top 1 
					MP.Person_FIO 
				from 
					v_MedStaffFact MP with (nolock)
				where 
					MP.LpuSection_id = coalesce(EvnUslugaCommon.LpuSection_uid,ELS.LpuSection_aid,ED.LpuSection_did)
					AND MP.MedPersonal_id = isnull(EvnUslugaCommon.MedPersonal_id,ELS.MedPersonal_aid)
			) MP
			outer apply (
				select top 1
					UslugaComplex_id
				from
					v_UslugaComplexMedService UCMPp (nolock)
				where
					UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
					".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
			) as UCp
			left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(EvnUslugaCommon.LpuSection_uid,ELS.LpuSection_aid,ED.LpuSection_did)
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(LS.Lpu_id,ELS.Lpu_aid,EvnUslugaCommon.Lpu_id)
			{$join_msf}
			where {$filter}
			--order by EvnUslugaCommon_setDate
		";

		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnUslugaTelemedNodeList($data)
	{
		// Фильтры: Person_id, Lpu_id, Server_id
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$data['Lpu_id']$filter .= " and EvnUslugaTelemed.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and EvnUslugaTelemed.Lpu_id=".$data['session']['lpu_id'];
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and EvnUslugaTelemed.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and EvnUslugaTelemed.Server_id=".$data['session']['server_id'];
		}
		*/

		$accessType = 'EvnUslugaTelemed.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params =  array(
			'Lpu_id' => (empty($data['Lpu_id'])?$data['session']['lpu_id']:$data['Lpu_id'])
		);
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= ' and EvnUslugaTelemed.MedPersonal_id = MSF.MedPersonal_id and EvnUslugaTelemed.LpuSection_uid = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}

		$filter = '(1=1) ';
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnUslugaTelemed.EvnUslugaTelemed_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnUslugaTelemed.EvnUslugaTelemed_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnUslugaTelemed.EvnUslugaTelemed_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= ' and EvnUslugaTelemed.Person_id=:Person_id';
			$params['Person_id'] = $data['Person_id'];
		}

		$filter .= ' and EvnUslugaTelemed.EvnUslugaTelemed_setDate is not null';
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/

		$sql = "
		Select
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnUslugaTelemed.Lpu_id,
			EvnUslugaTelemed.EvnUslugaTelemed_id,
			RTrim(EvnUslugaTelemed.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnUslugaTelemed_setDate as datetime),104),'')) as EvnUslugaTelemed_setDT,
			Usluga.Usluga_id,
			'' as Usluga_Code,
			LSP.LpuSectionProfile_Name as Usluga_Name,
			PayType.PayType_SysNick
			,isnull(MP.Person_FIO,'') as MedPersonal_Fio
			,isnull(LS.LpuSection_Name,'') as LpuSection_Name
			,isnull(Lpu.Lpu_Nick,'') as Lpu_Name
			{$addQuery}
			from v_EvnUslugaTelemed EvnUslugaTelemed with (nolock)
			left join v_EvnDirection_all ED with (nolock) on EvnUslugaTelemed.EvnDirection_id = ED.EvnDirection_id and ED.DirFailType_id is null
			left join v_LpuSectionProfile LSP (nolock) on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
			left join v_Diag d with (nolock) on d.Diag_id = EvnUslugaTelemed.Diag_id
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EvnUslugaTelemed.Usluga_id
			left join v_UslugaComplex UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = EvnUslugaTelemed.UslugaComplex_id
			left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnUslugaTelemed.PayType_id
			left join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EvnUslugaTelemed.EvnDirection_id
			outer apply (
				select top 1 Lpu_aid, LpuSection_aid, MedPersonal_aid from v_EvnLabSample with (nolock) where EvnLabRequest_id = ELR.EvnLabRequest_id
			) ELS
			outer apply (
				select top 1 MP.Person_FIO from v_MedStaffFact MP with (nolock)
				where MP.LpuSection_id = isnull(EvnUslugaTelemed.LpuSection_uid,ELS.LpuSection_aid)
					AND MP.MedPersonal_id = isnull(EvnUslugaTelemed.MedPersonal_id,ELS.MedPersonal_aid)
			) MP
			left join v_LpuSection LS with (nolock) on LS.LpuSection_id = isnull(EvnUslugaTelemed.LpuSection_uid,ELS.LpuSection_aid)
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = isnull(LS.Lpu_id,ELS.Lpu_aid)
			{$join_msf}
			where {$filter}
			--order by EvnUslugaTelemed_setDate
		";
		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}
	
	/**
	 * Возваращает список нод для типа EvnUslugaOper #190727
	 */
	function GetEvnUslugaOperNodeList($data)
	{

		$accessType = 'EvnUslugaOper.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params =  array(
			'Lpu_id' => (empty($data['Lpu_id']) ? $data['session']['lpu_id'] : $data['Lpu_id'])
		);
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= ' and EvnUslugaOper.MedPersonal_id = MSF.MedPersonal_id and EvnUslugaOper.LpuSection_uid = MSF.LpuSection_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		} else if (isset($data['user_MedStaffFact_id'])) {
			$params['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		if (isset($params['user_MedStaffFact_id'])) {
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
		}

		$filter = '(1=1) ';
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnUslugaOper.EvnUslugaOper_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnUslugaOper.EvnUslugaOper_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnUslugaOper.EvnUslugaOper_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		switch (true) {
			case ((isset($data['EvnSection_id'])) && ($data['EvnSection_id']>0)):
				$filter .= ' and EvnUslugaOper.EvnUslugaOper_pid=:EvnSection_id';
				$params['EvnSection_id'] = $data['EvnSection_id'];
				break;
			case ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0)):
				$filter .= ' and EvnUslugaOper.EvnUslugaOper_pid=:EvnVizitPL_id';
				$params['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
				break;
			case ((isset($data['EvnVizitPLStom_id'])) && ($data['EvnVizitPLStom_id']>0)):
				$filter .= ' and EvnUslugaOper.EvnUslugaOper_pid=:EvnVizitPLStom_id';
				$params['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
				break;
			case ((isset($data['EvnPS_id'])) && ($data['EvnPS_id']>0)):
				$filter .= ' and EvnUslugaOper.EvnUslugaOper_pid=:EvnPS_id';
				$params['EvnPS_id'] = $data['EvnPS_id'];
				break;
			case ((isset($data['EvnPL_id'])) && ($data['EvnPL_id']>0)):
				$filter .= ' and EvnUslugaOper.EvnUslugaOper_rid=:EvnPL_id';
				$params['EvnPL_id'] = $data['EvnPL_id'];
				break;
			case ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id']>0)):
				$filter .= ' and EvnUslugaOper.EvnUslugaOper_rid=:EvnPLStom_id';
				$params['EvnPLStom_id'] = $data['EvnPLStom_id'];
				break;
			case (!empty($data['type']) && 1==$data['type']):
				// При отображении дерева в ЭМК в виде "по событиям"
				// необходимо отображение всех результатов исследований,
				// в том числе и введенных в рамках конкретных случаев #33176
				break;
			default:
				$filter .= ' and EvnUslugaOper.EvnUslugaOper_pid is null';
				break;
		}

		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= ' and EvnUslugaOper.Person_id=:Person_id';
			$params['Person_id'] = $data['Person_id'];
		}

		$filter .= ' and EvnUslugaOper.EvnUslugaOper_setDate is not null';

		$filter .= " and ISNULL(Evn.EvnClass_SysNick, '') != 'EvnUslugaOper'"; // тесты проб не надо отображать
		$filterAccessRights = getAccessRightsTestFilter('UslugaComplex.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UslugaComplex_id', false, true);

		$existEvnSection = "";
		if (!empty($params['user_MedStaffFact_id'])) {
			$existEvnSection = "exists (
				select top 1 
					es.EvnSection_id 
				from 
					v_EvnSection es (nolock) 
				where 
					es.EvnSection_id = EvnUslugaOper.EvnUslugaOper_pid 
					and es.EvnSection_setDT <= EvnUslugaOper.EvnUslugaOper_setDT 
					and (es.EvnSection_disDT is null 
					or es.EvnSection_disDT >= EvnUslugaOper.EvnUslugaOper_setDT) 
					and es.MedStaffFact_id = :user_MedStaffFact_id
			) or ";
		}
		$filter .= " and (
			{$existEvnSection} (".((!empty($filterAccessRights))?$filterAccessRights."and UCp.UslugaComplex_id is null)":'1=1)');

		if (!empty($params['user_MedStaffFact_id'])){
			$filter .= " or ED.MedPersonal_id = MSF.MedPersonal_id ";
		}

		$filter .= " )";

		$sql = "
		Select
			case when {$accessType} then 'edit' else 'view' end as accessType,
			EvnUslugaOper.Lpu_id,
			EvnUslugaOper.EvnUslugaOper_id,
			RTrim(EvnUslugaOper.EvnClass_Name) as EvnClass_Name,
			RTrim(IsNull(convert(varchar,cast(EvnUslugaOper_setDate as datetime),104),'')) as EvnUslugaOper_setDT,
			RTrim(IsNull(convert(varchar,EvnUslugaOper_setDate,104),''))+' '+RTrim(IsNull(EvnUslugaOper_setTime,'')) as sortDate,
			Usluga.Usluga_id,
			isnull(UslugaComplex.UslugaComplex_Code, Usluga.Usluga_Code) as Usluga_Code,
			coalesce(ucms.UslugaComplex_Name, UslugaComplex.UslugaComplex_Name, Usluga.Usluga_Name) as Usluga_Name,
			PayType.PayType_SysNick
			,isnull(MP.Person_FIO,'') as MedPersonal_Fio
			,isnull(LS.LpuSection_Name,'') as LpuSection_Name
			,isnull(Lpu.Lpu_Nick,'') as Lpu_Name
			{$addQuery}
			from v_EvnUslugaOper EvnUslugaOper with (nolock)
			left join v_Evn Evn with (nolock) on Evn.Evn_id = EvnUslugaOper.EvnUslugaOper_pid
			left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EvnUslugaOper.EvnDirection_id
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EvnUslugaOper.Usluga_id
			left join v_UslugaComplex UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = EvnUslugaOper.UslugaComplex_id
			left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnUslugaOper.PayType_id
			left join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EvnUslugaOper.EvnDirection_id
			left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = ELR.MedService_id and UCMS.UslugaComplex_id = UslugaComplex.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
			outer apply (
				select top 1 Lpu_aid, LpuSection_aid, MedPersonal_aid from v_EvnLabSample with (nolock) where EvnLabRequest_id = ELR.EvnLabRequest_id
			) ELS
			outer apply (
				select top 1 MP.Person_FIO from v_MedStaffFact MP with (nolock)
				where MP.LpuSection_id = coalesce(EvnUslugaOper.LpuSection_uid,ELS.LpuSection_aid,ED.LpuSection_did)
					AND MP.MedPersonal_id = isnull(EvnUslugaOper.MedPersonal_id,ELS.MedPersonal_aid)
			) MP
			outer apply (
				select top 1
					UslugaComplex_id
				from
					v_UslugaComplexMedService UCMPp (nolock)
				where
					UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
					".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
			) as UCp
			left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(EvnUslugaOper.LpuSection_uid,ELS.LpuSection_aid,ED.LpuSection_did)
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(LS.Lpu_id,ELS.Lpu_aid,EvnUslugaOper.Lpu_id)
			{$join_msf}
			where {$filter}
			--order by EvnUslugaOper_setDate
		";

		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		else
			return false;
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnOnkoNotifyNodeList($data) {
		$params = array();
		
		if ((isset($data['EvnPL_id'])) && ($data['EvnPL_id'] > 0)) {
			$params['Evn_id'] = $data['EvnPL_id'];
		}
		
		if ((isset($data['EvnPS_id'])) && ($data['EvnPS_id'] > 0)) {
			$params['Evn_id'] = $data['EvnPS_id'];
		}
		
		if(empty($params['Evn_id'])) {
			$params['Person_id'] = $data['Person_id'];
			$filter = 'EON.Person_id = :Person_id';
		}
		else {
			$filter = 'EON.EvnOnkoNotify_rid = :Evn_id';
		}
		
		$sql = "
			select
				EON.EvnClass_id,
				EON.EvnOnkoNotify_id,
				RTrim(IsNull(convert(varchar, cast(EON.EvnOnkoNotify_setDate as datetime), 104), '')) as EvnOnkoNotify_setDT,
				case 
					when EON.PersonRegisterFailIncludeCause_id is null and PR.PersonRegister_setDate is not null then 'Включен в регистр'
					when PRF.PersonRegisterFailIncludeCause_Name is not null then PRF.PersonRegisterFailIncludeCause_Name
					else ''
				end as EvnOnkoNotify_Status
			from v_EvnOnkoNotify EON with (nolock)
				left join v_PersonRegister PR WITH (NOLOCK) on PR.EvnNotifyBase_id = EON.EvnOnkoNotify_id
				left join v_PersonRegisterFailIncludeCause PRF WITH (NOLOCK) on PRF.PersonRegisterFailIncludeCause_id = EON.PersonRegisterFailIncludeCause_id
			where 
				{$filter}
		";
		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
		
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnOnkoNotifyNeglectedNodeList($data) {
		$params = array();
		
		if ((isset($data['EvnPL_id'])) && ($data['EvnPL_id'] > 0)) {
			$params['Evn_id'] = $data['EvnPL_id'];
		}
		
		if ((isset($data['EvnPS_id'])) && ($data['EvnPS_id'] > 0)) {
			$params['Evn_id'] = $data['EvnPS_id'];
		}
		
		if(empty($params['Evn_id'])) {
			$params['Person_id'] = $data['Person_id'];
			$filter = 'EON.Person_id = :Person_id';
		}
		else {
			$filter = 'EON.EvnOnkoNotify_rid = :Evn_id';
		}
		
		$sql = "
			select
				EONN.EvnClass_id,
				EONN.EvnOnkoNotifyNeglected_id,
				RTrim(IsNull(convert(varchar, cast(EONN.EvnOnkoNotifyNeglected_setDate as datetime), 104), '')) as EvnOnkoNotifyNeglected_setDT,
				OLDC.OnkoLateDiagCause_Name as OnkoLateDiagCause_Name
			from v_EvnOnkoNotify EON with (nolock)
				inner join v_EvnOnkoNotifyNeglected EONN with (nolock) on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id 
				inner join v_OnkoLateDiagCause OLDC with (nolock) on OLDC.OnkoLateDiagCause_id = EONN.OnkoLateDiagCause_id
			where 
				{$filter}
		";
		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
		
	}

	/**
	 * Возваращает список нод
	 */
	function GetEvnStickNodeList($data)
	{
		$params = array(
			'Lpu_id' => $data['session']['lpu_id'],
			'Org_id' => $data['session']['org_id']
		);
		$filter = '';
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(ESB.EvnStickBase_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['EvnPL_id'])) && ($data['EvnPL_id'] > 0))
		{
			$params['Evn_id'] = $data['EvnPL_id'];
		}
		
		if ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id'] > 0))
		{
			$params['Evn_id'] = $data['EvnPLStom_id'];
		}

		if ((isset($data['EvnPS_id'])) && ($data['EvnPS_id'] > 0))
		{
			$params['Evn_id'] = $data['EvnPS_id'];
		}

		if(empty($params['Evn_id']))
		{
			$sel1 = '';
			$sel2 = '';
			$sel3 = '';
			$sel4 = 'ESB.EvnStickBase_mid';
			$filter .= ' and ESB.Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			$sel1 = "when ESB.EvnStickBase_mid = :Evn_id then 'Текущий'";
			$sel2 = "when ESB.EvnStickBase_mid = :Evn_id then ''";
			$sel3 = "when ESB.EvnStickBase_mid = :Evn_id then ''";
			$sel4 = ':Evn_id';
			$filter .= ' and ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :Evn_id
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id = :Evn_id
				)';
		}

		$this->load->model('Stick_model');
		$accessType = $this->Stick_model->getEvnStickAccessType($data);
		
		$sql = "
			select
				{$accessType}
				ESB.Lpu_id,
				ESB.EvnStickBase_id as EvnStick_id,
				ESB.EvnStickBase_pid as EvnStick_pid,
				ESB.EvnStickBase_Num as EvnStick_Num,
				ESB.EvnStickBase_Ser as EvnStick_Ser,
				convert(varchar(10),ESB.EvnStickBase_insDT,104) as sortdate,
				RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
				RTRIM(ISNULL(SWT.StickWorkType_Name, '')) as StickWorkType_Name,
				--convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStick_setDate, -- Дата выдачи
				CONVERT(varchar(10), EBWR_d.evnStickWorkRelease_begDT,104) as EvnStick_setDate,
				case
					{$sel1}
					when EvnPL.EvnPL_id is not null then 'ТАП'
					when EvnPLStom.EvnPLStom_id is not null then 'Стом. ТАП'
					when EvnPS.EvnPS_id is not null then 'КВС'
					else ''
				end as EvnStick_ParentTypeName, -- тип родительского документа
				case
					{$sel2}
					when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
					when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
					when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
					else ''
				end as EvnStick_ParentNum, -- номер родительского документа
				case
					{$sel3}
					when EvnPL.EvnPL_id is not null then convert(varchar(10), EvnPL.EvnPL_setDT, 104)
					when EvnPLStom.EvnPLStom_id is not null then convert(varchar(10), EvnPLStom.EvnPLStom_setDT, 104)
					when EvnPS.EvnPS_id is not null then convert(varchar(10), EvnPS.EvnPS_setDT, 104)
					else ''
				end as EvnStick_ParentDate, -- дата родительского документа
				{$sel4} as Evn_pid,
				SC.StickCause_Code as StickCause_Code,
				1 as evnStickType,
				case when ESB.EvnStickBase_disDT is not null then 1 else 0 end as EvnStick_closed,
				case when ESB.EvnStickBase_disDT is not null then 'ЛВН закрыт' else 'ЛВН открыт' end as EvnStick_closedName,
				convert(varchar(10), ESB.EvnStickBase_disDT, 104) as EvnStick_disDate
				{$addQuery}
			from v_EvnStickBase ESB with (nolock)
			 	inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
				left join StickOrder SO with (nolock) on SO.StickOrder_id = ESB.StickOrder_id 
				left join StickWorkType SWT with (nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ESB.MedStaffFact_id
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
				-- ТАП/КВС
				left join v_EvnPL EvnPL with (nolock) on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
				left join v_EvnPLStom EvnPLStom with (nolock) on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
				left join v_EvnPS EvnPS with (nolock) on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
				left join v_StickCause SC with (nolock) on SC.StickCause_id = ESB.StickCause_id
				outer apply (
					select top 1 Org_id
					from v_EvnStickWorkRelease ESWR with (nolock)
					where ESB.EvnStickBase_id = ESWR.EvnStickBase_id and ESWR.Org_id = :Org_id
				) ESWR
				outer apply(
					select top 1 evnStickWorkRelease_begDT
					from v_EvnStickWorkRelease ESWR with (nolock)
					where ESB.EvnStickBase_id = ESWR.EvnStickBase_id
				) EBWR_d
				outer apply(
					select top 1 RegistryESDataStatus_id
					from v_RegistryESData with(nolock)
					where Evn_id = ESB.EvnStickBase_id
					order by case when RegistryESDataStatus_id = 2 then 0 else 1 end
				) as RESD
				-- end ТАП/КВС
			where
				EC.EvnClass_SysNick = 'EvnStick'
				{$filter}
			--order by ESB.EvnStickBase_insDT desc
		";

		//echo getDebugSQL($sql, $params); exit();
		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			$response_arr = swFilterResponse::filterNotViewDiag($res->result('array'), $data);
			// ЛН выписанные в Закрытой МО видны только в этой МО
			if($this->getRegionNick() == 'ufa') {
				$sql = "
					select lp.Lpu_id
					from v_AccessRightsOrg ar with (nolock)
					left join v_Lpu lp with (nolock) on lp.Org_id = ar.Org_id
				";
				$result = $this->db->query($sql);
				if ( !is_object($result) )
				{
					throw new Exception('Ошибка запроса списка ЛПУ с особым статусом', 500);
				}
				$res_arr = $result->result('array');
				$_list_vip_lpu = array();
				foreach($res_arr as $row)
				{
					$_list_vip_lpu[] = $row['Lpu_id'];
				}
				$groups = explode('|', $data['session']['groups']);
				foreach ($groups as $key => $value) {
					$groups[$key] = "'".$value."'";
				}
				$groups = implode(',',$groups);

				foreach($response_arr as $i => $row)
				{
					if (
						isset($row['Lpu_id']) && in_array($row['Lpu_id'],$_list_vip_lpu) && ($row['Lpu_id'] != $data['Lpu_id'])
					)
					{
						$queryParams = array();
						$queryParams['Lpu_iid'] = $row['Lpu_id'];
						$queryParams['Lpu_id'] = $data['Lpu_id'];
						$queryParams['pmUser_id'] = $data['pmUser_id'];
						$join = '';
						$where = '';
						if(isset($data['user_MedStaffFact_id'])){
							$join = "left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = :MedStaffFact_id";
							$where = " or arl.Post_id = msf.Post_id ";
							$queryParams['MedStaffFact_id'] = $data['user_MedStaffFact_id'];
						}
						$sql = "
							select 
							lp.Lpu_id
							from v_Lpu lp with (nolock) 
							inner join v_AccessRightsOrg ar with (nolock) on ar.Org_id = lp.Org_id
							inner join v_AccessRightsLimit arl with (nolock) on arl.AccessRightsName_id = ar.AccessRightsName_id
							left join v_Lpu lpu with (nolock) on lpu.Lpu_id = :Lpu_id
							{$join}
							where lp.Lpu_id = :Lpu_iid and (arl.Org_id = lpu.Org_id or (arl.AccessRightsType_UserGroups in ({$groups})) or arl.AccessRightsType_User = :pmUser_id {$where})
						";
						$result = $this->db->query($sql,$queryParams);
						if ( !is_object($result) )
						{
							throw new Exception('Ошибка проверки исключений доступа к ЛПУ с особым статусом', 500);
						}
						$res = $result->result('array');
						if(!(count($res) > 0))
							unset($response_arr[$i]);
					}
				}
				$response_arr = array_values($response_arr);
				return $response_arr;
			} else {
				return $response_arr;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Возвращает список нод
	 */
	function GetEvnStickDopNodeList($data) {
		$params = array(
			'Lpu_id' => $data['session']['lpu_id'],
			'Org_id' => $data['session']['org_id']
		);
		$filter = '';
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(ESB.EvnStickBase_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ( isset($data['EvnPL_id'] ) && $data['EvnPL_id'] > 0 ) {
			$params['Evn_id'] = $data['EvnPL_id'];
		}
		
		if ( isset($data['EvnPLStom_id']) && $data['EvnPLStom_id'] > 0 ) {
			$params['Evn_id'] = $data['EvnPLStom_id'];
		}

		if ((isset($data['EvnPS_id'])) && ($data['EvnPS_id'] > 0))
		{
			$params['Evn_id'] = $data['EvnPS_id'];
		}

		if(empty($params['Evn_id']))
		{
			$sel1 = '';
			$sel2 = '';
			$sel3 = '';
			$sel4 = 'ESB.EvnStickBase_mid';
			$filter .= ' and ESB.Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			$sel1 = "when ESB.EvnStickBase_mid = :Evn_id then 'Текущий'";
			$sel2 = "when ESB.EvnStickBase_mid = :Evn_id then ''";
			$sel3 = "when ESB.EvnStickBase_mid = :Evn_id then ''";
			$sel4 = ':Evn_id';
			$filter .= ' and ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :Evn_id
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id = :Evn_id
				)';
		}

		$this->load->model('Stick_model');
		$accessType = $this->Stick_model->getEvnStickAccessType($data);
		
		$sql = "
			select
				{$accessType}
				ESB.Lpu_id,
				ESB.EvnStickBase_id as EvnStickDop_id,
				ESBD.EvnStickBase_pid as EvnStick_pid,
				ESB.EvnStickBase_pid as EvnStickDop_pid,
				ESB.EvnStickBase_Num as EvnStick_Num,
				ESB.EvnStickBase_Ser as EvnStick_Ser,
				convert(varchar(10),ESB.EvnStickBase_insDT,104) as sortdate,
				RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
				convert(varchar(10), ISNULL(ESBD.EvnStickBase_setDT, ESB.EvnStickBase_setDT), 104) as EvnStick_setDate, -- Дата выдачи
				RTRIM(ISNULL(SWT.StickWorkType_Name, '')) as StickWorkType_Name,
				case
					{$sel1}
					when EvnPL.EvnPL_id is not null then 'ТАП'
					when EvnPLStom.EvnPLStom_id is not null then 'Стом. ТАП'
					when EvnPS.EvnPS_id is not null then 'КВС'
					else ''
				end as EvnStick_ParentTypeName, -- тип родительского документа
				case
					{$sel2}
					when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
					when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
					when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
					else ''
				end as EvnStick_ParentNum, -- номер родительского документа
				case
					{$sel3}
					when EvnPL.EvnPL_id is not null then convert(varchar(10), EvnPL.EvnPL_setDT, 104)
					when EvnPLStom.EvnPLStom_id is not null then convert(varchar(10), EvnPLStom.EvnPLStom_setDT, 104)
					when EvnPS.EvnPS_id is not null then convert(varchar(10), EvnPS.EvnPS_setDT, 104)
					else ''
				end as EvnStick_ParentDate, -- дата родительского документа
				{$sel4} as Evn_pid,
				SC.StickCause_Code as StickCause_Code,
				2 as evnStickType,
				case when isnull(ESB.EvnStickBase_disDT,ESBD.EvnStickBase_disDT) is not null then 1 else 0 end as EvnStick_closed,
				case
					when isnull(ESB.EvnStickBase_disDT,ESBD.EvnStickBase_disDT) is not null then 'ЛВН закрыт'
					else 'ЛВН открыт'
				end as EvnStick_closedName,
				convert(varchar(10), isnull(ESB.EvnStickBase_disDT,ESBD.EvnStickBase_disDT), 104) as EvnStick_disDate
				{$addQuery}
			from v_EvnStickBase ESB with (nolock)
			 	inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
				left join v_EvnStickBase ESBD with (nolock) on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid
					and EC.EvnClass_SysNick = 'EvnStickDop'
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ESB.MedStaffFact_id
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
				-- ТАП/КВС
				left join v_EvnPL EvnPL with (nolock) on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
				left join v_EvnPLStom EvnPLStom with (nolock) on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
				left join v_EvnPS EvnPS with (nolock) on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
				left join v_StickCause SC with (nolock) on SC.StickCause_id = ESB.StickCause_id
				-- end ТАП/КВС
				left join StickOrder SO with (nolock) on SO.StickOrder_id = ISNULL(ESBD.StickOrder_id, ESB.StickOrder_id)
				left join StickWorkType SWT with (nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
				outer apply(
					select top 1 RegistryESDataStatus_id
					from v_RegistryESData with(nolock)
					where Evn_id = ESB.EvnStickBase_id
					order by case when RegistryESDataStatus_id = 2 then 0 else 1 end
				) as RESD
			where
				EC.EvnClass_SysNick = 'EvnStickDop'
				{$filter}
			--order by ESB.EvnStickBase_id desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	 * Возвращает список нод
	 */
	function GetEvnStickStudentNodeList($data) {
		$params = array(
			'Lpu_id' => $data['session']['lpu_id']
		);
		$filter = '';
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(ESB.EvnStickBase_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ( isset($data['EvnPL_id'] ) && $data['EvnPL_id'] > 0 ) {
			$params['Evn_id'] = $data['EvnPL_id'];
		}
		
		if ( isset($data['EvnPLStom_id']) && $data['EvnPLStom_id'] > 0 ) {
			$params['Evn_id'] = $data['EvnPLStom_id'];
		}

		if ((isset($data['EvnPS_id'])) && ($data['EvnPS_id'] > 0))
		{
			$params['Evn_id'] = $data['EvnPS_id'];
		}
		
		if(empty($params['Evn_id']))
		{
			$sel4 = 'ESB.EvnStickBase_mid';
			$filter .= ' and ESB.Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			$sel4 = ':Evn_id';
			$filter .= ' and ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :Evn_id
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id = :Evn_id
				)';
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$session = $data['session'];
		$med_personal_id = !empty($session['medpersonal_id'])?$session['medpersonal_id']:null;
		$isMedStatUser = !empty($session['isMedStatUser']) ? $session['isMedStatUser'] : false;
		$isPolkaRegistrator = (!empty($session['ARMList']) && in_array('regpol',$session['ARMList']))/* || (!empty($session['evnstickblank_access']) && $session['evnstickblank_access'])*/;
		$isARMLVN = !empty($session['ARMList']) && in_array('lvn',$session['ARMList']);

		$sql = "
			select
				case when (
					case
						when ESB.Lpu_id = :Lpu_id then 1
						" . (count($data['session']['linkedLpuIdList']) > 1 ? "when ESB.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and ISNULL(ESB.EvnStickBase_IsTransit, 1) = 2 then 1" : "") . "
						when " . (count($med_personal_list) > 0 ? 1 : 0) . " = 1 then 1
						when " . ($isMedStatUser || $isPolkaRegistrator || $isARMLVN ? 1 : 0) . " = 1 then 1
						else 0
					end = 1
					" . (!$isPolkaRegistrator && !$isMedStatUser && !$isARMLVN && count($med_personal_list)>0 ? "and (ESB.MedPersonal_id is null or ESB.MedPersonal_id in (".implode(',',$med_personal_list).") )" : "") . "
				) then 'edit' else 'view' end as accessType,
				ESB.Lpu_id,
				ESB.EvnStickBase_id as EvnStickStudent_id,
				ESB.EvnStickBase_pid as EvnStick_pid,
				ESB.EvnStickBase_pid as EvnStickStudent_pid,
				ESB.EvnStickBase_Num as EvnStickStudent_Num,
				convert(varchar(10),ESB.EvnStickBase_insDT,104) as sortdate,
				convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStickStudent_setDate, -- Дата выдачи
				{$sel4} as Evn_pid,
				SC.StickCause_Code as StickCause_Code,
				3 as evnStickType,
				case when ESB.EvnStickBase_disDT is not null then 1 else 0 end as EvnStick_closed
				{$addQuery}
			from v_EvnStickBase ESB with (nolock)
			 	inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id 
				left join v_StickCause SC with (nolock) on SC.StickCause_id = ESB.StickCause_id
			where
				EC.EvnClass_SysNick = 'EvnStickStudent'
				{$filter}
			--order by ESB.EvnStickBase_insDT desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			$response_arr = $res->result('array');
			// ЛН выписанные в Закрытой МО видны только в этой МО
			if($this->getRegionNick() == 'ufa') {
				$sql = "
					select lp.Lpu_id
					from v_AccessRightsOrg ar with (nolock)
					left join v_Lpu lp with (nolock) on lp.Org_id = ar.Org_id
				";
				$result = $this->db->query($sql);
				if ( !is_object($result) )
				{
					throw new Exception('Ошибка запроса списка ЛПУ с особым статусом', 500);
				}
				$res_arr = $result->result('array');
				$_list_vip_lpu = array();
				foreach($res_arr as $row)
				{
					$_list_vip_lpu[] = $row['Lpu_id'];
				}
				$groups = explode('|', $data['session']['groups']);
				foreach ($groups as $key => $value) {
					$groups[$key] = "'".$value."'";
				}
				$groups = implode(',',$groups);

				foreach($response_arr as $i => $row)
				{
					if (
						isset($row['Lpu_id']) && in_array($row['Lpu_id'],$_list_vip_lpu) && ($row['Lpu_id'] != $data['Lpu_id'])
					)
					{
						$queryParams = array();
						$queryParams['Lpu_iid'] = $row['Lpu_id'];
						$queryParams['Lpu_id'] = $data['Lpu_id'];
						$queryParams['pmUser_id'] = $data['pmUser_id'];
						$join = '';
						$where = '';
						if(isset($data['user_MedStaffFact_id'])){
							$join = "left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = :MedStaffFact_id";
							$where = " or arl.Post_id = msf.Post_id ";
							$queryParams['MedStaffFact_id'] = $data['user_MedStaffFact_id'];
						}
						$sql = "
							select 
							lp.Lpu_id
							from v_Lpu lp with (nolock) 
							inner join v_AccessRightsOrg ar with (nolock) on ar.Org_id = lp.Org_id
							inner join v_AccessRightsLimit arl with (nolock) on arl.AccessRightsName_id = ar.AccessRightsName_id
							left join v_Lpu lpu with (nolock) on lpu.Lpu_id = :Lpu_id
							{$join}
							where lp.Lpu_id = :Lpu_iid and (arl.Org_id = lpu.Org_id or (arl.AccessRightsType_UserGroups in ({$groups})) or arl.AccessRightsType_User = :pmUser_id {$where})
						";
						$result = $this->db->query($sql,$queryParams);
						if ( !is_object($result) )
						{
							throw new Exception('Ошибка проверки исключений доступа к ЛПУ с особым статусом', 500);
						}
						$res = $result->result('array');
						if(!(count($res) > 0))
							unset($response_arr[$i]);
					}
				}
				$response_arr = array_values($response_arr);
				return swFilterResponse::filterNotViewDiag($response_arr, $data);
			} else {
				return swFilterResponse::filterNotViewDiag($response_arr, $data);
			}
		}
		else
			return false;
	}

	/**
	 * Возвращает список нод
	 */
	function GetEvnCostPrintNodeList($data) {
		$params = array(
			'Lpu_id' => $data['session']['lpu_id']
		);
		$filter = '1=1';

		if ( isset($data['EvnUslugaPar_id'] ) && $data['EvnUslugaPar_id'] > 0 ) {
			$params['Evn_id'] = $data['EvnUslugaPar_id'];
		}

		if ( isset($data['EvnPL_id'] ) && $data['EvnPL_id'] > 0 ) {
			$params['Evn_id'] = $data['EvnPL_id'];
		}

		if ( isset($data['EvnPLStom_id']) && $data['EvnPLStom_id'] > 0 ) {
			$params['Evn_id'] = $data['EvnPLStom_id'];
		}

		if ((isset($data['EvnPS_id'])) && ($data['EvnPS_id'] > 0))
		{
			$params['Evn_id'] = $data['EvnPS_id'];
		}

		if(empty($params['Evn_id']))
		{
			$filter .= ' and ECP.Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			$filter .= ' and ECP.Evn_id = :Evn_id';
		}

		$sql = "
			select
				'edit' as accessType,
				ECP.EvnCostPrint_id,
				convert(varchar(10), ECP.EvnCostPrint_insDT, 104) as sortdate,
				convert(varchar(10), ECP.EvnCostPrint_setDT, 104) as EvnCostPrint_setDate, -- Дата выдачи
				ISNULL(EvnCostPrint_IsNoPrint, 1) as EvnCostPrint_IsNoPrint
			from v_EvnCostPrint ECP with (nolock)
			where
				{$filter}
			--order by ECP.EvnCostPrint_insDT desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возвращает список нод
	 */
	function GetCmpCallCardCostPrintNodeList($data) {
		$params = array(
			'Lpu_id' => $data['session']['lpu_id']
		);
		$filter = '1=1';

		if ( isset($data['CmpCallCard_id'] ) && $data['CmpCallCard_id'] > 0 ) {
			$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}

		if ( isset($data['CmpCloseCard_id']) && $data['CmpCloseCard_id'] > 0 ) {
			$params['CmpCallCard_id'] = $this->getFirstResultFromQuery("select CmpCallCard_id from v_CmpCloseCard (nolock) where CmpCloseCard_id = :CmpCloseCard_id", $data);
		}

		if(empty($params['CmpCallCard_id']))
		{
			$filter .= ' and CCP.Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			$filter .= ' and CCP.CmpCallCard_id = :CmpCallCard_id';
		}

		$sql = "
			select
				'edit' as accessType,
				CCP.CmpCallCardCostPrint_id,
				convert(varchar(10), CCP.CmpCallCardCostPrint_insDT, 104) as sortdate,
				convert(varchar(10), CCP.CmpCallCardCostPrint_setDT, 104) as CmpCallCardCostPrint_setDate, -- Дата выдачи
				ISNULL(CmpCallCardCostPrint_IsNoPrint, 1) as CmpCallCardCostPrint_IsNoPrint
			from v_CmpCallCardCostPrint CCP with (nolock)
			where
				{$filter}
			--order by CCP.CmpCallCardCostPrint_insDT desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Прикрепление с датой прикрепления
	 */
	function GetPersonCardBegNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and PersonCard.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and PersonCard.Lpu_id=".$data['session']['lpu_id'];
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and PersonCard.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and PersonCard.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and PersonCard.Person_id=".$data['Person_id'];
		}
		
		$sql = "
			Select 
			PersonCard.PersonCard_id, 
			RTrim(IsNull(convert(varchar,cast(PersonCard.PersonCard_begdate as datetime),104),'')) as PersonCard_begDT, 
			Lpu.Lpu_Nick, 
			case when ps.PersonCard_id is not null then 1 else 0 end as PersonCard_Current
			from v_PersonCard_all PersonCard with (nolock)
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PersonCard.Lpu_id
			left join v_personcard ps with (nolock) on ps.PersonCard_id = PersonCard.PersonCard_id
			where {$filter}
			group by PersonCard.PersonCard_id, PersonCard.PersonCard_begdate, Lpu.Lpu_Nick, ps.PersonCard_id
			--order by PersonCard.PersonCard_begdate";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Прикрепление с датой прикрепления
	 */
	function GetPersonCardEndNodeList($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		/*if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			$filter .= " and PersonCard.Lpu_id=".$data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			$filter .= " and PersonCard.Lpu_id=".$data['session']['lpu_id'];
		}*/
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and PersonCard.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and PersonCard.Server_id=".$data['session']['server_id'];
		}
		*/
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and PersonCard.Person_id=".$data['Person_id'];
		}
		
		$sql = "
			Select 
			PersonCard.PersonCard_id, 
			RTrim(IsNull(convert(varchar,cast(PersonCard.PersonCard_enddate as datetime),104),'')) as PersonCard_endDT, 
			Lpu.Lpu_Nick, 
			case when ps.PersonCard_id is not null then 1 else 0 end as PersonCard_Current
			from v_PersonCard_all PersonCard with (nolock)
			left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PersonCard.Lpu_id
			left join v_personcard ps with (nolock) on ps.PersonCard_id = PersonCard.PersonCard_id
			where {$filter} 
			and PersonCard.PersonCard_enddate is not null
			group by PersonCard.PersonCard_id, PersonCard.PersonCard_enddate, Lpu.Lpu_Nick, ps.PersonCard_id
			--order by PersonCard.PersonCard_enddate";
		//print $sql;
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	* Возвращает Направления на МСЭ
	*/
	function GetEvnPrescrMseNodeList($data)
	{
		$params = array(
			'Lpu_id' => empty($data['Lpu_id'])?$data['session']['lpu_id']:$data['Lpu_id'] ,
			'Person_id' => $data['Person_id']
		);
		$filter = '';
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnPrescrMse.EvnPrescrMse_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnPrescrMse.EvnPrescrMse_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnPrescrMse.EvnPrescrMse_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and cast(EvnPrescrMse.EvnPrescrMse_setDT as date) >= cast(:Beg_EvnDate as date)";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and cast(EvnPrescrMse.EvnPrescrMse_setDT as date) <= cast(:End_EvnDate as date)";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1)";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";

		$sql = "
			select
				EvnPrescrMse.EvnPrescrMse_id,
				EvnPrescrMse.Diag_id,
				EvnPrescrMse.Lpu_id,
				EvnPrescrMse.TimetableMedService_id,
				EvnPrescrMse.EvnVK_id,
				EvnPrescrMse.MedPersonal_sid,
				EvnPrescrMse.Lpu_gid,
				ms.MedService_id,
				ms.MedService_Name,
				d.Diag_Code,
				'Направление на МСЭ' as EvnClass_Name,
				isnull(convert(varchar,EvnPrescrMse.EvnPrescrMse_setDT,104),'') as date_beg
				{$addQuery}
			from
				v_EvnPrescrMse EvnPrescrMse with (nolock)
				left join v_Diag d with (nolock) on EvnPrescrMse.Diag_id = d.Diag_id
				left join v_MedService ms with (nolock) on EvnPrescrMse.MedService_id = ms.MedService_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPrescrMse.Lpu_id
				left join v_EvnQueue eq with (nolock) on eq.EvnQueue_id = EvnPrescrMse.EvnQueue_id
			where 
				EvnPrescrMse.Person_id = :Person_id
				and (eq.EvnQueue_id is null or eq.EvnQueue_failDT is null)
				{$filter}
			--order by EvnPrescrMse.EvnPrescrMse_setDT";
		//echo getDebugSql($sql, $params); exit;

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	* Возвращает Протоколы ВК
	*/
	function GetEvnVKNodeList($data)
	{
		$params = array(
			'Lpu_id' => empty($data['Lpu_id'])?$data['session']['lpu_id']:$data['Lpu_id'] ,
			'Person_id' => $data['Person_id']
		);
		$addQuery = '';
		$filter = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnVK.EvnVK_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnVK.EvnVK_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnVK.EvnVK_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and cast(EvnVK.EvnVK_setDT as date) >= cast(:Beg_EvnDate as date)";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and cast(EvnVK.EvnVK_setDT as date) <= cast(:End_EvnDate as date)";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}

		$needAccessFilter = true;
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;

		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnVK.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('ms.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1)";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";

		$sql = "
			select
				EvnVK.EvnVK_id,
				EvnVK.Diag_id,
				EvnVK.Lpu_id,
				ms.MedService_id,
				ms.MedService_Name,
				d.Diag_Code,
				'Протокол ВК' as EvnClass_Name,
				isnull(convert(varchar,EvnVK.EvnVK_setDT,104),'') as date_beg
				{$addQuery}
			from
				v_EvnVK EvnVK with (nolock)
				left join v_Diag d with (nolock) on EvnVK.Diag_id = d.Diag_id
				left join v_MedService ms with (nolock) on EvnVK.MedService_id = ms.MedService_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnVK.Lpu_id
			where 
				EvnVK.Person_id = :Person_id
				{$filter}
			--order by EvnVK.EvnVK_setDT";
		//echo getDebugSql($sql, $params); exit;

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}

	/**
	* Возвращает Протоколы МСЭ
	*/
	function GetEvnMseNodeList($data)
	{
		$params = array(
			'Lpu_id' => empty($data['Lpu_id'])?$data['session']['lpu_id']:$data['Lpu_id'] ,
			'Person_id' => $data['Person_id']
		);
		$filter = '';
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when ISNULL(EvnMse.EvnMse_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EvnMse.EvnMse_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EvnMse.EvnMse_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and cast(EvnMse.EvnMse_setDT as date) >= cast(:Beg_EvnDate as date)";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}

		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and cast(EvnMse.EvnMse_setDT as date) <= cast(:End_EvnDate as date)";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}

		$needAccessFilter = true;
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;

		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnMse.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('ms.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		
		/*if(isset($params['Lpu_id'])){
			//случаи, которые были заведены в МО или подразделении с особым статусом не должны быть видны пользователям, которые не работают в этих МО и подразделениях
			//$filter .= " and (Lpu.Lpu_id = :Lpu_id or isnull(Lpu.Lpu_IsSecret, 1) = 1) ";
		}*/
		$filter .= " 
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = :Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null) 
		) 
		";

		$sql = "
			select
				EvnMse.EvnMse_id,
				EvnMse.Diag_id,
				EvnMse.Lpu_id,
				EvnMse.EvnPrescrMse_id,
				ms.MedService_id,
				ms.MedService_Name,
				d.Diag_Code,
				'Обратный талон' as EvnClass_Name,
				isnull(convert(varchar,EvnMse.EvnMse_setDT,104),'') as date_beg
				{$addQuery}
			from
				v_EvnMse EvnMse with (nolock)
				left join v_Diag d with (nolock) on EvnMse.Diag_id = d.Diag_id
				left join v_MedService ms with (nolock) on EvnMse.MedService_id = ms.MedService_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnMse.Lpu_id
			where 
				EvnMse.Person_id = :Person_id
				{$filter}
			--order by EvnMse.EvnMse_setDT";
		//echo getDebugSql($sql, $params); exit;

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return $res->result('array');
			//return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}
	
	/**
	 * Возвращает Талон дополнений больного ЗНО
	 */
	function GetMorbusOnkoVizitPLDopNodeList($data)
	{
		if (empty($data['EvnVizitPL_id']))
		{
			return array();
		}
		$sql = '
			Select 
				t.EvnVizit_id as MorbusOnkoVizitPLDop_pid,
				t.MorbusOnkoVizitPLDop_id,
				convert(varchar,t.MorbusOnkoVizitPLDop_setDT,104) as MorbusOnkoVizitPLDop_setDT
			from
				v_MorbusOnkoVizitPLDop t with (NOLOCK)
			where
				t.EvnVizit_id = :EvnVizitPL_id
		';
		$res = $this->db->query($sql,$data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возвращает Выписка из медицинской карты стационарного больного ЗНО
	 */
	function GetMorbusOnkoLeaveNodeList($data)
	{
		if (empty($data['EvnSection_id'])) {
			return array();
		}
		$sql = '
			Select
				t.EvnSection_id as MorbusOnkoLeave_pid,
				t.MorbusOnkoLeave_id,
				convert(varchar,t.MorbusOnkoLeave_insDT,104) as MorbusOnkoLeave_setDT
			from
				v_MorbusOnkoLeave t with (NOLOCK)
			where
				t.EvnSection_id = :EvnSection_id
		';
		$res = $this->db->query($sql,$data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistory($data) {
		$this->load->model('Evn_model', 'Evn_model');
		$res = array('data' => array());
		
		$this->_personEvnClassList = $this->Evn_model->getPersonEvnClassList(array(
			'Person_id' => $data['Person_id'],
			'Evn_pid' => null,
			'ignoreFilterByEvnPid' => true
		));

		if (
			in_array('EvnPL', $this->_personEvnClassList)
			|| in_array('EvnPLStom', $this->_personEvnClassList)
		) {
			$tmp_list = $this->getPersonHistoryEvnPL($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if (in_array('EvnPS', $this->_personEvnClassList)) {
			$tmp_list = $this->getPersonHistoryEvnPS($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if (
			in_array('EvnPLDispDop13', $this->_personEvnClassList)
			|| in_array('EvnPLDispProf', $this->_personEvnClassList)
			|| in_array('EvnPLDispOrp', $this->_personEvnClassList)
			|| in_array('EvnPLDispTeenInspection', $this->_personEvnClassList)
			|| in_array('EvnPLDispDriver', $this->_personEvnClassList)
			|| in_array('EvnPLDispMigrant', $this->_personEvnClassList)
		) {
			$tmp_list = $this->getPersonHistoryEvnPLDisp($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}
		
		$tmp_list = $this->GetDispRefuse($data);
		$res['data'] = array_merge($res['data'], $tmp_list);

		if (in_array('EvnUslugaPar', $this->_personEvnClassList)) {
			$tmp_list_lis = array();
			if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$tmp_list_lis = $this->lis->GET('EvnUsluga/ParPersonHistory', $data, 'list');
			}
			if ($this->isSuccessful($tmp_list_lis)) {
				$tmp_list = $this->getPersonHistoryEvnUslugaPar($data, $tmp_list_lis);
				$res['data'] = array_merge($res['data'], $tmp_list, $tmp_list_lis);
			}
		}

		if (in_array('EvnVK', $this->_personEvnClassList)) {
			$tmp_list = $this->getPersonHistoryEvnVK($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if (in_array('EvnStick', $this->_personEvnClassList)) {
			$tmp_list = $this->getPersonHistoryEvnStick($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if (in_array('EvnDirection', $this->_personEvnClassList)) {
			$tmp_list_lis = array();
			if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$tmp_list_lis = $this->lis->GET('EvnDirection/PersonHistory', $data, 'list');
			}
			if ($this->isSuccessful($tmp_list_lis)) {
				$tmp_list = $this->getPersonHistoryEvnDirection($data, $tmp_list_lis);
				$res['data'] = array_merge($res['data'], $tmp_list, $tmp_list_lis);
			}
		}

		if (in_array('EvnRecept', $this->_personEvnClassList)) {
			$tmp_list = $this->getPersonHistoryEvnRecept($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if (in_array('EvnUslugaTelemed', $this->_personEvnClassList)) {
			$tmp_list = $this->getPersonHistoryEvnUslugaTelemed($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		$tmp_list = $this->GetCmpCallCard($data);
		foreach($tmp_list as $k => $row) {
			$tmp_list[$k]['object'] = 'CmpCallCard';
			$tmp_list[$k]['object_id'] = $row['CmpCallCard_id'];
			$tmp_list[$k]['EvnClass_id'] = 990;
			$tmp_list[$k]['EvnClass_Name'] = 'Вызов скорой помощи';
		}
		$res['data'] = array_merge($res['data'], $tmp_list);


		foreach ($res['data'] as &$row) {

			$row['cls'] = $this->getEvnCls($row);

			if (empty($row['children'])) {
				$row['children'] = array('baloons'=>array(
					'cls' => ''
				));
			}
			$this->getBallonCls($row['children']);

			$row['balooncls'] = $this->getBallonGroupCls($row['children']);

			//Если случай нельзя редактировать, и нельзя добавлять в него посещения то у него серая иконка (class='locked')
			if ($row['accessType'] == 'view' && isset($row['canCreateVizit']) && $row['canCreateVizit'] == 0) {
				$row['balooncls'] .= 'locked ';
			}

			if (in_array($row['object'], ['CmpCloseCard', 'CmpCallCard', 'EvnPS']) && !strpos($row['balooncls'], 'locked')) {
				$row['balooncls'] .= 'locked ';
			}
		}

		$dates = array();
		foreach ($res['data'] as $k => $r) {
			$date = $r['objectSetDate'];
			if (!empty($r['objectSetTime'])) {
				$date .= ' ' . $r['objectSetTime'];
			}
			$dates[$k] = strtotime($date);
		}
		
		array_multisort($dates, SORT_DESC, $res['data']);
		
		return $res;
	}

	/**
	 * условие на содержание класса в качестве элемента массива
	 */
	function isSetEvnClass($class, $array) {
		return isset($array[$class]);
	}

	/**
	 * условие на содержание класса в качестве ключа массива
	 */
	function inArrayEvnClass($class, $array) {
		return in_array($class, $array);
	}

	/**
	 * история болезни с поддержкой оффлайн режима для АПИ
	 */
	function getPersonHistoryForApi($data) {
		$this->load->model('Evn_model', 'Evn_model');
		$res = array('data' => array());

		$class_list = $this->Evn_model->getPersonEvnClassList(array(
			'Person_id' => $data['Person_id'],
			'Evn_pid' => null,
			'ignoreFilterByEvnPid' => true,
			'person_in' => !empty($data['person_in']) ? $data['person_in'] : null
		));

		// сюда положим функцию поиска класса
		$searchEvnClass = 'inArrayEvnClass';
		$isOffline = false;

		if (!empty($data['person_in']) && !empty($class_list)) {

			foreach ($class_list as $key => $person_list) {

				// собираем айдишники для фильтра по каждому классу
				$class_list[$key] = implode(',',$person_list);
			}

			// переопределим функцию поиска класса
			$searchEvnClass = 'isSetEvnClass';

			// ставим признак что это оффлайн режим
			$isOffline = true;
		}

		if ($this->$searchEvnClass('EvnPL', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnPL'];

			$tmp_list = $this->getPersonHistoryEvnPL($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if ($this->$searchEvnClass('EvnPS', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnPS'];

			$tmp_list = $this->getPersonHistoryEvnPS($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if ($this->$searchEvnClass('EvnPLDisp', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnPLDisp'];

			$tmp_list = $this->getPersonHistoryEvnPLDisp($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if ($this->$searchEvnClass('EvnUslugaPar', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnUslugaPar'];

			$tmp_list_lis = array();
			if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$tmp_list_lis = $this->lis->GET('EvnUsluga/ParPersonHistory', $data, 'list');
			}
			if ($this->isSuccessful($tmp_list_lis)) {
				$tmp_list = $this->getPersonHistoryEvnUslugaPar($data, $tmp_list_lis);
				$res['data'] = array_merge($res['data'], $tmp_list, $tmp_list_lis);
			}
		}

		if ($this->$searchEvnClass('EvnVK', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnVK'];

			$tmp_list = $this->getPersonHistoryEvnVK($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if ($this->$searchEvnClass('EvnStick', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnStick'];

			$tmp_list = $this->getPersonHistoryEvnStick($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if ($this->$searchEvnClass('EvnDirection', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnDirection'];

			$tmp_list_lis = array();
			if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$tmp_list_lis = $this->lis->GET('EvnDirection/PersonHistory', $data, 'list');
			}
			if ($this->isSuccessful($tmp_list_lis)) {
				$tmp_list = $this->getPersonHistoryEvnDirection($data, $tmp_list_lis);
				$res['data'] = array_merge($res['data'], $tmp_list, $tmp_list_lis);
			}
		}

		if ($this->$searchEvnClass('EvnRecept', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnRecept'];

			$tmp_list = $this->getPersonHistoryEvnRecept($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		if ($this->$searchEvnClass('EvnUslugaTelemed', $class_list)) {

			// для каждого класса определяем свой person_in
			if ($isOffline) $data['person_in'] = $class_list['EvnUslugaTelemed'];

			$tmp_list = $this->getPersonHistoryEvnUslugaTelemed($data);
			$res['data'] = array_merge($res['data'], $tmp_list);
		}

		$tmp_list = $this->GetCmpCallCard($data);
		foreach($tmp_list as $k => $row) {
			$tmp_list[$k]['object'] = 'CmpCallCard';
			$tmp_list[$k]['object_id'] = $row['CmpCallCard_id'];
			$tmp_list[$k]['EvnClass_id'] = 990;
			$tmp_list[$k]['EvnClass_Name'] = 'Вызов скорой помощи';
		}

		$res['data'] = array_merge($res['data'], $tmp_list);
		foreach ($res['data'] as &$row) { if (empty($row['children'])) $row['children'] = array(); }

		$dates = array();

		foreach ($res['data'] as $k => $r) {
			$date = $r['objectSetDate'];
			if (!empty($r['objectSetTime'])) {
				$date .= ' ' . $r['objectSetTime'];
			}
			$dates[$k] = strtotime($date);
		}
		array_multisort($dates, SORT_DESC, $res['data']);
		if (!empty($res['data'])) $res = $res['data'];
		else $res = array();// #106230#178 7 Дата в дате, когда нет ни одного случая

		return $res;
	}
	
	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnPL($data) {
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		list($accessType, $accessParams) = $this->getEvnPLAccessType($data);
		$queryParams = array_merge($queryParams, $accessParams);

		$filter = " (1=1) "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id ";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and ISNULL(epl.EvnPL_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and ISNULL(epl.EvnPL_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}
		
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('epl.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}

		$queryParams['curArm'] = 'EvnPL';
		if (isset($data['session']['CurARM']['ARMType']) && in_array($data['session']['CurARM']['ARMType'], ['stom', 'stom6'])) {
			$queryParams['curArm'] .= 'Stom';
		}

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				case when EPL.Lpu_id = :Lpu_id and epl.EvnPL_IsFinish != 2 and ec.EvnClass_SysNick = :curArm then 1 else 0 end as canCreateVizit,
				convert(varchar(10), epl.EvnPL_setDate, 104) as objectSetDate,
				epl.EvnPL_setTime as objectSetTime,
				convert(varchar(10), epl.EvnPL_disDate, 104) as objectDisDate,
				ec.EvnClass_SysNick as object,
				epl.EvnClass_id,
				epl.LpuSection_id,
				epl.MedPersonal_id,
				ec.EvnClass_Name,
				epl.EvnPL_id as Evn_id,
				epl.EvnPL_id as object_id,
				epl.EvnPL_IsFinish as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				d.Diag_Code,
				d.Diag_Name,
				'ambulant' as EvnType,
				null as hide,
				case when esb.EvnStickBase_id is not null then 1 else 0 end as HasOpenEvnStick
				{$select}
			from
				v_EvnPL epl (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
				left join v_Diag d (nolock) on d.Diag_id = epl.Diag_id
				outer apply (
					select top 1
						esb.EvnStickBase_id
					from
						v_EvnStickBase esb with (nolock)
					where
						esb.EvnStickBase_mid = epl.EvnPL_id
						and esb.EvnStickBase_disDT is null
				) esb
			where
				{$filter}
			order by 
				isnull(epl.EvnPL_disDate,epl.EvnPL_setDate) desc
		";
		$res = $this->queryResult($sql, $queryParams);
		if(count($res)) {
			$this->getPersonHistoryEvnVisitPL($res, $data);
		}
		return $res;
	}

	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnPLDisp($data) {

		$accessType = 'epl.Lpu_id = :Lpu_id';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter = " (1=1) "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id ";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and ISNULL(epl.EvnPLDisp_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and ISNULL(epl.EvnPLDisp_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}
		
		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), epl.EvnPLDisp_setDate, 104) as objectSetDate,
				epl.EvnPLDisp_setTime as objectSetTime,
				convert(varchar(10), epl.EvnPLDisp_disDate, 104) as objectDisDate,
				ec.EvnClass_SysNick as object,
				epl.EvnClass_id,
				ec.EvnClass_Name,
				epl.EvnPLDisp_id as Evn_id,
				epl.EvnPLDisp_id as object_id,
				epl.EvnPLDisp_fid as object_fid,
				epl.EvnPLDisp_IsFinish as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				null as Diag_Code,
				null as Diag_Name,
				case 
					when ec.EvnClass_SysNick = 'EvnPLDispDop13' then 'Диспансеризация'
					when ec.EvnClass_SysNick = 'EvnPLDispProf' then 'Профосмотр'
					else ec.EvnClass_Name
				end as EmkTitle,
				'disp' as EvnType,
				null as hide
				{$select}
			from
				v_EvnPLDisp epl (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
			where
				{$filter}
			order by 
				isnull(epl.EvnPLDisp_disDate,epl.EvnPLDisp_setDate) desc
		";
		
		$res = $this->queryResult($sql, $data);
		
		
		//далее проведем чистку:
		//уберем карты 1 этапа, имеющие продолжение во 2 этапе
		$ids = array();
		$del = array();
		foreach($res as $row) if($row['object_id']) {//пул всех id
			$ids[] = $row['object_id'];
		}
		foreach($res as $row) {//пул тех id, у которых есть предки
			if(in_array($row['object_fid'], $ids)) $del[] = $row['object_fid'];
		}
		foreach($res as $key => $row) {
			if(in_array($row['object_id'], $del)) unset($res[$key]);
		}
		
		return $res;
	}
	
	/**
	 * Список отказов пациента от диспансеризации за этот год
	 */
	function GetDispRefuse($data) {
		$sql = "
		declare @year int
			set @year = YEAR(dbo.tzGetDate())
			
		select
			'view' as accessType,
			convert(varchar(10), DR.DispRefuse_setDT, 104) as objectSetDate,
			DR.DispRefuse_setDT as setDate,
			convert(varchar(5), cast(DR.DispRefuse_setDT as time), 108) as objectSetTime,
			null as objectDisDate,
			null as disDate,
			'DispRefuse' as object,
			8 as EvnClass_id,
			'DispRefuse' as EvnClass_Name,
			null as Evn_id,
			null as object_id,
			null as object_fid,
			null as IsFinish,
			L.Lpu_Nick,
			L.Lpu_id,
			msf.Person_Fin,
			null as Diag_Code,
			null as Diag_Name,
			case 
				when DR.DispClass_id = 1 then 'Отказ от диспансеризации 1 этапа'
				when DR.DispClass_id = 2 then 'Отказ от диспансеризации 2 этапа'
				when DR.DispClass_id = 5 then 'Отказ от профосмотра'
				else ''
			end as EmkTitle,
			'' as EvnType,
			null as hide
		from
			v_DispRefuse DR (nolock)
			left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = DR.MedStaffFact_id
			left join v_Lpu L (nolock) on L.Lpu_id = msf.Lpu_id
		where 
			DR.Person_id = :Person_id AND YEAR(DR.DispRefuse_setDT) = @year
		order by DR.DispRefuse_setDT desc";
		//exit(getDebugSQL($sql, $data));
		$res = $this->queryResult($sql, $data);
		return $res;
	}

	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnVK($data) {

		$accessType = 'epl.Lpu_id = :Lpu_id';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter = " (1=1) "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id ";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and ISNULL(epl.EvnVK_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and ISNULL(epl.EvnVK_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}
		
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('epl.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), epl.EvnVK_setDate, 104) as objectSetDate,
				epl.EvnVK_setTime as objectSetTime,
				convert(varchar(10), epl.EvnVK_disDate, 104) as objectDisDate,
				ec.EvnClass_SysNick as object,
				epl.EvnClass_id,
				ec.EvnClass_Name,
				epl.EvnVK_id as Evn_id,
				epl.EvnVK_id as object_id,
				null as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				null as Diag_Code,
				null as Diag_Name,
				ISNULL(d.Diag_Code,'')+' '+'Протокол ВК №'+ISNULL(epl.EvnVK_NumProtocol,'') as EmkTitle,
				'vk' as EvnType,
				null as hide,
				2 as isVK,
				epl.EvnVK_NumProtocol AS number
				{$select}
			from
				v_EvnVK epl (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
				left join v_Diag d with (nolock) on epl.Diag_id = d.Diag_id
			where
				{$filter}
			order by 
				isnull(epl.EvnVK_disDate,epl.EvnVK_setDate) desc
		";
		$res = $this->queryResult($sql, $data);
		return $res;
	}

	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnStick($data) {

		$accessType = 'epl.Lpu_id = :Lpu_id';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter = " (1=1) "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id ";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and ISNULL(epl.EvnStick_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and ISNULL(epl.EvnStick_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), epl.EvnStick_setDate, 104) as objectSetDate,
				epl.EvnStick_setTime as objectSetTime,
				convert(varchar(10), epl.EvnStick_disDate, 104) as objectDisDate,
				ec.EvnClass_SysNick as object,
				epl.EvnClass_id,
				ec.EvnClass_Name,
				epl.EvnStick_id as Evn_id,
				epl.EvnStick_id as object_id,
				null as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				null as Diag_Code,
				null as Diag_Name,
				ec.EvnClass_Name as EmkTitle,
				'stick' as EvnType,
				null as hide,
				2 as isDoc,
				epl.EvnStick_Num as number
				{$select}
			from
				v_EvnStick epl (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
			where
				{$filter}
			order by 
				isnull(epl.EvnStick_disDate,epl.EvnStick_setDate) desc
		";
		$res = $this->queryResult($sql, $data);
		return $res;
	}

	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnDirection($data, $excepts) {

		$accessType = 'epl.Lpu_id = :Lpu_id';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter = " (1=1) ";
		$select = "";

		$except_ids = array();
		foreach($excepts as $except) {
			if (!empty($except['Evn_id'])) {
				$except_ids[] = $except['Evn_id'];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$filter .= " and epl.EvnDirection_id not in ({$except_ids})";
		}

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id ";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and ISNULL(epl.EvnDirection_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and ISNULL(epl.EvnDirection_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}
		
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}

		// только не обслуженные, т.е. те направления, назначения по которым не выполнены
		// @todo уточнить определение не обслуженных направлений
		$filterWithoutService = '
		(
			isnull(epl.EvnStatus_id,0) not in (15) 
			OR exists (
				select top 1 epd.EvnDirection_id from v_EvnPrescrDirection epd with (nolock)
				inner join v_EvnPrescr EP with (NOLOCK) on epd.EvnPrescr_id = EP.EvnPrescr_id
				where epl.EvnDirection_id = epd.EvnDirection_id
				and isnull(EP.EvnPrescr_IsExec, 1) = 1
			)
		)';
		// только не обслуженные электронные направления без признака "создано автоматически".
		$filterOnlyEl = '(isnull(epl.EvnDirection_IsAuto, 1) = 1 and ' . $filterWithoutService . ')';
		// необходимо отображение как направлений, привязанных к случаю - не отмененные, системные и электронные
		// так и непривязанных - только не обслуженные и не отмененные электронные направления без признака "создано автоматически".
		$filterWithParentOrWithout = '(epl.EvnDirection_pid is not null OR ' . $filterOnlyEl . ')';

		if (!empty($data['Person_id']) && $data['Person_id'] > 0 && !empty($data['DirType_id']) && $data['DirType_id'] > 0 && !empty($data['type']) && 1 == $data['type']) {
			// с фильтром по типу направления при отображении дерева в ЭМК в виде "по событиям"
			$filter .= ' and  epl.DirType_id=:DirType_id and ' . $filterWithParentOrWithout;
			$params['DirType_id'] = $data['DirType_id'];
		}

		if (!empty($data['Person_id']) && $data['Person_id'] > 0 && !empty($data['type']) && 1 == $data['type']) {
			// При отображении дерева в ЭМК в виде "по событиям" только с фильтром по человеку
			$filter .= ' and ' . $filterWithParentOrWithout;
		}

		// В дереве в принципе не отображать отмененные/отклоненные, не надо перегружать дерево
		$filter .= " and epl.EvnDirection_failDT is null
		and epl.DirFailType_id is null
		and isnull(epl.EvnStatus_id,0) not in (12,13)
		";

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), epl.EvnDirection_setDate, 104) as objectSetDate,
				epl.EvnDirection_setTime as objectSetTime,
				convert(varchar(10), epl.EvnDirection_disDate, 104) as objectDisDate,
				ISNULL(EPM.EvnClass_SysNick, ec.EvnClass_SysNick) as object,
				epl.EvnClass_id,
				ec.EvnClass_Name,
				DirType.DirType_id,
				LOWER(RTRIM(DirType.DirType_Name)) as DirType_Name,
				epl.EvnDirection_id as Evn_id,
				ISNULL(EPM.EvnPrescrMse_id, epl.EvnDirection_id) as object_id,
				null as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				null as Diag_Code,
				null as Diag_Name,
				ec.EvnClass_Name as EmkTitle,
				'direction' as EvnType,
				null as hide,
				2 as isDoc,
				epl.EvnDirection_Num AS number,
				lsp.LpuSectionProfile_Name,
				convert(varchar(10), epl.EvnDirection_statusDate, 104) as EvnDirection_statusDate,
				EvnStatus.EvnStatus_Name
				{$select}
			from
				v_EvnDirection_all epl (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
				left join v_Diag d with (nolock) on d.Diag_id = epl.Diag_id
				left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = epl.LpuSectionProfile_id
				left join v_DirType DirType with (nolock) on DirType.DirType_id=epl.DirType_id
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = epl.EvnStatus_id
				-- направление МСЭ
				outer apply (
					select top 1 EPM.EvnPrescrMse_id, ecm.EvnClass_SysNick
					from v_EvnPrescrMse EPM with (nolock)
						inner join EvnClass ecm with (nolock) on ecm.EvnClass_id = EPM.EvnClass_id
					where epl.EvnQueue_id = EPM.EvnQueue_id
				) EPM
			where
				{$filter}
			order by 
				isnull(epl.EvnDirection_disDate,epl.EvnDirection_setDate) desc
		";
		$res = $this->queryResult($sql, $data);
		return $res;
	}

	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnRecept($data) {

		$accessType['v_EvnRecept'] = 'epl.Lpu_id = :Lpu_id';
		$accessType['v_EvnReceptGeneral'] = 'erg.Lpu_id = :Lpu_id';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType['v_EvnRecept'] .= " and :userLpuUnitType_SysNick = 'polka'";
			$accessType['v_EvnReceptGeneral'] .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter['v_EvnRecept'] = " (1=1) ";$filter['v_EvnReceptGeneral'] = " (1=1) ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter['v_EvnRecept'] .= " and epl.Person_id in ({$data['person_in']}) ";
			$filter['v_EvnReceptGeneral'] .= " and erg.Person_id in ({$data['person_in']}) ";
		} else {
			$filter['v_EvnRecept'] .= " and epl.Person_id = :Person_id ";
			$filter['v_EvnReceptGeneral'] .= " and erg.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter['v_EvnRecept'] .= " and ISNULL(epl.EvnRecept_IsArchive, 1) = 1";
			$filter['v_EvnReceptGeneral'] .= " and ISNULL(erg.EvnReceptGeneral_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter['v_EvnRecept'] .= " and ISNULL(epl.EvnRecept_IsArchive, 1) = 2";
			$filter['v_EvnReceptGeneral'] .= " and ISNULL(erg.EvnReceptGeneral_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}
		
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter['v_EvnRecept'] .= " and $diagFilter";
			$filter['v_EvnReceptGeneral'] .= " and $diagFilter";
		}
		$lpuFilter['v_EvnRecept'] = getAccessRightsLpuFilter('epl.Lpu_id');
		$lpuFilter['v_EvnReceptGeneral'] = getAccessRightsLpuFilter('erg.Lpu_id');
		if (!empty($lpuFilter['v_EvnRecept']) && !empty($lpuFilter['v_EvnReceptGeneral'])) {
			$filter['v_EvnRecept'] .= " and {$lpuFilter['v_EvnRecept']}";
			$filter['v_EvnReceptGeneral'] .= " and {$lpuFilter['v_EvnReceptGeneral']}";
		}

		$sql = "
			select *
			from 
			(
			select
				case when {$accessType['v_EvnRecept']} then 'edit' else 'view' end as accessType,
				convert(varchar(10), epl.EvnRecept_setDate, 104) as objectSetDate,
				epl.EvnRecept_setTime as objectSetTime,
				convert(varchar(10), epl.EvnRecept_disDate, 104) as objectDisDate,
				ec.EvnClass_SysNick as object,
				epl.EvnClass_id,
				ec.EvnClass_Name,
				epl.EvnRecept_id as Evn_id,
				epl.EvnRecept_id as object_id,
				null as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				null as Diag_Code,
				null as Diag_Name,
				ec.EvnClass_Name as EmkTitle,
				'recept' as EvnType,
				null as hide,
				2 as isDoc,
				epl.EvnRecept_Num AS number,
				epl.EvnRecept_disDate,
				epl.EvnRecept_setDate,
				DCM.DrugComplexMnn_RusName
			from
				v_EvnRecept epl (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
				left join v_Diag d with (nolock) on d.Diag_id = epl.Diag_id
				left join rls.DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = epl.DrugComplexMnn_id
			where
				{$filter['v_EvnRecept']}
				
			union all 
			
			select
				case when {$accessType['v_EvnReceptGeneral']} then 'edit' else 'view' end as accessType,
				convert(varchar(10), ERGDL.EvnReceptGeneralDrugLink_updDT, 104) as objectSetDate,
				convert(varchar(8), ERGDL.EvnReceptGeneralDrugLink_updDT, 108) as objectSetTime,
				convert(varchar(10), erg.EvnReceptGeneral_disDate, 104) as objectDisDate,
				ec.EvnClass_SysNick as object,
				erg.EvnClass_id,
				ec.EvnClass_Name,
				erg.EvnReceptGeneral_id as Evn_id,
				erg.EvnReceptGeneral_id as object_id,
				null as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				null as Diag_Code,
				null as Diag_Name,
				ec.EvnClass_Name as EmkTitle,
				'recept' as EvnType,
				null as hide,
				2 as isDoc,
				erg.EvnReceptGeneral_Num AS number,
				erg.EvnReceptGeneral_disDate as EvnRecept_disDate,
				ERGDL.EvnReceptGeneralDrugLink_updDT as EvnRecept_setDate,
				DCM.DrugComplexMnn_RusName
			from
				v_EvnReceptGeneral erg (nolock)
				inner join v_EvnReceptGeneralDrugLink ERGDL with (nolock) on ERGDL.EvnReceptGeneral_id = erg.EvnReceptGeneral_id
				inner join v_EvnCourseTreatDrug ECTD with (nolock) on ECTD.EvnCourseTreatDrug_id = ERGDL.EvnCourseTreatDrug_id
				inner join v_Lpu l (nolock) on l.Lpu_id = erg.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = erg.EvnClass_id
				left join rls.DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = ECTD.DrugComplexMnn_id
				left join v_Diag d with (nolock) on d.Diag_id = erg.Diag_id
			where
				{$filter['v_EvnReceptGeneral']}
			) as recepts
			order by 
				isnull(recepts.EvnRecept_disDate,recepts.EvnRecept_setDate) desc
		";

		$res = $this->queryResult($sql, $data);
		return $res;
	}

	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnUslugaTelemed($data) {

		$accessType = 'epl.Lpu_id = :Lpu_id';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter = " (1=1) ";  $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id ";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and ISNULL(epl.EvnUslugaTelemed_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and ISNULL(epl.EvnUslugaTelemed_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), epl.EvnUslugaTelemed_setDate, 104) as objectSetDate,
				epl.EvnUslugaTelemed_setTime as objectSetTime,
				convert(varchar(10), epl.EvnUslugaTelemed_disDate, 104) as objectDisDate,
				ec.EvnClass_SysNick as object,
				epl.EvnClass_id,
				ec.EvnClass_Name,
				epl.EvnUslugaTelemed_id as Evn_id,
				epl.EvnUslugaTelemed_id as object_id,
				null as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				null as Diag_Code,
				null as Diag_Name,
				uc.UslugaComplex_Name as EmkTitle,
				'evnuslugatelemed' as EvnType,
				null as hide
				{$select}
			from
				v_EvnUslugaTelemed epl (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = epl.UslugaComplex_id
			where
				{$filter}
			order by 
				isnull(epl.EvnUslugaTelemed_disDate,epl.EvnUslugaTelemed_setDate) desc
		";
		$res = $this->queryResult($sql, $data);
		return $res;
	}

	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnUslugaPar($data, $excepts = array()) {

		$accessType = 'epl.Lpu_id = :Lpu_id';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter = " (1=1) "; $select = "";

		$except_ids = array();
		foreach($excepts as $except) {
			if (!empty($except['Evn_id'])) {
				$except_ids[] = $except['Evn_id'];
			}
		}
		if (count($except_ids) > 0) {
			$except_ids = implode(",", $except_ids);
			$filter .= " and epl.EvnUslugaPar_id not in ({$except_ids})";
		}

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id ";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and ISNULL(epl.EvnUslugaPar_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and ISNULL(epl.EvnUslugaPar_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), epl.EvnUslugaPar_setDate, 104) as objectSetDate,
				epl.EvnUslugaPar_setTime as objectSetTime,
				convert(varchar(10), epl.EvnUslugaPar_disDate, 104) as objectDisDate,
				ec.EvnClass_SysNick as object,
				epl.EvnClass_id,
				ec.EvnClass_Name,
				epl.EvnUslugaPar_id as Evn_id,
				epl.EvnUslugaPar_id as object_id,
				null as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				null as Diag_Code,
				null as Diag_Name,
				uc.UslugaComplex_Name as EmkTitle,
				'par' as EvnType,
				null as hide
				{$select}
			from
				v_EvnUslugaPar epl (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = epl.UslugaComplex_id
			where
				{$filter}
				and epl.EvnUslugaPar_setDT is not null
				and epl.EvnUslugaPar_pid is null
			order by
				isnull(epl.EvnUslugaPar_disDate,epl.EvnUslugaPar_setDate) desc
		";
		$res = $this->queryResult($sql, $data);
		return $res;
	}
	
	/**
	 * посещения
	 */
	function getPersonHistoryEvnVisitPL(&$epl, $data) {
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		list($accessType, $accessParams) = $this->getEvnVizitPLAccessType($data);
		$queryParams = array_merge($queryParams, $accessParams);

		foreach($epl as $e => &$j) {
			$j['children'] = array();
		}

		$filter = " (1=1) ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and evpl.Person_id in ({$data['person_in']}) ";
		} else {
			$filter .= " and evpl.Person_id = :Person_id ";
		}
		
		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				evpl.EvnClass_id,
				evpl.EvnVizitPL_pid as Evn_pid,
				evpl.EvnVizitPL_id as Evn_id,
				convert(varchar(10), evpl.EvnVizitPL_setDate, 104) as objectSetDate,
				evpl.EvnVizitPL_setTime as objectSetTime,
				evpl.MedStaffFact_id,
			    evpl.ProfGoal_id,
				lsp.LpuSectionProfile_Name,
				d.Diag_Code,
				d.Diag_Name
			from
				v_EvnVizitPL evpl (nolock)
				left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = evpl.LpuSectionProfile_id
				left join v_Diag d (nolock) on d.Diag_id = evpl.Diag_id
				outer apply (
					select
						LpuSection_id
					from
						v_MedStaffFact SMP
					where
						SMP.MedStaffFact_id = :MedStaffFact_id
				) SMP
			where
				{$filter}
			order by 
				evpl.EvnVizitPL_setDate desc,
				evpl.EvnVizitPL_setTime desc
		";
		$res = $this->queryResult($sql, $queryParams);
		foreach($res as $k => $r) {
			foreach($epl as $e => &$j) {
				if($j['Evn_id'] == $r['Evn_pid']) {
					$j['children'][] = $r;
				}
			}
		}
	}
	
	/**
	 * история болезни в формате для новой ЭМК
	 */
	function getPersonHistoryEvnPS($data) {
		
		$accessType = 'eps.Lpu_id = :Lpu_id';
		
		//врач может редактировать, если он работает в стационаре
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'],array('polka','stac','dstac','hstac','pstac','parka')) )
		{
			$accessType .= " and :userLpuUnitType_SysNick in ('stac','dstac','hstac','pstac')";
		}

		$filter = " (1=1) "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and eps.Person_id in ({$data['person_in']}) ";
			$select = " ,eps.Person_id ";
		} else {
			$filter .= " and eps.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and ISNULL(eps.EvnPS_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and ISNULL(eps.EvnPS_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}
		
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('eps.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}
		
		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), eps.EvnPS_setDate, 104) as objectSetDate,
				eps.EvnPS_setTime as objectSetTime,
				convert(varchar(10), isnull(eps.EvnPS_disDate,eps.EvnPS_setDate), 104) as objectDisDate,
				'EvnPS' as object,
				eps.EvnClass_id,
				ec.EvnClass_Name,
				eps.EvnPS_id as Evn_id,
				eps.EvnPS_id as object_id,
				case when eps.EvnPS_disDT is null then 1 else 2 end as IsFinish,
				l.Lpu_Nick,
				l.Lpu_id,
				d.Diag_Code,
				d.Diag_Name,
				eps.EvnPS_RFID,
				'hospital' as EvnType,
				null as hide
				{$select}
			from
				v_EvnPS eps (nolock)
				inner join v_Lpu l (nolock) on l.Lpu_id = eps.Lpu_id
				inner join EvnClass ec (nolock) on ec.EvnClass_id = eps.EvnClass_id
				left join v_Diag d (nolock) on d.Diag_id = eps.Diag_id
			where
				{$filter}
		";
		$res = $this->queryResult($sql, $data);
		if(count($res)) {
			// $this->getPersonHistoryEvnSection($res, $data);
		}
		return $res;
	}
	
	/**
	 * движения
	 */
	function getPersonHistoryEvnSection(&$eps, $data) {
		
		foreach($eps as $e => &$j) {
			$j['children'] = array();
		}

		$filter = " (1=1) ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and evs.Person_id in ({$data['person_in']}) ";
		} else {
			$filter .= " and evs.Person_id = :Person_id ";
		}
		
		$sql = "
			select
				evs.EvnClass_id,
				evs.EvnSection_pid as Evn_pid,
				evs.EvnSection_id as Evn_id,
				convert(varchar(10), evs.EvnSection_setDate, 104) as objectSetDate,
				evs.EvnSection_setTime as objectSetTime,
				convert(varchar(10), evs.EvnSection_disDate, 104) as objectDisDate,
				evs.MedStaffFact_id
			from
				v_EvnSection evs (nolock)
			where
				{$filter}
				/*and isnull(evs.EvnSection_IsPriem, 1) = 1*/
			order by 
				evs.EvnSection_setDate desc
		";
		$res = $this->queryResult($sql, array('Person_id' => $data['Person_id']));
		foreach($res as $k => $r) {
			foreach($eps as $e => &$j) {
				if($j['Evn_id'] == $r['Evn_pid']) {
					$j['children'][] = $r;
					continue;
				}
			}
		}
	}
	
	/**
	 * пишем стили для балунов
	 */
	function getBallonGroupCls($data) {
		$cls = array();
		foreach($data as $row) {
			if (!empty($row['objectDisDate'])) {
				$cls[] = 'long ';
			}
		}
		$cls = array_unique($cls);
		return join(' ', $cls);
	}
	
	/**
	 * пишем стили для балунов
	 */
	function getBallonCls(&$rows) {
		foreach($rows as &$row) {
			if (!is_array($row)) {
				return array();
			}
			$cls = '';
			if (isset($row['accessType']) && $row['accessType'] == 'edit') {
				$cls .= 'my ';
			}
			$row['cls'] = $cls;
		}
		return true;
	}
	
	/**
	 * пишем стили для балунов
	 */
	function getEvnCls($row) {
		$cls = '';
		switch($row['object']) {
			case 'EvnPLDispDop13': // диспансеризация
			case 'EvnPLDispProf': // диспансеризация
			case 'EvnPLDispOrp': // диспансеризация
			case 'EvnPLDispTeenInspection': // диспансеризация
				$cls .= 'disp ';
				break;
			case 'DispRefuse':
				$cls.= 'disprefuse ';
				break;
			case 'EvnPLDispDriver': // диспансеризация
				$cls .= 'driver ';
				break;
			case 'EvnPLDispMigrant': // диспансеризация
				$cls .= 'migrant ';
				break;
			case 'EvnPLDispScreenOnko': // онкоскрининг
				$cls .= 'onkoscreen ';
				break;
			case 'EvnUslugaTelemed':
			case 'EvnUslugaPar':
				$cls .= 'par ';
				break;
			case 'EvnVK':
				$cls .= 'vk ';
				break;
			case 'EvnPL': // тап
				$cls .= 'ambulant ';
				break;
			case 'EvnPLStom': // стомат
				$cls .= 'dentist ';
				break;
			case 'EvnPS': // квс
				$cls .= 'hospital ';
				break;
			case 'CmpCloseCard': // смп
			case 'CmpCallCard': // смп
				$cls .= 'smp ';
				break;
		}
		if (isset($row['IsFinish']) && $row['IsFinish'] != 2) {
			$cls .= 'unclosed ';
		}
 		return $cls;
	}

	/**
	 * Формирование accessType для ТАП
	 */
	function getEvnPLAccessType($data)
	{
		$accessParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => !empty($data['session']['CurLpuSection_id']) ? $data['session']['CurLpuSection_id'] : null
		);

		$accessType = 'case
				when EPL.Lpu_id = :Lpu_id and :LpuSection_id in (select EV.LpuSection_id
					from v_EvnVizitPL EV with (nolock)
					where EV.EvnVizitPL_pid = epl.EvnPL_id ) then 1
				else 0
			end = 1';

		if (getRegionNick() == 'ekb') {
			$accessType .= " and ISNULL(EPL.EvnPL_IsPaid, 1) = 1";
		}

		if (isset($data['session']['CurMedStaffFact_id'])) {
			// получаем LpuUniType_SysNick
			$resp = $this->queryResult("
				select
					LU.LpuUnitType_SysNick
				from
					v_MedStaffFact msf (nolock)
					left join v_LpuUnit LU (nolock) on MSF.LpuUnit_id = LU.LpuUnit_id
				where
					MSF.MedStaffFact_id = :MedStaffFact_id
			", array(
				'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
			));

			if (empty($resp[0]['LpuUnitType_SysNick']) || !in_array($resp[0]['LpuUnitType_SysNick'], array('polka', 'ccenter', 'traumcenter', 'fap'))) {
				$accessType .= " and 1=0";
			}
		}

		return array($accessType, $accessParams);
	}

	/**
	 * Формирование accessType для стомат. ТАП
	 */
	function getEvnPLStomAccessType($data)
	{
		$accessParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => !empty($data['session']['CurLpuSection_id']) ? $data['session']['CurLpuSection_id'] : null
		);

		$accessType = 'case
				when EPLS.Lpu_id = :Lpu_id and :LpuSection_id in (select EV.LpuSection_id
					from v_EvnVizitPLStom EV with (nolock)
					where EV.EvnVizitPLStom_pid = epls.EvnPLStom_id ) then 1
				else 0
			end = 1';

		if (getRegionNick() == 'ekb') {
			$accessType .= " and ISNULL(EPLS.EvnPLStom_IsPaid, 1) = 1";
		}

		if (isset($data['session']['CurMedStaffFact_id'])) {
			// получаем LpuUniType_SysNick
			$resp = $this->queryResult("
				select
					LU.LpuUnitType_SysNick
				from
					v_MedStaffFact msf (nolock)
					left join v_LpuUnit LU (nolock) on MSF.LpuUnit_id = LU.LpuUnit_id
				where
					MSF.MedStaffFact_id = :MedStaffFact_id
			", array(
				'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
			));

			if (empty($resp[0]['LpuUnitType_SysNick']) || !in_array($resp[0]['LpuUnitType_SysNick'], array('polka', 'ccenter', 'traumcenter', 'fap'))) {
				$accessType .= " and 1=0";
			}
		}

		return array($accessType, $accessParams);
	}

	/**
	 * Формирование accessType для посещения
	 */
	function getEvnVizitPLAccessType($data)
	{
		$accessParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null
		);
		$accessType = '
			case
				when EVPL.Lpu_id = :Lpu_id and (EVPL.LpuSection_id = SMP.LpuSection_id OR EVPL.MedStaffFact_sid = :MedStaffFact_id) then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EVPL.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EVPL.EvnVizitPL_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		if (getRegionNick() == 'pskov') {
			$accessType .= " and ISNULL(EVPL.EvnVizitPL_IsPaid, 1) = 1
			 	and not exists(
					select top 1 RD.Registry_id
					from r60.v_RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EVPL.EvnVizitPL_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		if ($data['session']['isMedStatUser'] == false && count($med_personal_list) > 0) {
			$accessType .= ' and exists (select top 1 MedStaffFact_id from v_MedStaffFact with (nolock) where MedPersonal_id in (' . implode(',', $med_personal_list) . ') and LpuSection_id = EVPL.LpuSection_id and WorkData_begDate <= EVPL.EvnVizitPL_setDate and (WorkData_endDate is null or WorkData_endDate >= EVPL.EvnVizitPL_setDate))';
		}

		return array($accessType, $accessParams);
	}

	/**
	 * Формирование accessType для стомат. посещения
	 */
	function getEvnVizitPLStomAccessType($data)
	{
		$accessParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null
		);
		$accessType = '
			case
				when evpls.Lpu_id = :Lpu_id and (evpls.LpuSection_id = SMP.LpuSection_id OR evpls.MedStaffFact_sid = :MedStaffFact_id) then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when evpls.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(evpls.EvnVizitPLStom_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		if (getRegionNick() == 'pskov') {
			$accessType .= " and ISNULL(evpls.EvnVizitPLStom_IsPaid, 1) = 1
			 	and not exists(
					select top 1 RD.Registry_id
					from r60.v_RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = evpls.EvnVizitPLStom_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		if ($data['session']['isMedStatUser'] == false && count($med_personal_list) > 0) {
			$accessType .= ' and exists (select top 1 MedStaffFact_id from v_MedStaffFact with (nolock) where MedPersonal_id in (' . implode(',', $med_personal_list) . ') and LpuSection_id = evpls.LpuSection_id and WorkData_begDate <= evpls.EvnVizitPLStom_setDate and (WorkData_endDate is null or WorkData_endDate >= evpls.EvnVizitPLStom_setDate))';
		}

		return array($accessType, $accessParams);
	}

	/**
	 * Загрузка формы ТАП в новой ЭМК
	 */
	function loadEvnPLForm($data) {
		$queryParams = array(
			'EvnPL_id' => $data['EvnPL_id']
		);

		list($accessType, $accessParams) = $this->getEvnPLAccessType($data);
		$queryParams = array_merge($queryParams, $accessParams);

		$queryParams['curArm'] = 'EvnPL';
		if (isset($data['session']['CurARM']['ARMType']) && in_array($data['session']['CurARM']['ARMType'], ['stom', 'stom6'])) {
			$queryParams['curArm'] .= 'Stom';
		}

		$resp_epl = $this->queryResult("
			select top 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				case when EPL.Lpu_id = :Lpu_id and epl.EvnPL_IsFinish != 2 and ec.EvnClass_SysNick = :curArm then 1 else 0 end as canCreateVizit,
				epl.EvnPL_id,
				epl.EvnPL_IsSigned,
				epl.Person_id,
				epl.PersonEvn_id,
				epl.Server_id,
				epl.EvnPL_NumCard,
				epl.EvnPL_IsFinish,
				(case when epl.EvnPL_IsFirstDisable = 0 then 'Нет' else (case when epl.EvnPL_IsFirstDisable = 1 then 'Да' else null end) end) as EvnPL_IsFirstDisable,
				(cast(prt.PrivilegeType_Code as varchar)+'. '+prt.PrivilegeType_Name) as PrivilegeType_id,
				d.Diag_id,
				d.Diag_Code,
				d.Diag_Name,
				ifin.YesNo_Name as EvnPL_IsFinishName,
				epl.EvnPL_UKL,
				isr.YesNo_Name as EvnPL_IsSurveyRefuseName,
				rc.ResultClass_Name,
				rc.ResultClass_id,
				ilt.InterruptLeaveType_Name,
				rdt.ResultDeseaseType_Name,
				dt.DirectType_Name,
				dt.DirectType_id,
				dc.DirectClass_Name,
				dc.DirectClass_id,
				ls.LpuSection_FullName,
				lpu.Lpu_Name,
				dl.Diag_Code + '. ' + dl.Diag_Name as Diag_lName,
				dco.Diag_Code + '. ' + dco.Diag_Name as Diag_concName,
				pt.PrehospTrauma_Name,
				pt.PrehospTrauma_id,
				iu.YesNo_Name as EvnPL_IsUnportName,
				lt.LeaveType_Name as LeaveType_fedName,
				lt.LeaveType_id,
				rdtf.ResultDeseaseType_Name as ResultDeseaseType_fedName,
				rdtf.ResultDeseaseType_id,
				EUP.cnt as EvnUslugaParCount,
				ES.cnt + ES2.cnt as EvnStickCount,
				ER.cnt as EvnReceptCount,
				EMD.cnt as EvnMediaDataCount
			from
				v_EvnPL epl (nolock)
				left join v_Lpu lpu (nolock) on epl.Lpu_oid = lpu.Lpu_id
				left join v_evnclass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
				left join v_LpuSection ls (nolock) on epl.LpuSection_oid = ls.LpuSection_id
				left join v_PrivilegeType prt (nolock) on epl.PrivilegeType_id = prt.PrivilegeType_id
				left join v_YesNo ifin (nolock) on ifin.YesNo_id = epl.EvnPL_IsFinish
				left join v_ResultClass rc (nolock) on rc.ResultClass_id = epl.ResultClass_id
				left join v_InterruptLeaveType ilt (nolock) on ilt.InterruptLeaveType_id = epl.InterruptLeaveType_id
				left join v_DirectType dt (nolock) on dt.DirectType_id = epl.DirectType_id
				left join v_DirectClass dc (nolock) on dc.DirectClass_id = epl.DirectClass_id
				left join v_Diag dl (nolock) on dl.Diag_id = epl.Diag_lid
				left join v_Diag dco (nolock) on dco.Diag_id = epl.Diag_concid
				left join v_PrehospTrauma pt (nolock) on pt.PrehospTrauma_id = epl.PrehospTrauma_id
				left join fed.v_LeaveType lt (nolock) on lt.LeaveType_id = epl.LeaveType_fedid
				left join v_ResultDeseaseType rdt (nolock) on rdt.ResultDeseaseType_id = epl.ResultDeseaseType_id
				left join fed.v_ResultDeseaseType rdtf (nolock) on rdtf.ResultDeseaseType_id = epl.ResultDeseaseType_fedid
				left join v_YesNo iu (nolock) on iu.YesNo_id = epl.EvnPL_IsUnport
				left join v_YesNo isr (nolock) on isr.YesNo_id = epl.EvnPL_IsSurveyRefuse
				left join v_Diag d (nolock) on d.Diag_id = epl.Diag_id
				outer apply (
					select
						count(EUP.EvnUslugaPar_id) as cnt
					from
						v_EvnUslugaPar EUP with (nolock)
						left join v_Evn EvnUP with (nolock) on EvnUP.Evn_id = eup.EvnUslugaPar_pid
					where
						EUP.EvnUslugaPar_rid = EPL.EvnPL_id
						and EUP.EvnUslugaPar_setDT is not null
						and ISNULL(EvnUP.EvnClass_SysNick, '') != 'EvnUslugaPar'
				) EUP
				outer apply (
					select
						count(ER.EvnRecept_id) as cnt
					from
						v_EvnRecept er (nolock)
					where
						er.EvnRecept_pid = EPL.EvnPL_id
						and er.Lpu_id = EPL.Lpu_id
				) ER
				outer apply (
					select
						count(ES.EvnStickBase_id) as cnt
					from
						v_EvnStickBase es (nolock)
					where
						es.EvnStickBase_mid = EPL.EvnPL_id
				) ES
				outer apply (
					select
						count(ES.EvnStickBase_id) as cnt
					from
						v_EvnStickBase es (nolock)
					where
						exists (select EvnLink_id from v_EvnLink with (nolock) where Evn_id = EPL.EvnPL_id and Evn_lid = es.EvnStickBase_id)
				) ES2
				outer apply (
					select
						count(EMD.EvnMediaData_id) as cnt
					from
						v_EvnMediaData EMD with (nolock)
					where
						EMD.Evn_id = epl.EvnPL_id
				) EMD
			where
				EvnPL_id = :EvnPL_id
		", $queryParams);

		if (empty($resp_epl[0]['EvnPL_id'])) {
			return false;
		}
		
		$filter = '';
		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('evpl.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}

		$resp_epl[0]['EvnVizitPL'] = $this->queryResult("
			select
				evpl.EvnVizitPL_id,
				convert(varchar(10), evpl.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate
			from
				v_EvnVizitPL evpl (nolock)
				left join v_Diag d with (nolock) on d.Diag_id = evpl.Diag_id
			where
				evpl.EvnVizitPL_pid = :EvnPL_id
				{$filter}
			order by
				evpl.EvnVizitPL_setDT desc
		", array(
			'EvnPL_id' => $data['EvnPL_id']
		));

		if (!empty($resp_epl[0]['EvnPL_id'])) {
			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
			$dbConnection = getRegistryChecksDBConnection();
			if ($dbConnection != 'default') {
				$this->regDB = $this->load->database($dbConnection, true);
				$this->Reg_model->db = $this->regDB;
			}
			if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
				if ($this->Reg_model->checkEvnInRegistry(array(
						'EvnPL_id' => $resp_epl[0]['EvnPL_id'],
						'Lpu_id' => $data['Lpu_id']
					), 'edit') !== false) {
					$resp_epl[0]['accessType'] = 'view';
				}
			} else {
				$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
					'EvnPL_id' => $resp_epl[0]['EvnPL_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session']
				), 'edit');

				if (is_array($registryData)) {
					if (!empty($registryData['Error_Msg'])) {
						$resp_epl[0]['accessType'] = 'view';
						$resp_epl[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
					} elseif (!empty($registryData['Alert_Msg'])) {
						$resp_epl[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
					}
				}
			}
		}

		return $resp_epl;
	}
	
	/**
	 * Загрузка формы стомат. ТАП в новой ЭМК
	 */
	function loadEvnPLStomForm($data) {
		$queryParams = array(
			'EvnPLStom_id' => $data['EvnPLStom_id']
		);

		list($accessType, $accessParams) = $this->getEvnPLStomAccessType($data);
		$queryParams = array_merge($queryParams, $accessParams);

		$queryParams['curArm'] = 'EvnPL';
		if (isset($data['session']['CurARM']['ARMType']) && in_array($data['session']['CurARM']['ARMType'], ['stom', 'stom6'])) {
			$queryParams['curArm'] .= 'Stom';
		}

		$resp_epls = $this->queryResult("
			select top 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				case when epls.Lpu_id = :Lpu_id and epls.EvnPLStom_IsFinish != 2 and ec.EvnClass_SysNick = :curArm then 1 else 0 end as canCreateVizit,
				epls.EvnPLStom_id,
				epls.EvnPLStom_IsSigned,
				epls.Person_id,
				epls.PersonEvn_id,
				epls.Server_id,
				epls.EvnPLStom_NumCard,
				epls.EvnPLStom_IsFinish,
				d.Diag_id,
				d.Diag_Code,
				d.Diag_Name,
				ifin.YesNo_Name as EvnPLStom_IsFinishName,
				epls.EvnPLStom_UKL,
				isr.YesNo_Name as EvnPLStom_IsSurveyRefuseName,
				rc.ResultClass_Name,
				rc.ResultClass_id,
				ilt.InterruptLeaveType_Name,
				rdt.ResultDeseaseType_Name,
				dt.DirectType_Name,
				dt.DirectType_id,
				dc.DirectClass_Name,
				dc.DirectClass_id,
				dl.Diag_Code + '. ' + dl.Diag_Name as Diag_lName,
				dco.Diag_Code + '. ' + dco.Diag_Name as Diag_concName,
				pt.PrehospTrauma_Name,
				pt.PrehospTrauma_id,
				iu.YesNo_Name as EvnPLStom_IsUnportName,
				lt.LeaveType_Name as LeaveType_fedName,
				lt.LeaveType_id,
				rdtf.ResultDeseaseType_Name as ResultDeseaseType_fedName,
				rdtf.ResultDeseaseType_id,
				EUP.cnt as EvnUslugaParCount,
				ES.cnt + ES2.cnt as EvnStickCount,
				ER.cnt as EvnReceptCount,
				EMD.cnt as EvnMediaDataCount,
				IsSan.YesNo_Name as EvnPLStom_IsSanName,
				v_SanationStatus.SanationStatus_Name
			from
				v_EvnPLStom epls (nolock)
				left join v_evnclass ec (nolock) on ec.EvnClass_id = epls.EvnClass_id
				left join v_YesNo ifin (nolock) on ifin.YesNo_id = epls.EvnPLStom_IsFinish
				left join v_ResultClass rc (nolock) on rc.ResultClass_id = epls.ResultClass_id
				left join v_InterruptLeaveType ilt (nolock) on ilt.InterruptLeaveType_id = epls.InterruptLeaveType_id
				left join v_DirectType dt (nolock) on dt.DirectType_id = epls.DirectType_id
				left join v_DirectClass dc (nolock) on dc.DirectClass_id = epls.DirectClass_id
				left join v_Diag dl (nolock) on dl.Diag_id = epls.Diag_lid
				left join v_Diag dco (nolock) on dco.Diag_id = epls.Diag_concid
				left join v_PrehospTrauma pt (nolock) on pt.PrehospTrauma_id = epls.PrehospTrauma_id
				left join fed.v_LeaveType lt (nolock) on lt.LeaveType_id = epls.LeaveType_fedid
				left join v_ResultDeseaseType rdt (nolock) on rdt.ResultDeseaseType_id = epls.ResultDeseaseType_id
				left join fed.v_ResultDeseaseType rdtf (nolock) on rdtf.ResultDeseaseType_id = epls.ResultDeseaseType_fedid
				left join v_YesNo iu (nolock) on iu.YesNo_id = epls.EvnPLStom_IsUnport
				left join v_YesNo isr (nolock) on isr.YesNo_id = epls.EvnPLStom_IsSurveyRefuse
				left join v_Diag d (nolock) on d.Diag_id = epls.Diag_id
				left join v_YesNo IsSan with (nolock) on isnull(epls.EvnPLStom_IsSan,1) = IsSan.YesNo_id
				left join v_SanationStatus with (nolock) on epls.SanationStatus_id = v_SanationStatus.SanationStatus_id
				outer apply (
					select
						count(EUP.EvnUslugaPar_id) as cnt
					from
						v_EvnUslugaPar EUP with (nolock)
						left join v_Evn EvnUP with (nolock) on EvnUP.Evn_id = eup.EvnUslugaPar_pid
					where
						EUP.EvnUslugaPar_rid = epls.EvnPLStom_id
						and EUP.EvnUslugaPar_setDT is not null
						and ISNULL(EvnUP.EvnClass_SysNick, '') != 'EvnUslugaPar'
				) EUP
				outer apply (
					select
						count(ER.EvnRecept_id) as cnt
					from
						v_EvnRecept er (nolock)
					where
						er.EvnRecept_pid = epls.EvnPLStom_id
						and er.Lpu_id = epls.Lpu_id
				) ER
				outer apply (
					select
						count(ES.EvnStickBase_id) as cnt
					from
						v_EvnStickBase es (nolock)
					where
						es.EvnStickBase_mid = epls.EvnPLStom_id
				) ES
				outer apply (
					select
						count(ES.EvnStickBase_id) as cnt
					from
						v_EvnStickBase es (nolock)
					where
						exists (select EvnLink_id from v_EvnLink with (nolock) where Evn_id = epls.EvnPLStom_id and Evn_lid = es.EvnStickBase_id)
				) ES2
				outer apply (
					select
						count(EMD.EvnMediaData_id) as cnt
					from
						v_EvnMediaData EMD with (nolock)
					where
						EMD.Evn_id = epls.EvnPLStom_id
				) EMD
				where
				epls.EvnPLStom_id = :EvnPLStom_id
		", $queryParams);

		if (empty($resp_epls[0]['EvnPLStom_id'])) {
			return false;
		}

		$resp_epls[0]['EvnVizitPLStom'] = $this->queryResult("
			select
				EvnVizitPLStom_id,
				convert(varchar(10), EvnVizitPLStom_setDT, 104) as EvnVizitPLStom_setDate
			from
				v_EvnVizitPLStom (nolock)
			where
				EvnVizitPLStom_pid = :EvnPLStom_id
			order by
				EvnVizitPLStom_setDT desc
		", array(
			'EvnPLStom_id' => $data['EvnPLStom_id']
		));

		if (!empty($resp_epls[0]['EvnPLStom_id'])) {
			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
			$dbConnection = getRegistryChecksDBConnection();
			if ($dbConnection != 'default') {
				$this->regDB = $this->load->database($dbConnection, true);
				$this->Reg_model->db = $this->regDB;
			}
			if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
				if ($this->Reg_model->checkEvnInRegistry(array(
						'EvnPLStom_id' => $resp_epls[0]['EvnPLStom_id'],
						'Lpu_id' => $data['Lpu_id']
					), 'edit') !== false) {
					$resp_epls[0]['accessType'] = 'view';
				}
			} else {
				$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
					'EvnPLStom_id' => $resp_epls[0]['EvnPLStom_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session']
				), 'edit');

				if (is_array($registryData)) {
					if (!empty($registryData['Error_Msg'])) {
						$resp_epls[0]['accessType'] = 'view';
						$resp_epls[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
					} elseif (!empty($registryData['Alert_Msg'])) {
						$resp_epls[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
					}
				}
			}
		}

		return $resp_epls;
	}
	
	/**
	 * Загрузка данных пациента в новой ЭМК
	 */
	function loadPersonForm($data) {

		$top = ' top 1 ';
		$filter = " ps.Person_id = :Person_id ";

		$pdFilter = array('PD.Person_id = ps.Person_id');
		if (!haveARMType('spec_mz')) {
			$pdFilter = array_merge(
				getAccessRightsDiagFilter('D.Diag_Code', true),
				getAccessRightsLpuFilter('PD.Lpu_id', true),
				getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id', true),
				$pdFilter
			);
		}
		
		$outer_apply = ''; $fields = '';
		
		if( getRegionNick() == 'penza' ) {
			$outer_apply .= "
				outer apply (
					SELECT count(*) as cnt
					FROM vac.v_JournalMantuAccount
					WHERE Person_id = ps.Person_id
				) MANTU
				outer apply (
					SELECT count(*) as cnt
					FROM vac.vac_JournalAccount ac WITH (NOLOCK)
					WHERE ac.Person_id = ps.Person_id and ac.vacJournalAccount_StatusType_id = 1
				) VAC
				outer apply (
					SELECT count(*) as cnt
					FROM vac.vac_PersonPlanFinal plf  WITH (NOLOCK)
					WHERE plf.Person_id = ps.Person_id and plf.vac_PersonPlanFinal_DatePlan >= getDate() - 1
				) VACP
			";
			$fields .= "
				MANTU.cnt as PersonMantuReactionCount,
				VAC.cnt as PersonInoculationCount,
				VACP.cnt as PersonInoculationPlanCount,
			";
		}

		$query = "
			select {$top}
				ps.Person_id,
				pc.PersonCard_id,
				ps.Server_id,
				ps.Person_Inn,
				S.Sex_Name,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
				SC.SocStatus_Name,
				PS.Person_Snils,
				isnull(RTRIM(UA.Address_Address), '') as Person_UAddress,
				isnull(RTRIM(PA.Address_Address), '') as Person_PAddress,
				PLI.cnt as PersonLpuInfoCount,
				PPR.cnt as PersonPrivilegeCount,
				PBG.cnt as PersonBloodGroupCount,
				CRC.cnt as PersonCardioRiskCalcPanelCount,
				PMH.cnt as PersonMedHistoryCount,
				PAR.cnt as PersonAllergicReactionCount,
				PD.cnt as PersonDispCount,
				EPLD.cnt as EvnPLDispCount,
				PH.cnt as PersonHeightCount,
				PW.cnt as PersonWeightCount,
				HC.cnt as HeadCircumferenceCount,
				CC.cnt as ChestCircumferenceCount,
				FTA.cnt as PersonFeedingTypeCount,
				PCH.PersonChild_id as PersonChild_id,
				FTN.FeedingType_Name as FeedingType_Name,
				{$fields}
				isnull(RTRIM(case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end), '') as Polis_Ser,
				isnull(RTRIM(case when ps.PolisType_id = 4 and ps.Person_EdNum is not null then PS.Person_EdNum else ps.Polis_Num end), '') as Polis_Num,
				Polis.OrgSmo_id,
				isnull(convert(varchar(10), Polis.Polis_begDate, 104), '') as Polis_begDate,
				isnull(convert(varchar(10), Polis.Polis_endDate, 104), '') as Polis_endDate,
				isnull(RTRIM(case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end), '') + ' ' + isnull(RTRIM(case when ps.PolisType_id = 4 and ps.Person_EdNum is not null then PS.Person_EdNum else ps.Polis_Num end), '') as Person_Polis,
				isnull(RTRIM(DocumentType.DocumentType_Name), '') + ' ' + isnull(RTRIM(Document.Document_Ser), '') + ' ' + isnull(RTRIM(Document.Document_Num), '') as Person_Document,
				isnull(RTRIM(DocumentType.DocumentType_Name), '') as DocumentType_Name,
				isnull(RTRIM(Document.Document_Num), '') as Document_Num,
				isnull(RTRIM(Document.Document_Ser), '') as Document_Ser,
				isnull(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				isnull(convert(varchar(10), Document.Document_endDate, 104), '') as Document_endDate,
				isnull(joborg.Org_Name, '') as Person_Job,
				isnull(RTRIM(PP.Post_Name), '') as Person_Post,
				l.Lpu_Nick + isnull(', участок: ' + lr.LpuRegion_Name, '') as Person_Attach,
				PS.Lpu_id,
				PL.MonitorTemperatureStartDate,
				FS.FamilyStatus_id,
				isnull(FS.FamilyStatus_Name, '') as FamilyStatus_Name
			from
				v_PersonState ps (nolock)
				left join v_Sex s (nolock) on s.Sex_id = ps.Sex_id
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				left join v_Address_all ua with (nolock) on ua.Address_id = ps.UAddress_id
				left join v_Address_all pa with (nolock) on pa.Address_id = ps.PAddress_id

				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join v_Job job with (nolock) on job.Job_id = ps.Job_id
				left join v_Org joborg with (nolock) on joborg.Org_id = job.Org_id
				left join Post PP with (nolock) on PP.Post_id = job.Post_id
				left join v_Lpu l with(nolock) on l.Lpu_id = ps.Lpu_id
				left join v_PersonCard PC with(nolock) on PC.Person_id = ps.Person_id and PC.LpuAttachType_id = 1
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_Polis Polis with(nolock) on Polis.Polis_id = ps.Polis_id
				left join v_FamilyStatus FS with(nolock) on FS.FamilyStatus_id = ps.FamilyStatus_id

				outer apply (
					select sum(item) as cnt
					from (
							select top 1
								case when (PLI.PersonLpuInfo_id is not null) then 1 else 0 end as item
							from
								v_PersonLpuInfo PLI with (nolock)
							where
								PLI.Person_id = ps.Person_id
							union all
							select top 1
								case when (RE.ReceptElectronic_id is not null) then 1 else 0 end as item
							from
								v_ReceptElectronic RE with (nolock)
							where
								RE.Person_id = ps.Person_id  
						) as item
				) PLI
				outer apply (
					select
						count(PPR.PersonPrivilege_id) as cnt
					from
						v_PersonPrivilege PPR with (nolock)
					where
						PPR.Person_id = ps.Person_id
				) PPR
				outer apply (
					select
						count(PBG.PersonBloodGroup_id) as cnt
					from
						v_PersonBloodGroup PBG with (nolock)
					where
						PBG.Person_id = ps.Person_id
				) PBG
				outer apply (
					select
						count(CRC.CardioRiskCalc_id) as cnt
					from
						v_CardioRiskCalc CRC with (nolock)
					where
						CRC.Person_id = ps.Person_id
				) CRC
				outer apply (
					select
						count(PMH.PersonMedHistory_id) as cnt
					from
						v_PersonMedHistory PMH with (nolock)
					where
						PMH.Person_id = ps.Person_id
				) PMH
				outer apply (
					select
						count(PAR.PersonAllergicReaction_id) as cnt
					from
						v_PersonAllergicReaction PAR with (nolock)
					where
						PAR.Person_id = ps.Person_id
				) PAR
				outer apply (
					select
						count(PD.PersonDisp_id) as cnt
					from
						v_PersonDisp PD with (nolock)
						left join v_Diag D with (nolock) on D.Diag_id = PD.Diag_id
						left join v_LpuSection LS with (nolock) on LS.LpuSection_id = PD.LpuSection_id
					where
						" . implode(" and ", $pdFilter) . "
				) PD
				outer apply (
					select
						count(EPLD.EvnPLDisp_id) as cnt
					from
						v_EvnPLDisp EPLD with (nolock)
					where
						EPLD.Person_id = ps.Person_id
				) EPLD
				outer apply (
					select
						count(PH.PersonHeight_id) as cnt
					from
						v_PersonHeight PH with (nolock)
					where
						PH.Person_id = ps.Person_id
				) PH
				outer apply (
					select
						count(PW.PersonWeight_id) as cnt
					from
						v_PersonWeight PW with (nolock)
					where
						PW.Person_id = ps.Person_id
				) PW
				outer apply (
					select
						count(HC.HeadCircumference_id) as cnt
					from
						v_HeadCircumference HC with (nolock)
						left join v_PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
					where
						PC.Person_id = ps.Person_id
				) HC
				outer apply (
					select
						count(CC.ChestCircumference_id) as cnt
					from
						v_ChestCircumference CC with (nolock)
						left join v_PersonChild PC with (nolock) on PC.PersonChild_id = CC.PersonChild_id
					where
						PC.Person_id = ps.Person_id
				) CC
				outer apply (
					select
						PCH.PersonChild_id as PersonChild_id
					from
						v_PersonChild PCH with (nolock)
					where
						PCH.Person_id = ps.Person_id
				) PCH
				outer apply (
					select top 1
						FTN.FeedingType_Name
					from v_FeedingTypeAge FTA with(nolock)
					left join v_FeedingType FTN (nolock) on FTN.FeedingType_id = FTA.FeedingType_id
					where FTA.PersonChild_id = PCH.PersonChild_id order by FTA.FeedingTypeAge_Age desc
				) FTN
				outer apply (
					select
						count(FTA.PersonChild_id) as cnt
					from
						v_FeedingTypeAge FTA with (nolock)
					where
						FTA.PersonChild_id = PCH.PersonChild_id
				) FTA
				outer apply (
					select top 1
						convert(varchar(10), PLA.PersonLabel_setDate, 104) as MonitorTemperatureStartDate
					from
						v_PersonLabel PLA with (nolock)
						inner join v_LabelObserveChart LOC with (nolock) on PLA.PersonLabel_id=LOC.PersonLabel_id
					where
						PLA.Person_id = ps.Person_id AND PLA.PersonLabel_disDate is null AND PLA.Label_id=7
				) PL
				{$outer_apply}
			where {$filter}
		";

		$result = $this->queryResult($query, array('Person_id' => $data['Person_id']));
		return $result;
	}

	/**
	 * Загрузка данных пациента в новой ЭМК (МАРМ)
	 */
	function mLoadPersonForm($data) {

		$top = ' top 1 ';
		$filter = " ps.Person_id = :Person_id ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$top = "";
			$filter = " ps.Person_id in ({$data['person_in']}) ";
		}

		$pdFilter = array('PD.Person_id = ps.Person_id');
		if (!haveARMType('spec_mz')) {
			$pdFilter = array_merge(
				getAccessRightsDiagFilter('D.Diag_Code', true),
				getAccessRightsLpuFilter('PD.Lpu_id', true),
				getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id', true),
				$pdFilter
			);
		}

		$query = "
			select {$top}
				ps.Person_id,
				pc.PersonCard_id,
				ps.Server_id,
				ps.Person_Inn,
				S.Sex_Name,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
				SC.SocStatus_Name,
				PS.Person_Snils,
				isnull(RTRIM(UA.Address_Address), '') as Person_UAddress,
				isnull(RTRIM(PA.Address_Address), '') as Person_PAddress,
				PLI.cnt as PersonLpuInfoCount,
				PPR.cnt as PersonPrivilegeCount,
				PBG.cnt as PersonBloodGroupCount,
				CRC.cnt as PersonCardioRiskCalcPanelCount,
				PMH.cnt as PersonMedHistoryCount,
				PAR.cnt as PersonAllergicReactionCount,
				PD.cnt as PersonDispCount,
				EPLD.cnt as EvnPLDispCount,
				PH.cnt as PersonHeightCount,
				PW.cnt as PersonWeightCount,
				FTA.cnt as PersonFeedingTypeCount,
				PCH.PersonChild_id  as PersonChild_id,
				EUP.cnt as EvnUslugaParCount,
				ER.cnt as EvnReceptCount,
				ESS.cnt as EvnStickCount,
				isnull(RTRIM(case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end), '') as Polis_Ser,
				isnull(RTRIM(case when ps.PolisType_id = 4 and ps.Person_EdNum is not null then PS.Person_EdNum else ps.Polis_Num end), '') as Polis_Num,
				Polis.OrgSmo_id,
				isnull(convert(varchar(10), Polis.Polis_begDate, 104), '') as Polis_begDate,
				isnull(convert(varchar(10), Polis.Polis_endDate, 104), '') as Polis_endDate,
				isnull(RTRIM(case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end), '') + ' ' + isnull(RTRIM(case when ps.PolisType_id = 4 and ps.Person_EdNum is not null then PS.Person_EdNum else ps.Polis_Num end), '') as Person_Polis,
				isnull(RTRIM(DocumentType.DocumentType_Name), '') + ' ' + isnull(RTRIM(Document.Document_Ser), '') + ' ' + isnull(RTRIM(Document.Document_Num), '') as Person_Document,
				isnull(RTRIM(DocumentType.DocumentType_Name), '') as DocumentType_Name,
				isnull(RTRIM(Document.Document_Num), '') as Document_Num,
				isnull(RTRIM(Document.Document_Ser), '') as Document_Ser,
				isnull(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				isnull(convert(varchar(10), Document.Document_endDate, 104), '') as Document_endDate,
				isnull(joborg.Org_Name, '') as Person_Job,
				isnull(RTRIM(PP.Post_Name), '') as Person_Post,
				l.Lpu_Nick + isnull(', участок: ' + lr.LpuRegion_Name, '') as Person_Attach,
				PS.Lpu_id,
				PL.MonitorTemperatureStartDate				
			from
				v_PersonState ps (nolock)
				left join v_Sex s (nolock) on s.Sex_id = ps.Sex_id
				left join v_SocStatus sc (nolock) on sc.SocStatus_id = ps.SocStatus_id
				left join v_Address_all ua with (nolock) on ua.Address_id = ps.UAddress_id
				left join v_Address_all pa with (nolock) on pa.Address_id = ps.PAddress_id

				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join v_Job job with (nolock) on job.Job_id = ps.Job_id
				left join v_Org joborg with (nolock) on joborg.Org_id = job.Org_id
				left join Post PP with (nolock) on PP.Post_id = job.Post_id
				left join v_Lpu l with(nolock) on l.Lpu_id = ps.Lpu_id
				left join v_PersonCard PC with(nolock) on PC.Person_id = ps.Person_id and PC.LpuAttachType_id = 1
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_Polis Polis with(nolock) on Polis.Polis_id = ps.Polis_id

				outer apply (
					select sum(item) as cnt
					from (
							select top 1
								case when (PLI.PersonLpuInfo_id is not null) then 1 else 0 end as item
							from
								v_PersonLpuInfo PLI with (nolock)
							where
								PLI.Person_id = ps.Person_id
							union all
							select top 1
								case when (RE.ReceptElectronic_id is not null) then 1 else 0 end as item
							from
								v_ReceptElectronic RE with (nolock)
							where
								RE.Person_id = ps.Person_id  
						) as item
				) PLI
				outer apply (
					select
						count(PPR.PersonPrivilege_id) as cnt
					from
						v_PersonPrivilege PPR with (nolock)
					where
						PPR.Person_id = ps.Person_id
				) PPR
				outer apply (
					select
						count(PBG.PersonBloodGroup_id) as cnt
					from
						v_PersonBloodGroup PBG with (nolock)
					where
						PBG.Person_id = ps.Person_id
				) PBG
				outer apply (
					select
						count(CRC.CardioRiskCalc_id) as cnt
					from
						v_CardioRiskCalc CRC with (nolock)
					where
						CRC.Person_id = ps.Person_id
				) CRC
				outer apply (
					select
						count(PMH.PersonMedHistory_id) as cnt
					from
						v_PersonMedHistory PMH with (nolock)
					where
						PMH.Person_id = ps.Person_id
				) PMH
				outer apply (
					select
						count(PAR.PersonAllergicReaction_id) as cnt
					from
						v_PersonAllergicReaction PAR with (nolock)
					where
						PAR.Person_id = ps.Person_id
				) PAR
				outer apply (
					select
						count(PD.PersonDisp_id) as cnt
					from
						v_PersonDisp PD with (nolock)
						left join v_Diag D with (nolock) on D.Diag_id = PD.Diag_id
						left join v_LpuSection LS with (nolock) on LS.LpuSection_id = PD.LpuSection_id
					where
						" . implode(" and ", $pdFilter) . "
				) PD
				outer apply (
					select
						count(EPLD.EvnPLDisp_id) as cnt
					from
						v_EvnPLDisp EPLD with (nolock)
					where
						EPLD.Person_id = ps.Person_id
				) EPLD
				outer apply (
					select
						count(PH.PersonHeight_id) as cnt
					from
						v_PersonHeight PH with (nolock)
					where
						PH.Person_id = ps.Person_id
				) PH
				outer apply (
					select
						count(PW.PersonWeight_id) as cnt
					from
						v_PersonWeight PW with (nolock)
					where
						PW.Person_id = ps.Person_id
				) PW
				outer apply (
					select
						PCH.PersonChild_id as PersonChild_id
					from
						v_PersonChild PCH with (nolock)
					where
						PCH.Person_id = PCH.PersonChild_id 
				) PCH
				outer apply (
					select
						count(FTA.PersonChild_id) as cnt
					from
						v_FeedingTypeAge FTA with (nolock)
					where
						FTA.PersonChild_id = 51
				) FTA
				outer apply (
					select top 1
						convert(varchar(10), PLA.PersonLabel_setDate, 104) as MonitorTemperatureStartDate
					from
						v_PersonLabel PLA with (nolock)
						inner join v_LabelObserveChart LOC with (nolock) on PLA.PersonLabel_id=LOC.PersonLabel_id
					where
						PLA.Person_id = ps.Person_id AND PLA.PersonLabel_disDate is null AND PLA.Label_id=7
				) PL
				outer apply (
					select
						count(eup.EvnUslugaPar_id) as cnt
					from
						v_EvnUslugaPar eup with (nolock)
						left join v_Evn EvnUP with (nolock) on EvnUP.Evn_id = eup.EvnUslugaPar_pid
					where 1=1
						and eup.EvnUslugaPar_setDT is not null
						and ISNULL(EvnUP.EvnClass_SysNick, '') != 'EvnUslugaPar'
						and eup.Person_id = ps.Person_id
				) EUP
				outer apply (
					select
						count(ER.EvnRecept_id) as cnt
					from
						v_EvnRecept ER with (nolock)
					where
						ER.Person_id = ps.Person_id
				) ER
				outer apply (
					select count(*) as cnt from 
					(
						select	EvnStick_id				
						from v_EvnStick with (nolock)
						where Person_id = ps.Person_id

						union all

						select es.EvnStick_id
						from v_EvnStickDop ESD with (nolock)
							inner join v_EvnStick ES (nolock) on ES.EvnStick_id = ESD.EvnStickDop_pid
						where esd.Person_id = ps.Person_id

						union all

						select EvnStickStudent_id as EvnStick_id
						from v_EvnStickStudent with (nolock)
						where Person_id = ps.Person_id
					) as cnt
				) ESS
			where {$filter}
		";

		$result = $this->queryResult($query, array('Person_id' => $data['Person_id']));

		// для офлайн режима не определяем эти параметры
		if (!empty($result[0]) && empty($data['person_in'])) {

			// считаем диагнозы
			$this->load->model('EvnDiag_model');
			$diags = $this->EvnDiag_model->getPersonDiagCount($data);

			$result[0]['EvnDiagCount'] = $diags['EvnDiagCount'];

			// считаем отмененные направления
			$this->load->model('EvnDirection_model');
			$data['onlyCount'] = true;
			$dirfails = $this->EvnDirection_model->getDirFailListViewData($data);

			$result[0]['DirFailListCount'] = (!empty($dirfails[0]['DirFailListCount']) ? $dirfails[0]['DirFailListCount'] : 0);

			// считаем оперативные вмешательства
			$this->load->model('EvnUsluga_model');
			$usluga_oper = $this->EvnUsluga_model->getEvnUslugaOperViewData($data);

			$result[0]['UslugaOperCount'] = (!empty($usluga_oper[0]) ? count($usluga_oper) : 0);

			// количество мед. свидетельств
			$result[0]['MedSvidCount'] = $this->getFirstResultFromQuery("
				select sum(total.cnt) as sum from(
				select top 1
					count(BS.Person_id) as cnt,
					'birth' as PersonSvidType_Code
				from v_BirthSvid BS (nolock)
				where
					BS.Person_id = :Person_id
					and ISNULL(BS.BirthSvid_IsBad,1) = '1'
					
				union
				
				select top 1
					count(PDS.Person_id) as cnt,
					'pntdeath' as PersonSvidType_Code
				from v_PntDeathSvid PDS (nolock)
				where
					PDS.Person_id = :Person_id
					and ISNULL(PDS.PntDeathSvid_IsBad,1) = '1'
					
				union
				
				select top 1
					count(DS.Person_id) as cnt,
					'death' as PersonSvidType_Code
				from v_DeathSvid DS (nolock)
				where
					DS.Person_id = :Person_id
					and ISNULL(DS.DeathSvid_IsBad,1) = '1'
				) as total
			", array('Person_id' => $data['Person_id']));

			$this->load->model('PMMediaData_model');
			$media_params = array('Person_id' => $data['Person_id']);
			$person_media_data = $this->PMMediaData_model->getpmMediaData($media_params);

			$result[0]['Person_PhotoThumb'] = '';
			$result[0]['Person_Photo'] = '';

			if (!empty($person_media_data[0]['pmMediaData_FilePath'])) {

				$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
				$domain = $protocol.$_SERVER['SERVER_NAME'];

				$fileName = $person_media_data[0]['pmMediaData_FilePath'];

				$person_thumbs_url = $this->PMMediaData_model->getUrlPersonThumbs($media_params);
				$thumb_link = $person_thumbs_url.$fileName;

				if (file_exists('.'.$thumb_link)) {
					$result[0]['Person_PhotoThumb'] = $domain.$thumb_link;
				}

				$person_files_url = $this->PMMediaData_model->getUrlPersonFiles($media_params);
				$photo_link = $person_files_url.$fileName;

				if (file_exists('.'.$photo_link)) {
					$result[0]['Person_Photo'] = $domain.$photo_link;
				}
			}
		}

		return $result;
	}

	/**
	 * Загрузка формы посещения в новой ЭМК
	 */
	function loadEvnVizitPLForm($data) {
		$join = '';
		$joinEvnDirection = "";
		$whereEvnDirection = "";
		if (!empty($data['forMobileArm'])) {
			$select = "
				ls.LpuSection_Name,
				ls.LpuSection_id,
				msf.Person_Fio as MedPersonal_Fio,
				tc.TreatmentClass_Name,
				tc.TreatmentClass_id,
				st.ServiceType_Name,
				st.ServiceType_id,
				vt.VizitType_Name,
				vt.VizitType_id,
				vc.VizitClass_Name,
				vc.VizitClass_id,
				mck.MedicalCareKind_Name,
				mck.MedicalCareKind_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				uc.UslugaComplex_id,
				lsp.LpuSectionProfile_Name,
				lsp.LpuSectionProfile_id,
				pt.PayType_Name,
				pt.PayType_id,
				d.Diag_Code,
				d.Diag_Name,
				d.Diag_id,
				dt.DeseaseType_Name,
				EVPL.MedStaffFact_id,
				EVPL.MedPersonal_sid,
			";
			$join .= '
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = evpl.LpuSection_id
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = evpl.TreatmentClass_id
				left join v_ServiceType st (nolock) on st.ServiceType_id = evpl.ServiceType_id
				left join v_VizitType vt (nolock) on vt.VizitType_id = evpl.VizitType_id
				left join v_VizitClass vc (nolock) on vc.VizitClass_id = evpl.VizitClass_id
				left join nsi.v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_id = evpl.MedicalCareKind_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = evpl.UslugaComplex_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = evpl.LpuSectionProfile_id
				left join v_PayType pt (nolock) on pt.PayType_id = evpl.PayType_id
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = evpl.DeseaseType_id
			';
		} else {
			$select = '
				EVPL.LpuSection_id,
				EVPL.MedStaffFact_id,
				ISNULL(MSF.MedPersonal_id, EVPL.MedPersonal_id) as MedPersonal_id,
				EVPL.MedPersonal_sid,
				EVPL.TreatmentClass_id,
				EVPL.ServiceType_id,
				EVPL.VizitType_id,
				EVPL.VizitClass_id,
				EVPL.MedicalCareKind_id,
				EVPL.UslugaComplex_id as UslugaComplex_uid,
				EVPL.LpuSectionProfile_id,
				EVPL.PayType_id,
				EVPL.Diag_id,
				d.Diag_Code,
				EVPL.EvnVizitPL_IsZNO,
				EVPL.PainIntensity_id,
				EVPL.TumorStage_id,
				EVPL.Diag_spid,
				EVPL.DispProfGoalType_id,
				EVPL.HealthKind_id,
				EVPL.Mes_id,
			';
			// #170933 для Перми, для врачей с должностью кардиолог (в любом звучании) с кодами 179,182,24, рецепты работаю по-иному
			if (getRegionNick() == 'perm') {
				$select .= '
					case when exists(select top 1
						msf.MedStaffFact_id
					from
						v_MedStaffFact msf with (nolock)
						left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
					where
						msf.MedStaffFact_id = evpl.MedStaffFact_id and
						ps.PostMed_Code IN (179,182,24,37,66,37,74,76,40,125,111,117)
					) then 1 else 0 end as isKardio,
				';
			} else {
				$select .= '
					0 as isKardio,
				';
			}
		}
		
		if (getRegionNick() != 'kz') {
			// Направление на ВМП
			$joinEvnDirection = " LEFT JOIN EvnLink EL WITH(NOLOCK) ON EL.Evn_id = ED.EvnDirection_id ";
			$whereEvnDirection = " OR EL.Evn_lid = evpl.EvnVizitPL_id ";
		}

		$queryParams = array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		);

		list($accessType, $accessParams) = $this->getEvnVizitPLAccessType($data);
		$queryParams = array_merge($queryParams, $accessParams);

		$resp = $this->queryResult("
			select
				EVPL.EvnVizitPL_id,
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), EVPL.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate,
				EVPL.EvnVizitPL_setTime,
				EVPL.DeseaseType_id,
				EVPL.EvnClass_id,
				EVPL.Person_id,
				EVPL.Server_id,
				EVPL.PersonEvn_id,
				{$select}
				EXP.cnt as ProtocolCount,
				(isnull(EP.cnt,0) + isnull(ED.cnt,0)) as EvnPrescrCount,
				ED.cnt as EvnDirectionCount,
				EDR.cnt as EvnDrugCount,
				EU.cnt as EvnUslugaCount,
				ER.cnt + ERG.cnt as EvnReceptCount,
				EX.cnt as EvnXmlCount,
				EDSO.cnt as EvnPLDispScreenOnkoCount,
				XML.EvnXml_id
			from
				v_EvnVizitPL EVPL (nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = evpl.MedStaffFact_id
				left join v_Diag d (nolock) on d.Diag_id = evpl.Diag_id
				{$join}
				outer apply (
					select
						count(EX.EvnXml_id) as cnt
					from
						v_EvnXml EX with (nolock)
					where
						EX.Evn_id = evpl.EvnVizitPL_id
						and XmlType_id = 3
				) EXP
				outer apply (
					select top 1 EX.EvnXml_id
					from v_EvnXml EX (nolock)
					where EX.Evn_id = evpl.EvnVizitPL_id and XmlType_id = 3
				) XML
				outer apply (
					select
						count(EP.EvnPrescr_id) as cnt
					from
						v_EvnPrescr EP with (nolock)
					where
						EP.EvnPrescr_pid = evpl.EvnVizitPL_id
				) EP
				outer apply (
					select
						count(ED.EvnDirection_id) as cnt
					from
						v_EvnDirection_all ED with (nolock)
						left join v_DirType dt (nolock) on dt.DirType_id = ED.DirType_id
						{$joinEvnDirection}
					where
						ED.EvnDirection_pid = evpl.EvnVizitPL_id
						and dt.DirType_Code IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 15, 18, 23, 25, 26, 27, 28, 29)
						{$whereEvnDirection}
				) ED
				outer apply (
					select
						count(EDR.EvnDrug_id) as cnt
					from
						v_EvnDrug EDR with (nolock)
					where
						EDR.EvnDrug_pid = evpl.EvnVizitPL_id
				) EDR
				outer apply (
					select
						count(EU.EvnUsluga_id) as cnt
					from
						v_EvnUsluga EU with (nolock)
					where
						EU.EvnUsluga_pid = evpl.EvnVizitPL_id
						and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
						and eu.EvnUsluga_setDT is not null
				) EU
				outer apply (
					select
						count(edso.EvnPLDispScreenOnko_id) as cnt
					from
						v_EvnPLDispScreenOnko edso with (nolock)
					where
						edso.EvnPLDispScreenOnko_pid = evpl.EvnVizitPL_id
				) EDSO
				outer apply (
					select
						count(ER.EvnRecept_id) as cnt
					from
						v_EvnRecept ER with (nolock)
						left join v_ReceptDelayType RDT (nolock) on RDT.ReceptDelayType_id = ER.ReceptDelayType_id
					where
						ER.EvnRecept_pid = evpl.EvnVizitPL_id
						and ISNULL(RDT.ReceptDelayType_Code,-1) <> 4
				) ER
				outer apply (
					select
						count(ERG.EvnReceptGeneral_id) as cnt
					from
						v_EvnReceptGeneral ERG with (nolock)
					where
						ERG.EvnReceptGeneral_pid = evpl.EvnVizitPL_id
				) ERG
				outer apply (
					select
						count(EX.EvnXml_id) as cnt
					from
						v_EvnXml EX with (nolock)
					where
						EX.Evn_id = evpl.EvnVizitPL_id
						and XmlType_id = 2
				) EX
				outer apply (
					select
						LpuSection_id
					from
						v_MedStaffFact SMP with (nolock)
					where
						SMP.MedStaffFact_id = :MedStaffFact_id
				) SMP
			where
				EvnVizitPL_id = :EvnVizitPL_id
			order by
				EvnVizitPL_setDate desc
		", $queryParams);

		if (empty($data['forMobileArm'])) {
			$this->load->library('swMorbus');
			$resp = swMorbus::processingEvnData($resp, 'EvnVizitPL');
		}

		if (!empty($resp[0]['EvnVizitPL_id'])) {
			if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$lisResp = $this->lis->GET('EvnUsluga/Count', array(
					'EvnUsluga_pid' => $resp[0]['EvnVizitPL_id']
				), 'single');
				if (!$this->isSuccessful($lisResp)) {
					return false;
				}
				$resp[0]['EvnUslugaCount'] += $lisResp['Count'];
				
				// Также нужно узнать количество направлений
				$respED = $this->lis->GET('EvnDirection/Count', [
					'EvnDirection_pid' => $resp[0]['EvnVizitPL_id'],
					//'status' => 'active'
				], 'single');
				if (!$this->isSuccessful($respED)) {
					return false;
				}
				$resp[0]['EvnPrescrCount'] += $respED['Count'];
			}

			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
			$dbConnection = getRegistryChecksDBConnection();
			if ($dbConnection != 'default') {
				$this->regDB = $this->load->database($dbConnection, true);
				$this->Reg_model->db = $this->regDB;
			}
			if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
				if ($this->Reg_model->checkEvnInRegistry(array(
						'EvnVizitPL_id' => $resp[0]['EvnVizitPL_id'],
						'Lpu_id' => $data['Lpu_id']
					), 'edit') !== false) {
					$resp[0]['accessType'] = 'view';
				}
			} else {
				$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
					'EvnVizitPL_id' => $resp[0]['EvnVizitPL_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session']
				), 'edit');

				if (is_array($registryData)) {
					if (!empty($registryData['Error_Msg'])) {
						$resp[0]['accessType'] = 'view';
						$resp[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
					} elseif (!empty($registryData['Alert_Msg'])) {
						$resp[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
					}
				}
			}

			$resp[0]['DrugTherapyScheme_ids'] = $this->queryResult("
				select
					evdts.EvnVizitPLDrugTherapyLink_id,
					dts.DrugTherapyScheme_id,
					dts.DrugTherapyScheme_Code,
					ISNULL(dts.DrugTherapyScheme_Name, dts.DrugTherapyScheme_Mnn) as DrugTherapyScheme_Name
				from
					v_EvnVizitPLDrugTherapyLink evdts (nolock)
					inner join DrugTherapyScheme dts (nolock) on dts.DrugTherapyScheme_id = evdts.DrugTherapyScheme_id
				where
					evdts.EvnVizitPL_id = :EvnVizitPL_id
			", array(
				'EvnVizitPL_id' => $resp[0]['EvnVizitPL_id']
			));
		}

		return $resp;
	}

	/**
	 * Загрузка списка специфик
	 */
	function loadEvnVizitPLListMorbus($data) {
		
		$queryParams = array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		);

		$resp = $this->queryResult("
			select
				EVPL.EvnVizitPL_id,
				EVPL.Person_id,
				EVPL.Diag_id
			from
				v_EvnVizitPL EVPL (nolock)
			where
				EvnVizitPL_id = :EvnVizitPL_id
		", $queryParams);

		$this->load->library('swMorbus');
		$resp = swMorbus::processingEvnData($resp, 'EvnVizitPL');

		return $resp;
	}

	/**
	 * Загрузка формы стомат. посещения в новой ЭМК
	 */
	function loadEvnVizitPLStomForm($data) {
		$join = '';
		if (!empty($data['forMobileArm'])) {
			$select = "
				ls.LpuSection_Name,
				ls.LpuSection_id,
				msf.Person_Fio as MedPersonal_Fio,
				tc.TreatmentClass_Name,
				tc.TreatmentClass_id,
				st.ServiceType_Name,
				st.ServiceType_id,
				vt.VizitType_Name,
				vt.VizitType_id,
				vc.VizitClass_Name,
				vc.VizitClass_id,
				mck.MedicalCareKind_Name,
				mck.MedicalCareKind_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				uc.UslugaComplex_id,
				lsp.LpuSectionProfile_Name,
				lsp.LpuSectionProfile_id,
				pt.PayType_Name,
				pt.PayType_id,
				d.Diag_Code,
				d.Diag_Name,
				d.Diag_id,
				dt.DeseaseType_Name,
				dt.DeseaseType_id,
			";
			$join .= '
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = evpls.LpuSection_id
				left join v_TreatmentClass tc (nolock) on tc.TreatmentClass_id = evpls.TreatmentClass_id
				left join v_ServiceType st (nolock) on st.ServiceType_id = evpls.ServiceType_id
				left join v_VizitType vt (nolock) on vt.VizitType_id = evpls.VizitType_id
				left join v_VizitClass vc (nolock) on vc.VizitClass_id = evpls.VizitClass_id
				left join nsi.v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_id = evpls.MedicalCareKind_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = evpls.UslugaComplex_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = evpls.LpuSectionProfile_id
				left join v_PayType pt (nolock) on pt.PayType_id = evpls.PayType_id
				left join v_Diag d (nolock) on d.Diag_id = evpls.Diag_id
				left join v_DeseaseType dt (nolock) on dt.DeseaseType_id = evpls.DeseaseType_id
			';
		} else {
			$select = '
				evpls.LpuSection_id,
				evpls.MedStaffFact_id,
				ISNULL(MSF.MedPersonal_id, evpls.MedPersonal_id) as MedPersonal_id,
				evpls.MedPersonal_sid,
				evpls.TreatmentClass_id,
				evpls.ServiceType_id,
				evpls.VizitType_id,
				evpls.VizitClass_id,
				evpls.MedicalCareKind_id,
				evpls.UslugaComplex_id as UslugaComplex_uid,
				evpls.LpuSectionProfile_id,
				evpls.PayType_id,
				evpls.Diag_id,
				euv.UslugaComplexTariff_id,
				euv.EvnUslugaCommon_Price as EvnUslugaStom_UED,
				Parodontogram.EvnUslugaStom_id as EvnUslugaParodontogram_id,
				Parodontogram.EvnUslugaStom_pid as EvnUslugaParodontogram_pid,
			';

			$join .= '
				outer apply (
					select top 1 e.EvnUslugaStom_id, e.EvnUslugaStom_pid
					from v_EvnUslugaStom e with (nolock)
					inner join v_Evn v with (nolock) on v.Evn_id = e.EvnUslugaStom_pid
					where e.Person_id = evpls.Person_id
					and e.EvnUslugaStom_setDate <= evpls.EvnVizitPLStom_setDate
					and exists(
						select top 1 p.Parodontogram_id
						from v_Parodontogram p with (nolock)
						where p.EvnUslugaStom_id = e.EvnUslugaStom_id
					)
					order by
						case when e.EvnUslugaStom_pid = evpls.EvnVizitPLStom_id then 1 else 2 end,
						v.Evn_setDT desc
				) Parodontogram
			';
		}

		$queryParams = array(
			'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id']
		);

		list($accessType, $accessParams) = $this->getEvnVizitPLStomAccessType($data);
		$queryParams = array_merge($queryParams, $accessParams);

		$resp = $this->queryResult("
			select
				evpls.EvnVizitPLStom_id,
				case when {$accessType} then 'edit' else 'view' end as accessType,
				convert(varchar(10), evpls.EvnVizitPLStom_setDT, 104) as EvnVizitPLStom_setDate,
				evpls.EvnVizitPLStom_setTime,
				evpls.DeseaseType_id,
				evpls.EvnClass_id,
				evpls.Person_id,
				evpls.Server_id,
				evpls.PersonEvn_id,
				evpls.EvnVizitPLStom_IsPrimaryVizit,
				evpls.ProfGoal_id,
				evpls.DispProfGoalType_id,
				evpls.DispClass_id,
				evpls.EvnPLDisp_id,
				BPD.BitePersonType_id,
				evpls.PersonDisp_id,
				{$select}
				EDPLS.cnt as EvnDiagPLStomCount,
				EXP.cnt as ProtocolCount,
				(isnull(EP.cnt,0) + isnull(ED.cnt,0)) as EvnPrescrCount,
				ED.cnt as EvnDirectionCount,
				EDR.cnt as EvnDrugCount,
				EU.cnt as EvnUslugaCount,
				ER.cnt as EvnReceptCount,
				EX.cnt as EvnXmlCount
			from
				v_EvnVizitPLStom evpls (nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = evpls.MedStaffFact_id
				outer apply (
					select top 1
						t1.UslugaComplexTariff_id,
						t1.EvnUslugaCommon_Price
					from
						v_EvnUslugaCommon t1 with (nolock)
					where
						t1.EvnUslugaCommon_pid = :EvnVizitPLStom_id
						and ISNULL(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
					order by
						t1.EvnUslugaCommon_setDT desc
				) euv
				{$join}
				outer apply (
					select
						count(EDPLS.EvnDiagPLStom_id) as cnt
					from
						v_EvnDiagPLStom EDPLS with (nolock)
					where
						EDPLS.EvnDiagPLStom_rid = evpls.EvnVizitPLStom_rid
				) EDPLS
				outer apply (
					select
						count(EX.EvnXml_id) as cnt
					from
						v_EvnXml EX with (nolock)
					where
						EX.Evn_id = evpls.EvnVizitPLStom_id
						and XmlType_id = 3
				) EXP
				outer apply (
					select
						count(EP.EvnPrescr_id) as cnt
					from
						v_EvnPrescr EP with (nolock)
					where
						EP.EvnPrescr_pid = evpls.EvnVizitPLStom_id
				) EP
				outer apply (
					select
						count(ED.EvnDirection_id) as cnt
					from
						v_EvnDirection_all ED with (nolock)
					where
						ED.EvnDirection_pid = evpls.EvnVizitPLStom_id
				) ED
				outer apply (
					select
						count(EDR.EvnDrug_id) as cnt
					from
						v_EvnDrug EDR with (nolock)
					where
						EDR.EvnDrug_pid = evpls.EvnVizitPLStom_id
				) EDR
				outer apply (
					select
						count(EU.EvnUsluga_id) as cnt
					from
						v_EvnUsluga EU with (nolock)
					where
						EU.EvnUsluga_pid = evpls.EvnVizitPLStom_id
						and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
						and eu.EvnUsluga_setDT is not null
				) EU
				outer apply (
					select
						count(ER.EvnRecept_id) as cnt
					from
						v_EvnRecept ER with (nolock)
					where
						ER.EvnRecept_pid = evpls.EvnVizitPLStom_id
				) ER
				outer apply (
					select
						count(EX.EvnXml_id) as cnt
					from
						v_EvnXml EX with (nolock)
					where
						EX.Evn_id = evpls.EvnVizitPLStom_id
						and XmlType_id = 2
				) EX
				outer apply(
					select top 1 BitePersonType_id
					from v_BitePersonData with (nolock)
					where EvnVizitPLStom_id = :EvnVizitPLStom_id and BitePersonType_id is not null and  BitePersonData_disDate is null
					order by BitePersonData_updDT DESC
				) BPD
				outer apply (
					select
						LpuSection_id
					from
						v_MedStaffFact SMP with (nolock)
					where
						SMP.MedStaffFact_id = :MedStaffFact_id
				) SMP
			where
				EvnVizitPLStom_id = :EvnVizitPLStom_id
			order by
				EvnVizitPLStom_setDate desc
		", $queryParams);

		if (!empty($resp[0]['EvnVizitPLStom_id'])) {
			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
			$dbConnection = getRegistryChecksDBConnection();
			if ($dbConnection != 'default') {
				$this->regDB = $this->load->database($dbConnection, true);
				$this->Reg_model->db = $this->regDB;
			}
			if (in_array(getRegionNick(), array('buryatiya'/*, 'kareliya'*/))) {
				if ($this->Reg_model->checkEvnInRegistry(array(
						'EvnVizitPLStom_id' => $resp[0]['EvnVizitPLStom_id'],
						'Lpu_id' => $data['Lpu_id']
					), 'edit') !== false) {
					$resp[0]['accessType'] = 'view';
				}
			} else {
				$registryData = $this->Reg_model->checkEvnAccessInRegistry(array(
					'EvnVizitPLStom_id' => $resp[0]['EvnVizitPLStom_id'],
					'Lpu_id' => $data['Lpu_id'],
					'session' => $data['session']
				), 'edit');

				if (is_array($registryData)) {
					if (!empty($registryData['Error_Msg'])) {
						$resp[0]['accessType'] = 'view';
						$resp[0]['AlertReg_Msg'] = $registryData['Error_Msg'];
					} elseif (!empty($registryData['Alert_Msg'])) {
						$resp[0]['AlertReg_Msg'] = $registryData['Alert_Msg'];
					}
				}
			}
		}

		return $resp;
	}
	
	/**
	 * Сохранение схемы лекарственной терапии 
	 */
	function saveDrugTherapyScheme($data) {
		
		return $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@EvnVizitPLDrugTherapyLink_id bigint = null;

			exec p_EvnVizitPLDrugTherapyLink_ins
				@EvnVizitPLDrugTherapyLink_id = @EvnVizitPLDrugTherapyLink_id output,
				@EvnVizitPL_id = :EvnVizitPL_id,
				@DrugTherapyScheme_id = :DrugTherapyScheme_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @EvnVizitPLDrugTherapyLink_id as EvnVizitPLDrugTherapyLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'DrugTherapyScheme_id' => $data['DrugTherapyScheme_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}
	
	/**
	 * Удаление схемы лекарственной терапии 
	 */
	function deleteDrugTherapyScheme($data) {
		
		return $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnVizitPLDrugTherapyLink_del
				@EvnVizitPLDrugTherapyLink_id = :EvnVizitPLDrugTherapyLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnVizitPLDrugTherapyLink_id' => $data['EvnVizitPLDrugTherapyLink_id']
		));
	}
}
