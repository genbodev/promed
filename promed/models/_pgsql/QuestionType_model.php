<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QuestionType_model - модель для работы с вопросами анкет
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.15.2016
 */

class QuestionType_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение настроек для элементов анкеты
	 */
	function loadQuestionTypeSettings($data) {
		$with_filters = array();
		$params = array();

		if (!empty($data['DispClass_id'])) {
			$with_filters[] = "t.DispClass_id = :DispClass_id";
			$with_filters[] = "t.QuestionType_pid is null";
			$params['DispClass_id'] = $data['DispClass_id'];
		}

		if (!empty($data['QuestionType_Code'])) {
			$with_filters[] = "t.QuestionType_Code = :QuestionType_Code";
			$params['QuestionType_Code'] = $data['QuestionType_Code'];
		}

		if (empty($with_filters)) {
			$with_filters[] = "t.QuestionType_pid is null";
		}

        //gaf 119289
        $regionset="where (e.Region_id=".$this->getRegionNumber()." or e.Region_id is null) ";
        if ($this->regionNick == "ufa"){
            //для региона Уфа gaf 14112017
            $regionset=" and not e.QuestionType_id in (182,185,190,194,197,212,213,234,239,245,246,581,258,259,260,266,275,277,278,280,281,286,287,288,289,317,327,329,330,402,403,404,398,583,399,400,553,584,413,415,405,389, 666,343,344,251,252,316,755,756,757,767,768,773,758)";
        }else if ($this->regionNick == "penza"){
            $regionset=" and not e.QuestionType_id in (182,185,190,194,197,212,213,234,239,245,246,581,258,259,260,266,275,277,278,280,281,286,287,288,289,317,327,329,330,402,403,404,398,583,399,400,553,584,413,415,405,389, 666,343,344,251,252,316,755,756,757,773)";
        }else if ($this->regionNick == "astra"){
            //Для Астрахани
            $regionset=" and not e.QuestionType_id in (182,185,190,194,212,213,230,234,239,245,258,259,260,266,277,278,280,281,289,317,327,329,330,377,380,390,391,392,394,413,418,419,420,393,398,399,400,422,401,403,404,405,602,606,660,661,662,663,664,665,666,668,666,582,583,584,666,747,748,749,750,751,752,753,754,767,768,773)";
            $with_filters[] = "not t.QuestionType_id in (392,413)";
        }else if ($this->regionNick == "khak"){
            //Для Хакассии
            $regionset=" and not e.QuestionType_id in (414,599,600,601,602,603,604,605,606,607,660,661,662,663,664,665,666,667,668,669,670,671,672,666,582,583,584,666,747,748,749,750,751,752,753,755,757,756)";
        }else{
            //для остальных регионов
            //$regionset=" and not e.QuestionType_id in (599,600,601,602,603,604,605,606,607,660,661,662,663,664,665,666,667,668,669,670,671,672,666,582,583,584,666,747,748,749,750,751,752)";
            //$regionset=" and not e.QuestionType_id in (599,600,601,602,603,604,605,606,607,660,661,662,663,664,665,666,667,668,669,670,671,672,666,582,583,584,666,747,748,749,750,751,752,753,755,757,756)";
            $regionset=" and not e.QuestionType_id in (599,600,601,602,603,604,605,606,607,660,661,662,663,664,665,666,667,668,669,670,671,672,666,582,583,584,666,747,748,749,750,751,752,753,755,757,756,758,767,768,773)";
        }

		$with_filters_str = implode(' and ', $with_filters);
		
		$query = "
			with recursive QuestionTypeStruct as (
				select t.QuestionType_id as QuestionType_id,
				 t.QuestionType_pid as QuestionType_pid,
				 1 as Level
				from v_QuestionType t
				where {$with_filters_str}
				union all
				select e.QuestionType_id as QuestionType_id,
				 e.QuestionType_pid as QuestionType_pid,
				 level+1 as Level
				from v_QuestionType e
				inner join QuestionTypeStruct d on d.QuestionType_id = e.QuestionType_pid
				{$regionset}
			)
			select
				QT.QuestionType_id as \"QuestionType_id\",
				QT.QuestionType_pid as \"QuestionType_pid\",
				QT.QuestionType_rid as \"QuestionType_rid\",
				QT.QuestionType_Code as \"QuestionType_Code\",
				QT.QuestionType_Name as \"QuestionType_Name\",
				QT.QuestionType_SysNick as \"QuestionType_SysNick\",
				QT.AnswerType_id as \"AnswerType_id\",
				AC.AnswerClass_id as \"AnswerClass_id\",
				AC.AnswerClass_Code as \"AnswerClass_Code\",
				AC.AnswerClass_SysNick as \"AnswerClass_SysNick\",
				QT.DispClass_id as \"DispClass_id\",
				qts.Level as \"Level\"
			from
				QuestionTypeStruct qts
				inner join v_QuestionType QT on QT.QuestionType_id = qts.QuestionType_id
				left join v_AnswerClass AC on AC.AnswerClass_id = QT.AnswerClass_id
			order by
				QT.QuestionType_GroupNum,
				QT.QuestionType_Code
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при получени списка вопросов');
		}

		$respVision = $this->getQuestionTypeVision($data);
		//$respVision = array();
		if (!is_array($respVision)) {
			return $this->createError('','Ошибка при получении настройки внешнего вида для элементов анкеты');
		}

		$arr = array();
		$maxLevel = 0;
		foreach($response as &$item) {
			if ($maxLevel < $item['Level']) {
				$maxLevel = $item['Level'];
			}
			$arr[$item['QuestionType_id']] = &$item;
		}

		foreach($respVision as $vision) {
			if (!empty($vision['QuestionType_id']) && isset($arr[$vision['QuestionType_id']])) {
				$arr[$vision['QuestionType_id']]['vision'][] = array(
					'id' => $vision['QuestionTypeVision_id'],
					'settings' => json_decode($vision['QuestionTypeVision_Settings'], true),
					'RecordStatus_Code' => 1
				);
			} else if (!empty($vision['QuestionType_pid']) && isset($arr[$vision['QuestionType_pid']])) {
				$arr[$vision['QuestionType_pid']]['childrenVision'][] = array(
					'id' => $vision['QuestionTypeVision_id'],
					'settings' => json_decode($vision['QuestionTypeVision_Settings'], true),
					'RecordStatus_Code' => 1
				);
			}
		}

		return array(array(
			'success' => true,
			'settings' => $response
		));
	}

	/**
	 * Получение настроек вывода элементов анкеты на форму
	 */
	function getQuestionTypeVision($data) {
		$params = array('DispClass_id' => $data['DispClass_id']);
        //gaf 119289
        if ($this->regionNick == "ufa" || $this->regionNick == "penza"){
            //для уфы gaf 14112017
            $regionset=" and not QuestionTypeVision_id in (5,14,15,16,20,25,26,28,30,31,33,29,41,37,38,39,40,46,47,185,36,362,262,184,187,188,186,185,191,192,99/*,405*/,82,35,244,430,455,456,457,458,459,460,461,463,464,472,502,503,504,505,507)";
        }else if ($this->regionNick == "astra"){
            //Для Астрахани 08102018
            $regionset=" and not QuestionTypeVision_id in (5,15,20,25,30,31,33,37,38,39,40,41,46,47,82,87,88,89,90,91,99,100,189,414,212,213,214,215,216,222,223,250,362,227,228,231,232,233,248,249,250,258,184,187,188,186,185,415,429,452,453,454,412,502,503,504,505,507)";
        }else if ($this->regionNick == "khak"){
            //Для Хакассии 07112019
            $regionset=" and not QuestionTypeVision_id in (20,76,79,191,192,212,213,214,215,216,217,218,219,220,221,222,223,226,227,228,229,230,231,232,233,248,249,250,251,258,388,390,391,395,184,187,188,186,185,410,419,429,452,453,454,412,455,456,457,458,459,460,461,463,464)";
        }else{
            //для остальных регионов gaf 14112017
            $regionset=" and not QuestionTypeVision_id in (212,213,214,215,216,217,218,219,220,221,222,223,226,227,228,229,230,231,232,233,248,249,250,251,258,388,390,391,395,184,187,188,186,185,410,419,429,452,453,455,456,457,458,459,460,461,463,464,472,502,503,504,505,507)";
        }
		$query = "
			select
				QuestionTypeVision_id as \"QuestionTypeVision_id\",
				QuestionType_id as \"QuestionType_id\",
				QuestionType_pid as \"QuestionType_pid\",
				DispClass_id as \"DispClass_id\",
				QuestionTypeVision_Settings as \"QuestionTypeVision_Settings\"
			from v_QuestionTypeVision
			where DispClass_id = :DispClass_id
			{$regionset}
		";
		$response = $this->queryResult($query, $params);

		return $response;
	}

	/**
	 * Сохранение массива настроек отображения анкет
	 */
	function saveQuestionTypeVisionList($data) {
		$this->beginTransaction();

		try{
			foreach($data['QuestionTypeVisionList'] as $QuestionTypeVision) {
				$QuestionTypeVision['pmUser_id'] = $data['pmUser_id'];
				switch($QuestionTypeVision['RecordStatus_Code']) {
					case 0:
						$QuestionTypeVision['QuestionTypeVision_id'] = null;
						$resp = $this->saveQuestionTypeVision($QuestionTypeVision);
						break;
					case 2:
						$resp = $this->saveQuestionTypeVision($QuestionTypeVision);
						break;
					case 3:
						$resp = $this->deleteQuestionTypeVision($QuestionTypeVision);
						break;
				}
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp['Error_Code'], $resp['Error_Msg']);
				}
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array(array('success' => true));
	}

	/**
	 * Сохранение настроки отображения элемента анкеты
	 */
	function saveQuestionTypeVision($data) {
		$params = array(
			'QuestionTypeVision_id' => !empty($data['QuestionTypeVision_id'])?$data['QuestionTypeVision_id']:null,
			'DispClass_id' => $data['DispClass_id'],
			'QuestionType_pid' => !empty($data['QuestionType_pid'])?$data['QuestionType_pid']:null,
			'QuestionType_id' => !empty($data['QuestionType_id'])?$data['QuestionType_id']:null,
			'QuestionTypeVision_Settings' => !empty($data['QuestionTypeVision_Settings'])?$data['QuestionTypeVision_Settings']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['QuestionType_id']) && empty($params['QuestionType_pid'])) {
			$pid = $this->getFirstResultFromQuery("
				select  QuestionType_pid 
				from v_QuestionType
				where QuestionType_id = :QuestionType_id 
				limit 1
			", array('QuestionType_id' => $params['QuestionType_id']));
			if ($pid === false) {
				return $this->createError('','Ошибка при получении идентификатора родительского элемента');
			}
			$params['QuestionType_pid'] = $pid;
		}

		if (empty($params['QuestionTypeVision_id'])) {
			$procedure = 'p_QuestionTypeVision_ins';
		} else {
			$procedure = 'p_QuestionTypeVision_upd';
		}

		$query = "
		    select 
		        QuestionTypeVision_id as \"QuestionTypeVision_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from {$procedure} (
				QuestionTypeVision_id := :QuestionTypeVision_id,
				QuestionType_id := :QuestionType_id,
				QuestionType_pid := :QuestionType_pid,
				DispClass_id := :DispClass_id,
				QuestionTypeVision_Settings := :QuestionTypeVision_Settings,
				pmUser_id := :pmUser_id
				)
					";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->createError('','Ошибка при сохранении настройки отображения элемента анкеты');
		}
		return $response;
	}

	/**
	 * Удаление настройки отображения элемента анкеты
	 */
	function deleteQuestionTypeVision($data) {
		$params = array(
			'QuestionTypeVision_id' => $data['QuestionTypeVision_id'],
		);

		$query = "
		    select
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_QuestionTypeVision_del(
				QuestionTypeVision_id := :QuestionTypeVision_id
				)	
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->createError('','Ошибка при удалении настройки отображения элемента анкеты');
		}
		return $response;
	}

	/**
	 * Получение списка
	 */
	function loadQuestionTypeVisionGrid($data) {
		$params = array();

		$query = "
			select
				QTV.QuestionTypeVision_id as \"QuestionTypeVision_id\",
				QTV.QuestionType_id as \"QuestionType_id\",
				QTV.QuestionType_pid as \"QuestionType_pid\",
				QTV.DispClass_id as \"DispClass_id\",
				coalesce(QT.QuestionType_Name, DC.DispClass_Name) as \"QuestionTypeVision_Name\"
			from
				v_QuestionTypeVision QTV
				left join v_QuestionType QT on QT.QuestionType_id = coalesce(QTV.QuestionType_id, QTV.QuestionType_pid)
				left join v_DispClass DC on DC.DispClass_id = QTV.DispClass_id
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при получении списка настроек вопросов анкеты');
		}

		return array('data' => $response);
	}

	/**
	 * Получение данных для формы настройки отображения элемента анкеты
	 */
	function loadQuestionTypeVisionForm($data) {
		$params = array(
			'QuestionTypeVision_id' => $data['QuestionTypeVision_id'],
		);
		$query = "
			select 
				QTV.QuestionTypeVision_id as \"QuestionTypeVision_id\",
				QTV.QuestionType_id as \"QuestionType_id\",
				QTV.QuestionType_pid as \"QuestionType_pid\",
				QTV.DispClass_id as \"DispClass_id\",
				QTV.QuestionTypeVision_Settings as \"QuestionTypeVision_Settings\"
			from v_QuestionTypeVision QTV
			where QTV.QuestionTypeVision_id = :QuestionTypeVision_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка анкет из справочника видов диспансеризаций
	 */
	function loadDispClassList($data) {
		$params = array();
		$query = "
			select
				DC.DispClass_id as \"DispClass_id\",
				DC.DispClass_Code as \"DispClass_Code\",
				DC.DispClass_Name as \"DispClass_Name\"
			from
				v_DispClass DC
			where
				exists(select * from v_QuestionType where DispClass_id = DC.DispClass_id)
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получения списка элементов анкет
	 */
	function loadQuestionTypeList($data) {
		$params = array();
		$filters = "1=1";
		$regime_id = !empty($data['Regime_id'])?(int)$data['Regime_id']:1;

		if (!empty($data['DispClass_id'])) {
			$params['DispClass_id'] = $data['DispClass_id'];
			$filters .= "
				and QT.DispClass_id = :DispClass_id";
		}
		if (!empty($data['QuestionType_pid'])) {
			if (!empty($data['DispClass_id']) && $data['QuestionType_pid'] == -1) {
				$filters .= "
				and QT.QuestionType_pid is null";
			} else {
				$params['QuestionType_pid'] = $data['QuestionType_pid'];
				$filters .= "
				and QT.QuestionType_pid = :QuestionType_pid";
			}
		}

		switch($regime_id) {
			case 1:	//Настройка отображения элемента. Выводятся все элементы
				break;
			case 2:	//Настройка отображения дочерних элементов в группе. Выводятся группы
				$filters .= "
				and exists(select * from v_QuestionType where QuestionType_pid = QT.QuestionType_id)";
				break;
			case 3:	//Настройка только вопросов. У них указан тип ответа.
				$filters .= "
				and QT.AnswerType_id is not null";
				break;
		}

		$query = "
			select
				QT.QuestionType_id as \"QuestionType_id\",
				QT.QuestionType_pid as \"QuestionType_pid\",
				QT.QuestionType_rid as \"QuestionType_rid\",
				QT.QuestionType_Code as \"QuestionType_Code\",
				QT.QuestionType_Name as \"QuestionType_Name\",
				QT.QuestionType_SysNick as \"QuestionType_SysNick\",
				QT.QuestionType_GroupNum as \"QuestionType_GroupNum\",
				AT.AnswerType_id as \"AnswerType_id\",
				AT.AnswerType_Code as \"AnswerType_Code\",
				AT.AnswerType_Name as \"AnswerType_Name\",
				AC.AnswerClass_id as \"AnswerClass_id\",
				AC.AnswerClass_Code as \"AnswerClass_Code\",
				AC.AnswerClass_Name as \"AnswerClass_Name\",
				AC.AnswerClass_SysNick as \"AnswerClass_SysNick\"
			from
				v_QuestionType QT
				left join v_AnswerType AT on AT.AnswerType_id = QT.AnswerType_id
				left join v_AnswerClass AC on AC.AnswerClass_id = QT.AnswerClass_id
			where
				{$filters}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}
}