/**
* swMorbusHepatitisQueueWindow - Очередь.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      24.05.2012
*/

sw.Promed.swMorbusHepatitisQueueWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
	width: 500,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		params.MorbusHepatitisQueue_IsCure = base_form.findField('MorbusHepatitisQueue_IsCure').getValue();
		
		base_form.submit({
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
				win.callback();
				win.hide();
			}
		});
		
	},
    getQueueNumber: function() {
   		if ( this.action == 'view' || this.FormPanel.getForm().findField('HepatitisQueueType_id').getValue() == 0 || this.FormPanel.getForm().findField('MorbusHepatitisQueue_Num').disabled  ) {
   			return false;
   		}
   		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение номера в очереди..."});
   		loadMask.show();
   		Ext.Ajax.request({
			params: {HepatitisQueueType_id: this.FormPanel.getForm().findField('HepatitisQueueType_id').getValue()},
   			callback: function(options, success, response) {
   				if ( success ) {
   					var response_obj = Ext.util.JSON.decode(response.responseText);
                    var field = this.FormPanel.getForm().findField('MorbusHepatitisQueue_Num');
                    field.setValue(response_obj[0].MorbusHepatitisQueue_Num);
                    field.focus(true);
   				}
   				else {
   					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_v_ocheredi']);
   				}
   				loadMask.hide();
   			}.createDelegate(this),
   			url: '/?c=MorbusHepatitisQueue&m=getQueueNumber'
   		});
   	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swMorbusHepatitisQueueWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].MorbusHepatitisQueue_id) 
			this.MorbusHepatitisQueue_id = arguments[0].MorbusHepatitisQueue_id;
		else 
			this.MorbusHepatitisQueue_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.MorbusHepatitisQueue_id ) && ( this.MorbusHepatitisQueue_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
				
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['ochered_dobavlenie']);
				this.setFieldsDisabled(false);
				base_form.findField('MorbusHepatitisQueue_IsCure').setDisabled(true);
				break;
			case 'edit':
				this.setTitle(lang['ochered_redaktirovanie']);
				this.setFieldsDisabled(false);
				base_form.findField('MorbusHepatitisQueue_IsCure').setDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['ochered_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
		var queue_num = base_form.findField('MorbusHepatitisQueue_Num');
		var is_cure = base_form.findField('MorbusHepatitisQueue_IsCure');

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					MorbusHepatitisQueue_id: this.MorbusHepatitisQueue_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					
					base_form.setValues(result[0]);
					queue_num.fireEvent('change', queue_num, queue_num.getValue());
					is_cure.fireEvent('change', is_cure, is_cure.getValue());
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=MorbusHepatitisQueue&m=load'
			});			
		} else {
			queue_num.setDisabled(false);	
			queue_num.setAllowBlank(false);
			loadMask.hide();			
		}
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 150,
			url:'/?c=MorbusHepatitisQueue&m=save',
			items: 
			[{
				name: 'MorbusHepatitisQueue_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusHepatitis_id',
				xtype: 'hidden'
			}, {
				name: 'HepatitisQueueType_id',
                comboSubject: 'HepatitisQueueType',
                fieldLabel: lang['tip_ocheredi'],
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				value: null,
				width: 300,
				listeners: {
					'change': function(field, newValue, oldValue) {
						this.getQueueNumber();
					}.createDelegate(this)
				}
			}, {
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					allowBlank: false,
					fieldLabel: lang['nomer_v_ocheredi'],
					name: 'MorbusHepatitisQueue_Num',
					autoCreate: {tag: "input", autocomplete: "off"},
					maskRe: /[0-9]/,
					triggerClass: 'x-form-plus-trigger',
					onTriggerClick: function() {
						this.getQueueNumber();
					}.createDelegate(this),
					width: 100,
					xtype: 'trigger'
				}]
			}, {
				value: 1,
				disabled: true,
				allowBlank: false,
				changeDisabled: false,
				width: 100,
				fieldLabel: lang['lechenie_provedeno'],
				hiddenName: 'MorbusHepatitisQueue_IsCure',
				xtype: 'swyesnocombo',
				listeners: {
					'change': function (combo, oldValue, newValue) {
						
						var base_form = this.FormPanel.getForm();
						var queue_num = base_form.findField('MorbusHepatitisQueue_Num');
						if ( base_form.findField('MorbusHepatitisQueue_IsCure').getValue() == 2 ) {
							queue_num.setValue(null);
							queue_num.setDisabled(true);
							queue_num.setAllowBlank(true);
						} else {
							queue_num.setDisabled(false);	
							queue_num.setAllowBlank(false);					
						}
					}.createDelegate(this)
				}
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusHepatitisQueue_id'},
				{name: 'MorbusHepatitis_id'},
				{name: 'HepatitisQueueType_id'},
				{name: 'MorbusHepatitisQueue_Num'},
				{name: 'MorbusHepatitisQueue_IsCure'}
			])
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusHepatitisQueueWindow.superclass.initComponent.apply(this, arguments);
	}
});