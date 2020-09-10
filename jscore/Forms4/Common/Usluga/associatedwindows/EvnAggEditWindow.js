Ext6.define('AggEditForm', {
	extend: 'GeneralFormPanel',
	alias: 'widget.AggEditForm',

	bodyPadding: '0 40 0 30',

	reader: {
		type: 'json',
		model: 'usluga.associatedwindows.models.AggFormModel'
	},

	defaults: {
		labelWidth: 170
	},
	items: [
		{
			name: 'accessType',
			value: '',
			xtype: 'hidden'
		}, {
			name: 'EvnAgg_id',
			value: null,
			xtype: 'hidden'
		}, {
			name: 'EvnAgg_pid',
			value: null,
			xtype: 'hidden'
		}, {
			name: 'Person_id',
			value: null,
			xtype: 'hidden'
		}, {
			name: 'PersonEvn_id',
			value: null,
			xtype: 'hidden'
		}, {
			name: 'Server_id',
			value: null,
			xtype: 'hidden'
		}, {
			layout: 'hbox',
			border: false,

			margin: '0 0 5 0',
			items: [
				{
					xtype: 'datefield',
					plugins: [new Ext6.ux.InputTextMask('99.99.9999', true)],
					fieldLabel: langs('Дата/время'),
					allowBlank: false,
					//margin: '0 40 0 0',
					labelWidth: 170,
					width: 300,
					name: 'EvnAgg_setDate'
				},
				{xtype: 'tbspacer', width: 30},
				{
					xtype: 'swTimeField',
					allowBlank: false,
					width: 125,

					hideLabel: true,
					name: 'EvnAgg_setTime'
				},
				{xtype: 'tbspacer', width: 100}
			]
		},
		{
			allowBlank: false,
			comboSubject: 'AggType',
			fieldLabel: langs('Вид осложнения'),
			name: 'AggType_id',
			displayCode: false,
			//orderBy: (getRegionNick() == 'kareliya' ? 'Name' : 'Code'),
			xtype: 'commonSprCombo'
		}, {
			allowBlank: false,
			comboSubject: 'AggWhen',
			displayCode: false,
			fieldLabel: langs('Контекст осложнения'),
			name: 'AggWhen_id',
			xtype: 'commonSprCombo'
		}
	]

});



Ext6.define('usluga.associatedwindows.EvnAggEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.EvnAggEditWindow',
	requires: ['usluga.associatedwindows.models.AggFormModel'],

	noTaskBarButton: true,
	modal: true,
	closeAction: 'destroy',

	title: 'Осложнение',
	closeToolText: 'Закрыть окно осложнения',
	cls: 'general-window',

	viewModel: {
		data: {

		}
	},

	width: 600,
	height: 230,

	layout: {
		type: 'vbox',
		align: 'stretch',
		pack: 'center' // центрирует форму в окне
	},


	items: [
		{
			xtype: 'AggEditForm'
		}
	],
	buttons: [
		'->',
		{xtype: 'SimpleButton'},
		{
			xtype: 'SubmitButton',
			text: 'Применить'
		}
	],

	show: function (params)
	{
		var wnd = this;
		if  ( ! params || ! params.formParams)
		{
			wnd.close();
			return;
		}


		this.callParent(arguments);

		return;
	},

	onSprLoad: function (args)
	{
		var params = args[0],
			vm = this.getViewModel(),
			action = params.action,
			EvnAgg_id = params.EvnAgg_id,
			form = this.down('form').getForm();


		vm.set('action', action);
		vm.set('parentAction', params.parentAction);

		this.callback = params.callback || Ext6.emptyFn;


		form.setValues(params.formParams);
		form.isValid();

		return;



		if (EvnAgg_id)
		{
			form.load({
				url: '/?c=EvnAgg&m=loadEvnAggEditForm',
				params: {EvnAgg_id: EvnAgg_id}
			})
		} else
		{

		}

		return;
	},

	doSave: function ()
	{
		var wnd = this,
			vm = wnd.getViewModel(),
			parentAction = vm.get('parentAction'),
			form = wnd.down('form').getForm(),
			AggType_id = form.findField('AggType_id').getValue(),
			AggWhen_id = form.findField('AggWhen_id').getValue(),
			AggType = form.findField('AggType_id'),
			AggWhen = form.findField('AggWhen_id'),
			typeRec = AggType.getStore().getById(AggType_id),
			whenRec = AggWhen.getStore().getById(AggWhen_id),
			AggType_Name = typeRec ? typeRec.get('AggType_Name') : null,
			AggWhen_Name = whenRec ? whenRec.get('AggWhen_Name') : null;

		if ( ! form.isValid())
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

		// возвращаем просто запись в грид, в базу не сохраняем
		if (parentAction === 'add')
		{
			var data = {
				EvnAggData: [
					{
						accessType: 'edit',
						//EvnAgg_id: form.findField('EvnAgg_id').getValue(),
						EvnAgg_pid: form.findField('EvnAgg_pid').getValue(),
						Person_id: form.findField('Person_id').getValue(),
						PersonEvn_id: form.findField('PersonEvn_id').getValue(),
						Server_id: form.findField('Server_id').getValue(),
						AggType_id: AggType_id,
						AggType_Name: AggType_Name,
						AggWhen_id: AggWhen_id,
						AggWhen_Name: AggWhen_Name,
						EvnAgg_setDate: form.findField('EvnAgg_setDate').getValue(),
						EvnAgg_setTime: form.findField('EvnAgg_setTime').getValue()
					}
				]
			};

			wnd.callback(data);
			wnd.close();

			return;
		}

		form.submit({
			url: '/?c=EvnAgg&m=saveEvnAgg',
			failure: function (form, reply)
			{

				if ( reply.result.Error_Msg )
				{
					Ext6.Msg.alert(langs('Ошибка'), reply.result.Error_Msg);
				}
				else
				{
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
				}

				return;
			},
			success: function (form, reply)
			{
				if ( reply.result && reply.result.EvnAgg_id > 0 )
				{
					form.findField('EvnAgg_id').setValue(reply.result.EvnAgg_id);

					var data = {};


					data.EvnAggData = [{
						accessType: 'edit',
						EvnAgg_id: form.findField('EvnAgg_id').getValue(),
						EvnAgg_pid: form.findField('EvnAgg_pid').getValue(),
						Person_id: form.findField('Person_id').getValue(),
						PersonEvn_id: form.findField('PersonEvn_id').getValue(),
						Server_id: form.findField('Server_id').getValue(),
						AggType_id: AggType_id,
						AggType_Name: AggType_Name,
						AggWhen_id: AggWhen_id,
						AggWhen_Name: AggWhen_Name,
						EvnAgg_setDate: form.findField('EvnAgg_setDate').getValue(),
						EvnAgg_setTime: form.findField('EvnAgg_setTime').getValue()
					}];

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