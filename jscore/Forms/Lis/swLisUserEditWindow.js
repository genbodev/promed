/**
 * swLisUserEditWindow - окно редактирования пользователя ЛИС для определенной службы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       gabdushev
 * @version      06.2012
 * @comment
 */
sw.Promed.swLisUserEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['polzovatel_lis-sistemyi'],
	layout: 'form',
	id: 'swLisUserEditWindow',
	modal: true,
	shim: false,
	autoHeight: true,
	width: 500,
	resizable: false,
	maximizable: false,
	maximized: false,
	plain: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					that.panel.getFirstInvalidEl().focus(true);
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
		that.getLoadMask("Подождите, идет сохранение...").show();
		var params = {};
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				that.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				that.getLoadMask().hide();
				if (that.callback)
					that.callback(that.owner);
				that.hide();
			}
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swLisUserEditWindow.superclass.show.apply(this, arguments);
		that.action = '';
		that.callback = Ext.emptyFn;
		that.User_id = null;
		if ( arguments[0].action ) {
			that.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			that.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			that.owner = arguments[0].owner;
		}
		if ( arguments[0].User_id ) {
			that.User_id = arguments[0].User_id;
		}
		if ( arguments[0].MedService_id ) {
			that.MedService_id = arguments[0].MedService_id;
		}
		
		that.form.reset();
		
		that.form.findField('User_ClientId').setContainerVisible(isSuperAdmin());
		that.syncSize();

		that.getLoadMask().show();
		that.form.load({
			params: {
				post: true
			},
			failure: function()  {
				that.form.findField('MedService_id').setValue( that.MedService_id );
				that.getLoadMask().hide();
			},
			success: function() 
			{
				that.form.findField('User_Login').focus(true, 50);
				that.form.findField('MedService_id').setValue( that.MedService_id );
				that.getLoadMask().hide();
			},
			url: '/?c=LisUser&m=get'
		});
		return true;
	},
	initComponent: function() {
		this.panel = new sw.Promed.FormPanel({
			id: 'LisUserEditForm',
			autoHeight: true,
			border: false,
			bodyStyle: 'padding: 5px',
			frame: true,
			labelAlign: 'right',
			items: [{
				name: 'User_id',
				xtype: 'hidden',
				value: null
			}, {
				name: 'MedService_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['login'],
				name: 'User_Login',
				allowBlank:false,
				xtype: 'textfield',
				width: 350
			}, {
				fieldLabel: lang['parol'],
				name: 'User_Password',
				allowBlank:false,
				xtype: 'textfield',
				width: 350
			}, {
				fieldLabel: lang['id_klienta'],
				name: 'User_ClientId',
				allowBlank:true,
				xtype: 'textarea',
				width: 350
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [{name: 'User_id'},
				{name: 'MedService_id'},
				{name: 'User_ClientId'},
				{name: 'User_Login'},
				{name: 'User_Password'}
			]),
			url:'/?c=LisUser&m=save'
		});
		Ext.apply(this, {
			buttons:[{
				handler: function()
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
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
			items:[this.panel]
		});
		sw.Promed.swLisUserEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.panel.getForm();
	}
});