/**
 * @class sw.Promed.TimeField
 * @extends Ext.form.TriggerField
 * Provides a timw input field with a {@link Ext.DatePicker} dropdown and automatic date validation.
 * @constructor
 * Create a new TimeField
 * @param {Object} config
 */
sw.Promed.TimeField = Ext.extend(Ext.form.TriggerField,  {
	maxValue: null, // дата начала, зависящая от данного поля (т.е. должна быть меньше чем текущая дата)
	dField:null,
	beforeBlur: function(){
		var v = this.parseTime(this.getRawValue());

		if ( v ) {
			this.setValue(v);
		}
	},
	defaultAutoCreate: {tag: "input", type: "text", size: "5", autocomplete: "off"},
	enableKeyEvents: true,
	getTime: function() {
		var dt = new Date();
		return dt.format('H:i');
	},
	getValue: function(){
		return sw.Promed.TimeField.superclass.getValue.call(this);
	},
	/*setMinValue : function(dt){
        this.minValue = (typeof dt == "string" ? this.parseDate(dt) : dt);
        
    },
*/
    /**
     * Replaces any existing {@link #maxValue} with the new value and refreshes the DatePicker.
     * @param {Date} value The maximum date that can be selected
     */
    setMaxValue : function(dt,df){
		
        this.maxValue = (typeof dt == "string" ? this.parseDate(dt) : dt);
		this.dField=Ext.util.Format.date(df,'d.m.Y');
    },
	initComponent: function() {
		sw.Promed.TimeField.superclass.initComponent.call(this);
		var timefieldcomp = this;
		this.addListener( 'change',
			function(field, newValue, oldValue) {
				// Устанавливаем минимальную дату окончания или максимальную дату начала. Надо как то по другому реализовать.
				
				if (timefieldcomp.maxValue != null&&timefieldcomp.dField!=null) {
					timefieldcomp.validateValue(newValue);
				}
				if(typeof timefieldcomp.onChange == 'function') {
					timefieldcomp.onChange(field, newValue, oldValue);
				}
			}
		);
	},
	invalidText: "Неверное значение времени: {0}",
	invalidMaxText: "Время не может быть больше: {0}",
	onDestroy: function(){
		sw.Promed.TimeField.superclass.onDestroy.call(this);
	},
	//перезагружаемая функция
	onChange: function(field, newValue, oldValue) {
	},
	onTriggerClick: function() {
		if ( this.disabled ) {
			return;
		}

		this.setValue(this.getTime());
		this.fireEvent('change');
	},
	parseTime: function(value) {
        if (typeof(value)=='undefined') {
            return value;
        }
        if ( !value || value.toString().length != 5 ) {
			return value;
		}

		var v = value.replace(this.stripCharsRe, '');

		if ( v.length == 0 ) {
			return value;
		}

		return v.replace(new RegExp('_', 'g'), '0');
	},
	setValue: function(time){
		sw.Promed.TimeField.superclass.setValue.call(this, time);
	},
	stripCharsRe: new RegExp('__:__'),
	triggerClass: 'x-form-clock-trigger',
	validateValue: function(value) {

		if ( !sw.Promed.TimeField.superclass.validateValue.call(this, value) ) {
			return false;
		}

		if ( value.toString().length == 0 ) {
			 return true;
		}

		var v = this.parseTime(value);
		
		if ( Number(v.substr(0, 2)) >= 24 || Number(v.substr(0, 2) < 0) || Number(v.substr(3, 2)) >= 60 || Number(v.substr(3, 2)) < 0 ) {
			this.markInvalid(String.format(this.invalidText, value));
			return false;
		}
		if(this.maxValue!=null&&this.dField!=null){
			if(Ext.util.Format.date(this.maxValue,'d.m.Y')==this.dField){
				var mv = this.parseTime(Ext.util.Format.date(this.maxValue,'H:i'));
				if ( Number(v.substr(0, 2)) > Number(mv.substr(0, 2))|| (Number(v.substr(0, 2)) == Number(mv.substr(0, 2))&& Number(v.substr(3, 2)) > Number(mv.substr(3, 2)) )) {
					this.markInvalid(String.format(this.invalidMaxText, v));
					return false;
				}
			}
		}
		return true;
	},
	validationEvent: 'blur'
});
Ext.reg('swtimefield', sw.Promed.TimeField);

sw.Promed.SwTemplTimeField = Ext.extend(sw.Promed.TimeField, {
	listeners: {
		'keydown': function (inp, e) {
			if ( e.getKey() == Ext.EventObject.F4 ) {
				e.stopEvent();
				inp.onTriggerClick();
			}
		}
	},
	onTriggerClick: function() {
		setCurrentDateTime({
			loadMask: true,
			setDate: true,
			setDateMaxValue: true,
			setTimeMaxValue:true,
			setDateMinValue: false,
			setTime: true,
			timeField: this
		});
	}.createDelegate(this),	
	plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
	width: 60,
	initComponent: function() {
		sw.Promed.SwTemplTimeField.superclass.initComponent.apply(this, arguments);
		
	}
});

Ext.reg('swtempltimefield', sw.Promed.SwTemplTimeField);