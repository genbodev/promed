/**
 * swSpecificationDetailWnd - Окно детализации назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @author       gtp_fox
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.tools.swSpecificationDetailWnd', {
	requires: [
		'common.EMK.PersonInfoPanel',
		'common.EMK.models.EvnPrescribePanelModel',
		'common.EMK.controllers.SpecificationDetailCntr',
		'common.EMK.SpecificationDetail.TTMSScheduleRecordPanel',
		'common.EMK.SpecificationDetail.EvnPrescrRegimePanel',
		'common.EMK.SpecificationDetail.EvnPrescrDietPanel',
		'common.EMK.SpecificationDetail.EvnCourseTreatEditPanel',
		'common.EMK.SpecificationDetail.EvnPrescrUslugaInputPanel',
		'common.EMK.SpecificationDetail.InDevelopPanel'
	],
	extend: 'base.BaseForm',
	//extend: 'Ext6.window.Window',
	maximized: true,
	itemId: 'common',
	callback: Ext6.emptyFn,
	isLoading: false,
	historyGroupMode: null,
	//объект с параметрами рабочего места, с которыми была открыта форма АРМа
	userMedStaffFact: null,
	controller: 'SpecificationDetailCntr',
	listeners: {
		//'show': 'onExpandPrescribePanel',
		'hide': 'onHide'
	},



	/* свойства */
	alias: 'widget.swSpecificationDetailWnd',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new evnPrescribePanel',
	constrain: true,
	autoHeight: true,
	findWindow: false,
	header: false,
	modal: false,
	layout: 'border',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	//resizable: false,
	width: 1000,
	manyDrug: false,
	title: 'Детализация назначений',
	data: {},
	firstLoad: true,

	/* методы */
	clearAllSelection: function (grid, rec) {
		this.getController().clearAllSelection(grid, rec);
	},
	show: function(data) {
		this.callParent(arguments);
		var win = this,
			cntr = win.getController();

		if (!arguments || !arguments[0]) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}
		win.action = (typeof data.action == 'string' ? data.action : 'add');
		win.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
		win.formParams = (typeof data.formParams == 'object' ? data.formParams : {});
		win.data = data;
		var loadPersonInfo = (!this.Person_id || this.Person_id !== data.Person_id);
		//log(arguments);

		this.historyGroupMode = null;
		this.currentNode = null;
		this.historyFilterClassChecked = [];


		this.Person_id = data.Person_id;
		this.PersonEvn_id = data.PersonEvn_id;
		this.Server_id = data.Server_id;
		this.userMedStaffFact = data.userMedStaffFact;
		this.openEvn = data.openEvn || null;
		this.evnPrescrCntr = data.evnPrescrCntr || null;

		cntr.prescrArrItems = data.prescrArrItems || null;

		this.useArchive = false;

		this.TimetableGraf_id = null;
		if (arguments[0].TimetableGraf_id) {
			this.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}
		this.ViewPrescrGridsPanel.mask('Загрузка списка назначений');
		if(win.firstLoad) {

			this.PrescribeSpecificationPanel.mask('Загрузка форм детализации');
		}
		if(loadPersonInfo) {
			this.PersonInfoPanel.load({
				Person_id: this.Person_id,
				Server_id: this.Server_id,
				userMedStaffFact: this.userMedStaffFact,
				PersonEvn_id: this.PersonEvn_id,
				callback: function () {
				}
			});
		}

		//this.fireEvent('afterShowAll');


		Ext6.defer(function(){
			win.loadGridsAndPanels();
		},100);
		//win.center();

		//var base_form = win.FormPanel.getForm();
		//base_form.reset();
		//base_form.setValues(win.formParams);

		//this.ViewPrescrPanel.setHeight(ViewPrescrGridsPanel.getHeight())

	},
	loadGridsAndPanels: function () {
		var win = this,
			cntr = win.getController(),
			data = win.data,
			selRec = data.record;

		if(win.firstLoad){
			win.addGrids();
			win.addPanels();
			win.firstLoad = false;
		}

		var conf = {
			userMedStaffFact: win.userMedStaffFact || data.userMedStaffFact,
			Person_id: win.Person_id || data.Person_id,
			PersonEvn_id: win.PersonEvn_id || data.PersonEvn_id,
			Server_id: win.Server_id || data.Server_id,
			Evn_id: data.Evn_id,
			Evn_setDate: data.Evn_setDate,
			LpuSection_id: data.LpuSection_id,
			MedPersonal_id: data.MedPersonal_id,
			Diag_id: data.Diag_id,
			callback: function() {
				cntr.loadGrids();
			}
		};
		cntr.loadData(conf);
		cntr.onExpandPrescribePanel();
		if(data.prescribe){
			var grid = cntr.getGridByObject(data.prescribe);
			cntr.openSpecification(data.prescribe, grid, selRec);
			/*cntr.cbFn = function(){
				//На случай, если что-то забыли сделать
			};*/
		}
		else{
			cntr.openSpecification();
		}
		if(data.onLoadForm)
			data.onLoadForm();
		win.ViewPrescrGridsPanel.unmask();
	},
	addGrids: function () {

		var me = this,
			cntr = me.getController(),
			widthBrowser = 20;

		if (Ext6.browser.is('Firefox')) {
			widthBrowser = 29;
		}
		this.GridsPanel = Ext6.create('Ext6.panel.Panel', {
			xtype: 'panel',
			//layout: 'auto',
			scrollable: 'y',
			border: false,
			tbar: {
				border: false,
				padding: '15 0 0 0',
				autoWidth: false,
				margin: 0,
				cls: 'EvnPrescrTBar',
				//reference: 'tbar',
				items: [
					{
						scale: 'small',
						text: 'Развернуть все',
						userCls: 'button-without-frame coll-exp-all button-expand-all-min',
						margin: 2,
						padding: 5,
						pressed: true,
						enableToggle: true,
						toggleHandler: function (button, pressed, eOpts) {
							this.toggleCls('button-expanded-all');
							if (this.text == 'Развернуть все') {
								this.setText('Свернуть все');
							} else {
								this.setText('Развернуть все');
							}
							me.getController().expandCollapseAll(pressed);
						}
					}, {
						scale: 'small',
						text: 'Обновить',
						userCls: 'button-without-frame button-reload-grids',
						margin: 2,
						padding: '5 5 5 0',
						handler: function () {
							cntr.loadGrids();
						}
					}, '->',
					{
						margin: 2,
						width: 14,
						disabled: false,
						userCls: 'button-without-frame bottom-icon grid-header-icon-cito-legend',
						iconCls: 'grid-header-icon-cito',
						tooltip: langs('Cito!'),
						style: {
							'opacity': '0.4'
						},
						handler: function () {
						}
					}, {
						margin: 2,
						width: 14,
						disabled: false,
						userCls: 'button-without-frame bottom-icon',
						iconCls: 'grid-header-icon-direction',
						tooltip: langs('Направление'),
						style: {
							'opacity': '0.4'
						},
						handler: function () {

						}
					}, {
						margin: 2,
						width: 14,
						disabled: false,
						style: {
							'opacity': '0.4'
						},
						userCls: 'button-without-frame bottom-icon',
						iconCls: 'grid-header-icon-otherMO',
						tooltip: langs('Место оказания - другая МО'),
						handler: function () {

						}
					}, {
						margin: 2,
						width: 14,
						disabled: false,
						style: {
							'opacity': '0.4'
						},
						userCls: 'button-without-frame bottom-icon',
						iconCls: 'grid-header-icon-selectDT',
						tooltip: langs('Определена дата и время. Услуга еще не оказана'),
						handler: 'loadGrids'
					}, {
						margin: '2 10 2 2',
						width: 14,
						disabled: false,
						style: {
							'opacity': '0.4'
						},
						userCls: 'button-without-frame bottom-icon',
						iconCls: 'grid-header-icon-results',
						tooltip: langs('Результаты'),
						handler: 'loadGrids'
					}, {
						xtype: 'tbspacer',
						//width: 37 // было 37 add a 16px space
						width: widthBrowser // без скролла
					}
				]
			},
			items: []
		});
		var arrGrids = [
			{
				xtype: 'swGridEvnPrescribeLabDiag',
				title: 'ЛАБОРАТОРНАЯ ДИАГНОСТИКА',
				onItemClick: 'TTMSScheduleRecordPanel',
				onPlusClick: 'EvnPrescrUslugaInputPanel',
				openTimeSeriesResults: function (selRec) {
					cntr.openTimeSeriesResults(selRec);
				},
				deleteFromDirection: function (selRec) {
					cntr.deleteFromDirection(selRec, this);
				}
			}, {
				xtype: 'swGridEvnPrescribeFuncDiag',
				title: 'ИНСТРУМЕНТАЛЬНАЯ ДИАГНОСТИКА',
				onItemClick: 'TTMSScheduleRecordPanel',
				onPlusClick: 'EvnPrescrUslugaInputPanel'
			}, {
				xtype: 'swGridEvnConsUsluga',
				title: 'КОНСУЛЬТАЦИОННАЯ УСЛУГА',
				onItemClick: 'TTMSScheduleRecordPanel',
				onPlusClick: 'EvnPrescrUslugaInputPanel'
			}, {
				xtype: 'swGridEvnCourseProc',
				title: 'МАНИПУЛЯЦИИ И ПРОЦЕДУРЫ',
				onItemClick: 'TTMSScheduleRecordPanel',
				onPlusClick: 'EvnPrescrUslugaInputPanel'
			}, {
				xtype: 'swGridEvnPrescrOperBlock',
				title: 'ОПЕРАТИВНОЕ ЛЕЧЕНИЕ',
				onItemClick: 'TTMSScheduleRecordPanel',
				onPlusClick: 'EvnPrescrUslugaInputPanel'
			}, {
				xtype: 'swGridEvnPrescrDiet',
				title: 'ДИЕТА',
				onItemClick: 'EvnPrescrDietPanel',
				onPlusClick: 'EvnPrescrDietPanel'
			}, {
				xtype: 'swGridEvnPrescrRegime',
				title: 'РЕЖИМ',
				onItemClick: 'EvnPrescrRegimePanel',
				onPlusClick: 'EvnPrescrRegimePanel'
			}, {
				xtype: 'swGridEvnCourseTreat',
				title: 'ЛЕКАРСТВЕННЫЕ НАЗНАЧЕНИЯ',
				onItemClick: 'EvnCourseTreatEditPanel',
				onPlusClick: 'EvnCourseTreatEditPanel'
			}
		];
		arrGrids.forEach(function(grid_el){
			var gridComp = Ext6.create(grid_el.xtype, {
				parentPanel: me,
				panelForMsg: me,
				title: grid_el.title,
				onItemClick: function (grid, rec) {
					cntr.openSpecification(grid_el.onItemClick, grid, rec);
				},
				onPlusClick: function (grid, rec, add, btn){
					var cbFn = function(){
						cntr.loadGrids([grid.objectPrescribe]);
					};
					me.evnPrescrCntr.openQuickSelectWindow(grid,cbFn,btn);
					//me.getController().openSpecification(grid_el.onPlusClick, grid, rec);
					cntr.clearAllSelection(grid);
				},
				onDelFn: function (grid, selRec, recIsSelected, callbackFn) {
					grid.getStore().reload({
						callback: function (records, operation, success) {
							if(callbackFn && typeof callbackFn == 'function') callbackFn();
							cntr.onDeletePrescribe(grid, selRec, recIsSelected);
							if(grid.objectPrescribe === 'EvnCourseTreat' && me.evnPrescrCntr){
								me.evnPrescrCntr.reloadReceptsPanels();
							}
						}
					});
					
				},
				openTimeSeriesResults: grid_el.openTimeSeriesResults?grid_el.openTimeSeriesResults:Ext6.emptyFn,
				deleteFromDirection: grid_el.deleteFromDirection?grid_el.deleteFromDirection:Ext6.emptyFn,
				onCancelDirClick: function(rec, grid){
					cntr.cancelEvnDirection(rec, grid);
				}
			});
			me.GridsPanel.add(gridComp);
		});
		me.ViewPrescrGridsPanel.add(this.GridsPanel);
	},
	addPanels: function(){
		var me = this,
			arrPanels = [
				{cardNumber: 2, name: 'TTMSScheduleRecordPanel'},
				{cardNumber: 3, name: 'EvnCourseTreatEditPanel'},
				{cardNumber: 4, name: 'EvnPrescrUslugaInputPanel'},
				{cardNumber: 5, name: 'EvnPrescrRegimePanel'},
				{cardNumber: 6, name: 'EvnPrescrDietPanel'}
			];
		arrPanels.forEach(function(panel){
			me[panel.name] = Ext6.create('common.EMK.SpecificationDetail.'+panel.name, {
				typePanel: 'SpecificationPanel',
				parentPanel: me
			});
			me.PrescribeSpecificationPanel.add({
				cardNumber: panel.cardNumber,
				items:[me[panel.name]]
			});
		});
		me.PrescribeSpecificationPanel.unmask();
	},
	/* конструктор */
	initComponent: function() {
		var me = this;

		this.ViewPrescrGridsPanel = Ext6.create('Ext6.panel.Panel', {
			floatable: false,
			animCollapse: true,
			region:'west',
			layout: 'fit',
			flex: 1,
			title: {
				text: 'СПИСОК НАЗНАЧЕНИЙ',
				style:{'fontSize':'14px', 'fontWeight':'500'},
				rotation: 2,
				textAlign: 'right'
			},
			autoHeight: true,
			header: false,
			collapsible: true,
			split: true,
			userCls: 'view-prescr-grids-panel',
			frame: false,
			border: false,
			default:{
				border: false
			}
		});

		this.InDevelopPanel = Ext6.create('common.EMK.SpecificationDetail.InDevelopPanel', {
			parentPanel: me,
			mode: 'SpecificationWindow'
		});

		this.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			addToolbar: false,
			border: false,
			userMedStaffFact: this.userMedStaffFact,
			ownerWin: this
		});
		this.PrescribeSpecificationPanel = Ext6.create('Ext6.panel.Panel', {
			header: false,
			region: 'center',
			autoHeight: true,
			collapsible: false,
			split: true,
			collapsed: false,
			preventHeader: true ,
			hideCollapseTool: false,
			//collapseMode: 'mini',
			floatable: true,
			minHeight: 90,
			flex: 2,
			frame: false,
			bodyPadding: 0,
			layout: 'card',
			activeItem: 0,
			height: 300, // в макете 300
			border: false,
			defaults: {
				layout: 'fit',
				autoHeight: true,
				border: false
			},
			items: [
				{cardNumber: 1, items:[me.InDevelopPanel]}
			]
		});

		this.ViewPrescrPanel = Ext6.create('Ext6.panel.Panel', {
			region: 'center',
			layout: 'border',
			border: false,
			autoHeight: true,
			bodyBorder: false,
			userCls: 'prescr-grids-panel',
			defaults: {
				border: false
			},
			items: [
				me.ViewPrescrGridsPanel,
				me.PrescribeSpecificationPanel
			],
			listeners: {
				/*add: function (c, i) {
					if (i.xtype == 'bordersplitter') i.width = 2;
				}*/
			}
		});

		Ext6.apply(me, {
			items: [me.PersonInfoPanel, me.ViewPrescrPanel],
			buttons: []
		});

		this.callParent(arguments);

		//this.getController().onExpandPrescribePanel();
	}
});
