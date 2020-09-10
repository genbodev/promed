/**
 * swMorbusOnkoDataExportWindow - окно выгрузки регистра онкобольных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			24.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swMorbusOnkoDataExportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMorbusOnkoDataExportWindow',
	width: 380,
	height: 100,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	title: lang['eksport_registra_onkobolnyih'],

	createXML: function() {
		if ( this.formMode == 'export' ) {
			return false;
		}

		this.formMode = 'export';

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if (!base_form.isValid())
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						wnd.formMode = 'iddle';
						wnd.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return;
		}

		var params = base_form.getValues();
		params.ARMType = this.ARMType;

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите, идет формирование данных..." });
		loadMask.show();

		Ext.Ajax.request({
			failure: function(response, options) {
				wnd.formMode = 'iddle';
				loadMask.hide();
			},
			params: params,
			success: function(response, action) {
				wnd.formMode = 'iddle';
				loadMask.hide();
				if ( response.responseText ) {
					var answer = Ext.util.JSON.decode(response.responseText);

					if ( answer.success ) {
						wnd.TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="'+answer.Link+'">Скачать и сохранить файл</a>';
						wnd.TextPanel.render();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], !Ext.isEmpty(answer.Error_Msg) ? answer.Error_Msg : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			url: '/?c=MorbusOnkoSpecifics&m=exportMorbusOnkoData'
		});
	},

	show: function()
	{
		sw.Promed.swMorbusOnkoDataExportWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		this.ARMType = null;
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		wnd.formMode = 'iddle';

		base_form.reset();
		base_form.clearInvalid();

		wnd.TextPanel.getEl().dom.innerHTML = '<p>Нажмите на кнопку "Сформировать"';
		wnd.TextPanel.render();
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'MODEW_XmlTextPanel',
			html: ''
		});

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			id: 'MODEW_FormPanel',
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			items: [/*{
				allowBlank: false,
				fieldLabel: lang['data'],
				name: 'Date',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 120,
				xtype: 'swdatefield'
			},*/
				wnd.TextPanel
			]
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function () {
						wnd.createXML();
					}.createDelegate(this),
					iconCls: 'refresh16',
					id: 'MODEW_ExportButton',
					text: lang['sformirovat']
				},{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'MODEW_CancelButton',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swMorbusOnkoDataExportWindow.superclass.initComponent.apply(this, arguments);
	}
});
