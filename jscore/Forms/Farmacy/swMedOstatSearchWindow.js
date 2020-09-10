/**
* swMedOstatSearchWindow - форма для просмотра отстатков медикаментов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      22.12.2011
*/
/*NO PARSE JSON*/
sw.Promed.swMedOstatSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedOstatSearchWindow',
	objectSrc: '/jscore/Forms/Farmacy/swMedOstatSearchWindow.js',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doReset: function() {
		var current_window = this;
		if (current_window.mode != 'LpuSection') {
			current_window.Contragent_id = null;
			current_window.Mol_id = null;
			current_window.findById('doswContragent_id').setValue(null);
		} else if (!current_window.Contragent_id) {
				current_window.findById('doswContragent_id').setValue(null);
				current_window.Mol_id = null;
			}

        var fin_combo = current_window.findById('doswDrugFinance_id');
        var cost_combo = current_window.findById('doswWhsDocumentCostItemType_id');
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
		current_window.findById('doswDrug_id').setValue(null);
		current_window.findById('doswDrug_id').setLinkedFields();

		loadContragent(current_window, 'doswContragent_id', {mode:'med_ost'}, function() {
			loadSprMol(current_window, 'doswMol_id','doswContragent_id');
		}.createDelegate(current_window));
	},
	doSearch: function() {
		var form = this.findById('MedOstatSearchForm');
		var params = form.getForm().getValues();
		if (!params.Contragent_id) {
			params.Contragent_id = form.getForm().findField('Contragent_id').getValue();
		}
		params.start = 0;
		params.limit = 100;
		this.Contragent_id = this.findById('doswContragent_id').getValue(); //для передачи в параметры, при формировании документов списания/передачи
		this.Mol_id = this.findById('doswMol_id').getValue();
		if (!this.Contragent_id || this.Contragent_id == '') this.Contragent_id = 0;

        if (Ext.isEmpty(params.Drug_id) && (Ext.isEmpty(params.DrugFinance_id) || Ext.isEmpty(params.WhsDocumentCostItemType_id))) {
            sw.swMsg.alert(langs('Ошибка'), langs('Для формирования остатков заполните или поле Медикамент, или  поля  Источник финанс. и Статья расхода'));
		} else if (!form.getForm().isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    form.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
        } else {
		this.findById('MedOstatSearchGrid').loadData({globalFilters: params});
		}
	},
	draggable: true,
	height: 550,
	id: 'MedOstatSearchWindow',
	Contragent_id: null,
	Mol_id: null,
	initComponent: function() {
		var form = this;
	
		this.MedOstatFormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 130,
			id: 'MedOstatSearchForm',
			items: [{
				layout: 'column',				
				border: false,
				items: 
				[{
					layout: 'form',
					border: false,
					width: 650,
					items: 
					[{
						width:500,
						allowBlank: false,
						fieldLabel: lang['kontragent'],
						xtype: 'swcontragentcombo',
						tabIndex: TABINDEX_DPREW + 1,
						id: 'doswContragent_id',
						name: 'Contragent_id',
						hiddenName:'Contragent_id',
						listeners: {
							change: function(combo) {
								this.findById('doswMol_id').setDisabled(!(combo.getValue()>0));							
								if ((combo.getValue()>0) && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
									this.findById('doswMol_id').enable();
									setFilterMol(this.findById('doswMol_id'), combo.getValue());
								} else {
									this.findById('doswMol_id').disable();									
									this.findById('doswMol_id').setValue(null);
								}
								//обновляем комбо Drug_id
								var form = this.findById('MedOstatSearchForm');
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
					}]
				}, {
					layout: 'form',
					border: false,
					width: 125,
					items: 
					[{
						xtype: 'button',
						id: 'DOSW_SearchButton',
						text: langs('Найти'),
						minWidth: 125,
						disabled: false,
						topLevel: true,						
						tabIndex: TABINDEX_DPREW + 21,						
						handler: function() {
							form.doSearch();
						}
					}]
				}]
			}, {
				layout: 'column',				
				border: false,
				items: 
				[{
					layout: 'form',
					border: false,
					width: 650,
					items: 
					[{
						allowBlank: true,
						width:500,
						fieldLabel: lang['mol'],
						hiddenName: 'Mol_id',
						id: 'doswMol_id',
						lastQuery: '',
						linkedElements: [ ],
						tabIndex: TABINDEX_DPREW + 2,
						xtype: 'swmolcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 125,
					items: 
					[{ 
						xtype: 'button',
						id: 'DOSW_ClearButton',
						text: lang['ochistit'],
						minWidth: 125,
						disabled: false,
						topLevel: true,
						tabIndex: TABINDEX_DPREW + 22,						
						handler: function() {
							form.doReset();
                            form.doSearch();
						}
					}]
				}]
			}, {
					layout: 'form',
					border: false,
					width: 650,
					items: [{
						fieldLabel: lang['istochnik_finans'],
						allowBlank: true,
						hiddenName: 'DrugFinance_id',
						id: 'doswDrugFinance_id',
						tabIndex: TABINDEX_DPREW + 3,
						width: 335,
						xtype: 'swdrugfinancecombo'
					}]
			}, {
				layout: 'form',
				border: false,
				width: 650,
				items: [{
					fieldLabel: langs('Статья расходов'),
					allowBlank: true,
					hiddenName: 'WhsDocumentCostItemType_id',
					id: 'doswWhsDocumentCostItemType_id',
					tabIndex: TABINDEX_DPREW + 4,
					width: 335,
					xtype: 'swwhsdocumentcostitemtypecombo'
				}]
			}, {
				allowBlank: true,
				displayField: 'Drug_FullName',
				enableKeyEvents: true,
				fieldLabel: lang['medikament'],
				forceSelection: true,
				hiddenName: 'Drug_id',
				id: 'doswDrug_id',
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
							store.baseParams.Contragent_id = Ext.getCmp('doswContragent_id').getValue();
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
				xtype: 'combo',
                listeners: {
                    change: function() {
                        this.setLinkedFields();
                    }
                },
				setLinkedFields: function() {
                    var base_form = form.findById('MedOstatSearchForm').getForm();
                	var allow_blank = !Ext.isEmpty(this.getValue());

                    base_form.findField('DrugFinance_id').setAllowBlank(allow_blank);
                    base_form.findField('WhsDocumentCostItemType_id').setAllowBlank(allow_blank);
				}
			}],
			keys: [{
				fn: function(e) {
					Ext.getCmp('MedOstatSearchWindow').doSearch();
				},
				key: Ext.EventObject.ENTER,
				scope: this,
				stopEvent: true
			}],
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north'
		});
		
		var sf = [
			{ name: 'row_id', type: 'int', header: 'ID', key: true },
			{ name: 'Drug_id', type: 'int', hidden: true},		
			{ name: 'DocumentUcStr_id', type: 'int', hidden: true},
			{ header: lang['medikament'],  type: 'string', name: 'Drug_Name', id: 'autoexpand', width: 100 },
			{ header: lang['srok_godnosti'],  type: 'date', name: 'godnDate', width: 100 },
			{ header: lang['ed_ucheta'],  type: 'string', name: 'unit', width: 100 }			
		];
		
		if (isFarmacyInterface) {
			sf.push({name: 'Price', width: 110, header: lang['tsena_opt_bez_nds'], type: 'money', align: 'right'});
			sf.push({name: 'PriceR', width: 110, header: lang['tsena_rozn_s_nds'], type: 'money', align: 'right'});
		} else  {
			sf.push({name: 'PriceR', width: 110, header: lang['tsena'], type: 'money', align: 'right'});
		}
		sf.push({header: lang['ostatok'],  type: 'float', name: 'ostat', width: 70, align: 'right'});
		if (isFarmacyInterface) {
			sf.push({name: 'Sum', width: 110, header: lang['summa_opt_bez_nds'], type: 'money', align: 'right'});
			sf.push({name: 'SumR', width: 110, header: lang['summa_rozn_s_nds'], type: 'money', align: 'right'});
		} else  {
			sf.push({name: 'SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'});
		}
		
        sf.push({header: langs('Источник финанс.'),  type: 'string', name: 'DrugFinance_Name', width: 100});
        sf.push({header: langs('Статья расхода'),  type: 'string', name: 'WhsDocumentCostItemType_Name', width: 100});

		this.MedOstatSearchFrame = new sw.Promed.ViewFrame({
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
				name: 'DOSW_SearchButton',
				type: 'field'
			},
			focusPrev: {
				name: 'DOSW_OstatDate',
				type: 'field'
			},
			id: 'MedOstatSearchGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: sf,
			toolbar: false,
			totalProperty: 'totalCount'
		});
	
		Ext.apply(this, {
			buttons: [{
				text: '-'
			}, {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					Ext.getCmp('DOSW_SearchButton').focus();
				},
				onTabAction: function() {
					var current_window = this.ownerCt;
					current_window.findById('MedOstatSearchForm').getForm().findField('OstatDate').focus(true, 200);
				},
				tabIndex: TABINDEX_DPREW + 26,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.MedOstatFormPanel,
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
							items: [form.MedOstatSearchFrame]
						}
					]
				}
			]
		});
		sw.Promed.swMedOstatSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('MedOstatSearchWindow');
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
			Ext.getCmp('MedOstatSearchWindow').findById('MedOstatSearchGrid').removeAll();
		}
	},
	plain: true,
	resizable: true,
	title: lang['ostatki_poisk_medikamentov'],
	width: 900,
	show: function() {
		sw.Promed.swMedOstatSearchWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		var base_form = current_window.findById('MedOstatSearchForm').getForm();
		current_window.restore();
		current_window.center();
		current_window.maximize();
		current_window.doReset();
		current_window.mode = '';
		//Контрагент - отделение ЛПУ. Передается из АРМ старшей медсестры
		if (arguments[0] && arguments[0].Contragent_id) {
			current_window.Contragent_id = arguments[0].Contragent_id;
			current_window.mode = 'LpuSection';
		} else {
			this.Contragent_id = null;
		}
		if (this.Contragent_id > 0) {
			base_form.findField('Contragent_id').setValue(this.Contragent_id);
		}
		
		loadContragent(current_window, 'doswContragent_id', {mode:'med_ost'}, function() {						
			this.loadSprMol(current_window, 'doswMol_id','doswContragent_id');			
		}.createDelegate(current_window));

		if (current_window.mode == 'LpuSection' && current_window.Contragent_id) {
			base_form.findField('Contragent_id').disable();
			current_window.doSearch();
		}
		
		base_form.findField('Drug_id').getStore().removeAll();
	},
	loadSprMol: function(form, comboId, contragentId, saveMol) {
		var combo = Ext.getCmp(comboId);
		var contragent = Ext.getCmp(contragentId);
		combo.getStore().load( {
			callback: function() {
				combo.setValue(combo.getValue());
				form.setFilterMol(combo, contragent.getValue(), saveMol);
			}
		});
	},
	setFilterMol: function(combo, Contragent_id, saveMol) {
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		var co = 0;
		var Mol_id = null;
		combo.getStore().filterBy(function(record) {
			/*if ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0)) {
				co++;
				Mol_id = record.get('Mol_id');
			}*/
			return ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0));
		});
		if (co==1) {
			combo.setValue(Mol_id);
		} else {
			if (!saveMol) {
				combo.setValue(null);
			} else {
				//если контрагент первоначальный, восстаноавливаем первоначальный Мол
				if (Ext.getCmp('DokDemandEditForm').reader.jsonData[0].Contragent_sid == Contragent_id && Contragent_id > 0)
					combo.setValue(Ext.getCmp('DokDemandEditForm').reader.jsonData[0].Mol_sid);
			}
		}
	}
});