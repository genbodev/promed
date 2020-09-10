/**
 * swDokNakImport - окно импорта приходной накладной.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Farmacy
 * @access       	public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.02.2015
 */

sw.Promed.swDokNakImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['import_prihodnoy_nakladnoy'],
	layout: 'border',
	id: 'swDokNakImportWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 120,
	resizable: false,
	maximizable: false,
	maximized: false,
	doImport:  function() {
        var wnd = this;

		if( !this.form.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']);
			return false;
		}

		var file = this.form.findField('uploadfilefield').fileInput.dom.files[0];

		this.getLoadMask(lang['import_dannyih']).show();

		this.form.submit({
			scope: this,
			params: this.formParams,
			success: function(result_form, action) {
				this.getLoadMask().hide();
				this.callback({DocumentUc_id: action.result.DocumentUc_id});
				this.hide();
			}.createDelegate(this),
			failure: function(result_form, action) {
				this.getLoadMask().hide();
				if (action.result) {
					if (action.result.Protocol_Link) {
						var link = '<a href="'+action.result.Protocol_Link+'" target="_blank">протоколе импорта</a>';
						sw.swMsg.alert('', ''+lang['zavershen']+' '+lang['import']+' '+lang['podrobnosti']+' '+lang['v']+' '+link+'.', function() {
                            wnd.callback({DocumentUc_id: action.result.DocumentUc_id});
                        });
                        this.hide();
					} else if (action.result.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
				}
			}.createDelegate(this)
		});
	},
	show: function() {
		var wnd = this;
		sw.Promed.swDokNakImportWindow.superclass.show.apply(this, arguments);
		this.callback = Ext.emptyFn;

		this.form.reset();

		if ( !arguments[0] || !arguments[0].formParams || !arguments[0].formParams.WhsDocumentUc_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		}

		this.formParams = arguments[0].formParams;

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		return true;
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
			url: '/?c=Farmacy&m=importDokNak',
			region: 'center',
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
					fieldLabel: lang['fayl'],
					anchor: '100%',
					maxFileSize: 2, // MB
					emptyText: lang['vyiberite_fayl'],
					listeners: {
						fileselected: function(f, v) {
							if(f.disabled){
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
					id: 'DNIW_ImportBtn',
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
		sw.Promed.swDokNakImportWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}
});