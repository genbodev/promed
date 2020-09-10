/**
 * Форма ДВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * EvnPLDispDop13Form
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */
Ext6.define('common.EMK.EvnPLDispDop13Form', {
	requires: [
		'common.EMK.EvnPLDispDop.controller.MainController',
		'common.EMK.EvnPLDispDop.model.MainModel',
		'common.EMK.EvnPLDispDop.store.ConsentStore',
		'common.EMK.EvnPLDispDop.view.ConsentPanel',
		'common.EMK.EvnPLDispDop.view.AnketaPanel',
		'common.EMK.EvnPLDispDop.view.AntropoPanel',
		'common.EMK.EvnPLDispDop.view.ArteriaPressPanel',
		'common.EMK.EvnPLDispDop.view.EyePressPanel',
		'common.EMK.EvnPLDispDop.view.DeseasePanel',
		'common.EMK.EvnPLDispDop.view.FactorRiskPanel',
		'common.EMK.EvnPLDispDop.view.IndividualProphConsultPanel',
		'common.EMK.EvnPLDispDop.view.TherapistViewPanel',
		'common.EMK.EvnPLDispDop.view.GynecologistPanel',
	],
	alias: 'widget.EvnPLDispDop13Form',
	viewModel: 'EvnPLDispDop13MainModel',
	controller: 'EvnPLDispDop13MainController',
	itemId: 'EvnPLDispDop13Form',
	cls: 'dispdop13form emk-forms-window arm-window-new',
	extend: 'Ext6.Panel',
	layout: 'border',
	region: 'center',
	border: false,
	formStatus: 'edit',
	evnParams: {},
	params: {},
	getParams: 'getParams', //function() { return this.getController().getParams(); },
	lastUpdateData: [],
	setParams: function(params) {//используется в ЭМК
		this.getController().setParams(params);
	},
	getConsentStore: function() {
		if(this.ConsentPanel && this.ConsentPanel.ConsentGrid && this.ConsentPanel.ConsentGrid.getStore()) 
			return this.ConsentPanel.ConsentGrid.getStore();
		else return false;
	},
	getSSRblock: function() {
		return !this.SumSSRiskPanel.hidden ? this.SumSSRiskPanel : 
			(!this.AbsSSRiskPanel.hidden ? this.AbsSSRiskPanel : this.RelSSRiskPanel);
	},
	loadData: function(options) {//используется в ЭМК
		this.getController().loadData(options);

		let me = this;
		let vm = me.getViewModel();
		let EvnPLDispDop13_id = vm.get('EvnPLDispDop13_id');
		/*Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=getDVNPanelsLastUpdater',
			params: {EvnPLDispDop13_id: EvnPLDispDop13_id},
			failure: function (response, options) {

			},
			success: function (response, action) {
				if (response.responseText) {
					me.lastUpdateData = Ext6.util.JSON.decode(response.responseText);
				}
			}
		});*/
	},
	initComponent: function() {
		let me = this,
			cntr = me.getController();
		me.swEMDPanel = Ext6.create('sw.frames.EMD.swEMDPanel', {
			bind: {
				disabled: '{accessType != "edit"}'
			}
		});
		me.toolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			height: 40,
			border: false,
			noWrap: true,
			right: 0,
			style: 'background: transparent;',
			items: [{
				xtype: 'tbspacer',
				width: 10
			}, me.swEMDPanel, {
				userCls: 'button-without-frame',
				style: {
					'color': 'transparent'
				},
				iconCls: 'panicon-print',
				tooltip: langs('Печать'),
				menu: new Ext6.menu.Menu({
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Печать информированного добровольного согласия',
						handler: function() {
							me.ConsentPanel.getController().printConsent();
						}
					}, {
						text: 'Печать паспорта здоровья',
						//disabled: true,
						bind: {
							disabled: '{!EvnPLDispDop13_id}'
						},
						handler: 'printEvnPLDispDop13Passport'
					}, {
						text: 'Печать карты диспансеризации',
						hidden: !getRegionNick().inlist(['kz', 'perm', 'ufa']),
						//disabled: true,
						bind: {
							disabled: '{!EvnPLDispDop13_id}'
						},
						handler: 'printEvnPLDispDop13'
					}, {
						text: 'Печать справки о стоимости лечения',
						disabled: true,
					}]
				})
			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-theedots',
				tooltip: langs('Меню'),
				handler: function() {
					//me.EvnPLMenu.showBy(this);
				}
			}]
		});
		
		me.titleLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			cls: 'no-wrap-ellipsis',
			style: 'font-size: 16px; padding: 3px 10px;',
			html: 'Диспансеризация взрослого населения'
		});
		
		me.titlePanel = Ext6.create('Ext6.Panel', {
			region: 'north',
			style: {
				'box-shadow': '0px 1px 6px 2px #ccc',
				zIndex: 2
			},
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #EEEEEE;',
			items: [{
				region: 'center',
				border: false,
				bodyStyle: 'background-color: #EEEEEE;',
				height: 40,
				bodyPadding: 10,
				items: [
					me.titleLabel
				]
			}, me.toolPanel
			],
			xtype: 'panel'
		});
		
		

		me.tabToolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			width: '100%',
			height: 40,
			border: false,
			margin: "0 10px 0 0",
			bind: {
				hidden: '{action != "add"}'
			},
			items: [
			{
				text: 'Карта диспансеризации',
				cls: 'dispdop-title1'
			},
			'->', 
			{
				text: 'Не сохранено',
				cls: 'dispdop-title2',
				tooltip: 'Для сохранения нажмите "Пройти диспансеризацию"'
			}]
		});
		
		me.tabPanel = Ext6.create('Ext6.TabPanel', {
			border: false,
			defaults: {
				tabConfig: {
					margin: 0,
					cls: 'evn-pl-tab-panel-items'
				}
			},
			items: [{
				title: LOADING_MSG,
				border: false,
				html: '',
				EvnPLDisp_id: null
			}],
			listeners: {
				tabchange: 'tabchange'
			}
		});

		me.TabContainer = Ext6.create('Ext6.Panel', {
			region: 'north',
			layout: 'border',
			border: false,
			height: 50,
			cls: 'topRadius leftPadding emk-top-panel',
			items: [{
				region: 'center',
				border: false,
				height: 40,
				items: [
					me.tabPanel
				]
			}, me.tabToolPanel, {
				region: 'south',
				bodyStyle: 'background-color: #EEEEEE; border-width: 0px 1px 0px 1px; -webkit-box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2); -moz-box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2); box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2);',
				height: 10,
				html: ''
			}],
			xtype: 'panel'
		});
		
		// === form-blocks:
		var PanelBindConfig = function(SurveyTypeCode) {
			return {
				hidden: '{action=="add" ' + (SurveyTypeCode ? (SurveyTypeCode > 0 ? ' || isHiddenSurveyType_Code' + SurveyTypeCode : ' || true') : '') + '}',
				disabled: '{!isEvnPLDispDop13_id || !PersonAgree}'
			};
		};
		me.defConf = {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			collapsed: true,
			collapseOnOnlyTitle: true
		};
		me.ConsentPanel = Ext6.create('common.EMK.EvnPLDispDop.view.ConsentPanel', Ext6.apply({
			itemId: 'ConsentPanel',
		}, me.defConf));
		
		me.AnketaPanel = Ext6.create('common.EMK.EvnPLDispDop.view.AnketaPanel', Ext6.apply({
			title: 'Опрос (анкетирование)',
			itemId: 'AnketaPanel',
			SurveyType_Code: 2,
			bind: PanelBindConfig(2)
		}, me.defConf));
		me.AntropoPanel = Ext6.create('common.EMK.EvnPLDispDop.view.AntropoPanel', Ext6.apply({
			title: 'Антропометрия',
			itemId: 'AntropoPanel',
			SurveyType_Code: 4,
			bind: PanelBindConfig(4)
		}, me.defConf));
		me.ADPanel = Ext6.create('common.EMK.EvnPLDispDop.view.ArteriaPressPanel', Ext6.apply({
			itemId: 'ArteriaPressPanel',
			SurveyType_Code: 3,
			lastUpdater: 'Врач',
			lastUpdateDateTime: 'Дата',
			bind: PanelBindConfig(3)
		}, me.defConf));
		/*me.SpecificsOnkoPanel = Ext6.create('swSpecificPanel', Ext6.apply({
			cls: 'emk-morbus-onko-panel',
			userCls: 'emk-morbus-onko-panel',
			bind: PanelBindConfig(19),
			//EvnDiag_id: item['EvnDiagPLSop_id'],
			specificTitle: 'ОНКОЛОГИЯ', //<span style="'+(!item['diagIsMain'] ? 'font-weight: normal;' : '')+'">' + item['Diag_Code'] + '</span>',
			handler: function() {
				cntr.openSpecificsWindow();
			}
		}, defConf));*/
		me.EDPanel = Ext6.create('common.EMK.EvnPLDispDop.view.EyePressPanel', Ext6.apply({
			itemId: 'EyePressPanel',
			SurveyType_Code: 8,
			lastUpdater: 'Врач',
			lastUpdateDateTime: 'Дата',
			bind: PanelBindConfig(8)
		}, me.defConf));

		me.IndividualProphConsultPanel = Ext6.create('common.EMK.EvnPLDispDop.view.IndividualProphConsultPanel', Ext6.apply({
			title: 'Индивидуальное профилактическое консультирование',
			itemId: 'IndividualProphConsultPanel',
			SurveyType_Code: getRegionNick() == 'ufa' ? 139 : 163,
			reference: getRegionNick() == 'ufa' ? 'st139' : 'st163',
			bind: PanelBindConfig(getRegionNick() == 'ufa' ? 139 : 163)
		}, me.defConf));
		me.SumSSRiskPanel = Ext6.create('common.EMK.EvnPLDispDop.view.SSRiskPanel', Ext6.apply({
			itemId: 'SumSSRPanel',
			SurveyType_Code: 7,
			bind: PanelBindConfig(7),
			lastUpdater: 'Врач',
			lastUpdateDateTime: 'Дата'
		}, me.defConf));
		me.AbsSSRiskPanel = Ext6.create('common.EMK.EvnPLDispDop.view.SSRiskPanel', Ext6.apply({
			itemId: 'AbsSSRPanel',
			SurveyType_Code: 97,
			bind: PanelBindConfig(97),
			lastUpdater: 'Врач',
			lastUpdateDateTime: 'Дата'
		}, me.defConf));
		me.RelSSRiskPanel = Ext6.create('common.EMK.EvnPLDispDop.view.SSRiskPanel', Ext6.apply({
			itemId: 'RelSSRPanel',
			SurveyType_Code: 96,
			bind: PanelBindConfig(96),
			lastUpdater: 'Врач',
			lastUpdateDateTime: 'Дата'
		}, me.defConf));
		me.PrescrPanel = Ext6.create('common.EMK.EvnPLDispDop.view.PrescrPanel', Ext6.apply({
			title: 'Направления на исследования',
			bind: PanelBindConfig(),
			autoHeight: true,
			confirmGrid: me.ConsentPanel.ConsentGrid,
			userMedStaffFact: me.ownerWin.userMedStaffFact,
			PersonInfoPanel: me.ownerWin.PersonInfoPanel,
			lastUpdater: '111',
			lastUpdateDateTime: '222',
			userCls: 'panel-with-tree-dots accordion-panel-window accordion-panel-prescr'
		}, me.defConf));
		me.GynecologistPanel = Ext6.create("common.EMK.EvnPLDispDop.view.GynecologistPanel", Ext6.apply({//yl:
			title: "Осмотр фельдшером (акушеркой) или врачом акушером-гинекологом",
			itemId: "GynecologistPanel",
			autoHeight: true,
			lastUpdater: "1111",
			lastUpdateDateTime: "2222",
			SurveyType_Code: 31,
			bind: PanelBindConfig(31)
		}, me.defConf));
		me.TerapevtPanel = Ext6.create('common.EMK.EvnPLDispDop.view.TherapistViewPanel', Ext6.apply({
			title: 'Прием(осмотр) врача-терапевта',
			itemId: 'ProtocolTerapevtPanel',
			autoHeight: true,
			SurveyType_Code: 19,
			bind: PanelBindConfig(19),
			lastUpdater: '1111',
			lastUpdateDateTime: '2222',
		}, me.defConf));
		me.DeseasePanel = Ext6.create('common.EMK.EvnPLDispDop.view.DeseasePanel', Ext6.apply({//yl:
			title: 'Заболевания',
			itemId: 'DeseasePanel',
			bind: PanelBindConfig()//-1
		}, me.defConf));
		me.FactorRiskPanel = Ext6.create("common.EMK.EvnPLDispDop.view.FactorRiskPanel", Ext6.apply({//yl:
			title: 'Факторы риска',
			itemId: 'FactorRiskPanel',
			bind: PanelBindConfig()
		}, me.defConf));
		me.ResultPanel = Ext6.create('swPanel', Ext6.apply({
			title: 'Результаты диспансеризации',
			itemId: 'ResultPanel',
			bind: PanelBindConfig(),
			items: [
				me.ResultForm = Ext6.create('Ext6.form.Panel', {
				border: false,
				padding: 30,
				items: [
					{
						xtype: 'checkbox',
						boxLabel: 'Взят под диспансерное наблюдение',
						itemId: 'EvnPLDispDop13_IsDisp',
						name: 'EvnPLDispDop13_IsDisp',
						inputValue: '2',
						uncheckedValue: '1',
						bind: {
							value: '{EvnPLDispDop13_IsDisp}',
							disabled: '{action == "view"}'
						},
					},
					{
						xtype: 'label',
						html: 'Группа здоровья',
						padding: '10px 0 10px 0',
						style: 'font-style: bold;'
					}, {
						xtype: 'segmentedbutton',
						itemId: 'HealthKind_id',
						//~ width: 300,
						maxWidth: 500,
						userCls: 'segmentedButtonGroup',
						bind: {
							value: '{HealthKind_id}',
							disabled: '{action == "view"}'
						},
						items: [{
							text: 'I',
							width: 50,
							value: '1',
							bind: {
								hidden: '{PersonDisp_id || EvnPLDispDop13_IsDisp}'
							}
						}, {
							text: 'II',
							width: 50,
							value: '2',
						}, {
							text: 'III',
							width: 50,
							value: '3',
							hidden: getRegionNick() != 'buryatiya'
						}, {
							text: 'IIIа',
							width: 50,
							value: '6'
						},
						{
							text: 'IIIб',
							width: 50,
							value: '7'
						}]
					}, {
						layout: 'column',
						userCls: 'buttonFooterGroup',
						border: false,
						margin: '20 10 30 0',
						items: [
							{
								//~ text: 'Завершить диспансеризацию',
								bind: {
									text: '{EvnPLDispDop13_IsEndStage==2 ? "Отменить завершение" : "Завершить диспансеризацию"}'
								},
								itemId: 'saveDVN',
								xtype: 'button',
								cls: 'button-primary',
								handler: 'doSave'
							}, {
								text: 'Перевести на второй этап',
								disabled: true, //пока не готов второй этап
								xtype: 'button',
								cls: 'button-secondary',
								style: 'margin-left: 10px;',
								handler: function() {
									inDevelopmentAlert();
								}
							}
						]
					}
				]})
			]
		}, me.defConf));
		// === end of blocks
		me.AccordionPanel = Ext6.create('Ext6.Panel', {
			cls: 'accordion-panel-emk',
			//~ border: false,
			bodyStyle: 'border-width: 0px 1px 1px 1px;',
			defaults: {
				margin: "0px 0px 2px 0px"
			},
			layout: {
				type: 'accordion',
				titleCollapse: false,
				animate: true,
				multi: true,//TODO: вообще надо бы false, но некорректно работает
				activeOnTop: false
			},
			listeners: {
				'resize': function() {
					this.updateLayout();
				}
			},
			expandPanel: function(panel) {
				var expanded = false;
				this.items.getRange().forEach(function(pan){
					if(!panel) {//режим открывания первой видимой панельки (исключая панель согласия)
						if(!expanded && pan.itemId != 'ConsentPanel') {
							if(!pan.collapsed) pan.fireEvent('expand');
							pan.expand();
							expanded = true;
						} else pan.collapse();
					} else {//режим открывания конкретной панельки
						if(pan.id!=panel.id) {
							//~ pan.collapse();
						} else {
							if(!pan.collapsed) pan.fireEvent('expand');
							pan.expand();
						}
					}
				});
			},
			getPositionNumber(panelItemId) {
				var index = -1,
					i = 0;
				this.items.getRange().forEach(function(pan){
					if(pan.itemId == panelItemId) index = i;
					i=i+1;
				});
				return index;
			},
			collapseOtherPanels: function(panel) {
				this.items.getRange().forEach(function(pan){
					if(pan.itemId!='ResultPanel' && pan.itemId != panel.itemId) {
						pan.collapse();
					}
				});
			},
			reset: function() {
				this.items.getRange().forEach(function(pan){
					if(!Ext6.isEmpty(pan.reset)) pan.reset();
				});
			},
			dockedItems: [
				me.TabContainer
			],
			items: [
				me.ConsentPanel,
				me.AnketaPanel,
				me.AntropoPanel,
				me.ADPanel,
				me.EDPanel,
				me.IndividualProphConsultPanel,
				me.SumSSRiskPanel,
				me.AbsSSRiskPanel,
				me.RelSSRiskPanel,
				me.PrescrPanel,
				me.GynecologistPanel,
				me.TerapevtPanel,
				//me.SpecificsOnkoPanel,
				me.DeseasePanel,
				me.FactorRiskPanel,
				me.ResultPanel
			]
		});

		me.bottomPanel = Ext6.create('common.EMK.PersonBottomPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin
		});

		me.MainForm = Ext6.create('Ext6.form.FormPanel', {
			border: false,
			url: '/?c=EvnPLDispDop13&m=loadEvnPLDispDop13EditForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'EvnPLDispDop13_id' },
						{ name: 'EvnPLDispDop13_IsPaid' },
						{ name: 'EvnPLDispDop13_IsNewOrder' },
						{ name: 'EvnPLDispDop13_IndexRep' },
						{ name: 'EvnPLDispDop13_IndexRepInReg' },
						{ name: 'accessType' },
						{ name: 'EvnPLDispDop13Sec_id' },
						{ name: 'DispClass_id' },
						{ name: 'EvnPLDispDop13_fid' },
						{ name: 'PayType_id' },
						{ name: 'Lpu_mid' },
						{ name: 'EvnPLDispDop13_IsMobile' },
						{ name: 'EvnPLDispDop13_IsOutLpu' },
						{ name: 'Person_id' },
						{ name: 'PersonEvn_id' },
						{ name: 'EvnPLDispDop13_setDate' },
						{ name: 'EvnPLDispDop13_consDate' },
						{ name: 'EvnPLDispDop13_firstConsDate' },
						{ name: 'Server_id' },
						{ name: 'EvnPLDispDop13_IsStenocard' },
						{ name: 'EvnPLDispDop13_IsShortCons' },
						{ name: 'EvnPLDispDop13_IsBrain' },
						{ name: 'EvnPLDispDop13_IsDoubleScan' },
						{ name: 'EvnPLDispDop13_IsTub' },
						{ name: 'EvnPLDispDop13_IsTIA'},
						{ name: 'EvnPLDispDop13_IsRespiratory'},
						{ name: 'EvnPLDispDop13_IsLungs'},
						{ name: 'EvnPLDispDop13_IsTopGastro'},
						{ name: 'EvnPLDispDop13_IsBotGastro'},
						{ name: 'EvnPLDispDop13_IsSpirometry'},
						{ name: 'EvnPLDispDop13_IsHeartFailure'},
						{ name: 'EvnPLDispDop13_IsOncology'},
						{ name: 'EvnPLDispDop13_IsEsophag' },
						{ name: 'EvnPLDispDop13_IsSmoking' },
						{ name: 'EvnPLDispDop13_IsRiskAlco' },
						{ name: 'EvnPLDispDop13_IsAlcoDepend' },
						{ name: 'EvnPLDispDop13_IsLowActiv' },
						{ name: 'EvnPLDispDop13_IsIrrational' },
						{ name: 'EvnPLDispDop13_IsUseNarko' },
						{ name: 'person_weight' },
						{ name: 'person_height' },
						{ name: 'body_mass_index' },
						{ name: 'waist_circumference' },
						{ name: 'systolic_blood_pressure'},
						{ name: 'diastolic_blood_pressure'},
						{ name: 'eye_pressure_right'},
						{ name: 'eye_pressure_left'},
						{ name: 'total_cholesterol' },
						{ name: 'glucose' },
						{ name: 'EvnPLDispDop13_IsHypoten' },
						{ name: 'EvnPLDispDop13_IsLipid' },
						{ name: 'EvnPLDispDop13_IsHypoglyc' },
						{ name: 'Diag_id' },
						{ name: 'Diag_sid' },
						{ name: 'EvnPLDispDop13_IsDisp' },
						{ name: 'NeedDopCure_id' },
						// { name: 'EvnPLDispDop13_IsStac' },
						{ name: 'EvnPLDispDop13_IsSanator' },
						{ name: 'EvnPLDispDop13_SumRick' },
						{ name: 'RiskType_id' },
						{ name: 'EvnPLDispDop13_IsSchool' },
						{ name: 'EvnPLDispDop13_IsProphCons' },
						{ name: 'HealthKind_id' },
						{ name: 'EvnPLDispDop13_IsEndStage' },
						{ name: 'EvnPLDispDop13_IsTwoStage' },
						{ name: 'CardioRiskType_id' },
						{ name: 'EvnCostPrint_setDT' },
						{ name: 'EvnCostPrint_Number' },
						{ name: 'EvnCostPrint_IsNoPrint' },
						{ name: 'EvnPLDispDop13_IsSuspectZNO' },
						{ name: 'Diag_spid' },
						{ name: 'PersonDisp_id' },
						{ name: 'TherapistViewData'},
						//{ name: 'therapistEditorText' } //???
					]
				})
			}),
			items: [
				me.AccordionPanel,
				//далее идут скрытые поля, но важные для функционала всего окна
				{
					name: 'EvnPLDispDop13_IsTwoStage', //направлен на второй этап
					value: 1, //пока так
					xtype: 'hidden'
				},
				{
					name: 'EvnPLDispDop13_IsEndStage',
					bind: '{EvnPLDispDop13_IsEndStage}',
					xtype: 'hidden'
				},
				{
					name: 'EvnPLDispDop13_id',
					//bind: '{EvnPLDispDop13_id}',
					value: 0,
					xtype: 'hidden'
				}, {
					name:'EvnPLDispDop13_IsPaid',
					bind: '{EvnPLDispDop13_IsPaid}',
					xtype:'hidden'
				}, {
					name:'EvnPLDispDop13_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPLDispDop13_IndexRepInReg',
					xtype:'hidden'
				}, {
					name: 'accessType',
					bind: '{accessType}',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13Sec_id',
					bind: '{EvnPLDispDop13Sec_id}',
					xtype: 'hidden'
				}, {
					name: 'DispClass_id',
					value: 1,
					bind: '{DispClass_id}',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_fid',
					bind: '{EvnPLDispDop13_fid}',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					//bind: '{Person_id}',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					bind: '{PersonEvn_id}',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_setDate',
					bind: '{EvnPLDispDop13_setDate}',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_disDate',
					bind: '{EvnPLDispDop13_disDate}',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_firstConsDate',
					xtype: 'hidden'
				}, {
					name: 'total_cholesterol',
					bind: '{total_cholesterol}',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_IsSmoking',
					bind: '{EvnPLDispDop13_IsSmoking}',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonDisp_id',
					xtype: 'hidden'
				}
			]
		});
		Ext6.apply(me, {
			border: false,
			items: [
				me.titlePanel, 
				{
					region: 'center',
					flex: 400,
					bodyPadding: 10,
					scrollable: true,
					bodyStyle: "border-width: 1px 0;",
					border: false,
					items: [
						me.MainForm
					]
				}, 
				me.bottomPanel
			]
		});

		me.callParent(arguments);
	}
});