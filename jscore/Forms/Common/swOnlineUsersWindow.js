/**
 * swOnlineUsersWindow - Пользователи онлайн
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */
sw.Promed.swOnlineUsersWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Пользователи онлайн',
	maximized: false,
	width: 800,
	height: 600,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swOnlineUsersWindow',
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function (button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text: BTN_FRMCLOSE,
			tabIndex: -1,
			tooltip: lang['zakryit'],
			iconCls: 'cancel16',
			handler: function () {
				this.ownerCt.hide();
			}
		}
	],
	show: function () {
		sw.Promed.swOnlineUsersWindow.superclass.show.apply(this, arguments);

		if(!this.Grid.getAction('action_actualdate')) {
			this.Grid.addActions({
				name:'action_actualdate',
				text: 'Актуальность данных неизвестна'
			}, 0);
		}

		this.doReset();
	},
	doSearch: function (mode) {
		var params = this.FilterPanel.getForm().getValues();
		this.Grid.removeAll();
		this.Grid.loadData({globalFilters: params});
	},
	doReset: function () {
		this.FilterPanel.getForm().reset();
		this.doSearch();
	},
	initComponent: function () {
		var win = this;

		this.FilterPanel = new Ext.form.FormPanel({
			floatable: false,
			autoHeight: true,
			animCollapse: false,
			labelAlign: 'right',
			defaults: {
				bodyStyle: 'background: #DFE8F6;'
			},
			region: 'north',
			frame: true,
			buttonAlign: 'left',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doSearch();
				},
				stopEvent: true
			}],
			items: [{
				xtype: 'fieldset',
				style: 'padding: 0px 3px 3px 6px;',
				autoHeight: true,
				listeners: {
					expand: function () {
						this.ownerCt.doLayout();
						win.syncSize();
					},
					collapse: function () {
						win.syncSize();
					}
				},
				collapsible: true,
				labelWidth: 150,
				collapsed: false,
				title: lang['filtr'],
				bodyStyle: 'background: #DFE8F6;',
				items: [{
					xtype: 'sworgcombo',
					fieldLabel: 'Организация',
					hiddenName: 'Org_id',
					width: 500,
					tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Nick}</div></tpl>',
					listeners: {
						'select': function (combo, record, index) {
							combo.setRawValue(record.get('Org_Nick'));
						},
						'change': function (combo, newValue, oldValue) {
							var base_form = win.FilterPanel.getForm();
							if (combo.getFieldValue('OrgType_SysNick')) {
								base_form.findField('OrgType_id').setFieldValue('OrgType_SysNick', combo.getFieldValue('OrgType_SysNick'));
							}
						},
						'keydown': function (inp, e) {
							if (e.getKey() == e.DELETE) {
								inp.setValue('');
								inp.setRawValue("");
								inp.selectIndex = -1;
								if (inp.onClearValue)
									this.onClearValue();
								e.stopEvent();
								return true;
							}

							if (e.getKey() == e.F4) {
								this.onTriggerClick();
							}
						}
					},
					onTriggerClick: function() {
						if (this.disabled) return false;
						var combo = this;

						var base_form = win.FilterPanel.getForm();

						getWnd('swOrgSearchWindow').show({
							//object: 'org',
							enableOrgType: combo.enableOrgType,
							defaultOrgType: base_form.findField('OrgType_id').getValue(),
							allowEmptyUAddress: combo.allowEmptyUAddress,
							disableEdit: true,
							onHide: function() {
								combo.focus(false);
							},
							onSelect: function(orgData) {
								combo.getStore().removeAll();
								combo.getStore().loadData([{
									Org_id: orgData.Org_id,
									Org_Name: orgData.Org_Name,
									Org_ColoredName : '',
									OrgType_SysNick: orgData.OrgType_SysNick
								}]);
								combo.setValue(orgData[combo.valueField]);

								var index = combo.getStore().find('Org_id', orgData.Org_id);

								if (index == -1)
								{
									return false;
								}

								var record = combo.getStore().getAt(index);
								combo.fireEvent('select', combo, record, 0);
								combo.fireEvent('change', combo, combo.getValue());

								getWnd('swOrgSearchWindow').hide();
							}
						});
					},
					emptyText: lang['vvedite_chast_nazvaniya'],
					mode: 'remote',
					triggerAction: 'query',
					minChars: 3,
					forceSelection: true
				}, {
					comboSubject: 'OrgType',
					moreFields: [{
						name: 'OrgType_SysNick', mapping: 'OrgType_SysNick'
					}],
					width: 400,
					typeCode: 'int',
					hiddenName: 'OrgType_id',
					fieldLabel: lang['tip_organizatsii'],
					xtype: 'swcommonsprcombo'
				}, {
					displayField: 'ARMType_Name',
					editable: true,
					fieldLabel: 'АРМ',
					hiddenName: 'ARMType_SysNick',
					mode: 'local',
					resizable: true,
					store: new Ext.data.Store({
						autoLoad: true,
						sortInfo: {
							field: 'ARMType_id'
						},
						reader: new Ext.data.JsonReader({
							id: 'ARMType_SysNick'
						}, [
							{ name: 'ARMType_id', mapping: 'ARMType_id' },
							{ name: 'ARMType_Code', mapping: 'ARMType_Code' },
							{ name: 'ARMType_Name', mapping: 'ARMType_Name' },
							{ name: 'ARMType_SysNick', mapping: 'ARMType_SysNick' }
						]),
						url:'/?c=User&m=getPHPARMTypeList'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{ARMType_Code}</font>&nbsp; {ARMType_Name}',
						'</div></tpl>'
					),
					triggerAction: 'all',
					valueField: 'ARMType_SysNick',
					width: 400,
					xtype: 'swbaselocalcombo'
				}, {
					layout: 'column',
					style: 'margin-left: 155px;',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'button',
							handler: function () {
								this.doSearch();
							}.createDelegate(this),
							iconCls: 'search16',
							text: BTN_FRMSEARCH
						}]
					}, {
						layout: 'form',
						style: 'margin-left: 10px;',
						items: [{
							xtype: 'button',
							handler: function () {
								win.doReset();
							},
							iconCls: 'resetsearch16',
							text: BTN_FRMRESET
						}]
					}]
				}]
			}]
		});

		this.Grid = new sw.Promed.ViewFrame({
			useEmptyRecord: false,
			region: 'center',
			layout: 'fit',
			autoLoadData: false,
			object: 'ARMType',
			dataUrl: '/?c=User&m=loadOnlineUsersList',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			startGroup: new Ext.XTemplate(
				'<div id="{groupId}" class="x-grid-group {cls}">',
				'<div id="{groupId}-hd" class="x-grid-group-hd" style="{style} {[ values.rs[0].data["ARMType_Name"] != "Всего"?"":"display:none;" ]}"><div>', '<b>{[ values.rs[0].data["ARMType_Name"] ]}</b>' ,'</div></div>',
				'<div id="{groupId}-bd" class="x-grid-group-body">'
			),
			groupingView: {showGroupName: true, showGroupsText: true},
			groupField: 'ARMType_Name',
			stringfields: [
				{name: 'OnlineUsers_id', type: 'int', header: 'ID', key: true},
				{name: 'ARMType_Name', type: 'string', header: 'АРМ', width: 100, group: true, sort: true},
				{name: 'Org_Nick', type: 'string', header: 'Организация', width: 100, id: 'autoexpand'},
				{name: 'Users_Count', type: 'int', header: 'Количество пользователей', width: 200}
			],
			onLoadData: function() {
				this.getAction('action_actualdate').setText('Актуальность данных на ' + new Date().format('H:i:s d.m.Y'));
			},
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			]
		});

		this.Grid.ViewGridStore.sortData = function(f, direction){
			direction = direction || 'ASC';
			var st = this.fields.get(f).sortType;
			var fn = function(r1, r2) {
				if (r1.data['OnlineUsers_id'] < 0 || r2.data['OnlineUsers_id'] < 0) {
					if (r1.data['OnlineUsers_id'] < r2.data['OnlineUsers_id']) {
						return (direction == 'ASC')?1:-1;
					} else {
						return (direction == 'ASC')?-1:1;
					}
				}
				var v1 = st(r1.data[f]), v2 = st(r2.data[f]);
				return v1 > v2 ? 1 : (v1 < v2 ? -1 : 0);
			};
			this.data.sort(direction, fn);
			if(this.snapshot && this.snapshot != this.data){
				this.snapshot.sort(direction, fn);
			}
		};

		this.Grid.getGrid().view.getRowClass = function (row, index) {
			var cls = '';

			if (row.get('OnlineUsers_id') == -2) { // всего
				cls = cls+'x-grid-rowbold ';
			}

			if (cls.length == 0) {
				cls = 'x-grid-panel';
			}

			return cls;
		};

		this.CenterPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [
				this.Grid
			]
		});

		Ext.apply(this, {
			layout: 'border',
			items: [
				this.FilterPanel,
				this.CenterPanel
			]
		});

		sw.Promed.swOnlineUsersWindow.superclass.initComponent.apply(this, arguments);
	}
});