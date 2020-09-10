sw.Promed.EvnJournalFrame = function(config)
{
	Ext.apply(this, config);
	sw.Promed.EvnJournalFrame.superclass.constructor.call(this);
};

Ext.extend(sw.Promed.EvnJournalFrame, Ext.Panel, {
	title: '',
	bodyStyle: 'background-color: #e3e3e3',
	autoScroll: true,
	minSize: 400,

	params: null,
	defaultParams: {
		Person_id: null,
		PersonNotice_id: null,
		PersonNotice_IsSend: 2,
		start: 0,
		limit: 10
	},
	totalCount: 0,

	store: new Ext.data.SimpleStore({
		autoLoad: false,
		fields: [],
		data: [],
		complete: function(data) {
			if (typeof(data) != 'object') return false;
			var record = null;
			for (var i = 0; i < data.length; i++) {
				record = new Ext.data.Record(data[i]);
				record.id = data[i].EvnClass_SysNick+'_'+data[i].Evn_id+'_'+data[i].EvnStatus_Nick;
				record.object_code = data[i].EvnClass_SysNick;
				record.object_key = data[i].EvnClass_SysNick+'_id';
				record.object_value = data[i].Evn_id;
				record.object_status = data[i].EvnStatus_Nick;
				this.add(record);
			}
		}
	}),

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

	setPersonNotice: function(params) {
		if (!params.PersonNotice_IsSend) {
			return false;
		}

		Ext.Ajax.request({
			params: {
				PersonNotice_id: this.params.PersonNotice_id,
				Person_id: this.params.Person_id,
				PersonNotice_IsSend: params.PersonNotice_IsSend
			},
			failure: function(result_form, action) {

			},
			success: function(result_form, action) {
				var response_obj = Ext.util.JSON.decode(result_form.responseText);
				this.params.PersonNotice_id = response_obj.PersonNotice_id;
				this.params.PersonNotice_IsSend = params.PersonNotice_IsSend;
				this.refreshPersonNoticeButtons();
			}.createDelegate(this),
			url: '/?c=PersonNotice&m=savePersonNotice'
		});
	},
	refreshPersonNoticeButtons: function() {
		var btn_on = Ext.get(this.id).select('.EvnJournal_PersonNotice_On');
		var btn_off = Ext.get(this.id).select('.EvnJournal_PersonNotice_Off');

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
	createActions: function() {
		var action = null, config = null, config_arr = [], el = null;
		for (var action_name in this.actions) {
			action = this.actions[action_name];
			action.name = action_name;

			if (action.sectionCode == 'EvnJournal') {
				el = Ext.get(this.id).select('.EvnJournal_'+action_name);
				config_arr.push({
					el: el,
					action: action
				});
			}
			else if(action.sectionCode == 'Evn') {
				this.store.each(function(rec) {
					el = Ext.get(this.id).select('.'+rec.id+'_'+action_name);
					config_arr.push({
						el: el,
						action: action,
						params: rec.data
					});
				}.createDelegate(this));
			}
		}
		for (var i = 0; i < config_arr.length; i++) {
			config = config_arr[i];
			if (!config.el) { continue; }
			if (this.actionParams[config.action.name]) {
				config.params = Ext.apply(config.params || {}, this.actionParams[config.action.name]);
			}
			if (config.params) {
				config.el.on('click', config.action.handler, this, config.params);
			} else {
				config.el.on('click', config.action.handler, this);
			}
			if (config.action.displayCondition && typeof config.action.displayCondition == "function") {
				config.el.setDisplayed(config.action.displayCondition(this));
			}
		}
	},

	loadNextPage: function() {
		this.params.start += this.params.limit;
		this.loadPage();
	},
	loadPrevPage: function() {
		this.params.start -= this.params.limit;
		this.loadPage();
	},
	loadPage: function(options) {
		if (options && options.reset === true || !this.params) {
			this.resetParams();
		}
		if (options && options.Person_id &&  options.Person_id > 0) {
			this.params.Person_id = options.Person_id;
		}
		this.params.isMseDepers = isMseDepers() ? 1 : 0;
		if (!this.params.Person_id) { return; }
		var callback = Ext.emptyFn;
		if (options && options.callback) {
			callback = options.callback;
		}

		this.totalCount = 0;
		var lm = new Ext.LoadMask(this.getEl(), {msg: lang['idet_zagruzka_dokumenta']});
		lm.show();

		Ext.Ajax.request({
			url: '/?c=Evn&m=getEvnJournal',
			params: this.params,
			callback: function(options, success, response)
			{
				if ( success )
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);

					this.store.removeAll();
					this.store.complete(response_obj.data.evn);

					if (response_obj.data.notice.length > 0) {
						var notice = response_obj.data.notice[0];
						this.params.PersonNotice_id = notice.PersonNotice_id;
						this.params.PersonNotice_IsSend = notice.PersonNotice_IsSend;
					}

					this.totalCount = response_obj.data.totalCount;

					this.tpl = new Ext.Template(response_obj.html);
					this.tpl.overwrite(this.body, response_obj);
					this.refreshPersonNoticeButtons();

					this.createActions();
		 			lm.hide();
					callback();
				}
				else
				{
		 			lm.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_dannyih_po_patsientu']);
				}
			}.createDelegate(this)
		});
	},
	resetParams: function() {
		this.params = Ext.apply({}, this.defaultParams);
	},

	initComponent: function() {
		var actions_config = {
			loadNextPage: {
				sectionCode: 'EvnJournal',
				handler: function() {
					this.loadNextPage();
				},
				displayCondition: function(scope) {
					return((scope.params.start+scope.params.limit) < scope.totalCount);
				}
			},
			loadPrevPage: {
				sectionCode: 'EvnJournal',
				handler: function()
				{
					this.loadPrevPage();
				},
				displayCondition: function(scope) {
					return (scope.params.start != 0);
				}
			},
			openEmk: {
				sectionCode: 'Evn',
				handler: function(e, c, p)
				{
					if ((!p) || (!p.ARMType) || (!p.userMedStaffFact))
					{
						Ext.Msg.alert(lang['oshibka_otkryitiya_emk'], lang['oshibka_otkryitiya_emkne_ukazanyi_neobhodimyie_parametryi']);
						return false;
					}
					var searchNodeObj = {
						parentNodeId: 'root',
						last_child: false,
						disableLoadViewForm: false,
						EvnClass_SysNick: p.EvnClass_rSysNick,
						Evn_id: p.Evn_rid
					};
					var emk = getWnd('swPersonEmkWindow');
					if (emk.isVisible()) {
						if (p.Person_id == emk.Person_id) {
							var sparams = {
								parent_node: emk.Tree.getRootNode(),
								last_child: false,
								disableLoadViewForm: searchNodeObj.disableLoadViewForm,
								node_attr_name: 'id',
								node_attr_value: searchNodeObj.EvnClass_SysNick +'_'+ searchNodeObj.Evn_id
							};
							emk.searchNodeInTreeAndLoadViewForm(sparams);
						} else {
							sw.swMsg.alert(lang['soobschenie'], lang['forma_elektronnoy_istorii_bolezni_emk_v_dannyiy_moment_otkryita']);
						}
					} else {
						emk.show({
							Person_id: p.Person_id,
							Server_id: p.Server_id,
							PersonEvn_id: p.PersonEvn_id,
							searchNodeObj: searchNodeObj,
							userMedStaffFact: p.userMedStaffFact,
							ARMType: p.ARMType
						});
						if (typeof p.afterOpenEmk == 'function') {
							p.afterOpenEmk();
						}
					}
				}
			},
			PersonNotice_On: {
				sectionCode: 'EvnJournal',
				handler: function(e, c, p)
				{
					// Изменяем на Off
					this.setPersonNotice({PersonNotice_IsSend: 1});
				}
			},
			PersonNotice_Off: {
				sectionCode: 'EvnJournal',
				handler: function(e, c, p)
				{
					// Изменяем на On
					this.setPersonNotice({PersonNotice_IsSend: 2});
				}
			}
		};
		for (var action in actions_config) {
			this.actions[action] = Ext.apply({}, actions_config[action])
		}

		sw.Promed.ViewFrame.superclass.initComponent.apply(this, arguments);
	}
});