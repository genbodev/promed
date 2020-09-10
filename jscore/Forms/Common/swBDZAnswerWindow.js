/**
* swBDZAnswerWindow - окно ответа сервиса БДЗ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Kurakin A.
* @version      12.2016
* @comment      
*/
sw.Promed.swBDZAnswerWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Данные о прикреплении',
	layout: 'border',
	id: 'BDZAnswerWindow',
	modal: true,
	shim: false,
	width: 800,
	height: 450,
	resizable: false,
	maximizable: true,
	maximized: false,
	doSave: function() {
		var base_form = this.FilterPanel.getForm();
		if (Ext.isEmpty(base_form.findField('OrgSMO_id'))) {
			return;
		}
		var values = base_form.getValues();
		var data = {
			OMSSprTerr_id: values.OMSSprTerr_id,
			PolisType_id: values.PolisType_id,
			PolisFormType_id: values.PolisFormType_id,
			Polis_Ser: values.Polis_Ser,
			Polis_Num: values.Polis_Num,
			Federal_Num: values.Federal_Num,
			OrgSMO_id: values.OrgSMO_id,
			Polis_begDate: values.Polis_begDate,
			Polis_endDate: values.Polis_endDate,
			Person_identDT: values.Person_identDT,
			PersonIdentState_id: values.PersonIdentState_id,
			BDZ_id: values.BDZ_id
		};
		this.callback(data);
		this.hide();
	},
	doReset: function() {
		var wnd = this;
		var base_form = wnd.FilterPanel.getForm();
		base_form.reset();
		wnd.ErrorGrid.removeAll();
		Ext.getCmp('DBZAW_SaveButton').disable();
	},	
	show: function() {
        var wnd = this;
		var base_form = wnd.FilterPanel.getForm();
		sw.Promed.swBDZAnswerWindow.superclass.show.apply(this, arguments);
		this.callback = Ext.emptyFn;
		this.doReset();
		if(arguments[0]) {
			if (arguments[0].callback) {
				this.callback = arguments[0].callback;
			}
            if(arguments[0].errors){
                wnd.ErrorGrid.getGrid().getStore().loadData(arguments[0].errors);
            }
			if(arguments[0].data){
				base_form.setValues(arguments[0].data);
				var algFlag = String(base_form.findField('PersonIdentAlgorithm_Code').getValue()).inlist(['C02','C03']);
				var hasErrors = (arguments[0].errors.length > 0);
				var hasFatalErrors = arguments[0].errors.some(function(error){
					return String(error.Error_Code).inlist(['200','201'])
				});
				if (
					!Ext.isEmpty(base_form.findField('SMO_Code').getValue())
					&& (!hasErrors || (!hasFatalErrors && algFlag && !Ext.isEmpty(base_form.findField('PolisType_id').getValue())))
				) {
					Ext.getCmp('DBZAW_SaveButton').enable();
				}
			}
        }
	},
	initComponent: function() {
		var wnd = this;

		this.FilterPanel = new Ext.FormPanel({
			layout: 'form',
			region: 'north',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			height:170,
			border: false,
			frame: true,
			items: [{
				xtype: 'hidden',
				name: 'OMSSprTerr_id'
			}, {
				xtype: 'hidden',
				name: 'PolisType_id'
			}, {
				xtype: 'hidden',
				name: 'PolisFormType_id'
			}, {
				xtype: 'hidden',
				name: 'Polis_Ser'
			}, {
				xtype: 'hidden',
				name: 'Polis_Num'
			}, {
				xtype: 'hidden',
				name: 'Federal_Num'
			}, {
				xtype: 'hidden',
				name: 'OrgSMO_id'
			}, {
				xtype: 'hidden',
				name: 'Polis_begDate'
			}, {
				xtype: 'hidden',
				name: 'Polis_endDate'
			}, {
				xtype: 'hidden',
				name: 'Sex_id'
			}, {
				xtype: 'hidden',
				name: 'PersonIdentState_id'
			}, {
				xtype: 'hidden',
				name: 'BDZ_id'
			}, {
				xtype: 'hidden',
				name: 'Person_identDT'
			}, {
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Код СМО',
						name: 'SMO_Code',
						readOnly: true,
						width:200
					}, {
						xtype: 'textfield',
						fieldLabel: 'Дата начала страхования',
						name: 'Insur_BegDate',
						readOnly: true,
						width:200
					}, {
						xtype: 'textfield',
						fieldLabel: 'Пол',
						name: 'Sex_Name',
						readOnly: true,
						width: 200
					}, {
						xtype: 'textfield',
						fieldLabel: 'Дата рождения',
						name: 'Person_BirthDay',
						readOnly: true,
						width: 200
					}, {
						xtype: 'textfield',
						fieldLabel: 'СНИЛС',
						name: 'Person_Snils',
						readOnly: true,
						width: 200
					}, {
						xtype: 'textfield',
						fieldLabel: 'Идентификатор ЗЛ в РСЕРЗ',
						name: 'Ident',
						readOnly: true,
						width:200
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Код МО',
						name: 'Lpu_Code',
						readOnly: true,
						width:200
					}, {
						xtype: 'textfield',
						fieldLabel: 'Краткое наименование МО',
						name: 'Lpu_Nick',
						readOnly: true,
						width:200
					}, {
						xtype: 'textfield',
						fieldLabel: 'Код участка',
						name: 'Rgn_Code',
						readOnly: true,
						width:200
					}, {
						xtype: 'textfield',
						fieldLabel: 'Дата начала прикрепления',
						name: 'Attach_BegDate',
						readOnly: true,
						width:200
					}, {
						xtype: 'textfield',
						fieldLabel: 'Алгоритм идентификации',
						name: 'PersonIdentAlgorithm_Code',
						readOnly: true,
						width:200
					}]
				}]
			}]
		});

		this.ErrorGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add',hidden:true,disabled:true},
				{name: 'action_edit',hidden:true,disabled:true},
				{name: 'action_view',hidden:true,disabled:true},
				{name: 'action_delete',hidden:true,disabled:true},
				{name: 'action_print',hidden:true,disabled:true},
				{name: 'action_refresh',hidden:true,disabled:true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			height: 130,
			object: 'Empty',
			id: 'ErrorGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'Error_id', type: 'int', header: 'Код ошибки', key: true, width:80 },
				{ name: 'Error_Code', type: 'int', header: 'Код ошибки', width:120 },
				{ name: 'Error_Name', type: 'string', header: 'Описание', id: 'autoexpand' }
			],
			title: null,
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
				id: 'DBZAW_SaveButton',
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.ErrorGrid]
					}]
				}
			]
		});
		sw.Promed.swBDZAnswerWindow.superclass.initComponent.apply(this, arguments);
	}	
});