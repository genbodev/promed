Ext6.define('common.EMK.EvnPLDispDop.view.ConsentPanel', {
	extend: 'swPanel',
	requires: [
		'common.EMK.EvnPLDispDop.controller.ConsentController', 
		'common.EMK.EvnPLDispDop.store.ConsentStore',
	],
	alias: 'widget.EvnPLDispDop_ConsentPanel',
	userCls: 'panel-with-tree-dots accordion-panel-window',
	title: 'Информированное добровольное согласие. Этап 1',
	controller: 'EvnPLDispDop13ConsentController',
	ownerPanel: {},
	bodyPadding: 10,
	getField: function(name) {
		return this.ConsentForm.getForm().findField(name);
	},
	getGrid: function() {
		return this.ConsentGrid;
	},
	//~ load: function() {
		//~ var me = this,
			//~ base_form = me.ConsentForm.getForm();
		//~ base_form.findField('Lpu_mid').getStore().load();
		//~ base_form.findField('PayType_id').getStore().load();
	//~ },
	initComponent: function() {
		var me = this;
		
		me.ConsentGrid = Ext6.create('Ext6.grid.Panel', {//таблица согласий/услуг
			//xtype: 'cell-editing',
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			viewConfig:{
				markDirty:false,
				getRowClass: function (record, rowIndex) {
					//~ var c = record.get('DopDispInfoConsent_IsImpossible');
					//~ if (c == 2) {
						//~ return 'x-item-disabled';
					//~ } else return '';
				}
			},
			bind: {
				store: '{ConsentStore}'
			},
			columns: [
				{ dataIndex: 'DopDispInfoConsent_id', hidden: true },
				{ dataIndex: 'SurveyTypeLink_id', hidden: true },
				{ dataIndex: 'SurveyTypeLink_IsNeedUsluga', hidden: true },
				{ dataIndex: 'SurveyType_Code', hidden: true },
				{ dataIndex: 'SurveyTypeLink_IsDel', hidden: true },
				{ dataIndex: 'SurveyTypeLink_IsUslPack', hidden: true },
				{ dataIndex: 'DopDispInfoConsent_IsAgeCorrect', hidden: true },
				{ dataIndex: 'SurveyType_isVizit ', hidden: true },
				//{ dataIndex: 'EvnUsluga_Date', hidden: true, type: 'date', formatter: 'date("d.m.Y")' },
				{ dataIndex: 'Lpu_Nick', hidden: true },
				
				{ dataIndex: 'SurveyType_Name', type: 'string', text: 'Осмотр,<br>исследование', flex: 1 },
				
				{ dataIndex: 'DopDispInfoConsent_IsImpossible', hidden: true },
				{ dataIndex: 'DopDispInfoConsent_IsImpossibleCheck', xtype: 'checkcolumn', header: 'Невозможно по<br>показаниям', width: 100, sortable: false,
					bind: {
						disabled: '{action == "view"}'
					},
					renderer : function(value, meta) {
						if(meta.record.get('DopDispInfoConsent_IsImpossible')=='hidden') {
							return '';
						}
						
						var cssPrefix = Ext6.baseCSSPrefix,
							cls = [cssPrefix + 'grid-checkcolumn'];

						if (this.disabled) {
							meta.tdCls += ' ' + this.disabledCls;
						}
						if (1*value>0) {
							cls.push(cssPrefix + 'grid-checkcolumn-checked');
						}
						
						return '<span class="checkcolumnlabelbox ' + cls.join(' ') + '"></span>';
					},
					listeners: {
						beforecheckchange: function(column, rowIndex, newValue, record, eOpts ) {
							
						},
						checkchange: function(column, rowIndex, newValue, record, eOpts ) {
							if(newValue) {
								record.set('DopDispInfoConsent_IsAgree', false);
								record.set('DopDispInfoConsent_IsEarlier', false);
							}
							if(record.get('DopDispInfoConsent_IsImpossible') != 'hidden')
								record.set('DopDispInfoConsent_IsImpossible', newValue ? "1" : "0");
							me.getController().onChangeConsentRecord(record);
						}
					}
				},
				{ dataIndex: 'DopDispInfoConsent_IsAgree', xtype: 'checkcolumn', header: 'Согласие<br>пациента', width: 80, sortable: false,
					bind: {
						disabled: '{action == "view"}'
					},
					listeners: {
						beforecheckchange: function(column, rowIndex, newValue, record, eOpts ) {
							
						},
						checkchange: function(column, rowIndex, newValue, record, eOpts ) {
							
							if(newValue) {
								//согласие на новое исследование
								record.set('DopDispInfoConsent_IsEarlier', false);
								if(record.get('DopDispInfoConsent_IsImpossible') != 'hidden') {
									record.set('DopDispInfoConsent_IsImpossible', "0");
									record.set('DopDispInfoConsent_IsImpossibleCheck', false);
								}
							} else {
								//отказ от исследования
							}
							me.getController().onChangeConsentRecord(record);
						}
					}
				},
				{ dataIndex: 'DopDispInfoConsent_IsEarlier', xtype: 'checkcolumn', text: 'Выполнено ранее', width: 300, sortable: false,
					bind: {
						disabled: '{action == "view"}'
					},
					listeners: {
						beforecheckchange: function(column, rowIndex, newValue, record, eOpts ) {
							//~ if(record.isDisable) {
							//~ if(record.get('DopDispInfoConsent_IsImpossible')==2) {
								//~ return false;
							//~ }
						},
						checkchange: function(column, rowIndex, newValue, record) {
							if(newValue) {
								//выполнено ранее - убираем другие отметки в строке
								record.set('DopDispInfoConsent_IsAgree', false);
								if(record.get('DopDispInfoConsent_IsImpossible') != 'hidden') {
									record.set('DopDispInfoConsent_IsImpossible', "0");
									record.set('DopDispInfoConsent_IsImpossibleCheck', false);
								}
							} else {
								//не выполнено ранее - ставим отметку на согласии / -пока убрал, непонятно, нужна ли такая логика.
								//~ record.set('DopDispInfoConsent_IsAgree', true);
							}
							me.getController().onChangeConsentRecord(record);
						}
					},
					renderer : function(value, meta) {
						var str = '',
							cssPrefix = Ext6.baseCSSPrefix,
							cls = [cssPrefix + 'grid-checkcolumn'],
							label = '';

						if (this.disabled) {
							meta.tdCls += ' ' + this.disabledCls;
						}
						if (value) {
							cls.push(cssPrefix + 'grid-checkcolumn-checked');
						}

						if(!Ext6.isEmpty(meta.record.get('OutUsluga_id'))) {
							label = '<span class="checkcolumnlabel">'+meta.record.get('OutUsluga_Date')+' • '+meta.record.get('OutUsluga_Lpu_Nick')+'</span>';
						}
						str = '<span class="checkcolumnlabelbox ' + cls.join(' ') + '"></span>'+ label;

						/*
						var link = '';
						if (meta.record.get('SurveyType_IsVizit') == 1 && meta.record.get('SurveyType_id') != 2) { // исследования
							link = "getWnd('uslugaResultWindow').show({Evn_id: "+meta.record.get('EvnUslugaPar_id')+", object: 'EvnUslugaPar', object_id: 'EvnUslugaPar_id'});";
						}
						link += ' return false;';
						if(meta.record.get('EvnUsluga_Date')){ str+= '<a href="#" onclick="'+link+'">' + meta.record.get('EvnUsluga_Date').dateFormat('d.m.Y') + '</a>';}
						if(meta.record.get('OutMedPersonalFIO')){ str+= ' • '+meta.record.get('OutMedPersonalFIO');}

						*/
						return str;
					}
				}
			]
		});
		
		me.ConsentForm = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			padding: "18 0 30 27",
			layout: 'anchor',
			//~ bodyPadding: 10,
			border: false,
			defaults: {
				anchor: '100%'
			},
			items: [{
				layout: 'column',
				border: false,
				items: [{
					xtype: 'swDateField',
					fieldLabel: 'Дата согласия/отказа',
					name: 'EvnPLDispDop13_consDate',
					valuePublishEvent: ['change'],//чтобы корректно работала модель на base_form.reset/setValues
					value: new Date(),
					width: 150+5+130,
					labelWidth: 150,
					bind: {
						value: '{EvnPLDispDop13_consDate}',
						disabled: '{action == "view"}'
					},
					listeners: {
						change: 'onEvnPLDispDop13_consDate'
					}
				}, {
					xtype: 'PayTypeCombo',
					useCommonFilter: true,
					name: 'PayType_id',
					itemId: 'PayType_id',
					fieldLabel: 'Вид оплаты',
					width: 100+5+140,
					labelWidth: 100,
					hidden: true,
					style: 'padding-left: 28px;',
				}, {
					xtype: 'checkboxfield',
					boxLabel: 'Обслужен мобильной бригадой',
					name: 'EvnPLDispDop13_IsMobile',
					itemId: 'EvnPLDispDop13_IsMobile',
					bind: '{EvnPLDispDop13_IsMobile}',
					width: 214+50,
					style: 'padding-left: 38px;',
					bind: {
						disabled: '{action =="view"}'
					}
				}, {
					fieldLabel: 'МО',
					xtype: 'swLpuCombo',
					name: 'Lpu_mid',
					style: 'padding-left: 28px;',
					width: 33+5+230,
					labelWidth: 33,
					bind: {
						value: '{Lpu_mid}',
						disabled: '{action == "view" || !EvnPLDispDop13_IsMobile}'
					},
					listeners: {
						'disable': function(field) {
							field.clearValue();
						},
					}
				}]
			}, {
				layout: 'column',
				border: false,
				items: [{
					hidden: getRegionNick() != 'ekb',
					xtype: 'checkboxfield',
					boxLabel: 'Проведен вне МО',
					name: 'EvnPLDispDop13_IsOutLpu',
					itemId: 'EvnPLDispDop13_IsOutLpu',
					bind: '{EvnPLDispDop13_IsOutLpu}',
					//~ width: 214+50,
					//~ style: 'padding-left: 38px;'
				}]
			},
			me.ConsentGrid,
			{
				layout: {
					type: 'hbox',
					align: 'stretch'
				},
				style: 'padding-top: 33px;',
				border: false,
				items: [{
					border: false,
					flex: 1,
					items: [{
						xtype: 'button',
						text: 'Печать согласия',
						cls: 'button-without-frame grey-btn',
						iconCls: 'panicon-print',
						bind: {
							disabled: '{!EvnPLDispDop13_id || !PersonAgree}'
						},
						handler: 'printConsent'
					}]
				}, {
					xtype: 'checkbox',
					width: 140,
					name: 'PersonAgree',
					itemId: 'PersonAgree',
					bind: {
						value: '{PersonAgree}',
						disabled: '{action == "view"}'
					},
					boxLabel: langs('Согласие получено'),
					checked: true
				}, {
					xtype: 'button',
					text: 'Пройти диспансеризацию',
					itemId: 'saveConsentButton',
					cls: 'button-primary',
					style: 'margin-left: 23px;',
					width: 212+50,
					bind: {
						//~ disabled: '{!PersonAgree}'
						disabled: '{action == "view"}'
					},
					handler: 'saveConsent'
				}, {
					xtype: 'button',
					text: 'Оформить отказ',
					itemId: 'buttonRefuse',
					cls: 'button-secondary',
					style: 'margin-left: 10px;',
					width: 170,
					bind: {
						disabled: '{action == "view"}'
					},
					handler: 'refuse'
				}]
			}]
		});
		
		var tools = [];
		var defaultTools = [
			{
				xtype: 'tbspacer',
				flex: 1
			}, {
				xtype: 'button',
				itemId: 'panelstatus',
				userCls: 'iconlabel button-without-frame',
				iconCls: 'icon-ok',
				width: 200,
				text: 'Согласие получено',
				bind: {
					hidden: '{!PersonAgree || action=="add"}'
				}
			}
			];
		
		defaultTools.forEach(function(tool){
			if(tool.xtype && tool.xtype == 'tbspacer')
				tools.push(tool);
			else
				tools.push(Ext6.Object.merge({
					//~ userCls: 'sw-tool-label',
				},tool));
		});
			
		Ext6.apply(me, {
			tools: tools,
			items: [
				me.ConsentForm
			]
		});
			
		this.callParent(arguments);
	}
});
	