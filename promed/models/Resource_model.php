<?php

class Resource_model extends swModel
{
	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *    Загрузка грида ресурсов
	 */
	function loadResourceMedServiceGrid($data)
	{
		$filters = "";

		$query = "
			SELECT
				-- select
				Res.Resource_id,
				Res.MedService_id,
				Res.Resource_Name,
				convert(varchar(10),Res.Resource_begDT,104) as Resource_begDT,
				convert(varchar(10),Res.Resource_endDT,104) as Resource_endDT
				-- end select
			FROM 
				-- from
				v_Resource Res with(nolock)
				-- end from
			where
				-- where
				Res.MedService_id = :MedService_id
				-- end where
			order by
				-- order by
				Res.Resource_Name
				-- end order by
				
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *    Загрузка комбо ресурсов
	 */
	function loadResourceList($data)
	{
		$filters = "";
		$queryParams = array();

		if (!empty($data['Resource_id'])) {
			$filters .= " and Res.Resource_id = :Resource_id";
			$queryParams['Resource_id'] = $data['Resource_id'];
		} else if (!empty($data['MedService_id'])) {
			$filters .= " and Res.MedService_id = :MedService_id";
			$queryParams['MedService_id'] = $data['MedService_id'];
		} else {
			return array(); // пусто
		}

		if (!empty($data['UslugaComplex_ids']) && is_array($data['UslugaComplex_ids'])) {
			$i = 0;
			foreach($data['UslugaComplex_ids'] as $oneUslugaComplex) {
				$i++;
				$field = "UslugaComplex{$i}_id";
				$queryParams[$field] = $oneUslugaComplex;
				$filters .= " and exists(
					select top 1
						ucr.UslugaComplexResource_id
					from
						v_UslugaComplexResource ucr (nolock)
						inner join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
					where
						ucr.Resource_id = Res.Resource_id
						and ucms.UslugaComplex_id = :{$field}
				)";
			}
		}

		if (!empty($data['UslugaComplex_id'])) {
			$queryParams["UslugaComplex_id"] = $data['UslugaComplex_id'];
			$filters .= " and exists(
				select top 1
					ucr.UslugaComplexResource_id
				from
					v_UslugaComplexResource ucr (nolock)
					inner join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
				where
					ucr.Resource_id = Res.Resource_id
					and ucms.UslugaComplex_id = :UslugaComplex_id
			)";
		}

		if (!empty($data['onDate'])) {
			$queryParams['onDate'] = $data['onDate'];
			$filters .= " and ISNULL(Resource_begDT, :onDate) <= :onDate";
			$filters .= " and ISNULL(Resource_endDT, :onDate) >= :onDate";
		}

		$query = "
			SELECT
				Res.Resource_id,
				Res.Resource_Name,
				Res.MedService_id,
				MS.MedService_Name
			FROM
				v_Resource Res (nolock)
				left join v_MedService MS (nolock) on MS.MedService_id = Res.MedService_id
			where
				(1 = 1)
				{$filters}

		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Отправка данных направлений с типом функциональная диагностика в сторонние сервисы
	 *
	 * @param array $data Данные полученные с сервера
	 * @return void
	 */
	public function transferDirection($data, &$return_data=null){

		if (!empty($return_data)) $return_data['archimed_status'] = 0;

		if (!isset($data['EvnPrescr_id'])) {
			if (isset($return_data['archimed_status'])) $return_data['archimed_status'] = 1;
			return;
		}

		$EvnPrescr_id = $data['EvnPrescr_id'];

		// Получаем данные направления но только с типом функциональная диагностика
		$sql = "
			SELECT TOP 1
				ed.EvnDirection_id,
				ed.EvnDirection_Descr,
				ed.MedService_id,
				ps.Person_id,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				CONVERT(varchar,ps.Person_BirthDay,104) as Person_BirthDay,
				ps.Sex_id,
				a.Address_Address,
				CONVERT(varchar,ttms.TimetableMedService_begTime,104) as TimetableMedService_begTime_Date,
				CONVERT(varchar,ttms.TimetableMedService_begTime,108) as TimetableMedService_begTime_Time,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				mp.MedPersonal_id,
				mp.Person_Fio as MedPersonal_Person_Fio
			FROM
				-- Назначение с типом функциональная диагностика
				v_EvnPrescrFuncDiag epfd
				-- Связь назначения и услуги
				INNER JOIN v_EvnPrescrFuncDiagUsluga epfdu WITH(nolock) ON(epfdu.EvnPrescrFuncDiag_id=epfd.EvnPrescrFuncDiag_id)
				-- Услуга
				INNER JOIN v_UslugaComplex uc WITH(nolock) ON(uc.UslugaComplex_id=epfdu.UslugaComplex_id)
				-- Связь назначения и направления
				INNER JOIN v_EvnPrescrDirection epd WITH(nolock) ON(epd.EvnPrescr_id=epfd.EvnPrescrFuncDiag_id)
				-- Направление
				INNER JOIN v_EvnDirection_all ed WITH(nolock) ON(ed.EvnDirection_id=epd.EvnDirection_id)
				-- Данные врача
				LEFT JOIN v_MedPersonal mp WITH(nolock) ON(mp.MedPersonal_id=ed.MedPersonal_id)
				-- Данные пользователя
				INNER JOIN v_PersonState ps WITH(nolock) ON(ps.Person_id=ed.Person_id)
				-- Адрес пользователя
				LEFT JOIN v_Address a WITH(nolock) ON(a.Address_id=ps.UAddress_id)
				-- Данные записи на прием
				LEFT JOIN v_TimeTableMedService_lite ttms WITH(nolock) ON(ttms.TimeTableMedService_id=ed.TimeTableMedService_id)
			WHERE
				epfd.EvnPrescrFuncDiag_id=:EvnPrescr_id
		";

		$person = $this->db->query($sql,array('EvnPrescr_id'=>$EvnPrescr_id))->row_array();

		if (empty($person)) {
			if (isset($return_data['archimed_status'])) $return_data['archimed_status'] = 2;
			return;
		}

		// Отправляем в АрхиМед
		$access = $this->retrieveAccessData($person['MedService_id']);
		if (empty($access['MedService_WialonURL']) || empty($access['MedService_WialonPort'])) {
			if (isset($return_data['archimed_status'])) $return_data['archimed_status'] = 3;
			return;
		}

		// Данные для отправки
		$send_data = array(
			'PATIENT_ID' => $person['Person_id'], // ID пациента в БД ПроМед
			'PATIENT_NAME' => trim(trim($person['Person_SurName']) . ' ' . trim($person['Person_FirName']) . ' ' . trim($person['Person_SecName'])), // ФИО пациента;
			'PATIENT_DATEOFBIRTH' => $person['Person_BirthDay'], // дата рождения пациента d.m.Y
			'PATIENT_SEX' => $person['Sex_id'] == 1 ? 'м' : ($person['Sex_id'] == 2 ? 'ж' : ''), // пол пациента м / ж
			'PATIENT_HOME_ADDRESS' => $person['Address_Address'], // домашний адрес пациента
			'PRESCRIPTIO_ID' => $person['EvnDirection_id'], // ID направления в БД ПроМед
			'STUDY_DATE' => (string)$person['TimetableMedService_begTime_Date'], // дата, на которую назначено исследование d.m.Y
			'STUDY_TIME' => preg_replace('#([0-9]{2}.[0-9]{2}).[0-9]{2}$#', '$1', $person['TimetableMedService_begTime_Time'] ), // время, на которое назначено исследование H:i
			'STUDY_TYPE_ID' => '', // ID вида исследования ПроМеда (Рентген, УЗИ, КТ и пр.).
			'STUDY_LIST' => array( // список исследований (предоставляемых услуг?) из БД ПроМед;
				array(
					'STUDY_ID' => $person['UslugaComplex_id'], // ID исследования (услуги) в БД ПроМед;
					'STUDY_NAME' => $person['UslugaComplex_Name'] // наименование исследования (услуги) в БД ПроМед;
				)
			),
			'DOCTOR_ID' => $person['MedPersonal_id'], // ID врача, назначившего исследование;
			'DOCTOR_NAME' => $person['MedPersonal_Person_Fio'], // ФИО врача, назначившего исследование;
			'STUDY_PURPOSE' => (string)$person['EvnDirection_Descr'], // цель исследования
		);

		// JSON_UNESCAPED_UNICODE для php 5.3
		$send_data_json = json_encode($send_data);
		$send_data_json = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $send_data_json);

		$this->load->helper('CURL');
		$result = CURL(
			$access['MedService_WialonURL'].':'.$access['MedService_WialonPort'].'/STUDY_PRESCRIPTION_PM/',
			$send_data_json,
			'POST',
			null,
			array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
				)
			)
		);

		if (isset($return_data['archimed_status'])) $return_data['archimed_status'] = $result ? true : false;
	}

	/**
	 * Возвращает данные для аутентификации в АрхиМед и пр.
	 * @param array $MedService_id
	 * @return array or false
	 */
	protected function retrieveAccessData( $MedService_id ){

		if (empty($MedService_id)) return false;

		$sql = "
			SELECT
				ms.MedService_id,
				ms.MedService_WialonURL,
				ms.MedService_WialonPort
			FROM
				v_MedService ms WITH(nolock)
				INNER JOIN v_MedServiceType mst WITH(nolock) ON(mst.MedServiceType_id=ms.MedServiceType_id)
			WHERE
				ms.MedService_id=:MedService_id
				-- 3 - диагностика
				AND mst.MedServiceType_Code=3
		";

		return $this->db->query($sql, array('MedService_id' => $MedService_id))->row_array();
	}

	/**
	 * Сохранение ресурса
	 */
	public function saveResource($data) {
		try {
			$this->beginTransaction();

			if ( !empty($data['Resource_id']) ) {
				$action = 'upd';

				/*if ( empty($data['Resource_Name']) && empty($data['ResourceType_id']) && empty($data['Resource_begDT']) && empty($data['Resource_endDT']) ) {
					throw new Exception('Отсутствуют входные данные');
				}*/

				$resourceData = $this->getFirstRowFromQuery("
					-- @file " . __FILE__ . "
					-- @line " . __LINE__ . "

					select
						Resource_Name,
						ResourceType_id, 
						MedService_id,
						convert(varchar(10), Resource_begDT, 120) as Resource_begDT,
						convert(varchar(10), Resource_endDT, 120) as Resource_endDT
					from v_Resource with (nolock)
					where Resource_id = :Resource_id
				", $data);

				if ( $resourceData === false || !is_array($resourceData) || count($resourceData) == 0 ) {
					throw new Exception('Ошибка при получении данных ресурса из БД');
				}

				$data['MedService_id'] = $resourceData['MedService_id'];
			}
			else {
				$action = 'ins';
				$data['Resource_id'] = null;
			}

			$response = $this->getFirstRowFromQuery("
				-- @file " . __FILE__ . "
				-- @line " . __LINE__ . "

				declare
					@Error_Code bigint,
					@Error_Message varchar(4000),
					@Res bigint = :Resource_id;

				exec p_Resource_{$action}
					@Resource_id = @Res output, 
					@Resource_Name = :Resource_Name,
					@ResourceType_id = :ResourceType_id, 
					@MedService_id = :MedService_id,
					@Resource_begDT = :Resource_begDT,
					@Resource_endDT = :Resource_endDT,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @Res as Resource_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			", $data);

			if ( $response === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД');
			}

			$data['Resource_id'] = $response['Resource_id'];

			// сохранение связей с мед. изделиями
			$rsp = $this->_saveMedProductCardResourceGrid($data);

			if ( !empty($rsp) ) {
				throw new Exception($rsp);
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$response = array(array('Error_Msg' => $e->getMessage()));
			$this->rollbackTransaction();
		}

		return $response;
	}

	/**
	 * Сохранение таблицы связи ресурса с медицинским изделием
	 */
	protected function _saveMedProductCardResourceGrid($data) {
		$MedProductCardResourceData = json_decode($data['MedProductCardResourceData'], true);

		if ( $data['ResourceType_id'] == 3 && count($MedProductCardResourceData) == 0 ) {
			return 'Массив карточек мед. изделий обязателен для заполнения';
		}

		$rsp = $this->queryResult("
			-- @file " . __FILE__ . "
			-- @line " . __LINE__ . "

			select
				MedProductCardResource_id
			from
				passport.v_MedProductCardResource with (nolock)
			where
				Resource_id = :Resource_id
		", $data);

		foreach ( $rsp as $row ) {
			$resp = $this->_deleteMedProductCardResource($row);

			if ( !empty($resp) ) {
				return 'Ошибка при удалении медицинского изделия';
			}
		}

		if ( $data['ResourceType_id'] != 3 ) {
			return '';
		}

		foreach ( $MedProductCardResourceData as $MedProductCardResource ) {
			$MedProductCardResource['Resource_id'] = $data['Resource_id'];
			$MedProductCardResource['pmUser_id'] = $data['pmUser_id'];
			$MedProductCardResource['MedProductCardResource_begDT'] = (!empty($MedProductCardResource['begDT']) ? $MedProductCardResource['begDT'] : null);
			$MedProductCardResource['MedProductCardResource_endDT'] = (!empty($MedProductCardResource['endDT']) ? $MedProductCardResource['endDT'] : null);

			$resp = $this->_saveMedProductCardResource($MedProductCardResource);

			if ( !empty($resp) ) {
				return 'Ошибка при сохранении медицинского изделия';
			}
		}

		return '';
	}

	/**
	 * Сохранение связи ресурса с медицинским изделием
	 */
	protected function _saveMedProductCardResource($data) {
		$check = $this->_checkMedProductCardResource($data);

		if ( !empty($check) ) {
			return $check;
		}

		$params = array(
			'MedProductCardResource_id' => (!empty($data['MedProductCardResource_id']) && $data['MedProductCardResource_id'] > 0 ? $data['MedProductCardResource_id'] : null),
			'MedProductCard_id' => $data['MedProductCard_id'],
			'Resource_id' => $data['Resource_id'],
			'MedProductCardResource_begDT' => $data['MedProductCardResource_begDT'],
			'MedProductCardResource_endDT' => empty($data['MedProductCardResource_endDT']) ? NULL : $data['MedProductCardResource_endDT'],
			'pmUser_id' => $data['pmUser_id'],
		);

		if ( !empty($params['MedProductCardResource_id'])  ) {
			$action = 'upd';
		}
		else {
			$procedure = 'ins';
		}

		$rsp = $this->getFirstRowFromQuery("
			-- @file " . __FILE__ . "
			-- @line " . __LINE__ . "

			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@MedProductCardResource_id bigint = :MedProductCardResource_id;

			exec passport.p_MedProductCardResource_{$action}
				@MedProductCardResource_id = @MedProductCardResource_id output,
				@MedProductCard_id = :MedProductCard_id,
				@Resource_id = :Resource_id,
				@MedProductCardResource_begDT = :MedProductCardResource_begDT,
				@MedProductCardResource_endDT = :MedProductCardResource_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @MedProductCardResource_id as MedProductCardResource_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", $params);

		if ( $rsp === false || !is_array($rsp) || count($rsp) == 0 ) {
			return 'Ошибка при сохранении связи ресурса с медицинским изделием';
		}
		else if ( !empty($rsp['Error_Msg']) ) {
			return $rsp['Error_Msg'];
		}

		return '';
	}

	/**
	 * Удаление связи ресурса с медицинским изделием
	 */
	protected function _deleteMedProductCardResource($data) {
		$rsp = $this->getFirstRowFromQuery("
			-- @file " . __FILE__ . "
			-- @line " . __LINE__ . "

			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);

			exec passport.p_MedProductCardResource_del
				@MedProductCardResource_id = :MedProductCardResource_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output

			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		", $data);

		if ( $rsp === false ) {
			return 'Ошибка при удалении связи ресурса с медицинским изделием';
		}
		else if ( !empty($rsp['Error_Msg']) ) {
			return $rsp['Error_Msg'];
		}

		return '';
	}

	/**
	 * Проверка дублирования связи ресурса с медицинским изделием
	 */
	protected function _checkMedProductCardResource($data) {
		$params = array(
			'Resource_id' => $data['Resource_id'],
			'MedProductCardResource_id' => $data['MedProductCardResource_id'],
			'MedProductCardResource_begDT' => $data['MedProductCardResource_begDT'],
			'MedProductCardResource_endDT' => empty($data['MedProductCardResource_endDT']) ? NULL : $data['MedProductCardResource_endDT']
		);

		$rsp = $this->getFirstResultFromQuery("
			-- @file " . __FILE__ . "
			-- @line " . __LINE__ . "

			select top 1
				MedProductCardResource_id
			from
				passport.v_MedProductCardResource with (nolock)
			where
				Resource_id = :Resource_id
				and MedProductCardResource_id != ISNULL(:MedProductCardResource_id, 0)
				and (
					(MedProductCardResource_begDT <= :MedProductCardResource_begDT AND (MedProductCardResource_endDT > :MedProductCardResource_endDT OR MedProductCardResource_endDT IS NULL))
					OR (:MedProductCardResource_begDT BETWEEN MedProductCardResource_begDT AND MedProductCardResource_endDT)
					OR (MedProductCardResource_begDT > :MedProductCardResource_begDT AND :MedProductCardResource_endDT is null)
				)
		", $params, true);

		if ( $rsp === false ) {
			return 'Не удалось проверить пересечение медицинских изделий';
		}
		else if ( !empty($rsp) ) {
			return 'В один период времени Ресурс может быть связан только с одним медицинским изделием';
		}

		return '';
	}
}