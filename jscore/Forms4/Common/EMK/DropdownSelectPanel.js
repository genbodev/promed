Ext6.define('common.EMK.DropdownSelectPanel', {
	extend: 'Ext6.menu.Menu',
	border: false,
	plain: true,
	width: 704,
	height: 240,
	minHeight: 150,
	layout: 'fit',
	focusOnToFront: true,
	ignoreParentClicks: true,
	ariaRole: 'dialog',
	userCls: 'template-search packet-fast-exec',
	shadow: 'frame',
	resizable: {
		dynamic: false,
		transparent: true
	},
	resizeHandles: 'se',
	parentPanel: '',
	params: {},
	onSelect: Ext6.emptyFn,

	select: function() {
		var me = this;
		var selection = me.PacketPrescrGrid.getSelection();
		if (selection.length > 0) {
			var rec = selection[0];
			me.onSelect(rec.getData());
			me.hide();
		}
	},
	setFavorite: function(rec,view){
		var win = this,
			cntr = win.parentPanel.getController(),
			params = {};
		win.PacketPrescrGrid.mask('Изменение параметра');
		params.MedPersonal_id = cntr.getParam('MedPersonal_id');
		params.PacketPrescr_id = rec.get('PacketPrescr_id');
		if(rec.get('Packet_IsFavorite') == 2)
			params.Packet_IsFavorite = 0;
		else
			params.Packet_IsFavorite = 2;
		Ext6.Ajax.request({
			url: '/?c=PacketPrescr&m=setPacketFavorite',
			params: params,
			callback: function(options, success, response) {
				if (success)
					rec.set('Packet_IsFavorite', (params.Packet_IsFavorite ? 2 : 0),{'silent': true});
				//иначе "менюшка" с пакетами закрывается при обновлении рендера всего грида без {'silent': true}
				view.refreshNode(rec);
				win.PacketPrescrGrid.unmask();
			}
		});
	},
	load: function(force) {
		var me = this;

		var params = Ext6.apply({}, me.params, {
			query: me.QueryField.getValue(),
			onlyFavor: (me.ModeToggler.getValue())?true:''
		});
		if(me.PersonInfoPanel){
			params.Sex_Code = me.PersonInfoPanel.getFieldValue('Sex_Code');
			var age = me.PersonInfoPanel.getFieldValue('Person_Age');
			if(age)
				params.PersonAgeGroup_Code = (age<18)?2:1;
		}

		var withDiag = this.down('#withDiag').getValue();
		if(withDiag && me.parentPanel.ownerPanel.getDiagId())
			params.Diag_id = me.parentPanel.ownerPanel.getDiagId();
		var store = me.PacketPrescrGrid.getStore();


		if (force || Ext6.encode(store.lastOptions.params) != Ext6.encode(params)) {
			me.PacketPrescrGrid.getStore().load({params: params});
		}

	},

	setParams: function(params) {
		this.params = params;
		this.load(true);
	},
	openPacketSelectWindow: function(){
		var me = this,
			EvnPrescrPanel = me.parentPanel,
			EvnPrescrPanelCntr = EvnPrescrPanel.getController();
		EvnPrescrPanelCntr.openTemplate();
	},
	refreshQueryFieldTrigger: function(value) {
		var isEmpty = Ext6.isEmpty(value || this.QueryField.getValue());
		this.QueryField.triggers.clear.setVisible(!isEmpty);
		this.QueryField.triggers.search.setVisible(isEmpty);
	},

	deletePacket: function(rec){
		var win = this;
		if(rec){
			var packet_id = rec.get('PacketPrescr_id');
			Ext6.Ajax.request({
				url: '/?c=PacketPrescr&m=deletePacket',
				params: {
					PacketPrescr_id: packet_id
				},
				callback: function(options, success, response) {
					win.load(true);
				}.createDelegate(this)
			});
		}
		else
			Ext6.Msg.alert('Ошибка', 'Необходимо выбрать пакет');
	},

	doSave: function(mode,cbFn,checkDrug){
		// доступен только этот режим
		// applyAllPacket - режим применения пакета целиком
		var me = this,
			save_url = '/?c=PacketPrescr&m=savePacketPrescrForm',
			evnPrescrPanel = me.parentPanel,
			cntr = evnPrescrPanel.getController(),
			data = cntr.getData(),
			params;
		if(Ext6.isEmpty(me.selectPacketPrescr_id)){
			Ext6.Msg.alert('Ошибка', 'Необходимо выбрать пакет');
			return false;
		}
		if(Ext6.isEmpty(data)){
			Ext6.Msg.alert('Ошибка', 'Ошибка получения данных');
			return false;
		}
		params = {
			PacketPrescr_id: me.selectPacketPrescr_id,
			PersonEvn_id: data.PersonEvn_id,
			Person_id: data.Person_id,
			Server_id: data.Server_id,
			Evn_pid: data.Evn_id,
			EvnVizitPL_id: data.Evn_id,
			parentEvnClass_SysNick: 'EvnVizitPL',
			LpuSection_id: getGlobalOptions().CurLpuSection_id,
			mode: mode
		};

		if(checkDrug){//yl:5588 проверка пакета перед применением
			params.checkDrug="check";
			me.mask('Проверка лекарственных назначений из пакета');
		}else{
			me.mask('Сохранение назначений');
		};

		Ext6.Ajax.request({
			url: save_url,
			callback: function(opt, success, response) {
				me.unmask();
				me.hide();
				if(checkDrug){//была проверка лекарств
					if (success && response && response.responseText && (resp = Ext.util.JSON.decode(response.responseText))) {
						checkPacketPrescrTreat(me,mode,cbFn,resp)
					} else {
						sw.swMsg.alert(langs("Ошибка"), "При проверке лекарственных назначений из пакета возникли ошибки");
					}
				}else{//обычное применения пакета
					if(evnPrescrPanel.collapsed){
						if(!evnPrescrPanel.titleCounter)
							cntr.loadGrids();
						evnPrescrPanel.expand();
					}
					else
						cntr.loadGrids();
				};
			},
			params: params
		});
	},

	openPacketPrescrSaveWindow: function(rec){
		var me = this,
			evnPrescrPanel = me.parentPanel,
			cntr = evnPrescrPanel.getController(),
			data = cntr.getData();
		if(rec){
			getWnd('swPacketPrescrCreateWindow').show({
				MedPersonal_id: data.MedPersonal_id,
				PacketPrescr_id: rec.get('PacketPrescr_id'),
				title: 'Свойства пакета',
				callback: function() {
					me.load(true);
				}
			});
		}
		else
			Ext6.Msg.alert('Ошибка', 'Необходимо выбрать пакет');
	},

	show: function() {
		var me = this;
		if (arguments[0]) {
			me._lastAlignTarget = arguments[0].target;
			me._lastAlignToPos = arguments[0].align || me.defaultAlign;
			me._lastAlignToOffsets = arguments[0].offset || me.alignOffset;
			var force = true;
		}

		me.callParent(arguments);

		me.QueryField.focus();

		log('select template', me);
		me.load(force);
	},

	initComponent: function () {
		var me = this;

		var delaySearch = function(delay) {
			if (me.delaySearchId) {
				clearTimeout(me.delaySearchId);
			}
			me.delaySearchId = setTimeout(function() {
				me.load();
				me.delaySearchId = null;
			}, delay);
		};

		me.QueryField = Ext6.create('Ext6.form.field.Text', {
			userCls:'template-search-field',
			name: 'query',
			emptyText: 'Пакеты назначений',
			margin: 0,
			width: 246,
			enableKeyEvents: true,
			triggers: {
				search: {
					cls: 'x6-form-search-trigger',
					handler: function() {
						me.load(true);
					}
				},
				clear: {
					cls: 'sw-clear-trigger',
					hidden: true,
					handler: function() {
						me.QueryField.setValue('');
						me.refreshQueryFieldTrigger();
						me.load();
					}
				}
			},
			listeners: {
				keyup: function(field, e) {
					if (!Ext6.isEmpty(e.target.value)) {
						me.ModeToggler.setValue(null);
					}
					me.refreshQueryFieldTrigger(e.target.value);
					delaySearch(300);
				}
			}
		});

		me.ModeToggler = Ext6.create('Ext6.button.Segmented', {
			userCls: 'packet-fast-exec-mode-toggler',
			//cls: 'sw-toggler',
			margin: '0 27 0 0',
			allowDepress: true,
			items: [{
				text: 'Избранные',
				userCls: 'button-without-frame bottom-icon buttonFavor',
				iconCls: 'favTemp-btn-icon',
				value: '1'
			}],
			listeners: {
				change: function(toggler, value) {
					if (!Ext6.isEmpty(value)) {
						me.QueryField.setValue('');
					}
					me.load();
				}
			}
		});

		var toolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			style: 'background-color: #EEE;',
			userCls: 'packet-fast-exec-toolbar',
			padding: '6 10',
			height: 59,
			border: true,
			items: [
				me.QueryField,
				{
					xtype: 'checkbox',
					boxLabel: 'С учетом диагноза',
					itemId: 'withDiag',
					checked: true,
					cls: 'drop-down-template-search-checkbox-container',
					margin: '0 20 0 32',
					listeners: {
						change: function(c, val){
							me.load();
						}
					}
				},
				me.ModeToggler,
				{
					margin: 0,
					cls: 'sw-tool all-packets-btn',
					iconCls: 'icon-all-packet',
					text: 'Все пакеты',
					handler: function() {
						me.openPacketSelectWindow();
					}
				}
			]
		});

		var rowTooltipTpl = new Ext6.Template(
			'data-qtip= "<div><p><b>Краткое описание: </b>{PacketPrescr_Descr}</p>',
			'<p><b>Диагнозы:</b> {Diag_Codes}</p>',
			'<p><b>Видимость: </b>{PacketPrescrVision_Name}</p>',
			'<p><b>Дата изменения: </b>{PacketPrescr_updDT}</p>',
			'<p><b>Пол: </b>{Sex_Name}</p>',
			'<p><b>Возраст: </b>{PersonAgeGroup_Name}</p></div>"'
			//<span>Места оказания услуг:</span>{subcaption2}
		);

		var contactTpl = new Ext6.XTemplate(
			'<div class="contact-cell">',
			'<div class="contact-text-panel" {rowTooltipTpl}>',
			'<p><span class="contact-text" style="font: 13px/17px Roboto; color: #000;">{PacketPrescr_Name} </span><span style="font: 400 11px/17px Roboto, Helvetica, Arial, Geneva, sans-serif; color: #999;">{PacketPrescr_Descr}</span></p>',
			'</div>',
			'<div class="contact-tools-panel">{tools}</div>',
			'</div>'
		);

		var actionIdTpl = new Ext6.Template([
			'{wndId}-{name}-{id}'
		]);
		var toolTpl = new Ext6.Template([
			'<span id="{actionId}" class="packet-btn packet-btn-{name} {cls}" data-qtip="{qtip}"></span>'
		]);
		var createTool = function(toolCfg) {
			if (toolCfg.hidden) return '';
			var obj = Ext6.apply({wndId: me.getId()}, toolCfg);
			obj.actionId = actionIdTpl.apply(obj);
			Ext6.defer(function() {
				var el = Ext.get(obj.actionId);
				if (el) el.on('click', function(e) {
					e.stopEvent();
					if (toolCfg.menu) {
						toolCfg.menu.showBy(e.target);
					}
					if (toolCfg.handler) {
						toolCfg.handler();
					}
				});
			}, 10);
			return toolTpl.apply(obj);
		};
		var toolsRenderer = function(value, meta, record) {
			if (!record.get('active')) return '';
			var selMod = me.PacketPrescrGrid.getSelectionModel();
			var id = record.get('PacketPrescr_id');
			var tools = [{
				id: id,
				name: 'edit',
				qtip: 'Редактировать (просмотреть состав)',
				handler: function() {
					selMod.select(record);
					/*if(record){
						me.selectPacketPrescr_id = record.get('PacketPrescr_id');
						me.setMode('addPrescrByPacket');
					}*/
				}
			}, {
				id: id,
				name: 'apply',
				qtip: 'Применить пакет',
				handler: function() {
					if(record){
						me.selectPacketPrescr_id = record.get('PacketPrescr_id');
						me.doSave('applyAllPacket',null,true);
					}
				}
			}, {
				id: id,
				name: 'delPacket',
				qtip: 'Удалить пакет',
				handler: function() {
					if(record) {
						me.deletePacket(record);
					}
				}
			}, {
				id: id,
				name: 'config',
				qtip: 'Свойства пакета',
				handler: function() {
					if(record) {
						me.openPacketPrescrSaveWindow(record);
					}
				}
			}];

			return tools.map(createTool).join('');
		};
		var captionRenderer = function(value, meta, record) {
			/*meta.tdAttr = rowTooltipTpl.apply({
				PacketPrescr_Descr: record.get('PacketPrescr_Descr')?record.get('PacketPrescr_Descr'):'',
				Diag_Codes: record.get('Diag_Codes')?record.get('Diag_Codes'):'',
				PacketPrescrVision_Name: record.get('PacketPrescrVision_Name')?record.get('PacketPrescrVision_Name'):'',
				PacketPrescr_updDT: record.get('PacketPrescr_updDT')?record.get('PacketPrescr_updDT'):''
			});*/
			//return value;
			var obj = Ext6.apply(record.data, {
				tools: toolsRenderer.apply(me, arguments),
				rowTooltipTpl: rowTooltipTpl.apply({
					PacketPrescr_Descr: record.get('PacketPrescr_Descr')?record.get('PacketPrescr_Descr'):'',
					Diag_Codes: record.get('Diag_Codes')?record.get('Diag_Codes'):'',
					PacketPrescrVision_Name: record.get('PacketPrescrVision_Name')?record.get('PacketPrescrVision_Name'):'',
					PacketPrescr_updDT: record.get('PacketPrescr_updDT')?record.get('PacketPrescr_updDT'):'',
					Sex_Name: record.get('Sex_Name')?record.get('Sex_Name'):'',
					PersonAgeGroup_Name: record.get('PersonAgeGroup_Name')?record.get('PersonAgeGroup_Name'):''
				})
			});
			return contactTpl.apply(obj);
		};
		me.PacketPrescrGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'cureStandartsGrid',
			userCls: 'template-search-grid',
			tbar: toolbar,
			emptyText: 'Нет результатов.',
			hideHeaders: true,
			border: false,
			columns: [
				{
					dataIndex: 'Packet_IsFavorite',
					xtype: 'actioncolumn',
					width: 30,
					align: 'end',
					items: [{
						getClass: function (value) {
							return (value == 2)
								? 'icon-star-active'
								: 'icon-star';
						},
						getTip: function (value) {
							return (value == 2)
								? 'Убрать из избранных'
								: 'Добавить в избранное';
						},
						handler: function (view, rowIndex, colIndex, item, e, record) {
							me.setFavorite(record, view);
						}
					}]
				}, {
					flex: 1,
					padding: '2 0 7 10',
					dataIndex: 'PacketPrescr_Name',
					text: '',
					renderer: captionRenderer
					/*renderer: function(value,el) {
						var resStr = '';
						if(el && el.record){
							resStr += '<div class="rowCureStandart" ><span class="favTemp" >'+value+'</span></div>';
						}
						return resStr;
					}*/
				}],
			store: {
				fields: [{
					name: 'PacketPrescr_id',
					type: 'string'
				}, {
					name: 'PacketPrescr_Name',
					type: 'string'
				}, {
					name: 'active',
					type: 'bool',
					defaultValue: false
				}],
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function (model, record) {
						record.set('active', true);
					},
					deselect: function (model, record) {
						record.set('active', false);
					}
				}
			},
			listeners: {
				itemdblclick: function (cmp, record) {
					me.select();
				},
				select: function () {
					me.select();
				},
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

		me.resizerPanel = Ext6.create('Ext6.Component', {
			dock: 'bottom',
			cls: 'resizer-panel',
			height: 13,
			html: '<div class="icon-resizer"></div>'
		});

		Ext6.apply(me, {
			items: [
				me.PacketPrescrGrid
			],
			dockedItems: [
				me.resizerPanel
			]
		});

		this.callParent(arguments);
	}
});