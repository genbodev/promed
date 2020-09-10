<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* ClinExWork - контроллер для работы с журналом учета клинико-экспертной работы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Storozhev
* @version      20.07.2011
* @property ClinExWork_model $dbmodel
*/
class ClinExWork extends swController {

	public $inputRules = array(
			'getNewEvnVKNumber' => array(
			
			),
			'searchData' => array(
				array(
					'field' => 'ExpertiseDateRange',
					'label' => 'Даты экспертиз от - до',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				/*array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),*/
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ExpertiseNameType_id',
					'label' => 'Вид экспертизы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ExpertiseEventType_id',
					'label' => 'Случай экспертизы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'PatientStatusType_id',
					'label' => 'Статус пациента',
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
					'field' => 'EvnVK_DirectionDate',
					'label' => 'Даты направлений на МСЭ: от - до',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'EvnVK_ConclusionDate',
					'label' => 'Даты получения заключения МСЭ: от - до',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'EvnVK_isUseStandard',
					'label' => 'Использовались стандарты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_isAberration',
					'label' => 'Отклонения от стандартов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_isErrors',
					'label' => 'Дефекты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_isResult',
					'label' => 'Достижение результата',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'EvnVK_isControl',
					'label' => 'На контроле',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Номер стартовой записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество записей',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 'off',
					'field' => 'onlySQL',
					'label' => 'Вывести SQL-запрос',
					'rules' => '',
					'type' => 'string'
				),
                array(
                    'field' => 'print_list',
                    'label' => 'print_list',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'EvnStatus_id',
                    'label' => 'EvnStatus_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'isSigned',
                    'label' => 'isSigned',
                    'rules' => '',
                    'type' => 'id'
                )
			),
			'saveEvnVK'	=> array(
				array(
					'field' => 'Server_id', 
					'label' => 'Идентификатор сервера', 
					'rules' => '', 
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field'	=> 'Numerator_id',
					'label'	=> 'Идентифиактор нумератора',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVK_setDT',
					'label' => 'Дата экспертизы',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'default' => 0,
					'field' => 'EvnVK_isReserve',
					'label' => 'Зарезервировано',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач, направивший на ВК',
					'rules' => '',
					'type' => 'id'
				),				
				array(
					'field' => 'EvnVK_didDT',
					'label' => 'Дата контроля',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'default' => 0,
					'field' => 'EvnVK_isControl',
					'label' => 'Контроль',
					'rules' => '',
					'type' => 'checkbox'
				),
				/*array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места врача',
					'rules' => '',
					'type' => 'id'
				),*/
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVK_NumCard',
					'label' => 'Номер КВС/ТАП',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PatientStatusType_id',
					'label' => 'Статус пациента',
					'rules' => '',
					//'type' => 'id'
					'type' => 'string'
				),
				array(
					'field' => 'PatientStatusType_List',
					'label' => 'Статус пациента',
					'rules' => '',
					//'type' => 'id'
					'type' => 'string'
				),
				/*array(
					'field' => 'Okved_id',
					'label' => 'Профессия пациента',
					'rules' => '',
					'type' => 'id'
				),*/
				array(
					'field' => 'EvnVK_Prof',
					'label' => 'Профессия пациента',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'CauseTreatmentType_id',
					'label' => 'Причина обращения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз основной',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_sid',
					'label' => 'Диагноз сопутствующий',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ExpertiseNameType_id',
					'label' => 'Вид экспертизы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ExpertiseEventType_id',
					'label' => 'Характеристика случая экспертизы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ExpertiseNameSubjectType_id',
					'label' => 'Предмет экспертизы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnStickBase_id',
					'label' => 'Идентификатор ЛВН (Больничный лист)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVK_LVN',
					'label' => 'ЛВН (ручной ввод)',
					'rules' => 'max_length[512]',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_Note',
					'label' => 'Примечание',
					'rules' => 'max_length[100]',
					'type' => 'string'
				),
				array(
					'field' => 'EvnStickWorkRelease_id',
					'label' => 'Период освобождения от работы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVK_WorkReleasePeriod',
					'label' => 'Период освобождения от работы (ручной ввод)',
					'rules' => 'max_length[512]',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_ExpertiseStickNumber',
					'label' => 'Экспертиза временной нетрудоспособности №',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_StickPeriod',
					'label' => 'Срок нетрудоспособности, дней',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_StickDuration',
					'label' => 'Длительность пребывания в ЛПУ, дней',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_DirectionDate',
					'label' => 'Дата направления в бюро МСЭ (или др. спец. учреждения)',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnVK_ConclusionDate',
					'label' => 'Дата получения заключения МСЭ (или др. спец. учреждений)',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnVK_ConclusionPeriodDate',
					'label' => 'Срок действия заключения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnVK_ConclusionDescr',
					'label' => 'Заключение МСЭ',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_AddInfo',
					'label' => 'Доп. информация',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field'	=> 'EvnVK_isUseStandard',
					'label'	=> 'Использование стандартов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_isAberration',
					'label' => 'Отклонение от стандартов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_isErrors',
					'label' => 'Дефекты, нарушения и ошибки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVK_isResult',
					'label' => 'Достижение результата или исхода',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field'	=> 'EvnVK_UseStandard',
					'label' => 'Использование стандартов - подробности',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_AberrationDescr',
					'label' => 'Отклонение от стандартов - подробности',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_ErrorsDescr',
					'label' => 'Дефекты, нарушения и ошибки - подробности',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_ResultDescr',
					'label' => 'Достижение результата или исхода - подробности',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_ExpertDescr',
					'label' => 'Заключ. экспертов, рекомендации',
					'rules' => 'trim|max_length[256]',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_DecisionVK',
					'label' => 'Решение ВК',
					'rules' => 'trim|max_length[1024]',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVK_NumProtocol',
					'label' => 'Номер протокола ВК',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'EvnVK_isAutoFill',
					'label' => 'признак базового протокола', //для дальнейшего использования его состава экспертов
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPrescrVK_id',
					'label' => 'Ид. назначения на ВК',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Экшн',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'checkNumProtocol',
					'label' => 'Флаг для проверки номера протокола ВК',
					'rules' => '',
					'type' => 'int',
					'default' => 1
				),
				array(
					'field' => 'numChangedByHand',
					'label' => 'Флаг для проверки номера протокола ВК (кем изменен)',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'EvnVK_MainDisease',
					'label' => 'Основное заболевание',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'SopDiagList',
					'label' => 'Сопутствующие заболевания',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'OslDiagList',
					'label' => 'Осложнения основного заболевания',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'isEmk',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescrMse_id',
					'label' => 'сведения о назначении на МСЭ',
					'rules' => '',
					'type' => 'id'
				),
				// ----- паллиативка -----
				array(
					'field' => 'PalliatEvnVK_IsPMP',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PalliativeType_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PalliatEvnVK_IsIVL',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PalliatEvnVK_IsSpecMedHepl',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PalliatEvnVK_VolumeMedHepl',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ConditMedCareType_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PalliatEvnVK_IsSurvey',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PalliatEvnVK_VolumeSurvey',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PalliatEvnVK_DirSocialProt',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PalliatEvnVK_IsInfoDiag',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PalliatEvnVK_TextTIR',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PalliatEvnVKMainSyndrome',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PalliatEvnVKTechnicInstrumRehab',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PalliatFamilyCare',
					'label' => '',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'isPalliat',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVK_isInternal',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVK_isAccepted',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
			),
			'getEvnVK' => array(
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteEvnVK' => array(
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteEvnPrescrMseEvnDirectionHTM' => array(
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteEvnPrescrMse' => array(
				array(
					'field' => 'EvnPrescrMse_id',
					'label' => 'Идентификатор направления',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getEvnVKStickPeriod' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getEvnVKStick' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'ТАП|КВС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnStickBase_id',
					'label' => 'ЛВН',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getCountEvnStickToVK' => array(
				array(
					'field' => 'EvnStick_id',
					'label' => 'Идентификатор больничного листа',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getEvnVKExpert' => array(
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescrVK_id',
					'label' => 'Идентификатор направления на ВК',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVK_isInternal',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'newEvnVK',
					'label' => 'Флаг',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveEvnVKExpert' => array(
				array(
					'field' => 'EvnVKExpert_id',
					'label' => 'Ид. врача-эксперта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedServiceMedPersonal_id',
					'label' => 'Ид. записи врача в службе',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Место работы врача',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ExpertMedStaffType_id',
					'label' => 'Признак эксперта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'VoteExpertVK_VoteDate',
					'label' => 'VoteExpertVK_VoteDate',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnVKExpert_isApproved',
					'label' => 'EvnVKExpert_isApproved',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnVKExpert_Descr',
					'label' => 'EvnVKExpert_Descr',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'deleteEvnVKExpert' => array(
				array(
					'field' => 'EvnVKExpert_id',
					'label' => 'Идентификатор эксперта',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveEvnVKDiagOne' => array(
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Идентификатор диагноза',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DiagType',
					'label' => 'Тип диагноза',
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'deleteEvnVKDiagOne' => array(
				array(
					'field' => 'EvnVKDiagLink_id',
					'label' => 'Идентификатор диагноза',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getEvnStickWorkRelease' => array(
				array(
					'field' => 'EvnStickWorkRelease_id',
					'label' => 'Идентификатор периода освобождения от работы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnStick_id',
					'label' => 'Идентификатор ЛВН',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getEvnNumCardList' => array(
				array(
					'field' => 'EvnDirection_pid',
					'label' => 'Идентификатор посещения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ТАП/КВС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Чел',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'printEvnVK' => array(
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadDecisionVKTemplateList' => array(
				array(
					'field' => 'ExpertiseNameType_id',
					'label' => 'Вид экспертизы',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getEvnVKViewData' => array(
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getEvnVKNum' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата экспертизы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getEvnXmlList' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'ТАП/КВС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Чел',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'getPalliatQuestionList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Чел',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'getDecisionVK' => array(
				array(
					'field' => 'EvnVK_id',
					'label' => 'Идентификатор ВК',
					'rules' => 'required',
					'type' => 'id'
				)
			)
	);
	
	private $inputData = array();

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('ClinExWork_model', 'dbmodel');
	}

	/**
	 * Поиск протоколов ВК
	 */
	function searchData()
	{
		$val  = array();
		$data = $this->ProcessInputData('searchData', false, false, false, false, false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->searchData($data, false);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Определяем срок нетрудоспособности пациента (дней)
	 */
	function getEvnVKStickPeriod()
	{
		$data = $this->ProcessInputData('getEvnVKStickPeriod', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getEvnVKStickPeriod($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * При добавлении нового ВК узнаем его номер ( = кол-во EvnVK_id + 1)
	 */
	function getNewEvnVKNumber()
	{	
		$data = $this->ProcessInputData('getNewEvnVKNumber', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getNewEvnVKNumber($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	
	/**
	 * Получение номера экспертизы
	 */
	function getCountEvnStickToVK()
	{
		$data = $this->ProcessInputData('getCountEvnStickToVK', false);
		if ($data === false) { return false; }
		
		$stick_parents = array();
		$stick_childs = array();
		$data1 = $data;
		$data2 = $data;
		//Находим ЛВН - предшественники
		$i = 0;
		do{
			$response = $this->dbmodel->getParentsAndChildsforGivenStick($data1, $searchType = 'parents');
			$stick_parents[$i] = $response['EvnStick_prid'];
			$data1['EvnStick_id'] = $response['EvnStick_prid'];
			$i++;
		} while($response['EvnStick_prid'] != 0);
		$pop1 = array_pop($stick_parents);
		
		//Находим ЛВН - продолжения
		$i = 0;
		do{
			$response = $this->dbmodel->getParentsAndChildsforGivenStick($data2, $searchType = 'childs');
			$stick_childs[$i] = $response['EvnStick_id'];
			$data2['EvnStick_id'] = $response['EvnStick_id'];
			$i++;
		} while($response['EvnStick_id'] != 0);
		$pop2 = array_pop($stick_childs);
		
		$sticks = array_merge($stick_parents, $stick_childs);
		$sticks[count($sticks)] = $data['EvnStick_id'];
		
		$sticktoVK_cnt = 0;
		
		for($i=0; $i<count($sticks); $i++)
		{
			$repsonse = $this->dbmodel->CheckStickToVK($sticks[$i]);
			$sticktoVK_cnt += count($repsonse);
		}
		$sticktoVK_cnt += 1;
		$this->ReturnData($sticktoVK_cnt);
	}
	
	
	/**
	 * Получение данных протокола ВК
	 */
	function getEvnVK()
	{
		$data = $this->ProcessInputData('getEvnVK', true);
		if ($data) {
			$response = $this->dbmodel->getEvnVK($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Получение ЛВН по протоколу ВК
	 */
	function getEvnVKStick()
	{
		$data = $this->ProcessInputData('getEvnVKStick', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getEvnVKStick($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	
	/**
	 * Сохранение протокола ВК
	 */
	function saveEvnVK()
	{
		$data = $this->ProcessInputData('saveEvnVK', true);
		if ($data === false) { return false; }
		//print_r($data); exit();
		
		if ($data['isPalliat'] == 1 && empty($data['PalliatEvnVKMainSyndrome'])) {
			$this->ReturnError('Должен быть выбран ведущий синдром');
			return;
		}

		$data['EvnVK_isControl'] = ($data['EvnVK_isControl'] == 1) ? 2 : 1;
		$data['EvnVK_isReserve'] = ($data['EvnVK_isReserve'] == 1) ? 2 : 1;
		$data['EvnVK_isAutoFill'] = ($data['EvnVK_isAutoFill'] == 1) ? 2 : null;
		$data['EvnPrescrMse_id'] = (!empty($data['EvnPrescrMse_id'])) ? $data['EvnPrescrMse_id'] : null;
		// Так как случались случаи некорректного сохранения  при использовании нумератора и ручного ввода,
		// временно ограничение проверки только ручного ввода сниму
		if ($data['checkNumProtocol'] /*&& $data['numChangedByHand'] == 1*/) {

			$resp = $this->dbmodel->checkEvnVKNumProtocol($data);
			if (!empty($resp[0]['Error_Msg']) || !empty($resp[0]['Alert_Msg'])) {
				$this->ReturnData($resp[0]);
				return;
			}
		}

		$response = $this->dbmodel->saveEvnVK($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
		if ( !empty($response[0]['Error_Msg']) ) {
			return;
		}
			
		// Генерируем уведомление
		$this->genNotice(
			array(
				'object' => 'EvnVK',
				'object_id' => $response[0]['EvnVK_id'],
				'action' => ( !empty($data['EvnVK_id']) ) ? 'upd' : 'ins',
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id']
			)
		);
	}

	/**
	 * Удаление протокола ВК
	 */
	function deleteEvnVK()
	{
		$this->load->model('Mse_model', 'Mse_model');
		$data = $this->ProcessInputData('deleteEvnVK', true);
		if($data){
			// Удаляем протокол
			$response = $this->dbmodel->deleteEvnVK($data);
			if(strlen($response[0]['Error_Msg']) > 0){
				DieWithError('При удалении данных произошла ошибка');
				return false;
			}
			// Ищем направление, привязанное к этому протоколу
			$evnprescrmse_data = $this->Mse_model->getEvnPrescrMseOnEvnVK($data);
			if(count($evnprescrmse_data) > 0 && isset($evnprescrmse_data[0]['EvnPrescrMse_id'])){
				$evnprescrmse_data[0]['pmUser_id'] = $data['pmUser_id'];
				$data = $evnprescrmse_data[0];
				// Удаляем направление на МСЭ
				$response = $this->Mse_model->deleteEvnPrescrMse($data);
				$this->ProcessModelList($response, true, true)->ReturnData();
			} else {
				$this->ProcessModelList($response, true, true)->ReturnData();
			}
		}
	}

	/**
	 * Удаление направления на МСЭ
	 */
	function deleteEvnPrescrMse()
	{
		$this->load->model('Mse_model', 'Mse_model');
		$data = $this->ProcessInputData('deleteEvnPrescrMse', true);
		if ($data === false) { return false; }
		
		$response = $this->Mse_model->deleteEvnPrescrMse($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Удаление направления на МСЭ/ВМП
	 */
	function deleteEvnPrescrMseEvnDirectionHTM()
	{
		$this->load->model('Mse_model', 'Mse_model');
		$data = $this->ProcessInputData('deleteEvnPrescrMseEvnDirectionHTM', true);
		if($data){
			// Проверяем наличие протокола МСЭ для ВК
			$evnmse_data = $this->Mse_model->getEvnMseOnEvnVK($data);
			if(count($evnmse_data) > 0 && $evnmse_data[0]['EvnMse_id'] > 0){
				DieWithError('Для выбранного ВК существует протокол МСЭ! Удаление направлений невозможно');
				return false;
			}
			$this->load->model("TimetableMedService_model", "TTMSmodel");
			// Ищем направление на МСЭ, привязанное к этому протоколу
			$evnprescrmse_data = $this->Mse_model->getEvnPrescrMseOnEvnVK($data);
			if(count($evnprescrmse_data) > 0 && isset($evnprescrmse_data[0]['EvnPrescrMse_id'])){
				// Удаляем бирку из расписания службы
				if(isset($evnprescrmse_data[0]['TimetableMedService_id'])){
					$msmsedata = $data;
					$msmsedata['TimetableMedService_id'] = $evnprescrmse_data[0]['TimetableMedService_id'];
					$msmsedata['object'] = 'TimetableMedService';
					$response = $this->TTMSmodel->Clear($msmsedata);
				}
				$evnprescrmse_data[0]['pmUser_id'] = $data['pmUser_id'];
				$msedata = $evnprescrmse_data[0];
				// Удаляем направление на МСЭ
				$response = $this->Mse_model->deleteEvnPrescrMse($msedata);
				if(strlen($response[0]['Error_Msg']) > 0){
					DieWithError('При удалении данных произошла ошибка');
					return false;
				}
			}

			// Ищем направление на ВМП, привязанное к этому протоколу
			$evndirectionhtm_data = $this->Mse_model->getEvnDirectionHTMOnEvnVK($data);
			if(count($evndirectionhtm_data) > 0 && isset($evndirectionhtm_data[0]['EvnDirectionHTM_id'])){
				// Удаляем бирку из расписания службы
				if(isset($evndirectionhtm_data[0]['TimetableMedService_id'])){
					$msdata = $data;
					$msdata['TimetableMedService_id'] = $evndirectionhtm_data[0]['TimetableMedService_id'];
					$msdata['object'] = 'TimetableMedService';
					$response = $this->TTMSmodel->Clear($msdata);
				}
				$evndirectionhtm_data[0]['pmUser_id'] = $data['pmUser_id'];
				$htmdata = $evndirectionhtm_data[0];
				// Удаляем направление на ВМП
				$response = $this->Mse_model->deleteEvnDirectionHTM($htmdata);
				if(strlen($response[0]['Error_Msg']) > 0){
					DieWithError('При удалении данных произошла ошибка');
					return false;
				}
			}

			if(count($evnprescrmse_data) == 0 && count($evndirectionhtm_data) == 0){
				DieWithError('Не существует направлений на МСЭ/ВМП для указанного протокола ВК');
				return false;
			}
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка экспертов в протоколе ВК
	 */
	function getEvnVKExpert()
	{
		$data = $this->ProcessInputData('getEvnVKExpert', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getEvnVKExpert($data);
		$this->ProcessModelList($response, true, true)->ReturnLimitData();
	}

	/**
	 * Сохранение эксперта в протоколе ВК
	 */
	function saveEvnVKExpert()
	{		
		$data = $this->ProcessInputData('saveEvnVKExpert', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveEvnVKExpert($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * Удаление эксперта в протоколе ВК
	 */
	function deleteEvnVKExpert()
	{
		$data = $this->ProcessInputData('deleteEvnVKExpert', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnVKExpert($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Сохранение диагноза в протоколе ВК
	 */
	function saveEvnVKDiagOne()
	{		
		$data = $this->ProcessInputData('saveEvnVKDiagOne', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveEvnVKDiagOne($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Удаление диагноза в протоколе ВК
	 */
	function deleteEvnVKDiagOne()
	{
		$data = $this->ProcessInputData('deleteEvnVKDiagOne', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnVKDiagOne($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * Печать протокола ВК
	 */
	function printEvnVK()
	{
		$data = $this->ProcessInputData('printEvnVK', true);
		if ($data === false) { return false; }

		$this->load->library('parser');
		$view = 'evn_vk_blank';
		
		$response = $this->dbmodel->printEvnVK($data);
		$this->parser->parse($view, $response);
	}

	/**
	 * Печать протокола ВК (Пермь)
	 */
	function printEvnVK_Perm()
	{
		$data = $this->ProcessInputData('printEvnVK', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->printEvnVK_Perm($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		}
	}
	
	/**
	 * Печать журнала ВК
	 */
	function printEvnVK_all()
	{
		$data = array();
		$val = array();
		$this->load->library('parser');
		$data = $this->ProcessInputData('searchData', false, false, false, false, false);
		if ($data === false) { return false; }
        if($data['print_list'] == 1)
            $view = 'evn_vk_template_list_1';
        else
            $view = 'evn_vk_template_list_2';
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$response = $this->dbmodel->printEvnVK_all($data);
		if (!is_array($response)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => toUTF('Ошибка БД при получении списка протоколов!') ) );
			return false;
		}
		foreach ($response as $row) {
			$val[] = $row;
		}
		$html = $this->parser->parse($view, array('search_results' => $val, 'year' => date('Y'), 'date' => date('d.m.Y H:i:s')));
	}

	/**
	 * Экспорт журнала ВК
	 */
	function exportEvnVK_all()
	{
		$data = array();
		$data = $this->ProcessInputData('searchData', false, false, false, false, false);
		if ($data === false) { return false; }
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$response = $this->dbmodel->printEvnVK_all($data);
		if (!is_array($response)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => toUTF('Ошибка БД при получении списка протоколов!') ) );
			return false;
		}

		require_once('vendor/autoload.php');
		$objPHPExcel = new PhpOffice\PhpSpreadsheet\Spreadsheet();
		$objPHPExcel->getProperties();
		$objPHPExcel->getActiveSheet()->setTitle('Журнал ВК');
		$objPHPExcel->setActiveSheetIndex(0);
		$sheet = $objPHPExcel->getActiveSheet();
			$sheet->setCellValue('A1', 'ЖУРНАЛ УЧЕТА КЛИНИКО-ЭКСПЕРТНОЙ РАБОТЫ ЛЕЧЕБНО-ПРОФИЛАКТИЧЕСКОГО УЧРЕЖДЕНИЯ '.date('Y').'г.')
				->setCellValue('A2', '№ п/п')
				->setCellValue('B2', 'Дата экспертизы')
				->setCellValue('C2', 'Наименование ЛПУ, фамилия врача, направившего пациента на экспертизу')
				->setCellValue('D2', 'Фамилия, имя, очество пациента')
				->setCellValue('E2', 'Адрес (либо № страхового полиса или медицинского документа) пациента')
				->setCellValue('F2', 'Дата рождения')
				->setCellValue('G2', 'Пол')
				->setCellValue('H2', 'Социальный статус, профессия')
				->setCellValue('I2', 'Причина обращения. Диагноз (основной, сопутствующий) в соответствии с МКБ-10')
				->setCellValue('J2', 'Характеристика случая экспертизы')
				->setCellValue('K2', 'Вид и предмет экспертизы')
				->setCellValue('L2', '№ п/п')
				->setCellValue('M2', 'Выявлено при экспертизе')
				->setCellValue('M3', 'Отклонение от стандартов')
				->setCellValue('N3', 'Дефекты, нарушения, ошибки и др.')
				->setCellValue('O3', 'Достижение результата этапа или исхода профилактического мероприятия')
				->setCellValue('P2', 'Обоснование заключения. Заключение экспертов, рекомендации')
				->setCellValue('Q2', 'Дата направления в бюро МСЭ или другие (специализированные) учреждения')
				->setCellValue('R2', 'Заключение МСЭ или других (специализированных) учреждений')
				->setCellValue('S2', 'Дата получения заключения МСЭ или других учреждений, срок их действий')
				->setCellValue('T2', 'Дополнительная информация по заключению других (специализированных) учреждений. Примечания.')
				->setCellValue('U2', 'Основной состав экспертов')
				->setCellValue('V2', 'Подписи экспертов');

		$sheet->mergeCells('A1:K1');		
		$sheet->mergeCells('M2:O2');
		$sheet->getStyle('A1')->getAlignment()->setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getRowDimension(2)->setRowHeight(60);
		$abc = 'A.B.C.D.E.F.G.H.I.J.K.L.M.N.O.P.Q.R.S.T.U.V';
		$abc = explode('.',$abc);
		foreach ($abc as $key) {
			switch ($key) {
				case 'A':
					$sheet->getColumnDimension($key)->setWidth(3);
					break;
				case 'B':
					$sheet->getColumnDimension($key)->setWidth(8);
					break;
				case 'C':
					$sheet->getColumnDimension($key)->setWidth(15);
					break;
				case 'D':
					$sheet->getColumnDimension($key)->setWidth(10);
					break;
				case 'E':
					$sheet->getColumnDimension($key)->setWidth(10);
					break;
				case 'F':
					$sheet->getColumnDimension($key)->setWidth(8);
					break;
				case 'G':
					$sheet->getColumnDimension($key)->setWidth(3);
					break;
				case 'H':
					$sheet->getColumnDimension($key)->setWidth(15);
					break;
				case 'I':
					$sheet->getColumnDimension($key)->setWidth(35);
					break;
				case 'J':
					$sheet->getColumnDimension($key)->setWidth(10);
					break;
				case 'K':
					$sheet->getColumnDimension($key)->setWidth(15);
					break;
				case 'L':
					$sheet->getColumnDimension($key)->setWidth(3);
					break;
				case 'M':
					$sheet->getColumnDimension($key)->setWidth(11);
					break;
				case 'N':
					$sheet->getColumnDimension($key)->setWidth(11);
					break;
				case 'O':
					$sheet->getColumnDimension($key)->setWidth(11);
					break;
				case 'P':
					$sheet->getColumnDimension($key)->setWidth(20);
					break;
				case 'Q':
					$sheet->getColumnDimension($key)->setWidth(10);
					break;
				case 'R':
					$sheet->getColumnDimension($key)->setWidth(15);
					break;
				case 'S':
					$sheet->getColumnDimension($key)->setWidth(10);
					break;
				case 'T':
					$sheet->getColumnDimension($key)->setWidth(15);
					break;
				case 'U':
					$sheet->getColumnDimension($key)->setWidth(15);
					break;
				case 'V':
					$sheet->getColumnDimension($key)->setWidth(10);
					break;
				default:
					# code...
					break;
			}
			if(!in_array($key,array('M','N','O'))){
				$merge = $key.'2:'.$key.'3';
				$objPHPExcel->getActiveSheet()->mergeCells($merge);
			} else {
				$cell = $key.'3';
				$sheet->getStyle($cell)->getAlignment()->setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
				$sheet->getStyle($cell)->getAlignment()->setVertical(PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
				$sheet->getStyle($cell)->getAlignment()->setWrapText(true);
				$sheet->getStyle($cell)->getAlignment()->setShrinkToFit(false);
				$sheet->getStyle($cell)->getFont()->setSize(8);
				$sheet->getStyle($cell)->applyFromArray(
					array(	'borders'=>	array(
												'left'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
													'	rgb' => '808080'
													)
												),
												'bottom'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
														'rgb' => '808080'
													)
												)
											)
				));
			}
			$cell = $key.'2';
			$sheet->getStyle($cell)->getAlignment()->setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle($cell)->getAlignment()->setVertical(PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			$sheet->getStyle($cell)->getAlignment()->setWrapText(true);
			$sheet->getStyle($cell)->getAlignment()->setShrinkToFit(false);
			$sheet->getStyle($cell)->getFont()->setSize(8);
			$sheet->getStyle($cell)->applyFromArray(
					array(	'borders'=>	array(
												'left'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
													'	rgb' => '808080'
													)
												),
												'bottom'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
														'rgb' => '808080'
													)
												),
												'top'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
														'rgb' => '808080'
													)
												),
												'right'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
														'rgb' => '808080'
													)
												)
											)
				));
			$cell = $key.'3';
			$sheet->getStyle($cell)->applyFromArray(
					array(	'borders'=>	array(
												'left'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
													'	rgb' => '808080'
													)
												),
												'bottom'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
														'rgb' => '808080'
													)
												),
												'top'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
														'rgb' => '808080'
													)
												),
												'right'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
														'rgb' => '808080'
													)
												)
											)
				));
		}

		$r = 4;
		foreach ($response as $item) {
			$c = 0;
			$correctItem = array();
			$correctItem[0] = $item['num'];
			$correctItem[1] = $item['EvnVK_ExpertiseDate'];
			$correctItem[2] = $item['MedPersonal_Fin'].' '.$item['Lpu_Nick'];
			$correctItem[3] = $item['Person_Fin'];
			$correctItem[4] = $item['Person_Polis_Addr'];
			$correctItem[5] = $item['Person_BirthDay'];
			$correctItem[6] = $item['Person_Sex'];
			$correctItem[7] = $item['PatientStatusType_Prof'];
			$correctItem[8] = $item['Person_Diag'].' '.$item['Person_Diag_s'].' '.$item['CauseTreatmentType_Name'];
			$correctItem[9] = $item['ExpertiseEventType_SysNick'];
			$correctItem[10] = $item['ExpertiseNameType'].' '.$item['ExpertiseNameSubjectType'].' '.$item['EvnVK_LVN'].' '.$item['EvnVK_WorkReleasePeriod'].' '.$item['EvnVK_StickDuration'];
			$correctItem[11] = $item['num'];
			$correctItem[12] = $item['EvnVK_isAberration'].' '.$item['EvnVK_AberrationDescr'];
			$correctItem[13] = $item['EvnVK_isErrors'].' '.$item['EvnVK_ErrorsDescr'];
			$correctItem[14] = $item['EvnVK_isResult'].' '.$item['EvnVK_ResultDescr'];
			$correctItem[15] = $item['EvnVK_ExpertDescr'];
			$correctItem[16] = $item['EvnVK_DirectionDate'];
			$correctItem[17] = $item['EvnVK_ConclusionDescr'];
			$correctItem[18] = $item['EvnVK_ConclusionDate'];
			$correctItem[19] = $item['EvnVK_AddInfo'];
			$correctItem[20] = $item['MF_Person_FIO'];
			$correctItem[21] = '';
			array_walk($correctItem, 'ConvertFromWin1251ToUTF8');

			foreach ($correctItem as $val) {
				$sheet->setCellValueByColumnAndRow($c, $r, $val);
				$sheet->getStyleByColumnAndRow($c, $r)->applyFromArray(
					array(	'borders'=>	array(
												'left'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
													'	rgb' => '808080'
													)
												),
												'right'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
													'	rgb' => '808080'
													)
												),
												'bottom'     => array(
													'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
													'color' => array(
														'rgb' => '808080'
													)
												)
											)
				));
				$sheet->getStyleByColumnAndRow($c, $r)->getAlignment()->setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
				$sheet->getStyleByColumnAndRow($c, $r)->getAlignment()->setVertical(PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
				$sheet->getStyleByColumnAndRow($c, $r)->getAlignment()->setWrapText(true);
				$sheet->getStyleByColumnAndRow($c, $r)->getAlignment()->setShrinkToFit(true);
				$sheet->getStyleByColumnAndRow($c, $r)->getFont()->setSize(6);
				$c++;
			}
			$r++;
		}

		$objWriter = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
		$file = 'export/vk_journals/journalVK' . date('d-m-Y-h-i-s', time()) . '.xlsx';
		$objWriter->save($file);
		$this->download($file);
		unlink($file);

	}

	/**
	 *
	 * @param type $file 
	 */
	function download($file) {
		if (ob_get_level()) {
			ob_end_clean();
		}
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	}
	
	/**
	 *	Method description
	 */
	function getEvnStickWorkRelease()
	{
		$data = $this->ProcessInputData('getEvnStickWorkRelease', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getEvnStickWorkRelease($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Method description
	 */
	function getEvnNumCardList()
	{
		$data = $this->ProcessInputData('getEvnNumCardList', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getEvnNumCardList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Генерация уведомлений для врачей
	 *	input:	object ('EvnPrescrVK', 'EvnVK', 'EvnPrescrMse', 'EvnMse')
	 *			action ('ins', 'upd')
	 *			object_id
	 *	TODO: пока что много лишнего, так как здесь работаем только с EvnVK
	 */
	function genNotice($data)
	{
		if (!$data || !isset($data['object']))
			return false;
		
		switch ( $data['object'] ) {
			case 'EvnPrescrVK': $evnText = 'направление на ВК'; break;
			case 'EvnVK': $evnText = 'протокол ВК'; break;
			case 'EvnPrescrMse': $evnText = 'направление на МСЭ'; break;
			case 'EvnMse': $evnText = 'обратный талон МСЭ'; //$evnText = 'протокол МСЭ';
				break;
		}
		
		$this->load->model('Mse_model', 'Mse_model');
		
		// Находим всех "причастных"
		$recipients = $this->Mse_model->getMedPersonalForNotice($data);
		if(!$recipients) return false;
		
		// Находим данные для уведомления
		$personData = $this->Mse_model->getDataForNotice($data);
		if(!$personData) return false;
		
		if( in_array($data['object'], array('EvnPrescrVK', 'EvnPrescrMse')) ) {
			$text = (( $data['action'] == 'upd' ) ? 'Изменено ' : 'Выписано ' ).$evnText.'. ';
		} else {
			$text = (( $data['action'] == 'upd' ) ? 'Изменен ' : 'Создан ' ).$evnText.'. ';
		}
		if($data['object'] == 'EvnVK' and !empty($personData['EvnVK_DecisionVK'])) {
			$text .= ' Решение комиссии: '.$personData['EvnVK_DecisionVK'].'. ';
		}
		
		$text .= 'Пациент - '.$personData['Person_Fio'].', '.$personData['Person_BirthDay'].'. Служба - '.$personData['MedService_Name'].'.';
		
		// Формируем ссылку на документ
		if($data['object'] != 'EvnPrescrVK') {
			switch ( $data['object'] ) {
				case 'EvnVK': $winPrototype = 'swPersonEmkWindow'; //$winPrototype = 'swClinExWorkEditWindow';
					break;
				case 'EvnPrescrMse': $winPrototype = 'swDirectionOnMseEditForm'; 
					if(!empty($personData['EvnStatus_Name']))
						$text.=' Новый статус: '.$personData['EvnStatus_Name'].'. ';
					break;
				case 'EvnMse': $winPrototype = 'swProtocolMseEditForm'; break;
			}
			$text .= ' <a href="javascript://" onClick="getWnd(\''.$winPrototype.'\').show({'.
						$data['object'].'_id: '.$personData[$data['object'].'_id'].','.
						'Person_id: '.$personData['Person_id'].','.
						'Server_id: '.$personData['Server_id'].','.
						'action: \'view\','.
						'showtype: \'view\'';
			if($data['object'] == 'EvnPrescrMse')
				$text .= ',EvnVK_id:'.$personData['EvnVK_id'];
			if($data['object'] == 'EvnVK') {
				$text .= ",ARMType: 'common',searchNodeObj:{EvnClass_SysNick: 'EvnVK', Evn_id: ".$personData['EvnVK_id'].", parentNodeId: 'root',last_child: false,disableLoadViewForm: false}";
				$text .= '})">Подробнее</a>';
			} else
			if($data['object'] == 'EvnPrescrMse') {
				$text .= '})">Подробнее</a>';
			}
			else $text .= '})">смотреть документ</a>';
		}
		
		$noticeData = array(
			'autotype' => 1
			,'Lpu_rid' => $data['Lpu_id']
			,'pmUser_id' => $data['pmUser_id']
			,'type' => 1
			,'title' => 'Автоматическое уведомление'
			,'text' => $text
		);
		
		$this->load->model('Messages_model', 'Messages_model');
		
		foreach($recipients as $recipient) {
			$noticeData['MedPersonal_rid'] = $recipient;
			
			$noticeResponse = $this->Messages_model->autoMessage($noticeData);
			if ( !empty($noticeResponse['Error_Msg']) ) {
				echo json_return_errors($noticeResponse['Error_Msg']);
				return false;
			}
		}
	}

	/**
	 * Получение списка шаблонов решения ВК
	 */
	function loadDecisionVKTemplateList() {
		$data = $this->ProcessInputData('loadDecisionVKTemplateList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDecisionVKTemplateList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение номера активного нумератора
	 */
	function getEvnVKNum() {
		$data = $this->ProcessInputData('getEvnVKNum', true);
		if ( $data === false ) {
			return false;
		}
		
		$numData = $this->dbmodel->getEvnVKNum($data);
		if (!empty($numData['Error_Msg'])) {
			$this->ProcessModelSave($numData, true, 'Ошибка получения номера')->ReturnData();
			return false;
		}
		$val['num'] = $numData['Numerator_Num'];
		$val['intnum'] = $numData['Numerator_IntNum'];
		$val['prenum'] = ($numData['Numerator_PreNum'] == null)?'':$numData['Numerator_PreNum'];
		$val['postnum'] = ($numData['Numerator_PostNum'] == null)?'':$numData['Numerator_PostNum'];
		$val['ser'] = $numData['Numerator_Ser'];
		$val['success'] = true;
		
		$this->ProcessModelSave($val, true, 'Ошибка получения номера')->ReturnData();
	}

	/**
	 * Получение списка эпикризов
	 */
	function getEvnXmlList() {
		$data = $this->ProcessInputData('getEvnXmlList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnXmlList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка анкет
	 */
	function getPalliatQuestionList() {
		$data = $this->ProcessInputData('getPalliatQuestionList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPalliatQuestionList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение решения ВК
	 */
	function getDecisionVK() {
		$data = $this->ProcessInputData('getDecisionVK', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDecisionVK($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}