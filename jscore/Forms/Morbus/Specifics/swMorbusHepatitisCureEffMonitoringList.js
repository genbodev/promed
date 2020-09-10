/**
* swMorbusHepatitisCureEffMonitoringList - Мониторинг эффективности лечения. Список.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      24.05.2012
*/

sw.Promed.swMorbusHepatitisCureEffMonitoringList = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
	title: lang['monitoring_effektivnosti_lecheniya'],
	openWindow: function(gridName, action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('sw'+gridName+'Window').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uje_otkryito']);
			return false;
		}

		var grid = this.findById('MHCEMW_'+gridName).getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			
			grid.getStore().load();
		}
		params.formMode = 'remote';
		params.formParams = new Object();

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
			params.formParams.MorbusHepatitisCure_id = this.MorbusHepatitisCure_id;
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.MorbusHepatitisCureEffMonitoring_id = selected_record.data.MorbusHepatitisCureEffMonitoring_id;
			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('sw'+gridName+'Window').show(params);
		
	},
	show: function() 
	{
		sw.Promed.swMorbusHepatitisCureEffMonitoringList.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		
		var grid = this.findById('MHCEMW_MorbusHepatitisCureEffMonitoring'); 

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].MorbusHepatitisCure_id) 
			this.MorbusHepatitisCure_id = arguments[0].MorbusHepatitisCure_id;
		else 
			this.MorbusHepatitisCure_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.MorbusHepatitisCure_id ) && ( this.MorbusHepatitisCure_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
			arguments[0].accessType = 'view';

		if (arguments[0].accessType == 'edit') 	{
			this.findById('MHCEMW_MorbusHepatitisCureEffMonitoring').setReadOnly(false);
		} else {
			this.findById('MHCEMW_MorbusHepatitisCureEffMonitoring').setReadOnly(true);
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();	

		grid.loadData({
			params: {MorbusHepatitisCure_id: this.MorbusHepatitisCure_id},
			globalFilters: {MorbusHepatitisCure_id: this.MorbusHepatitisCure_id, callback: null}
		});

		loadMask.hide();
		current_window.doLayout();
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			id: 'FormPanel',
			items: 
			[new sw.Promed.ViewFrame({
				actions: [
					{name: 'action_add', handler: function() {
						this.openWindow('MorbusHepatitisCureEffMonitoring', 'add');
					}.createDelegate(this)},
					{name: 'action_edit', handler: function() {
						this.openWindow('MorbusHepatitisCureEffMonitoring', 'edit');
					}.createDelegate(this)},
					{name: 'action_view', handler: function() {
						this.openWindow('MorbusHepatitisCureEffMonitoring', 'view');
					}.createDelegate(this)},
					{name: 'action_delete'},
					{name: 'action_print'}
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				border: false,
				dataUrl: '/?c=MorbusHepatitisCureEffMonitoring&m=loadList',
				collapsible: true,
				id: 'MHCEMW_MorbusHepatitisCureEffMonitoring',
				paging: false,
				style: 'margin-bottom: 10px',
				object: 'MorbusHepatitisCureEffMonitoring',
				stringfields: [
					{name: 'MorbusHepatitisCureEffMonitoring_id', type: 'int', header: 'ID', key: true},
					{name: 'RecordStatus_Code', type: 'int', hidden: true},
					{name: 'HepatitisCurePeriodType_id', type: 'string', hidden: true},
					{name: 'HepatitisCurePeriodType_Name', type: 'string', header: lang['srok_lecheniya'], width: 240},
					{name: 'HepatitisQualAnalysisType_id', type: 'string', hidden: true},
					{name: 'HepatitisQualAnalysisType_Name', type: 'string', header: lang['kachestvennyiy_analiz'], width: 240},
					{name: 'MorbusHepatitisCureEffMonitoring_VirusStress', type: 'string', header: lang['virusnaya_nagruzka'], width: 120}
				],
				toolbar: true
			})]
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusHepatitisCureEffMonitoringList.superclass.initComponent.apply(this, arguments);
	}
});