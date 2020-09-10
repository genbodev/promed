<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		05.11.2013
 */
 
/**
 * Модель маркеров и их связей
 */
class FreeDocument_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		$this->load->library('swMongoCache');
		parent::__construct();
	}

	/**
	 * Возвращает истину, если пользователю разрешено создание/редактирование маркера
	 */
	function allowEdit($data)
	{
		if ( isSuperadmin() ) {
			return true;
		} else {
			return array(array('Error_Msg' => 'Функционал создания и редактирования маркеров доступен только для пользователей, с указанной группой «Cуперадминистратор».'));
		}
	}

	/**
	 * Приватный вспомогательный метод для подготовки информации о маркере
	 * @param $array
	 * @param $field
	 * @param string $alternative_field
	 * @param string $default_value
	 * @return string
	 */
	function selectValue($array, $field, $alternative_field = '', $default_value = '') { //небольшая вспомогательная функция для insertIntoMarkerArray
		$value = '';
		if (isset($array[$field])) {
			$value = $array[$field];
		} else if (!empty($alternative_field) && isset($array[$alternative_field])) {
			$value = $array[$alternative_field];
		} else {
			$value = $default_value;
		}
		return $value;
	}

	/**
	 * Подготовка информации о маркере, для дальнейшей обработке в составе массива
	 * @param $markers
	 * @param $marker
	 */
	function insertIntoMarkerArray(&$markers, $marker) {
		$markers[$this->selectValue($marker, 'id', 'FreeDocMarker_id', 0)] = array(
			'name' => mb_strtolower($this->selectValue($marker, 'name', 'FreeDocMarker_Name')),
			'original_name' => $this->selectValue($marker, 'name', 'FreeDocMarker_Name'),
			'alias' => $this->selectValue($marker, 'alias', 'FreeDocMarker_TableAlias'),
			'field' => $this->selectValue($marker, 'field', 'FreeDocMarker_Field'),
			'query' => $this->selectValue($marker, 'query', 'FreeDocMarker_Query'),
			'is_table' => (($this->selectValue($marker, 'is_table', 'FreeDocMarker_IsTableValue')) == 2),
			'options' => $this->selectValue($marker, 'options', 'FreeDocMarker_Options'),
			'table_chain' => array(),
			'error' => false
		);
	}

	/**
	 * Заполняем цепочки таблиц для маркеров
	 * @param $markers
	 * @param $evnclass_id
	 */
	function buildTableChains(&$markers, $evnclass_id) {
		//$this->load->database();
		//$this->load->model('FreeDocument_model', 'dbmodel');
		
		$max_chain_len = 50; //максимальная длинна цепочек, для предотвращения зацикливаний
		for ($i = 0; $i < $max_chain_len; $i++) {
			$unfinished = array();
			foreach($markers as $key => $marker) {
				$chain_len = count($marker['table_chain']);
				if (!empty($marker['alias'])) {
					$alias = $chain_len == 0 ? $marker['alias'] : $marker['table_chain'][$chain_len-1]['linked_alias'];
					if (!empty($alias))
						$unfinished[] = $alias;
				}
			}
			
			if (count($unfinished) > 0) { //если есть неоконченые цепочки извлекаем по ним данные
				$chain_sections = array();
				$c_sec = $this->getChainSections($unfinished, $evnclass_id);
				foreach($c_sec as $sec) 
					$chain_sections[$sec['alias']] = $sec;
					
					
					
				foreach($markers as $key => $marker) {
					$chain_len = count($marker['table_chain']);
					if (!empty($marker['alias'])) {
						$alias = $chain_len == 0 ? $marker['alias'] : $marker['table_chain'][$chain_len-1]['linked_alias'];
						if (!empty($alias) && isset($chain_sections[$alias]))
							$markers[$key]['table_chain'][] = $chain_sections[$alias];
					}
				}
			} else //иначе прерываем цикл
				$i = $max_chain_len;
		}
		
		//по окончанию обработки ищем неоконченные цепи и помечаем цепочки как ошибочные
		foreach($markers as $key => $marker) {
			$chain_len = count($marker['table_chain']);
			if (!empty($marker['alias'])) {
				if ($chain_len == 0 || !empty($markers[$key]['table_chain'][$chain_len-1]['linked_alias']))
					$markers[$key]['error'] = true;
			}
		}
	}

	/**
	 * Читает документ по идентификатору
	 *
	 * Возможно метод устарел и не используется
	 * @param $data
	 * @return bool
	 */
	function getFreeDocumentById($data) {
		$queryParams = array();
		$query = "";
		if (isset($data['FreeDocument_id']) && $data['FreeDocument_id'] > 0) {
			$queryParams = array('EvnXml_id' => $data['FreeDocument_id']);
			$query = "
				select
					Evn_id as FreeDocument_pid,
					EvnXml_id as FreeDocument_id,
					EvnXml_Data as FreeDocument_Data,
					EvnXml_Name as FreeDocument_Name,
					convert(varchar, EvnXml_insDT, 104) as FreeDocument_Date,
					RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name
				from
					EvnXml with(nolock)
					left join pmUserCache with(nolock) on pmUserCache.pmUser_id = EvnXml.pmUser_insID
				where
					EvnXml_id = :EvnXml_id
			";
		} 
		$result = $this->db->query($query, $queryParams);
		
		if ( is_object($result) ) {
			$res = $result->result('array');
			return $res[0];
		} else {
			return false;
		}
	}

	/**
	 * Читает документ/список документов для отображения в панели просмотра в ЭМК
	 * @param $data
	 * @return bool
	 */
	function getFreeDocumentViewData($data) {
		$queryParams = array();
		$query = '';
		if (isset($data['FreeDocument_id']) && $data['FreeDocument_id'] > 0) {
			$queryParams = array('EvnXml_id' => $data['FreeDocument_id']);
			$query = '
				select
					Evn.Evn_id as FreeDocument_pid,
					EvnClass.EvnClass_id,
					EvnClass.EvnClass_SysNick,
					EvnXml.EvnXml_id as FreeDocument_id,
					EvnXml.EvnXml_Name as FreeDocument_Name,
					EvnXml.EvnXml_Data as FreeDocument_Data,
					convert(varchar, EvnXml.EvnXml_insDT, 104) as FreeDocument_Date,
					RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, \'\'))) as pmUser_Name
				from
					EvnXml with (NOLOCK)
					inner join Evn with (NOLOCK) on Evn.Evn_id = EvnXml.Evn_id
					inner join EvnClass with (NOLOCK) on EvnClass.EvnClass_id = Evn.EvnClass_id
					left join pmUserCache with (NOLOCK) on pmUserCache.pmUser_id = EvnXml.pmUser_insID
				where
					EvnXml.EvnXml_id = :EvnXml_id
				order by EvnXml.EvnXml_insDT
			';
		} else if (isset($data['FreeDocument_pid']) && $data['FreeDocument_pid'] > 0) {
			$queryParams = array('Evn_id' => $data['FreeDocument_pid']);
			$query = '
				select
					Evn.Evn_id as FreeDocument_pid,
					EvnClass.EvnClass_id,
					EvnClass.EvnClass_SysNick,
					EvnXml.EvnXml_id as FreeDocument_id,
					EvnXml.EvnXml_Name as FreeDocument_Name,
					EvnXml.EvnXml_Data as FreeDocument_Data,
					convert(varchar, EvnXml.EvnXml_insDT, 104) as FreeDocument_Date,
					RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, \'\'))) as pmUser_Name
				from
					EvnXml with (NOLOCK)
					inner join Evn with (NOLOCK) on Evn.Evn_id = EvnXml.Evn_id
					inner join EvnClass with (NOLOCK) on EvnClass.EvnClass_id = Evn.EvnClass_id
					left join pmUserCache with (NOLOCK) on pmUserCache.pmUser_id = EvnXml.pmUser_insID
				where
					EvnXml.Evn_id = :Evn_id
					and EvnXml.XmlType_id = 2
				order by EvnXml.EvnXml_insDT
			';
		}
		$result = $this->db->query($query, $queryParams);
		
		if ( is_object($result) ) {
			$res = $result->result('array');
			foreach($res as $key => $data) {
				$res[$key]['FreeDocument_Data'] = htmlspecialchars_decode($res[$key]['FreeDocument_Data']);
			}
			return $res;
		} else {
			return false;
		}
		//return array([0] => array());
	}

	/**
	 * Получает список маркеров для указанной категории документов
	 * @param $data
	 * @return array|bool
	 */
	function getMarkerByEvnClass($data) {
		
		$region_filter = ($this->getRegionNick() !== 'msk') ? 'and FreeDocMarker_Name <> \'ДатаВремяДокумента\'' : '';
		
		$query = '
			select
				-- select
				FreeDocMarker_id as id,
				FreeDocMarker_Name as name,
				FreeDocMarker_Description as description,
				FreeDocMarker_TableAlias as alias,
				FreeDocMarker_Field as field,
				FreeDocMarker_Query as query,
				FreeDocMarker_IsTableValue as is_table,
				FreeDocMarker_Options as options
				-- end select
			from
				-- from
				EvnClass EvnClass0 with (nolock)
				left join EvnClass EvnClass1 with (NOLOCK) on EvnClass1.EvnClass_id = EvnClass0.EvnClass_pid
				left join EvnClass EvnClass2 with (NOLOCK) on EvnClass2.EvnClass_id = EvnClass1.EvnClass_pid
				left join EvnClass EvnClass3 with (NOLOCK) on EvnClass3.EvnClass_id = EvnClass2.EvnClass_pid
				left join EvnClass EvnClass4 with (NOLOCK) on EvnClass4.EvnClass_id = EvnClass3.EvnClass_pid
				left join EvnClass EvnClass5 with (NOLOCK) on EvnClass5.EvnClass_id = EvnClass4.EvnClass_pid
				left join EvnClass EvnClass6 with (NOLOCK) on EvnClass6.EvnClass_id = EvnClass5.EvnClass_pid
				inner join FreeDocMarker with (nolock) on FreeDocMarker.EvnClass_id in (
					EvnClass0.EvnClass_id,
					EvnClass1.EvnClass_id,
					EvnClass2.EvnClass_id,
					EvnClass3.EvnClass_id,
					EvnClass4.EvnClass_id,
					EvnClass5.EvnClass_id,
					EvnClass6.EvnClass_id
				)
				-- end from
			where
				-- where
				EvnClass0.EvnClass_id = :EvnClass_id ' . $region_filter . '
				-- end where
			order by
				-- order by
				FreeDocMarker_Name
				-- end order by
		';

		$queryParams = array('EvnClass_id' => $data['EvnClass_id']);

		if ( $data['start'] >= 0 && $data['limit'] >= 0 ) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			$result = $this->db->query($limit_query, $queryParams);
		}
		else {
			$result = $this->db->query($query, $queryParams);
		}

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( is_array($res) ) {
				if ( $data['start'] == 0 && count($res) < $data['limit'] ) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				}
				else {
					$response['data'] = $res;
					$get_count_query = getCountSQLPH($query);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) ) {
						$count = $get_count_result->result('array');
						$response['totalCount'] = $count[0]['cnt'];
					}
					else {
						return false;
					}
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}

		return $response;
	}

	/**
	 * Получает список маркеров для указанного документа
	 * @param $evn_id
	 * @return array|bool
	 */
	function getMarkersDataByEvn($evn_id) {
		$query = '
			select
				FreeDocMarker_id,
				FreeDocMarker_Name,
				FreeDocMarker_Description,
				FreeDocMarker_TableAlias,
				FreeDocMarker_Field,
				FreeDocMarker_Query,
				FreeDocMarker_IsTableValue,
				FreeDocMarker_Options
				,Evn.Evn_id
				,Evn.Evn_pid
				,Evn.Evn_rid
				,EvnClass0.EvnClass_SysNick
				,EvnClass0.EvnClass_id as EvnClass_id0
				,EvnClass1.EvnClass_id as EvnClass_id1
				,EvnClass2.EvnClass_id as EvnClass_id2
				,EvnClass3.EvnClass_id as EvnClass_id3
				,EvnClass4.EvnClass_id as EvnClass_id4
				,EvnClass5.EvnClass_id as EvnClass_id5
				,EvnClass6.EvnClass_id as EvnClass_id6
			from
				Evn with (nolock)
				inner join EvnClass EvnClass0 with (nolock) on Evn.EvnClass_id = EvnClass0.EvnClass_id
				left join EvnClass EvnClass1 with (NOLOCK) on EvnClass1.EvnClass_id = EvnClass0.EvnClass_pid
				left join EvnClass EvnClass2 with (NOLOCK) on EvnClass2.EvnClass_id = EvnClass1.EvnClass_pid
				left join EvnClass EvnClass3 with (NOLOCK) on EvnClass3.EvnClass_id = EvnClass2.EvnClass_pid
				left join EvnClass EvnClass4 with (NOLOCK) on EvnClass4.EvnClass_id = EvnClass3.EvnClass_pid
				left join EvnClass EvnClass5 with (NOLOCK) on EvnClass5.EvnClass_id = EvnClass4.EvnClass_pid
				left join EvnClass EvnClass6 with (NOLOCK) on EvnClass6.EvnClass_id = EvnClass5.EvnClass_pid
				inner join FreeDocMarker with (nolock) on FreeDocMarker.EvnClass_id in (
					EvnClass0.EvnClass_id,
					EvnClass1.EvnClass_id,
					EvnClass2.EvnClass_id,
					EvnClass3.EvnClass_id,
					EvnClass4.EvnClass_id,
					EvnClass5.EvnClass_id,
					EvnClass6.EvnClass_id
				)
			where
				Evn.Evn_id = :Evn_id
		';

		$result = $this->db->query($query, array('Evn_id'=>$evn_id));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * получает список маркеров для uslugapar
	 */
	function getMarkersDataForUslugaPar($evn_id) {
		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$res = $this->lis->GET('EvnUsluga/EvnParams', [
				'Evn_id' => $evn_id
			], 'single');
		} else {
			$this->load->model('EvnUsluga_model');
			$res = $this->EvnUsluga_model->getEvnParams([
				'Evn_id' => $evn_id
			]);
		}
		if(!$this->isSuccessful($res)) {
			$res['Evn_id'] = $evn_id;
			$res['Evn_pid'] = null;
			$res['Evn_rid'] = null;
			$res['EvnClass_id'] = 47;
		}

		$query = '
			select
				FreeDocMarker_id,
				FreeDocMarker_Name,
				FreeDocMarker_Description,
				FreeDocMarker_TableAlias,
				FreeDocMarker_Field,
				FreeDocMarker_Query,
				FreeDocMarker_IsTableValue,
				FreeDocMarker_Options,
				:Evn_id as Evn_id,
				:Evn_pid as Evn_pid,
				:Evn_rid as Evn_rid,
				EvnClass0.EvnClass_SysNick,
				EvnClass0.EvnClass_id as EvnClass_id0,
				EvnClass1.EvnClass_id as EvnClass_id1,
				EvnClass2.EvnClass_id as EvnClass_id2,
				EvnClass3.EvnClass_id as EvnClass_id3
			from
				EvnClass EvnClass0 with (nolock)
				left join EvnClass EvnClass1 with (NOLOCK) on EvnClass1.EvnClass_id = EvnClass0.EvnClass_pid
				left join EvnClass EvnClass2 with (NOLOCK) on EvnClass2.EvnClass_id = EvnClass1.EvnClass_pid
				left join EvnClass EvnClass3 with (NOLOCK) on EvnClass3.EvnClass_id = EvnClass2.EvnClass_pid
				left join EvnClass EvnClass4 with (NOLOCK) on EvnClass4.EvnClass_id = EvnClass3.EvnClass_pid
				left join EvnClass EvnClass5 with (NOLOCK) on EvnClass5.EvnClass_id = EvnClass4.EvnClass_pid
				left join EvnClass EvnClass6 with (NOLOCK) on EvnClass6.EvnClass_id = EvnClass5.EvnClass_pid
				inner join FreeDocMarker with (nolock) on FreeDocMarker.EvnClass_id in (
					EvnClass0.EvnClass_id,
					EvnClass1.EvnClass_id,
					EvnClass2.EvnClass_id,
					EvnClass3.EvnClass_id,
					EvnClass4.EvnClass_id,
					EvnClass5.EvnClass_id,
					EvnClass6.EvnClass_id
				)
			where
				EvnClass0.EvnClass_id = :EvnClass_id
		';

		$resp = $this->queryResult($query, $res);

		return $resp;
	}

	/**
	 * получает список маркеров для человека
	 * @param $evn_id
	 * @return array|bool
	 */
	function getMarkersDataForPerson() {
		$query = '
			select
				FreeDocMarker_id,
				FreeDocMarker_Name,
				FreeDocMarker_Description,
				FreeDocMarker_TableAlias,
				FreeDocMarker_Field,
				FreeDocMarker_Query,
				FreeDocMarker_IsTableValue,
				FreeDocMarker_Options
			from
				FreeDocMarker with (nolock)
			where
				EvnClass_id = 1
		';

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Читает список связей
	 * @param $alias_list
	 * @param $evnclass_id
	 * @return bool
	 */
	function getChainSections($alias_list, $evnclass_id) {
		$query = "
			select
				FreeDocRelationship_id as id,
				FreeDocRelationship_AliasName as alias,
				FreeDocRelationship_AliasTable as alias_table,
				FreeDocRelationship_AliasQuery as alias_query,
				FreeDocRelationship_LinkedAlias as linked_alias,
				FreeDocRelationship_LinkDescription as link_description
			from
				FreeDocRelationship with(nolock)
			where
				EvnClass_id ".(is_array($evnclass_id) ? " in ('".join("','", $evnclass_id)."')" : " = ".$evnclass_id)."
				and FreeDocRelationship_AliasName in ('".join("','", $alias_list)."')
		";

		$result = $this->db->query($query, array());

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Ряд заранее определенных подстановок
	 * @param $text
	 * @param $evn_data
	 * @return mixed
	 */
	function replaceSpecialValues($text, $evn_data) {
		$res = $text;
		if (isset($evn_data['evn_table']))
			$res = preg_replace("/{roottable_name}/i", $evn_data['evn_table'], $res); //подстановка имени корневой таблицы
		if (isset($evn_data['evn_id']))
			$res = preg_replace("/{evn_id}/i", $evn_data['evn_id'], $res); //подстановка идентификатора текущего события
		if (isset($evn_data['evnxml_id']))
			$res = preg_replace("/{EvnXml_id}/i", $evn_data['evnxml_id'], $res); //подстановка идентификатора текущего документа
		return $res;
	}

	/**
	 * Сборка готового запроса для извлечения данных и его выполнение
	 * @param $markers
	 * @param $evn_data
	 * @param array $options
	 * @return array
	 */
	function buildAndExeDataQuery($markers, $evn_data, $options = array()) {
		$response = array();

		if ($this->usePostgreLis && !empty($options['Lis'])) {
			$this->load->swapi('lis');
			$res = $this->lis->GET('EvnUsluga/DataForResults', $evn_data, 'single');
			if (!$this->isSuccessful($res)) {
				$response['query'] = $this->buildDataQuery($markers, $evn_data, $options);
				$response['result'] = $this->getFirstRowFromQuery($response['query']);
			}

			$query = "
				select
					mp.Person_Fin as MarkerData_137
				from
					v_EvnXml xml with (nolock)
					left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = :MedPersonal_aid and mp.Lpu_id = :Lpu_aid
				where
					xml.EvnXml_id = :Evn_id
			";

			$resp = $this->queryResult($query, $res);
			if (!empty($resp[0]))
				$res = array_merge($res, $resp[0]);

			$response['result'] = $res;

		} else {
			$response['query'] = $this->buildDataQuery($markers, $evn_data, $options);
			if ($response['query']) {
				$result = $this->db->query($response['query'], array());
				if ( is_object($result) ) {
					$res = $result->result('array');
					if (isset($res[0])) {
						$response['result'] = $res[0];
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Сборка готового запроса для извлечения данных
	 * @param $markers
	 * @param $evn_data
	 * @param array $options
	 * @return bool|mixed
	 */
	function buildDataQuery($markers, $evn_data, $options = array()) {
		$f_br = '';
		$f_tab = '';
		if (isset($options['enable_html_format'])) {
			$f_br = '<br/>';
			$f_tab = '&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		$select_section = array();
		$from_section = '';
		$where_section = '';		
		$used_alias = array();		

		if ($evn_data['evn_table'] == 'EvnDirection') {
			$from_section .= 'v_EvnDirection_all as RootTable with (nolock) ';
		} elseif ($evn_data['evn_table'] == 'Person') {
			$from_section .= 'v_PersonState as RootTable with (nolock) ';
		} else {
			$from_section .= 'v_'.$evn_data['evn_table'].' as RootTable with (nolock) ';
		}

		$where_section .= 'RootTable.'.$evn_data['evn_table'].'_id = '.(isset($evn_data['evn_id']) ? $evn_data['evn_id'] : '{evn_id}');
		
		foreach($markers as $marker_id => $marker) {
			if (!$marker['error'] && !$marker['is_table']) {
				//конструируем фрагмент области select
				$select_part = '';
				if (!empty($marker['field'])) {
					$select_part = (!empty($marker['alias']) ? $marker['alias'] : 'RootTable').'.'.$marker['field'].' as MarkerData_'.$marker_id;
				} else if (!empty($marker['query'])) {
					$select_part = '('.$marker['query'].') as MarkerData_'.$marker_id;
				}				
				if (!empty($select_part)) {
					//добавляем фрагмент области select в список
					$select_section[] = $f_tab.$select_part;
				}
				//дополняем область from необходимыми фрагментами
				foreach(array_reverse($marker['table_chain']) as $ch) {
					if (!in_array($ch['alias'], $used_alias)) {
						if (!empty($ch['alias_query']))
							$from_section .= $f_br.$f_tab.'outer apply ('.$ch['alias_query'].') as '.$ch['alias'].' ';
						else
							$from_section .= $f_br.$f_tab.'left join '.$ch['alias_table'].' as '.$ch['alias'].' with (nolock) on '.$ch['link_description'].' ';
						$used_alias[] = $ch['alias'];
					}
				}
			}
		}
		if (count($select_section) > 0) {
			$query = 'select '.$f_br.join(', '.$f_br, $select_section).$f_br.' from '.$f_br.$f_tab.$from_section.$f_br.' where '.$f_br.$f_tab.$where_section;		
			//дополнительная обработка
			$query = $this->replaceSpecialValues($query, $evn_data);
			//var_export($query);
			return $query;
		} else {
			if (isset($options['get_query_section'])) {
				if ($options['get_query_section'] == 'from' && !empty($from_section)) {
					return $this->replaceSpecialValues($from_section, $evn_data);
				}
			}
			return false;
		}
	}

	/**
	 * Получение данных по маркерам
	 * @param $markers
	 * @param $evn_data
	 * @return array|bool
	 */
	function getMarkerData($markers, $evn_data) {
		$data = array();
		$table_data = array();
		
		//для обычных маркеров формируем гло
		$query = $this->buildDataQuery($markers, $evn_data);
		if ($query) {
			$result = $this->db->query($query, array());
			if ( is_object($result) ) {
				$res = $result->result('array');
				$data = $res[0];
			}
		}
		//для табличных маркеров используем отдельную функцию
		$table_data = $this->getMarkerTableData($markers, $evn_data);
		
		if (count($data) > 0 || count($table_data) > 0) {
			return array_merge($data, $table_data);
		} else {
			return false;
		}
	}

	/**
	 * Получение данных по табличным маркерам
	 * @param $markers
	 * @param $evn_data
	 * @return array
	 */
	function getMarkerTableData($markers, $evn_data) {
		$data = array();
		foreach($markers as $marker_id => $marker) {
			if (!$marker['error'] && $marker['is_table'] && !empty($marker['query'])) {
				$query = $this->replaceSpecialValues($marker['query'], $evn_data);
				//var_dump($query);
				$result = $this->db->query($query, array());
				if (is_object($result)) {
					$res = $result->result('array');
					$html = $this->renderTable($res, $marker['options']);
					//var_dump($html);
					if (!empty($html)) {
						$data['MarkerData_'.$marker_id] = $html;
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Сбор отладочной информации из подготовленного массива маркеров
	 * @param $markers
	 * @param $evn_data
	 * @return array
	 */
	function collectDebugInformation($markers, $evn_data) {
		$result = array('total_query' => '', 'query_section_from' => '', 'links_array' => array());		
		$result['total_query'] .= $this->buildDataQuery($markers, $evn_data, array('enable_html_format' => true)).' ';
		$result['query_section_from'] .= $this->buildDataQuery($markers, $evn_data, array('enable_html_format' => true, 'get_query_section' => 'from')).' ';
		foreach($markers as $marker_id => $marker) {
			if (!$marker['error'] && $marker['is_table'] && !empty($marker['query'])) {
				$result['total_query'] .= $this->replaceSpecialValues($marker['query'], $evn_data).' ';
			}
			$links_str = '';
			if ($marker_id > 0) {
				$links_str .= $marker['original_name'].' -> ';//.' ('.(!empty($marker['alias']) ? $marker['alias'] : 'Корневая таблица').') -> ';
			}
			foreach($marker['table_chain'] as $section) {
				$links_str .= $section['alias'].' ('.(!empty($section['alias_table']) ? 'Таблица: '.$section['alias_table'] : (!empty($section['alias_query']) ? 'Запрос' : 'Пусто')).') -> ';
			}
			$result['links_array'][$marker_id] = $links_str.($marker['error'] ? 'Обрыв (отсутствует звено "'.(count($marker['table_chain'])>0 ?  $marker['table_chain'][count($marker['table_chain'])-1]['linked_alias'] : $marker['alias']).'")' : 'Корневая таблица ('.$evn_data['evn_table'].')');
		}
		return $result;
	}

	/**
	 * Формирование таблицы по данным табличного маркера
	 *
	 * Преобразование данных из базы и описания шапки, в html-код таблицы
	 * @param $data
	 * @param string $header_description
	 * @return string
	 */
	function renderTable($data, $header_description = "") {
		$header = '';
		$html = '';
		$col_cnt = 0;
		$td_style = 'border: 1px solid black; padding: 5px;';
		$is_list = (stripos($header_description, "is_list") !== false);
		
		if ($is_list) { //списочный подвид табличных маркеров рендерится отдельно
			if (is_array($data) && count($data) > 0) {
				$_arr = array();
				foreach($data as $row) {
					$vl = current($row);
					if (!empty($vl)) {
						$_arr[] = $vl;
					}
				}
				$html .= join('<br/>', $_arr);
			}
		} else {		
			if (is_array($data) && count($data) > 0) { //формирование тела таблицы
				$col_cnt = count($data[0]);
				foreach($data as $row) {
					$row_text  = '';
					foreach($row as $cell) {
						$row_text .= '<td style="'.$td_style.'">'.(!empty($cell) ? $cell : '&nbsp;').'</td>';
					}
					if (!empty($row_text)) {
						$html .= '<tr>'.$row_text.'</tr>';
					}
				}
			}
			
			if (!empty($html) && !empty($header_description)) { //формирование заголовка таблицы
				$header = $this->renderTableHeader($header_description, $col_cnt = 1);
			}		
			if (!empty($html)) {
				$html = '<table style="border-collapse: collapse;">'.$header.$html.'</table>';
			}
		}	
			
		return $html;
	}

	/**
	 * Преобразование описания шапки в html-код таблицы
	 * @param $header_description
	 * @param int $column_count
	 * @return string
	 */
	function renderTableHeader($header_description, $column_count = 1) {
		$header = '';
		$td_style = 'border: 1px solid black; padding: 5px;';
		if (strpos($header_description, '|') === false) {
			$header = '<tr><th style="'.$td_style.'" colspan="'.$column_count.'">'.$header_description.'</th></tr>';
		} else {
			$h_array = array();
			$tmp_arr = explode('||', $header_description);
			foreach($tmp_arr as $tmp_str) {
				$h_array[] = explode('|', $tmp_str);
			}
			//преобразование заголовков для соединения ячеек с одинаковым содержимым
			for($i = 0; $i < count($h_array); $i++) {
				for($j = 0; $j < count($h_array[$i]); $j++) {
					$h_array[$i][$j] = array(
						'text' => $h_array[$i][$j],
						'row' => 1,
						'col' => 1
					);
				}
			}
			//преобразование по строкам	
			for($i = 0; $i < count($h_array); $i++) {
				$txt = null;
				$pos = null;
				$cnt = 1;
				for($j = 0; $j < count($h_array[$i]); $j++) {
					if ($h_array[$i][$j] != $txt) {
						if ($txt != null) {
							$h_array[$i][$pos]['col'] = $cnt;
							$cnt = 1;
						}
						$txt = $h_array[$i][$j];
						$pos = $j;
					} else {
						$h_array[$i][$j]['col'] = 0;
						$cnt++;
					}
				}
				$h_array[$i][$pos]['col'] = $cnt;
			}
			//преобразование по столбцам
			for($j = 0; $j < count($h_array[0]); $j++) {
				$txt = null;
				$pos = null;
				$cnt = 1;
				for($i = 0; $i < count($h_array); $i++) {
					if ($h_array[$i][$j] != $txt) {
						if ($txt != null) {
							$h_array[$pos][$j]['row'] = $cnt;
							$cnt = 1;
						}
						$txt = $h_array[$i][$j];
						$pos = $i;
					} else {
						$h_array[$i][$j]['row'] = 0;
						$cnt++;
					}
				}
				$h_array[$pos][$j]['row'] = $cnt;
			}
				
			foreach($h_array as $row) {
				$row_text  = '';
				foreach($row as $cell) if ($cell['row'] > 0 && $cell['col'] > 0) {
					$row_text .= '<th'.($cell['row'] > 1 ? ' rowspan="'.$cell['row'].'"' : '').($cell['col'] > 1 ? ' colspan="'.$cell['col'].'"' : '').' style="'.$td_style.'">'.(!empty($cell['text']) ? $cell['text'] : '&nbsp;').'</th>';
				}
				if (!empty($row_text)) {
					$header .= '<tr>'.$row_text.'</tr>';
				}
			}
		}
		return $header;
	}

	/**
	 * Получение необходимой информации о событии исходя из его идентификатора
	 * @param $evn_id
	 * @return array
	 */
	function getEvnData($evn_id) {
		$res = array('evn_id' => $evn_id, 'class_list' => array());
		$queryParams = array('Evn_id' => $evn_id);
		$query = "
			select
				EvnClass0.EvnClass_SysNick
				,EvnClass0.EvnClass_id as EvnClass_id0
				,EvnClass1.EvnClass_id as EvnClass_id1
				,EvnClass2.EvnClass_id as EvnClass_id2
				,EvnClass3.EvnClass_id as EvnClass_id3
				,EvnClass4.EvnClass_id as EvnClass_id4
				,EvnClass5.EvnClass_id as EvnClass_id5
				,EvnClass6.EvnClass_id as EvnClass_id6
			from
				Evn with (NOLOCK)
				inner join EvnClass EvnClass0 with (NOLOCK) on Evn.EvnClass_id = EvnClass0.EvnClass_id
				left join EvnClass EvnClass1 with (NOLOCK) on EvnClass1.EvnClass_id = EvnClass0.EvnClass_pid
				left join EvnClass EvnClass2 with (NOLOCK) on EvnClass2.EvnClass_id = EvnClass1.EvnClass_pid
				left join EvnClass EvnClass3 with (NOLOCK) on EvnClass3.EvnClass_id = EvnClass2.EvnClass_pid
				left join EvnClass EvnClass4 with (NOLOCK) on EvnClass4.EvnClass_id = EvnClass3.EvnClass_pid
				left join EvnClass EvnClass5 with (NOLOCK) on EvnClass5.EvnClass_id = EvnClass4.EvnClass_pid
				left join EvnClass EvnClass6 with (NOLOCK) on EvnClass6.EvnClass_id = EvnClass5.EvnClass_pid
			where
				Evn.Evn_id = :Evn_id
		";
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$result = $result->result('array');
			if (is_array($result) && count($result) > 0) {
				$res['evn_table'] = $result[0]['EvnClass_SysNick'];
				$res['evnclass_id'] = $result[0]['EvnClass_id0'];
				$next_id = 0;
				while(!empty($result[0]['EvnClass_id'.$next_id])) {
					$res['class_list'][] = $result[0]['EvnClass_id'.$next_id];
					$next_id ++;
				}
			}
		}
		return $res;
	}

	/**
	 * Получение необходимой информации о классе события исходя из его идентификатора
	 * @param $evnclass_id
	 * @return array
	 */
	function getEvnClassData($evnclass_id) {
		$res = array('evnclass_id' => $evnclass_id, 'class_list' => array());
		$queryParams = array('EvnClass_id' => $evnclass_id);
		$query = "
			select
				EvnClass0.EvnClass_SysNick
				,EvnClass0.EvnClass_id as EvnClass_id0
				,EvnClass1.EvnClass_id as EvnClass_id1
				,EvnClass2.EvnClass_id as EvnClass_id2
				,EvnClass3.EvnClass_id as EvnClass_id3
				,EvnClass4.EvnClass_id as EvnClass_id4
				,EvnClass5.EvnClass_id as EvnClass_id5
				,EvnClass6.EvnClass_id as EvnClass_id6
			from
				EvnClass EvnClass0 with (NOLOCK)
				left join EvnClass EvnClass1 with (NOLOCK) on EvnClass1.EvnClass_id = EvnClass0.EvnClass_pid
				left join EvnClass EvnClass2 with (NOLOCK) on EvnClass2.EvnClass_id = EvnClass1.EvnClass_pid
				left join EvnClass EvnClass3 with (NOLOCK) on EvnClass3.EvnClass_id = EvnClass2.EvnClass_pid
				left join EvnClass EvnClass4 with (NOLOCK) on EvnClass4.EvnClass_id = EvnClass3.EvnClass_pid
				left join EvnClass EvnClass5 with (NOLOCK) on EvnClass5.EvnClass_id = EvnClass4.EvnClass_pid
				left join EvnClass EvnClass6 with (NOLOCK) on EvnClass6.EvnClass_id = EvnClass5.EvnClass_pid
			where
				EvnClass0.EvnClass_id = :EvnClass_id
		";
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$result = $result->result('array');
			if (is_array($result) && count($result) > 0) {
				$res['evn_table'] = $result[0]['EvnClass_SysNick'];
				$next_id = 0;
				while(!empty($result[0]['EvnClass_id'.$next_id])) {
					$res['class_list'][] = $result[0]['EvnClass_id'.$next_id];
					$next_id ++;
				}
			}
		}
		return $res;
	}

	/**
	 * Возвращает список родительских классов
	 */
	function getEvnClassListByChild($EvnClass_id) {
		$res = array();
		$next_id = $EvnClass_id;
		while(!empty($next_id) && $next_id > 0) {
			$queryParams = array('EvnClass_id' => $next_id);
			$query = "
				select
					EvnClass_id,
					EvnClass_pid
				from
					EvnClass with(nolock)
				where
					EvnClass_id = :EvnClass_id
			";
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$result = $result->result('array');
				$result = $result[0];
				$res[] = $result['EvnClass_id'];
				$next_id = $result['EvnClass_pid'];
			} else {
				$next_id = 0;
			}
		}
		
		return $res;
	}

	/**
	 * Удаляет документ
	 *
	 * Этот метод надо будет убрать отсюда, т.к. он есть в EvnXmlBase_model
	 * @param $data
	 * @return bool
	 */
	function deleteFreeDocument($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnXml_del
				@EvnXml_id = :id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'id' => $data['id']
		));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загружает список всех категорий документов
	 *
	 * Устаревший метод, надо будет убрать отсюда
	 * @return bool
	 */
	function loadEvnClassList() {
		$query = "
			select
				EvnClass_id,
				EvnClass_Name,
				EvnClass_SysNick
			from
				EvnClass with(nolock)
			order by
				EvnClass_id
		";

		$result = $this->db->query($query, array());

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Читает список маркеров для грида
	 * @param $data
	 * @return array|bool
	 */
	function loadMarkerListByFilters($data) {
		$filter = '';
		$params = array();
		
		if ((isset($data['EvnClass_id'])) && ($data['EvnClass_id']>0)) {
			$filter = $filter." and fdm.EvnClass_id = :EvnClass_id";
			$params['EvnClass_id'] = $data['EvnClass_id'];
		}
		if ((isset($data['FreeDocMarker_Name'])) && !empty($data['FreeDocMarker_Name'])) {
			$filter = $filter." and fdm.FreeDocMarker_Name like :FreeDocMarker_Name";
			$params['FreeDocMarker_Name'] = '%'.$data['FreeDocMarker_Name'].'%';
		}
		if ((isset($data['FreeDocMarker_Description'])) && !empty($data['FreeDocMarker_Description'])) {
			$filter = $filter." and fdm.FreeDocMarker_Description like :FreeDocMarker_Description";
			$params['FreeDocMarker_Description'] = '%'.$data['FreeDocMarker_Description'].'%';
		}
		if ((isset($data['FreeDocMarker_TableAlias'])) && !empty($data['FreeDocMarker_TableAlias'])) {
			$filter = $filter." and fdm.FreeDocMarker_TableAlias like :FreeDocMarker_TableAlias";
			$params['FreeDocMarker_TableAlias'] = '%'.$data['FreeDocMarker_TableAlias'].'%';
		}
		
		
		$query = "
			select
				FreeDocMarker_id,
				convert(varchar(10), fdm.FreeDocMarker_insDT, 104) as FreeDocMarker_insDT,
				convert(varchar(10), fdm.FreeDocMarker_updDT, 104) as FreeDocMarker_updDT,
				(cast(fdm.EvnClass_id as varchar) + ' - ' + ec.EvnClass_Name) as EvnClass_Name,
				fdm.FreeDocMarker_Name,
				fdm.FreeDocMarker_Description,
				fdm.FreeDocMarker_TableAlias,
				(case when fdm.FreeDocMarker_Query is null then 'Нет' else 'Да' end) as FreeDocMarker_Query,
				isnull(yn_istable.YesNo_Name, 'Нет') as FreeDocMarker_IsTableValue,
				(case when fdm.FreeDocMarker_Options is null then 'Нет' else 'Да' end) as FreeDocMarker_Options
			from 
				FreeDocMarker fdm with(nolock)
				left join EvnClass ec with(nolock) on ec.EvnClass_id = fdm.EvnClass_id
				left join YesNo yn_istable with(nolock) on yn_istable.YesNo_id = fdm.FreeDocMarker_IsTableValue
			where (1 = 1)
				{$filter}
			order by 
				fdm.FreeDocMarker_insDT desc
		";
		//echo getDebugSql($query, $params); exit;
			
		$result = $this->db->query(getLimitSQL($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQL($query), $params);
		
		if (is_object($result_count)) {
			$result_count = $result_count->result('array');
			$count = $result_count[0]['cnt'];
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
	 * Читает список связей для грида
	 * @param $data
	 * @return array|bool
	 */
	function loadRelationshipListByFilters($data) {
		$filter = '';
		$params = array();
		
		if ((isset($data['EvnClass_id'])) && ($data['EvnClass_id']>0)) {
			$filter = $filter." and fdr.EvnClass_id = :EvnClass_id";
			$params['EvnClass_id'] = $data['EvnClass_id'];
		}
		if ((isset($data['FreeDocRelationship_AliasName'])) && !empty($data['FreeDocRelationship_AliasName'])) {
			$filter = $filter." and fdr.FreeDocRelationship_AliasName like :FreeDocRelationship_AliasName";
			$params['FreeDocRelationship_AliasName'] = '%'.$data['FreeDocRelationship_AliasName'].'%';
		}
		if ((isset($data['FreeDocRelationship_AliasTable'])) && !empty($data['FreeDocRelationship_AliasTable'])) {
			$filter = $filter." and fdr.FreeDocRelationship_AliasTable like :FreeDocRelationship_AliasTable";
			$params['FreeDocRelationship_AliasTable'] = '%'.$data['FreeDocRelationship_AliasTable'].'%';
		}
		if ((isset($data['FreeDocRelationship_LinkedAlias'])) && !empty($data['FreeDocRelationship_LinkedAlias'])) {
			$filter = $filter." and fdr.FreeDocRelationship_LinkedAlias like :FreeDocRelationship_LinkedAlias";
			$params['FreeDocRelationship_LinkedAlias'] = '%'.$data['FreeDocRelationship_LinkedAlias'].'%';
		}
		
		
		$query = "
			select
				FreeDocRelationship_id,
				convert(varchar(10), fdr.FreeDocRelationship_insDT, 104) as FreeDocRelationship_insDT,
				convert(varchar(10), fdr.FreeDocRelationship_updDT, 104) as FreeDocRelationship_updDT,
				(cast(fdr.EvnClass_id as varchar) + ' - ' + ec.EvnClass_Name) as EvnClass_Name,
				fdr.FreeDocRelationship_AliasName,
				fdr.FreeDocRelationship_AliasTable,
				fdr.FreeDocRelationship_LinkedAlias,
				(case when fdr.FreeDocRelationship_AliasQuery is null then 'Нет' else 'Да' end) as FreeDocRelationship_AliasQuery
			from 
				FreeDocRelationship fdr with(nolock)
				left join EvnClass ec with(nolock) on ec.EvnClass_id = fdr.EvnClass_id
			where (1 = 1)
				{$filter}
			order by 
				fdr.FreeDocRelationship_insDT desc
		";
		//echo getDebugSql($query, $params); exit;
			
		$result = $this->db->query(getLimitSQL($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQL($query), $params);
		
		if (is_object($result_count)) {
			$result_count = $result_count->result('array');
			$count = $result_count[0]['cnt'];
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
	 * Читает маркер для формы редактирования
	 * @param $data
	 * @return bool
	 */
	function getFreeDocMarkerData($data) {
		$params = array(
			'FreeDocMarker_id' => $data['FreeDocMarker_id']
		);
		$query = "
			Select top 1
				fdm.FreeDocMarker_id,
				fdm.EvnClass_id,
				fdm.FreeDocMarker_Name,
				fdm.FreeDocMarker_TableAlias,
				fdm.FreeDocMarker_Field,
				fdm.FreeDocMarker_Query,
				fdm.FreeDocMarker_Description,
				fdm.FreeDocMarker_IsTableValue,
				fdm.FreeDocMarker_Options
			from
				FreeDocMarker fdm with (nolock)
			where
				fdm.FreeDocMarker_id = :FreeDocMarker_id
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Читает связь для формы редактирования
	 * @param $data
	 * @return bool
	 */
	function getFreeDocRelationshipData($data) {
		$params = array(
			'FreeDocRelationship_id' => $data['FreeDocRelationship_id']
		);
		if ($this->swmongocache->isEntry('FreeDocRelationship', $params)) {
			return $this->swmongocache->get('FreeDocRelationship', $params);
		}
		$query = "
			Select top 1
				fdr.FreeDocRelationship_id,
				fdr.EvnClass_id,
				fdr.FreeDocRelationship_AliasName,
				fdr.FreeDocRelationship_AliasTable,
				fdr.FreeDocRelationship_AliasQuery,
				fdr.FreeDocRelationship_LinkedAlias,
				fdr.FreeDocRelationship_LinkDescription
			from
				FreeDocRelationship fdr with (nolock)
			where
				fdr.FreeDocRelationship_id = :FreeDocRelationship_id
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка на дублирование маркера по имени
	 * @param $data
	 * @return bool
	 */
	function checkMarkerDouble($data) {
		// проверяем при добавлении и редактировании
		$params = array(
			'FreeDocMarker_Name' => $data['FreeDocMarker_Name']
		);
		$f = '';
		if (!empty($data['FreeDocMarker_id'])) {
			$f = ' and FreeDocMarker_id != :FreeDocMarker_id';
			$params['FreeDocMarker_id'] = $data['FreeDocMarker_id'];
		}
		$query = "
			Select
				FreeDocMarker_id,
				FreeDocMarker_Name
			from
				FreeDocMarker with (NOLOCK)
			where
				lower(FreeDocMarker_Name) = lower(:FreeDocMarker_Name)
				{$f}
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка на дублирование связи по имени
	 * @param $data
	 * @return bool
	 */
	function checkRelationshipDouble($data) {
		// проверяем при добавлении и редактировании
		$params = array(
			'FreeDocRelationship_AliasName' => $data['FreeDocRelationship_AliasName']
		);
		$f = '';
		if (!empty($data['FreeDocRelationship_id'])) {
			$f = ' and FreeDocRelationship_id != :FreeDocRelationship_id';
			$params['FreeDocRelationship_id'] = $data['FreeDocRelationship_id'];
		}
		$query = "
			Select
				FreeDocRelationship_id,
				FreeDocRelationship_AliasName
			from
				FreeDocRelationship with (NOLOCK)
			where
				lower(FreeDocRelationship_AliasName) = lower(:FreeDocRelationship_AliasName)
				{$f}
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение маркера
	 * @param $data
	 * @return array|bool
	 */
	function saveFreeDocMarker($data) {
		$result = $this->allowEdit($data);
		if(is_array($result))
		{
			return $result;
		}
		if ($data['FreeDocMarker_id'] > 0) {
			$proc = 'p_FreeDocMarker_upd';
		} else {
			$proc = 'p_FreeDocMarker_ins';
			$data['FreeDocMarker_id'] = null;
		}
		$params = array (
			'FreeDocMarker_id' => $data['FreeDocMarker_id'],
			'EvnClass_id' => $data['EvnClass_id'],
			'FreeDocMarker_Name' => $data['FreeDocMarker_Name'],
			'FreeDocMarker_TableAlias' => $data['FreeDocMarker_TableAlias'],
			'FreeDocMarker_Field' => $data['FreeDocMarker_Field'],
			'FreeDocMarker_Query' => $data['FreeDocMarker_Query'],
			'FreeDocMarker_Description' => $data['FreeDocMarker_Description'],
			'FreeDocMarker_IsTableValue' => $data['FreeDocMarker_IsTableValue'],
			'FreeDocMarker_Options' => $data['FreeDocMarker_Options'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@FreeDocMarker_id bigint = :FreeDocMarker_id;
				
			exec " .$proc. "
				@FreeDocMarker_id = @FreeDocMarker_id output,
				@EvnClass_id = :EvnClass_id,
				@FreeDocMarker_Name = :FreeDocMarker_Name,
				@FreeDocMarker_TableAlias = :FreeDocMarker_TableAlias,
				@FreeDocMarker_Field = :FreeDocMarker_Field,
				@FreeDocMarker_Query = :FreeDocMarker_Query,
				@FreeDocMarker_Description = :FreeDocMarker_Description,
				@FreeDocMarker_IsTableValue = :FreeDocMarker_IsTableValue,
				@FreeDocMarker_Options = :FreeDocMarker_Options,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @FreeDocMarker_id as FreeDocMarker_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		// echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение связи
	 * @param $data
	 * @return bool
	 */
	function saveFreeDocRelationship($data) {
		if ($data['FreeDocRelationship_id'] > 0) {
			$proc = 'p_FreeDocRelationship_upd';
		} else {
			$proc = 'p_FreeDocRelationship_ins';
			$data['FreeDocRelationship_id'] = null;
		}
		$params = array (
			'FreeDocRelationship_id' => $data['FreeDocRelationship_id'],
			'EvnClass_id' => $data['EvnClass_id'],
			'FreeDocRelationship_AliasName' => $data['FreeDocRelationship_AliasName'],
			'FreeDocRelationship_AliasTable' => $data['FreeDocRelationship_AliasTable'],
			'FreeDocRelationship_AliasQuery' => $data['FreeDocRelationship_AliasQuery'],
			'FreeDocRelationship_LinkedAlias' => $data['FreeDocRelationship_LinkedAlias'],
			'FreeDocRelationship_LinkDescription' => $data['FreeDocRelationship_LinkDescription'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@FreeDocRelationship_id bigint = :FreeDocRelationship_id;
				
			exec " .$proc. "
				@FreeDocRelationship_id = @FreeDocRelationship_id output,
				@EvnClass_id = :EvnClass_id,
				@FreeDocRelationship_AliasName = :FreeDocRelationship_AliasName,
				@FreeDocRelationship_AliasTable = :FreeDocRelationship_AliasTable,
				@FreeDocRelationship_AliasQuery = :FreeDocRelationship_AliasQuery,
				@FreeDocRelationship_LinkedAlias = :FreeDocRelationship_LinkedAlias,
				@FreeDocRelationship_LinkDescription = :FreeDocRelationship_LinkDescription,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @FreeDocRelationship_id as FreeDocRelationship_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			// Кэширование 
			$res = $result->result('array');
			if (empty($params['FreeDocRelationship_id']) && is_array($res)) {
				$params['FreeDocRelationship_id'] = $res[0]['FreeDocRelationship_id'];
			}
			$this->swmongocache->save('FreeDocRelationship', $params);
			return $res;
		} else {
			return false;
		}
	}

	function getDynamicsOfTestResultsFormData($data) {
		$filter = '';
		$params = array();
		
		if ((isset($data['EvnClass_id'])) && ($data['EvnClass_id']>0)) {
			$filter = $filter." and fdr.EvnClass_id = :EvnClass_id";
			$params['EvnClass_id'] = $data['EvnClass_id'];
		}
		if ((isset($data['FreeDocRelationship_AliasName'])) && !empty($data['FreeDocRelationship_AliasName'])) {
			$filter = $filter." and fdr.FreeDocRelationship_AliasName like :FreeDocRelationship_AliasName";
			$params['FreeDocRelationship_AliasName'] = '%'.$data['FreeDocRelationship_AliasName'].'%';
		}
		if ((isset($data['FreeDocRelationship_AliasTable'])) && !empty($data['FreeDocRelationship_AliasTable'])) {
			$filter = $filter." and fdr.FreeDocRelationship_AliasTable like :FreeDocRelationship_AliasTable";
			$params['FreeDocRelationship_AliasTable'] = '%'.$data['FreeDocRelationship_AliasTable'].'%';
		}
		if ((isset($data['FreeDocRelationship_LinkedAlias'])) && !empty($data['FreeDocRelationship_LinkedAlias'])) {
			$filter = $filter." and fdr.FreeDocRelationship_LinkedAlias like :FreeDocRelationship_LinkedAlias";
			$params['FreeDocRelationship_LinkedAlias'] = '%'.$data['FreeDocRelationship_LinkedAlias'].'%';
		}
		
		$query = "
		SELECT TOP 1
				convert(varchar(10), EDH.EvnDirectionHistologic_LawDocumentDate, 104) as EvnDirectionHistologic_LawDocumentDate,
			EDH.Org_sid,
			EDH.EvnDirectionHistologic_Descr,
			EDH.EvnDirectionHistologic_id,
			EDH.EvnDirectionHistologic_pid,
			EHP.EvnHistologicProto_id,
			convert(varchar(10), EDH.EvnDirectionHistologic_setDate, 104) as EvnDirectionHistologic_setDate,
			EDH.EvnDirectionHistologic_setTime,
			convert(varchar(10), EDH.EvnDirectionHistologic_didDate, 104) as EvnDirectionHistologic_didDate,
			EDH.Person_id,
			EDH.Server_id,
			EDH.PersonEvn_id,
			EDH.EvnDirectionHistologic_Ser,
			EDH.EvnDirectionHistologic_Num,
			EDH.EvnDirectionHistologic_IsUrgent,
			EDH.LpuSection_did,
			EDH.Lpu_id,
			EDH.Lpu_sid,
			EDH.EvnDirectionHistologic_NumCard,
			EDH.HistologicMaterial_id,
			EDH.BiopsyOrder_id,
			EDH.UslugaComplex_id as EDHUslugaComplex_id,
			convert(varchar(10), EDH.EvnDirectionHistologic_BiopsyDT, 104) as  EvnDirectionHistologic_BiopsyDate,
			EDH.EvnDirectionHistologic_BiopsyNum,
			EDH.EvnDirectionHistologic_SpecimenSaint,
			EDH.EvnDirectionHistologic_Operation,
			EDH.EvnDirectionHistologic_ObjectCount,
			EDH.EvnDirectionHistologic_PredOperTreat,
			EDH.EvnDirectionHistologic_ClinicalData,
			EDH.EvnDirectionHistologic_ClinicalDiag,
			EDH.EvnDirectionHistologic_MedPersonalFIO,
			EDH.EvnDirectionHistologic_LpuSectionName,
			EDH.EvnPS_id,
			EDH.Diag_id,
			EDH.BiopsyReceive_id,
			EDH.EvnDirectionHistologic_IsPlaceSolFormalin,
			BSTL.BiopsyStudyType_ids,
			RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name
		FROM
			v_EvnDirectionHistologic EDH with (nolock)
			left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EDH.EvnDirectionHistologic_IsBad
			left join pmUserCache with (nolock) on pmUserCache.pmUser_id = EDH.pmUser_pid
			outer apply (
				select top 1
					EvnHistologicProto_id
				from
					v_EvnHistologicProto with (nolock)
				where
					EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
			) EHP
			outer apply (
				SELECT STUFF(
				(
					select
						',' + cast(BiopsyStudyType_id as varchar)
					from
						v_BiopsyStudyTypeLink (nolock)
					where
						EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
					FOR XML PATH ('')
				), 1, 1, '') as BiopsyStudyType_ids
			) BSTL
		WHERE (1 = 1)
			and EDH.EvnDirectionHistologic_id = :EvnDirectionHistologic_id
			and (EDH.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
	";

		$result = $this->db->query($query, array(
			'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id'],
			'Lpu_id' => $data['Lpu_id']
		));	
		$result_count = $this->db->query(getCountSQL($query), $params);
		
		if (is_object($result_count)) {
			$result_count = $result_count->result('array');
			$count = $result_count[0]['cnt'];
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
}
?>