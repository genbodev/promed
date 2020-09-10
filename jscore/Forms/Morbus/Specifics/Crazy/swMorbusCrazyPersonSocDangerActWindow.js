/**
* swMorbusCrazyPersonSocDangerActWindow - окно редактирования "Общественно-опасные действия"
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

sw.Promed.swMorbusCrazyPersonSocDangerActWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['obschestvenno-opasnyie_deystviya'],
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
					that.findById('swMorbusCrazyPersonSocDangerActEditForm').getFirstInvalidEl().focus(true);
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
				that.callback(that.owner, action.result.MorbusCrazyPersonSocDangerAct_id);
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
		sw.Promed.swMorbusCrazyPersonSocDangerActWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusCrazyPersonSocDangerAct_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].MorbusCrazyPersonSocDangerAct_id ) {
			this.MorbusCrazyPersonSocDangerAct_id = arguments[0].MorbusCrazyPersonSocDangerAct_id;
		}

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
		
        this.getLoadMask().show();
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				that.form.findField('MorbusCrazyPersonSocDangerAct_setDT').focus(true,200);

				that.getLoadMask().hide();
			break;
			case 'edit':
			case 'view':
				var person_id = arguments[0].formParams.Person_id;
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						that.getLoadMask().hide();
					},
					params:{
						MorbusCrazyPersonSocDangerAct_id: that.MorbusCrazyPersonSocDangerAct_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						that.form.findField('MorbusCrazyPersonSocDangerAct_setDT').focus(true,200);
					},
					url:'/?c=MorbusCrazy&m=loadMorbusCrazyPersonSocDangerAct'
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
			id: 'swMorbusCrazyPersonSocDangerActEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusCrazyPersonSocDangerAct_id', xtype: 'hidden', value: null},
				{name: 'MorbusCrazyPerson_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['data_soversheniya'],
					name: 'MorbusCrazyPersonSocDangerAct_setDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['statya_uk'],
					anchor:'100%',
					name: 'MorbusCrazyPersonSocDangerAct_Article',
					xtype: 'textfield',
					allowBlank: false
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusCrazyPersonSocDangerAct_id'},
				{name: 'MorbusCrazyPerson_id'},
				{name: 'MorbusCrazyPersonSocDangerAct_setDT'},
				{name: 'MorbusCrazyPersonSocDangerAct_Article'}
			]),
			url: '/?c=MorbusCrazy&m=saveMorbusCrazyPersonSocDangerAct'
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
		sw.Promed.swMorbusCrazyPersonSocDangerActWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusCrazyPersonSocDangerActEditForm').getForm();
	}	
});