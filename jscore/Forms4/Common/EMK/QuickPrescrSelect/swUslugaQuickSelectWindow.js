Ext6.define('common.EMK.QuickPrescrSelect.swUslugaQuickSelectWindow', {
	extend: 'base.DropdownPanel',
	width: 745,
	height: 425,
	minHeight: 350,
	focusOnToFront: true,
	userCls: 'template-search packet-fast-exec',
	parentPanel: '',
	params: {},
	onSelect: Ext6.emptyFn,
	showUslugaComplexCode: true,
	enableRefreshScroll: false,
	PrescriptionType_Code: null,
	select: function() {
		var me = this;
		if(!me.isVisible()) return false; // Бывает, после нажатия кнопки комбобокса, запись выбирается, но окно уже закрыто
		var selection = me.UslugaSelectGrid.getSelection();
		if (selection.length > 0) {
			var rec = selection[0];
			var conf = rec.getData();
			conf.PacketPrescr_id = me.PacketPrescr_id ? me.PacketPrescr_id : null;
			conf.callback = me.callback;
			me.onSelect(conf);
			me.hide();
		}
	},
	setFavorite: function(rec,view){
		inDevelopmentAlert();
	},
	load: function(force) {
		var me = this;
		me.UslugaSelectGrid.getStore().removeAll();
		me.UslugaSelectGrid.getStore().load({params: {PrescriptionType_Code: me.PrescriptionType_Code}});
	},

	setParams: function(params) {
		this.params = params;
		//this.load(true);
	},

	refreshQueryFieldTrigger: function(value) {
		var isEmpty = Ext6.isEmpty(value || this.UslComplexFilterCombo.getValue());
		this.UslComplexFilterCombo.triggers.clear.setVisible(!isEmpty);
		this.UslComplexFilterCombo.triggers.search.setVisible(isEmpty);
	},


	show: function(data) {
		var me = this;
		var force = true;
		me.callParent(arguments);

		me.UslComplexFilterCombo.focus();
		me.PrescriptionType_Code = null;
		me.callback = data.callback || null;
		me.PacketPrescr_id = data.PacketPrescr_id || null;
		me.isPP = !!me.PacketPrescr_id;
		if(data && data.objectPrescribe){
			me.objectPrescribe = data.objectPrescribe;

			switch(data.objectPrescribe) {
				case 'EvnCourseProc':
				case 'ProcData':
					me.PrescriptionType_Code = 6;
					me.MedServiceType_SysNick = 'prock';
					break;
				case 'EvnPrescrLabDiag':
				case 'LabDiagData':
					me.PrescriptionType_Code = 11;
					me.MedServiceType_SysNick = 'lab';
					break;
				case 'EvnPrescrFuncDiag':
				case 'FuncDiagData':
					me.PrescriptionType_Code = 12;
					break;
				case 'EvnPrescrConsUsluga':
				case 'ConsUslData':
					me.PrescriptionType_Code = 13;
					break;
				case 'EvnPrescrOperBlock':
				case 'OperBlockData':
					me.PrescriptionType_Code = 7;
					me.MedServiceType_SysNick = 'oper';
					break;
				default:
					Ext6.Msg.alert("Ошибка", "Неизвестный тип назначения: " + me.data.objectPrescribe);
					break;
			}
		}
		me.load(force);
	},
	setViewCode: function(check,eOpts){
		this.UslugaSelectGrid.down('[dataIndex=UslugaComplex_Code]').setVisible(check.checked)
	},
	applywithdate: function (params) {
		if(!this.isVisible()) return false; // Бывает, после нажатия кнопки комбобокса, запись выбирается, но окно уже закрыто
		params.PrescriptionType_Code = this.PrescriptionType_Code;
		params.withDate = true;
		this.onSelect(params);
		this.UslComplexFilterCombo.reset();
		this.hide();
	},
	applywithoutdate: function (params) {
		if(!this.isVisible()) return false; // Бывает, после нажатия кнопки комбобокса, запись выбирается, но окно уже закрыто
		params.PrescriptionType_Code = this.PrescriptionType_Code;
		params.callback = this.callback;
		params.PacketPrescr_id = this.PacketPrescr_id;
		this.onSelect(params);
		this.UslComplexFilterCombo.reset();
		this.hide();
	},
	initComponent: function () {
		var me = this;

		this.UslComplexFilterCombo = Ext6.create('swUslugaComplexSearchCombo', {
			type: 'string',
			filterByValue: true,
			listConfig: {
				//cls: 'choose-bound-list-menu update-scroller',
				minWidth: 575,
				listWidth: 575,
				getInnerTpl: function(){


					return btn.getEl().dom;
				}
			},
			listeners: {
				'render': function (combo) {
					combo.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['gost2011']);
				},
				'beforequery': function(queryPlan, eOpts ){
					this.getStore().proxy.extraParams = me.UslugaSelectGrid.getStore().proxy.extraParams;
					this.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['gost2011']);
					this.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['noprescr']);
					this.getStore().proxy.extraParams.PrescriptionType_Code = me.PrescriptionType_Code;
					this.getStore().proxy.extraParams.to = 'EvnPrescrUslugaInputWindow';
				},
				select: function(combo, record, eOpts ) {
					me.applywithoutdate(record.getData());
					combo.reset();
				}
			},
			hideLabel: true,
			userCls:'template-search-field',
			name: 'query',
			emptyText: 'Код или наименование услуги',
			margin: 0,
			flex: 1,
			tpl: new Ext6.XTemplate(
				'<tpl for="."><div class="x6-boundlist-item" style="padding: 7px 10px 6px 10px;">',
					'<div style="font-size: 13px; display: flex; line-height: 17px; color: #333; position: relative;">',
						'<div style="width: 100%; display: table; table-layout: fixed"><p style="width: 114px !important; color: #333; display: table-cell;white-space: nowrap; text-overflow: ellipsis; overflow: hidden">{UslugaComplex_Code}</p> <p style="display: table-cell color: #333;">{UslugaComplex_Name}</p> <p style="width: 135px !important; color: #333; display: table-cell;white-space: nowrap; text-overflow: ellipsis; overflow: hidden">{UslugaCategory_Name}</p></div>',
				'<div class="search-list-button-container" style="position: absolute; right: 0; top: 0;">',
				'{[this.applywithdate(values.UslugaComplex_id)]}',
						'<p class="boundlist-btn-add" style="margin: 0 0 0 10px" {[this.applywithoutdate(values.UslugaComplex_id)]} ></p>',
					'</div>',
					'</div>',
				'</div></tpl>',
				{
					applywithdate: function(UslugaComplex_id){
						var btn_str = "";
						if(!me.isPP){
							btn_str = '<p class="boundlist-btn-timetable" style="margin: 0 0 0 20px"';
							btn_str += "onclick=\"Ext6.getCmp(\'"+me.getId().trim()+"\').applywithdate({UslugaComplex_id:\'" + UslugaComplex_id +"\'});\"";
							btn_str += '></p>';
						}
						return btn_str;
					},
					applywithoutdate: function(UslugaComplex_id){
						var qtip = ' data-qtip="'+(me.isPP?'Добавить в пакет':'Назначить без указания времени')+'" ';
						return qtip+"onclick=\"Ext6.getCmp(\'"+me.getId().trim()+"\').applywithoutdate({UslugaComplex_id:\'" + UslugaComplex_id +"\'});\"";
					}
				}
			)
		});


		this.favToggler = Ext6.create('Ext6.button.Button', {
			enableToggle: true,
			text: 'Избранные',
			userCls: 'button-without-frame bottom-icon buttonFavor',
			iconCls: 'favTemp-btn-icon',
			value: '1',
			margin: '0 10 0 10',
			disabled: true,
			listeners: {
				change: function(toggler, value) {
					//inDevelopmentAlert();
				}
			}
		});

		this.allUslBtn = Ext6.create('Ext6.button.Button', {
			margin: 0,
			cls: 'sw-tool',
			text: 'Все услуги',
			handler: function() {
				me.hide();

				var conf = {
					objectPrescribe: me.objectPrescribe,
					callback: me.callback
				};

				if (me.PacketPrescr_id !== null) {
					conf.PacketPrescr_id = me.PacketPrescr_id;
				}
				me.parentPanel.getController().openAllUslugaInputWnd(conf);
			}
		});

		this.nameUslugaSet = Ext6.create('Ext6.form.field.Display', {
			padding: '0 0 0 4',
			style: 'user-select: none;',
			value: 'Последние 20'
		});

		this.ViewCodeMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'sw-editor-toolbar-menu',
			defaults: {
				scope: me
			},
			items: [{
				xtype: 'menucheckitem',
				cls: 'drop-down-template-search-checkbox-container checkbox-width-border',
				text: 'Отображать код услуги',
				checked: true,
				handler: 'setViewCode'
			}]
		});

		this.gridToolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			height: 40,
			border: false,
			items: [
				me.nameUslugaSet,
				{
					xtype: 'tbfill',
					style:{
						borderTop: '1px solid #ccc',
						height: '9px'
					}
				},
				{
					xtype: 'button',
					iconCls: 'panicon-config-packet',
					cls: 'button-without-frame',
					menu: me.ViewCodeMenu,
					style:{
						padding: '0px 0px 4px',
						top: '8px'
					}
				}
			]
		});

		this.mainToolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			style: 'background-color: #EEE;',
			userCls: 'packet-fast-exec-toolbar',
			padding: '6 10',
			height: 59,
			border: true,
			docked: 'top',
			items: [
				me.UslComplexFilterCombo,
				me.favToggler,
				me.allUslBtn
			]
		});

		var contactTpl = new Ext6.XTemplate(
			'<div class="contact-cell">',
			'<div class="contact-text-panel">',
			'<p><span class="contact-text" style="font: 13px/17px Roboto; color: #000;">{UslugaComplex_Name} </span><span style="font: 400 11px/17px Roboto, Helvetica, Arial, Geneva, sans-serif; color: #999;">{MedService_Name}</span></p>',
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
			var id = record.get('UslugaComplex_id');
			var tools = [{
				id: id,
				name: 'withoutDate',
				qtip: me.isPP?'Добавить в пакет':'Назначить без указания времени',
				handler: function() {
					me.applywithoutdate(record.getData());
				}
			}];
			if(!me.isPP) tools.push({
				id: id,
				name: 'withDate',
				qtip: 'Назначить с указанием времени',
				handler: function() {
					me.applywithdate(record.getData());
				}
			});
			return tools.map(createTool).join('');
		};
		var captionRenderer = function(value, meta, record) {
			var obj = Ext6.apply(record.data, {
				tools: toolsRenderer.apply(me, arguments)
			});
			return contactTpl.apply(obj);
		};
		var gl_opt = getGlobalOptions();
		this.UslugaSelectGrid = Ext6.create('Ext6.grid.Panel', {
			dockedItems: [
				me.mainToolbar
			],
			tbar: me.gridToolbar,
			cls: 'cureStandartsGrid',
			userCls: 'template-search-grid',
			emptyText: 'Нет результатов.',
			bodyStyle:{
				borderTop: '1px solid #fff'
			},
			hideHeaders: true,
			border: false,
			columns: [
				{
					dataIndex: 'UslugaComplex_IsFavorite',
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
				},
				{
					dataIndex: 'UslugaComplex_Code',
					width: '16%'

					//renderer: captionRenderer
					/*renderer: function(value,el) {
						var resStr = '';
						if(el && el.record){
							resStr += '<div class="rowCureStandart" ><span class="favTemp" >'+value+'</span></div>';
						}
						return resStr;
					}*/
				},
				{
					flex: 1,
					padding: '2 0 7 10',
					dataIndex: 'UslugaComplex_Name',
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
					name: 'MedService_id',
					type: 'int'
				}, {
					name: 'MedService_Name',
					type: 'string'
				}, {
					name: 'UslugaComplex_id',
					type: 'int'
				}, {
					name: 'UslugaComplex_Name',
					type: 'string'
				}, {
					name: 'UslugaComplex_Code',
					type: 'string'
				}, {
					name: 'PrescriptionType_Code',
					type: 'int',
					calculate: function(){
						return me.PrescriptionType_Code;
					}
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
					url: '/?c=MedService&m=loadLastPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					},
					extraParams: {
						top: 20,
						Lpu_id: gl_opt.lpu_id,
						//PrescriptionType_Code: 11,
						MedPersonal_id: gl_opt.medpersonal_id
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



		Ext6.apply(me, {
			panel: [
				me.UslugaSelectGrid
			]
		});

		this.callParent(arguments);
	}
});