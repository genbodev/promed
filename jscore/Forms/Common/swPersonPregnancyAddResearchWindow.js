/**
* swPersonPregnancyAddResearchWindow - окно добавления исследования в регистре беременных.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @author       Gilmiyarov Artur aka GAF (turken@yandex.ru)
* @version      20.12.2017
*/

sw.Promed.swPersonPregnancyAddResearchWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	width: 900,
	height: 450,
	formParams: null,
	modal: true,
	resizable: false,
	draggable: true,
	closeAction: 'hide',
	buttonAlign: 'left',
	title: 'Исследования',
	//action: '/?c=EvnUslugaPrivateClinic&m=save',
	//Evn_id: 1357449,
	id:'PersonPregnancyPrivateClinic',
	Person_id: null,
	Evn_id: null,
	EvnXml_id: null,
	filterType: null,
	MedPersonal_iidd: null,
	saveOnce: false,
	plain: true,
	action: 'add',
	onWinClose: function () {
	},
	onPersonSelect: function () {
	},	
	show: function () {
		
		console.log('arguments----AAA');
		console.log(arguments);
		this.Person_id=arguments[0].Person_id;
		this.Evn_id=arguments[0].Evn_id;
		this.MedPersonal_iidd=arguments[0].MedPersonal_iidd;
		this.TypeUsluga=arguments[0].TypeUsluga;
		this.action = arguments[0].action;
		
		sw.Promed.swPersonPregnancyAddResearchWindow.superclass.show.apply(this, arguments);
		
		//чистка поле врач
		Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "MedPersonal_iidd")[0].store.removeAll();
		Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "MedPersonal_iidd")[0].setValue();		

		Ext.getCmp('PersonPregnancyPrivateClinic').find("name", "Research_Data")[0].setValue(arguments[0].Research_Data);		
		Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "Org_did")[0].setValue(arguments[0].Lpu_id);
		Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "UslugaComplex_id")[0].setValue(arguments[0].UslugaComplex_id);
									
		var base_form = Ext.getCmp('PersonPregnancyPrivateClinic');	
		combo = Ext.getCmp('PersonPregnancyPrivateClinic').formPanels[0].form.findField("Org_did");
		var Org_id = combo.getValue();

		combo.getStore().load({
			callback: function() {
				var Org_id = combo.getValue();
				if ( combo.getStore().getCount() > 0 ) {
					combo.setValue(Org_id);
				}
				else {
					combo.clearValue();
				}
			}.createDelegate(this),
			params: {
				Org_id: Org_id																}
		});															
		combo.fireEvent('change', combo, combo.getValue());
			
		var diag_combo = Ext.getCmp('PersonPregnancyPrivateClinic').formPanels[0].form.findField("UslugaComplex_id");
		var diag_id = diag_combo.getValue();			
		if ( !Ext.isEmpty(diag_id) ) {
			diag_combo.getStore().load({
				callback: function() {
					if ( diag_combo.getStore().getCount() > 0 ) {
						diag_combo.setValue(diag_id);
					}
					else {
						diag_combo.clearValue();
					}
				}.createDelegate(this),
				params: {
					UslugaComplex_id: diag_id
				}
			});
		}			
			

		var win = this;
		this.center();
		var base_form = this.ResearchPanel.getForm();

		//добавление общей услуги
		if (this.TypeUsluga == "research"){
			this.setTitle("Исследование" + (this.action == 'edit' ? ': Редактирование' : ': Добавление'));
			base_form.findField('UslugaComplex_id').setUslugaCategoryList([ 'gost2011' ]);		
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ ]);
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'lab' ]);
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'func' ]);
		}else if (this.TypeUsluga == "consult"){
			this.setTitle("Консультация" + (this.action == 'edit' ? ': Редактирование' : ': Добавление'));
			//base_form.findField('UslugaComplex_id').setUslugaCategoryList([ 'gost2011' ]);		
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ ]);			
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'consult' ]);
			//base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'func' ]);
		}
			
		this.onSaveUsluga = function() { 		
				
						
			if (typeof Ext.getCmp('PersonPregnancyPrivateClinic').find("name", "Research_Data")[0].value == "undefined" || Ext.getCmp('PersonPregnancyPrivateClinic').find("name", "Research_Data")[0].value == ""){
				sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: 'Поле "Дата" обязательна для заполнения.',
						title: 'Проверка данных формы'
					});
					return false;
			}
			
			if (typeof Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "Org_did")[0].value == "undefined" || Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "Org_did")[0].value == ""){			
				sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: 'Поле "МО" обязательна для заполнения.',
						title: 'Проверка данных формы'
					});
					return false;
			}			
 			
 			if (typeof Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "UslugaComplex_id")[0].value == "undefined"){			
 				sw.swMsg.show({
 						buttons: Ext.Msg.OK,
 						icon: Ext.Msg.WARNING,
						msg: 'Поле Услуга обязательна для заполнения.',
						msg: 'Поле "Услуга" обязательна для заполнения.',
 						title: 'Проверка данных формы'
 					});
 					return false;
			}
		
			var evnUslugaData = {
							'Research_Data': Ext.getCmp('PersonPregnancyPrivateClinic').find("name", "Research_Data")[0].value,
							'Lpu_id': Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "Org_did")[0].value,
							'MedPersonal_iidd': Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "MedPersonal_iidd")[0].value,
							'UslugaComplex_id': Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "UslugaComplex_id")[0].value,
							'Person_id':Ext.getCmp('PersonPregnancyPrivateClinic').Person_id,
							'Evn_id': this.Evn_id
			};
			
			console.log(".submit()--AAA");
			console.log(evnUslugaData);
		
			Ext.Ajax.request({				
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {this.hide();}.createDelegate(this) );
				},
				method: 'POST',
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( !Ext.isEmpty(response_obj.Error_Message) ) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Message);
						return false;
					}

					Ext.getCmp('PersonPregnancyPrivateClinic').FileListPanel.listParams.Evn_id=response_obj.EvnUsluga_id;										
					Ext.getCmp('PersonPregnancyPrivateClinic').FileListPanel.saveChanges(function(rec){
						Ext.getCmp("PersonPregnancyPrivateClinic").hide();
						if (Ext.getCmp('PersonPregnancyPrivateClinic').TypeUsluga == "research"){
							Ext.getCmp('swPersonPregnancyEditWindow').formPanels[4].ResearchGridPanel.refreshRecords(null,0);
						}else{
							Ext.getCmp('swPersonPregnancyEditWindow').formPanels[3].ConsultationGridPanel.refreshRecords(null,0);
						}
					});										
				}.createDelegate(this),
				params: evnUslugaData,
				url: '/?c=EvnUslugaPrivateClinic&m=save'
			});
		
		}.createDelegate(this);


		//загружаем файлы
		this.FileListPanel.saveOnce = this.saveOnce;
		this.FileListPanel.filterType = this.filterType;
		this.FileListPanel.listParams = {
			Evn_id: this.Evn_id,
			EvnXml_id: this.EvnXml_id,
			filterType: this.filterType
		};
		this.FileListPanel.loadData({
			Evn_id: this.Evn_id,
			EvnXml_id: this.EvnXml_id,
			filterType: this.filterType
		});

	},
	searchInProgress: false,
	initComponent: function () {
		var win = this;		
		
		this.FileListPanel = new sw.Promed.FileList({
                                    saveOnce: false,
                                    id: 'DOHEF_FileList',
                                    dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
                                    saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
                                    saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
                                    deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
                            });
                
		this.ResearchPanel = new Ext.form.FormPanel({              
				//frame: true,
				autoHeight: true,
				//region: 'north',
				region: 'north',
				border: false,
				id: 'research_form',
				autoLoad: false,
				buttonAlign: 'left',
				bodyStyle: 'background:#FFF;padding:0;',
				items: [
						{
								autoHeight: true,
								style: 'padding: 5px',
								layout: 'form',
								//labelAlign: 'top',
								bodyStyle: 'padding: 5px;',
								labelWidth: 95,
								items: [

										{
												allowBlank: false,
												xtype: 'swdatefield',                                                                        
												fieldLabel: 'Дата',
												name: 'Research_Data',
												//anchor: '95%',
												tabIndex: TABINDEX_PERSSEARCH + 0,
										},{
												allowBlank: false,
												displayField: 'Org_Name',
												editable: false,
												enableKeyEvents: true,
												fieldLabel: "МО",
												hiddenName: 'Org_did',
												listeners: {
													'change': function(combo, newValue, oldValue) {
														
														console.log("change-AAA");														
														
														if (!Ext.isEmpty(newValue)) {
															console.log('ufa-AAAA');
															Ext.Ajax.request({
																url: '/?c=Org&m=getLpuOnOrg',
																params: {Org_id: newValue},
																success: function(response, options){
																	console.log('ufa-success--AAAA');
																	
																	var responseObj = Ext.util.JSON.decode(response.responseText);
																	console.log(responseObj);
																	if ((!Ext.isEmpty(responseObj))) {
																		console.log(response.responseText);
																		var base_form = Ext.getCmp('PersonPregnancyPrivateClinic').formPanels[0].form;																		
																		var med_personal_combo = base_form.findField('MedPersonal_iidd');
																		console.log(med_personal_combo);
																		if (Ext.isEmpty(newValue) || newValue == -1) {
																				console.log('stavim this.MedPersonal_iidd');

																				
																					med_personal_combo.setValue(null);
																					med_personal_combo.getStore().removeAll();
																				
																		} else {
																				console.log('med_personal_combo');
																				
																				if (this.MedPersonal_iidd != ""){
																					
																					
																					Ext.Ajax.request({
																						url: '/?c=EvnUslugaPrivateClinic&m=getMedPersonalid',
																						params: {Evn_id: Ext.getCmp('PersonPregnancyPrivateClinic').Evn_id},
																						success: function(response, options){
																							console.log('URA');

																							med_personal_combo.getStore().load({
																									params: {Lpu_iid: responseObj},
																									callback: function() {
																										console.log('diag_combo-A');
																										console.log(response.responseText);
																										var responseObj = Ext.util.JSON.decode(response.responseText);
																										console.log(responseObj);
																										if ((!Ext.isEmpty(responseObj))) {
																											console.log(response.responseText);
																											Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "MedPersonal_iidd")[0].setValue(responseObj);
																										}	
																									}.createDelegate(this)																						
																							});


																						}
																					});
																					
																					
																				}else{																				
																				
																					med_personal_combo.getStore().load({
																						params: {Lpu_iid: responseObj}
																					});
																				
																				}
																				
																		}
																		return false;
																	}else{
																		
																		Ext.getCmp('PersonPregnancyPrivateClinic').find("hiddenName", "MedPersonal_iidd")[0].store.removeAll()
																	}
																}
															});
														}
														
														
													
													}.createDelegate(this),
//													'keydown': function( inp, e ) {
//														console.log("keydown-AAA");
//														if ( inp.disabled )
//															return;
//
//														if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
//															var base_form = this.FormPanel.getForm();
//
//															e.stopEvent();
//
//															if ( !this.findById(win.id+'EStEF_EvnStickCarePersonPanel').hidden && !this.findById(win.id+'EStEF_EvnStickCarePersonPanel').collapsed && this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
//																this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
//																this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
//															}
//															else if ( !this.findById(win.id+'EStEF_StickRegimePanel').collapsed && this.action != 'view' ) {
//																if ( !base_form.findField('EvnStick_IsRegPregnancy').hidden ) {
//																	base_form.findField('EvnStick_IsRegPregnancy').focus(true);
//																}
//																else {
//																	base_form.findField('StickIrregularity_id').focus(true);
//																}
//															}
//															else if ( !this.findById(win.id+'EStEF_MSEPanel').collapsed && this.action != 'view' ) {
//																base_form.findField('EvnStick_mseDate').focus(true);
//															}
//															else if ( !this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').collapsed && this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
//																this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
//																this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
//															}
//															else if ( !this.findById(win.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
//																base_form.findField('StickLeaveType_id').focus(true);
//															}
//															else if ( this.action != 'view' ) {
//																this.buttons[0].focus();
//															}
//															else {
//																this.buttons[1].focus();
//															}
//														}
//														else if ( e.F4 == e.getKey() ) {
//															if ( e.browserEvent.stopPropagation )
//																e.browserEvent.stopPropagation();
//															else
//																e.browserEvent.cancelBubble = true;
//
//															if ( e.browserEvent.preventDefault )
//																e.browserEvent.preventDefault();
//															else
//																e.browserEvent.returnValue = false;
//
//															e.returnValue = false;
//
//															if ( Ext.isIE ) {
//																e.browserEvent.keyCode = 0;
//																e.browserEvent.which = 0;
//															}
//
//															inp.onTrigger1Click();
//
//															return false;
//														}
//													}.createDelegate(this),
//													'keyup': function(inp, e) {
//														console.log("keyup-AAA");
//														if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
//															var base_form = this.FormPanel.getForm();
//
//															e.stopEvent();
//
//															if ( !this.findById(win.id+'EStEF_EvnStickCarePersonPanel').hidden && !this.findById(win.id+'EStEF_EvnStickCarePersonPanel').collapsed && this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
//																this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
//																this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
//															}
//															else if ( !this.findById(win.id+'EStEF_StickRegimePanel').collapsed && this.action != 'view' ) {
//																if ( !base_form.findField('EvnStick_IsRegPregnancy').hidden ) {
//																	base_form.findField('EvnStick_IsRegPregnancy').focus(true);
//																}
//																else {
//																	base_form.findField('StickIrregularity_id').focus(true);
//																}
//															}
//															else if ( !this.findById(win.id+'EStEF_MSEPanel').collapsed && this.action != 'view' ) {
//																base_form.findField('EvnStick_mseDate').focus(true);
//															}
//															else if ( !this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').collapsed && this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
//																this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
//																this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
//															}
//															else if ( !this.findById(win.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
//																base_form.findField('StickLeaveType_id').focus(true);
//															}
//															else if ( this.action != 'view' ) {
//																this.buttons[0].focus();
//															}
//															else {
//																this.buttons[1].focus();
//															}
//														}
//														else if ( e.F4 == e.getKey() ) {
//															if ( e.browserEvent.stopPropagation )
//																e.browserEvent.stopPropagation();
//															else
//																e.browserEvent.cancelBubble = true;
//
//															if ( e.browserEvent.preventDefault )
//																e.browserEvent.preventDefault();
//															else
//																e.browserEvent.returnValue = false;
//
//															e.returnValue = false;
//
//															if ( Ext.isIE ) {
//																e.browserEvent.keyCode = 0;
//																e.browserEvent.which = 0;
//															}
//
//															return false;
//														}
//													}.createDelegate(this)
												},
												mode: 'local',
												onTrigger1Click: function() {
													console.log('onTrigger1Click-AAAA');
													console.log(this);
													var base_form = Ext.getCmp('PersonPregnancyPrivateClinic');
													var combo = base_form.find("name",'Org_did');

													if ( combo.disabled ) {
														return false;
													}

													getWnd('swOrgSearchWindow').show({
														object: 'org',
														onClose: function() {
															console.log('onClose-AAAA');
															var combo = Ext.getCmp('PersonPregnancyPrivateClinic').formPanels[0].form.findField("Org_did");
															combo.focus(true, 200)
														},
														onSelect: function(org_data) {
															console.log('onSelect-AAAA');
															console.log(org_data.Org_id);
															console.log(org_data.Org_Name);
															var base_form = Ext.getCmp('PersonPregnancyPrivateClinic');															
															combo = Ext.getCmp('PersonPregnancyPrivateClinic').formPanels[0].form.findField("Org_did");
															if ( org_data.Org_id > 0 ) {
																combo.getStore().loadData([{
																	Org_id: org_data.Org_id,
																	Org_Name: org_data.Org_Name
																}]);
																combo.setValue(org_data.Org_id);
																getWnd('swOrgSearchWindow').hide();
																combo.collapse();
																combo.fireEvent('change', combo, combo.getValue());
															}
														}
													});
												}.createDelegate(this),
												store: new Ext.data.JsonStore({
													autoLoad: false,
													fields: [
														{ name: 'Org_id', type: 'int' },
														{ name: 'Org_Name', type: 'string' }
													],
													key: 'Org_id',
													sortInfo: {
														field: 'Org_Name'
													},
													url: C_ORG_LIST
												}),
												tabIndex: TABINDEX_ESTEF + 19,
												tpl: new Ext.XTemplate(
													'<tpl for="."><div class="x-combo-list-item">',
													'{Org_Name}',
													'</div></tpl>'
												),
												trigger1Class: 'x-form-search-trigger',
												triggerAction: 'none',
												valueField: 'Org_id',
												width: 400,
												xtype: 'swbaseremotecombo'
										},{
											
												allowBlank: true,
												xtype: 'swmedpersonalcombo',
												hiddenName: 'MedPersonal_iidd',
												fieldLabel: 'Врач',
												width: 400
												//listWidth: 300
										},
//										{
//												fieldLabel: 'Лабораторно-диагностическая',
//												hiddenName: 'UslugaComplex_Checkbox',
//												valueField: 'UslugaComplex_Checkbox',
//												listWidth: 590,
//												tabIndex: TABINDEX_EPSSW + 104,
//												width: 250,
//												xtype: 'swcheckbox',
//												listeners: {
//													'check': function(checkbox, isChecked){
//														var base_form = this.ResearchPanel.getForm();
//														if (isChecked){                 
//															//Лабораторно-диагностическая
//															base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ ]);
//															//base_form.findField('UslugaComplex_Code_From').setAllowedUslugaComplexAttributeList([ 'oper' ]);
//															base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'lab' ]);
//														}else{
//															//Функционально-диагностическая
//															base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ ]);
//															base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'func' ]);
//
//														}
//													}.createDelegate(this)
//												}
//										},
										{
												fieldLabel: 'Услуга',
												hiddenName: 'UslugaComplex_id',
												valueField: 'UslugaComplex_id',
												listWidth: 590,
												tabIndex: TABINDEX_EPSSW + 104,
												width: 400,
												xtype: 'swuslugacomplexnewcombo',
												allowBlank: false
										}
										]
						}]
		});                
		this.FilePanel = new sw.Promed.Panel({
				title: lang['elektronnyie_kopii_dokumentov'],
				id: 'DOHEF_FilePanel',
				collapsible: false,
				region: 'center',
				autoHeight: true,
				bodyStyle: 'padding: 5px;',
				items: [
					this.FileListPanel
				],
				listeners: {
						'expand':function(panel){
								this.FileListPanel.doLayout();
						}.createDelegate(this)
				}
		});               
                

		Ext.apply(this, {
			items: [
				this.ResearchPanel,
				this.FilePanel                    
			],
			buttons: [{
					iconCls: 'save16',
					text: 'Сохранить',
					handler: function () {
						this.ownerCt.onSaveUsluga();
					},
					tabIndex: TABINDEX_PERSSEARCH + 21
				},
				{
					text: '-'
				},				
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function () {
						this.ownerCt.hide()
					},
					tabIndex: TABINDEX_PERSSEARCH + 21
				}
			]
		});

		sw.Promed.swPersonPregnancyAddResearchWindow.superclass.initComponent.apply(this, arguments);
	}
});