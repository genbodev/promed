/**
* Окно выбора палаты
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.SelectLpuSectionWard', {
	alias: 'widget.swSelectLpuSectionWardExt6',
	width: 660,	height: 480,
	title: langs('Выбор палаты'),
	cls: 'arm-window-new ',
	noTaskBarButton: true,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(),
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	expandGroup: function(groupName, callback) {
		var win = this;

		win.loadWardGrid({
			addRecords: true,
			onExpandGroup: true,
			LpuSection_id: groupName,
			callback: function() {
				if (typeof callback == 'function') {
					callback();
				}
			}
		});
	},
	loadWardGrid: function(options) {
		var win = this;
		var params = {};
		if (!options) {
			options = {};
		}
		if (!options.addRecords) {
			options.addRecords = false;
			win.WardGrid.getStore().removeAll();
		}

		if (options.onExpandGroup) {
			win.WardGrid.getStore().proxy.extraParams.expandOnLoad = 1;
		} else {
			win.WardGrid.getStore().proxy.extraParams.expandOnLoad = null;
		}

		if (options.LpuSection_id) {
			params.LpuSection_id = options.LpuSection_id;
		}

		if (win.LpuSection_uid) {
			params.LpuSection_uid = win.LpuSection_uid;
		}
		params.WithoutChildLpuSectionAge = win.WithoutChildLpuSectionAge;
		params.Person_id = win.Person_id;

		win.WardGrid.getStore().proxy.extraParams.formMode = 'ExtJS6';

		win.WardGrid.getStore().load({
			params: params,
			addRecords: options.addRecords,
			callback: function() {
				if (typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	onLoadGrid: function(store, records, successful, operation, eOpts) {
		log('onLoadGrid', store, records, successful, operation, eOpts);

		var win = this;
		if(!records) {
			return true;
		}

		win.WardGrid.getEl().query(".show-more-div").forEach(function(showMoreDiv) {
			showMoreDiv.remove();
		});

		if (operation.request && operation.request.config && operation.request.config.params && operation.request.config.params.expandOnLoad) {
			if (records.length > 0) {
				var Group_id = records[0].data.Group_id;

				var undeleteIds = [];
				for(var k in records) {
					if (records[k].data && records[k].data.id) {
						undeleteIds.push(records[k].data.id);
					}
				}
				// убираем из грида записи по группе, которые были до загрузки
				var recordsToRemove = [];
				win.WardGrid.getStore().findBy(function(rec) {
					if (!rec.get('id').inlist(undeleteIds) && (rec.get('Group_id') == Group_id)) {
						recordsToRemove.push(rec);
					}
				});
				win.WardGrid.getStore().remove(recordsToRemove);

				win.groupingFeature.expand(Group_id);
			}
		} else {
			win.groupingFeature.collapseAll();
		}
	},
	show: function(data) {
		var win = this;
		var base_form = win.MainPanel.getForm();
		win.callParent(arguments);
		win.Person_id = data.Person_id;
		win.Person_Fio = data.Person_Fio;
		win.onSelect = data.onSelect || Ext6.emptyFn;
		win.EvnPS_setDT = data.EvnPS_setDT || null;
		win.LpuSection_uid = data.LpuSection_uid || null;
		win.Person_BirthDay = data.Person_BirthDay || null;
		win.age = swGetPersonAge(win.Person_BirthDay, getGlobalOptions().date);
		win.WithoutChildLpuSectionAge = 0;
		if ( win.age >= 18 ) {
			win.WithoutChildLpuSectionAge = 1;
		}

		win.queryById('button_save').enable();

		base_form.findField('Person_FIO').setValue(this.Person_Fio);
		base_form.findField('EvnSection_setDate').setValue(getGlobalOptions().date);
		base_form.findField('EvnSection_setTime').setValue(new Date().format('H:i'));
		base_form.findField('EvnSection_EmptyWard').setValue(false);
		win.loadWardGrid();
	},
	doSelect: function() {
		var win = this;
		var base_form = win.MainPanel.getForm();
		var emptyWard = base_form.findField('EvnSection_EmptyWard').getValue();
		var record = win.WardGrid.getSelectionModel().getSelected().items[0];
		var lpu_section_id = record ? record.get('Group_id'):null;
		var ward_id = record ? record.get('LpuSectionWard_id'):null;
		var evnsection_setdate = base_form.findField('EvnSection_setDate').getValue();
		var evnsection_settime = base_form.findField('EvnSection_setTime').getValue();
		var evn_section_dis_dt = getValidDT(Ext6.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_setTime').getValue());
		var evn_ps_set_dt = win.EvnPS_setDT;

		switch (true) {
			case !emptyWard && !(record && record.get('LpuSectionWard_id') && record.get('Group_id')):
				Ext6.Msg.alert(langs('Ошибка'), langs('Не выбрана палата'));
				return false;
			case evn_ps_set_dt && evn_ps_set_dt > evn_section_dis_dt:
				Ext6.Msg.alert(langs('Ошибка'), langs('Дата/время выписки из отделения меньше даты/времени поступления'));
				return false;
			case !evnsection_setdate || !evnsection_settime:
				Ext6.Msg.alert(langs('Ошибка'), langs('Не выбрана дата или время госпитализации'));
				return false;
		}

		win.hide();
		win.onSelect({
			emptyWard: emptyWard,
			LpuSection_id: lpu_section_id,
			LpuSectionWard_id: ward_id,
			EvnSection_setDate: evnsection_setdate,
			EvnSection_setTime: evnsection_settime
		});
	},
	initComponent: function() {
		var win = this;

		win.groupingFeature = Ext6.create('swGridPrescrGroupingFeature', {
			enableGroupingMenu: false,
			onBeforeGroupClick: function(view, rowElement, groupName, e) {
				log('onBeforeGroupClick', view, rowElement, groupName, e);

				var groupIsCollapsed = !win.groupingFeature.isExpanded(groupName);
				if (groupIsCollapsed) {
					win.expandGroup(groupName);

					return false;
				} else {
					return true;
				}
			},
			groupHeaderTpl: new Ext6.XTemplate(
				'{[this.formatName(values.rows)]}',
				{
					formatName: function(rows) {
						var s = '';

						if (rows[0] && rows[0].get('Group_Type')) {
							switch (rows[0].get('Group_Type')) {
								case '1':
									s += '<img src="/img/icons/stac_rooms/male-room.png"> ';
									break;
								case '2':
									s += '<img src="/img/icons/stac_rooms/female-room.png"> ';
									break;
								case '3':
									s += '<img src="/img/icons/stac_rooms/combination-room.png"> ';
									break;
							}
						}
						if (rows[0] && rows[0].get('LpuSectionWardCount_Free') && rows[0].get('LpuSectionWardCount_All')) {
							s = s + ' (Свободных коек: ' + rows[0].get('LpuSectionWardCount_Free') + ' из ' + rows[0].get('LpuSectionWardCount_All') + ') | ';
						}
						if (rows[0] && rows[0].get('Group_Name')) {
							s = s + rows[0].get('Group_Name');
						}

						return s;
					}
				}
			)
		});

		win.WardGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			viewModel: true,
			scrollable: 'y',
			frame: false,
			border: false,
			defaults: {
				border: 0
			},
			bind: {
				selection: '{theRow}'
			},
			selModel: 'rowmodel',
			features: [
				win.groupingFeature
			],
			columns: [
				{
					text: 'Номер',
					dataIndex: 'Ward_Num',
					align: 'left'
				},
				{
					text: 'Тип',
					dataIndex: 'Ward_Type',
					align: 'left',
					width: 150,
					renderer: function (value, p, r) {
						if (value !== null && value !=="") {
							switch (value) {
								case '1':
									return '<img src="/img/icons/stac_rooms/male-room.png"> Мужская';
								case '2':
									return '<img src="/img/icons/stac_rooms/female-room.png"> Женская';
								case '3':
									return '<img src="/img/icons/stac_rooms/combination-room.png"> Общая';
							}
						}
						return '';
					}
				},
				{
					text: 'Свободно коек',
					dataIndex: 'WardCount_Free',
					align: 'left',
					width: 150
				},
				{
					text: 'Общее количество коек',
					dataIndex: 'WardCount_All',
					align: 'left',
					width: 180
				}
			],
			requires: [
				'Ext6.ux.GridHeaderFilters'
			],
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				})
			],
			store: {
				groupField: 'Group_id',
				fields: [{
					name: 'Ward_Num',
					type: 'string'
				}, {
					name: 'Ward_Type',
					type: 'string'
				}, {
					name: 'WardCount_Free',
					type: 'string'
				}, {
					name: 'WardCount_All',
					type: 'string'
				}
				],
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnSection&m=getLpuSectionWardSelectList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				listeners: {
					load: function(store, records, successful, operation, eOpts) {
						win.onLoadGrid(store, records, successful, operation, eOpts);
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			}
		});

		win.GridPanel = new Ext6.form.FormPanel({
			bodyPadding: '0 25 0 30',
			region: 'center',
			border: false,
			layout: 'fit',
			items:[win.WardGrid]
		});

		win.MainPanel = new Ext6.form.FormPanel({
			bodyPadding: '25 25 25 30',
			region: 'north',
			border: false,
			items:[{
				xtype: 'displayfield',
				fieldLabel: 'Пациент',
				name: 'Person_FIO',
				labelAlign: 'right',
				labelWidth: 200
			}, {
				layout: 'column',
				border: false,
				style: 'margin-bottom: 10px;',
				items: [{
					xtype: 'swDateField',
					allowBlank: false,
					inputCls: 'date_time_priem',
					format: 'd.m.Y',
					startDay: 1,
					fieldLabel: 'Дата и время госпитализации',
					style: 'margin-right: 10px;',
					labelAlign: 'right',
					labelWidth: 200,
					name: 'EvnSection_setDate'
				}, {
					xtype: 'swTimeField',
					allowBlank: false,
					width: 100,
					userCls:'vizit-time',
					hideLabel: true,
					name: 'EvnSection_setTime'
				}]
			}, {
				xtype: 'checkbox',
				fieldLabel: 'Без палаты',
				name: 'EvnSection_EmptyWard',
				labelAlign: 'right',
				labelWidth: 200,
				listeners: {
					change: function(checkbox, checked) {
						if(checked) {
							win.GridPanel.disable();
						}
						else {
							win.GridPanel.enable();
						}
					}
				}
			}, win.GridPanel]
		});

		Ext6.apply(win, {
			items: [
				win.MainPanel,
				win.GridPanel
			],
			buttons: ['->',
				{
					text: langs('ОТМЕНА'),
					itemId: 'button_cancel',
					userCls:'buttonPoupup buttonCancel',
					handler:function () {
						win.hide();
					}
				},
				{
					text: langs('ВЫБРАТЬ'),
					itemId: 'button_save',
					userCls:'buttonPoupup buttonAccept',
					handler: function() {
						win.doSelect();
					}.createDelegate(this)
				}
			]
		});

		this.callParent(arguments);
	}
});