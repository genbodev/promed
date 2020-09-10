/**
* swLgotTreeViewWindow - окно просмотра и редактирования льгот.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
*               Stas Bykov aka Savage (savage1981@gmail.com)
* @version      28.04.2009
* @comment      Префикс для id компонентов LTVW (LgotTreeViewWindow)
*/

sw.Promed.swLgotTreeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	showMode: 'window',
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	collapsible: true,
	deletePrivilege: function() {
		var current_window = this;
		var grid = current_window.findById('LTVW_PersonPrivilegeGrid').getGrid();

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
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(current_window.getEl(), {msg: "Подождите, идет удаление..."});
					loadMask.show();

					Ext.Ajax.request({
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
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
			msg: lang['udalit_lgotu'],
			title: lang['vopros']
		});
	},
	disableAllButtons: function() {

        var GridToolBar = this.findById('LTVW_PersonPrivilegeGrid').getGrid().getTopToolbar().items;

        GridToolBar.items[0].disable();
        GridToolBar.items[1].disable();
        GridToolBar.items[2].disable();
        GridToolBar.items[3].disable();
        GridToolBar.items[4].disable();
        GridToolBar.items[5].disable();
	},
	draggable: true,
	height: 500,
	id: 'LgotTreeViewWindow',
	initComponent: function() {

        var _this = this;

		this.TreePanel = new Ext.tree.TreePanel({
			animate: true,
			autoScroll: true,
			border: false,
			enableDD: false,
			enableKeyEvents: true,
			id: 'LTVW_PrivilegeTypeTree',
			keys: [{
				key: [
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.returnValue = false;

					if ( Ext.isIE ) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var current_window = Ext.getCmp('LgotTreeViewWindow');
					var privilege_grid = current_window.findById('LTVW_PersonPrivilegeGrid').getGrid();

					switch ( e.getKey() ) {
						case Ext.EventObject.TAB:
							if ( privilege_grid.getStore().getCount() == 0 ) {
								LoadEmptyRow(privilege_grid);
							}

							if ( privilege_grid.getSelectionModel().getSelected() ) {
								var index = privilege_grid.getStore().indexOf(privilege_grid.getSelectionModel().getSelected())

								privilege_grid.getView().focusRow(index);
							}
							else {
								privilege_grid.getView().focusRow(0);
								privilege_grid.getSelectionModel().selectFirstRow();
							}
							break;
					}
				},
				stopEvent: true
			}],
			listeners: {
				'click': function(node, e) {
					if ( node.id == 'root' || Ext.isEmpty(node.id)) {
						return false;
					}

					if (getRegionNick() != 'saratov') {
						this.ownerCt.disableAllButtons();
					}

					_this.refreshPrivilegeGrid(node.id);
				},
				'expandnode': function(node) {
					if ( node.id == 'root' ) {
						this.getSelectionModel().select(node.firstChild);
						this.fireEvent('click', node.firstChild);
					}
				}
			},
			loader: new Ext.tree.TreeLoader({
				clearOnLoad: true,
				dataUrl: C_LGOT_TREE
			}),
			region: 'west',
			root: {
				draggable: false,
				id: 'root',
				text: langs('Региональные льготы')
			},
			split: true,
			title: langs('Категории льготы'),
			useArrows: true,
			width: 270
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand_privilege',
			autoExpandMin: 100,
			tbar: true,
			border: false,
			autoLoadData: false,
			stringfields: [
				{ name: 'PersonPrivilege_id', type: 'string', header: 'PersonPrivilege_id',  key: true },
				{ name: 'Person_id', type: 'string', header: 'Person_id',  hidden: true },
				{ name: 'Lpu_id', type: 'string', header: 'Lpu_id',  hidden: true },
				{ name: 'Lpu_did', type: 'string', header: 'Lpu_did',  hidden: true },
				{ name: 'Polis_Num', type: 'string', header: 'Polis_Num',  hidden: true },
				{ name: 'PersonEvn_id', type: 'string', header: 'PersonEvn_id',  hidden: true },
				{ name: 'PrivilegeType_id', type: 'int', header: 'PrivilegeType_id',  hidden: true },
				{ name: 'ReceptFinance_id', type: 'int', header: 'ReceptFinance_id',  hidden: true },
				{ name: 'ReceptFinance_Code', type: 'int', header: 'ReceptFinance_Code',  hidden: true },
				{ name: 'Server_id', type: 'int', header: 'Server_id',  hidden: true },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), sort: true , width: 150 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), sort: true , width: 150 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), sort: true , width: 150 },
				{ name: 'Person_Birthday', type: 'date', header: langs('Дата рождения'), sort: true , width: 150 },
				{ name: 'Privilege_begDate', type: 'date', header: langs('Начало'), sort: true , width: 150 },
				{ name: 'Privilege_endDate', type: 'date', header: langs('Окончание'), sort: true , width: 150 },
				{ name: 'Diag_Name', type: 'string', header: langs('Диагноз'), sort: true , width: 150 },
				{ name: 'DocumentPrivilege_Data', type: 'string', header: langs('Документ о праве на льготу'), sort: true , width: 150 },
				{ name: 'PrivilegeCloseType_Name', type: 'string', header: langs('Причина закрытия'), sort: true , width: 150 },
				{ name: 'Lpu_Nick', type: 'string', header: langs('Организация'),  hidden: false, sort: true },
				{ name: 'Person_Snils', type: 'string', renderer: snilsRenderer, header: langs('СНИЛС'),  hidden: false, sort: true, width: 150 },
				{ name: 'Polis_Ser', type: 'string', renderer: function(value, p, r){return r.data['Polis_Ser'] + ' ' + r.data['Polis_Num']}, header: langs('Полис ОМС'),  hidden: false, sort: true, width: 150 },
				{ name: 'PrivilegeType_Code', type: 'string', hidden: true },
				{ name: 'PrivilegeType_VCode', type: 'string', header: langs('Код'), sort: true , width: 150 }

			],
			id: 'LTVW_PersonPrivilegeGrid',
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					grid.ownerCt.ownerCt.openPrivilegeEditWindow('edit');
				}
			},
			onRowSelect: function(sm,index,record) {
				this.getAction('action_add').enable();
				this.getAction('action_edit').disable();
				this.getAction('action_view').disable();
				this.getAction('action_delete').disable();

				if (!record || Ext.isEmpty(record.get('PersonPrivilege_id'))) {
					return;
				}

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
			},
			region: 'center',
			stripeRows: true,
			actions: [
				{name: 'action_add', handler: function(){_this.openPrivilegeEditWindow('add')}},
				{name: 'action_edit', handler: function(){_this.openPrivilegeEditWindow('edit')}},
				{name: 'action_view', handler: function(){_this.openPrivilegeEditWindow('view')}},
				{name: 'action_delete', handler: function(){_this.deletePrivilege()}},
			],
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'count',
			dataUrl: C_LGOT_LIST
		});

		this.FormPanel = new Ext.FormPanel({
			border: false,
			id: 'LTVW_LgotRightPanel',
			layout: 'border',
			region: 'center',
			title: langs('Регистр льготников'),
			items: [{
				frame: true,
				autoHeight: true,
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 130,
				region: 'north',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: true,
						codeField: 'PrivilegeStateType_Code',
						displayField: 'PrivilegeStateType_Name',
						editable: false,
						fieldLabel: langs('Актуальность льготы'),
						hiddenName: 'PrivilegeStateType_id',
						hideEmptyRow: true,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (!newValue){
									combo.setValue(1);
								}
							},
							'select': function(combo, record, index) {
								_this.refreshPrivilegeGrid();
							}.createDelegate(this)
						},
						store: new Ext.data.SimpleStore({
							autoLoad: true,
							data: [
								[ 1, 1, langs('Действующие льготы') ],
								[ 2, 2, langs('Включая недействующие льготы') ]
							],
							fields: [
								{ name: 'PrivilegeStateType_id', type: 'int'},
								{ name: 'PrivilegeStateType_Code', type: 'int'},
								{ name: 'PrivilegeStateType_Name', type: 'string'}
							],
							key: 'PrivilegeStateType_id',
							sortInfo: { field: 'PrivilegeStateType_Code' }
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{PrivilegeStateType_Code}</font>&nbsp;{PrivilegeStateType_Name}',
							'</div></tpl>'
						),
						value: 1,
						valueField: 'PrivilegeStateType_id',
						width: 240,
						xtype: 'swbaselocalcombo'
					},{
						codeField: 'PrivilegeSearchType_Code',
						displayField: 'PrivilegeSearchType_Name',
						valueField: 'PrivilegeSearchType_id',
						hiddenName: 'PrivilegeSearchType_id',
						editable: false,
						fieldLabel: langs('Тип поиска'),
						hideEmptyRow: true,
						store: new Ext.data.SimpleStore({
							autoLoad: true,
							data: [
								[ 1, 1, langs('По текущему прикреплению') ],
								[ 2, 2, langs('По всем периодикам') ]
							],
							fields: [
								{ name: 'PrivilegeSearchType_id', type: 'int'},
								{ name: 'PrivilegeSearchType_Code', type: 'int'},
								{ name: 'PrivilegeSearchType_Name', type: 'string'}
							],
							key: 'PrivilegeSearchType_id',
							sortInfo: { field: 'PrivilegeSearchType_Code' }
						}),
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (!newValue){
									combo.setValue(1);
								}
							},
							'select': function(combo, record, index) {
								_this.refreshPrivilegeGrid();
							}.createDelegate(this)
						},
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{PrivilegeSearchType_Code}</font>&nbsp;{PrivilegeSearchType_Name}',
							'</div></tpl>'
						),
						value: 1,
						width: 240,
						xtype: 'swbaselocalcombo'
					}]
				},{
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: langs('МО прикрепления'),
						hiddenName: 'Lpu_prid',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								//Фильтр тип поиска имеет смысл только при выбранной МО
								_this.findById('LTVW_LgotRightPanel').getForm().findField('PrivilegeSearchType_id').setDisabled(!newValue);
								if (!newValue){
									combo.fireEvent('select', combo);
								}
							},
							'select': function(combo, record, index) {
								_this.refreshPrivilegeGrid();
							}.createDelegate(this)
						},
						width: 240,
						value: getGlobalOptions().lpu_id,
						disabled: !(isSuperAdmin() || isUserGroup('OuzUser') || isUserGroup('OuzAdmin') || isUserGroup('OuzSpec')),
						xtype: 'swlpucombo'
					}]
				}]
			}, this.GridPanel]
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: [
				this.TreePanel,
				this.FormPanel
			]
		});
		sw.Promed.swLgotTreeViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('LgotTreeViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.P:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	loadPrivilegeTypeTree: Ext.emptyFn,
	maximizable: true,
	maximized: false,
	modal: false,
	openPrivilegeEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var current_window = this;

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swPrivilegeEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_lgotyi_uje_otkryito']);
			return false;
		}

		var params = new Object();
		params.ARMType = this.ARMType;
		var privilege_grid = current_window.findById('LTVW_PersonPrivilegeGrid').getGrid();
		var privilege_tree = current_window.findById('LTVW_PrivilegeTypeTree');

		if ( !privilege_tree.getSelectionModel().getSelectedNode() ) {
			return false;
		}

		params.callback = function(data) {
			if ( !data || !data.PersonPrivilegeData ) {
				privilege_grid.getStore().reload();
			}
			else {
				// Добавить или обновить запись в privilege_grid
				var record = privilege_grid.getStore().getById(data.PersonPrivilegeData.PersonPrivilege_id);

				if ( record ) {
					if ( data.PersonPrivilegeData.PrivilegeType_id == privilege_tree.getSelectionModel().getSelectedNode().id ) {
						// Обновление
						record.set('Person_id', data.PersonPrivilegeData.Person_id);
						record.set('PersonEvn_id', data.PersonPrivilegeData.PersonEvn_id);
						record.set('PersonPrivilege_id', data.PersonPrivilegeData.PersonPrivilege_id);
						record.set('Privilege_begDate', data.PersonPrivilegeData.Privilege_begDate);
						record.set('Privilege_endDate', data.PersonPrivilegeData.Privilege_endDate);
						record.set('PrivilegeType_Code', data.PersonPrivilegeData.PrivilegeType_Code);
						record.set('PrivilegeType_id', data.PersonPrivilegeData.PrivilegeType_id);
						record.set('PrivilegeType_Name', data.PersonPrivilegeData.PrivilegeType_Name);
						record.set('Server_id', data.PersonPrivilegeData.Server_id);

						record.commit();

						privilege_grid.getStore().each(function(record) {
							if ( record.get('Person_id') == data.PersonPrivilegeData.Person_id && record.get('Server_id') == data.PersonPrivilegeData.Server_id ) {
								record.set('Person_Birthday', data.PersonPrivilegeData.Person_Birthday);
								record.set('Person_Firname', data.PersonPrivilegeData.Person_Firname);
								record.set('Person_Secname', data.PersonPrivilegeData.Person_Secname);
								record.set('Person_Surname', data.PersonPrivilegeData.Person_Surname);

								record.commit();
							}
						});
					}
					else {
						privilege_grid.getStore().remove(record);
					}
				}
				else {
					if ( data.PersonPrivilegeData.PrivilegeType_id == privilege_tree.getSelectionModel().getSelectedNode().id ) {
						// Добавление
						if ( privilege_grid.getStore().getCount() == 1 && !privilege_grid.getStore().getAt(0).get('PersonPrivilege_id') ) {
							privilege_grid.getStore().removeAll();
						}
						privilege_grid.getStore().loadData({ 'data': [ data.PersonPrivilegeData ]}, true);
					}
				}
			}
		};

		if ( action == 'add' ) {
            var add_by_users_enabled = (getGlobalOptions().person_privilege_add_source == 2); //2 - Включение в регистр выполняется пользователями

			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.action = action;
					params.onHide = function() {
						// TODO: Продумать использование getWnd в таких случаях
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.Person_id =  person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					params.PrivilegeType_id = privilege_tree.getSelectionModel().getSelectedNode().id;
					if(getRegionNick() == 'krym' && isUserGroup('ChiefLLO')){
						Ext.Ajax.request({
							callback: function(options, success, response) {
								if ( success ) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if(!(!Ext.isEmpty(response_obj) && !Ext.isEmpty(response_obj[0]) && response_obj[0].cntPC > 0)) {
										Ext.Msg.alert(lang['oshibka'],'Пациент не прикреплен к Вашему МО. Добавление льготы невозможно.');
										return false;
									} else {
                                        if (add_by_users_enabled) { //если пользователям разрашено доавлять льготы то открываем форму добавления льготы
                                            getWnd('swPrivilegeEditWindow').show(params);
                                        } else { //иначе открываем форму добавления запроса на включение в льготный регистр
                                            getWnd('swPersonPrivilegeReqEditWindow').show(params);
                                        }
                                    }
								} else {
									Ext.Msg.alert(lang['oshibka'],'Пациент не прикреплен к Вашему МО. Добавление льготы невозможно.');
									return false;
								}
							},
							params: {
								Person_id: person_data.Person_id,
								Lpu_id: getGlobalOptions().lpu_id
							},
							url: '/?c=Privilege&m=checkPersonCard'
						});
					} else {
                        if (add_by_users_enabled) { //если пользователям разрашено доавлять льготы то открываем форму добавления льготы
                            getWnd('swPrivilegeEditWindow').show(params);
                        } else { //иначе открываем форму добавления запроса на включение в льготный регистр
                            getWnd('swPersonPrivilegeReqEditWindow').show(params);
                        }
                    }
				},
				searchMode: 'all'
			});
		}
		else {
			if ( !privilege_grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = privilege_grid.getSelectionModel().getSelected();

			var lpu_id = selected_record.get('Lpu_id');
			var lpu_did = selected_record.get('Lpu_did');
			var person_id = selected_record.get('Person_id');
			var person_privilege_id = selected_record.get('PersonPrivilege_id');
			var privilege_end_date = selected_record.get('Privilege_endDate');
			var server_id = selected_record.get('Server_id');

			if ( action == 'edit' ) {
				if (!current_window.ARMType.inlist(['superadmin','minzdravdlo']) && (
					privilege_end_date.toString().length > 0 ||
					!getGlobalOptions().lpu_id.inlist([lpu_did,lpu_id])
				)){
					action = 'view';
				}
			}

			if ( person_id && person_privilege_id && server_id >= 0 ) {
				params.action = action;
				params.onHide = function() {
					// NGS: IF A PREVIOUSLY SELECTED RECORD EXISTS IN THE GRID --> SELECT IT, OTHERWISE NO
					// THERE IS A CASE WHEN indexOf(selected_record) RETURNS -1 => A RECORD DOESN'T EXISTS ANYMORE
					// AND focusRow FUNCTION GOES IN A FAULT
					privilege_grid.getStore().indexOf(selected_record) >= 0
					&& privilege_grid.getView().focusRow(privilege_grid.getStore().indexOf(selected_record));
					//privilege_grid.getView().focusRow(privilege_grid.getStore().indexOf(selected_record)); // NSG: WAS LIKE
				};
				params.Person_id = person_id;
				params.PersonPrivilege_id = person_privilege_id;
				params.Server_id = server_id;

				getWnd('swPrivilegeEditWindow').show( params );
			}
		}
	},
	plain: true,
	loadinginProcess: false,
	refreshPrivilegeGrid: function(id) {
		var current_window = this;

		if (this.loadinginProcess){
			return false;
		}

		this.loadinginProcess = true;

		var privilege_grid = current_window.findById('LTVW_PersonPrivilegeGrid').getGrid(),
			privilege_tree = current_window.findById('LTVW_PrivilegeTypeTree'),
			base_form = this.findById('LTVW_LgotRightPanel').getForm(),
			Lpu_prid = base_form.findField('Lpu_prid').getValue(),
			PrivilegeSearchType_id = base_form.findField('PrivilegeSearchType_id').getValue(),
			PrivilegeStateType_id = base_form.findField('PrivilegeStateType_id').getValue();

		if ( !privilege_tree.getSelectionModel().getSelectedNode() ) {
			return false;
		}

		privilege_grid.getStore().removeAll();
		privilege_grid.getStore().baseParams['PrivilegeType_id'] = id || privilege_tree.getSelectionModel().getSelectedNode().id;
		privilege_grid.getStore().baseParams['Lpu_prid'] = Lpu_prid;
		privilege_grid.getStore().baseParams['PrivilegeSearchType_id'] = PrivilegeSearchType_id;
		privilege_grid.getStore().baseParams['PrivilegeStateType_id'] = PrivilegeStateType_id;
		privilege_grid.getStore().load({
			callback: function(store, records, options) {
				current_window.loadinginProcess = false;
				privilege_grid.getSelectionModel().selectFirstRow();
			}
		});
	},
	resizable: false,
	show: function() {
		sw.Promed.swLgotTreeViewWindow.superclass.show.apply(this, arguments);

		this.ARMType = '';

		this.disableAllButtons();

		this.loadPrivilegeTypeTree();
		this.maximize();

		if(arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		if(this.ARMType.inlist(['spesexpertllo','adminllo'])) {
			this.findById('LTVW_PersonPrivilegeGrid').setActionHidden('action_add', true);
			this.findById('LTVW_PersonPrivilegeGrid').setActionHidden('action_delete', true);
			this.findById('LTVW_PersonPrivilegeGrid').setActionHidden('action_edit', true);
		} else {
			this.findById('LTVW_PersonPrivilegeGrid').setActionHidden('action_add', false);
			this.findById('LTVW_PersonPrivilegeGrid').setActionHidden('action_delete', false);
			this.findById('LTVW_PersonPrivilegeGrid').setActionHidden('action_edit', false);
		}
		this.findById('LTVW_PrivilegeTypeTree').root.expand();
	},
	title: WND_DLO_LGOTLIST,
	width: 800
});