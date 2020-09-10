Ext6.define('swSignStickListWindow', { //подсказка-список версий
	extend: 'Ext6.menu.Menu',
	width: 400,
	minHeight: 10,
	focusOnToFront: true,
	cls: 'arm-window-new emk-forms-window ',
	typename: '',	
	parentWin: null,
	params: {},
	onSelect: Ext6.emptyFn,
	enableRefreshScroll: false,

	load: function(force) {
		var me = this,
			vm = me.parentWin.getViewModel(),
			id = null;

		if(me.typename=='Irr') id = vm.get('Signatures_iid');
		if(me.typename=='Leave') id = vm.get('Signatures_id');

		if(id) me.grid.store.load({
			params: {
				Signatures_id: id
			}
		});
	},
	initComponent: function () {
		var me = this;
		
		me.grid = Ext6.create('Ext6.grid.Panel', {
			cls: 'EmkGrid',
			region: 'center',
			emptyText: 'Нет результатов.',
			border: true,
			columns: [{
				dataIndex: 'Signatures_Version',
				header: langs('Версия'),
				width: 80,
				sortable: false,
			}, {
				dataIndex: 'SignaturesHistory_insDT',
				header: langs('Дата и время'),
				width: 150,
				sortable: false
			}, {
				dataIndex: 'PMUser_Name',
				header: langs('Пользователь'),
				flex: 1,
				sortable: false
			}],
			store: {
				fields: [{
					name: 'Signatures_Version',
					type: 'string'
				}, {
					name: 'SignaturesHistory_insDT',
					type: 'string'
				}, {
					name: 'PMUser_Name',
					type: 'string'
				}, {
					name: 'Signatures_id',
					type: 'int'
				}],
				autoLoad: false,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Stick&m=loadStickVersionList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					},
				},
				extend: 'Ext6.data.Store',
				pageSize: null,
				listeners: {
					load: function() {
						setTimeout(function(){
							me.showBy(me.parentWin.queryById('swSignStick'+me.typename+'List').getEl());
							/*me.show({
								target: me.parentWin.queryById('swSignStick'+me.typename+'List').getEl(),
								align: 'tr-br?',
							});*/
						}, 100);					
						
					}
				}
			}
		});

		Ext6.apply(me, {
			items: [
				me.grid
			]
		});

		me.callParent(arguments);
	}
});

/**
* EvnStickEditWindow - окно редактирования/добавления листа временной нетрудоспособности.
*
* Импорт формы из ext2:
* 1й этап - с редакции 113317 (ext2)
* 2й этап - с редакции 130912 (ext2)
* 05-07-2019 актуализировано до ред.157713
*
* @input data: action - действие (add, edit, view)
*/

/** Модель данных
 */
Ext6.define('lvnViewModel', {
	extend: 'Ext6.app.ViewModel',
	alias: 'viewmodel.lvnViewModel',
	data: {
		ext2: false,//настройка вида доп.форм
		readonly: false,
		Person_id: null,
		PersonSnils: null,
		Signatures_id: null,
		Signatures_iid: null,
		StickOrder_id: '',
		enableEdit: true,
		isPaid: false,
		isInReg: false,
		action: '',
		StickFSSData_id: null,
		StickCause_id: null,
		numvalid: true,
		EvnStick_Num: '',
		StickCause_SysNick: '',
		addWorkReleaseAccessType: null,
		EvnStickBase_consentDT: false,
		EvnStick_disDate: false,
		EvnStick_IsOriginal: true,
		EvnStick_IsNotOriginal: false,//вместо EvnStick_IsOriginal
		StickWorkType_id: false,
		EvnStickDop_pid: false,
		EvnStick_IsPaid: false,//использовать isPaid (bool)
		EvnStick_IsInReg: false,//использовать isInReg (bool)
		hasWorkReleaseIsInReg: false,
		hasWorkReleaseIsPaid: false,
		RegistryESStorage_id: null,
		ConsentInAnotherLvn: false, //ext6
		fromList: false,
		EvnStickFullNameText: '',
		isTubDiag: false,
		EvnStick_sstNum: true,
		region: '', //==getRegionNick чтобы использовать в bind
		fromStickLeave: '',// Указывает откуда загружен исход. 'orig' - из оригинала
		//~ isAccessToField_EvnStick_StickDT: false, //выключил т.к. не используется
		mainPanelAccess: true,
		mainPanelSomeFieldsAccess: true,
		mainPanelSomeMainFieldsKodyNetrudAccess: true,
		mainPanelSSTFieldsAccess: true,
		carePersonMSEAccess: false,
		workReleaseAccess: false,
		isAccessToStickLeave: false,
		isAccessToField_swSignStickIrr: false,
		isAccessToField_StickLeave_Sign: false,
		isAccessToField_EvnStick_stacBegDate: true,
		isAccessToField_EvnStick_stacEndDate: true,
		isHasDvijenia: null,
		isHasDvijeniaInStac24: null,
		isAccessToField_EStEF_btnSetMinDateFromPS: false,
		isAccessToField_EStEF_btnSetMaxDateFromPS: false, //не было в ext2
		isEvnStickFullNameText: false
	}
});

/** Базовая панель (==раздел в структуре формы)
 */
Ext6.define('DefaultEvnStickPanelPlus', {
	extend: 'swPanel',
	isLoaded: false,
	border: true,
	collapsible: true,
	collapsed: true,
	width: 1000,
	readOnly: false,
	onRender: function() {
		var me = this;
		me.callParent(arguments);
		if (me.plusButton) {
			me.addTool({
				type: 'plusbutton',
				itemId: 'plus',
				bind: {
					hidden: me.plusBindHidden
				},
				callback: function(panel, tool, event) {
					me.plusButton();
				}
			});
		}
	},
	refreshTitle: function(doExpand) {
		var grid = this.queryBy(function(el) { return el.$className=='Ext.grid.Panel' ; });
		var N =0;
		if(grid && grid[0]) {
			var always_collapsible = false;
			grid=grid[0];
			N = grid.getStore().getCount();
			this.setTitleCounter(N);

			if(N==0) {
				if(!always_collapsible) {
					this.collapse();
					this.collapseTool.addCls('collapse-tool-hide');
				}
			} else {
				if(!always_collapsible)	this.collapseTool.removeCls('collapse-tool-hide');
				this.setTitleCounter(N);
			}
		} else this.setTitleCounter(0);
		if(doExpand) if(grid.getStore().getCount()>0) this.expand(); else this.collapse();
		return N;
	},
	setReadOnly: function(enable) {
		this.readOnly = enable;
		if(this.readOnly) {

		}
	}
});

/** Основная форма (окно)
 */
Ext6.define('common.Stick.EvnStickEditWindow', {
	layout: 'border',
	requires: [
		'common.EMK.PersonInfoPanel',
		'common.Stick.EvnStickEditWindowController'
	],
	refreshCode: function() {//обновление кода формы и контроллера
		var win = this;
		className = 'common.Stick.EvnStickEditWindowController';
		pathWindow = Ext6.Loader.getPath(className);
		var sep = 1+pathWindow.indexOf('?') ? '&ext6id=' : '?';
		Ext6.undefine(className);
		Ext6.Loader.loadScript({
			url: pathWindow + sep + Ext6.id(),
			scope: this
		});
		win.callParent(arguments);
	},
	controller: 'EvnStickEditWindowController',
	viewModel: 'lvnViewModel',
	fields: [''],
	conf: { //конфиг для часто встречающихся отступов:
		paddingleft: 36,
		primarypanel: {
			bodypadding: '15px 20px 20px 26px' //26=36-10 чтобы fieldset левее поставить.
		},
		labelWidth: 195,//размеры полей основной части формы (сразу после заголовка)
		width: 690,
		dateWidth: 135,
		fieldset: { //размеры fieldset основной части
			width: 738,
			paddingleft: 10,
			item: { //размеры элементов внутри fieldset
				labelWidth: 194,
				width: 683+5
			}
		},
		'режим': {
			width: 595,
			labelWidth: 215
		},
		'мсэ': {
			width: 424,
			labelWidth: 315
		},
		'исход': {
			width: 482+215,//148
			labelWidth: 215
		}
	},
	setting: false,
	//~ addHelpButton: Ext6.emptyFn,
	addCodeRefresh: Ext6.emptyFn,
	maximized: true,
	header: getGlobalOptions().client == 'ext2',
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 1000,
	cls: 'arm-window-new emk-forms-window lvn-edit-window',
	extend: 'base.BaseForm',
	renderTo: main_center_panel.body.dom,
	title: 'ЛВН',
	constrain: true,
	userMedStaffFact: null,
	refId: 'swEvnStickEditWindowExt6',
	itemId: 'swEvnStickEditWindowExt6',

	codeRefresh: true,
	objectName: 'swEvnStickEditWindow',
	action: null,
	buttonAlign: 'left',
	callback: Ext6.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	JobOrg_id: null,
	link: false,
	parentNum: null,
	Person_Post: null,
	userMedStaffFactId: null,
	CurLpuSection_id: 0,
	CurLpuUnit_id: 0,
	CurLpuBuilding_id: 0,
	IngoreMSFFilter: 0,
	StickReg: 0,
	Signatures_id: null,
	Signatures_iid: null,
	isTubDiag: false,
	hasWorkReleaseIsInReg: false,
	draggable: true,
	evnStickType: 1,
	plain: true,
	loadMask: null,
	parentClass: null,
	firstTabIndex: 2600,
	listeners: {
		beforehide: function() {
			this.getController().beforeHide();
		},
		hide: function ()
		{
			typeof this.onHideFn === 'function' ? this.onHideFn() : null;
			return;
		}
	},
	formStatus: 'edit',
	height: 550,

	// КВС id, если есть
	EvnPS_id: null,

	// Движения в связанной КВС (или ТАП? УТОЧНИТЬ!)
	EvnSectionList: null,
	// Даты движений в связанной КВС (или ТАП? УТОЧНИТЬ!)
	EvnSectionDates: null,

	panelEvnStickCarePerson: null,
	panelMSE: null,
	// Панель Режим
	panelStickRegime: null,
	// Панель Периоды освобождения от работы
	panelEvnStickWorkRelease: null,
	// Панель Исход
	panelStickLeave: null,

	ignoreCheckEvnStickOrg: 0,

	onSprLoad: function(arguments) {
		this.getController().onSprLoad(arguments);
	},

	// 3. МСЭ
	_initPanelMSE: function(){
		var me = this;

		me.panelMSE = new Ext6.create('swPanel',
			{
				autoHeight: true,
				border: true,
				collapsible: true,
				itemId: 'EStEF_MSEPanel',
				listeners: {
					'expand': function(panel) {
						//
					}.createDelegate(this)
				},
				title: langs('МСЭ'),
				defaults: {
					labelWidth: me.conf['мсэ'].labelWidth, //310,
					width: me.conf.dateWidth + me.conf['мсэ'].labelWidth //425
				},
				items: [{
					allowBlank: true,
					xtype: 'swDateField',
					userCls:'date-field',
					fieldLabel: langs('Дата направления в бюро МСЭ'),
					name: 'EvnStick_mseDate',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ]
					//~ selectOnFocus: true,
					//~ tabIndex: TABINDEX_ESTEF + 25,
					//~ enableKeyEvents: true,
				}, {
					allowBlank: true,
					fieldLabel: langs('Дата регистрации документов в бюро МСЭ'),
					name: 'EvnStick_mseRegDate',
					xtype: 'swDateField',
					userCls: 'date-field',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ]
					//~ selectOnFocus: true,
					//~ tabIndex: TABINDEX_ESTEF + 26,
				}, {
					allowBlank: true,
					xtype: 'swDateField',
					userCls: 'date-field',
					fieldLabel: langs('Дата освидетельствования в бюро МСЭ'),
					name: 'EvnStick_mseExamDate',
					selectOnFocus: true,
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ]
					//~ tabIndex: TABINDEX_ESTEF + 27,
				}, {
					xtype: 'checkbox',
					hidden: (getRegionNick()!='kz'),
					boxLabel: langs('Установлена группа инвалидности'),
					name: 'EvnStick_IsDisability',
					lastQuery: '',
					//~ tabIndex: TABINDEX_ESTEF + 28,
					clearValue: function() {
						this.setValue(false);
					}
					//~ enableKeyEvents: true,
				/*	listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
								var base_form = this.FormPanel.getForm();

								e.stopEvent();

								if ( !this.queryById('EStEF_EvnStickWorkReleasePanel').collapsed && this.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
									this.queryById('EStEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
									this.queryById('EStEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
								}
								else if ( !this.queryById('EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
									base_form.findField('StickLeaveType_id').focus(true);
								}
								else if ( this.action != 'view' ) {
									this.buttons[0].focus();
								}
								else {
									this.buttons[1].focus();
								}
							}
						}.createDelegate(this)
					},
				*/
				}, {
					comboSubject: 'InvalidGroupType',
					//~ enableKeyEvents: true,
					//hidden: (getRegionNick()=='kz'),
					fieldLabel: langs('Установлена/изменена группа инвалидности'),
					name: 'InvalidGroupType_id',
					value: '',
					width: 310+263,
					lastQuery: '',
				/*	listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
								var base_form = this.FormPanel.getForm();

								e.stopEvent();

								if ( !this.queryById('EStEF_EvnStickWorkReleasePanel').collapsed && this.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
									this.queryById('EStEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
									this.queryById('EStEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
								}
								else if ( !this.queryById('EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
									base_form.findField('StickLeaveType_id').focus(true);
								}
								else if ( this.action != 'view' ) {
									this.buttons[0].focus();
								}
								else {
									this.buttons[1].focus();
								}
							}
						}.createDelegate(this)
					}, */
					//~ tabIndex: TABINDEX_ESTEF + 28,
					xtype: 'commonSprCombo'
				}]
			}
		);
	},

	// 4. Освобождение от работы
	_initPanelEvnStickWorkRelease: function() {
		var me = this;

		me.panelEvnStickWorkRelease = new Ext6.create('DefaultEvnStickPanelPlus',
			{
				checkWorkReleaseMenu: function() {	me.getController().checkWorkReleaseMenu(); },
				plusButton: function() { me.getController().openEvnStickWorkReleaseEditWindow('add'); },
				//plusBindHidden: '{ action=="view" || !workReleaseAccess || !StickFSSData_id}',
				plusBindHidden: '{!(workReleaseAccess && ((action=="add" && !StickFSSData_id)||(action=="edit" && addWorkReleaseAccessType == "edit")))}',

				border: true,
				collapsible: true,
				collapsed: true,
				itemId: 'EStEF_EvnStickWorkReleasePanel',
				isLoaded: false,
				listeners: {
					'expand': function(panel) {
						me.getController().onExpand_EvnStickWorkReleasePanel(panel);
						//~ panel.doLayout();
					}.createDelegate(this)
				},
				title: langs('ОСВОБОЖДЕНИЕ ОТ РАБОТЫ'),
				items: [
					{
						xtype: 'label',
						itemId: 'openEvnStickWorkReleaseCalculationWindow',
						hidden: true,
						style: 'margin-bottom: 10px;',
						html: "<a href='#' onclick='Ext6.getCmp(\""+me.id+"\").queryById(\"openEvnStickWorkReleaseCalculationWindow\").handler();'>Дней нетрудоспособности в году</a> ",
						handler: function() {
							me.getController().openEvnStickWorkReleaseCalculationWindow();
						}
					},
					me.gridEvnStickWorkRelease = new Ext6.grid.Panel({
						autoLoad: false,
						xtype: 'grid',
						cls: 'EmkGrid',
						itemId:'EStEF_EvnStickWorkReleaseGrid',
						//~ width: '100%',
						disableSelection: true,
						columns: [{
							dataIndex: 'EvnStickWorkRelease_Dates',
							header: langs('Период освобождения'),
							width: 187,
							hidden: false,
							renderer: function(v, p, row) {
								return Ext6.util.Format.date(row.get('EvnStickWorkRelease_begDate'), 'd.m.Y') +' - '+Ext6.util.Format.date(row.get('EvnStickWorkRelease_endDate'), 'd.m.Y');
							},
						}, {
							dataIndex: 'Org_Name',
							header: langs('МО'),
							width: 137,
							hidden: false,
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'MedPersonal_Fio',
							header: langs('ФИО врача'),
							width: 230,
							hidden: false,
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'EvnStickWorkRelease_IsDraft',
							header: langs('Статус'),
							renderer: function(v, p, row) {
								if (!Ext6.isEmpty(v) && v == 1) {
									return langs('Черновик');
								}

								if (Ext6.isEmpty(v))
									return '';

								return langs('Утвержден');
							},
							hidden: false,
							width: 100
						}, {
							dataIndex: 'SMP_Status_Name',
							header: 'Подписан врачом',
							hidden: getRegionNick() == 'kz',
							width: 250,
							renderer: function(v,p,row) {
								if(row.get('SMPStatus_id')==1)
									return Ext6.util.Format.date(row.get('SMP_updDT'), 'd.m.Y')+' '+row.get('SMP_updUser_Name');
								else
									return v;
							}
						}, {
							dataIndex: 'SVK_Status_Name',
							header: 'Подписан ВК',
							hidden: getRegionNick() == 'kz',
							//~ width: 220,
							flex: 1,
							renderer: function(v,p,row) {
								if(row.get('SVKStatus_id')==1)
									return Ext6.util.Format.date(row.get('SVK_updDT'), 'd.m.Y')+' '+row.get('SVK_updUser_Name');
								else
									return v;
							}
						}, {
							width: 40,
							dataIndex: 'EvnStickWorkRelease_Action',
							renderer: function (value, metaData, record) {
								return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.queryById('EStEF_EvnStickWorkReleaseGrid').id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
							}
						}
						],
						load: function() {//пока не используется
							var me = this;
							this.getStore().load({
								params: {
									EvnStick_id: me.FormPanel.getForm().findField('EvnStick_id').getValue()
								}
							});
						},
						store: new Ext6.data.JsonStore({
							autoLoad: false,
							proxy: {
								type: 'ajax',
								url: '/?c=Stick&m=loadEvnStickWorkReleaseGrid',
								reader: {
									type: 'json',
								}
							},
							listeners: {
								'load': function(panel) {
									me.getController().onLoadEvnStickWorkReleaseGrid(panel);
								}
							},
							fields: [
								{
									mapping: 'accessType',
									name: 'accessType',
									type: 'string'
								},
								{
									mapping: 'signAccess',
									name: 'signAccess',
									type: 'string'
								},
								{
									mapping: 'Lpu_id',
									name: 'Lpu_id',
									type: 'int'
								},
								{
									mapping: 'EvnStickWorkRelease_id',
									name: 'EvnStickWorkRelease_id',
									type: 'int'
								},
								{
									mapping: 'EvnStickBase_id',
									name: 'EvnStickBase_id',
									type: 'int'
								},
								{
									mapping: 'Org_id',
									name: 'Org_id',
									type: 'int'
								},
								{
									mapping: 'EvnStickWorkRelease_IsDraft',
									name: 'EvnStickWorkRelease_IsDraft',
									type: 'int'
								},
								{
									mapping: 'EvnStickWorkRelease_IsSpecLpu',
									name: 'EvnStickWorkRelease_IsSpecLpu',
									type: 'int'
								},
								{
									mapping: 'Signatures_mid',
									name: 'Signatures_mid',
									type: 'int'
								},
								{
									mapping: 'SMPStatus_id',
									name: 'SMPStatus_id',
									type: 'int'
								},
								{
									mapping: 'SMP_Status_Name',
									name: 'SMP_Status_Name',
									type: 'string'
								},
								{
									mapping: 'SMP_updDT',
									name: 'SMP_updDT',
									type: 'string'
								},
								{
									mapping: 'SMP_updUser_Name',
									name: 'SMP_updUser_Name',
									type: 'string'
								},
								{
									mapping: 'Signatures_wid',
									name: 'Signatures_wid',
									type: 'int'
								},
								{
									mapping: 'SVKStatus_id',
									name: 'SVKStatus_id',
									type: 'int'
								},
								{
									mapping: 'SVK_Status_Name',
									name: 'SVK_Status_Name',
									type: 'string'
								},
								{
									mapping: 'SVK_updDT',
									name: 'SVK_updDT',
									type: 'string'
								},
								{
									mapping: 'SVK_updUser_Name',
									name: 'SVK_updUser_Name',
									type: 'string'
								},
								{
									mapping: 'EvnStickWorkRelease_IsInReg',
									name: 'EvnStickWorkRelease_IsInReg',
									type: 'int'
								},
								{
									mapping: 'EvnStickWorkRelease_IsPaid',
									name: 'EvnStickWorkRelease_IsPaid',
									type: 'int'
								},
								{
									mapping: 'LpuSection_id',
									name: 'LpuSection_id',
									type: 'int'
								},
								{
									mapping: 'LpuUnitType_SysNick',
									name: 'LpuUnitType_SysNick',
									type: 'string'
								},
								{
									mapping: 'MedPersonal_id',
									name: 'MedPersonal_id',
									type: 'int'
								},
								{
									mapping: 'MedPersonal2_id',
									name: 'MedPersonal2_id',
									type: 'int'
								},
								{
									mapping: 'MedPersonal3_id',
									name: 'MedPersonal3_id',
									type: 'int'
								},
								{
									mapping: 'MedStaffFact_id',
									name: 'MedStaffFact_id',
									type: 'int'
								},
								{
									mapping: 'MedStaffFact2_id',
									name: 'MedStaffFact2_id',
									type: 'int'
								},
								{
									mapping: 'MedStaffFact3_id',
									name: 'MedStaffFact3_id',
									type: 'int'
								},
								{
									mapping: 'RecordStatus_Code',
									name: 'RecordStatus_Code',
									type: 'int'
								},
								{
									mapping: 'Org_Name',
									name: 'Org_Name',
									type: 'string'
								},
								{
									dateFormat: 'd.m.Y',
									mapping: 'EvnStickWorkRelease_begDate',
									name: 'EvnStickWorkRelease_begDate',
									type: 'date'
								},
								{
									dateFormat: 'd.m.Y',
									mapping: 'EvnStickWorkRelease_endDate',
									name: 'EvnStickWorkRelease_endDate',
									type: 'date'
								},
								{
									mapping: 'MedPersonal_Fio',
									name: 'MedPersonal_Fio',
									type: 'string'
								},
								{
									mapping: 'EvnStickWorkRelease_IsPredVK',
									name: 'EvnStickWorkRelease_IsPredVK',
									type: 'int'
								},
								{
									mapping: 'Post_id',
									name: 'Post_id',
									type: 'int'
								},
								{
									mapping: 'EvnVK_id',
									name: 'EvnVK_id',
									type: 'int'
								},
								{
									mapping: 'EvnVK_NumProtocol',
									name: 'EvnVK_NumProtocol',
									type: 'string'
								},
								{
									mapping: 'EvnVK_descr',
									name: 'EvnVK_descr',
									type: 'string'
								},
								{
									mapping: 'EvnStickWorkRelease_updDT',
									name: 'EvnStickWorkRelease_updDT',
									type: 'string'
								}
							]
						}),
						showRecordMenu: function(el, indx) {
							this.recordMenu.data_id = indx;
							this.ownerCt.checkWorkReleaseMenu();
							this.recordMenu.showBy(el);
						},
						getSelectedRecord: function() {
							var indx = this.recordMenu.data_id;
							return (!Ext6.isEmpty(indx) && indx>=0 && this.getStore().getCount()>0) ? this.getStore().getAt(indx) : false;
						},
						recordMenu: Ext6.create('Ext6.menu.Menu', {
							cls: 'lvn-edit-window-menu',
							items: [{
								text: 'Редактировать',
								itemId:'WRmenuEdit',
								iconCls: 'panicon-edit-pers-info',
								//~ iconCls: 'panicon-edit',
								handler: function() {
									me.getController().openEvnStickWorkReleaseEditWindow('edit');
								}
							}, {
								text: 'Просмотр',
								itemId:'WRmenuView',
								iconCls: 'panicon-view',
								handler: function() {
									me.getController().openEvnStickWorkReleaseEditWindow('view');
								}
							}, {
								text: 'Удалить',
								itemId: 'WRmenuDelete',
								iconCls: 'panicon-del-prescr-item',
								//~ iconCls: 'panicon-delete',
								handler: function() {
									me.getController().deleteGridRecord('EvnStickWorkRelease');
								}
							}, {
								text: 'Действия',
								itemId: 'WRmenuActions',
								cls: 'toolbar-padding',
								menu: new Ext6.menu.Menu({
									itemId: 'menu',
									userCls: 'menuWithoutIcons',
									items: [
										{
											text:'Подписать (Врач)',
											//~ iconCls : 'signature16',
											itemId: 'leaveActionsSign',
											handler: function() {
												me.getController().doSign_WorkRelease({SignObject: 'MP'});
											}.createDelegate(this)
										}, {
											text:'Список версий документа (Врач)',
											//~ iconCls : 'document16',
											itemId: 'leaveActionsList',
											handler: function() {
												this.openEvnStickSignHistoryWindow({SignObject: 'MP'});
											}.createDelegate(this)
										}, {
											text:'Верификация документа (Врач)',
											//~ iconCls : 'ok16',
											itemId: 'leaveActionsCheck',
											handler: function() {
												me.getController().doVerifySign_WorkRelease({SignObject: 'MP'});
											}.createDelegate(this)
										}, {
											text:'Подписать (ВК)',
											//~ iconCls : 'signature16',
											itemId: 'leaveActionsSignVK',
											handler: function() {
												me.getController().doSign_WorkRelease({SignObject: 'VK'});
											}.createDelegate(this)
										}, {
											text:'Список версий документа (ВК)',
											//~ iconCls : 'document16',
											itemId: 'leaveActionsListVK',
											handler: function() {
												this.openEvnStickSignHistoryWindow({SignObject: 'VK'});
											}.createDelegate(this)
										}, {
											text:'Верификация документа (ВК)',
											//~ iconCls : 'ok16',
											itemId: 'leaveActionsCheckVK',
											handler: function() {
												me.getController().doVerifySign_WorkRelease({SignObject: 'VK'});
											}.createDelegate(this)
										}
									]
								})
							}, ]
						})
					})
				]
			}
		);
	},

	// 5. Исход ЛВН
	_initPanelStickLeave: function(){
		var me = this;

		me.panelStickLeave = new Ext6.create('swPanel',
			{
				autoHeight: true,
				bodyStyle: 'padding: 20 20 20 32',
				border: true,
				collapsible: true,
				itemId: 'EStEF_StickLeavePanel',
				listeners: {
					'expand': function(panel){}
				},
				title: langs('ИСХОД ЛВН'),
				defaults: {
					width: me.conf['исход'].width,
					labelWidth: me.conf['исход'].labelWidth,
					defaults: {
						width: me.conf['исход'].width,
						labelWidth: me.conf['исход'].labelWidth
					}
				},
				items: [{
						allowBlank: true,
						xtype: 'swDateField',
						fieldLabel: langs('Дата исхода ЛВН'),
						width: me.conf['исход'].labelWidth + me.conf.dateWidth,
						itemId: 'EStEF_EvnStick_disDate',
						name: 'EvnStick_disDate',
						bind: '{EvnStick_disDate}',
						selectOnFocus: true,
						//~ tabIndex: TABINDEX_ESTEF + 31,
						//~ plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
						//~ format: 'd.m.Y',
						listeners: {
							blur: 'onEvnStick_disDate'
						}
					}, {
						allowBlank: true,
						comboSubject: 'StickLeaveType',
						fieldLabel: langs('Исход ЛВН'),
						itemId: 'ESEW_StickLeaveType_id',
						name: 'StickLeaveType_id',
						value: '',
						onLoadStore: function(){},
						listeners: {//TODO: уточнить необходимость этих методов:
							//~ 'change': function(combo, newValue, oldValue) {//и так вызовется в select
								//~ combo.fireEvent('select', combo, combo.getStore().getById(newValue));
							//~ }.createDelegate(this),
							/*'keydown': function(inp, e) {
///
								var base_form = this.FormPanel.getForm();
///
								if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
									e.stopEvent();
///
									if ( !this.queryById('EStEF_EvnStickWorkReleasePanel').collapsed && this.queryById('EStEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
										this.queryById('EStEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
										this.queryById('EStEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
									}
									else if ( !this.queryById('EStEF_MSEPanel').collapsed && !base_form.findField('InvalidGroupType_id').disabled ) {
										base_form.findField('InvalidGroupType_id').focus(true);
									}
									else if ( !this.queryById('EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_stacEndDate').disabled ) {
										base_form.findField('EvnStick_stacEndDate').focus(true);
									}
									else if ( !this.queryById('EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_irrDate').disabled ) {
										base_form.findField('EvnStick_irrDate').focus(true);
									}
									else if ( !this.queryById('EStEF_EvnStickCarePersonPanel').hidden && !this.queryById('EStEF_EvnStickCarePersonPanel').collapsed && this.queryById('EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
										this.queryById('EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
										this.queryById('EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
									}
									else if ( !base_form.findField('Org_did').hidden && !base_form.findField('Org_did').disabled ) {
										base_form.findField('Org_did').focus(true);
									}
									else if ( !base_form.findField('EvnStick_BirthDate').hidden && !base_form.findField('EvnStick_BirthDate').disabled ) {
										base_form.findField('EvnStick_BirthDate').focus(true);
									}
									else if ( !base_form.findField('StickCause_did').disabled ) {
										base_form.findField('StickCause_did').focus(true);
									}
									else {
										this.buttons[this.buttons.length - 1].focus();
									}
								}
							}.createDelegate(this),*/
							
							'select': 'onSelect_StickLeaveType_id'
						},
						//~ tabIndex: TABINDEX_ESTEF + 30,
						xtype: 'commonSprCombo'
					},
					{
						name: 'EvnStick_NumNext',
						//~ listWidth: 350,
						fieldLabel: 'ЛВН-продолжение',
						xtype: 'textfield',
						bind: {
							disabled: '{readOnly}'
						}
					},
					{
						dateFieldId: 'EStEF_EvnStick_disDate',
						enableOutOfDateValidation: false,
						fieldLabel: langs('Врач'),
						name: 'MedStaffFact_id',
						itemId: 'EStEF_MedStaffFactCombo',
						lastQuery: '',
						//~ listWidth: 670,
						//~ tabIndex: TABINDEX_ESTEF + 32,
						xtype: 'swMedStaffFactCombo', //'SwMedStaffFactGlobalCombo'
						value: '',
						bind: {
							disabled: '{readOnly}'
						},
						listeners: {
							change: 'onMedStaffFact_id'
						}
					},
					{
						fieldLabel: langs('Направлен в другую МО'),
						name: 'Lpu_oid',
						//~ listWidth: 600,
						//~ tabIndex: TABINDEX_ESTEF + 33,
						xtype: 'swLpuCombo',
						value: '',
						bind: {
							disabled: '{readOnly}'
						},
						listeners: {
							change: 'resetSignStatus'
						},
					},
					{
						layout: 'column',
						itemId: 'LeaveStatus_Block',
						width: 1000,
						padding: '18 0 28 '+(me.conf['исход'].labelWidth+5),
						border: false,
						hidden: getRegionNick() == 'kz',
						defaults: {
							padding: '0 20 0 0'
						},
						items: [
							{
								itemId: 'SLeaveStatus_Icon',
								padding: '0 5 0 0',
								height: 16,
								html: '',
								border: false,
								width: 16,
								tpl: Ext6.Template('<span class="lvn-doc-{status}"></span>')
							},
							{
								xtype: 'label',
								itemId: 'SLeaveStatus_Name',
								html: ''
							},
							{
								xtype: 'label',
								itemId: 'SLeaveStatus_FIO',
								html: ''
							},
							{
								xtype: 'label',
								itemId: 'swSignStickLeave',
								iconCls: 'signature16',
								html: "<a href='#' onclick='Ext6.getCmp(\""+me.id+"\").queryById(\"swSignStickLeave\").handler();'>Подписать</a> ",
								/*bind: {
									hidden: '{!isAccessToField_StickLeave_Sign}'
								},*/
								handler: function() {
									me.getController().doSign_StickLeave();
								},
								enable: function()  {
									this.show();
								},
								disable: function() {
									this.hide();
								}
							},
							{
								xtype: 'label',
								itemId: 'swSignStickLeaveList',
								tooltip: 'Список версий документа',
								html: "<a href='#' onclick='Ext6.getCmp(\""+me.id+"\").queryById(\"swSignStickLeaveList\").handler(this);'>Список версий</a> ",
								bind: {
									//hidden: '{readOnly}'
								},
								handler: function(e) {
									me.LeaveVersions.load();
								}
							},
							{
								xtype: 'label',
								itemId: 'swSignStickLeaveCheck',
								tooltip: 'Верификация документа',
								html: "<a href='#' onclick='Ext6.getCmp(\""+me.id+"\").queryById(\"swSignStickLeaveCheck\").handler();'>Верификация</a> ",
								userCls: 'lvn-sign-link',
								handler: function() {
									me.getController().doVerifySign_WorkRelease({SignObject: 'leave'});
								}
							}
						]
					}
				]
			}
		);
	},

	initComponent: function() {

		var win = this,
			me = win;
			
		me.LeaveVersions = new Ext6.create('swSignStickListWindow' , {
			typename: 'Leave',
			parentWin: me
		});
		
		me.IrrVersions = new Ext6.create('swSignStickListWindow' , {
			typename: 'Irr',
			parentWin: me
		});
		
		var evnStickNumField = {
			allowBlank: !isPolkaRegistrator() && (isPolkaVrach() || isStacVrach() || isStacReceptionVrach() || isOperator() || isMedStatUser() || isRegLvn()),
			fieldLabel: langs('Номер'),
			name: 'EvnStick_Num',
			disabled: false,
			//~ formBind: true,
			bind: {
				value: '{EvnStick_Num}',
				disabled:'{readOnly || link || action == "view" || !(mainPanelAccess || mainPanelSomeFieldsAccess) || RegistryESStorage_id>0}'
			},
			maskRe: /\d/,
			//~ tabIndex: TABINDEX_ESTEF + 7,
			xtype: 'textfield',
			labelWidth: me.conf.labelWidth,
			width: me.conf.labelWidth+158,
			maxLength: 12, // Длина  номера составляет 12 цифр #136679
			minLength: 12,
			listeners: {
				change: 'onEvnStick_Num', //!!
				validitychange: 'updateInvalidValue'
			}
		}

		var evnStickSerField = {
			allowBlank: true,
			fieldLabel: langs('Серия'),
			name: 'EvnStick_Ser',
			//~ tabIndex: TABINDEX_ESTEF + 6,
			maskRe: /[a-zA-Zа-яА-Я]/,
			regex : /[a-zA-Zа-яА-Я]+/,
			width: 100,
			xtype: 'textfield',
			bind: {
				disabled: '{ readOnly || link || action=="view" || !mainPanelAccess}'
			}
		}

		if ( getRegionNick() == 'kz' ) {
			evnStickNumField.allowBlank = true;
			evnStickNumField.autoCreate = {
				tag: 'input',
				type: 'text',
				maxLength: '7'
			};
			evnStickNumField.maxLength = 7;
			evnStickNumField.minLength = 7;
			evnStickSerField.allowBlank = true;
			evnStickSerField.autoCreate = {
				tag: 'input',
				type: 'text',
				maxLength: '2'
			};
			evnStickSerField.maxLength = 2;
			evnStickSerField.minLength = 2;
			evnStickSerField.toUpperCase = true;
			//~ evnStickSerField.xtype = 'textfieldpmw';
			evnStickSerField.plugins = [ new Ext6.ux.Translit(true, false) ];
		}

		// 1. Список пациентов, нуждающихся в уходе
		me.panelEvnStickCarePerson = new Ext6.create('DefaultEvnStickPanelPlus',
			{
				plusButton: function() { me.getController().openEvnStickCarePersonEditWindow('add'); },
				plusBindHidden: '{action=="view" || !carePersonMSEAccess}',
				border: true,
				collapsible: true,
				collapsed: true,
				itemId: 'EStEF_EvnStickCarePersonPanel',
				isLoaded: false,

				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							var evn_stick_id;

							if ( this.FormPanel.getForm().findField('EvnStickDop_pid').getValue() > 0 ) {
								evn_stick_id = me.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
							}
							else {
								evn_stick_id = me.FormPanel.getForm().findField('EvnStick_id').getValue();
							}

							panel.isLoaded = true;

							panel.queryById('EStEF_EvnStickCarePersonGrid').getStore().load({
								params: {
									EvnStick_id: evn_stick_id
								},
								callback: function(){
									me.getController().checkRebUhod();
								}
							});
						}

						//~ panel.doLayout();
					}.createDelegate(this)
				},

				title: langs('СПИСОК ПАЦИЕНТОВ, НУЖДАЮЩИХСЯ В УХОДЕ'),
				items: [ me.gridEvnStickCarePerson = new Ext6.grid.Panel({
					xtype: 'grid',
					cls: 'EmkGrid',
					width: me.conf.width,
					itemId: 'EStEF_EvnStickCarePersonGrid',
					disableSelection: true,
					columns: [
					{
						dataIndex: 'Person_Age',
						header: langs('Возраст'),
						hidden: false,
						resizable: true,
						sortable: true,
						width: 100
					},
					{
						dataIndex: 'RelatedLinkType_Name',
						header: langs('Родственная связь'),
						hidden: false,
						resizable: true,
						sortable: true,
						width: 200
					},
					{
						dataIndex: 'Person_Fio',
						header: langs('ФИО Пациента'),
						hidden: false,
						//~ resizable: true,
						sortable: true,
						flex: 1
					}, {
							width: 40,
							dataIndex: 'EvnStickWorkRelease_Action',
							renderer: function (value, metaData, record) {
								return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.queryById('EStEF_EvnStickCarePersonGrid').id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
							}
					}],
					store: new Ext6.data.JsonStore({
						autoLoad: false,
						proxy: {
							type: 'ajax',
							url: '/?c=Stick&m=loadEvnStickCarePersonGrid',
							reader: {
								type: 'json'
							}
						},
						idProperty: 'EvnStickCarePerson_id',
						listeners: {
							'load': function(panel) {
								me.getController().onLoadEvnStickCarePersonGrid(panel);
							},
						},
						fields: [{
							mapping: 'accessType',
							name: 'accessType',
							type: 'string'
						}, {
							mapping: 'EvnStickCarePerson_id',
							name: 'EvnStickCarePerson_id',
							type: 'int'
						}, {
							mapping: 'Person_id',
							name: 'Person_id',
							type: 'int'
						}, {
							// Тот, кому выдается ЛВН
							mapping: 'Person_pid',
							name: 'Person_pid',
							type: 'int'
						}, {
							mapping: 'RelatedLinkType_id',
							name: 'RelatedLinkType_id',
							type: 'int'
						}, {
							mapping: 'RecordStatus_Code',
							name: 'RecordStatus_Code',
							type: 'int'
						}, {
							mapping: 'Person_Age',
							name: 'Person_Age',
							type: 'int'
						}, {
							mapping: 'Person_Fio',
							name: 'Person_Fio',
							type: 'string'
						}, {
							mapping: 'RelatedLinkType_Name',
							name: 'RelatedLinkType_Name',
							type: 'string'
						}, {
							mapping: 'Person_Surname',
							name: 'Person_Surname',
							type: 'string'
						}, {
							mapping: 'Person_Firname',
							name: 'Person_Firname',
							type: 'string'
						}, {
							mapping: 'Person_Secname',
							name: 'Person_Secname',
							type: 'string'
						}, {
							mapping: 'Person_Birthday',
							name: 'Person_Birthday',
							type: 'string'
						}]
					}),//end store
					//~ layout: 'fit',
					showRecordMenu: function(el, indx) {
						this.recordMenu.data_id = indx;
						this.recordMenu.showBy(el);
					},
					getSelectedRecord: function() {
						var indx = this.recordMenu.data_id;
						return (!Ext6.isEmpty(indx) && indx>=0 && this.getStore().getCount()>0) ? this.getStore().getAt(indx) : false;
					},
					recordMenu: Ext6.create('Ext6.menu.Menu', {
						cls: 'lvn-edit-window-menu',
						items: [{
							text: 'Редактировать',
							itemId: 'CPmenuEdit',
							//~ iconCls:'menu_dispedit',
							iconCls: 'panicon-edit-pers-info',
							handler: function() {
								me.getController().openEvnStickCarePersonEditWindow('edit');
							}
						}, {
							text: 'Просмотр',
							itemId: 'CPmenuView',
							iconCls: 'panicon-view',
							handler: function() {
								me.getController().openEvnStickCarePersonEditWindow('view');
							}
						}, {
							text: 'Удалить',
							itemId: 'CPmenuDelete',
							//~ iconCls:'menu_dispdel',
							iconCls: 'panicon-del-prescr-item',
							handler: function() {
								me.getController().deleteGridRecord('EvnStickCarePerson');
							}
						}]
					}),
					listeners: {
						'rowdblclick': function(grid, number, obj) {
							this.getController().openEvnStickCarePersonEditWindow('edit');
						}.createDelegate(this)
					}
				})]
			}
		);
		// 2. Режим
		me.panelStickRegime = new Ext6.create('swPanel',
			{
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				itemId: 'EStEF_StickRegimePanel',
				listeners: {
					'expand': function(panel) {
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: langs('РЕЖИМ'),
				defaults: {
					width: me.conf['режим'].width,
					labelWidth: me.conf['режим'].labelWidth,
				},
				items: [
					{
						allowBlank: true,
						comboSubject: 'StickIrregularity',
						fieldLabel: langs('Нарушение режима'),
						xtype: 'commonSprCombo',
						name: 'StickIrregularity_id',
						value: '',
						bind: {
							disabled: '{readOnly}'
						},
						listeners: {
							change: 'onStickIrregularity_id'
						},
						//~ tabIndex: TABINDEX_ESTEF + 21,
						//~ enableKeyEvents: true,
					},
					{
						allowBlank: true,
						width: me.conf['режим'].labelWidth + me.conf.dateWidth,
						fieldLabel: langs('Дата нарушения режима'),
						xtype: 'swDateField',
						name: 'EvnStick_irrDate',
						//~ plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
						//~ selectOnFocus: true,
						bind: {
							disabled: '{readOnly}'
						}
					},
					{
						layout: 'column',
						itemId: 'IrrStatus_Block',
						padding: '12 0 18 '+(me.conf['режим'].labelWidth+5),
						width: 1000,
						border: false,
						hidden: getRegionNick() == 'kz',
						items: [
							{
								itemId: 'SIrrStatus_Icon',
								padding: '0 5 0 0',
								height: 16,
								html: '',
								border: false,
								width: 16,
								tpl: Ext6.Template('<span class="lvn-doc-{status}"></span>'),
							},
							{
								itemId: 'SIrrStatus_Name',
								userCls: 'lvn-sign-link',
								html: '',
								xtype: 'label'
							},
							{
								xtype: 'label',
								itemId: 'swSignStickIrr',
								userCls: 'lvn-sign-link',
								hidden: true,
								html: "<a href='#' onclick='Ext6.getCmp(\""+me.id+"\").queryById(\"swSignStickIrr\").handler();'>Подписать</a> ",
								handler: function() {
									me.getController().doSign_StickRegime();
								},
								enable: function() {
									this.show();
								},
								disable: function() {
									this.hide();
								},
								bind: {
									hidden: '{!isAccessToField_swSignStickIrr}'
								}
							},
							{
								xtype: 'label',
								itemId: 'swSignStickIrrList',
								tooltip: 'Список версий документа',
								userCls: 'lvn-sign-link',
								//~ hidden: true,
								html: "<a href='#' onclick='Ext6.getCmp(\""+me.id+"\").queryById(\"swSignStickIrrList\").handler(this);'>Список версий</a> ",
								handler: function(label) {
									//~ win.getController().openEvnStickSignHistory({SignObject: 'irr'}, label);
									//~ win.getController().doOpenSignHistory_WorkRelease({SignObject: 'irr'});
									//TODO: тултип с таблицей
									me.IrrVersions.load();
								}
							},
							{
								xtype: 'label',
								padding: '0 0 0 0',
								itemId: 'swSignStickIrrCheck',
								tooltip: 'Верификация документа',
								userCls: 'lvn-sign-link',
								//~ hidden: true,
								html: "<a href='#' onclick='Ext6.getCmp(\""+me.id+"\").queryById(\"swSignStickIrrCheck\").handler();'>Верификация</a> ",
								handler: function() {
									me.getController().doVerifySign_WorkRelease({SignObject: 'irr'});
								}
							}
						]
					},
					{
						title: langs('Лечение в стационаре'),
						width: 738,
						xtype: 'fieldset',
						defaults: {
							padding: '0 0 0 10'
						},
						items: [
							Ext6.create('Ext6.date.RangeField', {
								hidden: true, //добавлено в ext6 макете. скрыл, т.к. непонятно, как работать с периодом если одна из дат должна быть незаполнена
								fieldLabel: 'Период лечения',
								width: 410,
								labelWidth: 205,
								itemId: 'EvnStick_stacDates',
								name: 'EvnStick_stacDates',
								bind: {
									disabled: '{readOnly}'
								},
								listeners: {
									'change': function (cm, ov, nv) {
									},
									'set': function() {
										var begDate = me.queryById('EvnStick_stacBegDate');
										var endDate = me.queryById('EvnStick_stacEndDate');
										begDate.setValue(this.getDateFrom());
										endDate.setValue(this.getDateTo());
									}
								}
							}),
							{
								layout: 'column',
								border: false,
								items: [
								{
									width: 90+me.conf.dateWidth,
									labelWidth: 90,
									xtype: 'swDateField',
									name: 'EvnStick_stacBegDate',
									itemId: 'EvnStick_stacBegDate',
									fieldLabel: langs('Дата начала'),
									bind: {
										disabled: '{readOnly || !isAccessToField_EvnStick_stacBegDate}'
									},
									listeners: {
										blur: 'onEvnStick_stacBegDate'
									}
									//~ format: 'd.m.Y',
									//~ plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
								},
								{
									width: 110+me.conf.dateWidth,
									labelWidth: 110,
									padding: '0 0 0 30',
									xtype: 'swDateField',
									name: 'EvnStick_stacEndDate',
									itemId: 'EvnStick_stacEndDate',
									fieldLabel: langs('Дата окончания'),
									bind: {
										disabled: '{readOnly || !isAccessToField_EvnStick_stacEndDate}'
									},
									//~ format: 'd.m.Y',
									//~ plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
									/*listeners: {
										'keydown': function(inp, e) {
											if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
												var base_form = this.FormPanel.getForm();

												e.stopEvent();

												if ( !this.findById(me.id+'EStEF_MSEPanel').collapsed && this.action != 'view' ) {
													base_form.findField('EvnStick_mseDate').focus(true);
												}
												else if ( !this.findById(me.id+'EStEF_EvnStickWorkReleasePanel').collapsed && this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
													this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
													this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
													base_form.findField('StickLeaveType_id').focus(true);
												}
												else if ( this.action != 'view' ) {
													this.buttons[0].focus();
												}
												else {
													this.buttons[1].focus();
												}
											}
										}.createDelegate(this)
									} */
								}/*,
								{//не используется в макете
									xtype: 'hidden',
									text: '=',
									itemId: 'EStEF_btnSetMinDateFromPS',
									bind: {
										disabled: '{readOnly || !isAccessToField_EStEF_btnSetMinDateFromPS}'
									},
									tooltip: langs('Подставить минимальную дату поступления со связанных карт выбывшего из стационара'),
									handler: function() {
										var base_form = this.FormPanel.getForm();
										if (!base_form.findField('EvnStick_stacBegDate').disabled && this.advanceParams.stacBegDate) {
											base_form.findField('EvnStick_stacBegDate').setValue(this.advanceParams.stacBegDate);
										} else {
											base_form.findField('EvnStick_stacBegDate').setValue('');
										}
										base_form.findField('EvnStick_stacBegDate').fireEvent('change', base_form.findField('EvnStick_stacBegDate'), base_form.findField('EvnStick_stacBegDate').getValue());
									}.createDelegate(this)
								},
								{ //не используется в макете
									xtype: 'hidden',
									text: '=',
									itemId: 'EStEF_btnSetMaxDateFromPS',
									bind: {
										disabled: '{readOnly || !isAccessToField_EStEF_btnSetMaxDateFromPS}'
									},
									tooltip: langs('Подставить максимальную дату выписки со связанных карт выбывшего из стационара'),
									handler: function() {
										var base_form = this.FormPanel.getForm();
										if (!base_form.findField('EvnStick_stacEndDate').disabled && this.advanceParams.stacEndDate) {
											base_form.findField('EvnStick_stacEndDate').setValue(this.advanceParams.stacEndDate);
										} else {
											base_form.findField('EvnStick_stacEndDate').setValue('');
										}
										base_form.findField('EvnStick_stacEndDate').fireEvent('change', base_form.findField('EvnStick_stacEndDate'), base_form.findField('EvnStick_stacEndDate').getValue());
									}.createDelegate(this)
								}*/]
							}
						]
					},
					{
						hidden: getRegionNick()!='kz',
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						title: langs('Перевести временно на другую работу'),
						xtype: 'fieldset',
						items: [
							{
								allowBlank: true,
								fieldLabel: langs('Дата начала'),
								xtype: 'swDateField',
								name: 'EvnStick_regBegDate',
								width: me.conf.fieldset.labelWidth + me.conf.dateWidth,
								bind: {
									disabled: '{readOnly}'
								}
								//~ format: 'd.m.Y',
								//~ plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								//~ selectOnFocus: true,
								//~ tabIndex: TABINDEX_ESTEF + 15,
								//~ enableKeyEvents: true,
							},
							{
								allowBlank: true,
								fieldLabel: langs('Дата окончания'),
								xtype: 'swDateField',
								name: 'EvnStick_regEndDate',
								width: me.conf.fieldset.labelWidth + me.conf.dateWidth,
								bind: {
									disabled: '{readOnly}'
								}
								//~ format: 'd.m.Y',
								//~ plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								//~ selectOnFocus: true,
								//~ tabIndex: TABINDEX_ESTEF + 15,
								//~ ,enableKeyEvents: true,
							}
						]
					}
				]
			}
		);

		// 3. МСЭ
		me._initPanelMSE();

		// 4. Освобождение от работы
		me._initPanelEvnStickWorkRelease();

		// 5. Исход ЛВН
		me._initPanelStickLeave();

		win.PrimaryPanel = new Ext6.create('swPanel', {
			bodyPadding: me.conf.primarypanel.bodypadding,
			//~ autoHeight: true,
			width: '100%',
			border: false,
			title: null,
			collapsible: false,
			collapsed: false,
			defaults: {
				labelWidth: me.conf.labelWidth, //215,
				width: me.conf.width, //705,
				border: false,
				defaults: {
					labelWidth: me.conf.labelWidth,
					border: false,
					width: me.conf.width
				}
			},
			items: [
				//TAG: Блок основных данных формы
				{
					name: 'EvnStickBase_IsFSS',
					fieldLabel: 'ЛВН из ФСС',
					hidden: true,//в новом макете нет
					xtype: 'textfield'
				},
				{
					itemId: 'EStEF_ESSConsent',
					title: 'Согласие на получение ЭЛН',
					bind: {
						hidden: getRegionNick()=='kz' ? true : //собрал все условия из вложенных элементов:
						'{(action=="view" || !EvnStickBase_consentDT || EvnStick_disDate || StickFSSData_id) '
						+'&& (!EvnStickBase_consentDT || EvnStickFullNameText=="" || StickCause_id>0) '
						+'&& !(EvnStickBase_consentDT && EvnStickFullNameText!="" && StickCause_id>0) '
						+'&& (readOnly || action=="view" || EvnStickBase_consentDT || StickFSSData_id || !(!EvnStick_disDate || EvnStick_IsNotOriginal || StickWorkType_id==2))}' 
					},
					xtype: 'fieldset',
					width: me.conf.fieldset.width,
					items: [
						{
							layout: 'column',
							width: 980,
							padding: '4 0 8 0', //поля для блока "согласие" по макету
							border: false,
							items: [
								{//если согласие ЭЛН есть, показываем дату:
									xtype: 'label',
									text: '',
									itemId: 'EvnStickBase_consentDT',
									padding: '0 0 0 '+me.conf.fieldset.paddingleft
								}, {
									allowBlank: true,
									xtype: 'datefield',
									fieldLabel: 'Дата согласия',//поле скрыто
									hidden: true,
									format: 'd.m.Y',
									name: 'EvnStickBase_consentDT',
									bind: '{EvnStickBase_consentDT}',
									width: 500,
									listeners: {
										change: 'onEvnStickBase_consentDT'
									}
								},
								{
									xtype: 'label',
									border: false,
									padding: '0 0 0 '+me.conf.fieldset.paddingleft,
									itemId: 'EStEF_ESSConsentEdit',
									html: "<a href='#' onclick='Ext6.getCmp(\""+win.id+"\").queryById(\"EStEF_ESSConsentEdit\").handler();'>Согласие на получение ЭЛН</a> ",
									bind: {
										hidden: '{action=="view" || !EvnStickBase_consentDT || EvnStick_disDate || StickFSSData_id}'
									},
									handler: function() {
										me.getController().openESSConsent('edit');
									}
								},
								{
									xtype: 'button',
									itemId: 'EStEF_ESSConsentPrint',
									tooltip: 'Печать',
									userCls: 'button-without-frame',
									iconCls: 'panicon-print',
									padding: '0 0 0 '+me.conf.fieldset.paddingleft,
									bind: {
										hidden: '{!(EvnStickBase_consentDT && EvnStickFullNameText!="" && StickCause_id>0)}'
									},
									handler: function() {
										me.getController().doPrintESSConsent();
									}
								},
								{
									//согласие добавить
									xtype: 'label',
									border: false,
									padding: '0 0 0 '+me.conf.fieldset.paddingleft,
									itemId: 'EStEF_ESSConsentAdd',
									html: "<a href='#' onclick='Ext6.getCmp(\""+win.id+"\").queryById(\"EStEF_ESSConsentAdd\").handler();'>Добавить согласие на получение ЭЛН</a> ",
									bind: {
										hidden: '{readOnly || action=="view" || EvnStickBase_consentDT || StickFSSData_id || !(!EvnStick_disDate || EvnStick_IsNotOriginal || StickWorkType_id==2) }'
									},
									handler: function() {
										me.getController().openESSConsent('add');
									}
								}
							]
						}
					]
				},
				{//выравнивание из-за fieldset
					layout: 'vbox',
					xtype: 'panel',
					margin: '0 0 0 '+me.conf.fieldset.paddingleft,//выравнивание по тексту в fieldset
					width: '100%',
					border: false,
					items: [
					// Серия
					evnStickSerField,
					{
						layout: 'column',
						width: '100%',
						items: [
							//Поле: Номер
							evnStickNumField,
						{
							layout: 'column',
							border: false,
							width: '100%',
							hidden: getRegionNick() == 'kz',
							items: [
							{
								xtype: 'label',
								border: false,
								style: {
									padding: '5px 0px 0px 8px'
								},
								itemId: 'ClearEvnStickNumButton',
								bind: {
									hidden: '{readOnly || EvnStick_Num<1 || !RegistryESStorage_id || action=="view" || isPaid || isInReg || hasWorkReleaseIsInReg || hasWorkReleaseIsPaid}'
								},
								html: "<a href='#' onclick='Ext6.getCmp(\""+win.id+"\").getController().doClearEvnStickNumButton();'>Удалить номер ЭЛН</a> "
							}, {
								xtype: 'label',
								border: false,
								style: {
									padding: '5px 0px 0px 5px'
								},
								itemId: 'GetEvnStickNumButton',
								bind: {
									hidden: '{readonly || !(!EvnStick_Num && (EvnStickBase_consentDT ||(StickWorkType_id==2 && ConsentInAnotherLvn)) )}',
								},
								html: "<a href='#' onclick='Ext6.getCmp(\""+win.id+"\").getController().doGetEvnStickNumButton();'>Получить номер ЭЛН</a> "
							}
							]
						}]
					},
					{
						//Первичный/продолжение (Порядок выдачи)
						border: false,
						layout: 'column',
						padding: '0 0 0 '+(me.conf.labelWidth+5),
						items: [{
							hidden: true,
							xtype: 'commonSprCombo',
							comboSubject: 'StickOrder',
							name: 'StickOrder_spr'
						}, {
							allowBlank: false,
							xtype: 'checkbox',
							boxLabel: langs('Первичный'), //subject: StickOrder
							width: 120,
							//~ tabIndex: TABINDEX_ESTEF + 4,
							name: 'StickOrder_id',
							bind: {
								value: '{StickOrder_id}',
								disabled: '{readOnly || link || action=="view" || !mainPanelAccess || EvnStickDop_pid}'
							},
							listeners: {
								change: 'onStickOrder_id' //!!
							},
							value: 1,
							inputValue: 1,
							uncheckedValue: 2,
							getRawValue: function(){
								return this.value ? 1 : 2;
							},
							clearValue: function() {
								this.setRawValue(true);
							},
							getFieldValue: function(name) {//для совместимости с ext2
								switch(name) {
									case 'StickOrder_Code': return this.getValue() ? 1 : 2;
									case 'StickOrder_Name': return this.getValue() ? 'первичный ЛВН' : 'продолжение ЛВН';
								}
							}
						}, {
							xtype: 'baseCombobox',
							name: 	'EvnStickLast_id',
							itemId: 'EvnStickLast_id',
							bind: {
								hidden: '{StickOrder_id}',
								allowBlank: '{StickOrder_id}',
								disabled: '{readOnly || link || action=="view" || !mainPanelAccess}'
							},
							queryMode: 'local',
							allowBlank: true,
							width: 360,
							labelWidth: 120,
							//~ maxWidth : 400,
							matchFieldWidth: false,
							flex: 1,
							editable: false,
							fieldLabel: langs('Продолжение ЛВН'),
							valueField: 'EvnStick_id',
							displayField: 'EvnStick_Num',
							listeners: {
								/*'keydown': function(inp, e) {
									if ( inp.disabled ) {
										return false;
									}
									if( e.getKey() == e.DELETE || e.getKey() == e.BACKSPACE ) {
										e.stopEvent();
										var base_form = this.FormPanel.getForm();
										base_form.findField('EvnStickLast_id').clear();
									}
								}.createDelegate(this),*/
								'select': function( combo, record, eOpts ) {
									if(record.get('disabled')==1 && combo.getValue()!=combo.LoadedValue) {
										Ext6.Msg.show({
											buttons: Ext6.Msg.OK,
											fn: function() {
												//~ combo.setValue(combo.LoadedValue); //~ combo.setValue(); //либо восстанавливать первоначальный
												combo.clear();
											}.createDelegate(this),
											icon: Ext6.Msg.ERROR,
											msg: langs('Выбранный ЛВН уже имеет продолжение.'),
											title: langs('Ошибка')
										});
									}
								}
							},
							/*load: function() {
								var combo = me.FormPanel.getForm().findField('EvnStickLast_id');
								combo.getStore().load({
									params: {
										Person_id: me.FormPanel.getForm().findField('Person_id').getValue()
									}
								});
							},*/
							tpl: new Ext6.XTemplate(
								'<tpl for="."><div class="x6-boundlist-item">',
								'<table style="border: 0; width: 360px;">',
								'<tr>',
								'<td class="lvn-combo-number-num">{EvnStick_Num}</td>',
								'<td class="lvn-combo-number-title">Открыт {[this.formatDate(values.EvnStick_setDate)]}&nbsp;/ Закрыт {[this.formatDate(values.EvnStick_disDate)]}&nbsp;</td>',
								'</tr></table>',
								'</div></tpl>',
								{
									formatDate: function(d) {
										return Ext6.util.Format.date(d, 'd.m.Y');
									}
								}
							),
							store: new Ext6.data.JsonStore({
								autoLoad: false,
								proxy: {
									type: 'ajax',
									url: '/?c=Stick&m=loadEvnStickList',
									reader: {
										type: 'json'
									}
								},
								idProperty: 'EvnStick_id',
								fields: [
									{ name: 'EvnStick_id', mapping:'id' },
									{ name: 'Org_id', mapping:'Org_id' },
									{ name: 'EvnStick_OrgNick', mapping:'EvnStick_OrgNick' },
									{ name: 'Post_Name', mapping:'Post_Name' },
									{ name: 'disabled', mapping:'disabled' },
									{ name: 'MaxDaysLimitAfterStac', mapping:'MaxDaysLimitAfterStac' },
									{ name: 'StickLeaveType_Code', mapping:'StickLeaveType_Code' },
									{ name: 'EvnStick_stacBegDate', mapping:'EvnStick_stacBegDate' },
									{ name: 'EvnStickWorkRelease_begDate', mapping:'EvnStickWorkRelease_begDate' },
									{ name: 'EvnStickWorkRelease_endDate', mapping:'EvnStickWorkRelease_endDate' },
									{ name: 'ResumedIn', mapping:'ResumedIn' },
									{ name: 'ResumedInNum', mapping:'ResumedInNum' },
									{ name: 'EvnStick_setDate', mapping:'EvnStick_setDate' },
									{ name: 'EvnStick_disDate', mapping:'EvnStick_disDate' },
									{ name: 'EvnStick_Ser', mapping:'EvnStick_Ser' },
									{ name: 'EvnStick_Num', mapping:'EvnStick_Num' },
									{ name: 'StickOrder_Name', mapping:'StickOrder_Name' },
									{ name: 'EvnStatus_Name', mapping:'EvnStatus_Name' },
									{ name: 'StickWorkType_Name', mapping:'StickWorkType_Name' }
								],
								filters: [
									function(item) {//убираем дубли
										var id = item.get('EvnStick_id');
										if(!me.EvnStickLast_val) me.EvnStickLast_val ={};//счетчик дублей

										if (me.EvnStickLast_val[id]) {
											return false;
										} else {
											me.EvnStickLast_val[id] = true;
											return true;
										}
									}
								],
								listeners: {
									'load': function(obj, records, successful, operation, eOpts) {
										me.EvnStickLast_val={};
										var combo = me.queryById('EvnStickLast_id');
										if(combo.getValue()) combo.setValue(combo.getValue());
									}
								}
							}),
							clear: function() {
								var base_form = win.FormPanel.getForm();
								base_form.findField('EvnStickLast_id').clearValue();
								base_form.findField('EvnStick_Num').setRawValue('');
								base_form.findField('EvnStick_prid').setValue(0);
								base_form.findField('PridStickLeaveType_Code2').setValue(0);
								base_form.findField('EvnStick_Ser').setRawValue('');
								base_form.findField('EvnStickLast_id').setRawValue('');
								if (win.advanceParams && win.advanceParams.stacBegDate) {
									base_form.findField('EvnStick_stacBegDate').setValue(win.advanceParams.stacBegDate);
								}
							},
							//~ tabIndex: TABINDEX_ESTEF + 5,
						}]
					}, { //TAG: Оригинал/Дубликат
						border: false,
						layout: 'column',
						padding: '0 0 0 '+(me.conf.labelWidth+5),
						items: [{
							allowBlank: false,
							boxLabel: langs('Дубликат'),
								//comboSubject: 'OriginType',
								//	fields:
								//		[
								//			{name: 'EvnStick_IsOriginal', type: 'int'},
								//			{name: 'OriginType_Name', type: 'string'}
								//		],
								//	data: [[1, langs('Оригинал')], [2,langs('Дубликат')]]
							name: 'EvnStick_IsOriginal',

							bind: {	value: '{EvnStick_IsNotOriginal}', //т.к. =true если дубликат (=2)
								disabled: '{readOnly || !mainPanelAccess || EvnStickFullNameText=="" }'
							},
							//~ tabIndex: TABINDEX_ESTEF + 2,
							width: 120,
							xtype: 'checkbox',
							uncheckedValue: '1',
							inputValue: '2',
							getRawValue: function(){
								return this.value ? '2' : '1';
							},
							listeners: {
								change: 'onEvnStick_IsOriginal'//!!
							},
							clearValue: function() {
								this.setRawValue(false);
							},

						}, {
							displayField: 'EvnStick_Num',
							fieldLabel: langs('Оригинал ЛВН'),
							valueField: 'EvnStick_id',
							width: 360,
							labelWidth: 120,
							matchFieldWidth: false,
							editable: false,
							queryMode: 'local',
							xtype: 'baseCombobox',
							name: 'EvnStick_oid',
							itemId: 'EvnStick_oid',
							value: '',
							bind: {
								disabled: '{readOnly}'
							},
							listeners: {
								blur: 'onEvnStick_oid'
							},
							tpl: new Ext6.XTemplate(
								'<tpl for="."><div class="x6-boundlist-item">',
								'<table style="border: 0; width: 360px;">',
								'<tr>',
								'<td class="lvn-combo-number-num">{EvnStick_Num}</td>',
								'<td class="lvn-combo-number-title">Открыт {[this.formatDate(values.EvnStick_setDate)]}&nbsp;/ Закрыт {[this.formatDate(values.EvnStick_disDate)]}&nbsp;</td>',
								'</tr></table>',
								'</div></tpl>',
								{
									formatDate: function(d) {
										return Ext6.util.Format.date(d, 'd.m.Y');
									}
								}
							),
							store: new Ext6.data.JsonStore({
								autoLoad: false,
								proxy: {
									type: 'ajax',
									url: '/?c=Stick&m=getEvnStickOriginalsList',
									reader: {
										type: 'json'
									}
								},
								idProperty: 'EvnStick_id',
								fields: [
										{ name: 'EvnStick_BirthDate', mapping: 'EvnStick_BirthDate' },
										{ name: 'EvnStick_disDate', mapping: 'EvnStick_disDate' },
										{ name: 'EvnStick_id', mapping: 'EvnStick_id' },
										{ name: 'EvnStick_nid', mapping: 'EvnStick_nid' },
										{ name: 'EvnStick_GridId', mapping: 'EvnStick_GridId' },
										{ name: 'EvnStick_irrDate', mapping: 'EvnStick_irrDate' },
										{ name: 'EvnStick_IsDisability', mapping: 'EvnStick_IsDisability' },
										{ name: 'InvalidGroupType_id', mapping: 'InvalidGroupType_id' },
										{ name: 'EvnStick_StickDT', mapping: 'EvnStick_StickDT' },
										{ name: 'EvnStick_IsRegPregnancy', mapping: 'EvnStick_IsRegPregnancy' },
										{ name: 'EvnStick_mseDate', mapping: 'EvnStick_mseDate' },
										{ name: 'EvnStick_mseExamDate', mapping: 'EvnStick_mseExamDate' },
										{ name: 'EvnStick_mseRegDate', mapping: 'EvnStick_mseRegDate' },
										{ name: 'EvnStick_Num', mapping: 'EvnStick_Num' },
										{ name: 'EvnStickDop_pid', mapping: 'EvnStickDop_pid' },
										{ name: 'EvnStick_prid', mapping: 'EvnStick_prid' },
										{ name: 'EvnStick_Ser', mapping: 'EvnStick_Ser' },
										{ name: 'EvnStick_setDate', mapping: 'EvnStick_setDate' },
										{ name: 'EvnStick_sstBegDate', mapping: 'EvnStick_sstBegDate' },
										{ name: 'EvnStick_sstEndDate', mapping: 'EvnStick_sstEndDate' },
										{ name: 'EvnStick_sstNum', mapping: 'EvnStick_sstNum' },
										{ name: 'EvnStick_stacBegDate', mapping: 'EvnStick_stacBegDate' },
										{ name: 'EvnStick_stacEndDate', mapping: 'EvnStick_stacEndDate' },
										{ name: 'EvnStick_Title', mapping: 'EvnStick_Title' },
										{ name: 'EvnStickLast_Title', mapping: 'EvnStickLast_Title' },
										{ name: 'Lpu_oid', mapping: 'Lpu_oid' },
										{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
										{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
										{ name: 'Person_Snils', mapping: 'Person_Snils'},
										{ name: 'Org_did', mapping: 'Org_did' },
										{ name: 'Org_Nick', mapping: 'Org_Nick' },
										{ name: 'Person_id', mapping: 'Person_id' },
										{ name: 'Person_Fio', mapping: 'Person_Fio' },
										{ name: 'PersonEvn_id', mapping: 'PersonEvn_id' },
										{ name: 'Server_id', mapping: 'Server_id' },
										{ name: 'StickWorkType_id', mapping: 'StickWorkType_id' },
										{ name: 'StickCause_did', mapping: 'StickCause_did' },
										{ name: 'StickCause_id', mapping: 'StickCause_id' },
										{ name: 'StickCause_SysNick', mapping: 'StickCause_SysNick'},
										{ name: 'StickCauseDopType_id', mapping: 'StickCauseDopType_id' },
										{ name: 'StickIrregularity_id', mapping: 'StickIrregularity_id' },
										{ name: 'StickLeaveType_id', mapping: 'StickLeaveType_id' },
										{ name: 'StickOrder_id', mapping: 'StickOrder_id' },
										{ name: 'Post_Name', mapping: 'Post_Name' },
										{ name: 'EvnStick_OrgNick', mapping: 'EvnStick_OrgNick' },
										{ name: 'EvnStickBase_consentDT', mapping: 'EvnStickBase_consentDT' },
										{ name: 'Org_id', mapping: 'Org_id' },
										{ name: 'Status', mapping: 'Status' }
									],
								listeners: {
									load: function() {
										win.getController().onLoadEvnStick_oid();
									}
								},
								//~ sortInfo: {
									//~ field: 'EvnStick_Title'
								//~ }

							}),
							
							//~ tabIndex: TABINDEX_ESTEF + 3
						}]
					}, {
						xtype: 'swDateField',
						fieldLabel: langs('Дата выдачи'),
						name: 'EvnStick_setDate',
						width: me.conf.labelWidth + 110,
						bind: {
							disabled: '{ readOnly || link || action=="view" || !mainPanelAccess}'
						}
					}, {//TAG: ФИО
						xtype: 'textfield',
						fieldLabel: langs('ФИО'),
						name: 'EvnStickFullNameText',
						bind: { value: '{EvnStickFullNameText}',
							disabled: '{ readOnly || link || fromList || action=="view" || !mainPanelAccess || EvnStick_IsNotOriginal == true  || EvnStickDop_pid || StickCause_SysNick != "karantin" || StickCause_SysNick != "uhod" || StickCause_SysNick != "uhodnoreb" || StickCause_SysNick != "uhodreb" || StickCause_SysNick != "rebinv" || StickCause_SysNick != "postvaccinal" || StickCause_SysNick != "vich"}'
						},
						disabled: true,
						editable: false,
						listeners: {
							//~ blur: 'onEvnStickFullNameText',
							change: 'onEvnStickFullNameText',
							validitychange: 'updateInvalidValue'
						},
						triggers: {
							search: {
								cls: 'x6-form-search-trigger',
								extraCls: 'search-icon-out',
								handler: function() {
									win.getController().triggerSearchPerson();
								}
							}
						}
					}, {
						border: false,
						hidden: getRegionNick() == 'kz',
						layout: 'column',
						style: {
							padding: '0px 0px 5px 0px'
						},
						width: '100%',
						items: [{
								fieldLabel: 'СНИЛС',
								labelWidth: me.conf.labelWidth,
								width: me.conf.labelWidth+158,
								disabled: true,
								name: 'PersonSnils',
								xtype: 'textfield',
								bind: {
									value: '{PersonSnils}',
								}
							}, {
								itemId: 'setSnilsButton',
								xtype: 'label',
								hidden: true,
								style: {
									padding: '5px 0px 0px 8px'
								},
								html: '<a href="#" onClick="Ext6.getCmp(\''+win.id+'\').getController().setSnilsButtonOnClick();return false;">Указать СНИЛС</a>'
							}
						]
					}, {//TAG: Тип занятости
						allowBlank: getRegionNick()=='kz',
						hidden: getRegionNick()=='kz',
						comboSubject: 'StickWorkType',
						fieldLabel: langs('Тип занятости'),
						name: 'StickWorkType_id',
						bind: {
							value: '{StickWorkType_id}',
							disabled: '{ readOnly || !mainPanelAccess }' // !mainPanelAccess || action!="add"
						},
						
						xtype: 'commonSprCombo',
						listeners: {
							change: 'onStickWorkType_id',
							validitychange: 'updateInvalidValue'
						}
					}, {
						displayField: 'EvnStick_Title',
						fieldLabel: langs('ЛВН по основному месту работы'),
						name: 'EvnStickDop_pid',
						bind: {
							value: '{EvnStickDop_pid}',
							disabled: '{readOnly}'
						},
						listeners: {
							change: 'onEvnStickDop_pid', //!!
							validitychange: 'updateInvalidValue'
						},
						value: '',
						inputValue: '',
						xtype: 'baseCombobox',
						valueField: 'EvnStick_id',
						store: new Ext6.data.Store({
							autoLoad: false,
							reader: new Ext6.data.JsonReader({
								id: 'EvnStick_id'
							}, [
								{ name: 'EvnStick_BirthDate', mapping: 'EvnStick_BirthDate' },
								{ name: 'EvnStick_disDate', mapping: 'EvnStick_disDate' },
								{ name: 'EvnStick_id', mapping: 'EvnStick_id' },
								{ name: 'EvnStick_irrDate', mapping: 'EvnStick_irrDate' },
								{ name: 'EvnStick_IsDisability', mapping: 'EvnStick_IsDisability' },
								{ name: 'InvalidGroupType_id', mapping: 'InvalidGroupType_id' },
								{ name: 'EvnStick_IsRegPregnancy', mapping: 'EvnStick_IsRegPregnancy' },
								// { name: 'EvnStick_mid', mapping: 'EvnStick_mid' },
								{ name: 'EvnStick_mseDate', mapping: 'EvnStick_mseDate' },
								{ name: 'EvnStick_mseExamDate', mapping: 'EvnStick_mseExamDate' },
								{ name: 'EvnStick_mseRegDate', mapping: 'EvnStick_mseRegDate' },
								{ name: 'EvnStick_Num', mapping: 'EvnStick_Num' },
								{ name: 'EvnStick_prid', mapping: 'EvnStick_prid' },
								{ name: 'EvnStick_Ser', mapping: 'EvnStick_Ser' },
								{ name: 'EvnStick_setDate', mapping: 'EvnStick_setDate' },
								{ name: 'EvnStick_sstBegDate', mapping: 'EvnStick_sstBegDate' },
								{ name: 'EvnStick_sstEndDate', mapping: 'EvnStick_sstEndDate' },
								{ name: 'EvnStick_sstNum', mapping: 'EvnStick_sstNum' },
								{ name: 'EvnStick_stacBegDate', mapping: 'EvnStick_stacBegDate' },
								{ name: 'EvnStick_stacEndDate', mapping: 'EvnStick_stacEndDate' },
								{ name: 'EvnStick_Title', mapping: 'EvnStick_Title' },
								{ name: 'EvnStickLast_Title', mapping: 'EvnStickLast_Title' },
								{ name: 'PridStickLeaveType_Code', mapping: 'PridStickLeaveType_Code' },
								{ name: 'Lpu_oid', mapping: 'Lpu_oid' },
								{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
								{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
								// { name: 'MedPersonal_mseid', mapping: 'MedPersonal_mseid' },
								{ name: 'Org_id', mapping: 'Org_id' },
								{ name: 'Org_did', mapping: 'Org_did' },
								{ name: 'Person_id', mapping: 'Person_id' },
								{ name: 'Person_Fio', mapping: 'Person_Fio' },
								{ name: 'PersonEvn_id', mapping: 'PersonEvn_id' },
								{ name: 'Server_id', mapping: 'Server_id' },
								{ name: 'StickCause_did', mapping: 'StickCause_did' },
								{ name: 'StickCause_id', mapping: 'StickCause_id' },
								{ name: 'StickCauseDopType_id', mapping: 'StickCauseDopType_id' },
								{ name: 'StickIrregularity_id', mapping: 'StickIrregularity_id' },
								{ name: 'StickLeaveType_id', mapping: 'StickLeaveType_id' },
								{ name: 'StickOrder_id', mapping: 'StickOrder_id' },
								{ name: 'EvnStickBase_consentDT', mapping: 'EvnStickBase_consentDT'}
							]),
							sortInfo: {
								field: 'EvnStick_Title'
							},
							url: '/?c=Stick&m=getEvnStickMainList'
						}),
						//~ tabIndex: TABINDEX_ESTEF + 3,
						tpl: new Ext6.XTemplate(
							'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
							'<td style="padding: 2px; width: 70%;">Серия, номер</td>',
							'<td style="padding: 2px; width: 30%;">Дата выдачи</td></tr>',
							'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
							'<td style="padding: 2px;">{EvnStick_Ser}&nbsp;{EvnStick_Num}</td>',
							'<td style="padding: 2px;">{EvnStick_setDate}&nbsp;</td>',
							'</tr></tpl>',
							'</table>'
						)
					}, {
						xtype: 'textfield',//TODO: ?
						fieldLabel: langs('Адрес регистрации'),
						name: 'UAddress_AddressText',
						hidden: (getRegionNick() != 'kz'),
						hideLabel: (getRegionNick() != 'kz'),
						readOnly: true
					}]
				},
				{//выравнивание fieldset
					xtype: 'fieldset',
					itemId: 'EStEF_OrgFieldset',
					title: langs('Место работы'),
					width: me.conf.fieldset.width,
					defaults: {
						labelWidth: me.conf.fieldset.item.labelWidth,
						width: me.conf.fieldset.item.width,
						padding: '0 0 0 '+me.conf.fieldset.paddingleft
					},
					items: [{
						allowBlank: true,
						xtype: 'swOrgCombo',
						forceSelection: false,
						displayField: 'Org_Name',
						editable: false,
						orgType: 'org',
						//~ enableKeyEvents: true,
						userCls: 'combo-with-ext-trigger',
						fieldLabel: langs('Организация'),
						name: 'Org_id',
						value: '',
						//~ reset_setValue: false,
						bind: {
							disabled: '{readOnly || action=="view" || !(mainPanelAccess || mainPanelSomeFieldsAccess)}',
							allowBlank: '{regionNick=="kz" || !RegistryESStorage_id || !(StickWorkType_id == 1 || StickWorkType_id == 2)}' 
							/* Обязательность поля организация: if (
								getRegionNick() != 'kz' // кроме Казахстана
								&& !Ext.isEmpty(base_form.findField('RegistryESStorage_id').getValue()) // Номер ЛВН получен из хранилища номеров ЭЛН
								&& base_form.findField('StickWorkType_id').getValue()
								&& base_form.findField('StickWorkType_id').getValue().inlist([1, 2]) // Тип занятости выбрано значение: «основная работа» или  «работа по совместительству»
							) {
								base_form.findField('Org_id').setAllowBlank(false);
							} else {
								base_form.findField('Org_id').setAllowBlank(true);
							} */
						},
						listeners: {
							change: 'onOrg_id'
						},
						triggers: {
							picker: {
								hidden: true
							},
							search :{
								
							},
							clear: {
								cls: 'sw-clear-trigger',
								extraCls: 'clear-icon-out', // search-icon-out
								hidden: true
							}
						}
					}, {
						allowBlank: true,
						fieldLabel: langs('Наименование для печати'),
						name: 'EvnStick_OrgNick',
						maxLength: '255',
						bind: {
							disabled: '{readOnly || action=="view" || !(mainPanelAccess || mainPanelSomeFieldsAccess)}'
						},
						//~ tabIndex: TABINDEX_ESTEF + 10,
						userCls: 'combo-with-ext-trigger',
						xtype: 'textfield',
						triggers: {
							assign: {
								cls: 'x6-form-assign-trigger',
								extraCls: 'assign-icon-out',
								handler: 'triggerEvnStick_OrgNick'
							}
						}
					}, {
						allowBlank: true,
						fieldLabel: langs('Должность'),
						name: 'Post_Name',
						//~ tabIndex: TABINDEX_ESTEF + 11,
						//~ width: 500,
						xtype: 'textfield',
						bind: {
							disabled: '{readOnly || !mainPanelAccess}'
						}
					}]
				},
				{
					layout: 'vbox',
					xtype: 'panel',
					margin: '0 0 0 '+me.conf.fieldset.paddingleft,//выравнивание по тексту в fieldset
					width: '100%',
					border: false,

					items: [
					{
						allowBlank: false,
						fieldLabel: langs('Причина нетрудоспособности'),
						name: 'StickCause_id',
						value: '',
						bind: {
							value: '{StickCause_id}',
							disabled: '{readOnly || action=="view" || !mainPanelAccess}'
						},
						listeners: {
							change: 'onStickCause_id'
						},
						//~ tabIndex: TABINDEX_ESTEF + 12,
						comboSubject: 'StickCause',
						xtype: 'commonSprCombo',
						moreFields: [
							{name: 'StickCause_begDate', type: 'date', dateFormat: 'd.m.Y'},
							{name: 'StickCause_endDate', type: 'date', dateFormat: 'd.m.Y'}
						],
						filterFn: function(rec) {
							var dt = new Date();
							return (
								(Ext6.isEmpty(rec.get('StickCause_begDate')) || rec.get('StickCause_begDate') <= dt) &&
								(Ext6.isEmpty(rec.get('StickCause_endDate')) || rec.get('StickCause_endDate') >= dt)
							);
						}
					},
					{
						allowBlank: true,
						comboSubject: 'StickCauseDopType',
						fieldLabel: langs('Доп. код нетрудоспособности'),
						name: 'StickCauseDopType_id',
						value: '',
						//~ tabIndex: TABINDEX_ESTEF + 13,
						xtype: 'commonSprCombo',
						bind: {
							disabled: '{readOnly || !mainPanelSomeMainFieldsKodyNetrudAccess}'
						},
						listeners: {
							change: 'changeStickCauseDopType' //!!
						}
					},
					{
						allowBlank: true,
						allowSysNick: true,
						comboSubject: 'StickCause',
						//~ enableKeyEvents: true,
						fieldLabel: langs('Код изм. нетрудоспособности'),
						name: 'StickCause_did',
						value: '',
						bind: {
							disabled: '{readOnly || !mainPanelSomeMainFieldsKodyNetrudAccess}'
						},
						listeners: {
							change: 'onStickCause_did' //!!
						},
						//~ tabIndex: TABINDEX_ESTEF + 14,
						xtype: 'commonSprCombo',
						moreFields: [
							{name: 'StickCause_begDate', type: 'date', dateFormat: 'd.m.Y'},
							{name: 'StickCause_endDate', type: 'date', dateFormat: 'd.m.Y'}
						],
						/* в ext2 тут не фильтруются старые записи
						filterFn: function(rec) {
							var dt = new Date();
							return (
								(Ext6.isEmpty(rec.get('StickCause_begDate')) || rec.get('StickCause_begDate') <= dt) &&
								(Ext6.isEmpty(rec.get('StickCause_endDate')) || rec.get('StickCause_endDate') >= dt)
							);
						}*/
					},
					{
						allowBlank: true,
						xtype: 'swDateField',
						hidden: (getRegionNick()=='kz'),
						fieldLabel: langs('Дата изменения причины нетрудоспособности'),
						
						labelWidth: me.conf.labelWidth,
						width: me.conf.dateWidth + me.conf.labelWidth,
						name: 'EvnStick_StickDT',
						bind: {
							disabled: (win.getController().isVrach()) ? '{readOnly}' : '{readOnly || regionNick == "kz" || EvnStick_IsPaid || EvnStick_IsInReg || StickFSSData_id}'
						},
						//~ plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
						//~ selectOnFocus: true,
						//~ format: 'd.m.Y',
						//~ tabIndex: TABINDEX_ESTEF + 15,
						//~ enableKeyEvents: true,
					},
					{
						allowBlank: true,
						fieldLabel: langs('Дата усыновления/удочерения'),
						xtype: 'swDateField',
						format: 'd.m.Y',
						name: 'EvnStick_adoptDate',
						selectOnFocus: true,
						//~ tabIndex: TABINDEX_ESTEF + 15,
						bind: {
							disabled: 'readOnly'
						}
						//~ plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						//~ enableKeyEvents: true,
					},
					{
						layout: 'column',
						border: false,
						width: 2000,					
						items: [{
							labelWidth: me.conf.fieldset.item.labelWidth,
							width: me.conf.labelWidth+me.conf.dateWidth,
							allowBlank: true,
							xtype: 'swDateField',
							fieldLabel: langs('Предполагаемая дата родов'),
							name: 'EvnStick_BirthDate',
							selectOnFocus: true,
							bind: {
								disabled: '{readOnly}'
							}
							//~ format: 'd.m.Y',
							//~ tabIndex: TABINDEX_ESTEF + 15,
							//~ enableKeyEvents: true,
							//~ plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						}, {
							name: 'EvnStick_IsRegPregnancy',
							boxLabel: langs('Поставлена на учет в ранние сроки беременности (до 12 недель)'),
							xtype: 'checkbox',
							width: 440,
							padding: '0 0 0 30',
							clearValue: function() {
								this.setValue(false);
							},
							bind: {
								disabled: '{readOnly}'
							}
						}]
					},
					{
						border: false,
						layout: 'column',
						width: 900,
						margin: '0 0 5 0',
						items: [
							{
								allowBlank: true,
								fieldLabel: langs('Дата начала СКЛ'),
								name: 'EvnStick_sstBegDate',
								xtype: 'swDateField',
								labelWidth: me.conf.labelWidth,
								width: me.conf.dateWidth + me.conf.labelWidth,
								bind: {
									disabled: '{readOnly || action=="view" || !(mainPanelAccess || mainPanelSSTFieldsAccess)}'
								},
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();
									}.createDelegate(this)
								},
								//~ format: 'd.m.Y',
								//~ plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
								//~ selectOnFocus: true 
								//~ tabIndex: TABINDEX_ESTEF + 16															
							},
							{
								allowBlank: true,
								fieldLabel: langs('Дата окончания СКЛ'),
								xtype: 'swDateField',
								labelAlign: 'right',
								labelWidth: me.conf.labelWidth+30,
								width: me.conf.dateWidth + me.conf.labelWidth+30,
								name: 'EvnStick_sstEndDate',
								bind: {
									allowBlank: '{ !(region != "kz" && EvnStick_sstNum) }',
									disabled: '{readOnly || action=="view" || !(mainPanelAccess || mainPanelSSTFieldsAccess)}'
								},
								//~ plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
								//~ format: 'd.m.Y',
								//~ selectOnFocus: true,
								//~ tabIndex: TABINDEX_ESTEF + 17,
							}
						]
					},
					{
						allowBlank: true,
						fieldLabel: langs('Номер путевки'),
						name: 'EvnStick_sstNum',
						bind: {
							value: '{EvnStick_sstNum}',
							disabled: '{readOnly || action=="view" || !(mainPanelAccess || mainPanelSSTFieldsAccess)}'
						},
						listeners: {
							blur: 'onEvnStick_sstNum'
						},
						//~ tabIndex: TABINDEX_ESTEF + 18,
						width: me.conf.labelWidth + me.conf.dateWidth,
						xtype: 'textfield'
					},
					{
						xtype: 'swOrgCombo',
						displayField: 'Org_Name',
						valueField: 'Org_id',
						value: '',
						editable: false,
						orgType: 'org',
						fieldLabel: langs('Санаторий'),
						name: 'Org_did',
						//~ reset_setValue: false,
						bind: {
							disabled: '{readOnly || action=="view" || !(mainPanelAccess || mainPanelSSTFieldsAccess)}'
						},
						triggers: {
							picker: {
								hidden: true
							}
						},
						listeners: {
							change: 'onOrg_did'
						}
						//~ ,enableKeyEvents: true,
					}

					]
				}
			]
		});

		this.FormPanel = new Ext6.form.FormPanel({
			layout: 'vbox',
			//width: '100%',
			//scrollable: true,
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				success: Ext6.emptyFn,
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'accessType' },
						{ name: 'addWorkReleaseAccessType' },
						{ name: 'EvnStick_BirthDate' },
						{ name: 'EvnStick_disDate' },
						{ name: 'EvnStick_id' },
						{ name: 'Person_rid' },
						{ name: 'Person_Snils' },
						//~ { name: 'PersonSnils' },
						{ name: 'EvnStick_irrDate' },
						{ name: 'EvnStick_IsDisability' },
						{ name: 'InvalidGroupType_id' },
						{ name: 'EvnStick_StickDT' },
						{ name: 'EvnStick_IsRegPregnancy' },
						{ name: 'EvnStick_mseDate' },
						{ name: 'EvnStick_mseExamDate' },
						{ name: 'EvnStick_mseRegDate' },
						{ name: 'EvnStick_Num' },
						{ name: 'EvnStick_pid' },
						{ name: 'EvnStick_nid' },
						{ name: 'EvnStick_oid' },
						{ name: 'EvnStick_IsOriginal' },
						{ name: 'EvnStick_prid' },

						//Исход из ЛВН по основному месту работы
						{ name: 'PridStickLeaveType_Code1' },

						//Исход из предыдущего ЛВН
						{ name: 'PridStickLeaveType_Code2' },

						{ name: 'PridEvnStickWorkRelease_endDate' },
						{ name: 'EvnStick_Ser' },
						{ name: 'EvnStick_setDate' },
						{ name: 'EvnStick_sstBegDate' },
						{ name: 'EvnStick_sstEndDate' },
						{ name: 'EvnStick_sstNum' },
						{ name: 'EvnStick_stacBegDate' },
						{ name: 'EvnStick_stacEndDate' },
						{ name: 'EvnStickDop_pid' },
						{ name: 'EvnStickFullNameText' },
						{ name: 'EvnStickLast_id' },
						{ name: 'Lpu_oid' },
						{ name: 'Lpu_id' },
						{ name: 'MedStaffFact_id' },
						{ name: 'MedPersonal_id' },
						{ name: 'Org_did' },
						{ name: 'Org_id' },
						{ name: 'EvnStick_OrgNick' },
						{ name: 'Person_id' },
						{ name: 'PersonEvn_id' },
						{ name: 'pmUser_insID' },
						{ name: 'Post_id'},
						{ name: 'Post_Name' },
						{ name: 'Server_id' },
						{ name: 'StickCause_did' },
						{ name: 'StickCause_id' },
						{ name: 'StickCauseDopType_id' },
						{ name: 'StickIrregularity_id' },
						{ name: 'StickLeaveType_id' },
						{ name: 'StickOrder_id' },
						{ name: 'StickWorkType_id' },
						{ name: 'CountDubles' },
						{ name: 'MaxDaysLimitAfterStac' },
						{ name: 'EvnSection_setDate' },
						{ name: 'EvnSection_disDate' },
						{ name: 'WorkReleaseSumm', type: 'int' },
						{ name: 'EvnStickNext_id', type: 'int' },
						{ name: 'RegistryESStorage_id', type: 'int' },
						{ name: 'EvnStick_adoptDate' },
						{ name: 'EvnStick_regBegDate' },
						{ name: 'EvnStick_regEndDate' },
						{ name: 'EvnStick_IsPaid' },
						{ name: 'EvnStick_IsInReg' },
						{ name: 'EvnStick_IsDateInReg' },
						{ name: 'EvnStick_IsDateInFSS' },
						{ name: 'StickFSSData_id' },
						{ name: 'EvnStickBase_IsFSS' },
						{ name: 'EvnStick_NumNext' },
						{ name: 'isTubDiag' },
						{ name: 'EvnStickBase_consentDT' },
						{ name: 'EvnStick_NumPar' }
					]
				})
			}),
			region: 'center',
			url: '/?c=Stick&m=saveEvnStick',
			border: false,
			items: [
				// -----------------------------------------------------------------------------------------------------
				// HIDDEN FIELDS
				// -----------------------------------------------------------------------------------------------------
				{
					name: 'Lpu_id',
					value: '',
					xtype: 'hidden'
				},
				{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				},

				{
					name: 'addWorkReleaseAccessType',
					bind: '{addWorkReleaseAccessType}',
					xtype: 'hidden'
				},

				//ссылка на следующий ЛВН
				{
					name: 'EvnStick_nid',
					xtype: 'hidden'
				},
				// Идентификатор получателя ЛВН
				{
					name: 'Person_rid',
					xtype: 'hidden'
				},
				// Идентификатор основного ЛВН
				{
					name: 'EvnStick_id',
					value: 0,
					xtype: 'hidden'
				},
				//Идентификатор ЛВН с которого дублируем
				{
					name: 'EvnStickCopy_id',
					value: null,
					xtype: 'hidden'
				},
				// Идентификатор продолжения ЛВН
				{
					name: 'EvnStickNext_id',
					value: null,
					xtype: 'hidden'
				},

				// Текущий номер ЭЛН в хранилище
				{
					name: 'RegistryESStorage_id',
					bind: '{RegistryESStorage_id}',
					xtype: 'hidden'
				},

				// Признак «Дата направления в бюро МСЭ в реестре»
				{
					name: 'EvnStick_IsDateInReg',
					xtype: 'hidden'
				},

				// Признак «Дата направления в бюро МСЭ принята в ФСС»
				{
					name: 'EvnStick_IsDateInFSS',
					xtype: 'hidden'
				},

				// Количество дублей ЛВН
				{
					name: 'CountDubles',
					value: 0,
					xtype: 'hidden'
				},

				// Ограничивать ЛВН 10-ью днями, т.к. предыдущий ЛВН закрыт в стаце с прииной долечивание
				{
					name: 'MaxDaysLimitAfterStac',
					value: 1,
					xtype: 'hidden'
				},

				// Идентификатор учетного документа (ТАП, КВС)
				{
					name: 'EvnStick_mid',
					value: null,
					xtype: 'hidden'
				},

				// Идентификатор родительского события
				{
					name: 'EvnStick_pid',
					value: null,
					xtype: 'hidden'
				},

				// Идентификатор первичного ЛВН
				{
					name: 'EvnStick_prid',
					value: 0,
					xtype: 'hidden'
				},

				// Код исхода из ЛВН по основному месту работы
				{
					name: 'PridStickLeaveType_Code1',
					value: 0,
					xtype: 'hidden'
				},

				// Код исхода из предыдущего ЛВН
				{
					name: 'PridStickLeaveType_Code2',
					value: 0,
					xtype: 'hidden'
				},

				// Сумма периодов освобождений для цепочки ЛВН первичный -> продолжение -> ...
				{
					name: 'WorkReleaseSumm',
					value: 0,
					xtype: 'hidden'
				},

				{
					name: 'MedPersonal_id',
					xtype: 'hidden'
				},
				{
					name: 'pmUser_insID',
					xtype: 'hidden'
				},
				{
					name: 'Person_id',
					value: -1,
					xtype: 'hidden',
					bind: {
						value: '{Person_id}'
					}
				},
				{
					name: 'PersonEvn_id',
					value: -1,
					xtype: 'hidden'
				},
				{
					name: 'Server_id',
					value: -1,
					xtype: 'hidden'
				},
				{
					name: 'EvnSection_setDate',
					value: null,
					xtype: 'hidden'
				},
				{
					name: 'EvnSection_disDate',
					value: null,
					xtype: 'hidden'
				},
				{
					name: 'PridEvnStickWorkRelease_endDate',
					value: null,
					xtype: 'hidden'
				},
				{
					name: 'EvnStick_IsPaid',
					value: null,
					xtype: 'hidden',
					bind: '{EvnStick_IsPaid}',
					listeners: {
						change: function(field) {
							win.getViewModel().set('isPaid', field.getValue()==2);
						}
					}
				},
				{
					name: 'EvnStick_IsInReg',
					value: null,
					xtype: 'hidden',
					bind: '{EvnStick_IsInReg}',
					listeners: {
						change: function(field) {
							win.getViewModel().set('isInReg', field.getValue()==2);
						}
					}
				},
				// Общие данные ЭЛН для отправки в ФСС
				{
					name: 'StickFSSData_id',
					bind: '{StickFSSData_id}',
					value: null,
					xtype: 'hidden'
				},

				{
					xtype: 'hidden',
					name: 'UAddress_Zip'
				},
				{
					xtype: 'hidden',
					name: 'UKLCountry_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLRGN_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLRGNSocr_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLSubRGN_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLSubRGNSocr_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLCity_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLCitySocr_id'
				},
				{
					xtype: 'hidden',
					name: 'UPersonSprTerrDop_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLTown_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLTownSocr_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLStreet_id'
				},
				{
					xtype: 'hidden',
					name: 'UKLStreetSocr_id'
				},
				{
					xtype: 'hidden',
					name: 'UAddress_House'
				},
				{
					xtype: 'hidden',
					name: 'UAddress_Corpus'
				},
				{
					xtype: 'hidden',
					name: 'UAddress_Flat'
				},
				{
					xtype: 'hidden',
					name: 'UAddressSpecObject_id'
				},
				{
					xtype: 'hidden',
					name: 'UAddressSpecObject_Value'
				},
				{
					xtype: 'hidden',
					name: 'UAddress_Address'
				},
				{
					xtype: 'hidden',
					name: 'EvnStick_NumPar' //отображение ЛВН по совместительству, полученные из ФСС
				},
				// -----------------------------------------------------------------------------------------------------
				win.PrimaryPanel,
				{
					layout: {
						type: 'accordion',
						titleCollapse: false,
						animate: true,
						multi: true,
						activeOnTop: false,
						border: true
					},
					listeners: {
						'resize': function() {
							this.updateLayout();
						}
					},
					itemId: 'accord',
					defaults: {
						margin: "0px 0px 2px 0px",
						bodyPadding: 20,
					//	width: '100%',
						border: false
					},
					//scrollable: true,
					width: '100%',
					border: false,
					items: [

						// 1. Список пациентов, нуждающихся в уходе
						me.panelEvnStickCarePerson,


						// 2. Режим
						me.panelStickRegime,


						// 3. МСЭ
						me.panelMSE,


						// 4. Освобождение от работы
						me.panelEvnStickWorkRelease,


						// 5. Исход ЛВН
						me.panelStickLeave
					]
				}, {
					layout: 'column',
					userCls: 'buttonFooterGroup',
					border: false,
					margin: '0 10 10 30',
					items: [
						{
							text: 'Сохранить',
							xtype: 'button',	//xtype: 'SubmitButton',
							userCls: 'button-primary', //userCls: 'buttonAccept buttonPoupup',
							itemId: 'button_save',
							//style: 'margin-left: 30px;',
							margin: 5,
							handler: function() {
								me.getController().doSave();
							}
						}, {
							text: 'Отмена',
							xtype: 'button', //xtype: 'SimpleButton',
							itemId: 'button_cancel', //xtype: 'button',
							userCls: 'button-secondary', //userCls: 'buttonCanсel buttonPoupup',
							// style: 'margin-left: 10px;',
							margin: 5,
							handler: function() {
								me.hide();
							}
						}
					]
				}
			]
		});

		me.PersonInfo = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			border: false,
			userMedStaffFact: me.userMedStaffFact,
			addToolbar: false,
			ownerWin: me
		});
		
		me.TitleToolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			height: 40,
			border: false,
			noWrap: true,
			right: 0,
			style: 'background: transparent;',
			items: [{
				xtype: 'tbspacer',
				width: 10
			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-create-duplicate',
				tooltip: langs('Создать дубликат'),
				handler: function() {
					me.getController().doCopy();
				}
			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-print',
				itemId: 'buttonMenuPrint',
				tooltip: langs('Печать'),
				menu: Ext6.create('Ext6.menu.Menu', {
					itemId: 'printmenu',
					plain: true,
					showBy: function(cmp, pos, off) {
						var me = this;
						me._lastAlignTarget = win.queryById('buttonMenuPrint');
						me._lastAlignToPos = 'tr-b';
						//~ me._lastAlignToOffsets = me.alignOffset; //для более точного позиционирования
						me.show();
						return me;
					},
					defaults: {
						padding: '0px 20px 0px 20px'
					},
					items: [{
						userCls: 'dispcard',
						text: getRegionNick() != 'kz' ? langs('Печать ЛВН') : langs('Печать'),
						itemId: 'buttonPrint',
						handler: function() {
							win.getController().doBeforePrintEvnStick();
						}
					}, {
						userCls: 'dispcard',
						text: 'Печать усеченного талона ЭЛН',
						disabled: true,
						hidden: getRegionNick() == 'kz',
						itemId: 'buttonPrintTruncELN',
						handler: function() {
							win.getController().doPrintTruncELN();
						}
					}]
				})

			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-del-prescr-item',
				itemId: 'buttonDelete',
				tooltip: langs('Удалить'),
				handler: function() {
					me.getController().deleteEvnStick();
				}
			}]
		});

		me.titlePanel = Ext6.create('Ext6.Panel', {
			userCls: '',
			region: 'north',
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #EEEEEE;',
			items: [{
				region: 'center',
				border: false,
				bodyStyle: 'background-color: #EEEEEE;',
				height: 40,
				bodyPadding: '10 10 10 26',
				items: [
					Ext6.create('Ext6.form.Label', {
						xtype: 'label',
						cls: 'no-wrap-ellipsis',
						style: 'font-size: 16px; padding: 3px 10px;',
						html: 'Лист временной нетрудоспособности'
					})
				]
			}, me.TitleToolPanel
			],
			xtype: 'panel'
		});

		Ext6.apply(me, { //TAG: Ext6.apply
			items: [
				{
					region: 'north',
					border: false,
					items: [
						win.PersonInfo,
						win.titlePanel
					]
				}, {
					region: 'center',
					border: false,
					scrollable: 'y',
					items: [
						me.FormPanel
					]
				}
			]
		});

		me.callParent(arguments);

		//~ me.queryById('EStEF_MedStaffFactCombo').addListener('change', function(combo, newValue, oldValue) {
			//~ this.checkSaveButtonEnabled();
		//~ }.createDelegate(this));
	},

	
	// Список версий документа (в меню блока 2)
	openEvnStickSignHistoryWindow: function(options) {
		var me = this;
		var form = this;
		var params = {};
		var base_form = this.FormPanel.getForm();
		var grid = this.queryById('EStEF_EvnStickWorkReleaseGrid');
		var selected_record = grid.getSelectedRecord();
		var SignObject = options.SignObject;
		params.SignObject = options.SignObject;
		if (!selected_record && !SignObject.inlist(['leave', 'irr'])) return false;
		switch(SignObject) {
			case 'MP':
				params.Signatures_id = selected_record.get('Signatures_mid');
				break;
			case 'VK':
				params.Signatures_id = selected_record.get('Signatures_wid');
				break;
			case 'leave':
				params.Signatures_id = form.Signatures_id;
				break;
			case 'irr':
				params.Signatures_id = form.Signatures_iid;
				break;
		}
		getWnd('swStickVersionListWindow').show(params);
	}
});