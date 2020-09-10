/**
* swDrugRequestSynchronizeWindow - окно синхронизации списка медикаментов с нормативным перечнем 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      17.09.2012
* @comment      
*/
sw.Promed.swDrugRequestSynchronizeWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['sinhronizatsiya_spiska_medikamentov_dlya_zayavki_s_normativnyim_perechenem'],
	layout: 'border',
	id: 'DrugRequestSynchronizeWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 188,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	onSave: Ext.emptyFn,
	doSave: function() {
		var wnd = this;
		var data = wnd.DrugGrid.getChangedData();
		if (data.length > 0) {
			wnd.onSave(data);
			this.hide();
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['spisok_izmeneniy_pust']);
		}
		return true;		
	},
	doSynchronize: function(DrugNormativeList_id) {
		var params = new Object();
		params.limit = 100;
		params.start =  0;
		
		this.DrugGrid.removeAll();
		if (DrugNormativeList_id > 0) {
			params.DrugNormativeList_id = DrugNormativeList_id;

			if (this.DrugComplexMnn_id_list && this.DrugComplexMnn_id_list != '')
				params.DrugComplexMnn_id_list = this.DrugComplexMnn_id_list;
			else
				params.DrugComplexMnn_id_list = null;

			this.DrugGrid.loadData({
				globalFilters: params
			});
		}
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestSynchronizeWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		this.action = (arguments[0].action) ? arguments[0].action : 'add';
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		} else {
			wnd.onSave = Ext.emptyFn;
		}
		if ( arguments[0].DrugComplexMnn_id_list ) {
			this.DrugComplexMnn_id_list = arguments[0].DrugComplexMnn_id_list;
		} else {
			this.DrugComplexMnn_id_list = null;
		}
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		this.list_combo.getStore().load();
		this.list_combo.setValue(null);
		wnd.doSynchronize();
		
		loadMask.hide();
	},
	initComponent: function() {
		var wnd = this;		
		
		this.DrugGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_refresh', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugRequestProperty&m=loadSynchronizeList',
			height: 280,
			region: 'center',
			id: 'drswDrugGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugListRequest_id', type: 'int', header: 'ID', hidden: true, key: true},
				{name: 'state', type: 'string', header: 'state', hidden: true},
				{name: 'action_name', type: 'string', header: lang['deystvie'], width: 100},
				{name: 'DrugComplexMnn_id', type: 'string', header: lang['id_mnn'], hidden: true},
				{name: 'DrugComplexMnn_Code', type: 'string', hidden: true},
				{name: 'ATX_CODE_list', type: 'string', hidden: true},
				{name: 'STRONGGROUPID', type: 'string', hidden: true},
				{name: 'NARCOGROUPID', type: 'string', hidden: true},
				{name: 'ClsDrugForms_Name', type: 'string', hidden: true},
				{name: 'DrugComplexMnn_RusName', type: 'string', header: lang['mnn'], id: 'autoexpand', width: 160},
				{name: 'DrugComplexMnnName_Name', hidden: true},
				{name: 'DrugComplexMnnDose_Name', hidden: true},
				{name: 'DrugComplexMnnFas_Name', hidden: true},
				{name: 'NTFR_Name', hidden: true},
				{name: 'checked', header: lang['vyibrat'], width: 110, renderer: sw.Promed.Format.checkColumn}
                
			],
			title: lang['medikamentyi'],
			toolbar: false,			
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.getGrid().getStore().clearFilter();
				this.getGrid().getStore().each(function(record) {
					if (record.get('checked')) {
						data.push(record.data);
					}
				});
				return data;
			},
			onDblClick: function(grid) {
				var record = grid.getSelectionModel().getSelected();
				record.set('checked', !record.get('checked'));
				record.commit();
			}
		});
		
		this.list_combo = new Ext.form.ComboBox({
			mode: 'local',
			store: new Ext.data.JsonStore({
				url: '/?c=DrugNormativeList&m=loadList',
				key: 'DrugNormativeList_id',
				autoLoad: false,
				fields: [
					{name: 'DrugNormativeList_id',    type:'int'},
					{name: 'DrugNormativeList_Name',  type:'string'}
				],
				sortInfo: {
					field: 'DrugNormativeList_Name'
				}
			}),
			displayField:'DrugNormativeList_Name',
			valueField: 'DrugNormativeList_id',
			fieldLabel: lang['perechen'],
			triggerAction: 'all',
			width: 500,
			tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'{DrugNormativeList_Name}'+
			'</div></tpl>',
			listeners: {
				'select': function(combo) {					
					wnd.doSynchronize(combo.getValue());
				}
			}
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,			
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'drseDrugRequestSynchronizeForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 70,
				collapsible: true,
				items: [this.list_combo]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			bodyStyle: 'padding: 7px;',
			buttons:
			[{
				handler: function() 
				{
					var loadMask = new Ext.LoadMask(wnd.getEl(), {msg:lang['zagruzka']});
					loadMask.show();
					wnd.doSave();
					loadMask.hide();
				},
				iconCls: 'save16',
				text: lang['vnesti_izmeneniya']
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form,this.DrugGrid]
		});
		sw.Promed.swDrugRequestSynchronizeWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('drseDrugRequestSynchronizeForm').getForm();
	}	
});