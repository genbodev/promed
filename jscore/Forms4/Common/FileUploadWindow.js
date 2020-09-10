


Ext6.define('FilesUploadForm', {
	extend: 'Ext6.form.FormPanel',
	alias: 'widget.FilesUploadForm',
	//title: langs('Прикрепите файл к услуге'),
	bodyPadding: '15 0 0 20',
	border: false,
	width: 400,
	items: [
		{
			name: 'Evn_id',
			xtype: 'hidden'
		},
		{
			xtype: 'filefield',
			name: 'userfile',
			fieldLabel: langs('Файл'),
			msgTarget: 'side',
			allowBlank: false,
			anchor: '100%',
			buttonText: langs('Выбрать файл')
		}, {
			allowBlank: true,
			fieldLabel: langs('Примечание'),
			anchor: '79.5%',
			maxLength: 255,
			maxLengthText: langs('Вы превысили максимальный размер 255 символов'),
			name: 'FileDescr',
			xtype: 'textarea'
		}

	]
});





Ext6.define('common.FileUploadWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.FileUploadWindow',
	renderTo: Ext.getCmp('main-center-panel').body.dom,

	//title: langs('Добавить общую услугу'),
	width: 600,
	height: 250,
	title: 'Загрузка файлов',
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	modal: true,

	items: [
		Ext6.create('FilesUploadForm')
	],

	buttons: [
		Ext6.create('SubmitButton', {
			text: 'Прикрепить',
			handler: function()
			{
				var wnd = this.up('window'),
					form = wnd.down('form').getForm(),
					Evn_id = form.findField('Evn_id');

				if ( ! Evn_id)
				{
					Ext6.Msg.alert('Ошибка', 'Не найден идентификатор услуги. Сохраните услугу');
					return;
				}


				if(form.isValid())
				{
					form.submit({
						url: '/?c=EvnMediaFiles&m=uploadFile',
						params: {saveOnce: true},
						waitMsg: 'Загрузка...',
						success: function(fp, reply)
						{
							console.log(reply)
							console.log(reply.result)
							var fileName = '';

							if (reply.result.success)
							{
								var data = Ext6.JSON.decode(reply.result.data);
								if (data && data[0])
								{
									fileName = '"' + data[0].orig_name + '"';
								}
								Ext6.Msg.alert('Успешная загрузка', 'Файл ' + fileName + ' был загружен');

							} else if (reply.result.Error_Msg) {
								Ext6.Msg.alert('Ошибка', reply.result.Error_Msg);
							} else {
								Ext6.Msg.alert('Ошибка', 'Неизвестная ошибка');
							}
							wnd.callback();
							wnd.hide();
						}
					});
				}
			}
		})
	],

	show: function (params)
	{
		var wnd = this,
			form = wnd.down('form').getForm(),
			Evn = form.findField('Evn_id');



		if ( ! (params && params.Evn_id) )
		{
			Ext6.Msg.alert(langs('Сообщение'), langs('Не найден идентификатор услуги. Сохраните услугу'), function()
			{
				wnd.hide();
			});
			return false;
		}

		wnd.callback = params.callback || Ext6.emptyFn;

		Evn.setValue(params.Evn_id)

		this.callParent(arguments);
	},
	listeners: {
		hide: function () {
			this.down('form').reset();
			this.callback = Ext6.emptyFn;
		}
	}
});