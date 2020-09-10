/**
* swEvnReceptRlsProvideEditWindow - окно выбора серии и количества для обеспечения рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      	DLO
* @access       	public
* @copyright    	Copyright (c) 2013 Swan Ltd.
* @author       	Salakhov R.
* @version      	18.10.2013
*/

sw.Promed.swEvnReceptRlsProvideEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'EvnReceptRlsProvideEditWindow',
	layout: 'border',
	modal: true,
	plain: true,
	resizable: true,
	doSelect: function() {
		var wnd = this;

		if (!this.form.isValid()) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						wnd.base_form.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		var params = new Object();
		params.Kolvo = this.form.findField('Kolvo').getValue();

		var dor_combo = this.form.findField('DrugOstatRegistry_id');
		var idx = dor_combo.getStore().findBy(function(rec) { return rec.get('DrugOstatRegistry_id') == dor_combo.getValue(); });
		if (idx >= 0) {
			var record = dor_combo.getStore().getAt(idx);

			if (record.get('PrepSeries_isDefect') == 1) {
				sw.swMsg.alert('Внимание!', 'Cерия забракована и ЛП не может быть отпущен по рецепту.');
				return false;
			}
			
			if (params.Kolvo > record.data.DrugOstatRegistry_Kolvo) {
				sw.swMsg.alert('Внимание', 'Количество отпускаемого медикамента превышает остаток!', function() {wnd.form.findField('Kolvo').focus(true);} );
			   return false; 
			} 
			if (record.get('inKm') == 2) {
				//  Если препарат имеет код маркировки	
				//  https://jira.is-mis.ru/browse/PROMEDWEB-7446
				sw.swMsg.alert('Внимание!', 'Препарат имеет код маркировки. <br/> Необходимо отсканировать упаковку Сканером Штрих-кода.');		
				this.hide();
				return false; 
			}
			if (record) {
				Ext.apply(params, record.data);
			}
		}

		if (this.callback && typeof this.callback == 'function') {
			this.callback(params);
		}
		this.hide();
	},
	setInformationValues: function() {
		var dor_combo = this.form.findField('DrugOstatRegistry_id');
		var str_id = dor_combo.getValue();
		var idx = dor_combo.getStore().findBy(function(rec) { return rec.get('DrugOstatRegistry_id') == str_id; });

		this.form.setValues({
			PrepSeries_Ser: null,
			PrepSeries_GodnDate: null,
			DocumentUcStr_Price: null
		});

		if (idx > -1) {
			var record = dor_combo.getStore().getAt(idx);
			this.form.setValues({
				PrepSeries_Ser: record.get('PrepSeries_Ser'),
				PrepSeries_GodnDate: record.get('PrepSeries_GodnDate'),
				DocumentUcStr_Price: record.get('DocumentUcStr_Price') > 0 ? (record.get('DocumentUcStr_Price')*1).toFixed(2) : null
			});
		}
	},
	show: function() {
		sw.Promed.swEvnReceptRlsProvideEditWindow.superclass.show.apply(this, arguments);

		var wnd = this;

		this.EvnRecept_id = null;
		this.EvnReceptGeneral_id = null;
		this.DrugOstatRegistry_id = null;
		this.EvnRecept_Kolvo = 0;
		this.MedService_id = null;
                this.WhsDocumentCostItemType_id = null;
		this.callback = Ext.emptyFn;
		this.subAccountType_id = null;

		if (!arguments[0]) {
			this.hide();
			return false;
		}

		if (this.callback && typeof this.callback == 'function') {
			this.callback = arguments[0].callback;
		}

		this.form.reset();
		
		if (arguments[0].params.WhsDocumentCostItemType && !arguments[0].params.WhsDocumentCostItemType_id)
			arguments[0].params.WhsDocumentCostItemType_id = arguments[0].params.WhsDocumentCostItemType;

		if (arguments[0].params) {
			if (arguments[0].params.EvnRecept_id && arguments[0].params.EvnRecept_id > 0) {
				this.EvnRecept_id = arguments[0].params.EvnRecept_id;
			}
			if (arguments[0].params.EvnReceptGeneral_id && arguments[0].params.EvnReceptGeneral_id > 0) {
				this.EvnReceptGeneral_id = arguments[0].params.EvnReceptGeneral_id;
			}
			if (arguments[0].params.DrugOstatRegistry_id && arguments[0].params.DrugOstatRegistry_id > 0) {
				this.DrugOstatRegistry_id = arguments[0].params.DrugOstatRegistry_id;
			}
			if (arguments[0].params.EvnRecept_Kolvo && arguments[0].params.EvnRecept_Kolvo > 0) {
				this.EvnRecept_Kolvo = arguments[0].params.EvnRecept_Kolvo*1;
			}
			if (arguments[0].params.MedService_id && arguments[0].params.MedService_id > 0) {
				this.MedService_id = arguments[0].params.MedService_id;
			}
			if (arguments[0].params.WhsDocumentCostItemType_id && arguments[0].params.WhsDocumentCostItemType_id > 0) {
				//console.log ('WhsDocumentCostItemType_id = ' + arguments[0].params.WhsDocumentCostItemType_id);
				this.WhsDocumentCostItemType_id = arguments[0].params.WhsDocumentCostItemType_id;
			}
			this.form.setValues(arguments[0].params);
			
			if (arguments[0].params.subAccountType_id) {
				this.subAccountType_id = arguments[0].params.subAccountType_id;
			}
		}

		var dor_combo = this.form.findField('DrugOstatRegistry_id');
		var str_id = dor_combo.getValue();
		dor_combo.getStore().load({
			params: {
				EvnRecept_id: wnd.EvnRecept_id,
				EvnReceptGeneral_id: wnd.EvnReceptGeneral_id,
				MedService_id: wnd.MedService_id,
				WhsDocumentCostItemType_id: wnd.WhsDocumentCostItemType_id,
				subAccountType_id: wnd.subAccountType_id
			},
			callback: function() {
				if (str_id>0) {
					var idx = dor_combo.getStore().findBy(function(rec) { return rec.get('DrugOstatRegistry_id') == str_id; });
					dor_combo.setValue(idx > -1 ? str_id : null);
					dor_combo.fireEvent('change', dor_combo, dor_combo.getValue());
				} else {
					dor_combo.getStore().each(function(record){
						if (record.get('DrugOstatRegistry_id') > 0 && record.get('PrepSeries_isDefect') != 1) {
							//установка количества по умолчанию
							var kolvo = record.get('DrugOstatRegistry_Kolvo') > wnd.EvnRecept_Kolvo ? wnd.EvnRecept_Kolvo : record.get('DrugOstatRegistry_Kolvo');
							wnd.form.findField('Kolvo').setValue(kolvo);
							//установка серии по умолчанию
							dor_combo.setValue(record.get('DrugOstatRegistry_id'));
							dor_combo.fireEvent('select', dor_combo, record, 0);
							return false;
						}
					});
				}
				wnd.setInformationValues();
			}
		});
	},
	title: 'Выбор медикамента',
	width: 800,
	//width: 500,
	height: 220,
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			//autoHeight: true,
			border: false,
			frame: true,
			layount: 'form',
			items: [{
				xtype: 'combo',
				hiddenName: 'DrugOstatRegistry_id',
				fieldLabel: 'Медикамент',
				//displayField: 'DrugShipment_Name',
                                displayField: 'Drug_Name',
				valueField: 'DrugOstatRegistry_id',
				enableKeyEvents: true,
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				allowBlank: false,
				anchor: '95%',
				listWidth: 1000,
				loadingText: 'Идет поиск...',
				minChars: 1,
				minLength: 1,
				minLengthText: 'Поле должно быть заполнено',
				mode: 'local',
				resizable: true,
				selectOnFocus: true,
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'DrugOstatRegistry_id'
					}, [
						{name: 'DrugOstatRegistry_id', mapping: 'DrugOstatRegistry_id'},
						//{name: 'storage_id', mapping: 'storage_id'},
						{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
						{name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id'},
						{name: 'DrugOstatRegistry_Kolvo', mapping: 'DrugOstatRegistry_Kolvo'},
						{name: 'DrugOstatRegistry_Cost', mapping: 'DrugOstatRegistry_Cost'},
						{name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price'},
						{name: 'DrugNds_id', mapping: 'DrugNds_id'},
						{name: 'DocumentUcStr_IsNDS', mapping: 'DocumentUcStr_IsNDS'},
						{name: 'PrepSeries_id', mapping: 'PrepSeries_id'},
						{name: 'PrepSeries_Ser', mapping: 'PrepSeries_Ser'},
						{name: 'PrepSeries_GodnDate', mapping: 'PrepSeries_GodnDate'},
                        //{name: 'GodnDate_Ctrl', mapping: 'GodnDate_Ctrl'},
						{name: 'PrepSeries_isDefect', mapping: 'PrepSeries_isDefect'},
						{name: 'DrugNds_id', mapping: 'DrugNds_id'},
						{name: 'DrugNds_Code', mapping: 'DrugNds_Code'},
						{name: 'DrugFinance_Name', mapping: 'DrugFinance_Name'},
						{name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name'},
						{name: 'Drug_Name', mapping: 'Drug_Name'},
						{name: 'Drug_NameDop', mapping: 'Drug_NameDop'},
						{name: 'DrugNomen_Code', mapping: 'DrugNomen_Code'},
						{name: 'Drug_ShortName', mapping: 'Drug_ShortName'},
						{name: 'DrugShipment_Name', mapping: 'DrugShipment_Name'},
						{name: 'Okei_NationSymbol', mapping: 'Okei_NationSymbol'},
						{name: 'DocumentUcStr_didDate', mapping: 'DocumentUcStr_didDate'},
						{name: 'Finance_and_CostItem', mapping: 'Finance_and_CostItem'},
						{name: 'Storage_Name', mapping: 'Storage_Name'}, 
						{name: 'WhsDocumentSupplySpecDrug_Coeff', mapping: 'WhsDocumentSupplySpecDrug_Coeff'}, 
						{name: 'inKm', mapping: 'inKm'}
					]),
					url: '/?c=Farmacy&m=getDrugOstatForProvide'
				}),
                                tpl: new Ext.XTemplate(
					
					'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
					//'<td style="padding: 2px; width: 40%;">Медикамент</td>',
					'<td style="padding: 2px; width: 30px; word-wrap:break-word;">Медикамент</td>',
                                        '<td style="padding: 2px; width: 10%;">Остаток</td>',
					'<td style="padding: 2px; width: 10%;">Дата прихода</td>',
                                        '<td style="padding: 2px; width: 10%;">Срок годности</td>',
					'<td style="padding: 2px; width: 10%;">Серия</td>',
					'<td style="padding: 2px; width: 10%;">Код ЛС</td>',
                                        '<td style="padding: 2px; width: 10%;">МО</td>',
										'<td style="padding: 2px; width: 10%;">Ист.фин.</td>',
                                        '<td style="padding: 2px; width: 10%;">Ст.расхода</td>',
					
			       /*
					'<table cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
					//'<td style="padding: 2px; width: 40%;">Медикамент</td>',
					'<td style="padding: 2px; ">Медикамент</td>',
					'<td>&nbsp;&nbsp;</td>',
                                        '<td style="padding: 2px; ">Остаток</td>',
					'<td style="padding: 2px; ">Дата прихода</td>',
                                        '<td style="padding: 2px; ">Срок годности</td>',
					'<td style="padding: 2px; ">Серия</td>',
					'<td style="padding: 2px; ">Код ЛС</td>',
                                        '<td style="padding: 2px; ">МО</td>',
                                        '<td style="padding: 2px; ">Ст.расхода</td>',
			       */
			       
										//'</tr><tpl for="."><tr class="x-combo-list-item" style="color:red;">',
                                        '</tr><tpl for="."><tr class="x-combo-list-item" {[(values.GodnDate_Ctrl == 1) ? "style=color:red;" : "style=color:black;"]}>',
                                        		
                                        
					//'<td style="padding: 2px; word-wrap:break-word!important;width:50px; overflow: hidden">{Drug_Name}&nbsp;</td>',
                                        '<td style="padding: 2px; width: 40%; text-overflow: clip;">{Drug_NameDop}&nbsp;</td>',
					//'<td></td>',
					'<td style="padding: 2px;text-align: center;">{DrugOstatRegistry_Kolvo}&nbsp;</td>',
					'<td style="padding: 2px;">{DocumentUcStr_didDate}&nbsp;</td>',
                                        '<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
					'<td style="padding: 2px;">{[(values.PrepSeries_isDefect == 1) ? "<font color=#ff0000>"+values.PrepSeries_Ser+"</font>" : values.PrepSeries_Ser]}&nbsp;</td>',
                                        '<td style="padding: 2px;">{DrugNomen_Code}&nbsp;</td>', 
					'<td style="padding: 2px;">{Lpu_Nick}&nbsp;</td>',
                    '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',             
					'<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
					
                                        //'</tr>',
                                        //'<table style="border: 0;"><tr><td>123</td></tr></table>',
                                        //'</tpl>'
                                        
                                        '</tr></tpl>',
					'</table>'
				),
                                
                /*
				tpl: new Ext.XTemplate(
					'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
					'<td style="padding: 2px; width: 10%;">Медикамент</td>',
                                        '<td style="padding: 2px; width: 10%;">МО</td>',
					'<td style="padding: 2px; width: 10%;">Партия</td>',
					'<td style="padding: 2px; width: 10%;">Серия</td>',
					'<td style="padding: 2px; width: 12%;">Срок годности</td>',
					'<td style="padding: 2px; width: 12%;">Цена</td>',
					'<td style="padding: 2px; width: 10%;">Ставка НДС</td>',
					'<td style="padding: 2px; width: 10%;">Остаток</td>',
					'<td style="padding: 2px; width: 13%;">Ист.фин.</td>',
					'<td style="padding: 2px; width: 13%;">Ст.расхода</td>',//values.PrepSeries_GodnDate <= "01.01.2017"
					//'</tr><tpl for="."><tr class="x-combo-list-item" style="color:red;">',
                                        '</tr><tpl for="."><tr class="x-combo-list-item" {[(values.GodnDate_Ctrl == 1) ? "style=color:red;" : "style=color:black;"]}>',//  Вставил...  {[(values.PrepSeries_GodnDate != "01.01.2017") ? style="color:red;"])>'		
                                        '<td style="padding: 2px; width: 10%; text-overflow: clip;">{Drug_ShortName}&nbsp;</td>',
					
                                         '<td style="padding: 2px;">{Lpu_Nick}&nbsp;</td>',
                                          
                                          
                                        '<td style="padding: 2px;">{DrugShipment_Name}&nbsp;</td>',
					'<td style="padding: 2px;">{[(values.PrepSeries_isDefect == 1) ? "<font color=#ff0000>"+values.PrepSeries_Ser+"</font>" : values.PrepSeries_Ser]}&nbsp;</td>',
					'<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
					'<td style="padding: 2px;">{DocumentUcStr_Price}&nbsp;</td>',
					'<td style="padding: 2px;">{DrugNds_Code}&nbsp;</td>',
					'<td style="padding: 2px; text-align: left;">{DrugOstatRegistry_Kolvo}&nbsp;</td>',
					'<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
					'<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
					
//                                        '</tr>',
//                                        '<table style="border: 0;"><tr><td>123</td></tr></table>',
//                                        '</tpl>'
//                                        
                                        '</tr></tpl>',
					'</table>'
				),
                */
				listeners: {
					'beforeselect': function() {
						return true;
					}.createDelegate(this),
					'select': function() {
						wnd.setInformationValues();
					}
				}
			}, {
				xtype: 'textfield',
				fieldLabel: 'Серия',
				name: 'PrepSeries_Ser',
				anchor: '95%',
				disabled: true
			}, {
				xtype: 'textfield',
				fieldLabel: 'Срок годности',
				name: 'PrepSeries_GodnDate',
				anchor: '95%',
				disabled: true
			}, {
				xtype: 'textfield',
				fieldLabel: 'Цена',
				name: 'DocumentUcStr_Price',
				anchor: '95%',
				disabled: true
			}, {
				name: 'Kolvo',
				fieldLabel: 'Количество',
				xtype: 'numberfield',
				allowNegative: false,
				anchor: '95%',
				allowBlank: false
			}]
		});

		Ext.apply(this, {
			layout: 'fit',
			buttons: [{
				handler: function() {
					this.doSelect();
				}.createDelegate(this),
				iconCls: 'save16',
				text: 'Сохранить'
			},
			{
				handler: function() {
					// Принудительная отсрочка
					Ext.getCmp('EvnReceptRlsProvideWindow').hide();
					this.hide();
					Ext.getCmp('swWorkPlaceDistributionPointWindow').putEvnReceptOnDelay();
				}.createDelegate(this),
				text: 'Поставить на отсрочку',
				iconCls: 'receipt-ondelay16',
				hidden: !getGlobalOptions().region.nick == 'ufa'
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],	
			frame: true,
 			items: [form]
		});

		this.base_form = form;
		this.form = form.getForm();

		sw.Promed.swEvnReceptRlsProvideEditWindow.superclass.initComponent.apply(this, arguments);
	}
});