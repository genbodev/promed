/**
* swWhsDocumentOrderReserveEditWindow - окно редактирования "Распоряжение на включение в резерв"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Alexandr Chebukin
* @version      23.12.2012
* @comment      
*/
sw.Promed.swWhsDocumentOrderReserveEditWindow = Ext.extend(sw.Promed.BaseForm, { //getWnd('swWhsDocumentOrderReserveEditWindow').show();
	autoHeight: false,
	title: lang['rasporyajenie_na_vklyuchenie_v_rezerv'],
	layout: 'border',
	id: 'WhsDocumentOrderReserveEditWindow',
	modal: true,
	//shim: false,
	width: 950,
	height: 465,
	resizable: false,
	maximizable: true,
	maximized: true,
	firstTabIndex: 10000,
	plain: true,
	debug_mode: false, //только для разработки
	demand: null,
	delivery_graph: null,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
    setFinanceDefaultValues: function() {
        var form = this.findById('WhsDocumentOrderReservesEditForm').getForm();
        var fin_combo = form.findField('DrugFinance_id');
        var cost_combo = form.findField('WhsDocumentCostItemType_id');
        var request_combo = form.findField('DrugRequest_id');
        var supply_combo = form.findField('WhsDocumentSupply_id');
        var request_id = request_combo.getValue();
        var supply_id = supply_combo.getValue();

        fin_combo.enable_blocked = false;
        cost_combo.enable_blocked = false;

        if (supply_id > 0) {
            supply_combo.getStore().each(function(record) {
                if (record.get('WhsDocumentSupply_id') == supply_id) {
                    if (record.get('DrugFinance_id') > 0) {
                        fin_combo.setValue(record.get('DrugFinance_id'));
                        fin_combo.enable_blocked = true;
                    }
                    if (record.get('WhsDocumentCostItemType_id') > 0) {
                        cost_combo.setValue(record.get('WhsDocumentCostItemType_id'));
                        cost_combo.enable_blocked = true;
                    }
                }
            });
        } else if (request_id > 0) {
            request_combo.getStore().each(function(record) {
                if (record.get('DrugRequest_id') == request_id) {
                    if (record.get('DrugFinance_id') > 0) {
                        fin_combo.setValue(record.get('DrugFinance_id'));
                        fin_combo.enable_blocked = true;
                    }
                    if (record.get('WhsDocumentCostItemType_id') > 0) {
                        cost_combo.setValue(record.get('WhsDocumentCostItemType_id'));
                        cost_combo.enable_blocked = true;
                    }
                }
            });
        }

        this.setDisabled();
    },
	findDrug: function(params) { //функция для поиска конкретного медикамента в списке заказанных
		var wnd = this;		
		var viewframe =  wnd.findById('WhsDocumentOrderReserveDrugGrid');
		if (params.DrugComplexMnn_id > 0 || params.Drug_id) {
			var item_index = viewframe.getGrid().getStore().findBy(function(r,id) {
				if(r.get('DrugComplexMnn_id') == params.DrugComplexMnn_id)
					return true;
			});
			viewframe.getGrid().getSelectionModel().selectRows([item_index]);
		}
	},
	doSign: function() {
		var wnd = this;
		var form = wnd.findById('WhsDocumentOrderReservesEditForm').getForm();
		
		Ext.Ajax.request({
			url: '?c=WhsDocumentOrderReserve&m=sign',
			params: {
				WhsDocumentOrderReserve_id: form.findField('WhsDocumentOrderReserve_id').getValue()
			},
			success: function(response, action) {
				if (response && response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success) {
						Ext.Msg.alert(lang['soobschenie'], lang['dokument_uspeshno_podpisan']);
						form.findField('WhsDocumentStatusType_id').setValue(2);
						wnd.setDisabled(true);
						wnd.buttons[2].hide();
                        wnd.callback();
					}
				} else {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_podpisanii_otsutstvuet_otvet_servera']);
				}
			}
		});
	},
	doSave:  function(silent) {
		var wnd = this;
		var form = wnd.findById('WhsDocumentOrderReservesEditForm').getForm();
		var viewframe =  wnd.findById('WhsDocumentOrderReserveDrugGrid');
		
		form.findField('ReserveDrugJSON').setValue(viewframe.getJSONChangedData());
		
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentOrderReservesEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}		
		viewframe.updateSumm();		
		
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		var params = new Object();
		params.action = current_window.action;
		params.WhsDocumentUc_Sum = current_window.form.findField('WhsDocumentUc_Sum').getValue();
		params.DrugFinance_id = current_window.form.findField('DrugFinance_id').getValue();
		params.WhsDocumentCostItemType_id = current_window.form.findField('WhsDocumentCostItemType_id').getValue();
		params.Org_id = current_window.form.findField('Org_id').getValue();

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				current_window.callback();
				if ( silent == false ) {
					current_window.hide();
				} else {
					wnd.WhsDocumentUc_id = action.result.WhsDocumentUc_id;
					wnd.action = 'edit';
					form.findField('WhsDocumentUc_id').setValue(wnd.WhsDocumentUc_id);
					form.findField('WhsDocumentOrderReserve_id').setValue(wnd.WhsDocumentUc_id);	
					if ( typeof silent == 'function' ) {
						silent();
					}
				}
			}
		});
		
		return true;		
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.findById('WhsDocumentOrderReservesEditForm').getForm();
		var field_arr = [
			'WhsDocumentUc_Num',
			'WhsDocumentUc_Date',
			'WhsDocumentUc_Name',
			'DrugRequest_id',
			'WhsDocumentSupply_id',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id',
			'WhsDocumentOrderReserve_Percent'
		];
		
		for (var i in field_arr) if (form.findField(field_arr[i])) {
			if (disable || form.findField(field_arr[i]).enable_blocked) {
                form.findField(field_arr[i]).disable();
            } else {
                form.findField(field_arr[i]).enable();
            }
		}
		
		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
			wnd.buttons[2].disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
			wnd.buttons[2].enable();
		}
		
		wnd.findById('WhsDocumentOrderReserveDrugGrid').setReadOnly(disable);
	},
	show: function() {
        var that = this;		
		
		sw.Promed.swWhsDocumentOrderReserveEditWindow.superclass.show.apply(this, arguments);
		
		var viewframe = that.findById('WhsDocumentOrderReserveDrugGrid');

		if (getGlobalOptions().org_id > 0) { //по умолчанию организация соответствует организации пользователя
			this.form.findField('Org_id').setValueById(getGlobalOptions().org_id);
		}
		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentUc_id = null;
		this.WhsDocumentType_id = null;
		this.WhsDocumentType_Name = lang['dokument'];

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].WhsDocumentUc_id ) {
			this.WhsDocumentUc_id = arguments[0].WhsDocumentUc_id;
		}
		if ( arguments[0].WhsDocumentType_id ) {
			this.WhsDocumentType_id = arguments[0].WhsDocumentType_id;
		}
		
		this.form.reset();
        that.form.findField('DrugRequest_id').getStore().baseParams.DrugRequest_id = null;
        that.form.findField('WhsDocumentSupply_id').getStore().baseParams.DrugRequest_id = null;
        that.form.findField('WhsDocumentSupply_id').getStore().baseParams.WhsDocumentSupply_id = null;
		viewframe.removeAll({clearAll: true});
		this.buttons[2].hide();

        if (this.WhsDocumentType_id == 12) {
			this.WhsDocumentType_Name = lang['rasporyajenie_na_vklyuchenie_v_rezerv'];
		} else if (this.WhsDocumentType_id == 13) {
            this.WhsDocumentType_Name = lang['rasporyajenie_na_isklyuchenie_iz_rezerva'];
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		switch (this.action) {
			case 'add':
                this.setTitle(this.WhsDocumentType_Name + lang['_dobavlenie']);
				Ext.Ajax.request({
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							this.form.findField('WhsDocumentUc_Num').setValue(response_obj.WhsDocumentUc_Num);
							this.form.findField('WhsDocumentUc_Date').setValue(getGlobalOptions().date);
							this.form.findField('WhsDocumentUc_Name').setValue(response_obj.WhsDocumentUc_Num + '. ' + getGlobalOptions().date + ' - ' + that.WhsDocumentType_Name);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_dokumenta'], function() { 
								this.form.findField('WhsDocumentUc_Num').focus(true); 
							});
						}
					}.createDelegate(this),
					url: '/?c=WhsDocumentOrderReserve&m=getWhsDocumentOrderReserveNumber'
				});
				that.form.findField('WhsDocumentOrderReserve_Percent').fireEvent('change', that.form.findField('WhsDocumentOrderReserve_Percent'), that.form.findField('WhsDocumentOrderReserve_Percent').getValue());
				that.form.findField('WhsDocumentType_id').setValue(this.WhsDocumentType_id);
				that.findById('BtnGen').show();
                that.doLayout();
				this.setDisabled(false);
			break;
			case 'edit':
            case 'view':
                this.setTitle(this.WhsDocumentType_Name + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				that.findById('BtnGen').hide();
                that.doLayout();
				Ext.Ajax.request({
					params:{
						WhsDocumentUc_id: that.WhsDocumentUc_id
					},
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (!result[0]) { return false}
							that.form.setValues(result[0]);
							viewframe.loadData({
								url: '/?c=WhsDocumentOrderReserveDrug&m=loadList',
								params:{WhsDocumentOrderReserve_id: that.form.findField('WhsDocumentOrderReserve_id').getValue()}, 
								globalFilters:{WhsDocumentOrderReserve_id: that.form.findField('WhsDocumentOrderReserve_id').getValue()},
								callback: function() {
									viewframe.setRowNumbering();
								}
							});

                            if (!Ext.isEmpty(result[0].Org_id)) {
                                this.form.findField('Org_id').setValueById(result[0].Org_id);
                            } else {
                                this.form.findField('Org_id').setValueById(getGlobalOptions().org_id);
                            }

                            if (!Ext.isEmpty(result[0].DrugRequest_id)) {
                                that.form.findField('DrugRequest_id').setValueById(result[0].DrugRequest_id);
                                that.form.findField('WhsDocumentSupply_id').getStore().baseParams.DrugRequest_id = result[0].DrugRequest_id;
                            }

                            if (!Ext.isEmpty(result[0].WhsDocumentSupply_id)) {
                                that.form.findField('WhsDocumentSupply_id').setValueById(result[0].WhsDocumentSupply_id);
                            }

							if (that.form.findField('WhsDocumentStatusType_id').getValue() == 1) {
								that.buttons[2].show();
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih'], function() { 
								this.form.findField('WhsDocumentUc_Num').focus(true); 
							});
						}
                        that.setDisabled(this.action=='view');
					}.createDelegate(this),
					url: '/?c=WhsDocumentOrderReserve&m=load'
				});
			break;	
		}
		
		
	},
	initComponent: function() {
		var current_window = this;

        this.request_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['svodnaya_zayavka'],
            hiddenName: 'DrugRequest_id',
            displayField: 'DrugRequest_Name',
            valueField: 'DrugRequest_id',
            allowBlank: true,
            editable: true,
            width: 650,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 25%;">'+lang['period']+'</td>',
                '<td style="padding: 2px; width: 25%;">'+lang['istochnik_finansirovaniya']+'</td>',
                '<td style="padding: 2px; width: 25%;">'+lang['statya_rashoda']+'</td>',
                '<td style="padding: 2px; width: 25%;">'+lang['naimenovanie']+'</td>',
                '<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt;">',
                '<td style="padding: 2px;">{DrugRequestPeriod_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{DrugRequest_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'DrugRequest_id', mapping: 'DrugRequest_id' },
                    { name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id' },
                    { name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
                    { name: 'DrugRequest_Name', mapping: 'DrugRequest_Name' },
                    { name: 'DrugRequestPeriod_Name', mapping: 'DrugRequestPeriod_Name' },
                    { name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name' },
                    { name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' }
                ],
                key: 'DrugRequest_id',
                sortInfo: { field: 'DrugRequest_Name' },
                url:'/?c=WhsDocumentOrderReserve&m=loadConsolidatedDrugRequestCombo'
            }),
            childrenList: ['WhsDocumentSupply_id'],
            listeners: {
                'change': function(combo, newValue) {
                    combo.childrenList.forEach(function(field_name){
                        var f_combo = current_window.form.findField(field_name);
                        if (!f_combo.disabled) {
                            f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
                            f_combo.loadData();
                        }
                    });
                }
            },
            onTrigger2Click: function() {
                var combo = this;

                if (combo.disabled) {
                    return false;
                }

                combo.clearValue();
                delete combo.lastQuery;
                combo.getStore().removeAll();
                combo.getStore().baseParams.query = '';
                combo.fireEvent('change', combo, null);
                current_window.setFinanceDefaultValues();
            },
            setValueById: function(id) {
                var combo = this;
                combo.store.baseParams.DrugRequest_id = id;
                combo.store.load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams.DrugRequest_id = null;
                    }
                });
                current_window.setFinanceDefaultValues();
            }
        });

        this.supply_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['kontrakt'],
            hiddenName: 'WhsDocumentSupply_id',
            displayField: 'WhsDocumentSupply_Name',
            valueField: 'WhsDocumentSupply_id',
            allowBlank: true,
            editable: true,
            width: 650,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 20%;">'+lang['№']+'</td>',
                '<td style="padding: 2px; width: 40%;">'+lang['naimenovanie']+'</td>',
                '<td style="padding: 2px; width: 40%;">'+lang['postavschik']+'</td>',
                '<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt;">',
                '<td style="padding: 2px;">{WhsDocumentUc_Num}&nbsp;</td>',
                '<td style="padding: 2px;">{WhsDocumentSupply_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{Supplier_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id' },
                    { name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id' },
                    { name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
                    { name: 'WhsDocumentUc_Num', mapping: 'WhsDocumentUc_Num' },
                    { name: 'WhsDocumentSupply_Name', mapping: 'WhsDocumentSupply_Name' },
                    { name: 'Supplier_Name', mapping: 'Supplier_Name' }
                ],
                key: 'WhsDocumentSupply_id',
                sortInfo: { field: 'WhsDocumentSupply_Name' },
                url:'/?c=WhsDocumentOrderReserve&m=loadWhsDocumentSupplyCombo'
            }),
            listeners: {
                'change': function(combo, newValue) {
                    current_window.setFinanceDefaultValues();
                }
            },
            onTrigger2Click: function() {
                var combo = this;

                if (combo.disabled) {
                    return false;
                }

                combo.clearValue();
                delete combo.lastQuery;
                combo.getStore().removeAll();
                combo.getStore().baseParams.query = '';
                combo.fireEvent('change', combo, null);
                current_window.setFinanceDefaultValues();
            },
            setValueById: function(id) {
                var combo = this;
                combo.store.baseParams.WhsDocumentSupply_id = id;
                combo.store.load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams.WhsDocumentSupply_id = null;
                    }
                });
            },
            loadData: function() {
                var combo = this;
                combo.store.load({
                    callback: function(){
                        combo.setValue(null);
                        current_window.setFinanceDefaultValues();
                    }
                });
            }
        });

        this.DrugGrid = new sw.Promed.ViewFrame({
            actions: [
                {name: 'action_add', handler: function() { this.findById('WhsDocumentOrderReserveDrugGrid').editGrid('add') }.createDelegate(this)},
                {name: 'action_edit', disabled: true, handler: function() { this.findById('WhsDocumentOrderReserveDrugGrid').editGrid('edit') }.createDelegate(this)},
                {name: 'action_view', handler: function() { this.findById('WhsDocumentOrderReserveDrugGrid').editGrid('view') }.createDelegate(this)},
                {name: 'action_delete', handler: function() { this.findById('WhsDocumentOrderReserveDrugGrid').deleteRecord() }.createDelegate(this)},
                {name: 'action_refresh', handler: function() {
                    var callback = function() {
                        var that = this;
                        this.findById('WhsDocumentOrderReserveDrugGrid').loadData({
                            url: '/?c=WhsDocumentOrderReserveDrug&m=loadList',
                            params:{WhsDocumentOrderReserve_id: that.form.findField('WhsDocumentOrderReserve_id').getValue()},
                            globalFilters:{WhsDocumentOrderReserve_id: that.form.findField('WhsDocumentOrderReserve_id').getValue()}
                        })
                    }.createDelegate(this);

                    if (this.action == 'view') {
                        callback();
                    } else {
                        this.doSave(callback);
                    }
                }.createDelegate(this)},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=WhsDocumentOrderReserveDrug&m=loadList',
            height: 420,
            region: 'center',
            object: 'WhsDocumentOrderReserveDrug',
            editformclassname: 'swMinzdravDLODocumentsDrugEditWindow',
            id: 'WhsDocumentOrderReserveDrugGrid',
            paging: false,
            saveAtOnce:false,
            style: 'margin-bottom: 0px',
            title: lang['medikamentyi'],
            stringfields: [
                {name: 'WhsDocumentOrderReserveDrug_id', type: 'int', header: 'ID', key: true},
                {name: 'RowNumber', type: 'int', header: lang['№_p_p'], width: 50},
                {name: 'WhsDocumentUc_pid', type: 'int', hidden: true},
                {name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_gk'], width: 200},
                {name: 'WhsDocumentSupply_Year', type: 'string', header: 'Год', width: 50},
                {name: 'Drug_id', type: 'int', hidden: true},
                {name: 'Okei_id', type: 'int', hidden: true},
                {name: 'Drug_Name', hidden: true},
                {name: 'Tradenames_Name', type: 'string', header: lang['torgovoe_naimenovanie'], id: 'autoexpand'},
                {name: 'DrugForm_Name', type: 'string', header: 'Форма выпуска', width: 100},
                {name: 'Drug_Dose', type: 'string', header: 'Дозировка', width: 100},
                {name: 'Drug_Fas', type: 'string', header: 'Фасовка', width: 100},
                {name: 'WhsDocumentOrderReserveDrug_Kolvo', type: 'float', header: lang['kol-vo_up'], width: 100},
                {name: 'WhsDocumentOrderReserveDrug_PriceNDS', type: 'float', header: lang['tsena'], width: 100},
                {name: 'WhsDocumentUc_Sum', type: 'float', header: lang['summa'], width: 100},
                {name: 'Reg_Num', type: 'string', header: 'РУ', width: 100},
                {name: 'Reg_Firm', type: 'string', header: 'Держатель/Владелец РУ', width: 100},
                {name: 'Reg_Country', type: 'string', header: 'Страна держателя/владельца РУ', width: 100},
                {name: 'Reg_Period', type: 'string', header: 'Период действия РУ', width: 100},
                {name: 'Reg_ReRegDate', type: 'string', header: 'Дата переоформления РУ', width: 100},,
                {name: 'state', hidden: true}
            ],
            toolbar: true,
            onRowSelect: function(sm,rowIdx,record) {
                if (record.get('WhsDocumentOrderReserveDrug_id') > 0 && !this.readOnly) {
                    //this.ViewActions.action_edit.setDisabled(false);
                    this.ViewActions.action_delete.setDisabled(false);
                } else {
                    this.ViewActions.action_edit.setDisabled(true);
                    this.ViewActions.action_delete.setDisabled(true);
                }
                if (record.get('WhsDocumentOrderReserveDrug_id') > 0) {
                    this.ViewActions.action_view.setDisabled(false);
                } else {
                    this.ViewActions.action_view.setDisabled(true);
                }
            },
            updateSumm: function() {
                var summ = 0;
                var summ_field = current_window.findById('WhsDocumentOrderReservesEditForm').getForm().findField('WhsDocumentUc_Sum');
                this.getGrid().getStore().each(function(record) {
                    if(record.data.WhsDocumentUc_Sum && record.data.WhsDocumentUc_Sum > 0)
                        summ += (record.data.WhsDocumentUc_Sum * 1);
                });
                summ_field.setValue(summ.toFixed(2));
            },
            setRowNumbering: function() {
                var row_num = 1;
                this.getGrid().getStore().each(function(record) {
                    if (record.get('Drug_id') > 0) {
                        record.set('RowNumber', row_num++);
                        record.commit();
                    }
                });
            },
            editGrid: function (action) {

                var win = Ext.getCmp('WhsDocumentOrderReserveEditWindow');
                var form = win.findById('WhsDocumentOrderReservesEditForm').getForm();

                if (action == null)	action = 'add';

                var view_frame = this;
                var store = view_frame.getGrid().getStore();

                if (action == 'add') {
                    getWnd(view_frame.editformclassname).show({
                        action: action,
                        owner: view_frame,
                        DuplicateRecordChecking: true,
                        params: {
                            WhsDocumentOrderReserve_Percent: form.findField('WhsDocumentOrderReserve_Percent').getValue(),
                            WhsDocumentType_id: form.findField('WhsDocumentType_id').getValue(),
                            Org_id: form.findField('Org_id').getValue(),
                            DrugRequest_id: form.findField('DrugRequest_id').getValue(),
                            WhsDocumentSupply_id: form.findField('WhsDocumentSupply_id').getValue(),
                            DrugFinance_id: form.findField('DrugFinance_id').getValue(),
                            WhsDocumentCostItemType_id: form.findField('WhsDocumentCostItemType_id').getValue()
                        },
                        callback: function(data) {
                            var record = new Ext.data.Record.create(view_frame.jsonData['store']);
                            data.WhsDocumentOrderReserveDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                            data.grid_id = data.WhsDocumentOrderReserveDrug_id;
                            data.state = 'add';
                            view_frame.clearFilter();
                            var record_count = store.getCount();
                            if ( record_count == 1 && !store.getAt(0).get('WhsDocumentOrderReserveDrug_id') ) {
                                view_frame.removeAll({ addEmptyRecord: false });
                                record_count = 0;
                            }
                            store.insert(record_count, new record(data));
                            view_frame.setFilter();
                            view_frame.updateSumm();
                            view_frame.setRowNumbering();
                        }
                    });
                }

                if (action == 'edit' || action == 'view') {
                    var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
                    if (selected_record && selected_record.get('WhsDocumentOrderReserveDrug_id') > 0) {
                        var params = selected_record.data;

                        params.WhsDocumentOrderReserve_Percent = form.findField('WhsDocumentOrderReserve_Percent').getValue();
                        params.WhsDocumentType_id = form.findField('WhsDocumentType_id').getValue();
                        params.Org_id = form.findField('Org_id').getValue();
                        params.DrugRequest_id = form.findField('DrugRequest_id').getValue();
                        params.WhsDocumentSupply_id = form.findField('WhsDocumentSupply_id').getValue();
                        params.DrugFinance_id = form.findField('DrugFinance_id').getValue();
                        params.WhsDocumentCostItemType_id = form.findField('WhsDocumentCostItemType_id').getValue();

                        getWnd(view_frame.editformclassname).show({
                            action: action,
                            params: params,
                            callback: function(data) {
                                view_frame.clearFilter();

                                for(var key in data) {
                                    selected_record.set(key, data[key]);
                                }
                                if (selected_record.get('state') != 'add') {
                                    selected_record.set('state', 'edit');
                                }

                                view_frame.setFilter();
                                view_frame.updateSumm();
                            }
                        });
                    }
                }
            },
            deleteRecord: function(idx){ //удаление записи, если idx не указан то удаляется выбранная запись
                var view_frame = this;
                var record = idx >= 0 ? view_frame.getGrid().getStore().getAt(idx) : view_frame.getGrid().getSelectionModel().getSelected();
                if (record.get('state') == 'add') {
                    view_frame.getGrid().getStore().remove(record);
                } else {
                    record.set('state', 'delete');
                    record.commit();
                    this.setFilter();
                }
                view_frame.updateSumm();
                view_frame.setRowNumbering();
            },
            getChangedData: function(){ //возвращает новые и измненные показатели
                var data = new Array();
                this.clearFilter();
                this.getGrid().getStore().each(function(record) {
                    if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete'))
                        data.push(record.data);
                });
                this.setFilter();
                return data;
            },
            getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
                var dataObj = this.getChangedData();
                return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
            },
            clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
                this.getGrid().getStore().clearFilter();
            },
            setFilter: function() { //скрывает удаленные записи
                this.getGrid().getStore().filterBy(function(record){
                    return (record.get('state') != 'delete');
                });
            },
            findDuplicate: function(data) { //возвращает индекс записи с указаным идентификаторм медикамента и номером ГК
                var idx = this.getGrid().getStore().findBy(function(record) {
                    return (record.get('Drug_id') == data.Drug_id && record.get('WhsDocumentUc_Num') == data.WhsDocumentUc_Num && record.get('state') != 'delete')
                });
                return idx;
            },
            onDblClick: function() {
                this.onEnter();
            },
            onEnter: function() {
                if (!this.ViewActions.action_edit.isDisabled()) {
                    this.ViewActions.action_edit.execute();
                } else {
                    this.ViewActions.action_view.execute();
                }
            }
        });
		
		var form = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'background:#DFE8F6; padding: 0;',
			border: false,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentOrderReservesEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6; padding: 5px;',
				border: false,
				labelWidth: 180,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url: '/?c=WhsDocumentOrderReserve&m=save',
				layout: 'form',
				items: [{
					id: 'WDOREW_WhsDocumentUc_id',
					name: 'WhsDocumentUc_id',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'WDOREW_WhsDocumentOrderReserve_id',
					name: 'WhsDocumentOrderReserve_id',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'WDOREW_WhsDocumentType_id',
					name: 'WhsDocumentType_id',
					value: 12,
					xtype: 'hidden'
				}, {
					name: 'ReserveDrugJSON',
					xtype: 'hidden'
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 350,
						items: [{
							xtype: 'textfield',
							fieldLabel : lang['№'],
							width: 120,
							tabIndex: current_window.firstTabIndex + 10,
							name: 'WhsDocumentUc_Num',
							id: 'WDOREW_WhsDocumentUc_Num',
							value: '',
							allowBlank:false
						}]						
					}, {
						layout: 'form',
						border: false,
						width: 235,
						labelWidth: 30,
						items: [{
							xtype: 'swdatefield',
							fieldLabel : lang['ot'],
							tabIndex: current_window.firstTabIndex + 10,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'WhsDocumentUc_Date',
							id: 'WDOREW_WhsDocumentUc_Date',
							allowBlank: false
						}]
					}, {
						layout: 'form',
						border: false,
						width: 250,
						labelWidth: 115,
						items: [{
							fieldLabel: lang['status_dokumenta'],
							tabIndex: current_window.firstTabIndex + 10,
							hiddenName: 'WhsDocumentStatusType_id',
							id: 'WDOREW_WhsDocumentStatusType_id',
							xtype: 'swcommonsprcombo',
							sortField:'WhsDocumentStatusType_Code',
							comboSubject: 'WhsDocumentStatusType',
							width: 130,
							disabled: true,
							allowBlank:true,
							value: 1
						}]
					}]
				}, {
					xtype: 'textfield',
					fieldLabel : lang['naimenovanie'],
					tabIndex: current_window.firstTabIndex + 10,
					width: 650,
					name: 'WhsDocumentUc_Name',
					id: 'WDOREW_WhsDocumentUc_Name',						
					allowBlank:false
				}, {
					xtype: 'sworgcombo',
					fieldLabel : lang['organizatsiya'],
					tabIndex: current_window.firstTabIndex + 10,
					hiddenName: 'Org_id',
					id: 'WDOREW_Org_id',
					width: 650,
					disabled: true,
					editable: false,
					onTrigger1Click: function() {
						return false;
					},
                    setValueById: function(id) {
                        var combo = this;

                        combo.getStore().load({
                            params: {
                                Object: 'Org',
                                Org_id: id,
                                Org_Name: ''
                            },
                            callback: function() {
                                combo.setValue(id);
                            }
                        });
                    }
				},
                current_window.request_combo,
                current_window.supply_combo,
                {
					fieldLabel: lang['istochnik_finansirovaniya'],
					hiddenName: 'DrugFinance_id',
					xtype: 'swcommonsprcombo',
					tabIndex: current_window.firstTabIndex + 10,
					comboSubject: 'DrugFinance',
					width: 250,
					allowBlank: false
				}, {
					fieldLabel: lang['statya_rashoda'],
					hiddenName: 'WhsDocumentCostItemType_id',
					xtype: 'swcommonsprcombo',
					tabIndex: current_window.firstTabIndex + 10,
					comboSubject: 'WhsDocumentCostItemType',
					width: 250,
					typeCode: 'int',
					allowBlank: false
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						items: [{
							fieldLabel: lang['velichina_rezerva'],
							name: 'WhsDocumentOrderReserve_Percent',
							xtype: 'numberfield',
							tabIndex: current_window.firstTabIndex + 10,
							maxValue: 100,
							minValue: 0.01,
							value: 20,
							width: 120,
							allowBlank: false
						}]
					}, {
						layout: 'form',
						border: false,
						width: 350,
						items: [{
							style: 'margin: 3px',
							html: lang['%_ot_dostupnogo_kolichestva']
						}]
					}]
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						items: [{
							fieldLabel: lang['summa'],
							name: 'WhsDocumentUc_Sum',
							xtype: 'numberfield',
							width: 120,
							disabled: true
						}]
					}, {
						layout: 'form',
						border: false,
						width: 350,
						items: [{
							style: 'margin: 3px',
							html: lang['rub']
						}]
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						style: "padding-left: 185px; padding-bottom: 10px",
						xtype: 'button',
						id: 'BtnGen',
						text: lang['sformirovat_spisok'],
						//iconCls: 'resetsearch16',
						handler: function() {
							var form = current_window.findById('WhsDocumentOrderReservesEditForm').getForm();
							var viewframe = current_window.findById('WhsDocumentOrderReserveDrugGrid');
							
							if ( !form.isValid() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function() {
										current_window.findById('WhsDocumentOrderReservesEditForm').getFirstInvalidEl().focus(true);
									},
									icon: Ext.Msg.WARNING,
									msg: ERR_INVFIELDS_MSG,
									title: ERR_INVFIELDS_TIT
								});
								return false;
							}

                            var params = {
                                WhsDocumentType_id: current_window.WhsDocumentType_id,
                                Org_id: form.findField('Org_id').getValue(),
                                WhsDocumentOrderReserve_Percent: form.findField('WhsDocumentOrderReserve_Percent').getValue(),
                                DrugRequest_id: form.findField('DrugRequest_id').getValue(),
                                WhsDocumentSupply_id: form.findField('WhsDocumentSupply_id').getValue(),
                                DrugFinance_id: form.findField('DrugFinance_id').getValue(),
                                WhsDocumentCostItemType_id: form.findField('WhsDocumentCostItemType_id').getValue()
                            }
							
							viewframe.loadData({
								url: '/?c=WhsDocumentOrderReserveDrug&m=loadRAWList',
								params: params,
								globalFilters: params,
								callback: function () {
									viewframe.updateSumm();
									viewframe.setRowNumbering();
								}
							});
						}
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'WhsDocumentUc_pid'}, 
				{name: 'WhsDocumentUc_Num'}, 
				{name: 'WhsDocumentUc_Name'}, 
				{name: 'WhsDocumentType_id'}, 
				{name: 'WhsDocumentUc_Date'}, 
				{name: 'Org_sid'}, 
				{name: 'Org_cid'}, 
				{name: 'Org_pid'}, 
				{name: 'Org_rid'}, 
				{name: 'WhsDocumentUc_Sum'}, 
				{name: 'WhsDocumentSupply_id'}, 
				{name: 'WhsDocumentUc_id'}, 
				{name: 'WhsDocumentSupply_ProtNum'}, 
				{name: 'WhsDocumentSupply_ProtDate'}, 
				{name: 'WhsDocumentSupplyType_id'}, 
				{name: 'WhsDocumentSupply_ExecDate'}, 
				{name: 'DrugFinance_id'}, 
				{name: 'WhsDocumentCostItemType_id'},
				{name: 'WhsDocumentStatusType_id'}
			])
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{			
				hidden: true,
				handler: function() {},
				iconCls: null,
				text: lang['kontrol_postavki']
			}, {			
				handler: function() {
					this.ownerCt.doSave(false);
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {								
				handler: function() {
					this.ownerCt.doSave(function() {
                        current_window.doSign();
                    });
				},
				iconCls: null,
				text: lang['podpisat']
			}, {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
                form,
                this.DrugGrid
            ]
		});
		sw.Promed.swWhsDocumentOrderReserveEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentOrderReservesEditForm').getForm();
	}	
});