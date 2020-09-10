/**
* swDbfImportWindow - окно импорта Dbf
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
sw.Promed.swExportMoDbfWindow = Ext.extend(sw.Promed.BaseForm, {
	title:lang['zagruzka_faylov'],
	id: 'swExportMoDbfWindow',
	height: 190,
	width: 450,
	maximized: false,
	maximizable: false,
	resizable: false,
	modal: true,
	type:null,
	shim: false,
	show: function(callback) {
		this.callback = callback;
		this.type = null;
		sw.Promed.swExportMoDbfWindow.superclass.show.apply(this, arguments);
		if(!arguments[0]){
			return false;
		}
		if(arguments[0].name){
			this.type=arguments[0].name;
		}
		this.findById('DbfImportForm').getForm().reset();
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
						that.getLoadMask(lang['pojaluysta_podojdite_proizvoditsya_import']).show();
						that.findById('DbfImportForm').getForm().submit({
							params:{
								typeAction:that.type
							},
							success: function (){
								that.getLoadMask('').hide();
								
								Ext.getCmp('swSkladMO').doReset();
								if (that.callback && 'function' == typeof(that.callback)) {
									that.callback(arguments);
								}
								that.hide();
							},
							failure: function (){
								that.getLoadMask('').hide();
								that.hide();
							}
						});
					}
				}, {
					text      : lang['otmena'],
					tabIndex  : -1,
					tooltip   : lang['otmena'],
					iconCls   : 'cancel16',
					handler   : function() {
						this.ownerCt.hide();
					}
				}],
			layout: 'border',
			items: [
				{
					id:"anonceText",
					region: 'north',
					xtype: 'textarea',
					value: lang['vyiberite_fayl_dlya_zagruzki'],
					disabled: true
				},
				new Ext.form.FormPanel({
					region: 'center',
					autoHeight: true,
					bodyStyle: 'padding: 5px',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'DbfImportForm',
					labelAlign: 'right',
					labelWidth: 180,
					fileUpload: true,
					items: [
						{
							xtype: 'textfield',
							inputType: 'file',
							fieldLabel: lang['vyiberite_faylyi_dlya_zagruzki'],
							name: 'sourcefiles'
							
						},

					],
					keys: [{
						alt: true,
						fn: function(inp, e) {
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
					}],
					params: {
						typeAction: this.type
					},
					reader: new Ext.data.JsonReader({
							success: function() {
								//
							}
						},[
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
					url: '/?c=SkladOstat&m=importOst'
				})
			]});
		sw.Promed.swExportMoDbfWindow.superclass.initComponent.apply(this, arguments);
                
		this.findById('DbfImportForm').getForm().errorReader = {
			read: function (resp){
				var result = false;
				that.getLoadMask().hide();
				try {
					result = Ext.decode(resp.responseText);
                                        
				} catch (e) {
					sw.swMsg.alert(lang['oshibka_pri_vyipolnenii_importa'],lang['pri_vyipolnenii_importa_proizoshla_oshibka'] +
						lang['pojaluysta_obratites_k_razrabotchkam_soobschiv_sleduyuschuyu_otladochnuyu_informatsiyu'] +
						'<pre style="overflow: scroll; height: 200px; width: 100%;" >При отправке формы произошла ошибка. Ответ сервера: ' + resp.responseText + '</pre>')
				}
				return result;
			}
		}
	}
});
