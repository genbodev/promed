/**
 * swMorbusPalliatEditWindow - Паллиативная помощь
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.MorbusPalliat
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 */
Ext6.define('common.MorbusPalliat.swMorbusPalliatEditWindow', {
	/* свойства */
	requires: [
		'common.EMK.PersonInfoPanel',
		'common.MorbusPalliat.PalliatFamilyCarePanel',
		'common.MorbusPalliat.MedProductCardPanel',
	],
	alias: 'widget.swMorbusPalliatEditWindow',
    autoShow: false,
	closable: true,
	MorbusType_SysNick: 'palliat',
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'border',
	refId: 'MorbusPalliateditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Специфика / Паллиативная помощь',
	width: 830,
	height: 800,

	/* методы */
	save: function () {
		var
			base_form = this.FormPanel.getForm(),
			me = this,
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', ERR_INVFIELDS_MSG);
			return false;
		}
		
		params = base_form.getValues();
		
		params.PalliatFamilyCare = Ext6.encode(this.PalliatFamilyCarePanel.getValues());
		params.MethodRaspiratAssist = base_form.findField('MethodRaspiratAssist').getValue();
		params.MedProductCard = Ext6.encode(this.MedProductCardPanel.getValues());
		
		params.MorbusPalliat_IsFamCare = base_form.findField('MorbusPalliat_IsFamCare').getValue() ? 'on' : 'off';
		params.MorbusPalliat_IsTIR = base_form.findField('MorbusPalliat_IsTIR').getValue() ? 'on' : 'off';
		params.MorbusPalliat_IsIVL = base_form.findField('MorbusPalliat_IsIVL').getValue() ? 'on' : 'off';
		
		params.MainSyndrome = base_form.findField('MainSyndrome').getValue();

		var tir_ids = base_form.findField('TechnicInstrumRehab').getValue().split(',');
		var tir = [];
		tir_ids.forEach(function(id) {
			if (!Ext6.isEmpty(id)) {
				var date = base_form.findField('TIRDate'+id).getValue();
				date = date ? Ext6.util.Format.date(date, 'Y-m-d') : null;
				tir.push({id: id, date: date});
			}
		});

		params.TechnicInstrumRehab = Ext6.encode(tir);
		
		if (Ext6.isEmpty(params.MainSyndrome)) {
			sw.swMsg.alert('Ошибка', 'Не выбран ведущий синдром');
			return false;
		}
		
		Ext6.Object.each(params, function(key, value) {
			if (value == 'null') params[key] = '';
		});

		me.mask(LOAD_WAIT_SAVE);
		
		Ext6.Ajax.request({
			url:'/?c=MorbusPalliat&m=save',
			params: params,
            success: function (result, action) {
				me.unmask();

				me.callback();
				me.hide();
            },
			failure: function(form, action) {
				me.unmask();
			}
		});
	},

	loadLpuList: function() {
		var me = this;
		var base_form = me.FormPanel.getForm();

		var palliative_type_combo = base_form.findField('PalliativeType_id');
		var palliative_type_id = palliative_type_combo.getValue();

		var date_field = base_form.findField('MorbusPalliat_DiagDate');
		var date = Ext6.util.Format.date(date_field.getValue(), 'd.m.Y');

		var lpu_combo = base_form.findField('Lpu_sid');
		var lpu_sid = lpu_combo.getValue();

		if (Ext6.isEmpty(palliative_type_id)) {
			lpu_combo.setValue(null);
			lpu_combo.getStore().removeAll();
		} else {
			lpu_combo.getStore().load({
				params: {
					PalliativeType_id: palliative_type_id,
					Date: date
				},
				callback: function() {
					if (lpu_combo.getStore().getById(lpu_sid)) {
						lpu_combo.setValue(lpu_sid);
					} else {
						lpu_combo.setValue(null);
					}
					lpu_combo.fireEvent('change', lpu_combo, lpu_sid);
				}
			});
		}
	},
	
    setFieldsDisabled: function(d) {
        this.FormPanel.items.each(function(f){
            if (f && !f.xtype.inlist(['hidden', 'fieldset', 'panel'])) {
                f.setDisabled(d);
            }
			if (f.xtype == 'fieldset') {
				f.items.each(function(f1){
					if (!f1.xtype.inlist(['fieldset', 'panel'])) f1.setDisabled(d);
					if (f1.xtype == 'panel') {
						f1.items.each(function(f2){
							if (!f2.xtype.inlist(['fieldset', 'panel'])) f2.setDisabled(d);
						});
					}
				});
			}
        });
		this.PalliatFamilyCarePanel.setDisabled(d);
		this.MedProductCardPanel.setDisabled(d);
		this.queryById(this.id+'-save-btn').setVisible(!d);
    },
	
	onSprLoad: function(arguments) {
		
		var me = this;
		var base_form = this.FormPanel.getForm();
		
		if (!arguments[0] || !arguments[0].Person_id && !arguments[0].MorbusPalliat_id) {
			sw.swMsg.show({
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
					this.hide();
				}
			});
		}
		
		me.Person_id = arguments[0].Person_id;
		me.MorbusPalliat_id = arguments[0].MorbusPalliat_id;
		me.Evn_id = arguments[0].Evn_id;
		me.callback = arguments[0].callback || Ext6.emptyFn;
		me.action = arguments[0].action || 'add';
		
		me.PersonInfoPanel.load({
			Person_id: me.Person_id,
			Server_id: me.Server_id,
			PersonEvn_id: me.PersonEvn_id,
			noToolbar: true
		});
		
		me.isLoading = true;
		
		base_form.reset();
		
		me.PalliatFamilyCarePanel.reset();
		me.PalliatFamilyCarePanel.addCombo();
		
		var date = new Date();
		
		base_form.findField('MorbusType_SysNick').setValue(this.MorbusType_SysNick);
		base_form.findField('MorbusPalliat_DiagDate').setMaxValue(date);
		base_form.findField('MorbusPalliat_StomPrescrDate').setMaxValue(date);
		base_form.findField('MorbusPalliat_StomSetDate').setMinValue(null);
		base_form.findField('MorbusPalliat_StomSetDate').setMaxValue(date);
			
		var IsFamCareField = base_form.findField('MorbusPalliat_IsFamCare');
		
		me.mask(LOAD_WAIT);
		
		if (this.action == 'add') {
			me.setTitle('Специфика: Добавление');
			me.setFieldsDisabled(false);
			base_form.findField('Diag_id').setAllowBlank(false);
			base_form.setValues(arguments[0]);
			IsFamCareField.fireEvent('change', IsFamCareField, IsFamCareField.getValue());
			base_form.findField('TechnicInstrumRehab').fireEvent('change');
			me.loadLpuList();
		
			if (getRegionNick() == 'perm') {
				me.MedProductCardPanel.reset();
				me.MedProductCardPanel.addCombo();
			}
			
			me.unmask();
			me.isLoading = false;
		} 
		else {
			Ext6.Ajax.request({
				url: '/?c=MorbusPalliat&m=loadPalliatFamilyCareList',
				params: {
					MorbusPalliat_id: arguments[0].MorbusPalliat_id
				},
				callback: function(options, success, response) {
					var responseObj = Ext6.decode(response.responseText);
					
					if (responseObj.length) {
						me.PalliatFamilyCarePanel.reset();
						Ext6.each(responseObj, function(el) {
							me.PalliatFamilyCarePanel.addCombo(el);
						});
					}
				}
			});
			Ext6.Ajax.request({
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					me.unmask();
					me.isLoading = false;
				},
				params: {
					MorbusPalliat_id: arguments[0].MorbusPalliat_id,
					Evn_id: arguments[0].Evn_id
				},
				success: function (response) {
					var result = Ext6.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);

					IsFamCareField.fireEvent('change', IsFamCareField, IsFamCareField.getValue());

					me.loadLpuList();

					base_form.findField('MorbusPalliat_StomSetDate').setMinValue(base_form.findField('MorbusPalliat_StomPrescrDate').getValue());
					
					if (getRegionNick() == 'perm') {
						me.MedProductCardPanel.reset();
						me.MedProductCardPanel.baseParams.Lpu_did = result[0].Lpu_sid;
						if (result[0].MedProductCard.length) {
							Ext6.each(result[0].MedProductCard, function(el) {
								me.MedProductCardPanel.addCombo(el);
							});
						} else {
							me.MedProductCardPanel.addCombo();
						}
					}
						
					if (result[0].MainSyndrome) {
						var MainSyndrome = result[0].MainSyndrome.split(',');
						base_form.findField('MainSyndrome').setValue({'MainSyndrome': MainSyndrome});
					}
						
					if (result[0].MethodRaspiratAssist) {
						var MethodRaspiratAssist = result[0].MethodRaspiratAssist.split(',');
						base_form.findField('MethodRaspiratAssist').setValue({'MethodRaspiratAssist': MethodRaspiratAssist});
					}
						
					if (result[0].TechnicInstrumRehab) {
						var TechnicInstrumRehab = Ext6.decode(result[0].TechnicInstrumRehab);
						var TechnicInstrumRehabIds = [];
						Ext6.each(TechnicInstrumRehab, function(tir) {
							TechnicInstrumRehabIds.push(tir.id);
							base_form.findField('TIRDate'+tir.id).setValue(tir.date);
						});
						if (!!result[0].MorbusPalliat_TextTIR) TechnicInstrumRehabIds.push(9999);
						base_form.findField('TechnicInstrumRehab').setValue({'TechnicInstrumRehab': TechnicInstrumRehabIds});
					}
					
					base_form.findField('TechnicInstrumRehab').fireEvent('change');
					
					switch (me.action) {
						case 'edit':
							me.setFieldsDisabled(false);
							me.setTitle('Специфика: Редактирование');
							base_form.findField('Diag_id').disable();
							base_form.findField('Diag_id').setAllowBlank(false);
							break;
							
						case 'view':		
							me.setTitle('Специфика: Просмотр');		
							me.setFieldsDisabled(true);
							break;
					}

					if (me.action != 'view') {
						var change_condit_combo = base_form.findField('PalliatIndicatChangeCondit_id');
						var change_condit_index = change_condit_combo.getStore().find('PalliatIndicatChangeCondit_id', change_condit_combo.getValue());
						var change_condit_record = change_condit_combo.getStore().getAt(change_condit_index);
						change_condit_combo.fireEvent('select', change_condit_combo, change_condit_record, change_condit_index);
					}
		
					me.unmask();
					me.isLoading = false;
				},
				url: '/?c=MorbusPalliat&m=load'
			});
		}
	},

	show: function() {
		this.callParent(arguments);
	},

	/* конструктор */
    initComponent: function() {
        var me = this;

		me.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			buttonPanel: false,
			narrowPanel: true,
			border: true,
			bodyStyle: 'border-width: 0 0 1px 0;',
			userMedStaffFact: this.userMedStaffFact,
			ownerWin: this
		});
		
		me.PalliatFamilyCarePanel = Ext6.create('common.MorbusPalliat.PalliatFamilyCarePanel', {
			win: this,
			width: 748,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			labelWidth: 200
		});
		
		me.MedProductCardPanel = Ext6.create('common.MorbusPalliat.MedProductCardPanel', {
			win: this,
			width: 740,
			hidden: getRegionNick() != 'perm',
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			fieldWidth: 690,
			labelWidth: 200
		});

		me.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			userCls: 'vizitPanelEmk subFieldPanel',
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			items: [{
				name: 'MorbusPalliat_id',
				xtype: 'hidden',
				value: 0
			}, {
				name: 'Morbus_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusType_SysNick',
				xtype: 'hidden'
			}, {
				name: 'Evn_id',
				xtype: 'hidden'
			}, {
				triggerAction: 'all',
				fieldLabel: 'Диагноз',
				name: 'Diag_id',
				valueField: 'Diag_id',
				width: 700,
				xtype: 'swDiagCombo'
			}, {
				allowBlank: false,
				xtype: 'datefield',
				name: 'MorbusPalliat_DiagDate',
				fieldLabel: 'Дата установки диагноза',
				listeners: {
					'change': function(field, newValue, oldValue) {
						if (!me.isLoading) me.loadLpuList();
					}
				}
			}, {
				allowBlank: true,
				xtype: 'datefield',
				name: 'MorbusPalliat_VKDate',
				fieldLabel: 'Дата проведения ВК'
			}, {
				allowBlank: false,
				xtype: 'commonSprCombo',
				comboSubject: 'RecipientInformation',
				name: 'RecipientInformation_id',
				fieldLabel: 'Информирован о заболевании',
				width: 700
			}, {
				xtype: 'checkbox',
				name: 'MorbusPalliat_IsFamCare',
				uncheckedValue: 1, 
				inputValue: 2,
				boxLabel: 'Наличие родственников, имеющих возможность осуществлять уход за пациентом',
				listeners: {
					'change': function(field, checked) {
						me.PalliatFamilyCarePanel.setVisible(checked);
						me.PalliatFamilyCarePanel.setAllowBlank(!checked);
					}
				}
			},
			me.PalliatFamilyCarePanel,
			{
				allowBlank: false,
				xtype: 'commonSprCombo',
				comboSubject: 'PalliativeType',
				name: 'PalliativeType_id',
				fieldLabel: 'Условия оказания паллиативной помощи',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if (!me.isLoading) me.loadLpuList();
					}
				},
				width: 700
			}, {
				width: 700,
				allowBlank: false,
				name: 'Lpu_sid',
				id: 'MPEW_Lpu_sid',
				fieldLabel: 'МО оказания паллиативной помощи',
				triggerAction: 'all',
				valueField: 'Lpu_id',
				displayField: 'Lpu_Nick',
				queryMode: 'local',
				store: {
					fields: [
						{name: 'id', mapping: 'Lpu_id'},
						{name: 'Lpu_id', mapping: 'Lpu_id'},
						{name: 'Org_id', mapping: 'Org_id'},
						{name: 'Org_tid', mapping: 'Org_tid'},
						{name: 'Lpu_IsOblast', mapping: 'Lpu_IsOblast'},
						{name: 'Lpu_Name', mapping: 'Lpu_Name'},
						{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
						{name: 'Lpu_Ouz', mapping: 'Lpu_Ouz'},
						{name: 'Lpu_RegNomC', mapping: 'Lpu_RegNomC'},
						{name: 'Lpu_RegNomC2', mapping: 'Lpu_RegNomC2'},
						{name: 'Lpu_RegNomN2', mapping: 'Lpu_RegNomN2'},
						{name: 'Lpu_DloBegDate', mapping: 'Lpu_DloBegDate'},
						{name: 'Lpu_DloEndDate', mapping: 'Lpu_DloEndDate'},
						{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
						{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'},
						{name: 'LpuLevel_Code', mapping: 'LpuLevel_Code'},
						{name: 'Lpu_IsAccess', mapping: 'Lpu_IsAccess'},
						{name: 'Lpu_IsMse', mapping: 'Lpu_IsMse'}
					],
					proxy: {
						type: 'ajax',
						actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
						url: '/?c=MorbusPalliat&m=loadLpuList',
						reader: {type: 'json'}
					},
					sorters: {
						property: 'Lpu_Nick',
						direction: 'ASC'
					}
				},
				listeners: {
					change: function(combo, newValue, oldValue) {
						var base_form = me.FormPanel.getForm();
						var med_product_card_combo = base_form.findField('MedProductCard_id');
						if(getRegionNick() == 'perm' && !me.isLoading) {
							me.MedProductCardPanel.baseParams.Lpu_did = newValue;
							me.MedProductCardPanel.reloadCombo();
						} else {
							med_product_card_combo.getStore().load({params: {Lpu_did: newValue}});
						}
					}
				},
				xtype: 'baseCombobox'
			}, {
				fieldLabel: 'Ведущий синдром',
				xtype: 'checkboxgroup',
				width: 700,
				columns: [0.6, 0.4],
				name: 'MainSyndrome',
				items: [
					{boxLabel: 'Хронический болевой синдром', inputValue: 1},
					{boxLabel: 'Одышка', inputValue: 2},
					{boxLabel: 'Отеки', inputValue: 3},
					{boxLabel: 'Слабость', inputValue: 4},
					{boxLabel: 'Прогрессирование заболевания', inputValue: 5},
					{boxLabel: 'Тошнота', inputValue: 6},
					{boxLabel: 'Рвота', inputValue: 7},
					{boxLabel: 'Запор', inputValue: 8},
					{boxLabel: 'Асцит', inputValue: 9},
					{boxLabel: 'Другое', inputValue: 10},
				],
				getValue: function() {
					var out = [];
					this.items.each(function(item){
						if (item.checked) out.push(item.inputValue);
					});
					return out.join(',');
				}
			}, {
				fieldLabel: 'Степень выраженности стойких нарушений организма',
				allowBlank: false,
				name: 'ViolationsDegreeType_id',
				xtype: 'commonSprCombo',
				width: 700,
				displayCode: false,
				comboSubject: 'ViolationsDegreeType'
			}, {
				fieldLabel: 'Нуждается в обезболивании',
				name: 'AnesthesiaType_id',
				onLoadStore: function() {
					var index = this.getStore().findBy(function(record, id) {
						if ( record.get('AnesthesiaType_id') == -1 )
							return true;
						else
							return false;
					});

					if (index < 0) {
						this.getStore().loadData([{
							AnesthesiaType_id: -1,
							AnesthesiaType_Code: 0,
							AnesthesiaType_Name: 'Нет'
						}], true);
					}
				},
				xtype: 'commonSprCombo',
				width: 700,
				displayCode: false,
				comboSubject: 'AnesthesiaType'
			}, {
				allowBlank: false,
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				fieldLabel: 'Находится на зондовом питании',
				displayCode: false,
				name: 'MorbusPalliat_IsZond'
			}, {
				xtype: 'datefield',
				name: 'MorbusPalliat_StomPrescrDate',
				fieldLabel: 'Дата назначения установки Стомы',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = me.FormPanel.getForm();
						base_form.findField('MorbusPalliat_StomSetDate').setMinValue(newValue);
					}
				}
			}, {
				xtype: 'datefield',
				name: 'MorbusPalliat_StomSetDate',
				fieldLabel: 'Дата установки Стомы'
			}, {
				xtype: 'checkbox',
				name: 'MorbusPalliat_IsIVL',
				uncheckedValue: 1, 
				inputValue: 2,
				boxLabel: 'Наличие показаний к длительной респираторной поддержке',
				hideLabel: true,
				listeners: {
					'change': function(field, checked) {
						var fieldSet = Ext6.getCmp('MPEW_RaspiratAssistFieldSet');
						if (fieldSet) {
							fieldSet.setVisible(checked);
						}
					}
				}
			}, {
				id: 'MPEW_RaspiratAssistFieldSet',
				xtype: 'fieldset',
				title: 'Факты оказания респираторной поддержки',
				style: 'padding: 5px 10px 0 10px;',
				autoHeight: true,
				hidden: true,
				defaults: {
					labelWidth: 280,
				},
				items: [{
					xtype: 'swDateRangeField',
					width: 500,
					name: 'MorbusPalliat_VLDateRange',
					fieldLabel: 'Период оказания респираторной поддержки',
				}, {
					fieldLabel: 'Метод респираторной поддержки',
					xtype: 'checkboxgroup',
					width: 700,
					columns: 1,
					name: 'MethodRaspiratAssist',
					items: [
						{boxLabel: 'Применение аппаратов неинвазивной вентиляции легких', inputValue: 3},
						{boxLabel: 'Иные методы респираторной поддержки', inputValue: 5},
						{boxLabel: 'Применение аппаратов инвазивной вентиляции легких', inputValue: 4}
					],
					getValue: function() {
						var out = [];
						this.items.each(function(item){
							if (item.checked) out.push(item.inputValue);
						});
						return out.join(',');
					}
				}, 
				this.MedProductCardPanel,
				{
					xtype: 'baseCombobox',
					hidden: getRegionNick() == 'perm',
					displayField: 'MedProductClass_Name',
					valueField: 'MedProductCard_id',
					name: 'MedProductCard_id',
					fieldLabel: 'Оборудование',
					store: {
						fields: [
							{name: 'MedProductCard_id', type:'int'},
							{name: 'MedProductClass_id', type:'int'},
							{name: 'MedProductClass_Name', type:'string'}
						],
						proxy: {
							type: 'ajax',
							actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
							url: '/?c=MorbusPalliat&m=loadMedProductCardList',
							reader: {type: 'json'}
						},
						sorters: {
							property: 'MedProductClass_Name',
							direction: 'ASC'
						}
					},
					width: 700
				}]
			}, {
				xtype: 'checkbox',
				name: 'MorbusPalliat_IsTIR',
				uncheckedValue: 1, 
				inputValue: 2,
				boxLabel: 'Необходимость обеспечения ТСР, медицинскими изделиями',
				hideLabel: true,
				listeners: {
					'change': function(field, checked) {
						var fieldSet = Ext6.getCmp('MPEW_TIRFieldSet');
						if (fieldSet) {
							fieldSet.setVisible(checked);
						}
					}
				}
			}, {
				id: 'MPEW_TIRFieldSet',
				xtype: 'fieldset',
				title: 'Обеспечение техническими средствами реабилитации на дому',
				padding: '10',
				autoHeight: true,
				hidden: true,
				defaults: {
					labelWidth: 180,
				},
				items: [{
					xtype: 'datefield',
					name: 'MorbusPalliat_VKTIRDate',
					fieldLabel: 'Дата проведения ВК по ТСР'
				}, {
					layout: 'column',
					border: false,
					padding: '10 0 0 0',
					items: [{
						labelWidth: 130,
						fieldLabel: 'Наименование ТСР',
						xtype: 'checkboxgroup',
						width: 360,
						columns: 1,
						name: 'TechnicInstrumRehab',
						items: [
							{name: 'TechnicInstrumRehab', boxLabel: 'Кресло-каталка', inputValue: 1},
							{name: 'TechnicInstrumRehab', boxLabel: 'Стульчак', inputValue: 2},
							{name: 'TechnicInstrumRehab', boxLabel: 'Аспиратор', inputValue: 3},
							{name: 'TechnicInstrumRehab', boxLabel: 'Мешок Амбу', inputValue: 4},
							{name: 'TechnicInstrumRehab', boxLabel: 'Функциональная кровать', inputValue: 5},
							{name: 'TechnicInstrumRehab', boxLabel: 'Матрац пртивопролежневый', inputValue: 6},
							{name: 'TechnicInstrumRehab', boxLabel: 'Вертикализатор', inputValue: 7},
							{name: 'TechnicInstrumRehab', boxLabel: 'Откашливатель', inputValue: 8},
							{name: 'TechnicInstrumRehab', boxLabel: 'Кислородный концентратор', inputValue: 9},
							{name: 'TechnicInstrumRehab', boxLabel: 'Аппарат ИВЛ', inputValue: 10},
							{name: 'TechnicInstrumRehab', boxLabel: 'Иное', inputValue: 9999}
						],
						getValue: function() {
							var out = [];
							this.items.each(function(item){
								if (item.checked) out.push(item.inputValue);
							});
							return out.join(',');
						},
						listeners: {
							change: function() {
								var form = me.FormPanel.getForm();
								this.items.each(function(item){
									var dateField = form.findField('TIRDate'+item.inputValue);
									if (dateField) {
										if (!item.checked) dateField.setValue(null);
										dateField.setDisabled(!item.checked || me.action == 'view');
									}
									if (item.inputValue == 9999) {
										var TextTIRField = Ext6.getCmp('MPEW_MorbusPalliat_TextTIR');
										if (!item.checked) TextTIRField.setValue('');
										TextTIRField.setVisible(item.checked);
										TextTIRField.setAllowBlank(!item.checked);
									}
								});
							}
						}
					}, {
						border: false,
						width: 300,
						cls: 'TechnicInstrumRehabDates',
						defaults: {
							labelWidth: 150,
							fieldLabel: ' ',
							labelSeparator: '',
							width: 300
						},
						items: [{
							xtype: 'datefield',
							name: 'TIRDate1',
							labelSeparator: ':',
							fieldLabel: 'Дата обеспечения ТСР'
						}, {
							xtype: 'datefield',
							name: 'TIRDate2',
						}, {
							xtype: 'datefield',
							name: 'TIRDate3'
						}, {
							xtype: 'datefield',
							name: 'TIRDate4'
						}, {
							xtype: 'datefield',
							name: 'TIRDate5'
						}, {
							xtype: 'datefield',
							name: 'TIRDate6'
						}, {
							xtype: 'datefield',
							name: 'TIRDate7'
						}, {
							xtype: 'datefield',
							name: 'TIRDate8'
						}, {
							xtype: 'datefield',
							name: 'TIRDate9'
						}, {
							xtype: 'datefield',
							name: 'TIRDate10'
						}, {
							xtype: 'datefield',
							name: 'TIRDate9999'
						}]
					}]
				}, {
					hideLabel: true,
					style: 'left: 205px; top: -43px; margin-bottom: -27px; position: relative;',
					xtype: 'textfield',
					width: 200,
					name: 'MorbusPalliat_TextTIR',
					id: 'MPEW_MorbusPalliat_TextTIR'
				}]
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'PalliatIndicatChangeCondit',
				name: 'PalliatIndicatChangeCondit_id',
				labelWidth: 275,
				fieldLabel: 'Показания к изменению условий оказания паллиативной медицинской помощи',
				listeners: {
					'select': function(combo, record, index) {
						var base_form = me.FormPanel.getForm();

						if (record && record.get('PalliatIndicatChangeCondit_Code') == 4) {
							base_form.findField('MorbusPalliat_OtherIndicatChangeCondit').enable();
						} else {
							base_form.findField('MorbusPalliat_OtherIndicatChangeCondit').disable();
							base_form.findField('MorbusPalliat_OtherIndicatChangeCondit').setValue('');
						}
					}
				},
				width: 700
			}, {
				xtype: 'textfield',
				labelWidth: 275,
				name: 'MorbusPalliat_OtherIndicatChangeCondit',
				fieldLabel: 'Другие показания',
				width: 700
			}, {
				xtype: 'datefield',
				labelWidth: 275,
				name: 'MorbusPalliat_ChangeConditDate',
				fieldLabel: 'Дата изменения условий оказания паллиативной медицинской помощи'
			}, {
				xtype: 'fieldset',
				padding: '5 10 10 10;',
				title: 'Перевод в учреждение социальной защиты населения',
				defaults: {
					labelWidth: 275
				},
				items: [{
					xtype: 'datefield',
					name: 'MorbusPalliat_SocialProtDate',
					fieldLabel: 'Дата перевода в учреждение соц. защиты'
				}, {
					xtype: 'textfield',
					name: 'MorbusPalliat_SocialProt',
					fieldLabel: 'Учреждение соц. защиты',
					width: 680
				}]
			}]
		});

        Ext6.apply(me, {
			items: [
				me.PersonInfoPanel, {
					region: 'center',
					flex: 400,
					scrollable: true,
					border: false,
					items: [
						me.FormPanel
					]
				}
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					me.hide();
				}
			},{
				xtype: 'SubmitButton',
				id: me.getId()+'-save-btn',
				handler:function () {
					me.save();
				}
			}]
		});

		this.callParent(arguments);
    }
});