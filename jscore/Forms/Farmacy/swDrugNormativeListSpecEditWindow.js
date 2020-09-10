/**
* swDrugNormativeListSpecEditWindow - произвольное окно редактирования строки нормативного перечня
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      10.2012
* @comment      
*/
sw.Promed.swDrugNormativeListSpecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['stroka_normativnogo_perechnya_redaktirovanie'],
	layout: 'border',
	id: 'DrugNormativeListSpecEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	onSave: Ext.emptyFn,
	onSelectDrug: function(combo) {
		var wnd =  this;
		var rlsact_id = combo.getValue();
		var form_arr = new Array();
		var form_list = '';

		var panel = this.DrugFormPanel;
		panel.items.each(function(item,index,length) {
			var c = panel.getField(item);
			c.getStore().baseParams.RlsActmatters_id = rlsact_id;
			form_arr.push(c.getValue());
		});

		form_list = form_arr.join(',');

		panel = this.TorgNamePanel;
		panel.items.each(function(item,index,length) {
			var c = panel.getField(item);
			c.getStore().baseParams.RlsActmatters_id = rlsact_id;
			c.getStore().baseParams.DrugFormList = form_list;
		});

		//получение кода МНН из номенклатурного справочника
		var code_field = wnd.form.findField('DrugMnnCode_Code');
		if (rlsact_id > 0) {
			Ext.Ajax.request({
				params:{
					ActMatters_id: rlsact_id
				},
				failure:function () {
					code_field.setValue(null);
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					var mnn_code = null;
					if (result[0] && result[0].DrugMnnCode_Code && result[0].DrugMnnCode_Code > 0) {
						mnn_code = result[0].DrugMnnCode_Code;
					}
					code_field.setValue(mnn_code);
				},
				url:'/?c=DrugNomen&m=getDrugMnnCodeByActMattersId'
			});
		} else {
			code_field.setValue(null);
		}
	},
	getData: function() {
		var data = new Object();
		var arr = new Array();
		data.DrugNormativeListSpec_id = this.DrugNormativeListSpec_id;
		data.RlsActmatters_id = this.form.findField('RlsActmatters_id').getValue();		
		data.RlsActmatters_RusName = data.RlsActmatters_id > 0 ? this.form.findField('RlsActmatters_id').lastSelectionText : '';
		data.DrugNormativeListSpec_BegDT = this.dateMenu.getValue1();
		data.DrugNormativeListSpec_EndDT = this.dateMenu.getValue2();
		data.DrugFormArray = new Array();
		data.TorgNameArray = new Array();
		data.DrugNormativeListSpec_IsVK = this.form.findField('DrugNormativeListSpec_IsVK').getValue();
		
		arr = this.DrugFormPanel.getData();		
		for(var i = 0; i < arr.length; i++)
			if (arr[i].value > 0)
				data.DrugFormArray.push(arr[i].value);

		arr = this.TorgNamePanel.getData();		
		for(var i = 0; i < arr.length; i++)
			if (arr[i].value > 0)
				data.TorgNameArray.push(arr[i].value);

		return data;
	},
	setData: function(data) {
		var wnd = this;
		if (data.RlsActmatters_id > 0)
			wnd.form.findField('RlsActmatters_id').setValue(data.RlsActmatters_id);
		if (data.DrugNormativeListSpec_BegDT || data.DrugNormativeListSpec_EndDT)
			wnd.dateMenu.setValue((data.DrugNormativeListSpec_BegDT ? Ext.util.Format.date(data.DrugNormativeListSpec_BegDT,'d.m.Y') : '')+' - '+(data.DrugNormativeListSpec_EndDT ? Ext.util.Format.date(data.DrugNormativeListSpec_EndDT,'d.m.Y') : ''));
		if (data.DrugFormArray != '')
			wnd.DrugFormPanel.setData(data.DrugFormArray.split(','));
		if (data.TorgNameArray != '')		
			wnd.TorgNamePanel.setData(data.TorgNameArray.split(','));


		wnd.form.findField('DrugNormativeListSpec_IsVK').setValue(data.DrugNormativeListSpec_IsVK);
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.form;		
		
		if (disable) {
			wnd.dateMenu.disable();
			form.findField('RlsActmatters_id').disable();
			wnd.buttons[0].disable();
		} else {
			wnd.dateMenu.enable();
			form.findField('RlsActmatters_id').enable();
			wnd.buttons[0].enable();
		}
		
		wnd.DrugFormPanel.setDisabled(disable);
		wnd.TorgNamePanel.setDisabled(disable);
	},
	doSave:  function() {
		var wnd = this;
		if (!this.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('dsleDrugNormativeListSpecEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var data = wnd.getData();
		if (data.RlsActmatters_id < 1 && data.TorgNameArray.length < 1) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: lang['neobhodimo_ukazat_mnn_ili_hotya_byi_odno_torgovoe_naimenovanie']
			});
			return false;
		}

		Ext.Ajax.request({ //дописываем недостающие данные по мнн и лек. формам
			params:{
				RlsActmatters_id: data.RlsActmatters_id,
				DrugFormArray: data.DrugFormArray.join(','),
				TorgNameArray: data.TorgNameArray.join(',')
			},
			failure:function () {
				wnd.onSave(data);				
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result[0]) {
					Ext.apply(data, result[0]);					
				}
				wnd.onSave(data);
			},
			url:'/?c=DrugNormativeList&m=getDrugNormativeListSpecContext'
		});
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swDrugNormativeListSpecEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugNormativeListSpec_id = null;
		this.DrugNormativeList_BegDT = null;
		this.DrugNormativeList_EndDT = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		this.action = (arguments[0].action) ? arguments[0].action : 'add';
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		} else
			this.onSave = Ext.emptyFn;
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugNormativeListSpec_id ) {
			this.DrugNormativeListSpec_id = arguments[0].DrugNormativeListSpec_id;
		}
		if ( arguments[0].DrugNormativeList_BegDT ) {
			this.DrugNormativeList_BegDT = arguments[0].DrugNormativeList_BegDT;
		}
		if ( arguments[0].DrugNormativeList_EndDT ) {
			this.DrugNormativeList_EndDT = arguments[0].DrugNormativeList_EndDT;
		}
		
		this.form.reset();
		this.DrugFormPanel.reset();
		this.TorgNamePanel.reset();
		this.setTitle(lang['stroka_normativnogo_perechnya']);
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + lang['_dobavlenie']);
				if (wnd.DrugNormativeList_BegDT) {
					var date_str = '';
					date_str = wnd.DrugNormativeList_BegDT.format('d.m.Y');
					if (wnd.DrugNormativeList_EndDT)
						date_str += ' - ' + wnd.DrugNormativeList_EndDT.format('d.m.Y');
					wnd.dateMenu.setValue(date_str);
				}
				loadMask.hide();
			break;
			case 'view':
			case 'edit':
				this.setTitle(this.title + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				wnd.setData(arguments[0]);
				wnd.onSelectDrug(wnd.form.findField('RlsActmatters_id'));
				loadMask.hide();
			break;	
		}
		wnd.setDisabled(wnd.action == 'view');
	},
	initComponent: function() {
		var wnd = this;		

		this.DrugFormPanel = new sw.Promed.swMultiFieldPanel({
			title: lang['formyi_vyipuska_razreshennyie_k_ispolzovaniyu'],
			style: 'margin-top: 8px;',
			collapsible: true,
			onFieldAdd: function(c) {
				var rlsact_id = wnd.form.findField('RlsActmatters_id').getValue();
				if (rlsact_id > 0)
					c.getStore().baseParams.RlsActmatters_id = rlsact_id;
			},
			createField: function() {
				var field = new sw.Promed.SwCombo({
					width: 300,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'RlsClsdrugforms_id'
						},
						[
							{name: 'RlsClsdrugforms_id', mapping: 'RlsClsdrugforms_id'},
							{name: 'RlsClsdrugforms_Name', mapping: 'RlsClsdrugforms_Name'}
						]),
						url: '/?c=DrugNormativeList&m=loadDrugFormsCombo'
					}),
					triggerAction: 'all',
					editable: true,
					displayField: 'RlsClsdrugforms_Name',
					valueField: 'RlsClsdrugforms_id',
					onTriggerClick: function() {
						if (this.disabled)
							return false;
						var combo = this;
						var params = {};
						if (!this.isExpanded()) {
							if (combo.getValue() > 0)
								combo.getStore().baseParams.RlsClsdrugforms_id = combo.getValue();
							combo.focus();
							combo.getStore().load({
								params: params,
								callback: function() {
									combo.getStore().baseParams.RlsClsdrugforms_id = null;
								}
							});
							combo.expand();
						} else {
							this.collapse();
						}
						return false;
					},
					setValue: function(v) {
						var combo = this;
						sw.Promed.SwCombo.superclass.setValue.apply(this, arguments);
						if (v == '' || v == null) {
							combo.getStore().baseParams.RlsClsdrugforms_id = null;
							combo.getStore().baseParams.query = null;
						} else {
							var r = this.findRecord(this.valueField, v);
							if (!r) {
								combo.getStore().load({
									params: {RlsClsdrugforms_id: v},
									callback: function() {
										if (combo.getStore().getCount()>0) {
											sw.Promed.SwCombo.superclass.setRawValue.call(combo, combo.getStore().getAt(0).get(combo.displayField));
										}
										combo.getStore().baseParams.RlsClsdrugforms_id = null;
									}
								});
							}
						}
					}
				});
				return field;
			}
		});

		this.TorgNamePanel = new sw.Promed.swMultiFieldPanel({
			title: lang['torgovyie_naimenovaniya_razreshennyie_k_ispolzovaniyu'],
			style: 'margin-top: 8px;',
			collapsible: true,
			onFieldAdd: function(c) {
				var rlsact_id = wnd.form.findField('RlsActmatters_id').getValue();
				if (rlsact_id > 0)
					c.getStore().baseParams.RlsActmatters_id = rlsact_id;
			},
			createField: function() {
				var field = new sw.Promed.SwCombo({
					width: 300,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
								id: 'RlsTradenames_id'
							},
							[
								{name: 'RlsTradenames_id', mapping: 'RlsTradenames_id'},
								{name: 'RlsTradenames_Name', mapping: 'RlsTradenames_Name'}
							]),
						url: '/?c=DrugNormativeList&m=loadTradenamesCombo'
					}),
					triggerAction: 'all',
					editable: true,
					displayField: 'RlsTradenames_Name',
					valueField: 'RlsTradenames_id',
					onTriggerClick: function() {
						if (this.disabled)
							return false;
						var combo = this;
						var params = {};
						if (!this.isExpanded()) {
							if (combo.getValue() > 0)
								combo.getStore().baseParams.RlsTradenames_id = combo.getValue();
							else
								combo.setDrugFormList();
							combo.focus();
							combo.getStore().load({
								params: params,
								callback: function() {
									combo.getStore().baseParams.RlsTradenames_id = null;
								}
							});
							combo.expand();
						} else {
							this.collapse();
						}
						return false;
					},
					setValue: function(v) {
						var combo = this;
						sw.Promed.SwCombo.superclass.setValue.apply(this, arguments);
						if (v == '' || v == null) {
							combo.getStore().baseParams.RlsTradenames_id = null;
							combo.getStore().baseParams.query = null;
						} else {
							var r = this.findRecord(this.valueField, v);
							if (!r) {
								combo.getStore().load({
									params: {RlsTradenames_id: v},
									callback: function() {
										if (combo.getStore().getCount()>0) {
											sw.Promed.SwCombo.superclass.setRawValue.call(combo, combo.getStore().getAt(0).get(combo.displayField));
										}
										combo.getStore().baseParams.RlsTradenames_id = null;
									}
								});
							}
						}
					},
					setDrugFormList: function() {
						var form_arr = new Array();
						var arr = wnd.DrugFormPanel.getData();
						for(var i = 0; i < arr.length; i++)
							if (arr[i].value > 0)
								form_arr.push(arr[i].value);
						this.getStore().baseParams.DrugFormList = form_arr.join(',');
					}
				});
				return field;
			}
		});

		/*this.TorgNamePanel = new sw.Promed.swMultiFieldPanel({
			title: lang['ogranichenie_po_torgovyim_naimenovaniyam'],
			style: 'margin-top: 8px;',
			collapsible: true,			
			createField: function() {
				var field = new sw.Promed.SwTradenamesCombo({
					width: 300,
					anchor: ''
				});
				return field;
			}
		});*/

		this.dateMenu = new Ext.form.DateRangeField({
			width: 175,
			fieldLabel: lang['period_deystviya_zapisi'],
			hiddenName: 'DrugNormativeListSpec_DateRange',
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,			
			frame: false,
			region: 'south',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'dsleDrugNormativeListSpecEditForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=DrugNormativeListSpec&m=save',
				items: [{
					fieldLabel: lang['mnn'],
					hiddenName: 'RlsActmatters_id',
					listeners: {
						'select': function(combo, record) {
							wnd.onSelectDrug(combo, record);
						}
					},
					xtype: 'swrlsactmatterscombo',					
					allowBlank: true,
					width: 500
				}, {
					fieldLabel: lang['kod_mnn'],
					name: 'DrugMnnCode_Code',
					xtype: 'textfield',
					disabled: true,
					width: 175
				}, {
					hidden: true,
					items: [{
						hiddenName: 'hiddenField1',
						xtype: 'swrlsclsdrugformscombo'
					},{
						hiddenName: 'hiddenField2',
						xtype: 'swrlstradenamescombo'
					}]
				},
				wnd.dateMenu,
				{
					name: 'DrugNormativeListSpec_IsVK',
					fieldLabel: lang['vyipisyivaetsya_cherez_vk'],
					xtype: 'checkbox'
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'form',
			bodyStyle: 'padding: 7px;',
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
			HelpButton(this, 0),//todo проставить табиндексы
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
				this.DrugFormPanel,
				this.TorgNamePanel
			]
		});
		sw.Promed.swDrugNormativeListSpecEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('dsleDrugNormativeListSpecEditForm').getForm();
	}	
});