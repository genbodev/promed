/**
* swMorbusTubPrescrTimetableWindow - окно редактирования "График исполнения назначения процедур"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A. Markoff
* @version      2012/11
* @comment      
*/

sw.Promed.swMorbusTubPrescrTimetableWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	modal: true,
	width: 670,
	titleWin: lang['grafik_ispolneniya_naznacheniya_protsedur'],
	autoHeight: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					that.findById('swMorbusTubPrescrTimetableEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = that.action;
		this.form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				that.callback(that.owner, action.result.MorbusTubPrescrTimetable_id);
				that.hide();
			}
		});
	},
	setFieldsDisabled: function(d)
	{
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() {
		var that = this;
		sw.Promed.swMorbusTubPrescrTimetableWindow.superclass.show.apply(this, arguments);
		if ( !arguments[0] || !arguments[0].Person_id) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		var person_id = arguments[0].Person_id;
		this.callback = Ext.emptyFn;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.action = arguments[0].action || '';
		this.ARMType = arguments[0].ARMType || null;
		this.owner = arguments[0].owner || null;
		this.MorbusTubPrescrTimetable_id = arguments[0].MorbusTubPrescrTimetable_id || null;

		this.form.reset();
		
		switch (arguments[0].action) {
			case 'add':
				this.setTitle(this.titleWin+lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.titleWin+lang['_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.titleWin+lang['_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		that.form.setValues(arguments[0]);
		
		this.getLoadMask().show();
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: person_id
				});
				that.form.findField('MedPersonal_id').getStore().load({
					callback: function() {
						that.form.findField('MedPersonal_id').setValue(that.form.findField('MedPersonal_id').getValue());
						that.form.findField('MedPersonal_id').fireEvent('change', that.form.findField('MedPersonal_id'), that.form.findField('MedPersonal_id').getValue());
					}.createDelegate(this)
				});
				that.form.findField('MorbusTubPrescrTimetable_setDT').focus(true,200);
				that.getLoadMask().hide();
			break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						that.getLoadMask().hide();
					},
					params:{
						MorbusTubPrescrTimetable_id: that.MorbusTubPrescrTimetable_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						that.form.findField('MedPersonal_id').getStore().load({
							callback: function() {
								that.form.findField('MedPersonal_id').setValue(that.form.findField('MedPersonal_id').getValue());
								that.form.findField('MedPersonal_id').fireEvent('change', that.form.findField('MedPersonal_id'), that.form.findField('MedPersonal_id').getValue());
							}.createDelegate(this)
						});
						that.form.findField('MorbusTubPrescrTimetable_setDT').focus(true,200);
					},
					url:'/?c=MorbusTub&m=loadMorbusTubPrescrTimetable'
				});
			break;
		}
	},
	initComponent: function() {
		
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'swMorbusTubPrescrTimetableEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusTubPrescrTimetable_id', xtype: 'hidden', value: null},
				{name: 'MorbusTubPrescr_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['data'],
					name: 'MorbusTubPrescrTimetable_setDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['otmetka_o_vyipolnenii'],
					width: 70,
					hiddenName: 'MorbusTubPrescrTimetable_IsExec',
					xtype: 'swyesnocombo',
					allowBlank: true
				}, {
					fieldLabel: lang['vrach_vyipolnivshiy_naznachenie'],
					hiddenName: 'MedPersonal_id',
					anchor: '100%',
					xtype: 'swmedpersonalcombo',
					allowBlank: false
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusTubPrescrTimetable_id'},
				{name: 'MorbusTubPrescr_id'},
				{name: 'MorbusTubPrescrTimetable_setDT'},
				{name: 'MorbusTubPrescrTimetable_IsExec'},
				{name: 'MedPersonal_id'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubPrescrTimetable'
		});
		Ext.apply(this, {
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
			items:[this.InformationPanel,form]
		});
		sw.Promed.swMorbusTubPrescrTimetableWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusTubPrescrTimetableEditForm').getForm();
	}	
});