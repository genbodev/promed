/**
* swPersonPrivilegeViewWindow - окно просмотра льгот.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-18.06.2009
* @comment      Префикс для id компонентов PPVW (PersonPrivilegeViewWindow)
*
*
* Использует: окно редактирования удостоверения (swEvnUdostEditWindow)
*             окно редактирования льготы (swPrivilegeEditWindow)
*/

sw.Promed.swPersonPrivilegeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnUdost: function() {
		var current_window = this;
		var grid = current_window.findById('PPVW_EvnUdostGrid').getGrid();

		if (!grid || !grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var evn_udost_id = selected_record.get('EvnUdost_id');

		if ( !evn_udost_id )
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrano_udostoverenie']);
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes')
				{
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success)
							{
								grid.getStore().remove(selected_record);

								if (grid.getStore().getCount() == 0)
								{
									LoadEmptyRow(grid);
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else
							{
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_udostovereniya_lgotnika_voznikli_oshibki']);
							}
						},
						params: {
							EvnUdost_id: evn_udost_id
						},
						url: C_EVNUDOST_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_udostoverenie_lgotnika'],
			title: lang['vopros']
		});
	},
	deletePrivilege: function() {
		var current_window = this;
		var grid = current_window.findById('PPVW_PersonPrivilegeGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var lpu_id = selected_record.get('Lpu_id');
		var person_privilege_id = selected_record.get('PersonPrivilege_id');

		if ( !person_privilege_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_lgota']);
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(current_window.getEl(), {msg: "Подождите, идет удаление..."});
					loadMask.show();

					Ext.Ajax.request({
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								grid.getStore().remove(selected_record);

								if (grid.getStore().getCount() == 0) {
									LoadEmptyRow(grid);
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						},
						failure: function() {
							loadMask.hide();
						},
						params: {
							PersonPrivilege_id: person_privilege_id
						},
						url: C_PERS_PRIV_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Внимание! Удаление льготы может повлиять на отчетные данные по количеству льготополучателей. Вы действительно желаете удалить запись о льготе?'),
			title: langs('Вопрос')
		});
	},
	draggable: true,
	height: 450,
	id: 'PersonPrivilegeViewWindow',
	initComponent: function() {
        var _this = this;

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE,
				tooltip: lang['zakryit_okno_alt_+_z']
            }],
			items: [
                new sw.Promed.PersonInformationPanelShort({
                    id: 'PPVW_PersonInformationFrame',
                    region: 'north'
                }),
                new Ext.FormPanel({
                    border: false,
                    id: 'PPVW_PersonPrivilegePanel',
                    layout: 'border',
                    region: 'center',
                    //title: 'Регистр льготников',
                    items: [
                        new sw.Promed.ViewFrame({
                            autoExpandColumn: 'autoexpand_privilege',
                            autoExpandMin: 100,
                            tbar: false,
                            border: false,
                            autoLoadData: false,
                            stringfields: [
                                { name: 'PersonPrivilege_id', type: 'string', header: 'PersonPrivilege_id',  key: true },
                                { name: 'Person_id', type: 'string', header: 'Person_id',  hidden: true },
                                { name: 'Lpu_id', type: 'string', header: 'Lpu_id',  hidden: true },
                                { name: 'PersonEvn_id', type: 'string', header: 'PersonEvn_id',  hidden: true },
                                { name: 'Server_id', type: 'string', header: 'Server_id',  hidden: true },
                                { name: 'PrivilegeType_id', type: 'int', header: 'PrivilegeType_id',  hidden: true },
                                { name: 'ReceptFinance_id', type: 'int', header: 'ReceptFinance_id',  hidden: true },
                                { name: 'ReceptFinance_Code', type: 'int', header: 'ReceptFinance_Code',  hidden: true },
                                { name: 'PrivilegeType_Code', hidden: true },
                                { name: 'PrivilegeType_VCode', header: langs('Код'), sort: true , width: 100 },
                                { name: 'PrivilegeType_Name', header: langs('Категория'), id: 'autoexpand_privilege', sort: true , width: 100, renderer: function(v, p, r) {
                                	var value = '';
                                	if (r.get('PersonPrivilege_id') > 0) {
                                        value += v;
                                        value += !Ext.isEmpty(r.get('Diag_Name')) ? '<br/>Диагноз: ' + r.get('Diag_Name') : '';
									}
                                	return value;
								} },
                                { name: 'Privilege_begDate', header: langs('Начало'), type: 'date', sort: true , width: 100 },
                                { name: 'Privilege_endDate', header: langs('Окончание'), type: 'date', sort: true , width: 100 },
                                { name: 'Privilege_Refuse', header: langs('Отказ'), type: 'checkcolumn', sort: true , width: 100 },
                                { name: 'Privilege_RefuseNextYear', header: langs('Отказ на след.'), type: 'checkcolumn', sort: true , width: 100 },
                                { name: 'DocumentPrivilege_Data', header: langs('Документ о праве на льготу'), type: 'string', sort: true , width: 100 },
                                { name: 'PrivilegeCloseType_Name', header: langs('Причина закрытия'), type: 'string', sort: true , width: 100 },
                                { name: 'Diag_Name', hidden: true },
                                { name: 'Lpu_Name', header: langs('Организация'), sort: true , width: 100 }
                            ],
                            id: 'PPVW_PersonPrivilegeGrid',
                            onDblClick: function(){
                                if (!this.getAction('action_edit').isDisabled()) {
                                    _this.openPrivilegeEditWindow('edit');
                                }
                            },
                            onRowSelect: function(sm, rowIndex, record) {
								this.getAction('action_add').setDisabled(
									_this.viewOnly && !(getRegionNick() == 'krym' && isUserGroup('ChiefLLO'))
								);
								this.getAction('action_edit').disable();
								this.getAction('action_view').disable();
								this.getAction('action_delete').disable();

								if (!record || Ext.isEmpty(record.get('PersonPrivilege_id'))) {
									return;
								}

								var person_id = record.get('Person_id');
								var privilege_type_id = record.get('PrivilegeType_id');
								var cntPC = record.get('cntPC');
								var recept_finance_code = record.get('ReceptFinance_Code');
								var add_by_users_enabled = (getGlobalOptions().person_privilege_add_source == 2); //2 - Включение в регистр выполняется пользователями

								this.getAction('action_view').enable();

								if (_this.viewOnly) {
									if (getRegionNick() == 'krym' && isUserGroup('ChiefLLO') && cntPC > 0 && add_by_users_enabled) {
										this.getAction('action_edit').enable();
									}
								} else {
									if (!Ext.isEmpty(recept_finance_code) && add_by_users_enabled && (
										(recept_finance_code == 2 && isUserGroup(['SuperAdmin','ChiefLLO','LpuUser'])) ||
										(recept_finance_code != 2 && isUserGroup(['SuperAdmin','ChiefLLO']))
									)) {
										this.getAction('action_edit').enable();
									}
									if (isUserGroup(['SuperAdmin','ChiefLLO'])) {
										this.getAction('action_delete').enable();
									}
								}

								udostGrid = _this.findById('PPVW_EvnUdostGrid').getGrid();
								udostGrid.getStore().removeAll();
								udostGrid.getStore().load({
									params: {
										Person_id: person_id,
										PrivilegeType_id: privilege_type_id
									}
								});
                            },
                            region: 'center',
                            stripeRows: true,
                            actions: [
                                { name: 'action_add', hidden: false, handler: function(){ _this.openPrivilegeEditWindow('add')} },
                                { name: 'action_edit', hidden: false, handler: function(){ _this.openPrivilegeEditWindow('edit')} },
                                { name: 'action_view', handler: function(){ _this.openPrivilegeEditWindow('view')} },
                                { name: 'action_delete', hidden: false, handler: function(){ _this.deletePrivilege()} },
                                { name: 'action_refresh' },
                                { name: 'action_print' }
                            ],
                            dataUrl: C_PRIV_LOAD_LIST
                        }),
                        // второй грид
                        new sw.Promed.ViewFrame({
                            autoExpandColumn: 'autoexpand_privilege',
                            autoExpandMin: 100,
                            tbar: false,
                            border: false,
                            title: lang['vyidacha_udostovereniya_lgotnika'],
                            autoLoadData: false,
                            stringfields: [
                                { name: 'EvnUdost_id', header: 'PersonPrivilege_id',  key: true },
                                { name: 'Person_id', header: 'Person_id',  hidden: true },
                                { name: 'PersonEvn_id', header: 'PersonEvn_id',  hidden: true },
                                { name: 'Server_id', header: 'Server_id',  hidden: true },
                                { name: 'EvnUdost_setDate', header: lang['vyidano'], type: 'date', sort: true, width: 100 },
                                { name: 'EvnUdost_disDate', header: lang['zakryito'], type: 'date', sort: true, width: 100 },
                                { name: 'EvnUdost_Ser', type: 'string', header: lang['seriya']},
                                { name: 'EvnUdost_Num', type: 'string', header: lang['nomer']}
                            ],
                            id: 'PPVW_EvnUdostGrid',
                            onDblClick: function(){
                                _this.openPrivilegeEditWindow('edit');
                            },
                            onRowSelect: function(sm, rowIndex, record) {
								var evn_udost_id = record.get('EvnUdost_id'),
								    person_id = _this.personId,
								    server_id = record.get('Server_id');

								if (evn_udost_id && person_id && server_id >= 0) {
									this.getGrid().getTopToolbar().items.items[2].enable();
									if (_this.action != 'view') {
										this.getGrid().getTopToolbar().items.items[1].enable();
										this.getGrid().getTopToolbar().items.items[3].enable();
									}
								} else {
									this.getGrid().getTopToolbar().items.items[1].disable();
									this.getGrid().getTopToolbar().items.items[2].disable();
									this.getGrid().getTopToolbar().items.items[3].disable();
								}
                            },
			                region: 'south',
                            stripeRows: true,
                            actions: [
                                { name: 'action_add', hidden: false, handler: function(){ _this.openEvnUdostEditWindow('add')} },
                                { name: 'action_edit', hidden: false, handler: function(){ _this.openEvnUdostEditWindow('edit')} },
                                { name: 'action_view', handler: function(){ _this.openEvnUdostEditWindow('view')} },
                                { name: 'action_delete', hidden: false, handler: function(){ _this.deleteEvnUdost()} },
                                { name: 'action_refresh' },
                                { name: 'action_print' }
                            ],
                            dataUrl: C_EVNUDOST_LOADLIST
                        })
                    ]
                })
            ]
		});

		sw.Promed.swPersonPrivilegeViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('PersonPrivilegeViewWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 450,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	openEvnUdostEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view') {
			return false;
		}

		var current_window = this;

		if (getWnd('swEvnUdostEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_udostovereniya_uje_otkryito']);
			return false;
		}

		var evn_udost_grid = current_window.findById('PPVW_EvnUdostGrid').getGrid(),
		privilege_grid = current_window.findById('PPVW_PersonPrivilegeGrid').getGrid(),
		params = {};

		params.action = action;
		params.callback = function(data) {
			if (!data || !data.EvnUdostData)
			{
				evn_udost_grid.getStore().reload();
			}
			else
			{
				// Добавить или обновить запись в evn_udost_grid
				var record = evn_udost_grid.getStore().getById(data.EvnUdostData.EvnUdost_id);

				if (record)
				{
					// Обновление
					record.set('EvnUdost_disDate', data.EvnUdostData.EvnUdost_disDate);
					record.set('EvnUdost_Num', data.EvnUdostData.EvnUdost_Num);
					record.set('EvnUdost_Ser', data.EvnUdostData.EvnUdost_Ser);
					record.set('EvnUdost_setDate', data.EvnUdostData.EvnUdost_setDate);
					record.set('Person_id', data.EvnUdostData.Person_id);
					record.set('PersonEvn_id', data.EvnUdostData.PersonEvn_id);
					record.set('Server_id', data.EvnUdostData.Server_id);

					record.commit();
				}
				else
				{
					// Добавление
					if (evn_udost_grid.getStore().getCount() == 1 && !evn_udost_grid.getStore().getAt(0).get('EvnUdost_id'))
					{
						evn_udost_grid.getStore().removeAll();
					}

					evn_udost_grid.getStore().loadData([ data.EvnUdostData ], true);
				}
			}
		};

		if (action == 'add')
		{
			if ( !privilege_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var person_id = privilege_grid.getSelectionModel().getSelected().get('Person_id');
			var person_evn_id = privilege_grid.getSelectionModel().getSelected().get('PersonEvn_id');
			var privilege_type_id = privilege_grid.getSelectionModel().getSelected().get('PrivilegeType_id');
			var server_id = privilege_grid.getSelectionModel().getSelected().get('Server_id');

			if (person_id && person_evn_id && privilege_type_id && server_id >= 0)
			{
				params.onHide = Ext.emptyFn;
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.PrivilegeType_id = privilege_type_id;
				params.Server_id = server_id;

				getWnd('swEvnUdostEditWindow').show( params );
			}
		}
		else
		{
			if ( !evn_udost_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var selected_record = evn_udost_grid.getSelectionModel().getSelected();

			var evn_udost_id = selected_record.get('EvnUdost_id');
			var person_id = selected_record.get('Person_id');
			var person_evn_id = selected_record.get('PersonEvn_id');
			var privilege_type_id = selected_record.get('PrivilegeType_id');
			var server_id = selected_record.get('Server_id');

			if (evn_udost_id && person_id && person_evn_id && server_id >= 0)
			{
				params.EvnUdost_id = evn_udost_id;
				params.onHide = function() {
					evn_udost_grid.getView().focusRow(evn_udost_grid.getStore().indexOf(selected_record));
				};
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.Server_id = server_id;

				getWnd('swEvnUdostEditWindow').show( params );
			}
		}
    },
	openPrivilegeEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view')
		{
			return false;
		}

		var current_window = this;

		if (getWnd('swPrivilegeEditWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_lgotyi_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var privilege_grid = current_window.findById('PPVW_PersonPrivilegeGrid').getGrid();

		params.callback = function(data) {
			if (!data || !data.PersonPrivilegeData)
			{
				privilege_grid.getStore().reload();
			}
			else
			{
				var record = privilege_grid.getStore().getById(data.PersonPrivilegeData.PersonPrivilege_id);

				if (record)
				{
					// Обновление
					record.set('Person_id', data.PersonPrivilegeData.Person_id);
					record.set('PersonPrivilege_id', data.PersonPrivilegeData.PersonPrivilege_id);
					record.set('Privilege_begDate', data.PersonPrivilegeData.Privilege_begDate);
					record.set('Privilege_endDate', data.PersonPrivilegeData.Privilege_endDate);
					record.set('PrivilegeType_Code', data.PersonPrivilegeData.PrivilegeType_Code);
					record.set('PrivilegeType_VCode', data.PersonPrivilegeData.PrivilegeType_VCode);
					record.set('PrivilegeType_id', data.PersonPrivilegeData.PrivilegeType_id);
					record.set('PrivilegeType_Name', data.PersonPrivilegeData.PrivilegeType_Name);
					record.set('Server_id', data.PersonPrivilegeData.Server_id);
					record.set('ReceptFinance_id', data.PersonPrivilegeData.ReceptFinance_id);

					record.commit();

					privilege_grid.getStore().each(function(record) {
						record.set('Person_Birthday', data.PersonPrivilegeData.Person_Birthday);
						record.set('Person_Firname', data.PersonPrivilegeData.Person_Firname);
						record.set('Person_Secname', data.PersonPrivilegeData.Person_Secname);
						record.set('Person_Surname', data.PersonPrivilegeData.Person_Surname);

						record.commit();
					});
				}
				else
				{
					// Добавление
					if (privilege_grid.getStore().getCount() == 1 && !privilege_grid.getStore().getAt(0).get('PersonPrivilege_id'))
					{
						privilege_grid.getStore().removeAll();
					}

					privilege_grid.getStore().loadData([ data.PersonPrivilegeData ], true);
				}
			}
		};

		if (action == 'add')
		{
            params.action = action;
            params.onHide = Ext.emptyFn;
            params.Person_id =  current_window.personId;
            params.PersonEvn_id = current_window.personEvnId;
            params.Server_id = current_window.serverId;

			if (getGlobalOptions().person_privilege_add_source == 2) { //2 - Включение в регистр выполняется пользователем
                getWnd('swPrivilegeEditWindow').show(params);
			} else { //1 - Включение в регистр выполняется по запросу в ситуационный центр
				params.callback = Ext.emptyFn;
                getWnd('swPersonPrivilegeReqEditWindow').show(params);
			}
		}
		else
		{
			if ( !privilege_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var selected_record = privilege_grid.getSelectionModel().getSelected();

			var lpu_id = selected_record.get('Lpu_id');
			var person_id = selected_record.get('Person_id');
			var person_privilege_id = selected_record.get('PersonPrivilege_id');
			var privilege_end_date = selected_record.get('Privilege_endDate');
			var privilege_type_code = selected_record.get('PrivilegeType_Code');
			var server_id = selected_record.get('Server_id');
			if (action == 'edit')
			{
				var recept_finance_id = selected_record.get('ReceptFinance_id');
				/*switch (getGlobalOptions().region.nick)
				{
					case 'ufa':
						var is_feder_privilege  = (2 != recept_finance_id);
					break;
					default:
						var is_feder_privilege = (privilege_type_code > 0 && privilege_type_code < 250);
				}*/				
				var is_feder_privilege = (2 != recept_finance_id);
				if (is_feder_privilege || privilege_end_date.toString().length > 0 || (lpu_id != Ext.globalOptions.globals.lpu_id && !isSuperAdmin()))
				{
					//if(getGlobalOptions().region.nick != 'perm'||!isSuperAdmin())
					if(!(isSuperAdmin() || isUserGroup('minzdravdlo')))
					{
						action = 'view';
					}
				}
			}

			if (person_id && person_privilege_id && server_id >= 0)
			{
				params.action = action;
				params.onHide = function() {
					privilege_grid.getView().focusRow(privilege_grid.getStore().indexOf(selected_record));
				};
				params.Person_id = person_id;
				params.PersonPrivilege_id = person_privilege_id;
				params.Server_id = server_id;

				getWnd('swPrivilegeEditWindow').show( params );
			}
		}
	},
	personEvnId: null,
	personId: null,
	plain: true,
	refreshPrivilegeGrid: function() {
		this.findById('PPVW_PersonPrivilegeGrid').getGrid().getStore().removeAll();
		this.findById('PPVW_EvnUdostGrid').getGrid().getStore().removeAll();

		this.findById('PPVW_PersonPrivilegeGrid').getGrid().getStore().load({
			params: {
				Person_id: this.personId,
				Server_id: this.serverId
			}
		});
	},
	resizable: true,
	serverId: null,
	show: function() {
		sw.Promed.swPersonPrivilegeViewWindow.superclass.show.apply(this, arguments);
        var _this = this;
		this.onHide = Ext.emptyFn;
		this.personEvnId = null;
		this.personId = null;
		this.serverId = null;

		this.action = 'edit';

		/*this.restore();
		this.center();*/

		if ( !arguments[0] )
		{
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { _this.hide(); });
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}

		if (arguments[0].Person_id)
		{
			this.personId = arguments[0].Person_id;
		}

		if (arguments[0].PersonEvn_id)
		{
			this.personEvnId = arguments[0].PersonEvn_id;
		}

		if (arguments[0].Server_id >= 0)
		{
			this.serverId = arguments[0].Server_id;
		}

		this.findById('PPVW_PersonPrivilegeGrid').setReadOnly(this.action == 'view');
		this.findById('PPVW_EvnUdostGrid').setReadOnly(this.action == 'view');

		this.findById('PPVW_PersonInformationFrame').load({
			Person_id: this.personId,
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		this.refreshPrivilegeGrid();
	},
	title: WND_DLO_PERSONPRIV,
	width: 700
});
