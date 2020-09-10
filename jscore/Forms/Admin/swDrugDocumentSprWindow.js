/**
 * swDrugDocumentSprWindow - окно просмотра справочников системы учета медикаментов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.02.2016
 */

/*NO PARSE JSON*/

sw.Promed.swDrugDocumentSprWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugDocumentSprWindow',
	layout: 'border',
	maximized: true,
	maximizable: false,
	title: 'Справочники системы учета медикаментов',

	openEditWindow: function(action, gridPanel) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var grid = gridPanel.getGrid();
		var idFieldName = gridPanel.object+'_id';

		var params = {action: action};
		params.callback = function() {
			gridPanel.getAction('action_refresh').execute();
		};
		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();

			if (!record || Ext.isEmpty(record.get(idFieldName))) {
				return false;
			}

			params.formParams = {};
			params.formParams[idFieldName] = record.get(idFieldName);
		}

		getWnd(gridPanel.editformclassname).show(params);
		return true;
	},

	show: function() {
		sw.Promed.swDrugDocumentSprWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;

		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		this.TabPanel.setActiveTab(4);
		this.TabPanel.setActiveTab(3);
		this.TabPanel.setActiveTab(2);
		this.TabPanel.setActiveTab(1);
		this.TabPanel.setActiveTab(0);

		var readOnly = (this.ARMType != 'superadmin' || !isSuperAdmin());

		this.DrugFinanceGridPanel.setReadOnly(readOnly);
		this.BudgetFormTypeGridPanel.setReadOnly(readOnly);
		this.WhsDocumentCostItemTypeGridPanel.setReadOnly(readOnly);
		this.FinanceSourceGridPanel.setReadOnly(readOnly);

		this.doLayout();
	},

	initComponent: function() {
		var wnd = this;

		function addTooltip(value, metadata, record, rowIndex, colIndex, store){
			metadata.attr = 'ext:qtip="' + value + '"';
			return value;
		}

		this.DrugFinanceGridPanel = new sw.Promed.ViewFrame({
			id: 'DDSW_DrugFinanceGridPanel',
			dataUrl: '/?c=Farmacy&m=loadDrugFinanceGrid',
			object: 'DrugFinance',
			editformclassname: 'swDrugFinanceEditWindow',
			border: false,
			autoLoadData: false,
			paging: false,
			stringfields: [
				{name: 'DrugFinance_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugFinance_Code', header: 'Код', type: 'int', width: 120},
				{name: 'DrugFinance_Name', header: 'Наименование', type: 'string', id: 'autoexpand'},
				{name: 'DrugFinance_begDate', header: 'Дата начала', type: 'date', width: 120},
				{name: 'DrugFinance_endDate', header: 'Дата окончания', type: 'date', width: 120}
			],
			actions: [
				{name:'action_add', handler: function(){this.openEditWindow('add',this.DrugFinanceGridPanel)}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openEditWindow('edit',this.DrugFinanceGridPanel)}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openEditWindow('view',this.DrugFinanceGridPanel)}.createDelegate(this)},
				{name:'action_delete'}
			]
		});

		this.BudgetFormTypeGridPanel = new sw.Promed.ViewFrame({
			id: 'DDSW_BudgetFormTypeGridPanel',
			dataUrl: '/?c=Farmacy&m=loadBudgetFormTypeGrid',
			object: 'BudgetFormType',
			editformclassname: 'swBudgetFormTypeEditWindow',
			border: false,
			autoLoadData: false,
			paging: false,
			stringfields: [
				{name: 'BudgetFormType_id', type: 'int', header: 'ID', key: true},
				{name: 'BudgetFormType_Code', header: 'Код', type: 'int', width: 120},
				{name: 'BudgetFormType_Name', header: 'Наименование', type: 'string', id: 'autoexpand'},
				{name: 'BudgetFormType_begDate', header: 'Дата начала', type: 'date', width: 120},
				{name: 'BudgetFormType_endDate', header: 'Дата окончания', type: 'date', width: 120}
			],
			actions: [
				{name:'action_add', handler: function(){this.openEditWindow('add',this.BudgetFormTypeGridPanel)}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openEditWindow('edit',this.BudgetFormTypeGridPanel)}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openEditWindow('view',this.BudgetFormTypeGridPanel)}.createDelegate(this)},
				{name:'action_delete'}
			]
		});

		this.WhsDocumentCostItemTypeGridPanel = new sw.Promed.ViewFrame({
			id: 'DDSW_WhsDocumentCostItemTypeGridPanel',
			dataUrl: '/?c=Farmacy&m=loadWhsDocumentCostItemTypeGrid',
			object: 'WhsDocumentCostItemType',
			editformclassname: 'swWhsDocumentCostItemTypeEditWindow',
			border: false,
			autoLoadData: false,
			paging: false,
			stringfields: [
				{name: 'WhsDocumentCostItemType_id', type: 'int', header: 'ID', key: true},
				{name: 'WhsDocumentCostItemType_Code', header: 'Код', type: 'int', width: 80},
				{name: 'WhsDocumentCostItemType_Name', header: 'Наименование', type: 'string', id: 'autoexpand'},
				{name: 'DrugFinance_Name', header: 'Финансирование', type: 'string', width: 220},
				{name: 'PersonRegisterType_Name', header: 'Регистр', type: 'string', width: 260},
				{name: 'WhsDocumentCostItemType_isDLO', header: 'ЛЛО', type: 'checkbox', width: 60},
				{name: 'WhsDocumentCostItemType_isPersonAllocation', header: 'Персональная разнарядка', type: 'checkbox', width: 150},
				{name: 'WhsDocumentCostItemType_isDrugRequest', header: 'Заявка', type: 'checkbox', width: 60},
				{name: 'WhsDocumentCostItemType_begDate', header: 'Дата начала', type: 'date', width: 120},
				{name: 'WhsDocumentCostItemType_endDate', header: 'Дата окончания', type: 'date', width: 120}
			],
			actions: [
				{name:'action_add', handler: function(){this.openEditWindow('add',this.WhsDocumentCostItemTypeGridPanel)}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openEditWindow('edit',this.WhsDocumentCostItemTypeGridPanel)}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openEditWindow('view',this.WhsDocumentCostItemTypeGridPanel)}.createDelegate(this)},
				{name:'action_delete'}
			]
		});

		this.FinanceSourceGridPanel = new sw.Promed.ViewFrame({
			id: 'DDSW_FinanceSourceGridPanel',
			dataUrl: '/?c=Farmacy&m=loadFinanceSourceGrid',
			object: 'FinanceSource',
			editformclassname: 'swFinanceSourceEditWindow',
			border: false,
			autoLoadData: false,
			paging: false,
			stringfields: [
				{name: 'FinanceSource_id', type: 'int', header: 'ID', key: true},
				{name: 'FinanceSource_Code', header: 'Код', type: 'string', width: 120},
				{name: 'DrugFinance_Name', header: 'Бюджет', type: 'string', width: 240},
				{name: 'WhsDocumentCostItemType_Name', header: 'Статья расхода', type: 'string', id: 'autoexpand'},
				{name: 'BudgetFormType_Name', header: 'Целевая статья', type: 'string', width: 320},
				{name: 'FinanceSource_Name', header: 'Финансирование контракта', width: 320, renderer: addTooltip},
				{name: 'FinanceSource_begDate', header: 'Дата начала', type: 'date', width: 120},
				{name: 'FinanceSource_endDate', header: 'Дата окончания', type: 'date', width: 120}
			],
			actions: [
				{name:'action_add', handler: function(){this.openEditWindow('add',this.FinanceSourceGridPanel)}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openEditWindow('edit',this.FinanceSourceGridPanel)}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openEditWindow('view',this.FinanceSourceGridPanel)}.createDelegate(this)},
				{name:'action_delete'}
			]
		});

		this.PersonRegisterTypeGridPanel = new sw.Promed.ViewFrame({
			id: 'DDSW_PersonRegisterTypeGridPanel',
			dataUrl: '/?c=PersonRegister&m=loadPersonRegisterTypeGrid',
			border: false,
			autoLoadData: false,
			paging: false,
			stringfields: [
				{name: 'PersonRegisterType_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonRegisterType_Code', header: 'Код', type: 'int', width: 120},
				{name: 'PersonRegisterType_Name', header: 'Наименование', type: 'string', id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			]
		});

		this.TabPanel = new Ext.TabPanel({
			activeTab: 0,
			id: 'DDSW_TabPanel',
			layoutOnTabChange: true,
			region: 'center',
			items: [{
				id: 'DrugFinance',
				layout: 'fit',
				title: 'Источники финансирования',
				items: [this.DrugFinanceGridPanel]
			}, {
				id: 'BudgetFormType',
				layout: 'fit',
				title: 'Целевые статьи',
				items: [this.BudgetFormTypeGridPanel]
			}, {
				id: 'WhsDocumentCostItemType',
				layout: 'fit',
				title: 'Статьи расхода',
				items: [this.WhsDocumentCostItemTypeGridPanel]
			}, {
				id: 'FinanceSource',
				layout: 'fit',
				title: 'Финансирование контрактов',
				items: [this.FinanceSourceGridPanel]
			}, {
				id: 'PersonRegisterType',
				layout: 'fit',
				title: 'Регистры',
				items: [this.PersonRegisterTypeGridPanel]
			}],
			listeners:
			{
				tabchange: function(tab, panel) {

					switch(panel.id) {
						case 'DrugFinance':
							this.DrugFinanceGridPanel.loadData();
							break;
						case 'BudgetFormType':
							this.BudgetFormTypeGridPanel.loadData();
							break;
						case 'WhsDocumentCostItemType':
							this.WhsDocumentCostItemTypeGridPanel.loadData();
							break;
						case 'FinanceSource':
							this.FinanceSourceGridPanel.loadData();
							break;
						case 'PersonRegisterType':
							this.PersonRegisterTypeGridPanel.loadData();
							break;
					}
				}.createDelegate(this)
			}
		});

		Ext.apply(this, {
			items: [
				this.TabPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			]
		});

		sw.Promed.swDrugDocumentSprWindow.superclass.initComponent.apply(this, arguments);
	}
});
