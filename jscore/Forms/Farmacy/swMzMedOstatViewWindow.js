/**
* swMzMedOstatViewWindow - просмотр отстатков медикаментов (дополненая версия swMedOstatViewWindow).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov Rustam
* @version      05.03.2014
*/
/*NO PARSE JSON*/
sw.Promed.swMzMedOstatViewWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMzMedOstatViewWindow',
	objectSrc: '/jscore/Forms/Farmacy/swMzMedOstatViewWindow.js',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doReset: function() {
		var wnd = this;
		var form = this.FilterPanel.getForm();

		form.reset();

		wnd.Contragent_id = getGlobalOptions().Contragent_id ? getGlobalOptions().Contragent_id : null;
		wnd.Mol_id = null;
		wnd.findById('mmovwContragent_id').setValue(null);
		wnd.findById('mmovwDrugFinance_id').setValue(null);
		wnd.findById('mmovwDrug_id').setValue(null);
		wnd.loadContragent('mmovwContragent_id', null, function() {
			wnd.loadSprMol('mmovwMol_id','mmovwContragent_id');			
		}.createDelegate(wnd));
		this.doSearch();
	},
	doSearch: function() {
		var params = this.FilterPanel.getForm().getValues();
		params.start = 0;
		params.limit = 100;
		this.Contragent_id = this.findById('mmovwContragent_id').getValue(); //для передачи в параметры, при формировании документов списания/передачи
		this.Mol_id = null;
		if (!this.Contragent_id || this.Contragent_id == '') this.Contragent_id = 0;
		this.findById('MzMedOstatViewGrid').loadData({globalFilters: params});
	},
	draggable: true,
	height: 550,
	id: 'MzMedOstatViewWindow',
	Contragent_id: null,
	Mol_id: null,
	initComponent: function() {
		var form = this;

		//Вкладка "Общие"
		form.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [{
				width:500,
				allowBlank: false,
				fieldLabel: lang['kontragent'],
				xtype: 'swcontragentcombo',
				tabIndex: TABINDEX_DPREW + 1,
				id: 'mmovwContragent_id',
				name: 'Contragent_id',
				hiddenName:'Contragent_id',
				listeners: {
					change: function(combo) {
						this.findById('mmovwMol_id').setDisabled(!(combo.getValue()>0));
						if ((combo.getValue()>0) && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
							this.findById('mmovwMol_id').enable();
							this.setFilterMol(this.findById('mmovwMol_id'), combo.getValue());
						} else {
							this.findById('mmovwMol_id').disable();
							this.findById('mmovwMol_id').setValue(null);
						}
						//обновляем комбо Drug_id
						var form = this.FilterPanel;
						var base_form = form.getForm();
						base_form.findField('Drug_id').getStore().removeAll();
						base_form.findField('Drug_id').getStore().load({
							params: {
								mode: form.documentUcStrMode,
								Contragent_id: form.Contragent_id
							}
						});
						// Отключаю фильтрацию справочника DrugFinance, потому что она на больших данных просто не работает, поскольку остатки долго получаются / #59048
						/*
						Ext.Ajax.request({
							url: '/?c=Farmacy&m=loadDrugFinanceList',
							callback: function(opt, success, response) {
								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									var DrugFinanceList = new Array;

									for (var i=0; i < response_obj.length; i++) {
										DrugFinanceList.push(response_obj[i].DrugFinance_id);
									}

									if (Ext.isArray(response_obj) && response_obj) {
										this.findById('mmovwDrugFinance_id').getStore().filterBy(function(record) {
											if (record.get('DrugFinance_id').inlist(DrugFinanceList)) {
												return true;
											} else {return false;}
										});
									}
									this.findById('mmovwDrugFinance_id').setValue('');
								}
							}.createDelegate(this),
							failure: function() {
								sw.swMsg.alert(lang['vnimanie'], lang['ne_udalos_zagruzit_spisok_istochnikov_finansirovaniya_kontragenta']);
							},
							headers: { },
							params: {Contragent_id: combo.getFieldValue('Contragent_id')}
						});
						*/
					}.createDelegate(this)
				}
			}, {
				allowBlank: true,
				width:500,
				fieldLabel: lang['mol'],
				hidden: true,
				hiddenName: 'Mol_id',
				id: 'mmovwMol_id',
				lastQuery: '',
				linkedElements: [ ],
				tabIndex: TABINDEX_DPREW + 2,
				xtype: 'swmolcombo',
				hidelabel: true
			}, {
				fieldLabel: lang['istochnik_finans'],
				allowBlank: true,
				hiddenName: 'DrugFinance_id',
				id: 'mmovwDrugFinance_id',
				tabIndex: TABINDEX_DPREW + 3,
				width: 500,
				xtype: 'swdrugfinancecombo'
			}, {
				fieldLabel: lang['statya_rashodov'],
				allowBlank: true,
				hiddenName: 'WhsDocumentCostItemType_id',
				tabIndex: TABINDEX_DPREW + 3,
				width: 500,
				xtype: 'swwhsdocumentcostitemtypecombo'
			}, {
				allowBlank: true,
				displayField: 'Drug_FullName',
				enableKeyEvents: true,
				fieldLabel: lang['medikament'],
				forceSelection: true,
				hiddenName: 'Drug_id',
				id: 'mmovwDrug_id',
				loadingText: lang['idet_poisk'],
				minChars: 1,
				minLength: 1,
				minLengthText: lang['pole_doljno_byit_zapolneno'],
				mode: 'remote',
				resizable: true,
				selectOnFocus: true,
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'Drug_id',
							sortInfo: {
								field: 'Drug_FullName'
							}
						},
						[
							{ name: 'Drug_Fas', mapping: 'Drug_Fas' },
							{ name: 'Drug_id', mapping: 'Drug_id' },
							{ name: 'Drug_Name', mapping: 'Drug_Name' },
							{ name: 'Drug_FullName', mapping: 'Drug_FullName' },
							{ name: 'DrugMnn_id', mapping: 'DrugMnn_id' },
							{ name: 'DrugForm_Name', mapping: 'DrugForm_Name' },
							{ name: 'DrugUnit_Name', mapping: 'DrugUnit_Name' }
						]),
					url: '/?c=Farmacy&m=loadDrugList',
					listeners: {
						beforeload: function(store) {
							store.baseParams.Contragent_id = Ext.getCmp('mmovwContragent_id').getValue();
						}
					}
				}),
				tabIndex: TABINDEX_DPREW + 4,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<h3>{Drug_FullName}&nbsp;</h3>',
					'</div></tpl>'
				),
				triggerAction: 'all',
				valueField: 'Drug_id',
				width: 500,
				xtype: 'combo'
			}]
		});

		//Вкладка "Классификация"
		form.FilterClassPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [{
				xtype: 'swrlsclspharmagroupremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: lang['farmgruppa'],
				hiddenName: 'CLSPHARMAGROUP_ID'
			}, {
				xtype: 'swrlsclsatcremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: lang['ath'],
				hiddenName: 'CLSATC_ID'
			}, {
				xtype: 'swrlsclsmzphgroupremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: lang['ftg'],
				hiddenName: 'CLS_MZ_PHGROUP_ID'
			}, {
				xtype: 'swrlsstronggroupscombo',
				fieldLabel: lang['silnodeystvuyuschie'],
				hiddenName: 'STRONGGROUPS_ID'
			}, {
				xtype: 'swrlsnarcogroupscombo',
				fieldLabel: lang['narkoticheskie'],
				hiddenName: 'NARCOGROUPS_ID'
			}]
		});


		form.FilterTabs = new Ext.TabPanel({
			autoScroll: true,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'north',
			enableTabScroll: true,
			height: 170,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[{
				title: lang['obschie'],
				layout: 'fit',
				border:false,
				items: [form.FilterCommonPanel]
			}, {
				title: lang['klassifikatsiya'],
				layout: 'fit',
				border:false,
				items: [form.FilterClassPanel]
			}]
		});

		form.FilterButtonsPanel = new sw.Promed.Panel({
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
						id: 'mmovw_SearchButton',
						text: lang['sformirovat'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							form.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mmovw_ClearButton',
						text: lang['ochistit'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							form.doReset();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mmovw_CheckAllButton',
						text: lang['otmetit_vse'],
						minWidth: 100,
						handler: function() {
							Ext.getCmp('MzMedOstatViewGrid').selectAllOstat();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		form.FilterPanel = getBaseFiltersFrame({
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: form,
			toolBar: form.WindowToolbar,
			items: [
				form.FilterTabs,
				form.FilterButtonsPanel
			]
		});
		
		var sf = [
			{ name: 'row_id', type: 'int', header: 'ID', key: true },
			{ name: 'Drug_id', type: 'int', hidden: true},		
			{ name: 'DocumentUcStr_id', type: 'int', hidden: true},
			{ header: 'Медикамент',  type: 'string', name: 'Drug_Name', id: 'autoexpand', width: 100 },
			{ header: 'Срок годности',  type: 'date', name: 'godnDate', width: 100 },
			{ header: 'Ед. учета',  type: 'string', name: 'unit', width: 100 }
		];
		
		if (isFarmacyInterface) {
			sf.push({name: 'Price', width: 110, header: 'Цена (опт, без НДС)', type: 'money', align: 'right'});
			sf.push({name: 'PriceR', width: 110, header: 'Цена (розн, с НДС)', type: 'money', align: 'right'});
		} else  {
			sf.push({name: 'PriceR', width: 110, header: 'Цена', type: 'money', align: 'right'});
		}
		sf.push({header: 'Остаток',  type: 'float', name: 'ostat', width: 70, align: 'right'});
		if (isFarmacyInterface) {
			sf.push({name: 'Sum', width: 110, header: 'Сумма (опт, без НДС)', type: 'money', align: 'right'});
			sf.push({name: 'SumR', width: 110, header: 'Сумма (розн, с НДС)', type: 'money', align: 'right'});
		} else  {
			sf.push({name: 'SumR', width: 110, header: 'Сумма', type: 'money', align: 'right'});
		}
		sf.push({ header: 'Количество',  type: 'int', name: 'quantity', width: 100, align: 'right', editor: new Ext.form.NumberField({ allowBlank: true, allowDecimals: false, allowNegative: false, minValue: 0 }) });
		sf.push({ name: 'DrugFinance_id', hidden: true, type: 'int' });
		sf.push({ name: 'DrugFinance_Name', header: 'Источник финансирования', type: 'string', width: 120 });
		sf.push({ name: 'WhsDocumentCostItemType_id', hidden: true, type: 'int' });
		sf.push({ name: 'WhsDocumentCostItemType_Name', header: 'Статья расходов', type: 'string', width: 120 });
		
		this.MzMedOstatViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', disabled: true },
				{ name: 'action_view', disabled: true },
				{ name: 'action_delete', disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '?c=FarmacyDrugOstat&m=loadDrugOstatByFilters',
			focusOn: {
				name: 'mmovw_SearchButton',
				type: 'field'
			},
			focusPrev: {
				name: 'mmovw_OstatDate',
				type: 'field'
			},
			id: 'MzMedOstatViewGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: sf,
			toolbar: false,
			totalProperty: 'totalCount',
			onAfterEditSelf: function(o) { // количество нельзя ввести больше остатка				
				var ostat = o.record.data.ostat;
				var val = o.value;
				o.grid.stopEditing(true);
				if (val > ostat) val = ostat;
				//val = Math.round(val);
				if (val != o.value) {
					o.record.set('quantity', val);
					o.record.commit();
				}
			},
			selectAllOstat: function() {
				this.getGrid().getStore().each(function(r) {
					r.set('quantity', r.data.ostat);
					r.commit();
				});
			},
			getSelectedOstat: function() {
				var res = new Array();
				this.getGrid().getStore().each(function(r) {
					if(r.data.quantity != '' && r.data.quantity > 0){
						if((res.length == 0) || (res[0].DrugFinance_id == r.data.DrugFinance_id && res[0].WhsDocumentCostItemType_id == r.data.WhsDocumentCostItemType_id)){
							res.push({
								DocumentUcStr_id: r.data.DocumentUcStr_id, 
								quantity: r.data.quantity, 
								DrugFinance_id:r.data.DrugFinance_id, 
								WhsDocumentCostItemType_id:r.data.WhsDocumentCostItemType_id });
						} else {
							Ext.Msg.alert('Ошибка', 'В один документ можно добавлять медикаменты только с одинаковым источником финансирования и статьей расходов!');
							return (res = 'error');
						}
					}
				});
				return res;
			}
		});
	
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					var current_window = this.ownerCt;
					current_window.createDocument('DocSpis');
				},
				id: 'mmovw_CheckAllButton',
				tabIndex: TABINDEX_DPREW + 24,
				text: lang['spisat']
			}, {
				handler: function() {
					var current_window = this.ownerCt;
					current_window.createDocument('DocUc');
				},
				id: 'mmovw_CheckAllButton',
				tabIndex: TABINDEX_DPREW + 25,
				text: lang['peredat']
			}, {
				text: '-'
			}, {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					Ext.getCmp('mmovw_SearchButton').focus();
				},
				onTabAction: function() {
					var current_window = this.ownerCt;
					//current_window.findById('MzMedOstatViewForm').getForm().findField('OstatDate').focus(true, 200);
				},
				tabIndex: TABINDEX_DPREW + 26,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.FilterPanel,
				//form.MedOstatFormPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items: 
					[
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [form.MzMedOstatViewFrame]
						}
					]
				}
			]
		});
		sw.Promed.swMzMedOstatViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('MzMedOstatViewWindow');
			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;				
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C ],
		stopEvent: true
	}],
	layout: 'border',
	maximized: true,
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	listeners: {
		'hide': function() {
			Ext.getCmp('MzMedOstatViewWindow').findById('MzMedOstatViewGrid').removeAll();
		}
	},
	plain: true,
	resizable: true,
	title: lang['ostatki_medikamentov'],
	width: 900,
	show: function() {
		sw.Promed.swMzMedOstatViewWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		var base_form = current_window.FilterPanel.getForm();
		current_window.restore();
		current_window.center();
		current_window.maximize();
		current_window.doReset();
		base_form.findField('mmovwMol_id').hideContainer();
		base_form.findField('Contragent_id').getStore().baseParams.mode = 'med_ost';
		this.Contragent_id = getGlobalOptions().Contragent_id ? getGlobalOptions().Contragent_id : null;
		
		current_window.loadContragent('mmovwContragent_id', null/*{mode:'med_ost'}*/, function() {
			current_window.loadSprMol('mmovwMol_id','mmovwContragent_id');			
		}.createDelegate(current_window));
		
		base_form.findField('Drug_id').getStore().removeAll();

		this.FilterTabs.setActiveTab(1);
		this.FilterTabs.setActiveTab(0);

		base_form.findField('CLSATC_ID').getStore().load({params: {maxCodeLength: 5}});
		base_form.findField('CLSPHARMAGROUP_ID').getStore().load();
		base_form.findField('CLS_MZ_PHGROUP_ID').getStore().load();
	},
	loadContragent: function(comboId, params, callback) {
		var combo = this.findById(comboId);
		var value = combo.getValue() > 0 ? combo.getValue() : (this.Contragent_id ? this.Contragent_id : null);
		var form = this.FilterPanel;
		var base_form = form.getForm();
		combo.getStore().load({
			params: params,
			callback: function() {
				combo.setValue(value);
				combo.fireEvent('change', combo);				
				base_form.findField('Drug_id').getStore().load({
					params: {
						mode: form.documentUcStrMode,
						Contragent_id: form.Contragent_id						
					}
				});
				if (callback) {
					callback();
				}
			}.createDelegate(this)
		});
	},
	loadSprMol: function(comboId, contragentId) {
		var form = this;
		form.findById(comboId).getStore().load( {
			callback: function() {
				form.findById(comboId).setValue(form.findById(comboId).getValue());
				form.setFilterMol(form.findById(comboId), form.findById(contragentId).getValue());
			}
		});
	},
	setFilterMol: function(combo, Contragent_id) {
		// Устанавливаем фильтр и если по условиям фильтра найдена только одна запись - то устанавливаем эту запись 
		form = this;
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		var co = 0;
		var Mol_id = null;
		if (co==1) {
			combo.setValue(Mol_id);
		} else {
			combo.setValue(null);			
		}
	},
	createDocument: function(doc_type) {
		var ostat_array = Ext.getCmp('MzMedOstatViewGrid').getSelectedOstat();
		var current_window = this;
		var err_msg = '';
		if (ostat_array == 'error') return false;
		if (ostat_array.length <= 0) {
			if (doc_type == 'DocSpis')
				err_msg = lang['spisanie_nevozmojno_t_k_ne_zapolneno_kol-vo_spisyivaemogo_preparata'];
			else
				err_msg = lang['peredacha_nevozmojna_t_k_ne_zapolneno_kol-vo_peredavaemogo_preparata'];
		}

		if (err_msg == '') {
			if (doc_type == 'DocSpis') {
				getWnd('swDokCreateSpisWindow').show({
					Contragent_id: current_window.Contragent_id,
					Mol_id: current_window.Mol_id,
					save_data: ostat_array,
					callback: function() {
						Ext.getCmp('MzMedOstatViewGrid').refreshRecords(null,0);
					}
				});
			}
			if (doc_type == 'DocUc') {
				getWnd('swDokCreateUcWindow').show({
					Contragent_id: current_window.Contragent_id,
					Mol_id: current_window.Mol_id,
					save_data: ostat_array,
					callback: function() {
						Ext.getCmp('MzMedOstatViewGrid').refreshRecords(null,0);
					}
				});
			}
		} else {
			sw.swMsg.alert(lang['oshibka'], err_msg);
		};
	}
});