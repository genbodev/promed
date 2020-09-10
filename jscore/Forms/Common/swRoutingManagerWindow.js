/**
* swRoutingManagerWindow - Маршрутизация и сферы ответственности МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Sharipov Fidan
* @version      11.2019

*/
sw.Promed.swRoutingManagerWindow = Ext.extend(sw.Promed.BaseForm, {
	modal: true,
	maximized: true,
	resizable: false,
	layout: 'border',
	closeAction: 'hide',
	title: langs('Маршрутизация и сферы ответственности МО'),
	RoutingProfile_id: null,
	RoutingMap_id: null,
	RoutingLevel_id: null,
	listeners: {
		hide: function () {
			this.RoutingProfileCombo.setValue(null);
			this.LpuGrid.removeAll();
			this.RoutingProfile_id = null;
			this.RoutingMap_id = null;
			this.RoutingLevel_id = null;

			this.LpuTree.loader.baseParams = {};
			this.LpuTree.root.setText('Профиль не выбран');
			this.LpuTree.root.reload();
		}
	},
	initComponent: function () {
		let wnd = this;

		// Уровни МО по маршрутизации
		wnd.LpuTree = new Ext.tree.TreePanel({
			title: 'Уровни МО по маршрутизации',
			loaded: false,
			width: 200,
			height: 200,
			autoScroll: true,
			split: true,
			//rootVisible: false,
			width: Ext.getBody().getWidth() / 6,
			region: 'west',
			root: {
				text: 'Профиль не выбран',
				nodeType: 'async',
				leaf: true,
			},
			uiProviders: {
				'default': Ext.tree.TreeNodeUI,
				tristate: Ext.tree.TreeNodeTriStateUI
			},
			loader: new Ext.tree.TreeLoader({
				url: '/?c=RoutingMap&m=loadTree',
				expanded: true
			}),
			listeners: {
				click: function (node, e) {
					let RoutingMap_id = null,
						RoutingLevel_id = null;
					if (!Ext.isEmpty(node.attributes.RoutingMap_id)) {
						RoutingMap_id = node.attributes.RoutingMap_id;
					}
					if (!Ext.isEmpty(node.attributes.RoutingLevel_id)) {
						RoutingLevel_id = node.attributes.RoutingLevel_id;
					}
					wnd.RoutingLevel_id = RoutingLevel_id;
					wnd.RoutingMap_id = RoutingMap_id;
					wnd.LpuGrid.reload();

					let addFlag = true;
					if (!Ext.isEmpty(wnd.RoutingProfileCombo.getValue())) {
						addFlag = false;
					}
					if (RoutingLevel_id == 4) {
						addFlag = true;
					}
					let addAction = wnd.LpuGrid.getAction('action_add');
					addAction.setDisabled(addFlag);
				}
			}
		});

		// Грид списка подчиненных МО
		let filterCombo = new sw.Promed.SwBaseLocalCombo({
			value: 2,
			editable: false,
			displayField:'name',
			valueField: 'id',
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'id', type: 'int' },
					{ name: 'name', type: 'string' }
				],
				data: [
					[0, langs('Все')],
					[2, langs('Актуальные')],
					[3, langs('Закрытые')]
				],
			}),
			listeners: {
				select: function (combo, record) {
					wnd.LpuGrid.reload();
				}
			}
		});
		wnd.LpuGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=RoutingMap&m=loadGrid',
			border: false,
			autoLoadData: false,
			saveAtOnce: false,
			saveAllParams: true,
			checkBoxWidth: 25,
			selectionModel: 'multiselect',
			showCountInTop: false,
			noSelectFirstRowOnFocus: true,
			useEmptyRecord: false,
			stringfields: [{
				name: 'RoutingMap_id'
			}, {
				name: 'number',
				header: '№',
				renderer: function (value, opt, record, rowIndex) {
					return rowIndex + 1;
				}
			}, {
				name: 'RoutingLevel_name',
				header: 'Уровень МО'
			}, {
				name: 'Lpu_Name',
				width: 500,
				header: 'Медицинская организация'
			}, {
				name: 'RoutingMap_begDate',
				header: 'Дата начала действия',
				width: 130
			}, {
				name: 'RoutingMap_endDate',
				header: 'Дата окончания действия',
				width: 150
			}],
			actions: [{
					name: 'action_add',
					disabled: true,
					handler: function () {
						if (Ext.isEmpty(wnd.RoutingProfile_id)) {
							sw.swMsg.alert(lang['oshibka'], 'Необходимо выбрать МО или профиль');
							return;
						}
						getWnd('swRoutingMapAddWindow').show({
							parentWnd: wnd,
							RoutingProfile_id: wnd.RoutingProfile_id,
							RoutingLevel_id: wnd.RoutingLevel_id,
							RoutingMap_pid: wnd.RoutingMap_id
						});
					}
				}, {
					name: 'action_delete',
					disabled: true,
					handler: function () {
						let records = wnd.LpuGrid.getGrid().getSelectionModel().getSelections();

						if (records.length != 1) {
							sw.swMsg.alert(lang['oshibka'], 'Необходимо выбрать запись');
							return;
						}

						let RoutingMap = records[0];
						getWnd('swRoutingMapDeleteWindow').show({
							parentWnd: wnd,
							RoutingMap: RoutingMap
						});
					}
				}, {
					name: 'action_save',
					disabled: true,
					hidden: true
				}, {
					name: 'action_refresh',
					hidden: true
				}, {
					name: 'action_print',
					hidden: true
				}, {
					name: 'action_view',
					hidden: true
				}, {
					name: 'action_edit',
					hidden: true
				}
			],
			tbar: new Ext.Toolbar({
				items: [
					'Показывать: ',
					filterCombo
				]
			}),
			reload: function () {
				if (Ext.isEmpty(wnd.RoutingProfileCombo.getValue())) return;
				let store = this.ViewGridStore;
				store.baseParams.RoutingProfile_id = wnd.RoutingProfile_id;
				store.baseParams.RoutingMap_pid = wnd.RoutingMap_id;
				store.baseParams.OnlyActive = filterCombo.getValue();
				this.loadData();
			},
			onMultiSelectionChangeAdvanced: function() {
				let delFlag = true,
					resFlag = true;
				let selected = this.getMultiSelections();
				if (selected.length == 1) {
					let endDT = selected[0].get('RoutingMap_endDate');
					if (Ext.isEmpty(endDT)) {
						delFlag = false;
					} else {
						endDT = new Date(Ext.util.Format.date(endDT, 'Y-m-d')).setHours(0, 0, 0, 0);
						let today = new Date().setHours(0, 0, 0, 0);
						if (endDT == today) resFlag = false;
					}
				}

				this.getAction('action_delete').setDisabled(delFlag);
				this.getAction('action_restore').setDisabled(resFlag);
			}
		});
		wnd.LpuGrid.ViewGridPanel.view = new Ext.grid.GridView({
			enableRowBody: true,
			getRowClass : function (record, index) {
				let cls = '';
				if(record.get('RoutingMap_endDate')){
					cls = 'x-grid-rowgray ';
				}
				return cls;
			},
			listeners: {
				rowupdated: function(view, first, record) {
					view.getRowClass(record);
				}
			}
		});
		let GridWrapperPanel = new Ext.Panel({
			title: 'Список подчиненных МО',
			layout: 'fit',
			region: 'center',
			items: [
				wnd.LpuGrid
			]
		});

		// Панель инструментов
		wnd.RoutingProfileCombo = new sw.Promed.SwBaseLocalCombo({
			fieldLabel: 'Профиль',
			displayField: 'RoutingProfile_name',
			valueField: 'RoutingProfile_id',
			emptyText: 'Выберите тип',
			allowBlank: false,
			anchor: '100%',
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'RoutingProfile_id'
				}, [
					{ name: 'RoutingProfile_id', type: 'int' },
					{ name: 'RoutingProfile_name', type: 'string' }
				]),listeners: {
					load: function (store, records) {
						let profileId = wnd.RoutingProfileCombo.getValue();
						if (Ext.isEmpty(profileId)) return;
						wnd.RoutingProfileCombo.setValue(profileId);
						wnd.RoutingProfileCombo.fireEvent('select', wnd.RoutingProfileCombo, store.getById(profileId));
					}
				},
				url: '/?c=RoutingProfile&m=loadProfileList'
			}),
			listeners: {
				render: function (combo) {
					combo.getStore().baseParams = {
						Region_id: getGlobalOptions().region.number
					};

					combo.getStore().reload();
				},
				select: function(combo, record) {
					let profileName = record.get('RoutingProfile_name');
					wnd.LpuTree.getRootNode().setText(profileName);
					wnd.LpuTree.getRootNode().select();

					wnd.RoutingProfile_id = record.get('RoutingProfile_id');
					wnd.RoutingMap_id = null;
					wnd.RoutingLevel_id = null;
					wnd.LpuTree.loader.baseParams.RoutingProfile_id = wnd.RoutingProfile_id;

					Ext.getCmp(wnd.id + 'actionRefresh').setDisabled(false);
					Ext.getCmp(wnd.id + 'actionDelete').setDisabled(!getGlobalOptions().superadmin);
					wnd.LpuGrid.getAction('action_add').setDisabled(false);
					
					wnd.LpuTree.root.reload();
					wnd.LpuGrid.reload();
				}
			}
		});

		let WindowToolbar = new Ext.Toolbar({
			items: [
				'Профиль: ',
				wnd.RoutingProfileCombo,
				'|',
				new Ext.Action({
					id: wnd.id + 'actionRefresh',
					text: 'Обновить',
					iconCls: 'refresh16',
					disabled: true,
					handler: function () {
						wnd.RoutingProfileCombo.fireEvent('select', wnd.RoutingProfileCombo, wnd.RoutingProfileCombo.store.getById(wnd.RoutingProfile_id));
					}
				}),
				new Ext.Action({
					text: 'Добавить',
					iconCls: 'add16',
					disabled: !getGlobalOptions().superadmin,
					handler: function () {
						getWnd('swRoutingProfileAddWindow').show({
							parentWnd: wnd
						});
					}
				}),
				new Ext.Action({
					id: wnd.id + 'actionDelete',
					text: 'Удалить',
					iconCls: 'delete16',
					disabled: true,
					handler: function () {
						let profileId = wnd.RoutingProfileCombo.getValue();
						if (Ext.isEmpty(profileId)) return;

						Ext.Msg.show({
							title: 'Удалить записи',
							msg: langs('Вы хотите удалить данный тип маршрутизации?'),
							buttons: Ext.Msg.YESNO,
							fn: function(btn) {
								if (btn !== 'yes') return;
								wnd.getLoadMask("Удаление записей").show();
								Ext.Ajax.request({
									params: {
										RoutingProfile_id: profileId
									},
									url: '/?c=RoutingProfile&m=delete',
									callback: function(options, success, response) {
										wnd.getLoadMask().hide();
										if (success) {
											wnd.RoutingProfileCombo.setValue(null);
											wnd.RoutingProfileCombo.store.reload();
											wnd.LpuTree.root.setText('Профиль не выбран');
											wnd.LpuTree.baseParams = {};
											wnd.LpuTree.root.reload();
											wnd.LpuGrid.getGrid().store.removeAll()
											
											wnd.RoutingMap_id = null;
											wnd.RoutingLevel_id = null;
											wnd.RoutingProfile_id = null;
										}
									}
								});
								
							},
							icon: Ext.MessageBox.QUESTION
						});
					}
				})
			]
		});

		Ext.apply(wnd, {
			tbar: WindowToolbar,
			items: [
				wnd.LpuTree,
				GridWrapperPanel
			],
			buttons: [{
					text: BTN_FRMCLOSE,
					iconCls: 'close16',
					handler: function () {
						wnd.hide();
					}
				}
			],
			buttonAlign: 'right'
		});
		sw.Promed.swRoutingManagerWindow.superclass.initComponent.apply(this, arguments);
		
	},
	show: function () {
		let wnd = this;

		wnd.LpuGrid.addActions({
			text: 'Отменить удаление',
			iconCls: 'refresh16',
			name: 'action_restore',
			disabled: true,
			handler: function () {
				let records = wnd.LpuGrid.getGrid().getSelectionModel().getSelections(),
					idList = [];
				for (let i = 0; i < records.length; i++) {
					if (Ext.isEmpty(records[i].get('RoutingMap_id'))) continue;
					idList.push(records[i].get('RoutingMap_id'));
				}

				if (idList.length != 1) {
					sw.swMsg.alert(lang['oshibka'], 'Необходимо выбрать запись');
					return;
				}

				Ext.Msg.show({
					title: 'Восстановить записи',
					msg: langs('Вы действительно хотите восстановить выбранные записи?'),
					buttons: Ext.Msg.YESNO,
					fn: function(btn) {
						if (btn !== 'yes') return;
						wnd.getLoadMask("Восстановление записей").show();
						Ext.Ajax.request({
							params: {
								RoutingMap_List: Ext.util.JSON.encode(idList)
							},
							url: '/?c=RoutingMap&m=restore',
							callback: function(options, success, response) {
								wnd.getLoadMask().hide();
								if (success) {
									wnd.LpuGrid.reload();
									wnd.LpuTree.root.reload();
								}
							}
						});
						
					},
					icon: Ext.MessageBox.QUESTION
				});
			}
		});
		sw.Promed.swRoutingManagerWindow.superclass.show.apply(this, arguments);
	},
	reloadAll: function () {
		this.LpuTree.root.reload();
		this.LpuGrid.reload();
		this.RoutingProfileCombo.getStore().reload();
	}
});