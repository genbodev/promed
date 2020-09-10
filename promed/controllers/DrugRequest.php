<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DrugRequest - контроллер для работы с заявкой медикаментов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Andrew Markoff 
 * @version      27.09.2009
 */

/**
 * @property DrugRequest_model $drmodel
 */
class DrugRequest extends swController
{
	public $options = array();
	protected $inputRules = array(
		'checkDrugRequestLimitExceed' => array(
			array(
				'field' => 'PersonRegisterType_id',
				'label' => 'Тип регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период заявки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getDrugRequestPersonGrid' => array(
			array(
				'field' => 'DrugRequestPerson_id',
				'label' => 'Запись о пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
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
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegisterType_id',
				'label' => 'Тип регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 50,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveDrugRequestPerson' => array(
			array(
				'field' => 'DrugRequestPerson_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
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
				'field' => 'PersonRegisterType_id',
				'label' => 'Тип регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveDrugRequestRow' => array(
			array(
				'field' => 'DrugRequestRow_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequest_id',
				'label' => 'Заявка',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestType_id',
				'label' => 'Тип',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Пациент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugProtoMnn_id',
				'label' => 'Медикамент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnn_id',
				'label' => 'Медикамент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TRADENAMES_id',
				'label' => 'Торговое наименование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestRow_Kolvo',
				'label' => 'Количество',
				'rules' => 'required|numeric|no_zero',
				'type' => 'float'
			),
			array(
				'field' => 'DrugRequestRow_DoseOnce',
				'label' => 'Разовая доза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugRequestRow_DoseDay',
				'label' => 'Дневная доза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugRequestRow_DoseCource',
				'label' => 'Курсовая доза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Okei_oid',
				'label' => 'Единицы измерения разовой дозы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'IsDrug',
				'label' => 'Выбран медикамент',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Merge',
				'label' => 'Признак обьеденения строк',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveDrugRequest' => array(
			array(
				'field' => 'DrugRequest_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestStatus_id',
				'label' => 'Статус',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequest_Name',
				'label' => 'Наименование заявки',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequest_YoungChildCount',
				'label' => 'Количество детей до 3 лет',
				'rules' => 'numeric',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegisterType_id',
				'label' => 'Тип регистра',
				'rules' => '',
				'type' => 'id'
			)			
		),
		'deleteDrugRequestPerson' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteDrugRequest' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteDrugRequestRow' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setDrugRequestLpuClose' =>  array(
			array(
				'field' => 'DrugRequestTotalStatus_IsClose',
				'label' => 'Признак закрытия',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveDrugRequestLpu' =>  array(
			array(
				'field' => 'DrugRequestTotalStatus_IsClose',
				'label' => 'Признак закрытия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestTotalStatus_FedLgotCount',
				'label' => 'Кол-во федеральных льготников, прикрепленных к ЛПУ',
				'rules' => 'is_numeric|is_natural_no_zero|max_length[6]',
				'type' => 'int'
			),
			array(
				'field' => 'DrugRequestTotalStatus_RegLgotCount',
				'label' => 'Кол-во региональных льготников, прикрепленных к ЛПУ',
				'rules' => 'is_numeric|is_natural_no_zero|max_length[6]',
				'type' => 'int'
			)
		),
		'setDrugRequestLpuUt' =>  array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestStatus_id',
				'label' => 'Статус',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setDrugRequestLpuReallocated' =>  array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'reallocated',
				'label' => 'Признак перераспределения',
				'rules' => '',
				'type' => 'int'
			)
		),
		'checkUniAllLpuDrugRequestRow' =>  array(
			array(
				'field' => 'DrugProtoMnn_id',
				'label' => 'Медикамент',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRequestRow_id',
				'label' => 'Позиция заявки',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadDrugProtoMnnList' =>  array(
			array(
				'field' => 'DrugProtoMnn_id',
				'label' => 'МНН медикамента по протоколу',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugMnn_id',
				'label' => 'МНН медикамента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ReceptFinance_id',
				'label' => 'Финансирование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugProtoMnnType_id',
				'label' => 'Тип МНН медикамента по протоколу',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugProtoMnn_Name',
				'label' => 'Название МНН медикамента по протоколу',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'IsDrug',
				'label' => 'Медикамент?',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Строка запроса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ignoreOstat',
				'label' => 'ignoreOstat',
				'rules' => '',
				'type'  => 'int',
				'default' => '0'
			)
		),
		'getDrugRequestLast' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			)
		),
        'getDrugRequestGridSum' => array(
           array(
                'field' => 'MedPersonal_id',
                'label' => 'Врач',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'Lpu_id',
                'label' => 'ЛПУ',
                'rules' => '',
                'type'  => 'string'
            ),
            array(
                'field' => 'DrugRequestType_id',
                'label' => 'Тип',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'DrugRequestPeriod_id',
                'label' => 'Период',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'DrugRequestStatus_id',
                'label' => 'Статус',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'LpuUnit_id',
                'label' => 'Группа отделений',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'LpuSection_id',
                'label' => 'Отделение ЛПУ',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'DrugRequest_id',
                'label' => 'Идентификатор',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'PersonRegisterType_id',
                'label' => 'Тип регистра',
                'rules' => '',
                'type'  => 'id'
			),
			array(
				'field' => 'DrugRequestKind_id',
				'label' => 'Тип заявки',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'mode',
				'label' => 'Режим получения информации',
				'rules' => '',
				'type'  => 'string'
			)
        ),
        'getDrugRequestLpuClose' => array(
            array(
                'field' => 'Lpu_id',
                'label' => 'ЛПУ',
                'rules' => '',
                'type'  => 'string'
            ),
            array(
                'field' => 'DrugRequestPeriod_id',
                'label' => 'Период',
                'rules' => '',
                'type'  => 'id'
            )
        ),
        'DeleteObject' => array(
            array(
                'field' => 'object',
                'label' => 'object',
                'rules' => '',
                'type'  => 'string'
            ),
            array(
                'field' => 'id',
                'label' => 'Идентификатор',
                'rules' => 'required',
                'type'  => 'id'
            )
        ),
        'getDrugRequestRowGrid' => array(
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор человека',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'DrugRequest_id',
                'label' => 'Идентификатор заявки',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'MedPersonal_id',
                'label' => 'Врач',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'DrugRequestType_id',
                'label' => 'Тип заявки',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'PersonRegisterType_id',
                'label' => 'Тип регистра',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'DrugRequestPeriod_id',
                'label' => 'Период',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'Server_id',
                'label' => 'Идентификатор сервера',
                'rules' => '',
                'type'  => 'id'
            )
        ),
        'getDrugRequestPrintParams' => array(
            array(
                'field' => 'DrugRequestPeriod_id',
                'label' => 'Период заявки',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'DrugRequestRow_actDate',
                'label' => 'Дата актуальности заявки',
                'rules' => '',
                'type'  => 'date'
            ),
            array(
                'field' => 'DrugRequestType_id',
                'label' => 'Тип заявки',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'FilterLpu_id',
                'label' => 'ЛПУ для минздрава',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'Lpu_id',
                'label' => 'ЛПУ',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'LpuSection_id',
                'label' => 'Отделение ЛПУ',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'LpuUnit_id',
                'label' => 'Отделение ЛПУ',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'MedPersonal_id',
                'label' => 'Врач',
                'rules' => '',
                'type'  => 'id'
            ),
            array(
                'field' => 'PrintType_id',
                'label' => 'Вариант печати',
                'rules' => '',
                'type'  => 'id'
            )
        ),
		'loadList' => array(
			array(
				'field' => 'Year',
				'label' => 'Год',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Период заявки',
				'rules' => '',
				'type' => 'int'
			)
		),
		'saveDrugRequestPeriod' => array(
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'DrugRequestPeriod_begDate',
				'label' => 'Дата начала периода заявки',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'DrugRequestPeriod_endDate',
				'label' => 'Дата завершения периода заявки',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'DrugRequestPeriod_Name',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DrugRequestPlanPeriodJSON',
				'label' => 'Планово-отчетные периоды',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadDrugRequestPeriod' => array(
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'идентификатор',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'loadDrugRequestPeriodList' => array(
			array(
				'field' => 'DrugRequestPeriod_id',
				'label' => 'Идентификатор рабочего периода',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteDrugRequestPeriod' => array(
			array(
				'field' => 'id',
				'label' => 'идентификатор',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'getLpuRegionCountByDrugRequestId' => array(
			array(
				'field' => 'DrugRequest_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'createDrugRequestCopy' => array(
			array(
				'field' => 'DrugRequest_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SourceDrugRequest_id',
				'label' => 'Идентификатор оригинальной заявки',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'createDrugRequestPersonList' => array(
			array(
				'field' => 'DrugRequest_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'createDrugRequestDrugCopy' => array(
			array(
				'field' => 'DrugRequest_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SourceDrugRequest_id',
				'label' => 'Идентификатор оригинальной заявки',
				'rules' => 'required',
				'type' => 'int'
			)
		),
        'moveDrugRequestRow' => array(
            array('field' => 'DrugRequestRow_id', 'label' => 'Идентификатор строки заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugRequestRow_Kolvo', 'label' => 'Количество', 'rules' => '', 'type' => 'float')
        ),
        'excludeDrugRequestPerson' => array(
            array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'DrugRequestPerson_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id')
        ),
        'loadReservedDrugRequestRowCombo' => array(
            array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
            array('field' => 'DrugRequestType_id', 'label' => 'Идентификатор типа финансирования заявки', 'rules' => '', 'type' => 'id')
        )
	);

	/**
	 * Функция
	 */
	function __construct() {
		parent::__construct();
        $this->load->database();
		if (isset($_REQUEST['DrugRequestPeriod_id']))
		{
			if ($_REQUEST['DrugRequestPeriod_id']==12010)
			{
				$this->options['normativ_fed_lgot'] = 400;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 75;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==22010)
			{
				$this->options['normativ_fed_lgot'] = 560;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 125;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==32010)
			{
				$this->options['normativ_fed_lgot'] = 590;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 190;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==42010)
			{
				$this->options['normativ_fed_lgot'] = 570;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 190;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==12011)
			{
				$this->options['normativ_fed_lgot'] = 600;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 100;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==22011)
			{
				$this->options['normativ_fed_lgot'] = 590;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 110;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==32011)
			{
				$this->options['normativ_fed_lgot'] = 590;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 130;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==42011)
			{
				$this->options['normativ_fed_lgot'] = 590;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 130;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==12012)
			{
				$this->options['normativ_fed_lgot'] = 630;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 140;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==22012)
			{
				$this->options['normativ_fed_lgot'] = 630;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 140;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==52012)
			{
				$this->options['normativ_fed_lgot'] = 630;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 140;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==32012)
			{
				$this->options['normativ_fed_lgot'] = 800;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 180;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==42012)
			{
				$this->options['normativ_fed_lgot'] = 800;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 180;
				$this->options['koef_reg_lgot'] = 1;
			}
			if ($_REQUEST['DrugRequestPeriod_id']==12013)
			{
				$this->options['normativ_fed_lgot'] = 700;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 220;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['DrugRequestPeriod_id'], array(22013, 32013, 42013, 12014, 22014, 32014, 42014)))
			{
				$this->options['normativ_fed_lgot'] = 650;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 250;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['DrugRequestPeriod_id'], array(42014)))
			{
				$this->options['normativ_fed_lgot'] = 380;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 50;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['DrugRequestPeriod_id'], array(12015)))
			{
				$this->options['normativ_fed_lgot'] = 390;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 70;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['DrugRequestPeriod_id'], array(22015, 32015, 42015)))
			{
				$this->options['normativ_fed_lgot'] = 390;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 75;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['DrugRequestPeriod_id'], array(62036)))
			{
				$this->options['normativ_fed_lgot'] = 310;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 90;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['DrugRequestPeriod_id'], array(62039)))
			{
				$this->options['normativ_fed_lgot'] = 408;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 130;
				$this->options['koef_reg_lgot'] = 1;
			}
			if (in_array($_REQUEST['DrugRequestPeriod_id'], array(62157)))
			{
				$this->options['normativ_fed_lgot'] = 425;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 138;
				$this->options['koef_reg_lgot'] = 1;
			}
            if (in_array($_REQUEST['DrugRequestPeriod_id'], array(62161,62190)))
            {
                $this->options['normativ_fed_lgot'] = 573;
                $this->options['koef_fed_lgot'] = 1;
                $this->options['normativ_reg_lgot'] = 185;
                $this->options['koef_reg_lgot'] = 1;
            }
			if (in_array($_REQUEST['DrugRequestPeriod_id'], [62201]))
			{
				$this->options['normativ_fed_lgot'] = 649;
				$this->options['koef_fed_lgot'] = 1;
				$this->options['normativ_reg_lgot'] = 210;
				$this->options['koef_reg_lgot'] = 1;
			}
		}
	}

    /**
	 * Получение методов
	 */
	function index() {
		if (!isset($_SESSION['login']))
		{
			// тут перекидываем на форму логина
		}
		elseif ( !isset($_REQUEST['method']) )
			header("Location: /?c=promed");
		
		// Временно закрыть доступ
		/*
		$val = array('Error_Code' => 1, 'Error_Msg' => 'Доступ к заявке временно закрыт!');
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return false;
		*/
		if (isset($_REQUEST['method']))
		{
			switch ($_REQUEST['method'])
			{
				case 'checkDrugRequestLimitExceed':
					$this->checkDrugRequestLimitExceed();
				break;
				case 'getPersonGrid':
					$this->getDrugRequestPersonGrid();
				break;
				case 'saveDrugRequestPerson': case 'saveDrugRequestRow': case 'saveDrugRequest': case 'setDrugRequestLpuClose': case 'setDrugRequestLpuUt': case 'saveDrugRequestLpu': case 'setDrugRequestLpuReallocated':
					$this->SaveObject($_REQUEST['method']);
				break;
				case 'deleteDrugRequestPerson': case 'deleteDrugRequestRow': case 'deleteDrugRequest': case 'deleteDrugRequestRow':
					$this->DeleteObject($_REQUEST['method']);
				break;
				case 'getDrugRequestRow':
					$this->getDrugRequestRowGrid();
				break;
				case 'getDrugRequest':
					$this->getDrugRequestGrid();
				break;
				case 'getDrugRequestSum':
					$this->getDrugRequestGridSum();
				break;
				case 'getDrugRequestLpuClose':
					$this->getDrugRequestLpuClose();
				break;
				case 'getDrugRequestLpuUt':
					$this->getDrugRequestLpuUt();
				break;
				case 'getDrugRequestLpuReallocated':
					$this->getDrugRequestLpuReallocated();
				break;
				case 'getDrugRequestLast':
					$this->getDrugRequestLast();
				break;
				case 'loadDrugCombo':
					$this->loadDrugProtoMnnList();
				break;
                case 'loadMnnCombo':
                    $this->loadDrugMnnList();
                break;
				case 'printDrugRequest':
					$this->printDrugRequest();
				default:
					die;
			}
			return false;
		}
		$this->load->helper('Main');
		$this->load->view("index");
	}

    /**
	 * Заявка на последний период, из имеющихся в системе, или последняя имеющаяся заявка.
	 */
	function getDrugRequestLast() {
		$data = $this->ProcessInputData('getDrugRequestLast', true);
		if ($data)
		{
			$this->load->model('DrugRequest_model', 'drmodel');
			$response = $this->drmodel->getDrugRequestLast($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

    /**
	 * Проверка превышения лимита
	 */
	function checkDrugRequestLimitExceed() {

		$this->load->model("DrugRequest_model", "drmodel");

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('checkDrugRequestLimitExceed', true);
		
		if (!$data) {
			return false;
		}
		
		$response = $this->drmodel->checkDrugRequestLimitExceed($data, $this->options);

		if ( (is_array($response)) && (count($response) > 0) ) {
			$val = $response;
		}
		else {
			$val = array('Error_Msg' => 'Ошибка при проверке лимитов по заявке', 'FedLgotExceed_Sum' => 0, 'RegLgotExceed_Sum' => 0);
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}


    /**
     * Список пациентов
     */
	function getDrugRequestPersonGrid()
	{
		$this->load->model('DrugRequest_model', 'dbmodel');

		$data = $this->ProcessInputData('getDrugRequestPersonGrid', true);
		if ($data) {
			$response = $this->dbmodel->DrugRequestPersonGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Получает список медикаментов по заявке (Персонифицированная заявка)
     */
	function getDrugRequestRowGrid()
	{
		$this->load->model("DrugRequest_model", "drmodel");

        $data = $this->ProcessInputData('getDrugRequestRowGrid',true);
        if ($data === false) {return false;}

		$val = array();
		$getlist = $this->drmodel->DrugRequestRowGrid($data);
		if ( $getlist != false && count($getlist) > 0 )
		{
			foreach ($getlist as $rows)
			{
                array_walk($rows, 'ConvertFromWin1251ToUTF8');
                $val[] = $rows;
			}
			$this->ReturnData($val);
		}
		else
			$this->ReturnData($val);
	}

	/**
	 * Получение количества региональных и федеральных льготников
	 */
	function getDrugRequestLpuClose()
	{
		$this->load->model("DrugRequest_model", "drmodel");

        $data = $this->ProcessInputData('getDrugRequestLpuClose',true);
        if ($data === false) {return false;}

		$data['session']=$_SESSION;
		
		$val = array();
		$getlist = $this->drmodel->getDrugRequestLpuClose($data);
		if ( $getlist != false && count($getlist) > 0 )
		{
            $val = array($getlist[0]);
			$this->ReturnData($val[0]);
		}
		else
			$this->ReturnData($val);
	}

	/**
	 * Получение утвержденных заявок
	 */
	function getDrugRequestLpuUt()
	{
		$this->load->model("DrugRequest_model", "drmodel");
		$data = $this->ProcessInputData('getDrugRequestLpuClose',true);
        if ($data === false) {return false;}
		$data['session'] = $_SESSION;

		$val = array();
		$getlist = $this->drmodel->getDrugRequestLpuUt($data);
		if ( $getlist != false && count($getlist) > 0 )
		{
			$val = array(
				'DrugRequestStatus_id'=>$getlist[0]['DrugRequestStatus_id']
			);
			$this->ReturnData($val);
		}
		else
			$this->ReturnData($val);
	}

	/**
	 * Получение перераспредляемых заявок
	 */
	function getDrugRequestLpuReallocated() {
		$this->load->model("DrugRequest_model", "drmodel");
		$data = $this->ProcessInputData('getDrugRequestLpuClose',true);
        if ($data === false) {return false;}
		$data['session'] = $_SESSION;

		$val = array();
		$getlist = $this->drmodel->getDrugRequestLpuReallocated($data);
		if ($getlist != false && count($getlist) > 0) {
			$val = array(
				'DrugRequestStatus_id'=>$getlist[0]['DrugRequestStatus_id']
			);
			$this->ReturnData($val);
		} else {
            $this->ReturnData($val);
        }
	}

    /**
	 * Вывод заявок на лекарственные средства по установленным фильтрам
	 */
	function getDrugRequestGridSum()
	{
		$this->load->model("DrugRequest_model", "drmodel");

        $data = $this->ProcessInputData('getDrugRequestGridSum',true);
        if ($data === false) {return false;}
        $data['session'] = $_SESSION;
		
		$val = array();

		$getlist = $this->drmodel->DrugRequestGridSum($data, $this->options);
		if ( $getlist != false && count($getlist) > 0 )
		{
			foreach ($getlist as $rows)
			{
                array_walk($rows, 'ConvertFromWin1251ToUTF8');
                $val[] = $rows;
			}
			$this->ReturnData($val);
		}
		else
			$this->ReturnData($val);
	}

	/**
	 * Информация о заявке
	 */
	function getDrugRequestGrid()
	{
		$this->load->model("DrugRequest_model", "drmodel");
		$data = $_REQUEST;
		$data['session'] = $_SESSION;
		$val = array();
		$getlist = $this->drmodel->DrugRequestGrid($data);
		if ( $getlist != false && count($getlist) > 0 )
		{
			foreach ($getlist as $rows)
			{
                array_walk($rows, 'ConvertFromWin1251ToUTF8');
                $val[] = $rows;
			}
			$this->ReturnData($val);
		}
		else
			$this->ReturnData($val);
	}

	/**
	 * Проверка на уникальность пациента
	 * $data['DrugRequestPeriod_id'] - период заявки
	 * $data['MedPersonal_id'] - врач
	 * $data['Person_id'] - врач
	 */
	function checkUniPerson($model, $data)
	{
		$result = $model->checkUniPerson($data);
		if (is_array($result) && (count($result)>0))
		{
			if ($result[0]['record_count']>0)
				return "Данный пациент уже присутствует в списке за текущий период.";
			else 
				return "";
		}
		else 
			return "При выполнении проверки на уникальность пациента<br/>сервер базы данных вернул ошибку!";
	}

	/**
	 * Проверка на удаление пациента
	 */
	function checkDeletePerson($model, $data)
	{
		$this->load->model("Options_model", "opmodel");
		$isremove = $this->opmodel->getOptionsGlobals($data,'is_remove_drug');
		
		$result = $model->checkDeletePersonByMedPersonal($data, $isremove);
		if (is_array($result) && (count($result)>0))
		{
			if ($result[0]['record_count']>0) {
				return "На данного человека внесены медикаменты добавленные другими врачами.<br/>Удаление невозможно.";
			}
		} else {
			return "При выполнении проверки на возможность удаления пациента<br/>сервер базы данных вернул ошибку!";
		}
			
		$result = $model->checkDeletePerson($data, $isremove);
		if (is_array($result) && (count($result)>0))
		{
			if ($result[0]['record_count']>0)
				return "На данного человека внесены медикаменты.<br/>Для удаления человека из списка удалите<br/>сначала удалите медикаменты из заявки.";
			else 
				return "";
		}
		else 
			return "При выполнении проверки на возможность удаления пациента<br/>сервер базы данных вернул ошибку!";
	}

    /**
	 * Проверка на удаление заявки
	 */
	function checkDeleteDrugRequest($model, $data)
	{
		$this->load->model("Options_model", "opmodel");
		$result = $model->checkDeleteDrugRequest($data);
		if (is_array($result) && (count($result)>0))
		{
			if ($result[0]['DrugRequestStatus_id']>1)
				return "Удаление данной заявки невозможно, <br/>т.к. заявка имеет статус '".($result[0]['DrugRequestStatus_Name'])."'.";
			else 
				return "";
		}
		else 
			return "При выполнении проверки на возможность удаления заявки<br/>сервер базы данных вернул ошибку!";
	}
	
	/**
	 * Проверка на уникальность заявки
	 * $data['DrugRequestPeriod_id'] - период заявки
	 * $data['MedPersonal_id'] - врач
	 */
	function checkUniDrugRequest($model, $data)
	{
		$result = $model->checkUniDrugRequest($data);
		if (is_array($result) && (count($result)>0))
		{
			if ($result[0]['record_count']>0)
				return "Заявка на указанный период по выбранному врачу уже занесена.<br/>Сохранение заявки с выбранными параметрами невозможно!";
			else 
				return "";
		}
		else 
			return "При выполнении проверки на уникальность заявки<br/>сервер базы данных вернул ошибку!";
	}
	
	/**
	 * Проверка на доступ ЛПУ к заявке
	 * $data['DrugRequestPeriod_id'] - период заявки
	 * $data['MedPersonal_id'] - врач
	 */
	function checkDrugRequestAccessLpu($model, $data)
	{
		// можно сделать таблицу ЛПУ на базе, но пока это не принято массово а для одной ГУВД, то прямо в коде
		// ибо выполняя задачи, думайте о трудоемкости разных способов реализации в рамках сроков (c) Night
		//$result = $model->checkDrugRequestAccessLpu($data);
		/*
		if ($_SESSION['lpu_id']==10011372) // Lpu_Nick = ПЕРМЬ МСЧ ГУВД
		{
			return "Ввод новых заявок для вашего ЛПУ невозможен <br/>по причине: <b>доступ запрещен</b>.";
		}
		else 
		*/
		// ввод новых заявок для всех ЛПУ согласно настройкам
		$this->load->model("Options_model", "opmodel");
		$is_create = $this->opmodel->getOptionsGlobals($data,'is_create_drugrequest');
		if (!isSuperadmin() && (!$is_create))
		{
			return "Операции ввода и редактирования заявок временно невозможны,<br/> поскольку выполняются подготовительные работы <br/>по созданию заявок нового периода.";
		}
	}

    /**
     * Проверка на открытость заявки ЛПУ
     * $data['DrugRequestPeriod_id'] - период заявки
     * $data['Lpu_id'] - ЛПУ
     */
    function checkDrugRequestLpuClosed($model, $data)
    {
        $result = $model->checkDrugRequestLpuClosed($data);
        if (is_array($result) && (count($result)>0))
        {
            if ($result[0]['record_count']>0)
                return "Заявка ЛПУ на указанный период закрыта.<br/>Добавление заявки невозможно!";
            else
                return "";
        }
        else
            return "При выполнении проверки на уникальность заявки<br/>сервер базы данных вернул ошибку!";
    }

    /**
     * Проверка на наличие заявок врачей со статусом "Перераспределение" в рамках заявки ЛПУ
     * $data['DrugRequestPeriod_id'] - период заявки
     * $data['Lpu_id'] - ЛПУ
     */
    function checkDrugRequestLpuReallocated($model, $data)
    {
        $result = $model->checkDrugRequestLpuReallocated($data);
        if (is_array($result) && (count($result)>0))
        {
            if ($result[0]['record_count']>0)
                return "Заявка ЛПУ содержит заявки со статусом \"Перераспределение\".<br/>Добавление заявки невозможно!";
            else
                return "";
        }
        else
            return "При выполнении проверки на уникальность заявки<br/>сервер базы данных вернул ошибку!";
    }

	/**
	 * Проверка на уникальность медикамента в заявке
	 * $data['DrugProtoMnn_id'] - медикамент
	 * $data['DrugRequest_id'] - заявка
	 * $data['Person_id'] - пациент
	 * $data['DrugRequestType_id'] - тип заявки
	 */
	function checkUniDrugRequestRow($model, $data)
	{
		$result = $model->checkUniDrugRequestRow($data);
		if (is_array($result))
		{
			if (count($result)>0)
			{
				if ($result[0]['record_count']>0)
				{
					if ((!isset($data['Person_id'])) || ($data['Person_id']==0))
						$na_kogo = "врача";
					else 
						$na_kogo = "пациента";

					if ($result[0]['DrugRequestStatus_id']!=3)
					{
						return "Данный медикамент на выбранного ".$na_kogo." вашей ЛПУ уже занесен.";
					}
					else 
					{
						return "Данный медикамент на выбранного ".$na_kogo." уже занесен. <br/>
						Для изменения количества заявленного медикамента пометьте на удаление (кн. Удалить) <br/>
						предыдущую запись с медикаментом и добавьте новую запись с нужным количеством.";
					}
				}
				else 
					return "";
			}
			else 
				return "";
		}
		else 
			return "При выполнении проверки на уникальность медикамента<br/>сервер базы данных вернул ошибку!";
	}

	/**
	 * Проверка на добавление выбранного медикамента
	 */
	function checkUniAllLpuDrugRequestRow()
	{
		$this->load->model('DrugRequest_model', 'model');
		
		$data = $this->ProcessInputData('checkUniAllLpuDrugRequestRow', false);
		
		if (!$data) {
			return false;
		}
		
		$result = $this->model->checkUniAllLpuDrugRequestRow($data);
		$val = array();
		if (is_array($result))
		{
			if (count($result)>0)
			{
				if ($result[0]['record_count']>0)
				{
					$this->ReturnData(array('success' => true, 'count' => $result[0]['record_count']));
					return true;
				}
			}
		}
		//echo json_encode(array('success' => true, 'count' => 0));
        $this->ReturnData(array('success' => true, 'count' => 0));
		return true;
	}
	
	/**
	 * Проверка на свою ЛПУ при сохранении медикамента - только для уже сохраненных
	 * Минздрав может изменять любой медикамент
	 * $data['DrugRequestRow_id'] - позиция заявки
	 */
	function checkSelfDrugRequestRow($model, $data)
	{
		// Под Минздравом проверка не осуществляется 
		if ((isset($data['DrugRequestRow_id'])) && ($data['DrugRequestRow_id']>0) && (!isMinZdrav()))
		{
			$result = $model->checkSelfDrugRequestRow($data);
			if (is_array($result) && (count($result)>0))
			{
				if ($result[0]['record_count']>0)
					return "Вы не можете изменить данный медикамент!";
				else 
					return "";
			}
			else 
				return "При выполнении проверки на возможность сохранения<br/>текущей записи сервер базы данных вернул ошибку!";
		}
		else 
		{
			return "";
		}
	}
	
	/**
	 * Проверка на наличие открытой заявки региона и заявки МО для сохраняемой заявки врача
	 * $data['PersonRegisterType_id'] - тип (регистра) заявки 
	 * $data['DrugRequestPeriod_id'] - раборчий период заявки
	 * $data['Lpu_id'] - ЛПУ заявки
	 */
	function checkExistParentDrugRequest($model, $data) {
		return null;
	}
	

    /**
	 * Логические проверки
	 */
	function getObjectCheck($model, $data, $method) {
		$data['session'] = $_SESSION;
	
		// Логические проверки
		switch ($method) {
			case 'saveDrugRequestPerson': 
				// Проверка при сохранении человека
				return $this->checkUniPerson($model, $data);
				break;
			case 'saveDrugRequestRow':
				if ($data['DrugComplexMnn_id'] <= 0) {
					// Проверка при свою ЛПУ
					$result = $this->checkSelfDrugRequestRow($model, $data);
					if ($result!='')
						return $result;
				}
				if (!isset($data['Merge']) || $data['Merge'] != 'true') {
					// Проверка при сохранении медикамента в заявке
					return $this->checkUniDrugRequestRow($model, $data);
				}
				break;
			case 'saveDrugRequest':
				// Проверка наличия "родительских" заявок (заявка региона и заявка МО) для заявки врача.
				if (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
					$result = $this->checkExistParentDrugRequest($model, $data);
					if ($result!='')
						return $result;
				}
				// Проверка на наличие родительской заявки в составе сводной.
				if (isset($data['PersonRegisterType_id']) && $data['DrugRequestPeriod_id'] > 0) {
					$this->load->model("MzDrugRequest_model", "MzDrugRequest_model");
					$result = $this->MzDrugRequest_model->checkAllowedDrugRequestEdit(array(
						'PersonRegisterType_id' => $data['PersonRegisterType_id'],
						'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id']
					));
					if ($result!='')
						return $result;
				}

				if (isset($data['MedPersonal_id']) && empty($data['DrugRequest_id'])) {
                    // Проверка на предмет закрытия родительской заявки МО
                    $this->load->model("MzDrugRequest_model", "MzDrugRequest_model");
                    $result = $this->checkDrugRequestLpuClosed($model, $data);
                    if ($result!='') {
                        return $result;
                    }

                    // Проверка на наличие в заявке ЛПУ заявок со статусом "Перераспределение"
                    $this->load->model("MzDrugRequest_model", "MzDrugRequest_model");
                    $result = $this->checkDrugRequestLpuReallocated($model, $data);
                    if ($result!='') {
                        return $result;
                    }
				}
				// Проверка при сохранении заявки 
				return $this->checkDrugRequestAccessLpu($model, $data);
				//return $this->checkUniDrugRequest($model, $data);
				break;
			case 'deleteDrugRequestPerson': 
				// Проверка при удалении человека
				return $this->checkDeletePerson($model, $data);
				break;
			case 'deleteDrugRequest': 
				// Проверка при удалении заявки 
				return $this->checkDeleteDrugRequest($model, $data);
				break;
			default:
				return '';
				break;
		}
	}

	/**
	 * Сохранение
	 */
	function SaveObject($method)
	{
		$this->load->model('DrugRequest_model', 'drmodel');
		$this->load->helper('Text');
		
		$data = $this->ProcessInputData($method,true);
        if($data === false) {return false;}

		$val = array();

		$err = $this->getObjectCheck($this->drmodel, $data, $method);
		if (strlen($err) > 0) {
            $this->ReturnError($err);
			return false;
		}
		if( method_exists($this->drmodel, $method) )
			$result = $this->drmodel->$method($data);
		else
			return false;

		if (is_array($result) && (count($result) == 1))
		{
			if ($result[0]['Error_Code']>0)
			{
				$result[0]['success'] = false;
			}
			else 
			{
				$result[0]['success'] = true;
			}
			$val = $result[0];
		}
		else
		{
            $this->ReturnError('Системная ошибка при выполнении скрипта',100002);
            return false;
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}

	/**
	 * Удаление
	 */
	function DeleteObject($method)
	{
		$this->load->model('DrugRequest_model', 'drmodel');
		$this->load->helper('Text');

        $data = $this->ProcessInputData('DeleteObject',true);
        if($data === false) {return false;}
		
		$data['session'] = $_SESSION;

		$err = $this->getObjectCheck($this->drmodel, $data, $method);
		if (strlen($err) > 0)
		{
            $this->ReturnError($err);
			return false;
		}
		if( method_exists($this->drmodel, $method) )
			$result = $this->drmodel->$method($data);
		else
			return false;

        if (is_array($result) && (count($result) == 1))
		{
			if ($result[0]['Error_Code']>0)
			{
				$result[0]['success'] = false;
			}
			else
			{
				$result[0]['success'] = true;
			}
			$val = $result[0];
		}
		else
		{
            $this->ReturnError('Системная ошибка при выполнении скрипта',100002);
            return false;
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}
	
	/**
	 *  Получение справочника медикаментов для заявки
	 *  Входящие данные: $_POST['DrugProtoMnn_id'],
	 *                   $_POST['DrugMnn_id'],
	 *                   $_POST['ReceptFinance_id'],
	 *                   $_POST['DrugProtoMnnType_id'],
	 *                   $_POST['query']
	 *  На выходе: JSON-строка
	 *  Используется: в форме ввода медикаментов
	 */
	function loadDrugProtoMnnList()
	{
		$this->load->model("DrugRequest_model", "drmodel");

		$data = $this->ProcessInputData('loadDrugProtoMnnList', true);
		if ($data) {
			$response = $this->drmodel->loadDrugProtoMnnList($data);
			//TO-DO: непонятно почему не работает стандартный метод ReturnData
			//пока сделан вывод просто через json_encode, нужно будет разобраться
			$this->ProcessModelList($response, true, true);
			echo json_encode($this->GetOutData());
			return true;
		} else {
			return false;
		}
	}

    /**
     * @return bool
     */
    function loadDrugMnnList()
    {
        $this->load->model("DrugRequest_model", "drmodel");

        $data = $this->ProcessInputData('loadDrugProtoMnnList', true);
        if ($data) {
            $data['loadMnnList'] = '2';
            $response = $this->drmodel->loadDrugProtoMnnList($data);
            $this->ProcessModelList($response, true, true);
            echo json_encode($this->GetOutData());
            return true;
        } else {
            return false;
        }
    }

	/**
	 *  Обработка входящих параметров для печати заявки
	 */
	function getDrugRequestPrintParams() {

        $data = $this->ProcessInputData('getDrugRequestPrintParams',false);
        if ($data === false) {return false;}
        if (isMinZdrav()){
            $data['Lpu_id'] = $data['FilterLpu_id'];
        }
		return $data;
	}

    /**
	 * Вывод на печать (выбор шаблонов)
	 */
	function printDrugRequest() {
		$this->load->database('bdreports',false);
		
		//Выставляем таймауты для выполнения запросов, пока вручную
		$this->db->query_timeout = 600;
		
		$this->load->model('DrugRequest_model', 'dbmodel');
		$this->load->model("Options_model", "opmodel");
		$this->load->library('parser');

		$data = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		// Получаем параметры печати
		$data = array_merge($data, $this->getDrugRequestPrintParams());

		if ( (0 == $data['DrugRequestPeriod_id']) || (!in_array($data['PrintType_id'], array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17))) || (!isset($data['DrugRequestRow_actDate'])) || (strlen($data['DrugRequestRow_actDate']) == 0) ) {
			echo 'Неверно заданы параметры';
			return false;
		}

		$data['DrugRequestPeriod_Name'] = 'все';
		$data['DrugRequestType_Name']   = 'все';
		$data['Lpu_Name']               = 'все';
		$data['LpuSection_Name']        = 'все';
		$data['LpuUnit_Name']           = 'все';
		$data['MedPersonal_Fio']        = 'все';

		$response = $this->dbmodel->getDrugRequestHeaderData($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		if ( isset($response[0]['DrugRequestPeriod_Name']) && (strlen($response[0]['DrugRequestPeriod_Name']) > 0) ) {
			$data['DrugRequestPeriod_Name'] = $response[0]['DrugRequestPeriod_Name'];
		}

		if ( ($data['DrugRequestType_id'] > 0) && (isset($response[0]['DrugRequestType_Name'])) && (strlen($response[0]['DrugRequestType_Name']) > 0) ) {
			$data['DrugRequestType_Name'] = strtolower($response[0]['DrugRequestType_Name']);
		}

		if ( isset($response[0]['Lpu_Name']) && (strlen($response[0]['Lpu_Name']) > 0) ) {
			$data['Lpu_Name'] = $response[0]['Lpu_Name'];
		}

		if ( isset($response[0]['LpuSection_Name']) && (strlen($response[0]['LpuSection_Name']) > 0) ) {
			$data['LpuSection_Name'] = $response[0]['LpuSection_Name'];
		}

		if ( isset($response[0]['LpuUnit_Name']) && (strlen($response[0]['LpuUnit_Name']) > 0) ) {
			$data['LpuUnit_Name'] = $response[0]['LpuUnit_Name'];
		}

		if ( isset($response[0]['MedPersonal_Fio']) && (strlen($response[0]['MedPersonal_Fio']) > 0) ) {
			$data['MedPersonal_Fio'] = $response[0]['MedPersonal_Fio'];
		}

		switch ( $data['PrintType_id'] ) {
			case 1:
				$this->printDrugRequest1($data);
			break;

			case 2:
				$this->printDrugRequest2($data);
			break;

			case 3:
				$this->printDrugRequest3($data);
			break;

			case 4:
				$this->printDrugRequest4($data);
			break;

			case 5:
				$this->printDrugRequest5($data);
			break;

			case 6:
				$this->printDrugRequest6($data);
			break;

			case 7:
				$this->printDrugRequest7($data);
			break;

			case 8:
				$this->printDrugRequest8($data);
			break;

			case 9:
				$this->printDrugRequest9($data);
			break;

			case 10:
				$this->printDrugRequest10($data);
			break;

			case 11:
				$this->printDrugRequest11($data);
			break;

			case 12:
				$this->printDrugRequest12($data);
			break;

			case 13:
				$this->printDrugRequest13($data);
			break;

			case 14:
				$this->printDrugRequest14($data);
			break;

			case 15:
				$this->printDrugRequest15($data);
			break;

			case 16:
				$this->printDrugRequest16($data);
			break;

			case 17:
				$this->printDrugRequest17($data);
			break;
		}
	}

    /**
	 * Печать с группировкой по медикаментам, заявленным врачами ЛПУ
	 */
	function printDrugRequest1($data) {
		$drug_request_data      = array();
		$drug_request_total_sum = 0;
		$j                      = 0;
		$print_type_name        = 'Печать с группировкой по медикаментам, заявленным врачами ЛПУ';
		$template               = 'pf_drug_request_1'; // Имя шаблона

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData1($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];
			$j++;

			$response[$key]['Record_Num'] = $j;
			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			$drug_request_data[$key] = $response[$key];
		}

		$header_1 = "none";
		$header_2 = "none";

		if ( $data['LpuSection_id'] > 0 || $data['LpuUnit_id'] > 0 || $data['MedPersonal_id'] > 0 ) {
			$header_2 = "block";
		}
		else {
			$header_1 = "block";
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,
			'privilege_type_name' => ($data['DrugRequestType_id'] == 1 ? 'федеральных' : 'региональных'),
			'header_1' => $header_1,
			'header_2' => $header_2,

			// Данные для таблиц
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' '),
			'drug_request_data' => $drug_request_data
		);

		echo $this->parser->parse($template, $parse_data);
	}

	/**
	 * Печать с группировкой по пациентам из заявок врачей ЛПУ
	 */
	function printDrugRequest2($data) {
		$drug_request_data = array();
		$drug_request_reserve_total_sum = 0;
		$drug_request_total_sum = 0;
		$j = 0;
		$print_type_name = 'Печать с группировкой по пациентам из заявок врачей ЛПУ';
		$template = 'pf_drug_request_2';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData2($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			if ( strlen(trim($row['Person_Fio'])) == 0 ) {
				// Получение данных по резерву
				$drug_request_reserve_total_sum = $row['DrugRequestRow_Summa'];
			}
			else {
				$j++;
				$response[$key]['Record_Num'] = $j;
				$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');
				$drug_request_data[$key] = $response[$key];
			}
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_reserve_total_sum' => number_format($drug_request_reserve_total_sum, 2, ',', ' '),
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Печать с группировкой по пациенту и со списком медикаментов
	 */
	function printDrugRequest3($data) {
		$drug_request_data = array();
		$drug_request_reserve_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$j = 0;
		$person_id = -1;
		$print_type_name = 'Печать с группировкой по пациенту и со списком медикаментов';
		$template = 'pf_drug_request_3';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData3($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			if ($person_id != $row['Person_id']) {
				$i++;
				$person_id = $row['Person_id'];

				if ( $person_id != 0) {
					$drug_request_data[$i]['Person_Fio'] = $row['Person_Fio'];
				}
			}

			// Добавить получение данных по резерву
			if ( $person_id == 0) {
				$drug_request_reserve_data[$i]['drug_reserve_list'][] = $response[$key];
			}
			else {
				$j++;
				$response[$key]['Record_Num'] = $j;
				$drug_request_data[$i]['drug_list'][] = $response[$key];
			}
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_reserve_data' => $drug_request_reserve_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Итоговые сведения по заявке ЛПУ
	 */
	function printDrugRequest4($data) {
		$drug_request_data = array();
		$j = 0;
		//$options = $this->opmodel->getOptionsGlobals($data);
		$print_type_name = 'Итоговые сведения по заявке ЛПУ';
		$template = '';

		switch ( $data['DrugRequestType_id'] ) {
			case 1:
				$template = 'pf_drug_request_4_1';
			break;

			case 2:
				$template = 'pf_drug_request_4_2';
			break;
		}

		// Получаем данные для печати заявки по пациентам заявок
		//$response = $this->dbmodel->getPrintDrugRequestData4($data, $options['globals']);
		$response = $this->dbmodel->getPrintDrugRequestData4($data, $this->options);
		
		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$j++;

			// Для региональной заявки
			if ( isset($row['YoungChild_Count']) ) {
				$response[$key]['Request_PersonCount'] += $row['YoungChild_Count'];
			}

			// Всего заявлено врачами МУ
			$row['Request_SummaTotal1'] = $row['Request_SummaPerson'] + $row['Request_SummaReserve'];
			// Всего заявлено врачами МУ на прикрепленных пациентов
			$row['Attach_SummaTotal1'] = $row['Attach_SummaPerson'] + $row['Request_SummaReserve'];
			// Всего заявлено другими МУ на прикрепленных пациентов
			$row['Attach_SummaTotal2'] = $row['Attach_SummaMinZdrav'] + $row['Attach_SummaOnkoDisp'] + $row['Attach_SummaOnkoGemat'] + $row['Attach_SummaPsycho'] + $row['Attach_SummaRevmat'] + $row['Attach_SummaOtherLpu'];
			// Всего заявлено врачами МУ с учетом МЗ, онкодиспансера, онкогематологии, псих. больниц, ревматологии
			$row['Attach_SummaTotal3'] = $row['Request_SummaTotal1'] + $row['Attach_SummaMinZdrav'] + $row['Attach_SummaOnkoDisp'] + $row['Attach_SummaOnkoGemat'] + $row['Attach_SummaPsycho'] + $row['Attach_SummaRevmat'];
			// Всего заявлено на прикрепленных пациентов
			$row['Attach_SummaTotal4'] = $row['Attach_SummaTotal1'] + $row['Attach_SummaTotal2'];

			$response[$key]['Record_Num'] = $j;
			$response[$key]['Attach_SummaLimit'] = number_format($row['Attach_SummaLimit'], 2, ',', ' ');
			$response[$key]['Request_SummaPerson'] = number_format($row['Request_SummaPerson'], 2, ',', ' ');
			$response[$key]['Request_SummaReserve'] = number_format($row['Request_SummaReserve'], 2, ',', ' ');
			$response[$key]['Request_SummaTotal1'] = number_format($row['Request_SummaTotal1'], 2, ',', ' ');
			$response[$key]['Attach_SummaPerson'] = number_format($row['Attach_SummaPerson'], 2, ',', ' ');
			$response[$key]['Attach_SummaTotal1'] = number_format($row['Attach_SummaTotal1'], 2, ',', ' ');
			$response[$key]['Attach_SummaMinZdrav'] = number_format($row['Attach_SummaMinZdrav'], 2, ',', ' ');
			$response[$key]['Attach_SummaOnkoDisp'] = number_format($row['Attach_SummaOnkoDisp'], 2, ',', ' ');
			$response[$key]['Attach_SummaOnkoGemat'] = number_format($row['Attach_SummaOnkoGemat'], 2, ',', ' ');
			$response[$key]['Attach_SummaPsycho'] = number_format($row['Attach_SummaPsycho'], 2, ',', ' ');
			$response[$key]['Attach_SummaRevmat'] = number_format($row['Attach_SummaRevmat'], 2, ',', ' ');
			$response[$key]['Attach_SummaOtherLpu'] = number_format($row['Attach_SummaOtherLpu'], 2, ',', ' ');
			$response[$key]['Attach_SummaTotal2'] = number_format($row['Attach_SummaTotal2'], 2, ',', ' ');
			$response[$key]['Attach_SummaTotal3'] = number_format($row['Attach_SummaTotal3'], 2, ',', ' ');
			$response[$key]['Attach_SummaTotal4'] = number_format($row['Attach_SummaTotal4'], 2, ',', ' ');
			$response[$key]['Request_LimitOverflow1'] = number_format(round(($row['Attach_SummaLimit'] == 0 ? "-" : ($row['Request_SummaTotal1'] / $row['Attach_SummaLimit']) * 100 - 100), 2), 2, ',', ' ');
			$response[$key]['Attach_LimitOverflow1'] = number_format(round(($row['Attach_SummaLimit'] == 0 ? "-" : ($row['Attach_SummaTotal1'] / $row['Attach_SummaLimit']) * 100 - 100), 2), 2, ',', ' ');
			$response[$key]['Attach_LimitOverflow2'] = number_format(round(($row['Attach_SummaLimit'] == 0 ? "-" : ($row['Attach_SummaTotal3'] / $row['Attach_SummaLimit']) * 100 - 100), 2), 2, ',', ' ');

			$drug_request_data[$key] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Полная заявка на пациентов
	 */
	function printDrugRequest5($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$j = 0;
		$person_id = -1;
		$print_type_name = 'Полная заявка на пациентов';
		$template = 'pf_drug_request_5';

		if ( !isset($data['MedPersonal_id']) || $data['MedPersonal_id'] <= 0 ) {
			echo "Для получения отчета '", $print_type_name, "' должен быть указан врач";
			return false;
		}

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData5($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			if ( $person_id != $row['Person_id'] ) {
				$i++;
				$person_id = $row['Person_id'];

				if ( $person_id != 0) {
					$drug_request_data[$i]['Person_Fio'] = $row['Person_Fio'];
				}
			}

			if ( $row['DrugRequestRow_Summa'] > 0 ) {
				$drug_request_total_sum += $row['DrugRequestRow_Summa'];

				$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
				$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

				$j++;
				$response[$key]['Record_Num'] = $j;
			}
			else {
				$response[$key]['DrugRequestRow_Kolvo'] = '&nbsp;';
				$response[$key]['DrugRequestRow_Price'] = '&nbsp;';
				$response[$key]['DrugRequestRow_Summa'] = '&nbsp;';
				$response[$key]['Record_Num'] = '&nbsp;';
			}

			$drug_request_data[$i]['drug_list'][] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Заявки врачей
	 */
	function printDrugRequest6($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$i = -1;
		$j = -1;
		$k = 0;
		$med_personal_id = -1;
		$person_id = -1;
		$print_type_name = 'Заявки врачей';
		$template = 'pf_drug_request_6';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData6($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			// drug_request_data
			if ($med_personal_id != $row['MedPersonal_id']) { // Новый врач в списке
				$i++;

				$drug_request_data[$i]['drug_request_sum'] = 0;
				$drug_request_data[$i]['MedPersonal_Fio'] = $row['MedPersonal_Fio'];
				$drug_request_data[$i]['person_drug_request_data'] = array();
				$drug_request_data[$i]['reserve_drug_request_data'] = array();
				$j = -1;
				$med_personal_id = $row['MedPersonal_id'];
				$person_id = -1;
			}

			// person_drug_request_data
			if ($person_id != $row['Person_id']) { // Новый пациент в списке врача
				$j++;
				$k = 0;

				$person_id = $row['Person_id'];

				if ( $person_id != 0) {
					$drug_request_data[$i]['person_drug_request_data'][$j]['Person_Fio'] = $row['Person_Fio'];
				}
			}

			$drug_request_data[$i]['drug_request_sum'] += $row['DrugRequestRow_Summa'];

			if ( $person_id == 0) {
				// Добавить данные по резерву для текущего врача
				$drug_request_data[$i]['reserve_drug_request_data'][0]['reserve_drug_list'][] = $response[$key];
			}
			else {
				// Добавить медикамент в список для текущего пациента
				$k++;
				$response[$key]['Record_Num'] = $k;
				$drug_request_data[$i]['person_drug_request_data'][$j]['person_drug_list'][] = $response[$key];
			}
		}

		for ( $i = 0; $i < count($drug_request_data); $i++) {
			$drug_request_data[$i]['drug_request_sum'] = number_format($drug_request_data[$i]['drug_request_sum'], 2, ',', ' ');
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Сводная заявка на прикрепленных к ЛПУ пациентов
	 */
	function printDrugRequest7($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$j = 0;
		$print_type_name = 'Сводная заявка на прикрепленных к ЛПУ пациентов';
		$template = 'pf_drug_request_7';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData7($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];
			$j++;

			$response[$key]['Record_Num'] = $j;
			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');
			$drug_request_data[$key] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,
			'privilege_type_name' => ($data['DrugRequestType_id'] == 1 ? 'федеральных' : 'региональных'),

			// Данные для таблиц
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' '),
			'drug_request_data' => $drug_request_data
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Печать заявки с данными пациента
	 */
	function printDrugRequest8($data) {
		$drug_request_data = array();
		$drug_request_reserve_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$j = 0;
		$person_id = -1;
		$print_type_name = 'Печать заявки с данными пациента';
		$template = 'pf_drug_request_8';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData8($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			if ($person_id != $row['Person_id']) {
				$i++;
				$person_id = $row['Person_id'];

				if ( $person_id != 0) {
					$drug_request_data[$i]['Person_Fio'] = $row['Person_Fio'];
					$drug_request_data[$i]['Person_Birthday'] = $row['Person_Birthday'];
					$drug_request_data[$i]['UAddress_Name'] = $row['UAddress_Name'];
					$drug_request_data[$i]['AttachLpu_Name'] = $row['AttachLpu_Name'];
				}
			}

			// Добавить получение данных по резерву
			if ( $person_id == 0) {
				$drug_request_reserve_data[$i]['drug_reserve_list'][] = $response[$key];
			}
			else {
				$j++;
				$response[$key]['Record_Num'] = $j;
				$drug_request_data[$i]['drug_list'][] = $response[$key];
			}
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_reserve_data' => $drug_request_reserve_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Превышение лимита
	 */
	function printDrugRequest9($data) {
		$drug_request_data = array();
		$j = 0;
		$print_type_name = 'Превышение лимита';
		$template = '';

		switch ($data['DrugRequestType_id']) {
			case 1:
				$template = 'pf_drug_request_9_1';
			break;

			case 2:
				$template = 'pf_drug_request_9_2';
			break;
		}

		// Получаем данные для печати заявки по пациентам заявок
		$response = $this->dbmodel->getPrintDrugRequestData9($data, $this->options);
		
		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$j++;

			// Для региональной заявки
			if ( isset($row['YoungChild_Count']) ) {
				$response[$key]['Request_PersonCount'] += $row['YoungChild_Count'];
				$response[$key]['YoungChild_SummaLimit'] = number_format($row['YoungChild_SummaLimit'], 2, ',', ' ');

				$row['Request_SummaLimit'] += $row['YoungChild_SummaLimit'];
			}

			$row['Attach_SummaTotal1'] = $row['Attach_SummaPerson'] + $row['Attach_SummaReserve'] + $row['Attach_SummaOtherLpu'];
			$row['Attach_SummaTotal2'] = $row['Attach_SummaTotal1'] + $row['Attach_SummaMinZdrav'] + $row['Attach_SummaOnkoDisp'] + $row['Attach_SummaOnkoGemat'];

			$response[$key]['Record_Num'] = $j;
			$response[$key]['Request_SummaLimit'] = number_format($row['Request_SummaLimit'], 2, ',', ' ');
			$response[$key]['Attach_SummaLimit'] = number_format($row['Attach_SummaLimit'], 2, ',', ' ');
			$response[$key]['Attach_SummaPerson'] = number_format($row['Attach_SummaPerson'], 2, ',', ' ');
			$response[$key]['Attach_SummaReserve'] = number_format($row['Attach_SummaReserve'], 2, ',', ' ');
			$response[$key]['Attach_SummaOtherLpu'] = number_format($row['Attach_SummaOtherLpu'], 2, ',', ' ');
			$response[$key]['Attach_SummaTotal1'] = number_format($row['Attach_SummaTotal1'], 2, ',', ' ');
			$response[$key]['Attach_LimitOverflow1'] = number_format(round(($row['Attach_SummaLimit'] == 0 ? "-" : ($row['Attach_SummaTotal1'] / $row['Attach_SummaLimit']) * 100 - 100), 2), 2, ',', ' ');
			$response[$key]['Attach_SummaMinZdrav'] = number_format($row['Attach_SummaMinZdrav'], 2, ',', ' ');
			$response[$key]['Attach_SummaOnkoDisp'] = number_format($row['Attach_SummaOnkoDisp'], 2, ',', ' ');
			$response[$key]['Attach_SummaOnkoGemat'] = number_format($row['Attach_SummaOnkoGemat'], 2, ',', ' ');
			$response[$key]['Attach_SummaTotal2'] = number_format($row['Attach_SummaTotal2'], 2, ',', ' ');

			$drug_request_data[$key] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Заявка других ЛПУ на прикрепленных
	 */
	function printDrugRequest10($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$j = 0;
		$drug_proto_mnn_id = -1;
		$print_type_name = 'Заявка других ЛПУ на прикрепленных';
		$template = 'pf_drug_request_10';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData10($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			if ( $drug_proto_mnn_id != $row['DrugProtoMnn_id'] ) {
				$i++;
				$drug_proto_mnn_id = $row['DrugProtoMnn_id'];

				if ( $drug_proto_mnn_id != 0) {
					$drug_request_data[$i]['DrugProtoMnn_Name'] = $row['DrugProtoMnn_Name'];
				}
			}

			$j++;
			$response[$key]['Record_Num'] = $j;
			$drug_request_data[$i]['drug_list'][] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Заявка МЗ на прикрепленных
	 */
	function printDrugRequest11($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$j = 0;
		$drug_proto_mnn_id = -1;
		$print_type_name = 'Заявка МЗ на прикрепленных';
		$template = 'pf_drug_request_11';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData11($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			if ( $drug_proto_mnn_id != $row['DrugProtoMnn_id'] ) {
				$i++;
				$drug_proto_mnn_id = $row['DrugProtoMnn_id'];

				if ( $drug_proto_mnn_id != 0) {
					$drug_request_data[$i]['DrugProtoMnn_Name'] = $row['DrugProtoMnn_Name'];
				}
			}

			$j++;
			$response[$key]['Record_Num'] = $j;
			$drug_request_data[$i]['drug_list'][] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Заявка онкодиспансером на прикрепленных
	 */
	function printDrugRequest12($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$j = 0;
		$drug_proto_mnn_id = -1;
		$print_type_name = 'Заявка онкодиспансером на прикрепленных';
		$template = 'pf_drug_request_12';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData12($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			if ( $drug_proto_mnn_id != $row['DrugProtoMnn_id'] ) {
				$i++;
				$drug_proto_mnn_id = $row['DrugProtoMnn_id'];

				if ( $drug_proto_mnn_id != 0) {
					$drug_request_data[$i]['DrugProtoMnn_Name'] = $row['DrugProtoMnn_Name'];
				}
			}

			$j++;
			$response[$key]['Record_Num'] = $j;
			$drug_request_data[$i]['drug_list'][] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Заявка онкогематологией на прикрепленных
	 */
	function printDrugRequest13($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$j = 0;
		$drug_proto_mnn_id = -1;
		$print_type_name = 'Заявка онкогематологией на прикрепленных';
		$template = 'pf_drug_request_13';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData13($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			if ( $drug_proto_mnn_id != $row['DrugProtoMnn_id'] ) {
				$i++;
				$drug_proto_mnn_id = $row['DrugProtoMnn_id'];

				if ( $drug_proto_mnn_id != 0) {
					$drug_request_data[$i]['DrugProtoMnn_Name'] = $row['DrugProtoMnn_Name'];
				}
			}

			$j++;
			$response[$key]['Record_Num'] = $j;
			$drug_request_data[$i]['drug_list'][] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Соответствие выписки и заявки с группировкой по пациенту и со списком медикаментов
	 */
	function printDrugRequest14($data) {
		$drug_request_data = array();
		$drug_request_reserve_data = array();
		$drug_request_row_id = -1;
		$drug_request_total_sum = 0;
		$evn_recept_total_sum = 0;
		$i = 0;
		$j = 0;
		$person_id = -1;
		$print_type_name = 'Соответствие выписки и заявки с группировкой по пациенту и со списком медикаментов';
		$template = 'pf_drug_request_14';

		if ( !isset($data['MedPersonal_id']) || $data['MedPersonal_id'] <= 0 ) {
			echo "Для получения отчета 'Соответствие выписки и заявки с группировкой по пациенту и со списком медикаментов' должен быть указан врач";
			return false;
		}

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData14($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			if ( $person_id != $row['Person_id'] ) {
				$i++;
				$person_id = $row['Person_id'];

				$drug_request_data[$i]['PersonEvnRecept_Sum'] = 0;
				$drug_request_data[$i]['Person_Fio'] = $row['Person_Fio'];
				$drug_request_data[$i]['Person_Birthday'] = $row['Person_Birthday'];
				$drug_request_data[$i]['PersonDrugRequest_Sum'] = 0;
				$drug_request_data[$i]['UAddress_Name'] = $row['UAddress_Name'];
			}

			if ( $drug_request_row_id != $row['DrugRequestRow_id'] ) {
				$j++;

				$drug_request_row_id = $row['DrugRequestRow_id'];

				$drug_request_data[$i]['PersonDrugRequest_Sum'] += $row['DrugRequestRow_Summa'];
				$response[$key]['DrugRequestRow_Kolvo'] = number_format($row['DrugRequestRow_Kolvo'], 2, ',', ' ');
				$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
				$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');
				$response[$key]['Record_Num'] = $j;

				if ( $drug_request_row_id == 0 ) {
					$response[$key]['DrugRequestRow_Kolvo'] = '&nbsp;';
					$response[$key]['DrugRequestRow_Price'] = '&nbsp;';
					$response[$key]['DrugRequestRow_Summa'] = '&nbsp;';
				}
			}
			else {
				$response[$key]['Lpu_Nick'] = '&nbsp;';
				$response[$key]['LpuSection_Name'] = '&nbsp;';
				$response[$key]['MedPersonal_Fio'] = '&nbsp;';
				$response[$key]['DrugRequestRow_Kolvo'] = '&nbsp;';
				$response[$key]['DrugRequestRow_Price'] = '&nbsp;';
				$response[$key]['DrugRequestRow_Summa'] = '&nbsp;';
				$response[$key]['Record_Num'] = '&nbsp;';

				if ( $drug_request_row_id > 0 ) {
					$response[$key]['DrugRequestRow_Code'] = '&nbsp;';
					$response[$key]['DrugRequestRow_Name'] = '&nbsp;';
				}
			}

			$drug_request_data[$i]['PersonEvnRecept_Sum'] += $row['EvnRecept_Summa'];

			$response[$key]['Drug_Price'] = number_format($row['Drug_Price'], 2, ',', ' ');
			$response[$key]['EvnRecept_Kolvo'] = number_format($row['EvnRecept_Kolvo'], 2, ',', ' ');
			$response[$key]['EvnRecept_Summa'] = number_format($row['EvnRecept_Summa'], 2, ',', ' ');

			$drug_request_data[$i]['drug_list'][] = $response[$key];
		}

		foreach ( $drug_request_data as $key => $row ) {
			// var_dump($row);
			$drug_request_total_sum += $row['PersonDrugRequest_Sum'];
			$evn_recept_total_sum += $row['PersonEvnRecept_Sum'];

			$drug_request_data[$key]['PersonDrugRequest_Sum'] = number_format($row['PersonDrugRequest_Sum'], 2, ',', ' ');
			$drug_request_data[$key]['PersonEvnRecept_Sum'] = number_format($row['PersonEvnRecept_Sum'], 2, ',', ' ');
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' '),
			'evn_recept_total_sum' => number_format($evn_recept_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Несоответствие типа льготы и типа заявки
	 */
	function printDrugRequest15($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$print_type_name = 'Несоответствие типа льготы и типа заявки';
		$template = 'pf_drug_request_15';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData15($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];
			$i++;

			$response[$key]['Record_Num'] = $i;
			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			$drug_request_data[$key] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Заявка на прикрепленных к другим ЛПУ
	 */
	function printDrugRequest16($data) {
		$drug_request_data = array();
		$drug_request_total_sum = 0;
		$i = 0;
		$j = 0;
		$drug_proto_mnn_id = -1;
		$print_type_name = 'Заявка на прикрепленных к другим ЛПУ';
		$template = 'pf_drug_request_16';

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData16($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];

			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			if ( $drug_proto_mnn_id != $row['DrugProtoMnn_id'] ) {
				$i++;
				$drug_proto_mnn_id = $row['DrugProtoMnn_id'];

				if ( $drug_proto_mnn_id != 0) {
					$drug_request_data[$i]['DrugProtoMnn_Name'] = $row['DrugProtoMnn_Name'];
				}
			}

			$j++;
			$response[$key]['Record_Num'] = $j;
			$drug_request_data[$i]['drug_list'][] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'lpu_section_name' => $data['LpuSection_Name'],
			'lpu_unit_name' => $data['LpuUnit_Name'],
			'med_personal_fio' => $data['MedPersonal_Fio'],
			'print_type_name' => $print_type_name,

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

    /**
	 * Сводная заявка по медикаментам (отчет для МЗ)
	 */
	function printDrugRequest17($data) {
		$drug_request_data      = array();
		$drug_request_total_sum = 0;
		$j                      = 0;
		$print_type_name        = 'Сводная заявка по медикаментам (отчет для МЗ)';
		$template               = 'pf_drug_request_17'; // Имя шаблона

		// Получаем данные для печати заявки
		$response = $this->dbmodel->getPrintDrugRequestData17($data);

		if ( !is_array($response) ) {
			echo 'Ошибка при получении данных для печати заявки';
			return false;
		}

		foreach ( $response as $key => $row ) {
			$drug_request_total_sum += $row['DrugRequestRow_Summa'];
			$j++;

			$response[$key]['Record_Num'] = $j;
			$response[$key]['DrugRequestRow_Price'] = number_format($row['DrugRequestRow_Price'], 2, ',', ' ');
			$response[$key]['DrugRequestRow_Summa'] = number_format($row['DrugRequestRow_Summa'], 2, ',', ' ');

			$drug_request_data[$key] = $response[$key];
		}

		$parse_data = array(
			// Данные для заголовка
			'drug_request_period_name' => $data['DrugRequestPeriod_Name'],
			'drug_request_type_name' => $data['DrugRequestType_Name'],
			'lpu_name' => $data['Lpu_Name'],
			'print_type_name' => $print_type_name,
			'privilege_type_name' => ($data['DrugRequestType_id'] == 1 ? 'федеральных' : 'региональных'),

			// Данные для таблиц
			'drug_request_data' => $drug_request_data,
			'drug_request_total_sum' => number_format($drug_request_total_sum, 2, ',', ' ')
		);

		echo $this->parser->parse($template, $parse_data);
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$this->load->model('DrugRequest_model', 'dbmodel');
			$response = $this->dbmodel->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение рабочего периода
	 */
	function saveDrugRequestPeriod() {
		$data = $this->ProcessInputData('saveDrugRequestPeriod', true);		
		if ($data){
			$this->load->model('DrugRequest_model', 'DrugRequest_model');
			$save_response = $this->DrugRequest_model->saveDrugRequestPeriod($data);

			if (!empty($save_response[0]) && !empty($save_response[0]['DrugRequestPeriod_id'])) {
				//сохранение списка планово-отчетных периодов
				if(!empty($data['DrugRequestPlanPeriodJSON'])) {
					$this->load->model("MzDrugRequest_model", "MzDrugRequest_model");
					$response = $this->MzDrugRequest_model->saveDrugRequestPlanPeriodFromJSON(array(
						'DrugRequestPeriod_id' => $save_response[0]['DrugRequestPeriod_id'],
						'json_str' => $data['DrugRequestPlanPeriodJSON'],
						'pmUser_id' => $data['pmUser_id']
					));
					if ($response && !empty($response['Error_Msg'])) {
						$this->ReturnError($response['Error_Msg']);
						return false;
					}
				}
			}

			$this->ProcessModelSave($save_response, true, 'Ошибка при сохранении Справочник медикаментов: период заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных о рабочем периоде
	 */
	function loadDrugRequestPeriod() {
		$data = $this->ProcessInputData('loadDrugRequestPeriod', true);
		if ($data){			
			$this->load->model('DrugRequest_model', 'DrugRequest_model');
			$response = $this->DrugRequest_model->loadDrugRequestPeriod($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка рабочих периодов
	 */
	function loadDrugRequestPeriodList() {
		$data = $this->ProcessInputData('loadDrugRequestPeriodList', true, true);
		if ($data) {
			$this->load->model('DrugRequest_model', 'DrugRequest_model');
			$response = $this->DrugRequest_model->loadDrugRequestPeriodList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление рабочего периода
	 */
	function deleteDrugRequestPeriod() {
		$data = $this->ProcessInputData('deleteDrugRequestPeriod', true, true);
		if ($data) {
			$this->load->model('DrugRequest_model', 'DrugRequest_model');
			$response = $this->DrugRequest_model->deleteDrugRequestPeriod($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение максимальной даты
	 */
	function getDrugRequestPeriodMaxDate() {
		$this->load->model('DrugRequest_model', 'DrugRequest_model');
		$response = $this->DrugRequest_model->getDrugRequestPeriodMaxDate();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Подсчет количества участков у врача соответствующего заявке
	 */
	function getLpuRegionCountByDrugRequestId() {
		$data = $this->ProcessInputData('getLpuRegionCountByDrugRequestId', true);
		if ($data){
			$this->load->model('DrugRequest_model', 'DrugRequest_model');
			$response = $this->DrugRequest_model->getLpuRegionCountByDrugRequestId($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание копии заявки
	 */
	function createDrugRequestCopy() {
		$data = $this->ProcessInputData('createDrugRequestCopy', true);
		if ($data){
			$this->load->model('DrugRequest_model', 'DrugRequest_model');
			$response = $this->DrugRequest_model->createDrugRequestCopy($data);
			$this->ProcessModelSave($response, true, 'Ошибка при создании копии заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание списка пациентов для заявки
	 */
	function createDrugRequestPersonList() {
		$data = $this->ProcessInputData('createDrugRequestPersonList', true);
		if ($data){
			$this->load->model('DrugRequest_model', 'DrugRequest_model');
			$response = $this->DrugRequest_model->createDrugRequestPersonList($data);
			$this->ProcessModelSave($response, true, 'Ошибка при создании списка пациентов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Копирование списка медикаментов из одной заявки в другую
	 */
	function createDrugRequestDrugCopy() {
		$data = $this->ProcessInputData('createDrugRequestDrugCopy', true);
		if ($data){
			$this->load->model('DrugRequest_model', 'DrugRequest_model');
			$response = $this->DrugRequest_model->createDrugRequestDrugCopy($data);
			$this->ProcessModelSave($response, true, 'Ошибка при копировании списка медикаментов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Сохранение количества в плане потребности
     */
    function moveDrugRequestRow() {
        $data = $this->ProcessInputData('moveDrugRequestRow', true);
        if ($data){
            $this->load->model('DrugRequest_model', 'DrugRequest_model');
            $response = $this->DrugRequest_model->moveDrugRequestRow($data);
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении данных')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Исключение пациента из заявки с переносом медикаментов в резерв
     */
    function excludeDrugRequestPerson() {
        $data = $this->ProcessInputData('excludeDrugRequestPerson', true);
        if ($data){
            $this->load->model('DrugRequest_model', 'DrugRequest_model');
            $response = $this->DrugRequest_model->excludeDrugRequestPerson($data);
            $this->ProcessModelSave($response, true, 'Ошибка при обновлении данных')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка строк из резерва заявки для комбо
     */
    function loadReservedDrugRequestRowCombo() {
        $data = $this->ProcessInputData('loadReservedDrugRequestRowCombo', false);
        if ($data) {
            $this->load->model('DrugRequest_model', 'DrugRequest_model');
            $response = $this->DrugRequest_model->loadReservedDrugRequestRowCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
}