/**
 * swUserEditWindow - окно просмотра и редактирования пользователей.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package DLO
 * @access public
 * @copyright Copyright (c) 2009 Swan Ltd.
 * @author Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @version 10.04.2009
 */

sw.Promed.swUserEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout : 'fit',
	width : 950,
	modal : true,
	resizable : false,
	draggable : false,
	buttonAlign : 'left',
	autoHeight : true,
	closeAction : 'hide',
	plain : true,
	id : 'UserEditWindow',
	listeners : {
		'hide' : function() {
			this.onWinClose();
			this.onWinClose2();
		}
	},
	onWinClose : function() {

	},
	onWinClose2: function() {
		// обнуляем сертификаты, для того, чтобы при открытии формы в режиме добавления они не отображались
		if ( this.Certs != null)
		{
			this.Certs = null;
		}
	},
	returnFunc : function(owner) {
	},
	submit: function() {
		var form = this.findById('user_edit_form').getForm();

		if (!form.isValid()) {
			sw.swMsg.alert('Ошибка заполнения формы',
					'Проверьте правильность заполнения полей формы.',
					function() {
						form.findField("Org_id").focus(true, 100)
					});
			return;
		}
		var wnd = this;
		var action = this.fields.action;
		var org_id = 0;
		if (this.findById('user_edit_form').getForm().findField('Org_id').getValue() != "")
			org_id = this.findById('user_edit_form').getForm().findField('Org_id').getValue();
		var params = {
			mode : action,
			org_id : org_id
		};

		if (action == 'copy') {
			params.mode = 'add';
		}

		if (this.fields.action == 'edit')
			params.login = this.fields.user_login;
		var grid = this.findById('GroupsGrid');
		if (grid.getStore().getCount() < 1) {
			sw.swMsg.alert('Ошибка заполнения формы',
					'Должна быть введена хоть одна группа.', function() {
						form.findField("groups_com").focus(true, 100)
					});
			return;
		}

		//[gabdushev task#5019]
		// TODO: Такой же кусок встречается в форме редактирования пользователей аптек. Требует рефакторинга.
        var pass = form.findField("pass").getValue();
		var passwordWasChanged = (pass!='#$&password#$??');
        if (passwordWasChanged) {
			var pass = form.findField("pass").getValue();
			if (!checkPassword(pass, false, form)) {
				return;
			}
        }
		//[/gabdushev task#5019]


		var groups = [];
		grid.getStore().each(function(r) {
			groups.push(r.data.Group_id);
		});
		params.groups = Ext.util.JSON.encode(groups);
		
		var groupsNames = [];
		grid.getStore().each(function(r) {
			groupsNames.push(r.data.Group_Name);
		});
		params.groupsNames = Ext.util.JSON.encode(groupsNames);

		var grid = this.OrgGrid;
		var orgs = [];
		grid.getStore().each(function(r) {
			orgs.push(r.data.Org_id);
		});
		params.orgs = Ext.util.JSON.encode(orgs);

		var certs = [];
		for(var k in wnd.Certs) {
			if (!Ext.isEmpty(wnd.Certs[k].cert_id)) {
				certs.push(wnd.Certs[k]);
			}
		}
		params.certs = Ext.util.JSON.encode(certs);

		wnd.getLoadMask(LOAD_WAIT_SAVE).show();
		params.formMode = this.formMode;

		// если грид с правами на армы не пуст, то сохраняем матрицу запретов на АРМы
		if (wnd.accessGridPanel.getGrid().getStore().getCount() > 0) {
			var deniedARMs = [];
			wnd.accessGridPanel.getGrid().getStore().each(function(rec) {
				if (rec && !rec.get('ArmAccess_HasAccess')) {
					deniedARMs.push(rec.get('ArmAccess_Params'));
				}
			});

			params.deniedARMs = Ext.util.JSON.encode(deniedARMs);
		}

		form.submit({
			params : params,
			success : function(form, action) {
				wnd.getLoadMask().hide();
				wnd.hide();
				wnd.returnFunc(wnd.owner);
			},
			failure : function(form, action) {
				wnd.getLoadMask().hide();
				sw.swMsg.alert("Ошибка", action.result.Error_Msg);
			}
		});
	},
	filterGroups: function(rec) {

		var gname = rec.get('Group_Name');
		// бэд практикс, но чо поделать :-(
		var wnd = Ext.getCmp('UserEditWindow');

		if (
			wnd.LabGroups
			&& getGlobalOptions().lpu_isLab == 2
			&& isLpuAdmin()
			&& !isSuperAdmin()
		){
			return gname.inlist(this.LabGroups);
		} else {
			if (gname == 'RegOperPregnRegistry') {
				return isSuperAdmin();
			} else if (gname == 'OperPregnRegistry') {
				return (isSuperAdmin() || isLpuAdmin());
			} else if (gname == 'SuicideRegistry') {
				return isSuperAdmin();
			} else if (gname == 'IPRARegistryEdit' && getRegionNick() != 'ufa') {
				return isSuperAdmin();
			} else if (gname == 'editorperiodics') {
				return (isSuperAdmin()||(getRegionNick().inlist(['ekb','pskov','buryatiya'])&&isLpuAdmin()));
			} else if (gname == 'EduUser') {
				var isEduOrg = (wnd.isEduOrg) ? wnd.isEduOrg : false;
				return (isSuperAdmin() && isEduOrg);
			} else if (getRegionNick()=='astra' && gname.inlist(['smpvr','smpreg','smpdispatchdirect','SMPDispatchDirections','smpdispatchcall','SMPCallDispath','smpheadduty','smpheadbrig','smpadmin','SMPAdmin','smpdispatchstation','SMPMedServiceOper','PPDMedServiceOper'])) {
				return isUserGroup(['superadmin','smpadmin']) || !isUserGroup('lpuadmin');
			} else if (gname == 'RoutingManger') {
				return isSuperAdmin();
			} else if (gname == 'EcoRegistryRegion') {
				return isSuperAdmin();					
			}else if (gname == 'VIPRegistry') { //27.02.2019 - видимость группы VIPPeron
				return isSuperAdmin();
			}else if (gname == 'EditingMES') {
				return (isUserGroup(['superadmin','lpuadmin']));
			}else if (gname == 'Orphan') { 
				return isSuperAdmin();
			}else if (gname == 'NarkoRegistry') { 
				return isSuperAdmin();
			}
			return true;
		}
	},
	title : 'Редактирование: Пользователь',
	show: function() {
		sw.Promed.swUserEditWindow.superclass.show.apply(this, arguments);
		this.LabGroups = null;
		this.isEduOrg = null;

		if (arguments[0]) {
			if (arguments[0].callback)
				this.returnFunc = arguments[0].callback;
			if (arguments[0].owner)
				this.owner = arguments[0].owner;
			if (arguments[0].fields) {
				this.fields = arguments[0].fields;
			} else {
				this.fields = {};
			}

			if (arguments[0].onClose) {
				this.onWinClose = arguments[0].onClose;
			} else {
				this.onWinClose = function() {};
			}

			this.formMode = arguments[0].formMode || null;
		}

		if (this.formMode == 'orgaccess') {
			sw.swMsg.alert('Внимание', 'Чтобы разрешить доступ в систему организации нужно завести хотя бы одного пользователя');
		}

		var form = this.findById('user_edit_form').getForm();
		form.reset();
		this.findById('GroupsGrid').getStore().removeAll();

		if (form.findField('groups_com').getStore().getCount() == 0) {
			form.findField('groups_com').setBaseFilter(this.filterGroups);
			form.findField('groups_com').getStore().load();
		} else {
			this.LabGroups = ['LpuUser', 'LpuAdmin', 'RegistryUserReadOnly', 'RegistryUser', 'LpuCadrView'];
			form.findField('groups_com').setBaseFilter(this.filterGroups);
		}

		var wnd = this;
		wnd.OrgGrid.getStore().removeAll();
		wnd.accessGridPanel.getGrid().getStore().removeAll();
		wnd.tabPanel.setActiveTab(1);
		wnd.tabPanel.setActiveTab(0);

		if (!isSuperAdmin() && !(this.fields && this.fields.action == 'add' && this.fields.org_id && parseInt(this.fields.org_id) > 0)) {
			// если не суперадмин и не задана организация то берем из globaloptions.
			this.fields.org_id = getGlobalOptions().org_id || null;
		}

		if (this.fields && this.fields.action == 'add' && this.fields.org_id && parseInt(this.fields.org_id) > 0) {
			this.fields.org_id = parseInt(this.fields.org_id);

			var combo = form.findField('Org_id');

			combo.getStore().load({
				params: {
					Object:'Org',
					Org_id: wnd.fields.org_id,
					Org_Name:''
				},
				callback: function() {
					combo.setValue(wnd.fields.org_id);
					combo.fireEvent('change', combo);

					if (wnd.fields.action == 'add') {
						var store = combo.getStore();
						var row = store.getAt(store.find('Org_id', new RegExp( '^' + combo.getValue() + '$')));
						var org_id = row.data.Org_id;
						var org_nick = row.data.Org_Nick;
						var OrgType_Name = row.data.OrgType_Name;
						var grid = wnd.OrgGrid;
						if (grid.getStore().find('Org_id', new RegExp('^' + org_id + '$')) > -1) {
							return;
						}
						grid.getStore().loadData([{
							Org_id : org_id,
							Org_Nick : org_nick,
							OrgType_Name: OrgType_Name
						}]);
						grid.getView().refresh();
						wnd.loadMedPersonalForOrgGrid();
					}
				}
			});
		}

		// Только суперадмин может изменять/добавлять организаций
		form.findField('Org_id').setDisabled( !isAdmin );

		if (this.fields && this.fields.action) {
			form.findField('action').setValue(this.fields.action);
			if (this.fields.action == 'view') {
				this.fields.readOnly = true;
			}
		}

		var action = this.fields.action;
		switch (action) {
			case 'add' :
				// Фокусируем на поле Логин
				form.findField('login').focus(100, true);
				this.setTitle('Пользователь: Добавление');
				form.findField('login').enable();
				this.enableEdit(true);
				break;
			case 'view' :
			case 'edit' :
			case 'copy' :
				// Фокусируем на поле Имя
				form.findField('firname').focus(100, true);
				if (this.fields.readOnly) {
					this.setTitle('Пользователь: Просмотр');
					this.enableEdit(false);
				} else {
					if (action == 'copy') {
						this.setTitle('Пользователь: Добавление');
					} else {
						this.setTitle('Пользователь: Редактирование');
						var neprikosaemy = ['SWNT_ADMIN1', 'SWNT_ADMIN2'];
						if (this.fields.user_login.toUpperCase().substr(0, 4) == 'SWNT' && neprikosaemy.indexOf(UserLogin) == -1) {
							// скрываем редактирование учеток
							this.enableEdit(false);
						} else {
							this.enableEdit(true);
						}
					}
				}

				wnd.getLoadMask(LOAD_WAIT).show();
				form.load({
					params : {
						user_login : wnd.fields.user_login
					},
					success : function(frm, act) {
						wnd.getLoadMask().hide();
						form.findField('Org_id').setDisabled( !isAdmin );
						if (action == 'copy') {
							form.findField('login').enable();
							form.findField('login').setValue('');
							form.findField('pass').setValue('');
							form.findField('desc').setValue('');
						} else {
							form.findField('login').disable();
						}

						var Data = Ext.util.JSON.decode(act.response.responseText);

						for(var gr_indx = 0; gr_indx < Data[0].Groups.length; gr_indx++)
						{
							if(Data[0].Groups[gr_indx].Group_Name == 'Communic')
								form.findField('groups_com').setDisabled(true);
						}
						
						wnd.findById('GroupsGrid').getStore().loadData(Data[0].Groups);
						wnd.OrgGrid.getStore().loadData(Data[0].Orgs);
						wnd.Certs = Data[0].Certs;

						if (!isSuperAdmin() && wnd.findById('GroupsGrid').getStore().findBy(function(rec) { return rec.get('Group_Name') == 'SuperAdmin'; }) > -1) {
							// для не суперадминов запрещаем редактировать суперадминов! (refs #13380)
							wnd.enableEdit(false);
						}

						// грузим врачей всех организаций из грида
						wnd.loadMedPersonalForOrgGrid();
						form.findField("blocked").setValue(Data[0].blocked == 1);
					},
					failure : function(frm, act) {
						wnd.getLoadMask().hide();
						sw.swMsg.alert('Ошибка',
								'Не удалось загрузить данные с сервера',
								function() {
									wnd.hide()
								});
					},
					url : C_USER_GETDATA
				});
				break;
		}
	},
	loadMedPersonalForOrgGrid: function() {
		// грузим врачей всех организаций из грида

		var orgs = [];
		this.OrgGrid.getStore().each(function(r) {
			if (!Ext.isEmpty(r.get('Org_id'))) {
				orgs.push(r.get('Org_id'));
			}
		});

		var Org_ids = Ext.util.JSON.encode(orgs);
		var form = this.findById('user_edit_form').getForm();

		form.findField('MedPersonal_id').getStore().load(
		{
			params:
			{
				IsDlo: 0,
				Org_ids: Org_ids
			},
			callback: function()
			{
				form.findField('MedPersonal_id').setValue(form.findField('MedPersonal_id').getValue());
			}
		});
	},
	loadAccessGrid: function() {
		var wnd = this;
		var base_form = wnd.mainPanel.getForm();
		var Orgs = [];
		wnd.OrgGrid.getStore().each(function(r) {
			if (!Ext.isEmpty(r.get('Org_id'))) {
				Orgs.push(r.get('Org_id'));
			}
		});

		var Groups = [];
		wnd.findById('GroupsGrid').getStore().each(function(r) {
			if (!Ext.isEmpty(r.get('Group_Name'))) {
				Groups.push(r.get('Group_Name'));
			}
		});

		// прогрузить грид запретов, если ещё не загружен
		if (wnd.accessGridPanel.getGrid().getStore().getCount() < 1 /*&& !Ext.isEmpty(base_form.findField('MedPersonal_id').getValue())*/) {
			var params = {
				login: base_form.findField('login').getValue(),
				MedPersonal_id: base_form.findField('MedPersonal_id').getValue(),
				Orgs: Ext.util.JSON.encode(Orgs),
				Groups: Ext.util.JSON.encode(Groups)
			};
			wnd.accessGridPanel.loadData({ params: params, globalFilters: params });
		}
	},
	initComponent : function() {
		var wnd = this;

		this.OrgGrid = new Ext.grid.GridPanel({
			style : 'margin: 0px 13px 5px 0px; border: 1px solid #666',
			loadMask : true,
			autoExpandColumn : 'autoexpand',
			autoExpandMax : 2000,
			height : 60,
			store : new Ext.data.JsonStore({
				fields : [{
							name : 'Org_id'
						}, {
							name : 'Org_Nick'
						}, {
							name : 'OrgType_Name'
						}],
				autoLoad : false,
				url : C_USER_GETDATA
			}),
			columns : [{
				dataIndex : 'Org_id',
				hidden : true,
				hideable : false
			}, {
				id : 'autoexpand',
				header : "Наименование",
				sortable : true,
				dataIndex : 'Org_Nick'
			}, {
				header : "Тип",
				width: 100,
				sortable : true,
				dataIndex : 'OrgType_Name'
			}],
			sm : new Ext.grid.RowSelectionModel({
				singleSelect : true
			}),
			enableKeyEvents : true,
			listeners : {
				'keydown' : function(e) {
					if (e.getKey() == Ext.EventObject.DELETE) {
						e.stopEvent();
						Ext.getCmp("LpuDelButton").handler();
					}
				}
			}
		});

		this.mainPanel = new Ext.form.FormPanel({
			region: 'center',
			frame : true,
			autoHeight : true,
			labelAlign : 'right',
			id : 'user_edit_form',
			labelWidth : 125,
			buttonAlign : 'left',
			//bodyStyle : 'padding: 5px',
			url : C_USER_SAVEDATA,
			reader : new Ext.data.JsonReader({
				success : function() {
					alert('All Right!')
				}
				}, [{
					name : 'Org_id'
				}, {
					name : 'pmUser_id'
				}, {
					name : 'id'
				}, {
					name : 'login'
				}, {
					name : 'login_emias'
				}, {
					name : 'pass'
				}, {
					name : 'marshserial'
				}, {
					name : 'swtoken'
				}, {
					name : 'swtoken_enddate'
				}, {
					name : 'blocked'
				}, {
					name : 'firname'
				}, {
					name : 'secname'
				}, {
					name : 'surname'
				}, {
					name : 'name'
				}, {
					name : 'desc'
				}, {
					name : 'email'
				}, {
					name: 'parallel_sessions'
				}, {
					name : 'MedPersonal_id'
				}]),
			items : [{
				xtype : 'hidden',
				name : 'action'
			}, {
				xtype : 'hidden',
				name : 'pmUser_id'
			}, {
				layout : 'column',
				items : [{
					layout : 'form',
					items : [{
						xtype : 'sworgcombo',
						fieldLabel : 'Организация',
						tabIndex : 1701,
						displayField: 'Org_Nick',
						needOrgType: true,
						hiddenName : 'Org_id',
						width : 604,
						enableKeyEvents : true,
						listeners : {
							'keydown' : function(inp, e) {
								if (e.getKey() == Ext.EventObject.ENTER) {
									e.stopEvent();
									wnd.findById("OrgAddButton").handler();
								}
								else if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
									e.stopEvent();
									wnd.buttons[wnd.buttons.length - 1].focus();
								}
							}
						},
						onTrigger1Click: function() {
							if(!this.disabled){
								var combo = this;
								getWnd('swOrgSearchWindow').show({
									object: 'Org',
									onSelect: function(orgData) {
										if ( orgData.Org_id > 0 ) {
											combo.getStore().load({
												params: {
													Object:'Org',
													Org_id: orgData.Org_id,
													Org_Name:''
												},
												callback: function() {
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
							}}
					}]
				}, {
					layout : 'form',
					items : [{
						xtype : 'button',
						style : 'margin: 0px 2px 0px 3px;',
						id : 'OrgAddButton',
						text : BTN_GRIDADD,
						tabIndex : 1702,
						iconCls : 'add16',
						handler : function() {
							var combo = this.ownerCt.ownerCt.ownerCt.getForm().findField('Org_id');

							if (combo.getValue() == '' || combo.getValue() == null) {
								return;
							}
							var store = combo.getStore();
							var row = store.getAt(store.find('Org_id', new RegExp( '^' + combo.getValue() + '$')));
							var org_id = row.data.Org_id;
							var org_nick = row.data.Org_Nick;
							var OrgType_Name = row.data.OrgType_Name;

							wnd.isEduOrg = false;
							var form = wnd.findById('user_edit_form').getForm();

							// разрешаем группу "сотрудник образ. учреждения"
							var OrgType_SysNick = row.data.OrgType_SysNick;
							log(OrgType_SysNick);
							if (OrgType_SysNick.inlist(['preschool', 'secschool'])) {
								wnd.isEduOrg = true;
							}

							form.findField('groups_com').setBaseFilter(wnd.filterGroups);
							form.findField('groups_com').getStore().load();

							var grid = wnd.OrgGrid;
							if (grid.getStore().find('Org_id', new RegExp('^' + org_id + '$')) > -1) {
								return;
							}
							grid.getStore().loadData([{
								Org_id : org_id,
								Org_Nick : org_nick,
								OrgType_Name: OrgType_Name
							}], true);
							grid.getView().refresh();
							wnd.loadMedPersonalForOrgGrid();

							wnd.accessGridPanel.getGrid().getStore().removeAll(); // при изменении врача права надо перезагрузить
							wnd.loadAccessGrid();
						}
					}]
				}, {
					layout : 'form',
					items : [{
						xtype : 'button',
						text : BTN_GRIDDEL,
						tabIndex : 1703,
						iconCls : 'delete16',
						id : 'LpuDelButton',
						handler : function() {
							var combo = this.ownerCt.ownerCt.ownerCt.getForm().findField('Org_id');
							var grid = wnd.OrgGrid;
							if (!grid.getSelectionModel()
								.getSelected())
								return;
							var delIdx = grid.getStore().find('Org_id', new RegExp('^' + grid.getSelectionModel().getSelected().data.Org_id + '$'));
							if (delIdx >= 0)
								grid.getStore().removeAt(delIdx);
							grid.getView().refresh();
							wnd.loadMedPersonalForOrgGrid();

							var form = wnd.findById('user_edit_form').getForm();
							wnd.isEduOrg = false;
							form.findField('groups_com').setBaseFilter(wnd.filterGroups);

							wnd.accessGridPanel.getGrid().getStore().removeAll(); // при изменении врача права надо перезагрузить
							wnd.loadAccessGrid();
						}
					}]
				}]
			},
				this.OrgGrid,
				{
					layout : 'column',
					items : [{
						layout : 'form',
						columnWidth: .38,
						items : [{
							xtype : 'textfield',
							fieldLabel : 'Логин',
							tabIndex : 1704,
							allowBlank : false,
							anchor: '-10',
							name : 'login',
							maskRe : /[a-zA-Z0-9\_\.]/,
							regex : /[a-zA-Z0-9\_\.]+/
						}]
					}, {
						layout : 'form',
						hidden : true,
						items : [{
							xtype : 'textfield',
							fieldLabel : 'Идентификатор',
							hidden : true,
							name : 'id'
						}]
					}, {
						layout : 'form',
						columnWidth: .12,
						items : [{
							xtype : 'checkbox',
							hideLabel: true,
							name : 'blocked',
							tabIndex : 1705,
							labelSeparator: '',
							boxLabel : "Заблокирован"
						}]
					}, {
						layout : 'form',
						columnWidth: .5,
						items : [{
							xtype : 'textfield',
							fieldLabel : 'Идент. МАРШа',
							tabIndex : 1706,
							anchor: '-10',
							allowBlank : true,
							name : 'marshserial'
						}]
					}, {
						layout : 'form',
						columnWidth: .38,
						hidden: getRegionNick() != 'msk',
						items : [{
							xtype : 'textfield',
							fieldLabel : 'Логин ЕМИАС',
							tabIndex : 1707,
							name : 'login_emias'
						}]
					}]
				}, {
					layout : 'column',
					items : [{
						layout : 'form',
						columnWidth: .5,
						items : [{
							xtype : 'textfield',
							anchor: '-10',
							allowBlank : false,
							tabIndex : 1707,
							enableKeyEvents : true,
							fieldLabel : 'Временный пароль',
							inputType : 'password',
							name : 'pass'
						}]
					}]
				}, {
					layout : 'column',
					items : [{
						layout : 'form',
						items : [{
							xtype : 'textfield',
							readOnly: true,
							width: 300,
							fieldLabel : 'Токен',
							tabIndex : 1709,
							allowBlank : true,
							name : 'swtoken'
						}]
					}, {
						layout : 'form',
						labelWidth: 25,
						items : [{
							xtype : 'swdatefield',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width : 100,
							tabIndex : 1710,
							fieldLabel : 'До',
							allowBlank : true,
							name : 'swtoken_enddate'
						}]
					}, {
						layout : 'form',
						items : [{
							style: 'margin-left: 10px;',
							text: 'Сгенерировать',
							tabIndex : 1711,
							handler: function()
							{
								var form = wnd.findById('user_edit_form').getForm();
								form.findField('swtoken').setValue(SHA1(form.findField('login').getValue() + Math.floor(Math.random() * 10000)));
							},
							xtype: 'button'
						}]
					}]
				}, {
					border: false,
					style: 'margin-left: 130px;',
					layout: 'column',
					xtype: 'panel',
					items: [{
						layout: 'form',
						items: [{
							text: 'Сертификаты',
							handler: function() {
								var form = wnd.findById('user_edit_form').getForm();

								getWnd('swUserCertsWindow').show({
									certs: wnd.Certs,
									Person_Fio: form.findField("surname").getValue() + ' ' + form.findField("firname").getValue() + ' ' + form.findField("secname").getValue(),
									callback: function(certs) {
										wnd.Certs = certs;
									}
								});
							},
							tabIndex: 1712,
							xtype: 'button'
						}]
					}, {
						layout: 'form',
						style: 'margin-left: 10px;',
						items: [{
							text: 'Сертификаты РЭМД',
							handler: function() {
								var form = wnd.findById('user_edit_form').getForm();
								if (Ext.isEmpty(form.findField('pmUser_id').getValue())) {
									sw.swMsg.alert('Ошибка', 'Перед добавлением сертификатов РЭМД необходимо сохранить пользователя');
								} else {
									getWnd('swEMDCertificateViewWindow').show({
										pmUser_id: form.findField('pmUser_id').getValue()
									});
								}
							},
							tabIndex: 1712,
							xtype: 'button'
						}]
					}
					]
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					title: 'Сотрудник',
					//style: 'padding: 5px; margin: 5px;',
					items: [{
						layout: 'column',
						items: [{
							layout : 'form',
							labelWidth : 115,
							items : [{
								hiddenName: 'MedPersonal_id',
								width: 470,
								lastQuery: '',
								xtype: 'swmedpersonalallcombo',
								allowBlank: true,
								fieldLabel: 'Сотрудник',
								tabIndex: 1713,
								listeners: {
									change:  function(combo, nV, oV) {
										wnd.accessGridPanel.getGrid().getStore().removeAll(); // при изменении врача права надо перезагрузить
										wnd.loadAccessGrid();

										var form = wnd.mainPanel.getForm();
										if (combo.getValue()>0) {
											var fio = combo.getStore().data.get(combo.getValue()).data.MedPersonal_FIO.replace("  ", " ");
											var arr = fio.split(" ");
											form.findField("surname").setValue(arr[0]);
											form.findField("firname").setValue(arr[1]);
											form.findField("secname").setValue(arr[2]);

											var secname = '';
											if (!Ext.isEmpty(form.findField("secname").getValue())) {
												secname = ' ' + form.findField("secname").getValue();
											}

											form.findField("name").setValue(form.findField("surname").getValue() + ' '+form.findField("firname").getValue() + secname);
										}
									}
								}
							}]
						}]
					}]
				}, {
					layout : 'column',
					items : [{
						layout : 'form',
						columnWidth: .5,
						items : [{
							xtype : 'textfield',
							anchor: '-10',
							tabIndex : 1715,
							fieldLabel : 'Фамилия',
							allowBlank : false,
							name : 'surname'
						}, {
							xtype : 'textfield',
							anchor: '-10',
							tabIndex : 1716,
							fieldLabel : 'Имя',
							allowBlank : false,
							name : 'firname'
						}, {
							xtype : 'textfield',
							anchor: '-10',
							tabIndex : 1717,
							fieldLabel : 'Отчество',
							allowBlank : true,
							name : 'secname'
						}]
					}, {
						layout : 'form',
						columnWidth: .5,
						items : [{
							xtype : 'textfield',
							anchor: '-10',
							tabIndex : 1718,
							fieldLabel : 'Полное имя',
							name : 'name',
							disabled : true
						}, {
							xtype : 'textfield',
							anchor: '-10',
							tabIndex : 1719,
							allowBlank : true,
							fieldLabel : 'Эл. почта',
							name : 'email'
						}, {
							xtype : 'textfield',
							anchor: '-10',
							tabIndex : 1720,
							allowBlank : true,
							fieldLabel : 'Описание',
							name : 'desc'
						},]
					}]
				}, {
					layout : 'column',
					items : [{
						layout : 'form',
						items : [{
							xtype : 'swusersgroupscombo',
							id : 'groups_com',
							width : 341,
							lastQuery: '',
							tabIndex : 1721,
							hiddenName : 'groupscomboEdit',
							fieldLabel : 'Группы',
							enableKeyEvents : true,
							listeners : {
								'keydown' : function(inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										inp.ownerCt.ownerCt.ownerCt.ownerCt
											.findById("GroupsAddButton")
											.handler();
									}
								}
							}
						}]
					}, {
						layout : 'form',
						items : [{
							xtype : 'button',
							text : BTN_GRIDADD,
							tabIndex : 1722,
							style : 'margin: 0px 2px 0px 3px;',
							iconCls : 'add16',
							id : 'GroupsAddButton',
							handler : function() {
								var combo = this.ownerCt.ownerCt.ownerCt
									.getForm()
									.findField('groups_com');

								if (combo.getValue() == ''
									|| combo.getValue() == null)
									return;
								var store = combo.getStore();
								var row = store.getAt(store.find('Group_id', new RegExp('^' + combo.getValue() + '$')));
								var group_id = row.data.Group_id;
								var group_name = row.data.Group_Name;
								var group_desc = row.data.Group_Desc;
								var group_isonly = row.data.Group_IsOnly;
								var grid = combo.ownerCt.ownerCt.ownerCt
									.findById('GroupsGrid');
								if (grid.getStore().find('Group_id', new RegExp('^' + group_id + '$')) > -1)
									return;
								if (
									group_name == 'editorperiodics'
									&& !isSuperAdmin()
									&& ((!getRegionNick().inlist(['ekb', 'pskov','buryatiya'])) || (getRegionNick().inlist(['ekb', 'pskov','buryatiya']) && !isLpuAdmin()))
								) {
									sw.swMsg.alert('Ошибка',
										'Возможность добавления данной группы пользователю есть у пользователей с правами «Администратор ЛПУ», «Супер администратор СВАН».', function () {

										});
									return false;
								}
								if(['SuicideRegistry', 'RoutingManger'].indexOf(group_name) != -1  && !isSuperAdmin() ){
									sw.swMsg.alert('Ошибка', 'Возможность добавления данной группы пользователю есть у пользователей с правами «Суперадминистратор ЦОД».', function() {});
									return false;
								}
								
								if (group_name == 'Communic')
								{
									if(!isSuperAdmin())
									{
										sw.swMsg.alert('Ошибка', 'Возможность добавления данной группы пользователю есть только у Администратора ЦОД.', function() {});
										combo.clearValue();
										return false;
									}
									else
									{
										//Проверяем, есть ли организация с типом "МИРС".
										var org_grid = wnd.OrgGrid;
										var form = wnd.findById('user_edit_form').getForm();
										if(org_grid.getStore().getCount() == 0)
										{
											sw.swMsg.alert('Ошибка', 'Необходимо выбрать организацию с типом МИРС', function() {form.findField('Org_id').focus()});
											combo.clearValue();
											return false;
										}
										var mirs_org_exists = false;
										
										org_grid.getStore().each(function(record){
											if(record.get('OrgType_Name') == 'МИРС')
												mirs_org_exists = true;
										});
										
										if(!mirs_org_exists)
										{
											sw.swMsg.alert('Ошибка', 'Отсутствуют организации с типом МИРС', function() {form.findField('Org_id').focus()});
											combo.clearValue();
											return false;
										}
										org_grid.getStore().each(function(record){
											if(record.get('OrgType_Name') != 'МИРС')
												org_grid.getStore().remove(record);
										});
										combo.disable();
										grid.getStore().removeAll();
									}
								}
								if(getRegionNick().inlist['vologda', 'buryatiya'] && group_name == 'EditingMES' && !isLpuAdmin() && !isSuperAdmin()) {
									sw.swMsg.alert('Ошибка', 'Возможность добавления данной группы пользователю есть только у Администратора ЦОД или Администратора МО', function() {});
									combo.clearValue();
									return false;
								}

								// проверка единтсвенности
								if (grid.getStore().getCount() >= 1) {
									if (group_isonly == 2) {
										sw.swMsg.alert('Ошибка', 'Добавление учетной записи пользователя в группу "' + group_desc + '" невозможно, т.к. учетная запись добавлена в другие группы. Исключите учетную запись пользователя из других групп и повторите добавление.');
										return false;
									}

									var isOnlyGroup = false;
									grid.getStore().each(function (rec) {
										if (rec.get('Group_IsOnly') == 2) {
											isOnlyGroup = rec;
										}
									});
									if (isOnlyGroup) {
										sw.swMsg.alert('Ошибка', 'Добавление учетной записи пользователя в группу "' + group_desc + '" невозможно, т.к. учетная запись добавлена в группу "' + isOnlyGroup.get('Group_Desc') + '". Исключите учетную запись пользователя из группы "' + isOnlyGroup.get('Group_Desc') + '" и повторите добавление.');
										return false;
									}
								}

								grid.getStore().loadData([{
									Group_id : group_id,
									Group_Name : group_name,
									Group_Desc : group_desc,
									Group_IsOnly: group_isonly
								}], true);
								grid.getView().refresh();

								wnd.accessGridPanel.getGrid().getStore().removeAll(); // при изменении врача права надо перезагрузить
								wnd.loadAccessGrid();
							}
						}]
					}, {
						layout : 'form',
						items : [{
							xtype : 'button',
							text : BTN_GRIDDEL,
							tabIndex : 1723,
							iconCls : 'delete16',
							id : 'GroupsDelButton',
							handler : function() {
								var combo = this.ownerCt.ownerCt.ownerCt
									.getForm()
									.findField('groups_com');
								var grid = combo.ownerCt.ownerCt.ownerCt
									.findById('GroupsGrid');
								if (!grid.getSelectionModel()
									.getSelected())
									return;
								var delIdx = grid
									.getStore()
									.find(
										'Group_id',
										new RegExp('^' + grid
											.getSelectionModel()
											.getSelected().data.Group_id + '$'));
								if(grid.getSelectionModel().getSelected().data.Group_Name == 'Communic')
									combo.enable();
								if (delIdx >= 0)
									grid.getStore().removeAt(delIdx);
								grid.getView().refresh();

								wnd.accessGridPanel.getGrid().getStore().removeAll(); // при изменении врача права надо перезагрузить
								wnd.loadAccessGrid();
							}
						}]
					}]
				}, new Ext.grid.GridPanel({
					id : 'GroupsGrid',
					style : 'margin: 0px 13px 5px 0px; border: 1px solid #666',
					loadMask : true,
					autoExpandColumn : 'autoexpand',
					height : 100,
					store : new Ext.data.JsonStore({
						fields : [{
							name : 'Group_id'
						}, {
							name : 'Group_Name'
						}, {
							name : 'Group_Desc'
						}, {
							name : 'Group_IsOnly',
							type: 'int'
						}],
						autoLoad : false,
						url : C_USER_GETDATA
					}),
					columns : [{
						dataIndex : 'Group_id',
						hidden : true,
						hideable : false
					}, {
						header : "Группа",
						width : 130,
						sortable : true,
						dataIndex : 'Group_Name'
					}, {
						id : 'autoexpand',
						header : "Описание",
						sortable : true,
						dataIndex : 'Group_Desc'
					}],
					sm : new Ext.grid.RowSelectionModel({
						singleSelect : true
					}),
					enableKeyEvents : true,
					listeners : {
						'keydown' : function(e) {
							if (e.getKey() == Ext.EventObject.DELETE) {
								e.stopEvent();
								Ext.getCmp("GroupsDelButton").handler();
							}
						}
					}
				}),
				{
					layout : 'column',
					hidden: getRegionNick() == 'kz' ? true : false,
					items: [
						{
							layout : 'form',
							columnWidth: .2,
							items : {
								xtype : 'numberfield',
								allowNegative: false,
								allowDecimal: false,
								anchor: '-10',
								tabIndex : 1720,
								allowBlank : true,
								fieldLabel : 'Количество параллельных сеансов',
								name : 'parallel_sessions'
							}
						}
					]}
				],
				enableKeyEvents : true,
				keys : [{
				alt : true,
				fn : function(inp, e) {
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
						Ext.getCmp('UserEditWindow').hide();
						return false;
					}
					if (e.getKey() == Ext.EventObject.C) {
						Ext.getCmp('user_edit_form').buttons[0].handler();
						return false;
					}

					if (e.getKey() == Ext.EventObject.D) {
					}

					if (e.getKey() == Ext.EventObject.Y) {
					}
				},
				key : [Ext.EventObject.C, Ext.EventObject.J,
					Ext.EventObject.D, Ext.EventObject.Y],
				scope : this,
				stopEvent : false
			}]
		});

		this.accessGridPanel = new sw.Promed.ViewFrame({
			title: 'Список доступных пользователю АРМ',
			region: 'center',
			useEmptyRecord: false,
			autoLoadData: false,
			checkBeforeLoadData: function(store, options){

				//фильтруем армы для лабораторий (refs #85379)
				if(getGlobalOptions().lpu_isLab == 2){
					var groupsForLab = ['lab', 'pzm', 'reglab', 'lpuadmin', 'lpucadradmin', 'mstat'];

					options.callback = function(){
						this.filterBy(function(rec){
							return Ext.util.JSON.decode(rec.get('ArmAccess_Params')).at.inlist(groupsForLab);
						})
					};
				}

				return true;
			},
			dataUrl: '/?c=User&m=loadAccessGridPanel',
			saveRecord: function() {
				// сохранение будет вместе со всей формой.
			},
			stringfields:
			[
				{name: 'ArmAccess_id', type: 'int', header: 'ArmAccess_id', key: true, hidden: true},
				{name: 'ArmAccess_Params', type: 'string', header: 'ArmAccess_Params', hidden: true},
				{name: 'ArmAccess_WorkPlace', type: 'string', header: 'Место работы', width: 100, id: 'autoexpand'},
				{name: 'ArmAccess_HasAccess', type: 'checkcolumnedit', header: 'Доступ', width: 100}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			toolbar: false
		});

		this.tabPanel = new Ext.TabPanel({
			activeTab: 0,
			id: wnd.id + 'TabPanel',
			layoutOnTabChange: true,
			region: 'north',
			height: 530,
			border: false,
			items:
				[{
					title: "1. Основное",
					layout: 'border',
					id: "tab_main",
					items: [
						wnd.mainPanel
					]
				},
				{
					title: "2. Доступ к АРМ",
					layout: 'border',
					id: 'tab_access',
					items: [
						wnd.accessGridPanel
					]
				}],
			listeners:
			{
				tabchange: function(tab, panel)
				{
					switch (panel.id) {
						case 'tab_access':
							wnd.loadAccessGrid();
						break;
					}
				}
			}
		});

		Ext.apply(this, {
			buttons: [
				{
					text : BTN_FRMSAVE,
					tabIndex : 1724,
					iconCls : 'save16',
					handler : this.submit.createDelegate(this)
				}, {
					text : '-'
				}, HelpButton(this, 1725),
				{
					text : BTN_FRMCANCEL,
					iconCls : 'cancel16',
					tabIndex : 1726,
					handler : this.hide.createDelegate(this, []),
					onShiftTabAction:function () {
						if ( this.action != 'view' ) {
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					onTabAction:function () {
						var base_form = this.mainPanel.getForm();

						if ( !base_form.findField('Org_id').disabled ) {
							base_form.findField('Org_id').focus();
						}
						else if (this.action != 'view') {
							this.buttons[0].focus();
						}
					}.createDelegate(this)
				}
			],
			items : [ wnd.tabPanel ]
		});
		sw.Promed.swUserEditWindow.superclass.initComponent.apply(this,
				arguments);
	}
});