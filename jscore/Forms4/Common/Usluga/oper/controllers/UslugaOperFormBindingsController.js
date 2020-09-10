Ext6.define('usluga.oper.controllers.UslugaOperFormBindingsController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.UslugaOperFormBindingsController',

	bindings: {
		onEvnUslugaOper_pidChange: '{EvnUslugaOper_pid}',
		filterLpuSection: { date: '{EvnUslugaOper_setDate}'},
		filterMedStaffFact: {date: '{EvnUslugaOper_setDate}', LpuSection_id: '{LpuSection_uid}'},
		reloadForm: '{EvnUslugaOper_id}',
		onUslugaPlaceChange: '{UslugaPlace_id}',
		reloadLpuSectionProfile: {LpuSection_id: '{LpuSection_uid}', onDate: '{EvnUslugaOper_setDate}'},
		reloadMedSpecOmsCombo: {onDate: '{EvnUslugaOper_setDate}'},
		onEvnUslugaOper_setDateChange: '{EvnUslugaOper_setDate}',
		reloadUslugaComplexCombo: {
			UslugaComplex_Date: '{EvnUslugaOper_setDate}', ucp: '{UCP}',
			EvnUsluga_pid: '{EvnUslugaOper_pid}', UslugaCategory_id: '{UslugaCategory_id}'
		},
		loadUslugaComplexTariffCombo: {
			UslugaComplex_id: '{UslugaComplex_id}',
			PayType_id: '{PayType_id}',
			LpuSection_id: '{LpuSection_uid}',
			Person_id: '{Person_id}',
			UslugaComplexTariff_Date: '{EvnUslugaOper_setDate}'
		},
		setUslugaComplexPartitionCodeList:  {PayType_id: '{PayType_id}', LpuSection_id: '{LpuSection_uid}'}
	},

	reloadForm: function (value, none, binding)
	{
		if ( ! value || isNaN(value) || value < 1)
		{
			return;
		}


		var formPanel = this.getView(),
			wnd = formPanel.up('window'),
			vm = this.getViewModel(),
			form = formPanel.getForm(),
			EvnClass = 'EvnUslugaOper',
			action = vm.get('action') || '',
			isLoading = vm.get('formIsLoading'),

			EvnUslugaOper_id = value;

		if ( ! action.inlist(['edit', 'view']))
		{
			return;
		}


		var loadMask = new Ext6.LoadMask(wnd, {msg: "Загрузка..."});
		loadMask.show();

		form.load({

			url: '/?c=EvnUsluga&m=loadEvnUslugaEditForm',
			params: {
				class: EvnClass,
				id: EvnUslugaOper_id
			},

			failure: function()
			{
				Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function()
				{
					wnd.hide();
				});
				return false;
			},

			success: function(form, response)
			{
				var data = response.result.data;


				var	EvnCombo = form.findField('EvnUslugaOper_pid'),
					UslugaPlace = form.findField('UslugaPlace_id');

				if (action === 'edit' && data.accessType === 'view')
				{
					vm.set('action', 'view');
				}

				var uc = form.findField('UslugaComplex_id'),
					ut = form.findField('UslugaComplexTariff_id'),
					lsp = form.findField('LpuSectionProfile_id');


				if (response.response && response.response.responseText)
				{
					var response = Ext6.util.JSON.decode(response.response.responseText);
					if (response[0] && response[0].parentEvnComboData && response[0].parentEvnComboData.length)
					{
						EvnCombo.getStore().loadData(response[0].parentEvnComboData);
					}
				}


				//vm.set('Person_id', data.Person_id);
				vm.set('LpuSectionProfile_id', data.LpuSectionProfile_id);

				vm.set('UslugaComplex_id', data.UslugaComplex_id);
				vm.set('UslugaComplexTariff_id', data.UslugaComplexTariff_id);

				// при открытии происходит сразу много запросов, поэтому иногда в поле не встает название тарифа
				setTimeout(function (){

					if (data.UslugaComplexTariff_id)
					{
						if (ut.getRawValue().length == 0)
						{
							ut.setValue(data.UslugaComplexTariff_id);
						}
					}

					if (lsp.getValue() != data.LpuSectionProfile_id)
					{

						//lsp.setValue(data.LpuSectionProfile_id);
					}

					if (action == 'edit')
					{
						form.isValid();
					}

					vm.set('formIsLoading', false);

					loadMask.hide();

				}, 1500);



				// Простановка значений при выполнении операции
				// if ( this.action == 'edit' &&  this.useCase == 'OperBlock' )
				// {
				//
				// 	if ( Ext.isEmpty(base_form.findField('UslugaPlace_id').getValue()) )
				// 	{
				// 		base_form.findField('UslugaPlace_id').setValue(1);
				// 	}
				//
				// 	if ( Ext.isEmpty(base_form.findField('LpuSection_uid').getValue()) && this.LpuSection_id )
				// 	{
				// 		base_form.findField('LpuSection_uid').setValue(this.LpuSection_id);
				// 	}
				//
				// 	if ( Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue()) && this.OperBrig && this.OperBrig.length )
				// 	{
				// 		this.OperBrig.forEach(function(d, i, arr) {
				// 			if (d.SurgType_Code == 1) {
				// 				base_form.findField('MedStaffFact_id').setValue(d.MedStaffFact_id);
				// 			}
				// 		});
				// 	}
				// }


				// if ( this.action == 'edit' )
				// {
				// 	pay_type_combo.setDisabled(getRegionNick().inlist(['ekb']) && 'bud' == pay_type_combo.getFieldValue('PayType_SysNick'));
				//
				// 	if ( evn_combo.isVisible() && evn_usluga_oper_pid != null && evn_usluga_oper_pid.toString().length > 0 && evn_combo.getFieldValue('IsPriem') == 2 )
				// 	{
				// 		this.IsPriem = true;
				// 		evn_combo.focus(true, 250);
				// 	}
				//
				// 	else if ( this.parentClass != null || (evn_usluga_oper_pid != null && evn_usluga_oper_pid.toString().length > 0) ) {
				// 		evn_combo.disable();
				// 		base_form.findField('EvnUslugaOper_setDate').focus(true, 250);
				// 	}
				// 	else {
				// 		evn_combo.focus(true, 250);
				// 	}
				// }


				var EvnUslugaOper_pid = vm.get('EvnUslugaOper_pid'),

					Lpu_uid = vm.get('Lpu_uid'),
					LpuSection_uid = vm.get('LpuSection_uid'),
					LpuSectionProfile_id = vm.get('LpuSectionProfile_id'),
					MedPersonal_id = vm.get('MedPersonal_id'),
					MedStaffFact_id = vm.get('MedStaffFact_id'),
					Org_uid = vm.get('Org_uid'),

					UslugaComplex_id = vm.get('UslugaComplex_id'),
					UslugaPlace_id = vm.get('UslugaPlace_id'),
					UslugaComplexTariff_id = vm.get('UslugaComplexTariff_id'),
					UslugaComplexTariff_UED = vm.get('UslugaComplexTariff_UED'),
					isMinusUsluga = vm.get('isMinusUsluga'),

					UslugaCategory = form.findField('UslugaCategory_id'),
					rec,
					ucat_rec;


				rec = EvnCombo.getStore().getById(EvnUslugaOper_pid);

				if ( ! rec )
				{
					if (data.EvnUslugaCommon_pid_Name)
					{
						EvnCombo.setRawValue(data.EvnUslugaCommon_pid_Name);
					} else
					{
						vm.set('EvnUslugaOper_pid', null);
					}

				}

				return true;
			}

		});

		return;
	},

	onEvnUslugaOper_pidChange: function (value, none, binding)
	{
		// if (this.IsPriem) {
		// 	return false;
		// }

		var formPanel = this.getView(),
			wnd = formPanel.up('window'),
			wndVm = wnd.getViewModel(),
			vm = this.getViewModel(),
			viewData = vm.getData(),
			form = formPanel.getForm(),
			field = form.findField('EvnUslugaOper_pid'),
			record = field.getStore().getById(value),
			data = record ? record.data : null,
			action = vm.get('action') || 'add',
			rec;



		if ( data && action === 'add')
		{

			vm.set('EvnUslugaOper_setDate', data.Evn_setDate);
			vm.set('EvnUslugaOper_setTime', data.Evn_setTime);
			this.equalDates();


			vm.set('UslugaPlace_id', 1);

			if (getRegionNick() == 'ekb' && viewData.parentClass && viewData.parentClass.inlist(['EvnVizit', 'EvnSection', 'EvnPL', 'EvnPS']))
			{

				if ( ! viewData.Diag_id && data.Diag_id)
				{
					vm.set('Diag_id', data.Diag_id);
				}

			}


			if (data.LpuSection_id)
			{
				rec = form.findField('LpuSection_uid').getStore().getById(data.LpuSection_id);

				if (rec)
				{
					vm.set('LpuSection_uid', data.LpuSection_id);
				}
			}

			//this.isPriem = (form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_SysNick') == 'priem');  в отдельный binding
			//form.findField('LpuSection_uid').fireEvent('beforeselect', form.findField('LpuSection_uid'), form.findField('LpuSection_uid').getStore().getAt(index));
			// условия применения фильтра
			// beforeselect: function (combo, record, index)
			// {
			// 	var base_form = this.FormPanel.getForm();
			//
			// 	if (
			// 		typeof record == 'object'
			// 		&& Ext.isEmpty(record.get('LpuSectionServiceList'))
			// 		&& (
			// 			record.get('LpuSectionProfile_SysNick') == 'priem'
			// 			|| (getRegionNick() == 'kareliya' && record.get('LpuSectionProfile_Code') == '160')
			// 		)
			// 	) {
			// 		combo.linkedElementParams.ignoreFilter = true;
			// 	}
			// 	else {
			// 		combo.linkedElementParams.ignoreFilter = false;
			// 	}
			// }

			if (data.MedStaffFact_id)
			{
				rec = form.findField('MedStaffFact_id').getStore().getById(data.MedStaffFact_id);

				if (rec)
				{
					vm.set('MedStaffFact_id', data.MedStaffFact_id);
				}

			}


			if (data.LpuSectionProfile_id)
			{
				rec = form.findField('LpuSectionProfile_id').getStore().getById(data.LpuSectionProfile_id);

				if (rec)
				{
					vm.set('LpuSectionProfile_id', data.LpuSectionProfile_id);
				}
			}
		}

	},

	reloadLpuSectionProfile: function (params, none, binding)
	{
		var formPanel = this.getView(),
			vm = this.getViewModel(),
			form = formPanel.getForm(),
			combo = form.findField('LpuSectionProfile_id'),
			LpuSectionProfile_id = vm.get('LpuSectionProfile_id'),
			ProfileFromLpuSection_id = form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id');


		params.onDate = Ext6.util.Format.date(params.onDate, 'd.m.Y');
		params.LpuSection_id =  isNaN(Number(params.LpuSection_id)) ? null : Math.floor( Number(params.LpuSection_id) );

		if ( ! params.LpuSection_id )
		{
			combo.getStore().removeAll();
			vm.set('LpuSectionProfile_id', null);
			return;
		}


		combo.getStore().load(
			{
				params: params,
				callback: function (recs, b, s)
				{
					LpuSectionProfile_id = recs.length == 1 ? recs[0].get('LpuSectionProfile_id') : LpuSectionProfile_id;

					var recOld = this.getById(LpuSectionProfile_id),
						recFromSection = this.getById(ProfileFromLpuSection_id);

					LpuSectionProfile_id = recOld ? LpuSectionProfile_id : (recFromSection ? ProfileFromLpuSection_id : null);

					combo.setValue(recOld || recFromSection || null);
				}
			});

		return true;
	},

	reloadMedSpecOmsCombo: function (params, none, binding)
	{
		var formPanel = this.getView(),
			vm = this.getViewModel(),
			form = formPanel.getForm(),
			combo = form.findField('MedSpecOms_id'),
			MedSpecOms_id = vm.get('MedSpecOms_id');

		params.onDate = Ext6.util.Format.date(params.onDate, 'd.m.Y');


		combo.getStore().load(
			{
				params: params,
				callback: function (recs, b, s)
				{
					MedSpecOms_id = (recs.length == 1 && combo.isVisible()) ? recs[0].get('MedSpecOms_id') : MedSpecOms_id;
					var rec = this.findRecord('MedSpecOms_id', MedSpecOms_id);

					vm.set('MedSpecOms_id', rec ? MedSpecOms_id : null)
				}
			});

		return true;
	},

	filterMedStaffFact: function (params, none, binding) {
		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			vm = this.getViewModel(),
			action = vm.get('action') || '',
			parentClass = vm.get('parentClass') || '',

			form = this.getView().getForm(),
			LpuSection = form.findField('LpuSection_uid'),
			LpuSectionServiceList = LpuSection.getFieldValue('LpuSectionServiceList'),

			MedStaffFact_id = vm.get('MedStaffFact_id'),

			MedStaffFactFilterParams =
				{
					allowLowLevel: 'yes',
					onDate: date,
					isNotPolka: ! parentClass.inlist([ 'EvnPL', 'EvnVizit' ]),
					isPolka: parentClass.inlist([ 'EvnPL', 'EvnVizit' ])
				};

		if (parentClass.inlist(['EvnPS', 'EvnSection']) && 'kareliya' === getRegionNick()) {
			MedStaffFactFilterParams.isStac = true;
		}


		MedStaffFactFilterParams.LpuSection_id = (params.LpuSection_id && ! isNaN(params.LpuSection_id ) ) ? params.LpuSection_id : null;


		setMedStaffFactGlobalStoreFilter(MedStaffFactFilterParams);
		form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		rec = MedStaffFact_id ? form.findField('MedStaffFact_id').getStore().findRecord('MedStaffFact_id', MedStaffFact_id) : null;

		vm.set('MedStaffFact_id', rec ? MedStaffFact_id : null);

		return true;
	},

	filterLpuSection: function (params, none, binding)
	{
		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			vm = this.getViewModel(),
			action = vm.get('action'),
			parentClass = vm.get('parentClass') || '',

			LpuSection_id = vm.get('LpuSection_uid'),

			SectionFilterParams =
				{
					allowLowLevel: 'yes',
					onDate: date,
					isNotPolka: ! parentClass.inlist([ 'EvnPL', 'EvnVizit' ]),
					isPolka: parentClass.inlist([ 'EvnPL', 'EvnVizit' ])
				},

			form = this.getView().getForm(),
			rec = null;


		setLpuSectionGlobalStoreFilter(SectionFilterParams);
		form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));


		rec = LpuSection_id ? form.findField('LpuSection_uid').getStore().findRecord('LpuSection_id', LpuSection_id) : null;

		vm.set('LpuSection_uid', rec ? LpuSection_id : null );

		return true;
	},

	reloadUslugaComplexCombo: function (params, none, binding)
	{
		var formPanel = this.getView(),
			vm = this.getViewModel(),
			form = formPanel.getForm(),
			field = form.findField('UslugaComplex_id'),
			UslugaComplex_id = vm.get('UslugaComplex_id'),
			UslugaComplex_Name = field.getRawValue(),

			useCase = vm.get('useCase'),
			action = vm.get('action'),
			parentClass = vm.get('parentClass'),
			isLoading = vm.get('formIsLoading'),

			date = params.UslugaComplex_Date,
			dateNow = (date instanceof Date) ? date : (date ? getValidDT(date, '') : new Date()),
			UslugaComplex_Date = dateNow.format('d.m.Y'), // в строковом виде для параметров

			UslugaCategory = form.findField('UslugaCategory_id'),
			UslugaCategory_SysNick = UslugaCategory.getFieldValue('UslugaCategory_SysNick'),


			EvnUslugaOper = form.findField('EvnUslugaOper_pid'),
			record = EvnUslugaOper.getStore().getById(params.EvnUsluga_pid),
			data = record ? record.data : {};


		if (useCase == 'OperBlock')
		{
			return;
		}


		var extraParams = Object.assign(params,
			{
				to: field.to,
				DispClass_id: field.DispClass_id,
				dispOnly: field.dispOnly ? 1 : null,
				nonDispOnly: (! field.dispOnly && field.nonDispOnly) ? 1 : null
			}, params.ucp),

			fieldsToCheckCorrectId = ['EvnUsluga_pid', 'LpuSection_pid', 'Person_id', 'PersonAge', 'UslugaCategory_id', 'PayType_id']; // в селекте пустой записи может прийти строковый id

		if (data)
		{
			extraParams.LpuSection_pid = data.LpuSection_id;
			extraParams.EvnUsluga_pid = params.EvnUsluga_pid;
		} else
		{
			extraParams.LpuSection_pid = null;
			extraParams.EvnUsluga_pid = null;
		}

		extraParams.ucp = undefined;
		extraParams.UslugaComplex_Date = UslugaComplex_Date;
		extraParams.uslugaCategoryList = Ext6.JSON.encode([UslugaCategory_SysNick]);


		Ext6.each(fieldsToCheckCorrectId, function(el) {
			extraParams[el] = isNaN(Number(extraParams[el])) ? null :  Math.floor( Number(extraParams[el]) ); // могут быть не числа, а могут быть дробные числа
		});


		field.getStore().getProxy().setExtraParams(extraParams);


		if (isLoading === true || (!UslugaComplex_id && (this.UslugaCategory_SysNick_Old == UslugaCategory_SysNick)))
		{
			return;
		}

		this.UslugaCategory_SysNick_Old = UslugaCategory_SysNick?UslugaCategory_SysNick:null;
		var queryParam = ( UslugaComplex_id && UslugaComplex_Name) ? {query: UslugaComplex_Name} : {};

		field.getStore().load({
			params: queryParam,
			callback: function (recs, b, s)
			{
				var rec = this.getById(UslugaComplex_id),
					rec1;


				field.setValue(rec ? UslugaComplex_id : null);

				if (rec)
				{
					if ( ! params.UslugaCategory_id)
					{
						// если поле катеогрия услги было пустым
						rec1 = UslugaCategory.getStore().getById(rec.get('UslugaCategory_id'));

						vm.set('UslugaCategory_id', rec1 ? rec1.get('UslugaCategory_id') : null);
					}
				}
			}
		});

		return true;
	},

	loadUslugaComplexTariffCombo: function (params, none, binding)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),
			action = vm.get('action'),

			UslugaComplexTariff = form.findField('UslugaComplexTariff_id'),
			EvnUslugaOper_Price = form.findField('EvnUslugaOper_Price'),
			UslugaComplexTariff_id = vm.get('UslugaComplexTariff_id'),
			UslugaComplex = form.findField('UslugaComplex_id'),

			//UslugaComplexTariff_UED = form.findField('UslugaComplexTariff_UED'),
			//EvnUslugaCommon_Kolvo = form.findField('EvnUslugaCommon_Kolvo'),


			date = params.UslugaComplexTariff_Date,
			dateNow = (date instanceof Date) ? date : (date ? getValidDT(date, '') : new Date()),
			UslugaComplex_Date = dateNow.format('d.m.Y'); // в строковом виде для параметров



		params.UslugaComplexTariff_Date = UslugaComplex_Date;
		params.PayType_id =  isNaN(Number(params.PayType_id)) ? null : Math.floor( Number(params.PayType_id) );
		params.LpuSection_id =  isNaN(Number(params.LpuSection_id)) ? null : Math.floor( Number(params.LpuSection_id) );
		params.UslugaComplex_id =  isNaN(Number(params.UslugaComplex_id)) ? null : Math.floor( Number(params.UslugaComplex_id) );

		UslugaComplexTariff.params = params;


		if ( ! (params.PayType_id && params.Person_id && params.UslugaComplex_id  && params.UslugaComplexTariff_Date) ||
			!  (params.PayType_id > 0 && params.Person_id > 0 && params.UslugaComplex_id > 0)
		)
		{
			vm.set('UslugaComplexTariff_id', null);
			UslugaComplexTariff.getStore().removeAll();
			return;
		}

		UslugaComplexTariff.getStore().load({
			params: params,

			callback: function(recs, b, s)
			{
				this.getStore().clearFilter();
				this.lastQuery = '';


				if ( recs.length > 1 && this.isLpuFilter)
				{
					this.getStore().filterBy(function(rec)
					{
						return Ext6.isEmpty(rec.get('Lpu_id'));
					});
				}

				if ( recs.length > 0 )
				{

					if (this.isStom)
					{
						this.getStore().filterBy(function(rec) {
							return ( ! Ext6.isEmpty(rec.get('UslugaComplexTariff_UED')) && rec.get('UslugaComplexTariff_UED') != 0 )
								|| ( ! Ext6.isEmpty(rec.get('UslugaComplexTariff_UEM')) && rec.get('UslugaComplexTariff_UEM') != 0 );
						});
					} else
					{
						this.getStore().filterBy(function(rec)
						{
							return ! Ext6.isEmpty(rec.get('UslugaComplexTariff_Tariff')) && rec.get('UslugaComplexTariff_Tariff') != 0;
						});
					}

					UslugaComplexTariff_id = (recs.length == 1 && action === 'add') ? recs[0].get('UslugaComplexTariff_id') : UslugaComplexTariff_id;
					var rec = this.findRecord('UslugaComplexTariff_id', UslugaComplexTariff_id);

					vm.set('UslugaComplexTariff_id', rec ? UslugaComplexTariff_id : null);

					if (rec)
					{
						vm.set('EvnUslugaOper_Price', rec.get('UslugaComplexTariff_Tariff') || 0); //EvnUslugaCommon_Price.setValue(rec.get('UslugaComplexTariff_Tariff') || 0);
					}

					return;
				}
			}.createDelegate(UslugaComplexTariff)

		});

		return;
	},

	onEvnUslugaOper_setDateChange: function (value, none, binding)
	{
		//if (blockedDateAfterPersonDeath('personpanelid', 'EUOperEF_PersonInformationFrame', field, newValue, oldValue)) return;

		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),

			useCase = vm.get('useCase'),
			action = vm.get('action'),
			dateNow = (value instanceof Date) ? value : (value ? getValidDT(value, '') : new Date()),
			UslugaComplex_Date = dateNow.format('d.m.Y'), // в строковом виде для параметров
			parentClass = vm.get('parentClass');


		// Устанавливаем фильтр по дате для услуг
		if (getRegionNick() == 'perm' && useCase != 'OperBlock' && action == 'add')
		{
			var UslCatCombo = form.findField('UslugaCategory_id'),
				dateX = new Date(2015, 0, 1),
				rec;

			rec = UslCatCombo.getStore().findRecord('UslugaCategory_SysNick', (dateNow >= dateX) ? 'gost2011' : 'tfoms');

			if (rec && ! vm.get('UslugaCategory_id') )
			{
				//vm.set('UslugaCategory_id', rec.get('UslugaCategory_id'));
			}
		}

		this.equalDates();
	},

	onUslugaPlaceChange: function (value, none, binding)
	{
		var form = this.getView().getForm(),
			vm = this.getViewModel();


		if (value == 1) // поле будет скрыто
		{
			vm.set('MedSpecOms_id', null); // зануляем
			vm.set('Lpu_uid', null);
			vm.set('Org_uid', null);
		} else
		{
			vm.set('LpuSection_uid', null);
			vm.set('MedStaffFact_id', null);
			vm.set( value == 2 ? 'Org_uid' : 'Lpu_uid', null);
		}

		return;
	},

	// PayType_idChange: function (combo, record)
	// {
	//
	// 	if (getRegionNick() == 'buryatiya')
	// 	{
	// 		usluga_category_combo.lastQuery = "";
	// 		usluga_category_combo.getStore().clearFilter();
	// 		if (record && record.get('PayType_SysNick') == 'oms') {
	// 			usluga_category_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
	// 			usluga_category_combo.fireEvent('select', usluga_category_combo, usluga_category_combo.getStore().getAt(usluga_category_combo.getStore().findBy(function (rec) {
	// 				return (rec.get('UslugaCategory_SysNick') == 'gost2011');
	// 			})));
	// 		} else {
	// 			usluga_category_combo.clearValue();
	// 			usluga_category_combo.fireEvent('select', usluga_category_combo, null);
	// 		}
	// 	}
	// },

	// не работает
	onEvnPrescrChange: function (value, none, binding)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),

			parentClass = vm.get('parentClass'),
			action = vm.get('action'),

			UslugaPlace_id,
			LpuSection_uid,
			EvnPrescr = form.findField('EvnPrescr_id'),
			EvnPrescr_id = value,
			rec = EvnPrescr.getStore().getById(EvnPrescr_id),

			data = rec ? rec.data : null;


		if (data)
		{
			//form.findField('EvnUslugaOper_setDate').setValue(getGlobalOptions().date);
			//form.findField('EvnUslugaOper_setDate').fireEvent('change', form.findField('EvnUslugaOper_setDate'), form.findField('EvnUslugaOper_setDate').getValue());

			//если услуга добавляется по назначению, то
			//если ЛПУ назначения и места выполнения равны

			var UslugaPlace_id = data.Lpu_id == getGlobalOptions().lpu_id ? 1 : 2,
				LpuSection_uid = UslugaPlace_id == 1 ? data.LpuSection_id : null,
				Lpu_uid = UslugaPlace_id == 2 ? data.Lpu_id : null;

			vm.set('UslugaPlace_id', UslugaPlace_id);
			vm.set('LpuSection_uid', LpuSection_uid);
			vm.set('Lpu_uid', Lpu_uid);

			//index = lpu_section_combo.getStore().findBy(function(rec) {
			// 						return ( rec.get('LpuSection_id') == getGlobalOptions().CurLpuSection_id );
			// 					});
			//
			// 					if ( index >= 0 ) {
			// 						lpu_section_combo.setValue(getGlobalOptions().CurLpuSection_id);
			// 						lpu_section_combo.fireEvent('change', lpu_section_combo, getGlobalOptions().CurLpuSection_id);
			// 					}


			// if ( data.get('Lpu_id') == getGlobalOptions().lpu_id)
			// {
			// 		//this.isPriem = (LpuSection.getFieldValue('LpuSectionProfile_SysNick') == 'priem');
			//
			// } else
			// {
			// 	//указываем место выполнение Другое ЛПУ
			// 	UslugaPlace.setValue(2);
			// 	LpuSection.setValue(null);
			// 	//UslugaPlace.fireEvent('change', UslugaPlace, 2);
			//
			// 	LpuCombo.getStore().load({
			// 		callback: function(records, options, success) {
			// 			if (success && records.length>0) {
			// 				LpuCombo.setValue(records[0].get(LpuCombo.valueField));
			// 			} else {
			// 				LpuCombo.setValue(null);
			// 			}
			// 		},
			// 		params: {
			// 			Lpu_oid: getGlobalOptions().lpu_id,
			// 			OrgType: 'lpu'
			// 		}
			// 	});
			// }
		}

		return;
	},

	reloadEvnPrescr: function (params, none, binding)
	{
		// if (!evn_usluga_oper_pid)
		// {
		// 	prescr_combo.setValue(null);
		// 	prescr_combo.disable();
		//
		// } else
		// {
		// 	prescr_combo.setPrescriptionTypeCode(7);
		// 	prescr_combo.getStore().baseParams.EvnPrescr_pid = evn_usluga_oper_pid;
		// 	prescr_combo.enable();
		//
		// }
		//
		// if ( record )
		// {
		// 	evn_combo.setValue(evn_usluga_oper_pid);
		// 	prescr_combo.setPrescriptionTypeCode(7);
		// 	prescr_combo.getStore().baseParams.EvnPrescr_pid = evn_usluga_oper_pid;
		// грзузить только если такое значение есть
		// 	if ( prescr_combo.getValue() ) {
		// 		prescr_combo.getStore().baseParams.savedEvnPrescr_id = prescr_combo.getValue();
		// 		prescr_combo.getStore().load({
		// 			callback: function(){
		// 				prescr_combo.setValue(prescr_combo.getValue());
		// 				// чтобы дать возможность выбрать другое назначение
		// 				prescr_combo.hasLoaded = false;
		// 			},
		// 			params: {
		// 				EvnPrescr_id: prescr_combo.getValue()
		// 			}
		// 		});
		// 	}
		//
		// 	usluga_combo.getStore().baseParams.EvnUsluga_pid = evn_combo.getValue();
		// 	usluga_combo.getStore().baseParams.LpuSection_pid = record.get('LpuSection_id') || null;
		// } else {
		//
		// 	// взрывалось, добавил проверку на наличие response.result, вероятно нужно вообще переписать данный кусок.
		// 	//Если услуга добавлена из приёмного - подставляем приёмное
		// 	if (response.result && response.result.data && !Ext.isEmpty(response.result.data.EvnUslugaCommon_pid_Name)) {
		// 		evn_combo.setValue(response.result.data.EvnUslugaCommon_pid_Name);
		// 	} else {
		// 		evn_combo.clearValue();
		// 	}
		// 	usluga_combo.getStore().baseParams.EvnUsluga_pid = null;
		// 	usluga_combo.getStore().baseParams.LpuSection_pid = null;
		// }
	},

	linkFilesToEvn: function (value)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),
			EvnMediaDataIds = vm.get('EvnMediaDataIds') || [],
			action = vm.get('action');

		if ( ! (value && ! isNaN(value) && value > 0 && action === 'add' && EvnMediaDataIds.length > 0) )
		{
			return;
		}

		Ext6.Ajax.request({
			url: '/?c=EvnMediaFiles&m=linkFilesToEvn',
			params: {Evn_id: value, EvnMediaDataIds: Ext6.JSON.encode(EvnMediaDataIds)},
			callback: function (a, b, c, d)
			{
				console.log({a:a, b:b, c:c, d:d});
			}
		});

		return;
	},

	equalDates: function ()
	{
		var vm = this.getViewModel();

		vm.set('EvnUslugaOper_disDate', vm.get('EvnUslugaOper_setDate'));
		vm.set('EvnUslugaOper_disTime', vm.get('EvnUslugaOper_setTime'));

		return true;
	},


	setUslugaComplexPartitionCodeList: function(params, none, binding)
	{
		if ( getRegionNick() !== 'ekb')
		{
			return false;
		}

		var form = this.getView().getForm(),
			vm = this.getViewModel(),
			wndVm = vm.getParent(),

			PayType_id = ! isNaN(Number(params.PayType_id)) ? params.PayType_id : null,
			LpuSection_id = ! isNaN(Number(params.LpuSection_id)) ? params.LpuSection_id : null,

			LpuSection = form.findField('LpuSection_uid'),
			PayType = form.findField('PayType_id');


		var UslugaComplex = form.findField('UslugaComplex_id'),
			only351Group = wndVm.get('only351Group'),
			LpuUnitType_Code = wndVm.get('LpuUnitType_Code'),
			parentClass = vm.get('parentClass');



		var lsRec = LpuSection.getStore().getById(LpuSection_id),
			ptRec = PayType.getStore().getById(PayType_id),
			PayType_SysNick =  ptRec ? ptRec.get('PayType_SysNick') : null,
			LpuSectionProfile_SysNick =  ptRec ? ptRec.get('LpuSectionProfile_SysNick') : null;


		var LpuUnitType_Code = lsRec ? lsRec.get('LpuUnitType_Code') : LpuUnitType_Code,
			LpuSection_IsHTMedicalCare = lsRec ? lsRec.get('LpuSection_IsHTMedicalCare') : null;

		if (only351Group)
		{
			UslugaComplex.getStore().getProxy().setExtraParam('UslugaComplexPartition_CodeList', Ext6.util.JSON.encode([351]));

			vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode([351]));

		} else if ( parentClass == 'EvnVizit' || parentClass == 'EvnPL' )
		{
			var xdate = new Date(2015, 0, 1);


			if (PayType_SysNick == 'bud')
			{
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [351] ));
			}
		}
		else
		{
			if (LpuSectionProfile_SysNick == 'priem')
			{
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [300, 301] ));
			}
			else if ( LpuUnitType_Code == 3 || LpuUnitType_Code == 5 )
			{
				// днев
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [202,203,205] ));
			}

			else
			{
				if (PayType_SysNick == 'oms' && LpuSection_IsHTMedicalCare == 2)
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode([102,103,104,105,106,107]));
				}
				else if (PayType_SysNick == 'fbud' && LpuSection_IsHTMedicalCare == 2)
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [102,103,104,105,107,156] ));
				}
				else
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [102,103,104,105,107] ));
				}
			}

			if (PayType_SysNick == 'bud')
			{
				if (LpuUnitType_Code == 3 || LpuUnitType_Code == 5)
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [252] ));
				} else
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( LpuSection_IsHTMedicalCare == 2 ? [152, 156] : [152] ));
				}
			}

		}
	}
});