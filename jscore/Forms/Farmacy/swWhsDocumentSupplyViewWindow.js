/**
* swWhsDocumentSupplyViewWindow - окно поточного ввода договоров (контрактов) о поставках.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      02.07.2012
*/
sw.Promed.swWhsDocumentSupplyViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'WhsDocumentSupplyViewWindow',
	title: lang['kontraktyi'], 
	layout: 'border',
	maximizable: false,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 1000,
	isDirector: function () {
		return isUserGroup('director');
	}, 
	setOrgValueById: function(combo, id) {
		var wnd = this;
		if (id > 0) {
			combo.setValue(id);
			Ext.Ajax.request({
				url: C_ORG_LIST,
				params: {
					Org_id: id
				},
				success: function(response){
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && result[0].Org_id && result[0].Org_Name) {
						wnd.setOrgValueByData(combo, {
							Org_id: result[0].Org_id,
							Org_Name: result[0].Org_Name
						});
					} else {
						combo.reset();
					}
				}
			});
		}
	},
	setOrgValueByData: function(combo, data) {
		combo.getStore().removeAll();
		combo.getStore().loadData([{
			Org_id: data.Org_id,
			Org_Name: data.Org_Name
		}]);
		combo.setValue(data[combo.valueField]);

		var index = combo.getStore().findBy(function(rec) { return rec.get(combo.valueField) == data[combo.valueField]; });

		if (index == -1) {
			return false;
		}

		var record = combo.getStore().getAt(index);
		combo.fireEvent('select', combo, record, 0)
	},
	setDefaultOrg: function() {
		this.OrgCCombo.enable();

		switch(this.ARMType) {
			case 'minzdravdlo': //АРМ специалиста минздрава
				this.setOrgValueById(this.OrgCCombo, getGlobalOptions().minzdrav_org_id);
				break;
			case 'adminllo': //АРМ Администратора ЛЛО
				this.setOrgValueById(this.OrgCCombo, getGlobalOptions().org_id);
				this.OrgCCombo.disable();
				break;
			case 'gku': //АРМ специалиста по закупкам:
				if (!Ext.isEmpty(getGlobalOptions().lpu_id) && getGlobalOptions().lpu_id > 0) {
					this.setOrgValueById(this.OrgCCombo, getGlobalOptions().org_id);
					this.OrgCCombo.disable();
				}
				break;
			case 'hn': //АРМ Главной медсестры
				this.setOrgValueById(this.OrgCCombo, getGlobalOptions().org_id);
				this.OrgCCombo.disable();
				break;
		}
		this.OrgCCombo.enable();
	},
	setDefaultDateRange: function() {
		var start_date = new Date();
		var month = start_date.getMonth();
		start_date.setDate(1);
		start_date.setMonth(month - (month%3));
		this.DocDateField.setValue(Ext.util.Format.date(start_date.clearTime(), 'd.m.Y')+' - '+Ext.util.Format.date(start_date.add(Date.MONTH, 3).clearTime(), 'd.m.Y'))
	},
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},	
	show: function() {
		sw.Promed.swWhsDocumentSupplyViewWindow.superclass.show.apply(this, arguments);
		this.onlyView = false;
		this.ARMType = null;

        if(arguments[0]) {
            if(arguments[0].onlyView){
                this.onlyView = true;
            }
			if(!Ext.isEmpty(arguments[0].ARMType)){
				this.ARMType = arguments[0].ARMType;
			}
        }

        this.SearchGrid.setReadOnly(this.onlyView);
		var viewframe = this.SearchGrid;
        if(!this.SearchGrid.getAction('import_contracts') && getGlobalOptions().region.nick == 'kz')
        {
        	this.SearchGrid.addActions({
				id: 'WDSVW_action_other',
				name:'action_other',
				text:lang['deystviya'],
				menu: [{
					text: 'Получить данные от СК Фармация',
					tooltip: 'Получить данные от СК Фармация',
					handler: function() {
						getWnd('swContractsImportWindow').show({
							callback: viewframe.refreshRecords,
							owner: viewframe
						});
					}.createDelegate(this)
				}]
			});
        }
		
		this.SearchGrid.addActions({
			name:'action_LoadContracts',
			text:'Импорт контрактов',
			iconCls: 'downdownarrow',
			hidden: !(this.isDirector() && getRegionNick() == 'ufa'),
			handler: function () {
				getWnd('swFarmContractImportWindow').show({
					callback: viewframe.refreshRecords,
					owner: viewframe 
				});
			}.createDelegate(this)
		});

		this.doReset();
		this.doSearch();

        //отображение колонок грида в зависимости от региона
        var region = getGlobalOptions().region.nick;
        this.SearchGrid.setColumnHidden('FinDocument_Sum', region != 'saratov');
        this.SearchGrid.setColumnHidden('RegistryDataRecept_Sum', region != 'saratov');
		this.SearchGrid.setColumnHidden('KBK', region != 'ufa');
	},
	doReset: function() {
		this.form.reset();
		this.setDefaultDateRange();
		this.setDefaultOrg();
	},
	doSearch: function() {
		var params = this.form.getValues();
		params.limit = 100;
		params.start =  0;
		params.Org_cid = this.OrgCCombo.getValue();
		params.Org_sid = this.OrgSCombo.getValue();

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
	initComponent: function() {
		var wnd = this;

		this.DocDateField = new Ext.form.DateRangeField({
			width: 200,
			fieldLabel: lang['data_kontrakta'],
			id: 'wdsvWhsDocumentUc_DateRange',
			name: 'WhsDocumentUc_DateRange',
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});

		this.OrgCCombo = new sw.Promed.SwOrgComboEx({
			fieldLabel : lang['zakazchik'],
			hiddenName: 'Org_cid',
			id: 'wdsvOrg_cid',
			width: 200,
			editable: true,
			allowBlank: true,
			tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
			emptyText: lang['vvedite_chast_nazvaniya'],
			onTriggerClick: function() {
				if (this.disabled) {
					return false;
				}
				var combo = this;

				if (!this.formList) {
					this.formList = new sw.Promed.swListSearchWindow({
						title: lang['poisk_organizatsii'],
						id: 'OrgSearch_' + this.id,
						object: 'Org',
						prefix: 'lswdsv',
						editformclassname: 'swOrgEditWindow',
						stringfields: [
							{name: 'Org_id',    type:'int'},
							{name: 'Org_Name',  type:'string'}
						],
						dataUrl: C_ORG_LIST
					});
				}
				this.formList.show({
					params: this.getStore().baseParams,
					onSelect: function(data) {
						wnd.setOrgValueByData(combo, data);
					}
				});
			}
		});
		this.OrgCCombo.getStore().proxy.conn.url = C_ORG_LIST;

		this.OrgSCombo = new sw.Promed.SwOrgComboEx({
			fieldLabel : lang['postavschik'],
			hiddenName: 'Org_sid',
			id: 'wdsvOrg_sid',
			width: 200,
			editable: true,
			allowBlank: true,
			tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
			emptyText: lang['vvedite_chast_nazvaniya'],
			onTriggerClick: function() {
				if (this.disabled) {
					return false;
				}
				var combo = this;

				if (!this.formList) {
					this.formList = new sw.Promed.swListSearchWindow({
						title: lang['poisk_organizatsii'],
						id: 'OrgSearch_' + this.id,
						object: 'Org',
						prefix: 'lswdsv',
						editformclassname: 'swOrgEditWindow',
						stringfields: [
							{name: 'Org_id',    type:'int'},
							{name: 'Org_Name',  type:'string'}
						],
						dataUrl: C_ORG_LIST
					});
				}
				this.formList.show({
					params: this.getStore().baseParams,
					onSelect: function(data) {
						wnd.setOrgValueByData(combo, data);
					}
				});
			}
		});
		this.OrgSCombo.getStore().proxy.conn.url = C_ORG_LIST;

		this.FilterFormPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					items: [this.DocDateField]
				}, {
					layout: 'form',
					items: [this.OrgCCombo]
				}, {
					layout: 'form',
					items: [this.OrgSCombo]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						fieldLabel: lang['status'],
						tabIndex: wnd.firstTabIndex + 10,
						hiddenName: 'WhsDocumentStatusType_id',
						id: 'wdsvWhsDocumentStatusType_id',
						xtype: 'swcommonsprcombo',
						sortField:'WhsDocumentStatusType_Code',
						comboSubject: 'WhsDocumentStatusType',
						width: 200,
						allowBlank: true
					}]
				}, {
					layout: 'form',
					items: [{
						disabled: false,
						fieldLabel: lang['nomer_kontrakta'],
						id: 'wdsvWhsDocumentUc_Num',
						name: 'WhsDocumentUc_Num',
						width: 200,
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					items: [{
						hiddenName: 'BudgetFormType_id',
						fieldLabel: lang['tselevaya_statya'],
						xtype: 'swcommonsprcombo',
						comboSubject: 'BudgetFormType',
						width: 200
					}]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						fieldLabel: lang['istochnik_finansirovaniya'],
						tabIndex: wnd.firstTabIndex + 10,
						hiddenName: 'DrugFinance_id',
						id: 'wdsvDrugFinance_id',
						xtype: 'swcommonsprcombo',
						sortField:'DrugFinance_Code',
						comboSubject: 'DrugFinance',
						width: 200,
						allowBlank: true
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: lang['statya_rashodov'],
						hiddenName: 'WhsDocumentCostItemType_id',
						id: 'wdsvWhsDocumentCostItemType_id',
						xtype: 'swcommonsprcombo',
						sortField:'WhsDocumentCostItemType_Code',
						comboSubject: 'WhsDocumentCostItemType',
						width: 200,
						allowBlank: true
					}]
				}, {
					layout: 'form',
					items: [{
						hiddenName: 'WhsDocumentPurchType_id',
						fieldLabel: lang['vid_zakupa'],
						xtype: 'swcommonsprcombo',
						comboSubject: 'WhsDocumentPurchType',
						width: 200
					}]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						fieldLabel: lang['istochnik_oplaty'],
						tabIndex: wnd.firstTabIndex + 10,
						hiddenName: 'FinanceSource_id',
						id: 'wdsvFinanceSource_id',
						xtype: 'swcommonsprcombo',
						sortField:'FinanceSource_id',
						comboSubject: 'FinanceSource',
						width: 200,
						allowBlank: true
					}]
				}, {
					layout: 'form',
					hidden: getRegionNick() != 'ufa',
					items: [{
						fieldLabel: 'КБК',
						name: 'WhsDocumentUc_KBK',
						width: 200,
						xtype: 'textfield'
					}]
				}]
			}]
		});
		
		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
							wnd.doSearch();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: this,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterFormPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentSupply&m=loadList',
			height: 180,
			object: 'WhsDocumentSupply',
			editformclassname: 'swWhsDocumentSupplyEditWindow',
			id: 'WhsDocumentSupplyGrid',
			paging: true,
			root: 'data',
			cls: 'txtwrap',
			totalProperty: 'totalCount',
			actions: [
				{name: 'action_add', handler: function() {
					getWnd('swSelectWhsDocumentTypeWindow').show({
						onSelect: function() {
							if (arguments[0] && arguments[0].WhsDocumentType_id) {
								var viewframe = wnd.SearchGrid;
								getWnd(viewframe.editformclassname).show({
									WhsDocumentType_id: arguments[0].WhsDocumentType_id,
									WhsDocumentType_Name: arguments[0].WhsDocumentType_Name,
									callback: viewframe.refreshRecords,
									owner: viewframe,
									action: 'add'
								});
							}
						}
					});
				}},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=WhsDocumentSupply&m=delete'},
				{name: 'action_print'}
			],
			onRowSelect: function(sm,index,record) {
				if (this.readOnly || record.get('WhsDocumentStatusType_id') == 2) {
					this.setActionDisabled('action_delete', true);
				} else {
    				this.setActionDisabled('action_delete', false);
				}
				if (getRegionNick() == 'ufa') {
					this.setActionDisabled('action_edit', record.get('isImport') == 2);
					if (!(this.readOnly || record.get('WhsDocumentStatusType_id') == 2))
						this.setActionDisabled('action_delete', record.get('isImport') == 2);
				}
			},
			stringfields: [
				{ name: 'WhsDocumentSupply_id', type: 'int', header: 'ID', key: true },
				{ name: 'WhsDocumentStatusType_Name', type: 'string', header: 'Статус', width: 90 },
				{ name: 'WhsDocumentStatusType_id', type: 'int', header: '', hidden: true },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: '№ контракта', width:220 }, 
				{ name: 'ActualDateRange', type: 'string', header: 'Период действия контракта', width: 150 },
				{ name: 'WhsDocumentUc_Sum', type: 'money', header: 'Сумма', width: 95 },
				{ name: 'RegistryDataRecept_Sum', type: 'money', header: 'Отгружено', width: 95 },
				{ name: 'FinDocument_Sum', type: 'money', header: 'Оплачено', width: 95 },
				{ name: 'GraphLink', header: 'График поставки', width: 125, renderer: function(v, p, record)	{ if(!v) { return ""; }	return '<a href="javascript:getWnd(\'swWhsDocumentDeliveryGraphViewWindow\').show({WhsDocumentSupply_id: '+v+'});" style="cursor: pointer; color: #0000EE;">график поставки</a>'; }	},
				{ name: 'DrugFinanceSource_Name', type: 'string', header: lang['finansirovanie'], width: 125 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['statya_rashodov'], width: 125 },
				{ name: 'BudgetFormType_Name', type: 'string', header: lang['tselevaya_statya'], width: 125 },
				{ name: 'CommercialOffer_id', type: 'int', header: '', hidden: true, isparams: true},
				{ name: 'ProtInf', type: 'string', header: lang['protokol'], width: 175 },
				{ name: 'WhsDocumentProcurementRequest_Name', type: 'string', header: lang['lot'], width: 150 },
                { name: 'DrugRequest_Name', type: 'string', header: lang['zayavka'], width: 150 },
				{ name: 'Org_sid_Nick', type: 'string', header: lang['postavschik'], width: 100, id: 'autoexpand' },
				{ name: 'WhsDocumentSupply_KBK', type: 'string', header: 'КБК', width: 120, hidden: getRegionNick() != 'ufa'},
				{ name: 'isImport', type: 'int', header: 'isImport', hidden: true },
				{ name: 'WhsDocumentType_id', hidden: true, isparams: true },
				{ name: 'WhsDocumentType_Name', hidden: true, isparams: true }
			],
			title: null,
			toolbar: true
		});
		
		Ext.apply(this, {
			layout: 'border',
			defaults: {split: true},
			buttons: 
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: 
			[
				this.FilterPanel,
				this.SearchGrid
			]
		});
		sw.Promed.swWhsDocumentSupplyViewWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.FilterPanel.getForm();
	}
});