/**
* swFileUploadWindow - окно загрузки файлов на сервер
* Форма с необязательным полем для примечания, скрытым полем с путем файла, кнопкой для загрузки файла
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swFileUploadWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      21.10.2010
*/
/*NO PARSE JSON*/
sw.Promed.swFileUploadWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swFileUploadWindow',
	objectSrc: '/jscore/Forms/Admin/swFileUploadWindow.js',
	height: 110,
	width: 700,
	border: false,
	modal: false,
	plain: false,
	collapsible: false,
	resizable: false,
	maximizable: false,
	bodyStyle: 'padding: 2px',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	closeAction: 'hide',
	draggable: false,
	id: 'file_upload_window',
	title: lang['zagruzka_faylov'],
	enableFileDescription: false, //определяет наличие на форме поля для описания файла
	saveUrl: '', //путь к контроллеру для сохранения файла
	saveParams: null,
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				id: 'FUF_SaveButton',
				tabIndex: 10353,
				text: lang['zagruzit']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				id: 'FUF_CancelButton',
				handler: function() {
					this.ownerCt.hide();
				},
				tabIndex: 10354,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding: 5px',
				//frame: true,
				fileUpload: true,
				id: 'FileUploadForm',
				labelAlign: 'right',
				labelWidth: 80,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'FilesData' },
					{ name: 'FileDescr' }
				]),
				url: '/?c=Treatment&m=uploadFile',
				//html: '<label for="FUF_userfile3" style="width: 70px;" class="x-form-item-label">Документ:</label>  <input id="FUF_userfile3" type="file" name="userfile3" size="50" class="x-form-file-text" />',
				items: [{
					id: 'FUF_FilesData',
					name: 'FilesData',
					xtype: 'hidden'
				},
				{
					allowBlank: false,
					tabIndex: 10351,
					width: 585,
					fieldLabel: lang['dokument'],
					id: 'FUF_userfile',
					name: 'userfile',
					buttonText: lang['vyibrat'],
					xtype: 'fileuploadfield',
					listeners: {
						'fileselected': function(field, value){
							//log(value);
						}
					}
				},
				{	
					layout: 'form',
					border: false,					
					items: [{
						allowBlank: true,
						fieldLabel: lang['primechanie'],
						id: 'FUF_FileDescr',
						name: 'FileDescr',
						tabIndex: 10352,
						maxLength: 255,
						maxLengthText: lang['vyi_prevyisili_maksimalnyiy_razmer_255_simvolov'],
						height: 65,
						width: 585,
						xtype: 'textarea'
					}]
				}
				/*{this.fireEvent('fileselected', this, v);
					allowBlank: true,
					fieldLabel: lang['primechanie'],
					id: 'FUF_FileDescr',
					name: 'FileDescr',
					tabIndex: 10352,
					maxLength: 255,
					maxLengthText: lang['vyi_prevyisili_maksimalnyiy_razmer_255_simvolov'],
					height: 55,
					width: 200,
					xtype: 'textarea'
				} */]
			})]
		});
		sw.Promed.swFileUploadWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [
		{
			alt: true,
			fn: function(inp, e) {
				Ext.getCmp('file_upload_window').submit();
			},
			key: [
				Ext.EventObject.ENTER,
				Ext.EventObject.G
			],
			stopEvent: true
		}, {
			fn: function(inp, e) {
				Ext.getCmp('file_upload_window').submit();
			},
			key: [
				Ext.EventObject.ENTER
			],
			stopEvent: true
		}, {
			fn: function(inp, e) {
				Ext.getCmp('file_upload_window').hide();
			},
			key: [
				Ext.EventObject.ESC
			],
			stopEvent: true
		}
	],
	show: function() {
		sw.Promed.swFileUploadWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		var form = current_window.findById('FileUploadForm');
		form.getForm().reset();
		current_window.action = null;
		current_window.callback = Ext.emptyFn;
		current_window.owner = null;
		current_window.onHide = Ext.emptyFn;
		files_data = null;
		
		if ( arguments[0] ) {
			if ( arguments[0].action )
				current_window.action = arguments[0].action;

			if ( arguments[0].callback )
				current_window.callback = arguments[0].callback;

			if ( arguments[0].owner ) {
				current_window.owner = arguments[0].owner;
			}

			if ( arguments[0].onHide )
				current_window.onHide = arguments[0].onHide;

			if ( arguments[0].FilesData )
				files_data = arguments[0].FilesData;

			if ( arguments[0].enableFileDescription )
				current_window.enableFileDescription = arguments[0].enableFileDescription;
			else
				current_window.enableFileDescription = false;
				
			if ( arguments[0].saveUrl )
				current_window.saveUrl = arguments[0].saveUrl;
			else
				current_window.saveUrl = '/?c=Treatment&m=uploadFile';
				
			if ( arguments[0].saveParams )
				current_window.saveParams = arguments[0].saveParams;
			else
				current_window.saveParams = null;
				
			if( arguments[0].ignoreCheckData ) {
				current_window.ignoreCheckData = arguments[0].ignoreCheckData;
			} else {
				current_window.ignoreCheckData = false;
			}
			//log(arguments[0]);
		}
		if ( files_data )
		{
			form.getForm().setValues({
				FilesData: files_data
			});
		}
		else
		{
			form.getForm().setValues({
				FilesData: ''
			});
		}
		
		var desc_fld = form.getForm().findField('FileDescr');
		if (current_window.enableFileDescription) {
			current_window.setHeight(170);
			desc_fld.enable();
			desc_fld.ownerCt.show();
		} else {
			current_window.setHeight(110);
			desc_fld.ownerCt.hide();			
			desc_fld.disable();
		}
/*
		if ( current_window.action ) {
			switch ( current_window.action ) {
				case 'add':
					break;
				case 'edit':
					break;
			}
		}*/
	},
	submit: function(check_double_cancel, check_code) {
		/**/
		var current_window = this;
		var Mask = new Ext.LoadMask(this.getEl(), {msg: "Идет сохранение файла. Пожалуйста, подождите"});

		var form = this.findById('FileUploadForm').getForm();		
		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		Mask.show();
		var params = current_window.saveParams != null ? current_window.saveParams : {/*Object: 'Treatment'*/};
		form.submit({
			url: current_window.saveUrl,
			failure: function (form, action) {
				Mask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(form, action) {	
				Mask.hide();			
				if (!action.result.data && !current_window.ignoreCheckData) {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
					return false;
				}
				this.hide();
				current_window.callback(action.result.data);
				form.reset();
				current_window.findById('FileUploadForm').findById('FUF_FilesData').setValue('');
			}.createDelegate(this)
		});

	}
});