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
Ext6.define('common.MorbusPalliat.MedProductCardPanel', {
	extend: 'Ext6.Panel',
	layout: 'form',
	border: false,
	labelAlign: 'left',
	labelWidth: 200,
	fieldWidth: 600,
	lastItemsIndex: 0,
	win: null,
	reloadCombo: function(d) {
		var me = this;
		Ext6.each(Ext6.getCmp('MedProductCardFieldSet').items.items, function(el) {
			var combo = el.items.items[0];
			combo.getStore().load({params: me.baseParams});
		});
	},
	setDisabled: function(d) {
		Ext6.getCmp(this.id + 'addButton').setVisible(!d);
	},
	setAllowBlank: function(ab) {
		Ext6.each(Ext6.getCmp('MedProductCardFieldSet').items.items, function(el) {
			el.items.items[0].setAllowBlank(ab)
		});
	},
	getValues: function() {
		var data = [];
		Ext6.each(Ext6.getCmp('MedProductCardFieldSet').items.items, function(el) {
			if (el.items.items[0].getValue()) {
				var a = {
					MedProductCardLink_id: el.oId,
					MedProductCard_id: el.items.items[0].getValue()
				};
				data.push(a);
			}
		});
		return data;
	},
	reset: function() {
		this.lastItemsIndex = 0;
		Ext6.getCmp('MedProductCardFieldSet').removeAll();
	},
	deleteCombo: function(index) {
		Ext6.getCmp('MedProductCardFieldSet').remove(Ext6.getCmp(this.id + 'MedProductCardEl' + index),true);
		if (!Ext6.getCmp('MedProductCardFieldSet').items.items.length) this.addCombo();
	},
	getCount: function() {
		return Ext6.getCmp('MedProductCardFieldSet').items.items.length;
	},
	addCombo: function(data) {
		if (!data) data = {};
		this.lastItemsIndex++;
		var is_first = !(this.getCount()) ? true : false;
		var element = {
			id: this.id + 'MedProductCardEl' + this.lastItemsIndex,
			oId: data.MedProductCardLink_id || null,
			layout: 'column',
			style: 'margin-top: 5px;',
			border: false,
			defaults:{
				border: false,
				labelAlign: 'right'
			},
			items: [{
				disabled: this.win.action == 'view',
				xtype: 'baseCombobox',
				value: data.MedProductCard_id || null,
				displayField: 'MedProductClass_Name',
				valueField: 'MedProductCard_id',
				name: 'MedProductCard_id',
				id:	this.id + 'MedProductCard_id' + this.lastItemsIndex,
				fieldLabel: is_first ? 'Оборудование' : ' ',
				labelSeparator: is_first ? ':' : '',
				labelWidth: 280,
				queryMode: 'local',
				store: {
					fields: [
						{name: 'MedProductCard_id', type:'int'},
						{name: 'MedProductClass_id', type:'int'},
						{name: 'MedProductClass_Name', type:'string'}
					],
					proxy: {
						type: 'ajax',
						actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
						url: '/?c=MorbusPalliat&m=loadMedProductCardList',
						reader: {type: 'json'}
					},
					sorters: {
						property: 'MedProductClass_Name',
						direction: 'ASC'
					}
				},
				width: 620,
				onLoad: function(store, records, success) {
					if (success && !!this.value) this.setValue(this.value);
				}
			}, {
				hidden: this.win.action == 'view',
				height: 20,
				width: 20,
				margin: '5 0 0 15',
				html: '<div class="x6-tool-delete" onclick="Ext6.getCmp(\''+this.id+'\').deleteCombo(\''+this.lastItemsIndex+'\');"></div>'
			}]
		};
		Ext6.getCmp('MedProductCardFieldSet').add(element);
		Ext6.getCmp(this.id + 'MedProductCard_id' + this.lastItemsIndex).getStore().load({params: this.baseParams});
	},
	initComponent: function() {
		var me = this;
		
		this.MedProductCardPanel = new Ext6.Panel({
			region: 'center',
			border: false,
			frame: false,
			width: 700,
			bodyStyle: 'background: #fff;',
			items: [{
				border: false,
				id: 'MedProductCardFieldSet',
				items: []
			}, {
				text: 'Добавить',
				xtype: 'button',
				ui: 'plain',
				cls: 'simple-button-link',
				margin: '0 0 5 290',
				id: this.id + 'addButton',
				handler: function() {
					me.addCombo();
				}
			}]
		});
		
		if(typeof this.win != 'object')
			this.win = false;
		if(typeof this.baseParams != 'object')
			this.baseParams = {};
		
		Ext6.apply(this, {
			items: [this.MedProductCardPanel]
		});
		
		this.callParent(arguments);
	}
});