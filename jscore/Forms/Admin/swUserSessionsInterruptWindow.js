/**
 * swUserSessionsInterruptWindow - окно для отправки комманды о завершения сессии пользователей промеда
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			27.01.2014
 */

sw.Promed.swUserSessionsInterruptWindow = Ext.extend(sw.Promed.BaseForm,
{
	autoHeight: true,
	draggable: true,
	width: 600,
	layout: 'form',
	id: 'swUserSessionsInterruptWindow',
	modal: true,
	onHide: Ext.emptyFn,
	resizable: false,
	title: lang['prervat_seans'],

	interruptSessions: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {
			Session_ids: Ext.util.JSON.encode(this.Session_ids),
			emitUserID: ( sw.Promed.socket.id ) ? sw.Promed.socket.id : 0,
			DelayMinutes: base_form.findField('DelayMinutes').getValue(),
			Message: base_form.findField('Message').getValue()
		};

		win.getLoadMask('Отправка запроса').show();
		Ext.Ajax.request({
			url: '/?c=User&m=interruptUserSessions',
			params: params,
			callback: function (options, success, response) {
				var msg = 'Error sending request';
				win.getLoadMask().hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if( response_obj.Error_Msg ){
						msg = response_obj.Error_Msg;
					}else if (response_obj.success) {
						msg = ( response_obj.success == 'ERROR' ) ? 'ERROR onPostLogoutError' : 'Команда на прерывание сессий отправлена';
					}else{
						msg = 'Users is not found';
					}
				}
				win.callback(msg);
				win.hide();
			}
		});
		return true;
	},

	show: function()
	{
		sw.Promed.swUserSessionsInterruptWindow.superclass.show.apply(this, arguments);

		this.Session_ids = [];
		this.callback = Ext.emptyFn;

		if (!getGlobalOptions().NodeJSControl || !getGlobalOptions().NodeJSControl.enable) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['funktsiya_upravleniya_sessiyami_polzovateley_otklyuchena'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}.createDelegate(this)
			});
			return false;
		}

		if (!arguments[0])
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}.createDelegate(this)
			});
		}
		if (arguments[0].Session_ids.length == 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['nujno_ukazat_sessii_dlya_preryivaniya_seansa'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}.createDelegate(this)
			});
		}
		this.Session_ids = arguments[0].Session_ids;

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
	},

	initComponent: function()
	{
		this.FormPanel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
				allowBlank: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'DelayMinutes',
				fieldLabel: lang['zaderjka_min']
			}, {
				xtype: 'textarea',
				name: 'Message',
				fieldLabel: lang['soobschenie'],
				anchor: '99%'
			}]
		});

		Ext.apply(this,
		{
			buttons:
			[{
				handler: function()
				{
					this.interruptSessions();
				}.createDelegate(this),
				text: lang['vyipolnit']
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_LPEEW + 17,
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swUserSessionsInterruptWindow.superclass.initComponent.apply(this, arguments);
	}
});