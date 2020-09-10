/**
* Журнал событий
* вызывается из меню ЭМК
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/

Ext6.define('EvnJournalList', {
	extend: 'Ext6.Panel',
	title: '',
	cls: 'PEJ_list_noscroll',
	userCls: 'narrow-scroll',
	autoScroll: true,
	minSize: 400,
	width: '100%',
	height: 340,
	region: 'center',
	layout: 'border',
	border: false,
	preserveScrollOnRefresh: true,
	params: {},
	defaultParams: {
		Person_id: null,
		PersonNotice_id: null,
		PersonNotice_IsSend: 2,
		start: 0,
		limit: 10
	},
	loading_next: false,
	ARMType: null,
	userMedStaffFact: null,
	afterOpenEmk: null,
	
	totalCount: 0,
	tpl: null,
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
			for (var i = 0; i < data.length; i++) {
				record = new Ext6.data.Record(data[i]);
				record.id = data[i].EvnClass_SysNick+'_'+data[i].Evn_id+'_'+data[i].EvnStatus_Nick;
				record.object_code = data[i].EvnClass_SysNick;
				record.object_key = data[i].EvnClass_SysNick+'_id';
				record.object_value = data[i].Evn_id;
				record.object_status = data[i].EvnStatus_Nick;
				this.add(record);
			}
		}
	}),
	
	openEmk: function(params) { 
		
		if (!this.ARMType || !this.userMedStaffFact)
		{
			Ext6.Msg.alert(langs('Ошибка открытия ЭМК'), langs('Ошибка открытия ЭМК<br/>Не указаны необходимые параметры.'));
			return false;
		}
		
		var searchNodeObj = {
			parentNodeId: 'root',
			last_child: false,
			disableLoadViewForm: false,
			EvnClass_SysNick: params['EvnClass_rSysNick'],
			Evn_id: params['Evn_rid']
		};
		var emks = Ext6.ComponentQuery.query('window[refId=common]');
		var emk=null;
		for(i=0; i<emks.length; i++) if(emks[i].isVisible()) emk=emks[i];	
		
		if(emk && emk.isVisible() ) {
			if (params['Person_id'] == emk.Person_id) {
				var sparams = {
					parent_node: emk.treeData.data[0],
					last_child: false,
					disableLoadViewForm: searchNodeObj.disableLoadViewForm,
					node_attr_name: 'id',
					node_attr_value: searchNodeObj.EvnClass_SysNick +'_'+ searchNodeObj.Evn_id,
					object: params['EvnClass_rSysNick'],
					object_id: params['Evn_id']
				};
				
				emk.searchNodeInTreeAndLoadViewForm(sparams);
			} else {
				Ext6.Msg.alert(langs('Сообщение'), langs('Форма электронной истории болезни (ЭМК) в данный момент открыта.'));
			}
		} else {
			emk.show({
				Person_id: params['Person_id'],
				Server_id: params['Server_id'],
				PersonEvn_id: params['PersonEvn_id'],
				searchNodeObj: searchNodeObj,
				userMedStaffFact: this.userMedStaffFact,
				ARMType: this.ARMType
			});
			if (typeof this.afterOpenEmk == 'function') {
				this.afterOpenEmk();
			}
		}
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
	loadPage: function(options) {
		var me = this;
		if (options && options.reset === true || !this.params) {
			this.resetParams();
		}
		if (options && options.Person_id &&  options.Person_id > 0) {
			this.params.Person_id = options.Person_id;
		}
		if (!this.params.Person_id) { return; }
		var callback = Ext6.emptyFn;
		if (options && options.callback) {
			callback = options.callback;
		}

		
		this.totalCount = 0;

		Ext6.Ajax.request({
			url: '/?c=Evn&m=getEvnJournal',
			params: this.params,
			callback: function(options, success, response)
			{
				if ( success )
				{
					var response_obj = Ext6.util.JSON.decode(response.responseText);
					if(this.store.getCount() > 0 && this.ownerCt && this.ownerCt.ownerCt && this.ownerCt.ownerCt.isVisible() ) {
						this.ownerCt.ownerCt.oldHeight = this.ownerCt.ownerCt.getHeight();
					}
					if(this.params.start==0) {
						this.store.removeAll();
					}
					this.store.complete1(response_obj.data.evn);
					this.ownerCt.ownerCt.QueryField.getValue();
					var query = this.ownerCt.ownerCt.QueryField.getValue();
					this.store.data.each(function(rec){
						var caption = rec.data['EvnClass_Name'];
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
					if (response_obj.data.notice.length > 0) {
						var notice = response_obj.data.notice[0];
						this.params.PersonNotice_id = notice.PersonNotice_id;
						this.params.PersonNotice_IsSend = notice.PersonNotice_IsSend;
					}
					var need_expand = false;
					if(this.totalCount==0 && response_obj.data.totalCount>0) {
						need_expand = true;
					}
					this.totalCount = response_obj.data.totalCount;
					if(this.totalCount > 0) {
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

Ext6.define('common.EMK.PersonEvnJournalPanel', {
	extend: 'Ext6.menu.Menu',
	cls: 'PEJ_panel',
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
	//~ params: null,
	//~ defaultParams: {
		//~ Person_id: null,
		//~ PersonNotice_id: null,
		//~ PersonNotice_IsSend: 2,
		//~ start: 0,
		//~ limit: 10
	//~ },
	Person_id: null,
	ARMType: null,
	userMedStaffFact: null,
	
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
		var me = this;
		var jlist = me.EvnJournalList;
		
		if (arguments[0]) {
			me.alignTarget = arguments[0].target;
			
			me._lastAlignTarget = arguments[0].target;
			me._lastAlignToPos = arguments[0].align || me.defaultAlign;
			me._lastAlignToOffsets = arguments[0].offset || me.alignOffset;
		}
		
		if ((!arguments[0]) || (!arguments[0].Person_id))
		{
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны необходимые входные параметры.');
		}
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		this.Person_id = arguments[0].Person_id;

		jlist.ARMType = this.ARMType;
		jlist.userMedStaffFact = this.userMedStaffFact;
		jlist.afterOpenEmk = function() { this.hide();	}.createDelegate(this);
		
		this.callParent(arguments);
		var subtpl = '<span style="color: #f44336;">{subcaption}</span>{caption} ';
		//~ var query = this.QueryField.getValue();

		//~ jlist.store.data.each(function(rec){
			//~ var caption = rec.data['EvnClass_Name'];
			//~ var l = query.length;
			//~ if(l>0) {
				//~ var s1 = caption.slice(0, l);
				//~ var s2 = caption.slice(l);
				//~ if (s1.toLowerCase() == query.toLowerCase()) {
					//~ rec.data['subcaption'] = s1;
					//~ rec.data['caption'] = s2;
				//~ }
			//~ } else {
				//~ rec.data['caption'] = caption;
			//~ }
		//~ });
		if(this.EvnJournalList.totalCount>0) {
			this.EvnJournalList.tpl = new Ext6.XTemplate(
				'<div style="height:10px;"></div>',
				'<tpl for="list">',
				'<div class="PEJ_list">',
					'<tpl for="data">',
						'<div class="eventcase" evnclass_rsysnick="{EvnClass_rSysNick}" evn_id="{Evn_id}" personevn_id="{PersonEvn_id}" person_id="{Person_id}" server_id="{Server_id}" evn_rid="{Evn_rid}">',
							'<ul>',
								'<li>{[this.formatDate(values.Evn_DT)]}</li>',
								'<li class="PEJ_header"><a href="#"><span style="color: #f44336;">{subcaption}</span>{caption} ({EvnStatus_Name})</a></li>',
								'<li><div style="height: 15px; overflow-y:hidden;">{Evn_Body}</div></li>',
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
			);
		} else this.EvnJournalList.tpl = this.EvnJournalList.tpl_empty;
		
		jlist.tpl.overwrite(jlist.body, {'list':jlist.store.data.items});
		
		jlist.getEl().clearListeners();
		jlist.getEl().on('click', function(e, t) {
			e.stopEvent();
			var params = Array();
			params['Person_id'] = t.getAttribute('person_id');
			params['Server_id'] = t.getAttribute('server_id');
			params['PersonEvn_id'] = t.getAttribute('personevn_id');
			params['EvnClass_rSysNick'] = t.getAttribute('evnclass_rsysnick');
			params['Evn_id'] = t.getAttribute('evn_id');
			params['Evn_rid'] = t.getAttribute('evn_rid');
			me.EvnJournalList.openEmk(params);
		}, null, {delegate: 'div.eventcase'});
		
		jlist.getEl().on('mousemove', function(e, t) {
			e.stopEvent();
		}, null, {delegate: 'div.eventcase'});
		
		this.timer = setInterval(function(){
			var h = document.getElementById(me.body.query('.x6-scroller')[0].id).scrollHeight;
			var ch = me.EvnJournalList.getHeight();
			var y = me.EvnJournalList.getScrollY();		
			if(y>0 && y>=h-ch && !me.EvnJournalList.loading_next && me.EvnJournalList.store.getCount()>0 
				&& ((jlist.params.start + jlist.params.limit) < jlist.totalCount) ) {
				jlist.loadNextPage();
			}			
		}, 500);		
		this.refreshPersonNoticeButtons();
	},

	initComponent: function () {
		var me = this;
		Ext.test = me;
		me.EvnJournalList	= new Ext6.create('EvnJournalList');
		
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
						cls: 'PEJ_tbar', //emkFilterPanel
						height: 48,
						items: [
							me.QueryField = Ext6.create('Ext6.form.field.Text', {
									name: 'query',
									emptyText: 'Поиск по событиям',
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