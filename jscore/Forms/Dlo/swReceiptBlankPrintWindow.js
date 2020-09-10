/**
* swReceiptBlankPrintWindow - окно печати бланков рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

sw.Promed.swReceiptBlankPrintWindow = Ext.extend(sw.Promed.BaseForm, {
	closeAction : "hide",
	title: 'Печать бланков рецептов',
	id : "ReceiptBlankPrintWindow",
	modal: false,
	maximizable: false,
	height: 240,
	width: 570,
	layout: 'border',
	doPrint: function() {
		var base_form = this.MainPanel.getForm();
		var win = this;
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var receptform_id = base_form.findField('ReceptForm_id').getValue();
		var numerator_ser = base_form.findField('Numerator_Ser').getValue();
		var tmpl = null;
		var tmplo = null; // обратная сторона
		var lpu_id = getGlobalOptions().lpu_id;
		var no_query = base_form.findField('Numerator_id').getFieldValue('NumeratorObject_Query');
		var recept_finance = '0';
		
		var params = {
			Numerator_id: base_form.findField('Numerator_id').getValue(),
			NumeratorObject_SysName: base_form.findField('Numerator_id').getStore().baseParams.NumeratorObject_SysName,
			Numerator_Num: base_form.findField('Numerator_Num').getValue(),
			asString: 1,
			num_count: base_form.findField('num_count').getValue()
		};
		
		if (no_query == 'WhsDocumentCostItemType_id=1') {
			recept_finance = '1';
		}
		else if (no_query == 'WhsDocumentCostItemType_id=2') {
			recept_finance = '2';
		}
		
		switch(receptform_id) {
			case 1:
				tmpl = 'EvnReceptPrint_bl148_1u04_2InA4'; // 148-1/у-04(л), 148-1/у-06(л)
				break;
			case 2:
				tmpl = 'EvnReceptPrint_bl1MI_2InA4'; // 1-МИ
				break;
			case 3:
				tmpl = 'EvnReceptPrint_bl107_1u_1InA5'; // 107-1/у
				tmplo = 'EvnReceptPrint_bl107_1u_1InA5Oborot';
				break;
			case 5:
				tmpl = 'EvnReceptPrint_bl148_1u88_1InA5'; // 148-1/у-88
				tmplo = 'EvnReceptPrint_bl148_1u88_1InA5Oborot';
				break;
			case 9:
				tmpl = 'EvnReceptPrint_bl148_1u04_2InA4_2019'; // 148-1/у-04(л)
				break;
		}
		
		if (!tmpl) {
			return false;
		}
		
		win.getLoadMask('Получение списка номеров').show();
		Ext.Ajax.request({
			url: '/?c=Numerator&m=getNumeratorNumList',
			params: params,
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success) {
					var res = Ext.util.JSON.decode(response.responseText);
					printBirt({
						'Report_FileName': tmpl + '.rptdesign',
						'Report_Params': 
							([1,2,9].in_array(receptform_id) ? '&paramLpu=' + lpu_id + '&paramReceptFinance=' + recept_finance : '') +
							'&paramNumCount=' + res.Numerator_Count + '&paramNumerSer=' + numerator_ser + '&paramNumerNumbers=' + res.Numerator_Nums,
						'Report_Format': 'pdf'
					});
					if (tmplo) {
						printBirt({
							'Report_FileName': tmplo + '.rptdesign',
							'Report_Params': 
								([1,2,9].in_array(receptform_id) ? '&paramLpu=' + lpu_id + '&paramReceptFinance=' + recept_finance : '') +
								'&paramNumCount=' + res.Numerator_Count + '&paramNumerSer=' + numerator_ser + '&paramNumerNumbers=' + res.Numerator_Nums,
							'Report_Format': 'pdf'
						});
					}
					win.hide();
				}
			}
		});
		
		return true;
	},
	setNumeratorFilter: function() {
		var win = this,
			base_form = win.MainPanel.getForm(),
			NumeratorObject_SysName = null,
			is_pref = base_form.findField('isPref').getValue(),
			numerator_combo = base_form.findField('Numerator_id'),
			NumeratorObject_Querys = null;
		
		if (!win.receptform_id) {
			return false;
		}
		
		switch(true) {
			case (is_pref == 2):
				NumeratorObject_SysName = 'EvnRecept';
				NumeratorObject_Querys = Ext.util.JSON.encode(['WhsDocumentCostItemType_id=1', 'WhsDocumentCostItemType_id=2']);
				break;
			case (win.receptform_id == 2):
				NumeratorObject_SysName = 'EvnReceptGeneral';
				NumeratorObject_Querys = Ext.util.JSON.encode(['ReceptForm_id=2']);
				break;
			case (win.receptform_id == 3):
				NumeratorObject_SysName = 'EvnReceptGeneral';
				NumeratorObject_Querys = Ext.util.JSON.encode(['ReceptForm_id=3']);
				break;
			case (win.receptform_id == 5):
				NumeratorObject_SysName = 'EvnReceptGeneral';
				NumeratorObject_Querys = Ext.util.JSON.encode(['ReceptForm_id=5']);
				break;
		}
		
		numerator_combo.clearValue();
		numerator_combo.getStore().removeAll();
		if (NumeratorObject_SysName) {
			numerator_combo.getStore().baseParams.NumeratorObject_SysName = NumeratorObject_SysName;
			numerator_combo.getStore().baseParams.NumeratorObject_Querys = NumeratorObject_Querys;
			win.getLoadMask(lang['poluchenie_spiska_numeratorov']).show();
			numerator_combo.getStore().load({
				params: {
					NumeratorObject_SysName: NumeratorObject_SysName,
					NumeratorObject_Querys: NumeratorObject_Querys,
					allowFuture: 1
				},
				callback: function () {
					win.getLoadMask().hide();

					if (numerator_combo.getStore().getCount() == 1) {
						numerator_combo.setValue(numerator_combo.getStore().getAt(0).get('Numerator_id'));
						numerator_combo.fireEvent('change', numerator_combo, numerator_combo.getValue());
					}
				}
			});
		}
		else {
			numerator_combo.fireEvent('change', numerator_combo, numerator_combo.getValue());
		}
		
	},
	setNumeratorNumF: function() {
		var base_form = this.MainPanel.getForm();
		var combo = base_form.findField('Numerator_id');
		var num = base_form.findField('Numerator_Num').getValue().toString();
		if (!Ext.isEmpty(num)) {
			if (!Ext.isEmpty(combo.getFieldValue('Numerator_NumLen'))) {
				while(num.length < combo.getFieldValue('Numerator_NumLen')) {
					num = '0' + num;
				}
			}
			num = combo.getFieldValue('Numerator_PreNum') + num + combo.getFieldValue('Numerator_PostNum');
			base_form.findField('Numerator_NumF').setValue(num);
		}
		else {
			base_form.findField('Numerator_NumF').setValue(null);
		}
		
	},
	show: function() {
		sw.Promed.swReceiptBlankPrintWindow.superclass.show.apply(this, arguments);

		var base_form = this.MainPanel.getForm();
		base_form.reset();
		this.receptform_id = null;
		
		base_form.findField('isPref').getStore().load();
		base_form.findField('ReceptForm_id').lastQuery = '';
		base_form.findField('ReceptForm_id').getStore().filterBy(function(rec){
			return rec.get('ReceptForm_id').inlist([9,2,3,5]);
		});
		base_form.findField('ReceptForm_id').fireEvent('change', base_form.findField('ReceptForm_id'), base_form.findField('ReceptForm_id').getValue());
		
	},
	initComponent : function() {
	
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'ReceiptBlankPrintForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 140,
			items:
			[{
				fieldLabel: 'Форма рецепта',
				comboSubject: 'ReceptForm',
				hiddenName: 'ReceptForm_id',
				xtype: 'swcommonsprcombo',
				width: 370,
				allowBlank: false,
				editable: false,
				showCodefield: false,
				listeners: {
					'select': function(combo, record, index) {
						combo.fireEvent('change', combo, record.get('ReceptForm_id'));
					},
					'change': function (combo, newValue, oldValue) {
						if (win.receptform_id && win.receptform_id == newValue) {
							return false;
						}
						
						win.receptform_id = newValue;
						
						var base_form = win.MainPanel.getForm();
						
						switch(newValue) {
							case 1:
							case 2:
							case 9:
								NumeratorObject_SysName = 'EvnRecept';
								NumeratorObject_Query = null;
								break;
							case 3:
								NumeratorObject_SysName = 'EvnReceptGeneral';
								NumeratorObject_Query = 'ReceptForm_id=3';
								break;
							case 5:
								NumeratorObject_SysName = 'EvnReceptGeneral';
								NumeratorObject_Query = 'ReceptForm_id=5';
						}
						
						if ([1,2,9].in_array(newValue)) {
							base_form.findField('isPref').showContainer();
							base_form.findField('isPref').setAllowBlank(false);
							if (newValue == 1 || newValue == 9) {
								base_form.findField('isPref').setValue(2);
								base_form.findField('isPref').disable();
							}
							else {
								base_form.findField('isPref').enable();
							}
						}
						else {
							base_form.findField('isPref').setAllowBlank(true);
							base_form.findField('isPref').setValue(null);
							base_form.findField('isPref').hideContainer();
						}
						
						win.setNumeratorFilter();
					}
				},
				value: 9
			}, {
				hiddenName: 'isPref',
				fieldLabel: 'Льготный рецепт',
				allowBlank: true,
				width: 120,
				xtype: 'swyesnocombo',
				listeners: {
					'select': function(combo, record, index) {
						combo.fireEvent('change', combo, record.get('ReceptForm_id'));
					},
					'change': function (combo, newValue, oldValue) {
						win.setNumeratorFilter();
					}
				}
			}, {
				hiddenName: 'Numerator_id',
				fieldLabel: lang['numerator'],
				width: 370,
				allowBlank: false,
				mode: 'local',
				listeners: {
					'select': function(combo, record, index) {
						combo.fireEvent('change', combo, record.get('Numerator_id'));
					},
					'change': function (combo, newValue, oldValue) {
						var base_form = win.MainPanel.getForm();

						if (newValue != win.lastNumerator_id) {
							
							if (Ext.isEmpty(newValue)) {
								base_form.findField('Numerator_Ser').setValue(null);
								base_form.findField('Numerator_Num').setValue(null);
								base_form.findField('Numerator_NumF').setValue(null);
							}
							else {
								if (!Ext.isEmpty(combo.getFieldValue('Numerator_Ser'))) {
									base_form.findField('Numerator_Ser').setValue(combo.getFieldValue('Numerator_Ser'));
									base_form.findField('Numerator_Ser').disable();
								}
								else {
									base_form.findField('Numerator_Ser').setValue(null);
									base_form.findField('Numerator_Ser').enable();
								}
								base_form.findField('Numerator_Num').minValue = combo.getFieldValue('Numerator_Num')+1;
								base_form.findField('Numerator_Num').setValue(combo.getFieldValue('Numerator_Num')+1);
								win.setNumeratorNumF();
							}
						}
					}
				},
				store: new Ext.data.JsonStore({
					autoLoad: false,
					url: '/?c=Numerator&m=getActiveNumeratorList',
					fields: [
						{ name: 'Numerator_id', type: 'int' },
						{ name: 'Numerator_Name', type: 'string' },
						{ name: 'Numerator_Num', type: 'int' },
						{ name: 'Numerator_Ser', type: 'string' },
						{ name: 'Numerator_NumLen', type: 'int' },
						{ name: 'Numerator_PreNum', type: 'string' },
						{ name: 'Numerator_PostNum', type: 'string' },
						{ name: 'NumeratorObject_Query', type: 'string' }
					],
					key: 'Numerator_id'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{Numerator_Name}&nbsp;',
					'</div></tpl>'
				),
				triggerAction: 'all',
				valueField: 'Numerator_id',
				displayField: 'Numerator_Name',
				xtype: 'combo'
			}, {
				fieldLabel: 'Серия',
				width: 120,
				allowBlank: false,
				name: 'Numerator_Ser',
				xtype: 'textfield'
			}, {
				xtype: 'panel',
				layout: 'column',
				border: false,
				items:
				[{
					xtype: 'panel',
					layout: 'form',
					labelWidth: 140,
					columnWidth: 0.52,
					border: false,
					items: 
					[{
						fieldLabel: 'Номер первого бланка',
						allowBlank: false,
						width: 120,
						name: 'Numerator_Num',
						xtype: 'numberfield',
						listeners: {
							'change': function () {
								win.setNumeratorNumF();
							}
						}
					}]
				},
				{
					xtype: 'panel',
					border: false,
					columnWidth: 0.47,
					labelWidth: 85,
					layout: 'form',
					items: 
					[{
						fieldLabel: 'Номер бланка',
						width: 150,
						disabled: true,
						name: 'Numerator_NumF',
						xtype: 'textfield'
					}]
				}]
			}, {
				fieldLabel: 'Количество бланков',
				name: 'num_count',
				maxValue: 10,
				minValue: 1,
				width: 50,
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield'
			}]
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
			buttons:
			[{
				text: 'Выбрать',
				iconCls: 'ok16',
				handler: function()
				{
					this.doPrint();
				}.createDelegate(this)
			},
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}
				},
				key: [ Ext.EventObject.J ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swReceiptBlankPrintWindow.superclass.initComponent.apply(this, arguments);
	}
});