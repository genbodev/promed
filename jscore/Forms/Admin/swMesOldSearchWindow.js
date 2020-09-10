/**
 * swMesOldSearchWindow - окно поиска МЭСов.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Markoff A.A. <markov@swan.perm.ru>
 * @version      08.08.2011
 * @comment      Префикс для id компонентов MSW (MesOldSearchWindow)
 */
sw.Promed.swMesOldSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteMes: function () {
	},
	searchInProgress: false,
	curARMType: null,
	doSearch: function (MedicalCareKind_id) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		var form = this.findById('MSW_MesOldSearchForm');
		var grid = this.findById('MSW_MesOldSearchGrid').getGrid();
		var params = form.getForm().getValues();
		var arr = form.find('disabled', true);
		for (var i = 0; i < arr.length; i++) {
			params[arr[i].hiddenName] = arr[i].getValue();
		}
		params.start = 0;
		params.limit = 100;
		if (MedicalCareKind_id) {
			params.MedicalCareKind_id = MedicalCareKind_id;
		} else {
			params.MedicalCareKind_id = this.MedicalCareKindTreePanel.getSelectionModel().selNode.id;
		}
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params,
			callback: function (r) {
				thisWindow.searchInProgress = false;
				if (r.length > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
		return true;
	},
	draggable: true,
	height: 550,
	id: 'MesOldSearchWindow',
	stomatMesProf_Codes: [63,64,65,66,67,68],
	initComponent: function () {
		var that = this;
		var MedicalCareKindTreePanel = new Ext.tree.TreePanel({
			region: 'center',
			id: 'MedicalCareKindTreePanel',
			loaded: false,
			border: false,
			root: {
				nodeType: 'async',
				text: lang['vidyi_meditsinskoy_pomoschi'],
				id: 'root',
				expanded: false
			},
			loader: new Ext.tree.TreeLoader({
				dataUrl: '/?c=MedicalCareKind&m=loadList'
			}),
			rootVisible: false,
			lastSelectedId: 0,
			listeners: {
				'click': function (node) {
					var MesProfCombo = that.filterForm.findField('MesProf_id');
					if (1 == node.attributes.stomat) {
						that.filterForm.findField('Mes_KoikoDni_From').setFieldLabel(lang['maks_kol-vo_uet_s']);
						that.grid.setColumnHeader('Mes_KoikoDni', lang['maksimalnoe_kolichestvo_uet']);
						that.grid.setColumnHeader('Mes_KoikoDniMin', lang['minimalnoe_kolichestvo_uet']);
						MesProfCombo.stomat = true;
					} else {
						that.filterForm.findField('Mes_KoikoDni_From').setFieldLabel(lang['maks_norm_srok_s']);
						that.grid.setColumnHeader('Mes_KoikoDni', lang['maksimalnyiy_normativnyiy_srok']);
						that.grid.setColumnHeader('Mes_KoikoDniMin', lang['minimalnyiy_normativnyiy_srok']);
						MesProfCombo.stomat = false;
					}
					if (MedicalCareKindTreePanel.lastSelectedId != node.id) {
						if (MesProfCombo.stomat) {
							that.filterForm.findField('MesProf_id').getStore().filterBy(function (el){ return that.stomatMesProf_Codes.in_array(el.data.MesProf_Code)});
							if (!MesProfCombo.getStore().getById(MesProfCombo.getValue())) {
								MesProfCombo.clearValue();
							}
						} else {
							that.filterForm.findField('MesProf_id').getStore().clearFilter();
						}
						that.doSearch(node.id);
						MedicalCareKindTreePanel.lastSelectedId = node.id;
					}
				},
				'load': function () {
					MedicalCareKindTreePanel.loaded = true;
					MedicalCareKindTreePanel.root.eachChild(function (el){
						if (48 == el.attributes.MedicalCareKind_id) {
							el.disable();
						}
					});
					setTimeout(function (){MedicalCareKindTreePanel.root.firstChild.select()},500);
				}
			}
		});
		var MesOldSearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					disabled: true,
					handler: function () {
						that.openMesEditWindow('add');
					}
				},
				{
					name: 'action_edit',
					disabled: true,
					handler: function () {
						that.openMesEditWindow('edit');
					}
				},
				{
					name: 'action_view',
					handler: function () {
						that.openMesEditWindow('view');
					}
				},
				{
					name: 'action_delete',
					hidden: true,
					disabled: true,
					handler: function (){

					}
				},
				{
					name: 'action_refresh',
					handler: function () {
						that.refreshMesOldSearchGrid();
					}
				},
				{
					name: 'action_print'
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=MesOld&m=loadMesOldSearchList',
			focusOn: {
				name: 'MSW_CancelButton',
				type: 'button'
			},
			focusPrev: {
				name: 'MSW_CancelButton',
				type: 'button'
			},
			id: 'MSW_MesOldSearchGrid',
			onRowSelect: function (grd, ind) {
				var grid = Ext.getCmp("MSW_MesOldSearchGrid");
				var row = grid.getGrid().getStore().getAt(ind);
				if (!row || !row.get('Mes_id'))
					return;
				// запрет или разрешение удаления для планируемых
				that.grid.getAction('action_delete').setDisabled(row.get('MesStatus') != 4);
				that.grid.getAction('action_view').setDisabled( (getRegionNick().inlist['vologda','buryatiya'] && !isUserGroup('EditingMES')) ); //#183719
			},
			object: 'MesOld',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			remoteSort: true,
			stringfields: [
				{ name: 'Mes_id', type: 'int', header: 'ID', key: true },
				{ name: 'Mes_Code', type: 'string', header: lang['kod'] + getMESAlias(), width: 120 },
				{ name: 'MesProf_CodeName', type: 'string', header: lang['spetsialnost'], width: 200 },
				{ name: 'MesAgeGroup_CodeName', type: 'string', header: lang['vozrastnaya_gruppa'], width: 170 },
				{ name: 'MesLevel_CodeName', type: 'string', header: lang['kategoriya_slojnosti'], width: 170 },
				{ name: 'Diag_CodeName', type: 'string', id: 'autoexpand', header: lang['diagnoz'], width: 170 },
				{ name: 'Mes_KoikoDniMin', type: 'float', header: lang['minimalnyiy_normativnyiy_srok'], width: 170 },
				{ name: 'Mes_KoikoDni', type: 'float', header: lang['maksimalnyiy_normativnyiy_srok'], width: 170 },
				{ name: 'Mes_VizitNumber',  hidden: true, type: 'int', header: lang['poryadkovyiy_nomer_posescheniya'], width: 170 },
				{ name: 'MesStatus', type: 'int', hidden: true },
				{ name: 'Mes_begDT', type: 'string', header: 'Дата начала', width: 100 },//todo исправить костыль с датой, добиться чтобы работало type: 'date', format:'d.m.Y', убрать из запроса преобразование даты
				{ name: 'Mes_endDT', type: 'string', header: lang['data_okonchaniya'], width: 100 }
			],
			toolbar: true,
			totalProperty: 'totalCount'
		});
		this.MedicalCareKindTreePanel = MedicalCareKindTreePanel;
		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.ownerCt.doSearch();
					},
					iconCls: 'search16',
					id: 'MSW_SearchButton',
					tabIndex: TABINDEX_MSW + 1,
					text: BTN_FRMSEARCH
				},
				{
					handler: function () {
						that.filterForm.reset();
						that.filterForm.findField('MesStatus_id').focus();
					},
					iconCls: 'reset16',
					id: 'MSW_ResetButton',
					tabIndex: TABINDEX_MSW + 1,
					text: lang['sbros']
				},
				{
					text: '-'
				},
				HelpButton(this, TABINDEX_MSW + 0),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'MSW_CancelButton',
					onTabAction: function () {
					},
					tabIndex: TABINDEX_MSW + 2,
					text: lang['zakryit']
				}
			],
			items: [
				{
					region: 'west',
					split: true,
					layout: 'border',
					width: 350,
					items: [
						{
							xtype: 'label',
							text: lang['gruppyi_mes'],
							region: 'north',
							style: 'padding: 5px'
						},
						MedicalCareKindTreePanel
					]
				},
				{
					region: 'center',
					layout: 'border',
					id: 'MesOldSearchWindow_RightPanel',
					items: [
						{
							xtype: 'panel',
							layout: 'border',
							border: false,
							region: 'north',
							id: 'MesOldSearchWindow_FilterPanel',
							height: 190,
							items: [
								{
									xtype: 'label',
									border: false,
									text: lang['mediko-ekonomicheskiy_standart'],
									region: 'north',
									style: 'padding: 5px;'
								},
								{
									xtype: 'panel',
									region: 'center',
									height: 100,
									border: false,
									bodyStyle: 'background: #DFE8F6;',
									items: [
										{
											xtype: 'fieldset',
											style: 'margin: 3px 6px 6px 6px; background: #DFE8F6',
											title: lang['filtr'],
											region: 'certer',
											collapsible: true,
											bodyStyle: 'background: #DFE8F6;',
											collapsed: false,
											autoHeight: true,
											listeners: {
												expand: function () {
													that.findById('MesOldSearchWindow_FilterPanel').setHeight(190);
													that.findById('MesOldSearchWindow_RightPanel').doLayout();
												},
												collapse: function () {
													that.findById('MesOldSearchWindow_FilterPanel').setHeight(50);
													that.findById('MesOldSearchWindow_RightPanel').doLayout();
												}
											},
											items: [
												new Ext.form.FormPanel({
													region: 'center',
													border: false,
													frame: false,
													autoHeight: true,
													labelWidth: 120,
													labelAlign: 'right',
													defaults: {
														bodyStyle: 'background: #DFE8F6;',
														defaults: {
															bodyStyle: 'background: #DFE8F6;'
														}
													},
													bodyStyle: 'background: #DFE8F6;',
													id: 'MSW_MesOldSearchForm',
													items: [
														{
															border: false,
															layout: 'column',
															items: [
																{
																	border: false,
																	layout: 'form',
																	items: [
																		new sw.Promed.SwBaseLocalCombo({
																			displayField: 'MesStatus_Name',
																			editable: false,
																			fieldLabel: lang['status'],
																			hiddenName: 'MesStatus_id',
																			store: new Ext.data.SimpleStore(
																				{
																					key: 'MesStatus_id',
																					autoLoad: true,
																					fields: [
																						{name: 'MesStatus_id', type: 'int'},
																						{name: 'MesStatus_Name', type: 'string'}
																					],
																					data: [
																						[1, lang['otkryityie+planiruemyie']],
																						[2, lang['otkryityie']],
																						[3, lang['zakryityie']],
																						[4, lang['planiruemyie']]
																					]
																				}),
																			tpl: '<tpl for="."><div class="x-combo-list-item">{MesStatus_Name}&nbsp;</div></tpl>',
																			valueField: 'MesStatus_id',
																			width: 180
																		})
																	]
																},
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			enableKeyEvents: true,
																			listeners: {
																				'keydown': function (inp, e) {
																					if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
																						e.stopEvent();
																						var form = Ext.getCmp('MSW_MesOldSearchForm');
																						form.getForm().findField('MesAgeGroup_id').focus(true);
																					}
																				},
																				'expand':function () {
																					var MesProfCombo = that.filterForm.findField('MesProf_id');
																					if (this.stomat) {
																						MesProfCombo.getStore().filterBy(function (el){ return that.stomatMesProf_Codes.in_array(el.data.MesProf_Code)});
																						if (!MesProfCombo.getStore().getById(MesProfCombo.getValue())) {
																							MesProfCombo.clearValue();
																						}
																					} else {
																						MesProfCombo.getStore().clearFilter();
																					}
																				}

																			},
																			hiddenName: 'MesProf_id',
																			width: 180,
																			xtype: 'swmesprofcombo'
																		}
																	]
																}
															]
														},
														{
															border: false,
															layout: 'column',
															items: [
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			enableKeyEvents: true,
																			listeners: {
																				'keydown': function (inp, e) {
																					if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
																						e.stopEvent();
																						var form = Ext.getCmp('MSW_MesOldSearchForm');
																						form.getForm().findField('MesProf_id').focus(true);
																					}
																				}
																			},
																			hiddenName: 'MesAgeGroup_id',
																			width: 180,
																			xtype: 'swmesagegroupcombo'
																		}
																	]
																},
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			fieldLabel: lang['kat_slojn'],
																			hiddenName: 'MesLevel_id',
																			width: 180,
																			xtype: 'swmeslevelcombo'
																		}
																	]
																}
															]
														},
														{
															border: false,
															layout: 'column',
															items: [
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			fieldLabel: lang['diagnoz_s'],
																			hiddenName: 'Diag_Code_From',
																			width: 180,
																			valueField: 'Diag_Code',
																			xtype: 'swdiagcombo'
																		}
																	]
																},
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			fieldLabel: lang['po'],
																			hiddenName: 'Diag_Code_To',
																			width: 180,
																			valueField: 'Diag_Code',
																			xtype: 'swdiagcombo'
																		}
																	]
																}
															]
														},
														{
															border: false,
															layout: 'column',
															id: 'Mes_KoikoDni',
															items: [
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			xtype: 'numberfield',
																			allowNegative: false,
																			allowDecimals: false,
																			maxLength: 3,
																			autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
																			fieldLabel: lang['maks_norm_srok_s'],
																			name: 'Mes_KoikoDni_From',
																			width: 180
																		}
																	]
																},
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			xtype: 'numberfield',
																			allowNegative: false,
																			allowDecimals: false,
																			maxLength: 3,
																			autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
																			fieldLabel: lang['po'],
																			name: 'Mes_KoikoDni_To',
																			width: 180
																		}
																	]
																}
															]
														},
														{
															border: false,
															layout: 'column',
															items: [
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			fieldLabel: lang['data_nachala'],
																			name: 'Mes_begDT_Range',
																			plugins: [
																				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
																			],
																			width: 180,
																			xtype: 'daterangefield'
																		}
																	]
																},
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			fieldLabel: lang['data_okonchaniya'],
																			name: 'Mes_endDT_Range',
																			plugins: [
																				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
																			],
																			width: 180,
																			xtype: 'daterangefield'
																		}
																	]
																}
															]
														}
													]
												})
											]
										}
									]
								}
							]
						},
						MesOldSearchGrid
					]
				}
			]
		});
		sw.Promed.swMesOldSearchWindow.superclass.initComponent.apply(this, arguments);
		this.findById('MSW_MesOldSearchGrid').getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row) {
				var cls = '';
				if (row.get('MesStatus') == 4)
					cls = cls + 'x-grid-rowblue ';
				if (row.get('MesStatus') == 3)
					cls = cls + 'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});
		this.findById('MSW_MesOldSearchGrid').addListenersFocusOnFields();
		this.filterForm = this.findById('MSW_MesOldSearchForm').getForm();
		this.grid = this.findById('MSW_MesOldSearchGrid');
	},
	keys: [
		{
			fn: function () {
				Ext.getCmp('MesOldSearchWindow').openMesEditWindow('add');
			},
			key: [
				Ext.EventObject.INSERT
			],
			stopEvent: true
		},
		{
			alt: true,
			fn: function () {
				Ext.getCmp('MesOldSearchWindow').doSearch();
			},
			key: [
				Ext.EventObject.ENTER,
				Ext.EventObject.G
			],
			stopEvent: true
		},
		{
			fn: function () {
				Ext.getCmp('MesOldSearchWindow').doSearch();
			},
			key: [
				Ext.EventObject.ENTER
			],
			stopEvent: true
		},
		{
			alt: true,
			fn: function () {
				Ext.getCmp('MesOldSearchWindow').hide();
			},
			key: [
				Ext.EventObject.P
			],
			stopEvent: true
		}
	],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	setDisabled_AddEdit: function(){
		var disable = true;
		var mesOldSearchGrid = this.findById('MSW_MesOldSearchGrid');
		if(getRegionNick().inlist['vologda','buryatiya']){
			if( (this.curARMType == 'superadmin' && isUserGroup('OuzChief')) || (this.curARMType == 'mstat' && isUserGroup('EditingMES')) ) {
				disable = false;
			}
		}else if( isUserGroup('OuzChief') || isUserGroup('OuzUser') || isUserGroup('OuzAdmin') ){
			disable = false;
		}

		mesOldSearchGrid.setActionDisabled('action_add', disable);
		mesOldSearchGrid.setActionDisabled('action_edit', disable);
		if(getRegionNick()=='vologda') mesOldSearchGrid.setActionDisabled('action_view', disable);
	},
	openMesEditWindow: function (action) {
		if (action != 'add' && action != 'edit' && action != 'view' && action != 'copy') {
			return false;
		}
		if (action == 'add' && getWnd('swMesOldEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya'] + getMESAlias() + lang['uje_otkryito']);
			return false;
		}
		var grid = this.findById('MSW_MesOldSearchGrid').getGrid();
		if (!grid) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_spisok'] + getMESAlias());
			return false;
		}
		var params = {};
		if (action != 'add') {
			var current_row = grid.getSelectionModel().getSelected();
			if (!current_row) {
				return false;
			}
			var Mes_id = current_row.get('Mes_id');
			if (Mes_id > 0)
				params.Mes_id = Mes_id;
			else
				return false;
		}
		params.action = action;
		params.curARMType = this.curARMType;
		params.callback = function (data) {
			if (!data) {
				return false;
			}
			var record = grid.getStore().getById(data.Mes_id);
			if (!record) {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Mes_id')) {
					grid.getStore().removeAll();
				}
				grid.getStore().reload();
			}
			else {
				var Mes_fields = [];
				grid.getStore().fields.eachKey(function (key) {
					Mes_fields.push(key);
				});
				for (var i = 0; i < Mes_fields.length; i++) {
					if (data[Mes_fields[i]] && (data[Mes_fields[i]] instanceof Date)) {
						record.set(Mes_fields[i], data[Mes_fields[i]].format('d.m.Y'));
					} else {
						record.set(Mes_fields[i], data[Mes_fields[i]]);
					}
				}
				record.commit();
				var selected_record = grid.getSelectionModel().getSelected();
				if (selected_record) {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
				else {
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			}
			return true;
		}.createDelegate(this);
		params.onHide = function () {
			if (grid.getStore().getCount() > 0) {
				var selected_record = grid.getSelectionModel().getSelected();
				if (selected_record) {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
				else {
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			}
		};
		params.MedicalCareKind_id = this.MedicalCareKindTreePanel.getSelectionModel().selNode.id;
		params.stomat = this.MedicalCareKindTreePanel.getSelectionModel().selNode.attributes.stomat;
		getWnd('swMesOldEditWindow').show(params);
		return true;
	},
	plain: true,
	pmUser_Name: null,
	printMes: function () {
	},
	refreshMesOldSearchGrid: function () {
		var grid = this.findById('MSW_MesOldSearchGrid').getGrid();
		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();
		if (grid.getStore().getCount() > 0) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	resizable: false,
	show: function () {
		var that = this,
            action;
		sw.Promed.swMesOldSearchWindow.superclass.show.apply(this, arguments);
		if (!this.MedicalCareKindTreePanel.loaded) {
			this.MedicalCareKindTreePanel.getRootNode().expand();
		}
		this.restore();
		this.center();
		this.maximize();

        if (arguments.length>0 && arguments[0].action) {
            action = arguments[0].action;
        }
        this.curARMType = (arguments.length>0 && arguments[0].ARMType) ? arguments[0].ARMType : getGlobalOptions().curARMType;
        
		this.findById('MSW_MesOldSearchGrid').addActions({name:'action_copy', text: lang['kopirovat'], hidden: getWnd('swWorkPlaceAdminLLOWindow').isVisible() || action == 'view',handler: function (){that.openMesEditWindow('copy');}})
		var form = this.findById('MSW_MesOldSearchForm');
		form.getForm().reset();
		this.findById('MSW_MesOldSearchGrid').getGrid().getStore().removeAll();
		this.findById('MSW_MesOldSearchGrid').addEmptyRecord(this.findById('MSW_MesOldSearchGrid').getGrid().getStore());
		form.getForm().findField('MesStatus_id').focus(true, 200);
		this.findById('MesOldSearchWindow_FilterPanel').doLayout();//непонятно почему, иногда требуется выполнить
		this.findById('MesOldSearchWindow_FilterPanel').doLayout();  // причем два раза
		var firstChildClick = function (){
			var firstChild = that.MedicalCareKindTreePanel.getRootNode().firstChild;
			if (null == firstChild) {
				setTimeout(firstChildClick, 100);
			} else {
				firstChild.fireEvent('click', firstChild);
			}
		}
			
		if(this.curARMType == 'adminllo')
		{
			this.findById('MSW_MesOldSearchGrid').setActionHidden('action_add', true);
			this.findById('MSW_MesOldSearchGrid').setActionHidden('action_edit', true);
		}
		else {
			this.findById('MSW_MesOldSearchGrid').setActionHidden('action_add', false);
			this.findById('MSW_MesOldSearchGrid').setActionHidden('action_edit', false);
		}
		this.setDisabled_AddEdit();	
		setTimeout(firstChildClick, 200);
	},
	title: lang['spisok'] + ' ' + getMESAlias() + lang['_prosmotr'],
	width: 800
});