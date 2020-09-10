/**
* swDrugRequestPropertyViewWindow - окно просмотра переченей списков медикаментов для заявки  
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
sw.Promed.swDrugRequestPropertyViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spiski_medikamentov_dlya_zayavki'],
	layout: 'border',
	id: 'DrugRequestPropertyViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	changeYear: function(value) {
		var year_field = this.WindowToolbar.items.get(3);
		var val = year_field.getValue();
		if (!val || value == 0)
			val = (new Date()).getFullYear();
		year_field.setValue(val+value);
	},
	doSearch: function(clear, default_values) {
		var year_field = this.WindowToolbar.items.get(3);

		if (clear) {
			year_field.setValue(null);
		}
		if (default_values) {
			this.changeYear(0);
		}

		var params = new Object();
		params.Year = year_field.getValue();
		params.limit = 100;
		params.start =  0;
		
		this.DrugRequestPropertyGrid.removeAll();
		this.DrugRequestPropertyGrid.loadData({
			globalFilters: params
		});
	},
	armFilter:function(){
		var grid = this.DrugRequestPropertyGrid;

		if (haveArmType('mekllo') || haveArmType('minzdravdlo') || haveArmType('zakup') || getGlobalOptions().superadmin == true) {
			grid.readOnly = false;
		} else {
			grid.readOnly = true;
		}
        if (this.closeActions == true) {
			grid.readOnly=true;
		}
		grid.ViewActions.action_drpv_actions.items[0].menu.items.items[0].setDisabled(grid.readOnly);
	},
	show: function() {  
		var wnd = this;
		sw.Promed.swDrugRequestPropertyViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		this.center();
		this.maximize();
        this.closeActions = false;
        this.ARMType = null;

        if(arguments[0]) {
            if(arguments[0].onlyView){
                this.closeActions = true;
            }
            if(arguments[0].ARMType){
                this.ARMType = arguments[0].ARMType;
            }
        }
		this.doSearch(true, true);
        
        this.DrugRequestPropertyGrid.addActions(
            {
                name : 'action_editList',
				text: lang['redaktirovanie_spiska'],
				tooltip: lang['redaktirovanie_spiska_medikamentov'],
				disabled:wnd.DrugRequestPropertyGrid.readOnly,
				handler: function() {
					var record = wnd.DrugRequestPropertyGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequestProperty_id') > 0) {
						getWnd('swDrugListRequestViewWindow').show({
							DrugRequestProperty_id: record.get('DrugRequestProperty_id'),
							DrugRequestProperty_Name: record.get('DrugRequestProperty_Name'),
							owner: wnd,
							typeWnd:"edit"
						});
					}
				},
				iconCls: 'edit16'
			}        
        );  
        
        this.DrugRequestPropertyGrid.addActions(
		    {
		        name : 'action_viewList',
				text: lang['prosmotr_spiska'],
				tooltip: lang['prosmotr_spiska_medikamentov'],
				
				handler: function() {
					var record = wnd.DrugRequestPropertyGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequestProperty_id') > 0) {
						getWnd('swDrugListRequestViewWindow').show({
							DrugRequestProperty_id: record.get('DrugRequestProperty_id'),
							DrugRequestProperty_Name: record.get('DrugRequestProperty_Name'),
							owner: wnd,
							typeWnd:"view"
						});
					}
				},
				iconCls: 'edit16'
			}       
        );
        
        this.DrugRequestPropertyGrid.addActions(
		    {
		        name : 'action_copyList', 
				text: lang['kopirovanie_spiska'],
				tooltip: lang['kopirovanie_spiska'],
                hidden: (wnd.ARMType == 'spesexpertllo' || wnd.ARMType == 'adminllo'),
				handler: function() {
					var record = wnd.DrugRequestPropertyGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequestProperty_id') > 0) {
						getWnd('swDrugRequestPropertyEditWindow').show({
							action: 'add',
							callback: wnd.DrugRequestPropertyGrid.refreshRecords,
							owner: wnd.DrugRequestPropertyGrid,
							OriginalDrugRequestProperty_id: record.get('DrugRequestProperty_id')
						});
					}
				},
				iconCls: 'add16'
			}           
        );
        /**
         * end
         */              
        
		this.DrugRequestPropertyGrid.addActions({
			name:'action_drpv_actions',
			text:lang['deystviya'],
            hidden: true,
			menu: [{
				text: lang['redaktirovanie_spiska_medikamentov'],
				tooltip: lang['redaktirovanie_spiska_medikamentov'],
				disabled:wnd.DrugRequestPropertyGrid.readOnly,
				handler: function() {
					var record = wnd.DrugRequestPropertyGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequestProperty_id') > 0) {
						getWnd('swDrugListRequestViewWindow').show({
							DrugRequestProperty_id: record.get('DrugRequestProperty_id'),
							DrugRequestProperty_Name: record.get('DrugRequestProperty_Name'),
							owner: wnd,
							typeWnd:"edit"
						});
					}
				},
				iconCls: 'edit16'
			},
		    {
				text: lang['prosmotr_spiska_medikamentov'],
				tooltip: lang['prosmotr_spiska_medikamentov'],
				
				handler: function() {
					var record = wnd.DrugRequestPropertyGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequestProperty_id') > 0) {
						getWnd('swDrugListRequestViewWindow').show({
							DrugRequestProperty_id: record.get('DrugRequestProperty_id'),
							DrugRequestProperty_Name: record.get('DrugRequestProperty_Name'),
							owner: wnd,
							typeWnd:"view"
						});
					}
				},
				iconCls: 'edit16'
			},
		    {
				text: lang['kopirovanie_spiska'],
				tooltip: lang['kopirovanie_spiska'],
                hidden: (wnd.ARMType == 'spesexpertllo' || wnd.ARMType == 'adminllo'),
				handler: function() {
					var record = wnd.DrugRequestPropertyGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequestProperty_id') > 0) {
						getWnd('swDrugRequestPropertyEditWindow').show({
							action: 'add',
							callback: wnd.DrugRequestPropertyGrid.refreshRecords,
							owner: wnd.DrugRequestPropertyGrid,
							OriginalDrugRequestProperty_id: record.get('DrugRequestProperty_id')
						});
					}
				},
				iconCls: 'add16'
			}],
			iconCls: 'actions16'
		});
		wnd.armFilter();
		this.getLoadMask().hide();		
	},
	initComponent: function() {
		var wnd = this;

		this.WindowToolbar = new Ext.Toolbar({
			items: [{
				xtype: 'button',
				disabled: true,
				text: lang['god']
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function() {
					wnd.changeYear(-1);
					wnd.doSearch();
				}.createDelegate(this)
			}, {
				xtype : "tbseparator"
			}, {
				xtype : 'numberfield',
				allowDecimal: false,
				allowNegtiv: false,
				width: 35,
				enableKeyEvents: true,
                id : 'numberfieldYear',
				listeners: {
					'keydown': function (inp, e) {
						if (e.getKey() == Ext.EventObject.ENTER) {
							e.stopEvent();
							wnd.doSearch();
						}
					}
				}
			}, {
				xtype : "tbseparator"
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function() {
					wnd.changeYear(1);
					wnd.doSearch();
				}.createDelegate(this)
			}, {
				xtype: 'tbfill'
			}]
		});
		
		this.DrugRequestPropertyGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=DrugRequestProperty&m=delete'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugRequestProperty&m=loadList',
			height: 180,
			region: 'center',
			object: 'DrugRequestProperty',
			editformclassname: 'swDrugRequestPropertyEditWindow',
			id: 'drpvDrugRequestPropertyGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequestProperty_id', type: 'int', header: 'ID', key: true},				
				{name: 'DrugRequestProperty_Name', type: 'string', id: 'autoexpand', header: lang['naimenovanie_spiska_medikamentov'], width: 120},
				{name: 'DrugRequestPeriod_Name', type: 'string', header: lang['rabochiy_period'], width: 200},
				{name: 'PersonRegisterType_Name', type: 'string', header: lang['tip_spiska'], width: 200},
				{name: 'DrugFinance_Name', type: 'string', header: lang['istochnik_finansirovaniya'], width: 200},
				{name: 'Mnn_Count', type: 'string', header: lang['kol-vo_mnn'], width: 200}
			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugRequestProperty_id') > 0){
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_edit.setDisabled(this.readOnly || record.get('Mnn_Count') > 0);
					this.ViewActions.action_delete.setDisabled(this.readOnly);
				}else{
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
				this.ViewActions.action_add.setDisabled(this.readOnly);
				 
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
			tbar: this.WindowToolbar,
			items:[this.DrugRequestPropertyGrid]
		});
		sw.Promed.swDrugRequestPropertyViewWindow.superclass.initComponent.apply(this, arguments);		
	}	
});