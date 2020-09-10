/*
 * swRegistrationJournalSearchWindow - окно окно поиска людей.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	 DLO
 * @access	  public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	  Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @version	 10.03.2009
 */
sw.Promed.swRegistrationJournalSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	layout:'border',
	width:900,
	height:500,
	codeRefresh:true,
	formParams:null,
	modal:false,
	maximizable:true,
	resizable:false,
	draggable:true,
//	closable: false,
	collapsible:true,
	closeAction:'hide',
	buttonAlign:'left',
	title:WND_REGJOURNAL_SEARCH,
	id:'swRegistrationJournalSearchWindow',
	listeners:{
		'hide':function () {
			this.onWinClose();
		}
	},
	plain:true,
	searchWindowOpenMode:null,
	onWinClose:function () {
	},
	onPersonSelect:function () {
	},
	onOkButtonClick:function (/*callback_data*/) {

		var loadMask = new Ext.LoadMask(Ext.getBody(), {msg:"Синхронизация справочников..."});
		loadMask.show();

		Ext.Ajax.request({
			failure:function (response/*, options*/) {
				loadMask.hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			success:function (response/*, options*/) {
				loadMask.hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.success) {
					sw.swMsg.alert(lang['soobschenie'], lang['spravochniki_uspeshno_sinhronizirovanyi'], function () {
						reloadTree();
					});
				}
				else if (response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			url:'/?c=Lis&m=getDirectoryVersions'
		});

	},
	show:function () {
		sw.Promed.swRegistrationJournalSearchWindow.superclass.show.apply(this, arguments);
		LoadEmptyRow(this.findById('rjswListGrid').getGrid());
		this.center();
		this.maximize();
		Ext.getCmp('registration_journal_search_form_tab_panel').setActiveTab(0);

		//var form = this;
		var form = this.findById('registration_journal_search_form').getForm();

		setLpuSectionGlobalStoreFilter({
			//dateTo: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y')
		});
		form.findField('custDepartments_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		setMedStaffFactGlobalStoreFilter({
			//dateTo: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y')
		});
		form.findField('doctors_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		form.findField('departments_id').getStore().loadData(getStoreRecords(swLpuUnitGlobalStore));

		/*		// флаг того, что осуществлялся поиск
		 this.isSearched = false;

		 // объект с данными, передаваемыми в дочернюю форму
		 // можно использовать в onSelect
		 this.formParams = new Object();

		 // этот параметр определяет объект, для которой был вызван поиск человека
		 this.searchWindowOpenMode = null;

		 this.PersonEditWindow = getWnd('swPersonEditWindow');

		 var form = this.findById('person_search_form').getForm();
		 form.reset();

		 form.findField('PersonSurName_SurName').focus(true, 500);
		 var grid = this.findById('rjswListGrid');

		 grid.getStore().removeAll();
		 //grid.getStore().baseParams = form.getValues();
		 grid.getTopToolbar().items.items[1].disable();
		 grid.getTopToolbar().items.items[2].disable();
		 grid.getTopToolbar().items.items[4].disable();
		 grid.getTopToolbar().items.items[6].el.innerHTML = '0 / 0';

		 if ( arguments[0] )
		 {
		 if ( arguments[0].formParams && typeof arguments[0].formParams == 'object' ) {
		 this.formParams = arguments[0].formParams;
		 }

		 if ( arguments[0].personFirname )
		 form.findField('PersonFirName_FirName').setRawValue(arguments[0].personFirname);

		 if ( arguments[0].personSecname )
		 form.findField('PersonSecName_SecName').setRawValue(arguments[0].personSecname);

		 if ( arguments[0].personSurname )
		 form.findField('PersonSurName_SurName').setRawValue(arguments[0].personSurname);

		 if ( arguments[0].onClose )
		 this.onWinClose = arguments[0].onClose;
		 else
		 this.onClose = Ext.emptyFn;

		 if ( arguments[0].onSelect )
		 this.onPersonSelect = arguments[0].onSelect;
		 else
		 this.onClose = Ext.emptyFn;

		 if ( arguments[0].searchMode )
		 this.searchMode = arguments[0].searchMode;
		 else
		 this.searchMode = 'all';

		 if ( arguments[0].Year )
		 this.Year = arguments[0].Year;
		 else
		 this.Year = null;

		 if (this.searchMode=='wow')
		 {
		 this.setTitle(WND_PERS_SEARCH+lang['tolko_po_registru_vov']);
		 }
		 else
		 {
		 this.setTitle(WND_PERS_SEARCH);
		 }

		 if ((this.searchMode=='attachrecipients') && (!getGlobalOptions().isMinZdrav) && (!getGlobalOptions().isOnko) && (!getGlobalOptions().isOnkoGem) && (!getGlobalOptions().isPsih)  && (!getGlobalOptions().isRA))
		 {
		 this.setTitle(WND_PERS_SEARCH+lang['prikreplennyie_lgotniki']);
		 }
		 else
		 {
		 this.setTitle(WND_PERS_SEARCH);
		 }

		 if ( arguments[0].searchWindowOpenMode ) {
		 this.searchWindowOpenMode = arguments[0].searchWindowOpenMode;
		 }

		 if ( arguments[0].childPS ) {
		 this.openChildPS = true;
		 } else {
		 this.openChildPS = false;
		 }

		 }

		 form.findField('Person_Snils').setValue('');
		 form.findField('PersonCard_Code').setValue('');
		 form.findField('EvnPS_NumCard').setValue('');
		 form.findField('Polis_Ser').setValue('');
		 form.findField('Polis_Num').setValue('');
		 form.findField('Polis_EdNum').setValue('');
		 form.findField('EvnUdost_Ser').setValue('');
		 form.findField('EvnUdost_Num').setValue('');
		 form.findField('PersonFirName_FirName').setValue('');
		 form.findField('PersonSecName_SecName').setValue('');
		 form.findField('PersonBirthDay_BirthDay').setValue('');

		 Ext.getCmp('patient_search_form_tab_panel').setActiveTab(0);
		 grid.getStore().baseParams.searchMode = this.searchMode;
		 */
	},
	doSearch:function () {

		var grid = this.findById('rjswListGrid');
		var form = this.findById('registration_journal_search_form').getForm();
		/*
		 if (
		 form.getForm().findField('Person_Snils').getValue() != ''
		 || form.getForm().findField('PersonCard_Code').getValue() != ''
		 || form.getForm().findField('EvnPS_NumCard').getValue() != ''
		 || ( form.getForm().findField('Polis_Ser').getValue() != '' && form.getForm().findField('Polis_Num').getValue() != '' )
		 || form.getForm().findField('Polis_EdNum').getValue() != ''
		 || ( form.getForm().findField('EvnUdost_Ser').getValue() != '' && form.getForm().findField('EvnUdost_Num').getValue() != '' )
		 || ( form.getForm().findField('PersonFirName_FirName').getValue() != '' && form.getForm().findField('PersonSecName_SecName').getValue() != '' && form.getForm().findField('PersonBirthDay_BirthDay').getValue() != '' )
		 )
		 flag = false;

		 if ( (flag && form.getForm().findField('PersonSurName_SurName').getValue()=='') && !soc_card_id )
		 {
		 var win = this;
		 Ext.Msg.alert("Сообщение", "Не заполнены обязательные поля. Возможные варианты поиска:<br/>"+
		 "Поиск по фамилии.<br/>"+
		 "Поиск по совпадению имени, отчества и даты рождения.<br/>"+
		 "Поиск по точному совпадению СНИЛС.<br/>"+
		 "Поиск по точному совпадению номера амбулаторной карты.<br/>"+
		 "Поиск по точному совпадению номера КВС.<br/>"+
		 "Поиск по точному совпадению серии и номера полиса.<br/>"+
		 "Поиск по точному совпадению ЕНП.<br/>"+
		 "Поиск по точному совпадению серии и номера удостоверения льготника.<br/>"+
		 "Поиск по совпадению имени, отчества и даты рождения.<br/>"
		 , function() {
		 form.getForm().findField('PersonSurName_SurName').focus(true, 100);
		 });
		 return false;
		 }
		 */
		grid.removeAll();
		var params = form.getValues();
		params.priority = form.findField('Priority_id').getFieldValue('Priority_Code');
		params.defectState = form.findField('DefectState_id').getFieldValue('DefectState_Code');
		//log(params);
		grid.loadData({
			params:params,
			globalFilters:params
		});
	},
	initComponent:function () {
		var that = this;
		// грид для отображения списка данных
		this.ListGrid = new sw.Promed.ViewFrame({
			id:'rjswListGrid',
			region:'center',
			object:'RegistrationJournal',
			border:true,
			dataUrl:'/?c=Lis&m=listRegistrationJournal',
			toolbar:true,
			autoLoadData:false,
			//editformclassname: '',
			//paging: true,
			//root: 'data',
			//totalProperty: 'totalCount',
			stringfields:[
				{name:'id', type:'int', header:'ID', key:true},
				// internalNr
				{name:'internalNr', type:'string', header:lang['№_zayavki'], width:100},
				{name:'requestFormId', width:150, header:lang['podrazdelenie'], hidden:true},
				{name:'requestForm_name', width:150, header:lang['podrazdelenie']},
				{name:'sampleDeliveryDate', width:120, header:lang['data_dostavki'], type:'datetime'},
				{name:'endDate', width:120, header:lang['data_zakryitiya'], type:'datetime'},
				{name:'code', width:80, header:lang['nomer_kartyi']},
				{name:'lastName', header:lang['familiya'], autoexpand:true},
				{name:'firstName', width:100, header:lang['imya']},
				{name:'middleName', width:100, header:lang['otchestvo']},
				{name:'birthDate', width:80, header:lang['data_rojdeniya'], type:'date'},
				{name:'Sex', width:30, hidden:true, header:lang['pol']},
				{name:'Sex_name', width:30, header:lang['pol']},
				{name:'custHospitalId', hidden:true, width:80, header:lang['lpu']},
				// Заказчик
				{name:'custHospital_name', width:80, header:lang['lpu']},
				// Заказчик
				{name:'custDepartmentId', width:80, header:lang['otdelenie']},
				{name:'custDoctorId', width:80, header:lang['vrach']},
				{name:'defectState', width:80, header:lang['nalichie_brakov'], type:'checkbox'},
				{name:'endDate', width:80, header:lang['data_zakryitiya'], type:'datetime'},
				{name:'state', width:80, hidden:true, header:lang['status']},
				{name:'state_name', width:80, header:lang['status']},
				{name:'source', width:120, hidden:true, header:'Источник заявки'} // TODO: Источник заявки, надо сделать связывание
				/*{name: 'Priority', hidden: true},
				 {name: 'State', hidden: true},
				 {name: 'Timestamp', hidden: true},
				 {name: 'Source', hidden: true},
				 {name: 'Code', hidden: true},

				 {name: 'Removed', hidden: true},
				 {name: 'Sex', hidden: true},
				 {name: 'cyclePeriod', width: 120, header: lang['period_tsikla']},
				 {name: 'patientNr', width: 40, header: lang['№_patsienta']},
				 {name: 'internalNr', width: 40, header: lang['№_zayavki']},
				 {name: 'patientCardNr', width: 80, header: lang['№_kartyi_patsienta']},
				 {name: 'pregnancyDuration', width: 80, header: lang['srok_beremennosti']},

				 {name: 'samplingDate', width: 80, header: lang['data_vzyatiya_biomateriala']},

				 {name: 'Delivered', width: 80, header: lang['dostavlen']},
				 {name: 'Printed', width: 80, header: lang['raspechatan_otvet']},
				 {name: 'originalSent', width: 80, header: lang['vyislan_otvet']},
				 {name: 'copySent', width: 80, header: lang['vyislana_kopiya_otveta']},
				 {name: 'externalSystemId', hidden: true},
				 {name: 'copyMustBeSent', width: 80, header: lang['neobhodimo_vyislat_kopiyu_otveta']},

				 {name: 'requestFormId', width: 80, header: lang['forma']},
				 {name: 'payCategoryId', width: 80, header: lang['kategoriya_oplatyi']},
				 {name: 'endDate', width: 80, header: lang['data_zakryitiya_zayavki'], type: 'date'}*/
			],
			actions:[
				{name:'action_add', handler:function () {
					this.openLisRequestEditWindow('add');
				}.createDelegate(this) },
				{name:'action_edit', disabled:true},
				{name:'action_view', disabled:true},
				{name:'action_delete', disabled:true}
			],
			onRowSelect:function (/*sm, index, record*/) {
				//var form = 
			}
		});
		Ext.apply(this, {
			items:[
				new Ext.form.FormPanel({
					frame:true,
					autoHeight:true,
					region:'north',
					id:'registration_journal_search_form',
					autoLoad:false,
					buttonAlign:'left',
					items:[
						new Ext.TabPanel({
							id:'registration_journal_search_form_tab_panel',
							activeTab:0,
							layoutOnTabChange:true,
							listeners:{
								'tabchange':function (tab, panel) {
									var els = panel.findByType('textfield', false);
									if (els == undefined) {
										els = panel.findByType('combo', false);
									}
									var el = els[0];
									if (el != undefined && el.focus) {
										el.focus(true, 200);
									}
								}
							},
							items:[
								{
									title:lang['1_osnovnoe'],
									height:110,
									labelAlign:'right',
									style:'padding: 5px;',
									layout:'form',
									labelWidth:175,
									items:[
										{
											layout:'column',
											style:'padding-left: 10px; ',
											items:[
												{
													labelWidth:90,
													layout:'form',
													items:[
														{
															xtype:'textfield',
															fieldLabel:lang['nomer'],
															width:180,
															name:'nr',
															tabIndex:TABINDEX_RJSW
														},
														{
															fieldLabel:lang['prioritet'],
															name:'Priority_id',
															hiddenName:'Priority_id',
															tabIndex:TABINDEX_RJSW + 4,
															width:180,
															prefix: 'lis_',
															comboSubject:'Priority',
															sortField:'Priority_Code',
															xtype:'swcommonsprcombo'
														},
														{
															fieldLabel:lang['brak'],
															name:'DefectState_id',
															hiddenName:'DefectState_id',
															tabIndex:TABINDEX_RJSW + 5,
															width:180,
															prefix: 'lis_',
															comboSubject:'DefectState',
															sortField:'DefectState_Code',
															xtype:'swcommonsprcombo'
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px; ',
													layout:'form',
													items: [
														{
															xtype:'fieldset',
															labelAlign:'right',
															height: 80,
															width: 270,
															title: lang['data_dostavki_probyi'],
															labelWidth: 15,
															items: [
																{
																	dateLabel:lang['s'],
																	hiddenName:'dateFrom',
																	tabIndex:TABINDEX_RJSW + 1,
																	xtype:'swdatetimefield'
																},
																{
																	dateLabel:lang['po'],
																	hiddenName:'dateTill',
																	tabIndex:TABINDEX_RJSW + 2,
																	xtype:'swdatetimefield'
																}
															]
														}
													]
												}

											]
										}
									]
								},
								{
									title:lang['2_patsient'],
									height:110,
									labelAlign:'left',
									style:'padding: 5px',
									layout:'form',
									labelWidth:75,
									items:[
										{
											layout:'column',
											style:'padding-left: 10px',
											items:[
												{
													labelWidth:90,
													layout:'form',
													items:[
														{
															xtype:'textfield',
															fieldLabel:lang['familiya'],
															width:180,
															name:'lastName',
															tabIndex:TABINDEX_RJSW + 7
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px',
													layout:'form',
													items:[
														{
															xtype:'textfield',
															fieldLabel:lang['imya'],
															width:180,
															name:'firstName',
															tabIndex:TABINDEX_RJSW + 8
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px',
													layout:'form',
													items:[
														{
															xtype:'textfield',
															fieldLabel:lang['otchestvo'],
															width:180,
															name:'middleName',
															tabIndex:TABINDEX_RJSW + 9
														}
													]
												}
											]
										},
										{
											layout:'column',
											style:'padding-left: 10px',
											items:[
												{
													labelWidth:90,
													layout:'form',
													items:[
														{
															xtype:'swdatefield',
															fieldLabel:lang['d_r'],
															width:180,
															name:'birthDate',
															tabIndex:TABINDEX_RJSW + 10
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px',
													layout:'form',
													items:[
														{
															fieldLabel:lang['pol'],
															name:'Sex_id',
															hiddenName:'Sex_id',
															width:180,
															prefix:'lis_',
															tabIndex:TABINDEX_RJSW + 11,
															comboSubject:'Sex',
															sortField:'Sex_Code',
															xtype:'swcustomobjectcombo'
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px',
													layout:'form',
													items:[
														{
															xtype:'textfield',
															fieldLabel:lang['nomer'],
															width:180,
															name:'patientNr',
															tabIndex:TABINDEX_RJSW + 12
														}
													]
												}
											]
										},
										{
											layout:'column',
											style:'padding-left: 10px',
											items:[
												{
													labelWidth:90,
													layout:'form',
													items:[
														{
															xtype:'textfield',
															fieldLabel:lang['nomer_kartyi'],
															width:180,
															name:'patientCardNr',
															tabIndex:TABINDEX_RJSW + 13
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px',
													layout:'form',
													items:[
														{
															xtype:'textfield',
															fieldLabel:lang['nomer_scheta'],
															width:180,
															name:'billNr',
															tabIndex:TABINDEX_RJSW + 14
														}
													]
												}
											]
										}
									]
								},
								{
									title:lang['3_dopolnitelno'],
									height:110,
									labelAlign:'left',
									style:'padding: 5px',
									layout:'form',
									labelWidth:75,
									items:[
										{
											layout:'column',
											style:'padding-left: 10px',
											items:[
												{
													labelWidth:90,
													layout:'form',
													items:[
														{
															fieldLabel:lang['status'],
															name:'States_id',
															hiddenName:'States_id',
															width:180,
															prefix:'lis_',
															tabIndex:TABINDEX_RJSW + 15,
															comboSubject:'States',
															sortField:'States_Code',
															xtype:'swcustomobjectcombo'
														},
														{
															fieldLabel:lang['zakazchik'],
															name:'Hospital_id',
															hiddenName:'Hospital_id',
															width:180,
															prefix:'lis_',
															tabIndex:TABINDEX_RJSW + 17,
															comboSubject:'Hospital',
															sortField:'Hospital_Code',
															xtype:'swcustomobjectcombo'
														},
														{
															fieldLabel:lang['vrach'],
															name:'doctors_id',
															hiddenName:'doctors_id',
															tabIndex:TABINDEX_RJSW + 16,
															width:180,
															xtype:'swmedstafffactglobalcombo'
														},
														{
															fieldLabel:lang['otdelenie'],
															name:'custDepartments_id',
															hiddenName:'custDepartments_id',
															tabIndex:TABINDEX_RJSW + 18,
															width:180,
															xtype:'swlpusectionglobalcombo'
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px',
													layout:'form',
													items:[
														{
															fieldLabel:lang['podrazdelenie'],
															name:'departments_id',
															hiddenName:'departments_id',
															tabIndex:TABINDEX_RJSW + 19,
															width:180,
															xtype:'swlpuunitcombo'
														},
														{
															fieldLabel:lang['issledovanie'],
															name:'Target_id',
															hiddenName:'Target_id',
															width:180,
															prefix:'lis_',
															tabIndex:TABINDEX_RJSW + 20,
															comboSubject:'Target',
															sortField:'Target_Code',
															xtype:'swcustomobjectcombo'
														},
														{
															fieldLabel:lang['dop_status'],
															name:'CustomStates_id',
															hiddenName:'CustomStates_id',
															width:180,
															prefix:'lis_',
															tabIndex:TABINDEX_RJSW + 21,
															comboSubject:'CustomStates',
															sortField:'CustomStates_Code',
															xtype:'swcustomobjectcombo'
														},
														{
															fieldLabel:lang['forma'],
															name:'RequestForm_id',
															hiddenName:'RequestForm_id',
															width:180,
															prefix:'lis_',
															tabIndex:TABINDEX_RJSW + 22,
															comboSubject:'RequestForm',
															sortField:'RequestForm_Code',
															xtype:'swcustomobjectcombo'
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px; ',
													layout:'form',
													items:[
														{
															xtype:'fieldset',
															labelAlign:'right',
															height: 80,
															width: 270,
															title: lang['data_izmeneniya'],
															labelWidth: 15,
															items: [
																{
																	dateLabel:lang['s'],
																	hiddenName:'lastModificationDateFrom',
																	tabIndex:TABINDEX_RJSW + 1,
																	xtype:'swdatetimefield'
																},
																{
																	dateLabel:lang['po'],
																	hiddenName:'lastModificationDateTill',
																	tabIndex:TABINDEX_RJSW + 2,
																	xtype:'swdatetimefield'
																}
															]
														}
													]
												},
												{
													labelWidth:90,
													style:'padding-left: 10px; ',
													layout:'form',
													items:[
														{
															xtype:'fieldset',
															labelAlign:'right',
															height: 80,
															width: 270,
															title: lang['data_zakryitiya'],
															labelWidth: 15,
															items: [
																{
																	dateLabel:lang['s'],
																	hiddenName:'endDateFrom',
																	tabIndex:TABINDEX_RJSW + 1,
																	xtype:'swdatetimefield'
																},
																{
																	dateLabel:lang['po'],
																	hiddenName:'endDateTill',
																	tabIndex:TABINDEX_RJSW + 2,
																	xtype:'swdatetimefield'
																}
															]
														}
													]
												}
											]
										}
									]
								}
							]
						})
					],
					keys:[
						{
							key:Ext.EventObject.ENTER,
							fn:function (/*e*/) {
								Ext.getCmp('swRegistrationJournalSearchWindow').doSearch();
							},
							stopEvent:true
						}
					]
				}),

				this.ListGrid
			],
			buttons:[
				{
					text:BTN_FRMSEARCH,
					iconCls:'search16',
					handler:function () {
						this.ownerCt.doSearch()
					},
					tabIndex:1109
				},
				{
					text:BTN_FRMRESET,
					iconCls:'resetsearch16',
					handler:function () {
						var form = this.ownerCt.findById('registration_journal_search_form').getForm();
						form.reset();
						form.findField('nr').focus(true, 100);
						var grid = this.ownerCt.ListGrid;
						grid.removeAll();
					},
					tabIndex:1110
				},
				{
					iconCls:'ok16',
					text:lang['sinhronizatsiya_spravochnikov'],
					handler:function () {
						this.ownerCt.onOkButtonClick()
					},
					tabIndex:1111
				},
				{
					text:'-'
				},
				HelpButton(this, 1112),
				{
					iconCls:'cancel16',
					text:BTN_FRMCLOSE,
					handler:function () {
						this.ownerCt.hide()
					},
					tabIndex:1113
				}
			],
			keys:[
				{
					key:Ext.EventObject.INSERT,
					fn:function (inp, e) {
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						// TODO: ПОка действия выключены
						//Ext.getCmp('swRegistrationJournalSearchWindow').findById('rjswListGrid').getTopToolbar().items.items[0].handler();
					},
					stopEvent:true
				},
				{
					key:Ext.EventObject.ENTER,
					fn:function (inp, e) {
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						Ext.getCmp('swRegistrationJournalSearchWindow').onOkButtonClick();
					},
					stopEvent:true
				},
				{
					key:Ext.EventObject.F4,
					fn:function (inp, e) {
						if (e.altKey || e.ctrlKey || e.shiftKey)
							return true;
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						Ext.getCmp('swRegistrationJournalSearchWindow').findById('rjswListGrid').getTopToolbar().items.items[1].handler();
					},
					stopEvent:true
				},
				{
					key:Ext.EventObject.F3,
					fn:function (inp, e) {
						if (e.altKey || e.ctrlKey || e.shiftKey)
							return true;
						e.stopEvent();

						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						Ext.getCmp('swRegistrationJournalSearchWindow').findById('rjswListGrid').getTopToolbar().items.items[2].handler();
					},
					stopEvent:true
				},
				{
					alt:true,
					fn:function (inp, e) {
						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;
						if (e.browserEvent.preventDefault)
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

							Ext.getCmp('swRegistrationJournalSearchWindow').buttons[2].handler();
							return false;
						}
						if (e.getKey() == Ext.EventObject.C) {
							Ext.getCmp('swRegistrationJournalSearchWindow').buttons[1].handler();
							return false;
						}

						var search_filter_tabbar = Ext.getCmp('registration_journal_search_form_tab_panel');
						switch (e.getKey()) {
							case Ext.EventObject.NUM_ONE:
							case Ext.EventObject.ONE:
								search_filter_tabbar.setActiveTab(0);
								break;

							case Ext.EventObject.NUM_TWO:
							case Ext.EventObject.TWO:
								search_filter_tabbar.setActiveTab(1);
								break;
						}

					},
					key:[
						Ext.EventObject.C,
						Ext.EventObject.J,
						Ext.EventObject.NUM_ONE,
						Ext.EventObject.NUM_TWO,
						Ext.EventObject.ONE,
						Ext.EventObject.TWO
					],
					scope:this,
					stopEvent:false
				}
			]
		});
		sw.Promed.swRegistrationJournalSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	openLisRequestEditWindow:function (action) {

		var params = new Object();

		if (action != 'add' && action != 'edit' && action != 'view') {
			return false;
		}

		if (action == 'add' && getWnd('swPersonSearchWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swLisRequestEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_vvoda_zayavkiuje_otkryito']);
			return false;
		}

		getWnd('swPersonSearchWindow').show({
			onClose:Ext.emptyFn,
			onSelect:function (person_data) {
				var form_params = new Object();

				Ext.apply(form_params, params);

				form_params.onHide = function () {
					getWnd('swPersonSearchWindow').formParams = new Object();
					getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
				};

				form_params.Person_Birthday = person_data.Person_Birthday;
				form_params.Person_Firname = person_data.Person_Firname;
				form_params.Person_id = person_data.Person_id;
				form_params.Person_Secname = person_data.Person_Secname;
				form_params.Person_Surname = person_data.Person_Surname;
				//form_params.PersonEvn_id = person_data.PersonEvn_id;
				form_params.Server_id = person_data.Server_id;

				getWnd('swLisRequestEditWindow').show(form_params);
			},
			//personFirname: this.findById('EvnUslugaParSearchFilterForm').getForm().findField('Person_Firname').getValue(),
			//personSecname: this.findById('EvnUslugaParSearchFilterForm').getForm().findField('Person_Secname').getValue(),
			//personSurname: this.findById('EvnUslugaParSearchFilterForm').getForm().findField('Person_Surname').getValue(),
			searchMode:'all',
			searchWindowOpenMode:'LisRequest'
		});

		return true;

	}
});