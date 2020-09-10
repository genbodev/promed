/**
 * Нижняя панель в ЭМК (диагнозы/исследования/нетрудоспособность/файлы)
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
Ext6.define('common.EMK.PersonBottomPanel', {
	requires: [
		'common.EMK.EvnPLDiagPanel',
		'common.EMK.EvnUslugaParPanel',
		'common.EMK.EvnStickPanel',
		'common.EMK.PersonEvnReceptPanel',
		'common.EMK.EvnMediaDataPanel',
		'common.EMK.ObserveChartPanel'
	],
	extend: 'Ext6.Panel',
	region: 'south',
	layout: 'card',
	flex: 400,
	collapsed: true,
	split: true,
	hideCollapseTool: true,
	listeners: {
		'collapse': function() {
			this.setSplitterHidden(true);
			this.bottomTabPanel.activeTab = null; // пусть думает что таб не выбран
			this.bottomTabPanel.tabBar.activeTab.deactivate(); // деактивируем активный таб
			this.bottomTabPanel.tabBar.activeTab = null; // пусть думает что таб не выбран
		}
	},
	setSplitterHidden: function(hidden) {
		if (hidden) {
			this.splitter.setHeight(0);
			this.splitter.getEl().hide();
		} else {
			this.splitter.setHeight(10);
			this.splitter.getEl().show();
		}
	},
	collapsible: true,
	scrollable: true,
	border: false,
	bodyStyle: 'border: 0;',
	setParams: function(params) {
		var me = this,
			LabelChartCount = 0;
		if(me.ownerWin.PersonInfoPanel) {
			LabelChartCount = me.ownerWin.PersonInfoPanel.getFieldValue('RemoteMonitoring_OpenedChartsCount');
		}

		this.EvnPLDiagHeader.setTitleCounter(0);
		this.EvnPLDiagPanel.setParams({
			Evn_id: params.Evn_id,
			Person_id: params.Person_id,
			Server_id: params.Server_id,
			PersonEvn_id: params.PersonEvn_id
		});
		this.EvnUslugaParHeader.setTitleCounter(0);
		this.EvnUslugaParPanel.setParams({
			Evn_id: params.Evn_id,
			Person_id: params.Person_id,
			userMedStaffFact: params.userMedStaffFact
		});
		this.EvnStickHeader.setTitleCounter(0);
		this.EvnStickPanel.setParams({
			Evn_id: params.Evn_id,
			Person_id: params.Person_id,
			Server_id: params.Server_id,
			PersonEvn_id: params.PersonEvn_id
		});
		this.ObserveChartHeader.setTitleCounter(LabelChartCount);
		this.ObserveChartPanel.setParams({
			Evn_id: params.Evn_id,
			Person_id: params.Person_id,
			Server_id: params.Server_id,
			PersonEvn_id: params.PersonEvn_id,
			ownerWin: me
		});
		//this.ObserveChartHeader.setDisabled(LabelChartCount==0);
			
		this.PersonEvnReceptHeader.setTitleCounter(0);
		this.PersonEvnReceptPanel.setParams({
			Evn_id: params.Evn_id,
			EvnClass_id: params.EvnClass_id,
			userMedStaffFact: params.userMedStaffFact,
			Person_id: params.Person_id,
			Server_id: params.Server_id,
			PersonEvn_id: params.PersonEvn_id
		});
		this.EvnMediaDataHeader.setTitleCounter(0);
		this.EvnMediaDataPanel.setParams({
			Evn_id: params.Evn_id,
			Person_id: params.Person_id
		});

		if (!params.Evn_id) {
			// задисаблить диагнозы и файлы
			this.EvnPLDiagHeader.disable();
			this.EvnMediaDataHeader.disable();
		} else {
			this.EvnPLDiagHeader.enable();
			this.EvnMediaDataHeader.enable();
		}
	},
	setTitleCounters: function(data) {
		var me = this;
		me.EvnUslugaParHeader.setTitleCounter(data.EvnUslugaParCount);
		me.EvnStickHeader.setTitleCounter(data.EvnStickCount);
		me.PersonEvnReceptHeader.setTitleCounter(data.EvnReceptCount);
		me.EvnMediaDataHeader.setTitleCounter(data.EvnMediaDataCount);
	},
	enableEdit: function(enable) {
		this.items.each(function(e){
			e.editAvailable = enable;
			if(e.checkEnable && typeof e.checkEnable=='function') e.checkEnable();
		});
	},
	initComponent: function() {
		var me = this;

		this.EvnPLDiagHeader = Ext6.create('swPanel', {
			title: 'ДИАГНОЗЫ',
			itemId: 'EvnPLDiagPanel'
		});
		this.EvnUslugaParHeader = Ext6.create('swPanel', {
			title: 'ИССЛЕДОВАНИЯ',
			itemId: 'EvnUslugaParPanel'
		});
		this.EvnStickHeader = Ext6.create('swPanel', {
			title: 'НЕТРУДОСПОСОБНОСТЬ',
			itemId: 'EvnStickPanel'
		});
		this.ObserveChartHeader = Ext6.create('swPanel', {
			title: 'МОНИТОРИНГ',
			itemId: 'ObserveChartPanel',
			ownerWin: me
		});
		this.PersonEvnReceptHeader = Ext6.create('swPanel', {
			title: 'РЕЦЕПТЫ',
			itemId: 'PersonEvnReceptPanel'
		});
		this.EvnMediaDataHeader = Ext6.create('swPanel', {
			title: 'ФАЙЛЫ',
			itemId: 'EvnMediaDataPanel'
		});

		this.EvnPLDiagPanel = Ext6.create('common.EMK.EvnPLDiagPanel', {
			header: false,
			headerPanel: this.EvnPLDiagHeader,
			itemId: 'EvnPLDiagPanel',
			ownerPanel: me.ownerPanel,
			ownerWin: me.ownerWin
		});
		this.EvnUslugaParPanel = Ext6.create('common.EMK.EvnUslugaParPanel', {
			header: false,
			headerPanel: this.EvnUslugaParHeader,
			itemId: 'EvnUslugaParPanel'
		});
		this.EvnStickPanel = Ext6.create('common.EMK.EvnStickPanel', {
			header: false,
			headerPanel: this.EvnStickHeader,
			itemId: 'EvnStickPanel',
			ownerPanel: me.ownerPanel,
			ownerWin: me.ownerWin
		});
		this.ObserveChartPanel = Ext6.create('common.EMK.ObserveChartPanel', {
			header: false,
			headerPanel: this.ObserveChartHeader,
			itemId: 'ObserveChartPanel',
			refId: 'ObserveChartPanel',
			ownerPanel: me.ownerPanel,
			ownerWin: me.ownerWin,
			isEmk: true
		});
		this.PersonEvnReceptPanel = Ext6.create('common.EMK.PersonEvnReceptPanel', {
			header: false,
			headerPanel: this.PersonEvnReceptHeader,
			itemId: 'PersonEvnReceptPanel',
			refId: 'PersonEvnReceptPanel',
			ownerPanel: me.ownerPanel,
			ownerWin: me.ownerWin
		});
		this.EvnMediaDataPanel = Ext6.create('common.EMK.EvnMediaDataPanel', {
			header: false,
			headerPanel: this.EvnMediaDataHeader,
			itemId: 'EvnMediaDataPanel'
		});

		this.bottomTabPanel = Ext6.create('Ext6.TabPanel', {
			border: false,
			region: 'center',
			activeTab: null,
			width: 2000,
			defaults: {
				tabConfig: {
					margin: 0,
						cls: 'evn-pl-tab-panel-items bottom-tab-panel-items'
				}
			},
			items: [
				me.EvnPLDiagHeader,
				me.EvnUslugaParHeader,
				me.EvnStickHeader,
				me.ObserveChartHeader,
				me.PersonEvnReceptHeader,
				me.EvnMediaDataHeader
			],
			beforeSetActiveTab: function(card) {
				if (this.getActiveTab() == card) {
					me.collapse();
					return false;
				}

				return true;
			},
			listeners: {
				'render': function() {
					me.setSplitterHidden(true);
				},
				'tabchange': function(tabPanel, newCard) {
					me.expand();
					me.setActiveItem(newCard.itemId);
					var panel = me.layout.getActiveItem();
					if (!panel.loaded) {
						panel.load();
					}

					me.setSplitterHidden(false);
				}
			}
		});

		Ext6.apply(this, {
			collapseMode: 'header',
			header: {
				xtype: 'header',
				padding: 0,
				itemPosition: 0,
				border: false,
				items: [
					me.bottomTabPanel
				]
			},
			items: [
				me.EvnPLDiagPanel,
				me.EvnUslugaParPanel,
				me.EvnStickPanel,
				me.ObserveChartPanel,
				me.PersonEvnReceptPanel,
				me.EvnMediaDataPanel
			]
		});

		this.callParent(arguments);
	}
});