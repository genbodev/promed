//Компонент для работы быстрых фильтров по всем записям
//Для работы компонента необходимо указать в initStore - fields, sorters и proxy
//Компонент создает 2 стора
//bigGridStore - хранит в себе данные все данные из БД
//store - загружает данные из bigGridStore для показа 1 страницы
//Работа быстрых фильтров происходит по bigGridStore

Ext.define('sw.gridPanelTwoStore', {
	extend: 'Ext.grid.Panel',
	alias: 'widget.gridPanelTwoStore',
	name: 'gridPanelTwoStore',
	initComponent: function () {
		var me = this;
		me.mask = new Ext.LoadMask(me, {msg: "Пожалуйста, подождите..."});

		me.store = new Ext.data.Store({
			fields: me.initStore.fields,
			sorters: me.initStore.sorters,
			pageSize: 100,
			proxy: {
				type: 'memory',
				enablePaging: true
			}
		});

		me.bbar = Ext.create('Ext.PagingToolbar', {
			store: me.store,
			displayInfo: true,
			beforePageText: 'Страница',
			afterPageText: 'из {0}',
			displayMsg: 'показано {0} - {1} из {2}',
			doRefresh : function(){
				me.bigGridStore.reload();
			},
		});

		me.bigGridStore = new Ext.data.Store({
			fields: me.initStore.fields,
			autoLoad: false,
			stripeRows: true,
			sorters: me.initStore.sorters,
			proxy: me.initStore.proxy,
			listeners: {
				beforeload: function () {
					me.mask.show();
				},
				load: function () {
					me.mask.hide();
				},
				datachanged: function () {
					if (me.bigGridStore.getRange() == 0) {
						me.store.loadData([], false);
					} else {
						me.store.proxy.data = me.bigGridStore.getRange();
						me.store.currentPage = 1;
						me.store.load();
					}
				}
			}
		});
		me.callParent(arguments);
	}
});