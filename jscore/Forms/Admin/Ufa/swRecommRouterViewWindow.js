/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

sw.Promed.swRecommRouterViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	doResetAll: function () {
		var grid = this.findById('RecommRouterGrid').ViewGridPanel;
		grid.getStore().removeAll();
	},
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	doSearch: function (argements) {
		var grid = this.findById('RecommRouterGrid').ViewGridPanel,
			form = this.findById('RecommRouterForm'),
			params = {};
		params.Trimester = form.getForm().findField('mode').getValue();
		params.Lpu_iid = form.getForm().findField('Lpu_iid').getValue();
		switch (argements) {
			case 'RecommRouterRegionGridPanel':
				params.Type = 'rou';
				params.YesNo_id = 2;
				params.SignalInfo = 1;
				this.RecommRouterGrid.setDataUrl('/?c=PersonPregnancy&m=loadListRecommRouter');
				break;
			case 'PregnancyRouteNotConsultationRegion':
				this.RecommRouterGrid.setDataUrl('/?c=SignalInfo&m=loadPregnancyRouteNotConsultation');
				break;
			case 'PregnancyRouteHospitalRegion':
				params.PregnancyRouteType = 'Hospital';
				this.RecommRouterGrid.setDataUrl('/?c=SignalInfo&m=loadPregnancyRouteHospital');
				break;
		}
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().baseParams.start = 0;
		grid.getStore().baseParams.limit = 100;
		grid.getStore().load({
			params: params
		});
	},
	height: 550,
	id: 'RecommRouterViewWindow',
	initComponent: function () {
		var win = this;
		switch(swSignalInfoWindow.tabPanel.getActiveTab().getId()) {
			case 'tab_tabPanelPersonPregnancy':
				//Беременные женщины
				win.stringfields = [
					{name: 'PersonPregnancy_id', hidden: true, type: 'int'},
					{name: 'PersonRegister_id', hidden: true, type: 'int'},
					{name: 'Person_id', hidden: true, type: 'int'},
					{name: 'Server_id', hidden: true, type: 'int'},
					{name: 'PersonRegister_Code', header: 'Номер индивидуальной карты беременной', type: 'string', width: 120},
					{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 160},
					{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 120},
					{name: 'PersonPregnancy_Period', header: 'Срок', type: 'int', width: 50},
					{name: 'Trimester', header: 'Триместр', type: 'string', width: 160},
					{name: 'RiskType_AName', header: 'Степень риска с учетом ключ. факт.', width: 100},
					{name: 'EvnVizitPL_setDate', header: 'Дата предыдущего осмотра', type: 'date', width: 160},
					{name: 'PersonPregnancy_birthDate', header: 'Предполагаемый срок родов', type: 'date', width: 160},
					{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 140},
					{name: 'NickHospital', type: 'string',header: 'МО госпитализации', id: 'autoexpand'}
				];
				break;
			case 'tab_tabPanelPregnancyRouteNoConsultation':
				//Не проведена консультация
				win.stringfields = [
					{name: 'PersonPregnancy_id', hidden: true, type: 'int'},
					{name: 'PersonRegister_id', hidden: true, type: 'int'},
					{name: 'Person_id', hidden: true, type: 'int'},
					{name: 'Server_id', hidden: true, type: 'int'},
					{name: 'PersonRegister_Code', header: 'Номер индивидуальной карты беременной', type: 'string', width: 120},
					{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 160},
					{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 120},
					{name: 'PersonPregnancy_Period', header: 'Срок', type: 'int', width: 50},
					{name: 'Trimester', header: 'Триместр', type: 'string', width: 160},
					{name: 'RiskType_AName', header: 'Степень риска с учетом ключ. факт.', width: 100},
					{name: 'lstfactorrisk', header: 'Наличие ключевых факторов риска', width: 400},
					{name: 'PersonPregnancy_ObRisk', type: 'int', header: 'Баллы перинатального риска', width: 60},
					{name: 'EvnVizitPL_setDate', header: 'Дата предыдущего осмотра', type: 'date', width: 160},
					{name: 'PersonPregnancy_birthDate', header: 'Предполагаемый срок родов', type: 'date', width: 160},
					{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 140},
					{name: 'NickHospital', type: 'string',header: 'МО госпитализации', id: 'autoexpand'}
				];
				break;
			case 'tab_tabPanelPregnancyRouteHospital':
				//Находятся на госпитализации
				win.stringfields = [
					{name: 'PersonPregnancy_id', hidden: true, type: 'int'},
					{name: 'PersonRegister_id', hidden: true, type: 'int'},
					{name: 'Person_id', hidden: true, type: 'int'},
					{name: 'Server_id', hidden: true, type: 'int'},
					{name: 'PersonRegister_Code', header: 'Номер индивидуальной карты беременной', type: 'string', width: 120},
					{name: 'Person_Fio', header: 'ФИО', type: 'string', width: 160},
					{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 120},
					{name: 'PersonPregnancy_Period', header: 'Срок', type: 'int', width: 50},
					{name: 'Trimester', header: 'Триместр', type: 'string', width: 160},
					{name: 'RiskType_AName', header: 'Степень риска с учетом ключ. факт.', width: 100},
					{name: 'LpuUnitType_Name', header: 'Тип стационара', width: 150},
					{name: 'NickHospital', type: 'string',header: 'МО госпитализации',  width: 150},
					{name: 'EvnPS_setDate', type: 'date', header: 'Дата госпитализации', width: 130},
					{name: 'ProfilHospital', header: langs('Профиль'), width: 150},
					{name: 'EvnVizitPL_setDate', header: 'Дата предыдущего осмотра', type: 'date', width: 160},
					{name: 'PersonPregnancy_birthDate', header: 'Предполагаемый срок родов', type: 'date', width: 160},
					{name: 'MesLevel_Name', header: 'МО родоразрешения', width: 140}
				];
				break;
		}
		
		
		this.RecommRouterGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function () {
						win.openPersonPregnancyEditWindow()
					}},
				{name: 'action_delete', hidden: true}
			],
			dataUrl: '/?c=PersonPregnancy&m=loadListRecommRouter',
			autoLoadData: false,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			noFocusOnLoadOneTime: true,
			region: 'center',
			root: 'data',
			id: 'RecommRouterGrid',
			Height: 150,
			onLoadData: function () {
			},
			stringfields: win.stringfields
		})

		Ext.apply(this, {
			buttons: [
				'-',
				HelpButton(this),
				{
					handler: function () {
						Ext.getCmp('RecommRouterViewWindow').refresh();
					},
					iconCls: 'close16',
					id: 'PCSDVW_CancelButton',
					tabIndex: 2034,
					text: BTN_FRMCLOSE
				}
			],
			items: [new Ext.form.FormPanel({
					autoHeight: true,
					id: 'RecommRouterForm',
					items: [{
							xtype: 'hidden',
							name: 'mode'
						}, {
							xtype: 'hidden',
							name: 'Lpu_iid'
						}
					],
					//labelAlign: 'right',
					region: 'north'
				}),

				this.RecommRouterGrid
			]
		});
		sw.Promed.swRecommRouterViewWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function () {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: true,
	plain: true,
	resizable: true,
	refreshPersonCardViewGrid: function () {
		this.doSearch();
	},
	openEPHForm: function () {

		var record = this.RecommRouterGrid.getGrid().getSelectionModel().getSelected();

		if (!record)
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Ошибка выбора записи!'));
			return false;
		}
		if (getWnd('swPersonEmkWindow').isVisible())
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Форма ЭМК (ЭПЗ) в данный момент открыта.'));
			return false;
		} else
		{
			var params = {
				userMedStaffFact: this.userMedStaffFact,
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				mode: 'workplace',
				ARMType: 'common'
			};
			getWnd('swPersonEmkWindow').show(params);
		}
	},
	openPersonPregnancyEditWindow: function () {
		var record = this.RecommRouterGrid.getGrid().getSelectionModel().getSelected();
		var params = {};
		if (!record || Ext.isEmpty(record.get('PersonRegister_id'))) {
			return false;
		}
		params.Person_id = record.get('Person_id');
		params.PersonRegister_id = record.get('PersonRegister_id');
		params.action = 'view';
		getWnd('swPersonPregnancyEditWindow').show(params);
	},
	show: function () {
		sw.Promed.swRecommRouterViewWindow.superclass.show.apply(this, arguments);

		var grid = this.findById('RecommRouterGrid').ViewGridPanel;
		grid.getStore().removeAll();

		grid.removeListener('rowdblclick');

		grid.on('rowdblclick', function (grd, index) {
			grid.ownerCt.ownerCt.ViewActions.action_view.items[0].handler();
		});

		this.doResetAll();
		var form = this.findById('RecommRouterForm');
		form.getForm().setValues(arguments[0]);

		if (arguments[0] && arguments[0].onHide)
			this.onHide = arguments[0].onHide;
		else
			this.onHide = function () {};

		this.type = arguments[0].type;

		this.doSearch(this.type);

		var emkmenu = {
			handler: function () {
				this.openEPHForm();
			}.createDelegate(this),
			iconCls: 'open16',
			name: 'open_emk',
			text: langs('Открыть ЭМК'),
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			disabled: false
		};
		this.RecommRouterGrid.addActions(emkmenu);
		switch (this.type){
			case 'RecommRouterRegionGridPanel':
				swRecommRouterViewWindow.setTitle('Беременные женщины с пропущенным плановым осмотром');
				break;
			case 'PregnancyRouteNotConsultationRegion':
				swRecommRouterViewWindow.setTitle('Не проведена консультация');
				break;
			case 'PregnancyRouteHospitalRegion':
				swRecommRouterViewWindow.setTitle('Беременные женщины на госпитализации');
				break;
		}
	},
	width: 1600,
	title: '',
	refresh: function () {
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText('Обновить ' + this.objectName + ' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];
	}
});