/**
 * swRegistryESStorageViewWindow - окно просмотра реестров ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Mse
 * @access            public
 * @copyright        Copyright (c) 2014 Swan Ltd.
 * @author            Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version            26.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swRegistryESStorageViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRegistryESStorageViewWindow',
	width: 640,
	height: 800,
	maximized: true,
	maximizable: false,
	layout: 'border',
	title: 'Номера ЭЛН',
	show: function() {
		sw.Promed.swRegistryESStorageViewWindow.superclass.show.apply(this, arguments);

		var win = this;
		this.RegistryESStorageGrid.loadData();
	},
	openRegistryESStorageQueryWindow: function() {
		var win = this;

		getWnd('swRegistryESStorageQueryWindow').show({
			callback: function() {
				win.RegistryESStorageGrid.loadData();
			}
		});
	},
	initComponent: function() {
		var win = this;

		this.RegistryESStorageGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=RegistryESStorage&m=loadRegistryESStorageGrid',
			uniqueId: true,
			border: true,
			autoLoadData: false,
			object: 'RegistryESStorage',
			region: 'center',
			stringfields: [
				{name: 'RegistryESStorage_id', type: 'int', header: 'RegistryESStorage_id', key: true, hidden: true},
				{name: 'RegistryESStorage_NumQuery', type: 'string', group: true, sort: true, direction: 'DESC', header: 'Номер запроса', width: 120},
				{name: 'EvnStickBase_Num', type: 'string', header: 'Номер ЭЛН', width: 120},
				{name: 'RegistryESStorage_updDT', type: 'date', header: 'Дата запроса', width: 120},
				{name: 'RegistryESStorage_IsUsed', header: 'Использован', renderer: function(value, cellEl, rec) {
					if (value == 2) {
						return 'Да';
					}

					return '';
				}, width: 120},
				{name: 'pmUser_Name', type: 'string', header: 'Пользователь', id: 'autoexpand'}
			],
			showCountInTop: false,
			onRenderGrid: function() {
				win.RegistryESStorageGrid.getGrid().getTopToolbar().add({
					text: 'Свободно номеров ЭЛН: 0',
					xtype: 'tbtext'
				});
			},
			actions: [
				{ name: 'action_add', text: 'Создать запрос', handler: function() {
					this.openRegistryESStorageQueryWindow()
				}.createDelegate(this)},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			onLoadData: function() {
				var count = 0;

				win.RegistryESStorageGrid.getGrid().getStore().findBy(function(rec) {
					if (rec.get('RegistryESStorage_IsUsed') == 1) {
						count++;
					}
				});

				win.RegistryESStorageGrid.getGrid().getTopToolbar().items.last().el.innerHTML = 'Свободно номеров ЭЛН: ' + count;
			},
			onRowSelect: function (sm, index, record) {
			}.createDelegate(this)
		});

		Ext.apply(this, {
			items: [
				this.RegistryESStorageGrid
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swRegistryESStorageViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
