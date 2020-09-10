Ext6.define('common.EMK.tools.swTimetableSelectionWindow', {
	extend: 'base.DropdownPanel',
	width: 570,
	/*height: 425,
	minHeight: 350,*/
	focusOnToFront: true,
	userCls: 'timetable-selection',
	parentPanel: '',
	clickTTRec: null,
	EvnPrescr_id: null,
	resizable: false,
	params: {},
	onSelect: Ext6.emptyFn,
	enableRefreshScroll: false,
	months: [
		'января',
		'февраля',
		'марта',
		'апреля',
		'мая',
		'июня',
		'июля',
		'августа',
		'сентября',
		'октября',
		'ноября',
		'декабря'
	],
	
	// Стандартная высота таблицы с расписанием (когда не отображается примечание):
	_timesBoundListHeight: 166,
	
	prevDay: function() {
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	stepDay: function(day)
	{
		var me = this;
		var date = (me.TimetableDate.getValue() || Date.parseDate(me.StartDay, 'd.m.Y')).add(Date.DAY, day).clearTime();
		me.TimetableDate.setValue(date);
	},
	checkAllowDayPrev: function(){
		var allow = true,
			me = this;
		var now = new Date().format('d.m.Y');
		if(now === me.StartDay){
			allow = false;
			sw.swMsg.alert(ERR_WND_TIT, langs('Запись на прошедшие дни невозможна'));
		}
		return allow;
	},
	loadTimetable: function (rec,StartDay) {
		var me = this,
			store = me.TimetableStore;
		store.removeAll();
		if(rec){
			me.MedService_id = rec.get('withResource') == 1? null : rec.get('MedService_id');
			//для подгрузки расписания пункта забора, если у нас используется ресурс, нужны другие функции
			me.pzm_MedService_id = rec.get('withResource') == 1? null : rec.get('pzm_MedService_id');
			me.Resource_id = rec.get('Resource_id');
			me.UslugaComplex_id = rec.get('UslugaComplex_id');//для того, чтобы подгружать примечание для услуги
			me.UslugaComplexMedService_id = rec.get('UslugaComplexMedService_id');//может быть расписание у услуги, у службы
			me.pzm_UslugaComplexMedService_id = rec.get('pzm_UslugaComplexMedService_id');//может быть расписание у услуги, у ПЗ
			if(StartDay){
				me.StartDay = StartDay;
			} else {
				if(rec.get('TimetableMedService_begTime'))
					me.StartDay = rec.get('TimetableMedService_begTime');
				if(rec.get('TimetableResource_begTime'))
					me.StartDay = rec.get('TimetableResource_begTime');
				if(!me.StartDay)
					me.StartDay = new Date();
			}
			switch (typeof me.StartDay) {
				case 'object':
					me.StartDay = me.StartDay.format('d.m.Y');
					break;
				case 'string':
					me.StartDay = me.StartDay.substr(0, 10);
					break;
			}
		} else {
			me.StartDay = me.TimetableDate.getValue().format('d.m.Y')
		}

		var extraParams = {
			MedService_id: me.MedService_id || null,
			pzm_MedService_id: me.pzm_MedService_id || null,
			Resource_id: me.Resource_id || null,
			UslugaComplexMedService_id: me.UslugaComplexMedService_id || null,
			pzm_UslugaComplexMedService_id: me.pzm_UslugaComplexMedService_id || null,
			StartDay: me.StartDay
		};
		store.proxy.extraParams = extraParams;

		if(Ext6.isEmpty(me.StartDay)
			|| (Ext6.isEmpty(me.Resource_id)
				&& Ext6.isEmpty(me.MedService_id)
				&& Ext6.isEmpty(me.pzm_MedService_id)
				&& Ext6.isEmpty(me.UslugaComplexMedService_id)
				&& Ext6.isEmpty(me.pzm_UslugaComplexMedService_id))
		){
			sw.swMsg.alert(ERR_WND_TIT, langs('Необходим день и служба, услуга или ресурс'));
			return false;
		}

		var date = (Date.parseDate(me.StartDay, 'd.m.Y')).clearTime();
		me.TimetableDate.setValue(date);

		store.load();
		me.loadAnnotation(extraParams);
	},
	loadAnnotation: function (extraParams) {
		var me = this;
		extraParams.Lpu_id = getGlobalOptions().lpu_id;
		Ext6.Ajax.request({
			url: '/?c=TimetableMedService&m=loadAnnotateByDay',
			callback: function(opt, success, response) {
				var annotateField = me.down('#annotate');
				var tbl = me.down('#timesBoundList'); // таблица с расписанием
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj && response_obj.success &&
					!Ext6.isEmpty(response_obj.annotate)) {
					annotateField.setValue('<p class="day-select-description" style="padding-left: 16px;">Примечание врача общее или на день: '+response_obj.annotate+'</p>');
					annotateField.show();
					
					// Примечание есть - на таблицу с расписанием остается меньше места по высоте,
					// уменьшим стандартную высоту расписания на высоту и margin (15) примечания:
					tbl.setHeight(me._timesBoundListHeight - annotateField.getHeight() - 15);
				} else {
					annotateField.hide();
					
					// Примечания нет - установим стандартную высоту таблицы с расписанием:
					tbl.setHeight(me._timesBoundListHeight);
				}
			},
			params: extraParams
		});
	},
	show: function(data) {
		var me = this;
		me.callParent(arguments);
		me.UslugaComplex_id = data.UslugaComplex_id;
		me.callback = data.callback || null;
		me.clickTTRec = data.rec || null;
		if(data.rec) {
			me.EvnPrescr_id = data.rec.get('EvnPrescr_id') || null;
			if(data.toQueueAccess) {
				me.toQueueBtn.show();
			} else {
				me.toQueueBtn.hide();
			}
		}
		me.loadTimetable(data.rec,(data.StartDay?data.StartDay:null));
	},
	initComponent: function () {
		var me = this;

		this.TimetableStore = Ext6.create('Ext6.data.Store', {
			fields: [
				// Общие поля
				{ name: 'UniqueId', type: 'int', calculate: function (data) {
					return data.TimetableResource_id ? data.TimetableResource_id : data.TimetableMedService_id;
				}},
				{ name: 'Person_id', type: 'int'},
				{ name: 'TimetableType_id', type: 'int'},
				{ name: 'IsDop', type: 'int'},
				{ name: 'class', type: 'string'},
				{ name: 'formatTime', type: 'string', calculate: function (data) {
					var dtStr = '';
					var dt = data.TimetableResource_begTime ? data.TimetableResource_begTime : data.TimetableMedService_begTime;
					if(typeof dt == 'object')
						dtStr = dt.format('H:i');
					return dtStr;
				}},
				{ name: 'dataIndex', type: 'string', calculate: function (data) {
					var dtStr = '';
					var dt = data.TimetableResource_begTime ? data.TimetableResource_begTime : data.TimetableMedService_begTime;
					if(typeof dt == 'object')
						dtStr = dt.format('Ymd');
					return dtStr;
				}},
				{ name: 'dateFormat', type: 'string', calculate: function (data) {
					var dtStr = '';
					var dt = data.TimetableResource_begTime ? data.TimetableResource_begTime : data.TimetableMedService_begTime;
					if(typeof dt == 'object')
						dtStr = dt.format('d.m.Y H:i');
					return dtStr;
				}},
				{ name: 'description', type: 'auto', defaultValue: null},
				{ name: 'notAccepted', mapping: 'Person_id', type: 'bool', /*defaultValue: true,*/ convert: function (Person_id) {
					return !Ext6.isEmpty(Person_id);
				}},
				{ name: 'class', mapping: 'Person_id', type: 'string', /*defaultValue: true,*/ convert: function (Person_id) {
					return Ext6.isEmpty(Person_id)?'free':'recorded';
				}},
				// Для ресурса
				{ name: 'TimetableResource_id', type: 'int'},
				{ name: 'TimetableResource_Day', type: 'int'},
				{ name: 'TimetableResource_begTime', type: 'date', dateFormat: 'Y-m-d H:i:s'},//дата выполненной услуги
				// Для службы
				{ name: 'TimetableMedService_id', type: 'int'},
				{ name: 'TimetableMedService_Day', type: 'int'},
				{ name: 'TimetableMedService_begTime', type: 'date', dateFormat: 'Y-m-d H:i:s'},//дата выполненной услуги
				// Для услуги
				{ name: 'UslugaComplex_id', type: 'int'},
				{ name: 'UslugaComplexMedService_id', type: 'int'},
				{ name: 'pzm_UslugaComplexMedService_id', type: 'int'},
				{ name: 'UslugaComplexMedService_begDT', type: 'date', dateFormat: 'Y-m-d H:i:s'},
				{ name: 'pzm_UslugaComplexMedService_begDT', type: 'date', dateFormat: 'Y-m-d H:i:s'}
			],
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=TimetableMedService&m=loadTTListByDay',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			listeners: {
				load: function(store, records, successful, operation, eOpts){
					me.unmask();
				}
			}
		});

		this.toQueueBtn = Ext6.create('Ext6.button.Button', {
			text: 'Поставить в очередь',
			cls: 'btn-swMsg-minor-super-flat',
			handler: function () {
				me.onSelect();
				me.hide();
			}
		});

		this.TimetableDate = Ext6.create('swDateField',{
			format: 'd.m.Y',
			startDay: 1,
			hidden: true,
			minValue: new Date(),
			listeners: {
				'change': function(field,ndate,odate) {
					me.displayDate.setValue('<p style="text-align: center;margin: 0">' + ndate.getDate() + ' ' + me.months[ndate.getMonth()] + ', ' + ndate.toLocaleString('ru', {
						weekday: 'long'
					}) + '</p>');
				}
			}
		});

		this.displayDate = Ext6.create('Ext6.form.field.Display',{
			value: '',
			itemId: 'displayDate',
			width: 154,
			height: 16,
			cls: 'line-date-text',
			style: {
				display: 'block'
			}
		});

		Ext6.apply(me, {
			height: 250,
			panel: [
				{
					xtype: 'panel',
					border: false,
					bodyStyle: {
						border: 'none'
					},
					items: [
						{
							margin: '0 15 15',
							xtype: 'displayfield',
							itemId: 'annotate',
							border: false,
							hidden: true,
							value: '<p class="day-select-description" style="padding-left: 16px;">Примечание врача общее или на день: </p>'
						},
						{
							xtype: 'boundlist',
							cls: 'select-another-date',
							height: 166,
							border: false,
							itemId: 'timesBoundList',
							width: 570,
							store: me.TimetableStore,
							tpl: Ext6.create('Ext6.XTemplate',
								'<tpl for=".">',
								'<tpl if="notAccepted">',

								'<li role="option" class=" x-boundlist-item {class} ">',
								'<p class="date-time" style="margin: 0;">',
								'{formatTime}',
								'</p>',
								'</li>',

								'<tpl else>',

								'<tpl if="description == null">',
								'<li role="option" class=" x-boundlist-item {class} "><div data-id="{UniqueId}" class="accept-point" style="position: static;" data-qtip="Выбрать">',
								'<p class="date-time select-menu-day" style="margin: 0;">{formatTime}',
								'</p></div>',
								'<tpl else>',
								'<li role="option" class=" x-boundlist-item {class} "><div data-id="{UniqueId}" class="accept-point" style="position: static;" data-qtip="Выбрать"><p class="date-time select-menu-day" style="margin: 0;">{formatTime}</p></div>',
								'<div class="date-description" data-qtip="Текст примечания на бирку: {description}">',
								'</div>',
								'</li>',
								'</tpl>',

								'</tpl>',
								'</tpl>'),
							listeners: {
								render: function () {
									var body = this.getEl();
									body.on('click', function (e, t) {
										me.selRec = null;
										var deselectAllFn = function(){
											body.select('div.accept-point').elements.forEach(function (el, index) {
												el.classList.remove('accepted');
												el.setAttribute('data-qtip', 'Выбрать');
											});
										};
										if('accepted'.inlist(t.classList)){
											deselectAllFn();
										} else {
											deselectAllFn();
											t.classList.add('accepted');
											t.setAttribute('data-qtip', 'Отменить выбор');
											var UniqueId = t.getAttribute('data-id');
											me.selRec = me.TimetableStore.findRecord('UniqueId',UniqueId);
										}
										me.down("#acceptChanges").setDisabled(!('accepted'.inlist(t.classList)));

									}, null, {
										delegate: 'div.accept-point'
									})
								}
							}
						}
					],
					dockedItems: [
						{
							xtype: 'toolbar',
							dock: 'top',
							border: false,
							items: [
								{
									xtype: 'tool',
									type: 'day-prev',
									handler: function () {
										if(me.checkAllowDayPrev()){
											me.prevDay();
											me.loadTimetable();
										}
									}
								},
								me.TimetableDate,
								me.displayDate,
								{
									xtype: 'tool',
									type: 'day-next',
									handler: function () {
										me.nextDay();
										me.loadTimetable();
									}
								}
							]
						}, {
							xtype: 'toolbar',
							dock: 'bottom',
							align: 'right',
							border: false,
							items: [{
								xtype: 'displayfield',
								hidden: true,
								value: ''
							}, '->'
								//, me.toQueueBtn
								, {
								xtype: 'button',
								itemId: 'acceptChanges',
								text: 'Выбрать',
								cls: 'btn-swMsg-main-super-flat',
								disabled: true,
								handler: function () {

									var selRec = me.selRec;

									//me.showTarget.innerHTML = data.dom.childNodes[0].innerHTML;
									me.onSelect(selRec,me.EvnPrescr_id);
									me.hide();

									/*Ext6.get(me.showTarget.parentNode).down('div.accept-point').addCls('accepted');
									Ext6.get(me.showTarget.parentNode).down('div.accept-point').dom.setAttribute('data-qtip', 'Отменить запись');*/
									me.selRec = null;
									this.setDisabled(true);
								}
							}, {
								xtype: 'button',
								text: 'Закрыть',
								cls: 'btn-swMsg-minor-super-flat',
								handler: function () {
									me.hide();
								}
							}]
						}]
				}],
			count: new Date().getDate()
		});

		this.callParent(arguments);
	}
});