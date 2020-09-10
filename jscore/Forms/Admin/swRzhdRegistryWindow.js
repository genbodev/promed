/**
 * swRzhdRegistryWindow - окно просмотра регистра ржд
 * @author		Salavat Magafurov
 * @version		12.2017
 */

sw.Promed.swRzhdRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRzhdRegistryWindow',
	objectName: 'swRzhdRegistryWindow',
	title: langs('Регистр РЖД'),
	layout: 'border',
	buttonAlign: 'right',
	closeAction: 'hide',
	width: 500,
	modal: true,
	maximized: true,
	resizable: false,
	closable: true,
	plain: true,
	doSearch: function() {
		var params = getAllFormFieldValues(this.FilterPanel);
		params.start = 0;
		params.limit = 100;
		this.SearchGrid.loadData({globalFilters:params});
	},

	openEmk: function(gridPanel) {
		var record = gridPanel.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('Person_id')))
			return;
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			ARMType: 'common',
			readOnly: true
		});
	},

	showRegisterIncludeWindow: function(data) {
		var win = this;
		var registerIncludeWindow = getWnd('swRegisterIncludeWindow');

		registerIncludeWindow.show({
			Person_FIO: data.PersonFirName_FirName + ' ' + data.PersonSecName_SecName + ' ' + data.PersonSurName_SurName,
			Person_id: data.Person_id,
			RegisterType_Code: "RZHD",
			callback: function(windowCallback) {
				win.doSearch();
				Ext.Ajax.request({
					url: '/?c=RzhdRegistry&m=isExistProfile',
					params: {
						Register_id: windowCallback.Register_id
					},
					callback: function(options, success, response) {
						var params = {
							Register_id: windowCallback.Register_id,
							Register_setDate: windowCallback.Register_setDate,
							Person_id: data.Person_id,
							Action: 'edit'
						}
						
						if (success) {
							params.RzhdRegistry_id = Ext.util.JSON.decode(response.responseText) || null;
							if(params.RzhdRegistry_id) params.msg = 'У пациента есть сохраненная анкета';
						}
						getWnd('swRzhdRegistryViewWindow').show(params);

					}
				})

			}
		})
	},

	showPersonSearchWindow: function() {
		var wnd = this,
			params = new Object(),
			personSearchWindow = getWnd('swPersonSearchWindow');
		params.Action = 'add';

		personSearchWindow.show({
			onClose: Ext.emptyFn,
			onSelect: function(data) {
				if(data.Person_deadDT) {
					sw.swMsg.alert('Сообщение','Нельзя добавить умершего пациента');
					return;
				}
				personSearchWindow.hide();
				wnd.showRegisterIncludeWindow(data);

			},
			searchMode: 'all'
		});
		return true;
	},

	openWindow: function(Action) {
		var wnd = this,
			record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();

		if(!record) {
			sw.swMsg.alert(langs('Сообщение'),langs('Не выбрана запись.'));
			return;
		};

		var disDT = record.get('Register_disDate');
		var deadDT = record.get('Person_deadDT');
		var params = new Object();
		params.Action = Action;
		params.Register_id = record.get('Register_id');
		params.RzhdRegistry_id = record.get('RzhdRegistry_id');
		params.Person_id = record.get('Person_id');
		params.Register_setDate = record.get('Register_setDate');
		params.Register_disDate = record.get('Register_disDate');
		params.RegisterDisCause_id = record.get('RegisterDisCause_id');

		params.callback = function() {
			wnd.doSearch();
		};

		if(Action.inlist(['view','edit'])){

			if(disDT || deadDT)
				params.Action = 'view';

			getWnd('swRzhdRegistryViewWindow').show(params);

		} else if (Action == 'out') {

			if(disDT) {
				sw.swMsg.alert(langs('Внимание'),langs('Пациент уже исключен.'));
				return;
			}

			params.RegisterType_Code = 'RZHD';
			params.Register_setDate = record.get('Register_setDate');
			getWnd('swRegisterOutWindow').show(params);
		}
	},

	deletePerson: function() {
		var wnd = this;
		var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('Register_id')) {
			return false;
		}
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {
						Register_id: record.get('Register_id'),
						pmUser_id: getGlobalOptions().pmuser_id
					};
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (!response_obj.Error_Msg) {
								wnd.doSearch();
							}
						},
						params: params,
						url: '/?c=Register&m=delete'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},

	show: function() {
		sw.Promed.swRzhdRegistryWindow.superclass.show.apply(this, arguments);
		var wnd = this;

		if (!wnd.SearchGrid.getAction('action_openemk')) {
			wnd.SearchGrid.addActions({
				name: 'action_openemk',
				text: langs('Открыть ЭМК'),
				tooltip: 'Открыть электронную медицинскую карту пациента',
				iconCls: 'open16',
				handler: function() {
					wnd.openEmk(wnd.SearchGrid);
				}
			});
		}

		wnd.SearchGrid.addActions({
			name:'out', 
			text:langs('Исключить из регистра'), 
			tooltip: langs('Исключить из регистра'),
			iconCls: 'pers-disp16',
			handler: function() {
				wnd.openWindow('out');
			}
		});
	},

	initComponent: function() {
		var wnd = this;

		/* FIELDS */

		var RzhdFilterPanel = {
			border: false,
			layout: 'column',
			autoHeight: true,
			defaults: {
				width: 500,
				layout: 'form',
				labelWidth: 200,
				border: false
			},
			items: [
				{
					items:[
						new sw.Promed.SwRzhdOrgCombo({
							hiddenName: 'RzhdOrg_id'
						}),
						new sw.Promed.SwRzhdWorkerCategoryCombo({
							hiddenName: 'RzhdWorkerCategory_id'
						}),
						{
							xtype: 'swrzhdworkergroupcombo',
							hiddenName:'RzhdWorkerGroup_id',
							validate: function(){
								var value = this.getValue();
								var SubgroupStory = wnd.FilterPanel.getForm().findField('RzhdWorkerSubgroup_id').getStore();
								if(value == "") {
									SubgroupStory.clearFilter();
								} else {
									SubgroupStory.filterBy(function(record){
										return record.get('RzhdWorkerGroup_id') == value;
									});
								}
							},
							listeners: {
								'render': function() {
									this.getStore().filterBy(function(record){
										return record.get('RzhdWorkerCategory_id') == 2;
									});
								},
								'select': function() {
									wnd.FilterPanel.getForm().findField('RzhdWorkerSubgroup_id').clearValue();
								},
								'beforequery': function(queryEvent) {
									queryEvent.combo.onLoad();
									return false; 
								}
							}
						},
						new sw.Promed.SwRzhdWorkerSubgroupCombo({
							hiddenName:'RzhdWorkerSubgroup_id',
							listeners:{
								'valid':function(){
									if(this.getRawValue().trim()==""){
										this.value = "";
									}
								},
								'beforequery': function(queryEvent) {
									queryEvent.combo.onLoad();
									return false; 
								}
							}
						})
					]
				},
				{
					defaults: {
						width: 170
					},
					items: [
						new Ext.form.DateRangeField({
							name: 'RzhdRegistry_PensionBegDate_Range',
							fieldLabel: 'Дата начала пенсии',
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}),
						new Ext.form.DateRangeField({
							name: 'Register_setDate_Range',
							fieldLabel: 'Дата включения в регистр',
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}),
						new Ext.form.DateRangeField({
							name: 'Register_disDate_Range',
							fieldLabel: 'Дата исключения из регистра',
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}),
						{
							xtype: 'swregisterdiscausecombo',
							hiddenName: 'RegisterDisCause_id',
							RegisterType_Code: 'RZHD'
						}
					]
				}
			]
		};

		/* PANELS */

		wnd.SearchGrid = new sw.Promed.ViewFrame({
			id: 'RzhdRegistrySearchGrid',
			object: 'RzhdRegistry',
			region: 'center',
			pageSize: 100,
			border: true,
			uniqueId: true,
			forcePrintMenu: true,
			paging: true,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			root: 'data',
			totalProperty: 'totalCount',
			auditOptions: {
				key: 'RzhdRegistry_id',
				field: 'RzhdRegistry_id',
				schema: 'r2'
			},
			stringfields: [
				{name: 'RzhdRegistry_id', type: 'int'},
				{name: 'Register_id', hidden: true, type: 'int'},
				{name: 'Person_id', hidden: true, type: 'int'},
				{name: 'Person_Surname', header: 'Фамилия', type: 'string', width: 160},
				{name: 'Person_FirstName', header: 'Имя', type: 'string', width: 160},
				{name: 'Person_SecondName', header: 'Отчество', type: 'string', width: 160},
				{name: 'Person_Snils', hidden: true },
				{name: 'Person_PolisNum', hidden: true },
				{name: 'Person_BirthDay', header: 'Д/р', type: 'date', width: 120},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'Lpu_Nick', header: 'МО прикрепления', type: 'string', width: 200},
				{name: 'Register_setDate', header: 'Дата включения', type: 'date', width: 100},
				{name: 'Register_disDate', header: 'Дата исключения', type: 'date', width: 100},
				{name: 'RegisterDisCause_id', hidden: true, type: 'int'},
				{name: 'RegisterDisCause_name', header: 'Причина исключения', type: 'string', width: 160},
				{name: 'Person_deadDT', header: 'Дата смерти', type: 'date', width: 120 }
			],
			actions: [
				{ name: 'action_add', disabled: !isUserGroup('RzhdRegistry'), handler: function(){wnd.showPersonSearchWindow()} },
				{ name: 'action_edit', disabled: !isUserGroup('RzhdRegistry'), handler: function(){wnd.openWindow('edit')} },
				{ name: 'action_view', handler: function(){wnd.openWindow('view')} },
				{ name: 'action_delete', disabled: !isUserGroup('RzhdRegistry') || !isSuperAdmin(), handler: function(){wnd.deletePerson()} },
				{ name: 'action_refresh', handler: function() {wnd.doSearch() } }
			]
		});

		wnd.FilterPanel = getBaseSearchFiltersFrame({
			isDisplayPersonRegisterRecordTypeField: false,
			allowPersonPeriodicSelect: false,
			labelWidth: 130,
			ownerWindow: wnd,
			searchFormType: 'RzhdRegistry',
			tabIndexBase: TABINDEX_ORW,
			tabPanelHeight: 225,
			tabPanelId: 'RzhdSearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'form',
				title: '<u>6</u>. Регистр',
				items: [
					RzhdFilterPanel
				]
			}
			]
		})

		Ext.apply(wnd,{
			items: [
				wnd.FilterPanel,
				wnd.SearchGrid
			],
			buttons: [
			{
				text: BTN_FRMSEARCH,
				handler: function() {
					wnd.doSearch();
				},
				iconCls: 'search16'
			},
			{
				text: langs('Сброс'),
				handler: function() {
					wnd.SearchGrid.getGrid().getStore().removeAll();
					wnd.FilterPanel.getForm().reset();
				},
				iconCls: 'resetsearch16'
			},
			'-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(wnd.title);
				}
			},
			{
				text: langs('Закрыть'),
				tabIndex: -1,
				tooltip: langs('Закрыть'),
				iconCls: 'cancel16',
				handler: function() {
					wnd.hide();
				}
			}]
		});

		sw.Promed.swRzhdRegistryWindow.superclass.initComponent.apply(wnd, arguments);
	}
});