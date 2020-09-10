/**
 * swPersonIdentPackagePosHistoryWindow - окно для отображения истории идентифации человека в ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.04.2018
 */
/*NO PARSE JSON*/

sw.Promed.swPersonIdentPackagePosHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonIdentPackagePosHistoryWindow',
	title: 'История идентификации в ТФОМС',
	maximizable: false,
	maximized: true,
	layout: 'border',

	openableEvnClassList: ['EvnPL','EvnSection'],

	openEvnWindow: function(PersonIdentPackagePos_id) {
		var package_pos_grid = this.PackagePosGridPanel.getGrid();
		var record = package_pos_grid.getStore().getById(PersonIdentPackagePos_id);
		if (!record) return;

		var Evn_id = record.get('Evn_id');
		var Evn_pid = record.get('Evn_pid');
		var Evn_rid = record.get('Evn_rid');
		var EvnClass_SysNick = record.get('EvnClass_SysNick');
		var Person_id = record.get('Person_id');

		if (!Evn_id || !EvnClass_SysNick.inlist(this.openableEvnClassList)) {
			return;
		}

		switch(EvnClass_SysNick) {
			case 'EvnPL':
				getWnd('swEvnPLEditWindow').show({
					action: 'view',
					EvnPL_id: Evn_id
				});
				break;
			case 'EvnSection':
				getWnd('swEvnSectionEditWindow').show({
					action: 'view',
					Person_id: Person_id,
					formParams: {
						EvnSection_id: Evn_id,
						EvnSection_pid: Evn_pid,
						Person_id: Person_id
					}
				});
				break;
		}
	},

	show: function() {
		sw.Promed.swPersonIdentPackagePosHistoryWindow.superclass.show.apply(this, arguments);

		var grid = this.GridPanel.getGrid();
		grid.removeAll();

		this.Person_id = arguments[0].Person_id;

		var params = {
			Person_id: this.Person_id,
			start: 0,
			limit: 30
		};

		grid.getStore().load({params: params});
	},

	initComponent: function() {
		var wnd = this;

		var identRangeRenderer = function(value, meta, record) {
			var period = [];
			if (!Ext.isEmpty(record.get('PersonIdentPackagePos_identDT')))
				period.push(Ext.util.Format.date(record.get('PersonIdentPackagePos_identDT'), 'd.m.Y'));
			if (!Ext.isEmpty(record.get('PersonIdentPackagePos_identDT2')))
				period.push(Ext.util.Format.date(record.get('PersonIdentPackagePos_identDT2'), 'd.m.Y'));
			return period.join(' - ');
		};

		var evnTpl = new Ext.Template(
			'{EvnClass}<br/><span class="{linkCls}" onClick="Ext.getCmp(\'{wndId}\').openEvnWindow({Record_id})">{Evn_id}</span>'
		);
		var evnRenderer = function(value, meta, record) {
			if (Ext.isEmpty(record.get('Evn_id'))) {
				return '';
			}
			var params = {
				wndId: wnd.getId(),
				linkCls: String(record.get('EvnClass_SysNick')).inlist(wnd.openableEvnClassList)?'fake-link':'',
				Record_id: record.get('PersonIdentPackagePos_id'),
				EvnClass: record.get('EvnClass_Name'),
				Evn_id: record.get('Evn_id')
			};
			return evnTpl.apply(params);
		};

		this.GridPanel = new sw.Promed.ViewFrame({
			dataUrl: '/?c=PersonIdentPackage&m=loadPersonIdentPackagePosHistoryGrid',
			border: true,
			autoLoadData: false,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			paging: true,
			stringfields: [
				{name: 'PersonIdentPackagePos_id', type: 'int', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonIdentPackagePos_identDT', type: 'date', hidden: true},
				{name: 'PersonIdentPackagePos_identDT2', type: 'date', hidden: true},
				{name: 'Evn_id', type: 'int', hidden: true},
				{name: 'EvnClass_Name', type: 'string', hidden: true},
				{name: 'EvnClass_SysNick', type: 'string', hidden: true},
				{name: 'IdentRange', header: 'Период идентификации', width: 140, renderer: identRangeRenderer},
				{name: 'PersonIdentPackageTool_Name', header: 'Источник запроса', type: 'string', width: 180},
				{name: 'PersonIdentState_Name', header: 'Статус', type: 'string', width: 180},
				{name: 'PersonIdentPackagePos_updDT', header: 'Дата установки статуса', type: 'date', width: 140},
				{name: 'Errors', header: 'Ошибки', type: 'string', id: 'autoexpand'},
				{name: 'Evn', header: 'Случай лечения', width: 240, renderer: evnRenderer},
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});

		Ext.apply(this,{
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
				this.GridPanel
			]
		});

		sw.Promed.swPersonIdentPackagePosHistoryWindow.superclass.initComponent.apply(this, arguments);
	}
});