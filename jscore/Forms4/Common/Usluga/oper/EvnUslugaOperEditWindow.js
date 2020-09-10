Ext6.define('UslugaOperViewModel', {
	extend: 'Ext6.app.ViewModel',
	alias: 'viewmodel.UslugaOperViewModel',

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
		isEco: false,
		isPriem: false,
		Server_id: null,
		Person_id: null,

		EvnUslugaOper_id: null,

		EvnCount: 0,
		EvnUslugaOper_pid: null,
		EvnUslugaOper_setDate: new Date(),
		EvnUslugaOper_setTime: new Date().format('H:i'),
		EvnUslugaOper_disDate: new Date(),
		EvnUslugaOper_disTime: new Date().format('H:i'),

		UslugaPlace_id: null,
		LpuSection_uid: null,
		LpuSectionProfile_id: null,

		Lpu_uid: null,
		Org_id: null,
		PayType_id: null,

		MedSpecOms_id: null,
		MedStaffFact_id: null,

		OperDiff_id: null,

		UslugaCategory_id: null,
		UslugaComplex_id: null,
		UslugaComplex_IsCabEarlyZno: null,
		UslugaComplexTariff_id: null,
		UslugaComplexTariff_UED: 0,
		EvnUslugaOper_Price: 0,
		EvnUslugaOper_Kolvo: 0,

		LoadUslugaComplex_id: null,
		LoadUslugaComplexTariff_id: null,

		formIsLoading: false,
		EvnMediaDataIds: [],

		UslugaCode: '',
		isCardio: false,

		EvnUslugaOper_IsOpenHeart: false,

		PersonData: {},

		UslugaExecutionType_id: null,
		UslugaExecutionReason_id: null
	},
	formulas: {
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
				vm.set('EvnUslugaOper_Price', 0);
				return;
			}

			if (UslugaComplexTariff_Tariff !== false)
			{
				vm.set('EvnUslugaOper_Price', isNaN( Number(UslugaComplexTariff_Tariff) ) ? 0 : Number(UslugaComplexTariff_Tariff) );
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
					add: langs('Добавить оперативную услугу'),
					edit: langs('Редактировать оперативную услугу'),
					view: langs('Просмотр оперативной услуги')
				};

			if ( ! action )
			{
				title = 'Форма оперативной услуги';
			}

			title = action ? titles[action] : langs('Форма оперативной услуги');

			wndVm.set('title', title);

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
		UslugaCode: function (get)
		{
			var formPanel = this.getView(),
				vm = formPanel.getViewModel(),
				form = formPanel.getForm(),
				UslugaComplex_id = get('UslugaComplex_id'),
				UslugaComplex = form.findField('UslugaComplex_id'),

				rec = UslugaComplex.getStore().getById(UslugaComplex_id),
				code = rec ? rec.get('UslugaComplex_Code').substr(0,3) : '';

			if ( ! rec )
			{ // при загрузке формы, подождем, вдруг стор услуги просто еще не загрузился
				setTimeout(function () {
					rec = UslugaComplex.getStore().getById(UslugaComplex_id);

					if (rec)
					{
						vm.set('UslugaCode', rec.get('UslugaComplex_Code').substr(0,3));
						vm.set('isCardio', rec.get('UslugaComplex_Code').inlist(['A16.12.004.008', 'A16.12.004.009']));
					} else {
						vm.set('isCardio', false);
					}
				}, 2000);
			} else {
				vm.set('isCardio', rec.get('UslugaComplex_Code').inlist(['A16.12.004.008', 'A16.12.004.009']));
			}

			return code;
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

Ext6.define('UslugaOperEditForm', {
	extend: 'GeneralFormPanel',
	alias: 'widget.UslugaOperEditForm',
	requires: ['usluga.oper.controllers.UslugaOperFormBindingsController', 'usluga.oper.models.UslugaOperFormModel', 'common.PersonInfoPanel.PersonInfoPanel2'],
	bodyPadding: '15 27 20 20',
	controller: 'UslugaOperFormBindingsController',
	viewModel: 'UslugaOperViewModel',
	border: false,
	reader: {
		type: 'json',
		model: 'usluga.oper.models.UslugaOperFormModel'
	},
	url: '/?c=EvnUsluga&m=saveEvnUslugaOper',

	// просто вынес скрытые поля, чтобы не мешали работать. В initComponent добавляю их к основным
	hiddenFields: [
		{
			name: 'accessType',
			value: '',
			xtype: 'hidden'
		}, {
			name: 'XmlTemplate_id',
			value: 0,
			xtype: 'hidden'
		}, {
			name: 'EvnUslugaOper_id',
			value: 0,
			xtype: 'hidden'
		}, {
			name: 'EvnUslugaOper_rid',
			value: 0,
			xtype: 'hidden'
		}, {
			name: 'Server_id',
			value: 0,
			xtype: 'hidden'
		}, {
			name: 'Morbus_id',
			value: 0,
			xtype: 'hidden'
		},
		{
			name:'IsCardioCheck',
			bind: '{isCardio ? 1 : 0}',
			xtype:'hidden'
		},
		{
			name: 'Evn_id',
			value: 0,
			bind: '{EvnUslugaOper_pid}',
			xtype: 'hidden'
		},{
			name: 'EvnDirection_id',
			xtype: 'hidden'
		}, {
			name: 'Person_id',
			value: 0,
			bind: '{Person_id}',
			xtype: 'hidden'
		}, {
			name: 'PersonEvn_id',
			value: 0,
			bind: '{PersonEvn_id}',
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
				value: '{EvnUslugaOper_pid}',
				disabled: '{(editable  === false) || EvnCount == 1 || parentClass == "EvnPLStom" || parentClass == "EvnVizit" || parentClass == "EvnSection"}',
				hidden: '{useCase === "OperBlock"}'
			},
			xtype: 'EventCombo',
			allowBlank: false,
			fieldLabel: langs('Отделение'),
			name: 'EvnUslugaOper_pid',
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
						value: '{EvnUslugaOper_setDate}',
						disabled: '{editable === false}'
					},
					name: 'EvnUslugaOper_setDate',
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
					name: 'EvnUslugaOper_setTime',
					bind: {
						value: '{EvnUslugaOper_setTime}',
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
		},{
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
					name: 'EvnUslugaOper_disDate',
					bind: {
						value: '{EvnUslugaOper_disDate}',
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
					name: 'EvnUslugaOper_disTime',
					bind: {
						value: '{EvnUslugaOper_disTime}',
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
		},
		{
			fieldLabel: langs('Отделение'),
			xtype: 'SwLpuSectionGlobalCombo',
			bind: {
				value: '{LpuSection_uid}',
				disabled: '{(editable === false) || (UslugaPlace_id ? UslugaPlace_id != 1 : 1 == 1)}'
			},
			allowBlank: false,
			queryMode: 'local',
			name: 'LpuSection_uid',

			linkedElements: [
				'EUOperEF_MedPersonalCombo'
			],
			linkedElementParams: {
				additionalFilterFn: checkSlaveRecordForLpuSectionService, // не работает
				ignoreFilter: false
			}

		},
		{
			fieldLabel: langs('Профиль'),
			xtype: 'LpuSectionProfileWithFedCombo',
			bind: {
				value: '{LpuSectionProfile_id}',
				allowBlank: '{ ! isOms }',
				disabled: getRegionNick().inlist(['perm']) ? '{editable === false}' : '{editable === false || (UslugaPlace_id ? UslugaPlace_id != 1 : 1 != 1)}'
			},
			hidden: getRegionNick().inlist(['astra']),
			name: 'LpuSectionProfile_id'
		},
		{
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
		},
		{
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
		},
		{
			fieldLabel: langs('Специальнсть'),
			name: 'MedSpecOms_id',
			queryMode: 'local',
			bind: {
				value: '{MedSpecOms_id}',
				hidden: getRegionNick() == 'perm' ? '{UslugaPlace_id ? UslugaPlace_id == 1 : 1 != 1}' : '{ 1 == 1}',
				allowBlank: getRegionNick() == 'perm' ? '{UslugaPlace_id ? ( UslugaPlace_id == 1 ? 1 == 1 : ! isOms) : 1 == 1 }' : '{1 == 1}',
				disabled: '{(editable === false)}'
			},
			xtype: 'MedSpecOmsWithFedCombo'
		},
		{
			fieldLabel: langs('Врач, выполнивший услугу'),
			xtype: 'swMedStaffFactCombo',
			queryMode: 'local',
			name: 'MedStaffFact_id',
			listConfig:{
				userCls: 'usluga-med-staff-fact-combo swMedStaffFactSearch'
			},
			bind: {
				value: '{MedStaffFact_id}',
				disabled: '{(editable === false) || (UslugaPlace_id ? UslugaPlace_id != 1 : 1 == 1)}',
				//allowBlank: '{(UslugaPlace_id ? UslugaPlace_id != 1 : false)}'
			},
			allowBlank: false
		},
		{
			fieldLabel: langs('Вид оплаты'),
			allowBlank: false,
			xtype: 'swPayTypeCombo',
			bind: {
				value: '{PayType_id}',
				disabled: '{editable == false}'
			},
			name: 'PayType_id'
		},
		{
			fieldLabel: langs('Назначение'),
			xtype: 'EvnPrescrCombo',
			queryMode: 'local',
			bind: {
				value: '{EvnPrescr_id}',
				disabled: '{editable == false }', // || EvnCount == 0
				hidden: '{useCase === "OperBlock"}'
			},
			name: 'EvnPrescr_id',
			// listeners: {
			// 	'change': function (combo, newValue) {
			// 		combo.applyChanges(newValue); // надо разобраться
			// 	}.createDelegate(this)
			// }
		},
		{
			fieldLabel: langs('Категория услуги'),
			xtype: 'swUslugaCategoryCombo',
			queryMode: 'local',
			//loadParams: uslugaCategoryParams, // сделать
			allowBlank: false,
			name: 'UslugaCategory_id',
			bind: {
				value: '{UslugaCategory_id}',
				disabled: '{editable == false || useCase === "OperBlock"}'
			}
		},
		{
			fieldLabel: langs('Услуга'),
			to: 'EvnUslugaOper',
			queryMode: 'remote',
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
			bind: {
				value: '{UslugaComplex_id}',
				disabled: '{editable == false || useCase === "OperBlock"}'
			},
			xtype: 'UslugaComplexCombo',
			allowBlank: false,
			name: 'UslugaComplex_id'
		},
		{
			fieldLabel: langs('Тариф'),
			xtype: 'UslugaComplexTariffCombo',
			name: 'UslugaComplexTariff_id',
			isLpuFilter: false,
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


					var rec = form.findField('EvnUslugaOper_pid').getStore().getById(form.findField('EvnUslugaOper_pid').getValue()),
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
					//maxWidth: 295,
					flex: 1,
					name: 'EvnUslugaOper_Price',
					bind: {
						value: '{EvnUslugaOper_Price}'
					}
				},
				{xtype: 'tbspacer', width: 30},
				{
					fieldLabel: langs('Количество'),
					labelWidth: 80,
					//maxWidth: 295,
					flex: 1,
					allowBlank: false,
					enableKeyEvents: true,
					value: 1,
					xtype: 'SimpleNumField',
					bind: {
						value: '{EvnUslugaOper_Kolvo}',
						disabled: '{editable === false}'
					},
					name: 'EvnUslugaOper_Kolvo'
				},
				{xtype: 'tbspacer', width: 30}
			]
		},
		{
			border: false,
			bind: {
				hidden: '{ ! isCardio}'
			},
			items: [
				{
					layout: {
						type: 'hbox',
						align: 'middle'
					},
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
							fieldLabel: langs('Дата и время начала раздувания баллона'),
							margin: '0 40 0 0',
							bind: {
								value: '{EvnUslugaOper_BallonBegDate}',
								disabled: '{editable === false}',
								allowBlank: '{ ! isCardio }'
							},
							name: 'EvnUslugaOper_BallonBegDate',
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
							width: '15%',
							hideLabel: true,
							name: 'EvnUslugaOper_BallonBegTime',
							bind: {
								value: '{EvnUslugaOper_BallonBegTime}',
								disabled: '{editable === false}',
								allowBlank: '{ ! isCardio }'
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
					layout: {
						type: 'hbox',
						align: 'middle'
					},
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
							fieldLabel: langs('Дата и время окончания ЧКВ'),
							margin: '0 40 0 0',
							bind: {
								value: '{EvnUslugaOper_CKVEndDate}',
								disabled: '{editable === false}',
								allowBlank: '{ ! isCardio }'
							},
							name: 'EvnUslugaOper_CKVEndDate',
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
							name: 'EvnUslugaOper_CKVEndTime',
							bind: {
								value: '{EvnUslugaOper_CKVEndTime}',
								disabled: '{editable === false}',
								allowBlank: '{ ! isCardio }'
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
				}
			]
		},



		// {
		// 	allowNegative: false,
		// 	enableKeyEvents: true,
		// 	fieldLabel: langs('Коэффициент изменения тарифа'),
		// 	minValue: 0.000001,
		// 	name: 'EvnUslugaOper_CoeffTariff',
		// 	tabIndex: TABINDEX_EUOPEREF + 24,
		// 	width: 150,
		// 	xtype: 'numberfield'
		// }


		{
			fieldLabel: langs('Тип операции'),
			xtype: 'commonSprCombo',
			comboSubject: 'OperType',
			allowBlank: false,
			name: 'OperType_id',
			bind: {
				disabled: '{editable === false}'
			}
		},
		{
			fieldLabel: langs('Категория сложности'),
			xtype: 'commonSprCombo',
			comboSubject: 'OperDiff',
			allowBlank: false,
			name: 'OperDiff_id',
			bind: {
				disabled: '{editable === false}',
				value: '{OperDiff_id}'
			}
		},

		{
			fieldLabel: langs('Условие лечения'),
			name: 'TreatmentConditionsType_id',
			comboSubject: 'TreatmentConditionsType',
			xtype: 'commonSprCombo',
			bind: {
				disabled: '{editable === false}'
			}
		},
		{
			layout: {
				type: 'hbox'
			},
			border: false,
			items: [
				{xtype: 'tbspacer', width: 170 + 5},
				{
					xtype: 'checkbox',
					boxLabel: 'Смерть наступила на операционном столе',
					name: 'EvnUslugaOper_IsOperationDeath',
					inputValue: '2',
					uncheckedValue: '1',
					bind: {
						hidden: '{UslugaCode !== "A16"}',
						disabled: '{editable === false}'
					}
				}
			]
		},
		{
			border: false,
			layout: {
				type: 'hbox'
			},
			items: [
				{xtype: 'tbspacer', width: 170 + 5},
				{
					title: 'Операция',
					xtype: 'fieldset',
					cls: 'fieldset-checkbox-group',
					flex: 1,
					items: [{
						xtype: 'checkboxgroup',
						columns: [0.4, 0.6],
						vertical: true,
						items: [
							{ boxLabel: langs('Применение ВМТ'), name: 'EvnUslugaOper_IsVMT', bind: {disabled: '{editable === false}'}, uncheckedValue: '1', inputValue: '2' },
							{ boxLabel: langs('На открытом сердце'), name: 'EvnUslugaOper_IsOpenHeart', uncheckedValue: '1', inputValue: '2', bind: {value: '{EvnUslugaOper_IsOpenHeart}', disabled: '{editable === false}'} },
							{ boxLabel: langs('Микрохирургическая'), name: 'EvnUslugaOper_IsMicrSurg', bind: {disabled: '{editable === false}'}, uncheckedValue: '1', inputValue: '2' },
							{ boxLabel: langs('С искусственным кровообращением'), name: 'EvnUslugaOper_IsArtCirc', uncheckedValue: '1', inputValue: '2', bind: {disabled: '{(editable === false) || (EvnUslugaOper_IsOpenHeart == true)}'}}
						]

					}]
				}
			]
		},

		{
			border: false,
			layout: {
				type: 'hbox'
			},
			items: [
				{xtype: 'tbspacer', width: 170 + 5},
				{
					title: 'Признаки использования аппаратуры',
					xtype: 'fieldset',
					cls: 'fieldset-checkbox-group',
					flex: 1,
					items: [
						{
							xtype: 'checkboxgroup',
							columns: [0.4, 0.6],
							vertical: true,
							items: [
								{ allowBlank: false, boxLabel: langs('Эндоскопическая'), name: 'EvnUslugaOper_IsEndoskop', bind: {disabled: '{editable === false}'}, uncheckedValue: '1', inputValue: '2'},
								{ allowBlank: false, boxLabel: langs('Криогенная'), name: 'EvnUslugaOper_IsKriogen', bind: {disabled: '{editable === false}'}, uncheckedValue: '1', inputValue: '2'},
								{ allowBlank: false, boxLabel: langs('Лазерная'), name: 'EvnUslugaOper_IsLazer', bind: {disabled: '{editable === false}'}, uncheckedValue: '1', inputValue: '2'},
								{ allowBlank: false, boxLabel: langs('Рентгенологическая'), name: 'EvnUslugaOper_IsRadGraf', bind: {disabled: '{editable === false}'}, uncheckedValue: '1', inputValue: '2'}
							]
						}
					]
				}]
		},
	],


	initComponent: function ()
	{
		this.items = Array.prototype.concat(this.hiddenFields, this.items);
		this.callParent(arguments);
	}
});




Ext6.define('usluga.oper.EvnUslugaOperEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.EvnUslugaOperEditWindow',
	requires: ['usluga.oper.controllers.UslugaOperWindowController',
		'usluga.components.OperBrigGrid', 'usluga.components.EvnMediaGrid',
		'usluga.components.EvnAggGrid', 'usluga.components.FilesUploadForm',
		'usluga.components.OperAnestGrid', 'usluga.components.EvnDrugGrid',
		 'common.PersonInfoPanel.PersonInfoPanel2', 'common.EvnXml.ItemsPanel'],
	controller: 'UslugaOperWindowController',
	cls: 'general-window',
	closeToolText: 'Закрыть окно оперативной услуги',
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
	minWidth: 900,
	width: 980,
	height: 650,
	modal: true,
	closeAction: 'destroy',
	bind: {
		title: '{title}'
	},

	viewModel: {

		data: {

			EvnUslugaOper_id: null,
			changeUslugaComplexPlaceFilter: true,
			PrescriptionType_Code: null,
			withoutEvnDirection: 1,
			UslugaComplex_Date: null,
			LpuUnitType_Code: null,
			ignorePaidCheck: null,
			EvnClass_SysNick: 'EvnUslugaOper',
			parentClass: 'EvnVizit',
			title: 'Оперативная услуга',
			only351Group: null,
			editable: true,
			personPanel: {},
			useCase: ''
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
					items: [{xtype: 'UslugaOperEditForm'}]
				},
				{
					title: 'Операционная бригада',
					items: [
						{xtype: 'OperBrigGrid'},
						{
							margin: '20 0 0 30',
							xtype: 'button',
							ui: 'plain',
							cls: 'simple-button-link',
							text: 'Добавить врача',
							handler: 'openEvnUslugaOperBrigEditWindow',
							bind: {
								disabled: '{editable === false}'
							}
						}
					],
					listeners: {
						activate: function ()
						{
							var wnd = this.up('window'),
								formPanel = wnd.down('form'),
								gridPanel = this.down('grid'),
								EvnUslugaOperBrig_pid = formPanel.getViewModel().get('EvnUslugaOper_id');

							if (EvnUslugaOperBrig_pid)
							{
								if ( ! this.isLoaded )
								{
									this.isLoaded = true;

									gridPanel.getStore().getProxy().setExtraParam('EvnUslugaOperBrig_pid', EvnUslugaOperBrig_pid);
									gridPanel.getStore().load();
								}
							}

							return;
						}
					}
				},
				{
					title: 'Анестезия',
					items: [
						{xtype: 'OperAnestGrid'},
						{
							margin: '20 0 0 30',
							xtype: 'button',
							ui: 'plain',
							cls: 'simple-button-link',
							text: 'Добавить анестезию',
							handler: 'openEvnUslugaOperAnestEditWindow',
							bind: {
								disabled: '{editable === false}'
							}
						}
					],
					listeners: {
						activate: function ()
						{
							var wnd = this.up('window'),
								formPanel = wnd.down('form'),
								gridPanel = this.down('grid'),
								EvnUslugaOperAnest_pid = formPanel.getViewModel().get('EvnUslugaOper_id');

							if (EvnUslugaOperAnest_pid)
							{
								if ( ! this.isLoaded )
								{
									this.isLoaded = true;

									gridPanel.getStore().getProxy().setExtraParam('EvnUslugaOperAnest_pid', EvnUslugaOperAnest_pid);
									gridPanel.getStore().load();
								}
							}

							return;
						}
					}
				},
				{
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
								EvnAgg_pid = formPanel.getViewModel().get('EvnUslugaOper_id');

							if (EvnAgg_pid)
							{
								if ( ! this.isLoaded )
								{
									this.isLoaded = true;
									gridPanel.getStore().getProxy().setExtraParam('EvnAgg_pid', EvnAgg_pid);
									gridPanel.getStore().load();
								}
							}

							return;
						}
					}
				},
				{
					title: 'Медикаменты',
					items: [
						{xtype: 'EvnDrugGrid'},
						{
							margin: '20 0 0 30',
							xtype: 'button',
							ui: 'plain',
							cls: 'simple-button-link',
							text: 'Добавить медикамент',
							handler: 'openEvnDrugEditWindow',
							bind: {
								disabled: '{editable === false}'
							}
						}
					],
					listeners: {
						activate: function () {

							var wnd = this.up('window'),
								formPanel = wnd.down('form'),
								gridPanel = wnd.down('EvnDrugGrid'),
								EvnDrug_pid = formPanel.getViewModel().get('EvnUslugaOper_id');

							if (EvnDrug_pid)
							{
								if ( ! this.isLoaded )
								{
									this.isLoaded = true;
									gridPanel.getStore().getProxy().setExtraParam('EvnDrug_pid', EvnDrug_pid);
									gridPanel.getStore().load();
								}
							}

							return;
						}
					}
				}, {
					title: 'Протокол',
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
								Evn_id = data.EvnUslugaOper_id || null,
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
									XmlType_id: sw.Promed.EvnXml.OPERATION_PROTOCOL_TYPE_ID,
									EvnClass_id: 43,
									LpuSection_id: data.LpuSection_uid,
									//MedPersonal_id: data.MedPersonal_id,
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

								(allowedXmlTypeEvnClassLink = {})[sw.Promed.EvnXml.OPERATION_PROTOCOL_TYPE_ID] = [43];
								EvnXmlPanel.setAllowed([43], allowedXmlTypeEvnClassLink);
							}

							return;
						}
					}
				}, {
					title: 'Файлы',
					items: [
						{xtype: 'EvnMediaGrid'},
						{xtype: 'FilesUploadForm', margin :'20 0 0 30'}
					],
					listeners: {
						activate: function () {

							var wnd = this.up('window'),
								formPanel = wnd.down('form'),
								gridPanel = wnd.down('EvnMediaGrid'),
								Evn_id = formPanel.getViewModel().get('EvnUslugaOper_id');

							if (Evn_id)
							{
								if ( ! this.isLoaded )
								{
									this.isLoaded = true;
									gridPanel.getStore().getProxy().setExtraParam('Evn_id', Evn_id);
									gridPanel.getStore().load();
								}
							}

							return;

						}
					}
				}
			]
		}
	],
	buttons: [
		'->',
		{xtype: 'SimpleButton'},
		{
			xtype: 'SubmitButton',
			bind: {
				disabled: '{editable === false}'
			}
		}
	],

	doSave: function ()
	{
		return this.getController().doSave();
	},

	onSprLoad: function (args)
	{
		return this.getController().onSprLoad(args);
	},

	initComponent: function ()
	{
		// var uslugaCategoryParams = null;
		// switch (getRegionNick()) {
		// 	case 'kz':
		// 		uslugaCategoryParams = {params: {where: "where UslugaCategory_SysNick in ('classmedus', 'MedOp')"}};
		// 		break;
		// 	case 'kaluga':
		// 		uslugaCategoryParams = {params: {where: "where UslugaCategory_SysNick in ('gost2011', 'lpusectiontree')"}};
		// 		break;
		// }

		this.callParent(arguments);
	}
});