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
Ext6.define('common.MorbusOnko.AddOnkoComplPanel', {
	extend: 'Ext6.Panel',
	layout: 'form',
	border: false,
	labelAlign: 'left',
	labelWidth: 200,
	fieldWidth: 600,
	getValues: function() {
		var res = [];
		this.AddOnkoComplPanel.items.each(function(item,index,length) {
			if (item.id != this.id + 'addButton' && !!item.getValue()) {
				res.push(item.getValue());
			}
		},this);
		return res;
	},
	getSpecValues: function() {
		var pan = this;
		var res = [];
		this.AddOnkoComplPanel.items.each(function(item,index,length) {
			if (item.id != this.id + 'addButton') {
				var ar = {};
				ar[pan.objectName+'_id'] = item.getValue();
				res.push(ar);
			}
		},this);
		return res;
	},
	getFirstCombo: function() {
		return this.firstCombo;
	},
	reset: function(callback,values_arr) {
		this.AddOnkoComplPanel.items.each(function(item,index,length) {
			if(item.id != this.id + 'addButton'){
				this.AddOnkoComplPanel.remove(item,true);
			}
		},this);
		return this.addCombo(true,callback,values_arr);
	},
	setValues: function(values_arr) {
		this.inSetValues = true;
		if(!values_arr || !Ext6.isArray(values_arr))
			values_arr = [null];
		var callback = function(combo){
			if(values_arr[this.lastValueIndex] && values_arr[this.lastValueIndex][this.objectName+'_id']) {
				combo.setValue(values_arr[this.lastValueIndex][this.objectName+'_id']);
				if(this.lastValueIndex == 0){
					this.disableAddButton(false);
				}
				this.lastValueIndex++;
			}
		}.createDelegate(this);
		this.lastValueIndex = 0;
		this.loadParams = {};
		this.firstCombo = this.reset(callback,values_arr);
		for(var i=0;i<values_arr.length;i++) {
			if(i>0) {
				this.addCombo(false,callback,values_arr);
			}
		}
		this.inSetValues = false;
	},
	doAdd: function(comp) {
		this.initItems();
		var a = arguments, len = a.length;
		if(len > 1){
			for(var i = 0; i < len; i++) {
				this.add(a[i]);
			}
			return;
		}
		var c = this.lookupComponent(this.applyDefaults(comp));
		var pos = this.items.length;
		return c;
	},
	onChange: Ext6.emptyFn,
	disableAddButton: function(disable){
		var item = this.AddOnkoComplPanel.getComponent(this.id + 'addButton');
		item.setDisabled(disable);
	},
	addCombo: function(is_first,callback,values_arr,adds) {
		var panel = this;
		if(is_first)
			this.lastItemsIndex = 0;
		else
			this.lastItemsIndex++;
		if(getRegionNick() == 'perm'){
			if(this.lastItemsIndex > this.limitCountCombo) {
				this.disableAddButton(true);
				return false;
			} else if(this.lastItemsIndex == 0){
				this.disableAddButton(true);
			} else {
				this.disableAddButton(false);
			}
		}
		var conf_combo = {
			value: null,
			labelWidth: panel.labelWidth,
			listeners: {
				'change': function(c,n) {
					var panel = this;
					if(getRegionNick() == 'perm' && panel.lastItemsIndex == 0 && !Ext6.isEmpty(n) && n != 0){
						panel.disableAddButton(false);
					}
					if(getRegionNick() == 'perm' && !panel.inSetValues){
						panel.items.each(function(item){
							if(item.hiddenName){
								panel.loadSpr(item,panel.objectName+'_id');
							}
						});
					}
				}.createDelegate(this)
			},
			labelSeparator: '',
			hiddenName: panel.objectName+'_id'+this.lastItemsIndex,
			sortField: panel.objectName+'_Code',
			comboSubject: panel.objectName,
			typeCode: 'int',
			autoLoad: false,
			width: panel.fieldWidth
		};
		if(this.firstTabIndex) {
			conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
		}
		if(this.lastItemsIndex == 0){
			conf_combo.fieldLabel = (getRegionNick() == 'perm' ? panel.fieldLabelTitle+': 1.' : panel.fieldLabelTitle);
		} else {
			var index = this.lastItemsIndex;
			conf_combo.fieldLabel = '<span style="color: #fff">' + panel.fieldLabelTitle + ': </span>' + (index+1)+'.';
		}
		var c = Ext6.create('swCommonSprCombo', conf_combo);
		c.findRecord = function (prop, value){
			var record;
			if(this.store && this.store.getCount() > 0){
				this.store.each(function(r){
					if(r.data[prop] == value){
						record = r;
						return false;
					}
				});
			}
			return record;
		};
		var cb = this.AddOnkoComplPanel.add(c);		
		if(adds){
			var values = panel.getSpecValues();
			panel.setValues(values);
		} else {
			this.loadSpr(cb,panel.objectName+'_id', this.loadParams,callback,values_arr);
		}
		return cb;
	},
	loadSpr: function(combo, field_value, params, callback, values_arr)
	{
		var panel = this;
		var vals = [];
		
		var value = combo.getValue();
		if(combo.store){
			combo.getStore().removeAll();
			combo.getStore().load({
				callback: function() {
					if (combo && typeof combo.getStore == 'function' && combo.getStore() && combo.store && combo.store.data && combo.store.data.length > 0) {
						combo.getStore().each(function (record) {
							if (record.data[field_value] == value) {
							} else if (record.data[field_value].inlist(vals)) {
								combo.getStore().remove(record);
							}
						});
						if (callback) {
							callback(combo);
						}
					}
				},
				params: params 
			});
		}	
	},
	initComponent: function() {
		var me = this;
		var conf_add_btn = new Ext6.Button({
			handler: function() {
				if(me.afterRemove){
					this.addCombo(false,false,false,true);
					me.afterRemove = false;
				} else {
					this.addCombo();
				}
			}.createDelegate(this),
			iconCls: 'icon-add',
			id: me.id + 'addButton',
			tooltip: 'Добавить осложнение',
			style: 'float:right; margin-top: 4px;'
		});
		
		this.AddOnkoComplPanel = new Ext6.Panel({
			region: 'center',
			border: false,
			frame: false,
			bodyStyle: 'background: #fff;',
			items: [ conf_add_btn ]
		});
		
		if(typeof this.win != 'object')
			this.win = false;
		if(typeof this.loadParams != 'object')
			this.loadParams = {};
		if(typeof this.baseParams != 'object')
			this.baseParams = {level:0};
		
		Ext6.apply(this, {
			items: [this.AddOnkoComplPanel]
		});
		
		this.callParent(arguments);
	}
});