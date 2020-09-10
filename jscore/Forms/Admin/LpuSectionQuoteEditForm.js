/**
* swLpuSectionQuoteEditForm - окно просмотра и редактирования планирования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009-2011 Swan Ltd.
* @author       
* @version      27.02.2011
* @comment      TABINDEX_LSQEF = 
*/
/*NO PARSE JSON*/

sw.Promed.swLpuSectionQuoteEditForm = Ext.extend(sw.Promed.BaseForm, {
	callback: Ext.emptyFn,
	layout: 'form',
	title:lang['planirovanie'],
	id: 'LpuSectionQuoteEditForm',
	width: 700,
	autoHeight: true,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lsqefOk',
		tabIndex: TABINDEX_LSQEF + 91,
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	}, 
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		tabIndex: TABINDEX_LSQEF + 92,
		handler: function(button, event) 
		{
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_FRMCANCEL,
		id: 'lsqefCancel',
		tabIndex: TABINDEX_LSQEF + 93,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}
	],
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	doSave: function()  {
		var form = this.findById('LpuSectionQuoteEditFormPanel');
		if (!form.getForm().isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		// проверка на дату начала действия 
		
		var date = form.getForm().findField('LpuSectionQuote_begDate').getValue().getFirstDateOfMonth();
		/*
		log(date);
		log(form.getForm().findField('LpuSectionQuote_begDate').getValue());
		*/
		if (date.dateFormat('d.m.Y') != form.getForm().findField('LpuSectionQuote_begDate').getValue().dateFormat('d.m.Y'))
		{
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getForm().findField('LpuSectionQuote_begDate').setValue(date);
				},
				icon: Ext.Msg.WARNING,
				msg: 'Поле "Дата начала действия" может быть только первым числом месяца. <br/> Поле было автоматически исправлено на первое число выбранного месяца. ',
				title: 'Внимание'
			});
			return false;
		}
		
		form.ownerCt.submit();
	},
	/** Функция относительно универсальной загрузки справочников выбор в которых осуществляется при вводе букв (цифр)
	 * Пример загрузки Usluga:
	 * loadSpr('Usluga_id', { where: "where UslugaType_id = 2 and Usluga_id = " + Usluga_id });
	 */
	loadSpr: function(field_name, params, callback)
	{
		var bf = this.MainPanel.getForm();
		var combo = bf.findField(field_name);
		var value = combo.getValue();
		combo.getStore().removeAll();
		combo.getStore().load(
		{
			callback: function() 
			{
				if (value>0 && !combo.getStore().getById(value)) 
				{
					value = null;
				}
				combo.setValue(value);
				combo.fireEvent('change', combo, value, 0);
				/*
				combo.getStore().each(function(record) 
				{
					if (record.data[field_name] == value)
					{
						combo.setValue(value);
						combo.fireEvent('select', combo, record, 0);
						combo.fireEvent('change', combo, value, 0);
					}
				});
				*/
				if (callback)
				{
					callback();
				}
			},
			params: params 
		});
	},
	returnFunc: function(owner, kid) {},
	show: function() {
		sw.Promed.swLpuSectionQuoteEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionQuoteEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		
		if (arguments[0].LpuSectionQuote_id)
			this.LpuSectionQuote_id = arguments[0].LpuSectionQuote_id;
		else 
			this.LpuSectionQuote_id = null;
		
		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else 
			this.Lpu_id = null;

		if (arguments[0].LpuUnitType_id)
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		else 
			this.LpuUnitType_id = null;
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		var form = this;
		var bf = form.findById('LpuSectionQuoteEditFormPanel').getForm();
		bf.reset();
		
		// Устанавливаем фильтрацию по полю
		// todo: вообще скорее всего здесь надо будет исправить фильтрацию на фильтрацию по sysnick
		bf.findField('LpuUnitType_id').setFilter([1, 2, 6, 7, 9, 13]);
		
		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['planirovanie_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['planirovanie_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['planirovanie_prosmotr']);
				break;
		}
		
		if (this.action=='view') 
		{
			bf.findField('LpuSectionQuote_Year').disable();
			bf.findField('LpuUnitType_id').disable();
			bf.findField('LpuSectionProfile_id').disable();
			bf.findField('LpuSectionQuote_Count').disable();
			form.buttons[0].disable();
		}
		else 
		{
			bf.findField('LpuSectionQuote_Year').enable();
			bf.findField('LpuUnitType_id').enable();
			bf.findField('LpuSectionProfile_id').enable();
			bf.findField('LpuSectionQuote_Count').enable();
			form.buttons[0].enable();
		}
		
		form.findById('lsqefLpu_id').setValue(this.Lpu_id);
		if (this.action!='add') 
		{
			bf.load(
			{
				url: C_LPUSECTIONQUOTE_GET,
				params: 
				{
					object: 'LpuSectionQuote',
					LpuSectionQuote_id: this.LpuSectionQuote_id,
					Lpu_id: this.Lpu_id
				},
				success: function () 
				{
					if (form.action!='view') 
					{
						// 
					}
					//form.loadSpr('LpuSectionProfile_id');
					//bf.findField('LpuSectionProfile_id').fireEvent('change', bf.findField('LpuSectionProfile_id'), bf.findField('LpuSectionProfile_id').getValue());
					bf.findField('LpuUnitType_id').fireEvent('change', bf.findField('LpuUnitType_id'), bf.findField('LpuUnitType_id').getValue());

					bf.findField('LpuSectionQuote_Year').focus(true, 100);
					loadMask.hide();
				},
				failure: function () 
				{
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				}
			});
		} 
		else 
		{
			//form.loadSpr('LpuSectionProfile_id');
			bf.findField('LpuUnitType_id').fireEvent('change', bf.findField('LpuUnitType_id'), bf.findField('LpuUnitType_id').getValue());
			bf.findField('LpuSectionQuote_Year').focus(true, 100);
			loadMask.hide();
		}
	},
	submit: function()
	{
		var form = this.findById('LpuSectionQuoteEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionQuoteEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		form.getForm().submit(
		{
			failure: function(result_form, action) 
			{
				if (action.result) 
				{
					if (action.result.Error_Code) 
					{
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
				loadMask.hide();
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.LpuSectionQuote_id) 
					{
						form.ownerCt.hide();
						form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuSectionQuote_id);
					}
					else
						Ext.Msg.alert('Ошибка ', 'При сохранении произошла ошибка!');
				}
				else
					Ext.Msg.alert('Ошибка ', 'При сохранении произошла ошибка!');
			}
		});
	},
	initComponent: function() 
	{
		this.MainPanel = new sw.Promed.FormPanel(
		{
			autoHeight: true,
			bodyStyle:'background:#DFE8F6;padding:5px;',
			id:'LpuSectionQuoteEditFormPanel',
			layout: 'form',
			frame: true,
			autoWidth: false,
			region: 'center',
			labelWidth: 130,
			items:
			[
			{
				name: 'LpuSectionQuote_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lsqefLpuSectionQuote_id'
			},
			{
				name: 'Lpu_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lsqefLpu_id'
			},
			{	// год
				xtype: 'numberfield',
				tabIndex: TABINDEX_LSQEF + 1,
				name: 'LpuSectionQuote_Year',
				id:  'lsqefLpuSectionQuote_Year',
				maxValue: 2030,
				minValue: 2011,
				autoCreate: {tag: "input", size:14, maxLength: "4", autocomplete: "off"},
				allowBlank: false,
				fieldLabel: lang['god'],
				value: new Date().getFullYear()
			},
			{ // Вид медицинской помощи
				anchor: '100%',
				tabIndex: TABINDEX_LSQEF + 2,
				name: 'LpuUnitType_id',
				xtype: 'swlpuunittypecombo',
				id: 'lsqefLpuUnitType_id',
				allowBlank:false,
				listeners:
				{
					change:  function(combo, newValue, oldValue)
					{
						/* https://redmine.swan.perm.ru/issues/14076
						var bf = combo.findForm().getForm();
						if (newValue>0) {
							var code = combo.getFieldValue('LpuUnitType_Code');
							var isCmp = (code==12); // СМП
							bf.findField('LpuSectionProfile_id').setContainerVisible(!isCmp);
							bf.findField('LpuSectionProfile_id').setValue(null);
							bf.findField('LpuSectionProfile_id').setAllowBlank(isCmp);
						} else {
							bf.findField('LpuSectionProfile_id').setContainerVisible(true);
							bf.findField('LpuSectionProfile_id').setAllowBlank(false);
						}
						
						this.syncSize();
						*/
						// Читаем список профилей для данного типа
						var bf = combo.findForm().getForm();
						var params = {
							LpuUnitType_id: newValue
						}
						if (newValue>0) {
							if (!oldValue || (oldValue == 13 && newValue!=13) || (oldValue != 13 && newValue==13)) { // перегружаем справочник только при изменении с обычного условия на СМП 
								this.loadSpr('LpuSectionProfile_id', params);
							}
							
						} else {
							bf.findField('LpuSectionProfile_id').getStore().removeAll();
						}
						
						
						
					}.createDelegate(this)
				}
			},
			{ // Профиль отделения
				anchor: '100%',
				tabIndex: TABINDEX_LSQEF + 3,
				name: 'LpuSectionProfile_id',
				xtype: 'swlpusectionprofilelitecombo',
				id: 'lsqefLpuSectionProfile_id',
				allowBlank:false,
				listeners: 
				{
					change:  function(combo, newValue, oldValue) 
					{
						if (newValue>0)
						{
							var code = combo.getFieldValue('LpuSectionProfile_Code');
							var bf = combo.findForm().getForm();
							bf.findField('LpuSectionQuote_Count').decimalPrecision = (code.toString().substr(0, 2) == '18')?2:0;
							bf.findField('LpuSectionQuote_Count').setValue(bf.findField('LpuSectionQuote_Count').getValue());
						}
					}
				}
			},
			{
				allowBlank: false,
				fieldLabel : lang['data_nachala_deystviya'],
				tabIndex: TABINDEX_LSQEF + 4,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'LpuSectionQuote_begDate',
				id: 'lsqefLpuSectionQuote_begDate'
			},
			{ // Ограничение
				xtype: 'numberfield',
				fieldLabel : lang['ogranichenie'],
				tabIndex: TABINDEX_LSQEF + 5,
				name: 'LpuSectionQuote_Count',
				id:  'lsqefLpuSectionQuote_Count',
				maxValue: 999999,
				minValue: 0,
				allowBlank: false,
				decimalPrecision: 0,
				autoCreate: {tag: "input", type: "text", size:"14", maxLength: "9", autocomplete: "off"}
				
			},
			{
				allowBlank: false,
				hiddenName:'QuoteUnitType_id',
				tabIndex: TABINDEX_LSQEF + 6,
				xtype: 'swquoteunittypecombo',
				id: 'lsqefQuoteUnitType_id'
			},
			{
				allowBlank: false,
				fieldLabel : lang['vid_oplatyi'],
				hiddenName:'PayType_id',
				tabIndex: TABINDEX_LSQEF + 7,
				xtype: 'swpaytypecombo',
				useCommonFilter: true,
				id: 'lsqefPayType_id'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//alert('success');
				}
			},
			[
				{ name: 'LpuSectionQuote_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSectionQuote_Year' },
				{ name: 'LpuUnitType_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuSectionQuote_begDate' },
				{ name: 'LpuSectionQuote_Count' },
				{ name: 'QuoteUnitType_id' },
				{ name: 'PayType_id' }
			]
			),
			url: C_LPUSECTIONQUOTE_SAVE
		});
		
		Ext.apply(this,
		{
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swLpuSectionQuoteEditForm.superclass.initComponent.apply(this, arguments);
	}
});