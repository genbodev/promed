<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для работы с данными для передачи в ПАК НИЦ МБУ
 *
 * @package
 * @access		public
 * @copyright	Copyright (c) 2019 Swan Ltd.
 * @author		Марков Андрей
 * @version
 */
class Mbu_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		if (!isset($_SESSION['pmuser_id'])) {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}
	
	/**
	 *	Данные для передачи в ПАК НИЦ МБУ
	 */
	function preSendMbu($data) {
		// todo: По идее здесь надо просто писать данные в ActiveMQ, но пока мы будем писать в таблицу из которой потом выбирать и отправлять
		// todo: Надо будет переделать
		
		$filter = "";
		$params = array('EvnLabSample_id'=>$data['EvnLabSample_id']);
		
		// Перед всем этим чекаем настройку MedService_IsSendMbu
		$msset = $this->getFirstRowFromQuery("
			SELECT top 1
				MedService_IsSendMbu
			FROM v_EvnLabSample els with (nolock)
			inner join v_MedService ms with(nolock) on ms.MedService_id = els.MedService_id
			WHERE els.EvnLabSample_id = :EvnLabSample_id
		", $data);

		if (isset($msset) && ($msset['MedService_IsSendMbu']==2)) { // Записываем только для служб с настройками
			// Читаем данные по одобренным тестам, тест может быть один или несколько (при одобрении нескольких тестов)
			if (isset($data['UslugaTest_id'])) {
				$filter .= " and ut.UslugaTest_id = :UslugaTest_id";
				$params['UslugaTest_id'] = $data['UslugaTest_id'];
			}
			if (!empty($data['UslugaTest_ids'])) {
				$UslugaTest_ids = json_decode($data['UslugaTest_ids']);
				if (!empty($UslugaTest_ids)) {
					$filter .= " and ut.UslugaTest_id IN (".implode(',', $UslugaTest_ids).")";
				}
			}
			
			$query = "
				Select 
					ut.UslugaTest_id as UslugaTest_id,
					ut.UslugaTest_pid as UslugaTest_pid,
					ut.UslugaTest_ResultValue,
					ut.UslugaTest_ResultLower,
					ut.UslugaTest_ResultUpper,
					cast(ut.UslugaTest_CheckDT as date) as MbuPerson_sendDT,
					ls.Person_id as Person_id,
					ms.MedService_id,
					ms.MedServiceType_id,
					mst.MedServiceType_code,
					IsNull(uc.UslugaComplex_oid,ucg.UslugaComplex_oid) as UslugaComplex_oid,
					bmp.BactMicro_id,
					bmp.BactMicroProbe_IsNotShown
				FROM 
					v_UslugaTest ut with(nolock)
					inner join v_EvnLabSample ls with(nolock) on ut.EvnLabSample_id = ls.EvnLabSample_id
					inner join v_MedService ms with(nolock) on ms.MedService_id = ls.MedService_id
					left join v_MedServiceType mst with(nolock) on mst.MedServiceType_id = ms.MedServiceType_id
					inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = ut.UslugaComplex_id
					left join UslugaComplex ucg (nolock) on uc.UslugaComplex_2011id = ucg.UslugaComplex_id
					-- Бактериология
					left join v_BactMicroProbe bmp (nolock) on bmp.UslugaTest_id = ut.UslugaTest_id and bmp.EvnLabSample_id = ut.EvnLabSample_id
					
				where
					UslugaTest_ResultApproved = 2 and
					ls.EvnLabSample_id = :EvnLabSample_id
					{$filter}
			";
			$resp_ut = $this->queryResult($query, $params);

			$Person_id = null;
			// Собираем данные для сохранения в таблицу MbuPerson
			foreach($resp_ut as $ut) {
				// todo: Разобраться с бактериологией
				$ut['MbuPerson_IsBact'] = 1;
				//$ut['BactMicro_id'] = null;
				$ut['MbuStatus_id'] = 1; // ready	Готов к отправке
				if ($ut['MedServiceType_code']=='60') { // Можно определять и по наличии данных в v_BactMicroProbe
					$ut['MbuPerson_IsBact'] = 2;
					// todo: Тест выполнен на службе «Микробиологическая лаборатория» и имеет связь с микроорганизмом в пробе (BactMicroProbe) 
					// Это надо сделать, пока непонятно где это хранится
					if ($ut['BactMicroProbe_IsNotShown']==2) { // Микроорганизмы не найдены
						$ut['InterpretationResult_id'] = 25; // ND	Не обнаружено
					} else { // Микроорганизмы найдены
						$ut['InterpretationResult_id'] = 9; // DET	Обнаружено
					}
				} else {
					// todo: Выбрать исходя из референсных значений скорее всего нельзя, т.к. значения могут быть и не числовыми
					$ut['InterpretationResult_id'] = 24; // N	Нормальный (в пределах референсного диапазона)
					if (is_numeric($ut['UslugaTest_ResultValue']) && !empty($ut['UslugaTest_ResultLower']) && !empty($ut['UslugaTest_ResultUpper'])) {
						if ($ut['UslugaTest_ResultValue']<=$ut['UslugaTest_ResultLower'] || $ut['UslugaTest_ResultValue']>=$ut['UslugaTest_ResultUpper']) {
							$ut['InterpretationResult_id'] = 3; // A	Патологический (вне референсного диапазона)
						}
					}
				}
				if (empty($ut['UslugaComplex_oid'])) {
					$ut['MbuStatus_id'] = 2; // notpossible	Не возможно отправить
				}
				$ut['MbuPerson_AnswerCode'] = null; 
				$ut['pmUser_id'] = $data['pmUser_id']; 
				$result = $this->save($ut);
			}
		}
	}

	/**
	 *	Сохранение записи для передачи в ПАК НИЦ МБУ
	 */
	function save($data) {
		// Перед сохранением проверим что такой записи нет в таблице
		$query = "
			select top 1
				MbuPerson_id
			from
				v_MbuPerson (nolock)
			where
				UslugaTest_id = :UslugaTest_id
		";
		$result = $this->db->query($query, $data);
		
		$data['MbuPerson_id'] = null;
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['MbuPerson_id'])) {
				$data['MbuPerson_id'] = $resp[0]['MbuPerson_id'];
			}
		}
		if (empty($data['MbuPerson_id'])) { // Если запись не найдена, то добавляем
			$procedure = 'p_MbuPerson_ins';
		}
		if ( !empty($data['MbuPerson_id']) ) { // todo: Проверить условия, по идее запись уже может быть отправлена
			$procedure = 'p_MbuPerson_upd';
		}
		$query = "
			declare
				@MbuPerson_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @MbuPerson_id = :MbuPerson_id;
			exec {$procedure}
				@MbuPerson_id = @MbuPerson_id output,
				@Person_id = :Person_id,
				@UslugaTest_id = :UslugaTest_id,
				@MbuPerson_IsBact = :MbuPerson_IsBact,
				@BactMicro_id = :BactMicro_id,
				@MbuStatus_id = :MbuStatus_id,
				@InterpretationResult_id = :InterpretationResult_id,
				@MbuPerson_sendDT = :MbuPerson_sendDT,
				@MbuPerson_GUID = null,
				@UslugaComplex_oid = :UslugaComplex_oid,
				@MbuPerson_AnswerCode = :MbuPerson_AnswerCode,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @MbuPerson_id as MbuPerson_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSql($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
				sql_log_message('error', 'Mbu_model save error',var_export(array('query' => getDebugSql($query, $data), 'error' => $resp[0]['Error_Msg']), true));
				return false;
			}
			return true;
		} else {
			sql_log_message('error', var_export(array('query' => $query, 'params' => $data, 'error' => sqlsrv_errors()), true));
			return false;
		}
	}
	
	/**
	 * Читаем запись для отправки
	 */
	function loadRecord($data) {
		
		if (empty($data['MbuPerson_id'])) {
			return false;
		}
		// Достаем информацию для отправки по одной записи
		$query = "
			SELECT
				m.MbuPerson_id,
				-- Person
				m.Person_id,
				person.Person_guid,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				case when vip.VIPPerson_id is not null then 'anonymous' else '' end as patientUse,
				case
					when ps.Sex_id = 1 then 'male'
					when ps.Sex_id = 2 then 'female'
					else 'unknown'
				end as gender,
				convert(varchar(10), cast(Person_BirthDay as date), 120) as birthDate,
				ps.UAddress_id,
				uaddr.Address_Address as UAddress_AddressText,
				uaddr.Address_Zip as UAddress_Zip,
				UStreet.KLStreet_Name as UStreet_Name,
				URegion.KLRgn_Name as URegion_Name,
				UCity.KLCity_Name as UCity_Name,
				ps.PAddress_id,
				paddr.Address_Address as PAddress_AddressText,
				uaddr.Address_Zip as PAddress_Zip,
				PStreet.KLStreet_Name as PStreet_Name,
				PRegion.KLRgn_Name as PRegion_Name,
				PCity.KLCity_Name as PCity_Name,
				m.UslugaTest_id,
				ut.UslugaTest_guid,
				ut.UslugaTest_Comment,
				convert(varchar(10), cast(ut.UslugaTest_CheckDT as date), 120) as UslugaTest_CheckDT,
				uc.UslugaComplex_Name,
				uc.UslugaComplex_code,
				ut.UslugaTest_ResultValue,
				ut.UslugaTest_ResultLower,
				ut.UslugaTest_ResultUpper,
				m.MbuPerson_IsBact,
				m.BactMicro_id,
				m.MbuStatus_id,
				ms.MbuStatus_Name,
				m.InterpretationResult_id,
				convert(varchar(10), cast(m.MbuPerson_sendDT as date), 120) as MbuPerson_sendDT,
				m.MbuPerson_GUID,
				m.UslugaComplex_oid,
				convert(varchar(10), cast(ls.EvnLabSample_DelivDT as date), 120) as EvnLabSample_DelivDT,
				lr.EvnLabRequest_guid,
				ls.EvnLabSample_guid,
				lr.EvnLabRequest_id,
				ltm.LabTestMaterial_code, --RefMaterial_NSI
				lt.LabTest_code,
				ltg.LabTestGroup_code,
				ir.InterpretationResult_code,
				bmp.BactMicroProbe_id,
				bmp.BactMicroProbe_IsNotShown,
				bmas.BactMicroABPSens_ShortName,
				bmpa.BactMicroProbeAntibiotic_id,
				ba.BactAntibiotic_code,
				bm.BactMicro_code,
				bmw.BactMicroWorld_code,
				mu.MeasureUnit_Code,
				m.MbuPerson_AnswerCode
				
			FROM
				MbuPerson m WITH (NOLOCK)
				inner join v_PersonState ps (nolock) on ps.Person_id = m.Person_id
				left join person (nolock) on person.Person_id = m.Person_id
				left join VIPPerson vip (nolock) on vip.Person_id = ps.Person_id -- возможно тут надо фильтр еще и по Lpu
				-- адрес регистрации
				left join v_Address uaddr with (nolock) on ps.UAddress_id = uaddr.Address_id
				left join v_KLRgn URegion with(nolock) on URegion.KLRgn_id = uaddr.KLRgn_id
				left join v_KLSubRgn USubRgn with(nolock) on USubRgn.KLSubRgn_id = uaddr.KLSubRgn_id
				left join v_KLCity UCity with(nolock) on UCity.KLCity_id = uaddr.KLCity_id
				left join v_KLTown UTown with(nolock) on UTown.KLTown_id = uaddr.KLTown_id
				left join v_KLStreet UStreet with(nolock) on UStreet.KLStreet_id = uaddr.KLStreet_id
				-- адрес проживания
				left join v_Address paddr with (nolock) on ps.PAddress_id = paddr.Address_id
				left join v_KLRgn PRegion with(nolock) on PRegion.KLRgn_id = paddr.KLRgn_id
				left join v_KLSubRgn PSubRgn with(nolock) on PSubRgn.KLSubRgn_id = paddr.KLSubRgn_id
				left join v_KLCity PCity with(nolock) on PCity.KLCity_id = paddr.KLCity_id
				left join v_KLTown PTown with(nolock) on PTown.KLTown_id = paddr.KLTown_id
				left join v_KLStreet PStreet with(nolock) on PStreet.KLStreet_id = paddr.KLStreet_id
				
				inner join v_UslugaTest ut (nolock) on ut.UslugaTest_id = m.UslugaTest_id
				left join v_EvnLabSample ls with(nolock) on ut.EvnLabSample_id = ls.EvnLabSample_id
				left join v_EvnLabRequest lr with(nolock) on lr.EvnLabRequest_id = ls.EvnLabRequest_id
				left join v_RefSample RefSample_id_ref with(nolock) on RefSample_id_ref.RefSample_id = ls.RefSample_id
				left join v_RefMaterial rm with(nolock) on rm.RefMaterial_id = RefSample_id_ref.RefMaterial_id
				left join v_RefValues rv with(nolock) on rv.RefValues_id = ut.RefValues_id
				left join lis.v_Unit rvu with(nolock) on rvu.Unit_id = rv.Unit_id
				left join nsi.MeasureUnit mu with(nolock) on mu.MeasureUnit_id = rvu.MeasureUnit_id
				left join nsi.LabTestMaterial ltm with(nolock) on ltm.LabTestMaterial_id = rm.LabTestMaterial_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ut.UslugaComplex_id
				-- выбираем LabTest_id - todo - по идее надо перенести это поле прямо в MbuPerson
				left join UslugaComplex ucg (nolock) on uc.UslugaComplex_2011id = ucg.UslugaComplex_id
				left join nsi.NomenMedUsluga nmu (nolock) on nmu.NomenMedUsluga_Code = IsNull(uc.UslugaComplex_oid, ucg.UslugaComplex_oid)
				--left join nsi.LabTestLink ltl (nolock) on ltl.NomenMedUsluga_id = nmu.NomenMedUsluga_id
				left join nsi.LabTest lt (nolock) on lt.LabTest_id = ut.LabTest_id
				left join nsi.LabTestGroup ltg (nolock) on ltg.LabTestGroup_id = lt.LabTestGroup_id
				left join nsi.InterpretationResult ir (nolock) on m.InterpretationResult_id = ir.InterpretationResult_id
				left join v_MbuStatus ms (nolock) on ms.MbuStatus_id = m.MbuStatus_id
				-- и бактериология
				left join v_BactMicroProbe bmp (nolock) on bmp.UslugaTest_id = ut.UslugaTest_id and bmp.EvnLabSample_id = ut.EvnLabSample_id
				left join v_BactMicroProbeAntibiotic bmpa (nolock) on bmp.BactMicroProbe_id = bmpa.BactMicroProbe_id and ut.UslugaTest_id = bmpa.UslugaTest_id
				left join v_BactMicroABPSens bmas (nolock) on bmas.BactMicroABPSens_id = bmpa.BactMicroABPSens_id
				left join v_BactAntibiotic ba (nolock) on bmpa.BactAntibiotic_id = ba.BactAntibiotic_id 
				left join v_BactMicro bm (nolock) on bmp.BactMicro_id = bm.BactMicro_id 
				left join v_BactMicroWorld bmw (nolock) on bmw.BactMicroWorld_id = bm.BactMicroWorld_id 
			where
				m.MbuPerson_id = :MbuPerson_id and m.MbuStatus_id!=3
		";
		
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$row = $result->result('array');
			if (count($row)>0) {
				return $row[0];
			}
		}
		return false;
	}
	
	
	/**
	 * Читаем информацию из MbuLpu (Справочник МО для работы с ПАК НИЦ МБУ)
	 */
	function getMbuLpu($data) {
		if (empty($data['Lpu_id'])) { // Получаем информацию по текущей МО
			return false;
		}
		// Достаем информацию для отправки из "Справочник МО для работы с ПАК НИЦ МБУ"
		$query = "
			declare @curdate datetime =  dbo.tzgetdate();
			SELECT top 1
				ml.MbuLpu_id,
				ml.Lpu_id,
				ml.MbuLpu_oid,
				ml.MbuLpu_GUID,
				ml.MbuLpu_Token,
				ml.MbuLpu_begDT,
				ml.MbuLpu_endDT,
				pt.PassportToken_tid
			FROM
				MbuLpu ml WITH (NOLOCK)
				inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = ml.Lpu_id
				left join fed.PassportToken pt with(nolock) on pt.Lpu_id = Lpu.lpu_id
			where
				ml.Lpu_id = :Lpu_id 
				and MbuLpu_begDT <= @curdate and IsNull(MbuLpu_endDT, @curdate) >= @curdate
			order by ml.MbuLpu_id desc
		";
		//echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$row = $result->result('array');
			if (count($row)>0) {
				return $row[0];
			}
		}
		return false;
	}

	/**
	 * Читаем записи для отображения в гриде
	 */
	function loadList($data) {
		
		$where = array();
		if (!empty($data['Search_SurName'])) {
			$where[] = "ps.Person_SurName like :Search_SurName+'%'";
		}
		if (!empty($data['Search_FirName'])) {
			$where[] = "ps.Person_FirName like :Search_FirName+'%'";
		}
		if (!empty($data['Search_SecName'])) {
			$where[] = "ps.Person_SecName like :Search_SecName+'%'";
		}
		if (!empty($data['begDate']) && !empty($data['endDate'])) {
			$where[] = 'm.MbuPerson_sendDT between :begDate and :endDate';
		} else {
			// фильтр по дате пустой - вернем ошибку 
			return false;
		}
		
		$wheres = implode(' AND ', $where);
		if (strlen($wheres)) {
			$wheres = 'WHERE '.$wheres;
		} else {
			$wheres = 'WHERE (1=1)';
		}
		
		$query = "
			SELECT
				m.MbuPerson_id,
				m.Person_id,
				ps.Person_SurName+' '+isnull(ps.Person_FirName,'')+' '+isnull(ps.Person_SecName,'') as Person_Fio,
				m.UslugaTest_id,
				uc.UslugaComplex_Name,
				ut.UslugaTest_ResultValue,
				m.MbuPerson_IsBact,
				m.BactMicro_id,
				m.MbuStatus_id,
				ms.MbuStatus_Name,
				m.InterpretationResult_id,
				convert(varchar,cast(m.MbuPerson_sendDT as datetime),104) as MbuPerson_sendDT,
				m.MbuPerson_GUID,
				m.UslugaComplex_oid,
				m.MbuPerson_AnswerCode
			FROM
				MbuPerson m WITH (NOLOCK)
				inner join v_PersonState ps (nolock) on ps.Person_id = m.Person_id
				inner join v_UslugaTest ut (nolock) on ut.UslugaTest_id = m.UslugaTest_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ut.UslugaComplex_id
				left join v_MbuStatus ms (nolock) on ms.MbuStatus_id = m.MbuStatus_id
			{$wheres}
			order by 
				MbuPerson_id desc
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
	/**
	 *	Подготовка и отправка информации в ПАК НИЦ МБУ
	 */
	function sendMbu($data) {
		// todo: Надо будет доработать, пока просто меняем статус как будто данные успешно отправлены
		$b = array(
			'Order'=>array(),
			'OrderResponse'=>array(),
			'DiagnosticReport'=>array(),
			'Specimen'=>array(),
			'Observation'=>array()
		);
		
		// Выбираем данные по МО и проверяем конфиг
		$mo = $this->getMbuLpu(array('Lpu_id'=>$data['Lpu_id']));
		// Тестовые данные 
		/*
		$mo['MbuLpu_oid'] = '1.2.643.2.69.1.2.59.1'; // OID системы 
		$mo['MbuLpu_GUID'] = '49f9ecc5-8c2d-4cd4-b816-2ef13de5b87a'; // IDMO (заполяется вручную при добавлении записи в MbuLpu)
		$mo['MbuLpu_Token'] = '5afb5f18-a104-4ee0-bc61-1c6fc4cf95de'; // TOKEN
		*/
		//$mo['PassportToken_tid'] = '1.2.643.5.1.13.13.12.2.58.5728'; // Как оказалось этого не надо
		//$mo['MbuLpu_IDMO'] = '39e3cbe6-2af0-4476-bfd6-45ac5b644088'; // Как оказалось это MbuLpu_GUID
		
		// Сервис воспринимает только нижний регистр
		$mo['MbuLpu_GUID'] = strtolower($mo['MbuLpu_GUID']);
		$mo['MbuLpu_Token'] = strtolower($mo['MbuLpu_Token']);
		
		// Если нет данных и конфиг с настройками адреса не настроен - пишем об этом 
		$config = $this->config->item('MBU');
		if (!isset($config)) {
			return array('success'=>false, 'Error_Msg'=>'В конфигурационном файле не заполнены данные для подключения к ПАК НИЦ МБУ');
		}
		if (count($mo)==0) {
			return array('success'=>false, 'Error_Msg'=>'Для данной МО нет информации в таблице «Идентификаторы МО для работы с ПАК НИЦ МБУ»');
		}
		// Формируем бандлы
		// Bundle Order // Передача информации о ЛПУ откуда поступил биоматериал и какой лабораторией выполнено исследовани
		$b['Order']['fullUrl'] = 'urn:uuid:'.$mo['MbuLpu_GUID']; // todo: И это скорее всего должен быть гуид IDMO
		$b['Order']['resource'] = array(
			'resourceType'=>'Order',
			'source'=>array('reference'=>'Organization/'.$mo['MbuLpu_GUID']), // todo: Здесь должен быть идентификатор МО направившей // надо проверить это наши гуиды или IDMO из файла 
			'target'=>array('reference'=>'Organization/'.$mo['MbuLpu_GUID']), // todo: Здесь должен быть идентификатор МО выполнившей
			'detail'=>array('reference'=>'')
		);
		$b['Order']['request'] = array(
			'method'=>'POST',
			'url'=>'Order'
		);
		
		// Выбираем данные по записи
		$record = $this->loadRecord($data);
		
		// Проверки перед отправкой на наличие данных и правила, проверки сробатывают только при ручном режиме 
		if (!empty($data['mode']) && $data['mode']=='manual') {
			if (empty($mo['MbuLpu_Token']) || empty($mo['MbuLpu_oid']) || empty($mo['MbuLpu_GUID'])) {
				return array('success'=>false, 'Error_Msg'=>'Не заполнены token, oid или guid в таблице MbuLpu по текущей МО!');
			}
			// Далее проверка на обязательные поля $record
			$refields = '';
			$requireds = array(
				'all'=>array('UslugaTest_guid','LabTestMaterial_code','LabTest_code','UslugaComplex_code','UslugaTest_CheckDT','InterpretationResult_code','EvnLabRequest_guid','EvnLabSample_DelivDT','Person_guid'),
				'bactmicro'=>array('BactMicroWorld_code','BactMicroProbe_guid','BactMicro_code'),
				'bactmicroab'=>array('BactMicroProbeAntibiotic_guid','BactAntibiotic_code','BactMicroABPSens_ShortName')
			);
			foreach ($requireds as $group=>$block) {
				if (($group=='all') || 
					($group=='bactmicro' && $record['BactMicroProbe_IsNotShown']==1) || 
					($group=='bactmicroab' && !empty($record['BactMicroProbeAntibiotic_id']))) {
					foreach ($block as $field) {
						if (empty($record[$field])) {
							$refields .= $field.', ';
						}
					}
				}
			}
			if (!empty($refields)) {
				$refields = substr($refields,0,-2);
				return array('success'=>false, 'Error_Msg'=>'Не заполнены обязательные поля для отправки в ПАК НИЦ МБУ: '.$refields);
			}
			// Проверки на обязательность заполнения при условии числового теста
			if (empty($record['BactMicroProbe_id']) && is_numeric($record['UslugaTest_ResultValue'])) {
				// Если не бактериология и значение числовое, то проверим заполненность кода единицы изменения
				if (empty($record['MeasureUnit_Code'])) {
					return array('success'=>false, 'Error_Msg'=>'Для количественного показателя отсутствует связь с НСИ справочников единиц изменения (MeasureUnit)');
				}
			}
		}
		$record['UslugaTest_ResultRange'] = (!empty($record['UslugaTest_ResultValue']))?$record['UslugaTest_ResultValue']:' ';
		
		
		// Тестовые данные для клинического случая
		/*
		$record['LabTestMaterial_code'] = '108'; // Кровь венозная
		$record['LabTest_code'] = '1017987';//Лейкоциты в крови методом ручного подсчёта //'1000199';
		$record['LabTestGroup_code'] = '101'; //Гематологические исследования // '301';
		$record['UslugaTest_guid'] = 'd1df1fc8-91a5-4a4c-ba5e-13fdecfb5d7f';
		$record['UslugaComplex_code'] = 'A02.02.003';
		$record['MeasureUnit_Code'] = '164';//мкМЕ/мл //'207';
		
		// Текстовый результат
		
		$record['LabTestMaterial_code'] = '128'; // Кожа и ее придатки
		$record['LabTest_code'] = '1037571';//Протокол цитологического исследования соскоба, пунктата, отпечатков кожи
		$record['LabTestGroup_code'] = '501'; //Цитологические исследования // '301';
		$record['UslugaTest_guid'] = '75eb7828-1bd3-4bbb-9023-248cd6a9f28c';
		$record['UslugaComplex_code'] = 'A02.01.003'; //A02.01.003 	Определение сальности кожи 
		//$record['MeasureUnit_Code'] = '164';//мкМЕ/мл //'207';
		$record['UslugaTest_ResultValue'] = 'нормальная';
		$record['UslugaTest_ResultRange'] = 'от сальной до нормальной';
		
		// тестовые данные для микробиологии
		
		$record['BactMicroProbe_id'] = '136'; // пока это условный признак бактериологического исследования
		$record['LabTestMaterial_code'] = '117';
		$record['LabTest_code'] = '1132877';
		$record['LabTestGroup_code'] = '601';
		$record['UslugaComplex_code'] = 'A08.03.003';
		$record['InterpretationResult_code'] = 'ND';
		
		
		$record['BactMicroProbe_IsNotShown'] = 1;
		$record['BactMicroABPSens_ShortName'] = 'R';
		$record['BactMicroProbeAntibiotic_id'] = 104;
		$record['InterpretationResult_code'] = 'DET';
		$record['BactAntibiotic_code'] = '264';
		$record['BactMicro_code'] = '5018965';
		$record['BactMicroWorld_code'] = 1;
		//$record['UslugaTest_CheckDT'] = '2019-12-11';
		$record['BactMicroProbe_guid'] = 'b375e4f6-8e0d-42e0-99c0-4478e27a7cbf';
		$record['BactMicroProbeAntibiotic_guid'] = '1aa92e7e-a85c-46aa-824c-7f7c8c07a991';
		*/
		//$record['EvnLabRequest_guid'] = "054639DD-4CFC-41C5-BBDD-431762A3838F";
		
		// Bundle OrderResponse // Передача общей информации о результате исследований
		$b['OrderResponse']['fullUrl'] = 'urn:uuid:'.$record['EvnLabRequest_guid'];
		$b['OrderResponse']['resource'] = array(
			'resourceType'=>'OrderResponse',
			'identifier'=>array('system'=>'urn:oid:'.$mo['MbuLpu_oid'],'value'=>$record['UslugaTest_id']),
			'request'=>array('reference'=>$b['Order']['fullUrl']),
			'date'=>$record['MbuPerson_sendDT'],
			'who'=>$b['Order']['resource']['target'],
			'orderStatus'=>'completed',
			'description'=>(!empty($record['UslugaTest_Comment']))?$record['UslugaTest_Comment']:'',
			'fulfillment'=>array('reference'=>'urn:uuid:'.$record['MbuPerson_GUID']), // todo $b['DiagnosticReport']['fullUrl']
		);
		$b['OrderResponse']['request'] = array(
			'method'=>'POST',
			'url'=>'OrderResponse'
		);
		
		// Bundle DiagnosticReport // Передача информации о результате исследования в разрезе услуги, содержит ссылки на результаты каждого теста
		$b['DiagnosticReport']['fullUrl'] = 'urn:uuid:'.$record['MbuPerson_GUID']; // 
		$b['DiagnosticReport']['resource'] = array(
			'resourceType'=>'DiagnosticReport',
			'meta'=>array('security'=>array('code'=>'R')),
			'status'=>'final',
			'code'=>array('coding'=>array('system'=>'urn:oid:1.2.643.5.1.13.13.11.1070', 'version'=>'1','code'=>$record['UslugaComplex_code'])), // todo:•	В параметре version указывается версия справочника таблица nsi.RefTableRegistryVersionFile 
			'subject' => array('reference'=>'urn:uuid:'.$record['Person_guid']), // Ссылка на patient
			'category'=>array('coding'=>array('system'=>'urn:oid:1.2.643.5.1.13.13.11.1117', 'version'=>'1','code'=>$record['LabTestGroup_code'])),  // LabTestGroup_code, идентификатор ххраним на LabTest.LabTestGroup_id
			'performer'=>array('reference'=>''), // Ссылка. Должна передаваться пустая ссылка.
			'specimen'=>array('reference'=>'urn:uuid:'.$record['EvnLabSample_guid']), // Ссылка. Соотнесение с материалом исследования. Должен передаваться ресурс Specimen в Bundle
			'issued'=>$record['UslugaTest_CheckDT'], //UslugaTest_CheckDT в формате <гггг>-<мм>-<дд>
			'effectiveDatetime'=>$record['UslugaTest_CheckDT'], //UslugaTest_CheckDT в формате <гггг>-<мм>-<дд> // todo: 03.12 вдруг неожиданно начал ругаться на неизвестное поле
			'result'=>array(array('reference'=>'urn:uuid:'.$record['UslugaTest_guid'])), // Ссылка. Соотнесение с результатом теста. Должен передаваться ресурс Observation
			// 'conclusion'=>'', // Не передается
		);
		$b['DiagnosticReport']['request'] = array(
			'method'=>'POST',
			'url'=>'DiagnosticReport'
		);
		
		// Bundle Patient Передача информации о пациенте
		$b['Patient'] = array();
		$b['Patient']['fullUrl'] = 'urn:uuid:'.$record['Person_guid'];
		$b['Patient']['resource'] = array(
			'resourceType'=>'Patient',
			'identifier'=>array('system'=>'urn:oid:1.2.643.5.1.13.2.7.100.5','value'=>$record['Person_id'],'assigner'=>array('display'=>$mo['MbuLpu_oid'])),
			'managingOrganization'=>array('reference'=>'Organization/'.$mo['MbuLpu_GUID']), // ОИД организации из паспорта МО, раздел «Идентификация»
			//'name'=>array('family'=>array($record['Person_SurName'],$record['Person_SecName']),'given'=>array($record['Person_FirName'])), 
			'gender'=>$record['gender'], 
			'birthDate'=>$record['birthDate']//, 
			//'address'=>array()
		);
		// Если анонимный (випперсона)
		/*if ($record['patientUse']=='anonymous') {
			$b['Patient']['resource']['name']['family'] = array('Анонимный');
			$b['Patient']['resource']['name']['given'] = array('Анонимный');
			$b['Patient']['resource']['name']['use'] = $record['patientUse'];
		}*/
		// Заполняем адрес
		/*if ($record['patientUse']!='anonymous') { // Не передается, если name.use = anonymous (Тип адреса (справочник FHIR. OID: 1.2.643.2.69.1.1.1.41)
			if (!empty($record['UAddress_id'])) { // адрес регистрации
				$b['Patient']['resource']['address'][] = array(
					'use'=>'temp',
					'text'=>$record['UAddress_AddressText'],
					'line'=>$record['UStreet_Name'], // todo: Еще дом и квартира
					'state'=>$record['URegion_Name'],
					'district'=>$record['URegion_Name'], // todo: Район
					'city'=>$record['UCity_Name'],
					'postalCode'=>$record['UAddress_Zip']
				);
			}
			if (!empty($record['PAddress_id'])) { // адрес проживания
				$b['Patient']['resource']['address'][] = array(
					'use'=>'home',
					'text'=>$record['PAddress_AddressText'],
					'line'=>$record['PStreet_Name'], // todo: Еще дом и квартира
					'state'=>$record['PRegion_Name'], 
					'district'=>$record['PRegion_Name'], // todo: Район
					'city'=>$record['PCity_Name'],
					'postalCode'=>$record['PAddress_Zip']
				);
			}
		}*/
		$b['Patient']['request'] = array(
			'method'=>'POST',
			'url'=>'Patient'
		);
		
		// Bundle Specimen // передача информации о забранном материале
		$b['Specimen']['fullUrl'] = 'urn:uuid:'.$record['EvnLabSample_guid'];
		$b['Specimen']['resource'] = array(
			'resourceType'=>'Specimen',
			'type'=>array('coding'=>array('system'=>'urn:oid:1.2.643.5.1.13.13.11.1081', 'version'=>'1','code'=>$record['LabTestMaterial_code'])),
			'subject' => array('reference'=>$b['Patient']['fullUrl']),
			'collection'=>array('collectedDateTime'=>$record['EvnLabSample_DelivDT']), 
		);
		$b['Specimen']['request'] = array(
			'method'=>'POST',
			'url'=>'Specimen'
		);
		
		// Bundle Observation Передача результата лаборатории или микробиологического исследования
		$b['Observation']['fullUrl'] = 'urn:uuid:'.$record['UslugaTest_guid'];
		$b['Observation']['resource'] = array(
			'resourceType'=>'Observation',
			'status'=>'final',
			'code'=>array('coding'=>array('system'=>'urn:oid:1.2.643.5.1.13.13.11.1080', 'version'=>'1','code'=>$record['LabTest_code'])), // todo: version
			'interpretation'=>array('coding'=>array('system'=>'urn:oid:1.2.643.5.1.13.13.11.1381', 'version'=>'1','code'=>$record['InterpretationResult_code'])),
			'performer'=>array('reference'=>''),
			'comments'=>(!empty($record['UslugaTest_Comment']))?$record['UslugaTest_Comment']:'',
			'issued'=>$record['UslugaTest_CheckDT']
		);
		// Если микробиологическое исследование (interpretation определяется ранее при формировании MbuPerson: A и N при обычном, DET и ND при бактериологии)
		if (!empty($record['BactMicroProbe_id'])) {
			// заполняем related //ссылка на микроорганизм - если микроорганизм обнаружен
			if ($record['BactMicroProbe_IsNotShown']==1) {
				$b['Observation']['resource']['related'] = array(array('target'=>array('reference'=>'urn:uuid:'.$record['BactMicroProbe_guid']))); // todo:!
			}
		} else { // для обычных заполняем значение и границы значений
			if (is_numeric($record['UslugaTest_ResultValue'])) {
				$b['Observation']['resource']['valueQuantity'] = array('value'=>$record['UslugaTest_ResultValue'],'code'=>$record['MeasureUnit_Code']);
				$b['Observation']['resource']['referenceRange'] = array('low'=>array('value'=>$record['UslugaTest_ResultLower'], 'code'=>$record['MeasureUnit_Code']),'high'=>array('value'=>$record['UslugaTest_ResultUpper'],'code'=>$record['MeasureUnit_Code'])); // MeasureUnit - справочник 1.2.643.5.1.13.13.11.1358
			} else {
				$b['Observation']['resource']['valueString'] = $record['UslugaTest_ResultValue'];
				$b['Observation']['resource']['referenceRange'] = array('text'=>$record['UslugaTest_ResultRange']); // todo: Текстовое значение для указания референсного
			}
			
		}
		$b['Observation']['request'] = array(
			'method'=>'POST',
			'url'=>'Observation'
		);
		
		if ($record['BactMicroProbe_IsNotShown']==1) { // $record['InterpretationResult_code']=='DET' // Если микроорганизм обнаружен, то добавляем блок для передачи информации о микроорганизме (таблица BactMicro)
			// Bundle Observation BactMicro
			if ($record['BactMicroWorld_code']==1) {
				$oid = '1.2.643.5.1.13.13.11.1087';
				$version = '2.1';
			} else {
				$oid = '1.2.643.5.1.13.13.11.1088';
				$version = '1.2';
			}
			$b['ObservationBactMicro']['fullUrl'] = 'urn:uuid:'.$record['BactMicroProbe_guid']; // todo: !
			$b['ObservationBactMicro']['resource'] = array(
				'resourceType'=>'Observation',
				'status'=>'final',
				'code'=>array('coding'=>array('system'=>'urn:oid:'.$oid, 'version'=>$version,'code'=>$record['BactMicro_code'])), // todo: version
				'interpretation'=>array('coding'=>array('system'=>'urn:oid:1.2.643.5.1.13.13.11.1381', 'version'=>'1','code'=>$record['InterpretationResult_code'])),
				'performer'=>array('reference'=>''),
				'issued'=>$record['UslugaTest_CheckDT'],
				'valueString'=>$record['UslugaTest_ResultValue'], // todo: ! valueQuantity
				'referenceRange'=>array('text'=>$record['UslugaTest_ResultValue']) // todo: Текстовое значения для указания референсного значения теста, если данных нет, то передаем пробел « »
			);
			// todo: Добавляем ссылки на антибиотики в цикле по количеству антибиотиков
			if (!empty($record['BactMicroProbeAntibiotic_id'])) { // todo: Пока только один, но их может быть несколько
				$b['ObservationBactMicro']['resource']['related'] = array(array('target'=>array('reference'=>'urn:uuid:'.$record['BactMicroProbeAntibiotic_guid']))); // todo:!
			}
			$b['ObservationBactMicro']['request'] = array(
				'method'=>'POST',
				'url'=>'Observation'
			);
			array_push($b['DiagnosticReport']['resource']['result'],array('reference'=>'urn:uuid:'.$record['BactMicroProbe_guid']));
		}
		if (!empty($record['BactMicroProbeAntibiotic_id'])) { // Если есть ссылка на антибиотик
			// Bundle Observation BactMicroProbeAntibiotic
			$b['ObservationAntibiotic']['fullUrl'] = 'urn:uuid:'.$record['BactMicroProbeAntibiotic_guid']; // todo: !
			$b['ObservationAntibiotic']['resource'] = array(
				'resourceType'=>'Observation',
				'status'=>'final',
				'code'=>array('coding'=>array('system'=>'urn:oid:1.2.643.2.69.1.1.1.74', 'version'=>'1','code'=>$record['BactAntibiotic_code'])), // todo: version
				'interpretation'=>array('coding'=>array('system'=>'urn:oid:1.2.643.5.1.13.13.11.1381', 'version'=>'1','code'=>$record['BactMicroABPSens_ShortName'])),
				'issued'=>$record['UslugaTest_CheckDT'],
				'performer'=>array('reference'=>'')
			);
			$b['ObservationAntibiotic']['request'] = array(
				'method'=>'POST',
				'url'=>'Observation'
			);
			array_push($b['DiagnosticReport']['resource']['result'],array('reference'=>'urn:uuid:'.$record['BactMicroProbeAntibiotic_guid']));
		}
		
		// Формируем общий запрос и отправляем в сервис
		// Основное содержание запроса
		$body = array(
			'resourceType'=>'Bundle',
			'meta'=>array('profile'=>array('StructureDefinition/21f687dd-0b3b-4a7b-af8f-04be625c0201')),
			'type'=>'transaction',
			'entry'=>array()
		);
		// Заполняем entry для общего запроса
		foreach($b as $key=>$bundle) {
			//if ($key == 'Order' || $key == 'OrderResponse' || $key == 'DiagnosticReport') {
				$body['entry'][] = $bundle;
			//}
		}
		// Отправляем в сервис
		$response = $this->exec('POST', $mo, $body);
		// Логируем ответ
		// todo: 
		// Ловим ответ, записываем в табличку и меняем статус взависимости от ответа
		return $this->setAnswer($data,$response);
	}
	
	/**
	 * Выполнение запросов к сервису ПАК НИЦ МБУ и обработка ошибок, которые возвращает сервис
	 */
	function exec($methodType, $params, $data) {
		$this->load->library('swServiceMBU', $this->config->item('MBU'), 'service');

		$result = $this->service->data($methodType, $params, $data);
		if (is_array($result) && !empty($result['errorMsg'])) {
			//$log->add(false, $result['errorMsg']);
			return $result;
		}
		return $result;
	}
	/**
	 *	Изменение статуса 
	 */
	function setStatus($data) {
		if (empty($data['MbuStatus_id'])) {
			return false;
		}
		if (empty($data['MbuPerson_id'])) {
			return false;
		}
		if (empty($data['pmUser_id'])) {
			return false;
		}
		$query = "
			update
				MbuPerson with(rowlock)
			set
				MbuStatus_id = :MbuStatus_id,
				pmUser_updID = :pmUser_id,
				MbuPerson_updDT = dbo.tzgetdate()
			where
				MbuPerson_id = :MbuPerson_id
		";
		$res = $this->db->query($query, $data);
		if ($res) {
			return true;
		}
		return false;
	}
	/**
	 *	Запись ответа
	 */
	function setAnswer($data, $response) {
		if (empty($data['MbuPerson_id'])) {
			return false;
		}
		if (empty($data['pmUser_id'])) {
			return false;
		}
		if (!empty($response['errorCode'])) {
			$data['MbuPerson_AnswerCode'] = $response['errorCode'];
			$data['MbuStatus_id'] = 4;
		} else {
			$data['MbuPerson_AnswerCode'] = '200';
			$data['MbuStatus_id'] = 3;
		}
		$query = "
			update
				MbuPerson with(rowlock)
			set
				MbuStatus_id = :MbuStatus_id,
				MbuPerson_AnswerCode = :MbuPerson_AnswerCode,
				pmUser_updID = :pmUser_id,
				MbuPerson_updDT = dbo.tzgetdate()
			where
				MbuPerson_id = :MbuPerson_id
		";
		$res = $this->db->query($query, $data);
		if ($res) {
			return true;
		}
		return false;
	}
}