/**
 * swDrugRequestReceptImportWindow - окно импорта сводных заявок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Salakhov R.
 * @version      01.2015
 * @comment
 */
sw.Promed.swDrugRequestReceptImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['svodnyie_zayavki_import'],
	layout: 'border',
	id: 'DrugRequestReceptImportWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 155,
	resizable: false,
	maximizable: false,
	maximized: false,
	doImport:  function() {
		var wnd = this;

		if( !this.form.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']);
			return false;
		}

		Ext.Ajax.request({
			params: {
				ReceptFinance_id: wnd.form.findField('ReceptFinance_id').getValue(),
				DrugRequestPeriod_id: wnd.form.findField('DrugRequestPeriod_id').getValue()
			},
			callback: function(opt, success, resp) {
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj && response_obj.success) {
					if (response_obj.cnt > 0) {
						Ext.Msg.alert(lang['oshibka'], lang['dlya_zadannogo_tipa_i_perioda_uje_suschestvuyut_zayavki']);
					} else {
						wnd.getLoadMask(lang['import_dannyih']).show();
						wnd.form.submit({
							scope: this,
							success: function(result_form, action) {
								wnd.getLoadMask().hide();
								wnd.callback();
								wnd.hide();
							},
							failure: function(result_form, action) {
								wnd.getLoadMask().hide();
								if (action.result) {
									if (action.result.Error_Msg) {
										Ext.Msg.alert(lang['oshibka'], action.result.Error_Msg);
									}
								} else {
									Ext.Msg.alert(lang['oshibka'], lang['pri_importe_voznikla_oshibka']);
								}
							}
						});
					}
				}
			},
			url: '/?c=DrugRequestRecept&m=getCount'
		});
	},
	show: function() {
		var wnd = this;
		sw.Promed.swDrugRequestReceptImportWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;

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
		this.form.setValues(arguments[0]);

		return true;
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
			url: '/?c=DrugRequestRecept&m=importFromXls',
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
				layout: 'form',
				items: [{
					fieldLabel: lang['tip_zayavki'],
					xtype: 'swreceptfinancecombo',
					hiddenName: 'ReceptFinance_id',
					allowBlank: false
				}, {
					fieldLabel: lang['period'],
					xtype: 'swdrugrequestperiodcombo',
					hiddenName: 'DrugRequestPeriod_id',
					allowBlank: false
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
				form
			]
		});
		sw.Promed.swDrugRequestReceptImportWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}
});