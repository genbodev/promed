/**
* swDrugListRequestViewWindow - окно редактирования списка медикаментов для заявки
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
sw.Promed.swDrugListRequestViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spisok_medikamentov_dlya_zayavki'],
	layout: 'border',
	id: 'DrugListRequestViewWindow',
	modal: true,
	shim: false,
	width: 400,
	typeWnd:"view",
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	loadGrid: function() {
		var wnd = this;
		var params = new Object();
		params.limit = 100;
		params.start =  0;
		params.DrugRequestProperty_id = wnd.DrugRequestProperty_id;

		wnd.DrugListRequestGrid.removeAll();
		wnd.DrugListRequestGrid.loadData({
			globalFilters: params
		});
	},
	doSearch: function() {
		var wnd = this;
		var form = this.FilterPanel.getForm();
		var params = form.getValues();

		var mnn_code = params.DrugComplexMnn_Code != '' ? params.DrugComplexMnn_Code : null;
		var mnn_name = params.DrugComplexMnnName_Name != '' ? params.DrugComplexMnnName_Name.toLowerCase() : null;
		var torg_name = params.RlsTorg_Name != '' ? params.RlsTorg_Name.toLowerCase() : null;
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

		this.DrugListRequestGrid.getGrid().getStore().filterBy(function(record){
			return (
				(mnn_code == null || record.get('DrugComplexMnn_Code') == mnn_code) &&
				(mnn_name == null || record.get('DrugComplexMnnName_Name').toLowerCase().indexOf(mnn_name) > -1) &&
				(torg_name == null || record.get('TRADENAMES_NAME_list').toLowerCase().indexOf(torg_name) > -1) &&
				(atx_name == null || record.get('ATX_CODE_list').indexOf(','+atx_name) > -1) &&
				(narco == null || (narco == -1 && record.get('NARCOGROUPID') > 0) || (narco == -2 && record.get('NARCOGROUPID') <= 0) || (narco == record.get('NARCOGROUPID'))) &&
				(strong == null || (strong == -1 && record.get('STRONGGROUPID') > 0) || (strong == -2 && record.get('STRONGGROUPID') <= 0) || (strong == record.get('STRONGGROUPID')))
			);
		});
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.DrugListRequestGrid.clearFilter();
	},
	doSave:  function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(wnd.getEl(), {msg:lang['zagruzka']});
		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert(lang['oshibka'], lang['sohranenie_ne_udalos']);
				loadMask.hide();				
			},
			params:{
				DrugRequestProperty_id: wnd.DrugRequestProperty_id,
				DrugListRequest_JsonData: wnd.DrugListRequestGrid.getJSONChangedData()
			},
			success: function (response) {
				//var result = Ext.util.JSON.decode(response.responseText);
				//if (!result[0]) { return false}
				loadMask.hide();
				wnd.hide();				
				if (wnd.owner && wnd.owner.DrugRequestPropertyGrid) {
					wnd.owner.DrugRequestPropertyGrid.refreshRecords(null,0);
				}
			},
			url:'/?c=DrugRequestProperty&m=saveDrugListRequestFromJSON'
		});
		return true;
	},
	doSaveContinue:  function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(wnd.getEl(), {msg:lang['zagruzka']});
		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert(lang['oshibka'], lang['sohranenie_ne_udalos']);
				loadMask.hide();				
			},
			params:{
				DrugRequestProperty_id: wnd.DrugRequestProperty_id,
				DrugListRequest_JsonData: wnd.DrugListRequestGrid.getJSONChangedData()
			},
			success: function (response) {
				//var result = Ext.util.JSON.decode(response.responseText);
				//if (!result[0]) { return false}
				loadMask.hide();
                
                
				/*
                wnd.hide();				
				if (wnd.owner && wnd.owner.DrugRequestPropertyGrid) {
					wnd.owner.DrugRequestPropertyGrid.refreshRecords(null,0);
				}
                */
			},
			url:'/?c=DrugRequestProperty&m=saveDrugListRequestFromJSON',
            callback : function(){           
                //Обнуление state строк грида
                //Временно - пока нет пагинации		
                 var store = wnd.DrugListRequestGrid.getGrid().getStore().data.items;
                
                 for(var k in store){
                     var rec = store[k];
                     if(typeof rec == 'object'){
                         if(rec.get('state') != 'delete'){
                            rec.set('state', '');
                            rec.commit();
                         }
                     }
                 }  
                 
                 var parentGrid = Ext.getCmp('drpvDrugRequestPropertyGrid');
                 parentGrid.getGrid().getStore().load({DrugRequestProperty_id: Ext.getCmp('numberfieldYear').getValue()});
            }
            
		});
		return true;
	},    
	armFilter:function(){
		var grid = this.DrugListRequestGrid;

		if((haveArmType('mekllo') || haveArmType('minzdravdlo') || haveArmType('zakup') || getGlobalOptions().superadmin == true) && this.typeWnd == "edit") {
			grid.readOnly = false;
			this.buttons[0].setDisabled(false);
			this.buttons[4].setText(BTN_FRMCANCEL);
			this.setTitle(this.title + ": редактирование");

			//настройка доступа к редактированию цен
			var price_edit_enabled = (haveArmType('zakup') || getGlobalOptions().superadmin == true || (haveArmType('minzdravdlo') && getGlobalOptions().llo_price_edit_enabled));
			this.setDisabledAction(grid, 'action_price_calculating', !price_edit_enabled);
			this.setDisabledAction(grid, 'action_price_list_calculating', !price_edit_enabled);
			this.setDisabledAction(grid, 'action_epmty_price_calculating', !price_edit_enabled);
			this.setDisabledAction(grid, 'action_price_checking', !price_edit_enabled);
		} else {
			grid.readOnly = true;
			this.buttons[0].setDisabled(true);
			this.buttons[4].setText(lang['zakryit']);
		}
		grid.ViewActions.action_dlrv_actions.setDisabled(grid.readOnly);
	},
	doCalculatePrice: function(object, mode){
		var wnd = this;
		var params = new Object();
		var viewframe = wnd.DrugListRequestGrid;
		var record = viewframe.getGrid().getSelectionModel().getSelected();
		var store = viewframe.getGrid().getStore();
		var mnn_list = new Array();

		if (object == 'row') {
			if (record) {
				mnn_list.push(record.get('DrugComplexMnn_id'));
			} else {
				return false;
			}
		} else {
			store.each(function(rec) {
				if (object != 'empty_list' || rec.get('DrugListRequest_Price') <= 0) {
					mnn_list.push(rec.get('DrugComplexMnn_id'));
				}
			})
		}

		if (object != 'list_checking') {
			wnd.resetCheckMaxPrice();
		}

		if (mnn_list.length > 0) {
			params.mode = mode;
			params.DrugRequestProperty_id = wnd.DrugRequestProperty_id;
			params.DrugComplexMnn_List = mnn_list.join(',');

			var loadMask = new Ext.LoadMask(wnd.getEl(), {msg:lang['poluchenie_dannyih']});
			loadMask.show();

			Ext.Ajax.request({
				params: params,
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['poluchenie_dannyih_ne_udalos']);
					loadMask.hide();
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					var price_arr = new Array();

					for(var i = 0; i < result.length; i++) {
						price_arr[result[i]['DrugComplexMnn_id']] = result[i]['Price'];
					}

					if (object == 'row' || object == 'list' || object == 'empty_list') {
						store.each(function(rec) {
							if (object == 'list' || price_arr[rec.get('DrugComplexMnn_id')]) {
								rec.set('DrugListRequest_Price', price_arr[rec.get('DrugComplexMnn_id')]);
								if (rec.set('state') != 'add' && rec.set('state') != 'delete') {
									rec.set('state', 'edit');
								}
								rec.commit();
							}
						});
					}

					if (object == 'list_checking') {
						var is_valid = true;
						store.each(function(rec) {
							var price = price_arr[rec.get('DrugComplexMnn_id')];
							if (is_valid && price > 0 && rec.get('DrugListRequest_Price') > price) {
								is_valid = false;
							}
							rec.set('Max_Price', price);
							rec.commit();
						});
						if (is_valid) {
							sw.swMsg.alert(lang['proverka_okonchena'], lang['prevyisheniya_tsen_na_jnvlp_net']);
						}
					}

					loadMask.hide();
				},
				url:'/?c=DrugRequestProperty&m=getPriceList'
			});
		}
	},
	doCheckMaxPrice: function(mode) {
		this.doCalculatePrice('list_checking', mode);
	},
	resetCheckMaxPrice: function() {
		this.DrugListRequestGrid.getGrid().getStore().each(function(record) {
			record.set('Max_Price', null);
			record.commit();
		});
	},
	setDisabledAction: function(grid, action, disabled) {
		var actions = grid.getAction('action_dlrv_actions').items[0].menu.items,
			idx = actions.findIndexBy(function(a) { return a.name == action; });
		if( idx == -1 ) {
			return;
		}
		actions.items[idx].setDisabled(disabled);
		grid.getAction('action_dlrv_actions').items[1].menu.items.items[idx].setDisabled(disabled);
	},
	show: function() {
        var wnd = this;

		sw.Promed.swDrugListRequestViewWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequestProperty_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if(arguments[0].typeWnd)this.typeWnd=arguments[0].typeWnd;
		this.action = (arguments[0].action) ? arguments[0].action : 'add';
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequestProperty_id ) {
			this.DrugRequestProperty_id = arguments[0].DrugRequestProperty_id;
		}
		if ( arguments[0].DrugRequestProperty_Name ) {
			this.setTitle(arguments[0].DrugRequestProperty_Name);
		} else {
			this.setTitle(lang['spisok_medikamentov_dlya_zayavki']);
		}
		this.DrugListRequestGrid.addActions({
			name:'action_dlrv_actions',
			text:lang['deystviya'],
			menu: [{
				name:'action_synchronize',
                hidden: true,
				text: lang['sinhronizirovat_s_normativnyim_perechnem'],
				tooltip: lang['sinhronizirovat_s_normativnyim_perechnem'],
				handler: function() {					
					if (wnd.DrugRequestProperty_id > 0) {
						var mnn_list = new Array();
						wnd.DrugListRequestGrid.getGrid().getStore().each(function(record) {
							if (record.get('DrugComplexMnn_id') > 0 && record.get('state') != 'delete') {
								mnn_list.push(record.get('DrugComplexMnn_id'));
							}
						});
						getWnd('swDrugRequestSynchronizeWindow').show({
							DrugRequestProperty_id: wnd.DrugRequestProperty_id,
							DrugComplexMnn_id_list: mnn_list.join(','),
							onSave: function(data) {
								var view_frame = wnd.DrugListRequestGrid;
								var store = view_frame.getGrid().getStore();

								view_frame.clearFilter();
								var record_count = store.getCount();
								if ( record_count == 1 && !store.getAt(0).get('DrugListRequest_id') ) {
									 view_frame.removeAll({ addEmptyRecord: false });
									 record_count = 0;
								}
								var original_size = record_count;
								var rc_arr = new Array();
								var sz = 0;

								for(var i = 0; i < data.length; i++) {
									if (data[i].state == 'add') {
										var added = false;

										sz = 0;
										store.each(function(record) { //ищем среди удаленных
											if (record.get('DrugComplexMnn_id') == data[i]['DrugComplexMnn_id'] && record.get('state') == 'delete') {
												record.set('state', 'edit');
												record.commit();
												added = true;
											}
											if (++sz >= original_size)
												return false;
										});
										
										if (!added) {
											var record = new Ext.data.Record.create(view_frame.jsonData['store']);
											data[i].DrugListRequest_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
											data[i].DrugListRequest_IsProblem = null;
											data[i].DrugTorgUse_id = 1; //1 - не иcпользуется;
 											rc_arr.push(new record(data[i]));
										}
									}
									if (data[i].state == 'delete') {
										sz = 0;
										store.each(function(record) {
											if (record.get('DrugComplexMnn_id') == data[i]['DrugComplexMnn_id']) {
												if (record.get('state') == 'add') {
													view_frame.getGrid().getStore().remove(record);
												} else {								
													record.set('state', 'delete');
													record.commit();													
												}
											}
											if (++sz >= original_size)
												return false;
										});
									}
								};
								if (rc_arr.length > 0) {
									store.insert(store.getCount(), rc_arr);
								}
								view_frame.setFilter();
							}
						});
					}
				},
				iconCls: 'documents16'
			}, 
            /**
             * START https://redmine.swan.perm.ru/issues/73492
             */
            {
				name:'action_potochnyiy_vvod',
				text: lang['potochnyiy_vvod'],
				tooltip: lang['potochnyiy_vvod'],
				handler: function() {					
					if (wnd.DrugRequestProperty_id > 0) {
						var mnn_list = new Array();
						wnd.DrugListRequestGrid.getGrid().getStore().each(function(record) {
							if (record.get('DrugComplexMnn_id') > 0 && record.get('state') != 'delete') {
								mnn_list.push(record.get('DrugComplexMnn_id'));
							}
						});
						getWnd('swDrugRequestSynchronizeWindowFilter').show({
							DrugRequestProperty_id: wnd.DrugRequestProperty_id,
							DrugComplexMnn_id_list: mnn_list.join(','),
							onSave: function(data) {
								var view_frame = wnd.DrugListRequestGrid;
								var store = view_frame.getGrid().getStore();

								view_frame.clearFilter();
								var record_count = store.getCount();
								if ( record_count == 1 && !store.getAt(0).get('DrugListRequest_id') ) {
									 view_frame.removeAll({ addEmptyRecord: false });
									 record_count = 0;
								}
								var original_size = record_count;
								var rc_arr = new Array();
								var sz = 0;

								for(var i = 0; i < data.length; i++) {
									if (data[i].state == 'add') {
										var added = false;

										sz = 0;
										store.each(function(record) { //ищем среди удаленных
											if (record.get('DrugComplexMnn_id') == data[i]['DrugComplexMnn_id'] && record.get('state') == 'delete') {
												record.set('state', 'edit');
												record.commit();
												added = true;
											}
											if (++sz >= original_size)
												return false;
										});
										
										if (!added) {
											var record = new Ext.data.Record.create(view_frame.jsonData['store']);
											data[i].DrugListRequest_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
											data[i].DrugListRequest_IsProblem = null;
											data[i].DrugTorgUse_id = 1; //1 - не иcпользуется;
                                            
 											rc_arr.push(new record(data[i]));
										}
									}
                                     /* 
									if (data[i].state == 'delete') {
										sz = 0;
										store.each(function(record) {
											if (record.get('DrugComplexMnn_id') == data[i]['DrugComplexMnn_id']) {
												if (record.get('state') == 'add') {
													view_frame.getGrid().getStore().remove(record);
												} else {								
													record.set('state', 'delete');
													record.commit();													
												}
											}
											if (++sz >= original_size)
												return false;
										});
									}
                                    */
								};

								if (rc_arr.length > 0) {
									store.insert(store.getCount(), rc_arr);
								}
								view_frame.setFilter();
							}
						});
					}
				},
				iconCls: 'documents16'
			},   
            /**
             * END https://redmine.swan.perm.ru/issues/73492
             */          
            {
				name:'action_price_calculating',
				text: lang['rasschitat_tsenu_lp'],
				tooltip: lang['rasschitat_tsenu_lp'],
				menu: [{
					text: lang['na_jnvlp_po_maksimalnoy_optovoy_s_nds'],
					tooltip: lang['na_jnvlp_po_maksimalnoy_optovoy_s_nds'],
					handler: function() {
						wnd.doCalculatePrice('row', 'wholesale_price');
					},
					iconCls: 'actions16'
				}, {
					text: lang['na_jnvlp_po_maksimalnoy_roznichnoy_s_nds'],
					tooltip: lang['na_jnvlp_po_maksimalnoy_roznichnoy_s_nds'],
					handler: function() {
						wnd.doCalculatePrice('row', 'retail_price');
					},
					iconCls: 'actions16'
				}, {
					text: lang['po_predyiduschey_zayavke'],
					tooltip: lang['po_predyiduschey_zayavke'],
					handler: function() {
						wnd.doCalculatePrice('row', 'drug_request');
					},
					iconCls: 'actions16'
				}, {
					text: lang['kak_srednee_ot_tsen_dvuh_predyiduschih_gk_i_tsenyi_predyiduschey_zayavki'],
					tooltip: lang['kak_srednee_ot_tsen_dvuh_predyiduschih_gk_i_tsenyi_predyiduschey_zayavki'],
					handler: function() {
						wnd.doCalculatePrice('row', 'average_value');
					},
					iconCls: 'actions16'
				}],
				iconCls: 'document16'
			}, {
				name:'action_price_list_calculating',
				text: lang['rasschitat_tsenyi_vsego_spiska'],
				tooltip: lang['rasschitat_tsenyi_vsego_spiska'],
				menu: [{
					text: lang['na_jnvlp_po_maksimalnoy_optovoy_s_nds'],
					tooltip: lang['na_jnvlp_po_maksimalnoy_optovoy_s_nds'],
					handler: function() {
						wnd.doCalculatePrice('list', 'wholesale_price');
					},
					iconCls: 'actions16'
				}, {
					text: lang['na_jnvlp_po_maksimalnoy_roznichnoy_s_nds'],
					tooltip: lang['na_jnvlp_po_maksimalnoy_roznichnoy_s_nds'],
					handler: function() {
						wnd.doCalculatePrice('list', 'retail_price');
					},
					iconCls: 'actions16'
				}, {
					text: lang['po_predyiduschey_zayavke'],
					tooltip: lang['po_predyiduschey_zayavke'],
					handler: function() {
						wnd.doCalculatePrice('list', 'drug_request');
					},
					iconCls: 'actions16'
				}, {
					text: lang['kak_srednee_ot_tsen_dvuh_predyiduschih_gk_i_tsenyi_predyiduschey_zayavki'],
					tooltip: lang['kak_srednee_ot_tsen_dvuh_predyiduschih_gk_i_tsenyi_predyiduschey_zayavki'],
					handler: function() {
						wnd.doCalculatePrice('list', 'average_value');
					},
					iconCls: 'actions16'
				}],
				iconCls: 'documents16'
			}, {
				name:'action_epmty_price_calculating',
				text: lang['rasschitat_tsenyi_dlya_pozitsiy_bez_tsenyi'],
				tooltip: lang['rasschitat_tsenyi_dlya_pozitsiy_bez_tsenyi'],
				menu: [{
					text: lang['na_jnvlp_po_maksimalnoy_optovoy_s_nds'],
					tooltip: lang['na_jnvlp_po_maksimalnoy_optovoy_s_nds'],
					handler: function() {
						wnd.doCalculatePrice('empty_list', 'wholesale_price');
					},
					iconCls: 'actions16'
				}, {
					text: lang['na_jnvlp_po_maksimalnoy_roznichnoy_s_nds'],
					tooltip: lang['na_jnvlp_po_maksimalnoy_roznichnoy_s_nds'],
					handler: function() {
						wnd.doCalculatePrice('empty_list', 'retail_price');
					},
					iconCls: 'actions16'
				}, {
					text: lang['po_predyiduschey_zayavke'],
					tooltip: lang['po_predyiduschey_zayavke'],
					handler: function() {
						wnd.doCalculatePrice('empty_list', 'drug_request');
					},
					iconCls: 'actions16'
				}, {
					text: lang['kak_srednee_ot_tsen_dvuh_predyiduschih_gk_i_tsenyi_predyiduschey_zayavki'],
					tooltip: lang['kak_srednee_ot_tsen_dvuh_predyiduschih_gk_i_tsenyi_predyiduschey_zayavki'],
					handler: function() {
						wnd.doCalculatePrice('empty_list', 'average_value');
					},
					iconCls: 'actions16'
				}],
				iconCls: 'documents16'
			}, {
				name:'action_price_checking',
				text: lang['vyipolnit_proverku_tsen'],
				tooltip: lang['vyipolnit_proverku_tsen'],
				menu: [{
					text: lang['po_predelnyim_optovyim_tsenam_na_jnvlp_s_nds'],
					tooltip: lang['po_predelnyim_optovyim_tsenam_na_jnvlp_s_nds'],
					handler: function() {
						wnd.doCheckMaxPrice('wholesale_price');
					},
					iconCls: 'actions16'
				}, {
					text: lang['po_predelnyim_roznichnyim_tsenam_na_jnvlp_s_nds'],
					tooltip: lang['po_predelnyim_roznichnyim_tsenam_na_jnvlp_s_nds'],
					handler: function() {
						wnd.doCheckMaxPrice('retail_price');
					},
					iconCls: 'actions16'
				}],
				iconCls: 'doc-uch16'
			}],
			iconCls: 'actions16'
		});

		this.FilterPanel.getForm().findField('CLSATC_ID').getStore().load({params: {maxCodeLength: 5}});
		this.FilterPanel.getForm().reset();
		wnd.loadGrid();
		wnd.armFilter();
	},
	initComponent: function() {
		var wnd = this;		
		
		this.DrugListRequestGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.DrugListRequestGrid.editRecord('add'); }},
				{name: 'action_edit', handler: function() { wnd.DrugListRequestGrid.editRecord('edit'); }},
				{name: 'action_view', handler: function() { wnd.DrugListRequestGrid.editRecord('view'); }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_delete', handler: function() { wnd.DrugListRequestGrid.deleteRecord(); }},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
            focusOnFirstLoad:false,
            noFocusOnLoad : false,
			border: true,
			dataUrl: '/?c=DrugRequestProperty&m=loadDrugListRequestList',
			height: 280,
			region: 'center',
			object: 'DrugListRequest',
			editformclassname: 'swDrugListRequestViewWindow',
			id: 'dlrvDrugListRequestGrid',
			//paging: true,
            //root : '0',
            //totalProperty : '1',            
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugListRequest_id', type: 'int', header: 'ID', hidden: true, key: true},
				{name: 'state', type: 'string', header: 'state', hidden: true},
				{name: 'DrugComplexMnn_id', type: 'string', header: lang['id_mnn'], hidden: true},
				{name: 'DrugComplexMnn_Code', type: 'string', header: lang['kod'], width: 80},
				{name: 'ATX_Code', header: lang['ath'], width: 160, renderer: function(v, p, r){
					var str = r.get('ATX_CODE_list');
					return str && str.length > 0 && str[0] == ',' ? str.substring(1, str.length).replace(' ,', ', ')  : str;
				}},
				{name: 'DrugComplexMnnName_Name', type: 'string', header: lang['mnn'], id: 'autoexpand', width: 160},
				{name: 'DrugComplexMnnName_RusName', type: 'string', hidden: true},
				{name: 'ClsDrugForms_Name', type: 'string', header: lang['lekarstvennaya_forma'], width: 160},
				{name: 'DrugComplexMnnDose_Name', type: 'string', header: lang['dozirovka'], width: 100},
				{name: 'DrugComplexMnnFas_Name', type: 'string', header: lang['fasovka'], width: 100},
				{name: 'NTFR_Name', type: 'string', header: lang['klass_ntfr'], width: 100},
				{name: 'ATX_CODE_list', type: 'string', hidden: true},
				{name: 'STRONGGROUPID', type: 'string', hidden: true},
				{name: 'NARCOGROUPID', type: 'string', hidden: true},
				{name: 'TRADENAMES_ID_list', type: 'string', header: lang['torg'], width: 160, hidden: true},
				{name: 'TRADENAMES_NAME_list', type: 'string', header: lang['torg_naim'], width: 160},
				{name: 'DrugTorgUse_id', type: 'string', header: lang['id_isp_torg_nm'], hidden: true},
				{name: 'DrugTorgUse_Name', type: 'string', header: lang['ispolzovanie_torgovogo_naim'], width: 250, hidden: true},
				{name: 'DrugListRequest_Price', header: lang['tsena_rub'], width: 100, renderer: function(v, p, r){
					if (v != null) {
						v = (v*1).toFixed(2);
					}
					if (r.get('Max_Price') > 0 && v > r.get('Max_Price')) {
						return '<span style="color: red">' + v + '</span>';
					}
					return v;
				}},
				{name: 'Max_Price', type: 'float', hidden: true},
                {name: 'DrugListRequest_Number', type: 'string', header: lang['p_p'], width: 100, editor: new Ext.form.TextField()},
				{name: 'DrugListRequest_IsProblem', header: lang['problema_s_zakupom'], width: 150, renderer: function(v, p, r){
					p.css += ' x-grid3-check-col-td';
					var style = 'x-grid3-check-col-non-border'+((String(v)=='1')?'-on':'');
					return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
				}},
                {name: 'DrugListRequest_Comment', type: 'string', header: lang['primechanie'], width: 140},
				{name: 'DrugListRequestTorg_JsonData', type: 'string', header: 'json', hidden: true},
				{name: 'DrugListRequestTorg_GridData', type: 'string', header: 'grid', hidden: true}
			],
			title: lang['medikamentyi'],
			toolbar: true,
            onAfterEditSelf: function(o) {
                if (o.record.get('state') != 'add') {
                    o.record.set('state', 'edit');
                    o.record.commit();
                }
            },
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugListRequest_id') > 0 || record.get('id_array') != ''){
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
				params.DrugRequestProperty_id = wnd.DrugRequestProperty_id;
				if (record)
					params = Ext.apply(params, record.data);
				if (action == 'add') {
					var record_count = store.getCount();
					if ( record_count == 1 && !store.getAt(0).get('DrugListRequest_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}
					
					params.onSave = function(data) {
						if ( record_count == 1 && !store.getAt(0).get('DrugListRequest_id') ) {
							view_frame.removeAll({ addEmptyRecord: false });
						}										
						var record = new Ext.data.Record.create(view_frame.jsonData['store']);

						wnd.resetCheckMaxPrice();

						view_frame.clearFilter();
						data.DrugListRequest_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
						data.state = 'add';
						store.insert(record_count, new record(data));
						view_frame.setFilter();

						this.hide();
					}
				}
				if (action == 'edit') {
					params.onSave = function(data) {
						var record = view_frame.getGrid().getSelectionModel().getSelected();

						wnd.resetCheckMaxPrice();

						record.set('DrugComplexMnn_id', data['DrugComplexMnn_id']);
						record.set('DrugTorgUse_id', data['DrugTorgUse_id']);
						record.set('DrugListRequest_Price', data['DrugListRequest_Price']);
						record.set('DrugListRequest_id', data['DrugListRequest_id']);
						record.set('DrugComplexMnnName_Name', data['DrugComplexMnnName_Name']);
						record.set('ClsDrugForms_Name', data['ClsDrugForms_Name']);
						record.set('DrugComplexMnnDose_Name', data['DrugComplexMnnDose_Name']);
						record.set('DrugComplexMnnFas_Name', data['DrugComplexMnnFas_Name']);
						record.set('NTFR_Name', data['NTFR_Name']);
						record.set('DrugTorgUse_Name', data['DrugTorgUse_Name']);
						record.set('DrugListRequestTorg_JsonData', data['DrugListRequestTorg_JsonData']);
						record.set('DrugListRequestTorg_GridData', data['DrugListRequestTorg_GridData']);
						record.set('TRADENAMES_ID_list', data['TRADENAMES_ID_list']);
						record.set('TRADENAMES_NAME_list', data['TRADENAMES_NAME_list']);
						record.set('DrugListRequest_IsProblem', data['DrugListRequest_IsProblem']);
						record.set('DrugListRequest_Comment', data['DrugListRequest_Comment']);
						if (record.get('state') != 'add') {
							record.set('state', 'edit');
						}
						record.commit();
						
						this.hide();
					}
				}
				
				getWnd('swDrugListRequestEditWindow').show(params);
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
				if (selected_record.get('state') == 'add') {
					view_frame.getGrid().getStore().remove(selected_record);
				} else {								
					selected_record.set('state', 'delete');
					selected_record.commit();
					view_frame.setFilter();
				}
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
						record.set('DrugListRequestTorg_GridData', null);
						data.push(record.data);
					}
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

		wnd.mnn_combo = new sw.Promed.SwDrugComplexMnnCombo({
			fieldLabel: lang['mnn'],
			width: 300,
			anchor: ''
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
				fieldLabel: lang['kod'],
				name: 'DrugComplexMnn_Code',
				width: 120
			}, {
				fieldLabel: lang['mnn'],
				anchor: '80%',
				name: 'DrugComplexMnnName_Name',
				xtype: 'textfield'
			}, {
				fieldLabel: lang['torg_naimenovanie'],
				anchor: '80%',
				name: 'RlsTorg_Name',
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
					var idx = 0;

					if (store.getCount() > 0 && store.getAt(0).get('RlsStronggroups_id') < 1) {
						idx = 1;
					}

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
					var idx = 0;

					if (store.getCount() > 0 && store.getAt(0).get('RlsNarcogroups_id') < 1) {
						idx = 1;
					}

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
				handler: function() 
				{
					this.ownerCt.doSaveContinue();
				},
				iconCls: 'save16',
				text: lang['sohranit_i_prodoljit']
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
			items:[
				this.FilterPanel,
				this.DrugListRequestGrid
			]
		});
		sw.Promed.swDrugListRequestViewWindow.superclass.initComponent.apply(this, arguments);
	}
});