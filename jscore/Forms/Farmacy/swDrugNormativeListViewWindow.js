/**
* swDrugNormativeListViewWindow - окно редактирования cправочника «Нормативные перечни лекарственных средств»
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      10.2012
* @comment      
*/
sw.Promed.swDrugNormativeListViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Справочник «Нормативные перечни лекарственных средств»',
	layout: 'border',
	id: 'DrugNormativeListViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSearch: function() {
		var params = new Object();
		params.limit = 100;
		params.start =  0;
		
		this.DrugNormativeListGrid.removeAll();
		this.DrugNormativeListGrid.loadData({
			globalFilters: params
		});
	},
	copyDrugNormativeList: function(DrugNormativeList_id, callback){
		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert(lang['oshibka'], lang['kopirovanie_ne_udalos']);
			},
			params:{
				DrugNormativeList_id: DrugNormativeList_id
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if(result[0] && callback && getPrimType(callback) == 'function') {
					callback(result[0]);
				}
			},
			url:'/?c=DrugNormativeList&m=copyDrugNormativeList'
		});	
	},
	armFilter:function(){
		var grid = this.DrugNormativeListGrid;
		var msf_store = sw.Promed.MedStaffFactByUser.store;

		if(msf_store.findBy(function(rec) { return rec.get('ARMType') == 'mekllo'; }) > -1 || msf_store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) > -1 || getGlobalOptions().superadmin==true) {
			grid.readOnly=false;
		} else {
			grid.readOnly=true;
		}
        if (this.closeActions == true) {
			grid.readOnly=true;
		}
        grid.ViewActions.action_dnlv_actions.items[0].menu.items.items[0].setDisabled(grid.readOnly);
	},
	show: function() {  
		var wnd = this;
		
		sw.Promed.swDrugNormativeListViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		this.center();
		this.maximize();
        this.closeActions = false;
        if(arguments[0])
        {
            if(arguments[0].onlyView){
                this.closeActions = true;
            }
        }
		this.doSearch();
		this.DrugNormativeListGrid.addActions({
			name:'action_copy',
			text:lang['kopirovat'],
			tooltip: lang['redaktirovanie_spiska_medikamentov_normativnogo_perechnya'],
			handler: function() {
				var record = wnd.DrugNormativeListGrid.getGrid().getSelectionModel().getSelected();
				if (record.get('DrugNormativeList_id') > 0) {
					wnd.copyDrugNormativeList(record.get('DrugNormativeList_id'), function(data){
						if (data.DrugNormativeList_id && data.DrugNormativeList_id > 0) {
							wnd.DrugNormativeListGrid.refreshRecords(null,0);
							getWnd('swDrugNormativeListEditWindow').show({
								action: 'edit',
								DrugNormativeList_id: data.DrugNormativeList_id,
								edit_after_copy: (data.DrugNormativeListSpec_count > 0),
								callback: function() {
									wnd.DrugNormativeListGrid.refreshRecords(null,0);
								}
							});
						}
					});
				}
			},
			iconCls: 'copy16'
		});
        
        /**
         * https://redmine.swan.perm.ru/issues/63972
         */
        
        this.DrugNormativeListGrid.addActions(
            {
                name:'action_editList',
				text: lang['redaktirovanie_spiska_ls'],
				tooltip: lang['redaktirovanie_spiska_lekarstvennyih_sredstv_normativnogo_perechnya'],
				disabled:this.DrugNormativeListGrid.readOnly,
				handler: function() {
					var record = wnd.DrugNormativeListGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugNormativeList_id') > 0) {
						getWnd('swDrugNormativeListSpecViewWindow').show({
							DrugNormativeList_id: record.get('DrugNormativeList_id'),
							DrugNormativeList_BegDT: record.get('DrugNormativeList_BegDT'),
							DrugNormativeList_EndDT: record.get('DrugNormativeList_EndDT'),
							typeWnd:"edit",
							onSave: function() {
								wnd.DrugNormativeListGrid.refreshRecords(null,0);
							}
						});
					}
				},
				iconCls: 'edit16'
			}        
        ); 
        
        this.DrugNormativeListGrid.addActions(
		    {
		        name:'action_viewList',
				text: lang['prosmotr_spiska_ls'],
				tooltip: lang['prosmotr_spiska_lekarstvennyih_sredstv_normativnogo_perechnya'],
				
				handler: function() {
					var record = wnd.DrugNormativeListGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugNormativeList_id') > 0) {
						getWnd('swDrugNormativeListSpecViewWindow').show({
							DrugNormativeList_id: record.get('DrugNormativeList_id'),
							DrugNormativeList_BegDT: record.get('DrugNormativeList_BegDT'),
							DrugNormativeList_EndDT: record.get('DrugNormativeList_EndDT'),
							typeWnd:"view",
							onSave: function() {
								wnd.DrugNormativeListGrid.refreshRecords(null,0);
							}
						});
					}
				},
				iconCls: 'edit16'
			}        
        );
        
        /**
         * end
         */
        
		this.DrugNormativeListGrid.addActions({
			name:'action_dnlv_actions',
			text:lang['deystviya'],
            hidden : true,
			menu: [{
				text: lang['redaktirovanie_spiska_lekarstvennyih_sredstv_normativnogo_perechnya'],
				tooltip: lang['redaktirovanie_spiska_lekarstvennyih_sredstv_normativnogo_perechnya'],
				disabled:this.DrugNormativeListGrid.readOnly,
				handler: function() {
					var record = wnd.DrugNormativeListGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugNormativeList_id') > 0) {
						getWnd('swDrugNormativeListSpecViewWindow').show({
							DrugNormativeList_id: record.get('DrugNormativeList_id'),
							DrugNormativeList_BegDT: record.get('DrugNormativeList_BegDT'),
							DrugNormativeList_EndDT: record.get('DrugNormativeList_EndDT'),
							typeWnd:"edit",
							onSave: function() {
								wnd.DrugNormativeListGrid.refreshRecords(null,0);
							}
						});
					}
				},
				iconCls: 'edit16'
			},
		    {
				text: lang['prosmotr_spiska_lekarstvennyih_sredstv_normativnogo_perechnya'],
				tooltip: lang['prosmotr_spiska_lekarstvennyih_sredstv_normativnogo_perechnya'],
				
				handler: function() {
					var record = wnd.DrugNormativeListGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugNormativeList_id') > 0) {
						getWnd('swDrugNormativeListSpecViewWindow').show({
							DrugNormativeList_id: record.get('DrugNormativeList_id'),
							DrugNormativeList_BegDT: record.get('DrugNormativeList_BegDT'),
							DrugNormativeList_EndDT: record.get('DrugNormativeList_EndDT'),
							typeWnd:"view",
							onSave: function() {
								wnd.DrugNormativeListGrid.refreshRecords(null,0);
							}
						});
					}
				},
				iconCls: 'edit16'
			}],
			iconCls: 'actions16'
		});
		wnd.armFilter();
		this.getLoadMask().hide();
	},
	initComponent: function() {
		var wnd = this;		
		
		this.DrugNormativeListGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=DrugNormativeList&m=delete'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugNormativeList&m=loadList',
			height: 180,
			region: 'center',
			object: 'DrugNormativeList',
			editformclassname: 'swDrugNormativeListEditWindow',
			id: 'slvwDrugNormativeListGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugNormativeList_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugNormativeList_Name', type: 'string', id: 'autoexpand', header: lang['naimenovanie_perechnya'], width: 120},
				{name: 'PersonRegisterType_Name', type: 'string', header: lang['tip'], width: 200},
				{name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['programma_llo'], width: 200},
				{name: 'DrugNormativeList_BegDT', type: 'date', header: lang['data_nachala'], width: 120},
				{name: 'DrugNormativeList_EndDT', type: 'date', header: lang['data_okonchaniya'], width: 120},
				{name: 'DrugNormativeListSpec_count', type: 'int', header: 'count', hidden: true, width: 120}
			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugNormativeList_id') > 0){
				    this.ViewActions.action_view.setDisabled(false);
				    this.ViewActions.action_edit.setDisabled(this.readOnly);
				    this.ViewActions.action_delete.setDisabled(this.readOnly);
				    this.ViewActions.action_copy.setDisabled(this.readOnly);
				}else{
				    this.ViewActions.action_view.setDisabled(true);
				    this.ViewActions.action_edit.setDisabled(true);
				    this.ViewActions.action_delete.setDisabled(true);
				    this.ViewActions.action_copy.setDisabled(true);
				}
				this.ViewActions.action_add.setDisabled(this.readOnly);
				
			},
			params: {
				callback: function() {
					wnd.DrugNormativeListGrid.refreshRecords(null,0);
				}
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.DrugNormativeListGrid]
		});
		sw.Promed.swDrugNormativeListViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});