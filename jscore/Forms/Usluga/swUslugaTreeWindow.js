/**
* swUslugaTreeWindow - форма просмотра списка услуг
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Быков Станислав
* @version      15.05.2014
* @prefix       UTW
* @comment      
*
* @input        Не имеет входящих параметров 
*/

sw.Promed.swUslugaTreeWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	id: 'UslugaTreeWindow',
	objectName: 'swUslugaTreeWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaTreeWindow.js',

	deleteLinkedUsluga: function() {
		var contGrid = this.uslugaContentsGrid.getGrid();
		var linkGrid = this.linkedUslugaGrid.getGrid();

		if ( !linkGrid.getSelectionModel().getSelected() || !linkGrid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
			return false;
		}
		else if ( !contGrid.getSelectionModel().getSelected() || !contGrid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
			return false;
		}

		var linkedRecord = linkGrid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление связанной услуги..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_svyazannoy_uslugi']);
						},
						params: {
							 UslugaComplex_id: linkedRecord.get('UslugaComplex_id')
							,UslugaComplex_pid: contGrid.getSelectionModel().getSelected().get('UslugaComplex_id')
						},
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg );
							}
							else {
								linkGrid.getStore().remove(linkedRecord);
							}

							if ( linkGrid.getStore().getCount() > 0 ) {
								linkGrid.getView().focusRow(0);
								linkGrid.getSelectionModel().selectFirstRow();
							}
						}.createDelegate(this),
						url: '/?c=UslugaComplex&m=deleteLinkedUslugaComplex'
					});
				}
				else {
					linkGrid.getView().focusRow(0);
					linkGrid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_svyazannuyu_uslugu'],
			title: lang['vopros']
		});
	},
	deleteUsluga: function() {
		var win = this;
		var grid = this.uslugaContentsGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
			return false;
		}

		var node = this.uslugaTree.getSelectionModel().selNode;
		if ( !node ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		if ((record.get('UslugaCategory_SysNick') == 'simple' || (getRegionNick() == 'buryatiya' && record.get('UslugaCategory_SysNick') == 'tfoms')) && record.get('UslugaComplexLevel_id') == 1) {
			// Удаляем группу услуг
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление группы услуг..." });
						loadMask.show();

						Ext.Ajax.request({
							failure: function(response, options) {
								loadMask.hide();
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_gruppyi_uslug_iz_spravochnika']);
							},
							params: {
								UslugaComplex_id: record.get('UslugaComplex_id')
							},
							success: function(response, options) {
								loadMask.hide();

								var path = node.getPath();
								win.uslugaTree.getLoader().load(node,function(tl,n){
									win.uslugaTree.selectPath(path);
									win.onTreeSelect(win.uslugaTree.getSelectionModel(), win.uslugaTree.getSelectionModel().selNode);
								});
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
				msg: lang['udalit_gruppu_uslug_iz_spravochnika'],
				title: lang['vopros']
			});
			return false;
		}

		if ( this.uslugaNavigationString.getLevel() == 0 ) {
			// @task https://redmine.swan.perm.ru/issues/81221
			// Саму услугу удалять нельзя
			return false;

			// Удаляем услугу
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление услуги..." });
						loadMask.show();

						Ext.Ajax.request({
							failure: function(response, options) {
								loadMask.hide();
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_uslugi_iz_spravochnika']);
							},
							params: {
								 UslugaComplex_id: record.get('UslugaComplex_id')
							},
							success: function(response, options) {
								loadMask.hide();

								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg );
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
				msg: lang['udalit_uslugu_iz_spravochnika'],
				title: lang['vopros']
			});
		}
		else {
			// Удаляем услугу из состава комплексной услуги
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление услуги из состава комплексной услуги..." });
						loadMask.show();

						Ext.Ajax.request({
							failure: function(response, options) {
								loadMask.hide();
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_uslugi_iz_sostava_kompleksnoy_uslugi']);
							},
							params: {
								 UslugaComplexComposition_id: record.get('UslugaComplexComposition_id')
							},
							success: function(response, options) {
								loadMask.hide();

								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg );
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
				msg: lang['udalit_uslugu_iz_sostava_kompleksnoy_uslugi'],
				title: lang['vopros']
			});
		}
	},
	addCloseFilterMenu: function(gridCmp){
		var form = this;
		var grid = gridCmp;

		if ( !grid.getAction('action_isclosefilter_'+grid.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: lang['vse'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = null;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_vse']);
							grid.getGrid().getStore().baseParams.isClose = null;
							grid.getGrid().getStore().baseParams.start = 0;
							//grid.getGrid().getStore().reload();
							form.uslugaContentsGrid.loadData({
								params: grid.getGrid().getStore().baseParams,
								globalFilters: grid.getGrid().getStore().baseParams
							});
						}
					}),
					new Ext.Action({
						text: lang['otkryityie'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 1;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_otkryityie']);
							grid.getGrid().getStore().baseParams.isClose = 1;
							grid.getGrid().getStore().baseParams.start = 0;
							//grid.getGrid().getStore().reload();
							form.uslugaContentsGrid.loadData({
								params: grid.getGrid().getStore().baseParams,
								globalFilters: grid.getGrid().getStore().baseParams
							});
						}
					}),
					new Ext.Action({
						text: lang['zakryityie'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 2;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_zakryityie']);
							grid.getGrid().getStore().baseParams.isClose = 2;
							grid.getGrid().getStore().baseParams.start = 0;
							//grid.getGrid().getStore().reload();
							form.uslugaContentsGrid.loadData({
								params: grid.getGrid().getStore().baseParams,
								globalFilters: grid.getGrid().getStore().baseParams
							});
						}
					})
				]
			});

			grid.addActions({
				isClose: 1,
				name: 'action_isclosefilter_'+grid.id,
				text: lang['pokazyivat_otkryityie'],
				menu: menuIsCloseFilter
			});
			grid.getGrid().getStore().baseParams.isClose = 1;
		}

		return true;
	},
	initComponent: function() {
		var form = this;

		var configActions = {
			action_AddUsluga: {
				handler: function() {
					form.openUslugaEditWindow('add');
				},
				iconCls: 'add16',
				text: BTN_GRIDADD,
				tooltip: BTN_GRIDADD_TIP
			},
			action_EditUsluga: {
				handler: function() {
					form.openUslugaEditWindow('edit');
				},
				iconCls: 'edit16',
				text: BTN_GRIDEDIT,
				tooltip: BTN_GRIDEDIT_TIP
			},
			action_ViewUsluga: {
				handler: function() {
					form.openUslugaEditWindow('view');
				},
				iconCls: 'view16',
				text: BTN_GRIDVIEW,
				tooltip: BTN_GRIDVIEW_TIP
			},
			action_DeleteUsluga: {
				handler: function() {
					form.deleteUsluga();
				},
				iconCls: 'delete16',
				text: BTN_GRIDDEL,
				tooltip: BTN_GRIDDEL_TIP
			}
		};

		form.Actions = new Object();

		for ( var key in configActions ) {
			form.Actions[key] = new Ext.Action(configActions[key]);
		}
		
		form.uslugaTree = new Ext.tree.TreePanel({
			animate: false,
			autoLoad: false,
			autoScroll: true,
			border: true,
			enableDD: false,
			getLoadTreeMask: function(MSG) {
				if ( MSG )  {
					delete(this.loadMask);
				}

				if ( !this.loadMask ) {
					this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
				}

				return this.loadMask;
			},
			id: 'UTW_UslugaTree',
			loader: new Ext.tree.TreeLoader( {
				listeners: {
					'beforeload': function (tl, node) {
						form.uslugaTree.getLoadTreeMask(lang['zagruzka_dereva_uslug']).show();

						tl.baseParams.level = node.getDepth();
						tl.baseParams.UslugaCategory_id = null;
						tl.baseParams.UslugaComplex_id = null;
						tl.baseParams.UslugaComplexLevel_id = null;
						tl.baseParams.Lpu_uid = null;

						if ( node.getDepth() > 0 ) {
							tl.baseParams.UslugaCategory_id = node.attributes.UslugaCategory_id;

							if ( node.attributes.object == 'UslugaComplex' ) {
								tl.baseParams.UslugaComplex_id = node.attributes.object_value.replace('ucom', '');
								tl.baseParams.UslugaComplexLevel_id = node.attributes.UslugaComplexLevel_id;
							}
						}
					},
					'load': function(node) {
						callback: {
							form.uslugaTree.getLoadTreeMask().hide();
						}
					}
				},
				dataUrl:'/?c=UslugaComplex&m=loadUslugaComplexTree'
			}),
			region: 'west',
			root: {
				nodeType: 'async',
				text: lang['uslugi'],
				id: 'root',
				expanded: true
			},
			rootVisible: false,
			selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
			split: true,
			title: lang['uslugi'],
			width: 400
		});
		
		// Двойной клик на ноде выполняет соответствующий акшен
		form.uslugaTree.on('dblclick', function(node, event) {
			var tree = node.getOwnerTree();
		});

		form.uslugaTree.on('click', function(node, e) {
			form.onTreeClick(node, e);
		});

		// функция выбора элемента дерева
		form.onTreeClick = function(node, e) {
			for ( key in form.Actions ) {
				if ( typeof form.Actions[key] == 'object' ) {
					form.Actions[key].hide();
				}
			}

			var lvl = node.getDepth();

			switch ( lvl ) {
				case 0:
					// form.Actions.action_NoAction.show(); 
				break;

				case 1:
					// form.Actions.action_NoAction.show();
				break;

				case 2:
					// form.Actions.action_NoAction.show(); 
				break;

				default:
					// form.Actions.action_NoAction.show(); 
				break;
			}
		};

		form.uslugaTree.getSelectionModel().on('selectionchange', function(sm, node) {
			form.onTreeSelect(sm, node);
		});

		form.uslugaContentsGrid = new sw.Promed.ViewFrame( {
			actions: [
				{ name: 'action_add', handler: function() { form.openUslugaEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { form.openUslugaEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { form.openUslugaEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { form.deleteUsluga(); } },
				{ name: 'action_refresh', disabled: false }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=UslugaComplex&m=loadUslugaContentsGrid',
			// editformclassname: 'swUslugaTreeEditWindow',
			// height: 300,
			id: form.id + 'UslugaContentsGrid',
			object: 'UslugaComplex',
			onDblClick: function(grid, number, object){
				this.onEnter();
			},
			onEnter: function() {
				// Прогрузить состав выбранной услуги
				if ( !form.uslugaContentsGrid.getGrid().getSelectionModel().getSelected() ) {
					return false;
				}

				var record = form.uslugaContentsGrid.getGrid().getSelectionModel().getSelected();

				if ( !record.get('UslugaComplex_id') ) {
					return false;
				}

				// если выбрана группа услуг, то выбираем её в дереве
				if (record.get('UslugaComplexLevel_id') == 1) {
					var node = form.uslugaTree.getSelectionModel().selNode;
					var path = node.getPath();
					form.uslugaTree.selectPath(path + '/' + 'ucom' + record.get('UslugaComplex_id'));
					return false;
				}

				form.showUslugaComplexContents({
					UslugaComplex_id: record.get('UslugaComplex_id'),
					UslugaComplex_Name: record.get('UslugaComplex_Name'),
					level: form.uslugaNavigationString.getLevel() + 1,
					UslugaCategory_SysNick: record.get('UslugaCategory_SysNick'),
					levelUp: true
				});
			},
			onLoadData: function() {
				// this.getAction('action_add').setDisabled(this.getParam('RegistryStatus_id')!=3);
			},
			onRowSelect: function(sm, rowIdx, record) {
				form.linkedUslugaGrid.removeAll();

				if ( !record || !record.get('UslugaComplex_id') ) {
					return false;
				}

				if ((!isSuperAdmin() && record.get('UslugaCategory_SysNick') != 'lpu') || (form.action=='view')){
					form.linkedUslugaGrid.setActionDisabled('action_delete', true);
					form.linkedUslugaGrid.setActionDisabled('action_edit', true);
					form.linkedUslugaGrid.setActionDisabled('action_add', true);
				} else {
					form.linkedUslugaGrid.setActionDisabled('action_delete', false);
					form.linkedUslugaGrid.setActionDisabled('action_edit', false);
					form.linkedUslugaGrid.setActionDisabled('action_add', false);
				}

				form.linkedUslugaGrid.loadData({
					params: {
						UslugaComplex_id: record.get('UslugaComplex_id')
					},
					globalFilters: {
						UslugaComplex_id: record.get('UslugaComplex_id')
					},
					noFocusOnLoad: true
				});

				var node = form.uslugaTree.getSelectionModel().selNode;

				if ( !node ) {
					return false;
				}

				if (( form.uslugaNavigationString.getLevel() == 0 /*&& node.attributes.UslugaCategory_SysNick.toString().inlist([ 'gost2004', 'tfoms', 'gost2011', 'gost2011r', 'syslabprofile', 'lpulabprofile' ])*/ )||((form.action=='view'))) {
					this.setActionDisabled('action_delete', true);
				}
				else {
					this.setActionDisabled('action_delete', false);
				}
			},
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplexComposition_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'CompositionCount', type: 'int', hidden: true },
				{ name: 'UslugaCategory_id', type: 'int', hidden: true },
				{ name: 'UslugaCategory_SysNick', type: 'string', hidden: true },
				{ name: 'UslugaComplexLevel_id', renderer: function(v, p, row) {
					if (!Ext.isEmpty(v) && v == 1) {
						return "<img src='/img/icons/folder16.png' width='16' height='16' />";
					}
					return '';
				}, header: lang['tip'], width: 30 },
				{ name: 'UslugaCategory_Name', header: lang['kategoriya'], width: 150 },
				{ name: 'UslugaComplex_Code', header: lang['kod'], width: 80 },
				{ name: 'UslugaComplex_Name', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'UslugaComplex_begDT', header: lang['data_otkryitiya'], width: 80 },
				{ name: 'UslugaComplex_endDT', header: lang['data_zakryitiya'], width: 80 },
				{ name: 'Lpu_Name', header: lang['lpu'], width: 150 }
			],
			title: lang['sostav_uslugi'],
			totalProperty: 'totalCount'
		});

		form.uslugaContentsGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(form.uslugaContentsGrid);}.createDelegate(this));

		form.linkedUslugaGrid = new sw.Promed.ViewFrame( {
			actions: [
				{ name: 'action_add', handler: function() { form.openUslugaComplexLinkedEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { form.openUslugaComplexLinkedEditWindow('edit'); } },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: false, handler: function() { form.deleteLinkedUsluga(); } },
				{ name: 'action_refresh', disabled: false }
			],
			autoLoadData: false,
			dataUrl: '/?c=UslugaComplex&m=loadLinkedUslugaGrid',
			// editformclassname: 'swUslugaTreeEditWindow',
			height: 300,
			id: form.id + 'LinkedUslugaGrid',
			object: 'UslugaComplex',
			onLoadData: function() {
				// this.getAction('action_add').setDisabled(this.getParam('RegistryStatus_id')!=3);
			},
			region: 'center',
			stringfields: [
				{ name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaCategory_id', type: 'int', hidden: true },
				{ name: 'UslugaCategory_SysNick', type: 'string', hidden: true },
				{ name: 'UslugaCategory_Name', header: lang['kategoriya'], width: 150 },
				{ name: 'UslugaComplex_Code', header: lang['kod'], width: 80 },
				{ name: 'UslugaComplex_Name', header: lang['naimenovanie'], id: 'autoexpand' }
			],
			title: lang['svyazannyie_uslugi']
		});

		form.uslugaNavigationString = new Ext.Panel({
			addRecord: function(data) {
				this.setLevel(data.level);

				if (!data.UslugaCategory_SysNick) {
					data.UslugaCategory_SysNick = '';
				}
				
				// BEGIN произвести поиск по сторе, если уже есть, то не добавлять новую а перейти туда.
				var record;
				this.store.each(function(rec) {
					if ( rec.get('UslugaComplex_id') == data.UslugaComplex_id ) {
						record = rec;
					}
				});
				
				if (record && record.get('UslugaComplex_id')) {
					this.store.each(function(rec) {
						if ( rec.get('level') > record.get('level') ) {
							this.remove('UslugaComplexCmp_' + rec.get('UslugaComplex_id'));
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
					UslugaComplex_id: data.UslugaComplex_id,
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
					id: 'UslugaComplexCmp_' + data.UslugaComplex_id,
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

				this.remove('UslugaComplexCmp_' + record.get('UslugaComplex_id'));
			
				this.add({
					border: false,
					id: 'UslugaComplexCmp_' + record.get('UslugaComplex_id'),
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
					UslugaComplex_id: this.UslugaComplexRoot_id,
					UslugaComplex_Name: this.UslugaComplexRoot_Name,
					level: prevLevel
				});

				this.store.each(function(rec){
					if ( rec.get('level') > prevLevel && rec.get('level') < currentLevel ) {
						prevLevel = rec.get('level');
						prevRecord = rec;
					}
				});

				form.showUslugaComplexContents(prevRecord.data);
			},
			reset: function() {
				this.removeAll();
				this.store.removeAll();

				this.lastRecord = null;
				this.setLevel(0);

				form.uslugaContentsGrid.setActionDisabled('action_addexisting', true);

				this.addRecord({
					UslugaComplex_id: this.UslugaComplexRoot_id,
					UslugaComplex_Name: this.UslugaComplexRoot_Name,
					level: 0
				});
			},
			setLevel: function(level) {
				this.currentLevel = (Number(level) > 0 ? Number(level) : 0);

				if ( this.getLevel() == 0 ) {
					form.uslugaContentsGrid.setActionDisabled('action_upperfolder', true);
				}
				else {
					form.uslugaContentsGrid.setActionDisabled('action_upperfolder', false);
				}

				return this;
			},
			store: new Ext.data.SimpleStore({
				data: [
					//
				],
				fields: [
					{ name: 'UslugaComplex_id', type: 'int' },
					{ name: 'UslugaComplex_Name', type: 'string' },
					{ name: 'UslugaCategory_SysNick', type: 'string' },
					{ name: 'level', type: 'int' }
				],
				key: 'UslugaComplex_id'
			}),
			style: 'border: 0; padding: 0px; height: 25px; background: #fff;',
			textIntoButton: function(record) {
				if ( !record || typeof record != 'object' ) {
					return false;
				}

				this.remove('UslugaComplexCmp_' + record.get('UslugaComplex_id'));
			
				this.add({
					layout: 'form',
					id: 'UslugaComplexCmp_' + record.get('UslugaComplex_id'),
					style: 'padding: 2px;',
					border: false,
					items: [
						new Ext.Button({
							handler: function(btn, e) {
								var rec = this.store.getById(btn.record_id);

								if ( rec ) {
									form.showUslugaComplexContents(rec.data);
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
				
				if ( data.UslugaComplex_id == 0 ) {
					this.reset();
					form.uslugaContentsGrid.ViewActions.action_upperfolder.setDisabled(true);
				}
				else {
					this.setLevel(data.level);
					form.uslugaContentsGrid.ViewActions.action_upperfolder.setDisabled(false);

					this.store.each(function(record) {
						if ( record.get('level') > data.level ) {
							this.remove('UslugaComplexCmp_' + record.get('UslugaComplex_id'));
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
			UslugaComplexRoot_id: 0,
			UslugaComplexRoot_Name: lang['kornevaya_papka']
		});

		form.uslugaContentsGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('CompositionCount')!=null && row.get('CompositionCount') > 0)
					cls = cls+'x-grid-rowselect ';
				return cls;
			}
		});
		
		form.uslugaContentsGridResetFilter = function() {
			form.uslugaFiltersPanel.getForm().reset();
			form.uslugaContentsGrid.getGrid().getStore().baseParams.UslugaComplex_CodeName = null;
			form.uslugaContentsGrid.getGrid().getStore().reload();
		}

		form.uslugaContentsGridFilter = function() {
			var base_form = form.uslugaFiltersPanel.getForm();
			form.uslugaContentsGrid.getGrid().getStore().baseParams.UslugaComplex_CodeName = base_form.findField('UslugaComplex_CodeName').getValue();
			form.uslugaContentsGrid.getGrid().getStore().reload();
		}

		form.uslugaFiltersPanel = new Ext.form.FormPanel({
			region: 'center',
			layout: 'column',
			height: 30,
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			keys: 
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					form.uslugaContentsGridFilter();
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
					fieldLabel: lang['usluga'],
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
							form.uslugaContentsGridFilter();
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
							form.uslugaContentsGridResetFilter();
						}
					})
				]
			}]
		});
		
		form.uslugaTopPanel = new Ext.Panel({
			items: [
				form.uslugaNavigationString,
				form.uslugaFiltersPanel
			],
			height: 60,
			border: false,
			layout: 'border',
			region: 'north'
		});
		
		form.rightPanel = new Ext.Panel({
			items: [
				 new Ext.Panel({
					border: false,
					height: 350,
					items: [
						 form.uslugaTopPanel
						,form.uslugaContentsGrid
					],
					layout: 'border',
					region: 'north'
				 })
				,form.linkedUslugaGrid
			],
			layout: 'border',
			region: 'center',
			split: true
		})

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: -1,
				text: BTN_FRMCLOSE,
				tooltip: lang['zakryit']
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			region: 'center',

			items: [
				 form.uslugaTree
				,form.rightPanel
			]
		});

		sw.Promed.swUslugaTreeWindow.superclass.initComponent.apply(this, arguments);

		form.uslugaContentsGrid.ViewToolbar.on('render', function(vt){
			this.ViewActions['action_addmenu'] = new Ext.Action({ name:'action_addexisting', id: 'action_addmenu', hidden: true, disabled: false, menu: [{
				text: lang['dobavit_gruppu_uslug'],
				handler: function()
				{
					form.openUslugaGroupEditWindow('add');
				}
			}, {
				text: lang['dobavit_uslugu'],
				handler: function()
				{
					form.openUslugaEditWindow('add');
				}
			}] , text: lang['dobavit'], tooltip: lang['dobavit'], iconCls : 'x-btn-text', icon: 'img/icons/add16.png'});
			this.ViewActions['action_addexisting'] = new Ext.Action({ name:'action_addexisting', id: 'id_action_addexisting', disabled: false, handler: function() { form.openExistingUslugaComplexAddWindow(); }, text: lang['dobavit_suschestvuyuschuyu'], tooltip: lang['dobavit_v_sostav_suschestvuyuschuyu_uslugu'], iconCls : 'x-btn-text', icon: 'img/icons/copy16.png'});
			this.ViewActions['action_upperfolder'] = new Ext.Action({ name:'action_upperfolder', id: 'id_action_upperfolder', disabled: false, handler: function() { form.uslugaNavigationString.goToUpperLevel(); }, text: lang['na_uroven_vyishe'], tooltip: lang['na_uroven_vyishe'], iconCls: 'x-btn-text', icon: 'img/icons/arrow-previous16.png' });

			vt.insertButton(1, this.ViewActions['action_upperfolder']);
			vt.insertButton(1, this.ViewActions['action_addmenu']);
			vt.insertButton(1, this.ViewActions['action_addexisting']);

			return true;
		}, form.uslugaContentsGrid);
	},
	isUserFace: function() {
		return (!isAdmin && getGlobalOptions().CurMedStaffFact_id && getGlobalOptions().CurLpuSection_id && getGlobalOptions().CurLpuSectionProfile_id);
	},
	layout: 'border',
	loadTree: function () {
		var node = this.uslugaTree.getSelectionModel().selNode;

		if ( node ) {
			if ( node.parentNode ) {
				node = node.parentNode;
			}
		}
		else {
			node = this.uslugaTree.getRootNode();
		}

		if ( node ) {
			if ( node.isExpanded() ) {
				node.collapse();
				this.uslugaTree.getLoader().load(node);
			}
			node.expand();
			//this.uslugaTree.getRootNode().collapse();
			// Выбираем первую ноду и эмулируем клик 
			/*node.select();
			this.uslugaTree.fireEvent('click', node);*/
		}
	},
	maximized: true,
	maximizable: false,
	// функция выбора элемента дерева 
	onTreeSelect: function(sm, node) {
		if ( !node ) {
			return false;
		}

		this.linkedUslugaGrid.removeAll();
		this.uslugaContentsGrid.removeAll();

		this.uslugaContentsGrid.getGrid().setTitle(lang['uslugi']);
		this.uslugaNavigationString.reset();

		this.uslugaContentsGrid.setParam('UslugaComplex_pid', null, true);

		if ( node.attributes.leaf == 0 && getRegionNick() != 'astra' && (!node.attributes.UslugaCategory_SysNick.inlist(['simple']) && (getRegionNick() != 'buryatiya' || !node.attributes.UslugaCategory_SysNick.inlist(['tfoms']))) ) {
			return false;
		}

		var params = {
			 limit: 100
			,start: 0
			,contents: 1
			,paging: 2
			,Lpu_uid: null
			,UslugaCategory_id: node.attributes.UslugaCategory_id
			,UslugaComplex_pid: null
		}

		switch ( node.attributes.object ) {
			case 'Lpu':
				params.Lpu_uid = node.attributes.object_value.replace('lpu', '');
			break;

			case 'UslugaCategory':
				//
			break;

			case 'UslugaComplex':
				params.UslugaComplex_pid = node.attributes.object_value.replace('ucom', '');
			break;

			default:
				return false;
			break;
		}

		if ( node.attributes.UslugaCategory_SysNick.inlist([ 'gost2004', 'tfoms', 'gost2011', 'gost2011r', 'syslabprofile', 'lpulabprofile' ]) || (this.action=='view') ) {
			this.uslugaContentsGrid.setActionDisabled('action_add', true);
		}
		else {
			this.uslugaContentsGrid.setActionDisabled('action_add', false);
		}

		if ( node.attributes.UslugaCategory_SysNick.inlist([ 'simple' ]) || (getRegionNick() == 'buryatiya' && node.attributes.UslugaCategory_SysNick.inlist(['tfoms'])) ) {
			this.uslugaContentsGrid.setColumnHidden('UslugaComplexLevel_id', false);
			this.uslugaContentsGrid.setActionHidden('action_add', true);
			this.uslugaContentsGrid.setActionHidden('action_addmenu', false);
		} else {
			this.uslugaContentsGrid.setColumnHidden('UslugaComplexLevel_id', true);
			this.uslugaContentsGrid.setActionHidden('action_add', false);
			this.uslugaContentsGrid.setActionHidden('action_addmenu', true);
		}
		if(this.action=='view'){
            this.uslugaContentsGrid.setActionDisabled('action_addmenu', true);
            this.uslugaContentsGrid.setActionDisabled('action_add', true);
        }
		if (isSuperAdmin() && node.attributes.UslugaCategory_SysNick.inlist(['syslabprofile']) && this.action!='view') {
			this.uslugaContentsGrid.setActionDisabled('action_add', false);
		}

		this.uslugaContentsGrid.loadData({
			params: params,
			globalFilters: params
		});
	},
	openExistingUslugaComplexAddWindow: function() {
		if ( getWnd('swUslugaComplexContentEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_dobavleniya_suschestvuyuschey_uslugi_uje_otkryito']);
			return false;
		}

		var formParams = new Object();
		var params = new Object();
		var grid = this.uslugaContentsGrid.getGrid();
		var selectedRecord = this.uslugaNavigationString.getLastRecord();

		if ( selectedRecord.get('level') == 0 || !selectedRecord.get('UslugaComplex_id') ) {
			return false;
		}

		formParams.UslugaComplex_pid = selectedRecord.get('UslugaComplex_id');

		params.formParams = formParams;
		
		params.callback = function(data) {
			grid.getStore().reload();
		};

		getWnd('swUslugaComplexContentEditWindow').show(params);
	},
	openUslugaComplexLinkedEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		if ( getWnd('swUslugaComplexLinkedEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_dobavleniya_svyazannoy_uslugi_uje_otkryito']);
			return false;
		}

		var linkedUslugaGrid = this.linkedUslugaGrid.getGrid();
		var uslugaContentsGrid = this.uslugaContentsGrid.getGrid();

		if ( !uslugaContentsGrid.getSelectionModel().getSelected() || !uslugaContentsGrid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
			return false;
		}

		var deniedCategoryList = new Array();
		var formParams = new Object();
		var params = new Object();
		var record = uslugaContentsGrid.getSelectionModel().getSelected();

		formParams.UslugaComplex_pid = record.get('UslugaComplex_id');

		deniedCategoryList.push(record.get('UslugaCategory_id'));

		linkedUslugaGrid.getStore().each(function(rec) {
			if ( rec.get('UslugaCategory_id') && rec.get('UslugaCategory_SysNick') != 'lpu' ) {
				deniedCategoryList.push(rec.get('UslugaCategory_id'));
			}
		});
		
		if (action == 'edit') {
			if ( !linkedUslugaGrid.getSelectionModel().getSelected() || !linkedUslugaGrid.getSelectionModel().getSelected().get('UslugaCategory_id') ) {
				return false;
			}
			var linkedRecord = linkedUslugaGrid.getSelectionModel().getSelected();
			formParams.UslugaCategory_id = linkedRecord.get('UslugaCategory_id');
			formParams.UslugaComplex_id = linkedRecord.get('UslugaComplex_id');
		}

		params.action = action;
		params.deniedCategoryList = deniedCategoryList;
		params.UslugaCategory_SysNick = record.get('UslugaCategory_SysNick');
		params.formMode = 'remote';
		params.formParams = formParams;
		params.CompositionCount = record.get('CompositionCount');
		
		params.callback = function(data) {
			linkedUslugaGrid.getStore().reload();
		};
		
		getWnd('swUslugaComplexLinkedEditWindow').show(params);
	},
	openUslugaEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		var grid = this.uslugaContentsGrid.getGrid();

		if (action != 'add') {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
				return false;
			}

			// если группа услуг то openUslugaGroupEditWindow();
			if ((grid.getSelectionModel().getSelected().get('UslugaCategory_SysNick') == 'simple' || (getRegionNick() == 'buryatiya' && grid.getSelectionModel().getSelected().get('UslugaCategory_SysNick') == 'tfoms')) && grid.getSelectionModel().getSelected().get('UslugaComplexLevel_id') == 1) {
				this.openUslugaGroupEditWindow(action);
				return false;
			}
		}

		if ( getWnd('swUslugaEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_uslugi_uje_otkryito']);
			return false;
		}

		// var base_form = this.formPanel.getForm();
		var formParams = new Object();
		var params = new Object();

		if ( action == 'add' ) {
			// Тянем инфу из дерева
			var node = this.uslugaTree.getSelectionModel().selNode;

			if ( !node || !node.attributes.UslugaCategory_id || (node.attributes.leaf == 0 && (node.attributes.UslugaCategory_SysNick != 'simple' && (getRegionNick() != 'buryatiya' || record.get('UslugaCategory_SysNick') != 'tfoms'))) ) {
				return false;
			}

            formParams.UslugaCategory_SysNick = node.attributes.UslugaCategory_SysNick;
			formParams.UslugaCategory_id = node.attributes.UslugaCategory_id;
			switch ( node.attributes.object ) {
				case 'Lpu':
					formParams.Lpu_id = node.attributes.object_value.replace('lpu', '');
				break;
				
				case 'UslugaComplex':
					formParams.UslugaComplex_pid = node.attributes.object_value.replace('ucom', '');
					formParams.UslugaComplex_pid = parseInt(formParams.UslugaComplex_pid);
					if (isNaN(formParams.UslugaComplex_pid)) {
						formParams.UslugaComplex_pid = null;
					}
                    formParams.UslugaComplexLevel_id = node.attributes.UslugaComplexLevel_id;
				break;
			}

			// Получаем идентификатор услуги, чей состав отображается в верхнем гриде
			var selectedRecord = this.uslugaNavigationString.getLastRecord();

			if ( selectedRecord.get('level') > 0 && selectedRecord.get('UslugaComplex_id') ) {
				formParams.UslugaComplex_cid = selectedRecord.get('UslugaComplex_id');
			}
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
				return false;
			}

			formParams.UslugaComplex_id = grid.getSelectionModel().getSelected().get('UslugaComplex_id');
		}

		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.formParams = formParams;

		getWnd('swUslugaEditWindow').show(params);
	},
	openUslugaGroupEditWindow: function(action) {
		var win = this;
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		if ( getWnd('swUslugaGroupEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_gruppyi_uslug_uje_otkryito']);
			return false;
		}

		// var base_form = this.formPanel.getForm();
		var formParams = new Object();
		var grid = this.uslugaContentsGrid.getGrid();
		var params = new Object();

		// Тянем инфу из дерева
		var node = this.uslugaTree.getSelectionModel().selNode;

		if ( !node ) {
			return false;
		}

		if ( action == 'add' ) {
            formParams.UslugaCategory_SysNick = node.attributes.UslugaCategory_SysNick;
			formParams.UslugaCategory_id = node.attributes.UslugaCategory_id;
			formParams.UslugaComplex_pid = node.attributes.object_value.replace('ucom', '');
			formParams.UslugaComplex_pid = parseInt(formParams.UslugaComplex_pid);
			if (isNaN(formParams.UslugaComplex_pid)) {
				formParams.UslugaComplex_pid = null;
			}
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
				return false;
			}

			formParams.UslugaComplex_id = grid.getSelectionModel().getSelected().get('UslugaComplex_id');
		}

		params.action = action;
		params.callback = function(data) {
			if (data) {
				// выбрать в дереве ветку с данной услугой
				// если у node есть родитель, то берём родителя и прибавляем 'ucom' + UslugaComplex_pid, иначе выбираем ветку с родителем.
				if (!Ext.isEmpty(data.UslugaComplex_pid)) {
					var path = node.parentNode.getPath();
					win.uslugaTree.getLoader().load(node.parentNode,function(tl,n){
						win.uslugaTree.selectPath(path + '/' + 'ucom' + data.UslugaComplex_pid);
						win.onTreeSelect(win.uslugaTree.getSelectionModel(), win.uslugaTree.getSelectionModel().selNode);
					});
				} else {
					var path = node.getPath();
					win.uslugaTree.getLoader().load(node, function (tl, n) {
						win.uslugaTree.selectPath(path);
						win.onTreeSelect(win.uslugaTree.getSelectionModel(), win.uslugaTree.getSelectionModel().selNode);
					});
				}
			}

			// grid.getStore().reload();
		};
		params.formParams = formParams;

		getWnd('swUslugaGroupEditWindow').show(params);
	},
	reloadUslugaTree: function(reload) {
		var tree = this.uslugaTree;
		var root = tree.getRootNode();

		root.select();
		//tree.fireEvent('click', root);
		tree.getLoader().load(root);
		root.expand();
		root.select();

		if ( reload ) {
			this.onTreeSelect(tree.getSelectionModel(), root);
		}
		
		//this.uslugaTree.fireEvent('click', root);
	},
	shim: false,
	/** Данный метод вызывается при открытии формы.
	* @param - {Object} массив содержащий входные функции и переменные
	*/
	show: function() {
		sw.Promed.swUslugaTreeWindow.superclass.show.apply(this, arguments);
        this.action = '';
        if(arguments[0] && arguments[0].action)
            this.action = arguments[0].action;
		this.uslugaTree.getRootNode().select();
		this.loadTree();
        if(this.action=='view') {
            this.uslugaContentsGrid.setActionDisabled('action_addmenu', true);
            this.uslugaContentsGrid.setActionDisabled('action_add', true);
            this.uslugaContentsGrid.setActionDisabled('action_edit', true);
            this.uslugaContentsGrid.setActionDisabled('action_delete', true);
            this.linkedUslugaGrid.setActionDisabled('action_delete', true);
            this.linkedUslugaGrid.setActionDisabled('action_edit', true);
            this.linkedUslugaGrid.setActionDisabled('action_add', true);
        }
		this.uslugaNavigationString.reset();
	},
	showUslugaComplexContents: function(data) {
		if ( typeof data != 'object' ) {
			return false;
		}

		var uslugaNavigationString = this.uslugaNavigationString;

		if ( data.level == 0 ) {
			uslugaNavigationString.reset();
			this.onTreeSelect(this.uslugaTree.getSelectionModel(), this.uslugaTree.getSelectionModel().selNode);
		}
		else {
			this.linkedUslugaGrid.removeAll();
			this.uslugaContentsGrid.removeAll();

			this.uslugaContentsGrid.getGrid().setTitle(lang['sostav_uslugi']);
			this.uslugaNavigationString.setLevel(0);

			if (data.UslugaCategory_SysNick && !(data.UslugaCategory_SysNick.inlist(['gost2004','gost2011','gost2011r']))) {
				this.uslugaContentsGrid.setActionDisabled('action_addexisting', false);
			} else {
				this.uslugaContentsGrid.setActionDisabled('action_addexisting', true);
			}
			if(this.action == 'view'){
				this.uslugaContentsGrid.setActionDisabled('action_addexisting', true);
			}
			var params = {
				 limit: 100
				,start: 0
				,contents: 2
				,paging: 2
				,Lpu_uid: null
				,UslugaCategory_id: null
				,UslugaComplex_pid: data.UslugaComplex_id
			}

			this.uslugaContentsGrid.loadData({
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
	},
	title: lang['spravochnik_uslug']
});
