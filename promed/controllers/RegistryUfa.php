<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
* modified 19.06.2013
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
*/
require("Registry.php");
class RegistryUfa extends Registry {
	var $model_name = "RegistryUfa_model";
	var $model_nameVE = "RegistryUfa_model";
	var $scheme = "r2";
	
	/**
	* comment
	*/
	function __construct() {
		parent::__construct();
		// Инициализация класса и настройки
		$this->inputRules['getMoreInfoRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['getLpuCidList'] = [];

		$this->inputRules['refreshRegistryVolumes'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['exportUnionRegistryToXmlCheckExist'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['signRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'documentSigned',
				'label' => 'Подпись',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);
				
		$this->inputRules['updateRegistryTwoCols'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_orderDate',
				'label' => 'Отчётный месяц и год',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_pack',
				'label' => 'Пачка реестра',
				'rules' => '',
				'type' => 'int'
			)
		);
		$this->inputRules['getRegistryTwoCols'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_orderDate',
				'label' => 'Отчётный месяц и год',
				'rules' => '',
				'type' => 'date'
			), 
			array(
				'field' => 'Registry_pack',
				'label' => 'Пачка реестра',
				'rules' => '',
				'type' => 'int'
			)
		);
		$this->inputRules['saveRegistry'] = array(
			array(
				'field' => 'OrgSmo_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_cid',
				'label' => 'МО-контрагент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'Структурное подразделение МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitSet_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер счета',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistrySubType',
				'label' => 'Подтип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistrySubType_id',
				'label' => 'Подтип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsActive',
				'label' => 'Признак активного регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_IsNotInsur',
				'label' => 'Незастрахованные лица',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип диспансеризации / медосмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак новых реестров',
				'rules' => '',
				'default' => null,
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Comments',
				'label' => 'Коментарий для случаев не соответствующих отчетному периоду',
				'rules' => 'trim',
				'default' => '',
				'type' => 'string'
			)
		);
		$this->inputRules['saveUnionRegistry'] = array(
			array(
				'field' => 'Lpu_cid',
				'label' => 'МО-контрагент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSmo_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitSet_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер счета',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistrySubType',
				'label' => 'Подтип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsActive',
				'label' => 'Признак активного регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_IsNotInsur',
				'label' => 'Незастрахованные лица',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип диспансеризации / медосмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак новых реестров',
				'rules' => '',
				'default' => null,
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Comments',
				'label' => 'Коментарий для случаев не соответствующих отчетному периоду',
				'rules' => '',
				'default' => '',
				'type' => 'string'
			)
		);
		$this->inputRules['loadUnionRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
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
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStacType_id',
				'label' => 'Тип реестра стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accYear',
				'label' => 'год',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак новых реестров',
				'rules' => '',
				'default' => null,
				'type' => 'id'
			)
		);

		$this->inputRules['deleteUnionRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор финального реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['deleteUnionRegistryWithData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор финального реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);
	
		//Task# Групповое исключение записей из реестра
		$Filter = array(
			array(
				'field' => 'Filter',
				'label' => 'JSON строка фильтра',
				'rules' => '',
				'type' => 'string'
				),
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Type_select',
				'label' => 'Удаление по фильтру или по Evn_id', //0 - по списку Evn_id, 1 - по фильтру
				'rules' => '',
				'type' => 'id'
			)
		); 
		
		$this->inputRules['deleteRegistryGroupData'] = array_merge($this->inputRules['deleteRegistryData'], $Filter);
		//End Task
		
		$this->inputRules['loadRegistryData'] = array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'VolumeType_id',
					'label' => 'Объём',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OrgSmo_id',
					'label' => 'СМО',
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
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
						'field' => 'Person_SurName',
						'label' => 'Фамилия',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'Person_FirName',
						'label' => 'Имя',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'Person_SecName',
						'label' => 'Отчество',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'Polis_Num',
						'label' => 'Полис',
						'rules' => '',
						'type' => 'string'
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
				),
				array(
					'field' => 'RegistryStatus_id',
					'label' => 'Статус реестра',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'filterRecords',
					'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'filterIsEarlier',
					'label' => 'Подавался ранее',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'filterIsZNO',
					'label' => 'ЗНО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Json строка для фильтра',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'sort',
					'label' => 'Json строка сортировки',
					'rules' => '',
					'type' => 'string'
				)
		);

		$this->inputRules['loadRegistryDataBadVol'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'VolumeType_id',
				'label' => 'Объём',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSmo_id',
				'label' => 'СМО',
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
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Полис',
				'rules' => '',
				'type' => 'string'
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
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'filterRecords',
				'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
			array(
				'field' => 'filterIsEarlier',
				'label' => 'Подавался ранее',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
			array(
				'field' => 'filterIsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Filter',
				'label' => 'Json строка для фильтра',
				'rules' => '',
				'type' => 'string'
			)
		);

		$this->inputRules['setIsBadVol'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_ids',
				'label' => 'Случаи',
				'rules' => 'required',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'RegistryData_IsBadVol',
				'label' => 'Признак превышения объёма МП',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['reformUnionRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['loadRegistryErrorCom'] = array(
				array(
						'field' => 'Registry_id',
						'label' => 'Идентификатор реестра',
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
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Json строка для фильтра',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				)
		);
		$this->inputRules['loadRegistryErrorTFOMS'] = array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
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
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Person_FIO',
					'label' => 'ФИО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryErrorType_Code',
					'label' => 'Код ошибка',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter',
					'label' => 'Json строка для фильтра',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'RegistryType_id',
					'label' => 'Тип реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'filterIsZNO',
					'label' => 'ЗНО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Polis_Num',
					'label' => 'номер полиса',
					'rules' => '',
					'type' => 'string'
				)
		);
		$this->inputRules['loadRegistryError'] = array(
				array(
					'field' => 'Registry_id',
					'label' => 'Идентификатор реестра',
					'rules' => '',
					'type' => 'id'
				),
				array(
						'field' => 'RegistryType_id',
						'label' => 'Тип реестра',
						'rules' => '',
						'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
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
					'field' => 'Evn_id',
					'label' => 'ИД случая',
					'rules' => '',
					'type' => 'id'
				),
				array(
						'field' => 'Person_SurName',
						'label' => 'Фамилия',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'Person_FirName',
						'label' => 'Имя',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'Person_SecName',
						'label' => 'Отчество',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RegistryError_Code',
						'label' => 'Код ошибки',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RegistryErrorType_id',
						'label' => 'Ошибка',
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
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Filter',
					'label' => 'Json строка для фильтра',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'filterIsZNO',
					'label' => 'ЗНО',
					'rules' => '',
					'type' => 'int'
				),
		);
		//End Task
		
		$this->inputRules['deleteRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		//Task#25768 Ручной запуск МЭК
		$this->inputRules['startMek'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			)
		);
		
		$this->inputRules['importRegistryFromDbf'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		);
		
		$this->inputRules['importRegistryFromXml'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Идентификатор типа реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		);
		
		$this->inputRules['importRegistrySmoDataFromDbf'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		);
		
		$this->inputRules['printRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);
		
		$this->inputRules['exportRegistryErrorDataToDbf'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Идентификатор типа реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['loadUnionRegistryChildGrid'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
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
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Идентификатор типа реестра',
				'rules' => '',
				'type' => 'id'
			)
		);
		
		$this->inputRules['exportRegistryToDbf'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'send',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'int'
			)
		);
		
		$this->inputRules['exportRegistryToXml'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OverrideExportOneMoreOrUseExist',
				'label' => 'Скачать с сервера или перезаписать',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'withSign',
				'label' => 'Флаг выгрузки с подписанием',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Registry_orderDate',
				'label' => 'Отчётный месяц и год',
				'rules' => '',
				'type' => 'date'
			), 
			array(
				'field' => 'Registry_pack',
				'label' => 'Пачка реестра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'forSign',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['exportRegistryToXmlCheckVolume'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		//Корректировка объёмов реестров по справочникам
		$this->inputRules['RegistryLimitVolumeData'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_ids',
				'label' => 'Идентификаторы реестров в JSON',
				'rules' => '',
				'type' => 'string'
			)
		); 
		$this->inputRules['cutVolumeRegisters'] = array(
			array(
				'field' => 'json',
				'label' => 'Json строка',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_ids',
				'label' => 'Идентификаторы реестров в JSON',
				'rules' => '',
				'type' => 'string'
			)
		);

		$this->inputRules['loadUnionRegistryGrid'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Новые реестры',
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
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['loadUnionRegistryErrorTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorType_Code',
				'label' => 'Код ошибки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorClass_id',
				'label' => 'Вид ошибки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorTFOMS_Comment',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
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
		);

		$this->inputRules['loadUnionRegistryData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
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
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
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
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NumCard',
				'label' => 'Номер карты',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistrySubType',
				'label' => 'Подтип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Полис',
				'rules' => '',
				'type' => 'string'
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
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'filterRecords',
				'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
			array(
				'field' => 'filterIsEarlier',
				'label' => 'Подавался ранее',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
		);

		$this->inputRules['setUnionRegistryStatus'] = array(
			array(
				'default' => null,
				'field' => 'Registry_ids',
				'label' => 'Идентификаторы реестров',
				'rules' => 'required',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['deleteUnionRegistrys'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['deleteOrgSmoRegistryData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSmo_ids',
				'label' => 'СМО',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_IsNotInsur',
				'label' => 'Незастрахованные лица',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['checkIncludeInUnioinRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['setRegistryStatus'] = array(
			array(
				'default' => null,
				'field' => 'Registry_ids',
				'label' => 'Идентификаторы реестров',
				'rules' => 'required',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'is_manual',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		// Инициализация класса и настройки
		$this->inputRules['exportRegistryGroupToXml'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификаторы реестров',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Типы реестров',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номера реестров',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'KatNasel_id',
				'label' => 'Категории населения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OverrideControlFlkStatus',
				'label' => 'OverrideControlFlkStatus',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OverrideExportOneMoreOrUseExist',
				'label' => 'OverrideExportOneMoreOrUseExist',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'onlyLink',
				'label' => 'onlyLink',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'send',
				'label' => 'send',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'withSign',
				'label' => 'Флаг выгрузки с подписанием',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['loadRegistryDouble'] = array_merge(
			$this->inputRules['loadRegistryDouble'],
			$this->inputRules['Filter']
		);

		$this->inputRules['loadRegistryDoubleFilter'] = array_merge(
			$this->inputRules['loadRegistryDouble'],
			$this->inputRules['FilterGridPanel']
		);

		$this->inputRules['loadUnionRegistryData'] = array_merge(
			$this->inputRules['loadUnionRegistryData'],
			$this->inputRules['Filter']
		);

		$this->inputRules['loadUnionRegistryErrorTFOMS'] = array_merge(
			$this->inputRules['loadUnionRegistryErrorTFOMS'],
			$this->inputRules['Filter']
		);

		$this->inputRules['loadUnionRegistryErrorTFOMSFilter'] = array_merge(
			$this->inputRules['loadUnionRegistryErrorTFOMS'],
			$this->inputRules['FilterGridPanel']
		);
	}

	/**
	 * @param $file
	 * @param $data
	 * @return bool
	 */
	function sendMail($file, $data)
	{
		// Невозможно
		return false;
	}

	/**
	 * Изменение статуса счета-реестра
	 * Входящие данные: ID рееестра и значение статуса
	 * На выходе: JSON-строка
	 * Используется: форма просмотра реестра (счета)
	 */
	function setRegistryStatus()
	{
		$data = $this->ProcessInputData('setRegistryStatus', true);
		if ($data === false) { return false; }

		$outdata = array('Error_Msg' => '');

		foreach($data['Registry_ids'] as $Registry_id) {
			$data['Registry_id'] = $Registry_id;

			if ($this->dbmodel->checkRegistryInArchive($data)) {
				$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
				return false;
			}

			// В Уфе можно переводить в оплаченные и к оплате без данной проверки
			if (!in_array($data['RegistryStatus_id'], array(4, 2))) {
				if ($this->dbmodel->checkRegistryInGroupLink($data)) {
					$this->ReturnError('Реестр включён в реестр по СМО, все действия над реестром запрещены.');
					return false;
				}
			}

			if ($data['RegistryStatus_id'] == 3 && $this->dbmodel->checkRegistryIsBlocked($data)) {
				$this->ReturnError('Реестр заблокирован, запрещено менять статус на "В работе".');
				return false;
			}

			$response = $this->dbmodel->setRegistryStatus($data);
			$outdata = $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->GetOutData();
			if (!empty($outdata['Error_Msg'])) {
				$this->ReturnData($outdata);
				return false;
			}
		}

		$this->ReturnData($outdata);
	}

	/**
	 *  Task# Групповое исключение/восстановление записей реестра
	 */ 
	function deleteRegistryGroupData(){
		$this->load->model('Registry_model', 'dbmodel');
		
		$data = $this->ProcessInputData('deleteRegistryGroupData', true);

		if ($data === false) { return false; }
		
		$data['RegistryData_deleted'] = ($data['RegistryData_deleted']==1)?2:1;

		$response = $this->dbmodel->deleteRegistryGroupData($data);
	
		if (strlen($response[0]['Error_Msg']) != 0) {
			$result = array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
		} else {
			$result = array('success' => true);
		}
		$this->ReturnData($result);
	
		return true;
	}

	/**
	 * Получение списка данных объединённого реестра
	 */
	function loadUnionRegistryData()
	{
		$data = $this->ProcessInputData('loadUnionRegistryData', true);
		if ($data === false) { return false; }

		$data['RegistrySubType_id'] = 2;
		$response = $this->dbmodel->loadRegistryData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	public function loadUnionRegistryDataFilter()
	{
		$data = $this->ProcessInputData('loadRegistryDataFilter', true);
		if ($data === false) { return false; }

		$data['RegistrySubType_id'] = 2;
		$response = $this->dbmodel->loadRegistryDataFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportUnionRegistryToXmlCheckExist()
	{
		$data = $this->ProcessInputData('exportUnionRegistryToXmlCheckExist', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->GetRegistryXmlExport($data);
		if (is_array($res) && count($res) > 0) {
			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'inprogress'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0 && $res[0]['RegistryStatus_id'] == 4) // если уже выгружен реестр и оплаченный
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'onlyexists'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0)
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'exists'));
				return true;
			} else {
				$this->ReturnData(array('success' => true, 'exportfile' => 'empty'));
				return true;
			}
		} else {
			$this->ReturnError('Ошибка получения данных по реестру');
			return false;
		}
	}

	/**
	 * Удаление финального реестра
	 */
	function deleteUnionRegistry()
	{
		$data = $this->ProcessInputData('deleteUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Удаление финального реестра (с удалением случаев из предварительных реестров).
	 */
	function deleteUnionRegistryWithData()
	{
		$data = $this->ProcessInputData('deleteUnionRegistryWithData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistryWithData($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
	
	/**
	* функция для выгрузки данных реестра для сверки
	*/
	function exportRegistryErrorDataToDbf() {
		$data = $this->ProcessInputData('exportRegistryErrorDataToDbf', true);
		if ( $data === false ) { return false; }
		
		try {
			// данные о пациенте из реестра по ошибке: "Страховая организация указана не верно"
			$registry_person = $this->dbmodel->loadPersonInfoFromErrorRegistry($data);

			if ( $registry_person === false ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
				return false;
			}

			if ( !is_array($registry_person) || count($registry_person['data']) == 0 ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибок страховой по этому реестру нет в базе данных.')));
				return false;
			}

			// Формируем массив перс. данных с индексами, равными идентификатору застрахованного
			$personData = array();
			$evnData = array();

			foreach ( $registry_person['data'] as $array ) {
				$personData[$array['ID']] = $array;
			}

			$lpu_code = $registry_person['lpu_code'];

			unset($registry_person);

			switch ( $data['RegistryType_id'] ) {
				// данные по ошибочным движениям в стационаре
				case 1:
				case 14:
					$evnData = $this->dbmodel->loadEvnSectionErrorData($data);

					if ( $evnData === false ) {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
						return false;
					}

					if ( !is_array($evnData) || count($evnData) == 0 ) {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по движениям в стационаре с ошибками нет в базе данных.')));
						return false;
					}
				break;

				// данные по ошибочным посещениям
				case 2:
				case 6:
					$evnData = $this->dbmodel->loadEvnVizitErrorData($data);

					if ( $evnData === false ) {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
						return false;
					}

					if ( !is_array($evnData) || count($evnData) == 0 ) {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по посещениям с ошибками нет в базе данных.')));
						return false;
					}
				break;
			}
			
			$person_def = array(
				array( "ID",		"C",	36, 0 ),
				array( "FAM",		"C",	25, 0 ),
				array( "NAM",		"C",	25, 0 ),
				array( "FNAM",		"C",	25, 0 ),
				array( "DATE_BORN",	"D",	 8, 0 ),
				array( "SEX",		"C",	 1, 0 ),
				array( "DOC_TYPE",	"C",	 2, 0 ),
				array( "DOC_SER",	"C",	10, 0 ),
				array( "DOC_NUM",	"C",	16, 0 ),
				array( "INN",		"C",	12, 0 ),
				array( "KLADR",		"C",	19, 0 ),
				array( "HOUSE",		"C",	 5, 0 ),
				array( "ROOM",		"C",	 5, 0 ),
				array( "SMO",		"N",	 3, 0 ),
				array( "POL_NUM",	"C",	16, 0 ),
				array( "STATUS",	"N",	 2, 0 )
			);
			
			$evn_data_def = array(
				array( "ID_POS",	"C",	36, 0 ),
				array( "ID",		"C",	36, 0 ),
				array( "DATE_POS",	"D",	 8, 0 ),
				array( "SMO",		"C",	 3, 0 ),
				array( "POL_NUM",	"C",	16, 0 ),
				array( "ID_STATUS",	"C",	 2, 0 ),
				array( "NAM",		"C",	25, 0 ),
				array( "FNAM",		"C",	25, 0 ),
				array( "DATE_BORN",	"D",	 8, 0 ),
				array( "SEX",		"C",	 1, 0 ),
				array( "SNILS",		"C",	14, 0 ),
				array( "DATE_SV",	"D",	 8, 0 ),
				array( "FLAG",		"C",	 1, 0 )
			);
			
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "reerd_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);
			
			// данные о пациенте
			$file_reerd_sign = "patient";
			$file_reerd_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_reerd_sign . ".dbf";
			
			// данные о посещении
			$file_reerd_vizit_sign = "visit";
			$file_reerd_vizit_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_reerd_vizit_sign . ".dbf";

			// Начало цикла для формирования файлов
			$i = 0;
			$linkArray = array();
			$recordsLimit = 10000;

			$file_zip_sign = "Z" . $lpu_code;
			
			foreach ( $evnData as $row ) {
				if ( $i % $recordsLimit == 0 ) {
					// Если это не первый проход по циклу, то закрываем ссылки на dbf-файлы и формируем архив
					if ( $i > 0 ) {
						if ( !empty($evnDBF) ) {
							dbase_close($evnDBF);
						}

						if ( !empty($persDBF) ) {
							dbase_close($persDBF);
						}

						$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . "_" . (floor($i / $recordsLimit)) . ".zip";

						if ( file_exists($file_zip_name) ) {
							unlink($file_zip_name);
						}

						$zip = new ZipArchive();
						$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
						$zip->AddFile($file_reerd_name, $file_reerd_sign . ".dbf");
						$zip->AddFile($file_reerd_vizit_name, $file_reerd_vizit_sign . ".dbf");
						$zip->close();
						
						unlink($file_reerd_name);
						unlink($file_reerd_vizit_name);
						
						if ( file_exists($file_zip_name) ) {
							$linkArray[] = $file_zip_name;
						}
						else {
							throw new Exception('Ошибка создания архива');
						}
					}

					// Создаем новые файлы dbf
					$evnDBF = dbase_create($file_reerd_vizit_name, $evn_data_def);
					$persDBF = dbase_create($file_reerd_name, $person_def);

					// Список людей для отдельного visit.dbf
					$personArray = array();
				}

				$i++;

				foreach ( $evn_data_def as $descr ) {
					if ( $descr[1] == "C" ) {
						$row[$descr[0]] = str_replace('«', '"', $row[$descr[0]]);
						$row[$descr[0]] = str_replace('»', '"', $row[$descr[0]]);
					}
					else if ( $descr[1] == "D" ) {
						if ( !empty($row[$descr[0]]) ) {
							if ( $row[$descr[0]] == '31.12.9999' ) {
								$row[$descr[0]] = '99991231';
							}
						}
					}
				}

				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record($evnDBF, array_values($row));

				// Добавляем соответствующего пациента в patient.dbf
				if ( !in_array($personData[$row['ID']], $personArray) ) {
					$personArray[] = $personData[$row['ID']];

					$personRow = $personData[$row['ID']];

					if ( is_array($personRow) && count($personRow) > 0 ) {
						foreach ( $person_def as $descr ) {
							if ( $descr[1] == "C" ) {
								$personRow[$descr[0]] = str_replace('«', '"', $personRow[$descr[0]]);
								$personRow[$descr[0]] = str_replace('»', '"', $personRow[$descr[0]]);
							}
							else if ( $descr[1] == "D" ) {
								if ( !empty($personRow[$descr[0]]) ) {
									if ( $personRow[$descr[0]] == '31.12.9999' ) {
										$personRow[$descr[0]] = '99991231';
									}
								}
							}
						}
						
						array_walk($personRow, 'ConvertFromUtf8ToCp866');
						dbase_add_record($persDBF, array_values($personRow));
					}
				}
			}

			// Если после выхода из общего цикла последний zip-архив не сформирован, то формируем его
			if ( ($i > 0) && ($i % $recordsLimit != 0) ) {
				if ( !empty($evnDBF) ) {
					dbase_close($evnDBF);
				}

				if ( !empty($persDBF) ) {
					dbase_close($persDBF);
				}

				// Если это не первый проход по циклу, то формируем архив
				$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . "_" . (floor($i / $recordsLimit) + 1) . ".zip";

				if ( file_exists($file_zip_name) ) {
					unlink($file_zip_name);
				}

				$zip = new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile($file_reerd_name, $file_reerd_sign . ".dbf");
				$zip->AddFile($file_reerd_vizit_name, $file_reerd_vizit_sign . ".dbf");
				$zip->close();
					
				unlink($file_reerd_name);
				unlink($file_reerd_vizit_name);
					
				if ( file_exists($file_zip_name) ) {
					$linkArray[] = $file_zip_name;
				}
				else {
					throw new Exception('Ошибка создания архива');
				}
			}

			if ( count($linkArray) > 0 ) {
				$this->ReturnData(array('success' => true, 'Link' => $linkArray));
			}
			else {
				throw new Exception('Не создано ни одного файла');
			}
			/*
			// Старый вариант с одним файлом
			$h = dbase_create( $file_reerd_name, $person_def );
			foreach ($registry_person['data'] as $row)
			{
				// определяем которые даты и конвертируем их					
				foreach ($person_def as $descr)
				{
					if ( $descr[1] == "C" ) {
						$row[$descr[0]] = str_replace('«', '"', $row[$descr[0]]);
						$row[$descr[0]] = str_replace('»', '"', $row[$descr[0]]);
					}
					else if ( $descr[1] == "D" )
						if (!empty($row[$descr[0]]))
						{
							if ( $row[$descr[0]] == '31.12.9999' ) {
								$row[$descr[0]] = '99991231';
							}
							else {
								$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
							}
						}
				}
				
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record( $h, array_values($row) );
			}
			dbase_close ($h);
			
			$h = dbase_create( $file_reerd_vizit_name, $evn_data_def );
			foreach ($evn_data as $row)
			{
				// определяем которые даты и конвертируем их
				foreach ($evn_data_def as $descr)
				{
					if ( $descr[1] == "C" ) {
						$row[$descr[0]] = str_replace('«', '"', $row[$descr[0]]);
						$row[$descr[0]] = str_replace('»', '"', $row[$descr[0]]);
					}
					else if ( $descr[1] == "D" )
					{
						if (!empty($row[$descr[0]]))
						{
							if ( $row[$descr[0]] == '31.12.9999' ) {
								$row[$descr[0]] = '99991231';
							}
							else {
								$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
							}
						}
					}
				}
			
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record( $h, array_values($row) );
			}
			dbase_close ($h);
			
			$base_name = $_SERVER["DOCUMENT_ROOT"]."/";
			
			$file_zip_sign = "Z".$registry_person['lpu_code'];
			$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
			
			$zip=new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_reerd_name, $file_reerd_sign.".dbf" );
			$zip->AddFile( $file_reerd_vizit_name, $file_reerd_vizit_sign.".dbf" );
			$zip->close();
			
			unlink($file_reerd_name);
			unlink($file_reerd_vizit_name);
			
			if (file_exists($file_zip_name))
			{
				$link = $file_zip_name;
				echo "{'success':true,'Link':'$link'}";				
			}
			else{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка создания архива!')));
			}
			*/
		}
		catch (Exception $e)
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($e->getMessage())));
			return false;
		}
	}
	
	/**
	 * Функция формирует и выводит в поток вывода .dbf файлы, обернутые в архив.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: вывод в поток вывода .dbf файлов, обернутых в архив или сообщение об ошибке.
	 * Update 4 мая 2010 г. Night: поскольку результирующие Dbf для каждого типа реестра отличаются, 
	 * то будем использовать RegistryType_id (тип реестра), для определения, какие именно форматы Dbf использовать.
	 */
	function exportRegistryToDbf()
	{
		// Это решение, которое позволяет временно закрыть выгрузку реестров в Dbf
		/*
		if (!isSuperadmin()) {
			$this->ReturnError('Формирование dbf временно недоступно!');
			return false;
		}
		*/
		// Ошибка медленной работы базы
		
		$data = $this->ProcessInputData('exportRegistryToDbf', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->GetRegistryExport($data);
		
		if ( !is_array($res) || count($res) == 0 )  {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}

		if ( $res[0]['Registry_ExportPath'] == '1' ) {
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}
		// если уже выгружен реестр
		else if ( strlen($res[0]['Registry_ExportPath']) > 0 ) {
			$link = $res[0]['Registry_ExportPath'];
			echo "{'success':true,'Link':'$link'}";
			return true;
		}
		else {
			$type = $res[0]['RegistryType_id'];
		}
		
		// Формирование Dbf в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['Status'] = '1';
			$this->dbmodel->SetExportStatus($data);
			
			$registry_data_res = $this->dbmodel->loadRegistryDataForDbfUsing($type, $data);

			if ( $registry_data_res === false ) {
				$data['Status'] = '';
				$this->dbmodel->SetExportStatus($data);
				$this->ReturnError($this->error_deadlock);
				return false;
			}
			if ( !is_object($registry_data_res) ) {
				$data['Status'] = '';
				$this->dbmodel->SetExportStatus($data);
				$this->ReturnError('Данных по требуемому реестру нет в базе данных.');
				return false;
			}

			$reestr_data_def = array(
				array( "N_ZAP",	"N", 8, 0 ),
				array( "ID", "C", 36, 0 ),
				array( "FAM", "C", 40, 0 ),
				array( "NAM", "C", 40, 0 ),
				array( "FNAM", "C", 40, 0 ),
				array( "BDAY", "D", 8 , 0 ),
				array( "SEX", "N", 1 , 0 ),
				array( "STATUS", "N", 2 , 0 ),
				array( "POL_SER", "C", 10, 0 ),
				array( "POL_NUM", "C", 20, 0 ),
				array( "DOC_TYPE", "C", 2 , 0 ),
				array( "DOC_SER", "C", 10, 0 ), // 
				array( "DOC_NUM", "C", 20, 0 ), // 
				array( "INS_ID_AR",	"C", 5 , 0 ), // 
				array( "INS_ORG", "C", 100, 0 ), // 
				array( "INS_ID", "C", 5 , 0 ), // 
				array( "AREA", "C", 50, 0 ), // 
				array( "REGION", "C", 30, 0 ), // 
				array( "REG_CITY", "C", 30, 0 ), // 
				array( "ITEM", "C", 30, 0 ), // 
				array( "STREET", "C", 50, 0 ), // 
				array( "HOUSE", "N", 6 , 0 ), // 
				array( "LITER", "C", 5 , 0 ), // 
				array( "FLAT", "C", 5 , 0 ), // 
				array( "ZIP",  "N", 6 , 0 ), // 
				array( "IDSP",  "N", 2 , 0 ), // 
				array( "AMB_DEP", "N", 8, 0 ), // 
				array( "PROFIL", "N", 4, 0 ), // 
				array( "DEP", "N", 8, 0 ), // 
				array( "HISTORY", "C", 50, 0 ), // 
				array( "DATE_BEGIN", "D", 8 , 0 ), // 
				array( "DATE_END", "D", 8 , 0 ), // 
				array( "CODE_MES1", "C", 16, 0 ), // 
				array( "OUT", "N", 3, 0 ), // 
				array( "RSLT", "N", 3, 0 ), // 
				array( "DS0", "C", 10, 0 ), // 
				array( "MKB", "C", 10, 0 ), // 
				array( "DS2", "C", 10, 0 ), // 
				array( "TARIFF", "N", 15, 2 ), // 
				array( "SUM", "N", 15, 2 ), // 
				array( "DUR_FACT", "N", 3, 0 ), // 
				array( "REAN_D", "N", 3, 0 ), // 
				array( "OPER1", "C", 16, 0 ), // 
				array( "OPER2", "C", 16, 0 ), // 
				array( "OPER3", "C", 16, 0 ), // 
				array( "OPER4", "C", 16, 0 ), // 
				array( "OPER5", "C", 16, 0 ), // 
				array( "DATA_OP1", "D", 8 , 0 ), // 
				array( "DATA_OP2", "D", 8 , 0 ), // 
				array( "DATA_OP3", "D", 8 , 0 ), // 
				array( "DATA_OP4", "D", 8 , 0 ), // 
				array( "DATA_OP5", "D", 8 , 0 ), // 
				array( "ED_COL1", "N", 6, 2 ), // 
				array( "ED_COL2", "N", 6, 2 ), // 
				array( "ED_COL3", "N", 6, 2 ), // 
				array( "ED_COL4", "N", 6, 2 ), // 
				array( "ED_COL5", "N", 6, 2 ), // 
				array( "KSKP_COEF", "N", 5, 2 ), // 
				array( "KPG", "N", 5, 0), // 
				array( "KSG", "N", 10, 0), // 
				array( "VID_HMP", "C", 20, 0), // 
				array( "METOD_HMP", "C", 20, 0), // 
				array( "MP_FIO", "C", 50, 0), // 
				array( "VRACH1", "C", 50, 0), // 
				array( "VRACH2", "C", 50, 0), // 
				array( "VRACH3", "C", 50, 0), // 
				array( "VRACH4", "C", 50, 0), // 
				array( "VRACH5", "C", 50, 0), // 
				array( "VOLUME", "C", 50, 0), // 
				array( "BAD_VOL", "N", 1, 0), // 
				array( "OPLATA", "N", 1, 0), // 
			);

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_".time()."_".$data['Registry_id'];
			mkdir( EXPORTPATH_REGISTRY.$out_dir );
			
			// файл-тело реестра
			$file_re_data_sign = "REG_LPU1";
			$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".dbf";
			
			// выгрузка тела
			$h = dbase_create( $file_re_data_name, $reestr_data_def );
			$row_number = 1;
			$row_number_hash = array();

			//Получаем данные результата по строкам
			while( $row = $registry_data_res->_fetch_assoc()) {
				// определяем которые даты и конвертируем их
				foreach ( $reestr_data_def as $descr ) {
					if ( $descr[1] == "C" ) {
						$row[$descr[0]] = str_replace('«', '"', $row[$descr[0]]);
						$row[$descr[0]] = str_replace('»', '"', $row[$descr[0]]);
					}
					else if ( $descr[1] == "D" ) {
						if (!empty($row[$descr[0]])) {
							// КОСТЫЛЬ by Savage:
							// strtotime не понимает дату '31.12.9999', поэтому подсовываем явно строку 99991231, если в запросе пришла дата 31.12.9999
							// Грамотный вариант реализации: в запросе на получение данных конвертировать даты сразу в формат 112 вместо 104
							if ( $row[$descr[0]] == '31.12.9999' ) {
								$row[$descr[0]] = '99991231';
							}
							else {
								$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
							}
						}
					}
				}
				
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record( $h, array_values($row) );
			}

			dbase_close ($h);
			
			// Сформированные файлы reg_lpu.dbf и lpu_info.dbf (подробная структура – в приложении) 
			// упаковываются в zip-архив вида pXXXXXXX.zip, где XXXXXXX – код подразделения ЛПУ.
			$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".zip";
			
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign.".dbf" );
			$zip->close();
			
			unlink($file_re_data_name);
			
			if ( file_exists($file_zip_name) ) {
				$link = $file_zip_name;
				echo "{'success':true,'Link':'$link'}";
				$data['Status'] = $file_zip_name;
				$this->dbmodel->SetExportStatus($data);
			}
			else {
				$this->ReturnError('Ошибка создания архива реестра!');
			}
			
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->dbmodel->SetExportStatus($data);
			$this->ReturnError($e->getMessage());
		}
	}
	
	
	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportRegistryToXml() {
		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryToXml($data);
		$this->ProcessModelSave($response, true, 'Ошибка при экспорте реестров')->ReturnData();
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportUnionRegistryToXml() {
		$this->exportRegistryToXml();
	}

	/**
	 * Подписание реестра
	 */
	function signRegistry()
	{
		$data = $this->ProcessInputData('signRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->signRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при подписании реестра')->ReturnData();
	}
	
	/**
	* #Task# по плану 1.11 добавление сведений в вкладку 0 реестра: кол-во койко-мест, сумма принятая, сумма не принятая
	*/
	function getMoreInfoRegistry(){

		$data = $this->ProcessInputData('getMoreInfoRegistry', true);
		if ($data === false) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при получении койко-мест, сумм (принятая, непринятая)!')));
		}
		
		$moreInfo = $this->dbmodel->getMoreInfoRegistry($data);
		//var_dump($moreInfo);
		if (is_array($moreInfo) && count($moreInfo) > 0){
			array_walk_recursive($moreInfo, 'ConvertFromWin1251ToUTF8');  

			$this->ReturnData(array('success' => true, 'data'=>$moreInfo));
			//return array('success'=>true, 'data'=>$moreInfo);
		}
		else 
		{   
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка подсчёта койко-мест, принятой и не принятой суммы')));
			return;
		}
		/*
		if ( !is_object($registry_data_res) || !$registry_data_res->has_rows() )
		{
			$data['Status'] = '';
			$this->dbmodel->SetExportStatus($data);
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по требуемому реестру нет в базе данных.')));
			return false;
		}
		*/  
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных для проверки объемов.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportRegistryToXmlCheckVolume()
	{	
		$data = $this->ProcessInputData('exportRegistryToDbf', true);
		if ($data === false) { return false; }

		$type = 0;
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		// нафиг проверять
		$res = $this->dbmodel->GetRegistryXmlExport($data);
		
		if (is_array($res) && count($res) > 0) 
		{
			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).')));
				return;
			}
			elseif (strlen($res[0]['Registry_xmlExportPath'])>0) // если уже выгружен реестр
			{
				$link = $res[0]['Registry_xmlExportPath'];
				echo "{'success':true,'Link':'$link'}";
				return;
			}
			else 
			{
				$type = $res[0]['RegistryType_id'];
			}
		}
		else 
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.')));
			return;
		}
		
		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try
		{
			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);
			
			set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
			// RegistryData 
			$registry_data_res = $this->dbmodel->loadRegistryDataForXmlCheckVolumeUsing($type, $data);
			if ($registry_data_res === false)
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
				return false;
			}
			if ( empty($registry_data_res) )
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по требуемому реестру нет в базе данных.')));
				return false;
			}
			
			$this->load->library('parser');
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml_check_volume/registry_ufa_pl', $registry_data_res, true);
			reset($registry_data_res);
										
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_check_volume_".time()."_".$data['Registry_id'];
			mkdir( EXPORTPATH_REGISTRY.$out_dir );
							
			// файл-тело реестра
			$file_re_data_sign = $registry_data_res['lpu_code'] . '_' . date('Y_m') . '_' . count($registry_data_res['registry_data']) . "_2";
			$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";				
			
			file_put_contents($file_re_data_name, $xml);
			
			$base_name = $_SERVER["DOCUMENT_ROOT"]."/";
			
			$file_zip_sign = $file_re_data_sign;
			$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
			
			$zip=new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
			$zip->close();
			
			unlink($file_re_data_name);
			
			if (file_exists($file_zip_name))
			{
				$link = $file_zip_name;
				echo "{'success':true,'Link':'$link'}";					
				$data['Status'] = $file_zip_name;
				$this->dbmodel->SetExportStatus($data);
			}
			else{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка создания архива реестра!')));
			}
			
		}
		catch (Exception $e)
		{
			$data['Status'] = '';
			$this->dbmodel->SetExportStatus($data);
			$this->ReturnData(array('success' => false, 'Error_Msg' => $this->error_deadlock));
		}
	}
	
	/**
	 * Импорт реестра из DBF
	 */
	function importRegistryFromDbf()
	{
		
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar');
		$dbffile = "REG_LPU.DBF";
		$dbfhfile = "LPU_INFO.DBF";
		
		$data = $this->ProcessInputData('importRegistryFromDbf', true);
		if ($data === false) { return false; }
		
		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл реестра!') ) );
			return false;
		}
		
		if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
			switch($error)
			{
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array($file_data['file_ext'], $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}

		
		
		$zip = new ZipArchive;
		if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) 
		{
			$zip->extractTo( $upload_path );
			$zip->close();
		}
		unlink($_FILES["RegistryFile"]["tmp_name"]);
		// там должен быть файл REG_LPU.DBF
		if ((!file_exists($upload_path.$dbffile)) || (!file_exists($upload_path.$dbfhfile)))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}
		
		$recall = 0;
		$recerr = 0;
		
		$h = dbase_open($upload_path.$dbffile, 0);
		if ($h) 
		{
			// Определяем номер реестра из файла - нужно будет доделать
			$hd = dbase_open($upload_path.$dbfhfile, 0);
			$datah = array('ID_SMO'=>null,'ID_SUBLPU'=>null,'DATE_BEG'=>null,'DATE_END'=>null);
			if ($hd)
			{
				$r = dbase_numrecords($hd);
				for ($i=1; $i <= $r; $i++) 
				{
					$rech = dbase_get_record_with_names($hd, $i);
					$datah['ID_SMO'] = iconv("cp866","cp1251",trim($rech['ID_SMO']));
					$datah['ID_SUBLPU'] = iconv("cp866","cp1251",trim($rech['ID_SUBLPU']));
					$datah['DATE_BEG'] = iconv("cp866","cp1251",date("d.m.Y",strtotime(trim($rech['DATE_BEG']))));
					$datah['DATE_END'] = iconv("cp866","cp1251",date("d.m.Y",strtotime(trim($rech['DATE_END']))));
				}
				// Дальше запрос к базе и определение реестра 
				dbase_close ($hd);
			}
			
			// Удаляем ответ по этому реестру, если он уже был загружен
			$rr = $this->dbmodel->deleteRegistryError($data);
			// Всего записей в пришедшем реестре 
			$Rec_Count = dbase_numrecords($h);
			for ($i=1; $i <= $Rec_Count; $i++) 
			{
				$rec = dbase_get_record_with_names($h, $i);
				$d = array();
				/*
				foreach($rec as $k=>&$v)
				{
					if ($v=='')
					{
						$d[$k] = null;
					}
					else
					{
						$d[$k] = iconv("cp866","cp1251",$v);
					}
				}
				*/
				$d['ID'] = iconv("cp866","cp1251",trim($rec['ID']));
				$dstr = iconv("cp866","cp1251",trim($rec['FLAG']));
				if (strlen($dstr)>0)
				{
					// Залить в базу
					$dd = explode(',', $dstr);
					$rr = count($dd);
					if ($rr>0)
					{
						for ($ii=0; $ii < $rr; $ii++) 
						{
							$d['FLAG'] = $dd[$ii];
							$response = $this->dbmodel->setErrorFromImportRegistry($d,$data);
							if (!is_array($response)) 
							{
								$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!')));
								return false;
							}
						}
						// 
					}
					$recerr++; // Записей с ошибками
				}
				$recall++; // Всего загружено записей
			}
			if ($recall>0)
			{
				$rs = $this->dbmodel->setVizitNotInReg($data);
			}
			
			// Залили
			// После этого надо вывести количество обработанных записей всего 
			// записей с ошибками
			// и разнести их нормально по записям отображая ошибки ()
			dbase_close ($h);
			/*
			unlink($upload_path.$dbffile);
			unlink($upload_path.$dbfhfile);
			*/
			
			// Пишем информацию об импорте в историю
			$data['Registry_RecordCount'] = $recall;
			$data['Registry_ErrorCount'] = $recerr;
			$this->dbmodel->dumpRegistryInformation($data, 3);

			$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'dateBeg'=>$datah['DATE_BEG'], 'dateEnd'=>$datah['DATE_END'], 'Message' => toUTF('Реестр успешно загружен.')));
			return true;
		}
		else
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка чтения dbf!')));
			return false;
		}
	}
	
	
	/**
	 * Импорт реестра из XML
	 */
	function importRegistryFromXml() {
		$this->load->library('textlog', array('file' => 'importRegistryFromXml_' . date('Y-m-d') . '.log', 'logging' => isSuperadmin()));
		$this->textlog->add('');
		$this->textlog->add('Запуск');

		try {
			$this->dbmodel->beginTransaction();

			$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
			$allowed_types = explode('|','zip|rar|xml');
			
			$data = $this->ProcessInputData('importRegistryFromXml', true);
			if ($data === false) { return false; }
			
			if ( !isset($_FILES['RegistryFile']) ) {
				throw new Exception('Не выбран файл реестра!', __LINE__);
			}
			
			if ( !is_uploaded_file($_FILES['RegistryFile']['tmp_name']) ) {
				$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];

				switch ( $error ) {
					case 1:
						$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
						break;
					case 2:
						$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
						break;
					case 3:
						$message = 'Этот файл был загружен не полностью.';
						break;
					case 4:
						$message = 'Вы не выбрали файл для загрузки.';
						break;
					case 6:
						$message = 'Временная директория не найдена.';
						break;
					case 7:
						$message = 'Файл не может быть записан на диск.';
						break;
					case 8:
						$message = 'Неверный формат файла.';
						break;
					default :
						$message = 'При загрузке файла произошла ошибка.';
						break;
				}

				throw new Exception($message, __LINE__);
			}
			
			// Тип файла разрешен к загрузке?
			$x = explode('.', $_FILES['RegistryFile']['name']);
			$file_data['file_ext'] = strtolower(end($x));

			if ( !in_array($file_data['file_ext'], $allowed_types) ) {
				throw new Exception('Данный тип файла не разрешен.', __LINE__);
			}

			// Правильно ли указана директория для загрузки?
			if ( !@is_dir($upload_path) ) {
				mkdir($upload_path);
			}

			if ( !@is_dir($upload_path) ) {
				throw new Exception('Путь для загрузки файлов некорректен.', __LINE__);
			}
			
			// Имеет ли директория для загрузки права на запись?
			if ( !is_writable($upload_path) ) {
				throw new Exception('Загрузка файла не возможна из-за прав пользователя.', __LINE__);
			}

			// получаем данные реестра
			$this->textlog->add('Получаем данные реестра...');
			$registrydata = $this->dbmodel->loadRegistryForImportXml($data);

			if ( !is_array($registrydata) || !isset($registrydata[0]) ) {
				throw new Exception('Ошибка чтения данных реестра', __LINE__);
			}

			$this->textlog->add('... получили');

			$registrydata = $registrydata[0];

			$data['OrgSmo_id'] = $registrydata['OrgSmo_id'];
			$data['RegistrySubType_id'] = $registrydata['RegistrySubType_id'];
			$data['Registry_IsNotInsur'] = $registrydata['Registry_IsNotInsur'];
			$data['RegistryType_id'] = $registrydata['RegistryType_id'];

			$Registry_isNew = ($registrydata['Registry_IsNew'] == 2) ? true : false;

			if ( $file_data['file_ext'] == 'xml' ) {
				$xmlfile = $_FILES['RegistryFile']['name'];

				$this->textlog->add('Перемещаем загруженный файл...');

				if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile) ){
					throw new Exception('Не удаётся переместить файл.', __LINE__);
				}

				$this->textlog->add('... переместили');
			}
			else {
				if ( $Registry_isNew ) {
					$mask = '/^(H|C|DV|DP|DS|T|DO|DOV|DON)M.*xml/i';
				}
				else {
					// там должен быть файл HM*.xml, если его нет -> файл не является архивом реестра
					// обновлено: для ВМП - HTM*.hml, для полки, стаца и СМП - HYM*.xml
					switch ($data['RegistryType_id']) {
						case 1:
						case 2:
						case 6:
							$mask = '/HY?M.*xml/i';
							break;
						case 7:
						case 9:
						case 17:
							$mask = '/HD?M.*xml/i';
							break;
						case 14:
							$mask = '/HT?M.*xml/i';
							break;
						default:
							$mask = '/HM.*xml/i';
							break;
					}
				}

				$zip = new ZipArchive();

				if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
					$xmlfile = "";

					for ( $i = 0; $i < $zip->numFiles; $i++ ) {
						$filename = $zip->getNameIndex($i);

						if ( preg_match($mask, $filename) > 0 ) {
							$xmlfile = $filename;
						}
					}

					$this->textlog->add('Распаковываем архив...');

					$zip->extractTo( $upload_path );
					$zip->close();

					$this->textlog->add('... распаковали');
				}

				unlink($_FILES["RegistryFile"]["tmp_name"]);
			}

			if ( empty($xmlfile) ) {
				throw new Exception('Файл не является архивом реестра.', __LINE__);
			}
			
			// Счётчики
			$recall = 0;
			$recerr = 0;

			// Удаляем ответ по этому реестру, если он уже был загружен
			$this->textlog->add('Удаляем ответ по этому реестру, если он уже был загружен...');
			$rr = $this->dbmodel->deleteRegistryErrorTFOMS($data);
			$this->textlog->add('... удалили');

			libxml_use_internal_errors(true);

			// Читаем XML-файл
			$this->textlog->add('Читаем XML-файл...');
			$xmlString = file_get_contents($upload_path.$xmlfile);
			$xml = new SimpleXMLElement($xmlString);
			unset($xmlString);
			$this->textlog->add('... прочитали');

			if (
				!is_object($xml) || !property_exists($xml, 'SCHET')
				|| !property_exists($xml->SCHET, 'CODE') || strlen($xml->SCHET->CODE) == 0
				|| !property_exists($xml->SCHET, 'NSCHET') || strlen($xml->SCHET->NSCHET) == 0
			) {
				throw new Exception('XML файл не является файлом реестра.', __LINE__);
			}

			// Проверяем соответсвие шапки реестра
			$CODE = (string) $xml->SCHET->CODE;
			if ( $registrydata['Registry_id'] != trim($CODE) ) {
				throw new Exception('Не совпадает идентификатор реестра и импортируемого файла, импорт не произведен', __LINE__);
			}

			$NSCHET = (string) $xml->SCHET->NSCHET;
			if ( trim($registrydata['Registry_Num']) != trim($NSCHET) ) {
				throw new Exception('Не совпадает номер реестра и импортируемого файла, импорт не произведен', __LINE__);
			}

			$DSCHET = (string) $xml->SCHET->DSCHET;
			if ( $registrydata['Registry_accDate'] != date('d.m.Y',strtotime(trim($DSCHET))) ) {
				throw new Exception('Не совпадает дата реестра и импортируемого файла, импорт не произведен', __LINE__);
			}

			if ( $Registry_isNew ) {
				// Идём по случаям
				$this->textlog->add('Идём по случаям');

				foreach ( $xml->ZAP as $onezap ) {
					// Идём по законченным случаям
					foreach ( $onezap->Z_SL as $onezsl ) {
						// Идём по случаям
						foreach ( $onezsl->SL as $onesl ) {
							if (!empty($onesl->SL_ID)) {
								$SL_ID = (string)$onesl->SL_ID;
								$NHISTORY = (string)$onesl->NHISTORY;

								$recall++;

								$data['SL_ID'] = $SL_ID;

								$this->textlog->add('Проверяем соотвествие SL_ID реестру...');
								$check = $this->dbmodel->checkErrorDataInRegistry($data);
								if ( !$check ) {
									throw new Exception('Идентификатор SL_ID = "' . $SL_ID . '" для случая № "' . $NHISTORY . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', __LINE__);
								}
								$this->textlog->add('... проверили');

								// ошибка на уровне случая
								if ( !empty($onesl->SANK) ) {
									$recerr++; // записей с ошибками

									foreach ( $onesl->SANK as $onesank ) {
										$S_OSN = (string)$onesank->S_OSN;
										$S_COM = (string)$onesank->S_COM;

										$this->textlog->add('Добавляем ошибку (SL->SANK)...');

										$d = array(
											'FLAG' => $S_OSN,
											'SL_ID' => $SL_ID,
											'COMMENTS' => $S_COM
										);
										$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($d, $data);
										$this->textlog->add('... добавили (SL->SANK)');

										if ( !is_array($response) ) {
											throw new Exception('Ошибка при обработке реестра!', __LINE__);
										}
										elseif ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
											throw new Exception($response[0]['Error_Msg'], __LINE__);
										}
									}
								}

								// ошибка на уровне законченного случая
								if ( !empty($onezap->Z_SL->SANK) ) {
									$recerr++; // записей с ошибками

									foreach ( $onezap->Z_SL->SANK as $onesank ) {
										$S_OSN = (string)$onesank->S_OSN;
										$S_COM = (string)$onesank->S_COM;

										$this->textlog->add('Добавляем ошибку (Z_SL->SANK)...');

										$d = array(
											'FLAG' => $S_OSN,
											'SL_ID' => $SL_ID,
											'COMMENTS' => $S_COM
										);
										$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($d, $data);

										if ( !is_array($response) ) {
											throw new Exception('Ошибка при обработке реестра!', __LINE__);
										}
										elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
											throw new Exception($response[0]['Error_Msg'], __LINE__);
										}

										$this->textlog->add('... добавили (Z_SL->SANK)');
									}
								}
							}
						}
					}
				}

				$this->textlog->add('Обработали все случаи');
			}
			else {
				// Идём по случаям
				$this->textlog->add('Идём по случаям');

				foreach ( $xml->ZAP as $onezap ) {
					if ( !empty($onezap->SLUCH->IDCASE) ) {
						$IDCASE = (string)$onezap->SLUCH->IDCASE;
						$NHISTORY = (string)$onezap->SLUCH->NHISTORY;

						$recall++;

						if ( $recerr == 0 ) {
							$data['IDCASE'] = $IDCASE;

							// если ещё не добавляли ошибок к реестру, то проверяем соотвествие параметра реестру
							$this->textlog->add('Проверяем соотвествие IDCASE реестру...');
							$check = $this->dbmodel->checkErrorDataInRegistry($data);
							if ( !$check ) {
								throw new Exception('Идентификатор IDCASE = "' . $IDCASE . '" для случая № "' . $NHISTORY . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', __LINE__);
							}
							$this->textlog->add('... проверили');
						}

						if ( !empty($onezap->SLUCH->SANK) ) {
							$recerr++; // записей с ошибками

							foreach ( $onezap->SLUCH->SANK as $onesank ) {
								$S_OSN = (string)$onesank->S_OSN;
								$S_COM = (string)$onesank->S_COM;

								$this->textlog->add('Добавляем ошибку (ZAP->SLUCH->SANK)...');

								$d = array(
									'FLAG' => $S_OSN,
									'IDCASE' => $IDCASE,
									'COMMENTS' => $S_COM
								);
								$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($d, $data);

								if ( !is_array($response) ) {
									throw new Exception('Ошибка при обработке реестра!', __LINE__);
								}
								elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
									throw new Exception($response[0]['Error_Msg'], __LINE__);
								}

								$this->textlog->add('... добавили (ZAP->SLUCH->SANK)');
							}
						}
					}
				}

				$this->textlog->add('Обработали все случаи');
			}

			if ( $recall > 0 ) {
				$this->textlog->add('Запуск setVizitNotInReg...');
				$rs = $this->dbmodel->setVizitNotInReg($data);
				$this->textlog->add('... выполнено');
			}
				
			// Пишем информацию об импорте в историю
			$data['Registry_RecordCount'] = $recall;
			$data['Registry_ErrorCount'] = $recerr;
			$this->textlog->add('Пишем информацию об импорте в историю...');
			$this->dbmodel->dumpRegistryInformation($data, 3);
			$this->textlog->add('Выполнено...');

			$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll' => $recall, 'recErr' => $recerr, 'Message' => 'Реестр успешно загружен.'));

			$this->dbmodel->commitTransaction();

			$this->textlog->add('Метод успешно выполнен');
		}
		catch ( Exception $e ) {
			$this->dbmodel->rollbackTransaction();
			$this->textlog->add('Выход с ошибкой (' . $e->getCode() . '): ' . $e->getMessage());
			$this->ReturnError($e->getMessage(), $e->getCode());
		}

		return true;
	}
	
	
	/**
	* #Task# по плану 1.11 Установка отчётного месяца/года + номера пачки реестра
	*/
	function updateRegistryTwoCols(){
		$data = $this->ProcessInputData('updateRegistryTwoCols', true);
		
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->updateRegistryTwoCols($data);

		if(!$response){
			return $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg'])));
		}
		return $this->ReturnData(array('success' =>true));
		
	}

	/**
	 *  Функция возвращает запись/записи реестра
	 *  Входящие данные: _POST['Registry_id'],
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования реестра (счета)
	 */
	function loadUnionRegistry()
	{
		$data = $this->ProcessInputData('loadUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistry($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Импорт данных СМО реестра из DBF
	 */
	function importRegistrySmoDataFromDbf()
	{
		$this->load->database('default', false);
		$this->load->model($this->model_name, 'dbmodel');

		
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$allowed_types = explode('|','zip|rar');
		$dbfpatfile = "patient.dbf";
		$dbfprotofile = "lpu_rep.dbf";
		$dbfvizfile = "visit.dbf";
		$upload_path = './' . IMPORTPATH_ROOT . $_SESSION['lpu_id'] . '/';

		$data = $this->ProcessInputData('importRegistrySmoDataFromDbf', true);
		if ($data === false) { return false; }
		
		if ( !isset($_FILES['RegistryFile']) ) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл данных!') ) );
			return false;
		}
		
		if ( !is_uploaded_file($_FILES['RegistryFile']['tmp_name']) ) {
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];

			switch ( $error ) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
				break;

				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
				break;

				case 3:
					$message = 'Этот файл был загружен не полностью.';
				break;

				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
				break;

				case 6:
					$message = 'Временная директория не найдена.';
				break;

				case 7:
					$message = 'Файл не может быть записан на диск.';
				break;

				case 8:
					$message = 'Неверный формат файла.';
				break;

				default :
					$message = 'При загрузке файла произошла ошибка.';
				break;
			}

			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);

		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}


		$zip = new ZipArchive();

		if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
			$zip->extractTo( $upload_path );
			$zip->close();
		}

		unlink($_FILES["RegistryFile"]["tmp_name"]);

		if ( (!file_exists($upload_path.$dbfpatfile)) || (!file_exists($upload_path.$dbfvizfile)) || (!file_exists($upload_path.$dbfprotofile)) ) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом исправленных данных.')));
			return false;
		}
		
		$recall = 0;
		$recerr = 0;
		$recupd = 0;
		
		$h = dbase_open($upload_path . $dbfvizfile, 0);

		if ( $h ) {
			$personData = array();
			$r = dbase_numrecords($h);

			$cnt = 0;

			for ( $i = 1; $i <= $r; $i++ ) {
				$rech = dbase_get_record_with_names($h, $i);

				foreach ( $rech as $key => $value ) {
					$rech[$key] = trim($rech[$key]);
				}

				if ( /*!in_array($rech['ID'], $personData) &&*/ $rech['SMO'] != "NO" ) {
					//$personData[] = $rech['ID'];

					array_walk($rech, 'ConvertFromWin866ToCp1251');
					/*
					switch ( $rech['ID_STATUS'] ) {
						case 1:
						case 2:
							$rech['ID_STATUS'] = 3;
						break;

						case 3:
							$rech['ID_STATUS'] = 4;
						break;

						case 4:
							$rech['ID_STATUS'] = 1;
						break;

						case 5:
						case 6:
						case 8:
							$rech['ID_STATUS'] = 5;
						break;

						case 7:
							$rech['ID_STATUS'] = 2;
						break;
					}
					*/
					switch ( $rech['SEX'] ) {
						case 'м':
						case 'М':
							$rech['SEX'] = 1;
						break;

						case 'ж':
						case 'Ж':
							$rech['SEX'] = 2;
						break;

						default:
							$rech['SEX'] = NULL;
						break;
					}

					if ( !empty($rech['SNILS']) ) {
						$rech['SNILS'] = str_replace(' ', '', str_replace('-', '', $rech['SNILS']));
					}

					$rs = $this->dbmodel->savePersonData(array_merge($data, $rech));
					$recall++;

					if ( is_array($rs) && count($rs) > 0 ) {
						if ( is_array($rs[0]) && array_key_exists('Error_Msg', $rs[0]) ) {
							if ( !empty($rs[0]['Error_Msg']) ) {
								$recerr++;
							}
							else {
								$cnt++;
							}
						}
						else {
							$recerr++;
						}
					}
					else {
						$recerr++;
					}

					if ( $cnt == 100 || $i == $r ) {
						// Запуска процесса обработки загруженных данных
						$response = $this->dbmodel->updatePersonErrorData($data);

						if ( $response === false ) {
							$this->ReturnData( array('success' => false, 'Error_Code' => 100027 , 'Error_Msg' => toUTF('Ошибка при выполнении обработки загруженных данных.')));
							return false;
						}

						$cnt = 0;
						$recupd += (!empty($response[0]['CountUpd']) ? $response[0]['CountUpd'] : 0);
					}
				}
			}

			dbase_close($h);

			if ( $recupd > 0 ) {
				$this->db = null;
				$this->load->database('registry');

				$resposne = $this->dbmodel->setRegistryIsNeedReform(array(
					 'Registry_id' => $data['Registry_id']
					,'Registry_IsNeedReform' => 2
					,'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		$this->ReturnData(array('success' => true, 'recAll' => $recall, 'recUpd' => $recupd, 'recErr' => $recerr, 'Message' => toUTF('Данные обработаны.')));
		return true;
	}


	/**
	 * Печать реестра
	 */
	function printRegistry() {
		$this->load->library('parser');

		$template = '';

		$data = $this->ProcessInputData('printRegistry', true);
		if ($data === false) { return false; }

		// Получаем данные по счету
		$response = $this->dbmodel->getRegistryFields($data);

		if ( (!is_array($response)) || (count($response) == 0) || (!isset($response['Registry_Sum'])) ) {
			echo 'Ошибка при получении данных по счету';
			return true;
		}
		else {
			switch ( $response['RegistryType_Code'] ) {
				case 2:
					$template = 'print_registry_account_polka';
				break;

				case 4:
					$template = 'print_registry_account_dopdisp';
				break;

				case 5:
					$template = 'print_registry_account_orpdisp';
				break;
				
				case 6:
					$template = 'print_registry_account_smp';
				break;
				
				case 7:
					$template = 'print_registry_account_dopdispfirst';
				break;

				case 8:
					$template = 'print_registry_account_dopdispsecond';
				break;

				case 9:
					$template = 'print_registry_account_orpdispfirst';
				break;

				case 10:
					$template = 'print_registry_account_orpdispsecond';
				break;
				
				case 11:
					$template = 'print_registry_account_profsurvey';
				break;
				
				default:
					echo 'По данному реестру счет невозможно получить - функционал находится в разработке!';
					return true;
				break;
			}
		}

		$m = new money2str();
		$registry_sum_words = trim($m->work(number_format($response['Registry_Sum'], 2, '.', ''), 2));
		$registry_sum_words = strtoupper(substr($registry_sum_words, 0, 1)) . substr($registry_sum_words, 1, strlen($registry_sum_words) - 1);

		$month = array(
			'январе',
			'феврале',
			'марте',
			'апреле',
			'мае',
			'июне',
			'июле',
			'августе',
			'сентябре',
			'октябре',
			'ноябре',
			'декабре'
		);

		//array_walk($month, 'ConvertFromUTF8ToWin1251');

		$parse_data = array(
			'Lpu_Account' => isset($response['Lpu_Account']) ? $response['Lpu_Account'] : '&nbsp;',
			'Lpu_Address' => isset($response['Lpu_Address']) ? $response['Lpu_Address'] : '&nbsp;',
			'Lpu_Director' => isset($response['Lpu_Director']) ? $response['Lpu_Director'] : '&nbsp;',
			'Lpu_GlavBuh' => isset($response['Lpu_GlavBuh']) ? $response['Lpu_GlavBuh'] : '&nbsp;',
			'Lpu_INN' => isset($response['Lpu_INN']) ? $response['Lpu_INN'] : '&nbsp;',
			'Lpu_KPP' => isset($response['Lpu_KPP']) ? $response['Lpu_KPP'] : '&nbsp;',
			'Lpu_Name' => isset($response['Lpu_Name']) ? $response['Lpu_Name'] : '&nbsp;',
			'Lpu_OKPO' => isset($response['Lpu_OKPO']) ? $response['Lpu_OKPO'] : '&nbsp;',
			'Lpu_OKVED' => isset($response['Lpu_OKVED']) ? $response['Lpu_OKVED'] : '&nbsp;',
			'Lpu_Phone' => isset($response['Lpu_Phone']) ? $response['Lpu_Phone'] : '&nbsp;',
			'LpuBank_BIK' => isset($response['LpuBank_BIK']) ? $response['LpuBank_BIK'] : '&nbsp;',
			'LpuBank_Name' => isset($response['LpuBank_Name']) ? $response['LpuBank_Name'] : '&nbsp;',
			'Month' => isset($response['Registry_Month']) && isset($month[$response['Registry_Month'] - 1]) ? $month[$response['Registry_Month'] - 1] : '&nbsp;',
			'Registry_accDate' => isset($response['Registry_accDate']) ? $response['Registry_accDate'] : '&nbsp;',
			'Registry_Num' => isset($response['Registry_Num']) ? $response['Registry_Num'] : '&nbsp;',
			'Registry_Sum' => isset($response['Registry_Sum']) ? number_format($response['Registry_Sum'], 2, '.', ' ') : '&nbsp;',
			'Registry_Sum_Words' => $registry_sum_words,
			'Year' => isset($response['Registry_Year']) ? $response['Registry_Year'] : '&nbsp;'
		);

		$this->parser->parse($template, $parse_data);

		return true;
	}

	/**
	 * Удаление реестра
	 */
	function deleteRegistry() 
	{
		$this->load->model('Utils_model', 'umodel');
		
		$data = $this->ProcessInputData('deleteRegistry', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->checkRegistryInGroupLink(array('Registry_id' => $data['id']))) {
			$this->ReturnError('Реестр включён в реестр по СМО, удаление невозможно.');
			return false;
		}

		$result = $this->checkDeleteRegistry($data);
		if (!$result) {
			$this->ReturnError('Извините, удаление реестра невозможно!');
			return false;
		}

		$sch = "r2";
		$object = "Registry";
		$id = $data['id'];
		
		$response = $this->umodel->ObjectRecordDelete($data, $object, false, $id, $sch);
		if (isset($response[0]['Error_Message'])) { $response[0]['Error_Msg'] = $response[0]['Error_Message']; } else { $response[0]['Error_Msg'] = ''; }
		
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
	/**
	 * Получение данных из справочников корректировки объёмов
	 */ 
	function getRegistryLimitVolumeData()
	{
		$data = $this->ProcessInputData('RegistryLimitVolumeData', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->getRegistryLimitVolumeData($data);
		
		$result = $this->ReturnData($res);
		/*
		if (strlen($res[0]['Error_Msg']) != 0) {
			$result = array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
		} else {
			$result = array('success' => true);
		}
		$this->ReturnData($result);
	
		return true;
		*/
	}
	 /**
	 * Корректировка объёма
	 */
	function cutVolumeRegisters(){
		$data = $this->ProcessInputData('cutVolumeRegisters', true);
		if ($data === false) { return false; }
		
		//echo '<pre>' . print_r(json_decode($data['json'],1)) . '</pre>';
		$json_params =json_decode($data['json'],1);
		
		if(empty($json_params)){
			$this->ReturnData(array('success' => false, 'Message' => toUTF('Ошибка передачи параметров для корректировки объёмов')));
			return false;
		}  
  
		$res = $this->dbmodel->cutVolumeRegisters($data);
		$result = $this->ReturnData($res);  
	}
	/**
	 * Task#25768 Ручной запуск МЭК
	 */
	function startMek(){
		$data = $this->ProcessInputData('startMek', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->startMek($data);

		$result = $this->ReturnData($res);
	}

	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	function loadRegistryTree()
	{
		/**
		 *	Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "")
		{
			$val = array();
			$i = 0;
			if (!empty($node_id))
			{
				$node_id = "/".$node_id;
			}
			if ( $childrens != false && count($childrens) > 0 )
			{
				foreach ($childrens as $rows)
				{
					$node = array(
						'text'=>toUTF(trim($rows[$field['name']])),
						'id'=>$field['object'].".".$lvl.".".$rows[$field['id']].$node_id,
						//'new'=>$rows['New'],
						'object'=>$field['object'],
						'object_id'=>$field['id'],
						'object_value'=>$rows[$field['id']],
						'leaf'=>$field['leaf'],
						'iconCls'=>$field['iconCls'],
						'cls'=>$field['cls']
					);
					//$val[] = array_merge($node,$lrt,$lst);
					$val[] = $node;
				}

			}
			return $val;
		}

		// TODO: Тут надо поменять на ProcessInputData
		$data = array();
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
		$c_one = array();
		$c_two = array();

		// Текущий уровень
		if ((!isset($data['level'])) || (!is_numeric($data['level'])))
		{
			$val = array();//gabdushev: $val не определена в этом scope, добавил определение чтобы не было ворнинга, не проверял.
			$this->ReturnData($val);
			return;
		}

		$node = "";
		if (isset($data['node']))
		{
			$node = $data['node'];
		}

		if (mb_strpos($node, 'PayType.1.bud') !== false) {
			if ($data['level'] >= 3) {
				$data['level']++; // для бюджета нет реестров по СМО
			}
			$data['PayType_SysNick'] = 'bud';
		}

		$response = array();

		Switch ($data['level'])
		{
			case 0: // Уровень Root. ЛПУ
			{
				$this->load->model("LpuStructure_model", "lsmodel");
				$childrens = $this->lsmodel->GetLpuNodeList($data);

				$field = Array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 1: // Уровень 1. ОМС или бюджет
			{
				$childrens = array(
					array('PayType_SysNick' => 'oms', 'PayType_Name' => 'ОМС'),
					array('PayType_SysNick' => 'bud', 'PayType_Name' => 'Бюджет')
				);
				$field = Array('object' => "PayType",'id' => "PayType_SysNick", 'name' => "PayType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 2: // Уровень 2. Типочки
			{
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
			case 3: // Уровень 3. Предварительные/финальные
			{
				$childrens = array(
					array('RegistrySubType_id' => 1, 'RegistrySubType_Name' => 'Предварительные реестры'),
					array('RegistrySubType_id' => 2, 'RegistrySubType_Name' => 'Реестры по СМО'),
					array('RegistrySubType_id' => 3, 'RegistrySubType_Name' => 'Реестры для контроля объемов МП')
				);
				$field = Array('object' => "RegistrySubType",'id' => "RegistrySubType_id", 'name' => "RegistrySubType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
			case 4: // Уровень 4. Статусы реестров
			{
				$data['RegistrySubType_id'] = null;
				if (preg_match('/RegistrySubType\.[0-9]\.([0-9]*)\//ui', $node, $matches)) {
					$data['RegistrySubType_id'] = intval($matches[1]);
				}
				$childrens = $this->dbmodel->loadRegistryStatusNode($data);
				$field = Array('object' => "RegistryStatus",'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
		}
		if ( count($c_two)>0 )
		{
			$c_one = array_merge($c_one,$c_two);
		}

		$this->ReturnData($c_one);
	}

	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid()
	{
		$data = $this->ProcessInputData('loadUnionRegistryChildGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryChildGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция возвращает ошибки данных реестра по версии ТФОМС :)
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadUnionRegistryErrorTFOMS()
	{
		$data = $this->ProcessInputData('loadUnionRegistryErrorTFOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryErrorTFOMS($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	public function loadUnionRegistryErrorTFOMSFilter()
	{
		$data = $this->ProcessInputData('loadUnionRegistryErrorTFOMSFilter', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryErrorTFOMSFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry()
	{
		$data = $this->ProcessInputData('saveUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid()
	{
		$data = $this->ProcessInputData('loadUnionRegistryGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 * @description Загрузка списка МО, с которыми у МО формирования реестра заключен договор по профилю «0070. ЛАБОРАТОРНАЯ ДИАГНОСТИКА»
	 */
	public function getLpuCidList() {
		$data = $this->ProcessInputData('getLpuCidList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuCidList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка превышений объёмов
	 */
	function loadRegistryDataBadVol()
	{
		$data = $this->ProcessInputData('loadRegistryDataBadVol', true);
		if ($data === false) { return false; }

		$data['RegistryData_IsBadVol'] = 2;
		$response = $this->dbmodel->loadRegistryData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Добавление превышения объёма МП
	 */
	function setIsBadVol()
	{
		$data = $this->ProcessInputData('setIsBadVol', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setIsBadVol($data);
		$this->ProcessModelSave($response, true, 'Ошибка добавления превышения объёма МП')->ReturnData();
	}

	/**
	 * Переформирование финального реестра
	 */
	function reformUnionRegistry()
	{
		$data = $this->ProcessInputData('reformUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->reformUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при переформировании реестра')->ReturnData();
	}

	/**
	 * Изменение статуса счета-реестра
	 * Входящие данные: ID рееестра и значение статуса
	 * На выходе: JSON-строка
	 * Используется: форма просмотра реестра (счета)
	 */
	function setUnionRegistryStatus()
	{
		$data = $this->ProcessInputData('setUnionRegistryStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setUnionRegistryStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Удаление связанных реестров
	 */
	function deleteUnionRegistrys()
	{
		$data = $this->ProcessInputData('deleteUnionRegistrys', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistrys($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Удаление случаев по СМО
	 */
	function deleteOrgSmoRegistryData()
	{
		$data = $this->ProcessInputData('deleteOrgSmoRegistryData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteOrgSmoRegistryData($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Проверка случаев на включение в реестры по СМО
	 */
	function checkIncludeInUnioinRegistry()
	{
		$data = $this->ProcessInputData('checkIncludeInUnioinRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkIncludeInUnioinRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Пересчёт объёмов МП
	 */
	function refreshRegistryVolumes()
	{
		$data = $this->ProcessInputData('refreshRegistryVolumes', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->refreshRegistryVolumes($data);
		$this->ProcessModelSave($response, true, 'Ошибка при пересчёте объёмов МП')->ReturnData();
	}

	/**
	 * Функция формирует группу файлов в XML формате для выгрузки данных.
	 * На выходе: JSON-строка.
	 * Task#18694
	 */
	function exportRegistryGroupToXml()
	{	
		$data = $this->ProcessInputData('exportRegistryGroupToXml', true);
		if ($data === false) { return false; }

		$RegistryList = json_decode($data['Registry_id'], 1);
		$RegistryTypeList = json_decode($data['RegistryType_id'], 1);
		$RegistryNumList = json_decode($data['Registry_Num'], 1);
		
		//Странно..., после джейсона null превращается в строку "null"
		$KatNaselList = ($data['KatNasel_id'] == 'null') ? array() : json_decode($data['KatNasel_id'], 1);
		$OverrideControlFlkStatus = isset($data['OverrideControlFlkStatus']) ? json_decode($data['OverrideControlFlkStatus'], 1) : array();
		$OverrideExportOneMoreOrUseExist = isset($data['OverrideExportOneMoreOrUseExist']) ? json_decode($data['OverrideExportOneMoreOrUseExist'], 1) : array();
		$onlyLink = isset($data['onlyLink']) ? json_decode($data['onlyLink'], 1) : array();
		$send = isset($data['send']) ? json_decode($data['send'], 1) : array();
		
		$groupResult = array();
		
		$groupResult['success'] = true;
		
		if ( is_array($RegistryList) ) {
			//Имя большого архива
			$file_zip_name = EXPORTPATH_REGISTRY.'/'.'Big_'.date('d.m.Y_h-i-s').'.zip';

			$duplicates = 0;
			$used = array();

			foreach ( $RegistryList as $k => $Registry_id ) {
				$data['Registry_id'] = $RegistryList[$k];
				$data['RegistryType_id'] = $RegistryTypeList[$k];
				$data['KatNasel_id'] = ($KatNaselList == null) ? null : $KatNaselList[$k];
				$data['OverrideControlFlkStatus'] = ($OverrideControlFlkStatus == null) ? null : $OverrideControlFlkStatus[$k];
				$data['OverrideExportOneMoreOrUseExist'] = ($OverrideExportOneMoreOrUseExist == null) ? null : $OverrideExportOneMoreOrUseExist[$k];
				$data['onlyLink'] = ($onlyLink == null) ? null : $onlyLink[$k];
				$data['send'] = ($send == null) ? null : $send[$k];
				
				$res = $this->dbmodel->exportRegistryToXml($data);
				
				$groupResult[] = $res;
				$groupResult[$k]['number'] = $RegistryNumList[$k];

				if(isset($groupResult[$k]['Link'])){
					
					//Если нашёлся хотябы 1 маленький архив то
					if(!file_exists($file_zip_name)){
						file_put_contents($file_zip_name, '');
									
						$zip=new ZipArchive();
						$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
					}

					$registry_archive = explode('/',$groupResult[$k]['Link']);
					//Упаковываем и ложим в папку с именем СМО
					$resNameSmo = $this->dbmodel->getSmoName(array('Registry_id' => $Registry_id));
					$resNameSmo = explode(' (', $resNameSmo[0]['Smo_Name']);
					$dir_smo = iconv('utf-8', 'CP866', trim($resNameSmo[0]));
					if (in_array($registry_archive[3], $used)) { // если уже есть с таким названием, добавим что нибудь, а то не выгружается.
						$duplicates++;
						$registry_archive[3] = $duplicates.'_'.$registry_archive[3];
					}

					$used[] = $registry_archive[3];
					$zip->AddFile($groupResult[$k]['Link'], $dir_smo.'/'.$registry_archive[3]);
			   }

			}

			if(file_exists($file_zip_name)){
				$zip->close();

				$groupResult['big'] = array(
					'file_name'=>$file_zip_name
				);  
						   
			} 
		}

		echo json_encode($groupResult);
	}

	/**
	 * @return bool
	 */
	public function loadRegistryDataBadVolFilter()
	{
		$data = $this->ProcessInputData('loadRegistryDataFilter', true);
		if ($data === false) { return false; }

		$data['RegistryData_IsBadVol'] = 2;
		$response = $this->dbmodel->loadRegistryDataFilter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
}
