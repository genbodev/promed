/**
 * swAmbulanceCardImportDbfWindow - окно импорта амбулаторных карт из Dbf
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			16.04.2014
 */

/*NO PARSE JSON*/

sw.Promed.swAmbulanceCardImportDbfWindow = Ext.extend(sw.Promed.BaseForm, {
	title:lang['zagruzka_faylov'],
	id: 'ACIDW_DbfImportWindow',
	autoHeight: true,
	width: 450,
	maximized: false,
	maximizable: false,
	resizable: false,
	modal: true,
	shim: false,

	doImportFromDbf: function() {
		this.getLoadMask(lang['pojaluysta_podojdite_proizvoditsya_import']).show();
		this.findById('ACIDW_DbfImportForm').getForm().submit({
			//timeout: 0,
			success: function (result_form, action){
				this.getLoadMask().hide();
				sw.swMsg.alert(lang['import_vyipolnen'], action.result.Message+'<br/>'+action.result.Count_New);
				this.hide();
			}.createDelegate(this),
			failure: function (result_form, action){
				this.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka_pri_vyipolnenii_importa'], action.result.Error_Msg);
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swAmbulanceCardImportDbfWindow.superclass.show.apply(this, arguments);

		this.getLoadMask().hide();
		this.findById('ACIDW_DbfImportForm').getForm().reset();
	},
	initComponent: function() {
		var that = this;
		Ext.apply(this, {
			buttons: [{
				text: '-'
			}, {
				text      : lang['zapusk'],
				tabIndex  : -1,
				tooltip   : lang['zapustit_zagruzku'],
				iconCls   : 'actions16',
				handler   : function() {
					this.doImportFromDbf()
				}.createDelegate(this)
			}, {
				text      : lang['otmena'],
				tabIndex  : -1,
				tooltip   : lang['otmena'],
				iconCls   : 'cancel16',
				handler   : function() {
					this.ownerCt.hide();
				}
			}],
			layout: 'form',
			items: [
				new Ext.form.FormPanel({
					//region: 'center',
					autoHeight: true,
					bodyStyle: 'padding: 5px',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'ACIDW_DbfImportForm',
					labelAlign: 'right',
					labelWidth: 200,
					fileUpload: true,
					items: [{
						xtype: 'fileuploadfield',
						fieldLabel: lang['vyiberite_fayl_dlya_zagruzki'],
						name: 'AmbulanceCardDbf'
					}],
					url: '/?c=AmbulanceCard&m=ImportAmbulanceCardFromDbf'
				})
			]});
		sw.Promed.swAmbulanceCardImportDbfWindow.superclass.initComponent.apply(this, arguments);
	}
});