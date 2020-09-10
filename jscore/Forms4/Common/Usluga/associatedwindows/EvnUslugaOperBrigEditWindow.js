Ext6.define('OperBrigFormPanel', {
	extend: 'GeneralFormPanel',
	alias: 'widget.OperBrigFormPanel',

	url: '/?c=EvnUslugaOperBrig&m=saveEvnUslugaOperBrig',
	bodyPadding: '0 40 0 30',

	items: [
		{
			name: 'EvnUslugaOperBrig_id',
			value: null,
			xtype: 'hidden'
		}, {
			name: 'EvnUslugaOperBrig_pid',
			value: null,
			xtype: 'hidden'
		}, {
			fieldLabel: langs('Специальность'),
			allowBlank: false,
			comboSubject: 'SurgType',
			displayCode: false,
			name: 'SurgType_id',
			xtype: 'commonSprCombo'
		}, {
			fieldLabel: langs('Врач'),
			xtype: 'swMedStaffFactCombo',
			allowBlank: false,
			queryMode: 'local',
			name: 'MedStaffFact_id',
			listConfig:{
				userCls: 'usluga-med-staff-fact-combo swMedStaffFactSearch'
			}
		}
	]
});


Ext6.define('usluga.associatedwindows.EvnUslugaOperBrigEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.EvnUslugaOperBrigEditWindow',

	noTaskBarButton: true,
	modal: true,
	closeAction: 'destroy',

	title: langs('Врач'),
	closeToolText: 'Закрыть окно операционной бригады',
	cls: 'general-window',

	width: 650,
	height: 200,

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
						add: langs('Врач: Добавление'),
						edit: langs('Врач: Редактирование')
					};

				return titles[action] || langs('Врач');
			}
		}
	},

	bind: {
		title: '{title}'
	},

	items: [{xtype: 'OperBrigFormPanel'}],

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
			formPanel = wnd.down('form'),
			form = formPanel.getForm(),
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
			action = ! isNaN(Number(params.formParams.EvnUslugaOperBrig_id)) && params.formParams.EvnUslugaOperBrig_id > 0 ? 'edit' : 'add';
		}

		vm.set('action', action);
		vm.set('parentAction', params.parentAction);
		vm.set('EvnUslugaOper_setDate', params.formParams.EvnUslugaOper_setDate || null);

		wnd.callback = params.callback || Ext6.emptyFn;

		setMedStaffFactGlobalStoreFilter({onDate: params.formParams.EvnUslugaOper_setDate || null});
		form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));


		this.callParent(arguments);
		return;
	},

	onSprLoad: function (args)
	{
		var wnd = this,
			formPanel = wnd.down('form'),
			form = formPanel.getForm(),
			params = args[0];

		if ( params.surgTypeFilter )
		{
			form.findField('SurgType_id').getStore().filterBy(function(rec) {

				return {
					'-1': rec.get('SurgType_Code') != 1,
					'1': rec.get('SurgType_Code') == 1,
					'0': true
				}[params.surgTypeFilter];
			});
		}

		form.setValues(params.formParams);
		form.findField('SurgType_id').focus();

		form.isValid()

		return;
	},

	doSave: function()
	{
		var wnd = this,
			vm = wnd.getViewModel(),
			parentAction = vm.get('parentAction'),
			formPanel = wnd.down('form'),
			form = formPanel.getForm();

		var MedStaffFact_id = form.findField('MedStaffFact_id').getValue(),
			MedPersonal_Code = '',
			MedPersonal_id = null,
			MedPersonal_Fio = '',
			SurgType_Code = null,
			SurgType_id = form.findField('SurgType_id').getValue(),
			SurgType_Name = '',
			EvnUslugaOper_setDate = vm.get('EvnUslugaOper_setDate');

		var msfRec = form.findField('MedStaffFact_id').getStore().getById(MedStaffFact_id),
			stRec = form.findField('SurgType_id').getStore().getById(SurgType_id);


		if ( msfRec )
		{
			MedPersonal_Code = msfRec.get('MedPersonal_TabCode');
			MedPersonal_Fio = msfRec.get('MedPersonal_Fio');
			MedPersonal_id = msfRec.get('MedPersonal_id');
		}

		if ( stRec )
		{
			SurgType_Code = stRec.get('SurgType_Code');
			SurgType_Name = stRec.get('SurgType_Name');
		}
		

		if ( ! form.isValid() || ! (msfRec && stRec) )
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
				EvnUslugaOperBrigData: [
					{
						MedPersonal_Fio: MedPersonal_Fio,
						MedPersonal_Code: MedPersonal_Code,
						MedStaffFact_id: MedStaffFact_id,
						MedPersonal_id: MedPersonal_id,
						SurgType_Name: SurgType_Name,
						SurgType_id: SurgType_id,
						EvnUslugaOper_setDate: EvnUslugaOper_setDate
					}
				]
			};

			Ext6.Ajax.request(
				{
					url: `/?c=EvnUslugaOperBrig&m=checkMedStaffFactIsOpen&MedStaffFact_id=${MedStaffFact_id}&setDT=${EvnUslugaOper_setDate}`
				})
				.then( ({responseText}) => {console.log(responseText); return Ext6.JSON.decode(responseText)})
				.then( resp => {
					if (resp.success && resp.isOpen)
					{

						wnd.callback(data);
						wnd.close();
					} else
					{
						Ext6.Msg.alert(langs('Ошибка'), 'У выбранного врача закрыто рабочее место. Добавление врача невозможно');
					}
				});

			return;
		}


		var params = {
			MedPersonal_id: MedPersonal_id,
			EvnUslugaOper_setDate: EvnUslugaOper_setDate
		};

		
		form.submit({
			params: params,
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
			
			success: function(result_form, action) 
			{
				loadMask.hide();

				if ( action.result && action.result.EvnUslugaOperBrig_id > 0 ) 
				{
					form.findField('EvnUslugaOperBrig_id').setValue(action.result.EvnUslugaOperBrig_id);

					var data = {
						EvnUslugaOperBrigData: [{
							accessType: 'edit',
							EvnUslugaOperBrig_id: action.result.EvnUslugaOperBrig_id,
							EvnUslugaOperBrig_pid: form.findField('EvnUslugaOperBrig_pid').getValue(),
							MedPersonal_id: MedPersonal_id,
							MedStaffFact_id: MedStaffFact_id,
							SurgType_Code: SurgType_Code,
							SurgType_id: SurgType_id,
							MedPersonal_Code: MedPersonal_Code,
							MedPersonal_Fio: MedPersonal_Fio,
							SurgType_Name: SurgType_Name
						}]
					};

					wnd.callback(data);
					wnd.close();
				}
				else 
				{
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}
		});
	}
});