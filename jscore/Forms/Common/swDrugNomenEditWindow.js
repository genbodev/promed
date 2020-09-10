/**
* swDrugNomenEditWindow - окно просмотра, добавления и редактирования номенклатурного препарата
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @author       Dmitry Vlasenko
*/

/*NO PARSE JSON*/
sw.Promed.swDrugNomenEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swDrugNomenEditWindow',
	objectSrc: '/jscore/Forms/Common/swDrugNomenEditWindow.js',
	id: 'swDrugNomenEditWindow',
	layout: 'border',
	maximized: true,
	maximizable: false,
    setDisabled: function(disable) {
        var form = this.formPanel.getForm();

        this.enableEdit(!disable);
        this.edit_disabled = disable;

        var field_arr = [
            'DrugNomen_Code',
            'DrugComplexMnnCode_Code',
            'DrugComplexMnn_LatName'
        ];

        for (var i in field_arr) if (form.findField(field_arr[i])) {
            var field = form.findField(field_arr[i]);
            if (disable || field.enable_blocked) {
                field.disable();
            } else {
                field.enable();
            }
        }

        this.GoodsPackCountListGrid.setReadOnly(disable);
        //this.DrugPrepEdUcCountListGrid.setReadOnly(disable);
    },
    setSprType: function() {
        var base_form = this.formPanel.getForm();
        var spr_type = this.sprtype_combo.getValue();

        switch(spr_type) {
            case 'llo_nom':
            case 'org_nom':
                base_form.findField('DrugNomenOrgLink_Code').showContainer();
                break;
            case 'reg_nom':
            default:
                base_form.findField('DrugNomenOrgLink_Code').hideContainer();
                break;
        }

        base_form.findField('DrugComplexMnnCode_Code').enable_blocked = (spr_type == 'org_nom');

        this.setDrugNomenOrgLinkCode();
        this.setDrugPrepFasCodeCode();
        this.GoodsPackCountListGrid.setParam('Org_id', this.getDrugNomenOrgLinkOrgId(), true);
        //this.DrugPrepEdUcCountListGrid.setParam('Org_id', this.getDrugNomenOrgLinkOrgId(), true);
        this.generateDrugComplexMnnCode();
    },
    getDrugNomenOrgLinkOrgId: function() {
        var org_id = null;

        switch(this.sprtype_combo.getValue()) {
            case 'org_nom':
                org_id = getGlobalOptions().org_id;
                break;
            case 'llo_nom':
                org_id = getGlobalOptions().minzdrav_org_id;
                break;
        }

        return org_id;
    },
    setDrugNomenOrgLinkCode: function() {
        var code = null;
        var org_id = this.getDrugNomenOrgLinkOrgId();

        if (!Ext.isEmpty(this.DrugNomenOrgLink_Data) && !Ext.isEmpty(this.DrugNomenOrgLink_Data[org_id])) {
            code = this.DrugNomenOrgLink_Data[org_id];
        }

        this.formPanel.getForm().findField('DrugNomenOrgLink_Code').setValue(code);
    },
    setDrugPrepFasCodeCode: function() {
        var code = null;
        var org_id = this.getDrugNomenOrgLinkOrgId();
        var code =  null;

        if (!Ext.isEmpty(this.DrugPrepFasCode_Data)) {
            code = !Ext.isEmpty(this.DrugPrepFasCode_Data[org_id > 0 ? org_id : 'reg']) ? this.DrugPrepFasCode_Data[org_id > 0 ? org_id : 'reg'] : null;
        }

        this.formPanel.getForm().findField('DrugPrepFasCode_Code').setValue(code);
    },
    loadDrugNomenOrgLinkData: function(str) {
        this.DrugNomenOrgLink_Data = new Object();

        if (!Ext.isEmpty(str)) {
            var data_arr = str.split('|::|');
            for(var i = 0; i < data_arr.length; i++) {
                var dnol = data_arr[i].split('|');
                if (dnol.length == 2 && dnol[0] > 0 && !Ext.isEmpty(dnol[1])) {
                    this.DrugNomenOrgLink_Data[dnol[0]] = dnol[1];
                }
            }
        }

        this.setDrugNomenOrgLinkCode();
    },
    loadDrugPrepFasCodeData: function(str) {
        this.DrugPrepFasCode_Data = new Object();

        if (!Ext.isEmpty(str)) {
            var data_arr = str.split('|::|');
            for(var i = 0; i < data_arr.length; i++) {
                var dpfc = data_arr[i].split('|');
                if (dpfc.length == 2 && (dpfc[0] > 0 || dpfc[0] == 'reg') && !Ext.isEmpty(dpfc[1])) {
                    this.DrugPrepFasCode_Data[dpfc[0]] = dpfc[1];
                }
            }
        }

        this.setDrugPrepFasCodeCode();
    },
	generateCode: function(object) {
		if (!object) {
			return;
		}

		var params = new Object();
		var code_field = '';
		var base_form = this.formPanel.getForm();

		params.Object = object;
        params.Org_id = this.getDrugNomenOrgLinkOrgId();
        params.Drug_id = base_form.findField('Drug_id').getValue();
		code_field = object+'_Code';

		var Mask = new Ext.LoadMask(Ext.get(this.id), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
		Mask.show();

		Ext.Ajax.request({
			params: params,
			callback: function(opt, success, resp) {
				Mask.hide();

				var response_obj = Ext.util.JSON.decode(resp.responseText);

				if (response_obj[code_field] != '')
				{
					base_form.findField(code_field).setValue(response_obj[code_field]);
				}
			}.createDelegate(this.ownerCt.ownerCt),
			url: '/?c=DrugNomen&m=generateCodeForObject'
		});
	},
    generateDrugComplexMnnCode: function(){
        var base_form = this.formPanel.getForm();
        var spr_type = this.sprtype_combo.getValue();
        var dcmc_code = base_form.findField('DrugComplexMnnCode_Code').getValue();
        if(this.action == 'add' && Ext.isEmpty(dcmc_code) && spr_type == 'org_nom'){
            this.generateCode('DrugComplexMnnCode');
        }
    },
	convertFieldValue: function(inp_value) {
		var value = inp_value;
		var regexp = null;

		regexp = new RegExp('<[^>]+>','g');
		value = value.replace(regexp, '');

		regexp = new RegExp('&[^;]+;','g');
		value = value.replace(regexp, function(chr) {
			return htmlentities.decode(chr);
		});

		return value;
	},
	getDrugNomenData: function(drug_id) {
		if (!drug_id) {
			return;
		}

		var form = this;
		var base_form = this.formPanel.getForm();
		var params = new Object();
		params.Drug_id = drug_id;

		var Mask = new Ext.LoadMask(Ext.get(this.id), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
		Mask.show();

		Ext.Ajax.request({
			params: params,
			callback: function(opt, success, resp) {
				Mask.hide();

				var response_obj = Ext.util.JSON.decode(resp.responseText);

				if (Ext.isArray(response_obj)) {
					var data = response_obj[0];
					if (data.Tradenames_RusName) {
						data.Tradenames_RusName = form.convertFieldValue(data.Tradenames_RusName);
					}
					if (data.DrugComplexMnn_RusName) {
						data.DrugComplexMnn_RusName = form.convertFieldValue(data.DrugComplexMnn_RusName);
					}
					if (data.Tradenames_LatName) {
						data.Tradenames_LatName = form.convertFieldValue(data.Tradenames_LatName);
					}
					/*if (data.DrugComplexMnn_LatName) {
						data.DrugComplexMnn_LatName = form.convertFieldValue(data.DrugComplexMnn_LatName);
					}*/

					form.loadDrugNomenOrgLinkData(data.DrugNomenOrgLink_Data);
					form.loadDrugPrepFasCodeData(data.DrugPrepFasCode_Data);

					for(var field in data) {
						if (base_form.findField(field))
							base_form.findField(field).setValue(data[field]);
					}
					if (data.Actmatters_id) {
						base_form.findField('DrugMnnCode_Code').setAllowBlank(false);
					} else {
						base_form.findField('DrugMnnCode_Code').setAllowBlank(true);
					}
					if (data.Tradenames_id) {
						base_form.findField('DrugTorgCode_Code').setAllowBlank(false);
					} else {
						base_form.findField('DrugTorgCode_Code').setAllowBlank(true);
					}
					if (data.DrugComplexMnn_id) {
						base_form.findField('DrugComplexMnnCode_Code').setAllowBlank(false);
                        form.generateDrugComplexMnnCode();
					} else {
						base_form.findField('DrugComplexMnnCode_Code').setAllowBlank(true);
					}
					if (data.Extemporal_id) {
                        form.findById('ExtemporalFieldset').show();
                        form.ExtemporalCompListGrid.getGrid().getStore().load({params:{Extemporal_id:data.Extemporal_id}});
                    } else {
                        form.findById('ExtemporalFieldset').hide();
                    }
				}
			}.createDelegate(this.ownerCt.ownerCt),
			url: '/?c=DrugNomen&m=getDrugNomenData'
		});
	},
	doSave: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert(langs('Ошибка заполнения формы'), langs('Проверьте правильность заполнения полей формы.'));
			return false;
		}

		params.Drug_id = form.findField('Drug_id').getValue();
		params.DrugNomen_Code = form.findField('DrugNomen_Code').getValue();
		params.DrugComplexMnnCode_Code = form.findField('DrugComplexMnnCode_Code').getValue();
		params.DrugComplexMnnCode_DosKurs = form.findField('DrugComplexMnnCode_DosKurs').getValue();
        params.DrugNomenOrgLink_Org_id = this.getDrugNomenOrgLinkOrgId();

        //данные по кодам группировочного торгового наименования
        var dpfc_arr = new Array();
        dpfc_arr.push({
            Org_id: this.getDrugNomenOrgLinkOrgId(),
            DrugPrepFasCode_Code: form.findField('DrugPrepFasCode_Code').getValue()
        });
        params.DrugPrepFasCode_JsonData = Ext.util.JSON.encode(dpfc_arr);

		win.getLoadMask(langs('Подождите, сохраняется запись...')).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.action = 'edit';
				var data = {};
				if (action.result.success) {
					var id = action.result.DrugNomen_id;
					form.findField('DrugNomen_id').setValue(id);
					data.DrugNomen_id = id;
					win.owner.refreshRecords(win.owner, id);
					win.hide();
				}
			}
		});
	},
    doSaveDrugVznData: function() {
	    var wnd = this;
        var form = this.formPanel.getForm();
        var params = new Object();

        params.Drug_id = form.findField('Drug_id').getValue();
        params.DrugVZN_fid = form.findField('DrugVZN_fid').getValue();
        params.DrugFormVZN_id = form.findField('DrugFormVZN_id').getValue();
        params.DrugDose_id = form.findField('DrugDose_id').getValue();
        params.DrugKolDose_id = form.findField('DrugKolDose_id').getValue();
        params.DrugRelease_id = form.findField('DrugRelease_id').getValue();

        if (!Ext.isEmpty(params.Drug_id) && !Ext.isEmpty(params.DrugVZN_fid) && !Ext.isEmpty(params.DrugFormVZN_id) &&!Ext.isEmpty(params.DrugDose_id) && !Ext.isEmpty(params.DrugKolDose_id) && !Ext.isEmpty(params.DrugRelease_id)) {
            var load_mask = new Ext.LoadMask(Ext.get(this.id), { msg: "Пожалуйста, подождите, идет сохранение..." });
            load_mask.show();

            Ext.Ajax.request({
                params: params,
                callback: function(opt, success, resp) {
                    var response_obj = Ext.util.JSON.decode(resp.responseText);

                    if (!Ext.isEmpty(response_obj) && response_obj.success) {
                        Ext.Msg.alert(langs('Сообщение'), langs('Данные успешно сохранены'));
                    }

                    load_mask.hide();
                }.createDelegate(this.ownerCt.ownerCt),
                url: '/?c=DrugNomen&m=saveDrugVznData'
            });
        } else {
            sw.swMsg.alert(langs('Ошибка'), 'Для формирования выгрузки на портал по ВЗН заполнены не все данные. Сохранение не может быть выполнено');
        }
    },
    doLoadDrugVznData: function(drug_id) {
        var wnd = this;
        var form = this.formPanel.getForm();
        var vzn_data = new Object();

        if (Ext.isEmpty(drug_id)) {
            drug_id = form.findField('Drug_id').getValue();
        }

        vzn_data.DrugVZN_fid = null;
        vzn_data.DrugFormVZN_id = null;
        vzn_data.DrugDose_id = null;
        vzn_data.DrugKolDose_id = null;
        vzn_data.DrugRelease_id = null;
        vzn_data.DrugMnnVZN_Code = null;
        vzn_data.TradeNamesVZN_Code = null;
        vzn_data.DrugFormVZN_Code = null;

        if (!Ext.isEmpty(drug_id)) {
            var load_mask = new Ext.LoadMask(Ext.get(this.id), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
            load_mask.show();

            Ext.Ajax.request({
                params: {
                    Drug_id: drug_id
                },
                callback: function(opt, success, resp) {
                    var response_obj = Ext.util.JSON.decode(resp.responseText);

                    if (!Ext.isEmpty(response_obj[0])) {
                        Ext.apply(vzn_data, response_obj[0])
                    }

                    form.setValues(vzn_data);

                    if (!Ext.isEmpty(vzn_data.DrugVZN_fid) && !Ext.isEmpty(vzn_data.DrugFormVZN_id) && !Ext.isEmpty(vzn_data.DrugRelease_id) && !wnd.edit_disabled) {
                        wnd.nsi_dd_combo.enable();
                        if (!Ext.isEmpty(vzn_data.DrugDose_id)) {
                            wnd.nsi_dkd_combo.enable();
                        } else {
                            wnd.nsi_dkd_combo.disable();
                        }
                    } else {
                        wnd.nsi_dd_combo.disable();
                        wnd.nsi_dkd_combo.disable();
                    }

                    if (!Ext.isEmpty(vzn_data.DrugDose_id)) {
                        wnd.nsi_dd_combo.setValueById(vzn_data.DrugDose_id);
                    }
                    if (!Ext.isEmpty(vzn_data.DrugKolDose_id)) {
                        wnd.nsi_dkd_combo.setValueById(vzn_data.DrugKolDose_id);
                    }

                    load_mask.hide();
                }.createDelegate(this.ownerCt.ownerCt),
                url: '/?c=DrugNomen&m=loadDrugVznData'
            });
        } else {
            form.setValues(vzn_data);
        }
    },
	deleteGoodsPackCount: function(){
		var record = this.GoodsPackCountListGrid.getGrid().getSelectionModel().getSelected();
		if( !record ) {
			return false;
		}
        if ((record.get('Org_id') > 0 && record.get('Org_id') == getGlobalOptions().org_id) || isSuperAdmin()) {
            Ext.Msg.show({
                title: langs('Внимание'),
                msg: 'Вы действительно хотите удалить выбранную запись?',
                buttons: Ext.Msg.YESNO,
                fn: function(btn) {
                    if (btn === 'yes') {
                        Ext.Ajax.request({
                            scope: this,
                            url: '/?c=DrugNomen&m=deleteGoodsPackCount',
                            params: {
                                GoodsUnit_id: record.get('GoodsUnit_id'),
                                GoodsPackCount_Count: record.get('GoodsPackCount_Count'),
                                GoodsPackCount_id: record.get('GoodsPackCount_id'),
                                DrugComplexMnn_id: record.get('DrugComplexMnn_id')
                            },
                            success: function(response) {
                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                if(response_obj[0] == true){
                                    this.GoodsPackCountListGrid.getAction('action_refresh').execute();
                                } else if(response_obj[0] == false && response_obj[1] == 400) {
                                    sw.swMsg.alert(langs('Ошибка'), 'Удаление невозможно, т.к. существуют связанные записи');
                                    return false;
                                } else if(response_obj[0] == false) {
                                    sw.swMsg.alert(langs('Ошибка'), 'Не удалось удалить запись');
                                    return false;
                                }
                            }.createDelegate(this)
                        });
                    }
                }.createDelegate(this),
                icon: Ext.MessageBox.WARNING
            });
        }
	},
	deleteDrugPrepEdUcCount: function(){
		var record = this.DrugPrepEdUcCountListGrid.getGrid().getSelectionModel().getSelected();
		if( !record ) {
			return false;
		}
        if ((record.get('Org_id') > 0 && record.get('Org_id') == getGlobalOptions().org_id) || isSuperAdmin()) {
            Ext.Msg.show({
                title: langs('Внимание'),
                msg: 'Вы действительно хотите удалить выбранную запись?',
                buttons: Ext.Msg.YESNO,
                fn: function(btn) {
                    if (btn === 'yes') {
                        Ext.Ajax.request({
                            scope: this,
                            url: '/?c=DrugNomen&m=deleteDrugPrepEdUcCount',
                            params: {
                                GoodsUnit_id: record.get('GoodsUnit_id'),
                                DrugPrepEdUcCount_Count: record.get('DrugPrepEdUcCount_Count'),
                                DrugPrepEdUcCount_id: record.get('DrugPrepEdUcCount_id'),
                                DrugPrepFas_id: record.get('DrugPrepFas_id'),
                                Org_id: getGlobalOptions().org_id
                            },
                            success: function(response) {
                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                if(response_obj[0] == true){
                                    this.DrugPrepEdUcCountListGrid.getAction('action_refresh').execute();
                                } else if(response_obj[0] == false && response_obj[1] == 400) {
                                    sw.swMsg.alert(langs('Ошибка'), 'Удаление невозможно, т.к. существуют связанные записи');
                                    return false;
                                } else if(response_obj[0] == false && response_obj[1] == 500) {
                                    sw.swMsg.alert(langs('Ошибка'), 'У Вас нет прав на удаление данных');
                                    return false;
                                } else if(response_obj[0] == false) {
                                    sw.swMsg.alert(langs('Ошибка'), 'Не удалось удалить запись');
                                    return false;
                                }
                            }.createDelegate(this)
                        });
                    }
                }.createDelegate(this),
                icon: Ext.MessageBox.WARNING
            });
        }
	},
	listeners: {
		'resize': function() {
			if(this.layout.layout)
				this.doLayout();
		}
	},
	initComponent: function() {
		var win = this;

        this.sprtype_combo = new sw.Promed.SwBaseLocalCombo({
            hiddenName: 'SprType_Code',
            valueField: 'SprType_Code',
            displayField: 'SprType_Name',
            fieldLabel: 'Тип справочника',
            allowBlank: false,
            editable: false,
            width: 200,
            store: new Ext.data.SimpleStore({
                key: 'SprType_id',
                autoLoad: false,
                fields: [
                    {name: 'SprType_id', type: 'int'},
                    {name: 'SprType_Code', type: 'string'},
                    {name: 'SprType_Name', type: 'string'}
                ],
                data: [
                    [1, 'reg_nom', 'Региональная номенклатура'],
                    [2, 'org_nom', 'Номенклатура организации'],
                    [3, 'llo_nom', 'Номенклатура ЛЛО']
                ]
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{SprType_Name}&nbsp;',
                '</div></tpl>'
            ),
            listeners: {
                select: function() {
                    win.setSprType();
                    win.GoodsPackCountListGrid.loadData();
                    //win.DrugPrepEdUcCountListGrid.loadData();
                    win.setDisabled(win.action == 'view');
                }
            }
        });

        this.nsi_dd_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Дозировка'),
            hiddenName: 'DrugDose_id',
            displayField: 'DrugDose_Name',
            valueField: 'DrugDose_id',
            editable: true,
            allowBlank: true,
            listWidth: 200,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'DrugDose_id'
                }, [
                    {name: 'DrugDose_id', mapping: 'DrugDose_id'},
                    {name: 'DrugDose_Name', mapping: 'DrugDose_Name'}
                ]),
                url: '/?c=DrugNomen&m=loadNsiDrugDoseCombo'
            }),
            setLinkedFieldValues: function() {
                if (!this.disabled && !Ext.isEmpty(this.getValue())) {
                    win.nsi_dkd_combo.enable();
                } else {
                    win.nsi_dkd_combo.disable();
                }
            }
        });

        this.nsi_dkd_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Кол-во доз в уп.'),
            hiddenName: 'DrugKolDose_id',
            displayField: 'DrugKolDose_Name',
            valueField: 'DrugKolDose_id',
            editable: true,
            allowBlank: true,
            listWidth: 200,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'DrugKolDose_id'
                }, [
                    {name: 'DrugKolDose_id', mapping: 'DrugKolDose_id'},
                    {name: 'DrugKolDose_Name', mapping: 'DrugKolDose_Name'}
                ]),
                url: '/?c=DrugNomen&m=loadNsiDrugKolDoseCombo'
            })
        });

        this.ExtemporalCompListGrid = new sw.Promed.ViewFrame( {
            autoLoadData: false,
            dataUrl: '/?c=Extemporal&m=loadExtemporalCompList',
            object: 'ExtemporalComp',
            title: '',
            height: 155,
            width: 605,
            frame: true,
            style: 'margin-bottom: 6px;',
            editformclassname: 'swExtemporalCompEditWindow',
            actions: [
                {name:'action_add', disabled: true, hidden: true},
                {name:'action_edit', disabled: true, hidden: true},
                {name:'action_view'},
                {name:'action_delete', disabled: true, hidden: true },
                {name:'action_refresh', disabled: true, hidden: true},
                {name:'action_print', disabled: true, hidden: true}
            ],
            stringfields: [
                { name: 'ExtemporalComp_id', type: 'int', header: 'ID', key: true },
                { name: 'ExtemporalComp_Code', type: 'int', header: 'Код', width: 80 },
                { name: 'ExtemporalComp_Name', type: 'string', header: 'Наименование', id: 'autoexpand' },
                { name: 'ExtemporalComp_LatName', type: 'string', header: 'На латинском языке', width: 180 },
                { name: 'ExtemporalComp_Count', type: 'string', header: 'Количество', width: 160 },
                { name: 'GoodsUnit_id', type: 'int', hidden: true },
                { name: 'GoodsUnit_Name', type: 'string', header: 'Ед.измерения', width: 160 },
                { name: 'ExtemporalCompType_id', type: 'int', hidden: true },
                { name: 'ExtemporalCompType_Name', type: 'string', header: 'Вид компонента', width: 180 },
                { name: 'RlsActMatters_id', type: 'int', hidden: true },
                { name: 'RlsTradenames_id', type: 'int', hidden: true },
                { name: 'Extemporal_id', type: 'int', hidden: true }
            ]
        });

		this.PropertiesPanel = new sw.Promed.Panel({
			layout: 'form',
			id: 'dnew_PropertiesPanel',
			border: false,
			frame: true,
			autoHeight: true,
			items: [{
				xtype: 'fieldset',
				style: 'padding-top: 3px; padding-bottom: 3px;',
				autoHeight: true,
				title: langs('МНН'),
				items: [
					{
						name: 'DrugMnnCode_id',
						xtype: 'hidden'
					},
					{
						name: 'Actmatters_id',
						xtype: 'hidden'
					},
					{
						name: 'Drug_Fas',
						xtype: 'hidden'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: langs('Код'),
						width: 200,
						name: 'DrugMnnCode_Code',
						onTriggerClick: function() {
                            if (!this.disabled) {
							    win.generateCode('DrugMnnCode');
                            }
						},
						triggerClass: 'x-form-plus-trigger',
						validateOnBlur: false,
						xtype: 'trigger'
					}, {
                        fieldLabel: langs('Наим.'),
                        disabled: true,
                        width: 500,
                        name: 'Actmatters_RusName',
                        xtype: 'textfield'
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                fieldLabel: langs('Лат. наим.'),
                                disabled: true,
                                width: 240,
                                name: 'Actmatters_LatN',
                                xtype: 'textfield'
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 15,
                            labelAlign: 'right',
                            items: [{
                                fieldLabel: '/',
                                labelSeparator: null,
                                disabled: !(isSuperAdmin() || haveArmType('minzdravdlo')),
                                width: 240,
                                name: 'Actmatters_LatName',
                                xtype: 'textfield'
                            }]
                        }]
                    }
				]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				style: 'padding-top: 3px; padding-bottom: 3px;',
				title: langs('Торговое наименование'),
				items: [{
                    name: 'DrugTorgCode_id',
                    xtype: 'hidden'
                }, {
                    name: 'Tradenames_id',
                    xtype: 'hidden'
                }, {
                    name: 'Tradenames_LatName_id',
                    xtype: 'hidden'
                }, {
                    allowBlank: false,
                    enableKeyEvents: true,
                    fieldLabel: langs('Код'),
                    width: 200,
                    name: 'DrugTorgCode_Code',
                    onTriggerClick: function() {
                        if (!this.disabled) {
                            win.generateCode('DrugTorgCode');
                        }
                    },
                    triggerClass: 'x-form-plus-trigger',
                    validateOnBlur: false,
                    xtype: 'trigger'
                }, {
                    fieldLabel: langs('Наим.'),
                    disabled: true,
                    width: 500,
                    name: 'Tradenames_RusName',
                    xtype: 'textfield'
                }, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            fieldLabel: langs('Лат. наим.'),
                            disabled: true,
                            width: 240,
                            name: 'Tradenames_LatN',
                            xtype: 'textfield'
                        }]
                    }, {
                        layout: 'form',
                        labelWidth: 15,
                        labelAlign: 'right',
                        items: [{
                            fieldLabel: '/',
                            labelSeparator: null,
                            disabled: !(isSuperAdmin() || haveArmType('minzdravdlo')),
                            width: 240,
                            name: 'Tradenames_LatName',
                            xtype: 'textfield'
                        }]
                    }]
                }]
			}, /*{
				xtype: 'fieldset',
				autoHeight: true,
				style: 'padding-top: 3px; padding-bottom: 3px;',
				title: langs('Комплексное МНН'),
				items: [
					{
						name: 'DrugComplexMnnCode_id',
						xtype: 'hidden'
					},
					{
						name: 'DrugComplexMnn_id',
						xtype: 'hidden'
					}, {
						fieldLabel: langs('Компл. МНН'),
						disabled: true,
						width: 500,
						name: 'DrugComplexMnn_RusName',
						xtype: 'textfield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: langs('Код компл. МНН'),
						width: 500,
						name: 'DrugComplexMnnCode_Code',
						onTriggerClick: function() {
							win.generateCode('DrugComplexMnnCode');
						}.createDelegate(this),
						triggerClass: 'x-form-plus-trigger',
						validateOnBlur: false,
						xtype: 'trigger'
					}, {
						fieldLabel: langs('Лат. наим.'),
						disabled: true,
						width: 500,
						name: 'DrugComplexMnn_LatName',
						xtype: 'textfield'
					}
				]
			},*/ {
				xtype: 'fieldset',
				autoHeight: true,
				style: 'padding-top: 3px; padding-bottom: 3px;',
				title: langs('Форма выпуска'),
				items: [
					{
						name: 'Clsdrugforms_id',
						xtype: 'hidden'
					},
					{
						fieldLabel: langs('Форма выпуска'),
						disabled: true,
						width: 500,
						name: 'Clsdrugforms_RusName',
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Лат. наим.'),
						disabled: !(isSuperAdmin() || haveArmType('minzdravdlo')),
						width: 500,
						name: 'Clsdrugforms_LatName',
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Сокр. лат. наим.'),
						disabled: !(isSuperAdmin() || haveArmType('minzdravdlo')),
						width: 500,
						name: 'Clsdrugforms_LatNameSocr',
						xtype: 'textfield'
					}
				]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				style: 'padding-top: 3px; padding-bottom: 3px;',
				title: langs('Дозировка'),
				items: [
					{
						name: 'Unit_id',
						xtype: 'hidden'
					},
					{
						name: 'Unit_table',
						xtype: 'hidden'
					},
					{
						fieldLabel: langs('Дозировка'),
						disabled: true,
						width: 200,
						name: 'Unit_Value',
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Ед. изм.'),
						disabled: true,
						width: 500,
						name: 'Unit_RusName',
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Лат. наим.'),
						disabled: !(isSuperAdmin() || haveArmType('minzdravdlo')),
						width: 500,
						name: 'Unit_LatName',
						xtype: 'textfield'
					}
				]
			}, {
                xtype: 'fieldset',
                autoHeight: true,
                id: 'ExtemporalFieldset',
                style: 'padding-top: 3px; padding-bottom: 3px;',
                title: langs('Экстемпоральная рецептура'),
                items: [{
                    name: 'Extemporal_id',
                    xtype: 'hidden'
                }, {
                    layout: 'column',
                    items:[{
                        layout: 'form',
                        items:[{
                            fieldLabel: langs('Наименование'),
                            disabled: true,
                            width: 200,
                            name: 'Extemporal_Name',
                            xtype: 'textfield'
                        }]
                    }, {
                        layout: 'form',
                        style: 'padding-left: 10px;',
                        items:[
                            new Ext.Button({
                                text: langs('Просмотреть'),
                                disabled: false,
                                handler: function()
                                {
                                    var Extemporal_id = this.getFormPanel().find('name','Extemporal_id')[0].getValue();
                                    if(Extemporal_id){
                                        getWnd('swExtemporalEditWindow').show({
                                            Extemporal_id:Extemporal_id,
                                            fromNomenSpr:true,
                                            action:'view'
                                        });
                                    }
                                }.createDelegate(this)
                            })
                        ]
                    }]
                },
                this.ExtemporalCompListGrid    
                ]
            }]
		});

		this.StaticCodesPanel = new sw.Promed.Panel({
			layout: 'form',
			id: 'dnew_StaticCodesPanel',
			border: false,
			frame: true,
			autoHeight: true,
			labelWidth: 191,
			items: [{
				width: 500,
				fieldLabel: langs('ОКПД'),
				hiddenName: 'Okpd_id',
				xtype: 'swokpdcombo'
			}, {
				xtype: 'fieldset',
				style: 'padding-top: 3px; padding-bottom: 3px;',
				autoHeight: true,
				title: langs('Для сайта Росздравнадзора'),
				labelWidth: 180,
				items: [
				new Ext.form.TwinTriggerField ({
					displayField:'DrugRPN_id',
					name: 'DrugRPN_id',
					valueField: 'DrugRPN_id',
					readOnly: true,
					width: 500,
					trigger1Class: 'x-form-search-trigger',
					trigger2Class: 'x-form-clear-trigger',
					fieldLabel: langs('Код РЗН'),
					onTrigger1Click: function() {
						var base_form = win.formPanel.getForm();
						var searchWindow = 'swDrugRMZSearchWindow';

						getWnd(searchWindow).show({
							Reg_Num: base_form.findField('Reg_Num').getValue(),
							Drug_Ean: base_form.findField('Drug_Ean').getValue(),
							Drug_Fas: base_form.findField('Drug_Fas').getValue(),
							onSelect: function(data) {
								base_form.findField('DrugRMZ_id').setValue('DrugRMZ_id');
								base_form.findField('DrugRMZ_id').setValue(data.DrugRMZ_id);
								base_form.findField('DrugRPN_id').setValue(data.DrugRPN_id);
								base_form.findField('DrugRMZ_RegNum').setValue(data.DrugRMZ_RegNum);
								base_form.findField('DrugRMZ_EAN13Code').setValue(data.DrugRMZ_EAN13Code);
                                base_form.findField('DrugRMZ_Firm').setValue(data.DrugRMZ_Firm);
                                base_form.findField('DrugRMZ_Country').setValue(data.DrugRMZ_Country);
                                base_form.findField('DrugRMZ_FirmPack').setValue(data.DrugRMZ_FirmPack);
								base_form.findField('DrugRMZ_Name').setValue(data.DrugRMZ_Name);
								base_form.findField('DrugRMZ_Form').setValue(data.DrugRMZ_Form);
								base_form.findField('DrugRMZ_Dose').setValue(data.DrugRMZ_Dose);
								base_form.findField('DrugRMZ_Pack').setValue(data.DrugRMZ_Pack);
								base_form.findField('DrugRMZ_PackSize').setValue(data.DrugRMZ_PackSize);
								getWnd(searchWindow).hide();
							}
						});
					},
					onTrigger2Click: function() {
						var base_form = win.formPanel.getForm();
						base_form.findField('DrugRMZ_id').setValue(null);
						base_form.findField('DrugRPN_id').setValue(null);
						base_form.findField('DrugRMZ_RegNum').setValue(null);
						base_form.findField('DrugRMZ_EAN13Code').setValue(null);
                        base_form.findField('DrugRMZ_Firm').setValue(null);
                        base_form.findField('DrugRMZ_Country').setValue(null);
                        base_form.findField('DrugRMZ_FirmPack').setValue(null);
						base_form.findField('DrugRMZ_Name').setValue(null);
						base_form.findField('DrugRMZ_Form').setValue(null);
						base_form.findField('DrugRMZ_Dose').setValue(null);
						base_form.findField('DrugRMZ_Pack').setValue(null);
						base_form.findField('DrugRMZ_PackSize').setValue(null);
					}
				}), {
					name: 'DrugRMZ_id',
					xtype: 'hidden'
				}, {
					name: 'DrugRMZ_oldid',
					xtype: 'hidden'
				}, {
					width: 500,
					fieldLabel: langs('№ РУ'),
					name: 'DrugRMZ_RegNum',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Код EAN'),
					name: 'DrugRMZ_EAN13Code',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Производитель'),
					name: 'DrugRMZ_Firm',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Страна пр.'),
					name: 'DrugRMZ_Country',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Упаковщик'),
					name: 'DrugRMZ_FirmPack',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Торг. наименование'),
					name: 'DrugRMZ_Name',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Форма выпуска'),
					name: 'DrugRMZ_Form',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Дозировка'),
					name: 'DrugRMZ_Dose',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Упаковка'),
					name: 'DrugRMZ_Pack',
					disabled: true,
					xtype: 'textfield'
				}, {
					width: 500,
					fieldLabel: langs('Кол-во лек. форм в упаковке'),
					name: 'DrugRMZ_PackSize',
					disabled: true,
					xtype: 'textfield'
				}]
			}, {
				xtype: 'fieldset',
				style: 'padding-top: 3px; padding-bottom: 3px;',
				autoHeight: true,
				title: langs('Федеральный портал ВЗН'),
				labelWidth: 180,
				items: [{
				    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            fieldLabel: langs('МНН'),
                            name: 'DrugMnnVZN_Code',
                            xtype: 'textfield',
                            width: 500, //197
                            disabled: true
                        }, {
                            fieldLabel: langs('Торг. наим.'),
                            name: 'TradeNamesVZN_Code',
                            xtype: 'textfield',
                            width: 500,
                            disabled: true
                        }, {
                            fieldLabel: langs('Лек. форма'),
                            name: 'DrugFormVZN_Code',
                            xtype: 'textfield',
                            width: 500,
                            disabled: true
                        }]
                    }, {
                        layout: 'form',
                        labelWidth: 100,
                        style: 'margin-left: 15px;',
                        items: [
                            win.nsi_dd_combo, // Дозировка
                            win.nsi_dkd_combo // Кол-во доз в уп.
                        ]
                    }]
                }, {
                    fieldLabel: 'DrugVZN_fid',
                    name: 'DrugVZN_fid', // МНН
                    xtype: 'hidden'
                }, {
                    fieldLabel: 'DrugFormVZN_id',
                    name: 'DrugFormVZN_id', // Лек.форма
                    xtype: 'hidden'
                }, {
                    fieldLabel: 'DrugRelease_id',
                    name: 'DrugRelease_id', // Торг. наим.
                    xtype: 'hidden'
                }, {
                    style: "padding-bottom: 4px",
                    xtype: 'button',
                    text: langs('Сохранить'),
                    iconCls: 'save16',
                    minWidth: 100,
                    handler: function() {
                        win.doSaveDrugVznData();
                    }
                }]
			}]
		});

		this.DrugNormativeListGrid = new sw.Promed.ViewFrame( {
			autoLoadData: false,
			dataUrl: '/?c=DrugNormativeList&m=loadListByRlsDrug',
			object: 'DrugNormativeList',
			region: 'center',
			title: langs('Список нормативных перечней'),
			height: 460,
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			stringfields: [
				{ name: 'DrugNormativeList_id', type: 'int', header: 'ID', key: true },
				{ name: 'DrugNormativeList_Name', type: 'string', header: langs('Наименование перечня'), id: 'autoexpand' },
				{ name: 'MorbusType_Name', type: 'string', header: langs('Тип'), width: 420 },
				{ name: 'DrugNormativeList_begDate', type: 'date', header: langs('Дата начала'), width: 160 }
			]
		});

        this.GoodsPackCountListGrid = new sw.Promed.ViewFrame( {
            autoLoadData: false,
            dataUrl: '/?c=DrugNomen&m=loadGoodsPackCountListGrid',
            object: 'GoodsPackCountList',
            title: 'Единицы учета товара',
            height: 155,
            width: 605,
            frame: true,
            style: 'margin-bottom: 6px;',
            editformclassname: 'swGoodsUnitCountEditWindow',
            actions: [
                {name:'action_add', handler: function() { win.GoodsPackCountListGrid.openRecord('add'); }},
                {name:'action_edit', handler: function() { win.GoodsPackCountListGrid.openRecord('edit'); }},
                {name:'action_view', handler: function() { win.GoodsPackCountListGrid.openRecord('view'); }},
                {name:'action_delete', handler: this.deleteGoodsPackCount.createDelegate(this), disabled: !(haveArmType('minzdravdlo') || isSuperAdmin()) },
                {name:'action_refresh', disabled: true, hidden: true},
                {name:'action_print', disabled: true, hidden: true}
            ],
            stringfields: [
                { name: 'GoodsPackCount_id', type: 'int', header: 'ID', key: true, hidden: true },
                { name: 'GoodsUnit_id', type: 'int', hidden: true },
                { name: 'DrugComplexMnn_id', type: 'int', hidden: true },
                { name: 'GoodsUnit_Name', type: 'string', header: 'Единицы измерения', width: 160 },
                { name: 'GoodsPackCount_Count', type: 'string', header: 'Количество в упаковке', width: 160 },
                { name: 'Org_id', hidden: true },
                { name: 'Org_Name', type: 'string', header: 'Организация', id: 'autoexpand' }
            ],
            onRowSelect: function(sm, rowIdx, record) {
                if (record.get('GoodsPackCount_id') > 0 && !this.readOnly) {
                    var my_org = ((record.get('Org_id') > 0 && record.get('Org_id') == getGlobalOptions().org_id) || isSuperAdmin());
                    this.getAction('action_edit').setDisabled(false);
                    this.getAction('action_view').setDisabled(false);
                    this.getAction('action_delete').setDisabled(!my_org);
                } else {
                    this.getAction('action_edit').setDisabled(true);
                    this.getAction('action_view').setDisabled(true);
                    this.getAction('action_delete').setDisabled(true);
                }
            },
            openRecord: function(action) {
                var view_frame = this;
                var drug_combo = win.formPanel.getForm().findField('Drug_id');

                if (drug_combo.getValue() > 0) {
                    drug_combo.getStore().findBy(function(record) {
                        if (record.get('Drug_id') == drug_combo.getValue()) {
                            view_frame.setParam('DrugComplexMnn_id', record.get('DrugComplexMnn_id'), false);
                            view_frame.setParam('Org_id', win.getDrugNomenOrgLinkOrgId(), false);
                            view_frame.editRecord(action);
                            return;
                        }
                    });
                } else {
                    sw.swMsg.alert(langs('Ошибка'), 'Необходимо указать наименование медикамента', function(){ drug_combo.focus(); } );
                }
            }
        });


        /*this.DrugPrepEdUcCountListGrid = new sw.Promed.ViewFrame( {
            autoLoadData: false,
            dataUrl: '/?c=DrugNomen&m=loadDrugPrepEdUcCountListGrid',
            object: 'DrugPrepEdUcCountList',
            title: 'Единицы учета ЛС',
            height: 155,
            width: 605,
            frame: true,
            style: 'margin-bottom: 6px;',
            editformclassname: 'swDrugPrepEdUcCountEditWindow',
            actions: [
                {name:'action_add', handler: function() { win.DrugPrepEdUcCountListGrid.openRecord('add'); }},
                {name:'action_edit', handler: function() { win.DrugPrepEdUcCountListGrid.openRecord('edit'); }},
                {name:'action_view', handler: function() { win.DrugPrepEdUcCountListGrid.openRecord('view'); }},
                {name:'action_delete', handler: this.deleteDrugPrepEdUcCount.createDelegate(this) },
                {name:'action_refresh', disabled: true, hidden: true},
                {name:'action_print', disabled: true, hidden: true}
            ],
            stringfields: [
                { name: 'DrugPrepEdUcCount_id', type: 'int', header: 'ID', key: true, hidden: true },
                { name: 'GoodsUnit_id', type: 'int', hidden: true },
                { name: 'DrugPrepFas_id', type: 'int', hidden: true },
                { name: 'Drug_id', type: 'int', hidden: true },
                { name: 'GoodsUnit_Name', type: 'string', header: 'Единицы измерения', width: 160 },
                { name: 'DrugPrepEdUcCount_Count', type: 'string', header: 'Количество в упаковке', width: 160 },
                { name: 'Org_id', hidden: true },
                { name: 'Org_Name', type: 'string', header: 'Организация', id: 'autoexpand' }
            ],
            onRowSelect: function(sm, rowIdx, record) {
                if (record.get('DrugPrepEdUcCount_id') > 0 && !this.readOnly) {
                    var my_org = ((record.get('Org_id') > 0 && record.get('Org_id') == getGlobalOptions().org_id) || isSuperAdmin());
                    this.getAction('action_edit').setDisabled(false);
                    this.getAction('action_view').setDisabled(false);
                    this.getAction('action_delete').setDisabled(!my_org);
                } else {
                    this.getAction('action_edit').setDisabled(true);
                    this.getAction('action_view').setDisabled(true);
                    this.getAction('action_delete').setDisabled(true);
                }
            },
            openRecord: function(action) {
                var view_frame = this;
                var drug_combo = win.formPanel.getForm().findField('Drug_id');

                if (drug_combo.getValue() > 0) {
                    view_frame.setParam('Drug_id', drug_combo.getValue(), false);
                    view_frame.setParam('Org_id', win.getDrugNomenOrgLinkOrgId(), false);
                    this.editRecord(action);
                } else {
                    sw.swMsg.alert(langs('Ошибка'), 'Необходимо указать наименование медикамента', function(){ drug_combo.focus(); } );
                }
            }
        });*/

		this.PurchasePropertiesPanel = new sw.Promed.Panel({
			layout: 'form',
			id: 'dnew_PurchasePropertiesPanel',
			border: true,
			frame: true,
			autoHeight: true,
			items: [{
                layout: 'form',
                labelWidth: 111,
                items: [{
                    xtype: 'swcommonsprcombo',
                    comboSubject: 'PrepClass',
                    fieldLabel: 'Класс учета',
                    id: 'dnew_PrepClass_id',
                    allowBlank: false,
                    width: 300
                }]
			}, {
				xtype: 'fieldset',
				style: 'padding-top: 3px; padding-bottom: 3px;',
				autoHeight: true,
				title: 'Свойства закупа',
				items: [
					{
						name: 'DrugComplexMnnCode_id',
						xtype: 'hidden'
					},
					{
						name: 'DrugComplexMnn_id',
						xtype: 'hidden'
					}, {
						fieldLabel: langs('Компл. МНН'),
						disabled: true,
						width: 500,
						name: 'DrugComplexMnn_RusName',
						xtype: 'textfield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: langs('Код'),
						width: 200,
						name: 'DrugComplexMnnCode_Code',
						onTriggerClick: function() {
                            if (!this.disabled) {
                                win.generateCode('DrugComplexMnnCode');
                            }
						},
						triggerClass: 'x-form-plus-trigger',
						validateOnBlur: false,
						xtype: 'trigger'
					}, {
						fieldLabel: langs('Лат. наим.'),
						disabled: true,
						width: 500,
						name: 'DrugComplexMnn_LatName',
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Кол-во уп. в месяц по рецепту'),
						disabled: false,
						width: 500,
						listeners:  {
							'focus': function(me){
								me.qtip = new Ext.QuickTip({
									target: me.getEl(),
									text: 'Укажите, какое максимальное количество упаковок ЛП рекомендуется выписывать по рецепту на один месяц приема',
									enabled: true,
									showDelay: 60,
									trackMouse: true,
									autoShow: true
								});
								Ext.QuickTips.register(me.qtip);
							}
						},
						name: 'DrugComplexMnnCode_DosKurs',
						xtype: 'textfield'
					}
				]
			},
            this.GoodsPackCountListGrid, 
            {
                xtype: 'fieldset',
                autoHeight: true,
                style: 'padding-top: 3px; padding-bottom: 3px;',
                title: 'Свойства учета',
                items: [
                    {
                        name: 'DrugPrepFas_id',
                        xtype: 'hidden'
                    }, {
                        fieldLabel: 'Компл. торг.',
                        disabled: true,
                        width: 500,
                        name: 'DrugPrep_Name',
                        xtype: 'textfield'
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                allowBlank: false,
                                enableKeyEvents: true,
                                fieldLabel: 'Код',
                                width: 200,
                                name: 'DrugPrepFasCode_Code',
                                onTriggerClick: function() {
                                    if (!this.disabled) {
                                        win.generateCode('DrugPrepFasCode');
                                    }
                                },
                                triggerClass: 'x-form-plus-trigger',
                                validateOnBlur: false,
                                xtype: 'trigger'
                            }]
                        }]
                    }, {
                        fieldLabel: langs('Лат. наим.'),
                        disabled: false,
                        width: 500,
                        name: 'DrugTorg_NameLatin',
                        xtype: 'textfield'
                    }/*,
                    this.DrugPrepEdUcCountListGrid*/
                ]
            }]
		});

		this.TabsPanel = new Ext.TabPanel({
			id: 'dnew_DrugNomenTabsPanel',
			autoScroll: false,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'center',
			enableTabScroll: true,
			//autoHeight: true,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[
				{
					title: 'Свойства учета',
					layout: 'fit',
					bodyStyle: {
						height: '100%',
						minHeight: '450px'
					},
					border: false,
					items: [win.PurchasePropertiesPanel]
				},
				{
					title: langs('Свойства препарата'),
					layout: 'fit',
					bodyStyle: {
						height: '100%',
						minHeight: '415px'
					},
					border: false,
					items: [win.PropertiesPanel]
				},
				{
					title: langs('Информация об использовании'),
					layout: 'fit',
					bodyStyle: {
						height: '100%',
						minHeight: '460px'
					},
					border: false,
					items: [win.DrugNormativeListGrid]
				},
				{
					title: langs('Коды для отчетности'),
					layout: 'fit',
					bodyStyle: {
						height: '100%',
						minHeight: '450px'
					},
					border: false,
					items: [win.StaticCodesPanel]
				}
			]
		});

		this.MainPanel = new sw.Promed.Panel({
			layout: 'form',
			id: 'dnew_MainPanel',
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			region: 'north',
            labelWidth: 112,
			items: [{
				xtype: 'swdrugsimplecombo',
				width: 1000,
				listWidth: 800,
				fieldLabel: langs('Наименование'),
				labelStyle: 'font-weight: bold;',
				hiddenName: 'Drug_id',
				value: '',
				allowBlank: false,
				listeners: {
					'beforeselect': function(combo, object) {
						var base_form = win.formPanel.getForm();
						//Сбрасывает значение полей на форме, за исключением указанных полей
						var fields = ['DrugNomen_Code','PrepClass_id','SprType_Code'];
						if (win.action != 'add') {
							fields.push('DrugMnnCode_id','DrugTorgCode_id','DrugComplexMnnCode_id');
							fields.push('DrugMnnCode_Code','DrugTorgCode_Code','DrugComplexMnnCode_Code');
						}
						var values = new Object();

						for(var i=0;i<fields.length;i++) {
							values[fields[i]] = base_form.findField(fields[i]).getValue();
						}

						base_form.reset();

						base_form.setValues(values);
					},
					'select': function(combo, record) {
						var drug_id = record.get('Drug_id');
						var params = {Drug_id: drug_id};

						win.getDrugNomenData(drug_id);
						win.DrugNormativeListGrid.loadData({params: params, globalFilters: params});

                        win.doLoadDrugVznData(drug_id);

						var nick_field = win.formPanel.getForm().findField('DrugNomen_Nick');
						if (nick_field.getValue() == '') {
							nick_field.setValue(record.get('Drug_Name'));
						}

                        win.GoodsPackCountListGrid.setParam('DrugComplexMnn_id', record.get('DrugComplexMnn_id'), true);
                        win.GoodsPackCountListGrid.loadData();
                        //win.DrugPrepEdUcCountListGrid.setParam('Drug_id', record.get('Drug_id'), true);
                        //win.DrugPrepEdUcCountListGrid.loadData();
					},
					'render': function() {
						this.getStore().proxy.conn.url = "/?c=RlsDrug&m=loadDrugList";
					}
				},
				loadingText: langs('Идет поиск...'),
				onTrigger2Click: function() {
					if (this.disabled)
						return false;

					var combo = this;

					getWnd('swRlsDrugTorgSearchWindow').show({
						searchFull: true,
                        EanFilterEnabled: true,
                        FormValues: {
                            Drug_Ean: win.formPanel.getForm().findField('Drug_Ean').getValue()
                        },
						onHide: function() {
							combo.focus(false);
						},
						onSelect: function(drugData) {
							combo.fireEvent('beforeselect', combo);

							combo.getStore().removeAll();
							combo.getStore().loadData([{
								Drug_id: drugData.Drug_id,
								Drug_Dose: drugData.Drug_Dose,
								DrugForm_Name: drugData.DrugForm_Name,
								Drug_Name: drugData.Drug_Name,
								Drug_Code: drugData.Drug_Code,
                                DrugComplexMnn_id: drugData.DrugComplexMnn_id
							}], true);

							combo.setValue(drugData.Drug_id);
							var index = combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == drugData.Drug_id; });

							if (index == -1)
							{
								return false;
							}

							var record = combo.getStore().getAt(index);

							if ( typeof record == 'object' ) {
								combo.fireEvent('select', combo, record, 0);
								combo.fireEvent('change', combo, record.get('Drug_id'));
							}

							getWnd('swRlsDrugTorgSearchWindow').hide();
						}
					});
				}
			},
            win.sprtype_combo,
            {
                layout: 'column',
                items: [{
                    layout: 'form',
                    items: [{
                        allowBlank: false,
                        enableKeyEvents: true,
                        fieldLabel: langs('Код'),
                        width: 200,
                        name: 'DrugNomen_Code',
                        onTriggerClick: function() {
                            if (!this.disabled) {
                                win.generateCode('DrugNomen');
                            }
                        },
                        triggerClass: 'x-form-plus-trigger',
                        validateOnBlur: false,
                        xtype: 'trigger'
                    }]
                }, {
                    layout: 'form',
                    labelWidth: 62,
                    style: 'margin-left: 33px;',
                    items: [
                        new Ext.form.TwinTriggerField ({
                            allowBlank: true,
                            enableKeyEvents: true,
                            fieldLabel: 'Код орг.',
                            width: 200,
                            name: 'DrugNomenOrgLink_Code',
                            onTrigger1Click: function() {
                                if (!this.disabled) {
                                    win.generateCode('DrugNomenOrgLink');
                                }
                            },
                            onTrigger2Click: function() {
                                if (!this.disabled) {
                                    this.setValue(win.formPanel.getForm().findField('DrugNomen_Code').getValue());
                                }
                            },
                            trigger1Class: 'x-form-plus-trigger',
                            trigger2Class: 'x-form-equil-trigger',
                            validateOnBlur: false
                        })
                    ]
                }]
            }, {
				disabled: true,
				fieldLabel : langs('№ РУ'),
				width: 500,
				name: 'Reg_Num',
				xtype: 'textfield'
			}, {
				disabled: true,
				fieldLabel : langs('Код EAN'),
				width: 500,
				name: 'Drug_Ean',
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel : langs('Краткое наименование'),
				width: 500,
				name: 'DrugNomen_Nick',
				xtype: 'textfield'
			}, {
				name: 'PrepClass_id',
				xtype: 'hidden'
			}, {
				name: 'DrugNomen_id',
				xtype: 'hidden'
			}]
		});

		this.formPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: true,
			border: true,
			frame: false,
			id: 'dnew_FormPanel',
			bodyStyle: 'background: #DFE8F6',
			items: [
				win.MainPanel,
				win.TabsPanel
			],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())  {
						case Ext.EventObject.C:
							if (this.action != 'view') {
								this.doSave();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
					success: function() {
						//
					}
				},
				[
					{ name: 'DrugNomen_id' },
					{ name: 'Drug_id' },
					{ name: 'DrugNomen_Code' },
					{ name: 'DrugNomen_Nick' },
					{ name: 'DrugMnnCode_id' },
					{ name: 'DrugTorgCode_id' },
					{ name: 'DrugComplexMnnCode_id' },
					{ name: 'DrugComplexMnn_id' },
					{ name: 'DrugMnnCode_Code' },
					{ name: 'DrugTorgCode_Code' },
					{ name: 'DrugComplexMnnCode_Code' },
					{ name: 'Okpd_id' }
				]),
			timeout: 600,
			region: 'center',
			url: '/?c=DrugNomen&m=saveDrugNomen'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_GL + 29,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onTabElement: 'DNEW_Marker_Word',
				tabIndex: TABINDEX_GL + 31,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.formPanel
			]
		});

		sw.Promed.swDrugNomenEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swDrugNomenEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		this.SprType_Code = arguments[0].SprType_Code || null;
        this.edit_disabled = false;

        var win = this,
			base_form = this.formPanel.getForm();

		this.TabsPanel.setActiveTab(3);
		this.TabsPanel.setActiveTab(2);
		this.TabsPanel.setActiveTab(1);
		this.TabsPanel.setActiveTab(0);

        if (getRegionNick() == 'kz') { //для Казахстана скрывается вкладка "Коды для отчетности"
            this.TabsPanel.hideTabStripItem(3);
        } else {
            this.TabsPanel.unhideTabStripItem(3);
        }

		base_form.reset();
		base_form.setValues(arguments[0]);
		if(arguments[0].PrepClass_id){
			this.PurchasePropertiesPanel.findById('dnew_PrepClass_id').setValue(arguments[0].PrepClass_id);
		}
		this.DrugNormativeListGrid.removeAll();
		this.GoodsPackCountListGrid.removeAll();
		//this.DrugPrepEdUcCountListGrid.removeAll();

        this.nsi_dd_combo.fullReset();
        this.nsi_dkd_combo.fullReset();

        //блокировки полей в зависимости от прав пользователя
        base_form.findField('DrugNomen_Code').enable_blocked = this.action != 'add';
        if (isSuperAdmin() || getGlobalOptions().isMinZdrav) {
            base_form.findField('DrugNomen_Code').enable_blocked = false;
        }
        base_form.findField('DrugComplexMnn_LatName').enable_blocked = !(isSuperAdmin() || haveArmType('minzdravdlo') || haveArmType('adminllo') || haveArmType('superadmin'));

		switch (this.action) {
			case 'view':
				this.setTitle(langs('Номенклатурная карточка: Просмотр'));
				break;
			case 'edit':
				this.setTitle(langs('Номенклатурная карточка: Редактирование'));
				break;
			case 'add':
				this.setTitle(langs('Номенклатурная карточка: Добавление'));
				break;
			break;
		}

		if (this.action == 'add') {
			this.setSprType();
			this.syncSize();
			this.doLayout();
            this.setDisabled(false);
		} else {
            win.setDisabled(true);
			win.getLoadMask(langs('Пожалуйста, подождите, идет загрузка данных формы...')).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось загрузить данные с сервера'), function() { win.hide(); } );
				},
				params: {
					DrugNomen_id: base_form.findField('DrugNomen_id').getValue()
				},
				success: function(form, action) {
					win.getLoadMask().hide();
					if(win.action == 'edit') {
                        win.setDisabled(false);
						base_form.findField('Drug_id').setDisabled(!(getGlobalOptions().IsSMPServer && win.sprtype_combo.getValue() ==='org_nom'));
					}
					if (!Ext.isEmpty(base_form.findField('Drug_id').getValue())) {
						base_form.findField('Drug_id').getStore().load({
							params: {
								'Drug_id': base_form.findField('Drug_id').getValue()
							},
							callback: function() {
								base_form.findField('Drug_id').setValue(base_form.findField('Drug_id').getValue());
								base_form.findField('Drug_id').fireEvent('select',base_form.findField('Drug_id'),base_form.findField('Drug_id').getStore().getAt(0),0);
							}
						});
					}
					if (!Ext.isEmpty(base_form.findField('Okpd_id').getValue())) {
						base_form.findField('Okpd_id').getStore().load({
							params: {
								Okpd_id: base_form.findField('Okpd_id').getValue()
							},
							callback: function() {
								base_form.findField('Okpd_id').setValue(base_form.findField('Okpd_id').getValue());
							}
						});
					}

                    win.setSprType();
                    win.GoodsPackCountListGrid.setParam('DrugComplexMnn_id', action.result.data.DrugComplexMnn_id, true);
                    win.GoodsPackCountListGrid.loadData();
                    //win.DrugPrepEdUcCountListGrid.setParam('Drug_id', action.result.data.Drug_id, true);
                    //win.DrugPrepEdUcCountListGrid.loadData();
				},
				url: '/?c=DrugNomen&m=loadDrugNomenEditForm'
			});
		}
	}
});

