/**
* swDrugListRequestEditWindow - окно редактирования медикамента по заявке
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
sw.Promed.swDrugListRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['medikament_dlya_zayavki_redaktirovanie'],
	layout: 'border',
	id: 'DrugListRequestEditWindow',
	modal: true,
	shim: false,
	width: 820,
	height: 580,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	getData: function() {
		var data = new Object();
		var wnd = this;
		data = wnd.form.getValues();
		data.DrugListRequest_id = this.DrugListRequest_id;
		data.DrugComplexMnn_RusName = '';
		data.DrugListRequestTorg_JsonData = wnd.TorgGrid.getJSONChangedData();
		data.DrugListRequest_Comment = this.form.findField('DrugListRequest_Comment').getValue();
		data.DrugListRequest_Price = this.form.findField('DrugListRequest_Price').getValue();

		if (data.DrugComplexMnn_id > 0) {
			var combo = this.form.findField('DrugComplexMnn_id');
			var num = combo.getStore().findBy(function(rec) { return rec.get('DrugComplexMnn_id') == data.DrugComplexMnn_id; });
			if (num >= 0) {
				data.DrugComplexMnn_RusName = combo.getStore().getAt(num).get('DrugComplexMnn_Name');
			} else if (combo.getRawValue() != '') {
				data.DrugComplexMnn_RusName = combo.getRawValue();
			}
		}

		var grid_data = new Array();
		var torg_id_array = new Array();
		var torg_name_array = new Array();
		wnd.TorgGrid.getGrid().getStore().each(function(record){
			var obj = new Object();
			Ext.apply(obj, record.data);
			grid_data.push(obj);
			if (record.get('state') != 'delete' && record.get('TRADENAMES_id') > 0) {
				torg_id_array.push(record.get('TRADENAMES_id'));
				torg_name_array.push(record.get('TRADENAMES_Name'));
			}
		});
		data.DrugListRequestTorg_GridData = grid_data.length > 0 ? Ext.util.JSON.encode(grid_data) : "";
		data.TRADENAMES_ID_list = torg_id_array.join(',');
		data.TRADENAMES_NAME_list = torg_name_array.join(',');
		data.DrugListRequest_IsProblem = this.form.findField('DrugListRequest_IsProblem').checked ? 1 : 0;
		data.DrugComplexMnnName_Name = this.form.findField('DrugComplexMnnName_Name').getValue();
		data.ClsDrugForms_Name = this.form.findField('ClsDrugForms_Name').getValue();
		data.DrugComplexMnnDose_Name = this.form.findField('DrugComplexMnnDose_Name').getValue();
		data.DrugComplexMnnFas_Name = this.form.findField('DrugComplexMnnFas_Name').getValue();
		data.NTFR_Name = this.form.findField('NTFR_Name').getValue();

		return data;
	},
	setData: function(data) {
		this.form.setValues(data);
		this.setDrugComplexMnnValue();
		this.setGridData(data);
	},
	setGridData: function(data) {
		var wnd = this;
		if (data.DrugListRequestTorg_GridData && data.DrugListRequestTorg_GridData != '') {
			//загружаем данные из родительского грида
			var arr = Ext.util.JSON.decode(data.DrugListRequestTorg_GridData);
			wnd.TorgGrid.getGrid().getStore().loadData(arr);
		} else if (wnd.action != 'add' && data.DrugListRequest_id && data.DrugListRequest_id > 0) {
			//загружаем данные из бд
			var params = new Object();
			params.limit = 100;
			params.start =  0;
			params.DrugListRequest_id = data.DrugListRequest_id;
			
			wnd.TorgGrid.removeAll();
			wnd.TorgGrid.loadData({
				globalFilters: params
			});
		}
	},
	setDrugComplexMnnValue: function() {
		var combo = this.form.findField('DrugComplexMnn_id');
		var mnn_id = combo.getValue();
		if (mnn_id > 0) {
			combo.lastQuery = '';
			combo.getStore().baseParams.query = null;
			combo.getStore().load({
				params: {DrugComplexMnn_id: mnn_id},
				callback: function() {
					combo.setValue(mnn_id);
					var idx = combo.getStore().indexOfId(mnn_id);
					if (idx > -1) {
						combo.setLinkedFields(combo.getStore().getAt(idx));
					}
				}
			});
		}
	},
	setJnvlpPrices: function() {
		var wnd = this;

		wnd.form.findField('Jnvlp_Price').setValue(null);
		wnd.form.findField('Jnvlp_WholesalePrice').setValue(null);

		var mnn_id = this.mnn_combo.getValue();
		var torg_list = "";

		wnd.TorgGrid.getGrid().getStore().each(function(record) {
			if (record.get('TRADENAMES_id') > 0) {
				torg_list += (!Ext.isEmpty(torg_list) ? "," : "") + record.get('TRADENAMES_id');
			}
		});

		if (mnn_id > 0) {
			if (wnd.JnvlpPricesData && !Ext.isEmpty(wnd.JnvlpPricesData[mnn_id]) && Ext.isEmpty(torg_list)) {
				wnd.setJnvlpPricesFields(wnd.JnvlpPricesData[mnn_id]);
			} else {
				Ext.Ajax.request({
					url: '/?c=DrugRequestProperty&m=getJnvlpPrices',
					params: {
						DrugComplexMnn_id: mnn_id,
						TRADENAMES_ID_List: !Ext.isEmpty(torg_list) ? torg_list : null
					},
					success: function(response){
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0] && result[0].MinPrice && result[0].MinPrice > 0) {
							if (!Ext.isEmpty(torg_list)) {
								wnd.setJnvlpPricesFields(result[0]);
							} else {
								if (!wnd.JnvlpPricesData) {
									wnd.JnvlpPricesData = new Object();
								}
								wnd.JnvlpPricesData[mnn_id] = {
									MinPrice: result[0].MinPrice*1,
									MaxPrice: result[0].MaxPrice*1,
									Wholesale_MinPrice: result[0].Wholesale_MinPrice*1,
									Wholesale_MaxPrice: result[0].Wholesale_MaxPrice*1
								};
								wnd.setJnvlpPricesFields(wnd.JnvlpPricesData[mnn_id]);
							}
						}
					}
				});
			}
		}
	},
	setJnvlpPricesFields: function(data) {
		var price_str = data.MinPrice;
		var wprice_str = data.Wholesale_MinPrice;

		if (data.MaxPrice > 0 && data.MaxPrice > data.MinPrice) {
			price_str += " - " + data.MaxPrice;
		}
		if (data.Wholesale_MaxPrice > 0 && data.Wholesale_MaxPrice > data.Wholesale_MinPrice) {
			wprice_str += " - " + data.Wholesale_MaxPrice;
		}

		this.form.findField('Jnvlp_Price').setValue(price_str);
		this.form.findField('Jnvlp_WholesalePrice').setValue(wprice_str);
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.form;
		var price_edit_enabled = (haveArmType('zakup') || getGlobalOptions().superadmin == true || (haveArmType('minzdravdlo') && getGlobalOptions().llo_price_edit_enabled));
		
		if (disable) {			
			form.findField('DrugComplexMnn_id').disable();
			form.findField('DrugListRequest_Comment').disable();
			form.findField('DrugListRequest_Price').disable();
			form.findField('DrugTorgUse_id').disable();
			form.findField('DrugListRequest_IsProblem').disable();
			wnd.findById('dlre_PriceButton').disable();
			wnd.buttons[0].disable();
		} else {			
			form.findField('DrugComplexMnn_id').enable();
			if (price_edit_enabled) {
				form.findField('DrugListRequest_Price').enable();
				wnd.findById('dlre_PriceButton').enable();
			} else {
				form.findField('DrugListRequest_Price').disable();
				wnd.findById('dlre_PriceButton').disable();
			}
			form.findField('DrugTorgUse_id').enable();
			form.findField('DrugListRequest_IsProblem').enable();
			wnd.buttons[0].enable();
		}
		wnd.TorgGrid.setReadOnly(disable);
	},
	doSave:  function() {
		var wnd = this;
		if (!this.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('dlreDrugListRequestEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var data = wnd.getData();

		Ext.Ajax.request({ //дописываем недостающие данные
			params:{
				DrugComplexMnn_id: data.DrugComplexMnn_id
			},
			failure:function () {
				wnd.onSave(data);
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result[0]) {
					Ext.apply(data, result[0]);
				}
				wnd.onSave(data);
			},
			url:'/?c=DrugRequestProperty&m=getDrugListRequestContext'
		});
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swDrugListRequestEditWindow.superclass.show.apply(this, arguments);		
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.DrugRequestProperty_id = null;
		this.DrugListRequest_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequestProperty_id ) {
			this.DrugRequestProperty_id = arguments[0].DrugRequestProperty_id;
		}
		if ( arguments[0].DrugListRequest_id ) {
			this.DrugListRequest_id = arguments[0].DrugListRequest_id;
		}
		
		this.form.reset();
		this.setTitle(lang['medikament_dlya_zayavki']);
		this.TorgGrid.removeAll();
		this.mnn_combo.getStore().baseParams.DrugRequestProperty_id = this.DrugRequestProperty_id;
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();

		switch (this.action) {
			case 'add':
				this.setTitle(this.title + lang['_dobavlenie']);
                var tu_combo = this.form.findField('DrugTorgUse_id');
                if (tu_combo.getStore().getCount() > 0) {
                    tu_combo.setValue(tu_combo.getStore().getAt(0).get('DrugTorgUse_id'));
                }
				loadMask.hide();
			break;
			case 'view':
			case 'edit':
				this.setTitle(this.title + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				wnd.setData(arguments[0]);
				loadMask.hide();				
			break;	
		}
		wnd.setDisabled(wnd.action == 'view');
	},
	initComponent: function() {
		var wnd = this;
		
		wnd.TorgGrid = new sw.Promed.ViewFrame({
			region: 'center',
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view', hidden: true},
				{name: 'action_delete'},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugRequestProperty&m=loadDrugListRequestTorgList',
			height: 180,
			object: 'DrugListRequestTorg',
			editformclassname: '',
			id: 'dlreDrugListRequestTorgGrid',
			paging: false,
			hidden: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugListRequestTorg_id', type: 'int', header: 'ID', hidden: true, key: true},
				{name: 'state', type: 'string', header: 'state', width: 120, hidden: true},
				{name: 'TRADENAMES_id', header: lang['torg_naim'], width: 120, hidden: true/*, editor: new sw.Promed.SwTradenamesCombo({}), renderer: function(v, p, r){ return r.get('TRADENAMES_Name') }*/},
				{name: 'TRADENAMES_Name', type: 'string', header: lang['torg_naim'], width: 120, id: 'autoexpand'},
				{name: 'OrgFarmacyPrice_Min', type: 'money', header: lang['apt_min'], width: 120, hidden: true/*, editor: new Ext.form.TextField({})*/},
				{name: 'OrgFarmacyPrice_Max', type: 'money', header: lang['apt_maks'], width: 120, hidden: true/*, editor: new Ext.form.TextField({})*/},
				{name: 'DrugRequestPrice_Min', type: 'money', header: lang['kontrakt_min'], width: 120/*, editor: new Ext.form.TextField({})*/},
				{name: 'DrugRequestPrice_Max', type: 'money', header: lang['kontrakt_maks'], width: 120/*, editor: new Ext.form.TextField({})*/},
				{name: 'DrugRequest_Price', type: 'money', header: lang['tsena_v_zayav'], width: 120/*, editor: new Ext.form.TextField({})*/},
				{name: 'DrugListRequestTorg_IsProblem', header: lang['problema_s_zakupom'], width: 150, renderer: function(v, p, r){
					p.css += ' x-grid3-check-col-td';
					var style = 'x-grid3-check-col-non-border'+((String(v)=='1')?'-on':'');
					return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
				}}
			],
			title: lang['torgovyie_naimenovaniya'],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugListRequestTorg_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			onDblClick: function() {
				if (!this.ViewActions.action_edit.isDisabled()) {
					this.ViewActions.action_edit.execute();
				}
			},
			getSumm: function() {
				var grid = this.getGrid();
				var summ = 0;
				grid.getStore().each(function(item) {
					if (item.get('DrugRequest_Price') > 0)
						summ += item.get('DrugRequest_Price')*1;
				});
				return summ;
			},
			editRecord: function (action) {
				var view_frame = this;
				var record = view_frame.getGrid().getSelectionModel().getSelected();
				var store = view_frame.getGrid().getStore();
				
				if (!record && action != 'add')
					return false;		
					
				var params = new Object();
				params.action = action;
				params.DrugComplexMnn_id = wnd.form.findField('DrugComplexMnn_id').getValue() > 0 ? wnd.form.findField('DrugComplexMnn_id').getValue() : null;
				params.DrugRequestProperty_id = wnd.DrugRequestProperty_id;
				if (record && action == 'edit')
					params = Ext.apply(params, record.data);
				if (action == 'add') {
					params.onSave = function(data) {
						store.clearFilter();
						var record_count = store.getCount();
						if ( record_count == 1 && !store.getAt(0).get('DrugListRequestTorg_id') ) {
							view_frame.removeAll({ addEmptyRecord: false });
							record_count = 0;
						}

						var record = new Ext.data.Record.create(view_frame.jsonData['store']);
						data.DrugListRequestTorg_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
						data.state = 'add';
						/*var data = new Object();						
						data.state = 'add';
						data.TRADENAMES_id = '';
						data.OrgFarmacyPrice_Min = '';
						data.OrgFarmacyPrice_Max = '';
						data.DrugRequestPrice_Min = '';
						data.DrugRequestPrice_Max = '';
						data.DrugRequest_Price = '';*/
						store.insert(record_count, new record(data));
						
						view_frame.hideDeleted();
						view_frame.rowCount = store.getCount();

						wnd.setJnvlpPrices();
						
						this.hide();
					}
				}
				if (action == 'edit') {
					params.onSave = function(data) {
						var record = view_frame.getGrid().getSelectionModel().getSelected();

						record.set('TRADENAMES_id', data['TRADENAMES_id']);
						record.set('TRADENAMES_Name', data['TRADENAMES_Name']);
						record.set('OrgFarmacyPrice_Min', data['OrgFarmacyPrice_Min']);
						record.set('OrgFarmacyPrice_Max', data['OrgFarmacyPrice_Max']);
						record.set('DrugRequestPrice_Min', data['DrugRequestPrice_Min']);
						record.set('DrugRequestPrice_Max', data['DrugRequestPrice_Max']);
						record.set('DrugRequest_Price', data['DrugRequest_Price']);
						record.set('DrugListRequestTorg_IsProblem', data['DrugListRequestTorg_IsProblem']);

						if (record.get('state') != 'add') {
							record.set('state', 'edit');
						}
						record.commit();

						wnd.setJnvlpPrices();

						this.hide();
					}
				}
				
				getWnd('swDrugListRequestTorgEditWindow').show(params);
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
				if (selected_record.get('state') == 'add') {
					view_frame.getGrid().getStore().remove(selected_record);
				} else {								
					selected_record.set('state', 'delete');
					selected_record.commit();
					view_frame.hideDeleted();
				}
				wnd.setJnvlpPrices();
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();

				this.getGrid().getStore().clearFilter(true);				
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
						var obj = new Object();
						Ext.apply(obj, record.data)
						data.push(obj);
					}
				});
				this.hideDeleted();
				return data;
			},						
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			hideDeleted: function() {
				var view_frame = this;
				view_frame.getGrid().getStore().filterBy(function(record){
					if(record.data.state == 'delete') return false;
					return true;
				});
			}
		});

		wnd.mnn_combo = new sw.Promed.SwDrugComplexMnnCombo({
			fieldLabel: lang['naimenovanie_ls'],
			allowBlank: false,
			anchor: '100%',
			onTrigger2Click: function() {
				if (this.disabled)
					return false;

				var searchWindow = 'swDrugComplexMnnSearchWindow';
				var combo = this;
				combo.disableBlurAction = true;
				getWnd(searchWindow).show({
					searchUrl: '/?c=DrugRequestProperty&m=loadDrugComplexMnnList',
					DrugRequestProperty_id: wnd.DrugRequestProperty_id,
					onHide: function() {
						combo.focus(false);
						combo.disableBlurAction = false;
					},
					onSelect: function (drugData) {
						combo.setValue(drugData.DrugComplexMnn_id);
						wnd.setDrugComplexMnnValue();
						getWnd(searchWindow).hide();
					}
				});
			},
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.prototype.initComponent.apply(this, arguments);
				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'DrugComplexMnn_id'
						},
						[
							{name: 'DrugComplexMnn_id', mapping: 'DrugComplexMnn_id'},
							{name: 'DrugComplexMnn_Name', mapping: 'DrugComplexMnn_Name'},
							{name: 'ATX_Code', mapping: 'ATX_Code'},
							{name: 'DrugComplexMnnName_Name', mapping: 'DrugComplexMnnName_Name'},
							{name: 'ClsDrugForms_Name', mapping: 'ClsDrugForms_Name'},
							{name: 'DrugComplexMnnDose_Name', mapping: 'DrugComplexMnnDose_Name'},
							{name: 'DrugComplexMnnFas_Name', mapping: 'DrugComplexMnnFas_Name'},
							{name: 'NTFR_Name', mapping: 'NTFR_Name'}
						]),
					url: '/?c=DrugRequestProperty&m=loadDrugComplexMnnList'
				});
			},
			listeners: {
				select: function(combo, record, index) {
					this.setLinkedFields(record);
				},
				change:  function(combo, newValue, oldValue) {
					if (newValue < 1) {
						combo.setLinkedFields(null);
					}
				}
			},
			setLinkedFields: function(record) {
				wnd.form.findField('ATX_Code').setValue(null);
				wnd.form.findField('DrugComplexMnnName_Name').setValue(null);
				wnd.form.findField('ClsDrugForms_Name').setValue(null);
				wnd.form.findField('DrugComplexMnnDose_Name').setValue(null);
				wnd.form.findField('DrugComplexMnnFas_Name').setValue(null);
				wnd.form.findField('NTFR_Name').setValue(null);

				if (record) {
					wnd.form.findField('ATX_Code').setValue(record.get('ATX_Code'));
					wnd.form.findField('DrugComplexMnnName_Name').setValue(record.get('DrugComplexMnnName_Name'));
					wnd.form.findField('ClsDrugForms_Name').setValue(record.get('ClsDrugForms_Name'));
					wnd.form.findField('DrugComplexMnnDose_Name').setValue(record.get('DrugComplexMnnDose_Name'));
					wnd.form.findField('DrugComplexMnnFas_Name').setValue(record.get('DrugComplexMnnFas_Name'));
					wnd.form.findField('NTFR_Name').setValue(record.get('NTFR_Name'));
				}
			}
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,			
			frame: true,
			region: 'north',
			labelAlign: 'right',
			height: 339,
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'dlreDrugListRequestEditForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 150,
				collapsible: true,
				url:'/?c=DrugListRequest&m=save',
				items: [
				wnd.mnn_combo, {
					fieldLabel: lang['klass_ntfr'],
					xtype: 'textfield',
					name: 'NTFR_Name',
					anchor: '100%',
					disabled: true
				}, {
					fieldLabel: lang['ath'],
					xtype: 'textfield',
					name: 'ATX_Code',
					anchor: '100%',
					disabled: true
				},
				{
					fieldLabel: lang['mnn'],
					xtype: 'textfield',
					name: 'DrugComplexMnnName_Name',
					anchor: '100%',
					disabled: true
				}, {
					fieldLabel: lang['lekarstvennaya_forma'],
					xtype: 'textfield',
					name: 'ClsDrugForms_Name',
					width: 210,
					disabled: true
				}, {
					fieldLabel: lang['dozirovka'],
					xtype: 'textfield',
					name: 'DrugComplexMnnDose_Name',
					width: 210,
					disabled: true
				}, {
					fieldLabel: lang['fasovka'],
					xtype: 'textfield',
					name: 'DrugComplexMnnFas_Name',
					width: 210,
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['tsena_proizv'],
					name: 'Jnvlp_Price',
					disabled: true,
					width: 210
				}, {
					xtype: 'textfield',
					fieldLabel: lang['opt_tsena_bez_nds_reg'],
					name: 'Jnvlp_WholesalePrice',
					disabled: true,
					width: 210
				}, {
					xtype: 'textfield',
					fieldLabel: lang['primechanie'],
					name: 'DrugListRequest_Comment',
					width: 210,
                    maxLength: 200
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							fieldLabel: lang['tsena_v_zayavke_rub'],
							xtype: 'numberfield',
							name: 'DrugListRequest_Price',
							allowBlank: true,
							allowNegative: false,
							allowDecimals: true,
							width: 210
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'button',
							id: 'dlre_PriceButton',
                            hidden: true,
							style: 'margin-left: 3px;',
							handler: function() {
								var mnn_id = wnd.mnn_combo.getValue();
								if (mnn_id > 0) {
									Ext.Ajax.request({
										url: '/?c=DrugRequestProperty&m=getAveragePrice',
										params: {
											DrugListRequest_id: wnd.DrugListRequest_id,
											DrugComplexMnn_id: mnn_id
										},
										success: function(response){
											var result = Ext.util.JSON.decode(response.responseText);
											if (result[0] && result[0].AveragePrice && result[0].AveragePrice > 0) {
												wnd.form.findField('DrugListRequest_Price').setValue(result[0].AveragePrice);
											} else {
												sw.swMsg.alert(lang['oshibka'], lang['net_dannyih_dlya_rasscheta_sredney_tsenyi']);
											}
										}
									});
								} else {
									sw.swMsg.alert(lang['oshibka'], lang['dlya_rasscheta_sredney_tsenyi_neobhodimo_ukazat_mnn']);
								}
							},
							iconCls: null,
							text: lang['rasschitat']
						}]
					}]
				},
				{
					fieldLabel: lang['ispolzovanie_tn'],
					hiddenName: 'DrugTorgUse_id',
					xtype: 'swcommonsprcombo',
					sortField:'DrugTorgUse_Code',
					comboSubject: 'DrugTorgUse',
					allowBlank: false,
					width: 210
				}, {
					fieldLabel: lang['problema_s_zakupom'],
					name: 'DrugListRequest_IsProblem',
					xtype: 'checkbox'
				}, {
					hidden: true,
					items: [{
						hiddenName: 'hiddenField1',
						xtype: 'swrlstradenamescombo'
					}]
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			bodyStyle: 'padding: 7px;',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
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
			items:[
				form,
				wnd.TorgGrid
			]
		});
		sw.Promed.swDrugListRequestEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('dlreDrugListRequestEditForm').getForm();
	}	
});