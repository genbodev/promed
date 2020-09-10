/**
 * swSURMOSettingsWindow - окно настройки МО из СУР для импорта данных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.01.2019
 */
/*NO PARSE JSON*/

sw.Promed.swSURMOSettingsWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSURMOSettingsWindow',
	layout: 'border',
	title: langs('Настройки сервиса'),
	maximizable: false,
	maximized: true,

	doSave: function() {
		var wnd = this;
		var grid = wnd.GridPanel.getGrid();

		var saveData = Object.keys(wnd.changes).map(function(id) {
			return wnd.changes[id];
		});

		var params = {
			saveData: Ext.util.JSON.encode(saveData)
		};

		var mask = wnd.getLoadMask('Сохранение...');
		mask.show();

		Ext.Ajax.request({
			url: '/?c=ServiceSUR&m=saveMOSettings',
			params: params,
			success: function(response) {
				mask.hide();
				var responseObj = Ext.util.JSON.decode(response.responseText);

				if (responseObj.success) {
					wnd.changes = {};
					wnd.doSearch();
					wnd.hide();
				}
			},
			failure: function(response) {
				mask.hide();
			}
		});
	},

	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();

		if (reset) base_form.reset();

		var params = base_form.getValues();

		grid.getStore().load({params: params});
	},

	cancelChanges: function() {
		var grid = this.GridPanel.getGrid();

		grid.getStore().each(function(record) {
			if (record.get('GetMO_ex') != record.get('GetMO_ex_original')) {
				record.set('GetMO_ex', record.get('GetMO_ex_original'));
				record.commit();
			}
		});

		this.changes = {};
	},

	toggleCheck: function(id) {
		var grid = this.GridPanel.getGrid();
		var record = grid.getStore().getById(id);

		if (!record || Ext.isEmpty(record.get('GetMO_id'))) {
			return;
		}

		record.set('GetMO_ex', record.get('GetMO_ex')?0:1);
		record.commit();
	},

	show: function() {
		sw.Promed.swSURMOSettingsWindow.superclass.show.apply(this, arguments);

		this.changes = {};

		this.doSearch(true);
	},

	initComponent: function() {
		var wnd = this;

		this.FilterPanel = new Ext.form.FormPanel({
			border: false,
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			frame: true,
			bodyStyle: 'padding-top: 5px;',
			enableKeyEvents: true,
			keys: [{
				fn: function() {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 40,
					items: [{
						xtype: 'textfield',
						name: 'FullNameRU',
						fieldLabel: 'МО',
						width: 300
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 70,
					items: [{
						xtype: 'textfield',
						name: 'MedCode',
						fieldLabel: 'Код МО',
						width: 80
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 40px;',
					items: [{
						xtype: 'button',
						iconCls: 'search16',
						text: langs('Найти'),
						handler: function() {
							this.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						iconCls: 'resetsearch16',
						text: langs('Сброс'),
						handler: function() {
							this.doSearch(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.checkRenderer = function(value, meta, record) {
			var checked = value?' checked="checked"':'';
			var onclick = 'onClick="Ext.getCmp(\''+wnd.id+'\').toggleCheck('+record.get('GetMO_id')+');"';

			return '<input type="checkbox" '+checked+' '+onclick+'>';
		};

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'SLLW_ServiceListLogGrid',
			dataUrl: '/?c=ServiceSUR&m=loadMOListForSettings',
			border: false,
			autoLoadData: false,
			useEmptyRecord: false,
			toolbar: false,
			region: 'center',
			bodyStyle: 'border-top: 1px solid #99bbe8; border-bottom: 1px solid #99bbe8;',
			stringfields: [
				{name: 'GetMO_id', type: 'int', header: 'ID', key: true},
				{name: 'GetMO_ex_original', type: 'int', hidden: true},
				{name: 'MedCode', header: 'Код МО', type: 'string', width: 70},
				{name: 'FullNameRU', header: 'МО', type: 'string', id: 'autoexpand'},
				{name: 'GetMO_ex', header: 'Не использовать при выгрузке из СУР', renderer: this.checkRenderer, width: 220}
			]
		});

		this.GridPanel.getGrid().getStore().on('load', function(store, records) {
			records.forEach(function(record, index) {
				var id = record.get('GetMO_id');
				if (wnd.changes[id]) {
					record.set('GetMO_ex', wnd.changes[id].GetMO_ex);
					record.commit();
				}
			});
		});

		this.GridPanel.getGrid().getStore().on('update', function(store, record, operation) {
			if (operation != 'edit') return;

			var id = record.get('GetMO_id');

			if (record.get('GetMO_ex') == record.get('GetMO_ex_original')) {
				delete wnd.changes[id];
			} else {
				wnd.changes[id] = {
					GetMO_id: id,
					GetMO_ex: record.get('GetMO_ex')
				};
			}
		});

		Ext.apply(this, {
			buttons: [
				{
					text: langs('Сохранить'),
					iconCls: 'save16',
					handler: function () {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text: langs('Отменить'),
					iconCls: 'cancel16',
					handler: function () {
						this.cancelChanges();
					}.createDelegate(this)
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: langs('Закрыть')
				}
			],
			items: [
				this.FilterPanel,
				this.GridPanel
			]
		});

		sw.Promed.swSURMOSettingsWindow.superclass.initComponent.apply(this, arguments);
	}
});