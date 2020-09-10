/**
 * swInvoicePositionEditWindow - окно редактирования позиции в накладной
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			06.10.2014
 */
/*NO PARSE JSON*/

sw.Promed.swInvoicePositionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swInvoicePositionEditWindow',
	width: 620,
	autoHeight: true,
	modal: true,

	getOkeiConv: function(params) {
		this.doings.start('getOkeiConv');
		var loadMask = new Ext.LoadMask(this.FormPanel.getEl(), { msg: "Получение коэффициента пересчета" });
		loadMask.show();

		Ext.Ajax.request({
			params: {Okei_fid: params.Okei_fid, Okei_sid: params.Okei_sid},
			url: '/?c=UnitSpr&m=getOkeiConv',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.success && response_obj.UnitConv) {
					params.callback(response_obj.UnitConv);
				}
				loadMask.hide();
				this.doings.finish('getOkeiConv');
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
				this.doings.finish('getOkeiConv');
			}.createDelegate(this)
		});
	},

	doSave: function() {
		var wnd = this;
		if (this.doings.hasDoings()) {
			this.doings.doLater('doSave', function(){wnd.doSave()});
			return false;
		}
		this.doings.start('doSave');

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			this.doings.finish('doSave');
			return false;
		}

		var data = [];
		var fieldValues = getAllFormFieldValues(this.FormPanel);
		fieldValues.Okei_NationSymbol = base_form.findField('Okei_id').getFieldValue('Okei_NationSymbol');
		fieldValues.InventoryItem_Name = base_form.findField('InventoryItem_id').getFieldValue('InventoryItem_Name');
		fieldValues.Invoice_Date = Ext.util.Format.date(fieldValues.Invoice_Date, 'Y-m-d');
		fieldValues.InvoiceType_id = this.InvoiceType_id;

		if (this.InvoiceType_id == 1) {
			data.push(fieldValues);
		} else if (this.InvoiceType_id == 2) {
			if (!this.ShipmentData || this.ShipmentData.length == 0) {
				Ext.Msg.alert(lang['oshibka'], lang['ne_zaplonena_informatsiya_o_partii']);
				this.doings.finish('doSave');
				return false;
			}

			for(var i=0; i<this.ShipmentData.length; i++) {
				var recordData = Ext.apply({}, fieldValues);
				recordData.InvoicePosition_id = (i>0)?null:recordData.InvoicePosition_id;
				recordData.InvoicePosition_PositionNum += i;
				recordData.Shipment_id = this.ShipmentData[i].Shipment_id;
				recordData.InvoicePosition_Price = this.ShipmentData[i].Shipment_Price;
				recordData.InvoicePosition_Count = this.ShipmentData[i].Shipment_ReservedCount;
				recordData.InvoicePosition_Sum = recordData.InvoicePosition_Price*recordData.InvoicePosition_Count;
				data.push(recordData);
			}
		}

		var params = {InvoicePositionData: Ext.util.JSON.encode(data)};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		Ext.Ajax.request({
			params: params,
			url: '/?c=Invoice&m=saveInvoicePositionData',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.Error_Msg) {
					//Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
				} else {
					this.callback();
					this.hide();
				}
				this.doings.finish('doSave');
			}.createDelegate(this),
			failure: function(response) {
				this.doings.finish('doSave');
			}.createDelegate(this)
		});
	},

	calculateInvoicePositionSum: function() {
		var base_form = this.FormPanel.getForm();
		var count = base_form.findField('InvoicePosition_Count').getValue();
		var price = base_form.findField('InvoicePosition_Price').getValue();

		if (Ext.isEmpty(count) || Ext.isEmpty(price)) {
			base_form.findField('InvoicePosition_Sum').setValue(null);
		} else {
			base_form.findField('InvoicePosition_Sum').setValue(count*price);
		}
	},

	loadShipmentData: function(callback) {
		this.doings.start('loadShipmentData');
		var base_form = this.FormPanel.getForm();

		if (this.InvoiceType_id == 2) {
			if (Ext.isEmpty(base_form.findField('InventoryItem_id').getValue()) || Ext.isEmpty(base_form.findField('InvoicePosition_Count').getValue())) {
				this.doings.finish('loadShipmentData');
				return;
			}
			var loadMask = new Ext.LoadMask(this.FormPanel.getEl(), { msg: "Получение данных о партиях" });
			loadMask.show();

			var params = {
				InventoryItem_id: base_form.findField('InventoryItem_id').getValue(),
				InvoicePosition_id: base_form.findField('InvoicePosition_id').getValue(),
				InvoicePosition_Count: base_form.findField('InvoicePosition_Count').getValue(),
				InvoicePosition_Coeff: base_form.findField('InvoicePosition_Coeff').getValue(),
				Storage_id: base_form.findField('Storage_id').getValue(),
				Invoice_Date: Ext.util.Format.date(base_form.findField('Invoice_Date').getValue(), 'd.m.Y')
			};

			Ext.Ajax.request({
				params: params,
				url: '/?c=Invoice&m=getShipmentData',
				success: function(response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.Error_Msg || (response_obj[0] && response_obj[0].Error_Msg)) {
						this.ShipmentData = null;
						if (response_obj[0] && response_obj[0].Error_Msg) {
							Ext.Msg.alert(lang['oshibka'], response_obj[0].Error_Msg);
						}
						base_form.findField('InvoicePosition_Price').setValue(null);
						base_form.findField('InvoicePosition_Sum').setValue(null);
					} else {
						this.ShipmentData = response_obj;
						base_form.findField('InvoicePosition_Price').setValue(response_obj[0].Shipment_Price);
						if (callback) {callback()}
						this.calculateInvoicePositionSum();
					}
					loadMask.hide();
					this.doings.finish('loadShipmentData');
				}.createDelegate(this),
				failure: function(response) {
					loadMask.hide();
					this.doings.finish('loadShipmentData');
				}.createDelegate(this)
			});
		} else {
			this.doings.finish('loadShipmentData');
		}
	},

	show: function() {
		sw.Promed.swInvoicePositionEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;
		this.InvoiceType_id = 1;
		this.ShipmentData = null;
		this.doings = new sw.Promed.Doings();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].InvoiceType_id) {
			this.InvoiceType_id = arguments[0].InvoiceType_id;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		base_form.items.each(function(f){f.validate()});

		if (Ext.isEmpty(this.InvoiceType_id)) {
			this.action = 'view';
		}

		var invoice_position_price_field = base_form.findField('InvoicePosition_Price');
		var inventory_item_combo = base_form.findField('InventoryItem_id');
		var okei_combo = base_form.findField('Okei_id');
		var coeff_field = base_form.findField('InvoicePosition_Coeff');

		coeff_field.setAllowBlank(true);
		coeff_field.disable();

		if (this.InvoiceType_id == 2) {
			inventory_item_combo.getStore().baseParams = {
				Storage_id: base_form.findField('Storage_id').getValue(),
				InvoicePosition_id: base_form.findField('InvoicePosition_id').getValue(),
				Date: Ext.util.Format.date(base_form.findField('Invoice_Date').getValue(), 'd.m.Y')
			};
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle(lang['pozitsiya_nakladnoy_dobavlenie']);
				this.enableEdit(true);

				if (this.InvoiceType_id == 1) {
					invoice_position_price_field.enable();
				} else if (this.InvoiceType_id == 2) {
					invoice_position_price_field.disable();
				}

				if (inventory_item_combo.getFieldValue('Okei_id') == okei_combo.getValue()) {
					coeff_field.setAllowBlank(true);
					coeff_field.reset();
					coeff_field.disable();
				} else {
					coeff_field.setAllowBlank(false);
					coeff_field.enable();
				}
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['pozitsiya_nakladnoy_redaktirovanie']);
					this.enableEdit(true);

					if (this.InvoiceType_id == 1) {
						invoice_position_price_field.enable();
					} else if (this.InvoiceType_id == 2) {
						invoice_position_price_field.disable();
					}
				} else {
					this.setTitle(lang['pozitsiya_nakladnoy_prosmotr']);
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						InvoicePosition_id: base_form.findField('InvoicePosition_id').getValue()
					},
					url: '/?c=Invoice&m=loadInvoicePositionForm',
					success: function() {
						loadMask.hide();
						inventory_item_combo.getStore().load({
							callback: function() {
								inventory_item_combo.setValue(inventory_item_combo.getValue());

								if (this.action == 'edit') {
									if (inventory_item_combo.getFieldValue('Okei_id') == okei_combo.getValue()) {
										coeff_field.setAllowBlank(true);
										coeff_field.reset();
										coeff_field.disable();
									} else {
										coeff_field.setAllowBlank(false);
										coeff_field.enable();
									}
								}
							}.createDelegate(this)
						});

						this.ShipmentData = [{
							Shipment_id: base_form.findField('Shipment_id').getValue(),
							Shipment_Price: base_form.findField('InvoicePosition_Price').getValue(),
							Shipment_ReservedCount: base_form.findField('InvoicePosition_Count').getValue()
						}];

						this.calculateInvoicePositionSum();
					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'IEPW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			//url: '/?c=Invoice&m=saveInvoicePosition',
			items: [{
				xtype: 'hidden',
				name: 'InvoicePosition_id'
			}, {
				xtype: 'hidden',
				name: 'Invoice_id'
			}, {
				xtype: 'hidden',
				name: 'Invoice_Date'
			}, {
				xtype: 'hidden',
				name: 'Storage_id'
			}, {
				xtype: 'hidden',
				name: 'Shipment_id'
			}, /*{
				xtype: 'hidden',
				name: 'InvoicePosition_PrevCount'
			},*/ {
				allowBlank: false,
				xtype: 'numberfield',
				disabled: true,
				name: 'InvoicePosition_PositionNum',
				fieldLabel: lang['nomer_pozitsii'],
				width: 180
			}, {
				allowBlank: false,
				xtype: 'swinventoryitemcombo',
				hiddenName: 'InventoryItem_id',
				fieldLabel: lang['tmts'],
				listeners: {
					'select': function(combo,record,index) {
						var base_form = this.FormPanel.getForm();
						var okei_id = record.get('Okei_id');
						var okei_combo = base_form.findField('Okei_id');

						okei_combo.setValue(okei_id);
						okei_combo.fireEvent('select', okei_combo,
							okei_combo.getStore().getById(okei_id),
							okei_combo.getStore().indexOfId(okei_id)
						);
					}.createDelegate(this),
					'change': function(combo, newValue, oldValue) {
						if (this.InvoiceType_id == 2) {
							this.loadShipmentData();
						}
					}.createDelegate(this)
				},
				width: 180
			}, {
				allowBlank: false,
				xtype: 'numberfield',
				allowNegative: false,
				name: 'InvoicePosition_Count',
				fieldLabel: lang['kolichestvo'],
				listeners: {
					'change': function() {
						if (this.InvoiceType_id == 1) {
							this.calculateInvoicePositionSum();
						} else if (this.InvoiceType_id == 2) {
							this.loadShipmentData();
						}
					}.createDelegate(this)
				},
				width: 180
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						allowBlank: false,
						editable: true,
						xtype: 'swokeicombo',
						hiddenName: 'Okei_id',
						fieldLabel: lang['edinitsyi_izmereniya'],
						listeners: {
							'select': function(combo,record,index) {
								var base_form = this.FormPanel.getForm();
								var inventory_item_combo = base_form.findField('InventoryItem_id');
								var coeff_field = base_form.findField('InvoicePosition_Coeff');

								if (inventory_item_combo.getFieldValue('Okei_id') == record.get('Okei_id')) {
									coeff_field.setAllowBlank(true);
									coeff_field.reset();
									coeff_field.disable();
									if (this.InvoiceType_id == 2) {
										this.loadShipmentData();
									}
								} else {
									coeff_field.setAllowBlank(false);
									coeff_field.enable();

									this.getOkeiConv({
										Okei_fid: record.get('Okei_id'),
										Okei_sid: inventory_item_combo.getFieldValue('Okei_id'),
										callback: function(coeff) {
											coeff_field.setValue(coeff);
											if (this.InvoiceType_id == 2) {
												this.loadShipmentData();
											}
										}.createDelegate(this)
									});
								}
							}.createDelegate(this)
						},
						width: 180
					}, {
						xtype: 'numberfield',
						allowBlank: false,
						allowNegative: false,
						name: 'InvoicePosition_Price',
						fieldLabel: lang['tsena'],
						listeners: {
							'change': function() {
								this.calculateInvoicePositionSum();
							}.createDelegate(this)
						},
						width: 180
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						allowNegative: false,
						name: 'InvoicePosition_Coeff',
						listeners: {
							'change': function() {
								if (this.InvoiceType_id == 1) {
									this.calculateInvoicePositionSum();
								} else if (this.InvoiceType_id == 2) {
									this.loadShipmentData();
								}
							}.createDelegate(this)
						},
						fieldLabel: lang['koef_perescheta'],
						width: 160
					}, {
						disabled: true,
						xtype: 'numberfield',
						allowNegative: false,
						name: 'InvoicePosition_Sum',
						fieldLabel: lang['summa'],
						width: 160
					}]
				}]
			}, {
				xtype: 'textfield',
				name: 'InvoicePosition_Comment',
				fieldLabel: lang['primechanie'],
				width: 465
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'InvoicePosition_id'},
				{name: 'Invoice_id'},
				{name: 'Shipment_id'},
				{name: 'InvoicePosition_PositionNum'},
				{name: 'InventoryItem_id'},
				{name: 'InvoicePosition_Count'},
				{name: 'Okei_id'},
				{name: 'InvoicePosition_Price'},
				{name: 'InvoicePosition_Coeff'},
				{name: 'InvoicePosition_Sum'},
				{name: 'InvoicePosition_Comment'}
			])
		});

		Ext.apply(this,
		{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'IEPW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swInvoicePositionEditWindow.superclass.initComponent.apply(this, arguments);
	}
});