/**
* swZNOSuspectRegistryWindow - окно регистра пациентов с подозрением на ЗНО
* 
* 
*
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @package      MorbusReab
* @author       Артамонов И.Г.
* @version      18.10.2018
* @comment      Префикс для id компонентов ORW (ReabRegistryWindow)
*
*/

sw.Promed.swZNOSuspectRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['ZNOSuspectRegistry'],
	width: 800,
	height: 550,
	codeRefresh: true,
	objectName: 'swZNOSuspectRegistryWindow',
	id: 'swZNOSuspectRegistryWindow',
	objectSrc: '/jscore/Forms/OnkoCtrl/swZNOSuspectRegistryWindow.js',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	getButtonSearch: function () {
		return Ext.getCmp('ZNOSuspect_SearchButton');
	},
	
	doReset: function () {

		var base_form = this.findById('ZNOSuspectFilterForm').getForm();
		base_form.reset();
		this.ZNOSuspectRegistrySearchFrame.ViewActions.open_emk.setDisabled(true); //электронная карта
		this.ZNOSuspectRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.ZNOSuspectRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.ZNOSuspectRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false); // Обновление
		//this.ZNOSuspectRegistrySearchFrame.ViewActions.action_edit.setDisabled(true); // Переход на форму редактирования

		this.ZNOSuspectRegistrySearchFrame.getGrid().getStore().removeAll(); // Обнуление GRIDa

		if (this.userMedStaffFact.ARMType == 'smo')
		{
			base_form.findField('OrgSmo_id').setValue(base_form.findField('OrgSmo_id').getStore().getAt(base_form.findField('OrgSmo_id').getStore().find('Org_id',this.userMedStaffFact.Org_id)).id);
		}
		
	},
	
	doSearch: function (params) {

		if (typeof params != 'object') {
			params = {};
		}

		var base_form = this.findById('ZNOSuspectFilterForm').getForm();

		//console.log('ZNOSuspectFilterForm=', this.findById('ZNOSuspectFilterForm').isEmpty());
//		if (this.userMedStaffFact.ARMType == 'smo')
//		{
//			base_form.findField('OrgSmo_id').setDisabled(false);
//		}
		
		if (!params.firstLoad && this.findById('ZNOSuspectFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function () {
			});
			return false;
		}
//		if (this.userMedStaffFact.ARMType == 'smo')
//		{
//			base_form.findField('OrgSmo_id').setDisabled(true);
//		}
		
		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		//this.ZNOSuspectRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		//Загрузка GRIDa
		var grid = this.ZNOSuspectRegistrySearchFrame.getGrid();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();
		var post = getAllFormFieldValues(this.findById('ZNOSuspectFilterForm'));
		if(params.firstLoad)
		{
			post.PersonRegisterType_id = 1;
		};
		
		
		post.limit = 100;
		post.start = 0;
		//console.log('isSuperAdmin=', isSuperAdmin());  
		if(isSuperAdmin() || isUserGroup('OnkoRegistry') || this.userMedStaffFact.ARMType == "spec_mz" || this.userMedStaffFact.ARMType == "smo" )
		{
			console.log('ARMType=', this.userMedStaffFact.ARMType);  
			post.ZnoViewLpu_id = -1;
		}
		else{
			post.ZnoViewLpu_id = getGlobalOptions().lpu_id;	
			//post.ZnoViewLpu_id = -1;
		}
		
		if (base_form.isValid()) {
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function (records, options, success) {
					//this.ZNOSuspectRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
					//console.log('records=', records);  
					loadMask.hide();
				},
				params: post
			});
		}

	},
	
	getRecordsCount: function () {
		var base_form = this.getFilterForm().getForm();

		if (!base_form.isValid()) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());

		if (post.PersonPeriodicType_id == null) {
			post.PersonPeriodicType_id = 1;
		}
		
		if(isSuperAdmin() || isUserGroup('OnkoRegistry') || this.userMedStaffFact.ARMType == "spec_mz" )
		{
			post.ZnoViewLpu_id = -1;
		}
		else{
			post.ZnoViewLpu_id = getGlobalOptions().lpu_id;	
			//post.ZnoViewLpu_id = -1;
		}
		
		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.Records_Count != undefined) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					} else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	
	//Страничная форма
	openPersonWindow: function () {
		if (getWnd('swZnoSuspect_personWindow').isVisible()) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно просмотра уже открыто'));
			return false;
		}
		
		var grid = this.ZNOSuspectRegistrySearchFrame.getGrid();
		var rec = grid.getSelectionModel().getSelected();
		Ext.getCmp('ZNOSuspectRegistry').ViewActions.action_view.setDisabled(true);
		var params =
				{
					Person_id: rec.data.Person_id,
					Server_id: rec.data.Server_id,
					PersonEvn_id: rec.data.PersonEvn_id,
					userMedStaffFact: this.userMedStaffFact,
					MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
					LpuSection_id: this.userMedStaffFact.LpuSection_id,
				}
		console.log('params=', params);  
		getWnd('swZnoSuspect_personWindow').show(params);
	},
	
	initComponent: function () {
		var win = this;

		this.ZNOSuspectRegistrySearchFrame = new sw.Promed.ViewFrame(
				{
					actions: [
						{name: 'action_add', hidden: true},
						//  {name:'action_view',  handler: function() { this.openViewWindow('view'); }.createDelegate(this)},
						 {name:'action_view',  handler: function() { this.openPersonWindow(); }.createDelegate(this)},
						{name: 'action_delete', hidden: true, text: 'Удалить', disabled: true, },
//						{name: 'action_edit', handler: function () {
//								this.openPersonWindow('edit');
//							}.createDelegate(this)}, // Выход на списочную форму

						//{name: 'action_delete',  hidden: true, handler: this.deletePersonRegister.createDelegate(this)  },
						{name: 'action_edit',hidden: true},
						{name: 'action_refresh'},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					dataUrl: C_SEARCH, /* /?c=Search&m=searchData */
					id: 'ZNOSuspectRegistry',
					object: 'ZNOSuspectRegistry',
					pageSize: 100,
					paging: true,
					region: 'center',
					root: 'data',
					stringfields: [
						{name: 'ZNORout_id', type: 'int', header: 'ID', key: true},
						{name: 'Person_id', type: 'int', hidden: true},
						{name: 'Server_id', type: 'int', hidden: true},
						{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 250}, //1
						{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 250},
						{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 250},
						{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 70,align: 'center'},
						{name: 'Lpu_Nick', type: 'string', header: lang['mo_prikrepleniya'], width: 150},
						{name: 'Lpu_iid', type: 'int', hidden: true},
						{name: 'Finish', type: 'int', hidden: true},
						{name: 'Diag_CodeFirst', type: 'string', header: 'Диагноз подозрения <br>на ЗНО', width: 120,align: 'center'},
						{name: 'Terms', type: 'string', header: 'Несоблюдение <br>сроков', width: 100,align: 'center' },
						{name: 'Biopsy', type: 'string', header: 'Направление <br>на биопсию', width: 100,align: 'center'},
						{name: 'Diag_CodeFinish', type: 'string', header: lang['ustanovlennyiy_br_diagnoz'], width: 120,align: 'center'},
						{name: 'Registry_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr1'], width: 100,align: 'center'},
						{name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra1'], width: 100,align: 'center'}
						
						
						//{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, hidden: true},
						// {name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', hidden: true, header: lang['data_isklyucheniya_iz_registra'], width: 150}, 
						//{name: 'PMUser_Name', type: 'string', header: 'Кем создана анкета', hidden: true},
						//{name: 'Diag', type: 'int', id: 'autoexpand', header: ''}
					],
					toolbar: true,
					totalProperty: 'totalCount',
					focusOnFirstLoad: false,
					
					onBeforeLoadData: function () {
						var post = getAllFormFieldValues(this.findById('ZNOSuspectFilterForm'));
						if (isSuperAdmin() || isUserGroup('OnkoRegistry') || this.userMedStaffFact.ARMType == "spec_mz" || this.userMedStaffFact.ARMType == "smo") 
						{
							post.ZnoViewLpu_id = -1;
						} else {
							post.ZnoViewLpu_id = getGlobalOptions().lpu_id;
						}
						
						this.getButtonSearch().disable();
					}.createDelegate(this),

					onLoadData: function (sm, index, record) {
						Ext.getCmp('swZNOSuspectRegistryWindow').getColorSell();
						Ext.getCmp('ZNOSuspectRegistry').ViewActions.action_view.setDisabled(true);
						this.getButtonSearch().enable();
					}.createDelegate(this),
					
					onRowSelect: function (sm, index, record) {
						
					}
				});

		//Событие выбор записи                
		var ZNOSuspect = this.ZNOSuspectRegistrySearchFrame.getGrid().on(
				'rowclick',
				function () {
					//Здесь и начнем - есть запись-есть активность
					if(Ext.getCmp('ZNOSuspectRegistry').getGrid().getSelectionModel().getSelected().data.ZNORout_id > 0)
					{
						Ext.getCmp('ZNOSuspectRegistry').ViewActions.open_emk.setDisabled(false);
						Ext.getCmp('ZNOSuspectRegistry').ViewActions.action_view.setDisabled(false); // Переход на страничную форму
					}
					
					//Ext.getCmp('ZNOSuspectRegistry').ViewActions.ReabObjectButton.setDisabled(false);
					//Ext.getCmp('ZNOSuspectRegistry').ViewActions.action_edit.setDisabled(false); // 
				}
		);
		
		this.ZNOSuspectRegistrySearchFrame.getGrid().on(
				'rowdblclick',
				function () {
					//Здесь и начнем - есть запись-есть активность
					if(Ext.getCmp('ZNOSuspectRegistry').getGrid().getSelectionModel().getSelected().data.ZNORout_id > 0)
					{
						 Ext.getCmp('swZNOSuspectRegistryWindow').openPersonWindow();
					}
				}
		);


		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					tabIndex: TABINDEX_ORW + 120,
					id: 'ZNOSuspect_SearchButton',
					text: BTN_FRMSEARCH
				},
				{
					handler: function () {
						this.doReset();
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					tabIndex: TABINDEX_ORW + 121,
					text: BTN_FRMRESET
				},
				{
					handler: function () {
						this.getRecordsCount();
					}.createDelegate(this),
					// iconCls: 'resetsearch16',
					tabIndex: TABINDEX_ORW + 123,
					text: BTN_FRMCOUNT
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						this.buttons[this.buttons.length - 2].focus();
					}.createDelegate(this),
					onTabAction: function () {
						alert('Что-то с панелью');
						//this.findById('ORW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ORW_SearchFilterTabbar').getActiveTab());
						this.findById('ZNOSuspect_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ZNOSuspect_SearchFilterTabbar').getActiveTab());
					}.createDelegate(this),
					tabIndex: TABINDEX_ORW + 124,
					text: BTN_FRMCLOSE
				}
			],
			getFilterForm: function () {
				if (this.filterForm == undefined) 
				{
					this.filterForm = this.findById('ZNOSuspectFilterForm');
				}
				return this.filterForm;
			},
			
			items: [
				getBaseSearchFiltersFrame({
					isDisplayPersonRegisterRecordTypeField: true,
					allowPersonPeriodicSelect: true,
					id: 'ZNOSuspectFilterForm',
					labelWidth: 130,
					ownerWindow: this,
					searchFormType: 'ZNOSuspectRegistry',
					tabIndexBase: TABINDEX_ORW,
					tabPanelHeight: 225,
					tabPanelId: 'ZNOSuspect_SearchFilterTabbar',
					tabs: [
						{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 220,
							layout: 'form',
							listeners: {
								'activate': function () {
									this.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
								}.createDelegate(this)
							},
							
							title: lang['6_registr'],
							items: [
								{
									xtype: 'swpersonregistertypecombo',
									hiddenName: 'PersonRegisterType_id',
									labelWidth: 220,
									width: 200
								},
								{
									fieldLabel: lang['data_vklyucheniya_v_registr'],
									labelWidth: 220,
									name: 'PersonRegister_setDate_Range',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 170,
									xtype: 'daterangefield'
								},
								{
									fieldLabel: lang['data_isklyucheniya_iz_registra'],
									name: 'PersonRegister_disDate_Range',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 170,
									xtype: 'daterangefield'
								},
								{
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											items: [
												{
													fieldLabel: 'Тип наблюдения',
													mode: 'local',
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [
															{name: 'ObservType_id', type: 'int'},
															{name: 'ObservType_name', type: 'string'}
														],
														data:
																[
																	[1, 'С диагнозом ЗНО'],
																	[2, 'С диагнозом ДНО'],
																	[3, 'С хроническими заболеваниями'],
																	[4, 'Диагноз отсутствует']
																],
														key: 'ObservType_id',
													}),
													editable: false,
													triggerAction: 'all',
													hiddenName: 'ObservType_id',
													displayField: 'ObservType_name',
													valueField: 'ObservType_id',
													width: 200,
													xtype: 'combo',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
															'{ObservType_name} ' + '&nbsp;' +
															'</div></tpl>',
													listeners:
															{
																'select': function (combo, record, index)
																{
																	//console.log('record = ', record);
																	if (record.data.ObservType_id == '')
																	{
//																		Ext.getCmp('Reabquest_yn').setValue('');
//																		Ext.getCmp('Reabquest_yn').setDisabled(true);
//																		Ext.getCmp('ReabScale_yn').setValue('');
//																		Ext.getCmp('ReabScale_yn').setDisabled(true);
//																		Ext.getCmp('StageType').setValue('');
//																		Ext.getCmp('StageType').setDisabled(true);
																	} else
																	{
//																		Ext.getCmp('Reabquest_yn').setDisabled(false);
//																		Ext.getCmp('ReabScale_yn').setDisabled(false);
//																		Ext.getCmp('StageType').setDisabled(false);
																	}
																}
															}
												}
											]
										},
										{
											layout: 'form',
											border: false,
											labelWidth: 130,
											items: [
												{
													fieldLabel: 'Нарушение сроков',
													mode: 'local',
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [
															{name: 'DeadlineZNO_id', type: 'int'},
															{name: 'DeadlineZNO_name', type: 'string'}
														],
														data:
																[
																	[1, 'Да'],
																	[2, 'Нет']
																],
														key: 'DeadlineZNO_id',
													}),
													editable: false,
													triggerAction: 'all',
													hiddenName: 'DeadlineZNO_id',
													displayField: 'DeadlineZNO_name',
													valueField: 'DeadlineZNO_id',
													width: 50,
													listWidth: 50,
													xtype: 'combo',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
															'&nbsp&nbsp&nbsp;'+'{DeadlineZNO_name} ' + '&nbsp;' +
															'</div></tpl>',
													listeners:
															{
																'select': function (combo, record, index)
																{
																	
																}
															}
												}
											]
										},
										{
											layout: 'form',
											border: false,
											labelWidth: 160,
											items: [
												{
													fieldLabel: 'Направление на биопсию',
													mode: 'local',
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														fields: [
															{name: 'BiopsyRefZNO_id', type: 'int'},
															{name: 'BiopsyRefZNO_name', type: 'string'}
														],
														data:
																[
																	[1, 'Да'],
																	[2, 'Нет']
																],
														key: 'BiopsyRefZNO_id',
													}),
													editable: false,
													triggerAction: 'all',
													hiddenName: 'BiopsyRefZNO_id',
													displayField: 'BiopsyRefZNO_name',
													valueField: 'BiopsyRefZNO_id',
													width: 50,
													listWidth: 50,
													xtype: 'combo',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
															'&nbsp&nbsp&nbsp;'+'{BiopsyRefZNO_name} ' + '&nbsp;' +
															'</div></tpl>',
													listeners:
															{
																'select': function (combo, record, index)
																{
																	
																}
															}
												}
											]
										},
										{
											layout: 'form',
											border: false,
											items: [
												/*
												 {
												 xtype: 'combo',
												 displayField: 'pmUser_FioL',
												 editable: true,
												 enableKeyEvents: true,
												 fieldLabel: 'Пользователь',
												 hiddenName: 'pmUser_docupdID',
												 id: 'pmUser_docupd',
												 disabled: true,
												 minChars: 1,
												 width: 300,
												 name : "pmUser_docupdID",
												 minLength: 1,
												 mode: 'local',
												 resizable: true,
												 selectOnFocus: true,
												 store: new Ext.data.Store({
												 autoLoad: false,
												 reader: new Ext.data.JsonReader({
												 id: 'pmUser_id'
												 }, [
												 {name: 'pmUser_id', mapping: 'pmUser_id'},
												 {name: 'pmUser_FioL', mapping: 'pmUser_FioL'},
												 {name: 'pmUser_Fio', mapping: 'pmUser_Fio'},
												 {name: 'pmUser_Login', mapping: 'pmUser_Login'},
												 {name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode'}
												 ]),
												 sortInfo: {
												 direction: 'ASC',
												 field: 'pmUser_Fio'
												 },
												 url: '/?c=BSK_Register_User&m=getCurrentOrgUsersList'
												 }),
												 triggerAction: 'all',
												 valueField: 'pmUser_id',  
												 listeners: {
												 change: function() {
												 
												 },
												 keydown: function(inp, e) {
												 if ( e.getKey() == e.END ) {
												 this.inKeyMode = true;
												 this.select(this.getStore().getCount() - 1);
												 }
												 
												 if ( e.getKey() == e.HOME ) {
												 this.inKeyMode = true;
												 this.select(0);
												 }
												 
												 if ( e.getKey() == e.PAGE_UP ) {
												 this.inKeyMode = true;
												 var ct = this.getStore().getCount();
												 
												 if ( ct > 0 ) {
												 if ( this.selectedIndex == -1 ) {
												 this.select(0);
												 }
												 else if ( this.selectedIndex != 0 ) {
												 if ( this.selectedIndex - 10 >= 0 )
												 this.select(this.selectedIndex - 10);
												 else
												 this.select(0);
												 }
												 }
												 }
												 
												 if ( e.getKey() == e.PAGE_DOWN ) {
												 if ( !this.isExpanded() ) {
												 this.onTriggerClick();
												 }
												 else {
												 this.inKeyMode = true;
												 var ct = this.getStore().getCount();
												 
												 if ( ct > 0 ) {
												 if ( this.selectedIndex == -1 ) {
												 this.select(0);
												 }
												 else if ( this.selectedIndex != ct - 1 ) {
												 if ( this.selectedIndex + 10 < ct - 1 )
												 this.select(this.selectedIndex + 10);
												 else
												 this.select(ct - 1);
												 }
												 }
												 }
												 }
												 
												 if ( e.altKey || e.ctrlKey || e.shiftKey )
												 return true;
												 
												 if ( e.getKey() == e.DELETE||e.getKey() == e.BACKSPACE) {
												 inp.setValue('');
												 inp.setRawValue("");
												 inp.selectIndex = -1;
												 if ( inp.onClearValue ) {
												 this.onClearValue();
												 }
												 e.stopEvent();
												 return true;
												 }                                                                                
												 },
												 beforequery: function(q) {
												 if ( q.combo.getStore().getCount() == 0 ) {
												 q.combo.getStore().removeAll();
												 q.combo.getStore().load();
												 }                                                                                
												 }
												 }
												 }
												 */
											]

										}

									]
								}
							]
						}
					]
				}),
				this.ZNOSuspectRegistrySearchFrame
			]
		});

		sw.Promed.swZNOSuspectRegistryWindow.superclass.initComponent.apply(this, arguments);

	},

	listeners: {
		/*
		 'beforeShow': function(win) {
		 if (String(getGlobalOptions().groups).indexOf('BskRegistry', 0) < 0 && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
		 {
		 sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «'+ win.title +'»');
		 return false;
		 }
		 },
		 */
		'hide': function (win) {
			win.doReset();
		},
		'maximize': function (win) {
			win.findById('ZNOSuspectFilterForm').doLayout();
		},
		'restore': function (win) {
			win.findById('ZNOSuspectFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('ZNOSuspect_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('ZNOSuspectFilterForm').setWidth(nW - 5);
		}
	},

	show: function () {
		sw.Promed.swZNOSuspectRegistryWindow.superclass.show.apply(this, arguments);

		this.ZNOSuspectRegistrySearchFrame.addActions({
			name: 'open_emk',
			text: lang['otkryit_emk'],
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function () {
				this.emkOpen();
			}.createDelegate(this)
		});

		var base_form = this.findById('ZNOSuspectFilterForm').getForm();
//
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} 
		else 
		{
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			} else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function (data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		};
		console.log('userMedStaffFact=', this.userMedStaffFact);
		
		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		
		//Убираем лишнее спанели поиска
		//Панель  -Пользователь
		Ext.getCmp('ZNOSuspect_SearchFilterTabbar').hideTabStripItem('ZNOSuspect_SearchFilterTabbarfilterUser');
		// Тип поиска человека и записи регистра
		Ext.getCmp('swZNOSuspectRegistryWindow').findById('ZNOSuspectFilterForm').items.items[0].items.items[0].hide();
		//base_form.findField('PersonPeriodicType_id').getEl().parent().parent().parent().setVisible(false);
		//base_form.findField('PersonRegisterRecordType_id').getEl().parent().parent().parent().setVisible(false);


		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);
		if (this.userMedStaffFact.ARMType == 'smo')
		{
//			var index = base_form.findField('OrgSmo_id').getStore().find('Org_id',this.userMedStaffFact.Org_id);
//			var rec = base_form.findField('OrgSmo_id').getStore().getAt(base_form.findField('OrgSmo_id').getStore().find('Org_id',this.userMedStaffFact.Org_id)); 
			base_form.findField('OrgSmo_id').setValue(base_form.findField('OrgSmo_id').getStore().getAt(base_form.findField('OrgSmo_id').getStore().find('Org_id',this.userMedStaffFact.Org_id)).id);
			base_form.findField('OrgSmo_id').setDisabled(true);
			base_form.findField('Person_NoOrgSMO').setDisabled(true);
		}
		else
		{
			base_form.findField('OrgSmo_id').setDisabled(false);
			base_form.findField('Person_NoOrgSMO').setDisabled(false);
		}
		
		
		this.doSearch({firstLoad: true});
	},
	//Открытие электронной карты
	emkOpen: function ()
	{
		if (getWnd('swPersonEmkWindow').isVisible()) {
							sw.swMsg.alert(langs('Сообщение'), lang['emk'] + ' уже открыта'); 
							return false;
						}
		var grid = this.ZNOSuspectRegistrySearchFrame.getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			callback: function ()
			{
				//
			}.createDelegate(this)
		});
	},
	//Формирование отображения цветных символов
	getColorSell: function ()
	{
		var form = Ext.getCmp('ZNOSuspectRegistry');
		var nRecords = form.ViewGridPanel.getStore().data.items.length;
		if (nRecords > 0)
		{
			//Формируем значения полей Terms,Biopsy,Diag_CodeFinish
			for (var r = 0; r <= nRecords - 1; r++) {
				form.getGrid().getSelectionModel().selectRow(r);
				var record = form.getGrid().getSelectionModel().getSelected();
				if (record.get('Terms') != '')
				{
					if (record.get('Terms') == '!')
					{
						record.set('Terms', "<span style='color:red;font-size:14px; font-width:bold'>" + record.get('Terms') + "</span>");
					}
					if (record.get('Terms') == 'V')
					{
						record.set('Terms', '<img src="/img/icons/tick16.png" width="12" height="12"/>');
					}
				}
				if (record.get('Biopsy') != '')
				{
					//console.log('Biopsy не пустая');
					if (record.get('Biopsy') == '!')
					{
						record.set('Biopsy', "<span style='color:red;font-size:14px; font-width:bold'>" + record.get('Biopsy') + "</span>");
					}
					if (record.get('Biopsy') == 'V')
					{
						//record.set('Biopsy', "<span style='color:green;font-size:14px; font-width:bold'>" + record.get('Biopsy') + "</span>");
						record.set('Biopsy', '<img src="/img/icons/tick16.png" width="12" height="12"/>');
						
					}
				}
				if (record.get('Diag_CodeFinish') == '!')
				{
					record.set('Diag_CodeFinish', "<span style='color:red;font-size:14px; font-width:bold'>" + record.get('Diag_CodeFinish') + "</span>");
				}
				record.commit();
			}
			form.getGrid().getSelectionModel().deselectRow(nRecords-1);
		}
		return;
	}

});
