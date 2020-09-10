/**
 * swWhsDocumentUcInventDrugEditWindow - окно редактирования строки инвентаризационной ведомости
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.04.2017
 */
/*NO PARSE JSON*/

sw.Promed.swWhsDocumentUcInventDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swWhsDocumentUcInventDrugEditWindow',
	layout: 'form',
	modal: true,
	maximizable: false,
	resizable: false,
	autoHeight: true,
	width: 580,

	loadDrugList: function() {
		var base_form = this.FormPanel.getForm();

		var combo = base_form.findField('WhsDocumentSupplyStr_id');

		combo.reset();
		combo.validate();
		combo.getStore().removeAll();

		var WhsDocumentSupply_id = base_form.findField('WhsDocumentSupply_id').getValue();
		if (Ext.isEmpty(WhsDocumentSupply_id)) {
			combo.fireEvent('change', combo, combo.getValue());
		} else {
			combo.getStore().load({
				params: {WhsDocumentSupply_id: WhsDocumentSupply_id},
				callback: function() {
					combo.fireEvent('change', combo, combo.getValue());
				}
			});
		}
	},

	calcSum: function() {
		var base_form = this.FormPanel.getForm();

		var count = Number(base_form.findField('WhsDocumentUcInventDrug_FactKolvo').getValue());
		var price = Number(base_form.findField('WhsDocumentSupplyStr_id').getFieldValue('WhsDocumentSupplyStr_PriceNDS'));

		base_form.findField('WhsDocumentUcInventDrug_Sum').setValue(count * price);
	},

    setGoodsUnit: function() {
		var base_form = this.FormPanel.getForm();

		var gu_id = base_form.findField('WhsDocumentSupplyStr_id').getFieldValue('GoodsUnit_id');
		var gu_name = base_form.findField('WhsDocumentSupplyStr_id').getFieldValue('GoodsUnit_Name');

		base_form.findField('GoodsUnit_id').setValue(gu_id);
		base_form.findField('GoodsUnit_Name').setValue(gu_name);
	},

	doSave: function() {
		var base_form = this.FormPanel.getForm();
		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {};

		params.Drug_id = base_form.findField('WhsDocumentSupplyStr_id').getFieldValue('Drug_id');
		params.WhsDocumentUcInventDrug_Cost = base_form.findField('WhsDocumentSupplyStr_id').getFieldValue('WhsDocumentSupplyStr_PriceNDS');

		if (base_form.findField('StorageZone_id').disabled) {
			params.StorageZone_id = base_form.findField('StorageZone_id').getValue();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result.success){
					base_form.findField('WhsDocumentUcInventDrug_id').setValue(action.result.WhsDocumentUcInventDrug_id);
					this.callback();
					this.hide();
				}
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
			}.createDelegate(this)
		});

		return true;
	},

	show: function() {
		sw.Promed.swWhsDocumentUcInventDrugEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		this.action = 'view';
		this.callback = Ext.emptyFn;
		this.DrugFinance_id = null;
		this.WhsDocumentCostItemType_id = null;
		this.StorageZoneEditable = true;

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}
		if (arguments[0].DrugFinance_id) {
			this.DrugFinance_id = arguments[0].DrugFinance_id;
		}
		if (arguments[0].WhsDocumentCostItemType_id) {
			this.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		}
		if (arguments[0].StorageZoneEditable == false) {
			this.StorageZoneEditable = false;
		}

		var WhsDocumentSupplyCombo = base_form.findField('WhsDocumentSupply_id');
		var WhsDocumentSupplyStrCombo = base_form.findField('WhsDocumentSupplyStr_id');
		var OrgCombo = base_form.findField('Org_id');
		var StorageCombo = base_form.findField('Storage_id');
		var StorageZoneCombo = base_form.findField('StorageZone_id');
		var PersonWorkCombo = base_form.findField('PersonWork_id');

		StorageZoneCombo.setDisabled(!this.StorageZoneEditable);

		switch(this.action) {
			case 'add':
				this.enableEdit(true);
				this.setTitle('Строка инвентаризационной ведомости: Добавление');

				StorageZoneCombo.getStore().baseParams = {
					Storage_id: StorageCombo.getValue()
				};
				if (!Ext.isEmpty(StorageZoneCombo.getValue())) {
					StorageZoneCombo.getStore().load({
						params: {StorageZone_id: StorageZoneCombo.getValue()},
						callback: function() {
							StorageZoneCombo.setValue(StorageZoneCombo.getValue());
						}
					});
				}

				WhsDocumentSupplyCombo.getStore().baseParams = {
					WhsDocumentType_ids: Ext.util.JSON.encode([3,6]),
					Org_cid: OrgCombo.getValue(),
					DrugFinance_id: this.DrugFinance_id,
					WhsDocumentCostItemType_id: this.WhsDocumentCostItemType_id
				};

				PersonWorkCombo.getStore().baseParams = {
					Org_id: base_form.findField('Org_id').getValue()
				};

				OrgCombo.getStore().load({
					params: {Org_id: OrgCombo.getValue()},
					callback: function() {
						OrgCombo.setValue(OrgCombo.getValue())
					}
				});

				StorageCombo.getStore().load({
					params: {Storage_id: StorageCombo.getValue()},
					callback: function() {
						StorageCombo.setValue(StorageCombo.getValue());
					}
				});
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.enableEdit(true);
					this.setTitle('Строка инвентаризационной ведомости: Редактирование');
				} else {
					this.enableEdit(false);
					this.setTitle('Строка инвентаризационной ведомости: Просмотр');
				}

				var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
				loadMask.show();

				base_form.load({
					params: {WhsDocumentUcInventDrug_id: base_form.findField('WhsDocumentUcInventDrug_id').getValue()},
					url: '/?c=WhsDocumentUcInventDrug&m=load',
					success: function() {
						loadMask.hide();

						StorageZoneCombo.getStore().baseParams = {
							Storage_id: StorageCombo.getValue()
						};
						StorageZoneCombo.getStore().load({
							params: {StorageZone_id: StorageZoneCombo.getValue()},
							callback: function() {
								StorageZoneCombo.setValue(StorageZoneCombo.getValue());
							}
						});

						WhsDocumentSupplyCombo.getStore().baseParams = {
							WhsDocumentType_ids: Ext.util.JSON.encode([3,6]),
							Org_cid: OrgCombo.getValue(),
							DrugFinance_id: this.DrugFinance_id,
							WhsDocumentCostItemType_id: this.WhsDocumentCostItemType_id
						};
						WhsDocumentSupplyCombo.getStore().load({
							params: {WhsDocumentSupply_id: WhsDocumentSupplyCombo.getValue()},
							callback: function() {
								WhsDocumentSupplyCombo.setValue(WhsDocumentSupplyCombo.getValue());
							}
						});

						if (!Ext.isEmpty(WhsDocumentSupplyCombo.getValue())) {
							WhsDocumentSupplyStrCombo.getStore().load({
								params: {WhsDocumentSupply_id: WhsDocumentSupplyCombo.getValue()},
								callback: function() {
									WhsDocumentSupplyStrCombo.setValue(WhsDocumentSupplyStrCombo.getValue());
									this.calcSum();
								}.createDelegate(this)
							});
						}

						PersonWorkCombo.getStore().baseParams = {
							Org_id: base_form.findField('Org_id').getValue()
						};
						PersonWorkCombo.getStore().load({
							params: {PersonWork_id: PersonWorkCombo.getValue()},
							callback: function() {
								PersonWorkCombo.setValue(PersonWorkCombo.getValue());
							}
						});

						OrgCombo.getStore().load({
							params: {Org_id: OrgCombo.getValue()},
							callback: function() {
								OrgCombo.setValue(OrgCombo.getValue())
							}
						});

						StorageCombo.getStore().load({
							params: {Storage_id: StorageCombo.getValue()},
							callback: function() {
								StorageCombo.setValue(StorageCombo.getValue());
							}
						});
					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}.createDelegate(this)
				});
			break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			bodyStyle: 'margin: 5px 0',
			labelAlign: 'right',
			defaults: {
				width: 400
			},
			items: [
				{
					xtype: 'hidden',
					name: 'WhsDocumentUcInventDrug_id'
				}, {
					xtype: 'hidden',
					name: 'WhsDocumentUcInvent_id'
				}, {
					allowBlank: false,
					disabled: true,
					xtype: 'sworgcomboex',
					hiddenName: 'Org_id',
					fieldLabel: 'Организация'
				}, {
					allowBlank: false,
					disabled: true,
					xtype: 'swstoragecombo',
					hiddenName: 'Storage_id',
					fieldLabel: 'Склад'
				}, {
					xtype: 'swstoragezonecombo',
					hiddenName: 'StorageZone_id',
					fieldLabel: 'Место хранения',
					listWidth: 400
				}, {
					//allowBlank: false,
					xtype: 'swwhsdocumentsupplycombo',
					hiddenName: 'WhsDocumentSupply_id',
					fieldLabel: 'Контракт',
					listWidth: 400,
					listeners: {
						select: function (comp, record, index) {
							var str = '';
							var printRow = function(values) {
								var items = [];
								if (values.DrugRequest_Name) {
									items.push(values.DrugRequest_Name);
								}
								if (values.WhsDocumentProcurementRequest_Name) {
									items.push(values.WhsDocumentProcurementRequest_Name);
								}
								if (items.length == 0 && values.WhsDocumentSupply_ProtNum) {
									items.push(values.WhsDocumentSupply_ProtNum);
								}
								return items.length > 0 ? items.join(', ') : '';
							}
							if(record.data.WhsDocumentSupply_id){
								var strD = (record.data.WhsDocumentSupply_Date instanceof Date) ? record.data.WhsDocumentSupply_Date.format('d.m.Y') : record.data.WhsDocumentSupply_Date;
								str = printRow(record.data);
								str = record.data.WhsDocumentSupply_Num+'   '+strD+' '+str;
							}
							comp.setRawValue(str);
						},
						'change': function(combo, newValue, oldValue){
							this.loadDrugList();
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					xtype: 'swwhsdocumentsupplystrcombo',
					hiddenName: 'WhsDocumentSupplyStr_id',
					fieldLabel: 'Медикамент',
					listWidth: 640,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							this.calcSum();
							this.setGoodsUnit();
						}.createDelegate(this)
					}
				}, {
                    xtype: 'hidden',
                    name: 'GoodsUnit_id'
				}, {
                    xtype: 'textfield',
					disabled: true,
                    fieldLabel: 'Ед.учета',
                    name: 'GoodsUnit_Name'
				}, {
					allowBlank: false,
					allowNegative: false,
					xtype: 'numberfield',
					name: 'WhsDocumentUcInventDrug_FactKolvo',
					fieldLabel: 'Кол-во',
					listeners: {
						'change': function(field, newValue, oldValue) {
							this.calcSum();
						}.createDelegate(this)
					}
				}, {
					disabled: true,
					xtype: 'numberfield',
					name: 'WhsDocumentUcInventDrug_Sum',
					fieldLabel: 'Сумма'
				}, {
					layout: 'column',
					width: 505,
					items: [{
						layout: 'form',
						width: 255,
						items: [{
							allowBlank: false,
							xtype: 'textfield',
							name: 'PrepSeries_Ser',
							fieldLabel: 'Серия',
							anchor: '100%'
						}]
					}, {
						layout: 'form',
						width: 250,
						items: [{
							allowBlank: false,
							xtype: 'swdatefield',
							name: 'PrepSeries_GodnDate',
							fieldLabel: 'Срок годности',
							anchor: '100%'
						}]
					}]
				}, {
					allowBlank: false,
					xtype: 'swpersonworkcombo',
					hiddenName: 'PersonWork_id',
					fieldLabel: 'Исполнитель'
				}
			],
			reader: new Ext.data.JsonReader({
				success: function() {}
			},
			[
				{name: 'WhsDocumentUcInventDrug_id'},
				{name: 'WhsDocumentUcInvent_id'},
				{name: 'Drug_id'},
				{name: 'WhsDocumentUcInventDrug_FactKolvo'},
				{name: 'StorageZone_id'},
				{name: 'Org_id'},
				{name: 'Storage_id'},
				{name: 'WhsDocumentSupply_id'},
				{name: 'WhsDocumentSupplyStr_id'},
				{name: 'PrepSeries_Ser'},
				{name: 'PrepSeries_GodnDate'},
				{name: 'PersonWork_id'},
				{name: 'GoodsUnit_id'},
				{name: 'GoodsUnit_Name'}
			]),
			url: '/?c=WhsDocumentUcInventDrug&m=save'
		});

		Ext.apply(this,{
			buttons: [
				{
					iconCls: 'save16',
					text: BTN_FRMSAVE,
					handler: function() {
						this.doSave();
					}.createDelegate(this)
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

		sw.Promed.swWhsDocumentUcInventDrugEditWindow.superclass.initComponent.apply(this, arguments);
	}
});