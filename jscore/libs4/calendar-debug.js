/*!
 * Extensible 1.5.2
 * Copyright(c) 2010-2013 Extensible, LLC
 * licensing@ext.ensible.com
 * http://ext.ensible.com
 */
/**
 * @class Extensible.calendar.data.EventMappings
 * @extends Object
 * <p>A simple object that provides the field definitions for
 * {@link Extensible.calendar.EventRecord EventRecord}s so that they can be easily overridden.</p>
 *
 * <p>There are several ways of overriding the default Event record mappings to customize how
 * Ext records are mapped to your back-end data model. If you only need to change a handful
 * of field properties you can directly modify the EventMappings object as needed and then
 * reconfigure it. The simplest approach is to only override specific field attributes:</p>
 * <pre><code>
 var M = Extensible.calendar.data.EventMappings;
 M.Title.mapping = 'evt_title';
 M.Title.name = 'EventTitle';
 Extensible.calendar.EventRecord.reconfigure();
 </code></pre>
 *
 * <p>You can alternately override an entire field definition using object-literal syntax, or
 * provide your own custom field definitions (as in the following example). Note that if you do
 * this, you <b>MUST</b> include a complete field definition, including the <tt>type</tt> attribute
 * if the field is not the default type of <tt>string</tt>.</p>
 * <pre><code>
 // Add a new field that does not exist in the default EventMappings:
 Extensible.calendar.data.EventMappings.Timestamp = {
    name: 'Timestamp',
    mapping: 'timestamp',
    type: 'date'
};
 Extensible.calendar.EventRecord.reconfigure();
 </code></pre>
 *
 * <p>If you are overriding a significant number of field definitions it may be more convenient
 * to simply redefine the entire EventMappings object from scratch. The following example
 * redefines the same fields that exist in the standard EventRecord object but the names and
 * mappings have all been customized. Note that the name of each field definition object
 * (e.g., 'EventId') should <b>NOT</b> be changed for the default EventMappings fields as it
 * is the key used to access the field data programmatically.</p>
 * <pre><code>
 Extensible.calendar.data.EventMappings = {
    EventId:     {name: 'ID', mapping:'evt_id', type:'int'},
    Resource_id:  {name: 'CalID', mapping: 'cal_id', type: 'int'},
    Resource_Name:       {name: 'EvtTitle', mapping: 'evt_title'},
    StartDate:   {name: 'StartDt', mapping: 'start_dt', type: 'date', dateFormat: 'c'},
    EndDate:     {name: 'EndDt', mapping: 'end_dt', type: 'date', dateFormat: 'c'},
    RRule:       {name: 'RecurRule', mapping: 'recur_rule'},
    Notes:       {name: 'Desc', mapping: 'full_desc'},
    IsAllDay:    {name: 'AllDay', mapping: 'all_day', type: 'boolean'},

    // We can also add some new fields that do not exist in the standard EventRecord:
    CreatedBy:   {name: 'CreatedBy', mapping: 'created_by'},
    IsPrivate:   {name: 'Private', mapping:'private', type:'boolean'}
};
 // Don't forget to reconfigure!
 Extensible.calendar.EventRecord.reconfigure();
 </code></pre>
 *
 * <p><b>NOTE:</b> Any record reconfiguration you want to perform must be done <b>PRIOR to</b>
 * initializing your data store, otherwise the changes will not be reflected in the store's records.</p>
 *
 * <p>Another important note is that if you alter the default mapping for <tt>EventId</tt>, make sure to add
 * that mapping as the <tt>idProperty</tt> of your data reader, otherwise it won't recognize how to
 * access the data correctly and will treat existing records as phantoms. Here's an easy way to make sure
 * your mapping is always valid:</p>
 * <pre><code>
 var reader = new Ext6.data.JsonReader({
    totalProperty: 'total',
    successProperty: 'success',
    rootProperty: 'data',
    messageProperty: 'message',

    // read the id property generically, regardless of the mapping:
    idProperty: Extensible.calendar.data.EventMappings.EventId.mapping  || 'id',

    // this is also a handy way to configure your reader's fields generically:
    fields: Extensible.calendar.EventRecord.prototype.fields.getRange()
});
 </code></pre>
 */
Ext6.ns('Extensible.calendar.data');

Extensible.calendar.data.EventMappings = {
	EvnDirection_id: {
		name: 'EvnDirection_id',
		mapping: 'EvnDirection_id',
		type: 'int'
	},
	EvnUslugaOper_id: {
		name: 'EvnUslugaOper_id',
		mapping: 'EvnUslugaOper_id',
		type: 'int'
	},
	EvnUslugaOper_setDT: {
		name: 'EvnUslugaOper_setDT',
		mapping: 'EvnUslugaOper_setDT',
		dateFormat: 'd.m.Y',
		type: 'date'
	},
	Resource_id: {
		name: 'Resource_id',
		mapping: 'Resource_id',
		type: 'int'
	},
	UslugaComplex_id: {
		name: 'UslugaComplex_id',
		mapping: 'UslugaComplex_id',
		useNull: true,
		type: 'int'
	},
	Title: {
		name: 'Title',
		mapping: 'title',
		type: 'string'
	},
	StartDate: {
		name: 'StartDate',
		mapping: 'start',
		type: 'date',
		dateFormat: 'c'
	},
	EndDate: {
		name: 'EndDate',
		mapping: 'end',
		type: 'date',
		dateFormat: 'c'
	},
	RRule: { // not currently used
		name: 'RecurRule',
		mapping: 'rrule',
		type: 'string'
	},
	OperBrig: {
		name: 'OperBrig',
		mapping: 'operbrig',
		type: 'auto'
	},
	Usluga: {
		name: 'Usluga',
		mapping: 'usluga',
		type: 'string'
	},
	IsAllDay: {
		name: 'IsAllDay',
		mapping: 'ad',
		type: 'boolean'
	}
};
/**
 * @class Extensible.calendar.data.CalendarMappings
 * @extends Object
 * A simple object that provides the field definitions for
 * {@link Extensible.calendar.data.CalendarModel CalendarRecord}s so that they can be easily overridden.
 *
 * <p>There are several ways of overriding the default Calendar record mappings to customize how
 * Ext records are mapped to your back-end data model. If you only need to change a handful
 * of field properties you can directly modify the CalendarMappings object as needed and then
 * reconfigure it. The simplest approach is to only override specific field attributes:</p>
 * <pre><code>
 var M = Extensible.calendar.data.CalendarMappings;
 M.Title.mapping = 'cal_title';
 M.Title.name = 'CalTitle';
 Extensible.calendar.data.CalendarModel.reconfigure();
 </code></pre>
 *
 * <p>You can alternately override an entire field definition using object-literal syntax, or
 * provide your own custom field definitions (as in the following example). Note that if you do
 * this, you <b>MUST</b> include a complete field definition, including the <tt>type</tt> attribute
 * if the field is not the default type of <tt>string</tt>.</p>
 * <pre><code>
 // Add a new field that does not exist in the default CalendarMappings:
 Extensible.calendar.data.CalendarMappings.Owner = {
    name: 'Owner',
    mapping: 'owner',
    type: 'string'
};
 Extensible.calendar.data.CalendarModel.reconfigure();
 </code></pre>
 *
 * <p>If you are overriding a significant number of field definitions it may be more convenient
 * to simply redefine the entire CalendarMappings object from scratch. The following example
 * redefines the same fields that exist in the standard CalendarRecord object but the names and
 * mappings have all been customized. Note that the name of each field definition object
 * (e.g., 'Resource_id') should <b>NOT</b> be changed for the default CalendarMappings fields as it
 * is the key used to access the field data programmatically.</p>
 * <pre><code>
 Extensible.calendar.data.CalendarMappings = {
    Resource_id:   {name:'ID', mapping: 'id', type: 'int'},
    Title:        {name:'CalTitle', mapping: 'title', type: 'string'},
    Description:  {name:'Desc', mapping: 'desc', type: 'string'},
    ColorId:      {name:'Color', mapping: 'color', type: 'int'},
    IsHidden:     {name:'Hidden', mapping: 'hidden', type: 'boolean'},

    // We can also add some new fields that do not exist in the standard CalendarRecord:
    Owner:        {name: 'Owner', mapping: 'owner'}
};
 // Don't forget to reconfigure!
 Extensible.calendar.data.CalendarModel.reconfigure();
 </code></pre>
 *
 * <p><b>NOTE:</b> Any record reconfiguration you want to perform must be done <b>PRIOR to</b>
 * initializing your data store, otherwise the changes will not be reflected in the store's records.</p>
 *
 * <p>Another important note is that if you alter the default mapping for <tt>Resource_id</tt>, make sure to add
 * that mapping as the <tt>idProperty</tt> of your data reader, otherwise it won't recognize how to
 * access the data correctly and will treat existing records as phantoms. Here's an easy way to make sure
 * your mapping is always valid:</p>
 * <pre><code>
 var reader = new Ext6.data.JsonReader({
    totalProperty: 'total',
    successProperty: 'success',
    rootProperty: 'data',
    messageProperty: 'message',

    // read the id property generically, regardless of the mapping:
    idProperty: Extensible.calendar.data.CalendarMappings.Resource_id.mapping  || 'id',

    // this is also a handy way to configure your reader's fields generically:
    fields: Extensible.calendar.data.CalendarModel.prototype.fields.getRange()
});
 </code></pre>
 */
Ext6.ns('Extensible.calendar.data');

Extensible.calendar.data.CalendarMappings = {
	Resource_id: {
		name: 'Resource_id',
		mapping: 'Resource_id',
		type: 'int'
	},
	Resource_Name: {
		name: 'Resource_Name',
		mapping: 'Resource_Name',
		type: 'string'
	},
	Description: {
		name: 'Description',
		mapping: 'desc',
		type: 'string'
	},
	ColorId: {
		name: 'ColorId',
		mapping: 'color',
		type: 'int'
	},
	UslugaComplex_ids: {
		name: 'UslugaComplex_ids',
		mapping: 'UslugaComplex_ids',
		type: 'auto'
	},
	IsHidden: {
		name: 'IsHidden',
		mapping: 'hidden',
		type: 'boolean'
	}
};
/**
 * @class Extensible.calendar.template.BoxLayout
 * @extends Ext6.XTemplate
 * <p>This is the template used to render calendar views based on small day boxes within a non-scrolling container (currently
 * the all-day headers for {@link Extensible.calendar.view.Day DayView}.
 * This template is automatically bound to the underlying event store by the
 * calendar components and expects records of type {@link Extensible.calendar.data.EventModel}.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.calendar.template.BoxLayout', {
		extend: 'Ext6.XTemplate',

		requires: ['Ext6.Date'],

		/**
		 * @cfg {String} firstWeekDateFormat
		 * The date format used for the day boxes in the first week of the view only (subsequent weeks
		 * use the {@link #otherWeeksDateFormat} config). Defaults to 'D j'. Note that if the day names header is displayed
		 * above the first row (e.g., {@link #showHeader MonthView.showHeader} = true)
		 * then this value is ignored and {@link #otherWeeksDateFormat} will be used instead.
		 */
		firstWeekDateFormat: 'D j',
		/**
		 * @cfg {String} otherWeeksDateFormat
		 * The date format used for the date in day boxes (other than the first week, which is controlled by
		 * {@link #firstWeekDateFormat}). Defaults to 'j'.
		 */
		otherWeeksDateFormat: 'j',
		/**
		 * @cfg {String} singleDayDateFormat
		 * The date format used for the date in the header when in single-day view (defaults to 'l, F j, Y').
		 */
		singleDayDateFormat: 'l, F j, Y',

		// private
		constructor: function (config) {

			Ext6.apply(this, config);

			Extensible.calendar.template.BoxLayout.superclass.constructor.call(this,
				'<tpl for="weeks">',
				'<div id="{[this.id]}-wk-{[xindex-1]}" class="ext-cal-wk-ct" style="top:{[this.getRowTop(xindex, xcount)]}%; height:{[this.getRowHeight(xcount)]}%;">',
				'<table class="ext-cal-bg-tbl" cellpadding="0" cellspacing="0">',
				'<tbody>',
				'<tr>',
				'<tpl for=".">',
				'<td id="{[this.id]}-day-{[xindex-1]}" class="{cellCls}">&#160;</td>',
				'</tpl>',
				'</tr>',
				'</tbody>',
				'</table>',
				'<table class="ext-cal-evt-tbl" cellpadding="0" cellspacing="0">',
				'<tbody>',
				'<tr>',
				'<tpl for=".">',
				'<td id="{[this.id]}-ev-day-{[xindex-1]}" class="{titleCls}"><div>{title}</div></td>', // {date:date("Ymd")}
				'</tpl>',
				'</tr>',
				'</tbody>',
				'</table>',
				'</div>',
				'</tpl>', {
					getRowTop: function (i, ln) {
						return ((i - 1) * (100 / ln));
					},
					getRowHeight: function (ln) {
						return 100 / ln;
					}
				}
			);
		},

		// private
		applyTemplate: function (o) {

			Ext6.apply(this, o);

			var w = 0, title = '',
				todayCls = o.todayCls,
				weeks = [[]],
				today = Extensible.Date.today(),
				thisMonth = this.startDate.getMonth();

			weeks[w] = [];

			this.calendarStore.each(function (rec) {
				var dayFmt = (w == 0) ? this.firstWeekDateFormat : this.otherWeeksDateFormat;
				title = rec.get('Resource_Name');

				weeks[w].push({
					title: title,
					titleCls: 'ext-cal-dtitle ' +
					(w == 0 ? ' ext-cal-dtitle-first' : ''),
					cellCls: 'ext-cal-day '
				});
			});

			if (Ext6.getVersion().isLessThan('4.1')) {
				return Extensible.calendar.template.BoxLayout.superclass.applyTemplate.call(this, {
					weeks: weeks
				});
			}
			else {
				return this.applyOut({
					weeks: weeks
				}, []).join('');
			}
		}
	},
	function () {
		this.createAlias('apply', 'applyTemplate');
	});
/**
 * @class Extensible.calendar.template.DayHeader
 * @extends Ext6.XTemplate
 * <p>This is the template used to render the all-day event container used in {@link Extensible.calendar.view.Day DayView}.
 * Internally the majority of the layout logic is deferred to an instance of
 * {@link Extensible.calendar.template.BoxLayout}.</p>
 * <p>This template is automatically bound to the underlying event store by the
 * calendar components and expects records of type {@link Extensible.calendar.data.EventModel}.</p>
 * <p>Note that this template would not normally be used directly. Instead you would use the {@link Extensible.calendar.view.DayTemplate}
 * that internally creates an instance of this template along with a {@link Extensible.calendar.template.DayBody}.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.calendar.template.DayHeader', {
		extend: 'Ext6.XTemplate',

		requires: ['Extensible.calendar.template.BoxLayout'],

		// private
		constructor: function (config) {

			Ext6.apply(this, config);

			this.allDayTpl = Ext6.create('Extensible.calendar.template.BoxLayout', config);
			this.allDayTpl.compile();

			Extensible.calendar.template.DayHeader.superclass.constructor.call(this,
				'<div class="ext-cal-hd-ct">',
				'<table class="ext-cal-hd-days-tbl" cellspacing="0" cellpadding="0">',
				'<tbody>',
				'<tr>',
				'<td class="ext-cal-gutter"><div class="time_dropdown" ext:qtip="Изменить масштаб"></div></td>',
				'<td class="ext-cal-hd-days-td"><div class="ext-cal-hd-ad-inner">{allDayTpl}</div></td>',
				'<td class="ext-cal-gutter-rt"><div class="tables_menu" ext:qtip="Настроить список столов"></div></td>',
				'</tr>',
				'</tbody>',
				'</table>',
				'</div>'
			);
		},

		// private
		applyTemplate: function (o) {
			var templateConfig = {
				allDayTpl: this.allDayTpl.apply(o)
			};

			if (Ext6.getVersion().isLessThan('4.1')) {
				return Extensible.calendar.template.DayHeader.superclass.applyTemplate.call(this, templateConfig);
			}
			else {
				return this.applyOut(templateConfig, []).join('');
			}
		}
	},
	function () {
		this.createAlias('apply', 'applyTemplate');
	});
/**
 * @class Extensible.calendar.template.DayBody
 * @extends Ext6.XTemplate
 * <p>This is the template used to render the scrolling body container used in {@link Extensible.calendar.view.Day DayView}.
 * This template is automatically bound to the underlying event store by the
 * calendar components and expects records of type {@link Extensible.calendar.data.EventModel}.</p>
 * <p>Note that this template would not normally be used directly. Instead you would use the {@link Extensible.calendar.view.DayTemplate}
 * that internally creates an instance of this template along with a {@link Extensible.calendar.DayHeaderTemplate}.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.calendar.template.DayBody', {
		extend: 'Ext6.XTemplate',

		// private
		constructor: function (config) {

			Ext6.apply(this, config);

			Extensible.calendar.template.DayBody.superclass.constructor.call(this,
				'<table class="ext-cal-bg-tbl" cellspacing="0" cellpadding="0" style="height:{dayHeight}px;">',
				'<tbody>',
				'<tr height="1">',
				'<td class="ext-cal-gutter"></td>',
				'<td colspan="{columnCount}">',
				'<div class="ext-cal-bg-rows">',
				'<div class="ext-cal-bg-rows-inner">',
				'<tpl for="beforetimes">',
				'<div class="ext-cal-bg-row beforetimes ext-row-{[xcount-xindex+1]}" style="height:{parent.hourHeight}px;">',
				'<div class="ext-cal-bg-row-div {parent.hourSeparatorCls}" style="height:{parent.hourSeparatorHeight}px;"></div>',
				'</div>',
				'</tpl>',
				'<tpl for="times">',
				'<div class="ext-cal-bg-row times ext-row-{[xindex]}" style="height:{parent.hourHeight}px;">',
				'<div class="ext-cal-bg-row-div {parent.hourSeparatorCls}" style="height:{parent.hourSeparatorHeight}px;"></div>',
				'</div>',
				'</tpl>',
				'<tpl for="aftertimes">',
				'<div class="ext-cal-bg-row aftertimes ext-row-{[xindex]}" style="height:{parent.hourHeight}px;">',
				'<div class="ext-cal-bg-row-div {parent.hourSeparatorCls}" style="height:{parent.hourSeparatorHeight}px;"></div>',
				'</div>',
				'</tpl>',
				'</div>',
				'</div>',
				'</td>',
				'</tr>',
				'<tr>',
				'<td class="ext-cal-day-times">',
				'<tpl for="beforetimes">',
				'<div class="ext-cal-bg-row beforetimes ext-row-{[xcount-xindex+1]}" style="height:{parent.hourHeight}px;">',
				'<div class="ext-cal-day-time-inner"  style="height:{parent.hourHeight}px;">{.}</div>',
				'</div>',
				'</tpl>',
				'<tpl for="times">',
				'<div class="ext-cal-bg-row times" style="height:{parent.hourHeight}px;">',
				'<div class="ext-cal-day-time-inner"  style="height:{parent.hourHeight}px;">{.}</div>',
				'</div>',
				'</tpl>',
				'<tpl for="aftertimes">',
				'<div class="ext-cal-bg-row aftertimes ext-row-{[xindex]}" style="height:{parent.hourHeight}px;">',
				'<div class="ext-cal-day-time-inner"  style="height:{parent.hourHeight}px;">{.}</div>',
				'</div>',
				'</tpl>',
				'</td>',
				'<tpl for="columns">',
				'<td class="ext-cal-day-col">',
				'<div class="ext-cal-day-col-inner">',
				'<div id="{[this.id]}-day-col-{.}" class="ext-cal-day-col-gutter" style="height:{parent.dayHeight}px;"></div>',
				'</div>',
				'</td>',
				'</tpl>',
				'</tr>',
				'</tbody>',
				'</table>'
			);
		},

		// private
		applyTemplate: function (o) {
			this.today = Extensible.Date.today();

			var i = 0, columns = [],
				dt = Ext6.Date.clone(o.viewStart),
				beforedt = Ext6.Date.clone(o.viewStart);

			this.calendarStore.each(function (rec) {
				columns[i] = rec.get('Resource_id');
				i++;
			});

			var times = [],
				beforetimes = [],
				aftertimes = [],
				start = this.viewStartHour,
				end = this.viewEndHour,
				mins = this.hourIncrement,
				dayHeight = this.hourHeight * (end - start),
				fmt = 'G:i',
				templateConfig;

			// use a fixed DST-safe date so times don't get skipped on DST boundaries
			dt = Extensible.Date.add(new Date('5/26/1972'), {hours: start});
			beforedt = Extensible.Date.add(new Date('5/26/1972'), {hours: start}).add(Date.DAY, -1);

			for (i = start; i < end; i++) {
				times.push(Ext6.Date.format(dt, fmt));
				dt = Extensible.Date.add(dt, {minutes: mins});
			}

			this.dayBody.viewStartHourOffset = 0;

			// если в сторе есть операции заканчивающеся завтра то график продялем до конца последней операции, т.е. добавляем в times ещё время
			var first = null;
			var last = null;
			if (this.ownerCalendarPanel.store) {
				this.ownerCalendarPanel.store.each(function (rec) {
					if (first == null || first > rec.get('StartDate')) {
						first = rec.get('StartDate');
					}
					if (last == null || last < rec.get('EndDate')) {
						last = rec.get('EndDate');
					}
				});
			}

			var curDay = this.ownerCalendarPanel.startDate.format("d");
			if (first && first.format("d") != curDay) {
				// продляем график
				var minutes = parseInt(first.format("i")) + parseInt(first.format("H")) * 60;
				minutes = Math.floor(minutes / mins) * mins; // надо minutes округлить до кратного mins (hourIncrement), чтобы график не поехал
				beforedt = Extensible.Date.add(beforedt, {minutes: minutes}); // добавляем минуты
				start = this.viewEndHour * minutes / 1440;
				end = this.viewEndHour;
				this.dayBody.viewStartHourOffset = end - start;

				for (i = start; i < end; i++) {
					beforetimes.push(Ext6.Date.format(beforedt, fmt));
					beforedt = Extensible.Date.add(beforedt, {minutes: mins});
				}
			}
			if (last && last.format("d") != curDay) {
				// продляем график
				var minutes = parseInt(last.format("i")) + parseInt(last.format("H")) * 60;
				start = this.viewStartHour;
				end = this.viewEndHour * minutes / 1440;
				for (i = start; i < end; i++) {
					aftertimes.push(Ext6.Date.format(dt, fmt));
					dt = Extensible.Date.add(dt, {minutes: mins});
				}
			}

			templateConfig = {
				columns: columns,
				columnCount: columns.length,
				times: times,
				beforetimes: beforetimes,
				aftertimes: aftertimes,
				hourHeight: this.hourHeight,
				hourSeparatorCls: this.showHourSeparator ? '' : 'no-sep', // the class suppresses the default separator
				dayHeight: dayHeight,
				hourSeparatorHeight: (this.hourHeight / 2)
			};

			if (Ext6.getVersion().isLessThan('4.1')) {
				return Extensible.calendar.template.DayBody.superclass.applyTemplate.call(this, templateConfig);
			}
			else {
				return this.applyOut(templateConfig, []).join('');
			}
		}
	},
	function () {
		this.createAlias('apply', 'applyTemplate');
	});
/*
 * @class Ext6.dd.ScrollManager
 * <p>Provides automatic scrolling of overflow regions in the page during drag operations.</p>
 * <p>The ScrollManager configs will be used as the defaults for any scroll container registered with it,
 * but you can also override most of the configs per scroll container by adding a
 * <tt>ddScrollConfig</tt> object to the target element that contains these properties: {@link #hthresh},
 * {@link #vthresh}, {@link #increment} and {@link #frequency}.  Example usage:
 * <pre><code>
 var el = Ext6.get('scroll-ct');
 el.ddScrollConfig = {
 vthresh: 50,
 hthresh: -1,
 frequency: 100,
 increment: 200
 };
 Ext6.dd.ScrollManager.register(el);
 </code></pre>
 * <b>Note: This class uses "Point Mode" and is untested in "Intersect Mode".</b>
 * @singleton
 */
Ext6.define('Ext6.dd.ScrollManager', {
	singleton: true,
	requires: [
		'Ext6.dd.DragDropManager'
	],

	constructor: function () {
		var ddm = Ext6.dd.DragDropManager;
		ddm.fireEvents = Ext6.Function.createSequence(ddm.fireEvents, this.onFire, this);
		ddm.stopDrag = Ext6.Function.createSequence(ddm.stopDrag, this.onStop, this);
		this.doScroll = Ext6.Function.bind(this.doScroll, this);
		this.ddmInstance = ddm;
		this.els = {};
		this.dragEl = null;
		this.proc = {};
	},

	onStop: function (e) {
//        var sm = Ext6.dd.ScrollManager;
//        sm.dragEl = null;
//        sm.clearProc();
		this.dragEl = null;
		this.clearProc();
	},

	triggerRefresh: function () {
		if (this.ddmInstance.dragCurrent) {
			this.ddmInstance.refreshCache(this.ddmInstance.dragCurrent.groups);
		}
	},

	doScroll: function () {
		if (this.ddmInstance.dragCurrent) {
			var proc = this.proc,
				procEl = proc.el,
				ddScrollConfig = proc.el.ddScrollConfig,
				inc = ddScrollConfig ? ddScrollConfig.increment : this.increment;

			if (!this.animate) {
				if (procEl.scroll(proc.dir, inc)) {
					this.triggerRefresh();
				}
			} else {
				procEl.scroll(proc.dir, inc, true, this.animDuration, this.triggerRefresh);
			}
		}
	},

	clearProc: function () {
		var proc = this.proc;
		if (proc.id) {
			clearInterval(proc.id);
		}
		proc.id = 0;
		proc.el = null;
		proc.dir = "";
	},

	startProc: function (el, dir) {
		this.clearProc();
		this.proc.el = el;
		this.proc.dir = dir;
		var group = el.ddScrollConfig ? el.ddScrollConfig.ddGroup : undefined,
			freq = (el.ddScrollConfig && el.ddScrollConfig.frequency)
				? el.ddScrollConfig.frequency
				: this.frequency;

		if (group === undefined || this.ddmInstance.dragCurrent.ddGroup == group) {
			this.proc.id = setInterval(this.doScroll, freq);
		}
	},

	onFire: function (e, isDrop) {
		if (isDrop || !this.ddmInstance.dragCurrent) {
			return;
		}
		if (!this.dragEl || this.dragEl != this.ddmInstance.dragCurrent) {
			this.dragEl = this.ddmInstance.dragCurrent;
			// refresh regions on drag start
			this.refreshCache();
		}

		var xy = e.getXY(),
			pt = e.getPoint(),
			proc = this.proc,
			els = this.els;

		for (var id in els) {
			var el = els[id], r = el._region;
			var c = el.ddScrollConfig ? el.ddScrollConfig : this;
			if (r && r.contains(pt) && el.isScrollable()) {
				if (r.bottom - pt.y <= c.vthresh) {
					if (proc.el != el) {
						this.startProc(el, "down");
					}
					return;
				} else if (r.right - pt.x <= c.hthresh) {
					if (proc.el != el) {
						this.startProc(el, "left");
					}
					return;
				} else if (pt.y - r.top <= c.vthresh) {
					if (proc.el != el) {
						this.startProc(el, "up");
					}
					return;
				} else if (pt.x - r.left <= c.hthresh) {
					if (proc.el != el) {
						this.startProc(el, "right");
					}
					return;
				}
			}
		}
		this.clearProc();
	},

	/**
	 * Registers new overflow element(s) to auto scroll
	 * @param {Mixed/Array} el The id of or the element to be scrolled or an array of either
	 */
	register: function (el) {
		if (Ext6.isArray(el)) {
			for (var i = 0, len = el.length; i < len; i++) {
				this.register(el[i]);
			}
		} else {
			el = Ext6.get(el);
			this.els[el.id] = el;
		}
	},

	/**
	 * Unregisters overflow element(s) so they are no longer scrolled
	 * @param {Mixed/Array} el The id of or the element to be removed or an array of either
	 */
	unregister: function (el) {
		if (Ext6.isArray(el)) {
			for (var i = 0, len = el.length; i < len; i++) {
				this.unregister(el[i]);
			}
		} else {
			el = Ext6.get(el);
			delete this.els[el.id];
		}
	},

	/**
	 * The number of pixels from the top or bottom edge of a container the pointer needs to be to
	 * trigger scrolling (defaults to 25)
	 * @type Number
	 */
	vthresh: 25,
	/**
	 * The number of pixels from the right or left edge of a container the pointer needs to be to
	 * trigger scrolling (defaults to 25)
	 * @type Number
	 */
	hthresh: 25,

	/**
	 * The number of pixels to scroll in each scroll increment (defaults to 100)
	 * @type Number
	 */
	increment: 100,

	/**
	 * The frequency of scrolls in milliseconds (defaults to 500)
	 * @type Number
	 */
	frequency: 500,

	/**
	 * True to animate the scroll (defaults to true)
	 * @type Boolean
	 */
	animate: true,

	/**
	 * The animation duration in seconds -
	 * MUST BE less than Ext6.dd.ScrollManager.frequency! (defaults to .4)
	 * @type Number
	 */
	animDuration: 0.4,

	/**
	 * The named drag drop {@link Ext6.dd.DragSource#ddGroup group} to which this container belongs (defaults to undefined).
	 * If a ddGroup is specified, then container scrolling will only occur when a dragged object is in the same ddGroup.
	 * @type String
	 */
	ddGroup: undefined,

	/**
	 * Manually trigger a cache refresh.
	 */
	refreshCache: function () {
		var els = this.els,
			id;
		for (id in els) {
			if (typeof els[id] == 'object') { // for people extending the object prototype
				els[id]._region = els[id].getRegion();
			}
		}
	}
});
/**
 * @class Extensible.calendar.dd.StatusProxy
 * A specialized drag proxy that supports a drop status icon, {@link Ext6.dom.Layer} styles and auto-repair. It also
 * contains a calendar-specific drag status message containing details about the dragged event's target drop date range.
 * This is the default drag proxy used by all calendar views.
 * @constructor
 * @param {Object} config
 */
Ext6.define('Extensible.calendar.dd.StatusProxy', {
	extend: 'Ext6.dd.StatusProxy',

	/**
	 * @cfg {String} moveEventCls
	 * The CSS class to apply to the status element when an event is being dragged (defaults to 'ext-cal-dd-move').
	 */
	moveEventCls: 'ext-cal-dd-move',
	/**
	 * @cfg {String} addEventCls
	 * The CSS class to apply to the status element when drop is not allowed (defaults to 'ext-cal-dd-add').
	 */
	addEventCls: 'ext-cal-dd-add',

	// Overridden to add a separate message element inside the ghost area.
	// Applies only to Ext 4.1 and above, see notes in constructor
	renderTpl: [
		'<div class="' + Ext6.baseCSSPrefix + 'dd-drop-icon"></div>',
		'<div class="ext-dd-ghost-ct">',
		'<div id="{id}-ghost" class="' + Ext6.baseCSSPrefix + 'dd-drag-ghost"></div>',
		'<div id="{id}-message" class="ext-dd-msg"></div>',
		'</div>'
	],

	// private -- applies only to Ext 4.1 and above, see notes in constructor
	childEls: [
		'ghost',
		'message'
	],

	// private
	constructor: function (config) {
		this.callParent(arguments);
	},

	// inherit docs
	update: function (html) {
		this.callParent(arguments);

		// If available, set the ghosted event el to autoHeight for visual consistency
		var el = this.ghost.dom.firstChild;
		if (el) {
			Ext6.fly(el).setHeight('auto');
		}
	},

	/* @private
	 * Update the calendar-specific drag status message without altering the ghost element.
	 * @param {String} msg The new status message
	 */
	updateMsg: function (msg) {
		this.message.update(msg);
	}
});
/* @private
 * Internal drag zone implementation for the calendar day and week views.
 */
Ext6.define('Extensible.calendar.dd.DayDragZone', {
	extend: 'Ext6.dd.DragZone',
	requires: [
		'Ext6.util.Point',
		'Extensible.calendar.dd.StatusProxy',
		'Extensible.calendar.data.EventMappings'
	],
	eventSelector: '.ext-cal-evt',
	constructor: function (el, config) {
		/*if (!Extensible.calendar._statusProxyInstance) {
			Extensible.calendar._statusProxyInstance = Ext6.create('Extensible.calendar.dd.StatusProxy'); // todo в extjs 6 не получилось заюзать кастомный прокси, в котором отображается дата на которую кидают операцию
		}
		this.proxy = Extensible.calendar._statusProxyInstance;*/
		this.callParent(arguments);
	},
	onInitDrag: function (x, y) {
		if (this.dragData.ddel) {
			var ghost = this.dragData.ddel.cloneNode(true),
				child = Ext6.fly(ghost).down('dl');

			Ext6.fly(ghost).setWidth('auto');

			if (child) {
				// for IE/Opera
				child.setHeight('auto');
			}
			this.proxy.update(ghost);
			this.onStartDrag(x, y);
		}
		else if (this.dragData.start) {
			this.onStartDrag(x, y);
		}
		this.view.onInitDrag();
		return true;
	},
	afterRepair: function () {
		if (Ext6.enableFx && this.dragData.ddel) {
			// Ext6.Element.fly(this.dragData.ddel).highlight(this.hlColor || 'c3daf9');
		}
		this.dragging = false;
	},
	getRepairXY: function (e) {
		if (this.dragData.ddel) {
			// return Ext6.Element.fly(this.dragData.ddel).getXY();
		}
	},
	afterInvalidDrop: function (e, id) {
		Ext6.select('.ext-dd-shim').hide();
	},
	destroy: function () {
		this.callParent(arguments);
		delete Extensible.calendar._statusProxyInstance;
	},
	ddGroup: 'DayViewDD',
	resizeSelector: '.ext-evt-rsz',
	getDragData: function (e) {
		var t = e.getTarget(this.resizeSelector, 2, true);
		if (t) {
			var p = t.parent(this.eventSelector),
				rec = this.view.getEventRecordFromEl(p);

			if (!rec) {
				// if rec is null here it usually means there was a timing issue between drag
				// start and the browser reporting it properly. Simply ignore and it will
				// resolve correctly once the browser catches up.
				return;
			}
			return {
				type: 'eventresize',
				xy: e.getXY(),
				ddel: p.dom,
				eventStart: rec.data[Extensible.calendar.data.EventMappings.StartDate.name],
				eventEnd: rec.data[Extensible.calendar.data.EventMappings.EndDate.name],
				proxy: this.proxy
			};
		}
		var t = e.getTarget(this.eventSelector, 3);
		if (t) {
			var title = e.getTarget('.title', 3);
			if (title) { // таскать можно только за заголовок
				var rec = this.view.getEventRecordFromEl(t);
				if (!rec) {
					// if rec is null here it usually means there was a timing issue between drag
					// start and the browser reporting it properly. Simply ignore and it will
					// resolve correctly once the browser catches up.
					return;
				}
				return {
					type: 'eventdrag',
					xy: e.getXY(),
					ddel: t,
					eventStart: rec.data[Extensible.calendar.data.EventMappings.StartDate.name],
					eventEnd: rec.data[Extensible.calendar.data.EventMappings.EndDate.name],
					proxy: this.proxy
				};
			}
		}

		return null;
	}
});
/* @private
 * Internal drop zone implementation for the calendar day and week views.
 */
Ext6.define('Extensible.calendar.dd.DayDropZone', {
	extend: 'Ext6.dd.DropZone',
	requires: [
		Ext6.getVersion().isLessThan('4.2') ? 'Ext6.Layer' : 'Ext6.dom.Layer',
		'Extensible.calendar.data.EventMappings'
	],
	eventSelector: '.ext-cal-evt',
	// private
	shims: [],
	getTargetFromEvent: function (e) {
		var dragOffset = this.dragOffset || 0,
			y = e.getY() - dragOffset,
			d = this.view.getDayAt(e.getX(), y);

		return d.el ? d : null;
	},
	createShim: function () {
		var owner = this.view.ownerCalendarPanel ? this.view.ownerCalendarPanel : this.view;
		if (!this.shimCt) {
			this.shimCt = Ext6.get('ext-dd-shim-ct-' + owner.id);
			if (!this.shimCt) {
				this.shimCt = document.createElement('div');
				this.shimCt.id = 'ext-dd-shim-ct-' + owner.id;
				owner.getEl().parent().appendChild(this.shimCt);
			}
		}
		var el = document.createElement('div');
		el.className = 'ext-dd-shim';
		this.shimCt.appendChild(el);

		return Ext6.create(Ext6.getVersion().isLessThan('4.2') ? 'Ext6.Layer' : 'Ext6.dom.Layer', {
			shadow: false,
			useDisplay: true,
			constrain: false
		}, el);
	},
	clearShims: function () {
		Ext6.each(this.shims, function (shim) {
			if (shim) {
				shim.hide();
			}
		});
		this.DDMInstance.notifyOccluded = false;
	},
	onContainerOver: function (dd, e, data) {
		return this.dropAllowed;
	},
	onCalendarDragComplete: function () {
		delete this.dragStartDate;
		delete this.dragEndDate;
		this.clearShims();
	},
	onContainerDrop: function (dd, e, data) {
		this.onCalendarDragComplete();
		return false;
	},
	destroy: function () {
		Ext6.each(this.shims, function (shim) {
			if (shim) {
				Ext6.destroy(shim);
			}
		});

		Ext6.removeNode(this.shimCt);
		delete this.shimCt;
		this.shims.length = 0;
	},
	ddGroup: 'DayViewDD',
	dateRangeFormat: 'с {0} до {1}',
	dateFormat: 'd.m',
	/*notifyOver : function(dd, e, data){
		console.log('azaza1', e.getTarget());
		var n = this.getTargetFromEvent(e);
		console.log('azaza2', n);
		if(!n) {
			if(this.lastOverNode){
				this.onNodeOut(this.lastOverNode, dd, e, data);
				this.lastOverNode = null;
			}
			return this.onContainerOver(dd, e, data);
		}
		if(this.lastOverNode != n){
			if(this.lastOverNode){
				this.onNodeOut(this.lastOverNode, dd, e, data);
			}
			this.onNodeEnter(n, dd, e, data);
			this.lastOverNode = n;
		}
		return this.onNodeOver(n, dd, e, data);
	},*/
	onNodeOver: function (n, dd, e, data) {
		// получаем список разрешённых услуг для ресурса
		var UslugaComplex_ids = [];
		var rec = this.view.calendarStore.findRecord(Extensible.calendar.data.CalendarMappings.Resource_id.name, n.Resource_id);
		if (rec) {
			UslugaComplex_ids = rec.data[Extensible.calendar.data.CalendarMappings.UslugaComplex_ids.name];
		}

		var dt, text = this.createText,
			timeFormat = 'G:i';

		var evtEl = Ext6.get(data.ddel),
			dayCol = evtEl.parent().parent(),
			box = evtEl.getBox();
		var allowed = true;

		box.width = dayCol.getWidth();

		if (data.type == 'eventdrag') {
			var rec = this.view.getEventRecordFromEl(data.ddel);
			// проверяем услугу перемещаемой заявки
			if (!Ext6.isEmpty(rec.get('UslugaComplex_id')) && !rec.get('UslugaComplex_id').inlist(UslugaComplex_ids)) {
				allowed = false;
			}

			var curDay = this.ownerCalendarPanel.startDate.format("d");
			if (n.date.format("d") != curDay) {
				allowed = false; // переместить в другой день нельзя
			}

			if (this.dragOffset === undefined) {
				// on fast drags there is a lag between the original drag start xy position and
				// that first detected within the drop zone's getTargetFromEvent method (which is
				// where n.timeBox comes from). to avoid a bad offset we calculate the
				// timeBox based on the initial drag xy, not the current target xy.
				var initialTimeBox = this.view.getDayAt(data.xy[0], data.xy[1]).timeBox;
				this.dragOffset = initialTimeBox.y - box.y;
			}
			else {
				box.y = n.timeBox.y;
			}
			dt = Ext6.Date.format(n.date, (this.dateFormat + ' ' + timeFormat));
			box.x = n.el.getLeft();

			this.shim(n.date, box);
			text = this.moveText;
		} else if (data.type == 'eventresize') {
			if (!this.resizeDt) {
				this.resizeDt = n.date;
			}
			box.x = dayCol.getLeft();
			box.height = Math.ceil(Math.abs(e.getY() - box.y) / n.timeBox.height) * n.timeBox.height;
			if (e.getY() < box.y) {
				box.y -= box.height;
			}
			else {
				n.date = Extensible.Date.add(n.date, {minutes: this.ddIncrement});
			}
			this.shim(this.resizeDt, box);

			var diff = Extensible.Date.diff(this.resizeDt, n.date),
				curr = Extensible.Date.add(this.resizeDt, {millis: diff}),
				start = Extensible.Date.min(data.eventStart, curr),
				end = Extensible.Date.max(data.eventStart, curr);

			data.resizeDates = {
				StartDate: start,
				EndDate: end
			}

			dt = Ext6.String.format(this.dateRangeFormat,
				Ext6.Date.format(start, timeFormat),
				Ext6.Date.format(end, timeFormat));

			text = this.resizeText;
		} else if (data.records && data.records[0]) {
			// проверяем услугу перемещаемой заявки
			if (!Ext6.isEmpty(data.records[0].get('UslugaComplex_id')) && !data.records[0].get('UslugaComplex_id').inlist(UslugaComplex_ids)) {
				allowed = false;
			}
		}

		if (data.proxy && typeof data.proxy.updateMsg == 'function') {
			data.proxy.updateMsg(Ext6.String.format(text, dt));
		}

		data.allowed = allowed;
		if (allowed) {
			return this.dropAllowed;
		} else {
			return this.dropNotAllowed;
		}
	},

	shim: function (dt, box) {
		this.DDMInstance.notifyOccluded = true;

		Ext6.each(this.shims, function (shim) {
			if (shim) {
				shim.isActive = false;
				shim.hide();
			}
		});

		var shim = this.shims[0];
		if (!shim) {
			shim = this.createShim();
			this.shims[0] = shim;
		}

		shim.isActive = true;
		shim.show();
		shim.setBox(box);
	},

	onNodeDrop: function (n, dd, e, data) {
		var daydropzone = this;
		if (n && data && data.allowed && (!dd._dropNotAllowed)) {
			log('onNodeDrop', n, data);

			if (data.type == 'eventdrag') {
				var rec = this.view.getEventRecordFromEl(data.ddel);
				this.view.onEventDrop(rec, n.date, n.Resource_id);
				this.onCalendarDragComplete();
				delete this.dragOffset;
				return true;
			} else if (data.type == 'eventresize') {
				var rec = this.view.getEventRecordFromEl(data.ddel);
				this.view.onEventResize(rec, data.resizeDates);
				this.onCalendarDragComplete();
				delete this.resizeDt;
				return true;
			} else if (data.records && data.records[0]) {
				var rec = this.view.store.findRecord('EvnDirection_id', data.records[0].data['EvnDirection_id']);
				if (rec) {
					// если уже есть, то обновляем только дату
					var diff = n.date.getTime() - rec.data[Extensible.calendar.data.EventMappings.StartDate.name].getTime();
					rec.beginEdit();
					rec.set(Extensible.calendar.data.EventMappings.Resource_id.name, n.Resource_id);
					rec.set(Extensible.calendar.data.EventMappings.StartDate.name, n.date);
					rec.set(Extensible.calendar.data.EventMappings.EndDate.name, Extensible.Date.add(rec.data[Extensible.calendar.data.EventMappings.EndDate.name], {millis: diff}));
					rec.endEdit();
					this.view.save();
				} else {
					// иначе создаём новый, тут надо просто открыть форму планирования с заполненной датой, после её сохранения графики обновятся.
					getWnd('swEvnPrescrOperBlockPlanWindow').show({
						EvnDirection_id: data.records[0].data['EvnDirection_id'],
						Resource_id: n.Resource_id,
						startDate: n.date,
						callback: function() {
							// обновить гриды
							daydropzone.view.ownerWin.doFilter();
						}
					});

					/*
					// иначе создаём новый
					var rec = Ext6.create('Extensible.calendar.data.EventModel');
					rec.data['EvnDirection_id'] = data.records[0].data['EvnDirection_id'];
					rec.data[Extensible.calendar.data.EventMappings.Resource_id.name] = n.Resource_id;
					rec.data[Extensible.calendar.data.EventMappings.StartDate.name] = n.date;
					rec.data[Extensible.calendar.data.EventMappings.EndDate.name] = n.date;
					var sex = '';
					switch (data.records[0].data['Sex_id']) {
						case 1:
							sex = 'М';
							break;
						case 2:
							sex = 'Ж';
							break;
					}
					var age = data.records[0].data['Person_Age'];
					if (!Ext.isEmpty(age)) {
						age = age + sw4.ruWordCase(' год', ' года', ' лет', age);
					}
					rec.data[Extensible.calendar.data.EventMappings.Title.name] = data.records[0].data['Person_Fio'] + ' ' + age + ' ' + sex;
					this.view.onEventAdd(null, rec);
					*/
				}
				this.onCalendarDragComplete();
				return true;
			}
		}
		this.onCalendarDragComplete();
		return false;
	}
});
/**
 * @class Extensible.calendar.data.EventModel
 * @extends Ext6.data.Record
 * <p>This is the {@link Ext6.data.Record Record} specification for calendar event data used by the
 * {@link Extensible.calendar.CalendarPanel CalendarPanel}'s underlying store. It can be overridden as
 * necessary to customize the fields supported by events, although the existing field definition names
 * should not be altered. If your model fields are named differently you should update the <b>mapping</b>
 * configs accordingly.</p>
 * <p>The only required fields when creating a new event record instance are <tt>StartDate</tt> and
 * <tt>EndDate</tt>.  All other fields are either optional or will be defaulted if blank.</p>
 * <p>Here is a basic example for how to create a new record of this type:<pre><code>
 rec = new Extensible.calendar.data.EventModel({
    StartDate: '2101-01-12 12:00:00',
    EndDate: '2101-01-12 13:30:00',
    Title: 'My cool event',
    Notes: 'Some notes'
});
 </code></pre>
 * If you have overridden any of the record's data mappings via the {@link Extensible.calendar.data.EventMappings EventMappings} object
 * you may need to set the values using this alternate syntax to ensure that the field names match up correctly:<pre><code>
 var M = Extensible.calendar.data.EventMappings,
 rec = new Extensible.calendar.data.EventModel();

 rec.data[M.StartDate.name] = '2101-01-12 12:00:00';
 rec.data[M.EndDate.name] = '2101-01-12 13:30:00';
 rec.data[M.Title.name] = 'My cool event';
 rec.data[M.Notes.name] = 'Some notes';
 </code></pre>
 * @constructor
 * @param {Object} data (Optional) An object, the properties of which provide values for the new Record's
 * fields. If not specified the {@link Ext6.data.Field#defaultValue defaultValue}
 * for each field will be assigned.
 * @param {Object} id (Optional) The id of the Record. The id is used by the
 * {@link Ext6.data.Store} object which owns the Record to index its collection
 * of Records (therefore this id should be unique within each store). If an
 * id is not specified a {@link #phantom}
 * Record will be created with an {@link #Record.id automatically generated id}.
 */
Ext6.define('Extensible.calendar.data.EventModel', {
	extend: 'Ext6.data.Model',

	requires: [
		'Ext6.util.MixedCollection',
		'Extensible.calendar.data.EventMappings'
	],

	idProperty: 'EvnDirection_id',

	fields: [{
		name: 'EvnDirection_id',
		mapping: 'EvnDirection_id',
		type: 'int'
	}, {
		name: 'EvnUslugaOper_id',
		mapping: 'EvnUslugaOper_id',
		type: 'int'
	}, {
		name: 'EvnUslugaOper_setDT',
		mapping: 'EvnUslugaOper_setDT',
		dateFormat: 'd.m.Y',
		type: 'date'
	}, {
		name: 'Resource_id',
		mapping: 'Resource_id',
		type: 'int'
	}, {
		name: 'UslugaComplex_id',
		mapping: 'UslugaComplex_id',
		useNull: true,
		type: 'int'
	}, {
		name: 'Title',
		mapping: 'title',
		type: 'string'
	}, {
		name: 'StartDate',
		mapping: 'start',
		type: 'date',
		dateFormat: 'c'
	}, {
		name: 'EndDate',
		mapping: 'end',
		type: 'date',
		dateFormat: 'c'
	}, { // not currently used
		name: 'RecurRule',
		mapping: 'rrule',
		type: 'string'
	}, {
		name: 'OperBrig',
		mapping: 'operbrig',
		type: 'auto'
	}, {
		name: 'Usluga',
		mapping: 'usluga',
		type: 'string'
	}, {
		name: 'IsAllDay',
		mapping: 'ad',
		type: 'boolean'
	}],

	// Sencha broke getId() for mapped ids in 4.2. Mappings are required by Extensible and this method
	// began returning undefined for the record ids in 4.2. This override is here simply
	// to reinstate the 4.1.x version that still works as expected:
	getId: function () {
		return this.get(this.idProperty);
	}
});
Ext6.define('Extensible.calendar.data.EventStore', {
	extend: 'Ext6.data.Store',
	model: 'Extensible.calendar.data.EventModel',

	constructor: function (config) {
		config = config || {};

		// By default autoLoad will cause the store to load itself during the
		// constructor, before the owning calendar view has a chance to set up
		// the initial date params to use during loading.  We replace autoLoad
		// with a deferLoad property that the view can check for and use to set
		// up default params as needed, then call the load itself.
		this.deferLoad = config.autoLoad;
		config.autoLoad = false;

		//this._dateCache = [];

		this.callParent(arguments);
	},

	load: function (o) {
		Extensible.log('store load');
		o = o || {};

		// if params are passed delete the one-time defaults
		if (o.params) {
			delete this.initialParams;
		}
		// this.initialParams will only be set if the store is being loaded manually
		// for the first time (autoLoad = false) so the owning calendar view set
		// the initial start and end date params to use. Every load after that will
		// have these params set automatically during normal UI navigation.
		if (this.initialParams) {
			o.params = o.params || {};
			Ext6.apply(o.params, this.initialParams);
			delete this.initialParams;
		}

		this.callParent(arguments);
	}

//    execute : function(action, rs, options, /* private */ batch) {
//        if(action=='read'){
//            var i = 0,
//                dc = this._dateCache,
//                len = dc.length,
//                range,
//                p = options.params,
//                start = p.start,
//                end = p.end;
//
//            //options.add = true;
//            for(i; i<len; i++){
//                range = dc[i];
//
//            }
//        }
//        this.callParent(arguments);
//    }
});
/**
 * @class Extensible.calendar.data.CalendarModel
 * @extends Ext6.data.Record
 * <p>This is the {@link Ext6.data.Record Record} specification for calendar items used by the
 * {@link Extensible.calendar.CalendarPanel CalendarPanel}'s calendar store. If your model fields
 * are named differently you should update the <b>mapping</b> configs accordingly.</p>
 * <p>The only required fields when creating a new calendar record instance are Resource_id and
 * Title.  All other fields are either optional or will be defaulted if blank.</p>
 * <p>Here is a basic example for how to create a new record of this type:<pre><code>
 rec = new Extensible.calendar.data.CalendarModel({
    Resource_id: 5,
    Title: 'My Holidays',
    Description: 'My personal holiday schedule',
    ColorId: 3
});
 </code></pre>
 * If you have overridden any of the record's data mappings via the {@link Extensible.calendar.data.CalendarMappings CalendarMappings} object
 * you may need to set the values using this alternate syntax to ensure that the fields match up correctly:<pre><code>
 var M = Extensible.calendar.data.CalendarMappings;

 rec = new Extensible.calendar.data.CalendarModel();
 rec.data[M.Resource_id.name] = 5;
 rec.data[M.Title.name] = 'My Holidays';
 rec.data[M.Description.name] = 'My personal holiday schedule';
 rec.data[M.ColorId.name] = 3;
 </code></pre>
 * @constructor
 * @param {Object} data (Optional) An object, the properties of which provide values for the new Record's
 * fields. If not specified the {@link Ext6.data.Field#defaultValue defaultValue}
 * for each field will be assigned.
 * @param {Object} id (Optional) The id of the Record. The id is used by the
 * {@link Ext6.data.Store} object which owns the Record to index its collection
 * of Records (therefore this id should be unique within each store). If an
 * id is not specified a {@link #phantom}
 * Record will be created with an {@link #Record.id automatically generated id}.
 */
Ext6.define('Extensible.calendar.data.CalendarModel', {
	extend: 'Ext6.data.Model',

	requires: [
		'Ext6.util.MixedCollection',
		'Extensible.calendar.data.CalendarMappings'
	],

	idProperty: 'Resource_id',

	fields: [{
		name: 'Resource_id',
		mapping: 'Resource_id',
		type: 'int'
	}, {
		name: 'Resource_Name',
		mapping: 'Resource_Name',
		type: 'string'
	}, {
		name: 'Description',
		mapping: 'desc',
		type: 'string'
	}, {
		name: 'ColorId',
		mapping: 'color',
		type: 'int'
	}, {
		name: 'UslugaComplex_ids',
		mapping: 'UslugaComplex_ids',
		type: 'auto'
	}, {
		name: 'IsHidden',
		mapping: 'hidden',
		type: 'boolean'
	}]
});
/*
 * A simple reusable store that loads static calendar field definitions into memory
 * and can be bound to the CalendarCombo widget and used for calendar color selection.
 */
Ext6.define('Extensible.calendar.data.MemoryCalendarStore', {
	extend: 'Ext6.data.Store',
	model: 'Extensible.calendar.data.CalendarModel',

	requires: [
		'Ext6.data.proxy.Memory',
		'Ext6.data.reader.Json',
		'Ext6.data.writer.Json',
		'Extensible.calendar.data.CalendarModel',
		'Extensible.calendar.data.CalendarMappings'
	],

	proxy: {
		type: 'memory',
		reader: {
			type: 'json'
		},
		writer: {
			type: 'json'
		}
	},

	autoLoad: true,

	initComponent: function () {
		this.sorters = this.sorters || [{
				property: Extensible.calendar.data.CalendarMappings.Resource_Name.name,
				direction: 'ASC'
			}];

		this.idProperty = this.idProperty || Extensible.calendar.data.CalendarMappings.Resource_id.name || 'id';

		this.fields = Extensible.calendar.data.CalendarModel.prototype.fields.getRange();

		this.callParent(arguments);
	}
});
/*
 * This is a simple in-memory store implementation that is ONLY intended for use with
 * calendar samples running locally in the browser with no external data source. Under
 * normal circumstances, stores that use a MemoryProxy are read-only and intended only
 * for displaying data read from memory. In the case of the calendar, it's still quite
 * useful to be able to deal with in-memory data for sample purposes (as many people
 * may not have PHP set up to run locally), but by default, updates will not work since the
 * calendar fully expects all CRUD operations to be supported by the store (and in fact
 * will break, for example, if phantom records are not removed properly). This simple
 * class gives us a convenient way of loading and updating calendar event data in memory,
 * but should NOT be used outside of the local samples.
 *
 * For a real-world store implementation see the remote sample (remote.js).
 */
Ext6.define('Extensible.calendar.data.MemoryEventStore', {
	extend: 'Ext6.data.Store',
	model: 'Extensible.calendar.data.EventModel',

	requires: [
		'Ext6.data.proxy.Memory',
		'Ext6.data.reader.Json',
		'Ext6.data.writer.Json',
		'Extensible.calendar.data.EventModel',
		'Extensible.calendar.data.EventMappings'
	],

	proxy: {
		type: 'memory',
		reader: {
			type: 'json',
			rootProperty: 'evts'
		},
		writer: {
			type: 'json'
		}
	},

	// private
	constructor: function (config) {
		config = config || {};

		this.callParent(arguments);

		this.sorters = this.sorters || [{
				property: Extensible.calendar.data.EventMappings.StartDate.name,
				direction: 'ASC'
			}];

		this.idProperty = this.idProperty || 'EvnDirection_id' || 'id';

		this.fields = Extensible.calendar.data.EventModel.prototype.fields.getRange();

		// By default this shared example store will monitor its own CRUD events and
		// automatically show a page-level message for each event. This is simply a shortcut
		// so that each example doesn't have to provide its own messaging code, but this pattern
		// of handling messages at the store level could easily be implemented in an application
		// (see the source of test-app.js for an example of this). The autoMsg config is provided
		// to turn off this automatic messaging in any case where this store is used but the
		// default messaging is not desired.
		if (config.autoMsg !== false) {
			// Note that while the store provides individual add, update and remove events, those only
			// signify that records were added to the store, NOT that your changes were actually
			// persisted correctly in the back end (in remote scenarios). While this isn't an issue
			// with the MemoryProxy since everything is local, it's still harder to work with the
			// individual CRUD events since they have different APIs and quirks (notably the add and
			// update events both fire during record creation and it's difficult to differentiate a true
			// update from an update caused by saving the PK into a newly-added record). Because of all
			// this, in general the 'write' event is the best option for generically messaging after
			// CRUD persistance has actually succeeded.
			this.on('write', this.onWrite, this);
		}

		this.autoMsg = config.autoMsg;
		this.onCreateRecords = Ext6.Function.createInterceptor(this.onCreateRecords, this.interceptCreateRecords);
		this.initRecs();
	},

	// private - override to make sure that any records added in-memory
	// still get a unique PK assigned at the data level
	interceptCreateRecords: function (records, operation, success) {
		if (success) {
			var i = 0,
				rec,
				len = records.length;

			for (; i < len; i++) {
				records[i].data['EvnDirection_id'] = records[i].id;
			}
		}
	},

	// If the store started with preloaded inline data, we have to make sure the records are set up
	// properly as valid "saved" records otherwise they may get "added" on initial edit.
	initRecs: function () {
		this.each(function (rec) {
			rec.store = this;
			rec.phantom = false;
		}, this);
	},

	// private
	onWrite: function (store, operation) {

	},

	// private - override the default logic for memory storage
	onProxyLoad: function (operation) {
		var me = this,
			records;

		if (me.data && me.data.length > 0) {
			// this store has already been initially loaded, so do not reload
			// and lose updates to the store, just use store's latest data
			me.totalCount = me.data.length;
			records = me.data.items;
		}
		else {
			// this is the initial load, so defer to the proxy's result
			var resultSet = operation.getResultSet(),
				successful = operation.wasSuccessful();

			records = operation.getRecords();

			if (resultSet) {
				me.totalCount = resultSet.total;
			}
			if (successful) {
				me.loadRecords(records, operation);
			}
		}

		me.loading = false;
		me.fireEvent('load', me, records, successful);
	}
});
/**
 * @class Extensible.calendar.form.field.CalendarCombo
 * @extends Ext6.form.field.ComboBox
 * <p>A custom combo used for choosing from the list of available calendars to assign an event to. You must
 * pass a populated calendar store as the store config or the combo will not work.</p>
 * <p>This is pretty much a standard combo that is simply pre-configured for the options needed by the
 * calendar components. The default configs are as follows:<pre><code>
 fieldLabel: 'Calendar',
 triggerAction: 'all',
 queryMode: 'local',
 forceSelection: true,
 width: 200
 </code></pre>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.calendar.form.field.CalendarCombo', {
	extend: 'Ext6.form.field.ComboBox',
	alias: 'widget.extensible.calendarcombo',

	requires: ['Extensible.calendar.data.CalendarMappings'],

	fieldLabel: 'Calendar',
	triggerAction: 'all',
	queryMode: 'local',
	forceSelection: true,
	selectOnFocus: true,

	// private
	defaultCls: 'x-cal-default',
	hiddenCalendarCls: 'ext-cal-hidden',

	// private
	initComponent: function () {
		this.valueField = Extensible.calendar.data.CalendarMappings.Resource_id.name;
		this.displayField = Extensible.calendar.data.CalendarMappings.Resource_Name.name;

		this.listConfig = Ext6.apply(this.listConfig || {}, {
			getInnerTpl: this.getListItemTpl
		});

		this.store.on('update', this.refreshColorCls, this);

		this.callParent(arguments);
	},

	getListItemTpl: function (displayField) {
		return '<div class="x-combo-list-item x-cal-{' + Extensible.calendar.data.CalendarMappings.ColorId.name +
			'}"><div class="ext-cal-picker-icon">&#160;</div>{' + displayField + '}</div>';
	},

	// private
	afterRender: function () {
		this.callParent(arguments);

		this.wrap = this.el.down('.x6-form-item-body');
		this.wrap.addCls('ext-calendar-picker');

		this.icon = Ext6.core.DomHelper.append(this.wrap, {
			tag: 'div', cls: 'ext-cal-picker-icon ext-cal-picker-mainicon'
		});
	},

	/* @private
	 * Refresh the color CSS class based on the current field value
	 */
	refreshColorCls: function () {
		var me = this,
			calendarMappings = Extensible.calendar.data.CalendarMappings,
			colorCls = '',
			value = me.getValue();

		if (!me.wrap) {
			return me;
		}
		if (me.currentStyleClss !== undefined) {
			me.wrap.removeCls(me.currentStyleClss);
		}

		if (!Ext6.isEmpty(value)) {
			if (Ext6.isArray(value)) {
				value = value[0];
			}
			if (!value.data) {
				// this is a calendar id, need to get the record first then use its color
				value = this.store.findRecord(calendarMappings.Resource_id.name, value);
			}
			colorCls = 'x-cal-' + (value.data ? value.data[calendarMappings.ColorId.name] : value);
		}

		me.currentStyleClss = colorCls;

//        if (value && value.data && value.data[calendarMappings.IsHidden.name] === true) {
//            colorCls += ' ' + me.hiddenCalendarCls;
//        }
		me.wrap.addCls(colorCls);

		return me;
	},

	// inherited docs
	setValue: function (value) {
		if (!value && this.store.getCount() > 0) {
			// ensure that a valid value is always set if possible
			value = this.store.getAt(0).data[Extensible.calendar.data.CalendarMappings.Resource_id.name];
		}

		this.callParent(arguments);

		this.refreshColorCls();
	}
});
/* @private
 * Currently not used
 */
Ext6.define('Extensible.form.recurrence.Combo', {
	extend: 'Ext6.form.field.ComboBox',
	alias: 'widget.extensible.recurrencecombo',

	requires: ['Ext6.data.ArrayStore'],

	width: 160,
	fieldLabel: 'Repeats',
	mode: 'local',
	triggerAction: 'all',
	forceSelection: true,
	displayField: 'pattern',
	valueField: 'id',

	recurrenceText: {
		none: 'Does not repeat',
		daily: 'Daily',
		weekly: 'Weekly',
		monthly: 'Monthly',
		yearly: 'Yearly'
	},

	initComponent: function () {
		this.callParent(arguments);

		this.store = this.store || Ext6.create('Ext6.data.ArrayStore', {
				fields: ['id', 'pattern'],
				idIndex: 0,
				data: [
					['NONE', this.recurrenceText.none],
					['DAILY', this.recurrenceText.daily],
					['WEEKLY', this.recurrenceText.weekly],
					['MONTHLY', this.recurrenceText.monthly],
					['YEARLY', this.recurrenceText.yearly]
				]
			});
	},

	initValue: function () {
		this.callParent(arguments);

		if (this.value != undefined) {
			this.fireEvent('recurrencechange', this.value);
		}
	},

	setValue: function (v) {
		var old = this.value;

		this.callParent(arguments);

		if (old != v) {
			this.fireEvent('recurrencechange', v);
		}
		return this;
	}
});
/* @private
 * Currently not used
 * Rrule info: http://www.kanzaki.com/docs/ical/rrule.html
 */
Ext6.define('Extensible.form.recurrence.Fieldset', {
	extend: 'Ext6.form.field.Field',
	alias: 'widget.extensible.recurrencefield',

	requires: ['Extensible.form.recurrence.Combo'],

	fieldLabel: 'Repeats',
	startDate: Ext6.Date.clearTime(new Date()),
	enableFx: true,

	initComponent: function () {
		this.callParent(arguments);

		if (!this.height) {
			this.autoHeight = true;
		}
	},

	onRender: function (ct, position) {
		if (!this.el) {
			this.frequencyCombo = Ext6.create('Extensible.form.recurrence.Combo', {
				id: this.id + '-frequency',
				listeners: {
					'recurrencechange': {
						fn: this.showOptions,
						scope: this
					}
				}
			});
			if (this.fieldLabel) {
				this.frequencyCombo.fieldLabel = this.fieldLabel;
			}

			this.innerCt = Ext6.create('Ext6.Container', {
				cls: 'extensible-recur-inner-ct',
				items: []
			});
			this.fieldCt = Ext6.create('Ext6.Container', {
				autoEl: {id: this.id}, //make sure the container el has the field's id
				cls: 'extensible-recur-ct',
				renderTo: ct,
				items: [this.frequencyCombo, this.innerCt]
			});

			this.fieldCt.ownerCt = this;
			this.innerCt.ownerCt = this.fieldCt;
			this.el = this.fieldCt.getEl();
			this.items = Ext6.create('Ext6.util.MixedCollection');
			this.items.addAll(this.initSubComponents());
		}
		this.callParent(arguments);
	},

//    afterRender : function(){
//        this.callParent(arguments);
//        this.setStartDate(this.startDate);
//    },

	// private
	initValue: function () {
		this.setStartDate(this.startDate);

		if (this.value !== undefined) {
			this.setValue(this.value);
		}
		else if (this.frequency !== undefined) {
			this.setValue('FREQ=' + this.frequency);
		}
		else {
			this.setValue('NONE');
		}
		this.originalValue = this.getValue();
	},

	showOptions: function (o) {
		var layoutChanged = false, unit = 'day';

		if (o != 'NONE') {
			this.hideSubPanels();
		}
		this.frequency = o;

		switch (o) {
			case 'DAILY':
				layoutChanged = this.showSubPanel(this.repeatEvery);
				layoutChanged |= this.showSubPanel(this.until);
				break;

			case 'WEEKLY':
				layoutChanged = this.showSubPanel(this.repeatEvery);
				layoutChanged |= this.showSubPanel(this.weekly);
				layoutChanged |= this.showSubPanel(this.until);
				unit = 'week';
				break;

			case 'MONTHLY':
				layoutChanged = this.showSubPanel(this.repeatEvery);
				layoutChanged |= this.showSubPanel(this.monthly);
				layoutChanged |= this.showSubPanel(this.until);
				unit = 'month';
				break;

			case 'YEARLY':
				layoutChanged = this.showSubPanel(this.repeatEvery);
				layoutChanged |= this.showSubPanel(this.yearly);
				layoutChanged |= this.showSubPanel(this.until);
				unit = 'year';
				break;

			default:
				// case NONE
				this.hideInnerCt();
				return;
		}

		if (layoutChanged) {
			this.innerCt.doLayout();
		}

		this.showInnerCt();
		this.repeatEvery.updateLabel(unit);
	},

	showSubPanel: function (p) {
		if (p.rendered) {
			p.show();
			return false;
		}
		else {
			if (this.repeatEvery.rendered) {
				// make sure weekly/monthly options show in the middle
				p = this.innerCt.insert(1, p);
			}
			else {
				p = this.innerCt.add(p);
			}
			p.show();
			return true;
		}
	},

	showInnerCt: function () {
		if (!this.innerCt.isVisible()) {
			if (this.enableFx && Ext6.enableFx) {
				this.innerCt.getPositionEl().slideIn('t', {
					duration: .3
				});
			}
			else {
				this.innerCt.show();
			}
		}
	},

	hideInnerCt: function () {
		if (this.innerCt.isVisible()) {
			if (this.enableFx && Ext6.enableFx) {
				this.innerCt.getPositionEl().slideOut('t', {
					duration: .3,
					easing: 'easeIn',
					callback: this.hideSubPanels,
					scope: this
				});
			}
			else {
				this.innerCt.hide();
				this.hideSubPanels();
			}
		}
	},

	setStartDate: function (dt) {
		this.items.each(function (p) {
			p.setStartDate(dt);
		});
	},

	getValue: function () {
		if (!this.rendered) {
			return this.value;
		}
		if (this.frequency == 'NONE') {
			return '';
		}
		var value = 'FREQ=' + this.frequency;
		this.items.each(function (p) {
			if (p.isVisible()) {
				value += p.getValue();
			}
		});
		return value;
	},

	setValue: function (v) {
		this.value = v;

		if (v == null || v == '' || v == 'NONE') {
			this.frequencyCombo.setValue('NONE');
			this.showOptions('NONE');
			return this;
		}
		var parts = v.split(';');
		this.items.each(function (p) {
			p.setValue(parts);
		});
		Ext6.each(parts, function (p) {
			if (p.indexOf('FREQ') > -1) {
				var freq = p.split('=')[1];
				this.frequencyCombo.setValue(freq);
				this.showOptions(freq);
				return;
			}
		}, this);

		return this;
	},

	hideSubPanels: function () {
		this.items.each(function (p) {
			p.hide();
		});
	},

	initSubComponents: function () {
		Extensible.calendar.recurrenceBase = Ext6.extend(Ext6.Container, {
			fieldLabel: ' ',
			labelSeparator: '',
			hideLabel: true,
			layout: 'table',
			anchor: '100%',
			startDate: this.startDate,

			//TODO: This is not I18N-able:
			getSuffix: function (n) {
				if (!Ext6.isNumber(n)) {
					return '';
				}
				switch (n) {
					case 1:
					case 21:
					case 31:
						return "st";
					case 2:
					case 22:
						return "nd";
					case 3:
					case 23:
						return "rd";
					default:
						return "th";
				}
			},

			//shared by monthly and yearly components:
			initNthCombo: function (cbo) {
				var cbo = Ext6.getCmp(this.id + '-combo'),
					dt = this.startDate,
					store = cbo.getStore(),
					last = dt.getLastDateOfMonth().getDate(),
					dayNum = dt.getDate(),
					nthDate = Ext6.Date.format(dt, 'jS') + ' day',
					isYearly = this.id.indexOf('-yearly') > -1,
					yearlyText = ' in ' + Ext6.Date.format(dt, 'F'),
					nthDayNum, nthDay, lastDay, lastDate, idx, data, s;

				nthDayNum = Math.ceil(dayNum / 7);
				nthDay = nthDayNum + this.getSuffix(nthDayNum) + Ext6.Date.format(dt, ' l');
				if (isYearly) {
					nthDate += yearlyText;
					nthDay += yearlyText;
				}
				data = [[nthDate], [nthDay]];

				s = isYearly ? yearlyText : '';
				if (last - dayNum < 7) {
					data.push(['last ' + Ext6.Date.format(dt, 'l') + s]);
				}
				if (last == dayNum) {
					data.push(['last day' + s]);
				}

				idx = store.find('field1', cbo.getValue());
				store.removeAll();
				cbo.clearValue();
				store.loadData(data);

				if (idx > data.length - 1) {
					idx = data.length - 1;
				}
				cbo.setValue(store.getAt(idx > -1 ? idx : 0).data.field1);
				return this;
			},
			setValue: Ext6.emptyFn
		});

		this.repeatEvery = new Extensible.calendar.recurrenceBase({
			id: this.id + '-every',
			layoutConfig: {
				columns: 3
			},
			items: [{
				xtype: 'label',
				text: 'Repeat every'
			}, {
				xtype: 'numberfield',
				id: this.id + '-every-num',
				value: 1,
				width: 35,
				minValue: 1,
				maxValue: 99,
				allowBlank: false,
				enableKeyEvents: true,
				listeners: {
					'keyup': {
						fn: function () {
							this.repeatEvery.updateLabel();
						},
						scope: this
					}
				}
			}, {
				xtype: 'label',
				id: this.id + '-every-label'
			}],
			setStartDate: function (dt) {
				this.startDate = dt;
				this.updateLabel();
				return this;
			},
			getValue: function () {
				var v = Ext6.getCmp(this.id + '-num').getValue();
				return v > 1 ? ';INTERVAL=' + v : '';
			},
			setValue: function (v) {
				var set = false,
					parts = Ext6.isArray(v) ? v : v.split(';');

				Ext6.each(parts, function (p) {
					if (p.indexOf('INTERVAL') > -1) {
						var interval = p.split('=')[1];
						Ext6.getCmp(this.id + '-num').setValue(interval);
					}
				}, this);
				return this;
			},
			updateLabel: function (type) {
				if (this.rendered) {
					var s = Ext6.getCmp(this.id + '-num').getValue() == 1 ? '' : 's';
					this.type = type ? type.toLowerCase() : this.type || 'day';
					var lbl = Ext6.getCmp(this.id + '-label');
					if (lbl.rendered) {
						lbl.update(this.type + s + ' beginning ' + Ext6.Date.format(this.startDate, 'l, F j'));
					}
				}
				return this;
			},
			afterRender: function () {
				this.callParent(arguments);
				this.updateLabel();
			}
		});

		this.weekly = new Extensible.calendar.recurrenceBase({
			id: this.id + '-weekly',
			layoutConfig: {
				columns: 2
			},
			items: [{
				xtype: 'label',
				text: 'on:'
			}, {
				xtype: 'checkboxgroup',
				id: this.id + '-weekly-days',
				items: [
					{boxLabel: 'Sun', name: 'SU', id: this.id + '-weekly-SU'},
					{boxLabel: 'Mon', name: 'MO', id: this.id + '-weekly-MO'},
					{boxLabel: 'Tue', name: 'TU', id: this.id + '-weekly-TU'},
					{boxLabel: 'Wed', name: 'WE', id: this.id + '-weekly-WE'},
					{boxLabel: 'Thu', name: 'TH', id: this.id + '-weekly-TH'},
					{boxLabel: 'Fri', name: 'FR', id: this.id + '-weekly-FR'},
					{boxLabel: 'Sat', name: 'SA', id: this.id + '-weekly-SA'}
				]
			}],
			setStartDate: function (dt) {
				this.startDate = dt;
				this.selectToday();
				return this;
			},
			selectToday: function () {
				this.clearValue();
				var day = Ext6.Date.format(this.startDate, 'D').substring(0, 2).toUpperCase();
				Ext6.getCmp(this.id + '-days').setValue(day, true);
			},
			clearValue: function () {
				Ext6.getCmp(this.id + '-days').setValue([false, false, false, false, false, false, false]);
			},
			getValue: function () {
				var v = '', sel = Ext6.getCmp(this.id + '-days').getValue();
				Ext6.each(sel, function (chk) {
					if (v.length > 0) {
						v += ',';
					}
					v += chk.name;
				});
				var day = Ext6.Date.format(this.startDate, 'D').substring(0, 2).toUpperCase();
				return v.length > 0 && v != day ? ';BYDAY=' + v : '';
			},
			setValue: function (v) {
				var set = false,
					parts = Ext6.isArray(v) ? v : v.split(';');

				this.clearValue();

				Ext6.each(parts, function (p) {
					if (p.indexOf('BYDAY') > -1) {
						var days = p.split('=')[1].split(','),
							vals = {};

						Ext6.each(days, function (d) {
							vals[d] = true;
						}, this);

						Ext6.getCmp(this.id + '-days').setValue(vals);
						return set = true;
					}
				}, this);

				if (!set) {
					this.selectToday();
				}
				return this;
			}
		});

		this.monthly = new Extensible.calendar.recurrenceBase({
			id: this.id + '-monthly',
			layoutConfig: {
				columns: 3
			},
			items: [{
				xtype: 'label',
				text: 'on the'
			}, {
				xtype: 'combo',
				id: this.id + '-monthly-combo',
				mode: 'local',
				width: 150,
				triggerAction: 'all',
				forceSelection: true,
				store: []
			}, {
				xtype: 'label',
				text: 'of each month'
			}],
			setStartDate: function (dt) {
				this.startDate = dt;
				this.initNthCombo();
				return this;
			},
			getValue: function () {
				var cbo = Ext6.getCmp(this.id + '-combo'),
					store = cbo.getStore(),
					idx = store.find('field1', cbo.getValue()),
					dt = this.startDate,
					day = Ext6.Date.format(dt, 'D').substring(0, 2).toUpperCase();

				if (idx > -1) {
					switch (idx) {
						case 0:
							return ';BYMONTHDAY=' + Ext6.Date.format(dt, 'j');
						case 1:
							return ';BYDAY=' + cbo.getValue()[0].substring(0, 1) + day;
						case 2:
							return ';BYDAY=-1' + day;
						default:
							return ';BYMONTHDAY=-1';
					}
				}
				return '';
			}
		});

		this.yearly = new Extensible.calendar.recurrenceBase({
			id: this.id + '-yearly',
			layoutConfig: {
				columns: 3
			},
			items: [{
				xtype: 'label',
				text: 'on the'
			}, {
				xtype: 'combo',
				id: this.id + '-yearly-combo',
				mode: 'local',
				width: 170,
				triggerAction: 'all',
				forceSelection: true,
				store: []
			}, {
				xtype: 'label',
				text: 'each year'
			}],
			setStartDate: function (dt) {
				this.startDate = dt;
				this.initNthCombo();
				return this;
			},
			getValue: function () {
				var cbo = Ext6.getCmp(this.id + '-combo'),
					store = cbo.getStore(),
					idx = store.find('field1', cbo.getValue()),
					dt = this.startDate,
					day = Ext6.Date.format(dt, 'D').substring(0, 2).toUpperCase(),
					byMonth = ';BYMONTH=' + dt.format('n');

				if (idx > -1) {
					switch (idx) {
						case 0:
							return byMonth;
						case 1:
							return byMonth + ';BYDAY=' + cbo.getValue()[0].substring(0, 1) + day;
						case 2:
							return byMonth + ';BYDAY=-1' + day;
						default:
							return byMonth + ';BYMONTHDAY=-1';
					}
				}
				return '';
			}
		});

		this.until = new Extensible.calendar.recurrenceBase({
			id: this.id + '-until',
			untilDateFormat: 'Ymd\\T000000\\Z',
			layoutConfig: {
				columns: 5
			},
			items: [{
				xtype: 'label',
				text: 'and continuing'
			}, {
				xtype: 'combo',
				id: this.id + '-until-combo',
				mode: 'local',
				width: 85,
				triggerAction: 'all',
				forceSelection: true,
				value: 'forever',
				store: ['forever', 'for', 'until'],
				listeners: {
					'select': {
						fn: function (cbo, rec) {
							var dt = Ext6.getCmp(this.id + '-until-date');
							if (rec.data.field1 == 'until') {
								dt.show();
								if (dt.getValue() == '') {
									dt.setValue(this.startDate.add(Date.DAY, 5));
									dt.setMinValue(this.startDate.clone().add(Date.DAY, 1));
								}
							}
							else {
								dt.hide();
							}
							if (rec.data.field1 == 'for') {
								Ext6.getCmp(this.id + '-until-num').show();
								Ext6.getCmp(this.id + '-until-endlabel').show();
							}
							else {
								Ext6.getCmp(this.id + '-until-num').hide();
								Ext6.getCmp(this.id + '-until-endlabel').hide();
							}
						},
						scope: this
					}
				}
			}, {
				xtype: 'datefield',
				id: this.id + '-until-date',
				showToday: false,
				hidden: true
			}, {
				xtype: 'numberfield',
				id: this.id + '-until-num',
				value: 5,
				width: 35,
				minValue: 1,
				maxValue: 99,
				allowBlank: false,
				hidden: true
			}, {
				xtype: 'label',
				id: this.id + '-until-endlabel',
				text: 'occurrences',
				hidden: true
			}],
			setStartDate: function (dt) {
				this.startDate = dt;
				return this;
			},
			getValue: function () {
				var dt = Ext6.getCmp(this.id + '-date');
				if (dt.isVisible()) {
					return ';UNTIL=' + Ext6.String.format(dt.getValue(), this.untilDateFormat);
				}
				var ct = Ext6.getCmp(this.id + '-num');
				if (ct.isVisible()) {
					return ';COUNT=' + ct.getValue();
				}
				return '';
			},
			setValue: function (v) {
				var set = false,
					parts = Ext6.isArray(v) ? v : v.split(';');

				Ext6.each(parts, function (p) {
					if (p.indexOf('COUNT') > -1) {
						var count = p.split('=')[1];
						Ext6.getCmp(this.id + '-combo').setValue('for');
						Ext6.getCmp(this.id + '-num').setValue(count).show();
						Ext6.getCmp(this.id + '-endlabel').show();
					}
					else if (p.indexOf('UNTIL') > -1) {
						var dt = p.split('=')[1];
						Ext6.getCmp(this.id + '-combo').setValue('until');
						Ext6.getCmp(this.id + '-date').setValue(Date.parseDate(dt, this.untilDateFormat)).show();
						Ext6.getCmp(this.id + '-endlabel').hide();
					}
				}, this);
				return this;
			}
		});

		return [this.repeatEvery, this.weekly, this.monthly, this.yearly, this.until];
	}
});// Not currently used
/*
 * @class Extensible.form.field.DateRangeLayout
 * @extends Ext6.layout.container.Container
 * @markdown
 * @private
 */
Ext6.define('Extensible.form.field.DateRangeLayout', {
	extend: 'Ext6.layout.container.Container',
	alias: ['layout.extensible.daterange'],

	onLayout: function () {
		var me = this,
			shadowCt = me.getShadowCt(),
			owner = me.owner,
			singleLine = owner.isSingleLine();

		me.owner.suspendLayout = true;

		if (singleLine) {
			shadowCt.getComponent('row1').add(owner.startDate, owner.startTime, owner.toLabel,
				owner.endTime, owner.endDate, owner.allDay);
		}
		else {
			shadowCt.getComponent('row1').add(owner.startDate, owner.startTime, owner.toLabel);
			shadowCt.getComponent('row2').add(owner.endDate, owner.endTime, owner.allDay);
		}

		if (!shadowCt.rendered) {
			shadowCt.render(me.getRenderTarget());
		}

		// shadowCt.doComponentLayout();
		owner.setHeight(shadowCt.getHeight() - 5);

		delete me.owner.suspendLayout;
	},

	/**
	 * @private
	 * Creates and returns the shadow vbox container that will be used to arrange the owner's items
	 */
	getShadowCt: function () {
		var me = this,
			items = [];

		if (!me.shadowCt) {
			me.shadowCt = Ext6.createWidget('container', {
				layout: 'auto',
				anchor: '100%',
				ownerCt: me.owner,
				items: [{
					xtype: 'container',
					itemId: 'row1',
					layout: 'hbox',
					defaults: {
						margins: '0 5 0 0'
					}
				}, {
					xtype: 'container',
					itemId: 'row2',
					layout: 'hbox',
					defaults: {
						margins: '0 5 0 0'
					}
				}]
			});
		}

		return me.shadowCt;
	},

	// We don't want to render any items to the owner directly, that gets handled by each column's own layout
	renderItems: Ext6.emptyFn
});
/**
 * @class Extensible.form.field.DateRange
 * @extends Ext6.form.Field
 * <p>A combination field that includes start and end dates and times, as well as an optional all-day checkbox.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.form.field.DateRange', {
	extend: 'Ext6.form.FieldContainer',
	alias: 'widget.extensible.daterangefield',

	requires: [
		'Ext6.form.field.Date',
		'Ext6.form.field.Time',
		'Ext6.form.Label',
		'Ext6.form.field.Checkbox'
	],

	/**
	 * @cfg {String} toText
	 * The text to display in between the date/time fields (defaults to 'to')
	 */
	toText: 'to',
	/**
	 * @cfg {String} allDayText
	 * The text to display as the label for the all day checkbox (defaults to 'All day')
	 */
	allDayText: 'All day',
	/**
	 * @cfg {String/Boolean} singleLine
	 * <code>true</code> to render the fields all on one line, <code>false</code> to break the start
	 * date/time and end date/time into two stacked rows of fields to preserve horizontal space
	 * (defaults to <code>true</code>).
	 */
	singleLine: true,
	/*
	 * @cfg {Number} singleLineMinWidth -- not currently used
	 * If {@link singleLine} is set to 'auto' it will use this value to determine whether to render the field on one
	 * line or two. This value is the approximate minimum width required to render the field on a single line, so if
	 * the field's container is narrower than this value it will automatically be rendered on two lines.
	 */
	//singleLineMinWidth: 490,
	/**
	 * @cfg {String} dateFormat
	 * The date display format used by the date fields (defaults to 'n/j/Y')
	 */
	dateFormat: 'd.m.Y',

	// private
	fieldLayout: {
		type: 'hbox',
		defaultMargins: {top: 0, right: 5, bottom: 0, left: 0}
	},

	// private
	initComponent: function () {
		var me = this;
		/**
		 * @cfg {String} timeFormat
		 * The time display format used by the time fields. By default the DateRange uses the
		 * {@link Extensible.Date.use24HourTime} setting and sets the format to 'g:i A' for 12-hour time (e.g., 1:30 PM)
		 * or 'G:i' for 24-hour time (e.g., 13:30). This can also be overridden by a static format string if desired.
		 */
		me.timeFormat = me.timeFormat || 'G:i';

		me.addCls('ext-dt-range');

		if (me.singleLine) {
			me.layout = me.fieldLayout;
			me.items = me.getFieldConfigs();
		}
		else {
			me.items = [{
				xtype: 'container',
				layout: me.fieldLayout,
				items: [
					me.getStartDateConfig(),
					me.getStartTimeConfig(),
					me.getDateSeparatorConfig()
				]
			}, {
				xtype: 'container',
				layout: me.fieldLayout,
				items: [
					me.getEndDateConfig(),
					me.getEndTimeConfig(),
					me.getAllDayConfig()
				]
			}];
		}

		me.callParent(arguments);
		me.initRefs();
	},

	initRefs: function () {
		var me = this;
		me.startDate = me.down('#' + me.id + '-start-date');
		me.startTime = me.down('#' + me.id + '-start-time');
		me.endTime = me.down('#' + me.id + '-end-time');
		me.endDate = me.down('#' + me.id + '-end-date');
		me.allDay = me.down('#' + me.id + '-allday');
		me.toLabel = me.down('#' + me.id + '-to-label');
	},

	getFieldConfigs: function () {
		var me = this;
		return [
			me.getStartDateConfig(),
			me.getStartTimeConfig(),
			me.getDateSeparatorConfig(),
			me.getEndTimeConfig(),
			me.getEndDateConfig(),
			me.getAllDayConfig()
		];
	},

	getLayoutItems: function (singleLine) {
		var me = this;
		return singleLine ? me.items.items : [[
			me.startDate, me.startTime, me.toLabel
		], [
			me.endDate, me.endTime, me.allDay
		]];
	},

	getStartDateConfig: function () {
		return {
			xtype: 'datefield',
			id: this.id + '-start-date',
			format: this.dateFormat,
			width: 100,
			listeners: {
				'change': {
					fn: function () {
						this.onFieldChange('date', 'start');
					},
					scope: this
				}
			}
		};
	},

	getStartTimeConfig: function () {
		return {
			xtype: 'timefield',
			id: this.id + '-start-time',
			hidden: this.showTimes === false,
			labelWidth: 0,
			hideLabel: true,
			width: 90,
			format: this.timeFormat,
			listeners: {
				'select': {
					fn: function () {
						this.onFieldChange('time', 'start');
					},
					scope: this
				}
			}
		};
	},

	getEndDateConfig: function () {
		return {
			xtype: 'datefield',
			id: this.id + '-end-date',
			format: this.dateFormat,
			hideLabel: true,
			width: 100,
			listeners: {
				'change': {
					fn: function () {
						this.onFieldChange('date', 'end');
					},
					scope: this
				}
			}
		};
	},

	getEndTimeConfig: function () {
		return {
			xtype: 'timefield',
			id: this.id + '-end-time',
			hidden: this.showTimes === false,
			labelWidth: 0,
			hideLabel: true,
			width: 90,
			format: this.timeFormat,
			listeners: {
				'select': {
					fn: function () {
						this.onFieldChange('time', 'end');
					},
					scope: this
				}
			}
		};
	},

	getAllDayConfig: function () {
		return {
			xtype: 'checkbox',
			id: this.id + '-allday',
			hidden: this.showTimes === false || this.showAllDay === false,
			boxLabel: this.allDayText,
			margins: {top: 2, right: 5, bottom: 0, left: 0},
			handler: this.onAllDayChange,
			scope: this
		};
	},

	onAllDayChange: function (chk, checked) {
		this.startTime.setVisible(!checked);
		this.endTime.setVisible(!checked);
	},

	getDateSeparatorConfig: function () {
		return {
			xtype: 'label',
			id: this.id + '-to-label',
			text: this.toText,
			margins: {top: 4, right: 5, bottom: 0, left: 0}
		};
	},

	isSingleLine: function () {
		var me = this;

		if (me.calculatedSingleLine === undefined) {
			if (me.singleLine == 'auto') {
				var ownerCtEl = me.ownerCt.getEl(),
					w = me.ownerCt.getWidth() - ownerCtEl.getPadding('lr'),
					el = ownerCtEl.down('.x6-panel-body');

				if (el) {
					w -= el.getPadding('lr');
				}

				el = ownerCtEl.down('.x6-form-item-label')
				if (el) {
					w -= el.getWidth() - el.getPadding('lr');
				}
				singleLine = w <= me.singleLineMinWidth ? false : true;
			}
			else {
				me.calculatedSingleLine = me.singleLine !== undefined ? me.singleLine : true;
			}
		}
		return me.calculatedSingleLine;
	},

	// private
	onFieldChange: function (type, startend) {
		this.checkDates(type, startend);
		this.fireEvent('change', this, this.getValue());
	},

	// private
	checkDates: function (type, startend) {
		var me = this,
			typeCap = type === 'date' ? 'Date' : 'Time',
			startField = this['start' + typeCap],
			endField = this['end' + typeCap],
			startValue = me.getDT('start'),
			endValue = me.getDT('end');

		if (startValue > endValue) {
			if (startend == 'start') {
				endField.setValue(startValue);
			} else {
				startField.setValue(endValue);
				me.checkDates(type, 'start');
			}
		}
		if (type == 'date') {
			me.checkDates('time', startend);
		}
	},

	/**
	 * Returns an array containing the following values in order:<div class="mdetail-params"><ul>
	 * <li><b><code>DateTime</code></b> : <div class="sub-desc">The start date/time</div></li>
	 * <li><b><code>DateTime</code></b> : <div class="sub-desc">The end date/time</div></li>
	 * <li><b><code>Boolean</code></b> : <div class="sub-desc">True if the dates are all-day, false
	 * if the time values should be used</div></li><ul></div>
	 * @return {Array} The array of return values
	 */
	getValue: function () {
		return [
			this.getDT('start'),
			this.getDT('end'),
			this.allDay.getValue()
		];
	},

	// private getValue helper
	getDT: function (startend) {
		var time = this[startend + 'Time'].getValue(),
			dt = this[startend + 'Date'].getValue();

		if (Ext6.isDate(dt)) {
			dt = Ext6.Date.format(dt, this[startend + 'Date'].format);
		}
		else {
			return null;
		}
		;
		if (time && time != '') {
			time = Ext6.Date.format(time, this[startend + 'Time'].format);
			var val = Ext6.Date.parseDate(dt + ' ' + time, this[startend + 'Date'].format + ' ' + this[startend + 'Time'].format);
			return val;
			//return Ext6.Date.parseDate(dt+' '+time, this[startend+'Date'].format+' '+this[startend+'Time'].format);
		}
		return Ext6.Date.parseDate(dt, this[startend + 'Date'].format);

	},

	/**
	 * Sets the values to use in the date range.
	 * @param {Array/Date/Object} v The value(s) to set into the field. Valid types are as follows:<div class="mdetail-params"><ul>
	 * <li><b><code>Array</code></b> : <div class="sub-desc">An array containing, in order, a start date, end date and all-day flag.
	 * This array should exactly match the return type as specified by {@link #getValue}.</div></li>
	 * <li><b><code>DateTime</code></b> : <div class="sub-desc">A single Date object, which will be used for both the start and
	 * end dates in the range.  The all-day flag will be defaulted to false.</div></li>
	 * <li><b><code>Object</code></b> : <div class="sub-desc">An object containing properties for StartDate, EndDate and IsAllDay
	 * as defined in {@link Extensible.calendar.data.EventMappings}.</div></li><ul></div>
	 */
	setValue: function (v) {
		if (!v) {
			return;
		}
		var me = this,
			eventMappings = Extensible.calendar.data.EventMappings,
			startDateName = eventMappings.StartDate.name;

		if (Ext6.isArray(v)) {
			me.setDT(v[0], 'start');
			me.setDT(v[1], 'end');
			me.allDay.setValue(!!v[2]);
		}
		else if (Ext6.isDate(v)) {
			me.setDT(v, 'start');
			me.setDT(v, 'end');
			me.allDay.setValue(false);
		}
		else if (v[startDateName]) { //object
			me.setDT(v[startDateName], 'start');
			if (!me.setDT(v[eventMappings.EndDate.name], 'end')) {
				me.setDT(v[startDateName], 'end');
			}
			me.allDay.setValue(!!v[eventMappings.IsAllDay.name]);
		}
	},

	// private setValue helper
	setDT: function (dt, startend) {
		if (dt && Ext6.isDate(dt)) {
			this[startend + 'Date'].setValue(dt);
			this[startend + 'Time'].setValue(Ext6.Date.format(dt, this[startend + 'Time'].format));
			return true;
		}
	},

	// inherited docs
	isDirty: function () {
		var dirty = false;
		if (this.rendered && !this.disabled) {
			this.items.each(function (item) {
				if (item.isDirty()) {
					dirty = true;
					return false;
				}
			});
		}
		return dirty;
	},

	// inherited docs
	reset: function () {
		this.delegateFn('reset');
	},

	// private
	delegateFn: function (fn) {
		this.items.each(function (item) {
			if (item[fn]) {
				item[fn]();
			}
		});
	},

	// private
	beforeDestroy: function () {
		Ext6.destroy(this.fieldCt);
		this.callParent(arguments);
	},

	/**
	 * @method getRawValue
	 * @hide
	 */
	getRawValue: Ext6.emptyFn,
	/**
	 * @method setRawValue
	 * @hide
	 */
	setRawValue: Ext6.emptyFn
});/**
 * @class Extensible.calendar.util.ColorPicker
 * @extends Ext6.picker.Color
 * Simple color picker class for choosing colors specifically for calendars. This is a lightly modified version
 * of the default Ext color picker that is based on calendar ids rather than hex color codes so that the colors
 * can be easily modified via CSS and automatically applied to calendars. The specific colors used by default are
 * also chosen to provide good color contrast when displayed in calendars.
 </code></pre>
 * @constructor
 * Create a new color picker
 * @param {Object} config The config object
 * @xtype extensible.calendarcolorpicker
 */
Ext6.define('Extensible.calendar.util.ColorPicker', {
	extend: 'Ext6.picker.Color',
	alias: 'widget.extensible.calendarcolorpicker',

	requires: ['Ext6.XTemplate'],

	// private
	colorCount: 32,

	/**
	 * @cfg {Function} handler
	 * Optional. A function that will handle the select event of this color picker.
	 * The handler is passed the following parameters:<div class="mdetail-params"><ul>
	 * <li><code>picker</code> : ColorPicker<div class="sub-desc">The picker instance.</div></li>
	 * <li><code>colorId</code> : String<div class="sub-desc">The id that identifies the selected color and relates it to a calendar.</div></li>
	 * </ul></div>
	 */

	constructor: function () {
		this.renderTpl = [
			'<tpl for="colors">',
			'<a href="#" class="x-cal-{.}" hidefocus="on">',
			'<em><span unselectable="on">&#160;</span></em>',
			'</a>',
			'</tpl>'
		];
		this.callParent(arguments);
	},

	// private
	initComponent: function () {
		this.callParent(arguments);

		this.addCls('x-calendar-palette');

		if (this.handler) {
			this.on('select', this.handler, this.scope || this, {
				delegate: 'a'
			});
		}

		this.colors = [];
		for (var i = 1; i <= this.colorCount; i++) {
			this.colors.push(i);
		}
	},

	// private
	handleClick: function (e, t) {
		e.preventDefault();

		var colorId = t.className.split('x-cal-')[1];
		this.select(colorId);
	},

	/**
	 * Selects the specified color in the palette (fires the {@link #select} event)
	 * @param {Number} colorId The id that identifies the selected color and relates it to a calendar
	 * @param {Boolean} suppressEvent (optional) True to stop the select event from firing. Defaults to <tt>false</tt>.
	 */
	select: function (colorId, suppressEvent) {
		var me = this,
			selectedCls = me.selectedCls,
			value = me.value;

		if (!me.rendered) {
			me.value = colorId;
			return;
		}

		if (colorId != value || me.allowReselect) {
			var el = me.el;

			if (me.value) {
				el.down('.x6-cal-' + value).removeCls(selectedCls);
			}
			el.down('.x6-cal-' + colorId).addCls(selectedCls);
			me.value = colorId;

			if (suppressEvent !== true) {
				me.fireEvent('select', me, colorId);
			}
		}
	}
});
/**
 * @private
 * @class Extensible.calendar.gadget.CalendarListMenu
 * @extends Ext6.menu.Menu
 * <p>A menu containing a {@link Extensible.calendar.util.ColorPicker color picker} for choosing calendar colors,
 * as well as other calendar-specific options.</p>
 * @xtype extensible.calendarlistmenu
 */
Ext6.define('Extensible.calendar.gadget.CalendarListMenu', {
	extend: 'Ext6.menu.Menu',
	alias: 'widget.extensible.calendarlistmenu',

	requires: ['Extensible.calendar.util.ColorPicker'],

	/**
	 * @cfg {Boolean} hideOnClick
	 * False to continue showing the menu after a color is selected, defaults to true.
	 */
	hideOnClick: true,
	/**
	 * @cfg {Boolean} ignoreParentClicks
	 * True to ignore clicks on any item in this menu that is a parent item (displays a submenu)
	 * so that the submenu is not dismissed when clicking the parent item (defaults to true).
	 */
	ignoreParentClicks: true,
	/**
	 * @cfg {String} displayOnlyThisCalendarText
	 * The text to display for the 'Display only this calendar' option in the menu.
	 */
	displayOnlyThisCalendarText: 'Display only this calendar',
	/**
	 * @cfg {Number} Resource_id
	 * The id of the calendar to be associated with this menu. This Resource_id will be passed
	 * back with any events from this menu to identify the calendar to be acted upon. The calendar
	 * id can also be changed at any time after creation by calling {@link setCalendar}.
	 */

	/**
	 * @cfg {Boolean} enableScrolling
	 * @hide
	 */
	enableScrolling: false,
	/**
	 * @cfg {Number} maxHeight
	 * @hide
	 */
	/**
	 * @cfg {Number} scrollIncrement
	 * @hide
	 */
	/**
	 * @event click
	 * @hide
	 */
	/**
	 * @event itemclick
	 * @hide
	 */

	/**
	 * @property palette
	 * @type ColorPicker
	 * The {@link Extensible.calendar.util.ColorPicker ColorPicker} instance for this CalendarListMenu
	 */

	// private
	initComponent: function () {
		Ext6.apply(this, {
			plain: true,
			items: [{
				text: this.displayOnlyThisCalendarText,
				iconCls: 'extensible-cal-icon-cal-show',
				handler: Ext6.bind(this.handleRadioCalendarClick, this)
			}, '-', {
				xtype: 'extensible.calendarcolorpicker',
				id: this.id + '-calendar-color-picker',
				handler: Ext6.bind(this.handleColorSelect, this)
			}]
		});

		this.addClass('x-calendar-list-menu');
		this.callParent(arguments);
	},

	// private
	afterRender: function () {
		this.callParent(arguments);

		this.palette = this.down('#' + this.id + '-calendar-color-picker');

		if (this.colorId) {
			this.palette.select(this.colorId, true);
		}
	},

	// private
	handleRadioCalendarClick: function (e, t) {
		this.fireEvent('radiocalendar', this, this.Resource_id);
	},

	// private
	handleColorSelect: function (cp, selColorId) {
		this.fireEvent('colorchange', this, this.Resource_id, selColorId, this.colorId);
		this.colorId = selColorId;
		this.menuHide();
	},

	/**
	 * Sets the calendar id and color id to be associated with this menu. This should be called each time the
	 * menu is shown relative to a new calendar.
	 * @param {Number} Resource_id The id of the calendar to be associated
	 * @param {Number} colorId The id of the color to be pre-selected in the color palette
	 * @return {Extensible.calendar.gadget.CalendarListMenu} this
	 */
	setCalendar: function (id, cid) {
		this.Resource_id = id;
		this.colorId = cid;

		if (this.rendered) {
			this.palette.select(cid, true);
		}
		return this;
	},

	// private
	menuHide: function () {
		if (this.hideOnClick) {
			this.hide();
		}
	}
});
/**
 * @class Extensible.calendar.menu.Event
 * @extends Ext6.menu.Menu
 * The context menu displayed for calendar events in any {@link Extensible.calendar.view.AbstractCalendar CalendarView} subclass.
 * @xtype extensible.eventcontextmenu
 */
Ext6.define('Extensible.calendar.menu.Event', {
	extend: 'Ext6.menu.Menu',
	alias: 'widget.extensible.eventcontextmenu',

	requires: ['Ext6.menu.DatePicker'],

	/**
	 * @cfg {Boolean} hideOnClick
	 * False to continue showing the menu after a color is selected, defaults to true.
	 */
	hideOnClick: true,
	/**
	 * @cfg {Boolean} ignoreParentClicks
	 * True to ignore clicks on any item in this menu that is a parent item (displays a submenu)
	 * so that the submenu is not dismissed when clicking the parent item (defaults to true).
	 */
	ignoreParentClicks: true,

	/**
	 * @cfg {Boolean} enableScrolling
	 * @hide
	 */
	enableScrolling: false,
	/**
	 * @cfg {Number} maxHeight
	 * @hide
	 */
	/**
	 * @cfg {Number} scrollIncrement
	 * @hide
	 */
	/**
	 * @event click
	 * @hide
	 */
	/**
	 * @event itemclick
	 * @hide
	 */

	// private
	initComponent: function () {
		this.buildMenu();
		this.callParent(arguments);
	},

	/**
	 * Overrideable method intended for customizing the menu items. This should only to be used for overriding
	 * or called from a subclass and should not be called directly from application code.
	 */
	buildMenu: function () {
		if (this.rendered) {
			return;
		}
		this.dateMenu = Ext6.create('Ext6.menu.DatePicker', {
			scope: this,
			handler: function (dp, dt) {
				dt = Extensible.Date.copyTime(this.rec.data[Extensible.calendar.data.EventMappings.StartDate.name], dt);
				this.fireEvent('eventmove', this, this.rec, dt);
			}
		});

		Ext6.apply(this, {
			items: [{
				text: 'Планировать',
				disabled: !isUserGroup('operblock_head'),
				iconCls: 'extensible-cal-icon-evt-edit',
				scope: this,
				handler: function() {
					// открываем форму планирования
					var menu = this;
					getWnd('swEvnPrescrOperBlockPlanWindow').show({
						EvnDirection_id: menu.rec.data['EvnDirection_id'],
						callback: function() {
							// обновить гриды
							menu.ownerWin.doFilter();
						}
					});
				}
			}, {
				text: 'Результат',
				disabled: !isUserGroup('operblock_head') && !isUserGroup('operblock_surg'),
				scope: this,
				handler: function() {
					var menu = this;
					// пришлось сделать таймаут, иначе форма 2-го экста почему то уходит на задний план
					setTimeout(function(){
						// открываем форму ввода результата
						menu.ownerWin.showEvnUslugaOperEditWindow({
							EvnUslugaOper_id: menu.rec.data['EvnUslugaOper_id'],
							EvnUslugaOper_setDate: menu.rec.data['StartDate'],
							OperBrig: menu.rec.data['OperBrig']
						});
					}, 100);
				}
			}, {
				text: 'Отменить',
				disabled: !isUserGroup('operblock_head'),
				iconCls: 'extensible-cal-icon-evt-del',
				scope: this,
				handler: function () {
					var menu = this;
					Ext6.Msg.confirm('Вопрос','Вы действительно хотите отменить операцию?', function(btn) {
						if (btn == 'yes') {
							menu.fireEvent('eventdelete', menu, menu.rec, menu.ctxEl);
						}
					});
				}
			}, {
				text: 'Отменить выполнение',
				disabled: !isUserGroup('operblock_head'),
				iconCls: 'extensible-cal-icon-evt-del',
				scope: this,
				handler: function () {
					var menu = this;
					Ext6.Msg.confirm('Вопрос','Вы действительно хотите отменить выполнение операции?', function(btn) {
						if (btn == 'yes') {
							menu.fireEvent('eventdelete', menu, menu.rec, menu.ctxEl);
							menu.ownerWin.loadSchedulePanel();
						}
					});
				}
			}]
		});
	},

	/**
	 * Shows the specified event at the given XY position.
	 * @param {Extensible.calendar.data.EventModel} rec The {@link Extensible.calendar.data.EventModel record} for the event
	 * @param {Ext6.Element} el The element associated with this context menu
	 * @param {Array} xy The X & Y [x, y] values for the position at which to show the menu (coordinates are page-based)
	 */
	showForEvent: function (rec, el, xy) {
		this.rec = rec;
		this.ctxEl = el;
		this.dateMenu.picker.setValue(rec.data[Extensible.calendar.data.EventMappings.StartDate.name]);

		this.items.items[0].hide();
		this.items.items[1].hide();
		this.items.items[2].hide();
		this.items.items[3].hide();
		// в зависимости от выполненности разные кнопки доступны
		if (!Ext6.isEmpty(rec.data['EvnUslugaOper_setDT'])) {
			// выполненна
			this.items.items[1].show();
			this.items.items[3].show();
		} else {
			// запланированна
			this.items.items[0].show();
			this.items.items[1].show();
			this.items.items[2].show();
		}

		this.showAt(xy);
	},

	// private
	onHide: function () {
		this.callParent(arguments);
		delete this.ctxEl;
	}
});
/**
 * @class Extensible.calendar.view.AbstractCalendar
 * @extends Ext6.Component
 * <p>This is an abstract class that serves as the base for other calendar views. This class is not
 * intended to be directly instantiated.</p>
 * <p>When extending this class to create a custom calendar view, you must provide an implementation
 * for the <code>renderItems</code> method, as there is no default implementation for rendering events
 * The rendering logic is totally dependent on how the UI structures its data, which
 * is determined by the underlying UI template (this base class does not have a template).</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.calendar.view.AbstractCalendar', {
	extend: 'Ext6.Component',

	requires: [
		'Ext6.dom.CompositeElement'
	],

	requires: [
		'Extensible.calendar.menu.Event'
	],

	/**
	 * @cfg {Ext6.data.Store} eventStore
	 * The {@link Ext6.data.Store store} which is bound to this calendar and contains {@link Extensible.calendar.data.EventModel EventRecords}.
	 * Note that this is an alias to the default {@link #store} config (to differentiate that from the optional {@link #calendarStore}
	 * config), and either can be used interchangeably.
	 */
	/**
	 * @cfg {Ext6.data.Store} calendarStore
	 * The {@link Ext6.data.Store store} which is bound to this calendar and contains {@link Extensible.calendar.data.CalendarModel CalendarRecords}.
	 * This is an optional store that provides multi-calendar (and multi-color) support. If available an additional field for selecting the
	 * calendar in which to save an event will be shown in the edit forms. If this store is not available then all events will simply use
	 * the default calendar (and color).
	 */
	/*
	 * @cfg {Boolean} enableRecurrence
	 * True to show the recurrence field, false to hide it (default). Note that recurrence requires
	 * something on the server-side that can parse the iCal RRULE format in order to generate the
	 * instances of recurring events to display on the calendar, so this field should only be enabled
	 * if the server supports it.
	 */
	//enableRecurrence: false,
	/**
	 * @cfg {Boolean} readOnly
	 * True to prevent clicks on events or the view from providing CRUD capabilities, false to enable CRUD (the default).
	 */
	/**
	 * @cfg {Number} startDay
	 * The 0-based index for the day on which the calendar week begins (0=Sunday, which is the default)
	 */
	startDay: 0,
	/**
	 * @cfg {Boolean} spansHavePriority
	 * Allows switching between two different modes of rendering events that span multiple days. When true,
	 * span events are always sorted first, possibly at the expense of start dates being out of order (e.g.,
	 * a span event that starts at 11am one day and spans into the next day would display before a non-spanning
	 * event that starts at 10am, even though they would not be in date order). This can lead to more compact
	 * layouts when there are many overlapping events. If false (the default), events will always sort by start date
	 * first which can result in a less compact, but chronologically consistent layout.
	 */
	spansHavePriority: false,
	/**
	 * @cfg {Boolean} trackMouseOver
	 * Whether or not the view tracks and responds to the browser mouseover event on contained elements (defaults to
	 * true). If you don't need mouseover event highlighting you can disable this.
	 */
	trackMouseOver: true,
	/**
	 * @cfg {Boolean} enableFx
	 * Determines whether or not visual effects for CRUD actions are enabled (defaults to true). If this is false
	 * it will override any values for {@link #enableAddFx}, {@link #enableUpdateFx} or {@link enableRemoveFx} and
	 * all animations will be disabled.
	 */
	enableFx: true,
	/**
	 * @cfg {Boolean} enableAddFx
	 * True to enable a visual effect on adding a new event (the default), false to disable it. Note that if
	 * {@link #enableFx} is false it will override this value. The specific effect that runs is defined in the
	 * {@link #doAddFx} method.
	 */
	enableAddFx: true,
	/**
	 * @cfg {Boolean} enableUpdateFx
	 * True to enable a visual effect on updating an event, false to disable it (the default). Note that if
	 * {@link #enableFx} is false it will override this value. The specific effect that runs is defined in the
	 * {@link #doUpdateFx} method.
	 */
	enableUpdateFx: false,
	/**
	 * @cfg {Boolean} enableRemoveFx
	 * True to enable a visual effect on removing an event (the default), false to disable it. Note that if
	 * {@link #enableFx} is false it will override this value. The specific effect that runs is defined in the
	 * {@link #doRemoveFx} method.
	 */
	enableRemoveFx: true,
	/**
	 * @cfg {Boolean} enableDD
	 * True to enable drag and drop in the calendar view (the default), false to disable it
	 */
	enableDD: true,
	/**
	 * @cfg {Boolean} enableContextMenus
	 * True to enable automatic right-click context menu handling in the calendar views (the default), false to disable
	 * them. Different context menus are provided when clicking on events vs. the view background.
	 */
	enableContextMenus: true,
	/**
	 * @cfg {Boolean} suppressBrowserContextMenu
	 * When {@link #enableContextMenus} is true, the browser context menu will automatically be suppressed whenever a
	 * custom context menu is displayed. When this option is true, right-clicks on elements that do not have a custom
	 * context menu will also suppress the default browser context menu (no menu will be shown at all). When false,
	 * the browser context menu will still show if the right-clicked element has no custom menu (this is the default).
	 */
	suppressBrowserContextMenu: false,
	/**
	 * @cfg {Boolean} monitorResize
	 * True to monitor the browser's resize event (the default), false to ignore it. If the calendar view is rendered
	 * into a fixed-size container this can be set to false. However, if the view can change dimensions (e.g., it's in
	 * fit layout in a viewport or some other resizable container) it is very important that this config is true so that
	 * any resize event propagates properly to all subcomponents and layouts get recalculated properly.
	 */
	monitorResize: true,
	/**
	 * @cfg {String} ddCreateEventText
	 * The text to display inside the drag proxy while dragging over the calendar to create a new event (defaults to
	 * 'Create event for {0}' where {0} is a date range supplied by the view)
	 */
	ddCreateEventText: 'Create event for {0}',
	/**
	 * @cfg {String} ddMoveEventText
	 * The text to display inside the drag proxy while dragging an event to reposition it (defaults to
	 * 'Move event to {0}' where {0} is the updated event start date/time supplied by the view)
	 */
	ddMoveEventText: 'Запланировать операцию на {0}',
	/**
	 * @cfg {String} ddResizeEventText
	 * The string displayed to the user in the drag proxy while dragging the resize handle of an event (defaults to
	 * 'Update event to {0}' where {0} is the updated event start-end range supplied by the view). Note that
	 * this text is only used in views
	 * that allow resizing of events.
	 */
	ddResizeEventText: 'Запланировать операцию {0}',
	/**
	 * @cfg {String} defaultEventTitleText
	 * The default text to display as the title of an event that has a null or empty string title value (defaults to '(No title)')
	 */
	defaultEventTitleText: '(No title)',
	/**
	 * @cfg {String} dateParamStart
	 * The param name representing the start date of the current view range that's passed in requests to retrieve events
	 * when loading the view (defaults to 'startDate').
	 */
	dateParamStart: 'startDate',
	/**
	 * @cfg {String} dateParamEnd
	 * The param name representing the end date of the current view range that's passed in requests to retrieve events
	 * when loading the view (defaults to 'endDate').
	 */
	dateParamEnd: 'endDate',
	/**
	 * @cfg {String} dateParamFormat
	 * The format to use for date parameters sent with requests to retrieve events for the calendar (defaults to 'Y-m-d', e.g. '2010-10-31')
	 */
	dateParamFormat: 'Y-m-d',
	/**
	 * @cfg {Boolean} editModal
	 * True to show the default event editor window modally over the entire page, false to allow user interaction with the page
	 * while showing the window (the default). Note that if you replace the default editor window with some alternate component this
	 * config will no longer apply.
	 */
	editModal: false,
	/**
	 * @cfg {String} todayCls
	 * A CSS class to apply to the current date when it is visible in the current view (defaults to 'ext-cal-day-today' which
	 * highlights today in yellow). To disable this styling set the value to null or ''.
	 */
	todayCls: 'ext-cal-day-today',
	/**
	 * @cfg {String} hideMode
	 * <p>How this component should be hidden. Supported values are <tt>'visibility'</tt>
	 * (css visibility), <tt>'offsets'</tt> (negative offset position) and <tt>'display'</tt>
	 * (css display).</p>
	 * <br><p><b>Note</b>: For calendar views the default is 'offsets' rather than the Ext JS default of
	 * 'display' in order to preserve scroll position after hiding/showing a scrollable view like Day or Week.</p>
	 */
	hideMode: 'offsets',

	/**
	 * @property ownerCalendarPanel
	 * @type Extensible.calendar.CalendarPanel
	 * If this view is hosted inside a {@link Extensible.calendar.CalendarPanel CalendarPanel} this property will reference
	 * it. If the view was created directly outside of a CalendarPanel this property will be null. Read-only.
	 */

	//private properties -- do not override:
	eventSelector: '.ext-cal-evt',
	eventOverClass: 'ext-evt-over',
	eventElIdDelimiter: '-evt-',
	dayElIdDelimiter: '-day-',

	/**
	 * Returns a string of HTML template markup to be used as the body portion of the event template created
	 * by {@link #getEventTemplate}. This provides the flexibility to customize what's in the body without
	 * having to override the entire XTemplate. This string can include any valid {@link Ext6.Template} code, and
	 * any data tokens accessible to the containing event template can be referenced in this string.
	 * @return {String} The body template string
	 */
	getEventBodyMarkup: Ext6.emptyFn, // must be implemented by a subclass

	/**
	 * <p>Returns the XTemplate that is bound to the calendar's event store (it expects records of type
	 * {@link Extensible.calendar.data.EventModel}) to populate the calendar views with events. Internally this method
	 * by default generates different markup for browsers that support CSS border radius and those that don't.
	 * This method can be overridden as needed to customize the markup generated.</p>
	 * <p>Note that this method calls {@link #getEventBodyMarkup} to retrieve the body markup for events separately
	 * from the surrounding container markup.  This provides the flexibility to customize what's in the body without
	 * having to override the entire XTemplate. If you do override this method, you should make sure that your
	 * overridden version also does the same.</p>
	 * @return {Ext6.XTemplate} The event XTemplate
	 */
	getEventTemplate: Ext6.emptyFn, // must be implemented by a subclass

	/**
	 * This is undefined by default, but can be implemented to allow custom CSS classes and template data to be
	 * conditionally applied to events during rendering. This function will be called with the parameter list shown
	 * below and is expected to return the CSS class name (or empty string '' for none) that will be added to the
	 * event element's wrapping div. To apply multiple class names, simply return them space-delimited within the
	 * string (e.g., 'my-class another-class'). Example usage, applied in a CalendarPanel config:
	 * <pre><code>
	 // This example assumes a custom field of 'IsHoliday' has been added to EventRecord
	 viewConfig: {
    getEventClass: function(rec, allday, templateData, store){
        if(rec.data.IsHoliday){
            templateData.iconCls = 'holiday';
            return 'evt-holiday';
        }
        templateData.iconCls = 'plain';
        return '';
    },
    getEventBodyMarkup : function(){
        // This is simplified, but shows the symtax for how you could add a
        // custom placeholder that maps back to the templateData property created
        // in getEventClass. Note that this is standard Ext template syntax.
        if(!this.eventBodyMarkup){
            this.eventBodyMarkup = '&lt;span class="{iconCls}">&lt;/span> {Title}';
        }
        return this.eventBodyMarkup;
    }
}
	 </code></pre>
	 * @param {Extensible.calendar.data.EventModel} rec The {@link Extensible.calendar.data.EventModel record} being rendered
	 * @param {Boolean} isAllDay A flag indicating whether the event will be <em>rendered</em> as an all-day event. Note that this
	 * will not necessarily correspond with the value of the <tt>EventRecord.IsAllDay</tt> field &mdash; events that span multiple
	 * days will be rendered using the all-day event template regardless of the field value. If your logic for this function
	 * needs to know whether or not the event will be rendered as an all-day event, this value should be used.
	 * @param {Object} templateData A plain JavaScript object that is empty by default. You can add custom properties
	 * to this object that will then be passed into the event template for the specific event being rendered. If you have
	 * overridden the default event template and added custom data placeholders, you can use this object to pass the data
	 * into the template that will replace those placeholders.
	 * @param {Ext6.data.Store} store The Event data store in use by the view
	 * @method getEventClass
	 * @return {String} A space-delimited CSS class string (or '')
	 */

	// private
	initComponent: function () {
		this.setStartDate(this.startDate || new Date());

		this.callParent(arguments);

		if (this.readOnly === true) {
			this.addCls('ext-cal-readonly');
		}
	},

	// private
	afterRender: function () {
		this.callParent(arguments);

		this.renderTemplate();

		if (this.store) {
			this.setStore(this.store, true);
			if (this.store.deferLoad) {
				this.reloadStore(this.store.deferLoad);
				delete this.store.deferLoad;
			}
			else {
				this.store.initialParams = this.getStoreParams();
			}
		}
		if (this.calendarStore) {
			this.setCalendarStore(this.calendarStore, true);
		}

		this.on('resize', this.onResize, this);

		this.el.on({
			'mouseover': this.onMouseOver,
			'mouseout': this.onMouseOut,
			'click': this.onClick,
			//'resize': this.onResize,
			scope: this
		});

		// currently the context menu only contains CRUD actions so do not show it if read-only
		if (this.enableContextMenus && this.readOnly !== true) {
			this.el.on('contextmenu', this.onContextMenu, this);
		}

		this.el.unselectable();

		if (this.enableDD && this.readOnly !== true && this.initDD) {
			this.initDD();
		}

		this.on('eventsrendered', this.onEventsRendered);

		Ext6.defer(this.forceSize, 100, this);
	},

	/**
	 * Returns an object containing the start and end dates to be passed as params in all calls
	 * to load the event store. The param names are customizable using {@link #dateParamStart}
	 * and {@link #dateParamEnd} and the date format used in requests is defined by {@link #dateParamFormat}.
	 * If you need to add additional parameters to be sent when loading the store see {@link #getStoreParams}.
	 * @return {Object} An object containing the start and end dates
	 */
	getStoreDateParams: function () {
		var o = {};
		o[this.dateParamStart] = Ext6.Date.format(this.viewStart, this.dateParamFormat);
		o[this.dateParamEnd] = Ext6.Date.format(this.viewEnd, this.dateParamFormat);
		return o;
	},

	/**
	 * Returns an object containing all key/value params to be passed when loading the event store.
	 * By default the returned object will simply be the same object returned by {@link #getStoreDateParams},
	 * but this method is intended to be overridden if you need to pass anything in addition to start and end dates.
	 * See the inline code comments when overriding for details.
	 * @return {Object} An object containing all params to be sent when loading the event store
	 */
	getStoreParams: function () {
		// This is needed if you require the default start and end dates to be included
		var params = this.getStoreDateParams();

		// Here is where you can add additional custom params, e.g.:
		// params.now = Ext6.Date.format(new Date(), this.dateParamFormat);
		// params.foo = 'bar';
		// params.number = 123;

		return params;
	},

	/**
	 * Reloads the view's underlying event store using the params returned from {@link #getStoreParams}.
	 * Reloading the store is typically managed automatically by the view itself, but the method is
	 * available in case a manual reload is ever needed.
	 * @param {Object} options (optional) An object matching the format used by Store's {@link Ext6.data.Store#load load} method
	 */
	reloadStore: function (o) {
		Extensible.log('reloadStore');
		var calendar = this;
		o = Ext6.isObject(o) ? o : {};
		o.params = o.params || {};

		if (!o.callback) {
			o.callback = function() {
				calendar.ownerCalendarPanel.checkScrollToFirst();
			}
		}

		Ext6.apply(o.params, this.getStoreParams());
		this.store.load(o);
	},

	// private
	onEventsRendered: function () {
		this.forceSize();
	},

	// private
	forceSize: function () {
		if (this.el && this.el.down) {
			var hd = this.el.down('.ext-cal-hd-ct'),
				bd = this.el.down('.ext-cal-body-ct');

			if (bd == null || hd == null) return;

			var headerHeight = hd.getHeight(),
				sz = this.el.parent().getSize();

			bd.setHeight(sz.height - headerHeight);
		}
	},

	/**
	 * Refresh the current view, optionally reloading the event store also. While this is normally
	 * managed internally on any navigation and/or CRUD action, there are times when you might want
	 * to refresh the view manually (e.g., if you'd like to reload using different {@link #getStoreParams params}).
	 * @param {Boolean} reloadData True to reload the store data first, false to simply redraw the view using current
	 * data (defaults to false)
	 */
	refresh: function (reloadData) {
		Extensible.log('refresh (base), reload = ' + reloadData);
		if (reloadData === true) {
			this.reloadStore();
		}
		this.prepareData();
		this.renderTemplate();
		this.renderItems();
	},

	// private
	prepareData: function () {
		var lastInMonth = Ext6.Date.getLastDateOfMonth(this.startDate),
			w = 0, row = 0,
			dt = Ext6.Date.clone(this.viewStart);

		this.eventGrid = [[]];
		this.allDayGrid = [[]];
		this.evtMaxCount = [];

		var evtsInView = this.store.queryBy(function (rec) {
			return this.isEventVisible(rec.data);
		}, this);

		this.evtMaxCount[w] = 0;
		this.eventGrid[w] = this.eventGrid[w] || [];
		this.allDayGrid[w] = this.allDayGrid[w] || [];

		this.calendarStore.each(function (rec) {
			var d = rec.get('Resource_id');
			if (evtsInView.getCount() > 0) {
				// отбираем события только для текущего календаря
				var evts = evtsInView.filterBy(function (evtRec) {
					return evtRec.data[Extensible.calendar.data.EventMappings.Resource_id.name] == rec.get('Resource_id')
				}, this);
				this.sortEventRecordsForDay(evts);
				this.prepareEventGrid(evts, w, d);
			}
		}.createDelegate(this));
	},

	// private
	prepareEventGrid: function (evts, w, d) {
		var me = this,
			row = 0,
			max = me.maxEventsPerDay || 999,
			maxEventsForDay;

		evts.each(function (evt) {
			var M = Extensible.calendar.data.EventMappings;

			row = me.findEmptyRowIndex(w, d);
			me.eventGrid[w][d] = me.eventGrid[w][d] || [];
			me.eventGrid[w][d][row] = evt;

			if (evt.data[M.IsAllDay.name]) {
				row = me.findEmptyRowIndex(w, d, true);
				me.allDayGrid[w][d] = me.allDayGrid[w][d] || [];
				me.allDayGrid[w][d][row] = evt;
			}

			// If calculating the max event count for the day/week view header, use the allDayGrid
			// so that only all-day events displayed in that area get counted, otherwise count all events.
			maxEventsForDay = me[me.isHeaderView ? 'allDayGrid' : 'eventGrid'][w][d] || [];

			if (maxEventsForDay.length && me.evtMaxCount[w] < maxEventsForDay.length) {
				me.evtMaxCount[w] = Math.min(max + 1, maxEventsForDay.length);
			}
			return true;
		}, me);
	},

	// private
	prepareEventGridSpans: function (evt, grid, w, d, days, allday) {
		// this event spans multiple days/weeks, so we have to preprocess
		// the events and store special span events as placeholders so that
		// the render routine can build the necessary TD spans correctly.
		var w1 = w, d1 = d,
			row = this.findEmptyRowIndex(w, d, allday),
			dt = Ext6.Date.clone(this.viewStart);

		var start = {
			event: evt,
			isSpan: true,
			isSpanStart: true,
			spanLeft: false,
			spanRight: (d == 6)
		};
		grid[w][d] = grid[w][d] || [];
		grid[w][d][row] = start;

		while (--days) {
			dt = Extensible.Date.add(dt, {days: 1});
			if (dt > this.viewEnd) {
				break;
			}
			if (++d1 > 6) {
				// reset counters to the next week
				d1 = 0;
				w1++;
				row = this.findEmptyRowIndex(w1, 0);
			}
			grid[w1] = grid[w1] || [];
			grid[w1][d1] = grid[w1][d1] || [];

			grid[w1][d1][row] = {
				event: evt,
				isSpan: true,
				isSpanStart: (d1 == 0),
				spanLeft: (w1 > w) && (d1 % 7 == 0),
				spanRight: (d1 == 6) && (days > 1)
			};
		}
	},

	// private
	findEmptyRowIndex: function (w, d, allday) {
		var grid = allday ? this.allDayGrid : this.eventGrid,
			day = grid[w] ? grid[w][d] || [] : [],
			i = 0, ln = day.length;

		for (; i < ln; i++) {
			if (day[i] == null) {
				return i;
			}
		}
		return ln;
	},

	// private
	renderTemplate: function () {
		if (this.tpl) {
			this.tpl.overwrite(this.el, this.getTemplateParams());
			this.lastRenderStart = Ext6.Date.clone(this.viewStart);
			this.lastRenderEnd = Ext6.Date.clone(this.viewEnd);
		}
	},

	// private
	getTemplateParams: function () {
		return {
			viewStart: this.viewStart,
			viewEnd: this.viewEnd,
			startDate: this.startDate,
			todayCls: this.todayCls
		};
	},

	/**
	 * Disable store event monitoring within this view. Note that if you do this the view will no longer
	 * refresh itself automatically when CRUD actions occur. To enable store events see {@link #enableStoreEvents}.
	 * @return {CalendarView} this
	 */
	disableStoreEvents: function () {
		this.monitorStoreEvents = false;
		return this;
	},

	/**
	 * Enable store event monitoring within this view if disabled by {@link #disbleStoreEvents}.
	 * @return {CalendarView} this
	 */
	enableStoreEvents: function (refresh) {
		this.monitorStoreEvents = true;
		if (refresh === true) {
			this.refresh();
		}
		return this;
	},

	// private
	onResize: function () {
		this.refresh(false);
	},

	// private
	onInitDrag: function () {
		this.fireEvent('initdrag', this);
	},

	// private
	onEventDrop: function (rec, dt, Resource_id) {
		this.moveEvent(rec, dt, Resource_id);
	},

	// private
	onUpdate: function (ds, rec, operation) {
		if (this.hidden === true || this.monitorStoreEvents === false) {
			return;
		}
		if (operation == Ext6.data.Record.COMMIT) {
			Extensible.log('onUpdate');

			var rrule = rec.data[Extensible.calendar.data.EventMappings.RRule.name];
			// if the event has a recurrence rule we have to reload the store in case
			// any event instances were updated on the server
			this.refresh(rrule !== undefined && rrule !== '');

			if (this.enableFx && this.enableUpdateFx) {
				this.doUpdateFx(this.getEventEls(rec.data['EvnDirection_id']), {
					scope: this
				});
			}
		}
	},

	/**
	 * Provides the element effect(s) to run after an event is updated. The method is passed a {@link Ext6.CompositeElement}
	 * that contains one or more elements in the DOM representing the event that was updated. The default
	 * effect is {@link Ext6.Element#highlight highlight}. Note that this method will only be called when
	 * {@link #enableUpdateFx} is true (it is false by default).
	 * @param {Ext6.CompositeElement} el The {@link Ext6.CompositeElement} representing the updated event
	 * @param {Object} options An options object to be passed through to any Element.Fx methods. By default this
	 * object only contains the current scope (<tt>{scope:this}</tt>) but you can also add any additional fx-specific
	 * options that might be needed for a particular effect to this object.
	 */
	doUpdateFx: function (els, o) {
		this.highlightEvent(els, null, o);
	},

	// private
	onAdd: function (ds, recs, index) {
		var rec = Ext6.isArray(recs) ? recs[0] : recs;
		if (this.hidden === true || this.monitorStoreEvents === false) {
			return;
		}
		if (rec._deleting) {
			delete rec._deleting;
			return;
		}

		Extensible.log('onAdd');

		var rrule = rec.data[Extensible.calendar.data.EventMappings.RRule.name];

		this.tempEventId = rec.id;
		// if the new event has a recurrence rule we have to reload the store in case
		// new event instances were generated on the server
		this.refresh(rrule !== undefined && rrule !== '');

		if (this.enableFx && this.enableAddFx) {
			this.doAddFx(this.getEventEls(rec.data['EvnDirection_id']), {
				scope: this
			});
		}
		;
	},

	/**
	 * Provides the element effect(s) to run after an event is added. The method is passed a {@link Ext6.CompositeElement}
	 * that contains one or more elements in the DOM representing the event that was added. The default
	 * effect is {@link Ext6.Element#fadeIn fadeIn}. Note that this method will only be called when
	 * {@link #enableAddFx} is true (it is true by default).
	 * @param {Ext6.CompositeElement} el The {@link Ext6.CompositeElement} representing the added event
	 * @param {Object} options An options object to be passed through to any Element.Fx methods. By default this
	 * object only contains the current scope (<tt>{scope:this}</tt>) but you can also add any additional fx-specific
	 * options that might be needed for a particular effect to this object.
	 */
	doAddFx: function (els, o) {
		els.fadeIn(Ext6.apply(o, {duration: 2000}));
	},

	// private
	onRemove: function (ds, rec) {
		if (this.hidden === true || this.monitorStoreEvents === false) {
			return;
		}

		Extensible.log('onRemove');

		var rrule = rec.data[Extensible.calendar.data.EventMappings.RRule.name],
		// if the new event has a recurrence rule we have to reload the store in case
		// new event instances were generated on the server
			isRecurring = rrule !== undefined && rrule !== '';

		if (this.enableFx && this.enableRemoveFx) {
			this.doRemoveFx(this.getEventEls(rec.data['EvnDirection_id']), {
				remove: true,
				scope: this,
				callback: Ext6.bind(this.refresh, this, [isRecurring])
			});
		}
		else {
			this.getEventEls(rec.data['EvnDirection_id']).remove();
			this.refresh(isRecurring);
		}
	},

	/**
	 * Provides the element effect(s) to run after an event is removed. The method is passed a {@link Ext6.CompositeElement}
	 * that contains one or more elements in the DOM representing the event that was removed. The default
	 * effect is {@link Ext6.Element#fadeOut fadeOut}. Note that this method will only be called when
	 * {@link #enableRemoveFx} is true (it is true by default).
	 * @param {Ext6.CompositeElement} el The {@link Ext6.CompositeElement} representing the removed event
	 * @param {Object} options An options object to be passed through to any Element.Fx methods. By default this
	 * object contains the following properties:
	 * <pre><code>
	 {
   remove: true, // required by fadeOut to actually remove the element(s)
   scope: this,  // required for the callback
   callback: fn  // required to refresh the view after the fx finish
}
	 * </code></pre>
	 * While you can modify this options object as needed if you change the effect used, please note that the
	 * callback method (and scope) MUST still be passed in order for the view to refresh correctly after the removal.
	 * Please see the inline code comments before overriding this method.
	 */
	doRemoveFx: function (els, o) {
		// Please make sure you keep this entire code block or removing events might not work correctly!
		// Removing is a little different because we have to wait for the fx to finish, then we have to actually
		// refresh the view AFTER the fx are run (this is different than add and update).
		if (els.getCount() == 0 && Ext6.isFunction(o.callback)) {
			// if there are no matching elements in the view make sure the callback still runs.
			// this can happen when an event accessed from the "more" popup is deleted.
			o.callback.call(o.scope || this);
		}
		else {
			// If you'd like to customize the remove fx do so here. Just make sure you
			// DO NOT override the default callback property on the options object, and that
			// you still pass that object in whatever fx method you choose.
			els.fadeOut(o);
		}
	},

	/**
	 * Visually highlights an event using {@link Ext6.Fx#highlight} config options.
	 * @param {Ext6.CompositeElement} els The element(s) to highlight
	 * @param {Object} color (optional) The highlight color. Should be a 6 char hex
	 * color without the leading # (defaults to yellow: 'ffff9c')
	 * @param {Object} o (optional) Object literal with any of the {@link Ext6.Fx} config
	 * options. See {@link Ext6.Fx#highlight} for usage examples.
	 */
	highlightEvent: function (els, color, o) {
		if (this.enableFx) {
			var c;
			!(Ext6.isIE || Ext6.isOpera) ?
				els.highlight(color, o) :
				// Fun IE/Opera handling:
				els.each(function (el) {
					el.highlight(color, Ext6.applyIf({attr: 'color'}, o));
					if (c = el.down('.ext-cal-evm')) {
						c.highlight(color, o);
					}
				}, this);
		}
	},

	/**
	 * Retrieve an Event object's id from its corresponding node in the DOM.
	 * @param {String/Element/HTMLElement} el An {@link Ext6.Element}, DOM node or id
	 */
//	getEventIdFromEl : function(el){
//		el = Ext6.get(el);
//		var id = el.id.split(this.eventElIdDelimiter)[1];
//        if(id.indexOf('-w_') > -1){
//            //This id has the index of the week it is rendered in as part of the suffix.
//            //This allows events that span across weeks to still have reproducibly-unique DOM ids.
//            id = id.split('-w_')[0];
//        }
//        return id;
//	},
	getEventIdFromEl: function (el) {
		el = Ext6.get(el);
		var parts, id = '', cls, classes = el.dom.className.split(' ');

		Ext6.each(classes, function (cls) {
			parts = cls.split(this.eventElIdDelimiter);
			if (parts.length > 1) {
				id = parts[1];
				return false;
			}
		}, this);

		return id;
	},

	// private
	getEventId: function (eventId) {
		if (eventId === undefined && this.tempEventId) {
			// temp record id assigned during an add, will be overwritten later
			eventId = this.tempEventId;
		}
		return eventId;
	},

	/**
	 *
	 * @param {String} eventId
	 * @param {Boolean} forSelect
	 * @return {String} The selector class
	 */
	getEventSelectorCls: function (eventId, forSelect) {
		var prefix = forSelect ? '.' : '';
		return prefix + this.id + this.eventElIdDelimiter + this.getEventId(eventId);
	},

	/**
	 *
	 * @param {String} eventId
	 * @return {Ext6.dom.CompositeElement} The matching CompositeElement of nodes
	 * that comprise the rendered event.  Any event that spans across a view
	 * boundary will contain more than one internal Element.
	 */
	getEventEls: function (eventId) {
		var els = this.el.select(this.getEventSelectorCls(this.getEventId(eventId), true), false);
		return Ext6.create('Ext6.CompositeElement', els);
	},

	// private
	onDataChanged: function (store) {
		Extensible.log('onDataChanged');
		this.refresh(false);
	},

	// private
	isEventVisible: function (evt) {
		var M = Extensible.calendar.data.EventMappings,
			data = evt.data || evt,
			calRec = this.calendarStore ?
				this.calendarStore.findRecord(M.Resource_id.name, evt[M.Resource_id.name]) : null;

		if (calRec && calRec.data[Extensible.calendar.data.CalendarMappings.IsHidden.name] === true) {
			// if the event is on a hidden calendar then no need to test the date boundaries
			return false;
		}

		var start = this.viewStart.getTime(),
			end = this.viewEnd.getTime(),
			evStart = data[M.StartDate.name].getTime(),
			evEnd = data[M.EndDate.name].getTime();

		return Extensible.Date.rangesOverlap(start, end, evStart, evEnd);
	},

	// private
	isOverlapping: function (evt1, evt2) {
		var ev1 = evt1.data ? evt1.data : evt1,
			ev2 = evt2.data ? evt2.data : evt2,
			M = Extensible.calendar.data.EventMappings,
			start1 = ev1[M.StartDate.name].getTime(),
			end1 = Extensible.Date.add(ev1[M.EndDate.name], {seconds: -1}).getTime(),
			start2 = ev2[M.StartDate.name].getTime(),
			end2 = Extensible.Date.add(ev2[M.EndDate.name], {seconds: -1}).getTime(),
			startDiff = Extensible.Date.diff(ev1[M.StartDate.name], ev2[M.StartDate.name], 'm');

		// если в разных календарях значит не пересекаются.
		if (ev1['Resource_id'] != ev2['Resource_id']) {
			return false;
		}

		if (end1 < start1) {
			end1 = start1;
		}
		if (end2 < start2) {
			end2 = start2;
		}

		var evtsOverlap = Extensible.Date.rangesOverlap(start1, end1, start2, end2),
			minimumMinutes = this.minEventDisplayMinutes || 0, // applies in day/week body view only for vertical overlap
			ev1MinHeightOverlapsEv2 = minimumMinutes > 0 && (startDiff > -minimumMinutes && startDiff < minimumMinutes);

		//return (ev1startsInEv2 || ev1EndsInEv2 || ev1SpansEv2 || ev1MinHeightOverlapsEv2);
		return (evtsOverlap || ev1MinHeightOverlapsEv2);
	},

	// private
	getDayEl: function (Resource_id) {
		return Ext6.get(this.getDayId(Resource_id));
	},

	// private
	getDayId: function (Resource_id) {
		return this.id + this.dayElIdDelimiter + Resource_id;
	},

	/**
	 * Returns the start date of the view, as set by {@link #setStartDate}. Note that this may not
	 * be the first date displayed in the rendered calendar -- to get the start and end dates displayed
	 * to the user use {@link #getViewBounds}.
	 * @return {Date} The start date
	 */
	getStartDate: function () {
		return this.startDate;
	},

	/**
	 * Sets the start date used to calculate the view boundaries to display. The displayed view will be the
	 * earliest and latest dates that match the view requirements and contain the date passed to this function.
	 * @param {Date} dt The date used to calculate the new view boundaries
	 */
	setStartDate: function (start, /*private*/reload) {
		var me = this;

		Extensible.log('setStartDate (base) ' + Ext6.Date.format(start, 'Y-m-d'));

		var cloneDt = Ext6.Date.clone,
			cloneStartDate = me.startDate ? cloneDt(me.startDate) : null,
			cloneStart = cloneDt(start),
			cloneViewStart = me.viewStart ? cloneDt(me.viewStart) : null,
			cloneViewEnd = me.viewEnd ? cloneDt(me.viewEnd) : null;

		if (me.fireEvent('beforedatechange', me, cloneStartDate, cloneStart, cloneViewStart, cloneViewEnd) !== false) {
			me.startDate = Ext6.Date.clearTime(start);
			me.setViewBounds(start);

			if (me.ownerCalendarPanel && me.ownerCalendarPanel.startDate !== me.startDate) {
				// Sync the owning CalendarPanel's start date directly, not via CalendarPanel.setStartDate(),
				// since that would in turn call this method again.
				me.ownerCalendarPanel.startDate = me.startDate;
			}

			if (me.rendered) {
				me.refresh(reload);
			}
			me.fireEvent('datechange', me, cloneDt(me.startDate), cloneDt(me.viewStart), cloneDt(me.viewEnd));
		}
	},

	// private
	setViewBounds: function (startDate) {
		var me = this,
			start = startDate || me.startDate,
			offset = start.getDay() - me.startDay,
			Dt = Extensible.Date;

		me.viewStart = start; // начало текущего дня
		me.viewEnd = Dt.add(me.viewStart, {days: 1, seconds: -1}); // конец текущего дня
	},

	/**
	 * Returns the start and end boundary dates currently displayed in the view. The method
	 * returns an object literal that contains the following properties:<ul>
	 * <li><b>start</b> Date : <div class="sub-desc">The start date of the view</div></li>
	 * <li><b>end</b> Date : <div class="sub-desc">The end date of the view</div></li></ul>
	 * For example:<pre><code>
	 var bounds = view.getViewBounds();
	 alert('Start: '+bounds.start);
	 alert('End: '+bounds.end);
	 </code></pre>
	 * @return {Object} An object literal containing the start and end values
	 */
	getViewBounds: function () {
		return {
			start: this.viewStart,
			end: this.viewEnd
		}
	},

	/* private
	 * Sort events for a single day for display in the calendar.  This sorts allday
	 * events first, then non-allday events are sorted either based on event start
	 * priority or span priority based on the value of {@link #spansHavePriority}
	 * (defaults to event start priority).
	 * @param {MixedCollection} evts A {@link Ext6.util.MixedCollection MixedCollection}
	 * of {@link #Extensible.calendar.data.EventModel EventRecord} objects
	 */
	sortEventRecordsForDay: function (evts) {
		if (evts.length < 2) {
			return;
		}
		evts.sortBy(Ext6.bind(function (evtA, evtB) {
			var a = evtA.data,
				b = evtB.data,
				M = Extensible.calendar.data.EventMappings;

			// Always sort all day events before anything else
			if (a[M.IsAllDay.name]) {
				return -1;
			}
			else if (b[M.IsAllDay.name]) {
				return 1;
			}
			if (this.spansHavePriority) {
				// This logic always weights span events higher than non-span events
				// (at the possible expense of start time order). This seems to
				// be the approach used by Google calendar and can lead to a more
				// visually appealing layout in complex cases, but event order is
				// not guaranteed to be consistent.
				var diff = Extensible.Date.diffDays;
				if (diff(a[M.StartDate.name], a[M.EndDate.name]) > 0) {
					if (diff(b[M.StartDate.name], b[M.EndDate.name]) > 0) {
						// Both events are multi-day
						if (a[M.StartDate.name].getTime() == b[M.StartDate.name].getTime()) {
							// If both events start at the same time, sort the one
							// that ends later (potentially longer span bar) first
							return b[M.EndDate.name].getTime() - a[M.EndDate.name].getTime();
						}
						return a[M.StartDate.name].getTime() - b[M.StartDate.name].getTime();
					}
					return -1;
				}
				else if (diff(b[M.StartDate.name], b[M.EndDate.name]) > 0) {
					return 1;
				}
				return a[M.StartDate.name].getTime() - b[M.StartDate.name].getTime();
			}
			else {
				// Doing this allows span and non-span events to intermingle but
				// remain sorted sequentially by start time. This seems more proper
				// but can make for a less visually-compact layout when there are
				// many such events mixed together closely on the calendar.
				return a[M.StartDate.name].getTime() - b[M.StartDate.name].getTime();
			}
		}, this));
	},

	/**
	 * Sets the event store used by the calendar to display {@link Extensible.calendar.data.EventModel events}.
	 * @param {Ext6.data.Store} store
	 */
	setStore: function (store, initial) {
		var currStore = this.store;

		if (!initial && currStore) {
			currStore.un("datachanged", this.onDataChanged, this);
			currStore.un("clear", this.refresh, this);
			currStore.un("write", this.onWrite, this);
			currStore.un("exception", this.onException, this);
		}
		if (store) {
			store.on("datachanged", this.onDataChanged, this);
			store.on("clear", this.refresh, this);
			store.on("write", this.onWrite, this);
			store.on("exception", this.onException, this);
		}
		this.store = store;
	},

	// private
	onException: function (proxy, type, action, o, res, arg) {
		// form edits are explicitly canceled, but we may not know if a drag/drop operation
		// succeeded until after a server round trip. if the update failed we have to explicitly
		// reject the changes so that the record doesn't stick around in the store's modified list
		if (arg.reject) {
			arg.reject();
		}
	},

	/**
	 * Sets the calendar store used by the calendar (contains records of type {@link Extensible.calendar.data.CalendarModel CalendarRecord}).
	 * @param {Ext6.data.Store} store
	 */
	setCalendarStore: function (store, initial) {
		if (!initial && this.calendarStore) {
			this.calendarStore.un("datachanged", this.refresh, this);
			this.calendarStore.un("add", this.refresh, this);
			this.calendarStore.un("remove", this.refresh, this);
			this.calendarStore.un("update", this.refresh, this);
		}
		if (store) {
			store.on("datachanged", this.refresh, this);
			store.on("add", this.refresh, this);
			store.on("remove", this.refresh, this);
			store.on("update", this.refresh, this);
		}
		this.calendarStore = store;
	},

	// private
	getEventRecord: function (id) {
		var idx = this.store.find('EvnDirection_id', id,
			0,     // start index
			false, // match any part of string
			true,  // case sensitive
			true   // force exact match
		);
		return this.store.getAt(idx);
	},

	// private
	getEventRecordFromEl: function (el) {
		return this.getEventRecord(this.getEventIdFromEl(el));
	},
	// private
	save: function () {
		// If the store is configured as autoSync:true the record's endEdit
		// method will have already internally caused a save to execute on
		// the store. We only need to save manually when autoSync is false,
		// otherwise we'll create duplicate transactions.
		if (!this.store.autoSync) {
			this.store.sync();
		}
	},

	// private
	onWrite: function (store, operation) {
		if (operation.wasSuccessful()) {
			var rec = operation._records[0];

			switch (operation.action) {
				case 'create':
					this.onAdd(store, rec);
					break;
				case 'update':
					this.onUpdate(store, rec, Ext6.data.Record.COMMIT);
					break;
				case 'destroy':
					this.onRemove(store, rec);
					break;
			}
		}
	},

	// private
	onEventAdd: function (form, rec) {
		this.newRecord = rec;
		if (!rec.store) {
			this.store.add(rec);
			this.save();
		}
		this.fireEvent('eventadd', this, rec);
	},

	// private
	onEventUpdate: function (form, rec) {
		this.save();
		this.fireEvent('eventupdate', this, rec);
	},

	// private
	onEventDelete: function (form, rec) {
		if (rec.store) {
			this.store.remove(rec);
		}
		this.save();
		this.fireEvent('eventdelete', this, rec);
	},

	// private
	onEventCancel: function (form, rec) {
		this.fireEvent('eventcancel', this, rec);
	},

	// private -- called from subclasses
	onDayClick: function (dt, ad, el) {

	},

	// private
	showEventMenu: function (el, xy) {
		if (!this.eventMenu) {
			this.eventMenu = Ext6.create('Extensible.calendar.menu.Event', {
				ownerWin: this.ownerWin,
				listeners: {
					'eventdelete': Ext6.bind(this.onDeleteEvent, this),
					'eventmove': Ext6.bind(this.onMoveEvent, this)
				}
			});
		}
		this.eventMenu.showForEvent(this.getEventRecordFromEl(el), el, xy);
		this.menuActive = true;
	},

	// private
	onMoveEvent: function (menu, rec, dt, Resource_id) {
		this.moveEvent(rec, dt, Resource_id);
		this.menuActive = false;
	},

	/**
	 * Move the event to a new start date, preserving the original event duration.
	 * @param {Object} rec The event {@link Extensible.calendar.data.EventModel record}
	 * @param {Object} dt The new start date
	 */
	moveEvent: function (rec, dt, Resource_id) {
		if (
			Extensible.Date.compare(rec.data[Extensible.calendar.data.EventMappings.StartDate.name], dt) === 0
			&& rec.get(Extensible.calendar.data.EventMappings.Resource_id.name) == Resource_id
		) {
			// no changes
			return;
		}
		if (this.fireEvent('beforeeventmove', this, rec, Ext6.Date.clone(dt)) !== false) {
			var diff = dt.getTime() - rec.data[Extensible.calendar.data.EventMappings.StartDate.name].getTime();

			var BrigData = rec.data.operbrig;
			var params = {
				EvnDirection_id: rec.data['EvnDirection_id'],
				Resource_id: Resource_id,
				start: dt,
				end: Extensible.Date.add(rec.data[Extensible.calendar.data.EventMappings.EndDate.name], {millis: diff}),
				BrigDataJson: Ext6.JSON.encode(BrigData)
			};
			var to_save = false;
			Ext.Ajax.request ({
				self: this,
				params: params,
				async: false,
				cache: false,
				url: '/?c=OperBlock&m=getIntersectedResources',
				success: function(response, options) {
					var resp = Ext.util.JSON.decode(response.responseText);
					if ( resp.data.length > 0 ) {
						var sD = Ext6.Date.parse(resp.data[0]["TimetableResource_begTime"].date.split(".")[0], "Y-m-d H:i:s");
						var tT = Math.round(resp.data[0]["TimetableResource_Time"] / 60).toString().padStart(2,"0") +
							":" + (resp.data[0]["TimetableResource_Time"] - Math.round(resp.data[0]["TimetableResource_Time"] / 60) * 60).toString().padStart(2,"0");
						var message = resp.data[0]["MedPersonal_SurName"] + " " + resp.data[0]["MedPersonal_FirName"] + " " + resp.data[0]["MedPersonal_SecName"]
								+ " уже участвует в запланированной операции " +
								Ext6.Date.format(sD, 'd-m-Y H:i') + " длительностью " + tT
								+ ", в которой задействован данный врач. Продолжить сохранение?";
						if ( resp.data[0]["Resource_id"] == params["Resource_id"] ) {
							message = "На данном столе уже запланирована операция с " +
								Ext6.Date.format(sD, 'd-m-Y H:i')
								+ " длительностью " + tT + ". Продолжить сохранение?";
						}
						Ext.MessageBox.show({
							title: "Конфликт!",
							msg: message,
							buttons: Ext.Msg.YESNO,
							icon: Ext.MessageBox.WARNING,
							fn: function (butn) {
								if ( butn == 'yes' ) {
									rec.beginEdit();
									rec.set(Extensible.calendar.data.EventMappings.Resource_id.name, Resource_id);
									rec.set(Extensible.calendar.data.EventMappings.StartDate.name, dt);
									rec.set(Extensible.calendar.data.EventMappings.EndDate.name, Extensible.Date.add(rec.data[Extensible.calendar.data.EventMappings.EndDate.name], {millis: diff}));
									rec.endEdit();
									options.self.save();
									options.self.fireEvent('eventmove', options.self, rec);
								}
							}
						});
					} else {
						to_save = true;
						rec.beginEdit();
						rec.set(Extensible.calendar.data.EventMappings.Resource_id.name, Resource_id);
						rec.set(Extensible.calendar.data.EventMappings.StartDate.name, dt);
						rec.set(Extensible.calendar.data.EventMappings.EndDate.name, Extensible.Date.add(rec.data[Extensible.calendar.data.EventMappings.EndDate.name], {millis: diff}));
						rec.endEdit();
						options.self.save();
						options.self.fireEvent('eventmove', options.self, rec);
					}
				}
			});
/*
			rec.beginEdit();
			rec.set(Extensible.calendar.data.EventMappings.Resource_id.name, Resource_id);
			rec.set(Extensible.calendar.data.EventMappings.StartDate.name, dt);
			rec.set(Extensible.calendar.data.EventMappings.EndDate.name, Extensible.Date.add(rec.data[Extensible.calendar.data.EventMappings.EndDate.name], {millis: diff}));
			rec.endEdit();

			this.save();
			this.fireEvent('eventmove', this, rec);
*/
		}
	},

	// private
	onDeleteEvent: function (menu, rec, el) {
		rec._deleting = true;
		this.deleteEvent(rec, el);
		this.menuActive = false;
	},

	/**
	 * Delete the specified event.
	 * @param {Object} rec The event {@link Extensible.calendar.data.EventModel record}
	 */
	deleteEvent: function (rec, /* private */el) {
		if (this.fireEvent('beforeeventdelete', this, rec, el) !== false) {
			this.store.remove(rec);
			this.save();
			this.fireEvent('eventdelete', this, rec, el);
		}
	},

	// private
	onContextMenu: function (e, t) {
		var el, match = false;

		if (el = e.getTarget(this.eventSelector, 5, true)) {
			this.showEventMenu(el, e.getXY());
			match = true;
		}

		if (match || this.suppressBrowserContextMenu === true) {
			e.preventDefault();
		}
	},

	/*
	 * Shared click handling.  Each specific view also provides view-specific
	 * click handling that calls this first.  This method returns true if it
	 * can handle the click (and so the subclass should ignore it) else false.
	 */
	onClick: function (e, t) {
		var me = this,
			el = e.getTarget(me.eventSelector, 5);

		if (me.dropZone) {
			me.dropZone.clearShims();
		}
		if (el) {
			var el_plan = e.getTarget('.plan', 1);
			// только при клике на специальный значок в хеадере стикера
			if (el_plan) {
				var id = me.getEventIdFromEl(el),
					rec = me.getEventRecord(id);

				if (me.fireEvent('eventclick', me, rec, el) !== false) {
					if (me.readOnly !== true) {
						me.showEventMenu(el, e.getXY());
					}
				}
				return true;
			}
		}

		var el_tablesmenu = e.getTarget('.tables_menu', 1);
		if (el_tablesmenu) {
			this.ownerWin.tablesMenu(e, el_tablesmenu);
		}

		var el_timedropdown = e.getTarget('.time_dropdown', 1);
		if (el_timedropdown) {
			this.ownerWin.timeDropdown(e, el_timedropdown);
		}
	},

	// private
	onMouseOver: function (e, t) {
		if (this.trackMouseOver !== false && (this.dragZone == undefined || !this.dragZone.dragging)) {
			if (!this.handleEventMouseEvent(e, t, 'over')) {
				this.handleDayMouseEvent(e, t, 'over');
			}
		}
	},

	// private
	onMouseOut: function (e, t) {
		if (this.trackMouseOver !== false && (this.dragZone == undefined || !this.dragZone.dragging)) {
			if (!this.handleEventMouseEvent(e, t, 'out')) {
				this.handleDayMouseEvent(e, t, 'out');
			}
		}
	},

	// private
	handleEventMouseEvent: function (e, t, type) {
		var el;
		if (el = e.getTarget(this.eventSelector, 5, true)) {
			var title = e.getTarget('.title', 3, true);
			if (title) { // таскать можно только за заголовок
				var rel = Ext6.get(e.getRelatedTarget());
				if (title == rel || title.contains(rel)) {
					return true;
				}

				var evtId = this.getEventIdFromEl(el);

				if (this.eventOverClass != '') {
					var els = this.getEventEls(evtId);
					els[type == 'over' ? 'addCls' : 'removeCls'](this.eventOverClass);
				}
				this.fireEvent('event' + type, this, this.getEventRecord(evtId), el);

				return true;
			} else {
				if (!this.toolTip) {
					this.toolTip = Ext6.create('Ext6.tip.ToolTip', {
						trackMouse: true,
						renderTo: Ext6.getBody(),
						listeners: {
							beforeshow: function updateTipBody(tip) {
								tip.update("<div class='ext-cal-evr'>" + tip.target.dom.innerHTML + "</div>");
							}
						}
					});
				}

				if (type == 'over') {
					this.toolTip.setTarget(el);
					if (this.toolTip.hidden) {
						this.toolTip.show();
					}
				} else {
					if (!this.toolTip.hidden) {
						this.toolTip.hide();
					}
				}
			}
		}
		return false;
	},

	// private
	getDateFromId: function (id, delim) {
		var parts = id.split(delim);
		return parts[parts.length - 1];
	},

	// private
	handleDayMouseEvent: function (e, t, type) {
		if (t = e.getTarget('td', 3)) {
			if (t.id && t.id.indexOf(this.dayElIdDelimiter) > -1) {
				var dt = this.getDateFromId(t.id, this.dayElIdDelimiter),
					rel = Ext6.get(e.getRelatedTarget()),
					relTD, relDate;

				if (rel) {
					relTD = rel.is('td') ? rel : rel.up('td', 3);
					relDate = relTD && relTD.id ? this.getDateFromId(relTD.id, this.dayElIdDelimiter) : '';
				}
				if (!rel || dt != relDate) {
					var el = this.getDayEl(dt);
					if (el && this.dayOverClass != '') {
						el[type == 'over' ? 'addCls' : 'removeCls'](this.dayOverClass);
					}
					this.fireEvent('day' + type, this, Ext6.Date.parseDate(dt, "Ymd"), el);
				}
			}
		}
	},

	// private, MUST be implemented by subclasses
	renderItems: function () {
		throw 'This method must be implemented by a subclass';
	},

	// private
	destroy: function () {
		this.callParent(arguments);

		if (this.el) {
			this.el.un('contextmenu', this.onContextMenu, this);
		}
		Ext6.destroy(
			this.editWin,
			this.eventMenu,
			this.dragZone,
			this.dropZone
		);
	}
});
/**
 * @class Extensible.calendar.view.DayHeader
 * @extends Extensible.calendar.view.AbstractCalendar
 * <p>This is the header area container within the day and week views where all-day events are displayed.
 * Normally you should not need to use this class directly -- instead you should use {@link Extensible.calendar.view.Day DayView}
 * which aggregates this class and the {@link Extensible.calendar.view.DayBody DayBodyView} into the single unified view
 * presented by {@link Extensible.calendar.CalendarPanel CalendarPanel}.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.calendar.view.DayHeader', {
	extend: 'Extensible.calendar.view.AbstractCalendar',
	alias: 'widget.extensible.dayheaderview',

	requires: [
		'Extensible.calendar.template.DayHeader'
	],

	// private configs
	allDayOnly: true,
	monitorResize: false,
	isHeaderView: true,

	/**
	 * @event dayclick
	 * Fires after the user clicks within the view container and not on an event element. This is a cancelable event, so
	 * returning false from a handler will cancel the click without displaying the event editor view. This could be useful
	 * for validating that a user can only create events on certain days.
	 * @param {Extensible.calendar.view.DayHeader} this
	 * @param {Date} dt The date/time that was clicked on
	 * @param {Boolean} allday True if the day clicked on represents an all-day box, else false. Clicks within the
	 * DayHeaderView always return true for this param.
	 * @param {Ext6.Element} el The Element that was clicked on
	 */

	// private
	afterRender: function () {
		if (!this.tpl) {
			this.tpl = Ext6.create('Extensible.calendar.template.DayHeader', {
				id: this.id,
				calendarStore: this.calendarStore
			});
		}
		this.tpl.compile();
		this.addCls('ext-cal-day-header');

		this.callParent(arguments);
	},

	// private
	forceSize: Ext6.emptyFn,

	// private
	refresh: function (reloadData) {
		Extensible.log('refresh (DayHeaderView)');
		this.callParent(arguments);
		this.recalcHeaderBox();
	},

	// private
	recalcHeaderBox: function () {
		var tbl = this.el.down('.ext-cal-evt-tbl'),
			h = tbl.getHeight();

		if (!h) h = 24;
		this.el.setHeight(h + 7);

		// These should be auto-height, but since that does not work reliably
		// across browser / doc type, we have to size them manually
		this.el.down('.ext-cal-hd-ad-inner').setHeight(h + 5);
		this.el.down('.ext-cal-bg-tbl').setHeight(h + 5);
	},

	// private
	renderItems: function () {
		this.fireEvent('eventsrendered', this);
	},

	// private
	onClick: function (e, t) {
		if (el = e.getTarget('td', 3)) {
			if (el.id && el.id.indexOf(this.dayElIdDelimiter) > -1) {
				var parts = el.id.split(this.dayElIdDelimiter),
					dt = parts[parts.length - 1];

				this.onDayClick(Ext6.Date.parseDate(dt, 'Ymd'), true, Ext6.get(this.getDayId(dt, true)));
				return;
			}
		}
		this.callParent(arguments);
	}
});
/**
 * @class Extensible.calendar.view.DayBody
 * @extends Extensible.calendar.view.AbstractCalendar
 * <p>This is the scrolling container within the day and week views where non-all-day events are displayed.
 * Normally you should not need to use this class directly -- instead you should use {@link
	* Extensible.calendar.view.Day DayView} which aggregates this class and the {@link
	* Extensible.calendar.view.DayHeader DayHeaderView} into the single unified view
 * presented by {@link Extensible.calendar.CalendarPanel CalendarPanel}.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.calendar.view.DayBody', {
	extend: 'Extensible.calendar.view.AbstractCalendar',
	alias: 'widget.extensible.daybodyview',

	requires: [
		'Ext6.XTemplate',
		'Extensible.calendar.template.DayBody',
		'Extensible.calendar.data.EventMappings',
		'Extensible.calendar.dd.DayDragZone',
		'Extensible.calendar.dd.DayDropZone'
	],

	//private
	dayColumnElIdDelimiter: '-day-col-',
	hourIncrement: 60,
	viewStartHourOffset: 0, // оффсет, если вдруг график расширился на предыдущий день (чтобы показать переходящие операции)

	//private
	initComponent: function () {
		this.callParent(arguments);

		if (this.readOnly === true) {
			this.enableEventResize = false;
		}
		this.incrementsPerHour = this.hourIncrement / this.ddIncrement;
		this.minEventHeight = this.minEventDisplayMinutes / (this.hourIncrement / this.hourHeight);
	},

	//private
	initDD: function () {
		var cfg = {
			view: this,
			createText: this.ddCreateEventText,
			moveText: this.ddMoveEventText,
			resizeText: this.ddResizeEventText,
			ddIncrement: this.ddIncrement,
			ddGroup: this.ddGroup || this.id + '-DayViewDD',
			ownerCalendarPanel: this.ownerCalendarPanel
		};

		this.el.ddScrollConfig = {
			// scrolling is buggy in IE/Opera for some reason.  A larger vthresh
			// makes it at least functional if not perfect
			vthresh: Ext6.isIE || Ext6.isOpera ? 100 : 40,
			hthresh: -1,
			frequency: 50,
			increment: 100,
			ddGroup: this.ddGroup || this.id + '-DayViewDD'
		};

		this.dragZone = Ext6.create('Extensible.calendar.dd.DayDragZone', this.el, Ext6.apply({
			// disabled for now because of bugs in Ext 4 ScrollManager:
			//containerScroll: true
		}, cfg));

		this.dropZone = Ext6.create('Extensible.calendar.dd.DayDropZone', this.el, cfg);
	},

	//private
	refresh: function (reloadData) {
		Extensible.log('refresh (DayBodyView)');
		var top = this.el.getScroll().top;

		this.callParent(arguments);

		// skip this if the initial render scroll position has not yet been set.
		// necessary since IE/Opera must be deferred, so the first refresh will
		// override the initial position by default and always set it to 0.
		if (this.scrollReady) {
			this.scrollTo(top);
		}
	},

	/**
	 * Scrolls the container to the specified vertical position. If the view is large enough that
	 * there is no scroll overflow then this method will have no affect.
	 * @param {Number} y The new vertical scroll position in pixels
	 * @param {Boolean} defer (optional) <p>True to slightly defer the call, false to execute immediately.</p>
	 *
	 * <p>This method will automatically defer itself for IE and Opera (even if you pass false) otherwise
	 * the scroll position will not update in those browsers. You can optionally pass true, however, to
	 * force the defer in all browsers, or use your own custom conditions to determine whether this is needed.</p>
	 *
	 * <p>Note that this method should not generally need to be called directly as scroll position is
	 * managed internally.</p>
	 */
	scrollTo: function (y, defer) {
		defer = defer || (Ext6.isIE || Ext6.isOpera);
		if (defer) {
			Ext6.defer(function () {
				this.el.scrollTo('top', y);
				this.scrollReady = true;
			}, 10, this);
		}
		else {
			this.el.scrollTo('top', y);
			this.scrollReady = true;
		}
	},

	// private
	afterRender: function () {
		if (!this.tpl) {
			this.tpl = Ext6.create('Extensible.calendar.template.DayBody', {
				id: this.id,
				showHourSeparator: this.showHourSeparator,
				viewStartHour: this.viewStartHour,
				viewEndHour: this.viewEndHour,
				hourIncrement: this.hourIncrement,
				hourHeight: this.hourHeight,
				calendarStore: this.calendarStore,
				ownerCalendarPanel: this.ownerCalendarPanel,
				dayBody: this
			});
		}
		this.tpl.compile();

		this.addCls('ext-cal-body-ct');

		this.callParent(arguments);

		// default scroll position to scrollStartHour (7am by default) or min view hour if later
		var startHour = Math.max(this.scrollStartHour, this.viewStartHour),
			scrollStart = Math.max(0, startHour - this.viewStartHour);

		if (scrollStart > 0) {
			this.scrollTo(scrollStart * this.hourHeight);
		}
	},

	// private
	forceSize: Ext6.emptyFn,

	// private -- called from DayViewDropZone
	onEventResize: function (rec, data) {
		if (this.fireEvent('beforeeventresize', this, rec, data) !== false) {
			var D = Extensible.Date,
				start = Extensible.calendar.data.EventMappings.StartDate.name,
				end = Extensible.calendar.data.EventMappings.EndDate.name;

			if (D.compare(rec.data[start], data.StartDate) === 0 &&
				D.compare(rec.data[end], data.EndDate) === 0) {
				// no changes
				return;
			}
			rec.set(start, data.StartDate);
			rec.set(end, data.EndDate);
			this.onEventUpdate(null, rec);

			this.fireEvent('eventresize', this, rec);
		}
	},

	// inherited docs
	getEventBodyMarkup: function () {
		if (!this.eventBodyMarkup) {
			this.eventBodyMarkup = ['<div class="title"><tpl if="conflicted"><div class="conflimgback"></div><div class="conflimg"><img height="13" src="/img/icons/warn_red.png" /></div>&nbsp;</tpl><div class="plan">&#9660;</div>{Title}</div><div class="usluga">{Usluga}</div><div class="operbrig">{OperBrig}</div>',
				'<tpl if="_isRecurring">',
				'<i class="ext-cal-ic ext-cal-ic-rcr">&#160;</i>',
				'</tpl>'
			].join('');
		}
		return this.eventBodyMarkup;
	},

	// inherited docs
	getEventTemplate: function () {
		if (!this.eventTpl) {
			this.eventTpl = !(Ext6.isIE || Ext6.isOpera) ?
				Ext6.create('Ext6.XTemplate',
					'<div id="{_elId}" class="{_extraCls} ext-cal-evt ext-cal-evr" ',
					'style="left: {_left}%; width: {_width}%; top: {_top}px; height: {_height}px;">',
					'<div class="ext-evt-bd">', this.getEventBodyMarkup(), '</div>',
					this.enableEventResize ?
						'<div class="ext-evt-rsz"><div class="ext-evt-rsz-h">&#160;</div></div>' : '',
					'</div>'
				)
				: Ext6.create('Ext6.XTemplate',
				'<div id="{_elId}" class="ext-cal-evt {_extraCls}" ',
				'style="left: {_left}%; width: {_width}%; top: {_top}px;">',
				'<div class="ext-cal-evb">&#160;</div>',
				'<dl style="height: {_height}px;" class="ext-cal-evdm">',
				'<dd class="ext-evt-bd">',
				this.getEventBodyMarkup(),
				'</dd>',
				this.enableEventResize ?
					'<div class="ext-evt-rsz"><div class="ext-evt-rsz-h">&#160;</div></div>' : '',
				'</dl>',
				'<div class="ext-cal-evb">&#160;</div>',
				'</div>'
			);
			this.eventTpl.compile();
		}
		return this.eventTpl;
	},

	/**
	 * <p>Returns the XTemplate that is bound to the calendar's event store (it expects records of type
	 * {@link Extensible.calendar.data.EventModel}) to populate the calendar views with <strong>all-day</strong> events.
	 * Internally this method by default generates different markup for browsers that support CSS border radius
	 * and those that don't. This method can be overridden as needed to customize the markup generated.</p>
	 * <p>Note that this method calls {@link #getEventBodyMarkup} to retrieve the body markup for events separately
	 * from the surrounding container markup.  This provdes the flexibility to customize what's in the body without
	 * having to override the entire XTemplate. If you do override this method, you should make sure that your
	 * overridden version also does the same.</p>
	 * @return {Ext6.XTemplate} The event XTemplate
	 */
	getEventAllDayTemplate: function () {
		if (!this.eventAllDayTpl) {
			var tpl, body = this.getEventBodyMarkup();

			tpl = !(Ext6.isIE || Ext6.isOpera) ?
				Ext6.create('Ext6.XTemplate',
					'<div class="{_extraCls} {spanCls} ext-cal-evt ext-cal-evr" ',
					'style="left: {_left}%; width: {_width}%; top: {_top}px; height: {_height}px;">',
					body,
					'</div>'
				)
				: Ext6.create('Ext6.XTemplate',
				'<div class="ext-cal-evt" ',
				'style="left: {_left}%; width: {_width}%; top: {_top}px; height: {_height}px;">',
				'<div class="{_extraCls} {spanCls} ext-cal-evo">',
				'<div class="ext-cal-evm">',
				'<div class="ext-cal-evi">',
				body,
				'</div>',
				'</div>',
				'</div>',
				'</div>'
			);
			tpl.compile();
			this.eventAllDayTpl = tpl;
		}
		return this.eventAllDayTpl;
	},

	// private
	getTemplateEventData: function (evt) {
		var M = Extensible.calendar.data.EventMappings,
			extraClasses = [this.getEventSelectorCls(evt['EvnDirection_id'])],
			data = {},
			colorCls = 'x-cal-default',
			title = evt[M.Title.name],
			fmt = 'G:i ',
			recurring = evt[M.RRule.name] != '';

		this.getTemplateEventBox(evt);

		if (this.calendarStore && evt[M.Resource_id.name]) {
			var rec = this.calendarStore.findRecord(Extensible.calendar.data.CalendarMappings.Resource_id.name,
				evt[M.Resource_id.name]);

			if (rec) {
				colorCls = 'x-cal-' + rec.data[Extensible.calendar.data.CalendarMappings.ColorId.name];
			}
		}
		colorCls += (evt._renderAsAllDay ? '-ad' : '') + (Ext6.isIE || Ext6.isOpera ? '-x' : '');
		extraClasses.push(colorCls);

		if (this.getEventClass) {
			var rec = this.getEventRecord(evt['EvnDirection_id']),
				cls = this.getEventClass(rec, !!evt._renderAsAllDay, data, this.store);
			extraClasses.push(cls);
		}

		data._extraCls = extraClasses.join(' ');
		data._isRecurring = evt.Recurrence && evt.Recurrence != '';
		data.Title = (!title || title.length == 0 ? this.defaultEventTitleText : title);

		return Ext6.applyIf(data, evt);
	},

	// private
	getEventPositionOffsets: function () {
		return {
			top: 0,
			height: -1
		}
	},

	// private
	getTemplateEventBox: function (evt) {
		var heightFactor = this.hourHeight / this.hourIncrement,
			start = evt[Extensible.calendar.data.EventMappings.StartDate.name],
			end = evt[Extensible.calendar.data.EventMappings.EndDate.name],
			startOffset = this.viewStartHourOffset + Math.max(start.getHours() * 60 / this.hourIncrement - this.viewStartHour, 0),
			endOffset = this.viewStartHourOffset + Math.min(end.getHours() * 60 / this.hourIncrement - this.viewStartHour, this.viewEndHour - this.viewStartHour),
			startMins = startOffset * this.hourIncrement,
			endMins = endOffset * this.hourIncrement,
			viewEndDt = Extensible.Date.add(Ext6.Date.clone(end), {hours: this.viewEndHour, clearTime: true}),
			evtOffsets = this.getEventPositionOffsets();

		/*if (start.getHours() >= this.viewStartHour) {
			// only add the minutes if the start is visible, otherwise it offsets the event incorrectly
			startMins += start.getMinutes();
		}
		if (end <= viewEndDt) {
			// only add the minutes if the end is visible, otherwise it offsets the event incorrectly
			endMins += end.getMinutes();
		}
		if (endMins < startMins) {
		 	endMins = this.viewEndHour * this.hourIncrement;
		}*/

		startMins += start.getMinutes();
		endMins += end.getMinutes();

		if (start.getTime() < this.dayView.getStartDate().getTime()) {
			// ушло в предыдущие сутки значит (график расширяется вверху)
			startMins = startMins - 1440;
		}

		if (end.getTime() > this.dayView.getStartDate().getTime() + 1440 * 60 * 1000) {
			// ушло в следующие сутки значит (график расширяется внизу)
			endMins = endMins + 1440;
		}

		evt._left = 0;
		evt._width = 100;
		evt._top = startMins * heightFactor + evtOffsets.top;
		evt._height = Math.max(((endMins - startMins) * heightFactor), this.minEventHeight) + evtOffsets.height;
	},

	// private
	renderItems: function () {
		var evts = [];

		this.calendarStore.each(function (rec) {
			var Resource_id = rec.get('Resource_id');
			var ev = emptyCells = skipped = 0,
				d = this.eventGrid[0][Resource_id],
				ct = d ? d.length : 0,
				evt;
			for (; ev < ct; ev++) {
				evt = d[ev];
				if (!evt) {
					continue;
				}
				var item = evt.data || evt.event.data,
					M = Extensible.calendar.data.EventMappings,
					ad = item[M.IsAllDay.name] === true,
					renderAsAllDay = ad;

				if (renderAsAllDay) {
					// this event is already rendered in the header view
					continue;
				}
				Ext6.apply(item, {
					cls: 'ext-cal-ev',
					_positioned: true
				});
				evts.push({
					data: this.getTemplateEventData(item),
					Resource_id: Resource_id
				});
			}
		}.createDelegate(this));

		// overlapping event pre-processing loop
		var i = j = 0, overlapCols = [], l = evts.length, prevDt;
		for (; i < l; i++) {
			var evt = evts[i].data,
				evt2 = null,
				dt = evt[Extensible.calendar.data.EventMappings.StartDate.name].getDate();

			for (j = 0; j < l; j++) {
				if (i == j)continue;
				evt2 = evts[j].data;
				if (this.isOverlapping(evt, evt2)) {
					evt._overlap = evt._overlap == undefined ? 1 : evt._overlap + 1;
					if (i < j) {
						if (evt._overcol === undefined) {
							evt._overcol = 0;
						}
						evt2._overcol = evt._overcol + 1;
						overlapCols[dt] = overlapCols[dt] ? Math.max(overlapCols[dt], evt2._overcol) : evt2._overcol;
					}
				}
			}
		}

		// rendering loop
		for (i = 0; i < l; i++) {
			var evt = evts[i].data,
				dt = evt[Extensible.calendar.data.EventMappings.StartDate.name].getDate();

			if (evt._overlap !== undefined) {
				var colWidth = 100 / (overlapCols[dt] + 1),
					evtWidth = 100 - (colWidth * evt._overlap);

				evt._width = colWidth;
				evt._left = colWidth * evt._overcol;
			}
			var operBrig = '';
			if (evt.OperBrig && typeof evt.OperBrig == 'object') {
				evt.OperBrig.forEach(function(onebrig) {
					operBrig = operBrig + (onebrig.conflicted?"<span class='conflicted'><img src='/img/icons/warn_red_round12.png' />&nbsp;":"") + onebrig.MedPersonal_ShortFio + " <span class='surgtype'>" + onebrig.SurgType_Name + "</span>" + (onebrig.conflicted?"</span>":"") + "<br />";
				});
			}
			evt.OperBrig = operBrig;
			var markup = this.getEventTemplate().apply(evt),
				target = this.id + '-day-col-' + evts[i].Resource_id;

			Ext6.DomHelper.append(target, markup);
		}

		this.fireEvent('eventsrendered', this);
	},

	// private
	getDayEl: function (Resource_id) {
		return Ext6.get(this.getDayId(Resource_id));
	},

	// private
	getDayId: function (Resource_id) {
		return this.id + this.dayColumnElIdDelimiter + Resource_id;
	},

	// private
	getDaySize: function () {
		var box = this.el.down('.ext-cal-day-col-inner').getBox();
		return {height: box.height, width: box.width};
	},

	// private
	getDayAt: function (x, y) {
		var sel = '.ext-cal-body-ct',
			xoffset = this.el.down('.ext-cal-day-times').getWidth(),
			viewBox = this.el.getBox(),
			daySize = this.getDaySize(false),
			relX = x - viewBox.x - xoffset,
			dayIndex = Math.floor(relX / daySize.width), // clicked col index
			scroll = this.el.getScroll(),
			row = this.el.down('.ext-cal-bg-row'), // first avail row, just to calc size
			rowH = row.getHeight() / this.incrementsPerHour,
			relY = y - viewBox.y - rowH + scroll.top,
			rowIndex = Math.max(0, Math.ceil(relY / rowH)),
			mins = rowIndex * (this.hourIncrement / this.incrementsPerHour),
			Resource_id = null,
			dt = Extensible.Date.add(this.viewStart, {minutes: mins, hours: (this.viewStartHour - (this.viewStartHourOffset * this.hourIncrement / 60))});

		this.calendarStore.each(function (rec) {
			if (dayIndex >= 0) {
				Resource_id = rec.get('Resource_id');
			}

			dayIndex--;
		});

		var el = this.getDayEl(Resource_id),
			timeX = x;

		if (el) {
			timeX = el.getLeft();
		}

		return {
			date: dt,
			Resource_id: Resource_id,
			el: el,
			// this is the box for the specific time block in the day that was clicked on:
			timeBox: {
				x: timeX,
				y: (rowIndex * this.hourHeight / this.incrementsPerHour) + viewBox.y - scroll.top,
				width: daySize.width,
				height: rowH
			}
		}
	},

	// private
	onClick: function (e, t) {
		if (this.dragPending || Extensible.calendar.view.DayBody.superclass.onClick.apply(this, arguments)) {
			// The superclass handled the click already so exit
			return;
		}
		if (e.getTarget('.ext-cal-day-times', 3) !== null) {
			// ignore clicks on the times-of-day gutter
			return;
		}
		var el = e.getTarget('td', 3);
		if (el) {
			if (el.id && el.id.indexOf(this.dayElIdDelimiter) > -1) {
				var dt = this.getDateFromId(el.id, this.dayElIdDelimiter);
				this.onDayClick(Ext6.Date.parseDate(dt, 'Ymd'), true, Ext6.get(this.getDayId(dt)));
				return;
			}
		}
		var day = this.getDayAt(e.getX(), e.getY());
		if (day && day.Resource_id) {
			this.onDayClick(day.Resource_id, false, null);
		}
	}
});
/**
 * @class Extensible.calendar.view.Day
 * @extends Ext6.container.Container
 * <p>Unlike other calendar views, is not actually a subclass of {@link Extensible.calendar.view.AbstractCalendar CalendarView}.
 * Instead it is a {@link Ext6.container.Container Container} subclass that internally creates and manages the layouts of
 * a {@link Extensible.calendar.view.DayHeader DayHeaderView} and a {@link Extensible.calendar.view.DayBody DayBodyView}. As such
 * DayView accepts any config values that are valid for DayHeaderView and DayBodyView and passes those through
 * to the contained views. It also supports the interface required of any calendar view and in turn calls methods
 * on the contained views as necessary.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext6.define('Extensible.calendar.view.Day', {
	extend: 'Ext6.container.Container',
	alias: 'widget.extensible.dayview',

	requires: [
		'Extensible.calendar.view.AbstractCalendar',
		'Extensible.calendar.view.DayHeader',
		'Extensible.calendar.view.DayBody'
	],

	/**
	 * @cfg {Boolean} readOnly
	 * True to prevent clicks on events or the view from providing CRUD capabilities, false to enable CRUD (the default).
	 */
	/**
	 * @cfg {Boolean} enableEventResize
	 * True to allow events in the view's scrolling body area to be updated by a resize handle at the
	 * bottom of the event, false to disallow it (defaults to true). If {@link #readOnly} is true event
	 * resizing will be disabled automatically.
	 */
	enableEventResize: true,
	/**
	 * @cfg {Integer} ddIncrement
	 * <p>The number of minutes between each step during various drag/drop operations in the view (defaults to 30).
	 * This controls the number of times the dragged object will "snap" to the view during a drag operation, and does
	 * not have to match with the time boundaries displayed in the view. E.g., the view could be displayed in 30 minute
	 * increments (the default) but you could configure ddIncrement to 10, which would snap a dragged object to the
	 * view at 10 minute increments.</p>
	 * <p>This config currently applies while dragging to move an event, resizing an event by its handle or dragging
	 * on the view to create a new event.</p>
	 */
	ddIncrement: 10,
	/**
	 * @cfg {Integer} minEventDisplayMinutes
	 * This is the minimum <b>display</b> height, in minutes, for events shown in the view (defaults to 30). This setting
	 * ensures that events with short duration are still readable (e.g., by default any event where the start and end
	 * times were the same would have 0 height). It also applies when calculating whether multiple events should be
	 * displayed as overlapping. In datetime terms, an event that starts and ends at 9:00 and another event that starts
	 * and ends at 9:05 do not overlap, but visually the second event would obscure the first in the view. This setting
	 * provides a way to ensure that such events will still be calculated as overlapping and displayed correctly.
	 */
	minEventDisplayMinutes: 10,
	/**
	 * @cfg {Boolean} showHourSeparator
	 * True to display a dotted line that separates each hour block in the scrolling body area at the half-hour mark
	 * (the default), false to hide it.
	 */
	showHourSeparator: true,
	/**
	 * @cfg {Integer} viewStartHour
	 * The hour of the day at which to begin the scrolling body area's times (defaults to 0, which equals early 12am / 00:00).
	 * Valid values are integers from 0 to 24, but should be less than the value of {@link viewEndHour}.
	 */
	viewStartHour: 0,
	/**
	 * @cfg {Integer} viewEndHour
	 * The hour of the day at which to end the scrolling body area's times (defaults to 24, which equals late 12am / 00:00).
	 * Valid values are integers from 0 to 24, but should be greater than the value of {@link viewStartHour}.
	 */
	viewEndHour: 24,
	/**
	 * @cfg {Integer} scrollStartHour
	 * The default hour of the day at which to set the body scroll position on view load (defaults to 7, which equals 7am / 07:00).
	 * Note that if the body is not sufficiently overflowed to allow this positioning this setting will have no effect.
	 * This setting should be equal to or greater than {@link viewStartHour}.
	 */
	scrollStartHour: 8,
	/**
	 * @cfg {Integer} hourHeight
	 * <p>The height, in pixels, of each hour block displayed in the scrolling body area of the view (defaults to 42).</p>
	 * <br><p><b>Important note:</b> While this config can be set to any reasonable integer value, note that it is also used to
	 * calculate the ratio used when assigning event heights. By default, an hour is 60 minutes and 42 pixels high, so the
	 * pixel-to-minute ratio is 42 / 60, or 0.7. This same ratio is then used when rendering events. When rendering a
	 * 30 minute event, the rendered height would be 30 minutes * 0.7 = 21 pixels (as expected).</p>
	 * <p>This is important to understand when changing this value because some browsers may handle pixel rounding in
	 * different ways which could lead to inconsistent visual results in some cases. If you have any problems with pixel
	 * precision in how events are laid out, you might try to stick with hourHeight values that will generate discreet ratios.
	 * This is easily done by simply multiplying 60 minutes by different discreet ratios (.6, .8, 1.1, etc.) to get the
	 * corresponding hourHeight pixel values (36, 48, 66, etc.) that will map back to those ratios. By contrast, if you
	 * chose an hourHeight of 50 for example, the resulting height ratio would be 50 / 60 = .833333... This will work just
	 * fine, just be aware that browsers may sometimes round the resulting height values inconsistently.
	 */
	hourHeight: 126,
	/**
	 * @cfg {String} hideMode
	 * <p>How this component should be hidden. Supported values are <tt>'visibility'</tt>
	 * (css visibility), <tt>'offsets'</tt> (negative offset position) and <tt>'display'</tt>
	 * (css display).</p>
	 * <br><p><b>Note</b>: For calendar views the default is 'offsets' rather than the Ext JS default of
	 * 'display' in order to preserve scroll position after hiding/showing a scrollable view like Day or Week.</p>
	 */
	hideMode: 'offsets',

	// private
	initComponent: function () {
		/**
		 * @cfg {String} ddCreateEventText
		 * The text to display inside the drag proxy while dragging over the calendar to create a new event (defaults to
		 * 'Create event for {0}' where {0} is a date range supplied by the view)
		 */
		this.ddCreateEventText = this.ddCreateEventText || Extensible.calendar.view.AbstractCalendar.prototype.ddCreateEventText;
		/**
		 * @cfg {String} ddMoveEventText
		 * The text to display inside the drag proxy while dragging an event to reposition it (defaults to
		 * 'Move event to {0}' where {0} is the updated event start date/time supplied by the view)
		 */
		this.ddMoveEventText = this.ddMoveEventText || Extensible.calendar.view.AbstractCalendar.prototype.ddMoveEventText;

		var cfg = Ext6.apply({}, this.initialConfig);
		cfg.readOnly = this.readOnly;
		cfg.ddIncrement = this.ddIncrement;
		cfg.minEventDisplayMinutes = this.minEventDisplayMinutes;
		cfg.ownerWin = this.ownerWin;

		this.header = Ext6.applyIf({
			xtype: 'extensible.dayheaderview',
			id: this.id + '-hd',
			ownerCalendarPanel: this.ownerCalendarPanel
		}, cfg);

		this.body = Ext6.applyIf({
			xtype: 'extensible.daybodyview',
			ddGroup: 'DayViewDD',
			enableEventResize: this.enableEventResize,
			showHourSeparator: this.showHourSeparator,
			viewStartHour: this.viewStartHour,
			viewEndHour: this.viewEndHour,
			scrollStartHour: this.scrollStartHour,
			hourHeight: this.hourHeight,
			id: this.id + '-bd',
			dayView: this,
			ownerCalendarPanel: this.ownerCalendarPanel
		}, cfg);

		this.items = [this.header, this.body];
		this.addCls('ext-cal-dayview ext-cal-ct');

		this.callParent(arguments);
	},

	// private
	afterRender: function () {
		this.callParent(arguments);

		this.header = Ext6.getCmp(this.id + '-hd');
		this.body = Ext6.getCmp(this.id + '-bd');

		this.body.on('eventsrendered', this.forceSize, this);
		this.on('resize', this.onResize, this);
	},

	// private
	refresh: function () {
		Extensible.log('refresh (DayView)');
		this.header.refresh();
		this.body.refresh();
	},

	// private
	forceSize: function () {
		// The defer call is mainly for good ol' IE, but it doesn't hurt in
		// general to make sure that the window resize is good and done first
		// so that we can properly calculate sizes.
		Ext6.defer(function () {
			var ct = this.el.up('.x6-panel-body'),
				hd = this.el.down('.ext-cal-day-header'),
				h = ct.getHeight() - hd.getHeight();

			this.el.down('.ext-cal-body-ct').setHeight(h - 1);
		}, 1, this);
	},

	// private
	onResize: function () {
		this.forceSize();
		Ext6.defer(this.refresh, Ext6.isIE ? 1 : 0, this); //IE needs the defer
	},

	/*
	 * We have to "relay" this Component method so that the hidden
	 * state will be properly reflected when the views' active state changes
	 */
	doHide: function () {
		this.header.doHide.apply(this, arguments);
		this.body.doHide.apply(this, arguments);
	},

	// private
	getViewBounds: function () {
		return this.header.getViewBounds();
	},

	/**
	 * Returns the start date of the view, as set by {@link #setStartDate}. Note that this may not
	 * be the first date displayed in the rendered calendar -- to get the start and end dates displayed
	 * to the user use {@link #getViewBounds}.
	 * @return {Date} The start date
	 */
	getStartDate: function () {
		return this.header.getStartDate();
	},

	/**
	 * Sets the start date used to calculate the view boundaries to display. The displayed view will be the
	 * earliest and latest dates that match the view requirements and contain the date passed to this function.
	 * @param {Date} dt The date used to calculate the new view boundaries
	 */
	setStartDate: function (dt) {
		this.header.setStartDate(dt, true);
		this.body.setStartDate(dt);
	},

	// private
	renderItems: function () {
		this.header.renderItems();
		this.body.renderItems();
	}
});
/**
 * @class Extensible.calendar.CalendarPanel
 * @extends Ext6.panel.Panel
 * <p>This is the default container for calendar views. It supports day, week, multi-week and month views as well
 * as a built-in event edit form. The only requirement for displaying a calendar is passing in a valid
 * {@link #Ext6.data.Store store} config containing records of type {@link Extensible.calendar.data.EventModel EventRecord}.</p>
 * @constructor
 * @param {Object} config The config object
 * @xtype extensible.calendarpanel
 */
Ext6.define('Extensible.calendar.CalendarPanel', {
	ownerWin: null,
	extend: 'Ext6.panel.Panel',
	alias: 'widget.extensible.calendarpanel',

	requires: [
		'Ext6.layout.container.Card',
		'Extensible.calendar.view.Day'
	],

	/**
	 * @cfg {Number} activeItem
	 * The 0-based index within the available views to set as the default active view (defaults to undefined). If not
	 * specified the default view will be set as the last one added to the panel. You can retrieve a reference to the
	 * active {@link Extensible.calendar.view.AbstractCalendar view} at any time using the {@link #activeView} property.
	 */
	/*
	 * @cfg {Boolean} enableRecurrence
	 * True to show the recurrence field, false to hide it (default). Note that recurrence requires
	 * something on the server-side that can parse the iCal RRULE format in order to generate the
	 * instances of recurring events to display on the calendar, so this field should only be enabled
	 * if the server supports it.
	 */
	enableRecurrence: false, // not currently implemented
	/**
	 * @cfg {Boolean} readOnly
	 * True to prevent clicks on events or calendar views from providing CRUD capabilities, false to enable CRUD
	 * (the default). This option is passed into all views managed by this CalendarPanel.
	 */
	readOnly: false,
	/**
	 * @cfg {String} dayText
	 * Text to use for the 'Day' nav bar button.
	 */
	dayText: 'Day',
	/**
	 * @cfg {String} weekText
	 * Text to use for the 'Week' nav bar button.
	 */
	weekText: 'Week',
	/**
	 * @cfg {String} monthText
	 * Text to use for the 'Month' nav bar button.
	 */
	monthText: 'Month',
	/**
	 * @cfg {Boolean} editModal
	 * True to show the default event editor window modally over the entire page, false to allow user interaction with the page
	 * while showing the window (the default). Note that if you replace the default editor window with some alternate component this
	 * config will no longer apply.
	 */
	editModal: false,

	/**
	 * @cfg {Ext6.data.Store} eventStore
	 * The {@link Ext6.data.Store store} which is bound to this calendar and contains {@link Extensible.calendar.data.EventModel EventModels}.
	 * Note that this is an alias to the default {@link #store} config (to differentiate that from the optional {@link #calendarStore}
	 * config), and either can be used interchangeably.
	 */
	/**
	 * @cfg {Ext6.data.Store} calendarStore
	 * The {@link Ext6.data.Store store} which is bound to this calendar and contains {@link Extensible.calendar.data.CalendarModel CalendarModelss}.
	 * This is an optional store that provides multi-calendar (and multi-color) support. If available an additional field for selecting the
	 * calendar in which to save an event will be shown in the edit forms. If this store is not available then all events will simply use
	 * the default calendar (and color).
	 */
	/**
	 * @cfg {Object} viewConfig
	 * A config object that will be applied to all {@link Extensible.calendar.view.AbstractCalendar views} managed by this CalendarPanel. Any
	 * options on this object that do not apply to any particular view will simply be ignored.
	 */
	/**
	 * @cfg {Object} dayViewCfg
	 * A config object that will be applied only to the {@link Extensible.calendar.view.Day DayView} managed by this CalendarPanel.
	 */
	/**
	 * @cfg {Object} editViewCfg
	 * A config object that will be applied only to the {@link Extensible.calendar.form.EventDetails EventEditForm} managed by this CalendarPanel.
	 */

	/**
	 * A reference to the {@link Extensible.calendar.view.AbstractCalendar view} that is currently active.
	 * @type {Extensible.calendar.view.AbstractCalendar}
	 * @property activeView
	 */

	// private
	layout: {
		type: 'card',
		deferredRender: true
	},

	// private property
	startDate: new Date(),

	checkScrollToFirst: function() {
		var calendar = this;
		if (calendar.scrollToFirst) {
			// скроллим к первой операции, если её нет то к 8:00
			var curDay = calendar.startDate.format("d"),
				offset = calendar.activeView.body.viewStartHourOffset,
				startHour = Math.max(calendar.activeView.scrollStartHour, calendar.activeView.viewStartHour),
				scrollStart = Math.max(0, offset + startHour - calendar.activeView.viewStartHour);

			// ищем первую операцию в графике
			var operStart = null;
			calendar.getEl().query(".ext-cal-evt").forEach(function(div) {
				if (operStart == null || Ext6.get(div).getLocalY() < operStart) {
					operStart = Ext6.get(div).getLocalY();
				}
			});
			if (operStart != null) {
				calendar.activeView.body.scrollTo(operStart);
			} else if (scrollStart >= 0) {
				calendar.activeView.body.scrollTo(scrollStart * (60 / calendar.activeView.body.hourIncrement) * calendar.activeView.body.hourHeight);
			}

			calendar.scrollToFirst = false;
		}
	},

	// private
	initComponent: function () {
		this.activeItem = 0;
		this.addCls('x-calendar-nonav');

		this.callParent(arguments);

		this.addCls('x-cal-panel');

		if (this.eventStore) {
			this.store = this.eventStore;
			delete this.eventStore;
		}
		this.setStore(this.store);

		var sharedViewCfg = {
			ownerWin: this.ownerWin,
			showToday: this.showToday,
			readOnly: this.readOnly,
			enableRecurrence: this.enableRecurrence,
			store: this.store,
			calendarStore: this.calendarStore,
			editModal: this.editModal,
			ownerCalendarPanel: this
		};

		var mday = Ext6.apply({
			xtype: 'extensible.dayview',
			title: ''
		}, sharedViewCfg);

		mday = Ext6.apply(Ext6.apply(mday, this.viewConfig), this.dayViewCfg);
		mday.id = this.id + '-multiday';
		this.initEventRelay(mday);
		this.add(mday);
	},

	// private
	initEventRelay: function (cfg) {
		cfg.listeners = cfg.listeners || {};
		cfg.listeners.afterrender = {
			fn: function (c) {
				// relay view events so that app code only has to handle them in one place.
				// these events require no special handling by the calendar panel
				this.relayEvents(c, ['eventsrendered', 'eventclick', 'dayclick', 'eventover', 'eventout', 'beforedatechange',
					'datechange', 'rangeselect', 'beforeeventmove', 'eventmove', 'initdrag', 'dayover', 'dayout', 'beforeeventresize',
					'eventresize', 'eventadd', 'eventupdate', 'beforeeventdelete', 'eventdelete', 'eventcancel']);
			},
			scope: this,
			single: true
		}
	},

	// private
	afterRender: function () {
		this.callParent(arguments);

		this.body.addCls('x-cal-body');
		this.setActiveView();
	},

	/**
	 * Sets the event store used by the calendar to display {@link Extensible.calendar.data.EventModel events}.
	 * @param {Ext6.data.Store} store
	 */
	setStore: function (store, initial) {
		var currStore = this.store;

		if (!initial && currStore) {
			currStore.un("write", this.onWrite, this);
		}
		if (store) {
			store.on("write", this.onWrite, this);
		}
		this.store = store;
	},

	// private
	onStoreAdd: function (ds, rec, index) {
	},

	// private
	onStoreUpdate: function (ds, rec, operation) {
		if (operation == Ext6.data.Record.COMMIT) {

		}
	},

	// private
	onStoreRemove: function (ds, rec) {

	},

	// private
	onWrite: function (store, operation) {
		var rec = operation._records[0];

		switch (operation.action) {
			case 'create':
				this.onStoreAdd(store, rec);
				break;
			case 'update':
				this.onStoreUpdate(store, rec, Ext6.data.Record.COMMIT);
				break;
			case 'destroy':
				this.onStoreRemove(store, rec);
				break;
		}
	},
	/**
	 * Set the active view, optionally specifying a new start date.
	 * @param {String} id The id of the view to activate
	 * @param {Date} startDate (optional) The new view start date (defaults to the current start date)
	 */
	setActiveView: function (id, startDate) {
		var me = this,
			layout = me.layout,
			editViewId = me.id + '-edit',
			toolbar;

		if (startDate) {
			me.startDate = startDate;
		}

		// Make sure we're actually changing views
		if (id !== layout.getActiveItem().id) {
			// Show/hide the toolbar first so that the layout will calculate the correct item size
			toolbar = me.getDockedItems('toolbar')[0];
			if (toolbar) {
				toolbar[id === editViewId ? 'hide' : 'show']();
			}

			// Activate the new view and refresh the layout
			layout.setActiveItem(id || me.activeItem);
			// me.doComponentLayout();
			me.activeView = layout.getActiveItem();

			if (id !== editViewId) {
				if (id && id !== me.preEditView) {
					// We're changing to a different view, so the view dates are likely different.
					// Re-set the start date so that the view range will be updated if needed.
					// If id is undefined, it means this is the initial pass after render so we can
					// skip this (as we don't want to cause a duplicate forced reload).
					layout.activeItem.setStartDate(me.startDate, true);
				}
			}
			// Notify any listeners that the view changed
			me.fireViewChange();
		}
	},

	// private
	fireViewChange: function () {
		if (this.layout && this.layout.getActiveItem) {
			var view = this.layout.getActiveItem(),
				cloneDt = Ext6.Date.clone;

			if (view) {
				if (view.getViewBounds) {
					var vb = view.getViewBounds(),
						info = {
							activeDate: cloneDt(view.getStartDate()),
							viewStart: cloneDt(vb.start),
							viewEnd: cloneDt(vb.end)
						};
				}
				this.fireEvent('viewchange', this, view, info);
			}
		}
	},

	/**
	 * Sets the start date for the currently-active calendar view.
	 * @param {Date} dt The new start date
	 * @return {Extensible.calendar.CalendarPanel} this
	 */
	setStartDate: function (dt) {
		Extensible.log('setStartDate (CalendarPanel');
		this.startDate = dt;
		this.layout.activeItem.setStartDate(dt, true);
		this.fireViewChange();
		return this;
	},

	/**
	 * Return the calendar view that is currently active, which will be a subclass of
	 * {@link Extensible.calendar.view.AbstractCalendar CalendarView}.
	 * @return {Extensible.calendar.view.AbstractCalendar} The active view
	 */
	getActiveView: function () {
		return this.layout.activeItem;
	}
});