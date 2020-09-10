/**
* AdminVIPPersonWindow - окно регистра VIP пациентов (для ограничения видимости ЭМК)
* 
* 
*
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @package      MorbusReab
* @author       Артамонов И.Г.
* @version      21.02.2019
* @comment      Префикс для id компонентов ORW (AdminVIPPersonWindow)
*
*/
sw.Promed.AdminVIPPersonWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['VIPPerson'],
	width: 800,
	height: 550,
	codeRefresh: true,
	objectName: 'AdminVIPPersonWindow',
	id: 'AdminVIPPersonWindow',
	objectSrc: '/jscore/Forms/Admin/AdminVIPPersonWindow.js',
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
		return Ext.getCmp('AdminVIPPerson_SearchButton');
	},
	
	doReset: function () {

		var base_form = this.findById('AdminVIPPersonFilterForm').getForm();
		base_form.reset();
		base_form.findField('PersonRegisterType_id').setValue(null);
		//this.AdminVIPPersonSearchFrame.ViewActions.open_emk.setDisabled(true); //электронная карта
		this.AdminVIPPersonSearchFrame.ViewActions.action_view.setDisabled(true);
		this.AdminVIPPersonSearchFrame.ViewActions.action_delete.setDisabled(true);
		this.AdminVIPPersonSearchFrame.ViewActions.action_refresh.setDisabled(false); // Обновление
		//this.ZNOSuspectRegistrySearchFrame.ViewActions.action_edit.setDisabled(true); // Переход на форму редактирования

		this.AdminVIPPersonSearchFrame.getGrid().getStore().removeAll(); // Обнуление GRIDa

	},
	
	doSearch: function (params) {
		
		var flag = true;
		if (typeof params != 'object') {
			params = {};
			flag = false;
		}
		console.log('params=',params); 

		var base_form = this.findById('AdminVIPPersonFilterForm').getForm();

		if (  !flag && this.findById('AdminVIPPersonFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function () {
			});
			return false;
		}
		

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

		//Загрузка GRIDa
		var grid = this.AdminVIPPersonSearchFrame.getGrid();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();
		var post = getAllFormFieldValues(this.findById('AdminVIPPersonFilterForm'));
		if(params.firstLoad == 'true')
		{
			post.PersonRegisterType_id = 1;
			console.log('333333='); 
		}
		if(params.firstLoad == false)
		{
			post.PersonRegisterType_id = 2;
			console.log('111111='); 
		}
		post.limit = 100;
		post.start = 0;
		//console.log('isSuperAdmin=', isSuperAdmin());  
		//console.log('isLpuAdmin =', isLpuAdmin());  
		if(isSuperAdmin() )
		{
			post.AdminVIPPersonLpu_id = -1;
		}
		else{
			post.AdminVIPPersonLpu_id = getGlobalOptions().lpu_id;
		}
		
		if (base_form.isValid()) {
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function (records, options, success) {
					
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
		
		if(isSuperAdmin() )
		{
			post.AdminVIPPersonLpu_id = -1;
		}
		else{
			post.AdminVIPPersonLpu_id = getGlobalOptions().lpu_id;	
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
	
	initComponent: function () {
		var win = this;

		this.AdminVIPPersonSearchFrame = new sw.Promed.ViewFrame(
				{
					actions: [
						{name: 'action_add', handler: function () {
								this.doRecords('ins');
							}.createDelegate(this)},
						 {name:'action_view',   hidden: true},
						 // {name:'action_view',  handler: function() { this.openPersonWindow(); }.createDelegate(this)},
						//{name: 'action_delete', hidden: true, text: 'Удалить', disabled: true, },
						{name: 'action_delete', text: 'Исключить', handler: function () {
								this.doRecords('delete');
							}.createDelegate(this)}, // Выход на списочную форму

						//{name: 'action_delete',  hidden: true, handler: this.deletePersonRegister.createDelegate(this)  },
						{name: 'action_edit',hidden: true},
						{name: 'action_refresh',handler: function () {
								this.doSearch();
							}.createDelegate(this)},
						{name: 'action_print',hidden: true}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					dataUrl: C_SEARCH, /* /?c=Search&m=searchData */
					id: 'AdminVIPPerson',
					object: 'AdminVIPPerson',
					pageSize: 100,
					paging: true,
					region: 'center',
					root: 'data',
					stringfields: [
						{name: 'VIPPerson_id', type: 'int', header: 'ID', key: true},
						{name: 'Person_id', type: 'int', hidden: true},
						{name: 'Server_id', type: 'int', hidden: true},
						{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 250}, //1
						{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 250},
						{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 250},
						{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 80,align: 'center'},
						{name: 'Lpu_Nick', type: 'string', header: lang['mo_prikrepleniya'], width: 150,align: 'center'},
						{name: 'Lpu_id', type: 'int', hidden: true},
						{name: 'VIPPerson_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr1'], width: 110,align: 'center'},
						{name: 'VIPPerson_disDate', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra1'], width: 110,align: 'center'},
						{name: 'VIPPerson_deleted', type: 'int', hidden: true},
						{name: 'pmUser_insID', type: 'int', hidden: true},
						{name: 'PMUser_Name', type: 'string', header: lang['polzovatel'], width: 250,id: 'autoexpand'}
					],
					toolbar: true,
					totalProperty: 'totalCount',
					focusOnFirstLoad: false,
					
					onBeforeLoadData: function () {
						var post = getAllFormFieldValues(this.findById('AdminVIPPersonFilterForm'));
						if (isSuperAdmin()) //isUserGroup('OnkoRegistryFullAccess')
						{
							post.AdminVIPPersonLpu_id = -1;
						} else {
							post.AdminVIPPersonLpu_id = getGlobalOptions().lpu_id;
						}
						this.getButtonSearch().disable();
					}.createDelegate(this),

					onLoadData: function (sm, index, record) {
						Ext.getCmp('AdminVIPPerson').ViewActions.action_delete.setDisabled(true);
						this.getButtonSearch().enable();
					}.createDelegate(this),

					onRowSelect: function (sm, index, record) {

					}
				});
		// Расцветка		
//		Ext.getCmp('AdminVIPPerson').getGrid().view = new Ext.grid.GridView({
//			getRowClass: function (row, index) {
//				//alert('Раскраска');
//				var cls = '';
//				if (row.get('VIPPerson_deleted') == 2) {//  Выбранное значение шкалы
//					// alert('Раскраска');
//					//cls = cls + ' x-grid-rowblue ';
//					//cls = cls + ' x-grid-rowbold ';
//					cls = cls + 'x-grid-rowdeleted';
//				}
//				return cls;
//			}
//		});
		

		//Событие выбор записи                
		this.AdminVIPPersonSearchFrame.getGrid().on(
				'rowclick',
				function () {
					//Здесь и начнем - есть запись-есть активность
					if (Ext.getCmp('AdminVIPPerson').getGrid().getSelectionModel().getSelected().get('VIPPerson_disDate') != "")
					{
						Ext.getCmp('AdminVIPPerson').ViewActions.action_delete.setDisabled(true);
					}
					else
					{
						Ext.getCmp('AdminVIPPerson').ViewActions.action_delete.setDisabled(false);
					}
					
				}
		);
		
//		this.AdminVIPPersonSearchFrame.getGrid().on(
//				'rowdblclick',
//				function () {
//					//Здесь и начнем - есть запись-есть активность
//					if(Ext.getCmp('AdminVIPPerson').getGrid().getSelectionModel().getSelected().data.ZNORout_id > 0)
//					{
//						//Ext.getCmp('swZNOSuspectRegistryWindow').openPersonWindow();
//						alert("Что-то делать");
//					}
//				}
//		);


		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					tabIndex: TABINDEX_ORW + 120,
					id: 'AdminVIPPerson_SearchButton',
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
						this.findById('AdminVIPPerson_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('AdminVIPPerson_SearchFilterTabbar').getActiveTab());
					}.createDelegate(this),
					tabIndex: TABINDEX_ORW + 124,
					text: BTN_FRMCLOSE
				}
			],
			getFilterForm: function () {
				if (this.filterForm == undefined) 
				{
					this.filterForm = this.findById('AdminVIPPersonFilterForm');
				}
				return this.filterForm;
			},
			
			items: [
				getBaseSearchFiltersFrame({
					isDisplayPersonRegisterRecordTypeField: true,
					allowPersonPeriodicSelect: true,
					id: 'AdminVIPPersonFilterForm',
					labelWidth: 130,
					ownerWindow: this,
					searchFormType: 'AdminVIPPerson',
					tabIndexBase: TABINDEX_ORW,
					tabPanelHeight: 225,
					tabPanelId: 'AdminVIPPerson_SearchFilterTabbar',
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
							
							title: lang['registr'],
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
								}
							]
						}
					]
				}),
				this.AdminVIPPersonSearchFrame
			]
		});

		sw.Promed.AdminVIPPersonWindow.superclass.initComponent.apply(this, arguments);

	},

	listeners: {
		
		'hide': function (win) {
			win.doReset();
		},
		'maximize': function (win) {
			win.findById('AdminVIPPersonFilterForm').doLayout();
		},
		'restore': function (win) {
			win.findById('AdminVIPPersonFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('AdminVIPPerson_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('AdminVIPPersonFilterForm').setWidth(nW - 5);
		}
	},

	show: function () {
		sw.Promed.AdminVIPPersonWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('AdminVIPPersonFilterForm').getForm();
//
		this.restore();
		this.center();
		this.maximize();
		this.doReset();

//		if (arguments[0].userMedStaffFact)
//		{
//			this.userMedStaffFact = arguments[0].userMedStaffFact;
//		} else {
//			if (sw.Promed.MedStaffFactByUser.last)
//			{
//				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
//			} else
//			{
//				sw.Promed.MedStaffFactByUser.selectARM({
//					ARMType: arguments[0].ARMType,
//					onSelect: function (data) {
//						this.userMedStaffFact = data;
//					}.createDelegate(this)
//				});
//			}
//		};
		//Убираем лишнее панели поиска
		Ext.getCmp('AdminVIPPerson_SearchFilterTabbar').hideTabStripItem('AdminVIPPerson_SearchFilterTabbarfilterLgota');
		Ext.getCmp('AdminVIPPerson_SearchFilterTabbar').hideTabStripItem('AdminVIPPerson_SearchFilterTabbarfilterAdr');
		Ext.getCmp('AdminVIPPerson_SearchFilterTabbar').hideTabStripItem('AdminVIPPerson_SearchFilterTabbarfilterPrikrep');
		Ext.getCmp('AdminVIPPerson_SearchFilterTabbar').hideTabStripItem('AdminVIPPerson_SearchFilterTabbarfilterPatientDop');
		//Панель  -Пользователь
		Ext.getCmp('AdminVIPPerson_SearchFilterTabbar').items.items[6].setTitle("Пользователь");
		// Тип поиска человека и записи регистра
		Ext.getCmp('AdminVIPPersonWindow').findById('AdminVIPPersonFilterForm').items.items[0].items.items[0].hide();
		//base_form.findField('PersonPeriodicType_id').getEl().parent().parent().parent().setVisible(false);
		//base_form.findField('PersonRegisterRecordType_id').getEl().parent().parent().parent().setVisible(false);

		this.doLayout();
		
		//base_form.findField('PersonRegisterType_id').setValue(1);
		this.doSearch({firstLoad: 'true'});
		//this.doSearch();
	},
	//Работа с таблицей регистра VIP
	doRecords: function (oper) {
		
		console.log('oper=', oper);
		switch (oper) {
			case 'ins':
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(lang['registr_reability'], 'Окно выбора пациента уже открыто');
				} 
				else 
				{
					getWnd('swPersonSearchWindow').show({
						onSelect: function (person_data) {
							this.hide();
							//console.log('person_data = ',person_data);
							var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет выполнение..."});
							loadMask.show();
							Ext.Ajax.request({
								url: '?c=AdminVIPPerson_User&m=doRecords',
								params: {
									Person_id: person_data.Person_id,
									Operation: oper,
									Lpu_id: getGlobalOptions().lpu_id
									//VIPPerson_id: null
								},
								callback: function (options, success, response)
								{
									loadMask.hide(); // Обязательно сделать
									console.log('success=',success); 
									console.log('response=',response); 

									if (success == true)
									{
										var response_obj = Ext.util.JSON.decode(response.responseText);
										console.log('response_obj=', response_obj);

										if (response_obj.success == "true")
										{
											console.log('232323232232='); 
											Ext.getCmp('AdminVIPPersonWindow').doSearch({firstLoad: 'rtrt'});
										}
									} else {
										//form.getEl().mask().hide();
										sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
									}

								}
							});
						}
					})
				}
				break;
			case 'delete':
				if (this.findById('AdminVIPPerson').getGrid().getSelectionModel().getCount() == 0)
				{
					sw.swMsg.alert(lang['soobschenie'], 'Выберите запись для удаления из регистра!');
				} else
				{

					if (this.findById('AdminVIPPerson').getGrid().getSelectionModel().getSelected().get('Lpu_id') != getGlobalOptions().lpu_id)
					{
						sw.swMsg.alert(lang['soobschenie'], 'У Вас нет прав на изменение записи в данной МО!');
						return;
					};

					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет удаление пациета из регистра..."});

					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'yes')
							{
								console.log('buttonId=', buttonId);
								

								loadMask.show();
								Ext.Ajax.request({
									url: '?c=AdminVIPPerson_User&m=doRecords',
									params: {
										Person_id: this.findById('AdminVIPPerson').getGrid().getSelectionModel().getSelected().get('Person_id'),
										Operation: oper,
										Lpu_id: this.findById('AdminVIPPerson').getGrid().getSelectionModel().getSelected().get('Lpu_id'),
										VIPPerson_id: this.findById('AdminVIPPerson').getGrid().getSelectionModel().getSelected().get('VIPPerson_id')
									},
									callback: function (options, success, response)
									{
										loadMask.hide(); // Обязательно сделать
										console.log('success=', success);
										console.log('response=', response);

										if (success == true)
										{
											var response_obj = Ext.util.JSON.decode(response.responseText);
											console.log('response_obj=', response_obj);
											console.log('response_obj.success=', response_obj.success);
											if (response_obj.success == "true")
											{
												//Ext.getCmp('AdminVIPPersonWindow').doSearch({firstLoad: false});
												console.log('55555555555555555=');
												Ext.getCmp('AdminVIPPersonWindow').doSearch({firstLoad: 'rtrt'});
												//console.log('Конец=');
											}
										} else {
											//form.getEl().mask().hide();
											sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
										}

									}
								});

							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Вы хотите удалить пациента из регистра?'),
						title: langs('Подтверждение')
					});
				}
				break;
		}

	}

});

