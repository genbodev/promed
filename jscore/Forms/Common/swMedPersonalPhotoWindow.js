/**
* swMedPersonalPhotoWindow - окно редактирования фотографии врача.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Dmitry Storozhev
* @version      24.08.2011
*
*/

sw.Promed.swMedPersonalPhotoWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	shim: false,
	height: 430,
	width: 350,
	closeAction: 'hide',
	id: 'swMedPersonalPhotoWindow',
	objectName: 'swMedPersonalPhotoWindow',
	title: 'Фотография медицинского сотрудника',
	plain: true,
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : 'Отмена',
			tabIndex  : -1,
			tooltip   : 'Отмена',
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	listeners:
	{
		'hide': function(win)
		{
			if(win.form_fields)
			{
				for(i=0; i<win.form_fields.length; i++)
				{
					win.form_fields[i].enable();
				}
			}
			this.buttons[0].setVisible(true);
			win.FileUploadField.setVisible(true);
			win.overwriteTpl(false);
		}
	},

	show: function() 
	{
		sw.Promed.swMedPersonalPhotoWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		if(!arguments[0] || !arguments[0]['MedPersonal_id'])
		{
			sw.swMsg.alert('Ошибка', 'Не передан идентификатор врача (MedPersonal_id)!');
			win.hide();
			return false;
		}
		// Аргументы 
		this.MedPersonal_id = (arguments[0].MedPersonal_id)?arguments[0].MedPersonal_id:null;
		this.photo_path = (arguments[0].photo_path)?arguments[0].photo_path:null;
		win.action = 'view';
		this.FileUploadField.onResize(330,30);

		win.getLoadMask('Загрузка данных').show();
		var params = {MedPersonal_id: this.MedPersonal_id};
		// Чтение инфы о фотографии
		Ext.Ajax.request({
			callback:function (options, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText) {
					var obj = Ext.util.JSON.decode(response.responseText);
					
					if(obj.mp_photo == '')
						// todo: здесь надо нормальный размер фотографии
						obj.file_url = '';//'/img/default_user.jpg';
					else
						obj.file_url = obj.mp_photo;
					win.overwriteTpl(obj);
				} else {
					win.hide();
				}
			},
			params: params,
			url: '/?c=MedPersonal&m=getMedPersonalPhoto',
		});

	},
	
	overwriteTpl: function(obj)
	{
		if(!obj){
			var obj = {};
			obj.file_url = '';
			obj.full_url = '';
		}
		
		if (obj.file_url) {
			obj.full_url = obj.file_url.replace('thumbs/','');
			this.findById('MedPersonalPhotoPanel').tpl = new Ext.Template(this.PhotoTpl);
		} else {
			// todo: Здесь надо картинку по умолчанию
			this.findById('MedPersonalPhotoPanel').tpl = new Ext.Template('<div><!--img style="text-align: center; max-height:300px; max-width:300px;" src="" /--></div>');
		}
		this.findById('MedPersonalPhotoPanel').tpl.overwrite(this.findById('MedPersonalPhotoPanel').body, obj);
	},
	
	
	initComponent: function() 
	{
		
		this.PhotoTpl = [
			//'<div><a target="_blank" href="{full_url}"><img style="display: block; margin: 0 auto; max-height:300px; max-width:300px;" src="{file_url}" /></a></div>'
			// todo: Показывать полную фотографию надо по другому, с защитой пути отображения
			'<div><a target="_blank"><img style="display: block; margin: 0 auto; max-height:300px; max-width:300px;" src="{file_url}" /></a></div>'
		];
		this.FileUploadField = new Ext.form.FileUploadField({
			allowedExtensions: ['jpg', 'jpeg', 'pjpeg', 'png', 'gif'],
			hideLabel: true,
			buttonOnly: true,
			link: {bodyStyle: 'background: transparent;',border: false, linkId:'file_upload_link', html:'<div style="text-align: center; width:100%;"><a id="file_upload_link" style="text-align: center;" href="#">Выбрать фотографию</a></div>'},
			name: 'mp_photo',
			id: 'mp_photo',
			buttonText: 'Обновить',
			//input: {style:'display:none'},
			listeners: {
				fileselected: function(elem, fname) {
					var win = this;
					var frm = this.PhotoPanel.getForm();
					var re = /\.[jgp][pin][gf]/i;
					var access = re.test(fname);
					if(!access) {
						sw.swMsg.alert('Ошибка', 'Данный тип загружаемого файла не поддерживается!<br />Поддерживаемые типы: *jpg, *gif, *png');
						elem.reset();
						return false;
					}
					var params = {MedPersonal_id:win.MedPersonal_id};
					// Получаем уровень для отправки на сервер
					frm.submit({
						params: params,
						success: function(frm, resp) {
							var obj = Ext.util.JSON.decode(resp.response.responseText);
							win.overwriteTpl(obj);
						},
						failure: function(frm, action) {
							if (action.result) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: (action.result.Error_Msg)?action.result.Error_Msg:'При сохранении произошли ошибки',
									title: ERR_WND_TIT
								});
							}
						}
					});
				}.createDelegate(this)
			},
			width: 130
		});
		this.ClearButton = new Ext.Button({
			text: 'Очистить',
			style: "margin-left:130px;",
			handler: function() 
			{
				var win = this;
				win.getLoadMask('Удаление фото').show();
				Ext.Ajax.request({
					callback:function (options, success, response) {
						win.getLoadMask().hide();
						if (success && response.responseText) {
							var obj = Ext.util.JSON.decode(response.responseText);
							win.overwriteTpl(false);
						} else {
							sw.swMsg.alert('Ошибка', 'Ошибка при удалении фото');
						}
					},
					params: {MedPersonal_id: win.MedPersonal_id},
					url: '/?c=MedPersonal&m=deleteMedPersonalPhoto',
				});
			}.createDelegate(this)
		});
		
		this.PhotoPanel = new Ext.form.FormPanel({
			region: 'center',
			width: 350,
			//style: "padding: 5px;",
			bodyStyle: 'background: transparent;',
			//border: false,
			id: 'upload_panel',
			url: '/?c=MedPersonal&m=uploadMedPersonalPhoto',
			fileUpload: true,
			items: [
				{
					height: 300,
					style: "margin: 0 auto;",
					bodyStyle: 'background: transparent;',
					border: false,
					width: 300,
					xtype: 'panel',
					id: 'MedPersonalPhotoPanel',
					name: 'MedPersonalPhotoPanel',
					tpl: ''
				},
				this.FileUploadField,
				this.ClearButton 
			]
		});

		
		Ext.apply(this, 
		{
			layout: 'border',
			defaults:
			{
				bodyStyle: 'padding: 3px; background: #DFE8F6;'
			},
			items: [this.PhotoPanel]
		});
		sw.Promed.swMedPersonalPhotoWindow.superclass.initComponent.apply(this, arguments);
	}
});