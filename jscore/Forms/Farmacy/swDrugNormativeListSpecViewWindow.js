/**
* swDrugNormativeListSpecViewWindow - окно редактирования списка лекарственных средств нормативного перечня
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
sw.Promed.swDrugNormativeListSpecViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spisok_lekarstvennyih_sredstv_normativnogo_perechnya'],
	layout: 'border',
	id: 'DrugNormativeListSpecViewWindow',
	modal: true,
	shim: false,
	width: 400,
	typeWnd:'view',
	resizable: false,
	maximizable: false,
	maximized: true,
	armReadOnly:false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	onSave: Ext.emptyFn,
	selectCheckboxAll: function(select) {
		var input_array = document.getElementById('dnslvDrugNormativeListSpecGrid').getElementsByTagName('input');
		for(var key in input_array) if (input_array[key].name == "spec_row") {
			input_array[key].checked = select;
		}
	},
	collectCheckboxValues: function() {
		var input_array = document.getElementById('dnslvDrugNormativeListSpecGrid').getElementsByTagName('input');
		var tbl_arr = new Array();
		for(var key in input_array) {
			var inp = input_array[key];
			if(inp.checked && inp.value) {
				tbl_arr.push(inp.value);
			}
		}
		return tbl_arr;
	},
	loadGrid: function() {
		var wnd = this;
		var params = new Object();
		params.limit = 100;
		params.start =  0;
		params.DrugNormativeList_id = wnd.DrugNormativeList_id;
		
		wnd.DrugNormativeListSpecGrid.removeAll();
		wnd.DrugNormativeListSpecGrid.loadData({
			globalFilters: params
		});
	},
	doSearch: function() {
		var wnd = this;
		var form = this.FilterPanel.getForm();
		var params = form.getValues();

		var mnn_code = params.RlsActmatters_Code != '' ? params.RlsActmatters_Code : null;
		var mnn_name = params.RlsActmatters_RusName != '' ? params.RlsActmatters_RusName : null;
		var torg_name = params.RlsTorg_Name != '' ? params.RlsTorg_Name : null;
		var drug_form = params.RlsClsdrugforms_Name != '' ? params.RlsClsdrugforms_Name : null;
		var narco = params.NARCOGROUPS_ID != 0 ? params.NARCOGROUPS_ID : null;
		var strong = params.STRONGGROUPS_ID != 0 ? params.STRONGGROUPS_ID : null;
		var atx_name = null;

		if (params.CLSATC_ID > 0) {
			var idx = form.findField('CLSATC_ID').getStore().findBy(function(rec) { return rec.get('RlsClsatc_id') == params.CLSATC_ID; });
			if (idx >= 0) {
				atx_name = form.findField('CLSATC_ID').getStore().getAt(idx).get('RlsClsatc_Name');
				atx_name = atx_name.substr(0,atx_name.indexOf(' '));
			}
		}

		this.DrugNormativeListSpecGrid.getGrid().getStore().filterBy(function(record){
			return (
				(mnn_code == null || record.get('DrugMnnCode_Code') == mnn_code) &&
				(mnn_name == null || record.get('RlsActmatters_RusName').indexOf(mnn_name) > -1) &&
				(torg_name == null || record.get('TorgName_NameList').indexOf(torg_name) > -1) &&
				(drug_form == null || record.get('DrugForm_NameList').indexOf(drug_form) > -1) &&
				(atx_name == null || record.get('ParentATX_Name').indexOf(atx_name) == 0) &&
				(narco == null || (narco == -1 && record.get('NARCOGROUPID') > 0) || (narco == -2 && record.get('NARCOGROUPID') <= 0) || (narco == record.get('NARCOGROUPID'))) &&
				(strong == null || (strong == -1 && record.get('STRONGGROUPID') > 0) || (strong == -2 && record.get('STRONGGROUPID') <= 0) || (strong == record.get('STRONGGROUPID')))
			);
		});
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.DrugNormativeListSpecGrid.getGrid().getStore().clearFilter();
	},
	doSave:  function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(Ext.get(wnd.id), {msg:lang['sohranenie']});
		loadMask.show();
		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert(lang['oshibka'], lang['sohranenie_ne_udalos']);
				loadMask.hide();
			},
			params:{
				DrugNormativeList_id: wnd.DrugNormativeList_id,
				DrugNormativeList_JsonData: wnd.DrugNormativeListSpecGrid.getJSONChangedData()
			},
			success: function (response) {
				wnd.onSave();
				loadMask.hide();
				wnd.hide();
			},
			url:'/?c=DrugNormativeList&m=saveDrugNormativeListSpecFromJSON'
		});	
		return true;		
	},
	armFilter:function(){
		var wnd = this;
		var grid = wnd.DrugNormativeListSpecGrid;
		var flag_idx = grid.getGrid().getColumnModel().findColumnIndex('check');
		var msf_store = sw.Promed.MedStaffFactByUser.store;

		if((msf_store.findBy(function(rec) { return rec.get('ARMType') == 'mekllo'; }) > -1 || msf_store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) > -1 || getGlobalOptions().superadmin == true) && this.typeWnd == "edit") {
			grid.readOnly = false;
			wnd.buttons[3].setText(BTN_FRMCANCEL);
			this.setTitle(lang['spisok_lekarstvennyih_sredstv_normativnogo_perechnya']);
		} else {
			this.setTitle(lang['spisok_lekarstvennyih_sredstv_normativnogo_perechnya_prosmotr']);
			grid.readOnly = true;
			wnd.buttons[3].setText(lang['zakryit']);
		}
		wnd.buttons[0].setDisabled(grid.readOnly);
		grid.getGrid().getColumnModel().setHidden(flag_idx, grid.readOnly);
		this.armReadOnly=grid.readOnly;
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugNormativeListSpecViewWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugNormativeListSpec_id = null;
		this.DrugNormativeList_BegDT = null;
		this.DrugNormativeList_EndDT = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if(arguments[0].typeWnd)this.typeWnd=arguments[0].typeWnd;
		this.action = (arguments[0].action) ? arguments[0].action : 'add';
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		} else {
			this.onSave = Ext.emptyFn;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugNormativeList_id ) {
			this.DrugNormativeList_id = arguments[0].DrugNormativeList_id;
		}
		if ( arguments[0].DrugNormativeList_BegDT ) {
			this.DrugNormativeList_BegDT = arguments[0].DrugNormativeList_BegDT;
		}
		if ( arguments[0].DrugNormativeList_EndDT ) {
			this.DrugNormativeList_EndDT = arguments[0].DrugNormativeList_EndDT;
		}

		this.FilterPanel.getForm().findField('CLSATC_ID').getStore().load({params: {maxCodeLength: 5}});
		this.FilterPanel.getForm().reset();
		document.getElementById('dnslvCheckbox').checked = '';
		wnd.loadGrid();
		wnd.armFilter();
	},
	initComponent: function() {
		var wnd = this;	
		
		this.DrugNormativeListSpecGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.DrugNormativeListSpecGrid.editRecord('add'); }},
				{name: 'action_edit', handler: function() { wnd.DrugNormativeListSpecGrid.editRecord('edit'); }},
				{name: 'action_view', handler: function() { wnd.DrugNormativeListSpecGrid.editRecord('view'); }},
				{name: 'action_refresh', disabled: true,hidden:true},
				{name: 'action_delete', handler: function() { wnd.DrugNormativeListSpecGrid.deleteRecord(); }},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugNormativeList&m=loadDrugNormativeListSpecList',
			height: 180,
			region: 'center',
			object: 'DrugNormativeListSpec',
			editformclassname: 'swDrugNormativeListSpecViewWindow',
			id: 'dnslvDrugNormativeListSpecGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugNormativeListSpec_id', type: 'int', header: 'ID', key: true},
				{name: 'state', type: 'string', header: 'state', hidden: true},
				{name: 'ParentATX_id', type: 'string', header: lang['id_ath'], hidden: true},
				{name: 'check', sortable: false, header: '<input id="dnslvCheckbox" type="checkbox" onClick="getWnd(\'swDrugNormativeListSpecViewWindow\').selectCheckboxAll(this.checked);" />', renderer: function(v, p, r) { return '<input type="checkbox" name="spec_row" value="'+r.get('DrugNormativeListSpec_id')+'"/>'; }, width: 38},
				{name: 'ParentATX_Name', type: 'string', header: lang['ath'], width: 200},
				{name: 'STRONGGROUPID', type: 'string', hidden: true},
				{name: 'NARCOGROUPID', type: 'string', hidden: true},
				{name: 'RlsActmatters_id', type: 'string', header: lang['id_mnn'], hidden: true},
				{name: 'DrugMnnCode_Code', type: 'string', header: lang['kod_mnn'], width: 160},
				{name: 'RlsActmatters_RusName', type: 'string', header: lang['mnn'], width: 160},
				{name: 'TorgNameArray', type: 'string', header: lang['torg_naim_id'], hidden: true},
				{name: 'DrugFormArray', type: 'string', header: lang['formyi_vyipuska_id'], hidden: true},
				{name: 'DrugForm_NameList', type: 'string', header: lang['formyi_vyipuska'], width: 200},
				{name: 'TorgName_NameList', type: 'string', header: lang['kod_torgovoe_naimenovanie'], id: 'autoexpand', width: 200},
				{name: 'DrugNormativeListSpec_DateRange', type: 'string', header: lang['period_deystviya_zapisi'], hidden: true},
				{name: 'DrugNormativeListSpec_BegDT', type: 'date', header: lang['nch_data'], hidden: true},
				{name: 'DrugNormativeListSpec_EndDT', type: 'date', header: lang['kn_data'], hidden: true},
				{name: 'DrugNormativeListSpec_IsVK', type: 'bool', header: lang['cherez_vk'], hidden: true}
			],
			title: lang['medikamentyi'],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugNormativeListSpec_id') > 0 || record.get('id_array') != ''){
				    this.ViewActions.action_view.setDisabled(false);
				    this.ViewActions.action_edit.setDisabled(this.readOnly);
				    this.ViewActions.action_delete.setDisabled(this.readOnly);
				}else{
				    this.ViewActions.action_view.setDisabled(true);
				    this.ViewActions.action_edit.setDisabled(true);
				    this.ViewActions.action_delete.setDisabled(true);
		
				}
				this.ViewActions.action_add.setDisabled(this.readOnly);	
			},
			editRecord: function (action) {
				var view_frame = this;
				var record = view_frame.getGrid().getSelectionModel().getSelected();
				var store = view_frame.getGrid().getStore();
				
				if (!record && action != 'add')
					return false;		
					
				var params = new Object();
				params.action = action;		
				if (record)
					params = Ext.apply(params, record.data);
				if (action == 'add') {
					var record_count = store.getCount();
					if ( record_count == 1 && !store.getAt(0).get('DrugNormativeListSpec_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}
					params.DrugNormativeList_BegDT = wnd.DrugNormativeList_BegDT;
					params.DrugNormativeList_EndDT = wnd.DrugNormativeList_EndDT;
					params.onSave = function(data) {
						if ( record_count == 1 && !store.getAt(0).get('DrugNormativeListSpec_id') ) {
							view_frame.removeAll({ addEmptyRecord: false });
						}										
						var record = new Ext.data.Record.create(view_frame.jsonData['store']);										
						var dt_str1 = data['DrugNormativeListSpec_BegDT'] != '' ? data['DrugNormativeListSpec_BegDT'].format('d.m.Y') : '';
						var dt_str2 = data['DrugNormativeListSpec_EndDT'] != '' ? data['DrugNormativeListSpec_EndDT'].format('d.m.Y') : '';

						view_frame.clearFilter();

						data.DrugNormativeListSpec_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
						data.DrugNormativeListSpec_BegDT = dt_str1;
						data.DrugNormativeListSpec_EndDT = dt_str2;
						data.DrugNormativeListSpec_DateRange = dt_str1 + ' - ' + dt_str2;
						data.DrugFormArray = data['DrugFormArray'].join(',');
						data.TorgNameArray = data['TorgNameArray'].join(',');						
						data.state = 'add';						
						
						store.insert(record_count, new record(data));

						view_frame.setFilter();

						this.hide();
					}
				}
				if (action == 'edit') {
					params.onSave = function(data) {
						var record = view_frame.getGrid().getSelectionModel().getSelected();
						var dt_str1 = data['DrugNormativeListSpec_BegDT'] != '' ? data['DrugNormativeListSpec_BegDT'].format('d.m.Y') : '';
						var dt_str2 = data['DrugNormativeListSpec_EndDT'] != '' ? data['DrugNormativeListSpec_EndDT'].format('d.m.Y') : '';

						view_frame.clearFilter();

						record.set('DrugNormativeListSpec_BegDT', data['DrugNormativeListSpec_BegDT']);
						record.set('DrugNormativeListSpec_EndDT', data['DrugNormativeListSpec_EndDT']);
						record.set('DrugNormativeListSpec_DateRange', dt_str1 + ' - ' + dt_str2);
						record.set('RlsActmatters_id', data['RlsActmatters_id']);
						record.set('RlsActmatters_RusName', data['RlsActmatters_RusName']);
						record.set('DrugFormArray', data['DrugFormArray'].join(','));
						record.set('TorgNameArray', data['TorgNameArray'].join(','));						
						record.set('DrugMnnCode_Code', data['DrugMnnCode_Code']);
						record.set('ATX_id', data['ATX_id']);
						record.set('ATX_Name', data['ATX_Name']);
						record.set('DrugForm_NameList', data['DrugForm_NameList']);
						record.set('TorgName_NameList', data['TorgName_NameList']);
						record.set('DrugNormativeListSpec_IsVK', data['DrugNormativeListSpec_IsVK']);

						if (record.get('state') != 'add') {
							record.set('state', 'edit');
						}
						record.commit();

						view_frame.setFilter();
						this.hide();
					}
				}
				
				getWnd('swDrugNormativeListSpecEditWindow').show(params);
			},
			deleteRecord: function(){
				var view_frame = this;
				var id_array = wnd.collectCheckboxValues();
				var id_str = ','+id_array.join(',')+',';
				var cnt = view_frame.getGrid().getStore().getCount();

				if (id_array.length > 0) {
					sw.swMsg.show({
						title: lang['podtverdite_deystvie'],
						msg: lang['vyi_uverenyi_chto_hotite_udalit_otmechennyie_zapisi'],
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								view_frame.getGrid().getStore().each(function(record) {
									if (id_str.indexOf(','+record.get('DrugNormativeListSpec_id')+',') >= 0) {
										if (record.get('state') == 'add') {
											view_frame.getGrid().getStore().remove(record);
										} else {
											record.set('state', 'delete');
											record.commit();
										}
									}
								});

								view_frame.setFilter();
								document.getElementById('dnslvCheckbox').checked = '';
								var count = view_frame.getGrid().getStore().getCount();
								if (view_frame.getGrid().getStore().getCount() > 0) {
									view_frame.focus();
								} else {
									view_frame.getGrid().getTopToolbar().items.last().el.innerHTML = '0 / 0';
								}
							}
						}
					});
				}
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete'))
						data.push(record.data);
				});
				this.setFilter();
				return data;
			},						
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() { //скрывает удаленные записи
				this.getGrid().getStore().filterBy(function(record){
					return (record.get('state') != 'delete');
				});
			}
		});

		//Вкладка "Наименование"
		this.FilterNamePanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				xtype: 'textfield',
				fieldLabel: lang['kod_mnn'],
				name: 'RlsActmatters_Code',
				width: 120
			}, {
				fieldLabel: lang['mnn'],
				anchor: '80%',
				name: 'RlsActmatters_RusName',
				xtype: 'textfield'
			}, {
				fieldLabel: lang['torg_naimenovanie'],
				anchor: '80%',
				name: 'RlsTorg_Name',
				xtype: 'textfield'
			}, {
				fieldLabel: lang['forma_vyipuska'],
				anchor: '80%',
				name: 'RlsClsdrugforms_Name',
				xtype: 'textfield'
			}]
		});

		//Вкладка "Классификация"
		this.FilterClassPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				xtype: 'swrlsclsatcremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: lang['ath'],
				hiddenName: 'CLSATC_ID'
			}, {
				xtype: 'swrlsstronggroupscombo',
				fieldLabel: lang['silnodeystvuyuschie'],
				hiddenName: 'STRONGGROUPS_ID',
				onLoadStore: function(store) {
					var record = new Ext.data.Record.create(store);
					var idx = (store.getCount() > 0 && store.getAt(0).get('RlsStronggroups_id') < 1) ? 1 : 0;

					store.insert(idx, new record({
						RlsStronggroups_id: -2,
						RlsStronggroups_Name: lang['net']
					}));
					store.insert(idx, new record({
						RlsStronggroups_id: -1,
						RlsStronggroups_Name: lang['da']
					}));
				}
			}, {
				xtype: 'swrlsnarcogroupscombo',
				fieldLabel: lang['narkoticheskie'],
				hiddenName: 'NARCOGROUPS_ID',
				onLoadStore: function(store) {
					var record = new Ext.data.Record.create(store);
					var idx = (store.getCount() > 0 && store.getAt(0).get('RlsNarcogroups_id') < 1) ? 1 : 0;

					store.insert(idx, new record({
						RlsNarcogroups_id: -2,
						RlsNarcogroups_Name: lang['net']
					}));
					store.insert(idx, new record({
						RlsNarcogroups_id: -1,
						RlsNarcogroups_Name: lang['da']
					}));
				}
			}]
		});

		this.FilterTabs = new Ext.TabPanel({
			autoScroll: true,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'north',
			enableTabScroll: true,
			height: 170,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[{
				title: lang['naimenovanie'],
				layout: 'fit',
				border:false,
				items: [this.FilterNamePanel]
			}, {
				title: lang['klassifikatsiya'],
				layout: 'fit',
				border:false,
				items: [this.FilterClassPanel]
			}]
		});

		//Кнопки
		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
							wnd.doSearch();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterTabs,
				this.FilterButtonsPanel
			]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					var wnds = this.ownerCt;
					if(wnd.armReadOnly){
						wnds.hide();
					}else{
						sw.swMsg.show({
							title: lang['podtverdite_deystvie'],
							msg: lang['vyi_uverenyi_chto_hotite_vyiyti_bez_sohraneniya_izmeneniy'],
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									wnds.hide();
								}
							}
						});
					}
				},
				
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				this.FilterPanel,
				this.DrugNormativeListSpecGrid
			]
		});
		sw.Promed.swDrugNormativeListSpecViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});