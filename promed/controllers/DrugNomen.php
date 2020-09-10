<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property DrugNomen_model dbmodel
 */

class DrugNomen extends swController {
	public $inputRules = array(
		'loadDrugNomenGrid' => array(
			array(
				'field' => 'PrepClass_id',
				'label' => 'Класс номенклатуры',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugNomen_Code',
				'label' => 'Код номенклатуры',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugNomenOrgLink_Code',
				'label' => 'Код номенклатуры',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugComplexMnnCode_Code',
				'label' => 'Код номенклатуры',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugPrepFasCode_Code',
				'label' => 'Код комплексного торгового наименования',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Actmatters_id',
				'label' => 'Идентификатор действующего вещества',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Tradenames_id',
				'label' => 'Идентификатор торгового наименования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Clsdrugforms_id',
				'label' => 'Идентификатор формы выпуска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RlsActmatters_RusName',
				'label' => 'Наименование действующего вещества',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RlsTorg_Name',
				'label' => 'Торговое наименование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RlsClsdrugforms_Name',
				'label' => 'Наименование формы выпуска',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CLSPHARMAGROUP_ID',
				'label' => 'Идентификатор фармгруппы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CLSATC_ID',
				'label' => 'Идентификатор анатомо-террапевтической группы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CLS_MZ_PHGROUP_ID',
				'label' => 'Идентификатор фармгруппы МЗ РФ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'STRONGGROUPS_ID',
				'label' => 'Идентификатор группы сильнодейсвующих ЛС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NARCOGROUPS_ID',
				'label' => 'Идентификатор группы наркотических ЛС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FIRMS_ID',
				'label' => 'Идентификатор фирмы производителя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'COUNTRIES_ID',
				'label' => 'Идентификатор страны производителя',
				'rules' => '',
				'type' => 'id'
			),
            array(
                'field' => 'no_rmz',
                'label' => 'Признак отстутствия связи с РЗН',
                'rules' => 'trim',
                'type' => 'string'
            ),
            array(
                'field' => 'rls_drug_link',
                'label' => 'Признак наличия связи со справочником медикаментов',
                'rules' => 'trim',
                'type' => 'string'
            ),
            array(
                'field' => 'DrugNomenOrgLink_Org_id',
                'label' => 'Организация в справочнике кодов организации',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'SprType_Code',
                'label' => 'Код типа справочника',
                'rules' => 'trim',
                'type' => 'string'
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
			)
		),
		'loadDrugNomenCmpDrugUsageCombo' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'поисковая строка',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadNsiDrugDoseCombo' => array(
			array(
				'field' => 'DrugDose_id',
				'label' => 'Идентификатор дозировки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'поисковая строка',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadNsiDrugKolDoseCombo' => array(
			array(
				'field' => 'DrugKolDose_id',
				'label' => 'Идентификатор количества доз в упаковке',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'поисковая строка',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadPrepClassTree' => array(
			array(
				'default' => 0,
				'field' => 'level',
				'label' => 'Уровень',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PrepClass_pid',
				'label' => 'Идентификатор родительского объекта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'mode',
				'label' => 'режим загрузки',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadDrugNomenEditForm' => array(
			array(
				'field' => 'DrugNomen_id',
				'label' => 'Идентификатор норматива',
				'rules' => 'required',
				'type' => 'id'
			)		
		),
		'loadDrugVznData' => array(
			array(
				'field' => 'Drug_id',
				'label' => 'Идентификатор медикамента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveDrugNomen' => array(
			array(
				'field' => 'DrugNomen_id',
				'label' => 'Идентификатор препарата',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Drug_id',
				'label' => 'Идентификатор медикамента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrepClass_id',
				'label' => 'Класс учета',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugNomen_Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DrugNomen_Nick',
				'label' => 'Наименование',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DrugMnnCode_id',
				'label' => 'Идентификатор кода МНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Actmatters_id',
				'label' => 'Идентификатор действующего вещества',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Actmatters_LatName',
				'label' => 'Латинское название действующего вещества',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'DrugMnnCode_Code',
				'label' => 'Код МНН',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugTorgCode_id',
				'label' => 'Идентификатор кода торг. наим.',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Tradenames_id',
				'label' => 'Идентификатор торгового наименования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Tradenames_LatName',
				'label' => 'Латинское название торгового наименования',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Tradenames_LatName_id',
				'label' => 'Латинское название торгового наименования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugTorgCode_Code',
				'label' => 'Код торг. наим.',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'DrugComplexMnnCode_id',
				'label' => 'Идентификатор кода комплекс. МНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnn_id',
				'label' => 'Идентификатор комплекс. МНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnn_LatName',
				'label' => 'Латинское наименование комплекс. МНН',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugComplexMnnCode_Code',
				'label' => 'Код комплекс. МНН',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Clsdrugforms_id',
				'label' => 'Идентификатор формы выпуска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Clsdrugforms_LatName',
				'label' => 'Латинское наименование формы выпуска',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Clsdrugforms_LatNameSocr',
				'label' => 'Латинское наименование формы выпуска (сокращенное)',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Unit_id',
				'label' => 'Идентификатор дозировки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Unit_table',
				'label' => 'Таблица дозировки',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Unit_LatName',
				'label' => 'Латинское наименование дозировки',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Okpd_id',
				'label' => 'Идентификатор ОКПД',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRMZ_id',
				'label' => 'Идентификатор кода РЗН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugRMZ_oldid',
				'label' => 'Прежний идентификатор кода РЗН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugFormMnnVZN_id',
				'label' => 'Идентификатор ЛС ВЗН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugNomenOrgLink_Org_id',
				'label' => 'Организация для кода организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugNomenOrgLink_Code',
				'label' => 'Код организации',
				'rules' => 'trim',
				'type' => 'string'
			),
            array(
				'field' => 'DrugPrepFasCode_id',
				'label' => 'Идентификатор кода группировочного торг. наим.',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugPrepFas_id',
				'label' => 'Идентификатор группировочного торг. наим.',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugTorg_NameLatin',
				'label' => 'Латинское наименование ЛП',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugPrepFasCode_JsonData',
				'label' => 'Коды группировочного торг. наим.',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugComplexMnnCode_DosKurs',
				'label' => 'Максимальное кол-во упаковок на 1 месяц',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveDrugVznData' => array(
            array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugVZN_fid', 'label' => 'МНН', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugFormVZN_id', 'label' => 'Лек.форма', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugDose_id', 'label' => 'Дозировка', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugKolDose_id', 'label' => 'Кол-во доз в уп.', 'rules' => '', 'type' => 'id'),
            array('field' => 'DrugRelease_id', 'label' => 'Торг. наим.', 'rules' => '', 'type' => 'id')
        ),
		'saveDrugMnnCode' => array(
			array(
				'field' => 'DrugMnnCode_id',
				'label' => 'Идентификатор кода МНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Actmatters_id',
				'label' => 'Идентификатор действующего вещества',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugMnnCode_Code',
				'label' => 'Код МНН',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'saveDrugTorgCode' => array(
			array(
				'field' => 'DrugTorgCode_id',
				'label' => 'Идентификатор кода торг. наим.',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Tradenames_id',
				'label' => 'Идентификатор торгового наименования',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugTorgCode_Code',
				'label' => 'Код торг. наим.',
				'rules' => 'required|trim',
				'type' => 'string'
			)
		),
		'saveDrugComplexMnnCode' => array(
			array(
				'field' => 'DrugComplexMnnCode_id',
				'label' => 'Идентификатор кода комплекс. МНН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnn_id',
				'label' => 'Идентификатор комплекс. МНН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DrugComplexMnnCode_Code',
				'label' => 'Код комплекс. МНН',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'saveDrugPrepFasCode' => array(
			array(
				'field' => 'DrugPrepFasCode_id',
				'label' => 'Идентификатор кода группировочного торг. наим.',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugPrepFas_id',
				'label' => 'Идентификатор группировочного торг. наим.',
				'rules' => 'required',
				'type' => 'id'
			),
            array(
                'field' => 'DrugPrepFasCode_JsonData',
                'label' => 'Коды группировочного торг. наим.',
                'rules' => '',
                'type' => 'string'
            )
		),
		'generateCodeForObject' => array(
			array(
				'field' => 'Object',
				'label' => 'Объект',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Drug_id',
				'label' => 'Медикамент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getDrugNomenData' => array(
			array(
				'field' => 'Drug_id',
				'label' => 'Идентификатор ЛС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getDrugNomenCode' => array(
			array(
				'field' => 'Drug_id',
				'label' => 'Идентификатор ЛС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadOkpdList' => array(
			array(
				'field' => 'Okpd_id',
				'label' => 'Идентификатор ОКПД',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'запрос',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getDrugMnnCodeByActMattersId' => array(
			array(
				'field' => 'ActMatters_id',
				'label' => 'Идентификатор действующего вещества',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadDrugRMZList' => array(
			array('field' => 'no_rls', 'label' => 'Признак отстутствия связи с РЛС', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugRMZ_MNN', 'label' => 'МНН', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugRMZ_Name', 'label' => 'Торговое наименование', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugRMZ_Form', 'label' => 'Форма выпуска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugRMZ_Dose', 'label' => 'Дозировка', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugRMZ_PackSize', 'label' => 'Фасовка', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugRMZ_RegNum', 'label' => '№ РУ', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugRMZ_Firm', 'label' => 'Производитель', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100 )
		),
		'loadDrugRMZListByQuery' => array(
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Reg_Num', 'label' => '№ РУ', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Drug_Ean', 'label' => 'Код EAN', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Drug_Fas', 'label' => 'Количество лек. форм в упаковке', 'rules' => '', 'type' => 'float'),
			array('field' => 'no_rls', 'label' => 'Признак отстутствия связи с РЛС', 'rules' => 'trim', 'type' => 'string')
		),
		'getDrugByDrugNomenCode' => array(
			array(
				'field' => 'DrugNomen_Code',
				'label' => 'Номенклатурный код ЛС',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'loadDrugMnnCode' => array(
			array('field' => 'DrugMnnCode_id', 'label' => 'Идентификатор кода', 'rules' => 'required', 'type' => 'id')
		),
		'loadDrugMnnCodeList' => array(
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugMnnCode_Code', 'label' => 'Код', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100 )
		),
		'deleteDrugMnnCode' => array(
			array('field' => 'id', 'label' => 'Идентификатор кода', 'rules' => 'required', 'type' => 'id')
		),
		'loadDrugTorgCode' => array(
			array('field' => 'DrugTorgCode_id', 'label' => 'Идентификатор кода', 'rules' => 'required', 'type' => 'id')
		),
		'loadDrugTorgCodeList' => array(
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DrugTorgCode_Code', 'label' => 'Код', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100 )
		),
		'deleteDrugTorgCode' => array(
			array('field' => 'id', 'label' => 'Идентификатор кода', 'rules' => 'required', 'type' => 'id')
		),
		'loadDboDrugMnnCodeListByName' => array(
			array('field' => 'DrugMnn_Name', 'label' => 'Наименование', 'rules' => 'trim', 'type' => 'string')
		),
		'loadDboDrugTorgCodeListByName' => array(
			array('field' => 'DrugTorg_Name', 'label' => 'Наименование', 'rules' => 'trim', 'type' => 'string')
		),
		'loadDrugFormMnnVZNCombo' => array(
			array('field' => 'DrugFormMnnVZN_id', 'label' => 'Идентификатор записи', 'rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
		),
		'exportDrugRMZToCsv' => array(
			array('field' => 'Year', 'label' => 'Отчетный год', 'rules' => '', 'type' => 'int'),
			array('field' => 'Month', 'label' => 'Отчетный месяц', 'rules' => '', 'type' => 'int'),
			array('field' => 'Supply_DateRange', 'label' => 'Отчетный период', 'rules' => '', 'type' => 'daterange')
		),
		'getDrugRMZLinkData' => array(
			array('field' => 'Drug_id', 'label' => 'Идентификатор ЛС', 'rules' => '', 'type' => 'id')
		),
		'saveDrugRMZLink' => array(
			array('field' => 'Drug_id', 'label' => 'Идентификатор ЛС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugRMZ_id', 'label' => 'Идентификатор ЛС РЗН', 'rules' => 'required', 'type' => 'id')
		),
		'loadDrugNomenList' => array(
			array('field' => 'DrugNomen_id', 'label' => 'Идентификатор номенклатуры', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка запроса', 'rules' => '', 'type' => 'string'),
			array('field' => 'queryBy', 'label' => 'Запрос по...', 'rules' => '', 'type' => 'string'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
		),
		'saveGoodsPackCount' => array(
			array('field' => 'GoodsPackCount_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TRADENAMES_ID', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'GoodsPackCount_Count', 'label' => 'Количество товара в упаковке', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id')
		),
		'saveDrugPrepEdUcCount' => array(
			array('field' => 'DrugPrepEdUcCount_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugPrepFas_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugPrepEdUcCount_Count', 'label' => 'Количество товара в упаковке', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id')
		),
		'loadGoodsPackCountList'=> array(
			array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id')
		),
		'loadGoodsPackCountListGrid'=> array(
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id')
		),
		'loadDrugPrepEdUcCountListGrid'=> array(
			array('field' => 'Drug_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
            array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id')
		),
		'deleteGoodsPackCount' => array(
			array('field' => 'GoodsPackCount_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'GoodsPackCount_Count', 'label' => 'Количество товара в упаковке', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id')
		),
		'deleteDrugPrepEdUcCount' => array(
			array('field' => 'DrugPrepEdUcCount_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugPrepFas_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugPrepEdUcCount_Count', 'label' => 'Количество товара в упаковке', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'GoodsUnit_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id')
		),
		'getGoodsUnitData' => array(
			array('field' => 'Org_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id')
		)
	);


	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('DrugNomen_model', 'dbmodel');
	}

	/**
	 * Формирование элементов дерева из записей таблицы
	 */
	function getTreeNodes($nodes, $field, $level, $dop = "", $check = 0) {
		$val = array();
		$i = 0;

		if ( is_array($nodes) && count($nodes) > 0 ) {
			foreach ( $nodes as $rows ) {
				if ( array_key_exists('ChildrensCount', $rows) ) {
					$field['leaf'] = ($rows['ChildrensCount'] == 0 ? true : false);
				}

				$node = array(
					'id' => $rows[$field['id']],
					'object' => $rows['object'],
					'object_id' => $field['id'],
					'object_value' => $rows[$field['id']],
					'object_code' => $rows[$field['code']],
					'text' => $rows[$field['name']],
					'leaf' => $rows['leaf'],
					'iconCls' => (empty($rows['iconCls']) ? $field['iconCls'] : $rows['iconCls']),
					'cls' => $field['cls']
				);

				$val[] = $node;
			}
		}

		return $val;
	}
	
	/**
	 *	Функция читает ветку дерева
	 */
	function loadPrepClassTree() {
		$data = $this->ProcessInputData('loadPrepClassTree', true);
		if ( $data === false ) { return false; }

		if ($data['level'] == 0) {
			if (!empty($data['mode']) && $data['mode'] == 'common') {
				$data['PrepClass_Code'] = 1;
			}
		}
		$response = $this->dbmodel->loadPrepClassTree($data);
		$this->ProcessModelList($response, true, true);

		// Обработка для дерева 
		$field = array(
			'id' => 'id', 
			'name' => 'name',
			'code' => 'code',
			'iconCls' => 'folder16',
			'leaf' => false, 
			'cls' => 'folder'
		);

		$this->ReturnData($this->getTreeNodes($this->OutData, $field, $data['level'], ""));

		return true;
	}
	
	/**
	 *  Получение списка нормативов
     */
	function loadDrugNomenGrid() {
		$data = $this->ProcessInputData('loadDrugNomenGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugNomenGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 *  Получение комбо списка медикаментов (для карты закрытия вызова СМП)
	 */
	function loadDrugNomenCmpDrugUsageCombo() {
		$data = $this->ProcessInputData('loadDrugNomenCmpDrugUsageCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugNomenCmpDrugUsageCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Загрузка списка дозировок (nsi)
	 */
	function loadNsiDrugDoseCombo() {
		$data = $this->ProcessInputData('loadNsiDrugDoseCombo', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNsiDrugDoseCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Загрузка списка количеств доз в упаковках (nsi)
	 */
	function loadNsiDrugKolDoseCombo() {
		$data = $this->ProcessInputData('loadNsiDrugKolDoseCombo', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNsiDrugKolDoseCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Загрузка формы редактирования
     */
	function loadDrugNomenEditForm() {
		$data = $this->ProcessInputData('loadDrugNomenEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugNomenEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 *  Загрузка данных справочника ЛП ВЗН
     */
	function loadDrugVznData() {
		$data = $this->ProcessInputData('loadDrugVznData', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugVznData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 *  Сохранение норматива
     */
	function saveDrugNomen() {
		$data = $this->ProcessInputData('saveDrugNomen', true);
		if ($data === false) { return false; }

		if (!empty($data['Actmatters_id']) && $this->ProcessInputData('saveDrugMnnCode', true, true, false, false, false) === false) {
				return false;
		}
		if (!empty($data['Tradenames_id']) && $this->ProcessInputData('saveDrugTorgCode', true, true, false, false, false) === false) {
			return false;
		}
		if (!empty($data['DrugComplexMnn_id']) && $this->ProcessInputData('saveDrugComplexMnnCode', true, true, false, false, false) === false) {
			return false;
		}
		if (!empty($data['DrugComplexMnn_id']) && $this->ProcessInputData('saveDrugPrepFasCode', true, true, false, false, false) === false) {
			return false;
		}

		$check = $this->dbmodel->checkDrugNomen($data);
		if (!$check['success']) {
			$this->ReturnError($check['Error_Msg']);
			return false;
		}

		$this->dbmodel->beginTransaction();

		if (!empty($data['Actmatters_id'])) {
			$DrugMnnCode = $this->dbmodel->saveDrugMnnCode($data);
			if (!$DrugMnnCode['success']) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			$data['DrugMnnCode_id'] = $DrugMnnCode['DrugMnnCode_id'];
		} 

		if (!empty($data['Actmatters_id']) && (isset($data['Actmatters_LatName']) && !empty($data['Actmatters_LatName']))) {
			$actmatters_LatName = $this->dbmodel->saveActmatters_LatName($data);
			if (!$actmatters_LatName) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		if (!empty($data['DrugComplexMnn_id'])) {
			$DrugComplexMnnCode = $this->dbmodel->saveDrugComplexMnnCode($data);
			if (!$DrugComplexMnnCode['success']) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			$data['DrugComplexMnnCode_id'] = $DrugComplexMnnCode['DrugComplexMnnCode_id'];

			if(!empty($data['DrugComplexMnn_LatName'])){
				$drugComplexMnnLatName = $this->dbmodel->saveDrugComplexMnn_LatName($data);
				if (!$drugComplexMnnLatName) {
					$this->ReturnError('Error');
					$this->dbmodel->rollbackTransaction();
					return false;
				}
			}
		}

		if (!empty($data['Tradenames_id'])) {
			$DrugTorgCode = $this->dbmodel->saveDrugTorgCode($data);
			if (!$DrugTorgCode['success']) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
			$data['DrugTorgCode_id'] = $DrugTorgCode['DrugTorgCode_id'];
		}

		if (!empty($data['DrugPrepFas_id']) && !empty($data['DrugPrepFasCode_JsonData'])) {
            ConvertFromWin1251ToUTF8($data['DrugPrepFasCode_JsonData']);
            $dpfc_arr = (array) json_decode($data['DrugPrepFasCode_JsonData']);
            foreach($dpfc_arr as $dpfc) {
                $DrugPrepFasCode = $this->dbmodel->saveDrugPrepFasCode(array(
                    'DrugPrepFas_id' => $data['DrugPrepFas_id'],
                    'Org_id' => $dpfc->Org_id > 0 ? $dpfc->Org_id : null,
                    'DrugPrepFasCode_Code' => $dpfc->DrugPrepFasCode_Code,
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!$DrugPrepFasCode['success']) {
                    $this->ReturnError('Error');
                    $this->dbmodel->rollbackTransaction();
                    return false;
                }
            }
		}

		if (!empty($data['DrugPrepFas_id']) && !empty($data['DrugTorg_NameLatin'])) {
			$drugTorgNameLatin = $this->dbmodel->saveDrugTorg_NameLatin($data);
			if (!$drugTorgNameLatin) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		if (!empty($data['Tradenames_id']) 
			&& (isset($data['Tradenames_LatName_id']) && !empty($data['Tradenames_LatName_id'])) 
			&& (isset($data['Tradenames_LatName']) && !empty($data['Tradenames_LatName']))) {
			$tradenames_LatName = $this->dbmodel->saveTradenames_LatName($data);
			if (!$tradenames_LatName || $tradenames_LatName[0]['Error_Code'] !== null || $tradenames_LatName[0]['Error_Msg'] !== null) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		if (!empty($data['Clsdrugforms_id']) && (!empty($data['Clsdrugforms_LatName']) || !empty($data['Clsdrugforms_LatNameSocr']))) {
			$clsdrugforms_LatName = $this->dbmodel->saveClsdrugforms_LatName($data);
			if (!$clsdrugforms_LatName || $clsdrugforms_LatName[0]['Error_Code'] !== null || $clsdrugforms_LatName[0]['Error_Msg'] !== null) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}
		if (!empty($data['Unit_id']) && !empty($data['Unit_table']) && (isset($data['Unit_LatName']) && !empty($data['Unit_LatName']))) {
			$unit_LatName = $this->dbmodel->saveUnit_LatName($data);
			if (!$unit_LatName || $unit_LatName[0]['Error_Code'] !== null || $unit_LatName[0]['Error_Msg'] !== null) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		if (!empty($data['DrugRMZ_id']) || !empty($data['DrugRMZ_oldid'])) {
			$DrugRMZResponse = $this->dbmodel->saveDrugRMZLink($data);
			if (!$DrugRMZResponse['success']) {
				$this->ReturnError('Error');
				$this->dbmodel->rollbackTransaction();
				return false;
			}
		}

		$response = $this->dbmodel->saveDrugNomen($data);
        if (!empty($response[0]) && !empty($response[0]['DrugNomen_id'])) {
            if (!empty($data['DrugNomenOrgLink_Org_id'])) {
                $data['DrugNomen_id'] = $response[0]['DrugNomen_id'];
                $DrugNomenOrgLinkResponse = $this->dbmodel->saveDrugNomenOrgLink($data);
            }
        } else {
            $this->ReturnError('Ошибка при сохранении позиции справочника');
            $this->dbmodel->rollbackTransaction();
            return false;
        }

		$this->dbmodel->commitTransaction();

		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}

	/**
	 *  Сохранение данных справочника ЛП ВЗН
	 */
	function saveDrugVznData() {
		$data = $this->ProcessInputData('saveDrugVznData', false);

		if ($data){
			$response = $this->dbmodel->saveDrugVznData($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение кода
	 */
	function generateCodeForObject() {
		$data = $this->ProcessInputData('generateCodeForObject', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->generateCodeForObject($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных, связанных с Drug_id
	 */
	function getDrugNomenData() {
		$data = $this->ProcessInputData('getDrugNomenData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDrugNomenData($data);
		$this->ProcessModelList($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение регионального кода по Drug_id
	 */
	function getDrugNomenCode() {
		$data = $this->ProcessInputData('getDrugNomenCode', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDrugNomenCode($data);
		$this->ProcessModelList($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка ОКПД
	 */
	function loadOkpdList() {
		$data = $this->ProcessInputData('loadOkpdList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadOkpdList($data);
		$this->ProcessModelList($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение номенклатурного кода по действующему веществу
	 */
	function getDrugMnnCodeByActMattersId() {
		$data = $this->ProcessInputData('getDrugMnnCodeByActMattersId', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDrugMnnCodeByActMattersId($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Получение списка кодов РЗН для формы просмотра справочника
	 */
	function loadDrugRMZList() {
		$data = $this->ProcessInputData('loadDrugRMZList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugRMZList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка кодов РЗН для формы поиска
	 */
	function loadDrugRMZListByQuery() {
		$data = $this->ProcessInputData('loadDrugRMZListByQuery', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugRMZListByQuery($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение медикамента по номенклатурному коду
	 */
	function getDrugByDrugNomenCode() {
		$data = $this->ProcessInputData('getDrugByDrugNomenCode', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDrugByDrugNomenCode($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение регионального кода МНН
	 */
	function saveDrugMnnCode() {
		$data = $this->ProcessInputData('saveDrugMnnCode', true);
		if ($data){
			$response = $this->dbmodel->saveDrugMnnCode($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении регионального кода МНН')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	 /**
	  * Загрузка регионального кода МНН
	  */
	function loadDrugMnnCode() {
		$data = $this->ProcessInputData('loadDrugMnnCode', true);
		if ($data){
			$response = $this->dbmodel->loadDrugMnnCode($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка региональных кодов МНН
	 */
	function loadDrugMnnCodeList() {
		$data = $this->ProcessInputData('loadDrugMnnCodeList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugMnnCodeList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление регионального кода МНН
	 */
	function deleteDrugMnnCode() {
		$data = $this->ProcessInputData('deleteDrugMnnCode', true, true);
		if ($data) {
			$response = $this->dbmodel->deleteDrugMnnCode($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение регионального кода Торгового наименования
	 */
	function saveDrugTorgCode() {
		$data = $this->ProcessInputData('saveDrugTorgCode', true);

		if ($data){
			$response = $this->dbmodel->saveDrugTorgCode($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении регионального кода Торгового наименования')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	 /**
	  * Загрузка регионального кода Торгового наименования
	  */
	function loadDrugTorgCode() {
		$data = $this->ProcessInputData('loadDrugTorgCode', true);
		if ($data){
			$response = $this->dbmodel->loadDrugTorgCode($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка региональных кодов Торговых наименований
	 */
	function loadDrugTorgCodeList() {
		$data = $this->ProcessInputData('loadDrugTorgCodeList', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugTorgCodeList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление регионального кода Торгового наименования
	 */
	function deleteDrugTorgCode() {
		$data = $this->ProcessInputData('deleteDrugTorgCode', true, true);
		if ($data) {
			$response = $this->dbmodel->deleteDrugTorgCode($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка кодов из таблицы dbo.DrugMnn по имени
	 */
	function loadDboDrugMnnCodeListByName() {
		$data = $this->ProcessInputData('loadDboDrugMnnCodeListByName', true);
		if ($data) {
			$response = $this->dbmodel->loadDboDrugMnnCodeListByName($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка кодов из таблицы dbo.DrugTorg по имени
	 */
	function loadDboDrugTorgCodeListByName() {
		$data = $this->ProcessInputData('loadDboDrugTorgCodeListByName', true);
		if ($data) {
			$response = $this->dbmodel->loadDboDrugTorgCodeListByName($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка форм выпуска ЛС ВЗН
	 */
	function loadDrugFormMnnVZNCombo() {
		$data = $this->ProcessInputData('loadDrugFormMnnVZNCombo', false);
		if ($data) {
			$filter = $data;
			$response = $this->dbmodel->loadDrugFormMnnVZNCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Получение общей информации о справочнике ЛП Росздравнадзора
	 */
	function getDrugRMZInformation() {
		$response = $this->dbmodel->getDrugRMZInformation();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Импорт данных справочника ЛП Росздравнадзора из csv файла.
	 */
	function importDrugRMZFromCsv() {
		ini_set("max_execution_time", "300");
		ini_set("max_input_time", "300");
		ini_set("post_max_size", "30M");
		ini_set("upload_max_filesize", "30M");

		$data = array();
		$session_data = getSessionParams();
		$data['pmUser_id'] = $session_data['pmUser_id'];

		$root_dir = IMPORTPATH_ROOT;
		if( !is_dir($root_dir) ) {
			if( !mkdir($root_dir) ) {
				return $this->ReturnError('Не удалось создать директорию для хранения загружаемых данных!');
			}
		}

		if( !isset($_FILES['uploadfilefield']) ) {
			return $this->ReturnError('Ошибка! Отсутствует файл! (поле uploadfilefield)');
		}

		$file = $_FILES['uploadfilefield'];
		if( $file['error'] > 0 ) {
			return $this->ReturnError('Ошибка при загрузке файла!', $file['error']);
		}

		//вычисляем расширение из названия файла
		$ext = explode('.', $file['name']);
		if (count($ext) > 0) {
			$ext = strtolower($ext[count($ext)-1]);
		} else {
			$ext = null;
		}
		if( $ext != 'csv' ) {
			return $this->ReturnError('Необходим файл с расширением csv.');
		}

		$fileFullName = $root_dir.$file['name'];
		if( is_file($file['tmp_name']) ) {
			$fileFullName = $root_dir.time().'_'.$file['name'];
		}

		if( !rename($file['tmp_name'], $fileFullName) ) {
			return $this->ReturnError('Не удалось создать файл ' . $fileFullName);
		}
		$data['FileFullName'] = $fileFullName;

		$response = $this->dbmodel->importDrugRMZFromCsv($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		unlink($fileFullName);
		return true;
	}


	/**
	 *	Экспорт остатков и поставок по ОНЛС и ВЗН
	 */
	function exportDrugRMZToCsv() {
		$data = $this->ProcessInputData('exportDrugRMZToCsv', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDrugRMZExportData($data);
		if( !is_array($response) || count($response) == 0 ) {
			DieWithError("Нет данных для экспорта");
		}

		set_time_limit(0);

		if(!is_dir(EXPORTPATH_DRUGRMZ)) {
			if (!mkdir(EXPORTPATH_DRUGRMZ)) {
				DieWithError("Ошибка при создании директории ".EXPORTPATH_DRUGRMZ."!");
			}
		}

		$cur_date = new DateTime();
		$f_name = "drugrmz_".$cur_date->format('d-m-Y');
		$export_file_name = EXPORTPATH_DRUGRMZ.$f_name.".csv";
		$log_file_name = EXPORTPATH_DRUGRMZ."export_log.txt";
		$archive_name = EXPORTPATH_DRUGRMZ.$f_name.".zip";
		if( is_file($archive_name) ) {
			unlink($archive_name);
		}

		try {
			$str_result = "";
			$str_result .= "DrugID;";
			$str_result .= "extid;";
			$str_result .= "VZN;";
			$str_result .= "Year;";
			$str_result .= "Month;";
			$str_result .= "RecordType;";
			$str_result .= "FinYear;";
			$str_result .= "ExpDate;";
			$str_result .= "Amount;";
			$str_result .= "Summa\n";

			$log_result = "";
			$export_row_count = 0;

			foreach($response as $row) {
				switch($row['Error_Code']) {
					case 1:
						$log_result .= toUTF("Отсутствует код РЗН. Медикамент: {$row['Drug_Name']}.")."\r\n";
						break;
					case 2:
						$log_result .= toUTF("Отрицательная разница между поставкой и возвратом. DrugID: {$row['DrugID']}.")."\r\n";
						break;
					case 3:
						$log_result .= toUTF("Остаточный срок годности на дату выгрузки менее или равен 2 месяцам. Серия: {$row['Ser']}. Срок годности: {$row['ExpDate']}. DrugID: {$row['DrugID']}. Медикамент: {$row['Drug_Name']}.")."\r\n";
						break;
					case 4:
						$log_result .= toUTF("Есть признак забраковки. Серия: {$row['Ser']}. Срок годности: {$row['ExpDate']}. DrugID: {$row['DrugID']}. Медикамент: {$row['Drug_Name']}.")."\r\n";
						break;
					default:
						$str_result .= str_replace(';','',$row['DrugID']).";";
						$str_result .= ";";
						$str_result .= str_replace(';','',$row['VZN']).";";
						$str_result .= $data['Year'].";";
						$str_result .= $data['Month'].";";
						$str_result .= str_replace(';','',$row['RecordType']).";";
						$str_result .= str_replace(';','',$row['FinYear']).";";
						$str_result .= str_replace(';','',$row['ExpDate']).";";
						$str_result .= str_replace(';','',$row['Amount']).";";
						$str_result .= str_replace(';','',$row['Summa'])."\n";
						$export_row_count++;
						break;
				}
			}

			if ($export_row_count > 0) {
				$h = fopen($export_file_name, 'w');
				if(!$h) {
					DieWithError("Ошибка при попытке открыть файл!");
				}
				fwrite($h, $str_result);
				fclose($h);
			}

			if ($log_result != "") {
				$h = fopen($log_file_name, 'w');
				if(!$h) {
					DieWithError("Ошибка при попытке открыть файл!");
				}
				fwrite($h, $log_result);
				fclose($h);
			}

			if ($export_row_count > 0 || $log_result != "") {
				$zip = new ZipArchive();
				$zip->open($archive_name, ZIPARCHIVE::CREATE);
				if ($export_row_count > 0) {
					$zip->AddFile($export_file_name, basename($export_file_name));
				}
				if ($log_result != "") {
					$zip->AddFile($log_file_name, basename($log_file_name));
				}
				$zip->close();
			} else {
				DieWithError("Отсутсвуют данные для выгрузки!");
			}

			if(is_file($export_file_name)) {
				@unlink($export_file_name);
			}
			if(is_file($log_file_name)) {
				@unlink($log_file_name);
			}

			$this->ReturnData(array('success' => true, 'url' => $archive_name));
		} catch (Exception $e) {
			DieWithError($e->getMessage());
			$this->ReturnData(array('success' => false));
		}

		if(is_file($export_file_name)) {
			@unlink($export_file_name);
		}
		if(is_file($log_file_name)) {
			@unlink($log_file_name);
		}
	}


	/**
	 * Получение данных для сопоставления данных номенклатурного справочника и справочника ЛР РЗН
	 */
	function getDrugRMZLinkData() {
		$data = $this->ProcessInputData('getDrugRMZLinkData', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDrugRMZLinkData($data);
		$this->ProcessModelList($response, true)->ReturnData();

		return true;
	}


	/**
	 * Сохранение связи между медикаментом и прозицией справочника РЗН
	 */
	function saveDrugRMZLink() {
		$data = $this->ProcessInputData('saveDrugRMZLink', true);
		if ($data){
			$response = $this->dbmodel->saveDrugRMZLink($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении позиции справочника РЗН')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение связи между медикаментом и прозицией справочника РЗН
	 */
	function loadDrugNomenList() {
		$data = $this->ProcessInputData('loadDrugNomenList', true);
		if ($data){
			$response = $this->dbmodel->loadDrugNomenList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение записи справочника Количество товара в упаковке
	 */
	function saveGoodsPackCount() {
		$data = $this->ProcessInputData('saveGoodsPackCount', true);
		if ($data){
			$response = $this->dbmodel->saveGoodsPackCount($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение записи справочника Количество товара в упаковке
	 */
	function saveDrugPrepEdUcCount() {
		$data = $this->ProcessInputData('saveDrugPrepEdUcCount', true);
		if ($data){
			$response = $this->dbmodel->saveDrugPrepEdUcCount($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка количества товара в упаковке
	 */
	function loadGoodsPackCountList() {
		$data = $this->ProcessInputData('loadGoodsPackCountList', true);
		if ($data) {
			$response = $this->dbmodel->loadGoodsPackCountList($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка количества товара в упаковке
	 */
	function loadGoodsPackCountListGrid() {
		$data = $this->ProcessInputData('loadGoodsPackCountListGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadGoodsPackCountListGrid($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка Единицы учета ЛП
	 */
	function loadDrugPrepEdUcCountListGrid() {
		$data = $this->ProcessInputData('loadDrugPrepEdUcCountListGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadDrugPrepEdUcCountListGrid($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление записи из таблицы «Количество товара в упаковке»
	 */
	function deleteGoodsPackCount() {
		$data = $this->ProcessInputData('deleteGoodsPackCount', true);
		if ($data) {
			$response = $this->dbmodel->deleteGoodsPackCount($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление записи из таблицы «Количество товара в упаковке»
	 */
	function deleteDrugPrepEdUcCount() {
		$data = $this->ProcessInputData('deleteDrugPrepEdUcCount', true);
		if ($data) {
			$response = $this->dbmodel->deleteDrugPrepEdUcCount($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных единиц измерения
	 */
	function getGoodsUnitData() {
		$data = $this->ProcessInputData('getGoodsUnitData', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getGoodsUnitData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

}
