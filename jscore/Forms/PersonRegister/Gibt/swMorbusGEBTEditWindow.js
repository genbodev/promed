/**
* swMorbusGEBTEditWindow - форма «Нуждаемость в ГИБТ»
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PersonRegister
* @access       public
* @copyright	Copyright (c) 2019 Swan Ltd.
*/

sw.Promed.swMorbusGEBTEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Нуждаемость в ГИБТ'),
	id: 'swMorbusGEBTEditWindow',
	layout: 'form',
	maximizable: false,
	shim: false,
	width: 950,
	autoHeight: true,
	minHeight: 420,
	modal: true,
	show: function() {
		
		sw.Promed.swMorbusGEBTEditWindow.superclass.show.apply(this, arguments);
	
		this.MorbusGEBT_id = arguments[0].MorbusGEBT_id || null;
		this.returnFunc = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		this.action = arguments[0].action || 'edit';
		
		this.center();
		
		this.setTitle('Нуждаемость в ГИБТ: ' + (this.action == 'view' ? 'Просмотр' : 'Редактирование'));
		
		this.InformationPanel.load({Person_id: arguments[0].Person_id});
		this.InformationPanel.enable();
		
		this.MorbusGEBTDrugGrid.setReadOnly(this.action == 'view');
		this.MorbusGEBTPlanGrid.setReadOnly(this.action == 'view');
		
		this.MorbusGEBTDrugGrid.loadData({
			globalFilters: {MorbusGEBT_id: this.MorbusGEBT_id}, 
			params: {MorbusGEBT_id: this.MorbusGEBT_id}
		});
		this.MorbusGEBTPlanGrid.loadData({
			globalFilters: {MorbusGEBT_id: this.MorbusGEBT_id},
			params: {MorbusGEBT_id: this.MorbusGEBT_id}
		});
	},
	addMorbusGEBTPlan: function() {
		var grid = this.MorbusGEBTPlanGrid.getGrid();
		var index = grid.getStore().findBy(function(rec) {
			return rec.get('MorbusGEBTPlan_Treatment') == 'false';
		});
		if (index >= 0) {
			Ext.Msg.alert('Ошибка', 'Существует не проведенное запланированное лечение, новое планирование лечения невозможно');
			return false;
		}
		getWnd('swMorbusGEBTPlanEditWindow').show({
			MorbusGEBT_id: this.MorbusGEBT_id,
			callback: function() {
				grid.getStore().load()
			}
		});
	},
	initComponent: function() {
		
		var win = this;
		
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});

		this.MorbusGEBTDrugGrid = new sw.Promed.ViewFrame({
			id: 'MorbusGEBTDrugGrid',
			title: langs('Курс препарата'),
			object: 'MorbusGEBTDrug',
			editformclassname: 'swMorbusGEBTDrugEditWindow',
			dataUrl: '/?c=MorbusGEBT&m=loadMorbusGEBTDrugList',
			autoLoadData: false,
			height: 200,
			region: 'center',
			useEmptyRecord: false,
			stringfields: [
				{name: 'MorbusGEBTDrug_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'MorbusGEBT_id', type: 'int', hidden: true},
				{name: 'DrugComplexMNN_id', type: 'int', hidden: true},
				{name: 'Drug_Name', header: langs('МНН'), width: 150, id: 'autoexpand'},
				{name: 'MorbusGEBTDrug_OneInject', header: langs('На одно введение'), width: 110},
				{name: 'MorbusGEBTDrug_InjectCount', header: langs('Количество введений'), width: 130},
				{name: 'MorbusGEBTDrug_InjectQuote', header: langs('Количество введений на квоту'), width: 170},
				{name: 'MorbusGEBTDrug_QuoteYear', header: langs('Количество квот в год'), width: 130},
				{name: 'MorbusGEBTDrug_BoxYear', header: langs('Упаковок в год'), width: 100}
			],
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete'},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			]
		});

		this.MorbusGEBTPlanGrid = new sw.Promed.ViewFrame({
			id: 'MorbusGEBTPlanGrid',
			title: langs('Планируемое лечение'),
			object: 'MorbusGEBTPlan',
			editformclassname: 'swMorbusGEBTPlanEditWindow',
			dataUrl: '/?c=MorbusGEBT&m=loadMorbusGEBTPlanList',
			autoLoadData: false,
			height: 200,
			region: 'south',
			useEmptyRecord: false,
			stringfields: [
				{name: 'MorbusGEBTPlan_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'MorbusGEBT_id', type: 'int', hidden: true},
				{name: 'DrugComplexMNN_id', type: 'int', hidden: true},
				{name: 'MedicalCareType_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'MorbusGEBTPlan_Year', header: langs('Год лечения'), width: 80},
				{name: 'MorbusGEBTPlan_Month', header: langs('Месяц лечения'), width: 100},
				{name: 'MedicalCareType_Name', header: langs('Условия оказания МП'), width: 140},
				{name: 'Lpu_Nick', header: langs('МО планируемого лечения'), width: 160},
				{name: 'Drug_Name', header: langs('Препарат'), width: 150, id: 'autoexpand'},
				{name: 'MorbusGEBTPlan_Treatment', header: langs('Лечение проведено'), type: 'checkbox', width: 120}
			],
			actions: [
				{name: 'action_add', handler: function() {win.addMorbusGEBTPlan()}},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete'},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			]
		});	
		
		Ext.apply(this, 
		{
			items: [
				this.InformationPanel,
				this.MorbusGEBTDrugGrid,
				this.MorbusGEBTPlanGrid
			]
		});
		sw.Promed.swMorbusGEBTEditWindow.superclass.initComponent.apply(this, arguments);
	},
	buttons:
	[{
		text:'-'
	},{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	},{
		text: BTN_FRMCLOSE,
		id: 'lbCancel',
		iconCls: 'cancel16',
		handler: function() {
			this.ownerCt.hide();
		}
	}]
});