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
sw.Promed.swPersisFRMPImportWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['zagruzka_faylov'],
	id: 'swPersisFRMPImportWindow',
	out:null,
	height:600,
	width: 450,
	maximized: false,
	maximizable: false,
	resizable: false,
	modal: true,
	shim: false,
	show: function (callback) {
		this.callback = callback;
		sw.Promed.swPersisFRMPImportWindow.superclass.show.apply(this, arguments);
		this.RegisterList_id = null;
		this.RegisterList_Name = null;
		this.RegisterList_id = arguments[0].RegisterList_id;
		this.RegisterList_Name = arguments[0].RegisterList_Name;
		this.ChGroup.reset();
	},
	initComponent: function () {
		var that = this;
		var fields_array = [
			{name: 'academicdegree', description: lang['spravochnik_akadem_stepen'] },
			{name: 'citizenship', description: lang['spravochnik_grajdanstvo'] },
			{name: 'documtypes', description: lang['spravochnik_dokumentyi'] },
			{name: 'educationinstitution', description: lang['spravochnik_uchebnyie_zavedeniya'] },
			{name: 'educationtype', description: lang['spravochnik_obrazovanie'] },
			{name: 'level', description: lang['spravochnik_lpu'] },
			{name: 'medicalcare', description: lang['spravochnik_meditsinskaya_pomosch'] },
			{name: 'military', description: lang['spravochnik_otnoshenie_k_voennoy_slujbe'] },
			{name: 'nomenclature', description: lang['spravochnik_nomenklatura'] },
			{name: 'positiontype', description: lang['spravochnik_sovmeschenie'] },
			{name: 'post', description: lang['spravochnik_doljnost'] },
			{name: 'qualificationcategory', description: lang['spravochnik_kategoriya'] },
			{name: 'recordtypeout', description: lang['spravochnik_rabochie_dvijenie'] },
			{name: 'regime', description: lang['spravochnik_zanyatost_naseleniya'] },
			{name: 'sertificatespeciality', description: lang['spravochnik_spetsialnosti'] },
			{name: 'skippaymentreason', description: lang['spravochnik_propuski_oplatyi'] },
			{name: 'specialities', description: lang['spravochnik_stupeni_spets'] },
			{name: 'subdivision', description: lang['spravochnik_podrazdeleniya'] },
			{name: 'territories', description: lang['spravochnik_territorii'] }
			
		];
		var chgroup_array = new Array();
		
		
		for (i = 0; i < fields_array.length; i++) {
			chgroup_array.push({boxLabel: fields_array[i].name + ' - ' + fields_array[i].description, name: 'CB', value: fields_array[i].name});
		}
		
		this.ChGroup = new Ext.form.CheckboxGroup({
			id:'epsemChGroup',
			region: 'north',
			xtype: 'checkboxgroup',
			hidden: false,
			hideLabel: true,
			style : 'padding: 5px; padding-bottom:1px;',
			itemCls: 'x-check-group-alt',			
			columns: 1,
			items: chgroup_array,
			getValue: function() {
				var out = [];
				this.items.each(function(item){
				    
					if(item.checked){
						out.push(item.value);
						
					}
				});
				return out.join(',');
			}
		});
		
		Ext.apply(this, {
		    
			buttons: [
				{
					text: '-'
				},
				{
					text: lang['zapusk'],
					tabIndex: -1,
					tooltip: lang['zapustit_zagruzku'],
					iconCls: 'actions16',
					handler: function () {
						that.getLoadMask(lang['pojaluysta_podojdite_proizvoditsya_import']).show();
						that.findById('XmlImportForm').getForm().findField('FRMP').setValue(that.ChGroup.getValue());
					    that.findById('XmlImportForm').getForm().findField('RegisterList_id').setValue(that.RegisterList_id);
						that.findById('XmlImportForm').getForm().findField('RegisterList_Name').setValue(that.RegisterList_Name);
						that.findById('XmlImportForm').getForm().submit({
							timeout: 0,
							success: function () {
								that.getLoadMask('').hide();
								Ext.getCmp('ImportWindow').callback();
								that.hide();
																

								
							},
							failure: function () {
								that.getLoadMask('').hide();
								//Ext.getCmp('ImportWindow').callback();
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
			
					
			items: [this.ChGroup,
			    new Ext.form.FormPanel({
					region: 'center',
					autoHeight: true,
					bodyStyle: 'padding: 5px',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'XmlImportForm',
					labelAlign: 'right',
					labelWidth: 200,
					fileUpload: true,
					items: [
						
						{
							xtype: 'textfield',
							inputType: 'file',
							fieldLabel: lang['vyiberite_faylyi_dlya_zagruzki'],
							name:"ziparch"
							

						},
						{
							xtype: 'hidden',
							name: "FRMP",
							value: 0
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
		sw.Promed.swPersisFRMPImportWindow.superclass.initComponent.apply(this, arguments);
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
