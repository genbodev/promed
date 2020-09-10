/**
 * swPersonRequestDataViewWindow - окно просмотра истории статусов идентификации человека в ЦС ЕРЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			30.05.2016
 */
/*NO PARSE JSON*/

sw.Promed.swPersonRequestDataViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonRequestDataViewWindow',
	layout: 'fit',
	title: 'История идентификации в ' + (getRegionNick() == 'perm' ? 'ЦС' : (getRegionNick() == 'msk' ? 'АС' : 'РС')) + ' ЕРЗ',
	maximizable: true,
	maximized: true,
	width: 800,

	openEvnEditWindow: function(PersonRequestData_id) {
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!Ext.isEmpty(PersonRequestData_id)) {
			record = grid.getStore().getById(PersonRequestData_id);
		}

		if (!record || Ext.isEmpty(record.get('Evn_id'))) {
			return;
		}

		var wndName = '';
		var params = {action: 'view'};
		switch(record.get('EvnClass')) {
			case 'EvnPL':
				wndName = 'swEvnPLEditWindow';
				params.EvnPL_id = record.get('Evn_id');
				break;
			case 'EvnPS':
				wndName = 'swEvnPSEditWindow';
				params.EvnPS_id = record.get('Evn_id');
				break;
			case 'CmpCallCard':
				wndName = 'swCmpCallCardNewShortEditWindow';
				params.formParams = {CmpCallCard_id: record.get('Evn_id')};
				break;
		}
		if (Ext.isEmpty(wndName)) {
			return;
		}

		getWnd(wndName).show(params);
	},

	show: function() {
		sw.Promed.swPersonRequestDataViewWindow.superclass.show.apply(this, arguments);

		this.Person_id = null;

		var grid = this.GridPanel.getGrid();

		grid.removeAll();

		if (!arguments[0] || !arguments[0].Person_id) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_byili_peredanyi_neobhodimyie_parametryi']);
		}
		this.Person_id = arguments[0].Person_id;

		grid.getStore().load({
			params: {
				Person_id: this.Person_id,
				start: 0,
				limit: 100
			}
		});
	},

	initComponent: function() {
		var evnLinkRenderer = function(value, meta, record) {
			if (Ext.isEmpty(value)) return value;

			var style = 'color: #000079;text-decoration: underline;cursor: pointer;';
			var onclick = 'getWnd(\'swPersonRequestDataViewWindow\').openEvnEditWindow('+record.get('PersonRequestData_id')+')';

			return '<span style="'+style+'" onclick="'+onclick+'">'+value+'</span>';
		};

		var NoIdentCauseHidden = getRegionNick().inlist(['penza']);

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'PRDVW_GridPanel',
			dataUrl: '/?c=Person&m=loadPersonRequestDataGrid',
			border: false,
			autoLoadData: false,
			paging: true,
			root: 'data',
			auditOptions: {
				schema: 'erz',
				field: 'PersonRequestData_id', // поле в таблице БД
				key: 'PersonRequestData_id'    // поле в сторе грида
			},
			stringfields: [
				{name: 'PersonRequestData_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Evn_id', type: 'int', hidden: true},
				{name: 'EvnClass', type: 'string', hidden: true},
				{name: 'PersonRequestSourceType_id', type: 'int', hidden: true},
				{name: 'PersonRequestSourceType_Name', header: 'Источник запроса', type: 'string', width: 240, id: NoIdentCauseHidden?'autoexpand':''},
				{name: 'PersonRequestData_insDT', header: 'Дата запроса', type: 'datetime', width: 120},
				{name: 'PersonRequestData_csDT', header: 'Дата идентификации', type: 'datetime', width: 120},
				{name: 'PersonRequestDataStatus_Name', header: 'Статус', type: 'string', width: 250},
				{name: 'NoIdentCause', header: 'Ошибка', type: 'string', id: 'autoexpand', hidden: NoIdentCauseHidden},
				{name: 'Evn_Name', header: 'Документ', width: 220, renderer: evnLinkRenderer, hidden: getRegionNick().inlist(['vologda'])}
			],
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true}
			]
		});

		Ext.apply(this,{
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.GridPanel]
		});

		sw.Promed.swPersonRequestDataViewWindow.superclass.initComponent.apply(this, arguments);
	}
});