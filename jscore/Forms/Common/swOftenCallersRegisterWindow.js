/**
* Журнал часто обращающихся в СМП пациентов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Miyusov Alexandr
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      26.10.2012
*/

sw.Promed.swOftenCallersRegisterWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['registr_chasto_obraschayuschihsya'],
	iconCls: 'rpt-report',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	objectName: 'swOftenCallersRegisterWindow',
	closeAction: 'hide',
	id: 'swOftenCallersRegisterWindow',
	objectSrc: '/jscore/Forms/Common/swOftenCallersRegisterWindow.js',
	
	getPersonHistory: function() {
		if (this.OftenCallersGridPanel.getGrid().getSelectionModel().getCount() != 1)
			return false;
		
		var record = this.OftenCallersGridPanel.getGrid().getSelectionModel().getSelected();
		if(!record)
			return false;
		
		log({'record':Ext.globalOptions});
		var param = {
			ARMType : 'common',			
			Person_id: record.data.Person_id,
			readOnly: true,
			owner: this,
			userMedStaffFact: {MedStaffFact_id: 0}
		}
//		log(Ext.globalOptions);
		if (getRegionNick() != 'ufa'){
			getWnd('swPersonEmkWindow').show(param)
		}
	},
	show: function()
	{
		sw.Promed.swOftenCallersRegisterWindow.superclass.show.apply(this, arguments);
		
		this.OftenCallersGridPanel.getGrid().getTopToolbar().items.last().hide();

		if (getRegionNick() == 'ufa') {
			Ext.getCmp('id_action_view').hide()
		}
		if(this.isSmpAdminRegion){
			this.lpu_id.setFieldValue('Lpu_id', arguments[0].Lpu_id ? arguments[0].Lpu_id : sw.Promed.MedStaffFactByUser.current.Lpu_id)
		}
		this.reloadOftenCallersGrid();

	},
	

	doReset: function()
	{

	},
	
	reloadOftenCallersGrid: function()
	{
		var store = this.OftenCallersGridPanel.getGrid().getStore();

		if(this.isSmpAdminRegion){
			store.baseParams.Lpu_id = this.lpu_id.getValue();
		}
		store.load();
	},
	
	
	
	deleteMessages: function()
	{
		var win = this;
		var record = this.OftenCallersGridPanel.getGrid().getSelectionModel().getSelected();
		if(!record)
			return false;
			
		var selections = this.OftenCallersGridPanel.getGrid().getSelectionModel().getSelections();
		var OftenCallers_ids = [];

		for	(var key in selections) {
			if (selections[key].data) {
				OftenCallers_ids.push(selections[key].data['OftenCallers_id']);
			}
		}
		
		var params = {}
		params.OftenCallers_ids = Ext.util.JSON.encode(OftenCallers_ids);
		Ext.Ajax.request({
			params: params,
			url: '/?c=OftenCallers&m=deleteFromOftenCallers',
			callback: function(opt, success, resp)
			{
				if (success)
				{
					win.reloadOftenCallersGrid();
				}
			}
		});
	},
	
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.OftenCallersGridPanel = new sw.Promed.ViewFrame(
		{
		//	title:lang['registr_chasto_obraschayuschihsya'],
			focusOnFirstLoad:false,
			region: 'center',
			selectionModel: 'multiselect',
			dataUrl: '/?c=OftenCallers&m=getOftenCallers',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			paging: false,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			id: 'OftenCallersGridPanelViewFrame',
			
			onDblClick: Ext.emptyFn(),
			onCellClick: function(grid, rIndex, cIndex){

			},
			onRowSelect: function(sm,rowIdx,record) {
				log(this.ViewActions.action_delete);
				if (record.data.onDelete != 2) {
					if (sm.getCount()>1) {
						sm.deselectRow(rowIdx);
					}
					else {
						this.ViewActions.action_delete.setDisabled(true);
					}
				}
				else {
					this.ViewActions.action_delete.setDisabled(false);
				}

			},
			onRowDeSelect: function(sm) {

			},
			stringfields:
			[
				{name: 'OftenCallers_id', type: 'int', key: true, hidden: true, hideable: false},
				{name: 'Person_id', type: 'int', hidden: true, hideable: false},
				{name: 'onDelete', type: 'int', hidden: true, hideable: false},
				{name: 'Lpu_Nick', header: langs('МО'), hidden: true, hideable: true, type: 'string', width: 300},
				{name: 'Person_Fio', header: lang['fio_patsienta'], type: 'string', width: 300},
				{name: 'Adress_Name', header: lang['adres_poslednego_vyizova'], width: 300},
				{name: 'CmpReason_Name', header: lang['povod_poslednego_vyizova'], width: 300},
				{name: 'CmpCallCard_prmDate', header: lang['data_poslednego_vyizova'],renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), width: 150}
				
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: getRegionNick() == 'ufa' ?true: false, disabled: true, handler: this.getPersonHistory.createDelegate(this)},
				{name:'action_delete', hidden: false, disabled: true, handler: this.deleteMessages.createDelegate(this)},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', hidden: false, disabled: false}
			]
		});
		
		this.OftenCallersGridPanel.ViewGridModel.renderer = function (v,p,record) {
			if (record.data.onDelete == 2) {
				return '<div class="x-grid3-row-checker">&#160;</div>'
			}
			return '';
		}
		
		this.OftenCallersGridPanel.ViewGridModel.on('selectionchange', function(obj) {
			if (obj.getCount()<1) { 
				this.grid.ownerCt.ownerCt.ViewActions.action_view.setDisabled(true);
				this.grid.ownerCt.ownerCt.ViewActions.action_delete.setDisabled(true);
			}
			else {
				if (obj.getCount()==1) {
					this.grid.ownerCt.ownerCt.ViewActions.action_view.setDisabled(false);
				}
			}
		});

		this.isSmpAdminRegion = isUserGroup('smpAdminRegion');

		this.lpu_id = new sw.Promed.swlpuwithopersmpcombo({
			emptyText : langs('МО'),
			allowBlank: true
		});

		this.windowToolbar = new Ext.Toolbar({
			hidden: !this.isSmpAdminRegion,
			items:[
				{
					xtype: 'label',
					style: 'margin-left: 7px; margin-right: 3px',
					text: 'МО:'
				},
				this.lpu_id,
				{
					xtype: 'tbfill'
				},
				{
					xtype: 'button',
					text: langs('Найти'),
					iconCls: 'search16',
					handler: function () {
						cur_win.reloadOftenCallersGrid();
					}
				}, {
					xtype: 'button',
					text: langs('Сброс'),
					iconCls: 'resetsearch16',
					handler: function () {
						cur_win.lpu_id.clearValue()
					}
				}]
		});
		
		Ext.apply(this,
		{
			layout: 'border',
			items: [
				this.OftenCallersGridPanel
			],
			tbar: this.windowToolbar,
			buttons: [
				'-',
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event)
					{
						ShowHelp(this.ownerCt.title);
					}
				}, {
					text      : BTN_FRMCLOSE,
					tabIndex  : -1,
					tooltip   : lang['zakryit'],
					iconCls   : 'cancel16',
					handler   : function() {
						this.hide();
					}.createDelegate(this)
				}
			],
			defaults:
			{
				bodyStyle: 'background: #DFE8F6;'
			}
		});
		sw.Promed.swOftenCallersRegisterWindow.superclass.initComponent.apply(this, arguments);
	}
});