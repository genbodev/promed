<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnUslugaDispDop - контроллер для работы с услугами дд
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      11.07.2013
 * 
 * @property EvnUslugaDispDop_model $dbmodel
 */

class EvnUslugaDispDop extends swController {

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnUslugaDispDop_model', 'dbmodel');
		
		$this->inputRules = array(
			'loadMedSpecOmsList' => array(
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrpDispSpec_id',
					'label' => 'Специальность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Тип диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadLpuSectionProfileList' => array(
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrpDispSpec_id',
					'label' => 'Специальность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Тип диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveEvnUslugaDispDop' => array(
				array(
					'field' => 'ExtVersion',
					'label' => 'Версия Ext',
					'rules' => '',
					'type' => 'int',
					'default' => 2
				),
				array(
					'field' => 'EvnDiagDopDispGridData',
					'label' => 'Сопутствующие диагнозы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVizitDispDop_pid',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DopDispInfoConsent_id',
					'label' => 'Идентификатор записи из списка добровольного информированного согласия',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaDispDop_ExamPlace',
					'label' => 'Место проведения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispDop_setDate',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'				
				),
				array(
					'field' => 'EvnUslugaDispDop_setTime',
					'label' => 'Время',
					'rules' => '',
					'type' => 'time'				
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaDispDop_didDate',
					'label' => 'Дата начала выполнения',
					'rules' => '',
					'type' => 'date'				
				),
				array(
					'field' => 'EvnUslugaDispDop_didTime',
					'label' => 'Время начала выполнения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispDop_disDate',
					'label' => 'Дата окончания выполнения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaDispDop_disTime',
					'label' => 'Время окончания выполнения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ExaminationPlace_id',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedSpecOms_id',
					'label' => 'Специальность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Рабочее место врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_Code',
					'label' => 'Код диагноза',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field'	=> 'TumorStage_id',
					'label'	=> 'Стадия выявленного ЗНО',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field' => 'DopDispDiagType_id',
					'label' => 'Характер заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaDispDop_Result',
					'label' => 'Результат',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DeseaseStage',
					'label' => 'Стадия',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'CytoExaminationPlace_id',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CytoLpu_id',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CytoLpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CytoLpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CytoMedSpecOms_id',
					'label' => 'Специальность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CytoMedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CytoMedStaffFact_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CytoEvnUsluga_setDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'CytoUslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Cyto_IsNotAgree',
					'label' => 'Отказ от цитологического исследования',
					'rules' => '',
					'type' => 'checkbox'
				),array(
					'label' => 'Пункт обслуживания',
					'field' => 'ElectronicService_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreCheckMorbusOnko',
					'label' => 'Признак игнорирования проверки перед удалением специфики',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'isOnkoDiag',
					'label' => 'Признак игнорирования проверки перед удалением специфики',
					'rules' => '',
					'type' => 'int'
				),
				// куча различных полей результатов
				array(
					'label' => 'Систолическое АД (мм рт.ст.)',
					'field' => 'systolic_blood_pressure',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Диастолическое АД (мм рт.ст.)',
					'field' => 'diastolic_blood_pressure',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Вес (кг)',
					'field' => 'person_weight',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Рост (см)',
					'field' => 'person_height',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Окружность талии (см)',
					'field' => 'waist_circumference',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Индекс массы тела (кг/м2)',
					'field' => 'body_mass_index',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Общий холестерин (ммоль/л)',
					'field' => 'total_cholesterol',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Глюкоза (ммоль/л)',
					'field' => 'glucose',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Давление (правый глаз)',
					'field' => 'eye_pressure_right',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Давление (левый глаз)',
					'field' => 'eye_pressure_left',
					'rules' => '',
					'type' => 'string'
				),
				/*
				array(
					'label' => 'Признак "Норма / повышенное"',
					'field' => 'eye_pressure_increase',
					'rules' => '',
					'type' => 'int'
				),
				*/
				array(
					'label' => 'Число эритроцитов',
					'field' => 'number_erythrocytes',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Гемоглобин (г/л)',
					'field' => 'cln_blood_gem',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Гематокрит (%)',
					'field' => 'hematocrit',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Ширина распределения эритроцитов (%)',
					'field' => 'distribution_width_erythrocytes',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Средний объем эритроцита (фл)',
					'field' => 'volume_erythrocyte',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Среднее содержание гемоглобина в эритроците (пг)',
					'field' => 'hemoglobin_content',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Средняя концетрация гемоглобина в эритроците (г/л)',
					'field' => 'concentration_hemoglobin',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Число тромбоцитов (х 10 в 9  степени/л)',
					'field' => 'cln_blood_trom',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Число лейкоцитов (х 10 в 9  степени/л)',
					'field' => 'cln_blood_leyck',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Содержание лимфоцитов (%)',
					'field' => 'lymphocyte_content',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Содержание смеси моноцитов',
					'field' => 'contents_mixture_monocit',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Содержание смеси эозинофилов',
					'field' => 'contents_mixture_eozinofil',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Содержание смеси базофилов',
					'field' => 'contents_mixture_bazofil',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Содержание смеси незрелых клеток (%)',
					'field' => 'contents_mixture_nezrelklet',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Количество гранулоцитов (%)',
					'field' => 'granulocytes',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Количество моноцитов (%)',
					'field' => 'number_monocytes',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Скорость оседания эритроцитов (мм/ч)',
					'field' => 'erythrocyte_sedimentation_rate',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Количество (л)',
					'field' => 'amount_urine',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Белок (г/л)',
					'field' => 'cln_urine_protein',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Глюкоза (ммоль/л)',
					'field' => 'glucose',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Альбумины (г/л)',
					'field' => 'albumin',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Креатинин (ммоль/л)',
					'field' => 'bio_blood_kreatinin',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Билирубин общий (мкмоль/л)',
					'field' => 'bio_blood_bili',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'АсАт (аспартат-аминотрансаминазы) (ммоль/л)',
					'field' => 'AsAt',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'АлАт (аланин-аминотрансаминазы) (ммоль/л)',
					'field' => 'AlAt',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Фибриноген (г/л)',
					'field' => 'fibrinogen',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Калий (ммоль/л)',
					'field' => 'potassium',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Натрий (ммоль/л)',
					'field' => 'sodium',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Уровень',
					'field' => 'antigen_blood',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Результат',
					'field' => 'positive_result',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Сонографические признаки онкологических заболеваний',
					'field' => 'sonographic_signs',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Патология',
					'field' => 'pathology_found',
					'rules' => '',
					'type' => 'int'
				),
				// https://redmine.swan.perm.ru/issues/22155
				array(
					'label' => 'Кол-во мочи',
					'field' => 'amount_urine_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Удельный вес',
					'field' => 'specific_weight_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Белок',
					'field' => 'cln_urine_protein_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Сахар',
					'field' => 'cln_urine_sugar_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Ацетон',
					'field' => 'urine_acetone_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Билирубин',
					'field' => 'urine_bili_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Уробилин',
					'field' => 'urine_urobili_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Эритроциты',
					'field' => 'cln_urine_erit_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Лейкоциты',
					'field' => 'cln_urine_leyck_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Цилиндры гиалиновые',
					'field' => 'urine_hyal_cylin_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Цилиндры зернистые',
					'field' => 'urine_gran_cylin_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Цилиндры восковидные',
					'field' => 'urine_waxy_cylin_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Эпителий',
					'field' => 'urine_epit_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Эпителий почечный',
					'field' => 'urine_epit_kidney_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Эпителий плоский',
					'field' => 'urine_epit_flat_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Слизь',
					'field' => 'urine_mucus_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Соли',
					'field' => 'urine_salt_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Бактерии',
					'field' => 'urine_bact_s',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Цвет',
					'field' => 'color',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'рН',
					'field' => 'ph',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Запах',
					'field' => 'odour',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Плотность (г/л)',
					'field' => 'density',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Прозрачность',
					'field' => 'transparent',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Туберкулез',
					'field' => 'migrant_tub',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Принадлежность к декретированным группам',
					'field' => 'migrant_tub_decr',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Сроки предыдущего ФГ обследования',
					'field' => 'migrant_prev_fg',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Дата первого обращения за медицинской помощью',
					'field' => 'migrant_tub_first_dt',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'label' => 'Дата взятия на учет в противотуберкулезное учреждение',
					'field' => 'migrant_tub_take_dt',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'label' => 'Группа наблюдения',
					'field' => 'migrant_tub_group',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Метод выявления',
					'field' => 'migrant_tub_method',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Наличие распада',
					'field' => 'migrant_tub_decay',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Подтверждение бактериовыделения',
					'field' => 'migrant_tub_bac',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Метод подтверждения бактериовыделения',
					'field' => 'migrant_tub_bac_method',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Сопутствующие заболевания',
					'field' => 'migrant_tub_morbus',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Состоит на учете в наркологическом диспансере',
					'field' => 'migrant_tub_narko',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Сифилис',
					'field' => 'migrant_syphilis',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Наркологическое расстройство',
					'field' => 'migrant_narko',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Название и серия диагностикума на ВИЧ-инфекцию',
					'field' => 'migrant_HIV_diagn',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'ВИЧ-инфекция',
					'field' => 'migrant_HIV',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Лепра',
					'field' => 'migrant_HIV_lepr',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'АТ к ВИЧ-1, ВИЧ-2',
					'field' => 'migrant_HIV_at1_at2',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'АТ к ВИЧ-1',
					'field' => 'migrant_HIV_at1',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'АТ к ВИЧ-2',
					'field' => 'migrant_HIV_at2',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Диагностика сифилиса (ИФА)',
					'field' => 'migrant_syphilis_ifa',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Диагностика сифилиса (РПГА)',
					'field' => 'migrant_syphilis_rpga',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Диагностика сифилиса (РМП)',
					'field' => 'migrant_syphilis_rmp',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Флюорография легких',
					'field' => 'migrant_fluoro',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Проба Манту',
					'field' => 'migrant_mantu',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Проба с аллергеном туберкулезным рекомбинантным',
					'field' => 'migrant_allergen',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Результат на амфетамин',
					'field' => 'migrant_urine_amphet',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Результат на марихуану',
					'field' => 'migrant_urine_marij',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Результат на морфин',
					'field' => 'migrant_urine_morp',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' =>'Результат на кокаин',
					'field' => 'migrant_urine_cocaine',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Результат на метамфетамин',
					'field' => 'migrant_urine_meth',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Противопоказания к управлению ТС',
					'field' => 'driver_result',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Туберкулиновая проба',
					'field' => 'migrant_Tub_Probe',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Размер (мм)',
					'field' => 'migrant_tub_size',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Суммарный сердечно-сосудистый риск',
					'field' => 'EvnPLDispDop13_SumRick',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Тип сердечно-сосудистого риска',
					'field' => 'RiskType_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Индивидуальное профилактическое консультирование',
					'field' => 'indi_prof_consult',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Осмотр фельдшером или врачом акушером-гинекологом',
					'field' => 'gynecologist_inspection_text',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Осмотр кожных покровов',
					'field' => 'skin_inspection',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Осмотр слизистых губ и ротовой полости',
					'field' => 'oral_inspection',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Пальпация щитовидной железы',
					'field' => 'thyroid_palpation',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Пальпация лимфатических узлов',
					'field' => 'lymph_node_palpation',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'label' => 'Осмотр терапевтом',
					'field' => 'therapist_text',
					'rules' => '',
					'type' => 'string'
				),
			),
			'loadEvnUslugaDispDop' => array(
				array(
					'field' => 'ExtVersion',
					'label' => 'Версия Ext',
					'rules' => '',
					'type' => 'int',
					'default' => 2
				),
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор осмотра (исследования)',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadScoreField' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор карты',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'getFormalizedInspectionParamsBySurveyType' => array(
				array(
					'field' => 'SurveyType_id',
					'label' => 'Идентификатор SurveyType',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			)
		);
	}
	
	/**
	*  Сохранение осмотра (исследования)
	*/	
	function saveEvnUslugaDispDop() {
		$data = $this->ProcessInputData('saveEvnUslugaDispDop', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->saveEvnUslugaDispDop($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении осмотра (исследования)')->ReturnData();
	}
	
	/**
	*	Расчёт поля SCORE
	*/	
	function loadScoreField() {
		$data = $this->ProcessInputData('loadScoreField', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadScoreField($data);
		$this->ProcessModelSave($response, true, 'Ошибка при расчёте SCORE')->ReturnData();
	}
	
	/**
	*	Получение списка специальностей в зависимости от услуги
	*/	
	function loadMedSpecOmsList() {
		$data = $this->ProcessInputData('loadMedSpecOmsList', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadMedSpecOmsList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	*	Получение списка профилей в зависимости от услуги
	*/	
	function loadLpuSectionProfileList() {
		$data = $this->ProcessInputData('loadLpuSectionProfileList', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadLpuSectionProfileList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Получение данных формы осмотра (исследования)
	 * Входящие данные: $_POST['EvnUslugaDispDop_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispDop()
	{
		$data = $this->ProcessInputData('loadEvnUslugaDispDop', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnUslugaDispDop($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
}