/**
 * swHospDataImportForTfomsWindow - окно импорта данных из ТФОМС и СМО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			06.06.2014
 */

/*NO PARSE JSON*/

sw.Promed.swHospDataImportFromTfomsWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swHospDataImportFromTfomsWindow',
	width: 440,
	height: 140,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	title: lang['import_dannyih_iz_tfoms_i_smo'],

	doSave: function() {
		/*if ( this.formMode == 'import' ) {
			return false;
		}

		this.formMode = 'import';*/

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

		wnd.getLoadMask("Подождите, выполняется импорт...").show();

		base_form.submit({
			timeout: 0,
			failure: function(result_form, action) {
				wnd.formMode = 'iddle';
				wnd.getLoadMask().hide();

				if ( action.response.responseText ) {
					var answer = Ext.util.JSON.decode(action.response.responseText);
					if (answer.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_importe_fayla']);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			success: function(result_form, action) {
				wnd.formMode = 'iddle';
				wnd.getLoadMask().hide();

				if ( action.response.responseText ) {
					var answer = Ext.util.JSON.decode(action.response.responseText);

					var tpl = new Ext.Template(
						'Загружено {recall} объектов, из них идентифицировано {recident}, обновлено {recupdate}.',
						'<br/><a target="_blank" href="{log_link}">Сохранить файл лога</a>'
					);

					sw.swMsg.alert(lang['dannyie_zagrujenyi'], tpl.apply(answer));
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			}
		});
	},

	show: function()
	{
		sw.Promed.swHospDataImportFromTfomsWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		this.ARMType = null;
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		wnd.formMode = 'iddle';

		base_form.reset();
		base_form.clearInvalid();

		/*wnd.TextPanel.getEl().dom.innerHTML = '';
		wnd.TextPanel.render();*/
	},

	initComponent: function()
	{
		var wnd = this;

		/*wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'HDIFTW_XmlTextPanel',
			html: ''
		});*/

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			fileUpload: true,
			frame: true,
			id: 'HDIFTW_FormPanel',
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			items: [{
				xtype: 'fileuploadfield',
				anchor: '95%',
				emptyText: lang['vyiberite_fayl'],
				fieldLabel: lang['fayl'],
				name: 'ImportFile'
			}
			//,wnd.TextPanel
			],
			url: '/?c=EvnPS&m=importHospDataFromTfomsXml'
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function () {
						wnd.doSave();
					}.createDelegate(this),
					iconCls: 'refresh16',
					id: 'HDIFTW_ImportButton',
					text: lang['obnovit_dannyie']
				},{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'HDIFTW_CancelButton',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swHospDataImportFromTfomsWindow.superclass.initComponent.apply(this, arguments);
	}
});
