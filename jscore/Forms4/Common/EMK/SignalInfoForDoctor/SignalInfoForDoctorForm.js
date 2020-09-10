/**
 * Форма сигнальной информации для врачей поликлиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * SignalInfoForDoctorForm
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */
Ext6.define('common.EMK.SignalInfoForDoctor.SignalInfoForDoctorForm', {
	extend: 'base.BaseForm',
	alias: 'widget.swSignalInfoForDoctorForm',
	itemId: 'SignalInfoForDoctorForm',
	
	requires: [
		'common.EMK.SignalInfoForDoctor.controller.MainController',
		'common.EMK.SignalInfoForDoctor.controller.DisFromHospitalController',
		'common.EMK.SignalInfoForDoctor.model.MainModel',
		'common.EMK.SignalInfoForDoctor.store.MainStore',
		'common.EMK.SignalInfoForDoctor.view.MainPanel',
		'common.EMK.SignalInfoForDoctor.view.DisFromHospital'
	],
	
	viewModel: 'SignalInfoForDoctorMainModel',
	controller: 'SignalInfoForDoctorMainController',
	
	constrain: true,
	maximized: true,
	closable: true,
	header: false,
	border: false,
	layout: 'border',
	
	title: langs('Сигнальная информация для врача'),
	closeToolText: langs('Закрыть'),

	findWindow: false,
	evnParams: {},
	params: {},
	lastUpdateData: [],
	userMedStaffFact: null,

	// Панель с вкладками, наполняется в зависимости от рабочего места:
	tabPanel: undefined,

	// Вкладка "Грид" (common.EMK.SignalInfoForDoctor.view.MainPanel):
	MainPanel: undefined,

	// Вкладка "Выписанные из стационара" (common.EMK.SignalInfoForDoctor.view.DisFromHospital):
	DisFromHospital: undefined,

	addCodeRefresh: Ext6.emptyFn,
	getParams: 'getParams',

	/******************************************************************************************************************
	 *  setParams
	 *  Используется в ЭМК
	 ******************************************************************************************************************/
	setParams: function(params) {//используется в ЭМК
		this.getController().setParams(params);
	},
	
	/******************************************************************************************************************
	 *  getMainStore
	 ******************************************************************************************************************/
	getMainStore: function() {
		if(this.MainPanel && this.MainPanel.MainGrid && this.MainPanel.MainGrid.getStore())
			return this.MainPanel.MainGrid.getStore();
		else return false;
	},

	/******************************************************************************************************************
	 *  loadData
	 *  Используется в ЭМК
	 ******************************************************************************************************************/
	loadData: function(options) {//используется в ЭМК
		this.getController().loadData(options);

		let me = this;
		let vm = me.getViewModel();
		let SignalInfoForDoctorForm_id = vm.get('SignalInfoForDoctorForm_id');
	},

	/******************************************************************************************************************
	 *  show
	 ******************************************************************************************************************/
	show: function(params) {
		var me = this;
		me.userMedStaffFact = params.userMedStaffFact;
		this.DisFromHospital.userMedStaffFact = params.userMedStaffFact;
		this.getController().updateTabs(me);

		me.callParent(arguments);
	},

	/******************************************************************************************************************
	 *  initComponent
	 ******************************************************************************************************************/
	initComponent: function() {
		let me = this;
		
		me.toolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			height: 40,
			border: false,
			noWrap: true,
			right: 0,
			style: 'background: transparent;',
			items: [
				{
					xtype: 'tbspacer',
					width: 10
				}
			]
		});
		
		me.titlePanel = Ext6.create('Ext6.Panel', {
			region: 'north',
			style: {
				'box-shadow': '0px 1px 6px 2px #ccc',
				zIndex: 2
			},
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #EEEEEE;',
			items: [
				me.toolPanel
			],
			xtype: 'panel'
		});

		// Создадим вкладки по умолчанию:
		// 1. Грид:
		me.MainPanel = Ext6.create('common.EMK.SignalInfoForDoctor.view.MainPanel', {
			ownerPanel: me,
			itemId: 'MainPanel',
			collapseOnOnlyTitle: true,
			collapsed: true,
		});

		// 2. Выписанные из стационара:
		me.DisFromHospital = Ext6.create('common.EMK.SignalInfoForDoctor.view.DisFromHospital', {
			ownerPanel: me,
			itemId: 'DisFromHospitalPanel',
			collapsed: false
		});
		
		Ext6.apply(me, {
			cls: 'signal-info',
			
			defaults: {
				border: false,
				padding: 0
			},
			
			items: [
				{
					region: 'center',
					scrollable: true,
					layout: 'fit',

					items: [
						{
							xtype: 'tabpanel',
							itemId: 'tabPanel',
							
							header: {
								style: 'border: 1px solid silver; background-color: #fff;',
								titleRotation: 0,
								padding: 0,
								
								title: {
									text: "Разделы",
									flex: 0,
									height: 38,
									width: '100%',
									style: 'background-color: #ededed;',
									textAlign: 'left',
									padding: '10 20',
									margin: 0
								}
							},
							
							headerPosition : 'left',
							tabBarHeaderPosition: 1,
							tabRotation: 0,
							activeTab: null,

							tabBar: {
								cls: 'white-tab-bar',
								margin: 0,
								
								defaults: {
									cls: 'simple-tab',
									padding: '10 20',
									textAlign: 'left'
								}
							},
								
							items: [
								me.MainPanel,
								{
									title: 'Параклинические услуги',
									border: false,
								},
								{
									title: 'Вызовы СМП',
									border: false,
								},
								{
									title: 'Регистры льготников',
									border: false,
								},
								{
									title: 'Медицинские свидетельства о смерти',
									border: false,
								},
								{
									title: 'Список неявившихся',
									border: false,
								},
								me.DisFromHospital
							],

							listeners: {
								//tabchange: 'tabchange'
							}
						}
					]
				}
			]
		});

		me.callParent(arguments);

		me.tabPanel = me.down('#tabPanel');
	}
});