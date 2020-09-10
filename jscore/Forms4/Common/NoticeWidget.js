/**
* Виджет уведомлений (колокольчик)
* размещается на главном тулбаре Промеда
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/

Ext6.define('NoticeWidgetPanel', {
	extend: 'Ext6.Panel',
	title: '',
	userCls: 'notice-scroll',
	autoScroll: true,
	minSize: 400,
	width: '100%',
	height: 340,
	region: 'center',
	layout: 'border',
	border: false,
	preserveScrollOnRefresh: true,
	params: {
		start: 0,
		limit: 10
	},
	defaultParams: {
		start: 0,
		limit: 10
	},
	loading_next: false,
	userMedStaffFact: null,
	afterOpenEmk: null,
	//~ loadMask: null,
	
	unReadCount: 0,
	msgCount: 0,
	tpl: null,
	tpl_norm: new Ext6.XTemplate(
				'<div style="height:10px;"></div>',
				'<tpl for="list">',
				'<div class="PEJ_list">',
					'<tpl for="data">',
						'<div class="eventcase" >',
							'<ul>',
								'{% isRead=values.Message_isRead; %}',
								'<li>{[ isRead ? "": "<b>" ]} {[this.formatDate(values.Message_setDT)]} {[ isRead ? "": "</b>" ]}</li>',
								'<li class="PEJ_header">Пациент: <b><span style="color: #f44336;">{subcaption}</span>{caption}</b></li>',
								'<li><div style="height: 15px; overflow-y:hidden;">{EvnClass_Name} (выполнено)  <a evn_id="{Evn_id}" personevn_id="{PersonEvn_id}" person_id="{Person_id}" server_id="{Server_id}" evnclass_sysnick="{EvnClass_SysNick}"  href="#" onclick="main_notice_widget.showResults(this);">Результаты</a></div></li>',
							'</ul>',
						'</div>',
					'</tpl>',
					'</div>',
				'</div>',
				'</tpl>',
				{
					formatDate: function(date) {
						var now = new Date();
						if(typeof date != 'object') date = Ext6.Date.parse(date, "d.m.Y H:i"); //Ext6.Date.parse(date+" +0000", "d.m.Y H:i O");
						if( Ext6.util.Format.date(now, 'd.m.Y') == Ext6.util.Format.date(date, 'd.m.Y') ) {
							return Ext6.util.Format.date( date, 'H : i');
						} else if(now.getFullYear() == date.getFullYear()) {
							var month = Ext6.util.Format.date(date, 'F').toLowerCase();
							if(date.getMonth() == 2 || date.getMonth() == 7) month+='а';
							else month = month.substr(0,month.length-1)+'я';
							return date.getDate()+' '+month;
						} else return Ext6.util.Format.date(date, 'd.m.Y');
					}
				}
			),
	tpl_empty: new Ext6.XTemplate('<div class="PEJ_empty">',
		'<div style="height:30px !important;">',
			'Нет результатов.',
		'</div>',
	'</div>'),
	store: new Ext6.data.SimpleStore({
		autoLoad: false,
		fields: [],
		data: [],
		complete1: function(data) {
			if (typeof(data) != 'object') return false;
			var record = null;
			var fio='';
			for (var i = 0; i < data.length; i++) {
				record = new Ext6.data.Record(data[i]);
				record.id = data[i].Message_id;
				record.datetime = data[i].Message_setDT;
				record.text = data[i].Message_Text;
				this.add(record);
			}
		}
	}),
	
	openEmk: function(params) {

	},

	getData: function(object, object_id) {
		var record = this.store.getById(object+'_'+object_id);
		if (record && record.data) {
			return record.data;
		} else {
			return false;
		}
	},

	actionParams: {},
	actions: {},

	refreshPersonNoticeButtons: function() {
		var btn_on = Ext6.get(this.id).select('.EvnJournal_PersonNotice_On');
		var btn_off = Ext6.get(this.id).select('.EvnJournal_PersonNotice_Off');

		if (this.params.PersonNotice_IsSend == 1) {
			btn_off.setStyle({display: 'inline-block'});
			btn_on.setStyle({display: 'none'});
		} else {
			btn_off.setStyle({display: 'none'});
			btn_on.setStyle({display: 'inline-block'});
		}
	},

	setActionHandler: function(action_name, action) {
		this.actions[action_name]['handler'] = action;
	},
	setActionParams: function(action_name, params) {
		this.actionParams[action_name] = params;
	},
	setActionParam: function(action_name, param, value) {
		if (!this.actionParams[action_name]) {
			this.actionParams[action_name] = new Object();
		}
		this.actionParams[action_name][param] = value;
	},
	loadNextPage: function() {
		this.loading_next = true;
		this.params.start += this.params.limit;
		this.loadPage();
	},
	loadPrevPage: function() {
		this.params.start -= this.params.limit;
		this.loadPage();
	},
	/*getUnreadCount: function() {//теперь не используется
		var me = this;		
		Ext6.Ajax.request({
			url: '/?c=Messages&m=getUnreadNoticeCount',
			params: null,
			callback: function(options, success, response)
			{
				if ( success )
				{
					var response_obj = Ext6.util.JSON.decode(response.responseText);
					response_obj = response_obj.data;
					me.unReadCount = response_obj.totalCount;
					if(me.unReadCount > 0) {
						main_notice_widget.NoticeWidgetButton.setText("<span class='notice-widget-button'>"+(me.unReadCount>99?99:me.unReadCount)+"</span>");
					} else {
						main_notice_widget.NoticeWidgetButton.setText("");
					}
				}
			}
		});
	},*/
	setUnreadCount: function(unReadCount) {
		this.unReadCount = unReadCount;
		if(unReadCount > 0) {
			main_notice_widget.NoticeWidgetButton.setText("<span class='notice-widget-button'>"+(unReadCount>99?99:unReadCount)+"</span>");
		} else {
			main_notice_widget.NoticeWidgetButton.setText("");
		}
	},
	loadPage: function(options) {
		var me = this;
		if ( (options && options.reset === true) || !this.params) {
			this.resetParams();
		}
		var callback = Ext6.emptyFn;
		if (options && options.callback) {
			callback = options.callback;
		}
		
		this.msgCount = 0;
		if(me.loadMask == null)
			me.loadMask = new Ext6.LoadMask(me, { msg: LOAD_WAIT });
		me.loadMask.show();

		Ext6.Ajax.request({
			url: '/?c=Messages&m=getMessagesList',
			params: this.params,
			callback: function(options, success, response)
			{
				if ( success )
				{
					//пометить все сообщения прочитанными
					//~ if(me.unReadCount>0)
						Ext6.Ajax.request({
							url: '/?c=Messages&m=setMessagesIsReaded',
							params: null,
							callback: function(options2, success2, response2)
							{
								if ( success2 )
								{
									main_notice_widget.NoticeWidgetButton.setText("");
								}
							}
						});
					
					var response_obj = Ext6.util.JSON.decode(response.responseText);
					response_obj = response_obj.data;
					if(this.store.getCount() > 0 && this.ownerCt && this.ownerCt.ownerCt && this.ownerCt.ownerCt.isVisible() ) {
						this.ownerCt.ownerCt.oldHeight = this.ownerCt.ownerCt.getHeight();
					}
					if(this.params.start==0) {
						this.store.removeAll();
					}
					this.store.complete1(response_obj.data);
					this.ownerCt.ownerCt.QueryField.getValue();
					var query = this.ownerCt.ownerCt.QueryField.getValue();
					this.store.data.each(function(rec){
						var caption = rec.data['Person_Fio'];
						var l = query.length;
						if(l>0) {
							var s1 = caption.slice(0, l);
							var s2 = caption.slice(l);
							if (s1.toLowerCase() == query.toLowerCase()) {
								rec.data['subcaption'] = s1;
								rec.data['caption'] = s2;
							}
						} else {
							rec.data['caption'] = caption;
						}
					});
					var need_expand = false;
					if(this.msgCount==0 && response_obj.totalCount>0) {
						need_expand = true;
					}
					this.msgCount = response_obj.totalCount;
					
					if(this.msgCount > 0) {
						if(this.tpl) {
							this.tpl.overwrite(this.body, {'list':this.store.data.items});
							if(need_expand)
								this.ownerCt.ownerCt.setHeight(this.ownerCt.ownerCt.oldHeight);
						}
					} else {
						if(this.tpl_empty && this.body) {
							this.tpl_empty.overwrite(this.body, {});
							this.ownerCt.ownerCt.setHeight(this.ownerCt.ownerCt.collapseHeight);
						}
					}
					me.loading_next = false;
					me.loadMask.hide();
					callback();
				}
				else
				{
					if(this.tpl_empty && this.body) {
						me.tpl_empty.overwrite(me.body, {});
						this.ownerCt.ownerCt.setHeight(this.ownerCt.ownerCt.collapseHeight);
					}
				}
				
			}.createDelegate(this)
		});
	},
	resetParams: function() {
		this.params = Ext6.apply({}, this.defaultParams);
	},

	initComponent: function() {		
		this.callParent(arguments);
	}
});

Ext6.define('common.NoticeWidget', {
	extend: 'Ext6.menu.Menu',
	cls: 'PEJ_panel',
	alwaysOnTop: true,
	border: false,
	plain: true,
	width: 600,
	height: 400,
	oldHeight: 400,
	collapseHeight: 108,
	layout: 'border',
	onSelect: Ext6.emptyFn,
	
	resizable: {
		dynamic: false,
		transparent: true
	},
	resizeHandles: 'se',
	Person_id: null,
	userMedStaffFact: null,
	
	showResults: function(t) {	
		var obj = t.getAttribute('evnclass_sysnick');
		var obj_id = null;
		
		switch(obj) {
			case 'EvnUslugaTelemed': obj_id = 'EvnUslugaTelemed_id'; break;
			case 'EvnUslugaPar': obj_id = 'EvnUslugaPar_id'; break;
		}
		
		if(obj_id) getWnd('uslugaResultWindow').show({
			Evn_id: t.getAttribute('evn_id'), 
			object: obj,
			object_id: obj_id,
			userMedStaffFact: this.EvnJournalList.userMedStaffFact
		});
	},
	
	setPersonNotice: function(params) {
		Ext6.Ajax.request({
			params: {
				PersonNotice_id: this.EvnJournalList.params.PersonNotice_id,
				Person_id: this.EvnJournalList.params.Person_id,
				PersonNotice_IsSend: params.PersonNotice_IsSend
			},
			failure: function(result_form, action) {

			},
			success: function(result_form, action) {
				var response_obj = Ext6.util.JSON.decode(result_form.responseText);
				this.EvnJournalList.params.PersonNotice_id = response_obj.PersonNotice_id;
				this.EvnJournalList.params.PersonNotice_IsSend = params.PersonNotice_IsSend;
			}.createDelegate(this),
			url: '/?c=PersonNotice&m=savePersonNotice'
		});
	},
	
	refreshPersonNoticeButtons: function() {
		this.PersonNotice.setValue( this.EvnJournalList.params.PersonNotice_IsSend == 2 );
	},
	
	load: function(force) {
		var me = this;
		me.EvnJournalList.params.query = me.QueryField.getValue();
		me.EvnJournalList.params.start = 0;
		me.EvnJournalList.loadPage();		
	},

	show: function() {
		this.callParent(arguments);
		var me = this;
		
		var jlist = me.EvnJournalList;
		
		jlist.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		jlist.afterOpenEmk = function() { this.hide();	}.createDelegate(this);

		var subtpl = '<span style="color: #f44336;">{subcaption}</span>{caption} ';
		var query = this.QueryField.getValue();

		jlist.store.data.each(function(rec){
			var caption = rec.data['Message_Subject'];
			var l = query.length;
			if(l>0) {
				var s1 = caption.slice(0, l);
				var s2 = caption.slice(l);
				if (s1.toLowerCase() == query.toLowerCase()) {
					rec.data['subcaption'] = s1;
					rec.data['caption'] = s2;
				}
			} else {
				rec.data['caption'] = caption;
			}
		});
		this.EvnJournalList.tpl = this.EvnJournalList.tpl_norm;

		jlist.tpl.overwrite(jlist.body, {'list':jlist.store.data.items});
		
		jlist.getEl().clearListeners();
	/*	jlist.getEl().on('click', function(e, t) { //если нужно открывать результат по клику на всем элементе списка
			e.stopEvent();
			var params = Array();
			
			params['Person_id'] = t.getAttribute('person_id');
			params['Server_id'] = t.getAttribute('server_id');
			params['PersonEvn_id'] = t.getAttribute('personevn_id');
			params['EvnClass_rSysNick'] = t.getAttribute('evnclass_rsysnick');
			params['Evn_id'] = t.getAttribute('evn_id');
			params['Evn_rid'] = t.getAttribute('evn_rid');
			
			getWnd('uslugaResultWindow').show({ Evn_id: t.getAttribute('evn_id'), userMedStaffFact: jlist.userMedStaffFact });
		}, null, {delegate: 'div.eventcase'});*/
		
		jlist.getEl().on('mousemove', function(e, t) {
			e.stopEvent();
		}, null, {delegate: 'div.eventcase'});
		
		this.timer = setInterval(function(){
			var h = document.getElementById(me.body.query('.x6-scroller')[0].id).scrollHeight;
			var ch = me.EvnJournalList.getHeight();
			var y = me.EvnJournalList.getScrollY();
			if(y>0 && y>=h-ch && !me.EvnJournalList.loading_next && me.EvnJournalList.store.getCount()>0 
				&& ((jlist.params.start + jlist.params.limit) < jlist.msgCount) ) {
				jlist.loadNextPage();
			}			
		}, 500);
	/*	
		//пометить все сообщения прочитанными
		if(this.unReadCount>0)
		Ext6.Ajax.request({
			url: '/?c=Messages&m=setMessagesIsReaded',
			params: null,
			callback: function(options, success, response)
			{
				if ( success )
				{
					main_notice_widget.NoticeWidgetButton.setText("");
				}
			}
		});*/
		jlist.loadPage();
		//---end show
	},

	initComponent: function () {
		var me = this;
		Ext.test = me;
		me.EvnJournalList	= new Ext6.create('NoticeWidgetPanel');
		
		me.resizerPanel = Ext6.create('Ext6.Component', {
			dock: 'bottom',
			cls: 'resizer-panel',
			height: 14,
			html: '<div class="icon-resizer"></div>'
		});
		
		Ext6.apply(me, {
			layout: 'border',
			items: [
				
				new Ext6.Panel({
					region: 'center',
					layout: 'border',
					border: false,
					tbar: {
						xtype: 'toolbar',
						cls: 'PEJ_tbar',
						height: 48,
						items: [
							me.QueryField = Ext6.create('Ext6.form.field.Text', {
									name: 'query',
									emptyText: 'Поиск по пациентам',
									width: 320,
									enableKeyEvents: true,
									padding: '2px 2px 10px 12px',
									triggers: {
										search: {
											cls: 'x6-form-search-trigger',
											handler: function() {
												me.load(true);
											}
										},
										clear: {
											cls: 'x6-form-clear-trigger',
											hidden: true,
											handler: function() {
												me.QueryField.setValue('');
												this.triggers["search"].show();
												this.triggers["clear"].hide();
												me.load();
											}
										}
									}
									,listeners: { 
										keyup: function(field, e) {
											if(field.value=="") {
												this.triggers["search"].show();
												this.triggers["clear"].hide();
											} else {
												this.triggers["search"].hide();
												this.triggers["clear"].show();
											}												
											me.load(true);
										}
									}
								}), '->',
								me.PersonNotice = Ext6.create('Ext6.form.field.Checkbox', {
									hidden: true,
									style: 'padding-right: 17px;',
									xtype: 'checkbox',
									name: 'PersonNotice',
									boxLabel: 'Уведомлять о событиях',
									handler: function() {
										var v = me.PersonNotice.getValue();
										me.setPersonNotice({PersonNotice_IsSend: v ? 2 : 1});
									}
								})
						]
					},
					items: [
						me.EvnJournalList
					]
				})
			],
			dockedItems: [
				me.resizerPanel
			]
		});		
		this.callParent(arguments);
	}
});