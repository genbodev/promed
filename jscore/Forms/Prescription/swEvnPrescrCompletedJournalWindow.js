/**
* swEvnPrescrCompletedJournalWindow - журнал медицинских мероприятий.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      21.12.2011
* @comment      Префикс для id компонентов EPRCMPJW (EvnPrescrCompletedJournalWindow)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrCompletedJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrCompletedJournalWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrCompletedJournalWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	height: 550,
	id: 'EvnPrescrCompletedJournalWindow',
	initComponent: function() {
		// Фильтры
		this.FilterPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			height: 110,
			id: 'EvnPrescrCompletedJournalForm',
			labelAlign: 'right',
			labelWidth: 100,
			region: 'north',

			items: [{
				layout: 'column',
				border: false,	
				items: [{
					bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: lang['familiya'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Surname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['imya'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Firname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['otchestvo'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Secname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['data_rojdeniya'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						name: 'Person_Birthday',
						plugins: [
							new Ext.ux.InputTextMask('99.99.9999', false)
						],
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 140,
					layout: 'form',
					items: [{
						autoLoad: false,
						comboSubject: 'PrescriptionType',
						fieldLabel: lang['tip_naznacheniya'],
						hiddenName: 'PrescriptionType_id',
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this),
							'render': function(combo) {
								combo.getStore().load({
									params: {
										where: 'where PrescriptionType_id in (1, 2, 3, 4, 5, 6, 7, 10)'
									}
								});
							}.createDelegate(this)
						},
						typeCode: 'int',
						width: 250,
						xtype: 'swcommonsprcombo'
					}, {
						enableKeyEvents: true,
						fieldLabel: lang['period_vyipolneniya'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						name: 'EvnPrescr_setDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						width: 200,
						xtype: 'daterangefield'
					}]
				}]
			}]
		});

		this.reader = new Ext.data.JsonReader({
			id: 'EvnPrescr_id'
		}, [
			{ name: 'EvnPrescr_id' },
			{ name: 'Person_id' },
			{ name: 'PersonEvn_id' },
			{ name: 'Server_id' },
			{ name: 'EvnPrescr_setDate', type: 'date', dateFormat: 'd.m.Y' },
			{ name: 'PrescriptionType_Name' },
			{ name: 'Person_FIO' },
			{ name: 'Person_Birthday', type: 'date', dateFormat: 'd.m.Y' },
			{ name: 'EvnPrescr_Name' }
		]);

		this.gridStore = new Ext.data.GroupingStore({
			autoLoad: false,
			groupField: 'EvnPrescr_setDate',
			reader: this.reader,
			sortInfo: {
				field: 'EvnPrescr_setDate',
				direction: 'ASC'
			},
			url: '/?c=EvnPrescr&m=loadEvnPrescrCompletedJournalGrid'
		});

		this.PrescriptionGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand_prescr',
			autoExpandMin: 300,
			clearStore: function() {
				if ( this.getEl() ) {
/*
					if ( this.getTopToolbar().items.last() ) {
						this.getTopToolbar().items.last().el.innerHTML = '0 / 0';
					}
*/
					this.getStore().removeAll();
				}
			},
			columns: [{
				dataIndex: 'EvnPrescr_setDate',
				header: lang['data'],
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: true,
				sortable: true,
				width: 110
			}, {
				dataIndex: 'PrescriptionType_Name',
				header: "Тип назначения",
				resizable: true,
				sortable: true,
				width: 200
			}, {
				dataIndex: 'Person_FIO',
				header: lang['patsient'],
				resizable: true,
				sortable: true,
				width: 250
			}, {
				dataIndex: 'Person_Birthday',
				header: "Дата рождения",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: true,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'EvnPrescr_Name',
				header: lang['naznachenie'],
				id: 'autoexpand_prescr',
				resizable: true,
				sortable: true
			}],
			focus: function () {
				if ( this.getStore().getCount() > 0 ) {
					this.getView().focusRow(0);
					this.getSelectionModel().selectFirstRow();
				}
			},
			frame: false,
			hasPersonData: function() {
				return this.getStore().fields.containsKey('Person_id') && this.getStore().fields.containsKey('Server_id');
			},
			layout: 'fit',
			loadMask: true,
			loadStore: function(params) {
				if ( !this.params ) {
					this.params = null;
				}

				if ( params ) {
					this.params = params;
				}

				this.clearStore();
				this.getStore().load({
					params: this.params
				});
			},
			region: 'center',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIdx, record) {
						//
					}.createDelegate(this),
					'rowdeselect': function(sm, rowIdx, record) {
						//
					}
				},
				singleSelect: true
			}),
			store: this.gridStore,
			stripeRows: true,
			view: new Ext.grid.GroupingView( {
				forceFit: false,
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2, 3, 4]) ? "записи" : (values.rs.length == 1 ? "запись" : "записей")]})'
			})
		});

		Ext.apply(this, {
			buttons: [{
			    handler: function(){
					this.loadGridWithFilter();
			    }.createDelegate(this),
			    iconCls: 'search16',
			    text: BTN_FRMSEARCH
			}, {
			    handler: function(){
					this.loadGridWithFilter(true);
			    }.createDelegate(this),
			    iconCls: 'resetsearch16',
			    text: lang['sbros']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					//
				}.createDelegate(this),
				// tabIndex: TABINDEX_ESTEF + 36,
				text: BTN_FRMCLOSE
			}],
			items: [
				this.FilterPanel,
				this.PrescriptionGrid
			],
			layout: 'border'
		});

		sw.Promed.swEvnPrescrCompletedJournalWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPrescrCompletedJournalWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			//
		},
		'restore': function(win) {
			//
		}
	},
	loadGridWithFilter: function(clear) {
		var base_form = this.FilterPanel.getForm();

		this.PrescriptionGrid.clearStore();

		if ( clear ) {
			base_form.reset();
		}
		else {
			var params = base_form.getValues();

			params.limit = 100;
			params.start = 0;
		
			this.PrescriptionGrid.loadStore(params);
		}
	},
	loadMask: null,
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnPrescrCompletedJournalWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.PrescriptionGrid.clearStore();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		var base_form = this.FilterPanel.getForm();
		base_form.reset();

		var date = new Date();

		base_form.findField('EvnPrescr_setDate_Range').setValue(Ext.util.Format.date(date, 'd.m.Y') + ' - ' + Ext.util.Format.date(date, 'd.m.Y'));

		if ( arguments[0] && arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.LpuSection_Name ) {
			this.setTitle(WND_PRESCR_REGCMP + ' - ' + arguments[0].userMedStaffFact.LpuSection_Name);
		}
		else {
			this.setTitle(WND_PRESCR_REGCMP);
		}
	},
	title: WND_PRESCR_REGCMP,
	width: 850
});