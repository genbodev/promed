<?php
/**
* Модель - Электронная медицинская карта
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

if (IS_DEBUG)
{
	// Временная константы с Person_id пациентов для демонстрации на тестовом сервере
	define('TOTJMYANIN_ID',763567); // ИВАНОВ ИВАН ИВАНОВИЧ Д/р: 08.07.1941 
	define('PLESOVSKIH_ID',9020012861); // КРИВОШЕЕВ ПАВЕЛ Д/р: 21.02.1990
}
else
{
	// Временная константы с Person_id пациентов для демонстрации на рабочем сервере
	define('TOTJMYANIN_ID',479913);
	define('PLESOVSKIH_ID',656883);
} 

class EMK_model extends CI_Model
{
	/**
	 * Comment
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает данные для ноды случаи госпитализации
	 *{date_beg} - {date_end} / {Lpu_Name} / Госпитализация № {EvnPS_NumCard}
	 */
	function GetEvnPSNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("EvnPS_id"=>81,"EvnPS_NumCard"=>"А1221",'Lpu_Name'=>'ГКБ 4',"date_beg" => '26.01.2011', 'date_end' => ''));
		}
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("EvnPS_id"=>80,"EvnPS_NumCard"=>"А882",'Lpu_Name'=>'ГКБ 4',"date_beg" => '19.01.2011', 'date_end' => ''));
		}
		return array();
	}

	/**
	 * Возвращает данные для ноды движения по отделениям
	 * {date_beg} - {date_end} / {Lpu_Name} / {LpuSection_Name} / {Diag_Code} {Diag_Name}
	 */
	function GetEvnSectionNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("EvnSection_id"=>81,'Diag_Code'=>'I70.2','Diag_Name'=>'Атеросклероз артерий конечностей','Lpu_Name'=>'ГКБ 4','LpuSection_Name'=>'Отделение сосудистой хирургии',"date_beg" => '26.01.2011','date_end' => ''));
		}
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("EvnSection_id"=>80,'Diag_Code'=>'I70.2','Diag_Name'=>'Атеросклероз артерий конечностей','Lpu_Name'=>'ГКБ 4','LpuSection_Name'=>'Отделение сосудистой хирургии',"date_beg" => '19.01.2011','date_end' => ''));
		}
		return array();
	}

	/**
	 * Возвращает данные для ноды документы случая госпитализации
	 *{date} / {EvnDocument_Name}
	 */
	function GetEvnDocumentNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("EvnDocument_id"=>81,"EvnDocument_Name"=>"Информированное добровольное согласие","date" => '02.02.2011'));
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("EvnDocument_id"=>80,"EvnDocument_Name"=>"Согласие на рентгенохирургическое вмешательство","date" => '21.01.2011'),array("EvnDocument_id"=>79,"EvnDocument_Name"=>"Информированное добровольное согласие","date" => '24.01.2011'));
		}
		return array();
	}

	/**
	 * Возвращает данные для нод с результатами исследований
	 * {date} {Research_Name} № {Research_Num} {MedPersonal_Fin}
	 * - Лабораторная диагностика
	 */
	function GetLabDiagnosticNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(
				array("Research_id"=>4747,'ResearchType_id'=>3,"Research_Num"=>"4747","Research_Name"=>"КЛИНИЧЕСКИЙ АНАЛИЗ КРОВИ",'MedPersonal_Fin'=>'',"date" => '27.01.2011'),
				array("Research_id"=>3381,'ResearchType_id'=>3,"Research_Num"=>"3381","Research_Name"=>"АНАЛИЗ КРОВИ НА КОАГУЛОГРАММУ",'MedPersonal_Fin'=>'',"date" => '28.01.2011'),
				array("Research_id"=>3299,'ResearchType_id'=>3,"Research_Num"=>"3299","Research_Name"=>"БИОХИМИЧЕСКИЙ АНАЛИЗ КРОВИ",'MedPersonal_Fin'=>'',"date" => '28.01.2011'),
				array("Research_id"=>5726,'ResearchType_id'=>3,"Research_Num"=>"5726","Research_Name"=>"КЛИНИЧЕСКИЙ АНАЛИЗ КРОВИ",'MedPersonal_Fin'=>'',"date" => '28.01.2011'),
				array("Research_id"=>4997,'ResearchType_id'=>3,"Research_Num"=>"4997","Research_Name"=>"ОБЩИЙ АНАЛИЗ МОЧИ",'MedPersonal_Fin'=>'',"date" => '28.01.2011')
			);
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(
				array("Research_id"=>2045,'ResearchType_id'=>3,"Research_Num"=>"2045","Research_Name"=>"АНАЛИЗ КРОВИ НА КОАГУЛОГРАММУ",'MedPersonal_Fin'=>'',"date" => '20.01.2011'),
				array("Research_id"=>2134,'ResearchType_id'=>3,"Research_Num"=>"2134","Research_Name"=>"БИОХИМИЧЕСКИЙ АНАЛИЗ КРОВИ",'MedPersonal_Fin'=>'',"date" => '20.01.2011'),
				array("Research_id"=>3205,'ResearchType_id'=>3,"Research_Num"=>"3205","Research_Name"=>"КЛИНИЧЕСКИЙ АНАЛИЗ КРОВИ",'MedPersonal_Fin'=>'',"date" => '20.01.2011'),
				array("Research_id"=>3163,'ResearchType_id'=>3,"Research_Num"=>"3163","Research_Name"=>"ОБЩИЙ АНАЛИЗ МОЧИ",'MedPersonal_Fin'=>'',"date" => '20.01.2011'),
				array("Research_id"=>3557,'ResearchType_id'=>3,"Research_Num"=>"3557","Research_Name"=>"ГЛЮКОЗА КРОВИ",'MedPersonal_Fin'=>'',"date" => '24.01.2011'),
				array("Research_id"=>2556,'ResearchType_id'=>3,"Research_Num"=>"2556","Research_Name"=>"ГЛЮКОЗА КРОВИ",'MedPersonal_Fin'=>'',"date" => '31.01.2011'),
				array("Research_id"=>5401,'ResearchType_id'=>3,"Research_Num"=>"5401","Research_Name"=>"ОБЩИЙ АНАЛИЗ МОЧИ",'MedPersonal_Fin'=>'',"date" => '31.01.2011'),
				array("Research_id"=>3560,'ResearchType_id'=>3,"Research_Num"=>"3560","Research_Name"=>"БИОХИМИЧЕСКИЙ АНАЛИЗ КРОВИ",'MedPersonal_Fin'=>'',"date" => '31.01.2011'),
				array("Research_id"=>5412,'ResearchType_id'=>3,"Research_Num"=>"5412","Research_Name"=>"АНАЛИЗ МОЧИ НА САХАР",'MedPersonal_Fin'=>'',"date" => '31.01.2011'),
				array("Research_id"=>5410,'ResearchType_id'=>3,"Research_Num"=>"5410","Research_Name"=>"АНАЛИЗ МОЧИ НА САХАР",'MedPersonal_Fin'=>'',"date" => '31.01.2011'),
				array("Research_id"=>5409,'ResearchType_id'=>3,"Research_Num"=>"5409","Research_Name"=>"АНАЛИЗ МОЧИ НА САХАР",'MedPersonal_Fin'=>'',"date" => '31.01.2011'),
				array("Research_id"=>3459,'ResearchType_id'=>3,"Research_Num"=>"3459","Research_Name"=>"АНАЛИЗ КРОВИ НА КОАГУЛОГРАММУ",'MedPersonal_Fin'=>'',"date" => '31.01.2011')
			);
		}
		return array();
	}
	/**
	 * Возвращает данные для нод с результатами исследований
	 * {date} {Research_Name} № {Research_Num} {MedPersonal_Fin}
	 * - Лучевая диагностика
	 */
	function GetRadioDiagnosticNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Research_id"=>93,'ResearchType_id'=>2,"Research_Num"=>"93","Research_Name"=>"АНГИОГРАФИЧЕСКОЕ ИССЛЕДОВАНИЕ", 'MedPersonal_Fin'=>'Афанасьев О.А.',"date" => '25.01.2011'), array("Research_id"=>966,'ResearchType_id'=>2,"Research_Num"=>"C966","Research_Name"=>"РЕНТГЕНОЛОГИЧЕСКОЕ ИССЛЕДОВАНИЕ", 'MedPersonal_Fin'=>'Половинченко Л.А.',"date" => '26.01.2011'));
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Research_id"=>75,'ResearchType_id'=>2,"Research_Num"=>"75","Research_Name"=>"АНГИОГРАФИЧЕСКОЕ ИССЛЕДОВАНИЕ", 'MedPersonal_Fin'=>'Каракулов О.Г.',"date" => '21.01.2011'));
		}
		return array();
	}
	/**
	 * Возвращает данные для нод с результатами исследований
	 * {date} {Research_Name} № {Research_Num} {MedPersonal_Fin}
	 * - Инструментальная диагностика
	 */
	function GetInstrDiagnosticNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Research_id"=>1543,'ResearchType_id'=>1,"Research_Num"=>"1543","Research_Name"=>"ЭЛЕКТРОКАРДИОГРАФИЯ",'MedPersonal_Fin'=>'Самикулина Е.Р.',"date" => '25.01.2011') , array("Research_id"=>1912,'ResearchType_id'=>1,"Research_Num"=>"1912","Research_Name"=>"ЭЛЕКТРОКАРДИОГРАФИЯ",'MedPersonal_Fin'=>'Грибанова Т.М.',"date" => '28.01.2011'));
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Research_id"=>1995,'ResearchType_id'=>1,"Research_Num"=>"1995","Research_Name"=>"ЭЛЕКТРОКАРДИОГРАФИЯ",'MedPersonal_Fin'=>'Грибанова Т.М.',"date" => '31.01.2011'));
		}
		return array();
	}
	
	/**
	 * Возвращает данные для ноды ПРОТОКОЛ ОПЕРАЦИИ
	 * {date} {time} ПРОТОКОЛ ОПЕРАЦИИ № {ProtocolSurgery_Num} {SurgeryType_Name}. DS: {Diag_Code} {Diag_Name} ЛПУ: {Lpu_Name}, {LpuSection_Name} 
	 */
	function GetProtocolSurgeryNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("ProtocolSurgery_id"=>76,"ProtocolSurgery_Num"=>"76","SurgeryType_Name"=>"ПЛАНОВАЯ",'Diag_Code'=>'I70.2','Diag_Name'=>'Атеросклероз артерий конечностей','Lpu_Name'=>'ГКБ-4','LpuSection_Name'=>'Отделение сосудистой хирургии',"date" => '28.01.2011', 'time' => '13:00'));
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("ProtocolSurgery_id"=>68,"ProtocolSurgery_Num"=>"68","SurgeryType_Name"=>"ПЛАНОВАЯ",'Diag_Code'=>'I65.3','Diag_Name'=>' Закупорка и стеноз множественных и двусторонних прецеребр. артерий','Lpu_Name'=>'ГКБ-4','LpuSection_Name'=>'Отделение сосудистой хирургии',"date" => '25.01.2011', 'time' => '12:00'), array("ProtocolSurgery_id"=>75,"ProtocolSurgery_Num"=>"75","SurgeryType_Name"=>"ПЛАНОВАЯ",'Diag_Code'=>'I70.2','Diag_Name'=>'Атеросклероз артерий конечностей','Lpu_Name'=>'ГКБ-4','LpuSection_Name'=>'Отделение сосудистой хирургии',"date" => '27.01.2011', 'time' => '09:40'));
		}
		return array();
	}
	
	/**
	 * Возвращает данные для ноды Осмотры (консультации). Содержит список осмотров в формате:
	 * <Дата> <Время> <Вид осмотра: справочник> <Код диагноза + диагноз> <Профиль осмотра> <ФИО врача>
	 * {date} {time} {MedicalCheckupType_Name} {Diag_Code} {Diag_Name} {MedicalCheckupProfil_Name} {MedPersonal_Fin}
	 */
	function GetMedicalCheckupNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("MedicalCheckup_id"=>75,"MedicalCheckupType_Name"=>"ОСМОТР КАРДИОЛОГА",'Diag_Code'=>'','Diag_Name'=>'ИБС. Перенесенный инфаркт миокарда (2006 г.). Стенокардия II ФК. Гипертоническая болезнь III ст., риск IV ст. ХСН II ФК.','MedPersonal_Fin'=>'Запьянцев В.А.',"date" => '24.01.2011', 'time' => '13:00',"MedicalCheckupProfil_Name"=>""));
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("MedicalCheckup_id"=>70,"MedicalCheckupType_Name"=>"ОСМОТР НЕВРОЛОГА",'Diag_Code'=>'I69.3','Diag_Name'=>'Последствия инфаркта мозга','MedPersonal_Fin'=>'Чекуров Е.А.',"date" => '19.01.2011', 'time' => '11:00',"MedicalCheckupProfil_Name"=>""),array("MedicalCheckup_id"=>71,"MedicalCheckupType_Name"=>"ОСМОТР КАРДИОЛОГА",'Diag_Code'=>'M28.6','Diag_Name'=>'Пролапс митрального клапана I ст.','MedPersonal_Fin'=>'Корнеевский М.Ю.',"date" => '24.01.2011', 'time' => '13:00',"MedicalCheckupProfil_Name"=>""),array("MedicalCheckup_id"=>72,"MedicalCheckupType_Name"=>"ОСМОТР НЕВРОЛОГА",'Diag_Code'=>'I69.3','Diag_Name'=>'Последствия инфаркта мозга','MedPersonal_Fin'=>'Шевцов К.В',"date" => '25.01.2011', 'time' => '20:00',"MedicalCheckupProfil_Name"=>""),array("MedicalCheckup_id"=>74,"MedicalCheckupType_Name"=>"ОСМОТР ТЕРАПЕВТА",'Diag_Code'=>'GP.98','Diag_Name'=>'Гипертоническая болезнь III ст., 3 ст., риск IV ст. ХСН I ФК. ЦВБ','MedPersonal_Fin'=>'Селезнева В.И.',"date" => '01.02.2011', 'time' => '14:00',"MedicalCheckupProfil_Name"=>""));
		}
		return array();
	}

	/**
	 * Возвращает данные для ноды Эпикризы.
	 * l.Содержит список эпикризов, оформленных за период данной госпитализации. Формат:
	 * {date} {time} {EpicrisisType_Name} {MedPersonal_Fin}
	 */
	function GetEpicrisisNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Epicrisis_id" => 81,"EpicrisisType_Name" => "Ангиографический эпикриз",'MedPersonal_Fin'=>'Ташкинов А.Л.',"date" => '01.02.2011', 'time' => '8:28'),array("Epicrisis_id" => 82,"EpicrisisType_Name" => "Предоперационный эпикриз",'MedPersonal_Fin'=>'Ташкинов А.Л.',"date" => '02.02.2011', 'time' => '10:28'),array("Epicrisis_id" => 83,"EpicrisisType_Name" => "Выписной эпикриз",'MedPersonal_Fin'=>'',"date" => '', 'time' => ''));
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Epicrisis_id" => 80,"EpicrisisType_Name" => "Ангиографический эпикриз",'MedPersonal_Fin'=>'Сятчихин А.В.',"date" => '19.01.2011', 'time' => '19:28'),array("Epicrisis_id" => 79,"EpicrisisType_Name" => "Предоперационный эпикриз",'MedPersonal_Fin'=>'Курников Д.В.',"date" => '25.01.2011', 'time' => '9:28'),array("Epicrisis_id" => 78,"EpicrisisType_Name" => "Предоперационный эпикриз",'MedPersonal_Fin'=>'Курников Д.В.',"date" => '27.01.2011', 'time' => '17:28'));
		}
		return array();
	}

	/**
	 * Возвращает данные для ноды Запись врача при поступлении (первичный осмотр)
	 */
	function GetPrimaryMedViewNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Name" => "Запись врача при первоначальном осмотре","MedHisRecordReceptionist_id" => 81, 'order' => 71111));
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Name" => "Запись врача при первоначальном осмотре","MedHisRecordReceptionist_id" => 80, 'order' => 71111));
		}
		return array();
	}
	/**
	* Возвращает данные для ноды Запись врача приемного отделения
	*/
	function GetMedHisRecordReceptionistNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Name" => "Запись врача приемного отделения","MedHisRecordReceptionist_id" => 81, 'order' => 71111));
		}
		// на тестовом сервере показываем данные Плесовского для тестирования
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Name" => "Запись врача приемного отделения","MedHisRecordReceptionist_id" => 80, 'order' => 71111));
		}
		return array();
	}

	/**
	* Возвращает данные для ноды Антропометрические данные
	*/
	function GetAnthropometryNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Name" => "Антропометрические данные","Anthropometry_id" => 81,'order' => 20));
		}
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Name" => "Антропометрические данные","Anthropometry_id" => 80,'order' => 20));
		}
		return array();
	}

	/**
	* Возвращает данные для ноды Группа крови и резус-фактор
	*/
	function GetBloodDataNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Name" => "Группа крови и резус-фактор","BloodData_id" => 81,'order' => 19));
		}
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Name" => "Группа крови и резус-фактор","BloodData_id" => 80,'order' => 19));
		}
		return array();
	}
	
	/**
	* Возвращает данные для ноды Аллергологический анамнез
	*/
	function GetAllergHistoryNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Name" => "Аллергологический анамнез","AllergHistory_id" => 81, 'order' => 10));
		}
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Name" => "Аллергологический анамнез","AllergHistory_id" => 80, 'order' => 10));
		}
		return array();
	}

	/**
	* Возвращает данные для ноды Анамнез жизни
	*/
	function GetLifeHistoryNodeList($data)
	{
		if (TOTJMYANIN_ID == $data['Person_id'])
		{
			return array(array("Name" => "Анамнез жизни","LifeHistory_id" => 81, 'order' => 10));
		}
		if (PLESOVSKIH_ID == $data['Person_id'])
		{
			return array(array("Name" => "Анамнез жизни","LifeHistory_id" => 80, 'order' => 10));
		}
		return array();
	}

	/**
	* Возвращает общие данные группы узлов
	* 
	*/
	function getGroupNodeData($data)
	{
		// Array('object' => "EvnDirection", 'id' => "EvnDirection_id", 'name' => "Name", 'iconCls' => 'direction-16', 'leaf' => true, 'cls' => "folder");
		$query = "
			select top 1
				GroupNode_id,
				GroupNode_Code,
				GroupNode_Name as node_name,
				GroupNode_object as 'object',
				GroupNode_objectid as 'object_id',
				GroupNode_objectName as 'object_name',
				GroupNode_modelName as model_name,
				GroupNode_modelMethod as model_method,
				GroupNode_iconCls as iconCls,
				GroupNode_isLeaf,
				GroupNode_cls as cls,
				GroupNode_isHidden,
				GroupNode_NameView,
				GroupNode_FieldOrder
			from
				v_TreeViewEMK with (nolock)
			where
				GroupNode_Code = :GroupNode_Code
				AND TreeViewEMK_Level = :TreeViewEMK_Level
				AND (TreeViewEMK_ARMType = :TreeViewEMK_ARMType OR TreeViewEMK_ARMType = 'common')
				AND TreeViewEMK_GroupType = :TreeViewEMK_GroupType
		";

		/*
		if ($data['type'] == 0 AND $data['node'] == 'root')
		{
			$data['level'] = 1;
		}
		*/
		$queryParams = array(
			'GroupNode_Code' => $data['object'],
			'TreeViewEMK_Level' => $data['level'],
			'TreeViewEMK_ARMType' => $data['ARMType'],
			'TreeViewEMK_GroupType' => $data['type']
		);

		$result = $this->db->query($query, $queryParams);

		if ( ! is_object($result) )
		{
			return false;
		}
		$group_node = $result->result('array');
		if ($data['node'] == 'root')
		{
			$group_node[0]['person_id'] = $data['Person_id'];
		}
		if (isset($group_node[0]) AND isset($group_node[0]['model_name']) AND !empty($group_node[0]['model_method']) )
		{
			return $group_node[0];
		}
		//
		//var_dump($group_node);
		//echo getDebugSQL($query, $queryParams);
		return false;
	}

	/**
	* Возвращает данные дочерних узлов первого уровня дерева
	*
	* Выбирает дочек узла root в зависимости от
	* 1) типа АРМа
	* 2) типа группировки
	*/
	function getMainNodeList($data)
	{
		//$grouptype[] = array('id'=>11109, 'object' =>'PersonDocuments', 'text' => 'Документы и справки');
		$query = "
			select
				GroupNode_id,
				GroupNode_Code,
				TreeViewEMK_Order as 'order',
				GroupNode_Name as 'text',
				GroupNode_object as 'object',
				GroupNode_objectid as 'id'
			from
				v_TreeViewEMK with (nolock)
			where
				GroupNode_isHidden != 2
				AND ParentNode_Code = :ParentNode_Code
				AND TreeViewEMK_Level = :TreeViewEMK_Level
				AND (TreeViewEMK_ARMType = :TreeViewEMK_ARMType OR TreeViewEMK_ARMType = 'common')
				AND TreeViewEMK_GroupType = :TreeViewEMK_GroupType
			order by
				TreeViewEMK_Order DESC
		";

		$queryParams = array(
			'ParentNode_Code' => $data['object'],
			'TreeViewEMK_Level' => ($data['level']+1),
			'TreeViewEMK_ARMType' => $data['ARMType'],
			'TreeViewEMK_GroupType' => $data['type']
		);

		$result = $this->db->query($query, $queryParams);

		if ( ! is_object($result) )
		{
			// echo getDebugSQL($query, $queryParams); 
			return false;
		}
		$childrens = $result->result('array');
		

		return $childrens;
	}
	
	/**
	 * получить последний протокол BK для ВМП
	 */
	function getLastDirectionVKforVMP($data){
		$query = "
			SELECT TOP 1
				EVK.EvnVK_id,
				EVK.EvnVK_IsArchive,
				EVK.Diag_id,
				EPVK.EvnPrescrVK_pid,
				EVK.EvnVK_setDT,
				EVK.EvnVK_NumProtocol,
				convert(varchar(10), EVK.EvnVK_setDT, 104) as EvnVK_setDT,
				EVK.ExpertiseNameType_id,
				EVK.ExpertiseEventType_id,
				EvnVK_IsFail,
				EVK.PersonEvn_id,
				EVK.Server_id,
				PA.Person_Fio as Person_Fio,
				PA.Person_SurName,
				PA.Person_Firname,
				PA.Person_Secname,
				EVK.Person_id,
				convert(varchar(10), PA.Person_BirthDay, 104) as Person_BirthDay
			FROM v_EvnVK EVK with(nolock)
				LEFT JOIN v_EvnPrescrVK EPVK with(nolock) on EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
				left join v_EvnDirectionHTM EDH with(nolock) on EDH.EvnDirectionHTM_pid = EVK.EvnVK_id
				left join v_Person_all PA with(nolock) on PA.Person_id = EVK.Person_id
			WHERE 
				EVK.Person_id = :Person_id
				AND ISNULL(EVK.EvnVK_IsArchive, 1) = 1
				AND ISNULL(EVK.EvnVK_IsFail, 1) != 2
				AND EVK.ExpertiseNameType_id = 5
				AND EVK.ExpertiseEventType_id = 61
				AND EDH.EvnDirectionHTM_id IS NULL
				AND EVK.Lpu_id = :Lpu_id
			ORDER BY EVK.EvnVK_setDT DESC
		";

		$result = $this->db->query($query, $data);

		if ( ! is_object($result) )
		{
			echo getDebugSQL($query, $data); 
			return false;
		}
		$res = $result->result('array');
		$result = array('data' => $res);
		return $result;
	}

}

?>
