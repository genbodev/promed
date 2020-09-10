/**
* swTTHistoryWindow - История изменения бирок поликлиники/параклиники/стационара
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      18.10.2011
*/

sw.Promed.swTTHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 500,
	id: 'TTHistoryWindow',
	
	/**
	 * Объект грида
	 */
	HistoryGrid: null,
	
	/**
	 * Объект параметров сервера
	 */
	params: null,
	
	
	initComponent: function() {

		this.HistoryGrid = new sw.Promed.ViewFrame(
		{
			id: 'TTH_HistoryGrid',
			object: 'TimetableHist',
			dataUrl: C_TTG_HISTORY, //на самом деле задаётся динамически
			height:303,
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'TimetableHist_id', hidden: true, type: 'int', header: 'ID', key: true},
				{name: 'TimetableHist_insDT', type: 'datetimesec', header: lang['data_izmeneniya'], width: 120},
				{name: 'PMUser_Name', type: 'string', header: lang['operator']},
				{name: 'TimetableActionType_Name', type: 'string', header: lang['deystvie'], width: 150},
				{name: 'Person_FIO', type: 'string', header: lang['fio_patsienta'], width: 200},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya']},
				{name: 'TimetableType_Name', type: 'string', header: lang['sostoyanie']}
			],
			actions:
			[
				{name:'action_add', hidden: true},
				{name:'action_edit', 
					tooltip: lang['prosmotr_istorii_primechaniy_f4'],
					handler: function() {
						getWnd('swTTDescrHistoryWindow').show({
							params: this.params,
							callback: function() {
								//
							}.createDelegate(this)
						});
					}.createDelegate(this),
					text: lang['istoriya_primechaniy']
				},
				{name:'action_view', 
					tooltip: lang['prosmotr_vsey_istorii_na_vremya_f3'],
					handler: function() {
						this.params.globalFilters['ShowFullHistory'] = 1;
						this.HistoryGrid.loadData(this.params);
					}.createDelegate(this),
					text: lang['polnaya_istoriya']
				},
				{name:'action_delete', hidden: true},
				{name:'action_refresh',
					handler: function() {
						this.HistoryGrid.loadData(this.params);
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
				this.HistoryGrid
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
		sw.Promed.swTTHistoryWindow.superclass.initComponent.apply(this, arguments);
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
		sw.Promed.swTTHistoryWindow.superclass.show.apply(this, arguments);
		
		this.params = null;
		
		if (arguments[0]['TimetableGraf_id']) {
			this.params = {
				TimetableGraf_id: arguments[0]['TimetableGraf_id'],
				globalFilters: {
					TimetableGraf_id: arguments[0]['TimetableGraf_id']
				},
				url: C_TTG_HISTORY
			}
		}
		
		if (arguments[0]['TimetableStac_id']) {
			this.params = {
				TimetableStac_id: arguments[0]['TimetableStac_id'],
				globalFilters: {
					TimetableStac_id: arguments[0]['TimetableStac_id']
				},
				url: C_TTS_HISTORY
			}
		}
		
		if (arguments[0]['TimetableMedService_id']) {
			this.params = {
				TimetableMedService_id: arguments[0]['TimetableMedService_id'],
				globalFilters: {
					TimetableMedService_id: arguments[0]['TimetableMedService_id']
				},
				url: C_TTMS_HISTORY
			}
		}

		if (arguments[0]['TimetableResource_id']) {
			this.params = {
				TimetableResource_id: arguments[0]['TimetableResource_id'],
				globalFilters: {
					TimetableResource_id: arguments[0]['TimetableResource_id']
				},
				url: C_TTR_HISTORY
			}
		}
		
		this.params.globalFilters['ShowFullHistory'] = null;
		
		this.HistoryGrid.loadData(this.params);

		this.onHide = Ext.emptyFn;
	},
	title: lang['istoriya_izmeneniya_birki'],
	width: 800
});
