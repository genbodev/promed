/**
 * swEvnPLDispTeenExportWindow - окно экспорта карт по диспансеризации несовершеннолетних
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Polka
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.12.2013
 */

/*NO PARSE JSON*/

sw.Promed.swEvnPLDispTeenExportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnPLDispTeenExportWindow',
	width: 620,
	height: 250,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	title: lang['eksport_kart_po_dispanserizatsii_nesovershennoletnih'],

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
		params.Lpu_id = base_form.findField('Lpu_id').getValue();

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
						wnd.TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="'+answer.Link+'">Скачать и сохранить список</a>';
						//Ext.getCmp('EPLDTE_ExportButton').disable();
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
			timeout: 3600000,
			url: '/?c=EvnPLDisp&m=exportEvnPLDispToXml'
		});
	},

	show: function()
	{
		sw.Promed.swEvnPLDispTeenExportWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		wnd.formMode = 'iddle';

		base_form.reset();
		base_form.clearInvalid();

		if (!getGlobalOptions().lpu_id) {
			wnd.hide();
		}

		var lpu_id = getGlobalOptions().lpu_id;
		base_form.findField('Lpu_id').setValue(lpu_id);

		wnd.TextPanel.getEl().dom.innerHTML = '';
		wnd.TextPanel.render();
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'EPLDTE_XmlTextPanel',
			html: ''
		});

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			id: 'EPLDTE_FormPanel',
			labelAlign: 'right',
			labelWidth: 200,
			region: 'center',
			url: '/?c=EvnPLDisp&m=exportEvnPLDispToXml',
			items: [{
				anchor: '95%',
				fieldLabel: langs('МО'),
				hiddenName: 'Lpu_id',
				listWidth: 600,
				xtype: 'swlpucombo',
				hideTrigger: true,
				disabled: true
			}, {
				allowBlank: false,
				fieldLabel: lang['data_okonchaniya_kartyi'],
				name: 'EvnPLDisp_disDate_Range',
				plugins: [
					new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
				],
				width: 170,
				xtype: 'daterangefield'
			}, {
				fieldLabel: lang['sluchay_oplachen'],
				hiddenName: 'EvnPLDisp_IsPaid',
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: false,
				anchor: '95%',
				comboSubject: 'DispClass',
				codeField: 'DispClass_Code',
				fieldLabel: lang['tip_obsledovaniya'],
				lastQuery:'',
				orderBy: 'Code',
				typeCode: 'int',
				xtype: 'swcommonsprcombo',
				style: 'margin-bottom: 0.5em;',
				onLoadStore: function() {
					this.getStore().filterBy(function(rec) {
						return (rec.get('DispClass_Code').inlist([3,6,7,9,10]));
					});
				}
			}, {
				fieldLabel: 'Проверить файл по XSD-схеме',
				name: 'checkByXSD',
				xtype: 'checkbox'
			}, wnd.TextPanel
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
					id: 'EPLDTE_ExportButton',
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
					id: 'EPLDTE_CancelButton',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swEvnPLDispTeenExportWindow.superclass.initComponent.apply(this, arguments);
	}
});
