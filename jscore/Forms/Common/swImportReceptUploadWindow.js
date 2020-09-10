/**
* swImportReceptUploadWindow - окно Импорт данных
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @version      09.01.2013
*/

sw.Promed.swImportReceptUploadWindow = Ext.extend(sw.Promed.BaseForm, {
	modal: true,
	resizable: false,
	autoHeight: true,
	width: 450,
	autoScroll: true,
	title: '',
	action: null,
	callback: Ext.emptyFn,
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},

	show: function() {
		sw.Promed.swImportReceptUploadWindow.superclass.show.apply(this, arguments);

		if( !arguments[0] || !arguments[0].action ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			return false;
		}
		this.action = arguments[0].action;
		
		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		var b_f = this.Form.getForm();
		b_f.findField('Contragent_id').getStore().load({
			params: { ContragentType_id: 1 }, // только организации
			scope: b_f.findField('Contragent_id'),
			callback: function() {
				this.setValue(this.getValue());
				this.focus(true, 100);
			}
		});
		
		if( arguments[0].record != null ) {
			b_f.setValues(arguments[0].record.data);
		}
		
		this.buttons[0].setVisible(this.action != 'view');
		this.setDisabledFields(this.action == 'view');
		
		b_f.findField('uploadfilefield').setContainerVisible(this.action == 'add');
		var linkField = this.Form.find('name', 'link')[0];
		linkField.setVisible(this.action != 'add' && +arguments[0].record.get('isHisRecord') );
		if( linkField.isVisible() ) {
			linkField.getEl().update(
				arguments[0].record.get('file_name') + '[' +
				arguments[0].record.get('file_size') + '] <a href="' +
				arguments[0].record.get('ReceptUploadLog_InFail') + '" target="_blank">скачать</a>'
			);
		}

        if(arguments[0].fromSpecMEKLLO && arguments[0].fromSpecMEKLLO == true)
        {
            b_f.findField('ReceptUploadType_id').getStore().remove(b_f.findField('ReceptUploadType_id').getStore().getById(1));
            b_f.findField('ReceptUploadType_id').getStore().remove(b_f.findField('ReceptUploadType_id').getStore().getById(2));
        }
        if(arguments[0].fromAdminLLO && arguments[0].fromAdminLLO == true)
        {
            b_f.findField('ReceptUploadType_id').getStore().remove(b_f.findField('ReceptUploadType_id').getStore().getById(3));
        }

        this.setTitle(lang['import_dannyih']+this.getActionName());
		this.center();
	},
	
	setDisabledFields: function(isDisable) {
		this.Form.findBy(function(f) {
			if( f.xtype ) {
				f.setDisabled(isDisable);
			}
		});
	},
	
	getActionName: function() {
		return {
			add: lang['_dobavlenie'],
			edit: lang['_redaktirovanie'],
			view: lang['_prosmotr']
		}[this.action];
	},
	
	importReceptUpload: function() {
		var b_f = this.Form.getForm();
		if( !b_f.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']);
			return false;
		}
		
		var file = b_f.findField('uploadfilefield').fileInput.dom.files[0];
		
		this.getLoadMask(lang['import_dannyih']).show();
		b_f.submit({
			scope: this,
			success: function(form, action) {
				this.getLoadMask().hide();
				this.callback(action.result);
				this.hide();
			},
			failure: function() {
				this.getLoadMask().hide();
			}
		});
	},
	
	initComponent: function() {
		
		this.Form = new Ext.form.FormPanel({
			url: '/?c=ReceptUpload&m=uploadReceptUploadLog',
			autoHeight: true,
			frame: true,
			fileUpload: true,
			labelAlign: 'right',
			labelWidth: 100,
			defaults: {
				anchor: '100%'
			},
			items: [{
				fieldLabel: lang['punkt_otpuska'],
				listeners: {
					render: function(c) {
						//
					}
				},
				xtype: 'swcontragentcombo'
			}, {
				fieldLabel: lang['tip_dannyih'],
				xtype: 'swcommonsprcombo',
				comboSubject: 'ReceptUploadType',
                name: 'ReceptUploadType_id'
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
			}, {
				name: 'link',
				style: 'text-align: center; font-size: 10pt;',
				hidden: true
			}]
		});
		
		Ext.apply(this, {
			layout: 'fit',
			buttons: [{
				handler: this.importReceptUpload,
				scope: this,
				iconCls: 'ok16',
				text: lang['import']
			},
			'-',
			{
				handler: this.hide.createDelegate(this, []),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.Form]
		});
		sw.Promed.swImportReceptUploadWindow.superclass.initComponent.apply(this, arguments);
	}
});