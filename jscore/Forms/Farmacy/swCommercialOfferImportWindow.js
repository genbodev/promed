/**
 * swCommercialOfferImportWindow - окно импорта из xls
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Salakhov R.
 * @version      09.2013
 * @comment
 */
sw.Promed.swCommercialOfferImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['kommercheskoe_predlojenie_import_spiska_medikamentov'],
	layout: 'border',
	id: 'CommercialOfferImportWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 162,
	resizable: false,
	maximizable: false,
	maximized: false,
	doImport:  function() {
		if( !this.form.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']);
			return false;
		}

		var file = this.form.findField('uploadfilefield').fileInput.dom.files[0];

		this.getLoadMask(lang['import_dannyih']).show();
		this.form.submit({
			scope: this,
			success: function(result_form, action) {
                if (action.result && action.result.Protocol_Link) {
                    var link = '<a href="'+action.result.Protocol_Link+'" target="_blank">протоколе импорта</a>';
                    sw.swMsg.alert('', 'Завершен импорт. Подробности в '+link+'.');
                }
				this.getLoadMask().hide();
				this.callback();
				this.hide();
			},
			failure: function(result_form, action) {

				this.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Msg) {
						Ext.Msg.alert(lang['oshibka'], action.result.Error_Msg);
					} else if (action.result.Protocol_Link) {
                        var link = '<a href="'+action.result.Protocol_Link+'" target="_blank">протоколе импорта</a>';
                        sw.swMsg.alert('', 'Завершен импорт. Подробности в '+link+'.');
                    }
				}
			}
		});
	},
	show: function() {
		var wnd = this;
		sw.Promed.swCommercialOfferImportWindow.superclass.show.apply(this, arguments);
		this.callback = Ext.emptyFn;
		this.CommercialOffer_id = null;
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
		if ( arguments[0].CommercialOffer_id ) {
			this.CommercialOffer_id = arguments[0].CommercialOffer_id;
		}

		this.form.reset();
		this.form.setValues(arguments[0]);

		return true;
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
			url: '/?c=CommercialOffer&m=importFromXls',
			region: 'center',
			autoHeight: true,
			frame: true,
			fileUpload: true,
			labelAlign: 'right',
			labelWidth: 80,
			bodyStyle: 'padding: 5px 5px 0',
			defaults: {
				anchor: '100%'
			},
			items: [{
				name: 'CommercialOffer_id',
				xtype: 'hidden',
				value: 0
			}, {
				layout: 'form',
				items: [{
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
								sw.swMsg.alert('Ошибка', 'Размер файла '+file.name+' превышает максимально допустимый размер для файлов ('+f.maxFileSize+'MB)!');
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
				{
					region: 'north',
					height: 51,
					xtype: 'textarea',
					value: lang['dlya_importa_ukajite_fayl_formata_xls_soderjaschiy_spisok_medikamentov_kommercheskogo_predlojeniya_spisok_doljen_soderjat_nomenklaturnyiy_kod_medikamenta_po_spravochniku_lekarstvennyih_sredstv_sistemyi_llo_i_tsenu_v_rublyah'],
					disabled: true
				},
				form
			]
		});
		sw.Promed.swCommercialOfferImportWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}
});