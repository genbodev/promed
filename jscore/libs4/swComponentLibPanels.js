Ext6.define("swAllRowExpander", {
	alias: "plugin.allrowexpander",
	extend: "Ext6.grid.plugin.RowExpander",

	isCollapsed: function (rowIdx) {
		var me = this,
			rowNode = me.view.getNode(rowIdx),
			row = Ext6.fly(rowNode, '_rowExpander'),
			ret = true;
		if(row)
			ret = row.hasCls(me.rowCollapsedCls);
		return ret;
	},
	collapse: function (rowIdx) {
		if (this.isCollapsed(rowIdx) == false) {
			var v = this.grid.getView(),
				rowNode = v.getNode(rowIdx);
			if(rowNode)
				this.toggleRow(rowIdx, this.grid.getStore().getAt(rowIdx));
		}
	},
	collapseAll: function () {
		for (i = 0; i < this.grid.getStore().getTotalCount(); i++) {
			this.collapse(i);
		}
	},
	expand: function (rowIdx) {
		if (this.isCollapsed(rowIdx) == true) {
			var v = this.grid.getView(),
				rowNode = v.getNode(rowIdx);
			if(rowNode)
				this.toggleRow(rowIdx, this.grid.getStore().getAt(rowIdx));
		}
	},
	expandAll: function () {
		for (i = 0; i < this.grid.getStore().getTotalCount(); i++) {
			this.expand(i);
		}
	},
	getHeaderConfig: function() {
		var me = this,
			lockable = me.grid.lockable && me.grid;
		if (!me.grid.expanderWidth && me.grid.needIcon)
			me.grid.expanderWidth = 51;
		else
			me.grid.expanderWidth = 16;
		return {
			width: me.grid.expanderWidth,
			ignoreExport: true,
			lockable: false,
			autoLock: true,
			sortable: false,
			resizable: false,
			draggable: false,
			hideable: false,
			menuDisabled: true,
			tdCls: Ext6.baseCSSPrefix + 'grid-cell-special',
			innerCls: Ext6.baseCSSPrefix + 'grid-cell-inner-row-expander',
			renderer: function(i, cell, rec) {
				var icon = '';
				if(me.grid && me.grid.getIconExpander && typeof me.grid.getIconExpander == 'function')
					icon = me.grid.getIconExpander(rec);
				return icon + '<div class="' + Ext6.baseCSSPrefix + 'grid-row-expander" role="presentation" tabIndex="0"></div>';
			},
			processEvent: function(type, view, cell, rowIndex, cellIndex, e, record) {
				var isTouch = e.pointerType === 'touch',
					isExpanderClick = !!e.getTarget('.' + Ext6.baseCSSPrefix + 'grid-row-expander');

				if ((type === "click" && isExpanderClick) || (type === 'keydown' && e.getKey() === e.SPACE)) {

					// Focus the cell on real touch tap.
					// This is because the toggleRow saves and restores focus
					// which may be elsewhere than clicked on causing a scroll jump.
					if (isTouch) {
						cell.focus();
					}
					me.toggleRow(rowIndex, record, e);
					e.stopSelection = !me.selectRowOnExpand;
				} else if (e.type === 'mousedown' && !isTouch && isExpanderClick) {
					e.preventDefault();
				}
			},

			// This column always migrates to the locked side if the locked side is visible.
			// It has to report this correctly so that editors can position things correctly
			isLocked: function() {
				return lockable && (lockable.lockedGrid.isVisible() || this.locked);
			},

			// In an editor, this shows nothing.
			editRenderer: function() {
				return '&#160;';
			}
		};
	}
});

/**
 * Кастомная панелька
 * collapseOnOnlyTitle: true - При нажатии только на title, а не на header целиком панель будет сворачиваться
 * btnAddClickEnable - true - добавляет кнопку "Добавить" в header панельки, с методом описанным в конфиге onBtnAddClick
 * onBtnAddClick - метод вызываемые по кнопке "Добавить" в header-e панельки
 */
Ext6.define("swPanel", {
	border: false,
	alias: "widget.swPanel",
	extend: "Ext6.panel.Panel",
	headerPanel: null,
	titleCounter: null,
	accessType: 'edit', // все кнопки и поля
	editAvailable: true, // точечно, по условию
	/**
	 * true - При нажатии только на title, а не на header целиком панель будет сворачиваться
	 * @type {boolean}
	 */
	collapseOnOnlyTitle: false,
	/**
	 * true - Добавление кнопки "добавить" после title  с методом onBtnAddClick
	 * @type {boolean}
	 */
	btnAddClickEnable: false,
	/**
	 * false - Свиток раскроется, если titleCounter будет больше 0 в методе beforeexpand
	 * @type {boolean}
	 */
	allTimeExpandable: true,
	addSpacer: true,
	onBtnAddClick: Ext6.emptyFn(),
	tip: 'Развернуть',
	listeners: {
		render: function (c) {
			var h = c.getHeader();
			if (h && c.collapseOnOnlyTitle) {
				this.titleTooltip = Ext6.create('Ext6.tip.ToolTip', {
					target: h.getTitle().getId(),
					html: c.tip ? c.tip : "Свернуть"
				});
				if (h.hasCls('x6-panel-header-collapsed')) {
					this.titleTooltip.setHtml('Развернуть')
				}else {
					this.titleTooltip.setHtml('Свернуть');
				}
			}
		},
		beforeexpand: function( p, animate, eOpts ) {
			if (!this.allTimeExpandable) {
				if (!this.titleCounter) {

					return false;
				}
			}
		}
	},
	disableButtons: false,
	setAccessType: function(accessType) {
		var me = this,
			btn = false;
		me.accessType = accessType;
		if(me.btnAddClickEnable){
			btn = me.down('button#'+me.getId()+'-btnAdd');
		}
		var isEdit = (accessType == 'edit');
		if(btn)
			btn.setVisible(isEdit && !me.disableButtons);
		me.tools.forEach(function(tool) {
			if (tool.type != 'expand-bottom' && tool.type != 'collapse-top') {
				tool.setDisabled(!isEdit || me.disableButtons);
			}
		});
	},
	setTitleCounter: function(count) {
		this.titleCounter = count;
		var title = this.getTitle().replace(/<span class='titleCount'>.*?<\/span>/g, "");
		if (count > 0) {
			this.setTitle(title + "<span class='titleCount'>" + count + "</span>");
		}else {
			this.setTitle(title);
		}
		if (this.headerPanel) {
			this.headerPanel.setTitleCounter(count);
		}
		if (!this.allTimeExpandable) {
			if (!this.titleCounter) {
				this.addCls('remove-collapse-str');
				this.collapsed = true;
			}else{
				this.removeCls('remove-collapse-str');
			}
		}
	},
	onRender: function() {
		var me = this,
			headerTitle = false,
			toolSpacerCfg = {
				xtype: 'tbspacer',
				flex: 1
			},
			header = me.getHeader();
		me.callParent(arguments);

		if (me.collapseTool && me.collapsible) {
			if(me.collapseOnOnlyTitle && header){
				header.addCls('header-collapsible-by-title');
				headerTitle = header.getTitle();
				if(headerTitle && headerTitle.flex)
					delete(headerTitle.flex);
				me.getEl().on('click', function () {
					me.toggleCollapse();
				if (header.hasCls('x6-panel-header-collapsed')){
					me.titleTooltip.setHtml('Развернуть');
				} else {
					me.titleTooltip.setHtml('Свернуть');
				}
				},null,{delegate: '#'+headerTitle.getId()});
			}
			// перемещаем collapseTool в начало хеадера
			header.remove(me.collapseTool, false);
			header.insert(0, me.collapseTool);
			// добавим спэйсер для замещения flex title-а, чтобы иконки разместились по левому и правому краям header-а
			if(me.addSpacer){
				me.custSpacer = Ext6.widget(toolSpacerCfg);
				header.insert(2, me.custSpacer);
			}
		}
		if (me.btnAddClickEnable && me.onBtnAddClick && header) {
			headerTitle = header.getTitle();
			if(headerTitle && headerTitle.flex)
				delete(headerTitle.flex);
			var toolCfg = {
				//handler: me.onBtnAddClick,
				scope: me,
				xtype: 'button',
				itemId: me.getId()+'-btnAdd',
				cls: 'button-add-duplicate button-without-frame',
				html: 'Добавить',
				style:{
					'color': '#2196f3'
				},
				listeners:{
					click: function () {
						me.onBtnAddClick();
					}
				}
			};
			me.btnAddClick = Ext6.widget(toolCfg);
			header.insert(2, me.btnAddClick);
			// добавим спэйсер для замещения flex title-а, чтобы иконки разместились по левому и правому краям header-а
			if(!me.custSpacer){
				me.custSpacer = Ext6.widget(toolSpacerCfg);
				header.insert(3, me.custSpacer);
			}
		}

		if (me.threeDotMenu) {
			me.addTool({
				type: 'threedots',
				minWidth: 23,
				callback: function(panel, tool, event) {
					me.threeDotMenu.showBy(tool);
				}
			});
		}

		if (me.plusMenu) {
			me.addTool({
				type: 'plusmenu-extend',
				tooltip: 'Добавить',
				minWidth: 23,
				callback: function(panel, tool, event) {
					me.plusMenu.showBy(tool);
				}
			});
		}
	},
	updateCollapseTool: function() {
		var me = this,
			collapseTool = me.collapseTool,
			toolCfg;
		if (!collapseTool && me.collapsible) {
			me.collapseDirection = me.collapseDirection || me.getHeaderPosition() || 'top';
			toolCfg = {
				xtype: 'tool',
				style: 'margin-right:10px;',
				handler: me.toggleCollapse,
				scope: me,
				cls: 'collapse-tool-arrow'
			};
			// In accordion layout panels are collapsible/expandable with keyboard
			// via the panel title that is focusable. There is no need to have a separate
			// collapse/expand tool for keyboard interaction but we still have to react
			// to mouse clicks, and historically accordion panels had coolapse tools
			// so we leave the tool but make it unfocusable and keyboard inactive.
			// Note that we do the same thing for the automatically added close tool
			// but NOT for the other tools.
			if (me.isAccordionPanel) {
				toolCfg.focusable = false;
				toolCfg.ariaRole = 'presentation';
			}
			me.collapseTool = me.expandTool = collapseTool = Ext6.widget(toolCfg);
		}
		if (collapseTool) {
			if (me.collapsed && !me.isPlaceHolderCollapse()) {
				collapseTool.setType('expand-' + me.getOppositeDirection(me.collapseDirection));
				collapseTool.setTooltip(me.expandToolText);
			} else {
				collapseTool.setType('collapse-' + me.collapseDirection);
				collapseTool.setTooltip(me.collapseToolText);
			}
		}
	}
});

Ext6.define("swSpecificPanel", {
	alias: "widget.swSpecificPanel",
	extend: "swPanel",
	cls: 'accordion-panel-window specific-panel',
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	collapsed: true,
	specificTitle: '',
	initComponent: function() {
		var me = this;

		me.title = '<span class="specific-text">СПЕЦИФИКА:</span> ' + me.specificTitle;
		me.addListener('beforeexpand', function() {
			if (me.handler) {
				me.handler();
			}

			return false;
		});

		me.callParent(arguments);
	}
});

Ext6.define("swGridEvnPrescribe", {
	extend: "Ext6.grid.Panel",
	parentPanel: {},
	prescribeGridType: 'EvnPrescribeView',
	frame: false,
	border: false,
	default: {
		border: 0
	},
	viewConfig: {
		loadMask: false
	},
	params: {},
	alias: 'widget.swGridEvnPrescribe',
	title: 'НАЗНАЧЕНИЯ',
	hideHeaders: true,
	//expanderWidth: 51,
	objectPrescribe: '',
	cls: 'evnPrescribeGrid',
	columnName: {},
	needIcon: true,
	modelName: '',
	url: '/?c=Template&m=getPrescrLabDiag',
	expanderRowBodyTpl: new Ext6.XTemplate(''),
	filterFn: Ext6.emptyFn(),
	onItemClick: Ext6.emptyFn(),
	onPlusClick: Ext6.emptyFn(),
	onDelFn: Ext6.emptyFn(),
	onLoadStoreFn: Ext6.emptyFn(),//yl:
	btnAddClickEnable: true,
	onBtnAddClick: Ext6.emptyFn(),
	openTimeSeriesResults: Ext6.emptyFn(),
	deleteFromDirection: Ext6.emptyFn(),
	/**
	 * Добавляем верстку в экспандер в header строки перед div-ом c иконкой
	 * @param rec - запись в гриде
	 * @returns {string}
	 */
	getIconExpander: function (rec) {
		if (this.needIcon) {
			var objectPrescribe = 'default';
			if (this.objectPrescribe)
				objectPrescribe = this.objectPrescribe;
			return '<div class="icon-expander-' + objectPrescribe + '"></div>';
		} else {
			return '';
		}
	},
	addPrescribeWndName: '',
	threeDotMenuAdd: '',
	panelForMsg: {},
	btnPlusInHeader: true,
	/**
	 * Удаляет назначение и обновляет грид
	 * @param rec удаляемая запись
	 * @param cbFn функция выполняемая после обновления грида после удаления
	 */
	deleteItem: function(rec, cbFn){
		var me = this,
			panel = me.panelForMsg,
			callbackFn = (typeof cbFn == 'function' ? cbFn : Ext6.emptyFn),
			defaultFn = function(){
				me.getStore().reload({
					callback: function (records, operation, success) {
						callbackFn();
						//onDelFn(me, selRec, recIsSelected);
					}
				});
			},
			onDelFn = (typeof me.onDelFn == 'function' ? me.onDelFn : defaultFn ),
			recIsSelected = false,
			selRec = me.getSelectionModel().getSelectedRecord();
		if(selRec && rec == selRec)
			recIsSelected = true;
		if(!me.panelForMsg)
			panel = Ext6.ComponentQuery.query('panel[refId="polkawpr"]')[0];
		var url = '';
		//parentEvnClass_SysNick: EvnVizitPL
		var params = {
			PrescriptionType_id: rec.get('PrescriptionType_id'),
			parentEvnClass_SysNick: me.params.param_del_value
		};
		switch(me.params.param_del_value){
			case 'EvnVizitPL':
				url = '/?c=EvnPrescr&m=cancelEvnPrescr';
				params.EvnPrescr_id = rec.get('EvnPrescr_id');
				break;
			case 'EvnPrescrPolka':
				url = '/?c=EvnPrescr&m=cancelEvnCourse';
				params.EvnCourse_id = rec.get('EvnCourse_id');
				break;
		}
		if(!panel)
			panel = me.up('panel').up('panel').up('panel');

		if (url == '/?c=EvnPrescr&m=cancelEvnPrescr') {
			sw.Promed.EvnPrescr.cancel({
				ownerWindow: me,
				withoutQuestion: true,
				getParams: function(){
					var data = {
						EvnPrescr_id: params.EvnPrescr_id,
						parentEvnClass_SysNick: params.EvnPrescr_id,
						PrescriptionType_id: params.PrescriptionType_id
					};
					// ЛИС как всегда требует к себе особого внимания.....
					if(me.objectPrescribe === 'EvnPrescrLabDiag') {
						data.DirType_id = 10;
						data.EvnPrescr_IsExec = rec.get('EvnPrescr_IsExec') || null;
						data.EvnStatus_id = rec.get('EvnStatus_id') || null;
						data.UslugaComplex_id = rec.get('UslugaComplex_id') || null;
						data.couple = !!(rec.get('couple')==2);
					}
					return data;
				},
				callback: function(){
					onDelFn(me, selRec, recIsSelected, callbackFn);
					sw4.showInfoMsg({
						panel: panel,
						type: 'success',
						text: 'Назначение удалено',
						hideDelay: 2000
					});
				}
			});
		} else {
			me.mask('Удаление...');
			Ext6.Ajax.request({
				url: url,
				params: params,
				callback: function(req, success, response) {
					if (success) {
						var text = 'Назначение удалено',
							type = 'success';
						me.unmask();
						onDelFn(me, selRec, recIsSelected, callbackFn);
						var responseData = Ext6.JSON.decode(response.responseText);
						if (!responseData.success) {
							type = 'error';
							text = responseData.Error_Msg;
						}
						sw4.showInfoMsg({
							panel: panel,
							type: type,
							text: text,
							hideDelay: 2000
						});
					}
					else {
						callbackFn();
						Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
					}
				}
			});
		}
	},
	saveCheckedUslInComposit: function(rec,arrCheckedUsl){
		var grid = this,
			parentPanel = grid.parentPanel,
			data = false,
			uslArrStr = arrCheckedUsl.join(',');
		if(parentPanel)
			data = parentPanel.getController().data;
		if(data && data.Server_id && data.PersonEvn_id){
			grid.mask('Сохранение состава услуги...');
			Ext6.Ajax.request({
				params: {
					Server_id: data.Server_id,
					PersonEvn_id: data.PersonEvn_id,
					EvnPrescrLabDiag_id: rec.get('EvnPrescr_id'),
					UslugaComplex_id: rec.get('UslugaComplex_id'),
					EvnPrescrLabDiag_pid: rec.get('EvnPrescr_pid'),
					EvnUslugaOrder_id: rec.get('EvnUslugaPar_id'),
					EvnUslugaOrder_UslugaChecked: uslArrStr,
					EvnPrescrLabDiag_uslugaList: uslArrStr,
					EvnDirection_id: rec.get('EvnDirection_id'),
					UslugaComplexContent_ids: Ext.util.JSON.encode(arrCheckedUsl),
					EvnPrescrLabDiag_CountComposit: arrCheckedUsl.length,
					UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_pid'),
					isExt6: 1
				},
				callback: function (opt, success, response) {
					grid.unmask();
					var responseData = Ext6.JSON.decode(response.responseText);
					if (!responseData.success) {
						/*sw4.showInfoMsg({
							panel: parentPanel,
							type: 'error',
							text: responseData.Error_Msg,
							hideDelay: 2000
						});*/
					} else {
						rec.set('EvnPrescr_CountComposit', arrCheckedUsl.length);
						rec.commit();
						grid.reconfigure();
					}
				},
				url: '/?c=EvnPrescr&m=saveEvnPrescrLabDiag'
			});
		}
		else{
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: Ext6.emptyFn,
				icon: Ext6.Msg.WARNING,
				msg: langs('Ошибка получения данных'),
				title: ERR_WND_TIT
			});
		}
	},
	showCompositionMenu: function(link, key) {
		var me = this,
			rec = this.getStore().findRecord('EvnPrescr_id', key);
		me.showMenu = true;
		if (!rec) {
			return false;
		}
		if (!rec.compositionMenu) {
			var url = '/?c=MedService&m=loadCompositionMenu';
			if(rec.get('object') == 'EvnPrescrLabDiag' && rec.get('EvnDirection_id'))
				url = '/?c=EvnLabRequest&m=loadCompositionMenu';
			me.mask('Получение состава услуги...');
			Ext6.Ajax.request({
				params: {
					UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id'),
					EvnDirection_id: rec.get('EvnDirection_id'),
					UslugaComplex_pid: rec.get('UslugaComplex_id'),
					Lpu_id: rec.get('Lpu_id'),
					EvnPrescr_id: rec.get('EvnPrescr_id'),
					isExt6: 1
				},
				callback: function(opt, success, response) {
					me.unmask();
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.length > 0) {
							var menuHeight = response_obj.length > 10? 490: undefined;
							rec.compositionMenu = Ext6.create('Ext6.menu.Menu', {
								cls: 'timetable-menu',
								minWidth: 330,
								height: menuHeight,
								items: [],
								buttons: [{
									xtype: 'checkbox',
									boxLabel: 'Снять выделение',
									checked: true,
									handler: function(check, val){
										var boxLabel = val?'Снять выделение':'Выделить всё';
										check.setBoxLabel(boxLabel);
										if(rec && rec.compositionMenu){
											rec.compositionMenu.items.each(function(item){
												if (item.xtype == 'menucheckitem') {
													item.setChecked(val);
												}
											});
										}
									}
								},'->', {
									cls: 'buttonCancel-super-flat-min',
									text: 'Отмена',
									handler: function() {
										rec.compositionMenu.hide();
									}
								}, {
									text: 'Применить',
									cls: 'buttonAccept-super-flat-min',
									handler: function() {
										rec.compositionMenu.hide();
										// надо посчитать кол-во услуг отмеченых в меню и проставить в грид
										var compositionCntAll = 0,
											compositionCntChecked = 0,
											arrCheckedUsl = [];
										rec.compositionMenu.items.each(function(item){
											if (item.xtype == 'menucheckitem') {
												compositionCntAll++;
												if (item.checked) {
													compositionCntChecked++;
													arrCheckedUsl.push(item.UslugaComplex_id);
												}
											}
										});
										me.saveCheckedUslInComposit(rec,arrCheckedUsl);
										// rec.set('EvnPrescr_CountComposit', compositionCntChecked);
										//rec.set('compositionCntChecked', compositionCntChecked);
										//rec.set('compositionCntAll', compositionCntAll);
										// rec.commit();
										// me.reconfigure();
										delete rec.compositionMenu;
									}
								}]
							});
							for (var i = 0; i < response_obj.length; i++) {
								var checked = (
									(response_obj[i].checkedUsl && response_obj[i].checkedUsl == true) // Если через '/?c=MedService&m=loadCompositionMenu';
									|| (response_obj[i].UslugaComplex_InRequest && response_obj[i].UslugaComplex_InRequest > 0) // через  '/?c=EvnLabRequest&m=loadCompositionMenu';
								);
								rec.compositionMenu.add({
									text: response_obj[i].UslugaComplex_Name,
									UslugaComplex_id: response_obj[i].UslugaComplex_id,
									hideLabel: true,
									xtype: 'menucheckitem',
									rec: rec,
									checked: checked
								});
							}

							rec.compositionMenu.add({
								xtype: 'menuseparator'
							});

							rec.compositionMenu.showBy(link);
						}
					}
				},
				url: url
			});
		} else {
			rec.compositionMenu.showBy(link);
		}
	},
	//dirTypeCodeExcList: ['10','11','14','16','17'],
	dirTypeCodeExcList: [],
	onRender: function() {
		var me = this,
			toolSpacerCfg = {
				xtype: 'tbspacer',
				flex: 1
			},
			header = me.getHeader();
		header.setHeight(24);
		me.callParent(arguments);
		if (me.btnAddClickEnable && header) {
			var headerTitle = header.getTitle();
			if (headerTitle && headerTitle.flex)
				delete(headerTitle.flex);
			var toolCfg = {
				//handler: me.onBtnAddClick,
				scope: me,
				xtype: 'button',
				cls: 'button-add-duplicate button-without-frame',
				html: 'Добавить',
				style:{
					'color': '#2196f3'
				},
				listeners:{
					click: function (btn) {
						me.onPlusClick(me, false, 'add', btn);
					}
				}
			};
			me.btnAddClick = Ext6.widget(toolCfg);
			header.insert(1, me.btnAddClick);
			// добавим спэйсер для замещения flex title-а, чтобы иконки разместились по левому и правому краям header-а
			me.custSpacer = Ext6.widget(toolSpacerCfg);
			header.insert(2, me.custSpacer);
		}
	},
	initComponent: function() {
		var me = this;
		me.threeDotMenu =  Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			parentGrid: me,
			items: [
				/*{
					text: 'Параметры назначения',
					handler: function () {
						inDevelopmentAlert();
						log('Параметры');
						log(arguments);
					}
				},*/ {
					text: 'Удалить назначение',
					name: 'delPrescribe',
					handler: function (menuItem) {
						var grid = menuItem.ownerCt.parentGrid,
							rec = menuItem.ownerCt.selRecord;
						if (grid && rec)
							grid.deleteItem(rec);
					}
				}, {
					text: 'Отменить направление',
					name: 'cancelDirection',
					handler: function (menuItem) {
						var grid = menuItem.ownerCt.parentGrid,
							rec = menuItem.ownerCt.selRecord;
						if (grid && rec && rec.get('EvnDirection_id')) {
							me.onCancelDirClick(rec, grid);
						}
					}
				}
			]
		});
		if(me.threeDotMenuAdd)
			me.threeDotMenu.add(me.threeDotMenuAdd);
		if(me.btnPlusInHeader){
			me.tools = [{
				tooltip: 'Добавить назначение',
				type: 'plus',
				pressed: true,
				callback: function(panel, tool, event) {
					me.onPlusClick(me, false, 'add', tool);
					// А ведь когда-то мы могли, сидеть с гитарами всю ночь (с)
					//me.parentPanel.getController().addEvnPrescr(me.addPrescribeWndName);
				}
			}];
		}
		var columns = [
			me.columnName,
		];
		if(me.objectPrescribe !== 'EvnDirection'){
			columns.push({
					xtype: 'actioncolumn',
					width: 18,
					dataIndex: 'EvnPrescr_IsCito',
					sortable: false,
					menuDisabled: true,
					items: ['@isCito']
				},
				{
					xtype: 'actioncolumn',
					width: 18,
					sortable: false,
					menuDisabled: true,
					items: ['@isDirection']
				},
				{
					xtype: 'actioncolumn',
					width: 18,
					sortable: false,
					menuDisabled: true,
					items: ['@isOtherMO']
				},
				{
					xtype: 'actioncolumn',
					width: 18,
					sortable: false,
					menuDisabled: true,
					items: ['@isSelectDT']
				},
				{
					xtype: 'actioncolumn',
					width: 18,
					sortable: false,
					menuDisabled: true,
					items: ['@isResults']
				});
		} else {
			columns.push({
				width: 60,
				dataIndex: 'EvnDirection_Sign',
				tdCls: 'vertical-middle',
				xtype: 'widgetcolumn',
				widget: {
					xtype: 'swEMDPanel',
					bind: {
						EMDRegistry_ObjectName: '{record.EMDRegistry_ObjectName}',
						EMDRegistry_ObjectID: '{record.EMDRegistry_ObjectID}',
						SignCount: '{record.EvnDirection_SignCount}',
						MinSignCount: '{record.EvnDirection_MinSignCount}',
						IsSigned: '{record.IsSigned}',
						Hidden: '{record.SignHidden}'
					}
				}
			});
		}
		columns.push({
			xtype: 'actioncolumn',
			width: 30,
			sortable: false,
			menuDisabled: true,
			tooltip: 'Меню',
			items: ['@menuItem']
		});

		if(typeof me.onCancelDirClick === 'object' && me.onCancelDirClick.fn && me.onCancelDirClick.scope){
			me.onCancelDirClick = me.onCancelDirClick.scope[me.onCancelDirClick.fn];
		}

		Ext6.apply(this, {
			onDelFn: me.onDelFn,
			expanderWidth: me.expanderWidth,
			dirTypeCodeExcList: me.dirTypeCodeExcList?me.dirTypeCodeExcList:false,
			getIconExpander: me.getIconExpander,
			openTimeSeriesResults: me.openTimeSeriesResults,
			deleteFromDirection: me.deleteFromDirection,
			store: {
				model: me.modelName,
				folderSort: true,
				proxy: {
					//extraParams: {
					//	user_MedStaffFact_id: 99560020593,
					//	object: 'EvnPrescrLabDiag',
					//	object_id: 'EvnPrescrLabDiag_id',
					//	object_value: 11,
					//	archiveRecord: 0,
					//	is_reload_one_section: 1,
					//	parent_object_id: 'EvnPrescr_pid',
					//	parent_object_value: 1357394,
					//	param_name: 'section',
					//	param_value: 'EvnPrescrPolka',
					//	from_MZ: 1
					//},
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: me.url,
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null,
				filters: me.filterFn,
				listeners: {
					load: function(){
						//yl:176439 добавил вызов для обновления Исследований нижней панели
						if(me.onLoadStoreFn && typeof me.onLoadStoreFn == "function"){
							me.onLoadStoreFn();
						}
						//if(this.getCount() > 0)
							//me.parentPanel.getController().setTitleCounterGrids(me.parentPanel);
					}
				}
			},
			columns: columns,
			plugins: [{
				ptype: 'allrowexpander',
				pluginId: 'allrowexpander',
				rowBodyTpl : me.expanderRowBodyTpl
			}],
			threeDotMenu: me.threeDotMenu,
			listeners: {
				itemclick: function( grid, rec, item, index, e, eOpts){
					if (!this.showMenu && !e.onBtnClick && !Ext6.isElement(e.getTarget('.x6-grid-row-expander'))) {
						if (me.onItemClick) {
							me.onItemClick(me, rec);
						}
						me.parentPanel.clearAllSelection(me, rec);
					}
					delete this.showMenu;
				}
			}
		});

		me.callParent(arguments);
	},
	actions: {
		isCito: {
			getClass: 'getCitoClass',
			userCls: 'button-without-frame',
			getTip: 'getCitoTip',
			handler: 'addCitoInPrescr'
		},
		isDirection: {
			getClass: 'getDirectionClass',
			userCls: 'button-without-frame',
			getTip: 'getDirectionTip',
			handler: 'onDirectionClick'
		},
		isOtherMO: {
			getClass: 'getOtherMOClass',
			userCls: 'button-without-frame',
			getTip: 'getOtherMOTip',
			handler: 'onOtherMOClick'
		},
		isSelectDT: {
			getClass: 'getSelectDTClass',
			userCls: 'button-without-frame',
			getTip: 'getSelectDTTip',
			handler: 'openSpecificationByItem'
		},
		isResults: {
			getClass: 'getResultsClass',
			userCls: 'button-without-frame',
			getTip: 'getResultsTip',
			handler: 'onResultClick'
		},
		menuItem: {
			userCls: 'button-without-frame',
			iconCls: 'grid-header-icon-menuItem',
			tooltip: 'Меню',
			tdCls: 'action-col-menuItem',
			handler: 'onMenuClick'
		}
	}
});


Ext6.define("swGridEvnPrescribeLabDiag", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnPrescribeLabDiag',
	alias: 'widget.swGridEvnPrescribeLabDiag',
	frame: false,
	border: false,
	default: {
		border: 0
	},
	columnName: {
		dataIndex: 'UslugaComplex_Name',
		flex: 1,
		align: 'left',
		renderer: function(value,cell) {
			var resStr = '',// Для назначения idшника всей строки в целом EXTJSом
				me = this;
			if(cell && cell.record){
				var rec = cell.record;
				var text = '';
				if(rec.get('isComposite') && rec.get('isComposite')>0){
					var countComposite = 'состав';
					var maxCountComposite = null;
					if(rec.get('EvnPrescr_CountComposit') && rec.get('EvnPrescr_CountComposit')>0)
						countComposite = rec.get('EvnPrescr_CountComposit');
					if(rec.get('EvnPrescr_MaxCountComposit') && rec.get('EvnPrescr_MaxCountComposit')>0)
						maxCountComposite = rec.get('EvnPrescr_MaxCountComposit');
					/*text = '<div class="many-prescr-sostav-edit"><a href="#" ' +
						'onclick="Ext6.getCmp(\'' + me.id + '\').showCompositionMenu(this, ' +
						"'" + rec.get('EvnPrescr_id') + "'" +
						')">'+countComposite+'</a></div>';*/
					text = '<div class="many-prescr-sostav-edit" onclick="Ext6.getCmp(\'' + me.id + '\').showCompositionMenu(this, ' +
						"'" + rec.get('EvnPrescr_id') + "'" +
						')">'+countComposite+(maxCountComposite && countComposite!='состав' ? '/'+maxCountComposite : '')+'</div>';
				}

				var manyDrug = (rec.get('couple') > 1);
				if(manyDrug)
					resStr += '<div class="onePrescr overRelatedPrescr'+rec.get('EvnDirection_id')+'" ><span class="manyEvnPrescr" >'+value+'</span></div>';
				else
					resStr += '<div class="onePrescr" >' + value + '</div>';

				resStr += text;
			}
			return resStr;
		}
	},
	threeDotMenuAdd: [
		{
			text: 'Динамика результатов тестов',
			name: 'TimeSeries',
			handler: function () {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.openTimeSeriesResults)
					this.ownerCt.parentGrid.openTimeSeriesResults(this.ownerCt.selRecord);
			}
		}, {
			text: 'Исключить из направления',
			name: 'delFromDirect',
			handler: function () {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.deleteFromDirection)
					this.ownerCt.parentGrid.deleteFromDirection(this.ownerCt.selRecord);
			}
		}
	],
	params: {
		object_value: 5,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL'
	},
	objectPrescribe: 'EvnPrescrLabDiag',
	hideHeaders: true,
	cls: 'evnPrescribeGrid',
	modelName: 'common.EMK.models.EvnPrescrLabDiag',
	filterFn: Ext6.emptyFn(),
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.EvnDirection_Num && values.EvnDirection_id)
						row+= '<p><b>Направление №'+values.EvnDirection_Num+'</b></p>';
					if(values.RecTo && values.EvnDirection_id)
						row+= '<p><b>Место проведения: </b>'+values.RecTo+'</p>';
					if(values.RecDate && values.EvnDirection_id)
						row+= '<p><b>Записан: </b>'+values.RecDate+'</p>';
					if(values.UslugaComplex_Code)
						row+= '<p><b>Код услуги: </b>'+values.UslugaComplex_Code+'</p>';
				}
				return row;
			}
		}),
	addPrescribeWndName: 'EvnPrescrUslugaInputPanel',
	overItem: function(EvnDirection_id,over){
		var me = this,
			store = this.getStore(),
			arrPrescr = me.getEl().query('.overRelatedPrescr'+EvnDirection_id);
		arrPrescr.forEach(function(el){
			if(over)
				el.classList.add('overRelatedPrescr');
			else
				el.classList.remove('overRelatedPrescr');
		});
		// Если все-таки придется изменять стор
		/*store.each(function(el){
			if(el.get('EvnDirection_id') == EvnDirection_id)
				me.getView().getRow(el).classList.add('overRelatedPrescr');
			else
				me.getView().getRow(el).classList.remove('overRelatedPrescr');
		});*/
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
		//!!!Запомни или запиши!!!
		//ename, fn, scope, options, order, caller, manager
		me.addListener('itemmouseenter', function(el, rec, item, index, e, eOpts){
			if (rec && rec.get('EvnDirection_id')) {
				me.overItem(rec.get('EvnDirection_id'), true)
			}
		});
		me.addListener('itemmouseleave', function(el, rec, item, index, e, eOpts){
			if (rec && rec.get('EvnDirection_id')) {
				me.overItem(rec.get('EvnDirection_id'), false)
			}
		});
	}
});

Ext6.define("swGridEvnPrescribeFuncDiag", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnPrescribeFuncDiag',
	alias: 'widget.swGridEvnPrescribeFuncDiag',
	frame: false,
	border: false,
	default: {
		border: 0
	},
	columnName: {
		dataIndex: 'UslugaComplex_Name',
		flex: 1,
		align: 'left',
		renderer: function(value,el) {
			var resStr = ''; // Для назначения idшника всей строки в целом EXTJSом
			if(el && el.record){
				var manyDrug = (el.record.get('couple') > 1);
				if(manyDrug)
					resStr += '<div class="onePrescr overRelatedPrescr'+el.record.get('EvnDirection_id')+'" ><span class="manyEvnPrescr" >'+value+'</span></div>';
				else
					resStr += '<div class="onePrescr" >' + value + '</div>';

			}
			return resStr;
		}
	},
	params: {
		object_value: 12,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL'
	},
	objectPrescribe: 'EvnPrescrFuncDiag',
	hideHeaders: true,
	cls: 'evnPrescribeGrid',
	modelName: 'common.EMK.models.EvnPrescrFuncDiag',
	filterFn: Ext6.emptyFn(),
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.EvnDirection_Num)
						row+= '<p><b>Направление №'+values.EvnDirection_Num+'</b></p>';
					if(values.RecTo)
						row+= '<p><b>Место проведения: </b>'+values.RecTo+'</p>';
					if(values.RecDate)
						row+= '<p><b>Записан: </b>'+values.RecDate+'</p>';
					if(values.UslugaComplex_Code)
						row+= '<p><b>Код услуги: </b>'+values.UslugaComplex_Code+'</p>';
				}
				return row;
			}
		}),
	addPrescribeWndName: 'EvnPrescrUslugaInputPanel',
	overItem: function(EvnDirection_id,over){
		var me = this,
			store = this.getStore(),
			arrPrescr = me.getEl().query('.overRelatedPrescr'+EvnDirection_id);
		arrPrescr.forEach(function(el){
			if(over)
				el.classList.add('overRelatedPrescr');
			else
				el.classList.remove('overRelatedPrescr');
		});
		// Если все-таки придется изменять стор
		/*store.each(function(el){
			if(el.get('EvnDirection_id') == EvnDirection_id)
				me.getView().getRow(el).classList.add('overRelatedPrescr');
			else
				me.getView().getRow(el).classList.remove('overRelatedPrescr');
		});*/
	},
	initComponent: function() {
		var me = this;

		me.callParent(arguments);

		//!!!Запомни или запиши!!!
		//ename, fn, scope, options, order, caller, manager
		me.addListener('itemmouseenter', function(el, rec, item, index, e, eOpts){
			if (rec && rec.get('EvnDirection_id')) {
				me.overItem(rec.get('EvnDirection_id'), true)
			}
		});
		me.addListener('itemmouseleave', function(el, rec, item, index, e, eOpts){
			if (rec && rec.get('EvnDirection_id')) {
				me.overItem(rec.get('EvnDirection_id'), false)
			}
		});

	}
});

Ext6.define("swGridEvnCourseTreat", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnCourseTreat',
	params: {
		object_value: 11,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnPrescrPolka'
	},
	alias: 'widget.swGridEvnCourseTreat',
	title: 'ЛЕКАРСТВЕННЫЕ НАЗНАЧЕНИЯ',
	objectPrescribe: 'EvnCourseTreat',
	needIcon: true,
	filterFn: Ext6.emptyFn(),
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.PrescriptionIntroType_Name)
						row+= '<p><b>Метод введения: </b>'+values.PrescriptionIntroType_Name+'</p>';
					if(values.EvnCourse_begDate)
						row+= '<p><b>Период: c </b>'+values.EvnCourse_begDate+'</p>';
					if(values.Duration && values.DurationType_Nick)
						row+= '<p><b>Продолжительность: </b>'+values.Duration+' '+values.DurationType_Nick+'</p>';
					if(values.PerformanceType_Name)
						row+= '<p><b>Исполнение: </b>'+values.PerformanceType_Name+'</p>';
					if(values.EvnPrescr_Descr)
						row+= '<p><b>Комментарий: </b>'+values.EvnPrescr_Descr+'</p>';
				}
				return row;
				/*var t = {
					Drug_Name: "Курс 1",
					Duration: 1,
					DurationType_Nick: "дн",
					EvnCourse_Title: "Курс 1",
					EvnCourse_begDate: "27.05.2018",
					EvnCourse_id: 730023881097730,
					EvnPrescrPolka_id: "730023881097712-730023881097730",
					EvnPrescr_Count: 1
					EvnPrescr_Descr: "й904й72897 й98ун фышгяа шыгап фшгцай9 8кн й3к"
					EvnPrescr_IsCito: 2,
					EvnPrescr_IsExec: "1",
					EvnPrescr_IsHasEvn: 1,
					EvnPrescr_id: "730023881097731",
					EvnPrescr_pid: "730023881097712",
					EvnPrescr_rid: "730023881097711",
					EvnPrescr_setDate: "27.05.2018",
					MaxCountInDay: 1,
					MinCountInDay: 1,
					PerformanceType_Name: "самостоятельно",
					PrescriptionIntroType_Name: "пероральное введение",
					PrescriptionStatusType_id: "1",
					PrescriptionType_Code: "5",
					PrescriptionType_id: "5",
					expanded: true,
					id: "common.EMK.models.EvnCourseTreat-1"
					isEvnCourse: 1,
					leaf: false,
					object: "EvnCourseTreat"
				};*/
			}
		}),
	columnName: {
		dataIndex: 'DrugListData',
		flex: 1,
		align: 'left',
		renderer: function(value) {
			var resStr = ''; // Для назначения idшника всей строки в целом EXTJSом
			if(value){
				var manyDrug = (Object.keys(value).length > 1);
				for(var key in value) {
					if(manyDrug)
						resStr += '<div class="onePrescr" ><span class="manyEvnPrescr" data-qtip="Объединенные медикаменты">'+value[key].Drug_Name+'</span></div>';
					else
						resStr += '<div class="onePrescr" >' + value[key].Drug_Name + '</div>';
				}
			}
			return resStr;
		}
	},
	/*threeDotMenuAdd: [
		{
			text: 'Включить в рецепт',
			handler: function () {
				inDevelopmentAlert();
				log('Параметры');
				log(arguments);
			}
		}
	],*/
	getIconExpander: function(rec){
		if (this.needIcon) {
			var iconHtml = '<div class="one-drugs-expander"></div>';
			if (rec) {
				var d = rec.get('DrugListData');
				if (Object.keys(d).length > 1)
					iconHtml = '<div class="many-drugs-expander"></div>';
			}
			return iconHtml;
		} else { return '' }
	},
	modelName: 'common.EMK.models.EvnCourseTreat',
	addPrescribeWndName: 'EvnCourseTreatEditPanel',
	initComponent: function(){
		var me = this;
		me.callParent(arguments);
	}

});
Ext6.define("swGridEvnConsUsluga", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnConsUsluga',
	params: {
		object_value: 13,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL'
	},
	alias: 'widget.swGridEvnConsUsluga',
	objectPrescribe: 'EvnPrescrConsUsluga',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.EvnDirection_Num)
						row+= '<p><b>Направление №'+values.EvnDirection_Num+'</b></p>';
					if(values.RecTo)
						row+= '<p><b>Место проведения: </b>'+values.RecTo+'</p>';
					if(values.RecDate)
						row+= '<p><b>Записан: </b>'+values.RecDate+'</p>';
					if(values.UslugaComplex_Code)
						row+= '<p><b>Код услуги: </b>'+values.UslugaComplex_Code+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		dataIndex: 'UslugaComplex_Name',
		flex: 1,
		sortable: true,
		align: 'left'
	},
	filterFn: Ext6.emptyFn(),
	modelName: 'common.EMK.models.EvnConsUsluga',
	initComponent: function(){
		var me = this;


		me.callParent(arguments);


	}
});

//Вакцинация
Ext6.define("swGridEvnVaccination", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnVaccination',
	params: {
		object_value: 14,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL'
	},
	alias: 'widget.swGridEvnVaccination',
	objectPrescribe: 'EvnPrescrVaccination',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.TN_NAME)
						row+= '<p><b>Вакцина: '+values.TN_NAME+'</b></p>';
					if(values.VaccinationType_Name)
						row+= '<p><b>Вакцинация: '+values.VaccinationType_Name+'</b></p>';
					if(values.EvnPrescr_setDate)
						row+= '<p><b>Дата назначения: '+values.EvnPrescr_setDate+'</b></p>';
					if(values.EvnPrescrVaccination_didDT)
						row+= '<p><b>Исполнено: '+values.EvnPrescrVaccination_didDT+'</b></p>';
				}
				return row;
			}
		}),
	columnName: {
		dataIndex: 'VaccinationType_Name',
		flex: 1,
		sortable: true,
		align: 'left',
		renderer: function(val, metaData, record) {
			var s = '';
			s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('VaccinationType_Name')+'</span></div></div>';
			return s;
		}
	},
	modelName: 'common.EMK.models.EvnVaccination',
	initComponent: function(){
		var me = this;
		me.callParent(arguments);
	}
})


Ext6.define("swGridEvnCourseProc", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnCourseProc',
	params: {
		object_value: 6,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL'
	},
	alias: 'widget.swGridEvnCourseProc',
	objectPrescribe: 'EvnCourseProc',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.EvnDirection_Num)
						row+= '<p><b>Направление №'+values.EvnDirection_Num+'</b></p>';
					if(values.RecTo)
						row+= '<p><b>Место проведения: </b>'+values.RecTo+'</p>';
					if(values.RecDate)
						row+= '<p><b>Записан: </b>'+values.RecDate+'</p>';
					if(values.UslugaComplex_Code)
						row+= '<p><b>Код услуги: </b>'+values.UslugaComplex_Code+'</p>';
					if(values.UslugaComplex_Code)
						row+= '<p><b>Комментарий: </b>'+values.EvnPrescr_Descr+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		dataIndex: 'UslugaComplex_Name',
		flex: 1,
		sortable: true,
		align: 'left'
	},
	filterFn: [
		// Процедура без idшника
		function(item) {
			var flag = false;
			if(item.get('UslugaComplex_id'))
				flag = true;
			return flag;
		}
	],
	modelName: 'common.EMK.models.EvnCourseProc',
	initComponent: function(){
		var me = this;


		me.callParent(arguments);


	}
});
Ext6.define("swGridEvnPrescrOperBlock", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnPrescrOperBlock',
	params: {
		object_value: 7,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL'
	},
	alias: 'widget.swGridEvnPrescrOperBlock',
	objectPrescribe: 'EvnPrescrOperBlock',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.EvnDirection_Num)
						row+= '<p><b>Направление №'+values.EvnDirection_Num+'</b></p>';
					if(values.RecTo)
						row+= '<p><b>Место проведения: </b>'+values.RecTo+'</p>';
					if(values.RecDate)
						row+= '<p><b>Записан: </b>'+values.RecDate+'</p>';
					if(values.UslugaComplex_Code)
						row+= '<p><b>Код услуги: </b>'+values.UslugaComplex_Code+'</p>';
					if(values.UslugaComplex_Code)
						row+= '<p><b>Комментарий: </b>'+values.EvnPrescr_Descr+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		dataIndex: 'UslugaComplex_Name',
		flex: 1,
		sortable: true,
		align: 'left'
	},
	filterFn: [
		// Процедура без idшника
		function(item) {
			var flag = false;
			if(item.get('UslugaComplex_id'))
				flag = true;
			return flag;
		}
	],
	modelName: 'common.EMK.models.EvnPrescrOperBlock',
	initComponent: function(){
		var me = this;


		me.callParent(arguments);


	}
});
Ext6.define("swGridEvnPrescrDiet", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnPrescrDiet',
	params: {
		object_value: 2,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL'
	},
	alias: 'widget.swGridEvnPrescrDiet',
	objectPrescribe: 'EvnPrescrDiet',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.EvnPrescr_setDate)
						row+= '<p><b>Период: </b>'+values.EvnPrescr_setDate+'</p>';
					if(values.EvnPrescr_Descr)
						row+= '<p><b>Комментарий: </b>'+values.EvnPrescr_Descr+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		dataIndex: 'PrescriptionDietType_Name',
		flex: 1,
		sortable: true,
		align: 'left'
	},
	filterFn: Ext6.emptyFn(),
	modelName: 'common.EMK.models.EvnPrescrDiet',
	initComponent: function(){
		var me = this;
		me.callParent(arguments);
	}
});
Ext6.define("swGridEvnPrescrRegime", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnPrescrRegime',
	params: {
		object_value: 1,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL'
	},
	alias: 'widget.swGridEvnPrescrRegime',
	objectPrescribe: 'EvnPrescrRegime',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.EvnPrescr_setDate)
						row+= '<p><b>Период: </b>'+values.EvnPrescr_setDate+'</p>';
					if(values.EvnPrescr_Descr)
						row+= '<p><b>Комментарий: </b>'+values.EvnPrescr_Descr+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		dataIndex: 'PrescriptionRegimeType_Name',
		flex: 1,
		sortable: true,
		align: 'left'
	},
	filterFn: Ext6.emptyFn(),
	modelName: 'common.EMK.models.EvnPrescrRegime',
	addPrescribeWndName: 'EvnPrescrRegimePanel',
	initComponent: function(){
		var me = this;
		me.callParent(arguments);
	}
});


Ext6.define("swGridEvnDirection", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnDirection',
	params: {
		object_value: 1,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL',
		DopDispInfoConsent_id: null,
		DirType: 'swGridEvnDirection'
	},
	dirTypeCodeExcList: ['3', '12', '13'],
	url: '/?c=EvnDirection&m=loadEvnDirectionPanel',
	alias: 'widget.swGridEvnDirection',
	objectPrescribe: 'EvnDirection',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.LpuSection_Name)
						row+= '<p><b>Отделение: </b>'+values.LpuSection_Name+'</p>';
					if(values.Org_Name || values.Lpu_Name)
						row+= '<p><b>МО: </b>'+ (Ext6.isEmpty(values.Org_Nick) ? values.Lpu_Nick : values.Org_Name) +'</p>';
					if(values.EvnDirection_setDate)
						row+= '<p><b>Дата: </b>'+values.EvnDirection_setDate+'</p>';
					if(values.EvnPrescr_Descr)
						row+= '<p><b>Время: </b>'+values.EvnPrescr_Descr+'</p>';
					if(values.EvnStatus_epvkName)
						row+= '<p>'+values.EvnStatus_epvkName+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		flex: 1,
		minWidth: 100,
		tdCls: 'padLeft20',
		dataIndex: 'EvnDirection_Data',
		renderer: function (value, metaData, record) {
			var text = '<span class="dirInfoIcon"></span>' + record.get('DirType_Name') + " № " + record.get('EvnDirection_Num');
			var text = '<span class="dirInfoIcon"></span>' + record.get('DirType_Name') + ": " + record.get('LpuSection_Name') + " / " + (Ext6.isEmpty(record.get('Org_Nick')) ? record.get('Lpu_Nick') : record.get('Org_Name')) + " / " + record.get('EvnDirection_setDate') + " / Направление № " + record.get('EvnDirection_Num');

			if (record.get('EvnStatus_id') && record.get('EvnStatus_id').inlist([12, 13])) {
				text = text + ' / <span style="color: red;">' + record.get('EvnStatus_Name') + ' ' + record.get('EvnDirection_statusDate') + '</span>';

				if (record.get('EvnStatusCause_Name')) {
					text = text + ', по причине: <span style="color: red;">' + record.get('EvnStatusCause_Name') + '</span>';
				}
			}

			return text;
		}
	},
	filterFn: Ext6.emptyFn(),
	modelName: 'common.EMK.models.EvnDirection',
	addPrescribeWndName: 'swEvnDirectionEditWindowExt6',
	threeDotMenuAdd: [
		{
			text: 'Просмотр',
			name: 'ViewDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
			text: 'Редактировать',
			name: 'EditDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
			text: 'Печать',
			name: 'PrintDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
            text: 'Печать Документа',
            name: 'PrintDirectionDocument',
            handler: function() {
                Ext6.Ajax.request({
                    url: '/?c=EvnDirection&m=getLinkedXmlForEvnDirection',
                    params: {
                        EvnDirection_id: this.ownerCt.selRecord.get('EvnDirection_id')
                    },
                    callback: function (options, success, response) {
                        if (success) {
							if (response && response.responseText) {
								var result  = Ext6.util.JSON.decode(response.responseText);
								Ext.each(result , function(el) {
									window.open('/?c=EvnXml&m=doPrint&EvnXml_id=' + el.EvnXml_id, '_blank');
								});
							}
                        }
                    }
                });
            }
        }
	],
	initComponent: function(){
		var me = this;
		if (getRegionNick() == 'buryatiya') {
			me.dirTypeCodeExcList.push('18');
		}
		me.callParent(arguments);
	}
});
Ext6.define("swGridEvnDirectionCommon", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnDirectionCommon',
	params: {
		object_value: 1,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL',
		DopDispInfoConsent_id: null,
		DirType: 'swGridEvnDirectionCommon'
	},
	dirTypeCodeExcList: ['2', '8', '9', '15', '25', '26','27', '28'],
	url: '/?c=EvnDirection&m=loadEvnDirectionPanel',
	alias: 'widget.swGridEvnDirectionCommon',
	objectPrescribe: 'EvnDirection',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.LpuSection_Name)
						row+= '<p><b>Отделение: </b>'+values.LpuSection_Name+'</p>';
					if(values.Org_Name || values.Lpu_Name)
						row+= '<p><b>МО: </b>'+ (Ext6.isEmpty(values.Org_Name) ? values.Lpu_Name : values.Org_Name) +'</p>';
					if(values.EvnDirection_setDate && values.DirType_Code != 30)
						row+= '<p><b>Дата: </b>'+values.EvnDirection_setDate+'</p>';
					if(values.EvnPrescr_Descr)
						row+= '<p><b>Время: </b>'+values.EvnPrescr_Descr+'</p>';
					if(values.EvnDirectionCVI_Lab)
						row+= '<p><b>Лаборатория направления: </b>'+values.EvnDirectionCVI_Lab+'</p>';
					if(values.EvnDirectionCVI_takeDate)
						row+= '<p><b>Дата взятия образцов: </b>'+values.EvnDirectionCVI_takeDate+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		flex: 1,
		minWidth: 100,
		tdCls: 'padLeft20',
		dataIndex: 'EvnDirection_Data',
		renderer: function (value, metaData, record) {
			var text = '<span class="dirInfoIcon"></span>' + record.get('DirType_Name') + " № " + record.get('EvnDirection_Num');

			if (record.get('DirType_Code') && record.get('DirType_Code').inlist([30])) {
				text = '<span class="dirInfoIcon"></span>' + record.get('DirType_Name') + " от " + record.get('EvnDirectionCVI_takeDate');
			}

			if (record.get('EvnStatus_id') && record.get('EvnStatus_id').inlist([12, 13])) {
				text = text + ' / <span style="color: red;">' + record.get('EvnStatus_Name') + ' ' + record.get('EvnDirection_statusDate') + '</span>';

				if (record.get('EvnStatusCause_Name')) {
					text = text + ', по причине: <span style="color: red;">' + record.get('EvnStatusCause_Name') + '</span>';
				}
			}

			return text;
		}
	},
	filterFn: Ext6.emptyFn(),
	modelName: 'common.EMK.models.EvnDirection',
	addPrescribeWndName: 'swEvnDirectionEditWindowExt6',
	threeDotMenuAdd: [
		{
			text: 'Направить зав. отделением',
			name: 'directZav',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord, this.ownerCt.parentGrid);
			}
		}, {
			text: 'Просмотр',
			name: 'ViewDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
			text: 'Редактировать',
			name: 'EditDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
			text: 'Печать',
			name: 'PrintDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}
	],
	initComponent: function(){
		var me = this;
		me.callParent(arguments);
	}
});
Ext6.define("swGridEvnDirectionHosp", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnDirectionHosp',
	params: {
		object_value: 1,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL',
		DopDispInfoConsent_id: null,
		DirType: 'swGridEvnDirectionHosp'
	},
	dirTypeCodeExcList: ['1', '4', '5', '6'],
	url: '/?c=EvnDirection&m=loadEvnDirectionPanel',
	alias: 'widget.swGridEvnDirectionHosp',
	objectPrescribe: 'EvnDirection',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.LpuSection_Name)
						row+= '<p><b>Отделение: </b>'+values.LpuSection_Name+'</p>';
					if(values.Org_Name || values.Lpu_Name)
						row+= '<p><b>МО: </b>'+ (Ext6.isEmpty(values.Org_Name) ? values.Lpu_Name : values.Org_Name) +'</p>';
					if(values.EvnDirection_setDate)
						row+= '<p><b>Дата: </b>'+values.EvnDirection_setDate+'</p>';
					if(values.EvnPrescr_Descr)
						row+= '<p><b>Время: </b>'+values.EvnPrescr_Descr+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		flex: 1,
		minWidth: 100,
		tdCls: 'padLeft20',
		dataIndex: 'EvnDirection_Data',
		renderer: function (value, metaData, record) {
			var text = '<span class="dirInfoIcon"></span>' + record.get('DirType_Name') + " № " + record.get('EvnDirection_Num');

			if (record.get('EvnStatus_id') && record.get('EvnStatus_id').inlist([12, 13])) {
				text = text + ' / <span style="color: red;">' + record.get('EvnStatus_Name') + ' ' + record.get('EvnDirection_statusDate') + '</span>';

				if (record.get('EvnStatusCause_Name')) {
					text = text + ', по причине: <span style="color: red;">' + record.get('EvnStatusCause_Name') + '</span>';
				}
			}

			return text;
		}
	},
	filterFn: Ext6.emptyFn(),
	modelName: 'common.EMK.models.EvnDirection',
	addPrescribeWndName: 'swEvnDirectionEditWindowExt6',
	threeDotMenuAdd: [
		{
			text: 'Просмотр',
			name: 'ViewDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
			text: 'Редактировать',
			name: 'EditDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
			text: 'Печать',
			name: 'PrintDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}
	],
	initComponent: function(){
		var me = this;
		me.callParent(arguments);
	}
});
Ext6.define("swGridEvnDirectionPat", {
	extend: "swGridEvnPrescribe",
	xtype: 'swGridEvnDirectionPat',
	params: {
		object_value: 1,
		param_name: 'section',
		param_value: 'EvnPrescrPolka',
		param_del_value: 'EvnVizitPL',
		DopDispInfoConsent_id: null,
		DirType: 'swGridEvnDirectionPat'
	},
	dirTypeCodeExcList: ['7', '29'],
	url: '/?c=EvnDirection&m=loadEvnDirectionPanel',
	alias: 'widget.swGridEvnDirectionPat',
	objectPrescribe: 'EvnDirection',
	expanderRowBodyTpl: new Ext6.XTemplate(
		'{[this.formatRow(values)]}',
		{
			formatRow: function (values) {
				var row = 'По данному назначению нет информации';
				if(values){
					row = '';
					if(values.LpuSection_Name)
						row+= '<p><b>Отделение: </b>'+values.LpuSection_Name+'</p>';
					if(values.Org_Name || values.Lpu_Name)
						row+= '<p><b>МО: </b>'+ (Ext6.isEmpty(values.Org_Name) ? values.Lpu_Name : values.Org_Name) +'</p>';
					if(values.EvnDirection_setDate)
						row+= '<p><b>Дата: </b>'+values.EvnDirection_setDate+'</p>';
					if(values.EvnPrescr_Descr)
						row+= '<p><b>Время: </b>'+values.EvnPrescr_Descr+'</p>';
				}
				return row;
			}
		}),
	columnName: {
		flex: 1,
		minWidth: 100,
		tdCls: 'padLeft20',
		dataIndex: 'EvnDirection_Data',
		renderer: function (value, metaData, record) {
			var text = '<span class="dirInfoIcon"></span>' + record.get('DirType_Name') + " № " + record.get('EvnDirection_Num');

			if (record.get('EvnStatus_id') && record.get('EvnStatus_id').inlist([12, 13])) {
				text = text + ' / <span style="color: red;">' + record.get('EvnStatus_Name') + ' ' + record.get('EvnDirection_statusDate') + '</span>';

				if (record.get('EvnStatusCause_Name')) {
					text = text + ', по причине: <span style="color: red;">' + record.get('EvnStatusCause_Name') + '</span>';
				}
			}

			return text;
		}
	},
	filterFn: Ext6.emptyFn(),
	modelName: 'common.EMK.models.EvnDirection',
	addPrescribeWndName: 'swEvnDirectionEditWindowExt6',
	threeDotMenuAdd: [
		{
			text: 'Просмотр',
			name: 'ViewDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
			text: 'Редактировать',
			name: 'EditDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}, {
			text: 'Печать',
			name: 'PrintDirection',
			handler: function() {
				if (this.ownerCt.parentGrid && this.ownerCt.selRecord && this.ownerCt.parentGrid.onMenuItemClick)
					this.ownerCt.parentGrid.onMenuItemClick(this, this.ownerCt.selRecord);
			}
		}
	],
	initComponent: function(){
		var me = this;
		me.callParent(arguments);
	}
});


Ext6.define("swPersonInfoPanel",
{
			extend: "Ext6.Panel",
			alias: 'widget.swPersonInfoPanel',
			xtype: 'swPersonInfoPanel',
			additionalFields: [],
			border: false,
			readOnly: false,
			button1Callback: Ext6.emptyFn,
			button2Callback: Ext6.emptyFn,
			button3Callback: Ext6.emptyFn,
			button4Callback: Ext6.emptyFn,
			button5Callback: Ext6.emptyFn,
			button1OnHide: Ext6.emptyFn,
			button2OnHide: Ext6.emptyFn,
			button3OnHide: Ext6.emptyFn,
			button4OnHide: Ext6.emptyFn,
			button5OnHide: Ext6.emptyFn,
			collectAdditionalParams: Ext6.emptyFn,
			getFieldValue: function(field) {
				var result = '';
				if (this.DataView.getStore().getAt(0))
					result = this.DataView.getStore().getAt(0).get(field);

				return result;
			},
			layout: 'form',
			title: langs('Загрузка...'),
			listeners: {
				'expand': function(p) {
					p.load({
						onExpand: true,
						PersonEvn_id: p.personEvnId,
						Person_id: p.personId,
						Server_id: p.serverId,
						Evn_setDT:p.Evn_setDT
					});
				},
				'render': function(panel) {
				if (panel.header) {
						panel.header.on({
							'click': {
								fn: this.toggleCollapse,
								scope: panel
							}
						});
					}
				},
				'resize': function (p,nW, nH, oW, oH){
					p.updateLayout();
				},
				'maximize': function (p,nW, nH, oW, oH){
					p.updateLayout();
				}
			},
			load: function(params) {
				var callback_param = function () {
					if (typeof params.callback == 'function') {
						params.callback();
					}
					this.setReadOnly(this.readOnly);
					this.updateLayout();
				}.createDelegate(this);
				this.personId = params.Person_id;
				this.serverId = params.Server_id;
				this.personEvnId = params.PersonEvn_id;
				this.Evn_setDT = params.Evn_setDT;

				// если персон не сменился после последней загрузки и загружена полная информация или схлапывание панели
				if ( typeof this.loadedPerson_id != undefined && this.loadedPerson_id == params.personId &&
				typeof this.loadedServer_id != undefined && this.loadedServer_id == params.serverId &&
				typeof this.loadedPersonEvn_id != undefined && this.loadedPersonEvn_id == params.personEvn &&
				this.loadedFull && params.onExpand )
					return true;

				params.LoadShort = false;
				this.loadedFull = true;

				this.loadedPerson_id = this.personId;
				this.loadedPersonEvn_id = this.personEvnId;
				this.loadedServer_id = this.serverId;
				this.loadedEvn_setDT = this.Evn_setDT;

				this.DataView.getStore().removeAll();
				this.DataView.getStore().load({
					params: params,
					callback: callback_param
				});

				this.setReadOnly(false);

				if (haveArmType('spec_mz')) {
					this.setReadOnly(true);
				} else {
					// ищем родительское окно, если есть то устанавливаем возможность редактировать пользователя в зависимости от action/readOnly в родительском окне.
					var ownerCur = this.ownerCt;
					if (typeof ownerCur != "undefined") {
						while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
							ownerCur = ownerCur.ownerCt;
						}
						if (typeof ownerCur.checkRole == 'function') {
							if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
								this.setReadOnly(true);
							}
						}
					}
				}

				this.updateLayout();
			},
			panelButtonClick: function(winType) {
				var params = this.collectAdditionalParams(winType);
				var window_name = '';

				if ( typeof params != 'object' ) {
					params = new Object();
				}

				switch ( winType ) {
					case 1:
						params.callback = this.button1Callback;
						params.onHide = this.button1OnHide;
						params.Person_Birthday = this.getFieldValue('Person_Birthday');
						params.Person_Firname = this.getFieldValue('Person_Firname');
						params.Person_Secname = this.getFieldValue('Person_Secname');
						params.Person_Surname = this.getFieldValue('Person_Surname');
						params.action = this.readOnly?'view':'edit';
						window_name = 'swPersonCardHistoryWindow';
					break;

					case 2:
						var allow_open = 1;
						var ownerCur = this.ownerCt;
						if( typeof ownerCur != "undefined" ) {
							while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
								ownerCur = ownerCur.ownerCt;
							}
							if (typeof ownerCur.checkRole == 'function') {
								if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
									allow_open = 0;
								}
							}
						}
					if (allow_open == 1) {
							params.action = 'edit';
							params.callback = this.button2Callback;
							params.onClose = this.button2OnHide;
							window_name = 'swPersonEditWindow';
						}
						else
							return false;
					break;

					case 3:
						params.callback = this.button3Callback;
						params.onHide = this.button3OnHide;
						params.Person_Birthday = this.getFieldValue('Person_Birthday');
						params.Person_Firname = this.getFieldValue('Person_Firname');
						params.Person_Secname = this.getFieldValue('Person_Secname');
						params.Person_Surname = this.getFieldValue('Person_Surname');
						params.action = this.readOnly?'view':'edit';
						window_name = 'swPersonCureHistoryWindow';
					break;

					case 4:
						params.callback = this.button4Callback;
						params.onHide = this.button4OnHide;
						params.Person_Birthday = this.getFieldValue('Person_Birthday');
						params.Person_Firname = this.getFieldValue('Person_Firname');
						params.Person_Secname = this.getFieldValue('Person_Secname');
						params.Person_Surname = this.getFieldValue('Person_Surname');
						params.action = this.readOnly?'view':'edit';
						window_name = 'swPersonPrivilegeViewWindow';
					break;

					case 5:
						params.callback = this.button5Callback;
						params.onHide = this.button5OnHide;
						params.Person_Birthday = this.getFieldValue('Person_Birthday');
						params.Person_Firname = this.getFieldValue('Person_Firname');
						params.Person_Secname = this.getFieldValue('Person_Secname');
						params.Person_Surname = this.getFieldValue('Person_Surname');
						params.action = this.readOnly?'view':'edit';
						window_name = 'swPersonDispHistoryWindow';
					break;

					default:
						return false;
					break;
				}

				params.Person_id = this.personId;
				params.Server_id = this.serverId;
				params.PersonEvn_id = this.personEvnId;
				params.Evn_setDT = this.Evn_setDT;

				if ( getWnd(window_name).isVisible() ) {
					Ext6.Msg.show({
						buttons: Ext6.Msg.OK,
						fn: Ext6.emptyFn,
						icon: Ext6.Msg.WARNING,
						msg: langs('Окно уже открыто'),
						title: ERR_WND_TIT
					});

					return false;
				}

				getWnd(window_name).show(params);
			},
			personId: null,
			serverId: null,
			personEvnId: null,
			Evn_setDT:null,
			setParams: function(params) {
				if ( typeof params != 'object' ) {
					return false;
				}

				this.personId = params.Person_id;
				this.serverId = params.Server_id;
				this.personEvnId = params.PersonEvn_id;
				this.Evn_setDT = params.Evn_setDT;
			},
			setPersonChangeParams: function(params) {
				if (getRegionNick() == 'ufa' && !params.isEvnPS) { // для Уфы только в КВС
					this.clearPersonChangeParams();
					return false;
				}
				this.personChangeParams = new Object();

				if ( typeof params != 'object' ) {
					return false;
				}

				this.personChangeParams.callback = params.callback;
				this.personChangeParams.CmpCallCard_id = params.CmpCallCard_id;
				this.personChangeParams.Evn_id = params.Evn_id;

				return true;
			},
			clearPersonChangeParams: function() {
				this.personChangeParams = new Object();

				this.personChangeParams.callback = Ext6.emptyFn;
				this.personChangeParams.CmpCallCard_id = null;
				this.personChangeParams.Evn_id = null;

				return true;
			},
			changePerson: function() {
				if ( !(getRegionNick().inlist(['perm', 'ufa'])) ) {
					return false;
				}
				else if ( !this.personChangeParams.CmpCallCard_id && !this.personChangeParams.Evn_id ) {
					return false;
				}

				var params = {
					 CmpCallCard_id: this.personChangeParams.CmpCallCard_id
					,Evn_id: this.personChangeParams.Evn_id
				}

				if ( getWnd('swPersonSearchWindow').isVisible() ) {
					Ext6.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						params.Person_id = person_data.Person_id;
						params.PersonEvn_id = person_data.PersonEvn_id;
						params.Server_id = person_data.Server_id;
						params.Person_SurName = person_data.Person_Surname;
						params.Person_FirName = person_data.Person_Firname;
						params.Person_SecName = person_data.Person_Secname;

						this.setAnotherPersonForDocument(params);
					}.createDelegate(this),
					personFirname: this.getFieldValue('Person_Firname'),
					personSecname: this.getFieldValue('Person_Secname'),
					personSurname: this.getFieldValue('Person_Surname'),
					searchMode: 'all'
				});
			},
			setAnotherPersonForDocument: function(params) {
				var loadMask = new Ext6.LoadMask(getWnd('swPersonSearchWindow').getEl(), { msg: "Переоформление документа на другого человека..." });
				loadMask.show();

				Ext6.Ajax.request({
					callback: function(options, success, response) {
						loadMask.hide();

						if ( success ) {
							var response_obj = Ext6.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								Ext6.Msg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при переоформлении документа на другого человека'));
							}
							else if ( response_obj.Alert_Msg ) {
								Ext6.Msg.show({
									buttons: Ext6.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											switch ( response_obj.Alert_Code ) {
												case 1:
													params.allowEvnStickTransfer = 2;
												case 2:
													params.ignoreAgeFioCheck = 2;
												break;
											}

											this.setAnotherPersonForDocument(params);
										}
									}.createDelegate(this),
									msg: response_obj.Alert_Msg,
									title: langs('Вопрос')
								});
							} else {
								getWnd('swPersonSearchWindow').hide();
								var info_msg = langs('Документ успешно переоформлен на другого человека');
								if (response_obj.Info_Msg) {
									info_msg += '<br>' + response_obj.Info_Msg;
								}
								Ext6.Msg.alert(langs('Сообщение'), info_msg, function() {
									this.personChangeParams.callback({
										 CmpCallCard_id: response_obj.CmpCallCard_id
										,Evn_id: response_obj.Evn_id
										,Person_id: params.Person_id
										,PersonEvn_id: params.PersonEvn_id
										,Server_id: params.Server_id
										,Person_SurName: params.Person_SurName
										,Person_FirName: params.Person_FirName
										,Person_SecName: params.Person_SecName
									});
								}.createDelegate(this));
							}
						}
						else {
							Ext6.Msg.alert(langs('Ошибка'), langs('При переоформлении документа на другого человека произошли ошибки'));
						}
					}.createDelegate(this),
					params: params,
					url: C_CHANGEPERSONFORDOC
				});
			},
			setPersonTitle: function()
			{
				if (Ext6.isEmpty(this.personId)) {
					this.setTitle('...');
					return;
				}
				var personChangeIsAvailable = (typeof this.personChangeParams == 'object' && (this.personChangeParams.CmpCallCard_id > 0 || this.personChangeParams.Evn_id > 0));
				var PersonMainInfo = '';
				if (!Ext6.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp'))) {
					PersonMainInfo = this.getFieldValue('PersonEncrypHIV_Encryp');
				}
				else {
					PersonMainInfo = this.getFieldValue('Person_Surname')+' '+
						this.getFieldValue('Person_Firname')+' '+this.getFieldValue('Person_Secname')+', '+
						Ext6.util.Format.date(this.getFieldValue('Person_Birthday'), "d.m.Y");
					if (getRegionNick() == 'kz') {
						PersonMainInfo += (this.getFieldValue('Person_Inn') && this.getFieldValue('Person_Inn').length>0)?' / ИИН '+this.getFieldValue('Person_Inn'):'';
					} else {
						PersonMainInfo += (this.getFieldValue('Polis_Ser') && this.getFieldValue('Polis_Ser').length>0)?' / полис '+this.getFieldValue('Polis_Ser')+' '+this.getFieldValue('Polis_Num'):'';
					}
				}
				this.setTitle('<div class="x-panel-collapsed-title">'+PersonMainInfo+
					(this.getFieldValue('Person_deadDT') && String(this.getFieldValue('Person_deadDT')) != '' ? ' Дата смерти: <font color=red>' + Ext6.util.Format.date(this.getFieldValue('Person_deadDT'), "d.m.Y") + '</font>' : '' ) +
					(this.getFieldValue('Person_closeDT') && String(this.getFieldValue('Person_closeDT')) != '' ? ' Дата закрытия: <font color=red>' + Ext6.util.Format.date(this.getFieldValue('Person_closeDT'), "d.m.Y") + '</font>' : '' ) +
					(personChangeIsAvailable && getRegionNick().inlist(['perm', 'ufa']) ? ' <a onclick="Ext.getCmp(\'' + this.id + '\').changePerson(); return false;" style="font-weight: bold; color: blue; text-decoration: underline;" onMouseover="this.style.cursor=\'pointer\'">СМЕНИТЬ ПАЦИЕНТА</a>' : '') + '</div>');
			},
			setReadOnly: function (is_read_only)
			{
				if (!Ext6.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp'))) {
					is_read_only = true;
				}
				if (is_read_only) {
					this.readOnly = true;
					this.ButtonPanel.items.items[1].disable();
				} else {
					this.readOnly = false;
					this.ButtonPanel.items.items[1].enable();
				}
			},
			initComponent: function()
			{
				var comp = this;

				var PolisInnField = '<div style="padding-left: 25px;">Полис: <font style="color: blue;">{Polis_Ser} {Polis_Num}</font> Выдан: <font style="color: blue;">{[Ext6.util.Format.date(values.Polis_begDate, "d.m.Y")]}, {OrgSmo_Name}</font>. Закрыт: <font style="color: blue;">{[Ext6.util.Format.date(values.Polis_endDate, "d.m.Y")]}</font></div>';
				if (getRegionNick() == 'kz') {
					PolisInnField = '<div style="padding-left: 25px;">ИИН: <font style="color: blue;">{Person_Inn}</font></div>';
				}
				// в Казахстане СНИЛС отсутствует
				var Snils = (Ext.globalOptions.globals.region.nick == 'kz') ? '' : ' СНИЛС: <font style="color: blue;">{[snilsRenderer(values.Person_Snils)]}</font>';

				if (getRegionNick() == 'ufa') {
					comp.additionalFields.push('PrivilegeType_id');
					comp.additionalFields.push('PrivilegeType_Name');
				}

				this.DataView = new Ext6.DataView(
				{
					border: false,
					frame: false,
					autoScroll: true,
					itemSelector: 'div',
					region: 'center',
					store: new Ext6.data.JsonStore(
					{
						autoLoad: false,
						baseParams: {
							mode: 'PersonInfoPanel',
							additionalFields: Ext6.util.JSON.encode(comp.additionalFields)
						},
						fields:
						[
							{name: 'Person_id'},
							{name: 'Server_id'},
							{name: 'Document_begDate', dateFormat: 'd.m.Y', type: 'date'},
							{name: 'Document_Num'},
							{name: 'Document_Ser'},
							{name: 'KLAreaType_id'},
							{name: 'Lpu_Nick'},
							{name: 'Lpu_id'},
							{name: 'LpuRegion_Name'},
							{name: 'OrgDep_Name'},
							{name: 'OrgSmo_Name'},
							{name: 'Person_Age'},
							{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
							{name: 'PersonCard_id'},
							{name: 'PersonCard_begDate', dateFormat: 'd.m.Y', type: 'date'},
							{name: 'PersonEvn_id'},
							{name: 'Person_Firname'},
							{name: 'Person_Job'},
							{name: 'Person_PAddress'},
							{name: 'Person_Phone'},
							{name: 'JobOrg_id'},
							{name: 'Person_Post'},
							{name: 'Person_RAddress'},
							{name: 'Person_Secname'},
							{name: 'Person_Snils'},
							{name: 'Person_Inn'},
							{name: 'Person_Surname'},
							{name: 'Polis_begDate', dateFormat: 'd.m.Y', type: 'date'},
							{name: 'Polis_endDate', dateFormat: 'd.m.Y', type: 'date'},
							{name: 'Polis_Num'},
							{name: 'Polis_Ser'},
							{name: 'OmsSprTerr_id'},
							{name: 'OmsSprTerr_Code'},
							{name: 'Sex_Code'},
							{name: 'Sex_id'},
							{name: 'SocStatus_id'},
							{name: 'Sex_Name'},
							{name: 'SocStatus_Name'},
							{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
							{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
							{name: 'PersonCloseCause_id'},
							{name: 'Person_IsDead'},
							{name: 'Person_IsBDZ'},
							{name: 'Person_IsFedLgot'},
							{name: 'PrivilegeType_id'},
							{name: 'PrivilegeType_Name'},
							{name: 'PersonEncrypHIV_Encryp'},
							{name: 'Person_IsAnonym'},
							{name: 'NewslatterAccept'}
						],
						url: '/?c=Common&m=loadPersonData',
						proxy: {
							limitParam: undefined,
							startParam: undefined,
							paramName: undefined,
							pageParam: undefined,
							type: 'ajax',
							url: '/?c=Common&m=loadPersonData',
							reader: {
								type: 'json'
							//	successProperty: 'success'
							//	rootProperty: 'data'
							},
							actionMethods: {
								create : 'POST',
								read   : 'POST',
								update : 'POST',
								destroy: 'POST'
							},
							extraParams: {
								mode: 'PersonInfoPanel',
								additionalFields: Ext6.util.JSON.encode(comp.additionalFields)
							}
						},
					}),
					tpl: new Ext6.XTemplate(
						'<tpl for=".">',
						'<tpl if="this.allowPersonEncrypHIV() == true">',
						'<div style="padding-left: 25px;">Шифр: <font style="color: blue;">{PersonEncrypHIV_Encryp}</font></div>',
						'</tpl>',
						'<tpl if="this.allowPersonEncrypHIV() == false">',

						'<p style="padding-left: 25px;">ФИО: <font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font> Д/р: <font style="color: blue;">{[Ext6.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> Пол: <font style="color: blue;">{Sex_Name}</font> {[(values.Person_deadDT && String(values.Person_deadDT) != "" ? "&nbsp;Дата смерти: <font color=red>" + String(Ext6.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</font>" : "&nbsp;")]} {[(values.Person_closeDT && String(values.Person_closeDT) != "" ? "&nbsp;Дата закрытия: <font color=red>" + String(Ext6.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</font>" : "&nbsp;")]}</p>',

						'<p style="padding-left: 25px;">Соц. статус: <font style="color: blue;">{SocStatus_Name}</font>'+Snils+'</p><tpl if="this.allowShowPrivilegeType(values.PrivilegeType_id) == true"><p style="padding-left: 25px;">Инвалидность: <font style="color: blue;">{PrivilegeType_Name}</font></p></tpl>',

						'<p style="padding-left: 25px;">Регистрация: <font style="color: blue;">{Person_RAddress}</font></p>',
						'<p style="padding-left: 25px;">Проживает: <font style="color: blue;">{Person_PAddress}</font></p>',
						'<p style="padding-left: 25px;">Телефон: <font style="color: blue;">{Person_Phone}</font></p>',
						PolisInnField,
						'<p style="padding-left: 25px;">Документ: <font style="color: blue;">{Document_Ser} {Document_Num}</font> Выдан: <font style="color: blue;">{[Ext6.util.Format.date(values.Document_begDate, "d.m.Y")]}, {OrgDep_Name}</font></p>',
						'<p style="padding-left: 25px;">Работа: <font style="color: blue;">{Person_Job}</font> Должность: <font style="color: blue;">{Person_Post}</font></p>',
						'<p style="padding-left: 25px;">МО: <font style="color: blue;">{Lpu_Nick}</font> Участок: <font style="color: blue;">{LpuRegion_Name}</font> Дата прикрепления: <font style="color: blue;">{[Ext6.util.Format.date(values.PersonCard_begDate, "d.m.Y")]}</font></p>',
						'<p style="padding-left: 25px;">Согласие на получение уведомлений: <font style="color: blue;">{NewslatterAccept}</font></p>',

						'</tpl>',
						'</tpl>',
						{
							allowPersonEncrypHIV: function() { return false;
								return (!Ext.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp')));
							}.createDelegate(this),
							allowShowPrivilegeType: function(PrivilegeType_id) { return true;
								if (
									PrivilegeType_id
									&& PrivilegeType_id.toString().inlist(['81','82','83','84'])
									&& getRegionNick() == 'ufa'
								) {
									return true;
								}
								return false;
							}.createDelegate(this)
						}
					)
				});
				this.ButtonPanel = new Ext6.Panel(
				{
					border: false,
					style: 'background-color: transparent;',
					defaults:
					{
						//minWidth: 130,
						xtype: 'button'
					},
					items:
					[{
						disabled: false,
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.panelButtonClick(1);
						},
						iconCls: 'pers-card16',
						tooltip: BTN_PERSCARD_TIP
					}, {
						disabled: false,
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.panelButtonClick(2);
						},
						iconCls: 'edit16',
						tooltip: BTN_PERSEDIT_TIP
					}, {
						disabled: false,
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.panelButtonClick(3);
						},
						iconCls: 'pers-curehist16',
						tooltip: BTN_PERSCUREHIST_TIP
					}, {
						disabled: false,
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.panelButtonClick(4);
						},
						iconCls: 'pers-priv16',
						tooltip: BTN_PERSPRIV_TIP
					}, {
						disabled: false,
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.panelButtonClick(5);
						},
						iconCls: 'pers-disp16',
						tooltip: BTN_PERSDISP_TIP
					}],
					region: 'east',
					width: 26
				});
				Ext6.apply(this,
				{
					border: false,
					style: 'height: 175px;',
					layout: 'form',
					listeners:
					{
						resize: function (p,nW, nH, oW, oH)
						{
							p.updateLayout();
						},
						maximize: function (p,nW, nH, oW, oH)
						{
							p.updateLayout();
						}
					},
					items:
					{
						height: 145,
						frame: true,
						border: false,
						autoScroll: true,
						region: 'center',
						layout: 'border',
						items:
						[
							this.DataView,
							this.ButtonPanel
						]
					}
				});
				this.callParent(arguments);
			}
		});

/**
 * Святло-серый грид из макета к общей и оперативной услугам. Скорее всего можно переиспользовать
 */
Ext6.define('LightGreyGridPanel', {
	extend: 'Ext6.grid.Panel',
	alias: 'widget.LightGreyGridPanel',
	cls: 'light-grey-grid',
	border: true
});

/**
 * Автоматически скрываемая таблица при 0 записей, видна при записях > 0
 */
Ext6.define('GreyAutoHideGridPanel', {
	extend: 'LightGreyGridPanel',
	alias: 'widget.GreyAutoHideGridPanel',

	viewModel: {
		data: {
			counter: 0 // счетчик записей в таблице
		}
	},

	hidden: true, // автоматически будет показана после загрузки стора

	bind: {
		hidden: '{counter == 0}' // привязываем видимость таблицы к количеству записей
	},

	listeners: {
		render: function ()
		{
			var grid = this;

			this.getStore().on('datachanged', function (store) {

				var vm = grid.getViewModel();

				if (vm)
				{
					vm.set('counter', store.getCount()); // от каунтера зависит видимость таблицы. Если 0 записей - то не показываем
					setTimeout( function () {grid.doCollapseExpand()}, 50); // если записей много, то скроллбар скрывает часть таблицы, заставляем ее развернуться еще раз, уже с учетом скроллбара
				}

				return true;
			});

			return;
		}
	}
});

// Дерево с кастомными иконками-изображениями, а не шрифтовыми. Стрелочки и папки
Ext6.define('TreePanelCustomIcons', {
	extend: 'Ext6.tree.Panel',
	alias: 'widget.TreePanelCustomIcons',
	userCls: 'tree-panel-custom-icons',
	border: false,
	hideHeaders: true,
	rootVisible: false,
	useArrows: true
});


/**
 * Таб-панель из макета к общей и оперативной услугам
 */
Ext6.define('WhiteTabPanel', {
	extend: 'Ext6.tab.Panel',
	alias: 'widget.WhiteTabPanel',
	viewModel: {
		data: {},
		formulas: {
			hidePrintIcon: function (get)
			{
				var tabPanel = this.getView(),
					wnd = tabPanel.up('window'), // родительское окно
					vm = wnd ? wnd.getViewModel() : null, // viewModel родительского окна
					EvnClass_SysNick = vm ? vm.get('EvnClass_SysNick') : '', // искомый параметр

					hidePrintIcon = [
						'EvnUslugaOper' // конфиг для форм, на которых кнопку печати надо скрыть
					];

				if (EvnClass_SysNick)
				{
					return EvnClass_SysNick.inlist(hidePrintIcon);
				} else
				{
					false; // по умолчанию показываем
				}
			}
		}
	},
	tabBar: {
		cls: 'white-tab-bar',
		defaults: {
			cls: 'simple-tab'
		},
		items: [
			{ xtype: 'tbfill' },
			{
				xtype: 'tool',
				margin: '0 24 0 0',
				cls: 'flex-center',
				iconCls: 'action_print',
				handler: 'doPrint',
				bind: {
					hidden: '{hidePrintIcon}'
				}
			}
		]
	}
});


Ext6.define('GeneralFormPanel', {
	extend: 'Ext6.form.FormPanel',
	alias: 'widget.GeneralFormPanel',
	userCls: 'general-form-panel triggers-outside-in-box-layout',
	border: false,
	layout: {
		type: 'vbox',
		align: 'stretch'
	}
});

Ext6.define('swGridPrescrGroupingFeature', {
	extend: 'Ext6.grid.feature.Grouping',
	alias: 'widget.swGridPrescrGroupingFeature',
	onBeforeGroupClick: function(view, rowElement, groupName, e) {
		return true;
	},
	onGroupClick: function(view, rowElement, groupName, e) {
		if (this.onBeforeGroupClick(view, rowElement, groupName, e)) {
			this.callParent(arguments);
		}
	}
});

Ext6.define('sw.toolbar.VerticalMenuBar', {
	extend: 'Ext6.toolbar.Toolbar',
	alias: 'widget.swverticalmenubar',
	cls: 'vertical-menu-bar',
	itemCls: 'vertical-menu-bar-item',
	defaultType: 'extendedbutton',

	defaults: {
		textAlign: 'left'
	},

	borderSides: 'all',

	setBorderSides: function(sides) {
		var me = this;

		var sideToCls = function(side) {
			return 'border-'+side;
		};

		var availableSides = ['top','bottom','left','right'];
		var availableClsList = availableSides.map(sideToCls);

		if (!sides) {
			me.removeCls(availableClsList);
		}
		if (!Ext6.isArray(sides)) {
			if (sides == 'all') {
				sides = availableSides;
			} else {
				sides = String(sides).split(' ');
			}
		}

		var addClsList = sides.filter(function(side) {
			return side.inlist(availableSides);
		}).map(sideToCls);

		me.addCls(addClsList);

		var allClsList = me.el.dom.className.split(' ');

		var removeClsList = allClsList.filter(function(cls) {
			return cls.inlist(availableClsList) && !cls.inlist(addClsList);
		});

		me.removeCls(removeClsList);
	},

	reset: function() {
		var me = this;
		me.activeItem = null;
		me.setActiveItem(me.originalActiveItemName);
	},

	findItem: function(name) {
		var me = this;
		return me.items.find(function(item) {
			return item.xtype.inlist(['button','extendedbutton']) && item.name == name;
		});
	},

	setActiveItem: function(activeItem, forceEvent) {
		var me = this;
		var name = Ext6.isObject(activeItem)?activeItem.name:activeItem;

		var oldItem = me.activeItem;
		var newItem = me.findItem(name);

		if (oldItem != newItem || forceEvent) {
			me.activeItem = newItem;
			me.fireEvent('activeitemchange', me, newItem, oldItem);
		}
	},

	getActiveItem: function() {
		var me = this;
		return me.activeItem;
	},

	getActiveItemName: function() {
		var me = this;
		return me.activeItem?me.activeItem.name:null;
	},

	listeners: {
		render: function(me) {
			if (me.borderSides) {
				me.setBorderSides(me.borderSides);
			}
		},
		afterrender: function(me) {
			if (me.originalActiveItemName) {
				me.setActiveItem(me.originalActiveItemName);
			}
		},
		add: function(me, item) {
			if (item.xtype.inlist(['button','extendedbutton'])) {
				item.handler = function() {
					me.setActiveItem(item);
				};
			}
		},
		activeitemchange: function(me, activeItem) {
			me.items.each(function(item) {
				item.removeCls('active');
			});
			if (activeItem) {
				activeItem.addCls('active');
			}
		}
	},

	initComponent: function() {
		var me = this;
		me.vertical = true;
		me.border = null;

		me.originalActiveItemName = me.activeItem;
		me.activeItem = null;

		me.callParent(arguments);
	}
});

// родительская панель с чекбоксами
Ext6.define("swGridCheckPrescr", {
	extend: "Ext6.grid.Panel",
	alias: 'widget.swGridCheckPrescr',
	prescribeGridType: 'EvnPrescribeAdd',
	viewConfig: {
		loadMask: false
	},
	userCls: 'width-grid-normal',
	cls : 'addPrescribeGrid',
	collapsible: true,
	viewModel: true,
	buttonAlign: 'center',
	hideHeaders: true,
	objectPrescribe: '',
	frame: false,
	border: false,
	listeners: {},
	default: {
		border: 0
	},
	header: {
		titlePosition: 1
	},
	bind: {
		selection: '{theRow}'
	},
	modelName: '',
	columnName: {},
	params: {},
	onDelFn: Ext6.emptyFn(),
	onPlusClick: Ext6.emptyFn(),
	btnAddClickEnable: true,
	btnPlusInHeader: true,
	btnDel: true,
	setVisibleButtons: function(disabled){
		var me = this;
		me.btnAddClick.setVisible(disabled);
		me.tools.plus.setVisible(disabled);//swGridCheckPrescrLabDiag
	},
	/**
	 * Удаляет назначение и обновляет грид
	 * @param rec удаляемая запись
	 * @param cbFn функция выполняемая после обновления грида после удаления
	 */
	deleteItemFromPacket: function (rec, cbFn) {

		var me = this,
			panel = me.panelForMsg,
			callbackFn = (typeof cbFn == 'function' ? cbFn : Ext6.emptyFn),
			onDelFn = (typeof me.onDelFn == 'function' ? me.onDelFn : Ext6.emptyFn),
			recIsSelected = false,
			selRec = me.getSelectionModel().getSelectedRecord();
		if (selRec && rec == selRec)
			recIsSelected = true;
		if (!me.panelForMsg)
			panel = Ext6.ComponentQuery.query('panel[refId="polkawpr"]')[0];

		//parentEvnClass_SysNick: EvnVizitPL
		var params = {
			value: rec.get(me.params.param_id),
			object: me.params.param_value
		};
		if (!panel)
			panel = me.up('panel').up('panel').up('panel');

		me.mask('Удаление...');
		Ext6.Ajax.request({
			url: '/?c=PacketPrescr&m=deletePrescrInPacket',
			params: params,
			callback: function (req, success, response) {
				if (success) {
					var text = 'Назначение удалено',
						type = 'success';
					me.unmask();
					var responseData = Ext6.JSON.decode(response.responseText);
					if (!responseData.success) {
						type = 'error';
						text = responseData.Error_Msg;
					} else {
						me.getStore().remove(rec);
						callbackFn();
						onDelFn(me, selRec, recIsSelected);
					}
					sw4.showInfoMsg({
						panel: panel,
						type: type,
						text: text,
						hideDelay: 2000
					});
				}
				else {
					callbackFn();
					Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
				}
			}
		});

	},
	onRender: function() {
		var me = this,
			headerTitle = false,
			toolSpacerCfg = {
				xtype: 'tbspacer',
				flex: 1
			},
			header = me.getHeader();
		//header.setHeight(24);
		me.callParent(arguments);
		if (me.btnAddClickEnable && header) {
			var headerTitle = header.getTitle();
			if (headerTitle && headerTitle.flex)
				delete(headerTitle.flex);
			var toolCfg = {
				//handler: me.onBtnAddClick,
				scope: me,
				xtype: 'button',
				cls: 'button-add-duplicate button-without-frame',
				html: 'Добавить',
				style:{
					'color': '#2196f3'
				},
				listeners:{
					click: function (btn) {
						me.onPlusClick(me, false, 'add', btn);
					}
				}
			};
			me.btnAddClick = Ext6.widget(toolCfg);
			header.insert(2, me.btnAddClick);
			// добавим спэйсер для замещения flex title-а, чтобы иконки разместились по левому и правому краям header-а
			me.custSpacer = Ext6.widget(toolSpacerCfg);
			header.insert(3, me.custSpacer);
		}
	},
	initComponent: function() {
		var me = this;
		var selModel = Ext6.create('Ext6.selection.CheckboxModel',{
			headerWidth: 61,
			checkOnly: true,
			listeners: {
				select: 'setEditMode',
				deselect: 'setEditModeSave'
			}
		});

		if(me.btnPlusInHeader){
			me.tools = [{
				tooltip: 'Добавить назначение',
				type: 'plus',
				pressed: true,
				callback: function(panel, tool, event) {
					me.onPlusClick(me, false, 'add', tool);
				}
			}];
		}

		var itemInfo = '';
		if (me.objectPrescribe.inlist(['ProcData', 'ConsUslData', 'FuncDiagData', 'LabDiagData', 'OperBlockData']))
			itemInfo = '{Lpu_Nick} {MedService_Nick}';

		var contactTpl = new Ext6.XTemplate(
			'<div class="contact-cell">',
			'<div class="contact-text-panel">',
			itemInfo,
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
		var createTool = function (toolCfg) {
			if (toolCfg.hidden) return '';
			var obj = Ext6.apply({wndId: me.getId()}, toolCfg);
			obj.actionId = actionIdTpl.apply(obj);
			Ext6.defer(function () {
				var el = Ext.get(obj.actionId);
				if (el) el.on('click', function (e) {
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
		var toolsRenderer = function (value, meta, record) {
			if (!record.get('active')) return '';
			var id = record.get(me.params.param_id);
			var tools = [{
				id: id,
				name: 'delPacket',
				qtip: 'Удалить из пакета',
				handler: function () {
					if (record) {
						me.deleteItemFromPacket(record);
					}
				}
			}];
			if(me.btnDel) return tools.map(createTool).join('');
			else return '';
		};
		var captionRenderer = function (value, meta, record) {
			var obj = Ext6.apply(record.data, {
				tools: toolsRenderer.apply(me, arguments)
			});
			return contactTpl.apply(obj);
		};

		Ext6.apply(this, {
			store: {
				model: me.modelName,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					extraParams: {
						"objectPrescribe" : me.objectPrescribe
					},
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			},
			objectPrescribe: me.objectPrescribe,
			columns: [
				me.columnName,
				{
					text: '',
					flex: 1,
					renderer: captionRenderer
				}
			],
			selModel: selModel,
			listeners: {
				itemmouseenter: function (grid, record) {
					record.set('active', true);
					},
				itemmouseleave: function (grid, record) {
					record.set('active', false);
				}
			}
		});

		me.callParent(arguments);
	}
});
Ext6.define("swGridCheckPrescrDiet", {
	extend: "swGridCheckPrescr",
	xtype: 'swGridCheckPrescrDiet',
	alias: 'widget.swGridCheckPrescrDiet',
	objectPrescribe: 'DietData',
	modelName: 'common.EMK.models.CureStandDiet',
	columnName: {
		dataIndex: 'PrescriptionDietType_Name',
		flex: 1,
		sortable: true,
		align: 'left',
		renderer: function(val, metaData, record) {
			var s = '';
			s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('PrescriptionDietType_Name')+'</span></div></div>';
			return s;
		}
	},
	title: 'ДИЕТА',
	params: {
		param_id: 'PacketPrescrDiet_id',
		param_value: 'PacketPrescrDiet'
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
});
Ext6.define("swGridCheckPrescrRegime", {
	extend: "swGridCheckPrescr",
	xtype: 'swGridCheckPrescrRegime',
	alias: 'widget.swGridCheckPrescrRegime',
	objectPrescribe: 'RegimeData',
	modelName: 'common.EMK.models.CureStandRegime',
	columnName: {
		dataIndex: 'PrescriptionRegimeType_Name',
		flex: 1,
		sortable: true,
		align: 'left',
		renderer: function(val, metaData, record) {
			var s = '';
			s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">' + record.get('PrescriptionRegimeType_Name')+'</span></div></div>';
			return s;
		}
	},
	title: 'РЕЖИМ',
	params: {
		param_id: 'PacketPrescrRegime_id',
		param_value: 'PacketPrescrRegime'
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
});
Ext6.define("swGridCheckPrescrProc", {
	extend: "swGridCheckPrescr",
	xtype: 'swGridCheckPrescrProc',
	alias: 'widget.swGridCheckPrescrProc',
	objectPrescribe: 'ProcData',
	modelName: 'common.EMK.models.CureStandProc',
	columnName: {
		text: '',
		dataIndex: 'UslugaComplex_Name',
		renderer: function(val, metaData, record) {
			var s = '';
			s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('UslugaComplex_id') + ' ' + record.get('UslugaComplex_Name')+'</span></div></div>';
			return s;
		},
		flex: 1
	},
	title: 'МАНИПУЛЯЦИИ И ПРОЦЕДУРЫ',
	params: {
		param_id: 'PacketPrescrUsluga_id',
		param_value: 'PacketPrescrUsluga'
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
});

Ext6.define("swGridCheckPrescrConsUsl", {
	extend: "swGridCheckPrescr",
	xtype: 'swGridCheckPrescrConsUsl',
	alias: 'widget.swGridCheckPrescrConsUsl',
	objectPrescribe: 'ConsUslData',
	modelName: 'common.EMK.models.CureStandConsUsl',
	columnName: {
		text: '',
		dataIndex: 'UslugaComplex_Name',
		renderer: function(val, metaData, record) {
			var s = '';
			s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('UslugaComplex_id') + ' ' + record.get('UslugaComplex_Name')+'</span></div></div>';
			return s;
		},
		flex: 1
	},
	title: 'КОНСУЛЬТАЦИОННАЯ УСЛУГА',
	params: {
		param_id: 'PacketPrescrUsluga_id',
		param_value: 'PacketPrescrUsluga'
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
});

Ext6.define("swGridCheckPrescrFuncDiag", {
	extend: "swGridCheckPrescr",
	xtype: 'swGridCheckPrescrFuncDiag',
	alias: 'widget.swGridCheckPrescrFuncDiag',
	objectPrescribe: 'FuncDiagData',
	modelName: 'common.EMK.models.CureStandFuncDiag',
	columnName: {
		text: '',
		dataIndex: 'UslugaComplex_Name',
		renderer: function(val, metaData, record) {
			var s = '';
			s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('UslugaComplex_id') + ' ' + record.get('UslugaComplex_Name')+'</span></div></div>';
			return s;
		},
		flex: 1
	},
	title: 'ИНСТРУМЕНТАЛЬНАЯ ДИАГНОСТИКА',
	params: {
		param_id: 'PacketPrescrUsluga_id',
		param_value: 'PacketPrescrUsluga'
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
});

Ext6.define("swGridCheckPrescrLabDiag", {
	extend: "swGridCheckPrescr",
	xtype: 'swGridCheckPrescrLabDiag',
	alias: 'widget.swGridCheckPrescrLabDiag',
	objectPrescribe: 'LabDiagData',
	modelName: 'common.EMK.models.CureStandLabDiag',
	columnName: {
		text: '',
		dataIndex: 'UslugaComplex_Name',
		renderer: function(val, metaData, record) {
			var s = '';
			s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('UslugaComplex_id') + ' ' + record.get('UslugaComplex_Name')+'</span></div></div>';
			return s;
		},
		flex: 1
	},
	title: 'ЛАБОРАТОРНАЯ ДИАГНОСТИКА',
	params: {
		param_id: 'PacketPrescrUsluga_id',
		param_value: 'PacketPrescrUsluga'
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
});

Ext6.define("swGridCheckPrescrDrug", {
	extend: "swGridCheckPrescr",
	xtype: 'swGridCheckPrescrDrug',
	alias: 'widget.swGridCheckPrescrDrug',
	objectPrescribe: 'DrugData',
	modelName: 'common.EMK.models.PacketPrescrDrug',
	columnName: {
		dataIndex: 'DrugListData',
		flex: 1,
		align: 'left',
		renderer: function(value) {
			var resStr = ''; // Для назначения idшника всей строки в целом EXTJSом
			if(value){
				var manyDrug = (Object.keys(value).length > 1);
				if (manyDrug)
					resStr += '<div style="display:table;"><div class="many-drugs-packet-icon" style="display: table-cell"></div>';
				value.forEach(function(e){
					if(manyDrug)
						resStr += '<div class="onePrescr" ><span class="manyEvnPrescr" >'+e.Drug_Name+'</span></div>';
					else
						resStr += '<div style="display: flex"><div class="one-drugs-packet-icon"></div><div class="onePrescr" style="line-height: 25px">' + e.Drug_Name + '</div></div>';
				});
				if (manyDrug)
					resStr += '</div>';
			}
			return resStr;
		}
	},
	title: 'ЛЕКАРСТВЕННЫЕ НАЗНАЧЕНИЯ',
	params: {
		param_id: 'PacketPrescrTreat_id',
		param_value: 'PacketPrescrTreat'
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
});

Ext6.define("swGridWithBtnAddRecords", {
	extend: "Ext6.grid.Panel",
	params: {},
	alias: 'widget.swGridWithBtnAddRecords',
	// Показывать кнопку "Показать ещё"
	withBtnShowMore: true,
	// Автоматически подгружать записи при скролле до конца вниз
	withAutoAddRecordsEndScroll: true,
	// Загружены все возможные записи (больше грузить не нужно)
	allRowsLoaded: false,
	onLoadGrid: function(store, records, successful, operation, eOpts) {
		log('onLoadGrid', store, records, successful, operation, eOpts);

		var me = this;

		if(me.withBtnShowMore) {
			this.getEl().query(".show-more-div").forEach(function (showMoreDiv) {
				showMoreDiv.remove();
			});
			var cont = this.getEl().query(".x6-grid-item-container");
			var showMoreDiv = document.createElement('div');
			if (records && records.length >= 10) {
				if (cont && cont[0]) {
					showMoreDiv.innerHTML = "<a href='#' onClick='Ext6.getCmp(\"" + this.id + "\").showMore(\"" + me.loadedByAllLpu + "\");' class='show-more-button'>Показать ещё</a>";
					showMoreDiv.className = "show-more-div";
					Ext6.get(cont[0]).append(showMoreDiv);
				}
			} else {
				if (cont && cont[0]) {
					var s = "";
					if (this.getStore().getCount() == 0) {
						s = s + "<div class='not-found'>Записи не найдены</div><br>";
					}

					if (s.length > 0) {
						showMoreDiv.innerHTML = s;
						showMoreDiv.className = "show-more-div";
						Ext6.get(cont[0]).append(showMoreDiv);
					}
				}
			}
		}
	},
	reload: function(){
		var st = this.getStore(),
			params = Ext6.Object.merge({start: 0},this.params);
		st.load({
			params: params,
			addRecords: true
		});
	},
	loadAll: function() {
		var me = this,
			st = me.getStore(),
			params = Ext6.Object.merge({start: st.getTotalCount()},this.params);

		st.load({
			params: params,
			addRecords: true,
			callback: function (answ) {
				if(answ.length == params.limit) {
					me.loadAll();
				} else {
					me.onLoadGrid();
				}
			}
		});
	},
	showMore: function(y) {
		var me = this,
			st = me.getStore(),
			scroll = me.getScrollable(),
			params = Ext6.Object.merge({start: st.getTotalCount()},this.params),
			scrollAvailable = (st.getTotalCount() == st.getCount());
		st.load({
			params: params,
			addRecords: true,
			callback: function(){
				if(y && scrollAvailable)
					scroll.scrollTo(null, y, false);
			}
		});
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
		me.getStore().on({
			load: function (store, records, successful, operation, eOpts) {
				if(me.withBtnShowMore)
					me.onLoadGrid(store, records, successful, operation, eOpts);
				// Если при загрузке не пришли записи - значит больше грузиться нечего, а при скролле подавно
				me.allRowsLoaded = (!(records && records.length));
			}
		});
		if(me.withAutoAddRecordsEndScroll)
			me.getScrollable().on({
				scrollend: function( scroll, x, y, deltaX, deltaY, eOpts ){
					var max = scroll.getMaxPosition(),
						maxY = max?max.y:false;
					// Если скролл опустился до максимального значения по y
					// а также есть еще записи, которые можно грузить - грузим следующие записи
					if(maxY && y == maxY && !me.allRowsLoaded){
						me.showMore(y);
					}
				}
			});
	}
});

Ext6.define("swGridWithBtnShowAllRecordsThisYear", {
	extend: "Ext6.grid.Panel",
	params: {},
	alias: 'widget.swGridWithBtnShowAllRecordsThisYear',
	// Показывать кнопку "Показать все"
	withBtnShowAll: false,
	// Автоматически подгружать записи при скролле до конца вниз
	withAutoAddRecordsEndScroll: true,
	// Загружены все возможные записи (больше грузить не нужно)
	allRowsLoaded: false,
	onLoadGrid: function(store, records, successful, operation, eOpts) {
		var me = this;

		//используем show-more-div, так как стили нам все подходят,а создавать тот же элемент с другим именем не имеет смысла
		if(me.withBtnShowAll) {
			this.getEl().query(".show-more-div").forEach(function (showMoreDiv) {
				showMoreDiv.remove();
			});
			var cont = this.getEl().query(".x6-grid-item-container");
			var showAllDiv = document.createElement('div');
			if (cont && cont[0]) {
				if (records && records.length != 0 && records.length % me.params.limit == 0) {
					showAllDiv.innerHTML = "<a href='#' onClick='Ext6.getCmp(\"" + this.id + "\").loadAll();' class='show-more-button'>Показать все</a>";
					showAllDiv.className = "show-more-div";
					Ext6.get(cont[0]).append(showAllDiv);
				} else {
					var s = "";
					if (this.getStore().getCount() == 0) {
						s = s + "<div class='not-found'>Записи не найдены</div><br>";
					}

					if (s.length > 0) {
						showAllDiv.innerHTML = s;
						showAllDiv.className = "show-more-div";
						Ext6.get(cont[0]).append(showAllDiv);
					}
				}
			}
		}
	},
	loadAll: function() {
		var me = this,
			st = me.getStore(),
			params = Ext6.Object.merge({start: st.getTotalCount()}, this.params);

		st.load({
			params: params,
			addRecords: true,
			callback: function (answ) {
				if(answ.length == params.limit) {
					me.loadAll();
				} else {
					me.onLoadGrid();
				}
			}
		});
	},
	initComponent: function() {
		var me = this;
		me.callParent(arguments);
		me.getStore().on({
			load: function (store, records, successful, operation, eOpts) {
				if(me.withBtnShowAll)
					me.onLoadGrid(store, records, successful, operation, eOpts);
				me.allRowsLoaded = (!(records && records.length));
			}
		});
		if(me.withAutoAddRecordsEndScroll)
			me.getScrollable().on({
				scrollend: function( scroll, x, y, deltaX, deltaY, eOpts ){
					var max = scroll.getMaxPosition(),
						maxY = max?max.y:false;
					if(maxY && y == maxY && !me.allRowsLoaded){
						me.showMore(y);
					}
				}
			});
	}
});

Ext6.define("swDirectionInfoPanel", {
	extend: "Ext6.form.Panel",
	alias: 'widget.swDirectionInfoPanel',
	region: 'north',
	autoHeight: true,
	layout: 'anchor',
	border: false,
	hidden: false,
	fieldIsWithDirectionName: 'EvnPL_IsWithoutDirection',
	fieldPrehospDirectName: 'PrehospDirect_Name',
	fieldLpuSectionName: 'LpuSection_Name',
	fieldMedStaffFactName: 'Person_fio',
	fieldOrgName: 'Lpu_Nick',
	fieldNumName: 'EvnDirection_Num',
	fieldSetDateName: 'EvnDirection_setDate',
	fieldDiagName: 'Diag_Name',
	fieldDiagFName:'Diag_fid',
	fieldDiagPreidName:'Diag_preid',
	fieldIdName: 'EvnDirection_id',
	fieldIsAutoName: 'EvnDirection_IsAuto',
	fieldIsExtName: 'EvnDirection_IsReceive',
	fieldDoctorCode: 'MedPersonalCode',
	loadEvnDirection: function(EvnPL_id) {
		let me = this;
		let base_form = me.getForm();
		me.queryById('whoDirectEvnPL').setTitle('Кем направлен');
		base_form.load({
			params: {EvnPL_id: EvnPL_id},
			success: function (one, two, three) {
				let orgNick = base_form.findField(me.fieldOrgName).getValue();
				if ( orgNick == null) {
					orgNick = base_form.findField(me.fieldPrehospDirectName).getValue();
				}
				let medStaff = base_form.findField(me.fieldMedStaffFactName).getValue();
				if (medStaff == null) {
					medStaff = '';
				}
				me.queryById('whoDirectEvnPL').setTitle('Кем направлен ' + (orgNick ? orgNick : '') + ' - ' + medStaff);
				let isWithDirection = me.ownerWin.EvnPLDirectionInfoEditPanel.isWithDirection(base_form.findField(me.fieldIdName).getValue(),
					base_form.findField(me.fieldIsAutoName).getValue(),
					base_form.findField(me.fieldIsExtName).getValue(),
					base_form.findField(me.fieldNumName).getValue());
				base_form.findField(me.fieldIsWithDirectionName).setValue(isWithDirection == 1? 'Да' : 'Нет');
			}
		});
	},
	initComponent: function () {
		let me = this;

		Ext6.apply(me, {
			layout: 'anchor',
			items: [{
				layout: 'column',
				border: false,
				style: 'margin-bottom: 5px;',
				anchor:'100%',
				defaults: {
					border: false,
					labelWidth: 172
				},
				items: [{
					xtype: 'fieldset',
					title: 'Кем направлен ',
					border: false,
					collapsible: true,
					columnWidth: .95,
					id: 'whoDirectEvnPL',
					defaults: {
						margin: "0px 0px 2px 10px",
						labelWidth: 270,
						style: 'text-align:right',
						fieldStyle: 'font-weight: 700'
					},
					cls: 'personPanel',
					items: [{
						xtype: 'hidden',
						name: 'EvnPL_id'
					}, {
						xtype: 'hidden',
						name: me.fieldIdName
					}, {
						xtype: 'hidden',
						name: me.fieldIsAutoName
					}, {
						xtype: 'hidden',
						name: me.fieldIsExtName
					}, {
						xtype: 'displayfield',
						fieldLabel: langs('Электронное направление'),
						name: me.fieldIsWithDirectionName
					}, {
						xtype: 'displayfield',
						fieldLabel: langs('Кем направлен'),
						name: me.fieldPrehospDirectName
					}, {
						xtype: 'displayfield',
						fieldLabel: langs('Организация'),
						name: me.fieldOrgName
					}, {
						xtype: 'displayfield',
						fieldLabel: langs('Отделение'),
						name: me.fieldLpuSectionName
					}, {
						xtype: 'displayfield',
						fieldLabel: langs('Врач'),
						name: me.fieldMedStaffFactName
					}, {
						xtype: 'hidden',
						name: me.fieldDoctorCode
					},{
						xtype: 'displayfield',
						fieldLabel: langs('№ направления'),
						name: me.fieldNumName
					}, {
						xtype: 'displayfield',
						fieldLabel: langs('Дата направления'),
						name: me.fieldSetDateName
					}, {
						xtype: 'displayfield',
						fieldLabel: langs('Диагноз напр. учреждения'),
						name: me.fieldDiagName
					}]
				}, {
					xtype: 'button',
					padding: "13px 10px",
					userCls: 'button-without-frame',
					iconCls: 'panicon-edit-pers-info',
					name: 'editPerson',
					tooltip: langs('Редактировать данные пациента'),
					handler: function () {
						if (me.ownerWin.EvnPLFormPanel.accessType == 'view' || me.ownerWin.EvnPL_IsFinish) {
							return false;
						}
						let EvnPL_id = me.ownerWin.EvnPL_id;
						let EvnPLDIEP = me.ownerWin.EvnPLDirectionInfoEditPanel;
						me.hide();
						EvnPLDIEP.show();
						EvnPLDIEP.onReset();
						EvnPLDIEP.isReadOnly = false;
						EvnPLDIEP.loadEvnDirection(EvnPL_id);
					}
				}]
			}],
		});

		me.callParent(arguments);
	}
});

Ext6.define("swDirectionInfoEditPanel", {
	extend: "Ext6.form.Panel",
	alias: 'widget.swDirectionInfoEditPanel',
	accessType: 'view',
	region: 'north',
	hidden: true,
	autoHeight: true,
	layout: 'anchor',
	border: false,
	prefix: '',
	useCase: null,
	personFieldName: null,
	fromLpuFieldName: null,
	fieldIsWithDirectionName: null,
	buttonSelectId: null,
	parentSetDateFieldName: null,
	nextFieldName: null,
	showMedStaffFactCombo: false,
	fieldPrehospDirectName: 'PrehospDirect_id',
	fieldLpuSectionName: 'LpuSection_did',
	fieldMedStaffFactName: 'MedStaffFact_did',
	fieldOrgName: 'Org_did',
	fieldNumName: 'EvnDirection_Num',
	fieldSetDateName: 'EvnDirection_setDate',
	fieldDiagName: 'Diag_did',
	fieldDiagFName:'Diag_fid',
	fieldDiagPreidName:'Diag_preid',
	fieldIdName: 'EvnDirection_id',
	fieldIsAutoName: 'EvnDirection_IsAuto',
	fieldIsExtName: 'EvnDirection_IsReceive',
	fieldDoctorCode: 'MedPersonalCode',
	medStaffFactFieldName: null,
	isReadOnly: false,//можно ли заполнять поля
	isDisabledChooseDirection: false,//можно ли выбрать электронное направление
	getBaseForm: function() {
		return this.getForm();
	},
	defineIsWithDirectionValue: function() {
		var me = this, base_form = me.getBaseForm();
		return me.isWithDirection(base_form.findField(me.fieldIdName).getValue(), base_form.findField(me.fieldIsAutoName).getValue(), base_form.findField(me.fieldIsExtName).getValue(), base_form.findField(me.fieldNumName).getValue());
	},
	onReset: function() {
		var me = this, base_form = me.getBaseForm(),
			iswd_combo = base_form.findField(me.fieldIsWithDirectionName);
		me.isReadOnly = false;
		me.isDisabledChooseDirection = false;
		setLpuSectionGlobalStoreFilter();
		base_form.findField(me.fieldLpuSectionName).getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
		setMedStaffFactGlobalStoreFilter();
		base_form.findField(me.fieldMedStaffFactName).getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
		base_form.findField(me.fieldMedStaffFactName).setContainerVisible(me.showMedStaffFactCombo);
		me._applyEvnDirectionData(null);
		iswd_combo.setValue(0);
		iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
	},
	loadEvnDirection: function(EvnPL_id) {
		let me = this;
		let base_form = me.getBaseForm();
		base_form.load({
			params: {
				EvnPL_id: EvnPL_id
			},
			success: function () {
				me.onLoadForm();
			}
		});
	},
	onBeforeSubmit: function(win, params) {
		var me = this,
			base_form = me.getBaseForm(),
			org_combo = base_form.findField(me.fieldOrgName),
			diag_combo = base_form.findField(me.fieldDiagName),
			evn_direction_set_date_field = base_form.findField(me.fieldSetDateName),
			evn_direction_num_field = base_form.findField(me.fieldNumName),
			lpu_section_combo = base_form.findField(me.fieldLpuSectionName),
			med_staff_fact_combo = base_form.findField(me.fieldMedStaffFactName),
			prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName),
			evn_direction_id_field = base_form.findField(me.fieldIdName),
			iswd_combo = base_form.findField(me.fieldIsWithDirectionName);

		if (!getRegionNick().inlist(['buryatiya', 'ekb', 'kaluga', 'kareliya', 'krym', 'perm'])
			&& me.useCase.inlist([
				'choose_for_evnplstom'
				,'choose_for_evnpl'
				,'choose_for_evnpl_stream_input'
				,'choose_for_evnplstom_stream_input'
			])
			&& iswd_combo.getValue() == 2
			&& prehosp_direct_combo.getValue() == 2
			&& Ext.isEmpty(evn_direction_id_field.getValue())
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					prehosp_direct_combo.focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: langs('При выбранном значении "Другое ЛПУ" в поле "Кем направлен" выбор электронного направления является обязательным'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if ( iswd_combo.disabled ) {
			params[me.fieldIsWithDirectionName] = iswd_combo.getValue();
		}
		if ( prehosp_direct_combo.disabled ) {
			params[me.fieldPrehospDirectName] = prehosp_direct_combo.getValue();
		}
		if ( lpu_section_combo.disabled ) {
			params[me.fieldLpuSectionName] = lpu_section_combo.getValue();
		}
		if ( med_staff_fact_combo.disabled ) {
			params[me.fieldMedStaffFactName] = med_staff_fact_combo.getValue();
		}
		if ( org_combo.disabled ) {
			params[me.fieldOrgName] = org_combo.getValue();
		}
		if ( diag_combo.disabled ) {
			params[me.fieldDiagName] = diag_combo.getValue();
		}
		if ( evn_direction_num_field.disabled ) {
			params[me.fieldNumName] = evn_direction_num_field.getRawValue();
		}
		params[me.fieldSetDateName] = Ext.util.Format.date(evn_direction_set_date_field.getValue(), 'd.m.Y');
		return params;
	},
	calcPrehospDirectId: function(Lpu_sid, Org_did, LpuSection_id, EvnDirection_IsAuto) {
		if (!EvnDirection_IsAuto) {
			EvnDirection_IsAuto = 1;
		}
		if (2 == EvnDirection_IsAuto) {
			return null;
		}
		if (!Ext.isEmpty(Lpu_sid) && LpuSection_id && Lpu_sid == getGlobalOptions().lpu_id)  {
			// Отделение ЛПУ
			if (getRegionNick() == 'kz') {
				return 8;
			} else {
				return 1;
			}
		}
		if (!Ext.isEmpty(Lpu_sid) && Lpu_sid != getGlobalOptions().lpu_id)  {
			// Другое ЛПУ
			if (getRegionNick() == 'kz') {
				return 14;
			} else {
				return 2;
			}
		}
		if (Org_did && getGlobalOptions().org_id && Org_did != getGlobalOptions().org_id && getRegionNick() != 'kz')  {
			return 3; // Другая организация
		}
		return null;
	},
	onLoadForm: function() {
		var me = this,
			base_form = me.getBaseForm(),
			prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName),
			evn_direction_data = {
				PrehospDirect_id: prehosp_direct_combo.getValue(),
				Lpu_sid: base_form.findField(me.fromLpuFieldName).getValue(),
				EvnDirection_id: base_form.findField(me.fieldIdName).getValue(),
				EvnDirection_IsAuto: base_form.findField(me.fieldIsAutoName).getValue(),
				EvnDirection_IsReceive: base_form.findField(me.fieldIsExtName).getValue(),
				EvnDirection_Num: base_form.findField(me.fieldNumName).getValue(),
				EvnDirection_setDate: base_form.findField(me.fieldSetDateName).getValue(),
				LpuSection_id: base_form.findField(me.fieldLpuSectionName).getValue(),
				MedStaffFact_id: base_form.findField(me.fieldMedStaffFactName).getValue(),
				Org_did: base_form.findField(me.fieldOrgName).getValue(),
				Diag_did: base_form.findField(me.fieldDiagName).getValue(),
				Diag_fid: base_form.findField(me.fieldDiagFName).getValue(),
				Diag_preid: base_form.findField(me.fieldDiagPreidName).getValue()
			};

		if (prehosp_direct_combo.getStore().getCount() == 0) {
			prehosp_direct_combo.getStore().load({
				callback: function(){
					me._applyEvnDirectionData(evn_direction_data);
				}
			});
		} else {
			me._applyEvnDirectionData(evn_direction_data);
		}
	},
	isWithDirection: function(EvnDirection_id, EvnDirection_IsAuto, EvnDirection_IsReceive, EvnDirection_Num) {
		if (!EvnDirection_id) {
			return 0;
		}
		if (!EvnDirection_IsAuto) {
			EvnDirection_IsAuto = 1;
		}
		if (!EvnDirection_IsReceive) {
			EvnDirection_IsReceive = 1;
		}
		// Нужно отображать “С ЭН”=ДА только если направление неавтоматическое или внешнее. Для остальных направлений должно быть “С ЭН”=нет
		if (1 == EvnDirection_IsAuto/* || 2 == EvnDirection_IsReceive*/) {
			return 1;
		}
		return 0;
	},
	_applyEvnDirectionData: function(data, notDisableChoose) {
		var me = this,
			base_form = me.getBaseForm(),
			from_lpu_field = base_form.findField(me.fromLpuFieldName),
			diag_combo = base_form.findField(me.fieldDiagName),
			evn_direction_id_field = base_form.findField(me.fieldIdName),
			evn_direction_isauto_field = base_form.findField(me.fieldIsAutoName),
			evn_direction_isext_field = base_form.findField(me.fieldIsExtName),
			evn_direction_set_date_field = base_form.findField(me.fieldSetDateName),
			evn_direction_num_field = base_form.findField(me.fieldNumName),
			lpu_section_combo = base_form.findField(me.fieldLpuSectionName),
			med_staff_fact_combo = base_form.findField(me.fieldMedStaffFactName),
			diag_f_combo = base_form.findField(me.fieldDiagFName),
			fieldDiagPreidName = base_form.findField(me.fieldDiagPreidName),
			org_combo = base_form.findField(me.fieldOrgName),
			prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName),
			iswd_combo = base_form.findField(me.fieldIsWithDirectionName);

		from_lpu_field.setValue(null);
		diag_combo.setValue(null);
		diag_f_combo.setValue(null);
		fieldDiagPreidName.setValue(null);
		evn_direction_id_field.setValue(null);
		evn_direction_isauto_field.setValue(null);
		evn_direction_isext_field.setValue(null);
		evn_direction_set_date_field.setValue(null);
		evn_direction_num_field.setValue('');
		lpu_section_combo.setValue(null);
		med_staff_fact_combo.setValue(null);
		org_combo.setValue(null);
		prehosp_direct_combo.setValue(null);
		// Убираем признак обязательности заполнения с полей
		lpu_section_combo.setAllowBlank(true);
		med_staff_fact_combo.setAllowBlank(true);
		org_combo.setAllowBlank(true);
		diag_combo.setAllowBlank(true);
		evn_direction_set_date_field.setAllowBlank(true);
		evn_direction_num_field.setAllowBlank(true);
		fieldDiagPreidName.setContainerVisible(false);
		fieldDiagPreidName.setDisabled(true);
		//
		log('_applyEvnDirectionData', data);

		if (!notDisableChoose) { // Если электронное направление в талоне выбрано впервые, то до момента сохранения талона возможен выбор другого электронного направления.
			me.isDisabledChooseDirection = me.isReadOnly || (data && data.EvnDirection_id && data.EvnDirection_id > 0);
		}

		me.isReadOnly = me.isReadOnly || (data && data.EvnDirection_id && data.EvnDirection_id > 0 && 2 != data.EvnDirection_IsAuto);
		me.queryById(me.buttonSelectId).setDisabled(me.isDisabledChooseDirection);
		iswd_combo.setDisabled(me.isDisabledChooseDirection);
		prehosp_direct_combo.setDisabled(me.isReadOnly);
		org_combo.setDisabled(me.isReadOnly);
		lpu_section_combo.setDisabled(me.isReadOnly);
		med_staff_fact_combo.setDisabled(me.isReadOnly);
		evn_direction_num_field.setDisabled(me.isReadOnly);
		evn_direction_set_date_field.setDisabled(me.isReadOnly);
		diag_combo.setDisabled(me.isReadOnly);
		if (data) {
			if (2 == data.EvnDirection_IsAuto) {
				// информацию из направления не подтягиваем
				/*
				data.EvnDirection_Num = '';
				data.EvnDirection_setDate = '';
				data.LpuSection_id = null;
				data.MedStaffFact_id = null;
				data.Org_did = null;
				data.Diag_did = null;
				data.Lpu_sid = null;
				*/
			}
			var PrehospDirect_id = null;
			if (data.PrehospDirect_id) {
				PrehospDirect_id = data.PrehospDirect_id;
			} else {
				PrehospDirect_id = me.calcPrehospDirectId(data.Lpu_sid, data.Org_did, data.LpuSection_id, data.EvnDirection_IsAuto);
			}
			prehosp_direct_combo.setValue(PrehospDirect_id);
			evn_direction_id_field.setValue(data.EvnDirection_id||null);
			evn_direction_isauto_field.setValue(data.EvnDirection_IsAuto||null);
			evn_direction_isext_field.setValue(data.EvnDirection_IsReceive||null);
			from_lpu_field.setValue(data.Lpu_sid||null);
			evn_direction_num_field.setValue(data.EvnDirection_Num||'');
			evn_direction_set_date_field.setValue(data.EvnDirection_setDate);
			if ( data.Diag_did ) {
				diag_combo.getStore().load({
					callback: function() {
						diag_combo.getStore().each(function(record) {
							if ( record.get('Diag_id') == data.Diag_did ) {
								diag_combo.setValue(data.Diag_did);
								diag_combo.fireEvent('select', diag_combo, record, 0);
								diag_combo.fireEvent('change', diag_combo, data.Diag_did);
							}
						});
						if(data.Diag_preid){
							fieldDiagPreidName.getStore().load({
								callback: function() {
									fieldDiagPreidName.getStore().each(function(record) {
										if ( record.get('Diag_id') == data.Diag_preid ) {
											fieldDiagPreidName.setValue(data.Diag_preid);
											fieldDiagPreidName.fireEvent('select', fieldDiagPreidName, record, 0);
										}
									})
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + data.Diag_preid
								}
							})
						}
					},
					params: {
						where: "where DiagLevel_id = 4 and Diag_id = " + data.Diag_did
					}
				});
			}
			if (me.isDisabledChooseDirection) {
				iswd_combo.setValue(me.defineIsWithDirectionValue());
				var prehosp_direct_sysnick = prehosp_direct_combo.getFieldValue('PrehospDirect_SysNick');
				var org_type = '';
				switch (prehosp_direct_sysnick) {
					case 'lpusection':
					case 'OtdelMO':
						if (data.LpuSection_id) {
							lpu_section_combo.setValue(data.LpuSection_id);
						}
						if (data.MedStaffFact_id) {
							med_staff_fact_combo.setValue(data.MedStaffFact_id);
						}
						break;
					case 'lpu':
					case 'skor':
					case 'DrMO':
					case 'Skor':
						org_type = 'lpu';
						break;
					case 'rvk':
					case 'Rvk':
						org_type = 'military';
						break;
					case 'org':
					case 'admin':
					case 'Pmsp':
					case 'Kdp':
					case 'Stac':
					case 'Rdom':
						org_type = 'org';
						break;
				}
				if ( data.Org_did ) {
					org_combo.getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								org_combo.setValue(data.Org_did);
								me.checkOtherLpuDirection();
							}
						},
						params: {
							Org_id: data.Org_did,
							OrgType: org_type
						}
					});

					if (data.LpuSection_id) {
						lpu_section_combo.getStore().load({
							params: {
								Org_id: data.Org_did,
								mode: 'combo'
							},
							callback: function () {
								lpu_section_combo.setValue(data.LpuSection_id);
							}
						});
					}

					if (data.MedStaffFact_id) {
						med_staff_fact_combo.getStore().load({
							params: {
								Org_id: data.Org_did,
								andWithoutLpuSection: 3,
								mode: 'combo'
							},
							callback: function () {
								med_staff_fact_combo.setValue(data.MedStaffFact_id);
							}
						});
					}
				} else if (data.Lpu_sid) {
					org_combo.getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								if (records.length == 1)  {
									org_combo.setValue(records[0].get('Org_id'));
									me.checkOtherLpuDirection();
								}
							}
						},
						params: {
							Lpu_oid: data.Lpu_sid,
							OrgType: org_type
						}
					});

					if (data.LpuSection_id) {
						lpu_section_combo.getStore().load({
							params: {
								Lpu_id: data.Lpu_sid,
								LpuSection_id: data.LpuSection_id,
								mode: 'combo'
							},
							callback: function () {
								lpu_section_combo.setValue(data.LpuSection_id);
							}
						});
					}

					if (data.MedStaffFact_id) {
						med_staff_fact_combo.getStore().load({
							params: {
								Lpu_id: data.Lpu_sid,
								MedStaffFact_id: data.MedStaffFact_id,
								andWithoutLpuSection: 3,
								mode: 'combo'
							},
							callback: function () {
								med_staff_fact_combo.setValue(data.MedStaffFact_id);
							}
						});
					}
				}
			} else {
				org_combo.setValue(data.Org_did||null);
				lpu_section_combo.setValue(data.LpuSection_id||null);
				med_staff_fact_combo.setValue(data.MedStaffFact_id||null);
				fieldDiagPreidName.setValue(data.Diag_preid||null);
				iswd_combo.setValue(me.defineIsWithDirectionValue());
				iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
			}
		}
	},
	checkOtherLpuDirection: function() {
		var base_form = this.getBaseForm();

		if (getRegionNick() == 'perm') {
			var org_id = base_form.findField('Org_did').getValue();
			var date = Ext6.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');

			if (base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code') == 2) {
				if (Ext6.isEmpty(org_id)) {
					base_form.findField('Diag_did').setAllowBlank(true);
					if (getRegionNick() != 'ekb') {
						base_form.findField('EvnDirection_Num').setAllowBlank(true);
						base_form.findField('EvnDirection_setDate').setAllowBlank(true);
					}
				} else {
					this.ownerWin.checkLpuPeriodOMS(org_id, date, function (hasLpuPeriodOMS) {
						base_form.findField('Diag_did').setAllowBlank(!hasLpuPeriodOMS);
						base_form.findField('EvnDirection_Num').setAllowBlank(!hasLpuPeriodOMS);
						base_form.findField('EvnDirection_setDate').setAllowBlank(!hasLpuPeriodOMS);
					});
				}
			}
		}
	},
	openEvnDirectionSelectWindow: function() {
		if ( this.isDisabledChooseDirection ) {
			return false;
		}
		var me = this,
			base_form = me.getBaseForm(),
			person_info = me.ownerWin.ownerWin.PersonInfoPanel;
		// По кнопке “Выбор направления” всегда вызывать форму выбора со скрытым нижним гридом “Записи”
		if ( getWnd('swEvnDirectionSelectWindow').isVisible() ) {
			getWnd('swEvnDirectionSelectWindow').hide();
		}
		getWnd('swEvnDirectionSelectWindow').show({
			callback: function(evnDirectionData) {
				if (evnDirectionData && evnDirectionData.EvnDirection_id){
					// создавать случай со связью с направлением
					me._applyEvnDirectionData(evnDirectionData, true);
				} else {
					// создать случай без связи с направлением
					me._applyEvnDirectionData(null);
				}
			},
			onDate: me.parentSetDateFieldName ? base_form.findField(me.parentSetDateFieldName).getValue() : getGlobalOptions().date,
			useCase: me.useCase,
			MedStaffFact_id: me.medStaffFactFieldName ? base_form.findField(me.medStaffFactFieldName).getValue() : getGlobalOptions().CurMedStaffFact_id,
			Person_Birthday: person_info.getFieldValue('Person_Birthday'),
			Person_Firname: person_info.getFieldValue('Person_Firname'),
			Person_id: base_form.findField(me.personFieldName).getValue(),
			Person_Secname: person_info.getFieldValue('Person_Secname'),
			Person_Surname: person_info.getFieldValue('Person_Surname')
		});
		return true;
	},
	filterLpuSectionOidCombo: function (filter) {
		var me = this;
		var base_form = me.getBaseForm(),
			LpuSectionDidCombo = base_form.findField(me.fieldLpuSectionName),
			LpuSection_did = base_form.findField(me.fieldLpuSectionName).getValue();

		if (!LpuSectionDidCombo.isVisible()) {
			return false;
		}
		if (Ext6.isEmpty(filter)) {
			filter = {};
		}
		filter.onDate = getGlobalOptions().date;
		LpuSectionDidCombo.getStore().clearFilter();
		LpuSectionDidCombo.lastQuery = '';

		var setComboValue = function (combo, id) {
			if (Ext6.isEmpty(id)) {
				return false;
			}

			var index = combo.getStore().findBy(function (rec) {
				return (rec.get('LpuSection_id') == id);
			});

			if (index == -1 && combo.isVisible()) {
				combo.clearValue();
			}
			else {
				combo.setValue(id);
			}

			return true;
		};

		setLpuSectionGlobalStoreFilter(filter);
		LpuSectionDidCombo.getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
		setComboValue(LpuSectionDidCombo, LpuSection_did);
	},
	filterMedStaffFactCombo: function(filter) {
		var me = this;
		var base_form = me.getBaseForm();
		let MedStaffFactDidCombo = base_form.findField(me.fieldMedStaffFactName),
			MedStaffFact_did = base_form.findField(me.fieldMedStaffFactName).getValue();
		let MedStaffFactStore = MedStaffFactDidCombo.getStore();

		if (Ext6.isEmpty(filter)) {
			filter = {};
		}

		if (!Ext6.isEmpty(base_form.findField(me.fieldSetDateName).getValue()) && typeof base_form.findField(me.fieldSetDateName).getValue() == 'object') {
			filter.onDate = base_form.findField(me.fieldSetDateName).getValue().format('d.m.Y');
		}

		let setComboValue = function (combo, id) {
			if (Ext6.isEmpty(id)) {
				return false;
			}

			var index = combo.getStore().findBy(function (rec) {
				return (rec.get('MedStaffFact_id') == id);
			});

			if (index == -1 && combo.isVisible()) {
				combo.clearValue();
			}
			else {
				combo.setValue(id);
			}

			return true;
		};

		MedStaffFactStore.filter('LpuSection_id', filter.LpuSection_id);
		setComboValue(MedStaffFactDidCombo, MedStaffFact_did);
	},
	initComponent: function () {
		var me = this;

		var iswd_combo = {
			xtype: 'checkbox',
			labelWidth: 200,
			labelStyle: 'padding-top:8px;padding-right:23px',
			fieldLabel: langs('С электронным направлением'),
			name: me.fieldIsWithDirectionName,
			listeners:
				{
					'change': function (combo, newValue, oldValue)
					{
						if (false == me.isDisabledChooseDirection) {
							let base_form = me.getBaseForm();

							// запрещаем редактировать, если выбрано эл. направление
							base_form.findField(me.fieldDiagName).setDisabled(newValue);
							base_form.findField(me.fieldSetDateName).setDisabled(newValue);
							base_form.findField(me.fieldNumName).setDisabled(newValue);
							base_form.findField(me.fieldLpuSectionName).setDisabled(newValue);
							base_form.findField(me.fieldMedStaffFactName).setDisabled(newValue);
							base_form.findField(me.fieldOrgName).setDisabled(newValue);
							let prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName);
							prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_combo.getValue());
						}
					},
					'select': function(combo, record, index) {
						combo.enabledSetValue = false;
						combo.fireEvent('change', combo, record.get(combo.valueField));
					}
				}
		};
		me.getIsWithDirectionField = function() { return iswd_combo; };
		Ext6.apply(me, {
			items: [{
				layout: 'column',
				border: false,
				style: 'margin-bottom: 5px',
				anchor:'100%',
				defaults: {
					border: false,
					labelWidth: 172,
					listeners: {
						'change': function () {
							me.saveEvnDirection();
						}
					}
				},
				items: [{
					xtype: 'fieldset',
					title: 'Кем направлен' + '',
					border: false,
					collapsible: true,
					columnWidth: .95,
					defaults: {
						margin: "0px 0px 5px 25px",
						anchor: '90%',
						labelWidth: 220,
						readOnly: false,
						labelStyle: 'padding-top:8px',
					},
					cls: 'personPanel',
					items: [{
						xtype: 'hidden',
						name: me.fieldIdName
					},{
						xtype: 'hidden',
						name: me.fromLpuFieldName
					}, {
						xtype: 'hidden',
						name: me.fieldIsAutoName
					}, {
						xtype: 'hidden',
						name: me.fieldIsExtName
					}, {
						xtype: 'hidden',
						name: me.fieldDiagPreidName
					}, {
						xtype: 'hidden',
						name: me.fieldDiagFName
					}, {
						xtype: 'hidden',
						name: me.personFieldName
					}, {
						xtype: 'hidden',
						name: me.parentSetDateFieldName
					}, {
						border: false,
						layout: 'column',
						default: {
							margin: 0
						},
						items: [iswd_combo,
							{
								style:{
									font: '400 12px/1.4em Roboto;',
									margin: '8px 0 0 30px'
								},
								userCls: 'evn-stick-panel',
								id: me.buttonSelectId,
								html: '<a href="#">Выбрать направление</a>',
								xtype: 'label',
								listeners: {
									click: {
										element: 'el',
										preventDefault: true,
										fn: function (e, target) {
											var el = Ext.fly(target);
											if (el.dom.nodeName === "A") {
												me.openEvnDirectionSelectWindow();
											}
										}
									}
								}
							}]
					}, {
						xtype: 'commonSprCombo',
						fieldLabel: langs('Кем направлен'),
						comboSubject: 'PrehospDirect',
						name: me.fieldPrehospDirectName,
						anchor: '50%',
						listeners: {
							'change': function(combo, newValue, oldValue) {

								let base_form = me.getBaseForm(),
									diag_combo = base_form.findField(me.fieldDiagName),
									evn_direction_id_field = base_form.findField(me.fieldIdName),
									fieldDiagPreidName = base_form.findField(me.fieldDiagPreidName),
									evn_direction_isauto_field = base_form.findField(me.fieldIsAutoName),
									evn_direction_isext_field = base_form.findField(me.fieldIsExtName),
									evn_direction_set_date_field = base_form.findField(me.fieldSetDateName),
									evn_direction_num_field = base_form.findField(me.fieldNumName),
									lpu_section_combo = base_form.findField(me.fieldLpuSectionName),
									med_staff_fact_combo = base_form.findField(me.fieldMedStaffFactName),
									org_combo = base_form.findField(me.fieldOrgName),
									iswd_combo = base_form.findField(me.fieldIsWithDirectionName),
									record = combo.getStore().getById(newValue),
									prehosp_direct_sysnick;

								if ( record ) {
									prehosp_direct_sysnick = record.get('PrehospDirect_SysNick');
								}
								let isDisabledChooseDirection = me.isDisabledChooseDirection;
								let isReadOnly = me.isReadOnly;
								let iswd_value = iswd_combo.getValue();
								let evn_direction_id = evn_direction_id_field.getValue();
								let evn_direction_isauto = evn_direction_isauto_field.getValue();
								let evn_direction_isext = evn_direction_isext_field.getValue();
								let evn_direction_set_date = evn_direction_set_date_field.getValue();
								let evn_direction_num = evn_direction_num_field.getValue();
								let lpu_section_id = lpu_section_combo.getValue();
								let med_staff_fact_id = med_staff_fact_combo.getValue();
								let org_id = org_combo.getValue();
								let diag_id = diag_combo.getValue();

								lpu_section_combo.clearValue();
								med_staff_fact_combo.clearValue();

								me.isDisabledChooseDirection = isDisabledChooseDirection;
								me.isReadOnly = isReadOnly;
								me.queryById(me.buttonSelectId).setDisabled(me.isDisabledChooseDirection);
								iswd_combo.setDisabled(me.isDisabledChooseDirection);

								if ( prehosp_direct_sysnick == null ) {
									diag_combo.disable();
									evn_direction_set_date_field.disable();
									evn_direction_num_field.disable();
									lpu_section_combo.disable();
									med_staff_fact_combo.disable();

									org_combo.disable();
									// созданные регистратором автонаправления не являются направлением от отделения ЛПУ. Для таких направлений "Кем направлен" нужно оставить пустым
									if (evn_direction_id && 1 == iswd_value) {
										evn_direction_id_field.setValue(evn_direction_id);
										evn_direction_isauto_field.setValue(evn_direction_isauto||1);
										evn_direction_isext_field.setValue(evn_direction_isext||1);
										evn_direction_set_date_field.setValue(evn_direction_set_date);
										evn_direction_num_field.setValue(evn_direction_num);
										diag_combo.setValue(diag_id);
										if(diag_id){
											diag_combo.fireEvent('change', diag_combo,diag_id);
										}
										fieldDiagPreidName.setValue(fieldDiagPreidName.getValue());
									}
									return false;
								}
								combo.setValue(newValue);
								if (prehosp_direct_sysnick && prehosp_direct_sysnick.inlist(['lpusection', 'lpu'])) {
									iswd_combo.setValue(iswd_value);
									if (evn_direction_id||evn_direction_num) {
										evn_direction_id_field.setValue(evn_direction_id||null);
										evn_direction_isauto_field.setValue(evn_direction_isauto||1);
										evn_direction_isext_field.setValue(evn_direction_isext||1);
										evn_direction_set_date_field.setValue(evn_direction_set_date);
										evn_direction_num_field.setValue(evn_direction_num);
										diag_combo.setValue(diag_id);
										if(diag_id){
											diag_combo.fireEvent('change', diag_combo,diag_id);
										}
										fieldDiagPreidName.setValue(fieldDiagPreidName.getValue());
									}
								}

								switch ( prehosp_direct_sysnick ) {
									case 'lpusection':
									case 'OtdelMO':
										if ( lpu_section_id ) {
											lpu_section_combo.setValue(lpu_section_id);
										}
										if ( med_staff_fact_id ) {
											med_staff_fact_combo.setValue(med_staff_fact_id);
										}
										evn_direction_set_date_field.setDisabled(me.isReadOnly);
										evn_direction_num_field.setDisabled(me.isReadOnly);
										diag_combo.setDisabled(me.isReadOnly);
										lpu_section_combo.setDisabled(me.isReadOnly);
										lpu_section_combo.setAllowBlank(getRegionNick() == 'kz');
										med_staff_fact_combo.setDisabled(me.isReadOnly);
										if (med_staff_fact_combo.isVisible() && getRegionNick() != 'kz') {
											med_staff_fact_combo.setAllowBlank(false);
										} else {
											med_staff_fact_combo.setAllowBlank(true);
										}
										org_combo.reset();
										if (me.useCase.inlist(['choose_for_evnpl','choose_for_evnpl_stream_input'])) {
											lpu_section_combo.reset();
											med_staff_fact_combo.reset();
											let Lpu_id = getGlobalOptions().lpu_id;
											let filter = {
												Lpu_id: Lpu_id
											};

											if (Lpu_id) {
												base_form.findField(me.fieldLpuSectionName).getStore().load({
													params: {
														Lpu_id: Lpu_id,
														mode: 'combo'
													},
													callback: function(){
														base_form.findField(me.fieldLpuSectionName).setValue(lpu_section_id);
													}
												});
												base_form.findField(me.fieldMedStaffFactName).getStore().load({
													params: {
														Lpu_id: Lpu_id,
														mode: 'combo'
													},
													callback: function(){
														base_form.findField(me.fieldMedStaffFactName).setValue(med_staff_fact_id);
													}
												});
											}

											me.filterLpuSectionOidCombo(filter);
											me.filterMedStaffFactCombo(filter);
										}
										org_combo.setDisabled(true);
										if (getRegionNick() != 'ekb') {
											evn_direction_set_date_field.setAllowBlank(true);
											evn_direction_num_field.setAllowBlank(true);
										}
										diag_combo.setAllowBlank(true);
										break;

									case 'lpu':
									case 'DrMO':
									case 'Pmsp':
										if ( org_id ) {
											org_combo.setValue(org_id);
										}
										evn_direction_set_date_field.setDisabled(me.isReadOnly);
										evn_direction_num_field.setDisabled(me.isReadOnly);
										diag_combo.setDisabled(me.isReadOnly);
										lpu_section_combo.setDisabled(me.isReadOnly);
										med_staff_fact_combo.setDisabled(me.isReadOnly);
										org_combo.setDisabled(me.isReadOnly);
										org_combo.setAllowBlank(getRegionNick() == 'kz');
										if (me.useCase.inlist(['choose_for_evnpl','choose_for_evnpl_stream_input'])) {
											lpu_section_combo.reset();
											med_staff_fact_combo.reset();
											lpu_section_combo.setDisabled(evn_direction_id && 1 == iswd_value);
											lpu_section_combo.setAllowBlank(true);
											med_staff_fact_combo.setDisabled(evn_direction_id && 1 == iswd_value);
											med_staff_fact_combo.setAllowBlank(true);

											base_form.findField(me.fieldLpuSectionName).getStore().removeAll();
											base_form.findField(me.fieldMedStaffFactName).getStore().removeAll();

											if (org_id) {
												base_form.findField(me.fieldLpuSectionName).getStore().load({
													params: {
														Org_id: org_id,
														mode: 'combo'
													},
													callback: function(){
														base_form.findField(me.fieldLpuSectionName).setValue(lpu_section_id);
													}
												});
												base_form.findField(me.fieldMedStaffFactName).getStore().load({
													params: {
														Org_id: org_id,
														andWithoutLpuSection: 3,
														mode: 'combo'
													},
													callback: function(){
														base_form.findField(me.fieldMedStaffFactName).setValue(med_staff_fact_id);
													}
												});
											}
										}

										if (!getRegionNick().inlist([ 'buryatiya' ]) && me.useCase.inlist([
											'choose_for_evnplstom'
											,'choose_for_evnpl'
											,'choose_for_evnpl_stream_input'
											,'choose_for_evnplstom_stream_input'
										]) && !me.isReadOnly) {
											diag_combo.setAllowBlank(false);
											evn_direction_set_date_field.setAllowBlank(false);
											evn_direction_num_field.setAllowBlank(false);

											me.checkOtherLpuDirection();
										}
										break;

									case 'org':
									case 'rvk':
									case 'skor':
									case 'admin':
									case 'Kdp':
									case 'Skor':
									case 'Stac':
									case 'Rvk':
									case 'Rdom':
										if ( org_id ) {
											org_combo.setValue(org_id);
										}
										evn_direction_set_date_field.setDisabled(me.isReadOnly);
										evn_direction_num_field.setDisabled(me.isReadOnly);
										diag_combo.setDisabled(me.isReadOnly);
										lpu_section_combo.setDisabled(true);
										med_staff_fact_combo.setDisabled(true);
										org_combo.setDisabled(me.isReadOnly);
										org_combo.setAllowBlank(true);
										evn_direction_set_date_field.setAllowBlank(true);
										evn_direction_num_field.setAllowBlank(true);
										diag_combo.setAllowBlank(true);
										break;

									default:
										evn_direction_set_date_field.setDisabled(true);
										evn_direction_num_field.setDisabled(true);
										diag_combo.setDisabled(true);
										lpu_section_combo.setDisabled(true);
										med_staff_fact_combo.setDisabled(true);
										org_combo.setDisabled(true);
										break;
								}

								if ( org_combo.getValue() && !org_combo.getStore().getById(org_combo.getValue())) {
									var org_type = '';
									switch ( prehosp_direct_sysnick ) {
										case 'lpu':
										case 'skor':
										case 'DrMO':
										case 'Skor':
											org_type = 'lpu';
											break;
										case 'rvk':
										case 'Rvk':
											org_type = 'military';
											break;
										case 'org':
										case 'admin':
										case 'Pmsp':
										case 'Kdp':
										case 'Stac':
										case 'Rdom':
											org_type = 'org';
											break;
									}
									org_combo.getStore().load({
										callback: function(records, options, success) {
											if ( success ) {
												org_combo.setValue(org_combo.getValue());
											}
										},
										params: {
											Org_id: org_combo.getValue(),
											OrgType: org_type
										}
									});
								}

								if ( diag_combo.getValue() && !diag_combo.getStore().getById(diag_combo.getValue())) {
									diag_combo.getStore().load({
										callback: function() {
											diag_combo.getStore().each(function(record) {
												if ( record.get('Diag_id') == diag_combo.getValue() ) {
													diag_combo.setValue(diag_combo.getValue());
													diag_combo.fireEvent('select', diag_combo, record, 0);
													diag_combo.fireEvent('change', diag_combo,diag_id);
												}
											});
											fieldDiagPreidName.setValue(fieldDiagPreidName.getValue());
										},

										params: {
											where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue()
										}
									});
								}
							},
							'select': function(combo, record, index) {
								combo.fireEvent('change', combo, record.get(combo.valueField));
							}
						}
					}, {
						xtype: 'swOrgCombo',
						displayField: getRegionNick()=='ekb' ? 'Org_Nick' : 'Org_Name',
						editable: false,
						fieldLabel: langs('Организация'),
						name: me.fieldOrgName,
						anchor: '60%',
						listeners: {
							'keydown': function( inp, e ) {
								if ( inp.disabled )
									return true;

								if ( e.F4 == e.getKey() ) {
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.returnValue = false;

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									inp.onTrigger1Click();
									return false;
								}
								return true;
							},
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() ) {
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.returnValue = false;

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									return false;
								}
								return true;
							}
						},
						triggers: {
							picker: {
								hidden: true
							},
							search :{
								handler: function() {
									var base_form = me.getBaseForm();
									var combo = base_form.findField(me.fieldOrgName);
									if ( combo.disabled ) {
										return false;
									}
									var prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName);
									var prehosp_direct_id = prehosp_direct_combo.getValue();
									var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);
									if ( !record ) {
										return false;
									}
									var prehosp_direct_sysnick = record.get('PrehospDirect_SysNick');
									var org_type = '';

									switch (prehosp_direct_sysnick) {
										case 'lpu':
										case 'skor':
										case 'DrMO':
										case 'Skor':
											org_type = 'lpu';break;
										case 'rvk':
										case 'Rvk':
											org_type = 'military';break;
										case 'org':
										case 'admin':
										case 'Pmsp':
										case 'Kdp':
										case 'Stac':
										case 'Rdom':
											org_type = 'org';break;
										default:
											return false;
									}

									getWnd('swOrgSearchWindowExt6').show({
										enableOrgType: false,
										object: org_type,
										//onlyFromDictionary: true,
										onDate: base_form.findField(me.fieldSetDateName).getValue(),
										onSelect: function(org_data) {
											if ( org_data.Org_id > 0 ) {
												combo.getStore().loadData([{
													Org_id: org_data.Org_id,
													Org_Name: org_data.Org_Name,
													Org_Nick: org_data.Org_Nick
												}]);
												combo.setValue(org_data.Org_id);
												if (me.useCase.inlist(['choose_for_evnpl','choose_for_evnpl_stream_input'])) {
													let base_form = me.getBaseForm();

													base_form.findField(me.fieldLpuSectionName).getStore().removeAll();
													base_form.findField(me.fieldMedStaffFactName).getStore().removeAll();

													var lpu_section_id = base_form.findField(me.fieldLpuSectionName).getValue();
													var med_staff_fact_id = base_form.findField(me.fieldMedStaffFactName).getValue();
													base_form.findField(me.fieldLpuSectionName).getStore().clearFilter();
													base_form.findField(me.fieldLpuSectionName).getStore().load({
														params: {
															Org_id: org_data.Org_id,
															mode: 'combo'
														},
														callback: function(result){
															if ( result.length == 0 ) {
																base_form.findField(me.fieldLpuSectionName).disable();
															}
															base_form.findField(me.fieldLpuSectionName).setValue(lpu_section_id);
														}
													});
													base_form.findField(me.fieldMedStaffFactName).getStore().clearFilter();
													base_form.findField(me.fieldMedStaffFactName).getStore().load({
														params: {
															Org_id: org_data.Org_id,
															andWithoutLpuSection: 3,
															mode: 'combo'
														},
														callback: function(result){
															if ( result.length == 0 ) {
																base_form.findField(me.fieldMedStaffFactName).disable();
															}
															base_form.findField(me.fieldMedStaffFactName).setValue(med_staff_fact_id);
														}
													});
												}
												getWnd('swOrgSearchWindowExt6').hide();
												combo.collapse();
											}

											me.checkOtherLpuDirection();
										},
										onClose: function() {combo.focus(true, 200)}
									});
								}
							},
							clear: {
								cls: 'sw-clear-trigger',
								extraCls: 'clear-icon-out', // search-icon-out
								hidden: true
							}
						}
					}, {
						id: me.prefix+'_EDAP_LpuSectionCombo',
						name: me.fieldLpuSectionName,
						anchor: '60%',
						xtype: 'SwLpuSectionGlobalCombo',
						queryMode: 'local',
						linkedElements: [
							me.prefix+'_EDAP_MedPersonalCombo'
						],
						listeners: {
							'change': function(combo, newValue, oldValue) {
								let filter = {
									LpuSection_id: newValue
								};

								me.filterMedStaffFactCombo(filter);
							}
						}
					}, {
						id: me.prefix+'_EDAP_MedPersonalCombo',
						name: me.fieldMedStaffFactName,
						anchor: '60%',
						xtype: 'swMedStaffFactCombo',
						parentElementId: me.prefix+'_EDAP_LpuSectionCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								let base_form = me.getBaseForm();

								if(newValue){

									let fieldDoctorCode = base_form.findField(me.fieldDoctorCode);
									if(fieldDoctorCode.isVisible()){
										let rec = combo.findRecord('MedStaffFact_id', newValue);
										let code = rec.get('MedPersonal_DloCode');
										if (code){
											fieldDoctorCode.setValue(code);
										} else {
											fieldDoctorCode.setValue();
										}
									}
								}
							}
						}
					}, {
						// id: me.prefix+'_EDAP_MedPersonalCode',
						hidden: (getRegionNick() != 'ekb'),
						name: me.fieldDoctorCode,
						xtype: 'hidden'
					},{
						fieldLabel: langs('№ направления'),
						name: me.fieldNumName,
						xtype: 'numberfield',
						anchor:'35%',
					}, {
						fieldLabel: langs('Дата направления'),
						listeners: {
							'change': function(field, newValue, oldValue) {
								blockedDateAfterPersonDeath('personpanelid', me.personPanelId, field, newValue, oldValue);
								me.getBaseForm().findField(me.fieldDiagName).setFilterByDate(newValue);
								me.checkOtherLpuDirection();
							}
						},
						name: me.fieldSetDateName,
						anchor:'40%',
						startDay: 1,
						inputCls: 'date_time_priem',
						format: 'd.m.Y',
						xtype: 'swDateField'
					}, {
						checkAccessRights: true,
						fieldLabel: langs('Диагноз напр. учреждения'),
						name: me.fieldDiagName,
						userCls: 'diagnoz',
						xtype: 'swDiagCombo',
						cls: 'trigger-outside',
						anchor: '60%',
						triggers: {
							search: {
								extraCls: 'search-icon-out',
							}
						}
					}]
				}, {
					xtype: 'label',
					padding: "10px 10px",
					userCls: 'evn-stick-panel',
					name: 'editPerson',
					html: '<a href="#">Сохранить изменения</a>',
					tooltip: langs('Редактировать данные направления'),
					listeners: {
						click: {
							element: 'el',
							preventDefault: true,
							fn: function(e, target){
								var el = Ext.fly(target);
								if(el.dom.nodeName === "A"){
									me.ownerWin.saveEvnPLDirection();
									me.hide();
									me.ownerWin.EvnPLDirectionInfoPanel.show();
								}
							}
						}
					},
					handler: function (){}
				}]
			}],
		});

		me.callParent(arguments);
	}
});

Ext6.define("swEditableDisplayField", {
	border: false,
	cls: 'editable-displayfield',
	alias: "widget.swEditableDisplayField",
	extend: "Ext6.panel.Panel",
	layout: {
		type: 'hbox',
		align: 'strech'
	},
	defaults: {
		border: false
	},
	setValue: function(val){
		this.display.setValue(val);
		this.text.setValue(val);
	},
	onBlurText: Ext6.emptyFn,
	initComponent: function() {
		var me = this;
		this.display = Ext6.create('Ext6.form.field.Display', {
			flex: 1,
			padding: '0 0 0 4',
			style: 'user-select: none;',
			cls: 'packet-prescr-name',
			listeners: {
				afterrender: function(field, eOpts){
					var el = field.el;
					if (el) {
						el.on('click', function(field, event, eOpts){
							me.text.setWidth(me.display.getWidth()+10); // + паддинги
							me.display.hide();
							me.text.show();
							me.text.focus();
						});
					}
				}
			}
		});
		this.text = Ext6.create('Ext6.form.field.Text', {
			flex: 1,
			style: 'user-select: none;',
			hidden: true,
			cls: 'packet-prescr-name',
			listeners: {
				blur: function(field, event, eOpts){
					var val = me.text.getValue();
					me.display.setValue(val);
					me.onBlurText(val);
					me.text.hide();
					me.display.show();
				}
			}
		});

		if(this.value){
			this.display.setValue(this.value);
			this.text.setValue(this.value);
		}

		Ext6.apply(me, {
			defaults:{
				border: false
			},
			items: [
				{
					width: 20,
					iconCls: 'packet-name-icon'
				},
				me.text,
				me.display
			]
		});

		me.callParent(arguments);
	}
});
