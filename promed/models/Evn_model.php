<?php
/**
 * Evn_model - модель для работы с таблицей Evn и таблицами на основе Evn
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2009-2012 Swan Ltd.
 * @author			Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			2012-11-28
 *
 * @property Parodontogram_model $Parodontogram_model
 * @property PersonToothCard_model $PersonToothCard_model
 * @property EvnAbstract_model $evnObject
 */
class Evn_model extends swModel {
	private $TimetableGrafArr = array();

	/**
	 * Обновление статуса события
	 *
	 * @return boolean Результат сохранения
	 */
	function updateEvnStatus($data) {
		$sql = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_Evn_setStatus
				@Evn_id = :Evn_id,
				@EvnStatus_id = :EvnStatus_id,
				@EvnStatus_SysNick = :EvnStatus_SysNick,
				@EvnClass_id = :EvnClass_id,
				@EvnClass_SysNick = :EvnClass_SysNick,
				@EvnStatusCause_id = :EvnStatusCause_id,
				@EvnStatusHistory_Cause = :EvnStatusHistory_Cause,
				@MedServiceMedPersonal_id = :MedServiceMedPersonal_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$query = $this->queryResult($sql,array(
			'Evn_id' => $data['Evn_id'],
			'EvnStatus_id' => !empty($data['EvnStatus_id']) ? $data['EvnStatus_id'] : null,
			'EvnStatus_SysNick' => !empty($data['EvnStatus_SysNick']) ? $data['EvnStatus_SysNick'] : null,
			'EvnClass_id' => !empty($data['EvnClass_id']) ? $data['EvnClass_id'] : null,
			'EvnClass_SysNick' => !empty($data['EvnClass_SysNick']) ? $data['EvnClass_SysNick'] : null,
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id']) ? $data['EvnStatusCause_id'] : null,
			'EvnStatusHistory_Cause' => !empty($data['EvnStatusHistory_Cause']) ? $data['EvnStatusHistory_Cause'] : null,
			'MedServiceMedPersonal_id' => !empty($data['MedServiceMedPersonal_id']) ? $data['MedServiceMedPersonal_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		));

		//#44150 : Предшествующий код код не работал при возвращении ошибки!
		if (!$this->isSuccessful($query)) {
			return false;
		} else {
			if (
				!empty($data['EvnClass_SysNick'])
				&& $data['EvnClass_SysNick'] == 'EvnPrescrVK'
			) {
				// надо оповестить врача, который создал направление на ВК
				$this->load->model('Mse_model');
				$this->Mse_model->notifyEvnPrescrVKStatusChange([
					'EvnPrescrVK_id' => $data['Evn_id'],
					'EvnStatus_SysNick' => $data['EvnStatus_SysNick'],
					'EvnStatusHistory_Cause' => $data['EvnStatusHistory_Cause'] ?? null,
					'pmUser_id' => $data['pmUser_id']
				]);
			}
			return true;
		}
	}

    /**
     * Проверка направления
     */
	function checkEvnDirection($evnClass, $evnId = null) {
		$query = "
			select EvnDirection_id
			from v_" . $evnClass . " with (nolock)
			where " . $evnClass . "_id = :Evn_id
		";

		$result = $this->db->query($query, array('Evn_id' => $evnId));

		if ( !is_object($result) ) {
			return false;
		}

		$checkResult = false;
		$response = $result->result('array');

		foreach ( $response as $row ) {
			if ( !empty($row['EvnDirection_id']) ) {
				$checkResult = true;
				break;
			}
		}

		return array('evnDirectionIsNotEmpty' => $checkResult);
	}

	/**
	 * Проверка направлений
	 */
	function checkNonAutoEvnDirections($evnDirectionList = array()) {
		$checkResult = false;

		if ( count($evnDirectionList) > 0 ) {
			$query = "
				select top 1 EvnDirection_id
				from v_EvnDirection with (nolock)
				where EvnDirection_id in (" . implode(",", $evnDirectionList) . ")
			";
			$result = $this->db->query($query);

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnDirection_id']) ) {
				$checkResult = true;
			}
		}

		return array('evnDirectionIsNotAuto' => $checkResult);
	}

    /**
     * Проверка
     */
	function checkEvnStickListOnUsage($evnStickList) {
		$query = "
			select EvnLink_id
			from v_EvnLink with (nolock)
			where Evn_id in (" . implode(', ', $evnStickList) . ")
			union all
			select EvnLink_id
			from v_EvnLink with (nolock)
			where Evn_lid in (" . implode(', ', $evnStickList) . ")
		";

		$result = $this->db->query($query);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array('allow' => true);
		}
		else {
			return array('allow' => false);
		}
	}

    /**
     * Проверка
     */
	function checkEvnStickListOnVK($evnStickList) {
		$query = "
			select top 1 EvnVK_id
			from v_EvnVK with (nolock)
			where EvnStickWorkRelease_id in (
				select EvnStickWorkRelease_id
				from v_EvnStickWorkRelease with (nolock)
				where EvnStickBase_id in (" . implode(', ', $evnStickList) . ")
			)
		";

		$result = $this->db->query($query);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array('allow' => true);
		}
		else {
			return array('allow' => false);
		}
	}

	/**
	 * Проверка, что это единственное посещение в АПЛ
	 */
	function checkOnOnlyOneExist($data) {

		if($data['isStom']) {
			$from = 'v_EvnVizitPLStom';
			$prefix = 'EvnVizitPLStom';
		} else {
			$from = 'v_EvnVizitPL';
			$prefix = 'EvnVizitPL';
		}

		$query = "
			SELECT COUNT(*) as count,
				evpl.{$prefix}_pid AS EvnVizitPL_pid
			FROM {$from} evpl WITH (nolock)
			outer apply (
					select {$prefix}_pid AS Evn_pid
					FROM {$from} WITH (nolock)
					WHERE {$prefix}_id = :EvnVizitPL_id
			) epu
			where EVPL.{$prefix}_pid = epu.Evn_pid
			GROUP BY evpl.{$prefix}_pid
		";

		$result = $this->db->query($query, array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	// https://redmine.swan.perm.ru/issues/19513
	// Добавлен параметр для простого удаления события (без дополнительных проверок), т.к. валилась смена пациента в учетном документе
    /**
     * Удаление события
     */
	function deleteEvn($data, $simple = false) {
		if ($simple === false
			&& in_array($data['EvnClass_SysNick'],array('EvnPS', 'EvnSection','EvnVizitPL','EvnVizitPLStom','EvnPL','EvnPLStom'))
		) {
			// Удаление связанных событиый
			if ( $this->deleteEvnLink($data) === false ) {
				return array(array('Error_Msg' => 'Ошибка при удалении связанных событий'));
			}

			$params = array(
				'session' => $data['session'],
				'isExecCommonChecksOnDelete' => true, // уже выполнены проверки в контроллере Evn::doCommonChecksOnDelete
				'isAllowIgnoreDoc' => $data['ignoreDoc'], // TODO перенести проверки из $this->onBeforeDelete
				'ignoreEvnDrug' => !empty($data['ignoreEvnDrug']) ? $data['ignoreEvnDrug'] : null,
				'ignoreCheckEvnUslugaChange' => !empty($data['ignoreCheckEvnUslugaChange']) ? $data['ignoreCheckEvnUslugaChange'] : null
			);
			$params[$data['EvnClass_SysNick'] . '_id'] = $data['Evn_id'];
			if (!empty($data['MedStaffFact_id'])) {
				// удаление из АРМа врача
				$params['user_MedStaffFact_id'] = $data['MedStaffFact_id'];
			}
			switch ($data['EvnClass_SysNick']) {
				case 'EvnPS':
				case 'EvnSection': // аналогично
				case 'EvnVizitPL': // аналогично
				case 'EvnVizitPLStom': // аналогично
				case 'EvnPL':// аналогично
				case 'EvnPLStom':// аналогично
					$this->load->model($data['EvnClass_SysNick'] . '_model', 'evnObject');
					// в контроллере начата транзакция в Evn::deleteFromArm или в Evn::deleteEvn
					$isAllowTransaction = false;
					$response = array($this->evnObject->doDelete($params, $isAllowTransaction));
					$this->load->model('PersonPregnancy_model');
					$this->PersonPregnancy_model->checkAndSaveQuarantine([
						'Person_id' => $this->evnObject->Person_id,
						'pmUser_id' => $this->promedUserId
					]);
					break;
				default:
					$response = array(array('Error_Msg' => 'Ошибочный вход в тупиковую ветку (удаление события)'));
					break;
			}
			return $response;
		}

		if (in_array($data['EvnClass_SysNick'], array('EvnDiagPLStom'))) {
			$this->load->model('EvnDiagPLStom_model');
			return $this->EvnDiagPLStom_model->deleteEvnDiagPLStom(array(
				'EvnDiagPLStom_id' => $data['Evn_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_" . $data['EvnClass_SysNick'] . "_del
				@" . $data['EvnClass_SysNick'] . "_id = :id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

        $id = $data[$data['EvnClass_SysNick'] . '_id'];
        if (isset($data['Evn_id']) && $data['Evn_id']){
            $id = $data['Evn_id'];
        }

		$result = $this->db->query($query, array(
			'id' => $id,
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление события)'));
		}

	}

	/**
	 *	Удаление связанных событиый
	 */
	function deleteEvnLink($data) {

		$query = "
			select EvnLink_id
			from v_EvnLink with (nolock)
			where Evn_id = :Evn_id or Evn_lid = :Evn_id
		";

		$result = $this->db->query($query, array('Evn_id' => $data['Evn_id']));

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return true;
		}

		foreach ($response as $id) {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_EvnLink_del
					@EvnLink_id = :EvnLink_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$result = $this->db->query($query, array('EvnLink_id' => $id['EvnLink_id']));

		}

		return true;
	}

	/**
	 *	Получение данных по событию
	 */
	function getEvnData($data, $fieldsList) {
		$query = "
			select " . implode(', ', $fieldsList) . "
			from v_" . $data['EvnClass_SysNick'] . (in_array($data['EvnClass_SysNick'], array('EvnDirection')) ? "_all" : "") . " with (nolock)
			where " . $data['EvnClass_SysNick'] . "_id = :Evn_id
		";

		$queryParams = array(
			'Evn_id' => $data['Evn_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		return $response[0];
	}

	/**
	 *	Получение количества потомков события
	 */
	function getChildEvnCount($data) {

		$params = array('Evn_id' => $data['Evn_id']);

		$innerFilter = ""; $filter = "";
		if (!empty($data['parentEvnClass_SysNick'])) {
			$innerFilter .= " and parent.EvnClass_SysNick = :parentEvnClass_SysNick";
			$params['parentEvnClass_SysNick'] = $data['parentEvnClass_SysNick'];
		}

		if (!empty($data['childEvnClass_SysNick'])) {
			$filter .= " and child.EvnClass_SysNick = :childEvnClass_SysNick";
			$params['childEvnClass_SysNick'] = $data['childEvnClass_SysNick'];
		}

		$query = "
			select top 1
				count(child.Evn_id) as evnCount,
				child.Evn_pid
			from v_Evn child (nolock)
			where child.Evn_pid =
			(
				select
					evn.Evn_pid
				from v_Evn evn (nolock)
				inner join v_Evn parent (nolock) on parent.Evn_id = evn.Evn_pid
				where
					evn.Evn_id = :Evn_id
					{$innerFilter}

			)
			{$filter}
			group by child.Evn_pid
		";

		//echo '<pre>',print_r(getDebugSQL($query, $params)),'</pre>'; die();
		$resp = $this->queryResult($query, $params);

		if (!empty($resp[0])) $resp = $resp[0];
		else $resp = array();

		return $resp;
	}

	/**
	 *	Получение родительского события
	 */
	function getParentEvn($data) {
		$query = "
			select
				ep.EvnClass_SysNick,
				ep.Evn_id,
				'' as Error_Msg
			from
				v_Evn e (nolock)
				inner join v_Evn ep (nolock) on ep.Evn_id = e.Evn_rid
			where
				e.Evn_id = :Evn_id
		";

		$resp = $this->queryResult($query, array(
			'Evn_id' => $data['Evn_id']
		));

		if (!empty($resp[0]['Evn_id'])) {
			return $resp[0];
		}

		return false;
	}


	/**
	 *	Формирование запроса для сохранения события
	 */
	function getEvnSaveQuery($evnClass = '', $evnData = array()) {
		// Получаем список параметров хранимой процедуры
		$response = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :" . $evnClass . "_id;

			exec p_" . $evnClass . "_" . (!empty($evnData[$evnClass . '_id']) ? "upd" : "ins") . "
				@" . $evnClass . "_id = @Res output,
		";

		foreach ( array_keys($evnData) as $fieldName ) {
			if ( $fieldName != $evnClass . "_id" ) {
				$response .= "@" . $fieldName . " = :" . $fieldName . ",";
			}
		}

		$response .= "
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as " . $evnClass . "_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $response;
	}


	/**
	 *	Формирование дерева связанных событий для Evn_id
	 */
	function getEvnTree($treeData, $pid = null) {
		if ( !isset($treeData) || !is_array($treeData) || count($treeData) == 0 ) {
			return false;
		}

		$response = array();

		foreach ( $treeData as $array ) {
			if ( $array['Evn_pid'] == $pid ) {
				$response[] = array(
					 'Evn_id' => $array['Evn_id']
					,'Evn_pid' => $array['Evn_pid']
					,'Evn_rid' => $array['Evn_rid']
					,'Evn_setDT' => $array['Evn_setDT']
					,'Evn_IsSigned' => $array['Evn_IsSigned']
					,'EvnClass_SysNick' => $array['EvnClass_SysNick']
					,'Person_id' => $array['Person_id']
					,'children' => array()
				);
			}
		}

		foreach ( $response as $key => $array ) {
			$response[$key]['children'] = $this->getEvnTree($treeData, $array['Evn_id']);
		}

		return $response;
	}

	/**
	 *	Получение списка связанных событий для Evn_id
	 */
	function getRelatedEvnList($data) {
		$response_lis = array();
		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response_lis = $this->lis->GET('Evn/RelatedEvnList', $data, 'list');
			if (!$this->isSuccessful($response_lis)) {
				return false;
			}
		}
		$response_ms = $this->_getRelatedEvnList($data);
		if (!is_array($response_ms)) {
			return false;
		}

		$response = array_unique(array_merge($response_ms, $response_lis), SORT_REGULAR);
		return $response;
	}

	/**
	 *	Получение списка связанных событий для Evn_id
	 */
	function _getRelatedEvnList($data) {
		$query = "
			select
				 e.Evn_id
				,e.Evn_pid
				,e.Evn_rid
				,convert(varchar(19), e.Evn_setDT, 120) as Evn_setDT
				,e.EvnClass_SysNick
				,isnull(e.Evn_IsSigned, 1) as Evn_IsSigned
				,e.Person_id
				,e.Lpu_id
			from v_Evn e with (nolock)
			where
				e.Evn_rid = :Evn_id
				or e.Evn_pid = :Evn_id
				or e.Evn_pid in (select Evn_id from v_Evn with (nolock) where Evn_pid = :Evn_id)
				or e.Evn_id = :Evn_id
				or e.Evn_id in (select Evn_lid from v_EvnLink with (nolock) where Evn_id = :Evn_id)
		";

		$queryParams = array(
			'Evn_id' => $data['Evn_id']
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
	 *	Получение списка параметров хранимой процедуры
	 */
	function getStoredProcedureParamsList($sp, $schema = null) {
		$query = "
			select [name]
			from sys.all_parameters with(nolock)
			where [object_id] = (select top 1 [object_id] from sys.objects with(nolock) where [type_desc] = 'SQL_STORED_PROCEDURE' and [name] = :name)
				and [name] not in ('@pmUser_id', '@Error_Code', '@Error_Message', '@isReloadCount')
		";

		$queryParams = array(
			'name' => $sp
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$outputData = array();
		$response = $result->result('array');

		foreach ( $response as $row ) {
			// Исключаем параметры DocumentUc_cid и DocumentUcStr_cid для p_EvnDrug_ins
			if (mb_strtolower($sp) == 'p_evndrug_ins' && in_array(mb_strtolower($row['name']), array('documentuc_cid', 'documentucstr_cid'))) {
				continue;
			}
			// Исключаем параметр *_IsInReg, т.к. его убрали из хранимых процедур
			if (preg_match("/_IsInReg/u", $row['name'])) {
				continue;
			}

			$outputData[] = str_replace('@', '', $row['name']);
		}

		return $outputData;
	}


	/**
	 *	Сохранение события и связанных с ним событий
	 */
	function setAnotherPersonForDocument($data, $evnTree, &$evnLink = array()) {
		if ( !isset($data) || !is_array($data) || count($data) == 0 || !isset($evnTree) || !is_array($evnTree) || count($evnTree) == 0 ) {
			return false;
		}

		foreach ( $evnTree as $key => $evn ) {
			$spParamsList = $this->getStoredProcedureParamsList('p_' . $evn['EvnClass_SysNick'] . '_ins');

			if ( !is_array($spParamsList) || count($spParamsList) == 0 ) {
				return array(array('Error_Msg' => 'Ошибка при получении списка параметров хранимой процедуры'));
			}

			$evnData = $this->getEvnData($evn, $spParamsList);

			// Услуга посещения должна игнорироваться, т.к. добавляется в хранимых процедурах p_EvnVizitPL_set и p_EvnSection_set
			// @task https://redmine.swan.perm.ru/issues/59328
			if ( 'EvnUslugaCommon' == $evn['EvnClass_SysNick'] && array_key_exists('EvnUslugaCommon_IsVizitCode', $evnData) && 2 == $evnData['EvnUslugaCommon_IsVizitCode'] ) {
				continue;
			}

			$evnData['pmUser_id'] = $data['pmUser_id'];

			// Переопределяем PersonEvn_id и Server_id для всех событий, кроме ЛВН или ЛВН, которые выписаны на того же человека,
			// который указан в ЛВН
			if ( !preg_match("/EvnStick/", $evn['EvnClass_SysNick']) || $evn['Person_id'] == $data['Person_oid'] ) {
				$evnData['PersonEvn_id'] = $data['PersonEvn_id'];
				$evnData['Server_id'] = $data['Server_id'];
			}

			// Зануляем некоторые поля
			$nullFieldsList = array(
				 'Morbus_id'
				,$evn['EvnClass_SysNick'] . '_id'
				,$evn['EvnClass_SysNick'] . '_rid'
				,$evn['EvnClass_SysNick'] . '_insDT'
				,$evn['EvnClass_SysNick'] . '_updDT'
			);

			foreach ( $nullFieldsList as $fieldName ) {
				if ( !empty($evnData[$fieldName]) ) {
					$evnData[$fieldName] = null;
				}
			}

			// Указываем родительское событие
			$evnData[$evn['EvnClass_SysNick'] . '_pid'] = $evn['Evn_pid'];

			// ... а для ЛВН еще и mid
			if ( preg_match("/EvnStick/", $evn['EvnClass_SysNick']) ) {
				$evnData[$evn['EvnClass_SysNick'] . '_mid'] = $evn['Evn_rid'];

				// Если ЛВН является продолжением и предыдущий ЛВН был уже обработан...
				if ( !empty($evnData[$evn['EvnClass_SysNick'] . '_prid']) && !empty($evnLink[$evnData[$evn['EvnClass_SysNick'] . '_prid']]) ) {
					// ... меняем идентификатор предыдущего ЛВН
					$evnData[$evn['EvnClass_SysNick'] . '_prid'] = $evnLink[$evnData[$evn['EvnClass_SysNick'] . '_prid']];
				}
			}

			// карты переносятся отдельно #105492
			/*if (!empty($evnData['CmpCallCard_id'])) {
				// переносим связанную карту СМП
				$this->load->model('Common_model');
				$resp_cc = $this->Common_model->setAnotherPersonForDocument(array(
					'CmpCallCard_id' => $evnData['CmpCallCard_id'],
					'Person_id' => $data['Person_id'],
					'pmUser_id' => $data['pmUser_id'],
					'no_trans' => true // без транзакции, т.к. транзакция уже начата.
				));

				if (!empty($resp_cc['Error_Msg'])) {
					return array(array('Error_Msg' => $resp_cc['Error_Msg']));
				}
			}*/

			// Сохраняем событие
			$response = $this->saveEvn($evn['EvnClass_SysNick'], $evnData);

			if ( !is_array($response) || count($response) == 0 ) {
				return array(array('Error_Msg' => 'Ошибка при сохранении события'));
			}

			// В случае движения нужно пересчитать КСГ
			if ( "EvnSection" == $evn['EvnClass_SysNick'] ) {
				$this->load->model('EvnSection_model');
				$this->EvnSection_model->recalcKSGKPGKOEF($response[0][$evn['EvnClass_SysNick'] . '_id'], $data['session']);
			}

			// В случае ЛВН вручную пересохраняем записи из EvnStickCarePerson и EvnStickWorkRelease
			if ( preg_match("/EvnStick/", $evn['EvnClass_SysNick']) ) {
				// Получить списки для $evnTree[$key]['Evn_id']
				// Сохранить для $response[0][$evn['EvnClass_SysNick'] . '_id']

				$evnStickCarePersonList = $this->getEvnStickCarePersonList($evnTree[$key]['Evn_id']);

				if ( $evnStickCarePersonList === false ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение списка лиц, нуждающихся в уходе)'));
				}

				$evnStickWorkReleaseList = $this->getEvnStickWorkReleaseList($evnTree[$key]['Evn_id']);

				if ( $evnStickWorkReleaseList === false ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение списка освобождений от работы)'));
				}

				foreach ( $evnStickCarePersonList as $record ) {
					$responseTmp = $this->saveEvnStickCarePerson(array(
						'Evn_id' => $response[0][$evn['EvnClass_SysNick'] . '_id'],
						'Person_id' => ($record['Person_id'] == $data['Person_oid'] ? $data['Person_id'] : $record['Person_id']),
						'RelatedLinkType_id' => $record['RelatedLinkType_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					if ( !is_array($responseTmp) || count($responseTmp) == 0 ) {
						return array(array('Error_Msg' => 'Ошибка при сохранении записи о нуждающемся в уходе'));
					}
					else if ( !empty($responseTmp[0]['Error_Msg']) ) {
						return $responseTmp;
					}
				}

				foreach ( $evnStickWorkReleaseList as $record ) {
					$responseTmp = $this->saveEvnStickWorkRelease(array(
						'EvnStickBase_id' => $response[0][$evn['EvnClass_SysNick'] . '_id'],
						'EvnStickWorkRelease_begDT' => $record['EvnStickWorkRelease_begDT'],
						'EvnStickWorkRelease_endDT' => $record['EvnStickWorkRelease_endDT'],
						'MedPersonal_id' => $record['MedPersonal_id'],
						'MedPersonal2_id' => $record['MedPersonal2_id'],
						'MedPersonal3_id' => $record['MedPersonal3_id'],
						'LpuSection_id' => $record['LpuSection_id'],
						'Post_id' => $record['Post_id'],
						'EvnStickWorkRelease_IsPredVK' => $record['EvnStickWorkRelease_IsPredVK'],
						'pmUser_id' => $data['pmUser_id']
					));

					if ( !is_array($responseTmp) || count($responseTmp) == 0 ) {
						return array(array('Error_Msg' => 'Ошибка при сохранении записи об освобождении от работы'));
					}
					else if ( !empty($responseTmp[0]['Error_Msg']) ) {
						return $responseTmp;
					}
				}
			}

			// Добавляем связь "старый ID -> новый ID"
			$evnLink[$evnTree[$key]['Evn_id']] = $response[0][$evn['EvnClass_SysNick'] . '_id'];

			$evnTree[$key]['Evn_id'] = $response[0][$evn['EvnClass_SysNick'] . '_id'];

			// переносим TimetableGraf, если он есть
			$this->load->model('TimetableGraf_model');
			$this->TimetableGraf_model->onSetAnotherPersonForDocument(array(
				'Evn_id' => $evnTree[$key]['Evn_id'],
				'Evn_oldid' => $evn['Evn_id'],
				'Person_id' => $data['Person_id']
			));

			// переносим TimetableStac, если он есть
			$this->load->model('TimetableStac_model');
			$this->TimetableStac_model->onSetAnotherPersonForDocument(array(
				'Evn_id' => $evnTree[$key]['Evn_id'],
				'Evn_oldid' => $evn['Evn_id'],
				'Person_id' => $data['Person_id']
			));

			// Сохраняем дочерние события
			if ( is_array($evn['children']) && count($evn['children']) > 0 ) {
				// пробиваем для всех children новый pid и rid
				foreach ( $evn['children'] as $k => $childEvn ) {
					$evn['children'][$k]['Evn_pid'] = $evnTree[$key]['Evn_id'];

					if ( in_array($evn['EvnClass_SysNick'], array('EvnPL', 'EvnPLStom', 'EvnPS') ) ) {
						$evn['children'][$k]['Evn_rid'] = $evnTree[$key]['Evn_id'];
					}
					else {
						$evn['children'][$k]['Evn_rid'] = $evnTree[$key]['Evn_rid'];
					}
				}

				// Вызываем сохранение дочерних событий
				$this->setAnotherPersonForDocument($data, $evn['children'], $evnLink);
			}
		}

		return array(array('Error_Code' => '', 'Error_Msg' => '', 'Evn_id' => $evnTree[0]['Evn_id']));
	}


	/**
	 *	Сохранение события
	 */
	function saveEvn($evnClass, $evnData) {
		// Получаем строку запроса и массив с параметрами запроса
		$query = $this->getEvnSaveQuery($evnClass, $evnData);

		// Конвертируем даты в строки
		foreach ( $evnData as $key => $value ) {
			if ( $value instanceof DateTime ) {
				$evnData[$key] = $value->format('Y-m-d H:i:s');
			}
		}

		// Сохраняем событие
		$result = $this->db->query($query, $evnData);
		// echo "<div>", getDebugSQL($query, $evnData), "</div>";

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Получение списка
     */
	function getEvnStickCarePersonList($Evn_id) {
		$query = "
			select
				 Person_id
				,RelatedLinkType_id
			from v_EvnStickCarePerson with (nolock)
			where Evn_id = :Evn_id
		";
		$result = $this->db->query($query, array('Evn_id' => $Evn_id));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Получение списка
     */
	function getEvnStickWorkReleaseList($Evn_id) {
		$query = "
			select
				 convert(varchar(20), EvnStickWorkRelease_begDT, 120) as EvnStickWorkRelease_begDT
				,convert(varchar(20), EvnStickWorkRelease_endDT, 120) as EvnStickWorkRelease_endDT
				,MedPersonal_id
				,MedPersonal2_id
				,MedPersonal3_id
				,LpuSection_id
				,EvnStickWorkRelease_IsPredVK
				,Post_id
			from v_EvnStickWorkRelease with (nolock)
			where EvnStickBase_id = :Evn_id
		";
		$result = $this->db->query($query, array('Evn_id' => $Evn_id));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Сохранение
     */
	function saveEvnStickCarePerson($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnStickCarePerson_ins
				@Evn_id = :Evn_id,
				@Person_id = :Person_id,
				@RelatedLinkType_id = :RelatedLinkType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Evn_id' => $data['Evn_id'],
			'Person_id' => $data['Person_id'],
			'RelatedLinkType_id' => $data['RelatedLinkType_id'],
			'pmUser_id' => $data['pmUser_id']
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
     * Сохранение
     */
	function saveEvnStickWorkRelease($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnStickWorkRelease_ins
				@EvnStickBase_id = :EvnStickBase_id,
				@EvnStickWorkRelease_begDT = :EvnStickWorkRelease_begDT,
				@EvnStickWorkRelease_endDT = :EvnStickWorkRelease_endDT,
				@MedPersonal_id = :MedPersonal_id,
				@MedPersonal2_id = :MedPersonal2_id,
				@MedPersonal3_id = :MedPersonal3_id,
				@EvnStickWorkRelease_IsPredVK = :EvnStickWorkRelease_IsPredVK,
				@LpuSection_id = :LpuSection_id,
				@Post_id = :Post_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'EvnStickWorkRelease_begDT' => $data['EvnStickWorkRelease_begDT'],
			'EvnStickWorkRelease_endDT' => $data['EvnStickWorkRelease_endDT'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedPersonal2_id' => $data['MedPersonal2_id'],
			'MedPersonal3_id' => $data['MedPersonal3_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'EvnStickWorkRelease_IsPredVK' => $data['EvnStickWorkRelease_IsPredVK'],
			'Post_id' => $data['Post_id'],
			'pmUser_id' => $data['pmUser_id']
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
     * Удаление события из АРМ
     */
	function deleteFromArm($data) {

        // Проверяем что отделение выбранного рабочего места врача соответствует отделению, указанному в движении/посещении в учетном документе

        //Тащим актуальные места работы из v_MedStaffFact по MedStaffFact_id
        /*$query = "
            select
                LpuSection_id
            from
                v_MedStaffFact with (nolock)
            where
                MedStaffFact_id = :MedStaffFact_id
                and WorkData_begDate <= dbo.tzGetDate()
                and (WorkData_endDate > dbo.tzGetDate() or WorkData_endDate  is null)
        ";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            $cur_lpusections = $result->result('array');

            if (is_array($cur_lpusections) && count($cur_lpusections) > 0 && !empty($cur_lpusections[0]['LpuSection_id'])) {

                $LpuSections_MedWorks = $cur_lpusections[0]['LpuSection_id'];

            } else {
                return array(array('Error_Code' => 21,'Error_Msg' => 'В данном АРМе отсутствует место работы. Удаление невозможно.'));
            }
        } else {
            return array(array('Error_Code' => 22,'Error_Msg'=>'Ошибка при получении данных о месте работы врача'));
        }

        //Получаем отделения, которые фигурируют в объекте удаления
        $query = "
            select
                Evn_id,
                EvnClass_SysNick
            from
                v_Evn with (nolock)
            where
                (Evn_rid = :Evn_id  or Evn_pid = :Evn_id)
                and Evn_id != :Evn_id
                and Lpu_id = :Lpu_id
        ";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            $response = $result->result('array');

            if (is_array($response) && count($response) > 0 && !empty($response[0]['Evn_id'])) {

                if (!empty($response[0]['Evn_id']) && !empty($response[0]['EvnClass_SysNick']) && false === stripos($response[0]['EvnClass_SysNick'], 'EvnDiag')) {
	                if (false === stripos($response[0]['EvnClass_SysNick'], 'EvnUsluga')) {
		                $LpuSection_id = 'LpuSection_id';
	                } else {
		                $LpuSection_id = 'LpuSection_uid';
	                }
                    $query = "
                        select
                            {$LpuSection_id} as LpuSection_id
                        from
                            v_{$response[0]['EvnClass_SysNick']} with (nolock)
                        where
                            {$response[0]['EvnClass_SysNick']}_id = {$response[0]['Evn_id']}
                    ";
                }

                $result = $this->db->query($query, $data);
                if ( is_object($result) ) {
                    $response = $result->result('array');

                    if (is_array($response) && count($response) > 0 && !empty($response[0]['LpuSection_id'])) {

                        if ($LpuSections_MedWorks != $response[0]['LpuSection_id']) {
                            return array(array('Error_Code' => 30,'Error_Msg'=>'Посещение в случае не соответствуют месту работы текущего врача. Удаление невозможно.'));
                        }
                    }
                } else {
                    return array(array('Error_Code' => 40,'Error_Msg'=>'Ошибка при получении идентификаторов отделений.'));
                }
            }
        } else {
            return array(array('Error_Code' => 50,'Error_Msg'=>'Ошибка при получении данных по отделениям в удаляемом объекте.'));
        }*/
		/*
		 * Эти проверки уже не нужны #18053 note-42
		// Получаем класс удаляемого случая
		$data['EvnClass_SysNick'] = $this->getEvnClassSysNick($data['Evn_id']);
		if ( empty($data['EvnClass_SysNick']) ) {
			return array(array('Error_Msg' => 'Ошибка при получении класса удаляемого случая', 'Error_Code' => '', ));
		}

		if ( in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPS', 'EvnPLStom')) ) {
			$fieldsList = array('pmUser_insID','Lpu_id');
			// Получаем специфические данные документа
			switch ($data['EvnClass_SysNick']) {
				case 'EvnPL':
					$fieldsList[] = 'isnull(EvnPL_IsFinish, 1) as Evn_IsFinish';
					break;
				case 'EvnPS':
					$fieldsList[] = 'case when EvnPS_disDT is null then 1 else 2 end as Evn_IsFinish';
					$fieldsList[] = 'ISNULL(EvnDirection_id,0) as EvnDirection_id';
					break;
				case 'EvnPLStom':
					$fieldsList[] = 'isnull(EvnPLStom_IsFinish, 1) as Evn_IsFinish';
					break;
			}
			$evnData = $this->getEvnData($data, $fieldsList);
			if ( empty($evnData) ) {
				return array(array('Error_Msg' => 'Ошибка при получении данных удаляемого случая', 'Error_Code' => '', ));
			}
			// проверяем МО - эта проверка есть в общих #18053
			if ($data['Lpu_id'] != $evnData['Lpu_id']) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Вы не можете удалить документ, который заведен в другой МО'));
			}
			// проверяем, что удаляет тот же пользователь, который создал - это не нужно #18053 note-42
			if ($data['pmUser_id'] != $evnData['pmUser_insID']) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Вы не можете удалить документ, который добавлен другим пользователем'));
			}

			// проверяем, что случай не закончен
			if (1 != $evnData['Evn_IsFinish']) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Вы не можете удалить случай, который закончен'));
			}
			// из АРМа нельзя удалять КВС, которые созданы по направлению (из поиска можно)
			if ( 'EvnPS' == $data['EvnClass_SysNick'] && 0 < $evnData['EvnDirection_id'] ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Вы не можете удалить КВС, пациент принят по направлению'));
			}
		}
		*/
		// общие проверки и удаление
		return $this->deleteEvn($data);
	}

	/**
	 * Содержит ли случай единственное движение/посещение данного врача
	 * Вызывается из onBeforeDelete
	 * @return mixed
	 * #18053 note-42
	 */
	private function isDisableDeleteOrEditEvnByMpOrMsf($data, $action = 'delete') {
		$where_id = 'pid';
		switch ($data['EvnClass_SysNick']) {
			case 'EvnPL':
				$evnclass_sysnick = 'EvnVizitPL';
				$select = "count(*) as cnt";
				//$select = "count(*) as cnt, {$evnclass_sysnick}.{$evnclass_sysnick}_Count";
				break;
			case 'EvnPS':
				$evnclass_sysnick = 'EvnSection';
                $select = "count(*) as cnt";
                //$select = "count(*) as cnt, {$evnclass_sysnick}.{$evnclass_sysnick}_Count";
				break;
			case 'EvnPLStom':
				$evnclass_sysnick = 'EvnVizitPLStom';
				$select = "count(*) as cnt";
				//$select = "count(*) as cnt, {$evnclass_sysnick}.{$evnclass_sysnick}_Count";
				break;
			case 'EvnVizitPL':
			case 'EvnSection':
			case 'EvnVizitPLStom':
				$where_id = 'id';
				$evnclass_sysnick = $data['EvnClass_SysNick'];
				$select = "case when {$evnclass_sysnick}.{$evnclass_sysnick}_Index+1={$evnclass_sysnick}.{$evnclass_sysnick}_Count then 2 else 1 end as isLast";
				break;
			default:
				return false;
		}

		$params = array('Evn_id' => $data['Evn_id']);

		if ($action == 'edit' && in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPLStom', 'EvnPS'))
			&& $data['session']['isMedStatUser'] == false
			&& !empty($data['session']['medpersonal_id'])
		) {
			// $this->load->helper('MedStaffFactLink');
			// не стал использовать getMedPersonalListWithLinks, т.к. там берется из $_SESSION
			$med_personal_list = array();
			$med_personal_list[] = $data['session']['medpersonal_id'];
			if (!empty($data['session']['MedStaffFactLinks'])) {
				foreach($data['session']['MedStaffFactLinks'] as $item) {
					$med_personal_list[] = $item['MedPersonal_id'];
				}
			}
			$tmp = $this->getFirstResultFromQuery("
				select count(*) as cnt
				from v_{$evnclass_sysnick} {$evnclass_sysnick} with (nolock)
				where
					{$evnclass_sysnick}.{$evnclass_sysnick}_{$where_id} = :Evn_id
					and {$evnclass_sysnick}.LpuSection_id is not null
					and exists (
						select top 1 MedStaffFact_id from v_MedStaffFact with (nolock)
						where MedPersonal_id in (".implode(',',$med_personal_list).")
						and LpuSection_id = {$evnclass_sysnick}.LpuSection_id
						and WorkData_begDate <= {$evnclass_sysnick}.{$evnclass_sysnick}_setDate
						and (WorkData_endDate is null or WorkData_endDate >= {$evnclass_sysnick}.{$evnclass_sysnick}_setDate)
					)
			", array('Evn_id' => $data['Evn_id']));
			if (empty($tmp)) {
				return 'Случай не содержит движения/посещения, которые относятся к отделению врача.';
			}
			return false;
		}

		switch (true) {
			case ($action == 'edit' && $data['session']['isMedStatUser'] == false && !empty($data['session']['medpersonal_id']) && in_array($data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom'))):
				// $this->load->helper('MedStaffFactLink');
				// не стал использовать getMedPersonalListWithLinks, т.к. там берется из $_SESSION
				$med_personal_list = array();
				$med_personal_list[] = $data['session']['medpersonal_id'];
				if (!empty($data['session']['MedStaffFactLinks'])) {
					foreach($data['session']['MedStaffFactLinks'] as $item) {
						$med_personal_list[] = $item['MedPersonal_id'];
					}
				}
				$query = "
					select {$select}
					from v_{$evnclass_sysnick} {$evnclass_sysnick} with (nolock)
					where
						{$evnclass_sysnick}.{$evnclass_sysnick}_{$where_id} = :Evn_id
						and {$evnclass_sysnick}.LpuSection_id is not null
						and not exists (
							select top 1 MedStaffFact_id from v_MedStaffFact with (nolock)
							where MedPersonal_id in (".implode(',',$med_personal_list).")
							and LpuSection_id = {$evnclass_sysnick}.LpuSection_id
							and WorkData_begDate <= {$evnclass_sysnick}.{$evnclass_sysnick}_setDate
							and (WorkData_endDate is null or WorkData_endDate >= {$evnclass_sysnick}.{$evnclass_sysnick}_setDate)
						)
				";
			break;
			case (!empty($data['MedStaffFact_id']) && !empty($data['LpuSections_MedWorks'])):
				$query = "
					select {$select}
					from v_{$evnclass_sysnick} {$evnclass_sysnick} with (nolock)
					left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :MedStaffFact_id
					where
						{$evnclass_sysnick}.{$evnclass_sysnick}_{$where_id} = :Evn_id
						and ( ({$evnclass_sysnick}.LpuSection_id not in ({$data['LpuSections_MedWorks']}) and {$evnclass_sysnick}.LpuSection_id is not null)" . ($action == 'delete' ? " or ({$evnclass_sysnick}.MedPersonal_id != MSF.MedPersonal_id and {$evnclass_sysnick}.MedPersonal_id is not null )" : "") . ")
						and ({$evnclass_sysnick}.{$evnclass_sysnick}_index + 1 = {$evnclass_sysnick}.{$evnclass_sysnick}_Count and {$evnclass_sysnick}.MedPersonal_id != MSF.MedPersonal_id)
				";
				$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			break;
			case (!empty($data['session']['medpersonal_id'])):
				$query = "
					select {$select}
					from v_{$evnclass_sysnick} {$evnclass_sysnick} with (nolock)
					where
						{$evnclass_sysnick}.{$evnclass_sysnick}_{$where_id} = :Evn_id
						and {$evnclass_sysnick}.MedPersonal_id != :MedPersonal_id
				";
				$params['MedPersonal_id'] = $data['session']['medpersonal_id'];
			break;
			default:
				return false;
			break;
		}

		//echo getDebugSQL($query, $params); exit();
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			return 'Ошибка запроса к БД';
		}
		$resp = $result->result('array');
        //http://redmine.swan.perm.ru/issues/24872
		/*if (count($resp) == 0) {
			return 'Не найдены посещения/движения созданные текущим врачом';
		}*/

		// для Екб автоматически создаётся движение в приёмном, которое не отображается, поменял проверку на Count > 2 для екб
		/*$evnMaxCount = 1;
		if ($data['session']['region']['nick'] == 'ekb' && $evnclass_sysnick == 'EvnSection') {
			$evnMaxCount = 2;
		}

		if (isset($resp[0][$evnclass_sysnick.'_Count']) && $resp[0][$evnclass_sysnick.'_Count'] > $evnMaxCount) {
			return 'Случай содержит более одного посещения/движения';
		}*/

		if (isset($resp[0]['cnt']) && $resp[0]['cnt'] >= 1) {
			if ($action == 'delete') {
				return 'Случай содержит движения/посещения, которые относятся к другим отделению и/или врачу.';
			} else {
				return 'Случай содержит движения/посещения, которые относятся к другому отделению.';
			}
		}

		if ( isset($resp[0]['isLast']) ) {
			if ( $resp[0]['isLast'] != 2 && in_array($data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom')) ) {
				return 'Посещение/движение не является последним в случае';
			}
			else if ( in_array($data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnSection', 'EvnVizitPLStom')) ) {
				return 'Посещение/движение относится к другим отделению и/или врачу';
			}
		}

		// Добавляем проверку на наличие в удаляемом ТАП/КВС посещений/движений с другими врачами
		// https://redmine.swan.perm.ru/issues/34510
		// https://redmine.swan.perm.ru/issues/18053
		// В АРМ врача можно удалять законченный случай при соблюдении всех остальных условий:
		// б) документ не имеет в рамках одного случая несколько движений/посещений, хотя бы одно из которых относится к другому отделению и/или врачу;
		if ( in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPS', 'EvnPLStom')) ) {
			$query = "";

			if ( !empty($data['MedStaffFact_id']) && !empty($data['LpuSections_MedWorks']) ) {
				$query = "
					select
						count({$evnclass_sysnick}.{$evnclass_sysnick}_id) as cnt
					from
						v_{$evnclass_sysnick} {$evnclass_sysnick} with (nolock)
						left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :MedStaffFact_id
					where
						{$evnclass_sysnick}.{$evnclass_sysnick}_pid = :Evn_id
						and (
							{$evnclass_sysnick}.LpuSection_id not in ({$data['LpuSections_MedWorks']})
							" . ($action == 'delete' ? "or {$evnclass_sysnick}.MedPersonal_id != MSF.MedPersonal_id" : "") . "
						)
				";
				$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			}
			else if ( !empty($data['session']['medpersonal_id']) ) {
				$query = "
					select
						count({$evnclass_sysnick}.{$evnclass_sysnick}_id) as cnt
					from
						v_{$evnclass_sysnick} {$evnclass_sysnick} with (nolock)
					where
						{$evnclass_sysnick}.{$evnclass_sysnick}_{$where_id} = :Evn_id
						and {$evnclass_sysnick}.MedPersonal_id != :MedPersonal_id
				";
			}

			if ( !empty($query) ) {
				$result = $this->db->query($query, $params);

				if ( !is_object($result) ) {
					return 'Ошибка запроса к БД';
				}

				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					return 'Ошибка запроса к БД';
				}
				else if ( !empty($resp[0]['cnt']) ) {
					return 'Удаляемый случай содержит посещения/движения, которые относятся к другим отделению и/или врачу';
				}
			}
		}

		return false;
	}

	/**
	 *	Получение класса случая по Evn_id
	 * @return mixed
	 */
	function getEvnClassSysNick($Evn_id) {
		$query = '
			select EvnClass_SysNick
			from v_Evn with (nolock)
			where Evn_id = :Evn_id
		';
		$result = $this->db->query($query, array('Evn_id' => $Evn_id));

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		return $response[0]['EvnClass_SysNick'];
	}

	/**
	 * Специфическая логика перед удалением учетного документа определенного класса
	 * Вызывается из deleteEvn
	 * @return mixed
	 * Для отмены удаления надо вернуть массив с ошибкой
	 */
	public function onBeforeDelete(&$data, $evnTreeData) {
		$result = true;

		if ( $data['session']['isMedStatUser'] === false && isSuperadmin() === false ) {
			// #18053 note-42
			// через поиск пользователь с привязкой к врачу может удалять:
			// - случай, если движение/посещение данного врача в случае единственное
			// - движение или посещение данного врача, если оно последнее в случае
			// - пользователь работает в арм мед статистика
			// при удалении из АРМа то же самое, только с контролем - отделение удаляемого события должно совпадать с местом работы из АРМ

			// Является ли это движение/посещение последним в случае и создано ли оно данным врачом
			if (
				in_array($data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom', 'EvnSection'))
				&& (!empty($data['MedStaffFact_id']) || !empty($data['session']['medpersonal_id']))
			) {
				$msg = $this->isDisableDeleteOrEditEvnByMpOrMsf($data);
				if ( $msg ) {
					return array(array('Error_Code' => 1,'Error_Msg'=>'Вы не можете удалить документ по следующей причине: '.$msg ));
				}
			}

			// Содержит ли случай единственное движение/посещение данного врача
			if (
				in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPS', 'EvnPLStom'))
				&& (!empty($data['MedStaffFact_id']) || !empty($data['session']['medpersonal_id']))
			) {
				$msg = $this->isDisableDeleteOrEditEvnByMpOrMsf($data);
				if ( $msg ) {
					return array(array('Error_Code' => 1,'Error_Msg'=>'Вы не можете удалить документ по следующей причине: '.$msg ));
				}
			}

			//пользователь не имеет привязки к врачу или имеет привязку к врачу, место работы которого соответствует отделению, указанному в движении/посещении в учетном документе или пользователь работает в АРМ мед. статистика
			if (!empty($data['session']['medpersonal_id'])) {

				//Тащим актуальные места работы из v_MedStaffFact по MedPersonal_id
				$query = "
					select distinct
						LpuSection_id
					from
						v_MedStaffFact with (nolock)
					where
						MedPersonal_id = :medpersonal_id
						and WorkData_begDate <= dbo.tzGetDate()
						and (WorkData_endDate > dbo.tzGetDate() or WorkData_endDate  is null)
						and LpuSection_id is not null
				";

				$result = $this->db->query($query, $data['session']);
				if ( is_object($result) ) {
					$cur_lpusections = $result->result('array');

					if (is_array($cur_lpusections) && count($cur_lpusections) > 0 && !empty($cur_lpusections[0]['LpuSection_id'])) {
						$LpuSections_MedWorks = array();
						foreach ($cur_lpusections as $key_med_work => $val_med_work) {

							//Собираем отделения работы в массив
							array_push($LpuSections_MedWorks, $val_med_work['LpuSection_id']);
						}
					} else {
						return array(array('Error_Code' => 21,'Error_Msg' => 'У выбранного врача в данный момент отсутствуют места работы. Удаление невозможно.'));
					}
				} else {
					return array(array('Error_Code' => 22,'Error_Msg'=>'Ошибка при получении данных по местам работы врача'));
				}

				//Получаем отделения, которые фигурируют в объекте удаления
				$query = "
					select
						Evn_id,
						EvnClass_SysNick
					from
						v_Evn with (nolock)
					where
						(Evn_rid = :Evn_id  or Evn_pid = :Evn_id)
						and Evn_id != :Evn_id
						and Lpu_id = :Lpu_id
				";

				$result = $this->db->query($query, $data);
				if ( is_object($result) ) {
					$response = $result->result('array');

					if (is_array($response) && count($response) > 0 && !empty($response[0]['Evn_id'])) {

						if (
							!empty($response[0]['Evn_id'])
							&& !empty($response[0]['EvnClass_SysNick'])
							&& (
								in_array($response[0]['EvnClass_SysNick'], array('EvnSection'))
								//|| preg_match("/EvnUsluga/", $response[0]['EvnClass_SysNick'])
								|| preg_match("/EvnVizit/", $response[0]['EvnClass_SysNick'])
							)
						) {
							$EvnClass_SysNick = $response[0]['EvnClass_SysNick'];
							/*if ( preg_match("/EvnUsluga/", $EvnClass_SysNick) ) {
								$LpuSectionField = 'LpuSection_uid';
							} else {
								$LpuSectionField = 'LpuSection_id';
							}*/
							$LpuSectionField = 'LpuSection_id';

							// https://redmine.swan.perm.ru/issues/69078
							// Добавил выборку дополнительной информации
							$param1 = "null";

							if ( in_array($EvnClass_SysNick, array('EvnSection')) ) {
								$param1 = "case when {$EvnClass_SysNick}_IsPriem = 2 then 2 else null end";
							}

							$query = "
								select
									{$LpuSectionField} as LpuSection_id,
									{$param1} as param1
								from
									v_{$EvnClass_SysNick} with (nolock)
								where
									{$EvnClass_SysNick}_id = {$response[0]['Evn_id']}
							";
						}

						$result = $this->db->query($query, $data);
						if ( is_object($result) ) {
							$response = $result->result('array');

							if (is_array($response) && count($response) > 0 && !empty($response[0]['LpuSection_id'])) {
								$LpuSection_id = $response[0]['LpuSection_id'];

								if (!in_array($LpuSection_id, $LpuSections_MedWorks)) {
									if (!empty($response[0]['param1'])) {
										// Дополнительно проверяем:
										// 1) доступ пользователя к приемному отделению (https://redmine.swan.perm.ru/issues/69078)
										if ( $response[0]['param1'] == 2 && $EvnClass_SysNick == 'EvnSection' ) {
											$this->load->model('User_model', 'usermodel');
											$userMedStaffFactList = $this->usermodel->getUserMedStaffFactList(array(
												'Lpu_id' => $data['Lpu_id'],
												'MedService_id' => 0,
												'MedStaffFact_id' => 0,
												'MedPersonal_id' => $data['session']['medpersonal_id'],
												'StacPriemOnly' => 2,
												'pmUser_id' => $data['pmUser_id'],
												'session' => $data['session']
											));

											$checkResult = false;

											if ( is_array($userMedStaffFactList) && count($userMedStaffFactList) > 0 ) {
												foreach ( $userMedStaffFactList as $record )  {
													if ( $record['LpuSection_id'] == $LpuSection_id ) {
														$checkResult = true;
													}
												}
											}

											if ( $checkResult === false ) {
												return array(array('Error_Code' => 30,'Error_Msg'=>'Одно или несколько посещений в случае не соответствуют месту работы текущего врача. Удаление невозможно.'));
											}
										}
									}
									else {
										return array(array('Error_Code' => 30,'Error_Msg'=>'Одно или несколько посещений в случае не соответствуют месту работы текущего врача. Удаление невозможно.'));
									}
								}
							}
						} else {
							return array(array('Error_Code' => 40,'Error_Msg'=>'Ошибка при получении идентификаторов отделений.'));
						}
					}
				} else {
					return array(array('Error_Code' => 50,'Error_Msg'=>'Ошибка при получении данных по отделениям в удаляемом объекте.'));
				}
			}
		}

		if (!$data['ignoreDoc'] && in_array($data['EvnClass_SysNick'], array('EvnPL','EvnPLStom','EvnVizitPL','EvnVizitPLStom','EvnPS','EvnSection'))) {
			$query = "
				select sum(doc.cnt) as cnt
				from (
					select top 1 count(*) as cnt
					from v_EvnXml with(nolock)
					where Evn_id = :Evn_id
				 union
					select top 1 count(*) as cnt
					from v_EvnMediaData with(nolock)
					where Evn_id = :Evn_id
				) doc
			";

			$response = $this->queryResult($query, $data);
			if (!$this->isSuccessful($response)) {
				return array(array('Error_Code' => 60,'Error_Msg'=>'Ошибка при получении количества документов, прикрепленным к случаю лечения.'));
			}
			if ($response[0]['cnt'] > 0) {
				return array(array('Alert_Code' => 701,'Alert_Msg'=>'Случай лечения содержит документы, созданные врачом. Продолжить удаление?'));
			}
		}

		if ($data['EvnClass_SysNick'] == 'EvnPL') {
			$evndirection_id = $this->getFirstResultFromQuery("select top 1 EvnDirection_id from v_EvnPL (nolock) where EvnPL_id = :Evn_id", array(
				'Evn_id' => $data['Evn_id']
			));
			if (!empty($evndirection_id)) {
				$this->load->model('EvnDirectionAll_model');
				$this->EvnDirectionAll_model->rollbackStatus(array(
					'Evn_id' => $evndirection_id,
					'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
					'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		//echo '<pre>',print_r($result),'</pre>'; die();
		return $result;
	}

	/**
	 *	Проверка на доступность редактирования события
	 */
	public function CommonChecksForEdit(&$data, $evnTreeData) {
		$result = true;

		//пользователь не имеет привязки к врачу или имеет привязку к врачу, место работы которого соответствует отделению, указанному в движении/посещении в учетном документе или пользователь работает в АРМ мед. статистика;
		//документ не имеет в рамках одного случая несколько движений/посещений, хотя бы одно из которых относится к другому отделению  («своё» движение/посещение можно редактировать, если оно является последним);

		$filter = "(1=0)";
		if (!empty($data['session']['medpersonal_id'])) {
			$filter = " MedPersonal_id = :medpersonal_id";
			$data['medpersonal_id'] = $data['session']['medpersonal_id'];
		} else if (!empty($data['MedStaffFact_id'])) {
			$filter = " MedStaffFact_id = :MedStaffFact_id";
		}

		//пользователь не имеет привязки к врачу или имеет привязку к врачу, место работы которого соответствует отделению, указанному в движении/посещении в учетном документе или пользователь работает в АРМ мед. статистика
		if (
			(
				(!empty($data['MedStaffFact_id']) || !empty($data['medpersonal_id']))
				&& $data['session']['isMedStatUser'] === false
			)
		) {
			//Тянем актуальные места работы из v_MedStaffFact по MedPersonal_id
			$query = "
				select distinct
					LpuSection_id
				from
					v_MedStaffFact with (nolock)
				where
					{$filter}
					and WorkData_begDate <= dbo.tzGetDate()
					and (WorkData_endDate > dbo.tzGetDate() or WorkData_endDate  is null)
					and LpuSection_id is not null
			";

			//echo getDebugSQL($query, $data);die;
			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$cur_lpusections = $result->result('array');

				if (is_array($cur_lpusections) && count($cur_lpusections) > 0 && !empty($cur_lpusections[0]['LpuSection_id'])) {
					$LpuSections_MedWorks = '';
					foreach ($cur_lpusections as $key_med_work => $val_med_work) {

						//Собираем отделения работы в строку
						$LpuSections_MedWorks .= $val_med_work['LpuSection_id'] . ', ';
					}

					$LpuSections_MedWorks = substr($LpuSections_MedWorks, 0, strlen($LpuSections_MedWorks) - 2);
					$data['LpuSections_MedWorks'] = $LpuSections_MedWorks;
				} else {
					return array(array('Error_Code' => 21,'Error_Msg' => 'У выбранного врача в данный момент отсутствуют места работы. Редактирование невозможно.'));
				}
			} else {
				return array(array('Error_Code' => 22,'Error_Msg'=>'Ошибка при получении данных по местам работы врача'));
			}
		}

		//пользователь не имеет привязки к врачу или имеет привязку к врачу, место работы которого соответствует отделению, указанному в движении/посещении в учетном документе или пользователь работает в АРМ мед. статистика
		/*if (
			(
				(!empty($data['session']['medpersonal_id']) || !empty($data['MedStaffFact_id']))
				&& $data['session']['isMedStatUser'] === false
			)
			//|| $data['from'] == 'workplace' // хз, что это за условие
		) {

			// Является ли это движение/посещение последним в случае, создано ли оно данным врачом, cодержит ли случай единственное движение/посещение данного врача
			if (
				in_array($data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom', 'EvnSection', 'EvnPL', 'EvnPS', 'EvnPLStom'))
				&& (!empty($data['MedStaffFact_id']) || !empty($data['session']['medpersonal_id']))
			) {
				$msg = $this->isDisableDeleteOrEditEvnByMpOrMsf($data, 'edit');
				$msg = str_replace('Удаляемый','Редактируемый',$msg);
				if ( $msg ) {
					return array(array('Error_Code' => 1,'Error_Msg'=>'Вы не можете редактировать документ по следующей причине: '.$msg ));
				}
			}*/

			//Получаем отделения, которые фигурируют в объекте удаления
			/*$query = "
				select
					Evn_id,
					EvnClass_SysNick
				from
					v_Evn with (nolock)
				where
					(Evn_rid = :Evn_id  or Evn_pid = :Evn_id)
					and Evn_id != :Evn_id
					and Lpu_id = :Lpu_id
			";

			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$response = $result->result('array');

				if (is_array($response) && count($response) > 0 && !empty($response[0]['Evn_id'])) {

					if (!empty($response[0]['Evn_id']) && !empty($response[0]['EvnClass_SysNick'])) {

						$query = "
							select
								LpuSection_id
							from
								v_{$response[0]['EvnClass_SysNick']} with (nolock)
							where
								{$response[0]['EvnClass_SysNick']}_id = {$response[0]['Evn_id']}
						";
					}

					$result = $this->db->query($query, $data);
					if ( is_object($result) ) {
						$response = $result->result('array');

						if (is_array($response) && count($response) > 0 && !empty($response[0]['LpuSection_id'])) {

							if (!in_array($response[0]['LpuSection_id'], $LpuSections_MedWorks)) {
								return array(array('Error_Code' => 30,'Error_Msg'=>'Одно или несколько посещений в случае не соответствуют месту работы текущего врача. Редактирование невозможно.'));
							}
						}
					} else {
						return array(array('Error_Code' => 40,'Error_Msg'=>'Ошибка при получении идентификаторов отделений.'));
					}
				}
			} else {
				return array(array('Error_Code' => 50,'Error_Msg'=>'Ошибка при получении данных по отделениям в удаляемом объекте.'));
			}*/
		//}

		return true;
	}

	/**
	 * Получения настроек для журнала событий
	 */
	function getEvnJournalSettings() {
		$this->load->helper('Options');
		return getEvnNoticeOptions();
	}

	/**
	 * Получение списка
	 */
	function getAllowedEvnClassListForNotice() {
		$params = array();
		$settings = $this->getEvnJournalSettings();
		$allowed_evn_class_str = "'".implode("','",$settings['allowed_evn_class_arr'])."'";

		$query = "
			select
				EC.EvnClass_id,
				EC.EvnClass_Code,
				EC.EvnClass_SysNick,
				EC.EvnClass_Name
			from v_EvnClass EC with(nolock)
			where
				EC.EvnClass_SysNick in ({$allowed_evn_class_str})
			order by
				EC.EvnClass_Name
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Получение дат статусов события
	 */
	function getEvnStatusValues($data) {
		$Object = $data['EvnClass_SysNick'];
		$params = array('Evn_id' => $data['Evn_id']);

		$settings = $this->getEvnJournalSettings();
		$allowed_evn_status_list = array_keys($settings['full_evn_status_list'][$Object]);
		$fields_arr = array();
		foreach($allowed_evn_status_list as $status) {
			$field = $Object.'_'.$status.'DT';
			$fields_arr[] = "convert(varchar(10), {$field}, 104)+' '+convert(varchar(5), {$field}, 108) as [{$status}]";
		}
		$fields_str = implode(",", $fields_arr);

		$query = "
			select {$fields_str}
			from v_{$Object} with(nolock)
			where {$Object}_id = :Evn_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Получение даннх для отображения в журнале событий
	 */
	function getEvnJournalData($data) {
		$response = array();
		$where = '';

		$params = array(
			'Person_id' => $data['Person_id'],
			'start' => $data['start'],
			'limit' => $data['limit']
		);

		$allow_encryp = allowPersonEncrypHIV()?'1':'0';

		$query = "
			select top 1
				P.Person_id,
				case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then rtrim(PEH.PersonEncrypHIV_Encryp)
				else (isnull(rtrim(P.Person_SurName),'')
					+ case when P.Person_FirName is null then '' else ' '+rtrim(P.Person_FirName) end
					+ case when P.Person_SecName is null then '' else ' '+rtrim(P.Person_SecName) end
				) end as Person_Fio,
				convert(varchar(10), Person_BirthDay, 104) as Person_BirthDay,
				case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end PersonEncrypHIV_Encryp
			from v_PersonState P with(nolock)
			left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = P.Person_id
			where P.Person_id = :Person_id
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}
		$res_arr = $result->result('array');
		if (!is_array($res_arr) || count($res_arr) == 0) {
			return false;
		}
		$response = $res_arr[0];
		unset($res_arr);

		$settings = $this->getEvnJournalSettings();

		$allowed_evn_class_arr = $settings['allowed_evn_class_arr'];
		$allowed_evn_class_str = "'".implode("','",$allowed_evn_class_arr)."'";

		// Базовые статусы из базового класса событий Evn
		$base_status_list = $settings['base_status_list'];

		//Специфичные статусы по событиям и подписи к базовым статусам
		$specific_evn_status_list = $settings['specific_evn_status_list'];

		$query_arr = array();
		$archive_database_enable = $this->config->item('archive_database_enable');
		// Формирует запросы на выбор изменений базовых статусов
		foreach($base_status_list as $EvnStatus_Nick => $EvnStatus_Name) {
			$Evn_DT = 'Evn_'.$EvnStatus_Nick.'DT';
			$status_name_field = "'".$EvnStatus_Name."'";
			$status_name_field_arr = array();

			$allowed_evn_class_arr = array();
			foreach($settings['allowed_evn_class_arr'] as $evn_class) {
				if (isset($settings['full_evn_status_list'][$evn_class][$EvnStatus_Nick])) {
					$allowed_evn_class_arr[] = $evn_class;
				}
			}
			$allowed_evn_class_str = "'".implode("','",$allowed_evn_class_arr)."'";

			// Определение специфичных наименований базовых статусов по событиям
			foreach($specific_evn_status_list as $EvnClass_SysNick => $specific) {
				if (isset($specific[$EvnStatus_Nick]) && !empty($specific[$EvnStatus_Nick])) {
					$status_name_field_arr[] = "when t.EvnClass_SysNick = '{$EvnClass_SysNick}' then '{$specific[$EvnStatus_Nick]}'";
				}
			}
			if (count($status_name_field_arr) > 0) {
				$status_name_field = "case ".implode(' ', $status_name_field_arr)." else '{$EvnStatus_Name}' end";
			}
			$archquery='';
			if (!empty($archive_database_enable)) {
				$archquery .= "
					, case when ISNULL(t.Evn_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
				";
			}

			$query_arr[] = "
				select
					t.Evn_id,
					t.EvnClass_Name,
					t.EvnClass_SysNick,
					t.Evn_rid,
					t.Person_id,
					t.PersonEvn_id,
					t.Server_id,
					convert(varchar(10),t.{$Evn_DT},104)+' '+convert(varchar(5),t.{$Evn_DT},108) as Evn_DT,
					t.{$Evn_DT} as Evn_orderDT,
					{$status_name_field} as EvnStatus_Name,
					'{$EvnStatus_Nick}' as EvnStatus_Nick
					{$archquery}
				from v_Evn t with(nolock)
				where
					t.Person_id = :Person_id
					and t.EvnClass_SysNick in({$allowed_evn_class_str})
					and t.{$Evn_DT} is not null
					and not exists (
						select top 1 EvnUslugaPar_id
						from v_EvnUslugaPar with (nolock)
						where EvnUslugaPar_id = t.Evn_id
							and EvnLabSample_id is not null
					)
			";
		}

		$allowed_evn_class_arr = $settings['allowed_evn_class_arr'];
		// Формирует запросы на выбор специфичных статусов для событий
		foreach($specific_evn_status_list as $EvnClass_SysNick => $evn_status_list) {
			if (!in_array($EvnClass_SysNick, $allowed_evn_class_arr)) { continue; }
			$Object = $EvnClass_SysNick;
			foreach($evn_status_list as $EvnStatus_Nick => $EvnStatus_Name) {
				if (isset($base_status_list[$EvnStatus_Nick])) { continue; }
				$Evn_DT = $Object.'_'.$EvnStatus_Nick.'DT';
				$status_name_field = "'".$EvnStatus_Name."'";

				$archquery='';
				if (!empty($archive_database_enable)) {
					$archquery .= "
						, case when ISNULL(t.{$Object}_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
					";
				}


				$query_arr[] = "
					select
						t.{$Object}_id,
						t.EvnClass_Name,
						'{$EvnClass_SysNick}' as EvnClass_SysNick,
						t.{$Object}_rid,
						t.Person_id,
						t.PersonEvn_id,
						t.Server_id,
						convert(varchar(10),t.{$Evn_DT},104)+' '+convert(varchar(5),t.{$Evn_DT},108) as Evn_DT,
						t.{$Evn_DT} as Evn_orderDT,
						{$status_name_field} as EvnStatus_Name,
						'{$EvnStatus_Nick}' as EvnStatus_Nick
						{$archquery}
					from v_{$Object} t with(nolock)
					where
						t.Person_id = :Person_id
						and t.{$Evn_DT} is not null
				";
			}
		}
		$order='';
		$archquery='';
		if (!empty($archive_database_enable)) {
			$order='Evn.archiveRecord,';
			$archquery=',Evn.archiveRecord';
		}
		$union = implode(' union ', $query_arr);

		if(!empty($data['query'])) {
			$where.=" AND Evn.EvnClass_Name LIKE :query+'%' ";
			$params['query'] = $data['query'];
		}
		$query = "
			select
			-- select
				Evn.Evn_id,
				Evn.EvnClass_Name,
				Evn.EvnClass_SysNick,
				Evn.Evn_rid,
				rEvn.EvnClass_SysNick as EvnClass_rSysNick,
				Evn.Person_id,
				Evn.PersonEvn_id,
				Evn.Server_id,
				Evn.Evn_DT,
				Evn.EvnStatus_Name,
				Evn.EvnStatus_Nick
				{$archquery}
			-- end select
			from
			-- from
				({$union}) Evn
				left join v_Evn rEvn with(nolock) on rEvn.Evn_id = Evn.Evn_rid
				left join EvnDirection ED with(nolock) on ED.EvnDirection_id = Evn.Evn_id
			-- end from
			where
			-- where
				(ED.EvnDirection_id is null or ED.EvnDirection_IsAuto <> 2) {$where}
			-- end where
			order by
			-- order by
				{$order}Evn.Evn_orderDT desc
			-- end order by
		";

		//echo $query;exit;
		//echo getDebugSQL($query, $params);exit;
		//echo getDebugSQL(getLimitSQLPH($query, $params['start'], $params['limit']), $params);exit;

		$result = $this->db->query(getLimitSQLPH($query, $params['start'], $params['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (!is_object($result)) {
			return false;
		}
		$res_arr = $result->result('array');
		if (!is_array($res_arr)) {
			return false;
		}
		$response['evn'] = $res_arr;
		$response['totalCount'] = $count;
		unset($res_arr);

		// Формирования тела для каждого статуса событий
		foreach($response['evn'] as $key => $item) {
			$item['session'] = $data['session'];
			$evn_data = $this->getEvnBodyDataForJournal($item);
			if (is_array($evn_data) && in_array($item['EvnClass_SysNick'], array(
				'EvnSection','EvnOtherLpu','EvnOtherSection','EvnOtherStac','EvnDirection','EvnReanimatPeriod'
			))) {
				//если нет прав, то вернется false --> по следующему ветвлению событие удалится
				$evn_data = $this->checkLpuAccessRights($evn_data);
			}

			if (is_array($evn_data)) {
				//$item['Evn_BodyData'] = $evn_data;
				$evn_data = array_merge($evn_data, $item);
				$response['evn'][$key]['Evn_Header'] = $this->getEvnHeaderForJournal($evn_data);
				$response['evn'][$key]['Evn_Body'] = $this->getEvnBodyForJournal($evn_data);
			} else {
				// $response['evn'][$key]['Evn_Header'] = $this->getEvnHeaderForJournal($item);
				// $response['evn'][$key]['Evn_Body'] = 'Не найдена запись о событии!';
				unset($response['evn'][$key]); // не найдена запись о событии (вероятно нет доступа)
			}
		}

		return $response;
	}

	/**
	 * Проверка прав доступа к событиям по ЛПУ
	 */
	function checkLpuAccessRights($data)
	{
		$fields = array(
			'Lpu_id', 'OtherLpu_id', 'EvnOtherLpu_id'
		);
		$deniedLpus = getDeniedLpuOptions();

		if (empty($deniedLpus)) {
			return $data;
		} else {
			if(!is_array($data)) return false;
			foreach ($fields as $field) {
				if (key_exists($field, $data)) {
					if (isset($data[$field]) && in_array($data[$field], $deniedLpus)) {
						return false;
					}
				}
			}
		}
		return $data;
	}
	/**
	 * Получение заголовка события для журнала
	 */
	function getEvnHeaderForJournal($data) {
		$template = "";
		$header_text = "";

		switch ($data['EvnClass_SysNick']) {
			case 'EvnDirection':
				if ( !empty($data['DirType_Name']) ) {
					$data['DirType_Name'] = mb_strtolower($data['DirType_Name']);
					$template = "Направление {DirType_Name}";
				}
				else {
					$template = "{EvnClass_Name}";
				}
				break;

			default:
				$template = "{EvnClass_Name}";
		}

		$this->load->library('parser');
		$header_text = $this->parser->parse_string($template, $data, true);

		return $header_text;
	}

	/**
	 * Получение тела одного события для журнала
	 */
	function getEvnBodyForJournal($data) {
		$template = "";
		$body_text = "";

		switch ($data['EvnClass_SysNick']) {
			case 'EvnDoctor':
				$template = "{MPPerson_Fio} {EvnDoctor_setDate}";
				break;

			case 'EvnUslugaCommon':
				$template = "{UslugaComplex_Name} {EvnUslugaCommon_setDate}";
				break;

			case 'EvnUslugaOper':
				$template = "{UslugaComplex_Name} {EvnUslugaOper_setDate}";
				break;

			case 'EvnUslugaPar':
				$template = "{UslugaComplex_Name} {EvnUslugaPar_setDate}";
				break;

			case 'EvnPS':
				if (!empty($data['EvnPS_disDate'])) {
					$data['EvnPS_disDate'] = '- '.$data['EvnPS_disDate'];
				}
				$template = "{EvnPS_NumCard} {PrehospType_Name} {EvnPS_setDate} {EvnPS_disDate} {Diag_Code} {Diag_Name}";
				break;

			case 'EvnSection':
				if (!empty($data['EvnSection_disDate'])) {
					$data['EvnSection_disDate'] = '- '.$data['EvnSection_disDate'];
				}
				$template = "{Lpu_Nick} {LpuSection_Name} {EvnSection_setDate} {EvnSection_disDate} {Diag_Code} {Diag_Name}";
				break;

			case 'EvnDie':
				$template = "{EvnDie_setDate} {Diag_Code} {Diag_Name} {EvnDie_expDT}";
				break;

			case 'EvnLeave':
				$template = "{EvnLeave_setDate} {ResultDesease_Name} {LeaveCause_Name}";
				break;

			case 'EvnOtherLpu':
				$template = "{EvnOtherLpu_setDate} {ResultDesease_Name} {LeaveCause_Name} {OtherLpu_Nick}";
				break;

			case 'EvnOtherSection':
				$template = "{EvnOtherLpu_setDate} {ResultDesease_Name} {LeaveCause_Name} {OtherLpuSection_Name}";
				break;

			case 'EvnOtherStac':
				$template = "{EvnOtherStac_setDate} {ResultDesease_Name} {LeaveCause_Name} {OtherLpuUnitType_Name} {OtherLpuSection_Name}";
				break;

			case 'EvnDirection':
				if (empty($data['DirFailType_id'])) {
					$template = "№{EvnDirection_Num}, {Lpu_Nick}, {LpuSectionProfile_Code}. {LpuSectionProfile_Name}, {EvnDirection_setDateTime}";
				} else {
					$template = "№{EvnDirection_Num}, Причина: {DirFailType_Name}, отменено врачем {MedPersonalFail_Fio}, {LpuFail_Nick}";
				}
				break;

            case 'EvnReanimatPeriod':      //BOB - 10.04.2017
				if (!empty($data['EvnReanimatPeriod_disDate'])) {
					$data['EvnReanimatPeriod_disDate'] = ' - '.$data['EvnReanimatPeriod_disDate'];
				}
				$template = "{MedService_Name} {Lpu_Nick} {LpuSection_Name} {EvnReanimatPeriod_setDate} {EvnReanimatPeriod_disDate}";
				break;

			default:
				return '';
		}

		$this->load->library('parser');
		$body_text = $this->parser->parse_string($template, $data, true);

		return $body_text;
	}

	/**
	 * Получение данных для формирования тела события в журнале
	 */
	function getEvnBodyDataForJournal($data) {
		$params = array('Evn_id' => $data['Evn_id']);
		$query = "";

		switch ($data['EvnClass_SysNick']) {
			case 'EvnDoctor':
				$query = "
					select top 1
						ED.EvnDoctor_id,
						MP.MedPersonal_id,
						MP.Person_Fio as MPPerson_Fio,
						(convert(varchar(10),ED.EvnDoctor_setDate,104)) as EvnDoctor_setDate
					from
						v_EvnDoctor ED with(nolock)
						outer apply(
							select top 1 t.MedPersonal_id, t.Person_Fio
							from v_MedPersonal t with(nolock)
							where t.MedPersonal_id = ED.MedPersonal_id
								and t.Lpu_id = ED.Lpu_id
						) MP
					where
						ED.EvnDoctor_id = :Evn_id
				";
				break;

			case 'EvnUslugaCommon':
				$query = "
					select top 1
						EUC.EvnUslugaCommon_id,
						UC.UslugaComplex_id,
						UC.UslugaComplex_Name,
						convert(varchar(10),EUC.EvnUslugaCommon_setDate,104) as EvnUslugaCommon_setDate
					from
						v_EvnUslugaCommon EUC with(nolock)
						left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EUC.UslugaComplex_id
					where
						EUC.EvnUslugaCommon_id = :Evn_id
				";
				break;

			case 'EvnUslugaOper':
				$query = "
					select top 1
						EUO.EvnUslugaOper_id,
						UC.UslugaComplex_id,
						UC.UslugaComplex_Name,
						convert(varchar(10),EUO.EvnUslugaOper_setDate,104) as EvnUslugaOper_setDate
					from
						v_EvnUslugaOper EUO with(nolock)
						left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EUO.UslugaComplex_id
					where
						EUO.EvnUslugaOper_id = :Evn_id
				";
				break;

			case 'EvnUslugaPar':
				$join_msf = '';
				$filter = '';

				$filterAccessRights = getAccessRightsTestFilter('UslugaComplex.UslugaComplex_id');
				$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);

				$existEvnSection = "";
				if (!empty($data['session']['CurMedStaffFact_id'])) {
					$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
					$existEvnSection = "exists (
						select top 1 es.EvnSection_id from v_EvnSection es (nolock) where es.EvnSection_id = EvnUslugaPar.EvnUslugaPar_pid and es.EvnSection_setDT <= EvnUslugaPar.EvnUslugaPar_setDT and (es.EvnSection_disDT is null or es.EvnSection_disDT >= EvnUslugaPar.EvnUslugaPar_setDT) and es.MedStaffFact_id = :user_MedStaffFact_id
					) or ";
					$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
				}
				$filter .= " and ({$existEvnSection} (".((!empty($filterAccessRights))?$filterAccessRights."and UCp.UslugaComplex_id is null)":'1=1)');

				if (!empty($data['session']['CurMedStaffFact_id'])){
					$filter .= " or ED.MedPersonal_id = MSF.MedPersonal_id ";
				}

				$filter .= " )";

				$query = "
					select top 1
						EvnUslugaPar.EvnUslugaPar_id,
						UslugaComplex.UslugaComplex_id,
						UslugaComplex.UslugaComplex_Name,
						convert(varchar(10),EvnUslugaPar.EvnUslugaPar_setDate,104) as EvnUslugaPar_setDate
					from
						v_EvnUslugaPar EvnUslugaPar with(nolock)
						left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EvnUslugaPar.EvnDirection_id
						left join v_UslugaComplex UslugaComplex with(nolock) on UslugaComplex.UslugaComplex_id = EvnUslugaPar.UslugaComplex_id
						left join v_EvnLabSample ELS with (nolock) on ELS.EvnLabSample_id = EvnUslugaPar.EvnLabSample_id
						left join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = EvnUslugaPar.EvnDirection_id
						left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = ELS.MedService_id and UCMS.UslugaComplex_id = UslugaComplex.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
						outer apply (
							select top 1
								UCMPp.UslugaComplex_id
							from
								v_UslugaComplexMedService UCMPp (nolock)
							inner join v_EvnLabRequestUslugaComplex ELRUC on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = ELR.EvnLabRequest_id
							inner join v_EvnLabSample ELS2 (nolock) on ELS2.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS2.LabSampleStatus_id IN(4,6)
							where
								UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
								".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
						) as UCp
						{$join_msf}
					where
						EvnUslugaPar.EvnUslugaPar_id = :Evn_id
						and ED.EvnDirection_failDT is null
						{$filter}
				";
				break;

			case 'EvnPS':
				$query = "
					select top 1
						EPS.EvnPS_id,
						EPS.EvnPS_NumCard,
						D.Diag_id,
						D.Diag_Code,
						D.Diag_Name,
						PT.PrehospType_id,
						PT.PrehospType_Name,
						convert(varchar(10),EPS.EvnPS_setDate,104) as EvnPS_setDate,
						convert(varchar(10),EPS.EvnPS_disDate,104) as EvnPS_disDate
					from
						v_EvnPS EPS with(nolock)
						left join v_Diag D with(nolock) on D.Diag_id = EPS.Diag_id
						left join v_PrehospType PT with(nolock) on PT.PrehospType_id = EPS.PrehospType_id
					where
						EPS.EvnPS_id = :Evn_id
				";
				$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
				if ( !empty($diagFilter) ) {
					$query .= " and {$diagFilter}";
				}
				break;

			case 'EvnSection':
				$query = "
					select top 1
						ES.EvnSection_id,
						D.Diag_id,
						D.Diag_Code,
						D.Diag_Name,
						L.Lpu_id,
						L.Lpu_Nick,
						LS.LpuSection_id,
						LS.LpuSection_Name,
						convert(varchar(10),ES.EvnSection_setDate,104) as EvnSection_setDate,
						convert(varchar(10),ES.EvnSection_disDate,104) as EvnSection_disDate
					from
						v_EvnSection ES with(nolock)
						left join v_Diag D with(nolock) on D.Diag_id = ES.Diag_id
						left join v_Lpu L with(nolock) on L.Lpu_id = ES.Lpu_id
						left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
					where
						ES.EvnSection_id = :Evn_id
				";
				$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
				if ( !empty($diagFilter) ) {
					$query .= " and {$diagFilter}";
				}
				break;

			case 'EvnDie':
				$query = "
					select top 1
						ED.EvnDie_id,
						D.Diag_id,
						D.Diag_Code,
						D.Diag_Name,
						convert(varchar(10),ED.EvnDie_setDate,104) as EvnDie_setDate,
						(convert(varchar(10),ED.EvnDie_expDate,104)+' '+ED.EvnDie_expTime) as EvnDie_expDT
					from
						v_EvnDie ED with(nolock)
						left join v_Diag D with(nolock) on D.Diag_id = ED.Diag_aid
					where
						ED.EvnDie_id = :Evn_id
				";
				$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
				if ( !empty($diagFilter) ) {
					$query .= " and {$diagFilter}";
				}
				break;

			case 'EvnLeave':
				$query = "
					select top 1
						EL.EvnLeave_id,
						RD.ResultDesease_id,
						RD.ResultDesease_Name,
						LC.LeaveCause_id,
						LC.LeaveCause_Name,
						convert(varchar(10),EL.EvnLeave_setDate,104) as EvnLeave_setDate
					from
						v_EvnLeave EL with(nolock)
						left join v_LeaveCause LC with(nolock) on LC.LeaveCause_id = EL.LeaveCause_id
						left join v_ResultDesease RD with(nolock) on RD.ResultDesease_id = EL.ResultDesease_id
					where
						EL.EvnLeave_id = :Evn_id
				";
				break;

			case 'EvnOtherLpu':
				$query = "
					select top 1
						EOL.EvnOtherLpu_id,
						RD.ResultDesease_id,
						RD.ResultDesease_Name,
						LC.LeaveCause_id,
						LC.LeaveCause_Name,
						OL.Lpu_id as OtherLpu_id,
						OL.Lpu_Nick as OtherLpu_Nick,
						convert(varchar(10),EOL.EvnOtherLpu_setDate,104) as EvnOtherLpu_setDate
					from
						v_EvnOtherLpu EOL with(nolock)
						left join v_LeaveCause LC with(nolock) on LC.LeaveCause_id = EOL.LeaveCause_id
						left join v_ResultDesease RD with(nolock) on RD.ResultDesease_id = EOL.ResultDesease_id
						left join v_Lpu OL with(nolock) on OL.Lpu_id = EOL.Org_oid
					where
						EOL.EvnOtherLpu_id = :Evn_id
				";
				break;

			case 'EvnOtherSection':
				$query = "
					select top 1
						EOS.EvnOtherSection_id,
						RD.ResultDesease_id,
						RD.ResultDesease_Name,
						LC.LeaveCause_id,
						LC.LeaveCause_Name,
						OLS.Lpu_id,
						OLS.LpuSection_id as OtherLpuSection_id,
						OLS.LpuSection_Name as OtherLpuSection_Name,
						convert(varchar(10),EOS.EvnOtherSection_setDate,104) as EvnOtherSection_setDate
					from
						v_EvnOtherSection EOS with(nolock)
						left join v_LeaveCause LC with(nolock) on LC.LeaveCause_id = EOS.LeaveCause_id
						left join v_ResultDesease RD with(nolock) on RD.ResultDesease_id = EOS.ResultDesease_id
						left join v_LpuSection OLS with(nolock) on OLS.LpuSection_id = EOS.LpuSection_oid
					where
						EOS.EvnOtherSection_id = :Evn_id
				";
				break;

			case 'EvnOtherStac':
				$query = "
					select top 1
						EOS.EvnOtherStac_id,
						RD.ResultDesease_id,
						RD.ResultDesease_Name,
						LC.LeaveCause_id,
						LC.LeaveCause_Name,
						OLUT.LpuUnitType_id as OtherLpuUnitType_id,
						OLUT.LpuUnitType_Name as OtherLpuUnitType_Name,
						OLS.Lpu_id,
						OLS.LpuSection_id as OtherLpuSection_id,
						OLS.LpuSection_Name as OtherLpuSection_Name,
						convert(varchar(10),EOS.EvnOtherStac_setDate,104) as EvnOtherStac_setDate
					from
						v_EvnOtherStac EOS with(nolock)
						left join v_LeaveCause LC with(nolock) on LC.LeaveCause_id = EOS.LeaveCause_id
						left join v_ResultDesease RD with(nolock) on RD.ResultDesease_id = EOS.ResultDesease_id
						left join v_LpuUnitType OLUT with(nolock) on OLUT.LpuUnitType_id = EOS.LpuUnitType_oid
						left join v_LpuSection OLS with(nolock) on OLS.LpuSection_id = EOS.LpuSection_oid
					where
						EOS.EvnOtherStac_id = :Evn_id
				";
				break;

			case 'EvnDirection':
				$query = "
					select top 1
						ED.EvnDirection_id,
						ED.EvnDirection_Num,
						DT.DirType_Name,
						DFT.DirFailType_id,
						DFT.DirFailType_Name,
						isnull(fMP.Person_Fio,'') as MedPersonalFail_Fio,
						isnull(fLpu.Lpu_Nick,'') as LpuFail_Nick,
						L.Lpu_Nick,
						ED.Lpu_id,
						case when EQ.EvnQueue_id is not null then 'В очереди'
							else convert(varchar(10), coalesce(TTMS.TimetableMedService_begTime, TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime, TTS.TimetableStac_setDate), 104) + ' ' +
							convert(varchar(5), coalesce(TTMS.TimetableMedService_begTime, TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime, TTS.TimetableStac_setDate), 108)
						end as EvnDirection_setDateTime,
						LSP.LpuSectionProfile_Code,
						LSP.LpuSectionProfile_Name
					from
						v_EvnDirection ED with(nolock)
						left join v_DirType DT with(nolock) on DT.DirType_id = ED.DirType_id
						left join v_Lpu_all L with(nolock) on L.Lpu_id = ED.Lpu_did
						left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
						left join v_TimetableGraf_lite TTG with (nolock) on TTG.EvnDirection_id = ED.EvnDirection_id
						left join TimetablePar TTP with (nolock) on TTP.TimetablePar_id = ED.TimetablePar_id
						left join v_TimetableStac_lite TTS with (nolock) on TTS.EvnDirection_id = ED.EvnDirection_id
						left join v_TimetableMedService_lite TTMS with (nolock) on TTMS.EvnDirection_id = ED.EvnDirection_id
						left join v_EvnQueue EQ with(nolock) on EQ.EvnDirection_id = ED.EvnDirection_id
						left join v_pmUserCache fUser with(nolock) on fUser.PMUser_id = ED.pmUser_failID
						left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
						outer apply(
							select top 1 MP.MedPersonal_id, MP.Person_Fio
							from v_MedPersonal MP with(nolock)
							where MP.MedPersonal_id = fUser.MedPersonal_id and MP.WorkType_id = 1
						) fMP
						left join v_Lpu_all fLpu with(nolock) on fLpu.Lpu_id = fUser.Lpu_id
					where
						ED.EvnDirection_id = :Evn_id
				";
				//echo getDebugSQL($query,$params);exit;
				break;
			case 'EvnReanimatPeriod':           //BOB - 10.04.2017
					$query = "
							select  ERP.EvnReanimatPeriod_id,
									LS.LpuSection_id,
									LS.LpuSection_Name,
									L.Lpu_id,
									L.Lpu_Nick,
									MS.MedService_id,
									MS.MedService_Name,
									ERP.EvnReanimatPeriod_setDate,
									convert(varchar(10), ERP.EvnReanimatPeriod_setDate  ,104) as EvnReanimatPeriod_setDate,
									convert(varchar(10), ERP.EvnReanimatPeriod_disDT  ,104) as EvnReanimatPeriod_disDate
							from
									v_EvnReanimatPeriod ERP with(nolock)
									left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ERP.LpuSection_id
									left join v_Lpu L with(nolock) on L.Lpu_id = LS.Lpu_id
									left join v_MedService MS with(nolock) on MS.MedService_id = ERP.MedService_id
							where
									ERP.EvnReanimatPeriod_id = :Evn_id
							";
					break;

			default:
				return false;
		}

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}
		$res_arr = $result->result('array');

		if (is_array($res_arr) && count($res_arr) == 1) {
			return $res_arr[0];
		} else {
			return false;
		}
	}

	/**
	 * Поиск событий привязанных к переданному
	 */
	function getLinkedEvnData( $data ) {
		$params = array('object_id' => $data['object_id']);
		switch ( $data['object'] ) {
			//поиск событий созданных по экстренному направлению (направлению не связанному с расписаниями и очередью)
			case 'EvnDirection':
				$query = '
					select
						case
							when EvnPS.EvnPS_id is not null
							then EvnPS.EvnPS_id
							when EvnPL.EvnPL_id is not null
							then EvnPL.EvnPL_id
						end as Evn_id
						,EvnClass.EvnClass_SysNick
						,null as Evn_pid
						,null as ParentEvnClass_SysNick
					from v_EvnDirection EvnDirection with (nolock)
						left join v_EvnPS EvnPS with (nolock) on EvnPS.EvnDirection_id = EvnDirection.EvnDirection_id
						left join v_EvnPL EvnPL with (nolock) on EvnPL.EvnDirection_id = EvnDirection.EvnDirection_id
						inner join EvnClass with (NOLOCK) on isnull(EvnPS.EvnClass_id,EvnPL.EvnClass_id) = EvnClass.EvnClass_id
					where EvnDirection.EvnDirection_id = :object_id
				';
				break;
			//поиск событий созданных из очереди к врачу, службы, отделения стационара, параклиники
			case 'EvnQueue':
				$query = '
					select
						coalesce(EvnUslugaPar.EvnUslugaPar_id, EvnPS.EvnPS_id, EvnPL.EvnPL_id) as Evn_id
						,EvnClass.EvnClass_SysNick
						,null as Evn_pid
						,null as ParentEvnClass_SysNick
					from v_EvnQueue EvnQueue with (nolock)
						left join EvnLabRequest with (NOLOCK) on EvnLabRequest.EvnDirection_id = EvnQueue.EvnDirection_id
						left join EvnLabSample with (NOLOCK) on EvnLabRequest.EvnLabRequest_id = EvnLabSample.EvnLabRequest_id
						left join v_EvnUslugaPar EvnUslugaPar with (nolock) on EvnQueue.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id and EvnLabSample.EvnLabRequest_id is not null
						left join v_EvnDirection_all EvnDirection with (nolock) on EvnDirection.EvnDirection_id = EvnQueue.EvnDirection_id
						left join v_EvnPS EvnPS with (nolock) on EvnPS.EvnDirection_id = EvnDirection.EvnDirection_id
						left join v_EvnPL EvnPL with (nolock) on EvnPL.EvnDirection_id = EvnDirection.EvnDirection_id
						inner join EvnClass with (NOLOCK) on coalesce(EvnUslugaPar.EvnClass_id
							,EvnPS.EvnClass_id
							,EvnPL.EvnClass_id) = EvnClass.EvnClass_id
					where EvnQueue.EvnQueue_id = :object_id
				';
				break;
			case 'EvnUslugaPar':
				$query = '
					select
						Evn.EvnUslugaPar_id as Evn_id,
						EvnClass.EvnClass_SysNick,
						null as Evn_pid,
						null as ParentEvnClass_SysNick
					from
						v_EvnUslugaPar Evn with (NOLOCK)
						inner join EvnClass with (NOLOCK) on Evn.EvnClass_id = EvnClass.EvnClass_id
					where
						Evn.EvnUslugaPar_id = :object_id
						and Evn.EvnUslugaPar_setDT is not null
				';
				break;
			default:
				return array(array('Error_Msg' => 'Указанный тип события не существует.'));
		}
		$result = $this->db->query( $query,
			$params );
		if ( is_object( $result ) ) {
			return $result->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * Установка признака "Переходный случай между МО"
	 */
	function setEvnIsTransit($data) {

		$queryParams = array(
			'Evn_id' => $data['Evn_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Evn_IsTransit' => (!empty($data['Evn_IsTransit']) && $data['Evn_IsTransit'] == 2 ? 2 : 1)
		);

		//Смотрим есть ли у карты второй этап
		$EvnPLDispSecond_id = $this->getFirstResultFromQuery("select top 1 EvnPLDisp_id from v_EvnPLDisp with(nolock) where EvnPLDisp_fid = :Evn_id", array(
			'Evn_id' => $data['Evn_id']
		));

		//Если есть второй этап то проставляем ему переходной признак
		if (!empty($EvnPLDispSecond_id)){
			$filter = " Evn_rid in (:Evn_id, :Evn_sid) ";
			$queryParams['Evn_sid'] = $EvnPLDispSecond_id;
		} else {
			$filter = " Evn_rid = :Evn_id ";
		}

		$query = "
			declare
				@Error_Code bigint = null,
				@Error_Message varchar(4000) = '';

			set nocount on

			begin try
				update Evn with (rowlock)
				set Evn_IsTransit = :Evn_IsTransit
				where
					{$filter}
					and Lpu_id = :Lpu_id
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off

			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->queryResult($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных по событию
	 */
	function getDataByEvn($data) {
		$fields = "";
		$joins = "";
		if (!empty($data['mode']) && $data['mode'] == 'registry') {
			$fields .= ", E.EvnClass_id";
			$fields .= ", E.Person_id";
			$fields .= ", E.Server_id";
			$fields .= ", E.PersonEvn_id";
			$fields .= ", EPLD.DispClass_id";
			$joins .= " left join v_EvnPLDisp EPLD with (nolock) on EPLD.EvnPLDisp_id = E.Evn_id";
		}

		if (!empty($data['mode']) && $data['mode'] == 'getPid') {
			$fields .= ", E.Evn_pid";
		}

		$query = "
			select
				convert(varchar(10), E.Evn_setDT, 104) as Evn_setDate
				{$fields}
			from
				v_Evn E with (nolock)
				{$joins}
			where
				E.Evn_id = :Evn_id
		";

		$queryParams = array(
			'Evn_id' => $data['Evn_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}
		$response = $result->result('array');
		return $response;
	}

	/**
	 * Получение списка классов событий пациента
	 */
	public function getPersonEvnClassList($data) {

		$filterList = array();

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filterList[] = " Person_id in ({$data['person_in']}) ";
		} else {
			$filterList[] = " Person_id = :Person_id ";
		}

		$response = array();
		$archive_database_enable = $this->config->item('archive_database_enable');

		if ( !empty($archive_database_enable) ) {
			if ( empty($_REQUEST['useArchive']) ) {
				// только актуальные
				$filterList[] = "ISNULL(Evn_IsArchive, 1) = 1";
}
			else if ( !empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1 ) {
				// только архивные
				$filterList[] = "ISNULL(Evn_IsArchive, 1) = 2";
			}
		}

		if (empty($data['ignoreFilterByEvnPid'])) {
			if (!empty($data['Evn_pid'])) {
				$filterList[] = "Evn_pid = :Evn_pid";
			} else {
				$filterList[] = "(Evn_pid is null or EvnClass_SysNick in ('EvnPrescrMse','EvnUslugaTelemed'))";
			}
		}

		$queryResult = $this->queryResult("
			select distinct
				EvnClass_SysNick,
				Person_id
			from v_Evn with (nolock)
			where " . implode(' and ', $filterList) . "
		", array(
			'Person_id' => $data['Person_id'],
			'Evn_pid' => !empty($data['Evn_pid']) ? $data['Evn_pid'] : null,
		));

		if ( is_array($queryResult) ) {
			if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$resp = $this->lis->GET('Evn/PersonEvnClassList', $data, 'list');
				if ($this->isSuccessful($resp)) {
					$queryResult = array_merge($queryResult, $resp);
				}
			}

			foreach ( $queryResult as $row ) {
				if (empty($data['person_in'])) {
					if (!in_array($row['EvnClass_SysNick'], $response)) {
						$response[] = $row['EvnClass_SysNick'];
					}
				} else {
					// для оффлайн режима, когда указано много персонов
					// группируем по классу
					if (strpos($row['EvnClass_SysNick'], 'EvnPLDisp') !== false) {

						if (!isset($response['EvnPLDisp'])) $response['EvnPLDisp'] = array();

						// для диспансеризационных классов объединяем всех в один
						if (!in_array($row['Person_id'], $response['EvnPLDisp'])) {
							$response['EvnPLDisp'][] = $row['Person_id'];
						}

					} else {
						// для остальных отдельно
						$response[$row['EvnClass_SysNick']][] = $row['Person_id'];
					}
				}
			}
		}
		return $response;
	}

	/**
	 * Получает количество дочерних объектов
	 */
	function getCountChildren($data) {
		if (empty($data['Evn_id']) ) { return false;}

		$filter = "";
		if (!empty($data['EvnClass_SysNick'])) {
			$filter .= " and E.EvnClass_SysNick = :EvnClass_SysNick";
		}
		
		$query = "
			select count(Evn_id) as count
			from
				v_Evn E with (nolock)
			where
				e.Evn_pid = :Evn_id
				{$filter}
					
		";

		$resp = $this->getFirstResultFromQuery($query, $data, true);
		if ($resp === null) {
			$resp = 0;
		}

		return $resp;

	}

	/**
	 *	Общие проверки, выполняемые при удалении события
	 * продублировал из контроллера чтобы можно было вызвать из АПИ
	 */
	function doCommonChecksOnDelete(&$data) {
		// Получаем список связанных событий
		$evnTreeData = $this->getRelatedEvnList($data);

		if ( !is_array($evnTreeData) || count($evnTreeData) == 0 ) {
			return array(array('Error_Code' => 1,'Error_Msg'=>'Ошибка при получении списка связанных событий'));
}

		// Получаем класс и идентификатор удаляемого случая с учетом класса события
		foreach ( $evnTreeData as $evnData ) {
			if ( $evnData['Evn_id'] == $data['Evn_id'] ) {
				if ( $evnData['Lpu_id'] != $data['Lpu_id'] ) {
					return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. он был создан в другой МО'));
				}

				$data['EvnClass_SysNick'] = $evnData['EvnClass_SysNick'];
				$data[$evnData['EvnClass_SysNick'] . '_id'] = $evnData['Evn_id'];
				$data['Person_id'] = $evnData['Person_id'];
				break;
			}
		}

		// Проверяем, чтобы в случае не было ЛВН, не было подписанных документов, не было рецептов, не было выполненного назначения, не было списания медикаментов
		// https://redmine.swan.perm.ru/issues/18053
		// https://redmine.swan.perm.ru/issues/47549
		// https://redmine.swan.perm.ru/issues/73268
		// https://redmine.swan.perm.ru//issues/109266
		foreach ( $evnTreeData as $evnData ) {
			// Проверяем признак подписания документа
			if ( $evnData['Evn_IsSigned'] == 2 ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. в рамках случая имеются подписанные документы'));
			}
			// Наличие дочерних ЛВН в случае, если удаляем не ЛВН
			else if ( !in_array($data['EvnClass_SysNick'], array('EvnStick', 'EvnStickDop', 'EvnStickStudent')) && in_array($evnData['EvnClass_SysNick'], array('EvnStick', 'EvnStickDop', 'EvnStickStudent')) ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. в рамках случая имеются выданные листы временной нетрудоспособности'));
			}
			// Наличие рецептов в случае, если удаляем не рецепт
			else if ( !in_array($data['EvnClass_SysNick'], array('EvnRecept')) && in_array($evnData['EvnClass_SysNick'], array('EvnRecept')) ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. в рамках случая имеются выписанные рецепты'));
			}
			// Наличие назначений, если удаляем не назначение
			else if ( !preg_match('/^EvnPrescr/', $data['EvnClass_SysNick']) && preg_match('/^EvnPrescr/', $evnData['EvnClass_SysNick']) && !in_array($evnData['EvnClass_SysNick'], array('EvnPrescrMse', 'EvnPrescrVK')) ) {
				// @task https://redmine.swan.perm.ru/issues/74589
				return array(array('Error_Code' => 1, 'Error_Msg' => 'Удаление документа невозможно, т.к. в рамках случая имеются назначения'));
				/*$this->load->model('EvnPrescr_model');
				$response = $this->EvnPrescr_model->getEvnPrescrIsExec(array('EvnPrescr_id' => $evnData['Evn_id'], 'EvnClass_SysNick' => $evnData['EvnClass_SysNick']));
				if (!is_array($response) || !isset($response[0]) || !isset($response[0]['EvnPrescr_IsExec'])) {
					return array(array('Error_Code' => 10, 'Error_Msg' => 'Ошибка при получении статуса выполнения назначения'));
				}
				if ($response[0]['EvnPrescr_IsExec'] == 2) {
					return array(array('Error_Code' => 1, 'Error_Msg' => 'Удаление документа невозможно, т.к. в рамках случая имеются выполненные назначения'));
				}*/
			}
			// Наличие списания медикаментов в случае, если удаляем не факт списания медикамента
			else if ( !in_array($data['EvnClass_SysNick'], array('EvnDrug')) && in_array($evnData['EvnClass_SysNick'], array('EvnDrug')) ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Данные о случаях лечения, содержащие сведения об использовании медикаментов, не могут быть удалены'));
			}
		}

		// Проверка есть ли в реестрах записи об этом случае
		if ( in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPS', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection')) ) {
			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');

			// Соединение с реестровой БД происходит в методе checkEvnAccessInRegistry
			$registryData = $this->Reg_model->checkEvnAccessInRegistry($data);

			if (is_array($registryData)) {
				return array($registryData);
			}
		}

		// Проверка, есть ли в стомат. посещении услуги
		if ( in_array($data['EvnClass_SysNick'], array('EvnVizitPLStom')) ) {
			$this->load->model('EvnVizitPLStom_model', 'EvnVizitPLStom_model');
			$checkResult = $this->EvnVizitPLStom_model->checkEvnUslugaStomCount($data);

			if ( !empty($checkResult) ) {
				return array(
					array('Error_Msg' => $checkResult)
				);
			}

			$checkResult = $this->EvnVizitPLStom_model->checkEvnDiagPLStomCount($data);

			if ( !empty($checkResult) ) {
				return array(
					array('Error_Msg' => $checkResult)
				);
			}
		}

		// Проверка, есть ли в стомат. заболевании услуги
		if ( in_array($data['EvnClass_SysNick'], array('EvnDiagPLStom')) ) {
			$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
			$checkResult = $this->EvnDiagPLStom_model->checkEvnUslugaStomCount($data);

			if ( !empty($checkResult) ) {
				return array(
					array('Error_Msg' => $checkResult)
				);
			}
		}

		$error_arr = $this->onBeforeDelete($data, $evnTreeData);

		if ( is_array($error_arr) ) {
			return $error_arr;
		}

		return true;
	}

	function getEvnClass($data)
	{
		return $this->getFirstResultFromQuery('
			select
				EvnClass_id
			from v_Evn with(nolock)
			where Evn_id = :id
		', $data);
	}

	function getPid($data)
	{
		$query = "
			select
				{$data['evnVizitPLtable']}_pid as pid
			from
				v_{$data['evnVizitPLtable']} with (nolock)
			where
				{$data['evnVizitPLtable']}_id = :id
		";
		return $this->db->query($query, $data);
	}

	/**
	 *	Получение истории смены докторов #192334
	 * 
	 *  @property EvnDoctor_pid 
	 */
	function getDoctorHistory($data)
	{
		$query = "
			select 
				MedPersonal_id 
			from 
				v_EvnDoctor with(nolock)
			where 
				EvnDoctor_pid=:EvnDoctor_pid
		";
		return  $this->queryResult($query, $data);
	}

	/**
	 *	Получение истории смены докторов в тестовом представлении #192334
	 * 
	 *  @property EvnDoctor_pid
	 */
	function getDoctorHistoryWrapper($data)
	{

		$template="{Person_Fio}/{Dolgnost_Name}/{LpuSection_FullName} - {EvnDoctor_insDT}";
		$query = "
			select
				ved.EvnDoctor_id,
				ved.MedPersonal_id,
				vmp.Person_Fio,
				post.name as Dolgnost_Name,
				vls.LpuSection_FullName,
				CONVERT(varchar,ved.EvnDoctor_insDT,104) as EvnDoctor_insDT
			from 
				v_EvnDoctor ved with(nolock)
				inner join v_MedStaffFact vmsf with(nolock) on vmsf.MedStaffFact_id=ved.MedStaffFact_id
				inner join persis.post post with(nolock) on post.id = vmsf.post_id
				inner join v_MedPersonal vmp with(nolock) on vmp.MedPersonal_id=ved.MedPersonal_id and vmp.Lpu_id=ved.Lpu_id
				inner join v_LpuSection vls with(nolock) on vls.LpuSection_id=ved.LpuSection_id
			where EvnDoctor_pid=:EvnDoctor_pid 
			order by ved.EvnDoctor_insDT
		";
		$this->load->library('parser');
		$query_result= $this->queryResult($query, $data);
		$result=[];
		foreach($query_result as $row){
			$result[]=["text"=>$this->parser->parse_string($template, $row, true)];
		}
		return $result;
	}

	/**
	 *	Получение истории смены докторов в тестовом представлении для ЕМК #192334
	 * 
	 *  @property EvnDoctor_pid
	 */
	function getDoctorHistoryEMKWrapper($data)
	{

		$template="{Person_Fio} {Dolgnost_Name} {EvnDoctor_insDT} - {LpuSection_FullName}";
		$query = "
			select
				ved.EvnDoctor_id,
				ved.MedPersonal_id,
				vmp.Person_Fio,
				post.name as Dolgnost_Name,
				vls.LpuSection_FullName,
				CONVERT(varchar,ved.EvnDoctor_insDT,104) as EvnDoctor_insDT
			from 
				v_EvnDoctor ved with(nolock)
				inner join v_MedStaffFact vmsf with(nolock) on vmsf.MedStaffFact_id=ved.MedStaffFact_id
				inner join persis.post post with(nolock) on post.id = vmsf.post_id				
				inner join v_MedPersonal vmp with(nolock) on vmp.MedPersonal_id=ved.MedPersonal_id and vmp.Lpu_id=ved.Lpu_id
				inner join v_LpuSection vls with(nolock) on vls.LpuSection_id=ved.LpuSection_id
			where EvnDoctor_pid=:EvnDoctor_pid 
			order by ved.EvnDoctor_insDT
		";
		$this->load->library('parser');
		$query_result= $this->queryResult($query, $data);
		$result=[];
		foreach($query_result as $key=>$row){
			$result[$row["EvnDoctor_id"]]=$this->parser->parse_string($template, $row, true);
		}
		return $result;
	}


	function getEvnPersonByEvnPLId($data) {
		$query = "
			select top 1
				e.Person_id
			from
				v_Evn e (nolock)
			where
				e.Evn_pid = :Evn_id
		";
		return $this->dbmodel->getFirstResultFromQuery($query, $data);
	}

	function getIsFinish($data) {

		if(in_array($data['Evn'], array('EvnPL', 'EvnPLStom'))) {
			$query ="
				select top 1
					EvnPLBase_IsFinish
				from
					v_EvnPLBase (nolock)
				where
					EvnPLBase_id = :Evn_id
			";
		}

		if($data['Evn'] == 'EvnPS') {
			$query = "
				select top 1
					EvnPS_disDT
				from
					v_EvnPS (nolock)
				where
					EvnPS_id = :Evn_id
			";
		}

		return $this->dbmodel->getFirstResultFromQuery($query, $data);
	}

	/**
	 * Получение стадии ХСН
	 */
	function getHsnStage()
	{
		$result = $this->queryResult(
			"SELECT HSNStage_id, HSNStage_Name FROM v_HSNStage WITH (NOLOCK)");

		if (!empty($result))
		{
			return $result;
		}
		else
		{
			return [[]];
		}
	}

	/**
	 * Получение функционального класса ХСН
	 */
	function getHSNFuncClass()
	{
		$result = $this->queryResult(
			"SELECT HSNFuncClass_id, HSNFuncClass_Name FROM v_HSNFuncClass WITH (NOLOCK)");

		if (!empty($result))
		{
			return $result;
		}
		else
		{
			return [[]];
		}
	}

	/**
	 * Относится ли диагноз с заданным идентификатором к группе ХСН
	 */
	function isHsn($id)
	{
		if (empty($id))
		{
			return false;
		}

		$resp = $this->queryResult(
			"SELECT Diag_Code FROM v_Diag WHERE Diag_id = :id",
			array('id' => $id)
		);

		if (!empty($resp) && is_array($resp) && array_key_exists(0, $resp) &&
			(is_array($tmp = $resp[0])) && !empty($code = $tmp['Diag_Code']) &&
			($code == 'I50.0' || $code == 'I50.1' || $code == 'I50.9'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Сохранение детализации диагноза ХСН по пациенту в рамках события
	 */
	function saveEvnDiagHSNDetails($data)
	{
		$id = "";
		$evnId = $data['Evn_id'];

		// Ид. основного диагноза:
		$diagId = $data['Diag_id'];

		// Если ни основной диагноз, ни осложнение не переданы, ничего не
		// делаем:
		if (empty($diagId) && empty($complDiagId))
		{
			return false;
		}

		$isHsn = false;

		// Если диагноз известен, проверяем, относится ли он к ХСН:
		if (!empty($diagId))
		{
			$isHsn = $this->isHsn($diagId);
		}

		// Если диагноз не относится к ХСН (или неизвестен), но известно
		// осложнение, проверяем осложнение:
		if (!$isHsn && !empty($complDiagId))
		{
			$isHsn = $this->isHsn($complDiagId);
		}

		// Если ид. детализации не задан, ищем его по ид. события:
		if (empty($id) && !empty($evnId))
		{
			$resp = $this->queryResult(
				"SELECT DiagHSNDetails_id
				FROM v_DiagHSNDetails
				WHERE Evn_id = :Evn_id
				ORDER BY DiagHSNDetails_insDT desc",
				array('Evn_id' => $evnId));
 
			if (!empty($resp) && is_array($resp) && array_key_exists(0, $resp) &&
				(is_array($tmp = $resp[0])) && !empty($tmp['DiagHSNDetails_id']))
			{
				$id = $tmp['DiagHSNDetails_id'];
			}
		}

		// Определяем, что нужно сделать в таблице с детализацией по ХСН:
		$action =
			(empty($id) ? ($isHsn ? "ins": "") : ($isHsn ? "upd": "del"));

		if ($action == 'del' && $data['nonDel']) {
			//Если экшен удаление, но очищать для ивента ХСН не надо - выходим
			return false;
		}

		if (empty($action))
			return false;

		$params = array(
			'DiagHSNDetails_id' => $id,
			'Evn_id' => $evnId,
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
			'HSNStage_id' => $data['HSNStage_id'],
			'HSNFuncClass_id' => $data['HSNFuncClass_id']
		);

		$query = "
			DECLARE
				@DiagHSNDetails_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000);

			EXEC p_DiagHSNDetails_{$action}
				@DiagHSNDetails_id = :DiagHSNDetails_id,";

		if ($action != 'del')
		{
			$query = $query . "
				@Evn_id = :Evn_id,
				@Person_id = :Person_id,
				@HSNStage_id = :HSNStage_id,
				@HSNFuncClass_id = :HSNFuncClass_id,
				@pmUser_id = :pmUser_id,";
		}

		$query = $query . "
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			SELECT
				@DiagHSNDetails_id AS DiagHSNDetails_id,
				@Error_Code AS Error_Code,
				@Error_Message AS Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		return (array($resp));
	}

	/**
	 * Получение последней детализации диагноза ХСН по пациенту
	 */
	function getLastHsnDetails($data)
	{
		$result = $this->db->query(
			"SELECT
				dhd.HSNStage_id,
				dhd.HSNFuncClass_id
			FROM
				v_DiagHSNDetails dhd WITH (NOLOCK)
				LEFT JOIN v_EvnVizitPl evp WITH (NOLOCK) ON dhd.Evn_id = evp.EvnVizitPL_id
			WHERE
				dhd.Person_id = :Person_id
			ORDER BY dhd.DiagHSNDetails_updDT DESC",
			array('Person_id'=> $data['Person_id']));

		if (is_object($result))
		{
			$result = $result->result('array');

			if (count($result) > 0)
			{
				return $result;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
}
