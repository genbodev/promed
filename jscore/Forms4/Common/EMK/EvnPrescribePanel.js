/**
 * Панель назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.EvnPrescribePanel', {
	extend: 'swPanel',
	collapsed: true,
	requires: [
		'common.EMK.controllers.EvnPrescribePanelCntr',
		'common.EMK.models.EvnPrescribePanelModel',
		'Ext6.grid.feature.Grouping',
		'common.EMK.DropdownSelectPanel',
		'common.EMK.QuickPrescrSelect.swDrugQuickSelectWindow',
		'common.EMK.QuickPrescrSelect.swUslugaQuickSelectWindow',
		'sw.frames.EMD.swEMDPanel'
	],
	/*masked: {
		xtype: 'loadmask',
		message: '....'
	},*/
	title: 'НАЗНАЧЕНИЯ И НАПРАВЛЕНИЯ',
	allTimeExpandable: false,
	refId: 'EvnPrescribePanel',
	alias: 'widget.EvnPrescribePanel',
	controller: 'EvnPrescribePanelCntr',

	cls: 'evnPrescribePanel',
	collapseOnOnlyTitle: true,
	addSpacer: false,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	listeners: {
		'expand': 'onExpandPrescribePanel'
	},
	clearAllSelection: function (grid, rec) {
		this.getController().clearAllSelection(grid, rec);
	},
	setTitleCounter: function(count) {
		this.getController().data.EvnPrescrCount = count;
		this.callParent(arguments);
		this.ownerCt.query('evnxmleditor').forEach(function(editor) {
			editor.setParams({EvnPrescrCount: count});
			editor.refreshSpecMarkerBlocksContent();
		});
	},
	enableEdit: function(isEdit){
		this.getController().enableEdit(isEdit);
	},
	openPrintDoc: function(url)
	{
		window.open(url, '_blank');
	},
	initComponent: function() {
		var me = this,
			cntr = this.getController(),
			UslugaAdding = function (grid, rec, add, btn) {
				if (add === 'add')
					cntr.openQuickSelectWindow(grid,false,btn);
				else
					cntr.openSpecification('TTMSScheduleRecordPanel', grid, rec);
			},
			onCancelDirClick = function(rec, grid){
				cntr.cancelEvnDirection(rec, grid);
			},
			onMenuItemClick = function(menuItem, rec, grid){
				switch(menuItem.name){
					case 'ViewDirection':
						cntr.openEvnDirectionEditWindow('view', rec);
						break;
					case 'EditDirection':
						cntr.openEvnDirectionEditWindow('edit', rec);
						break;
					case 'PrintDirection':
						cntr.printEvnDirection(rec);
						break;
					case 'directZav':
						cntr.directZav(rec, grid);
						break;
				}
			};

		this.ViewPrescrGridsPanel = Ext6.create('Ext6.panel.Panel', {
			collapsible: false,
			minHeight: 90,

			flex: 1,
			split: true,
			autoHeight: true,
			padding: 10,
			frame: false,
			border: false,
			default:{
				border: false
			},
			tbar: {
				border: false,
				height: 26,
				padding: 0,
				autoWidth: false,
				margin  : 0,
				cls: 'EvnPrescrTBar',
				//reference: 'tbar',
				items: [
					{
					scale: 'small',
					text: 'Развернуть все',
					userCls: 'button-without-frame coll-exp-all button-expand-all',
					margin: '0 0 0 2',
					padding: 5,
					pressed: true,
					enableToggle: true,
					toggleHandler: function (button, pressed, eOpts) {
						this.toggleCls('button-expanded-all');
						if (this.text === 'Развернуть все') {
							this.setText('Свернуть все');
						} else {
							this.setText('Развернуть все');
						}
						cntr.expandCollapseAll(pressed);

					}
				},
				// {
				// 	padding: '0 0 0 2',
				// 	iconCls: 'grid-header-clinical-recomend-for-diagnoz-icon',
				// 	userCls: 'button-without-frame coll-exp-all cure-standart-text',
				// 	reference: 'CureStandartText',
				// 	itemId: 'CureStandartText',
				// 	flex: 6,
				// 	html: 'Клинические рекомендации для диагноза L20.0 Почесуха Бенье',
				// 	handler : function() {
				// 		me.getController().openTemplate('standart');
				// 	}
				// },
					'->' ,{
					margin: 2,
					width: 14,
					disabled: false,
					style:{
						'opacity':'0.4'
					},
					userCls: 'button-without-frame bottom-icon grid-header-icon-cito-legend',
					iconCls: 'grid-header-icon-cito',
					tooltip: langs('Cito!'),
					handler : function() {

					}
				},{
					margin: 2,
					width: 14,
					disabled: false,
					style:{
						'opacity':'0.4'
					},
					userCls: 'button-without-frame bottom-icon',
					iconCls: 'grid-header-icon-direction',
					tooltip: langs('Направление'),
					handler : function() {

					}
				},{
					margin: 2,
					width: 14,
					disabled: false,
					style:{
						'opacity':'0.4'
					},
					userCls: 'button-without-frame bottom-icon',
					iconCls: 'grid-header-icon-otherMO',
					tooltip: langs('Место оказания - другая МО'),
					handler : function() {

					}
				},{
					margin: 2,
					width: 14,
					disabled: false,
					style:{
						'opacity':'0.4'
					},
					userCls: 'button-without-frame bottom-icon',
					iconCls: 'grid-header-icon-selectDT',
					tooltip: langs('Определена дата и время. Услуга еще не оказана'),
					handler : 'loadGrids'
				},{
					margin: 2,
					width: 14,
					disabled: false,
					style:{
						'opacity':'0.4'
					},
					userCls: 'button-without-frame bottom-icon',
					iconCls: 'grid-header-icon-results',
					tooltip: langs('Результаты'),
					handler : 'loadGrids'
				},{
					xtype: 'tbspacer',
					//width: 37 // было 37 add a 16px space
					width: 20
				}
				]
			},
			items:[{
				//autoHeight: true,
				border: true,
				itemId: 'swPrescrGridsPanel',
				defaults:{
					needIcon: false,
					parentPanel: me,
					panelForMsg: me.ownerPanel,
					onCancelDirClick: onCancelDirClick,
					btnPlusInHeader: false,
					onMenuItemClick: onMenuItemClick
				},
				items: [
					{
						xtype: 'swGridEvnPrescribeLabDiag',
						title: 'Лабораторная диагностика',
						onPlusClick: UslugaAdding,
						onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
							cntr.reloadLinkedGrids(grid,false,function () {
								if(callbackFn && typeof callbackFn === 'function')
									callbackFn();
								cntr.setTitleCounterGrids();
							});
							
						},
						openTimeSeriesResults: function (selRec) {
							cntr.openTimeSeriesResults(selRec);
						},
						deleteFromDirection: function (selRec) {
							cntr.deleteFromDirection(selRec, this);
						},
						onLoadStoreFn: function () {//yl:176439 обновить Исследования в нижней панели
							if(me.ownerPanel.bottomPanel.EvnUslugaParPanel.loaded){//панель была открыта
								if(!me.ownerPanel.bottomPanel.EvnUslugaParPanel.EvnUslugaParGrid.getStore().loading){//если уже не грузится
									me.ownerPanel.bottomPanel.EvnUslugaParPanel.load();
								}
							}
						}
					},
					{
						xtype: 'swGridEvnPrescribeFuncDiag',
						title: 'Инструментальная диагностика',
						onPlusClick: UslugaAdding,
						onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
							cntr.reloadLinkedGrids(grid,false,function () {
								if(callbackFn && typeof callbackFn === 'function')
									callbackFn();
								cntr.setTitleCounterGrids();
							});
						},
						onLoadStoreFn: function () {//yl:176439 обновить Исследования в нижней панели
							if(me.ownerPanel.bottomPanel.EvnUslugaParPanel.loaded){//панель была открыта
								if(!me.ownerPanel.bottomPanel.EvnUslugaParPanel.EvnUslugaParGrid.getStore().loading){//если уже не грузится
									me.ownerPanel.bottomPanel.EvnUslugaParPanel.load();
								}
							}
						}
					},
					{
						xtype: 'swGridEvnConsUsluga',
						title: 'Консультационная услуга',
						onPlusClick: UslugaAdding,
						onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
							cntr.reloadLinkedGrids(grid,false,function () {
								if(callbackFn && typeof callbackFn === 'function')
									callbackFn();
								cntr.setTitleCounterGrids();
							});
						}
					},
					{
						xtype: 'swGridEvnCourseProc',
						title: 'Манипуляции и процедуры',
						onPlusClick: UslugaAdding,
						onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
							cntr.reloadLinkedGrids(grid,false,function () {
								if(callbackFn && typeof callbackFn === 'function')
									callbackFn();
								cntr.setTitleCounterGrids();
							});
						}
					},
					{
						xtype: 'swGridEvnPrescrOperBlock',
						title: 'Оперативное лечение',
						onPlusClick: UslugaAdding,
						onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
							cntr.reloadLinkedGrids(grid,false,function () {
								if(callbackFn && typeof callbackFn === 'function')
									callbackFn();
								cntr.setTitleCounterGrids();
							});
						}
					},
					{
						xtype: 'swGridEvnPrescrDiet',
						title: 'Диета',
						onPlusClick: function (grid, rec, add, btn) {
							if (add === 'add')
								cntr.openQuickSelectWindow(grid,false,btn);
							else
								cntr.openSpecification('EvnPrescrDietPanel', grid, rec);
						},
						onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
							cntr.reloadLinkedGrids(grid,false,function () {
								if(callbackFn && typeof callbackFn === 'function')
									callbackFn();
								cntr.setTitleCounterGrids();
							});
						}
					},
					{
						xtype: 'swGridEvnPrescrRegime',
						title: 'Режим',
						onPlusClick: function (grid, rec, add, btn) {
							if (add === 'add')
								cntr.openQuickSelectWindow(grid,false,btn);
							else
								cntr.openSpecification('EvnPrescrRegimePanel', grid, rec);
						},
						onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
							cntr.reloadLinkedGrids(grid,false,function () {
								if(callbackFn && typeof callbackFn === 'function')
									callbackFn();
								cntr.setTitleCounterGrids();
							});
						}
					},
					{
						xtype: 'swGridEvnCourseTreat',
						title: 'Лекарственные назначения',
						onPlusClick: function (grid, rec) {
							cntr.openSpecification('EvnCourseTreatEditPanel', grid, rec);
						},
						onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
							cntr.reloadLinkedGrids(grid,false,function () {
								if(callbackFn && typeof callbackFn === 'function')
									callbackFn();
								cntr.setTitleCounterGrids();
								cntr.reloadReceptsPanels();
							});
						}
					},
					{
						xtype: 'swGridEvnDirection',
						title: 'Направления к врачу',
						onPlusClick: function (grid, rec, add, btn) {
							cntr.openDirMenu(grid,btn,true);
						}
					},
					{
						xtype: 'swGridEvnDirectionCommon',
						title: 'Общие направления',
						onPlusClick: function (grid, rec, add, btn) {
							cntr.openDirMenu(grid,btn);
						}
					},
					{
						xtype: 'swGridEvnDirectionHosp',
						title: 'Направления на госпитализацию',
						onPlusClick: function (grid, rec, add, btn) {
							cntr.openDirMenu(grid,btn);
						}
					},
					{
						xtype: 'swGridEvnDirectionPat',
						title: 'Направления на патоморфологию',
						onPlusClick: function (grid, rec, add, btn) {
							cntr.openDirMenu(grid,btn);
						}
					},
					{
						xtype: 'swGridEvnVaccination',
						title: 'Вакцинация',
						onPlusClick: function (grid, rec, add, btn) {
							cntr.openQuickSelectWindow(grid,false,btn)
						}
					}
				]
			}]
		});

		this.ViewPrescrPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			autoHeight: true,
			bodyBorder: false,
			scrollable: false,
			defaults: {
			border: false
			},
			items: [
				me.ViewPrescrGridsPanel
			]
		});
		var evnPLForm = me.ownerPanel,
			personEmkWindow = evnPLForm.ownerWin,
			PersonInfoPanel = personEmkWindow.PersonInfoPanel;
		this.packetSelectPanel = Ext6.create('common.EMK.DropdownSelectPanel', {
			parentPanel: me,
			reference: 'PacketSelectPanel',
			PersonInfoPanel: PersonInfoPanel,
			onSelect: function(packet) {
				cntr.openTemplate('my',packet);
			}
		});
		this.DrugSelectPanel = Ext6.create('common.EMK.QuickPrescrSelect.swDrugQuickSelectWindow', {
			parentPanel: me,
			reference: 'DrugSelectPanel',
			onSelect: function(drug) {
			}
		});
		this.UslugaSelectPanel = Ext6.create('common.EMK.QuickPrescrSelect.swUslugaQuickSelectWindow', {
			parentPanel: me,
			reference: 'UslugaSelectPanel',
			onSelect: function(params){
				if(params.PacketPrescr_id)
					cntr.addPrescrToPacket(params,this);
				else
					cntr.saveEvnPrescr(params,this);
			}
		});
		this.printMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			defaults: {
				scope: cntr
			},
			items: [{
				text: 'Маршрутная карта назначений',
				handler: 'printEvnPLPrescr'
			}, {
				text: 'Печать назначений',
				handler: 'printAllEvnPLPrescr'
			}, {
				text: 'Печать единого направления на лабораторные исследования',
				handler: 'printOneDirectionLabResearch',
				hidden: !getRegionNick().inlist([ 'ufa' ])
			}]
		});

		/*this.plusMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'select-evn-menu',
			items: []
		});*/
		var grids = this.ViewPrescrGridsPanel.query('grid[objectPrescribe!=\'EvnDirection\']');
		var defaultTools = [
			{
				userCls: 'sw-tool',
				width: 14,
				margin: '0 0 0 20',
				type: 'add-EvnDirection',
				tooltip: 'Направления к врачу',
				handler: function(e,eOpts,header, tool){
					cntr.openDirMenu(false,tool,true);
				}
			}, {
				xtype: 'tbspacer',
				flex: 1
			}, {
				//id: me.getId()+'-template-select',
				type: 'packet-fast',
				tooltip: 'Пакетные назначения',
				minWidth: 24,
				callback: function (panel, tool, event) {
					me.packetSelectPanel.show({
						target: tool, align: 'tr-br?', force: true
					});
				}
			}, {
				type: 'save-packet-prescr',
				tooltip: 'Сохранить как пакет назначений',
				handler: 'openPacketPrescrSaveWindow'
			}, {
				type: 'open-specific',
				tooltip: 'Детализация назначений',
				handler: 'openWndSpecification'
			}, {
				type: 'open-multiple-events',
				tooltip: 'Подбор времени записи',
				handler: 'openWndAutoSelectDateTime'
				//handler: 'autoRecordAllPrescribe'
			},/*{
					type: 'prescr-list',
					tooltip: 'Лист назначений',
					disabled: true,
					handler: function () {
						inDevelopmentAlert();
					}
			}*/ {
				type: 'all-prescr-print',
				tooltip: langs('Печать'),
				disabled: true,
				width: 24,
				margin: 0,
				callback: function(panel, tool, event) {
					me.printMenu.showBy(tool);
				}
			}
		];
		// Кнопки инструментов в заголовке списка назначений
		var tools = [];
		//Инструменты по левой стороне
		grids.forEach(function (g) {
			var t = g.getTitle();
			if (typeof t === 'object')
				t = t.getText();
			tools.push(Ext6.Object.merge({
				userCls: 'sw-tool',
				width: 14,
				margin: '0 0 0 20'
			}, {
				type: 'add-' + (g.objectPrescribe ? g.objectPrescribe : 'default'),
				tooltip: t,
				grid: g,
				handler: function(e,eOpts,header, tool){
					cntr.openQuickSelectWindow(g,false,tool);
				}
			}));
		});
		// Инструменты по правой стороне
		defaultTools.forEach(function(tool){
			if(tool.xtype && tool.xtype == 'tbspacer')
				tools.push(tool);
			else
				tools.push(Ext6.Object.merge({
					userCls: 'sw-tool',
					width: 16,
					margin: '0 20 0 0'
				},tool));
		});
		Ext6.apply(me, {
			tools: tools,
			itemId: 'EvnPrescribeAllPanel',
			border: false,
			items: [
				me.ViewPrescrPanel
			]
		});

		this.callParent(arguments);
	}
});
