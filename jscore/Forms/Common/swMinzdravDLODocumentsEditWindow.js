/**
* swMinzdravDLODocumentsEditWindow - окно редактирования "Распоряжение на включение в резерв"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Alexandr Chebukin
* @version      23.12.2012
* @comment      
*/
sw.Promed.swMinzdravDLODocumentsEditWindow = Ext.extend(sw.Promed.BaseForm, { //getWnd('swMinzdravDLODocumentsEditWindow').show();
	autoHeight: false,
	title: lang['rasporyajenie_na_vklyuchenie_v_rezerv'],
	layout: 'border',
	id: 'MinzdravDLODocumentsEditWindow',
	modal: true,
	//shim: false,
	width: 950,
	height: 465,
	resizable: false,
	maximizable: true,
	maximized: true,
	firstTabIndex: 10000,
	plain: true,
	debug_mode: false, //только для разработки
	demand: null,
	delivery_graph: null,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	findDrug: function(params) { //функция для поиска конкретного медикамента в списке заказанных
		var wnd = this;		
		var viewframe =  wnd.findById('WhsDocumentOrderReserveDrugGrid');
		if (params.DrugComplexMnn_id > 0 || params.Drug_id) {
			var item_index = viewframe.getGrid().getStore().findBy(function(r,id) {
				if(r.get('DrugComplexMnn_id') == params.DrugComplexMnn_id)
					return true;
			});
			viewframe.getGrid().getSelectionModel().selectRows([item_index]);
		}
	},
	doSign: function() {
		var wnd = this;
		var form = wnd.findById('MinzdravDLODocumentsEditForm').getForm();
		
		Ext.Ajax.request({
			url: '?c=WhsDocumentOrderReserve&m=sign',
			params: {
				WhsDocumentOrderReserve_id: form.findField('WhsDocumentOrderReserve_id').getValue(),
				WhsDocumentType_id: form.findField('WhsDocumentType_id').getValue()
			},
			success: function(response, action) {
				if (response && response.responseText)
				{
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success)
					{
						Ext.Msg.alert(lang['soobschenie'], lang['dokument_uspeshno_podpisan']);
						form.findField('WhsDocumentStatusType_id').setValue(2);
						wnd.buttons[2].hide();
					}
				}
				else
				{
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_podpisanii_otsutstvuet_otvet_servera']);
				}
			}
		});
	},
	doSave:  function(silent) {
		var wnd = this;
		var form = wnd.findById('MinzdravDLODocumentsEditForm').getForm();
		var viewframe =  wnd.findById('WhsDocumentOrderReserveDrugGrid');
		
		form.findField('ReserveDrugJSON').setValue(viewframe.getJSONChangedData());
		
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('MinzdravDLODocumentsEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}		
		viewframe.updateSumm();		
		
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = current_window.action;
		params.WhsDocumentUc_Sum = current_window.form.findField('WhsDocumentUc_Sum').getValue();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				current_window.callback();
				if ( silent == false ) {
					current_window.hide();
				} else {
					wnd.WhsDocumentUc_id = action.result.WhsDocumentUc_id;
					wnd.action = 'edit';
					form.findField('WhsDocumentUc_id').setValue(wnd.WhsDocumentUc_id);
					form.findField('WhsDocumentOrderReserve_id').setValue(wnd.WhsDocumentUc_id);	
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
		var form = wnd.findById('MinzdravDLODocumentsEditForm').getForm();
		var field_arr = [
			'WhsDocumentUc_Num',
			'WhsDocumentUc_Date',
			'WhsDocumentUc_Name',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id',
			'WhsDocumentOrderReserve_Percent'
		];
		
		for (var i in field_arr) if (form.findField(field_arr[i])) {
			if (disable)
				form.findField(field_arr[i]).disable();
			else
				form.findField(field_arr[i]).enable();
		}
		
		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
			wnd.buttons[2].disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
			wnd.buttons[2].enable();
		}
		
		wnd.findById('WhsDocumentOrderReserveDrugGrid').setReadOnly(disable);
	},
	show: function() {
        var that = this;		
		
		sw.Promed.swMinzdravDLODocumentsEditWindow.superclass.show.apply(this, arguments);
		
		var viewframe = that.findById('WhsDocumentOrderReserveDrugGrid');

		if ( getGlobalOptions().org_id > 0 ) {
			this.form.findField('Org_id').getStore().load({
				params: {
					Object:'Org',
					Org_id: getGlobalOptions().org_id,
					Org_Name:''
				},
				callback: function() {
					that.form.findField('Org_id').setValue(getGlobalOptions().org_id);
				}
			});
		}		
		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentUc_id = null;
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].WhsDocumentUc_id ) {
			this.WhsDocumentUc_id = arguments[0].WhsDocumentUc_id;
		}
		if ( arguments[0].WhsDocumentType_id ) {
			this.WhsDocumentType_id = arguments[0].WhsDocumentType_id;
		}
		
		this.form.reset();
		viewframe.removeAll({clearAll: true});
		
		if (this.WhsDocumentType_id == 12) {
			this.setTitle(lang['rasporyajenie_na_vklyuchenie_v_rezerv']);
		} else if (this.WhsDocumentType_id == 13) {
			this.setTitle(lang['rasporyajenie_na_isklyuchenie_iz_rezerva']);
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		switch (this.action) {
			case 'add':
				Ext.Ajax.request({
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							this.form.findField('WhsDocumentUc_Num').setValue(response_obj.WhsDocumentUc_Num);
							this.form.findField('WhsDocumentUc_Date').setValue(getGlobalOptions().date);
							this.form.findField('WhsDocumentUc_Name').setValue(response_obj.WhsDocumentUc_Num + '. ' + getGlobalOptions().date + ' - ' + that.title);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_dokumenta'], function() { 
								this.form.findField('WhsDocumentUc_Num').focus(true); 
							});
						}
					}.createDelegate(this),
					url: '/?c=WhsDocumentOrderReserve&m=getWhsDocumentOrderReserveNumber'
				});
				that.form.findField('WhsDocumentOrderReserve_Percent').fireEvent('change', that.form.findField('WhsDocumentOrderReserve_Percent'), that.form.findField('WhsDocumentOrderReserve_Percent').getValue());
				that.form.findField('WhsDocumentType_id').setValue(this.WhsDocumentType_id);
				that.findById('BtnGen').show();
				this.setDisabled(false);
			break;
			case 'edit':
			case 'view':
				that.findById('BtnGen').hide();
				Ext.Ajax.request({
					params:{
						WhsDocumentUc_id: that.WhsDocumentUc_id
					},
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (!result[0]) { return false}
							that.form.setValues(result[0]);
							viewframe.loadData({
								url: '/?c=WhsDocumentOrderReserveDrug&m=loadList',
								params:{WhsDocumentOrderReserve_id: that.form.findField('WhsDocumentOrderReserve_id').getValue()}, 
								globalFilters:{WhsDocumentOrderReserve_id: that.form.findField('WhsDocumentOrderReserve_id').getValue()}
							});
							if (that.form.findField('WhsDocumentStatusType_id').getValue() > 1) {
								that.buttons[2].hide();
							} else {
								that.buttons[2].show();
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih'], function() { 
								this.form.findField('WhsDocumentUc_Num').focus(true); 
							});
						}
					}.createDelegate(this),
					url: '/?c=WhsDocumentOrderReserve&m=load'
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
				id: 'MinzdravDLODocumentsEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6; padding: 5px;',
				border: false,
				labelWidth: 180,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url: '/?c=WhsDocumentOrderReserve&m=save',
				layout: 'form',
				items: [{
					id: 'MDDEW_WhsDocumentUc_id',
					name: 'WhsDocumentUc_id',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'MDDEW_WhsDocumentOrderReserve_id',
					name: 'WhsDocumentOrderReserve_id',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'MDDEW_WhsDocumentType_id',
					name: 'WhsDocumentType_id',
					value: 12,
					xtype: 'hidden'
				}, {
					name: 'ReserveDrugJSON',
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
							id: 'MDDEW_WhsDocumentUc_Num',
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
							id: 'MDDEW_WhsDocumentUc_Date',
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
							id: 'MDDEW_WhsDocumentStatusType_id',
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
					id: 'MDDEW_WhsDocumentUc_Name',						
					allowBlank:false
				}, {
					xtype: 'sworgcombo',
					fieldLabel : lang['organizatsiya'],
					tabIndex: current_window.firstTabIndex + 10,
					hiddenName: 'Org_id',
					id: 'MDDEW_Org_id',
					width: 650,
					disabled: true,
					editable: false,
					onTrigger1Click: function() {
						return false;
					}
				}, {
					fieldLabel: lang['istochnik_finansirovaniya'],
					hiddenName: 'DrugFinance_id',
					xtype: 'swcommonsprcombo',
					tabIndex: current_window.firstTabIndex + 10,
					comboSubject: 'DrugFinance',
					width: 250,
					allowBlank: false
				}, {
					fieldLabel: lang['statya_rashoda'],
					hiddenName: 'WhsDocumentCostItemType_id',
					xtype: 'swcommonsprcombo',
					tabIndex: current_window.firstTabIndex + 10,
					comboSubject: 'WhsDocumentCostItemType',
					width: 250,
					typeCode: 'int',
					allowBlank: false
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 260,
						items: [{
							fieldLabel: lang['velichina_rezerva'],
							name: 'WhsDocumentOrderReserve_Percent',
							xtype: 'numberfield',
							tabIndex: current_window.firstTabIndex + 10,
							maxValue: 100,
							minValue: 0.01,
							value: 20,
							width: 70,
							allowBlank: false
							/*listeners:
							{
								change:  function(field, newValue, oldValue)
								{
									var viewframe =  current_window.findById('WhsDocumentOrderReserveDrugGrid');
									if (newValue > 0 && current_window.action == 'add') {
										viewframe.loadData({
											url: '/?c=WhsDocumentOrderReserveDrug&m=loadRAWList',
											params: {
												WhsDocumentType_id: current_window.WhsDocumentType_id, 
												WhsDocumentOrderReserve_Percent: newValue
											}, 
											globalFilters: {
												WhsDocumentType_id: current_window.WhsDocumentType_id, 
												WhsDocumentOrderReserve_Percent: newValue
											},
											callback: function () {
												viewframe.updateSumm();
											}
										});
									}
								}.createDelegate(this)
							}*/
						}]
					}, {
						layout: 'form',
						border: false,
						width: 350,
						items: [{
							style: 'margin: 3px',
							html: lang['%_ot_dostupnogo_kolichestva']
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
						width: 260,
						items: [{
							fieldLabel: lang['summa'],
							name: 'WhsDocumentUc_Sum',
							xtype: 'numberfield',
							width: 70,
							disabled: true
						}]
					}, {
						layout: 'form',
						border: false,
						width: 350,
						items: [{
							style: 'margin: 3px',
							html: lang['rub']
						}]
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						style: "padding-left: 185px; padding-bottom: 10px",
						xtype: 'button',
						id: 'BtnGen',
						text: lang['sformirovat_spisok'],
						//iconCls: 'resetsearch16',
						handler: function() {
							var form = current_window.findById('MinzdravDLODocumentsEditForm').getForm();
							var viewframe = current_window.findById('WhsDocumentOrderReserveDrugGrid');
							
							if ( !form.isValid() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function() {
										current_window.findById('MinzdravDLODocumentsEditForm').getFirstInvalidEl().focus(true);
									},
									icon: Ext.Msg.WARNING,
									msg: ERR_INVFIELDS_MSG,
									title: ERR_INVFIELDS_TIT
								});
								return false;
							}
							
							viewframe.loadData({
								url: '/?c=WhsDocumentOrderReserveDrug&m=loadRAWList',
								params: {
									WhsDocumentType_id: current_window.WhsDocumentType_id, 
									WhsDocumentOrderReserve_Percent: form.findField('WhsDocumentOrderReserve_Percent').getValue(),
									DrugFinance_id: form.findField('DrugFinance_id').getValue(),
									WhsDocumentCostItemType_id: form.findField('WhsDocumentCostItemType_id').getValue()
								}, 
								globalFilters: {
									WhsDocumentType_id: current_window.WhsDocumentType_id, 
									WhsDocumentOrderReserve_Percent: form.findField('WhsDocumentOrderReserve_Percent').getValue(),
									DrugFinance_id: form.findField('DrugFinance_id').getValue(),
									WhsDocumentCostItemType_id: form.findField('WhsDocumentCostItemType_id').getValue()
								},
								callback: function () {
									viewframe.updateSumm();
								}
							});
						}
					}]
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					frame: true,
					collapsible: true,
					//id: 'MDDEW__InfPanel2',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: lang['medikamentyi'],
					items: [{
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
								id: 'MDDEW_DrugComplexMnn_id',
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
								xtype: 'swdrugcombo',
								fieldLabel : lang['torg'],
								width: 300,
								tabIndex: current_window.firstTabIndex + 10,
								name: 'Drug_id',
								id: 'MDDEW_Drug_id',
								value: '',
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								style: "padding-left: 0px",
								xtype: 'button',
								id: 'MDDEW_BtnSearch',
								text: lang['poisk'],
								iconCls: 'search16',
								handler: function() {
									var win = Ext.getCmp('MinzdravDLODocumentsEditWindow');
									var form = win.findById('MinzdravDLODocumentsEditForm').getForm();
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
								id: 'MDDEW_BtnClear',
								text: lang['sbros'],
								iconCls: 'resetsearch16',
								handler: function() {
									var form = Ext.getCmp('MinzdravDLODocumentsEditForm').getForm();
									form.findField('Drug_id').reset();
									form.findField('DrugComplexMnn_id').reset();
								}
							}]
						}]
					},
					new sw.Promed.ViewFrame({
						actions: [
							{name: 'action_add', handler: function() { this.findById('WhsDocumentOrderReserveDrugGrid').editGrid('add') }.createDelegate(this)},
							{name: 'action_edit', handler: function() { this.findById('WhsDocumentOrderReserveDrugGrid').editGrid('edit') }.createDelegate(this)},
							{name: 'action_view', handler: function() { this.findById('WhsDocumentOrderReserveDrugGrid').editGrid('view') }.createDelegate(this)},
							{name: 'action_delete', handler: function() { this.findById('WhsDocumentOrderReserveDrugGrid').deleteRecord() }.createDelegate(this)},
							{name: 'action_refresh', handler: function() {
								this.doSave(
									function() {
										var that = this;
										this.findById('WhsDocumentOrderReserveDrugGrid').loadData({
											url: '/?c=WhsDocumentOrderReserveDrug&m=loadList',
											params:{WhsDocumentOrderReserve_id: that.form.findField('WhsDocumentOrderReserve_id').getValue()}, 
											globalFilters:{WhsDocumentOrderReserve_id: that.form.findField('WhsDocumentOrderReserve_id').getValue()}
										})
									}.createDelegate(this)
								);
							}.createDelegate(this)},
							{name: 'action_print'}
						],
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 150,
						autoLoadData: false,
						border: true,
						dataUrl: '/?c=WhsDocumentOrderReserveDrug&m=loadList',
						height: 180,
						region: 'north',
						object: 'WhsDocumentOrderReserveDrug',
						editformclassname: 'swMinzdravDLODocumentsDrugEditWindow',
						id: 'WhsDocumentOrderReserveDrugGrid',
						paging: false,
						saveAtOnce:false,
						style: 'margin-bottom: 0px',
						stringfields: [
							{name: 'WhsDocumentOrderReserveDrug_id', type: 'int', header: 'ID', key: true},
							{name: 'rownumberer', type: 'rownumberer', header: lang['№_p_p'], width: 50},
							{name: 'WhsDocumentUc_pid', type: 'int', hidden: true},
							{name: 'WhsDocumentUc_Name', type: 'string', header: lang['№_gk'], width: 200},
							{name: 'Drug_id', type: 'int', hidden: true},
							{name: 'Okei_id', type: 'int', hidden: true},
							{name: 'Drug_Name', type: 'string', header: lang['torgovoe_naimenovanie'], width: 400},
							{name: 'WhsDocumentOrderReserveDrug_Kolvo', type: 'string', header: lang['kol-vo_up'], width: 100},
							{name: 'WhsDocumentOrderReserveDrug_PriceNDS', type: 'string', header: lang['tsena'], width: 100},
							{name: 'WhsDocumentUc_Sum', type: 'string', header: lang['summa'], width: 100},
							{name: 'state', hidden: true}
						],
						title: false,
						toolbar: true,
						onRowSelect: function(sm,rowIdx,record) {				
							if (record.get('WhsDocumentOrderReserveDrug_id') > 0 && !this.readOnly) {
								this.ViewActions.action_edit.setDisabled(false);
								this.ViewActions.action_delete.setDisabled(false);
							} else {
								this.ViewActions.action_edit.setDisabled(true);
								this.ViewActions.action_delete.setDisabled(true);
							}
							if (record.get('WhsDocumentOrderReserveDrug_id') > 0) {
								this.ViewActions.action_view.setDisabled(false);
							} else {
								this.ViewActions.action_view.setDisabled(true);
							}
						},
						updateSumm: function() {
							var summ = 0;
							var summ_field = current_window.findById('MinzdravDLODocumentsEditForm').getForm().findField('WhsDocumentUc_Sum');
							this.getGrid().getStore().each(function(record) {	
								log(record.data);
								if(record.data.WhsDocumentUc_Sum && record.data.WhsDocumentUc_Sum > 0)
									summ += (record.data.WhsDocumentUc_Sum * 1);
							});
							summ_field.setValue(summ);
						},
						editGrid: function (action) {
						
							var win = Ext.getCmp('MinzdravDLODocumentsEditWindow');
							var form = win.findById('MinzdravDLODocumentsEditForm').getForm();
						
							if (action == null)	action = 'add';

							var view_frame = this;
							var store = view_frame.getGrid().getStore();
							
							if (action == 'add') {		
								var record_count = store.getCount();
								if ( record_count == 1 && !store.getAt(0).get('WhsDocumentOrderReserveDrug_id') ) {
									view_frame.removeAll({ addEmptyRecord: false });
									record_count = 0;
								}
								
								getWnd(view_frame.editformclassname).show({
									action: action,
									params: { 
										WhsDocumentOrderReserve_Percent: form.findField('WhsDocumentOrderReserve_Percent').getValue(),
										WhsDocumentType_id: form.findField('WhsDocumentType_id').getValue()
									},
									callback: function(data) {
										if ( record_count == 1 && !store.getAt(0).get('WhsDocumentOrderReserveDrug_id') ) {
											view_frame.removeAll({ addEmptyRecord: false });
										}										
										var record = new Ext.data.Record.create(view_frame.jsonData['store']);										
										data.WhsDocumentOrderReserveDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
										data.grid_id = data.WhsDocumentOrderReserveDrug_id;
										data.state = 'add';
										store.insert(record_count, new record(data));									
										view_frame.updateSumm();
									}
								});
							}
							
							if (action == 'edit' || action == 'view') {
								var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
								if (selected_record.get('WhsDocumentOrderReserveDrug_id') > 0) {						
									var params = selected_record.data;
									params.WhsDocumentOrderReserve_Percent = form.findField('WhsDocumentOrderReserve_Percent').getValue();
									params.WhsDocumentType_id = form.findField('WhsDocumentType_id').getValue();
									getWnd(view_frame.editformclassname).show({
										action: action,
										params: params,
										callback: function(data) {											
											for(var key in data)
												selected_record.set(key, data[key]);
											if (selected_record.get('state') != 'add')
												selected_record.set('state', 'edit');
											view_frame.updateSumm();
										}
									});									
								}
							}
						},
						deleteRecord: function(){
							var view_frame = this;
							var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
							if (selected_record.get('state') == 'add') {
								view_frame.getGrid().getStore().remove(selected_record);
							} else {								
								selected_record.set('state', 'delete');
								selected_record.commit();
								view_frame.getGrid().getStore().filterBy(function(record){
									if(record.data.state == 'delete') return false;
									return true;
								});
							}
							view_frame.updateSumm();
						},
						getChangedData: function(){ //возвращает новые и измненные показатели
							var data = new Array();
							this.getGrid().getStore().clearFilter();
							this.getGrid().getStore().each(function(record) {
								if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete'))
									data.push(record.data);
							});
							this.getGrid().getStore().filterBy(function(record){
								if(record.data.state == 'delete') return false;
								return true;
							});
							return data;
						},						
						getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
							var dataObj = this.getChangedData();
							return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
						},
						onDblClick: function() {
							this.onEnter();
						},
						onEnter: function() {
							if (!this.ViewActions.action_edit.isDisabled()) {
								this.ViewActions.action_edit.execute();
							} else {
								this.ViewActions.action_view.execute();
							}
						}
					})
					]
				})]
			}],
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
			])
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{			
				hidden: true,
				handler: function() {},
				iconCls: null,
				text: lang['kontrol_postavki']
			}, {			
				handler: function() {
					this.ownerCt.doSave(false);
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {								
				handler: function() {
					this.ownerCt.doSave(this.ownerCt.doSign());
				},
				iconCls: null,
				text: lang['podpisat']
			}, {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swMinzdravDLODocumentsEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('MinzdravDLODocumentsEditForm').getForm();
	}	
});