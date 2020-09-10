/**
* swDokInvEditWindow - окно редактирования инвентаризационной ведомости.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      09.02.2011
*/
/*NO PARSE JSON*/
sw.Promed.swDokInvEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	codeRefresh: true,
	objectName: 'swDokInvEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokInvEditWindow.js',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	filterFinCombo: function(date) { //фильтрация справочников "источник финансирования" и "статья расходов"
		var base_form = this.findById('DokInvEditForm').getForm();
		if (Ext.isEmpty(date)) {
			date = base_form.findField('DocumentUc_InvDate').getValue();
		}
		if (!Ext.isEmpty(date)) {
			date = date.format('d.m.Y');
		}
		base_form.findField('DrugFinance_id').setDateFilter({Date: date});
		base_form.findField('WhsDocumentCostItemType_id').setDateFilter({Date: date});
	},
	doReset: function() {
		var current_window = this;
        var base_form = this.findById('DokInvEditForm').getForm();

        var fin_combo = base_form.findField('DrugFinance_id');
        var cost_combo = base_form.findField('WhsDocumentCostItemType_id');
        var default_fin_id = null;
        var default_cost_id = null;

        //вычисление значений по умолчанию
        var idx = fin_combo.getStore().findBy(function(r) {
            return (r.get('DrugFinance_Code') == '1'); // 1 - ОМС
        });
        if (idx >= 0) {
            default_fin_id = fin_combo.getStore().getAt(idx).get('DrugFinance_id');
        }

        idx = cost_combo.getStore().findBy(function(r) {
            return (r.get('WhsDocumentCostItemType_Code') == '99'); // 99 - Прочие
        });
        if (idx >= 0) {
            default_cost_id = cost_combo.getStore().getAt(idx).get('WhsDocumentCostItemType_id');
        }

        fin_combo.setValue(default_fin_id);
        cost_combo.setValue(default_cost_id);

		current_window.Contragent_id = null;
		current_window.Mol_id = null;
        current_window.findById('diewContragent_id').setValue(null);
		current_window.findById('diewDrug_id').setValue(null);
		current_window.loadContragent('diewContragent_id', {mode:'med_ost'}, function() {						
			current_window.loadSprMol('diewMol_id','diewContragent_id');			
		}.createDelegate(current_window));
		current_window.findById('diewDocumentUc_InvNum').setValue(null);
		current_window.findById('diewDocumentUc_InvDate').setValue(null);
		//добавить очистку грида
	},
	doSearch: function() {
		if(!this.doCheckBeforeSave()) return false;
		var win = this;
		var form = this.findById('DokInvEditForm');
		var params = form.getForm().getValues();
		params.start = 0;
		params.limit = 100;
		this.Contragent_id = this.findById('diewContragent_id').getValue(); //для передачи в параметры, при формировании документов списания/передачи
		this.Mol_id = this.findById('diewMol_id').getValue();
		if (!this.Contragent_id || this.Contragent_id == '') this.Contragent_id = 0;
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		Ext.Ajax.request({
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						return false;
					}
					if (response_obj.totalCount > 0) {
						var save_data = new Array();
						for(i = 0; i < response_obj.totalCount; i++) {
							save_data.push({DocumentUcStr_id: response_obj.data[i].DocumentUcStr_id, 'quantity': response_obj.data[i].ostat});
						}
						win.doSave('no_check', function(data) {
							Ext.Ajax.request({ //сохраняем найденые строки в документе ведомости
								callback: function(options, success, response) {
									loadMask.hide();
									if (success) {
										win.DocumentUc_id = data.DocumentUc_id;
										win.action = 'edit';					
										win.setMode();
									} else {
										sw.swMsg.alert(lang['oshibka'], lang['proizoshla_oshibka_na_etape_formirovaniya_vedomosti']);
									}
								}.createDelegate(this),
								params: {
									DocumentUc_id: data.DocumentUc_id,
									save_data: Ext.util.JSON.encode(save_data)
								},
								url: '/?c=FarmacyDrugOstat&m=saveDocumentUcStrFromArray'
							});
						});
					} else {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['poisk_po_zadannyim_usloviyam_ne_dal_rezultata']);
					}
				} else {
					loadMask.hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poiske_ostatkov_medikamentov']);
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=FarmacyDrugOstat&m=loadDrugOstatByFilters'
		});
	},
	doCheckBeforeSave: function() {
		var form = this.findById('DokInvEditForm');
		var base_form = form.getForm();			
		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {		
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		return true;
	},
	doSave: function(mode, callback) {
		var form = this.DokInvFormPanel;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		
		if (mode != 'no_check' && !win.doCheckBeforeSave())	return false;
		
		loadMask.show();
		form.getForm().submit({
			params: {
				DocumentUc_id: win.action == 'edit' && win.DocumentUc_id > 0 ? win.DocumentUc_id : 0,
				Contragent_sid: form.findById('diewContragent_id').getValue(),
				Mol_sid: form.findById('diewMol_id').getValue()
			},
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {				
				if (action.result) {
					if (action.result.DocumentUc_id) {
						Ext.getCmp('DokInvGridPanel').refreshRecords(null,0);
						loadMask.hide();
						if (callback)
							callback(action.result);
					} else {
						loadMask.hide();
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() { form.hide(); },
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	draggable: true,
	height: 550,
	id: 'DokInvEditWindow',
	Contragent_id: null,
	Mol_id: null,
	initComponent: function() {
		var form = this;
	
		this.DokInvFormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 130,
			id: 'DokInvEditForm',
			items: [{
				width:500,
				allowBlank: false,
				fieldLabel: lang['kontragent'],
				xtype: 'swcontragentcombo',
				tabIndex: TABINDEX_DPREW + 1,
				id: 'diewContragent_id',
				name: 'Contragent_id',
				hiddenName:'Contragent_id',
				listeners: {
					change: function(combo) {
						var ctr_type = combo.getFieldValue('ContragentType_id');						
						this.findById('diewMol_id').setDisabled(!(combo.getValue()>0));
						if ((combo.getValue() > 0) && ((ctr_type == '2') || (ctr_type == 3 && isFarmacyInterface) || (ctr_type == 5))) {
							this.findById('diewMol_id').setAllowBlank(false);
							if (combo.enabled)
								this.findById('diewMol_id').enable();
							else
								this.findById('diewMol_id').disable();
							this.setFilterMol(this.findById('diewMol_id'), combo.getValue());							
						} else {
							this.findById('diewMol_id').disable();
							this.findById('diewMol_id').setAllowBlank(true);
							this.findById('diewMol_id').setValue(null);
						}
						//обновляем комбо Drug_id
						var form = this.findById('DokInvEditForm');
						var base_form = form.getForm();
						base_form.findField('Drug_id').getStore().removeAll();
						base_form.findField('Drug_id').getStore().load({
							params: {
								mode: form.documentUcStrMode,
								Contragent_id: form.Contragent_id						
							}
						});
					}.createDelegate(this)
				}
			}, {
				allowBlank: false,
				width:500,
				fieldLabel: lang['mol'],
				hiddenName: 'Mol_id',
				id: 'diewMol_id',
				lastQuery: '',
				linkedElements: [ ],
				tabIndex: TABINDEX_DPREW + 2,
				xtype: 'swmolcombo'
			}, {
				fieldLabel: lang['istochnik_finans'],
				allowBlank: false,
				hiddenName: 'DrugFinance_id',
				id: 'diewDrugFinance_id',
				tabIndex: TABINDEX_DPREW + 3,
				width: 335,
				xtype: 'swdrugfinancecombo'
			}, {
				fieldLabel: lang['statya_rashodov'],
				allowBlank: false,
				hiddenName: 'WhsDocumentCostItemType_id',
				id: 'diewWhsDocumentCostItemType_id',
				tabIndex: TABINDEX_DPREW + 3,
				width: 335,
				xtype: 'swwhsdocumentcostitemtypecombo'
			}, {
				allowBlank: true,
				displayField: 'Drug_FullName',
				enableKeyEvents: true,
				fieldLabel: lang['medikament'],
				forceSelection: true,
				hiddenName: 'Drug_id',
				id: 'diewDrug_id',
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
							store.baseParams.Contragent_id = Ext.getCmp('diewContragent_id').getValue();
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
			}, {
				tabIndex: TABINDEX_DPREW + 5,				
				fieldLabel : lang['nomer'],
				name: 'DocumentUc_InvNum',
				id: 'diewDocumentUc_InvNum',
				allowBlank:false,
				xtype: 'textfield'
			}, {
				tabIndex: TABINDEX_DPREW + 6,
				fieldLabel : lang['data'],				
				allowBlank: false,				
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_InvDate',
				id: 'diewDocumentUc_InvDate',
				xtype: 'swdatefield',
				listeners: {
					change: function(field, newValue, oldValue) {
						form.filterFinCombo();
					}
				}
			}, {
				xtype: 'button',
				id: 'DIEW_SearchButton',
				text: lang['sformirovat'],
				minWidth: 125,
				disabled: false,
				topLevel: true,						
				tabIndex: TABINDEX_DPREW + 21,						
				handler: function() {
					form.doSearch();
				}
			}],
			keys: [{
				fn: function(e) {					
				},
				key: Ext.EventObject.ENTER,
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, 
			[
				{ name: 'DocumentUc_id' },
				{ name: 'Contragent_id' },
				{ name: 'Mol_id' },
				{ name: 'DocumentUc_InvNum' },
				{ name: 'DocumentUc_InvDate' },
				{ name: 'DrugFinance_id' },
				{ name: 'WhsDocumentCostItemType_id' }
			]),
			url: '/?c=Farmacy&m=save&method=DokInv',
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north'
		});
		
		var sf = [
			{ name: 'row_id', type: 'int', header: 'ID', key: true },
			{ name: 'Drug_id', type: 'int', hidden: true},
			{ name: 'DocumentUcStr_id', type: 'int', hidden: true, isparams: true },
			{ header: lang['kontragent'],  type: 'string', name: 'Contragent_Name', width: 220 },
			{ header: lang['mol'],  type: 'string', name: 'Mol_Name', width: 250 },
			{ header: lang['istochnik_fin'],  type: 'string', name: 'DrugFinance_Name', width: 130 },
			{ header: lang['statya_rashoda'],  type: 'string', name: 'WhsDocumentCostItemType_Name', width: 130 },
			{ header: lang['kod'],  type: 'string', name: 'Drug_Code', width: 100 },
			{ header: lang['naimenovanie'],  type: 'string', name: 'Drug_Name', width: 200 },
			{ header: lang['staroe'], type: 'float', name: 'DocumentUcStr_OstCount', width: 70 },
			{ header: lang['novoe'],  type: 'float', name: 'DocumentUcStr_Count', width: 70 },
			{ header: lang['rashojdenie'],  type: 'string', name: 'balance', width: 100 },
			{ header: lang['ed_ucheta'],  type: 'string', name: 'unit', width: 100 }	
		];

		this.DokInvEditFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			editformclassname: 'swDokInvStrEditWindow',
			dataUrl: '/?c=Farmacy&m=loadDocumentInvStrView',
			focusOn: {
				name: 'DIEW_SearchButton',
				type: 'field'
			},
			focusPrev: {
				name: 'DIEW_OstatDate',
				type: 'field'
			},
			id: 'DokInvEditGrid',
			region: 'center',			
			stringfields: sf,
			toolbar: true
		});
	
		Ext.apply(this, {
			buttons: [{
				id: 'DIEW_SaveButton',
				handler: function() {
					this.ownerCt.doSave('no_check', function(data) { form.hide(); });
				},
				iconCls: 'save16',
				tabIndex: form.firstTabIndex + 6,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function() {
					ShowHelp('Инвентаризационная ведомость');
				}
			}, {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					Ext.getCmp('DIEW_SearchButton').focus();
				},
				onTabAction: function() {
					var current_window = this.ownerCt;
					current_window.findById('DokInvEditForm').getForm().findField('OstatDate').focus(true, 200);
				},
				tabIndex: TABINDEX_DPREW + 26,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.DokInvFormPanel,
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
							items: [form.DokInvEditFrame]
						}
					]
				}
			]
		});
		sw.Promed.swDokInvEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('DokInvEditWindow');
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
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	listeners: {
		'hide': function() {
			Ext.getCmp('DokInvEditWindow').findById('DokInvEditGrid').removeAll();
		}
	},
	plain: true,
	resizable: true,
	title: lang['inventarizatsionnaya_vedomost'],
	width: 900,
	show: function() {
		sw.Promed.swDokInvEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		var base_form = current_window.findById('DokInvEditForm').getForm();
		current_window.restore();
		current_window.center();
		current_window.maximize();
		current_window.doReset();
		
		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+current_window.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		if (arguments[0].action) {
			current_window.action = arguments[0].action;
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
			current_window.action = 'view';
		if (arguments[0].DocumentUc_id) 
			current_window.DocumentUc_id = arguments[0].DocumentUc_id;
		else 
			current_window.DocumentUc_id = null;
		if (arguments[0].Contragent_id) {
			this.Contragent_id = arguments[0].Contragent_id;
		}
		current_window.loadContragent('diewContragent_id', {mode:'med_ost'}, function() {						
			current_window.loadSprMol('diewMol_id','diewContragent_id');			
		}.createDelegate(current_window));		
		current_window.enableEdit();

		this.setMode();
		
		base_form.findField('Drug_id').getStore().removeAll();
	},
	setMode: function() {
		var form = this;
		if (form.action == 'add') {
			form.setTitle(lang['inventarizatsionnaya_vedomost_dobavlenie']);
			this.findById('DIEW_SearchButton').enable();
			Ext.getCmp('DIEW_SaveButton').disable();
			form.filterFinCombo();
		} else {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
			loadMask.show();
			
			this.findById('DIEW_SearchButton').disable();
			Ext.getCmp('DIEW_SaveButton').enable();
			form.DokInvFormPanel.getForm().load({
				params: {
					DocumentUc_id: form.DocumentUc_id
				},
				failure: function() {
					loadMask.hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function(obj, action) {
					loadMask.hide();
					
					form.DokInvEditFrame.loadData({
						globalFilters: {
							DocumentUc_id: form.DocumentUc_id,
							mode: 'income'
						}, 
						noFocusOnLoad:true}
					);
					
					form.Mol_id = form.findById('diewMol_id').getValue();
					form.Contragent_id = form.findById('diewContragent_id').getValue();
					form.loadContragent('diewContragent_id', {mode:'med_ost'}, function() {
						form.loadSprMol('diewMol_id','diewContragent_id');	
						loadMask.hide();
					}.createDelegate(form));
					
					form.enableEdit();
					if (form.action=='edit') {
						form.setTitle(lang['inventarizatsionnaya_vedomost_redaktirovanie']);
						form.findById('diewDocumentUc_InvNum').focus(true, 50);
					}
					else {
						form.setTitle(lang['inventarizatsionnaya_vedomost_prosmotr']);
						form.focus();
					}

					form.filterFinCombo();
				},
				url: '/?c=Farmacy&m=edit&method=DokInv'
			});			
			

		}
	},
	enableEdit: function() {
		var form = this;
		if (form.action == 'add' || form.action == 'edit') {
			if (form.action == 'add') {
				form.findById('diewMol_id').enable();
				if (form.Contragent_id) {
					form.findById('diewContragent_id').disable();
				} else {
					form.findById('diewContragent_id').enable();
				}
				form.findById('diewDrugFinance_id').enable();
				form.findById('diewWhsDocumentCostItemType_id').enable();
				form.findById('diewDrug_id').enable();
			} else {
				form.findById('diewMol_id').disable();
				form.findById('diewContragent_id').disable();
				form.findById('diewDrugFinance_id').disable();
				form.findById('diewWhsDocumentCostItemType_id').disable();
				form.findById('diewDrug_id').disable();
			}
			form.findById('diewDocumentUc_InvNum').enable();
			form.findById('diewDocumentUc_InvDate').enable();
			form.DokInvEditFrame.setReadOnly(false);
		} else {
			form.findById('diewMol_id').disable();
			form.findById('diewContragent_id').disable();
			form.findById('diewDrugFinance_id').disable();
			form.findById('diewWhsDocumentCostItemType_id').disable();
			form.findById('diewDrug_id').disable();
			form.findById('diewDocumentUc_InvNum').disable();
			form.findById('diewDocumentUc_InvDate').disable();
			form.DokInvEditFrame.setReadOnly(true);
		}
	},
	loadContragent: function(comboId, params, callback) {
		var combo = this.findById(comboId);
		var value = combo.getValue() > 0 ? combo.getValue() : (this.Contragent_id ? this.Contragent_id : null);
		var form = this.findById('DokInvEditForm');
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
		combo.getStore().filterBy(function(record) {
			if ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0)) {
				co++;
				Mol_id = record.get('Mol_id');
			}
			return ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0));
		});
		if (co==1) {
			combo.setValue(Mol_id);
		} else {
			combo.setValue(this.Mol_id ? this.Mol_id : null);			
		}
	}
});