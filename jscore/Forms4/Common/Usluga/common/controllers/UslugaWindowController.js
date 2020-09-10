/**
 * Контроллер окна EvnUslugaCommonEditWindow
 */

Ext6.define('usluga.common.controllers.UslugaWindowController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.UslugaWindowController',

	show: function(params)
	{
		var wnd = this.getView();

		if ( ! params || ! params.formParams )
		{
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function()
			{
				wnd.close();
			});
			return false;
		}

		var wndVm = this.getViewModel(),

			formPanel = wnd.down('UslugaEditForm'),
			form = formPanel.getForm(),
			vm = formPanel.getViewModel(),

			//calculateAge = swGetPersonAge(params.Person_Birthday, new Date()) || null,
			//personAge = calculateAge > 0 ? calculateAge : null,
			EvnUslugaCommon_id = (params.formParams.EvnUslugaCommon_id && ! isNaN(params.formParams.EvnUslugaCommon_id) && params.formParams.EvnUslugaCommon_id > 0) ? params.formParams.EvnUslugaCommon_id : null;





		if ( ! params.action )
		{
			params.action = params.formParams.EvnUslugaCommon_id ? 'view' : 'add';
		}

		vm.set('action', params.action);


		wndVm.set('changeUslugaComplexPlaceFilter', (getRegionNick() != 'ekb' || getUslugaOptions().enable_usluga_section_load || getUslugaOptions().enable_usluga_section_load_filter) );
		wndVm.set('EvnClass_SysNick', params.EvnClass_SysNick || 'EvnUslugaCommon');
		wndVm.set('PrescriptionType_Code', params.PrescriptionType_Code || null);
		wndVm.set('UslugaComplex_Date', params.UslugaComplex_Date || null);
		wndVm.set('LpuUnitType_Code', params.LpuUnitType_Code || null);
		wndVm.set('ignorePaidCheck', params.ignorePaidCheck || null);
		wndVm.set('action', params.action);
		wndVm.set('isPriem', params.isPriem || null);

		wndVm.set('parentClass', params.parentClass || "EvnVizit");

		wndVm.set('only351Group', params.only351Group || null);
		wndVm.set('EvnUslugaCommon_id', EvnUslugaCommon_id);



		params.formParams.EvnUslugaCommon_id = null;


		form.findField('EvnUslugaCommon_pid').getStore().loadData(params.parentEvnComboData || []);
		vm.set('EvnCount', form.findField('EvnUslugaCommon_pid').getStore().getCount() );

		form.findField('UslugaExecutionType_id').getStore().clearFilter();

		if (getRegionNick() == 'perm' && wndVm.get('parentClass') != 'EvnPS' && wndVm.get('parentClass') != 'EvnSection') {
			form.findField('UslugaExecutionType_id').getStore().filterBy(function(rec) {
				return rec.get('UslugaExecutionType_id') != 2;
			});
			form.findField('UslugaExecutionReason_id').getStore().filterBy(function(rec) {
				return (rec.get('UslugaExecutionReason_id') == 1 || rec.get('UslugaExecutionReason_id') == 2);
			});
		}


		wnd.submitUrl = params.formUrl || wnd.defaultSubmitUrl;
		wnd.loadUrl = params.formLoadUrl || wnd.defaultLoadUrl;


		wnd.specialDoSave = params.doSave || null;
		wnd.callback = params.callback || Ext6.emptyFn;
		wnd.onHideFn = params.onHide || Ext6.emptyFn;


		vm.set('UCP', { // Часть параметров для загрузки UslugaComplex

			allowedUslugaComplexAttributeList: null,
			allowedUslugaComplexAttributeMethod: 'or',
			disallowedUslugaComplexAttributeList: null,

			allowMorbusVizitCodesGroup88: 0,
			allowMorbusVizitOnly: 0,
			allowNonMorbusVizitOnly: 0,
			ignoreUslugaComplexDate: 0,

			Mes_id: null,
			LpuLevel_Code: null,
			uslugaCategoryList: null,
			uslugaComplexCodeList: null,
			UslugaComplex_2011id: null,
			//personAge: personAge || null,
			notFilterByEvnVizitMes: getRegionNick().inlist(['ekb']) ? (params.notFilterByEvnVizitMes || null) : null,
			MesOldVizit_id: getRegionNick().inlist(['ekb']) ? (params.MesOldVizit_id || null) : null,
			allowDispSomeAdultLabOnly: params.allowDispSomeAdultLabOnly ? 1 : null,
			Sex_Code: params.Sex_Code || null,
			withoutPackage: 1

		});



		var EvnCombo = form.findField('EvnUslugaCommon_pid'),
			LpuCombo = form.findField('Lpu_uid'),
			LpuSection = form.findField('LpuSection_uid'),
			MedStaffFact = form.findField('MedStaffFact_id'),
			OrgCombo = form.findField('Org_uid'),
			UslugaPlace = form.findField('UslugaPlace_id'),
			UslugaComplex = form.findField('UslugaComplex_id'),
			PrescrCombo = form.findField('EvnPrescr_id'),
			PayType = form.findField('PayType_id'),
			DiagSetClass = form.findField('DiagSetClass_id'),
			DiagCombo = form.findField('Diag_id'),

			action = vm.get('action'),
			parentClass = vm.get('parentClass');



		if ( ! getRegionNick().inlist(['ekb']) )
		{
			// https://redmine.swan.perm.ru/issues/16610
			// https://redmine.swan.perm.ru/issues/18276
			// https://redmine.swan.perm.ru/issues/43012
			if ( getRegionNick().inlist([ 'kareliya' ]) )
			{
				vm.set('UCP.disallowedUslugaComplexAttributeList', Ext6.util.JSON.encode([ 'oper', 'vizit' ]) );

				form.findField('UslugaCategory_id').lastQuery = '';
				form.findField('UslugaCategory_id').getStore().filterBy(function(rec)
				{
					return ! (rec.get('UslugaCategory_SysNick').inlist([ 'stomoms', 'stomklass' ]));
				});

			} else if ( getRegionNick().inlist([ 'perm', 'pskov' ]) ) {
				// оказалось такие услуги выбирать можно refs #76264
				// usluga_combo.setDisallowedUslugaComplexAttributeList([ 'vizit' ]);
			} else {
				// для стомат услуг эта форма тоже используется при добавлении услуг в ТАП
				vm.set('UCP.disallowedUslugaComplexAttributeList', Ext6.util.JSON.encode([ 'oper', 'stom', 'vizit' ]) );
			}
		}


		// Для Консультанта убираем все фильтры, кроме consul
		if ( params.formParams.VizitType_SysNick == 'consul' )
		{
			vm.set('UCP.disallowedUslugaComplexAttributeList', null );
			vm.set('UCP.allowedUslugaComplexAttributeList', Ext6.util.JSON.encode([ 'consul' ]) );
		}


		if ( ! PrescrCombo.getValue() && action == 'add' && EvnCombo.getStore().getCount() > 0)
		{
			// UPD пакеты вообще не стали делать, оказалось что некоторые прожекты даже не знали что это было на форме
			// решили что все это уйдет в назначения или типа того, не вникал

			/*
			 При создании событий оказания услуги,
			 у которых родительским событием будет движение или посещение,
			 можно выбрать пакет услуг
			 */
			vm.set('UCP.withoutPackage', 0);
		} else
		{
			// во всех остальных случаях НЕЛЬЗЯ
			vm.set('UCP.withoutPackage', 1);
		}




		switch ( action )
		{
			case 'add':

				form.setValues(params.formParams);


				var LpuSection_id = params.LpuSection_id,
					MedStaffFact_id = params.MedStaffFact_id,

					UslugaCategory = form.findField('UslugaCategory_id'),
					UslugaCategory_SysNick,

					PayType_SysNick = getPayTypeSysNickOms(),
					rec;

				UslugaCategory_SysNick = ( function () {

					UslugaCategory.enable();

					switch(getRegionNick())
					{

						// Для Перми по умолчанию подставляем услуги ГОСТ-2011
						// https://redmine.swan.perm.ru/issues/53028
						case 'perm':
						case 'pskov':
						case 'kareliya':
						case 'adygeya':	
							return 'gost2011';
						case 'ekb':
							return 'tfoms';
						// case 'kareliya':
						// 	UslugaCategory.disable();
						// 	return parentClass == 'PersonDisp' ? 'gost2011' : null;
						default:
							return null;
					}

				}) () || null;



				vm.set('EvnUslugaCommon_pid', params.parentEvnComboData.length > 0 ? params.parentEvnComboData[0].Evn_id : null);

				rec = PayType.getStore().findRecord('PayType_SysNick', PayType_SysNick) || PayType.getStore().getAt(0);

				vm.set('PayType_id', rec ? rec.get('PayType_id') : null );

				if ( ! vm.get('MedStaffFact_id') && (MedStaffFact_id || LpuSection_id) )
				{
					rec = MedStaffFact.getStore().getById(MedStaffFact_id) || MedStaffFact.getStore().findRecord('LpuSection_id', LpuSection_id);

					vm.set('MedStaffFact_id', rec ? rec.get('MedStaffFact_id') : null );

				}

				// UslugaCategory
				if ( UslugaCategory.getStore().getCount() == 1 )
				{
					UslugaCategory.disable();
				}
				else
				{
					UslugaCategory.enable();
				}

				rec = (UslugaCategory.getStore().getCount() == 1 || ! UslugaCategory_SysNick) ? UslugaCategory.getStore().getAt(0) :  UslugaCategory.getStore().findRecord('UslugaCategory_SysNick', UslugaCategory_SysNick);
				vm.set('UslugaCategory_id', rec ? rec.get('UslugaCategory_id') : null);

				break;
		}

		return;
	},

	// onAdd: function ()
	// {
	// 	this.enableEdit(true);
	//
	// 	//form.findField('UslugaComplex_id').getStore().baseParams.Person_id = form.findField('Person_id').getValue();
	// 	var PayType_SysNick = getPayTypeSysNickOms();
	// 	rec = PayType.getStore().findRecord('PayType_SysNick', PayType_SysNick) || PayType.getStore().getAt(0);
	// 	vm.set('PayType_id', rec ? rec.get('PayType_id') : null );
	//
	//
	// 	//PayType.setDisabled(getRegionNick().inlist(['ekb']) && 'bud' == PayType.getFieldValue('PayType_SysNick'));
	//
	//

	//
	// 	var LpuSection_id = params.LpuSection_id,
	// 		MedStaffFact_id = params.MedStaffFact_id,
	// 		set_date = false,
	//
	// 		UslugaCategory = form.findField('UslugaCategory_id'),
	// 		UslugaCategory_SysNick,
	// 		rec;
	//
	//
	//
	//
	//
	// 	// var diag_set_class_id = DiagSetClass.getValue();
	// 	// if ( !Ext6.isEmpty(DiagCombo.getValue()) ) {
	// 	// 	DiagCombo.getStore().load({
	// 	// 		callback: function() {
	// 	// 			DiagCombo.setValue(DiagCombo.getValue());
	// 	// 			DiagCombo.onChange(DiagCombo, DiagCombo.getValue());
	// 	//
	// 	// 			if (DiagSetClass.getStore().findBy(function(rec) { return rec.get('DiagSetClass_id') == diag_set_class_id; }) >= 0) {
	// 	// 				DiagSetClass.setValue(diag_set_class_id);
	// 	// 			}
	// 	// 		},
	// 	// 		params: {where: "where DiagLevel_id = 4 and Diag_id = " + DiagCombo.getValue()}
	// 	// 	});
	// 	// }
	//
	//
	// 	form.findField('LpuSectionProfile_id').disableLoad = true;
	//
	// 	vm.set('EvnUslugaCommon_setDate', new Date().format('d.m.Y'));
	// 	vm.set('EvnUslugaCommon_setTime', new Date().format('H:i'));
	// 	//vm.set('UslugaPlace_id', null);
	//
	//
	// 	vm.set('UslugaPlace_id', null);
	//
	// 	vm.set('EvnUslugaCommon_pid', params.parentEvnComboData.length > 0 ? params.parentEvnComboData[0].Evn_id : null);
	//
	//
	//
	//
	// 	if ( ! vm.get('MedStaffFact_id') )
	// 	{
	// 		rec = MedStaffFact.getStore().getById(MedStaffFact_id) || MedStaffFact.getStore().findRecord('LpuSection_id', LpuSection_id);
	//
	// 		vm.set('MedStaffFact_id', rec ? rec.get('MedStaffFact_id') : null );
	//
	// 	}
	//
	// 	// form.findField('LpuSectionProfile_id').disableLoad = false;
	// 	// form.findField('LpuSectionProfile_id').loadStore();
	//

	// },



	// prescrComboLoadFromAdd: function ()
	// {
	// 	// vm.set('PLP', { // PrescrLoadParams
	// 	// 	PrescriptionType_Code: params.PrescriptionType_Code || null,
	// 	// 	withoutEvnDirection: 1
	// 	// });
	// 	//reloadEvnPrescrCombo: {plp: '{PLP}', EvnPrescr_pid: '{EvnUslugaCommon_pid}'}
	//
	// 	// if ( EvnCombo.getStore().getCount() > 0 )
	// 	// {
	// 	// 	EvnCombo.setValue(EvnCombo.getStore().getAt(0).get('Evn_id'));
	// 	//
	// 	// 	PrescrCombo.setPrescriptionTypeCode(wnd.PrescriptionType_Code);
	// 	// 	PrescrCombo.getStore().baseParams.withoutEvnDirection = wnd.withoutEvnDirection;
	// 	// 	PrescrCombo.getStore().baseParams.EvnPrescr_pid = EvnCombo.getStore().getAt(0).get('Evn_id');
	// 	// 	UslugaComplex.getStore().baseParams.LpuSection_pid = LpuSection_id;
	// 	// 	PrescrCombo.enable();
	// 	// } else
	// 	// {
	// 	// 	UslugaComplex.getStore().baseParams.LpuSection_pid = null;
	// 	// 	set_date = true;
	// 	// 	PrescrCombo.disable();
	// 	// }
	//
	//
	// 	// if ( PrescrCombo.getValue() )
	// 	// {
	// 	// 	PrescrCombo.getStore().baseParams.newEvnPrescr_id = PrescrCombo.getValue();
	// 	// 	// при выполнении назначения с оказанием услуги
	// 	// 	// нужно автоматически подставлять совпадающую по эталонным полям услугу,
	// 	// 	// на комбо услуг накладывать дополнительный фильтр по атрибуту услуги соответственно типу назначения
	// 	// 	PrescrCombo.getStore().load({
	// 	// 		callback: function(){
	// 	// 			// чтобы НЕ дать возможность выбрать другое назначение
	// 	// 			PrescrCombo.hasLoaded = true;
	// 	// 			PrescrCombo.setValue(PrescrCombo.getValue());
	// 	// 			index = PrescrCombo.getStore().findBy(function(rec) {
	// 	// 				return (rec.get(PrescrCombo.valueField) == PrescrCombo.getValue());
	// 	// 			});
	// 	// 			var rec = PrescrCombo.getStore().getAt(index);
	// 	// 			if (rec) {
	// 	// 				/*if (rec.get('EvnPrescr_setDate')) {
	// 	// 					form.findField('EvnUslugaCommon_setDate').setValue(rec.get('EvnPrescr_setDate'));
	// 	// 				} else {
	// 	// 					form.findField('EvnUslugaCommon_setDate').setValue(getGlobalOptions().date);
	// 	// 				}*/
	// 	// 				form.findField('EvnUslugaCommon_setDate').setValue(getGlobalOptions().date);
	// 	// 				form.findField('EvnUslugaCommon_setDate').fireEvent('change', form.findField('EvnUslugaCommon_setDate'), form.findField('EvnUslugaCommon_setDate').getValue());
	// 	//
	// 	// 				//если услуга добавляется по назначению, то
	// 	// 				//если ЛПУ назначения и места выполнения равны
	// 	// 				if ( rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
	// 	// 					//указываем место выполнение Отделение ЛПУ
	// 	// 					UslugaPlace.setValue(1);
	// 	// 					UslugaPlace.fireEvent('change', UslugaPlace, 1);
	// 	//
	// 	// 					index = LpuSection.getStore().findBy(function(rec) {
	// 	// 						return ( rec.get('LpuSection_id') == LpuSection_id );
	// 	// 					});
	// 	//
	// 	// 					if ( index >= 0 ) {
	// 	// 						LpuSection.setValue(LpuSection_id);
	// 	// 						this.isPriem = (LpuSection.getFieldValue('LpuSectionProfile_SysNick') == 'priem');
	// 	// 						LpuSection.fireEvent('change', LpuSection, LpuSection_id);
	// 	// 					}
	// 	// 				} else {
	// 	// 					//указываем место выполнение Другое ЛПУ
	// 	// 					UslugaPlace.setValue(2);
	// 	// 					LpuSection.setValue(null);
	// 	// 					UslugaPlace.fireEvent('change', UslugaPlace, 2);
	// 	// 					LpuCombo.getStore().load({
	// 	// 						callback: function(records, options, success) {
	// 	// 							if (success && records.length>0) {
	// 	// 								LpuCombo.setValue(records[0].get(LpuCombo.valueField));
	// 	// 							} else {
	// 	// 								LpuCombo.setValue(null);
	// 	// 							}
	// 	// 						},
	// 	// 						params: {
	// 	// 							Lpu_oid: getGlobalOptions().lpu_id,
	// 	// 							OrgType: 'lpu'
	// 	// 						}
	// 	// 					});
	// 	// 				}
	// 	// 			}
	// 	// 			PrescrCombo.fireEvent('change', PrescrCombo, PrescrCombo.getValue());
	// 	// 		},
	// 	// 		params: {
	// 	// 			EvnPrescr_id: PrescrCombo.getValue()
	// 	// 		}
	// 	// 	});
	// 	// }
	// 	// else {
	// 	// 	if ( parentClass != 'EvnPLStom' )
	// 	// 	{
	// 	// 		if (EvnCombo.getStore().getAt(0)) {
	// 	// 			EvnCombo.fireEvent('change', EvnCombo, EvnCombo.getStore().getAt(0).get('Evn_id'), 0);
	// 	// 		}
	// 	// 	}
	// 	// }
	// },
	//
	// onEditAndView: function ()
	// {
	//
	// },

	onSprLoad: function (args) // используется в baseForm после загрузки всех справочников через getDataAll
	{

		var wnd = this.getView(),
			wndVm = this.getViewModel(),
			formPanel = wnd.down('UslugaEditForm'),
			vm = formPanel.getViewModel(),
			form = formPanel.getForm(),
			EvnUslugaCommon_id,
			params = args[0];



		this.show(args[0]);

		var EvnUslugaCommon_id = wndVm.get('EvnUslugaCommon_id');



		if ( ! isNaN(EvnUslugaCommon_id) && EvnUslugaCommon_id > 0)
		{
			vm.set('formIsLoading', true);
			vm.set('EvnUslugaCommon_id', EvnUslugaCommon_id);
		}

		return;
	},


	doSave: function (options)
	{
		var wnd = this.getView(),
			wndVm = this.getViewModel(),
			formPanel = wnd.down('UslugaEditForm'),
			form = formPanel.getForm(),
			vm = formPanel.getViewModel(),
			controller = this,

			EvnUslugaCommon_pid = vm.get('EvnUslugaCommon_pid'),
			ignorePaidCheck = wndVm.get('ignorePaidCheck'),
			parentClass = vm.get('parentClass'),
			action = vm.get('action');


		if ( action == 'view' )
		{
			return false;
		}

		options = options || {};
		options.ignoreErrors = options.ignoreErrors || [];

		var loadMask = new Ext6.LoadMask(wnd, {msg: "Сохранение..."});
		loadMask.show();



		if ( ! form.isValid() )
		{
			if (IS_DEBUG)
			{
				form.getFields().filterBy(function(field){console.log(field); console.log(field.name + ' = ' + field.validate())})
			}

			loadMask.hide();

			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {

				},
				icon: Ext6.Msg.WARNING,
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				title: 'Проверка данных формы'
			});
			return false;
		}


		if ( (parentClass == 'EvnVizit' || parentClass == 'EvnPS') && ! EvnUslugaCommon_pid && getRegionNick().inlist(['perm', 'kareliya']))
		{
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
// 					this.formStatus = 'edit';
					loadMask.hide();
					form.findField('EvnUslugaCommon_pid').focus(true);
				},
				icon: Ext6.Msg.WARNING,
				msg: langs('Не выбрано отделение (посещение)'),
				title: 'Проверка данных формы'
			});
			return false;
		}

		var EvnUslugaCommon_Price = Number(vm.get('EvnUslugaCommon_Price')),

			MedPersonal_id = vm.get('MedPersonal_id'),
			MedPersonal_Fin,
			MedStaffFact_id = vm.get('MedStaffFact_id'),
			MedStaffFact = form.findField('MedStaffFact_id'),
			params = new Object(),
			PayType_SysNick = form.findField('PayType_id').getFieldValue('PayType_SysNick'),
			UslugaComplex_id =  vm.get('UslugaComplex_id'),
			UslugaComplex_Code = form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code'),
			UslugaComplex_Name = form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name');

		if ( ! MedPersonal_id && MedStaffFact_id)
		{
			vm.set('MedPersonal_id', MedStaffFact.getFieldValue('MedPersonal_id'));
		}


		var set_date = vm.get('EvnUslugaCommon_setDate'),
			set_time = vm.get('EvnUslugaCommon_setTime'),
			dis_date = vm.get('EvnUslugaCommon_disDate'),
			dis_time = vm.get('EvnUslugaCommon_disTime');

		if ( ! Ext6.isEmpty(dis_date) )
		{
			var setDateStr = Ext6.util.Format.date(set_date, 'Y-m-d')+' '+(Ext6.isEmpty(set_time)?'00:00':set_time);
			var disDateStr = Ext6.util.Format.date(dis_date, 'Y-m-d')+' '+(Ext6.isEmpty(dis_time)?'00:00':dis_time);

			if (Date.parseDate(setDateStr, 'Y-m-d H:i') > Date.parseDate(disDateStr, 'Y-m-d H:i'))
			{
				Ext6.MessageBox.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						loadMask.hide();
						form.findField('EvnUslugaCommon_setDate').focus(false)
					},
					icon: Ext6.Msg.WARNING,
					msg: langs('Дата окончания выполнения услуги не может быть меньше даты начала выполнения услуги.'),
					title: langs('Ошибка')
				});
				return false;
			}
		}

		if ( ! Ext6.isEmpty(vm.get('UslugaComplexTariff_UED')) )
		{
			EvnUslugaCommon_Price = EvnUslugaCommon_Price + Number(vm.get('UslugaComplexTariff_UED'));
		}

		MedPersonal_Fin = form.findField('MedStaffFact_id').getFieldValue('MedPersonal_Fin');


		var PersonData = vm.get('PersonData') || {},
			SexCode = PersonData.Sex_id,
			Person_Birthday = PersonData.Person_Birthday,

			diag_record = form.findField('Diag_id').getStore().getById(form.findField('Diag_id').getValue()),

			person_age = swGetPersonAge(Person_Birthday, set_date),
			person_age_month = swGetPersonAgeMonth(Person_Birthday, set_date),
			person_age_day = swGetPersonAgeDay(Person_Birthday, set_date);

		// логика для екб
		if ( getRegionNick() == 'ekb' && diag_record)
		{
			var err = (function () {

				if ( person_age == -1 || person_age_month == -1 || person_age_day == -1 )
				{
					return {msg: langs('Ошибка при определении возраста пациента')};
				}

				if ( ! SexCode || ! (SexCode.toString().inlist([ '1', '2' ])) )
				{
					return {msg: langs('Не указан пол пациента')};
				}

				if ( ! Ext6.isEmpty(diag_record.get('Sex_Code')) && Number(diag_record.get('Sex_Code')) != Number(SexCode) )
				{
					return {warningMsg: langs('Выбранный диагноз не соответствует полу пациента'), fieldName: 'Diag_id'};
				}

				if ( PayType_SysNick == 'oms' )
				{
					if ( form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_Code').toString().inlist([ '658', '684', '558', '584' ]) ) {
						if ( diag_record.get('DiagFinance_IsHealthCenter') === false ) {
							return {warningMsg: langs('Диагноз не оплачивается для Центров здоровья'), fieldName: 'Diag_id'};
						}
					} else if ( diag_record.get('DiagFinance_IsOms') === false ) {
						return {warningMsg: langs('Данный диагноз не подлежит оплате в системе ОМС. Смените вид оплаты.'), fieldName: 'Diag_id'};
					}
				}
				if (
					(person_age < 18 && Number(diag_record.get('PersonAgeGroup_Code')) == 1) ||
					((person_age > 19 || (person_age == 18 && person_age_month >= 6)) && Number(diag_record.get('PersonAgeGroup_Code')) == 2) ||
					((person_age > 0 || (person_age == 0 && person_age_month >= 3)) && Number(diag_record.get('PersonAgeGroup_Code')) == 3) ||
					(person_age_day >= 28 && Number(diag_record.get('PersonAgeGroup_Code')) == 4) ||
					(person_age >= 4 && Number(diag_record.get('PersonAgeGroup_Code')) == 5)
				) {
					return {warningMsg: langs('Выбранный диагноз не соответствует возрасту пациента'), fieldName: 'Diag_id'};
				}

				return {};

			}) () || {};


			if (err.warningMsg || err.msg) {
				loadMask.hide();

				if (!options.ignoreErrors.includes(err.warningMsg.toString())) {
					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if ('yes' == buttonId) {
								options.ignoreErrors.push(err.warningMsg);
								controller.doSave(options);
							} else if (err.fieldName && form.findField(err.fieldName)) {
								form.findField(err.fieldName).markInvalid(err.warningMsg);
								form.findField(err.fieldName).focus(true);
							}
						},
						icon: Ext6.Msg.WARNING,
						msg: '' + err.warningMsg + '<br>Продолжить сохранение?',
						title: langs('Предупреждение')
					});
					return false;
				} else {
					Ext6.Msg.alert(langs('Ошибка'), err.msg || err.toString());
					return false;
				}
			}
		}


		params.EvnUslugaCommon_Price = EvnUslugaCommon_Price;
		var summa = vm.get('EvnUslugaCommon_Summa') || Number( EvnUslugaCommon_Price * vm.get('EvnUslugaCommon_Kolvo') );

		params.EvnUslugaCommon_Summa = summa.toFixed(2);

		params.ignoreParentEvnDateCheck = options.ignoreParentEvnDateCheck === 1 ? 1 : 0;
		params.ignorePaidCheck = ignorePaidCheck;



		form.submit({
			params: params,
			url: wnd.submitUrl,
			failure: function(form, reply)
			{
				loadMask.hide();

				if ( reply.result )
				{
					if (reply.result.Alert_Msg)
					{
						Ext6.Msg.show({
							buttons: Ext6.Msg.YESNO,
							fn: function(buttonId, text, obj)
							{
								if ( buttonId == 'yes' )
								{
									if (reply.result.Error_Code == 109)
									{
										options.ignoreParentEvnDateCheck = 1;
									}

									wnd.getController().doSave(options);
								}
							},
							icon: Ext6.MessageBox.QUESTION,
							msg: reply.result.Alert_Msg,
							title: langs('Продолжить сохранение?')
						});
					} else if ( reply.result.Error_Msg )
					{
						Ext6.Msg.alert(langs('Ошибка'), reply.result.Error_Msg);
					}
					else
					{
						Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
					}
				}
			},

			success: function(form, reply)
			{
				loadMask.hide();

				if ( reply.result && reply.result.EvnUslugaCommon_id > 0 )
				{
					form.findField('EvnUslugaCommon_id').setValue(reply.result.EvnUslugaCommon_id);
					formPanel.getController().linkFilesToEvn(reply.result.EvnUslugaCommon_id);

					// автосохраняем документ
					controller.saveDocument({
						Evn_id: reply.result.EvnUslugaCommon_id,
						onSaveEvnXml: options.onSaveEvnXml
					});
					controller.linkEvnAggRecords(reply.result.EvnUslugaCommon_id);


					if ( options && typeof options.openChildWindow == 'function' && action == 'add' )
					{
						options.openChildWindow();
					}
					else
					{
						if (reply.result.Warning_Msg)
						{
							// При успехе все равно приходит сообщение что нет тарифа, не знаю надо или не надо показывать
							//Ext6.Msg.alert(langs('Внимание'), reply.result.Warning_Msg[0]);
						}

						var data = {},
							EvnUslugaCommon_setTime = vm.get('EvnUslugaCommon_setTime');

						if ( ! EvnUslugaCommon_setTime || EvnUslugaCommon_setTime.length == 0 )
						{
							EvnUslugaCommon_setTime = '00:00';
						}

						data.evnUslugaData = {
							'accessType': 'edit',
							'EvnClass_SysNick': 'EvnUslugaCommon',
							'EvnUsluga_Kolvo': vm.get('EvnUslugaCommon_Kolvo'),
							'EvnUsluga_id': vm.get('EvnUslugaCommon_id'),
							'EvnUsluga_Price': EvnUslugaCommon_Price,
							'EvnUsluga_setDate': vm.get('EvnUslugaCommon_setDate'),
							'EvnUsluga_setTime': EvnUslugaCommon_setTime,
							'EvnUsluga_Summa': params.EvnUslugaCommon_Summa, //Number(EvnUslugaCommon_Price * form.findField('EvnUslugaCommon_Kolvo').getValue()).toFixed(2),
							'PayType_id': vm.get('PayType_id'),
							'PayType_SysNick': PayType_SysNick,
							'Usluga_Code': UslugaComplex_Code,
							'Usluga_Name': UslugaComplex_Name,
							'MedStaffFact_id': MedStaffFact_id,
							'MedPersonal_Fin': MedPersonal_Fin
						};


						if ( options.print == true )
						{
							wnd.doPrint(true);

							if ( action == 'add' )
							{
								wnd.callback(data);
								wnd.close();
							}
						}
						else
						{
							wnd.callback(data);
							if (typeof options.onSaveEvnXml != 'function') {
								wnd.close();
							}
						}
					}
				}
				else
				{
					wnd.callback();
					wnd.close();
				}
			}
		});

		return true;
	},

	saveDocument: function(options)
	{
		var wnd = this.getView(),
			EvnXmlPanel = wnd.down('evnxmlitemspanel'),
			me = EvnXmlPanel.editorPanel;

		if ( ! options.Evn_id || EvnXmlPanel.wasOpened !== true)
		{
			return;
		}

		var params = {
			EvnXml_id: me.params.EvnXml_id,
			Evn_id: options.Evn_id,
			XmlType_id: me.params.XmlType_id,
			XmlTemplate_id: me.params.XmlTemplate_id,
			XmlTemplate_HtmlTemplate: me.getTemplate(),
			EvnXml_Data: Ext6.JSON.encode(me.xmlData)
		};

		// сохранение вызовется при закры
		Ext6.Ajax.request({
			url: '/?c=EvnXml6E&m=saveEvnXml',
			params: params,
			success: function(response) {
				if (response && response.responseText) {
					var result = Ext6.util.JSON.decode(response.responseText);
					if (result.success && result.EvnXml_id) {
						if (typeof options.onSaveEvnXml == 'function') {
							options.onSaveEvnXml(result.EvnXml_id);
						}
					}
				}
			},
			failure: function(response) {}
		});

		return;
	},


	init: function ()
	{

		var uslugaCategoryParams = null;
		// switch (getRegionNick()) {
		// 	case 'kz':
		// 		uslugaCategoryParams = {params: {where: "where UslugaCategory_SysNick in ('classmedus')"}};
		// 		break;
		// 	case 'kaluga':
		// 		uslugaCategoryParams = {params: {where: "where UslugaCategory_SysNick in ('gost2011', 'lpusectiontree')"}};
		// 		break;
		// }

		// fieldLabel: langs('Категория услуги'),
		// hiddenName: 'UslugaCategory_id',
		// loadParams: uslugaCategoryParams,
		return;

	},

	doPrint: function(uslugaIsSaved)
	{
		var wnd = this.getView(),
			formPanel = wnd.down('UslugaEditForm'),
			vm = formPanel.getViewModel(),

			action = vm.get('action');


		var params = {};

		if ( action.inlist([ 'add', 'edit' ]) && ! uslugaIsSaved )
		{
			//this.doSave({
			//	print: true
			//});

			//return false;
		}

		params.object =	'EvnUslugaCommon';
		params.object_id = 'EvnUslugaCommon_id';
		params.object_value	=  vm.get('EvnUslugaCommon_id');
		params.view_section = 'main';

		Ext6.Ajax.request({
			failure: function(response, options)
			{
				//loadMask.hide();
				Ext6.Msg.alert(langs('Ошибка'), langs('При печати услуги произошла ошибка.'));
			},
			params: params,
			success: function(response, options) {

				//_this.formStatus = 'edit';

				if ( response.responseText )
				{
					var result  = Ext6.util.JSON.decode(response.responseText);
					if (result.html)
					{
						var id_salt = Math.random(),
							win_id = 'printEvent' + Math.floor(id_salt*10000),
							win = window.open('', win_id);

						win.document.write('<html><head><title>Печатная форма</title><link href='+ id_salt +'"/css/emk.css?" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'+ result.html +'</body></html>');

					} else
					{
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function() {
								//_this.formStatus = 'edit';
							},
							icon: Ext6.Msg.WARNING,
							msg: langs('Не удалось получить содержание услуги.'),
							title: 'Проверка данных формы'
						});
						return false;
					}
				} else {
					Ext6.Msg.show({
						buttons: Ext6.Msg.OK,
						fn: function() {
							//_this.formStatus = 'edit';
						},
						icon: Ext6.Msg.WARNING,
						msg: langs('Ошибка при печати услуги.'),
						title: 'Проверка данных формы'
					});
					return false;
				}

				//loadMask.hide();


			}.createDelegate(this),
			url: '/?c=Template&m=getEvnForm'
		});
	},

	onMenuClick: function(panel, rowIndex, colIndex, item, e, record)
	{
		if(panel.grid.threeDotMenu)
		{
			var menu = panel.grid.threeDotMenu,
				position = e.getXY();

			menu.selRecord = record;
			menu.controller = this;

			e.stopEvent();
			menu.showAt(position);
		}

		return;
	},

	openEvnAggEditWindow: function (btn, e) // сохранять осложнение до сохранения услуги на общей услуге
	{
		var wnd = this.getView(),
			formPanel = wnd.down('UslugaEditForm'),
			form = formPanel.getForm(),
			gridPanel = wnd.down('EvnAggGrid'),
			vm = formPanel.getViewModel(),
			formAction = vm.get('action'),
			data = form.getValues(),
			EvnAgg_id,
			action = 'add',
			rec;

		if (action === 'add')
		{
			if ( ! (data.EvnUslugaCommon_setDate && data.EvnUslugaCommon_setTime && data.Person_id && data.PersonEvn_id) )
			{
				return;
			}
		}

		var params = {
			EvnAgg_pid: data.EvnUslugaCommon_id || null,
			Person_id: data.Person_id,
			PersonEvn_id: data.PersonEvn_id,
			Server_id: data.Server_id,
			EvnAgg_setDate:data.EvnUslugaCommon_setDate,
			EvnAgg_setTime: data.EvnUslugaCommon_setTime
		};


		if (btn.up('menu'))
		{
			rec = btn.up('menu').selRecord;
			if (rec)
			{
				EvnAgg_id = rec ? rec.get('EvnAgg_id') : null;
				action = 'edit';

				Ext6.apply(params, rec.data);
			}
		}


		getWnd('EvnAggEditWindow').show({
			EvnAgg_id: EvnAgg_id,
			action: action,
			parentAction: formAction,
			formParams: params,
			callback: function (data)
			{

				if ( ! data || ! data.EvnAggData )
				{
					return false;
				}

				if (formAction === 'add')
				{
					if (action === 'edit' && rec)
					{
						// отредактировать запись
						rec.set(data.EvnAggData[0]);
					} else
					{
						gridPanel.getStore().loadData(data.EvnAggData, true /*to append*/)
					}

				} else {
					gridPanel.getStore().reload();
				}

				return;
			}});

		return;
	},

	linkEvnAggRecords: function (EvnUslugaCommon_id)
	{
		if ( ! EvnUslugaCommon_id )
		{
			return;
		}

		var wnd = this.getView(),
			formPanel = wnd.down('form'),
			vm = formPanel.getViewModel(),
			gridPanel = wnd.down('EvnAggGrid'),
			action = vm.get('action');

		if ( action !== 'add' )
		{
			return;
		}

		gridPanel.getStore().each( rec => rec.save({params:{EvnAgg_pid: EvnUslugaCommon_id}}) );

		return;
	},

	uploadFiles: function (el, value)
	{
		var wnd = this.getView(),

			form = wnd.down('FilesUploadForm'),
			fileVm = form.getViewModel(),
			filesGrid = wnd.down('EvnMediaGrid'),
			uslugaPanel = wnd.down('UslugaEditForm'),
			uslugaForm = uslugaPanel.getForm(),
			vm = uslugaPanel.getViewModel(),
			editable = fileVm.get('editable'),
			Evn_id = uslugaForm.findField('EvnUslugaCommon_id').getValue();


		if(form.isValid() && editable)
		{
			form.submit({
				url: '/?c=EvnMediaFiles&m=uploadFile',
				params: {saveOnce: true, Evn_id: Evn_id},
				waitMsg: 'Загрузка...',
				failure: function(fp, reply)
				{
					var fileNames = [],
						data = null;

					if (reply.result.data)
					{
						data = Ext6.JSON.decode(reply.result.data);

						if (Evn_id)
						{
							filesGrid.getStore().load({params: {Evn_id: Evn_id}})
						} else if (data && data.length > 0)
						{
							filesGrid.getStore().loadData(data, true);
						}

					}

					if (reply.result.EvnMediaDataIds)
					{
						var EvnMediaDataIds = vm.get('EvnMediaDataIds'),
							EvnMediaDataIdsFromLoad = Ext6.JSON.decode(reply.result.EvnMediaDataIds);

						EvnMediaDataIds = (typeof EvnMediaDataIds == 'object' && EvnMediaDataIds !== null) ? EvnMediaDataIds : [];

						vm.set('EvnMediaDataIds', Array.prototype.concat(EvnMediaDataIds, EvnMediaDataIdsFromLoad));
					}


					if (reply.result.Error_Msg)
					{
						var errors = reply.result.Error_Msg.split('|'),
							Error_Msg = '';

						Ext6.each(errors, function(el) {

							Error_Msg += el.replace('(.*)', '<p>$1</p>');
						});

						Ext6.Msg.alert('Ошибка', reply.result.Error_Msg);
					}  else if (reply.result.Warning_Msg)
					{
						Ext6.Msg.alert('Ошибка', reply.result.Warning_Msg);
					} else
					{
						Ext6.Msg.alert('Ошибка', 'Неизвестная ошибка');
					}


					return;
				},
				success: function(fp, reply)
				{
					var fileNames = [],
						data = null;

					if (reply.result.data)
					{
						data = Ext6.JSON.decode(reply.result.data);

						if (data && data.length > 0)
						{
							Ext6.each(data, function (el)
							{
								fileNames.push('"' + el.orig_name + '"');
							});

							if (fileNames.length > 1)
							{
								Ext6.Msg.alert('Успешная загрузка', 'Файлы ' + fileNames.join(', ') + ' были загружены');
							} else
							{
								Ext6.Msg.alert('Успешная загрузка', 'Файл ' + fileNames[0] + ' был загружен');
							}
						}
					}

					if (Evn_id)
					{
						filesGrid.getStore().load({params: {Evn_id: Evn_id}})
					} else if (data && data.length > 0)
					{
						filesGrid.getStore().loadData(data, true);
					}

					if (reply.result.EvnMediaDataIds)
					{
						var EvnMediaDataIds = vm.get('EvnMediaDataIds'),
							EvnMediaDataIdsFromLoad = Ext6.JSON.decode(reply.result.EvnMediaDataIds);

						EvnMediaDataIds = (typeof EvnMediaDataIds == 'object' && EvnMediaDataIds !== null) ? EvnMediaDataIds : [];

						vm.set('EvnMediaDataIds', Array.prototype.concat(EvnMediaDataIds, EvnMediaDataIdsFromLoad));
					}

					return;
				}
			});
		}
	}
});