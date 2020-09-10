/**
 * swEvnDirectionHTMRegistryWindow - окно просмотра регистра направлений на ВМП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Htm
 * @access       public
 * @copyright    Copyright (c) 2009-2019 Swan Ltd.
 * @author
 * @version      11.2019
 *
 */
sw.Promed.swEvnDirectionHTMRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnDirectionHTMRegistryWindow',
	objectName: 'swEvnDirectionHTMRegistryWindow',

	title: lang['HTM_registry'],

	maximized: true,
	layout: 'border',

	// Кнопки "Помощь" и "Закрыть":
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',

			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text: lang['zakryit'],
			tabIndex: -1,
			tooltip: lang['zakryit'],
			iconCls: 'cancel16',

			handler: function() {
				this.ownerCt.hide();
			}
		}
	],

	userMedStaffFact: undefined,

	// Статусы направлений:
	EVN_STATUS_NEW: 36, // новое
	EVN_STATUS_SERVICED: 37, // обслужено
	EVN_STATUS_CANCELED: 38, // отменено

	// Поля панели даты:
	_dtPeriod: undefined,
	_btnDay: undefined,
	_btnWeek: undefined,
	_btnMonth: undefined,

	// Поля фильтра:
	_frmFilter: undefined,
	_cmbStatus: undefined,
	_cmbHTMFinance: undefined,
	_cmbHTMedicalCareType: undefined,
	_cmbHTMedicalCareClass: undefined,

	_cmbLpu: undefined,
	_cmbRegion: undefined,
	_cmbLpuHtm: undefined,

	// Панель и сами таблицы:
	_tabPanel: undefined,
	_gridWaitList: undefined,
	_gridServiced: undefined,

	// Фильтр статуса на панели листа ожидания:
	_statusFilter: undefined,

	// Флаги инициализации:
	_loadListsDone: false, // списки загружены
	_initFilterDone: false, // фильтр подготовлен
	_initMask: false, // отображается маска

	// Для фильтрации метода ВМП:
	_curHTMFinance_Code: undefined, // источник финансирования
	_curStartDate: undefined, // дата
	_htMedicalCareClassLink_ids: undefined, // массив допустимых idов методов ВМП
	_htmcclRequestId: undefined, // id запроса допустимых idов

	/******* initComponent ********************************************************
	 *
	 ******************************************************************************/
	initComponent: function() {
		// Панель с таблицами:
		this._tabPanel = new Ext.TabPanel({
			border: false,
			region: 'center',
			deferredRender: false,

			listeners: {
				tabchange: this._onTabChange,
				scope: this
			}
		});

		// Лист ожидания:
		this._gridWaitList = new sw.Promed.ViewFrame({
			actions: [{
					name: 'action_add',
					text: lang['sozdat'],
					handler: () => this._onAction_add()
				},
				{
					name: 'action_edit',
					disabled: true,
					hidden: true
				},
				{
					name: 'action_view',
					text: lang['prosmotret'],
					handler: () => this._onAction_view('WaitList')
				},
				{
					name: 'action_delete',
					disabled: true,
					hidden: true
				},
				{
					name: 'action_print'
				}
			],

			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=EvnDirectionHTM&m=loadEvnDirectionHTMRegistry',
			root: 'data',
			totalProperty: 'totalCount',
			id: 'EDHTMRW_EvnDirectionHTMGrid_WaitList',
			object: 'EvnDirectionHTM',
			paging: true,
			useEmptyRecord: false,

			stringfields: [{
					name: 'EvnDirectionHTM_id',
					type: 'int',
					header: 'ID',
					key: true
				},
				{
					name: 'Person_id',
					type: 'int',
					hidden: true
				},
				{
					name: 'PersonEvn_id',
					type: 'int',
					hidden: true
				},
				{
					name: 'Person_FIO',
					type: 'string',
					header: lang['patsient'],
					width: 280
				},
				{
					name: 'Person_BirthDay',
					type: 'date',
					header: lang['dr'],
					width: 100
				},
				{
					name: 'Diag_FullName',
					header: lang['diagnoz'],
					type: 'string',
					width: 200
				},
				{
					name: 'EvnDirectionHTM_directDate',
					type: 'date',
					header: lang['data_napravleniya'],
					width: 100
				},
				{
					name: 'EvnDirectionHTM_Num',
					type: 'int',
					header: lang['nomer_napravleniya']
				},
				{
					name: 'HTMFinance_Name',
					header: lang['istochnik_finansirovaniya'],
					type: 'string',
					width: 150
				},
				{
					name: 'EvnStatus_id',
					type: 'int',
					hidden: true
				},
				{
					name: 'EvnStatus_Name',
					header: lang['status'],
					type: 'string',
					width: 100
				},
				{
					name: 'EvnStatusCause_Name',
					header: lang['prichina_ustanovki_statusa'],
					type: 'string',
					width: 100
				},
				{
					name: 'EvnDirectionHTM_statusDate',
					type: 'date',
					header: lang['data_ustanovki_statusa'],
					width: 100
				},
				{
					name: 'EvnDirectionHTM_disDate',
					type: 'date',
					header: lang['data_zakrytiya_kvs'],
					width: 100
				},
				{
					name: 'Lpu_id',
					type: 'int',
					hidden: true
				},
				{
					name: 'Lpu_Name',
					header: lang['kem_napravlen'],
					type: 'string',
					width: 200
				},
				{
					name: 'MedStaffFact_FullName',
					header: lang['vrach_vyipisavshiy_napravlenie'],
					type: 'string',
					width: 200
				},
				{
					name: 'Region_id',
					type: 'int',
					hidden: true
				},
				{
					name: 'LpuHTM_FullName',
					header: lang['kuda_napravlen'],
					type: 'string',
					width: 200
				},
				{
					name: 'LpuSectionProfile_Name',
					header: lang['profil'],
					type: 'string',
					width: 200
				},
				{
					name: 'HTMedicalCareType_Name',
					header: lang['vid_vmp'],
					type: 'string',
					width: 200
				},
				{
					name: 'HTMedicalCareClass_Name',
					header: lang['metod_vmp'],
					type: 'string',
					width: 200
				}
			]
		});

		// Обслуженные направления:
		this._gridServiced = new sw.Promed.ViewFrame({
			actions: [{
					name: 'action_add',
					disabled: true,
					hidden: true
				},
				{
					name: 'action_edit',
					disabled: true,
					hidden: true
				},
				{
					name: 'action_view',
					text: lang['prosmotret'],
					handler: () => this._onAction_view('Serviced')
				},
				{
					name: 'action_delete',
					disabled: true,
					hidden: true
				},
				{
					name: 'action_print'
				}
			],

			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=EvnDirectionHTM&m=loadEvnDirectionHTMRegistry',
			root: 'data',
			totalProperty: 'totalCount',
			id: 'EDHTMRW_EvnDirectionHTMGrid_Serviced',
			object: 'EvnDirectionHTM',
			paging: true,
			useEmptyRecord: false,

			stringfields: [{
					name: 'EvnDirectionHTM_id',
					type: 'int',
					header: 'ID',
					key: true
				},
				{
					name: 'Person_id',
					type: 'int',
					hidden: true
				},
				{
					name: 'PersonEvn_id',
					type: 'int',
					hidden: true
				},
				{
					name: 'Person_FIO',
					type: 'string',
					header: lang['patsient'],
					width: 280
				},
				{
					name: 'Person_BirthDay',
					type: 'date',
					header: lang['dr'],
					width: 100
				},
				{
					name: 'Diag_FullName',
					header: lang['diagnoz'],
					type: 'string',
					width: 200
				},
				{
					name: 'EvnDirectionHTM_directDate',
					type: 'date',
					header: lang['data_napravleniya'],
					width: 100
				},
				{
					name: 'EvnDirectionHTM_Num',
					type: 'int',
					header: lang['nomer_napravleniya']
				},
				{
					name: 'HTMFinance_Name',
					header: lang['istochnik_finansirovaniya'],
					type: 'string',
					width: 150
				},
				{
					name: 'EvnStatus_Name',
					header: lang['status'],
					type: 'string',
					width: 100
				},
				{
					name: 'EvnStatusCause_Name',
					header: lang['prichina_ustanovki_statusa'],
					type: 'string',
					width: 100
				},
				{
					name: 'EvnDirectionHTM_statusDate',
					type: 'date',
					header: lang['data_ustanovki_statusa'],
					width: 100
				},
				{
					name: 'EvnDirectionHTM_disDate',
					type: 'date',
					header: lang['data_zakrytiya_kvs'],
					width: 100
				},
				{
					name: 'Lpu_Name',
					header: lang['kem_napravlen'],
					type: 'string',
					width: 200
				},
				{
					name: 'MedStaffFact_FullName',
					header: lang['vrach_vyipisavshiy_napravlenie'],
					type: 'string',
					width: 200
				},
				{
					name: 'LpuHTM_FullName',
					header: lang['kuda_napravlen'],
					type: 'string',
					width: 200
				},
				{
					name: 'LpuSectionProfile_Name',
					header: lang['profil'],
					type: 'string',
					width: 200
				},
				{
					name: 'HTMedicalCareType_Name',
					header: lang['vid_vmp'],
					type: 'string',
					width: 200
				},
				{
					name: 'HTMedicalCareClass_Name',
					header: lang['metod_vmp'],
					type: 'string',
					width: 200
				}
			]
		});

		this._gridWaitList.title = lang['list_ozhidaniya'];
		this._gridServiced.title = lang['obslujennyie_napravleniya'];

		this._tabPanel.add(this._gridWaitList);
		this._tabPanel.add(this._gridServiced);
		this._tabPanel.setActiveTab(this._gridWaitList);

		this.items = [{
				xtype: 'container',
				autoEl: {},
				region: 'north',
				autoHeight: true,

				items: [
					// Панель даты:
					{
						xtype: 'toolbar',

						items: [{
								xtype: 'button',
								text: lang['predyiduschiy'],
								iconCls: 'arrow-previous16',
								handler: () => this._stepDay(-1)
							},
							'-',
							{
								xtype: 'daterangefield',
								itemId: 'dtPeriod',
								fieldLabel: lang['period'],
								width: 150,
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],

								listeners: {
									select: this._clearToggles,
									scope: this
								}
							},
							'-',
							{
								xtype: 'button',
								text: lang['sleduyuschiy'],
								iconCls: 'arrow-next16',
								handler: () => this._stepDay(1)
							},
							'-',
							'->',
							'-',
							{
								xtype: 'button',
								itemId: 'btnDay',
								text: lang['den'],
								toggleGroup: 'periodToggle',
								iconCls: 'datepicker-day16',
								handler: this._onClick_curDay,
								scope: this
							},
							{
								xtype: 'button',
								itemId: 'btnWeek',
								text: lang['nedelya'],
								toggleGroup: 'periodToggle',
								iconCls: 'datepicker-week16',
								handler: this._onClick_curWeek,
								scope: this
							},
							{
								xtype: 'button',
								itemId: 'btnMonth',
								text: lang['mesyats'],
								toggleGroup: 'periodToggle',
								iconCls: 'datepicker-month16',
								handler: this._onClick_curMonth,
								scope: this
							}
						]
					},

					// Фильтр:
					{
						xtype: 'form',
						itemId: 'frmFilter',
						border: false,
						bodyStyle: 'background: transparent',
						layout: 'column',
						labelAlign: 'right',
						defaults: {
							labelWidth: 150
						},

						items: [{
								xtype: 'container',
								autoEl: {},
								layout: 'form',
								columnWidth: 1 / 3,
								style: "margin: 5px",

								items: [{
										xtype: 'swevnstatuscombo',
										itemId: 'cmbStatus',
										hiddenName: 'EvnStatus_id',
										fieldLabel: lang['status'],
										anchor: '100%'
									},
									{
										xtype: 'swcommonsprcombo',
										itemId: 'cmbHTMFinance',
										comboSubject: 'HTMFinance',
										fieldLabel: lang['istochnik_finansirovaniya'],
										anchor: '100%',

										listeners: {
											change: this._filterHTMedicalCareClass,
											scope: this
										}
									},
									{
										xtype: 'swcommonsprcombo',
										itemId: 'cmbHTMedicalCareType',
										comboSubject: 'HTMedicalCareType',

										moreFields: [{
												name: 'HTMedicalCareType_begDate',
												type: 'date',
												dateFormat: 'd.m.Y'
											},
											{
												name: 'HTMedicalCareType_endDate',
												type: 'date',
												dateFormat: 'd.m.Y'
											}
										],

										fieldLabel: lang['vid_vmp'],
										listWidth: 600,
										anchor: '100%',

										listeners: {
											change: this._filterHTMedicalCareClass,
											scope: this
										}
									},
									{
										xtype: 'container',
										autoEl: {},
										layout: 'form',

										items: [{
											xtype: 'swcommonsprcombo',
											itemId: 'cmbHTMedicalCareClass',
											comboSubject: 'HTMedicalCareClass',

											moreFields: [{
													name: 'HTMedicalCareClass_begDate',
													type: 'date',
													dateFormat: 'd.m.Y'
												},
												{
													name: 'HTMedicalCareClass_endDate',
													type: 'date',
													dateFormat: 'd.m.Y'
												},
												{
													name: 'HTMedicalCareType_id',
													type: 'int'
												}
											],

											fieldLabel: lang['metod_vmp'],
											listWidth: 600,
											anchor: '100%'
										}]
									}
								]
							},
							{
								xtype: 'container',
								autoEl: {},
								layout: 'form',
								columnWidth: 1 / 3,
								style: "margin: 5px",

								items: [{
										xtype: 'swlpucombo',
										itemId: 'cmbLpu',
										fieldLabel: lang['napravivshaya_mo'],
										anchor: '100%'
									},
									{
										xtype: 'numberfield',
										name: 'EvnDirectionHTM_Num',
										fieldLabel: lang['nomer_napravleniya'],
										anchor: '100%'
									},
									{
										xtype: 'fieldset',
										title: lang['kuda_napravlen'],
										autoHeight: true,
										labelWidth: 139,
										style: "padding: 5px",

										items: [{
												xtype: 'swregioncombo',
												itemId: 'cmbRegion',
												hiddenName: 'HTMRegion_id',
												fieldLabel: lang['region'],
												anchor: '100%',

												listeners: {
													change: this._onChange_region,
													scope: this
												}
											},
											{
												xtype: 'swlpuhtmcombo',
												itemId: 'cmbLpuHtm',
												anchor: '100%'
											}
										]
									}
								]
							},
							{
								xtype: 'fieldset',
								title: lang['patsient'],
								autoHeight: true,

								columnWidth: 1 / 3,
								style: "margin: 5px; padding: 5px",

								items: [{
										xtype: 'textfield',
										anchor: '100%',
										name: 'Person_SurName',
										fieldLabel: lang['familiya']
									},
									{
										xtype: 'textfield',
										anchor: '100%',
										name: 'Person_FirName',
										fieldLabel: lang['imya']
									},
									{
										xtype: 'textfield',
										anchor: '100%',
										name: 'Person_SecName',
										fieldLabel: lang['otchestvo']
									},
									{
										xtype: 'swdatefield',
										name: 'Person_BirthDay',
										fieldLabel: lang['data_rojdeniya'],
										format: 'd.m.Y',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
									}
								]
							}
						]
					},

					// Панель инструментов:
					{
						xtype: 'toolbar',
						autoCreate: {
							html: '<table cellspacing="0"><tr></tr></table>'
						},

						items: [
							'->',
							{
								xtype: 'button',
								text: BTN_FRMSEARCH,
								iconCls: 'search16',
								handler: this._onClick_search,
								scope: this
							},
							{
								xtype: 'button',
								text: lang['sbros'],
								iconCls: 'resetsearch16',
								style: "margin: 0px 6px 0px 6px",
								handler: this._onClick_clear,
								scope: this
							}
						]
					}
				]
			},

			// Таблицы:
			this._tabPanel
		];

		sw.Promed.swEvnDirectionHTMRegistryWindow.superclass.initComponent.apply(this, arguments);

		setTimeout(() => this._finishInitComponent(), 1);
	},

	/******* _finishInitComponent *************************************************
	 * Инициализация компонентов по готовности модуля.
	 ******************************************************************************/
	_finishInitComponent: function() {
		// Инициализация переменных - ссылок на компоненты:
		this.items.each(this._findComponents, this);

		// По умолчанию период - месяц:
		this._btnMonth.toggle(true);
		this._onClick_curMonth();

		// Кнопки и обработчики таблиц:
		this._gridWaitList.addActions({
				name: 'action_cancel_dir',
				text: lang['otmenit'],
				disabled: true,
				handler: this._onAction_cancelDir,
				scope: this
			},
			3);

		this._gridWaitList.addActions({
				name: 'action_open_emk',
				text: lang['otkryit_emk'],
				disabled: true,
				handler: this._onAction_openEmk,
				scope: this,
				_grid: this._gridWaitList
			},
			4);

		this._gridWaitList.addActions({
				name: 'action_serviced',
				text: lang['obslujeno'],
				disabled: true,
				handler: this._onAction_serviced,
				scope: this
			},
			5);

		this._gridWaitList.getGrid().getSelectionModel()
			.addListener('selectionchange', this._onSelChange, {
				grid: this._gridWaitList,
				win: this
			});

		this._gridServiced.addActions({
				name: 'action_open_emk',
				text: lang['otkryit_emk'],
				disabled: true,
				handler: this._onAction_openEmk,
				scope: this,
				_grid: this._gridServiced
			},
			3);

		this._gridServiced.getGrid().getSelectionModel()
			.addListener('selectionchange', this._onSelChange, {
				grid: this._gridServiced,
				win: this
			});

		this._callAfterInit(() => this._loadListsDone,
			this._initFilter);
	},

	/******* afterRender **********************************************************
	 * Если инициализация не завершена, покажем маску.
	 ******************************************************************************/
	afterRender: function() {
		sw.Promed.swEvnDirectionHTMRegistryWindow.superclass.afterRender.apply(this, arguments);

		if (!this._initFilterDone) {
			this.showLoadMask();
			this._initMask = true;
		}
	},

	/******* _findComponents ******************************************************
	 * Инициализация переменных - ссылок на компоненты.
	 *  item - компонент ExtJs.
	 ******************************************************************************/
	_findComponents: function(item) {
		if (item.itemId)
			this['_' + item.itemId] = item;

		if (item.items)
			item.items.each(this._findComponents, this);
	},

	/******* _callAfterInit *******************************************************
	 * Вызов функции по условию.
	 *  flagFn - функция - условие,
	 *  execFn - вызываемая функция,
	 *  scope  - контекст вызываемой функции, по умолчанию - окно,
	 *  params - массив параметров вызываемой функции.
	 *
	 * Функция execFn вызывается один раз после того, как flagFn вернет true.
	 ******************************************************************************/
	_callAfterInit: function(flagFn, execFn, scope, params) {
		if (flagFn())
			execFn.apply(scope || this, params || []);
		else
			setTimeout(() => this._callAfterInit(flagFn, execFn, scope, params), 1);
	},

	/******* loadDataLists ********************************************************
	 * Загрузка списков.
	 * Устанавливает флаг _loadListsDone.
	 ******************************************************************************/
	loadDataLists: function(args, lists, noclose, callback) {
		var me = this,
			newArgs = Ext.apply({}, arguments);

		newArgs[3] = ldlCallback;
		newArgs.length = 4;

		sw.Promed.swEvnDirectionHTMRegistryWindow.superclass.loadDataLists.apply(this, newArgs);

		/******* ldlCallback **********************************************************
		 *
		 */
		function ldlCallback() {
			me._loadListsDone = true;

			if (callback)
				callback();
		}
	},

	/******* _initFilter **********************************************************
	 * Инициализация фильтра.
	 * Загрузка и фильтрация списков.
	 ******************************************************************************/
	_initFilter: function() {
		this._filterStatus();
		this._filterHTMedicalCareType();
		this._filterHTMedicalCareClass();
		this._initFilterDone = true;

		this._cmbRegion.getStore().load({
			params: {
				country_id: 643,
				level: 1,
				value: 0
			},

			callback: function() {
				if (this._initMask)
					this.hideLoadMask();
			},

			scope: this
		});
	},

	/******* _filterStatus ********************************************************
	 * Фильтрация статуса.
	 * Только статусы направлений ВМП.
	 ******************************************************************************/
	_filterStatus: function() {
		var store = this._cmbStatus.getStore();

		this._cmbStatus.lastQuery = '';
		store.clearFilter();

		store.filterBy((item) => item.get('EvnStatus_id')
			.inlist([this.EVN_STATUS_NEW,
				this.EVN_STATUS_SERVICED,
				this.EVN_STATUS_CANCELED
			]));
	},

	/******* _filterHTMedicalCareType *********************************************
	 * Фильтрация вида ВМП.
	 * Виды, действующие на дату начала периода.
	 ******************************************************************************/
	_filterHTMedicalCareType: function() {
		var cmb = this._cmbHTMedicalCareType,
			store = cmb.getStore(),
			value = cmb.getValue(),
			isValueValid = false,
			date = this._dtPeriod.getValue1();

		cmb.lastQuery = '';
		store.clearFilter();

		store.filterBy(function(item) {
			isValueValid = (isValueValid || item.get('HTMedicalCareType_id') == value);

			return ((item.get('HTMedicalCareType_begDate') || date) <= date &&
				(item.get('HTMedicalCareType_endDate') || date) >= date);
		});

		// Если текущее значение отфильтровано, очистим комбобокс:
		if (!isValueValid)
			cmb.clearValue();
	},

	/******* _filterHTMedicalCareClass ********************************************
	 * Фильтрация метода ВМП.
	 * Фильтрация по дате, типу ВМП, источнику финансирования.
	 ******************************************************************************/
	_filterHTMedicalCareClass: function() {
		var me = this,
			cmb = this._cmbHTMedicalCareClass,
			htmctId = this._cmbHTMedicalCareType.getValue(),
			doRequest = false, // выполнить запрос допустимых методов ВМП
			maskEl, // маска
			v;

		// Изменился источник финансирования:
		if ((v = this._cmbHTMFinance.getFieldValue('HTMFinance_Code')) !=
			this._curHTMFinance_Code) {
			this._curHTMFinance_Code = v;
			doRequest = true;
		}

		// Изменилась дата:
		if ((v = this._dtPeriod.getValue1()) != this._curStartDate &&
			(!v || !this._curStartDate || v.valueOf() != this._curStartDate.valueOf())) {
			this._curStartDate = v;
			doRequest = true;
		}

		// Запрос методов ВМП по источнику финансирования и дате:
		if (doRequest) {
			(maskEl = this._cmbHTMedicalCareClass.ownerCt) && (maskEl = maskEl.getEl());

			if (maskEl)
				maskEl.mask().applyStyles({
					'background-image': 'url(/css/themes/blue/images/grid/loading.gif)',
					'background-repeat': 'no-repeat',
					'background-position': 'center'
				});

			// Аннулируем старый запрос:
			if (this._htmcclRequestId)
				Ext.Ajax.abort(this._htmcclRequestId);

			// Шлем новый:
			this._htmcclRequestId =
				Ext.Ajax.request({
					url: '/?c=HTMedicalCare&m=loadHTMedicalCareClassListByHTFinance',

					params: {
						HTMFinance_Code: this._curHTMFinance_Code,

						endDate: this._curStartDate ?
							Ext.util.Format.date(this._curStartDate,
								'd.m.Y') : undefined
					},

					callback: onLoad_medicalCareClassLink,
					scope: this
				});
		} else // фильтр без запроса
			if (!this._htmcclRequestId)
				filterStore();

		/******* filterStore **********************************************************
		 * Фильтрация стора методов ВМП.
		 */
		function filterStore() {
			var HTMedicalCareClass_id = cmb.getValue(),
				date = me._curStartDate,
				store = cmb.getStore(),
				isValueValid = false; // допустимо ли текущее значение

			store.clearFilter();
			cmb.lastQuery = '';

			store.filterBy(filterFn);

			if (!isValueValid)
				cmb.clearValue();

			/******* filterFn *************************************************************
			 * Функция фильтрации.
			 */
			function filterFn(record) {
				var res = ((!htmctId || record.get('HTMedicalCareType_id') == htmctId) &&
					(!date ||
						(record.get('HTMedicalCareClass_begDate') || date) <= date &&
						(record.get('HTMedicalCareClass_endDate') || date) >= date) &&
					(!me._htMedicalCareClassLink_ids ||
						me._htMedicalCareClassLink_ids.includes(record.get('HTMedicalCareClass_id'))));

				isValueValid = (isValueValid ||
					res && record.get('HTMedicalCareClass_id') == HTMedicalCareClass_id);

				return (res);
			}
		}

		/******* onLoad_medicalCareClassLink ******************************************
		 * Callback запроса допустимых методов ВМП.
		 */
		function onLoad_medicalCareClassLink(options, success, response) {
			me._htmcclRequestId = null;

			if (success) {
				// Заполняем this._htMedicalCareClassLink_ids:
				var responseObj = Ext.util.JSON.decode(response.responseText);

				if (Array.isArray(responseObj))
					me._htMedicalCareClassLink_ids = responseObj.map(item => Number.parseInt(item.HTMedicalCareClass_id));

				// Фильтруем стор:
				filterStore();
			}

			if (maskEl)
				maskEl.unmask();
		}
	},

	/******* _stepDay *************************************************************
	 * Перемещение периода поиска.
	 *  count - количество дней.
	 ******************************************************************************/
	_stepDay: function(count) {
		var date1 = (this._dtPeriod.getValue1() ||
				Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, count).clearTime(),
			date2 = (this._dtPeriod.getValue2() ||
				Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, count).clearTime();

		this._dtPeriod.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' +
			Ext.util.Format.date(date2, 'd.m.Y'));

		// Отжимаем все кнопки:
		this._clearToggles();

		// Фильтруем поля:
		this._filterHTMedicalCareType();
		this._filterHTMedicalCareClass();
	},

	/******* _clearToggles ********************************************************
	 * Отжатие всех кнопок панели даты.
	 ******************************************************************************/
	_clearToggles: function() {
		this._btnDay.toggle(false);
		this._btnWeek.toggle(false);
		this._btnMonth.toggle(false);
	},

	/******* _onClick_curDay *****************************************************
	 * Кнопка "День".
	 ******************************************************************************/
	_onClick_curDay: function() {
		var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');

		this._dtPeriod.setValue(Ext.util.Format.date(date, 'd.m.Y') + ' - ' +
			Ext.util.Format.date(date, 'd.m.Y'));

		this._filterHTMedicalCareType();
		this._filterHTMedicalCareClass();
	},

	/******* _onClick_curWeek ****************************************************
	 * Кнопка "Неделя".
	 ******************************************************************************/
	_onClick_curWeek: function() {
		var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
			dayOfWeek,
			date2;

		dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		date2 = date1.add(Date.DAY, 6).clearTime();

		this._dtPeriod.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' +
			Ext.util.Format.date(date2, 'd.m.Y'));

		this._filterHTMedicalCareType();
		this._filterHTMedicalCareClass();
	},

	/******* _onClick_curMonth ***************************************************
	 * Кнопка "Месяц".
	 ******************************************************************************/
	_onClick_curMonth: function() {
		var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).getFirstDateOfMonth(),
			date2 = date1.getLastDateOfMonth();

		this._dtPeriod.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' +
			Ext.util.Format.date(date2, 'd.m.Y'));

		if (this._initFilterDone) // по умолчанию - до инициализации
		{
			this._filterHTMedicalCareType();
			this._filterHTMedicalCareClass();
		}
	},

	/******* _onChange_region *****************************************************
	 * Изменение регионаю
	 * Загружаем список ЛПУ.
	 ******************************************************************************/
	_onChange_region: function(cmb, newValue) {
		if (newValue)
			this._cmbLpuHtm.getStore().load({
				params: {
					Region_id: newValue
				}
			});
	},

	/******* show *****************************************************************
	 *
	 ******************************************************************************/
	show: function(conf) {
		this.ARMType = conf.ARMType;
		this.userMedStaffFact = conf.userMedStaffFact || {};

		sw.Promed.swEvnDirectionHTMRegistryWindow.superclass.show.apply(this, arguments);

		// Могут вызвать до завершения инициализации:
		this._callAfterInit(() => this._initFilterDone, this._initOnShow);
	},

	/******* _initOnShow **********************************************************
	 * Инициализация при каждом вызове show.
	 * Фильтр и видимость кнопок в зависимости от контекста вызова.
	 ******************************************************************************/
	_initOnShow: function() {
		this._prepareFilter();

		this._gridWaitList.getAction('action_add')
			.setHidden(this.ARMType.inlist(['common', 'polka']));

		this._gridWaitList.getAction('action_cancel_dir')
			.setHidden(this.ARMType.inlist(['common', 'polka', 'vk']));

		this._gridWaitList.getAction('action_serviced')
			.setHidden(!this.ARMType.inlist(['spec_mz', 'htm']));
	},

	/******* _prepareFilter *******************************************************
	 * Подготовка фильтра в зависимости от контекста вызова.
	 ******************************************************************************/
	_prepareFilter: function() {
		var isSpecMz = (this.ARMType == 'spec_mz');

		if (this._tabPanel.getActiveTab() == this._gridWaitList)
			this._cmbStatus.setValue(this.EVN_STATUS_NEW);

		this._cmbHTMFinance.setDisabled(!isSpecMz);
		this._cmbLpu.setDisabled(!isSpecMz);
		this._cmbLpu.setContainerVisible(isSpecMz);

		if (!isSpecMz) {
			this._cmbHTMFinance.setFieldValue('HTMFinance_Code', 3); // ОМС
			this._cmbLpu.setValue(this.userMedStaffFact.Lpu_id);
		}

		this._onClick_search();
	},

	/******* _onTabChange *********************************************************
	 * Переключение вкладки таблиц.
	 * Поле фильтра "Статус".
	 ******************************************************************************/
	_onTabChange: function(tabPabnel, tab) {
		if (!this._initFilterDone)
			return;

		if (tab == this._gridServiced) // обслуженные направления
		{
			this._statusFilter = this._cmbStatus.getValue();
			this._cmbStatus.setValue(this.EVN_STATUS_SERVICED);
			this._cmbStatus.disable();
		} else // лист ожидания
		{
			this._cmbStatus.setValue(this._statusFilter);
			this._cmbStatus.enable();
		}

		if (!tab._isLoaded)
			this._loadTabData(tab);
	},

	/******* _onClick_search ******************************************************
	 * Кнопка "Найти".
	 ******************************************************************************/
	_onClick_search: function() {
		var tab = this._tabPanel.getActiveTab();

		this._tabPanel.items.each((item) => delete item._isLoaded);
		this._loadTabData(tab);
	},

	/******* _loadTabData *********************************************************
	 * Загрузка данных таблицы tab.
	 ******************************************************************************/
	_loadTabData: function(tab) {
		tab.loadData({
			globalFilters: Ext.apply({
					begDate: Ext.util.Format.date(this._dtPeriod.getValue1(), 'd.m.Y'),
					endDate: Ext.util.Format.date(this._dtPeriod.getValue2(), 'd.m.Y'),

					start: 0,
					limit: 100
				},
				getAllFormFieldValues(this._frmFilter))
		});

		tab._isLoaded = true;
	},

	/******* _onClick_clear *******************************************************
	 * Кнопка "Сброс".
	 ******************************************************************************/
	_onClick_clear: function() {
		this.editFields.forEach((field) => _clearField(field));

		/******* _clearField **********************************************************
		 *
		 */
		function _clearField(field) {
			if (!field.disabled)
				if (field.clearField)
					field.clearField();
				else
					field.setValue(null);
		}
	},

	/******* _onSelChange *********************************************************
	 * Изменение текущей строки таблицы.
	 * Доступность кнопок.
	 ******************************************************************************/
	_onSelChange: function() {
		var grid = this.grid,
			record = grid.getGrid().getSelectionModel().getSelected(),
			action,
			v;

		if (action = grid.getAction('action_open_emk'))
			action.setDisabled(!record);

		if (action = grid.getAction('action_cancel_dir'))
			action.setDisabled(!record ||
				(v = record.get('EvnStatus_id')) == this.win.EVN_STATUS_SERVICED ||
				v == this.win.EVN_STATUS_CANCELED ||
				record.get('Lpu_id') != this.win.userMedStaffFact.Lpu_id);

		if (action = grid.getAction('action_serviced'))
			action.setDisabled(!record ||
				record.get('EvnStatus_id') != this.win.EVN_STATUS_NEW ||
				record.get('Region_id') == getGlobalOptions().region.number);
	},

	/******* _onAction_add ********************************************************
	 * Кнопка "Создать".
	 * Окно поиска человека + Окно создания направления.
	 ******************************************************************************/
	_onAction_add: function() {
		var me = this,
			wndPersonSearch = getWnd('swPersonSearchWindow');

		if (wndPersonSearch.isVisible()) {
			sw.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		wndPersonSearch.show({
			searchMode: 'all',
			onSelect: _onSelect_person
		});

		/******* _onSelect_person *****************************************************
		 * Выбрали человека.
		 ******************************************************************************/
		function _onSelect_person(pdata) {
			if (pdata.Person_IsDead != 'true') {
				wndPersonSearch.hide();

				getWnd('swDirectionOnHTMEditForm')
					.show({
						action: 'add',
						Person_id: pdata.Person_id,
						PersonEvn_id: pdata.PersonEvn_id,
						Server_id: pdata.Server_id,
						LpuSection_id: getGlobalOptions().CurLpuSection_id,
						LpuSection_did: getGlobalOptions().CurLpuSection_id,
						ARMType: me.ARMType,

						onSave: function() {
							me._gridWaitList.getAction('action_refresh').execute();
						}
					});
			} else
				sw.swMsg.alert(lang['oshibka'],
					lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
		}
	},

	/******* _onAction_view *******************************************************
	 * Кнопка "Просмотр".
	 ******************************************************************************/
	_onAction_view: function(name) {
		var record = this['_grid' + name].getGrid().getSelectionModel().getSelected();

		if (record)
			getWnd('swDirectionOnHTMEditForm').show({
				EvnDirectionHTM_id: record.get('EvnDirectionHTM_id'),
				action: 'view',
				ARMType: this.ARMType
			});
	},

	/******* _onAction_openEmk ****************************************************
	 * Кнопка "Открыть ЭМК".
	 ******************************************************************************/
	_onAction_openEmk: function(opts) {
		var record = opts._grid.getGrid().getSelectionModel().getSelected();

		if (!record)
			return;

		var winEmk = getWnd('swPersonEmkWindow');

		if (winEmk.isVisible()) {
			sw.swMsg.alert(lang['soobschenie'],
				lang['forma_elektronnoy_istorii_bolezni_emk_v_dannyiy_moment_otkryita']);

			return;
		}

		winEmk.show({
			Person_id: record.get('Person_id'),
			PersonEnv_id: record.get('PersonEvn_id'),
			ARMType: this.ARMType,
			userMedStaffFact: this.userMedStaffFact
		});
	},

	/******* _onAction_cancelDir **************************************************
	 * Кнопка "Отменить".
	 * Окно выбора причины + установка статуса выбранного в таблице направления.
	 ******************************************************************************/
	_onAction_cancelDir: function() {
		var me = this,
			record = this._gridWaitList.getGrid().getSelectionModel().getSelected();

		if (record)
			getWnd('swSelectEvnStatusCauseWindow')
			.show({
				EvnClass_id: 117, // Направление ВМП

				callback: function(data) {
					me._setEvnDirectionHTMStatus({
						EvnDirectionHTM_id: record.get('EvnDirectionHTM_id'),
						EvnStatus_id: me.EVN_STATUS_CANCELED,
						EvnStatusCause_id: data.EvnStatusCause_id,
						EvnStatusHistory_Cause: data.EvnStatusHistory_Cause,
						pmUser_id: getGlobalOptions().pmuser_id
					});
				}
			});
	},

	/******* _setEvnDirectionHTMStatus ********************************************
	 * Установка статуса направления.
	 * Запрос на сервер.
	 ******************************************************************************/
	_setEvnDirectionHTMStatus: function(params) {
		var me = this;

		this.showLoadMask();

		Ext.Ajax.request({
			url: '/?c=EvnDirectionHTM&m=setEvnDirectionHTMStatus',
			params: params,

			callback: function(options, success, response) {
				if (success)
					me._gridWaitList.loadData();
				else
					sw.swMsg.alert(lang['oshibka'],
						lang['pri_otmene_napravleniya_iz_ocheredi_proizoshla_oshibka']);

				me.hideLoadMask();
			}
		});
	},

	/******* _onAction_serviced ***************************************************
	 * Кнопка "Обслужено".
	 ******************************************************************************/
	_onAction_serviced: function() {
		var record = this._gridWaitList.getGrid().getSelectionModel().getSelected();

		if (record)
			this._setEvnDirectionHTMStatus({
				EvnDirectionHTM_id: record.get('EvnDirectionHTM_id'),
				EvnStatus_id: this.EVN_STATUS_SERVICED,
				pmUser_id: getGlobalOptions().pmuser_id
			});
	}
});
