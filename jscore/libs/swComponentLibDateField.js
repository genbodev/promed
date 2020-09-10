/**
* swComponentLibDateField - классы выбора даты.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      06.04.2009
*/

sw.Promed.SwDateField = Ext.extend(Ext.form.DateField, {
	format: 'd.m.Y',
	enableKeyEvents: true,
	minValue: '01.01.1900',
	stripCharsRe: new RegExp('__\.__\.____'),
	validationEvent: 'blur',
	begDateField: null, // дата начала, зависящая от данного поля (т.е. должна быть меньше чем текущая дата)
	endDateField: null, // дата окончания, зависящая от данного поля (т.е. должна быть больше чем текущая дата)
	
	beforeBlur : function(){
		var v = this.parseDate(this.getRawValue());
		if(v){
			this.setValue(v);
		}
		else { // режем все точки и знаки подчеркивания в строке и пробуем снова
			var s = this.getRawValue();
			s = s.replace(/\./g,'');
			s = s.replace(/\_/g,'');
			v = this.parseDate(s);
			if(v){
				this.setValue(v);
			}
			if ((this.isFromReports)&&(v=='')) //В репозитории отчетов и статистической отчетности в связи с одной давней задачей было установлено srtipCharsRe: false;
											// из-за этого возникла ошибка, описанная в задаче 19672. Так как таким способом поле определяется только из отчетов,
											//поставил условие на isFromReports и пустую строку, чтобы вместо '__.__.____' после "ухода" с поля устанавливалась пустая строка.
			{
				this.setValue(v);
			}
		}
	},
	
	// plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ], - ломает загрузку форм (например Структуру ЛПУ).
	
	//перезагружаемая функция
	onChange: function(field, newValue, oldValue) {
	},

	initComponent: function() {
		var datefieldcomp = this;
		
		this.plugins = [ new Ext.ux.InputTextMask('99.99.9999', false) ];
		
		this.addListener( 'keydown',
			function( inp, e ) {
				if ( e.F4 == e.getKey() )
				{
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;
					e.browserEvent.returnValue = false;
					e.returnValue = false;
					if ( Ext.isIE )
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					inp.onTriggerClick();
					inp.menu.picker.focus();
					return false;
				}
				if ( e.ENTER == e.getKey() )
				{
					this.beforeBlur();
				}
			}
		);
		this.addListener( 'keyup',
			function( inp, e ) {
				if ( e.F4 == e.getKey() )
				{
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;
					e.browserEvent.returnValue = false;
					e.returnValue = false;
					if ( Ext.isIE )
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					return false;
				}
			}
		);
		this.addListener( 'select',
			function( inp, date ) {
				inp.focus(false);
			}
		);
		this.addListener( 'change',
			function(field, newValue, oldValue) {
				// Устанавливаем минимальную дату окончания или максимальную дату начала. Надо как то по другому реализовать.
				if (datefieldcomp.begDateField != null || datefieldcomp.endDateField != null) {
					// 1. ищем форму на которой лежит компонент
					var panel = datefieldcomp;
					while (panel.ownerCt && !panel.getForm) {
						panel = panel.ownerCt;
					}
				
					// 2. если нашли то ищем компонент
					if (datefieldcomp.begDateField != null && panel.getForm) {
						panel.getForm().findField(datefieldcomp.begDateField).setMaxValue(newValue);
					}
					
					if (datefieldcomp.endDateField != null && panel.getForm) {
						panel.getForm().findField(datefieldcomp.endDateField).setMinValue(newValue);
					}
				}
				if(typeof datefieldcomp.onChange == 'function') {
					datefieldcomp.onChange(field, newValue, oldValue);
				}
			}
		);
		sw.Promed.SwDateField.superclass.initComponent.apply(this, arguments);
	}
});

Ext.reg('swdatefield', sw.Promed.SwDateField);


sw.Promed.SwDateTimeField = Ext.extend(Ext.Panel,{
	dateLabel: langs('Дата'),
    dateFieldWidth: 100,
	dateLabelStyle: null,
    timeLabel: langs('Время'),
    timeLabelWidth: 50,
	log_enabled: false,
	dateLabelWidth: undefined,
	dateLabelWidth1: null,
	timeLabelWidth1: null,
	allowBlank: true,
	setDateMinValueWhenGetFromSrv: false, // При получении даты и времени с сервера устаналивать минимальное значение для даты?
	setDateMaxValueWhenGetFromSrv: true, // При получении даты и времени с сервера устаналивать максимальное значение для даты?
	onChange: function(field, newValue) {},
	minValue: null, // Минимальное значение даты: в указанном формате или Date
	maxValue: null, // Максимальное значение даты: в указанном формате или Date
	
	log: function (){
		if (this.log_enabled) {
			log(this.hiddenName, arguments);
		}
	},
	setDisabled: function(disable) {
		var that = this;
		that.dateEditField.setDisabled(disable);
		that.timeEditField.setDisabled(disable);
	},
    initComponent: function() {
        var that = this;
        this.updateHiddenValue = function (){
			var adate = that.dateEditField.getValue();
			var atime = that.timeEditField.getValue();
			if(!that.readOnly)
			{
				that.log('->updateHiddenValue');
				atime = that.timeEditField.parseTime(atime);
				var dateIsSet = ((typeof(adate)=='object')&&(adate instanceof Date));
				var timeIsSet = (typeof(atime)!='undefined') && that.timeEditField.validate() && (atime != '');
				var val = '';
				if (dateIsSet && timeIsSet) {
					val = adate.format('d.m.Y') + ' ' + atime;
				} else {
					if (dateIsSet) {
						val = adate.format('d.m.Y H:i');
					}
				}
				if (val != '') {
					val = Date.parseDate(val, 'd.m.Y H:i');
				}
				if (typeof val == 'undefined') {
					val = adate.format('d.m.Y') + ' 00:00';
					val = Date.parseDate(val, 'd.m.Y H:i');
				}
				that.hiddenField.setValue(val);
			}
			else
			{
				that.dateEditField.reset();
				that.timeEditField.reset();
				Ext.Msg.alert('Ошибка', 'Это поле заполняется автоматически');
			}
        };
		
		// Инициализация поля даты
		var dateEditFieldParams = {
            fieldLabel: that.dateLabel,
            width: that.dateFieldWidth,
            allowBlank: that.allowBlank,
            listeners: {
                'change': function (){
                    that.log('->dateEditField.change listener');
					that.updateHiddenValue();
                }
            }
        };
		if ( that.minValue !== null ) {
			dateEditFieldParams.minValue = that.minValue;
		}
		if ( that.maxValue !== null ) {
			dateEditFieldParams.maxValue = that.maxValue;
		}
		if ( this.dateLabelStyle !== null ) {
			dateEditFieldParams.labelStyle = this.dateLabelStyle;
		}
        this.dateEditField = new sw.Promed.SwDateField( dateEditFieldParams );
		
		// Инициализация поля время
        this.timeEditField = new sw.Promed.TimeField({
            fieldLabel: that.timeLabel,
			allowBlank: that.allowBlank,
            plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
            onTriggerClick: function() {
				if(this.disabled) return;
				that.log('->timeEditField.onTriggerClick');
				setCurrentDateTime({
                    dateField: that.dateEditField,
                    loadMask: true,
                    setDate: true,
                    setDateMaxValue: that.setDateMaxValueWhenGetFromSrv,
                    setDateMinValue: that.setDateMinValueWhenGetFromSrv,
                    setTime: true,
                    timeField: that.timeEditField,
                    windowId: that.ownerCt.id,
                    callback: function (){
						that.log('->timeEditField.onTriggerClick.Callback');
                        that.updateHiddenValue();
                    }
                });
            },
            validateOnBlur: false,
            listeners: {
                'change': function (){
					that.log('->timeEditField.change listener');
                    that.updateHiddenValue();
                }
            }
        });
        this.hiddenField = new Ext.form.Hidden({
            tag:'input',
            type:'hidden',
            name: this.hiddenName,
            id: (this.hiddenId||this.hiddenName),
            setValue: function (){
				that.log('->hiddenField.setValue');
				Ext.form.Hidden.superclass.setValue.apply(this, arguments);
                this.fireEvent('change');
            },
			getStringValue: function () {
				var datetime = this.getValue();
				if (typeof(datetime) != 'object' && !Ext.isEmpty(datetime)) {
					var value = datetime;
					datetime = Date.parseDate(value, 'd.m.Y H:i');
					if (typeof(datetime) != 'object') {
                        datetime = Date.parseDate(value, 'd.m.Y');
						if (typeof(datetime)!='object') {
							that.log('parsing as date');
							var dt = new Date(value);
							if(dt != 'Invalid Date')
								datetime = dt;
							if (typeof(datetime)!='object') {
								var v = Date.parse(value);
								if (!isNaN(v)) {
									that.log('parsed');
									datetime = new Date();
									datetime.setTime(Date.parse(value));
								} 
							}
						}
                    }
				}
				
				if (typeof(datetime) == 'object') {
					return datetime.format('d.m.Y H:i');
				}
				
				return '';
			},
            listeners:{
                'change': function (){
					that.log('->hiddenField.change listener');
                    that.setValue(this.value);
					that.onChange(that, this.value);
                }
            }
        });
		
		this.dateFormParams = {
			layout: 'form',
			labelWidth: that.dateLabelWidth,
			border: false,
			bodyStyle:'background: transparent',
			items: [this.dateEditField]
		};
			
		if (typeof this.dateLabelWidth1 != undefined && this.dateLabelWidth1 != null) {
			this.dateFormParams.width = this.dateLabelWidth1;
		}		
				
		this.timeFormParams = {
                    layout: 'form',
                    labelWidth: that.timeLabelWidth,
                    border: false,
                    bodyStyle:'background: transparent',
                    items: [this.timeEditField]
                };
		
		if (typeof this.timeLabelWidth1 != undefined && this.timeLabelWidth1 != null) {
			this.timeFormParams.width = this.timeLabelWidth1;
		}
                
		
        Ext.apply(this, {
            layout: 'column',
            border: false,
            bodyStyle:'background: transparent',
            getValue: function () {
				that.log('->getValue');
				return Date.parse(that.hiddenField.getValue());
            },
            setValue: function (value) {
				that.log('->setValue', value);
                var datetime;
                if (typeof(value)=='string' && value != "") {
                    that.log('is string');
					datetime = Date.parseDate(value, 'd.m.Y H:i');
                    if (typeof(datetime)!='object') {
                        datetime = Date.parseDate(value, 'd.m.Y');
                        if (typeof(datetime)!='object') {
							that.log('parsing as date');
							var dt = new Date(value);
							if(dt != 'Invalid Date')
								datetime = dt;
							if (typeof(datetime)!='object') {
								var v = Date.parse(value);
								if (!isNaN(v)) {
									that.log('parsed');
									datetime = new Date();
									datetime.setTime(Date.parse(value));
								} else {
									that.log('can\'t parse');
								}
							}
                        }
                    }
                } else {
					that.log('is not string');
                    datetime = value;
                }
                if ((typeof(datetime) == 'object') && (datetime instanceof Date)){
					that.log('type of and instanceof');
                    that.timeEditField.setValue(datetime.format('H:i'));
                    that.dateEditField.setValue(datetime);
                    that.hiddenField.value = datetime.toString();
                } else {
					that.log('not type of or not datetime',datetime);
                    that.timeEditField.setValue('');
                    that.dateEditField.setValue('');
                    that.hiddenField.value = '';
                }
            },
            items: [
                this.hiddenField,
                this.dateFormParams,
                this.timeFormParams
            ]
        });
        sw.Promed.SwDateTimeField.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('swdatetimefield', sw.Promed.SwDateTimeField);

// компонент для вставки даты в шаблоны
sw.Promed.SwTemplDateField = Ext.extend(sw.Promed.SwDateField, {
	format: 'd.m.Y',
	plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
	width: 100,
	getDateStringValue: function() {
		var value = this.getValue();
		return Ext.util.Format.date(value, 'd.m.Y');
	},
	initComponent: function() {
		sw.Promed.SwTemplDateField.superclass.initComponent.apply(this, arguments);
	}
});

Ext.reg('swtempldatefield', sw.Promed.SwTemplDateField);

function getMinBirthDate(){

	// выпилено по https://redmine.swan.perm.ru/issues/105724
	//if (getRegionNick() == 'ekb') {
	//	// #100150 Регион Свердловская область
	//	// Для поля ввода даты рождения установить доступный диапазон значений: с 01.01.1901.
	//	return new Date(1901,0,1);
	//}

	var birth_date = new Date();
	var offset = 150;
	if (getGlobalOptions().date) {
		birth_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
	}
	if (getRegionNick() == 'ufa') {
		offset = 150;
	}
	birth_date.setFullYear((birth_date.getFullYear() - offset));
	return birth_date.format('d.m.Y');
}
