/**
* swNarcoReviseListWindow- окно просмотра, добавления и редактирования организаций
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Registry
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       swanuser (info@swan.ru)
* @version      23.07.2014
* @comment      comment
*/
sw.Promed.swNarcoReviseListWindow = Ext.extend(sw.Promed.BaseForm, {
	
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['jurnal_ucheta_sverok_dannyih_s_prokuraturoy_rh'],
	draggable: true,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	width: 700,
	height: 500,
	modal: true,
	resizable: false,
	maximized: true,
	id:'NarcoReviseListWindow',
	onCancel: Ext.emptyFn,
	action:'edit',
	callback: Ext.emptyFn,
	plain: true,
	show: function() {
		sw.Promed.swNarcoReviseListWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.ReviseListDataLink.addActions({
			name:'actions',
			key: 'actions',
			text:lang['deystviya'], 
			tooltip: lang['deystviya'],
			menu: [
				new Ext.Action({name:'print_act', text:lang['pechat_akta_sverki'], tooltip: lang['pechat_akta_sverki'], handler: function() {win.printResult()}}),
				new Ext.Action({name:'expand_all', text:lang['vyigruzka_provedennoy_sverki'], tooltip: lang['vyigruzka_provedennoy_sverki'], handler: function() {win.exportResult()}})
			],
			iconCls : 'x-btn-text',
			icon: 'img/icons/actions16.png',
			handler: function() {}
		});
		this.doSearch();
		this.center();
		this.maximize();
	},
	printResult:function(){
		var win  = this;
		var grid = win.ReviseList.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ReviseList_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		var ReviseList_id = selected_record.data.ReviseList_id;
		if(ReviseList_id) {
			printBirt({
				'Report_FileName': 'ReviseAktsverki.rptdesign',
				'Report_Params': '&paramReviseList=' + ReviseList_id,
				'Report_Format': 'pdf'
			});
			return false;
		}
		return true;
	},
	exportResult:function(){
		var win  = this;
		var grid = win.ReviseList.getGrid();
		var params = {};
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ReviseList_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		params.ReviseList_id = selected_record.data.ReviseList_id;
		getWnd('swExportReviseWindow').show(params);
		
	},
	doReset: function() {
		var form = this.filterPanel.getForm(),
			grid = this.ReviseList.getGrid();
		form.reset();
		grid.getStore().baseParams = {};
		this.ReviseList.removeAll(true);
		this.ReviseListDataLink.removeAll(true);
		grid.getStore().removeAll();
		this.doSearch();
	},
	doSearch: function(id) 
	{
		var Record={};
		var grid = this.ReviseList.getGrid();
		var form = this.filterPanel.getForm();
			//grid = this.viewFrame.getGrid(),
		var	params = form.getValues();
		
		grid.getStore().removeAll();
		this.ReviseListDataLink.removeAll(true);
		grid.getStore().load({params:params,callback:function(){
		if(id){
			
				var index = grid.getStore().findBy(function(record, idd){return idd == id;});
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
		}
		}});
	},
	openWindow:function(type){
		var win  = this;
		var grid = win.ReviseList.getGrid();
		var params = {}; 
		switch(type){
			case'add':
				params.action = type;
				getWnd('swNarcoReviseEditWindow').show(params);
				break;
			case 'edit':
			case 'view':
				if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ReviseList_id') )
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
					return false;
				}
				var selected_record = grid.getSelectionModel().getSelected();
				params.action = type;
				params.callback = Ext.emptyFn;
				params.ReviseList_id = selected_record.data.ReviseList_id;
				getWnd('swNarcoReviseEditWindow').show(params);
				break;
		}
		
	},
	delReviseList:function(){
		var win  = this;
		var grid = win.ReviseList.getGrid();
		var params = {}; 
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ReviseList_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		params.ReviseList_id = selected_record.data.ReviseList_id;
		Ext.Msg.show({
			title: lang['vopros'],
			msg: lang['udalit_vyibrannuyu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie']).show();
					Ext.Ajax.request({
						url: '/?c=NarcoRevise&m=deleteReviseList',
						params: params,
						callback: function(options, success, response) {
							this.getLoadMask().hide();
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success )
									grid.getStore().remove(selected_record);
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});	
	},
	delReviseListDataLink:function(){
		var win  = this;
		var grid = win.ReviseListDataLink.getGrid();
		var ReviseList = win.ReviseList.getGrid();
		var params = {}; 
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('ReviseListDataLink_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		var revise_select = ReviseList.getSelectionModel().getSelected();
		params.ReviseListDataLink_id = selected_record.data.ReviseListDataLink_id;
		params.ReviseList_id = selected_record.data.ReviseList_id;
		Ext.Msg.show({
			title: lang['vopros'],
			msg: lang['udalit_vyibrannuyu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie']).show();
					Ext.Ajax.request({
						url: '/?c=NarcoRevise&m=deleteReviseListDataLink',
						params: params,
						callback: function(options, success, response) {
							this.getLoadMask().hide();
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success ){
									grid.getStore().remove(selected_record);
									revise_select.set('ReviseList_MatchKolvo',obj.cnt);
									revise_select.commit();
								}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});	
	},
	initComponent: function() {
		var win = this;
		 var xg = Ext.grid;
		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'NarcoReviseListSearchForm',
			labelAlign: 'right',
			labelWidth: 160,
			region: 'north',
			items: [{
					fieldLabel: lang['data'],
					name: 'ReviseList_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				},{
					width:260,
					comboSubject: 'PermitType',
					hiddenName: 'PermitType_id',
					fieldLabel: lang['profil'],
					xtype: 'swcommonsprcombo'
				}, {
					width:260,
					fieldLabel:lang['ispolnitel'],
					name:'ReviseList_Performer',
					xtype:'textfield',
					value:getGlobalOptions().CurMedPersonal_FIO
				},{
					width:260,
					comboSubject: 'YesNo',
					hiddenName: 'isMatch',
					fieldLabel: lang['sovpadeniya'],
					xtype: 'swcommonsprcombo'
				}
			],
            buttons: [{
                handler: function() {
                    win.doSearch();
                },
                iconCls: 'search16',
                text: BTN_FRMSEARCH
            }, {
                handler: function() {
                    win.doReset();
					win.doSearch();
                },
                iconCls: 'resetsearch16',
                text: BTN_FRMRESET
            }],
            keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.ReviseList = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			frame: true,
			dataUrl: '/?c=NarcoRevise&m=loadNarcoReviseList',
			id: 'ReviseList',
			actions:
			[
				{name:'action_add', handler:function(){win.openWindow('add')}},
				{name:'action_edit',hidden:true},
				{name:'action_delete', handler:function(){win.delReviseList()}},
				{name:'action_view', handler:function(){win.openWindow('view')}},
				{name:'action_print',hidden:true}
			],
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{header: 'ID', type: 'int', name: 'ReviseList_id', key: true},
                {header: lang['nomer'],  type: 'string', name: 'ReviseList_Code', id: 'autoexpand', width: 170},
                {header: lang['data'],  type: 'date', name: 'ReviseList_setDate', width: 170},
				{header: lang['profil'],  type: 'string', name: 'PermitType_Name', width: 150},
				{header: lang['initsiator'],  type: 'string', name: 'Org_Nick', width: 140},
				{header: lang['zapisey_v_ishodnom_fayle'], type:'int', name: 'ReviseList_Kolvo', width: 170},
                {header: lang['sovpadeniya'],  type: 'int', name: 'ReviseList_MatchKolvo', width: 170},
                {header: lang['ispolnitel'],  type: 'string', name: 'ReviseList_Performer', width: 170}
			],
			toolbar: true,
			onRowSelect: function (sm, index, record) {
				var rec = sm.getSelected();
				if (rec.get('ReviseList_id') > 0) {
					var s = win.ReviseListDataLink.getGrid().getStore();
					s.removeAll();												
					s.baseParams = {
						ReviseList_id: rec.get('ReviseList_id'),
						start:0,
						limit:100
					};
					
					s.load({});											//загружаем грид с выбраным справочником
				}
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				var grid = this.getGrid();
				var sm = grid.getSelectionModel();
				var rec = sm.getSelected();
				if (rec.get('ReviseList_id') > 0) {
					win.openWindow('view')
				}
			},
			onEnter: function()
			{
				var grid = this.getGrid();
				var sm = grid.getSelectionModel();
				var rec = sm.getSelected();
				if (rec.get('ReviseList_id') > 0) {
					win.openWindow('view')
				}
			}
		});
		this.ReviseListDataLink = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			frame: true,
			paging:true,
			enableKeyEvents:true,
			autoLoadData: false,
			dataUrl: '/?c=NarcoRevise&m=loadNarcoReviseListDataLink',
			id: 'ReviseListDataLink',
			actions:
			[
				{name:'action_add',hidden:true},
				{name:'action_edit',hidden:true},
				{name:'action_view',hidden:true},
				{name:'action_delete', handler:function(){win.delReviseListDataLink()}},
				{name:'action_print', hidden:true},
				{name:'action_refresh',hidden:true}
			],
			totalProperty: 'totalCount',
			root: 'data',
			region: 'center',
			stringfields: [
				{header: lang['nomer_zapisi'],type: 'int', name: 'ReviseListDataLink_id',width: 100},
                //{ header: 'Название',  type: 'string', name: 'ReviseListData_ProcurId',width: 170 },
				{header: lang['indetifikator_cheloveka'],type: 'int', name: 'Person_id',width: 100,hidden:true},
				{header: lang['indetifikator_cheloveka'],type: 'int', name: 'Server_id',width: 100,hidden:true},
				{header: lang['indetifikator_cheloveka'],type: 'int', name: 'ReviseList_id',width: 100,hidden:true},
				{header: lang['nomer_zapisi'],type: 'int', name: 'ReviseListDataLink_id',width: 100,hidden:false},
				{header: lang['fio'],  type: 'string', name: 'Person_FIO',width: 170},
				{header: lang['pol'],  type: 'string', name: 'Person_Sex',width: 50},
				{header: lang['data_rojdeniya'],  type: 'date', name: 'Person_Birthday',width: 100},
				{header: lang['diagnoz'],  type: 'string', name: 'Diag_Name',width: 170},
				{header: lang['lpu'],  type: 'string', name: 'Lpu_Nick',width: 100},
				{header: lang['seriya_pasporta_grajdanina_rf'],  type: 'string', name: 'Document_Ser',width: 100},
				{header: lang['nomer_pasporta_grajdanina_rf'],  type: 'int', name: 'Document_Num',width: 100},
				{header: lang['status'],  type: 'string', name: 'PersonRegisterOutCause_Name',width: 170},
				{header: lang['data_vklyucheniya_v_registr'],  type: 'date', name: 'PersonRegister_setDate',width: 100},
				{header: lang['data_isklyucheniya_iz_registra'],  type: 'date', name: 'PersonRegister_disDate',width: 100},
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
              
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
			
				var grid = this.getGrid();
				var sm = grid.getSelectionModel();
				var rec = sm.getSelected();
				if (rec.get('Person_id') > 0&&rec.get('Server_id') >= 0) {
					var params = {
						Person_id:rec.get('Person_id'),
						Server_id:rec.get('Server_id')
					}
					ShowWindow('swPersonEditWindow', params);
					return false;
				}
			},
			onEnter: function()
			{
				
			}
		});
		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'WIN_ParameterValue_Alias',
				text: BTN_FRMCLOSE
			}],
			items: [ 
				this.filterPanel,
				this.ReviseList,
				this.ReviseListDataLink
			]
		});
		sw.Promed.swNarcoReviseListWindow.superclass.initComponent.apply(this, arguments);
        
	}
	
});