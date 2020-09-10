/**
* swLpuSectionPlanEditForm - окно просмотра и редактирования планирования
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

sw.Promed.swLpuSectionPlanEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['planirovanie'],
	id: 'LpuSectionPlanEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 500,
	height: 220,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lspefOk',
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
		id: 'lspefCancel',
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
		var form = this.findById('LpuSectionPlanEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionPlanEditForm'), { msg: "Подождите, идет проверка..." });
		loadMask.show();
		var params = {
			LpuSectionPlan_id: form.findById('lspefLpuSectionPlan_id').getValue(), 
			LpuSection_id: form.findById('lspefLpuSection_id').getValue(), 
			LpuSectionPlan_setDate: Ext.util.Format.date(form.findById('lspefLpuSectionPlan_setDate').getValue(),'d.m.Y')
		}
		Ext.Ajax.request( {
			url: C_LPUSECTIONPLAN_CHECK,
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
									form.findById('lspefLpuSectionPlan_setDate').focus(false)
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
		var form = this.findById('LpuSectionPlanEditFormPanel');
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
		var begDate = form.findById('lspefLpuSectionPlan_setDate').getValue();
		var endDate = form.findById('lspefLpuSectionPlan_disDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate)) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.findById('lspefLpuSectionPlan_setDate').focus(false)
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
		sw.Promed.swLpuSectionPlanEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionPlanEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuSectionPlan_id)
			this.LpuSectionPlan_id = arguments[0].LpuSectionPlan_id;
		else 
			this.LpuSectionPlan_id = null;
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
		form.findById('LpuSectionPlanEditFormPanel').getForm().reset();

		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['planirovanie_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['planirovanie_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['planirovanie_prosmotr']);
				break;
		}
		
		if (this.action=='view') {
			form.findById('lspefLpuSectionPlan_setDate').disable();
			form.findById('lspefLpuSectionPlan_disDate').disable();
			form.findById('LpuSectionPlanType_combo').disable();
			form.buttons[0].disable();
		} else {
			form.findById('lspefLpuSectionPlan_setDate').enable();
			form.findById('lspefLpuSectionPlan_disDate').enable();			
			form.findById('LpuSectionPlanType_combo').enable();
			form.buttons[0].enable();
		}
		
		form.findById('lspefLpuSection_id').setValue(this.LpuSection_id);
		form.findById('lspefLpuSection_Name').setValue(this.LpuSection_Name);
		if (this.action!='add') {			
			form.findById('LpuSectionPlanEditFormPanel').getForm().load({
				url: C_LPUSECTIONPLAN_GET,
				params: {
					object: 'LpuSectionPlan',
					LpuSectionPlan_id: this.LpuSectionPlan_id,
					LpuSectionPlanType_id: '',
					LpuSectionPlan_setDate: '',
					LpuSectionPlan_disDate: '',
					LpuSection_id: ''
				},
				success: function () {
					if (form.action!='view') {
							//
					}
					form.findById('LpuSectionPlanType_combo').focus(true, 100);
					loadMask.hide();
				},
				failure: function () {
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				}
			});			
		} else {		
			form.findById('LpuSectionPlanType_combo').focus(true, 100);
			loadMask.hide();
		}
	},
	submit: function() {
		var form = this.findById('LpuSectionPlanEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionPlanEditForm'), { msg: "Подождите, идет сохранение..." });
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
					if (action.result.LpuSectionPlan_id) {
						form.ownerCt.hide();
						form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuSectionPlan_id);
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
			id:'LpuSectionPlanEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			items:
			[
			{
				name: 'LpuSectionPlan_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lspefLpuSectionPlan_id'
			},
			{
				name: 'LpuSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lspefLpuSection_id'
			},
			{
				name: 'LpuSection_Name',
				disabled: true,
				fieldLabel: lang['otdelenie'],
				tabIndex: 1,
				xtype: 'descfield',
				id: 'lspefLpuSection_Name'
			},
			{
				allowBlank: true,	
				disabled: false,
				fieldLabel: lang['tip_tarifa'],
				comboSubject: 'LpuSectionPlanType',
				sortField: 'LpuSectionPlanType_id',
				maxLength: 100,										
				tabIndex: TABINDEX_EREF + 25,
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
					name: 'LpuSectionPlan_setDate',
					id: 'lspefLpuSectionPlan_setDate'
				},
				{
					fieldLabel : lang['okonchanie'],
					tabIndex: 1325,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionPlan_disDate',
					id: 'lspefLpuSectionPlan_disDate'
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
				{ name: 'LpuSectionPlan_id' },
				{ name: 'LpuSection_id' },
				{ name: 'LpuSectionPlanType_id' },
				{ name: 'LpuSectionPlan_setDate' },
				{ name: 'LpuSectionPlan_disDate' }
			]
			),
			url: C_LPUSECTIONPLAN_SAVE
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swLpuSectionPlanEditForm.superclass.initComponent.apply(this, arguments);
	}
});