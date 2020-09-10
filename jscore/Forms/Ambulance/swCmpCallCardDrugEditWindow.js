/**
* swCmpCallCardDrugEditWindow - окно редактирования информации о использовании медикаментов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Salakhov R.
* @version      08.2016
* @comment      
*/
sw.Promed.swCmpCallCardDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Использование медикаментов. СМП',
	layout: 'border',
	id: 'CmpCallCardDrugEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
    setDisabled: function(disable) {
        var wnd = this;

        var field_arr = [
            'MedStaffFact_id',
            'CmpCallCardDrug_setDate',
            'CmpCallCardDrug_setTime',
            'LpuBuilding_id',
            'Storage_id',
            'Mol_id',
            'StorageZone_id',
            'DrugPrepFas_id',
            'Drug_id',
            'DocumentUcStr_oid',
            'CmpCallCardDrug_Cost',
            'CmpCallCardDrug_Kolvo',
            'CmpCallCardDrug_KolvoUnit',
            'CmpCallCardDrug_Sum',
            'GoodsUnit_id'
        ];

        for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
            var field = wnd.form.findField(field_arr[i]);
            if (disable || field.enable_blocked) {
                field.disable();
            } else {
                field.enable();
            }
        }

        if (disable) {
            wnd.buttons[0].disable();
        } else {
            wnd.buttons[0].enable();
        }
    },
    setSum: function() {
        var kolvo_field = this.form.findField('CmpCallCardDrug_Kolvo');
        var cost_field = this.form.findField('CmpCallCardDrug_Cost');
        var sum_field = this.form.findField('CmpCallCardDrug_Sum');
        var kolvo = kolvo_field.getValue()*1 > 0 ? kolvo_field.getValue()*1 : 0;
        var cost = cost_field.getValue()*1 > 0 ? cost_field.getValue()*1 : 0;

        sum_field.setValue(kolvo*cost);
    },
    setCost: function() {
        var kolvo_field = this.form.findField('CmpCallCardDrug_Kolvo');
        var cost_field = this.form.findField('CmpCallCardDrug_Cost');
        var sum_field = this.form.findField('CmpCallCardDrug_Sum');
        var kolvo = kolvo_field.getValue()*1 > 0 ? kolvo_field.getValue()*1.0 : 0;
        var cost = cost_field.getValue()*1 > 0 ? cost_field.getValue()*1.0 : 0;
        var sum = sum_field.getValue()*1 > 0 ? sum_field.getValue()*1.0 : 0;

        if (kolvo > 0 && sum > 0) {
            cost_field.setValue((sum/kolvo).toFixed(2));
        }
    },
    setKolvo: function() {
        var kolvo_field = this.form.findField('CmpCallCardDrug_Kolvo');
        var kolvo_u_field = this.form.findField('CmpCallCardDrug_KolvoUnit');
        var gpc_count_field = this.form.findField('GoodsPackCount_Count');
        var gpc_b_count_field = this.form.findField('GoodsPackCount_bCount');
        var kolvo_u = kolvo_u_field.getValue()*1 > 0 ? kolvo_u_field.getValue()*1.0 : 0;
        var gpc_count = gpc_count_field.getValue()*1 > 0 ? gpc_count_field.getValue()*1.0 : 0;
        var gpc_b_count = gpc_b_count_field.getValue()*1 > 0 ? gpc_b_count_field.getValue()*1.0 : 1;

        if (kolvo_u > 0 && gpc_count > 0) {
            kolvo_field.setValue(((kolvo_u/gpc_count)*gpc_b_count).toFixed(3));
            this.setSum();
        }
    },
    setKolvoUnit: function() {
        var kolvo_field = this.form.findField('CmpCallCardDrug_Kolvo');
        var kolvo_u_field = this.form.findField('CmpCallCardDrug_KolvoUnit');
        var gpc_count_field = this.form.findField('GoodsPackCount_Count');
        var gpc_b_count_field = this.form.findField('GoodsPackCount_bCount');
        var kolvo = kolvo_field.getValue()*1 > 0 ? kolvo_field.getValue()*1 : 0;
        var gpc_count = gpc_count_field.getValue()*1 > 0 ? gpc_count_field.getValue()*1 : 0;
        var gpc_b_count = gpc_b_count_field.getValue()*1 > 0 ? gpc_b_count_field.getValue()*1.0 : 1;

        if (kolvo > 0 && gpc_count > 0) {
            kolvo_u_field.setValue(((kolvo*gpc_count)/gpc_b_count).toFixed(3));
        }
    },
    setOstKolvoUnit: function() {
        var ost_field = this.form.findField('Ost_Kolvo');
        var ost_u_field = this.form.findField('Ost_KolvoUnit');
        var gpc_count_field = this.form.findField('GoodsPackCount_Count');
        var gpc_b_count_field = this.form.findField('GoodsPackCount_bCount');
        var ost = ost_field.getValue()*1 > 0 ? ost_field.getValue()*1 : 0;
        var gpc_count = gpc_count_field.getValue()*1 > 0 ? gpc_count_field.getValue()*1 : 0;
        var gpc_b_count = gpc_b_count_field.getValue()*1 > 0 ? gpc_b_count_field.getValue()*1.0 : 1;

        if (ost > 0 && gpc_count > 0) {
            ost_u_field.setValue(((ost*gpc_count)/gpc_b_count).toFixed(3));
        } else {
            ost_u_field.setValue(null);
        }
    },
    setFieldDateFilter: function(date_str, combo_reload) { //функция устанавливает фильтры по дате для полей "медикамент" и "партия"
        var drug_combo = this.form.findField('Drug_id');
        var oid_combo = this.form.findField('DocumentUcStr_oid');

        drug_combo.getStore().baseParams.DrugShipment_setDT_max = date_str;
        oid_combo.getStore().baseParams.DrugShipment_setDT_max = date_str;

        if (combo_reload) {
            drug_combo.clearValue(); //очищаем только комбо "упаковка", поле "партия" является дочерним и очистится автоматически
            drug_combo.loadData();
        }
    },
    doSave:  function() {
        var wnd = this;
        if ( !this.form.isValid() ) {
            sw.swMsg.show( {
                buttons: Ext.Msg.OK,
                fn: function() {
                    wnd.findById('WhsDocumentSupplySpecEditForm').getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        //проверяем не превышает ли количество доступный остаток
        var ost_kolvo =  wnd.form.findField('Ost_Kolvo').getValue()*1.0;
        var kolvo = wnd.form.findField('CmpCallCardDrug_Kolvo').getValue()*1.0;

        if (kolvo > ost_kolvo) {
            sw.swMsg.alert('Ошибка', 'Количество превышает доступный остаток');
            return false;
        }

        var params = this.form.getValues();
        var gu_combo = this.form.findField('GoodsUnit_id');
        var gu_b_combo = this.form.findField('Kolvo_GoodsUnit_id');
        var drug_combo = this.form.findField('Drug_id');
        var oid_combo = this.form.findField('DocumentUcStr_oid');
        var drug_data = drug_combo.getSelectedRecordData();
        var oid_data = oid_combo.getSelectedRecordData();
        var gu_name = '';
        var gu_b_name = '';

        params.Contragent_id = getGlobalOptions().Contragent_id;
        params.Lpu_id = getGlobalOptions().Lpu_id;
        params.Org_id = getGlobalOptions().org_id;
        params.DrugFinance_id = !Ext.isEmpty(oid_data.DrugFinance_id) ? oid_data.DrugFinance_id : null;
        params.WhsDocumentCostItemType_id = !Ext.isEmpty(oid_data.WhsDocumentCostItemType_id) ? oid_data.WhsDocumentCostItemType_id : null;
        params.PrepSeries_id = !Ext.isEmpty(oid_data.PrepSeries_id) ? oid_data.PrepSeries_id : null;
        params.EmergencyTeam_id = !Ext.isEmpty(this.params.EmergencyTeam_id) ? this.params.EmergencyTeam_id : null;
        params.CmpCallCardDrug_Kolvo = this.form.findField('CmpCallCardDrug_Kolvo').getValue();

        gu_combo.getStore().each(function(record) {
            if (record.get('GoodsUnit_id') == gu_combo.getValue()) {
                gu_name = record.get('GoodsUnit_Name');
                return false;
            }
        });

        gu_b_combo.getStore().each(function(record) {
            if (record.get('GoodsUnit_id') == gu_b_combo.getValue()) {
                gu_b_name = record.get('GoodsUnit_Name');
                return false;
            }
        });

        params.GoodsUnit_bid = gu_b_combo.getValue();
        if (wnd.show_diff_gu) {
            params.GoodsUnit_id = gu_combo.getValue();
            params.GoodsUnit_Name = gu_name;
        } else {
            params.GoodsUnit_id = params.GoodsUnit_bid;
            params.GoodsUnit_Name = gu_b_name;
        }

        params.Drug_Name = !Ext.isEmpty(drug_data.Drug_Name) ? drug_data.Drug_Name : null;
        params.DrugNomen_Code = !Ext.isEmpty(drug_data.DrugNomen_Code) ? drug_data.DrugNomen_Code : null;

        wnd.onSave(params);
        wnd.hide();

        return true;
    },
	show: function() {
        var wnd = this;
		sw.Promed.swCmpCallCardDrugEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.onSave = Ext.emptyFn;
		this.CmpCallCardDrug_id = null;
		this.params = new Object();
        this.show_diff_gu = (getDrugControlOptions().doc_uc_different_goods_unit_control && getGlobalOptions().orgtype == 'lpu'); //отображение поле списания в альтернативных ед. измерения

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
        if ( arguments[0].params ) {
            this.params = arguments[0].params;
			this.CmpCallCardDrug_id = !Ext.isEmpty(arguments[0].params.CmpCallCardDrug_id) ? arguments[0].params.CmpCallCardDrug_id : null;
		}

		this.setTitle("Использование медикаментов. СМП");
		this.form.reset();

        //параметры по умолчанию

        this.form.findField('MedStaffFact_id').defaultBaseParams.Lpu_id = !Ext.isEmpty(getGlobalOptions().lpu_id) ? getGlobalOptions().lpu_id : null;
        this.form.findField('MedStaffFact_id').defaultBaseParams.EmergencyTeam_id = !Ext.isEmpty(this.params.EmergencyTeam_id) ? this.params.EmergencyTeam_id : null;
        this.form.findField('LpuBuilding_id').defaultBaseParams.Lpu_id = getGlobalOptions().lpu_id > 0 ? getGlobalOptions().lpu_id : null;
        this.form.findField('LpuBuilding_id').defaultBaseParams.LpuBuildingType_id = 27; //27 - Подразделение СМП

        //поля попросили скрыть
        this.form.findField('CmpCallCardDrug_Cost').hideContainer();
        this.form.findField('CmpCallCardDrug_Sum').hideContainer();

        //delete this.form.findField('CmpCallCardDrug_Kolvo').maxValue;

        this.form.findField('MedStaffFact_id').fullReset();
        this.form.findField('LpuBuilding_id').fullReset();
        this.form.findField('Storage_id').fullReset();
        this.form.findField('Mol_id').fullReset();
        this.form.findField('StorageZone_id').fullReset();
        this.form.findField('DrugPrepFas_id').fullReset();
        this.form.findField('Drug_id').fullReset();
        this.form.findField('DocumentUcStr_oid').fullReset();
        this.form.findField('GoodsUnit_id').fullReset();

        this.form.findField('StorageZone_id').ownerCt.hide();

        //установка видимости некоторых полей
        if (this.show_diff_gu) {
            this.form.findField('GoodsUnit_id').allowBlank = false;
            this.form.findField('CmpCallCardDrug_KolvoUnit').allowBlank = false;
            this.form.findField('GoodsUnit_id').ownerCt.show();
            this.form.findField('Ost_KolvoUnit').ownerCt.show();
            this.form.findField('CmpCallCardDrug_KolvoUnit').ownerCt.show();
            this.form.findField('CmpCallCardDrug_Kolvo').enable_blocked = true;
        } else {
            this.form.findField('GoodsUnit_id').allowBlank = true;
            this.form.findField('CmpCallCardDrug_KolvoUnit').allowBlank = true;
            this.form.findField('GoodsUnit_id').ownerCt.hide();
            this.form.findField('Ost_KolvoUnit').ownerCt.hide();
            this.form.findField('CmpCallCardDrug_KolvoUnit').ownerCt.hide();
            this.form.findField('CmpCallCardDrug_Kolvo').enable_blocked = false;
        }

        //утановка ед. учета по умолчанию
        this.form.findField('Kolvo_GoodsUnit_id').getStore().each(function(record) {
            if (record.get('GoodsUnit_Name') == 'упаковка') {
                wnd.form.findField('Kolvo_GoodsUnit_id').setValue(record.get('GoodsUnit_id'));
                return false
            }
        });

        this.setDisabled(this.action == 'view');

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
            case 'add':
				this.setTitle(this.title + ": Добавление");
                if (arguments[0].params) {
                    this.form.setValues(this.params);

                    this.setFieldDateFilter(!Ext.isEmpty(this.params.CmpCallCardDrug_setDate) ? this.params.CmpCallCardDrug_setDate : null);
                }

                //получение и установка значений по умолчанию
                var team_id = !Ext.isEmpty(this.params.EmergencyTeam_id) ? this.params.EmergencyTeam_id : null;
                if (team_id > 0) {
                    Ext.Ajax.request({
                        url: '/?c=CmpCallCard&m=getCmpCallCardDrugDefaultValues',
                        params: {
                            EmergencyTeam_id: team_id
                        },
                        callback: function(opt, success, response) {
                            if (success) {
                                var data = Ext.util.JSON.decode(response.responseText);

                                if (!Ext.isEmpty(data.MedStaffFact_id)) {
                                    wnd.form.findField('MedStaffFact_id').setValueById(data.MedStaffFact_id);
                                }
                                if (!Ext.isEmpty(data.LpuBuilding_id)) {
                                    wnd.form.findField('LpuBuilding_id').setValueById(data.LpuBuilding_id);
                                }
                                if (!Ext.isEmpty(data.Storage_id)) {
                                    wnd.form.findField('Storage_id').setValueById(data.Storage_id);
                                }
                                if (!Ext.isEmpty(data.Mol_id)) {
                                    wnd.form.findField('Mol_id').setValueById(data.Mol_id);
                                }
                                if (!Ext.isEmpty(data.StorageZone_id)) {
                                    wnd.form.findField('StorageZone_id').setValueById(data.StorageZone_id);
                                }
                            }
                        }
                    });
                }

				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
                if (arguments[0].params) {
                    this.form.setValues(arguments[0].params);

                    this.setFieldDateFilter(!Ext.isEmpty(arguments[0].params.CmpCallCardDrug_setDate) ? arguments[0].params.CmpCallCardDrug_setDate : null);

                    if (!Ext.isEmpty(arguments[0].params.MedStaffFact_id)) {
                        this.form.findField('MedStaffFact_id').setValueById(arguments[0].params.MedStaffFact_id);
                    }
                    if (!Ext.isEmpty(arguments[0].params.LpuBuilding_id)) {
                        this.form.findField('LpuBuilding_id').setValueById(arguments[0].params.LpuBuilding_id);
                    }
                    if (!Ext.isEmpty(arguments[0].params.Storage_id)) {
                        this.form.findField('Storage_id').setValueById(arguments[0].params.Storage_id);
                    }
                    if (!Ext.isEmpty(arguments[0].params.Mol_id)) {
                        this.form.findField('Mol_id').setValueById(arguments[0].params.Mol_id);
                    }
                    if (!Ext.isEmpty(arguments[0].params.StorageZone_id)) {
                        wnd.form.findField('StorageZone_id').setValueById(arguments[0].params.StorageZone_id);
                    }
                    if (!Ext.isEmpty(arguments[0].params.DrugPrepFas_id)) {
                        this.form.findField('DrugPrepFas_id').setValueById(arguments[0].params.DrugPrepFas_id);
                    }
                    if (!Ext.isEmpty(arguments[0].params.Drug_id)) {
                        this.form.findField('Drug_id').setValueById(arguments[0].params.Drug_id);
                        this.form.findField('GoodsUnit_id').getStore().baseParams.Drug_id = arguments[0].params.Drug_id;
                    }
                    if (!Ext.isEmpty(arguments[0].params.DocumentUcStr_oid)) {
                        this.form.findField('DocumentUcStr_oid').setValueById(arguments[0].params.DocumentUcStr_oid);
                    }
                    if (!Ext.isEmpty(arguments[0].params.GoodsUnit_id)) {
                        this.form.findField('GoodsUnit_id').setValueById(arguments[0].params.GoodsUnit_id);
                    }
                }
				loadMask.hide();
				break;
		}
	},
	initComponent: function() {
		var wnd = this;
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'CmpCallCardDrugEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				collapsible: true,
				url:'/?c=CmpCallCardDrug&m=save',
                labelWidth: 120,
                labelAlign: 'right',
				items: [{					
					xtype: 'hidden',
					name: 'CmpCallCardDrug_id'
				}, {
                    xtype: 'hidden',
                    name: 'DocumentUc_id'
                }, {
                    xtype: 'hidden',
                    name: 'DocumentUcStr_id'
                }, {
					xtype: 'textfield',
					fieldLabel: 'Пациент',
					name: 'Person_FIO',
					anchor: '50%',
                    disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: 'Бригада',
					name: 'EmergencyTeam_Name',
                    anchor: '50%',
                    disabled: true
				}, {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Врач',
                    hiddenName: 'MedStaffFact_id',
                    displayField: 'MedStaffFact_Name',
                    valueField: 'MedStaffFact_id',
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
                            { name: 'MedStaffFact_Name', mapping: 'MedStaffFact_Name' },
                            { name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
                            //{ name: 'MedPersonal_DloCode', mapping: 'MedPersonal_DloCode' },
                            { name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
                            { name: 'PostMed_Name', mapping: 'PostMed_Name' },
                            { name: 'MedStaffFact_Stavka', mapping: 'MedStaffFact_Stavka' },
                            { name: 'WorkData_begDate', mapping: 'WorkData_begDate' },
                            { name: 'WorkData_endDate', mapping: 'WorkData_endDate' },
                            { name: 'Lpu_id', mapping: 'Lpu_id' }
                        ],
                        key: 'MedStaffFact_id',
                        sortInfo: { field: 'MedStaffFact_Name' },
                        url:'/?c=CmpCallCard&m=loadMedStaffFactCombo'
                    }),
                    ownerWindow: wnd,
                    anchor: '50%',
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<table style="border: 0;">',
                        '<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
                        //'<td style="width: 45px;">{MedPersonal_DloCode}&nbsp;</td>',
                        '<td>',
                        '<div style="font-weight: bold;">{MedStaffFact_Name}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
                        '<div style="font-size: 10px;">{PostMed_Name}{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? ", ст." : ""]} {MedStaffFact_Stavka}</div>',
                        '<div style="font-size: 10px;">{[!Ext.isEmpty(values.WorkData_begDate) ? "Дата начала работы: " + values.WorkData_begDate:""]} {[!Ext.isEmpty(values.WorkData_endDate) ? "Дата увольнения: " + values.WorkData_endDate:""]}</div>',
                        '<div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && !Ext.isEmpty(values.Lpu_Name) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div>',
                        '</td>',
                        '</tr></table>',
                        '</div></tpl>'
                    )
				}, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            xtype: 'swdatefield',
                            fieldLabel: 'Дата',
                            name: 'CmpCallCardDrug_setDate',
                            allowBlank: false,
                            listeners: {
                                change: function(field, newValue) {
                                    wnd.setFieldDateFilter(!Ext.isEmpty(newValue) ? newValue.format('d.m.Y') : null, true);
                                }
                            }
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'swtimefield',
                            fieldLabel: 'Время',
                            name: 'CmpCallCardDrug_setTime',
                            plugins: [new Ext.ux.InputTextMask('99:99', true)],
                            allowBlank: false
                        }]
                    }]
                }, {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Подстанция',
                    hiddenName: 'LpuBuilding_id',
                    displayField: 'LpuBuilding_Name',
                    valueField: 'LpuBuilding_id',
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
                            { name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name' }
                        ],
                        key: 'LpuBuilding_id',
                        sortInfo: { field: 'LpuBuilding_Name' },
                        url:'/?c=CmpCallCard&m=loadLpuBuildingCombo'
                    }),
                    ownerWindow: wnd,
                    childrenList: ['Storage_id'],
                    anchor: '50%'
				}, {
					name: 'Storage_id',
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Склад',
                    hiddenName: 'Storage_id',
                    displayField: 'Storage_Name',
                    valueField: 'Storage_id',
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'Storage_id', mapping: 'Storage_id' },
                            { name: 'Storage_Name', mapping: 'Storage_Name' },
                            { name: 'StorageZone_Count', mapping: 'StorageZone_Count' }
                        ],
                        key: 'Storage_id',
                        sortInfo: { field: 'Storage_Name' },
                        url:'/?c=CmpCallCard&m=loadStorageCombo'
                    }),
                    ownerWindow: wnd,
                    childrenList: ['Mol_id', 'StorageZone_id', 'DrugPrepFas_id', 'Drug_id', 'DocumentUcStr_oid'],
                    anchor: '50%',
                    setLinkedFieldValues: function(event_name) {
                        var record_data = this.getSelectedRecordData();
                        var sz_combo = wnd.form.findField('StorageZone_id');

                        if (!Ext.isEmpty(record_data.StorageZone_Count) && record_data.StorageZone_Count > 0) {
                            sz_combo.ownerCt.show();
                        } else {
                            sz_combo.ownerCt.hide();
                        }
                    }
				}, {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'МОЛ',
                    hiddenName: 'Mol_id',
                    displayField: 'Mol_Name',
                    valueField: 'Mol_id',
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'Mol_id', mapping: 'Mol_id' },
                            { name: 'Mol_Name', mapping: 'Mol_Name' }
                        ],
                        key: 'Mol_id',
                        sortInfo: { field: 'Mol_Name' },
                        url:'/?c=CmpCallCard&m=loadMolCombo'
                    }),
                    ownerForm: wnd.form,
                    anchor: '50%'
				}, {
                    layout: 'form',
                    items: [{
                        xtype: 'swcustomownercombo',
                        fieldLabel: 'Место хранения',
                        hiddenName: 'StorageZone_id',
                        displayField: 'StorageZone_Name',
                        valueField: 'StorageZone_id',
                        allowBlank: true,
                        store: new Ext.data.SimpleStore({
                            autoLoad: false,
                            fields: [
                                { name: 'StorageZone_id', mapping: 'StorageZone_id' },
                                { name: 'StorageZone_Name', mapping: 'StorageZone_Name' }
                            ],
                            key: 'StorageZone_id',
                            sortInfo: { field: 'StorageZone_Name' },
                            url:'/?c=CmpCallCard&m=loadStorageZoneCombo'
                        }),
                        ownerWindow: wnd,
                        childrenList: ['DrugPrepFas_id', 'Drug_id', 'DocumentUcStr_oid'],
                        anchor: '50%'
                    }]
                }, {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Медикамент',
                    hiddenName: 'DrugPrepFas_id',
                    displayField: 'DrugPrepFas_Name',
                    valueField: 'DrugPrepFas_id',
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'DrugPrepFas_id', mapping: 'DrugPrepFas_id' },
                            { name: 'DrugPrepFas_Name', mapping: 'DrugPrepFas_Name' }
                        ],
                        key: 'DrugPrepFas_id',
                        sortInfo: { field: 'DrugPrepFas_Name' },
                        url:'/?c=CmpCallCard&m=loadDrugPrepFasCombo'
                    }),
                    ownerWindow: wnd,
                    childrenList: ['Drug_id'],
                    anchor: '50%'
				}, {
					xtype: 'swcustomownercombo',
                    fieldLabel: 'Упаковка',
                    hiddenName: 'Drug_id',
                    displayField: 'Drug_Nomen',
                    valueField: 'Drug_id',
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'Drug_id', mapping: 'Drug_id' },
                            { name: 'Drug_Name', mapping: 'Drug_Name' },
                            { name: 'Drug_Nomen', mapping: 'Drug_Nomen' },
                            { name: 'DrugNomen_Code', mapping: 'DrugNomen_Code' }
                        ],
                        key: 'Drug_id',
                        sortInfo: { field: 'Drug_Nomen' },
                        url:'/?c=CmpCallCard&m=loadDrugCombo'
                    }),
                    ownerWindow: wnd,
                    childrenList: ['DocumentUcStr_oid', 'GoodsUnit_id'],
                    anchor: '50%'
				},
                {
                    xtype: 'swcustomownercombo',
					fieldLabel: 'Партия',
                    hiddenName: 'DocumentUcStr_oid',
                    displayField: 'DocumentUcStr_Name',
                    valueField: 'DocumentUcStr_id',
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id' },
                            { name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name' },
                            { name: 'DrugOstatRegistry_Kolvo', mapping: 'DrugOstatRegistry_Kolvo' },
                            { name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
                            { name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
                            { name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id' },
                            { name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name' },
                            { name: 'PrepSeries_id', mapping: 'PrepSeries_id' },
                            { name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price' },
                            { name: 'PrepSeries_Ser', mapping: 'PrepSeries_Ser' },
                            { name: 'PrepSeries_GodnDate', mapping: 'PrepSeries_GodnDate' },
                            { name: 'GoodsUnit_bid', mapping: 'GoodsUnit_bid' },
                            { name: 'GoodsUnit_id', mapping: 'GoodsUnit_id' },
                            { name: 'GoodsUnit_bNick', mapping: 'GoodsUnit_bNick' },
                            { name: 'GoodsPackCount_bCount', mapping: 'GoodsPackCount_bCount' }
                        ],
                        key: 'DocumentUcStr_id',
                        url:'/?c=CmpCallCard&m=loadDocumentUcStrOidCombo'
                    }),
                    ownerWindow: wnd,
                    childrenList: [],
                    anchor: '50%',
                    tpl: new Ext.XTemplate(
                        '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
                        '<td style="padding: 2px; width: 15%;">Серия</td>',
                        '<td style="padding: 2px; width: 15%;">Срок годности</td>',
                        '<td style="padding: 2px; width: 10%;">Цена</td>',
                        '<td style="padding: 2px; width: 10%;">Остаток</td>',
                        '<td style="padding: 2px; width: 10%;">Ед. изм.</td>',
                        '<td style="padding: 2px; width: 20%;">Источник финансирования</td>',
                        '<td style="padding: 2px; width: 20%;">Статья расхода</td></tr>',
                        '<tpl for="."><tr class="x-combo-list-item">',
                        '<td style="padding: 2px;">{PrepSeries_Ser}&nbsp;</td>',
                        '<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
                        '<td style="padding: 2px;">{DocumentUcStr_Price}</td>',
                        '<td style="padding: 2px;">{DrugOstatRegistry_Kolvo}&nbsp;</td>',
                        '<td style="padding: 2px;">{GoodsUnit_bNick}&nbsp;</td>',
                        '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
                        '<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td></tr>',
                        '</tr></tpl>',
                        '</table>'
                    ),
                    setLinkedFieldValues: function(event_name) {
                        var str_data = this.getSelectedRecordData();

                        //delete wnd.form.findField('CmpCallCardDrug_Kolvo').maxValue;

                        if (event_name == 'clear') {
                            wnd.form.findField('Ost_Kolvo').setValue(null);
                            wnd.form.findField('Kolvo_GoodsUnit_id').setValue(null);
                            wnd.form.findField('GoodsUnit_id').setValue(null);
                            wnd.form.findField('GoodsPackCount_bCount').setValue(null);
                            wnd.setOstKolvoUnit();
                        } else {
                            var ost_kolvo = str_data.DrugOstatRegistry_Kolvo;

                            //проверяем не списывали ли мы с этой партии во вновь добавленных строках
                            if (wnd.owner) {
                                var kolvo = 0;
                                var cccd_id = wnd.form.findField('CmpCallCardDrug_id').getValue();
                                wnd.owner.getGrid().getStore().each(function(record) {
                                    if (record.get('DocumentUcStr_oid') == str_data.DocumentUcStr_id && record.get('state') == 'add' && record.get('CmpCallCardDrug_id') != cccd_id && record.get('CmpCallCardDrug_Kolvo') > 0) {
                                        kolvo += record.get('CmpCallCardDrug_Kolvo')*1;
                                    }
                                });

                                if (ost_kolvo > 0) {
                                    ost_kolvo = kolvo <= ost_kolvo ? ost_kolvo - kolvo : 0;
                                }
                            }

                            wnd.form.findField('Ost_Kolvo').setValue(ost_kolvo);
                            wnd.form.findField('Kolvo_GoodsUnit_id').setValue(str_data.GoodsUnit_bid);
                            wnd.form.findField('GoodsUnit_id').setValueById(str_data.GoodsUnit_id);
                            wnd.form.findField('GoodsPackCount_bCount').setValue(str_data.GoodsPackCount_bCount);
                            //wnd.form.findField('CmpCallCardDrug_Kolvo').maxValue = ost_kolvo;
                            wnd.setOstKolvoUnit();
                        }

                        if (wnd.show_diff_gu) {
                            wnd.setKolvo();
                        }

                        if (event_name == 'change' && !Ext.isEmpty(str_data.DocumentUcStr_Price)) {
                            wnd.form.findField('CmpCallCardDrug_Cost').setValue(str_data.DocumentUcStr_Price);
                            wnd.setSum();
                        }
                    },
                    onLoadData: function() {
                        if(this.getStore().getCount() > 0 && this.getStore().getAt(0).get('DocumentUcStr_id') != this.getValue()) {
                            this.setValue(this.getStore().getAt(0).get('DocumentUcStr_id'));
                            this.setLinkedFieldValues('change');
                        }
                    }
				}, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            xtype: 'swcommonsprcombo',
                            comboSubject: 'GoodsUnit',
                            fieldLabel: 'Ед. учета',
                            hiddenName: 'Kolvo_GoodsUnit_id',
                            width: 150,
                            disabled: true
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'swcustomownercombo',
                            fieldLabel: 'Ед.спис.',
                            hiddenName: 'GoodsUnit_id',
                            displayField: 'GoodsUnit_Name',
                            valueField: 'GoodsUnit_id',
                            allowBlank: false,
                            anchor: null,
                            width: 150,
                            store: new Ext.data.SimpleStore({
                                autoLoad: false,
                                fields: [
                                    { name: 'GoodsUnit_id', mapping: 'GoodsUnit_id' },
                                    { name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name' },
                                    { name: 'GoodsPackCount_Count', mapping: 'GoodsPackCount_Count' }
                                ],
                                key: 'GoodsUnit_id',
                                sortInfo: { field: 'GoodsUnit_Name' },
                                url:'/?c=CmpCallCard&m=loadGoodsUnitCombo'
                            }),
                            ownerWindow: wnd,
                            childrenList: [],
                            onLoadData: function() {
                                var combo = this;
                                var cnt = this.getStore().getCount();
                                if(cnt > 0) {
                                    var idx = this.getStore().findBy(function(record) {
                                        return (record.get('GoodsUnit_Name') == 'упаковка');
                                    })
                                    if (idx > -1 && this.getStore().getAt(idx).get('GoodsUnit_id') != this.getValue()) {
                                        this.setValue(this.getStore().getAt(idx).get('GoodsUnit_id'));
                                        this.setLinkedFieldValues('change');
                                    }
                                }
                            },
                            setLinkedFieldValues: function(event_name) {
                                var gu_data = this.getSelectedRecordData();
                                var kolvo_field = wnd.form.findField('CmpCallCardDrug_Kolvo');
                                var kolvo_u_field = wnd.form.findField('CmpCallCardDrug_KolvoUnit');
                                var kolvo = kolvo_field.getValue()*1 > 0 ? kolvo_field.getValue()*1 : 0;
                                var kolvo_u = kolvo_u_field.getValue()*1 > 0 ? kolvo_u_field.getValue()*1 : 0;

                                if (event_name == 'change' || event_name == 'clear') {
                                    wnd.form.findField('GoodsPackCount_Count').setValue(!Ext.isEmpty(gu_data.GoodsPackCount_Count) ? gu_data.GoodsPackCount_Count : null);

                                    if (kolvo > 0) {
                                        wnd.setKolvoUnit();
                                    } else if (kolvo_u > 0) {
                                        wnd.setKolvo();
                                    }
                                    wnd.setOstKolvoUnit();
                                }
                                if (event_name == 'set_by_id') {
                                    wnd.form.findField('GoodsPackCount_Count').setValue(!Ext.isEmpty(gu_data.GoodsPackCount_Count) ? gu_data.GoodsPackCount_Count : null);
                                    wnd.setOstKolvoUnit();
                                }
                            }
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'hidden',
                            name: 'GoodsPackCount_bCount' //Кол-во в ед уч. упак.
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Кол-во в упак.',
                            name: 'GoodsPackCount_Count',
                            disabled: true,
                            width: 150
                        }]
                    }]
                }, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Остаток (ед.уч.)',
                            name: 'Ost_Kolvo',
                            disabled: true,
                            width: 150
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Остаток (ед.спис.)',
                            name: 'Ost_KolvoUnit',
                            disabled: true,
                            width: 150
                        }]
                    }]
                }, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Кол-во (ед.уч.)',
                            name: 'CmpCallCardDrug_Kolvo',
                            decimalPrecision: 3,
                            allowNegative: false,
                            allowBlank: false,
                            width: 150,
                            listeners: {
                                'change': function() {
                                    wnd.setKolvoUnit();
                                    wnd.setSum();
                                }
                            }
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Кол-во (ед.спис.)',
                            decimalPrecision: 3,
                            name: 'CmpCallCardDrug_KolvoUnit',
                            allowNegative: false,
                            width: 150,
                            listeners: {
                                'change': function() {
                                    wnd.setKolvo();
                                }
                            }
                        }]
                    }]
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Цена',
                    name: 'CmpCallCardDrug_Cost',
                    allowNegative: false,
                    listeners: {
                        'change': function() {
                            wnd.setSum();
                        }
                    }
                }, {
					xtype: 'numberfield',
					fieldLabel: 'Сумма',
					name: 'CmpCallCardDrug_Sum',
                    allowNegative: false,
                    listeners: {
                        'change': function() {
                            wnd.setCost();
                        }
                    }
				}/*, {
					xtype: 'textfield',
					fieldLabel: 'Текст',
					name: 'Text'
				}*/]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swCmpCallCardDrugEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('CmpCallCardDrugEditForm').getForm();
	}	
});