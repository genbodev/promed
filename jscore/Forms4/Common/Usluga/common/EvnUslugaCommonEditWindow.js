Ext6.define('UslugaViewModel', {
	extend: 'Ext6.app.ViewModel',
	alias: 'viewmodel.UslugaViewModel',
	data: {

		UCP: { // UslugaComplexParams

			allowedUslugaComplexAttributeList: null,
			allowedUslugaComplexAttributeMethod: 'or',
			allowMorbusVizitCodesGroup88: 0,
			allowMorbusVizitOnly: 0,
			allowNonMorbusVizitOnly: 0,
			ignoreUslugaComplexDate: 0,
			disallowedUslugaComplexAttributeList: null,
			Mes_id: null,
			MesOldVizit_id: null,
			LpuLevel_Code: null,
			uslugaCategoryList: null,
			uslugaComplexCodeList: null,
			UslugaComplex_2011id: null,
			personAge: null
		},

		action: 'add',
		isPriem: false,
		Server_id: null,
		Person_id: null,

		EvnUslugaCommon_id: null,

		EvnCount: 0,
		EvnUslugaCommon_pid: null,
		EvnUslugaCommon_setDate: new Date(),
		EvnUslugaCommon_setTime: new Date().format('H:i'),
		EvnUslugaCommon_disDate: new Date(),
		EvnUslugaCommon_disTime: new Date().format('H:i'),

		UslugaPlace_id: null,
		LpuSection_uid: null,
		LpuSectionProfile_id: null,

		Lpu_uid: null,
		Org_id: null,
		PayType_id: null,

		MedSpecOms_id: null,
		MedStaffFact_id: null,


		UserMedStaffFact_id: null,
		UserMedStaffFacts: null,
		UserLpuSection_id: null,
		UserLpuSections: null,


		UslugaCategory_id: null,
		UslugaComplex_id: null,
		UslugaComplex_IsCabEarlyZno: null,
		UslugaComplexTariff_id: null,
		UslugaComplexTariff_UED: 0,
		EvnUslugaCommon_Price: 0,
		EvnUslugaCommon_Kolvo: 0,

		LoadUslugaComplex_id: null,
		LoadUslugaComplexTariff_id: null,
		DiagSetClass_id: null,

		PersonData: {},

		formIsLoading: false,
		EvnMediaDataIds: [],

		UslugaExecutionType_id: null,
		UslugaExecutionReason_id: null
	},
	formulas: {
		getMpId: function (get) // автоматически устанавливем MedPersonal_id при изменении MedStaffFact_id
		{
			var form = this.getView().getForm(),
				MedStaffFact = form.findField('MedStaffFact_id'),
				MedStaffFact_id = get('MedStaffFact_id'), // достаем значение из viewModel - входящий аргумент это функция
				rec = MedStaffFact.getStore().getById(MedStaffFact_id),
				MedPersonal_id = rec ? rec.get('MedPersonal_id') : null;

			return MedPersonal_id;
		},
		isOms: function (get)
		{
			var form = this.getView().getForm(),
				PayType = form.findField('PayType_id'),

				PayType_id = get('PayType_id'),
				rec = PayType.getStore().findRecord('PayType_id', PayType_id),
				PayType_SysNick = PayType.getFieldValue('PayType_SysNick') || (rec ? rec.get('PayType_SysNick') : null);

			return PayType_SysNick ? PayType_SysNick == 'oms' : false;
		},
		UslugaComplexTariff_Tariff: function (get)
		{
			var formPanel = this.getView(),
				form = formPanel.getForm(),
				vm = formPanel.getViewModel(),
				UslugaComplexTariff_id = get('UslugaComplexTariff_id'),
				UslugaComplex_id = get('UslugaComplex_id'),

				UslugaComplexTariff = form.findField('UslugaComplexTariff_id'),
				rec = UslugaComplexTariff.getStore().getById(UslugaComplexTariff_id),
				UslugaComplexTariff_Tariff = rec ? rec.get('UslugaComplexTariff_Tariff') : false;


			if ( ! (UslugaComplex_id && UslugaComplexTariff_id) )
			{
				vm.set('EvnUslugaCommon_Price', 0);
				return;
			}

			if (UslugaComplexTariff_Tariff !== false)
			{
				vm.set('EvnUslugaCommon_Price', isNaN( Number(UslugaComplexTariff_Tariff) ) ? 0 : Number(UslugaComplexTariff_Tariff) );
			}


			return;
		},
		getTitle: function (get)
		{
			var formPanel = this.getView(),
				wnd = formPanel.up('window'),
				vm = formPanel.getViewModel(),
				wndVm = vm.getParent(),
				action = get('action'),
				title,

				titles = {
					add: langs('Добавить общую услугу'),
					edit: langs('Редактировать общую услугу'),
					view: langs('Просмотр общей услуги')
				};

			if ( ! action )
			{
				title = 'Форма общей услуги';
			}

			title = action ? titles[action] : langs('Форма общей услуги');

			wndVm.set('title', title);
			//wnd.setTitle(title);

			return;
		},
		editable: function (get)
		{
			var formPanel = this.getView(),
				wnd = formPanel.up('window'),
				vm = formPanel.getViewModel(),
				filesVm = wnd.down('FilesUploadForm').getViewModel(),
				action = get('action') || '';

			vm.getParent().set('editable', action.inlist(['add', 'edit']));
			filesVm.set('editable', action.inlist(['add', 'edit']));

			return action.inlist(['add', 'edit']);
		},
		PersonData: function (get)
		{
			var Person_id = get('Person_id'),
				formPanel = this.getView(),
				vm = formPanel.getViewModel();

			if ( ! Person_id || isNaN(Number(Person_id)))
			{
				return {};
			}

			Ext6.Ajax.request({
				params: {Person_id: Person_id, mode: 'PersonInfoPanel'},
				url: '/?c=Common&m=loadPersonData'
			}).then( ({responseText}) => vm.set('PersonData', Ext6.JSON.decode(responseText)[0]) );
		}
	}
});



Ext6.define('UslugaEditForm', {
	extend: 'GeneralFormPanel',
	alias: 'widget.UslugaEditForm',
	requires: ['usluga.common.controllers.UslugaFormBindingsController', 'usluga.common.models.UslugaFormModel', 'common.PersonInfoPanel.PersonInfoPanel2'],
	bodyPadding: '15 27 20 30',
	controller: 'UslugaFormBindingsController',
	viewModel: 'UslugaViewModel',
	border: false,
	reader: {
		type: 'json',
		model: 'usluga.common.models.UslugaFormModel'
	},

	// просто вынес скрытые поля, чтобы не мешали работать. В initComponent добавляю их к основным
	hiddenFields: [
		{
			name: 'accessType',
			value: '',
			xtype: 'hidden'
		}, {
			name: 'EvnClass_SysNick',
			value: 0,
			xtype: 'hidden',
			bind: '{EvnClass_SysNick}'
		}, {
			name: 'EvnUslugaCommon_id',
			value: 0,
			xtype: 'hidden'
		}, {
			name: 'EvnUslugaCommon_rid',
			value: 0,
			xtype: 'hidden'
		},{
			name: 'Evn_id',
			value: 0,
			bind: '{EvnUslugaCommon_pid}',
			xtype: 'hidden'
		}, {
			name: 'EvnDirection_id',
			value: 0,
			xtype: 'hidden'
		}, {
			name: 'MedPersonal_id',
			value: 0,
			xtype: 'hidden',
			bind: '{getMpId}'
		}, {
			name: 'Morbus_id',
			value: -1,
			xtype: 'hidden'
		}, {
			name: 'Person_id',
			value: -1,
			bind: '{Person_id}',
			xtype: 'hidden'
		}, {
			name: 'PersonEvn_id',
			value: -1,
			bind: '{PersonEvn_id}',
			xtype: 'hidden'
		}, {
			name: 'Server_id',
			value: -1,
			xtype: 'hidden'
		}
	],

	defaults: {
		width: '100%',
		maxWidth: 635,
		labelWidth: 170
	},
	items: [
		{
			bind: {
				fieldLabel: '{ (parentClass == "EvnVizit" || parentClass == "EvnPL" || parentClass == "EvnPLStom") ? "Посещение" : "Движение"}',
				value: '{EvnUslugaCommon_pid}',
				disabled: '{(editable  === false) || EvnCount == 1}' // || parentClass == "EvnPLStom" || parentClass == "EvnVizit" || parentClass == "EvnPrescr" || parentClass == "EvnSection" ||
			},
			xtype: 'EventCombo',
			name: 'EvnUslugaCommon_pid',
			labelWidth: 170,
			width: '100%',
			maxWidth: 635
		},
		{
			layout: 'column',
			border: false,
			defaults: {
				labelWidth: 170,
				width: '100%'
			},
			margin: '0 0 5 0',
			items: [
				{
					xtype: 'datefield',
					plugins: [new Ext6.ux.InputTextMask('99.99.9999', true)],
					maxWidth: 295,
					fieldLabel: langs('Дата/время начала'),
					allowBlank: false,
					margin: '0 40 0 0',
					bind: {
						value: '{EvnUslugaCommon_setDate}',
						disabled: '{editable === false}'
					},
					name: 'EvnUslugaCommon_setDate',
					listeners: {
						change: function (c, v)
						{
							try {
								if (this.up('form') && this.up('form').getViewModel())
								{
									this.up('form').getViewModel().set(this.name, v);
								}

								//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
							} catch (e) {
								if (IS_DEBUG)
								{
									console.log(e)
								}
							}


							return;
						}
					}
				},
				{
					xtype: 'swTimeField',
					allowBlank: false,
					width: '15%',
					hideLabel: true,
					name: 'EvnUslugaCommon_setTime',
					bind: {
						value: '{EvnUslugaCommon_setTime}',
						disabled: '{editable === false}'
					},
					listeners: {
						change: function (c, v)
						{
							try {
								if (this.up('form') && this.up('form').getViewModel())
								{
									this.up('form').getViewModel().set(this.name, v);
								}

								//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
							} catch (e) {
								if (IS_DEBUG)
								{
									console.log(e)
								}
							}


							return;
						}
					}
				}
			]
		},
		{
			layout: 'column',
			border: false,
			defaults: {
				labelWidth: 170,
				width: '100%'
			},
			margin: '0 0 5 0',
			items: [
				{
					xtype: 'datefield',
					allowBlank: false,
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
					maxWidth: 295,
					fieldLabel: langs('Дата/время окончания'),
					margin: '0 40 0 0',
					name: 'EvnUslugaCommon_disDate',
					bind: {
						value: '{EvnUslugaCommon_disDate}',
						disabled: '{editable === false}'
					},
					listeners: {
						change: function (c, v)
						{
							try {
								if (this.up('form') && this.up('form').getViewModel())
								{
									this.up('form').getViewModel().set(this.name, v);
								}

								//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
							} catch (e) {
								if (IS_DEBUG)
								{
									console.log(e)
								}
							}


							return;
						}
					}
				},
				{
					xtype: 'swTimeField',
					allowBlank: false,
					width: '15%',
					hideLabel: true,
					name: 'EvnUslugaCommon_disTime',
					bind: {
						value: '{EvnUslugaCommon_disTime}',
						disabled: '{editable === false}'
					},
					listeners: {
						change: function (c, v)
						{
							try {
								if (this.up('form') && this.up('form').getViewModel())
								{
									this.up('form').getViewModel().set(this.name, v);
								}

								//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
							} catch (e) {
								if (IS_DEBUG)
								{
									console.log(e)
								}
							}


							return;
						}
					}
				},
				{
					xtype: 'button',
					width: 32,
					userCls: 'equally-button',
					iconCls: 'equally-button-icon',
					handler: 'equalDates',
					bind: { disabled: '{editable === false }'}
				}
			]
		},
		{
			xtype: 'fieldset',
			title: 'Уточнить объем выполнения',
			bind: {
				collapsed: '{!UslugaExecutionType_id || UslugaExecutionType_id == 1}',
				hidden: getRegionNick().inlist(['perm']) ? '{parentClass != "EvnPS" && parentClass != "EvnSection" && !UslugaComplex_IsCabEarlyZno}' : true
			},
			bodyPadding: '0 0 0 0',
			border: false,
			collapsible: true,
			listeners: {
				expand: function() {
					var formPanel = this.up('form'),
						form = formPanel.getForm();

					if ( Ext6.isEmpty(form.findField('UslugaExecutionType_id').getValue()) ) {
						form.findField('UslugaExecutionType_id').setValue(1);
					}
				}
			},
			width: '100%',
			defaults: {
				labelWidth: 170,
				width: '100%'
			},
			items: [{
				anchor: '100%',
				bind: {
					value: '{UslugaExecutionType_id}'
				},
				comboSubject: 'UslugaExecutionType',
				displayCode: true,
				fieldLabel: 'Объем выполнения',
				listeners: {
					change: function(combo, newValue, oldValue) {
						var formPanel = this.up('form'),
							form = formPanel.getForm();

						if ( newValue != 2 && newValue != 3 ) {
							form.findField('UslugaExecutionReason_id').clearValue();
						}
					}
				},
				name: 'UslugaExecutionType_id',
				typeCode: 'int',
				xtype: 'commonSprCombo'
			}, {
				anchor: '100%',
				bind: {
					allowBlank: '{editable === false}',
					disabled: '{UslugaExecutionType_id != 2 && UslugaExecutionType_id != 3}',
					value: '{UslugaExecutionReason_id}'
				},
				comboSubject: 'UslugaExecutionReason',
				displayCode: true,
				fieldLabel: 'Причина частичного выполнения (невыполнения)',
				name: 'UslugaExecutionReason_id',
				typeCode: 'int',
				xtype: 'commonSprCombo'
			}]
		},
		{
			allowBlank: false,
			comboSubject: 'UslugaPlace',
			fieldLabel: langs('Место выполнения'),
			xtype: 'commonSprCombo',
			lastQuery: '',
			bind: {
				value: '{UslugaPlace_id}',
				disabled: '{editable === false}'
			},
			name: 'UslugaPlace_id'
		},{
			fieldLabel: langs('Отделение'),
			xtype: 'SwLpuSectionGlobalCombo',
			bind: {
				value: '{LpuSection_uid}',
				disabled: '{(editable === false) || (UslugaPlace_id ? UslugaPlace_id != 1 : 1 != 1)}'
			},
			allowBlank: false,
			queryMode: 'local',
			name: 'LpuSection_uid'
		},{
			fieldLabel: langs('Профиль'),
			xtype: 'LpuSectionProfileWithFedCombo',
			bind: {
				value: '{LpuSectionProfile_id}',
				allowBlank: '{ ! isOms }',
				disabled: getRegionNick().inlist(['perm']) ? '{editable === false}' : '{editable === false || (UslugaPlace_id ? UslugaPlace_id != 1 : 1 != 1)}'
			},
			hidden: getRegionNick() == 'astra',
			name: 'LpuSectionProfile_id'
		},{
			fieldLabel: langs('МО'),
			editable: false,
			xtype: 'swOrgCombo',
			orgType: 'lpu',
			onlyFromDictionary: true,
			bind: {
				value: '{Lpu_uid}',
				disabled: '{(editable === false) || (UslugaPlace_id ? UslugaPlace_id != 2 : 1 == 1)}'
			},
			allowBlank: false,
			name: 'Lpu_uid'
		},{
			fieldLabel: langs('Другая организация'),
			xtype: 'swOrgCombo',
			editable: false,
			orgType: 'org',
			bind: {
				value: '{Org_uid}',
				disabled: '{(editable === false) || (UslugaPlace_id ? UslugaPlace_id != 3 : 1 == 1)}'
			},
			allowBlank: false,
			name: 'Org_uid'
		},{
			fieldLabel: langs('Специальнсть'),
			name: 'MedSpecOms_id',
			queryMode: 'local',
			//allowBlank: false,
			bind: {
				value: '{MedSpecOms_id}',
				hidden: getRegionNick() == 'perm' ? '{UslugaPlace_id ? UslugaPlace_id == 1 : 1 != 1}' : '{1 == 1}',
				allowBlank: getRegionNick() == 'perm' ? '{UslugaPlace_id ? ( UslugaPlace_id == 1 ? 1 == 1 : ! isOms) : 1 == 1 }' : '{1 == 1}',
				disabled: '{(editable === false)}'
			},
			xtype: 'MedSpecOmsWithFedCombo'
		},{
			name: 'EvnUslugaCommon_IsMinusUsluga',
			fieldLabel: 'Вычесть стоимость услуги',
			bind: {
				value: '{isMinusUsluga}',
				disabled: '{editable === false}'
			},
			xtype: 'checkbox',
			hidden: true
		},{
			fieldLabel: langs('Врач, выполнивший услугу'),
			xtype: 'swMedStaffFactCombo',
			queryMode: 'local',
			name: 'MedStaffFact_id',
			listConfig:{
				userCls: 'usluga-med-staff-fact-combo swMedStaffFactSearch'
			},
			bind: {
				value: '{MedStaffFact_id}',
				disabled: '{(editable === false) || (UslugaPlace_id ? UslugaPlace_id != 1 : 1 != 1)}',
				allowBlank: '{(UslugaPlace_id ? UslugaPlace_id != 1 : 1 != 1)}'
			}
		},{
			fieldLabel: langs('Вид оплаты'),
			allowBlank: false,
			xtype: 'swPayTypeCombo',
			bind: {
				value: '{PayType_id}',
				disabled: '{editable == false}'
			},
			name: 'PayType_id'
		},{
			fieldLabel: langs('Назначение'),
			xtype: 'EvnPrescrCombo',
			queryMode: 'local',
			bind: {
				value: '{EvnPrescr_id}',
				disabled: '{editable == false || EvnCount == 0}'
			},
			name: 'EvnPrescr_id'
		},{
			fieldLabel: langs('Категория услуги'),
			xtype: 'swUslugaCategoryCombo',
			allowBlank: false,
			queryMode: 'local',
			name: 'UslugaCategory_id',
			bind: {
				value: '{UslugaCategory_id}',
				disabled: '{editable == false}'
			}
		},
		// {
		// 	name: 'filterUslugaComplex',
		// 	fieldLabel: langs('Фильтровать услуги'),
		// 	hidden: !getRegionNick().inlist([ 'ekb' ]),
		// 	listeners: {
		// 		check: function(checkbox)
		// 		{
		// 			var form = win.FormPanel.getForm();
		//
		// 			if (checkbox.getValue()) {
		// 				form.findField('UslugaComplex_id').setPersonId(form.findField('Person_id').getValue());
		// 				form.findField('UslugaComplex_id').setPayType(form.findField('PayType_id').getValue());
		// 				form.findField('UslugaComplex_id').getStore().getProxy().setExtraParam('MedPersonal_id', form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));
		// 			} else {
		// 				form.findField('UslugaComplex_id').setPersonId(null);
		// 				form.findField('UslugaComplex_id').setPayType(null);
		// 				form.findField('UslugaComplex_id').getStore().getProxy().setExtraParam('MedPersonal_id', null);
		// 			}
		//
		// 			form.findField('UslugaComplex_id').clearValue();
		// 			form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
		// 			form.findField('UslugaComplex_id').getStore().removeAll();
		// 			form.findField('UslugaComplex_id').getStore().getProxy().setExtraParam('query', '');
		// 		}
		// 	},
		// 	xtype: 'checkbox'
		// },
		{
			fieldLabel: langs('Услуга'),
			to: 'EvnUslugaCommon',
			queryMode: 'remote',
			bind: {
				value: '{UslugaComplex_id}',
				disabled: '{editable == false}'
			},
			listeners: {
				change: function(combo, newValue, oldValue) {
					var formPanel = this.up('form'),
						form = formPanel.getForm(),
						parentClass = formPanel.getViewModel().get('parentClass');

					var UslugaComplex_IsCabEarlyZno = !Ext6.isEmpty(combo.getFieldValue('UslugaComplex_AttributeList')) && combo.getFieldValue('UslugaComplex_AttributeList').indexOf('kab_early_zno') != -1;

					formPanel.getViewModel().set('UslugaComplex_IsCabEarlyZno', UslugaComplex_IsCabEarlyZno);

					if (getRegionNick() == 'perm' && parentClass != 'EvnPS' && parentClass != 'EvnSection' && UslugaComplex_IsCabEarlyZno == false) {
						form.findField('UslugaExecutionType_id').clearValue();
					}

					return;
				}
			},
			xtype: 'UslugaComplexCombo',
			allowBlank: false,
			name: 'UslugaComplex_id'
		},
		// {
		// 	comboSubject: 'YesNo',
		// 	hidden: true,
		// 	fieldLabel: langs('По модернизации'),
		// 	xtype: 'commonSprCombo',
		// 	name: 'EvnUslugaCommon_IsModern'
		// },{
		// 	comboSubject: 'MesOperType',
		// 	hidden: true,
		// 	fieldLabel: langs('Вид лечения'),
		// 	//typeCode: 'int',
		// 	xtype: 'commonSprCombo',
		// 	name: 'MesOperType_id'
		// }, {
		// 	enableKeyEvents: true,
		// 	hidden: true,
		// 	fieldLabel: langs('Коэффициент изменения тарифа'),
		// 	minValue: 0.000001,
		// 	xtype: 'SimpleNumField',
		// 	name: 'EvnUslugaCommon_CoeffTariff'
		// },
		{
			fieldLabel: langs('Тариф'),
			xtype: 'UslugaComplexTariffCombo',
			name: 'UslugaComplexTariff_id',
			bind: {
				value: '{UslugaComplexTariff_id}',
				disabled: '{editable == false}'
			}
		}, {
			comboSubject: 'DiagSetClass',
			allowSysNick: true,
			bind: {
				hidden: getRegionNick() === 'ekb' ? '{ ! (parentClass =="EvnVizit" || parentClass == "EvnSection" || parentClass == "EvnPL" || parentClass == "EvnPS")}' : '{ 1 == 1 }',
				disabled: '{editable == false}',
				value: '{DiagSetClass_id}',
				allowBlank: getRegionNick() === 'ekb' ? '{ ! (parentClass =="EvnVizit" || parentClass == "EvnSection" || parentClass == "EvnPL" || parentClass == "EvnPS")}' : '{ 1 == 1 }'
			},
			allowBlank: false,
			fieldLabel: langs('Вид диагноза'),
			xtype: 'commonSprCombo',
			name: 'DiagSetClass_id'
		},{
			xtype: 'swDiagCombo',
			allowBlank: false,
			bind: {
				hidden: getRegionNick() === 'ekb' ? '{ ! (parentClass =="EvnVizit" || parentClass == "EvnSection" || parentClass == "EvnPL" || parentClass == "EvnPS")}' : '{ 1 == 1 }',
				disabled: '{editable == false}',
				value: '{Diag_id}',
				allowBlank: getRegionNick() === 'ekb' ? '{ ! (parentClass =="EvnVizit" || parentClass == "EvnSection" || parentClass == "EvnPL" || parentClass == "EvnPS")}' : '{ 1 == 1 }'
			},
			fieldLabel: 'Основной диагноз',
			name: 'Diag_id',
			listeners: {
				change: function(combo, newValue, oldValue)
				{
					var formPanel = this.up('form'),
						form = formPanel.getForm(),
						action = formPanel.getViewModel().get('action');


					var rec = form.findField('EvnUslugaCommon_pid').getStore().getById(form.findField('EvnUslugaCommon_pid').getValue()),
						Diag_id = rec ? rec.get('Diag_id') : null;


					form.findField('DiagSetClass_id').getStore().clearFilter();

					if (!Ext6.isEmpty(newValue) && newValue == Diag_id)
					{
						form.findField('DiagSetClass_id').getStore().filterBy(function(rec) {
							return (rec.get('DiagSetClass_Code').inlist(['1']));
						});
						form.findField('DiagSetClass_id').setFieldValue('DiagSetClass_Code', '1');
						form.findField('DiagSetClass_id').disable();

					} else
					{
						if (form.findField('DiagSetClass_id').getFieldValue('DiagSetClass_Code') == '1') {
							form.findField('DiagSetClass_id').setValue(null);
						}
						form.findField('DiagSetClass_id').getStore().filterBy(function(rec) {
							return (rec.get('DiagSetClass_Code').inlist(['0','2','3']));
						});

						if (action != 'view') {
							form.findField('DiagSetClass_id').enable();
						}
					}
				}
			}
		},

		{
			border: false,
			layout: {
				type: 'hbox'
			},
			margin: '0 0 5 0',
			items: [
				{
					fieldLabel: langs('Цена'),
					allowBlank: true,
					disabled: true,
					labelWidth: 170,
					xtype: 'SimpleNumField',
					maxWidth: 295,
					width: '100%',
					name: 'EvnUslugaCommon_Price',
					bind: {
						value: '{EvnUslugaCommon_Price}'
					}
				},
				{xtype: 'tbspacer', flex: 1, maxWidth: 30},
				{
					fieldLabel: langs('УЕТ'),
					labelWidth: 90,
					width: '100%',
					maxWidth: 215,
					maxValue: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ? 99 : 20),
					allowBlank: true,
					allowDecimals: true,
					disabled: true,
					xtype: 'SimpleNumField',
					bind: {
						value: '{UslugaComplexTariff_UED}'
					},
					name: 'UslugaComplexTariff_UED'
				},
				{xtype: 'tbspacer', flex: 1, maxWidth: 30},
			]
		},{
			border: false,
			layout: {
				type: 'hbox'
			},
			margin: '0 0 5 0',
			items: [
				{
					fieldLabel: langs('Количество'),
					labelWidth: 170,
					maxWidth: 295,
					width: '100%',
					allowBlank: false,
					enableKeyEvents: true,
					value: 1,
					xtype: 'SimpleNumField',
					bind: {
						value: '{EvnUslugaCommon_Kolvo}',
						disabled: '{editable === false}'
					},
					name: 'EvnUslugaCommon_Kolvo'
				},
				{xtype: 'tbspacer', flex: 1, maxWidth: 30},
				{
					fieldLabel: langs('Сумма (УЕТ)'),
					allowDecimals: true,
					disabled: true,
					labelWidth: 90,
					width: '100%',
					maxWidth: 215,
					xtype: 'SimpleNumField',
					bind: {
						value: '{ - ( -(UslugaComplexTariff_UED ? UslugaComplexTariff_UED : 0) - (EvnUslugaCommon_Price ? EvnUslugaCommon_Price : 0)) * (EvnUslugaCommon_Kolvo ? EvnUslugaCommon_Kolvo : 0) }'
					},
					name: 'EvnUslugaCommon_Summa'
				},
				{xtype: 'tbspacer', flex: 1, maxWidth: 30}
			]
		}
	],

	initComponent: function ()
	{
		this.items = Array.prototype.concat(this.hiddenFields, this.items);
		this.callParent(arguments);
	}
});








Ext6.define('usluga.common.EvnUslugaCommonEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.EvnUslugaCommonEditWindow',
	requires: ['usluga.common.controllers.UslugaWindowController', 'usluga.components.EvnMediaGrid',
		'usluga.components.EvnAggGrid', 'usluga.components.FilesUploadForm', 'common.EvnXml.ItemsPanel'],
	controller: 'UslugaWindowController',
	cls: 'general-window',
	closeToolText: 'Закрыть окно общей услуги',
	noTaskBarButton: true,
	addCodeRefresh: function()
	{
		var wnd = this;

		if (IS_DEBUG)
		{
			if (!this.tools)
			{
				this.tools = [];
			}
			this.tools.push({
				type: 'refresh',
				margin: '0 15 0 5',
				hidden: (!IS_DEBUG),
				qtip: 'Окно в консоль',
				handler: function(event, toolEl, panel) {
					console.log(wnd);
				}
			});
		}
	},

	resizable: true,
	minWidth: 850,
	width: 980,
	height: 650,
	modal: true,
	closeAction: 'destroy',
	defaultLoadUrl: '/?c=EvnUsluga&m=loadEvnUslugaEditForm',
	loadUrl: '/?c=EvnUsluga&m=loadEvnUslugaEditForm',
	defaultSubmitUrl: '/?c=EvnUsluga&m=saveEvnUslugaCommon',
	submitUrl: '/?c=EvnUsluga&m=saveEvnUslugaCommon',
	bind: {
		title: '{title}'
	},

	viewModel: {

		data: {

			EvnUslugaCommon_id: null,
			changeUslugaComplexPlaceFilter: true,
			EvnClass_SysNick: 'EvnUslugaCommon',
			PrescriptionType_Code: null,
			withoutEvnDirection: 1,
			UslugaComplex_Date: null,
			LpuUnitType_Code: null,
			ignorePaidCheck: null,
			parentClass: 'EvnVizit',
			title: ' ',
			only351Group: null,
			editable: true,
			personPanel: {} // record of swPersonInfoModel
		}
	},

	layout: {
		type: 'vbox',
		align: 'stretch'
	},

	items: [
		{
			xtype: 'PersonInfoPanel2',
			hidden: true
		},

		{
			xtype: 'WhiteTabPanel',
			border: false,
			flex: 1,
			layout: 'fit',
			defaults: {
				scrollable: 'y',
				border: false
			},
			items: [
				{
					title: 'Услуга',
					items: [
						{xtype: 'UslugaEditForm'}
					]
				},{
					title: 'Осложнения',
					items: [
						{xtype: 'EvnAggGrid'},
						{
							margin: '20 0 0 30',
							xtype: 'button',
							ui: 'plain',
							cls: 'simple-button-link',
							text: 'Добавить осложнение',
							handler: 'openEvnAggEditWindow',
							bind: {
								disabled: '{editable === false}'
							}
						}
					],
					listeners: {
						activate: function () {
							var wnd = this.up('window'),
								formPanel = wnd.down('form'),
								gridPanel = wnd.down('EvnAggGrid'),
								EvnAgg_pid = formPanel.getViewModel().get('EvnUslugaCommon_id');




							if (EvnAgg_pid)
							{
								if ( ! this.isLoaded )
								{
									var loadMask = new Ext6.LoadMask(wnd, {msg: "Загрузка осложнений..."});
									loadMask.show();
									this.isLoaded = true;

									gridPanel.getStore().getProxy().setExtraParam('EvnAgg_pid', EvnAgg_pid);
									gridPanel.getStore().load({callback: () => loadMask.hide()});
								}
							}

							return;

						}
					}
				},{
					title: 'Специфика',
					scrollable: false,
					cls: 'evn-xml-tab-panel',
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'evnxmlitemspanel',
							maxCount: 1,
							border: false,
							collapsed: false,
							flex: 1,
							layout: 'fit',
							header: {
								hidden: true
							},
							bind: {
								disabled: '{editable === false}'
							}
						}
					],
					listeners: {
						activate: function ()
						{
							var wnd = this.up('window'),
								form = wnd.down('form').getForm(),
								action = wnd.getViewModel().get('action'),
								data = form.getValues(),
								Evn_id = data.EvnUslugaCommon_id,
								EvnXmlPanel = this.down('evnxmlitemspanel'),
								saveBtn = EvnXmlPanel.editorPanel.getToolbarButton('save'),
								allowedXmlTypeEvnClassLink;

							EvnXmlPanel.editorPanel.beforeSign = function(options) {
								// перед подписанием нужно сохранить услугу и документ
								wnd.getController().doSave({
									onSaveEvnXml: options.callback
								});
							};

							// этот код должен выполнится только при первом открытии
							if ( ! EvnXmlPanel.wasOpened )
							{
								EvnXmlPanel.wasOpened = true; // для того, что определить, сохранять при закрытии или нет
								saveBtn.disable();

								EvnXmlPanel.setParams({
									Person_id: data.Person_id,
									Evn_id: Evn_id,
									XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID,
									EvnClass_id: 22,
									LpuSection_id: data.LpuSection_uid,
									MedPersonal_id: data.MedPersonal_id,
									MedStaffFact_id: data.MedStaffFact_id
								});

								// если есть идентификатор услуги то пробуем загрузить документ
								if (Evn_id)
								{
									EvnXmlPanel.loadTabList();
									if (EvnXmlPanel.editorPanel.isDisabled() && action !== 'view')
									{
										EvnXmlPanel.editorPanel.enable();
									}
								} else
								{
									if (EvnXmlPanel.editorPanel.isDisabled() && action !== 'view')
									{
										EvnXmlPanel.editorPanel.enable();
									}
								}

								allowedXmlTypeEvnClassLink = {};
								allowedXmlTypeEvnClassLink[sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID] = [22];
								EvnXmlPanel.setAllowed([22], allowedXmlTypeEvnClassLink);
							}

							return;
						}
					}
				},{
					title: 'Файлы',
					listeners: {
						activate: function () {
							var wnd = this.up('window'),
								formPanel = wnd.down('form'),
								gridPanel = wnd.down('EvnMediaGrid'),
								Evn_id = formPanel.getViewModel().get('EvnUslugaCommon_id');

							if (Evn_id)
							{
								if ( ! this.isLoaded )
								{
									var loadMask = new Ext6.LoadMask(wnd, {msg: "Загрузка файлов..."});
									loadMask.show();

									this.isLoaded = true;
									gridPanel.getStore().getProxy().setExtraParam('Evn_id', Evn_id);
									gridPanel.getStore().load({callback: () => loadMask.hide()});
								}
							}

							return;

						}
					},
					items: [
						{xtype: 'EvnMediaGrid'},
						{xtype: 'FilesUploadForm', margin : '20 0 0 30'}
					]
				}
			]
		}
	],
	buttons: [
		'->',
		{xtype: 'SimpleButton', margin: 0},
		{
			xtype: 'SubmitButton',
			margin: '0 20 0 0',
			bind: {
				disabled: '{editable === false}'
			}
		}
	],

	/**
	 * methods
	 */

	doSave: function ()
	{
		return typeof this.specialDoSave === 'function' ? this.specialDoSave() : this.getController().doSave();

	},
	onSprLoad: function (args)
	{
		return this.getController().onSprLoad(args);
	},
	initComponent: function ()
	{
		this.callParent(arguments);

		this.getController().init();
	},

	listeners: {
		hide: function ()
		{

			typeof this.onHideFn === 'function' ? this.onHideFn() : null;

			return;
		}

	}
});
