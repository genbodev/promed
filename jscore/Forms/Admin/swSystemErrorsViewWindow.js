/**
 * swSystemErrorsViewWindow - окно просмотра ошибки
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Dmitriy Vlasenko
 */

/*NO PARSE JSON*/

sw.Promed.swSystemErrorsViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSystemErrorsViewWindow',
	width: 730,
	autoHeight: true,
	modal: true,
	title: lang['oshibka_prosmotr'],
	action: 'view',
	callback: Ext.emptyFn,
	show: function() {
		sw.Promed.swSystemErrorsViewWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = win.FormPanel.getForm();
		base_form.reset();

		this.SystemError_id = null;
		if (arguments[0] && arguments[0].SystemError_id) {
			this.SystemError_id = arguments[0].SystemError_id;
		}

		win.getLoadMask(LOAD_WAIT).show();
		base_form.load({
			failure:function () {
				win.getLoadMask().hide();
			},
			url: '/?c=Common&m=loadSystemErrorsViewWindow',
			params: {
				SystemError_id: win.SystemError_id
			},
			success: function() {
				win.getLoadMask().hide();

				win.findById('SSEVW_SystemError_OpenUrlLabel').setText(base_form.findField('SystemError_OpenUrl').getValue(), false);
			}
		});
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 90,
			items: [{
				name: 'SystemError_id',
				xtype: 'hidden'
			}, {
				readOnly: true,
				name: 'SystemError_Code',
				fieldLabel: lang['nomer_oshibki'],
				xtype: 'textfield',
				width: 200
			}, {
				readOnly: true,
				name: 'SystemError_Error',
				fieldLabel: lang['oshibka'],
				xtype: 'textarea',
				height: 180,
				width: 600
			}, {
				readOnly: true,
				name: 'SystemError_Login',
				fieldLabel: lang['login'],
				xtype: 'textfield',
				width: 200
			}, {
				readOnly: true,
				name: 'SystemError_Date',
				fieldLabel: lang['data'],
				xtype: 'textfield',
				width: 200
			}, {
				readOnly: true,
				name: 'SystemError_Window',
				fieldLabel: lang['forma'],
				xtype: 'textfield',
				width: 600
			}, {
				readOnly: true,
				name: 'SystemError_Url',
				fieldLabel: lang['adres'],
				xtype: 'textfield',
				width: 600
			}, {
				readOnly: true,
				name: 'SystemError_Params',
				fieldLabel: lang['parametryi'],
				xtype: 'textfield',
				width: 600
			}, {
				readOnly: true,
				name: 'SystemError_Count',
				fieldLabel: lang['kolichestvo'],
				xtype: 'textfield',
				width: 200
			}, {
				name: 'SystemError_OpenUrl',
				xtype: 'hidden'
			}, {
				readOnly: true,
				id: 'SSEVW_SystemError_OpenUrlLabel',
				html: lang['ssyilka'],
				style: 'padding: 5px 20px; font-size: 15px;',
				xtype: 'label',
				width: 200
			}, {
				readOnly: true,
				name: 'SystemError_Fixed',
				fieldLabel: lang['ispravleno'],
				xtype: 'textfield',
				width: 200
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'SystemError_id'},
				{name: 'SystemError_Code'},
				{name: 'SystemError_Error'},
				{name: 'SystemError_Login'},
				{name: 'SystemError_Date'},
				{name: 'SystemError_Window'},
				{name: 'SystemError_Url'},
				{name: 'SystemError_Params'},
				{name: 'SystemError_Count'},
				{name: 'SystemError_OpenUrl'},
				{name: 'SystemError_Fixed'}
			])
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});

		sw.Promed.swSystemErrorsViewWindow.superclass.initComponent.apply(this, arguments);
	}
});