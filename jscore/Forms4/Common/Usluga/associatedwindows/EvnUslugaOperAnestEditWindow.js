Ext6.define('AnestFormPanel', {
	extend: 'GeneralFormPanel',
	alias: 'widget.AnestFormPanel',
	url: '/?c=EvnUslugaOperAnest&m=saveEvnUslugaOperAnest',

	bodyPadding: '0 40 0 30',

	items: [
		{
			name: 'EvnUslugaOperAnest_id',
			value: null,
			xtype: 'hidden'
		}, {
			name: 'EvnUslugaOperAnest_pid',
			value: null,
			xtype: 'hidden'
		}, {
			fieldLabel: langs('Вид анестезии'),
			allowBlank: false,
			displayCode: false,
			comboSubject: 'AnesthesiaClass',
			name: 'AnesthesiaClass_id',
			xtype: 'commonSprCombo'
		}
	]
});




Ext6.define('usluga.associatedwindows.EvnUslugaOperAnestEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.EvnUslugaOperAnestEditWindow',

	noTaskBarButton: true,
	modal: true,
	closeAction: 'destroy',

	title: langs('Анестезия'),
	closeToolText: 'Закрыть окно анестезии',

	cls: 'general-window',

	width: 500,
	height: 170,

	layout: {
		type: 'vbox',
		align: 'stretch',
		pack: 'center' // центрирует форму в окне
	},

	viewModel: {
		data: {
			action: 'add'
		},

		formulas: {
			title: function (get)
			{
				var action = get('action') || '',

					titles = {
						add: langs('Анестезия: Добавление'),
						edit: langs('Анестезия: Редактирование')
					};


				return titles[action] || langs('Анестезия');
			}
		}
	},

	bind: {
		title: '{title}'
	},

	items: [
		{xtype: 'AnestFormPanel'}
	],

	buttons: [
		'->',
		{xtype: 'SimpleButton'},
		{
			text: langs('Применить'),
			xtype: 'SubmitButton',
			bind: {
				disabled: '{editable === false}'
			}
		}
	],

	show: function (params)
	{
		var wnd = this,
			vm = wnd.getViewModel(),
			action;

		if ( ! params || ! params.formParams)
		{
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'));
			wnd.close();
			return;
		}

		action = params.action;

		if ( ! action)
		{
			action = ! isNaN(Number(params.formParams.EvnUslugaOperAnest_id)) && params.formParams.EvnUslugaOperAnest_id > 0 ? 'edit' : 'add';
		}

		vm.set('action', action);
		vm.set('parentAction', params.parentAction);


		wnd.callback = params.callback || Ext6.emptyFn;

		this.callParent(arguments);

		return;
	},

	onSprLoad: function (args)
	{
		var wnd = this,

			formPanel = wnd.down('form'),
			form = formPanel.getForm(),
			params = args[0];

		form.setValues(params.formParams);

		form.findField('AnesthesiaClass_id').focus();

		return;
	},

	doSave: function()
	{
		var wnd = this,
			vm = wnd.getViewModel(),
			parentAction = vm.get('parentAction'),
			formPanel = wnd.down('form'),
			form = formPanel.getForm(),
			AnesthesiaClass_id = form.findField('AnesthesiaClass_id').getValue(),
			rec = form.findField('AnesthesiaClass_id').getStore().getById(AnesthesiaClass_id);


		if ( ! form.isValid() || ! rec)
		{
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
				},
				icon: Ext6.Msg.WARNING,
				msg: langs('Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо'),
				title: langs('Проверка данных формы')
			});

			return false;
		}

		var loadMask = new Ext6.LoadMask(wnd, {msg: "Сохранение..."});
		loadMask.show();


		// возвращаем просто запись в грид, в базу не сохраняем
		if (parentAction === 'add')
		{
			var data = {
				EvnUslugaOperAnestData: [rec.data]
			};

			wnd.callback(data);
			wnd.close();

			return;
		}



		form.submit({
			failure: function(form, reply)
			{
				loadMask.hide();

				if ( reply.result )
				{
					if ( reply.result.Error_Msg )
					{
						Ext6.Msg.alert(langs('Ошибка'), reply.result.Error_Msg);
					}
					else
					{
						Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
					}
				}

				return;
			},

			success: function(form, reply)
			{
				loadMask.hide();

				if ( reply.result && reply.result.EvnUslugaOperAnest_id > 0 )
				{
					var EvnUslugaOperAnest_id = reply.result.EvnUslugaOperAnest_id,
						formData = form.getValues(),
						rec,
						anestData = {},
						data = {
							EvnUslugaOperAnestData: []
						};

					form.findField('EvnUslugaOperAnest_id').setValue(EvnUslugaOperAnest_id)

					rec = form.findField('AnesthesiaClass_id').getStore().getById(EvnUslugaOperAnest_id);
					if ( rec )
					{
						anestData = record.data;
					}

					Ext6.apply(anestData, {
						accessType: 'edit',
						EvnUslugaOperAnest_id: EvnUslugaOperAnest_id,
						EvnUslugaOperAnest_pid: formData.EvnUslugaOperAnest_pid
					});

					data.EvnUslugaOperAnestData.push(anestData);

					wnd.callback(data);
					wnd.close();
				}
				else
				{
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}

				return;
			}
		});

		return;
	}
});