sw.Promed.swCVIRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Регистр КВИ'),
	id: 'swCVIRegistryWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	modal: false,
	plain: true,
	height: 550,
	minHeight: 550,
	minWidth: 800,
	width: 800,
	buttons: [
		{
			text: BTN_FRMSEARCH,
			handler: function() {
				Ext.getCmp('swCVIRegistryWindow').doSearch();
			},
			iconCls: 'search16'
		}, {
			text: lang['sbros'],
			handler: function() {
				Ext.getCmp('swCVIRegistryWindow').doSearch(true);
			},
			iconCls: 'resetsearch16'
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],

	initComponent: function () {
		var scope = this;

		this.LpuRegion_id = new Ext.form.TextField({
			name: 'LpuRegion_id',
			hidden: true,
			isConstant: true
		});
		this.MainFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'margin-top: 5px;',
			height: 140,
			labelAlign: 'right',
			labelWidth: 180,
			layout: 'column',
			region: 'north',
			frame: true,
			border: false,
			items: [
				{
					layout: 'form',
					cmpType: 'form',
					border: false,
					items: [
						scope.LpuRegion_id,
						{
							xtype: 'textfield',
							name: 'Person_SurName',
							fieldLabel: 'Фамилия',
							width: 170
						},
						{
							xtype: 'textfield',
							name: 'Person_FirName',
							fieldLabel: 'Имя',
							width: 170
						},
						{
							xtype: 'textfield',
							name: 'Person_SecName',
							fieldLabel: 'Отчество',
							width: 170
						},
						{
							fieldLabel: 'Дата включения в регистр',
							name: 'PersonRegister_setDateRange',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							width: 170,
							xtype: 'daterangefield'
						},
						{
							fieldLabel: 'Дата начала случая',
							name: 'CVIRegistry_setDTRange',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							width: 170,
							xtype: 'daterangefield'
						},
						{
							fieldLabel: 'Дата окончания случая',
							name: 'CVIRegistry_disDTRange',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							width: 170,
							xtype: 'daterangefield'
						}
					]
				},
				{
					layout: 'form',
					cmpType: 'form',
					border: false,
					labelWidth: 125,
					items: [
						{
							hiddenName: 'RegistryRecordType_id',
							fieldLabel: 'Тип записи регистра',
							xtype: 'swstaticcombo',
							width: 500,
							value: 0,
							comboData: [
								[ 0, 'Все' ],
								[ 1, 'Включенные в регистр' ],
								[ 2, 'Исключенные из регистра' ],
								[ 3, 'Удаленные из регистра' ]
							]
						},
						{
							xtype: 'swlpucombo',
							hiddenName: 'Lpu_id',
							fieldLabel: lang['mo'],
							width: 500,
							listeners: {
								render: function () {
									this.store.load();
								}
							}
						},
						new Ext.ux.Andrie.Select({
							multiSelect: true,
							mode: 'local',
							allowBlank: true,
							emptyText: 'Не выбран',
							fieldLabel: 'Основной диагноз',
							hiddenName: 'Diag_id',
							displayField: 'Diag_Code',
							valueField: 'Diag_id',
							xtype:'swbaselocalcombo',
							width: 500,
							store: new Ext.data.Store({
								url: '/?c=CVIRegistry&m=loadMainDiagCombo',
								autoLoad: true,
								reader: new Ext.data.JsonReader({
									id: 'Diag_id'
								}, [
									{ mapping: 'Diag_id', name: 'Diag_id', type: 'int' },
									{ mapping: 'Diag_Code', name: 'Diag_Code', type: 'string' },
									{ mapping: 'Diag_Name', name: 'Diag_Name', type: 'string' }
								])
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{[ values.Diag_Code + " - " + values.Diag_Name ]}&nbsp;',
								'</div></tpl>'
							),
							initEvents:function(){
								Ext.form.ComboBox.superclass.initEvents.call(this);

								this.keyNav = new Ext.KeyNav(this.el, {

									"enter" : function(e) {
										if (this.isExpanded()){
											this.inKeyMode = true;
											if (!this.view.lastItem) return false;
											var hoveredIndex = this.view.indexOf(this.view.lastItem);
											this.onViewBeforeClick(this.view, hoveredIndex, this.view.getNode(hoveredIndex), e);
											this.onViewClick(this.view, hoveredIndex, this.view.getNode(hoveredIndex), e);
										} else {
											this.onSingleBlur();
										}
										return true;
									},

									"esc" : function(e) {
										this.collapse();
									},

									"tab" : function(e) {
										this.collapse();
										return true;
									},

									"home" : function(e) {
										this.hoverFirst();
										return false;
									},

									"end" : function(e) {
										this.hoverLast();
										return false;
									},

									scope : this,

									doRelay : function(foo, bar, hname){
										if(hname == 'down' || this.scope.isExpanded()){
											return Ext.KeyNav.prototype.doRelay.apply(this, arguments);
										}
										if(hname == 'enter' || this.scope.isExpanded()){
											return Ext.KeyNav.prototype.doRelay.apply(this, arguments);
										}
										return true;
									},

									forceKeyDown: true
								});
							},
						}),
						{
							xtype: 'swdiagcombo',
							hiddenName: 'Diag_Code_From',
							valueField: 'Diag_Code',
							fieldLabel: 'Соп. диагноз с',
							width: 500
						},
						{
							xtype: 'swdiagcombo',
							hiddenName: 'Diag_Code_To',
							valueField: 'Diag_Code',
							fieldLabel: 'по',
							width: 500
						}
					]
				},
				{
					itemId: 'dynamicForm',
					layout: 'form',
					cmpType: 'form',
					border: false,
					labelWidth: 165,
					items: [
						{
							hiddenName: 'Status_id',
							fieldLabel: 'Статус записи',
							xtype: 'swstaticcombo',
							value: 1,
							ownerTabs: ['suspicion', 'confirmed', 'notconfirmed'],
							comboData: [
								[ 0, 'Все' ],
								[ 1, 'Все открытые случаи' ],
								[ 2, 'Все закрытые случаи' ]
							]
						},
						{
							hiddenName: 'ControlCard_Type',
							fieldLabel: 'Есть контрольная карта',
							xtype: 'swstaticcombo',
							ownerTabs: ['suspicion', 'notconfirmed'],
							comboData: [
								[0, 'Все записи'],
								[1, 'Открытая'],
								[2, 'Закрытая'],
								[3, 'Любая'],
								[4, 'Отсутствует']
							]
						},
						{
							hiddenName: 'TreatmentPlace',
							fieldLabel: 'Место лечения',
							xtype: 'swstaticcombo',
							value: 0,
							ownerTabs: ['confirmed'],
							comboData: [
								[0, 'Не выбрано'],
								[1, 'В стационаре'],
								[2, 'На дому']
							]
						},
						{
							fieldLabel: 'Контр. карта открыта на',
							name: 'ControlCard_OpenDateRange',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							width: 200,
							ownerTabs: ['suspicion', 'notconfirmed'],
							xtype: 'daterangefield'
						},
						{
							hiddenName: 'RegistryIncludeOnMSS',
							fieldLabel: 'Включение в Регистр на основе МСС',
							xtype: 'swstaticcombo',
							value: 0,
							ownerTabs: ['died'],
							comboData: [
								[0, 'Все'],
								[1, 'Нет'],
								[2, 'Да']
							]
						},
						{
							hiddenName: 'ResultClass_id',
							fieldLabel: 'Результат лечения',
							width: 200,
							xtype: 'swresultclasscombo',
							ownerTabs: ['confirmed', 'recovered', 'notconfirmed', 'suspicion']
						},
						{
							hiddenName: 'LeaveType_id',
							fieldLabel: 'Исход госпитализации',
							width: 200,
							xtype: 'swleavetypecombo',
							ownerTabs: ['confirmed', 'recovered']
						}
					]
				}
			],
			keys: [{
				fn: function(inp, e) {
					scope.doSearch();
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}]
		});

		var contactegGridConf = {
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			uniqueId: true,
			useEmptyRecord: false,
			forcePrintMenu: true,
			border: false,
			dataUrl: '/?c=CVIRegistry&m=loadContactedGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			layout: 'fit',
			totalProperty: 'totalCount',
			stringfields: [
				{ name: 'Person_id', type: 'int', hidden: true, key: true },
				{ name: 'CVIRegistry_id', type: 'int', hidden: true },
				{ name: 'PersonQuarantine_id', type: 'int', hidden: true },

				{ name: 'PersonContactCVI_contactDate', type: 'string', header: 'Дата контакта с больным КВИ', width: 90 },
				{ name: 'Person_SurName', type: 'string', header: lang['familiya'], width: 150 },
				{ name: 'Person_FirName', type: 'string', header: lang['imya'], width: 150 },
				{ name: 'Person_SecName', type: 'string', header: lang['otchestvo'], width: 150 },
				{ name: 'Person_BirthDay', type: 'string', header: 'Дата рождения', width: 90 },
				{ name: 'Person_Age', type: 'string', header: 'Возраст', width: 50 },

				{ name: 'Person_Phone', type: 'string', header: 'Телефон', width: 90 },
				{ name: 'RegAddres', type: 'string', header: 'Адрес регистрации', width: 90 },
				{ name: 'LiveAddress', type: 'string', header: 'Адрес проживания', width: 150 },
				{ name: 'PersonQuarantine_begDT', type: 'string', header: 'Дата открытия контрольной карты', width: 90 },
				{ name: 'PersonQuarantine_approveDT', type: 'string', header: 'Дата выявления заболевания', width: 90 },
				{ name: 'Lpu_Nick', type: 'string', header: 'МО госпитализации', width: 90 }
			],
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit',  hidden: true },
				{ name: 'action_view',  hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', handler: function () { scope.doSearch(); } },
				{ name: 'action_print',
					menuConfig: {
						printObject: { name: 'printObject', text: langs('Печать'), handler: function() { scope.GridList[scope.TabPanel.getActiveTab().itemId].printObject()} },
						printObjectList: { name: 'printObjectList', text: langs('Печать текущей страницы'), handler: function() { scope.GridList[scope.TabPanel.getActiveTab().itemId].printObjectList()} },
						printObjectListFull: { name: 'printObjectListFull', text: langs('Печать всего списка'), handler: function() { scope.GridList[scope.TabPanel.getActiveTab().itemId].printObjectListFull()} },
					}
				}
			]
		};
		var registerGridConf = {
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			uniqueId: true,
			useEmptyRecord: false,
			forcePrintMenu: true,
			border: false,
			dataUrl: '/?c=CVIRegistry&m=loadGrid',
			pageSize: 100,
			paging: false,
			region: 'center',
			layout: 'fit',
			root: 'data',
			totalProperty: 'totalCount',
			showCountInTop: true,
			stringfields: [
				{ name: 'id', type: 'int', hidden: true, key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'CVIRegistry_id', type: 'int', hidden: true },
				{ name: 'PersonQuarantine_id', type: 'int', hidden: true },
				{ name: 'EvnDirection_id', type: 'int', hidden: true },
				{ name: 'EvnPL_id', type: 'int', hidden: true },
				{ name: 'EvnPS_id', type: 'int', hidden: true },
				{ name: 'RecordType', type: 'string', hidden: true },
				{ name: 'IsRed', type: 'int', hidden: true },

				{ name: 'Person_SurName', type: 'string', header: lang['familiya'], width: 150 },
				{ name: 'Person_FirName', type: 'string', header: lang['imya'], width: 150 },
				{ name: 'Person_SecName', type: 'string', header: lang['otchestvo'], width: 150 },
				{ name: 'Person_Age', type: 'string', header: 'Возраст', width: 60 },
				{ name: 'Person_BirthDay', type: 'string', header: 'Дата рождения', width: 110 },

				{ name: 'begDT', type: 'string', header: 'Дата начала случая', width: 110 },
				{ name: 'endDT', type: 'string', header: 'Дата окончания случая', width: 110 },
				{ name: 'RecType', type: 'string', header: 'Учетный документ', width: 120,
					renderer: function (value, cellEl, rec) {
						var type = '';
						switch (value) {
							case 'pl': type = 'ТАП'; break;
							case 'ps': type = 'КВС'; break;
							case 'pq': type = 'КК'; break;
							case 'd': type = 'МСС'; break;
						}
						return type;
					}
				},
				{ name: 'Diag_Code', type: 'string', header: 'Диагноз', width: 70 },
				{ name: 'SopDiag', type: 'string', header: 'Сопутствующие/Осложнения', width: 150 },
				{ name: 'TreatmentPlace', type: 'string', header: 'Место лечения', width: 90 },
				{ name: 'Heft', type: 'string', header: 'Тяжесть', width: 90 },
				{ name: 'Nutrition', type: 'string', header: 'Питание', width: 90 },
				{ name: 'Ventilation', type: 'string', header: 'ИВЛ', width: 90 },
				{ name: 'UslugaTest_ResultValue', type: 'string', header: 'Последний результат исследования на COVID-19', width: 120,
					renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnDirection_id'))) { return value; }
						var link = "<a href='#' onClick='getWnd(\"swResearchViewWindow\").show({EvnDirection_id: " + rec.get('EvnDirection_id') + " });'>" + value + "</a>";
						if (value == 'Обнаружено') {
							link = "<b>" + link + "</b>";
						}
						return link;
					}
				},
				{ name: 'Result', type: 'string', header: 'Исход', width: 120 },
				{ name: 'setDT', type: 'string', header: 'Дата включения в Регистр', width: 120 },
				{ name: 'disDT', type: 'string', header: 'Дата исключения из Регистра', width: 120 }
			],
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit',  hidden: true },
				{ name: 'action_view',  hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', handler: function () { scope.doSearch(); } },
				{ name: 'action_print',
					menuConfig: {
						printObject: { name: 'printObject', text: langs('Печать'), handler: function() { scope.GridList[scope.TabPanel.getActiveTab().itemId].printObject()} },
						printObjectList: { name: 'printObjectList', text: langs('Печать текущей страницы'), handler: function() { scope.GridList[scope.TabPanel.getActiveTab().itemId].printObjectList()} },
						printObjectListFull: { name: 'printObjectListFull', text: langs('Печать всего списка'), handler: function() { scope.GridList[scope.TabPanel.getActiveTab().itemId].printObjectListFull()} },
					}
				}
			]
		};

		this.SuspicionGrid = new sw.Promed.ViewFrame(registerGridConf);
		this.ConfirmedGrid = new sw.Promed.ViewFrame(registerGridConf);
		this.RecoveredGrid = new sw.Promed.ViewFrame(registerGridConf);
		this.DiedGrid = new sw.Promed.ViewFrame(registerGridConf);
		this.NotconfirmedGrid = new sw.Promed.ViewFrame(registerGridConf);
		this.ContactedGrid = new sw.Promed.ViewFrame(contactegGridConf);

		this.GridList = {
			suspicion: this.SuspicionGrid,
			confirmed: this.ConfirmedGrid,
			recovered: this.RecoveredGrid,
			died: this.DiedGrid,
			notconfirmed: this.NotconfirmedGrid,
			contacted: this.ContactedGrid
		};

		for (var key in this.GridList) {
			this.GridList[key].getGrid().view = new Ext.grid.GridView({
				getRowClass: function (row) {
					var cls = '';
					if (row.get('IsRed') == 2) cls = cls + 'x-grid-rowred ';
					if (cls.length == 0) cls = 'x-grid-panel';
					return cls;
				}
			});
		}

		this.TabPanel = new Ext.TabPanel({
			activeTab: 0,
			layoutOnTabChange: true,
			border: false,
			region: 'center',

			hideMode: 'offsets',
			deferredRender: false,
			items: [
				{
					itemId: 'suspicion',
					autoHeight: true,
					autoScroll: true,
					border: false,
					layout: 'form',
					title: 'Подозрение на КВИ',
					items: [ this.SuspicionGrid ]
				},
				{
					itemId: 'confirmed',
					autoHeight: true,
					autoScroll: true,
					border: false,
					layout: 'form',
					title: 'С подтвержденным заболеванием КВИ',
					items: [ this.ConfirmedGrid ]
				},
				{
					itemId: 'recovered',
					autoHeight: true,
					autoScroll: true,
					border: false,
					layout: 'form',
					title: 'Выздоровевшие',
					items: [ this.RecoveredGrid ]
				},
				{
					itemId: 'died',
					autoHeight: true,
					autoScroll: true,
					border: false,
					layout: 'form',
					title: 'Умершие',
					items: [ this.DiedGrid ]
				},
				{
					itemId: 'notconfirmed',
					autoHeight: true,
					autoScroll: true,
					border: false,
					layout: 'form',
					title: 'Подозрение на КВИ не подтвердилось',
					items: [ this.NotconfirmedGrid ]
				},
				{
					itemId: 'contacted',
					autoHeight: true,
					autoScroll: true,
					border: false,
					layout: 'form',
					title: 'Контактные с больным КВИ без прикрепления',
					items: [ this.ContactedGrid ]
				}
			],
			listeners: {
				beforetabchange: function (panel, newTab, curTab) {
					if (!curTab) return;
					scope.resetFilters();
					scope.setFieldsVisible(newTab.itemId);
					scope.disableFilters(newTab.itemId == 'contacted');

					//scope.GridList[curTab.itemId].removeAll();
				},
				tabchange: function (panel, tab) {
					scope.GridList[tab.itemId].setHeight(panel.getInnerHeight());
					if (!scope.GridList[tab.itemId].getAction('open_emk')) {
						scope.GridList[tab.itemId].addActions({
							name: 'open_emk',
							text: 'Открыть ЭМК',
							tooltip: 'Открыть электронную медицинскую карту пациента',
							iconCls: 'open16',
							handler: function () { scope.emkOpen(); }
						});
					}
					if (!scope.GridList[tab.itemId].getAction('open_cc')) {
						scope.GridList[tab.itemId].addActions({
							name: 'open_cc',
							text: 'Открыть контр. карту',
							tooltip: 'Открыть контрольную карту пациента',
							iconCls: 'open16',
							handler: function () { scope.ccOpen(); }
						});
					}
				}
			}
		});

		Ext.apply(this, {
			items: [
				this.MainFiltersPanel, this.TabPanel
			]
		});

		sw.Promed.swCVIRegistryWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function () {
		sw.Promed.swCVIRegistryWindow.superclass.show.apply(this, arguments);

		this.LpuRegion_id.hideContainer();
		if (arguments && arguments[0]) {
			if (arguments[0].LpuRegion_id) this.LpuRegion_id.setValue(arguments[0].LpuRegion_id);
			this.armType = Ext.isEmpty(arguments[0].armType) ? null : arguments[0].armType;
			if (arguments[0].userMedStaffFact) this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		this.setFieldsVisible('suspicion');

		this.center();
		this.maximize();
		this.doLayout();

		this.GridList[this.TabPanel.getActiveTab().itemId].setHeight(this.TabPanel.getInnerHeight());
		this.GridList[this.TabPanel.getActiveTab().itemId].removeAll();
		this.resetFilters();

		// Столбцы убираются вручную чтобы повторно не описывать грид
		this.SuspicionGrid.getColumnModel().setHidden(20, true); // Тяжесть
		this.SuspicionGrid.getColumnModel().setHidden(21, true); // Питание
		this.SuspicionGrid.getColumnModel().setHidden(22, true); // ИВЛ

		this.DiedGrid.getColumnModel().setHidden(19, true); // Место лечения
		this.DiedGrid.getColumnModel().setHidden(20, true); // Тяжесть
		this.DiedGrid.getColumnModel().setHidden(21, true); // Питание
		this.DiedGrid.getColumnModel().setHidden(22, true); // ИВЛ

		this.TabPanel.setActiveTab(0);

		var scope = this;
		this.showLoadMask(langs('Идет обработка открытых случаев. Пожалуйста, подождите...'));
		this.processOpenRecords()
			.catch((err) => { Ext.Msg.alert(lang['oshibka'], err.message); })
			.finally(() => { scope.hideLoadMask(); });
	},
	doSearch: function(clear) {
		if (clear) { this.resetFilters(); }

		var params = this.getFormParams();
		params.mode = this.TabPanel.getActiveTab().itemId;
		params.start = 0;
		params.limit = 100;
		if (this.armType == 'mp' && !getGlobalOptions().superadmin && Ext.isEmpty(params.LpuRegion_id)) {
			params.returnEmpty = '2';
		} else params.returnEmpty = '1';
		if (getGlobalOptions().superadmin) {
			params.LpuRegion_id = null;
		}
		if (this.armType == 'mp' && !getGlobalOptions().superadmin) {
			params.pmuser_id = getGlobalOptions().pmuser_id;
		}

		var gridPanel = this.GridList[params.mode];
		gridPanel.setHeight(this.TabPanel.getInnerHeight());
		gridPanel.removeAll();
		gridPanel.getGrid().getStore().load({
			params: params
		});
	},
	emkOpen: function () {
		var mode = this.TabPanel.getActiveTab().itemId;
		var grid = this.GridList[mode].getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id')) {
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		var params = {
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			ARMType: 'common'
		};
		if (this.userMedStaffFact) {
			params.userMedStaffFact = this.userMedStaffFact;
			params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id || null;
			params.LpuSection_id = this.userMedStaffFact.LpuSection_id || null;
		}
		getWnd('swPersonEmkWindow').show(params);
	},
	ccOpen: function () {
		var mode = this.TabPanel.getActiveTab().itemId;
		var grid = this.GridList[mode].getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id')) {
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		var params = {
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			action: 'edit'
		};
		if (this.userMedStaffFact) {
			params.userMedStaffFact = this.userMedStaffFact;
			params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id || null;
			params.LpuSection_id = this.userMedStaffFact.LpuSection_id || null;
		}
		getWnd('swPersonQuarantineEditWindow').show(params);
	},
	setFieldsVisible: function (tabId) {
		var forms = this.MainFiltersPanel.findBy((cmp) => {
			return cmp.itemId && cmp.itemId == 'dynamicForm';
		});

		if (!forms.length) return;
		var form = forms[0];
		form.items.each((cmp) => {
			if (cmp.ownerTabs.includes(tabId)) {
				cmp.showContainer();
				cmp.skip = false;
			}
			else {
				cmp.hideContainer();
				cmp.skip = true;
			}
		})
	},
	getFilters: function () {
		var fields = [];
		var forms = this.MainFiltersPanel.findBy((cmp) => {
			return cmp.cmpType && cmp.cmpType == 'form';
		});
		for (var i = 0; i < forms.length; i++) {
			forms[i].items.each((cmp) => { fields.push(cmp); });
		}
		return fields;
	},
	resetFilters: function () {
		var fields = this.getFilters();
		for (var i = 0; i < fields.length; i++) {
			if (fields[i].isConstant) continue;
			fields[i].reset();
		}
	},
	disableFilters: function (flag) {
		var fields = this.getFilters();
		for (var i = 0; i < fields.length; i++) {
			fields[i].setDisabled(flag);
		}
	},
	getFormParams: function () {
		var fields = this.getFilters();
		var params = {};

		for (var i = 0; i < fields.length; i++) {
			if (fields[i].skip) continue;
			var value = fields[i].getValue();
			if (fields[i].xtype == 'daterangefield' && fields[i].getValue1() && fields[i].getValue2()) value = Ext.util.Format.date(fields[i].getValue1()) + ' - ' + Ext.util.Format.date(fields[i].getValue2());
			if (fields[i].xtype == 'swdatefield') value = Ext.util.Format.date(fields[i].getValue());
			params[fields[i].getName()] = value;
		}
		return params;
	},
	processOpenRecords: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (!success) throw new Error('Ошибка при обработке открытых случаев КВИ');
					resolve(true);
				},
				url: '/?c=CVIRegistry&m=processOpenRecords'
			};
			Ext.Ajax.request(requestParams);
		});
	}
});
