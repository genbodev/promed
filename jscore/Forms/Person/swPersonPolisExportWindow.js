/**
 * swPersonPolisExportWindow - окно выгрузки реестров неработающих застрахованныз лиц
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Person
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.01.2014
 */

/*NO PARSE JSON*/

sw.Promed.swPersonPolisExportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonPolisExportWindow',
	width: 550,
	autoHeight: true,
	callback: Ext.emptyFn,
	layout: 'form',
	modal: true,
	title: lang['reestryi_nerabotayuschih_zastrahovannyih_lits'],

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
						wnd.FormPanel.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return;
		}

		var params = base_form.getValues();

		if ( base_form.findField('AttachLpu_id').disabled ) {
			params.AttachLpu_id = base_form.findField('AttachLpu_id').getValue();
		}

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите, идет формирование данных..." });
		loadMask.show();

		Ext.Ajax.request({
			failure: function(response, options) {
				wnd.formMode = 'iddle';
				loadMask.hide();
			},
			params: params,
			timeout: 7200000, // 2 часа
			success: function(response, action) {
				wnd.formMode = 'iddle';
				loadMask.hide();

				if ( response.responseText ) {
					var answer = Ext.util.JSON.decode(response.responseText);

					if ( answer.success ) {
						var html = lang['vyigrujeno_zapisey']+answer.Count+'<br/>';
						html += '<a target="_blank" href="'+answer.Link+'">Скачать и сохранить файл</a>';
						wnd.TextPanel.getEl().dom.innerHTML = html;
						wnd.TextPanel.render();
						wnd.syncSize();
						wnd.syncShadow();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], !Ext.isEmpty(answer.Error_Msg) ? answer.Error_Msg : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
 			url: '/?c=Person&m=exportPersonPolisToXml'
		});
	},

	show: function()
	{
		sw.Promed.swPersonPolisExportWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		base_form.reset();

		base_form.findField('AttachLpu_id').setAllowBlank(!getRegionNick().inlist([ 'buryatiya' ]));
		base_form.findField('AttachLpu_id').setContainerVisible(getRegionNick().inlist([ 'buryatiya' ]));

		if ( getRegionNick().inlist([ 'buryatiya' ]) && (!isSuperAdmin() || (typeof arguments == 'object' && arguments.length > 0 && !Ext.isEmpty(arguments[0]['AttachLpu_id']))) ) {
			base_form.findField('AttachLpu_id').disable();
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		}
		else {
			base_form.findField('AttachLpu_id').enable();
			base_form.findField('AttachLpu_id').clearValue();
		}

		wnd.formMode = 'iddle';

		wnd.TextPanel.getEl().dom.innerHTML = '';
		wnd.TextPanel.render();

		wnd.syncSize();
		wnd.syncShadow();
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'PPEW_XmlTextPanel',
			html: ''
		});

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			id: 'PPEW_FormPanel',
			labelAlign: 'right',
			labelWidth: 180,
			region: 'center',
			items: [{
				fieldLabel: lang['mo'],
				hiddenName: 'AttachLpu_id',
				width: 300,
				xtype: 'swlpucombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['poryadkovyiy_nomer_vyigruzki'],
				plugins: [ new Ext.ux.InputTextMask('99', false) ],
				name: 'PersonPolis_ExportIndex',
				width: 120,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['vyigruzka_na_datu'],
				name: 'PersonPolis_Date',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 120,
				xtype: 'swdatefield'
			},
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
					id: 'PPEW_ExportButton',
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
					id: 'PPEW_CancelButton',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swPersonPolisExportWindow.superclass.initComponent.apply(this, arguments);
	}
});
