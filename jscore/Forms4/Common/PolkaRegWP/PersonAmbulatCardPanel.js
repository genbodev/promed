/**
 * Панель амбулаторных карт
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
Ext6.define('common.PolkaRegWP.PersonAmbulatCardPanel', {
	extend: 'swPanel',
	title: 'АМБУЛАТОРНЫЕ КАРТЫ',
	layout: 'border',
	clearParams: function() {
		var me = this;

		me.Person_id = null;
		me.Server_id = null;
		me.Person_Surname = '';
		me.Person_Firname = '';
		me.Person_Secname = '';
		me.Person_IsDead = null;

		me.PersonAmbulatCardGrid.getStore().removeAll();
		me.onRecordSelect();
	},
	setParams: function(params) {
		var me = this;

		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.Person_Surname = params.Person_Surname;
		me.Person_Firname = params.Person_Firname;
		me.Person_Secname = params.Person_Secname;
		me.Person_IsDead = params.Person_IsDead;

		if (!me.ownerCt.collapsed && me.isVisible()) {
			me.load();
		}
	},
	load: function() {
		var me = this;
		if (!Ext6.isEmpty(me.Person_id)) {
			this.PersonAmbulatCardGrid.getStore().load({
				params: {
					Person_id: me.Person_id,
					Lpu_id: getGlobalOptions().lpu_id
				}
			});
		}
	},
	onRecordSelect: function() {
		var me = this;

		me.PersonAmbulatCardGrid.down('#action_add').disable();
		me.PersonAmbulatCardGrid.down('#action_edit').disable();
		me.PersonAmbulatCardGrid.down('#action_delete').disable();

		if (me.Person_id) {
			me.PersonAmbulatCardGrid.down('#action_add').enable();
		}

		if (this.PersonAmbulatCardGrid.getSelectionModel().hasSelection()) {
			var record = this.PersonAmbulatCardGrid.getSelectionModel().getSelection()[0];

			if (record.get('PersonAmbulatCard_id')) {
				me.PersonAmbulatCardGrid.down('#action_edit').enable();
				me.PersonAmbulatCardGrid.down('#action_delete').enable();
			}
		}
	},
	openPersonAmbulatCardEditWindow: function(action) {
		var me = this;
		var grid = this.PersonAmbulatCardGrid;
		var params = {};
		if (action == 'add') {
			if (me.Person_IsDead == "true") {
				sw.swMsg.show({
					buttons: sw.swMsg.OK,
					//title: 'Предупреждение',
					msg: 'Невозможно создать новую карту. Причина: смерть пациента',
					icon: sw.swMsg.WARNING
				});
				return false;
			}
			params = {
				action: action,
				Person_id: me.Person_id,
				Server_id: me.Server_id,
				PersonFIO: me.Person_Surname + ' ' + me.Person_Firname.substr(0, 1) + ' ' + me.Person_Secname.substr(0, 1)
			}
		} else {

			if (!grid.getSelectionModel().getSelectedRecord() || !grid.getSelectionModel().getSelectedRecord().get('PersonAmbulatCard_id')) {
				return false;
			}
			var record = grid.getSelectionModel().getSelectedRecord();
			params = {
				action: action,
				PersonAmbulatCard_id: record.get('PersonAmbulatCard_id')
			}
		}
		params.callback = function() {
			grid.getStore().reload();
		}
		getWnd('swPersonAmbulatCardEditWindow').show(params);
	},
	deletePersonAmbulatCard: function() {
		var me = this;
		var grid = this.PersonAmbulatCardGrid;

		if (!grid.getSelectionModel().getSelectedRecord() || !grid.getSelectionModel().getSelectedRecord().get('PersonAmbulatCard_id')) {
			return false;
		}
		var record = grid.getSelectionModel().getSelectedRecord();
		if (me.Person_IsDead == "true") {
			Ext6.Msg.show({
				buttons: sw.swMsg.OK,
				//title: 'Предупреждение',
				msg: 'Невозможно удалить карту. Причина: смерть пациента',
				icon: sw.swMsg.WARNING
			});
			return false;
		}
		Ext6.Msg.show({
			buttons: Ext6.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					me.mask('Удаление амбулаторной карты');
					Ext6.Ajax.request({
						params: {
							PersonAmbulatCard_id: record.get('PersonAmbulatCard_id')
						},
						success: function(response) {
							me.unmask();
							grid.getStore().reload();
						},
						failure: function(response, options) {
							me.unmask();
							sw.swMsg.alert('Ошибка', 'При удалении оригинала АК.');
						},
						url: '/?c=PersonAmbulatCard&m=deletePersonAmbulatCard'
					});
				}
				else {
					return false;
				}
			}.createDelegate(this),
			icon: Ext6.MessageBox.QUESTION,
			msg: 'Удалить выбранную амбулаторную карту?',
			title: 'Вопрос'
		});
	},
	_printMedCard: function(personCard, personId, personAmbulatCard_id, personAmbulatCard_num) {// #137782
		if (!personCard) personCard = 0;
		if (!personId) personId = 0;
		if (!personAmbulatCard_id) personAmbulatCard_id = 0;
		if (!personAmbulatCard_num) personAmbulatCard_num = 0;
		var lpu = getLpuIdForPrint();
		if (getRegionNick().inlist(['kz'])) {
			var params = {
				PersonCard_id: personCard,
				Person_id: personId
			};
			if (personAmbulatCard_num) {
				params.PersonAmbulatCard_Num = personAmbulatCard_num;
			}
			Ext6.Ajax.request({
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						openNewWindow(response_obj.result);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При получении данных для печати мед. карты произошла ошибка');
					}
				}.createDelegate(this),
				params: params,
				url: '/?c=PersonCard&m=printMedCard'
			});
		}
		else if (getRegionNick() == 'ufa') {
			//printMedCard4Ufa(gridSelected.get('PersonCard_id'));// функцию не трогаю, может вызываться откуда-то ещё
			printBirt({
				'Report_FileName': 'f025u_oborot.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
			printBirt({
				'Report_FileName': 'f025u.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
		}
		else {
			printBirt({
				'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
		}
	},
	initComponent: function() {
		var me = this;

		this.PersonAmbulatCardGrid = Ext6.create('Ext6.grid.Panel', {
			itemId: 'PersonAmbulatCardGrid',
			border: false,
			cls: 'grid-common',
			region: 'center',
			tbar: {
				xtype: 'toolbar',
				defaults: {
					margin: '0 4 0 0',
					padding: '4 10'
				},
				height: 40,
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					margin: '0 0 0 6',
					text: 'Открыть',
					itemId: 'action_edit',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_edit',
					handler: function() {
						me.openPersonAmbulatCardEditWindow('edit');
					}
				}, {
					text: 'Добавить карту',
					itemId: 'action_add',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_add',
					handler: function() {
						me.openPersonAmbulatCardEditWindow('add');
					}
				}, {
					text: 'Удалить',
					itemId: 'action_delete',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_delete',
					handler: function() {
						me.deletePersonAmbulatCard();
					}
				}, {
					text: 'Печать',
					itemId: 'action_print',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					menu: new Ext6.menu.Menu({
						userCls: 'menuWithoutIcons',
						items: [{
							text: 'Печать списка',
							handler: function() {
								Ext6.ux.GridPrinter.print(me.PersonAmbulatCardGrid);
							}
						}, {
							text: 'Печать амбулаторной карты',
							handler: function() {
								var grid = me.PersonAmbulatCardGrid,
									record = grid.getSelectionModel().getSelectedRecord();

								if (Ext6.isEmpty(record)) {
									return false;
								}

								var personCard = 0,
									personId = 0,
									personAmbulatCard_id = 0,
									personAmbulatCard_num = 0;

								if (!Ext6.isEmpty(record.get('PersonCard_id'))) {
									personCard = record.get('PersonCard_id');
								}

								if (!Ext6.isEmpty(record.get('Person_id'))) {
									personId = parseInt(record.get('Person_id'));
								}

								if (!Ext6.isEmpty(record.get('PersonAmbulatCard_id'))) {
									personAmbulatCard_id = parseInt(record.get('PersonAmbulatCard_id'));
								}

								if (!Ext6.isEmpty(record.get('PersonAmbulatCard_Num'))) {
									personAmbulatCard_num = parseInt(record.get('PersonAmbulatCard_Num'));
								}

								me._printMedCard(personCard, personId, personAmbulatCard_id, personAmbulatCard_num);

								return true;
							}
						}, {
							disabled: false,
							handler: function() {
								var grid = me.PersonAmbulatCardGrid,
									record = grid.getSelectionModel().getSelectedRecord();
								var PersonAmbulatCard_id = record.get('PersonAmbulatCard_id');
								if(!PersonAmbulatCard_id) return false;
								printBirt({
									'Report_FileName': 'BarCodesPrint_AmbulatCard.rptdesign',
									'Report_Params': '&AmbulatCard=' + PersonAmbulatCard_id,
									'Report_Format': 'pdf'
								});
							}.createDelegate(this),
							name: 'printed_barcode_ambulatory_card',
							text: 'Печать штрих-кода амбулаторной карты',
							tooltip: 'Печать штрих-кода амбулаторной карты'
						}, {
							text: 'Печать стомат. карты (форма 043/у)',
							handler: function() {
								var grid = me.PersonAmbulatCardGrid,
									record = grid.getSelectionModel().getSelectedRecord();

								if (Ext6.isEmpty(record)) {
									return false;
								}

								var Person_id = grid.getSelectionModel().getSelectedRecord().get('Person_id');

								printBirt({
									'Report_FileName': 'f043u.rptdesign',
									'Report_Params': '&paramLpu=' + (!Ext6.isEmpty(getGlobalOptions().lpu_id) ? getGlobalOptions().lpu_id : 0) + '&paramEvnVizitPLStom_id=0&paramPerson_id=' + Person_id,
									'Report_Format': 'pdf'
								});
							}
						}]
					})
				}]
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						me.onRecordSelect();
					}
				}
			},
			columns: [
				{text: '№карты', tdCls: 'padLeft', width: 150, dataIndex: 'PersonAmbulatCard_Num'},
				{
					text: 'Последнее движение',
					width: 280,
					dataIndex: 'PersonAmbulatCardLocat_begDate',
					renderer: Ext6.util.Format.dateRenderer('d.m.Y H:i')
				},
				{text: 'Текущее местоположение', width: 150, flex: 1, dataIndex: 'AmbulatCardLocatType_Name'}
			],
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{name: 'PersonAmbulatCard_id', type: 'int'},
					{name: 'Person_id', type: 'int'},
					{name: 'PersonCard_id', type: 'int'},
					{name: 'PersonAmbulatCard_Num'},
					{name: 'PersonAmbulatCardLocat_begDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'AmbulatCardLocatType_id', type: 'int'},
					{name: 'isAttach', type: 'int'},
					{name: 'AmbulatCardLocatType_Name'}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonAmbulatCard&m=getPersonAmbulatCardList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'PersonAmbulatCard_Num',
					direction: 'ASC'
				},
				listeners: {
					load: function() {
						me.onRecordSelect();
					}
				}
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonAmbulatCardGrid
			]
		});

		this.callParent(arguments);
	}
});