/**
 * Панель зубной карты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EvnPLStom.PersonToothCardPanel', {
	extend: 'swPanel',
	title: 'ЗУБНАЯ КАРТА',
	collapsed: true,
	collapseOnOnlyTitle: true,
	setAccessType: function(accessType) {
		this.setReadOnly(accessType != 'edit');
	},
	_params: {
		EvnVizitPLStom_id: null,
		//EvnVizitPLStom_setDate: null,
		Person_id: null
	},
	isLoaded: false,
	_enableEdit: false,
	_isReadOnly: true,
	_hasChanges: false,
	_toothMap: {},
	_toothSurfacePosition: -1,
	setParams: function(params) {
		var me = this;

		this.setParam('Person_id', params.Person_id);
		this.setParam('EvnVizitPLStom_id', params.Evn_id);

		if (!me.collapsed) {
			me.load();
		}
	},
	load: function() {
		this.doClear(true);
		this.doLoad();
	},
	getParam: function(key) {
		var value = this._params[key] || null;
		if ('EvnVizitPLStom_id' == key && value > 0) {
			var id = this.id.split('_')[1] || null;
			if (id > 0 && id != value) {
				//log('debug: use kostyl');
				value = id;
			}
		}
		return value;
	},
	setParam: function(key, value) {
		this._params[key] = value || null;
	},
	isEnableEdit: function() {
		return this._enableEdit||false;
	},
	setEnableEdit: function(enable) {
		if (this.isReadOnly()) {
			this._enableEdit = false;
		} else {
			this._enableEdit = enable;
		}
		this.down('#action_delete').setDisabled(!this.isAllowDelete());
	},
	getPrintBtn: function() {
		return this.down('#action_print');
	},
	isReadOnly: function() {
		return this._isReadOnly;
	},
	setReadOnly: function(isReadOnly) {
		this._isReadOnly = isReadOnly;
		this.setEnableEdit(!isReadOnly);
	},
	doReset: function() {
		this.setParam('Person_id', null);
		this.setParam('EvnVizitPLStom_id', null);
		//this.setParam('EvnVizitPLStom_setDate', null);
		this.doClear(true);
		this.setReadOnly(true);
	},
	doClear: function(isAll) {
		if (isAll) {
			this.historyStore.removeAll();
			this.historyStore.proxy.extraParams = {};
		}
		this.mainViewPanelStore.removeAll();
		this.mainViewPanel.refresh();
	},
	onLoadHistory: function() {
		var me = this;
		/*log({
			debug: 'onLoadHistory',
			panel: me,
			initId: me.getParam('EvnVizitPLStom_id')
		});*/
		if (me.mainViewPanelStore.getCount() == 0) {
			me.historyComboBox.setValue(me.historyStore.proxy.extraParams.EvnVizitPLStom_id);
			me.historyComboBox.fireEvent('change', me.historyComboBox, me.historyComboBox.getValue(), null);
		}
	},
	doLoad: function() {
		var me = this;
		/*log({
			debug: 'beforeLoadHistory',
			initId: me.getParam('EvnVizitPLStom_id'),
			mainStoreCnt: me.mainViewPanelStore.getCount(),
			historyStoreCnt: me.historyStore.getCount()
		});*/
		me.historyStore.proxy.extraParams.EvnVizitPLStom_id = me.getParam('EvnVizitPLStom_id');
		me.historyStore.proxy.extraParams.Person_id = me.getParam('Person_id');
		if (me.historyStore.getCount() == 0) {
			me.historyStore.load({
				scope: me,
				callback: me.onLoadHistory
			});
		} else {
			me.onLoadHistory();
		}
	},
	doPrint: function() {
		window.open('/?c=PersonToothCard&m=doPrint&EvnVizitPLStom_id=' + this.getParam('EvnVizitPLStom_id'), '_blank');
	},
	doDelete: function(options) {
		var me = this, isAll;
		if (!me.getParam('EvnVizitPLStom_id')) {
			return false;
		}
		if (!options) {
			options = {};
		}
		if (!options.callback) {
			options.callback = function(response_obj){};
		}
		isAll = options.withoutLoad || false;
		me.mask("Отмена внесенных изменений...");
		Ext6.Ajax.request({
			params: {
				EvnVizitPLStom_id: me.getParam('EvnVizitPLStom_id')
			},
			url: '/?c=PersonToothCard&m=doRemove',
			failure: function() {
				me.unmask();
			},
			success: function(response) {
				me.unmask();
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj.success) {
					me.doClear(isAll);
					if (!isAll) {
						me.doReloadViewData();
					}
					options.callback(response_obj);
				}
			}
		});
		return true;
	},
	doReloadViewData: function() {
		this.doLoadViewData(this.getParam('EvnVizitPLStom_id'));
	},
	doLoadViewData: function(EvnVizitPLStom_id) {
		if (!EvnVizitPLStom_id) {
			return false;
		}
		var me = this,
			params = {
				EvnVizitPLStom_id: EvnVizitPLStom_id
			},
			rec = me.historyStore.findRecord('EvnVizitPLStom_id', EvnVizitPLStom_id),
			index = (rec) ? me.historyStore.indexOf(rec) : -1,
			allowEdit = (0 == index && EvnVizitPLStom_id == me.getParam('EvnVizitPLStom_id'));
		me._hasChanges = false;
		me.setEnableEdit(allowEdit);
		if (me.getPrintBtn()) {
			me.getPrintBtn().setDisabled(true);
		}
		/*log({
			debug: 'beforeLoadViewData',
			index: index,
			initId: me.getParam('EvnVizitPLStom_id'),
			post: params
		});*/
		me.mask("Загрузка зубной карты...");
		me.mainViewPanelStore.removeAll();
		me.mainViewPanelStore.load({
			params: params,
			scope: me,
			callback: function(){
				var me = this;
				me.unmask();
				/*log({
					debug: 'onLoadViewData',
					initId: me.getParam('EvnVizitPLStom_id'),
					historyInitId: me.historyStore.proxy.extraParams.EvnVizitPLStom_id
				});*/
				me.isLoaded = true;
				//me.doLayout();
				me.onLoad(me);
			}
		});
		return true;
	},
	onLoad: function(panel){},
	isAllowDelete: function()
	{
		return (this.isEnableEdit() && this.getParam('EvnVizitPLStom_id') > 0);
	},
	/**
	 * Показываем форму состояния зуба
	 */
	_showToothStateEditForm: function(params)
	{
		var win = getWnd('swPersonToothCardEditWindow');
		if ( win.isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования состояний зуба уже открыто'));
			return false;
		}
		win.show({
			tooth: params.toothStates,
			ToothStateClassRelation: params.rec.get('ToothStateClassRelation'),
			Person_Age: params.rec.get('Person_Age'),
			callback: params.callback
		});
		return true;
	},
	/**
	 * Показываем меню поверхности
	 */
	_showToothSurfaceStateEditMenu: function(params)
	{
		var me = this,
			i, cls,
			checkedList = [];
		for(i=0; i < params.toothStates.states.length; i++) {
			cls = params.toothStates.states[i];
			if (cls['ToothSurfaceType_id'] && params.ToothSurfaceType_id == cls['ToothSurfaceType_id']) {
				checkedList.push(cls['ToothStateClass_id']);
			}
		}
		if (me.ToothSurfaceStateEditMenu) {
			me.ToothSurfaceStateEditMenu.destroy();
		}
		me.ToothSurfaceStateEditMenu = Ext6.create('Ext6.menu.Menu', {
			minWidth: 220
		});
		for (i in params.rec.get('ToothStateClassRelation')) {
			cls = params.rec.get('ToothStateClassRelation')[i];
			if (!cls['OnlyTooth']) {
				me.ToothSurfaceStateEditMenu.add(Ext6.create('Ext6.menu.CheckItem', {
					canActivate: true,
					checked: cls['ToothStateClass_id'].toString().inlist(checkedList),
					ToothStateClass_id: cls['ToothStateClass_id'],
					text: '<span style="font-weight: bold">' +
						cls['ToothStateClass_Code'] +
						'</span> <span>' +
						cls['ToothStateClass_Name'] +
						'</span>',
					handler: function(item){
						params.onSelect(item.ToothStateClass_id, item.checked);
					}
				}));
			}
		}
		me.ToothSurfaceStateEditMenu.showAt(params.e.getXY());
		return true;
	},
	/**
	 * Показываем меню поверхности или форму состояния зуба
	 */
	editToothState: function(el, e)
	{
		var me = this,
			rec = me.mainViewPanelStore.getAt(0),
			params = {
				e: e,
				rec: rec,
				callback: function(tooth, newStates, canceled) {
					var data = {
						EvnVizitPLStom_id: me.getParam('EvnVizitPLStom_id'),
						PersonToothCard_IsSuperSet: tooth.PersonToothCard_IsSuperSet,
						ToothPositionType_aid: tooth.ToothPositionType_aid,
						ToothPositionType_bid: tooth.ToothPositionType_bid,
						ToothSurfaceType_id: tooth.ToothSurfaceType_id || null,
						ToothType: tooth.ToothType,
						states: newStates.toString(),
						deactivate: canceled.toString(),
						Tooth_Code: tooth.Tooth_Code // or Tooth_SysNum JawPartType_id
					};
					me.mask("Изменение состояний зубной карты...");
					Ext6.Ajax.request({
						params: data,
						url: '/?c=PersonToothCard&m=doSave',
						failure: function() {
							me.unmask();
						},
						success: function(response) {
							me.unmask();
							var response_obj = Ext6.JSON.decode(response.responseText);
							if (response_obj.success == false) {
								//sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else {
								me.doLoadViewData(me.getParam('EvnVizitPLStom_id'));
							}
						}
					});
					return true;
				}
			},
			typeState, pos, segments;

		if (!rec || !rec.get('ToothStates')  || !me._toothMap[el.id] ||
			!rec.get('ToothStates')[me._toothMap[el.id].getCode()] ||
			!rec.get('ToothStateClassRelation')
		) {
			return false;
		}
		params.toothStates = rec.get('ToothStates')[me._toothMap[el.id].getCode()];
		params.toothStates.Tooth_SysNum = me._toothMap[el.id].getSysNum();
		params.toothStates.ToothType = sw.Promed.StomHelper.ToothMap.getDefaultToothType(rec.get('Person_Age'), params.toothStates.Tooth_SysNum);
		typeState = me._toothMap[el.id].getType();
		if (typeState) {
			params.toothStates.ToothType = typeState['ToothStateClass_id'];
		}
		segments = me._toothMap[el.id].getSurfaces();
		pos = me._toothMap[el.id].defineToothSurfacePos(e);
		/*
		 log({
			 ToothType: params.toothStates.ToothType,
			 ToothCode: me._toothMap[el.id].getCode(),
			 JawPartCode: me._toothMap[el.id].getJawPartCode(),
			 pos: pos,
			 surfaces: segments,
			 ToothSurfaceType_id: segments[pos],
			 isAllowEditSurface: me._toothMap[el.id].isAllowEditSurface()
		 });
		 */
		if (me._toothMap[el.id].isAllowEditSurface() && segments[pos]) {
			params.ToothSurfaceType_id = segments[pos];
			params.toothStates.ToothSurfaceType_id = segments[pos];
			params.onSelect = function(id, checked) {
				var state,
					canceled = [],
					newStates = [];
				state = sw.Promed.StomHelper.ToothMap.hasState(params.toothStates.states, id, true);
				if (state && params.ToothSurfaceType_id != state['ToothSurfaceType_id']) {
					state = null;
				}
				//log({state: state, id: id, checked: checked});
				// обрабатываем изменения состояний поверхности
				if (!state && checked) {
					// новое
					newStates.push(id);
				}
				if (state && !checked) {
					// отмена
					canceled.push(state['PersonToothCard_id']);
				}
				params.callback(params.toothStates, newStates, canceled);
			};
			return me._showToothSurfaceStateEditMenu(params);
		} else {
			return me._showToothStateEditForm(params);
		}
	},
	onMouseMove: function(el, e)
	{
		var me = this, pos;
		if (me._toothMap[el.id]) {
			pos = me._toothMap[el.id].defineToothSurfacePos(e);
			// Если изменилось положение курсора относительно поверхности зуба
			if (me._toothSurfacePosition != pos) {
				//Показываем ховер поверхности или ховер зуба
				//log(pos);
				me._toothMap[el.id].showHover(pos);
				me._toothSurfacePosition = pos;
			}
		}
	},
	/**
	 * Cкрываем и ховер поверхности и ховер зуба
	 */
	hideToothHover: function(el)
	{
		if (this._toothMap[el.id]) {
			this._toothMap[el.id].hideHover();
			this._toothSurfacePosition = -1;
		}
	},
	listeners: {
		'expand': function() {
			this.load();
		}
	},
	initComponent: function() {
		var me = this;

		me.historyStore = Ext6.create('Ext6.data.Store', {
			autoLoad: false,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=PersonToothCard&m=doLoadHistory',
				reader: {
					type: 'json'
				}
			},
			reader: Ext6.create('Ext6.data.JsonReader', {
				id: 'EvnVizitPLStom_id'
			}, [{
				mapping: 'EvnVizitPLStom_id',
				name: 'EvnVizitPLStom_id',
				type: 'int'
			}, {
				mapping: 'EvnVizitPLStom_setDT',
				name: 'EvnVizitPLStom_setDT',
				type: 'string'
			}])
		});

		me.historyComboBox = Ext6.create('Ext6.form.ComboBox', {
			enableKeyEvents: false,
			editable: false,
			allowBlank: false,// чтобы убрать пустую запись
			//fieldLabel: '',
			emptyText: langs('новая'),
			hideLabel: true,
			forceSelection: true,
			width: 120,
			mode: 'remote',
			//onTrigger2Click: Ext6.emptyFn,
			//trigger2Class: 'hideTrigger',
			resizable: false,
			selectOnFocus: false,
			triggerAction: 'all',
			displayField: 'EvnVizitPLStom_setDT',
			hiddenName: 'EvnVizitPLStom_hid',
			valueField: 'EvnVizitPLStom_id',
			store: me.historyStore,
			doBack: function() {
				var oldValue = this.getValue(),
					//index = (oldValue ? this.getStore().indexOfId(oldValue) : 0),
					index = this.getStore().indexOfId(oldValue),
					newRec = this.getStore().getAt(index + 1);
				if (newRec) {
					this.setValue(newRec.get('EvnVizitPLStom_id'));
					this.fireEvent('change', this, this.getValue(), oldValue);
				}
			},
			doForward: function() {
				var oldValue = this.getValue(),
					//index = (oldValue ? this.getStore().indexOfId(oldValue) : 0),
					index = this.getStore().indexOfId(oldValue),
					newRec = this.getStore().getAt(index - 1);
				if (newRec) {
					this.setValue(newRec.get('EvnVizitPLStom_id'));
					this.fireEvent('change', this, this.getValue(), oldValue);
				}
			}
		});

		me.historyBackBtn = Ext6.create('Ext6.Button', {
			handler: function() {
				me.historyComboBox.doBack();
			},
			disabled: true,
			hidden: true,
			hideMode: 'visibility',
			tooltip : langs('К предыдущему состоянию'),
			iconCls: 'back16',
			text: '&nbsp;'
		});
		me.historyForwardBtn = Ext6.create('Ext6.Button', {
			handler: function() {
				me.historyComboBox.doForward();
			},
			disabled: true,
			hidden: true,
			hideMode: 'visibility',
			tooltip : langs('К следущему состоянию'),
			iconCls: 'forward16',
			style: 'margin-left: 12px',
			text: '&nbsp;'
		});

		me.historyComboBox.addListener({
			change: function(field, newValue, oldValue) {
				var rec = field.getStore().findRecord('EvnVizitPLStom_id', newValue),
					index = field.getStore().indexOf(rec),
					prevRec = field.getStore().getAt(index + 1),
					nextRec = field.getStore().getAt(index - 1);
				if (rec) {
					me.historyBackBtn.setDisabled(!prevRec);
					me.historyBackBtn.setVisible(prevRec);
					me.historyForwardBtn.setDisabled(!nextRec);
					me.historyForwardBtn.setVisible(nextRec);
					me.doLoadViewData(rec.get('EvnVizitPLStom_id'));
				}
			}
		});

		me.mainViewPanelStore = Ext6.create('Ext6.data.Store', {
			autoLoad: false,
			listeners: {
				'load': function() {
					var rec = me.mainViewPanelStore.getAt(0),
						isRefresh = me.mainViewPanel.refresh();
					/*
					 log('mainViewPanelStore on load');// #debug
					 log(rec);// #debug
					 log(me.isEnableEdit());// #debug
					 log(isRefresh); // #debug
					 */
					if (isRefresh && me.getPrintBtn() && !isMseDepers()) {
						me.getPrintBtn().setDisabled(false);
					}
				}
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=PersonToothCard&m=doLoadViewData',
				reader: {
					type: 'json'
				}
			},
			reader: Ext6.create('Ext6.data.JsonReader', {
				id: 'Person_id'
			}, [{
				mapping: 'Person_id',
				name: 'Person_id'
			}, {
				mapping: 'Person_SurName',
				name: 'Person_SurName'
			}, {
				mapping: 'Person_FirName',
				name: 'Person_FirName'
			}, {
				mapping: 'Person_SecName',
				name: 'Person_SecName'
			}, {
				mapping: 'Person_BirthDay',
				name: 'Person_BirthDay'
			}, {
				mapping: 'Person_Age',
				name: 'Person_Age'
			}, {
				mapping: 'MedPersonal_Fin',
				name: 'MedPersonal_Fin'
			}, {
				mapping: 'history_date',
				name: 'history_date'
			}, {
				mapping: 'ToothMap',
				name: 'ToothMap'
			}, {
				mapping: 'ToothStates',
				name: 'ToothStates'
			}, {
				mapping: 'ToothStateClassRelation',
				name: 'ToothStateClassRelation'
			}])
		});

		me.mainViewPanel = Ext6.create('Ext6.Panel', {
			region: 'center',
			collapsible: false,
			layout: 'fit',
			border: false,
			height: 330,
			autoScroll: true,
			bodyStyle: 'background-color: #fff;',
			refresh : function()
			{
				var rec = me.mainViewPanelStore.getAt(0);
				if (!rec) {
					this.body.update(langs('<p>Отсутствуют данные зубной карты...</p>'));
					return false;
				}
				this.updateToothMap(this.body);
				if (me.isEnableEdit()) {
					var tooth_list = Ext6.query("td[class*=toothStates]", this.body.dom),
						data,
						i, el;
					me._toothMap = {};
					me._toothSurfacePosition = -1;
					for (i=0; i < tooth_list.length; i++) {
						el = Ext6.get(tooth_list[i]);
						me._toothMap[el.id] = new sw.Promed.Tooth(el);
						data = rec.get('ToothStates')[me._toothMap[el.id].getCode()];
						if (data && data.states) {
							me._toothMap[el.id].applyData(data.states);
						}
						el.toothMap = me;
						el.on('click', function(e){
							this.toothMap.editToothState(this, e);
						}, el);
						el.on('mousemove', function(e){
							this.toothMap.onMouseMove(this, e);
						}, el);
						el.on('mouseout', function(e){
							this.toothMap.hideToothHover(this);
						}, el);
					}
				}
				return true;
			},
			/**
			 * Обновляем данные
			 */
			updateToothMap: function(body)
			{
				var rec = me.mainViewPanelStore.getAt(0);
				if (!rec) {
					return false;
				}
				var tpl = new Ext6.XTemplate(rec.get('ToothMap')),
					data = {};
				tpl.overwrite(body, data);
				return true;
			}
		});

		me.tbar = Ext6.create('Ext6.Toolbar', {
			style: me.topToolbarStyle||'',
			items: [{
				handler: function() {
					me.doPrint();
				},
				iconCls: 'print16',
				itemId: 'action_print',
				text: BTN_GRIDPRINT
			}, {
				handler: function() {
					me.doDelete();
				},
				iconCls: 'delete16',
				itemId: 'action_delete',
				text: BTN_GRIDDEL
			}, '->',
				me.historyBackBtn,
				me.historyComboBox,
				me.historyForwardBtn
			]
		});

		Ext6.apply(this, {
			items: [
				this.mainViewPanel
			]
		});

		this.callParent(arguments);
	}
});