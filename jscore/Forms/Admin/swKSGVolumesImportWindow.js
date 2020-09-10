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

sw.Promed.swKSGVolumesImportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swKSGVolumesImportWindow',
	width: 440,
	autoHeight: true,
	callback: Ext.emptyFn,
	layout: 'form',
	modal: true,
	title: 'Загрузка объёмов КСГ',

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
						'Объёмы КСГ успешно обновлены.',
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
		sw.Promed.swKSGVolumesImportWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		this.ARMType = null;
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		wnd.formMode = 'iddle';

		base_form.reset();
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			autoHeight: true,
			border: false,
			fileUpload: true,
			frame: true,
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			items: [{
				fieldLabel: 'Дата начала',
				name: 'StartDate',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: 'Тип КСГ',
				allowBlank: false,
				hiddenName: 'MesType_id',
				comboSubject: 'MesType',
				width: 200,
				loadParams: {params: {where: ' where MesType_id in (9, 10, 13, 14)'}},
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: 'Примечание',
				width: 200,
				name: 'Descr',
				xtype: 'textfield'
			}, {
				xtype: 'fileuploadfield',
				anchor: '95%',
				emptyText: lang['vyiberite_fayl'],
				fieldLabel: 'Выбрать файл',
				name: 'ImportFile'
			}],
			url: '/?c=TariffVolumes&m=importKSGVolumes'
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
					text: 'Загрузить данные'
				},{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swKSGVolumesImportWindow.superclass.initComponent.apply(this, arguments);
	}
});
