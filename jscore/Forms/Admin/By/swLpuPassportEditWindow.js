/**
* swLpuPassportEditWindow - окно редактирования паспорта МО (Беларусь эдишн)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Bykov Stanislav (savage@swan.perm.ru)
* @version      08.10.2014
* @comment      Префикс для id компонентов LPEW (LpuPassportEditWindow)
*/

sw.Promed.swLpuPassportEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	height: 550,
	id: 'LpuPassportEditWindow',
	title: 'Паспорт МО',
	width: 800,

	deleteOrgHead: function(){
		var grid = this.findById('LPEW_OrgHeadGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('OrgHead_id') )
			return;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success) {
									grid.getStore().remove(record);

									if ( grid.getStore().getCount() == 0 ) {
										LoadEmptyRow(grid);
									}

									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert('Ошибка', 'При удалении руководителя возникли ошибки');
							}
						},
						params: {
							OrgHead_id: record.get('OrgHead_id')
						},
						url: '/?c=Org&m=deleteOrgHead'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить руководителя',
			title: 'Вопрос'
		});
	},
	openTransportConnectEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swTransportConnectEditWindow').isVisible() ) {
			sw.swMsg.alert('Ошибка', 'Окно редактирования связи с транспортным узлом уже открыто.');
			return false;
		}

		var formParams = {},
			grid = this.findById('LPEW_TransportConnectGrid').getGrid(),
			params = {},
			selectedRecord;

		params.Lpu_id = this.Lpu_id;

		if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('TransportConnect_id') ) {
			selectedRecord = grid.getSelectionModel().getSelected();
		}

		if ( action != 'add' ) {
			if ( !selectedRecord ) {
				return false;
			}

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.action = action;
		params.callback = function(data) {
			grid.getStore().loadData();
		};
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swTransportConnectEditWindow').show(params);
	},
	deleteOrgRSchet: function(){
		var grid = this.findById('LPEW_OrgRSchetGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('OrgRSchet_id') )
			return;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var obj = Ext.util.JSON.decode(response.responseText);
								if(!obj.success) {
									return false;
								}
								grid.getStore().remove(record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else {
								sw.swMsg.alert('Ошибка', 'При удалении счета возникли ошибки');
							}
						},
						params: {
							OrgRSchet_id: record.get('OrgRSchet_id')
						},
						url: '/?c=Org&m=deleteOrgRSchet'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить расчетный счет',
			title: 'Вопрос'
		});
	},

	numberRenderer: function(v){
		return (v) ? Number(v.slice(0,-2)) : null;
	},
	
	initComponent: function(){
		var _this = this;

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					
					var form = Ext.getCmp('LPEW_panelForm');
					var base_form = form.getForm();
		
					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								log(form.getFirstInvalidEl().id);
								form.getFirstInvalidEl().focus(false);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}
					
					form = Ext.getCmp('Lpu_IdentificationPanel');
					base_form = form.getForm();
		
					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								log(form.getFirstInvalidEl().id);
								Ext.getCmp('LpuPassportEditWindowTab').setActiveTab(0);
								form.getFirstInvalidEl().focus(false);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}
					
					form = Ext.getCmp('Lpu_SupInfoPanel');
					base_form = form.getForm();

					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								Ext.getCmp('LpuPassportEditWindowTab').setActiveTab(1);
								form.getFirstInvalidEl().focus(false);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}
					
					form = Ext.getCmp('Lpu_PopulationPanel');
					base_form = form.getForm();
		
					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								Ext.getCmp('LpuPassportEditWindowTab').setActiveTab(7);
								form.getFirstInvalidEl().focus(false);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}

					var loadMask = new Ext.LoadMask(Ext.get('LpuPassportEditWindow'), {msg: "Подождите, идет сохранение..."});
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=LpuPassport&m=saveLpuPassport',
						params: {

							Lpu_id: Ext.getCmp('LPEW_Lpu_id').getValue(),
							Server_id: Ext.getCmp('LPEW_Server_id').getValue(),
							Lpu_Name: Ext.getCmp('LPEW_Lpu_Name').getValue(),
							Lpu_Nick: Ext.getCmp('LPEW_Lpu_Nick').getValue(),
							Lpu_f003mcod: Ext.getCmp('LPEW_Lpu_f003mcod').getValue(),
							Lpu_RegNomN2: Ext.getCmp('LPEW_Lpu_RegNomN2').getValue(),
							
							LpuType_id: Ext.getCmp('LPEW_LpuType_id').getValue(),
							LpuAgeType_id: Ext.getCmp('LPEW_LpuAgeType_id').getValue(),
							Lpu_begDate: Ext.getCmp('LPEW_Lpu_begDate').getRawValue(),
							Lpu_endDate: Ext.getCmp('LPEW_Lpu_endDate').getRawValue(),
							Lpu_pid: Ext.getCmp('LPEW_Lpu_pid').getValue(),
							Lpu_nid: Ext.getCmp('LPEW_Lpu_nid').getValue(),
							Lpu_StickNick: Ext.getCmp('LPEW_Lpu_StickNick').getValue(),	
							Lpu_StickAddress: Ext.getCmp('LPEW_Lpu_StickAddress').getValue(),
							Lpu_DistrictRate: Ext.getCmp('LPEW_Lpu_DistrictRate').getValue(),

							Lpu_IsSecret: (Ext.getCmp('LPEW_Lpu_IsSecret').checked) ? '2' : '1',

							Lpu_Www: Ext.getCmp('LPEW_Lpu_Www').getValue(),
							Lpu_Email: Ext.getCmp('LPEW_Lpu_Email').getValue(),
							Lpu_Phone: Ext.getCmp('LPEW_Lpu_Phone').getValue(),
							Lpu_Worktime: Ext.getCmp('LPEW_Lpu_Worktime').getValue(),

							PasportMO_id: Ext.getCmp('LPEW_PasportMO_id').getValue(),
							UAddress_id: Ext.getCmp('LPEW_UAddress_id').getValue(),
							UAddress_Zip: Ext.getCmp('LPEW_UAddress_Zip').getValue(),
							UKLCountry_id: Ext.getCmp('LPEW_UKLCountry_id').getValue(),
							UKLRGN_id: Ext.getCmp('LPEW_UKLRGN_id').getValue(),
							UKLSubRGN_id: Ext.getCmp('LPEW_UKLSubRGN_id').getValue(),
							UKLCity_id: Ext.getCmp('LPEW_UKLCity_id').getValue(),
							UKLTown_id: Ext.getCmp('LPEW_UKLTown_id').getValue(),
							UKLStreet_id: Ext.getCmp('LPEW_UKLStreet_id').getValue(),
							UAddress_House: Ext.getCmp('LPEW_UAddress_House').getValue(),
							UAddress_Corpus: Ext.getCmp('LPEW_UAddress_Corpus').getValue(),
							UAddress_Flat: Ext.getCmp('LPEW_UAddress_Flat').getValue(),
							UAddress_Address: Ext.getCmp('LPEW_UAddress_Address').getValue(),
							
							PAddress_id: Ext.getCmp('LPEW_PAddress_id').getValue(),
							PAddress_Zip: Ext.getCmp('LPEW_PAddress_Zip').getValue(),
							PKLCountry_id: Ext.getCmp('LPEW_PKLCountry_id').getValue(),
							PKLRGN_id: Ext.getCmp('LPEW_PKLRGN_id').getValue(),
							PKLSubRGN_id: Ext.getCmp('LPEW_PKLSubRGN_id').getValue(),
							PKLCity_id: Ext.getCmp('LPEW_PKLCity_id').getValue(),
							PKLTown_id: Ext.getCmp('LPEW_PKLTown_id').getValue(),
							PKLStreet_id: Ext.getCmp('LPEW_PKLStreet_id').getValue(),
							PAddress_House: Ext.getCmp('LPEW_PAddress_House').getValue(),
							PAddress_Corpus: Ext.getCmp('LPEW_PAddress_Corpus').getValue(),
							PAddress_Flat: Ext.getCmp('LPEW_PAddress_Flat').getValue(),
							PAddress_Address: Ext.getCmp('LPEW_PAddress_Address').getValue(),
							
							Okopf_id: Ext.getCmp('LPEW_Okopf_id').getValue(),
							Okved_id: Ext.getCmp('LPEW_Okved_id').getValue(),
							LpuSpecType_id: Ext.getCmp('LPEW_LpuSpecType_id').getValue(),
							Okogu_id: Ext.getCmp('LPEW_Okogu_id').getValue(),
							Okfs_id: Ext.getCmp('LPEW_Okfs_id').getValue(),
							Org_INN: Ext.getCmp('LPEW_Org_INN').getValue(),
							Org_OGRN: Ext.getCmp('LPEW_Org_OGRN').getValue(),
							Lpu_Okato: Ext.getCmp('LPEW_Lpu_Okato').getValue(),
							
							Org_lid: Ext.getCmp('LPEW_Org_lid').getValue(),
							Lpu_RegDate: Ext.getCmp('LPEW_Lpu_RegDate').getRawValue(),
							Lpu_PensRegNum: Ext.getCmp('LPEW_Lpu_PensRegNum').getValue(),
							Lpu_RegNum: Ext.getCmp('LPEW_Lpu_RegNum').getValue(),
							Lpu_DocReg: Ext.getCmp('LPEW_Lpu_DocReg').getValue(),

							LpuSubjectionLevel_id: Ext.getCmp('LPEW_LpuSubjectionLevel_id').getValue(),
							LpuLevel_id: Ext.getCmp('LPEW_LpuLevel_id').getValue(),
							LpuLevelType_id: Ext.getCmp('LPEW_LpuLevelType_id').getValue(),
							LevelType_id: Ext.getCmp('LPEW_LevelType_id').getValue(),
							Lpu_VizitFact: Ext.getCmp('LPEW_Lpu_VizitFact').getValue(),
							Lpu_KoikiFact: Ext.getCmp('LPEW_Lpu_KoikiFact').getValue(),
							Lpu_AmbulanceCount: Ext.getCmp('LPEW_Lpu_AmbulanceCount').getValue(),
							InstitutionLevel_id: Ext.getCmp('LPEW_InstitutionLevel_id').getValue(),
							Lpu_FondOsn: Ext.getCmp('LPEW_Lpu_FondOsn').getValue(),
							Lpu_FondEquip: Ext.getCmp('LPEW_Lpu_FondEquip').getValue(),
							Lpu_gid: Ext.getCmp('LPEW_Lpu_gid').getValue(),

							Lpu_isCMP: Ext.getCmp('LPEW_Lpu_isCMP').getValue(),
							OftenCallers_CallTimes: Ext.getCmp('LPEW_Lpu_OftenCallers_CallTimes').getValue(),
							OftenCallers_SearchDays: Ext.getCmp('LPEW_Lpu_OftenCallers_SearchDays').getValue(),
							OftenCallers_FreeDays: Ext.getCmp('LPEW_Lpu_OftenCallers_FreeDays').getValue(),

							Lpu_ErInfo: Ext.getCmp('LPEW_Lpu_ErInfo').getValue(),
							Lpu_IsAllowInternetModeration: (Ext.getCmp('LPEW_IsAllowInternetModeration').checked) ? '2' : '1',
							Lpu_MedCare: Ext.getCmp('LPEW_Lpu_MedCare').getValue(),

							PasportMO_MaxDistansePoint: Ext.getCmp('LPEW_PasportMO_MaxDistansePoint').getValue(),
							PasportMO_IsFenceTer: Ext.getCmp('LPEW_PasportMO_IsFenceTer').getValue(),
							PasportMO_IsSecur: Ext.getCmp('LPEW_PasportMO_IsSecur').getValue(),
							DLocationLpu_id: Ext.getCmp('LPEW_DLocationLpu_id').getValue(),
							PasportMO_IsMetalDoors: Ext.getCmp('LPEW_PasportMO_IsMetalDoors').getValue(),
							PasportMO_IsAssignNasel: Ext.getCmp('LPEW_PasportMO_IsAssignNasel').getValue(),
							PasportMO_IsVideo: Ext.getCmp('LPEW_PasportMO_IsVideo').getValue(),
							PasportMO_IsAccompanying: Ext.getCmp('LPEW_PasportMO_IsAccompanying').getValue()

						},
						callback: function(options, success, response) {
							loadMask.hide();
							if (success)
							{
								if ( response.responseText.length > 0 )
								{
									var resp_obj = Ext.util.JSON.decode(response.responseText);
									if (resp_obj.success == true)
									{
										getGlobalOptions().lpu_email = Ext.getCmp('LPEW_Lpu_Email').getValue();
										Ext.getCmp('LpuPassportEditWindow').hide();
									}
								}
							}
						}
					});
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'LPEW_SaveButton',
				tabIndex: TABINDEX_LPEW + 12,
				text: '<u>С</u>охранить'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'LPEW_CancelButton',
				onTabAction: function () {
					//this.findById('ERPSIF_EvnRecept_Ser').focus(true, 100);
				}.createDelegate(this),
				tabIndex: TABINDEX_LPEW + 12,
				text: '<u>З</u>акрыть'
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding: 5px',
				border: false,
				frame: true,
				id: 'LPEW_panelForm',
				labelWidth: 240,
				region: 'north',
				items: [{
					id: 'LPEW_Lpu_id',
					name: 'Lpu_id',
					xtype: 'hidden',
					value: '0'
				}, {
					id: 'LPEW_Org_id',
					name: 'Org_id',
					xtype: 'hidden'
				}, {
					id: 'LPEW_Lpu_isCMP',
					name: 'Lpu_isCMP',
					xtype: 'hidden',
					value: '0'
				}, {
					id: 'LPEW_Server_id',
					name: 'Server_id',
					xtype: 'hidden',
					value: '0'
				}, {
					allowBlank: false,
					anchor: '60%',
					disabled: true,
					fieldLabel: 'Наименование МО',
					id: 'LPEW_Lpu_Name',
					name: 'Lpu_Name',
					tabIndex: TABINDEX_LPEW + 1,
					xtype: 'textfield'
				}, {
					allowBlank: false,
					anchor: '60%',
					disabled: true,
					fieldLabel: 'Краткое наименование МО',
					id: 'LPEW_Lpu_Nick',
					name: 'Lpu_Nick',
					tabIndex: TABINDEX_LPEW + 2,
					xtype: 'textfield'
				}, {
					allowBlank: true,
					anchor: '60%',
					autoCreate: { tag: "input",  maxLength: "6", autocomplete: "off" },
					disabled: true,
					fieldLabel: 'Федеральный код МО',
					id: 'LPEW_Lpu_f003mcod',
					name: 'Lpu_f003mcod',
					tabIndex: TABINDEX_LPEW + 3,
					xtype: 'textfield'
				}, {
					allowBlank: true,
					anchor: '60%',
					autoCreate: {tag: "input",  maxLength: "6", autocomplete: "off"},
					disabled: true,
					fieldLabel: 'Региональный код МО',
					id: 'LPEW_Lpu_RegNomN2',
					name: 'Lpu_RegNomN2',
					tabIndex: TABINDEX_LPEW + 4,
					xtype: 'textfield'
				}]
			}),
			new Ext.TabPanel({
				border: false,
				region: 'center',
				id: 'LpuPassportEditWindowTab',
				activeTab: 0,
				autoScroll: true,
				enableTabScroll:true,
				defaults: {bodyStyle:'width:100%;'},
				layoutOnTabChange: true,
				items: [{
					title: '1. Идентификация',
					layout: 'fit',
					id: 'tab_identification',
					iconCls: 'info16',
					border:false,
					items: 
					new Ext.form.FormPanel({
						autoScroll: true,
						bodyBorder: false,
						bodyStyle: 'padding: 5px 5px 0',
						border: false,
						frame: false,
						id: 'Lpu_IdentificationPanel',
						labelAlign: 'right',
						labelWidth: 180,
						items: [
						new sw.Promed.Panel({
							autoHeight: true,
							style: 'margin-bottom: 0.5em;',
							border: true,
							collapsible: true,
							region: 'north',
							id: 'Lpu_IdentificationEditForm',
							layout: 'form',
							title: '1. Идентификация',
							items: [{
								xtype: 'panel',
								layout: 'column',
								border: false,
								bodyStyle:'background:#DFE8F6;padding:5px;',
								items: [{// Левая часть
									layout: 'form',
									border: false,
									bodyStyle:'background:#DFE8F6;padding-right:5px;',
									columnWidth: .50,
									labelWidth: 180,
									items:
									[{
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										disabled: true,
										allowBlank: false,
										fieldLabel: 'Дата начала деятельности',
										format: 'd.m.Y',
										id: 'LPEW_Lpu_begDate',
										name: 'Lpu_begDate',
										tabIndex: 1100
									}, {
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										disabled: true,
										fieldLabel: 'Дата закрытия',
										format: 'd.m.Y',
										id: 'LPEW_Lpu_endDate',
										name: 'Lpu_endDate',
										tabIndex: 1100
									}, {
										fieldLabel: 'Правопреемник',
										allowBlank: true,
										id: 'LPEW_Lpu_pid',
										hiddenName: 'Lpu_pid',
										disabled: true,
										xtype: 'swlpulocalcombo',
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: 'Наследователь',
										allowBlank: true,
										id: 'LPEW_Lpu_nid',
										hiddenName: 'Lpu_nid',
										disabled: true,
										xtype: 'swlpulocalcombo',
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: 'Адрес электронной почты',
										id: 'LPEW_Lpu_Email',
										name: 'Lpu_Email',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: 'Адрес сайта',
										id: 'LPEW_Lpu_Www',
										name: 'Lpu_Www',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1100
									},
									{
										fieldLabel: 'Телефон',
										id: 'LPEW_Lpu_Phone',
										name: 'Lpu_Phone',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1100
									},
									{
										fieldLabel: 'Время работы',
										id: 'LPEW_Lpu_Worktime',
										name: 'Lpu_Worktime',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: 'Наименование МО для ЛВН',
										allowBlank: true,
										id: 'LPEW_Lpu_StickNick',
										name: 'Lpu_StickNick',
										xtype: 'textfield',
										anchor: '100%',
										autoCreate: {tag: "input", maxLength: "38", autocomplete: "off"},
										tabIndex: 1100
									}, {
										fieldLabel: 'Адрес МО для ЛВН',
										allowBlank: true,
										id: 'LPEW_Lpu_StickAddress',
										name: 'Lpu_StickAddress',
										xtype: 'textfield',
										disabled: true,
										autoCreate: {tag: "input", maxLength: "38", autocomplete: "off"},
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: 'Код по СОАТО',
										allowBlank: false,
										id: 'LPEW_Lpu_Okato',
										name: 'Lpu_Okato',
										xtype: 'textfield',
										disabled: true,
										autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
										maskRe: /[0-9]/,
										anchor: '100%',
										tabIndex: 1100
									}, {
										allowBlank: false,
										anchor: '100%',
										comboSubject: 'LpuType',
										fieldLabel: 'Тип МО',
										hiddenName: 'LpuType_id',
										id: 'LPEW_LpuType_id',
										xtype: 'swcommonsprcombo'
									}, {
										fieldLabel: 'Тип МО по возрасту',
										allowBlank: true,
										id: 'LPEW_LpuAgeType_id',
										hiddenName: 'LpuAgeType_id',
										disabled: true,
										xtype: 'swlpuagetypecombo',
										anchor: '100%',
										tabIndex: 1100
									}]
								},
								{
									// Правая часть
									layout: 'form',
									border: false,
									bodyStyle:'background:#DFE8F6;padding-right:5px;',
									columnWidth: .50,
									labelWidth: 180,
									items:
									[new sw.Promed.TripleTriggerField ({
										anchor: '100%',
										enableKeyEvents: true,
										disabled: true,
										fieldLabel: 'Юридический адрес',
										id: 'LPEW_UAddress_AddressText',
										listeners: {
											'keydown': function(inp, e) {
												if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
													if ( e.F4 == e.getKey() )
														inp.onTrigger1Click();
													if ( e.F2 == e.getKey() )
														inp.onTrigger2Click();
													if ( e.DELETE == e.getKey() && e.altKey)
														inp.onTrigger3Click();

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

													if ( Ext.isIE ) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											},
											'keyup': function( inp, e ) {
												if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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

												if ( Ext.isIE ) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											}
										},
										name: 'UAddress_AddressText',
										onTrigger2Click: function() {
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											ownerForm.findById('LPEW_UAddress_Zip').setValue(ownerForm.findById('LPEW_UAddress_Zip').getValue());
											ownerForm.findById('LPEW_UKLCountry_id').setValue(ownerForm.findById('LPEW_UKLCountry_id').getValue());
											ownerForm.findById('LPEW_UKLRGN_id').setValue(ownerForm.findById('LPEW_UKLRGN_id').getValue());
											ownerForm.findById('LPEW_UKLSubRGN_id').setValue(ownerForm.findById('LPEW_UKLSubRGN_id').getValue());
											ownerForm.findById('LPEW_UKLCity_id').setValue(ownerForm.findById('LPEW_UKLCity_id').getValue());
											ownerForm.findById('LPEW_UKLTown_id').setValue(ownerForm.findById('LPEW_UKLTown_id').getValue());
											ownerForm.findById('LPEW_UKLStreet_id').setValue(ownerForm.findById('LPEW_UKLStreet_id').getValue());
											ownerForm.findById('LPEW_UAddress_House').setValue(ownerForm.findById('LPEW_UAddress_House').getValue());
											ownerForm.findById('LPEW_UAddress_Corpus').setValue(ownerForm.findById('LPEW_UAddress_Corpus').getValue());
											ownerForm.findById('LPEW_UAddress_Flat').setValue(ownerForm.findById('LPEW_UAddress_Flat').getValue());
											ownerForm.findById('LPEW_UAddress_Address').setValue(ownerForm.findById('LPEW_UAddress_Address').getValue());
											ownerForm.findById('LPEW_UAddress_AddressText').setValue(ownerForm.findById('LPEW_UAddress_AddressText').getValue());
										},
										onTrigger3Click: function() {
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											ownerForm.findById('LPEW_UAddress_Zip').setValue('');
											ownerForm.findById('LPEW_UKLCountry_id').setValue('');
											ownerForm.findById('LPEW_UKLRGN_id').setValue('');
											ownerForm.findById('LPEW_UKLSubRGN_id').setValue('');
											ownerForm.findById('LPEW_UKLCity_id').setValue('');
											ownerForm.findById('LPEW_UKLTown_id').setValue('');
											ownerForm.findById('LPEW_UKLStreet_id').setValue('');
											ownerForm.findById('LPEW_UAddress_House').setValue('');
											ownerForm.findById('LPEW_UAddress_Corpus').setValue('');
											ownerForm.findById('LPEW_UAddress_Flat').setValue('');
											ownerForm.findById('LPEW_UAddress_Address').setValue('');
											ownerForm.findById('LPEW_UAddress_AddressText').setValue('');
										},
										onTrigger1Click: function() {
											var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											getWnd('swAddressEditWindow').show({
												fields: {
													Address_ZipEdit: ownerForm.findById('LPEW_UAddress_Zip').value,
													KLCountry_idEdit: ownerForm.findById('LPEW_UKLCountry_id').value,
													KLRgn_idEdit: ownerForm.findById('LPEW_UKLRGN_id').value,
													KLSubRGN_idEdit: ownerForm.findById('LPEW_UKLSubRGN_id').value,
													KLCity_idEdit: ownerForm.findById('LPEW_UKLCity_id').value,
													KLTown_idEdit: ownerForm.findById('LPEW_UKLTown_id').value,
													KLStreet_idEdit: ownerForm.findById('LPEW_UKLStreet_id').value,
													Address_HouseEdit: ownerForm.findById('LPEW_UAddress_House').value,
													Address_CorpusEdit: ownerForm.findById('LPEW_UAddress_Corpus').value,
													Address_FlatEdit: ownerForm.findById('LPEW_UAddress_Flat').value,
													Address_AddressEdit: ownerForm.findById('LPEW_UAddress_Address').value
												},
												callback: function(values) {
													ownerForm.findById('LPEW_UAddress_Zip').setValue(values.Address_ZipEdit);
													ownerForm.findById('LPEW_UKLCountry_id').setValue(values.KLCountry_idEdit);
													ownerForm.findById('LPEW_UKLRGN_id').setValue(values.KLRgn_idEdit);
													ownerForm.findById('LPEW_UKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
													ownerForm.findById('LPEW_UKLCity_id').setValue(values.KLCity_idEdit);
													ownerForm.findById('LPEW_UKLTown_id').setValue(values.KLTown_idEdit);
													ownerForm.findById('LPEW_UKLStreet_id').setValue(values.KLStreet_idEdit);
													ownerForm.findById('LPEW_UAddress_House').setValue(values.Address_HouseEdit);
													ownerForm.findById('LPEW_UAddress_Corpus').setValue(values.Address_CorpusEdit);
													ownerForm.findById('LPEW_UAddress_Flat').setValue(values.Address_FlatEdit);
													ownerForm.findById('LPEW_UAddress_Address').setValue(values.Address_AddressEdit);
													ownerForm.findById('LPEW_UAddress_AddressText').setValue(values.Address_AddressEdit);
													ownerForm.findById('LPEW_UAddress_AddressText').focus(true, 500);
												},
												onClose: function() {
													ownerForm.findById('LPEW_UAddress_AddressText').focus(true, 500);
												}
											})
										},
										readOnly: true,
										tabIndex: 1105,
										trigger1Class: 'x-form-search-trigger',
										trigger2Class: 'x-form-equil-trigger',
										trigger3Class: 'x-form-clear-trigger',
										width: 395
									}),
									new sw.Promed.TripleTriggerField ({
										//xtype: 'trigger',
										anchor: '100%',
										enableKeyEvents: true,
										disabled: true,
										fieldLabel: 'Фактический адрес',
										id: 'LPEW_PAddress_AddressText',
										listeners: {
											'keydown': function(inp, e) {
												if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
													if ( e.F4 == e.getKey() )
														inp.onTrigger1Click();
													if ( e.F2 == e.getKey() )
														inp.onTrigger2Click();
													if ( e.DELETE == e.getKey() && e.altKey)
														inp.onTrigger3Click();

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

													if ( Ext.isIE ) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											},
											'keyup': function( inp, e ) {
												if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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

													if ( Ext.isIE ) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											}
										},
										name: 'PAddress_AddressText',
										onTrigger1Click: function() {
											var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											getWnd('swAddressEditWindow').show({
												fields: {
													Address_ZipEdit: ownerForm.findById('LPEW_PAddress_Zip').value,
													KLCountry_idEdit: ownerForm.findById('LPEW_PKLCountry_id').value,
													KLRgn_idEdit: ownerForm.findById('LPEW_PKLRGN_id').value,
													KLSubRGN_idEdit: ownerForm.findById('LPEW_PKLSubRGN_id').value,
													KLCity_idEdit: ownerForm.findById('LPEW_PKLCity_id').value,
													KLTown_idEdit: ownerForm.findById('LPEW_PKLTown_id').value,
													KLStreet_idEdit: ownerForm.findById('LPEW_PKLStreet_id').value,
													Address_HouseEdit: ownerForm.findById('LPEW_PAddress_House').value,
													Address_CorpusEdit: ownerForm.findById('LPEW_PAddress_Corpus').value,
													Address_FlatEdit: ownerForm.findById('LPEW_PAddress_Flat').value,
													Address_AddressEdit: ownerForm.findById('LPEW_PAddress_Address').value
												},
												callback: function(values) {
													ownerForm.findById('LPEW_PAddress_Zip').setValue(values.Address_ZipEdit);
													ownerForm.findById('LPEW_PKLCountry_id').setValue(values.KLCountry_idEdit);
													ownerForm.findById('LPEW_PKLRGN_id').setValue(values.KLRgn_idEdit);
													ownerForm.findById('LPEW_PKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
													ownerForm.findById('LPEW_PKLCity_id').setValue(values.KLCity_idEdit);
													ownerForm.findById('LPEW_PKLTown_id').setValue(values.KLTown_idEdit);
													ownerForm.findById('LPEW_PKLStreet_id').setValue(values.KLStreet_idEdit);
													ownerForm.findById('LPEW_PAddress_House').setValue(values.Address_HouseEdit);
													ownerForm.findById('LPEW_PAddress_Corpus').setValue(values.Address_CorpusEdit);
													ownerForm.findById('LPEW_PAddress_Flat').setValue(values.Address_FlatEdit);
													ownerForm.findById('LPEW_PAddress_Address').setValue(values.Address_AddressEdit);
													ownerForm.findById('LPEW_PAddress_AddressText').setValue(values.Address_AddressEdit);
													ownerForm.findById('LPEW_PAddress_AddressText').focus(true, 500);
												},
												onClose: function() {
													ownerForm.findById('LPEW_PAddress_AddressText').focus(true, 500);
												}
											})
										},
										onTrigger2Click: function() {
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											ownerForm.findById('LPEW_PAddress_Zip').setValue(ownerForm.findById('LPEW_PAddress_Zip').getValue());
											ownerForm.findById('LPEW_PKLCountry_id').setValue(ownerForm.findById('LPEW_PKLCountry_id').getValue());
											ownerForm.findById('LPEW_PKLRGN_id').setValue(ownerForm.findById('LPEW_PKLRGN_id').getValue());
											ownerForm.findById('LPEW_PKLSubRGN_id').setValue(ownerForm.findById('LPEW_PKLSubRGN_id').getValue());
											ownerForm.findById('LPEW_PKLCity_id').setValue(ownerForm.findById('LPEW_PKLCity_id').getValue());
											ownerForm.findById('LPEW_PKLTown_id').setValue(ownerForm.findById('LPEW_PKLTown_id').getValue());
											ownerForm.findById('LPEW_PKLStreet_id').setValue(ownerForm.findById('LPEW_PKLStreet_id').getValue());
											ownerForm.findById('LPEW_PAddress_House').setValue(ownerForm.findById('LPEW_PAddress_House').getValue());
											ownerForm.findById('LPEW_PAddress_Corpus').setValue(ownerForm.findById('LPEW_PAddress_Corpus').getValue());
											ownerForm.findById('LPEW_PAddress_Flat').setValue(ownerForm.findById('LPEW_PAddress_Flat').getValue());
											ownerForm.findById('LPEW_PAddress_Address').setValue(ownerForm.findById('LPEW_PAddress_Address').getValue());
											ownerForm.findById('LPEW_PAddress_AddressText').setValue(ownerForm.findById('LPEW_PAddress_AddressText').getValue());
										},
										onTrigger3Click: function() {
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											ownerForm.findById('LPEW_PAddress_Zip').setValue('');
											ownerForm.findById('LPEW_PKLCountry_id').setValue('');
											ownerForm.findById('LPEW_PKLRGN_id').setValue('');
											ownerForm.findById('LPEW_PKLSubRGN_id').setValue('');
											ownerForm.findById('LPEW_PKLCity_id').setValue('');
											ownerForm.findById('LPEW_PKLTown_id').setValue('');
											ownerForm.findById('LPEW_PKLStreet_id').setValue('');
											ownerForm.findById('LPEW_PAddress_House').setValue('');
											ownerForm.findById('LPEW_PAddress_Corpus').setValue('');
											ownerForm.findById('LPEW_PAddress_Flat').setValue('');
											ownerForm.findById('LPEW_PAddress_Address').setValue('');
											ownerForm.findById('LPEW_PAddress_AddressText').setValue('');
										},
										readOnly: true,
										tabIndex: 1105,
										trigger1Class: 'x-form-search-trigger',
										trigger2Class: 'x-form-equil-trigger',
										trigger3Class: 'x-form-clear-trigger',
										width: 395
									}),
									{
										id: 'LPEW_PAddress_id',
										name: 'PAddress_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_Zip',
										name: 'PAddress_Zip',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLCountry_id',
										name: 'PKLCountry_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLRGN_id',
										name: 'PKLRGN_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLSubRGN_id',
										name: 'PKLSubRGN_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLCity_id',
										name: 'PKLCity_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLTown_id',
										name: 'PKLTown_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLStreet_id',
										name: 'PKLStreet_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_House',
										name: 'PAddress_House',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_Corpus',
										name: 'PAddress_Corpus',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_Flat',
										name: 'PAddress_Flat',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_Address',
										name: 'PAddress_Address',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_id',
										name: 'UAddress_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_Zip',
										name: 'UAddress_Zip',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLCountry_id',
										name: 'UKLCountry_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLRGN_id',
										name: 'UKLRGN_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLSubRGN_id',
										name: 'UKLSubRGN_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLCity_id',
										name: 'UKLCity_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLTown_id',
										name: 'UKLTown_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLStreet_id',
										name: 'UKLStreet_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_House',
										name: 'UAddress_House',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_Corpus',
										name: 'UAddress_Corpus',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_Flat',
										name: 'UAddress_Flat',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_Address',
										name: 'UAddress_Address',
										xtype: 'hidden'
									}, {
										allowBlank: false,
										anchor: '100%',
										disabled: true,
										fieldLabel: 'ОКФС',
										hiddenName: 'Okfs_id',
										id: 'LPEW_Okfs_id',
										tabIndex: 1105,
										xtype: 'swokfscombo'
									}, {
										allowBlank: false,
										anchor: '100%',
										disabled: true,
										fieldLabel: 'ОКОПФ',
										hiddenName: 'Okopf_id',
										id: 'LPEW_Okopf_id',
										tabIndex: 1105,
										xtype: 'swokopfcombo'
									}, {
										allowBlank: false,
										fieldLabel: 'УНП',
										id: 'LPEW_Org_INN',
										name: 'Org_INN',
										disabled: true,
										autoCreate: {tag: "input",  maxLength: "9", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										anchor: '100%',
										tabIndex: 1105
									}, {
										allowBlank: false,
										fieldLabel: 'Номер в ЕГР',
										id: 'LPEW_Org_OGRN',
										name: 'Org_OGRN',
										disabled: true,
										autoCreate: {tag: "input",  maxLength: "9", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										anchor: '100%',
										tabIndex: 1105
									}, {
										allowBlank: false,
										fieldLabel: 'ОКОГУ',
										id: 'LPEW_Okogu_id',
										hiddenName: 'Okogu_id',
										tabIndex: 1105,
										xtype: 'swokogucombo',
										anchor: '100%'
									}, {
										allowBlank: false,
										fieldLabel: 'ОКЭД',
										id: 'LPEW_Okved_id',
										hiddenName: 'Okved_id',
										tabIndex: 1105,
										xtype: 'swokvedcombo',
										anchor: '100%'
									}, {
										allowBlank: true,
										anchor: '100%',
										comboSubject: 'LpuSpecType',
										fieldLabel: 'Специализация',
										hiddenName: 'LpuSpecType_id',
										id: 'LPEW_LpuSpecType_id',
										prefix: 'passport201_',
										typeCode: 'int',
										xtype: 'swcommonsprcombo'
									}, {
										fieldLabel: 'Районный коэффициент',
										id: 'LPEW_Lpu_DistrictRate',
										name: 'Lpu_DistrictRate',
										maskRe: /[0-9]/,
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1105
									},{
										id: 'LPEW_Lpu_IsSecret',
										fieldLabel: 'Особый статус',
										tabIndex: 1105,
										xtype: 'checkbox',
										name: 'Lpu_IsSecret'
									}]
								},{
									autoHeight: true,
									title: 'Данные о регистрации',
									bodyStyle:'padding: 10px;',
									xtype: 'fieldset',
									columnWidth: 1,
									labelWidth: 180,
									items:
									[{
										fieldLabel: 'Орган',
										xtype: 'sworgcombo',
										autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
										anchor: '60%',
										id: 'LPEW_Org_lid',
										hiddenName: 'Org_lid',
										tabIndex: 1110,
										listeners:
										{
											'change': function()
											{
												//
											},
											keydown: function(inp, e)
											{
												if (e.getKey() == e.DELETE || e.getKey() == e.F4 )
												{
													e.stopEvent();
													if (e.browserEvent.stopPropagation)
													{
														e.browserEvent.stopPropagation();
													}
													else
													{
														e.browserEvent.cancelBubble = true;
													}
													if (e.browserEvent.preventDefault)
													{
														e.browserEvent.preventDefault();
													}
													else
													{
														e.browserEvent.returnValue = false;
													}
													e.returnValue = false;

													if (Ext.isIE)
													{
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													switch (e.getKey())
													{
														case e.DELETE:
															inp.clearValue();
															inp.ownerCt.ownerCt.findField('OrgLic_id').setRawValue(null);
															break;
														case e.F4:
															inp.onTrigger1Click();
															break;
													}
												}
											}
										},
										onTrigger1Click: function()
										{
											var combo = this;
											getWnd('swOrgSearchWindow').show({
												object: 'lic',
												onSelect: function(orgData) {
													if ( orgData.Org_id > 0 )
													{
														combo.getStore().load({
															params: {
																OrgType: 'lic',
																Org_id: orgData.Org_id,
																Org_Name:''
															},
															callback: function()
															{
																combo.setValue(orgData.Org_id);
																combo.focus(true, 500);
																combo.fireEvent('change', combo);
															}
														});
													}
													getWnd('swOrgSearchWindow').hide();
												},
												onClose: function() {combo.focus(true, 200)}
											});
										}
									},{
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										fieldLabel: 'Дата регистрации',
										format: 'd.m.Y',
										id: 'LPEW_Lpu_RegDate',
										name: 'Lpu_RegDate',
										tabIndex: 1110
									},{
										fieldLabel: 'Наименование регистрационного документа',
										xtype: 'textfield',
										id: 'LPEW_Lpu_DocReg',
										name: 'Lpu_DocReg',
										anchor: '60%',
										tabIndex: 1110
									},{
										fieldLabel: 'Рег. номер',
										autoCreate: {tag: "input",  maxLength: "13", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										id: 'LPEW_Lpu_RegNum',
										name: 'Lpu_RegNum',
										anchor: '60%',
										tabIndex: 1110
									},{
										fieldLabel: 'Рег. номер в ПФ',
										autoCreate: {tag: "input",  maxLength: "12", autocomplete: "off"},
										xtype: 'textfield',
										id: 'LPEW_Lpu_PensRegNum',
										name: 'Lpu_PensRegNum',
										maskRe: /[0-9]/,
										anchor: '60%',
										tabIndex: 1110
									}]
								}]
							}]
						}),
						new sw.Promed.Panel({
							autoHeight: true,
							style:'margin-bottom: 0.5em;',
							border: true,
							collapsible: true,
							collapsed: true,
							region: 'north',
							id: 'LPEW_Lpu_DLOPanel',
							layout: 'form',
							title: '2. ЛЛО',
							listeners: {
								expand: function () {
									this.findById('LPEW_DLOGrid').removeAll({clearAll: true});
									this.findById('LPEW_DLOGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}.createDelegate(this)
							},
							items: [
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add'},
										{name: 'action_edit'},
										{name: 'action_view'},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									object: 'LpuPeriodDLO',
									editformclassname: 'swLpuPeriodDLOEditWindow',
									autoExpandColumn: 'autoexpand',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadLpuPeriodDLOGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_DLOGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'LpuPeriodDLO_id', type: 'int', header: 'ID', key: true},
										{name: 'LpuPeriodDLO_begDate', type: 'string', header: 'Дата включения', width: 120},
										{name: 'LpuPeriodDLO_endDate', type: 'string', header: 'Дата исключения', width: 120}
									]
								})
							]		
						}),
						new sw.Promed.Panel({
							autoHeight: true,
							style:'margin-bottom: 0.5em;',
							border: true,
							collapsible: true,
							collapsed: true,
							id: 'LPEW_Lpu_DMSPanel',
							layout: 'form',
							region: 'center', 
							title: '3. ДМС',
							listeners: {
								expand: function () {
									this.findById('LPEW_DMSGrid').removeAll({clearAll: true});
									this.findById('LPEW_DMSGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}.createDelegate(this)
							},
							items: [
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add'},
										{name: 'action_edit'},
										{name: 'action_view'},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									object: 'LpuPeriodDMS',
									editformclassname: 'swLpuPeriodDMSEditWindow',
									autoExpandColumn: 'autoexpand',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadLpuPeriodDMSGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_DMSGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'LpuPeriodDMS_id', type: 'int', header: 'ID', key: true},
										{name: 'LpuPeriodDMS_begDate', type: 'string', header: 'Дата включения', width: 120},
										{name: 'LpuPeriodDMS_endDate', type: 'string', header: 'Дата исключения', width: 120},
										{name: 'LpuPeriodDMS_DogNum', type: 'string', header: 'Номер договора', width: 120}
									]
								})
							]		
						}),
						new sw.Promed.Panel({
							autoHeight: true,
							style:'margin-bottom: 0.5em;',
							border: true,
							collapsible: true,
							collapsed: true,
							id: 'LPEW_Lpu_FondHolderPanel',
							layout: 'form',
							title: '4. Участковая служба',
							listeners: {
								expand: function () {
									this.findById('LPEW_FondHolderGrid').removeAll({clearAll: true});
									this.findById('LPEW_FondHolderGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}.createDelegate(this)
							},
							items: [
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add'},
										{name: 'action_edit'},
										{name: 'action_view'},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									object: 'LpuPeriodFondHolder',
									editformclassname: 'swLpuPeriodFondHolderEditWindow',
									autoExpandColumn: 'autoexpand',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadLpuPeriodFondHolderGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_FondHolderGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'LpuPeriodFondHolder_id', type: 'int', header: 'ID', key: true},
										{name: 'LpuPeriodFondHolder_begDate', type: 'string', header: 'Дата включения', width: 120},
										{name: 'LpuPeriodFondHolder_endDate', type: 'string', header: 'Дата исключения', width: 120},
										{name: 'LpuRegionType_Name', type: 'string', header: 'Тип участка', width: 250}
									]
								})
							]		
						})
					]})
				},
				{
					title: '2. Справочная информация',
					layout: 'fit',
					id: 'tab_sprav',
					iconCls: 'info16',
					border:false,
					items: [
						new Ext.form.FormPanel({
							autoScroll: true,
							bodyBorder: false,
							bodyStyle: 'padding: 5px 5px 0',
							border: false,
							frame: false,
							id: 'Lpu_SupInfoPanel',
							labelAlign: 'right',
							labelWidth: 180,
							items: [
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									id: 'Lpu_SupInfoEditForm',
									layout: 'form',
									title: '1. Справочная информация',
									items: [{
										xtype: 'panel',
										layout: 'form',
										border: false,
										bodyStyle:'background:#DFE8F6;padding:5px;',
										items: [{
											xtype: 'panel',
											layout: 'column',
											border: false,
											bodyStyle:'background:#DFE8F6;padding:5px;',
											items: [{// Левая часть 
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:5px;',
												columnWidth: .50,
												labelWidth: 180,
												items: 
												[{
													id: 'LPEW_LpuLevelType_id',
													name: 'LpuLevelType_id',
													xtype: 'hidden'
												}, {
													id: 'LPEW_LpuSubjectionLevel_id',
													moreFields: [
														{ name: 'LpuSubjectionLevel_pid', mapping: 'LpuSubjectionLevel_pid' }
													],
													fieldLabel: 'Подчиненность организации',
													tabIndex: 1120,
													disabled: true,
													allowBlank: true,
													comboSubject: 'LpuSubjectionLevel',
													xtype: 'swcommonsprcombo',
													anchor: '100%',
													lastQuery: '',
													typeCode: 'int',
													name: 'LpuSubjectionLevel_id'
												}, {
													id: 'LPEW_LpuLevel_id',
													fieldLabel: 'Технологический уровень МП',
													tabIndex: 1120,
													disabled: true,
													allowBlank: true,
													comboSubject: 'LpuLevel',
													xtype: 'swcommonsprcombo',
													anchor: '100%',
													hiddenName: 'LpuLevel_id'
												}, {
													id: 'LPEW_LevelType_id',
													fieldLabel: 'Уровень оказания МП',
													tabIndex: 1120,
													disabled: true,
													comboSubject: 'LevelType',
													xtype: 'swcommonsprcombo',
													anchor: '100%',
													hiddenName: 'LevelType_id'
												}, {
													id: 'LPEW_Lpu_VizitFact',
													fieldLabel: 'Посещений в смену',
													tabIndex: 1120,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "4", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_VizitFact'
												}, {
													id: 'LPEW_Lpu_KoikiFact',
													fieldLabel: 'Число коек',
													tabIndex: 1120,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "4", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_KoikiFact'
												}, {
													id: 'LPEW_Lpu_AmbulanceCount',
													fieldLabel: 'Число выездных бригад ВОВ',
													tabIndex: 1120,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "2", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_AmbulanceCount'
												},{
													comboSubject: 'InstitutionLevel',
													width: 150,
													hiddenName: 'InstitutionLevel_id',
													id: 'LPEW_InstitutionLevel_id',
													fieldLabel: 'Уровень учреждения в иерархии сети',
													tabIndex: 1122,
													xtype: 'swcommonsprcombo'
												},{
													layout: 'form',
													border: false,
													hidden: true,
													bodyStyle:'background:#DFE8F6;padding-right:0px;',
													//labelWidth: 180,
													items: [{
														id: 'LPEW_PasportMO_IsAssignNasel',
														tabIndex: 1125,
														name: 'PasportMO_IsAssignNasel',
														fieldLabel: 'МО имеет приписное население',
														xtype: 'checkbox'
													}]
												}]
											},{// Правая часть 
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:5px;',
												columnWidth: .50,
												labelWidth: 240,
												items: 
												[{
													id: 'LPEW_Lpu_FondOsn',
													fieldLabel: 'Фондооснащенность на 1 кв.м (руб.) (Отношение стоимости основных фондов к площади организации)',
													tabIndex: 1125,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "6", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_FondOsn'
												}, {
													id: 'LPEW_Lpu_FondEquip',
													fieldLabel: 'Фондовооруженность на 1 врача (руб.) (Отношение стоимости основных фондов к численности врачей)',
													tabIndex: 1125,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "8", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_FondEquip'
												}, {
													fieldLabel: 'Головное учреждение',
													hiddenName: 'Lpu_gid',
													id: 'LPEW_Lpu_gid',
													listWidth: 400,
													anchor: '100%',
													xtype: 'swlpucombo'
												}]
											}]
										},{
											xtype: 'fieldset',
											layout: 'column',
											border: true,
											bodyStyle:'background:#DFE8F6;padding:5px;',
											title: 'Опции автоматического занесения в регистр часто обращающихся',
											height: 70,
											id: 'LPEW_Lpu_OftenCallers_Panel',
											items: [
												{
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:0px;',
												//labelWidth: 180,
												items: [{
													id: 'LPEW_Lpu_OftenCallers_CallTimes',
													fieldLabel: 'Количество обращений',
													tabIndex: 1130,
													xtype: 'textfield',
													labelSeparator: '',
													autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_OftenCallers_CallTimes'
												}]
											},{
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:0px;',
												labelWidth: 15,
												items: [{
													id: 'LPEW_Lpu_OftenCallers_SearchDays',
													fieldLabel: ' за ',
													tabIndex: 1135,
													xtype: 'textfield',
													labelSeparator: '',
													autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_OftenCallers_SearchDays'
												}]
											},{
												xtype: 'label',
												html:'дней для включения в регистр.',
												style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;'
											},{
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:0px;',
												labelWidth: 250,
												items: [{
													id: 'LPEW_Lpu_OftenCallers_FreeDays',
													fieldLabel: 'Дней без обращений для снятия статуса: ',
													tabIndex: 1140,
													xtype: 'textfield',
													labelSeparator: '',
													autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_OftenCallers_FreeDays'
												}]
											}]
										}
									]
									}]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_Licence',
									layout: 'form',
									title: '2. Лицензии МО',
									listeners: {
										expand: function () {
											this.findById('LPEW_LpuLicenceGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_edit'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'LpuLicence',
											editformclassname: 'swLpuLicenceEditWindow',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											dataUrl: '/?c=LpuPassport&m=loadLpuLicenceGrid',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_LpuLicenceGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'LpuLicence_id', type: 'int', header: 'ID', key: true},
												{name: 'LpuLicence_Num', type: 'string', header: 'Номер лицензии', width: 120},
												{name: 'LpuLicence_setDate', type: 'date', header: 'Дата выдачи', width: 120},
												{name: 'LpuLicence_RegNum', type: 'string', header: 'Регистрационный номер', width: 180},
												{name: 'LpuLicence_begDate', type: 'date', header: 'Начало действия', width: 120},
												{name: 'LpuLicence_endDate', type: 'date', header: 'Окончание действия', width: 120}
											]
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_RSchet',
									layout: 'form',
									title: '3. Расчетный счет',
									listeners: {
										expand: function () {
											this.findById('LPEW_OrgRSchetGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add', handler: function() {this.openOrgRSchetEditWindow('add', this.Lpu_id);}.createDelegate(this)},
												{name: 'action_edit', handler: function() {this.openOrgRSchetEditWindow('edit', this.Lpu_id);}.createDelegate(this)},
												{name: 'action_view', handler: function() {this.openOrgRSchetEditWindow('view', this.Lpu_id);}.createDelegate(this)},
												{name: 'action_delete', handler: function() {this.deleteOrgRSchet();}.createDelegate(this)},
												{name: 'action_refresh', handler: function() {Ext.getCmp('LPEW_OrgRSchetGrid').getGrid().getStore().removeAll();Ext.getCmp('LPEW_OrgRSchetGrid').getGrid().getStore().load();}.createDelegate(this)},
												{name: 'action_print'}
											],
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											dataUrl: '/?c=Org&m=loadOrgRSchetGrid',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_OrgRSchetGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'OrgRSchet_id', type: 'int', header: 'ID', key: true},
												{name: 'OrgRSchet_Name', type: 'string', header: 'Наименование', width: 270},
												{name: 'OrgBank_Name', type: 'string', header: 'Банк', width: 270},
												{name: 'OrgRSchet_RSchet', type: 'string', header: 'Номер счета', width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_MOInfoSys',
									layout: 'form',
									title: '4. Информационная система',
									listeners: {
										expand: function () {
											this.findById('LPEW_MOInfoSysGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id},  params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'MOInfoSys',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'fed',
											dataUrl: '/?c=LpuPassport&m=loadMOInfoSys',
											editformclassname: 'swMOInfoSysEditWindow',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_MOInfoSysGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'MOInfoSys_id', type: 'int', header: 'ID', key: true},
												{name: 'MOInfoSys_Name', type: 'string', header: 'Наименование ИС', width: 270},
												{name: 'DInfSys_Name', type: 'string', header: 'Тип ИС', width: 270, id: 'autoexpand'},
												{name: 'MOInfoSys_Cost', type: 'string', header: 'Стоимость ИС, Br.', width: 270},
												{name: 'MOInfoSys_CostYear', type: 'int', header: 'Стоимость сопровождения ИС в год, Br.', width: 270},
												{name: 'MOInfoSys_IntroDT', type: 'date', header: 'Дата внедрения', width: 270},
												{name: 'MOInfoSys_IsMainten', type: 'checkcolumn', header: 'Признак сопровождения', width: 180},
												{name: 'MOInfoSys_NameDeveloper', type: 'string', header: 'Наименование разработчика', width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_SpecializationMO',
									layout: 'form',
									title: '5. Специализация организации',
									listeners: {
										expand: function () {
											this.findById('LPEW_SpecializationMOGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'SpecializationMO',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'fed',
											dataUrl: '/?c=LpuPassport&m=loadSpecializationMO',
											editformclassname: 'swSpecializationMOEditWindow',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_SpecializationMOGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'SpecializationMO_id', type: 'int', header: 'ID', key: true},
												{name: 'Mkb10Code_id', type: 'string', header: 'Код МКБ-10', width: 270},
												{name: 'SpecializationMO_MedProfile', type: 'string', header: 'Медицинский профиль', width: 270},
												{name: 'LpuLicence_Num', type: 'string', header: 'Номер лицензии', width: 270},
												{name: 'SpecializationMO_IsDepAftercare', type: 'checkcolumn', header: 'Наличие отделения долечивания', width: 190}
											 ],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_MedUsluga',
									layout: 'form',
									title: '6. Медицинские услуги',
									listeners: {
										expand: function () {
											this.findById('LPEW_MedUslugaGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'MedUsluga',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'fed',
											dataUrl: '/?c=LpuPassport&m=loadMedUsluga',
											editformclassname: 'swMedUslugaEditWindow',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_MedUslugaGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'MedUsluga_id', type: 'int', header: 'ID', hidden: true, key: true},
												{name: 'DUslugi_id', type: 'int', header: 'ID', hidden: true},
												{name: 'DUslugi_Name', type: 'string', header: 'Наименование услуги', width: 270},
												{name: 'MedUsluga_LicenseNum', type: 'int', header: 'Номер лицензии', width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_MedTechnology',
									layout: 'form',
									title: '7. Медицинские технологии',
									listeners: {
										expand: function () {
											this.findById('LPEW_MedTechnologyGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'MedTechnology',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'fed',
											dataUrl: '/?c=LpuPassport&m=loadMedTechnology',
											editformclassname: 'swMedTechnologyEditWindow',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_MedTechnologyGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'MedTechnology_id', type: 'int', header: 'ID', key: true},
												{name: 'MedTechnology_Name', type: 'string', header: 'Наименование медицинской технологии', width: 270},
												{name: 'TechnologyClass_Name', type: 'string', header: 'Класс технологии', width: 270},
												{name: 'LpuBuildingPass_Name', type: 'string', header: 'Идентификатор здания', width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_UslugaComplexLpu',
									layout: 'form',
									title: '8. Направления оказания медицинской помощи',
									listeners: {
										expand: function () {
											this.findById('LPEW_UslugaComplexLpuGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'UslugaComplexLpu',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'passport',
											dataUrl: '/?c=LpuPassport&m=loadUslugaComplexLpu',
											editformclassname: 'swUslugaComplexLpuEditWindow',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_UslugaComplexLpuGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'UslugaComplexLpu_id', type: 'int', header: 'ID', key: true},
												{name: 'UslugaComplex_id', header: 'Id услуги', hidden: true},
												{name: 'UslugaComplex_Code', type: 'string', header: 'Код услуги', width: 270},
												{name: 'UslugaComplex_Name', type: 'string', header: 'Наименование услуги', width: 270},
												{name: 'UslugaComplexLpu_begDate', type: 'date', header: 'Дата начала оказания услуги', width: 270},
												{name: 'UslugaComplexLpu_endDate', type: 'date', header: 'Дата окончания оказания услуги', width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_PitanFormTypeLink',
									layout: 'form',
									title: '9. Питание',
									listeners: {
										expand: function () {
											this.findById('LPEW_PitanFormTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'PitanFormTypeLink',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'fed',
											dataUrl: '/?c=LpuPassport&m=loadPitanFormTypeLink',
											editformclassname: 'swPitanFormTypeLinkEditWindow',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_PitanFormTypeLinkGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'PitanFormTypeLink_id', type: 'int', header: 'ID', key: true},
												{name: 'VidPitan_Name', type: 'string', header: 'Вид питания', width: 270},
												{name: 'PitanCnt_Name', type: 'string', header: 'Кратность питания', width: 270},
												{name: 'PitanForm_Name', type: 'string', header: 'Форма питания', width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_PlfDocTypeLink',
									layout: 'form',
									title: '10. Природные лечебные факторы',
									listeners: {
										expand: function () {
											this.findById('LPEW_PlfDocTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'PlfDocTypeLink',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'fed',
											dataUrl: '/?c=LpuPassport&m=loadPlfDocTypeLink',
											editformclassname: 'swPlfDocTypeLinkEditWindow',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_PlfDocTypeLinkGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'PlfDocTypeLink_id', type: 'int', header: 'ID', key: true},
												{name: 'Plf_Name', type: 'string', header: 'Наименование фактора', width: 270},
												{name: 'PlfType_Name', type: 'string', header: 'Тип фактора', width: 270},
												{name: 'DocTypeUsePlf_Name', type: 'string', header: 'Документ', width: 270},
												{name: 'PlfDocTypeLink_Num', type: 'string', header: 'Номер документа', width: 270},
												{name: 'PlfDocTypeLink_GetDT', type: 'date', header: 'Дата выдачи документа', width: 270},
												{name: 'PlfDocTypeLink_BegDT', type: 'date', header: 'Дата начала действия фактора', width: 270},
												{name: 'PlfDocTypeLink_EndDT', type: 'date', header: 'Дата окончания действия фактора', width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_PlfObjectCount',
									layout: 'form',
									title: '11. Объекты/места использования природных лечебных факторов',
									listeners: {
										expand: function () {
											this.findById('LPEW_PlfObjectCountGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'PlfObjectCount',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'fed',
											dataUrl: '/?c=LpuPassport&m=loadPlfObjectCount',
											editformclassname: 'swPlfObjectCountEditWindow',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_PlfObjectCountGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'PlfObjectCount_id', type: 'int', header: 'ID', key: true},
												{name: 'PlfObjects_Name', type: 'string', header: 'Наименование объекта', width: 270},
												{name: 'PlfObjectCount_Count', type: 'int', header: 'Количество объектов по использованию', width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_LpuMobileTeam',
									layout: 'form',
									title: '12. Мобильные бригады',
									listeners: {
										expand: function () {
											this.findById('LPEW_LpuMobileTeamGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_edit', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_view'},
												{name: 'action_delete', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'LpuMobileTeam',
											editformclassname: 'swLpuMobileTeamEditWindow',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											dataUrl: '/?c=LpuPassport&m=loadLpuMobileTeamGrid',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_LpuMobileTeamGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'LpuMobileTeam_id', type: 'int', header: 'ID', key: true},
												{name: 'LpuMobileTeam_begDate', type: 'date', header: 'Дата начала', width: 100},
												{name: 'LpuMobileTeam_endDate', type: 'date', header: 'Дата окончания', width: 100},
												{name: 'LpuMobileTeam_Count', type: 'int', header: 'Количество бригад', width: 100},
												{name: 'DispClass_Name', type: 'string', header: 'Тип бригады', width: 200, id: 'autoexpand'}
											],
											totalProperty: 'totalCount',
											toolbar: true
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_FunctionTime',
									layout: 'form',
									title: '13. Периоды функционирования',
									listeners: {
										expand: function () {
											this.findById('LPEW_FunctionTimeGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_edit', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_view'},
												{name: 'action_delete', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'FunctionTime',
											editformclassname: 'swFunctionTimeEditWindow',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											scheme: 'passport',
											dataUrl: '/?c=LpuPassport&m=loadFunctionTime',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_FunctionTimeGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'FunctionTime_id', type: 'int', header: 'ID', key: true},
												{name: 'InstitutionFunction_id', type: 'int', hidden: true},
												{name: 'InstitutionFunction_Name', type: 'string', id: 'autoexpand', header: 'Период функционирования учреждения'},
												{name: 'FunctionTime_begDate', type: 'date', header: 'Дата начала периода', width: 150},
												{name: 'FunctionTime_endDate', type: 'date', header: 'Дата окончания периода', width: 150}
											],
											totalProperty: 'totalCount',
											toolbar: true
										})
									]
								})
							]
						})
					],
					listeners: {
						activate: function(){
							if (!Ext.isEmpty(this.Lpu_id)) {
								if (!Ext.getCmp('Lpu_Licence').collapsed)
									Ext.getCmp('LPEW_LpuLicenceGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_RSchet').collapsed)
									Ext.getCmp('LPEW_OrgRSchetGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_LpuMobileTeam').collapsed)
									Ext.getCmp('LPEW_LpuMobileTeamGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				},
				{
					title: '3. Руководство',
					layout: 'fit',
					id: 'tab_ruk',
					iconCls: 'info16',
					border:false,
					items: [
						new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {this.openOrgHeadEditWindow('add');}.createDelegate(this)},
								{name: 'action_edit', handler: function() {this.openOrgHeadEditWindow('edit');}.createDelegate(this)},
								{name: 'action_view', handler: function() {this.openOrgHeadEditWindow('view');}.createDelegate(this)},
								{name: 'action_delete', handler: function() {this.deleteOrgHead();}.createDelegate(this)},
								{name: 'action_refresh', handler: function() {Ext.getCmp('LPEW_OrgHeadGrid').getGrid().getStore().removeAll();Ext.getCmp('LPEW_OrgHeadGrid').getGrid().getStore().load();}.createDelegate(this)},
								{name: 'action_print'/*, handler: function() { this.printEvnRP(); }.createDelegate(this)*/}
							],
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 150,
							autoLoadData: false,
							dataUrl: '/?c=Org&m=loadOrgHeadGrid',
							focusOn: {
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							focusPrev: {
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							id: 'LPEW_OrgHeadGrid',
							//pageSize: 100,
							paging: false,
							region: 'center',
							//root: 'data',
							stringfields: [
								{name: 'OrgHead_id', type: 'int', header: 'ID', key: true},
								{name: 'Person_id', type: 'int', hidden: true},
								{name: 'OrgHeadPerson_Fio', type: 'string', header: 'ФИО', width: 270},
								{name: 'OrgHeadPost_Name', type: 'string', header: 'Должность', width: 270},
								{name: 'OrgHead_Phone', type: 'string', header: 'Телефон(ы)', width: 270},
								{name: 'OrgHead_Fax', type: 'string', header: 'Факс', width: 270}
							],
							title: 'Руководство',
							toolbar: true,
							totalProperty: 'totalCount'
						})
					],
					listeners: {
						activate: function() {
							if (!Ext.isEmpty(this.Lpu_id)) {
								Ext.getCmp('LPEW_OrgHeadGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				},
				{
					title: '4. Договоры по сторонним специалистам',
					layout: 'fit',
					id: 'tab_dogdd',
					iconCls: 'info16',
					border:false,
					items: 
					[
						new sw.Promed.ViewFrame(
						{
							autoLoadData: false,
							dataUrl: '/?c=LpuPassport&m=loadLpuDispContract',
							focusOn: 
							{
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							focusPrev: 
							{
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							id: 'LPEW_DogDDGrid',
							paging: false,
							region: 'center',
							object: 'LpuDispContract',
							editformclassname: 'swLpuDispContractEditWindow',
							stringfields: 
							[
								{name: 'LpuDispContract_id', type: 'int', header: 'ID', key: true},
								{name: 'LpuDispContract_setDate', type: 'date', header: 'Дата'},
								{name: 'LpuDispContract_Num', type: 'string', header: 'Номер'},
								{name: 'Lpu_oid', type: 'int', hidden: true},
								{id: 'autoexpand', name: 'Lpu_Nick', type: 'string', header: 'МО', width: 250},
								{name: 'LpuSectionProfile_Code', type: 'string', header: 'Код профиля'},
								{name: 'LpuSection_id', type: 'int', hidden: true},
								{name: 'LpuSection_Name', type: 'string', header: 'Отделение', width: 220}
							],
							title: 'Договоры по сторонним специалистам',
							toolbar: true
						})
					],
					listeners: 
					{
						activate: function() {
							if (!Ext.isEmpty(this.Lpu_id)) {
								Ext.getCmp('LPEW_DogDDGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				},
				{
					title: '5. Электронная регистратура',
					layout: 'fit',
					id: 'tab_er',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					iconCls: 'info16',
					scrolable: true,
					border:false,
					items: [
						{
							xtype: 'checkbox',
							autoHeight:true,
							boxLabel: 'Разрешить модерацию записей из интернет',
							id:'LPEW_IsAllowInternetModeration',
							name:'Lpu_IsAllowInternetModeration'
						},
						{
						xtype: 'panel',
						layout: 'form',
						border: false,
						id: 'LPEW_Lpu_ERPanel',
						bodyStyle:'background:#DFE8F6;padding:5px;',
						labelWidth: 170,
						labelAlign: 'top',
						items: 
						[{
							xtype: 'textarea',
							//autoCreate: {tag: "input", size:5, maxLength: "3", autocomplete: "off"},
							fieldLabel: 'Информация о возможности записи пациентов организацией, оказывающей услугу "Единая регистратура"',
							name: 'Lpu_ErInfo',
							id: 'LPEW_Lpu_ErInfo',
							height: 200,
							width: 800
						}],
						listeners: 
						{
							activate: function() {
								//Ext.getCmp('LpuPassportEditWindow').findById('LPEW_Lpu_AmbulanceCount').focus(100);
							}
						}
					}]
				},
				{
					title: '6. Здания МО',
					layout: 'fit',
					id: 'tab_zdanie',
					iconCls: 'info16',
					autoScroll: true,
					border:false,
					items: [
						new Ext.form.FormPanel({
						autoScroll: true,
						bodyBorder: false,
						bodyStyle: 'padding: 5px 5px 0',
						border: false,
						frame: false,
						id: 'Lpu_PasportMOPanel',
						labelAlign: 'right',
						labelWidth: 180,
						items: [
							new sw.Promed.Panel({
								autoHeight: true,
								style:'margin-bottom: 0.5em;',
								//border: true,
								collapsible: true,
								collapsed: true,
								id: 'Lpu_PasportMO',
								layout: 'form',
								title: '1. Общая информация',
								items: [{
									xtype: 'panel',
									layout: 'form',
									labelWidth: 220,
									border: false,
									bodyStyle:'background:#DFE8F6;padding:5px;',
									items: [{
										id: 'LPEW_PasportMO_id',
										xtype: 'hidden',
										name: 'PasportMO_id'
									},{
										id: 'LPEW_DLocationLpu_id',
										comboSubject: 'DLocationLpu',
										fieldLabel: 'Местоположение',
										tabIndex: 1120,
										xtype: 'swcommonsprcombo',
										width: 150,
										hiddenName: 'DLocationLpu_id'
									},{
										fieldLabel: 'Ограждение территории',
										xtype: 'checkbox',
										anchor: '100%',
										name: 'PasportMO_IsFenceTer',
										id: 'LPEW_PasportMO_IsFenceTer',
										tabIndex: TABINDEX_LPEEW + 1
									},{
										fieldLabel: 'Наличие охраны',
										xtype: 'checkbox',
										anchor: '100%',
										name: 'PasportMO_IsSecur',
										id: 'LPEW_PasportMO_IsSecur',
										tabIndex: TABINDEX_LPEEW + 1
									},{
										fieldLabel: 'Наличие металлических входных дверей в здание',
										xtype: 'checkbox',
										anchor: '100%',
										name: 'PasportMO_IsMetalDoors',
										id: 'LPEW_PasportMO_IsMetalDoors',
										tabIndex: TABINDEX_LPEEW + 1
									},{
										fieldLabel: 'Видеонаблюдение территорий и помещений для здания',
										xtype: 'checkbox',
										anchor: '100%',
										name: 'PasportMO_IsVideo',
										id: 'LPEW_PasportMO_IsVideo',
										tabIndex: TABINDEX_LPEEW + 1
									},{
										fieldLabel: 'Проживание сопровождающих лиц',
										xtype: 'checkbox',
										anchor: '100%',
										name: 'PasportMO_IsAccompanying',
										id: 'LPEW_PasportMO_IsAccompanying',
										tabIndex: TABINDEX_LPEEW + 1
									},{
										fieldLabel: 'Приспособленность территории для пациентов с ограниченными возможностями',
										xtype: 'checkbox',
										anchor: '100%',
										name: 'PasportMO_IsTerLimited',
										tabIndex: TABINDEX_LPEEW + 1,
										id: 'LPEW_PasportMO_IsTerLimited'
									}]
								}]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								style: 'margin-bottom: 0.5em;',
								border: true,
								collapsible: true,
								collapsed: true,
								id: 'LPEW_MOArea',
								layout: 'form',
								title: '2. Площадка, занимаемая организацией',
								listeners: {
									expand: function () {
										this.findById('LPEW_MOAreaGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									}.createDelegate(this)
								},
								items: [
									new sw.Promed.ViewFrame({
										actions: [
											{name: 'action_add'},
											{name: 'action_edit'},
											{name: 'action_view'},
											{name: 'action_delete'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										object: 'MOArea',
										editformclassname: 'swMOAreaEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										border: false,
										scheme: 'fed',
										dataUrl: '/?c=LpuPassport&m=loadMOArea',
										focusOn: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										focusPrev: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										id: 'LPEW_MOAreaGrid',
										//pageSize: 100,
										paging: false,
										region: 'center',
										//root: 'data',
										stringfields: [
											{name: 'MOArea_id', type: 'int', header: 'id', hidden: true},
											{name: 'MOArea_Name', type: 'string', header: 'Наименование площадки', width: 200},
											{name: 'MOArea_Member', type: 'string', header: 'Идентификатор участка', width: 200},
											{name: 'MoArea_Right', type: 'string', header: 'Право на земельный участок', width: 200},
											{name: 'MoArea_Space', header: 'Площадь участка, га', width: 100, renderer: this.numberRenderer },
											{name: 'MoArea_KodTer', type: 'string', header: 'Код территории', width: 100},
											{name: 'MoArea_OrgDT', type: 'date', header: 'Дата организации', width: 100},
											{name: 'MoArea_AreaSite', header: 'Площадь площадки, га', width: 100, renderer: this.numberRenderer },
											{name: 'MoArea_OKATO', type: 'string', header: 'Код по СОАТО', width: 100},
											{name: 'Address_Address', type: 'string', header: 'Адрес', id: 'autoexpand', width: 200}
										],
										toolbar: true,
										totalProperty: 'totalCount'
									})
								]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								style:'margin-bottom: 0.5em;',
								//border: true,
								collapsible: true,
								collapsed: true,
								id: 'Lpu_Svyaz',
								layout: 'form',
								title: '3. Связь с транспортными узлами',
								listeners: {
									expand: function () {
										this.findById('LPEW_TransportConnectGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									}.createDelegate(this)
								},
								items: [
									new sw.Promed.ViewFrame({
										actions: [
											{name: 'action_add', handler: function() { _this.openTransportConnectEditWindow('add')} },
											{name: 'action_edit', handler: function() { _this.openTransportConnectEditWindow('edit')} },
											{name: 'action_view', handler: function() { _this.openTransportConnectEditWindow('view')} },
											{name: 'action_delete'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										object: 'TransportConnect',
										editformclassname: 'swTransportConnectEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										border: false,
										scheme: 'passport',
										dataUrl: '/?c=LpuPassport&m=loadTransportConnect',
										focusOn: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										focusPrev: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										id: 'LPEW_TransportConnectGrid',
										//pageSize: 100,
										paging: false,
										region: 'center',
										//root: 'data',
										stringfields: [
											{name: 'TransportConnect_id', type: 'int', header: 'id', hidden: true},
											{name: 'MOArea_id', type: 'int',   header: 'id', hidden: true, width: 200},
											{name: 'MOArea_Name', type: 'string', header: 'Наименование площадки, занимаемой учреждением', width: 200},
											//{name: 'TransportConnect_AreaIdent', type: 'string', header: 'Идентификатор участка ', width: 200},
											{name: 'TransportConnect_Station', type: 'string', header: 'Ближайшая станция', width: 200},
											{name: 'TransportConnect_DisStation', header: 'Расстояние до ближайшей станции (км)', width: 200, renderer: this.numberRenderer },
											{name: 'TransportConnect_Airport', type: 'string', header: 'Ближайший аэропорт', width: 200},
											{name: 'TransportConnect_DisAirport', header: 'Расстояние до аэропорта (км)', width: 200, renderer: this.numberRenderer },
											{name: 'TransportConnect_Railway', type: 'string', header: 'Ближайший автовокзал', width: 200},
											{name: 'TransportConnect_DisRailway', header: 'Расстояние до автовокзала (км)', width: 200, renderer: this.numberRenderer },
											{name: 'TransportConnect_Heliport', type: 'string', header: 'Ближайшая вертолетная площадка', width: 200},
											{name: 'TransportConnect_DisHeliport', header: 'Расстояние до вертолетной площадки (км)', width: 200, renderer: this.numberRenderer },
											{name: 'TransportConnect_MainRoad', type: 'string', header: 'Главная дорога', width: 200}
										],
										toolbar: true,
										totalProperty: 'totalCount'
									})]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								style: 'margin-bottom: 0.5em;',
								border: true,
								collapsible: true,
								collapsed: true,
								id: 'LPEW_LpuBuilding_id',
								layout: 'form',
								title: '4. Здания МО',
								listeners: {
									expand: function () {
										this.findById('LPEW_LpuBuilding').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									}.createDelegate(this)
								},
								items: [
									new sw.Promed.ViewFrame({
										actions: [
											{name: 'action_add'},
											{name: 'action_edit'},
											{name: 'action_view'},
											{name: 'action_delete'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										object: 'LpuBuildingPass',
										editformclassname: 'swLpuBuildingEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										dataUrl: '/?c=LpuPassport&m=loadLpuBuilding',
										focusOn: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										focusPrev: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										id: 'LPEW_LpuBuilding',
										//pageSize: 100,
										paging: false,
										region: 'center',
										//root: 'data',
										stringfields: [
											{name: 'LpuBuildingPass_id', type: 'int', header: 'ID', key: true},
											{name: 'LpuBuildingPass_Name', type: 'string', header: 'Наименование', width: 240},
											{name: 'LpuBuildingPass_Number', type: 'string', header: 'Номер', width: 80},
											{name: 'LpuBuildingType_Name', type: 'string', header: 'Тип', width: 240},
											{name: 'BuildingAppointmentType_Name', type: 'string', header: 'Назначение', width: 180},
											{name: 'LpuBuildingPass_YearBuilt', type: 'int', header: 'Год постройки', width: 120},
											{name: 'LpuBuildingPass_TotalArea', header: 'Общая площадь', width: 180, renderer: this.numberRenderer},
											{name: 'LpuBuildingPass_RegionArea', type: 'string', header: 'Площадь участка', width: 180}
										],
										toolbar: true,
										//title: '4. Здания МО',
										totalProperty: 'totalCount'
									})
								]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								style: 'margin-bottom: 0.5em;',
								border: true,
								collapsible: true,
								collapsed: true,
								id: 'LPEW_MOAreaObject',
								layout: 'form',
								title: '5. Объекты инфраструктуры',
								listeners: {
									expand: function () {
										this.findById('LPEW_MOAreaObjectGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									}.createDelegate(this)
								},
								items: [
									new sw.Promed.ViewFrame({
										actions: [
											{name: 'action_add'},
											{name: 'action_edit'},
											{name: 'action_view'},
											{name: 'action_delete'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										object: 'MOAreaObject',
										editformclassname: 'swMOAreaObjectEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										border: false,
										scheme: 'fed',
										dataUrl: '/?c=LpuPassport&m=loadMOAreaObject',
										focusOn: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										focusPrev: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										id: 'LPEW_MOAreaObjectGrid',
										//pageSize: 100,
										paging: false,
										region: 'center',
										//root: 'data',
										stringfields: [
											{name: 'MOAreaObject_id', type: 'int', header: 'id', hidden: true},
											{name: 'DObjInfrastructure_Name', type: 'string', header: 'Наименование объекта', width: 270},
											{name: 'MOAreaObject_Count', type: 'int', header: 'Количество объектов', width: 270},
											{name: 'MOAreaObject_Member', type: 'string', header: 'Идентификатор участка'}
										],
										toolbar: true,
										totalProperty: 'totalCount'
									})
								]
							})

					],
					listeners:
					{
						activate: function() {
							if (!Ext.isEmpty(this.Lpu_id)) {
								Ext.getCmp('LPEW_LpuBuilding').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				})]
				},{
					title: '7. Оборудование и транспорт',
					layout: 'fit',
					id: 'tab_medprod',
					iconCls: 'info16',
					border:false,
					items: [
						new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {this.openMedProductCardEditWindow('add', this.Lpu_id);}.createDelegate(this)},
								{name: 'action_edit', handler: function() {this.openMedProductCardEditWindow('edit', this.Lpu_id);}.createDelegate(this)},
								{name: 'action_view', handler: function() {this.openMedProductCardEditWindow('view', this.Lpu_id);}.createDelegate(this)},
								{name: 'action_delete', handler: function() {this.deleteMedProductCard();}.createDelegate(this), hidden: !(isSuperAdmin() || getGlobalOptions().groups.indexOf('LpuAdmin') != -1 || getGlobalOptions().groups.indexOf('MPCModer') != -1 )},
								{name: 'action_refresh'},
								{name: 'action_print'}
							],
							object: 'MedProductCard',
							scheme: 'passport',
							editformclassname: 'swMedProductCardEditWindow',
							autoExpandColumn: 'autoexpand',
							//autoExpandMin: 150,
							autoLoadData: false,
							border: false,
							dataUrl: '/?c=LpuPassport&m=loadMedProductCard',
							focusOn: {
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							focusPrev: {
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							id: 'LPEW_MedProductCardGrid',
							paging: false,
							title: 'Медицинские изделия',
							region: 'center',
							stringfields: [
								{name: 'MedProductCard_id', type: 'int', header: 'ID', key: true},
								//{name: 'LpuEquipmentPacs_id', type: 'int', header: 'pacs', hidden: true},
								{name: 'AccountingData_InventNumber', type: 'string', header: 'Инвентарный номер', width: 150},
								{name: 'MedProductClass_Name', type: 'string', header: 'Наименование МИ', width: 150},
								{name: 'MedProductClass_Model', type: 'string', header: 'Модель МИ', width: 150},
								{name: 'MedProductCard_SerialNumber', type: 'string', header: 'Серийный номер', width: 150},
								{name: 'CardType_Name', type: 'string', header: 'Тип медицинского изделия', width: 150},
								{name: 'ClassRiskType_Name', type: 'string', header: 'Класс риска применения', width: 150},
								{name: 'FuncPurpType_Name', type: 'string', header: 'Функциональное назначение', width: 150},
								{name: 'UseAreaType_Name', type: 'string', header: 'Область применения', width: 150},
								{name: 'UseSphereType_Name', type: 'string', header: 'Сфера применения', width: 150},
								{name: 'LpuBuilding_Name', type: 'string', header: 'Код подразделения', width: 150},
								{name: 'Org_Nick', type: 'string', header: 'Производитель', width: 150},
								{name: 'MedProductCard_begDate', type: 'string', header: 'Дата выпуска', width: 150},
								{name: 'FinancingType_Name', type: 'string', header: 'Программа закупки', width: 150},
								{name: 'AccountingData_setDate', type: 'date', header: 'Дата ввода в эксплуатацию', width: 150},
								{name: 'AccountingData_BuyCost', type: 'string', header: 'Стоимость приобретения', width: 150},
								{name: 'GosContract_Number', type: 'string', header: 'Номер гос. контракта', width: 150},
								{name: 'GosContract_setDate', type: 'string', header: 'Дата заключения контракта', width: 150}
							],
							toolbar: true
							//totalProperty: 'totalCount'
						})
					],
					listeners:
					{
						activate: function() {
							Ext.getCmp('LPEW_MedProductCardGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
						}.createDelegate(this)
					}
				},{
					title: '8. PACS',
					layout: 'fit',
					id: 'tab_pacs_equipment',
					iconCls: 'info16',
					border:false,
					items: [
						new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {this.openEquipmentEditWindow('add', this.Lpu_id);}.createDelegate(this)},
								{name: 'action_edit', handler: function() {this.openEquipmentEditWindow('edit', this.Lpu_id);}.createDelegate(this)},
								{name: 'action_view', handler: function() {this.openEquipmentEditWindow('view', this.Lpu_id);}.createDelegate(this)},
								{name: 'action_delete', handler: function() {this.deleteEquipment();}.createDelegate(this)},
								{name: 'action_refresh'},
								{name: 'action_print'}
							],
							object: 'LpuEquipment',
							editformclassname: 'swLpuEquipmentEditWindow',
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 150,
							autoLoadData: false,
							border: false,
							dataUrl: '/?c=LpuPassport&m=loadLpuEquipment',
							focusOn: {
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							focusPrev: {
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							id: 'LPEW_EquipmentGrid',
							//pageSize: 100,
							paging: false,
							title: 'Оборудование PACS',
							region: 'center',
							//root: 'data',
							stringfields: [
								{name: 'id', type: 'int', header: 'ID', key: true},
								{name: 'LpuEquipment_id', type: 'int', header: 'eq', hidden: true},
								{name: 'LpuEquipmentPacs_id', type: 'int', header: 'pacs', hidden: true},
								{name: 'LpuEquipment_Name', type: 'string', header: 'Наименование', width: 270},
								{name: 'LpuEquipment_Model', type: 'string', header: 'Модель', width: 270},
								{name: 'LpuEquipment_InvNum', type: 'string', header: 'Инвентарный номер', width: 270}
							],
							toolbar: true,
							totalProperty: 'totalCount'
						})
					],
					listeners:
					{
						activate: function() {
							Ext.getCmp('LPEW_EquipmentGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
						}.createDelegate(this)
					}
				},{
					title: '9. Обслуживаемое население',
					layout: 'fit',
					id: 'tab_population',
					iconCls: 'info16',
					border:false,
					items: [{
						autoScroll: true,
						bodyBorder: false,
						bodyStyle: 'padding: 5px 5px 0',
						border: false,
						frame: false,
						labelAlign: 'right',
						labelWidth: 250,
						items: 
						[
							new sw.Promed.Panel({
								autoHeight: true,
								style: 'margin-bottom: 0.5em;',
								border: true,
								collapsible: true,
								layout: 'form',
								title: '1. Территории обслуживания',
								listeners: {
									expand: function () {
										this.findById('LPEW_OrgServiceTerrGrid').loadData({globalFilters:{Org_id: this.Org_id}, params:{Org_id: this.Org_id}});
									}.createDelegate(this)
								},
								bodyStyle:'background:#DFE8F6;',
								items: [
									new Ext.form.FormPanel({
										border: false,
										bodyStyle:'background:#DFE8F6; padding: 5px;',
										id: 'Lpu_PopulationPanel',
										labelWidth: 300,
										items: 
										[{
											fieldLabel: 'Расстояние до наиболее удаленной точки территориального обслуживания, км',
											xtype: 'numberfield',
											allowDecimals: true,
											decimalPrecision: 5,
											maxLength:24,
											width: 300,
											name: 'PasportMO_MaxDistansePoint',
											id: 'LPEW_PasportMO_MaxDistansePoint',
											tabIndex: TABINDEX_LPEEW + 3
										}]
									}),
									// грид обслуживаемое население
									new sw.Promed.ViewFrame({
										actions: [
											{name: 'action_add'},
											{name: 'action_edit'},
											{name: 'action_view'},
											{name: 'action_delete'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										object: 'OrgServiceTerr',
										editformclassname: 'swOrgServiceTerrEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										border: false,
										dataUrl: '/?c=OrgServiceTerr&m=loadOrgServiceTerrGrid',
										focusOn: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										focusPrev: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										id: 'LPEW_OrgServiceTerrGrid',
										paging: false,
										region: 'center',
										stringfields: [
											{name: 'OrgServiceTerr_id', type: 'int', header: 'ID', key: true},
											{name: 'KLCountry_Name', type: 'string', header: 'Страна', width: 120},
											{name: 'KLRgn_Name', type: 'string', header: 'Регион', width: 120},
											{name: 'KLSubRgn_Name', type: 'string', header: 'Район', width: 120},
											{name: 'KLCity_Name', type: 'string', header: 'Город', width: 120},
											{name: 'KLTown_Name', type: 'string', header: 'Населенный пункт', width: 120},
											{name: 'KLAreaType_Name', type: 'string', header: 'Тип населенного пункта', width: 120}
										],
										totalProperty: 'totalCount'
									})
								]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								style: 'margin-bottom: 0.5em;',
								border: true,
								collapsible: true,
								layout: 'form',
								title: '2. Расчетные квоты',
								listeners: {
									expand: function () {
										this.findById('LPEW_LpuQuoteGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									}.createDelegate(this)
								},
								items: [
									new sw.Promed.ViewFrame({
										actions: [
											{name: 'action_add'},
											{name: 'action_edit'},
											{name: 'action_view'},
											{name: 'action_delete'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										object: 'LpuQuote',
										editformclassname: 'swLpuQuoteEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										border: false,
										dataUrl: '/?c=LpuPassport&m=loadLpuQuote',
										focusOn: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										focusPrev: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										id: 'LPEW_LpuQuoteGrid',
										//pageSize: 100,
										paging: false,
										region: 'center',
										//root: 'data',
										stringfields: [
											{name: 'LpuQuote_id', type: 'int', header: 'ID', key: true},
											{name: 'PayType_Name', type: 'string', header: 'Вид оплаты', width: 120},
											{name: 'LpuQuote_HospCount', type: 'string', header: 'Кол-во госпитализаций', width: 120},
											{name: 'LpuQuote_BedDaysCount', type: 'string', header: 'Кол-во койко-дней', width: 120},
											{name: 'LpuQuote_VizitCount', type: 'string', header: 'Кол-во посещений', width: 120},
											{name: 'LpuQuote_begDate', type: 'string', header: 'Начало', width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
											{name: 'LpuQuote_endDate', type: 'string', header: 'Окончание', width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')}
										],
										totalProperty: 'totalCount'
									})
								]
							})
						]
					}],
					listeners: 
					{
						activate: function() {
							if (!Ext.isEmpty(this.Org_id)) {
								Ext.getCmp('LPEW_OrgServiceTerrGrid').loadData({globalFilters:{Org_id: this.Org_id}, params:{Org_id: this.Org_id}});
							}
							if (!Ext.isEmpty(this.Lpu_id)) {
								Ext.getCmp('LPEW_LpuQuoteGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				},
				{
					title: '10. Виды помощи',
					layout: 'fit',
					id: 'tab_med_care',
					iconCls: 'info16',
					bodyStyle:'background:#DFE8F6;',
					border:false,
					items: [
						new Ext.form.HtmlEditor({
							hideLabel: true,
							name: 'Lpu_MedCare',
							id: 'LPEW_Lpu_MedCare',
							defaultValue: ''
						})
					]
				},{
						title: '11. Санаторно-курортное лечение',
						layout: 'fit',
						id: 'tab_sanatoriumtreatment',
						iconCls: 'info16',
						border:false,
						items: [
							new Ext.form.FormPanel({
								autoScroll: true,
								bodyBorder: false,
								bodyStyle: 'padding: 5px 5px 0',
								border: false,
								frame: false,
								id: 'STForm',
								labelAlign: 'right',
								labelWidth: 180,
								items: [
									new sw.Promed.Panel({
										autoHeight: true,
										style: 'margin-bottom: 0.5em;',
										border: true,
										collapsible: true,
										id: 'LPEW_KurortStatus',
										layout: 'form',
										title: '1. Статус курорта',
										listeners: {
											expand: function () {
												this.findById('LPEW_KurortStatusGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
											}.createDelegate(this)
										},
										items: [
											new sw.Promed.ViewFrame({
												actions: [
													{name: 'action_add'},
													{name: 'action_edit'},
													{name: 'action_view'},
													{name: 'action_delete'},
													{name: 'action_refresh'},
													{name: 'action_print'}
												],
												object: 'KurortStatusDoc',
												scheme: 'fed',
												editformclassname: 'swKurortStatusEditWindow',
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 150,
												autoLoadData: false,
												border: false,
												dataUrl: '/?c=LpuPassport&m=loadKurortStatus',
												focusOn: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												focusPrev: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												id: 'LPEW_KurortStatusGrid',
												//pageSize: 100,
												paging: false,
												region: 'center',
												//root: 'data',
												stringfields: [
													{name: 'KurortStatusDoc_id', type: 'int', hidden: true},
													{name: 'KurortStatusDoc_id', type: 'int', header: 'ID', key: true},
													{name: 'KurortStatusDoc_IsStatus', type: 'checkcolumn', header: 'Наличие статуса курорта', width: 160},
													{name: 'KurortStatus_Name', type: 'string', header: 'Статус курорта', width: 270},
													{name: 'KurortStatusDoc_Doc', type: 'string', header: 'Документ', width: 270},
													{name: 'KurortStatusDoc_Num', type: 'string', header: 'Номер документа', width: 270},
													{name: 'KurortStatusDoc_Date', type: 'date', header: 'Дата документа'}
												],
												toolbar: true
												//totalProperty: 'totalCount'
											})
										]
									}),
									new sw.Promed.Panel({
										autoHeight: true,
										style: 'margin-bottom: 0.5em;',
										border: true,
										collapsible: true,
										//id: 'Lpu_Transport',//Округ горно-санитарной охраны id
										layout: 'form',
										id: 'LPEW_DisSanProtection',
										title: '2. Округ горно-санитарной охраны',
										listeners: {
											expand: function () {
												this.findById('LPEW_DisSanProtectionGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
											}.createDelegate(this)
										},
										items: [
											new sw.Promed.ViewFrame({
												actions: [
													{name: 'action_add'},
													{name: 'action_edit'},
													{name: 'action_view'},
													{name: 'action_delete'},
													{name: 'action_refresh'},
													{name: 'action_print'}
												],
												object: 'DisSanProtection',
												editformclassname: 'swDisSanProtectionEditWindow',
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 150,
												autoLoadData: false,
												border: false,
												scheme: 'fed',
												dataUrl: '/?c=LpuPassport&m=loadDisSanProtection',
												focusOn: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												focusPrev: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												id: 'LPEW_DisSanProtectionGrid',
												//pageSize: 100,
												paging: false,
												region: 'center',
												//root: 'data',
												stringfields: [
													{name: 'DisSanProtection_id', type: 'int', header: 'ID', key: true},
													{name: 'DisSanProtection_IsProtection', type: 'checkcolumn', header: 'Признак наличия округа', width: 150},
													{name: 'DisSanProtection_Doc', type: 'string', header: 'Документ', width: 270},
													{name: 'DisSanProtection_Num', type: 'string', header: 'Номер документа', width: 270},
													{name: 'DisSanProtection_Date', type: 'date', header: 'Дата документа', width: 270}
												],
												toolbar: true
												//totalProperty: 'totalCount'
											})
										]
									}),
									new sw.Promed.Panel({
										autoHeight: true,
										style: 'margin-bottom: 0.5em;',
										border: true,
										collapsible: true,
										id: 'LPEW_KurortTypeLink',
										layout: 'form',
										title: '3. Тип курорта',
										listeners: {
											expand: function () {
												this.findById('LPEW_KurortTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
											}.createDelegate(this)
										},
										items: [
											new sw.Promed.ViewFrame({
												actions: [
													{name: 'action_add'},
													{name: 'action_edit'},
													{name: 'action_view'},
													{name: 'action_delete'},
													{name: 'action_refresh'},
													{name: 'action_print'}
												],
												object: 'KurortTypeLink',
												editformclassname: 'swKurortTypeLinkEditWindow',
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 150,
												autoLoadData: false,
												border: false,
												scheme: 'fed',
												dataUrl: '/?c=LpuPassport&m=loadKurortTypeLink',
												focusOn: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												focusPrev: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												id: 'LPEW_KurortTypeLinkGrid',
												//pageSize: 100,
												paging: false,
												region: 'center',
												//root: 'data',
												stringfields: [
													{name: 'KurortTypeLink_id', type: 'int', header: 'ID', key: true},
													{name: 'KurortTypeLink_IsKurortTypeLink', type: 'checkcolumn', header: 'Наличие типа курорта', width: 150},
													{name: 'KurortType_Name', type: 'string', header: 'Тип курорта', width: 270, id: 'autoexpand'},
													{name: 'KurortTypeLink_Doc', type: 'string', header: 'Документ', width: 270},
													{name: 'KurortTypeLink_Num', type: 'string', header: 'Номер документа', width: 270},
													{name: 'KurortTypeLink_Date', type: 'date', header: 'Дата документа', width: 270}
												],
												toolbar: true
												//totalProperty: 'totalCount'
											})
										]
									}),
									new sw.Promed.Panel({
										autoHeight: true,
										style: 'margin-bottom: 0.5em;',
										border: true,
										collapsible: true,
										id: 'LPEW_MOArrival',
										layout: 'form',
										title: '4. Заезды',
										listeners: {
											expand: function () {
												this.findById('LPEW_MOArrivalGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
											}.createDelegate(this)
										},
										items: [
											new sw.Promed.ViewFrame({
												actions: [
													{name: 'action_add'},
													{name: 'action_edit'},
													{name: 'action_view'},
													{name: 'action_delete'},
													{name: 'action_refresh'},
													{name: 'action_print'}
												],
												object: 'MOArrival',
												editformclassname: 'swMOArrivalEditWindow',
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 150,
												autoLoadData: false,
												border: false,
												scheme: 'fed',
												dataUrl: '/?c=LpuPassport&m=loadMOArrival',
												focusOn: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												focusPrev: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												id: 'LPEW_MOArrivalGrid',
												//pageSize: 100,
												paging: false,
												region: 'center',
												//root: 'data',
												stringfields: [
													{name: 'MOArrival_id', type: 'int', header: 'ID', hidden: true},
													{name: 'MOArrival_CountPerson', type: 'int', header: 'Количество человек в заезде', width: 270},
													{name: 'MOArrival_TreatDis', type: 'int', header: 'Длительность лечения', width: 270},
													{name: 'MOArrival_EndDT', type: 'date', header: 'Дата окончания заезда', width: 270}

												],
												toolbar: true,
												totalProperty: 'totalCount'
											})
										]
									})
								]
							})
						],
						listeners:
						{
							activate: function() {
								if (!Ext.isEmpty(this.Lpu_id)) {
									if (!Ext.getCmp('LPEW_KurortStatus').collapsed)
										Ext.getCmp('LPEW_KurortStatusGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									if (!Ext.getCmp('LPEW_DisSanProtection').collapsed)
										Ext.getCmp('LPEW_DisSanProtectionGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									if (!Ext.getCmp('LPEW_KurortTypeLink').collapsed)
										Ext.getCmp('LPEW_KurortTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									if (!Ext.getCmp('LPEW_MOArrival').collapsed)
										Ext.getCmp('LPEW_MOArrivalGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}
							}.createDelegate(this)
						}
					}]
			})]
		});
		sw.Promed.swLpuPassportEditWindow.superclass.initComponent.apply(this, arguments);
		//this.findById('LPEW_OrgRSchetGrid').addListenersFocusOnFields();
		//this.findById('LPEW_OrgHeadGrid').addListenersFocusOnFields();
	},
	keys: [{
		alt: true, 
		fn: function(inp, e) {
			Ext.getCmp('LpuPassportEditWindow').hide();
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	enableEdit: function(enable) 
	{
		var form = this;

		if (isSuperAdmin()) {
			form.findById('LPEW_Lpu_IsSecret').setDisabled(!enable);
		} else {
			form.findById('LPEW_Lpu_IsSecret').setDisabled(true);
		}
		// Делаем загрушку для Специалиста МЗ 
		if (isUserGroup('OuzSpec') || isUserGroup('OuzSpecMPC')) {
			enable = true;
		}
		
		if ( enable ) {
			if ( (isSuperAdmin()) || isUserGroup('OuzSpecMPC')) {
				form.findById('LPEW_Lpu_Name').enable();
				form.findById('LPEW_Lpu_Nick').enable();
				form.findById('LPEW_Lpu_f003mcod').enable();
				form.findById('LPEW_Lpu_RegNomN2').enable();
				
				form.findById('LPEW_Lpu_begDate').enable();
				form.findById('LPEW_Lpu_endDate').enable();
				
				form.findById('LPEW_Lpu_pid').enable();
				form.findById('LPEW_Lpu_nid').enable();
				
				form.findById('LPEW_Lpu_Okato').enable();
				form.findById('LPEW_Okfs_id').enable();
				form.findById('LPEW_Okopf_id').enable();
				form.findById('LPEW_Org_INN').enable();
				form.findById('LPEW_Org_OGRN').enable();
				
				form.findById('LPEW_LpuAgeType_id').enable();

				form.findById('LPEW_PAddress_AddressText').enable();
				form.findById('LPEW_UAddress_AddressText').enable();
				
				form.findById('LPEW_LpuSubjectionLevel_id').enable();
				form.findById('LPEW_LpuLevel_id').enable();
				form.findById('LPEW_LevelType_id').enable();
				
				form.findById('LPEW_DLOGrid').setReadOnly(false);
				form.findById('LPEW_DMSGrid').setReadOnly(false);
				form.findById('LPEW_FondHolderGrid').setReadOnly(false);
			} 
			else {
				form.findById('LPEW_DLOGrid').setReadOnly(true);
				form.findById('LPEW_DMSGrid').setReadOnly(true);
				form.findById('LPEW_FondHolderGrid').setReadOnly(true);
			}
			
			form.findById('LPEW_Lpu_StickNick').enable();

			form.findById('LPEW_Lpu_Email').enable();
			form.findById('LPEW_Lpu_Www').enable();
			form.findById('LPEW_Lpu_Phone').enable();
			form.findById('LPEW_Lpu_Worktime').enable();
			form.findById('LPEW_Lpu_StickAddress').enable();
			form.findById('LPEW_Lpu_DistrictRate').enable();
			
			form.findById('LPEW_Okogu_id').enable();
			form.findById('LPEW_Okved_id').enable();
			form.findById('LPEW_LpuSpecType_id').enable();
			
			form.findById('LPEW_Org_lid').enable();
			form.findById('LPEW_Lpu_RegDate').enable();
			form.findById('LPEW_Lpu_RegNum').enable();
			form.findById('LPEW_Lpu_DocReg').enable();
			form.findById('LPEW_Lpu_PensRegNum').enable();
	
			form.findById('LPEW_Lpu_VizitFact').enable();
			form.findById('LPEW_Lpu_KoikiFact').enable();
			form.findById('LPEW_Lpu_AmbulanceCount').enable();
			form.findById('LPEW_Lpu_FondOsn').enable();
			form.findById('LPEW_Lpu_FondEquip').enable();

			form.findById('LPEW_Lpu_OftenCallers_FreeDays').enable();
			form.findById('LPEW_PasportMO_IsAssignNasel').enable();
			form.findById('LPEW_Lpu_OftenCallers_SearchDays').enable();
			form.findById('LPEW_Lpu_OftenCallers_CallTimes').enable();
			
			form.findById('LPEW_Lpu_ErInfo').enable();
			if(isSuperAdmin())
				form.findById('LPEW_IsAllowInternetModeration').enable();
			else
				form.findById('LPEW_IsAllowInternetModeration').disable();

			form.findById('LPEW_Lpu_MedCare').enable();
						
			form.findById('LPEW_LpuLicenceGrid').setReadOnly(false);
			form.findById('LPEW_OrgRSchetGrid').setReadOnly(false);
			
			form.findById('LPEW_OrgHeadGrid').setReadOnly(false);
			form.findById('LPEW_DogDDGrid').setReadOnly(false);
			form.findById('LPEW_LpuBuilding').setReadOnly(false);
			
			form.findById('LPEW_MedProductCardGrid').setReadOnly(false);
			form.findById('LPEW_LpuQuoteGrid').setReadOnly(false);
			
			form.findById('LPEW_MOInfoSysGrid').setReadOnly(false);
			form.findById('LPEW_SpecializationMOGrid').setReadOnly(false);
			form.findById('LPEW_MedUslugaGrid').setReadOnly(false);
			form.findById('LPEW_MedTechnologyGrid').setReadOnly(false);
			form.findById('LPEW_PitanFormTypeLinkGrid').setReadOnly(false);
			form.findById('LPEW_PlfDocTypeLinkGrid').setReadOnly(false);
			form.findById('LPEW_PlfObjectCountGrid').setReadOnly(false);
			form.findById('LPEW_LpuMobileTeamGrid').setReadOnly(false);
			form.findById('LPEW_MOAreaGrid').setReadOnly(false);
			form.findById('LPEW_MOAreaObjectGrid').setReadOnly(false);
			form.findById('LPEW_EquipmentGrid').setReadOnly(false);
			form.findById('LPEW_OrgServiceTerrGrid').setReadOnly(false);
			form.findById('LPEW_KurortStatusGrid').setReadOnly(false);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(false);
			form.findById('LPEW_MOArrivalGrid').setReadOnly(false);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(false);
			
			this.buttons[0].enable();
		}
		else {
			form.findById('LPEW_Lpu_Name').disable();
			form.findById('LPEW_Lpu_Nick').disable();
			form.findById('LPEW_Lpu_f003mcod').disable();
			form.findById('LPEW_Lpu_RegNomN2').disable();
			
			form.findById('LPEW_Lpu_StickNick').disable();
			
			form.findById('LPEW_Lpu_begDate').disable();
			form.findById('LPEW_Lpu_endDate').disable();
			form.findById('LPEW_Lpu_pid').disable();
			form.findById('LPEW_Lpu_nid').disable();
			
			form.findById('LPEW_Lpu_Email').disable();
			form.findById('LPEW_Lpu_Www').disable();
			form.findById('LPEW_Lpu_Phone').disable();
			form.findById('LPEW_Lpu_Worktime').disable();
			form.findById('LPEW_Lpu_StickAddress').disable();
			form.findById('LPEW_Lpu_Okato').disable();
			form.findById('LPEW_LpuAgeType_id').disable();
			form.findById('LPEW_Lpu_DistrictRate').disable();
			
			form.findById('LPEW_PAddress_AddressText').disable();
			form.findById('LPEW_UAddress_AddressText').disable();
			
			form.findById('LPEW_Okfs_id').disable();
			form.findById('LPEW_Okopf_id').disable();
			form.findById('LPEW_Org_INN').disable();
			form.findById('LPEW_Org_OGRN').disable();
			form.findById('LPEW_Okogu_id').disable();
			form.findById('LPEW_Okved_id').disable();
			form.findById('LPEW_LpuSpecType_id').disable();
			
			form.findById('LPEW_Org_lid').disable();
			form.findById('LPEW_Lpu_RegDate').disable();
			form.findById('LPEW_Lpu_RegNum').disable();
			form.findById('LPEW_Lpu_DocReg').disable();
			form.findById('LPEW_Lpu_PensRegNum').disable();
			
			form.findById('LPEW_LpuSubjectionLevel_id').disable();
			form.findById('LPEW_LpuLevel_id').disable();
			form.findById('LPEW_LevelType_id').disable();
			form.findById('LPEW_Lpu_VizitFact').disable();
			form.findById('LPEW_Lpu_KoikiFact').disable();
			form.findById('LPEW_Lpu_AmbulanceCount').disable();
			form.findById('LPEW_Lpu_FondOsn').disable();
			form.findById('LPEW_Lpu_FondEquip').disable();
			
			form.findById('LPEW_Lpu_OftenCallers_FreeDays').disable();
			form.findById('LPEW_PasportMO_IsAssignNasel').disable();
			form.findById('LPEW_Lpu_OftenCallers_SearchDays').disable();
			form.findById('LPEW_Lpu_OftenCallers_CallTimes').disable();
			
			form.findById('LPEW_Lpu_ErInfo').disable();
			form.findById('LPEW_IsAllowInternetModeration').disable();
			form.findById('LPEW_Lpu_MedCare').disable();
			
			form.findById('LPEW_DLOGrid').setReadOnly(true);
			form.findById('LPEW_DMSGrid').setReadOnly(true);
			form.findById('LPEW_FondHolderGrid').setReadOnly(true);
			
			form.findById('LPEW_LpuLicenceGrid').setReadOnly(true);
			form.findById('LPEW_OrgRSchetGrid').setReadOnly(true);
			
			form.findById('LPEW_OrgHeadGrid').setReadOnly(true);
			form.findById('LPEW_DogDDGrid').setReadOnly(true);
			form.findById('LPEW_LpuBuilding').setReadOnly(true);
			form.findById('LPEW_MedProductCardGrid').setReadOnly(true);
			form.findById('LPEW_LpuQuoteGrid').setReadOnly(true);
			
			form.findById('LPEW_MOInfoSysGrid').setReadOnly(true);
			form.findById('LPEW_SpecializationMOGrid').setReadOnly(true);
			form.findById('LPEW_MedUslugaGrid').setReadOnly(true);
			form.findById('LPEW_MedTechnologyGrid').setReadOnly(true);
			form.findById('LPEW_PitanFormTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_PlfDocTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_PlfObjectCountGrid').setReadOnly(true);
			form.findById('LPEW_LpuMobileTeamGrid').setReadOnly(true);
			form.findById('LPEW_MOAreaGrid').setReadOnly(true);
			form.findById('LPEW_MOAreaObjectGrid').setReadOnly(true);
			form.findById('LPEW_EquipmentGrid').setReadOnly(true);
			form.findById('LPEW_OrgServiceTerrGrid').setReadOnly(true);
			form.findById('LPEW_KurortStatusGrid').setReadOnly(true);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(true);
			form.findById('LPEW_MOArrivalGrid').setReadOnly(true);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(true);
			
			this.buttons[0].disable();
		}
		
		if ( isUserGroup('OuzSpec') || isUserGroup('OuzSpecMPC') ) {
			form.findById('LPEW_DLOGrid').setReadOnly(true);
			form.findById('LPEW_DMSGrid').setReadOnly(true);
			form.findById('LPEW_FondHolderGrid').setReadOnly(true);
			
			form.findById('LPEW_LpuLicenceGrid').setReadOnly(true);
			form.findById('LPEW_OrgRSchetGrid').setReadOnly(true);
			
			form.findById('LPEW_OrgHeadGrid').setReadOnly(true);
			form.findById('LPEW_DogDDGrid').setReadOnly(true);
			form.findById('LPEW_LpuBuilding').setReadOnly(true);
			form.findById('LPEW_LpuQuoteGrid').setReadOnly(true);
			
			form.findById('LPEW_MedProductCardGrid').setReadOnly(true);
			form.findById('LPEW_MOInfoSysGrid').setReadOnly(true);
			form.findById('LPEW_SpecializationMOGrid').setReadOnly(true);
			form.findById('LPEW_MedUslugaGrid').setReadOnly(true);
			form.findById('LPEW_MedTechnologyGrid').setReadOnly(true);
			form.findById('LPEW_PitanFormTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_PlfDocTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_PlfObjectCountGrid').setReadOnly(true);
			form.findById('LPEW_LpuMobileTeamGrid').setReadOnly(true);
			form.findById('LPEW_MOAreaGrid').setReadOnly(true);
			form.findById('LPEW_MOAreaObjectGrid').setReadOnly(true);
			form.findById('LPEW_EquipmentGrid').setReadOnly(true);
			form.findById('LPEW_OrgServiceTerrGrid').setReadOnly(true);
			form.findById('LPEW_KurortStatusGrid').setReadOnly(true);
			form.findById('LPEW_KurortTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_MOArrivalGrid').setReadOnly(true);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(true);

			form.findById('LPEW_UslugaComplexLpuGrid').setReadOnly(true);
			form.findById('LPEW_FunctionTimeGrid').setReadOnly(true);
			form.findById('LPEW_TransportConnectGrid').setReadOnly(true);

			this.buttons[0].disable();
		}
	},
	resetForm:  function () {
		Ext.getCmp('LPEW_panelForm').getForm().reset();
		Ext.getCmp('Lpu_IdentificationPanel').getForm().reset();
		Ext.getCmp('Lpu_SupInfoPanel').getForm().reset();
		
		var form  = Ext.getCmp('LpuPassportEditWindow');
		
		form.findById('LPEW_DLOGrid').removeAll({clearAll: true});
		form.findById('LPEW_DMSGrid').removeAll({clearAll: true});
		form.findById('LPEW_FondHolderGrid').removeAll({clearAll: true});
		
		form.findById('LPEW_LpuLicenceGrid').removeAll({clearAll: true});
		form.findById('LPEW_OrgRSchetGrid').removeAll({clearAll: true});
		
		form.findById('LPEW_OrgHeadGrid').removeAll({clearAll: true});
		form.findById('LPEW_DogDDGrid').removeAll({clearAll: true});
		form.findById('LPEW_LpuBuilding').removeAll({clearAll: true});
		
		form.findById('LPEW_MedProductCardGrid').removeAll({clearAll: true});
		form.findById('LPEW_LpuQuoteGrid').removeAll({clearAll: true});
	},
	disableGrids: function () {
			var form  = Ext.getCmp('LpuPassportEditWindow');

			form.findById('LPEW_DLOGrid').setReadOnly(true);
			form.findById('LPEW_DMSGrid').setReadOnly(true);
			form.findById('LPEW_FondHolderGrid').setReadOnly(true);
			
			form.findById('LPEW_LpuLicenceGrid').setReadOnly(true);
			form.findById('LPEW_OrgRSchetGrid').setReadOnly(true);
			
			form.findById('LPEW_OrgHeadGrid').setReadOnly(true);
			form.findById('LPEW_DogDDGrid').setReadOnly(true);
			form.findById('LPEW_LpuBuilding').setReadOnly(true);
			
			form.findById('LPEW_MedProductCardGrid').setReadOnly(true);
			form.findById('LPEW_LpuQuoteGrid').setReadOnly(true);
	},
	getLpuInfo: function (Lpu_id)
	{
		var win = this;
		
		Ext.Ajax.request(
		{
			params: 
			{
				Lpu_id: Lpu_id
			},
			url: '/?c=LpuPassport&m=getLpuPassport',
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					var form  = Ext.getCmp('LpuPassportEditWindow');
					form.findById('LPEW_Lpu_IsSecret').setValue((result[0].Lpu_IsSecret == '2'));
					form.findById('LPEW_Lpu_id').setValue(result[0].Lpu_id);
					win.Org_id = result[0].Org_id;
					form.findById('LPEW_Org_id').setValue(result[0].Org_id);
					form.findById('LPEW_Server_id').setValue(result[0].Server_id);
					form.findById('LPEW_Lpu_Name').setValue(result[0].Lpu_Name);
					form.findById('LPEW_Lpu_Nick').setValue(result[0].Lpu_Nick);
					form.findById('LPEW_Lpu_f003mcod').setValue(result[0].Lpu_f003mcod);
					form.findById('LPEW_Lpu_RegNomN2').setValue(result[0].Lpu_RegNomN2);
					
					form.findById('LPEW_Lpu_StickNick').setValue(result[0].Lpu_StickNick);
					
					form.findById('LPEW_Lpu_begDate').setValue(result[0].Lpu_begDate);
					form.findById('LPEW_Lpu_endDate').setValue(result[0].Lpu_endDate);
					form.findById('LPEW_Lpu_pid').setValue(result[0].Lpu_pid);
					form.findById('LPEW_Lpu_nid').setValue(result[0].Lpu_nid);
					
					form.findById('LPEW_Lpu_Email').setValue(result[0].Lpu_Email);
					form.findById('LPEW_Lpu_Www').setValue(result[0].Lpu_Www);
					form.findById('LPEW_Lpu_Phone').setValue(result[0].Lpu_Phone);
					form.findById('LPEW_Lpu_Worktime').setValue(result[0].Lpu_Worktime);
					form.findById('LPEW_Lpu_StickAddress').setValue(result[0].Lpu_StickAddress);
					form.findById('LPEW_Lpu_Okato').setValue(result[0].Lpu_Okato);
					form.findById('LPEW_LpuType_id').setValue(result[0].LpuType_id);
					form.findById('LPEW_LpuAgeType_id').setValue(result[0].MesAgeLpuType_id);
					form.findById('LPEW_Lpu_DistrictRate').setValue(result[0].Lpu_DistrictRate);

					form.findById('LPEW_Okfs_id').setValue(result[0].Okfs_id);
					form.findById('LPEW_Okopf_id').setValue(result[0].Okopf_id);
					form.findById('LPEW_Org_INN').setValue(result[0].Org_INN);
					form.findById('LPEW_Org_OGRN').setValue(result[0].Org_OGRN);
					form.findById('LPEW_Okogu_id').setValue(result[0].Okogu_id);
					form.findById('LPEW_Okved_id').setValue(result[0].Okved_id);
					form.findById('LPEW_LpuSpecType_id').setValue(result[0].LpuSpecType_id);
					
					form.findById('LPEW_Org_lid').setValue(result[0].Org_lid);

					var combo = form.findById('LPEW_Org_lid');
					if (!Ext.isEmpty(combo.getValue())) {
						combo.getStore().load(
						{
							callback: function() 
							{
								combo.setValue(combo.getValue());
								combo.fireEvent('change', combo);
							},
							params: 
							{
								Org_id: combo.getValue(),
								OrgType: 'lic'
							}
						});
					}
					
					form.findById('LPEW_Lpu_RegDate').setValue(result[0].Lpu_RegDate);
					form.findById('LPEW_Lpu_RegNum').setValue(result[0].Lpu_RegNum);
					form.findById('LPEW_Lpu_DocReg').setValue(result[0].Lpu_DocReg);
					form.findById('LPEW_Lpu_PensRegNum').setValue(result[0].Lpu_PensRegNum);
					
					form.findById('LPEW_UAddress_id').setValue(result[0].UAddress_id);
					form.findById('LPEW_UAddress_Zip').setValue(result[0].UAddress_Zip);
					form.findById('LPEW_UKLCountry_id').setValue(result[0].UKLCountry_id);
					form.findById('LPEW_UKLRGN_id').setValue(result[0].UKLRGN_id);
					form.findById('LPEW_UKLSubRGN_id').setValue(result[0].UKLSubRGN_id);
					form.findById('LPEW_UKLCity_id').setValue(result[0].UKLCity_id);
					form.findById('LPEW_UKLTown_id').setValue(result[0].UKLTown_id);
					form.findById('LPEW_UKLStreet_id').setValue(result[0].UKLStreet_id);
					form.findById('LPEW_UAddress_House').setValue(result[0].UAddress_House);
					form.findById('LPEW_UAddress_Corpus').setValue(result[0].UAddress_Corpus);
					form.findById('LPEW_UAddress_Flat').setValue(result[0].UAddress_Flat);
					form.findById('LPEW_UAddress_Address').setValue(result[0].UAddress_Address);
					form.findById('LPEW_UAddress_AddressText').setValue(result[0].UAddress_AddressText);
					form.findById('LPEW_PAddress_id').setValue(result[0].PAddress_id);
					form.findById('LPEW_PAddress_Zip').setValue(result[0].PAddress_Zip);
					form.findById('LPEW_PKLCountry_id').setValue(result[0].PKLCountry_id);
					form.findById('LPEW_PKLRGN_id').setValue(result[0].PKLRGN_id);
					form.findById('LPEW_PKLSubRGN_id').setValue(result[0].PKLSubRGN_id);
					form.findById('LPEW_PKLCity_id').setValue(result[0].PKLCity_id);
					form.findById('LPEW_PKLTown_id').setValue(result[0].PKLTown_id);
					form.findById('LPEW_PKLStreet_id').setValue(result[0].PKLStreet_id);
					form.findById('LPEW_PAddress_House').setValue(result[0].PAddress_House);
					form.findById('LPEW_PAddress_Corpus').setValue(result[0].PAddress_Corpus);
					form.findById('LPEW_PAddress_Flat').setValue(result[0].PAddress_Flat);
					form.findById('LPEW_PAddress_Address').setValue(result[0].PAddress_Address);
					form.findById('LPEW_PAddress_AddressText').setValue(result[0].PAddress_AddressText);

					form.findById('LPEW_PasportMO_id').setValue(result[0].PasportMO_id);
					form.findById('LPEW_PasportMO_MaxDistansePoint').setValue(result[0].PasportMO_MaxDistansePoint);
					form.findById('LPEW_PasportMO_IsFenceTer').setValue(result[0].PasportMO_IsFenceTer);
					form.findById('LPEW_DLocationLpu_id').setValue(result[0].DLocationLpu_id);
					form.findById('LPEW_PasportMO_IsSecur').setValue(result[0].PasportMO_IsSecur);
					form.findById('LPEW_PasportMO_IsMetalDoors').setValue(result[0].PasportMO_IsMetalDoors);
					form.findById('LPEW_PasportMO_IsVideo').setValue(result[0].PasportMO_IsVideo);
					form.findById('LPEW_PasportMO_IsAccompanying').setValue(result[0].PasportMO_IsAccompanying);

					form.findById('LPEW_LpuSubjectionLevel_id').setValue(result[0].LpuSubjectionLevel_id);
					form.findById('LPEW_LpuLevel_id').setValue(result[0].LpuLevel_id);
					form.findById('LPEW_LpuLevelType_id').setValue(result[0].LpuLevelType_id);
					form.findById('LPEW_LevelType_id').setValue(result[0].LevelType_id);
					form.findById('LPEW_Lpu_VizitFact').setValue(result[0].Lpu_VizitFact);
					form.findById('LPEW_Lpu_KoikiFact').setValue(result[0].Lpu_KoikiFact);
					form.findById('LPEW_Lpu_AmbulanceCount').setValue(result[0].Lpu_AmbulanceCount);
					form.findById('LPEW_InstitutionLevel_id').setValue(result[0].InstitutionLevel_id);
					form.findById('LPEW_Lpu_FondOsn').setValue(result[0].Lpu_FondOsn);
					form.findById('LPEW_Lpu_FondEquip').setValue(result[0].Lpu_FondEquip);
					form.findById('LPEW_Lpu_gid').setValue(result[0].Lpu_gid);

					if (result[0].isCMP == 1) {
						form.findById('LPEW_Lpu_OftenCallers_Panel').show();
					} else {
						form.findById('LPEW_Lpu_OftenCallers_Panel').hide();
					}
					
					form.findById('LPEW_Lpu_isCMP').setValue(result[0].isCMP);
					
					form.findById('LPEW_Lpu_OftenCallers_CallTimes').setValue(result[0].OftenCallers_CallTimes);
					form.findById('LPEW_Lpu_OftenCallers_SearchDays').setValue(result[0].OftenCallers_SearchDays);
					form.findById('LPEW_Lpu_OftenCallers_FreeDays').setValue(result[0].OftenCallers_FreeDays);

					form.findById('LPEW_Lpu_ErInfo').setValue(result[0].Lpu_ErInfo);

					if (result[0].Lpu_IsAllowInternetModeration == '2')
						form.findById('LPEW_IsAllowInternetModeration').setValue(1);
					else
						form.findById('LPEW_IsAllowInternetModeration').setValue(0);

					form.findById('LPEW_Lpu_MedCare').setValue(result[0].Lpu_MedCare);

					if (!Ext.getCmp('LPEW_Lpu_DLOPanel').collapsed) {
						Ext.getCmp('LPEW_DLOGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_DLOGrid').loadData({globalFilters:{Lpu_id: result[0].Lpu_id}, params:{Lpu_id: result[0].Lpu_id}});
					}
					if (!Ext.getCmp('LPEW_Lpu_DMSPanel').collapsed) {
						Ext.getCmp('LPEW_DMSGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_DMSGrid').loadData({globalFilters:{Lpu_id: result[0].Lpu_id}, params:{Lpu_id: result[0].Lpu_id}});
					}
					if (!Ext.getCmp('LPEW_Lpu_FondHolderPanel').collapsed) {
						Ext.getCmp('LPEW_FondHolderGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_FondHolderGrid').loadData({globalFilters:{Lpu_id: result[0].Lpu_id}, params:{Lpu_id: result[0].Lpu_id}});
					}	
				}
			}
		});
	},
	openOrgHeadEditWindow: function(action)
	{
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swOrgHeadEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования руководства уже открыто');
			return false;
		}

		var grid = this.findById('LPEW_OrgHeadGrid').getGrid();

		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data ) {
				return false;
			}
			var grid = current_window.findById('LPEW_OrgHeadGrid').getGrid();
			var record = grid.getStore().getById(data.OrgHead_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgHead_id') > 0) ) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData([ data ], true);
			}
			else {
				var head = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					head.push(key);
				});

				for ( i = 0; i < head.length; i++ ) {
					record.set(head[i], data[head[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		var Lpu_id = this.Lpu_id;
		if ( action == 'add' ) {
			params.OrgHead_id = 0;
			params.onHide = function() {
				current_window.findById('LPEW_OrgHeadGrid').focus(true, 100);
			};
			// ищем человека и передаем его
			if (getWnd('swPersonSearchWindow').isVisible())
			{
				current_window.showMessage('Сообщение', 'Окно поиска человека уже открыто');
				return false;
			}
			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					//current_window.refreshPersonDispSearchGrid();
				},
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();
					params.Person_id = person_data.Person_id;
					params.Lpu_id = Lpu_id;
					getWnd('swOrgHeadEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
		else
		{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.OrgHead_id = grid.getSelectionModel().getSelected().get('OrgHead_id');
			params.Person_id = grid.getSelectionModel().getSelected().get('Person_id');
			params.Lpu_id = this.Lpu_id;
			params.onHide = function() {
				current_window.findById('LPEW_OrgHeadGrid').focus(true, 100);
			};
			getWnd('swOrgHeadEditWindow').show(params);
		}
	},
	
	deleteEquipment: function(){
		var grid = this.findById('LPEW_MedProductCardGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		if ( !record || (!record.get('LpuEquipment_id') && !record.get('LpuEquipmentPacs_id')) )
			return;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								grid.getStore().remove(record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else {
								sw.swMsg.alert('Ошибка', 'При удалении  возникли ошибки');
							}
						},
						params: {
							LpuEquipment_id: record.get('LpuEquipment_id'),
							LpuEquipmentPacs_id: record.get('LpuEquipmentPacs_id')
						},
						url: '/?c=LpuPassport&m=deleteEquipment'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить оборудование',
			title: 'Вопрос'
		});
	},
	openEquipmentEditWindow: function(action, Lpu_id)
	{
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}
		var grid = this.findById('LPEW_EquipmentGrid').getGrid();
		var params = {};
		params.action = action;
		params.Lpu_id = Lpu_id;
		params.callback = function(data) {
//			if ( !data ) {
//				return false;
//			}
//			var grid = current_window.findById('LPEW_OrgRSchetGrid').getGrid();
//			var record = grid.getStore().getById(data.OrgRSchet_id);
//
//			if ( !record ) {
//				if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgRSchet_id') > 0) ) {
//					grid.getStore().removeAll();
//				}
//				grid.getStore().loadData([ data ], true);
//			}
//			else {
//				var schet = new Array();
//
//				grid.getStore().fields.eachKey(function(key, item) {
//					schet.push(key);
//				});
//
//				for ( i = 0; i < schet.length; i++ ) {
//					record.set(schet[i], data[schet[i]]);
//				}
//
//				record.commit();
//			}
		}.createDelegate(this);

		if ( action == 'add' ) {
			params.LpuEquipment_id = 0;
			params.onHide = function() {
				current_window.findById('LPEW_EquipmentGrid').focus(true, 100);
			};
			getWnd('swLpuEquipmentEditWindow').show(params);
		} else	{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.LpuEquipment_id = grid.getSelectionModel().getSelected().get('LpuEquipment_id');
			params.LpuEquipmentPacs_id = grid.getSelectionModel().getSelected().get('LpuEquipmentPacs_id');
			params.Lpu_id = Lpu_id;
			params.onHide = function() {
				current_window.findById('LPEW_EquipmentGrid').focus(true, 100);
			};
			getWnd('swLpuEquipmentEditWindow').show(params);
		}
	},
	openMedProductCardEditWindow: function(action, Lpu_id)
	{
		var _this = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var grid = this.findById('LPEW_MedProductCardGrid').getGrid();
		var params = new Object();
		params.action = action;
		params.Lpu_id = Lpu_id;
		params.callback = function(data) {
			_this.findById('LPEW_MedProductCardGrid').loadData();
			//if ( !data ) {
			//	return false;
			//}
			//var grid = current_window.findById('LPEW_OrgRSchetGrid').getGrid();
			//var record = grid.getStore().getById(data.OrgRSchet_id);
			//
			//if ( !record ) {
			//	if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgRSchet_id') > 0) ) {
			//		grid.getStore().removeAll();
			//	}
			//	grid.getStore().loadData([ data ], true);
			//}
			//else {
			//	var schet = new Array();
			//
			//	grid.getStore().fields.eachKey(function(key, item) {
			//		schet.push(key);
			//	});
			//
			//	for ( i = 0; i < schet.length; i++ ) {
			//		record.set(schet[i], data[schet[i]]);
			//	}
			//
			//	record.commit();
			//}
		}

		if ( action == 'add' ) {
			params.LpuEquipment_id = 0;
			params.onHide = function() {
				_this.findById('LPEW_MedProductCardGrid').focus(true, 100);
			};
			getWnd('swMedProductCardEditWindow').show(params);
		} else	{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.MedProductCard_id = grid.getSelectionModel().getSelected().get('MedProductCard_id');
			params.Lpu_id = Lpu_id;
			params.onHide = function() {
				_this.findById('LPEW_MedProductCardGrid').focus(true, 100);
			};
			getWnd('swMedProductCardEditWindow').show(params);
		}
	},
	deleteMedProductCard: function() {

	var grid = this.findById('LPEW_MedProductCardGrid').getGrid();
	var record = grid.getSelectionModel().getSelected();
	if ( !record || !record.get('MedProductCard_id') )
		return;

	sw.swMsg.show({
		buttons: Ext.Msg.YESNO,
		fn: function(buttonId, text, obj) {
			if ( buttonId == 'yes' ) {
				Ext.Ajax.request({
					callback: function(options, success, response) {
						if ( success ) {
							var obj = Ext.util.JSON.decode(response.responseText);
							if(!obj.success) {
								return false;
							}
							grid.getStore().remove(record);

							if ( grid.getStore().getCount() == 0 ) {
								LoadEmptyRow(grid);
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
						else {
							sw.swMsg.alert('Ошибка', 'При удалении карточки медицинского изделия возникли ошибки');
						}
					},
					params: {
						MedProductCard_id: record.get('MedProductCard_id')
					},
					url: '/?c=LpuPassport&m=deleteMedProductCard'
				});
			}
		},
		icon: Ext.MessageBox.QUESTION,
		msg: 'Вы действительно хотите удалить запись?',
		title: 'Вопрос'
	});
	},
	openOrgRSchetEditWindow: function(action, Lpu_id)
	{
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swOrgRSchetEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования счета уже открыто');
			return false;
		}

		var grid = this.findById('LPEW_OrgRSchetGrid').getGrid();		

		var params = new Object();

		params.action = action;
		params.Lpu_id = Lpu_id;
		params.callback = function(data) {
			if ( !data ) {
				return false;
			}
			var grid = current_window.findById('LPEW_OrgRSchetGrid').getGrid();			
			var record = grid.getStore().getById(data.OrgRSchet_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgRSchet_id') > 0) ) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData([ data ], true);
			}
			else {
				var schet = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					schet.push(key);
				});

				for ( i = 0; i < schet.length; i++ ) {
					record.set(schet[i], data[schet[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);

		if ( action == 'add' ) {
			params.OrgRSchet_id = 0;
			params.onHide = function() {
				current_window.findById('LPEW_OrgRSchetGrid').focus(true, 100);
			};
			getWnd('swOrgRSchetEditWindow').show(params);
		}
		else
		{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.OrgRSchet_id = grid.getSelectionModel().getSelected().get('OrgRSchet_id');
			params.onHide = function() {
				current_window.findById('LPEW_OrgRSchetGrid').focus(true, 100);
			};
			getWnd('swOrgRSchetEditWindow').show(params);
		}
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swLpuPassportEditWindow.superclass.show.apply(this, arguments);

		this.maximize();

		this.findById('LpuPassportEditWindowTab').setActiveTab(10);
		this.findById('LpuPassportEditWindowTab').setActiveTab(9);
		this.findById('LpuPassportEditWindowTab').setActiveTab(8);
		this.findById('LpuPassportEditWindowTab').setActiveTab(7);
		this.findById('LpuPassportEditWindowTab').setActiveTab(6);
		this.findById('LpuPassportEditWindowTab').setActiveTab(5);
		this.findById('LpuPassportEditWindowTab').setActiveTab(4);
		this.findById('LpuPassportEditWindowTab').setActiveTab(3);
		this.findById('LpuPassportEditWindowTab').setActiveTab(2);
		this.findById('LpuPassportEditWindowTab').setActiveTab(1);
		this.findById('LpuPassportEditWindowTab').setActiveTab(0);

		//Поле "Тип МО по возрасту" фильтруем, убирая значение "Все МО". refs #11505
		this.findById('LPEW_LpuAgeType_id').setFilter([1,2,3]);
		this.findById('LPEW_IsAllowInternetModeration').setVisible(false);

		var _this = this;

		this.Org_id = null;

		if ( arguments[0].Lpu_id ) {
			this.Lpu_id = arguments[0].Lpu_id;
		}
		else {
			this.Lpu_id = null;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		else {
			if ( this.Lpu_id && this.Lpu_id > 0 ) {
				this.action = "edit";
			}
			else {
				this.action = "add";
			}
		}

		// https://redmine.swan.perm.ru/issues/15894
		var LpuSubjectionLevelPidList = new Array();
		var supInfoBaseForm = this.findById('Lpu_SupInfoPanel').getForm();

		// Получаем список родительских идентификаторов
		supInfoBaseForm.findField('LpuSubjectionLevel_id').getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('LpuSubjectionLevel_pid')) && !rec.get('LpuSubjectionLevel_pid').inlist(LpuSubjectionLevelPidList) ) {
				LpuSubjectionLevelPidList.push(rec.get('LpuSubjectionLevel_pid'));
			}
		});

		// Фильтруем список, оставляя только записи нижнего уровня
		supInfoBaseForm.findField('LpuSubjectionLevel_id').getStore().filterBy(function(rec) {
			return (!rec.get('LpuSubjectionLevel_id').inlist(LpuSubjectionLevelPidList));
		});

		this.resetForm();

		this.findById('LPEW_LevelType_id').setAllowBlank(true);

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.disableGrids();
			break;

			case 'edit':
				this.enableEdit(true);
				this.getLpuInfo(this.Lpu_id);
			break;

			case 'view':
				this.enableEdit(false);
				this.getLpuInfo(this.Lpu_id);
			break;
		}

		//Скрывам вкладки в зависимости от прав пользователей
		if (getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('MPCModer') != -1 && getGlobalOptions().groups.toString().indexOf('Admin') == -1 && getGlobalOptions().groups.toString().indexOf('SuperAdmin') == -1) {
			['tab_identification', 'tab_sprav', 'tab_ruk', 'tab_dogdd', 'tab_er', 'tab_zdanie', 'tab_pacs_equipment', 'tab_population', 'tab_med_care', 'tab_sanatoriumtreatment'].forEach(function(El){
				_this.findById('LpuPassportEditWindowTab').hideTabStripItem(El);
			});

			this.findById('LpuPassportEditWindowTab').unhideTabStripItem('tab_medprod'); //7
			this.findById('LpuPassportEditWindowTab').setActiveTab('tab_medprod'); //7
		} else {
			['tab_identification', 'tab_sprav', 'tab_ruk', 'tab_dogdd', 'tab_er', 'tab_medprod', 'tab_zdanie', 'tab_pacs_equipment', 'tab_population', 'tab_med_care', 'tab_sanatoriumtreatment'].forEach(function(El){
				_this.findById('LpuPassportEditWindowTab').unhideTabStripItem(El);
			});

			this.findById('LpuPassportEditWindowTab').setActiveTab('tab_identification'); //1
		}

		var org_rschet_grid = Ext.getCmp('LPEW_OrgRSchetGrid');
		var org_head = Ext.getCmp('LPEW_OrgHeadGrid');

		org_rschet_grid.focus();
	}
});