<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnRecept - контроллер для работы с рецептами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
*               Bykov Stas aka Savage (savage1981@gmail.com)
* @version      14.05.2009
* @property Dlo_EvnRecept_model $dbmodel
*/

class EvnRecept extends swController {
	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	public $inputRules = array();

    /**
     * Конструктор контроллера
     */
	function __construct() {
		parent::__construct();

		$this->load->database();
		
		$this->load->model('Dlo_EvnRecept_model', 'dbmodel');
		//$this->load->model('ufa/Ufa_Dlo_EvnRecept_model', 'dbmodel');
		
		$this->inputRules = array(
			'loadEvnReceptPanel' => array(
				array('field' => 'EvnRecept_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id')
			),
			'loadPersonEvnReceptPanel' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnReceptGeneral_rid', 'label' => 'Идентификатор случая лечения', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_pid', 'label' => 'Идентификатор родительского рецепта', 'rules' => '', 'type' => 'id'),
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int')
			),
			'saveEvnReceptIsPrinted' => array(
				array('field' => 'EvnRecept_id', 'label' => 'Идентификатор рецепта', 'rules' => 'required', 'type' => 'id')
			),
			'saveEvnReceptGeneralIsPrinted' => array(
				array('field' => 'EvnReceptGeneral_id', 'label' => 'Идентификатор общего рецепта', 'rules' => 'required', 'type' => 'id')
			),
			'saveEvnReceptRls' => array(
				array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Drug_rlsid', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Комплексное МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestRow_id', 'label' => 'Заявка врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnRecept_id', 'label' => 'Идентификатор рецепта', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnRecept_Is7Noz', 'label' => 'Признак "7 нозологий"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnRecept_IsKEK', 'label' => 'Протокол КЭК', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_IsMnn', 'label' => 'Выписка по МНН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnRecept_IsSigned', 'label' => 'Признак подписанного рецепта', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_IsOtherDiag', 'label' => 'Другие показания к применению', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_Kolvo', 'label' => 'Количество (D. t. d.)', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'EvnRecept_Num', 'label' => 'Номер рецепта', 'rules' => 'trim|required', 'type' => 'string'),
				array('field' => 'EvnRecept_pid', 'label' => 'Идентификатор родительского события для рецепта', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_Ser', 'label' => 'Серия рецепта', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'EvnRecept_setDate', 'label' => 'Дата выписки рецепта', 'rules' => 'trim|required', 'type' => 'date'),
				array('field' => 'EvnRecept_Signa', 'label' => 'Signa', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'EvnRecept_IsDelivery', 'label' => 'Выдан уполномоченному лицу', 'rules' => '', 'type' => 'string'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'OrgFarmacy_id', 'label' => 'Аптека', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonPrivilege_id', 'label' => 'Идентификатор льготы человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PrivilegeType_id', 'label' => 'Категория льготы', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ReceptDiscount_id', 'label' => 'Скидка', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ReceptFinance_id', 'label' => 'Тип финансирования', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ReceptType_id', 'label' => 'Тип рецепта', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ReceptValid_id', 'label' => 'Срок действия', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugOstatRegistry_id', 'label' => 'Регистр остатков', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_id', 'label' => 'Контракт на поставку', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_IsNotOstat', 'label' => 'Признак остутствия медикамента на остатках', 'rules' => '', 'type' => 'id'),
				array('field' => 'ReceptDelayType_id', 'label' => 'Тип отсрочки', 'rules' => '', 'type' => 'id'),
				array('field' => 'Drug_Price', 'label' => 'Цена', 'rules' => '', 'type' => 'float'),
                array(
                    'field' => 'ReceptForm_id',
                    'label' => 'Форма рецепта',
                    'rules' => '',
                    'type'  => 'int'
                ),
				array('field' => 'Storage_id', 'label' => 'Склад', 'rules' => '', 'type' => 'id'),
				array('field' => 'isKardio', 'label' => 'Флаг режима ЛЛО Кардио', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnRecept_VKProtocolNum', 'label' => 'Номер протокола ВК', 'rules' => '', 'type' => 'string'),
				array('field' => 'EvnRecept_VKProtocolDT', 'label' => 'Дата протокола ВК', 'rules' => '', 'type' => 'date'),
				array('field' => 'CauseVK_id', 'label' => 'Основание для проведения ВК', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonAmbulatCard_id', 'label' => 'Амбулаторная карта', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnCourseTreatDrug_KolvoEd', 'label' => 'Кол-во ЛС на 1 прием', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnCourseTreatDrug_Kolvo', 'label' => 'Разовая доза', 'rules' => '', 'type' => 'string'),
				array('field' => 'GoodsUnit_sid', 'label' => 'Кол-во ЛС на 1 прием (ед. изм.)', 'rules' => '', 'type' => 'id'),
				array('field' => 'GoodsUnit_id', 'label' => 'Разовая доза (ед. изм.)', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnCourseTreat_CountDay', 'label' => 'Приемов в сутки', 'rules' => '', 'type' => 'int'),
				array('field' => 'PrescriptionIntroType_id', 'label' => 'Способ применения', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnCourseTreat_setDate', 'label' => 'Дата начала приема', 'rules' => '', 'type' => 'date'),
				array('field' => 'EvnCourseTreat_Duration', 'label' => 'Дней приема', 'rules' => '', 'type' => 'int'),
				array('field' => 'PrescrSpecCause_id', 'label' => 'Причина специального назначения', 'rules' => '', 'type' => 'id'),
				array('field' => 'ReceptUrgency_id', 'label' => 'Срочность', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnRecept_IsExcessDose', 'label' => 'Превышение дозировки', 'rules' => '', 'type' => 'string'),
				array('field' => 'EvnUslugaTelemed', 'label' => 'Признак сохранения рецепта из формы Оказания телемедицинской услуги', 'rules' => '', 'type' => 'int')
			),
			'loadPersonRegisterList' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
			),
			'checkEvnRecept' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_setDate',
					'label' => 'Дата выписки рецепта',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'default' => '',
					'field' => 'mode',
					'label' => 'Режим проверки',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
            'checkPrivDiag' => array(
                array(
                    'field' => 'Diag_id',
                    'label' => 'Диагноз',
                    'rules' => 'required',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'PrivilegeType_id',
                    'label' => 'Категория льготы',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
			'checkEvnMatterRecept' => array(
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Действующее вещество',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkReceptKardioReissue' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Комплексное МНН',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkReceptKardioTicagrelor' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Комплексное МНН',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО выписки рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_setDate',
					'label' => 'Дата рецепта',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'checkReceptKardioSetDate' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_pid',
					'label' => 'Родительское событие',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_setDate',
					'label' => 'Дата рецепта',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'markReceptDeleted' => array(

			),
			'deleteEvnRecept' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptRemoveCauseType_id',
					'label' => 'Причина удаления рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DeleteType',
					'label' => 'Тип удаления', // 0 - пометить рецепт в удалению, 1 - удалить рецепт
					'rules' => '',
					'type'	=> 'int',
					'default' => 1
				)
			),
			'UndoDeleteEvnRecept' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getReceptNumber' => array(
				array(
					'field' => 'ReceptForm_id',
					'label' => 'Форма рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptType_id',
					'label' => 'Тип рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_setDate',
					'label' => 'Дата рецепта',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Программа ЛЛО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'isRLS',
					'label' => 'Из формы рецепта РЛС',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'isGeneral',
					'label' => 'Из формы нельготного рецепта',
					'rules' => '',
					'type' => 'int'
				)
			),
			/**
			* Загрузка комбо "Торговое наименование" на форме редактирования рецепта
			*/
			'loadDrugList' => array(
				array(
					'default' => date('Y-m-d'),
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Drug_DoseCount',
					'label' => 'Дозировка',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'Drug_DoseQ',
					'label' => 'Дозировка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_DoseUEEi',
					'label' => 'Непонятная хрень',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_Fas',
					'label' => 'Количество',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_CodeG',
					'label' => 'Код ГЕС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugFormGroup_id',
					'label' => 'Группа формы выпуска',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugMnn_id',
					'label' => 'Идентификатор МНН',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestRow_id',
					'label' => 'Идентификатор строки заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestRow_IsReserve',
					'label' => 'Признак выписки медикамента из резерва',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_Is7Noz_Code',
					'label' => 'Код признака 7 нозологий',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 'all', // варианты: all, request
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Наименование медикамента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ReceptFinance_Code',
					'label' => 'Код типа финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptType_Code',
					'label' => 'Код типа рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Код категории',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Программа ЛЛО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RequestDrug_id',
					'label' => 'Идентификатор медикамента из заявки',
					'rules' => '',
					'type' => 'id'
				),
                array(
                    'field' => 'is_mi_1',
                    'label' => 'МИ-1',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'default' => '1',
                    'field' => 'DopRequest',
                    'label' => 'Дополнительная заявка',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'default' => false,
                    'field' => 'ignoreCheck',
                    'label' => 'Игнорировать проверку',
                    'rules' => '',
                    'type' => 'boolean'
                )
			),
			'loadDrugRequestOtovGrid' => array(
				array(
					'field' => 'Date',
					'label' => 'Дата выписки рецепта',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadDrugRequestRowGrid' => array(
				array(
					'field' => 'Date',
					'label' => 'Дата актуальности',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'DrugRequestRow_IsReserve',
					'label' => 'Признак выписки медикамента из резерва',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugProtoMnn_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptFinance_id',
					'label' => 'Тип финасирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CurrentLpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadDrugRequestMnnList' => array(
				array(
					'field' => 'Date',
					'label' => 'Дата выписки рецепта',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestRow_id',
					'label' => 'Идентификатор строки заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestRow_IsReserve',
					'label' => 'Признак выписки медикамента из резерва',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'mode',
					'label' => 'Режим поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'МНН',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ReceptFinance_Code',
					'label' => 'Код типа финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptType_Code',
					'label' => 'Код типа рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Код категории',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugProtoMnn_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptFinance_id',
					'label' => 'Тип финасирования',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadDrugRequestMedPersonalList' => array(
				array(
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_rid',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_rid',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLpuPrev' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadOrgFarmacyList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_Is7Noz_Code',
					'label' => 'Код признака 7 нозологий',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnRecept_IsExtemp',
					'label' => 'Признак экстемпорального рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптеки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptFinance_Code',
					'label' => 'Код типа финансирования',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ReceptType_Code',
					'label' => 'Код типа рецепта',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Категория льготы',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveEvnRecept' => array(
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'required',
					'type' => 'id'
				),
                array(
                    'field' => 'is_mi_1',
                    'label' => 'МИ-1',
                    'rules' => '',
                    'type'  => 'string'
                ),
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_Price',
					'label' => 'Цена',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_IsKEK',
					'label' => 'Протокол КЭК',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_IsMnn',
					'label' => 'Выписка по МНН',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestRow_id',
					'label' => 'Идентификатор строки заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_ExtempContents',
					'label' => 'Состав экстемпорального рецепта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_Is7Noz',
					'label' => '7 нозологий',
					'rules' => '', // на уфе нет 
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_IsExtemp',
					'label' => 'Признак экстемпорального рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_pid',
					'label' => 'Идентификатор родителя рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_Kolvo',
					'label' => 'Количество (D. t. d.)',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'EvnRecept_Num',
					'label' => 'Номер рецепта',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'EvnRecept_Ser',
					'label' => 'Серия рецепта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnRecept_setDate',
					'label' => 'Дата выписки рецепта',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Аптека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор состояния человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonPrivilege_id',
					'label' => 'Идентификатор льготы человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Категория льготы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptDiscount_id',
					'label' => 'Скидка',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptFinance_id',
					'label' => 'Финансирование',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Тип финансирования',
					'rules' => '',
					'type'  => 'id'
				),
                array(
                    'field' => 'ReceptForm_id',
                    'label' => 'Форма рецепта',
                    'rules' => '',
                    'type'  => 'int'
                ),
				array(
					'field' => 'ReceptType_id',
					'label' => 'Тип рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptValid_id',
					'label' => 'Срок действия',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'default' => '',
					'field' => 'EvnRecept_Signa',
					'label' => 'Signa',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnRecept_IsDelivery',
					'label' => 'Выдан уполномоченному лицу',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Программа ЛЛО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_VKProtocolNum',
					'label' => 'Номер протокола ВК',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnRecept_VKProtocolDT',
					'label' => 'Дата протокола ВК',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'CauseVK_id',
					'label' => 'Основание для проведения ВК',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PrescrSpecCause_id',
					'label' => 'Причина специального назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ReceptUrgency_id',
					'label' => 'Срочность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnRecept_IsExcessDose',
					'label' => 'Превышение дозировки',
					'rules' => '',
					'type' => 'string'
				)
			),
            'saveDrugRequestDop' => array(
                array(
                    'field' => 'DrugRequestDop_setDT',
                    'label' => 'Дата',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'DrugRequestPeriod_id',
                    'label' => 'Идентификатор периода заявки',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'MedStaffFact_id',
                    'label' => 'Врач',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Diag_id',
                    'label' => 'Диагноз',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'DrugRequestDop_IsMedical',
                    'label' => 'Решение ВК',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'DrugFinance_id',
                    'label' => 'Тип финансирования',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'PrivilegeType_id',
                    'label' => 'Льгота',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Пациент',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'DrugProtoMnn_id',
                    'label' => 'МНН',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Drug_id',
                    'label' => 'Медикамент',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'DrugRequestDop_PackCount',
                    'label' => 'Количество',
                    'rules' => 'required',
                    'type'  => 'float'
                )
            ),
			'searchEvnRecept' => array(
				array(
						'default' => 0,
						'field' => 'start',
						'label' => 'Начальный номер записи',
						'rules' => 'trim',
						'type' => 'int'
				),
				array(
						'default' => 100,
						'field' => 'limit',
						'label' => 'Количество возвращаемых записей',
						'rules' => 'trim',
						'type' => 'int'
				),
				array(
						'field' => 'Person_Surname',
						'label' => 'Фамилия',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_Firname',
						'label' => 'Имя',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_Secname',
						'label' => 'Отчество',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_BirthDay',
						'label' => 'Дата рождения',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'PersonCard_NumCard',
						'label' => 'Номер карты',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'EvnRecept_setDate',
						'label' => 'Дата выписки',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'EvnRecept_otpDate',
						'label' => 'Дата отпуска',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'PrivilegeType_id',
						'label' => 'Категория льготы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Person_SNILS',
						'label' => 'СНИЛС',
						'rules' => 'trim',
						'type' => 'snils'
					),
				array(
						'field' => 'ER_MedPersonal_id',
						'label' => 'Врач',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ER_Diag_Code_From',
						'label' => 'Код диагноза с',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'ER_Diag_Code_To',
						'label' => 'Код диагноза по',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'PersonSex_id',
						'label' => 'Пол',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'SocStatus_id',
						'label' => 'Социальный статус',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PersonPrivilegeType_id',
						'label' => 'Человек: Категория льготы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'DocumentType_id',
						'label' => 'Документ : Тип',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgDep_id',
						'label' => 'Документ: Выдан',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OMSSprTerr_id',
						'label' => 'Полис: Территория',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PolisType_id',
						'label' => 'Полис: Тип',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgSMO_id',
						'label' => 'Полис: Выдан',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Org_id',
						'label' => 'Место работы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Post_id',
						'label' => 'Должность',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptFinance_id',
						'label' => 'Финансирование',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptDiscount_id',
						'label' => 'Скидка',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptValid_id',
						'label' => 'Срок действия',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnRecept_Num',
						'label' => 'Номер рецепта',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_Ser',
						'label' => 'Серия рецепта',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'ReceptType_id',
						'label' => 'Тип рецепта',
						'rules' => 'trim',
						'type' => 'id'
				),
				array(
						'field' => 'OrgFarmacy_id',
						'label' => 'Аптека',
						'rules' => '',
						'type' => 'id'
				),
				array(
						'field' => 'DrugMnn_id',
						'label' => 'МНН',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Drug_id',
						'label' => 'Торговое наименование',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnRecept_IsNotOstat',
						'label' => 'Выписка без наличия медикамента на остатках',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'pmUser_insID',
						'label' => 'Добавивший пользователь',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'pmUser_updID',
						'label' => 'Обновивший пользователь',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnRecept_insDT',
						'label' => 'Дата внесения рецепта',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'EvnRecept_updDT',
						'label' => 'Дата изменения рецепта',
						'rules' => 'trim',
						'type' => 'daterange'
					)),
			'SearchReceptFromBarcode' => array(
				array(
					'field' => 'EvnRecept_setDate',
					'label' => 'Дата выписки',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnRecept_Ser',
					'label' => 'Серия рецепта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnRecept_Num',
					'label' => 'Номер рецепта',
					'rules' => 'trim',
					'type' => 'int'
				),
                array(
                    'field' => 'EvnRecept_id',
                    'label' => 'Идентификатор рецепта',
                    'rules' => 'trim',
                    'type' => 'int'
                )
			),
			'searchEvnReceptInCorrect' => array(
				array(
						'default' => 0,
						'field' => 'start',
						'label' => 'Начальный номер записи',
						'rules' => 'trim',
						'type' => 'int'
				),
				array(
						'default' => 100,
						'field' => 'limit',
						'label' => 'Количество возвращаемых записей',
						'rules' => 'trim',
						'type' => 'int'
				),
				array(
						'field' => 'Person_Surname',
						'label' => 'Фамилия',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_Firname',
						'label' => 'Имя',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_Secname',
						'label' => 'Отчество',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_BirthDay',
						'label' => 'Дата рождения',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'PersonCard_NumCard',
						'label' => 'Номер карты',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'EvnRecept_Is7Noz',
						'label' => '7 нозологий',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'EvnRecept_setDate',
						'label' => 'Дата выписки',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'EvnRecept_otpDate',
						'label' => 'Дата отпуска',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'PrivilegeType_id',
						'label' => 'Категория льготы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'SubCategoryPrivType_id',
						'label' => 'Подкатегория льготы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Person_SNILS',
						'label' => 'СНИЛС',
						'rules' => 'trim',
						'type' => 'snils'
					),
				array(
						'field' => 'Person_Inn',
						'label' => 'ИНН',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'receptLpuId',
						'label' => 'ЛПУ',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'LpuBuilding_id',
						'label' => 'Подразделение',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'LpuSection_id',
						'label' => 'Отделение',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ER_MedPersonal_id',
						'label' => 'Врач',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ER_Diag_Code_From',
						'label' => 'Код диагноза с',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'ER_Diag_Code_To',
						'label' => 'Код диагноза по',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'PersonSex_id',
						'label' => 'Пол',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'SocStatus_id',
						'label' => 'Социальный статус',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PersonPrivilegeType_id',
						'label' => 'Человек: Категория льготы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'DocumentType_id',
						'label' => 'Документ : Тип',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgDep_id',
						'label' => 'Документ: Выдан',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OMSSprTerr_id',
						'label' => 'Полис: Территория',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PolisType_id',
						'label' => 'Полис: Тип',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgSMO_id',
						'label' => 'Полис: Выдан',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Org_id',
						'label' => 'Место работы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Post_id',
						'label' => 'Должность',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptFinance_id',
						'label' => 'Финансирование',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptDiscount_id',
						'label' => 'Скидка',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptValid_id',
						'label' => 'Срок действия',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnRecept_Num',
						'label' => 'Номер рецепта',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_Ser',
						'label' => 'Серия рецепта',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'ReceptYes_id',
						'label' => 'Выписан рецепт',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptType_id',
						'label' => 'Тип рецепта',
						'rules' => 'trim',
						'type' => 'id'
					),
                array(
                        'field' => 'ReceptForm_id',
                        'label' => 'Форма рецепта',
                        'rules' => 'trim',
                        'type'  => 'id'
                ),
				array(
						'field' => 'EvnRecept_IsNotOstat',
						'label' => 'Выписка без наличия медикамента на остатках',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgFarmacy_id',
						'label' => 'Аптека',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'ReceptResult_id',
						'label' => 'Выписан рецепт',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptMismatch_id',
						'label' => 'Несовпадения в рецептах',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnRecept_obrTimeFrom',
						'label' => 'Время обращения в аптеку с момента выписки, от',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_obrTimeTo',
						'label' => 'Время обращения в аптеку с момента выписки, до',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_otsTimeFrom',
						'label' => 'Время отсрочки отоваривания рецепта, от',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_otsTimeTo',
						'label' => 'Время отсрочки отоваривания рецепта, до',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_otovTimeFrom',
						'label' => 'Время отов. рецепта с момента выписки, от',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_otovTimeTo',
						'label' => 'Время отов. рецепта с момента выписки, до',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_obrDate',
						'label' => 'Обращение в аптеку',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'EvnRecept_otpDate',
						'label' => 'Отоваривание рецепта',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'EvnRecept_otsDate',
						'label' => 'Актуальность отсрочки',
						'rules' => 'trim',
						'type' => 'date'
					),
				array(
						'field' => 'DrugMnn_id',
						'label' => 'МНН',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Drug_id',
						'label' => 'Торговое наименование',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'pmUser_insID',
						'label' => 'Добавивший пользователь',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'pmUser_updID',
						'label' => 'Обновивший пользователь',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnRecept_insDT',
						'label' => 'Дата внесения рецепта',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'EvnRecept_updDT',
						'label' => 'Дата изменения рецепта',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'ReceptYes_id',
						'label' => 'Выписан рецепт',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptResult_id',
						'label' => 'Результат',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'ReceptMismatch_id',
						'label' => 'Несовпадения в рецептах',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'Recept_obrTimeFrom',
						'label' => 'Время обращения в аптеку с момента выписки, от',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'Recept_otsTimeFrom',
						'label' => 'Время отсрочки отоваривания рецепта, от',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'Recept_otovTimeFrom',
						'label' => 'Время отов. рецепта с момента выписки, от',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'Recept_obrTimeTo',
						'label' => 'Время обращения в аптеку с момента выписки, до',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'Recept_otsTimeTo',
						'label' => 'Время отсрочки отоваривания рецепта, до',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'Recept_otovTimeTo',
						'label' => 'Время отов. рецепта с момента выписки, до',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'Recept_setDate',
						'label' => 'Дата выписки рецепта',
						'rules' => '',
						'type' => 'daterange'
					),
				array(
						'field' => 'Recept_obrDT',
						'label' => 'Дата обращения в аптеку',
						'rules' => '',
						'type' => 'daterange'
					),
				array(
						'field' => 'Recept_otpDT',
						'label' => 'Дата отоваривания рецепта',
						'rules' => '',
						'type' => 'daterange'
					),
				array(
						'field' => 'Recept_otsDT',
						'label' => 'Дата актуальности отсрочки',
						'rules' => '',
						'type' => 'daterange'
					),					
				array(
						'default' => 0,
						'field' => 'SearchedLpu_id',
						'label' => 'ЛПУ',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 0,
						'field' => 'SearchedOMSSprTerr_Code',
						'label' => 'Территория',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'default' => 0,
						'field' => 'Lpu_IsOblast_id',
						'label' => 'Принадлженость ЛПУ',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 0,
						'field' => 'ReceptStatusType_id',
						'label' => 'Признак прохождения экспертизы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 0,
						'field' => 'ReceptStatusFLKMEK_id',
						'label' => 'Идентификатор результата экспертизы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 0,
						'field' => 'RegistryReceptErrorType_id',
						'label' => 'Идентификатор причины отказа',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 0,
						'field' => 'AllowRegistryDataRecept',
						'label' => 'Признак передачи на оплату',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 0,
						'field' => 'RegistryDataRecept_IsReceived',
						'label' => 'Признак принятия на хранение',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'default' => 0,
						'field' => 'RegistryDataRecept_IsPaid',
						'label' => 'Признак оплаты',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'WhsDocumentCostItemType_id',
						'label' => 'Источник финмнсирования',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'EvnRecept_IsKEK',
						'label' => 'Источник финмнсирования',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'EvnRecept_VKProtocolNum',
						'label' => 'Номер протокола ВК',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'EvnRecept_VKProtocolDT',
						'label' => 'Дата протокола ВК',
						'rules' => '',
						'type' => 'date'
					),
				array(
						'default' => 0,
						'field' => 'start',
						'label' => 'Начальный номер записи',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'default' => 100,
						'field' => 'limit',
						'label' => 'Количество возвращаемых записей',
						'rules' => '',
						'type' => 'int'
					)
			),
			'loadEvnReceptEditForm' => array(
				array(
						'field' => 'EvnRecept_id',
						'label' => 'Идентификатор рецепта',
						'rules' => 'trim|required',
						'type' => 'id'
					),
			),
			'loadEvnReceptGeneralEditForm' => array(
				array(
					'field'	=> 'EvnReceptGeneral_id',
					'label'	=> 'Идентификатор рецепта',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'fromExt6',
					'label'	=> 'флаг новой формы на Ext6',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'EvnReceptGeneralDrugLink_id',
					'label'	=> 'Идентификатор ссылки на медикамент из рецепта',
					'rules'	=> '',
					'type'	=> 'id'
				)
			),
			'loadEvnReceptViewForm' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadReceptList' => array(
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int' ),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int' ),
				array('field' => 'begDate', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date' ),
				array('field' => 'endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date' ),
				array('field' => 'Search_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string' ),
				array('field' => 'Search_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string' ),
				array('field' => 'Search_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string' ),
				array('field' => 'Search_BirthDay', 'label' => 'Отчество', 'rules' => '', 'type' => 'date' ),
				array('field' => 'Person_Snils', 'label' => 'СНИЛС', 'rules' => '', 'type' => 'snils' ),
				array('field' => 'EvnRecept_pid', 'label' => 'Идентификатор посещения', 'rules' => '', 'type' => 'id' ),
			),
			'loadStreamReceptList' => array(
				array(
					'field' => 'begDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'begTime',
					'label' => 'Время начала',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadEvnReceptList' => array(
				array(
					'field' => 'EvnRecept_pid',
					'label' => 'Идентификатор родителя рецепта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				)
			),
			'printRecept' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'IsForLpu',
					'label' => 'Флаг для ЛПУ',
					'rules' => '',
					'type' => 'int'
				)
			),
            'getReceptForm' => array(
                array(
                    'field' => 'EvnRecept_id',
                    'label' => 'Идентификатор рецепта',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
			'getReceptGeneralForm' => array(
                array(
                    'field' => 'EvnReceptGeneral_id',
                    'label' => 'Идентификатор общего рецепта',
                    'rules' => 'required',
                    'type'  => 'id'
                )
            ),
			'checkReceptValidByDate' => array(
				array('field' => 'EvnRecept_id', 'label' => 'Рецепт', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnReceptGeneral_id', 'label' => 'Рецепт', 'rules' => '', 'type' => 'id'),
				array('field' => 'Date', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date')
			),
			'deleteEvnReceptGeneral' => array(
				array('field' => 'EvnReceptGeneral_id', 'label' => 'Рецепт', 'rules' => 'required', 'type' => 'id')
			),
			'checkBeforeCreateEvnReceptGeneral' => array(
				array('field' => 'EvnCourseTreatDrug_id', 'label' => 'Медикамент', 'rules' => 'required', 'type' => 'id')
			),
			'checkFormEvnReceptGeneral' => array(
				array('field' => 'Drug_id', 'label' => 'Торговое наименование', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'МНН', 'rules' => 'required', 'type' => 'id')
			),
			'loadPersonDrugRequestPanel' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
			),
			'getEvnReceptList4Provider' => array(
			array(
				'field' => 'EvnRecept_Num',
				'label' => 'Номер рецепта ("Рецепт")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnRecept_Ser',
				'label' => 'Серия рецепта ("Рецепт")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnRecept_setDate',
				'label' => 'Дата выписки ("Рецепт")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnRecept_setDate_Range',
				'label' => 'Диапазон дат выписки ("Рецепт")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			 array(
				'field' => 'OrgFarmacyIndex_OrgFarmacy_id',
				'label' => 'Аптека ("Рецепт")',
				'rules' => '',
				'type' => 'int'
			),
                array(
				'field' => 'Person_Firname',
				'label' => 'Имя ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
                        array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения ("Пациент")',
				'rules' => 'trim',
				'type' => 'date'
			),
                        array(
				'default' => 0,
				'field' => 'ER_MedPersonal_id',
				'label' => 'Врач ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
                        array(
				'default' => 0,
				'field' => 'Drug_id',
				'label' => 'Торговое наименование ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			), 
			 array(
				'default' => 0,
				'field' => 'ReceptDelayType_id',
				'label' => 'ReceptDelayType_id',
				'rules' => '',
				'type' => 'int'
			),
                       array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 50,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
			),
			'pullOffServiceRecept' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentUcActReceptOut_setDT',
					'label' => 'Дата снятия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentUcActReceptList_outCause',
					'label' => 'Причина снятия',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'OrgFarmacy_id',
					'label' => 'Идентификатор аптечной организации',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getReceptOutDateAndCause' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkOutDocumentStatus' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deletePullOfServiceRecord' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteReceptWrongRecord' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getEvnCourseTreatDrugDetail' => array(
				array(
					'field'	=> 'EvnCourseTreatDrug_id',
					'label'	=> 'Строка лекарственного лечения',
					'rules'	=> 'required',
					'type'	=> 'id'
				)
			),
			'checkDrugByLinkIsStrong' => array(
				array(
					'field'	=> 'EvnReceptGeneralDrugLink_id',
					'label'	=> 'Связь строки лекарственного назначения с рецептом',
					'rules'	=> 'required',
					'type'	=> 'id'
				)
			),
			'saveAddingDrugToReceptGeneral' => array(
				array(
					'field'	=> 'EvnCourseTreatDrug_id',
					'label'	=> 'Строка лекарственного лечения',
					'rules'	=> 'required',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'EvnReceptGeneral_id',
					'label'	=> 'Идентификатор рецепта',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'fromExt6',
					'label'	=> 'флаг новой формы на Ext6',
					'rules'	=> '',
					'type'	=> 'int'
				)
			),

			'saveEvnReceptGeneral'	=> array(
				array('field'	=> 'EvnReceptGeneral_id',				'label'	=> 'Идентификатор общего рецепта',			'rules'	=> '',			'type'	=> 'id'),
				array('field'	=> 'EvnReceptGeneral_pid',				'label'	=> 'Идентификатор родительского события',	'rules'	=> '',			'type'	=> 'id'),
				array('field'	=> 'Lpu_id',							'label'	=> 'Идентификатор МО',						'rules'	=> '',			'type'	=> 'id'),
				array('field'	=> 'MedPersonal_id',					'label'	=> 'Идентификатор врача',					'rules'	=> '',			'type'	=> 'id'),
				array('field'	=> 'LpuSection_id',						'label'	=> 'Идентифиактор отделения',				'rules'	=> '',			'type'	=> 'id'),
				array('field'	=> 'Diag_id',							'label'	=> 'Идентификатор диагноза',				'rules'	=> '',			'type'	=> 'int'),
				
				array('field'	=> 'PersonEvn_id',						'label'	=> 'PersonEvn_id',							'rules'	=> 'required',	'type'	=> 'id'),
				array('field'	=> 'ReceptForm_id',						'label'	=> 'Форма рецепта',							'rules'	=> 'required',	'type'	=> 'int'),
				array('field'	=> 'ReceptType_id',						'label'	=> 'Тип рецепта',							'rules'	=> 'required',	'type'	=> 'int'),
				array('field'	=> 'EvnReceptGeneral_setDate',			'label'	=> 'Дата',									'rules'	=> 'trim',		'type'	=> 'date'),
				array('field'	=> 'EvnReceptGeneral_Ser',				'label'	=> 'Серия рецепта',							'rules'	=> '', 			'type'	=> 'string'),
				array('field'	=> 'EvnReceptGeneral_Num',				'label'	=> 'Номер рецепта',							'rules'	=> '',			'type'	=> 'string'),
				array('field'	=> 'EvnReceptGeneral_IsChronicDisease',	'label'	=> 'Пациенту с хроническими заболеваниями',	'rules'	=> '',			'type'	=> 'string'), //????
				array('field'	=> 'EvnReceptGeneral_IsSpecNaz',		'label'	=> 'По специальному назначению',			'rules'	=> '',			'type'	=> 'string'),
				array('field'	=> 'PrescrSpecCause_id',				'label'	=> 'Причина специального назначения',		'rules'	=> '',			'type'	=> 'id'),
				array('field'	=> 'EvnReceptGeneral_IsDelivery',		'label'	=> 'Выдан уполномоченному лицу',			'rules'	=> '',			'type'	=> 'string'),
				array('field'	=> 'ReceptUrgency_id',					'label'	=> 'Срочность',								'rules'	=> '',			'type'	=> 'int'),
				array('field'	=> 'ReceptValid_id',					'label'	=> 'Срок действия',							'rules'	=> '',			'type'	=> 'int'),
				array('field'	=> 'EvnReceptGeneral_Validity',			'label'	=> 'Срок действия, указанный врачом',		'rules'	=> '',			'type'	=> 'string'),
				array('field'	=> 'EvnReceptGeneral_endDate',			'label'	=> 'Рецепт действителен до',				'rules'	=> '',			'type'	=> 'date'),
				array('field'	=> 'EvnReceptGeneral_Period',			'label'	=> 'Периодичность отпуска',					'rules'	=> '',			'type'	=> 'string'),
				array('field'	=> 'EvnReceptGeneral_VKProtocolNum',	'label'	=> 'Номер протокола ВК',					'rules'	=> '',			'type'	=> 'string'),
				array('field'	=> 'EvnReceptGeneral_VKProtocolDT',		'label'	=> 'Дата протокола ВК',						'rules'	=> '',			'type'	=> 'date'),
				array('field'	=> 'CauseVK_id',						'label'	=> 'Основание для проведения ВК',			'rules'	=> '',			'type'	=> 'id'),

				array('field'	=> 'EvnCourseTreatDrug_id',				'label'	=> 'Строка лекарственного назначения',		'rules'	=> '',			'type'	=> 'int'),
				
				array('field'	=> 'EvnReceptGeneralDrugLink_id0',		'label'	=> 'Связь строки лекарственного назначения с рецептом (#0)',	'rules'	=> '',	'type'	=> 'int'),
				array('field'	=> 'Drug_Kolvo_Pack0',					'label'	=> 'Количество уп. (#0)',										'rules'	=> '',	'type'	=> 'string'),
				array('field'	=> 'Drug_Fas0',							'label'	=> 'Количество доз. (#0)',										'rules'	=> '',	'type'	=> 'string'),
				array('field'	=> 'Drug_Signa0',						'label'	=> 'signa (#0)',												'rules'	=> '',	'type'	=> 'string'),
	
				array('field'	=> 'EvnReceptGeneralDrugLink_id1',		'label'	=> 'Связь строки лекарственного назначения с рецептом (#1)',	'rules'	=> '',	'type'	=> 'int'),
				array('field'	=> 'Drug_Kolvo_Pack1',					'label'	=> 'Количество уп. (#1)',										'rules'	=> '',	'type'	=> 'string'),
				array('field'	=> 'Drug_Fas1',							'label'	=> 'Количество доз. (#1)',										'rules'	=> '',	'type'	=> 'string'),
				array('field'	=> 'Drug_Signa1',						'label'	=> 'signa (#1)',												'rules'	=> '',	'type'	=> 'string'),
				
				array('field'	=> 'EvnReceptGeneralDrugLink_id2',		'label'	=> 'Связь строки лекарственного назначения с рецептом (#2)',	'rules'	=> '',	'type'	=> 'int'),
				array('field'	=> 'Drug_Kolvo_Pack2',					'label'	=> 'Количество уп. (#2)',										'rules'	=> '',	'type'	=> 'string'),
				array('field'	=> 'Drug_Fas2',							'label'	=> 'Количество доз. (#2)',										'rules'	=> '',	'type'	=> 'string'),
				array('field'	=> 'Drug_Signa2',						'label'	=> 'signa (#2)',												'rules'	=> '',	'type'	=> 'string'),
				array('field'	=> 'EvnReceptGeneral_IsExcessDose',		'label'	=> 'Превышение дозировки',										'rules'	=> '',	'type'	=> 'string')
				
			),
			'deleteEvnReceptGeneralDrugLink' => array(
				array(
					'field'	=> 'EvnReceptGeneralDrugLink_id',
					'label'	=> 'EvnReceptGeneralDrugLink_id',
					'rules'	=> 'required',
					'type'	=> 'id'
				)
			),
			'searchEvnReceptGeneralList' => array(
				array(
					'field' => 'EvnRecept_setDate_Range',
					'label' => 'Диапазон дат',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Номер стартовой записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 50,
					'field' => 'limit',
					'label' => 'Количество записей',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field'	=> 'EvnReceptSearchDateType',
					'label'	=> 'Тип поиска по дате', //'vypis' - 'Дата выписки рецепта', 'obr'- 'Дата обращения в аптеку', 'obesp' - 'Дата обеспечения рецепта'
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field' => 'inValidRecept',
					'label' => 'С истекшим сроком действия', //1 - да, 0 - нет
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field'	=> 'ReceptDelayType_id',
					'label'	=> 'Статус рецепта',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'Person_Surname',
					'label'	=> 'Фамилия',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'Person_Firname',
					'label'	=> 'Имя',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'Person_Secname',
					'label'	=> 'Отчество',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'Person_Birthday',
					'label'	=> 'Дата рождения',
					'rules'	=> 'trim',
					'type'	=> 'date'
				),
				array(
					'field' => 'Person_Snils',
					'label'	=> 'СНИЛС',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'Polis_Ser',
					'label'	=> 'Серия полиса',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'Polis_Num',
					'label'	=> 'Номер полиса',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'Polis_EdNum',
					'label'	=> 'Единый номер полиса',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'EvnRecept_Ser',
					'label'	=> 'Серия рецепта',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'EvnRecept_Num',
					'label'	=> 'Номер рецепта',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'MedPersonal_Name',
					'label'	=> 'Врач',
					'rules'	=> '',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'Drug_Name',
					'label'	=> 'Медикамент',
					'rules'	=> '',
					'type'	=> 'string'
				)
			),
			'provideEvnReceptGeneralDrugLink' => array(
				array(
					'field' => 'EvnReceptGeneralDrugLink_id',
					'label'	=> 'Идентификатор строки медикамента в общем рецепте',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'Org_id',
					'label'	=> 'Идентифиактор организации',
					'rules'	=> '',
					'type'	=> 'int'
				)
			),
			'getEvnReceptKardioVisibleData' => array(
				array(
					'field'	=> 'parent_object',
					'label'	=> 'Родительский объект',
					'rules'	=> 'required',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'parent_object_value',
					'label'	=> 'Идентификатор родительского объекта',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'Lpu_id',
					'label'	=> 'Идентификатор МО',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'MedStaffFact_id',
					'label'	=> 'Место работы',
					'rules'	=> '',
					'type'	=> 'id'
				)
			),
			'undo_provideEvnReceptGeneralDrugLink' => array(
				array(
					'field'	=> 'GeneralReceptSupply_id',
					'label'	=> 'GeneralReceptSupply_id',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'EvnReceptGeneralDrugLink_id',
					'label'	=> 'EvnReceptGeneralDrugLink_id',
					'rules'	=> 'required',
					'type'	=> 'int'
				)
			),
			'getReceptGenFormList' => array(
				array(
					'field'	=> 'group',
					'label'	=> 'Группа ЛП',
					'rules'	=> '',
					'type'	=> 'string'
				)
			),
			'getLastPersonPrivilegeModerationData' => array(
				array(
					'field'	=> 'Person_id',
					'label'	=> 'Пациент',
					'rules'	=> 'required',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'PrivilegeType_id',
					'label'	=> 'Категория льготы',
					'rules'	=> 'required',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'EvnRecept_setDate',
					'label'	=> 'Дата выписки ',
					'rules'	=> 'required',
					'type' => 'date'
				)
			),
			'getPersonAddressAndDocData' => array(
				array(
					'field'	=> 'Person_id',
					'label'	=> 'Пациент',
					'rules'	=> 'required',
					'type'	=> 'id'
				)
			),
			'loadPersonAmbulatCardCombo' => array(
				array(
					'field'	=> 'PersonAmbulatCard_id',
					'label'	=> 'Идентификатор карты',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'Lpu_id',
					'label'	=> 'Идентификатор МО',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'Person_id',
					'label'	=> 'Идентификатор пациента',
					'rules'	=> '',
					'type'	=> 'id'
				),
				array(
					'field'	=> 'Date',
					'label'	=> 'Дата',
					'rules'	=> '',
					'type'	=> 'date'
				),
				array(
					'field'	=> 'LpuAttachType_Code',
					'label'	=> 'Тип прикрепления',
					'rules'	=> 'trim',
					'type'	=> 'string'
				),
				array(
					'field'	=> 'query',
					'label'	=> 'Строка запроса',
					'rules'	=> 'trim',
					'type'	=> 'string'
				)
			),
			'getDosKurs' => array(
				array(
					'field'	=> 'DrugComplexMnn_id',
					'label'	=> 'Идентификатор комплексного МНН',
					'rules'	=> 'required',
					'type'	=> 'id'
				)
			),
			'isNarcoOrStrongDrug' => array(
				array(
				'field'	=> 'DrugComplexMnn_id',
				'label'	=> 'Комплексное МНН',
				'rules'	=> '',
				'type'	=> 'id'
				),
				array(
				'field'	=> 'Drug_id',
				'label'	=> 'Торговое наименование',
				'rules'	=> '',
				'type'	=> 'id'
				)
			)
		);
		$this->inputRules['searchEvnRecept'] = array_merge($this->inputRules['searchEvnRecept'],getAddressSearchFilter());
		$this->inputRules['searchEvnReceptInCorrect'] = array_merge($this->inputRules['searchEvnReceptInCorrect'],getAddressSearchFilter());

	}

	/**
	 * Индекс
	 */
    function Index() {
		return false;
	}


	/**
	*  Проверка возможности сохранения рецепта
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function checkEvnRecept() {
		$data = $this->ProcessInputData('checkEvnRecept', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkEvnRecept($data);
		echo $response;
		return true;
	}

    /**
     * Проверка на соответствие диагноза выбранной льготе
     */
    function checkPrivDiag(){
        $data = $this->ProcessInputData('checkPrivDiag', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->checkPrivDiag($data);

		$this->ReturnData($response);
        return true;
    }

	/**
	*  Проверка соответствия диагноза выписываемому лекарственному средству
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function checkEvnMatterRecept() {
		$data = $this->ProcessInputData('checkEvnMatterRecept', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkEvnMatterRecept($data);
        //var_dump($response);
        //$this->ProcessModelList($response, true, 'При проверки соответствия рецепта и ЛС возникли ошибки')->ReturnData();
        $this->ReturnData($response);
		//echo $response;
        //return $response;
		return true;
	}

	/**
	*  Проверка на повторную выписку рецепта ЛКО Кардио
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта (rls)
	*/
	function checkReceptKardioReissue() {
		$data = $this->ProcessInputData('checkReceptKardioReissue');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkReceptKardioReissue($data);
        $this->ReturnData($response);

		return true;
	}

	/**
	*  Проверка на выписку ЛП Тикагрелор в стационаре и поликлинике (только для рецептов по рограмме "ЛЛО Кардио")
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта (rls)
	*/
	function checkReceptKardioTicagrelor() {
		$data = $this->ProcessInputData('checkReceptKardioTicagrelor', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkReceptKardioTicagrelor($data);
        $this->ReturnData($response);

		return true;
	}

	/**
	*  Проверка даты выписки рецепта
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта (rls)
	*/
	function checkReceptKardioSetDate() {
		$data = $this->ProcessInputData('checkReceptKardioSetDate');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkReceptKardioSetDate($data);
		$this->ReturnData($response);

		return true;
	}

	/**
	*  Удаление рецепта
	*  Входящие данные: $_POST['EvnRecept_id']
	*  На выходе: JSON-строка
	*  Используется: форма поиска льгот
	*/
	function deleteEvnRecept() {
		$data = $this->ProcessInputData('deleteEvnRecept', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnRecept($data);
		$this->ProcessModelSave($response, true, 'При удалении рецепта возникли ошибки')->ReturnData();
		return true;
	}

	/**
	*  Удаление рецепта
	*  Входящие данные: $_POST['EvnReceptGeneral_id']
	*  На выходе: JSON-строка
	*/
	function deleteEvnReceptGeneral() {
		$data = $this->ProcessInputData('deleteEvnReceptGeneral', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnReceptGeneral($data);
		$this->ProcessModelSave($response, true, 'При удалении рецепта возникли ошибки')->ReturnData();
		return true;
	}

	/**
	*  Проверки перед созданием общего рецепта
	*  На выходе: JSON-строка
	*/
	function checkBeforeCreateEvnReceptGeneral() {
		$data = $this->ProcessInputData('checkBeforeCreateEvnReceptGeneral', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkBeforeCreateEvnReceptGeneral($data);
		//var_dump($response);die;
		//$this->ProcessModelSave($response, true, true)->ReturnData();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Проверка на вид формы платного рецепта в ЭМК ExtJs6
	 *  На выходе: JSON-строка
	 */
	function checkFormEvnReceptGeneral() {
		$data = $this->ProcessInputData('checkFormEvnReceptGeneral', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkFormEvnReceptGeneral($data);
		//var_dump($response);die;
		//$this->ProcessModelSave($response, true, true)->ReturnData();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Снятие отметки к удалению
	 */
	function UndoDeleteEvnRecept(){
		$data = $this->ProcessInputData('UndoDeleteEvnRecept', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->UndoDeleteEvnRecept($data);
		$this->ProcessModelSave($response, true, 'При удалении рецепта возникли ошибки')->ReturnData();
		return true;
	}
	/**
	*  Получение номера рецепта
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function getReceptNumber($returnValue = false, $convertFromUTF8 = true) {
		$data = $this->ProcessInputData('getReceptNumber', true);
		
		$this->load->model("Options_model", "opmodel");
		$options = $this->opmodel->getOptionsGlobals($data);
		
		if ($options['globals']['use_numerator_for_recept'] == 2 || !empty($data['isGeneral'])) { //для нельготных рецептов нумераторы используются по умолчанию
			// через нумератор
			$this->load->model('Numerator_model');

			$name = 'Выписка рецепта';
			$query = null;

			if (!empty($data['isGeneral'])) { //общие рецепты
				$sysname = 'EvnReceptGeneral';

				switch($data['ReceptForm_id']) {
					case 2:
						$query = 'ReceptForm_id=2';
						$name = 'Выписка простого рецепта 1-МИ';
						break;
					case 3:
						$query = 'ReceptForm_id=3';
						$name = 'Выписка простого рецепта по форме  107-1/у';
						break;
					case 5:
						$query = 'ReceptForm_id=5';
						$name = 'Выписка простого рецепта 148-1/у-88';
						break;
					case 8:
						$query = 'ReceptForm_code =’107/у-НП’';
						$name = 'Выписка рецепта на НС и ПВ по форме  107/у-НП';
						break;
				}
			} else { //льготные рецепты
				$sysname = 'EvnRecept';

				if (!empty($data['WhsDocumentCostItemType_id'])) {
					$obj_query_data = $this->dbmodel->getNumeratorObjectQueryByWhsDocumentCostItemTypeId($data['WhsDocumentCostItemType_id']);
					$query = $obj_query_data['query'];
					$name = $obj_query_data['name'];
				}
			}
			switch($data['ReceptType_id']) {
				case 3:
					$query = 'ReceptType_id = 3';
					break;
			}

			$params = array(
				'NumeratorObject_SysName' => $sysname,
				'NumeratorObject_Query' => $query,
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id'],
				'onDate' => $data['EvnRecept_setDate']
			);
			
			$resp = $this->Numerator_model->getNumeratorNum($params, null);
			$val = array();
			if (!empty($resp['Numerator_Num'])) {
				$this->load->model('LpuPassport_model');
				$OKATO = $this->LpuPassport_model->getOKATO()[0]['OKATO'];
				$val['EvnRecept_Ser'] = str_replace('{YY}', date('y'), $resp['Numerator_Ser']);
				$val['EvnRecept_Num'] = str_replace('{YY}', date('y'), $resp['Numerator_Num']);
				$val['EvnRecept_Ser'] = str_replace('{ОКАТО}', $OKATO, $val['EvnRecept_Ser']); //первый параметр на русском
				$val['EvnRecept_Num'] = str_replace('{ОКАТО}', $OKATO, $val['EvnRecept_Num']); //первый параметр на русском
				$val['SerNum_Source'] = 'Numerator';
				if ( $returnValue === true ) {
					return $val['EvnRecept_Num'];
				}
				else {
					$this->ReturnData($val);
				}
			}
			else {
				if (!empty($resp['Error_Msg'])) {
					$this->ReturnError($resp['Error_Msg']);
				}
				else {
					$this->ReturnError('Не задан активный нумератор для "'.$name.'". Обратитесь к администратору системы.');
				}
			}
		}
		elseif (getRegionNick() == 'msk' && $options['globals']['use_external_service_for_recept_num'] == 1) {
			$this->load->model('ServiceEMIAS_model');
			$result = $this->ServiceEMIAS_model->getEvnReceptSerialNum($data);

			if ( is_array($result) && count($result) > 0 ) {
				if (!empty($result['Error_Msg'])) {
					$this->ReturnError($result['Error_Msg']);
					return false;
				}

				$val['EvnRecept_Ser'] = $result['Recipe']['RecipeSerial'];
				$val['EvnRecept_Num'] = $result['Recipe']['RecipeNumber'];
				$this->ReturnData($val);
			}
		}
		else {
			// по классической схеме
			$prefix = '1';
			$punkt  = '0';
			$val    = array();

			if (getRegionNick() == 'buryatiya' && !empty($data['isRLS']) && $data['isRLS']) {
				$result = $this->dbmodel->getReceptNumberRls($data);
			} else {
				$result = $this->dbmodel->getReceptNumber($data);
			}

			if ( is_array($result) && count($result) > 0 ) {
				if (getRegionNick() == 'khak') {
					$val['EvnRecept_Num'] = $result[0]['rnumber'];
				} else {
					$val['EvnRecept_Num'] = $prefix . $punkt . sprintf('%06d', $result[0]['rnumber']);
				}
			}

			if ( $returnValue === true ) {
				return $val['EvnRecept_Num'];
			}
			else {
				$this->ReturnData($val);
			}
		}
		
		return true;
	}


    /**
    *  Получение справочника торговых наименований медикаментов
    *  Входящие данные: ...
    *  На выходе: JSON-строка
    *  Используется: форма редактирования рецепта
    */
	function loadDrugList() {
		$data = $this->ProcessInputData('loadDrugList', true);
        if (!(isset($data['DopRequest']) && $data['DopRequest'] == 2)) {
            if((!isset($data['Drug_id'])) && ((!isset($data['ReceptFinance_Code'])) /*|| (!isset($data['ReceptType_Code'])) || (!isset($data['Date']))*/)) {
                return false;
            }
        }
		if ($data) {
			$response = $this->dbmodel->loadDrugList($data);
			if (!empty($response['Error_Msg'])) {
				$this->ReturnError($response['Error_Msg']);
				return false;
			}
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	*  Получение списка заявленных медикаментов для пациента в соответствии с закупленной позицией
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта (комбо "Заявка")
	*/
	function loadDrugRequestMnnList() {
		$data = $this->ProcessInputData('loadDrugRequestMnnList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugRequestMnnList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	*  Получение списка заявленных и выписанных медикаментов на пациента
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function loadDrugRequestOtovGrid() {
		$val  = array();

		$data = $this->ProcessInputData('loadDrugRequestOtovGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugRequestOtovGrid($data);

		$groupped = array();
		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				// по 1 заявке и 1 врачу должны сгруппироваться.
				if ( empty($row['DrugRequestRow_id']) ) {
					$row['DrugRequestRow_Kolvo'] = NULL;
				}

				if (!empty($row['DrugRequestRow_id']) && !empty($row['ER_MedPersonal_id'])) {
					// группируем
					$key = $row['DrugRequestRow_id'] .'_'.$row['ER_MedPersonal_id'];
					if (!empty($groupped[$key])) {
						$groupped[$key]['EvnRecept_Kolvo'] += $row['EvnRecept_Kolvo'];
					} else {
						$groupped[$key] = $row;
					}
				} else {
					// не группируем
					$groupped[] = $row;
				}
			}
		}

		$i = 0;
		foreach($groupped as $one) {
			$i++;
			$one['ERDRR_id'] = $i;
			$val[] = $one;
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение списка заявленных медикаментов для пациента
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function loadDrugRequestRowGrid() {
		$data = $this->ProcessInputData('loadDrugRequestRowGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugRequestRowGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Получение списка врачей, которые заявляли какие-либо медикаменты на пациента
	*  Входящие данные: $_POST['EvnRecept_setDate'], $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function loadDrugRequestMedPersonalList() {
		$data = $this->ProcessInputData('loadDrugRequestMedPersonalList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugRequestMedPersonalList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение предшественника МО
	 */
	function getLpuPrev() {
		$data = $this->ProcessInputData('getLpuPrev',true);
		if($data) {
			$response = $this->dbmodel->getLpuPrev($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
		else {
			return false;
		}
	}

	/**
	* Список аптек, в которых есть медикамент
	*/
	function loadOrgFarmacyList() {
		$val = array();

		$data = $this->ProcessInputData('loadOrgFarmacyList', true);
		if ($data === false) { return false; }

		$data['Lpu_id'] = $_SESSION['lpu_id'];

		if ( isset($data['EvnRecept_IsExtemp']) && $data['EvnRecept_IsExtemp'] == 1 ) {
			if ( !isset($data['ReceptFinance_Code']) ) {
				$err = 'Не задан код типа финансирования';
			}
			else if ( !isset($data['ReceptType_Code']) ) {
				$err = 'Не задан тип рецепта';
			}
			else if ( !isset($data['Drug_id']) ) {
				$err = 'Не задан идентификатор медикамента';
			}

			if ( strlen($err) > 0 ) {
				echo json_return_errors($err);
				return false;
			}
		}

		$response = $this->dbmodel->loadOrgFarmacyList($data);

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$row['DrugOstat_Kolvo'] = ($row['DrugOstat_Kolvo'] != '' ? number_format($row['DrugOstat_Kolvo'], 2, '.', '') : '');
				$val[] = $row;
			}
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования рецепта
	*  Входящие данные: $_POST['EvnRecept_id'],
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function loadEvnReceptEditForm() {
		$data = $this->ProcessInputData('loadEvnReceptEditForm', true);
		if ($data) {
			$response = $this->dbmodel->loadEvnReceptEditForm($data);
            if(isset($response[0]['Error_Code']) && $response[0]['Error_Code'] == 'error_cause_lpu')
            {
                $this->ReturnError('Вы не можете открыть рецепт, созданный в другой МО');
                return false;
            }
			$this->ProcessModelList($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Получение данных для редактирования общего рецепта
	*/
	function loadEvnReceptGeneralEditForm() {
		$data = $this->ProcessInputData('loadEvnReceptGeneralEditForm', true);
		
		if ($data) {
			$response = $this->dbmodel->loadEvnReceptGeneralEditForm($data);
            if(isset($response[0]['Error_Code']) && $response[0]['Error_Code'] == 'error_cause_lpu')
            {
                $this->ReturnError('Вы не можете открыть рецепт, созданный в другой МО');
                return false;
            }
			$this->ProcessModelList($response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*  Получение списка рецептов
	*  Входящие данные: $_POST['EvnRecept_pid'],
	*                   $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: список льготных рецептов
	*/
	function loadEvnReceptList() {
		$data = $this->ProcessInputData('loadEvnReceptList', true);
		if ($data === false) { return false; }

		if ( $data['EvnRecept_pid'] == 0 && $data['Person_id'] == 0 ) {
			echo false;
			return false;
		}

		$response = $this->dbmodel->loadEvnReceptList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	
	/**
	*  Сохранение рецепта
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function saveEvnRecept($recept_number = null, $convertFromUTF8 = true) {
		$region_nick = $_SESSION['region']['nick'];

		$data = $this->ProcessInputData('saveEvnRecept', true, true, false, false, $convertFromUTF8);
		if ($data === false) { return false; }

		$this->load->helper('Options');
		$this->load->model("Options_model", "opmodel");
		$options = $this->opmodel->getOptionsAll($data);

		// если на бланке, то не меняем номер.
		if ( !empty($recept_number) && $data['ReceptType_id'] != 1 )	{
			$data['EvnRecept_Num'] = $recept_number;
		}

        /*if (($_SESSION['region']['nick'] == 'ufa')&&(( $data['ReceptFinance_id'] == 2 )&&($data['is_mi_1']!='true')&& ($data['ReceptType_id'] != 1))) {
            $data['EvnRecept_Num'] = str_pad($data['EvnRecept_Num'], 13, '0', STR_PAD_LEFT);
        }*/

		$err = "";

		switch ( $data['EvnRecept_IsExtemp'] ) {
			case 1:
				$data['EvnRecept_ExtempContents'] = NULL;

				if ( !isset($data['Drug_id']) ) {
					$err = 'Поле "Торговое наименование" обязательно для заполнения';
				}
				else if ( !isset($data['Drug_IsMnn']) ) {
					$err = 'Поле "Выписка по МНН" обязательно для заполнения';
				}
			break;

			case 2:
				$data['Drug_id'] = NULL;
				$data['Drug_IsKEK'] = NULL;
				$data['Drug_IsMnn'] = NULL;
				$data['DrugRequestRow_id'] = NULL;

				if ( !isset($data['EvnRecept_ExtempContents']) ) {
					$err = 'Не задан состав экстемпорального рецепта';
				}
			break;

			default:
				$err = 'Неверное значение признака экстемпорального рецепта';
			break;
		}

		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}
        /*
		$person_is_refuse = $this->dbmodel->checkPersonIsRefuse($data);
		if ($person_is_refuse['success'] == false) {
			array_walk($person_is_refuse, 'ConvertFromWin1251ToUTF8');
			echo json_encode($person_is_refuse);
			return false;
		}
        */
		$compare_result = swCompareDates(substr($data['EvnRecept_setDate'], 8, 2) . '.' . substr($data['EvnRecept_setDate'], 5, 2) . '.' . substr($data['EvnRecept_setDate'], 0, 4), date('d.m.Y'));
		if ( -1 == $compare_result[0] ) {
			$response = array('success' => false, 'Error_Msg' => 'Дата выписки рецепта не должна быть больше текущей даты');
			array_walk($response, 'ConvertFromWin1251ToUTF8');
			echo json_encode($response);
			return true;
		}

		$compare_result = swCompareDates('01.01.1900', substr($data['EvnRecept_setDate'], 8, 2) . '.' . substr($data['EvnRecept_setDate'], 5, 2) . '.' . substr($data['EvnRecept_setDate'], 0, 4));
		if ( -1 == $compare_result[0] ) {
			$response = array('success' => false, 'Error_Msg' => 'Дата выписки рецепта должна быть больше 01.01.1900');
			array_walk($response, 'ConvertFromWin1251ToUTF8');
			echo json_encode($response);
			return true;
		}

		// Проверки
		/*if ( $options['recepts']['unique_ser_num'] === true || $options['recepts']['unique_ser_num'] == '1' ) {
			$check_recept_ser_num = $this->dbmodel->checkReceptSerNum($data);
			if ( $check_recept_ser_num == -1 ) {
				$response = array('success' => false, 'Error_Msg' => 'Ошибка при проверке уникальности серии и номера рецепта');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				echo json_encode($response);
				return true;
			}
			else if ( $check_recept_ser_num > 0 ) {
				$response = array('success' => false, 'Error_Msg' => 'Рецепт с такими серией и номером уже был выписан ранее');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				echo json_encode($response);
				return true;
			}
		}*/

		/*$this->load->model('PrivilegeAccessRights_model', 'parmodel');
		$check_privilege_access_rights = $this->parmodel->checkPrivilegeAccessRights($data);
		if ( $check_privilege_access_rights == false ) {
			$response = array('success' => false, 'Error_Msg' => 'Ограничен доступ ко льготе');
			array_walk($response, 'ConvertFromWin1251ToUTF8');
			echo json_encode($response);
			return false;
		}*/
		if ( !checkPrivilegeTypeAccessRights($data['PrivilegeType_id']) ) {
			$this->ReturnError('Ограничен доступ к льготе');
			return false;
		}

        $check_recept_person_polis = $this->dbmodel->checkReceptPersonPolis($data);
        if ( $check_recept_person_polis == false ) {
            $response = array('success' => false, 'Error_Msg' => 'У пациента отсутствует полис ОМС');
            array_walk($response, 'ConvertFromWin1251ToUTF8');
            echo json_encode($response);
            return false;
        }

		$EvnRecept_setDate = new DateTime($data['EvnRecept_setDate']);
		$checkDate = new DateTime('01.01.2016');

		//06.01.2016 https://redmine.swan.perm.ru/issues/80151 shorev : Временно отлкючил эту проверку, т.к. сначала придется что-то решить насчет пациентов без кодов АК.
		/*if ( $EvnRecept_setDate >= $checkDate ) {
			$check_recept_person_card = $this->dbmodel->checkReceptPersonCard($data);
			if ( $check_recept_person_card == false ) {
				$this->ReturnError('У пациента отсутствует амбулаторная карта');
				return false;
			}
		}*/

		if ( $options['recepts']['validate_start_date'] === true || $options['recepts']['validate_start_date'] == '1' ) {
			$check_recept_person_birthday = $this->dbmodel->checkReceptPersonBirthday($data);
			if ( $check_recept_person_birthday == -1 ) {
				$response = array('success' => false, 'Error_Msg' => 'Ошибка при проверке даты выписки пациента');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				echo json_encode($response);
				return true;
			}
			else if ( $check_recept_person_birthday == 0 ) {
				$response = array('success' => false, 'Error_Msg' => 'Дата выписки рецепта меньше даты рождения пациента');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				echo json_encode($response);
				return true;
			}
		}

		//проверки нумератора
		if (!in_array($region_nick, array('kz'))) {
			$numerator_err_msg = null;
			switch($data['ReceptType_id']) {
				case 1: //На бланке
					if ($options['recepts']['blank_form_creation_method'] == 2) { //Способ создания бланков льготного рецепта: из информационной системы с генерацией номеров
						$check_num_result = $this->dbmodel->checkNumInRezerv($data);
						if ($check_num_result === false) {
							$numerator_err_msg = 'Номер бланка должен попадать в один из введенных диапазонов резервирования';
						} else if (!empty($check_num_result['Error_Msg'])) {
							$numerator_err_msg = $check_num_result['Error_Msg'];
						}
					}
					break;
				case 2: //На листе
					if ($options['globals']['use_numerator_for_recept'] == 2) { //Использовать нумератор для рецептов «на листе»: Да
						$check_num_result = $this->dbmodel->checkNumInRezerv($data);
						if (!empty($check_num_result['Error_Msg'])) {
							$numerator_err_msg = $check_num_result['Error_Msg'];
						}
					}
					break;
			}
			if (!empty($numerator_err_msg)) {
				$this->ReturnError($numerator_err_msg);
				return false;
			}
		}
		
        /*
		if ( $options['recepts']['validate_end_of_lgot'] === true || $options['recepts']['validate_end_of_lgot'] == '1' ) {
			$check_recept_privilege_date = $this->dbmodel->checkReceptPrivilegeDate($data);
			if ( $check_recept_privilege_date == -1 ) {
				$response = array('success' => false, 'Error_Msg' => 'Ошибка при проверке срока действия льготы на дату выписки рецепта');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				echo json_encode($response);
				return true;
			}
			else if ( $check_recept_privilege_date == 0 ) {
				$response = array('success' => false, 'Error_Msg' => 'Льгота не является действительной на дату выписки рецепта');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				echo json_encode($response);
				return true;
			}
		}

		if ( $options['recepts']['block_drug_extracting'] === true || $options['recepts']['block_drug_extracting'] == '1' ) {
			$check_drug_is_actual = $this->dbmodel->checkDrugIsActual($data);
			if ( $check_drug_is_actual == -1 ) {
				$response = array('success' => false, 'Error_Msg' => 'Ошибка при проверке медикамента');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				echo json_encode($response);
				return true;
			}
			else if ( $check_drug_is_actual == 0 ) {
				$response = array('success' => false, 'Error_Msg' => 'Медикамент не найден в списке льготных медикаментов на текущую дату');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				echo json_encode($response);
				return true;
			}
		}

		if ( $options['recepts']['block_drug_extracting'] === true || $options['recepts']['block_drug_extracting'] == '1' ) {
			$check_recept_drug_ostat = $this->dbmodel->checkReceptDrugOstat($data);
			if ( $check_recept_drug_ostat == -1 ) {
				$response = array('success' => false, 'Error_Msg' => 'Ошибка при проверке наличия медикамента в аптеках');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				return json_encode($response);
			}
			else if ( $check_recept_drug_ostat == 0 ) {
				$response = array('success' => false, 'Error_Msg' => 'На текущую дату медикамент отсутствует на остатках в аптеках');
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				return json_encode($response);
			}
		}
        */
		// Проверка врача, отделения, льготы и медикамента на дату выписки рецепта
		// Передаем Drug_id, EvnRecept_setDate, MedPersonal_id, LpuSection_id, Lpu_id
		// upd [2012-05-14 13:27]: добавил в checkEvnReceptValues проверку на код диагноза при выписке рецепта по 7 нозологиям
		// https://redmine.swan.perm.ru/issues/8253
		$response = $this->dbmodel->checkEvnReceptValues($data);

		if ( $response[0]['success'] == false ) {
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');
			$val = $response[0];
			$this->ReturnData($val);
			return false;
		}

		// Проверяем наличие медикамента на остатках в выбранной аптеке
		// и получаем значение поля EvnRecept_IsNotOstat
		$data['EvnRecept_IsNotOstat'] = $this->dbmodel->checkDrugOstat($data);
		if ( $data['EvnRecept_IsNotOstat'] == 0 ) {
			$response = array('success' => false, 'Error_Msg' => 'Ошибка при определении наличия остатков медикамента в выбранной аптеке');
			array_walk($response, 'ConvertFromWin1251ToUTF8');
			echo json_encode($response);
			return true;
		}

		// var_dump($data); return false;

		// Сохранение
		$response = $this->dbmodel->saveEvnRecept($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}

    /**
     * Сохранение дополнительной заявки
     */
    function saveDrugRequestDop(){
        $data = $this->ProcessInputData('saveDrugRequestDop', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->saveDrugRequestDop($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();

        return true;
    }

	/**
	 * Печать всего списка рецептов полученного при поиске
	 */
	function printSearchEvnRecept() {
		$this->load->library('parser');

		$data = getSesionParams();
		$err = getInputParams($data, $this->inputRules['searchEvnRecept'], false);

		if ( $err!="" ) {
			echo json_return_errors($err);
		}
		else {
			$response = $this->dbmodel->getEvnReceptList($data);
			if ( !is_array($response) ) {
				die('Ошибка при выполнении запроса к базе данных');
			}

			for ( $i = 0; $i < count($response); $i++ ) {
				$response[$i]['Record_Num'] = $i + 1;
			}
			$this->parser->parse('evnrecept_search_results', array('search_results' => $response));
		}
	}


	/**
	 * Поиск рецептов в журнале отсрочки
	 */
	function searchEvnReceptInCorrect() {
		$data = $this->ProcessInputData('searchEvnReceptInCorrect', true);
		if ($data === false) { return false; }

		$recepts = $this->dbmodel->getEvnReceptInCorrectList($data);
		if ( is_array($recepts) && count($recepts) > 0 ) {
			foreach ($recepts['data'] as &$row) {
				// Зануляем поле Отсрочка у просроченных рецептов
				if ($row['ReceptDelayType_Name'] == 'Просрочен')
					$row['EvnRecept_otsDay'] = NULL;
				array_walk($row, 'ConvertFromWin1251ToUTF8');
			}
			$this->ReturnData($recepts);
		}
		else {
			echo json_encode(array());
		}
	}
	
	/**
	 * Количество рецептов в журнале отсрочки
	 */
	function getIncRecordsCount() {
		$data = $this->ProcessInputData('searchEvnReceptInCorrect', true);
		if ($data === false) { return false; }

		$data['onlyCount'] = true;
		$cnt = $this->dbmodel->getEvnReceptInCorrectList($data);
		
		$val = array('Records_Count' => $cnt);
		$this->ReturnData($val);
	}

	/**
	 * Печать рецептов в журнале отсрочки
	 */
	function printSearchEvnReceptInCorrect() {
		$this->load->library('parser');

		$data = getSessionParams();
		$err = getInputParams($data, $this->inputRules['searchEvnReceptInCorrect'], false);

		if ($err!="") {
			echo json_return_errors($err);
		}
		else {
			$data['print'] = true; // полный список для печати
			$response = $this->dbmodel->getEvnReceptInCorrectList($data);
			if ( !is_array($response) ) {
				die('Ошибка при выполнении запроса к базе данных');
			}
			if (isset($response['data']) && is_array($response['data'])) {
				$response = $response['data'];
				for ( $i = 0; $i < count($response); $i++ ) {
					$response[$i]['Record_Num'] = $i + 1;
				}
				if (isMinZdrav())
					$this->parser->parse('evnreceptinc_search_minzdrav_results', array('search_results' => $response));
				else if ($data['session']['region']['nick'] == 'ufa') 
                                    $this->parser->parse('evnreceptinc_search_results_ufa', array('search_results' => $response));
				else $this->parser->parse('evnreceptinc_search_results', array('search_results' => $response));
			} else {
				die('Ошибка при выполнении запроса к базе данных');
			}

		}
	}

	/**
	 * Получить дозу из ответа
	 */
	function getDrugDose($row) {
		return (string)($row['Drug_Fas'] * $row['EvnRecept_Kolvo']); // Drug_Fas * EvnRecept_Kolvo
	}
	
	/**
	 * Получить полную дозу из ответа
	 */
	function getDrugFullDose($row) {
		return (string)$row['Drug_DoseFull']; // Drug_DoseFull
	}

	/**
	 * Получение типа печати
	 */
	function getPrintType()
	{
		$this->load->helper('Options');
		$options = getOptions();
		$server_port = $_SERVER["SERVER_PORT"];
		$server_http = (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])?$_SERVER["HTTP_X_FORWARDED_PROTO"]:'http');
		echo json_encode(array("success"=>true, "PrintType" => $options['recepts']['print_format'], "server_port"=>$server_port, "server_http"=>$server_http, "CopiesCount" => $options['recepts']['copies_count']));
		//$server_url = getPromedUrl();
		//echo json_encode(array("success"=>true, "PrintType" => $options['recepts']['print_format'], "server_url"=>$server_url));
		return true;

	}
	
	/**
	 * Получить название шаблона
	 */
	function getReceptTemplateName($row, $data) {
		if ( 1 == $row['ReceptType_Code'] ) {
			return 'recept_template_blank';
		}
		else {
			// Получаем настройки
			$options = getOptions();
			if($options['recepts']['copies_count'] == 1)
                return 'recept_template_2_pages';
            else
            {
                switch ( $options['recepts']['print_format'] ) {

                    case 1:
                        // В 3-х экземплярах, на двух листах формата А4 и двумя корешками, на Уфе с тремя корешками
                        return 'recept_template_list_1';
                    break;

                    case 2:
                        // В 3-х экземплярах, на трех листах формата А5 и двумя корешками
                        return 'recept_template_list_2';
                    break;

                    case 3:
                        // В 3-х экземплярах, на одном листе формата А4 и двумя корешками
                        return 'recept_template_list_3';
                    break;

                    default:
                        echo "Необходимо задать формат печати в настройках рецепта";
                        return false;
                    break;
                }
            }
		}
		
		If (isset($data['IsForLpu']) && $data['IsForLpu'] == 1) {
			return 'recept_template_list_0';
		}
	}
	
	
	/**
	*  Печать рецепта
	*  Входящие данные: $_GET['EvnRecept_id']
	*  На выходе: форма для печати рецепта
	*  Используется: форма редактирования рецепта
	*/
	function printRecept($EvnRecept_id = null, $ReturnString = false) {
		$this->load->helper('Barcode');
		$this->load->helper('Options');
		$this->load->library('parser');

		$data = $this->ProcessInputData('printRecept', true);
		if ($data === false) { return false; }

		if ( !empty($EvnRecept_id) ) {
			$data['EvnRecept_id'] = $EvnRecept_id;
		}

		if ( empty($data['EvnRecept_id'])) {
			echo 'Неверно заданы параметры';
			return true;
		}


		// Получаем данные по рецепту

        if(in_array($_SESSION['region']['nick'],array('saratov','khak','pskov','ekb','astra','kareliya','ufa')))
		//if ($_SESSION['region']['nick'] == 'saratov')
			$response = $this->dbmodel->getReceptFieldsSaratov($data);
		else
			$response = $this->dbmodel->getReceptFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по рецепту';
			return true;
		}
		//var_dump($response[0]['Drug_Fas']);die;
		$recept_template = $this->getReceptTemplateName($response[0], $data);

        if (($response[0]['ReceptForm_Code'] == '1-МИ') && ($response[0]['ReceptType_Code']!=1))
            $recept_template .= '_1-mi';

		$drug_code_array           = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$evn_recept_set_date_array = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$lpu_unit_set_array        = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$med_personal_code_array   = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$person_address_array      = array('&nbsp;', '&nbsp;');
		$person_birthday_array     = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$person_snils_array        = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$person_state_array        = array('&nbsp;', '&nbsp;');
		$polis_ser_num_array       = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$privilege_type_code_array = array('&nbsp;', '&nbsp;', '&nbsp;');
        $lpu_ogrn_array            = array('&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;','&nbsp;');
		$noz_form_code_array 	   = array('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
		$recept_discount_array     = array('none', 'none');
		$recept_finance_array      = array('none', 'none', 'none');


		

		if ( $response[0]['Drug_Fas'] == 0 ) {
			$response[0]['Drug_Fas'] = 1;
		}

		$drug_dose_count = $this->getDrugDose($response[0]);
		$dose_int = (int)($drug_dose_count);
		$dose_float = (float)($drug_dose_count);
        //var_dump($dose_float);die;
		if($dose_int==$dose_float){
			$drug_dose_count = $dose_int;
		}
        else
        {
            $drug_dose_count = $dose_float;
        }
		$drug_dose_full = $this->getDrugFullDose($response[0]);

		$diag_code               = (string)$response[0]['Diag_Code']; // Diag_Code
		$drug_code               = (string)sprintf('%08d', $response[0]['Drug_Code']); // Drug_Code
		$drug_dose               = (string)$response[0]['Drug_Dose']; // Drug_Dose
		$drug_form_name          = (string)$response[0]['DrugForm_Name']; // DrugForm_Name
		$drug_is_kek             = $response[0]['Drug_IsKEK']; // Drug_IsKEK
		$drug_is_mnn             = $response[0]['Drug_IsMnn']; // Drug_IsMnn
		$drug_mnn_torg_code      = (string)$response[0]['DrugMnnTorg_Code']; // DrugMnnTorg_Code
		$drug_name               = nl2br((string)$response[0]['Drug_Name']); // Drug_Name

        if($response[0]['ReceptForm_Code'] == '1-МИ') {
			$drug_form_name = $_SESSION['region']['nick'] == 'saratov' ? $response[0]['DrugTorg_Name_mi1'] : $response[0]['Drug_Name_mi1'];
		}

		//6-5-4;
		$options = getOptions();
		$drug_form_length = $options['recepts']['print_format']==3?'6px':'9px';
		$drug_form_line_height = '1';
		if(strlen($drug_form_name) > 110)
			$drug_form_length = $options['recepts']['print_format']==3?'5px':'8px';
		if(strlen($drug_form_name) > 128)
		{
			$drug_form_length = $options['recepts']['print_format']==3?'5px':'7px';
			$drug_form_line_height = $options['recepts']['print_format']==3?'1':'0.9';//'0.9';
		}
		$OrgFarmacy_id        = $response[0]['OrgFarmacy_id']; // OrgFarmacy_id
        if(($_SESSION['region']['nick'] == 'ufa') && ($response[0]['ReceptForm_Code'] == '1-МИ')){ //Для Уфы для 1-МИ убираем печать информации о наличии лек средств в аптеке
            if($options['recepts']['print_format'] == 1)
                $farm_info = "";//"<br><br><br><br><br>";
            else
                $farm_info = "<br><br><br><br><br>";
        }
        else{
			if (!empty($OrgFarmacy_id) && $OrgFarmacy_id != 1) {
				$farm_info = "<div style='font-weight: bold; font-size: 12px;'>Наличие лекарственных препаратов:</div>
				<div>
					<div style='font-size: 9px; font-weight: bold;'>Аптека: {orgfarmacy_name}</div>
					<div style='font-size: 9px; font-weight: bold;'>Адрес: {orgfarmacy_howgo}</div>
					<div style='font-size: 9px; font-weight: bold;'>Телефон: {orgfarmacy_phone}</div>
				</div>";
			} else {
				$farm_info = "";
			}
        }
        $drugmnn_name            = $drug_form_name;
		$evn_recept_num          = (string)$response[0]['EvnRecept_Num']; // EvnRecept_Num
		$evn_recept_ser          = (string)$response[0]['EvnRecept_Ser']; // EvnRecept_Ser
		$evn_recept_set_date     = (string)$response[0]['EvnRecept_setDate']; // EvnRecept_setDate
		$evn_recept_set_day      = $response[0]['EvnRecept_setDay']; // EvnRecept_setDay
		$evn_recept_set_month    = $response[0]['EvnRecept_setMonth']; // EvnRecept_setMonth
		$evn_recept_set_year     = $response[0]['EvnRecept_setYear'] - 2000; // EvnRecept_setYear
		$evn_recept_signa        = (string)$response[0]['EvnRecept_Signa']; // EvnRecept_Signa
		$lpu_code                = $response[0]['Lpu_Code']; // Lpu_Code
        $lpu_name                = $response[0]['Lpu_Name'];
		$lpu_ogrn                = strlen((string)$response[0]['Lpu_Ogrn']) > 0 ? (string)$response[0]['Lpu_Ogrn'] : '&nbsp;'; // Lpu_Orgn
		$lpu_unit_set_code       = $response[0]['LpuUnitSet_Code']; // LpuUnitSet_Code
		$medpersonal_code        = str_pad($response[0]['MedPersonal_Code'], 6, '0', STR_PAD_LEFT); // MedPersonal_Code
		$medpersonal_fio         = (string)$response[0]['MedPersonal_Fio']; // MedPersonal_Fio
		$orgfarmacy_howgo        = $response[0]['OrgFarmacy_HowGo']; // OrgFarmacy_HowGo
		$orgfarmacy_name         = $response[0]['OrgFarmacy_Name']; // OrgFarmacy_Name
		$orgfarmacy_phone        = $response[0]['OrgFarmacy_Phone']; // OrgFarmacy_Phone
		$orgsmo_name_mi1         = (strlen($response[0]['OrgSmo_Name']) > 0 ? htmlspecialchars($response[0]['OrgSmo_Name']) : '&nbsp;');// для печати рецепта МИ-1 http://redmine.swan.perm.ru/issues/31345
		$orgsmo_name             = '&nbsp;';
		$person_address_array[0] = (string)$response[0]['Person_Address_1']; // Person_Address_1
		$person_address_array[1] = strlen((string)$response[0]['PersonCard_Code']) > 0 ? '&nbsp;' : (($_SESSION['region']['nick'] !='perm'&&$_SESSION['region']['nick']!='ufa') ? '&nbsp' : (string)$response[0]['Person_Address_2']);
		$person_birthday         = (string)$response[0]['Person_Birthday']; // Person_Birthday
		$person_card_code        = strlen((string)$response[0]['PersonCard_Code']) > 0 ? (string)$response[0]['PersonCard_Code'] : '&nbsp;'; // ($_SESSION['region']['nick']!='perm' ? '&nbsp;' : $person_address_array[0]);
		$person_fio              = (string)$response[0]['Person_Fio']; // Person_Fio
		$person_snils            = (string)$response[0]['Person_Snils']; // Person_Snils
		$privilege_type_code     = sprintf('%03d', strval($response[0]['PrivilegeType_Code'])); // PrivilegeType_Code
		$recept_discount_code    = $response[0]['ReceptDiscount_Code']; // ReceptDiscount_Code
		$recept_finance_code     = $response[0]['ReceptFinance_Code']; // ReceptFinance_Code
		$recept_type_code        = $response[0]['ReceptType_Code']; // ReceptType_Code
		$recept_valid_code       = $response[0]['ReceptValid_Code']; // ReceptValid_Code
		//echo $recept_valid_code;
		$polis_ser_num           = '';
        $polis_ser               = '';
        $polis_num               = '';
		$recept_valid_4          = '';
		$recept_valid_7          = '';
		$recept_valid_1          = '';
		$recept_valid_2          = '';
		$style_striked           = '1; text-decoration:underline;';

		if(($recept_finance_code == 2)&&($_SESSION['region']['nick'] == 'ufa'))
			$evn_recept_num = str_pad($evn_recept_num, 13, '0', STR_PAD_LEFT); //Дополняем нулями слева

		if ( strlen(trim($response[0]['Polis_Ser'])) > 0 ) {
			$polis_ser_num .= trim($response[0]['Polis_Ser']) . ' ';
            $polis_ser = trim($response[0]['Polis_Ser']);
		}

		$polis_ser_num .= trim($response[0]['Polis_Num']);
        $polis_num = $response[0]['Polis_Num'];

		$polis_ser_num = mb_substr($polis_ser_num, 0, 25);
		$polis_ser_num .= str_repeat(' ', 25 - mb_strlen($polis_ser_num));

        $polis_num = mb_substr($polis_num, 0, 8);
        $polis_num .= str_repeat(' ', 8 - mb_strlen($polis_num));
        $polis_ser = mb_substr($polis_ser, 0, 6);
        $polis_ser .= str_repeat(' ', 6 - mb_strlen($polis_ser));

		if ( preg_match('/^\d{8}$/', $drug_code) ) {
			for ( $i = 0; $i < mb_strlen($drug_code); $i++ ) {
				$drug_code_array[$i] = mb_substr($drug_code, $i, 1);
			}
		}

		if ( preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $evn_recept_set_date) ) {
			for ( $i = 0; $i < mb_strlen($evn_recept_set_date); $i++ ) {
				$evn_recept_set_date_array[$i] = mb_substr($evn_recept_set_date, $i, 1);
			}
		}

		if ( $lpu_unit_set_code > 0 ) {
			for ( $i = 4; $i >= 0; $i-- ) {
				$lpu_unit_set_array[$i] = $lpu_unit_set_code - floor($lpu_unit_set_code / 10) * 10;
				$lpu_unit_set_code = floor($lpu_unit_set_code / 10);
			}
		}
		if($data['session']['region']['nick']=='saratov'){ //https://redmine.swan.perm.ru/issues/27883
			$lpu_unit_set_array[0] = '';
			$lpu_unit_set_array[1] = '';
			$lpu_unit_set_array[2] = '';
			$lpu_unit_set_array[3] = '';
			$lpu_unit_set_array[4] = '';
			for($i=0;$i<5;$i++){
				if(isset($response[0]['Lpu_Ouz'][$i])){
					$lpu_unit_set_array[$i] = $response[0]['Lpu_Ouz'][$i];
				}
			}
		}

        for ( $i = 0; $i < mb_strlen($medpersonal_code); $i++ ) {
            $med_personal_code_array[$i] = mb_substr($medpersonal_code, $i, 1);
        }

		if ( preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $person_birthday) ) {
			for ( $i = 0; $i < mb_strlen($person_birthday); $i++ ) {
				$person_birthday_array[$i] = mb_substr($person_birthday, $i, 1);
			}
		}

		if ( preg_match('/^\d{11}$/', $person_snils) ) {
			$person_snils_temp = mb_substr($person_snils, 0, 3) . '-' . mb_substr($person_snils, 3, 3) . '-' . mb_substr($person_snils, 6, 3) . ' ' . mb_substr($person_snils, 9, 2);
			for ( $i = 0; $i < mb_strlen($person_snils_temp); $i++ ) {
				$person_snils_array[$i] = mb_substr($person_snils_temp, $i, 1);
			}
		}

		if ( preg_match('/^\d{3}$/', $privilege_type_code) ) {
			for ( $i = 0; $i < mb_strlen($privilege_type_code); $i++ ) {
				$privilege_type_code_array[$i] = mb_substr($privilege_type_code, $i, 1);
			}
		}

        for ($i=0; $i < mb_strlen($lpu_ogrn); $i++) {
            $lpu_ogrn_array[$i] = mb_substr($lpu_ogrn,$i,1);
        }

		for ( $i = 0; $i < mb_strlen($diag_code); $i++ ) {
			$noz_form_code_array[$i] = mb_substr($diag_code, $i, 1);
		}

		if ( ($recept_discount_code >= 1) && ($recept_discount_code <= 2) ) {
			$recept_discount_code = $recept_discount_code - 1;
			$recept_discount_array[$recept_discount_code] = '1px solid #000';
		}

		if ( ($recept_finance_code >= 1) && ($recept_finance_code <= 3) ) {
			$recept_finance_array[$recept_finance_code - 1] = '1px solid #000';
		}

		switch ( $recept_valid_code ) {
			case 4:
				$recept_valid_4 = $style_striked;
				//$recept_valid_code = 4;
			break;

			case 7:
			case 11:
				$recept_valid_7 = $style_striked;
				//$recept_valid_code = 7;
			break;
		    case 1:
			case 9:
				$recept_valid_1 = $style_striked;
				//$recept_valid_code = 1;
			break;
		    case 2:
			case 10:
				$recept_valid_2 = $style_striked;
				//$recept_valid_code = 2;
			break;
            /*
			case 3:
				$recept_valid = '14 дней';
				$recept_valid_code = 0;
			break;
            */
		}

		$max_i = (mb_strlen($polis_ser_num) <= 24 ? mb_strlen($polis_ser_num) : 24);
		for ( $i = 0; $i < $max_i; $i++ ) {
			if ( mb_substr($polis_ser_num, $i, 1) != ' ' ) {
				$polis_ser_num_array[$i] = mb_substr($polis_ser_num, $i, 1);
			}
		}

		if ( $drug_is_mnn == 1 ) {
			$drug_is_mnn = 0;
		}
		else if ( $drug_is_mnn == 0 ) {
			$drug_is_mnn = 1;
		}

		$person_state_array[0] = $person_address_array[0];
		$person_state_array[1] = $person_address_array[1];

		$this->load->model('Barcode_model');
		$resp_barcode = $this->Barcode_model->getBarcodeFields($data);
		$barcode_string = $this->Barcode_model->getBinaryString($resp_barcode);
		//$barcode_string = $this->Barcode_model->getBinaryString($response[0]);

		// echo $binary_string;
		$rec_date = date('Y-m-d',strtotime($evn_recept_set_date));
		if($rec_date >= '2016-07-30'){
			$str_amb_header = 'Номер медицинской карты пациента, получающего медицинскую помощь в амбулаторных условиях';
			$r_v_0 = '';
			$r_v_1 = '15 дней';
			$r_v_2 = '30 дней';
			$r_v_3 = '90 дней';

			$person_address_array[0] = '';
			$person_address_array[1] = '';
		}
		else if($rec_date >= '2016-01-01')
		{
			$str_amb_header = 'Номер медицинской карты пациента, получающего медицинскую помощь в амбулаторных условиях';
			$r_v_0 = '5 дней,';
			$r_v_1 = '15 дней';
			$r_v_2 = '30 дней';
			$r_v_3 = '90 дней';

			$person_address_array[0] = '';
			$person_address_array[1] = '';
		}
		else
		{
			$str_amb_header = '№ медицинской карты амбулаторного пациента (истории развития ребенка)';
			$r_v_0 = '5 дней,';
			$r_v_1 = '10 дней';
			$r_v_2 = '1 месяца';
			$r_v_3 = '3 месяцев';
		}
		/*if($_SESSION['region']['nick'] == 'ufa') //https://redmine.swan.perm.ru/issues/70751
		{
			$person_address_array[0] = '';
			$person_address_array[1] = '';
		}*/
		$drug_name = str_replace('+', ' + ', $drug_name);
		 if(in_array($_SESSION['region']['nick'],array('saratov','khak')))
			$drug_form_name = '';
		$parse_data = array(
			'str_amb_header' => $str_amb_header,
			'r_v_0' => $r_v_0,
			'r_v_1' => $r_v_1,
			'r_v_2' => $r_v_2,
			'r_v_3' => $r_v_3,
            'farm_info' => $farm_info,
			'address_string_1' => $person_address_array[0],
			'address_string_2' => $person_address_array[1],
			'ambul_card_num' => $person_card_code,
			'barcode_string' => urlencode($barcode_string),
            'drugmnn_name' => $drugmnn_name,
			'drug_dose' => $drug_dose_full,
			'drug_form' => $drug_form_name,
			'drug_form_length' => $drug_form_length,
			'drug_form_line_height' => $drug_form_line_height,
			'drug_kolvo' => $drug_dose_count . ' ' . $response[0]['Drug_Fas_Ed'],
			'drug_name' => $drug_name,
			'lpu_ogrn' => $lpu_ogrn,
			'lpu_stamp_1' => $lpu_unit_set_array[0],
			'lpu_stamp_2' => $lpu_unit_set_array[1],
			'lpu_stamp_3' => $lpu_unit_set_array[2],
			'lpu_stamp_4' => $lpu_unit_set_array[3],
			'lpu_stamp_5' => $lpu_unit_set_array[4],
			'medpersonal_code_1' => $med_personal_code_array[0],
			'medpersonal_code_2' => $med_personal_code_array[1],
			'medpersonal_code_3' => $med_personal_code_array[2],
			'medpersonal_code_4' => $med_personal_code_array[3],
			'medpersonal_code_5' => $med_personal_code_array[4],
			'medpersonal_code_6' => $med_personal_code_array[5],
			'medpersonal_fio' => $medpersonal_fio,
			'noz_form_code' => $diag_code,
			'orgfarmacy_howgo' => $orgfarmacy_howgo,
			'OrgFarmacy_id' => $OrgFarmacy_id,
			'orgfarmacy_name' => $orgfarmacy_name,
			'orgfarmacy_phone' => $orgfarmacy_phone,
			'orgsmo_name' => $orgsmo_name,
            'orgsmo_name_mi1' =>$orgsmo_name_mi1, // для печати рецепта МИ-1 http://redmine.swan.perm.ru/issues/31345
            'Lpu_Ouz' => $response[0]['Lpu_Ouz'],
			'person_birthday_1' => $person_birthday_array[0],
			'person_birthday_2' => $person_birthday_array[1],
			'person_birthday_3' => $person_birthday_array[3],
			'person_birthday_4' => $person_birthday_array[4],
			'person_birthday_5' => $person_birthday_array[6],
			'person_birthday_6' => $person_birthday_array[7],
			'person_birthday_7' => $person_birthday_array[8],
			'person_birthday_8' => $person_birthday_array[9],
			'person_fio' => $person_fio,
            'person_snils' => $person_snils,
			'person_snils_1' => $person_snils_array[0],
			'person_snils_2' => $person_snils_array[1],
			'person_snils_3' => $person_snils_array[2],
			'person_snils_4' => $person_snils_array[3],
			'person_snils_5' => $person_snils_array[4],
			'person_snils_6' => $person_snils_array[5],
			'person_snils_7' => $person_snils_array[6],
			'person_snils_8' => $person_snils_array[7],
			'person_snils_9' => $person_snils_array[8],
			'person_snils_10' => $person_snils_array[9],
			'person_snils_11' => $person_snils_array[10],
			'person_snils_12' => $person_snils_array[11],
			'person_snils_13' => $person_snils_array[12],
			'person_snils_14' => $person_snils_array[13],
            'polis_ser_num'   => $polis_ser_num,
			'polis_ser_num_1' => $polis_ser_num_array[0],
			'polis_ser_num_2' => $polis_ser_num_array[1],
			'polis_ser_num_3' => $polis_ser_num_array[2],
			'polis_ser_num_4' => $polis_ser_num_array[3],
			'polis_ser_num_5' => $polis_ser_num_array[4],
			'polis_ser_num_6' => $polis_ser_num_array[5],
			'polis_ser_num_7' => $polis_ser_num_array[6],
			'polis_ser_num_8' => $polis_ser_num_array[7],
			'polis_ser_num_9' => $polis_ser_num_array[8],
			'polis_ser_num_10' => $polis_ser_num_array[9],
			'polis_ser_num_11' => $polis_ser_num_array[10],
			'polis_ser_num_12' => $polis_ser_num_array[11],
			'polis_ser_num_13' => $polis_ser_num_array[12],
			'polis_ser_num_14' => $polis_ser_num_array[13],
			'polis_ser_num_15' => $polis_ser_num_array[14],
			'polis_ser_num_16' => $polis_ser_num_array[15],
			'polis_ser_num_17' => $polis_ser_num_array[16],
			'polis_ser_num_18' => $polis_ser_num_array[17],
			'polis_ser_num_19' => $polis_ser_num_array[18],
			'polis_ser_num_20' => $polis_ser_num_array[19],
			'polis_ser_num_21' => $polis_ser_num_array[20],
			'polis_ser_num_22' => $polis_ser_num_array[21],
			'polis_ser_num_23' => $polis_ser_num_array[22],
			'polis_ser_num_24' => $polis_ser_num_array[23],
			'polis_ser_num_25' => $polis_ser_num_array[24],
			'privilege_type_code_1' => $privilege_type_code_array[0],
			'privilege_type_code_2' => $privilege_type_code_array[1],
			'privilege_type_code_3' => $privilege_type_code_array[2],
            'lpu_name'   => $lpu_name,
            'lpu_ogrn_0' => $lpu_ogrn_array[0],
            'lpu_ogrn_1' => $lpu_ogrn_array[1],
            'lpu_ogrn_2' => $lpu_ogrn_array[2],
            'lpu_ogrn_3' => $lpu_ogrn_array[3],
            'lpu_ogrn_4' => $lpu_ogrn_array[4],
            'lpu_ogrn_5' => $lpu_ogrn_array[5],
            'lpu_ogrn_6' => $lpu_ogrn_array[6],
            'lpu_ogrn_7' => $lpu_ogrn_array[7],
            'lpu_ogrn_8' => $lpu_ogrn_array[8],
            'lpu_ogrn_9' => $lpu_ogrn_array[9],
            'lpu_ogrn_10' => $lpu_ogrn_array[10],
            'lpu_ogrn_11' => $lpu_ogrn_array[11],
            'lpu_ogrn_12' => $lpu_ogrn_array[12],
            'lpu_ogrn_13' => $lpu_ogrn_array[13],
            'lpu_ogrn_14' => $lpu_ogrn_array[14],
			'noz_form_code_1' => $noz_form_code_array[0],
			'noz_form_code_2' => $noz_form_code_array[1],
			'noz_form_code_3' => $noz_form_code_array[2],
			'noz_form_code_4' => $noz_form_code_array[3],
			'noz_form_code_5' => $noz_form_code_array[4],

			'recept_date' => $evn_recept_set_date,
			'recept_date_1' => $evn_recept_set_date_array[0],
			'recept_date_2' => $evn_recept_set_date_array[1],
			'recept_date_3' => $evn_recept_set_date_array[3],
			'recept_date_4' => $evn_recept_set_date_array[4],
			'recept_date_5' => $evn_recept_set_date_array[6],
			'recept_date_6' => $evn_recept_set_date_array[7],
			'recept_date_7' => $evn_recept_set_date_array[8],
			'recept_date_8' => $evn_recept_set_date_array[9],
			'recept_discount_1' => $recept_discount_array[0],
			'recept_discount_2' => $recept_discount_array[1],
            'recept_discount_mi1' => '1px solid #000',
			'recept_finance_1' => $recept_finance_array[0],
			'recept_finance_2' => $recept_finance_array[1],
			'recept_finance_3' => $recept_finance_array[2],
			'recept_num' => $evn_recept_num,
			'recept_ser' => $evn_recept_ser,
			'recept_template_title' => 'Печать рецепта ' . $evn_recept_ser . ' ' . $evn_recept_num,
			'recept_valid_4' => $recept_valid_4,
			'recept_valid_7' => $recept_valid_7,
			'recept_valid_1' => $recept_valid_1,
			'recept_valid_2' => $recept_valid_2,
			'signa' => $evn_recept_signa,

			'drug_code_1' => $drug_code_array[0],
			'drug_code_2' => $drug_code_array[1],
			'drug_code_3' => $drug_code_array[2],
			'drug_code_4' => $drug_code_array[3],
			'drug_code_5' => $drug_code_array[4],
			'drug_code_6' => $drug_code_array[5],
			'drug_code_7' => $drug_code_array[6],
			'drug_code_8' => $drug_code_array[7],
			'evn_recept_id' => $data['EvnRecept_id'],
			'person_state_1' => $person_state_array[0],
			'person_state_2' => $person_state_array[1],
            'polis_ser_1' => mb_substr($polis_ser, 0, 1),
            'polis_ser_2' => mb_substr($polis_ser, 1, 1),
            'polis_ser_3' => mb_substr($polis_ser, 2, 1),
            'polis_ser_4' => mb_substr($polis_ser, 3, 1),
            'polis_ser_5' => mb_substr($polis_ser, 4, 1),
            'polis_ser_6' => mb_substr($polis_ser, 5, 1),
            'polis_num_1' => mb_substr($polis_num, 0, 1),
            'polis_num_2' => mb_substr($polis_num, 1, 1),
            'polis_num_3' => mb_substr($polis_num, 2, 1),
            'polis_num_4' => mb_substr($polis_num, 3, 1),
            'polis_num_5' => mb_substr($polis_num, 4, 1),
            'polis_num_6' => mb_substr($polis_num, 5, 1),
            'polis_num_7' => mb_substr($polis_num, 6, 1),
            'polis_num_8' => mb_substr($polis_num, 7, 1),

		);

		// array_walk($data, 'htmlspecialchars');
		return $this->parser->parse($recept_template, $parse_data, $ReturnString);
	}


	/**
	*  Печать оборотной стороны рецепта
	*  Входящие данные: нет
	*  На выходе: форма для печати оборотной стороны рецепта
	*  Используется: форма редактирования рецепта
	*                форма поиска рецепта
	*/
	function printReceptDarkSide() {
		$this->load->helper('Options');
		$this->load->library('parser');

		// Получаем настройки
		$options = getOptions();
        if($options['recepts']['copies_count'] == 1)
           $recept_template = 'recept_dark_side_template_2_pages';
        else
        {
            switch ( $options['recepts']['print_format'] ) {
                case 1:
                    $recept_template = 'recept_dark_side_template_list_1';
                break;

                case 2:
                    $recept_template = 'recept_dark_side_template_list_2';
                break;

                case 3:
                    $recept_template = 'recept_dark_side_template_list_3';
                break;

                default:
                    echo "Необходимо задать формат печати в настройках рецепта";
                    return false;
                break;
            }
        }
		return $this->parser->parse($recept_template, array());
	}

	/**
	*  Получение списка рецептов для арма ЛЛО поликлиники
	*  Входящие данные: $_POST['begDate'],
	*                   $_POST['endDate']
	*					+ фильтры
	*  На выходе: JSON-строка
	*  Используется: форма арм "ЛЛО поликлиника"
	*/
	function loadReceptList() {
		$data = $this->ProcessInputData('loadReceptList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadReceptList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	
	/**
	*  Получение списка рецептов для потокового ввода
	*  Входящие данные: $_POST['begDate'],
	*                   $_POST['begTime']
	*  На выходе: JSON-строка
	*  Используется: форма потокового ввода рецептов
	*/
	function loadStreamReceptList() {
		$data = $this->ProcessInputData('loadStreamReceptList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadStreamReceptList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	
	/**
	*  Получение ссылки для печати рецепта из АРМ врача
	*  Входящие данные: $_POST['EvnRecept_id']
	*  На выходе: JSON-строка с HTML в элементе 'html'
	*  Используется: электронная медицинская карта
	*/
	function loadEvnReceptViewForm() {
		$data = $this->ProcessInputData('loadEvnReceptViewForm', true);
		if ($data === false) { return false; }
		
		//$str = $this->printRecept($data['EvnRecept_id'], TRUE);
		echo json_encode(array("success"=>true, "html" => "/?c=EvnRecept&m=printRecept&IsForLpu=1&EvnRecept_id=".$data['EvnRecept_id']));
		return true;
	}
	

	/**
	 *	Получение списка заболеваний пациента
	 *	Входящие данные: $_POST['Person_id']
	 *	На выходе: JSON-строка
	 *	Используется: форма редактирования рецепта
	 */
	function loadPersonRegisterList() {
		$data = $this->ProcessInputData('loadPersonRegisterList', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadPersonRegisterList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Сохранение признака печати рецепта
	 *  Входящие данные: $_POST
	 *  На выходе: JSON-строка
	 */
	function saveEvnReceptIsPrinted() {
		$data = $this->ProcessInputData('saveEvnReceptIsPrinted', true);
		if ($data) {
			$response = $this->dbmodel->saveEvnReceptIsPrinted($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение признака печати общего рецепта
	 *  Входящие данные: $_POST
	 *  На выходе: JSON-строка
	 */
	function saveEvnReceptGeneralIsPrinted() {
		$data = $this->ProcessInputData('saveEvnReceptGeneralIsPrinted', true);
		if ($data) {
			$response = $this->dbmodel->saveEvnReceptGeneralIsPrinted($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Сохранение рецепта
	 *	Входящие данные: ...
	 *	На выходе: JSON-строка
	 *	Используется: форма редактирования рецепта
	 */
	function saveEvnReceptRls() {
		$region_nick = $_SESSION['region']['nick'];

		$data = $this->ProcessInputData('saveEvnReceptRls', true);
		if ( $data === false ) { return false; }

		$this->load->helper('Options');
		$this->load->model("Options_model", "opmodel");
		$options = $this->opmodel->getOptionsAll($data);

		if ( !empty($data['EvnRecept_id']) ) {
			$this->ReturnError('Редактирование рецепта запрещено');
			return false;
		}

		if (empty($data['Drug_rlsid']) && empty($data['DrugComplexMnn_id'])) {
			$this->ReturnError('Должно быть указано наименование или торговое наимен-е ЛС');
			return false;
		}

		// Непонятно, зачем сейчас это нужно, все номера приходят с формы
		/*if ( $data['ReceptType_id'] != 1 && empty($data['EvnRecept_id']) ) {
			// Получаем новый рецепта для Саратова и Уфы
			$data['EvnRecept_Num'] = $this->getReceptNumber(true, false);
		}*/

		//проверки нумератора
		if (!in_array($region_nick, array('kz'))) {
			$numerator_err_msg = null;
			switch($data['ReceptType_id']) {
				case 1: //На бланке
					if ($options['recepts']['blank_form_creation_method'] == 2) { //Способ создания бланков льготного рецепта: из информационной системы с генерацией номеров
						$check_num_result = $this->dbmodel->checkNumInRezerv($data);
						if ($check_num_result === false) {
							$numerator_err_msg = 'Номер бланка должен попадать в один из введенных диапазонов резервирования';
						} else if (!empty($check_num_result['Error_Msg'])) {
							$numerator_err_msg = $check_num_result['Error_Msg'];
						}
					}
					break;
				case 2: //На листе
					if ($options['globals']['use_numerator_for_recept'] == 2) { //Использовать нумератор для рецептов «на листе»: Да
						$check_num_result = $this->dbmodel->checkNumInRezerv($data);
						if (!empty($check_num_result['Error_Msg'])) {
							$numerator_err_msg = $check_num_result['Error_Msg'];
						}
					}
					break;
			}
			if (!empty($numerator_err_msg)) {
				$this->ReturnError($numerator_err_msg);
				return false;
			}
		}

		$compare_result = swCompareDates($data['EvnRecept_setDate'], date('d.m.Y'));
		if ( -1 == $compare_result[0] ) {
			$this->ReturnError('Дата выписки рецепта не должна быть больше текущей даты');
			return false;
		}

		$compare_result = swCompareDates('01.01.1900', $data['EvnRecept_setDate']);
		if ( -1 == $compare_result[0] ) {
			$this->ReturnError('Дата выписки рецепта должна быть больше 01.01.1900');
			return false;
		}

		// Проверки
		if ( $options['recepts']['validate_start_date'] === true || $options['recepts']['validate_start_date'] == '1' ) {
			$check_recept_person_birthday = $this->dbmodel->checkReceptPersonBirthday($data);

			if ( $check_recept_person_birthday == -1 ) {
				$this->ReturnError('Ошибка при проверке даты выписки пациента');
				return false;
			}
			else if ( $check_recept_person_birthday == 0 ) {
				$this->ReturnError('Дата выписки рецепта меньше даты рождения пациента');
				return false;
			}
		}
        /*
		// Проверка врача, отделения, льготы и медикамента на дату выписки рецепта
		// Передаем Drug_id, EvnRecept_setDate, MedPersonal_id, LpuSection_id, Lpu_id
		// upd [2012-05-14 13:27]: добавил в checkEvnReceptValues проверку на код диагноза при выписке рецепта по 7 нозологиям
		// https://redmine.swan.perm.ru/issues/8253
		$response = $this->dbmodel->checkEvnReceptValues($data);

		if ( $response[0]['success'] == false ) {
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($response[0]);
			return false;
		}
        */
        /*
		// Проверяем наличие медикамента на остатках в выбранной аптеке
		// и получаем значение поля EvnRecept_IsNotOstat
		$data['EvnRecept_IsNotOstat'] = $this->dbmodel->checkDrugOstat($data);
		if ( $data['EvnRecept_IsNotOstat'] == 0 ) {
			$this->ReturnError('Ошибка при определении наличия остатков медикамента в выбранной аптеке');
			return false;
		}
        */

		$settings = $data['session']['setting']['server'];
		//Проверка остатков медикамента по заявке врача
		if ($data['isKardio'] != 1 && !empty($settings['select_drug_from_list']) && in_array($settings['select_drug_from_list'], array('request'/*, 'request_and_allocation'*/))) {
			$resp = $this->dbmodel->getDrugRequestRowOstat($data);
			if ( !empty($resp[0]['Error_Msg']) ) {
				$this->ReturnError($resp[0]['Error_Msg']);
				return false;
			}
			//print_r(array($resp[0]['DrugRequestRowOstat_Kolvo'], $data['EvnRecept_Kolvo']));exit;
			if ( $resp[0]['DrugRequestRowOstat_Kolvo'] - $data['EvnRecept_Kolvo'] < 0 ) {
				$this->ReturnError('Недостаточное количество медикаментов на остатках по заявке врача');
				return false;
			}
		}

		// Сохранение
		$response = $this->dbmodel->saveEvnReceptRls($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}

    /**
     * @return bool
     */
    function getReceptFormList(){
		$response = $this->dbmodel->getReceptFormList();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
    }
	
	/**
     * @return bool
     */
	function getReceptGenFormList(){
		$data = $this->ProcessInputData('getReceptGenFormList', false);
		$response = $this->dbmodel->getReceptGenFormList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
     * @return bool
     */
	function getReceptUrgencyList(){
		$response = $this->dbmodel->getReceptUrgencyList();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
    /**
     * Получим форму рецепта
     */
    function getReceptForm(){

        $data = $this->ProcessInputData('getReceptForm', true);
        if ($data) {
            $response = $this->dbmodel->getReceptForm($data);
			if (!empty($response[0]['ReceptForm_Code'])) {
				$this->ReturnData(array("success"=>true, "ReceptForm_id" => toUtf($response[0]['ReceptForm_id']), "ReceptForm_Code" => toUtf($response[0]['ReceptForm_Code']), "EvnRecept_setDate" => toUtf($response[0]['EvnRecept_setDate'])));
			} else {
				$this->ReturnData(array("success"=>true, "ReceptForm_id" => "", "ReceptForm_Code" => "", "EvnRecept_setDate" => toUtf($response[0]['EvnRecept_setDate'])));
			}
            return true;
        } else {
            return false;
        }
    }

	/**
     * Получим форму рецепта
     */
    function getReceptGeneralForm(){

        $data = $this->ProcessInputData('getReceptGeneralForm', true);
        if ($data) {
            $response = $this->dbmodel->getReceptGeneralForm($data);
			$this->ReturnData(array("success"=>true, "ReceptForm_Code" => toUtf($response[0]['ReceptForm_Code']), "ReceptForm_id" => $response[0]['ReceptForm_id'], "EvnReceptGeneral_setDate" => $response[0]['EvnReceptGeneral_setDate']));
            return true;
        } else {
            return false;
        }
    }
	
	/**
	 *  Проверка срока годности рецепта на определенную дату
	 */
	function checkReceptValidByDate() {
		$data = $this->ProcessInputData('checkReceptValidByDate', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkReceptValidByDate($data);
		$this->ReturnData($response);
		return true;
	}

    /**
     * Поиск рецепта по данным со штрих-кода
     */
    function SearchReceptFromBarcode(){
        $data = $this->ProcessInputData('SearchReceptFromBarcode', false);
        if ($data === false)
        {
            return false;
        }
        $response = $this->dbmodel->SearchReceptFromBarcode($data);
        $this->ReturnData($response);
        return true;
    }
	
	        /**
	 * Получить список рецептов
	 */
	function getEvnReceptList4Provider() {
           
		$data = $this->ProcessInputData('getEvnReceptList4Provider', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getEvnReceptList4Provider($data, false, false);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
        
	}

	/**
	* Получить cписок ЛС, заявленных в рамках ЛЛО
	*/
	function loadPersonDrugRequestPanel() {
		$data = $this->ProcessInputData('loadPersonDrugRequestPanel', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadPersonDrugRequestPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Снять рецепт с обслуживания
	 */
	function pullOffServiceRecept() {
           
		$data = $this->ProcessInputData('pullOffServiceRecept', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->pullOffServiceRecept($data, false, false);
		$this->ProcessModelSave($response, true, true)->ReturnData();
        
	}

	/**
	 * Получение даты и причины снятия с обслуживания
	 */
	function getReceptOutDateAndCause() {
           
		$data = $this->ProcessInputData('getReceptOutDateAndCause', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getReceptOutDateAndCause($data, false, false);
		$this->ProcessModelList($response, true, true)->ReturnData();
        
	}

	/**
	 * Проверка статуса акта снятия с обслуживания
	 */
	function checkOutDocumentStatus() {
           
		$data = $this->ProcessInputData('checkOutDocumentStatus', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->checkOutDocumentStatus($data, false, false);
		$this->ProcessModelList($response, true, true)->ReturnData();
        
	}

	/**
	 * Удаление данных о снятии рецепта с обслуживания
	 */
	function deletePullOfServiceRecord() {
           
		$data = $this->ProcessInputData('deletePullOfServiceRecord', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->deletePullOfServiceRecord($data, false, false);
		$this->ProcessModelSave($response, true, true)->ReturnData();
        
	}

	/**
	 * Удаление данных об отказе по рецепту
	 */
	function deleteReceptWrongRecord() {
           
		$data = $this->ProcessInputData('deleteReceptWrongRecord', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->deleteReceptWrongRecord($data, false, false);
		$this->ProcessModelSave($response, true, true)->ReturnData();
        
	}
	
	/**
	*	Получение медикаментов из строки лекарственного лечения
	*/
	function getEvnCourseTreatDrugDetail() {
		$data = $this->ProcessInputData('getEvnCourseTreatDrugDetail',true);
		if($data === false)
			return false;
		$response = $this->dbmodel->getEvnCourseTreatDrugDetail($data, false, false);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*	Проверка лекарства на вхождение в группу сильнодействующих
	*/
	function checkDrugByLinkIsStrong() {
		$data = $this->ProcessInputData('checkDrugByLinkIsStrong',true);
		if($data === false)
			return false;
		$response = $this->dbmodel->checkDrugByLinkIsStrong($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	*	Получение дефолтных данных и данных по медикаменту для добавления общего рецепта
	*/
	function getReceptGeneralAddDetails() {
		$data = $this->ProcessInputData('getEvnCourseTreatDrugDetail',true);
		if($data === false)
			return false;
		$response = $this->dbmodel->getReceptGeneralAddDetails($data, false, false);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*	Сохранение общего рецепта
	*/
	function saveEvnReceptGeneral()
	{	
		$data = $this->ProcessInputData('saveEvnReceptGeneral', true);
		if ($data === false) {
			return false;
		}
		$data['EvnRecept_Ser'] = $data['EvnReceptGeneral_Ser'];
		$data['EvnRecept_Num'] = $data['EvnReceptGeneral_Num'];
		$this->load->model("Options_model", "opmodel");
		$glob_options = $this->opmodel->getOptionsGlobals($data);
		if ($data['ReceptType_id'] == 1 && $glob_options['globals']['use_numerator_for_recept'] == 2) {
			$data['EvnRecept_Num'] = $data['EvnReceptGeneral_Num'];
			$data['isGeneral'] = 1;
			$check_num_reserved = $this->dbmodel->checkNumInRezerv($data);
			if (!$check_num_reserved) {
				$this->ReturnError('Номер бланка должен попадать в один из введенных диапазонов резервирования');
				return false;
			}
		}
		
		//Проверим номер на уникальность
		//$ReceptSerNum_Exists = $this->dbmodel->checkReceptSerNum
		$check_recept_ser_num = $this->dbmodel->checkReceptGeneralSerNum($data);
		if ( $check_recept_ser_num == -1 ) {
			$response = array('success' => false, 'Error_Msg' => 'Ошибка при проверке уникальности серии и номера рецепта');
			array_walk($response, 'ConvertFromWin1251ToUTF8');
			echo json_encode($response);
			return true;
		}
		else if ( $check_recept_ser_num > 0 ) {
			$response = array('success' => false, 'Error_Msg' => 'Рецепт с такими серией и номером уже был выписан ранее');
			array_walk($response, 'ConvertFromWin1251ToUTF8');
			echo json_encode($response);
			return true;
		}
		
		//var_dump($data);die;
		$response = $this->dbmodel->saveEvnReceptGeneral($data, false, false);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
	
	/**
	*  Удаление медикамента из рецепта
	*/
	function deleteEvnReceptGeneralDrugLink() {
		$data = $this->ProcessInputData('deleteEvnReceptGeneralDrugLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnReceptGeneralDrugLink($data);
		$this->ProcessModelSave($response, true, 'При удалении медикамента из рецепта возникли ошибки')->ReturnData();
		return true;
	}

	/**
	*	Поиск рецептов для АРМа провизора общего отдела
	*/
	function searchEvnReceptGeneralList() {
		$data = $this->ProcessInputData('searchEvnReceptGeneralList', true);
		//var_dump($data);die;
		if ($data === false) 
			return false;
		$response = $this->dbmodel->searchEvnReceptGeneralList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	* provideEvnReceptGeneralDrugLink
	*/
	function provideEvnReceptGeneralDrugLink()
	{
		$data = $this->ProcessInputData('provideEvnReceptGeneralDrugLink', true);
			if ($data === false) 
		return false;
		$response = $this->dbmodel->provideEvnReceptGeneralDrugLink($data);
		$this->ProcessModelSave($response, true, 'При обеспечении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	* undo_provideEvnReceptGeneralDrugLink
	*/
	function undo_provideEvnReceptGeneralDrugLink()
	{
		$data = $this->ProcessInputData('undo_provideEvnReceptGeneralDrugLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->undo_provideEvnReceptGeneralDrugLink($data);
		$this->ProcessModelSave($response, true, 'При отмене обеспечении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *	Возвращает данные о видимости раздела рецептов по программе ДЛО Кардио
	 */
	function getEvnReceptKardioVisibleData() {
		$data = $this->ProcessInputData('getEvnReceptKardioVisibleData', false);
		if ($data) {
			$response = $this->dbmodel->getEvnReceptKardioVisibleData($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка рецептов для ЭМК
	 */
	function loadEvnReceptPanel()
	{
		$data = $this->ProcessInputData('loadEvnReceptPanel', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnReceptPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка рецептов пациента для ЭМК
	 */
	function loadPersonEvnReceptPanel()
	{
		$data = $this->ProcessInputData('loadPersonEvnReceptPanel', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonEvnReceptPanel($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Автоматическое включение в рецепт из новой ЭМК
	 */
	function saveAddingDrugToReceptGeneral(){
		$data = $this->ProcessInputData('saveAddingDrugToReceptGeneral',true);
		if($data === false)
			return false;
		$Error_Msg = false;
		$i = '';
		$resAddingDrug = $this->dbmodel->getReceptGeneralAddDetails($data, false, false);
		$resAddingDrug = $resAddingDrug[1];
		$resRecept = $this->dbmodel->loadEvnReceptGeneralEditForm($data);

		if(!empty($resRecept[0]['EvnReceptGeneralDrugLink_id2']))
			$Error_Msg = 'В рецепте не может быть более 3 медикаментов';
		elseif(!empty($resRecept[0]['EvnReceptGeneralDrugLink_id1']))
			$i = 2;
		elseif(!empty($resRecept[0]['EvnReceptGeneralDrugLink_id0']))
			$i = 1;
		else
			$Error_Msg = 'Ошибка загрузки данных';

		if($Error_Msg){
			$response = array('success' => false, 'Error_Msg' => $Error_Msg);
			array_walk($response, 'ConvertFromWin1251ToUTF8');
			echo json_encode($response);
		} else {
			$resRecept[0]['EvnCourseTreatDrug_id'] = $resAddingDrug['EvnCourseTreatDrug_id'];
			$resRecept[0]['EvnReceptGeneralDrugLink_id'.$i] = null;
			$resRecept[0]['Drug_Kolvo_Pack'.$i] = $resAddingDrug['Drug_Kolvo_Pack'];
			$resRecept[0]['Drug_Fas'.$i] = $resAddingDrug['Drug_Fas_'];
			$resRecept[0]['Drug_Signa'.$i] = $resAddingDrug['Drug_Signa'];
			$resRecept[0]['Drug_Name'.$i] = $resAddingDrug['Drug_Name'];
			$resRecept[0]['EvnReceptGeneral_setDate'] = date_format(new DateTime($resRecept[0]['EvnReceptGeneral_setDate']), 'Y-m-d');
			$resRecept[0]['EvnRecept_Ser'] = $resRecept[0]['EvnReceptGeneral_Ser'];
			$resRecept[0]['EvnRecept_Num'] = $resRecept[0]['EvnReceptGeneral_Num'];


			$response = $this->dbmodel->saveEvnReceptGeneral(array_merge($data, $resRecept[0]), false, false);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
		return true;
	}

	/**
	 *	Возвращает данные о видимости раздела рецептов по программе ДЛО Кардио
	 */
	function getLastPersonPrivilegeModerationData() {
		$data = $this->ProcessInputData('getLastPersonPrivilegeModerationData', false);
		if ($data) {
			$response = $this->dbmodel->getLastPersonPrivilegeModerationData($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Возвращает данные об адресе и документе пациента
	 */
	function getPersonAddressAndDocData() {
		$data = $this->ProcessInputData('getPersonAddressAndDocData', false);
		if ($data) {
			$response = $this->dbmodel->getPersonAddressAndDocData($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка для комбобокса
	 */
	function loadPersonAmbulatCardCombo() {
		$data = $this->ProcessInputData('loadPersonAmbulatCardCombo',false);
		if ($data) {
			$response = $this->dbmodel->loadPersonAmbulatCardCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получаем максимальное количество упаковок лекарственного препарата на 1 месяц
	 */
	function getDosKurs() {
		$data = $this->ProcessInputData('getDosKurs',false);
		if ($data) {
			$response = $this->dbmodel->getDosKurs($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Проверка, является ли вещество сильнодействующим или наркотическим
	 */
	function isNarcoOrStrongDrug()
	{
		$data = $this->ProcessInputData('isNarcoOrStrongDrug', false);
		if ($data) {
			$response = $this->dbmodel->isNarcoOrStrongDrug($data);
			if ($response[0]['NarcoOrStrongDrugCount'] > 0) {
				$val = array('isNarcoOrStrongDrug' => true);
			} else {
				$val = array('isNarcoOrStrongDrug' => false);
			}
			$this->ReturnData($val);
		}
	}
}