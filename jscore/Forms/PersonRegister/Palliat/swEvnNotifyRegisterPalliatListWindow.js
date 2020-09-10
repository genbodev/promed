/**
 * swEvnNotifyRegisterPalliatListWindow - Журнал извещений по паллиативной помощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2018 Swan Ltd.
 * @author       Sabirov Kirill
 * @version      12.2018
 * @comment      Префикс для id компонентов ENRPLW
 */
sw.Promed.swEvnNotifyRegisterPalliatListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	title: langs('Журнал извещений по паллиативной помощи'),
	PersonRegisterType_SysNick: 'palliat',
	MorbusType_SysNick: 'palliat',
	width: 800,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	height: 550,
	modal: false,
	plain: true,
	resizable: true,

	getButtonSearch: function() {
		return Ext.getCmp('ENRPLW_SearchButton');
	},

	doReset: function() {
		var base_form = this.getFilterForm().getForm();
		base_form.reset();
		this.RootViewFrame.ViewActions.open_emk.setDisabled(true);
		this.RootViewFrame.ViewActions.action_edit.setDisabled(true);
		this.RootViewFrame.ViewActions.action_view.setDisabled(true);
		this.RootViewFrame.ViewActions.action_delete.setDisabled(true);
		this.RootViewFrame.ViewActions.person_register_include.setDisabled(true);
		this.RootViewFrame.ViewActions.palliat_person_register_not_include.setDisabled(true);
		this.RootViewFrame.ViewActions.action_refresh.setDisabled(true);
		this.RootViewFrame.getGrid().getStore().removeAll();
	},

	doSearch: function(params) {

		var base_form = this.getFilterForm().getForm();

		if (typeof params != 'object') {
			params = {};
		}
		if ( !params.firstLoad && this.findById('EvnNotifyRegisterPalliatListFilterForm').isEmpty() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function() {
			});
			return false;
		}

		var grid = this.RootViewFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Выбран тип поиска человека ') + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? langs('по состоянию на момент случая') : langs('По всем периодикам')) + langs('.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?'),
				title: langs('Предупреждение')
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('EvnNotifyRegisterPalliatListFilterForm'));

		post.limit = 100;
		post.start = 0;
		post.PersonRegisterType_SysNick = this.PersonRegisterType_SysNick;

		//log(post);

		if ( base_form.isValid() ) {
			this.RootViewFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}

	},

	initComponent: function() {
		var me = this;
		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', tooltip: langs('Создать Направление на включение в регистр'), handler: function() {
					me.openEvnNotifyRegisterIncludeWindow('add');
				}},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function() {
					me.openEvnNotifyRegisterIncludeWindow('view');
				}},
				{name: 'action_delete', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			disableActions:false,
			dataUrl: C_SEARCH,
			id: 'ENRPLW_EvnNotifyRegisterPalliatListSearchGrid',
			object: 'PalliatNotify',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PalliatNotify_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'PersonRegisterType_SysNick', type: 'string', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Lpu_did', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'pmUser_updId', type: 'int', hidden: true},
				{name: 'isInclude', type: 'int', hidden: true},
				{name: 'EvnNotifyBase_setDate', type: 'date', header: langs('Дата создания'), width: 120},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 120},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 120},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 120},
				{name: 'Person_Birthday', type: 'date', header: langs('Дата рождения'), width: 90},
				{name: 'AttachLpu_Nick', type: 'string', header: langs('МО прикр.'), width: 150},
				{name: 'Diag_Name', type: 'string', header: langs('Диагноз МКБ-10'), width: 150, id: 'autoexpand'},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата вкл / невкл в регистр'), width: 180}
			],
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function() {
				me.getButtonSearch().disable();
			},
			onDblClick:function () {
				me.openEvnNotifyRegisterIncludeWindow('view');
			},
			onLoadData: function() {
				me.getButtonSearch().enable();
				this.getAction('action_add').setDisabled(me.fromARM !== null && me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer']) && false == sw.Promed.personRegister.isPalliatRegistryOperator());
			},
			onRowSelect: function(sm,index,record) {
				this.getAction('open_emk').setDisabled(Ext.isEmpty(record.get('Person_id')));
				//this.getAction('action_edit').setDisabled(!Ext.isEmpty(record.get('isInclude')));
				//this.getAction('action_delete').setDisabled(!Ext.isEmpty(record.get('isInclude')));
				this.getAction('person_register_include').setDisabled(!Ext.isEmpty(record.get('isInclude')));
				this.getAction('palliat_person_register_not_include').setDisabled(!Ext.isEmpty(record.get('isInclude')));
			}
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					me.doSearch();
				},
				iconCls: 'search16',
				id: 'ENRPLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					me.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				text: '-'
			}, HelpButton(this, -1), {
				handler: function() {
					me.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					me.buttons[me.buttons.length - 2].focus();
				},
				onTabAction: function() {
					me.findById('ENRPLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', me.findById('ENRPLW_SearchFilterTabbar').getActiveTab());
				},
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( me.filterForm == undefined ) {
					me.filterForm = me.findById('EvnNotifyRegisterPalliatListFilterForm');
				}
				return me.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnNotifyRegisterPalliatListFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'PalliatNotify',
				tabPanelHeight: 235,
				tabPanelId: 'ENRPLW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							var form = me.getFilterForm().getForm();
							form.findField('Diag_Code_From').focus(250, true);
						}
					},
					title: langs('<u>6</u>. Извещение'),
					items: [{
						fieldLabel: langs('Код диагноза с'),
						hiddenName: 'Diag_Code_From',
						valueField: 'Diag_Code',
						width: 450,
						//PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: langs('по'),
						hiddenName: 'Diag_Code_To',
						valueField: 'Diag_Code',
						width: 450,
						//PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: langs('МО, куда направлено извещение'),
						hiddenName: 'Lpu_sid',
						width: 450,
						xtype: 'swlpucombo'
					}, {
						fieldLabel: langs('Дата заполнения извещения'),
						name: 'EvnNotifyBase_setDT_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}, {
						fieldLabel: langs('Включен в регистр'),
						xtype: 'swyesnocombo',
						width: 120,
						hiddenName: 'isNotifyProcessed'
					}]
				}]
			}), this.RootViewFrame]
		});

		sw.Promed.swEvnNotifyRegisterPalliatListWindow.superclass.initComponent.apply(this, arguments);
	},

	listeners: {
		'beforeShow': function(win) {
			if (false == sw.Promed.personRegister.isAllow(win.PersonRegisterType_SysNick)) {
				return false;
			}
			return true;
		},
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.getFilterForm().doLayout();
		},
		'restore': function(win) {
			win.getFilterForm().doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('ENRPLW_SearchFilterTabbar').setWidth(nW - 5);
			win.getFilterForm().setWidth(nW - 5);
		}
	},

	show: function() {
		sw.Promed.swEvnNotifyRegisterPalliatListWindow.superclass.show.apply(this, arguments);
		var me = this;

		this.RootViewFrame.addActions({
			name:'palliat_person_register_not_include',
			text:langs('Не включать в регистр'),
			tooltip: langs('Не включать в регистр'),
			iconCls: 'reset16',
			disabled: true,
			menu: new Ext.menu.Menu({id:'ENRPLW_personRegisterNotIncludeMenu'})
		});

		this.RootViewFrame.addActions({
			name:'person_register_include',
			text:langs('Включить в регистр'),
			tooltip: langs('Включить в регистр'),
			iconCls: 'ok16',
			disabled: true,
			handler: function() {
				me.personRegisterInclude();
			}
		});

		this.RootViewFrame.addActions({
			name:'open_emk',
			text:langs('Открыть ЭМК'),
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			iconCls: 'open16',
			handler: function() {
				me.emkOpen();
			}
		});

		var base_form = this.getFilterForm().getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		this.fromARM = null;
		if(arguments[0] && arguments[0].fromARM)
		{
			this.fromARM = arguments[0].fromARM;
		}
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		var attach_lpu_combo = base_form.findField('AttachLpu_id');
		if (false == sw.Promed.personRegister.isPalliatRegistryOperator()) {
			attach_lpu_combo.setValue(getGlobalOptions().lpu_id);
		} else {
			attach_lpu_combo.setValue(null);
		}
		attach_lpu_combo.setDisabled(false == sw.Promed.personRegister.isPalliatRegistryOperator());

		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
		{
			attach_lpu_combo.setValue(null);
			attach_lpu_combo.setDisabled(false);
		}
		this.doLayout();
		this.setMenu(true);
		this.doSearch({firstLoad: true});
	},
	/** Создание меню
	 */
	setMenu: function(is_first) {
		if (is_first) {
			this.createPersonRegisterFailIncludeCauseMenu();
		}
	},
	/** Создание меню причин не включения в регистр
	 */
	createPersonRegisterFailIncludeCauseMenu: function() {
		sw.Promed.personRegister.createPersonRegisterFailIncludeCauseMenu({
			id: 'ENRPLW_personRegisterNotIncludeMenu',
			ownerWindow: this,
			getParams: function(){
				var record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
				if ( !record || !record.get('Person_id') ) {
					Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
					return false;
				}
				return {
					RegisterType: 'palliat',
					PersonRegisterType_SysNick: record.get('PersonRegisterType_SysNick'),
					EvnNotifyBase_id: record.get('EvnNotifyBase_id'),
					MedPersonal_id: record.get('MedPersonal_id'),
					Lpu_id: record.get('Lpu_did')
				};
			}.createDelegate(this),
			onCreate: function(menu){
				var a = this.RootViewFrame.getAction('palliat_person_register_not_include');
				a.items[0].menu = menu;
				a.items[1].menu = menu;
			}.createDelegate(this),
			callback: function(){
				this.RootViewFrame.getAction('action_refresh').execute();
			}.createDelegate(this)
		});
	},
	/** Включить в регистр
	 */
	personRegisterInclude: function() {
		var grid = this.RootViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') ) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		sw.Promed.personRegister.doInclude({
			EvnNotifyBase_id: record.get('EvnNotifyBase_id'),
			Person_id: record.get('Person_id'),
			PersonRegisterType_SysNick: this.PersonRegisterType_SysNick,
			Diag_id: record.get('Diag_id') || null,
			MorbusType_SysNick: this.MorbusType_SysNick,
			Morbus_id: record.get('Morbus_id') || null,
			MedPersonal_id: record.get('MedPersonal_id'),
			Lpu_did: record.get('Lpu_did'),
			ownerWindow: this,
			question: langs('Включить данные по выбранному Направлению в регистр?'),
			callback: function () {
				grid.getStore().reload();
			}
		});
	},
	/** Удаление направления/извещения
	 */
	delEvnNotifyRegister: function() {
		var me = this;
		var grid = me.RootViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('PalliatNotify_id')) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var delMessage = langs('Удалить выбранное извещение?');
		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: delMessage,
			title: langs('Подтверждение'),
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId) {
					var loadMask = new Ext.LoadMask(me.RootViewFrame.getEl(), {msg:langs('Удаление...')});
					loadMask.show();

					Ext.Ajax.request({
						url: '/?c=EvnNotifyPalliat&m=delete',
						params: {
							PalliatNotify_id: record.get('PalliatNotify_id')
						},
						failure: function(response, options) {
							loadMask.hide();
							Ext.Msg.alert(langs('Ошибка'), langs('При удалении произошла ошибка!'));
						},
						success: function(response, action) {
							loadMask.hide();
							if (response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success) {
									grid.getStore().reload();
								}
							} else {
								Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при удалении! Отсутствует ответ сервера.'));
							}
						}
					});
				} else {
					if (grid.getStore().getCount()>0) {
						grid.getView().focusRow(0);
					}
				}
			}
		});
		return true;
	},

	checkAllowCreate: function(opts) {
		Ext.Ajax.request({
			url: '/?c=EvnNotifyPalliat&m=checkAllowCreate',
			params: {
				Person_id: opts.Person_id
			},
			callback: function(options, success, response) {
				var responseObj = Ext.util.JSON.decode(response.responseText);

				if (responseObj.isAllowCreate !== undefined) {
					opts.callback(responseObj.isAllowCreate);
				}
			}
		});
	},

	/** Направление на включение в регистр
	 */
	openEvnNotifyRegisterIncludeWindow: function(action) {
		var win = getWnd('swEvnNotifyRegisterPalliatEditWindow');
		var grid = this.RootViewFrame.getGrid();
		if (!String(action).inlist(['add','edit','view'])) {
			return;
		}
		if (win.isVisible()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: langs('Окно уже открыто'),
				title: ERR_WND_TIT
			});
			return;
		}

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();

			if (!record || Ext.isEmpty(record.get('PalliatNotify_id'))) {
				return;
			}

			win.show({
				action: action,
				formParams: {
					PalliatNotify_id: record.get('PalliatNotify_id')
				},
				callback: function() {
					grid.getStore().reload();
				}
			});
		} else {
			var createNotify = function(personData) {
				win.show({
					formParams: {
						PersonEvn_id: personData.PersonEvn_id,
						Server_id: personData.Server_id,
						Person_id: personData.Person_id
					},
					callback : function() {
						grid.getStore().reload();
					}
				});
			};

			getWnd('swPersonSearchWindow').show({
				onSelect: function(personData) {
					if (personData.Person_id > 0) {
						createNotify(personData);
					}
					getWnd('swPersonSearchWindow').hide();
				}.createDelegate(this)
			});
		}
	},
	emkOpen: function() {
		var grid = this.RootViewFrame.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}

		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			callback: function() {
				//grid.getStore().reload();
			}
		});
	}
});
