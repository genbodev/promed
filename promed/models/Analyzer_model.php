<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Анализатор
 *
 * @package
 * @access	   public
 * @copyright	Copyright (c) 2010-2011 Swan Ltd.
 * @author	   gabdushev
 * @version
 */
class Analyzer_model extends swModel {
	private $Analyzer_id;//Идентификатор
	private $Analyzer_Name;//Наименование анализатора
	private $Analyzer_Code;//Код
	private $AnalyzerModel_id;//Модель анализатора
	private $MedService_id;//Служба
	private $Analyzer_begDT;//Дата открытия
	private $Analyzer_endDT;//Дата закрытия
	private $Analyzer_LisClientId;//Id клиента
	private $Analyzer_LisCompany;//Наименование ЛПУ
	private $Analyzer_LisLab;//Наименование лаборатории
	private $Analyzer_LisMachine;//Название машины в ЛИС
	private $Analyzer_LisLogin;//Логин в ЛИС
	private $Analyzer_LisPassword;//Пароль
	private $Analyzer_LisNote;//Примечание
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_id() { return $this->Analyzer_id;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_id($value) { $this->Analyzer_id = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_Name() { return $this->Analyzer_Name;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_Name($value) { $this->Analyzer_Name = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_Code() { return $this->Analyzer_Code;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_Code($value) { $this->Analyzer_Code = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzerModel_id() { return $this->AnalyzerModel_id;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzerModel_id($value) { $this->AnalyzerModel_id = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getMedService_id() { return $this->MedService_id;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setMedService_id($value) { $this->MedService_id = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_begDT() { return $this->Analyzer_begDT;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_begDT($value) { $this->Analyzer_begDT = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_endDT() { return $this->Analyzer_endDT;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_endDT($value) { $this->Analyzer_endDT = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_LisClientId() { return $this->Analyzer_LisClientId;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_LisClientId($value) { $this->Analyzer_LisClientId = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_LisCompany() { return $this->Analyzer_LisCompany;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_LisCompany($value) { $this->Analyzer_LisCompany = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_LisLab() { return $this->Analyzer_LisLab;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_LisLab($value) { $this->Analyzer_LisLab = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_LisMachine() { return $this->Analyzer_LisMachine;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_LisMachine($value) { $this->Analyzer_LisMachine = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_LisLogin() { return $this->Analyzer_LisLogin;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_LisLogin($value) { $this->Analyzer_LisLogin = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_LisPassword() { return $this->Analyzer_LisPassword;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_LisPassword($value) { $this->Analyzer_LisPassword = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getAnalyzer_LisNote() { return $this->Analyzer_LisNote;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setAnalyzer_LisNote($value) { $this->Analyzer_LisNote = $value; }
	
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getequipment_id() { return $this->equipment_id;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setequipment_id($value) { $this->equipment_id = $value; }

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 *	Сохранение признака(активности, связи, учёта) анализатора
	 */
	function saveAnalyzerField($data)
	{
		if(!isset($data['Analyzer_IsNotActive']) 
			&& !isset($data['Analyzer_2wayComm'])
			&& !isset($data['Analyzer_IsUseAutoReg']))
			return false;
		
		$set = 'Analyzer_IsNotActive = :Analyzer_IsNotActive';
		if ($data['Analyzer_2wayComm'])
			$set = "Analyzer_2wayComm = :Analyzer_2wayComm";
		if ($data['Analyzer_IsUseAutoReg'])
			$set = "Analyzer_IsUseAutoReg = :Analyzer_IsUseAutoReg";
		$query = "
			update
				lis.Analyzer with (rowlock)
			set
				{$set}
			where
				Analyzer_id = :Analyzer_id
		";

		$this->db->query($query, $data);

		return true;
	}
	
	/**
	 * Загрузка
	 */
	function load() {
		$q = "
			select
				a.Analyzer_id,
				a.Analyzer_Name,
				a.Analyzer_Code,
				a.AnalyzerModel_id,
				a.MedService_id,
				a.Analyzer_begDT,
				a.Analyzer_endDT,
				a.Analyzer_LisClientId,
				a.Analyzer_LisCompany,
				a.Analyzer_LisLab,
				a.Analyzer_LisMachine,
				a.Analyzer_LisLogin,
				a.Analyzer_LisPassword,
				a.Analyzer_LisNote,
				case when a.Analyzer_2wayComm = 2 then 1 else 0 end as Analyzer_2wayComm,
				case when a.Analyzer_IsUseAutoReg = 2 then 1 else 0 end as Analyzer_IsUseAutoReg,
				case when a.Analyzer_IsNotActive = 2 then 1 else 0 end as Analyzer_IsNotActive,
				case when a.Analyzer_IsAutoOk = 2 then 1 else 0 end as Analyzer_IsAutoOk,
				case when a.Analyzer_IsAutoGood = 2 then 1 else 0 end as Analyzer_IsAutoGood,
				case 
					when a.Analyzer_IsAutoOk = 2 and a.Analyzer_IsAutoGood = 2 then 2
					when a.Analyzer_IsAutoOk = 2 and a.Analyzer_IsAutoGood = 1 then 1
					else 0
				end as 'AutoOkType',
			    case when a.Analyzer_IsManualTechnic = 2 then 1 else 0 end as Analyzer_IsManualTechnic,
				link.equipment_id
			from
				lis.v_Analyzer a (nolock)
				outer apply (
					select top 1
						lis_id as equipment_id
					from
						lis.v_Link l (nolock)
					where
						l.object_id = a.Analyzer_id and l.link_object = 'Analyzer'
				) link
			where
				Analyzer_id = :Analyzer_id
		";
		$r = $this->db->query($q, array('Analyzer_id' => $this->Analyzer_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->Analyzer_id = $r[0]['Analyzer_id'];
				$this->Analyzer_Name = $r[0]['Analyzer_Name'];
				$this->Analyzer_Code = $r[0]['Analyzer_Code'];
				$this->AnalyzerModel_id = $r[0]['AnalyzerModel_id'];
				$this->MedService_id = $r[0]['MedService_id'];
				$this->Analyzer_begDT = $r[0]['Analyzer_begDT'];
				$this->Analyzer_endDT = $r[0]['Analyzer_endDT'];
				$this->Analyzer_LisClientId = $r[0]['Analyzer_LisClientId'];
				$this->Analyzer_LisCompany = $r[0]['Analyzer_LisCompany'];
				$this->Analyzer_LisLab = $r[0]['Analyzer_LisLab'];
				$this->Analyzer_LisMachine = $r[0]['Analyzer_LisMachine'];
				$this->Analyzer_LisLogin = $r[0]['Analyzer_LisLogin'];
				$this->Analyzer_LisPassword = $r[0]['Analyzer_LisPassword'];
				$this->Analyzer_LisNote = $r[0]['Analyzer_LisNote'];
				$this->equipment_id = $r[0]['equipment_id'];
				return $r;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		if (!empty($filter['EvnLabSamples'])) {
			// будем для каждой пробы вызывать запрос получения анализаторов, затем всё это мержить, т.к. как всё выгрести одним запросом неясно.
			$EvnLabSamples = json_decode($filter['EvnLabSamples'], true);
			$resp = array();
			$filter['EvnLabSamples'] = null;
			$count = 0;
			foreach($EvnLabSamples as $EvnLabSample) {
				$filter['EvnLabSample_id'] = $EvnLabSample;
				$analyzers = $this->loadList($filter);
				$newanalyzers = array();
				foreach($analyzers as $analyzer) {
					$newanalyzers[$analyzer['Analyzer_id']] = $analyzer;
					$newanalyzers[$analyzer['Analyzer_id']]['disabled'] = false;

					if ($count > 0) {
						// помечаем все которых нет неактивными
						if (empty($resp[$analyzer['Analyzer_id']])) {
							$newanalyzers[$analyzer['Analyzer_id']]['disabled'] = true;
						}
					}
				}

				// помечаем все которых нет неактивными
				foreach($resp as $analyzer) {
					if (empty($newanalyzers[$analyzer['Analyzer_id']])) {
						$resp[$analyzer['Analyzer_id']]['disabled'] = true;
					}
				}

				// сливаем всё в $resp
				foreach($newanalyzers as $analyzer) {
					if (empty($resp[$analyzer['Analyzer_id']])) {
						$resp[$analyzer['Analyzer_id']] = $analyzer;
					}
				}

				$count++;
			}
			return $resp;
		}
		$where = array();
		$join = "";
		if (isset($filter['hideRuchMetodiki']) && $filter['hideRuchMetodiki']) {
			// скрыть ручные методики
			$where[] = "(A.pmUser_insID <> 1 OR A.Analyzer_Code <> '000')";
		}
		if (isset($filter['Analyzer_id']) && $filter['Analyzer_id']) {
			$where[] = 'A.Analyzer_id = :Analyzer_id';
		}
		if (isset($filter['Analyzer_Name']) && $filter['Analyzer_Name']) {
			$where[] = 'A.Analyzer_Name = :Analyzer_Name';
		}
		if (isset($filter['Analyzer_Code']) && $filter['Analyzer_Code']) {
			$where[] = 'A.Analyzer_Code = :Analyzer_Code';
		}
		if (isset($filter['AnalyzerModel_id']) && $filter['AnalyzerModel_id']) {
			$where[] = 'A.AnalyzerModel_id = :AnalyzerModel_id';
		}
		if (isset($filter['MedService_id']) && $filter['MedService_id']) {
			$where[] = 'A.MedService_id = :MedService_id';
		}
		if (isset($filter['Analyzer_begDT']) && $filter['Analyzer_begDT']) {
			$where[] = 'A.Analyzer_begDT = :Analyzer_begDT';
		}
		if (isset($filter['Analyzer_endDT']) && $filter['Analyzer_endDT']) {
			$where[] = 'A.Analyzer_endDT = :Analyzer_endDT';
		}
		if (isset($filter['Analyzer_LisClientId']) && $filter['Analyzer_LisClientId']) {
			$where[] = 'A.Analyzer_LisClientId = :Analyzer_LisClientId';
		}
		if (isset($filter['Analyzer_LisCompany']) && $filter['Analyzer_LisCompany']) {
			$where[] = 'A.Analyzer_LisCompany = :Analyzer_LisCompany';
		}
		if (isset($filter['Analyzer_LisLab']) && $filter['Analyzer_LisLab']) {
			$where[] = 'A.Analyzer_LisLab = :Analyzer_LisLab';
		}
		if (isset($filter['Analyzer_LisMachine']) && $filter['Analyzer_LisMachine']) {
			$where[] = 'A.Analyzer_LisMachine = :Analyzer_LisMachine';
		}
		if (isset($filter['Analyzer_LisLogin']) && $filter['Analyzer_LisLogin']) {
			$where[] = 'A.Analyzer_LisLogin = :Analyzer_LisLogin';
		}
		if (isset($filter['Analyzer_LisPassword']) && $filter['Analyzer_LisPassword']) {
			$where[] = 'A.Analyzer_LisPassword = :Analyzer_LisPassword';
		}
		if (isset($filter['Analyzer_LisNote']) && $filter['Analyzer_LisNote']) {
			$where[] = 'A.Analyzer_LisNote = :Analyzer_LisNote';
		}
		if (isset($filter['Analyzer_IsNotActive']) && $filter['Analyzer_IsNotActive']) {
			$where[] = 'ISNULL(A.Analyzer_IsNotActive, 1) = :Analyzer_IsNotActive';
			// также не показываем закрытые анализаторы
			if ($filter['Analyzer_IsNotActive'] == 1) {
				$where[] = '(A.Analyzer_endDT >= dbo.tzGetDate() OR A.Analyzer_endDT IS NULL)';
			}
		}
		if (isset($filter['EvnLabSample_id'])) {
			$uccodes = array();
			if (!empty($filter['uccodes'])) {
				$uccodes = $filter['uccodes'];
			} else {
				// получаем список исследований пробы
				$query = "
					select
						uc.UslugaComplex_id as UslugaComplexTest_id,
						euinp.UslugaComplex_id as UslugaComplexTarget_id
					from
						v_UslugaComplex uc (nolock)
						inner join v_EvnUslugaPar euin (nolock) on euin.UslugaComplex_id = uc.UslugaComplex_id
						inner join v_EvnLabSample els (nolock) on els.EvnLabSample_id = euin.EvnLabSample_id
						inner join v_EvnUslugaPar euinp (nolock) on euinp.EvnUslugaPar_id = euin.EvnUslugaPar_pid -- корневая услуга
					where
						els.EvnLabSample_id = :EvnLabSample_id
				";

				$result = $this->db->query($query, $filter);
				if (is_object($result)) {
					$uccodes = $result->result('array');
				}
			}

			$uccodes_count = 0;
			if (count($uccodes) > 0) {
				// т.к. исселдование в пробе может быть и не одно, собираем в массив тесты к их исследованиям
				$researches = array();
				foreach($uccodes as $respone) {
					if (empty($researches[$respone['UslugaComplexTarget_id']])) {
						$researches[$respone['UslugaComplexTarget_id']] = array();
					}

					if (!in_array($respone['UslugaComplexTest_id'], $researches[$respone['UslugaComplexTarget_id']])) {
						$researches[$respone['UslugaComplexTarget_id']][] = $respone['UslugaComplexTest_id'];
						$uccodes_count++;
					}
				}

				$filterChildAr = array();
				foreach(array_keys($researches) as $key) {
					$filterChildAr[] = "(ucms_child.UslugaComplex_id IN ('".implode("','",$researches[$key])."') and at_ucms.UslugaComplex_id = '{$key}')";
				}

				$filterChild = "";
				if (count($filterChildAr) > 0) {
					$filterChild = " and (".implode(' or ', $filterChildAr).")";
				}
			}
		}

		// если есть фильтрация по составу, то отображаем анализаторы при наличии хоть одной услуги на нём
		if ( !empty($uccodes_count) && $uccodes_count > 0 ) {
			$join .= "
				CROSS APPLY ( 
					SELECT top 1
						at_child.AnalyzerTest_id 
					FROM 
						lis.v_AnalyzerTest at (nolock)
						inner join v_UslugaComplexMedService at_ucms (nolock) on at_ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
						inner join lis.v_AnalyzerTest at_child (nolock) on (case when at.AnalyzerTest_IsTest = 2 then at_child.AnalyzerTest_id else at_child.AnalyzerTest_pid end) = at.AnalyzerTest_id
						inner join v_UslugaComplexMedService ucms_child (nolock) on ucms_child.UslugaComplexMedService_id = at_child.UslugaComplexMedService_id
					where
						at.Analyzer_id = a.Analyzer_id
						and at.AnalyzerTest_pid is null
						{$filterChild}
						and ISNULL(AT.AnalyzerTest_IsNotActive, 1) = 1
						and ISNULL(at_child.AnalyzerTest_IsNotActive, 1) = 1
				) at
			";
		}

		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		} else {
			$where_clause = 'WHERE (1=1)';
		}
		
		$q = "
			SELECT
				A.Analyzer_id,
				A.Analyzer_Name,
				A.Analyzer_Code,
				A.pmUser_insID,
				A.AnalyzerModel_id,
				A.MedService_id,
				convert(varchar, A.Analyzer_begDT, 104) as Analyzer_begDT,
				convert(varchar,A.Analyzer_endDT,104) as Analyzer_endDT,
				A.Analyzer_LisClientId,
				A.Analyzer_LisCompany,
				A.Analyzer_LisLab,
				A.Analyzer_LisMachine,
				A.Analyzer_LisLogin,
				A.Analyzer_LisPassword,
				A.Analyzer_LisNote,
								case when A.Analyzer_2wayComm = 2 then 1 else 0 end as Analyzer_2wayComm,
								case when A.Analyzer_IsUseAutoReg = 2 then 1 else 0 end as Analyzer_IsUseAutoReg,
				case when A.Analyzer_IsNotActive = 2 then 1 else 0 end as Analyzer_IsNotActive,
				case when A.Analyzer_IsAutoOk = 2 then 1 else 0 end as Analyzer_IsAutoOk,
				case when A.Analyzer_IsAutoGood = 2 then 1 else 0 end as Analyzer_IsAutoGood,
				AnalyzerModel_id_ref.AnalyzerModel_Name AnalyzerModel_id_Name,
				MedService_id_ref.MedService_Name MedService_id_Name
			FROM
				lis.v_Analyzer A WITH (NOLOCK)
				LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref WITH (NOLOCK) ON AnalyzerModel_id_ref.AnalyzerModel_id = A.AnalyzerModel_id
				LEFT JOIN dbo.v_MedService MedService_id_ref WITH (NOLOCK) ON MedService_id_ref.MedService_id = A.MedService_id
				{$join}
			{$where_clause}
		";

		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			if (count($result->result('array')) > 0) {
				return $result->result('array');
			} else {
				$q = "
					SELECT
						A.Analyzer_id,
						A.Analyzer_Name,
						A.Analyzer_Code,
						A.pmUser_insID,
						A.AnalyzerModel_id,
						A.MedService_id,
						convert(varchar, A.Analyzer_begDT, 104) as Analyzer_begDT,
						convert(varchar,A.Analyzer_endDT,104) as Analyzer_endDT,
						A.Analyzer_LisClientId,
						A.Analyzer_LisCompany,
						A.Analyzer_LisLab,
						A.Analyzer_LisMachine,
						A.Analyzer_LisLogin,
						A.Analyzer_LisPassword,
						A.Analyzer_LisNote,
										case when A.Analyzer_2wayComm = 2 then 1 else 0 end as Analyzer_2wayComm,
										case when A.Analyzer_IsUseAutoReg = 2 then 1 else 0 end as Analyzer_IsUseAutoReg,
						case when A.Analyzer_IsNotActive = 2 then 1 else 0 end as Analyzer_IsNotActive,
						case when A.Analyzer_IsAutoOk = 2 then 1 else 0 end as Analyzer_IsAutoOk,
						case when A.Analyzer_IsAutoGood = 2 then 1 else 0 end as Analyzer_IsAutoGood,
						AnalyzerModel_id_ref.AnalyzerModel_Name AnalyzerModel_id_Name,
						MedService_id_ref.MedService_Name MedService_id_Name
					FROM
						lis.v_Analyzer A WITH (NOLOCK)
						LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref WITH (NOLOCK) ON AnalyzerModel_id_ref.AnalyzerModel_id = A.AnalyzerModel_id
						LEFT JOIN dbo.v_MedService MedService_id_ref WITH (NOLOCK) ON MedService_id_ref.MedService_id = A.MedService_id
					{$where_clause}
					";
				$result = $this->db->query($q, $filter);
				if (is_object($result)) {
					return $result->result('array');
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Сохранение ссылки
	 */
	function saveEquimpentLink($data) {
		$query = "
			select top 1
				Link_id
			from
				lis.v_Link (nolock)
			where
				object_id = :Analyzer_id
				and link_object = 'Analyzer'
		";
		$result = $this->db->query($query, $data);
		
		$data['Link_id'] = null;
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Link_id'])) {
				$data['Link_id'] = $resp[0]['Link_id'];
			}
		}
		
		$procedure = 'p_Link_ins';
		if ( !empty($data['Link_id']) ) {
			$procedure = 'p_Link_upd';
		}
		$query = "
			declare
				@Link_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Link_id = :Link_id;
			exec lis.{$procedure}
				@Link_id = @Link_id output,
				@link_object = 'Analyzer',
				@lis_id = :equipment_id,
				@object_id = :Analyzer_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Link_id as Link_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
				return false;
			}
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Проверка на связь анализатора
	 */
	function checkAnalyzerHasLinkAllready($data)
	{
		if (empty($data['Analyzer_id']))
		{
			$data['Analyzer_id'] = NULL;
		}
		
		// проверяем наличие связи
		$query = "
			select top 1
				Link_id
			from
				lis.v_Link (nolock)
			where
				lis_id = :equipment_id
				and link_object = 'Analyzer'
				and (object_id <> :Analyzer_id OR :Analyzer_id IS NULL)
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Link_id'])) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Добавление услуги теста в состав услуги исследования
	 */
	function addUslugaComplexTargetTestComposition($data)
	{
		// 1. ищем, возможно уже есть связь указанных услуг
		$query = "
			select top 1
				ucc.UslugaComplexComposition_id
			from
				v_UslugaComplexComposition ucc (nolock)
			where
				ucc.UslugaComplex_pid = :UslugaComplexTarget_id
				and ucc.UslugaComplex_id = :UslugaComplexTest_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['UslugaComplexComposition_id'])) {
				return $resp[0]['UslugaComplexComposition_id'];
			}
		}
		
		// 2. если не нашли связь то добавляем услугу в состав
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = null;

			exec p_UslugaComplexComposition_ins
				@UslugaComplexComposition_id = @Res output,
				@UslugaComplex_id = :UslugaComplexTest_id,
				@UslugaComplex_pid = :UslugaComplexTarget_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as UslugaComplexComposition_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
	}
	
	/**
	 * Добавление услуги ЛИС
	 */
	function addUslugaComplexFromLis($data, $UslugaComplex_Code, $UslugaComplex_Name)
	{
		$data['UslugaComplex_Code'] = $UslugaComplex_Code;
		$data['UslugaComplex_Name'] = $UslugaComplex_Name;
		// 0. ищем услугу ГОСТ-2011
		$UslugaComplex_2011id = null;
		$query = "
			select top 1
				uc.UslugaComplex_id
			from
				v_UslugaComplex uc (nolock)
				inner join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where
				uc.UslugaComplex_Code = :UslugaComplex_Code
				and ucat.UslugaCategory_SysNick = 'gost2011'
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['UslugaComplex_id'])) {
				$UslugaComplex_2011id = $resp[0]['UslugaComplex_id'];
			}
		}
		
		if (empty($UslugaComplex_2011id)) {
			// die("нет услуги ГОСТ2011 с кодом {$UslugaComplex_Code}, пишем в лог и не связываем");
			return false;
		}
		
		$data['UslugaComplex_2011id'] = $UslugaComplex_2011id;

		// Теперь услуга строго по госту(refs #PROMEDWEB-9543)
		$UslugaComplex_id = $data['UslugaComplex_2011id'];
		
		// добавляем услуге атрибут "Лабораторно-диагностическая"
		$UslugaComplexAttribute_id = $this->addUslugaComplexAttr([
			'UslugaComplex_id' => $UslugaComplex_id,
			'pmUser_id' => $data['pmUser_id']
		]);
		if (empty($UslugaComplexAttribute_id))
			return false;
		
		return $UslugaComplex_id;
	}
	
	/**
	 * Добавление услуги на службу из исследования ЛИС
	 */
	function addUslugaComplexTargetMedService($data)
	{
		$data['UslugaComplexMedService_id'] = null;

		// сначала услугу превращаем в связанную гостовскую услугу!
		$data['UslugaComplexToAdd_id'] = $data['UslugaComplexTarget_id'];
		$UslugaComplex_2011id = $this->getFirstResultFromQuery("select UslugaComplex_2011id from v_UslugaComplex with(nolock) where UslugaComplex_id = :UslugaComplexToAdd_id", $data);
		if (!empty($UslugaComplex_2011id)) {
			$data['UslugaComplexToAdd_id'] = $UslugaComplex_2011id;
		}

		// 1. сначала ищем, может уже добавлена на службу такая услуга
		$query = "
			select
				ucm.UslugaComplexMedService_id
			from
				v_UslugaComplexMedService ucm (nolock)
			where
				ucm.UslugaComplex_id = :UslugaComplexToAdd_id
				and ucm.MedService_id = :MedService_id
				and ucm.UslugaComplexMedService_pid IS NULL
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp[0]['UslugaComplexMedService_id'];
			}
		}
		
		if (empty($data['UslugaComplexMedService_id'])) {
			$this->load->model('UslugaComplexMedService_model');
			$resp = $this->UslugaComplexMedService_model->doSave(array(
				'UslugaComplexMedService_id' => null,
				'scenario' => self::SCENARIO_DO_SAVE,
				'MedService_id' => $data['MedService_id'],
				'UslugaComplex_id' => $data['UslugaComplexToAdd_id'],
				'UslugaComplexMedService_begDT' => '@curDT',
				'UslugaComplexMedService_endDT' => null,
				'RefSample_id' => $data['RefSample_id'],
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($resp['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp['UslugaComplexMedService_id'];
			}
		}
		
		// добавить исследование на анализатор
		if (!empty($data['UslugaComplexMedService_id'])) {
			$this->addAnalyzerTestForUslugaComplexMedService($data, null);
		}
		
		return $data['UslugaComplexMedService_id'];
	}
	
	/**
	 * Добавление услуги на службе на анализатор
	 */
	function addAnalyzerTestForUslugaComplexMedService($data, $test_id)
	{
		$data['AnalyzerTest_id'] = null;
		
		// 1. сначала ищем, может уже добавлена на анализатор такая услуга
		$query = "
			select
				at.AnalyzerTest_id
			from
				lis.v_AnalyzerTest at (nolock)
			where
				at.UslugaComplexMedService_id = :UslugaComplexMedService_id
				and at.Analyzer_id = :Analyzer_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['AnalyzerTest_id'])) {
				$data['AnalyzerTest_id'] = $resp[0]['AnalyzerTest_id'];
			}
		}
		
		if (empty($data['AnalyzerTest_id'])) {
			if (empty($data['AnalyzerTest_Code'])) {
				$data['AnalyzerTest_Code'] = null;
			}
			if (empty($data['AnalyzerTest_SysNick'])) {
				$data['AnalyzerTest_SysNick'] = null;
			}
			$data['AnalyzerTest_pid'] = null;
			// 2. добавляем услугу на анализатор
			$query = '
				declare
					@AnalyzerTest_id bigint,
					@curdate datetime,
					@AnalyzerTest_pid bigint,
					@UslugaComplex_id bigint,
					@AnalyzerTest_Name varchar(500),
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @UslugaComplex_id = (
					select top 1
						UslugaComplex_id
					from
						v_UslugaComplexMedService (nolock)
					where
						UslugaComplexMedService_id = :UslugaComplexMedService_id
				);
				set @AnalyzerTest_Name = (
					select top 1
						UslugaComplex_Name
					from
						v_UslugaComplex (nolock)
					where
						UslugaComplex_id = @UslugaComplex_id
				);
				set @AnalyzerTest_pid = (
					select top 1
						parentAt.AnalyzerTest_id
					from
						lis.v_AnalyzerTest parentAt (nolock)
						inner join v_UslugaComplexMedService parentUc (nolock) on parentAt.UslugaComplexMedService_id = parentUc.UslugaComplexMedService_id
						inner join v_UslugaComplexMedService childUc (nolock) on parentUc.UslugaComplexMedService_id = childUc.UslugaComplexMedService_pid
					where
						childUc.UslugaComplexMedService_id = :UslugaComplexMedService_id and parentAt.Analyzer_id = :Analyzer_id
				);
				set @curdate = dbo.tzGetDate();
				set @AnalyzerTest_id = :AnalyzerTest_id;
				exec lis.p_AnalyzerTest_ins
					@AnalyzerTest_id = @AnalyzerTest_id output,
					@AnalyzerTest_pid = @AnalyzerTest_pid,
					@AnalyzerModel_id = NULL,
					@Analyzer_id = :Analyzer_id,
					@UslugaComplex_id = @UslugaComplex_id,
					@AnalyzerTest_IsTest = :AnalyzerTest_IsTest,
					@AnalyzerTestType_id = 1,
					@Unit_id = NULL,
					@AnalyzerTest_Code = :AnalyzerTest_Code,
					@AnalyzerTest_Name = @AnalyzerTest_Name,
					@AnalyzerTest_SysNick = :AnalyzerTest_SysNick,
					@AnalyzerTest_begDT = @curdate,
					@AnalyzerTest_endDT = null,
					@UslugaComplexMedService_id = :UslugaComplexMedService_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @AnalyzerTest_id as AnalyzerTest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';

			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['AnalyzerTest_id'])) {
					$data['AnalyzerTest_id'] = $resp[0]['AnalyzerTest_id'];
				}
			}
			
			if (!empty($test_id) && $data['AnalyzerTest_IsTest'] == 2 && !empty($data['AnalyzerTest_id'])) {
				// 3. Копируем референсные значения
				$this->copyRefValuesFromLis($data, $test_id);
			}
		}
	}
	
	/**
	 * Копирование референсных значений из теста лис
	 */
	function copyRefValuesFromLis($data, $test_id) {
		// 1. достаем все референсные значения из лис
		$query = "
			select
				nr.point2 as RefValues_LowerLimit,
				nr.point3 as RefValues_UpperLimit,
				pg.sex as Sex_id,
				case
					when pg.ageunit = 1 then round(cast(pg.agestart as float) * 12, 0)
					when pg.ageunit = 2 then round(cast(pg.agestart as float) * 365, 0)
					else round(pg.agestart, 0)
				end as RefValues_LowerAge,
				case
					when pg.ageunit = 1 then round(cast(pg.ageend as float) * 12, 0)
					when pg.ageunit = 2 then round(cast(pg.ageend as float) * 365, 0)
					else round(pg.ageend, 0)
				end as RefValues_UpperAge,
				case
					when pg.ageunit = 1 then 2
					when pg.ageunit = 2 then 3
					else 1
				end as AgeUnit_id,
				pg.pregnancyStart as RefValues_PregnancyFrom,
				pg.pregnancyEnd as RefValues_PregnancyTo
			from
				lis._test_numericRanges tnr (nolock)
				inner join lis._numericRanges nr (nolock) on nr.id = tnr.numericRanges_id
				left join lis._patientGroup pg (nolock) on pg.id = nr.patientGroup_id
			where
				tnr.test_id = :test_id
		";
		
		$result = $this->db->query($query, array(
			'test_id' => $test_id
		));
		
		$this->load->model('AnalyzerTestRefValues_model', 'AnalyzerTestRefValues_model');
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as $respone) {
				// 2. добавляем референсное значение тесту
				
				$ret = $this->AnalyzerTestRefValues_model->save(array(
					'AnalyzerTest_id' => $data['AnalyzerTest_id'],
					'pmUser_id' => $data['pmUser_id'],
					'AnalyzerTestRefValues_id' => null,
					'RefValues_Name' => $respone['RefValues_LowerLimit'].'-'.$respone['RefValues_UpperLimit'],
					'Unit_id' => null,
					'RefValues_LowerLimit' => $respone['RefValues_LowerLimit'],
					'RefValues_UpperLimit' => $respone['RefValues_UpperLimit'],
					'RefValues_BotCritValue' => null,
					'RefValues_TopCritValue' => null,
					'RefValues_Description' => null,
					'Sex_id' => $respone['Sex_id'],
					'RefValues_LowerAge' => $respone['RefValues_LowerAge'],
					'RefValues_UpperAge' => $respone['RefValues_UpperAge'],
					'AgeUnit_id' => $respone['AgeUnit_id'],
					'HormonalPhaseType_id' => null,
					'RefValues_PregnancyFrom' => $respone['RefValues_PregnancyFrom'],
					'RefValues_PregnancyTo' => $respone['RefValues_PregnancyTo'],
					'PregnancyUnitType_id' => 1,
					'RefValues_TimeOfDayFrom' => null,
					'RefValues_TimeOfDayTo' => null
				));
			}
		}
	}
	
	/**
	 * Добавление услуги на службу из теста ЛИС
	 */
	function addUslugaComplexTestMedService($data, $test_id)
	{
		$data['UslugaComplexMedService_id'] = null;

		// сначала услугу превращаем в связанную гостовскую услугу!
		$data['UslugaComplexToAdd_id'] = $data['UslugaComplexTest_id'];
		$UslugaComplex_2011id = $this->getFirstResultFromQuery("select UslugaComplex_2011id from v_UslugaComplex with (nolock) where UslugaComplex_id = :UslugaComplexToAdd_id", $data);
		if (!empty($UslugaComplex_2011id)) {
			$data['UslugaComplexToAdd_id'] = $UslugaComplex_2011id;
		}

		// 1. сначала ищем, может уже добавлена на службу такая услуга
		$query = "
			select
				ucm.UslugaComplexMedService_id
			from
				v_UslugaComplexMedService ucm (nolock)
			where
				ucm.UslugaComplex_id = :UslugaComplexToAdd_id
				and ucm.MedService_id = :MedService_id
				and ucm.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp[0]['UslugaComplexMedService_id'];
			}
		}
		
		if (empty($data['UslugaComplexMedService_id'])) {
			$this->load->model('UslugaComplexMedService_model');
			$resp = $this->UslugaComplexMedService_model->doSave(array(
				'UslugaComplexMedService_id' => null,
				'scenario' => self::SCENARIO_DO_SAVE,
				'MedService_id' => $data['MedService_id'],
				'UslugaComplex_id' => $data['UslugaComplexToAdd_id'],
				'UslugaComplexMedService_pid' => $data['UslugaComplexMedService_pid'],
				'UslugaComplexMedService_begDT' => '@curDT',
				'UslugaComplexMedService_endDT' => null,
				'RefSample_id' => $data['RefSample_id'],
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($resp['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp['UslugaComplexMedService_id'];
			}
		}
		
		// добавить тест на анализатор
		if (!empty($data['UslugaComplexMedService_id'])) {
			$data['AnalyzerTest_IsTest'] = 2;
			$this->addAnalyzerTestForUslugaComplexMedService($data, $test_id);
		}
		
		return false;
	}
	
	/**
	 * Добавление пробы для исследования/теста
	 */
	function addRefSample($data)
	{
		$RefSample_id = null;
		$RefMaterial_id = null;
		// если не задан биоматериал, то пробу не создаём
		if (!empty($data['RefMaterial_Code']) && !empty($data['RefMaterial_SysNick'])) {
			// 1. ищем биоматериал
			$query = "
				select top 1
					RefMaterial_id
				from
					v_RefMaterial (nolock)
				where
					RefMaterial_SysNick = :RefMaterial_SysNick
			";
			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['RefMaterial_id'])) {
					$RefMaterial_id = $resp[0]['RefMaterial_id'];
				}
			}
			// 2. если не нашли биоматериал то создаём новый
			if (empty($RefMaterial_id)) {
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @Res = NULL;
					exec p_RefMaterial_ins
						@RefMaterial_id = @Res output,
						@RefMaterial_Code = :RefMaterial_Code,
						@RefMaterial_Name = :RefMaterial_Name,
						@RefMaterial_SysNick = :RefMaterial_SysNick,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as RefMaterial_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				
				$result = $this->db->query($query, $data);
				
				if ( is_object($result) ) {
					$resp = $result->result('array');
					if (count($resp) > 0 && !empty($resp[0]['RefMaterial_id'])) {
						$RefMaterial_id = $resp[0]['RefMaterial_id'];
					}
				}
			}
			
			// 3. создаём пробу
			if (!empty($RefMaterial_id)) {
				$data['RefMaterial_id'] = $RefMaterial_id;
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @Res = NULL;
					exec p_RefSample_ins
						@RefSample_id = @Res output,
						@RefMaterial_id = :RefMaterial_id,
						@RefSample_Name = :RefMaterial_Name,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as RefSample_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				
				$result = $this->db->query($query, $data);
				
				if ( is_object($result) ) {
					$resp = $result->result('array');
					if (count($resp) > 0 && !empty($resp[0]['RefSample_id'])) {
						$RefSample_id = $resp[0]['RefSample_id'];
					}
				}
			}
		}
		
		// 4. отдаём id-шник пробы
		return $RefSample_id;
	}
	
	/**
	 * Добавление услуги на из теста ЛИС
	 */
	function addUslugaComplexMedServiceFromTest($data)
	{
		$this->db->trans_begin();
		// 1. получаем код услуги в ЛИС
		$query = "
			declare
				@MedService_id bigint,
				@UslugaComplexTarget_id bigint;
				
			set @MedService_id = (
				select top 1 MedService_id from v_UslugaComplexMedService (nolock) where UslugaComplexMedService_id = :UslugaComplexMedService_pid
			);
			
			set @UslugaComplexTarget_id = (
				select top 1 UslugaComplex_id from v_UslugaComplexMedService (nolock) where UslugaComplexMedService_id = :UslugaComplexMedService_pid
			);
			
			select top 1
				t.id as test_id,
				t.code as test_code,
				t.name as test_name,
				target_bio.code as RefMaterialTarget_Code,
				target_bio.name as RefMaterialTarget_Name,
				target_bio.mnemonics as RefMaterialTarget_SysNick,
				@MedService_id as MedService_id,
				@UslugaComplexTarget_id as UslugaComplexTarget_id
			from
				lis._test t (nolock)
				outer apply(
					select top 1
						b.code,
						b.name,
						b.mnemonics
					from lis._biomaterial b (nolock)
						inner join lis._target_biomaterials tb (nolock) on tb.biomaterial_id = b.id
						inner join lis._test_targets tt (nolock) on tt.target_id = tb.target_id
					where tt.test_id = t.id
				) target_bio
			where
				t.id = :test_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['test_code'])) {
				// добавляем услугу теста
				$data['MedService_id'] = $resp[0]['MedService_id'];
				$data['UslugaComplexTarget_id'] = $resp[0]['UslugaComplexTarget_id'];
				$data['UslugaComplexTest_id'] = $this->addUslugaComplexFromLis($data, $resp[0]['test_code'], $resp[0]['test_name']);
				if (!empty($data['UslugaComplexTarget_id']) && !empty($data['UslugaComplexTest_id']))
				{
					// добавляем услугу теста в состав услуги исследования
					$this->addUslugaComplexTargetTestComposition($data);
					// добавляем услугу теста на службу в состав услуги исследования
					if (!empty($data['UslugaComplexMedService_pid'])) {
						$data['RefSample_id'] = null;
						$data['RefMaterial_Code'] = $resp[0]['RefMaterialTarget_Code'];
						$data['RefMaterial_Name'] = $resp[0]['RefMaterialTarget_Name'];
						$data['RefMaterial_SysNick'] = $resp[0]['RefMaterialTarget_SysNick'];
						$data['RefSample_id'] = $this->addRefSample($data);
						$response = $this->addUslugaComplexTestMedService($data, $resp[0]['test_id']);
						$this->db->trans_commit();
						return $response;
					}
				}
			}
		}
		$this->db->trans_commit();
		return array('Error_Msg' => '');
	}

	/**
	 * Копирование атрибутов тестов
	 */
	function copyAnalyzerTestAttr($data)
	{
		// 1. QuantitativeTestUnit
		$query = "
			select
				Unit_id,
				QuantitativeTestUnit_IsBase,
				QuantitativeTestUnit_CoeffEnum
			from
				lis.v_QuantitativeTestUnit (nolock)
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";
		
		$result = $this->db->query($query, array(
			'AnalyzerTest_id' => $data['AnalyzerTest_idFrom']
		));
		
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as $respone) {
				$query = "
					declare
						@QuantitativeTestUnit_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @QuantitativeTestUnit_id = NULL;
					exec lis.p_QuantitativeTestUnit_ins
						@QuantitativeTestUnit_id = @QuantitativeTestUnit_id output,
						@AnalyzerTest_id = :AnalyzerTest_id,
						@Unit_id = :Unit_id,
						@QuantitativeTestUnit_IsBase = :QuantitativeTestUnit_IsBase,
						@QuantitativeTestUnit_CoeffEnum = :QuantitativeTestUnit_CoeffEnum,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @QuantitativeTestUnit_id as QuantitativeTestUnit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$respone['pmUser_id'] = $data['pmUser_id'];
				$respone['AnalyzerTest_id'] = $data['AnalyzerTest_idTo'];
				$this->db->query($query, $respone);
			}
		}
		
		// 2. QualitativeTestAnswerAnalyzerTest
		$query = "
			select
				QualitativeTestAnswerAnalyzerTest_id,
				QualitativeTestAnswer_id,
				QualitativeTestAnswerAnalyzerTest_Answer
			from
				lis.v_QualitativeTestAnswerAnalyzerTest (nolock)
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";
		
		$result = $this->db->query($query, array(
			'AnalyzerTest_id' => $data['AnalyzerTest_idFrom']
		));
		
		$QualitativeTestAnswerAnalyzerTests = array();
		
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as $respone) {
				$query = "
					declare
						@QualitativeTestAnswerAnalyzerTest_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @QualitativeTestAnswerAnalyzerTest_id = NULL;
					exec lis.p_QualitativeTestAnswerAnalyzerTest_ins
						@QualitativeTestAnswerAnalyzerTest_id = @QualitativeTestAnswerAnalyzerTest_id output,
						@AnalyzerTest_id = :AnalyzerTest_id,
						@QualitativeTestAnswer_id = :QualitativeTestAnswer_id,
						@QualitativeTestAnswerAnalyzerTest_Answer = :QualitativeTestAnswerAnalyzerTest_Answer,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @QualitativeTestAnswerAnalyzerTest_id as QualitativeTestAnswerAnalyzerTest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$respone['pmUser_id'] = $data['pmUser_id'];
				$respone['AnalyzerTest_id'] = $data['AnalyzerTest_idTo'];
				$result_qtaat = $this->db->query($query, $respone);
				if (is_object($result_qtaat)) {
					$resp_qtaat = $result_qtaat->result('array');
					if (!empty($resp_qtaat[0]['QualitativeTestAnswerAnalyzerTest_id'])) {
						$QualitativeTestAnswerAnalyzerTests[$respone['QualitativeTestAnswerAnalyzerTest_id']] = $resp_qtaat[0]['QualitativeTestAnswerAnalyzerTest_id'];
					}
				}
			}
		}
		
		// 3. AnalyzerTestRefValues
		$query = "
			select
				atrv.AnalyzerTestRefValues_id,
				atrv.RefValues_id,
				rv.RefValues_Name,
				rv.Unit_id,
				rv.RefValues_LowerLimit,
				rv.RefValues_UpperLimit,
				rv.RefValues_BotCritValue,
				rv.RefValues_TopCritValue,
				rv.RefValues_Description,
				rv.Sex_id,
				rv.RefValues_LowerAge,
				rv.RefValues_UpperAge,
				rv.AgeUnit_id,
				rv.HormonalPhaseType_id,
				rv.RefValues_PregnancyFrom,
				rv.RefValues_PregnancyTo,
				rv.PregnancyUnitType_id,
				rv.RefValues_TimeOfDayFrom,
				rv.RefValues_TimeOfDayTo			
			from
				lis.v_AnalyzerTestRefValues atrv (nolock)
				inner join v_RefValues rv (nolock) on rv.RefValues_id = atrv.RefValues_id
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";
		
		$result = $this->db->query($query, array(
			'AnalyzerTest_id' => $data['AnalyzerTest_idFrom']
		));
		
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as $respone) {
				$query = "
					declare
						@RefValues_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @RefValues_id = NULL;
					exec p_RefValues_ins
						@RefValues_id = @RefValues_id output,
						@RefValues_Name = :RefValues_Name,
						@RefValues_Nick = :RefValues_Name,
						@RefValuesType_id = NULL,
						@Unit_id = :Unit_id,
						@RefValues_LowerLimit = :RefValues_LowerLimit,
						@RefValues_UpperLimit = :RefValues_UpperLimit,
						@RefValues_BotCritValue = :RefValues_BotCritValue,
						@RefValues_TopCritValue = :RefValues_TopCritValue,
						@RefValues_Description = :RefValues_Description,
						@Sex_id = :Sex_id,
						@RefValues_LowerAge = :RefValues_LowerAge,
						@RefValues_UpperAge = :RefValues_UpperAge,
						@AgeUnit_id = :AgeUnit_id,
						@HormonalPhaseType_id = :HormonalPhaseType_id,
						@RefValues_PregnancyFrom = :RefValues_PregnancyFrom,
						@RefValues_PregnancyTo = :RefValues_PregnancyTo,
						@PregnancyUnitType_id = :PregnancyUnitType_id,
						@RefValues_TimeOfDayFrom = :RefValues_TimeOfDayFrom,
						@RefValues_TimeOfDayTo = :RefValues_TimeOfDayTo,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @RefValues_id as RefValues_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$respone['pmUser_id'] = $data['pmUser_id'];
				$result = $this->db->query($query, $respone);
				if ( is_object($result) ) {
					$resp = $result->result('array');
					if (!empty($resp[0]['RefValues_id'])) {
						$query = "
							declare
								@AnalyzerTestRefValues_id bigint,
								@ErrCode int,
								@ErrMessage varchar(4000);
							set @AnalyzerTestRefValues_id = NULL;
							exec lis.p_AnalyzerTestRefValues_ins
								@AnalyzerTestRefValues_id = @AnalyzerTestRefValues_id output,
								@AnalyzerTest_id = :AnalyzerTest_id,
								@RefValues_id = :RefValues_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @AnalyzerTestRefValues_id as AnalyzerTestRefValues_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$result_atrv = $this->db->query($query, array(
							'AnalyzerTest_id' => $data['AnalyzerTest_idTo'],
							'RefValues_id' => $resp[0]['RefValues_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						$resp_atrv = $result_atrv->result('array');
						if (!empty($resp_atrv[0]['AnalyzerTestRefValues_id']) && !empty($respone['AnalyzerTestRefValues_id'])) {
							// скопировать значения ответов качественных тестов
							$query = "
								select
									QualitativeTestAnswerAnalyzerTest_id
								from
									lis.v_QualitativeTestAnswerReferValue qtarv (nolock)
								where
									AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
							";
							
							$result_qtarv = $this->db->query($query, array(
								'AnalyzerTestRefValues_id' => $respone['AnalyzerTestRefValues_id']
							));
							
							if (is_object($result_qtarv)) {
								$resp_qtarv = $result_qtarv->result('array');
								foreach($resp_qtarv as $resp_qtarvone) {
									if (!empty($QualitativeTestAnswerAnalyzerTests[$resp_qtarvone['QualitativeTestAnswerAnalyzerTest_id']])) {
										$query = "
											declare
												@QualitativeTestAnswerReferValue_id bigint,
												@ErrCode int,
												@ErrMessage varchar(4000);
											set @QualitativeTestAnswerReferValue_id = NULL;
											exec lis.p_QualitativeTestAnswerReferValue_ins
												@QualitativeTestAnswerReferValue_id = @QualitativeTestAnswerReferValue_id output,
												@AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id,
												@QualitativeTestAnswerAnalyzerTest_id = :QualitativeTestAnswerAnalyzerTest_id,
												@pmUser_id = :pmUser_id,
												@Error_Code = @ErrCode output,
												@Error_Message = @ErrMessage output;
											select @QualitativeTestAnswerReferValue_id as QualitativeTestAnswerReferValue_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
										";
										
										$this->db->query($query, array(
											'AnalyzerTestRefValues_id' => $resp_atrv[0]['AnalyzerTestRefValues_id'],
											'QualitativeTestAnswerAnalyzerTest_id' => $QualitativeTestAnswerAnalyzerTests[$resp_qtarvone['QualitativeTestAnswerAnalyzerTest_id']],
											'pmUser_id' => $data['pmUser_id']
										));
									}
								}
							}
							
							// скопировать Limit
							$query = "
								select
									l.*
								from
									v_Limit l (nolock)
								where
									l.RefValues_id = :RefValues_id
							";
							
							$result_limit = $this->db->query($query, array(
								'RefValues_id' => $respone['RefValues_id']
							));
							
							if ( is_object($result_limit) ) {
								$resp_limit = $result_limit->result('array');
								foreach ($resp_limit as $resp_limitone) {
									$resp_limitone['RefValues_id'] = $resp[0]['RefValues_id'];
									$resp_limitone['pmUser_id'] = $data['pmUser_id'];
									
									$query = "
										declare
											@Limit_id bigint,
											@ErrCode int,
											@ErrMessage varchar(4000);
										set @Limit_id = NULL;
										exec p_Limit_ins
											@Limit_id = @Limit_id output,
											@RefValues_id = :RefValues_id,
											@LimitType_id = :LimitType_id,
											@Limit_Values = :Limit_Values,
											@Limit_ValuesFrom = :Limit_ValuesFrom,
											@Limit_ValuesTo = :Limit_ValuesTo,
											@Limit_IsActiv = :Limit_IsActiv,
											@RefValuesSetRefValues_id = null,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
										select @Limit_id as Limit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									";						
									
									$this->db->query($query, $resp_limitone);
								}					
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * Получение и сохранение указанной услуги для анализатора из Промед
	 */	
	function saveUslugaFromModelToAnalyzer($data)
	{
		//if (empty($data['Analyzer_id']) && !empty($response[0]['Analyzer_id']) && empty($data['equipment_id']) && !empty($data['AnalyzerModel_id']) && !empty($data['MedService_id'])) {
		if (!empty($data['AnalyzerModel_id']) && !empty($data['UslugaComplex_id'])) {
			$this->db->trans_begin();
			// 1. получаем услугу в моделях анализаторов
			$query = "
				select TOP 1
					at.AnalyzerTest_id,
					at.UslugaComplex_id as UslugaComplex_2011id,
					at.AnalyzerTest_Code,
					at.AnalyzerTest_Name,
					at.AnalyzerTest_SysNick,
					at.AnalyzerTestType_id,
					at.Unit_id,
					at.AnalyzerTest_IsTest
				from
					lis.v_AnalyzerTest at (nolock)
				where
					at.AnalyzerModel_id = :AnalyzerModel_id
					AND at.UslugaComplex_id = :UslugaComplex_id
					AND at.AnalyzerTest_IsTest = 2
			";
			$result = $this->db->query($query, $data);
				
			if ( is_object($result) ) {
				$resp = $result->result('array');
				foreach($resp as $respone) {//на сам деле имеет место только один элемент массива
					// копируем услугу на анализатор
					$respone['AnalyzerTest_pid'] = $data['AnalyzerTest_pid'];
					$respone['MedService_id'] = $data['MedService_id'];
					$respone['Analyzer_id'] = $data['Analyzer_id'];
					$respone['pmUser_id'] = $data['pmUser_id'];
					$respone['Server_id'] = $data['Server_id'];
					$respone['session'] = $data['session'];
					$savetest = $this->saveAnalyzerTestFromAnalyzerModel($respone);
					if (!empty($savetest[0]['AnalyzerTest_id'])) {
						// копируем аттрибуты
						$data['AnalyzerTest_idFrom'] = $respone['AnalyzerTest_id'];
						$data['AnalyzerTest_idTo'] = $savetest[0]['AnalyzerTest_id'];
						$this->copyAnalyzerTestAttr($data);
					}
				}
			}
			$this->db->trans_commit();
		}
	}
		
	/**
	 * Получение и сохранение услуг для анализатора из Промед
	 * (без тестов, приписанных к автоучету реактивов)
	 */	
	function getAndSaveUslugaCodesForAnalyzerModel($data)
	{
		$this->db->trans_begin();
		// 1. получаем услуги в моделях анализаторов
		$query = "
			select
				at.AnalyzerTest_id,
				at.UslugaComplex_id as UslugaComplex_2011id,
				at.AnalyzerTest_Code,
				at.AnalyzerTest_Name,
				at.AnalyzerTest_SysNick,
				at.AnalyzerTestType_id,
				at.Unit_id,
				at.AnalyzerTest_IsTest
			from
				lis.v_AnalyzerTest at (nolock)
				LEFT JOIN [lis].ReagentNormRate rnr ON rnr.AnalyzerTest_id = at.AnalyzerTest_id
			where
				at.AnalyzerModel_id = :AnalyzerModel_id
				AND at.AnalyzerTest_pid IS NULL
				AND rnr.ReagentNormRate_id IS NULL
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			// идём по всем услугам модели анализатора
			foreach($resp as $respone) {
				// копируем услугу на анализатор
				$respone['AnalyzerTest_pid'] = null;
				$respone['MedService_id'] = $data['MedService_id'];
				$respone['Analyzer_id'] = $data['Analyzer_id'];
				$respone['pmUser_id'] = $data['pmUser_id'];
				$respone['Server_id'] = $data['Server_id'];
				$respone['session'] = $data['session'];
				$savetest = $this->saveAnalyzerTestFromAnalyzerModel($respone);
				if (!empty($savetest[0]['AnalyzerTest_id'])) {
					// копируем аттрибуты
					$data['AnalyzerTest_idFrom'] = $respone['AnalyzerTest_id'];
					$data['AnalyzerTest_idTo'] = $savetest[0]['AnalyzerTest_id'];
					$this->copyAnalyzerTestAttr($data);
					
					// 2. получаем дочерние услуги
					$query_child = "
						select
							at.AnalyzerTest_id,
							at.UslugaComplex_id as UslugaComplex_2011id,
							at.AnalyzerTest_Code,
							at.AnalyzerTest_Name,
							at.AnalyzerTest_SysNick,
							at.AnalyzerTestType_id,
							at.Unit_id,
							at.AnalyzerTest_IsTest
						from
							lis.v_AnalyzerTest at (nolock)
						where
							at.AnalyzerModel_id = :AnalyzerModel_id
							and at.AnalyzerTest_pid = :AnalyzerTest_pid
					";
					$data['AnalyzerTest_pid'] = $respone['AnalyzerTest_id'];
					$result_child = $this->db->query($query_child, $data);
					if ( is_object($result_child) ) {
						$resp_child = $result_child->result('array');
						// идём по всем услугам модели анализатора
						foreach($resp_child as $respone_child) {
							// копируем услугу на анализатор
							$respone_child['AnalyzerTest_pid'] = $savetest[0]['AnalyzerTest_id'];
							$respone_child['MedService_id'] = $data['MedService_id'];
							$respone_child['Analyzer_id'] = $data['Analyzer_id'];
							$respone_child['pmUser_id'] = $data['pmUser_id'];
							$respone_child['Server_id'] = $data['Server_id'];
							$respone_child['session'] = $data['session'];
							$savetest_child = $this->saveAnalyzerTestFromAnalyzerModel($respone_child);
							if (!empty($savetest_child[0]['AnalyzerTest_id'])) {
								// копируем аттрибуты
								$data['AnalyzerTest_idFrom'] = $respone_child['AnalyzerTest_id'];
								$data['AnalyzerTest_idTo'] = $savetest_child[0]['AnalyzerTest_id'];
								$this->copyAnalyzerTestAttr($data);
							}
						}
					}
				}
			}
		}		
		$this->db->trans_commit();
	}
	
	/**
	 * Сохранение теста на анализатор из теста модели анализатора
	 */
	function saveAnalyzerTestFromAnalyzerModel($data)
	{
		// Теперь услуга строго по госту(refs #PROMEDWEB-9543)
		$UslugaComplex_id = $data['UslugaComplex_2011id'];
		
		$data['UslugaComplex_id'] = $UslugaComplex_id;
		// добавляем услуге атрибут "Лабораторно-диагностическая"
		$UslugaComplexAttribute_id = $this->addUslugaComplexAttr($data);
		if (empty($UslugaComplexAttribute_id))
			return ['Error_Msg' => 'Ошибка сохранения атрибута комплексной услуге'];
		
		$data['AnalyzerTest_id'] = null;
		$data['UslugaComplexMedService_id'] = null;
		$procedure = 'p_AnalyzerTest_ins';
		
		$data['MedService_id'] = $this->getFirstResultFromQuery("
			select top 1
				MedService_id
			from
				lis.v_Analyzer (nolock)
			where
				Analyzer_id = :Analyzer_id
		", $data);
				
		$data['UslugaComplexMedService_pid'] = $this->getFirstResultFromQuery("
			select top 1
				UslugaComplexMedService_id
			from
				lis.v_AnalyzerTest (nolock)
			where
				AnalyzerTest_id = :AnalyzerTest_pid
		", $data);
		
		if (empty($data['MedService_id'])) {
			$data['MedService_id'] = null;
		}
		
		if (empty($data['UslugaComplexMedService_pid'])) {
			$data['UslugaComplexMedService_pid'] = null;
		}

		// сначала услугу превращаем в связанную гостовскую услугу!
		$data['UslugaComplexToAdd_id'] = $data['UslugaComplex_id'];
		$UslugaComplex_2011id = $this->getFirstResultFromQuery("select UslugaComplex_2011id from v_UslugaComplex with (nolock) where UslugaComplex_id = :UslugaComplexToAdd_id", $data);
		if (!empty($UslugaComplex_2011id)) {
			$data['UslugaComplexToAdd_id'] = $UslugaComplex_2011id;
		}
		
		// 1. сначала ищем, может уже добавлена на службу такая услуга
		$query = "
			select
				ucm.UslugaComplexMedService_id
			from
				v_UslugaComplexMedService ucm (nolock)
			where
				ucm.UslugaComplex_id = :UslugaComplexToAdd_id
				and ucm.MedService_id = :MedService_id
				and ISNULL(ucm.UslugaComplexMedService_pid, 0) = ISNULL(:UslugaComplexMedService_pid, 0)
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp[0]['UslugaComplexMedService_id'];
			}
		}
		
		if (empty($data['UslugaComplexMedService_id'])) {
			$this->load->model('UslugaComplexMedService_model');
			$resp = $this->UslugaComplexMedService_model->doSave(array(
				'UslugaComplexMedService_id' => null,
				'scenario' => self::SCENARIO_DO_SAVE,
				'MedService_id' => $data['MedService_id'],
				'UslugaComplex_id' => $data['UslugaComplexToAdd_id'],
				'UslugaComplexMedService_pid' => $data['UslugaComplexMedService_pid'],
				'UslugaComplexMedService_begDT' => '@curDT',
				'UslugaComplexMedService_endDT' => null,
				'RefSample_id' => null,
				'LpuEquipment_id' => null,
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($resp['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp['UslugaComplexMedService_id'];
			}
		}
		
		
		if (empty($data['UslugaComplexMedService_id'])) {
			return array('Error_Msg' => 'Ошибка сохранения услуги на службе');
		}

		$q = "
			declare
				@AnalyzerTest_id bigint,
				@curdate datetime,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @curdate = dbo.tzGetDate();
			set @AnalyzerTest_id = :AnalyzerTest_id;
			exec lis." . $procedure . "
				@AnalyzerTest_id = @AnalyzerTest_id output,
				@AnalyzerTest_pid = :AnalyzerTest_pid,
				@AnalyzerModel_id = NULL,
				@Analyzer_id = :Analyzer_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@AnalyzerTest_IsTest = :AnalyzerTest_IsTest,
				@AnalyzerTestType_id = :AnalyzerTestType_id,
				@Unit_id = :Unit_id,
				@AnalyzerTest_Code = NULL,
				@AnalyzerTest_Name = :AnalyzerTest_Name,
				@AnalyzerTest_SysNick = :AnalyzerTest_SysNick,
				@AnalyzerTest_begDT = @curdate,
				@AnalyzerTest_endDT = null,
				@UslugaComplexMedService_id = :UslugaComplexMedService_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AnalyzerTest_id as AnalyzerTest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			return $r->result('array');
		}
		
		return false;
	}
	
	/**
	 * Получение и сохранение услуг для анализатора из ЛИС
	 */
	function getAndSaveUslugaCodesForEquipment($data)
	{
		$this->db->trans_begin();
		// 1. получаем коды услуг в ЛИС
		$query = "
			select distinct
				ta.code as target_code,
				ta.name as target_name,
				t.id as test_id,
				t.code as test_code,
				t.name as test_name,
				tm.code as test_sysnick,
				target_bio.code as RefMaterialTarget_Code,
				target_bio.name as RefMaterialTarget_Name,
				target_bio.mnemonics as RefMaterialTarget_SysNick
			from
				lis._test t (nolock)
				inner join lis._test_targets tt (nolock) on tt.test_id = t.id
				inner join lis._target ta (nolock) on ta.id = tt.target_id
				inner join lis._test_equipments e (nolock) on t.id = e.test_id
				outer apply(
					select top 1
						b.code,
						b.name,
						b.mnemonics
					from lis._biomaterial b (nolock)
						inner join lis._target_biomaterials tb (nolock) on tb.biomaterial_id = b.id
					where tb.target_id = ta.id
				) target_bio
				left join lis._testMappings tm (nolock) on tm.test_id = t.id and tm.equipment_id = e.equipment_id
			where
				e.equipment_id = :equipment_id and
				ta.id + '_' + t.id IN ('".implode("','",$data['Test_JSON'])."')
			order by
				ta.code,
				t.code
		";
		
		// echo getDebugSql($query, $data); die();
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			$lastTargetCode = null;
			$lastRefMaterialTestCode = null;
			$lastRefSample_id = null;
			// идём по всем услугам анализатора
			foreach($resp as $respone) {
				// добавляем услугу исследования
				$data['UslugaComplexTarget_id'] = $this->addUslugaComplexFromLis($data, $respone['target_code'], $respone['target_name']);
				if (!empty($data['UslugaComplexTarget_id'])) {
					// добавляем услугу исследования на службу
					// если услуга исследования = услуге теста, то создаём пробу и добавляем её услуге
					$data['RefSample_id'] = null;
					
					if ($lastTargetCode != $respone['target_code'] || $lastRefMaterialTestCode != $respone['RefMaterialTarget_Code']) {
						$lastTargetCode = $respone['target_code'];
						$lastRefMaterialTestCode = $respone['RefMaterialTarget_Code'];
						$data['RefMaterial_Code'] = $respone['RefMaterialTarget_Code'];
						$data['RefMaterial_Name'] = $respone['RefMaterialTarget_Name'];
						$data['RefMaterial_SysNick'] = $respone['RefMaterialTarget_SysNick'];
						$lastRefSample_id = $this->addRefSample($data);
					}
					
					$data['AnalyzerTest_IsTest'] = 1;
					// добавляем услугу исследования
					$data['AnalyzerTest_Code'] = null;
					$data['AnalyzerTest_SysNick'] = null;
					$data['UslugaComplexMedService_pid'] = $this->addUslugaComplexTargetMedService($data);
					$data['RefSample_id'] = $lastRefSample_id;
					// добавляем услугу теста
					$data['UslugaComplexTest_id'] = $this->addUslugaComplexFromLis($data, $respone['test_code'], $respone['test_name']);
					if (!empty($data['UslugaComplexTarget_id']) && !empty($data['UslugaComplexTest_id']))
					{
						// добавляем услугу теста в состав услуги исследования
						$this->addUslugaComplexTargetTestComposition($data);
						// добавляем услугу теста на службу в состав услуги исследования
						if (!empty($data['UslugaComplexMedService_pid'])) {
							$data['AnalyzerTest_Code'] = $respone['test_sysnick'];
							$data['AnalyzerTest_SysNick'] = null; // убрал по задаче #42668
							$this->addUslugaComplexTestMedService($data, $respone['test_id']);
						}
					}
				}
			}
		}
		$this->db->trans_commit();
	}
	
	/**
	 * Сохранение
	 */
	function save($data) {
		$procedure = 'p_Analyzer_ins';
		if (isset($data['Analyzer_id']) && $data['Analyzer_id'] > 0 ) {
			$procedure = 'p_Analyzer_upd';
		} else {
			$data['Analyzer_id'] = null;
		}
		
		if (isset($data['Analyzer_2wayComm']) && $data['Analyzer_2wayComm']) {
			$data['Analyzer_2wayComm'] = 2;
		} else {
			$data['Analyzer_2wayComm'] = 1;
		}
				
		if (isset($data['Analyzer_IsUseAutoReg']) && $data['Analyzer_IsUseAutoReg']) {
			$data['Analyzer_IsUseAutoReg'] = 2;
		} else {
			$data['Analyzer_IsUseAutoReg'] = 1;
		}
				
		if ($data['Analyzer_IsNotActive']) {
			$data['Analyzer_IsNotActive'] = 2;
		} else {
			$data['Analyzer_IsNotActive'] = 1;
		}

		if( !empty($data['AutoOkType']) ) {
			switch ($data['AutoOkType']) {
				case 1:
					$data['Analyzer_IsAutoOk'] = 2;
					$data['Analyzer_IsAutoGood'] = 1;
					break;
				case 2:
					$data['Analyzer_IsAutoOk'] = 2;
					$data['Analyzer_IsAutoGood'] = 2;
					break;
			}
		} else {
			$data['Analyzer_IsAutoOk'] = $data['Analyzer_IsAutoOk'] ? 2 : 1;
			$data['Analyzer_IsAutoGood'] = $data['Analyzer_IsAutoGood'] ? 2 : 1;
		}

		$data['Analyzer_IsManualTechnic'] = (isset($data['Analyzer_IsManualTechnic']) && $data['Analyzer_IsManualTechnic']) ? 2 : 1;
		
		if (!empty($data['Analyzer_id']) && !empty($data['Analyzer_endDT'])) {
			// при закрытии закрываем и все дочерние услугиы
			$query = "
				update
					lis.AnalyzerTest with (rowlock)
				set
					AnalyzerTest_endDT = :Analyzer_endDT
				where
					Analyzer_id = :Analyzer_id AND AnalyzerTest_endDT IS NULL
			";
			
			$this->db->query($query, $data);
		}
		
		$q = "
			declare
				@Analyzer_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Analyzer_id = :Analyzer_id;
			exec lis." . $procedure . "
				@Analyzer_id = @Analyzer_id output,
				@Analyzer_Name = :Analyzer_Name,
				@Analyzer_Code = :Analyzer_Code,
				@AnalyzerModel_id = :AnalyzerModel_id,
				@MedService_id = :MedService_id,
				@Analyzer_begDT = :Analyzer_begDT,
				@Analyzer_endDT = :Analyzer_endDT,
				@Analyzer_LisClientId = :Analyzer_LisClientId,
				@Analyzer_LisCompany = :Analyzer_LisCompany,
				@Analyzer_LisLab = :Analyzer_LisLab,
				@Analyzer_LisMachine = :Analyzer_LisMachine,
				@Analyzer_LisLogin = :Analyzer_LisLogin,
				@Analyzer_LisPassword = :Analyzer_LisPassword,
				@Analyzer_LisNote = :Analyzer_LisNote,
				@Analyzer_IsNotActive = :Analyzer_IsNotActive,
				@Analyzer_IsAutoOk = :Analyzer_IsAutoOk,
				@Analyzer_IsAutoGood = :Analyzer_IsAutoGood,
				@Analyzer_2wayComm = :Analyzer_2wayComm,
				@Analyzer_IsUseAutoReg = :Analyzer_IsUseAutoReg,
				@Analyzer_IsManualTechnic = :Analyzer_IsManualTechnic,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Analyzer_id as Analyzer_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			$result = $r->result('array');
						
			// Если при добавлении анализатора такой код уже сгенерирован
			if ( empty( $data[ 'Analyzer_id' ] ) ) { // только при добавлении
				$ccodes = 2;
				while( $ccodes > 1 ){ // проверяем пока код не станет нормальным
					$ccodes = $this->checkAnalyzerCode( $data );
					if ( $ccodes > 1 ) {
						// если уже есть анализатор с таким кодом, то надо проапдейтить наш код анализатора на вновь сгенерированный
						$result_update = $this->incAnalyzerCode( array( 'MedService_id' => $data[ 'MedService_id' ], 'Analyzer_id' => $result[ 0 ][ 'Analyzer_id' ] ) );
						if ( !$result_update ) {
							// Запишем ошибку в лог
							log_message( 'error', 'Error update Analyzer Code: Analyzer_id = '.$result[ 0 ][ 'Analyzer_id' ].' params: '.var_export( $data, true ) );
							$ccodes = 1; // если количество анализаторов с таким кодом два, но апдейт вернул ошибку, то нужно остановить это насилие
						}
					}
				}
			}
			
			$data['Analyzer_id'] = $result[0]['Analyzer_id'];
			if (!empty($data['equipment_id']))
			{
				$saveequip = $this->saveEquimpentLink(array(
					'Analyzer_id' => $data['Analyzer_id'],
					'equipment_id' => $data['equipment_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if ($saveequip === false) {
					$result = array(array('Error_Msg' => 'Ошибка при сохранении связи с анализатором ЛИС'));
				}
			}
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $data, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}
		
	/**
	 *  Возвращает количество анализаторов с указанным кодом 
	 *  Входной параметр: Analyzer_Code
	 */
	function checkAnalyzerCode($data)
	{
		$params = array('Analyzer_Code'=>$data['Analyzer_Code']);
		$sql = "
			SELECT count(*) as record_count
			FROM lis.v_Analyzer a (nolock)
			WHERE a.Analyzer_Code = :Analyzer_Code
			AND a.Analyzer_Code <> '000'
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$rc = $result->result('array');
			if (count($rc)>0 && is_array($rc[0])) {
				return $rc[0]['record_count'];
			}
		}
		return null;
	}
		
	/**
	 *  Меняет код для указанного анализатора на максимальный
	 *  Входные параметры: MedService_id, Analyzer_id
	 */
	function incAnalyzerCode($data)
	{
		$params = array('MedService_id'=>$data['MedService_id']);
		$resp = $this->getAnalyzerCode($params);
		$params = array();
		if (is_array($resp))
		{
			$params['Analyzer_Code'] = $resp[0]['Analyzer_Code'];
		}
		$params['Analyzer_id'] = $data['Analyzer_id'];

		$sql = "
			UPDATE lis.Analyzer with (rowlock)
			SET Analyzer_Code = :Analyzer_Code
			WHERE Analyzer_id = :Analyzer_id
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		// надо проверить что у анализатора нет услуг
		$q = "
			select top 1
				AnalyzerTest_id
			from
				lis.v_AnalyzerTest (nolock)
			where
				Analyzer_id = :Analyzer_id
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			$resp = $r->result('array');
			if (!empty($resp[0]['AnalyzerTest_id'])) {
				return array('Error_Msg' => 'Нельзя удалить анализатор пока на него заведены услуги');
			}
		}
		
		// надо проверить что анализатор не "ручные методики"
		$q = "
			select top 1
				Analyzer_Code
			from
				lis.v_Analyzer (nolock)
			where
				Analyzer_id = :Analyzer_id and pmUser_insID = 1 and Analyzer_Code = '000'
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			$resp = $r->result('array');
			if (!empty($resp[0]['Analyzer_Code'])) {
				return array('Error_Msg' => 'Нельзя удалить "Ручные методики"');
			}
		}
		
		$q = "
			set nocount on
			-- удаляем связи с анализаторами лис
			delete lis.Link with (ROWLOCK)
			where object_id = :Analyzer_id and link_object = 'Analyzer'

			declare
				@ErrCode int,
				@ErrMessage varchar(4000)
				
			exec lis.p_Analyzer_del
				@Analyzer_id = :Analyzer_id,
				@pmUser_delID = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
			
			set nocount off
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
		
	/**
	 *  Генерирует код анализатора
	 */
	function getAnalyzerCode($data = array())
	{
		$sql = "
			DECLARE 
			 @NewAnalyzerNum INT;

			SELECT
			 @NewAnalyzerNum = MAX(CAST( (case when isnumeric(a.Analyzer_Code) = 1 then RIGHT(a.Analyzer_Code, 2) else 0 end) AS INT))
			FROM lis.Analyzer a with(nolock)
			WHERE a.MedService_id = :MedService_id

			SELECT @NewAnalyzerNum AS NewAnalyzerNum, 
			 RIGHT('0000'+CAST(IsNull(s.MedService_Code,0) AS varchar(4)), 4) + RIGHT('00'+CAST(IsNull(@NewAnalyzerNum,0)+1 AS varchar(2)), 2) AS Analyzer_Code
			 ,s.MedService_id, s.MedService_Code
			FROM dbo.MedService s with(nolock)
			WHERE s.MedService_id = :MedService_id
		";
		$result = $this->db->query($sql, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Добавление аттрибута комплексной услуге
	 * @param $data
	 * @return bool|string
	 */
	function addUslugaComplexAttr($data)
	{
		$UslugaComplexAttribute_id = null;
		$query = "
			declare @UslugaComplexAttributeType_id bigint;
			set @UslugaComplexAttributeType_id = (select top 1 UslugaComplexAttributeType_id from v_UslugaComplexAttributeType (nolock) where UslugaComplexAttributeType_SysNick = 'lab');
			
			select
				uca.UslugaComplexAttribute_id
			from
				v_UslugaComplexAttribute uca (nolock)
			where
				uca.UslugaComplexAttributeType_id = @UslugaComplexAttributeType_id
				and uca.UslugaComplex_id = :UslugaComplex_id
		";

		$result = $this->db->query($query, array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
		));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['UslugaComplexAttribute_id'])) {
				$UslugaComplexAttribute_id = $resp[0]['UslugaComplexAttribute_id'];
			}
		}
		
		if (empty($UslugaComplexAttribute_id))
		{
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000),
					@UslugaComplexAttributeType_id bigint;
					
				set @Res = null;
				set @UslugaComplexAttributeType_id = (select top 1 UslugaComplexAttributeType_id from v_UslugaComplexAttributeType (nolock) where UslugaComplexAttributeType_SysNick = 'lab');

				exec p_UslugaComplexAttribute_ins
					@UslugaComplexAttribute_id = @Res output,
					@UslugaComplex_id = :UslugaComplex_id,
					@UslugaComplexAttributeType_id = @UslugaComplexAttributeType_id,
					@UslugaComplexAttribute_Float = null,
					@UslugaComplexAttribute_Int = null,
					@UslugaComplexAttribute_Text = null,
					@UslugaComplexAttribute_Value = null,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as UslugaComplexAttribute_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['UslugaComplexAttribute_id'])) {
					$UslugaComplexAttribute_id = $resp[0]['UslugaComplexAttribute_id'];
				}
			}
		}
		
		return $UslugaComplexAttribute_id;
	}

	/**
	 * Проверка, является ли служба, на которой заведен анализатор, внешней
	 */
	function checkIfFromExternalMedService($data) {
		$res = $this->getFirstResultFromQuery("
			select
				case when isnull(ms.MedService_IsExternal, 1) = 2
					then 'true'
					else 'false'
				end as isExternal
			from lis.v_Analyzer a with (nolock)
				inner join dbo.v_MedService ms with (nolock) on ms.MedService_id = a.MedService_id
			where Analyzer_id = :Analyzer_id
		", $data);

		return ['isExternal' => $res == 'true' ? true : false];
	}
}