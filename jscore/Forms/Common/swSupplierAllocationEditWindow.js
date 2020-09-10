/**
* swSupplierAllocationEditWindow - окно редактирования плана поставки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov Rustam
* @version      03.10.2013
* @comment      
*/
sw.Promed.swSupplierAllocationEditWindow = Ext.extend(sw.Promed.BaseForm, { //getWnd('swSupplierAllocationEditWindow').show();
	title: lang['plan_postavok'],
	layout: 'border',
	id: 'SupplierAllocationEditWindow',	
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
	setOrg: function(combo, data) { //вспомогательная функция для комбобоксов для выбора организации
		if (!data && combo.getValue() > 0) {
			Ext.Ajax.request({
				url: C_ORG_LIST,
				params: {
					Org_id: combo.getValue()
				},
				success: function(response){
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && result[0].Org_id && result[0].Org_Name) {
						combo.setValue(result[0].Org_id);
						combo.setRawValue(result[0].Org_Name);
					}
				}
			});
		} else if (data) {
			combo.setValue(data['Org_id']);
			combo.setRawValue(data['Org_Name']);
		}
	},
	doSave:  function(silent) {
		var wnd = this;
		var form = wnd.findById('SupplierAllocationEditForm').getForm();
		var viewframe =  wnd.findById('SupplierAllocationDrugGrid');
		var viewframe_spec = wnd.findById('SupplierAllocationDrugGridSpec');

		form.findField('OrderAllocationDrugJSON').setValue(viewframe.getJSONChangedData());
		form.findField('OrderAllocationDrugSpecJSON').setValue(viewframe_spec.getJSONChangedData());

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('SupplierAllocationEditForm').getFirstInvalidEl().focus(true);
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
		//params.WhsDocumentUc_Sum = wnd.form.findField('WhsDocumentUc_Sum').getValue();
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
		var form = wnd.findById('SupplierAllocationEditForm').getForm();
		var field_arr = [
			'WhsDocumentUc_Num',
			'WhsDocumentUc_Date',
			'WhsDocumentUc_Name',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id'
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

		wnd.findById('SupplierAllocationDrugGrid').setReadOnly(disable);
	},
	show: function() {
		var wnd = this;

		sw.Promed.swSupplierAllocationEditWindow.superclass.show.apply(this, arguments);

		var viewframe = wnd.findById('SupplierAllocationDrugGrid');
		var viewframe_spec = wnd.findById('SupplierAllocationDrugGridSpec');

		if ( getGlobalOptions().org_id > 0 ) {
			this.form.findField('Org_id').getStore().load({
				params: {
					Object:'Org',
					Org_id: getGlobalOptions().org_id,
					Org_Name:''
				},
				callback: function() {
					wnd.form.findField('Org_id').setValue(getGlobalOptions().org_id);
				}
			});
		}

		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentUc_id = null;
        this.WhsDocumentType_Name = lang['plan_postavok'];

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
				wnd.form.findField('WhsDocumentType_id').setValue(this.WhsDocumentType_id);
				wnd.findById('SAEW_BtnGen').show();
				this.setDisabled(false);
				break;
			case 'edit':
			case 'view':
                this.setTitle(this.WhsDocumentType_Name + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				wnd.findById('SAEW_BtnGen').hide();
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
								url: '/?c=WhsDocumentOrderAllocationDrug&m=loadSupList',
								params:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								globalFilters:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()}
							});
							viewframe_spec.loadData({
								url: '/?c=WhsDocumentOrderAllocationDrug&m=loadSupSpecList',
								params:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								globalFilters:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()}
							});
							if (wnd.form.findField('WhsDocumentStatusType_id').getValue() > 1) {
								wnd.buttons[2].hide();
							} else {
								wnd.buttons[2].show();
							}
							var params = new Object();
							if (result[0].DrugRequest_id && result[0].DrugRequest_id > 0 && this.action == 'view') {
								params.DrugRequest_id = result[0].DrugRequest_id;
							}
							wnd.form.findField('DrugRequest_id').getStore().load({
								params: params
							});
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
				id: 'SupplierAllocationEditForm',
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
					name: 'OrderAllocationDrugSpecJSON',
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
					xtype: 'sworgcombo',
					fieldLabel : lang['organizatsiya'],
					tabIndex: current_window.firstTabIndex + 10,
					hiddenName: 'Org_id',
					id: 'SAEW_Org_id',
					width: 650,
					editable: false,
					onTrigger1Click: function() {
						if (this.disabled)
							return false;
						var combo = this;
						if (!this.formList) {
							this.formList = new sw.Promed.swListSearchWindow({
								title: lang['poisk_organizatsii'],
								id: 'OrgSearch_' + this.id,
								object: 'Org',
								prefix: 'lsssae',
								editformclassname: 'swOrgEditWindow',
								store: this.getStore()
							});
						}
						this.formList.show({
							params: {
								OrgType_Code: 16, //16 - Поставщик
								Org_pid: 0 //исключаем дочерние организации
							},
							onSelect: function(data) {
								current_window.setOrg(combo, data);
							}
						});
						return false;
					},
					allowBlank:false
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
							allowBlank: false
							, value: 27 //временно для тестов
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
							allowBlank: false
							, value: 2 //временно для тестов
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
							id: 'SAEW_BtnGen',
							text: lang['rasschitat_plan'],
							//iconCls: 'resetsearch16',
							handler: function() {
								var base_form = current_window.findById('SupplierAllocationEditForm').getForm();
								var viewframe = current_window.findById('SupplierAllocationDrugGrid');
								var viewframe_spec = current_window.findById('SupplierAllocationDrugGridSpec');

								if ( !base_form.isValid() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											current_window.findById('SupplierAllocationEditForm').getFirstInvalidEl().focus(true);
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

								viewframe.loadData({
									url: '/?c=WhsDocumentOrderAllocationDrug&m=loadRAWSupList',
									params: params,
									globalFilters: params,
									callback: function () {
									}
								});

								viewframe_spec.loadData({
									url: '/?c=WhsDocumentOrderAllocationDrug&m=loadRAWSupSpecList',
									params: params,
									globalFilters: params,
									callback: function () {
										viewframe.setPlanKolvo();
									}
								});
							}
						}]
					}]
				},
					new sw.Promed.Panel({
						autoHeight: true,
						//bodyStyle: 'padding-top: 0.5em;',
						border: true,
						frame: true,
						collapsible: true,
						layout: 'form',
						style: 'margin-bottom: 0.5em;',
						title: lang['medikamentyi_podlejaschie_raspredeleniyu'],
						items: [/*{
							layout: 'column',
							labelWidth: 40,
							border: false,
							items: [{
								layout: 'form',
								border: false,
								width: 375,
								items: [{
									xtype: 'swdrugcomplexmnncombo',
									fieldLabel: lang['mnn'],
									width: 300,
									tabIndex: current_window.firstTabIndex + 10,
									id: 'SAEW_DrugComplexMnn_id',
									value: '',
									allowBlank: true,
									listeners: {
										'select': function(combo, record) {
											if(typeof this.onSelectDrug == 'function') {
												this.onSelectDrug(combo, record);
											}
										}.createDelegate(this)
									}
								}]
							}, {
								layout: 'form',
								border: false,
								width: 375,
								items: [{
									xtype: 'swdrugsimplecombo',
									fieldLabel : lang['torg'],
									width: 300,
									tabIndex: current_window.firstTabIndex + 10,
									name: 'Drug_id',
									id: 'SAEW_Drug_id',
									value: '',
									allowBlank: true
								}]
							}, {
								layout: 'form',
								border: false,
								items: [{
									style: "padding-left: 0px",
									xtype: 'button',
									id: 'SAEW_BtnSearch',
									text: lang['poisk'],
									iconCls: 'search16',
									handler: function() {
										var win = Ext.getCmp('SupplierAllocationEditWindow');
										var form = win.findById('SupplierAllocationEditForm').getForm();
										win.findDrug({
											Drug_id: form.findField('Drug_id').getValue(),
											DrugComplexMnn_id: form.findField('DrugComplexMnn_id').getValue()
										});
									}
								}]
							}, {
								layout: 'form',
								border: false,
								items: [{
									style: "padding-left: 10px",
									xtype: 'button',
									id: 'SAEW_BtnClear',
									text: lang['sbros'],
									iconCls: 'resetsearch16',
									handler: function() {
										var form = Ext.getCmp('SupplierAllocationEditForm').getForm();
										form.findField('Drug_id').reset();
										form.findField('DrugComplexMnn_id').reset();
									}
								}]
							}]
						},*/
						new sw.Promed.ViewFrame({
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 150,
							autoLoadData: false,
							border: true,
							dataUrl: '/?c=WhsDocumentOrderAllocationDrug&m=loadSupList',
							height: 180,
							region: 'north',
							object: 'WhsDocumentOrderAllocation',
							id: 'SupplierAllocationDrugGrid',
							paging: false,
							saveAtOnce:false,
							style: 'margin-bottom: 0px',
							stringfields: [
								{name: 'WhsDocumentOrderAllocationDrug_id', type: 'int', header: 'ID', key: true},
								{name: 'Drug_Name', type: 'string', header: lang['medikament'], id: 'autoexpand'},
								{name: 'Drug_id', type: 'int', hidden: true},
								{name: 'Okei_id', type: 'int', hidden: true},
								{name: 'WhsDocumentOrderAllocationDrug_Allocation', type: 'string', header: lang['kol-vo_dost'], width: 100},
								{name: 'WhsDocumentOrderAllocationDrug_PriceNDS', type: 'string', hidden: true},
								{name: 'Plan_Kolvo', type: 'string', header: lang['kol-vo_v_plane'], width: 140},
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
							setPlanKolvo: function() { //устанавливает значения для столбца "Кол-во в плане" исходя из содержимого плана поставок
								var plan = new Object();
								var viewframe_spec = current_window.findById('SupplierAllocationDrugGridSpec');

								viewframe_spec.getGrid().getStore().each(function(record){
									var drug_id = record.get('Drug_id');
									var kolvo = record.get('WhsDocumentOrderAllocationDrug_Allocation')*1;
									if (drug_id > 0 && kolvo > 0) {
										if (!plan[drug_id]) { plan[drug_id] = 0; }
										plan[drug_id] += kolvo;
									}
								});

								this.clearFilter();
								this.getGrid().getStore().each(function(record){
									if (plan[record.get('Drug_id')] || true) {
										record.set('Plan_Kolvo', plan[record.get('Drug_id')]);
									}
								});
								this.setFilter();
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
						title: lang['plan_postavok'],
						items: [
							new sw.Promed.ViewFrame({
								autoExpandColumn: 'autoexpand',
								autoExpandMin: 150,
								autoLoadData: false,
								border: true,
								dataUrl: '/?c=WhsDocumentOrderAllocationDrug&m=loadSupSpecList',
								height: 180,
								region: 'north',
								object: 'WhsDocumentOrderAllocation',
								id: 'SupplierAllocationDrugGridSpec',
								paging: false,
								saveAtOnce:false,
								style: 'margin-bottom: 0px',
								stringfields: [
									{name: 'WhsDocumentOrderAllocationDrug_id', type: 'int', header: 'ID', key: true},
									{name: 'Org_id', type: 'int', hidden: true},
									{name: 'Org_Name', type: 'string', header: lang['apteka'], width: 200},
									{name: 'Drug_id', type: 'int', hidden: true},
									{name: 'Okei_id', type: 'int', hidden: true},
									{name: 'Drug_Name', type: 'string', header: lang['torgovoe_naimenovanie'], id: 'autoexpand'},
									{name: 'WhsDocumentOrderAllocationDrug_Allocation', mapping: 'kolvo', type: 'string', header: lang['kol-vo'], width: 100},
									{name: 'WhsDocumentOrderAllocationDrug_PriceNDS', mapping: 'price', type: 'string', hidden: true},
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
								onAfterEdit: function(o) {
									//o.record.set('WhsDocumentUc_Sum', parseInt(o.record.get('WhsDocumentOrderAllocationDrug_Allocation')) * parseFloat(o.record.get('WhsDocumentOrderAllocationDrug_PriceNDS')));
								}
							})
						]
					})]
			}]/*,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'WhsDocumentUc_pid'},
				{name: 'WhsDocumentUc_Num'},
				{name: 'WhsDocumentUc_Name'},
				{name: 'WhsDocumentType_id'},
				{name: 'WhsDocumentUc_Date'},
				{name: 'Org_sid'},
				{name: 'Org_cid'},
				{name: 'Org_pid'},
				{name: 'Org_rid'},
				{name: 'WhsDocumentUc_Sum'},
				{name: 'WhsDocumentSupply_id'},
				{name: 'WhsDocumentUc_id'},
				{name: 'WhsDocumentSupply_ProtNum'},
				{name: 'WhsDocumentSupply_ProtDate'},
				{name: 'WhsDocumentSupplyType_id'},
				{name: 'WhsDocumentSupply_ExecDate'},
				{name: 'DrugFinance_id'},
				{name: 'WhsDocumentCostItemType_id'},
				{name: 'WhsDocumentStatusType_id'}
			])*/
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
		sw.Promed.swSupplierAllocationEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('SupplierAllocationEditForm').getForm();
	}
});