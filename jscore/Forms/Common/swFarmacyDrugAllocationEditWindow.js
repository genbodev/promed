/**
* swFarmacyDrugAllocationEditWindow - окно редактирования документа распределения ЛС по апатекам
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov Rustam
* @version      04.11.2013
* @comment      
*/
sw.Promed.swFarmacyDrugAllocationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['dokument_raspredeleniya_ls_po_aptekam'],
	layout: 'border',
	id: 'FarmacyDrugAllocationEditWindow',	
	modal: true,
	width: 950,
	height: 465,
	resizable: false,
	maximizable: true,
	maximized: true,
	firstTabIndex: 10000,
	plain: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function(silent) {
		var wnd = this;
		var form = wnd.findById('FarmacyDrugAllocationEditForm').getForm();
		var viewframe =  wnd.findById('FarmacyDrugAllocationDrugGrid');
		var viewframe_spec = wnd.findById('FarmacyDrugAllocationDrugGridSpec');

		form.findField('OrderAllocationDrugJSON').setValue(viewframe.getJSONChangedData());
		form.findField('OrderAllocationDrugFarmacyJSON').setValue(viewframe_spec.getJSONChangedData());

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('FarmacyDrugAllocationEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		this.getLoadMask(lang['podojdite_idet_sohranenie']).show();
		var params = new Object();
		params.action = wnd.action;
		params.WhsDocumentUc_pid = wnd.form.findField('SourceWhsDocumentUc_id').getValue();
		params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
		params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();
		params.WhsDocumentUc_Sum = wnd.form.findField('WhsDocumentUc_Sum').getValue();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				wnd.callback();
				if ( silent == false ) {
					wnd.hide();
				} else {
					wnd.WhsDocumentUc_id = action.result.WhsDocumentUc_id;
					wnd.action = 'edit';
					form.findField('WhsDocumentUc_id').setValue(wnd.WhsDocumentUc_id);
					form.findField('WhsDocumentOrderAllocation_id').setValue(wnd.WhsDocumentUc_id);
					if ( typeof silent == 'function' ) {
						silent();
					}
				}
			}
		});

		return true;
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.findById('FarmacyDrugAllocationEditForm').getForm();
		var field_arr = [
			'WhsDocumentUc_Num',
			'WhsDocumentUc_Date',
			'WhsDocumentUc_Name',
			'SourceWhsDocumentUc_id',
			'Org_id'
		];

		for (var i in field_arr) if (form.findField(field_arr[i])) {
			if (disable)
				form.findField(field_arr[i]).disable();
			else
				form.findField(field_arr[i]).enable();
		}

		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}

		wnd.findById('FarmacyDrugAllocationDrugGrid').setReadOnly(disable);
	},
	show: function() {
		var wnd = this;

		sw.Promed.swFarmacyDrugAllocationEditWindow.superclass.show.apply(this, arguments);

		var viewframe = wnd.findById('FarmacyDrugAllocationDrugGrid');
		var viewframe_spec = wnd.findById('FarmacyDrugAllocationDrugGridSpec');

		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentUc_id = null;
		this.WhsDocumentType_Name = lang['dokument_raspredeleniya_ls_po_aptekam'];

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].WhsDocumentUc_id ) {
			this.WhsDocumentUc_id = arguments[0].WhsDocumentUc_id;
		}
		if ( arguments[0].WhsDocumentType_id ) {
			this.WhsDocumentType_id = arguments[0].WhsDocumentType_id;
		}

		this.form.reset();
		viewframe.removeAll({clearAll: true});
		viewframe_spec.removeAll({clearAll: true});
		this.form.findField('Org_id').getStore().removeAll();
		viewframe_spec.setReadOnly(this.action == 'view');

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		switch (this.action) {
			case 'add':
                this.setTitle(this.WhsDocumentType_Name + lang['_dobavlenie']);
				Ext.Ajax.request({
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							this.form.findField('WhsDocumentUc_Num').setValue(response_obj.WhsDocumentUc_Num);
							this.form.findField('WhsDocumentUc_Date').setValue(getGlobalOptions().date);
							this.form.findField('WhsDocumentUc_Name').setValue(response_obj.WhsDocumentUc_Num + '. ' + getGlobalOptions().date + ' - ' + wnd.WhsDocumentType_Name);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_dokumenta'], function() {
								this.form.findField('WhsDocumentUc_Num').focus(true);
							});
						}
					}.createDelegate(this),
					url: '/?c=WhsDocumentOrderAllocation&m=getWhsDocumentOrderAllocationNumber'
				});
				wnd.form.findField('SourceWhsDocumentUc_id').getStore().load();
				wnd.form.findField('WhsDocumentType_id').setValue(this.WhsDocumentType_id);
				wnd.findById('FDAEW_BtnGen').show();
				this.setDisabled(false);
				break;
			case 'edit':
			case 'view':
                this.setTitle(this.WhsDocumentType_Name + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				wnd.findById('FDAEW_BtnGen').hide();
				Ext.Ajax.request({
					params:{
						WhsDocumentUc_id: wnd.WhsDocumentUc_id
					},
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (!result[0]) { return false}
							wnd.form.setValues(result[0]);
							viewframe.loadData({
								url: '/?c=WhsDocumentOrderAllocationDrug&m=loadFarmDrugList',
								params:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								globalFilters:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								callback: function() {
									viewframe.selectFirstRow();
								}
							});
							viewframe_spec.loadData({
								url: '/?c=WhsDocumentOrderAllocationDrug&m=loadFarmDrugSpecList',
								params:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								globalFilters:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								callback: function() {
									viewframe_spec.filter_data = null;
									viewframe.selectFirstRow();
								}
							});
							if (wnd.form.findField('WhsDocumentStatusType_id').getValue() > 1) {
								wnd.buttons[2].hide();
							} else {
								wnd.buttons[2].show();
							}
							var params = new Object();
							if (result[0].WhsDocumentUc_pid && result[0].WhsDocumentUc_pid > 0) {
								params.WhsDocumentUc_id = result[0].WhsDocumentUc_pid;
							}
							if (result[0].Org_id && result[0].Org_id > 0) {
								params.Org_id = result[0].Org_id;
							}
							var doc_combo = wnd.form.findField('SourceWhsDocumentUc_id');
							var org_combo = wnd.form.findField('Org_id');
							doc_combo.getStore().load({
								params: params,
								callback: function() {
									if (doc_combo.getStore().getCount() > 0) {
										var record = doc_combo.getStore().getAt(0);
										doc_combo.fireEvent('select', doc_combo, record, 0);
										doc_combo.setValue(record.get('WhsDocumentUc_id'));
									}
								}
							});
							org_combo.getStore().load({
								params: params,
								callback: function() {
									if (org_combo.getStore().getCount() > 0) {
										var record = org_combo.getStore().getAt(0);
										org_combo.fireEvent('select', org_combo, record, 0);
										org_combo.setValue(record.get('Org_id'));
									}
								}
							});
							wnd.form.findField('SourceWhsDocumentUc_id').disable();
							wnd.form.findField('Org_id').disable();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih'], function() {
								this.form.findField('WhsDocumentUc_Num').focus(true);
							});
						}
					}.createDelegate(this),
					url: '/?c=WhsDocumentOrderAllocation&m=load'
				});
				this.setDisabled(this.action=='view');
				break;
		}


	},
	initComponent: function() {
		var current_window = this;

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'background:#DFE8F6; padding: 0;',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'FarmacyDrugAllocationEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6; padding: 5px;',
				border: false,
				labelWidth: 180,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url: '/?c=WhsDocumentOrderAllocation&m=save',
				layout: 'form',
				items: [{
					name: 'WhsDocumentUc_id',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'WhsDocumentOrderAllocation_id',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'WhsDocumentType_id',
					value: 12,
					xtype: 'hidden'
				}, {
					name: 'OrderAllocationDrugJSON',
					xtype: 'hidden'
				}, {
					name: 'OrderAllocationDrugFarmacyJSON',
					xtype: 'hidden'
				}, {
					name: 'WhsDocumentUc_Sum',
					xtype: 'hidden'
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 350,
						items: [{
							xtype: 'textfield',
							fieldLabel : lang['№'],
							width: 120,
							tabIndex: current_window.firstTabIndex + 10,
							name: 'WhsDocumentUc_Num',
							value: '',
							allowBlank:false
						}]
					}, {
						layout: 'form',
						border: false,
						width: 235,
						labelWidth: 30,
						items: [{
							xtype: 'swdatefield',
							fieldLabel : lang['ot'],
							tabIndex: current_window.firstTabIndex + 10,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'WhsDocumentUc_Date',
							allowBlank: false
						}]
					}, {
						layout: 'form',
						border: false,
						width: 250,
						labelWidth: 115,
						items: [{
							fieldLabel: lang['status_dokumenta'],
							tabIndex: current_window.firstTabIndex + 10,
							hiddenName: 'WhsDocumentStatusType_id',
							xtype: 'swcommonsprcombo',
							sortField:'WhsDocumentStatusType_Code',
							comboSubject: 'WhsDocumentStatusType',
							width: 130,
							disabled: true,
							allowBlank:true,
							value: 1
						}]
					}]
				}, {
					xtype: 'textfield',
					fieldLabel : lang['naimenovanie'],
					tabIndex: current_window.firstTabIndex + 10,
					width: 650,
					name: 'WhsDocumentUc_Name',
					allowBlank:false
				}, {
					fieldLabel : lang['raznaryadka_mo'],
					hiddenName: 'SourceWhsDocumentUc_id',
					xtype: 'swbaseremotecombo',
					valueField: 'WhsDocumentUc_id',
					displayField: 'WhsDocumentUc_Name',
					allowBlank: false,
					editable: true,
					lastQuery: '',
					validateOnBlur: true,
					triggerAction: 'all',
					width: 650,
					listeners: {
						'beforequery': function(qe){
							delete qe.combo.lastQuery;
						},
						'change': function(combo, newValue, oldValue) {
							var org_combo = current_window.form.findField('Org_id');

							if (newValue > 0) {
								var idx = combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == newValue; });
								if (idx > -1) {
									var record = combo.getStore().getAt(idx);
									current_window.form.findField('DrugFinance_id').setValue(record.get('DrugFinance_id'));
									current_window.form.findField('WhsDocumentCostItemType_id').setValue(record.get('WhsDocumentCostItemType_id'));
									org_combo.getStore().load({
										params: {
											WhsDocumentUc_id: record.get('WhsDocumentUc_id')
										},
										callback: function(){
											if(org_combo.getStore().getCount() > 0) {
												org_combo.setValue(org_combo.getStore().getAt(0).get('Org_id'));
											}
										}
									});
								}
							} else {
								current_window.form.findField('DrugFinance_id').setValue(null);
								current_window.form.findField('WhsDocumentCostItemType_id').setValue(null);
								org_combo.setValue(null);
								org_combo.getStore().removeAll();
							}
						}
					},
					onTrigger2Click: function() {
						if (!this.disabled) {
							this.clearValue()
							current_window.form.findField('DrugFinance_id').setValue(null);
							current_window.form.findField('WhsDocumentCostItemType_id').setValue(null);
							current_window.form.findField('Org_id').setValue(null);
							current_window.form.findField('Org_id').getStore().removeAll();
						}
					},
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'WhsDocumentUc_id'
						}, [
							{name: 'WhsDocumentUc_id', mapping: 'WhsDocumentUc_id'},
							{name: 'WhsDocumentUc_Name', mapping: 'WhsDocumentUc_Name'},
							{name: 'DrugFinance_id', mapping: 'DrugFinance_id'},
							{name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id'}
						]),
						url: '/?c=WhsDocumentOrderAllocation&m=loadSourceWhsDocumentUcCombo'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><tr><td>{WhsDocumentUc_Name}</td></tr></table>',
						'</div></tpl>'
					)
				}, {
					fieldLabel : lang['organizatsiya'],
					hiddenName: 'Org_id',
					xtype: 'swbaselocalcombo',
					valueField: 'Org_id',
					displayField: 'Org_Name',
					allowBlank: false,
					editable: true,
					lastQuery: '',
					validateOnBlur: true,
					width: 650,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'Org_id'
						}, [
							{name: 'Org_id', mapping: 'Org_id'},
							{name: 'Org_Name', mapping: 'Org_Name'}
						]),
						url: '/?c=WhsDocumentOrderAllocation&m=loadSourceWhsDocumentUcOrgCombo'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><tr><td>{Org_Name}</td></tr></table>',
						'</div></tpl>'
					)
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 450,
						items: [{
							fieldLabel: lang['istochnik_finansirovaniya'],
							hiddenName: 'DrugFinance_id',
							xtype: 'swcommonsprcombo',
							tabIndex: current_window.firstTabIndex + 10,
							comboSubject: 'DrugFinance',
							width: 260,
							allowBlank: false,
							disabled: true
						}]
					},{
						layout: 'form',
						border: false,
						labelWidth: 120,
						width: 450,
						items: [{
							fieldLabel: lang['statya_rashoda'],
							hiddenName: 'WhsDocumentCostItemType_id',
							xtype: 'swcommonsprcombo',
							tabIndex: current_window.firstTabIndex + 10,
							comboSubject: 'WhsDocumentCostItemType',
							width: 260,
							typeCode: 'int',
							allowBlank: false,
							disabled: true
						}]
					}]
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 510,
						items: [{
							style: "padding-left: 185px; padding-bottom: 10px",
							xtype: 'button',
							id: 'FDAEW_BtnGen',
							text: lang['rasschitat'],
							handler: function() {
								var base_form = current_window.findById('FarmacyDrugAllocationEditForm').getForm();
								var viewframe = current_window.findById('FarmacyDrugAllocationDrugGrid');
								var viewframe_spec = current_window.findById('FarmacyDrugAllocationDrugGridSpec');

								if ( !base_form.isValid() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											current_window.findById('FarmacyDrugAllocationEditForm').getFirstInvalidEl().focus(true);
										},
										icon: Ext.Msg.WARNING,
										msg: ERR_INVFIELDS_MSG,
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}

								var params = {};
								params.WhsDocumentType_id = current_window.WhsDocumentType_id;
								params.Org_id = base_form.findField('Org_id').getValue();
								params.DrugFinance_id = base_form.findField('DrugFinance_id').getValue();
								params.WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getValue();
								params.WhsDocumentUc_id = base_form.findField('SourceWhsDocumentUc_id').getValue();

								viewframe.loadData({
									url: '/?c=WhsDocumentOrderAllocationDrug&m=loadRAWFarmDrugList',
									params: params,
									globalFilters: params,
									callback: function () {
										viewframe.setPlanKolvo();
										viewframe.selectFirstRow();
									}
								});

								viewframe_spec.loadData({
									url: '/?c=WhsDocumentOrderAllocationDrug&m=loadRAWFarmDrugSpecList',
									params: params,
									globalFilters: params,
									callback: function () {
										viewframe_spec.filter_data = null;
										viewframe.setPlanKolvo();
										viewframe.selectFirstRow();
									}
								});
							}
						}]
					}]
				},
					new sw.Promed.Panel({
						autoHeight: true,
						border: true,
						frame: true,
						collapsible: true,
						layout: 'form',
						style: 'margin-bottom: 0.5em;',
						title: lang['medikamentyi_podlejaschie_raspredeleniyu'],
						items: [new sw.Promed.ViewFrame({
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 150,
							autoLoadData: false,
							border: true,
							dataUrl: '/?c=WhsDocumentOrderAllocationDrug&m=loadFarmDrugList',
							height: 230,
							region: 'north',
							object: 'WhsDocumentOrderAllocation',
							id: 'FarmacyDrugAllocationDrugGrid',
							paging: false,
							saveAtOnce:false,
							style: 'margin-bottom: 0px',
							stringfields: [
								{name: 'WhsDocumentOrderAllocationDrug_id', type: 'int', header: 'ID', key: true},
								{name: 'Drug_Name', type: 'string', header: lang['medikament'], id: 'autoexpand'},
								{name: 'Drug_id', type: 'int', hidden: true},
								{name: 'Okei_id', type: 'int', hidden: true},
								{name: 'WhsDocumentOrderAllocationDrug_Kolvo', type: 'string', header: lang['kol-vo_dost'], width: 100},
								{name: 'WhsDocumentOrderAllocationDrug_Price', type: 'string', hidden: true},
								{name: 'Plan_Kolvo', type: 'string', header: lang['kol-vo_raspredeleno'], width: 140},
								{name: 'state', hidden: true}
							],
							title: false,
							toolbar: false,
							contextmenu: false,
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
							},
							setPlanKolvo: function() { //устанавливает значения для столбца "Кол-во распределено" исходя из содержимого спецификации распределения по аптекам
								var plan = new Object();
								var viewframe_spec = current_window.findById('FarmacyDrugAllocationDrugGridSpec');

								viewframe_spec.clearFilter();
								viewframe_spec.getGrid().getStore().each(function(record){
									var key = record.get('Drug_id');
									var kolvo = record.get('WhsDocumentOrderAllocationDrug_Kolvo')*1;
									if (record.get('state') != 'delete' && key != '' && kolvo > 0) {
										if (!plan[key]) { plan[key] = 0; }
										plan[key] += kolvo;
									}
								});
								this.setFilter();

								this.clearFilter();
								this.getGrid().getStore().each(function(record){
									var key = record.get('Drug_id');
									if (plan[key] > 0 || true) {
										record.set('Plan_Kolvo', plan[key]);
									}
								});
								this.setFilter();
							},
							onRowSelect: function(sm,index,rec) {
								var viewframe_spec = current_window.findById('FarmacyDrugAllocationDrugGridSpec');
								viewframe_spec.filter_data = null;
								if (rec.get('Drug_id') > 0) {
									viewframe_spec.filter_data = {
										Drug_id:  rec.get('Drug_id')
									};
									viewframe_spec.getGrid().getStore().filterBy(function(record){
										return (record.get('Drug_id') == rec.get('Drug_id'));
									});
								}
							},
							selectFirstRow: function() {
								var viewframe = this;
								if (viewframe.getGrid().getStore().getCount() > 0) {
									viewframe.getGrid().getView().focusRow(0);
									viewframe.getGrid().getSelectionModel().selectFirstRow();
								}
							}
						})]
					}),
					new sw.Promed.Panel({
						autoHeight: true,
						bodyStyle: 'padding: 0;',
						border: true,
						frame: true,
						collapsible: true,
						layout: 'form',
						style: 'margin-bottom: 0.5em;',
						title: lang['raspredelenie_po_aptekam'],
						items: [
							new sw.Promed.ViewFrame({
								autoExpandColumn: 'autoexpand',
								autoExpandMin: 150,
								autoLoadData: false,
								border: true,
								dataUrl: '/?c=WhsDocumentOrderAllocationDrug&m=loadFarmDrugSpecList',
								height: 230,
								region: 'north',
								object: 'WhsDocumentOrderAllocation',
								id: 'FarmacyDrugAllocationDrugGridSpec',
								paging: false,
								saveAtOnce:false,
								style: 'margin-bottom: 0px',
								stringfields: [
									{name: 'WhsDocumentOrderAllocationDrug_id', type: 'int', header: 'ID', key: true},
									{name: 'WhsDocumentOrderAllocation_id', type: 'int', hidden: true},
									{name: 'Org_id', type: 'int', hidden: true},
									{name: 'Org_Name', type: 'string', header: lang['apteka'], width: 200},
									{name: 'Drug_id', type: 'int', hidden: true},
									{name: 'Okei_id', type: 'int', hidden: true},
									{name: 'Drug_Name', type: 'string', header: lang['medikament'], id: 'autoexpand'},
									{name: 'WhsDocumentOrderAllocationDrug_Kolvo', type: 'string', header: lang['kol-vo'], width: 100, editor: new Ext.form.NumberField()},
									{name: 'WhsDocumentOrderAllocationDrug_Price', type: 'string', hidden: true},
									{name: 'state', hidden: true}
								],
								title: false,
								toolbar: false,
								contextmenu: false,
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
										if (this.filter_data) {
											return (record.get('state') != 'delete' && record.get('Drug_id') == this.filter_data.Drug_id);
										} else {
											return (record.get('state') != 'delete');
										}
									});
								},
								onAfterEdit: function(o) {
									var viewframe_spec = this;
									var viewframe = current_window.findById('FarmacyDrugAllocationDrugGrid');

									if (o.value < 0) {
										o.record.set('WhsDocumentOrderAllocationDrug_Kolvo', o.originalValue);
										o.record.commit();
									} else {
										var available = 0;

										viewframe.getGrid().getStore().each(function(record){
											if (record.get('Drug_id') == o.record.get('Drug_id')) {
												available += record.get('WhsDocumentOrderAllocationDrug_Kolvo')*1;
											}
										});
										viewframe_spec.getGrid().getStore().each(function(record){
											if (record.get('Drug_id') == o.record.get('Drug_id') && record.get('Org_id') != o.record.get('Org_id')) {
												available -= record.get('WhsDocumentOrderAllocationDrug_Kolvo')*1;
											}
										});

										if (o.record.get('WhsDocumentOrderAllocationDrug_Kolvo') > available && available >= 0) {
											o.record.set('WhsDocumentOrderAllocationDrug_Kolvo', available);
											o.record.commit();
										}

										viewframe.setPlanKolvo();
										o.record.set('state', 'edit');
										o.record.commit();
									}
								}
							})
						]
					})]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
				[{
					handler: function() {
						this.ownerCt.doSave(false);
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				}, {
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
			items:[form]
		});
		sw.Promed.swFarmacyDrugAllocationEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('FarmacyDrugAllocationEditForm').getForm();
	}
});