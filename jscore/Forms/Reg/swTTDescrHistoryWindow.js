/**
* swTTDescrHistoryWindow - История изменения примечаний  по биркам поликлиники/параклиники/стационара
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      25.04.2013
*/

sw.Promed.swTTDescrHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 500,
	id: 'TTDescrHistoryWindow',
	
	/**
	 * Объект грида
	 */
	HistoryGrid: null,
	
	/**
	 * Объект параметров сервера
	 */
	params: null,
	
	
	initComponent: function() {

		this.HistoryDescrGrid = new sw.Promed.ViewFrame(
		{
			id: 'TTDH_HistoryGrid',
			object: 'TimetableExtend',
			dataUrl: C_TT_DESCR_HISTORY,
			height:303,
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'TimetableExtendHist_id', hidden: true, type: 'int', header: 'ID', key: true},
				{name: 'TimetableExtendHist_insDT', type: 'datetimesec', header: lang['data_izmeneniya'], width: 120},
				{name: 'PMUser_Name', type: 'string', header: lang['operator'], width: 200},
				{name: 'TimetableExtend_Descr', type: 'string', header: lang['primechanie'], id: 'autoexpand'}
			],
			actions:
			[
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true},
				{name:'action_refresh',
					handler: function() {
						this.HistoryDescrGrid.loadData(this.params);
					}.createDelegate(this)
				},
				{name:'action_print'}
			]
		});
			
		Ext.apply(this, {
			buttons: [
				HelpButton(this),
				{
					handler: function() {
						this.ownerCt.returnFunc();
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
				this.HistoryDescrGrid
			],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					this.hide();
				},
				key: [ Ext.EventObject.P ],
				scope: this,
				stopEvent: true
			}]
		});
		sw.Promed.swTTDescrHistoryWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 500,
	minWidth: 800,
	modal: true,
	plain: true,
	resizable: true,
	returnFunc: Ext.emptyFn,
	show: function() {
		sw.Promed.swTTDescrHistoryWindow.superclass.show.apply(this, arguments);
		
		this.params = null;
		
		if (arguments[0]['params']) {
			this.params = {
				globalFilters: arguments[0]['params']['globalFilters']
			}
		}
		
		this.HistoryDescrGrid.loadData(this.params);

		this.onHide = Ext.emptyFn;
	},
	title: lang['istoriya_izmeneniya_primechaniy_po_birke'],
	width: 800
});
