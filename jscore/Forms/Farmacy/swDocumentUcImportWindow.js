/**
 * swDocumentUcImportWindow - окно импорта документов учета из xls
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Salakhov R.
 * @version      10.2013
 * @comment
 */
sw.Promed.swDocumentUcImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['import_dokumentov'],
	layout: 'border',
	id: 'DocumentUcImportWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 159,
	resizable: false,
	maximizable: false,
	maximized: false,
	doImport:  function() {
		if( !this.form.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']);
			return false;
		}

		var file = this.form.findField('uploadfilefield').fileInput.dom.files[0];

		//this.getLoadMask('Импорт данных...').show();
		this.form.submit({
			scope: this,
			success: function() {
				//this.getLoadMask().hide();
				this.callback();
				this.hide();
			},
			failure: function(result_form, action) {
				//this.getLoadMask().hide();
				if (action.result) {
					if (action.result.Protocol_Link) {
						var link = '<a href="'+action.result.Protocol_Link+'" target="_blank">протоколе импорта</a>';
						sw.swMsg.alert('', ''+lang['zavershen']+' '+lang['import']+' '+lang['podrobnosti']+' '+lang['v']+' '+link+'.');
					} else if (action.result.ErrorProtocol_Link) {
						var link = '<a href="'+action.result.ErrorProtocol_Link+'" target="_blank">протоколе импорта</a>';
						sw.swMsg.alert(lang['oshibka'], lang['pri_importe_voznikli_oshibki_podrobnosti_v']+link+'.');
					} else if (action.result.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
				}
			}
		});
	},
	show: function() {
		var wnd = this;
		sw.Promed.swDocumentUcImportWindow.superclass.show.apply(this, arguments);
		this.callback = Ext.emptyFn;
		this.DocumentUc_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		this.form.reset();
		this.form.findField('Contragent_id').getStore().baseParams.ContragentType_CodeList = '1,3,6'; //1 - Организация; 3 - Аптека; 6 - Региональный склад;

		return true;
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
			url: '/?c=Farmacy&m=importDocumentUc',
			region: 'center',
			autoHeight: true,
			frame: true,
			fileUpload: true,
			labelAlign: 'right',
			labelWidth: 110,
			bodyStyle: 'padding: 5px 5px 0',
			defaults: {
				anchor: '100%'
			},
			items: [{
				layout: 'form',
				items: [{
					fieldLabel: lang['tip_dokumenta'],
					xtype: 'swcommonsprcombo',
					comboSubject: 'DrugDocumentType',
					allowBlank: false,
					anchor: '100%',
					onLoadStore: function() {
						//отфильтровываем все типы, помимо доступных для импорта
						this.doQuery('');
						this.getStore().filterBy(function(record){
							return (record.get('DrugDocumentType_Code') == 6 || record.get('DrugDocumentType_Code') == 16); //6 - Приходная накладная; 16 - Возвратная накладная;
						});
					},
					listeners: {
						select: function(combo, record, index) {
							var code_list = '1,3,6';
							switch(record.get('DrugDocumentType_Code')) {
								case '6': //6 - Приходная накладная;
									code_list = '1,6'; //1 - Организация; 6 - Региональный склад;
									break;
								case '16': //16 - Возвратная накладная;
									code_list = '3'; //3 - Аптека;
									break;
							}
							var wnd = combo.ownerCt.ownerCt.ownerCt;
							var c_combo = wnd.form.findField('Contragent_id');
							if (c_combo.getStore().baseParams.ContragentType_CodeList != code_list) {
								c_combo.getStore().baseParams.ContragentType_CodeList = code_list;
								c_combo.setValue(null);
							}
						}
					}
				}, {
					anchor: '100%',
					allowBlank: false,
					fieldLabel: lang['organizatsiya'],
					xtype: 'swcontragentcombo',
					name: 'Contragent_id',
					hiddenName:'Contragent_id'
				}, {
					fieldLabel: lang['fayl'],
					anchor: '100%',
					maxFileSize: 2, // MB
					emptyText: lang['vyiberite_fayl'],
					listeners: {
						fileselected: function(f, v) {
							if(f.dasabled){
								f.reset();
								return false;
							}
							var file = f.fileInput.dom.files[0];
							if( file.size/1024 > f.maxFileSize*1024 ) {
								sw.swMsg.alert(lang['oshibka'], lang['razmer_fayla']+file.name+lang['prevyishaet_maksimalno_dopustimyiy_razmer_dlya_faylov']+f.maxFileSize+'MB)!');
								f.reset();
								return false;
							}
							f.setValue(v + ' '+(file.size/1024).toFixed(2)+' KB');
						}
					},
					name: 'uploadfilefield',
					allowBlank: false,
					xtype: 'fileuploadfield'
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons: [
				{
					handler: function() {
						this.ownerCt.doImport();
					},
					iconCls: 'add16',
					text: lang['import']
				},
				{
					text: '-'
				},
				HelpButton(this, 0),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items:[
				/*{
					region: 'north',
					height: 51,
					xtype: 'textarea',
					value: lang['dlya_importa_ukajite_fayl_formata_xls'],
					disabled: true
				},*/
				form
			]
		});
		sw.Promed.swDocumentUcImportWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}
});