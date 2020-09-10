/**
* swComponentLibToolbar - класс тулбара, прописываются ownerCt для кнопок.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      04.03.2009
*/

sw.Promed.Toolbar = Ext.extend(Ext.Toolbar, {
	initComponent: function() {
		if ( this.buttons )
			for ( i = 0; i<this.buttons.length; i++ )
				this.buttons[i].ownerCt = this;
		if ( this.items )
			for ( i = 0; i<this.items.length; i++ )
				this.items[i].ownerCt = this;
		sw.Promed.Toolbar.superclass.initComponent.apply(this, arguments);
  	}
});

sw.Promed.datePeriodToolbar = Ext.extend(Ext.Toolbar, {
	curDate: null,
	mode: 'day',
	onSelectPeriod: Ext.emptyFn,
	stepPeriod: function(num)
	{
		var date1 = (this.dateMenu.getValue1() || Date.parseDate(this.curDate, 'd.m.Y'));
		var date2 = (this.dateMenu.getValue2() || Date.parseDate(this.curDate, 'd.m.Y'));
		if('week'==this.mode) {
			date1 = date1.add(Date.DAY, (7 * num));
			var dayOfWeek = (date1.getDay() + 6) % 7;
			date1 = date1.add(Date.DAY, -dayOfWeek);
			date2 = date1.add(Date.DAY, 6);
		} else if('month'==this.mode) {
			date1 = date1.add(Date.MONTH, num).getFirstDateOfMonth();
			date2 = date1.getLastDateOfMonth();
		} else {
			date1 = date1.add(Date.DAY, num);
			date2 = date2.add(Date.DAY, num);
		}
		date1 = date1.clearTime();
		date2 = date2.clearTime();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	prevPeriod: function ()
	{
		this.stepPeriod(-1);
	},
	nextPeriod: function ()
	{
		this.stepPeriod(1);
	},
	currentDay: function ()
	{
		var date1 = Date.parseDate(this.curDate, 'd.m.Y');
		var date2 = Date.parseDate(this.curDate, 'd.m.Y');
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentWeek: function ()
	{
		var date1 = (Date.parseDate(this.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function ()
	{
		var date1 = (Date.parseDate(this.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	getPeriodBtn: function (mode)
	{
		switch(mode)
		{
			case 'day':
				return this.items.items[6];
				break;
			case 'week':
				return this.items.items[7];
				break;
			case 'month':
				return this.items.items[8];
				break;
			case 'range':
				return this.items.items[9];
				break;
			default:
				return null;
				break;
		}
	},
	onSelectMode: function(mode,allowLoad)
	{
		var btn = this.getPeriodBtn(mode);
		if (btn) 
		{
			this.mode = mode;
			btn.toggle(true);
			this.onSelectPeriod(this.dateMenu.getValue1(),this.dateMenu.getValue2(),allowLoad);
		}
	},
	initComponent: function() {
		this.dateMenu = new Ext.form.DateRangeField({
			width: 152,
			fieldLabel: langs('Период'),
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			],
			listeners:
			{
				'keydown': function (inp, e)
				{
					if (e.getKey() == Ext.EventObject.ENTER)
					{
						e.stopEvent();
						this.onSelectMode('range',true);
					}
				}.createDelegate(this),
				'select': function ()
				{
					this.onSelectMode('range',true);
				}.createDelegate(this)
			}
		});
		
		this.items = 
		[
			new Ext.Action({
				text: langs('Предыдущий'),
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function()
				{
					// на один интервал назад
					this.prevPeriod();
					this.onSelectMode(this.mode,true);
				}.createDelegate(this)
			}), 
			{
				xtype : "tbseparator"
			},
			this.dateMenu,
			{
				xtype : "tbseparator"
			},
			new Ext.Action({
				text: langs('Следующий'),
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function()
				{
					// на один интервал вперед
					this.nextPeriod();
					this.onSelectMode(this.mode,true);
				}.createDelegate(this)
			}), 
			{
				xtype: 'tbfill'
			},
			new Ext.Action({
				text: langs('День'),
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-day16',
				pressed: ('day' == this.mode),
				handler: function()
				{
					this.currentDay();
					this.onSelectMode('day',true);
				}.createDelegate(this)
			}), 
			new Ext.Action({
				text: langs('Неделя'),
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-week16',
				pressed: ('week' == this.mode),
				handler: function()
				{
					this.currentWeek();
					this.onSelectMode('week',true);
				}.createDelegate(this)
			}), 
			new Ext.Action({
				text: langs('Месяц'),
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-month16',
				pressed: ('month' == this.mode),
				handler: function()
				{
					this.currentMonth();
					this.onSelectMode('month',true);
				}.createDelegate(this)
			}),
			new Ext.Action({
				text: langs('Период'),
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-range16',
				//disabled: ('range' != this.mode),
				pressed: ('range' == this.mode),
				handler: function()
				{
					this.onSelectMode('range',true);
				}.createDelegate(this)
			})
		];
		sw.Promed.datePeriodToolbar.superclass.initComponent.apply(this, arguments);
	},
	onShow:function(allowLoad) {
		var mode = this.firstMode;
		switch(mode)
		{
			case 'day':
				this.currentDay();
				this.onSelectMode('day',allowLoad);
				break;
			case 'week':
				this.currentWeek();
				this.onSelectMode('week',allowLoad);
				break;
			case 'month':
				this.currentMonth();
				this.onSelectMode('month',allowLoad);
				break;
			case 'range':
				this.onSelectMode('range',allowLoad);
				break;
		}
	},
	onRender:function() {
		this.firstMode = this.mode;
		sw.Promed.datePeriodToolbar.superclass.onRender.apply(this, arguments);
	}
});
Ext.reg('dateperiodtoolbar', sw.Promed.datePeriodToolbar);