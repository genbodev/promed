/**
 * Панель льгот
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.SignalInfo.PersonPrivilegePanel', {
	extend: 'swPanel',
	title: 'ЛЬГОТЫ',
	btnAddClickEnable: true,
	allTimeExpandable: false,
	collapseOnOnlyTitle: true,
	onBtnAddClick: function(){
		this.openPersonPrivilegeEditWindow('add');
	},
	collapsed: true,
	setParams: function(params) {
		var me = this;

		me.Person_id = params.Person_id;
		me.Person_Birthday = params.Person_Birthday;
		me.Person_deadDT = params.Person_deadDT;
		me.Server_id = params.Server_id;
        me.userMedStaffFact = params.userMedStaffFact;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	loaded: false,
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	load: function() {
		var me = this;
		this.loaded = true;
		this.PersonPrivilegeGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonPrivilege: function() {
		var me = this;
		var PersonPrivilege_id = me.PersonPrivilegeGrid.recordMenu.PersonPrivilege_id;
		if (!PersonPrivilege_id) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext6.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					me.mask("Подождите, идет удаление...");
					Ext6.Ajax.request({
						success: function(response) {
							me.unmask();
							me.load();
						},
						failure: function() {
							me.unmask();
						},
						params: {
							PersonPrivilege_id: PersonPrivilege_id
						},
						url: C_PERS_PRIV_DEL
					});
				}
			},
			icon: Ext6.MessageBox.QUESTION,
			msg: langs('Внимание! Удаление льготы может повлиять на отчетные данные по количеству льготополучателей. Вы действительно желаете удалить запись о льготе?'),
			title: langs('Вопрос')
		});
	},
    openPersonPrivilegeEditWindow: function(action) {
		if (getGlobalOptions().person_privilege_add_source == 2) { //2 - Включение в регистр выполняется пользователем
			this._openPersonPrivilegeEditWindow(action);
		} else {
			if (action == 'add') {
				var params = new Object();
				params.action = action;
				params.Person_id = this.Person_id;
				params.userMedStaffFact = this.userMedStaffFact;
				getWnd('swPersonPrivilegeReqEditWindow').show(params);
			} else {
				sw.swMsg.alert(langs('Сообщение'), langs('Редактирование данных льготы не доступно, так как эта операция осуществляется только в ситуационном центре ЛЛО. Подайте запрос в ситуационный центр'));
			}
		}
	},
	_openPersonPrivilegeEditWindow: function(action) {
		var me = this;
		var params = new Object();

		params.callback = function(data) {
			if (!data || !data.PersonPrivilegeData)
			{
				return false;
			}

			me.load();
		};

		params.onHide = Ext6.emptyFn;
		params.Person_id =  me.Person_id;
		params.Server_id = me.Server_id;
		params.action = action;
		params.Person_Birthday = me.Person_Birthday;
		params.Person_deadDT = me.Person_deadDT;

		if (action != 'add') {
			var PersonPrivilege_id = me.PersonPrivilegeGrid.recordMenu.PersonPrivilege_id;
			if (!PersonPrivilege_id) {
				return false;
			}

			params.PersonPrivilege_id = PersonPrivilege_id;
		}
		//следующие 2 строки добавлена только для временного вызова формы добавления льготы в старом интерфейсе до завершения переноса новых функций
		params.ARMType = this.userMedStaffFact.ARMType;
		getWnd('swPrivilegeEditWindow').show(params);
		//getWnd('swPrivilegeEditWindowExt6').show(params);
	},
	initComponent: function() {
		var me = this;

		this.PersonPrivilegeGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			viewConfig: {
				minHeight: 33
			},
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function () {
						if (getRegionNick() == 'perm') {
							var PersonPrivilege_id = me.PersonPrivilegeGrid.recordMenu.PersonPrivilege_id;
							var index = me.PersonPrivilegeGrid.getStore().findBy(function (rec) {
								return (rec.get('PersonPrivilege_id') == PersonPrivilege_id);
							});
							var record = me.PersonPrivilegeGrid.getStore().getAt(index).data;
							if (record.PrivilegeType_Code == '508') {								//ДЛО Кардио
								getWnd('swPrivilegeConsentEditWindow').show({
									Person_id: me.Person_id,
									action: 'edit',
									PersonPrivilege_id: PersonPrivilege_id,
									Privilege_begDate: record.Privilege_begDate,
									Privilege_endDate: record.Privilege_endDate
								});
							} else {
								me.openPersonPrivilegeEditWindow('edit');
							}
						} else {
							me.openPersonPrivilegeEditWindow('edit');
						}
					}
				}, {
					text: 'Удалить запись',
					handler: function() {
						me.deletePersonPrivilege();
					}
				}]
			}),
			showRecordMenu: function(el, PersonPrivilege_id) {
				this.recordMenu.PersonPrivilege_id = PersonPrivilege_id;
				this.recordMenu.showBy(el);
			},
			cls: 'EmkGrid',
			padding: 10,
			columns: [{
				width: 50,
				header: 'Вид',
				dataIndex: 'ReceptFinance_Code',
				renderer: function(val, metaData, record) {
					switch(val) {
						case '1':
							return "<span class='lgot_fl' data-qtip='Федеральная льгота'>ФЛ</span>";
							break;
						case '2':
							return "<span class='lgot_rl' data-qtip='Региональная льгота'>РЛ</span>";
							break;
					}
					return '';
				}
			}, {
				width: 50,
				header: 'Код',
				dataIndex: 'PrivilegeType_Code'
			}, {
				flex: 1,
				minWidth: 100,
				header: 'Наименование, диагноз, документы о праве на льготу',
				dataIndex: 'PrivilegeType_Name',
                renderer: function(val, metaData, record) {
                    var value = '';
                    if (record.get('PersonPrivilege_id') > 0) {
                    	value += val;
                        value += !Ext6.isEmpty(record.get('Diag_Name')) ? '<br/>Диагноз: ' + record.get('Diag_Name') : '';
                        value += !Ext6.isEmpty(record.get('DocumentPrivilege_Data')) ? '<br/>' + record.get('DocumentPrivilege_Data') : '';
					}
                    return value;
                }
			}, {
				width: 100,
				header: 'Начало',
				renderer: Ext6.util.Format.dateRenderer('d.m.Y'),
				dataIndex: 'Privilege_begDate'
			}, {
				width: 100,
				header: 'Окончание',
				renderer: Ext6.util.Format.dateRenderer('d.m.Y'),
				dataIndex: 'Privilege_endDate'
			}, {
				width: 80,
				header: 'Отказ',
				renderer: sw.Promed.Format.checkColumn,
				dataIndex: 'Privilege_Refuse'
			}, {
				width: 80,
				header: 'Отказ на след. год',
				renderer: sw.Promed.Format.checkColumn,
				dataIndex: 'Privilege_RefuseNextYear'
			}, {
				width: 80,
				header: 'Причина закрытия',
				dataIndex: 'PrivilegeCloseType_Name'
			}, {
				width: 200,
				header: 'МО',
				dataIndex: 'Lpu_Name'
			}, {
				width: 40,
				dataIndex: 'PersonPrivilege_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonPrivilegeGrid.id + "\").showRecordMenu(this, " + record.get('PersonPrivilege_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonPrivilege_id', type: 'int' },
					{ name: 'PrivilegeType_Code', type: 'string' },
					{ name: 'PrivilegeType_Name', type: 'string' },
					{ name: 'Privilege_begDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'Privilege_endDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'Privilege_Refuse', type: 'string' },
					{ name: 'Privilege_RefuseNextYear', type: 'string' },
					{ name: 'ReceptFinance_Code', type: 'string' },
					{ name: 'Lpu_Name', type: 'string' },
					{ name: 'PrivilegeCloseType_Name', type: 'string' },
					{ name: 'DocumentPrivilege_Data', type: 'string' },
					{ name: 'Diag_Name', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Privilege&m=loadPersonPrivilegeList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonPrivilege_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonPrivilegeGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.openPersonPrivilegeEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});