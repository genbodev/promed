/**
 * Контроллер формы EvnUslugaCommonEditWindow
 */

Ext6.define('usluga.common.controllers.UslugaFormBindingsController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.UslugaFormBindingsController',

	bindings: {
		onEvnUslugaCommon_pidChange: '{EvnUslugaCommon_pid}',
		onEvnUslugaCommon_setDateChange: '{EvnUslugaCommon_setDate}',
		filterLpuSection: {UserLpuSection_id: '{UserLpuSection_id}', UserLpuSections: '{UserLpuSections}', EvnUslugaCommon_setDate: '{EvnUslugaCommon_setDate}'},
		filterMedStaffFact: {UserMedStaffFact_id: '{UserMedStaffFact_id}', UserMedStaffFacts: '{UserMedStaffFacts}', EvnUslugaCommon_setDate: '{EvnUslugaCommon_setDate}', LpuSection_id: '{LpuSection_uid}'},
		reloadLpuSectionProfile: {LpuSection_id: '{LpuSection_uid}', onDate: '{EvnUslugaCommon_setDate}'},
		reloadMedSpecOmsCombo: { onDate: '{EvnUslugaCommon_setDate}'},
		reloadUslugaComplexCombo: {
			ucp: '{UCP}',  LpuSection_id: '{LpuSection_uid}', UslugaComplex_Date: '{EvnUslugaCommon_setDate}',
			LpuSectionProfile_id: '{LpuSectionProfile_id}', PayType_id: '{PayType_id}',
			EvnUsluga_pid: '{EvnUslugaCommon_pid}', UslugaCategory_id: '{UslugaCategory_id}'
		}, // isStom: '{isStom}', isStac: '{isStac}',
		checkOtherLpu: {Lpu_oid: '{Lpu_uid}', Date: '{EvnUslugaCommon_setDate}'},
		onUslugaPlaceChange: '{UslugaPlace_id}',
		//onPayTypeChange: '{PayType_id}',
		loadUslugaComplexTariffCombo: {UslugaComplex_id: '{UslugaComplex_id}', PayType_id: '{PayType_id}', LpuSection_id: '{LpuSection_uid}', Person_id: '{Person_id}', UslugaComplexTariff_Date: '{EvnUslugaCommon_setDate}'},
		//onUslugaCategoryChange: '{UslugaCategory_id}',
		reloadForm: '{EvnUslugaCommon_id}',
		reloadPersonInfoPanel: '{Person_id}',
		reloadEvnPrescr: {EvnPrescr_pid: '{EvnUslugaCommon_pid}', LpuSection_pid: '{LpuSection_uid}'},
		onEvnPrescrChange: '{EvnPrescr_id}',
		setUslugaComplexPartitionCodeList: {PayType_id: '{PayType_id}', LpuSection_id: '{LpuSection_uid}', date: '{EvnUslugaCommon_setDate}'}
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
				//если услуга добавляется по назначению, то
				//если ЛПУ назначения и места выполнения равны

				var UslugaPlace_id = data.Lpu_id == getGlobalOptions().lpu_id ? 1 : 2,
					LpuSection_uid = UslugaPlace_id == 1 ? data.LpuSection_id : null,
					Lpu_uid = UslugaPlace_id == 2 ? data.Lpu_id : null;

				vm.set('UslugaPlace_id', UslugaPlace_id);
				vm.set('LpuSection_uid', LpuSection_uid);
				vm.set('Lpu_uid', Lpu_uid);
		}

		return;
	},

	reloadEvnPrescr: function(params, none, binding)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),

			withoutEvnDirection = vm.get('withoutEvnDirection') || 1,
			PrescriptionType_Code = vm.get('PrescriptionType_Code') || null,

			Evn = form.findField('EvnUslugaCommon_pid'),
			EvnPrescr = form.findField('EvnPrescr_id'),
			EvnPrescr_id = vm.get('EvnPrescr_id'),

			rec = Evn.getStore().getById(params.EvnPrescr_pid);

		params.PrescriptionType_Code = PrescriptionType_Code;
		params.withoutEvnDirection = withoutEvnDirection;


		if ( ! (rec && params.LpuSection_pid > 0) )
		{
			return;
		}

		EvnPrescr.getStore().load({params: params, callback: function (recs, b, s)
			{
				rec = this.findRecord('EvnPrescr_id', EvnPrescr_id);

				vm.set('EvnPrescr_id', rec ? EvnPrescr_id : null);
			}
		});

		return;
	},

	reloadPersonInfoPanel: function (value, none, binding)
	{
		var formPanel = this.getView(),
			wnd = formPanel.up('window'),
			PersonInfoPanel = wnd.down('PersonInfoPanelContents');

		if (value && ! isNaN(value) && value > 0)
		{
			PersonInfoPanel.getStore().load({params:{Person_id: value}});
		}

		return;
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
			EvnClass = 'EvnUslugaCommon',
			action = vm.get('action') || '',
			isLoading = vm.get('formIsLoading'),

			EvnUslugaCommon_id = value;

		if ( ! action.inlist(['edit', 'view']))
		{
			return;
		}


		var loadMask = new Ext6.LoadMask(wnd, {msg: "Загрузка..."});
		loadMask.show();

		form.load({

			url: wnd.loadUrl,
			params: {
				class: EvnClass,
				id: EvnUslugaCommon_id,
				archiveRecord: wnd.archiveRecord
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


				var	EvnCombo = form.findField('EvnUslugaCommon_pid'),
					UslugaPlace = form.findField('UslugaPlace_id');

				if (action === 'edit' && data.accessType === 'view')
				{
					vm.set('action', 'view');
				}



				var uc = form.findField('UslugaComplex_id'),
					ut = form.findField('UslugaComplexTariff_id'),
					lsp = form.findField('LpuSectionProfile_id');


				vm.set('LpuSectionProfile_id', data.LpuSectionProfile_id);


				vm.set('UslugaComplex_id', data.UslugaComplex_id);
				vm.set('UslugaComplexTariff_id', data.UslugaComplexTariff_id)

				// при открытии происходит сразу много запросов, поэтому иногда в поле не встает название тарифа
				setTimeout(function (){

					if (data.UslugaComplexTariff_id)
					{
						if (ut.getRawValue().length == 0)
						{
							ut.setValue(data.UslugaComplexTariff_id); //vm.set('UslugaComplexTariff_id', data.UslugaComplexTariff_id)
						}
					}

					if (lsp.getValue() != data.LpuSectionProfile_id)
					{
						lsp.setValue(data.LpuSectionProfile_id);
					}

					if (action == 'edit')
					{
						form.isValid();
					}

					vm.set('formIsLoading', false);

					loadMask.hide();

				}, 1500);




				var evnUslugaCommon_pid = vm.get('EvnUslugaCommon_pid'),
					LpuSection_uid = vm.get('LpuSection_uid'),
					UslugaPlace_id = vm.get('UslugaPlace_id'),
					rec;



				rec = EvnCombo.getStore().getById(evnUslugaCommon_pid);

				if ( rec )
				{
					//EvnCombo.setValue(evnUslugaCommon_pid);

					// логика для поля назначение

					// PrescrCombo.setPrescriptionTypeCode(thas.PrescriptionType_Code || null);
					// PrescrCombo.getStore().baseParams.withoutEvnDirection = thas.withoutEvnDirection;
					// PrescrCombo.getStore().baseParams.EvnPrescr_pid = evnUslugaCommon_pid;


					// if ( PrescrCombo.getValue() )
					// {
					// 	PrescrCombo.getStore().baseParams.savedEvnPrescr_id = PrescrCombo.getValue();
					// 	PrescrCombo.getStore().load({
					// 		callback: function(){
					// 			// чтобы дать возможность выбрать другое назначение
					// 			PrescrCombo.hasLoaded = false;
					// 			var rec = PrescrCombo.getStore().getById(PrescrCombo.getValue());
					// 			if (rec && rec.get('PrescriptionType_Code')) {
					// 				thas.PrescriptionType_Code = rec.get('PrescriptionType_Code');
					// 				PrescrCombo.setPrescriptionTypeCode(thas.PrescriptionType_Code);
					// 			}
					// 			PrescrCombo.setValue(PrescrCombo.getValue());
					// 		},
					// 		params: {
					// 			EvnPrescr_id: PrescrCombo.getValue()
					// 		}
					// 	});
					// }


				} else
				{
					if (data.EvnUslugaCommon_pid_Name)
					{
						EvnCombo.setRawValue(data.EvnUslugaCommon_pid_Name);
					} else
					{
						vm.set('EvnUslugaCommon_pid', null);
					}

				}

				// Если есть права на изменение услуги, то назначение должно быть редактируемо
				//PrescrCombo.setDisabled(PrescrCombo.uslugaCombo.isDisabled());



				if ( action == 'edit' )
				{
					if(UslugaPlace_id==1) {
						//Если не известен MedStaffFact_id, ищем по MedPersonal_id и LpuSection_uid
						var msf = form.findField('MedStaffFact_id');
						var mp_id = form.findField('MedPersonal_id').getValue();
						
						if( !Ext6.isEmpty(mp_id) && Ext6.isEmpty(msf.getValue()) ) {
							var index = msf.getStore().findBy(function(rec, id) {
													return ( rec.get('LpuSection_id') == LpuSection_uid && rec.get('MedPersonal_id') == mp_id );
												});
							var record = msf.getStore().getAt(index);
							if ( record ) {
								msf.setValue(record.get('MedStaffFact_id'));
							}
						}
					}
					
					form.isValid();

					//PayType.setDisabled(getRegionNick().inlist(['ekb']) && 'bud' == PayType.getFieldValue('PayType_SysNick'));

					// if (wnd.parentClass != null || (evnUslugaCommon_pid != null && evnUslugaCommon_pid.toString().length > 0) )
					// {
					// 	EvnCombo.disable();
					// }
					// else
					// {
					// 	EvnCombo.focus(true, 250);
					// }
				}

				return true;
			}

		});

		return;
	},


	loadUslugaComplexTariffCombo: function (params, none, binding)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),
			action = vm.get('action'),

			UslugaComplexTariff = form.findField('UslugaComplexTariff_id'),
			UslugaComplexTariff_id = vm.get('UslugaComplexTariff_id'),

			date = params.UslugaComplexTariff_Date,
			dateNow = (date instanceof Date) ? date : (date ? getValidDT(date, '') : new Date()),
			UslugaComplex_Date = dateNow.format('d.m.Y'); // в строковом виде для параметров

		params.UslugaComplexTariff_Date = UslugaComplex_Date;
		params.PayType_id =  isNaN(Number(params.PayType_id)) ? null : Math.floor( Number(params.PayType_id) );
		params.LpuSection_id =  isNaN(Number(params.LpuSection_id)) ? null : Math.floor( Number(params.LpuSection_id) );
		params.UslugaComplex_id =  isNaN(Number(params.UslugaComplex_id)) ? null : Math.floor( Number(params.UslugaComplex_id) );

		if (action === 'add')
		{
			vm.set('EvnUslugaCommon_Kolvo', 1);
			//UslugaComplexTariff_UED.setDisabled(isPackage || !this.allowRayTherapy());
			//UslugaComplexTariff_UED.setDisabled(false);
			vm.set('UslugaComplexTariff_UED', null);
		}

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
						vm.set('EvnUslugaCommon_Price', rec.get('UslugaComplexTariff_Tariff') || 0); //EvnUslugaCommon_Price.setValue(rec.get('UslugaComplexTariff_Tariff') || 0);
					}

					return;
				}
			}.createDelegate(UslugaComplexTariff)

		});

		return;
	},

	//allowRayTherapy: function()
	//{
	// var form = this.FormPanel.getForm();
	// var combo = form.findField('LpuSection_uid');
	// var result = false;
	//
	// if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' && !Ext6.isEmpty(combo.getValue()) ) {
	// 	var index = combo.getStore().findBy(function(rec) {
	// 		return (rec.get(combo.valueField) == combo.getValue());
	// 	});
	//
	// 	if ( index >= 0 && combo.getStore().getAt(index).get('LpuSectionProfile_Code').inlist([ '577', '677', '877' ]) && this.action != 'view' ) {
	// 		result = true;
	// 	}
	// }
	//
	// return result;
	//},

	//onPayTypeChange: function (value, none, binding)
	//{
	//return;
	// if (getRegionNick() == 'buryatiya') {
	// 	var usluga_category_combo = form.findField('UslugaCategory_id');
	// 	usluga_category_combo.lastQuery = "";
	// 	usluga_category_combo.getStore().clearFilter();
	// 	if (record && record.get('PayType_SysNick') == 'oms'){
	// 		usluga_category_combo.setFieldValue('UslugaCategory_SysNick', 'tfoms');
	// 		usluga_category_combo.fireEvent('select', usluga_category_combo, usluga_category_combo.getStore().getAt(usluga_category_combo.getStore().findBy(function(rec) {
	// 			return (rec.get('UslugaCategory_SysNick') == 'tfoms');
	// 		})));
	// 	} else {
	// 		usluga_category_combo.clearValue();
	// 		usluga_category_combo.fireEvent('select', usluga_category_combo, null);
	// 	}
	// }


	setUslugaComplexPartitionCodeList: function(params, none, binding)
	{
		if ( getRegionNick() !== 'ekb')
		{
			return false;
		}

		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			dateObj = Date.parseDate(date, 'd.m.Y'),
			form = this.getView().getForm(),
			vm = this.getViewModel(),
			wndVm = vm.getParent(),

			PayType_id = ! isNaN(Number(params.PayType_id)) ? params.PayType_id : null,
			LpuSection_id = ! isNaN(Number(params.LpuSection_id)) ? params.LpuSection_id : null,

			LpuSection = form.findField('LpuSection_uid'),
			PayType = form.findField('PayType_id');


		var UslugaComplex = form.findField('UslugaComplex_id'),
			isPriem = wndVm.get('isPriem'),
			only351Group = wndVm.get('only351Group'),
			LpuUnitType_Code = wndVm.get('LpuUnitType_Code'),
			parentClass = vm.get('parentClass');



		var lsRec = LpuSection.getStore().getById(LpuSection_id),
			ptRec = PayType.getStore().getById(PayType_id),
			PayType_SysNick =  ptRec ? ptRec.get('PayType_SysNick') : null,
			LpuSectionProfile_SysNick =  ptRec ? ptRec.get('LpuSectionProfile_SysNick') : null;


		var setPriemFilter = (isPriem && ! getUslugaOptions().enable_usluga_section_load && ! getUslugaOptions().enable_usluga_section_load_filter),
			LpuUnitType_Code = lsRec ? lsRec.get('LpuUnitType_Code') : LpuUnitType_Code,
			LpuSection_IsHTMedicalCare = lsRec ? lsRec.get('LpuSection_IsHTMedicalCare') : null;

		if (only351Group)
		{
			UslugaComplex.getStore().getProxy().setExtraParam('UslugaComplexPartition_CodeList', Ext6.util.JSON.encode([351]));

			vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode([351]));

		} else if ( parentClass == 'EvnVizit' || parentClass == 'EvnPL' || parentClass == 'EvnPLStom' )
		{
			var xdate = new Date(2015, 0, 1);

			if ( parentClass == 'EvnPLStom' )
			{
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( dateObj >= xdate ? [303] : [300, 301]));
			}
			else
			{
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [300, 301] ));
			}

			if (PayType_SysNick == 'bud')
			{
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [351] ));
			}
		}
		else
		{

			if ( LpuUnitType_Code == 3 || LpuUnitType_Code == 5 )
			{
				// днев
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [202,203,205,206] ));
			}
			else
			{
				// кругл
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( LpuSection_IsHTMedicalCare == 2 ? [102,103,104,105,106,107] : [102,103,104,105,107] ));
			}

			if (PayType_SysNick == 'bud')
			{
				if (LpuUnitType_Code == 3 || LpuUnitType_Code == 5)
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [252] ));
				} else
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [152] ));
				}
			}

			if (setPriemFilter || LpuSectionProfile_SysNick == 'priem')
			{
				var list = Ext6.util.JSON.decode(vm.get('UCP.UslugaComplexPartition_CodeList')) || [];
				list.push(300, 301);
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( list ));
			}
		}

		if(LpuUnitType_Code != 3 && LpuUnitType_Code != 5 && LpuSection_IsHTMedicalCare == 2 && ! only351Group)
		{
			var list = Ext6.util.JSON.decode(vm.get('UCP.UslugaComplexPartition_CodeList')) || [];

			switch (PayType_SysNick)
			{
				case 'bud':
				case 'fbud':
					list.push(156);
					break;
				case 'oms':
					list.push(106);
					break;
			}
			vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( list ));
		}
	},

	onUslugaPlaceChange: function (value, none, binding)
	{
		var form = this.getView().getForm(),
			vm = this.getViewModel(),
			lpu = form.findField('Lpu_uid'),
			lpuSection = form.findField('LpuSection_uid'),
			medStaffFact = form.findField('MedStaffFact_id'),
			org = form.findField('Org_uid');


		if (getRegionNick() === 'perm')
		{
			if (value == 1) // поле будет скрыто
			{
				vm.set('MedSpecOms_id', null); // зануляем
				if ( ! vm.get('EvnPrescr_id') )
				{
					vm.set('Lpu_uid', null);
					vm.set('Org_uid', null);
				}

			} else
			{
				if ( ! vm.get('EvnPrescr_id') )
				{
					vm.set('LpuSection_uid', null);
					vm.set('MedStaffFact_id', null);
					vm.set( value == 2 ? 'Org_uid' : 'Lpu_uid', null);
				}
			}
		}


		return;
	},

	checkOtherLpu: function(params, none, binding)
	{
		var formPanel = this.getView(),
			vm = this.getViewModel(),
			form = formPanel.getForm(),
			field = form.findField('UslugaComplex_id'),
			UslugaComplex_id = vm.get('UslugaComplex_id'),
			parentClass = vm.get('parentClass'),
			date = params.UslugaComplex_Date,
			dateNow = (date instanceof Date) ? date : (date ? getValidDT(date, '') : new Date()),
			UslugaComplex_Date = dateNow.format('d.m.Y'), // в строковом виде для параметров

			isMinusUslugaField = form.findField('EvnUslugaCommon_IsMinusUsluga'),
			LpuUnitType_SysNick = form.findField('EvnUslugaCommon_pid').getFieldValue('LpuUnitType_SysNick');

		params.Date = UslugaComplex_Date;

		if ( ! params.Lpu_oid )
		{
			return;
		}

		if (getRegionNick() == 'perm' && ! Ext6.isEmpty(params.Lpu_oid) && !Ext6.isEmpty(params.Date) && LpuUnitType_SysNick == 'stac' && parentClass.inlist(['EvnPS','EvnSection']))
		{

			Ext6.Ajax.request({
				url: '/?c=TariffVolumes&m=getTariffClassListByLpu',
				params: params,
				callback: function(options, success, response)
				{
					isMinusUslugaField.hide();

					if (success && ! Ext6.isEmpty(response.responseText))
					{
						var list = Ext6.util.JSON.decode(response.responseText);

						if ( '2015-10PSO'.inlist(list) )
						{
							if ( ! isMinusUslugaField.isVisible())
							{
								isMinusUslugaField.show();
							}

						} else
						{
							vm.set('EvnUslugaCommon_IsMinusUsluga', false);
							isMinusUslugaField.hide();
						}
					}
				}
			});
		} else
		{
			vm.set('EvnUslugaCommon_IsMinusUsluga', false);
			isMinusUslugaField.hide();
		}

		return;
	},

	reloadUslugaComplexCombo: function (params, none, binding)
	{
		var formPanel = this.getView(),
			vm = this.getViewModel(),
			form = formPanel.getForm(),
			field = form.findField('UslugaComplex_id'),
			UslugaComplex_id = vm.get('UslugaComplex_id'),
			UslugaComplex_Name = field.getRawValue(),

			action = vm.get('action'),
			parentClass = vm.get('parentClass'),
			isLoading = vm.get('formIsLoading'),

			date = params.UslugaComplex_Date,
			dateNow = (date instanceof Date) ? date : (date ? getValidDT(date, '') : new Date()),
			UslugaComplex_Date = dateNow.format('d.m.Y'), // в строковом виде для параметров

			PayType = form.findField('PayType_id'),
			LpuUnitType = form.findField('LpuSection_uid').getFieldValue('LpuUnitType_id'),
			
			UslugaCategory = form.findField('UslugaCategory_id'),
			UslugaCategory_SysNick = UslugaCategory.getFieldValue('UslugaCategory_SysNick'),

			changeUslugaComplexPlaceFilter = vm.get('changeUslugaComplexPlaceFilter'),

			disallowedUsluga = [],
			EvnUslugaCommon = form.findField('EvnUslugaCommon_pid'),
			record = EvnUslugaCommon.getStore().getById(params.EvnUsluga_pid),
			data = record ? record.data : {};

		var extraParams = Object.assign(params,
			{
				to: field.to,
				DispClass_id: field.DispClass_id,
				dispOnly: field.dispOnly ? 1 : null,
				nonDispOnly: (! field.dispOnly && field.nonDispOnly) ? 1 : null
			}, params.ucp),

			fieldsToCheckCorrectId = ['LpuSection_id', 'Person_id', 'UslugaCategory_id', 'LpuSectionProfile_id', 'PayType_id']; // в селекте пустой записи может прийти строковый id


		extraParams.ucp = undefined;
		extraParams.UslugaComplex_Date = UslugaComplex_Date;
		extraParams.EvnUsluga_pid = data.Evn_id; // на случай если записи в комбике нет, то услга из приемного и фильтровать не надо (так было на старой форме)
		extraParams.LpuSection_id = changeUslugaComplexPlaceFilter ? extraParams.LpuSection_id : null;

		if (getRegionNick() !== 'ekb')
		{
			extraParams.uslugaCategoryList = UslugaCategory_SysNick ? Ext6.util.JSON.encode([UslugaCategory_SysNick]) : null;
		}
		
		if (getRegionNick() === 'perm')
		{
			extraParams.PayType_id = PayType.getValue();
			extraParams.LpuUnitType_id = LpuUnitType;
		}

		if (data.UslugaComplex_Code)
		{
			disallowedUsluga.push(data.UslugaComplex_Code);
			extraParams.disallowedUslugaComplexCodeList = Ext6.util.JSON.encode(disallowedUsluga);
		}


		Ext6.each(fieldsToCheckCorrectId, function(el) {
			extraParams[el] = isNaN(Number(extraParams[el])) ? null :  Math.floor( Number(extraParams[el]) );
		});



		if (getRegionNick() == 'ekb' && parentClass == 'EvnPLStom')
		{
			var dateX = new Date(2015, 0, 1);
			extraParams.UslugaComplexPartition_CodeList =  Ext6.util.JSON.encode(dateNow >= dateX ? [303] : [300, 301]);
		}

		field.getStore().getProxy().setExtraParams(extraParams);

		// if ( ! ( (extraParams.UslugaCategory_id && extraParams.PayType_id) && (extraParams.UslugaCategory_id > 0 && extraParams.PayType_id > 0) ) )
		// {
		// 	field.getStore().removeAll();
		// 	vm.set('UslugaComplex_id', null);
		//
		// 	return;
		// }

		if (isLoading === true || ! UslugaComplex_id)
		{
			return;
		}

		var queryParam = ( UslugaComplex_id && UslugaComplex_Name) ? {query: UslugaComplex_Name} : {};

		field.getStore().load({
			params: queryParam,
			callback: function (recs, b, s)
			{
				var rec = this.findRecord('UslugaComplex_id', UslugaComplex_id),
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
						//vm.set('LpuSectionProfile_id', LpuSectionProfile_id);
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

	onEvnUslugaCommon_pidChange: function (value, none, binding)
	{
		var formPanel = this.getView(),
			wnd = formPanel.up('window'),
			wndVm = wnd.getViewModel(),
			vm = this.getViewModel(),
			viewData = vm.getData(),
			form = formPanel.getForm(),
			field = form.findField('EvnUslugaCommon_pid'),
			record = field.getStore().getById(value),
			data = record ? record.data : null,
			action = vm.get('action') || 'add',
			rec;


		if ( data && action === 'add')
		{

			vm.set('EvnUslugaCommon_setDate', data.Evn_setDate);
			vm.set('EvnUslugaCommon_setTime', data.Evn_setTime);
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

			if (data.MedStaffFact_id)
			{
				rec = form.findField('MedStaffFact_id').getStore().getById(data.MedStaffFact_id);

				if (rec)
				{
					vm.set('MedStaffFact_id', data.MedStaffFact_id);
				}
			}
		}

	},



	filterMedStaffFact: function (params, none, binding)
	{
		var UserMedStaffFact_id = params.UserMedStaffFact_id || null,
			UserMedStaffFacts = params.UserMedStaffFacts || [],
			EvnUslugaCommon_setDate = Ext6.util.Format.date(params.EvnUslugaCommon_setDate ? params.EvnUslugaCommon_setDate : new Date(), 'd.m.Y'),
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
					onDate: EvnUslugaCommon_setDate
				},

			rec = null;



		if (action.inlist(['add', 'edit']))
		{
			UserMedStaffFact_id ? (MedStaffFactFilterParams.id = UserMedStaffFact_id) : (MedStaffFactFilterParams.ids = UserMedStaffFacts.length > 0 ? UserMedStaffFacts : null);
		}


		if (parentClass.inlist(['EvnPS', 'EvnSection']) && 'kareliya' === getRegionNick())
		{
			MedStaffFactFilterParams.isStac = true;
		}

		MedStaffFactFilterParams.LpuSection_id = (params.LpuSection_id && ! isNaN(params.LpuSection_id ) ) ? params.LpuSection_id : null;


		setMedStaffFactGlobalStoreFilter(MedStaffFactFilterParams);
		form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));



		if ( action.inlist(['add', 'edit']))
		{
			if ( UserMedStaffFact_id  ||  UserMedStaffFacts.length == 1 ) // по идее они не должны поменяться, поэтому поле всегда будет disabled
			{
				form.findField('MedStaffFact_id').disable();

			} else
			{
				//form.findField('MedStaffFact_id').enable();
			}
		}

		rec = MedStaffFact_id ? form.findField('MedStaffFact_id').getStore().findRecord('MedStaffFact_id', MedStaffFact_id) : null;

		vm.set('MedStaffFact_id', UserMedStaffFact_id || ( UserMedStaffFacts.length == 1 ? UserMedStaffFacts[0] : (rec ? MedStaffFact_id : null) ) );

		return true;
	},

	filterLpuSection: function (params, none, binding)
	{
		var UserLpuSection_id = params.UserLpuSection_id,
			UserLpuSections = params.UserLpuSections || [],
			EvnUslugaCommon_setDate = Ext6.util.Format.date(params.EvnUslugaCommon_setDate ? params.EvnUslugaCommon_setDate : new Date(), 'd.m.Y'),
			vm = this.getViewModel(),
			action = vm.get('action'),

			LpuSection_id = vm.get('LpuSection_uid'),

			SectionFilterParams =
				{
					allowLowLevel: 'yes',
					onDate: EvnUslugaCommon_setDate
				},

			form = this.getView().getForm(),
			rec = null;


		if (action.inlist(['add', 'edit']))
		{
			UserLpuSection_id ? (SectionFilterParams.id = UserLpuSection_id) : (SectionFilterParams.ids = UserLpuSections.length > 0 ? UserLpuSections : null);
		}

		setLpuSectionGlobalStoreFilter(SectionFilterParams);
		form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));


		if ( action.inlist(['add', 'edit']))
		{
			if ( UserLpuSection_id  ||  UserLpuSections.length == 1 )
			{
				form.findField('LpuSection_uid').disable();

			} else
			{
				//form.findField('LpuSection_uid').enable();
			}
		}

		rec = LpuSection_id ? form.findField('LpuSection_uid').getStore().findRecord('LpuSection_id', LpuSection_id) : null;

		vm.set('LpuSection_uid', UserLpuSection_id || ( UserLpuSections.length == 1 ? UserLpuSections[0] : (rec ? LpuSection_id : null) ) );

		return true;
	},


	onEvnUslugaCommon_setDateChange: function (value, none, binding)
	{
		// if ( blockedDateAfterPersonDeath('personpanelid', 'EUComEF_PersonInformationFrame', field, newValue, oldValue) )
		// {
		// 	return false;
		// }

		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),

			action = vm.get('action'),
			dateNow = (value instanceof Date) ? value : (value ? getValidDT(value, '') : new Date()),
			UslugaComplex_Date = dateNow.format('d.m.Y'), // в строковом виде для параметров
			parentClass = vm.get('parentClass');


		// Устанавливаем фильтр по дате для услуг
		if (getRegionNick() == 'perm' && action == 'add')
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

		// не тестировано
		if (getRegionNick() == 'ufa' && parentClass.inlist(['EvnPS', 'EvnSection']))
		{
			form.findField('EvnUslugaCommon_disDate').setMinValue(dateNow);
		}

		// if (!Ext6.isEmpty(this.UslugaComplex_Date) && getValidDT(this.UslugaComplex_Date, '00:00') > getValidDT(UslugaComplex_Date, '00:00'))
		// {
		// 	UslugaComplex_Date = this.UslugaComplex_Date;
		// }  непонятно что означает эта дата. возможно если дата задана извне формы, то не надо вообще фильтровать, так как не зависит от этой даты

	},

	setUslugaComplexPartitionCodeList: function(params, none, binding)
	{
		if ( getRegionNick() !== 'ekb')
		{
			return false;
		}

		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			dateObj = Date.parseDate(date, 'd.m.Y'),
			form = this.getView().getForm(),
			vm = this.getViewModel(),
			wndVm = vm.getParent(),

			PayType_id = ! isNaN(Number(params.PayType_id)) ? params.PayType_id : null,
			LpuSection_id = ! isNaN(Number(params.LpuSection_id)) ? params.LpuSection_id : null,

			LpuSection = form.findField('LpuSection_uid'),
			PayType = form.findField('PayType_id');


		var UslugaComplex = form.findField('UslugaComplex_id'),
			isPriem = wndVm.get('isPriem'),
			only351Group = wndVm.get('only351Group'),
			LpuUnitType_Code = wndVm.get('LpuUnitType_Code'),
			parentClass = vm.get('parentClass');



		var lsRec = LpuSection.getStore().getById(LpuSection_id),
			ptRec = PayType.getStore().getById(PayType_id),
			PayType_SysNick =  ptRec ? ptRec.get('PayType_SysNick') : null,
			LpuSectionProfile_SysNick =  ptRec ? ptRec.get('LpuSectionProfile_SysNick') : null;


		var setPriemFilter = (isPriem && ! getUslugaOptions().enable_usluga_section_load && ! getUslugaOptions().enable_usluga_section_load_filter),
			LpuUnitType_Code = lsRec ? lsRec.get('LpuUnitType_Code') : LpuUnitType_Code,
			LpuSection_IsHTMedicalCare = lsRec ? lsRec.get('LpuSection_IsHTMedicalCare') : null;

		if (only351Group)
		{
			UslugaComplex.getStore().getProxy().setExtraParam('UslugaComplexPartition_CodeList', Ext6.util.JSON.encode([351]));

			vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode([351]));

		} else if ( parentClass == 'EvnVizit' || parentClass == 'EvnPL' || parentClass == 'EvnPLStom' )
		{
			var xdate = new Date(2015, 0, 1);

			if ( parentClass == 'EvnPLStom' )
			{
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( dateObj >= xdate ? [303] : [300, 301]));
			}
			else
			{
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [300, 301] ));
			}

			if (PayType_SysNick == 'bud')
			{
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [351] ));
			}
		}
		else
		{

			if ( LpuUnitType_Code == 3 || LpuUnitType_Code == 5 )
			{
				// днев
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [202,203,205,206] ));
			}
			else
			{
				// кругл
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( LpuSection_IsHTMedicalCare == 2 ? [102,103,104,105,106,107] : [102,103,104,105,107] ));
			}

			if (PayType_SysNick == 'bud')
			{
				if (LpuUnitType_Code == 3 || LpuUnitType_Code == 5)
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [252] ));
				} else
				{
					vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( [152] ));
				}
			}

			if (setPriemFilter || LpuSectionProfile_SysNick == 'priem')
			{
				var list = Ext6.util.JSON.decode(vm.get('UCP.UslugaComplexPartition_CodeList')) || [];
				list.push(300, 301);
				vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( list ));
			}
		}

		if(LpuUnitType_Code != 3 && LpuUnitType_Code != 5 && LpuSection_IsHTMedicalCare == 2 && ! only351Group)
		{
			var list = Ext6.util.JSON.decode(vm.get('UCP.UslugaComplexPartition_CodeList')) || [];

			switch (PayType_SysNick)
			{
				case 'bud':
				case 'fbud':
					list.push(156);
					break;
				case 'oms':
					list.push(106);
					break;
			}
			vm.set('UCP.UslugaComplexPartition_CodeList', Ext6.util.JSON.encode( list ));
		}
	},

	equalDates: function ()
	{
		var vm = this.getViewModel();

		vm.set('EvnUslugaCommon_disDate', vm.get('EvnUslugaCommon_setDate'));
		vm.set('EvnUslugaCommon_disTime', vm.get('EvnUslugaCommon_setTime'));

		return true;
	}
});

