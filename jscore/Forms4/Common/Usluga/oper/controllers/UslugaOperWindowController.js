Ext6.define('usluga.oper.controllers.UslugaOperWindowController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.UslugaOperWindowController',

	uploadFiles: function (el, value)
	{
		var wnd = this.getView(),

			form = wnd.down('FilesUploadForm'),
			filesGrid = wnd.down('EvnMediaGrid'),
			uslugaPanel = wnd.down('UslugaOperEditForm'),
			uslugaForm = uslugaPanel.getForm(),
			vm = uslugaPanel.getViewModel(),
			Evn_id = uslugaForm.findField('EvnUslugaOper_id').getValue();


		if(form.isValid())
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
	},

	onSprLoad: function (args) // используется в baseForm после загрузки всех справочников через getDataAll
	{

		var wnd = this.getView(),
			wndVm = this.getViewModel(),
			formPanel = wnd.down('UslugaOperEditForm');
		// Нет формы - нет проблем
		if(!formPanel)
			return;

		var vm = formPanel.getViewModel(),
			form = formPanel.getForm(),
			EvnUslugaOper_id,
			params = args[0];



		this.show(args[0]);

		var EvnUslugaOper_id = wndVm.get('EvnUslugaOper_id');



		if ( ! isNaN(EvnUslugaOper_id) && EvnUslugaOper_id > 0)
		{
			vm.set('formIsLoading', true);
			vm.set('EvnUslugaOper_id', EvnUslugaOper_id);
		}

		return;
	},

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

			formPanel = wnd.down('UslugaOperEditForm'),
			form = formPanel.getForm(),
			vm = formPanel.getViewModel(),

			//calculateAge = swGetPersonAge(params.Person_Birthday, new Date()) || null,
			//personAge = calculateAge > 0 ? calculateAge : null,
			EvnUslugaOper_id = (params.formParams.EvnUslugaOper_id && ! isNaN(params.formParams.EvnUslugaOper_id) && params.formParams.EvnUslugaOper_id > 0) ? params.formParams.EvnUslugaOper_id : null;


		if ( ! params.action )
		{
			params.action = params.formParams.EvnUslugaOper_id ? 'view' : 'add';
		}

		vm.set('action', params.action);

		wndVm.set('EvnUslugaOper_setDate', params.formParams.EvnUslugaOper_setDate || null);
		wndVm.set('LpuSection_id', params.formParams.LpuSection_id || null);
		wndVm.set('OperBrig', params.formParams.OperBrig || null);
		wndVm.set('LpuUnitType_Code', params.LpuUnitType_Code || null);
		wndVm.set('ignorePaidCheck', params.ignorePaidCheck || null);
		wndVm.set('useCase', params.useCase || null);
		wndVm.set('action', params.action);

		wndVm.set('parentClass', params.parentClass || "EvnVizit");

		wndVm.set('only351Group', params.only351Group || null);
		wndVm.set('EvnUslugaOper_id', EvnUslugaOper_id);



		params.formParams.EvnUslugaOper_id = null;


		form.findField('EvnUslugaOper_pid').getStore().loadData(params.parentEvnComboData || []);
		vm.set('EvnCount', form.findField('EvnUslugaOper_pid').getStore().getCount() );

		form.findField('UslugaExecutionType_id').getStore().clearFilter();

		if (getRegionNick() == 'perm' && wndVm.get('parentClass') != 'EvnPS' && wndVm.get('parentClass') != 'EvnSection') {
			form.findField('UslugaExecutionType_id').getStore().filterBy(function(rec) {
				return rec.get('UslugaExecutionType_id') != 2;
			});
			form.findField('UslugaExecutionReason_id').getStore().filterBy(function(rec) {
				return (rec.get('UslugaExecutionReason_id') == 1 || rec.get('UslugaExecutionReason_id') == 2);
			});
		}

		wnd.callback = params.callback || Ext6.emptyFn;
		wnd.onHideFn = params.onHide || Ext6.emptyFn;

		vm.set('UCP', { // Часть параметров для загрузки UslugaComplex

			allowedUslugaComplexAttributeList: Ext6.JSON.encode(['oper']),
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
			withoutPackage: 1

		});




		var EvnCombo = form.findField('EvnUslugaOper_pid'),
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



		// if ( getRegionNick().inlist([ 'astra', 'ufa' ]) )
		// {
		// 	base_form.findField('OperDiff_id').getStore().filterBy(function(rec)
		// 	{
		// 		return (
		// 			(getRegionNick() == 'ufa' && rec.get('OperDiff_Code').toString().inlist([ '0','1','2','3','4','5' ]))
		// 			||
		// 			(getRegionNick() == 'astra' && rec.get('OperDiff_Code').toString().inlist([ '0','1','2','3' ]))
		// 		);
		// 	});
		// }



		switch ( action )
		{
			case 'add':

				form.setValues(params.formParams);

				vm.set('OperDiff_id', 5);


				var LpuSection_id = params.LpuSection_id,
					MedStaffFact_id = params.MedStaffFact_id,

					UslugaCategory = form.findField('UslugaCategory_id'),
					UslugaCategory_SysNick,

					PayType_SysNick = getPayTypeSysNickOms(),
					rec;

				// PayType
				rec = PayType.getStore().findRecord('PayType_SysNick', PayType_SysNick) || PayType.getStore().getAt(0);
				//pay_type_combo.setDisabled(getRegionNick().inlist(['ekb']) && 'bud' == pay_type_combo.getFieldValue('PayType_SysNick'));


				vm.set('PayType_id', rec ? rec.get('PayType_id') : null );
				// PayType_idChange  - select



				// MedStaffFact
				if ( ! vm.get('MedStaffFact_id') && (MedStaffFact_id || LpuSection_id) )
				{
					rec = MedStaffFact.getStore().getById(MedStaffFact_id) || MedStaffFact.getStore().findRecord('LpuSection_id', LpuSection_id);

					vm.set('MedStaffFact_id', rec ? rec.get('MedStaffFact_id') : null );

				}

				if ( getRegionNick().inlist([ 'kareliya' ]) ) {

					form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
						return ! (rec.get('UslugaCategory_SysNick').inlist([ 'stomoms', 'stomklass' ]));
					});
				}

				// UslugaCategory
				UslugaCategory_SysNick = ( function () {

					UslugaCategory.enable();

					switch(getRegionNick())
					{
						// Для Перми по умолчанию подставляем услуги ГОСТ-2011
						// https://redmine.swan.perm.ru/issues/53028
						case 'perm':
						case 'kareliya':
						case 'penza':
						case 'adygeya':	
							return 'gost2011';
						case 'ekb':
							return 'tfoms';
						case 'kaluga':
							return 'lpusectiontree';
						default:
							return null;
					}

				}) () || null;

				if ( UslugaCategory.getStore().getCount() == 1 )
				{
					UslugaCategory.disable();
				}
				else
				{
					UslugaCategory.enable();
				}

				rec = ( UslugaCategory.getStore().getCount() == 1 || ! UslugaCategory_SysNick ) ? UslugaCategory.getStore().getAt(0) :  UslugaCategory.getStore().findRecord('UslugaCategory_SysNick', UslugaCategory_SysNick);
				vm.set('UslugaCategory_id', rec ? rec.get('UslugaCategory_id') : null);



				//base_form.findField('OperDiff_id').setFieldValue('OperDiff_Code', 0); - не определена

				vm.set('EvnUslugaOper_pid', params.parentEvnComboData.length > 0 ? params.parentEvnComboData[0].Evn_id : null);


				// Устанавливаем значение поля "Условие лечения" по умолчанию
				// https://redmine.swan.perm.ru/issues/22849
				vm.set('TreatmentConditionsType_id', {EvnPL: 1, EvnPS: 2}[parentClass] );


				//if ( prescr_combo.getValue() ) {
					//prescr_combo.disable();
					//prescr_combo.getStore().baseParams.newEvnPrescr_id = prescr_combo.getValue();
					// при выполнении назначения с оказанием услуги
					// нужно автоматически подставлять совпадающую по эталонным полям услугу,
					// на комбо услуг накладывать дополнительный фильтр по атрибуту услуги соответственно типу назначения


					// если услуга по назначению значит надо будет расставить даты и место и тд, примерно как с pid
				// 	prescr_combo.getStore().load({
				// 		callback: function()
				// 		{
				// 			// чтобы НЕ дать возможность выбрать другое назначение
				// 			prescr_combo.hasLoaded = true;
				// 			prescr_combo.setValue(prescr_combo.getValue());
				// 			index = prescr_combo.getStore().findBy(function(rec) {
				// 				return (rec.get(prescr_combo.valueField) == prescr_combo.getValue());
				// 			});
				// 			var rec = prescr_combo.getStore().getAt(index);
				//
				// 			if (rec) {
				// 				if (rec.get('EvnPrescr_setDate')) {
				// 					base_form.findField('EvnUslugaOper_setDate').setValue(rec.get('EvnPrescr_setDate'));
				// 				} else {
				// 					base_form.findField('EvnUslugaOper_setDate').setValue(getGlobalOptions().date);
				// 				}
				// 				base_form.findField('EvnUslugaOper_setDate').fireEvent('change', base_form.findField('EvnUslugaOper_setDate'), base_form.findField('EvnUslugaOper_setDate').getValue());
				//
				// 				//если услуга добавляется по назначению, то
				// 				//если ЛПУ назначения и места выполнения равны
				// 				if ( rec.get('Lpu_id') == getGlobalOptions().lpu_id)
				// 				{
				// 					//указываем место выполнение Отделение ЛПУ
				// 					usluga_place_combo.setValue(1);
				// 					usluga_place_combo.fireEvent('change', usluga_place_combo, 1);
				//
				// 					index = lpu_section_combo.getStore().findBy(function(rec) {
				// 						return ( rec.get('LpuSection_id') == getGlobalOptions().CurLpuSection_id );
				// 					});
				//
				// 					if ( index >= 0 ) {
				// 						lpu_section_combo.setValue(getGlobalOptions().CurLpuSection_id);
				// 						lpu_section_combo.fireEvent('change', lpu_section_combo, getGlobalOptions().CurLpuSection_id);
				// 					}
				// 				} else
				// 				{
				// 					//указываем место выполнение Другое ЛПУ
				// 					usluga_place_combo.setValue(2);
				// 					usluga_place_combo.fireEvent('change', usluga_place_combo, 2);
				// 					lpu_combo.getStore().load({
				// 						callback: function(records, options, success) {
				// 							if (success) {
				// 								lpu_combo.setValue(getGlobalOptions().lpu_id);
				// 							}
				// 						},
				// 						params: {
				// 							Org_id: getGlobalOptions().lpu_id, // какой в этом смысл
				// 							OrgType: 'lpu'
				// 						}
				// 					});
				// 				}
				// 			}
				// 			prescr_combo.fireEvent('change', prescr_combo, prescr_combo.getValue());
				// 		},
				// 		params: {
				// 			EvnPrescr_id: prescr_combo.getValue()
				// 		}
				// 	});
				// }
				break;
		}


		return;
	},
	doSave: function(options)
	{
		var wnd = this.getView(),
			wndVm = this.getViewModel(),
			formPanel = wnd.down('UslugaOperEditForm'),
			form = formPanel.getForm(),
			vm = formPanel.getViewModel(),
			controller = this,

			EvnCombo = form.findField('EvnUslugaOper_pid'),
			EvnUslugaOper_pid = vm.get('EvnUslugaOper_pid'),
			UslugaComplex = form.findField('UslugaComplex_id'),
			PayType_SysNick = form.findField('PayType_id').getFieldValue('PayType_SysNick'),
			parentClass = vm.get('parentClass'),
			ignorePaidCheck = wndVm.get('ignorePaidCheck'),
			action = vm.get('action'),
			isPriem = vm.get('isPriem');


		options = options||{};
		options.ignoreErrors = options.ignoreErrors || [];


		var loadMask = new Ext6.LoadMask(wnd, {msg: "Сохранение..."});
		loadMask.show();

		if ( ! form.isValid() )
		{
			if (IS_DEBUG)
			{
				form.getFields().filterBy(function(field){console.log(field); console.log(field.name + ' = ' + field.validate())})
			}

			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					loadMask.hide();
				},
				icon: Ext6.Msg.WARNING,
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				title: 'Проверка данных формы'
			});
			return false;
		}

		var setDate = vm.get('EvnUslugaOper_setDate'),
			setTime = vm.get('EvnUslugaOper_setTime'),
			disDate = vm.get('EvnUslugaOper_disDate'),
			disTime = vm.get('EvnUslugaOper_disTime');

		if (isPriem)
		{
			var Evn_setDate = EvnCombo.getFieldValue('Evn_setDate'),
				Evn_disDate = EvnCombo.getFieldValue('Evn_disDate');

			if (setDate < Evn_setDate || ( ! Ext6.isEmpty(Evn_disDate) && setDate > Evn_disDate)) 
			{
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						EvnCombo.focus()
					},
					icon: Ext6.Msg.WARNING,
					msg: 'Дата выполнения услуги не попадает в период выбранного движения',
					title: langs('Ошибка')
				});
				return false;
			}
		}

		if ( ! Ext6.isEmpty(disDate))
		{
			var setDateStr = Ext6.util.Format.date(setDate, 'Y-m-d')+' '+(Ext6.isEmpty(setTime)?'00:00':setTime);
			var disDateStr = Ext6.util.Format.date(disDate, 'Y-m-d')+' '+(Ext6.isEmpty(disTime)?'00:00':disTime);

			if (Date.parseDate(setDateStr, 'Y-m-d H:i') > Date.parseDate(disDateStr, 'Y-m-d H:i')) 
			{
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						//this.formStatus = 'edit';
						form.findField('EvnUslugaOper_setDate').focus()
					},
					icon: Ext6.Msg.WARNING,
					msg: langs('Дата окончания выполнения услуги не может быть меньше даты начала выполнения услуги.'),
					title: langs('Ошибка')
				});
				return false;
			}
		}


		if ( parentClass.inlist([ 'EvnPL', 'EvnPS', 'EvnVizit' ]) && ! EvnUslugaOper_pid && getRegionNick().inlist(['perm', 'kareliya','ekb']))
		{
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					form.findField('EvnUslugaOper_pid').focus();
				},
				icon: Ext6.Msg.WARNING,
				msg: 'Не выбрано ' + (parentClass == 'EvnPS' ? 'движение' : 'посещение'),
				title: 'Проверка данных формы'
			});
			return false;
		}

		var PersonData = vm.get('PersonData') || {},
			SexCode = PersonData.Sex_id,
			Person_Birthday = PersonData.Person_Birthday,

			diag_record = form.findField('Diag_id').getStore().getById(form.findField('Diag_id').getValue()),

			person_age = swGetPersonAge(Person_Birthday, setDate),
			person_age_month = swGetPersonAgeMonth(Person_Birthday, setDate),
			person_age_day = swGetPersonAgeDay(Person_Birthday, setDate);

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

		
		
		var params = new Object(),
			UslugaComplex_Name = UslugaComplex.getFieldValue('UslugaComplex_Name'),
			UslugaComplex_Code = UslugaComplex.getFieldValue('UslugaComplex_Code');
		
		params.MedPersonal_id = form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');

		params.ignorePaidCheck = ignorePaidCheck;
		params.ignoreParentEvnDateCheck = options.ignoreParentEvnDateCheck === 1 ? 1 : 0;
		params.ignoreBallonBegCheck = options.ignoreBallonBegCheck === 1 ? 1 : 0;
		params.ignoreCKVEndCheck = options.ignoreCKVEndCheck === 1 ? 1 : 0;

		
		form.submit({
			params: params,
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
									if (reply.result.Error_Code == 110) 
									{
										options.ignoreBallonBegCheck = 1;
									}
									if (reply.result.Error_Code == 111) 
									{
										options.ignoreCKVEndCheck = 1;
									}

									controller.doSave(options);
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

				if ( reply.result && reply.result.EvnUslugaOper_id > 0 )
				{
					form.findField('EvnUslugaOper_id').setValue(reply.result.EvnUslugaOper_id);

					formPanel.getController().linkFilesToEvn(reply.result.EvnUslugaOper_id);

					// автосохраняем документ
					controller.saveDocument({
						Evn_id: reply.result.EvnUslugaOper_id,
						onSaveEvnXml: options.onSaveEvnXml
					});
					controller.linkAnestRecords(reply.result.EvnUslugaOper_id);
					controller.linkOperBrigRecords(reply.result.EvnUslugaOper_id);
					controller.linkEvnAggRecords(reply.result.EvnUslugaOper_id);
					controller.linkEvnDrugRecords(reply.result.EvnUslugaOper_id);


					if ( options && typeof options.openChildWindow == 'function' && action == 'add' ) {
						options.openChildWindow();
					}
					else 
					{
						var data = new Object();

						data.evnUslugaData = {
							accessType: 'edit',
							EvnClass_SysNick: 'EvnUslugaOper',
							EvnUsluga_Kolvo: vm.get('EvnUslugaOper_Kolvo'),
							EvnUsluga_id: vm.get('EvnUslugaOper_id'),
							EvnUsluga_setDate: vm.get('EvnUslugaOper_setDate'),
							EvnUsluga_setTime: setTime,
							EvnUslugaOper_IsVMT: vm.get('EvnUslugaOper_IsVMT'),
							EvnUslugaOper_IsMicrSurg: vm.get('EvnUslugaOper_IsMicrSurg'),
							EvnUslugaOper_IsOpenHeart: vm.get('EvnUslugaOper_IsOpenHeart'),
							EvnUslugaOper_IsArtCirc: vm.get('EvnUslugaOper_IsArtCirc'),
							Usluga_Code: UslugaComplex_Code,
							Usluga_Name: UslugaComplex_Name
						};

						wnd.callback(data);
						if (typeof options.onSaveEvnXml != 'function') {
							wnd.close();
						}
					}
				}
				else {
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}
		});
	},
	openEvnAggEditWindow: function (btn, e)
	{
		var wnd = this.getView(),
			formPanel = wnd.down('UslugaOperEditForm'),
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
			if ( ! (data.EvnUslugaOper_setDate && data.EvnUslugaOper_setTime && data.Person_id && data.PersonEvn_id) )
			{
				return;
			}
		}


		var params = {
			EvnAgg_pid: data.EvnUslugaOper_id || null,
			Person_id: data.Person_id,
			PersonEvn_id: data.PersonEvn_id,
			Server_id: data.Server_id,
			EvnAgg_setDate:data.EvnUslugaOper_setDate,
			EvnAgg_setTime: data.EvnUslugaOper_setTime
		};


		if (btn.up('menu'))
		{
			rec = btn.up('menu').selRecord;
			if (rec)
			{
				EvnAgg_id = rec ? rec.get('EvnAgg_id') : null;
				action = 'edit';

				Ext6.apply(params, rec.data);
				console.log(params)
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
	openEvnUslugaOperBrigEditWindow: function(btn, e)
	{
		var wnd = this.getView(),
			formPanel = wnd.down('UslugaOperEditForm'),
			form = formPanel.getForm(),
			gridPanel = wnd.down('OperBrigGrid'),
			data = form.getValues(),
			EvnUslugaOperBrig_id,
			MedStaffFact_id,
			SurgType_id,
			formAction = formPanel.getViewModel().get('action'),
			action = 'add',
			rec,
			hasSurgeon = gridPanel.getViewModel().get('hasSurgeon'),
			surgTypeFilter;


		if ( ! (data.EvnUslugaOper_id ))
		{
			//return;
		}

		if ( Ext6.isEmpty(data.EvnUslugaOper_setDate) )
		{
			Ext6.Msg.alert(langs('Сообщение'), langs('Для заведения врачей в операционную бригаду необходимо указать Дату начала операции'));
			return;
		}

		var params = {
			EvnUslugaOperBrig_pid: data.EvnUslugaOper_id || null,
			EvnUslugaOper_setDate: data.EvnUslugaOper_setDate
		};


		if (btn.up('menu'))
		{
			rec = btn.up('menu').selRecord;
			if (rec)
			{
				EvnUslugaOperBrig_id = rec ? rec.get('EvnUslugaOperBrig_id') : null;
				SurgType_id = rec ? rec.get('SurgType_id') : null;
				MedStaffFact_id = rec ? rec.get('MedStaffFact_id') : null;
				action = 'edit';


				Ext6.apply(params, {
					EvnUslugaOperBrig_id: EvnUslugaOperBrig_id,
					MedStaffFact_id: MedStaffFact_id,
					SurgType_id: SurgType_id
				});
			}
		}

		if (action == 'add')
		{
			surgTypeFilter = hasSurgeon ? -1 : 0;
		} else
		{
			surgTypeFilter = rec.get('SurgType_Code') == 1 ? 1 : (hasSurgeon ? -1 : 0);
		}


		getWnd('EvnUslugaOperBrigEditWindow')
			.show({
				action: action,
				formParams: params,
				parentAction: formAction,
				surgTypeFilter: surgTypeFilter,

				callback: function(data)
				{
					if ( ! data || ! data.EvnUslugaOperBrigData )
					{
						return false;
					}

					if (formAction === 'add')
					{
						if (action === 'edit' && rec)
						{
							// отредактировать запись
							rec.set(data.EvnUslugaOperBrigData[0]);
						} else
						{
							gridPanel.getStore().loadData(data.EvnUslugaOperBrigData, true /*to append*/)
						}

						gridPanel.getViewModel().set('hasSurgeon', gridPanel.brigadeHasSurgeon()); // обновлеяем данные о наличии хирурга на гриде, вручную потому что тут не срабатывает событие datachanged

					} else {
						gridPanel.getStore().reload();
					}

					return;
				}
		});

		return;
	},
	openEvnUslugaOperAnestEditWindow: function(btn, e)
	{
		var wnd = this.getView(),
			formPanel = wnd.down('UslugaOperEditForm'),
			form = formPanel.getForm(),
			gridPanel = wnd.down('OperAnestGrid'),
			data = form.getValues(),
			EvnUslugaOperAnest_id,
			AnesthesiaClass_id,
			formAction = formPanel.getViewModel().get('action'),
			action = 'add',
			rec;

		if ( ! data.EvnUslugaOper_id )
		{
			//return;
		}

		var params = {
			EvnUslugaOperAnest_pid: data.EvnUslugaOper_id || null
		};


		if (btn.up('menu'))
		{
			rec = btn.up('menu').selRecord;
			if (rec)
			{
				EvnUslugaOperAnest_id = rec ? rec.get('EvnUslugaOperAnest_id') : null;
				AnesthesiaClass_id = rec ? rec.get('AnesthesiaClass_id') : null;
				action = 'edit';

				Ext6.apply(params, {
					EvnUslugaOperAnest_id: EvnUslugaOperAnest_id,
					AnesthesiaClass_id: AnesthesiaClass_id
				});
			}
		}

		getWnd('EvnUslugaOperAnestEditWindow')
			.show({
				action: action,
				parentAction: formAction,
				formParams: params,

				callback: function(data)
				{
					if ( ! data || ! data.EvnUslugaOperAnestData ) {
						return false;
					}

					if (formAction === 'add')
					{
						if (action === 'edit' && rec)
						{
							// отредактировать запись
							rec.set(data.EvnUslugaOperAnestData[0]);
						} else
						{
							gridPanel.getStore().loadData(data.EvnUslugaOperAnestData, true /*to append*/)
						}

					} else {
						gridPanel.getStore().reload();
					}

					return;
				}
		});
	},

	openEvnDrugEditWindow: function(btn, e)
	{
		var wnd = this.getView(),
			formPanel = wnd.down('UslugaOperEditForm'),
			form = formPanel.getForm(),
			gridPanel = wnd.down('EvnDrugGrid'),
			formAction = formPanel.getViewModel().get('action'),
			data = form.getValues(),
			action = 'add',
			EvnDrug_id,
			rec;

		if ( ! data.EvnUslugaOper_id )
		{
			//return;
		}


		var params = {
			Person_id: data.Person_id,
			PersonEvn_id: data.PersonEvn_id,
			Server_id: data.Server_id,
			EvnDrug_pid: data.EvnUslugaOper_id,
			EvnDrug_rid: data.EvnUslugaOper_id
		};

		if (btn.up('menu'))
		{
			rec = btn.up('menu').selRecord;
			if (rec)
			{
				EvnDrug_id = rec ? rec.get('EvnDrug_id') : null;
				action = 'edit';

				if (formAction === 'add')
				{
					params = rec.data;
				} else {
					Ext6.apply(params, {
						EvnDrug_id: EvnDrug_id
					});
				}
			}
		}


		getWnd('EvnDrugEditWindow').show({ // getEvnDrugEditWindowName()
			action: action,
			parentEvnClass_SysNick: 'EvnUslugaOper',
			parentAction: formAction,
			formParams: params,
			callback: function(data) {
				if ( ! data || !data.evnDrugData ) {
					return false;
				}

				if (formAction === 'add')
				{
					if (action === 'edit' && rec)
					{
						// отредактировать запись
						rec.set(data.evnDrugData[0]);
					} else
					{
						gridPanel.getStore().loadData(data.evnDrugData, true /*to append*/)
					}

				} else {
					gridPanel.getStore().reload();
				}
			}
		});

		return;
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

		// сохранение вызовется при закрытии
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

	linkAnestRecords: function (EvnUslugaOper_id)
	{
		if ( ! EvnUslugaOper_id )
		{
			return;
		}

		var wnd = this.getView(),
			formPanel = wnd.down('form'),
			vm = formPanel.getViewModel(),
			gridPanel = wnd.down('OperAnestGrid'),
			action = vm.get('action');

		if ( action !== 'add' )
		{
			return;
		}

		gridPanel.getStore().each( rec => rec.save({params:{EvnUslugaOperAnest_pid: EvnUslugaOper_id}}) );

		return;
	},

	linkOperBrigRecords: function (EvnUslugaOper_id)
	{
		if ( ! EvnUslugaOper_id )
		{
			return;
		}

		var wnd = this.getView(),
			formPanel = wnd.down('form'),
			vm = formPanel.getViewModel(),
			gridPanel = wnd.down('OperBrigGrid'),
			action = vm.get('action');

		if ( action !== 'add' )
		{
			return;
		}

		gridPanel.getStore().each( rec => rec.save({params:{EvnUslugaOperBrig_pid: EvnUslugaOper_id}}) );

		return;
	},

	linkEvnAggRecords: function (EvnUslugaOper_id)
	{
		if ( ! EvnUslugaOper_id )
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

		gridPanel.getStore().each( rec => rec.save({params:{EvnAgg_pid: EvnUslugaOper_id}}) );

		return;
	},

	linkEvnDrugRecords: function (EvnUslugaOper_id)
	{
		if ( ! EvnUslugaOper_id )
		{
			return;
		}

		var wnd = this.getView(),
			formPanel = wnd.down('form'),
			vm = formPanel.getViewModel(),
			gridPanel = wnd.down('EvnDrugGrid'),
			action = vm.get('action');

		if ( action !== 'add' )
		{
			return;
		}

		gridPanel.getStore().each( rec => { rec.set('EvnDrug_pid', EvnUslugaOper_id); rec.save();} );

		return;
	}
});