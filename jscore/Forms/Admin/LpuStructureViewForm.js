/**
 * Структура МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package	  All
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @version	  15.09.2009
 */
/*NO PARSE JSON*/

sw.Promed.swLpuStructureViewForm = Ext.extend(sw.Promed.BaseForm,
	{
		id: 'swLpuStructureViewForm',
		addShowScheduleEditActions: function() {
			if ( !this.swStaffPanel.getAction('action_schedule') && !(getGlobalOptions().region && getGlobalOptions().region.nick=='saratov') )
			{
				this.swStaffPanel.addActions({
					iconCls: 'eph-record16',
					name:'action_schedule',
					hidden: getWnd('swWorkPlaceMZSpecWindow').isVisible(),
					text:langs('Рaсписание'),
					handler: function()
					{
						if ( this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var MedStaffFact_id = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().get('MedStaffFact_id');
							var MedPersonal_FIO = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().get('MedPersonal_FIO');
							var LpuSection_Name = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().get('LpuSection_Name');
							getWnd('swTTGScheduleEditWindow').show({
								MedStaffFact_id : MedStaffFact_id,
								MedPersonal_FIO : MedPersonal_FIO,
								LpuSection_Name : LpuSection_Name,
								readOnly: (this.action=='view')
							});
						}
					}.createDelegate(this)
				});
			}
		},
		addStaffActions: function() {
			var win = this;
			if ( getRegionNick() == 'kz' && !win.swStaffPanel.getAction('action_sur') ) {
				win.swStaffPanel.openSURPersonalWorkListWindow = function(action) {
					var grid = win.swStaffPanel.getGrid();
					var record = grid.getSelectionModel().getSelected();

					if (!record || Ext.isEmpty(record.get('MedStaffFact_id'))) return false;

					log('record', record);

					getWnd('swSURPersonalWorkListWindow').show({
						action: action,
						Lpu_id: win.findById('lpu-structure-frame').Lpu_id,
						MedStaffFact_id: record.get('MedStaffFact_id'),
						filterParams: {
							LastName: record.get('PersonSurName_SurName'),
							FirstName: record.get('PersonFirName_FirName'),
							SecondName: record.get('PersonSecName_SecName')
						},
						callback: function() {win.swStaffPanel.getAction('action_refresh').execute()}
					});

					return true;
				};
				win.swStaffPanel.openSURPersonalWorkViewWindow = function() {
					var grid = win.swStaffPanel.getGrid();
					var record = grid.getSelectionModel().getSelected();

					if (!record || Ext.isEmpty(record.get('SURWorkPlace_id'))) return false;

					getWnd('swSURPersonalWorkViewWindow').show({ID: record.get('SURWorkPlace_id')});

					return true;
				};
				win.swStaffPanel.deleteSUPPersonalHistoryWP = function() {
					var grid = win.swStaffPanel.getGrid();
					var record = grid.getSelectionModel().getSelected();

					if (!record || Ext.isEmpty(record.get('MedStaffFact_id'))) return false;

					sw.swMsg.show({
						buttons: Ext.Msg.OKCANCEL,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'ok') {
								var params = {MedStaffFact_id: record.get('MedStaffFact_id')};

								Ext.Ajax.request({
									callback: function(opt, scs, response) {
										var response_obj = Ext.util.JSON.decode(response.responseText);

										if (!response_obj.Error_Msg) {
											win.swStaffPanel.getAction('action_refresh').execute();
										}
									}.createDelegate(this),
									params: params,
									url: '/?c=ServiceSUR&m=deletePersonalHistoryWP'
								});
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Связь выбранного места работы и записи в СУР будет удалена. Продолжить?'),
						title: langs('Подтверждение')
					});
				};

				var surActions = {
					edit: new Ext.Action({
						text: 'Изменить',
						iconCls: 'edit16',
						handler: function() {
							win.swStaffPanel.openSURPersonalWorkListWindow('select');
						}
					}),
					view: new Ext.Action({
						text: 'Просмотреть',
						iconCls: 'view16',
						handler: function() {
							win.swStaffPanel.openSURPersonalWorkViewWindow();
						}
					}),
					delete: new Ext.Action({
						text: 'Удалить',
						iconCls: 'delete16',
						handler: function() {
							win.swStaffPanel.deleteSUPPersonalHistoryWP();
						}
					})
				};

				win.swStaffPanel.surActions = surActions;
				win.swStaffPanel.addActions({
					id: 'swStaffPanel_action_sur',
					name: 'action_sur',
					text: 'СУР',
					menu: [
						surActions.edit,
						surActions.view,
						surActions.delete
					]
				});
			}
			if (!this.swStaffPanel.getAction('action_covid')) {
				this.swStaffPanel.addActions({
					name:'action_covid',
					hidden: getRegionNick() == 'kz',
					text: 'COVID',
					disabled: (this.action=='view'),
					handler: function() {
						if ( this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected() ) {
							var MedStaffFact_id = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().get('MedStaffFact_id');
							if (MedStaffFact_id) {
								getWnd('swWorkPlaceCovidPeriodEditWindow').show({
									MedStaffFact_id: MedStaffFact_id,
									callback: function() {
										this.swStaffPanel.ViewGridPanel.getStore().reload();
									}.createDelegate(this)
								});
							}
						}
					}.createDelegate(this)
				});
			}
			if ( getRegionNick() == 'msk' && !this.swStaffPanel.getAction('action_extdlocode') )
			{
				this.swStaffPanel.addActions({
					name:'action_extdlocode',
					text:langs('Внешний код ЛЛО'),
					disabled: (this.action=='view'),
					handler: function()
					{
						if ( this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var MedStaffFact_id = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().get('MedStaffFact_id');
							if (MedStaffFact_id) {
								// форма профиля
								getWnd('swMedStaffFactDLOPeriodLinkEditWindow').show({
									MedStaffFact_id: MedStaffFact_id,
									callback: function() {
										this.swStaffPanel.ViewGridPanel.getStore().reload();
									}.createDelegate(this)
								});
							}
						}
					}.createDelegate(this)
				});
			}
			if ( !this.swStaffPanel.getAction('action_educationplan') )
			{
				this.swStaffPanel.addActions({
					name:'action_educationplan',
					text:langs('План обучения'),
					disabled: (this.action=='view'),
					handler: function()
					{
						if ( this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var MedPersonal_id = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().get('MedPersonal_id');
							window.gwtBridge.runTrainingPlanEditor(getPromedUserInfo(), String(MedPersonal_id), function(result) { });
						}
					}.createDelegate(this)
				});
			}
			if ( !this.swStaffPanel.getAction('action_card') )
			{
				this.swStaffPanel.addActions({
					name:'action_card',
					text:langs('Карточка'),
					disabled: (this.action=='view'),
					handler: function()
					{
						if ( this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var MedPersonal_id = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().get('MedPersonal_id');
							window.gwtBridge.runMedWorkerEditor(getPromedUserInfo(), String(MedPersonal_id), function(result) {

							});
						}
					}.createDelegate(this)
				});
			}
			if ( !this.swStaffPanel.getAction('action_union') )
			{
				win.swStaffPanel.addActions({
					id: 'swStaffPanel_action_union',
					name:'action_union',
					text:langs('Действия'),
					disabled: win.action=='view',
					menu: [{
						text: langs('Объединение'),
						tooltip: langs('Добавить запись к объединению'),
						handler: function() {
							var
								grid = this.swStaffPanel.getGrid(),
								PersonUnionWindow = getWnd('swPersonUnionWindow');

							var params = {
								successFn: function () {
									grid.getStore().reload();
								},
								selRec: grid.getSelectionModel().getSelected(),
								clearGrid: !PersonUnionWindow.isVisible() ? true : false
							};

							PersonUnionWindow.show(params);
						}.createDelegate(this),
						iconCls: 'union16'
					}]
				});
			}
		},
		addStaffProfileActions: function() {
			if ( !this.swStaffPanel.getAction('action_profile') )
			{
				this.swStaffPanel.addActions({
					name:'action_profile',
					text:langs('Профиль'),
					disabled: (this.action=='view'),
					handler: function()
					{
						if ( this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var MedStaffFact_id = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().get('MedStaffFact_id');
							if (MedStaffFact_id) {
								// форма профиля
								getWnd('swMedStaffFactProfileEditWindow').show({
									MedStaffFact_id: MedStaffFact_id
								});
							}
						}
					}.createDelegate(this)
				});
			}
		},
		addStaffTTActions: function() {
			var staffTTPanel = this.swStaffTTPanel;
			if ( staffTTPanel.getEl() && !staffTTPanel.getAction('action_wp_add') )
			{
				/*
				 this.swStaffTTPanel.addActions({
				 name: 'action_staff_actions',
				 disabled: isMedPersView(),
				 text:langs('Действия'),
				 menu: [{
				 name: 'download_med_staff',
				 text: langs('Выгрузить ФРМП'),
				 hidden: !isAdmin,
				 handler: function()
				 {
				 window.open('ermp/servlets/ErmpExportServlet.ermpExportServlet');
				 }.createDelegate(this)
				 },{
				 name: 'download_qwerty_lpu_q',
				 text: langs('Выгрузка для QWERTY LPU_Q'),
				 hidden: !isAdmin,
				 handler: function()
				 {
				 var fd = 'swLpuStructureStaffExport2Dbf';
				 var params = {
				 onHide: Ext.emptyFn,
				 query2export: 'LPU_Q',
				 queryName: langs('Выгрузка для QWERTY LPU_Q')
				 };
				 getWnd(fd).show(params);
				 }.createDelegate(this)
				 },{
				 name: 'download_qwerty_svf_q',
				 text: langs('Выгрузка для QWERTY SVF_Q'),
				 hidden: !isAdmin,
				 handler: function()
				 {
				 var fd = 'swLpuStructureStaffExport2Dbf';
				 var params = {
				 onHide: Ext.emptyFn,
				 query2export: 'SVF_Q',
				 queryName: langs('Выгрузка для QWERTY SVF_Q')
				 };
				 getWnd(fd).show(params);
				 }.createDelegate(this)
				 },{
				 name: 'download_qwerty_svf_q_2',
				 text: langs('Выгрузка для QWERTY SVF_Q_2'),
				 hidden: !isAdmin,
				 handler: function()
				 {
				 var fd = 'swLpuStructureStaffExport2Dbf';
				 var params = {
				 onHide: Ext.emptyFn,
				 query2export: 'SVF_Q_2',
				 queryName: langs('Выгрузка для QWERTY SVF_Q_2')
				 };
				 getWnd(fd).show(params);
				 }.createDelegate(this)
				 },{
				 name: 'download_reg_fond',
				 text: langs('Регистр ФОМС (старый)'),
				 hidden: !isAdmin,
				 handler: function()
				 {
				 var fd = 'swLpuStructureStaffExport2Dbf';
				 var params = {
				 onHide: Ext.emptyFn,
				 query2export: 'REG_FOND',
				 queryName: langs('Регистр ФОМС (старый)')
				 };
				 getWnd(fd).show(params);
				 }.createDelegate(this)
				 },{
				 name: 'download_reg_fond',
				 text: langs('Регистр ФОМС (новый)'),
				 hidden: !isAdmin,
				 handler: function()
				 {
				 var fd = 'swLpuStructureStaffExport2Dbf';
				 var params = {
				 onHide: Ext.emptyFn,
				 query2export: 'REG_FOND_NEW',
				 queryName: langs('Регистр ФОМС (новый)')
				 };
				 getWnd(fd).show(params);
				 }.createDelegate(this)
				 }
				 ]
				 });
				 */
				staffTTPanel.addActions({
					name:'action_wp_add',
					hidden: isMedPersView(),
					text:langs('Добавить место работы'),
					disabled: (this.action=='view'),
					handler: function()
					{
						if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var row = this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected();
							var lpuStruct = new Array();
							lpuStruct.Lpu_id = String(row.get('Lpu_id')) == 'null' ? null : String(row.get('Lpu_id'));
							lpuStruct.LpuBuilding_id = String(row.get('LpuBuilding_id')) == 'null' ? null : String(row.get('LpuBuilding_id'));
							lpuStruct.LpuUnit_id = String(row.get('LpuUnit_id')) == 'null' ? null : String(row.get('LpuUnit_id'));
							lpuStruct.LpuSection_id = String(row.get('LpuSection_id')) == 'null' ? null : String(row.get('LpuSection_id'));
							lpuStruct.description = '';
							window.gwtBridge.runWorkPlaceEditor(getPromedUserInfo(), null, lpuStruct, function(result) {
								if ( Number(result) > 0 )
									this.swStaffTTPanel.refreshRecords();
							}.createDelegate(this));
						}
					}.createDelegate(this)
				});
			}
		},
		addSectionWardPanelActions: function()
		{
			if(!this.swSectionWardPanel.getAction('action_setAct'))
			{
				this.swSectionWardPanel.addActions({
					name: 'action_setAct',
					text: langs('Отображать все'),
					handler: function()
					{
						var grid = this.swSectionWardPanel.ViewGridPanel;
						var setActAction = this.swSectionWardPanel.ViewActions.action_setAct;
						if(grid.getStore().baseParams.LpuSectionWard_isAct == 2)
						{
							grid.getStore().baseParams.LpuSectionWard_isAct = 0;
							setActAction.setText(langs('Отображать только действующие'));
						}
						else
						{
							grid.getStore().baseParams.LpuSectionWard_isAct = 2;
							setActAction.setText(langs('Отображать все'));
						}
						grid.getStore().load();
					}.createDelegate(this)
				});
			}
		},
		addSectionBedStatePanelActions: function()
		{
			if(!this.swSectionBedStatePanel.getAction('action_setAct'))
			{
				this.swSectionBedStatePanel.addActions({
					name: 'action_setAct',
					text: langs('Отображать только действующие'),
					handler: function()
					{
						var grid = this.swSectionBedStatePanel.ViewGridPanel;
						var setActAction = this.swSectionBedStatePanel.ViewActions.action_setAct;
						if(grid.getStore().baseParams.is_Act == null || typeof grid.getStore().baseParams.is_Act == 'undefined')
						{
							grid.getStore().baseParams.is_Act = 1;
							setActAction.setText(langs('Отображать все'));
						}
						else
						{
							grid.getStore().baseParams.is_Act = null;
							setActAction.setText(langs('Отображать только действующие'));
						}
						grid.getStore().load();
					}.createDelegate(this)
				});

			}
		},
		addMedServicePanelActions: function()
		{
			if(!this.swMedServicePanel.getAction('action_searchMedService'))
			{
				//переводить курсор на выбранную службу в структуре дерева МО
				this.swMedServicePanel.addActions({
					name: 'action_searchMedService',
					text: langs('Переход к службе'),
					handler: function()
					{
						var record = this.swMedServicePanel.ViewGridPanel.getSelectionModel().getSelected();
						if(record){
							var tree = this.findById('lpu-structure-frame'),
								node = tree.getSelectionModel().selNode,
								ch_node;

							var searchChildNode = function(options){
								return options.node.findChildBy(function(n){
									if(options.object != 'LpuUnitType' && n.attributes.object == options.object && n.attributes.object_value == options.record.get(options.object+'_id'))
										return true;
									if(options.object == 'LpuUnitType' && n.attributes.object == options.object && n.attributes.object_value == options.record.get('LpuBuilding_id') && n.attributes.LpuUnitType_id == options.record.get(options.object+'_id'))
										return true;
								});
							};

							var searchMedServiceByRecord = function(options){
								options.object = 'MedService';
								var s_node = searchChildNode(options);
								//log(['searchMedServiceByRecord',s_node,options.node.childNodes.length,options]);
								if(s_node) {
									s_node.select();
									tree.fireEvent('click', s_node);
								}
							};

							var searchRecursive = function(conf){
								//log(conf);
								var str = {
									Lpu:'LpuBuilding',
									LpuBuilding: 'LpuUnitType',
									LpuUnitType: 'LpuUnit',
									LpuUnit: 'LpuSection',
									LpuSection: false
								};
								if(conf.object && conf.node.attributes.object == conf.object) {
									var object = str[conf.object];
									//log(object);
									var object_key = object+'_id';
									var options = {node: conf.node,record: conf.record, object: object};
									//это служба уровня conf.object?
									if(conf.record.get(object_key) > 0) {
										//нет
										if(conf.node.isExpanded() && conf.node.childNodes.length>0) {
											ch_node = searchChildNode(options);
											if(ch_node) {
												options.node = ch_node;
												searchRecursive(options);
											}
										} else {
											conf.node.expand(false,false,function(n){
												options.node = n;
												ch_node = searchChildNode(options);
												if(ch_node) {
													options.node = ch_node;
													searchRecursive(options);
												}
											});
										}
									} else {
										//да
										if(conf.node.isExpanded() && conf.node.childNodes.length>0) {
											searchMedServiceByRecord({node: conf.node,record: conf.record});
										} else {
											conf.node.expand(false,false,function(n){
												searchMedServiceByRecord({node: n,record: conf.record});
											});
										}
									}
								}
							}
							//начинаем поиск с текущего уровня
							searchRecursive({node: node,record: record, object: node.attributes.object});
						}
					}.createDelegate(this)
				});
			}
			/*if(!this.swMedServicePanel.getAction('action_setAct'))
			 {
			 this.swMedServicePanel.addActions({
			 name: 'action_setAct',
			 text: 'Актуальные службы',//по умолчанию 'Актуальные службы' is_Act = 0
			 handler: function()
			 {
			 var grid = this.swMedServicePanel.ViewGridPanel;
			 var setActAction = this.swMedServicePanel.ViewActions.action_setAct;
			 if(Ext.isEmpty(grid.getStore().baseParams.is_Act) || grid.getStore().baseParams.is_Act == 0)
			 {
			 grid.getStore().baseParams.is_Act = 1;
			 setActAction.setText(langs('Все'));
			 }
			 else
			 {
			 grid.getStore().baseParams.is_Act = 0;
			 setActAction.setText(langs('Актуальные службы'));
			 }
			 grid.getStore().load();
			 }.createDelegate(this)
			 });
			 }*/
			if(!this.swMedServicePanel.getAction('action_setAll'))
			{
				this.swMedServicePanel.addActions({
					name: 'action_setAll',
					text: 'Службы Выбранного уровня',//по умолчанию 'Все службы' is_All = 1
					handler: function()
					{
						var grid = this.swMedServicePanel.ViewGridPanel;
						var setAllAction = this.swMedServicePanel.ViewActions.action_setAll;
						if(Ext.isEmpty(grid.getStore().baseParams.is_All) || grid.getStore().baseParams.is_All == 0)
						{
							grid.getStore().baseParams.is_All = 1;
							setAllAction.setText(langs('Службы Выбранного уровня'));
						}
						else
						{
							grid.getStore().baseParams.is_All = 0;
							setAllAction.setText(langs('Все службы'));
						}
						grid.getStore().load();
					}.createDelegate(this)
				});
			}
			if(!this.swMedServicePanel.getAction('action_schedule'))
			{
				this.swMedServicePanel.addActions({
					iconCls: 'eph-record16',
					name:'action_schedule',
					hidden: getWnd('swWorkPlaceMZSpecWindow').isVisible(),
					text:langs('Рaсписание'),
					handler: function()
					{
						if ( this.swMedServicePanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var MedService_id = this.swMedServicePanel.ViewGridPanel.getSelectionModel().getSelected().get('MedService_id');
							var MedService_Name = this.swMedServicePanel.ViewGridPanel.getSelectionModel().getSelected().get('MedService_Name');
							var MedServiceType_SysNick = this.swMedServicePanel.ViewGridPanel.getSelectionModel().getSelected().get('MedServiceType_SysNick');

							if (MedServiceType_SysNick == 'func') {
								getWnd('swTTRScheduleEditWindow').show({
									MedService_id: MedService_id,
									MedService_Name: MedService_Name,
									readOnly: (this.action == 'view')
								});
							} else {
								getWnd('swTTMSScheduleEditWindow').show({
									MedService_id: MedService_id,
									MedService_Name: MedService_Name,
									readOnly: (this.action == 'view')
								});
							}
						}
					}.createDelegate(this)
				});
			}
		},
		title:langs('Структура МО'),
		layout: 'border',
		maximized: true,
		maximizable: false,
		shim: false,
		buttonAlign : "right",
		codeRefresh: true,
		objectName: 'swLpuStructureViewForm',
		objectSrc: '/jscore/Forms/Admin/LpuStructureViewForm.js',
		buttons:
			[
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event)
					{
						ShowHelp(this.ownerCt.title);
					}
				},
				{
					text	  : BTN_FRMCLOSE,
					tabIndex  : -1,
					tooltip   : langs('Закрыть структуру'),
					iconCls   : 'cancel16',
					handler   : function()
					{
						this.ownerCt.hide();
					}
				}
			],
		show: function()
		{
			sw.Promed.swLpuStructureViewForm.superclass.show.apply(this, arguments);

			/*this.findById('lpustructure-tabs-panel').setActiveTab(1);
			 this.findById('lpustructure-tabs-panel').setActiveTab(2);
			 this.findById('lpustructure-tabs-panel').setActiveTab(3);
			 this.findById('lpustructure-tabs-panel').setActiveTab(4);
			 this.findById('lpustructure-tabs-panel').setActiveTab(5);
			 this.findById('lpustructure-tabs-panel').setActiveTab(6);
			 this.findById('lpustructure-tabs-panel').setActiveTab(7);
			 this.findById('lpustructure-tabs-panel').setActiveTab(8);
			 this.findById('lpustructure-tabs-panel').setActiveTab(9);
			 this.findById('lpustructure-tabs-panel').setActiveTab(10);
			 this.findById('lpustructure-tabs-panel').setActiveTab(11);
			 this.findById('lpustructure-tabs-panel').setActiveTab(0);*/
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_oneregion');
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_bedstate');
			//this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_ward');
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_finans');
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_tariff');
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_lputariff'); // Тарифы СМП / Тарифы ДД (вкладка тарифов на уровне МО)
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_shift');
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_licence');
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_unit');	  // Группа отделений
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_section');   // Отделения
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_region');	// Участки
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_lpuorgserved');  // Обслуживаемые организации
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_tariffmes'); // Тарифы МЭС
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_plan');	  // Планирование
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_building');  // Здания
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_usluga');	// Услуги
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_resource');
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_quote');	 // Планирование
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_dopdispplandd');	 // План диспансеризации двн
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_medservice');	 // Службы
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_medservicemedpersonal');	 // Врачи
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_lpusectionprofilemedservice');	 // Профили консультирования
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_analyzer');	 // Анализаторы
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_apparatus');	 // Аппараты
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_remotecons');	 // Центр удаленной консультации
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_forenmedcorps');	 // Обслуживающие отделения для служб судмедэкспертизы трупов/ районных отделений БСМЭ
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_forenhist');	 // Обслуживающие отделения для служб судебно-гистологической/медико-криминалистической экспертизы
			this.findById('lpustructure-tabs-panel').hideTabStripItem('tab_smpunitparams');	 // Настройки подстанции СМП
			if ( arguments[0] )
			{
				if ( arguments[0].Lpu_id ){
					this.findById('lpu-structure-frame').Lpu_id = arguments[0].Lpu_id;
				}
				else{
					this.findById('lpu-structure-frame').Lpu_id = getGlobalOptions().lpu_id;
				}
				if(arguments[0].action) this.action = arguments[0].action;
				else this.action = 'edit';
			} else {
				this.findById('lpu-structure-frame').Lpu_id = getGlobalOptions().lpu_id;
				this.action = "edit";
			}
			var win = this;

			var node = this.findById('lpu-structure-frame').getRootNode();
			if (this.findById('lpu-structure-frame').rootVisible == false)
			{
				if (node.hasChildNodes() == true)
				{
					node = node.findChild('object', 'Lpu');
				}
			}

			this.FileUploadField.onResize(285,30);

			this.findById('lpu-structure-frame').getLoader().load(this.findById('lpu-structure-frame').getRootNode());
			node = this.findById('lpu-structure-frame').getRootNode();
			this.findBy(function(rec){
				if(typeof rec.getGrid=="function") {
					//не скрываем кнопки для АРМ минздрава на вкладке планирование
					if (!(haveArmType('spec_mz') && rec.id == 'LpuSectionQuote')) {
						rec.setReadOnly(win.action=='view');
						if (win.action == 'view' || rec.getAction('action_add') && !rec.getAction('action_add').initialConfig.hidden) {
							rec.setActionHidden('action_add', (win.action == 'view'));
						}
						if (win.action == 'view' || rec.getAction('action_add') && !rec.getAction('action_add').initialConfig.hidden) {
							rec.setActionHidden('action_edit', (win.action == 'view'));
						}
						if (win.action == 'view' || rec.getAction('action_add') && !rec.getAction('action_add').initialConfig.hidden) {
							rec.setActionHidden('action_delete', (win.action == 'view'));
						}
					}
					if(rec.getAction('action_new')!=null){
						rec.setActionHidden('action_new',(win.action=='view'));
					}
					if(rec.getAction('action_wp_add')!=null){
						rec.setActionHidden('action_wp_add',(win.action=='view'));
					}

				}
			});
		},
		/**
		 * Получение аргументов из дерева, вообще конечно можно оптимизировать
		 */
		getNodeArgs: function(node)
		{
			var node = node || swLpuStructureFrame.getSelectionModel().getSelectedNode();
			var args = {
				Lpu_id: null,
				LpuBuilding_id: null,
				LpuUnitType_id: null,
				LpuUnit_id: null,
				LpuSection_id: null,
				MedService_id: null,
				Org_id: null,
				OrgStruct_id: null
			};
			args.action = 'add';

			var lookNode = node;
			var attr;
			while(lookNode != null) {
				attr = lookNode.attributes;
				if (!args[attr.object_id] && attr.object_value) {
					args[attr.object_id] = attr.object_value;
				}
				if (attr.LpuUnitType_id) {
					args.LpuUnitType_id = attr.LpuUnitType_id;
				}
				lookNode = lookNode.parentNode;
			}
			return args;
		},
		reloadCurrentTreeNode: function(frm)
		{
			var LpuStructureFrame= Ext.getCmp('lpu-structure-frame');
			var selNode = LpuStructureFrame.getSelectionModel().selNode;


			// Если открепить подразделение от филиала, то надо обновлять всю ветку МО
			if (selNode.attributes.object === 'LpuFilial')
			{
				selNode = selNode.parentNode;
			}

			//if ((selNode.isExpanded()) || (selNode.childNodes.length>0))
			//{
			if (selNode.isExpanded()) // перечитываем ветку, только когда она уже была распахнутой
			{
				LpuStructureFrame.loader.load(selNode);

				selNode.on({'expand': {fn: function() {frm.focus();}, scope: selNode, delay: 500}});
				selNode.expand();
			}
			//}
		},
		deleteResource: function(){
			var ResourceMedServiceGrid = this.resourceContentsGrid.getGrid();

			var params = new Object();
			if ( !ResourceMedServiceGrid.getSelectionModel().getSelected() || !ResourceMedServiceGrid.getSelectionModel().getSelected().get('Resource_id') ) {
				return false;
			}
			var record = ResourceMedServiceGrid.getSelectionModel().getSelected();

			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление ресурса..."});
						loadMask.show();

						Ext.Ajax.request({
							failure: function(response, options) {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении услуги на службе'));
							},
							params: {
								Resource_id: record.get('Resource_id')
							},
							success: function(response, options) {
								loadMask.hide();

								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.Error_Msg ) {
									sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg );
								}
								else {
									ResourceMedServiceGrid.getStore().remove(record);
								}
							}.createDelegate(this),
							url: '/?c=MedService&m=deleteResource'
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Удалить ресурс?'),
				title: langs('Вопрос')
			});
		},
		deleteUsluga: function() {
			if (this.uslugaContentsGrid.MedService_id != null) {
				// удаляем услугу на службе
				var UslugaComplexMedServiceGrid = this.uslugaContentsGrid.getGrid();

				if ( !UslugaComplexMedServiceGrid.getSelectionModel().getSelected() || !UslugaComplexMedServiceGrid.getSelectionModel().getSelected().get('UslugaComplexMedService_id') ) {
					return false;
				}

				var record = UslugaComplexMedServiceGrid.getSelectionModel().getSelected();

				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление услуги..."});
							loadMask.show();

							Ext.Ajax.request({
								failure: function(response, options) {
									loadMask.hide();
									sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении услуги на службе'));
								},
								params: {
									UslugaComplexMedService_id: record.get('UslugaComplexMedService_id')
								},
								success: function(response, options) {
									loadMask.hide();

									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( response_obj.Error_Msg ) {
										sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg );
									}
									else {
										UslugaComplexMedServiceGrid.getStore().remove(record);
									}

									if ( UslugaComplexMedServiceGrid.getStore().getCount() > 0 ) {
										UslugaComplexMedServiceGrid.getView().focusRow(0);
										UslugaComplexMedServiceGrid.getSelectionModel().selectFirstRow();
									}
								}.createDelegate(this),
								url: '/?c=UslugaComplex&m=deleteUslugaComplexMedService'
							});
						}
						else {
							UslugaComplexMedServiceGrid.getView().focusRow(0);
							UslugaComplexMedServiceGrid.getSelectionModel().selectFirstRow();
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Удалить услугу?'),
					title: langs('Вопрос')
				});
			} else {
				// удаляем услугу не на службе.
				var grid = this.uslugaContentsGrid.getGrid();

				if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
					return false;
				}

				var record = grid.getSelectionModel().getSelected();

				if ( this.uslugaNavigationString.getLevel() == 0 ) {
					// Удаляем услугу
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление услуги..."});
								loadMask.show();

								Ext.Ajax.request({
									failure: function(response, options) {
										loadMask.hide();
										sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении услуги из справочника'));
									},
									params: {
										UslugaComplex_id: record.get('UslugaComplex_id')
									},
									success: function(response, options) {
										loadMask.hide();

										var response_obj = Ext.util.JSON.decode(response.responseText);

										if ( response_obj.Error_Msg ) {
											sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg );
										}
										else {
											grid.getStore().remove(record);
										}

										if ( grid.getStore().getCount() > 0 ) {
											grid.getView().focusRow(0);
											grid.getSelectionModel().selectFirstRow();
										}
									}.createDelegate(this),
									url: '/?c=UslugaComplex&m=deleteUslugaComplex'
								});
							}
							else {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Удалить услугу из справочника?'),
						title: langs('Вопрос')
					});
				}
				else {
					// Удаляем услугу из состава комплексной услуги
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление услуги из состава комплексной услуги..."});
								loadMask.show();

								Ext.Ajax.request({
									failure: function(response, options) {
										loadMask.hide();
										sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении услуги из состава комплексной услуги'));
									},
									params: {
										UslugaComplexComposition_id: record.get('UslugaComplexComposition_id')
									},
									success: function(response, options) {
										loadMask.hide();

										var response_obj = Ext.util.JSON.decode(response.responseText);

										if ( response_obj.Error_Msg ) {
											sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg );
										}
										else {
											grid.getStore().remove(record);
										}

										if ( grid.getStore().getCount() > 0 ) {
											grid.getView().focusRow(0);
											grid.getSelectionModel().selectFirstRow();
										}
									}.createDelegate(this),
									url: '/?c=UslugaComplex&m=deleteUslugaComplexComposition'
								});
							}
							else {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Удалить услугу из состава комплексной услуги?'),
						title: langs('Вопрос')
					});
				}
			}
		},
		deleteUslugaComplexPlace: function() {
			var grid = this.uslugaComplexOnPlaceGrid.getGrid();

			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexPlace_id') ) {
				return false;
			}

			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						var record = grid.getSelectionModel().getSelected();

						var params = new Object();
						var url = "/?c=UslugaComplex&m=deleteUslugaComplexPlace";
						params.UslugaComplexPlace_id = record.get('UslugaComplexPlace_id');

						if (!Ext.isEmpty(url)) {
							Ext.Ajax.request({
								callback: function(opt, scs, response) {
									if (scs) {
										var result = Ext.util.JSON.decode(response.responseText);

										if (result.success)
										{
											grid.getStore().reload();
										}
									}
								}.createDelegate(this),
								params: params,
								url: url
							});
						}
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Удалить место оказания услуги?'),
				title: langs('Вопрос')
			});
		},
		deleteUslugaComplexTariff: function() {
			var parentGrid = this.uslugaComplexOnPlaceGrid.getGrid();
			var grid = this.uslugaComplexTariffGrid.getGrid();

			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexTariff_id') ) {
				return false;
			}

			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						var record = grid.getSelectionModel().getSelected();

						var params = new Object();
						var url = "/?c=UslugaComplex&m=deleteUslugaComplexTariff";
						params.UslugaComplexTariff_id = record.get('UslugaComplexTariff_id');

						if (!Ext.isEmpty(url)) {
							Ext.Ajax.request({
								callback: function(opt, scs, response) {
									if (scs) {
										var result = Ext.util.JSON.decode(response.responseText);

										if (result.success)
										{
											parentGrid.getStore().reload();
										}
									}
								}.createDelegate(this),
								params: params,
								url: url
							});
						}
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Удалить тариф услуги?'),
				title: langs('Вопрос')
			});
		},
		openUslugaComplexTariffEditWindow: function(action) {
			if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
				return false;
			}

			if ( getWnd('swUslugaComplexTariffEditWindow').isVisible() ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования тарифа услуги уже открыто'));
				return false;
			}

			var wnd = this;
			var base_form = this.formPanel.getForm();
			var parentGrid = this.uslugaComplexOnPlaceGrid.getGrid();
			var parentRecord = parentGrid.getSelectionModel().getSelected();
			var grid = this.uslugaComplexTariffGrid.getGrid();
			var params = new Object();

			if (action == 'add') {
				params.Lpu_id = grid.getStore().baseParams.Lpu_id || parentRecord.get('Lpu_id') || null;
				params.LpuBuilding_id = grid.getStore().baseParams.LpuBuilding_id || parentRecord.get('LpuBuilding_id') || null;
				params.LpuUnit_id = grid.getStore().baseParams.LpuUnit_id || parentRecord.get('LpuUnit_id') || null;
				params.LpuSection_id = grid.getStore().baseParams.LpuSection_id || parentRecord.get('LpuSection_id') || null;
			} else {
				params.Lpu_id = grid.getStore().baseParams.Lpu_id || null;
				params.LpuBuilding_id = grid.getStore().baseParams.LpuBuilding_id || null;
				params.LpuUnit_id = grid.getStore().baseParams.LpuUnit_id || null;
				params.LpuSection_id = grid.getStore().baseParams.LpuSection_id || null;
			}

			params.mode = 'LpuStructure';

			params.action = action;
			params.callback = function(data) {
				if ( typeof data == 'object' && typeof data.uslugaComplexTariffData == 'object' && !Ext.isEmpty(data.uslugaComplexTariffData.UslugaComplexTariff_id) ) {
					wnd.uslugaComplexTariffGrid.focusOnRecord = data.uslugaComplexTariffData.UslugaComplexTariff_id;
				}
				parentGrid.getStore().reload();
			}.createDelegate(this);
			params.formMode = 'remote';
			params.formParams = new Object();

			if ( action == 'add' ) {
				params.formParams.UslugaComplex_id = parentRecord.get('UslugaComplex_id');
				params.onHide = function() {
					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
					}
				};
			}
			else {
				if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexTariff_id') ) {
					return false;
				}

				var record = grid.getSelectionModel().getSelected();

				params.formParams = record.data;
				params.formParams.UslugaComplex_id = parentRecord.get('UslugaComplex_id');
				params.onHide = function() {
					if ( grid.getStore().indexOf(record) != -1 ){
						grid.getView().focusRow(grid.getStore().indexOf(record));
					}
				};
			}

			getWnd('swUslugaComplexTariffEditWindow').show(params);
		},
		openUslugaComplexPlaceEditWindow: function(action) {
			if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
				return false;
			}

			if ( getWnd('swUslugaComplexPlaceEditWindow').isVisible() ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования места оказания услуги уже открыто'));
				return false;
			}

			var base_form = this.formPanel.getForm();
			var grid = this.uslugaComplexOnPlaceGrid.getGrid();
			var params = new Object();

			params.Lpu_id = grid.getStore().baseParams.Lpu_id || null;
			params.LpuBuilding_id = grid.getStore().baseParams.LpuBuilding_id || null;
			params.LpuUnit_id = grid.getStore().baseParams.LpuUnit_id || null;
			params.LpuSection_id = grid.getStore().baseParams.LpuSection_id || null;
			params.mode = 'LpuStructure';

			params.action = action;
			params.callback = function(data) {
				grid.getStore().reload();
			}.createDelegate(this);
			params.formMode = 'remote';
			params.formParams = new Object();

			if ( action == 'add' ) {
				params.onHide = function() {
					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
					}
				};
			}
			else {
				if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexPlace_id') ) {
					return false;
				}

				var record = grid.getSelectionModel().getSelected();

				params.UslugaComplex_id = record.get('UslugaComplex_id');
				params.formParams = record.data;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(record));
				};
			}

			getWnd('swUslugaComplexPlaceEditWindow').show(params);
		},
		openUslugaEditWindow: function(action) {
			var win = this;
			if ( !action || !action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
				return false;
			}
			//alert('@');
			if ( getWnd('swUslugaComplexMedServiceEditWindow').isVisible() ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Окно добавления услуги на службу уже открыто'));
				return false;
			}

			var UslugaComplexMedServiceGrid = this.uslugaContentsGrid.getGrid();

			var formParams = new Object();
			var params = new Object();

			if (UslugaComplexMedServiceGrid.getStore().baseParams.MedService_id) {
				formParams.MedService_id = UslugaComplexMedServiceGrid.getStore().baseParams.MedService_id;
			} else {
				return false;
			}

			if (UslugaComplexMedServiceGrid.getStore().baseParams.UslugaComplexMedService_pid) {
				formParams.UslugaComplexMedService_pid = UslugaComplexMedServiceGrid.getStore().baseParams.UslugaComplexMedService_pid;
			}

			if (action == 'edit' || action == 'view') {
				if ( !UslugaComplexMedServiceGrid.getSelectionModel().getSelected() || !UslugaComplexMedServiceGrid.getSelectionModel().getSelected().get('UslugaComplexMedService_id') ) {
					return false;
				}
				var record = UslugaComplexMedServiceGrid.getSelectionModel().getSelected();
				formParams.UslugaComplexMedService_id = record.get('UslugaComplexMedService_id');
				formParams.UslugaComplex_id = record.get('UslugaComplex_id');
				formParams.UslugaComplexMedService_pid = record.get('UslugaComplexMedService_pid');
				formParams.UslugaComplexMedService_begDT = record.get('UslugaComplexMedService_begDT');
				formParams.UslugaComplexMedService_endDT = record.get('UslugaComplexMedService_endDT');
				formParams.UslugaComplexMedService_Time = record.get('UslugaComplexMedService_Time');
				formParams.UslugaComplexMedService_IsPortalRec = record.get('UslugaComplexMedService_IsPortalRec');
				formParams.UslugaComplexMedService_IsPay = record.get('UslugaComplexMedService_IsPay');
				formParams.UslugaComplexMedService_IsElectronicQueue = record.get('UslugaComplexMedService_IsElectronicQueue');
			}

			params.action = action;
			params.formMode = 'remote';
			params.formParams = formParams;

			params.callback = function(data) {
				UslugaComplexMedServiceGrid.getStore().reload();
			};

			var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();

			// при добавлении услуги на службу лаборатории в состав нужно дать возможность выбирать только услуги тестов лис
			// отключил по #26867
			if (false && action == 'add' && !Ext.isEmpty(formParams.UslugaComplexMedService_pid) && node.attributes.MedServiceType_SysNick && node.attributes.MedServiceType_SysNick.inlist(['lab'])) {
				// окно выбора теста в ЛИС
				params = new Object();
				params.callback = function(rec) {
					if (!Ext.isEmpty(rec.test_id)) {
						// добавляем услугу из теста на службу
						win.getLoadMask(langs('Добавление услуги из теста на службу...')).show();
						Ext.Ajax.request({
							callback: function(options, success, response) {
								win.getLoadMask().hide();
								if ( success ) {
									UslugaComplexMedServiceGrid.getStore().reload();
								}
								else {
									sw.swMsg.alert(langs('Ошибка'), langs('При добавлении услуги возникли ошибки'));
								}
							},
							params: {
								UslugaComplexMedService_pid: formParams.UslugaComplexMedService_pid,
								test_id: rec.test_id
							},
							url: '/?c=Analyzer&m=addUslugaComplexMedServiceFromTest'
						});
					}
				};
				params.UslugaComplexMedService_pid = formParams.UslugaComplexMedService_pid;
				getWnd('swLisSelectTestWindow').show(params);
			} else {
				// окно редактирования услуги на службе
				params.formParams.MedServiceType_SysNick = (node.attributes.MedServiceType_SysNick) ? node.attributes.MedServiceType_SysNick : null;
				getWnd('swUslugaComplexMedServiceEditWindow').show(params);
			}
		},
		openResourceEditWindow: function(action) {
			var win = this;
			if ( !action || !action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
				return false;
			}
			//alert('@');
			if ( getWnd('swResourceMedServiceEditWindow').isVisible() ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Окно добавления услуги на службу уже открыто'));
				return false;
			}

			var ResourceMedServiceGrid = this.resourceContentsGrid.getGrid();

			var formParams = new Object();
			var params = new Object();

			if (ResourceMedServiceGrid.getStore().baseParams.MedService_id) {
				formParams.MedService_id = ResourceMedServiceGrid.getStore().baseParams.MedService_id;
			} else {
				return false;
			}

			if (action == 'edit' || action == 'view') {
				if ( !ResourceMedServiceGrid.getSelectionModel().getSelected() || !ResourceMedServiceGrid.getSelectionModel().getSelected().get('Resource_id') ) {
					return false;
				}
				var record = ResourceMedServiceGrid.getSelectionModel().getSelected();
				formParams.Resource_id = record.get('Resource_id');
			}

			params.action = action;
			params.formMode = 'remote';
			params.formParams = formParams;
			params.Lpu_id = win.findById('lpu-structure-frame').Lpu_id || null;

			params.callback = function(data) {
				ResourceMedServiceGrid.getStore().reload();
			};

			var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
			if (node.attributes.MedServiceType_SysNick && node.attributes.MedServiceType_SysNick == 'oper_block') {
				params.AllowedResourceType_ids = [2]; // только операционные столы
			}

			getWnd('swResourceMedServiceEditWindow').show(params);

		},

		addCloseFilterMenu: function(gridCmp, defaultValue){
			var form = this;
			var grid = gridCmp;

			if ( !grid.getAction('action_isclosefilter_'+grid.id) ) {
				var menuIsCloseFilter = new Ext.menu.Menu({
					items: [
						new Ext.Action({
							text: langs('Все'),
							handler: function() {
								if (grid.gFilters) {
									grid.gFilters.isClose = null;
								}
								grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Все</b>'));
								grid.getGrid().getStore().baseParams.isClose = null;
								grid.getGrid().getStore().reload();
							}
						}),
						new Ext.Action({
							text: langs('Открытые'),
							handler: function() {
								if (grid.gFilters) {
									grid.gFilters.isClose = 1;
								}
								grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Открытые</b>'));
								grid.getGrid().getStore().baseParams.isClose = 1;
								grid.getGrid().getStore().reload();
							}
						}),
						new Ext.Action({
							text: langs('Закрытые'),
							handler: function() {
								if (grid.gFilters) {
									grid.gFilters.isClose = 2;
								}
								grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Закрытые</b>'));
								grid.getGrid().getStore().baseParams.isClose = 2;
								grid.getGrid().getStore().reload();
							}
						})
					]
				});

				if (defaultValue == 'all') {
					grid.addActions({
						isClose: null,
						name: 'action_isclosefilter_' + grid.id,
						text: langs('Показывать: <b>Все</b>'),
						menu: menuIsCloseFilter
					});
					grid.getGrid().getStore().baseParams.isClose = null;
				} else {
					grid.addActions({
						isClose: 1,
						name: 'action_isclosefilter_' + grid.id,
						text: langs('Показывать: <b>Открытые</b>'),
						menu: menuIsCloseFilter
					});
					grid.getGrid().getStore().baseParams.isClose = 1;
				}
			}

			return true;
		},

		overwriteTpl: function(obj)
		{
			if(!obj){
				var obj = {};
				obj.file_url = '';
				obj.full_url = '';
			}

			if (obj.file_url) {
				obj.full_url = obj.file_url.replace('thumbs/','');
				this.findById('orgPhotoPanel').tpl = new Ext.Template(this.PhotoTpl);
			} else {
				// todo: Здесь надо картинку по умолчанию
				this.findById('orgPhotoPanel').tpl = new Ext.Template('<div><!--img style="text-align: center; max-height:300px; max-width:300px;" src="" /--></div>');
			}
			this.findById('orgPhotoPanel').tpl.overwrite(this.findById('orgPhotoPanel').body, obj);
		},

		reloadViewIfFilialDisappeared: function (lpuNode, filialNode) {

			// Если в новом дереве текущий филиал пропал, то в правой части выберем уровень МО
			var LpuStructureFrame= Ext.getCmp('lpu-structure-frame'),
				lpuFilial_id = filialNode.attributes.object_value;

			if (lpuNode.findChild('object_value', lpuFilial_id) === null)
			{
				lpuNode.select();
				LpuStructureFrame.fireEvent('click', lpuNode);
			}

		},

		nodeHasFilialParent: function (node) {

			var object = 'LpuFilial';

			while (node != null)
			{

				if (node.attributes.object === object)
				{
					return true;
				}

				node = node.parentNode;
			}

			return false;
		},

		getNodeParentAttribute: function (node, object, attribute) {

			var attr;
			while (node != null)
			{
				attr = node.attributes;

				if (attr.object === object)
				{
					return attr[attribute];
				}

				node = node.parentNode;
			}

			return null;
		},

		getNodeTrueLevel: function (node) {
			return this.nodeHasFilialParent(node) ? node.getDepth() - 1 : node.getDepth();
		},
		loadMedicalCareBudgTypeTariffGrid: function() {
			var filtersForm = this.MedicalCareBudgTypeTariffFilterPanel.getForm();
			var params = filtersForm.getValues();
			params.start = 0;
			params.limit = 100;
			params.Lpu_id = this.findById('lpu-structure-frame').Lpu_id;
			params.addWithoutLpu = 1;

			this.MedicalCareBudgTypeTariffGrid.loadData({globalFilters: params, params: params});
		},
		initComponent: function()
		{
			var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');

			frms = this;
			//frms.MedStaffFactEditWindow = getWnd('swMedStaffFactEditWindow'); // это уже не нужно
			//swStructureFrame = new sw.Promed.LpuStructure({id:'lpu-structure-frame1'});

			swLpuStructureFrame = new sw.Promed.LpuStructure({id:'lpu-structure-frame'});
			swLpuStructureFrame.loader.on("beforeload", function(TreeLoader, node) {return TreeBeforeLoad(TreeLoader, node);}.createDelegate(frms), this);
			swLpuStructureFrame.on('click', function(node, e) {LpuStructureTreeClick(node, e)} );
			swLpuStructureFrame.loader.addListener('load', function (loader,node)
			{
				if (node==swLpuStructureFrame.root)
				{
					if (swLpuStructureFrame.rootVisible == false)
					{
						if (node.hasChildNodes() == true)
						{
							node = node.findChild('object', 'Lpu');
							swLpuStructureFrame.fireEvent('click', node);
						}
					}
				}
				//swLpuStructureFrame.getSelectionModel().select(node);
				//node.getUI().getAnchor().focus();
			});

			new Ext.tree.TreeSorter(swLpuStructureFrame, {
				folderSort: false,
				sortType: function(node) {
					var text = node.attributes.text;
					if(node.attributes.object == 'MedService')
						text = 'яяя'+text;
					if(node.attributes.object == 'StorageTitle')
						text = 'яяю'+text;
					if(node.attributes.object == 'Storage')
						text = 'яяю'+text;
					if(node.attributes.object == 'LpuRegionTitle')
						text = 'яящ'+text;
					if(node.attributes.object == 'LpuFilial')
						text = 'ббб'+text;
					if(node.attributes.object == 'LpuBuilding')
						text = 'ааа'+text;
					return text;
				}
				//,property: 'order'
			});


			// Фотографии
			this.PhotoTpl = [
				'<div><a target="_blank" href="{full_url}"><img style="text-align: center; max-height:300px; max-width:300px;" src="{file_url}" /></a></div>'
			];
			this.FileUploadField = new Ext.form.FileUploadField({
				allowedExtensions: ['jpg', 'jpeg', 'pjpeg', 'png', 'gif'],
				hideLabel: true,
				buttonOnly: true,
				link: {bodyStyle: 'background: transparent;',border: false, linkId:'file_upload_link', html:'<div style="text-align: center; width:100%;"><a id="file_upload_link" style="text-align: center;" href="#">Изменить фотографию</a></div>'},
				name: 'org_photo',
				id: 'org_photo',
				buttonText: langs('Обновить '),
				//input: {style:'display:none'},
				listeners: {
					fileselected: function(elem, fname) {
						var win = this;
						var frm = this.PhotoPanel.getForm();
						var re = /\.[jgp][pin][gf]/i;
						var access = re.test(fname);
						if(!access) {
							sw.swMsg.alert(langs('Ошибка'), langs('Данный тип загружаемого файла не поддерживается!<br />Поддерживаемые типы: *jpg, *gif, *png'));
							elem.reset();
							return false;
						}
						// Получаем уровень для отправки на сервер
						var args = win.getNodeArgs();
						frm.submit({
							params: args,
							success: function(frm, resp) {
								var obj = Ext.util.JSON.decode(resp.response.responseText);
								win.overwriteTpl(obj);
							},
							failure: function(frm, action) {
								if (action.result) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: Ext.emptyFn,
										icon: Ext.Msg.WARNING,
										msg: (action.result.Error_Msg)?action.result.Error_Msg:langs('При сохранении произошли ошибки'),
										title: ERR_WND_TIT
									});
								}
							}
						});
					}.createDelegate(this)
				},
				width: 130
			});

			this.PhotoPanel = new Ext.form.FormPanel({
				//region: 'west',
				width: 300,
				style: "padding: 5px;",
				bodyStyle: 'background: transparent;',
				//border: false,
				id: 'upload_panel',
				url: '/?c=LpuStructure&m=uploadOrgPhoto',
				fileUpload: true,
				items: [
					{
						height: 300,
						bodyStyle: 'background: transparent;',
						border: false,
						width: 300,
						xtype: 'panel',
						id: 'orgPhotoPanel',
						name: 'orgPhotoPanel',
						tpl: ''
					},
					this.FileUploadField
				]
			});

			//this.PhotoPanel,
// Таб 1. Уровень 1. Описание МО.
			var swLpuDescription = new sw.Promed.FormPanel(
				{
					id: 'lpudescription-panel',
					hidden: false,
					items:
						[
							{
								fieldLabel : langs('ОПФ'),
								xtype: 'descfield',
								name: 'Okopf_Name'
							},
							{
								fieldLabel : langs('Наименование'),
								xtype: 'descfield',
								name: 'Org_Name'
							},
							{
								fieldLabel : langs('Сокращение'),
								xtype: 'descfield',
								name: 'Lpu_Nick'
							},
							{
								fieldLabel : langs('Код'),
								xtype: 'descfield',
								name: 'Org_Code'
							},
							{
								fieldLabel : langs('Тип МО'),
								xtype: 'descfield',
								name: 'LpuType_Name'
							},{
							fieldLabel : langs('Код типа МО'),
							xtype: 'hidden',
							name: 'LpuType_Code'
						},
							{
								fieldLabel : langs('Юр. адрес'),
								xtype: 'descfield',
								name: 'UAddress_Address'
							},
							{
								fieldLabel : langs('Факт. адрес'),
								xtype: 'descfield',
								id: 'PAddress_Address'
							},
							{
								xtype: 'hidden',
								id: 'Lpu_IsLab'
							}
						],
					reader: new Ext.data.JsonReader(
						{
							success: function()
							{
								alert('All Right!');
							}
						},
						[
							{name: 'Lpu_id'},
							{name: 'LpuType_Code'},
							{name: 'Okopf_Name'},
							{name: 'Org_Name'},
							{name: 'Lpu_Nick'},
							{name: 'Org_Code'},
							{name: 'LpuType_Name'},
							{name: 'UAddress_Address'},
							{name: 'PAddress_Address'},
							{name: 'photo'},
							{name: 'Lpu_IsLab'}
						]),
					url: C_LPU_GET
				});

//Табгрид Атрибуты МО ( только на первом уровне )
			swLpuAttributeSignValueGridPanel = new sw.Promed.AttributeSignValueGridPanel({
				formMode: 'remote'
			});


// Таб 1. Уровень 2. Описание Филиала.
			var swLpuFilialDescription = new sw.Promed.FormPanel(
				{
					id: 'lpufilialdescription-panel',
					hidden: false,
					items:
						[
							{
								name: 'LpuFilial_id',
								xtype: 'hidden'
							},
							{
								fieldLabel : langs('Код'),
								xtype: 'descfield',
								name: 'LpuFilial_Code'
							},
							{
								fieldLabel : langs('Наименование'),
								xtype: 'descfield',
								name: 'LpuFilial_Name'
							},
							{
								fieldLabel : langs('Краткое наименование'),
								xtype: 'descfield',
								name: 'LpuFilial_Nick'
							},
							{
								fieldLabel : langs('Дата начала'),
								xtype: 'descfield',
								name: 'LpuFilial_begDate'
							},
							{
								fieldLabel : langs('Дата окончания'),
								xtype: 'descfield',
								name: 'LpuFilial_endDate'
							}
						],
					reader: new Ext.data.JsonReader(
						{
							success: function()
							{
								alert('All Right!');
							}
						},
						[
							{name: 'LpuFilial_id'},
							{name: 'LpuFilial_Code'},
							{name: 'LpuFilial_Name'},
							{name: 'LpuFilial_Nick'},
							{name: 'LpuFilial_begDate'},
							{name: 'LpuFilial_endDate'}
						]),
					url: C_LPUFILIAL_GET
				});



// Таб 1. Уровень 2. Описание подразделения.
			var swLpuBuildingDescription = new  sw.Promed.FormPanel(
				{
					id: 'buidingdescription-panel',
					hidden: true,
					items:
						[
							{
								fieldLabel : langs('Наименование'),
								xtype: 'descfield',
								name: 'LpuBuilding_Name',
								id: 'LpuBuilding_NameEdit'
							},
							{
								fieldLabel : langs('Сокращение'),
								xtype: 'descfield',
								name: 'LpuBuilding_Nick',
								id: 'LpuBuilding_NickEdit'
							},
							{
								fieldLabel : langs('Код'),
								xtype: 'descfield',
								name: 'LpuBuilding_Code',
								id: 'LpuBuilding_CodeEdit'
							},
							{
								fieldLabel : langs('Тип подразделения'),
								xtype: 'descfield',
								name: 'LpuBuildingType_Name',
								id: 'LpuBuildingTypeEdit'
							}
						],
					reader: new Ext.data.JsonReader(
						{
							success: function()
							{
							}
						},
						[
							{name: 'LpuBuilding_id'},
							{name: 'LpuBuilding_Name'},
							{name: 'LpuBuilding_Nick'},
							{name: 'LpuBuilding_Code'},
							{name: 'LpuBuildingType_Name'},
							{name: 'photo'}
						]),
					url: C_LPUBUILDING_GET
				});

// Таб 1. Уровень 3. Описание групп отделений.
			var swLpuUnitDescription = new  sw.Promed.FormPanel(
				{
					id: 'lpuunitdescription-panel',
					hidden: true,
					items:
						[
							{
								fieldLabel : langs('Код'),
								xtype: 'descfield',
								name: 'LpuUnit_Code'
							},
							{
								fieldLabel : langs('Наименование'),
								xtype: 'descfield',
								name: 'LpuUnit_Name'
							},
							{
								anchor: '100%',
								disabled: true,
								xtype: 'swlpuunittypecombo',
								//hiddenName: 'LpuUnitTypeEdit',
								name: 'LpuUnitType_id'
							}
						],
					reader: new Ext.data.JsonReader(
						{
							success: function()
							{
								alert('success');
							}
						},
						[
							{name: 'LpuUnit_id'},
							{name: 'LpuUnit_Code'},
							{name: 'LpuUnit_Name'},
							{name: 'LpuUnitType_id'},
							{name: 'photo'}
						]
					),
					url: C_LPUUNIT_GETEDIT
				});

// Таб 1. Уровень 4. Описание отделения.
			var swLpuSectionDescription = new  sw.Promed.FormPanel(
				{
					id: 'lpusectiondescription-panel',
					hidden: true,
					items:
						[
							{
								disabled: true,
								anchor: '100%',
								fieldLabel : langs('Профиль'),
								xtype: 'swlpusectionprofilecombo',
								id: 'LpuSectionProfile_idEdit',
								name: 'LpuSectionProfile_id'
							},
							{
								fieldLabel : langs('Код'),
								xtype: 'descfield',
								id: 'LpuSection_CodeEdit',
								name: 'LpuSection_Code'
							},
							{
								fieldLabel : langs('Наименование'),
								xtype: 'descfield',
								id: 'LpuSection_NameEdit',
								name: 'LpuSection_Name'
							},
							{
								xtype: 'fieldset',
								autoHeight: true,
								title: langs('Период действия'),
								style: 'padding: 2; padding-left: 5px',
								items: [
									{
										disabled: true,
										fieldLabel : langs('Начало'),
										xtype: 'swdatefield',
										format: 'd.m.Y',
										name: 'LpuSection_setDate',
										id: 'LpuSection_setDateEdit'
									},
									{
										disabled: true,
										fieldLabel : langs('Окончание'),
										xtype: 'swdatefield',
										format: 'd.m.Y',
										name: 'LpuSection_disDate',
										id: 'LpuSection_disDateEdit'
									}]}
						],
					reader: new Ext.data.JsonReader(
						{
							success: function() { }
						},
						[
							{name: 'LpuSection_id'},
							{name: 'LpuSectionProfile_id'},
							{name: 'LpuSection_Code'},
							{name: 'LpuSection_Name'},
							{name: 'LpuSection_setDate'},
							{name: 'LpuSection_disDate'},
							{name: 'photo'}
						]
					),
					url: C_LPUSECTION_GET
					/* ,
					 listeners: {
					 'beforeload': function (node) {
					 alert('LpuSectionProfile_id');
					 console.log(LpuSectionProfile_id);
					 }
					 }*/
				});

			var swStaffFilterPanel = new Ext.form.FormPanel({
				id: 'MedStaffFactFilter',
				labelAlign: 'right',
				region: 'north',
				height: 30,
				frame: true,
				validateSearchForm: function() {
					var form = this;
					var base_form = this.getForm();
					var msg = ERR_INVFIELDS_MSG;

					if (!base_form.isValid()) {
						if (!base_form.findField('MedStaffFact_date_range').validate() || !base_form.findField('MedStaffFact_disDate_range').validate()) {
							msg = langs('Требуется указать период!');
						}
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function()
							{
								form.getFirstInvalidEl().focus(false);
							},
							icon: Ext.Msg.WARNING,
							msg: msg,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}
					return true;
				},
				doReset: function() {
					this.getForm().reset();
				},
				doSearch: function() {
					var base_form = this.getForm();
					var gridPanel = Ext.getCmp('MedStaffFact');

					var params = base_form.getValues();
					if (gridPanel.getGrid().getStore().baseParams.isClose) {
						params.isClose = gridPanel.getGrid().getStore().baseParams.isClose;
					}
					params.Lpu_id = Ext.getCmp('lpu-structure-frame').Lpu_id;
					gridPanel.getGrid().getStore().baseParams = params;

					gridPanel.getGrid().getStore().load();
				},
				items: [{
					xtype: 'fieldset',
					title: langs('Фильтр'),
					style: 'padding: 5px;',
					autoHeight: true,
					collapsible: true,
					collapsed: true,
					keys: [{
						key: Ext.EventObject.ENTER,
						fn: function(e) {
							var form = this.findById('MedStaffFactFilter');
							if (form.validateSearchForm()) {
								form.doSearch();
							}
						}.createDelegate(this),
						stopEvent: true
					}, {
						ctrl: true,
						fn: function(inp, e) {
							var form = this.findById('MedStaffFactFilter');
							form.doReset();
						}.createDelegate(this),
						key: 188,
						scope: this,
						stopEvent: true
					}],
					listeners:{
						expand:function () {
							this.ownerCt.setHeight(170);
							this.ownerCt.ownerCt.syncSize();
						},
						collapse:function () {
							this.ownerCt.setHeight(30);
							this.ownerCt.ownerCt.syncSize();
						}
					},
					items: [{
						name: 'LpuBuilding_id',
						xtype: 'hidden'
					}, {
						name: 'LpuUnit_id',
						xtype: 'hidden'
					}, {
						name: 'LpuSection_id',
						xtype: 'hidden'
					}, {
						layout: 'column',
						items: [{
							width: 230,
							layout: 'form',
							labelWidth: 32,
							items:
								[{
									xtype: 'textfieldpmw',
									width: 190,
									name: 'Search_Fio',
									fieldLabel: langs('ФИО')
								}]
						}, {
							width: 230,
							labelWidth: 115,
							layout: 'form',
							items:
								[{
									xtype: 'swdatefield',
									format: 'd.m.Y',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									name: 'Search_BirthDay',
									fieldLabel: langs('Дата рождения')
								}]
						}, {
							hidden: getRegionNick().inlist([ 'kz' ]),
							layout: 'form',
							width: 195,
							labelWidth: 40,
							items:
								[{
									enableKeyEvents: true,
									fieldLabel: langs('СНИЛС'),
									hiddenName: 'Person_Snils',
									//width: 120,
									xtype: 'swsnilsfield'
								}]
						}, {
							layout: 'form',
							width: 280,
							labelWidth: 65,
							items: [{
								enableKeyEvents: true,
								fieldLabel: langs('Должность'),
								hiddenName: 'PostMed_id',
								width: 210,
								xtype: 'swpostmedlocalcombo'
							}]
						}]
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							width: 285,
							labelWidth: 80,
							items: [{
								enableKeyEvents: true,
								fieldLabel: langs('Уровень ЛПУ'),
								hiddenName: 'LpuStructure_id',
								width: 200,
								xtype: 'swlpustructureelementcombo',
								listeners: {
									'select': function(combo,record,value) {
										var base_form = Ext.getCmp('MedStaffFactFilter').getForm();

										var object = record.get('LpuStructure_Nick');
										var object_id = record.get('LpuStructureElement_id');

										if (!object.inlist(['LpuBuilding','LpuUnit','LpuSection'])) {
											return;
										}

										base_form.findField('LpuBuilding_id').setValue(null);
										base_form.findField('LpuUnit_id').setValue(null);
										base_form.findField('LpuSection_id').setValue(null);

										base_form.findField(object+'_id').setValue(object_id);
									}
								}
							}]
						}, {
							layout: 'form',
							width: 350,
							labelWidth: 125,
							items: [{
								enableKeyEvents: true,
								fieldLabel: langs('Тип подразделения'),
								hiddenName: 'LpuUnitType_id',
								lastQuery: '',
								width: 220,
								xtype: 'swlpuunittypecombo'
							}]
						}, {
							layout: 'form',
							width: 307,
							labelWidth: 150,
							items: [{
								comboSubject: 'PostOccupationType',
								fieldLabel: langs('Тип занятия должности'),
								hiddenName: 'WorkType_id',
								listWidth: 250,
								width: 147,
								xtype: 'swcommonsprcombo'
							}]
						}]
					}, {
						layout: 'column',
						items: [{
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Сотрудники ЛПУ'),
							style: "padding: 5px 0;",
							items: [{
								layout: 'column',
								width: 440,
								items: [{
									layout: 'form',
									labelWidth: 10,
									items: [{
										labelSeparator: '',
										name: 'medStaffFactDateRange',
										xtype: 'checkbox',
										listeners: {
											'check': function(checkbox, checked) {
												Ext.getCmp('MedStaffFactFilter').getForm().findField('MedStaffFact_date_range').setAllowBlank(!checked);
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									labelWidth: 135,
									items: [{
										fieldLabel: langs('Работающие на дату'),
										xtype: 'daterangefield',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
										width: 180,
										name: 'MedStaffFact_date_range'
									}]
								}]
							}, {
								layout: 'column',
								width: 440,
								items: [{
									layout: 'form',
									labelWidth: 10,
									items: [{
										labelSeparator: '',
										name: 'medStaffFactEndDateRange',
										xtype: 'checkbox',
										listeners: {
											'check': function(checkbox, checked) {
												Ext.getCmp('MedStaffFactFilter').getForm().findField('MedStaffFact_disDate_range').setAllowBlank(!checked);
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									labelWidth: 135,
									items: [{
										fieldLabel: langs('Уволенные в период'),
										xtype: 'daterangefield',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
										width: 180,
										name: 'MedStaffFact_disDate_range'
									}]
								}]
							}]
						}, {
							layout: 'form',
							items: [{
								style: "padding-left: 30px; padding-top: 10px;",
								xtype: 'button',
								id: 'swStaffBtnSearch',
								text: langs('Найти'),
								iconCls: 'search16',
								handler: function() {
									var form = Ext.getCmp('MedStaffFactFilter');
									if (form.validateSearchForm()) {
										form.doSearch();
									}
								}
							}, {
								style: "padding-left: 30px; padding-top: 10px;",
								xtype: 'button',
								id: 'swStaffBtnClean',
								text: langs('Сброс'),
								iconCls: 'reset16',
								handler: function() {
									var form = Ext.getCmp('MedStaffFactFilter');
									form.doReset();
									if (form.validateSearchForm()) {
										form.doSearch();
									}
								}
							}]
						}]
					}]
				}]
			});

// Табгрид - Сотрудники. Все уровни.
			var swStaffPanel = new sw.Promed.ViewFrame(
				{
					title:langs('Место работы сотрудника'),
					id: 'MedStaffFact',
					object: 'MedStaffFact',
					editformclassname: 'swMedStaffFactEditWindow',
					dataUrl: C_MP_GRIDDETAIL,
					height:303,
					toolbar: true,
					region: 'center',
					autoLoadData: false,
					stringfields:
						[
							{name: 'MedStaffFact_id', type: 'int', header: 'ID', key: true},
							{name: 'MedPersonal_id', type: 'int', hidden: true, isparams: true},
							{name: 'Person_id', type: 'int', hidden: true},
							{name: 'Server_id', type: 'int', hidden: true},
							{name: 'PersonSurName_SurName', type: 'string', hidden: true},
							{name: 'PersonFirName_FirName', type: 'string', hidden: true},
							{name: 'PersonSecName_SecName', type: 'string', hidden: true},
							{name: 'ArriveOrderNumber', type: 'string', hidden: true},
							{name: 'SURWorkPlace_id', type: 'string', hidden: true},
							{name: 'WorkPlaceCovidPeriod_id', type: 'int', hidden: true},
							{name: 'MedPersonal_TabCode', type: 'string', header: langs('Таб.№'), width: 50},
							{name: 'MedPersonalDLOPeriod_PCOD', type: 'string', header: langs('Код ЛЛО'), width: 75, hidden: getRegionNick() != 'msk'},
							{name: 'PersonBirthDay_BirthDay',  type: 'date', header: langs('Дата рождения'), width: 75},
							{id: 'autoexpand', name: 'MedPersonal_FIO',  type: 'string', header: langs('ФИО сотрудника')},
							{name: 'LpuSection_Name',  type: 'string', header: langs('Структурный элемент МО'), width: 200},
							{name: 'PostMed_Name',  type: 'string', header: langs('Должность'), width: 150},
							{name: 'MedStaffFact_Stavka',  type: 'float', header: langs('Ставка'), width: 55},
							{name: 'MedStaffFact_setDate',  type: 'date', header: langs('Начало работы'), width: 75},
							{name: 'MedStaffFact_disDate',  type: 'date', header: langs('Окончание работы'), width: 75},
							{name: 'SURWorkPlace_Name',  type: 'string', header: 'Место работы СУР', width: 220, hidden: getRegionNick() != 'kz'}
						],
					actions:
						[
							{name:'action_add', disabled: isMedPersView(), handler: function()
							{
								var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
								var Lpu_id = swLpuStructureViewForm.getNodeParentAttribute(node, 'Lpu', 'object_value');
								var LpuUnit_id = null;
								var LpuSection_id = null;
								var LpuBuilding_id = null;

								if ( node.attributes.object == 'LpuUnit' )
								{
									//Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
									LpuBuilding_id = node.parentNode.parentNode.attributes.object_value;
									LpuUnit_id = node.attributes.object_value;
								}
								else if ( node.attributes.object == 'LpuSection' )
								{
									if (swLpuStructureViewForm.getNodeTrueLevel(node) == 6)
									{
										//Lpu_id = node.parentNode.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
										LpuBuilding_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
										LpuUnit_id = node.parentNode.parentNode.attributes.object_value;
										// Если отделение брать с вышестоящего уровня, то так
										//LpuSection_id = node.parentNode.attributes.object_value;
										// Если с подотделения, то так  - поправил согласно ошибке #412 (http://172.19.61.14:81/issues/show/412)
										LpuSection_id = node.attributes.object_value;

									}
									else
									{
										//Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
										LpuBuilding_id = node.parentNode.parentNode.parentNode.attributes.object_value;
										LpuUnit_id = node.parentNode.attributes.object_value;
										LpuSection_id = node.attributes.object_value;
									}
								}
								else if ( node.attributes.object == 'Lpu' )
								{
									//Lpu_id = node.attributes.object_value;
								}
								else if ( node.attributes.object == 'LpuBuilding' )
								{
									//Lpu_id = node.parentNode.attributes.object_value;
									LpuBuilding_id = node.attributes.object_value;
								}
								var lpuStruct = new Array();
								lpuStruct.Lpu_id = String(Lpu_id) == 'null' ? null : String(Lpu_id);
								lpuStruct.LpuBuilding_id = String(LpuBuilding_id) == 'null' ? null : String(LpuBuilding_id);
								lpuStruct.LpuUnit_id = String(LpuUnit_id) == 'null' ? null : String(LpuUnit_id);
								lpuStruct.LpuSection_id = String(LpuSection_id) == 'null' ? null : String(LpuSection_id);
								lpuStruct.description = '';
								window.gwtBridge.runWorkPlaceEditor(getPromedUserInfo(), null, lpuStruct, function(result) {
									if ( Number(result) > 0 )
										frms.findById('MedStaffFact').ViewGridPanel.getStore().reload();
								});
								//frms.MedStaffFactEditWindow.show({callback: frms.findById('MedStaffFact').refreshRecords, owner: frms.findById('MedStaffFact'), fields: {action: 'addinstructure', Lpu_id: Lpu_id, LpuUnit_id: LpuUnit_id, LpuSection_id: LpuSection_id}});
							}
							},
							{name:'action_edit', text:(isMedPersView())?langs('Просмотр'):langs('Изменить'), handler: function()
							{
								var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
								var Lpu_id = swLpuStructureViewForm.getNodeParentAttribute(node, 'Lpu', 'object_value');
								var LpuUnit_id = null;
								var LpuSection_id = null;
								var LpuBuilding_id = null;

								if ( node.attributes.object == 'LpuUnit' )
								{
									//Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
									LpuBuilding_id = node.parentNode.parentNode.attributes.object_value;
									LpuUnit_id = node.attributes.object_value;
								}
								else if ( node.attributes.object == 'LpuSection' )
								{
									if (swLpuStructureViewForm.getNodeTrueLevel(node) == 6)
									{
										//Lpu_id = node.parentNode.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
										LpuBuilding_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
										LpuUnit_id = node.parentNode.parentNode.attributes.object_value;
										// Если отделение брать с вышестоящего уровня, то так
										//LpuSection_id = node.parentNode.attributes.object_value;
										// Если с подотделения, то так  - поправил согласно ошибке #412 (http://172.19.61.14:81/issues/show/412)
										LpuSection_id = node.attributes.object_value;

									}
									else
									{
										//Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
										LpuBuilding_id = node.parentNode.parentNode.parentNode.attributes.object_value;
										LpuUnit_id = node.parentNode.attributes.object_value;
										LpuSection_id = node.attributes.object_value;
									}
								}
								else if ( node.attributes.object == 'Lpu' )
								{
									//Lpu_id = node.attributes.object_value;
								}
								else if ( node.attributes.object == 'LpuBuilding' )
								{
									//Lpu_id = node.parentNode.attributes.object_value;
									LpuBuilding_id = node.attributes.object_value;
								}
								var lpuStruct = new Array();
								lpuStruct.Lpu_id = String(Lpu_id) == 'null' ? null : String(Lpu_id);
								lpuStruct.LpuBuilding_id = String(LpuBuilding_id) == 'null' ? null : String(LpuBuilding_id);
								lpuStruct.LpuUnit_id = String(LpuUnit_id) == 'null' ? null : String(LpuUnit_id);
								lpuStruct.LpuSection_id = String(LpuSection_id) == 'null' ? null : String(LpuSection_id);
								lpuStruct.description = '';
								var MedPersonal_id = swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().data.MedPersonal_id;
								var MedPersonal_TabCode = swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().data.MedPersonal_TabCode;
								var MedPersonal_FIO = swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().data.MedPersonal_FIO;
								var MedStaffFact_id = swStaffPanel.ViewGridPanel.getSelectionModel().getSelected().data.MedStaffFact_id;
								var scrollPos = swStaffPanel.getGrid().getView().getScrollState();
								window.gwtBridge.runWorkPlaceEditor(getPromedUserInfo(), String(MedStaffFact_id), lpuStruct, function(result) {
									if ( Number(result) > 0 ){
										frms.findById('MedStaffFact').ViewGridPanel.getStore().reload({
											callback:function(){
												swStaffPanel.getGrid().getView().scroller.dom.scrollTop = scrollPos.top;
											}
										});
									}
								});
								//frms.MedStaffFactEditWindow.show({callback: frms.findById('MedStaffFact').refreshRecords, owner: frms.findById('MedStaffFact'), fields: {MedPersonal_id: MedPersonal_id, MedPersonal_TabCode: MedPersonal_TabCode, MedPersonal_FIO: MedPersonal_FIO, MedStaffFact_id: MedStaffFact_id, action: 'edit'}});
							}
							},
							{name:'action_view', disabled: true, hidden: true,  handler: function() {}},
							{name:'action_delete', disabled: isMedPersView(),
								handler: function() {
									if ( this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected() )
									{
										sw.swMsg.show({
											icon: Ext.MessageBox.QUESTION,
											msg: langs('Вы хотите удалить запись?'),
											title: langs('Подтверждение'),
											buttons: Ext.Msg.YESNO,
											fn: function(buttonId, text, obj)
											{
												if ('yes' == buttonId)
												{
													var row = this.swStaffPanel.ViewGridPanel.getSelectionModel().getSelected();
													var staff_id = row.get('MedStaffFact_id');
													window.gwtBridge.deleteWorkPlace(getPromedUserInfo(), String(staff_id), function(result) {
														this.swStaffPanel.ViewGridPanel.getStore().reload();
													}.createDelegate(this));
												}
											}.createDelegate(this)
										});
									}
								}.createDelegate(this)
							},
							{name:'action_refresh'},
							{name:'action_print', hidden: isMedPersView()}
						],
					onRowSelect: function(sm, index, record) {
						var gridPanel = this;
						var isRecord = (record && !Ext.isEmpty(record.get('MedStaffFact_id')));

						if (gridPanel.surActions) {
							gridPanel.surActions.edit.setDisabled(!isRecord || frms == 'view');
							gridPanel.surActions.view.setDisabled(!isRecord || Ext.isEmpty(record.get('SURWorkPlace_id')));
							gridPanel.surActions.delete.setDisabled(!isRecord || frms == 'view' || Ext.isEmpty(record.get('SURWorkPlace_id')));
						}
						
						gridPanel.getAction('action_covid').setText(!!record.get('WorkPlaceCovidPeriod_id') ? '<b>COVID</b>' : 'COVID');
					}
				});
			swStaffPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swStaffPanel);}.createDelegate(this));

			this.swStaffPanel = swStaffPanel;

			var swStaffTTFilterPanel = new Ext.form.FormPanel({
				id: 'MedStaffFactTTFilter',
				labelAlign: 'right',
				region: 'north',
				height: 30,
				frame: true,
				validateSearchForm: function() {
					var form = this;
					var base_form = this.getForm();
					var msg = ERR_INVFIELDS_MSG;

					if (!base_form.isValid()) {
						if (!base_form.findField('Staff_Date_range').validate() || !base_form.findField('Staff_endDate_range').validate()) {
							msg = langs('Требуется указать период!');
						}
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function()
							{
								form.getFirstInvalidEl().focus(false);
							},
							icon: Ext.Msg.WARNING,
							msg: msg,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}
					return true;
				},
				doReset: function() {
					this.getForm().reset();
				},
				doSearch: function() {
					var gridPanel = Ext.getCmp('MedStaffFactTT');
					var base_form = Ext.getCmp('MedStaffFactTTFilter').getForm();

					var params = base_form.getValues();
					if (gridPanel.getGrid().getStore().baseParams.isClose) {
						params.isClose = gridPanel.getGrid().getStore().baseParams.isClose;
					}
					gridPanel.getGrid().getStore().baseParams = params;

					gridPanel.getGrid().getStore().load();
				},
				items: [{
					xtype: 'fieldset',
					title: langs('Фильтр'),
					style: 'padding: 5px;',
					autoHeight: true,
					collapsible: true,
					collapsed: true,
					keys: [{
						key: Ext.EventObject.ENTER,
						fn: function(e) {
							var form = this.findById('MedStaffFactTTFilter');
							if (form.validateSearchForm()) {
								form.doSearch();
							}
						}.createDelegate(this),
						stopEvent: true
					}, {
						ctrl: true,
						fn: function(inp, e) {
							var form = this.findById('MedStaffFactTTFilter');
							form.doReset();
						}.createDelegate(this),
						key: 188,
						scope: this,
						stopEvent: true
					}],
					listeners:{
						expand:function () {
							this.ownerCt.setHeight(140);
							this.ownerCt.ownerCt.syncSize();
						},
						collapse:function () {
							this.ownerCt.setHeight(30);
							this.ownerCt.ownerCt.syncSize();
						}
					},
					items: [{
						name: 'LpuBuilding_id',
						xtype: 'hidden'
					}, {
						name: 'LpuUnit_id',
						xtype: 'hidden'
					}, {
						name: 'LpuSection_id',
						xtype: 'hidden'
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							width: 285,
							labelWidth: 80,
							items: [{
								fieldLabel: langs('Уровень ЛПУ'),
								hiddenName: 'LpuStructure_id',
								width: 200,
								xtype: 'swlpustructureelementcombo',
								listeners: {
									'select': function(combo,record,value) {
										var base_form = Ext.getCmp('MedStaffFactTTFilter').getForm();

										var object = record.get('LpuStructure_Nick');
										var object_id = record.get('LpuStructureElement_id');

										if (!object.inlist(['LpuBuilding','LpuUnit','LpuSection'])) {
											return;
										}

										base_form.findField('LpuBuilding_id').setValue(null);
										base_form.findField('LpuUnit_id').setValue(null);
										base_form.findField('LpuSection_id').setValue(null);

										base_form.findField(object+'_id').setValue(object_id);
									}
								}
							}]
						}, {
							layout: 'form',
							width: 330,
							labelWidth: 95,
							items: [{
								fieldLabel: langs('Должность'),
								hiddenName: 'PostMed_id',
								width: 230,
								xtype: 'swpostmedlocalcombo'
							}]
						}, {
							layout: 'form',
							width: 320,
							labelWidth: 80,
							items: [{
								hiddenName: 'MedicalCareKind_id',
								valueField: 'MedicalCareKind_id',
								displayField: 'MedicalCareKind_Name',
								fieldLabel: langs('Вид МП'),
								store: new Ext.data.SimpleStore({
									autoLoad: true,
									data: [
										[ 1, 1, langs('Первичная медико-санитарная помощь') ],
										[ 2, 2, langs('Специализированная медицинская помощь') ],
										[ 3, 3, langs('Скорая медицинская помощь') ],
										[ 4, 4, langs('Реабилитационная медицинская помощь') ],
										[ 5, 5, langs('Иное') ]
									],
									fields: [
										{ name: 'MedicalCareKind_id', type: 'int'},
										{ name: 'MedicalCareKind_Code', type: 'int'},
										{ name: 'MedicalCareKind_Name', type: 'string'}
									],
									key: 'MedicalCareKind_id',
									sortInfo: { field: 'MedicalCareKind_Code' }
								}),
								editable: false,
								width: 230,
								xtype: 'swbaselocalcombo'
							}]
						}]
					}, {
						layout: 'column',
						items: [{
							xtype: 'fieldset',
							autoHeight: true,
							title: langs('Должность'),
							style: "padding: 5px 0;",
							items: [{
								layout: 'column',
								width: 440,
								items: [{
									layout: 'form',
									labelWidth: 10,
									items: [{
										labelSeparator: '',
										name: 'medStaffFactDateRange',
										xtype: 'checkbox',
										listeners: {
											'check': function(checkbox, checked) {
												Ext.getCmp('MedStaffFactTTFilter').getForm().findField('Staff_Date_range').setAllowBlank(!checked);
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									labelWidth: 120,
									items: [{
										fieldLabel: langs('Создана в период'),
										xtype: 'daterangefield',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
										width: 180,
										name: 'Staff_Date_range'
									}]
								}]
							}, {
								layout: 'column',
								width: 440,
								items: [{
									layout: 'form',
									labelWidth: 10,
									items: [{
										labelSeparator: '',
										name: 'medStaffFactEndDateRange',
										xtype: 'checkbox',
										listeners: {
											'check': function(checkbox, checked) {
												Ext.getCmp('MedStaffFactTTFilter').getForm().findField('Staff_endDate_range').setAllowBlank(!checked);
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									labelWidth: 120,
									items: [{
										fieldLabel: langs('Закрыта в период'),
										xtype: 'daterangefield',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
										width: 180,
										name: 'Staff_endDate_range'
									}]
								}]
							}]
						}, {
							layout: 'form',
							items: [{
								style: "padding-left: 30px; padding-top: 10px;",
								xtype: 'button',
								id: 'swStaffBtnSearch',
								text: langs('Найти'),
								iconCls: 'search16',
								handler: function() {
									var form = Ext.getCmp('MedStaffFactTTFilter');
									if (form.validateSearchForm()) {
										form.doSearch();
									}
								}
							}, {
								style: "padding-left: 30px; padding-top: 10px;",
								xtype: 'button',
								id: 'swStaffBtnClean',
								text: langs('Сброс'),
								iconCls: 'reset16',
								handler: function() {
									var form = Ext.getCmp('MedStaffFactTTFilter');
									form.doReset();
									if (form.validateSearchForm()) {
										form.doSearch();
									}
								}
							}]
						}]
					}]
				}]
			});

			this.swStaffPanel = swStaffPanel;

			var swFDServicedByRemoteConsultPanelEditLine = new Ext.form.FormPanel({
				id:'swFDServicedByRemoteConsultPanelEditLine',
				border: false,
				region: 'north',
				layout: 'column',
//		status: 'empty',
				height: 30,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				items:
					[
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							columnWidth: 0.20,
							labelWidth: 30,
							items: [{
								hiddenName:'Lpu_id',
								displayField:'Lpu_Nick',
								valueField: 'Lpu_id',
								fieldLabel: langs('ЛПУ'),
								triggerAction: 'all',
								resizable : false,
								mode: 'local',
								editable: false,
								parentObj: this,
								listeners:
								{
									beforeselect:function(combo, record){
										if (record.get('Lpu_id')&&(record.get('Lpu_id')>0)) {
											swFDServicedByRemoteConsultPanelEditLine.findById('swFDServicedByRemoteConsultPanelEditLine_saveButton').setDisabled(true);
											var editLineForm = swFDServicedByRemoteConsultPanelEditLine.getForm();
											editLineForm.findField('MedService_id').setValue(null);
											editLineForm.findField('MedService_id').setDisabled(false);
											editLineForm.findField('MedService_id').getStore().load({params:{'Lpu_id':record.get('Lpu_id')}});
										}
									}
								},
								store: new Ext.data.JsonStore({
									url: '/?c=LpuStructure&m=getLpuWithUnservedDiagMedService',
									fields: [
										{name: 'Lpu_id', type: 'int'},
										{name: 'Lpu_Nick', type: 'string'}
									],
									key: 'Lpu_id',
									sortInfo: {
										field: 'Lpu_Nick'
									}
								}),
								xtype:'swbaselocalcombo'
							}]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							columnWidth: 0.78,
							width: '100%',
							labelWidth: 50,
							listeners:
							{
								resize: function(panel) {
									var combo = panel.items.items[0];
									if (combo&&((panel.getSize().width-100)>120)) {
										combo.setWidth(combo.ownerCt.getSize().width-100);
									}
								}
							},
							items: [{
								hiddenName:'MedService_id',
								displayField:'MedService_FullName',
								valueField: 'MedService_id',
								fieldLabel: langs('Служба'),
								triggerAction: 'all',
								mode: 'local',
								editable: false,
								parentObj: this,
								disabled: true,
								listeners:{
									beforeselect:function(combo, record){
										if (record.get('MedService_id')&&(record.get('MedService_id')>0)) {
											swFDServicedByRemoteConsultPanelEditLine.findById('swFDServicedByRemoteConsultPanelEditLine_saveButton').setDisabled(false);
										} else {
											swFDServicedByRemoteConsultPanelEditLine.findById('swFDServicedByRemoteConsultPanelEditLine_saveButton').setDisabled(true);
										}
									}
								},
								store: new Ext.data.JsonStore({
									url: '/?c=LpuStructure&m=getUnservedDiagMedService',
									fields: [
										{name: 'MedService_id', type: 'int'},
										{name: 'MedService_FullName', type: 'string'}
									],
									sortInfo: {
										field: 'MedService_FullName'
									}
								}),
								xtype:'swbaselocalcombo'
							}]
						},
						{
							layout: 'form',
							border: false,
							//columnWidth: 1,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							width: 100,
							items: [
								new Ext.Button({
									id:'swFDServicedByRemoteConsultPanelEditLine_saveButton',
									text: langs('Сохранить'),
									iconCls : 'save16',
									disabled: true,
									handler: function()
									{
										Ext.Ajax.request({
											callback: function(options, success, response) {
												if ( success ) {
													var editLineForm = swFDServicedByRemoteConsultPanelEditLine.getForm();
													editLineForm.findField('MedService_id').setValue(null);
													editLineForm.findField('Lpu_id').setValue(null);
													swFDServicedByRemoteConsultPanelEditLine.setDisabled(true);
													swFDServicedByRemoteConsultGrid.setDisabled(false);
													swFDServicedByRemoteConsultGrid.getGrid().getStore().load();
													swFDServicedByRemoteConsultGrid.getGrid().getStore().load({params:{MedService_id:swFDServicedByRemoteConsultPanel.MedService_id}});
												}
												else {
													sw.swMsg.alert(langs('Ошибка'), langs('При сохранении службы произошла ошибка'));
												}
											},
											params: {
												MedService_FDid: swFDServicedByRemoteConsultPanelEditLine.getForm().findField('MedService_id').getValue(),
												MedService_RCCid: swFDServicedByRemoteConsultPanel.MedService_id
											},
											url: '/?c=LpuStructure&m=saveLinkFDServiceToRCCService'
										});
									}
								})
							]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							width: 100,
							items: [
								new Ext.Button({
									text: langs('Отменить'),
									id:'swFDServicedByRemoteConsultPanelEditLine_cancelButton',
									iconCls : 'cancel16',
									disabled: false,
									handler: function()
									{
										var editLineForm = swFDServicedByRemoteConsultPanelEditLine.getForm();
										editLineForm.findField('MedService_id').setValue(null);
										editLineForm.findField('Lpu_id').setValue(null);
										swFDServicedByRemoteConsultPanelEditLine.setDisabled(true);
										swFDServicedByRemoteConsultGrid.setDisabled(false);
									}
								})
							]
						}]
			});

			var swFDServicedByRemoteConsultGrid = new sw.Promed.ViewFrame(
				{
					title:langs('Список обслуживаемых служб ФД'),
					id: 'FDServicedByRemoteConsult_Grid',
					//editformclassname: 'swMedStaffFactEditWindow',
					dataUrl: '/?c=LpuStructure&m=getFDServicesConnectedToRCCService',
					toolbar: true,
					region: 'center',
					autoLoadData: false,
					stringfields:
						[
							{name: 'MedServiceLink_id', type: 'int', header: 'ID', key: true},
							{name: 'MedService_lid', type: 'int', hidden: true},
							{name: 'Lpu_id', type: 'int', hidden: true},
							{name: 'MedService_Name', type: 'string', width: 150, hidden: false, header: langs('Служба')},
							{name: 'Lpu_Nick', type: 'string', width: 150, hidden: false, header: langs('ЛПУ')},
							{name: 'LpuUnit_Name', type: 'string', width: 250, hidden: false, header: langs('Группа отделений')},
							{name: 'LpuSection_Name', type: 'string', width: 150, hidden: false, header: langs('Отделения')},
							{name: 'LpuBuilding_Name', type: 'string', hidden: false, header: langs('Подразделение'),id: 'autoexpand'}
						],
					actions:
						[
							{
								name:'action_add',  handler: function(){
								var editLineForm = swFDServicedByRemoteConsultPanelEditLine.getForm();
								swFDServicedByRemoteConsultPanelEditLine.setDisabled(false)
								swFDServicedByRemoteConsultGrid.setDisabled(true);
								editLineForm.findField('MedService_id').setDisabled(true);
								editLineForm.findField('Lpu_id').getStore().load();
							}
							},
							{
								name:'action_edit', hidden: true, disabled: true,
								//Пока нет надобности
								handler: function(){
									var editLineForm = swFDServicedByRemoteConsultPanelEditLine.getForm(),
										cell = swFDServicedByRemoteConsultGrid.getGrid().getSelectionModel().getSelected();

									if ( !cell) {
										return false;
									}
									return;
									swFDServicedByRemoteConsultPanelEditLine.setDisabled(false)
									swFDServicedByRemoteConsultGrid.setDisabled(true);
									editLineForm.findField('MedService_id').setDisabled(true);
									editLineForm.findField('Lpu_id').getStore().load({
										callback: function(){
											editLineForm.findField('MedService_id').getStore().load({
												params: {
													Lpu_id: cell.get('Lpu_id')
												}
											})
										}
									});


								}
							},
							{
								name:'action_delete', handler: function() {
								Ext.Ajax.request({
									callback: function(options, success, response) {
										if ( success ) {
											swFDServicedByRemoteConsultGrid.getGrid().getStore().load({params:{MedService_id:swFDServicedByRemoteConsultPanel.MedService_id}});
										}
										else {
											sw.swMsg.alert(langs('Ошибка'), langs('При удалении связи служб произошла ошибка'));
										}
									},
									params: {
										MedServiceLink_id: swFDServicedByRemoteConsultGrid.getGrid().getSelectionModel().getSelected().get('MedServiceLink_id')
									},
									url: '/?c=LpuStructure&m=deleteLinkFDServiceToRCCService'
								});
							}
							},
							{
								name:'action_view', disabled: true, hidden: true
							},
							{
								name:'action_refresh', handler: function(){
								swFDServicedByRemoteConsultGrid.getGrid().getStore().load({params:{MedService_id:swFDServicedByRemoteConsultPanel.MedService_id}});
							}
							},
							{
								name:'action_print', disabled: true, hidden: true
							}
						]
				});

			var swFDServicedByRemoteConsultPanel = new Ext.Panel({
				id:'swFDServicedByRemoteConsultPanel',
				MedService_id: 0,
				items: [
					swFDServicedByRemoteConsultPanelEditLine,
					swFDServicedByRemoteConsultGrid
				],
				layout: 'border',
				region: 'center',
				split: true
			});

			var swBSMEForenMedCorpMedServicesPanel =  new Ext.form.FormPanel({
				id:'swBSMEForenMedCorpMedServicesPanel',
				MedService_id: 0,
				region: 'center',
				layout: 'fit',
				split: true,
				bodyStyle: 'padding-left: 20px; padding-top: 40px',
				comboNames: [
					'MedService_ForenCrim_id',
					'MedService_ForenChem_id',
					'MedService_ForenHist_id',
					'MedService_ForenBio_id'
				],
				loadComboStores: function() {
					var name,
						field,
						store,
						form = this.getForm();

					for (name in this.comboNames) {
						if (this.comboNames.hasOwnProperty(name)) {
							field = form.findField(this.comboNames[name]);
							if (field && (typeof field.getStore == 'function')) {
								store = field.getStore();
								if (store && (typeof store.load == 'function')) {
									store.load({params: {MedServiceType_Code: field.MedServiceType_Code},callback: function(){
										field.setValue(field.getValue);
									}});
								}
							}
						}
					}
					form.findField();
				},
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						columnWidth: 1,
						buttonAlign : 'left',
						labelWidth: 300,
						items: [{
							width: 600,
							MedServiceType_Code: 38,
							hiddenName:'MedService_ForenCrim_id',
							displayField:'MedService_Nick',
							valueField: 'MedService_id',
							fieldLabel: langs('Служба медико-криминалистического отделения'),
							triggerAction: 'all',
							mode: 'local',
							editable: false,
							parentObj: this,
							allowBlank: false,
							listeners:{},
							store: new Ext.data.JsonStore({
								url: '/?c=LpuStructure&m=getLpuWithMedServiceList',
								fields: [
									{name: 'MedService_id', type: 'int'},
									{name: 'MedService_Nick', type: 'string'}
								],
								sortInfo: {
									field: 'MedService_Nick'
								}
							}),
							xtype:'swbaselocalcombo'
						},{
							width: 600,
							hiddenName:'MedService_ForenChem_id',
							MedServiceType_Code: 37,
							displayField:'MedService_Nick',
							valueField: 'MedService_id',
							fieldLabel: langs('Служба судебно-химического отделения'),
							triggerAction: 'all',
							mode: 'local',
							editable: false,
							parentObj: this,
							allowBlank: false,
							listeners:{},
							store: new Ext.data.JsonStore({
								url: '/?c=LpuStructure&m=getLpuWithMedServiceList',
								fields: [
									{name: 'MedService_id', type: 'int'},
									{name: 'MedService_Nick', type: 'string'}
								],
								sortInfo: {
									field: 'MedService_Nick'
								}
							}),
							xtype:'swbaselocalcombo'
						},{
							width: 600,
							hiddenName:'MedService_ForenHist_id',
							MedServiceType_Code: 39,
							displayField:'MedService_Nick',
							valueField: 'MedService_id',
							fieldLabel: langs('Служба судебно-гистологического отделения'),
							triggerAction: 'all',
							mode: 'local',
							editable: false,
							parentObj: this,
							allowBlank: false,
							listeners:{},
							store: new Ext.data.JsonStore({
								url: '/?c=LpuStructure&m=getLpuWithMedServiceList',
								fields: [
									{name: 'MedService_id', type: 'int'},
									{name: 'MedService_Nick', type: 'string'}
								],
								sortInfo: {
									field: 'MedService_Nick'
								}
							}),
							xtype:'swbaselocalcombo'
						},{
							width: 600,
							hiddenName:'MedService_ForenBio_id',
							MedServiceType_Code: 36,
							displayField:'MedService_Nick',
							valueField: 'MedService_id',
							fieldLabel: langs('Служба судебно-биологического отделения'),
							triggerAction: 'all',
							mode: 'local',
							editable: false,
							parentObj: this,
							allowBlank: false,
							listeners:{},
							store: new Ext.data.JsonStore({
								url: '/?c=LpuStructure&m=getLpuWithMedServiceList',
								fields: [
									{name: 'MedService_id', type: 'int'},
									{name: 'MedService_Nick', type: 'string'}
								],
								sortInfo: {
									field: 'MedService_Nick'
								}
							}),
							xtype:'swbaselocalcombo'
						}],
						buttons: [{
							xtype: 'button',
							text: langs('Сохранить'),
							iconCls : 'save16',
							handler: function()
							{
								var base_form = swBSMEForenMedCorpMedServicesPanel.getForm();

								if ( !base_form.isValid() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											swBSMEForenMedCorpMedServicesPanel.getFirstInvalidEl().focus(false);
										},
										icon: Ext.Msg.WARNING,
										msg: ERR_INVFIELDS_MSG,
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}

								if (!swBSMEForenMedCorpMedServicesPanel.MedService_id) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										msg: langs('Не указан идентификатор службы'),
										title: ERR_INVFIELDS_TIT
									});
								}

								var name,
									field,
									params = { MedService_id: swBSMEForenMedCorpMedServicesPanel.MedService_id };

								for (name in swBSMEForenMedCorpMedServicesPanel.comboNames) {
									if (swBSMEForenMedCorpMedServicesPanel.comboNames.hasOwnProperty(name)) {
										field = base_form.findField(swBSMEForenMedCorpMedServicesPanel.comboNames[name]);
										if (field && (typeof field.getValue == 'function')) {
											params[swBSMEForenMedCorpMedServicesPanel.comboNames[name]] = field.getValue();
										}
									}
								}


								Ext.Ajax.request({
									callback: function(options, success, response) {
										if ( success ) {
											var resp = JSON.parse(response.responseText);
											resp = resp[0];
											if (!resp || !resp['success']) {
												if (resp['Error_Msg']) {
													sw.swMsg.alert(langs('Ошибка'), langs('При сохранении службы произошла ошибка')+resp['Error_Msg']);
												} else {
													sw.swMsg.alert(langs('Ошибка'), langs('При сохранении службы произошла ошибка'));
												}
											} else {
												sw.swMsg.alert(langs('Успех'), langs('Список обслуживающих отделений успешно сохранен'));
											}
										}
										else {
											sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
										}
									},
									params: params,
									url: '/?c=LpuStructure&m=saveForenCorpServingMedServices'
								});
							}
						}]
					}]
				}]
			});
			var swBSMEForenMedHistMedServicesPanel =  new Ext.form.FormPanel({
				id:'swBSMEForenMedHistMedServicesPanel',
				MedService_id: 0,
				region: 'center',
				layout: 'fit',
				split: true,
				bodyStyle: 'padding-left: 20px; padding-top: 40px',
				comboNames: [
					'MedService_ForenChem_id'
				],
				loadComboStores: function() {
					var name,
						field,
						store,
						form = this.getForm();

					for (name in this.comboNames) {
						if (this.comboNames.hasOwnProperty(name)) {
							field = form.findField(this.comboNames[name]);
							if (field && (typeof field.getStore == 'function')) {
								store = field.getStore();
								if (store && (typeof store.load == 'function')) {
									store.load({params: {MedServiceType_Code: field.MedServiceType_Code},callback: function(){
										field.setValue(field.getValue);
									}});
								}
							}
						}
					}
					form.findField();
				},
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						columnWidth: 1,
						buttonAlign : 'left',
						labelWidth: 300,
						items: [{
							width: 600,
							hiddenName:'MedService_ForenChem_id',
							MedServiceType_Code: 37,
							displayField:'MedService_Nick',
							valueField: 'MedService_id',
							fieldLabel: langs('Служба судебно-химического отделения'),
							triggerAction: 'all',
							mode: 'local',
							editable: false,
							parentObj: this,
							allowBlank: false,
							listeners:{},
							store: new Ext.data.JsonStore({
								url: '/?c=LpuStructure&m=getLpuWithMedServiceList',
								fields: [
									{name: 'MedService_id', type: 'int'},
									{name: 'MedService_Nick', type: 'string'}
								],
								sortInfo: {
									field: 'MedService_Nick'
								}
							}),
							xtype:'swbaselocalcombo'
						}],
						buttons: [{
							xtype: 'button',
							text: langs('Сохранить'),
							iconCls : 'save16',
							handler: function()
							{
								var base_form = swBSMEForenMedHistMedServicesPanel.getForm();

								if ( !base_form.isValid() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											swBSMEForenMedHistMedServicesPanel.getFirstInvalidEl().focus(false);
										},
										icon: Ext.Msg.WARNING,
										msg: ERR_INVFIELDS_MSG,
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}

								if (!swBSMEForenMedHistMedServicesPanel.MedService_id) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										msg: langs('Не указан идентификатор службы'),
										title: ERR_INVFIELDS_TIT
									});
								}

								var name,
									field,
									params = { MedService_id: swBSMEForenMedHistMedServicesPanel.MedService_id };

								for (name in swBSMEForenMedHistMedServicesPanel.comboNames) {
									if (swBSMEForenMedHistMedServicesPanel.comboNames.hasOwnProperty(name)) {
										field = base_form.findField(swBSMEForenMedHistMedServicesPanel.comboNames[name]);
										if (field && (typeof field.getValue == 'function')) {
											params[swBSMEForenMedHistMedServicesPanel.comboNames[name]] = field.getValue();
										}
									}
								}


								Ext.Ajax.request({
									callback: function(options, success, response) {
										if ( success ) {
											var resp = JSON.parse(response.responseText);
											resp = resp[0];
											if (!resp || !resp['success']) {
												if (resp['Error_Msg']) {
													sw.swMsg.alert(langs('Ошибка'), langs('При сохранении службы произошла ошибка')+resp['Error_Msg']);
												} else {
													sw.swMsg.alert(langs('Ошибка'), langs('При сохранении службы произошла ошибка'));
												}
											} else {
												sw.swMsg.alert(langs('Успех'), langs('Список обслуживающих отделений успешно сохранен'));
											}
										}
										else {
											sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
										}
									},
									params: params,
									url: '/?c=LpuStructure&m=saveForenHistServingMedServices'
								});
							}
						}]
					}]
				}]
			});

			//Форма зон обслуживания подстанций СМП
//	var territoryPanel = new  sw.Promed.ViewFrame({
//		title:'Обслуживаемые территории подразделения',
//		id: 'LpuBuildingStreet',
//		object: 'LpuBuildingKLHouseCoordsRel',
//		editformclassname: 'swLpuBuildingStreetEditWindow',
//		dataUrl: C_LPUBUILDINGSTREET_GET,
//		height:303,
//		toolbar: true,
//		autoLoadData: false,
//		stringfields:
//		[
//			{name: 'LpuBuildingStreet_id', type: 'int', header: 'ID', key: true},
//			{name: 'LpuBuilding_id', hidden: true, isparams: true},
//			{name: 'KLCountry_id', hidden: true, isparams: false},
//			{name: 'KLRGN_id', hidden: true, isparams: false},
//			{name: 'KLSubRGN_id', hidden: true, isparams: false},
//			{name: 'KLCity_id', hidden: true, isparams: false},
//			{name: 'KLTown_id', hidden: true, isparams: false},
//			{name: 'KLTown_Name', type: 'string', header: 'Населенный пункт', width: 200},
//			{name: 'KLStreet_id', hidden: true, isparams: false},
//			{name: 'KLStreet_Name', type: 'string', header: 'Улица', width: 200},
//			{id: 'autoexpand',name: 'LpuBuildingStreet_HouseSet', type: 'string', header: 'Номера домов'}
//		],
//		actions:
//		[
//			{name:'action_add'},
//			{name:'action_edit'},
//			{name:'action_view'},
//			{name:'action_delete'},
//			{name:'action_refresh'},
//			{name:'action_print'}
//		]
//	});

			// Грид со списком территорий, обслуживаемых подразделением
			var territoryServicePanel = new sw.Promed.ViewFrame({
				title: langs('Территории, обслуживаемые подразделением'),
				id: 'LpuBuildingTerritoryService',
				object: 'LpuBuildingStreet',
				//object: 'LpuBuildingTerritoryServiceRel',
				editformclassname: 'swLpuBuildingStreetEditWindow',
				dataUrl: C_LPUBUILDINGTERRITORYSERVICE_LOAD,
				height: 303,
				toolbar: true,
				autoLoadData: false,
				stringfields: [
					{name: 'LpuBuildingStreet_id', key: true, isparams: true},
					/*{name: 'LpuBuildingTerritoryServiceRel_id', key: true},*/
					{name: 'LpuBuilding_id', hidden: true, isparams: false},

					/*{name: 'TerritoryService_id', hidden: true, isparams: true},*/
					{name: 'KLCountry_id', hidden: true, isparams: false},
					{name: 'KLRegion_id', hidden: true, isparams: false},
					{name: 'KLSubRegion_id', hidden: true, isparams: false},
					{name: 'KLCity_id', hidden: true, isparams: false},
					{name: 'KLTown_id', hidden: true, isparams: false},
					{name: 'KLStreet_id', hidden: true, isparams: true},
					{name: 'KLSubRgn_Name', header: 'Район', isparams: false},
					{name: 'KLCity_Name', header: 'Город', isparams: false},
					{name: 'KLTown_Name', header: 'Нас. пункт', isparams: false},

					{name: 'KLStreet_FullName', header: langs('Улица'), isparams: false},

					{name: 'LpuBuildingStreet_IsAll', header: langs('Вся территория'), isparams: false, renderer: function(value, cell, row){
						return (value == 2 ? '<div style="text-align: center;">&#10004;</div>' : '');
					}},

					{name: 'LpuBuildingStreet_HouseSet', header: langs('Номера домов'), isparams: false}
					/*
					 {name: 'TerritoryService_All', header: langs('Вся территория'), isparams: false, renderer: function(value, cell, row){
					 return (value == 2 ? '&#10004;' : '');
					 }},
					 {name: 'TerritoryServiceHouse_id', hidden: true, isparams: false},
					 {name: 'TerritoryServiceHouse_Name', header: langs('Номер'), isparams: false},

					 {name: 'TerritoryServiceHouseRange_id', hidden: true, isparams: false},
					 {name: 'TerritoryServiceHouseRange_From', hidden: true, isparams: false},
					 {name: 'TerritoryServiceHouseRange_To', hidden: true, isparams: false},
					 {name: 'TerritoryServiceHouseRange_OddEven', header: langs('Диапазон'), renderer: function(value, cell, row){
					 var str = '',
					 div = '',
					 odd_even = (value == 1 ? 'Н ' : (value == 2 ? 'Ч ' : ''));
					 if (row.data && row.data.TerritoryServiceHouseRange_From) {
					 str += row.data.TerritoryServiceHouseRange_From;
					 div = '-';
					 }
					 if (row.data && row.data.TerritoryServiceHouseRange_To) {
					 str += div + row.data.TerritoryServiceHouseRange_To;
					 }
					 return odd_even + str;
					 }}
					 */
				],
				actions: [
					{name: 'action_add'},
					{name: 'action_edit'},
					{name: 'action_view'},
					{name: 'action_delete'},
					{name: 'action_refresh'},
					{name: 'action_print'}
				]
			});
			// Грид содержит список правил контроля вызовов с превышением времени назначения на бригаду.
			var activeCallRulePanel = new sw.Promed.ViewFrame({
				title: langs('Правила контроля вызовов с превышением времени назначения на бригаду'),
				id: 'activeCallRulePanel',
				object: 'ActiveCallRule',
				style: "padding-bottom: 10px;",
				editformclassname: 'swCmpActiveCallRulesEditWindow',
				dataUrl: C_ACTIVECMPCALLRULES_LOAD,
				height: 303,
				toolbar: true,
				autoLoadData: false,
				stringfields: [
					{name: 'LpuBuilding_id', hidden: true, isparams: true},
					{name: 'ActiveCallRule_id', hidden: true, key: true, isparams: true},
					{name: 'ActiveCallRule_From', header: langs('Возраст с')},
					{name: 'ActiveCallRule_To', header: langs('Возраст по')},
					{name: 'ActiveCallRule_UrgencyFrom', header: langs('Срочность с')},
					{name: 'ActiveCallRule_UrgencyTo', header: langs('Срочность по')},
					{name: 'ActiveCallRule_WaitTime', header: langs('Время ожидания для звонка, мин')}
					/*{name: 'LpuBuildingStreet_IsAll', header: langs('Вся территория'), isparams: false, renderer: function(value, cell, row){
					 return (value == 2 ? '<div style="text-align: center;">&#10004;</div>' : '');*/
				],
				actions: [
					{name: 'action_add'},
					{name: 'action_edit'},
					{name: 'action_view'},
					{name: 'action_delete'},
					{name: 'action_refresh'}
				]
			});
			// Электронная очередь на службе или на подразделении\отделении
			var swElectronicQueuePanel = new  sw.Promed.ViewFrame({
				title: 'Электронная очередь',
				id: 'MedServiceElectronicQueue',
				object: 'MedServiceElectronicQueue',
				editformclassname: 'swMedServiceElectronicQueueEditWindow',
				dataUrl: '/?c=MedServiceElectronicQueue&m=loadList',
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields:
					[
						{name: 'MedServiceElectronicQueue_id', type: 'int', header: 'ID', key: true},
						{name: 'MedService_id', hidden: true},
                        {name: 'UslugaComplexMedService_id', hidden: true},
                        {name: 'MedStaffFact_id', hidden: true},
                        {name: 'Resource_id', hidden: true},
						{name: 'LpuBuilding_id', hidden: true},
						{name: 'LpuSection_id', hidden: true},
						{name: 'MedPersonal_Name', type: 'string', header: 'Сотрудник', width: 200, id: 'autoexpand'},
						{name: 'UslugaComplex_Name', id:'UslugaComplex_Name_col', hidden: false,type: 'string', header: 'Услуга', width: 250},
						{name: 'ElectronicService_Name', type: 'string', header: 'Пункт обслуживания', width: 200},
						{name: 'ElectronicService_Num', type: 'string', header: 'Порядковый номер', width: 100}
					],
				actions:
					[
						{name:'action_add'},
						{name:'action_edit'},
						{name:'action_view'},
						{name:'action_delete'},
						{name:'action_refresh'},
						{name:'action_print'}
					],
                onRowSelect: function(sm, rowIdx, record) {

                    Ext.Ajax.request({
                        url: '/?c=ElectronicQueue&m=isEnableEvnDirectionsWithEmptyTalonCode',
                        params: {
                            MedService_id: record.get('MedService_id'),
                            UslugaComplexMedService_id: record.get('UslugaComplexMedService_id'),
                            MedStaffFact_id: record.get('MedStaffFact_id'),
                            Resource_id: record.get('Resource_id')
						},
                        success: function(response) {

                            swElectronicQueuePanel.getAction('action_genTalonCode').setDisabled(true);

                            var responseData = Ext.util.JSON.decode(response.responseText);
                            if (responseData.length > 0) {
                                if (responseData[0]) {
                                    swElectronicQueuePanel.getAction('action_genTalonCode').setDisabled(false);
                                }
							}
						},
                        failure: function(response) { log('fail', response) }
                    });
                }
			});

			//Форма зон обслуживания служб
			var territoryMedServicePanel = new  sw.Promed.ViewFrame({
				title:langs('Обслуживаемые территории службы'),
				id: 'MedServiceStreet',
				object: 'MedServiceKLHouseCoordsRel',
				editformclassname: 'swMedServiceStreetEditWindow',
				dataUrl: '/?c=LpuStructure&m=GetMedServiceStreet',
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields:
					[
						{name: 'MedServiceStreet_id', type: 'int', header: 'ID', key: true},
						{name: 'MedService_id', hidden: true, isparams: true},
						{name: 'KLCountry_id', hidden: true, isparams: false},
						{name: 'KLRGN_id', hidden: true, isparams: false},
						{name: 'KLSubRGN_id', hidden: true, isparams: false},
						{name: 'KLSubRGN_Name', type: 'string', header: langs('Район'), width: 120},
						{name: 'KLCity_id', hidden: true, isparams: false},
						{name: 'KLTown_id', hidden: true, isparams: false},
						{name: 'KLTown_Name', type: 'string', header: langs('Населенный пункт'), width: 200},
						{name: 'KLStreet_id', hidden: true, isparams: false},
						{name: 'KLStreet_Name', type: 'string', header: langs('Улица'), width: 200},
						{id: 'autoexpand',name: 'MedServiceStreet_HouseSet', type: 'string', header: langs('Номера домов')},
						{name: 'MedServiceStreet_isAll', type: 'checkbox', header: langs('Вся территория'), width: 200}
					],
				actions:
					[
						{name:'action_add'},
						{name:'action_edit'},
						{name:'action_view'},
						{name:'action_delete'},
						{name:'action_refresh'},
						{name:'action_print'}
					]
			});

			var swSmpUnitParamsPanel = new sw.Promed.FormPanel({
				id: this.id + '_swSmpUnitParamsPanel',
				url: '/?c=LpuStructure&m=getLpuBuildingData',
				reader: new Ext.data.JsonReader({},[
					{name: 'LpuBuilding_id'},
					{name: 'LpuBuilding_eid'},
					{name: 'Lpu_eid'},
					{name: 'minTimeSMP'},
					{name: 'maxTimeSMP'},
					{name: 'minTimeNMP'},
					{name: 'maxTimeNMP'},
					{name: 'minResponseTimeNMP'},
					{name: 'maxResponseTimeNMP'},

					{name: 'minResponseTimeET'},
					{name: 'minResponseTimeETNMP'},
					{name: 'maxResponseTimeET'},
					{name: 'maxResponseTimeETNMP'},
					{name: 'ArrivalTimeET'},
					{name: 'ArrivalTimeETNMP'},
					{name: 'ServiceTimeET'},
					{name: 'DispatchTimeET'},
					{name: 'LunchTimeET'},

					{name: 'LpuBuilding_IsCallCancel'},
					{name: 'LpuBuilding_IsCallDouble'},
					{name: 'LpuBuilding_IsCallSpecTeam'},
					{name: 'LpuBuilding_IsCallReason'},
					{name: 'LpuBuildingType_id'},

					{name: 'LpuBuildingSmsType_id'},
					{name: 'LpuBuilding_setDefaultAddressCity'},
					{name: 'LpuBuilding_IsEmergencyTeamDelay'},

					{name: 'SmpUnitParam_id'},
					{name: 'SmpUnitParam_IsAutoBuilding'},
					{name: 'SmpUnitParam_IsCall112'},
					{name: 'LpuBuilding_IsPrint'},
					{name: 'SmpUnitType_id'},
					{name: 'LpuBuilding_pid'},
					{name: 'SmpUnitParam_IsOverCall'},
					{name: 'SmpUnitParam_IsCallSenDoc'},
					{name: 'LpuBuilding_IsUsingMicrophone'},
					{name: 'LpuBuilding_IsWithoutBalance'},
					{name: 'SmpUnitParam_IsSignalBeg'},
					{name: 'SmpUnitParam_IsSignalEnd'},
					{name: 'SmpUnitParam_IsShowCallCount'},
					{name: 'SmpUnitParam_IsNoMoreAssignCall'},
					{name: 'SmpUnitParam_MaxCallCount'},
					{name: 'SmpUnitParam_IsKTPrint'},
					{name: 'SmpUnitParam_IsAutoEmergDuty'},
					{name: 'SmpUnitParam_IsAutoEmergDutyClose'},
					{name: 'SmpUnitParam_IsSendCall'},
					{name: 'SmpUnitParam_IsViewOther'},
					{name: 'SmpUnitParam_IsCancldCall'},
					{name: 'SmpUnitParam_IsCancldDisp'},
					{name: 'SmpUnitParam_IsCallControll'},
					{name: 'SmpUnitParam_IsShowAllCallsToDP'},
					//{name: 'SmpUnitParam_IsAutoHome'},
					//{name: 'SmpUnitParam_IsPrescrHome'},
					{name: 'SmpUnitParam_IsCallApproveSend'},
					{name: 'SmpUnitParam_IsNoTransOther'},
					{name: 'LpuBuilding_IsDenyCallAnswerDoc'},
					{name: 'SmpUnitParam_IsDenyCallAnswerDisp'},
					{name: 'SmpUnitParam_IsDispNoControl'},
					{name: 'SmpUnitParam_IsDocNoControl'},
					{name: 'SmpUnitParam_IsDispOtherControl'},
					{name: 'SmpUnitParam_IsSaveTreePath'},
					{name: 'SmpUnitParam_IsGroupSubstation'}


				]),
				labelWidth: 550,
				items: [
					{
						xtype: 'hidden',
						name: 'LpuBuilding_id'
					},
					{
						xtype: 'hidden',
						name: 'LpuBuildingType_id'
					},
					{
						xtype: 'hidden',
						name: 'SmpUnitParam_id'
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						title: 'Общее',
						items: [
							{
								xtype: 'swbaselocalcombo',
								fieldLabel: langs('Тип подстанции'),
								hiddenName: 'SmpUnitType_id',
								valueField: 'SmpUnitType_id',
								displayField: 'SmpUnitType_Name',
								allowBlank: false,
								store: new Ext.data.JsonStore({
									url: '/?c=LpuStructure&m=getSmpUnitTypes',
									key: 'SmpUnitType_id',
									fields: [
										{name: 'SmpUnitType_id', type: 'int'},
										{name: 'SmpUnitType_Name', type: 'string'},
										{name: 'SmpUnitType_Code', type: 'int'}
									],
									sortInfo: {
										field: 'SmpUnitType_Name'
									}
								}),
								listeners: {
									select: function(combo, record, index){
										combo.ownerCt.ownerCt.checkEnableLpuBuildingPid(record.get('SmpUnitType_id'));
									}.createDelegate(this)
								}
							},
							{
								xtype: 'swbaselocalcombo',
								fieldLabel: langs('Диспетчерская'),
								hiddenName: 'LpuBuilding_pid',
								valueField: 'LpuBuilding_id',
								displayField: 'LpuBuilding_Name',
								editable: false,
								disabled: true,
								listWidth: 500,
								store: new Ext.data.JsonStore({
									url: '/?c=LpuStructure&m=getLpuBuildingsForFilials',
									key: 'LpuBuilding_id',
									fields: [
										{name: 'LpuBuilding_id', type: 'int'},
										{name: 'LpuBuilding_Name', type: 'string'},
										{name: 'LpuBuilding_Code', type: 'string'}
									]
								}),
								listeners: {
									'select': function(combo, record, index){
										if ( typeof record == 'object' && !Ext.isEmpty(record.get('LpuBuilding_id')) ) {
											var base_form = swSmpUnitParamsPanel.getForm();

											Ext.Ajax.request({
												failure: function(response, options) {
													log(response);
												},
												params: {
													LpuBuilding_id: record.get('LpuBuilding_id')
												},
												success: function(response, action) {
													if ( typeof response == 'object' && !Ext.isEmpty(response.responseText) ) {
														var responseData = Ext.util.JSON.decode(response.responseText);

														if ( typeof responseData == 'object' && responseData.length > 0 ) {
															var timeFieldsArray = [
																'minTimeSMP',
																'minResponseTimeNMP',
																'maxTimeSMP',
																'maxResponseTimeNMP',
																'minTimeNMP',
																'maxTimeNMP',
																'minResponseTimeET',
																'minResponseTimeETNMP',
																'maxResponseTimeET',
																'maxResponseTimeETNMP',
																'ArrivalTimeET',
																'ArrivalTimeETNMP',
																'ServiceTimeET',
																'DispatchTimeET',
																'LunchTimeET'
															];
															var i;

															for ( var i = 0; i < timeFieldsArray.length; i++ ) {
																if ( typeof base_form.findField(timeFieldsArray[i]) == 'object' ) {
																	base_form.findField(timeFieldsArray[i]).setValue(responseData[0][timeFieldsArray[i]]);
																}
															}
														}
													}
												},
												url: '/?c=LpuStructure&m=getLpuBuildingData'
											});
										}
									}
								}
							},
							{
								hiddenName: 'LpuBuildingSmsType_id',
								disabled: true,
								comboSubject: 'LpuBuildingSmsType',
								xtype: 'swcommonsprcombo',
								fieldLabel: "Отправлять СМС-сообщение о назначении бригады на вызов"
							},
							{
								name:'LpuBuilding_setDefaultAddressCity',
								xtype: 'checkbox',
								fieldLabel: "При приеме вызова населенный пункт заполнять по умолчанию"
							},
							{
								name:'LpuBuilding_IsEmergencyTeamDelay',
								xtype: 'checkbox',
								fieldLabel: "Запрашивать причины задержек бригады СМП"
							},
							{
								name:'LpuBuilding_IsPrint',
								xtype: 'checkbox',
								hidden: !getRegionNick().inlist(['krym']),
								hideLabel: !getRegionNick().inlist(['krym']),
								fieldLabel: "Двусторонняя печать Карты вызова"
							},
							{
								name:'SmpUnitParam_IsAutoBuilding',
								xtype: 'checkbox',
								fieldLabel: "Автоматически определять подразделение обслуживания неотложных вызовов"
							},
							{
								name:'SmpUnitParam_IsCall112',
								xtype: 'checkbox',
								fieldLabel: "Принимать звонки из 112"
							},
							{
								name:'SmpUnitParam_IsOverCall',
								xtype: 'checkbox',
								fieldLabel: "Отображать вызовы с превышением срока обслуживания в отдельной группе АРМ СВ"
							},

							{
								name:'SmpUnitParam_IsCallSenDoc',
								xtype: 'checkbox',
								fieldLabel: "Создавать вызовы в АРМ Старшего врача"
							},

							{
								name:'LpuBuilding_IsUsingMicrophone',
								xtype: 'checkbox',
								fieldLabel: "Использовать микрофон для записи вызова"
							},
							{
								name:'SmpUnitParam_IsKTPrint',
								xtype: 'checkbox',
								fieldLabel: "Запрос печати КТ при назначении бригады на вызов"
							},
							{
								name:'SmpUnitParam_IsAutoEmergDuty',
								xtype: 'checkbox',
								fieldLabel: "Автоматически выводить бригады на смену"
							},
							{
								name:'SmpUnitParam_IsAutoEmergDutyClose',
								xtype: 'checkbox',
								fieldLabel: "Автоматически закрывать смены бригад"
							},
							{
								name:'SmpUnitParam_IsCallApproveSend',
								xtype: 'checkbox',
								fieldLabel: "Вызов подчиненной подстанции утверждается и передается оперативным отделом"
							},
							{
								name:'SmpUnitParam_IsNoTransOther',
								xtype: 'checkbox',
								fieldLabel: "Запретить перевод на другую подстанцию"
							},
							{
								name:'SmpUnitParam_IsSendCall',
								xtype: 'checkbox',
								fieldLabel: "Передача вызовов на другие подстанции Опер. отдела"
							},
							{
								name:'SmpUnitParam_IsViewOther',
								xtype: 'checkbox',
								fieldLabel: "Просмотр бригад других подстанций Опер. отдела"
							},
							{
								name:'SmpUnitParam_IsCallControll',
								xtype: 'checkbox',
								fieldLabel: "Включить функцию «Контроль вызовов»"
							},
							{
								name:'SmpUnitParam_IsShowAllCallsToDP',
								xtype: 'checkbox',
								fieldLabel: "Отображать все вызовы в АРМ диспетчера по приему вызовов"
							},
							{
								name:'SmpUnitParam_IsGroupSubstation',
								xtype: 'checkbox',
								fieldLabel: "Группировать вызовы по подстанциям"
							},
							{
								name:'SmpUnitParam_IsSaveTreePath',
								xtype: 'checkbox',
								fieldLabel: "Сохранять путь в дереве решений"
							}
						]
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						hidden: !getRegionNick().inlist(['perm']),
						title: 'Контроль управления подстанциями',
						items: [
							{
								name:'SmpUnitParam_IsDispNoControl',
								xtype: 'checkbox',
								fieldLabel: "Сообщать диспетчерам оперативного отдела о подстанциях, не взятых под управление"
							},
							{
								name:'SmpUnitParam_IsDocNoControl',
								xtype: 'checkbox',
								fieldLabel: "Сообщать Старшему врачу о подстанциях, не взятых под управление"
							},
							{
								name:'SmpUnitParam_IsDispOtherControl',
								xtype: 'checkbox',
								fieldLabel: "Сообщать диспетчеру, если подстанция уже находится под управлением другого диспетчера"
							}
						]
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						title: 'Контроль времени',
						items: [
							{
								name:'minTimeSMP',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								editable: false,
								disabled: true,
								fieldLabel: langs('Время на принятие вызова подстанцией СМП в форме скорой помощи, минут')
							},
							{
								name:'minResponseTimeNMP',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								editable: false,
								disabled: true,
								fieldLabel: langs('Время на принятие вызова подстанцией СМП в форме неотложной помощи, минут')
							},
							{
								name:'minResponseTimeET',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								editable: false,
								disabled: true,
								value: 0.25,
								fieldLabel: langs('Время на принятие вызова бригадой СМП, минут')
							},{
								layout: 'form',
								hidden: getRegionNick() != 'ufa',
								bodyStyle:'background:#DFE8F6;',
								border: false,
								items: [
									{
										name:'minResponseTimeETNMP',
										xtype: 'numberfield',
										//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
										minValue: 0,
										maxValue: 9999,
										width: 100,
										editable: false,
										disabled: true,
										value: 0.25,
										fieldLabel: langs('Время на принятие вызова бригадой СМП в форме неотложной помощи, минут')
									}
								]
							},
							{
								name:'maxResponseTimeET',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								value: 2,
								editable: false,
								disabled: true,
								fieldLabel: langs('Время на выезд на вызов, минут')
							},
							{
								layout: 'form',
								hidden: getRegionNick() != 'ufa',
								bodyStyle:'background:#DFE8F6;',
								border: false,
								items: [
									{
										name:'maxResponseTimeETNMP',
										xtype: 'numberfield',
										//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
										minValue: 0,
										maxValue: 9999,
										width: 100,
										value: 2,
										editable: false,
										disabled: true,
										fieldLabel: langs('Время на выезд на вызов в форме неотложной помощи, минут')
									}
								]
							},
							{
								name:'ArrivalTimeET',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								value: 20,
								editable: false,
								disabled: true,
								fieldLabel: langs('Время доезда на место вызова СМП, минут')
							},{
								layout: 'form',
								hidden: getRegionNick() != 'ufa',
								bodyStyle:'background:#DFE8F6;',
								border: false,
								items: [
									{
										name:'ArrivalTimeETNMP',
										xtype: 'numberfield',
										//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
										minValue: 0,
										maxValue: 9999,
										width: 100,
										value: 20,
										editable: false,
										disabled: true,
										fieldLabel: langs('Время доезда на место вызова НМП, минут')
									}
								]
							},
							{
								name:'ServiceTimeET',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								value: 40,
								editable: false,
								disabled: true,
								fieldLabel: langs('Время на обслуживание вызова, минут')
							},
							{
								name:'DispatchTimeET',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								value: 15,
								editable: false,
								disabled: true,
								fieldLabel: langs('Время на передачу пациента в МО госпитализации, минут')
							},
							{
								name:'maxTimeSMP',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								editable: false,
								disabled: true,
								fieldLabel: langs('Общее время на выполнение вызова подстанцией СМП в форме скорой помощи, минут')
							},
							{
								name:'maxResponseTimeNMP',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								editable: false,
								disabled: true,
								fieldLabel: langs('Общее время на выполнение вызова подстанцией СМП в форме неотложной помощи, минут')
							},
							{
								name:'minTimeNMP',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								editable: false,
								disabled: true,
								fieldLabel: langs('Время на принятие вызова отделением (кабинетом) НМП, минут')
							},
							{
								name:'maxTimeNMP',
								xtype: 'numberfield',
								//plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								editable: false,
								disabled: true,
								fieldLabel: langs('Общее время на выполнение вызова отделением (кабинетом) НМП, минут')
							},
							{
								name:'LunchTimeET',
								xtype: 'numberfield',
								plugins: [ new Ext.ux.InputTextMask('9999', true) ],
								minValue: 0,
								maxValue: 9999,
								width: 100,
								editable: false,
								disabled: true,
								fieldLabel: langs('Время на обед для бригад СМП, минут')
							},
						]
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						id: this.id + '_ExtraNmpInfoGroup',
						title: 'Передача экстренных вызовов из НМП',
						items: [
							{
								fieldLabel: 'МО передачи (СМП)',
								valueField: 'Lpu_id',
								autoLoad: true,
								editable: true,
								width: 400,
								hiddenName: 'Lpu_eid',
								displayField: 'Lpu_Nick',
								medServiceTypeId: 19,
								comAction: 'AllAddress',
								xtype: 'swlpuwithmedservicecombo',
								listeners: {
									select: function (combo, record) {
										var base_form = swSmpUnitParamsPanel.getForm(),
											lpuBuilding = base_form.findField('LpuBuilding_eid');

										lpuBuilding.getStore().clearFilter();
										lpuBuilding.reset();

										lpuBuilding.enable();

										lpuBuilding.getStore().baseParams = {
											'MedServiceType_id' : 19,
											'Lpu_id' : record.get('Lpu_id'),
											'isClose' : 1
										};

										lpuBuilding.getStore().load();
									},
									change: function(cb, newVal){
										if(newVal == 0){
											var base_form = swSmpUnitParamsPanel.getForm();
											base_form.findField('LpuBuilding_eid').disable()
										}
									}
								}
							},
							{
								//xtype: (getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) ? 'swsmpunitscombo' : 'swregionsmpunitscombo' ,
								xtype: 'swsmpunitscombo',
								fieldLabel: 'Подразделение СМП',
								hiddenName: 'LpuBuilding_eid',
								width: 400,
								showOperDpt: 1
							}
						]
					},
					/*
					{
						xtype: 'fieldset',
						autoHeight: true,
						id: this.id + '_HomeVisitFlagsContainer',
						title: 'Вызовы врача на дом',
						items: [
							{
								name:'SmpUnitParam_IsAutoHome',
								xtype: 'checkbox',
								fieldLabel: "Автоматически создавать вызов врача на дом"
							},
							{
								name:'SmpUnitParam_IsPrescrHome',
								xtype: 'checkbox',
								fieldLabel: "Автоматически назначать врача для вызова на дом"
							}
						]
					},
					*/
					{
						xtype: 'fieldset',
						autoHeight: true,
						id: this.id + '_HeadDocFlagsContainer',
						title: 'Вызовы, требующие решения старшего врача',
						items: [
							{
								name:'LpuBuilding_IsCallCancel',
								xtype: 'checkbox',
								fieldLabel: "Отменяющие вызовы"
							},
							{
								name:'LpuBuilding_IsDenyCallAnswerDoc',
								xtype: 'checkbox',
								fieldLabel: "Отклоняющие вызовы"
							},
							{
								name:'LpuBuilding_IsCallDouble',
								xtype: 'checkbox',
								fieldLabel: "Дублирующие вызовы "
							},
							{
								name:'LpuBuilding_IsCallSpecTeam',
								hidden: getRegionNick().inlist(['perm']),
								hideLabel: getRegionNick().inlist(['perm']),
								xtype: 'checkbox',
								fieldLabel: "Вызовы на спец. бригаду СМП"
							},
							{
								name:'LpuBuilding_IsCallReason',
								xtype: 'checkbox',
								fieldLabel: "Вызовы с поводом, требующим наблюдения старшего врача "
							}
						]
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						id: this.id + '_DispStationFlagsContainer',
						title: 'Вызовы, требующие решения диспетчера отправляющей части',
						items: [
							{
								name:'SmpUnitParam_IsCancldCall',
								xtype: 'checkbox',
								fieldLabel: "Отменяющие вызовы"
							},
							{
								name:'SmpUnitParam_IsDenyCallAnswerDisp',
								xtype: 'checkbox',
								fieldLabel: "Отклоняющие вызовы"
							}
						]
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						id: this.id + '_DispNestedStationFlagsContainer',
						title: 'Вызовы, требующие решения диспетчера удаленной подстанции',
						items: [
							{
								name:'SmpUnitParam_IsCancldDisp',
								xtype: 'checkbox',
								fieldLabel: "Отменяющие вызовы"
							}
						]
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						id: this.id + '_CmpCallCardDrugFlagsContainer',
						title: 'Учет медикаментов',
						items: [
							{
								name:'LpuBuilding_IsWithoutBalance',
								xtype: 'checkbox',
								fieldLabel: "Учет расхода медикаментов без обращения к остаткам"
							}
						]
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						id: this.id + '_CmpCallCardSignalFlagsContainer',
						title: 'Звуковые оповещения на события с вызовом',
						items: [
							{
								name:'SmpUnitParam_IsSignalBeg',
								xtype: 'checkbox',
								fieldLabel: "При передаче вызова на подстанцию"
							},
							{
								name:'SmpUnitParam_IsSignalEnd',
								xtype: 'checkbox',
								fieldLabel: "По окончании обслуживания вызова"
							}
						]
					},
					{
						xtype: 'fieldset',
						autoHeight: true,
						id: this.id + '_controllCountCallsOnTeamFlagsContainer',
						title: 'Контроль количества вызовов на бригаде',
						items: [
							{
								name:'SmpUnitParam_IsShowCallCount',
								xtype: 'checkbox',
								width: 300,
								fieldLabel: "Показывать количество вызовов, назначенных на бригаду"
							},
							{
								xtype: 'container',
								autoEl: {},
								layout: 'column',
								columnWidth: 0.3,
								items: [
									{
										xtype: 'label',
										text: "Запрещать назначение вызова на бригаду при превышении:",
										style: 'font-size: 12px; width: 545px; padding: 0px 5px 0px;'
									},
									{
										name:'SmpUnitParam_IsNoMoreAssignCall',
										xtype: 'checkbox',
										fieldLabel: "Запрещать назначение вызова на бригаду при превышении",
										listeners: {
											check: function(checkbox, checked){
												swSmpUnitParamsPanel.getForm().findField('SmpUnitParam_MaxCallCount').setVisible(checked);
											}
										}
									},
									{
										name:'SmpUnitParam_MaxCallCount',
										xtype: 'numberfield',
										minValue: 0,
										maxValue: 600,
										width: 100,
										style: 'margin: 0 5px 0;'
									}
								]
							}
						]
					},
					activeCallRulePanel,
					{
						xtype: 'button',
						text: langs('Сохранить'),
						iconCls: 'save16',
						style: '20px 0 0 10px;',
						handler: function(){
							var panel = this.ownerCt,
								unitParamsForm = swSmpUnitParamsPanel.getForm(),
								formValues = unitParamsForm.getValues(),
								SmpUnitType_id = unitParamsForm.findField('SmpUnitType_id').getValue(),
								SmpUnitType_Code = unitParamsForm.findField('SmpUnitType_id').getFieldValue('SmpUnitType_Code'),
								LpuBuilding_IsPrint = unitParamsForm.findField('LpuBuilding_IsPrint').getValue(),
								LpuBuildingSmsType_id = unitParamsForm.findField('LpuBuildingSmsType_id').getValue(),
								LpuBuilding_setDefaultAddressCity = unitParamsForm.findField('LpuBuilding_setDefaultAddressCity').getValue(),
								LpuBuilding_IsEmergencyTeamDelay = unitParamsForm.findField('LpuBuilding_IsEmergencyTeamDelay').getValue(),
								LpuBuilding_IsCallCancel = unitParamsForm.findField('LpuBuilding_IsCallCancel').getValue(),
								LpuBuilding_IsDenyCallAnswerDoc = unitParamsForm.findField('LpuBuilding_IsDenyCallAnswerDoc').getValue(),
								LpuBuilding_IsCallDouble = unitParamsForm.findField('LpuBuilding_IsCallDouble').getValue(),
								LpuBuilding_IsCallSpecTeam = unitParamsForm.findField('LpuBuilding_IsCallSpecTeam').getValue(),
								LpuBuilding_IsCallReason = unitParamsForm.findField('LpuBuilding_IsCallReason').getValue(),
								LpuBuilding_id = unitParamsForm.findField('LpuBuilding_id').getValue(),
								Lpu_eid = unitParamsForm.findField('Lpu_eid').getValue(),
								LpuBuilding_eid = unitParamsForm.findField('LpuBuilding_eid').getValue(),
								LpuBuilding_IsUsingMicrophone = unitParamsForm.findField('LpuBuilding_IsUsingMicrophone').getValue(),
								LpuBuilding_IsWithoutBalance = unitParamsForm.findField('LpuBuilding_IsWithoutBalance').getValue(),
								SmpUnitParam_IsCancldCall = unitParamsForm.findField('SmpUnitParam_IsCancldCall').getValue(),
								SmpUnitParam_IsDenyCallAnswerDisp = unitParamsForm.findField('SmpUnitParam_IsDenyCallAnswerDisp').getValue();

							if(LpuBuilding_IsCallCancel && SmpUnitParam_IsCancldCall){
								sw.swMsg.alert(langs('Ошибка'), langs('Отменяющие вызовы не могут одновременно передаваться на согласование и старшему врачу, и диспетчеру отправляющей части. Снимите один из флагов и повторите сохранение.'));
								return;
							}

							if(LpuBuilding_IsDenyCallAnswerDoc && SmpUnitParam_IsDenyCallAnswerDisp){
								sw.swMsg.alert(langs('Ошибка'), langs('Отклоняющие вызовы не могут одновременно передаваться на согласование и старшему врачу, и диспетчеру отправляющей части. Снимите один из флагов и повторите сохранение.'));
								return;
							}

							Ext.Ajax.request({
								url: '/?c=LpuStructure&m=saveLpuBuildingAdditionalParams',
								params: {
									LpuBuilding_IsPrint: LpuBuilding_IsPrint,
									LpuBuilding_id: LpuBuilding_id,
									LpuBuildingSmsType_id: LpuBuildingSmsType_id,
									LpuBuilding_setDefaultAddressCity: LpuBuilding_setDefaultAddressCity,
									LpuBuilding_IsEmergencyTeamDelay: LpuBuilding_IsEmergencyTeamDelay,
									LpuBuilding_IsCallCancel: LpuBuilding_IsCallCancel,
									LpuBuilding_IsDenyCallAnswerDoc: LpuBuilding_IsDenyCallAnswerDoc,
									LpuBuilding_IsCallDouble: LpuBuilding_IsCallDouble,
									LpuBuilding_IsCallReason: LpuBuilding_IsCallReason,
									LpuBuilding_IsCallSpecTeam: LpuBuilding_IsCallSpecTeam,
									LpuBuilding_IsUsingMicrophone: LpuBuilding_IsUsingMicrophone,
									LpuBuilding_IsWithoutBalance: LpuBuilding_IsWithoutBalance,
									Lpu_eid: Lpu_eid,
									LpuBuilding_eid: LpuBuilding_eid
								},
								callback: function (opt, success, response) {

								}.bind(this)
							});


							unitParamsForm.submit({
								url: '/?c=LpuStructure&m=saveSmpUnitParams',
								clientValidation: true,
								success: function(sform, action){
									sform.findField('SmpUnitParam_id').setValue(action.result.SmpUnitParam_id);
									if (SmpUnitType_Code == '4') {
										Ext.Ajax.request({
											url: '/?c=LpuStructure&m=saveSmpUnitTimes',
											params: {
												LpuBuilding_id: formValues.LpuBuilding_id,
												minTimeSMP: formValues.minTimeSMP,
												maxTimeSMP: formValues.maxTimeSMP,
												minTimeNMP: formValues.minTimeNMP,
												maxTimeNMP: formValues.maxTimeNMP,
												minResponseTimeNMP: formValues.minResponseTimeNMP,
												maxResponseTimeNMP: formValues.maxResponseTimeNMP,
												minResponseTimeET: formValues.minResponseTimeET,
												minResponseTimeETNMP: formValues.minResponseTimeETNMP,
												maxResponseTimeET: formValues.maxResponseTimeET,
												maxResponseTimeETNMP: formValues.maxResponseTimeETNMP,
												ArrivalTimeET: formValues.ArrivalTimeET,
												ArrivalTimeETNMP: formValues.ArrivalTimeETNMP,
												ServiceTimeET: formValues.ServiceTimeET,
												DispatchTimeET: formValues.DispatchTimeET,
												LunchTimeET: formValues.LunchTimeET
											},
											callback: function (opt, success, response) {
												// sw.swMsg.alert(INF_MSG, INF_SAVED_DATA);
												showSysMsg('', 'Настройки успешно сохранены', null, {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
											}
										});
									} else {
										/*panel.load({
										 params: {LpuBuilding_id: sform.findField('LpuBuilding_id').getValue()}
										 });*/
										// sw.swMsg.alert(INF_MSG, INF_SAVED_DATA);
										showSysMsg('', 'Настройки успешно сохранены', null, {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
									}
								},
								failure: function(form, action) {
									switch (action.failureType) {
										case Ext.form.Action.CLIENT_INVALID:
											Ext.Msg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG, function(){ panel.getInvalid()[0].focus(); });
											break;
										case Ext.form.Action.CONNECT_FAILURE:
											Ext.Msg.alert(ERR_WND_TIT, ERR_CONNECT_FAILURE);
											break;
										case Ext.form.Action.SERVER_INVALID:
											Ext.Msg.alert(ERR_WND_TIT, action.result.Error_Msg + ' (код ' + action.result.Error_Code + ')');
											break;
									}
								}
							});


						}
					}
				],
				checkEnableLpuBuildingPid: function(SmpUnitType_id){
					var HeadDocFlagsContainer = Ext.getCmp('swLpuStructureViewForm_HeadDocFlagsContainer'),
						ExtraNmpInfoGroup = Ext.getCmp('swLpuStructureViewForm_ExtraNmpInfoGroup'),
						DispStationFlagsContainer = Ext.getCmp('swLpuStructureViewForm_DispStationFlagsContainer'),
						DispNestedStationFlagsContainer = Ext.getCmp('swLpuStructureViewForm_DispNestedStationFlagsContainer'),
						CmpCallCardDrugFlagsContainer = Ext.getCmp('swLpuStructureViewForm_CmpCallCardDrugFlagsContainer'),
						CmpCallCardSignalFlagsContainer = Ext.getCmp('swLpuStructureViewForm_CmpCallCardSignalFlagsContainer'),
						controllCountCallsOnTeamFlagsContainer = Ext.getCmp('swLpuStructureViewForm_controllCountCallsOnTeamFlagsContainer'),
						//HomeVisitFlagsContainer = Ext.getCmp('swLpuStructureViewForm_HomeVisitFlagsContainer'),
						unitParamsForm = swSmpUnitParamsPanel.getForm(),
						lpuBuildingSmsTypeCombo = unitParamsForm.findField('LpuBuildingSmsType_id');

					lpuBuildingSmsTypeCombo.hideContainer();
					HeadDocFlagsContainer.setVisible(false);
					//HomeVisitFlagsContainer.setVisible(false);
					ExtraNmpInfoGroup.setVisible(false);
					DispStationFlagsContainer.setVisible(false);
					DispNestedStationFlagsContainer.setVisible(false);
					CmpCallCardDrugFlagsContainer.setVisible(false);
					CmpCallCardSignalFlagsContainer.setVisible(false);
					controllCountCallsOnTeamFlagsContainer.setVisible(false);

					if (!SmpUnitType_id) {
						return;
					}

					var form = this.getForm ? this.getForm() : this;

					// Метод может вызываться в разных областях видимости

					var LpuBuildingCombo = form.findField('LpuBuilding_pid'),
						LpuBuildingType_id = form.findField('LpuBuildingType_id').getValue(),
						minTimeSMP = form.findField('minTimeSMP'),
						maxTimeSMP = form.findField('maxTimeSMP'),
						minTimeNMP = form.findField('minTimeNMP'),
						maxTimeNMP = form.findField('maxTimeNMP'),
						minResponseTimeNMP = form.findField('minResponseTimeNMP'),
						maxResponseTimeNMP = form.findField('maxResponseTimeNMP'),

						minResponseTimeET = form.findField('minResponseTimeET'),
						minResponseTimeETNMP = form.findField('minResponseTimeETNMP'),
						maxResponseTimeET = form.findField('maxResponseTimeET'),
						maxResponseTimeETNMP = form.findField('maxResponseTimeETNMP'),
						ArrivalTimeET = form.findField('ArrivalTimeET'),
						ArrivalTimeETNMP = form.findField('ArrivalTimeETNMP'),
						ServiceTimeET = form.findField('ServiceTimeET'),
						DispatchTimeET = form.findField('DispatchTimeET'),
						LunchTimeET = form.findField('LunchTimeET'),

						isCallCancel = form.findField('LpuBuilding_IsCallCancel'),
						isCallDouble = form.findField('LpuBuilding_IsCallDouble'),
						isCallSpecTeam = form.findField('LpuBuilding_IsCallSpecTeam'),
						isCallReason = form.findField('LpuBuilding_IsCallReason'),

						isCancldCall = form.findField('SmpUnitParam_IsCancldCall'),

						SmpUnitType_Code = form.findField('SmpUnitType_id').getFieldValue('SmpUnitType_Code');

					// 2 - Удаленная/подчиненная подстанция?
					if (SmpUnitType_Code.inlist([2,5])) {
						lpuBuildingSmsTypeCombo.hideContainer();
						LpuBuildingCombo.setDisabled(false);
						LpuBuildingCombo.allowBlank = false;
						LpuBuildingCombo.focus();
					} else {
						lpuBuildingSmsTypeCombo.showContainer();
						LpuBuildingCombo.setDisabled(true);
						LpuBuildingCombo.allowBlank = true;
						LpuBuildingCombo.setValue('');
					}
					//4 - оперативный отдел
					if (SmpUnitType_Code == 4) {
						activeCallRulePanel.setVisible(true);
						minTimeSMP.setDisabled(false);
						maxTimeSMP.setDisabled(false);
						minTimeNMP.setDisabled(false);
						maxTimeNMP.setDisabled(false);
						minResponseTimeNMP.setDisabled(false);
						maxResponseTimeNMP.setDisabled(false);

						minResponseTimeET.setDisabled(false);
						minResponseTimeETNMP.setDisabled(false);
						maxResponseTimeET.setDisabled(false);
						maxResponseTimeETNMP.setDisabled(false);
						ArrivalTimeET.setDisabled(false);
						ArrivalTimeETNMP.setDisabled(false);
						ServiceTimeET.setDisabled(false);
						DispatchTimeET.setDisabled(false);
						LunchTimeET.setDisabled(false);

						isCallCancel.setDisabled(false);
						isCallDouble.setDisabled(false);
						isCallSpecTeam.setDisabled(false);
						isCallReason.setDisabled(false);
						isCancldCall.setDisabled(false);
						HeadDocFlagsContainer.setVisible(true);
						//HomeVisitFlagsContainer.setVisible(false);
						ExtraNmpInfoGroup.setVisible(LpuBuildingType_id == 28);
						DispStationFlagsContainer.setVisible(true);
						DispNestedStationFlagsContainer.setVisible(getRegionNick().inlist(['perm']));
						CmpCallCardDrugFlagsContainer.setVisible(false);
						CmpCallCardSignalFlagsContainer.setVisible(true);
						controllCountCallsOnTeamFlagsContainer.setVisible(true);
						form.findField('SmpUnitParam_IsOverCall').setContainerVisible(true);
						form.findField('SmpUnitParam_IsCallSenDoc').setContainerVisible(true);
						form.findField('SmpUnitParam_IsCall112').setContainerVisible(true);
						form.findField('SmpUnitParam_IsKTPrint').setContainerVisible(false);
						form.findField('SmpUnitParam_IsAutoEmergDuty').setContainerVisible(true);
						form.findField('SmpUnitParam_IsAutoEmergDutyClose').setContainerVisible(true);
						form.findField('SmpUnitParam_IsCallApproveSend').setContainerVisible(getRegionNick().inlist(['perm']));
						form.findField('SmpUnitParam_IsNoTransOther').setContainerVisible(false);
						form.findField('SmpUnitParam_IsSendCall').setContainerVisible(true);
						form.findField('SmpUnitParam_IsViewOther').setContainerVisible(true);
						form.findField('SmpUnitParam_IsCallControll').setContainerVisible(true);
						form.findField('SmpUnitParam_IsSaveTreePath').setContainerVisible(LpuBuildingType_id == 28);
						form.findField('SmpUnitParam_IsShowAllCallsToDP').setContainerVisible(true);
						form.findField('SmpUnitParam_IsDenyCallAnswerDisp').setContainerVisible(getRegionNick().inlist(['perm']));
						form.findField('LpuBuilding_IsDenyCallAnswerDoc').setContainerVisible(getRegionNick().inlist(['perm']));
						form.findField('SmpUnitParam_IsDispNoControl').setContainerVisible(getRegionNick().inlist(['perm']));
						form.findField('SmpUnitParam_IsDocNoControl').setContainerVisible(getRegionNick().inlist(['perm']));
						form.findField('SmpUnitParam_IsDispOtherControl').setContainerVisible(getRegionNick().inlist(['perm']));
						form.findField('SmpUnitParam_IsGroupSubstation').setContainerVisible(getRegionNick().inlist(['astra']));


						if((LpuBuildingType_id == 28) && form.findField('Lpu_eid').getValue()){
							var lpuBuilding = form.findField('LpuBuilding_eid'),
								lpuBuilding_id = lpuBuilding.getValue();

							lpuBuilding.getStore().baseParams = {
								'MedServiceType_id' : 19,
								'Lpu_id' : form.findField('Lpu_eid').getValue(),
								'isClose' : 1
							};

							lpuBuilding.getStore().load({
								callback: function(){
									lpuBuilding.enable();
									lpuBuilding.setValue(lpuBuilding_id);
								}
							});
						}else{
							form.findField('LpuBuilding_eid').disable()
						}

					} else {
						activeCallRulePanel.setVisible(false);
						minTimeSMP.setDisabled(true);
						maxTimeSMP.setDisabled(true);
						minTimeNMP.setDisabled(true);
						maxTimeNMP.setDisabled(true);
						minResponseTimeNMP.setDisabled(true);
						maxResponseTimeNMP.setDisabled(true);

						minResponseTimeET.setDisabled(true);
						minResponseTimeETNMP.setDisabled(true);
						maxResponseTimeET.setDisabled(true);
						maxResponseTimeETNMP.setDisabled(true);
						ArrivalTimeET.setDisabled(true);
						ArrivalTimeETNMP.setDisabled(true);
						ServiceTimeET.setDisabled(true);
						DispatchTimeET.setDisabled(true);
						LunchTimeET.setDisabled(true);

						isCallCancel.setDisabled(true);
						isCallDouble.setDisabled(true);
						isCallSpecTeam.setDisabled(true);
						isCallReason.setDisabled(true);
						isCancldCall.setDisabled(true);
						HeadDocFlagsContainer.setVisible(false);
						//HomeVisitFlagsContainer.setVisible(LpuBuildingType_id == 28 && SmpUnitType_Code == 5);
						ExtraNmpInfoGroup.setVisible(false);
						DispStationFlagsContainer.setVisible(false);
						DispNestedStationFlagsContainer.setVisible(false);
						CmpCallCardDrugFlagsContainer.setVisible(true);
						CmpCallCardSignalFlagsContainer.setVisible(false);
						controllCountCallsOnTeamFlagsContainer.setVisible(false);
						form.findField('SmpUnitParam_IsOverCall').setContainerVisible(false);
						form.findField('SmpUnitParam_IsCallSenDoc').setContainerVisible(false);
						form.findField('SmpUnitParam_IsCall112').setContainerVisible(false);
						form.findField('SmpUnitParam_IsKTPrint').setContainerVisible(SmpUnitType_Code == 2);
						form.findField('SmpUnitParam_IsAutoEmergDuty').setContainerVisible(false);
						form.findField('SmpUnitParam_IsAutoEmergDutyClose').setContainerVisible(false);
						form.findField('SmpUnitParam_IsCallApproveSend').setContainerVisible(false);
						form.findField('SmpUnitParam_IsNoTransOther').setContainerVisible(getRegionNick().inlist(['perm']) && SmpUnitType_Code == 5);
						form.findField('SmpUnitParam_IsSendCall').setContainerVisible(false);
						form.findField('SmpUnitParam_IsViewOther').setContainerVisible(false);
						form.findField('SmpUnitParam_IsCallControll').setContainerVisible(false);
						form.findField('SmpUnitParam_IsSaveTreePath').setContainerVisible(false);
						form.findField('SmpUnitParam_IsShowAllCallsToDP').setContainerVisible(false);
						form.findField('SmpUnitParam_IsDenyCallAnswerDisp').setContainerVisible(false);
						form.findField('LpuBuilding_IsDenyCallAnswerDoc').setContainerVisible(false);
						form.findField('SmpUnitParam_IsDispNoControl').setContainerVisible(false);
						form.findField('SmpUnitParam_IsDocNoControl').setContainerVisible(false);
						form.findField('SmpUnitParam_IsDispOtherControl').setContainerVisible(false);
						form.findField('SmpUnitParam_IsGroupSubstation').setContainerVisible(false);
					}
				}
			});

			var getNmpDays = function() {
				return {
					Mo: 'Понедельник',
					Tu: 'Вторник',
					We: 'Среда',
					Th: 'Четверг',
					Fr: 'Пятница',
					Sa: 'Суббота',
					Su: 'Воскресенье'
				};
			};

			var getNmpWorkTimesConfig = function() {
				var config = [];
				var days = getNmpDays();

				for(var day_nick in days) {
					var beg_time_field_name = 'LpuHMPWorkTime_'+day_nick+'From';
					var end_time_field_name = 'LpuHMPWorkTime_'+day_nick+'To';

					config.push({
						layout: 'column',
						border: false,
						bodyStyle: 'background: #DFE8F6;',
						defaults: {
							border: false,
							bodyStyle: 'background: #DFE8F6;',
						},
						items: [{
							layout: 'form',
							width: 110,
							items: [{
								xtype: 'label',
								bodyStyle: 'padding-top: 3px; background:#DFE8F6;',
								style: 'font-size: 12px; font-weight: bold;',
								text: days[day_nick]
							}]
						}, {
							layout: 'form',
							labelWidth: 20,
							items: [{
								xtype: 'swtimefield',
								fieldLabel: 'С',
								name: beg_time_field_name,
								endTimeName: end_time_field_name,
								hideTrigger: true,
								plugins: [new Ext.ux.InputTextMask('99:99', false)],
								onChange: function(field, newValue, oldValue) {
									var base_form = Ext.getCmp(frms.id + '_swNmpParamsPanel').getForm();
									var endTimeField = base_form.findField(field.endTimeName);

									endTimeField.setAllowBlank(Ext.isEmpty(newValue));
									endTimeField.fireEvent('focus', endTimeField);	//Для плагина InputTextMask
								}
							}]
						}, {
							layout: 'form',
							labelWidth: 30,
							items: [{
								xtype: 'swtimefield',
								fieldLabel: 'По',
								name: end_time_field_name,
								begTimeName: beg_time_field_name,
								hideTrigger: true,
								plugins: [new Ext.ux.InputTextMask('99:99', false)],
								onChange: function(field, newValue, oldValue) {
									var base_form = Ext.getCmp(frms.id + '_swNmpParamsPanel').getForm();
									var begTimeField = base_form.findField(field.begTimeName);

									begTimeField.setAllowBlank(Ext.isEmpty(newValue));
									begTimeField.fireEvent('focus', begTimeField);	//Для плагина InputTextMask
								}
							}]
						}]
					});
				}

				return config;
			};

			var swNmpParamsPanel = new sw.Promed.FormPanel({
				id: this.id + '_swNmpParamsPanel',
				url: '/?c=LpuStructure&m=getNmpParams',
				setWorkTimesDisabled: function(disabled) {
					var base_form = this.getForm();
					var days = Object.keys(getNmpDays());

					days.forEach(function(day){
						base_form.findField('LpuHMPWorkTime_'+day+'From').setDisabled(disabled);
						base_form.findField('LpuHMPWorkTime_'+day+'To').setDisabled(disabled);
					});
				},
				refreshWorkTimeAllowBlank: function() {
					var base_form = this.getForm();
					var days = Object.keys(getNmpDays());

					days.forEach(function(day){
						var begTimeField = base_form.findField('LpuHMPWorkTime_'+day+'From');
						var endTimeField = base_form.findField('LpuHMPWorkTime_'+day+'To');

						begTimeField.setAllowBlank(Ext.isEmpty(endTimeField.getValue()));
						endTimeField.setAllowBlank(Ext.isEmpty(begTimeField.getValue()));
					});
				},
				reader: new Ext.data.JsonReader({},[
					{name: 'MedService_id', type: 'int'},
					{name: 'LpuHMPWorkTime_MoFrom', type: 'string'},
					{name: 'LpuHMPWorkTime_MoTo', type: 'string'},
					{name: 'LpuHMPWorkTime_TuFrom', type: 'string'},
					{name: 'LpuHMPWorkTime_TuTo', type: 'string'},
					{name: 'LpuHMPWorkTime_WeFrom', type: 'string'},
					{name: 'LpuHMPWorkTime_WeTo', type: 'string'},
					{name: 'LpuHMPWorkTime_ThFrom', type: 'string'},
					{name: 'LpuHMPWorkTime_ThTo', type: 'string'},
					{name: 'LpuHMPWorkTime_FrFrom', type: 'string'},
					{name: 'LpuHMPWorkTime_FrTo', type: 'string'},
					{name: 'LpuHMPWorkTime_SaFrom', type: 'string'},
					{name: 'LpuHMPWorkTime_SaTo', type: 'string'},
					{name: 'LpuHMPWorkTime_SuFrom', type: 'string'},
					{name: 'LpuHMPWorkTime_SuTo', type: 'string'}
				]),
				items: [{
					xtype: 'hidden',
					name: 'MedService_id'
				}, {
					xtype: 'fieldset',
					title: 'Время работы',
					autoHeight: true,
					items: getNmpWorkTimesConfig()
				}, {
					xtype: 'button',
					text: langs('Сохранить'),
					iconCls: 'save16',
					style: '20px 0 0 10px;',
					handler: function(){
						var panel = Ext.getCmp(frms.id + '_swNmpParamsPanel');
						var base_form = panel.getForm();
						var params = {};

						if (!base_form.isValid()) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									panel.getFirstInvalidEl().focus(false);
								},
								icon: Ext.Msg.WARNING,
								msg: ERR_INVFIELDS_MSG,
								title: ERR_INVFIELDS_TIT
							});
							return false;
						}

						base_form.items.each(function(field){
							params[field.getName()] = field.getValue();
						});

						for (day in getNmpDays()) {
							var beg_time = params['LpuHMPWorkTime_'+day+'From'];
							var end_time = params['LpuHMPWorkTime_'+day+'To'];

							if (!Ext.isEmpty(beg_time) && Date.parseDate(beg_time, 'H:i') > Date.parseDate(end_time, 'H:i')) {
								Ext.Msg.alert(ERR_WND_TIT, 'Время начала работы кабинета НМП не может быть больше времени окончания');
								return;
							}
						}

						var loadMask = new Ext.LoadMask(frms.getEl(), {msg: 'Подождите идет сохранение...'});
						loadMask.show();

						Ext.Ajax.request({
							url: '/?c=LpuStructure&m=saveNmpParams',
							params: params,
							success: function(response, options) {
								loadMask.hide();
							},
							failure: function() {
								loadMask.hide();
							}
						});
					}
				}]
			});

			// Табгрид - Сотрудники. Все уровни.
			var swStaffTTPanel = new sw.Promed.ViewFrame(
				{
					title: langs('Строки штатного расписания'),
					id: 'MedStaffFactTT',
					object: 'MedStaffFactTT',
					editformclassname: 'swMedStaffFactEditWindow',
					dataUrl: '/?c=MedPersonal&m=getStaffTTGridDetail',
					height: 800,
					toolbar: true,
					autoLoadData: false,
					region: 'center',
					stringfields:
						[
							{name: 'Staff_id', type: 'int', header: 'ID', key: true},
							{name: 'Lpu_id', type: 'int', hidden: true, isparams: true},
							{name: 'LpuBuilding_id', type: 'int', hidden: true, isparams: true},
							{name: 'LpuUnit_id', type: 'int', hidden: true, isparams: true},
							{name: 'LpuSection_id', type: 'int', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'StructElement_Name', type: 'string', header: langs('Структурный элемент МО'), width: 200},
							{name: 'Post_Name',  type: 'string', header: langs('Должность'), width: 150},
							{name: 'MedicalCareKind_Name',  type: 'string', header: langs('Вид МП'), width: 150},
							{name: 'BeginDate',  type: 'date', header: langs('Дата создания'), width: 75},
							{name: 'Staff_Comment',  type: 'string', header: langs('Комментарий'), width: 200},
							{name: 'Staff_Rate',  type: 'float', header: langs('Количество ставок'), width: 55},
							{name: 'Staff_RateSum',  type: 'float', header: langs('Из них занято'), width: 55},
							{name: 'Staff_RateCount',  type: 'float', header: langs('Количество сотрудников'), width: 55}
						],
					actions:
						[
							{name: 'action_add', disabled: isMedPersView(),
								handler: function() {
									if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
									{
										var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
										var Lpu_id = swLpuStructureViewForm.getNodeParentAttribute(node, 'Lpu', 'object_value');
										var LpuUnit_id = null;
										var LpuSection_id = null;
										var LpuBuilding_id = null;

										if ( node.attributes.object == 'LpuUnit' )
										{
											//Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
											LpuBuilding_id = node.parentNode.parentNode.attributes.object_value;
											LpuUnit_id = node.attributes.object_value;
										}
										else if ( node.attributes.object == 'LpuSection' )
										{
											if (swLpuStructureViewForm.getNodeTrueLevel(node) == 6)
											{
												//Lpu_id = node.parentNode.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
												LpuBuilding_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
												LpuUnit_id = node.parentNode.parentNode.attributes.object_value;
												LpuSection_id = node.attributes.object_value;

											}
											else
											{
												//Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
												LpuBuilding_id = node.parentNode.parentNode.parentNode.attributes.object_value;
												LpuUnit_id = node.parentNode.attributes.object_value;
												LpuSection_id = node.attributes.object_value;
											}
										}
										else if ( node.attributes.object == 'Lpu' )
										{
											//Lpu_id = node.attributes.object_value;
										}
										else if ( node.attributes.object == 'LpuBuilding' )
										{
											//Lpu_id = node.parentNode.attributes.object_value;
											LpuBuilding_id = node.attributes.object_value;
										}
										var lpuStruct = {};
										lpuStruct.Lpu_id = String(Lpu_id) == 'null' ? null : String(Lpu_id);
										lpuStruct.LpuBuilding_id = String(LpuBuilding_id) == 'null' ? null : String(LpuBuilding_id);
										lpuStruct.LpuUnit_id = String(LpuUnit_id) == 'null' ? null : String(LpuUnit_id);
										lpuStruct.LpuSection_id = String(LpuSection_id) == 'null' ? null : String(LpuSection_id);
										lpuStruct.description = node.text;
										window.gwtBridge.runStaffEditor(getPromedUserInfo(), null, lpuStruct, function(result) {
											if ( Number(result) > 0 )
												this.swStaffTTPanel.ViewGridPanel.getStore().reload();
										}.createDelegate(this));
										//frms.MedStaffFactEditWindow.show({callback: frms.findById('MedStaffFact').refreshRecords, owner: frms.findById('MedStaffFact'), fields: {action: 'addinstructure', Lpu_id: Lpu_id, LpuUnit_id: LpuUnit_id, LpuSection_id: LpuSection_id}});
									}
								}.createDelegate(this)
							},
							{name: 'action_edit', text: (isMedPersView())?langs('Просмотр'):langs('Изменить'),
								handler: function() {
									if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
									{
										var row = this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected();
										var staff_id = row.get('Staff_id');
										var lpuStruct = {};
										lpuStruct.Lpu_id = String(row.get('Lpu_id')) == 'null' ? null : String(row.get('Lpu_id'));
										lpuStruct.LpuBuilding_id = String(row.get('LpuBuilding_id')) == 'null' ? null : String(row.get('LpuBuilding_id'));
										lpuStruct.LpuUnit_id = String(row.get('LpuUnit_id')) == 'null' ? null : String(row.get('LpuUnit_id'));
										lpuStruct.LpuSection_id = String(row.get('LpuSection_id')) == 'null' ? null : String(row.get('LpuSection_id'));
										lpuStruct.description = '';
										lpuStruct.action = 'view';
										window.gwtBridge.runStaffEditor(getPromedUserInfo(), String(staff_id), lpuStruct, function(result) {
											if ( Number(result) > 0 )
												this.swStaffTTPanel.ViewGridPanel.getStore().reload();
										}.createDelegate(this));
									}
								}.createDelegate(this)
							},
							{name:'action_view', disabled: true, hidden: true, handler: function() {}},
							{name:'action_delete', disabled: isMedPersView(),
								handler: function() {
									if ( this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected() )
									{
										sw.swMsg.show({
											icon: Ext.MessageBox.QUESTION,
											msg: langs('Вы хотите удалить запись?'),
											title: langs('Подтверждение'),
											buttons: Ext.Msg.YESNO,
											fn: function(buttonId, text, obj)
											{
												if ('yes' == buttonId)
												{
													var row = this.swStaffTTPanel.ViewGridPanel.getSelectionModel().getSelected();
													var staff_id = row.get('Staff_id');
													window.gwtBridge.deleteStaff(getPromedUserInfo(), String(staff_id), function(result) {
														this.swStaffTTPanel.ViewGridPanel.getStore().reload();
													}.createDelegate(this));
												}
											}.createDelegate(this)
										});
									}
								}.createDelegate(this)
							},
							{name:'action_refresh'},
							{name:'action_print', hidden: isMedPersView()}
						]
				});
			swStaffTTPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swStaffTTPanel);}.createDelegate(this));

			this.swStaffTTPanel = swStaffTTPanel;

			//Штатные расписания
			var swLpuStaffPanel = new sw.Promed.ViewFrame(
				{
					title: langs('Штатные расписания'),
					id: 'LpuStaff',
					object: 'LpuStaff',
					editformclassname: 'swLpuStaffEditWindow',
					dataUrl: '/?c=LpuStructure&m=getLpuStaffGridDetail',
					height: 303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuStaff_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuStaff_Num', type: 'int', header: langs('Номер'), width: 150},
							{id: 'autoexpand', name: 'LpuStaff_Descript',  type: 'string', header: 'описание', width: 200},
							{name: 'LpuStaff_ApprovalDT',  type: 'date', header: 'дата утверждения', width: 110},
							{name: 'LpuStaff_begDate',  type: 'date', header: 'дата начала', width: 110},
							{name: 'LpuStaff_endDate',  type: 'date', header: 'дата окончания', width: 110}
						],
					actions:
						[
							{name: 'action_add'},
							{name: 'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print', hidden: true}
						]
				});
			this.swLpuStaffPanel = swLpuStaffPanel;

			// Табгрид - Организационно-штатные мероприятия. Все уровни.
			var swStaffOSMPanel = new sw.Promed.ViewFrame(
				{
					title: langs('Организационно-штатные мероприятия'),
					id: 'Staff',
					object: 'Staff',
					editformclassname: 'swStaffEditWindow',
					//dataUrl: '/?c=LpuStructure&m=saveStaff',
					dataUrl: '/?c=LpuStructure&m=getStaffOSMGridDetail',
					height: 303,
					toolbar: true,
					scheme: 'fed',
					autoLoadData: false,
					stringfields:
						[
							{name: 'Staff_id', type: 'int', header: 'ID', key: true},
							{/*id: 'autoexpand',*/ name: 'Staff_Num', type: 'int', header: langs('Номер штата'), width: 200},
							{name: 'Staff_OrgName',  type: 'string', header: langs('Наименование ОШМ'), width: 150},
							{name: 'Staff_OrgDT',  type: 'date', header: langs('Дата ОШМ'), width: 75},
							{name: 'Staff_OrgBasis',  type: 'string', header: langs('Основание ОШМ'), width: 150}
						],
					actions:
						[
							{name: 'action_add', disabled: isMedPersView()},
							{name: 'action_edit', disabled: isMedPersView()},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print', hidden: isMedPersView()}
						]
				});

			this.swStaffOSMPanel = swStaffOSMPanel;

// Табгрид - Подразделения.  2 уровень
			var swLpuBuildingPanel = new sw.Promed.ViewFrame(
				{
					title:langs('Подразделения'),
					id: 'LpuBuilding',
					object: 'LpuBuilding',
					editformclassname: 'swLpuBuildingEditForm',
					dataUrl: '/?c=LpuStructure&m=GetLpuBuilding',
					//dataUrl: C_GETOBJECTLIST,
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuBuilding_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuBuilding_Code',  type: 'string', header: langs('Код'), width: 80},
							{id: 'autoexpand', name: 'LpuBuilding_Name',  type: 'string', header: langs('Наименование подразделения')},
							{name: 'LpuBuilding_begDate',  type: 'date', header: langs('Дата начала'), width: 120},
							{name: 'LpuBuilding_endDate',  type: 'date', header: langs('Дата окончания'), width: 120}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						],
					afterSaveEditForm: function ()
					{
						var LpuStructureFrame= Ext.getCmp('lpu-structure-frame'),
							selNode = LpuStructureFrame.getSelectionModel().selNode;

						swLpuStructureViewForm.reloadCurrentTreeNode(this);

						if (selNode.attributes.object === 'LpuFilial')
						{
							var lpuNode = LpuStructureFrame.getRootNode().findChild('object', 'Lpu'),
								delTask = new Ext.util.DelayedTask;

							delTask.delay(1500, swLpuStructureViewForm.reloadViewIfFilialDisappeared, this, [lpuNode, selNode]);
						}

					},
					afterDeleteRecord: function()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);

						if (selNode.attributes.object === 'LpuFilial')
						{
							var lpuNode = LpuStructureFrame.getRootNode().findChild('object', 'Lpu'),
								delTask = new Ext.util.DelayedTask;

							delTask.delay(1500, swLpuStructureViewForm.reloadViewIfFilialDisappeared, this, [lpuNode, selNode]);
						}
					}
				});

			swLpuBuildingPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swLpuBuildingPanel);}.createDelegate(this));

// Табгрид - Группа отделений.  3 уровень
			var swLpuUnitPanel = new sw.Promed.ViewFrame(
				{
					title:langs('Группа отделений'),
					id: 'LpuUnitPanel',
					object: 'LpuUnit',
					editformclassname: 'swLpuUnitEditForm',
					dataUrl: C_GETOBJECTLIST,
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuUnit_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuUnit_Code',  type: 'string', header: langs('Код'), width: 80},
							{id: 'autoexpand', name: 'LpuUnit_Name',  type: 'string', header: langs('Наименование группы отделений')},
							{name: 'LpuUnit_begDate',  type: 'date', header: langs('Дата начала'), width: 120},
							{name: 'LpuUnit_endDate',  type: 'date', header: langs('Дата окончания'), width: 120}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'}, // , handler: function() {Ext.Msg.alert('Ошибка', 'Удаление запрещено!');}
							{name:'action_refresh'},
							{name: 'action_print'}
						],
					afterSaveEditForm: function ()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);
					},
					afterDeleteRecord: function()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);
					}
				});
			swLpuUnitPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swLpuUnitPanel);}.createDelegate(this));

// Табгрид - Отделения. 4 уровень
			var swLpuSectionPanel = new sw.Promed.ViewFrame(
				{
					title:langs('Отделения'),
					id: 'LpuSection',
					object: 'LpuSection',
					editformclassname: 'swLpuSectionEditForm',
					dataUrl: C_LPUSECTION_GRID,
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuSection_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuSection_Code', type: 'string', header: langs('Код'), width: 80},
							{name: 'LpuSection_Name', autoexpand: true,  type: 'string', header: langs('Наименование отделения')},
							{name: 'LpuSectionProfile_Name',  type: 'string', header: langs('Профиль'), width: 150},
							{name: 'FPID',  type: 'string', header: langs('Функциональное подразделение (СУР)'), width: 220, hidden: getRegionNick() != 'kz'},
							/*
							 {id: 'LpuSectionBedState_Plan', name: 'LpuSectionBedState_Plan',  type: 'string', header: langs('Всего коек'), width: 70, hidden: true},
							 {id: 'LpuSectionBedState_Fact', name: 'LpuSectionBedState_Fact',  type: 'string', header: langs('Коек фактически'), width: 95, hidden: true},
							 {id: 'LpuSectionBedState_Repair', name: 'LpuSectionBedState_Repair',  type: 'string', header: langs('Kоек на ремонте'), width: 95, hidden: true},
							 */
							{name: 'LpuSection_pid', type: 'int', hidden: true, value: 'null'},
							{name: 'LpuSectionBedState_id', type: 'int', hidden: true},
							{name: 'LpuSectionProfile_id', type: 'int', hidden: true},
							{name: 'UnitDepartType_fid', type: 'int', hidden: true}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						],
					afterSaveEditForm: function ()
					{
						var LpuStructureFrame= Ext.getCmp('lpu-structure-frame');
						var selNode = LpuStructureFrame.getSelectionModel().selNode;
						var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
						if (swLpuStructureViewForm.getNodeTrueLevel(node) == 5)
						{
							var curnode = selNode.parentNode;
							if ((curnode.isExpanded()) || (curnode.childNodes.length>0))
							{
								LpuStructureFrame.loader.load(curnode);
								//selNode.on({'expand': {fn: function() {frm.focus();}, scope: selNode, delay: 500}});
								curnode.expand();
							}
							curnode.select();
							LpuStructureFrame.fireEvent('click', curnode);
						}
						else
						{
							swLpuStructureViewForm.reloadCurrentTreeNode(this);
						}
					},
					afterDeleteRecord: function()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);
					}
				});
			swLpuSectionPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swLpuSectionPanel);}.createDelegate(this));

// Табгрид - Участки. 4 уровень
			var swLpuRegionPanel = new sw.Promed.ViewFrame(
				{
					title:langs('Участки'),
					id: 'LpuRegion',
					object: 'LpuRegion',
					editformclassname: 'swLpuRegionEditForm',
					dataUrl: '/?c=LpuStructure&m=getLpuRegion',
					//dataUrl: C_GETOBJECTLIST,
					height:303,
					toolbar: true,
					autoLoadData: false,
					linkedTables: 'MedStaffRegion',
					stringfields:
						[
							{name:'LpuRegion_id', type: 'int', header: 'ID', key: true},
							{name:'LpuRegion_Name',  type: 'int', header: langs('Номер участка'), width: 100},
							{name:'LpuRegion_begDate', type: 'date', header: langs('Дата создания'), width: 100},
							{name:'LpuRegion_endDate', type: 'date', header: langs('Дата закрытия'), width: 100},
							{id: 'autoexpand', name: 'LpuRegion_Descr',  type: 'string', header: langs('Описание участка')}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						],
					afterSaveEditForm: function ()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);
					},
					afterDeleteRecord: function()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);
					}
				});

			swLpuRegionPanel.getGrid().view = new Ext.grid.GridView({
				getRowClass: function (row) {
					var cls = '';
					if (!Ext.isEmpty(row.get('LpuRegion_endDate')))
						cls = cls + 'x-grid-rowgray ';
					if (cls.length == 0)
						cls = 'x-grid-panel';
					return cls;
				}
			});

			swLpuRegionPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swLpuRegionPanel);}.createDelegate(this));

			var statusesStore = new Ext.data.SimpleStore({
				fields: ['statusID', 'status'],
				data : [['1', langs('действующие')], ['2', langs('Закрытые')], ['0', langs('Все')]]
			});
// Табгрид - Обслуживаемые организации
			var swLpuOrgServed = new sw.Promed.ViewFrame({
				title:langs('Обслуживаемые организации'),
				id: 'LpuOrgServed',
				object: 'LpuOrgServed',
				editformclassname: 'swLpuOrgServedEditForm',
				dataUrl: C_GET_LOS,
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields:
					[
						{name: 'Lpu_id', type: 'int', hidden: true, isparams: true},
						{name:'LpuOrgServed_id', type: 'int', header: 'ID', key: true},
						{name:'Org_Name', type: 'string', header: langs('Полное название'), width: 400},
						{name:'Org_Nick', type: 'string', header: langs('Краткое название'), width: 200},
						{name:'LpuOrgServiceType_Name', type: 'string', header: langs('Тип обслуживания'), width: 200},
						{name:'LpuOrgServed_begDate', type: 'date', header: langs('Дата начала'),width:100},
						{name:'LpuOrgServed_endDate', type: 'date', header: langs('Дата окончания'),width:100}
					],
				actions:
					[
						{name:'action_add'},
						{name:'action_edit'},
						//{name:'action_view'},
						{name:'action_view', handler: function(){
							var grid = this.findById('LpuOrgServed').getGrid();
							var record = grid.getSelectionModel().getSelected();
							if ( !record || !record.get('LpuOrgServed_id') ) {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
								return;
							}
							params = {
								action: 'view',
								owner: viewframe,
								LpuOrgServed_id: record.get('LpuOrgServed_id'),
								Lpu_id: record.get('Lpu_id')
							};
							getWnd('swLpuOrgServedEditForm').show(params);
						}.createDelegate(this,['view'])
						},
						{name:'action_delete'},
						{name:'action_refresh'},
						{name:'action_print'}
					]
			});
			swLpuOrgServed.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swLpuOrgServed);}.createDelegate(this));

// Табгрид - Услуги.
			frms.uslugaContentsGrid = new sw.Promed.ViewFrame(
				{
					region: 'center',
					id: frms.id + 'UslugaContentsGrid',
					object: 'UslugaComplex',
					title: langs('Услуги'),
					MedService_id: null,
					paging: true,
					root: 'data',
					totalProperty: 'totalCount',
					dataUrl: '/?c=UslugaComplex&m=loadUslugaContentsGrid',
					// dataUrl: '/?c=UslugaComplex&m=loadUslugaComplexMedServiceGrid', // для сервисов (только 0 уровня)
					autoLoadData: false,
					onDblClick: function(grid, number, object){
						if ( !frms.uslugaContentsGrid.getGrid().getSelectionModel().getSelected() ) {
							return false;
						}

						var record = frms.uslugaContentsGrid.getGrid().getSelectionModel().getSelected();

						if ( !record.get('UslugaComplexMedService_id') ) {
							return false;
						}

						if(record.get('IsLabUsluga') == 0){
							var uslugaContentsGrid = frms.uslugaContentsGrid.getGrid();
							var formParams = new Object();
							var params = new Object();
							if (record.get('MedService_id')) {
								formParams.MedService_id = record.get('MedService_id');
							} else {
								return false;
							}
							formParams.UslugaComplexMedService_id = record.get('UslugaComplexMedService_id');
							formParams.UslugaComplex_id = record.get('UslugaComplex_id');
							formParams.UslugaComplexMedService_pid = record.get('UslugaComplexMedService_pid');
							formParams.UslugaComplexMedService_begDT = record.get('UslugaComplexMedService_begDT');
							formParams.UslugaComplexMedService_endDT = record.get('UslugaComplexMedService_endDT');
							formParams.UslugaComplexMedService_Time = record.get('UslugaComplexMedService_Time');
							formParams.UslugaComplexMedService_IsPortalRec = record.get('UslugaComplexMedService_IsPortalRec');
							formParams.UslugaComplexMedService_IsPay = record.get('UslugaComplexMedService_IsPay');
							formParams.UslugaComplexMedService_IsElectronicQueue = record.get('UslugaComplexMedService_IsElectronicQueue');
							params.action = 'edit';
							params.formMode = 'remote';
							params.formParams = formParams;

							params.callback = function(data) {
								uslugaContentsGrid.getStore().reload();
							};
							getWnd('swUslugaComplexMedServiceEditWindow').show(params);
						} else {
							this.onEnter();
						}
					},
					onEnter: function() {
						// Прогрузить состав выбранной услуги
						if ( !frms.uslugaContentsGrid.getGrid().getSelectionModel().getSelected() ) {
							return false;
						}

						var record = frms.uslugaContentsGrid.getGrid().getSelectionModel().getSelected();

						if ( !record.get('UslugaComplexMedService_id') ) {
							return false;
						}

						frms.showUslugaComplexContents({
							UslugaComplexMedService_id: record.get('UslugaComplexMedService_id'),
							UslugaComplex_Name: record.get('UslugaComplex_Name'),
							level: frms.uslugaNavigationString.getLevel() + 1,
							UslugaCategory_SysNick: record.get('UslugaCategory_SysNick'),
							levelUp: true
						});
					},
					onRowSelect: function(sm, rowIdx, record) {
						/*
						 if ( !record || !record.get('UslugaComplex_id') ) {
						 return false;
						 }

						 // дисаблим акшены для состава услуг служб
						 if (this.MedService_id != null && frms.uslugaNavigationString.getLevel() != 0 ) {
						 this.setActionDisabled('action_edit', true);
						 this.setActionDisabled('action_delete', true);
						 } else {
						 this.setActionDisabled('action_edit', false);
						 this.setActionDisabled('action_delete', false);
						 }
						 */
					},
					stringfields:
						[
							{name: 'UslugaComplexMedService_id', type: 'int', hidden: true, key: true, isparams: true},
							{name: 'UslugaComplex_id', type: 'int', hidden: true},
							{name: 'UslugaComplexMedService_pid', type: 'int', hidden: true, isparams: true},
							{ name: 'CompositionCount', type: 'int', hidden: true },
							{name: 'UslugaCategory_SysNick', type: 'string', hidden: true},
							{name: 'MedService_id', type: 'int', hidden: true, isparams: true},
							{name: 'UslugaCategory_Name', header: langs('Категория'), width: 150},
							{name: 'UslugaComplex_Code', header: langs('Код'), width: 80},
							{name: 'UslugaComplex_Name', header: langs('Наименование'), id: 'autoexpand'},
							{name: 'UslugaComplexMedService_begDT',  type: 'date', header: langs('Дата начала'), width: 100},
							{name: 'UslugaComplexMedService_endDT',  type: 'date', header: langs('Дата окончания'), width: 100},
							{name: 'UslugaComplexMedService_Time', type: 'int', hidden: true },
							{name: 'IsLabUsluga', type: 'int', hidden: true },
							{name: 'UslugaComplexMedService_IsPortalRec', type: 'int', hidden: true },
							{name: 'UslugaComplexMedService_IsPay', type: 'int', hidden: true },
							{name: 'UslugaComplexMedService_IsElectronicQueue', type: 'int', hidden: true }
						],
					actions:
						[
							{name: 'action_add', handler: function() {frms.openUslugaEditWindow('add');}},
							{name: 'action_edit', handler: function() {frms.openUslugaEditWindow('edit');}},
							{name: 'action_view', handler: function() {frms.openUslugaEditWindow('view');}},
							{name: 'action_delete', disabled: false, handler: function() {frms.deleteUsluga();}}
						]

				});

			frms.resourceContentsGrid = new sw.Promed.ViewFrame(
				{
					region: 'center',
					id: frms.id + 'ResourceContentsGrid',
					object: 'UslugaComplex',
					title: langs('Ресурсы'),
					MedService_id: null,
					paging: true,
					root: 'data',
					totalProperty: 'totalCount',
					dataUrl: '/?c=Resource&m=loadResourceMedServiceGrid', // для сервисов (только 0 уровня)
					autoLoadData: false,
					onDblClick: function(grid, number, object){
						this.onEnter();
					},
					onEnter: function() {
						frms.openResourceEditWindow('edit');
					},
					onRowSelect: function(sm, rowIdx, record) {
						/*
						 if ( !record || !record.get('UslugaComplex_id') ) {
						 return false;
						 }

						 // дисаблим акшены для состава услуг служб
						 if (this.MedService_id != null && frms.uslugaNavigationString.getLevel() != 0 ) {
						 this.setActionDisabled('action_edit', true);
						 this.setActionDisabled('action_delete', true);
						 } else {
						 this.setActionDisabled('action_edit', false);
						 this.setActionDisabled('action_delete', false);
						 }
						 */
					},
					stringfields:
						[
							{name: 'Resource_id', type: 'int', hidden: true, key: true, isparams: true},
							{name: 'MedService_id', type: 'int', hidden: true, isparams: true},
							{name: 'Resource_Name', header: langs('Наименование'), id: 'autoexpand'},
							{name: 'Resource_begDT',  type: 'date', header: langs('Дата начала'), width: 100},
							{name: 'Resource_endDT',  type: 'date', header: langs('Дата окончания'), width: 100}
						],
					actions:
						[
							{name: 'action_add', handler: function() {frms.openResourceEditWindow('add');}},
							{name: 'action_edit', handler: function() {frms.openResourceEditWindow('edit');}},
							{name: 'action_view', handler: function() {frms.openResourceEditWindow('view');}},
							{name: 'action_delete', disabled: false, handler: function() {frms.deleteResource();}}
						]
				});
			/*#################таб настройки ПАКС#########################*/

			var swPacsSettings = new sw.Promed.ViewFrame({
				title:langs('Настройки PACS'),
				id: 'LpuPacsSettings',
				object: 'LpuPacsSettings',
				editformclassname: 'swLpuPacsSettingsEditForm',
				dataUrl: '/?c=LpuStructure&m=getLinkFDToRCCList',
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields:
					[
						{name:'LpuSection_id', hidden: true},
						{name:'LpuPacs_id', type: 'int', header: 'PACS-ID', key: true},
						{name:'LpuPacs_aetitle', type: 'string', header: 'AE', width: 200},
						{name:'LpuPacs_desc', type: 'string', header: langs('Описание'), width: 200},
						{name:'LpuPacs_ip', type: 'string', header: 'IP', width: 100},
						{name:'LpuPacs_port', type: 'int', header: langs('Порт'),width:50},
						{name:'LpuPacs_wadoPort', type: 'int', header: langs('Порт WADO'),width:100}
					],
				actions:
					[
						{name:'action_add', handler: function(){
							var grid = this.findById('LpuPacsSettings').getGrid();
							var record = grid.getSelectionModel().getSelected();
							var tree = this.findById('lpu-structure-frame');
							var node = tree.getSelectionModel().selNode;
							params = {
								action: 'add',
								LpuSection_id: node.attributes.object_value,
								globalFilters: {LpuSection_id:node.attributes.object_value},
								callback: function() {
									grid.getStore().reload();
								}
							};
							getWnd('swLpuPacsSettingsEditForm').show(params);
						}.createDelegate(this, ['add'])},

						{name:'action_edit', handler: function(){
							var grid = this.findById('LpuPacsSettings').getGrid();
							var record = grid.getSelectionModel().getSelected();
							if ( !record || !record.get('LpuPacs_id') ) {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
								return;
							}
							params = {
								action: 'edit',
								/*formParams: */
								LpuPacs_aetitle: record.get('LpuPacs_aetitle'),
								LpuPacs_desc: record.get('LpuPacs_desc'),
								LpuPacs_ip: record.get('LpuPacs_ip'),
								LpuPacs_port: record.get('LpuPacs_port'),
								LpuPacs_id: record.get('LpuPacs_id'),
								LpuSection_id: record.get('LpuSection_id'),
								LpuPacs_wadoPort: record.get('LpuPacs_wadoPort'),
								callback: function() {
									grid.getStore().reload();
								}
							};
							//console.log( params );
							getWnd('swLpuPacsSettingsEditForm').show(params);

						}.createDelegate(this, ['edit'])},

						{name:'action_delete', handler: function(){
							var grid = this.findById('LpuPacsSettings').getGrid();
							var record = grid.getSelectionModel().getSelected();
							if ( !record || !record.get('LpuPacs_id') ) {
								return;
							}

							current_window = this;

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
													grid.getStore().reload();
												}
												else {
													sw.swMsg.alert(langs('Ошибка'), langs('При удалении PACS возникли ошибки'));
												}
											},
											params: {
												LpuPacs_id: record.get('LpuPacs_id')
											},
											url: C_DEL_PACSSET
										});
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: langs('Удалить PACS'),
								title: langs('Вопрос')
							})
						}.createDelegate(this)},
						{name:'action_print', hidden: true},
						{name:'action_view', hidden: true},
						{name:'action_refresh', hidden: true}
					]
			});

			/*##########################################*/

			frms.showUslugaComplexContents = function(data) {
				if ( typeof data != 'object' ) {
					return false;
				}

				var uslugaNavigationString = this.uslugaNavigationString;

				if ( data.level == 0 ) {
					uslugaNavigationString.reset();
					// если услуги сервисов
					var params = {
						limit: 100
						,start: 0
						,MedService_id: frms.uslugaContentsGrid.MedService_id
						,UslugaComplexMedService_pid: null
					}
					frms.uslugaContentsGrid.removeAll();
					frms.uslugaContentsGrid.getGrid().setTitle(langs('Услуги на службе'));
					frms.uslugaContentsGrid.loadData({
						url: '/?c=UslugaComplex&m=loadUslugaComplexMedServiceGrid',
						params: params,
						globalFilters: params
					});
				}
				else {
					this.uslugaContentsGrid.removeAll();

					this.uslugaContentsGrid.getGrid().setTitle(langs('Состав услуги'));
					this.uslugaNavigationString.setLevel(0);

					var params = {
						limit: 100
						,start: 0
						,MedService_id: frms.uslugaContentsGrid.MedService_id
						,UslugaComplexMedService_pid: data.UslugaComplexMedService_id
					}

					this.uslugaContentsGrid.loadData({
						url: '/?c=UslugaComplex&m=loadUslugaComplexMedServiceGrid',
						params: params,
						globalFilters: params
					});
				}

				if ( data.levelUp ) {
					uslugaNavigationString.addRecord(data);
				}
				else {
					uslugaNavigationString.update(data);
				}
			};

			frms.uslugaNavigationString = new Ext.Panel({
				addRecord: function(data) {
					this.setLevel(data.level);

					if (!data.UslugaCategory_SysNick) {
						data.UslugaCategory_SysNick = '';
					}

					// BEGIN произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.
					var record;
					this.store.each(function(rec) {
						if ( rec.get('UslugaComplexMedService_id') == data.UslugaComplexMedService_id ) {
							record = rec;
						}
					});

					if (record && record.get('UslugaComplexMedService_id')) {
						this.store.each(function(rec) {
							if ( rec.get('level') > record.get('level') ) {
								this.remove('UslugaComplexCmp_' + rec.get('UslugaComplexMedService_id'));
								this.store.remove(rec);
							}
						}, this);

						this.buttonIntoText(record);
						this.lastRecord = record;
						this.doLayout();
						this.syncSize();
						return;
					}
					// END произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.

					var record = new Ext.data.Record({
						UslugaComplexMedService_id: data.UslugaComplexMedService_id,
						UslugaComplex_Name: data.UslugaComplex_Name,
						UslugaCategory_SysNick: data.UslugaCategory_SysNick,
						level: data.level
					});

					// Добавляем новую запись
					this.store.add([ record ]);

					if ( typeof this.lastRecord == 'object' ) {
						// Предыдущий текст заменяем на кнопку (удаляем текстовую, добавляем кнопку)
						this.textIntoButton(this.lastRecord);
					}

					// добавляем новую текстовую
					this.lastRecord = record;

					this.add({
						border: false,
						id: 'UslugaComplexCmp_' + data.UslugaComplexMedService_id,
						items: [
							new Ext.form.Label({
								record_id: record.id,
								html : "<img src='img/icons/folder16.png'>&nbsp;" + data.UslugaComplex_Name
							})
						],
						layout: 'form',
						style: 'padding: 2px;'
					});

					this.doLayout();
					this.syncSize();
				},
				autoHeight: true,
				buttonAlign: 'left',
				buttonIntoText: function(record) {
					if ( !record || typeof record != 'object' ) {
						return false;
					}

					this.remove('UslugaComplexCmp_' + record.get('UslugaComplexMedService_id'));

					this.add({
						border: false,
						id: 'UslugaComplexCmp_' + record.get('UslugaComplexMedService_id'),
						items: [
							new Ext.form.Label({
								record_id: record.id,
								html : "<img src='img/icons/folder16.png'>&nbsp;" + record.get('UslugaComplex_Name')
							})
						],
						layout: 'form',
						style: 'padding: 2px;'
					});

				},
				currentLevel: 0,
				//frame: true,
				items: [
					//
				],
				lastRecord: null,
				layout: 'column',
				region: 'north',
				getLastRecord: function() {
					var record;
					var level = -1;

					this.store.each(function(rec) {
						if ( rec.get('level') > level ) {
							record = rec;
						}
					});

					return record;
				},
				getLevel: function() {
					return this.currentLevel;
				},
				goToUpperLevel: function() {
					var currentLevel = this.getLevel();

					if ( currentLevel == 0 ) {
						return false;
					}

					var prevLevel = 0;
					var prevRecord = new Ext.data.Record({
						UslugaComplexMedService_id: this.UslugaComplexMedServiceRoot_id,
						UslugaComplex_Name: this.UslugaComplexRoot_Name,
						level: prevLevel
					});

					this.store.each(function(rec){
						if ( rec.get('level') > prevLevel && rec.get('level') < currentLevel ) {
							prevLevel = rec.get('level');
							prevRecord = rec;
						}
					});

					frms.showUslugaComplexContents(prevRecord.data);
				},
				reset: function() {
					this.removeAll();
					this.store.removeAll();

					this.lastRecord = null;
					this.setLevel(0);

					this.addRecord({
						UslugaComplexMedService_id: this.UslugaComplexMedServiceRoot_id,
						UslugaComplex_Name: this.UslugaComplexRoot_Name,
						level: 0
					});
				},
				setLevel: function(level) {
					this.currentLevel = (Number(level) > 0 ? Number(level) : 0);

					if ( this.getLevel() == 0 ) {
						frms.uslugaContentsGrid.setActionDisabled('action_upperfolder', true);
					}
					else {
						frms.uslugaContentsGrid.setActionDisabled('action_upperfolder', false);
					}

					return this;
				},
				store: new Ext.data.SimpleStore({
					data: [
						//
					],
					fields: [
						{name: 'UslugaComplexMedService_id', type: 'int'},
						{name: 'UslugaComplex_Name', type: 'string'},
						{name: 'UslugaCategory_SysNick', type: 'string'},
						{name: 'level', type: 'int'}
					],
					key: 'UslugaComplexMedService_id'
				}),
				style: 'border: 0; padding: 0px; height: 25px; background: #fff;',
				textIntoButton: function(record) {
					if ( !record || typeof record != 'object' ) {
						return false;
					}

					this.remove('UslugaComplexCmp_' + record.get('UslugaComplexMedService_id'));

					this.add({
						layout: 'form',
						id: 'UslugaComplexCmp_' + record.get('UslugaComplexMedService_id'),
						style: 'padding: 2px;',
						border: false,
						items: [
							new Ext.Button({
								handler: function(btn, e) {
									var rec = this.store.getById(btn.record_id);

									if ( rec ) {
										frms.showUslugaComplexContents(rec.data);
									}
								},
								iconCls: 'folder16',
								record_id: record.id,
								text: record.get('UslugaComplex_Name'),
								scope: this
							})
						]
					});
				},
				update: function(data) {
					this.lastRecord = null;

					if ( data.UslugaComplexMedService_id == 0 ) {
						this.reset();
						frms.uslugaContentsGrid.ViewActions.action_upperfolder.setDisabled(true);
					}
					else {
						this.setLevel(data.level);
						frms.uslugaContentsGrid.ViewActions.action_upperfolder.setDisabled(false);

						this.store.each(function(record) {
							if ( record.get('level') > data.level ) {
								this.remove('UslugaComplexCmp_' + record.get('UslugaComplexMedService_id'));
								this.store.remove(record);
								this.doLayout();
								this.syncSize();
							}

							if ( record.get('level') == data.level ) {
								this.buttonIntoText(record);
								this.lastRecord = record;
							}

							return true;
						}, this);
					}
				},
				UslugaComplexMedServiceRoot_id: 0,
				UslugaComplexRoot_Name: langs('Корневая папка')
			});

			frms.uslugaContentsGrid.ViewToolbar.on('render', function(vt){
				this.ViewActions['action_upperfolder'] = new Ext.Action({name:'action_upperfolder', id: 'id_action_upperfolder', disabled: false, handler: function() {frms.uslugaNavigationString.goToUpperLevel();}, text: langs('На уровень выше'), tooltip: langs('На уровень выше'), iconCls: 'x-btn-text', icon: 'img/icons/arrow-previous16.png'});

				vt.insertButton(1, this.ViewActions['action_upperfolder']);

				return true;
			}, frms.uslugaContentsGrid);

			frms.uslugaContentsGrid.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass : function (row, index)
					{
						var cls = '';
						if (row.get('CompositionCount')!=null && row.get('CompositionCount') > 0)
							cls = cls+'x-grid-rowselect ';
						return cls;
					}
				});

			frms.uslugaContentsGridResetFilter = function() {
				frms.uslugaFiltersPanel.getForm().reset();
				frms.uslugaContentsGrid.getGrid().getStore().baseParams.UslugaComplex_CodeName = null;
				frms.uslugaContentsGrid.getGrid().getStore().reload();
			}

			frms.uslugaContentsGridFilter = function() {
				var base_form = frms.uslugaFiltersPanel.getForm();
				frms.uslugaContentsGrid.getGrid().getStore().baseParams.UslugaComplex_CodeName = base_form.findField('UslugaComplex_CodeName').getValue();
				frms.uslugaContentsGrid.getGrid().getStore().reload();
			}

			frms.uslugaFiltersPanel = new Ext.form.FormPanel({
				region: 'center',
				layout: 'column',
				height: 30,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				keys:
					[{
						key: Ext.EventObject.ENTER,
						fn: function(e)
						{
							frms.uslugaContentsGridFilter();
						},
						stopEvent: true
					}],
				items:
					[{
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						columnWidth: 1,
						labelWidth: 40,
						items:
							[{
								anchor: '100%',
								fieldLabel: langs('Услуга'),
								name: 'UslugaComplex_CodeName',
								xtype: 'textfield'
							}]
					},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							width: 80,
							items: [
								new Ext.Button({
									text: BTN_FIND,
									iconCls : 'search16',
									disabled: false,
									handler: function()
									{
										frms.uslugaContentsGridFilter();
									}
								})
							]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							width: 80,
							items: [
								new Ext.Button({
									text: BTN_RESETFILTER,
									iconCls : 'resetsearch16',
									disabled: false,
									handler: function()
									{
										frms.uslugaContentsGridResetFilter();
									}
								})
							]
						}]
			});

			frms.uslugaTopPanel = new Ext.Panel({
				items: [
					frms.uslugaNavigationString,
					frms.uslugaFiltersPanel
				],
				height: 60,
				border: false,
				layout: 'border',
				region: 'north'
			});

			frms.uslugaComplexOnPlaceGridResetFilter = function() {
				frms.uslugaComplexOnPlaceFiltersPanel.getForm().reset();
				frms.uslugaComplexOnPlaceGrid.getGrid().getStore().baseParams.UslugaComplex_CodeName = null;
				frms.uslugaComplexOnPlaceGrid.getGrid().getStore().reload();
			}

			frms.uslugaComplexOnPlaceGridFilter = function() {
				var base_form = frms.uslugaComplexOnPlaceFiltersPanel.getForm();
				frms.uslugaComplexOnPlaceGrid.getGrid().getStore().baseParams.UslugaComplex_CodeName = base_form.findField('UslugaComplex_CodeName').getValue();
				frms.uslugaComplexOnPlaceGrid.getGrid().getStore().reload();
			}

			frms.uslugaComplexOnPlaceFiltersPanel = new Ext.form.FormPanel({
				region: 'north',
				title: langs('Услуги'),
				layout: 'column',
				height: 55,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				keys:
					[{
						key: Ext.EventObject.ENTER,
						fn: function(e)
						{
							frms.uslugaComplexOnPlaceGridFilter();
						},
						stopEvent: true
					}],
				items:
					[{
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						columnWidth: 1,
						labelWidth: 40,
						items:
							[{
								anchor: '100%',
								fieldLabel: langs('Услуга'),
								name: 'UslugaComplex_CodeName',
								xtype: 'textfield'
							}]
					},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							width: 80,
							items: [
								new Ext.Button({
									text: BTN_FIND,
									iconCls : 'search16',
									disabled: false,
									handler: function()
									{
										frms.uslugaComplexOnPlaceGridFilter();
									}
								})
							]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							width: 80,
							items: [
								new Ext.Button({
									text: BTN_RESETFILTER,
									iconCls : 'resetsearch16',
									disabled: false,
									handler: function()
									{
										frms.uslugaComplexOnPlaceGridResetFilter();
									}
								})
							]
						}]
			});

			frms.uslugaComplexOnPlaceGrid  = new sw.Promed.ViewFrame(
				{
					region: 'center',
					id: frms.id + 'uslugaComplexOnPlaceGrid',
					paging: true,
					root: 'data',
					totalProperty: 'totalCount',
					dataUrl: '/?c=UslugaComplex&m=loadUslugaComplexOnPlaceGrid',
					autoLoadData: false,
					onDblClick: function(grid, number, object){
						this.onEnter();
					},
					onEnter: function() {
						var action = this.getAction('action_edit').isDisabled() ? 'view':'edit';
						if (Ext.getCmp('swLpuStructureViewForm').action=='view') {
							action = 'view';
						}
						frms.openUslugaComplexPlaceEditWindow(action);
					},
					onRowSelect: function(sm, rowIdx, record) {
						if ( !record || !record.get('UslugaComplexPlace_id') ) {
							return false;
						}

						var baseParams = this.getGrid().getStore().baseParams;

						if ( !isSuperAdmin() && record && (
								(Ext.isEmpty(record.get('Lpu_id')) && !Ext.isEmpty(baseParams.Lpu_id))
								|| (Ext.isEmpty(record.get('LpuBuilding_id')) && !Ext.isEmpty(baseParams.LpuBuilding_id))
								|| (Ext.isEmpty(record.get('LpuUnit_id')) && !Ext.isEmpty(baseParams.LpuUnit_id))
								|| (Ext.isEmpty(record.get('LpuSection_id')) && !Ext.isEmpty(baseParams.LpuSection_id))
							)) {
							this.setActionDisabled('action_edit', true);
							this.setActionDisabled('action_delete', true);
						} else {
							this.setActionDisabled('action_edit', false);
							this.setActionDisabled('action_delete', false);
						}

						// прогрузить грид тарифов
						frms.uslugaComplexTariffGrid.removeAll();
						frms.uslugaComplexTariffGrid.loadData({
							valueOnFocus: frms.uslugaComplexTariffGrid.focusOnRecord,
							globalFilters: {
								limit: 100,
								start: 0,
								Lpu_id: baseParams.Lpu_id || null,
								LpuBuilding_id: baseParams.LpuBuilding_id || null,
								LpuUnit_id: baseParams.LpuUnit_id || null,
								LpuSection_id: baseParams.LpuSection_id || null,
								UslugaComplexPlace_id: record.get('UslugaComplexPlace_id')
							}
						});
					},
					onLoadData: function() {
						// очищаем грид тарифов
						frms.uslugaComplexTariffGrid.removeAll();
					},
					stringfields:
						[
							{name: 'UslugaComplexPlace_id', type: 'int', hidden: true, key: true},
							{name: 'UslugaComplex_id', type: 'int', hidden: true},
							{name: 'Lpu_id', type: 'int', hidden: true },
							{name: 'LpuBuilding_id', type: 'int', hidden: true },
							{name: 'LpuUnit_id', type: 'int', hidden: true },
							{name: 'LpuSection_id', type: 'int', hidden: true },
							{name: 'UslugaCategory_Name', header: langs('Категория'), width: 150},
							{name: 'UslugaComplexPlace_Name', header: langs('Место оказания'), width: 200},
							{name: 'UslugaComplex_Code', header: langs('Код'), width: 80},
							{name: 'UslugaComplex_Name', header: langs('Наименование'), id: 'autoexpand'},
							{name: 'UslugaComplexPlace_begDate',  type: 'date', header: langs('Дата начала'), width: 100},
							{name: 'UslugaComplexPlace_endDate',  type: 'date', header: langs('Дата окончания'), width: 100},
							{name: 'UslugaComplex_Tariff', header: langs('Активный тариф ОМС'), width: 200}
						],
					actions:
						[
							{ name: 'action_add', handler: function() { frms.openUslugaComplexPlaceEditWindow('add'); } },
							{ name: 'action_edit', handler: function() { frms.openUslugaComplexPlaceEditWindow('edit'); } },
							{ name: 'action_view', handler: function() { frms.openUslugaComplexPlaceEditWindow('view'); } },
							{ name: 'action_delete', handler: function() { frms.deleteUslugaComplexPlace(); } },
							{ name: 'action_refresh' },
							{ name: 'action_print' }
						]
				});
			frms.uslugaComplexOnPlaceGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(frms.uslugaComplexOnPlaceGrid);}.createDelegate(this));

			frms.uslugaComplexTariffGrid  = new sw.Promed.ViewFrame(
				{
					region: 'south',
					id: frms.id + 'uslugaComplexTariffGrid',
					title: langs('Тарифы '),
					height: 250,
					paging: true,
					root: 'data',
					totalProperty: 'totalCount',
					dataUrl: '/?c=UslugaComplex&m=loadUslugaComplexTariffOnPlaceGrid',
					autoLoadData: false,
					onDblClick: function(grid, number, object){
						this.onEnter();
					},
					onEnter: function() {
						var action = this.getAction('action_edit').isDisabled() ? 'view':'edit';
						if (Ext.getCmp('swLpuStructureViewForm').action=='view') {
							action = 'view';
						}
						frms.openUslugaComplexTariffEditWindow(action);
					},
					onRowSelect: function(sm, rowIdx, record) {
						if ( !record || !record.get('UslugaComplexTariff_id') ) {
							return false;
						}

						var baseParams = this.getGrid().getStore().baseParams;

						if ( !isSuperAdmin() && record && (
								(Ext.isEmpty(record.get('Lpu_id')) && !Ext.isEmpty(baseParams.Lpu_id))
								|| (Ext.isEmpty(record.get('LpuBuilding_id')) && !Ext.isEmpty(baseParams.LpuBuilding_id))
								|| (Ext.isEmpty(record.get('LpuUnit_id')) && !Ext.isEmpty(baseParams.LpuUnit_id))
								|| (Ext.isEmpty(record.get('LpuSection_id')) && !Ext.isEmpty(baseParams.LpuSection_id))
							)) {
							this.setActionDisabled('action_edit', true);
							this.setActionDisabled('action_delete', true);
						} else {
							this.setActionDisabled('action_edit', false);
							this.setActionDisabled('action_delete', false);
						}
					},
					focusOnRecord: false,
					onLoadData: function() {
						this.focusOnRecord = false;
					},
					stringfields:
						[
							{ name: 'UslugaComplexTariff_id', type: 'int', header: 'ID', key: true },
							{ name: 'RecordStatus_Code', type: 'int', hidden: true },
							{ name: 'UslugaComplexTariffType_id', type: 'int', hidden: true },
							{ name: 'Lpu_id', type: 'int', hidden: true },
							{ name: 'LpuBuilding_id', type: 'int', hidden: true },
							{ name: 'LpuSection_id', type: 'int', hidden: true },
							{ name: 'LpuUnit_id', type: 'int', hidden: true },
							{ name: 'PayType_id', type: 'int', hidden: true },
							{ name: 'LpuLevel_id', type: 'int', hidden: true },
							{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
							{ name: 'LpuUnitType_id', type: 'int', hidden: true },
							{ name: 'MesAgeGroup_id', type: 'int', hidden: true },
							{ name: 'Sex_id', type: 'int', hidden: true },
							{ name: 'VizitClass_id', type: 'int', hidden: true },
							{ name: 'EvnUsluga_setDate', type: 'date', hidden: true },
							{ name: 'UslugaComplexTariff_Code', type: 'string', header: langs('Код'), width: 50 },
							{ name: 'UslugaComplexTariff_Name', type: 'string', header: langs('Наименование'), width: 100 },
							{ name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 80 },
							{ name: 'UslugaComplexTariffType_Name', type: 'string', header: langs('Тип тарифа'), width: 100 },
							{ name: 'LpuLevel_Name', type: 'string', header: langs('Уровень МО'), width: 100 },
							{ name: 'Lpu_Name', type: 'string', header: langs('МО'), width: 200 },
							{ name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль'), width: 150 },
							{ name: 'LpuUnitType_Name', type: 'string', header: langs('Вид мед. помощи'), width: 100 },
							{ name: 'MesAgeGroup_Name', type: 'string', header: langs('Возрастная группа'), width: 100 },
							{ name: 'Sex_Name', type: 'string', header: langs('Пол пациента'), width: 80 },
							{ name: 'UslugaComplexTariff_Tariff', type: 'float', header: langs('Тариф'), width: 80 },
							{ name: 'UslugaComplexTariff_UED', type: 'float', header: langs('УЕТ врача'), width: 80 },
							{ name: 'UslugaComplexTariff_UEM', type: 'float', header: langs('УЕТ ср. медперсонала'), width: 80 },
							{ name: 'UslugaComplexTariff_begDate', type: 'date', header: langs('Дата начала'), width: 80 },
							{ name: 'UslugaComplexTariff_endDate', type: 'date', header: langs('Дата окончания'), width: 80 },
							{ name: 'pmUser_Name', type: 'string', header: langs('Пользователь'), id: 'autoexpand' }
						],
					actions:
						[
							{ name: 'action_add', handler: function() { frms.openUslugaComplexTariffEditWindow('add'); } },
							{ name: 'action_edit', handler: function() { frms.openUslugaComplexTariffEditWindow('edit'); } },
							{ name: 'action_view', handler: function() { frms.openUslugaComplexTariffEditWindow('view'); } },
							{ name: 'action_delete', handler: function() { frms.deleteUslugaComplexTariff(); } },
							{ name: 'action_refresh' },
							{ name: 'action_print' }
						]
				});
			frms.uslugaComplexTariffGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(frms.uslugaComplexTariffGrid);}.createDelegate(this));

			frms.uslugaLpuLevelPanel = new Ext.Panel({
				items: [
					frms.uslugaComplexOnPlaceFiltersPanel
					,frms.uslugaComplexOnPlaceGrid
					,frms.uslugaComplexTariffGrid
				],
				layout: 'border',
				region: 'center',
				split: true
			});

			frms.uslugaMedServicePanel = new Ext.Panel({
				items: [
					frms.uslugaTopPanel
					,frms.uslugaContentsGrid
				],
				layout: 'border',
				region: 'center',
				split: true
			});

			frms.resourceMedServicePanel = new Ext.Panel({
				items: [
					frms.resourceContentsGrid
				],
				layout: 'border',
				region: 'center',
				split: true
			});

			var swUslugaComplexTreePanel = new Ext.Panel({
				layout: 'card',
				activeItem: 0,
				border: false,
				defaults:
				{
					border:false
				},
				items: [
					frms.uslugaLpuLevelPanel,
					frms.uslugaMedServicePanel
				]
			});
			var swResourceTreePanel = new Ext.Panel({
				layout: 'card',
				activeItem: 0,
				border: false,
				defaults:
				{
					border:false
				},
				items: [
					frms.resourceMedServicePanel
				]
			});

// Табгрид - Участок. 3 уровень
			var swOneRegionGridPanel = new sw.Promed.ViewFrame(
				{
					xtype:'viewframe',
					title:langs('Улицы участка'),
					id: 'LpuRegionStreet',
					object: 'LpuRegionStreet',
					editformclassname: 'swLpuRegionStreetEditWindow',
					dataUrl: C_LPUREGIONSTREET_GET,
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuRegionStreet_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuRegion_id', hidden: true, isparams: true},
							{name: 'KLCountry_id', hidden: true, isparams: true},
							{name: 'KLRGN_id', hidden: true, isparams: true},
							{name: 'KLSubRGN_id', hidden: true, isparams: true},
							{name: 'KLCity_id', hidden: true, isparams: true},
							{name: 'KLTown_id', hidden: true, isparams: true},
							{name: 'KLTown_Name', type: 'string', header: langs('Населенный пункт'), width: 200},
							{name: 'KLStreet_id', hidden: true, isparams: true},
							{name: 'KLStreet_Name', type: 'string', header: langs('Улица'), width: 200},
							{name: 'LpuRegionStreet_IsAll', header: langs('Вся территория'),
								renderer: function(value){
									return (value == 1 ? '<div style="text-align: center;">&#10004;</div>' : '');
								}
							},
							{id: 'autoexpand',name: 'LpuRegionStreet_HouseSet', type: 'string', header: langs('Номера домов')},
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						]
				});
			var swOneRegionPanel = new Ext.Panel(
				{
					id: 'oneregion_panel',
					region:'center',
					layout:'border',
					border: false,
					items:
						[
							new sw.Promed.FormPanel(
								{
									region:'north',
									frame: false,
									bodyStyle:'width:100%;background-color:transparent;padding:1px;padding-top:4px;',
									items:
										[
											{
												anchor: '100%',
												tabIndex: -1,
												disabled: true,
												width: 400,
												name: 'LpuRegionType_id',
												xtype: 'swlpuregiontypecombo',
												id: 'lpustrLpuRegionType_id',
												allowBlank:false
											}
										]})
							,
							swOneRegionGridPanel
						]
				});

// Табгрид - Тарифы отделения. 3 уровень
			var swSectionTariffPanel = new sw.Promed.ViewFrame(
				{
					title:langs('Тарифы отделения'),
					id: 'LpuSectionTariff',
					object: 'LpuSectionTariff',
					editformclassname: 'swLpuSectionTariffEditForm',
					dataUrl: C_LPUSECTIONTARIFF_GET,
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuSectionTariff_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuSection_id', hidden: true, isparams: true},
							{name: 'TariffClass_id', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'TariffClass_Name', type: 'string', header: langs('Вид тарифа')},
							{name: 'LpuSectionTariff_Tariff', type: 'float', header: langs('Тариф'), width: 200},
							{name: 'LpuSectionTariff_TotalFactor', type: 'float', header: langs('Итоговый коэффициент'), width: 200, hidden: !getRegionNick().inlist(['ufa','astra'])},
							{name: 'LpuSectionTariff_setDate', type: 'date', header: langs('Начало'), width: 120},
							{name: 'LpuSectionTariff_disDate', type: 'date', header: langs('Окончание'), width: 120}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						]
				});
			swSectionTariffPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swSectionTariffPanel);}.createDelegate(this));
// Таб - коэффициенты индексации
			frms.loadCoeffIndexTariffGrid = function(params) {
				frms.swCoeffIndexTariffFiltersPanel.getForm().reset();
				var isClose = frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams.isClose;
				frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams = {isClose: isClose, LpuSection_id: params.LpuSection_id};
				frms.swCoeffIndexTariffGrid.getGrid().getStore().load();
			};

			frms.coeffIndexTariffGridResetFilter = function() {
				frms.swCoeffIndexTariffFiltersPanel.getForm().reset();
				var isClose = frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams.isClose;
				var LpuSection_id = frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams.LpuSection_id;
				frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams = {isClose: isClose, LpuSection_id: LpuSection_id};
				frms.swCoeffIndexTariffGrid.getGrid().getStore().reload();
			};

			frms.coeffIndexTariffGridFilter = function() {
				var base_form = frms.swCoeffIndexTariffFiltersPanel.getForm();
				var params = base_form.getValues();
				params.LpuSection_id = frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams.LpuSection_id;
				if (frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams.isClose) {
					params.isClose = frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams.isClose;
				}
				frms.swCoeffIndexTariffGrid.getGrid().getStore().baseParams = params;
				frms.swCoeffIndexTariffGrid.getGrid().getStore().reload();
			};

			frms.openCoeffIndexEditWindow = function(action) {
				if (!action || !action.inlist(['add','edit','view']))
					return false;

				var tree = frms.findById('lpu-structure-frame');
				var node = tree.getSelectionModel().selNode;

				var callback = function(){
					var TariffClassArray = [];
					swSectionTariffPanel.getGrid().getStore().each(function(rec) {
						TariffClassArray.push(rec.get('TariffClass_id'));
					});

					var grid = frms.swCoeffIndexTariffGrid.getGrid();

					var params = new Object();
					params.action = action;
					params.TariffClassArray = TariffClassArray;
					params.formParams = {
						LpuSection_id: node.attributes.object_value
					};

					if (action != 'add') {
						var record = grid.getSelectionModel().getSelected();
						params.formParams.CoeffIndexTariff_id = record.get('CoeffIndexTariff_id');
					};

					params.callback = function(data) {
						grid.getStore().load({params: {LpuSection_id: node.attributes.object_value}});
					};

					getWnd(frms.swCoeffIndexTariffGrid.editformclassname).show(params);
				};

				swSectionTariffPanel.loadData({globalFilters: {LpuSection_id:node.attributes.object_value}, callback: callback});
			};

			frms.swCoeffIndexTariffFiltersPanel = new Ext.form.FormPanel({
				title: langs('Коэффициенты индексации'),
				border: true,
				region: 'north',
				layout: 'column',
				height: 80,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				keys:
					[{
						key: Ext.EventObject.ENTER,
						fn: function(e)
						{
							frms.coeffIndexTariffGridFilter();
						},
						stopEvent: true
					}],
				items:
					[{
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						//columnWidth: 1,
						labelWidth: 80,
						labelAlign: 'right',
						items: [{
							fieldLabel: langs('Вид тарифа'),
							hiddenName: 'TariffClass_id',
							xtype: 'swtariffclasscombo',
							width: 300,
							listWidth: 460
						}, {
							fieldLabel: langs('Период с'),
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'CoeffIndexTariff_begDate'
						}]
					}, {
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						//columnWidth: 1,
						labelWidth: 160,
						labelAlign: 'right',
						items: [{
							fieldLabel: langs('Коэффициент идексации'),
							name: 'CoeffIndex_id',
							xtype: 'swcoeffindexcombo',
							width: 200
						}, {
							fieldLabel : langs('по'),
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'CoeffIndexTariff_endDate'
						}]
					}, {
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						width: 80,
						items: [
							new Ext.Button({
								text: BTN_FIND,
								iconCls : 'search16',
								disabled: false,
								handler: function()
								{
									frms.coeffIndexTariffGridFilter();
								}
							})
						]
					},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							width: 80,
							items: [
								new Ext.Button({
									text: BTN_RESETFILTER,
									iconCls : 'resetsearch16',
									disabled: false,
									handler: function()
									{
										frms.coeffIndexTariffGridResetFilter();
									}
								})
							]
						}]
			});
			frms.swCoeffIndexTariffGrid = new sw.Promed.ViewFrame(
				{
					region: 'center',
					id: 'CoeffIndexTariffGrid',
					object: 'CoeffIndexTariff',
					editformclassname: 'swCoeffIndexTariffEditWindow',
					root: 'data',
					dataUrl: '/?c=CoeffIndex&m=loadCoeffIndexTariffGrid',
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'CoeffIndexTariff_id', type: 'int', header: 'ID', key: true},
							{name: 'TarifClass_id', type: 'int', hidden: true},
							{name: 'CoeffIndex_id', type: 'int', hidden: true},
							{name: 'TariffClass_Name', type: 'string', header: langs('Вид тарифа'), id: 'autoexpand'},
							{name: 'CoeffIndex_Code', type: 'int', header: langs('Код'), width: 60},
							{name: 'CoeffIndex_SysNick', type: 'string', header: langs('Краткое наименование'), width: 200},
							{name: 'CoeffIndexTariff_Value', type: 'float', header: langs('Значение'), width: 100},
							{name: 'CoeffIndexTariff_begDate', type: 'date', header: langs('Начало'), width: 100},
							{name: 'CoeffIndexTariff_endDate', type: 'date', header: langs('Окончание'), width: 100}
						],
					actions:
						[
							{ name:'action_add', handler: function(){frms.openCoeffIndexEditWindow('add');} },
							{ name:'action_edit', handler: function(){frms.openCoeffIndexEditWindow('edit');} },
							{ name:'action_view', handler: function(){frms.openCoeffIndexEditWindow('view');} },
							{ name:'action_delete' },
							{ name:'action_refresh' },
							{ name:'action_print', disabled: true, hidden: true }
						]
				});
			frms.swCoeffIndexTariffGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(frms.swCoeffIndexTariffGrid);}.createDelegate(this));

			// Тарифы отделения СМП
			var swSmpTariffGrid = new sw.Promed.ViewFrame({
				id: 'SmpTariffGrid',
				actions: [
					{name: 'action_add'},
					{name: 'action_edit'},
					{name: 'action_view'},
					{name: 'action_delete', handler: function(){
						var grid = this.findById('SmpTariffGrid').getGrid();
						var record = grid.getSelectionModel().getSelected();
						if ( !record || !record.get('CmpProfileTariff_id') ) {
							return;
						}

						current_window = this;

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
												sw.swMsg.alert(langs('Ошибка'), langs('При удалении тарифа возникли ошибки'));
											}
										},
										params: {
											CmpProfileTariff_id: record.get('CmpProfileTariff_id'),
											Lpu_id: current_window.findById('lpu-structure-frame').Lpu_id
										},
										url: '/?c=LpuPassport&m=deleteSmpTariff'
									});
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Удалить тариф') + '?',
							title: langs('Вопрос')
						})
					}.createDelegate(this)},
					{name: 'action_refresh'},
					{name: 'action_print'}
				],
				object: 'SmpTariff',
				editformclassname: 'swSmpTariffEditWindow',
				autoLoadData: false,
				toolbar: true,
				border: false,
				dataUrl: '/?c=LpuPassport&m=loadSmpTariffGrid',
				stringfields: [
					{name: 'CmpProfileTariff_id', type: 'int', header: 'ID', key: true},
					{name: 'LpuSection_id', hidden: true, isparams: true},
					{name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль'), width: 300},
					{name: 'TariffClass_Name', type: 'string', header: langs('Вид тарифа'), width: 300},
					{name: 'CmpProfileTariff_begDT', type: 'date', format: 'd.m.Y', header: langs('Начало действия'), width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{name: 'CmpProfileTariff_endDT', type: 'date', format: 'd.m.Y', header: langs('Окончание действия'), width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{name: 'CmpProfileTariff__Value', type: 'float', header: langs('Значение'), width: 180}
				]
			});
			swSmpTariffGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swSmpTariffGrid);}.createDelegate(this));

			// Тарифы по ДД
			var swTariffDispGrid = new sw.Promed.ViewFrame({
				id: 'TariffDispGrid',
				actions: [
					{name: 'action_add'},
					{name: 'action_edit'},
					{name: 'action_view'},
					{name: 'action_delete', handler: function(){
						var grid = this.findById('TariffDispGrid').getGrid();
						var record = grid.getSelectionModel().getSelected();
						if ( !record || !record.get('TariffDisp_id') ) {
							return;
						}

						current_window = this;

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
												sw.swMsg.alert(langs('Ошибка'), langs('При удалении тарифа возникли ошибки'));
											}
										},
										params: {
											TariffDisp_id: record.get('TariffDisp_id'),
											Lpu_id: current_window.findById('lpu-structure-frame').Lpu_id
										},
										url: '/?c=LpuPassport&m=deleteTariffDisp'
									});
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Удалить тариф') + '?',
							title: langs('Вопрос')
						})
					}.createDelegate(this)},
					{name: 'action_refresh'},
					{name: 'action_print'}
				],
				object: 'TariffDisp',
				editformclassname: 'swTariffDispEditWindow',
				autoLoadData: false,
				toolbar: true,
				border: false,
				dataUrl: '/?c=LpuPassport&m=loadTariffDispGrid',
				stringfields: [
					{name: 'TariffDisp_id', type: 'int', header: 'ID', key: true},
					{name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль'), width: 300, hidden: (getRegionNick().inlist(['astra','kareliya','pskov']))},
					{name: 'TariffClass_Name', type: 'string', header: langs('Вид тарифа'), width: 300},
					{name: 'AgeGroupDisp_Name', type: 'string', header: langs('Возрастная группа'), width: 100},
					{name: 'Sex_Name', type: 'string', header: langs('Пол'), width: 100},
					{name: 'TariffDisp_begDT', type: 'date', format: 'd.m.Y', header: langs('Начало действия'), width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{name: 'TariffDisp_endDT', type: 'date', format: 'd.m.Y', header: langs('Окончание действия'), width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{name: 'TariffDisp_Tariff', type: 'float', header: (getRegionNick().inlist(['kareliya']) ? langs('Тариф') : langs('Значение')), width: 180},
					{name: 'TariffDisp_TariffDayOff', type: 'float', header: langs('Тариф выходного дня'), width: 180}
				]
			});
			swTariffDispGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swTariffDispGrid);}.createDelegate(this));

			// Тарифы МО
			var swTariffLpuGrid = new sw.Promed.ViewFrame({
				id: 'TariffLpuGrid',
				actions: [
					{name: 'action_add'},
					{name: 'action_edit'},
					{name: 'action_view'},
					{name: 'action_delete', handler: function(){
						var grid = this.findById('TariffLpuGrid').getGrid();
						var record = grid.getSelectionModel().getSelected();
						if ( !record || !record.get('LpuTariff_id') ) {
							return;
						}

						current_window = this;

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
												sw.swMsg.alert(langs('Ошибка'), langs('При удалении тарифа возникли ошибки'));
											}
										},
										params: {
											LpuTariff_id: record.get('LpuTariff_id'),
											Lpu_id: current_window.findById('lpu-structure-frame').Lpu_id
										},
										url: '/?c=LpuPassport&m=deleteTariffLpu'
									});
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Удалить тариф') + '?',
							title: langs('Вопрос')
						})
					}.createDelegate(this)},
					{name: 'action_refresh'},
					{name: 'action_print'}
				],
				object: 'LpuTariff',
				editformclassname: 'swTariffLpuEditWindow',
				autoLoadData: false,
				toolbar: true,
				border: false,
				dataUrl: '/?c=LpuPassport&m=loadTariffLpuGrid',
				stringfields: [
					{name: 'LpuTariff_id', type: 'int', header: 'ID', key: true},
					{name: 'TariffClass_Name', type: 'string', header: langs('Вид тарифа'), width: 300},
					{name: 'LpuTariff_setDate', type: 'date', format: 'd.m.Y', header: langs('Начало действия'), width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{name: 'LpuTariff_disDate', type: 'date', format: 'd.m.Y', header: langs('Окончание действия'), width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{name: 'LpuTariff_Tariff', type: 'float', header: langs('Значение'), width: 180}
				]
			});
			swTariffLpuGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swTariffLpuGrid);}.createDelegate(this));

			// Тарифы (бюджет)
			frms.MedicalCareBudgTypeTariffFilterPanel = new Ext.form.FormPanel({
				border: true,
				collapsible: false,
				region: 'north',
				layout: 'form',
				height: 30,
				bodyStyle: 'background: transparent; padding: 5px;',
				keys: [{
					key: Ext.EventObject.ENTER,
					fn: function(e) {
						form.loadRegistryDataGrid();
					},
					stopEvent: true
				}],
				items: [{
					layout: 'column',
					border: false,
					bodyStyle: 'background: transparent;',
					defaults: {
						labelAlign: 'right',
						bodyStyle: 'background: transparent; padding-left: 10px;'
					},
					items: [{
						layout: 'form',
						border: false,
						width: 400,
						labelWidth: 120,
						items: [{
							anchor: '100%',
							comboSubject: 'MedicalCareBudgType',
							fieldLabel: 'Тип мед. помощи',
							hiddenName: 'MedicalCareBudgType_id',
							xtype: 'swcommonsprcombo'
						}]
					}, {
						layout: 'form',
						border: false,
						width: 250,
						labelWidth: 100,
						items: [{
							anchor: '100%',
							fieldLabel: 'Вид оплаты',
							hiddenName: 'PayType_id',
							loadParams: {
								params: {where: " where PayType_SysNick in ('bud', 'fbud')"}
							},
							xtype: 'swpaytypecombo'
						}]
					}, {
						layout: 'form',
						border: false,
						width: 300,
						labelWidth: 130,
						items: [{
							anchor: '100%',
							comboSubject: 'QuoteUnitType',
							fieldLabel: 'Единица измерения',
							hiddenName: 'QuoteUnitType_id',
							loadParams: {
								params: {where: " where QuoteUnitType_Code in (1, 2, 3)"}
							},
							xtype: 'swcommonsprcombo'
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							tooltip: BTN_FRMSEARCH_TIP,
							xtype: 'button',
							text: BTN_FRMSEARCH,
							icon: 'img/icons/search16.png',
							iconCls: 'x-btn-text',
							handler: function() {
								frms.loadMedicalCareBudgTypeTariffGrid();
							}
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							xtype: 'button',
							text: BTN_FRMRESET,
							icon: 'img/icons/reset16.png',
							iconCls: 'x-btn-text',
							handler: function() {
								var filtersForm = frms.MedicalCareBudgTypeTariffFilterPanel.getForm();
								filtersForm.reset();
								frms.MedicalCareBudgTypeTariffGrid.removeAll(true);
								frms.loadMedicalCareBudgTypeTariffGrid();
							}
						}]
					}]
				}]
			});

			frms.MedicalCareBudgTypeTariffGrid = new sw.Promed.ViewFrame({
				id: 'MedicalCareBudgTypeTariffGrid',
				actions: [
					{name: 'action_add', hidden: true, disabled: true},
					{name: 'action_edit', hidden: true, disabled: true},
					{name: 'action_view'},
					{name: 'action_delete', hidden: true, disabled: true},
					{name: 'action_refresh'},
					{name: 'action_print'}
				],
				object: 'MedicalCareBudgTypeTariff',
				editformclassname: 'swMedicalCareBudgTypeTariffEditWindow',
				autoLoadData: false,
				toolbar: true,
				border: false,
				dataUrl: '/?c=LpuPassport&m=loadMedicalCareBudgTypeTariffGrid',
				paging: true,
				root: 'data',
				totalProperty: 'totalCount',
				stringfields: [
					{name: 'MedicalCareBudgTypeTariff_id', type: 'int', header: 'ID', key: true},
					{name: 'MedicalCareBudgType_Name', type: 'string', header: langs('Тип мед. помощи'), width: 300},
					{name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 120},
					{name: 'QuoteUnitType_Name', type: 'string', header: langs('Единица измерения'), width: 120},
					{name: 'MedicalCareBudgTypeTariff_Value', type: 'float', header: langs('Значение'), width: 180},
					{name: 'MedicalCareBudgTypeTariff_begDT', type: 'date', header: langs('Начало действия'), width: 120},
					{name: 'MedicalCareBudgTypeTariff_endDT', type: 'date', header: langs('Окончание действия'), width: 120}
				]
			});
			frms.MedicalCareBudgTypeTariffGrid.ViewToolbar.on('render', function(vt) {
				return this.addCloseFilterMenu(frms.MedicalCareBudgTypeTariffGrid, 'all');
			}.createDelegate(this));

// Табгрид - Коечный фонд. 3 уровень
			var swSectionBedStateForm = new Ext.form.FormPanel({
				border: false,
				region: 'north',
				height: (Ext.isIE)?195:175,
				layout: 'form',
				url: '/?c=LpuStructure&m=getLpuSectionBedAllQuery',
				bodyStyle: 'padding: 0px 5px 0px 5px;',
				labelAlign: 'right',
				labelWidth: 360,
				items: [
					{
						xtype: 'textfield',
						anchor: '100%',
						readOnly: true,
						style: 'border: 0; background: white;',
						name: 'LpuSection_CommonCount',
						fieldLabel: langs('Общее количество коек в отделении, план')
					}, /*{
					 xtype: 'textfield',
					 name: 'LpuSection_ProfileCount',
					 anchor: '100%',
					 style: 'border: 0; background: white;',
					 readOnly: true,
					 fieldLabel: langs('Из них по основному профилю')
					 }, {
					 xtype: 'textfield',
					 name: 'LpuSection_UzCount',
					 style: 'border: 0; background: white;',
					 anchor: '100%',
					 readOnly: true,
					 fieldLabel: langs('Из них профильных коек')
					 },*/ {
						xtype: 'textfield',
						name: 'LpuSection_Fact',
						anchor: '100%',
						style: 'border: 0; background: white;',
						readOnly: true,
						fieldLabel: langs('Общее количество коек в отделении, факт')
					}, {
						xtype: 'textfield',
						name: 'LpuSection_BedCount',
						style: 'border: 0; background: white;',
						anchor: '100%',
						readOnly: true,
						fieldLabel: langs('Общее количество коек по палатам')
					}, {
						xtype: 'textfield',
						name: 'LpuSection_BedRepair',
						anchor: '100%',
						style: 'border: 0; background: white;',
						readOnly: true,
						fieldLabel: langs('из них на ремонте')
					},
					{
						layout: 'column',
						border: false,
						defaults: {border: false},
						items: [
							{
								layout: 'form',
								items: [
									{
										xtype: 'textfield',
										width: 20,
										style: 'border: 0; background: white;',
										name: 'LpuSection_MaxEmergencyBed',
										readOnly: true,
										fieldLabel: langs('Плановый резерв коек для экстр. госпитализаций, не более')
									}
								]
							},
							{
								layout: 'form',
								style: 'margin-left: 5px;',
								items: [
									{
										id: 'editBedPlanReserveBtn',
										xtype: 'button',
										text: langs('Изменить'),
										handler: function()
										{
											args = {}
											args.LpuSection_CommonCount = this.ownerCt.ownerCt.ownerCt.getForm().findField('LpuSection_CommonCount').getValue();
											args.LpuSection_MaxEmergencyBed = this.ownerCt.ownerCt.ownerCt.getForm().findField('LpuSection_MaxEmergencyBed').getValue();
											args.LpuSection_id = Ext.getCmp('lpu-structure-frame').getSelectionModel().getSelectedNode().attributes.object_value;
											args.onHide = function()
											{
												getWnd('swLpuStructureViewForm').swSectionBedStateForm.getForm().load({
													params: {LpuSection_id: Ext.getCmp('lpu-structure-frame').getSelectionModel().getSelectedNode().attributes.object_value}
												});
											}
											getWnd('swLpuSectionBedPlanReserveEditForm').show(args);
										}
									}
								]
							}
						]
					}
				],
				reader: new Ext.data.JsonReader(
					{
						success: function(){}
					},
					[
						{name: 'LpuSection_CommonCount'},
						//{name: 'LpuSection_ProfileCount'},
						//{name: 'LpuSection_UzCount'},
						{name: 'LpuSection_Fact'},
						{name: 'LpuSection_BedCount'},
						{name: 'LpuSection_BedRepair'},
						{name: 'LpuSection_MaxEmergencyBed'}
					])
			});

			this.swSectionBedStateForm = swSectionBedStateForm;

			var swSectionBedStatePanel = new sw.Promed.ViewFrame(
				{
					title:langs('Койки по профилю'),
					region: 'south',
					id: 'LpuSectionBedState',
					object: 'LpuSectionBedState',
					editformclassname: 'swLpuSectionBedStateEditForm',
					dataUrl: C_LPUSECTIONBEDSTATE_GET,
					height: 200,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuSectionBedState_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuSection_id', hidden: true, isparams: true},
							{name: 'LpuSectionProfile_id', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль')},
							{name: 'LpuSectionBedState_Plan', type: 'string', header: langs('Количество (план)'), width: 150},
							{name: 'LpuSectionBedState_Fact', type: 'string', header: langs('Количество (факт)'), width: 150},
							{name: 'LpuSectionBedState_begDate', type: 'date', header: langs('Дата начала'), width: 150},
							{name: 'LpuSectionBedState_endDate', type: 'date', header: langs('Дата окончания'), width: 150}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						],
					afterSaveEditForm: function() {
						swSectionBedStateForm.getForm().load({params:{LpuSection_id: Ext.getCmp('lpu-structure-frame').getSelectionModel().getSelectedNode().attributes.object_value}});
					}
				});
			this.swSectionBedStatePanel = swSectionBedStatePanel;

// Табгрид - Палаты. 3 уровень
			var swSectionWardPanel = new sw.Promed.ViewFrame(
				{
					region:'center',
					title:langs('Палатная структура'),
					id: 'LpuSectionWard',
					object: 'LpuSectionWard',
					editformclassname: 'swLpuSectionWardEditForm',
					dataUrl: C_LPUSECTIONWARD_GET,
					//height:303,
					toolbar: true,
					autoLoadData: false,
					showOnlyAct: 2,//да
					stringfields:
						[
							{name: 'LpuSectionWard_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuSectionWard_isAct', hidden: true, isparams: true},
							{name: 'LpuSection_id', hidden: true, isparams: true},
							{name: 'LpuWardType_id', hidden: true},
							{name: 'LpuWardType_Code', hidden: true},
							{name: 'Server_id', hidden: true, isparams: true},
							{name: 'pmUser_insID', hidden: true},
							{name: 'pmUser_updID', hidden: true},
							{name: 'LpuSectionWard_Name', type: 'string', header: langs('Наименование'), width: 90},
							{id: 'autoexpand', name: 'LpuWardType_Name', type: 'string', header: langs('Комфортность палаты'), width: 70},
							{name: 'Sex_id', type: 'int', hidden: true, isparams: true},
							{name: 'Sex_Name', type: 'string', header: langs('Тип палаты'), width: 120},
							{name: 'LpuSectionWard_BedCount', type: 'string', header: langs('Количество коек'), width: 150},
							{name: 'LpuSectionWard_BedRepair', type: 'string', header: langs('из них на ремонте'), width: 110},
							{name: 'LpuSectionWard_DayCost', type: 'money', header: langs('Стоимость нахождения в сутки'), width: 250},
							{name: 'LpuSectionWard_setDate', type: 'date', header: langs('Дата начала'), width: 100},
							{name: 'LpuSectionWard_disDate', type: 'date', header: langs('Дата окончания'), width: 100}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						],
					afterSaveEditForm: function() {
						swSectionBedStateForm.getForm().load({params:{LpuSection_id: Ext.getCmp('lpu-structure-frame').getSelectionModel().getSelectedNode().attributes.object_value}});
					}
				});

			this.swSectionWardPanel = swSectionWardPanel;

			var swWardMainPanel = new Ext.Panel(
				{
					id: 'wardmain_panel',
					layout:'border',
					//style: 'background-color:transparent;',
					border: false,
					items:
						[
							{
								region:'north',
								layout: 'form',
								border: false,
								height: 28,
								style: 'width:100%; padding:3px;',
								/*
								 title: langs('Фильтры'),
								 xtype: 'fieldset',*/
								items: [
									{
										border: false,
										layout: 'column',
										items: [{
											border: false,
											style: 'padding-left: 0px;',
											layout: 'form',
											items: [{
												id: 'LpuSectionWard_showAct',
												toggleGroup: 'LpuSectionWard_isAct_toogle',
												xtype: 'button',
												allowDepress: false,
												enableToggle:true,
												pressed:true,
												text:langs('Отображать только действующие'),
												tooltip: langs('Отображать только действующие палаты'),
												toggleHandler: function(button,state) {
													if (state)
													{
														swSectionWardPanel.showOnlyAct = 2;
														swSectionWardPanel.gFilters.LpuSectionWard_isAct = 2;
														swSectionWardPanel.loadData({params: swSectionWardPanel.params, globalFilters: swSectionWardPanel.gFilters});
													}
												}
											}]
										},
											{
												border: false,
												style: 'padding-left: 5px;',
												layout: 'form',
												items: [{
													id: 'LpuSectionWard_showAll',
													toggleGroup: 'LpuSectionWard_isAct_toogle',
													xtype: 'button',
													allowDepress: false,
													enableToggle:true,
													pressed:false,
													text:langs('Отображать все палаты'),
													tooltip: langs('Отображать все палаты'),
													toggleHandler: function(button,state) {
														if (state)
														{
															swSectionWardPanel.showOnlyAct = 0;
															swSectionWardPanel.gFilters.LpuSectionWard_isAct = 0;
															swSectionWardPanel.loadData({params: swSectionWardPanel.params, globalFilters: swSectionWardPanel.gFilters});
														}
													}
												}]
											}]
									}]
							}
							,swSectionWardPanel
						]
				});

// Табгрид - Финансирование отделения. 3 уровень

			var swSectionFinansPanel = new sw.Promed.ViewFrame(
				{
					title:langs('Источники финансирования'),
					id: 'LpuSectionFinans',
					object: 'LpuSectionFinans',
					editformclassname: 'swLpuSectionFinansEditForm',
					dataUrl: C_LPUSECTIONFINANS_GET,
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuSectionFinans_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuSection_id', hidden: true, isparams: true},
							{name: 'PayType_id', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'PayType_Name', type: 'string', header: langs('Вид оплаты')},
							{name: 'IsMRC_Name', type: 'string', header: langs('МРЦ'), width: 70},
							{name: 'LpuSectionFinans_IsMRC', hidden: true},
							{name: 'IsQuoteOff_Name', type: 'string', header: langs('Отк.квоту'), width: 70},
							{name: 'LpuSectionFinans_IsQuoteOff', hidden: true},
							{name: 'LpuSectionFinans_begDate', type: 'date', header: langs('Начало'), width: 120},
							{name: 'LpuSectionFinans_endDate', type: 'date', header: langs('Окончание'), width: 120},
							{name: 'LpuSectionFinans_Plan', type: 'string', header: langs('План работы койки'), width: 180}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						]
				});
			swSectionFinansPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swSectionFinansPanel);}.createDelegate(this));

// Табгрид - Финансирование отделения. 3 уровень
			var swSectionShiftPanel = new sw.Promed.ViewFrame(
				{
					title: langs('Количество смен койки'),
					id: 'LpuSectionShift',
					object: 'LpuSectionShift',
					editformclassname: 'swLpuSectionShiftEditForm',
					dataUrl: C_LPUSECTIONSHIFT_GET,
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuSectionShift_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuSection_id', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'LpuSectionShift_Count', type: 'int', header: langs('Количество')},
							{name: 'LpuSectionShift_setDate', type: 'date', header: langs('Начало'), width: 120},
							{name: 'LpuSectionShift_disDate', type: 'date', header: langs('Окончание'), width: 120}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						]
				});

// Табгрид - Лицензирование отделения. 3 уровень
			var swSectionLicencePanel = new sw.Promed.ViewFrame(
				{
					title:langs('Лицензии отделения МО'),
					id: 'LpuSectionLicence',
					object: 'LpuSectionLicence',
					editformclassname: 'swLpuSectionLicenceEditForm',
					dataUrl: C_LPUSECTIONLICENSE_GET,
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'LpuSectionLicence_id', type: 'int', header: 'ID', key: true},
							{name: 'LpuSection_id', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'LpuSectionLicence_Num', type: 'string', header: langs('Номер')},
							{name: 'LpuSectionLicence_begDate', type: 'date', header: langs('Начало'), width: 120},
							{name: 'LpuSectionLicence_endDate', type: 'date', header: langs('Окончание'), width: 120}
						],
					actions:
						[
							{name:'action_add'},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						]
				});
			swSectionLicencePanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swSectionLicencePanel);}.createDelegate(this));

// Табгрид - Тарифы МЭС. 3 уровень
			var swSectionTariffMesPanel = new sw.Promed.ViewFrame({
				title:langs('Тарифы ') + getMESAlias(),
				id: 'LpuSectionTariffMes',
				object: 'LpuSectionTariffMes',
				editformclassname: 'swLpuSectionTariffMesEditForm',
				dataUrl: C_LPUSECTIONTARIFFMES_GET,
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields:
					[
						{name: 'LpuSectionTariffMes_id', type: 'int', header: 'ID', key: true},
						{name: 'LpuSection_id', hidden: true, isparams: true},
						{name: 'Mes_id', hidden: true, isparams: true},
						{name: 'Mes_Code', type: 'string', header: langs('Код') + getMESAlias()},
						{name: 'LpuSectionTariffMes_Tariff', type: 'string', header: langs('Тариф') + getMESAlias()},
						{id: 'autoexpand', name: 'Diag_Name', type: 'string', header: langs('Наименование') + getMESAlias()},
						{name: 'LpuSectionTariffMes_setDate', type: 'date', header: langs('Начало'), width: 120},
						{name: 'LpuSectionTariffMes_disDate', type: 'date', header: langs('Окончание'), width: 120},
						{name: 'TariffMesType_id', hidden: true, isparams: true},
						{name: 'TariffMesType_Name', type: 'string', header: langs('Тип тарифа'), width: 180}
					],
				actions:
					[
						{name:'action_add'},
						{name:'action_edit'},
						{name:'action_view'},
						{name:'action_delete'},
						{name:'action_refresh'},
						{name:'action_print'}
					]
			});

// Табгрид - Планирование. 3 уровень
			var swSectionPlanPanel = new sw.Promed.ViewFrame({
				title:langs('Планирование'),
				id: 'LpuSectionPlan',
				object: 'LpuSectionPlan',
				editformclassname: 'swLpuSectionPlanEditForm',
				dataUrl: C_LPUSECTIONPLAN_GET,
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields:
					[
						{name: 'LpuSectionPlan_id', type: 'int', header: 'ID', key: true},
						{name: 'LpuSection_id', hidden: true, isparams: true},
						{name: 'LpuSectionPlanType_id', hidden: true, isparams: true},
						{id: 'autoexpand', name: 'LpuSectionPlanType_Name', type: 'string', header: langs('Признак планирования')},
						{name: 'LpuSectionPlan_setDate', type: 'date', header: langs('Начало'), width: 120},
						{name: 'LpuSectionPlan_disDate', type: 'date', header: langs('Окончание'), width: 120}
					],
				actions:
					[
						{name:'action_add'},
						{name:'action_edit'},
						{name:'action_view'},
						{name:'action_delete'},
						{name:'action_refresh'},
						{name:'action_print'}
					]
			});

			frms.swSectionQuotePanelFilter = function() {
				var base_form = frms.swSectionQuoteFilters.getForm();
				frms.swSectionQuotePanel.getGrid().getStore().baseParams.LpuSectionQuote_Year = base_form.findField('LpuSectionQuote_Year').getValue();
				frms.swSectionQuotePanel.getGrid().getStore().baseParams.PayType_id = base_form.findField('PayType_id').getValue();
				frms.swSectionQuotePanel.getGrid().getStore().baseParams.LpuUnitType_id = base_form.findField('LpuUnitType_id').getValue();
				frms.swSectionQuotePanel.getGrid().getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
				frms.swSectionQuotePanel.getGrid().getStore().reload();
			}

			frms.swSectionQuoteFilters = new Ext.form.FormPanel({
				region: 'north',
				layout: 'column',
				title:langs('Планирование'),
				height: 80,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				keys:
					[{
						key: Ext.EventObject.ENTER,
						fn: function(e)
						{
							frms.swSectionQuotePanelFilter();
						},
						stopEvent: true
					}],
				items:
					[{
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						labelAlign: 'right',
						columnWidth: .30,
						labelWidth: 80,
						items:
							[ new Ext.ux.form.Spinner({
								xtype: 'numberfield',
								width: 70,
								name: 'LpuSectionQuote_Year',
								strategy: new Ext.ux.form.Spinner.NumberStrategy({minValue:'1990', maxValue: new Date().getFullYear()+1}),
								listeners: {
									'spin': function(combo) {
										var year = combo.getValue();
										if (year == (new Date().getFullYear()+1) && (new Date().getMonth()) > 8) {
											this.setValue(new Date().getFullYear()+1);
										} else if (year > new Date().getFullYear()) {
											this.setValue(new Date().getFullYear());
										} else if (year < 1990) {
											this.setValue(1990);
										}
										frms.swSectionQuotePanelFilter();
									}
								},
								fieldLabel: langs('Год'),
								value: new Date().getFullYear()
							}), {
								fieldLabel : langs('Вид оплаты'),
								hiddenName: 'PayType_id',
								xtype: 'swpaytypecombo',
								anchor: '100%',
								useCommonFilter: true
							}]
					},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							labelAlign: 'right',
							columnWidth: .60,
							labelWidth: 120,
							items: [
								{
									hiddenName: 'LpuUnitType_id',
									fieldLabel: langs('Вид мед. помощи'),
									anchor: '100%',
									xtype: 'swlpuunittypecombo'
								},
								{
									hiddenName: 'LpuSectionProfile_id',
									fieldLabel: langs('Профиль'),
									anchor: '100%',
									xtype: 'swlpusectionprofilecombo'
								}
							]
						},
						{
							layout: 'form',
							border: false,
							bodyStyle:'padding: 4px;background:#DFE8F6;',
							width: 80,
							items: [
								new Ext.Button({
									text: BTN_RESETFILTER,
									iconCls : 'resetsearch16',
									disabled: false,
									handler: function()
									{
										frms.swSectionQuoteFilters.getForm().reset();
										frms.swSectionQuotePanelFilter();
									}
								}),
								new Ext.Button({
									text: BTN_FIND,
									iconCls : 'search16',
									style:'margin-top:4px;',
									disabled: false,
									handler: function()
									{
										frms.swSectionQuotePanelFilter();
									}
								})
							]
						}]
			});

// Табгрид - Планирование. 0 уровень
			frms.swSectionQuotePanel = new sw.Promed.ViewFrame({
				id: 'LpuSectionQuote',
				object: 'LpuSectionQuote',
				editformclassname: 'swLpuSectionQuoteEditForm',
				dataUrl: C_LPUSECTIONQUOTE_GET,
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields:
					[
						{name: 'LpuSectionQuote_id', type: 'int', header: 'ID', key: true},
						{name: 'Lpu_id', hidden: true, isparams: true},
						{name: 'LpuUnitType_id', hidden: true, isparams: true},
						{name: 'LpuSectionProfile_id', hidden: true, isparams: true},
						{name: 'LpuSectionQuote_begDate', type: 'date', header: langs('Дата начала'), width: 120},
						{name: 'LpuSectionQuote_Year', type: 'int', header: langs('Год'), width: 120},
						{name: 'LpuUnitType_Name', autoexpand: true, header: langs('Вид медицинской помощи')},
						{name: 'LpuSectionProfile_Name', width: 300, header: langs('Профиль')},
						{name: 'LpuSectionQuote_Count', type: 'float', header: langs('Ограничение'), width: 120},
						{name: 'QuoteUnitType_Name', width:120,header:langs('Единицы измерения')},
						{name: 'LpuSectionQuote_Fact', type: 'float', header: langs('Фактически выполнено'), width: 140},
						{name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 140}
					]
			});

			frms.swDopDispPlanPanelFilter = function(Lpu_id) {
				var base_form = frms.swDopDispPlanFilters.getForm();
				if (!base_form.isValid())
				{
					sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function()
							{
								frms.swDopDispPlanFilters.getFirstInvalidEl().focus(true);
							},
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
					return false;
				}

				frms.swDopDispPlanPanel.getGrid().getStore().baseParams.PersonDopDispPlan_Year = base_form.findField('PersonDopDispPlan_Year').getValue();
				frms.swDopDispPlanPanel.getGrid().getStore().baseParams.DispDopClass_id = base_form.findField('DispDopClass_id').getValue();

				var DispDopClass_id = base_form.findField('DispDopClass_id').getValue();
				var LpuRegionType_ids = null;
				switch (DispDopClass_id) {
					case 1:
						LpuRegionType_ids = [1];
						break;
					case 2:
						LpuRegionType_ids = [2];
						break;
					case 4:
						LpuRegionType_ids = [2,4];
						break;
				}

				var columnModel = frms.swDopDispPlanPanel.getGrid().getColumnModel();
				if (DispDopClass_id.inlist([4,5])){
					columnModel.setHidden(9, false);
				}
				else{
					columnModel.setHidden(9, true);
				}

				frms.swDopDispPlanPanel.params = {
					PersonDopDispPlan_Year: base_form.findField('PersonDopDispPlan_Year').getValue(),
					DispDopClass_id: DispDopClass_id,
					LpuRegionType_ids: LpuRegionType_ids
				};
				if (!Ext.isEmpty(Lpu_id)) {
					frms.swDopDispPlanPanel.getGrid().getStore().baseParams.Lpu_id = Lpu_id;
					frms.swDopDispPlanPanel.params.Lpu_id = Lpu_id;
				}
				frms.swDopDispPlanPanel.loadData();
			}

			frms.swDopDispPlanFilters = new Ext.form.FormPanel({
				region: 'north',
				layout: 'form',
				labelWidth: 80,
				labelAlign: 'right',
				title:langs('План диспансеризации'),
				height: 60,
				bodyStyle:'width:100%;background:#DFE8F6;padding:5px;',
				keys:
					[{
						key: Ext.EventObject.ENTER,
						fn: function(e)
						{
							frms.swDopDispPlanPanelFilter();
						},
						stopEvent: true
					}],
				items:
					[
						{
							layout: 'column',
							border: false,
							bodyStyle:'background:#DFE8F6;',
							defaults: {bodyStyle:'background:#DFE8F6;', border: false},
							items: [
								{
									layout: 'form',
									labelWidth: 40,
									items: [
										new Ext.ux.form.Spinner({
											allowBlank: (getRegionNick() == 'by'),
											xtype: 'numberfield',
											width: 70,
											name: 'PersonDopDispPlan_Year',
											strategy: new Ext.ux.form.Spinner.NumberStrategy({minValue:'1990', maxValue: new Date().getFullYear()+1}),
											listeners: {
												'spin': function(combo) {
													var year = combo.getValue();
													if (year == (new Date().getFullYear()+1) && (new Date().getMonth()) > 8) {
														this.setValue(new Date().getFullYear()+1);
													} else if (year > new Date().getFullYear()) {
														this.setValue(new Date().getFullYear());
													} else if (year < 1990) {
														this.setValue(1990);
													}
													frms.swDopDispPlanPanelFilter();
												}
											},
											fieldLabel: langs('Год'),
											value: new Date().getFullYear()
										})
									]
								},
								{
									layout: 'form',
									labelWidth: 240,
									style: 'margin-left: 5px;',
									items: [
										{
											allowBlank: (getRegionNick() == 'by'),
											comboSubject: 'DispDopClass',
											codeField: 'DispDopClass_Code',
											fieldLabel: langs('Тип диспансеризации / осмотров'),
											id: 'DP_DispDopClass_id',
											lastQuery:'',
											value: 1,
											autoLoad: true,
											listeners: {
												'select': function(field, newValue) {
													frms.swDopDispPlanPanelFilter();
												},
												'render': function(field, v){
													if(field.getStore().getCount() > 0){
														var num = field.getStore().getAt(0).get('DispDopClass_id');
														field.setValue(num);
													}
												}
											},
											width: 300,
											xtype: 'swcommonsprcombo'
										}
									]
								}
							]
						}
					]
			});

// План диспансеризации
			frms.swDopDispPlanPanel = new sw.Promed.ViewFrame({
				id: 'PersonDopDispPlan',
				object: 'PersonDopDispPlan',
				editformclassname: 'swPersonDopDispPlanEditForm',
				dataUrl: C_PERSONDOPDISPPLAN_GET,
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields:
					[
						{name: 'PersonDopDispPlan_id', type: 'int', header: 'ID', key: true},
						{name: 'Lpu_id', hidden: true, isparams: true},
						{name: 'groups',  hidden: true, isparams: true },
						{name: 'DispDopClass_id', hidden: true, isparams: true},
						{name: 'LpuSectionProfile_id', hidden: true, isparams: true},
						{name: 'PersonDopDispPlan_Year', type: 'int', header: langs('Год'), width: 120},
						{name: 'PersonDopDispPlan_Month', type: 'int', hidden: true},
						{name: 'PersonDopDispPlan_MonthName', type: 'string', header: langs('Месяц'), width: 120},
						{name: 'EducationInstitutionType_id', type: 'int', hidden: true},
						{name: 'EducationInstitutionType_Name', type: 'string', header: langs('Тип образовательного учреждения'), width: 300, hidden: true},
						{name: 'LpuRegion_Name', autoexpand: true, header: langs('№ участка')},
						{name: 'PersonDopDispPlan_Plan', type: 'int', header: langs('План'), width: 140},
						{name: 'QuoteUnitType_Name', width:120,header:langs('Единицы измерения')}
					],
				actions:
					[
						{name: 'action_add', disabled:!(isSuperAdmin() || (getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1))}
					]
			});

			frms.swDopDispPlanPanel.ViewGridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {//http://redmine.swan.perm.ru/issues/20275#note-42
				var isLpu = rec.get('Lpu_id'),
					groups = rec.get('groups')?rec.get('groups'):[],
					thereIsSuperAdmin = false
				actions = frms.swDopDispPlanPanel.ViewActions;
				if (groups.toString().indexOf('SuperAdmin') != -1) {
					thereIsSuperAdmin = true;
				}
				if(rec.get('PersonDopDispPlan_id')){
					if (isSuperAdmin() || ( getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1 && !thereIsSuperAdmin && isLpu == getGlobalOptions().lpu_id)) {
						actions.action_delete.setDisabled( false );
						actions.action_edit.setDisabled( false );
					} else if ( getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1 && thereIsSuperAdmin) {
						actions.action_delete.setDisabled( true );
						actions.action_edit.setDisabled( true );
					}
				}
			}.createDelegate(this));

// Табгрид - Службы. Все уровни.
			var swMedServicePanel = new sw.Promed.ViewFrame(
				{
					title: langs('Службы'),
					id: 'MedService',
					object: 'MedService',
					editformclassname: 'swMedServiceEditWindow',
					dataUrl: '/?c=MedService&m=loadGrid',
					height:303,
					toolbar: true,
					autoLoadData: false,
					defaultFilterValues: {
						is_All: 1 //Все службы
						,is_Act: 0 //Актуальные службы
					},
					stringfields:
						[
							{name: 'MedService_id', type: 'int', header: 'ID', key: true},
							{name: 'Lpu_id', type: 'int', hidden: true, isparams: true},
							{name: 'LpuBuilding_id', type: 'int', hidden: true, isparams: true},
							{name: 'LpuUnit_id', type: 'int', hidden: true, isparams: true},
							{name: 'LpuUnitType_id', type: 'int', hidden: true, isparams: true},
							{name: 'LpuSection_id', type: 'int', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'MedService_Name',  type: 'string', header: langs('Наименование'), width: 150},
							{name: 'MedService_Nick',  type: 'string', header: langs('Краткое наименование'), width: 150},
							{name: 'MedServiceType_SysNick',  type: 'string', hidden: true},
							{name: 'MedServiceType_Name',  type: 'string', header: langs('Тип'), width: 250},
							{name: 'MedService_begDT',  type: 'date', header: langs('Дата создания'), width: 100},
							{name: 'MedService_endDT',  type: 'date', header: langs('Дата закрытия'), width: 100}
						],
					actions:
						[
							{
								name: 'action_add',
								handler: function() {
									var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
									var Lpu_id = swLpuStructureViewForm.getNodeParentAttribute(node, 'Lpu', 'object_value');
									var LpuUnit_id = null;
									var LpuUnitType_id = null;
									var LpuSection_id = null;
									var LpuBuilding_id = null;


									if ( node.attributes.object == 'Lpu' )
									{
										//Lpu_id = node.attributes.object_value;
									}
									else if ( node.attributes.object == 'LpuBuilding' )
									{
										//Lpu_id = node.parentNode.attributes.object_value;
										LpuBuilding_id = node.attributes.object_value;
									}
									else if ( node.attributes.object == 'LpuUnitType' )
									{
										//Lpu_id = node.parentNode.parentNode.attributes.object_value;
										LpuBuilding_id = node.parentNode.attributes.object_value;
										LpuUnitType_id = node.attributes.LpuUnitType_id;
									}
									else if ( node.attributes.object == 'LpuUnit' )
									{
										//Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
										LpuBuilding_id = node.parentNode.parentNode.attributes.object_value;
										LpuUnitType_id = node.parentNode.attributes.LpuUnitType_id;
										LpuUnit_id = node.attributes.object_value;
									}
									else if ( node.attributes.object == 'LpuSection' )
									{

										if (swLpuStructureViewForm.getNodeTrueLevel(node) == 6)
										{
											return false;

										}
										else
										{
											//Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
											LpuBuilding_id = node.parentNode.parentNode.parentNode.attributes.object_value;
											LpuUnitType_id = node.parentNode.parentNode.attributes.LpuUnitType_id;
											LpuUnit_id = node.parentNode.attributes.object_value;
											LpuSection_id = node.attributes.object_value;
										}
									}
									var args = {};
									args.action = 'add';
									args.Lpu_id = Lpu_id;
									args.LpuBuilding_id = LpuBuilding_id;
									args.LpuUnitType_id = LpuUnitType_id;
									args.LpuUnit_id = LpuUnit_id;
									args.LpuSection_id = LpuSection_id;
									var viewframe = this.findById('MedService');
									args.owner = viewframe;
									args.callback = function(){
										if (!viewframe.ViewActions.action_refresh.isDisabled())
										{
											viewframe.ViewActions.action_refresh.execute();
										}
										swLpuStructureViewForm.reloadCurrentTreeNode(this);
									}.createDelegate(this);
									getWnd('swMedServiceEditWindow').show(args);
								}.createDelegate(this)
							},
							{name: 'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						],
					onRowSelect:function (sm, rowIdx, record) {
						var sel = this.getGrid().getSelectionModel().getSelected();
						if (this.getAction('action_schedule')) {
							this.getAction('action_schedule').setDisabled(Ext.isEmpty(sel.get('MedService_id')) || (sel && 'remoteconsultcenter' == sel.get('MedServiceType_SysNick')));
						}
					},
					afterDeleteRecord: function()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);
					}
				});
			swMedServicePanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swMedServicePanel);}.createDelegate(this));

			this.swMedServicePanel = swMedServicePanel;

// Табгрид - Врачи служб.
			var swMedServiceMedPersonalPanel = new sw.Promed.ViewFrame(
				{
					title: langs('Сотрудники на службе'),
					id: 'MedServiceMedPersonal',
					object: 'MedServiceMedPersonal',
					editformclassname: 'swMedServiceMedPersonalEditWindow',
					dataUrl: '/?c=MedService&m=loadMedServiceMedPersonalGrid',
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'MedServiceMedPersonal_id', type: 'int', header: 'ID', key: true},
							{name: 'MedService_id', type: 'int', hidden: true, isparams: true},
							{name: 'MedPersonal_id', type: 'int', hidden: true, isparams: true},
							{name: 'Server_id', type: 'int', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'MedPersonal_Name',  type: 'string', header: langs('Сотрудник'), width: 150},
							{name: 'MedServiceMedPersonal_begDT',  type: 'date', header: langs('Дата начала'), width: 100},
							{name: 'MedServiceMedPersonal_endDT',  type: 'date', header: langs('Дата окончания'), width: 100},
							{name: 'MedServiceMedPersonal_IsTransfer',  type: 'checkbox', header: langs('Передавать в ЕГИСЗ'), width: 150}
						],
					actions:
						[
							{name: 'action_add'},
							{name: 'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						]
				});

			this.swMedServiceMedPersonalPanel = swMedServiceMedPersonalPanel;


// Табгрид - Профили консультирования
			var swLpuSectionProfileMedServicePanel = new sw.Promed.ViewFrame({
				title: langs('Профили консультирования'),
				id: 'LpuSectionProfileMedServicePanel',
				object: 'LpuSectionProfileMedService',
				editformclassname: 'swLpuSectionProfileMedServiceEditWindow',
				dataUrl: '/?c=MedService&m=loadLpuSectionProfileGrid',
				height:303,
				toolbar: true,
				autoLoadData: false,
				stringfields: [
					{name: 'LpuSectionProfileMedService_id', type: 'int', header: 'ID', key: true},
					{name: 'MedService_id', type: 'int', hidden: true, isparams: true},
					{name: 'LpuSectionProfile_id', type: 'int', hidden: true, isparams: true},
					{id: 'autoexpand', name: 'LpuSectionProfile_Name',  type: 'string', header: langs('Профиль'), width: 150},
					{name: 'LpuSectionProfileMedService_begDT',  type: 'date', header: langs('Дата начала'), width: 100},
					{name: 'LpuSectionProfileMedService_endDT',  type: 'date', header: langs('Дата окончания'), width: 100}
				],
				actions: [
					{name: 'action_add'},
					{name: 'action_edit'},
					{name:'action_view'},
					{name:'action_delete'},
					{name:'action_refresh'},
					{name:'action_print'}
				]
			});

			this.swLpuSectionProfileMedServicePanel = swLpuSectionProfileMedServicePanel;

// Табгрид - Службы. Все уровни.
			var swStoragePanel = new sw.Promed.ViewFrame(
				{
					title: langs('Склады'),
					id: 'LSVF_StoragePanel',
					object: 'Storage',
					editformclassname: 'swStorageEditWindow',
					dataUrl: '/?c=Storage&m=loadStorageGrid',
					height:303,
					toolbar: true,
					autoLoadData: false,
					paging: false,
					root: 'data',
					stringfields:
						[
							{name: 'StorageStructLevel_id', type: 'int', header: 'ID', key: true},
							{name: 'Storage_id', type: 'int', hidden: true},
							{name: 'StorageType_id',  type: 'int', hidden: true},
							{name: 'Lpu_id', type: 'int', hidden: true},
							{name: 'LpuBuilding_id', type: 'int', hidden: true},
							{name: 'LpuUnit_id', type: 'int', hidden: true},
							{name: 'LpuUnitType_id', type: 'int', hidden: true},
							{name: 'LpuSection_id', type: 'int', hidden: true},
							{name: 'Storage_Code',  type: 'string', header: langs('Номер'), width: 60},
							{name: 'Storage_Name',  type: 'string', header: langs('Наименование'), width: 140},
							{name: 'StorageStructLevel_Name',  type: 'string', header: langs('Уровень'), width: 140},
							{name: 'Storage_pName',  type: 'string', header: langs('Подчинен складу'), width: 140},
							{name: 'MedService_Nick',  type: 'string', header: langs('Служба'), width: 140},
							{name: 'StorageType_Name',  type: 'string', header: langs('Тип'), width: 120},
							{id: 'autoexpand', name: 'Address_Address',  type: 'string', header: langs('Адрес')},
							{name: 'Storage_begDate',  type: 'date', header: langs('Дата открытия'), width: 100},
							{name: 'Storage_endDate',  type: 'date', header: langs('Дата закрытия'), width: 100}
						],
					actions:
						[
							{
								name: 'action_add',
								handler: function() {
									var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
									args = {struct: {
										Lpu_id: null,
										LpuBuilding_id: null,
										LpuUnit_id: null,
										LpuSection_id: null,
										MedService_id: null,
										Org_id: null,
										OrgStruct_id: null
									}};
									args.action = 'add';
									args.mode = 'lpu';

									var lookNode = node;
									var attr;
									while(lookNode != null) {
										attr = lookNode.attributes;
										if (!args.struct[attr.object_id] && attr.object_value) {
											args.struct[attr.object_id] = attr.object_value;
										}
										lookNode = lookNode.parentNode;
									}

									var viewframe = this.findById('LSVF_StoragePanel');
									var frm = this;
									args.owner = viewframe;
									args.callback = function(){
										if (!viewframe.ViewActions.action_refresh.isDisabled())
										{
											viewframe.ViewActions.action_refresh.execute();
										}
										swLpuStructureViewForm.reloadCurrentTreeNode(frm);
									};
									getWnd('swStorageEditWindow').show(args);
								}.createDelegate(this)
							},
							{
								name:'action_edit',
								handler: function() {
									var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
									var viewframe = this.findById('LSVF_StoragePanel');

									var record = viewframe.getGrid().getSelectionModel().getSelected();
									if (!record || Ext.isEmpty(record.get('Storage_id'))) {
										return false;
									}
									args = {struct: {
										Lpu_id: null,
										LpuBuilding_id: null,
										LpuUnit_id: null,
										LpuSection_id: null,
										MedService_id: null,
										Org_id: null,
										OrgStruct_id: null
									}};
									args.action = 'edit';
									args.mode = 'lpu';

									var lookNode = node;
									var attr;
									while(lookNode != null) {
										attr = lookNode.attributes;
										if (!args.struct[attr.object_id] && attr.object_value) {
											args.struct[attr.object_id] = attr.object_value;
										}
										lookNode = lookNode.parentNode;
									}

									args.owner = viewframe;
									args.formParams = {
										Storage_id: record.get('Storage_id')
									};
									var frm = this;
									args.callback = function(){
										if (!viewframe.ViewActions.action_refresh.isDisabled())
										{
											viewframe.ViewActions.action_refresh.execute();
										}
										swLpuStructureViewForm.reloadCurrentTreeNode(frm);
									};
									getWnd('swStorageEditWindow').show(args);
								}.createDelegate(this)
							},
							{
								name:'action_view',
								handler: function() {
									var viewframe = this.findById('LSVF_StoragePanel');
									var record = viewframe.getGrid().getSelectionModel().getSelected();
									if (!record || Ext.isEmpty(record.get('Storage_id'))) {
										return false;
									}
									var args = {};
									args.action = 'view';
									args.mode = 'lpu';

									args.owner = viewframe;
									args.formParams = {
										Storage_id: record.get('Storage_id')
									};
									getWnd('swStorageEditWindow').show(args);
								}.createDelegate(this)
							},
							{
								name:'action_delete',
								handler: function() {
									var frm = this;
									var viewframe = this.findById('LSVF_StoragePanel');
									var record = viewframe.getGrid().getSelectionModel().getSelected();
									if (!record || Ext.isEmpty(record.get('Storage_id'))) {
										return false;
									}

									sw.swMsg.show({
										buttons:Ext.Msg.YESNO,
										fn:function (buttonId, text, obj) {
											if (buttonId == 'yes') {
												var loadMask = new Ext.LoadMask(frms.getEl(), {msg:langs('Удаление...')});
												loadMask.show();
												Ext.Ajax.request({
													callback:function (options, success, response) {
														loadMask.hide();
														if (success) {
															var response_obj = Ext.util.JSON.decode(response.responseText);
															if (response_obj.success == false) {
																sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении произошла ошибка!'));
															}
															else {
																viewframe.ViewActions.action_refresh.execute();
																swLpuStructureViewForm.reloadCurrentTreeNode(frm);
															}
															/*if (grid.getStore().getCount() > 0) {
															 grid.getView().focusRow(0);
															 grid.getSelectionModel().selectFirstRow();
															 }*/
														}
														/*else {
														 sw.swMsg.alert(langs('Ошибка'), langs('При удалении склада возникли ошибки'));
														 }*/
													},
													params:{
														Storage_id: record.get('Storage_id')
													},
													url:'/?c=Storage&m=deleteStorage'
												});
											}
										},
										icon:Ext.MessageBox.QUESTION,
										msg:langs('Склад будет удален со всех структурных уровней. <br/>Вы хотите удалить склад?'),
										title:langs('Подтверждение')
									});
								}.createDelegate(this)
							},
							{name:'action_refresh'},
							{name:'action_print'}
						],
					afterDeleteRecord: function()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);
					}
				});
			swStoragePanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(swStoragePanel);}.createDelegate(this));

			this.swStoragePanel = swStoragePanel;

// Табгрид - персонал склада.
			var swStorageMedPersonalPanel = new sw.Promed.ViewFrame(
				{
					title: langs('Сотрудники'),
					id: 'StorageMedPersonalPanel',
					object: 'StorageMedPersonal',
					editformclassname: 'swStorageMedPersonalEditWindow',
					dataUrl: '/?c=Storage&m=loadStorageMedPersonalGrid',
					height:303,
					toolbar: true,
					autoLoadData: false,
					paging: false,
					root: 'data',
					stringfields:
						[
							{name: 'StorageMedPersonal_id', type: 'int', header: 'ID', key: true},
							{name: 'Storage_id', type: 'int', hidden: true, isparams: true},
							{name: 'MedPersonal_id', type: 'int', hidden: true, isparams: true},
							{name: 'Server_id', type: 'int', hidden: true, isparams: true},
							{id: 'autoexpand', name: 'MedPersonal_Name',  type: 'string', header: langs('Сотрудник'), width: 150},
							{name: 'StorageMedPersonal_begDT',  type: 'date', header: langs('Дата начала'), width: 100},
							{name: 'StorageMedPersonal_endDT',  type: 'date', header: langs('Дата окончания'), width: 100}
						],
					actions:
						[
							{
								name: 'action_add',
								handler: function() {
									var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
									args = {};
									args.action = 'add';
									args.Lpu_id = swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id;
									args.Storage_id = node.attributes.object_value;

									var viewframe = this.findById('StorageMedPersonalPanel');

									args.owner = viewframe;
									args.callback = function(){
										if (!viewframe.ViewActions.action_refresh.isDisabled())
										{
											viewframe.ViewActions.action_refresh.execute();
										}
									};
									getWnd('swStorageMedPersonalEditWindow').show(args);
								}.createDelegate(this)
							},
							{name: 'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						]
				});

			this.swStorageMedPersonalPanel = swStorageMedPersonalPanel;

			frms.swAnalyzerPanel = new sw.Promed.ViewFrame(
				{
					title: langs('Анализаторы'),
					id: 'AnalyzerPanel',
					object: 'Analyzer',
					uniqueId: true,
					editformclassname: 'swAnalyzerEquipmentEditWindow',
					scheme: 'lis',
					dataUrl: '/?c=Analyzer&m=loadList',
					height:303,
					toolbar: true,
					autoLoadData: false,
					onRowSelect:function () {
						var sel = frms.swAnalyzerPanel.getGrid().getSelectionModel().getSelected();
						if (sel) {
							var params = {
								Analyzer_id: sel.get('Analyzer_id')
								,AnalyzerTest_pid: null
								//,Analyzer_IsUseAutoReg: sel.get('Analyzer_IsUseAutoReg')
							};
							frms.AnalyzerTestGrid.Analyzer_id = sel.get('Analyzer_id');
							frms.AnalyzerTestGrid.Analyzer_IsUseAutoReg = sel.get('Analyzer_IsUseAutoReg');
							frms.AnalyzerTestGrid.AnalyzerModel_id = sel.get('AnalyzerModel_id');
							frms.NavigationString.reset();
							if (!Ext.isEmpty(params.Analyzer_id)) {
								frms.AnalyzerTestGrid.loadData({
									params: params,
									globalFilters: params
								})
							} else {
								frms.AnalyzerTestGrid.removeAll();
							}
						}
					},
					stringfields:
						[
							{name: 'Analyzer_id', type: 'int', header: 'ID', key: true},
							{name: 'Analyzer_Name', type: 'string', header: langs('Наименование анализатора'), width: 120},
							{name: 'Analyzer_Code', type: 'string', header: langs('Код'), width: 60},
							{name: 'AnalyzerModel_id_Name', type: 'string', header: langs('Модель анализатора'), width: 100, hidden: true, hideable: true},
							{name: 'AnalyzerModel_id', type: 'int', hidden: true},
							{name: 'MedService_id_Name', type: 'string', header: langs('Служба'), hidden: true, width: 120},
							{name: 'MedService_id', type: 'int', hidden: true},
							{name: 'Analyzer_IsUseAutoReg', type: 'int', hidden: true},
							{name: 'Analyzer_begDT', type: 'date', header: langs('Дата открытия'), width: 80, hidden: true, hideable: true},
							{name: 'Analyzer_endDT', type: 'date', header: langs('Дата закрытия'), width: 60, hidden: true, hideable: true},
							{name: 'Analyzer_2wayComm', type: 'checkcolumnedit', header: langs('Двустор. связь'), width: 80},
							{name: 'Analyzer_IsUseAutoReg', type: 'checkcolumnedit', header: langs('Учет реактивов'), width: 80},
							{name: 'Analyzer_IsNotActive', type: 'checkcolumnedit', header: langs('Неактивный'), width: 80}
						],
					saveRecord: function(o) {
						let record = o.record;
						let field = o.field;
						let params = {};
						
						if (Ext.isEmpty(record.get('Analyzer_id')))
							return;

						if (frms.action === 'view') {
							record.set(o.field, !record.get( o.field ));
							record.commit();
							return;
						}
						
						params.Analyzer_id = record.get('Analyzer_id');
						let value = record.get(o.field) ? 2 : 1 ;
						switch(field) {
							case 'Analyzer_IsNotActive':
								params.Analyzer_IsNotActive = value;
								break;
							case 'Analyzer_2wayComm':
								params.Analyzer_2wayComm = value;
								break;
							case 'Analyzer_IsUseAutoReg':
								params.Analyzer_IsUseAutoReg = value;
								break;
						}
						
						frms.getLoadMask(langs('Пожалуйста подождите...')).show();
						Ext.Ajax.request({
							url: '/?c=Analyzer&m=saveAnalyzerField',
							params: params,
							callback: function(o, success, response) {
								frms.getLoadMask().hide();
								if( success ) {
									var obj = Ext.decode(response.responseText);
									if( obj.success ) {
										record.set(field, record.get(field));
									} else {
										sw.swMsg.alert(langs('Внимание'), langs('Не получилось сохранить!'));
										record.set(field, !record.get(field));
									}
									record.commit();
								}
							}
						});

					},
					actions:
						[
							{ name: 'action_add', id: 'LSVF_addanalyzer', handler: function() {}, menu: new Ext.menu.Menu({
								items: [{
									text: langs('из ПроМед'),
									tooltip: langs('Добавить анализатор из Промеда'),
									handler: function() {
										frms.openAnalyzerEditWindow('add', false);
									}
								}, {
									text: langs('из ЛИС'),
									tooltip: langs('Добавить анализатор из ЛИС'),
									handler: function() {
										frms.openAnalyzerEditWindow('add', true);
									}
								}]
							})},
							{ name: 'action_edit' },
							{ name: 'action_view' },
							{ name: 'action_delete', handler: function() { frms.deleteAnalyzer(); }},
							{ name: 'action_refresh' },
							{ name: 'action_print' }
						]
				});

			frms.AnalyzerTestGrid = new sw.Promed.ViewFrame({
				actions:[
					{name:'action_add', text: langs('Добавить тест'),
						handler: function() {
							frms.openAnalyzerTestEditWindow('add', 2);
						}
					},
					{name:'action_edit', handler: function() { frms.openAnalyzerTestEditWindow('edit', 0); }},
					{name:'action_view', hidden:true},
					{name:'action_delete', handler: function() { frms.deleteAnalyzerTest(); }},
					{name:'action_print', hidden:false}
				],
				onDblClick: function(grid, number, object){
					this.onEnter();
				},
				onEnter: function() {
					// Прогрузить состав выбранной услуги
					if ( !frms.AnalyzerTestGrid.getGrid().getSelectionModel().getSelected() ) {
						return false;
					}

					var record = frms.AnalyzerTestGrid.getGrid().getSelectionModel().getSelected();

					if ( !record.get('AnalyzerTest_id') ) {
						return false;
					}

					if (record.get('AnalyzerTest_isTest') == 2) {
						// открыть на редактирование
						frms.openAnalyzerTestEditWindow('edit', 0);
					} else {
						frms.showAnalyzerTestContents({
							AnalyzerTest_id: record.get('AnalyzerTest_id'),
							AnalyzerTest_Name: record.get('AnalyzerTest_Name'),
							level: frms.NavigationString.getLevel() + 1,
							levelUp: true
						});
					}
				},
				autoExpandColumn:'autoexpand',
				autoExpandMin:150,
				autoLoadData:false,
				border:true,
				dataUrl:'/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
				height:180,
				region:'center',
				object: 'AnalyzerTest',
				uniqueId: true,
				root: 'data',
				totalProperty: 'totalCount',
				editformclassname:'swAnalyzerTestEditWindow',
				style:'margin-bottom: 10px',
				stringfields:[
					{name:'AnalyzerTest_id', type:'int', header:'ID', key:true},
					{name:'AnalyzerTest_pid', type:'int', hidden:true, isparams:true},
					{name:'Analyzer_id', type:'int', hidden:true, isparams:true},
					{name:'AnalyzerTest_Code', type:'string', header:langs('Код теста'), width:120},
					{name:'AnalyzerTest_Name', type:'string', header:langs('Наименование теста'), width:120, id: 'autoexpand'},
					{name:'AnalyzerTest_begDT', type: 'date', header: langs('Дата начала'), width: 80},
					{name:'AnalyzerTest_endDT', type: 'date', header: langs('Дата окончания'), width: 80},
					{name:'AnalyzerTest_SortCode', type:'int', header:langs('Код сортировки'), width: 80},
					{name:'AnalyzerTest_SysNick', type:'string', header:langs('Мнемоника'), width:80},
					{name:'AnalyzerTestType_id_Name', type:'string', header:langs('Тип теста'), width:120},
					{name:'Unit_Name', type:'string', header:langs('Единица измерения'), width:120},
					{name:'AnalyzerTest_IsNotActive', type: 'checkcolumnedit', header: langs('Неактивный'), width: 40},
					{name:'AnalyzerTest_HasLisLink', type: 'checkcolumn', header: langs('Связь с ЛИС'), width: 40},
					{name:'AnalyzerTestType_id', type:'int', hidden:true},
					{name:'AnalyzerTest_isTest', type:'int', hidden:true},
					{name:'Unit_id', type:'int', hidden:true}
				],
				saveAnalyzerTestNotActive: function(record) {
					frms.getLoadMask(langs('Пожалуйста подождите...')).show();
					Ext.Ajax.request({
						url: '/?c=AnalyzerTest&m=saveAnalyzerTestNotActive',
						params: {
							AnalyzerTest_id: record.get('AnalyzerTest_id'),
							AnalyzerTest_IsNotActive: record.get('AnalyzerTest_IsNotActive')?2:1
						},
						callback: function(o, success, response) {
							frms.getLoadMask().hide();
							if( success ) {
								var obj = Ext.decode(response.responseText);
								if( obj.success ) {
									record.set('AnalyzerTest_IsNotActive', record.get('AnalyzerTest_IsNotActive'));
									record.commit();
								}
							}
						}
					});
				},
				saveRecord: function(o) {
					var viewframe = this;
					var record = o.record;

					if (!Ext.isEmpty(record.get('AnalyzerTest_id')) && !Ext.isEmpty(record.get('AnalyzerTest_IsNotActive'))) {
						viewframe.saveAnalyzerTestNotActive(record);
					}
				},
				title:langs('Исследования и тесты'),
				toolbar:true
			});

			frms.AnalyzerTestGrid.ViewToolbar.on('render', function(vt){
				this.ViewActions['action_addisl'] = new Ext.Action({name:'action_addisl', id: 'id_action_addisl', disabled: (frms.action=='view'), hidden: (frms.action=='view'), handler: function() {frms.openAnalyzerTestEditWindow('add', 1);}, text: langs('Добавить исследование'), tooltip: langs('Добавить исследование'), iconCls: 'x-btn-text', icon: 'img/icons/add16.png'});
				this.ViewActions['action_upperfolder'] = new Ext.Action({name:'action_upperfolder', id: 'id_action_upperfolder', disabled: false, handler: function() {frms.NavigationString.goToUpperLevel();}, text: langs('На уровень выше'), tooltip: langs('На уровень выше'), iconCls: 'x-btn-text', icon: 'img/icons/arrow-previous16.png'});

				vt.insertButton(1, this.ViewActions['action_upperfolder']);
				vt.insertButton(1, this.ViewActions['action_addisl']);

				return true;
			}, frms.AnalyzerTestGrid);

			frms.AnalyzerTestGrid.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass : function (row, index)
					{
						var cls = '';
						if (row.get('AnalyzerTest_isTest') == 1)
							cls = cls+'x-grid-rowselect ';
						return cls;
					}
				});

			frms.NavigationString = new Ext.Panel({
				addRecord: function(data) {
					this.setLevel(data.level);

					// BEGIN произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.
					var record;
					this.store.each(function(rec) {
						if ( rec.get('AnalyzerTest_id') == data.AnalyzerTest_id ) {
							record = rec;
						}
					});

					if (record && record.get('AnalyzerTest_id')) {
						this.store.each(function(rec) {
							if ( rec.get('level') > record.get('level') ) {
								this.remove('AnalyzerTestCmp_' + rec.get('AnalyzerTest_id'));
								this.store.remove(rec);
							}
						}, this);

						this.buttonIntoText(record);
						this.lastRecord = record;
						this.doLayout();
						this.syncSize();
						return;
					}
					// END произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.

					var record = new Ext.data.Record({
						AnalyzerTest_id: data.AnalyzerTest_id,
						AnalyzerTest_Name: data.AnalyzerTest_Name,
						level: data.level
					});

					// Добавляем новую запись
					this.store.add([ record ]);

					if ( typeof this.lastRecord == 'object' ) {
						// Предыдущий текст заменяем на кнопку (удаляем текстовую, добавляем кнопку)
						this.textIntoButton(this.lastRecord);
					}

					// добавляем новую текстовую
					this.lastRecord = record;

					this.add({
						border: false,
						id: 'AnalyzerTestCmp_' + data.AnalyzerTest_id,
						items: [
							new Ext.form.Label({
								record_id: record.id,
								html : "<img src='img/icons/folder16.png'>&nbsp;" + data.AnalyzerTest_Name
							})
						],
						layout: 'form',
						style: 'padding: 2px;'
					});

					this.doLayout();
					this.syncSize();
				},
				autoHeight: true,
				buttonAlign: 'left',
				buttonIntoText: function(record) {
					if ( !record || typeof record != 'object' ) {
						return false;
					}

					this.remove('AnalyzerTestCmp_' + record.get('AnalyzerTest_id'));

					this.add({
						border: false,
						id: 'AnalyzerTestCmp_' + record.get('AnalyzerTest_id'),
						items: [
							new Ext.form.Label({
								record_id: record.id,
								html : "<img src='img/icons/folder16.png'>&nbsp;" + record.get('AnalyzerTest_Name')
							})
						],
						layout: 'form',
						style: 'padding: 2px;'
					});

				},
				currentLevel: 0,
				//frame: true,
				items: [
					//
				],
				lastRecord: null,
				layout: 'column',
				region: 'north',
				getLastRecord: function() {
					var record;
					var level = -1;

					this.store.each(function(rec) {
						if ( rec.get('level') > level ) {
							record = rec;
						}
					});

					return record;
				},
				getLevel: function() {
					return this.currentLevel;
				},
				goToUpperLevel: function() {
					var currentLevel = this.getLevel();

					if ( currentLevel == 0 ) {
						return false;
					}

					var prevLevel = 0;
					var prevRecord = new Ext.data.Record({
						AnalyzerTest_id: this.AnalyzerTestRoot_id,
						AnalyzerTest_Name: this.AnalyzerTestRoot_Name,
						level: prevLevel
					});

					this.store.each(function(rec){
						if ( rec.get('level') > prevLevel && rec.get('level') < currentLevel ) {
							prevLevel = rec.get('level');
							prevRecord = rec;
						}
					});

					frms.showAnalyzerTestContents(prevRecord.data);
				},
				reset: function() {
					this.removeAll();
					this.store.removeAll();

					this.lastRecord = null;
					this.setLevel(0);

					this.addRecord({
						AnalyzerTest_id: this.AnalyzerTestRoot_id,
						AnalyzerTest_Name: this.AnalyzerTestRoot_Name,
						level: 0
					});
				},
				setLevel: function(level) {
					this.currentLevel = (Number(level) > 0 ? Number(level) : 0);

					if ( this.getLevel() == 0 ) {
						frms.AnalyzerTestGrid.setActionDisabled('action_upperfolder', true);
					}
					else {
						frms.AnalyzerTestGrid.setActionDisabled('action_upperfolder', false);
					}

					return this;
				},
				store: new Ext.data.SimpleStore({
					data: [
						//
					],
					fields: [
						{name: 'AnalyzerTest_id', type: 'int'},
						{name: 'AnalyzerTest_Name', type: 'string'},
						{name: 'level', type: 'int'}
					],
					key: 'AnalyzerTest_id'
				}),
				style: 'border: 0; padding: 0px; height: 25px; background: #fff;',
				textIntoButton: function(record) {
					if ( !record || typeof record != 'object' ) {
						return false;
					}

					this.remove('AnalyzerTestCmp_' + record.get('AnalyzerTest_id'));

					this.add({
						layout: 'form',
						id: 'AnalyzerTestCmp_' + record.get('AnalyzerTest_id'),
						style: 'padding: 2px;',
						border: false,
						items: [
							new Ext.Button({
								handler: function(btn, e) {
									var rec = this.store.getById(btn.record_id);

									if ( rec ) {
										frms.showAnalyzerTestContents(rec.data);
									}
								},
								iconCls: 'folder16',
								record_id: record.id,
								text: record.get('AnalyzerTest_Name'),
								scope: this
							})
						]
					});
				},
				update: function(data) {
					this.lastRecord = null;

					if ( data.AnalyzerTest_id == 0 ) {
						this.reset();
						frms.AnalyzerTestGrid.ViewActions.action_upperfolder.setDisabled(true);
					}
					else {
						this.setLevel(data.level);
						frms.AnalyzerTestGrid.ViewActions.action_upperfolder.setDisabled(false);

						this.store.each(function(record) {
							if ( record.get('level') > data.level ) {
								this.remove('AnalyzerTestCmp_' + record.get('AnalyzerTest_id'));
								this.store.remove(record);
								this.doLayout();
								this.syncSize();
							}

							if ( record.get('level') == data.level ) {
								this.buttonIntoText(record);
								this.lastRecord = record;
							}

							return true;
						}, this);
					}
				},
				AnalyzerTestRoot_id: 0,
				AnalyzerTestRoot_Name: langs('Корневая папка')
			});

			frms.deleteAnalyzerTest = function() {
				var grid = this.AnalyzerTestGrid.getGrid();
				var gridAnalyzer = this.swAnalyzerPanel.getGrid();
				if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('AnalyzerTest_id')) {
					return false;
				}
				var record = grid.getSelectionModel().getSelected();
				var Analyzer_id = gridAnalyzer.getSelectionModel().getSelected().get('Analyzer_id');
				sw.swMsg.show({
					buttons:Ext.Msg.YESNO,
					fn:function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							var loadMask = new Ext.LoadMask(frms.getEl(), {msg:langs('Удаление...')});
							loadMask.show();
							Ext.Ajax.request({
								callback:function (options, success, response) {
									loadMask.hide();
									if (success) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (response_obj.success == false) {
											sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении произошла ошибка!'));
										}
										else {
											grid.getStore().remove(record);
										}
										if (grid.getStore().getCount() > 0) {
											grid.getView().focusRow(0);
											grid.getSelectionModel().selectFirstRow();
										}
									}
									else {
										sw.swMsg.alert(langs('Ошибка'), langs('При удалении теста анализатора возникли ошибки'));
									}
								},
								params:{
									Analyzer_id: Analyzer_id,
									AnalyzerTest_pid: record.get('AnalyzerTest_pid'),
									AnalyzerTest_id: record.get('AnalyzerTest_id'),
								},
								url:'/?c=AnalyzerTest&m=delete'
							});
						}
					},
					icon:Ext.MessageBox.QUESTION,
					msg:langs('Вы хотите удалить запись?'),
					title:langs('Подтверждение')
				});
			};

			frms.deleteAnalyzer = function() {
				var grid = this.swAnalyzerPanel.getGrid();
				if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Analyzer_id')) {
					return false;
				}
				var record = grid.getSelectionModel().getSelected();
				sw.swMsg.show({
					buttons:Ext.Msg.YESNO,
					fn:function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							var loadMask = new Ext.LoadMask(frms.getEl(), {msg:langs('Удаление...')});
							loadMask.show();
							Ext.Ajax.request({
								callback:function (options, success, response) {
									loadMask.hide();
									if (success) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (response_obj.success == false) {
											sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении произошла ошибка!'));
										}
										else {
											grid.getStore().remove(record);
										}
										if (grid.getStore().getCount() > 0) {
											grid.getView().focusRow(0);
											grid.getSelectionModel().selectFirstRow();
										}
									}
									else {
										sw.swMsg.alert(langs('Ошибка'), langs('При удалении анализатора возникли ошибки'));
									}
								},
								params:{
									Analyzer_id:record.get('Analyzer_id')
								},
								url:'/?c=Analyzer&m=delete'
							});
						}
					},
					icon:Ext.MessageBox.QUESTION,
					msg:langs('Вы хотите удалить запись?'),
					title:langs('Подтверждение')
				});
			};

			frms.showAnalyzerTestContents = function(data) {
				if ( typeof data != 'object' ) {
					return false;
				}

				var NavigationString = this.NavigationString;

				if ( data.level == 0 ) {
					NavigationString.reset();
					// если услуги сервисов
					var params = {
						Analyzer_id: frms.AnalyzerTestGrid.Analyzer_id
						,AnalyzerTest_pid: null
					}
					frms.AnalyzerTestGrid.removeAll();
					frms.AnalyzerTestGrid.loadData({
						url: '/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
						params: params,
						globalFilters: params
					});
				}
				else {
					this.AnalyzerTestGrid.removeAll();

					this.NavigationString.setLevel(0);

					var params = {
						Analyzer_id: frms.AnalyzerTestGrid.Analyzer_id
						,AnalyzerTest_pid: data.AnalyzerTest_id
					}

					this.AnalyzerTestGrid.loadData({
						url: '/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
						params: params,
						globalFilters: params
					});
				}

				if ( data.levelUp ) {
					NavigationString.addRecord(data);
				}
				else {
					NavigationString.update(data);
				}
			};

			frms.openAnalyzerEditWindow = function(action, fromLIS) {
				var grid = frms.swAnalyzerPanel.getGrid();
				var selected_record = grid.getSelectionModel().getSelected();

				if (action == 'edit' && (!selected_record || Ext.isEmpty(selected_record.get('Analyzer_id'))))
				{
					return false;
				}

				var p = {
					MedService_id: grid.getStore().baseParams.MedService_id,
					fromLIS: fromLIS,
					action: action,
					callback:function () {
						frms.swAnalyzerPanel.loadData();
					}
				};

				if (action == 'edit') {
					p.Analyzer_id = selected_record.get('Analyzer_id');
				}

				getWnd('swAnalyzerEquipmentEditWindow').show(p);
			};

			frms.openAnalyzerTestEditWindow = function(action, type) {
				var grid = frms.AnalyzerTestGrid.getGrid();
				//var IsUseAutoReg = frms.AnalyzerTestGrid.Analyzer_IsUseAutoReg;
				var selected_record = grid.getSelectionModel().getSelected();

				if (action == 'edit' && (!selected_record || Ext.isEmpty(selected_record.get('AnalyzerTest_id'))))
				{
					return false;
				}

				var sel = frms.swAnalyzerPanel.getGrid().getSelectionModel().getSelected();
				if (sel) {
					var Analyzer_id = sel.get('Analyzer_id');
					var AnalyzerTest_pid = frms.AnalyzerTestGrid.getGrid().getStore().baseParams.AnalyzerTest_pid;
					if (!Ext.isEmpty(AnalyzerTest_pid) && type == 1)
					{
						sw.swMsg.alert(langs('Внимание'),langs('Нельзя добавить исследование в состав исследования'));
						return false;
					}

					var p = {
						Analyzer_IsUseAutoReg: frms.AnalyzerTestGrid.Analyzer_IsUseAutoReg,
						AnalyzerTest_pid: AnalyzerTest_pid,
						Analyzer_id: Analyzer_id,
						AnalyzerTest_isTest: type,
						action: action,
						callback:function () {
							frms.AnalyzerTestGrid.loadData();
						}
					};

					if (action == 'edit') {
						p.AnalyzerTest_id = selected_record.get('AnalyzerTest_id');
						type = selected_record.get('AnalyzerTest_isTest');
						//} else if (action == 'add' && p.Analyzer_IsUseAutoReg == 1) {

					}

					switch(type) {
						case 2:
							getWnd('swAnalyzerTestEditWindow').show(p);
							break;
						default:
							getWnd('swAnalyzerTargetEditWindow').show(p);
							break;
					}
				} else {
					sw.swMsg.alert(langs('Не выбран анализатор'),langs('Пожалуйста, выберите анализатор'));
				}
			};

			frms.AnalyzerLeftPanel = new Ext.Panel({
				region:'west',
				border:false,
				layout:'border',
				style:'padding-right: 5px',
				split: true,
				width: 500,
				layoutConfig:{
					titleCollapse:true,
					animate:true,
					activeOnTop:false
				},
				items:[
					frms.swAnalyzerPanel
				]
			});

			frms.AnalyzerRightPanel = new Ext.Panel({
				region:'center',
				border:false,
				layout:'border',
				height:150,
				layoutConfig:{
					titleCollapse:true,
					animate:true,
					activeOnTop:false
				},
				items:[
					frms.NavigationString,
					frms.AnalyzerTestGrid
				]
			});

			frms.swApparatusPanel = new sw.Promed.ViewFrame(
				{
					title: langs('Аппараты'),
					id: 'Apparatus',
					object: 'MedService',
					editformclassname: 'swApparatusEditWindow',
					dataUrl: '/?c=MedService&m=loadApparatusList',
					height:303,
					toolbar: true,
					autoLoadData: false,
					stringfields:
						[
							{name: 'MedService_id', type: 'int', header: 'ID', key: true},
							{id: 'autoexpand', name: 'MedService_Name',  type: 'string', header: langs('Наименование'), width: 150},
							{name: 'MedService_begDT',  type: 'date', header: langs('Дата создания'), width: 100},
							{name: 'MedService_endDT',  type: 'date', header: langs('Дата закрытия'), width: 100}
						],
					actions:
						[
							{
								name: 'action_add',
								handler: function() {
									var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();
									args = {}
									args.action = 'add';
									args.MedService_pid = node.attributes.object_value;
									var viewframe = this.findById('Apparatus');
									args.owner = viewframe;
									args.callback = function(){
										if (!viewframe.ViewActions.action_refresh.isDisabled())
										{
											viewframe.ViewActions.action_refresh.execute();
										}
										swLpuStructureViewForm.reloadCurrentTreeNode(this);
									}.createDelegate(this);
									getWnd('swApparatusEditWindow').show(args);
								}.createDelegate(this)
							},
							{name:'action_edit'},
							{name:'action_view'},
							{name:'action_delete'},
							{name:'action_refresh'},
							{name:'action_print'}
						],
					afterDeleteRecord: function()
					{
						swLpuStructureViewForm.reloadCurrentTreeNode(this);
					}
				});

			frms.swCoeffIndexTarifPanel = new Ext.Panel({
				items: [
					frms.swCoeffIndexTariffFiltersPanel,
					frms.swCoeffIndexTariffGrid
				],
				height: 60,
				border: false,
				layout: 'border',
				region: 'north'
			});

			var swLpuTariffTabs = new Ext.TabPanel(
				{
					autoScroll: true,
					plain: true,
					activeTab: 0,
					resizeTabs: true,
					region: 'center',
					enableTabScroll: true,
					minTabWidth: 120,
					//autoWidth: true,
					tabWidth: 'auto',
					defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
					layoutOnTabChange: true,
					//plugins: new Ext.ux.TabCloseMenu(),
					listeners:
					{
						tabchange: function(tab, panel)
						{
							var Lpu_id = swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id;
							// Загрузка соответсвующего грида
							if (panel.id == 'tab_tariffsmp') {
								swSmpTariffGrid.loadData({globalFilters:{Lpu_id: Lpu_id}, params:{Lpu_id: Lpu_id}});
							} else if(panel.id == 'tab_tariffdd') {
								swTariffDispGrid.loadData({globalFilters:{Lpu_id: Lpu_id}, params:{Lpu_id: Lpu_id}});
							} else if(panel.id == 'tab_tariffbud') {
								frms.loadMedicalCareBudgTypeTariffGrid();
							} else {
								swTariffLpuGrid.loadData({globalFilters:{Lpu_id: Lpu_id}, params:{Lpu_id: Lpu_id}});
							}
						}
					},
					items:[{
						title: langs('Тарифы СМП/НМП'),
						layout: 'fit',
						id: 'tab_tariffsmp',
						iconCls: 'lpu-tariff16',
						//header:false,
						border:false,
						items: [swSmpTariffGrid]
					},
						{
							title: langs('Тарифы ДД'),
							layout: 'fit',
							id: 'tab_tariffdd',
							iconCls: 'lpu-tariff16',
							border:false,
							items: [swTariffDispGrid]
						},
						{
							title: langs('Тарифы МО'),
							layout: 'fit',
							id: 'tab_tarifflpu',
							iconCls: 'lpu-tariff16',
							border:false,
							items: [swTariffLpuGrid]
						},
						{
							title: langs('Тарифы (бюджет)'),
							hidden: !getRegionNick().inlist(['astra', 'ufa', 'kareliya', 'krym', 'perm', 'pskov']),
							disabled: !getRegionNick().inlist(['astra', 'ufa', 'kareliya', 'krym', 'perm', 'pskov']),
							layout: 'border',
							id: 'tab_tariffbud',
							iconCls: 'lpu-tariff16',
							border:false,
							items: [
								frms.MedicalCareBudgTypeTariffFilterPanel,
								frms.MedicalCareBudgTypeTariffGrid
							]
						}
					]
				});


			var swStaffTTTabPanel = new Ext.TabPanel(
				{
					id: 'MedStaffFactTT-tabs-panel',
					autoScroll: true,
					plain: true,
					activeTab: 0,
					resizeTabs: true,
					region: 'center',
					enableTabScroll: true,
					minTabWidth: 120,
					//autoWidth: true,
					tabWidth: 'auto',
					defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
					layoutOnTabChange: true,
					//plugins: new Ext.ux.TabCloseMenu(),
					listeners:
					{
						tabchange: function(tab, panel)
						{
							// Загрузка соответсвующего грида
							var n = swLpuStructureFrame.getSelectionModel().getSelectedNode();
							if (n)
								LoadOnChangeTab(n);
						}
					},
					items:[
						{
							title: langs('Штатные расписания'),
							id: 'tab_LpuStaff',
							iconCls: 'info16',
							border:false,
							layout: 'fit',
							items: [swLpuStaffPanel]
						},{
							title: langs('Строки штатного расписания'),
							// layout: 'border',
							id: 'tab_MedStaffFactTT',
							iconCls: 'info16',
							//header:false,
							border:false,
							items: [swStaffTTFilterPanel,swStaffTTPanel]
						},{
							title: langs('Организационно-штатные мероприятия'),
							layout: 'fit',
							id: 'tab_Staff',
							iconCls: 'info16',
							//header:false,
							border:false,
							items: [swStaffOSMPanel]
						}]
				});
			var swSectionTariffTabPanel = new Ext.TabPanel(
				{
					id: 'LpuSectionTariff-tabs-panel',
					autoScroll: true,
					plain: true,
					activeTab: 0,
					resizeTabs: true,
					region: 'center',
					enableTabScroll: true,
					minTabWidth: 120,
					//autoWidth: true,
					tabWidth: 'auto',
					defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
					layoutOnTabChange: true,
					//plugins: new Ext.ux.TabCloseMenu(),
					listeners:
					{
						tabchange: function(tab, panel)
						{
							// Загрузка соответсвующего грида
							var n = swLpuStructureFrame.getSelectionModel().getSelectedNode();
							if (n)
								LoadOnChangeTab(n);
						}
					},
					items:[{
						title: langs('Тарифы отделения'),
						layout: 'fit',
						id: 'tab_LpuSectionTariff',
						iconCls: 'info16',
						//header:false,
						border:false,
						items: [swSectionTariffPanel]
					}, {
						title: langs('Коэффициенты индексации'),
						layout: 'fit',
						id: 'tab_CoeffIndexTariff',
						iconCls: 'info16',
						//header:false,
						border:false,
						items: [frms.swCoeffIndexTarifPanel]
					}]
				});

// Табы
			var swLpuStructureTabs = new Ext.TabPanel(
				{
					id: 'lpustructure-tabs-panel',
					autoScroll: true,
					plain: true,
					activeTab: 0,
					resizeTabs: true,
					region: 'center',
					enableTabScroll: true,
					minTabWidth: 120,
					//autoWidth: true,
					tabWidth: 'auto',
					defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
					layoutOnTabChange: true,
					//plugins: new Ext.ux.TabCloseMenu(),
					listeners:
					{
						tabchange: function(tab, panel)
						{
							/*	var els='';
							 var type = 0;
							 if (els=='')
							 {
							 els=panel.findByType('textfield', false);
							 type = 1;
							 }
							 if (els=='')
							 {
							 els=panel.findByType('combo', false);
							 type = 1;
							 }
							 if (els=='')
							 {
							 els=panel.findByType('grid', false);
							 type = 2;
							 }
							 if (els=='')
							 {
							 type = 0;
							 }
							 var el;
							 if (type!=0)
							 el=els[0];
							 if (el!='undefined' && el.focus && type==1)
							 {
							 el.focus(true, 100);
							 }
							 else if (el!='undefined' && el.focus && type==2)
							 {
							 if (el.getStore().getCount()>0)
							 {
							 el.getView().focusRow(0);
							 el.getSelectionModel().selectFirstRow();
							 }
							 }
							 */
							// Загрузка соответсвующего грида
							var n = swLpuStructureFrame.getSelectionModel().getSelectedNode();
							if (n)
								LoadOnChangeTab(n);
						}
					},
					items:[{
						title: langs('Описание'),
						layout: 'fit',
						id: 'tab_descr',
						iconCls: 'info16',
						//header:false,
						border:false,

						layout: 'column',
						items: [
							this.PhotoPanel
							, {
								//layout: 'fit',
								layout: 'form',
								columnWidth: 0.95,
								border:false,
								items: [swLpuDescription, swLpuFilialDescription, swLpuBuildingDescription, swLpuUnitDescription, swLpuSectionDescription]
							}]
					},

						{
							title: langs('Атрибуты'),
							id: 'tab_attributes',
							//iconCls: 'fit',
							layout: 'fit',
							border:false,
							items: [ swLpuAttributeSignValueGridPanel ]
						},
						{
							title: langs('Подразделения'),
							layout: 'fit',
							id: 'tab_building',
							iconCls: 'lpu-building16',
							border:false,
							items: [swLpuBuildingPanel]
						},
						{
							title: langs('Группа отделений'),
							layout: 'fit',
							id: 'tab_unit',
							iconCls: 'lpu-unittype16',
							border:false,
							items: [swLpuUnitPanel]
						},
						{
							title: langs('Отделения'),
							layout: 'fit',
							id: 'tab_section',
							iconCls: 'lpu-section16',
							border:false,

							items: [swLpuSectionPanel]
						},
						{
							title: langs('Сотрудники'),
							layout: 'border',
							id: 'tab_staff',
							iconCls: 'staff16',
							//header:false,
							border:false,
							items: [swStaffFilterPanel,swStaffPanel]
						},
						{
							title: langs('Штатные расписания'),
							layout: 'fit',
							id: 'tab_staff_tt',
							iconCls: 'staff16',
							//header:false,
							border:false,
							items: [swStaffTTTabPanel
								/*swStaffTTPanel,
								 swStaffOSMPanel*/
							]
						},
						{
							title: langs('Участки'),
							id: 'tab_region',
							iconCls: 'lpu-regiontype16',
							layout: 'fit',
							//header:false,
							border:false,
							items: [swLpuRegionPanel]
						},
						{
							title: langs('Обслуживаемые организации'),
							id: 'tab_lpuorgserved',
							layout: 'fit',
							border: false,
							items: [swLpuOrgServed]
						},
						{
							title: langs('Услуги'),
							id: 'tab_usluga',
							iconCls: 'lpu-usluga16',
							layout: 'fit',
							//header:false,
							border:false,
							items: [swUslugaComplexTreePanel]
						},
						{
							title: langs('Ресурсы'),
							id: 'tab_resource',
							iconCls: 'lpu-usluga16',
							layout: 'fit',
							//header:false,
							border:false,
							items: [swResourceTreePanel]
						},
						{
							title: langs('Участок'),
							id: 'tab_oneregion',
							iconCls: 'lpu-region16',
							layout: 'fit',
							//header:false,
							border:false,
							items: [swOneRegionPanel]
						},
						{
							title: langs('Тарифы '),
							id: 'tab_tariff',
							iconCls: 'lpu-tariff16',
							layout: 'fit',
							//header:false,
							border:false,
							items: [
								swSectionTariffTabPanel
							]
						},
						{
							// Тарифы на уровне МО
							title:		langs('Тарифы '),
							id:			'tab_lputariff',
							iconCls:	'lpu-tariff16',
							layout:		'fit',
							border:		false,
							//
							items: [swLpuTariffTabs],
							width: 300
						},
						{
							title: langs('Коечный фонд'),
							id: 'tab_bedstate',
							iconCls: 'stac16',
							layout: 'border',
							border:false,
							items: [
								swSectionBedStateForm,
								swSectionWardPanel,
								swSectionBedStatePanel
							]
						},
						/*{
						 title: langs('Палаты'),
						 id: 'tab_ward',
						 //iconCls: 'ward16',
						 layout: 'fit',
						 border:false,
						 items: [swWardMainPanel]
						 },*/
						{
							title: langs('Финансирование'),
							id: 'tab_finans',
							iconCls: 'lpu-finans16',
							layout: 'fit',
							border:false,
							items: [swSectionFinansPanel]
						},
						{
							title: langs('Смены койки'),
							id: 'tab_shift',
							iconCls: 'lpu-shift16',
							layout: 'fit',
							border:false,
							items: [swSectionShiftPanel]
						},
						{
							title: langs('Лицензии'),
							id: 'tab_licence',
							iconCls: 'lpu-licence16',
							layout: 'fit',
							border:false,
							items: [swSectionLicencePanel]
						},
						{
							title: langs('Тарифы ') + getMESAlias(),
							id: 'tab_tariffmes',
							iconCls: 'lpu-tariff16',
							layout: 'fit',
							border:false,
							items: [swSectionTariffMesPanel]
						},
						{
							title: langs('Планирование'),
							id: 'tab_plan',
							iconCls: 'lpu-shift16',
							layout: 'fit',
							border:false,
							items: [swSectionPlanPanel]
						},
						{
							title: langs('Планирование'),
							id: 'tab_quote',
							iconCls: 'lpu-shift16',
							layout: 'border',
							border:false,
							items: [ frms.swSectionQuoteFilters, frms.swSectionQuotePanel]
						},
						{
							title: langs('План диспансеризации / осмотров'),
							id: 'tab_dopdispplandd',
							iconCls: 'lpu-shift16',
							layout: 'border',
							border:false,
							items: [ frms.swDopDispPlanFilters, frms.swDopDispPlanPanel]
						},
						{
							title: langs('Группа отделений'),
							id: 'tab_lpusection_group',
							iconCls: 'info16',
							layout: 'fit',
							hidden: true,
							disabled: false,
							border:false,
							html: ''
							//items: [ new  sw.Promed.FormPanel({id: 'lpusection-group-panel',hidden: true}) ]
						},
						{
							title: langs('Службы'),
							id: 'tab_medservice',
							iconCls: 'medservice16',
							layout: 'fit',
							hidden: false,
							border:false,
							items: [swMedServicePanel]
						},
						{
							title: langs('Сотрудники на службе'),
							id: 'tab_medservicemedpersonal',
							iconCls: '',
							layout: 'fit',
							border:false,
							items: [swMedServiceMedPersonalPanel]
						},
						{
							title: langs('Профили консультирования'),
							id: 'tab_lpusectionprofilemedservice',
							iconCls: '',
							layout: 'fit',
							border:false,
							items: [swLpuSectionProfileMedServicePanel]
						},
						{
							title: langs('Склады'),
							id: 'tab_storage',
							iconCls: 'product16',
							layout: 'fit',
							hidden: false,
							border:false,
							items: [swStoragePanel]
						},
						{
							title: langs('Сотрудники'),
							id: 'tab_storagemedpersonal',
							iconCls: '',
							layout: 'fit',
							hidden: false,
							border:false,
							items: [swStorageMedPersonalPanel]
						},
						{
							title: langs('Анализаторы'),
							id: 'tab_analyzer',
							iconCls: '',
							layout: 'border',
							border:false,
							items: [
								frms.AnalyzerLeftPanel,
								frms.AnalyzerRightPanel
							]
						},
						{
							title: langs('Аппараты'),
							id: 'tab_apparatus',
							iconCls: '',
							layout: 'fit',
							border:false,
							items: [frms.swApparatusPanel]
						},
						{
							title: langs('Обслуживаемые ЛПУ'),
							id: 'tab_remotecons',
							iconCls: '',
							layout: 'fit',
							border:false,
							items: [swFDServicedByRemoteConsultPanel]
						},
						{
							title: langs('Обслуживающие отделения'),
							id: 'tab_forenmedcorps',
							iconCls: '',
							layout: 'fit',
							border:false,
							items: [swBSMEForenMedCorpMedServicesPanel]
						},
						{
							title: langs('Обслуживающие отделения'),
							id: 'tab_forenhist',
							iconCls: '',
							layout: 'fit',
							border:false,
							items: [swBSMEForenMedHistMedServicesPanel]
						},
						{
							title: langs('Территория'),
							id: 'tab_territory',
							iconCls: '',
							layout: 'fit',
							border:false,
							items: [/*territoryPanel, */territoryServicePanel]
						},
						{
							title: langs('Территория службы'),
							id: 'tab_territory_med_service',
							iconCls: '',
							layout: 'fit',
							border:false,
							items: [territoryMedServicePanel]
						},
						{
							title: langs('Разное'),
							id: 'tab_smpunitparams',
							iconCls: '',
							autoScroll: true,
							layout: 'fit',
							border:false,
							items: [swSmpUnitParamsPanel]
						},
						{
							title: langs('Разное'),
							id: 'tab_nmpparams',
							iconCls: '',
							autoScroll: true,
							layout: 'fit',
							border:false,
							items: [swNmpParamsPanel]
						},
						{
							title: 'Электронная очередь',
							id: 'tab_electronicqueue',
							iconCls: '',
							autoScroll: true,
							layout: 'fit',
							border:false,
							items: [swElectronicQueuePanel],
							listeners: {
								'activate': function () {

									var gridColumnModel = swElectronicQueuePanel.getGrid().getColumnModel();

									//скрываем колонку с "Наименование услуги", если у нас нет службы
									var columnToHideIndex = gridColumnModel.getIndexById('UslugaComplex_Name_col');
									if (columnToHideIndex) {

										var node = swLpuStructureFrame.getSelectionModel().getSelectedNode();

										if (node.attributes.object == 'MedService') {

											if (gridColumnModel.isHidden(columnToHideIndex))
												gridColumnModel.setHidden(columnToHideIndex, false);

										} else {

											if (!gridColumnModel.isHidden(columnToHideIndex))
												gridColumnModel.setHidden(columnToHideIndex, true);
										}
									}
								}
							}
						},

						/*#########################################################################*/
						{
							title: 'PACS',
							id: 'tab_pacs',
							iconCls: 'dicom',
							layout: 'fit',
							border:false,
							items: [ swPacsSettings ]
						}
						/*#########################################################################*/
					]
				});

			function TreeBeforeLoad(TreeLoader, node)
			{
				var panel = Ext.getCmp('lpu-structure-frame'),
					level = swLpuStructureViewForm.getNodeTrueLevel(node); // Определяет уровень с поправкой на наличие филиала в иерархии


				TreeLoader.baseParams.level = level;
				TreeLoader.baseParams.level_two = 'All';

				if (node.getDepth()==0)
				{
					TreeLoader.baseParams.object = 'Lpu';
				}
				else
				{
					TreeLoader.baseParams.object = node.attributes.object;
					TreeLoader.baseParams.object_id = node.attributes.object_value;
				}
				if (!panel.Lpu_id) {
					//запрещаем загрузку при инициализации
					return false;
					//TreeLoader.baseParams.Lpu_id = 0;
				} else {
					TreeLoader.baseParams.Lpu_id = panel.Lpu_id;
				}

				if (node.attributes.object=='LpuUnitType')
					TreeLoader.baseParams.LpuUnitType_id = node.attributes.LpuUnitType_id;
				else
					TreeLoader.baseParams.LpuUnitType_id = 0;

				if (node.attributes.object_key)
					TreeLoader.baseParams.object_key = node.attributes.object_key;

				TreeLoader.baseParams.SectionsOnly = '';
				TreeLoader.baseParams.deniedSectionsList = '';

				if (!isSuperAdmin() && !isLpuAdmin() && isRegAdmin()) {
					TreeLoader.baseParams.regionsOnly = true;
				}
				//node.getOwnerTree().fireEvent('click', node);
				return true;
			}

			function LoadOnChangeTab(node)
			{
				var win = Ext.getCmp('swLpuStructureViewForm'),
					level = swLpuStructureViewForm.getNodeTrueLevel(node),
					LpuSection_pid, Lpu_id, LpuBuilding_id, LpuUnitType_id, LpuUnit_id, LpuSection_id;



				if (node.id != 'root')
				{

					if (swLpuStructureTabs.getActiveTab().id == 'tab_attributes')
					{
						var tableName = 'dbo.' + node.attributes.object;
						swLpuAttributeSignValueGridPanel.doLoad({tableName: tableName, tablePKey: node.attributes.object_value});
					}


					if (swLpuStructureTabs.getActiveTab().id == 'tab_usluga')
					{
						if (node.attributes.object == 'MedService') {
							// для сервисов одна форма, для услуг МО другая.
							swUslugaComplexTreePanel.getLayout().setActiveItem(1);
						} else {
							swUslugaComplexTreePanel.getLayout().setActiveItem(0);
						}
						swUslugaComplexTreePanel.doLayout();
					}
					// Читаем список врачей службы
					if (swLpuStructureTabs.getActiveTab().id == 'tab_medservicemedpersonal' && node.attributes.object == 'MedService')
					{
						if( getRegionNick() != 'kz' && ['profosmotrvz','profosmotr'].indexOf(node.attributes.MedServiceType_SysNick) >= 0 ){
							swMedServiceMedPersonalPanel.getColumnModel().setHidden(7, false);
						}else{
							swMedServiceMedPersonalPanel.getColumnModel().setHidden(7, true);
						}
						swMedServiceMedPersonalPanel.loadData
						({
							params:{
								MedService_id: node.attributes.object_value,
								Lpu_id: swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id
							},
							globalFilters: {
								MedService_id: node.attributes.object_value,
								Lpu_id: swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id
							}
						});
					}
					// Читаем список Профили консультирования
					if (swLpuStructureTabs.getActiveTab().id == 'tab_lpusectionprofilemedservice' && node.attributes.object == 'MedService') {
						swLpuSectionProfileMedServicePanel.loadData({
							params:{ MedService_id: node.attributes.object_value },
							globalFilters: { MedService_id: node.attributes.object_value }
						});
					}
					if (swLpuStructureTabs.getActiveTab().id == 'tab_analyzer' && node.attributes.object == 'MedService')
					{
						// frms.AnalyzerTestGrid.addActions({name:'action_exportlis', id: 'id_action_exportlis', disabled: false, handler: function() {}, text: 'Экспорт в ЛИС', tooltip: 'Экспорт в ЛИС', iconCls: 'x-btn-text', icon: 'img/icons/save16.png'});
						frms.swAnalyzerPanel.loadData
						({
							params:{
								MedService_id: node.attributes.object_value,
								Lpu_id: swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id
							},
							globalFilters: {
								MedService_id: node.attributes.object_value,
								Lpu_id: swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id
							}
						});
					}
					if (swLpuStructureTabs.getActiveTab().id == 'tab_territory')
					{
						//alert(node.attributes.object_value);
						var params = {
							LpuBuilding_id: node.attributes.object_value
						}
						//territoryPanel.loadData({params:params, globalFilters: params});
						territoryServicePanel.loadData({params:params, globalFilters: params});
						//	swLpuStructureViewForm.reloadCurrentTreeNode(swLpuRegionPanel);
					}
					if (swLpuStructureTabs.getActiveTab().id == 'tab_territory_med_service')
					{
						//alert(node.attributes.object_value);
						var params = {
							MedService_id: node.attributes.object_value
						}
						territoryMedServicePanel.loadData({params:params, globalFilters: params});
						//	swLpuStructureViewForm.reloadCurrentTreeNode(swLpuRegionPanel);
					}
					if (swLpuStructureTabs.getActiveTab().id == 'tab_electronicqueue')
					{
						// сбросим параметры
						var params = {
							MedService_id: '',
							LpuBuilding_id: '',
							LpuSection_id: '',
							MedServiceType_SysNick: '',
						};

						if (node.attributes.object == 'LpuBuilding') {
							params.LpuBuilding_id = node.attributes.object_value
						}

						if (node.attributes.object == 'LpuSection') {
							params.LpuSection_id = node.attributes.object_value
						}

						if (node.attributes.object == 'MedService') {
							params.MedService_id = node.attributes.object_value;
							params.MedServiceType_SysNick = node.attributes.MedServiceType_SysNick;
						}

                        swElectronicQueuePanel.addActions({
                            name: 'action_genTalonCode',
                            text: 'Сгенерировать коды бронирования',
							disabled: true,
                            handler: function() {

                                var record = swElectronicQueuePanel.ViewGridPanel.getSelectionModel().getSelected();

                                if (record) {

                                    var loadMask = new Ext.LoadMask(frms.getEl(), {msg: 'Генерация кодов бронирования...'});
                                    loadMask.show();

                                    Ext.Ajax.request({
                                        url: '/?c=ElectronicQueue&m=generateTalonCodeForExistedRecords',
                                        params: {
                                            MedService_id: record.get('MedService_id'),
                                            UslugaComplexMedService_id: record.get('UslugaComplexMedService_id'),
                                            MedStaffFact_id: record.get('MedStaffFact_id'),
                                            Resource_id: record.get('Resource_id')
                                        },
                                        success: function(response) {
                                            loadMask.hide();
                                            swElectronicQueuePanel.getAction('action_genTalonCode').setDisabled(true);
                                        },
                                        failure: function(response) {
                                            loadMask.hide();
                                        	log('fail', response)
                                        }
                                    });
								}
                            }
                        });

						swElectronicQueuePanel.loadData({params:params, globalFilters: params});
					}
					if (swLpuStructureTabs.getActiveTab().id == 'tab_nmpparams')
					{
						swNmpParamsPanel.getForm().reset();
						swNmpParamsPanel.setWorkTimesDisabled(getGlobalOptions().nmp_edit_work_time != '1');
						swNmpParamsPanel.refreshWorkTimeAllowBlank();

						var params = {MedService_id: node.attributes.object_value};
						swNmpParamsPanel.getForm().load({
							params: params,
							success: function() {
								swNmpParamsPanel.refreshWorkTimeAllowBlank();
							}
						});
					}
					if ( IS_DEBUG == 1 && swLpuStructureTabs.getActiveTab().id == 'tab_apparatus' && node.attributes.object == 'MedService')
					{
						frms.swApparatusPanel.loadData
						({
							params:{
								MedService_pid: node.attributes.object_value,
								Lpu_id: swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id
							},
							globalFilters: {
								MedService_pid: node.attributes.object_value,
								Lpu_id: swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id
							}
						});
					}

					// Читаем список услуг службы
					if (swLpuStructureTabs.getActiveTab().id == 'tab_usluga' && node.attributes.object == 'MedService')
					{
						//alert('@');
						var params = {
							limit: 100
							,start: 0
							,MedService_id: node.attributes.object_value
							,UslugaComplexMedService_pid: null
						}
						frms.uslugaContentsGrid.MedService_id = node.attributes.object_value;
						frms.uslugaContentsGrid.removeAll();
						frms.uslugaNavigationString.reset();
						frms.uslugaContentsGrid.getGrid().setTitle(langs('Услуги на службе'));
						frms.uslugaContentsGrid.loadData({
							url: '/?c=UslugaComplex&m=loadUslugaComplexMedServiceGrid',
							params: params,
							globalFilters: params
						});
					} else {
						// иначе обнулляем MedService_id для грида.
						frms.uslugaContentsGrid.MedService_id = null;
					}
					if (swLpuStructureTabs.getActiveTab().id == 'tab_resource' && node.attributes.object == 'MedService')
					{
						//alert('@');
						var params = {
							limit: 100
							,start: 0
							,MedService_id: node.attributes.object_value
						}
						frms.resourceContentsGrid.MedService_id = node.attributes.object_value;
						frms.resourceContentsGrid.removeAll();
						frms.resourceContentsGrid.getGrid().setTitle(langs('Ресурсы на службе'));
						frms.resourceContentsGrid.loadData({
							url: '/?c=Resource&m=loadResourceMedServiceGrid',
							params: params,
							globalFilters: params
						});
					} else {
						// иначе обнулляем MedService_id для грида.
						frms.resourceContentsGrid.MedService_id = null;
					}
					if (node.attributes.MedServiceType_SysNick && node.attributes.MedServiceType_SysNick == 'remoteconsultcenter') {
						//swLpuStructureTabs.unhideTabStripItem('tab_remotecons');
						swLpuStructureTabs.hideTabStripItem('tab_remotecons');
						swFDServicedByRemoteConsultPanel.MedService_id = node.attributes.object_value
					} else {
						swLpuStructureTabs.hideTabStripItem('tab_remotecons');
					}

					if (node.attributes.MedServiceType_SysNick && (node.attributes.MedServiceType_SysNick == 'forenmedcorpsexpdprt' || node.attributes.MedServiceType_SysNick == 'forenareadprt') ) {
						swLpuStructureTabs.unhideTabStripItem('tab_forenmedcorps');
						swBSMEForenMedCorpMedServicesPanel.MedService_id = node.attributes.object_value;
					} else {
						swLpuStructureTabs.hideTabStripItem('tab_forenmedcorps');
					}

					if (node.attributes.MedServiceType_SysNick && (node.attributes.MedServiceType_SysNick == 'forenhistdprt' || node.attributes.MedServiceType_SysNick == 'medforendprt') ) {
						swLpuStructureTabs.unhideTabStripItem('tab_forenhist');
						swBSMEForenMedHistMedServicesPanel.MedService_id = node.attributes.object_value;
					} else {
						swLpuStructureTabs.hideTabStripItem('tab_forenhist');
					}

					// Вкладка территория теперь доступна для подразделений всех типов 115758
					if (node.attributes.object == "LpuBuilding") {
						if (node.attributes.LpuBuildingType_id.inlist([27, 28]))
						{
							swLpuStructureTabs.unhideTabStripItem('tab_smpunitparams');
						}

						swLpuStructureTabs.unhideTabStripItem('tab_territory');

					} else {
						swLpuStructureTabs.hideTabStripItem('tab_smpunitparams');
						swLpuStructureTabs.hideTabStripItem('tab_territory');
					}

					if (node.attributes.object == "MedService" && node.attributes.MedServiceType_SysNick == "slneotl") {
						swLpuStructureTabs.unhideTabStripItem('tab_territory_med_service');
						swLpuStructureTabs.unhideTabStripItem('tab_nmpparams');
					} else {
						swLpuStructureTabs.hideTabStripItem('tab_territory_med_service');
						swLpuStructureTabs.hideTabStripItem('tab_nmpparams');
					}

					//log(node.attributes);
					//log(node.attributes.object);

					if ((node.attributes.object == "MedService"
						|| node.attributes.object == "LpuBuilding"
						|| node.attributes.object == "LpuSection")
						&& !Ext.isEmpty(node.attributes.ElectronicQueueInfo_id))
					{
						swLpuStructureTabs.unhideTabStripItem('tab_electronicqueue');
					} else {
						swLpuStructureTabs.hideTabStripItem('tab_electronicqueue');
					}

					//Читаем список складов
					if (swLpuStructureTabs.getActiveTab().id == 'tab_storage')
					{
						var params = {
							Lpu_id: null,
							LpuBuilding_id: null,
							LpuUnit_id: null,
							LpuSection_id: null,
							LpuUnitType_id: null,
							MedService_id: null,
							Storage_pid: null
						};
						var lookNode = node;
						var attr;
						while(lookNode) {
							attr = lookNode.attributes;
							if (attr.object == 'LpuUnitType') {
								params.LpuUnitType_id = attr.LpuUnitType_id;
							}
							if (attr.object == 'Storage' && Ext.isEmpty(params.Storage_pid)) {
								params.Storage_pid = attr.object_value;
							}
							if (!params[attr.object_id]) {
								params[attr.object_id] = attr.object_value;
							}
							lookNode = lookNode.parentNode;
						}
						frms.swStoragePanel.removeAll();
						frms.swStoragePanel.loadData({
							url: '/?c=Storage&m=loadStorageGrid',
							params: params,
							globalFilters: params
						});
					}

					if (swLpuStructureTabs.getActiveTab().id == 'tab_storagemedpersonal' && node.attributes.object == 'Storage')
					{
						swStorageMedPersonalPanel.loadData
						({
							params:{
								Storage_id: node.attributes.object_value,
								Lpu_id: swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id
							},
							globalFilters: {
								Storage_id: node.attributes.object_value,
								Lpu_id: swLpuStructureViewForm.findById('lpu-structure-frame').Lpu_id
							}
						});
					}



					if (swLpuStructureTabs.getActiveTab().id == 'tab_forenmedcorps') {
//			инициализация
						swBSMEForenMedCorpMedServicesPanel.getForm().reset();
						swBSMEForenMedCorpMedServicesPanel.loadComboStores();

						Ext.Ajax.request({
							callback: function(options, success, response) {
								if ( success ) {
									var resp = JSON.parse(response.responseText);
									resp = resp;
									if (!resp || !resp['success']) {
										if (resp['Error_Msg']) {
											sw.swMsg.alert(langs('Ошибка'), langs('При загрузке данных формы службы произошла ошибка')+resp['Error_Msg']);
										} else {
											sw.swMsg.alert(langs('Ошибка'), langs('При загрузке данных формы службы произошла ошибка'));
										}
									} else {
										swBSMEForenMedCorpMedServicesPanel.getForm().setValues(resp);
									}
								}
								else {
									sw.swMsg.alert(langs('Ошибка'), langs('При загрузке данных формы произошла ошибка'));
								}
							},
							params: {
								MedService_id : swBSMEForenMedCorpMedServicesPanel.MedService_id
							},
							url: '/?c=LpuStructure&m=loadForenCorpServingMedServices'
						});

					}

					if (swLpuStructureTabs.getActiveTab().id == 'tab_forenhist') {
//			инициализация
						swBSMEForenMedHistMedServicesPanel.getForm().reset();
						swBSMEForenMedHistMedServicesPanel.loadComboStores();

						Ext.Ajax.request({
							callback: function(options, success, response) {
								if ( success ) {
									var resp = JSON.parse(response.responseText);
									resp = resp;
									if (!resp || !resp['success']) {
										if (resp['Error_Msg']) {
											sw.swMsg.alert(langs('Ошибка'), langs('При загрузке данных формы службы произошла ошибка')+resp['Error_Msg']);
										} else {
											sw.swMsg.alert(langs('Ошибка'), langs('При загрузке данных формы службы произошла ошибка'));
										}
									} else {
										swBSMEForenMedHistMedServicesPanel.getForm().setValues(resp);
									}
								}
								else {
									sw.swMsg.alert(langs('Ошибка'), langs('При загрузке данных формы произошла ошибка'));
								}
							},
							params: {
								MedService_id : swBSMEForenMedHistMedServicesPanel.MedService_id
							},
							url: '/?c=LpuStructure&m=loadForenHistServingMedServices'
						});

					}

					var Lpu_Name = swLpuStructureViewForm.getNodeParentAttribute(node, 'Lpu', 'text');
					Lpu_id = swLpuStructureViewForm.getNodeParentAttribute(node, 'Lpu', 'object_value');


					switch (level)
					{
						case 1:
							/**
							 * На первом уровне кроме объекта Lpu может быть LpuFilial (филиал)
							 */
							if (node.attributes.object === 'LpuFilial')
							{
								var LpuFilial_id = node.attributes.object_value ;


								// Загружаем описание Филиала
								if (swLpuStructureTabs.getActiveTab().id == 'tab_descr') {

									swLpuFilialDescription.load({
										params: {LpuFilial_id: LpuFilial_id}
									});

									// Скрываем фотографию в описании филиала.
									swLpuStructureTabs.findById('tab_descr').findById('upload_panel').hide();

								}

								// Загружаем подразделения филиала
								else // Здесь active tab всегда будет tab_building, потому что он первый не скрытый. По ТЗ 115758 по умолчанию надо показывать подразделения
								{
									// Добавляем к запросу параметр. Если кидать его сразу в loadData, при повторной загрузке параметры меняться не будут, а этот объект используется еще и для МО, где этот параметр не нужен
									swLpuBuildingPanel.setParam('LpuFilial_id', LpuFilial_id);

									swLpuBuildingPanel.loadData({params:{ Lpu_Name: Lpu_Name, Lpu_id: Lpu_id }, globalFilters: { Lpu_id: Lpu_id } });
									swLpuBuildingPanel.addActions({
										id: 'swLpuBuildingPanel_action_new',
										name:'action_new',
										text:langs('Действия'),
										disabled: win.action=='view',
										menu: [{
											text: langs('Объединение'),
											tooltip: langs('Добавить запись к объединению'),
											handler: function() {
												AddRecordToUnion(
													swLpuBuildingPanel.ViewGridPanel.getSelectionModel().getSelected(),
													'LpuBuilding',
													langs('Подразделения'),
													function () {
														swLpuBuildingPanel.loadData();
														swLpuStructureViewForm.reloadCurrentTreeNode(swLpuBuildingPanel);
													}
												)
											},
											iconCls: 'union16'
										}]
									});
								}


								break;
							}


							/**
							 * Далее часть объекта Lpu
							 */

							if ( node.attributes.object == 'Lpu' ) {
								//Lpu_id = node.attributes.object_value;
							}
							// Читаем описание МО
							if (swLpuStructureTabs.getActiveTab().id == 'tab_descr' && Lpu_id) {
								swLpuDescription.load({
									params: {Lpu_id: Lpu_id},
									success: function(frm, action){
										win.overwriteTpl({file_url: (action.result && action.result.data && action.result.data.photo)?action.result.data.photo:''});
									}
								});

								// Отображаем фотопанель, если в описании филиала она закрылась
								swLpuStructureTabs.findById('tab_descr').findById('upload_panel').show();
							}

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff')
								swLpuStructureViewForm.addStaffActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick().inlist(['ekb','astra']))
								swLpuStructureViewForm.addStaffProfileActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick() != 'kareliya')
								swLpuStructureViewForm.addShowScheduleEditActions();

							/*
							 if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt'){
							 swLpuStructureViewForm.addStaffTTActions();
							 }
							 */
							// Читаем список сотрудников МО
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff') {
								swStaffFilterPanel.getForm().reset();
								swStaffFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{Lpu_id: Lpu_id}});
								swStaffFilterPanel.getForm().findField('MedStaffFact_date_range').setMaxValue(getGlobalOptions().date);
								swStaffFilterPanel.getForm().findField('MedStaffFact_disDate_range').setMaxValue(getGlobalOptions().date);
								swStaffPanel.loadData({params:{Lpu_Name: Lpu_Name, Lpu_id: Lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}});
							}

							// Читаем список штатки
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt') {
								if (swStaffTTTabPanel.getActiveTab().id == 'tab_MedStaffFactTT') {
									swLpuStructureViewForm.addStaffTTActions();
									swStaffTTFilterPanel.getForm().reset();
									swStaffTTFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{Lpu_id: Lpu_id}});
									swStaffTTFilterPanel.getForm().findField('Staff_Date_range').setMaxValue(getGlobalOptions().date);
									swStaffTTFilterPanel.getForm().findField('Staff_endDate_range').setMaxValue(getGlobalOptions().date);
									swStaffTTPanel.loadData({params:{Lpu_Name: Lpu_Name, Lpu_id: Lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}});
								} else if( swStaffTTTabPanel.getActiveTab().id == 'tab_Staff') {
									swStaffOSMPanel.loadData({params:{Lpu_Name: Lpu_Name, Lpu_id: Lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: null, LpuUnit_id: null, LpuSection_id: null}});
								} else if(swStaffTTTabPanel.getActiveTab().id == 'tab_LpuStaff'){
									swLpuStaffPanel.loadData({params:{Lpu_id: Lpu_id}});
								}
							}

							// Читаем подразделения
							if (swLpuStructureTabs.getActiveTab().id == 'tab_building') {

								// Обнуляем, на случай, если в объекте сохранен этот параметр запроса
								swLpuBuildingPanel.setParam('LpuFilial_id', '');

								swLpuBuildingPanel.loadData({params:{ Lpu_Name: Lpu_Name, Lpu_id: Lpu_id }, globalFilters: { Lpu_id: Lpu_id} });
								swLpuBuildingPanel.addActions({
									id: 'swLpuBuildingPanel_action_new',
									name:'action_new',
									text:langs('Действия'),
									disabled: win.action=='view',
									menu: [{
										text: langs('Объединение'),
										tooltip: langs('Добавить запись к объединению'),
										handler: function() {
											AddRecordToUnion(
												swLpuBuildingPanel.ViewGridPanel.getSelectionModel().getSelected(),
												'LpuBuilding',
												langs('Подразделения'),
												function () {
													swLpuBuildingPanel.loadData();
													swLpuStructureViewForm.reloadCurrentTreeNode(swLpuBuildingPanel);
												}
											)
										},
										iconCls: 'union16'
									}]
								});

							}

							// Читаем услуги МО
							if (swLpuStructureTabs.getActiveTab().id == 'tab_usluga' && node.attributes.object == 'Lpu')
							{
								var params = {
									limit: 100
									,start: 0
									,Lpu_id: Lpu_id
									,LpuBuilding_id: null
									,LpuUnit_id: null
									,LpuSection_id: null
								}
								frms.uslugaComplexOnPlaceGrid.removeAll();
								frms.uslugaComplexOnPlaceGrid.getGrid().getStore().load({
									params: params
								});
							}

							// Читаем список участков на МО
							if (swLpuStructureTabs.getActiveTab().id == 'tab_region')
							{
								swLpuRegionPanel.loadData({params:{Lpu_Name: Lpu_Name, Lpu_id: Lpu_id, LpuRegionType_id: null}, globalFilters: {Lpu_id: Lpu_id, LpuRegionType_id: null}});
								swLpuRegionPanel.addActions({
									id: 'swLpuRegionPanel_action_new',
									name:'action_new',
									text:langs('Действия'),
									disabled: win.action=='view',
									menu: [{
										text: langs('Объединение'),
										tooltip: langs('Добавить запись к объединению'),
										handler: function() {
											AddRecordToUnion(
												swLpuRegionPanel.ViewGridPanel.getSelectionModel().getSelected(),
												'LpuRegion',
												langs('Участки'),
												function () {
													swLpuRegionPanel.loadData();
													swLpuStructureViewForm.reloadCurrentTreeNode(swLpuRegionPanel);
												}
											)
										},
										iconCls: 'union16'
									}, {
										text: langs('Получить данные участков с портала РПН'),
										hidden: (getRegionNick() != 'kz'),
										handler: function() {
											sw.Promed.serviceKZRPN.getLpuRegionList(this, {callback: function() {
												swLpuRegionPanel.loadData();
												swLpuStructureViewForm.reloadCurrentTreeNode(swLpuRegionPanel);
											}});
										}.createDelegate(frms)
									}, {
										text: langs('Получить данные пациентов с портала РПН'),
										hidden: (getRegionNick() != 'kz'),
										handler: function() {
											getWnd('swImportPersonRpnWindow').show();
										}.createDelegate(frms)
									},  '-', {
										text: langs('Отчет по зонам обслуживания участков'),
										tooltip: langs('Отобразить отчет по зонам обслуживания учатсков'),
										handler: function() {
											var id_salt = Math.random();
											var win_id = 'report' + Math.floor(id_salt*10000);
											// собственно открываем окно и пишем в него
											var win = window.open('/?c=LpuRegionStreetsReport&m=ViewLpuRegionStreetsReport', win_id);
										}
									}]
								});
							}
							// Читаем обслуживаемые организации
							if (swLpuStructureTabs.getActiveTab().id == 'tab_lpuorgserved')
							{
								swLpuOrgServed.getGrid().getStore().baseParams.Lpu_id = Lpu_id;// = params2;
								swLpuOrgServed.loadData({params:{Lpu_id: Lpu_id}});
							}
							// Читаем планирование для МО
							if (swLpuStructureTabs.getActiveTab().id == 'tab_quote') {
								var base_form = frms.swSectionQuoteFilters.getForm();
								frms.swSectionQuotePanel.getGrid().getStore().baseParams.LpuSectionQuote_Year = base_form.findField('LpuSectionQuote_Year').getValue();
								frms.swSectionQuotePanel.getGrid().getStore().baseParams.PayType_id = base_form.findField('PayType_id').getValue();
								frms.swSectionQuotePanel.getGrid().getStore().baseParams.LpuUnitType_id = base_form.findField('LpuUnitType_id').getValue();
								frms.swSectionQuotePanel.getGrid().getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
								frms.swSectionQuotePanel.loadData({params:{Lpu_Name:Lpu_Name, Lpu_id: Lpu_id}, globalFilters: {Lpu_id: Lpu_id, LpuUnit_id:null, LpuSection_id:null}});
							}

							if ( getRegionNick() != 'by' ) {
								// Читаем план диспансеризации
								if (swLpuStructureTabs.getActiveTab().id == 'tab_dopdispplandd') {
									frms.swDopDispPlanPanelFilter(Lpu_id);
								}
							}

							// Читаем список служб
							if (swLpuStructureTabs.getActiveTab().id == 'tab_medservice' && Lpu_id > 0)
							{
								swMedServicePanel.loadData({params:{Lpu_id: Lpu_id}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id:null, LpuUnitType_id: null, LpuUnit_id:null, LpuSection_id:null, is_All: swMedServicePanel.defaultFilterValues.is_All, is_Act: swMedServicePanel.defaultFilterValues.is_Act}});
								swLpuStructureViewForm.addMedServicePanelActions();
							}
							if ( getRegionNick() != 'by' ) {
								// Загружаем список тарифов МО
								if ( swLpuStructureTabs.getActiveTab().id == 'tab_lputariff' ) {
									// смотрим какая вкладка открыта и грузим либо тарифы ДД либо тарифы СМП TODO
									if (swLpuTariffTabs.getActiveTab().id == 'tab_tariffsmp') {
										swSmpTariffGrid.loadData({globalFilters:{Lpu_id: Lpu_id}, params:{Lpu_id: Lpu_id}});
									} else if (swLpuTariffTabs.getActiveTab().id == 'tab_tariffdd') {
										swTariffDispGrid.loadData({globalFilters:{Lpu_id: Lpu_id}, params:{Lpu_id: Lpu_id}});
									} else if (swLpuTariffTabs.getActiveTab().id == 'tab_tariffbud') {
										frms.loadMedicalCareBudgTypeTariffGrid();
									} else {
										swTariffLpuGrid.loadData({globalFilters:{Lpu_id: Lpu_id}, params:{Lpu_id: Lpu_id}});
									}
								}
							}

							break;
						case 2:
							if (swLpuStructureTabs.getActiveTab().id == 'tab_remotecons') {
								swFDServicedByRemoteConsultPanelEditLine.setDisabled(true);
								swFDServicedByRemoteConsultGrid.setDisabled(false);
								swFDServicedByRemoteConsultGrid.getGrid().getStore().load({params:{MedService_id:node.attributes.object_value}});
								swFDServicedByRemoteConsultPanel.MedService_id = node.attributes.object_value;
								//loadData({params:{MedService_id:node.attributes.object_value}});
							}

							if ( node.attributes.object == 'LpuBuilding' ) {
								// Lpu_id = Lpu_id;
								LpuBuilding_id = node.attributes.object_value;
							}

							if (node.attributes.object =='LpuRegionTitle') {
								// Lpu_id = Lpu_id;
							}

							// Читаем услуги подразделения
							if (swLpuStructureTabs.getActiveTab().id == 'tab_usluga' && node.attributes.object == 'LpuBuilding')
							{
								var params = {
									limit: 100
									,start: 0
									,Lpu_id: Lpu_id
									,LpuBuilding_id: LpuBuilding_id
									,LpuUnit_id: null
									,LpuSection_id: null
								}
								frms.uslugaComplexOnPlaceGrid.removeAll();
								frms.uslugaComplexOnPlaceGrid.getGrid().getStore().load({
									params: params
								});
							}

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff')
								swLpuStructureViewForm.addStaffActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick().inlist(['ekb','astra']))
								swLpuStructureViewForm.addStaffProfileActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick() != 'kareliya')
								swLpuStructureViewForm.addShowScheduleEditActions();

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt')
								swLpuStructureViewForm.addStaffTTActions();

							// Читаем описание подразделения
							if (swLpuStructureTabs.getActiveTab().id == 'tab_descr')
								swLpuBuildingDescription.load({params:{LpuBuilding_id: LpuBuilding_id},
									success: function(frm, action){
										win.overwriteTpl({file_url: (action.result && action.result.data && action.result.data.photo)?action.result.data.photo:''});
									}
								});

							// Читаем описание подразделения
							if (swLpuStructureTabs.getActiveTab().id == 'tab_smpunitparams'){
								var SmpUnitParamsPanel = Ext.getCmp('swLpuStructureViewForm_swSmpUnitParamsPanel'),
									SmpUnitParamsPanelForm = SmpUnitParamsPanel.getForm();

								var SmpUnitTypeIdStore = SmpUnitParamsPanelForm.findField('SmpUnitType_id').getStore();
								if (SmpUnitTypeIdStore.getCount() == 0){
									SmpUnitTypeIdStore.load({params: {LpuBuilding_id: LpuBuilding_id}});
								}

								var LpuBuildingPidStore = SmpUnitParamsPanelForm.findField('LpuBuilding_pid').getStore();
								if (LpuBuildingPidStore.getCount() == 0){
									LpuBuildingPidStore.load({params: {LpuBuilding_id: LpuBuilding_id}});
								}

								if (!SmpUnitParamsPanel.loadMask) {
									SmpUnitParamsPanel.loadMask = new Ext.LoadMask(SmpUnitParamsPanel.getEl(), {msg: LOAD_WAIT});
								}
								SmpUnitParamsPanel.loadMask.show();
								SmpUnitParamsPanel.load({
									params: {LpuBuilding_id: LpuBuilding_id},
									success: function(form, action){
										SmpUnitParamsPanel.loadMask.hide();
										form.checkEnableLpuBuildingPid(form.findField('SmpUnitType_id').getValue());
									}
								});
								/*
								 var SmpUnitTimesPanel = Ext.getCmp('swLpuStructureViewForm_swSmpUnitTimesPanel');
								 SmpUnitTimesPanel.load({
								 params: {LpuBuilding_id: LpuBuilding_id}
								 });*/

								if (node.attributes.object == 'LpuBuilding') {
									var paramsActRules = {
										LpuBuilding_id: node.attributes.object_value
									};
									activeCallRulePanel.loadData({params:paramsActRules, globalFilters: paramsActRules});
								}
							}

							// Читаем список сотрудников МО
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff') {
								swStaffFilterPanel.getForm().reset();
								swStaffFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{LpuBuilding_id: LpuBuilding_id}});
								swStaffFilterPanel.getForm().findField('MedStaffFact_date_range').setMaxValue(getGlobalOptions().date);
								swStaffFilterPanel.getForm().findField('MedStaffFact_disDate_range').setMaxValue(getGlobalOptions().date);
								swStaffPanel.loadData({params:{Lpu_Name: Lpu_Name, Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:null, LpuSection_id:null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:null, LpuSection_id:null}});
							}

							// Читаем список штатки
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt') {
								if (swStaffTTTabPanel.getActiveTab().id == 'tab_MedStaffFactTT') {
									swStaffTTFilterPanel.getForm().reset();
									swStaffTTFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{LpuBuilding_id: LpuBuilding_id}});
									swStaffTTFilterPanel.getForm().findField('Staff_Date_range').setMaxValue(getGlobalOptions().date);
									swStaffTTFilterPanel.getForm().findField('Staff_endDate_range').setMaxValue(getGlobalOptions().date);
									swStaffTTPanel.loadData({params:{Lpu_Name: Lpu_Name, Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:null, LpuSection_id:null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:null, LpuSection_id:null}});
								}else{
									swStaffOSMPanel.loadData({params:{Lpu_Name: Lpu_Name, Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:null, LpuSection_id:null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:null, LpuSection_id:null}});
								}
							}

							// Читаем группу отделений
							if (swLpuStructureTabs.getActiveTab().id == 'tab_unit')
							{
								swLpuUnitPanel.loadData({
									params: {
										LpuUnitType_id: null,
										LpuBuilding_id: LpuBuilding_id,
										Lpu_id: Lpu_id,
										RegisterMO_OID: node.attributes.RegisterMO_OID
									},
									globalFilters: {
										LpuUnitType_id: null,
										Lpu_id: Lpu_id,
										LpuBuilding_id: LpuBuilding_id
									}
								});
								swLpuUnitPanel.addActions({
									id: 'swLpuUnitPanel_action_new',
									name:'action_new',
									text:langs('Действия'),
									disabled: win.action=='view',
									menu: [{
										text: langs('Объединение'),
										tooltip: langs('Добавить запись к объединению'),
										handler: function() {
											AddRecordToUnion(
												swLpuUnitPanel.ViewGridPanel.getSelectionModel().getSelected(),
												'LpuUnit',
												langs('Группа отделений'),
												function ()
												{
													swLpuUnitPanel.loadData();
													swLpuStructureViewForm.reloadCurrentTreeNode(swLpuUnitPanel);
												}
											)
										},
										iconCls: 'union16'
									}]
								});
							}
							// Читаем список участков на МО
							if (swLpuStructureTabs.getActiveTab().id == 'tab_region')
							{
								swLpuRegionPanel.loadData({params:{Lpu_Name: Lpu_Name, Lpu_id: Lpu_id, LpuRegionType_id: null}, globalFilters: {Lpu_id: Lpu_id, LpuRegionType_id: null}});
								swLpuRegionPanel.addActions({
									id: 'swLpuRegionPanel_action_new',
									name:'action_new',
									text:langs('Действия'),
									disabled: win.action=='view',
									menu: [{
										text: langs('Объединение'),
										tooltip: langs('Добавить запись к объединению'),
										handler: function() {
											AddRecordToUnion(
												swLpuRegionPanel.ViewGridPanel.getSelectionModel().getSelected(),
												'LpuRegion',
												langs('Участки'),
												function () {
													swLpuRegionPanel.loadData();
													swLpuStructureViewForm.reloadCurrentTreeNode(swLpuRegionPanel);
												}
											)
										},
										iconCls: 'union16'
									}]
								});
							}

							// Читаем список служб
							if (swLpuStructureTabs.getActiveTab().id == 'tab_medservice' && LpuBuilding_id > 0)
							{
								swMedServicePanel.loadData({params:{Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnitType_id: null, LpuUnit_id: LpuUnit_id, LpuSection_id: LpuSection_id, is_All: null, is_Act: swMedServicePanel.defaultFilterValues.is_Act}});
								swLpuStructureViewForm.addMedServicePanelActions();
							}

							/*####*/						// Читаем настройки пакас
							if (swLpuStructureTabs.getActiveTab().id == 'tab_pacs')
							{
								swPacsSettings.loadData({params:{Lpu_id: Lpu_id}});
							}
							break;
						case 3:
							if ( node.attributes.object == 'LpuUnitType' ) {
								// Lpu_id = node.parentNode.parentNode.attributes.object_value; Lpu_id уже определен перед началом конструкции switch
								LpuBuilding_id = node.parentNode.attributes.object_value;
								LpuUnitType_id = node.attributes.LpuUnitType_id;
							}

							if (node.attributes.object =='LpuRegionType') {
								// Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value; Lpu_id уже определен перед началом конструкции switch
							}

							// Читаем типы групп отделений
							if (swLpuStructureTabs.getActiveTab().id == 'tab_unit')
							{
								swLpuUnitPanel.loadData({
									params: {
										LpuUnitType_id: LpuUnitType_id,
										LpuBuilding_id: LpuBuilding_id,
										Lpu_id: Lpu_id,
										RegisterMO_OID: node.parentNode.attributes.RegisterMO_OID
									},
									globalFilters: {
										LpuUnitType_id: LpuUnitType_id,
										Lpu_id: Lpu_id,
										LpuBuilding_id: LpuBuilding_id
									}
								});
								swLpuUnitPanel.addActions({
									id: 'swLpuUnitPanel_action_new',
									name:'action_new',
									text:langs('Действия'),
									disabled: win.action=='view',
									menu: [{
										text: langs('Объединение'),
										tooltip: langs('Добавить запись к объединению'),
										handler: function() {
											AddRecordToUnion(
												swLpuUnitPanel.ViewGridPanel.getSelectionModel().getSelected(),
												'LpuUnit',
												langs('Группа отделений'),
												function ()
												{
													swLpuUnitPanel.loadData();
													swLpuStructureViewForm.reloadCurrentTreeNode(swLpuUnitPanel);
												}
											)
										},
										iconCls: 'union16'
									}]
								});
							}

							// Читаем список участков на каждом из типов
							if (swLpuStructureTabs.getActiveTab().id == 'tab_region')
							{
								swLpuRegionPanel.loadData({params:{Lpu_Name: Lpu_Name, LpuRegionType_id: node.attributes.object_value, Lpu_id: Lpu_id}, globalFilters: {LpuRegionType_id: node.attributes.object_value, Lpu_id: Lpu_id} /*[{name: 'LpuUnit_id', value: node.attributes.object_value}]*/});
								swLpuRegionPanel.addActions({
									id: 'swLpuRegionPanel_action_new',
									name:'action_new',
									text:langs('Действия'),
									disabled: win.action=='view',
									menu: [{
										text: langs('Объединение'),
										tooltip: langs('Добавить запись к объединению'),
										handler: function() {
											AddRecordToUnion(
												swLpuRegionPanel.ViewGridPanel.getSelectionModel().getSelected(),
												'LpuRegion',
												langs('Участки'),
												function () {
													swLpuRegionPanel.loadData();
													swLpuStructureViewForm.reloadCurrentTreeNode(swLpuRegionPanel);
												}
											)
										},
										iconCls: 'union16'
									}]
								});
							}
							// Читаем список служб
							if (swLpuStructureTabs.getActiveTab().id == 'tab_medservice' && LpuUnitType_id > 0)
							{
								swMedServicePanel.loadData({params:{LpuUnitType_id: LpuUnitType_id, LpuBuilding_id: LpuBuilding_id, Lpu_id: Lpu_id}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnitType_id: LpuUnitType_id, LpuUnit_id: LpuUnit_id, LpuSection_id: LpuSection_id, is_All: null, is_Act: swMedServicePanel.defaultFilterValues.is_Act}});
								swLpuStructureViewForm.addMedServicePanelActions();
							}
							break;
						case 4:
							if ( node.attributes.object == 'LpuUnit' ) {
								// Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value; Lpu_id уже определен перед началом конструкции switch
								LpuBuilding_id = node.parentNode.parentNode.attributes.object_value;
								LpuUnitType_id = node.parentNode.attributes.LpuUnitType_id;
								LpuUnit_id = node.attributes.object_value;
							}

							// Читаем услуги группы отделений
							if (swLpuStructureTabs.getActiveTab().id == 'tab_usluga' && node.attributes.object == 'LpuUnit')
							{
								var params = {
									limit: 100
									,start: 0
									,Lpu_id: Lpu_id
									,LpuBuilding_id: LpuBuilding_id
									,LpuUnit_id: LpuUnit_id
									,LpuSection_id: null
								}
								frms.uslugaComplexOnPlaceGrid.removeAll();
								frms.uslugaComplexOnPlaceGrid.getGrid().getStore().load({
									params: params
								});
							}

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff')
								swLpuStructureViewForm.addStaffActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick().inlist(['ekb','astra']))
								swLpuStructureViewForm.addStaffProfileActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick() != 'kareliya')
								swLpuStructureViewForm.addShowScheduleEditActions();

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt'){
								swLpuStructureViewForm.addStaffTTActions();
							}

							if ((node.attributes.object =='LpuRegion') && (swLpuStructureTabs.getActiveTab().id == 'tab_oneregion'))
							{
								// Устанавливаем тип участка из структуры МО
								swLpuStructureTabs.findById('lpustrLpuRegionType_id').setValue(node.attributes.LpuRegionType_id);
								// Читаем список улиц на участке
								swOneRegionGridPanel.loadData({params:{LpuRegion_id: node.attributes.object_value, LpuRegion_Name: node.attributes.text}, globalFilters: {LpuRegion_id:node.attributes.object_value}});
							}
							// Читаем описание группы отделений
							if (swLpuStructureTabs.getActiveTab().id == 'tab_descr' && node.attributes.object == 'LpuUnit') {
								swLpuUnitDescription.load({params:{LpuUnit_id: node.attributes.object_value},
									success: function(frm, action){
										win.overwriteTpl({file_url: (action.result && action.result.data && action.result.data.photo)?action.result.data.photo:''});
									}
								});
							}
							// Читаем список сотрудников МО
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff')
							{
								//var Lpu_id = node.parentNode.parentNode.attributes.object_value;
								swStaffFilterPanel.getForm().reset();
								swStaffFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{LpuUnit_id: node.attributes.object_value}});
								swStaffFilterPanel.getForm().findField('MedStaffFact_date_range').setMaxValue(getGlobalOptions().date);
								swStaffFilterPanel.getForm().findField('MedStaffFact_disDate_range').setMaxValue(getGlobalOptions().date);
								swStaffPanel.loadData({params:{LpuUnit_Name: node.attributes.text, Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:node.attributes.object_value, LpuSection_id:null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:node.attributes.object_value, LpuSection_id:null}});
							}
							// Читаем список штатки
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt')
							{
								//var Lpu_id = node.parentNode.parentNode.attributes.object_value;
								var panelID = swStaffTTTabPanel.getActiveTab().id;
								var objectLoad = swStaffOSMPanel;
								if (panelID == 'tab_MedStaffFactTT') {
									swStaffTTFilterPanel.getForm().reset();
									swStaffTTFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{LpuUnit_id:node.attributes.object_value}});
									swStaffTTFilterPanel.getForm().findField('Staff_Date_range').setMaxValue(getGlobalOptions().date);
									swStaffTTFilterPanel.getForm().findField('Staff_endDate_range').setMaxValue(getGlobalOptions().date);
									objectLoad = swStaffTTPanel;
									//swStaffTTPanel.loadData({params:{LpuUnit_Name: node.attributes.text, Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:node.attributes.object_value, LpuSection_id:null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:node.attributes.object_value, LpuSection_id:null}});
								}
								if(panelID == 'tab_LpuStaff') objectLoad = swLpuStaffPanel;

								objectLoad.loadData({params:{LpuUnit_Name: node.attributes.text, Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:node.attributes.object_value, LpuSection_id:null}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id:node.attributes.object_value, LpuSection_id:null}});
							}
							// Читаем список отделений
							if (swLpuStructureTabs.getActiveTab().id == 'tab_section')
							{
								var LpuBuilding_id = node.parentNode.attributes.object_value;
								swLpuSectionPanel.loadData({
									params: {
										LpuUnitType_id: LpuUnitType_id,
										Lpu_id: Lpu_id,
										UnitDepartType_fid: node.attributes.UnitDepartType_fid,
										LpuUnitType_Nick: node.parentNode.attributes.LpuUnitType_Nick,
										LpuBuilding_id: LpuBuilding_id,
										LpuUnit_Name: node.attributes.text,
										LpuUnit_id: node.attributes.object_value,
										RegisterMO_OID: node.parentNode.parentNode.attributes.RegisterMO_OID,
										FRMOUnit_OID: node.attributes.FRMOUnit_OID,
										LpuSection_pid: null
									},
									globalFilters: {LpuUnit_id: node.attributes.object_value, LpuSection_pid: 'null'}
								});
								swLpuSectionPanel.addActions({
									id: 'swLpuSectionPanel_action_new',
									name:'action_new',
									text:langs('Действия'),
									disabled: win.action=='view',
									menu: [{
										text: langs('Объединение'),
										tooltip: langs('Добавить запись к объединению'),
										handler: function() {
											AddRecordToUnion(
												swLpuSectionPanel.ViewGridPanel.getSelectionModel().getSelected(),
												'LpuSection',
												langs('Отделения'),
												function () {
													swLpuSectionPanel.loadData();
													swLpuStructureViewForm.reloadCurrentTreeNode(swLpuSectionPanel);
												}
											)
										},
										iconCls: 'union16'
									}]
								});
							}

							// Читаем список служб
							if (swLpuStructureTabs.getActiveTab().id == 'tab_medservice' && LpuUnit_id > 0)
							{
								swMedServicePanel.loadData({params:{LpuUnit_id: LpuUnit_id, LpuUnitType_id: LpuUnitType_id, LpuBuilding_id: LpuBuilding_id, Lpu_id: Lpu_id}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnitType_id: LpuUnitType_id, LpuUnit_id: LpuUnit_id, LpuSection_id: LpuSection_id, is_All: null, is_Act: swMedServicePanel.defaultFilterValues.is_Act}});
								swLpuStructureViewForm.addMedServicePanelActions();
							}
							break;
						case 5:
						{
							if ( node.attributes.object == 'LpuSection' ) {
								// Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value; Lpu_id уже определен перед началом конструкции switch
								LpuBuilding_id = node.parentNode.parentNode.parentNode.attributes.object_value;
								LpuUnitType_id = node.parentNode.parentNode.attributes.LpuUnitType_id;
								LpuUnit_id = node.parentNode.attributes.object_value;
								LpuSection_id = node.attributes.object_value;/*#####*/
							}

							// Читаем услуги отеления
							if (swLpuStructureTabs.getActiveTab().id == 'tab_usluga' && node.attributes.object == 'LpuSection')
							{
								var params = {
									limit: 100
									,start: 0
									,Lpu_id: Lpu_id
									,LpuBuilding_id: LpuBuilding_id
									,LpuUnit_id: LpuUnit_id
									,LpuSection_id: LpuSection_id
								}
								frms.uslugaComplexOnPlaceGrid.removeAll();
								frms.uslugaComplexOnPlaceGrid.getGrid().getStore().load({
									params: params
								});
							}

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff')
								swLpuStructureViewForm.addStaffActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick().inlist(['ekb','astra']))
								swLpuStructureViewForm.addStaffProfileActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick() != 'kareliya')
								swLpuStructureViewForm.addShowScheduleEditActions();

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt')
								swLpuStructureViewForm.addStaffTTActions();

							// Читаем описание отделения
							if (swLpuStructureTabs.getActiveTab().id == 'tab_descr' && !String(node.attributes.object).inlist(['MedService','Storage']))
							{
//						checkLpuProfile(LpuSection_id);
								swLpuSectionDescription.load({
									params:{LpuSection_id: node.attributes.object_value},
									success: function(frm, action){
										win.overwriteTpl({file_url: (action.result && action.result.data && action.result.data.photo)?action.result.data.photo:''});

										//console.log (node.attributes);
										//swLpuStructureTabs.hideTabStripItem('tab_pacs');
//									if ( Ext.getCmp('lpusectiondescription-panel').findById('LpuSectionProfile_idEdit').getValue() == 65 )
//									{
//										swLpuStructureTabs.unhideTabStripItem('tab_pacs');
//									}
//									else
//									{
//										swLpuStructureTabs.hideTabStripItem('tab_pacs');
//									}
									}

								});
								//alert (LpuSectionProfile_id.value);
							}

							// Читаем список подотделений
							if (swLpuStructureTabs.getActiveTab().id == 'tab_section')
							{
								swLpuSectionPanel.loadData({
									params: {
										Lpu_id: Lpu_id,
										LpuUnitType_id: LpuUnitType_id,
										LpuUnitType_Nick: node.parentNode.parentNode.attributes.LpuUnitType_Nick,
										LpuBuilding_id: LpuBuilding_id,
										LpuUnit_Name: node.parentNode.attributes.text,
										LpuUnit_id: LpuUnit_id,
										RegisterMO_OID: node.parentNode.parentNode.parentNode.attributes.RegisterMO_OID,
										FRMOUnit_OID: node.parentNode.attributes.FRMOUnit_OID,
										LpuSection_pid: node.attributes.object_value
									}, globalFilters: {LpuUnit_id: null, LpuSection_pid: node.attributes.object_value}
								}); // Временно или совсем LpuUnit_id: LpuUnit_id
								swLpuSectionPanel.addActions({
									id: 'swLpuSectionPanel_action_new',
									name:'action_new',
									text:langs('Действия'),
									disabled: win.action=='view',
									menu: [{
										text: langs('Объединение'),
										tooltip: langs('Добавить запись к объединению'),
										handler: function() {
											AddRecordToUnion(
												swLpuSectionPanel.ViewGridPanel.getSelectionModel().getSelected(),
												'LpuSection',
												langs('Подотделения'),
												function () {
													swLpuSectionPanel.loadData();
													swLpuStructureViewForm.reloadCurrentTreeNode(swLpuSectionPanel);
												}
											)
										},
										iconCls: 'union16'
									}]
								});
							}
							// Читаем список сотрудников
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff') {

								swStaffFilterPanel.getForm().reset();
								swStaffFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{LpuSection_id: node.attributes.object_value}});
								swStaffFilterPanel.getForm().findField('MedStaffFact_date_range').setMaxValue(getGlobalOptions().date);
								swStaffFilterPanel.getForm().findField('MedStaffFact_disDate_range').setMaxValue(getGlobalOptions().date);
								swStaffPanel.loadData({params:{Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id: LpuUnit_id, LpuSection_id: node.attributes.object_value}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id: LpuUnit_id, LpuSection_id: node.attributes.object_value}});
							}
							// Читаем список штатки
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt') {
								if (swStaffTTTabPanel.getActiveTab().id == 'tab_MedStaffFactTT') {
									swStaffTTFilterPanel.getForm().reset();
									swStaffTTFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{LpuSection_id: node.attributes.object_value}});
									swStaffTTFilterPanel.getForm().findField('Staff_Date_range').setMaxValue(getGlobalOptions().date);
									swStaffTTFilterPanel.getForm().findField('Staff_endDate_range').setMaxValue(getGlobalOptions().date);
									swStaffTTPanel.loadData({params:{Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id: LpuUnit_id, LpuSection_id: node.attributes.object_value}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id: LpuUnit_id, LpuSection_id: node.attributes.object_value}});
								}else{
									swStaffOSMPanel.loadData({params:{Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id: LpuUnit_id, LpuSection_id: node.attributes.object_value}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnit_id: LpuUnit_id, LpuSection_id: node.attributes.object_value}});
								}
							}
							// Читаем список тарифов
							if (swLpuStructureTabs.getActiveTab().id == 'tab_tariff')
								if (swSectionTariffTabPanel.getActiveTab().id == 'tab_LpuSectionTariff') {
									swSectionTariffPanel.loadData({params:{LpuUnitType_id: LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
								} else if (swSectionTariffTabPanel.getActiveTab().id == 'tab_CoeffIndexTariff') {
									frms.loadCoeffIndexTariffGrid({LpuSection_id: node.attributes.object_value});
								}
							// Читаем список лицензий
							if (swLpuStructureTabs.getActiveTab().id == 'tab_licence')
								swSectionLicencePanel.loadData({params:{LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
							// Читаем  список коечного фонда
							if (swLpuStructureTabs.getActiveTab().id == 'tab_bedstate')
							{
								LpuSection_pid = null;
								if (node.parentNode.attributes.object_id == 'LpuSection_id')
								{
									LpuSection_pid = node.parentNode.attributes.object_value;
								}
								if (node.isExpanded())
								{
									swSectionBedStatePanel.loadData({params:{LpuUnitType_id: LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text, LpuSection_pid: LpuSection_pid, child_count: node.childNodes.length}, globalFilters: {LpuSection_id:node.attributes.object_value}});
								}
								else
								{
									node.expand(false,false,function(node){
										swSectionBedStatePanel.loadData({params:{LpuUnitType_id: LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text, LpuSection_pid: LpuSection_pid, child_count: node.childNodes.length}, globalFilters: {LpuSection_id:node.attributes.object_value}});
									});
								}
							}
							// Читаем общую информацию о койках отделения (подотделения)
							if (swLpuStructureTabs.getActiveTab().id == 'tab_bedstate')
							{
								swSectionBedStateForm.getForm().load({params: {LpuSection_id: node.attributes.object_value}});
								swLpuStructureViewForm.addSectionWardPanelActions();
								swLpuStructureViewForm.addSectionBedStatePanelActions();
								Ext.getCmp('editBedPlanReserveBtn').setVisible(win.action!='view');
							}
							// Читаем список палат
							if (swLpuStructureTabs.getActiveTab().id == 'tab_bedstate')
								swSectionWardPanel.loadData({params:{LpuUnitType_id: LpuUnitType_id,LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value,LpuSectionWard_isAct: swSectionWardPanel.showOnlyAct}});
							// Читаем  список источников финасирования
							if (swLpuStructureTabs.getActiveTab().id == 'tab_finans')
								swSectionFinansPanel.loadData({params:{LpuUnitType_id: LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
							/* Закрыто по задаче https://redmine.swan.perm.ru/issues/4856
							 // Читаем тарифы МЕС
							 if (swLpuStructureTabs.getActiveTab().id == 'tab_tariffmes')
							 swSectionTariffMesPanel.loadData({params:{LpuUnitType_id: LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
							 // Читаем планирование
							 if (swLpuStructureTabs.getActiveTab().id == 'tab_plan')
							 swSectionPlanPanel.loadData({params:{LpuUnitType_id: LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
							 */
							// Читаем Список смен
							if (swLpuStructureTabs.getActiveTab().id == 'tab_shift')
							{
								swSectionShiftPanel.loadData({params:{LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
								// В случае если тип отделения не стационар, то добавление смен не доступно
								if (!node.parentNode.parentNode.attributes.LpuUnitType_id.inlist([6,9]))
								{
									swSectionShiftPanel.ViewActions.action_add.setDisabled(true);
								}
								else
								{
									swSectionShiftPanel.ViewActions.action_add.setDisabled(false);
								}
							}
							// Читаем список служб
							if (swLpuStructureTabs.getActiveTab().id == 'tab_medservice' && LpuSection_id > 0)
							{
								swMedServicePanel.loadData({params:{LpuSection_id: LpuSection_id, LpuUnit_id: LpuUnit_id, LpuUnitType_id: LpuUnitType_id, LpuBuilding_id: LpuBuilding_id, Lpu_id: Lpu_id}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: LpuBuilding_id, LpuUnitType_id: LpuUnitType_id, LpuUnit_id: LpuUnit_id, LpuSection_id: LpuSection_id, is_All: null, is_Act: swMedServicePanel.defaultFilterValues.is_Act}});
								swLpuStructureViewForm.addMedServicePanelActions();
							}

							/*####*/		// Читаем настройки пакас

							if (swLpuStructureTabs.getActiveTab().id == 'tab_pacs')
							{
								params = ( {params:{LpuSection_id: node.attributes.object_value}, globalFilters: {LpuSection_id:node.attributes.object_value}} );
								swPacsSettings.loadData(params);
							}
							//console.log(swPacsSettings);

						}
							break;
						case 6:

							if ( node.attributes.profile == 68 )
							{
								params = ( {params:{LpuSection_id: node.attributes.object_value}, globalFilters: {LpuSection_id:node.attributes.object_value}} );
								swPacsSettings.loadData(params);
							}
							//console.log(swPacsSettings);

							if ( node.attributes.object == 'LpuSection' ) {
								// Lpu_id = node.parentNode.parentNode.parentNode.parentNode.parentNode.attributes.object_value; Lpu_id уже определен перед началом конструкции switch
								LpuBuilding_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
								LpuUnitType_id = node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id;
								LpuUnit_id = node.parentNode.parentNode.attributes.object_value;
								LpuSection_id = node.attributes.object_value;/*#####*/
							}

							// Читаем услуги подотеления
							if (swLpuStructureTabs.getActiveTab().id == 'tab_usluga' && node.attributes.object == 'LpuSection')
							{
								var params = {
									limit: 100
									,start: 0
									,Lpu_id: Lpu_id
									,LpuBuilding_id: LpuBuilding_id
									,LpuUnit_id: LpuUnit_id
									,LpuSection_id: LpuSection_id
								}
								frms.uslugaComplexOnPlaceGrid.removeAll();
								frms.uslugaComplexOnPlaceGrid.getGrid().getStore().load({
									params: params
								});
							}

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff')
								swLpuStructureViewForm.addStaffActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick().inlist(['ekb','astra']))
								swLpuStructureViewForm.addStaffProfileActions();
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff' && getRegionNick() != 'kareliya')
								swLpuStructureViewForm.addShowScheduleEditActions();

							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt')
								swLpuStructureViewForm.addStaffTTActions();

							// Читаем описание отделения
							if (swLpuStructureTabs.getActiveTab().id == 'tab_descr' && !String(node.attributes.object).inlist(['MedService','Storage'])) {
								swLpuSectionDescription.load({params:{LpuSection_id: node.attributes.object_value}});
							}
							// Читаем список сотрудников
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff') {
								swStaffFilterPanel.getForm().reset();
								swStaffFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{LpuSection_id: node.attributes.object_value}});
								swStaffFilterPanel.getForm().findField('MedStaffFact_date_range').setMaxValue(getGlobalOptions().date);
								swStaffFilterPanel.getForm().findField('MedStaffFact_disDate_range').setMaxValue(getGlobalOptions().date);
								swStaffPanel.loadData({params:{Lpu_id: Lpu_id, LpuBuilding_id: node.parentNode.parentNode.parentNode.parentNode.attributes.object_value, LpuUnit_id:node.parentNode.parentNode.attributes.object_value, LpuSection_id: node.attributes.object_value}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: node.parentNode.parentNode.parentNode.parentNode.attributes.object_value, LpuUnit_id:node.parentNode.parentNode.attributes.object_value, LpuSection_id: node.attributes.object_value}});
							}
							// Читаем список штатки
							if (swLpuStructureTabs.getActiveTab().id == 'tab_staff_tt') {
								swStaffTTFilterPanel.getForm().reset();
								swStaffTTFilterPanel.getForm().findField('LpuStructure_id').getStore().load({params:{LpuSection_id: node.attributes.object_value}});
								swStaffTTFilterPanel.getForm().findField('Staff_Date_range').setMaxValue(getGlobalOptions().date);
								swStaffTTFilterPanel.getForm().findField('Staff_endDate_range').setMaxValue(getGlobalOptions().date);
								swStaffTTPanel.loadData({params:{Lpu_id: Lpu_id, LpuBuilding_id: node.parentNode.parentNode.parentNode.parentNode.attributes.object_value, LpuUnit_id:node.parentNode.parentNode.attributes.object_value, LpuSection_id: node.attributes.object_value}, globalFilters: {Lpu_id: Lpu_id, LpuBuilding_id: node.parentNode.parentNode.parentNode.parentNode.attributes.object_value, LpuUnit_id:node.parentNode.parentNode.attributes.object_value, LpuSection_id: node.attributes.object_value}});
							}
							// Читаем список тарифов
							if (swLpuStructureTabs.getActiveTab().id == 'tab_tariff')
								if (swSectionTariffTabPanel.getActiveTab().id == 'tab_LpuSectionTariff') {
									swSectionTariffPanel.loadData({params:{LpuUnitType_id:node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
								} else if (swSectionTariffTabPanel.getActiveTab().id == 'tab_CoeffIndexTariff') {
									frms.loadCoeffIndexTariffGrid({LpuSection_id: node.attributes.object_value});
								}
							// Читаем список лицензий
							if (swLpuStructureTabs.getActiveTab().id == 'tab_licence')
								swSectionLicencePanel.loadData({params:{LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
							// Читаем список коечного фонда
							if (swLpuStructureTabs.getActiveTab().id == 'tab_bedstate')
							{
								LpuSection_pid = null;
								if (node.parentNode.attributes.object_id == 'LpuSection_id')
								{
									LpuSection_pid = node.parentNode.attributes.object_value;
								}
								if (node.isExpanded())
								{
									swSectionBedStatePanel.loadData({params:{LpuUnitType_id:node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text, LpuSection_pid: LpuSection_pid, child_count: node.childNodes.length}, globalFilters: {LpuSection_id:node.attributes.object_value}});
								}
								else
								{
									node.expand(false,false,function(node){
										swSectionBedStatePanel.loadData({params:{LpuUnitType_id:node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text, LpuSection_pid: LpuSection_pid, child_count: node.childNodes.length}, globalFilters: {LpuSection_id:node.attributes.object_value}});
									});
								}
							}
							// Читаем список палат
							if (swLpuStructureTabs.getActiveTab().id == 'tab_bedstate')
								swSectionWardPanel.loadData({params:{LpuUnitType_id:node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id,LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value,LpuSectionWard_isAct: swSectionWardPanel.showOnlyAct}});
							// Читаем список источников финасирования
							if (swLpuStructureTabs.getActiveTab().id == 'tab_finans')
								swSectionFinansPanel.loadData({params:{LpuUnitType_id:node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
							/* Закрыто по задаче https://redmine.swan.perm.ru/issues/4856
							 // Читаем тарифы МЭС
							 if (swLpuStructureTabs.getActiveTab().id == 'tab_tariffmes')
							 swSectionTariffMesPanel.loadData({params:{LpuUnitType_id:node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
							 // Читаем планирование
							 if (swLpuStructureTabs.getActiveTab().id == 'tab_plan')
							 swSectionPlanPanel.loadData({params:{LpuUnitType_id:node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id, LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
							 */
							// Читаем Список смен
							if (swLpuStructureTabs.getActiveTab().id == 'tab_shift')
							{
								swSectionShiftPanel.loadData({params:{LpuSection_id: node.attributes.object_value, LpuSection_Name: node.attributes.text}, globalFilters: {LpuSection_id:node.attributes.object_value}});
								// В случае если тип отделения не стационар, то добавление смен не доступно
								if (!node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id.inlist([6,9]))
								{
									swSectionShiftPanel.ViewActions.action_add.setDisabled(true);
								}
								else
								{
									swSectionShiftPanel.ViewActions.action_add.setDisabled(false);
								}
							}
							break;
						default:
							break;
					}
				}
			}

			function LpuStructureTreeClick(node,e)
			{
				var level = swLpuStructureViewForm.getNodeTrueLevel(node); // Добавлено в связи с появлением филиалов на уровне с подразделениями, из-за чего подразделения (группы отделений и прочее), находящиеся в филиале съехали на один уровень вверх


				var unhideTabsList = [];
				if (node.id != 'root')
				{
					if (node.attributes.object != 'MedService' && node.attributes.object != 'Storage') {
						switch (level)
						{
							case 1:  // МО или Филиалы
								if (node.attributes.object === 'LpuFilial')
								{
									unhideTabsList = ['tab_building', 'tab_descr'];

									swLpuFilialDescription.show();
									swLpuDescription.hide();
									swLpuBuildingDescription.hide();
									swLpuUnitDescription.hide();
									swLpuSectionDescription.hide();

									break;
								}

								unhideTabsList = ['tab_descr','tab_staff','tab_staff_tt', 'tab_attributes'];
								if ( getRegionNick() != 'by' ) {
									unhideTabsList.push('tab_lputariff');
								}
								if (!onlyCadrUserView()) {
									unhideTabsList = unhideTabsList.concat(['tab_building','tab_region','tab_lpuorgserved','tab_usluga','tab_quote','tab_medservice','tab_storage']);
								}

								if (!onlyCadrUserView()) { // && !Ext.isEmpty(node.attributes.MesAgeLpuType_Code) && node.attributes.MesAgeLpuType_Code.inlist([1,3]) возможно нужна фильтрация комбо доступных DispDopClass в зависимости от MesAgeLpuType_Code.
									if ( getRegionNick() != 'by' ) {
										unhideTabsList.push('tab_dopdispplandd');
									}
								}

								// Описания
								swLpuDescription.show();
								swLpuFilialDescription.hide();
								swLpuBuildingDescription.hide();
								swLpuUnitDescription.hide();
								swLpuSectionDescription.hide();

								var
									DispDopClass_id = Ext.getCmp('DP_DispDopClass_id').getValue(),
									MesAgeLpuType_Code = swLpuStructureViewForm.findById('lpu-structure-frame').getRootNode().findChild('object', 'Lpu').attributes.MesAgeLpuType_Code;

								Ext.getCmp('DP_DispDopClass_id').getStore().filterBy(function(record) {
									if ( MesAgeLpuType_Code == 2 ) {
										return (record.get('DispDopClass_Code').toString().inlist([2,3,4,5,6,8]));
									}
									else if ( MesAgeLpuType_Code == 1 ) {
										return (record.get('DispDopClass_Code').toString().inlist([1,7]));
									}
									else {
										return true;
									}
								});

								if (!Ext.isEmpty(DispDopClass_id)) {
									var index = Ext.getCmp('DP_DispDopClass_id').getStore().findBy(function(record) {
										return (record.get('DispDopClass_id') == DispDopClass_id);
									});

									if ( index == -1 ) {
										DispDopClass_id = null;
									}
								}

								if (Ext.isEmpty(DispDopClass_id) && Ext.getCmp('DP_DispDopClass_id').getStore().getCount() > 0) {
									DispDopClass_id = Ext.getCmp('DP_DispDopClass_id').getStore().getAt(0).get('DispDopClass_id');
								}

								Ext.getCmp('DP_DispDopClass_id').setValue(DispDopClass_id);

								break;

							case 2:  // Подразделения и участки
								if (node.attributes.object == 'LpuRegionTitle' || node.attributes.object == 'StorageTitle') {
									if (node.attributes.object == 'LpuRegionTitle') {
										if (!onlyCadrUserView()) {
											unhideTabsList = unhideTabsList.concat(['tab_region']);
										}
										swLpuBuildingDescription.hide();
									}
									if (node.attributes.object == 'StorageTitle') {
										if (!onlyCadrUserView()) {
											unhideTabsList = unhideTabsList.concat(['tab_storage']);
										}
									}
								} else {
									unhideTabsList = ['tab_staff','tab_staff_tt'];
									if (!onlyCadrUserView()) {
										unhideTabsList = unhideTabsList.concat(['tab_descr','tab_unit','tab_medservice','tab_usluga','tab_storage']);
									}
									unhideTabsList.push('tab_attributes');
									swLpuBuildingDescription.show();
								}

								// Описания
								swLpuDescription.hide();
								swLpuFilialDescription.hide();
								swLpuUnitDescription.hide();
								swLpuSectionDescription.hide();
								break;

							case 3:  // Тип группы отделений и участки
								if (node.attributes.object =='LpuRegionType')
								{
									if (!onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_region']);
								}
								else
								{
									if (!onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_unit','tab_medservice','tab_storage']);
								}

								if (onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_lpusection_group']);

								// Описания
								swLpuBuildingDescription.hide();
								swLpuDescription.hide();
								swLpuUnitDescription.hide();
								swLpuSectionDescription.hide();
								swLpuFilialDescription.hide();
								break;
							case 4: // Группы отделений и участки
								if (node.attributes.object =='LpuRegion')
								{
									if (!onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_oneregion']);
								}
								else
								{
									unhideTabsList = ['tab_descr','tab_staff','tab_staff_tt'];
									if (!onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_section','tab_usluga','tab_medservice','tab_storage']);
									var isStac = !Ext.isEmpty(node.parentNode.attributes.LpuUnitType_id) && node.parentNode.attributes.LpuUnitType_id.inlist([1,6,7,9]);
									/*
									 swLpuSectionPanel.setColumnHidden('LpuSectionBedState_Plan', !isStac);
									 swLpuSectionPanel.setColumnHidden('LpuSectionBedState_Repair', !isStac);
									 swLpuSectionPanel.setColumnHidden('LpuSectionBedState_Fact', !isStac);
									 */
								}
								// Если текущий таб скрытый то ставим активным первый таб описания

								// Описания
								swLpuDescription.hide();
								swLpuBuildingDescription.hide();
								swLpuSectionDescription.hide();
								swLpuUnitDescription.show();
								swLpuFilialDescription.hide();
								swLpuStructureTabs.findById('tab_section').setTitle(langs('Отделения'));
								swLpuStructureTabs.findById('tab_section').setIconClass('lpu-section16');
								break;
							case 5:	 // отделения
								/*PACS*/			//checkLpuProfile(node.attributes.object_value);

								unhideTabsList = ['tab_descr','tab_staff','tab_staff_tt'];
								if (getRegionNick() == 'msk') {
									unhideTabsList.push('tab_attributes');
								}
								if (!onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_medservice','tab_section','tab_usluga','tab_tariff','tab_finans','tab_licence','tab_storage']);

								// Описания
								swLpuSectionDescription.show();
								swLpuBuildingDescription.hide();
								swLpuUnitDescription.hide();
								swLpuDescription.hide();
								swLpuFilialDescription.hide();

								swLpuStructureTabs.findById('tab_section').setTitle(langs('Подотделения'));
								swLpuStructureTabs.findById('tab_section').setIconClass('lpu-subsection16');

								//console.log ('my'+node.parentNode.parentNode.attributes.LpuUnitType_id.inlist);

								/*if (node.parentNode.parentNode.attributes.LpuUnitType_id.inlist([1,6,9])) // для стационаров, кроме на дому
								 {
								 unhideTabsList = unhideTabsList.concat(['tab_ward']);
								 }
								 else
								 {
								 }*/
								var stacIds = [1,6,7,9,17];
								var isStac = node.parentNode.parentNode.attributes.LpuUnitType_id.inlist(stacIds);
								/*
								 swLpuSectionPanel.setColumnHidden('LpuSectionBedState_Plan', !isStac);
								 swLpuSectionPanel.setColumnHidden('LpuSectionBedState_Repair', !isStac);
								 swLpuSectionPanel.setColumnHidden('LpuSectionBedState_Fact', !isStac);
								 */
								if (isStac) {  // Только для стационаров (в случае самары включая стац на дому - см. #18332)
									if (!onlyCadrUserView()) {
										unhideTabsList = unhideTabsList.concat(['tab_bedstate']);
									}
									//swLpuStructureTabs.unhideTabStripItem('tab_tariffmes');//Тарифы МЭС
									//swLpuStructureTabs.unhideTabStripItem('tab_plan');  // Планирование

								}

								if (node.parentNode.parentNode.attributes.LpuUnitType_id.inlist([6,9])) {  // Смены. Только для стационаров при поликлинике (UPD:#38099)и стационаров при станционаре
									if (!onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_shift']);
								}

								//console.log(node.attributes);

								if (node.attributes.profile == 65)
								{
									/* temporary			unhideTabsList = unhideTabsList.concat(['tab_pacs']); */
								}
								//костыль для УФЫ


								regionNum = getGlobalOptions().region.number;
								unitCode =   Ext.getCmp('lpusectiondescription-panel').findById('LpuSection_CodeEdit').getValue();
								//alert (unitCode);
								if (regionNum == 2)
								//console.log(unitCode)
								{
									if (
										( node.attributes.profile == 69) ||
										( unitCode == 874 )||
										( unitCode == 875 )||
										( unitCode == 876 )||
										( unitCode == 879 )
									)
									{
										/* temporary				unhideTabsList = unhideTabsList.concat(['tab_pacs']); */
									}
								}

								break;
							case 6: // подотделения
								unhideTabsList = ['tab_descr'];
								if (!onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_staff','tab_staff_tt','tab_tariff','tab_finans','tab_licence','tab_storage']);

								//console.log(node.attributes.profile);

								if (node.attributes.profile == 68)
								{
									/* temporary				unhideTabsList = unhideTabsList.concat(['tab_pacs']); */
								}
								else if (node.attributes.object != 'MedService')
								{
									swLpuStructureTabs.setActiveTab('tab_descr');
								}
								/*if (node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id.inlist([1,6,9])) // для стационаров, кроме на дому
								 {
								 unhideTabsList = unhideTabsList.concat(['tab_ward']);// палаты
								 }
								 */

								if (node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id.inlist([1,6,7,9])) {  // Только для стационаров
									//unhideTabsList = unhideTabsList.concat(['tab_bedstate']);  // Коечный фонд
									//unhideTabsList = unhideTabsList.concat(['tab_tariffmes']);//Тарифы МЭС
									//unhideTabsList = unhideTabsList.concat(['tab_plan']);	 // Планирование
								}

								if (node.parentNode.parentNode.parentNode.attributes.LpuUnitType_id.inlist([6,9])) {  // Смены. Только для стационаров при поликлинике
									if (!onlyCadrUserView()) unhideTabsList = unhideTabsList.concat(['tab_shift']);
								}

								// Описания
								swLpuSectionDescription.show();
								swLpuBuildingDescription.hide();
								swLpuUnitDescription.hide();
								swLpuDescription.hide();
								swLpuFilialDescription.hide();
								swLpuStructureTabs.findById('tab_section').setTitle(langs('Подотделения'));

								break;
							default:
								break;
						}
					}

					if (!onlyCadrUserView() && node.attributes.object == 'MedService') {
						if (node.attributes.MedServiceType_SysNick) {
							switch (true) {
								case (node.attributes.MedServiceType_SysNick.inlist(['lab', 'microbiolab'])):
									unhideTabsList = ['tab_medservicemedpersonal','tab_storage','tab_analyzer'];
									break;
								case (node.attributes.MedServiceType_SysNick.inlist(['remoteconsultcenter'])):
									unhideTabsList = ['tab_medservicemedpersonal','tab_lpusectionprofilemedservice'];//'tab_remotecons'
									//log(unhideTabsList);
									break;
								// @task https://redmine.swan.perm.ru/issues/99731
								case (node.attributes.MedServiceType_SysNick.inlist(['func','oper_block'])):
									unhideTabsList = ['tab_medservicemedpersonal','tab_storage','tab_usluga','tab_resource'];
									break;
								case (node.attributes.MedServiceType_SysNick.inlist(['osmotrgosp'])):
									unhideTabsList = ['tab_medservicemedpersonal'];
									break;
								default:
									unhideTabsList = ['tab_medservicemedpersonal','tab_storage','tab_usluga'];
									break;
							}
						}
						if (IS_DEBUG == 1 && node.attributes.MedServiceType_SysNick && node.attributes.MedServiceType_SysNick.inlist(['func'])) {
							unhideTabsList = unhideTabsList.concat(['tab_apparatus']);
						}
					}
					if (!onlyCadrUserView() && node.attributes.object == 'Storage') {
						unhideTabsList = [/*'tab_storagemedpersonal',*/ 'tab_storage'];
					}
				}

				var firstUnHiddenTab = '';
				//console.log(unhideTabsList);//

				// список всех вкладок
				for( var key in swLpuStructureTabs.items.items ) {
					// прячем те что не надо показывать и открываем те, что надо
					if (swLpuStructureTabs.items.items[key].id) {
						if (swLpuStructureTabs.items.items[key].id.inlist(unhideTabsList)) {
							if (firstUnHiddenTab == '') {
								firstUnHiddenTab = swLpuStructureTabs.items.items[key].id;
							}
							swLpuStructureTabs.unhideTabStripItem(swLpuStructureTabs.items.items[key].id);
						} else {
							swLpuStructureTabs.hideTabStripItem(swLpuStructureTabs.items.items[key].id);
						}
					}
				}

				//Для администратора регистратуры ЛПУ оставляем только описание и учатски
				if (!isSuperAdmin() && !isLpuAdmin() && isRegAdmin()) {
					swLpuStructureTabs.items.each(function(rec){
						if (!rec.id.inlist([ 'tab_descr', 'tab_region'])) {
							swLpuStructureTabs.hideTabStripItem(rec.id);
						}
					});
				}

				// По умолчанию филиал должен открываться на вкладке подразделения. ТЗ 115758
				if (node.attributes.object == 'LpuFilial')
				{
					swLpuStructureTabs.setActiveTab('tab_building');
				}


				// Если текущий таб скрытый то ставим активным первый активный таб
				if (!swLpuStructureTabs.getActiveTab().id.inlist(unhideTabsList) && firstUnHiddenTab != '')
				{
					swLpuStructureTabs.setActiveTab(firstUnHiddenTab);
				}

				LoadOnChangeTab(node);
				swLpuStructureTabs.doLayout();
			}


			function copyUslugaSectionList(options) {
				if ( typeof options != 'object' ) {
					return false;
				}

				var params = {};
				switch(options.mode) {
					case 'lpu':
						if ( !options.LpuSection_id ) {
							return false;
						}
						params.LpuSection_id = options.LpuSection_id;
						break;
					case 'lpusection':
						if ( !options.LpuSection_id || !options.LpuSection_pid) {
							return false;
						}
						params.LpuSection_id = options.LpuSection_id;
						params.LpuSection_pid = options.LpuSection_pid;
						break;
					case 'medservice':
						if ( !options.MedService_id || !options.LpuSection_pid) {
							return false;
						}
						params.MedService_id = options.MedService_id;
						params.LpuSection_pid = options.LpuSection_pid;
						break;
					default:
						return false;
				}

				var loadMask = new Ext.LoadMask(Ext.getBody(), {msg: "Копирование списка услуг..."});
				loadMask.show();

				Ext.Ajax.request({
					failure: function(response, options) {
						loadMask.hide();

						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
						}
					},
					params: params,
					success: function(response, options) {
						loadMask.hide();

						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success ) {
							sw.swMsg.alert(langs('Сообщение'), langs('Список услуг успешно скопирован'), function() {reloadTree();});
						}
						else if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
						}
					},
					url: C_USLUGASECTION_COPY
				});
			}

			Ext.apply(this, {
				xtype: 'panel',
				//layout:'border',
				items: [
					swLpuStructureFrame,
					swLpuStructureTabs]
			});

			sw.Promed.swLpuStructureViewForm.superclass.initComponent.apply(this, arguments);

		}
	});

