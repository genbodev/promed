/**
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2013, Swan.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @prefix       mphvlw
 * @version      October, 2013
 */
/*NO PARSE JSON*/
sw.Promed.swPersonEvnPSListWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_PEPSLW,//WND_HVL,
	iconCls: 'workplace-mp16',
	id: 'swPersonEvnPSListWindow',
	readOnly: false,

	/**
	 * Загрузка вызовов на дом
	 *
	 * @param date Дата, на которую загружать вызовы на дом
	 */
	loadPersonEvnPS: function (date) {
		if (date) {
			this.date = date;
		}

		var params = new Object();
		params.date = this.date;

		params.limit = 100;
		params.start = 0;
		var base_form = this.filtersPanel.getForm();
	
		params.Person_Surname = base_form.findField('Person_SurName').getValue();
		params.Person_Firname = base_form.findField('Person_FirName').getValue();
		params.Person_Secname = base_form.findField('Person_SecName').getValue();
		params.Person_BirthDay = Ext.util.Format.date(base_form.findField('Person_Birthday').getValue(), 'd.m.Y');
		params.Polis_Ser = base_form.findField('Polis_Ser').getValue();
		params.Polis_Num = base_form.findField('Polis_Num').getValue();
		
		params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

		params.AmbulatCardLocatType_id = base_form.findField('AmbulatCardLocatType').getValue();
		params.PEPSLW_date_range = Ext.getCmp('PEPSLW_date_range').getRawValue();
		this.EvnPSLocatGrid.loadData({
			globalFilters: params
		});
	},

	initComponent: function () {

		var win = this;
		this.tbar = new Ext.Toolbar({
			autoHeight: true,
			/*buttons: [
				{
					text: lang['period'],
					xtype: 'tbtext'
				},
				{
					xtype: 'daterangefield',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
					width: 180,
					id: 'PEPSLW_date_range'
				}
			],*/
			style: "border-bottom: 0px solid #99BBE8;"
		});

		this.filtersPanel = new Ext.FormPanel({
			xtype: 'form',
			labelAlign: 'right',
			labelWidth: 50,
			items: [
				{
					listeners: {
						collapse: function (p) {
							this.doLayout();
						}.createDelegate(this),
						expand: function (p) {
							this.doLayout();
						}.createDelegate(this)
					},
					xtype: 'fieldset',
					style: 'margin: 5px 0 0 0',
					title: lang['poisk'],
					autoHeight:true,
					collapsible: true,
					layout: 'form',
					items: [
					new Ext.form.DateRangeField(
					{
						width: 170,
						testId: 'wnd_workplace_dateMenu',
						id: 'PEPSLW_date_range',
						fieldLabel: lang['period'],
						plugins: 
						[
							new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
						],
						listeners: {
							'keydown': function (inp, e) {
								if (e.getKey() == Ext.EventObject.ENTER) {
									e.stopEvent();
									this.loadPersonEvnPS();
								}
							}.createDelegate(this)
						}
					}),
					{
							autoHeight:true,
							xtype: 'fieldset',
							style: 'margin: 5px 0 0 0',
							title: lang['dannyie_patsienta'],
							layout: 'form',
							items: [
								{
									layout: 'column',
									items: [
										{
											layout: 'form',
											labelWidth: 60,
											items: [
												{
													xtype: 'textfieldpmw',
													width: 120,
													name: 'Person_SurName',
													fieldLabel: lang['familiya'],
													listeners: {
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.ENTER) {
																e.stopEvent();
																this.loadPersonEvnPS();
															}
														}.createDelegate(this)
													}
												}
											]
										},
										{
											layout: 'form',
											items: [
												{
													xtype: 'textfieldpmw',
													width: 120,
													name: 'Person_FirName',
													fieldLabel: lang['imya'],
													listeners: {
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.ENTER) {
																e.stopEvent();
																this.loadPersonEvnPS();
															}
														}.createDelegate(this)
													}
												}
											]
										},
										{
											layout: 'form',
											labelWidth: 75,
											items: [
												{
													xtype: 'textfieldpmw',
													width: 120,
													name: 'Person_SecName',
													fieldLabel: lang['otchestvo'],
													listeners: {
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.ENTER) {
																e.stopEvent();
																this.loadPersonEvnPS();
															}
														}.createDelegate(this)
													}
												}
											]
										},
										{
											layout: 'form',
											labelWidth: 110,
											items: [
												{
													xtype: 'swdatefield',
													format: 'd.m.Y',
													plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
													name: 'Person_Birthday',
													fieldLabel: lang['data_rojdeniya'],
													listeners: {
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.ENTER) {
																e.stopEvent();
																this.loadPersonEvnPS();
															}
														}.createDelegate(this)
													}
												}
											]
										}
								]},
								{
									layout: 'column',
									items: [
										{
											layout: 'form',
											labelWidth: 90,
											items: [
												{
													xtype: 'textfield',
													width: 90,
													name: 'Polis_Ser',
													fieldLabel: lang['seriya_polisa'],
													enableKeyEvents: true,
													listeners: {
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.ENTER) {
																e.stopEvent();
																this.loadPersonEvnPS();
															}
														}.createDelegate(this)
													}
												}
										]},
											{
											layout: 'form',
											labelWidth: 110,
											items: [
												{
													xtype: 'textfield',
													width: 120,
													name: 'Polis_Num',
													fieldLabel: lang['nomer_polisa'],
													enableKeyEvents: true,
													listeners: {
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.ENTER) {
																e.stopEvent();
																this.loadPersonEvnPS();
															}
														}.createDelegate(this)
													}
												}
											]}
							]}
					]},{
							autoHeight:true,
							xtype: 'fieldset',
							style: 'margin: 5px 0 0 0',
							title: 'Движение истории',
							layout: 'form',
							items: [
								{
									layout: 'column',
									items: [
									{
										layout: 'form',
										labelWidth: 130,
										items: [
											{
												xtype: 'swambulatcardlocattypecombo',
												width: 230,
												name: 'AmbulatCardLocatType',
												fieldLabel: lang['mestonahojdenie'],
												listeners: {
													'keydown': function (inp, e) {
														if (e.getKey() == Ext.EventObject.ENTER) {
															e.stopEvent();
															this.loadPersonEvnPS();
														}
													}.createDelegate(this),
													'change': function(combo,value)
													{
														var base_form = win.filtersPanel.getForm();
														if(value==2)
														{
															base_form.findField('LpuSection_id').enable();
															base_form.findField('MedStaffFact_id').enable();
														}
														else
														{
															base_form.findField('LpuSection_id').disable();
															base_form.findField('MedStaffFact_id').disable();
															base_form.findField('LpuSection_id').setValue(null);
															base_form.findField('MedStaffFact_id').setValue(null);
														}
															
													}
												}
											},
											new sw.Promed.SwLpuSectionGlobalCombo({
												id: 'PEPSL_LpuSectionCombo',
												name: 'LpuSection_id',
												fieldLabel: lang['otdelenie'],
												disabled: true,
												lastQuery: '',
												listWidth: 700,
												linkedElements: [
													'PEPSL_MedPersonalCombo'
												],
												tabIndex: TABINDEX_ERSIF + 2,
												width: 400
											}),
											new sw.Promed.SwMedStaffFactGlobalCombo({
												id: 'PEPSL_MedPersonalCombo',
												name: 'MedStaffFact_id',
												fieldLabel: 'Место работы сотрудника',
												disabled: true,
												lastQuery: '',
												listWidth: 700,
												parentElementId: 'PEPSL_LpuSectionCombo',
												tabIndex: TABINDEX_ERSIF + 3,
												width: 400
											})
										]
									}]
								}
							]},
						{
							layout: 'column',
							labelWidth: 55,
							items: [
								{
									layout: 'form',
									items: [
										{
											style: "padding-top: 10px",
											xtype: 'button',
											id: 'mpwpBtnSearch',
											text: lang['nayti'],
											iconCls: 'search16',
											handler: function () {
												this.loadPersonEvnPS();
											}.createDelegate(this)
										}
									]
								},
								{
									layout: 'form',
									items: [
										{
											style: "padding-top: 10px; padding-left: 10px",
											xtype: 'button',
											id: 'mphvlw_BtnClear',
											text: lang['sbros'],
											iconCls: 'resetsearch16',
											handler: function () {
												var base_form = win.filtersPanel.getForm();
												base_form.findField('Person_SurName').setValue(null);
												base_form.findField('Person_FirName').setValue(null);
												base_form.findField('Person_SecName').setValue(null);
												base_form.findField('Person_Birthday').setValue(null);
												base_form.findField('Polis_Ser').setValue(null);
												base_form.findField('Polis_Num').setValue(null);
												base_form.findField('AmbulatCardLocatType').setValue(null);
												base_form.findField('LpuSection_id').setValue(null);
												base_form.findField('MedStaffFact_id').setValue(null);
												this.loadPersonEvnPS();
											}.createDelegate(this)
										}
									]
								}
						]}
					]
				}
			]
		});

		this.TopPanel = new Ext.Panel(
			{
				region: 'north',
				frame: true,
				border: false,
				autoHeight: true,
				tbar: this.tbar,
				items: [
					this.filtersPanel
				]
			});

		
		this.EvnPSLocatGrid= new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add',hidden:true},
				{name: 'action_edit',hidden: true, disabled: true },
				{name: 'action_view' , text: 'Движения истории болезни'},
				{name: 'action_refresh',hidden: true, disabled: true},
				{name: 'action_delete',hidden:true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=EvnPSLocat&m=getEvnPSList',
			region: 'center',
			object: 'EvnPS',
			editformclassname: 'swMedicalHistoryEditWindow',
			id: 'PersonEvnPSGrid',
			paging: true,
			root: 'data',
			pageSize:100,
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'EvnPS_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnPS_NumCard', type: 'string', width: 170, header: lang['nomer_istorii_bolezni']},
				{name: 'PersonFIO', type: 'string', width: 210, header: lang['fio_patsienta']},
				{name: 'PersonBirthDay', type: 'string', header: lang['data_rojdeniya']},
				{name: 'AmbulatCardLocatType', type: 'string', width: 200, header: lang['mestonahojdenie']},
				{name: 'MedFIO', type: 'string', header: lang['fio_sotrudnika'], id: 'autoexpand', width: 300,}
			],
			title: lang['dvijeniya_originala_ib'],
			toolbar: true
		});

		Ext.apply(this, {
			autoScroll: true,
			buttons: [
				{
					text: '-'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function (button, event) {
						ShowHelp(WND_PEPSLW/*WND_HVL*/);
					}.createDelegate(this),
					tabIndex: TABINDEX_MPSCHED + 98
				},
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function () {
						this.hide();
					}.createDelegate(this)
				}
			],
			layout: 'border',
			items: [
				this.TopPanel,
				{
					layout: 'border',
					region: 'center',
					items: [
						this.EvnPSLocatGrid
					]
				}
			],
			keys: [
				{
					key: [
						Ext.EventObject.F5,
						Ext.EventObject.F9
					],
					fn: function (inp, e) {
						e.stopEvent();
						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						switch (e.getKey()) {
							case Ext.EventObject.F5:
								this.loadPersonEvnPS();
								break;
						}
					},
					scope: this,
					stopEvent: false
				}
			]
		});
		sw.Promed.swPersonEvnPSListWindow.superclass.initComponent.apply(this, arguments);

	},

	show: function () {
		sw.Promed.swPersonEvnPSListWindow.superclass.show.apply(this, arguments);
		var base_form = this.filtersPanel.getForm();
		
		swLpuSectionGlobalStore.clearFilter();
		swMedStaffFactGlobalStore.clearFilter();
		
		setLpuSectionGlobalStoreFilter({
			allowLowLevel: 'yes',
			isStac: true,
			Lpu_id: getGlobalOptions().lpu_id
		});

		setMedStaffFactGlobalStoreFilter({
			allowLowLevel: 'yes',
			isStac: true,
			Lpu_id: getGlobalOptions().lpu_id
		});
		
		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		this.filtersPanel.getForm().reset();
		
		base_form.findField('LpuSection_id').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('LpuSection_id').setValue(null);
		base_form.findField('MedStaffFact_id').setValue(null);
		
		var date =new Date();
		
		Ext.getCmp('PEPSLW_date_range').setRawValue(Ext.util.Format.date(date, 'd.m.Y')+' - '+Ext.util.Format.date(date, 'd.m.Y'));
		//this.loadPersonEvnPS();
	}
});
