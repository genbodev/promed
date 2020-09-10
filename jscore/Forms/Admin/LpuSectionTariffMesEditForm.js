/**
* swLpuSectionTariffMesEditForm - окно просмотра и редактирования тарифов МЭС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      08.10.2010
*/

sw.Promed.swLpuSectionTariffMesEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['tarifyi'] + getMESAlias(),
	id: 'LpuSectionTariffMesEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 500,
	height: 270,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lstmefOk',
		tabIndex: 1326,
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	}, 
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) 
		{
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_FRMCANCEL,
		id: 'lstmefCancel',
		tabIndex: 1327,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}
	],
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	checkDataExits: function () 
	{
		// Проверка на ранее введенные данные 
		var form = this.findById('LpuSectionTariffMesEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionTariffMesEditForm'), { msg: "Подождите, идет проверка..." });
		loadMask.show();
		var params = {
			LpuSectionTariffMes_id: form.findById('lstmefLpuSectionTariffMes_id').getValue(), 
			LpuSection_id: form.findById('lstmefLpuSection_id').getValue(), 
			LpuSectionTariffMes_setDate: Ext.util.Format.date(form.findById('lstmefLpuSectionTariffMes_setDate').getValue(),'d.m.Y'),
			Mes_id: form.findById('MesCombo').getValue() 
		}
		Ext.Ajax.request( {
			url: C_LPUSECTIONTARIFFMES_CHECK,
			params: params,
			callback: function(options, success, response) {
				loadMask.hide();
				if (success) {
					if ( response.responseText.length > 0 ) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result.success) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									form.findById('lstmefLpuSectionTariffMes_setDate').focus(false)
								},
								icon: Ext.Msg.ERROR,
								msg: result.ErrorMessage,
								title: ERR_INVFIELDS_TIT
							});
						} else {
							form.ownerCt.submit();
						}
					}
				}
			}
		});
	},
	doSave: function()  {
		var form = this.findById('LpuSectionTariffMesEditFormPanel');
		if (!form.getForm().isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var begDate = form.findById('lstmefLpuSectionTariffMes_setDate').getValue();
		var endDate = form.findById('lstmefLpuSectionTariffMes_disDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate)) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.findById('lstmefLpuSectionTariffMes_setDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.checkDataExits();
	},
	returnFunc: function(owner, kid) {},
	show: function() {
		sw.Promed.swLpuSectionTariffMesEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionTariffMesEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuSectionTariffMes_id)
			this.LpuSectionTariffMes_id = arguments[0].LpuSectionTariffMes_id;
		else 
			this.LpuSectionTariffMes_id = null;
		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else 
			this.LpuSection_id = null;
		if (arguments[0].LpuSection_Name)
			this.LpuSection_Name = arguments[0].LpuSection_Name;
		else 
			this.LpuSection_Name = null;
		if (arguments[0].LpuUnitType_id)
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		else 
			this.LpuUnitType_id = null;
		
		if (!arguments[0])
			{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
			}
		var form = this;
		form.findById('LpuSectionTariffMesEditFormPanel').getForm().reset();

		switch (this.action)
		{
			case 'add':
				form.setTitle('Тарифы ' + getMESAlias() + ': Добавление');
				break;
			case 'edit':
				form.setTitle('Тарифы ' + getMESAlias() + ': Редактирование');
				break;
			case 'view':
				form.setTitle('Тарифы ' + getMESAlias() + ': Просмотр');
				break;
		}
		
		if (this.action=='view')
		{
			form.findById('lstmefLpuSectionTariffMes_setDate').disable();
			form.findById('lstmefLpuSectionTariffMes_disDate').disable();
			form.findById('MesCombo').disable();
			form.findById('lstLpuSectionTariffMes_Tariff').disable();
			form.findById('TariffMesType_combo').disable();
			form.findById('lstmefLpuSection_id').disable();
			form.findById('lstmefLpuSectionTariffMes_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('lstmefLpuSectionTariffMes_setDate').enable();
			form.findById('lstmefLpuSectionTariffMes_disDate').enable();			
			form.findById('MesCombo').enable();
			form.findById('lstLpuSectionTariffMes_Tariff').enable();
			form.findById('TariffMesType_combo').enable();
			form.findById('lstmefLpuSection_id').enable();
			form.findById('lstmefLpuSectionTariffMes_id').enable();
			form.buttons[0].enable();
		}
		
		form.findById('lstmefLpuSection_id').setValue(this.LpuSection_id);
		form.findById('lstmefLpuSection_Name').setValue(this.LpuSection_Name);
		if (this.action!='add') {			
			form.findById('LpuSectionTariffMesEditFormPanel').getForm().load({
				url: C_LPUSECTIONTARIFFMES_GET,
				params: {
					object: 'LpuSectionTariffMes',
					LpuSectionTariffMes_id: this.LpuSectionTariffMes_id,
					LpuSectionTariffMes_Plan: '',
					LpuSectionTariffMes_Fact: '',
					LpuSectionTariffMes_Repair: '',					
					LpuSectionTariffMes_setDate: '',
					LpuSectionTariffMes_disDate: '',
					LpuSection_id: ''
				},
				success: function () {
					if (form.action!='view') {
							//
					}
					form.findById('MesCombo').getStore().load({
						callback: function(r, o, s) {
							var combo = form.findById('MesCombo');
							
							combo.getStore().each(function(record) {
								if ( record.get('Mes_id') == combo.getValue() ) {
									//form.findById('MesCombo').fireEvent('select', form.findById('MesCombo'), record, 0);
									combo.setValue(record.get(combo.valueField));
									combo.setRawValue(record.get('Mes_Code'));
								}
							});
							combo.focus(true, 100);
						},
						params: {
							Mes_id: form.findById('MesCombo').getValue()
						}
					});					
					loadMask.hide();
				},
				failure: function () {
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				}
			});			
		} else {		
			form.findById('MesCombo').focus(true, 100);
			loadMask.hide();
		}
	},
	submit: function() {
		var form = this.findById('LpuSectionTariffMesEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionTariffMesEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		form.getForm().submit( {
			failure: function(result_form, action) {
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					} else {
						//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
					}
				}
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.LpuSectionTariffMes_id) {
						form.ownerCt.hide();
						form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuSectionTariffMes_id);
					} else
						Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
				}
				else
					Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
			}
		});
	},
	initComponent: function() {
		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'LpuSectionTariffMesEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			items:
			[
			{
				name: 'LpuSectionTariffMes_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lstmefLpuSectionTariffMes_id'
			},
			{
				name: 'LpuSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lstmefLpuSection_id'
			},
			{
				name: 'LpuSection_Name',
				disabled: true,
				fieldLabel: lang['otdelenie'],
				tabIndex: 1,
				xtype: 'descfield',
				id: 'lstmefLpuSection_Name'
			},
			{
				xtype: 'swmesoldcombo',
				width: 200,
				allowBlank: false,
				tabIndex:1222,
				hiddenName: 'Mes_id',
				name: 'Mes_id',
				id: 'MesCombo',
				fieldLabel: lang['kod'] + getMESAlias(),
				disabled: true
			},
			{
				xtype: 'textfield',
				allowBlank: false,
				tabIndex:1223,
				hiddenName: 'LpuSectionTariffMes_Tariff',
				name: 'LpuSectionTariffMes_Tariff',
				id: 'lstLpuSectionTariffMes_Tariff',				
				fieldLabel: lang['tarif'] + getMESAlias(),
				disabled: true
				//maskRe: /[0-9]/
			},
			{
				allowBlank: false,
				disabled: false,
				fieldLabel: lang['tip_tarifa'],
				comboSubject: 'TariffMesType',
				sortField: 'TariffMesType_id',
				maxLength: 100,										
				tabIndex: 1224,
				width: 200,
				value: 1, //default value
				xtype: 'swcustomobjectcombo'
			},	
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				style: 'padding: 2; padding-left: 5px',
				items: [
				{
					fieldLabel : lang['nachalo'],
					tabIndex: 1324,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionTariffMes_setDate',
					id: 'lstmefLpuSectionTariffMes_setDate'
				},
				{
					fieldLabel : lang['okonchanie'],
					tabIndex: 1325,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionTariffMes_disDate',
					id: 'lstmefLpuSectionTariffMes_disDate'
				}]
			}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
				//alert('success');
				}
			},
			[
				{ name: 'LpuSectionTariffMes_id' },
				{ name: 'LpuSection_id' },
				{ name: 'Mes_id' },
				{ name: 'TariffMesType_id' },
				{ name: 'LpuSectionTariffMes_Tariff' },
				{ name: 'LpuSectionTariffMes_setDate' },
				{ name: 'LpuSectionTariffMes_disDate' }
			]
			),
			url: C_LPUSECTIONTARIFFMES_SAVE
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swLpuSectionTariffMesEditForm.superclass.initComponent.apply(this, arguments);
	}
});