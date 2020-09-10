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
Ext6.define('common.EvnPLStom.ParodontogramPanel', {
	extend: 'swPanel',
	title: 'ПАРОДОНТОГРАММА',
	collapsed: true,
	collapseOnOnlyTitle: true,
	setAccessType: function(accessType) {
		this.setReadOnly(accessType != 'edit');
	},
	_params: {
		EvnUslugaStom_id: null,
		EvnUslugaStom_setDate: null,
		Person_id: null
	},
	_enableEdit: false,
	_isReadOnly: true,
	_hasChanges: false,
	setParams: function(params) {
		var me = this;

		this.setParam('Person_id', params.Person_id);
		this.setParam('EvnUslugaStom_id', params.EvnUslugaStom_id);
		this.setParam('EvnUslugaStom_setDate', params.EvnUslugaStom_setDate);
		me.Evn_id = params.Evn_id;
		me.Evn_pid = params.Evn_pid;
		me.Person_id = params.Person_id;
		me.PersonEvn_id = params.PersonEvn_id;
		me.Server_id = params.Server_id;
		me.userMedStaffFact = params.userMedStaffFact;

		if (!me.collapsed) {
			me.load();
		}
	},
	load: function() {
		this.doClear();

		if (this.getParam('EvnUslugaStom_id')) {
			this.doLoad();
		} else {
			this.collapse();
			this.addParodontogram();
		}
	},
	addParodontogram: function() {
		var me = this;
		var my_params = {
			formParams: {
				Person_id: me.Person_id,
				PersonEvn_id: me.PersonEvn_id,
				Server_id: me.Server_id
			},
			formMode: 'morbus',
			onHide: Ext.emptyFn
		};

		my_params.formParams.EvnUslugaStom_id = 0;
		my_params.formParams.EvnUslugaStom_pid = me.Evn_id;
		my_params.formParams.EvnUslugaStom_rid = me.Evn_pid;

		my_params.action = 'add';
		my_params.isAddParodontogram = true;

		my_params.callback = function(data) {
			if ( !data || !data.evnUslugaData ) {
				return false;
			}

			me.setParam('EvnUslugaStom_id', data.evnUslugaData.EvnUsluga_id);

			// обновить список услуг
			me.ownerPanel.EvnUslugaPanel.load();
			// развернуть парадонтограмму
			me.expand();

			return true;
		};
		my_params.Mes_id = null;
		my_params.formParams.LpuSection_uid = me.userMedStaffFact.LpuSection_id;
		my_params.formParams.MedStaffFact_id = me.userMedStaffFact.MedStaffFact_id;
		my_params.formParams.PayType_id = null;
		my_params.formParams.EvnUslugaStom_setDate = Date.parseDate(this.getParam('EvnUslugaStom_setDate'), 'd.m.Y');

		var piPanel = me.ownerWin.PersonInfoPanel;
		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			my_params.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
			my_params.Person_Surname = piPanel.getFieldValue('Person_Surname');
			my_params.Person_Firname = piPanel.getFieldValue('Person_Firname');
			my_params.Person_Secname = piPanel.getFieldValue('Person_Secname');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}

		getWnd('swEvnUslugaStomEditWindow').show(my_params);

		return true;
	},
	getParam: function(key) {
		return this._params[key] || null;
	},
	setParam: function(key, value) {
		this._params[key] = value || null;
	},
	isNewEvnUslugaStom: function() {
		return this.getParam('EvnUslugaStom_id') == 0;
	},
	isNewParodontogram: function() {
		var me = this,
			flag = true;
		if (me.isNewEvnUslugaStom() || !me.historyStore.getAt(0)) {
			return true;
		}
		me.historyStore.each(function(rec) {
			if (rec.get('EvnUslugaStom_id') == me.getParam('EvnUslugaStom_id')) {
				flag = false;
				return false;
			}
			return true;
		});
		return flag;
	},
	isEnableEdit: function() {
		return this._enableEdit || false;
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
		this.setParam('EvnUslugaStom_id', null);
		this.setParam('EvnUslugaStom_setDate', null);
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
	_onLoadHistory: function(records, options, success) {
		var me = this;
		if (!success) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неудалось загрузить историю выполнения услуги Пародонтограмма'));
			return false;
		}
		/*log({
			debug: 'onLoadParodontogramHistory',
			panel: me,
			initId: me.getParam('EvnUslugaStom_id')
		});*/
		me.historyComboBox.setValue(me.getParam('EvnUslugaStom_id'));
		me.historyComboBox.fireEvent('change', me.historyComboBox, me.historyComboBox.getValue(), null);
		me.isLoadHistoryByPerson = false;
		return true;
	},
	doLoad: function(byPerson) {
		var me = this;
		/*log({
		 debug: 'beforeLoadParodontogramHistory',
		 initId: me.getParam('EvnUslugaStom_id'),
		 mainStoreCnt: me.mainViewPanelStore.getCount(),
		 historyStoreCnt: me.historyStore.getCount()
		 });*/
		me.historyStore.proxy.extraParams.EvnUslugaStom_id = me.getParam('EvnUslugaStom_id');
		me.historyStore.proxy.extraParams.Person_id = me.getParam('Person_id');
		me.isLoadHistoryByPerson = byPerson;
		if (me.historyStore.getCount() == 0) {
			me.historyStore.load({
				params: {
					Person_id: me.getParam('Person_id')
				},
				scope: me,
				callback: me._onLoadHistory
			});
		} else {
			me._onLoadHistory(null, null, true);
		}
	},
	doPrint: function() {
		//window.open('/?c=Parodontogram&m=doPrint&EvnUslugaStom_id=' + this.getParam('EvnUslugaStom_id'), '_blank');
		var doc = this.mainViewPanel.getParodontogrammaHtml();
		var id_salt = Math.random();
		var win_id = 'printEvent' + Math.floor(id_salt * 10000);
		var win = window.open('', win_id);
		win.document.write(doc);
		win.document.close();
		//win.print();
	},
	doDelete: function(options) {
		var me = this;
		if (!me.getParam('EvnUslugaStom_id')) {
			return false;
		}
		if (!options) {
			options = {};
		}
		if (!options.callback) {
			options.callback = function(response_obj) {
			};
		}
		me.mask("Удаление пародонтограммы...");
		Ext6.Ajax.request({
			params: {
				EvnUslugaStom_id: me.getParam('EvnUslugaStom_id')
			},
			url: '/?c=Parodontogram&m=doRemove',
			failure: function() {
				me.unmask();
			},
			success: function(response) {
				me.unmask();
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj.success) {
					me.doClear(false);// не очищаем историю и базовые параметры
					me.doLoad(true);// загружаем предыдущие данные
					options.callback(response_obj);
				}
			}
		});
		return true;
	},
	doLoadViewData: function(EvnUslugaStom_id) {
		var me = this,
			params = {
				Person_id: me.getParam('Person_id')
			};
		if (EvnUslugaStom_id && EvnUslugaStom_id > 0) {
			me._hasChanges = false;
			params.EvnUslugaStom_id = EvnUslugaStom_id;
			me.setEnableEdit(EvnUslugaStom_id == me.getParam('EvnUslugaStom_id'));
		} else {
			me._hasChanges = true;
			me.setEnableEdit(true);
		}
		if (me.getPrintBtn()) {
			me.getPrintBtn().setDisabled(true);
		}
		/*log({
		 debug: 'beforeLoadParodontogramViewData',
		 initId: me.getParam('EvnUslugaStom_id'),
		 post: params
		 });*/
		me.mask("Загрузка пародонтограммы...");
		me.mainViewPanelStore.removeAll();
		me.mainViewPanelStore.load({
			params: params,
			callback: function() {
				me.unmask();
			}
		});
	},
	showToothStateValuesMenu: function(event, node) {
		var me = this,
			nodeEl = Ext6.get(node),
			menu = Ext6.create('Ext6.menu.Menu', {
				minWidth: 220
			}),
			toothCode = nodeEl.getAttribute('itemId');
		me.toothStateValuesMenuStore.each(function(rec) {
			if (rec.get('Tooth_Code') == toothCode) {
				var color = '#000';
				var name = rec.get('ToothStateType_Name');
				var arr = name.split(',');
				if (arr[1]) {
					name = arr[1];
				}
				if (rec.get('ToothStateType_Code') > 1) {
					color = '#ff0000';
				}
				menu.add(Ext6.create('Ext6.menu.Item', {
					hideLabel: true,
					style: 'text-align: left; cursor: pointer; padding: 0 5px; border: 1px solid #fff;',
					ToothStateValues_id: rec.get('ToothStateValues_id'),
					text: '<span style="color: ' + color
						+ '">' + rec.get('ToothStateType_Nick')
						+ '</span> <span>' + name + '</span>',
					handler: function(item) {
						me.onSelectToothStateValue(item.ToothStateValues_id);
					}
				}));
			}
		});
		nodeEl.setStyle('border', '1px solid #00318B');
		menu.on('hide', function() {
			nodeEl.setStyle('border', '1px solid #808080');
		});
		menu.showBy(nodeEl);
	},
	onSelectToothStateValue: function(ToothStateValues_id) {
		var me = this;
		var index = me.toothStateValuesMenuStore.find('ToothStateValues_id', ToothStateValues_id);
		if (index < 0) {
			return false;
		}
		var val_rec = me.toothStateValuesMenuStore.getAt(index);
		if (!val_rec) {
			return false;
		}
		var toothCode = val_rec.get('Tooth_Code');
		var rec = me.mainViewPanelStore.getAt(0);
		if (!rec) {
			return false;
		}
		var state = rec.get('state');
		if (!state[toothCode]) {
			return false;
		}
		//для сохранения
		state[toothCode]['Tooth_id'] = val_rec.get('Tooth_id');
		state[toothCode]['ToothStateType_id'] = val_rec.get('ToothStateType_id');
		//для отображения
		state[toothCode]['ToothStateType_Code'] = val_rec.get('ToothStateType_Code');
		state[toothCode]['ToothStateType_Nick'] = val_rec.get('ToothStateType_Nick');
		state[toothCode]['ToothState_Value'] = val_rec.get('ToothStateType_Value');
		rec.set('state', state);
		rec.commit();
		me._hasChanges = true;
		me.mainViewPanel.refresh();
		return true;
	},
	isAllowSave: function() {
		return (this._hasChanges && this.mainViewPanelStore.getAt(0));
	},
	isAllowDelete: function() {
		return (this.isEnableEdit() && this.getParam('EvnUslugaStom_id') > 0);
	},
	doSave: function(options) {
		var me = this,
			rec_state = me.mainViewPanelStore.getAt(0),
			toothCode,
			toothId,
			parodontogram_state = {};
		if (!me.getParam('EvnUslugaStom_id')) {
			return false;
		}
		if (!rec_state) {
			return false;
		}
		var state = rec_state.get('state');
		for (toothCode in state) {
			toothId = state[toothCode]['Tooth_id'];
			parodontogram_state[toothId] = state[toothCode]['ToothStateType_id'];
		}
		if (!options) {
			options = {};
		}
		if (!options.callback) {
			options.callback = function(response_obj) {
			};
		}
		me.mask("Сохранение пародонтограммы...");
		loadMask.show();
		Ext6.Ajax.request({
			params: {
				EvnUslugaStom_id: me.getParam('EvnUslugaStom_id'),
				state: Ext6.JSON.encode(parodontogram_state)
			},
			url: '/?c=Parodontogram&m=doSave',
			failure: function() {
				me.unmask();
			},
			success: function(response) {
				me.unmask();
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj.success == false) {
					//sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
				} else {
					options.callback(response_obj);
				}
			}
		});
		return true;
	},
	listeners: {
		'expand': function() {
			this.load();
		}
	},
	initComponent: function() {
		var me = this;

		me.toothStateValuesMenuStore = new Ext6.data.Store({
			autoLoad: false,
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=Parodontogram&m=doLoadToothStateValues',
				reader: {
					type: 'json'
				}
			},
			reader: Ext6.create('Ext6.data.JsonReader', {
				id: 'ToothStateValues_id'
			}, [
				{name: 'Tooth_id', mapping: 'Tooth_id'},
				{name: 'JawPartType_Code', mapping: 'JawPartType_Code', type: 'int'},
				{name: 'Tooth_Code', mapping: 'Tooth_Code', type: 'int'},
				{name: 'ToothStateType_Code', mapping: 'ToothStateType_Code', type: 'int'},
				{name: 'ToothStateType_id', mapping: 'ToothStateType_id'},
				{name: 'ToothStateType_Name', mapping: 'ToothStateType_Name'},
				{name: 'ToothStateType_Nick', mapping: 'ToothStateType_Nick'},
				{name: 'ToothStateValues_id', mapping: 'ToothStateValues_id'},
				{name: 'ToothStateType_Value', mapping: 'ToothStateType_Value', type: 'float'}
			])
		});

		me.historyStore = Ext6.create('Ext6.data.Store', {
			autoLoad: false,
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=Parodontogram&m=doLoadHistory',
				reader: {
					type: 'json'
				}
			},
			listeners: {
				load: function(store, records, options) {
					if (options && options.params && (me.isNewEvnUslugaStom() || me.isNewParodontogram())) {
						//log({debug: 'load', args: arguments});
						var recs = [], key;
						recs.push({
							EvnUslugaStom_id: 0,
							EvnUslugaStom_Display: 'Новая',
							EvnUslugaStom_setDate: '',
							EvnUslugaStom_setTime: '',
							MedPersonal_id: null,
							Lpu_id: null
						});
						for (key in records) {
							if (records[key].data) {
								recs.push(records[key].data);
							}
						}
						store.removeAll();
						store.loadData(recs);
					}
				}
			},
			reader: Ext6.create('Ext6.data.JsonReader', {
				id: 'EvnUslugaStom_id'
			}, [{
				mapping: 'EvnUslugaStom_id',
				name: 'EvnUslugaStom_id',
				type: 'string'
			}, {
				mapping: 'Lpu_id',
				name: 'Lpu_id',
				type: 'string'
			}, {
				mapping: 'MedPersonal_id',
				name: 'MedPersonal_id',
				type: 'string'
			}, {
				mapping: 'EvnUslugaStom_setDate',
				name: 'EvnUslugaStom_setDate',
				type: 'string'
			}, {
				mapping: 'EvnUslugaStom_setTime',
				name: 'EvnUslugaStom_setTime',
				type: 'string'
			}, {
				mapping: 'EvnUslugaStom_Display',
				name: 'EvnUslugaStom_Display',
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
			width: 280,
			listWidth: 330,
			mode: 'remote',
			//onTriggerClick: function() { log(arguments); },
			//triggerClass: 'hideTrigger',
			//resizable: false,
			selectOnFocus: false,
			triggerAction: 'all',
			displayField: 'EvnUslugaStom_Display',
			hiddenName: 'EvnUslugaStom_hid',
			valueField: 'EvnUslugaStom_id',
			store: me.historyStore,
			doBack: function() {
				var oldValue = this.getValue(),
					index = this.getStore().indexOfId(oldValue),
					newRec = this.getStore().getAt(index + 1);
				if (newRec) {
					this.setValue(newRec.get('EvnUslugaStom_id'));
					this.fireEvent('change', this, this.getValue(), oldValue);
				}
			},
			doForward: function() {
				var oldValue = this.getValue(),
					index = this.getStore().indexOfId(oldValue),
					newRec = this.getStore().getAt(index - 1);
				if (newRec) {
					this.setValue(newRec.get('EvnUslugaStom_id'));
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
			tooltip: langs('К предыдущему состоянию'),
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
			tooltip: langs('К следущему состоянию'),
			iconCls: 'forward16',
			style: 'margin-left: 12px',
			text: '&nbsp;'
		});

		me.historyComboBox.addListener({
			change: function(field, newValue, oldValue) {
				var index = field.getStore().indexOfId(newValue),
					rec = field.getStore().getAt(index),
					prevRec = field.getStore().getAt(index + 1),
					nextRec = field.getStore().getAt(index - 1),
					forwardDisabled = (!nextRec || !nextRec.get('EvnUslugaStom_id')),
					backDisabled = (!prevRec || !prevRec.get('EvnUslugaStom_id'));
				if (rec && !me.isLoadHistoryByPerson) {
					me.doLoadViewData(rec.get('EvnUslugaStom_id'));
				} else {
					me.doLoadViewData(0);
				}
				me.historyBackBtn.setDisabled(backDisabled);
				me.historyBackBtn.setVisible(!backDisabled);
				me.historyForwardBtn.setDisabled(forwardDisabled);
				me.historyForwardBtn.setVisible(!forwardDisabled);
				field.clearInvalid();
			}
		});

		me.mainViewPanelStore = Ext6.create('Ext6.data.Store', {
			autoLoad: false,
			listeners: {
				'load': function() {
					var onLoad = function() {
						var isRefresh = me.mainViewPanel.refresh();
						if (isRefresh && me.getPrintBtn()) {
							me.getPrintBtn().setDisabled(false);
						}
					};
					if (me.isEnableEdit()) {
						if (me.toothStateValuesMenuStore.getCount() == 0) {
							sw.Promed.StomHelper.ToothStateValues.loadStore(
								me.toothStateValuesMenuStore,
								onLoad
							);
						} else {
							onLoad();
						}
					} else {
						onLoad();
					}
				}
			},
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=Parodontogram&m=doLoadViewData',
				reader: {
					type: 'json'
				}
			},
			reader: Ext6.create('Ext6.data.JsonReader', {
				id: 'UslugaComplex_Code'
			}, [{
				mapping: 'UslugaComplex_Code',
				name: 'UslugaComplex_Code'
			}, {
				mapping: 'UslugaComplex_Name',
				name: 'UslugaComplex_Name'
			}, {
				mapping: 'parodontogramma',
				name: 'parodontogramma'
			}, {
				mapping: 'state',
				name: 'state'
			}])
		});

		me.mainViewPanel = Ext6.create('Ext6.Panel', {
			region: 'center',
			collapsible: false,
			layout: 'fit',
			border: false,
			height: 160,
			bodyStyle: 'background-color: #fff; padding: 10px;',
			refresh: function() {
				var rec = me.mainViewPanelStore.getAt(0);
				if (!rec) {
					this.body.update('<p>Извините, не удалось загрузить пародонтограмму...</p>');
					return false;
				}
				this.updateParodontogramma(this.body);
				var tooth_list = Ext6.query("td[class*=parodont]", this.body.dom);
				var i, el, clickEl;
				for (i = 0; i < tooth_list.length; i++) {
					el = Ext6.get(tooth_list[i]);
					if (el.hasCls('state-type-1')) {
						el.setStyle('border', '1px solid #808080');
					} else {
						el.setStyle('border', '1px solid #808080');
						el.setStyle('color', '#ff0000');
						el.setStyle('background-color', '#ffcccc');
					}
					if (me.isEnableEdit()) {
						el.on('click', me.showToothStateValuesMenu, me);
						el.setStyle('cursor', 'pointer');
					}
				}
				tooth_list = Ext6.query("td[class*=tooth-state-value]", this.body.dom);
				for (i = 0; i < tooth_list.length; i++) {
					el = Ext6.get(tooth_list[i]);
					el.setStyle('color', 'gray');
				}
				tooth_list = Ext6.query("td[class*=tooth-info]", this.body.dom);
				for (i = 0; i < tooth_list.length; i++) {
					el = Ext6.get(tooth_list[i]);
					el.setStyle('font-size', '11px');
				}
				return true;
			},
			/**
			 * Обновляем данные
			 */
			updateParodontogramma: function(body) {
				var rec = me.mainViewPanelStore.getAt(0);
				if (!rec) {
					return false;
				}
				var tpl = new Ext6.XTemplate(rec.get('parodontogramma')),
					data = {
						EvnUslugaStom_setDate: me.getParam('EvnUslugaStom_setDate'),
						Sum1: 0,
						Sum2: 0,
						Sum3: 0,
						Sum4: 0
					},
					toothCode,
					jawCode,
					state = rec.get('state');
				for (toothCode in state) {
					data['TypeCode' + toothCode] = state[toothCode]['ToothStateType_Code'];
					data['TypeNick' + toothCode] = state[toothCode]['ToothStateType_Nick'];
					data['Value' + toothCode] = state[toothCode]['ToothState_Value'];
					jawCode = state[toothCode]['JawPartType_Code'];
					data['Sum' + jawCode] += state[toothCode]['ToothState_Value'];
				}
				data.Sum1 = data.Sum1.toFixed(2);
				data.Sum2 = data.Sum2.toFixed(2);
				data.Sum3 = data.Sum3.toFixed(2);
				data.Sum4 = data.Sum4.toFixed(2);
				tpl.overwrite(body, data);
				return true;
			},
			/**
			 * Возвращаем строку для печати с клиента
			 * @return {String}
			 */
			getParodontogrammaHtml: function() {
				var body = Ext6.get(document.createElement('body'));
				this.updateParodontogramma(body);
				return '<html><head><title>Печать</title>' +
					'<style type="text/css">' +
					'div.parodontogramma { margin: 20px; }' +
					'table, span, div, td { font-family: tahoma,arial,helvetica,sans-serif;}' +
					'td.state-type-1 { border: 1px solid #808080; }' +
					'td.state-type-2 { border: 3px solid #000000; }' +
					'td.state-type-3 { border: 3px solid #000000; }' +
					'td.state-type-4 { border: 3px solid #000000; }' +
					'td.state-type-5 { border: 3px solid #000000; }' +
					'td.state-type-6 { border: 3px solid #000000; }' +
					'</style>' +
					'<style type="text/css" media="print">' +
					'@page port { size: portrait }' +
					'@page land { size: landscape }' +
					'</style>' +
					'</head>' +
					'<body>' + body.dom.innerHTML + '</body></html>';
			}
		});

		me.tbar = Ext6.create('Ext6.Toolbar', {
			style: me.topToolbarStyle || '',
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