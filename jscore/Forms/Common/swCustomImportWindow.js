/**
 * swCustomImportWindow - настраиваемое окно импорта
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Salakhov R.
 * @version      01.2015
 * @comment
 */
sw.Promed.swCustomImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['import'],
	layout: 'border',
	id: 'CustomImportWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 162,
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
            params: wnd.import_params,
			scope: this,
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action && action.result && action.result.data) {
					wnd.callback(action.result.data);
				}
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
	},
	show: function() {
		var wnd = this;
		sw.Promed.swCustomImportWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;
		this.upload_url = null;
		this.title = lang['import'];
		this.format_message = lang['dlya_importa_ukajite_fayl'];
		this.import_btn_text = lang['import'];
		this.import_params = new Object();
		this.max_file_size = 2;

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].title ) {
			this.title = arguments[0].title;
		}
		if ( arguments[0].upload_url ) {
			this.upload_url = arguments[0].upload_url;
		}
		if ( arguments[0].format_message ) {
			this.format_message = arguments[0].format_message;
		}
		if ( arguments[0].import_btn_text ) {
			this.import_btn_text = arguments[0].import_btn_text;
		}
		if ( arguments[0].import_params ) {
			this.import_params = arguments[0].import_params;
		}
		if ( arguments[0].max_file_size ) {
			this.max_file_size = arguments[0].max_file_size;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		this.setTitle(this.title);
		this.format_message_area.setValue(this.format_message);
		this.buttons[0].setText(this.import_btn_text);
		this.form.url = this.upload_url;
		this.form.reset();
		this.form.setValues(arguments[0]);
		this.form.findField('uploadfilefield').maxFileSize = this.max_file_size;

		return true;
	},
	initComponent: function() {
		var wnd = this;

		this.format_message_area = new Ext.form.TextArea({
			region: 'north',
			height: 51,
			value: null,
			disabled: true
		});

		var form = new Ext.form.FormPanel({
			url: null,
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
				wnd.format_message_area,
				form
			]
		});
		sw.Promed.swCustomImportWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}
});