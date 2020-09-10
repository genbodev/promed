Ext6.define('common.swOrgSearchWindow', {
	alias: 'widget.swOrgSearchWindowExt6',
	addCodeRefresh: Ext6.emptyFn,
	closeToolText: 'Закрыть',
	title: 'Поиск организации',
	maximized: false,
	width: 890,
	height: 590,
	layout: 'border',
	modal: true,
	closable: true,
	cls: 'arm-window-new emk-forms-window org-search-window',
	extend: 'base.BaseForm',
	renderTo: main_center_panel.body.dom,
	onOrgSelect: Ext6.emptyFn,
	disableAllActions: function(disable) {
		var win = this;
		if ( (disable === true) || (disable == undefined) ) {
			//~ this.SearchGrid.setReadOnly(true);
			win.queryById('btnAdd').hide();
			win.queryById('btnEdit').hide();
		}
		else {
			//~ this.SearchGrid.setReadOnly(false);
			win.queryById('btnAdd').show();
			win.queryById('btnEdit').show();
		}
	},
	doSearch: function() {
		var win = this,
			orgTypeId = win.queryById('OrgType_id').getValue('OrgType_id'),
			orgName = win.grid.getHeaderFilterField('Org_Name').getValue(),
			orgNick = win.grid.getHeaderFilterField('Org_Nick').getValue();

		win.grid.getStore().load({params:{
			OrgType_id: orgTypeId,
			OrgType: win.obj,
			Org_Name: orgName,
			Org_Nick: orgNick,
			onlyFromDictionary: win.onlyFromDictionary,

			// #15304 Если задано в настройках окна, загружаем только организации, не работающие в системе:
			isNotForSystem: (win.isNotForSystem ? 1 : null) 
		}});
	},
	onOKbutton: function() {
		var win = this,
			rec = win.grid.getSelectionModel().getSelected();

		if ( rec && rec.length>0 ) {
			rec = rec.getAt(0);
			win.onOrgSelect({
				Org_id: rec.get('Org_id') || null,
				OrgType_id: rec.get('OrgType_id') || null,
				OrgSMO_id: rec.get('OrgSMO_id') || null,
				OrgAnatom_id: rec.get('OrgAnatom_id') || null,
				Org_pid: rec.get('Org_pid') || null,
				Lpu_id: rec.get('Lpu_id') || null,
				Org_Nick: rec.get('Org_Nick') || null,
				Org_Name: rec.get('Org_Name') || null,
				Org_StickNick: rec.get('Org_StickNick') || null,
				Org_begDate: rec.get('Org_begDate') || null,
				Org_endDate: rec.get('Org_endDate') || null,
				OrgStac_Code: rec.get('OrgStac_Code') || null,
				OrgType_SysNick: rec.get('OrgType_SysNick') || null
			});

			win.hide();
		}
	},
	show: function() {
		var win = this;
		win.callParent(arguments);
		
		win.obj = 'org';
		win.OMSSprTerr_Code = 0;
		win.onOrgSelect = Ext6.emptyFn;
		win.onWinClose = Ext6.emptyFn;
		win.enableOrgType = false;
		win.defaultOrgType = null;
		win.DepartAffilType_id = null;
		win.DispClass_id = null;
		win.Disp_consDate = null;
		win.KLRgn_id = null;
		win.onlyFromDictionary = false;
        win.allowEmptyUAddress = '1';
        win.disableEdit = false;
		
		win.grid.resetHeaderFilters();//~ win.doReset();
		
		win.onDate = getGlobalOptions().date;

		win.queryById('OrgType_id').clearValue();//~ base_form.reset();
		
		if ( arguments[0] ) {
			if ( arguments[0].onSelect ) {
				win.onOrgSelect = arguments[0].onSelect;
			}

			if ( arguments[0].onDate ) {
				if ( typeof arguments[0].onDate == 'object' ) {
					win.onDate = Ext.util.Format.date(arguments[0].onDate, 'd.m.Y');
				} else {
					win.onDate = arguments[0].onDate;
				}
			}
			
			if ( arguments[0].DispClass_id ) {
				win.DispClass_id = arguments[0].DispClass_id;
			}
			
			if ( arguments[0].Disp_consDate ) {
				win.Disp_consDate = arguments[0].Disp_consDate;
			}

			if ( arguments[0].onClose ) {
				win.onWinClose = arguments[0].onClose;
			}

			if ( arguments[0].disableEdit ) {
				win.disableEdit = arguments[0].disableEdit;
			}
			
			if ( arguments[0].DepartAffilType_id ) {
				win.DepartAffilType_id = arguments[0].DepartAffilType_id;
			}
			
			if ( arguments[0].KLRgn_id ) {
				win.KLRgn_id = arguments[0].KLRgn_id;
			}

			if (( arguments[0].object ) && (arguments[0].object!='Org_Served')) {
				win.obj = arguments[0].object;
			}

			if ( arguments[0].OMSSprTerr_Code ) {
				win.OMSSprTerr_Code = arguments[0].OMSSprTerr_Code;
			}

			if ( arguments[0].object && arguments[0].object.inlist(['Org_Served','org','Org']) ){
				win.enableOrgType = true;
			}

			if ( arguments[0].enableOrgType ){
				win.enableOrgType = arguments[0].enableOrgType;
			}
			
			if ( arguments[0].defaultOrgType ){
				win.defaultOrgType = arguments[0].defaultOrgType;
			}


			if ( arguments[0].onlyFromDictionary ) {
				win.onlyFromDictionary = arguments[0].onlyFromDictionary;
			}

            if ( arguments[0].showOrgStacFilters ){
                win.showOrgStacFilters = arguments[0].showOrgStacFilters;
            } else {
                win.showOrgStacFilters = null;
            }

            if ( arguments[0].allowEmptyUAddress ) {
                win.allowEmptyUAddress = arguments[0].allowEmptyUAddress;
            }

			this.isNotForSystem = arguments[0].isNotForSystem || false;

			if (this.isNotForSystem) {
				this.enableOrgType = false;
			}
		}

		var org_type_combo = win.queryById('OrgType_id');

		// ставим доступность поля "Тип организации"
		org_type_combo.setDisabled(!win.enableOrgType);

		org_type_combo.getStore().load({
			callback: function (records, operation, success) {
				if ( arguments[0] && arguments[0].OrgType_id ) {
					org_type_combo.setValue(arguments[0].OrgType_id);
				}

				if(win.defaultOrgType){
					org_type_combo.setValue(win.defaultOrgType);
				}
				switch ( win.obj ) {
					case 'anatom':
						//~ win.setTitle(WND_SEARCH_ORGANATOM);
						break;

					case 'lpu':
						//~ win.setTitle(WND_SEARCH_LPU);

						win.grid.getColumnManager().getHeaderByDataIndex('Lpu_f003mcod').setHidden(true);//~ win.grid.getColumnModel().setHidden(win.grid.getColumnModel().findColumnIndex('Lpu_f003mcod'), false);

						org_type_combo.setFieldValue('OrgType_Code',11);
						break;

					case 'bank':
						//~ win.setTitle(WND_SEARCH_BANK);
						win.SearchGrid.setActionDisabled('action_add', true);
						org_type_combo.setFieldValue('OrgType_Code',2);
						break;

					case 'military':
						//~ win.setTitle(WND_SEARCH_MILITARY);
						org_type_combo.setFieldValue('OrgType_Code',15);
						break;

					case 'rjd':
						org_type_combo.setFieldValue('OrgType_Code',22);
						break;

					default:
						//~ win.setTitle(WND_SEARCH_ORG);
						break;
				}
			},
			scope: this
		});

		if ( win.obj != 'smo' ) {
			win.disableAllActions(false);
		}
		else {
			win.disableAllActions();
		}
		
											//~ win.SearchGrid.setActionDisabled('action_add', false);
		win.queryById('btnAdd').enable();	//~ win.grid.getTopToolbar().items.items[0].enable();
		
		win.grid.getColumnManager().getHeaderByDataIndex('Lpu_f003mcod').setHidden(true);//~ win.grid.getColumnModel().setHidden(win.grid.getColumnModel().findColumnIndex('Lpu_f003mcod'), true);

		win.grid.getColumnManager().getHeaderByDataIndex('OrgStac_Code').setHidden(!win.showOrgStacFilters);
		
		//~ win.showOrgStacFilters?base_form.findField('WithOrgStacCode').show(true):base_form.findField('WithOrgStacCode').hide(true); //?

		if (win.disableEdit) {
			//~ win.SearchGrid.setReadOnly(true);
			win.queryById('btnAdd').hide();//~ win.SearchGrid.setActionHidden('action_add', true);
			win.queryById('btnEdit').hide();//~ win.SearchGrid.setActionHidden('action_edit', true);
		} else {
			win.queryById('btnAdd').show();//~ win.SearchGrid.setActionHidden('action_add', false);
			win.queryById('btnEdit').show();//~ win.SearchGrid.setActionHidden('action_edit', false);
		}
	},
	initComponent: function() {
		var win = this;
		
		win.grid = new Ext6.grid.Panel({
			autoLoad: false,
			xtype: 'grid',
			cls: 'EmkGrid',
			region: 'center',
			disableSelection: false,
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				}),
				Ext6.create('Ext6.ux.GridHeaderFilters', {
					enableTooltip: false,
					reloadOnChange: false
				})
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						win.queryById('btnEdit').enable();
						win.queryById('btnOK').enable();
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					win.onOKbutton();
				}
			},
			dockedItems: [
				win.toolbar = Ext6.create('Ext6.toolbar.Toolbar', {
					cls: 'grid-toolbar',
					width: '100%',
					border: false,
					defaults: {
						margin: '0 4 0 0',
						padding: '4 10'
					},
					items:[{
							xtype: 'commonSprCombo',
							userCls: 'org-type-combo',
							comboSubject: 'OrgType',
							name: 'OrgType_id',
							itemId: 'OrgType_id',
							//~ lastQuery: '',
							valueField: 'OrgType_id',
							sortField: 'OrgType_id',
							width: 340,
							labelWidth: 110,
							fieldLabel: langs('Тип организации'),
							queryMode: 'local',
							minChars: 2,
							forceSelection: true,
							listeners: {
								change: function(combo, newVal, oldVal){
									var sel = win.queryById('OrgType_id').getSelection();
									var orgtype_id = null;
									//~ win.grid.getStore().removeFilter('OrgTypeFilter');
									if(sel) {
										orgtype_id = sel.get('OrgType_id');
										if(orgtype_id) {
											//~ win.grid.getStore().addFilter({
												//~ id: 'OrgTypeFilter',
												//~ property: 'OrgType_id',
												//~ value: orgtype_id
											//~ });
											win.doSearch();
										}
									}
								}
							}
						}, '->', {
							xtype: 'button',
							text: langs('Добавить'),
							itemId: 'btnAdd',
							iconCls: 'action_add',
							handler: function() {
								if(!(getGlobalOptions().enable_action_reference_by_admref_group) || isUserGroup('AdminOrgReference') ){
									if ( win.obj == 'bank' )
										return;
									var obj = win.obj;
									getWnd('swOrgEditWindow').show({
										action: 'add',
										callback: function(Org_id) {
											win.doSearch();//~ win.grid.getStore().load();
											if (Org_id) {
												win.onOrgSelect({Org_id: Org_id});
											}
										},
										orgType: obj,
										allowEmptyUAddress: win.allowEmptyUAddress
									});
								}
								else{
									Ext6.Msg.alert(langs('Ошибка'), langs('Для добавления, редактирования или удаления организации обратитесь к Администратору справочника организаций. Контактная информация: ')+getGlobalOptions().contact_info);
									return false;
								}
							}
						}, {
							xtype: 'button',
							text: langs('Редактировать'),
							itemId: 'btnEdit',
							iconCls: 'action_edit',
							handler: function() {
								if(!(getGlobalOptions().enable_action_reference_by_admref_group) || isUserGroup('AdminOrgReference') ){
									if ( win.obj == 'bank'||win.obj == 'lpu' )
										return;
									var record = win.grid.getSelectionModel().getSelected().getAt(0);
									if ( Ext6.isEmpty(record) )
										return;
									var obj = win.obj;
									var orgIdField = 'Org_pid';

									if (( record.get('Server_id') && record.get('Server_id') == 0 && !isSuperAdmin()) || !Ext.isEmpty(record.get('OrgStac_Code')))
										return;

									if ( obj.inlist([ 'orgstac', 'orgstaceducation', 'lpu' ]) ) {
										orgIdField = 'Org_id';
									}

									getWnd('swOrgEditWindow').show({
										action: 'edit',
										callback: function(Org_id) {
											win.doSearch();//~ win.grid.getStore().load();
										},
										Org_id: record.get(orgIdField),
										orgType: obj,
										allowEmptyUAddress: win.allowEmptyUAddress
									});
								}
								else{
									Ext6.Msg.alert(langs('Ошибка'), langs('Для добавления, редактирования или удаления организации обратитесь к Администратору справочника организаций. Контактная информация: ')+getGlobalOptions().contact_info);
									return false;
								}
							}
						}
					]
				})
			],
			columns: [
				{ dataIndex: 'Org_id', type: 'int', hidden: true },
				{ dataIndex: 'OrgType_id', type: 'int', hidden: true },
				{ dataIndex: 'Org_pid', type: 'int', hidden: true },
				{ dataIndex: 'OrgSMO_id', type: 'int', hidden: true },
				{ dataIndex: 'OrgAnatom_id', type: 'int', hidden: true },
				{ dataIndex: 'Lpu_id', type: 'int', hidden: true },
				{ dataIndex: 'Org_Code', type: 'string', hidden: true },
				{ dataIndex: 'Org_StickNick', type: 'string', hidden: true },
				{ dataIndex: 'Server_id', type: 'int', hidden: true },
				{ dataIndex: 'OrgType_SysNick', type: 'string', hidden: true },
				{ dataIndex: 'Org_Nick', text: '', type: 'string', width: 145,
					filter: {
						emptyText: 'Сокращение',
						xtype: 'textfield',
						itemId: 'nickfilter',
						anchor: '-20',
						enableKeyEvents: true,
						refreshTrigger: function() {
							var isEmpty = Ext6.isEmpty(this.getValue());
							this.triggers.clear.setVisible(!isEmpty);
							this.triggers.search.setVisible(isEmpty);
						},
						delaySearchId: null,
						delaySearch: function(delay) {
							var _this = this;
							if (this.delaySearchId) {
								clearTimeout(this.delaySearchId);
							}
							win.delaySearchId = setTimeout(function() {
								
								win.doSearch();
								
								this.delaySearchId = null;
							}, delay);
							
							if(win.grid.store.filters.length) {
								win.grid.store.clearFilter();
							}
						},
						triggers: {
							search: {
								cls: 'x6-form-search-trigger',
								handler: function() {
									win.doSearch();
								}
							},
							clear: {
								cls: 'x6-form-clear-trigger',
								hidden: true,
								handler: function() {
									this.setValue('');
									win.doSearch();
									this.refreshTrigger();
								}
							}
						},
						listeners: { 
							keyup: function(field, e) {
								this.refreshTrigger();
								this.delaySearch(300);
							}
						}
					}
				},
				{ dataIndex: 'OrgStac_Code', header: langs('Федеральный код '), type: 'string', hidden: true },
				{ dataIndex: 'Org_Name', text: '', type: 'string', flex: 1,
					/*filter: {
						type: 'string',
						xtype: 'textfield',
						triggers: {
							search: {
								cls: 'x6-form-search-trigger',
								handler: function() {
									// ?
								}
							}
						},
						anchor: '-20',
						emptyText: 'Наименование'
					}*/
					
					filter: {
						emptyText: 'Наименование',
						xtype: 'textfield',
						itemId: 'namefilter',
						anchor: '-20',
						enableKeyEvents: true,
						refreshTrigger: function() {
							var isEmpty = Ext6.isEmpty(this.getValue());
							this.triggers.clear.setVisible(!isEmpty);
							this.triggers.search.setVisible(isEmpty);
						},
						delaySearchId: null,
						delaySearch: function(delay) {
							var _this = this;
							if (this.delaySearchId) {
								clearTimeout(this.delaySearchId);
							}
							win.delaySearchId = setTimeout(function() {
								win.doSearch();
								this.delaySearchId = null;
							}, delay);
							
							if(win.grid.store.filters.length) {
								win.grid.store.clearFilter();
							}
						},
						triggers: {
							search: {
								cls: 'x6-form-search-trigger',
								handler: function() {
									win.doSearch();
								}
							},
							clear: {
								cls: 'x6-form-clear-trigger',
								hidden: true,
								handler: function() {
									this.setValue('');
									win.doSearch();
									this.refreshTrigger();
								}
							}
						},
						listeners: {
							keyup: function(field, e) {
								this.refreshTrigger();
								this.delaySearch(300);
							}
						}
					}
				},
				{ dataIndex: 'Org_Address', header: langs('Адрес'), type: 'string', width: 215 },
				{ dataIndex: 'Org_begDate', header: langs('Дата открытия'), type: 'date', formatter: 'date("d.m.Y")', width: 130 },
				{ dataIndex: 'Org_endDate', header: langs('Дата закрытия'), type: 'date', formatter: 'date("d.m.Y")', width: 130 },
				{ dataIndex: 'Lpu_f003mcod', header: langs('Реестровый номер'), type: 'string', hidden: true},
			],
			store: Ext6.create('Ext6.data.Store', {
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.Org_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					{ name: 'Org_id', type: 'int', hidden: true },
					{ name: 'OrgType_id', type: 'int', hidden: true },
					{ name: 'Org_pid', type: 'int', hidden: true },
					{ name: 'OrgSMO_id', type: 'int', hidden: true },
					{ name: 'OrgAnatom_id', type: 'int', hidden: true },
					{ name: 'Lpu_id', type: 'int', hidden: true },
					{ name: 'Org_Code', type: 'string', hidden: true },
					{ name: 'Org_StickNick', type: 'string', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'OrgType_SysNick', type: 'string', hidden: true },
					
					{ name: 'Org_Nick', header: langs('Сокращение'), width: 180, type: 'string' },
					
					{ name: 'OrgStac_Code', header: langs('Федеральный код '), type: 'string', hidden: true },
					
					{ name: 'Org_Name', header: langs('Наименование'), type: 'string', flex: 1 },
					{ name: 'Org_Address', header: langs('Адрес'), width: 200, type: 'string' },
					{ name: 'Org_begDate', header: langs('Дата открытия'), width: 100, type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'Org_endDate', header: langs('Дата закрытия'), width: 100, type: 'date', dateFormat: 'd.m.Y' },
					
					{ name: 'Lpu_f003mcod', header: langs('Реестровый номер'), type: 'string', hidden: true },
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: C_ORG_LIST,
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'Org_Nick'
				],
				listeners: {
					load: function() {
						win.queryById('btnEdit').disable();
						win.queryById('btnOK').disable();
					}
				}
			})
		});
		
		win.MainPanel = new Ext6.Panel({
			layout: 'border',
			region: 'center',
			border: false,
			items: [
				win.grid
			]
		});
		
		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			}, {
				xtype: 'SubmitButton',
				text: langs('Выбрать'),
				itemId: 'btnOK',
				handler:function () {
					win.onOKbutton();
				}
			}]
		});

		this.callParent(arguments);
	}
});