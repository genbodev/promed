/**
 * Панель с возможностью добавления комбобоксов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */
Ext6.define('common.MorbusPalliat.PalliatFamilyCarePanel', {
	extend: 'Ext6.Panel',
	layout: 'form',
	border: false,
	labelAlign: 'left',
	labelWidth: 200,
	fieldWidth: 600,
	lastItemsIndex: 0,
	win: null,
	deletedItems: [],
	setDisabled: function(d) {
		Ext6.getCmp(this.id + 'addButton').setVisible(!d);
	},
	setAllowBlank: function(ab) {
		Ext6.each(Ext6.getCmp('PalliatFamilyCareFieldSet').items.items, function(el) {
			el.items.items[0].setAllowBlank(ab),
			el.items.items[1].setAllowBlank(ab),
			el.items.items[2].setAllowBlank(ab)
		});
	},
	getValues: function() {
		var data = [];
		var me = this;
		Ext6.each(Ext6.getCmp('PalliatFamilyCareFieldSet').items.items, function(el) {
			var a = {
				PalliatFamilyCare_id: el.oId,
				PalliatFamilyCare_Age: el.items.items[0].getValue(),
				FamilyRelationType_id:  el.items.items[1].getValue(),
				PalliatFamilyCare_Phone:  el.items.items[2].getValue(),
				RecordStatus_Code:  el.items.items[3].getValue(),
			};
			if (a.PalliatFamilyCare_Age && a.FamilyRelationType_id && a.PalliatFamilyCare_Phone) {
				data.push(a);
			} else if (el.oId) {
				me.deletedItems.push(el.oId);
			}
		});
		Ext6.each(this.deletedItems, function(el) {
			data.push({
				PalliatFamilyCare_id: el,
				RecordStatus_Code: 3
			});
		});
		return data;
	},
	reset: function() {
		this.lastItemsIndex = 0;
		this.deletedItems = [];
		Ext6.getCmp('PalliatFamilyCareFieldSet').removeAll();
	},
	deleteCombo: function(index) {
		var el = Ext6.getCmp(this.id + 'PalliatFamilyCareEl' + index);
		if (el.items.items[3].getValue() != 0 && !!el.oId) {
			this.deletedItems.push(el.oId);
		}
		Ext6.getCmp('PalliatFamilyCareFieldSet').remove(el, true);
		if (!Ext6.getCmp('PalliatFamilyCareFieldSet').items.items.length) this.addCombo();
	},
	addCombo: function(data) {
		var me = this;
		if (!data) data = {};
		this.lastItemsIndex++;
		var element = {
			id: this.id + 'PalliatFamilyCareEl' + this.lastItemsIndex,
			oId: data.PalliatFamilyCare_id || null,
			layout: 'column',
			style: 'margin-top: 5px;',
			border: false,
			defaults:{
				border: false,
				labelAlign: 'right'
			},
			items: [{
				disabled: this.win.action == 'view',
				labelWidth: 60,
				xtype: 'numberfield',
				hideTrigger: true,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: 'Возраст',
				allowBlank: false,
				value: data.PalliatFamilyCare_Age || '',
				width: 120,
				indx: this.lastItemsIndex,
				id:	this.id + 'PalliatFamilyCare_Age' + this.lastItemsIndex,
				listeners: {
					change: function(field, newValue, oldValue) {
						var rs_field = Ext6.getCmp(me.id + 'RecordStatus_Code' + field.indx);
						if (rs_field.getValue() == 1 && field.originalValue != newValue) {
							rs_field.setValue(2);
						}
					}
				}
			}, {
				disabled: this.win.action == 'view',
				labelWidth: 125,
				fieldLabel: 'Степень родства',
				typeCode: 'int',
				value: data.FamilyRelationType_id || null,
				width: 300,
				allowBlank: false,
				indx: this.lastItemsIndex,
				id:	this.id + 'FamilyRelationType_id' + this.lastItemsIndex,
				comboSubject: 'FamilyRelationType',
				xtype: 'commonSprCombo',
				onLoad: function(store, records, success) {
					if (success && !!this.value) this.setValue(this.value);
				},
				listeners: {
					change: function(field, newValue, oldValue) {
						var rs_field = Ext6.getCmp(me.id + 'RecordStatus_Code' + field.indx);
						if (rs_field.getValue() == 1 && field.originalValue != newValue) {
							rs_field.setValue(2);
						}
					}
				}
			}, {
				disabled: this.win.action == 'view',
				labelWidth: 75,
				xtype: 'textfield',
				width: 220,
				fieldLabel: 'Телефон',
				allowBlank: false,
				value: data.PalliatFamilyCare_Phone || '',
				indx: this.lastItemsIndex,
				id:	this.id + 'PalliatFamilyCare_Phone' + this.lastItemsIndex,
				listeners: {
					change: function(field, newValue, oldValue) {
						var rs_field = Ext6.getCmp(me.id + 'RecordStatus_Code' + field.indx);
						if (rs_field.getValue() == 1 && field.originalValue != newValue) {
							rs_field.setValue(2);
						}
					}
				}
			}, {
				name: 'RecordStatus_Code',
				xtype: 'hidden',
				value: data.RecordStatus_Code || 0,
				id:	this.id + 'RecordStatus_Code' + this.lastItemsIndex,
			}, {
				hidden: this.win.action == 'view',
				height: 20,
				width: 20,
				margin: '5 0 0 15',
				html: '<div class="x6-tool-delete" onclick="Ext6.getCmp(\''+this.id+'\').deleteCombo(\''+this.lastItemsIndex+'\');"></div>'
			}]
		};
		Ext6.getCmp('PalliatFamilyCareFieldSet').add(element);
		Ext6.getCmp(this.id + 'FamilyRelationType_id' + this.lastItemsIndex).getStore().load();
	},
	initComponent: function() {
		var me = this;
		
		this.PalliatFamilyCarePanel = new Ext6.Panel({
			region: 'center',
			border: false,
			frame: false,
			width: me.width,
			bodyStyle: 'background: #fff;',
			items: [{
				title: 'Сведения о родственниках, осуществляющих уход за пациентом',
				xtype: 'fieldset',
				padding: '5 10',
				autoHeight: true,
				items: [{
					border: false,
					id: 'PalliatFamilyCareFieldSet',
					items: []
				}, {
					text: 'Добавить',
					xtype: 'button',
					ui: 'plain',
					cls: 'simple-button-link',
					margin: 5,
					id: this.id + 'addButton',
					handler: function() {
						me.addCombo();
					}
				}]
			}]
		});
		
		if(typeof this.win != 'object')
			this.win = false;
		
		Ext6.apply(this, {
			items: [this.PalliatFamilyCarePanel]
		});
		
		this.callParent(arguments);
	}
});