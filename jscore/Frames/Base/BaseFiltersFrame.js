/**
 * sw.Promed.BaseFiltersPanel - Обёртка для фильтров на формах
 *
 *
 * @project  PromedWeb
 * @copyright  (c) Swan Ltd, 2013
 * @package frames
 * @author  Dmitry Vlasenko (в большей части copy-pasted от BaseSearchFiltersPanel, by Петухов Иван aka Lich)
 * @class sw.Promed.BaseFiltersPanel
 * @extends Ext.form.FormPanel
 * @version 09.03.2013
 */

/**
 * Подключение фрейма фильтров
	this.FilterPanel = getBaseFiltersFrame({
		ownerWindow: this,
		toolBar: this.WindowToolbar,
		items: [{
			...
		}]
	});
 */
 /**
 * @config {String} id Идентификатор самой панели, для последующего обращения к ней
 */
 /**
 * @config {Object} ownerWindow Окно, на которое добавляется панель фильтров
 */
 /**
 * @config {Array} items Массив, содержащий содержимое панели фильтров
 */

function getBaseFiltersFrame( config ) {
	return new sw.Promed.BaseFiltersFrame( config );
}

/**
 * Сам фрейм с фильтрами
 */
sw.Promed.BaseFiltersFrame = Ext.extend(Ext.form.FormPanel, {
	autoScroll: true,
	bodyBorder: false,
	border: false,
	buttonAlign: 'left',
	frame: false,
	autoHeight: true,
	onExpand: Ext.emptyFn,
	onCollapse: Ext.emptyFn,
	/**
	 * Получение ссылки на окно, на котором находится форма
	 * @return {Ext.Window}
	 */
	getOwnerWindow: function () {
		return this.ownerWindow
	},
	initComponent: function() {
		var pan = this;

		this.MainPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			autoHeight: true,
			title: lang['najmite_na_zagolovok_chtobyi_svernut_razvernut_panel_filtrov'],
			region: 'center',
			bodyStyle: 'padding: 5px;',
			defaults: { border: false, bodyStyle: 'padding: 0px;' },
			autoScroll: true,
			tbar: this.toolBar,
			plain: true,
			items: this.items,
			listeners: {
				collapse: function(p) {
					pan.setHeight('25px');
					pan.ownerWindow.doLayout();
					pan.ownerWindow.syncSize();
					if (typeof pan.onCollapse == 'function') {
						pan.onCollapse();
					}
				},
				expand: function(p) {
					pan.ownerWindow.doLayout();
					pan.ownerWindow.syncSize();
					if (typeof pan.onExpand == 'function') {
						pan.onExpand();
					}
				}
			}
		});
		
		Ext.apply(this, {
			items: [ this.MainPanel ],
			keys:
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					if ('doSearch' in pan) {
						pan.doSearch();
					} else if ('doSearch' in pan.getOwnerWindow()) {
						pan.getOwnerWindow().doSearch();
					}
				},
				stopEvent: true
			}]
		});

		sw.Promed.BaseFiltersFrame.superclass.initComponent.apply(this, arguments);
	},
	labelAlign: 'right',
	labelWidth: 130,
	layout: 'fit',
	region: 'north'
});