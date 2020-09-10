/**
* swDrugRequestRowEditWindow - окно редактирования медикамента в спецификации заявки врача
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      12.2012
* @comment      
*/
sw.Promed.swDrugRequestRowEditWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 364,
	title: lang['dobavlenie_medikamenta'],
	layout: 'border',
	id: 'DrugRequestRowEditWindow',
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
	doSave:  function() {
		var wnd = this;
		var params = new Object();
		Ext.apply(params, wnd.form.getValues());

		if (params.DrugComplexMnn_id > 0) {
			var rec = wnd.mnn_combo.store.getById(params.DrugComplexMnn_id);
			params.DrugComplexMnn_Price = rec.get('DrugComplexMnn_Price');
			params.DrugComplexMnn_RusName = rec.get('DrugComplexMnn_RusName');
		} else {
			params.DrugComplexMnn_Price = null;
			params.DrugComplexMnn_RusName = null;
		}

		params.DrugRequestType_id = wnd.form.findField('DrugRequestType_id').getValue();

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

		wnd.onSave(params);
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestRowEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequestRow_id = null;
		this.PersonRegisterType_id = null;
		this.PersonRegisterType_SysNick = null;
		this.DrugRequestPeriod_id = null;
		this.allowAllDrugRequestType = null;
		this.DrugRequestType_id = null;
		this.mode = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
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
		if ( arguments[0].DrugRequestRow_id ) {
			this.DrugRequestRow_id = arguments[0].DrugRequestRow_id;
		}
		if ( arguments[0].PersonRegisterType_id ) {
			this.PersonRegisterType_id = arguments[0].PersonRegisterType_id;
		}
		if ( arguments[0].DrugRequestPeriod_id ) {
			this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id;
		}
		if ( arguments[0].allowAllDrugRequestType ) {
			this.allowAllDrugRequestType = arguments[0].allowAllDrugRequestType;
		}
		if ( arguments[0].DrugRequestType_id ) {
			this.DrugRequestType_id = arguments[0].DrugRequestType_id;
		}
		if ( arguments[0].mode ) {
			this.mode = arguments[0].mode;
		}
		//для некоторых морбусов существуют специальные ограничения
		if ( arguments[0].PersonRegisterType_SysNick ) {
			this.PersonRegisterType_SysNick = arguments[0].PersonRegisterType_SysNick;

			if (this.PersonRegisterType_SysNick == 'common_fl' || this.PersonRegisterType_SysNick == 'common_rl') {
				this.allowAllDrugRequestType = false;
				this.DrugRequestType_id = this.PersonRegisterType_SysNick == 'common_fl' ? 1 : 2;
			}
		}
		this.form.reset();

		wnd.mnn_combo.store.baseParams.PersonRegisterType_id = wnd.PersonRegisterType_id;
		wnd.mnn_combo.store.baseParams.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id;
		wnd.tradenames_combo.store.baseParams.PersonRegisterType_id = wnd.PersonRegisterType_id;
		wnd.tradenames_combo.store.baseParams.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id;
		wnd.tradenames_combo.store.baseParams.DrugCommplexMnn_id = null;
		wnd.form.findField('DrugRequestType_id').enable();

		if (this.mode == 'pacient') { //Поля с дозами показываем только при редактировании персональной заявки
			Ext.getCmp('drrewDoseFields').show();
			wnd.default_height = 389;
		} else {
			Ext.getCmp('drrewDoseFields').hide();
			wnd.default_height = 293;
		}
		wnd.tradenames_combo.show(false); //не только скрывает комбо, но и устанавливает высоту окна по умолчанию

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (wnd.action) {		
			case 'add':
				if (wnd.DrugRequestType_id && wnd.DrugRequestType_id > 0) {
					wnd.form.findField('DrugRequestType_id').setValue(wnd.DrugRequestType_id);
					wnd.mnn_combo.store.baseParams.DrugRequestType_id = wnd.DrugRequestType_id;
					wnd.tradenames_combo.store.baseParams.DrugRequestType_id = wnd.DrugRequestType_id;
				} else {
					wnd.mnn_combo.store.baseParams.DrugRequestType_id = null;
					wnd.tradenames_combo.store.baseParams.DrugRequestType_id = null;
				}
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
				var mnn_id = arguments[0].DrugComplexMnn_id;
				var torg_id = arguments[0].TRADENAMES_id;

				wnd.form.setValues(arguments[0]);
				wnd.mnn_combo.store.baseParams.DrugRequestType_id = wnd.DrugRequestType_id;
				wnd.tradenames_combo.store.baseParams.DrugRequestType_id = wnd.DrugRequestType_id;
				wnd.tradenames_combo.store.baseParams.DrugComplexMnn_id = mnn_id;

				wnd.mnn_combo.store.load({callback: function() {
					if (wnd.mnn_combo.getStore().findBy(function(rec) { return rec.get('DrugComplexMnn_id') == mnn_id; }) >= 0) {
						wnd.mnn_combo.setValue(mnn_id);
					} else {
						wnd.mnn_combo.setValue(null);
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
				loadMask.hide();
			break;	
		}

		if (wnd.allowAllDrugRequestType) {
			wnd.form.findField('DrugRequestType_id').enable();
		} else {
			wnd.form.findField('DrugRequestType_id').disable();
		}
	},
	initComponent: function() {
		var wnd = this;	

		wnd.mnn_combo = new Ext.form.ComboBox({
			anchor: '100%',
			allowBlank: false,
			displayField: 'DrugComplexMnn_RusName',
			enableKeyEvents: true,
			fieldLabel: lang['naimenovanie_ls'],
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
					tnc_store.load({callback: function() {
						if (tnc_store.findBy(function(rec) { return rec.get('TRADENAMES_ID') == combo.getValue(); }) < 0) {
							wnd.tradenames_combo.setValue(null);
						}
						wnd.tradenames_combo.show(wnd.tradenames_combo.getStore().getCount() > 0);
						wnd.tradenames_combo.focus();
						wnd.tradenames_combo.collapse();
					}});

					this.setLinkedFields(record);
				},
				'change':  function(combo, newValue, oldValue) {
					if (newValue < 1) {
						combo.setLinkedFields(null);
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
							{name: 'DrugComplexMnn_RusName', mapping: 'DrugComplexMnn_RusName'},
							{name: 'DrugComplexMnn_Price', mapping: 'DrugComplexMnn_Price'},
							{name: 'DrugListRequest_IsProblem', mapping: 'DrugListRequest_IsProblem'},
							{name: 'ATX_Code', mapping: 'ATX_Code'},
							{name: 'DrugComplexMnnName_Name', mapping: 'DrugComplexMnnName_Name'},
							{name: 'ClsDrugForms_Name', mapping: 'ClsDrugForms_Name'},
							{name: 'DrugComplexMnnDose_Name', mapping: 'DrugComplexMnnDose_Name'},
							{name: 'DrugComplexMnnFas_Name', mapping: 'DrugComplexMnnFas_Name'}
						]),
					url: '/?c=MzDrugRequest&m=loadDrugComplexMnnCombo'
				});
			},
			setLinkedFields: function(record) {
				wnd.form.findField('ATX_Code').setValue(null);
				wnd.form.findField('DrugRequestRow_Name').setValue(null);
				wnd.form.findField('ClsDrugForms_Name').setValue(null);
				wnd.form.findField('DrugComplexMnnDose_Name').setValue(null);
				wnd.form.findField('DrugComplexMnnFas_Name').setValue(null);

				if (record) {
					wnd.form.findField('ATX_Code').setValue(record.get('ATX_Code'));
					wnd.form.findField('DrugRequestRow_Name').setValue(record.get('DrugComplexMnnName_Name'));
					wnd.form.findField('ClsDrugForms_Name').setValue(record.get('ClsDrugForms_Name'));
					wnd.form.findField('DrugComplexMnnDose_Name').setValue(record.get('DrugComplexMnnDose_Name'));
					wnd.form.findField('DrugComplexMnnFas_Name').setValue(record.get('DrugComplexMnnFas_Name'));
				}
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
						{name: 'DrugListRequestTorg_IsProblem', type:'int'}
					]
				});
			},
			show: function(show) {
				if (show) {
					this.ownerCt.show();
				} else {
					this.ownerCt.hide();
				}
				wnd.setHeight(show ? wnd.default_height+42 : wnd.default_height);
			}
		});
		
		var form = new Ext.Panel({
			//autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			autoHeight: true,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DrugRequestRowEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 140,
				collapsible: true,
				url:'/?c=DrugRequestRow&m=save',
				items: [{
					xtype: 'hidden',
					fieldLabel: lang['identifikator_zayavki'],
					name: 'DrugRequest_id'
				}, {
					xtype: 'hidden',
					fieldLabel: lang['identifikator_stroki'],
					name: 'DrugRequestRow_id'
				}, {
					xtype: 'swdrugrequesttypecombo',
					fieldLabel: lang['tip'],
					name: 'DrugRequestType_id',
					allowBlank: false,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var mnn_id = wnd.mnn_combo.getValue();

							wnd.tradenames_combo.store.baseParams.DrugRequestType_id = combo.getValue();
							wnd.tradenames_combo.store.baseParams.DrugComplexMnn_id = null;
							wnd.tradenames_combo.store.removeAll();
							wnd.tradenames_combo.setValue(null);
							wnd.tradenames_combo.show(false);

							wnd.mnn_combo.store.baseParams.DrugRequestType_id = combo.getValue();
							wnd.mnn_combo.store.load({callback: function(store) {
								if (mnn_id > 0) {
									var num = wnd.mnn_combo.store.findBy(function(rec) { return rec.get('DrugComplexMnn_id') == mnn_id; });
									wnd.mnn_combo.setValue(num >= 0 ? mnn_id : null);
								}
							}});
						}
					}
				},
				wnd.mnn_combo,
				{
					fieldLabel: lang['ath'],
					xtype: 'textfield',
					name: 'ATX_Code',
					anchor: '100%',
					disabled: true
				}, {
					fieldLabel: lang['mnn'],
					xtype: 'textfield',
					name: 'DrugRequestRow_Name',
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
				},
				{
					xtype: 'hidden',
					fieldLabel: lang['medikament'],
					name: 'DrugProtoMnn_id'
				}, {
					xtype: 'textfield',
					fieldLabel: lang['kolichestvo'],
					name: 'DrugRequestRow_Kolvo',
					allowBlank: false
				}, {
					id: 'drrewDoseFields',
					layout: 'form',
					items: [{
						xtype: 'textfield',
						fieldLabel: lang['razovaya_doza'],
						name: 'DrugRequestRow_DoseOnce',
						maxLength: 30,
						allowBlank: true
					}, {
						xtype: 'textfield',
						fieldLabel: lang['dnevnaya_doza'],
						name: 'DrugRequestRow_DoseDay',
						maxLength: 30,
						allowBlank: true
					}, {
						xtype: 'textfield',
						fieldLabel: lang['kursovaya_doza'],
						name: 'DrugRequestRow_DoseCource',
						maxLength: 30,
						allowBlank: true
					}, {
						xtype: 'swcommonsprcombo',
						fieldLabel: lang['ed_izm'],
						hiddenName: 'Okei_oid',
						sortField:'Okei_Code',
						comboSubject: 'Okei',
						displayedField: 'Okei_Name',
						width: 249,
						allowBlank: true
					}]
				}]
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
		sw.Promed.swDrugRequestRowEditWindow.superclass.initComponent.apply(this, arguments);
		this.base_form = this.findById('DrugRequestRowEditForm');
		this.form = this.base_form.getForm();
	}	
});