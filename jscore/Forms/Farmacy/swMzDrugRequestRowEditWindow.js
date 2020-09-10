/**
* swMzDrugRequestRowEditWindow - окно редактирования медикамента в спецификации заявки врача
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      06.2015
* @comment      
*/
sw.Promed.swMzDrugRequestRowEditWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 394,
	title: lang['dobavlenie_medikamenta_v_zayavku_na_ls'],
	layout: 'border',
	id: 'MzDrugRequestRowEditWindow',
	modal: true,
	shim: false,
	width: 640,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setWindowHeight: function() {
		var h = this.default_height;

		if (!this.tradenames_combo.ownerCt.hidden) {
            h += 42;
        }

		if (!this.form.findField('DrugFinance_id').hidden) {
            h += 24;
        }

		if (!this.FactorGrid.hidden) {
			h += 230;
		}

		this.setHeight(h);
	},
	doSave:  function() {
		var wnd = this;

		if (!wnd.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.base_form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		wnd.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		wnd.getLoadMask(lang['podojdite_idet_sohranenie']).show();

		var params = new Object();
		params.DrugRequest_id = wnd.DrugRequest_id;
		params.DrugRequestRow_id = wnd.DrugRequestRow_id;
		params.DrugComplexMnn_id = wnd.mnn_combo.getValue();
		params.TRADENAMES_id = wnd.tradenames_combo.getValue();
		params.DrugRequestRow_Kolvo = wnd.form.findField('DrugRequestRow_Kolvo').getValue();
		params.DrugRequestRow_Summa = wnd.form.findField('Sum').getValue();
		params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
		params.PersonRegisterType_id = wnd.PersonRegisterType_id;

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
				if (typeof wnd.callback == 'function') {
					wnd.callback(wnd.owner, action.result.DrugRequestRow_id);
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.DrugRequestRow_id > 0) {
					wnd.DrugRequestRow_id = action.result.DrugRequestRow_id;
				}
				if (typeof wnd.callback == 'function' ) {
					wnd.callback(wnd.owner, action.result.DrugRequestRow_id);
				}
				if (typeof wnd.onSave == 'function' ) {
					wnd.onSave();
				}
			}
		});
	},
	calculateSum: function() {
		var kolvo_field = this.form.findField('DrugRequestRow_Kolvo');
		var price_field = this.form.findField('Price');
		var sum_field = this.form.findField('Sum');
		var mnn_price = null;
		var tn_price = null;

		if (this.mnn_combo.getValue() > 0) {
			var mnn_record = this.mnn_combo.getStore().getById(this.mnn_combo.getValue());
			var mnn_price = !Ext.isEmpty(mnn_record) ? mnn_record.get('DrugComplexMnn_Price') : null;
		}
		if (this.tradenames_combo.getValue() > 0) {
			var tn_record = this.tradenames_combo.getStore().getById(this.tradenames_combo.getValue());
			var tn_price = !Ext.isEmpty(tn_record) ? tn_record.get('Tradenames_Price') : null;
		}

		if (mnn_price > 0 || tn_price > 0) {
			price_field.setValue(tn_price > 0 ? tn_price : mnn_price);
		} else {
			price_field.setValue(null);
		}

		if (!Ext.isEmpty(kolvo_field.getValue()) && !Ext.isEmpty(price_field.getValue())) {
			sum_field.setValue((kolvo_field.getValue()*price_field.getValue()).toFixed(2));
		} else {
			sum_field.setValue(null);
		}
	},
	setDisabled: function(disable) {
		var wnd = this;

		var field_arr = [
			'DrugComplexMnn_id',
			'TRADENAMES_id',
			'DrugRequestRow_Kolvo',
			'DrugFinance_id'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			var combo = wnd.form.findField(field_arr[i]);
			if (disable || combo.enable_blocked || wnd.form.enable_blocked || (field_arr[i] != 'DrugRequestRow_Kolvo' && wnd.action == 'edit')) {
				combo.disable();
			} else {
				combo.enable();
			}
		}

		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
	show: function() {
        var wnd = this;
		sw.Promed.swMzDrugRequestRowEditWindow.superclass.show.apply(this, arguments);

		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.DrugRequest_id = null;
		this.DrugRequestRow_id = null;
		this.DrugFinance_id = null;
		this.PersonRegisterType_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequest_id ) {
			this.DrugRequest_id = arguments[0].DrugRequest_id;
		}
		if ( arguments[0].DrugRequestRow_id ) {
			this.DrugRequestRow_id = arguments[0].DrugRequestRow_id;
		}
		if ( arguments[0].DrugFinance_id ) {
			this.DrugFinance_id = arguments[0].DrugFinance_id;
		}
		if ( arguments[0].PersonRegisterType_id ) {
			this.PersonRegisterType_id = arguments[0].PersonRegisterType_id;
		}

		this.form.reset();

		wnd.mnn_combo.store.baseParams.DrugRequest_id = wnd.DrugRequest_id;
		wnd.tradenames_combo.store.baseParams.DrugRequest_id = wnd.DrugRequest_id;
		wnd.tradenames_combo.store.baseParams.DrugCommplexMnn_id = null;

		wnd.default_height = 341;
		if (/*wnd.action != 'view'*/false) {
			wnd.FactorGrid.show();
		} else {
			wnd.FactorGrid.hide();
		}
		wnd.tradenames_combo.show(false); //не только скрывает комбо, но и устанавливает высоту окна по умолчанию
		if(this.PersonRegisterType_id && this.PersonRegisterType_id == 1){
			wnd.form.findField('DrugFinance_id').showContainer();
			wnd.form.findField('DrugFinance_id').setAllowBlank(false);
			wnd.form.findField('DrugFinance_id').getStore().load({
				params: {where:' where DrugFinance_id = 3 or DrugFinance_id = 27'},
				callback: function(){
					wnd.form.findField('DrugFinance_id').setValue(null);
				}
			});
			wnd.mnn_combo.store.baseParams.DrugFinance_id = null;
            wnd.setWindowHeight();
		} else {
			wnd.form.findField('DrugFinance_id').hideContainer();
			wnd.form.findField('DrugFinance_id').setAllowBlank(true);
			wnd.form.findField('DrugFinance_id').getStore().removeAll();
            wnd.form.findField('DrugFinance_id').setValue(wnd.DrugFinance_id); //если у заявочной кампании один список медикаментов, то берем значение переданное на форму
            wnd.setWindowHeight();
		}
		

		wnd.setDisabled(wnd.action == 'view');

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (wnd.action) {		
			case 'add':
				wnd.mnn_combo.store.load({callback: function() {
					wnd.mnn_combo.focus();
					wnd.mnn_combo.collapse();
				}});
				wnd.tradenames_combo.store.load({callback: function() {
					wnd.tradenames_combo.collapse();
				}});
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugRequestRow_id: wnd.DrugRequestRow_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);

						if (!result[0]) {
							return false
						}

						var mnn_id = result[0].DrugComplexMnn_id;
						var torg_id = result[0].TRADENAMES_id;

						wnd.form.setValues(result[0]);
						wnd.tradenames_combo.store.baseParams.DrugComplexMnn_id = mnn_id;

						wnd.mnn_combo.store.load({callback: function() {
							var idx = wnd.mnn_combo.getStore().findBy(function(rec) { return rec.get('DrugComplexMnn_id') == mnn_id; });

							if (idx >= 0) {
								wnd.mnn_combo.setValue(mnn_id);
								wnd.mnn_combo.setLinkedFields(wnd.mnn_combo.getStore().getAt(idx));
							} else {
								wnd.mnn_combo.setValue(null);
								wnd.mnn_combo.setLinkedFields(null);
							}

							wnd.mnn_combo.focus();
							wnd.mnn_combo.collapse();
						}});

						wnd.tradenames_combo.store.load({callback: function() {
							if (wnd.tradenames_combo.getStore().findBy(function(rec) { return rec.get('TRADENAMES_ID') == torg_id; }) >= 0) {
								wnd.tradenames_combo.setValue(torg_id);
							} else {
								wnd.tradenames_combo.setValue(null);
							}
							wnd.tradenames_combo.show(wnd.tradenames_combo.getStore().getCount() > 0);
							wnd.tradenames_combo.collapse();
						}});

						wnd.FactorGrid.loadData({
							globalFilters: {
								DrugRequest_id: wnd.DrugRequest_id,
								DrugComplexMnn_id: mnn_id,
								TRADENAMES_id: torg_id
							}
						});
						loadMask.hide();
					},
					url:'/?c=MzDrugRequest&m=loadDrugRequestRow'
				});

			break;	
		}
	},
	initComponent: function() {
		var wnd = this;	

		wnd.mnn_combo = new Ext.form.ComboBox({
			anchor: '100%',
			allowBlank: false,
			displayField: 'DrugComplexMnn_RusName',
			enableKeyEvents: true,
			fieldLabel: langs('Наименование'),
			forceSelection: false,
			hiddenName: 'DrugComplexMnn_id',
			loadingText: lang['idet_poisk'],
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			onTrigger2Click: Ext.emptyFn,
			resizable: true,
			selectOnFocus: true,
			triggerAction: 'all',
			valueField: 'DrugComplexMnn_id',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item"{[values.DrugListRequest_IsProblem > 0 ? " style=\'color: #ff0000\'" : ""]}>',
				'<span style="float:left;">{DrugComplexMnn_Price}&nbsp;р.&nbsp;&nbsp;</span><h3>{DrugComplexMnn_RusName}</h3>',
				'</div></tpl>'
			),
			listeners: {
				'select': function(combo, record, index) {
					var tnc_store = wnd.tradenames_combo.getStore();
					tnc_store.baseParams.DrugComplexMnn_id = combo.getValue();
					/*  
					 * Если не персональная разнорядка, то даем право на торговое наименование
					tnc_store.load({callback: function() {
						if (tnc_store.findBy(function(rec) { return rec.get('TRADENAMES_ID') == combo.getValue(); }) < 0) {
							wnd.tradenames_combo.setValue(null);
						}
						wnd.tradenames_combo.show(wnd.tradenames_combo.getStore().getCount() > 0);
						wnd.tradenames_combo.focus();
						wnd.tradenames_combo.collapse();
					}});
					*/

					this.setLinkedFields(record);
				},
				'change':  function(combo, newValue, oldValue) {
					if (newValue < 1) {
						combo.setLinkedFields(null);
					}
					if(newValue > 0 && wnd.form.findField('DrugFinance_id').isVisible()){
						if(Ext.isEmpty(wnd.form.findField('DrugFinance_id').getValue())){
							var rec = combo.getStore().getById(newValue);
							if(rec && rec.get('DrugFinance_id') && wnd.form.findField('DrugFinance_id').getStore().getById(rec.get('DrugFinance_id'))){
								wnd.form.findField('DrugFinance_id').setValue(rec.get('DrugFinance_id'));
								wnd.form.findField('DrugFinance_id').fireEvent('change',wnd.form.findField('DrugFinance_id'),wnd.form.findField('DrugFinance_id').getValue());
							}
						}
					}
				}
			},
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.superclass.initComponent.apply(this, arguments);
				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'DrugComplexMnn_id'
						},
						[
							{name: 'DrugComplexMnn_id', mapping: 'DrugComplexMnn_id'},
							{name: 'DrugComplexMnn_Price', mapping: 'DrugComplexMnn_Price'},
							{name: 'DrugListRequest_IsProblem', mapping: 'DrugListRequest_IsProblem'},
							{name: 'DrugListRequest_Comment', mapping: 'DrugListRequest_Comment'},
							{name: 'ATX_Code', mapping: 'ATX_Code'},
							{name: 'DrugComplexMnnName_Name', mapping: 'DrugComplexMnnName_Name'},
							{name: 'DrugComplexMnn_RusName', mapping: 'DrugComplexMnn_RusName'},
							{name: 'ClsDrugForms_Name', mapping: 'ClsDrugForms_Name'},
							{name: 'DrugComplexMnnDose_Name', mapping: 'DrugComplexMnnDose_Name'},
							{name: 'DrugFinance_id', mapping: 'DrugFinance_id'},
							{name: 'DrugComplexMnnFas_Name', mapping: 'DrugComplexMnnFas_Name'}
						]),
					url: '/?c=MzDrugRequest&m=loadDrugComplexMnnCombo'
				});
			},
			setLinkedFields: function(record) {
				wnd.form.findField('DrugListRequest_Comment').setValue(null);
				wnd.form.findField('ATX_Code').setValue(null);
				wnd.form.findField('DrugComplexMnnName_Name').setValue(null);
				wnd.form.findField('ClsDrugForms_Name').setValue(null);
				wnd.form.findField('DrugComplexMnnDose_Name').setValue(null);
				wnd.form.findField('DrugComplexMnnFas_Name').setValue(null);

				if (record) {
					wnd.form.findField('DrugListRequest_Comment').setValue(record.get('DrugListRequest_Comment'));
					wnd.form.findField('ATX_Code').setValue(record.get('ATX_Code'));
					wnd.form.findField('DrugComplexMnnName_Name').setValue(record.get('DrugComplexMnnName_Name'));
					wnd.form.findField('ClsDrugForms_Name').setValue(record.get('ClsDrugForms_Name'));
					wnd.form.findField('DrugComplexMnnDose_Name').setValue(record.get('DrugComplexMnnDose_Name'));
					wnd.form.findField('DrugComplexMnnFas_Name').setValue(record.get('DrugComplexMnnFas_Name'));
				}

				wnd.calculateSum();

				wnd.FactorGrid.loadData({
					globalFilters: {
						DrugRequest_id: wnd.DrugRequest_id,
						DrugComplexMnn_id: wnd.mnn_combo.getValue(),
						TRADENAMES_id: wnd.tradenames_combo.getValue()
					}
				});
			}
		});

		this.tradenames_combo = new Ext.form.ComboBox({
			fieldLabel: lang['torg_naimenovanie'],
			hiddenName: 'TRADENAMES_id',
			displayField:'NAME',
			valueField: 'TRADENAMES_ID',
			anchor: '100%',
			allowBlank: true,
			enableKeyEvents: true,
			forceSelection: false,
			loadingText: lang['idet_poisk'],
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			onTrigger2Click: Ext.emptyFn,
			resizable: true,
			selectOnFocus: true,
			triggerAction: 'all',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item"{[values.DrugListRequestTorg_IsProblem > 0 ? " style=\'color: #ff0000\'" : ""]}>',
				'<table style="border:0;"><td nowrap>{NAME}&nbsp;</td></tr></table>',
				'</div></tpl>'
			),
			initComponent: function() {
				Ext.form.ComboBox.superclass.initComponent.apply(this, arguments);
				this.store = new Ext.data.JsonStore({
					url: '/?c=MzDrugRequest&m=loadTradenamesCombo',
					key: 'TRADENAMES_ID',
					autoLoad: false,
					fields: [
						{name: 'TRADENAMES_ID', type:'int'},
						{name: 'NAME', type:'string'},
						{name: 'DrugListRequestTorg_IsProblem', type:'int'},
						{name: 'Tradenames_Price', type:'string'}
					]
				});
			},
			listeners: {
				'select': function(combo, record, index) {
					wnd.calculateSum();

					wnd.FactorGrid.loadData({
						globalFilters: {
							DrugRequest_id: wnd.DrugRequest_id,
							DrugComplexMnn_id: wnd.mnn_combo.getValue(),
							TRADENAMES_id: wnd.tradenames_combo.getValue()
						}
					});
				}
			},
			show: function(show) {
				if (show) {
					this.ownerCt.show();
				} else {
					this.ownerCt.hide();
				}
				wnd.setWindowHeight();
			}
		});
		
		var form = new Ext.Panel({
			//autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			autoHeight: true,
			border: false,			
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'MzDrugRequestRowEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 140,
				collapsible: true,
				url:'/?c=MzDrugRequest&m=saveDrugRequestRow',
				items: [
				{
					fieldLabel: lang['finansirovanie'],
					xtype: 'swcommonsprcombo',
					comboSubject: 'DrugFinance',
					anchor: '100%',
					disabled: true,
					listeners: {
						change: function(c,n){
							var mnn_id = wnd.mnn_combo.getValue();
							wnd.mnn_combo.store.baseParams.DrugFinance_id = n;
							wnd.mnn_combo.store.load({callback: function() {
								var idx = wnd.mnn_combo.getStore().find('DrugComplexMnn_id', mnn_id);

								if (idx >= 0) {
									wnd.mnn_combo.setValue(mnn_id);
									wnd.mnn_combo.setLinkedFields(wnd.mnn_combo.getStore().getAt(idx));
								} else {
									wnd.mnn_combo.setValue(null);
									wnd.mnn_combo.setLinkedFields(null);
								}

								wnd.mnn_combo.focus();
								wnd.mnn_combo.collapse();
							}});
						}
					}
				},
				wnd.mnn_combo,
				{
					fieldLabel: lang['primechanie'],
					xtype: 'textfield',
					name: 'DrugListRequest_Comment',
					anchor: '100%',
					disabled: true
				},
				{
					fieldLabel: lang['ath'],
					xtype: 'textfield',
					name: 'ATX_Code',
					anchor: '100%',
					disabled: true
				}, {
					fieldLabel: lang['mnn'],
					xtype: 'textfield',
					name: 'DrugComplexMnnName_Name',
					anchor: '100%',
					disabled: true
				}, {
					fieldLabel: lang['lekarstvennaya_forma'],
					xtype: 'textfield',
					name: 'ClsDrugForms_Name',
					width: 249,
					disabled: true
				}, {
					fieldLabel: lang['dozirovka'],
					xtype: 'textfield',
					name: 'DrugComplexMnnDose_Name',
					width: 249,
					disabled: true
				}, {
					fieldLabel: lang['fasovka'],
					xtype: 'textfield',
					name: 'DrugComplexMnnFas_Name',
					width: 249,
					disabled: true
				}, {
					layout: 'form',
					items: [
						wnd.tradenames_combo,
						{
							xtype: 'panel',
							bodyStyle: 'color: red; padding-left: 136px; padding-bottom: 5px;',
							html: lang['primechanie_torgovoe_naimenovanie_mojet_byit_zakazano_pri_nalichii_resheniya_vk']
						}
					]
				}, {
					xtype: 'numberfield',
					fieldLabel: lang['kolichestvo'],
					name: 'DrugRequestRow_Kolvo',
					allowBlank: false,
					allowNegative: false,
					listeners: {
						'change': function(field, newValue, oldValue) {
                            if (newValue <= 0) {
                                field.setValue(null);
                            }
							wnd.calculateSum();
						}
					}
				}, {
					xtype: 'textfield',
					fieldLabel: lang['tsena'],
					name: 'Price',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['summa'],
					name: 'Sum',
					disabled: true
				}]
			}]
		});

		this.FactorGrid = new sw.Promed.ViewFrame({
			title: lang['analitika_po_stroke'],
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadDrugRequestRowFactorList',
			region: 'center',
			id: 'mdrreFactorGrid',
			paging: false,
			saveAtOnce: false,
			style: 'margin: 0px',
			stringfields: [
				{name: 'Factor_id', type: 'int', header: 'ID', key: true},
				{id: 'autoexpand', name: 'Factor_Name', type: 'string', header: lang['pokazatel']},
				{name: 'Factor_Value', type: 'string', header: lang['znachenie'], width: 100}
			],
			toolbar: true
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
			items:[
				form,
				this.FactorGrid
			]
		});
		sw.Promed.swMzDrugRequestRowEditWindow.superclass.initComponent.apply(this, arguments);
		this.base_form = this.findById('MzDrugRequestRowEditForm');
		this.form = this.base_form.getForm();
	}	
});