/**
 * Created by JetBrains PhpStorm.
 * User: IGabdushev
 * Date: 29.08.11
 * Time: 15:07
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swLpuStructureStaffExport2Dbf = Ext.extend(sw.Promed.BaseForm, { codeRefresh: true,

	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'RegistryDbfWindow',
	title: lang['formirovanie_dbf'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() {
		this.TextPanel = new Ext.Panel(
				{
					autoHeight: true,
					bodyBorder: false,
					border: false,
					id: 'RegistryDbfTextPanel',
					html: lang['vyigruzka_dannyih_reestra_v_formate_dbf']
				});

		this.Panel = new Ext.form.FormPanel(
				{
					autoHeight: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: true,
					id: 'RegistryDbfPanel',
					labelAlign: 'right',
					labelWidth: 100,
					items: [this.TextPanel]
					/*,
					 keys: [
					 {
					 alt: true,
					 fn: function(inp, e)
					 {
					 switch (e.getKey())
					 {
					 case Ext.EventObject.G:
					 this.print();
					 break;
					 case Ext.EventObject.J:
					 this.hide();
					 break;
					 }
					 },
					 key: [ Ext.EventObject.G, Ext.EventObject.J ],
					 scope: this,
					 stopEvent: true
					 }]*/
				});

		Ext.apply(this,
				{
					autoHeight: true,
					buttons: [
						{
							id: 'rdfOk',
							handler: function() {
								this.ownerCt.createDBF();
							},
							iconCls: 'refresh16',
							text: lang['sformirovat']
						},
						{
							text: '-'
						},
						HelpButton(this),
						{
							handler: function() {
								this.ownerCt.hide();
							},
							iconCls: 'cancel16',
							onTabElement: 'rdfOk',
							text: BTN_FRMCANCEL
						}
					],
					items: [this.Panel]
				});
		sw.Promed.swLpuStructureStaffExport2Dbf.superclass.initComponent.apply(this, arguments);
	},

	listeners:
	{
		'hide': function() {
			this.onHide();
		}
	},
	createDBF: function() {
		var query2export = this.query2export;
		var form = this;
		form.getLoadMask().show();
		Ext.Ajax.request(
				{
					url: form.formUrl,
					params:
					{
						query2export: query2export
					},
					callback: function(options, success, response) {
						form.getLoadMask().hide();
						if (success) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.success == true) {
								var alt = '';
								var msg = '';
								form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="' + alt + '" href="' + result.Link + '">Скачать и сохранить файл</a>';
								Ext.getCmp('rdfOk').disable();
							} else {
								form.TextPanel.getEl().dom.innerHTML = lang['trebuemyiy_otchet_ne_soderjit_dannyih_ili_pri_formirovanii_proizoshla_oshibka_obratites_s_razrabotchikam'];
								Ext.getCmp('rdfOk').enable();
							}
							form.TextPanel.render();
						}
						else {
							var result = Ext.util.JSON.decode(response.responseText);
							form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
							form.TextPanel.render();
						}
					}
				});
	},
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	show: function() {
		sw.Promed.swLpuStructureStaffExport2Dbf.superclass.show.apply(this, arguments);
		var form = this;

		form.query2export = null;
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_v_formate_dbf'];
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rdfOk').enable();

		form.TextPanel.render();
		if (arguments[0].query2export) {
			form.query2export = arguments[0].query2export;
		}
		if (arguments[0].queryName) {
			form.setTitle(arguments[0].queryName);
			form.TextPanel.getEl().dom.innerHTML = arguments[0].queryName;
		}
		if (arguments[0].onHide) {
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].url) {
			form.formUrl = arguments[0].url;
		}
		else {
			form.formUrl = '/?c=LpuStructure&m=staffExport2Dbf';
		}
	}
});