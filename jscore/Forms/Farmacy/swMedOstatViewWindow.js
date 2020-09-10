/**
* swMedOstatViewWindow - просмотр отстатков медикаментов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      14.01.2011
*/
/*NO PARSE JSON*/
sw.Promed.swMedOstatViewWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedOstatViewWindow',
	objectSrc: '/jscore/Forms/Farmacy/swMedOstatViewWindow.js',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doReset: function() {
		var current_window = this;
		var form = this.findById('MedOstatViewForm').getForm();

		form.reset();

		current_window.Contragent_id = null;
		current_window.Mol_id = null;
		current_window.findById('dovwContragent_id').setValue(null);
		current_window.findById('dovwDrugFinance_id').setValue(null);
		current_window.findById('dovwDrug_id').setValue(null);
		current_window.loadContragent('dovwContragent_id', null/*{mode:'med_ost'}*/, function() {
			current_window.loadSprMol('dovwMol_id','dovwContragent_id');			
		}.createDelegate(current_window));

		//установка источника финансирования по умолчанию
		var fin_combo = form.findField('DrugFinance_id');
		var idx = fin_combo.getStore().findBy(function(r) {
			return (r.get('DrugFinance_Code') == '1'); // 1 - ОМС
		});
		if (idx >= 0) {
            fin_combo.setValue(fin_combo.getStore().getAt(idx).get('DrugFinance_id'));
		}

		//this.doSearch(); // Некоторые обязательные для заполнения поля неимеют значения по умолачнию. Следовательно нет смысла сразу вызывать формирование остатков.
	},
	doSearch: function() {
		var form = this.findById('MedOstatViewForm');
		var params = form.getForm().getValues();
		var colModel = this.findById('MedOstatViewGrid').getGrid().getColumnModel();

        if ( !form.getForm().isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    form.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

		params.start = 0;
		params.limit = 100;
		params.searchByDrug = form.getForm().findField('searchByDrug').getValue();
		this.Contragent_id = this.findById('dovwContragent_id').getValue(); //для передачи в параметры, при формировании документов списания/передачи
		//this.Mol_id = this.findById('dovwMol_id').getValue();
		this.Mol_id = null;
		if (!this.Contragent_id || this.Contragent_id == '') this.Contragent_id = 0;
		log(params.searchByDrug);
		colModel.setHidden(colModel.findColumnIndex('PrepSeries_Ser'), params.searchByDrug);
		colModel.setHidden(colModel.findColumnIndex('PrepBlockCause_Name'), params.searchByDrug);
		this.findById('MedOstatViewGrid').loadData({globalFilters: params});
	},
	draggable: true,
	height: 550,
	id: 'MedOstatViewWindow',
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
			id: 'MedOstatViewForm',
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
						id: 'dovwContragent_id',
						name: 'Contragent_id',
						hiddenName:'Contragent_id',
						listeners: {
							change: function(combo) {
								this.findById('dovwMol_id').setDisabled(!(combo.getValue()>0));							
								if ((combo.getValue()>0) && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
									//this.findById('dovwMol_id').setAllowBlank(false);
									this.findById('dovwMol_id').enable();
									this.setFilterMol(this.findById('dovwMol_id'), combo.getValue());
								} else {
									this.findById('dovwMol_id').disable();
									//this.findById('dovwMol_id').setAllowBlank(true);
									this.findById('dovwMol_id').setValue(null);
								}
								//обновляем комбо Drug_id
								var form = this.findById('MedOstatViewForm');
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
                                                this.findById('dovwDrugFinance_id').getStore().filterBy(function(record) {
                                                    if (record.get('DrugFinance_id').inlist(DrugFinanceList)) {
                                                        return true;
                                                    } else {return false;}
                                                });
                                            }
                                            this.findById('dovwDrugFinance_id').setValue('');
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
					}]
				}, {
					layout: 'form',
					border: false,
					width: 125,
					items: 
					[{
						xtype: 'button',
						id: 'DOVW_SearchButton',
						text: lang['sformirovat'],
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
						hidden: true,
						hiddenName: 'Mol_id',
						id: 'dovwMol_id',
						lastQuery: '',
						linkedElements: [ ],
						tabIndex: TABINDEX_DPREW + 2,
						xtype: 'swmolcombo',
						hidelabel: true
					}]
				}, 
				
				
				{
					layout: 'form',
					border: false,
					width: 650,
					items: 
					[{
						width:500,
						hidden: true,
						tabIndex: TABINDEX_DPREW + 2,
						hideLabel: true,
						xtype: 'swcombo'
					}]
				},
				
				
				
				{
					layout: 'form',
					border: false,
					width: 125,
					items: 
					[{ 
						xtype: 'button',
						id: 'DOVW_ClearButton',
						text: lang['ochistit'],
						minWidth: 125,
						disabled: false,
						topLevel: true,
						tabIndex: TABINDEX_DPREW + 22,						
						handler: function() {
							form.doReset();
						}
					}]
				}]
			}, {
				layout: 'column',				
				border: false,
				items: [{
					layout: 'form',
					border: false,
					width: 650,
					items: [{
						fieldLabel: lang['istochnik_finans'],
						allowBlank: false,
						hiddenName: 'DrugFinance_id',
						id: 'dovwDrugFinance_id',
						tabIndex: TABINDEX_DPREW + 3,
						width: 500,
						xtype: 'swdrugfinancecombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 125,
					items: 
					[{
						xtype: 'button',
						id: 'DOVW_CheckAllButton',
						text: lang['otmetit_vse'],
						minWidth: 125,
						disabled: false,
						topLevel: true,
						tabIndex: TABINDEX_DPREW + 23,						
						handler: function() {
							Ext.getCmp('MedOstatViewGrid').selectAllOstat();
						}
					}]
				}]
			}, {
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					width: 650,
					items: [{
						fieldLabel: lang['statya_rashodov'],
						allowBlank: false,
						hiddenName: 'WhsDocumentCostItemType_id',
						tabIndex: TABINDEX_DPREW + 3,
						width: 500,
						xtype: 'swwhsdocumentcostitemtypecombo'
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						hideLabel: true,
						name: 'searchByDrug',
						tabIndex: TABINDEX_DPREW + 24,
						xtype: 'checkbox',
						boxLabel: lang['otobrajat_ostatki_po_torgovomu_naimenovaniyu']
					}]
				}]
			}, {
				allowBlank: true,
				displayField: 'Drug_FullName',
				enableKeyEvents: true,
				fieldLabel: lang['medikament'],
				forceSelection: true,
				hiddenName: 'Drug_id',
				id: 'dovwDrug_id',
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
							store.baseParams.Contragent_id = Ext.getCmp('dovwContragent_id').getValue();
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
			}],
			keys: [{
				fn: function(e) {
					Ext.getCmp('MedOstatViewWindow').doSearch();
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
			{ name: 'PrepSeries_id', type: 'int', hidden: true},
			{ name: 'PrepBlockCause_id', type: 'int', hidden: true},
			{ name: 'DocumentUcStr_id', type: 'int', hidden: true},
			{ name: 'STRONGGROUPID', type: 'int', hidden: true},
			{ header: lang['medikament'],  type: 'string', name: 'Drug_Name', id: 'autoexpand', width: 100 },
			{ header: lang['seriya_vyipuska'],  type: 'string', name: 'PrepSeries_Ser', width: 100 },
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
		sf.push({ header: lang['kolichestvo'],  type: 'int', name: 'quantity', width: 100, align: 'right', editor: new Ext.form.NumberField({ allowBlank: true, allowDecimals: true, decimalPrecision: 3, allowNegative: false, minValue: 0 }) });
        sf.push({ name: 'DrugFinance_id', hidden: true, type: 'int' });
		sf.push({ name: 'DrugFinance_Name', header: lang['istochnik_finansirovaniya'], type: 'string', width: 120 });
        sf.push({ name: 'WhsDocumentCostItemType_id', hidden: true, type: 'int' });
		sf.push({ name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashodov'], type: 'string', width: 120 });
		sf.push({ name: 'PrepBlockCause_Name', header: lang['prichina_blokirovki'], type: 'string', width: 120 });

		this.MedOstatViewFrame = new sw.Promed.ViewFrame({
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
				name: 'DOVW_SearchButton',
				type: 'field'
			},
			focusPrev: {
				name: 'DOVW_OstatDate',
				type: 'field'
			},
			id: 'MedOstatViewGrid',
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
				if (o.record.data.STRONGGROUPID != 2) val = Number(val).toFixed(2); // Если лекарство не сильнодействующее, округляем дл 2 знаков
				if (val > ostat) val = ostat;
				if (val != o.value) o.record.set('quantity', val);
				o.record.commit();
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

		this.MedOstatViewFrame.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if (!Ext.isEmpty(row.get('PrepBlockCause_id'))) {
					cls = cls + 'x-grid-rowred';
				} else {
					cls = 'x-grid-panel';
				}

				return cls;
			}.createDelegate(this)
		});
	
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					var current_window = this.ownerCt;
					current_window.createDocument('DocSpis');
				},
				id: 'DOVW_CheckAllButton',
				tabIndex: TABINDEX_DPREW + 24,
				text: lang['spisat']
			}, {
				handler: function() {
					var current_window = this.ownerCt;
					current_window.createDocument('DocUc');
				},
				id: 'DOVW_CheckAllButton',
				tabIndex: TABINDEX_DPREW + 25,
				text: lang['peredat']
			}, {
				text: '-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function() {
					ShowHelp(this.ownerCt.title);
				}
			}, {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					Ext.getCmp('DOVW_SearchButton').focus();
				},
				onTabAction: function() {
					var current_window = this.ownerCt;
					current_window.findById('MedOstatViewForm').getForm().findField('OstatDate').focus(true, 200);
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
							items: [form.MedOstatViewFrame]
						}
					]
				}
			]
		});
		sw.Promed.swMedOstatViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('MedOstatViewWindow');
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
			Ext.getCmp('MedOstatViewWindow').findById('MedOstatViewGrid').removeAll();
		}
	},
	plain: true,
	resizable: true,
	title: lang['ostatki_medikamentov'],
	width: 900,
	show: function() {
		sw.Promed.swMedOstatViewWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		var base_form = current_window.findById('MedOstatViewForm').getForm();
		current_window.restore();
		current_window.center();
		current_window.maximize();
		current_window.doReset();
		base_form.findField('dovwMol_id').hideContainer();
		base_form.findField('Contragent_id').getStore().baseParams.mode = 'med_ost';
		this.Contragent_id = null;
		
		current_window.loadContragent('dovwContragent_id', null/*{mode:'med_ost'}*/, function() {
			current_window.loadSprMol('dovwMol_id','dovwContragent_id');			
		}.createDelegate(current_window));
		
		base_form.findField('Drug_id').getStore().removeAll();
		this.doLayout();
	},
	loadContragent: function(comboId, params, callback) {
		var combo = this.findById(comboId);
		var value = combo.getValue() > 0 ? combo.getValue() : (this.Contragent_id ? this.Contragent_id : null);
		var form = this.findById('MedOstatViewForm');
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
		/*combo.getStore().filterBy(function(record) {
			if ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0)) {
				co++;
				Mol_id = record.get('Mol_id');
			}
			return ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0));
		});*/
		if (co==1) {
			combo.setValue(Mol_id);
		} else {
			combo.setValue(null);			
		}
	},
	createDocument: function(doc_type) {
		var ostat_array = Ext.getCmp('MedOstatViewGrid').getSelectedOstat();
		var current_window = this;
		var err_msg = '';
		if (ostat_array == 'error') return false;
		if (ostat_array.length <= 0) {
			if (doc_type == 'DocSpis')
				err_msg = lang['spisanie_nevozmojno_t_k_ne_zapolneno_kol-vo_spisyivaemogo_preparata'];
			else
				err_msg = lang['peredacha_nevozmojna_t_k_ne_zapolneno_kol-vo_peredavaemogo_preparata'];
		}
		
		//контроль МОЛ		
		/*var m_combo = Ext.getCmp('dovwMol_id');
		if (!this.Mol_id && !m_combo.disabled) {
			this.Mol_id = m_combo.getValue();
			if (!this.Mol_id) {
				err_msg = lang['neobhodimo_vyibrat_mol'];
				m_combo.focus(true, 50);
			}
		}*/
		
		if (err_msg == '') {
			if (doc_type == 'DocSpis') {
				getWnd('swDokCreateSpisWindow').show({
					Contragent_id: current_window.Contragent_id,
					Mol_id: current_window.Mol_id,
					save_data: ostat_array,
					callback: function() {
						Ext.getCmp('MedOstatViewGrid').refreshRecords(null,0);
					}
				});
			}
			if (doc_type == 'DocUc') {
				getWnd('swDokCreateUcWindow').show({
					Contragent_id: current_window.Contragent_id,
					Mol_id: current_window.Mol_id,
					save_data: ostat_array,
					callback: function() {
						Ext.getCmp('MedOstatViewGrid').refreshRecords(null,0);
					}
				});
			}
		} else {
			sw.swMsg.alert(lang['oshibka'], err_msg);
		};
	}
});