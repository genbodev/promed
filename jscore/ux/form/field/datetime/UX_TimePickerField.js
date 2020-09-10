/**
 * Поле ввода времени H:i|H:i:s
 * @author wangzilong
 * update Ext - 4.1 2012/04/27
 * update Ext - 4.2 2013/03/18 change alias name, change default value
 * update Ext - 4.2.1 2014/08/25 fix the width in different Theme
 */
Ext4.define('Ext4.ux.form.TimePickerField', {
	  extend: 'Ext4.form.field.Base',
	  alias: 'widget.uxtimepicker',
	  alternateClassName: 'Ext4.form.field.TimePickerField',
	  requires: ['Ext4.form.field.Number'],

	  // 隐藏BaseField的输入框 , hidden basefield's input
	  inputType: 'hidden',

	  style: 'padding:4px 0 0 0;margin-bottom:0px',

	  msgTarget: 'none',

	  /**
	   * @cfg {String} value
	   * initValue, format: 'H:i'|'H:i:s'
	   */
	  value: null,

	  /**
	  * @cfg {Object} spinnerCfg
	  * 数字输入框参数, number input config
	  */
	
	/**
	 * @cfg {String} timeFormat Формат времени
	 */
	timeFormat: 'H:i:s',
	
	hasSeconds: function(){
		return /s/.test(this.timeFormat);
	},

	/**
	 * Override
	 */
	initComponent: function(){
		var me = this;

		me.spinnerCfg = me.spinnerCfg || {
			columnWidth: .333
		};

		me.value = me.value || (this.hasSeconds() ? '00:00:00' : '00:00');

		me.callParent();// called setValue

		me.spinners = [];
		var cfg = Ext4.apply({}, me.spinnerCfg, {
			readOnly: me.readOnly,
			disabled: me.disabled,
			style: 'float: left',
			listeners: {
				change: {
					fn: me.onSpinnerChange,
					scope: me
				}
			}
		});

		me.hoursSpinner = Ext4.create('Ext4.form.field.Number', Ext4.apply({}, cfg, {
			minValue: 0,
			maxValue: 23
		}));
		me.minutesSpinner = Ext4.create('Ext4.form.field.Number', Ext4.apply({}, cfg, {
			minValue: 0,
			maxValue: 59
		}));
		if (/s/.test(this.timeFormat)) {
			me.secondsSpinner = Ext4.create('Ext4.form.field.Number', Ext4.apply({}, cfg, {
				minValue: 0,
				maxValue: 59
			}));
		}

		me.spinners.push(me.hoursSpinner, me.minutesSpinner, me.secondsSpinner);

		me.on('render', me.addSpinners, me);
	},
	
	addSpinners: function(){
		var me = this, spinnerWrapDom, spinnerWrap;
		// me.callParent(arguments);

		// render to original BaseField input td
		// spinnerWrap = Ext4.get(Ext4.DomQuery.selectNode('div', this.el.dom)); // 4.0.2
		spinnerWrapDom = Ext4.dom.Query.select('td', this.getEl().dom)[1]; // 4.0 ->4.1 div->td
		spinnerWrap = Ext4.get(spinnerWrapDom);
		// me.callSpinnersFunction('render', spinnerWrap); // use wrap
		Ext4.create('Ext4.container.Container', {
			layout: 'column',
			border: false,
			frame: false,
			items: me.spinners,
			renderTo: spinnerWrap
		});

		Ext4.core.DomHelper.append(spinnerWrap, {
			tag: 'div',
			cls: 'x-form-clear-left'
		});

		this.setRawValue(this.value);
	},

	_valueSplit: function(v){
		if (Ext4.isDate(v)) {
			v = Ext4.Date.format(v, this.timeFormat);
		}
		var split = v.split(':');
		if (this.hasSeconds()) {
			return {
				h: split.length > 0 ? split[0] : 0,
				m: split.length > 1 ? split[1] : 0,
				s: split.length > 2 ? split[2] : 0
			};
		} else {
			return {
				h: split.length > 0 ? split[0] : 0,
				m: split.length > 1 ? split[1] : 0
			};
		}
	},
	  
	onSpinnerChange: function(){
		if (!this.rendered) {
			return;
		}
		this.fireEvent('change', this, this.getValue(), this.getRawValue());
	},
	  
	// 依次调用各输入框函数, call each spinner's function
	callSpinnersFunction: function(funName, args){
		for (var i = 0; i < this.spinners.length; i++) {
			this.spinners[i][funName](args);
		}
	},
	  
	/**
	 * @private get time as object
	 */
	getRawValue: function(){
		if (!this.rendered) {
			var date = this.value || new Date();
			return this._valueSplit(date);
		} else {
			if (this.hasSeconds()) {
				return {
					h: this.hoursSpinner.getValue(),
					m: this.minutesSpinner.getValue(),
					s: this.secondsSpinner.getValue()
				};
			} else {
				return {
					h: this.hoursSpinner.getValue(),
					m: this.minutesSpinner.getValue()
				};
			}
		}
	},

	/**
	 * @private
	 */
	setRawValue: function(value){
		value = this._valueSplit(value);
		if (this.hoursSpinner) {
			this.hoursSpinner.setValue(value.h);
			this.minutesSpinner.setValue(value.m);
			if (this.secondsSpinner) {
				this.secondsSpinner.setValue(value.s);
			}
		}
	},
	
	// overwrite
	getValue: function(){
		var v = this.getRawValue();
		if (this.hasSeconds()) {
			return Ext4.String.leftPad(v.h, 2, '0') + ':' + Ext4.String.leftPad(v.m, 2, '0') + ':'
					+ Ext4.String.leftPad(v.s, 2, '0');
		} else {
			return Ext4.String.leftPad(v.h, 2, '0') + ':' + Ext4.String.leftPad(v.m, 2, '0');
		}
	},
	
	// overwrite
	setValue: function(value){
		this.value = Ext4.isDate(value) ? Ext4.Date.format(value, this.timeFormat) : value;
		if (!this.rendered) {
			return;
		}
		this.setRawValue(this.value);
		this.validate();
	},
	  
	// overwrite
	disable: function(){
		this.callParent(arguments);
		this.callSpinnersFunction('disable', arguments);
	},
	
	// overwrite
	enable: function(){
		this.callParent(arguments);
		this.callSpinnersFunction('enable', arguments);
	},
	
	// overwrite
	setReadOnly: function(){
		this.callParent(arguments);
		this.callSpinnersFunction('setReadOnly', arguments);
	},
	
	// overwrite
	clearInvalid: function(){
		this.callParent(arguments);
		this.callSpinnersFunction('clearInvalid', arguments);
	},
	  
	// overwrite
	isValid: function(preventMark){
		var is_valid = this.hoursSpinner.isValid(preventMark) && this.minutesSpinner.isValid(preventMark);
		if (this.secondsSpinner) {
			is_valid = this.secondsSpinner.isValid(preventMark) && is_valid;
		}
		return is_valid;
	},
	
	// overwrite
	validate: function(){
		var validate = this.hoursSpinner.validate() && this.minutesSpinner.validate();
		if (this.secondsSpinner) {
			validate = this.secondsSpinner.validate() && validate;
		}
		return validate;
	}
});