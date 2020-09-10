Ext6.define('common.EMK.QuickPrescrSelect.swDrugQuickSelectWindow', {
	extend: 'Ext6.menu.Menu',
	border: false,
	plain: true,
	width: 745,
	height: 425,
	minHeight: 350,
	layout: 'fit',
	focusOnToFront: true,
	ignoreParentClicks: true,
	ariaRole: 'dialog',
	userCls: 'template-search packet-fast-exec quick-select-window-drug',
	shadow: 'frame',
	resizable: {
		dynamic: false,
		transparent: true
	},
	resizeHandles: 'se',
	parentPanel: '',
	params: {},
	setParams: function(params) {
		this.params = params;
		//Убрал фоновую загрузку стора во время открытия случая лечения
		//this.reloadDrugGrid();
	},
	show: function(data) {
		var me = this;
		if (data) {
			me._lastAlignTarget = data.target;
			me._lastAlignToPos = data.align || me.defaultAlign;
			me._lastAlignToOffsets = data.offset || me.alignOffset;
			var force = true;
		}

		me.callback = data.callback || null;
		me.PacketPrescr_id = data.PacketPrescr_id || null;
		me.isPP = !!me.PacketPrescr_id;

		me.callParent(arguments);

		me.searchCombo.focus();

		me.reloadDrugGrid();

		if(data.Person_id && data.checkPersonPrescrTreat && !me.PacketPrescr_id){//проверка только в назначениях, а не в пакетах
			//yl:5588 количество действующих лекарственных наначений для пациента
			checkPersonPrescrTreat(data.Person_id,me);
		};
	},
	deleteTemplateDrug: function(rec, btn){
		var me = this,
			store = me.DrugTemplateGrid.getStore();
		me.mask('Удаление шаблона');
		Ext6.Ajax.request({
			url: '/?c=PacketPrescr&m=deletePacketPrescrTreat',
			params: {
				PacketPrescrTreat_id: rec.get('PacketPrescrTreat_id')
			},
			callback: function(opt, success, response) {
				me.unmask();
				store.remove(rec);
				if(btn)
					me.getTemplatesMenu(btn);
			}
		});
	},
	reloadDrugGrid: function(cfg){
		if(!cfg || (cfg && Ext6.isObject(cfg) && cfg.DrugTemplateGrid)){
			this.DrugTemplateGrid.getStore().load({
				params: {
					'MedPersonal_id': getGlobalOptions().medpersonal_id
				},
				callback: function(records, operation, success) {
					if(cfg && cfg.cbFn)
						cfg.cbFn(this)
				}
			});
		}
		if(!cfg || (cfg && Ext6.isObject(cfg) && cfg.lastSelectedDrugGrid)) {
			this.lastSelectedDrugGrid.getStore().load({
				params: {
					'MedPersonal_id': getGlobalOptions().medpersonal_id,
					'Lpu_id': getGlobalOptions().lpu_id
				},
				callback: function (records, operation, success) {
					if(cfg && cfg.cbFn)
						cfg.cbFn(this)
				}
			});
		}
	},
	applydrug: function (selectedDrug) {
		var me = this;
		if(!me.isVisible()) return false; // Бывает, после нажатия кнопки комбобокса, запись выбирается, но окно уже закрыто
		me.hide();
		selectedDrug.PacketPrescr_id = me.PacketPrescr_id;
		if(me.PacketPrescr_id){
			selectedDrug.cbFn = function(){
				if(me.callback && typeof me.callback == 'function')
					me.callback();
			};
		} else {
			if(me.callback && typeof me.callback == 'function'){
				selectedDrug.cbFn = function(){
					me.callback();
				};
			}
		}

		me.parentPanel.getController().openEvnPrescrTreatCreateWnd(selectedDrug);
	},
	applydrugwithspec: function (selectedDrugRec) {
		this.hide();
		if(!selectedDrugRec) return false;
		if(typeof selectedDrugRec === 'string')
			selectedDrugRec = this.searchCombo.getStore().findRecord('id',selectedDrugRec);
		this.parentPanel.getController().openSpecification('EvnCourseTreat', false, selectedDrugRec);
	},
	initComponent: function () {
		var me = this;



		this.searchCombo = new Ext6.create('swSearchDrugComplexMnnCombo', {
			userCls:'template-search-field',
			emptyText: 'МНН лекарственного средства',
			margin: 0,
			flex: 1,
			name: 'searchDrugNameCombo',
			listConfig: {
				cls: 'choose-bound-list-menu update-scroller',
				/*itemTpl: [
					'{Drug_Name} <span class="drug-lat-name">{LatName}</span>'
				]*/
			},
			triggers: {
				clear: {
					cls: 'sw-clear-trigger',
					hidden: true,
					handler: function () {
						if (this.disabled) return false;
						this.reset();
						this.triggers.clear.hide();
						this.triggers.search.show();
					}
				},
				search: {
					cls: 'x6-form-search-trigger',
					handler: function () {
						//а хз что тут делать, и так работает
					}
				}
			},
			onBeforeLoad: function(store, oper){
				var xParams = {
					onlyMnn: me.onlyMnn.getValue(),
					findByLatName: false
				};
				const regex = /[a-z-\+\s]+$/gmi;
				const str = me.searchCombo.getRawValue();

				if (regex.test(str)) {
					xParams.findByLatName = true;
				}

				store.getProxy().setExtraParams(xParams);
			},
			listeners:{
				select: function(combo, record, eOpts ) {
					me.applydrug(record.getData());
					combo.reset();
				},
				keyup: function (field, e) {
					if (!Ext6.isEmpty(e.target.value)){
						this.triggers.clear.show();
						this.triggers.search.hide();
					}else {
						this.triggers.clear.hide();
						this.triggers.search.show();
					}
				}
			},
			tpl: new Ext6.XTemplate(
				'<tpl for="."><div class="x6-boundlist-item" style="padding: 7px 10px 6px 10px;">',
				'<div style="position: relative; display: flex">',
				'<p style="font-size: 13px; line-height: 17px; color: #333;">{Drug_Name} <span class="drug-lat-name">{LatName}</span></p>',
				'<div class="search-list-button-container" style="position: absolute; top: 0; right: 0;">',
				'{[this.applydrugwithspec(values.id)]}',
				'{[this.applydrug(values.Drug_id,values.DrugComplexMnn_id,values.ActMatters_id)]}',
				'</div>',
				'</div>',
				'</div></tpl>',
				{
					applydrugwithspec: function(id){
						var btn_str = "";
						if(!me.isPP){
							btn_str = '<p class="boundlist-btn-specific"';
							btn_str += ' data-qtip="Назначить с детализацией" ';
							btn_str += "onclick=\"Ext6.getCmp(\'"+me.getId().trim()+"\').applydrugwithspec(\'" + id + "\');\"";
							btn_str += ' ></p>';
						}
						return btn_str;
					},
					applydrug: function(Drug_id,DrugComplexMnn_id,ActMatters_id){
						var drug_str = Drug_id?Drug_id:'null',
							complex_str = DrugComplexMnn_id?DrugComplexMnn_id:'null',
							ActMatters_id = ActMatters_id?ActMatters_id:'null',
							qtip = ' data-qtip="'+(me.isPP?'Добавить в пакет':'Назначить')+'" ';
						var btn_str = '<p class="boundlist-btn-add" style="margin-left: 10px;" ';
						btn_str += qtip;
						btn_str += " onclick=\"Ext6.getCmp(\'"+me.getId().trim()+"\').applydrug({Drug_id:" + drug_str +",DrugComplexMnn_id:"+complex_str+",ActMatters_id:"+ActMatters_id+"});\"";
						btn_str += ' ></p>';
						return btn_str;
					}
				}
			)
		});

		var contactTpl = new Ext6.XTemplate(
			'<div class="contact-cell">',
			'<div class="contact-text-panel" style="padding-left: 17px;">',
			'<p><span class="contact-text" style="font: 13px/17px Roboto !important; color: #333;">{Drug_Name}</span></p>',
			'</div>',
			'<div class="contact-tools-panel">{tools}</div>',
			'</div>'
		);

		var actionIdTpl = new Ext6.Template([
			'{wndId}-{name}-{id}'
		]);
		var toolTpl = new Ext6.Template([
			'<span id="{actionId}" class="quick-select-btn quick-select-btn-{name} {cls}" data-qtip="{qtip}"></span>'
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
			var tools = [], id = '';
			if(record.get('PacketPrescrTreat_id')){
				id = record.get('PacketPrescrTreat_id');
				tools = [{
					id: id,
					name: 'delPacket',
					qtip: 'Удалить',
					handler: function() {
						me.deleteTemplateDrug(record);
					}
				}];
				record.set('Drug_Name',record.get('PacketPrescrTreat_Name'));
			} else {
				id = (record.get('Drug_id'))?record.get('Drug_id'):record.get('DrugComplexMnn_id');
				tools = [{
					id: id,
					name: 'withoutDate',
					qtip: me.isPP?'Добавить в пакет':'Назначить',
					handler: function() {
						me.applydrug(record.getData());
					}
				}];
				if(!me.isPP) tools.push({
					id: id,
					name: 'specific',
					qtip: 'Назначить с детализацией',
					handler: function() {
						me.applydrugwithspec(record);
					}
				});
			}
			return tools.map(createTool).join('');
		};
		var captionRenderer = function(value, meta, record) {
			var obj = Ext6.apply(record.data, {
				tools: toolsRenderer.apply(me, arguments)
			});
			return contactTpl.apply(obj);
		};

		this.DrugTemplateGrid = Ext6.create('Ext6.grid.Panel', {
			frame: false,
			border: false,
			cls: 'no-border-grid DrugTemplateGrid',
			emptyText: 'Вы можете создать свой шаблон на форме добавления лек. назначений',
			rowLines: false,
			default: {
				border: 0
			},
			itemConfig: {
				height: 28
			},
			columns: [
				{
					dataIndex: 'PacketPrescrTreat_Name',
					flex: 1,
					padding: '2 0 7 0',
					renderer: captionRenderer
				}
			],
			store: {
				fields: [
					{
						name: 'PacketPrescrTreat_id',
						type: 'int'
					}, {
						name: 'PacketPrescrTreat_Name',
						type: 'string'
					}, {
						name: 'Drug_Name',
						type: 'string',
						convert: function (value,rec) {
							return rec.get('PacketPrescrTreat_Name');
						}
					},{
						name: 'active',
						type: 'bool',
						defaultValue: false
					}
				],
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadDrugTemplateList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			},
			listeners:{
				select: function (grid, rec) {
					me.applydrug(rec.getData());
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
			}
		});


		this.lastSelectedDrugGrid = Ext6.create('Ext6.grid.Panel', {
			frame: false,
			border: false,
			rowLines: false,
			minHeight: 100,
			cls: 'lastSelectedDrugGrid',
			emptyText: 'Созданных Вами лекарственных назначений не обнаружено',
			default: {
				border: 0
			},
			itemConfig: {
				padding: 0,
				margin: 0
			},
			columns: [
				{
					dataIndex: 'Drug_Name',
					flex: 1,
					padding: '2 0 7 0',
					renderer: captionRenderer
				}
			],
			store: {
				fields: [
					{
						name: 'Drug_id',
						type: 'int'
					},
					{
						name: 'DrugComplexMnn_id',
						type: 'int'
					}, {
						name: 'Drug_Name',
						type: 'string'
					}, {
						name: 'active',
						type: 'bool',
						defaultValue: false
					}
				],
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadLastSelectedDrugList',
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
			listeners:{
				select: function (grid, rec) {

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

		this.onlyMnn = new Ext6.create('Ext6.form.field.Checkbox', {
			boxLabel: 'Только по МНН',
			itemId: 'onlyMnn',
			minWidth: 120,
			checked: true,
			cls: 'drop-down-template-search-checkbox-container',
			margin: '0 10 0 10'
		});

		this.toolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			style: 'background-color: #EEE;',
			userCls: 'packet-fast-exec-toolbar',
			padding: '6 10',
			docked: 'top',
			height: 59,
			border: true,
			items: [
				me.searchCombo,
				me.onlyMnn,
				{
					xtype: 'checkbox',
					boxLabel: 'Только из остатков',
					itemId: 'only',
					minWidth: 140,
					disabled: true,
					cls: 'drop-down-template-search-checkbox-container',
					margin: '0 10 0 10'
				}
			]
		});

		this.tabs = new Ext6.tab.Panel({
			dockedItems: [
				me.toolbar
			],
			border: false,
			region: 'north',
			tabBar: {
				border: false,
				height: 40,
				cls: 'white-tab-bar',
				style:{
					boxShadow: 'none'
				},
				defaults: {
					cls: 'simple-tab',
					padding: '10 0 6 0',
					margin: '0 10'
				}
			},
			activeItem: 0,
			defaults: {
				border: false
			},
			items: [
				{
					title: 'Последние назначенные',
					itemId: 'last',
					items: [
						me.lastSelectedDrugGrid
					]
				}, {
					title: 'Сохраненные в шаблон',
					itemId: 'temp',
					items: [
						me.DrugTemplateGrid
					]
				}
			],
			listeners: {
				tabchange: function () {
					var tab = this.getActiveTab();
					//win.setMode(tab.itemId)
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
				me.tabs
			],
			dockedItems: [
				me.resizerPanel
			]
		});

		this.callParent(arguments);
	}
});