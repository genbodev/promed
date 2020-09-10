/**
* swTimeJournalWindow - журнал учета рабочего времени сотрудников.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author
* @version      11.2019
*/
sw.Promed.swTimeJournalWindow = Ext.extend(sw.Promed.BaseForm,
{
	id: 'TimeJournalWindow',
	objectName: 'swTimeJournalWindow',

	title: langs('Журнал учета рабочего времени сотрудников'),

	layout: 'border',
	buttonAlign: 'left',
	closeAction: 'hide',
	maximizable: true,
	modal: true,
	plain: true,

	height: 550,
	width: 850,
	minHeight: 550,
	minWidth: 750,

	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,

	loadMask: null,
	gridPanelAutoLoad: false,

// Кнопки внизу окна:
	buttons:
	[
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',

			handler:
				function(button, event)
				{
					ShowHelp(this.ownerCt.title);
				}
		},
		{
			text: langs('zakryit'),
			tabIndex: -1,
			tooltip: langs('zakryit'),
			iconCls: 'cancel16',

			handler:
				function()
				{
					this.ownerCt.hide();
				}
		}
	],

// Компоненты формы:
	_dtPeriod: undefined,
	_btnDay: undefined,
	_btnWeek: undefined,
	_btnMonth: undefined,
	_frmFilter: undefined,
	_cmbLpu: undefined,
	_cmbMedStaffFact: undefined,
	_gridTimeJournal: undefined,
	_leftPanel: undefined,

// Входные параметры:
//  1. АРМ, из которого открыта форма.
//  2. Ид. врача, открывшего форму.
//  3. Ид. МО, в котором работает врач.
	_ARMType: undefined,
	_MedStaffFact_id: undefined,
	_Lpu_id: undefined,

// Флаг, показывающий, завершена ли инициализация фильтра:
	_initFilterDone: false,

/******* initComponent ********************************************************
 *
 ******************************************************************************/
	initComponent: function()
	{
		this._gridTimeJournal = new sw.Promed.ViewFrame(
			{
				id: 'TJW_TimeJournalGrid',
				object: 'TimeJournal',
				dataUrl: '/?c=TimeJournal&m=loadTimeJournal',
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				paging: true,
				readOnly: true,
				toolbar: false,

				stringfields:
				[
					{ name: 'TimeJournal_id', type: 'int',  header: 'ID', hidden: true, key: true },
					{ name: 'pmUser_id', type: 'int', hidden: true },
					{ name: 'MedPersonal_FIO', type: 'string', header: langs('fio_sotrudnika'), id: 'autoexpand' },
					{ name: 'MedPersonal_TabCode', type: 'string', header: langs('tab_№'), width: 50 },
					{ name: 'BegDT_date', type: 'string', header: langs('Дата начала смены'), width: 100 },
					{ name: 'BegDT_time', type: 'string', header: langs('Время начала'), width: 100 },
					{ name: 'EndDT_date', type: 'string', header: langs('Дата завершения смены'), width: 100 },
					{ name: 'EndDT_time', type: 'string', header: langs('Время завершения'), width: 100 },
				]
			});

		this.items =
		[
			// Фильтр:
			{
				xtype: 'container',
				autoEl: {},
				region: 'north',
				autoHeight: true,

				items:
				[
					// Панель даты:
					{
						xtype: 'toolbar',

						items:
						[
							{
								xtype: 'button',
								text: langs('predyiduschiy'),
								iconCls: 'arrow-previous16',
								handler: () => this._stepDay(-1)
							},
							'-',
							{
								xtype: 'daterangefield',
								itemId: 'dtPeriod',
								fieldLabel: langs('period'),
								width: 150,
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],

								listeners:
								{
									select: () => this._clearToggles()
								}
							},
							'-',
							{
								xtype: 'button',
								text: langs('sleduyuschiy'),
								iconCls: 'arrow-next16',
								handler: () => this._stepDay(1)
							},
							'-',
							'->',
							'-',
							{
								xtype: 'button',
								itemId: 'btnDay',
								text: langs('den'),
								toggleGroup: 'periodToggle',
								iconCls: 'datepicker-day16',
								handler: this._onClick_curDay,
								scope: this
							},
							{
								xtype: 'button',
								itemId: 'btnWeek',
								text: langs('nedelya'),
								toggleGroup: 'periodToggle',
								iconCls: 'datepicker-week16',
								handler: this._onClick_curWeek,
								scope: this
							},
							{
								xtype: 'button',
								itemId: 'btnMonth',
								text: langs('mesyats'),
								toggleGroup: 'periodToggle',
								iconCls: 'datepicker-month16',
								handler: this._onClick_curMonth,
								scope: this
							}
						]
					},

					// Панель фильтров:
					{
						xtype: 'form',
						itemId: 'frmFilter',
						border: false,
						bodyStyle: 'background: transparent',
						layout: 'column',
						labelAlign: 'right',
						autoHeight: true,
						defaults: { labelWidth: 200 },

						items:
						[
							{
								xtype: 'container',
								autoEl: {},
								layout: 'form',
								columnWidth: 1,
								style: "margin: 5px",

								items:
								[
									{
										xtype: 'swlpucombo',
										itemId: 'cmbLpu',
										hiddenName:'lpu_id',
										fieldLabel: langs('meditsinskaya_organizatsiya'),
										anchor: '100%',
										disabled: true
									},
									{
										xtype: 'swmedstafffactglobalcombo',
										itemId: 'cmbMedStaffFact',
										hiddenName:'MedStaffFact_id',
										fieldLabel: langs('sotrudnik'),
										anchor: '100%'
									}
								]
							}
						]
					},

					// Кнопки "Найти" и "Сброс":
					{
						xtype: 'toolbar',
						autoCreate: { html:'<table cellspacing="0"><tr></tr></table>' },
						style: "margin: 10px 0 10px 0",

						items:
						[
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
								text: langs('sbros'),
								iconCls: 'resetsearch16',
								style: "margin: 0px 6px 0px 6px",
								handler: this._onClick_reset,
								scope: this
							}
						]
					}
				]
			},

			// Область данных:
			this._gridTimeJournal
		];

		sw.Promed.swTimeJournalWindow.superclass.initComponent.apply(this, arguments);

		setTimeout(() => this._finishInitComponent(), 1);
	},

/******* _callOnFlag **********************************************************
 *
 ******************************************************************************/
	_callOnFlag: function(flagName, func, args)
	{
		if (this[flagName])
			func.apply(this, args);
		else
			setTimeout(() => this._callOnFlag(flagName, func, args), 1);
	},

/******* _finishInitComponent *************************************************
 *
 ******************************************************************************/
	_finishInitComponent: function()
	{
		this.items.each(this._findComponents, this);
		this._initFilterDone = true;
	},

/******* _findComponents ******************************************************
 *
 ******************************************************************************/
	_findComponents: function(item)
	{
		if (item.itemId)
			this['_' + item.itemId] = item;

		if (item.items)
			item.items.each(this._findComponents, this);
	},

/******* _stepDay *************************************************************
 *
 ******************************************************************************/
	_stepDay: function(count)
	{
		var date1 = (this._dtPeriod.getValue1() ||
					Date.parseDate(getGlobalOptions().date, 'd.m.Y'))
						.add(Date.DAY, count).clearTime(),
			date2 = (this._dtPeriod.getValue2() ||
					Date.parseDate(getGlobalOptions().date, 'd.m.Y'))
						.add(Date.DAY, count).clearTime();

		this._dtPeriod.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' +
								Ext.util.Format.date(date2, 'd.m.Y'));

		this._clearToggles();
	},

/******* _clearToggles ********************************************************
 *
 ******************************************************************************/
	_clearToggles: function()
	{
		this._btnDay.toggle(false);
		this._btnWeek.toggle(false);
		this._btnMonth.toggle(false);
	},

/******* _onClick_curDay *****************************************************
 *
 ******************************************************************************/
	_onClick_curDay: function()
	{
		var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');

		this._dtPeriod.setValue(Ext.util.Format.date(date, 'd.m.Y') + ' - ' +
								Ext.util.Format.date(date, 'd.m.Y'));
	},

/******* _onClick_curWeek ****************************************************
 *
 ******************************************************************************/
	_onClick_curWeek: function()
	{
		var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y')),
			dayOfWeek,
			date2;

		dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		date2 = date1.add(Date.DAY, 6).clearTime();

		this._dtPeriod.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' +
								Ext.util.Format.date(date2, 'd.m.Y'));
	},

/******* _onClick_curMonth ***************************************************
 *
 ******************************************************************************/
	_onClick_curMonth: function()
	{
		var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'))
						.getFirstDateOfMonth(),
			date2 = date1.getLastDateOfMonth();

		this._dtPeriod.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' +
								Ext.util.Format.date(date2, 'd.m.Y'));
	},

/******* show *****************************************************************
 *
 ******************************************************************************/
	show: function(conf)
	{
		this._callOnFlag('_initFilterDone', this._doShow, [conf]);
	},

/******* _doShow **************************************************************
 *
 ******************************************************************************/
	_doShow: function(conf)
	{
		var cmb = this._cmbMedStaffFact,
			msfStore = cmb.getStore(),
			msfFilter,
			value = cmb.getValue(),
			isValueValid = false;

		this._ARMType = conf.ARMType;
		this._MedStaffFact_id = conf.MedStaffFact_id;
		this._Lpu_id = conf.Lpu_id;

		// В комбобоксе "Медицинская организация" выберем текущую МО:
		this._cmbLpu.setValue(this._Lpu_id);

		// В комбобокс "Сотрудник" загрузим сотрудников текущего МО:
		cmb.lastQuery = '';
		msfStore.clearFilter();

		msfFilter =
			{
				Lpu_id: this._Lpu_id
			};

		if (swMedStaffFactGlobalStore.data.length == 0)
			msfStore.load(
				{
					callback: function()
					{
						var store = setMedStaffFactGlobalStoreFilter(msfFilter, msfStore);

						msfStore.loadData(getStoreRecords(store));
					}
				});
		else
		{
			setMedStaffFactGlobalStoreFilter(msfFilter);
			msfStore.loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}

		// Если форма открыта из АРМ Администратора или пользователь входит в группу
		// "Учет рабочего времени сотрудников. Полный доступ", комбобокс "Сотрудник"
		// сделаем доступным, наложим фильтр, чтобы отображались только сотрудники,
		// на рабочих местах которых ведется учет рабочего времени, и очистим выбор.
		// В противном случае заблокируем комбобокс "Сотрудник" и выберем в нем
		// текущего сотрудника.

		if (this._ARMType == 'lpuadmin' || isUserGroup('TimeJournal'))
		{
			msfStore.filterBy((item) => item.get('LpuUnitType_Code')
										.inlist(['1', '6', '9', '17']));

			cmb.enable();
			cmb.clearValue();
		}
		else
		{
			cmb.disable();
			cmb.setValue(this._MedStaffFact_id);
		}

		this._btnDay.toggle(true);
		this._onClick_curDay();

		sw.Promed.swTimeJournalWindow.superclass.show.apply(this, arguments);

		this._onClick_search();
	},

/******* _onClick_search ******************************************************
 * Фильтр: Найти
 ******************************************************************************/
	_onClick_search: function(mode)
	{
		var maxBegDT,
			dt1 = this._dtPeriod.getValue1(),
			dt2 = this._dtPeriod.getValue2();

		// Сравниваем с числом 30, т. к. в дате окончания время 0 часов 0 минут:
		if ((dt2 - dt1) / (1000 * 60 * 60 * 24) > 30)
		{
			sw.swMsg.alert(langs('Ошибка'),
						   langs('Заданный период не может превышать 31 день'));
			return;
		}

		maxBegDT = new Date(dt2);
		maxBegDT.setHours(23, 59, 59);

		this._gridTimeJournal.loadData(
			{
				globalFilters: Ext.apply(
					{
						minBegDT: Ext.util.Format.date(dt1, 'Y-m-d\\TH:i:s'),
						maxBegDT: Ext.util.Format.date(dt2, 'Y-m-d') +
									'T23:59:59',
						fullInfo: true,
						start: 0,
						limit: 100
					},
					getAllFormFieldValues(this._frmFilter))
			});
	},

/******* _onClick_reset *******************************************************
 * Фильтр: Сброс
 ******************************************************************************/
	_onClick_reset: function()
	{
		var cmb = this._cmbMedStaffFact;

		if (this._ARMType == 'lpuadmin' || isUserGroup('TimeJournal'))
			cmb.clearValue();

		this._btnDay.toggle(true);
		this._onClick_curDay();
	}
});
