/**
 * swXmlImportWindow - окно импорта Dbf
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       gabdushev
 * @version      2013-04
 */
sw.Promed.swXmlImportWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Импорт данных ФРМР'),
	id: 'XmlImportWindow',
	height: 150,
	width: 450,
	maximized: false,
	maximizable: false,
	resizable: false,
	modal: true,
	shim: false,
	show: function (callback) {
		this.callback = callback;
		sw.Promed.swXmlImportWindow.superclass.show.apply(this, arguments);
		var base_form = this.findById('XmlImportForm').getForm();
		base_form.reset();
		this.RegisterList_id = null;
		this.RegisterList_Name = null;
		this.RegisterList_id = arguments[0].RegisterList_id;
		this.RegisterList_Name = arguments[0].RegisterList_Name;
		this.Fl = 0;
		if(arguments[0].Fl){
			this.Fl = 1;
		}
		base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		base_form.findField('Lpu_id').disable();
	},
	initComponent: function () {
		var that = this;
		Ext.apply(this, {
			buttons: [
				{
					text: '-'
				},
				{
					text: lang['zagruzit'],
					tabIndex: -1,
					tooltip: lang['zapustit_zagruzku'],
					iconCls: 'actions16',
					handler: function () {
						that.getLoadMask(lang['pojaluysta_podojdite_proizvoditsya_import']).show();
						that.findById('XmlImportForm').getForm().findField('RegisterList_id').setValue(that.RegisterList_id);
						that.findById('XmlImportForm').getForm().findField('RegisterList_Name').setValue(that.RegisterList_Name);
						that.findById('XmlImportForm').getForm().submit({
							timeout: 0,
							success: function () {
								that.getLoadMask('').hide();
								if(this.Fl==0)Ext.getCmp('ImportWindow').callback();
								that.hide();
								
								if (that.callback && 'function' == typeof(that.callback)) {
									that.callback(arguments);
								}
							},
							failure: function () {
								that.getLoadMask('').hide();
								that.hide();
							}
						});
					}
				},
				{
					text: lang['otmena'],
					tabIndex: -1,
					tooltip: lang['otmena'],
					iconCls: 'cancel16',
					handler: function () {
						this.ownerCt.hide();
					}
				}
			],
			layout: 'border',
			items: [
				
				new Ext.form.FormPanel({
					region: 'center',
					height: 150,
					bodyStyle: 'padding: 5px',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'XmlImportForm',
					labelAlign: 'right',
					labelWidth: 100,
					fileUpload: true,
					items: [
						{
							xtype:'swlpucombo',
							name: "Lpu_id"
						},
						{
							xtype: 'textfield',
							inputType: 'file',
							fieldLabel: lang['dokument'],
							autoCreate: { tag: 'input', name: 'sourcefiles[]', type: 'text', size: '20', autocomplete: 'off' }

						},
						{
							xtype: 'hidden',
							name: "RegisterList_id",
							value: this.RegisterList_id
						},
						{
							xtype: 'hidden',
							name: "RegisterList_Name",
							value: this.RegisterList_Name
						}
					],
					keys: [
						{
							alt: true,
							fn: function (inp, e) {
								switch (e.getKey()) {
									case Ext.EventObject.C:
										if (this.action != 'view') {
											this.doSave(false);
										}
										break;
									case Ext.EventObject.J:
										this.hide();
										break;
								}
							},
							key: [ Ext.EventObject.C, Ext.EventObject.J ],
							scope: this,
							stopEvent: true
						}
					],
					params: {
						RegisterList_id: this.RegisterList_id,
						RegisterList_Name: this.RegisterList_Name
					},
					reader: new Ext.data.JsonReader({
						success: function () {
							//
						}
					}, [
						{ name: 'LocalDbList_id' },
						{ name: 'LocalDbList_name' },
						{ name: 'LocalDbList_prefix' },
						{ name: 'LocalDbList_nick' },
						{ name: 'LocalDbList_schema' },
						{ name: 'LocalDbList_key' },
						{ name: 'LocalDbList_module' },
						{ name: 'LocalDbList_sql' }
					]),
					timeout: 60000,
					url: '/?c=ImportSchema&m=run'
				})
			]});
		sw.Promed.swXmlImportWindow.superclass.initComponent.apply(this, arguments);
		this.findById('XmlImportForm').getForm().errorReader = {
			read: function (resp) {
				var result = false;
				that.getLoadMask().hide();
				try {
					result = Ext.decode(resp.responseText);
				} catch (e) {
					sw.swMsg.alert(lang['oshibka_pri_vyipolnenii_importa'], lang['pri_vyipolnenii_importa_proizoshla_oshibka'] +
						lang['pojaluysta_obratites_k_razrabotchkam_soobschiv_sleduyuschuyu_otladochnuyu_informatsiyu'] +
						'<pre style="overflow: scroll; width: 300px; height: 200px; width: 100%;" >При отправке формы произошла ошибка. Ответ сервера: ' + resp.responseText + '</pre>')
				}
				return result;
			}
		}
	}
});
