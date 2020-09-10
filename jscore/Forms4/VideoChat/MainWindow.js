Ext6.define('videoChat.MainWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swVideoChatWindow',
	requires: [
		'videoChat.ContactInfoPanel',
		'videoChat.ContactViewPanel',
		'videoChat.MessagesPanel',
		'videoChat.HistoryPanel'
	],

	maximized: true,
	autoShow: false,
	cls: 'arm-window-new swVideoChatWindow',
	title: 'Видеосвязь',
	iconCls: 'videos-chat-icon-tab',
	header: false,
	renderTo: main_center_panel.body.dom,
	layout: 'border',
	constrain: true,
	header: false,
	width: 640,
	height: 480,

	listeners: {
		beforehide: function() {
			var me = this;

			if (me.settingsPanel.isVisible()) {
				me.settingsPanel.hide();
			}
			if (me.historyPanel.isVisible()) {
				me.historyPanel.hide();
			}
			if (me.callContactPanel.isVisible()) {
				me.callContactPanel.hide();
			}

			if (me.engine) {
				me.engine.removeEvent('setStatus', me.onSetCallStatus);
			}
		}
	},

	onShowSettingsPanel: function() {
		var me = this;

		var video = me.cameraView.el.down('video').dom;

		me.avatarView.setData1(me.engine.getSettings());

		me.cameraCombo.getStore().removeAll();
		me.microCombo.getStore().removeAll();

		me.cameraCombo.onLoad();
		me.microCombo.onLoad();

		me.engine.getPlugedDevices({
			withStream: true, video: true, audio: true
		}).then(function(data) {
			video.srcObject = data.stream;

			me.cameraCombo.store.loadData(data.devices.video);
			me.microCombo.store.loadData(data.devices.audio);

			me.cameraCombo.onLoad();
			me.microCombo.onLoad();

			if (me.cameraCombo.getStore().getCount() > 1) {
				var videoDevice = me.engine.getCurrentVideoDevice(data) || {};
				var cameraIndex = me.cameraCombo.getStore().find('deviceId', videoDevice.deviceId) || 1;
				var cameraRecord = me.cameraCombo.getStore().getAt(cameraIndex);

				me.cameraCombo.setValue(cameraRecord.get('deviceId'));
				me.cameraCombo.fireEvent('select', me.cameraCombo, cameraRecord);
			} else {
				me.cameraCombo.setValue(null);
				me.cameraCombo.fireEvent('select', me.cameraCombo, null);
			}

			if (me.microCombo.getStore().getCount() > 1) {
				var audioDevice = me.engine.getCurrentAudioDevice(data) || {};
				var microIndex = me.microCombo.getStore().find('deviceId', audioDevice.deviceId) || 1;
				var microRecord = me.microCombo.getStore().getAt(microIndex);

				me.microCombo.setValue(microRecord.get('deviceId'));
				me.microCombo.fireEvent('select', me.microCombo, microRecord);
			} else {
				me.microCombo.setValue(null);
				me.microCombo.fireEvent('select', me.microCombo, null);
			}
		}).catch(function(error) {
			log(error);

			me.cameraCombo.store.loadData(me.engine.getVideoDevices());
			me.microCombo.store.loadData(me.engine.getAudioDevices());

			Ext6.Msg.alert(langs('Ошибка'), error.name+': '+error.message);
		});
	},

	onHideSettingsPanel: function() {
		var me = this;

		var video = me.cameraView.el.down('video').dom;
		me.engine.stopVideo({video: video});
	},

	callContact: function(record, type) {
		var me = this;
		type = type || 'video';

		if (!record) {
			record = me.ContactGrid.getSelection()[0];
		}

		if (!record || !String(type).inlist(['video', 'audio']) ||
			Ext6.isEmpty(record.get('pmUser_id')) || record.get('Status') != 'online'
		) {
			return;
		}

		if (me.settingsPanel.isVisible()) {
			me.settingsPanel.hide();
		}

		me.engine.call(record, type == 'video', true);
	},

	addContact: function(record) {
		var me = this;

		if (!record) {
			record = me.ContactGrid.getSelection()[0];
		}

		if (!record || Ext6.isEmpty(record.get('pmUser_id')) || record.get('Status') != 'add') {
			return;
		}

		var params = {
			pmUserCache_rid: record.get('pmUser_id')
		};

		me.mask('Добавление контакта...');

		Ext6.Ajax.request({
			url: '/?c=VideoChat&m=addPMUserContact',
			params: params,
			success: function() {
				me.unmask();
				me.loadContactList(true);
			},
			failure: function() {
				me.unmask();
			}
		});
	},

	deleteContact: function(record) {
		var me = this;

		if (!record) {
			record = me.ContactGrid.getSelection()[0];
		}

		if (!record || Ext6.isEmpty(record.get('pmUser_id')) || !record.get('Status').inlist(['online','offline'])) {
			return;
		}

		var params = {
			pmUserCache_rid: record.get('pmUser_id')
		};

		Ext6.Msg.show({
			title: 'Подтверждение',
			msg: 'Вы действительно хотите удалить контакт?',
			buttons: Ext6.Msg.YESNO,
			icon: Ext6.Msg.QUESTION,
			fn: function(btn) {
				if (btn === 'yes') {
					me.mask('Удаление контакта...');
					Ext6.Ajax.request({
						url: '/?c=VideoChat&m=deletePMUserContact',
						params: params,
						success: function() {
							me.unmask();
							me.loadContactList(true);
						},
						failure: function() {
							me.unmask();
						}
					});
				}
			}
		});
	},

	loadContactList: function(force) {
		var me = this;
		var baseForm = me.ContactFilterPanel.getForm();
		var store = me.ContactGrid.getStore();
		var counter = me.ContactCountPanel;

		var params =  {
			query: me.QueryField.getValue(),
			searchInPromed: me.ContactTabPanel.activeTab.searchInPromed
		};

		if (params.searchInPromed) {
			params.Lpu_oid = baseForm.findField('Lpu_oid').getValue();
			params.LpuSection_id = baseForm.findField('LpuSection_id').getValue();
			if (!Ext6.isNumeric(params.LpuSection_id)) params.LpuSection_id = null;
			params.Dolgnost_id = baseForm.findField('Dolgnost_id').getValue();
		}

		if (force || !store.lastOptions || Ext6.encode(store.lastOptions.params) != Ext6.encode(params)) {
			store.removeAll();
			counter.refresh();

			store.load({
				params: params,
				callback: function() {
					counter.refresh();
				}
			});
		}
	},

	refreshQueryFieldTrigger: function(value) {
		var me = this;
		var isEmpty = Ext6.isEmpty(value || me.QueryField.getValue());
		me.QueryField.triggers.clear.setVisible(!isEmpty);
		me.QueryField.triggers.search.setVisible(isEmpty);
	},

	togglePanel: function(panelName, options) {
		options = options || {};
		var me = this;
		var grid = me.ContactGrid;
		var panels = me.mainPanel.items.items;
		var panel = me[panelName+'Panel'];

		if (panelName == 'contact' && me.ContactGrid.getSelection().length == 0) {
			panel = null;
		}

		panels.forEach(function(item) {
			if (item != panel) item.hide();
		});

		if (panel && panels.indexOf(panel) >= 0) {
			panel.show();
		}
	},

	convertTime: function(time) {
		if (!time) return '00:00';

		var timePart = function(number) {
			return Ext6.String.leftPad(number, 2, '0');
		};

		var _seconds = Math.floor(time);
		var _minutes = Math.floor(_seconds/60);
		var _hours = Math.floor(_minutes/60);

		var hours = _hours;
		var minutes = _minutes - _hours*60;
		var seconds = _seconds - _minutes*60;

		return (hours?timePart(hours)+':':'')+timePart(minutes)+':'+timePart(seconds);
	},

	/**
	 * Выщывается при установке статуса вызова
	 * @param status Установленный статус
	 * @param oldStatus Предыдущий статус
	 * @param cause Причина изменения статуса
	 */
	onSetCallStatus: function(status, oldStatus, cause) {
		var me = this;
		var record = me.ContactGrid.getSelection()[0];

		me.SettingsBtn.setDisabled(status && status != 'free');

		switch(status) {
			case 'free':
				if (record && record.get('id')) {
					me.togglePanel('contact');
				} else {
					me.togglePanel(null);
				}
				me.callMessagesPanel.contacts = [];
				break;
			case 'income':

				break;
			case 'wait':
			case 'waitAnswer':
				me.togglePanel('callContactWait');
				break;
			case 'connect':
				
				break;
			case 'call':
				me.togglePanel('callContact');
				me.selfView.setVisible(me.engine.callType == 'videocall');
				break;
		}
	},

	onConnectUser: function(connection) {
		this.callView.refresh();
	},

	onDisconnectUser: function(connection) {
		this.callView.refresh();
	},
	
	onStartRecording: function(started) {
		var me = this;
		var button = me.toolsPanelVideoChat.down('[iconCls=video-chat-record]');
		
		if (started) {
			button.btnIconEl.addCls('recording');
			button.setTooltip('Завершить запись');
		}
	},
	
	onStopRecording: function(stopped) {
		var me = this;
		var button = me.toolsPanelVideoChat.down('[iconCls=video-chat-record]');
		
		if (stopped) {
			button.btnIconEl.removeCls('recording');
			button.setTooltip('Запись');
		}
	},

	show: function() {
		var me = this;
		var baseForm = me.ContactFilterPanel.getForm();

		me.callParent(arguments);

		if (!me.engine) {
			me.hide();
			Ext6.Msg.alert(langs('Ошибка'), langs('Не найден модуль видеосвязи'));
			return;
		}
		if (!me.engine.status) {
			me.hide();
			Ext6.Msg.alert(langs('Ошибка'), langs('Не установленно соединение с сервером видеосвязи'));
			return;
		}

		var lpuCombo = baseForm.findField('Lpu_oid');

		if (!lpuCombo.getValue()) {
			lpuCombo.store.load({
				callback: function() {
					lpuCombo.setValue(getGlobalOptions().lpu_id);
					me.loadContactList();
				}
			});
		} else {
			me.loadContactList();
		}

		me.engine.addEvent('setStatus', me.onSetCallStatus, me);
		me.onSetCallStatus(me.engine.getStatus());

		if (arguments[0] && arguments[0].message) {
			me.togglePanel('messages', {message: arguments[0].message});
		}
	},

	initComponent: function() {
		var me = this;

		me.engine = sw.Promed.VideoChat;

		var delaySearch = function(delay, force) {
			if (me.delaySearchId) {
				clearTimeout(me.delaySearchId);
			}
			me.delaySearchId = setTimeout(function() {
				me.loadContactList(force);
				me.delaySearchId = null;
			}, delay);
		};

		me.QueryField = Ext6.create('Ext6.form.field.Text', {
			flex: 1,
			userCls: 'VideoChatSearchInput',
			name: 'contact',
			emptyText: 'Поиск',
			enableKeyEvents: true,
			triggers: {
				search: {
					cls: 'x6-form-search-trigger',
					handler: function() {
						me.loadContactList(true);
					}
				},
				clear: {
					cls: 'x6-form-clear-trigger',
					hidden: true,
					handler: function() {
						me.QueryField.setValue('');
						me.refreshQueryFieldTrigger();
						me.loadContactList();
					}
				}
			},
			listeners: {
				keyup: function(field, e) {
					me.refreshQueryFieldTrigger(e.target.value);
					delaySearch(300, e.getKey() == e.ENTER);
				}
			}
		});
		
		me.HistoryBtn = Ext6.create('Ext6.Button', {
			'text': 'История',
			handler: function() {
				if (me.engine.getStatus() == 'free') {
					if (me.historyPanel.isVisible()) {
						me.togglePanel('contact');
					} else {
						me.togglePanel('history');
					}
				}
			}
		});
		
		me.SettingsBtn = Ext6.create('Ext6.Button', {
			userCls: 'videoChatUserSettings',
			iconCls: 'videoChatSettings',
			handler: function() {
				if (me.engine.getStatus() == 'free') {
					if (me.settingsPanel.isVisible()) {
						me.togglePanel('contact');
					} else {
						me.togglePanel('settings');
					}
				}
			}
		});

		me.ContactCountPanel = Ext6.create('Ext6.Panel', {
			border: false,
			style: 'text-align: right;',
			tpl: new Ext6.XTemplate('Найдено: {count} пользователя'),
			html: 'Найдено: 0 пользователя',
			refresh: function() {
				var panel = me.ContactCountPanel;
				var count = me.ContactGrid.store.totalCount;
				panel.setHtml(panel.tpl.apply({count: count}));
			}
		});

		me.ContactTabPanel = Ext6.create('Ext6.TabPanel', {
			cls: 'light-tab-panel',
			border: false,
			bodyBorder: false,
			flex: 1,
			items: [{
				border: false,
				title: 'Все пользователи',
				searchInPromed: true
			}, {
				border: false,
				title: 'Мои контакты',
				searchInPromed: false
			}],
			listeners: {
				tabchange: function(panel, tab) {
					me.ContactFilterPanel.query('[searchInPromedField]').forEach(function(field) {
						field.setVisible(tab.searchInPromed);
					});

					me.loadContactList();
				}
			}
		});

		me.ContactFilterPanel = Ext6.create('Ext6.form.Panel', {
			userCls: 'ContactSearchPanel',
			border: false,
			bodyPadding: 10,
			defaults: {
				labelWidth: 75,
				anchor: '100%',
				matchFieldWidth: false
			},
			items: [me.QueryField, {
				allowBlank: false,
				xtype: 'swLpuCombo',
				name: 'Lpu_oid',
				searchInPromedField: true,
				listeners: {
					select: function(combo, record, index) {
						me.loadContactList();
					},
					change: function(combo, newValue, oldValue) {
						var baseForm = me.ContactFilterPanel.getForm();
						var lpuSectionCombo = baseForm.findField('LpuSection_id');

						lpuSectionCombo.setValue(null);

						if (!newValue) {
							lpuSectionCombo.store.removeAll();
						} else {
							lpuSectionCombo.store.load({
								params: {Lpu_id: newValue, mode: 'combo'}
							});
						}
					}
				}
			}, {
				xtype: 'SwLpuSectionGlobalCombo',
				queryMode: 'local',
				name: 'LpuSection_id',
				searchInPromedField: true,
				listeners: {
					select: function(combo, record, index) {
						me.loadContactList();
					}
				}
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'PostMed',
				displayCode: false,
				typeCode: 'int',
				name: 'Dolgnost_id',
				fieldLabel: 'Должность',
				searchInPromedField: true,
				listeners: {
					select: function(combo, record, index) {
						me.loadContactList();
					}
				}
			}, me.ContactCountPanel]
		});

		var actionIdTpl = new Ext6.Template([
			'{wndId}-{name}-{id}'
		]);
		var toolTpl = new Ext6.Template([
			'<span id="{actionId}" class="contact-btn contact-btn-{name} {cls}" data-qtip="{qtip}"></span>'
		]);
		var createTool = function(toolCfg) {
			if (toolCfg.hidden) return '';
			var obj = Ext6.apply({wndId: me.getId()}, toolCfg);
			obj.actionId = actionIdTpl.apply(obj);
			Ext6.defer(function() {
				var el = Ext.get(obj.actionId);
				if (el) {
					el.removeAllListeners();
					el.on('click', function (e) {
						e.stopEvent();
						if (toolCfg.menu) {
							toolCfg.menu.showBy(e.target);
						}
						if (toolCfg.handler) {
							toolCfg.handler();
						}
					});
				}
			}, 10);
			return toolTpl.apply(obj);
		};

		var getVideoCallCls = function(record) {
			switch(true) {
				case record.get('videocall'):
					return 'active';
				case record.get('audiocall'):
				case record.get('Status') != 'online':
					return 'disabled';
			}
			return '';
		};
		var getAudioCallCls = function(record) {
			switch(true) {
				case record.get('audiocall'):
					return 'active';
				case record.get('videocall'):
				case record.get('Status') != 'online':
					return 'disabled';
			}
			return '';
		}

		var toolsRenderer = function(value, meta, record) {
			var showTools = (
				record.get('active') ||
				record.get('videocall') ||
				record.get('audiocall')
			);
			if (!showTools) return '';

			var id = record.get('pmUser_id');

			var tools = [{
				id: id,
				name: 'add',
				qtip: 'Добавить в контакты',
				hidden: record.get('Status') != 'add',
				handler: function() {
					me.addContact(record);
				}
			}, {
				id: id,
				name: 'videocall',
				cls: getVideoCallCls(record),
				qtip: 'Видеозвонок',
				handler: function() {
					me.callContact(record, 'video');
				}
			}, {
				id: id,
				name: 'audiocall',
				cls: getAudioCallCls(record),
				qtip: 'Вызов',
				handler: function() {
					me.callContact(record, 'audio');
				}
			}, {
				id: id,
				name: 'menu',
				qtip: 'Меню',
				menu: Ext6.create('Ext6.menu.Menu', {
					items: [{
						text: 'Удалить контакт',
						handler: function() {
							me.deleteContact(record);
						}
					}]
				})
			}];

			return tools.map(createTool).join('');
		};

		var contactTpl = new Ext6.XTemplate(
			'<div class="contact-cell">',
				'<div class="contact-avatar-panel">',
					'<tpl if="Ext6.isEmpty(values.Avatar)"><div class="contact-avatar empty"></div></tpl>',
					'<tpl if="!Ext6.isEmpty(values.Avatar)"><div class="contact-avatar photo" style="background-image: url({Avatar})"></div></tpl>',
					'<span class="contact-avatar-icon contact-{Status}"></span>',
				'</div>',
				'<div class="contact-text-panel">',
					'<p class="contact-text" style="font: 13px/17px Roboto; color: #000;">{SurName} {FirName} {SecName}</p>',
					'<p class="contact-subtext"style="font: 12px/17px Roboto; color: #999;">{Login}</p>',
				'</div>',
				'<div class="contact-tools-panel">',
					'{tools}',
				'</div>',
			'</div>'
		);

		var contactRenderer = function(value, meta, record) {
			var obj = Ext6.apply(record.data, {
				tools: toolsRenderer.apply(me, arguments)
			});
			return contactTpl.apply(obj);
		};

		me.ContactGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			region: 'center',
			store: me.engine.contactManager.createStore({
				listeners: {
					beforeload: function(store, operation) {
						if (store.currentOperation) {
							store.currentOperation.abort();
						}

						store.currentOperation = operation;
					},
					load: function(store) {
						store.currentOperation = null;
					},
					update: function(store, record) {
						if (me.ContactGrid && me.ContactGrid.selection == record) {
							me.contactPanel.setContact(record);
						}
					}
				}
			}),
			columns: [
				{dataIndex: 'Contact', flex: 1, renderer: contactRenderer}
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						record.set('active', true);
						if (!me.callContactPanel.isVisible() && !me.callContactWaitPanel.isVisible()) {
							me.togglePanel('contact');
							me.contactPanel.setContact(record);
						}
					},
					deselect: function(model, record) {
						record.set('active', false);
					}
				}
			},
			listeners: {
				itemmouseenter: function(grid, record) {
					if (grid.selection != record) {
						record.set('active', true);
					}
				},
				itemmouseleave: function(grid, record) {
					if (grid.selection != record) {
						record.set('active', false);
					}
				}
			}
		});

		me.leftPanel = Ext6.create('Ext6.Panel', {
			animCollapse: true,
			title: {
				text: 'ВИДЕОСВЯЗЬ',
				style:{'fontSize':'14px', 'fontWeight':'500'},
				rotation: 2,
				textAlign: 'right'
			},
			maxWidth: 550,
			split: true,
			flex: 1,
			region: 'west',
			layout: 'border',
			collapsible: true,
			header: false,
			items: [{
				region: 'north',
				border: false,
				items: [{
					layout: 'hbox',
					bodyStyle: 'border-width: 0 0 1px 0;',
					items: [me.ContactTabPanel, {
						height: 34,
						width: 120,
						border: false,
						layout: {
							type: 'hbox', align: 'middle', pack: 'center'
						},
						items: [
							me.HistoryBtn,
							me.SettingsBtn
						]
					}]
				}, me.ContactFilterPanel]
			}, me.ContactGrid]
		});


		me.cameraCombo = Ext6.create('swBaseCombobox', {
			name: 'Camera',
			width: 360,
			editable: false,
			forceSelection: true,
			triggerAction: 'all',
			valueField: 'deviceId',
			displayField: 'label',
			queryMode: 'local',
			store: {
				fields: [
					{name: 'deviceId', type: 'string'},
					{name: 'kind', type: 'string'},
					{name: 'label', type: 'string', defaultValue: 'unknown'}
				]
			},
			listeners: {
				select: function(combo, record) {
					var video = me.cameraView.el.down('video').dom;

					var deviceId = record ? record.get('deviceId') : null;

					var updateSettings = function(callback) {
						callback = callback || Ext6.emptyFn;
						var settings = me.engine.getSettings();
						if (settings.Camera != deviceId) {
							me.engine.setSettings({Camera: deviceId}, callback);
						} else {
							callback(settings);
						}
					};

					var streamDevice = me.engine.getCurrentVideoDevice({
						stream: video.srcObject
					});

					updateSettings(function() {
						if (!deviceId) {
							me.engine.stopVideo({video: video});
						} else if (!streamDevice || streamDevice.deviceId != deviceId) {
							me.engine.stopVideo({video: video});

							me.engine.getPlugedDevices({
								withStream: true, video: true, audio: true
							}).then(function(data) {
								video.srcObject = data.stream;
							});
						}
					});
				}
			}
		});

		me.cameraView = Ext6.create('Ext6.Panel', {
			border: false,
			width: 360,
			height: 270,
			layout: 'fit',
			bodyStyle: {
				background: 'black'
			},
			html: '<video autoplay muted style="width: 100%; height: 100%;"/>'
		});

		me.microCombo = Ext6.create('swBaseCombobox', {
			name: 'Micro',
			width: 360,
			editable: false,
			forceSelection: true,
			triggerAction: 'all',
			valueField: 'deviceId',
			displayField: 'label',
			queryMode: 'local',
			store: {
				fields: [
					{name: 'deviceId', type: 'string'},
					{name: 'kind', type: 'string'},
					{name: 'label', type: 'string'}
				]
			},
			listeners: {
				select: function(combo, record) {
					var video = me.cameraView.el.down('video').dom;

					var deviceId = record ? record.get('deviceId') : null;

					var updateSettings = function(callback) {
						callback = callback || Ext6.emptyFn;
						var settings = me.engine.getSettings();
						if (settings.Micro != deviceId) {
							me.engine.setSettings({Micro: deviceId}, callback);
						} else {
							callback(settings);
						}
					};

					var streamDevice = me.engine.getCurrentAudioDevice({
						stream: video.srcObject
					});

					updateSettings(function() {
						if (!deviceId) {
							me.engine.stopVideo({video: video});
						} else if (!streamDevice || streamDevice.deviceId != deviceId) {
							me.engine.stopVideo({video: video});

							me.engine.getPlugedDevices({
								withStream: true, video: true, audio: true
							}).then(function(data) {
								video.srcObject = data.stream;
							});
						}
					});
				}
			}
		});

		me.avatarView = Ext6.create('Ext6.Component', {
			width: 160,
			height: 160,
			tpl: [
				'<div class="video-chat-avatar">',
					'<tpl if="Ext6.isEmpty(values.Avatar)"><div class="empty-img"/></tpl>',
					'<tpl if="!Ext6.isEmpty(values.Avatar)"><div class="img" style="background-image: url({Avatar})"/></tpl>',
				'</div>',
			],
			data: {
				Avatar: null
			},
			setData1: function(data) {
				this.setData(data);
				this.fireEvent('updatedata', this, this.data);
			},
			listeners: {
				updatedata: function(comp, data) {
					if (me.deleteAvatarFoto) {
						me.deleteAvatarFoto.setDisabled(Ext6.isEmpty(data.Avatar));
					}
				}
			}
		});

		me.ScreenShotBtn = Ext6.create('Ext6.Button', {
			text: 'Сделать скриншот с веб-камеры',
			minWidth: 250,
			handler: function() {
				var video = me.cameraView.el.down('video').dom;

				if (!video.srcObject) return;

				var canvas = document.createElement('canvas');

				canvas.width = video.videoWidth;
				canvas.height = video.videoHeight;

				canvas.getContext('2d').drawImage(video, 0, 0);

				var params = {
					imageBase64: canvas.toDataURL()
				};

				me.avatarFieldSet.mask('Сохранение...');

				Ext6.Ajax.request({
					url: '/?c=VideoChat&m=saveImage',
					params: params,
					success: function(response) {
						var responseData = Ext6.JSON.decode(response.responseText);

						if (responseData.url) {
							me.engine.setSettings({
								Avatar: responseData.url
							}, function(settings) {
								me.avatarFieldSet.unmask();

								if (settings) {
									me.avatarView.setData1(settings);
								}
							});
						} else {
							me.avatarFieldSet.unmask();
						}
					},
					failure: function(response) {
						me.avatarFieldSet.unmask();
					}
				});
			}
		});
		me.selectAvatarFoto = Ext6.create('Ext6.form.field.File', {
			width: 120,
			margin: '0 0 7 0',
			style: 'display: inline-block;',
			clearOnSubmit: true,
			buttonOnly: true,
			buttonText: 'Добавить фото',
			name: 'ImageFile',
			listeners: {
				change: function() {
					me.avatarFieldSet.mask('Сохранение...');
					me.avatarSegmentButtons.submit({
						url: '/?c=VideoChat&m=uploadImage',
						success: function(form, action) {
							if (action.result.url) {
								me.engine.setSettings({
									Avatar: action.result.url
								}, function(settings) {
									me.avatarFieldSet.unmask();

									if (settings) {
										me.avatarView.setData1(settings);
									}
								});
							} else {
								me.avatarFieldSet.unmask();
							}
						},
						failure: function() {
							me.avatarFieldSet.unmask();
						}
					});
				}
			}
		});
		me.deleteAvatarFoto = Ext6.create('Ext6.Button',{
			text: 'Удалить фото',
			disabled: true,
			width: 120,
			margin: '0 0 7 10',
			handler: function() {
				me.avatarFieldSet.mask('Сохранение...');
				me.engine.setSettings({
					Avatar: null
				}, function(settings) {
					me.avatarFieldSet.unmask();

					if (settings) {
						me.avatarView.setData1(settings);
					}
				});
			}
		});

		me.cameraFieldSet = Ext6.create('Ext6.form.FieldSet', {
			title: 'Камера',
			border: false,
			style: {
				background: 'white',
			},
			layout: 'vbox',
			items: [
				me.cameraCombo,
				me.cameraView
			]
		});
		me.avatarSegmentButtons = Ext6.create('Ext6.form.Panel',{
			width: 251,
			border: false,
			cls: 'avatar-segment-buttons',
			margin: '18 0 0 0',
			items:[
				me.selectAvatarFoto,
				me.deleteAvatarFoto,
				me.ScreenShotBtn

			]
		});
		me.microFieldSet = Ext6.create('Ext6.form.FieldSet', {
			title: 'Микрофон',
			border: false,
			style: {
				background: 'white',
			},
			layout: 'vbox',
			items: [
				me.microCombo
			]
		});
		me.testCall = Ext6.create('Ext6.Button',{
			text: 'Сделать тестовый звонок',
			cls: 'button-primary',
			margin: '35 0 0 0',
		});

		me.avatarFieldSet = Ext6.create('Ext6.form.FieldSet', {
			title: 'Ваш аватар',
			cls: 'user-avatar-settings',
			border: false,
			style: {
				background: 'white',
			},
			items: [
				me.avatarView,
				me.avatarSegmentButtons
			]
		});
		
		me.historyPanel = Ext6.create('videoChat.HistoryPanel', {
			hidden: true
		});

		me.settingsPanel = Ext6.create('Ext6.Panel', {
			title: 'Настройки',
			cls: 'video-chat-user-settings-window',
			layout: 'hbox',
			bodyPadding: '34 0 0 30',
			header: false,
			border: false,
			hidden: true,
			autoScroll: true,
			listeners: {
				show: function() {
					me.onShowSettingsPanel();
				},
				hide: function() {
					me.onHideSettingsPanel();
				}
			},
			items: [{
				flex: 1,
				layout: 'vbox',
				border: false,
				items: [
					me.cameraFieldSet,
					me.microFieldSet,
					me.testCall,
				]
			}, {
				flex: 1,
				layout: 'vbox',
				border: false,
				items: [
					me.avatarFieldSet
				]
			}]
		});

		me.userCallInitialButton = Ext6.create('Ext6.form.Panel', {
			margin: '25 0 0 0',
			//width: 243,
			border: false,
			cls: 'videochat-person-info-buttons',
			items:[{
				xtype: 'button',
				width: 139,
				id: me.getId()+'-call-btn',
				text: 'Видеозвонок',
				iconCls: 'video-call-button-user',
				hidden: true,
				handler: function() {
					var record = me.ContactGrid.getSelection()[0];
					if (record) me.callContact(record, 'video');
				}
			}, {
				xtype: 'button',
				width: 93,
				id: me.getId() + '-call-btn-no-video',
				margin: '0 0 0 10',
				text: 'Вызов',
				iconCls: 'no-video-call-button-user',
				hidden: true,
				handler: function () {
					var record = me.ContactGrid.getSelection()[0];
					if (record) me.callContact(record, 'audio');
				}
			}]
		});

		me.messagesPanel = Ext6.create('videoChat.MessagesPanel', {
			flex: 1,
			bodyStyle: 'border-width: 1px 0 0 0 !important;'
		});

		me.contactPanel = Ext6.create('Ext6.Panel', {
			cls: 'videochat-person-info',
			layout: 'vbox',
			defaults: {
				width: '100%'
			},
			border: false,
			hidden: true,
			setContact: function(contact) {
				if (!me.contactPanel.isVisible()) {
					return;
				}

				Ext6.getCmp(me.getId()+'-contact-info').setContact(contact);

				var status = contact.get('Status');

				//var addBtn = Ext6.getCmp(me.getId()+'-add-btn');
				var callBtn = Ext6.getCmp(me.getId()+'-call-btn');
				var callBtnNoVideo = Ext6.getCmp(me.getId()+ '-call-btn-no-video');

				//addBtn.setVisible(userInfo.Status == 'add');
				callBtn.setVisible(status.inlist(['online','offline']));
				callBtn.setDisabled(status == 'offline');
				callBtnNoVideo.setVisible(status.inlist(['online','offline']));
				callBtnNoVideo.setDisabled(status == 'offline');

				me.messagesPanel.setContacts(contact);
			},
			listeners: {
				show: function() {
					var record = me.ContactGrid.getSelection()[0];
					if (record) {
						me.contactPanel.setContact(record);
					}
				}
			},
			items: [
				{
					layout: 'hbox',
					border: false,
					padding: '10 25',
					items: [
						Ext6.create('videoChat.ContactInfoPanel', {
							id: me.getId()+'-contact-info',
							border: false,
							flex: 1
						}),
						me.userCallInitialButton
					]
				},
				me.messagesPanel
			]
		});

		me.callContactWaitPanel = Ext6.create('Ext6.Panel', {
			layout: {
				type: 'vbox',
				align: 'middle',
				pack: 'center',
			},
			userCls: 'callContactWaitWindow',
			border: false,
			hidden: true,
			setData: function(userInfo) {
				Ext6.getCmp(me.getId()+'-call-avatar').setData(userInfo);
				Ext6.getCmp(me.getId()+'-call-user').setData(userInfo);
			},
			listeners: {
				show: function() {
					var record = me.ContactGrid.getSelection()[0];
					if (record) {
						me.callContactWaitPanel.setData(record.data);
					}
				}
			},
			items: [{
				border: false,
				id: me.getId()+'-call-avatar',
				cls: 'video-chat-call-avatar',
				width: 100,
				height: 100,
				tpl: [
					'<tpl if="Ext6.isEmpty(values.Avatar)"><div class="empty-img"/></tpl>',
					'<tpl if="!Ext6.isEmpty(values.Avatar)"><div class="img"><img src="{Avatar}" alt=""></div></tpl>'
				],
				data: {
					Avatar: null
				}
			}, {
				border: false,
				id: me.getId()+'-call-user',
				cls: 'video-chat-call-user',
				tpl: '{SurName} {FirName} {SecName}',
				data: {
					SurName: '',
					FirName: '',
					SecName: ''
				}
			},{
				border: false,
				id: me.getId()+'-status',
				cls: 'video-chat-call-status',
				html: 'Офтальмолог' +'<br>'+
				'Пермь ГП2'
			}, {
				border: false,
				height: 40,
				width: 100,
				cls: 'video-chat-call-spinner-center',
				html: '<div class="call-accept">' +
				'<div class="bubble">' +
				'<div class="circle"></div>' +
				'</div>' +
				'<div class="bubble">' +
				'<div class="circle"></div>' +
				'</div>' +
				'<div class="bubble">' +
				'<div class="circle"></div>' +
				'</div>' +
				'<div class="bubble">' +
				'<div class="circle"></div>' +
				'</div>' +
				'<div class="bubble">' +
				'<div class="circle"></div>' +
				'</div>' +
				'</div>'
			}, {
				xtype: 'button',
				cls: 'video-chat-call-refuse',
				width: 60,
				height: 60,
				handler: function() {
					me.engine.hangup();
				}
			}]
		});

		me.videoChatImageSelector = Ext6.create('Ext6.form.File', {
			name: 'ImageFile',
			buttonOnly: true,
			margin: '0',
			buttonConfig: {
				text: 'Изображение',
				ui: 'default-small',
				margin: '0 4 0 0',
				padding: '4 10'
			},
			listeners: {
				change: function(field) {
					var files = field.fileInputEl.dom.files;
					var reader = new FileReader();
					var image = new Image();

					reader.onload = function() {
						image.src = reader.result;
					};
					image.onload = function() {
						me.videoChatImageSelector.hide();
						me.videoChatImageCancel.show();
						me.engine.startShowImage(image);
					};

					reader.readAsDataURL(files[0]);
				}
			}
		});

		me.videoChatImageCancel = Ext6.create('Ext6.Button', {
			text: 'Убрать изображение',
			handler: function() {
				me.videoChatImageSelector.show();
				me.videoChatImageCancel.hide();
				me.engine.stopShowImage();
			}
		});

		me.toolsPanelVideoChat = Ext6.create('Ext6.Panel',{
			cls: 'tools-panel-video-chat',
			border: false,
			layout:'hbox',
			flex: 3,
			buttons: [{
				id: me.getId()+'-stream-time',
				cls: 'video-chat-call-time',
				xtype: 'label',
				text: '00:00'
			}, {
				iconCls: 'video-chat-contact-window-speak-refuse',
				minWidth: 32,
				handler: function() {
					me.engine.hangup();
				}
			}, {
				id: me.getId()+'-toggle-camera-btn',
				iconCls: 'video-chat-contact-window-speak-toggle-camera',
				minWidth: 32,
				handler: function () {
					me.engine.toggleMute('video');
				}
			}, {
				id: me.getId()+'-toggle-micro-btn',
				iconCls: 'video-chat-contact-window-speak-toggle-micro',
				minWidth: 32,
				handler: function() {
					me.engine.toggleMute('audio');
				}
			}, {
				id: me.getId()+'-toggle-screen-btn',
				iconCls: 'video-chat-screen-view',
				tooltip: 'Демонстрация экрана',
				minWidth: 32,
				handler: function() {
					me.engine.toggleScreenSharing();
				}
			}, me.videoChatImageSelector, me.videoChatImageCancel, '->', {
				iconCls: 'video-chat-record',
				tooltip: 'Запись',
				minWidth: 32,
				handler: function() {
					if (!me.engine.isRecording()) {
						me.engine.startRecording(me.onStartRecording.bind(me));
					} else {
						me.engine.stopRecording(me.onStopRecording.bind(me));
					}
				}
			}, {
				iconCls: 'video-chat-invite-contact',
				minWidth: 32,
				handler: function() {
					getWnd('swVideoChatContactSelectWindow').show({
						callback: function(record) {
							if (record && record.get('id')) {
								me.engine.call(record);
							}
						}
					});
				}
			}, {
				iconCls: 'video-chat-contact-window-speak-toggle-chat',
				minWidth: 32,
				handler: function() {
					if (me.callMessagesPanel.isVisible()) {
						me.callMessagesPanel.hide();
					} else {
						me.callMessagesPanel.show();
					}
				}
			}, {
				iconCls: 'video-chat-contact-window-speak-resize-window',
				minWidth: 32
			}]
		});

		me.callView = Ext6.create('Ext6.Panel', {
			layout: 'fit',
			border: false,
			cls: 'video-chat-contact-window-speak',
			anchor: '100% 100%',
			bodyStyle: {
				background: 'transparent',
				display: 'flex',
				justifyContent: 'center',
				alignItems: 'center',
				flexWrap: 'wrap',
				alignContent: 'center'
			},
			getVideoBlock: function(userKey) {
				var videoId = 'remote-video-block-'+userKey;
				return Ext6.get(videoId);
			},
			getVideo: function(userKey) {
				var videoId = 'remote-video-'+userKey;
				return Ext6.get(videoId);
			},
			refresh: function() {
				me.callView.removeAll();

				me.engine.connections.forEach(function(connection) {
					me.callView.add(Ext6.create('videoChat.ContactViewPanel', {
						contact: connection.user,
						stream: connection.stream
					}));
				});
			}
		});

		me.selfView = Ext6.create('Ext6.Panel', {
			border: false,
			width: 280,
			//height: 210,
			bodyStyle: {
				background: 'black'
			},
			html: '<video autoplay muted style="width:100%;"></video>'
		});

		me.PanelVideoChatFooter = Ext6.create('Ext6.Panel',{
			layout: {
				type: 'hbox',
				align: 'bottom'
			},
			style: {
				bottom: 0
			},
			border: false,
			bodyStyle: {
				background: 'none'
			},
			items: [
				me.toolsPanelVideoChat,
				me.selfView
			]
		});

		me.callMessagesPanel = Ext6.create('videoChat.MessagesPanel', {
			flex: 1,
			hidden: true,
			bodyStyle: 'border-width: 1px 0 0 0 !important;',
			getContacts: function() {
				return me.engine.connections.map(conn => conn.user);
			}
		});

		me.callContactPanel = Ext6.create('Ext6.Panel', {
			layout: 'vbox',
			defaults: {
				width: '100%',
			},
			border: false,
			hidden: true,
			getVideo: function(type) {
				switch(type) {
					case 'local': return me.selfView.el.down('video').dom;
					//case 'remote': return me.callView.el.down('video').dom;
					default: return null;
				}
			},
			getStream: function(type) {
				return this.getVideo(type).srcObject;
			},
			setStream: function(type, stream) {
				this.getVideo(type).srcObject = stream;
			},
			removeStream: function(type) {
				this.getVideo(type).srcObject = null;
			},
			getTime: function() {
				var video = me.callContactPanel.getVideo('local') || {};
				return video.currentTime;
			},
			onSetMuted: function(user, kind, muted) {
				if (!user) user = me.engine.user;
				var toggleCameraBtn = Ext6.getCmp(me.getId()+'-toggle-camera-btn');
				var toggleMicroBtn = Ext6.getCmp(me.getId()+'-toggle-micro-btn');

				switch(true) {
					case me.engine.user == user && kind == 'video':
						me.selfView.setVisible(!muted);
						toggleCameraBtn.setIconCls(muted
							?'video-chat-contact-window-speak-toggle-no-camera'
							:'video-chat-contact-window-speak-toggle-camera');
						break;
					case me.engine.user == user && kind == 'audio':
						toggleMicroBtn.setIconCls(muted
							?'video-chat-contact-window-speak-toggle-no-micro'
							:'video-chat-contact-window-speak-toggle-micro');
						break;
					case me.engine.user != user && kind.inlist(['video','screen']):
						me.callView.refresh();
						break;
				}
			},
			onScreenSharing: function(active) {
				var toggleScreenBtn = Ext6.getCmp(me.getId()+'-toggle-screen-btn');
				toggleScreenBtn.setIconCls('video-chat-screen-view'+(active?'-active':''));
			},
			listeners: {
				show: function() {
					var localStream = me.engine.stream;

					me.videoChatImageSelector.show();
					me.videoChatImageCancel.hide();

					me.videoChatImageSelector.triggers.filebutton.el.setWidth(
						me.videoChatImageSelector.button.getWidth()
					);

					if (me.engine.getStatus().inlist(['call'])) {
						me.callContactPanel.setStream('local', localStream);

						me.callView.refresh();

						var timeLabel = Ext6.getCmp(me.getId()+'-stream-time');

						me.timeIntervalId = setInterval(function() {
							if (!me.callContactPanel) {
								clearInterval(me.timeIntervalId);
								me.timeIntervalId = null;
							} else {
								timeLabel.setText(me.convertTime(me.callContactPanel.getTime()));
							}
						}, 300);

						me.callContactPanel.onSetMuted(null, 'video', me.engine.callType != 'videocall');
						me.callContactPanel.onSetMuted(null, 'audio', false);
						me.callContactPanel.onSetMuted(null, 'screen', true);
						
						me.engine.addEvent('screenSharing', me.callContactPanel.onScreenSharing, me);
						me.engine.addEvent('setMuted', me.callContactPanel.onSetMuted, me);

						me.engine.addEvent('connectUser', me.onConnectUser, me);
						me.engine.addEvent('disconnectUser', me.onDisconnectUser, me);
					}
				},
				hide: function() {
					me.onStopRecording(true);
					me.callMessagesPanel.hide();

					if (me.engine.getStatus().inlist(['call'])) {
						me.callContactPanel.removeStream('local');
						me.callView.refresh();
					 	me.engine.hangup();

						if (me.timeIntervalId) {
							clearInterval(me.timeIntervalId);
							me.timeIntervalId = null;
						}

					 	me.engine.removeEvent('screenSharing', me.callContactPanel.onScreenSharing);
					 	me.engine.removeEvent('setMuted', me.callContactPanel.onSetMuted);
						me.engine.removeEvent('connectUser', me.onConnectUser);
						me.engine.removeEvent('disconnectUser', me.onDisconnectUser);
					}
				}
			},
			items: [{
				layout: 'absolute',
				border: false,
				flex: 2,
				bodyStyle: {
					background: 'url("../img/icons/videochat/callContactWait.png") no-repeat center'
				},
				items: [
					me.callView,
					me.PanelVideoChatFooter
				]
			}, me.callMessagesPanel]
		});
		
		/*me.selfContactViewPanel = Ext6.create('videoChat.ContactViewPanel', {
			contact: sw.Promed.VideoChat.user, 
			needBackground: true
		});*/

		me.mainPanel = Ext6.create('Ext6.Panel', {
			border: false,
			layout: 'fit',
			flex: 1,
			items: [
				me.settingsPanel,
				me.historyPanel,
				me.contactPanel,
				me.callContactWaitPanel,
				me.callContactPanel
			]
		});

		me.mainContainer = Ext6.create('Ext6.Panel', {
			layout: 'vbox',
			region: 'center',
			flex: 3,
			defaults: {
				width: '100%'
			},
			items: [
				me.mainPanel
			]
		});
		Ext6.apply(me, {
			items: [
				me.leftPanel,
				me.mainContainer
			]
		});
		me.callParent(arguments);
	}
});