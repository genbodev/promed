/* 
datePickerRange
компоненты для работы с датами
Входящие параметры:
minValue
maxValue
dateFrom
dateTo
intervalMode
dateFields = array[from, to] - параметры для поста грида (параметры для загрузки интервала грида)
 */

Ext.define('sw.datePickerRange', {
	extend: 'Ext.form.field.Date',
	requires: ['Ux.InputTextMask'],
	alias: 'widget.datePickerRange',
	enableKeyEvents : true,
	width: 175,
	format: 'd.m.Y - d.m.Y',	
	name: 'DateRange',
	dateChanged: false,
	minValue: 'unlimited',
	dateFields: null,
	maxValue: Ext.Date.clearTime(new Date()),
	dateFrom: Ext.Date.clearTime(new Date()),
	dateTo: Ext.Date.clearTime(new Date()),
	setExtraParams: false,
	intervalMode: 'day',
	initComponent : function(){
		var me = this,
            isString = Ext.isString,
            min, max,
			config = me.initialConfig;
		
		me.addEvents({
			setInterval: true
		});
		
		me.plugins = [new Ux.InputTextMask('99.99.9999 - 99.99.9999')];
        me.disabledDatesRE = null;
        me.initDisabledDays();

        me.callParent();
	},
	initValue: function() {
		var me = this,
            value = me.value;
			
		me.value = Ext.Date.format(me.dateFrom, 'd.m.Y') + ' - ' + Ext.Date.format(me.dateTo, 'd.m.Y')
        if (Ext.isString(value)) {
            me.value = me.rawToValue(value)
        }
        me.callParent();
    },
	
	setIntervalMode: function(mode){
		var panel = this.up('panel');
		//console.log(typeof this.initialConfig.intervalMode)
		if (typeof mode == 'undefined' && this.initialConfig.intervalMode){
			var mode = this.initialConfig.intervalMode;
		}
		if (panel && mode){
			this.intervalMode = mode;
			var intervalPrevButton = panel.down('toolbar datePrevDay');
			var intervalNextButton = panel.down('toolbar dateNextDay');
			var t = '';
			var ptext = 'Пред. ';
			var ntext = 'След. ';
			switch (mode)
			{
				case 'day' : {t = 'день'; break;}
				case 'week' : {t = 'неделя'; break;}
				case 'month' : {t = 'месяц'; break;}
			}
			intervalPrevButton.setText(ptext+t);
			intervalNextButton.setText(ntext+t);			
		}
	},
	
	prevDay: function(){
		var interval = 1;
		switch (this.intervalMode)
		{
			case 'day' : {interval = 1; break;}
			case 'week' : {interval = 7; break;}
			case 'month' : {this.setMonth(-1); return;}
		}
		this.dateChanged = false;
		if (this.minValue == 'unlimited' && this.intervalMode != 'month'){
			var daYAfterFrom = Ext.Date.add(this.dateFrom, Ext.Date.DAY, -interval);
			var daYAfterTo = Ext.Date.add(this.dateTo, Ext.Date.DAY, -interval);
			this.dateChanged = true;
		}
		else{
			if ( (Ext.Date.clearTime(this.dateFrom) < Ext.Date.clearTime(this.maxValue)) )
			{
				var daYAfterFrom = Ext.Date.add(this.dateFrom, Ext.Date.DAY, -interval)
				this.dateChanged = true
			}
			else{daYAfterFrom = this.dateFrom}

			if ( Ext.Date.clearTime(this.dateTo) < Ext.Date.clearTime(this.maxValue) )
			{
				var daYAfterTo = Ext.Date.add(this.dateTo, Ext.Date.DAY, -interval)
				this.dateChanged = true
			}
			else{daYAfterTo = this.dateTo}
		}
		
		this.shadowSetValue(daYAfterFrom, daYAfterTo)
		this.reloadParentGrid()
	},
	nextDay: function(){
		var interval = 1;
		switch (this.intervalMode)
		{
			case 'day' : {interval = 1; break;}
			case 'week' : {interval = 7; break;}
			case 'month' : {this.setMonth(1); return;}
		}
		this.dateChanged = false;
		if (this.maxValue == 'unlimited'){
			var daYAfterFrom = Ext.Date.add(this.dateFrom, Ext.Date.DAY, +interval);
			var daYAfterTo = Ext.Date.add(this.dateTo, Ext.Date.DAY, +interval);
			this.dateChanged = true;
		}
		else{
			if ( (Ext.Date.clearTime(this.dateFrom) < Ext.Date.clearTime(this.maxValue)) )
			{
				var daYAfterFrom = Ext.Date.add(this.dateFrom, Ext.Date.DAY, +interval)
				this.dateChanged = true
			}
			else{daYAfterFrom = this.dateFrom}

			if ( Ext.Date.clearTime(this.dateTo) < Ext.Date.clearTime(this.maxValue) )
			{
				var daYAfterTo = Ext.Date.add(this.dateTo, Ext.Date.DAY, +interval)
				this.dateChanged = true
			}
			else{daYAfterTo = this.dateTo}
		}
		this.shadowSetValue(daYAfterFrom, daYAfterTo)
		this.reloadParentGrid()
	},
	setMonth: function(m){
		
		var today = this.dateFrom;
		var todayMonth = Ext.Date.format(today, 'n');
		var todayYear = Ext.Date.format(today, 'Y');
		var newMonth = parseInt(todayMonth)+parseInt(m);
		var newYear = todayYear;
		if (newMonth == 0)
		{
			newMonth = 12;
			newYear--;
		}
		if (newMonth == 13)
		{
			newMonth = 1;
			newYear++;
		}
		
		var d = '01.'+newMonth+'.'+newYear;
		var firstDayPrevMonth = Ext.Date.parse(d, "d.n.Y");
		
		var lastDayPrevMonth = Ext.Date.getLastDateOfMonth(firstDayPrevMonth);
		this.shadowSetValue(firstDayPrevMonth, lastDayPrevMonth);
		this.reloadParentGrid();
	},
	clearDate: function() {
		var parentGrid = this.getParentGrid();
			
		if (!parentGrid) {
			return false;
		}
			
		var store = parentGrid.store,
			proxy = store.getProxy(),
			begDate = (this.dateFields[0])?this.dateFields[0]:'begDate',
			endDate = (this.dateFields[1])?this.dateFields[1]:'endDate'	;
		
			
		proxy.setExtraParam(begDate,null);
		proxy.setExtraParam(endDate,null);
		
		this.setValue('');
		store.load();
	},
	currentDay: function(){
		this.dateChanged = true
		if ( Ext.Date.isEqual(this.dateFrom, Ext.Date.clearTime(new Date())) && Ext.Date.isEqual(this.dateTo, Ext.Date.clearTime(new Date())) )
			{this.dateChanged = false}
		this.shadowSetValue(new Date(), new Date())
		this.reloadParentGrid()
	},
	currentWeek: function(){
		var today = new Date()
		var startWeek = Ext.Date.add(today, Ext.Date.DAY, -today.getDay()+1);
		var endWeek = Ext.Date.add(today, Ext.Date.DAY, 7-today.getDay());
		if (this.maxValue != 'unlimited'){
			if (endWeek > this.maxValue)
				{endWeek = this.maxValue}
		}

		this.shadowSetValue(startWeek, endWeek)
		this.reloadParentGrid()
	},
	currentMonth: function(){
		var dt = new Date()
		var startMonth = Ext.Date.getFirstDateOfMonth(dt);
		var endMonth = Ext.Date.getLastDateOfMonth(dt);
		if (this.maxValue != 'unlimited'){
			if (endMonth > this.maxValue)
				{endMonth = this.maxValue}
		}
		this.shadowSetValue(startMonth, endMonth)
		this.reloadParentGrid()
	},	
	formatDate : function(date){
		if (typeof date == 'object')
		{var s = Ext.Date.dateFormat(date.from, "d.m.Y") +' - '+ Ext.Date.dateFormat(date.to, "d.m.Y")}
		else {s = date}
		return s
    },
	getErrors: function(value) {
		var dateFrom = value.slice(0, 10)
		var dateTo = value.slice(13, 23)
		var valfrom =  Ext.Date.parse(dateFrom, "d.m.Y")
		var valto = Ext.Date.parse(dateTo, "d.m.Y")
		if (!valfrom || !valto){	
			return "Неправильный формат даты"
		}
		if (valfrom && valto){
			if (this.minValue != 'unlimited'){
				if (valfrom > this.maxValue || valto > this.maxValue){
					return "Дата не должна быть больше текущей"
				}
			}
			if ((valfrom > valto)){
				return "Значение даты начала должно быть меньше даты окончания"
			}
			else{
				this.dateFrom = valfrom
				this.dateTo = valto
			}
		}
		
    },
	valueToRaw : function(value){
		if (typeof value == 'object'){value = Ext.Date.dateFormat(value.from, "d.m.Y") +' - '+ Ext.Date.dateFormat(value.to, "d.m.Y")}
		return this.formatDate(this.parseDate(value))
    },
	rawToValue : function(rawValue){
		return rawValue;
    },
	shadowSetValue: function(dFrom, dTo){
		if (dFrom){			
		this.dateFrom = dFrom			
		}
		if (dTo){
			this.dateTo = dTo			
		}
		this.setValue(Ext.Date.dateFormat(this.dateFrom, "d.m.Y") +' - '+ Ext.Date.dateFormat(this.dateTo, "d.m.Y"))
	},
	parseDate : function(value) {
		if(!value){
            return value;
        }
		var val = {}
		var dateFrom = value.slice(0, 10)
		var dateTo = value.slice(13, 23)
		val.from =  Ext.Date.parse(dateFrom, "d.m.Y")
		val.to = Ext.Date.parse(dateTo, "d.m.Y")		
		if (!val.from || !val.to){return value}
		else{		
			this.dateFrom = val.from
			this.dateTo = val.to
			return (val)
		}
    },
	onExpand: function() {
        this.picker.setValue(this.dateFrom, this.dateTo);
    },
	getParentGrid: function() {
		var parentPanel = this.up('panel'),
			parentGrid;
			if (parentPanel.xtype == 'gridpanel'){
				parentGrid = parentPanel;
			}
			else{
				parentGrid = parentPanel.down('grid');
			}
			
		return parentGrid;

	},
	reloadParentGrid: function(){
		var params = null;
		
		if (this.dateFields){
			params = {};
			params[this.dateFields[0]] = Ext.Date.format(this.dateFrom, 'd.m.Y'),
			params[this.dateFields[1]] =  Ext.Date.format(this.dateTo, 'd.m.Y')			
		}
		this.fireEvent('setInterval', this.dateFrom, this.dateTo);
		//не будем грузить лишний раз грид
		//if(this.dateChanged)
		//{
			//@to do - сделать не parentGrid a store в качестве вход параметра
			var parentGrid = this.getParentGrid();
			if (!this.hasActiveError())
			{
				if(parentGrid){
					if (this.setExtraParams) {
						if (params) {
							for (var key in params) {
								if (params.hasOwnProperty(key)) {
									parentGrid.store.getProxy().setExtraParam(key,params[key]);
								}
							}
						} else {
							parentGrid.store.getProxy().setExtraParam('begDate',Ext.Date.format(this.dateFrom, 'd.m.Y'))
							parentGrid.store.getProxy().setExtraParam('endDate',Ext.Date.format(this.dateTo, 'd.m.Y'))
						}
						
						parentGrid.store.currentPage = 1;
						parentGrid.store.load();
					} else {
						
						parentGrid.store.currentPage = 1;
						parentGrid.store.load({
							params: (params) ? params : {
								begDate: Ext.Date.format(this.dateFrom, 'd.m.Y'),
								endDate: Ext.Date.format(this.dateTo, 'd.m.Y')
							}
						})
					}
				}
			}
	//}
	},
	createPicker: function() {
        var me = this
		
		var max = this.maxValue
		if (this.maxValue == 'unlimited'){
			var max = null;
		}
		
		var pickerFrom =  Ext.create('Ext.picker.Date', {
			value: this.dateTo,
			maxDate: max,
			region: 'west',
			split: true,
			floating: false,
			width: 250,
			height: 200,
			listeners: {
				select: function( c, date, eOpts ){
					this.shadowSetValue(date, null)
				}.bind(this)
			}
		})
		
		var pickerTo =  Ext.create('Ext.picker.Date', {
			value: this.dateTo,
			maxDate: max,
			region: 'east',
			split: true,
			floating: false,
			width: 250,
			height: 200,
			listeners: {
				select: function( c, date, eOpts ){
					this.shadowSetValue(null, date)
				}.bind(this)
			}
		})
		
		var picker = new Ext.panel.Panel({
			setValue : function(valueFrom, valueTo){
				pickerFrom.setValue(valueFrom)
				pickerTo.setValue(valueTo)
			},
			pickerField: me,
			floating: true,
			hidden: true,
			ownerCt: this.ownerCt,
			renderTo: document.body,
			height: 230,
			width: 502,
			cls: 'datarange_picker',
			layout: {
				type: 'border'
			},
			//title: '',
			items: [pickerFrom, pickerTo],
			dockedItems: [{
				xtype: 'toolbar',
				split: false,
				splitterResize: false,
				dock: 'bottom',
				layout: {
					align: 'middle',
					type: 'hbox'
				},
				items: [
					{
						xtype: 'button',
						text: 'Выбрать',
						flex: 1,
						handler: function() {
							this.reloadParentGrid()
							picker.hide()
						}.bind(this)
					}
				]
			}]
		})
        return (picker);
    },
	listeners: {
		keypress: function(c, e, o){
		if ( (e.getKey() == 9) || (e.getKey()==13) )
		{
			this.reloadParentGrid()
		}},
		change: function( c, newValue, oldValue, eOpts ){
			if (newValue == oldValue)
				{this.dateChanged = false}
				else{this.dateChanged = true}
		}
	}
})

Ext.define('sw.datePrevDay', {
	extend: 'Ext.Button',
	alias: 'widget.datePrevDay',
	text: 'Пред. день',
	textAlign: 'left',
	iconCls: 'arrow-previous16', 
	handler: function() {
		var panel = this.up('panel')
		if (panel){
			var daterange = panel.down('toolbar datePickerRange')
			if (daterange) {daterange.prevDay()}
		}
	},
	listeners: {
		render: function(){
			var panel = this.up('panel')
			if (panel){
				var daterange = panel.down('toolbar datePickerRange')
				if (daterange) {daterange.setIntervalMode()}
			}
		}
	}
})

Ext.define('sw.dateNextDay', {
	extend: 'Ext.Button',
	alias: 'widget.dateNextDay',
	text: 'След. день',
	textAlign: 'left',
	iconCls: 'arrow-next16',
	handler: function() {
		var panel = this.up('panel')
		if (panel){
			var daterange = panel.down('toolbar datePickerRange')			
			if (daterange) {daterange.nextDay()}
		}
	}
})

Ext.define('sw.dateCurrentDay', {
	extend: 'Ext.Button',
	alias: 'widget.dateCurrentDay',
	text: 'День',
	tooltip: 'текущий день',
	iconCls: 'datepicker-day16',
	handler: function() {
		var panel = this.up('panel')
		if (panel){
			var daterange = panel.down('toolbar datePickerRange')
			if (daterange) {
				daterange.currentDay();				
			}
		}
	},
	toggle: function(){
		var panel = this.up('panel')
		if (panel){
			var daterange = panel.down('toolbar datePickerRange')
			if (daterange) {
				daterange.setIntervalMode('day');
			}
		}
	}
})

Ext.define('sw.dateCurrentWeek', {
	extend: 'Ext.Button',
	alias: 'widget.dateCurrentWeek',
	text: 'Неделя',
	tooltip: 'текущая неделя',
	iconCls: 'datepicker-week16',
	handler: function() {
		var panel = this.up('panel')
		if (panel){
			var daterange = panel.down('toolbar datePickerRange')
			if (daterange) {
				daterange.currentWeek();				
			}
		}
	},
	toggle: function(){
		var panel = this.up('panel')
		if (panel){
			var daterange = panel.down('toolbar datePickerRange')
			if (daterange) {
				daterange.setIntervalMode('week');
			}
		}
	}
})

Ext.define('sw.dateCurrentMonth', {
	extend: 'Ext.Button',
	alias: 'widget.dateCurrentMonth',
	text: 'Месяц',
	tooltip: 'текущий месяц',
	iconCls: 'datepicker-month16',
	handler: function() {
		var panel = this.up('panel')
		if (panel){
			var daterange = panel.down('toolbar datePickerRange')
			if (daterange) {
				daterange.currentMonth();
				//daterange.setIntervalMode('month');
			}
		}
	},
	toggle: function(){
		var panel = this.up('panel')
		if (panel){
			var daterange = panel.down('toolbar datePickerRange')
			if (daterange) {
				daterange.setIntervalMode('month');
			}
		}
	}
})

Ext.define('sw.timeIntervalQuadHourCombo', {
	extend: 'Ext.form.ComboBox',
	alias: 'widget.timeIntervalQuadHourCombo',
	displayField: 'time',
	cls: 'stateCombo',
	initComponent : function(){
		var me = this,
			config = me.initialConfig;
			
		var k = [],
			t='';
		for(var i=0; i<23; i++){
			for(var j=0; j<=45; j+=15){
			    var h='', m='';
			    h = ((i==0)? '00':i.toString());
			    if(h.length==1) h = '0'+h
			    m = ((j==0)? '00':j);
				t = (h+':'+m);
				k.push({time:t});
			}
		}
		
		me.store = Ext.create ('Ext.data.Store', {
			fields: [
				{name: 'time', type:'string'}
			],
			data : k
		});

        me.callParent();
	},
	validator: function(val){
		var dt = Ext.Date.parse(val, "H:i");
			if (!dt){
				return 'Неправильный формат времени'
			}
			return true
	}
})

Ext.define('sw.timeGetCurrentTimeCombo', {
    extend: 'Ext.form.field.Trigger',
    alias: 'widget.timeGetCurrentTimeCombo',
	triggerCls: 'x-form-clock-trigger',
	cls: 'stateCombo',
    onTriggerClick: function() {
		this.setValue(Ext.Date.format(new Date(), 'H:i'));
    },
	validator: function(val){
		var ini = this.initialConfig,		
			dt = Ext.Date.parse(val, "H:i");
		
		if (!dt){
			if ((ini.allowBlank == true) && (val=='' || val=='__:__')){
				return true;
			}
			else
			{
				return 'Неправильный формат времени';
			}

		}
		return true	;
	}
});