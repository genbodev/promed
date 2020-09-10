/**
* swScheduleEditMasterWindow - мастер редактирования расписания во всех объектах ЛПУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      19.03.2012
*/

/*NO PARSE JSON*/
sw.Promed.swScheduleEditMasterWindow = Ext.extend(sw.Promed.BaseForm,
{
	/**
	 * Настройки для отладки
	 */
	codeRefresh: true,
	objectName: 'swScheduleEditMasterWindow',
	objectSrc: '/jscore/Forms/Reg/swScheduleEditMasterWindow.js',

	/**
	 * Панель редактирования расписания врача
	 */
	TTGScheduleEditPanel: null,
	/**
	 * Панель редактирования расписания стационара
	 */
	TTSScheduleEditPanel: null,
	/**
	 * Панель редактирования расписания службы, услуги
	 */
	TTMSScheduleEditPanel: null,
	/**
	 * Дерево структуры ЛПУ
	 */
	StructureTree: null,
	/**
	 * Список медперсонала
	 */
	MedPersonalGrid: null,
	/**
	 * Список услуг службы
	 */
	UslugaComplexGrid: null,
	/**
	 * Список ресурсов службы
	 */
	ResourceMedServiceGrid: null,
	/**
	 * Табпанель на которой находятся панели редактирования расписания
	 */
	ScheduleTabPanel: null,
	/**
	 * Табпанель для выбора или списка врачей или списка услуг
	 */
	MP_UCTabPanel: null,
	
	/**
	 * Параметры
	 */
	params: {
		Lpu_Nick: null, // Название ЛПУ, с которым идет работа
		Lpu_id: null // Идентификатор ЛПУ, с которым идет работа
	},
	
	/**
	 * Настройки окна
	 */
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	height: 550,
	id: 'ScheduleEditMasterWindow',
	layout: 'border',
	maximizable: true,
	maximized: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	resizable: true,
	title: WND_REG_EDITSCHEDULE,
	width: 900,
	
	/**
	 * Смена ЛПУ, с расписанием которой мы работаем
	 */
	changeLpu: function(Lpu_id, Lpu_Nick) {
		this.params.Lpu_id = Lpu_id;
		this.params.Lpu_Nick = Lpu_Nick;
		
		this.StructureTree.fireEvent('beforeload', this.StructureTree.root);
		this.StructureTree.getLoader().baseParams.Lpu_id = this.params.Lpu_id;
		if (getRegionNick().inlist(['vologda','msk', 'ufa'])) {
			this.StructureTree.getLoader().baseParams.filterByArm = this.params.fromArm;
			if (getWnd('swMPWorkPlaceStacWindow').isVisible()) {
				this.StructureTree.getLoader().baseParams.UserMedStaffFact_id = this.params.UserMedStaffFact_id;
			}
			if (getWnd('swMPWorkPlacePriemWindow').isVisible()) {
				this.StructureTree.getLoader().baseParams.UserLpuSection_id = this.params.UserLpuSection_id;
			}
		}
		this.StructureTree.getLoader().load(
			this.StructureTree.root,
			function (tl,root) {
				this.StructureTree.setTitle(lang['lpu'] + this.params.Lpu_Nick);
				root.setText(this.params.Lpu_Nick);
				root.expand(false,false,function(node){
					if(node.childNodes.length == 1) {
						node.childNodes[0].select();
						this.StructureTree.onSelectNode(node.childNodes[0]);
						node.childNodes[0].expand();
					} else {
						node.select();
						this.StructureTree.onSelectNode(node);
					}
				}.createDelegate(this));
			}.createDelegate(this)
		);
	},

	loadResourceMedServiceGrid: function() {
		var params = {MedService_id: this.params.MedService_id};

		params.Resource_begDate = this.TTRScheduleEditPanel.date;

		this.ResourceMedServiceGrid.getStore().load({
			params: params,
			callback: function() {
				if (this.ResourceMedServiceGrid.getStore().getCount() > 0) {
					var index = this.ResourceMedServiceGrid.getStore().findBy(function(rec) { return rec.get('Resource_id') == this.TTRScheduleEditPanel.Resource_id; }.createDelegate(this));
					if (index >= 0) {
						this.ResourceMedServiceGrid.getSelectionModel().selectRow(index);
					} else {
						this.ResourceMedServiceGrid.getSelectionModel().selectFirstRow();
					}
				} else {
					this.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / 0';

					this.TTMSScheduleEditPanel.Resource_id = null;
					this.TTMSScheduleEditPanel.MedService_id = this.params.MedService_id;
					this.TTMSScheduleEditPanel.UslugaComplexMedService_id = null;

					this.TTRScheduleEditPanel.doResetAnnotationDate(this.TTRScheduleEditPanel.calendar.value, false);
					this.TTRScheduleEditPanel.loadSchedule(this.TTRScheduleEditPanel.calendar.value);
				}
			}.createDelegate(this)
		});
	},
	
	filterMsfGrid: function() {
		
		var win = this,
			grid = this.MedPersonalGrid,
			field = this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter'),
			exp = field.getValue(),
			rx = new RegExp(exp, "i");
		grid.getStore().filterBy(function(rec) {
			return 	rx.test(rec.get('MedPersonal_FIO')) && 
					(rec.get('isClosed') == grid.gFilters.isClosed || grid.gFilters.isClosed == null)
		});
		grid.getTopToolbar().items.items[4].el.innerHTML = '0 / ' + grid.getStore().getCount();
		
	},
	
	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this;
		// По умолчанию выставляем ЛПУ из глобальных настроек
		// log(Ext.globalOptions.globals);
		this.params.Lpu_Nick = Ext.globalOptions.globals.lpu_nick;
		this.params.Lpu_id = Ext.globalOptions.globals.lpu_id;
		
		this.TTGScheduleEditPanel = new sw.Promed.swTTGScheduleEditPanel({
			id:'TTGScheduleEdit',
			frame: false,
			border: false,
			region: 'center',
			hidden: true
		});
		
		this.TTSScheduleEditPanel = new sw.Promed.swTTSScheduleEditPanel({
			id:'TTSScheduleEdit',
			frame: false,
			border: false,
			region: 'center',
			hidden: true
		});
		
		this.TTMSScheduleEditPanel = new sw.Promed.swTTMSScheduleEditPanel({
			id:'TTMSScheduleEdit',
			frame: false,
			border: false,
			region: 'center',
			hidden: true
		});

		this.TTRScheduleEditPanel = new sw.Promed.swTTRScheduleEditPanel({
			id:'TTRScheduleEdit',
			frame: false,
			border: false,
			region: 'center',
			hidden: true,
			onDateChange: function() {
				this.loadResourceMedServiceGrid();
			}.createDelegate(this)
		});
		
		this.SelectLpuCombo = new sw.Promed.SwLpuLocalCombo({
			allowBlank: false,
			anchor : "98%",
			editable : true,
			forceSelection: true,
			id : 'SEMW_Lpu_id',
			tabIndex: TABINDEX_SEMW + 1,
			lastQuery : '',
			listeners: {
				'blur': function(combo) {
					if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
						combo.clearValue();
					}
				},
				'keydown': function (inp, e) {
					if (e.shiftKey == false && e.getKey() == Ext.EventObject.ENTER)
					{
						e.stopEvent();
					}
				}.createDelegate(this),
				'select':function(combo, record, index) {
					this.changeLpu(record.get('Lpu_id'), record.get('Lpu_Nick'));
				}.createDelegate(this),
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			},
			fieldLabel: lang['lpu'],
			typeAhead: false
		});
		
		this.StructureTree = new Ext.tree.TreePanel({
			region: 'center',
			height: 200,
			autoScroll: true,
			id: 'SEMW_StructureTree',
			tabIndex: TABINDEX_SEMW + 2,
			/**
			 * Событие при выборе ноды
			 */
			onSelectNode: function(node) {

				var object = node.attributes.object;
				var object_id = node.attributes.object_id;

				var params = new Object();
				if (object) {
					params[object+'_id'] = object_id;
					params['LpuUnitType_SysNick'] = node.attributes.LpuUnitType_SysNick;
					this.params[object+'_id'] = object_id;
				}
				
				params['Lpu_id'] = this.params.Lpu_id;
				
				this.MedPersonalGrid.getStore().removeAll();
				this.UslugaComplexGrid.getStore().removeAll();
				this.ResourceMedServiceGrid.getStore().removeAll();

				if (node.attributes.LpuUnitType_id) { // если есть атрибут типа подразделения
					
					if (node.attributes.LpuUnitType_id == 2) { // для поликлиники
						this.MP_UCTabPanel.layout.setActiveItem(0);
						
						if (!this.params.MedServiceOnly) {
							this.MedPersonalGrid.getStore().load({
								params: params,
								callback: function() {
									this.filterMsfGrid();
								}.createDelegate(this)
							});
						}
					} else if (node.attributes.LpuUnitType_id == 12) { // для ФАП

						if (!this.params.MedServiceOnly) {
							this.MedPersonalGrid.getStore().load({
								params: params,
								callback: function() {
									this.filterMsfGrid();
								}.createDelegate(this)
							});
						}
					} else { // для стационаров
						if (node.attributes.LpuUnitType_id.inlist([1,6,7,9]) ){
							if (object == 'LpuSection' || object == 'LpuSectionPid') {
								this.ScheduleTabPanel.layout.setActiveItem('TTSScheduleEdit');
								this.TTSScheduleEditPanel.show();
								this.TTSScheduleEditPanel.doLayout();
								this.TTSScheduleEditPanel.LpuSection_id = object_id;
								this.TTSScheduleEditPanel.loadSchedule(this.TTSScheduleEditPanel.calendar.value);
								this.setTitle(WND_REG_EDITSCHEDULE + lang['|_otdelenie'] + node.text);
							}
							
						}
					}
				} else { // если атрибута типа подразделения нет
					if (object == 'MedService') { // службы
						if (node.attributes.MedServiceType_SysNick.inlist(['func'])) { //Службы с ресурсами
							this.MP_UCTabPanel.layout.setActiveItem(2);
							this.loadResourceMedServiceGrid();
						} else {
							this.ScheduleTabPanel.layout.setActiveItem('TTMSScheduleEdit');
							this.TTMSScheduleEditPanel.setReadOnly(false);
							this.TTMSScheduleEditPanel.show();
							this.TTMSScheduleEditPanel.doLayout();
							this.TTMSScheduleEditPanel.MedService_id = object_id;
							this.TTMSScheduleEditPanel.UslugaComplexMedService_id = null;
							this.TTMSScheduleEditPanel.loadSchedule(this.TTMSScheduleEditPanel.calendar.value);
							this.setTitle(WND_REG_EDITSCHEDULE + lang['|_slujba'] + node.text);

							if (!node.attributes.MedServiceType_SysNick.inlist(['osmotrgosp'])) {
								this.MP_UCTabPanel.layout.setActiveItem(1);
								this.UslugaComplexGrid.getStore().load({
									params: params
								});
							}
						}
					} else {
						if (object != 'MedServices') { // не корень служб
							// значит корень дерева
							this.MP_UCTabPanel.layout.setActiveItem(0);
							
							if (!this.params.MedServiceOnly) {
								this.MedPersonalGrid.getStore().load({
									params: params,
									callback: function() {
										this.filterMsfGrid();
									}.createDelegate(this)
								});
							}
						}
					}
					
				}
				
				
				
			}.createDelegate(this),
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					var node = this.StructureTree.getSelectionModel().selNode;
					if ( node.id == 'root' )
					{
						if ( node.isExpanded() )
							node.collapse();
						else
							node.expand();
						return;
					}
					if ( node.isExpandable() )
					{
						if ( node.isExpanded() )
							node.collapse();
						else
							node.expand();
					}
					
					this.StructureTree.onSelectNode(node);
				}.createDelegate(this),
				stopEvent: true
			}, {
				key: Ext.EventObject.TAB,
				stopEvent: true,
				shift: false,
				fn: function() {
					this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter').focus();
				}.createDelegate(this)
			},
			{
				key: Ext.EventObject.TAB,
				stopEvent: true,
				shift: true,
				fn: function() {
					this.SelectLpuCombo.focus();
				}.createDelegate(this)
			}
			],
			root: {
				id: 'root',
				text: this.params.Lpu_Nick
			},
			title: lang['lpu'] + this.params.Lpu_Nick,
			enableKeyEvents: true,
			listeners: {
				'beforeload': function( node ) {
					if(!this.SelectLpuCombo.getValue()) {
						//отмена загрузки при инициализации
						return false;
					}
					
					this.StructureTree.getLoader().baseParams = {};
					var object = node.attributes.object;
					var object_id = node.attributes.object_id;
					if ( object != undefined )
					{
						this.StructureTree.getLoader().baseParams.object = object;
					}
					if ( object_id != undefined )
					{
						this.StructureTree.getLoader().baseParams.object_id = object_id;
					}
					this.StructureTree.getLoader().baseParams.Lpu_id = this.params.Lpu_id;
					this.StructureTree.getLoader().baseParams.LpuUnit_id = this.params.LpuUnit_id;
					
					this.StructureTree.getLoader().baseParams.LpuUnitType_id = node.attributes.LpuUnitType_id;
					if (getRegionNick().inlist(['vologda','msk', 'ufa'])) {
						this.StructureTree.getLoader().baseParams.filterByArm = this.params.fromArm;
						if (getWnd('swMPWorkPlaceStacWindow').isVisible()) {
							this.StructureTree.getLoader().baseParams.UserMedStaffFact_id = this.params.UserMedStaffFact_id;
						}
						if (getWnd('swMPWorkPlacePriemWindow').isVisible()) {
							this.StructureTree.getLoader().baseParams.UserLpuSection_id = this.params.UserLpuSection_id;
						}
					}
					
					if (!!this.params.MedServiceOnly) {
						this.StructureTree.getLoader().baseParams.LpuSection_id = this.params.LpuSection_id;
						this.StructureTree.getLoader().baseParams.MedServiceType_SysNick = this.params.MedServiceType_SysNick;

						this.StructureTree.getLoader().baseParams.MedServiceOnly = this.params.MedServiceOnly;
					}
				}.createDelegate(this),
				'beforeclick': function(node) {
					this.StructureTree.onSelectNode(node);
				}.createDelegate(this)
			},
			loader: new Ext.tree.TreeLoader({
				url: '/?c=Reg&m=GetFilterTree'
			})
		});
		
		
		this.msfMenuIsCloseFilter = new Ext.menu.Menu({
			items: [
				new Ext.Action({
					text: lang['otkryityie'],
					handler: function() {
						var grid = win.MedPersonalGrid;
						if (grid.gFilters) {
							grid.gFilters.isClosed = 0;
						}
						grid.getTopToolbar().items.items[0].setText('Показывать: <b>Открытые</b>');
						win.filterMsfGrid();
					}
				}),
				new Ext.Action({
					text: lang['zakryityie'],
					handler: function() {
						var grid = win.MedPersonalGrid;
						if (grid.gFilters) {
							grid.gFilters.isClosed = 1;
						}
						grid.getTopToolbar().items.items[0].setText('Показывать: <b>Закрытые</b>');
						win.filterMsfGrid();
					}
				}),
				new Ext.Action({
					text: lang['vse'],
					handler: function() {
						var grid = win.MedPersonalGrid;
						if (grid.gFilters) {
							grid.gFilters.isClosed = null;
						}
						grid.getTopToolbar().items.items[0].setText('Показывать: <b>Все</b>');
						win.filterMsfGrid();
					}
				})
			]
		});
		
		this.MedPersonalGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: false,
			region: 'center',
			width: 365,
			split: true,
			header: false,
			id: 'SEMW_MedStaffFactGrid',
			tabIndex: TABINDEX_SEMW + 4,
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			gFilters: {
				isClosed: null
			},
			keys: [{
				key: [
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{
						
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter').focus();
							} else {
								this.buttons[this.buttons.length - 2].focus(true);
							}
						break;
					}
				}.createDelegate(this),
				stopEvent: true
			}],
			store: new Ext.data.JsonStore({
				autoLoad: false,
				url: '/?c=Reg&m=getMedStaffFactListForSchedule',
				fields: [
					'MedStaffFact_id',
					'MedPersonal_FIO',
					'isClosed'
				],
				listeners: {
					'load': function(store) {
						var field = this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter');
						var exp = field.getValue();
						if (exp != "") {
							this.MedPersonalGrid.getStore().filter('MedPersonal_FIO', new RegExp(exp, "i"));
						}
						this.MedPersonalGrid.getTopToolbar().items.items[4].el.innerHTML = '0 / ' + store.getCount();
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'MedStaffFact_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: lang['fio_vracha'], dataIndex: 'MedPersonal_FIO', sortable: true},
				{dataIndex: 'isClosed', hidden: true}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
					items: [{
					isClose: 0,
					name: 'action_isclosefilter',
					text: 'Показывать: <b>Открытые</b>',
					menu: this.msfMenuIsCloseFilter
				}, {
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'FIOFilter',
					tabIndex: TABINDEX_SEMW + 3,
					width: 100,
					style: 'margin-left: 5px',
					enableKeyEvents: true,
					listeners: {
						'keyup': function(field, e) {
							if (tm) {
                                clearTimeout(tm)
                            } else {
                                var tm = null;
                            }
							tm = setTimeout(function () {
									var field = this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter');
									var exp = field.getValue();
									this.filterMsfGrid();
									this.MedPersonalGrid.getTopToolbar().items.items[4].el.innerHTML = '0 / ' + this.MedPersonalGrid.getStore().getCount();
									field.focus();
								}.createDelegate(this),
								100
							);
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.TAB )
							{
								e.stopEvent();
								if (e.shiftKey == false) {
									if ( this.MedPersonalGrid.getStore().getCount() > 0 )
									{
										this.MedPersonalGrid.getView().focusRow(0);
										this.MedPersonalGrid.getSelectionModel().selectFirstRow();
									}
								} else {
									this.StructureTree.focus();
								}
							}
						}.createDelegate(this)
					}
				},
				{
					xtype: 'tbfill'
				}, {
					text: '0 / 0',
					xtype: 'tbtext'
				}]
			}),
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					'rowselect': function(sm, rowIdx, r) {
						
						this.MedPersonalGrid.getTopToolbar().items.items[4].el.innerHTML = (rowIdx + 1) + ' / ' + this.MedPersonalGrid.getStore().getCount();
						
						this.ScheduleTabPanel.layout.setActiveItem('TTGScheduleEdit');
						this.TTGScheduleEditPanel.show();
						this.TTGScheduleEditPanel.doLayout();
						this.TTGScheduleEditPanel.MedStaffFact_id = r.data.MedStaffFact_id;
						this.TTGScheduleEditPanel.doResetAnnotationDate(this.TTGScheduleEditPanel.calendar.value, false);
						this.TTGScheduleEditPanel.loadSchedule(this.TTGScheduleEditPanel.calendar.value);
						this.setTitle(WND_REG_EDITSCHEDULE + lang['|_vrach'] + r.data.MedPersonal_FIO.replace("<br/>", " / "));
					}.createDelegate(this)
				}
			})
		});
		
		
		this.UslugaComplexGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: false,
			region: 'south',
			width: 365,
			height: 200,
			split: true,
			header: false,
			id: 'SEMW_UslugaComplexGrid',
			tabIndex: TABINDEX_SEMW + 6,
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			keys: [{
				key: [
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								this.UslugaComplexGrid.getTopToolbar().items.item('UslugaFilter').focus();
							} else {
								this.buttons[this.buttons.length - 2].focus(true);
							}
						break;
					}
				}.createDelegate(this),
				stopEvent: true
			}],
			store: new Ext.data.JsonStore({
				autoLoad: false,
				url: '/?c=Reg&m=getUslugaComplexListForSchedule',
				fields: [
					'UslugaComplexMedService_id',
					'UslugaComplex_Name'
				],
				listeners: {
					'load': function(store) {
						var field = this.UslugaComplexGrid.getTopToolbar().items.item('UslugaFilter');
						var exp = field.getValue();
						if (exp != "") {
							this.UslugaComplexGrid.getStore().filter('UslugaComplex_Name', new RegExp(exp, "i"));
						}
						this.UslugaComplexGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'UslugaComplexMedService_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: lang['usluga'], dataIndex: 'UslugaComplex_Name', sortable: true}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
				items: [{
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'UslugaFilter',
					tabIndex: TABINDEX_SEMW + 5,
					style: 'margin-left: 5px',
					enableKeyEvents: true,
					listeners: {
						'keyup': function(field, e) {
							if (tm) {
								clearTimeout(tm);
							} else {
								var tm = null;
							}
							tm = setTimeout(function () {
									var field = this.UslugaComplexGrid.getTopToolbar().items.item('UslugaFilter');
									var exp = field.getValue();
									this.UslugaComplexGrid.getStore().filter('UslugaComplex_Name', new RegExp(exp, "i"));
									this.UslugaComplexGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + this.UslugaComplexGrid.getStore().getCount();
									field.focus();
								}.createDelegate(this),
								100
							);
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.TAB )
							{
								e.stopEvent();
								if  (e.shiftKey == false) {
									if ( this.UslugaComplexGrid.getStore().getCount() > 0 )
									{
										this.UslugaComplexGrid.getView().focusRow(0);
										this.UslugaComplexGrid.getSelectionModel().selectFirstRow();
									}
								} else {
									this.StructureTree.focus();
								}
							}
						}.createDelegate(this)
					}
				},
				{
					xtype: 'tbfill'
				}, {
					text: '0 / 0',
					xtype: 'tbtext'
				}]
			}),
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					'rowselect': function(sm, rowIdx, r) {
						
						this.UslugaComplexGrid.getTopToolbar().items.items[3].el.innerHTML = (rowIdx + 1) + ' / ' + this.UslugaComplexGrid.getStore().getCount();
						
						this.ScheduleTabPanel.layout.setActiveItem('TTMSScheduleEdit');
						this.TTMSScheduleEditPanel.show();
						this.TTMSScheduleEditPanel.doLayout();
						this.TTMSScheduleEditPanel.MedService_id = null;
						this.TTMSScheduleEditPanel.UslugaComplexMedService_id = r.data.UslugaComplexMedService_id;
						this.TTMSScheduleEditPanel.loadSchedule(this.TTMSScheduleEditPanel.calendar.value);
						this.setTitle(WND_REG_EDITSCHEDULE + lang['|_usluga'] + r.data.UslugaComplex_Name);
					}.createDelegate(this)
				}
			})
		});

		this.ResourceMedServiceGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: false,
			region: 'south',
			width: 365,
			height: 200,
			split: true,
			header: false,
			id: 'SEMW_ResourceMedServiceGrid',
			tabIndex: TABINDEX_SEMW + 6,
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			keys: [{
				key: [
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								this.ResourceMedServiceGrid.getTopToolbar().items.item('ResurceFilter').focus();
							} else {
								this.buttons[this.buttons.length - 2].focus(true);
							}
						break;
					}
				}.createDelegate(this),
				stopEvent: true
			}],
			store: new Ext.data.JsonStore({
				autoLoad: false,
				url: '/?c=Reg&m=getResourceListForSchedule',
				fields: [
					'Resource_id',
					'Resource_Name'
				],
				listeners: {
					'load': function(store) {
						var field = this.ResourceMedServiceGrid.getTopToolbar().items.item('ResourceFilter');
						var exp = field.getValue();
						if (exp != "") {
							this.ResourceMedServiceGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
						}
						this.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'Resource_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: lang['resurs'], dataIndex: 'Resource_Name', sortable: true}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
				items: [{
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'ResourceFilter',
					tabIndex: TABINDEX_SEMW + 5,
					style: 'margin-left: 5px',
					enableKeyEvents: true,
					listeners: {
						'keyup': function(field, e) {
							if (tm) {
								clearTimeout(tm);
							} else {
								var tm = null;
							}
							tm = setTimeout(function () {
									var field = this.ResourceMedServiceGrid.getTopToolbar().items.item('ResourceFilter');
									var exp = field.getValue();
									this.ResourceMedServiceGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
									this.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + this.ResourceMedServiceGrid.getStore().getCount();
									field.focus();
								}.createDelegate(this),
								100
							);
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.TAB )
							{
								e.stopEvent();
								if  (e.shiftKey == false) {
									if ( this.ResourceMedServiceGrid.getStore().getCount() > 0 )
									{
										this.ResourceMedServiceGrid.getView().focusRow(0);
										this.ResourceMedServiceGrid.getSelectionModel().selectFirstRow();
									}
								} else {
									this.StructureTree.focus();
								}
							}
						}.createDelegate(this)
					}
				},
				{
					xtype: 'tbfill'
				}, {
					text: '0 / 0',
					xtype: 'tbtext'
				}]
			}),
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					'rowselect': function(sm, rowIdx, r) {

						this.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = (rowIdx + 1) + ' / ' + this.ResourceMedServiceGrid.getStore().getCount();

						this.ScheduleTabPanel.layout.setActiveItem('TTRScheduleEdit');
						this.TTRScheduleEditPanel.show();
						this.TTRScheduleEditPanel.doLayout();
						this.TTRScheduleEditPanel.MedService_id = null;
						this.TTRScheduleEditPanel.Resource_id = r.data.Resource_id;
						this.TTRScheduleEditPanel.doResetAnnotationDate(this.TTRScheduleEditPanel.calendar.value, false);
						this.TTRScheduleEditPanel.loadSchedule(this.TTRScheduleEditPanel.calendar.value);
						this.setTitle(WND_REG_EDITSCHEDULE + lang['|_resurs'] + r.data.Resource_Name);
					}.createDelegate(this)
				}
			})
		});
		
		this.ScheduleTabPanel = new Ext.Panel({
				region: 'center',
				id: 'ScheduleEditPanel',
				layout:'card',
				items: [
					this.TTGScheduleEditPanel,
					this.TTSScheduleEditPanel,
					this.TTMSScheduleEditPanel,
					this.TTRScheduleEditPanel
				]
		});
		
		this.MP_UCTabPanel = new Ext.Panel({
				region: 'center',
				id: 'MP_UCTabPanel',
				layout:'card',
				items: [
					this.MedPersonalGrid,
					this.UslugaComplexGrid,
					this.ResourceMedServiceGrid,
				]
		});
		
		Ext.apply(this, {
			buttons: [
				new Ext.Button({
					text: BTN_FRMHELP,
					iconCls: 'help16',
					id: 'SEMW_HelpButton',
					tabIndex: TABINDEX_SEMW + 10,
					handler: function(button, event) {
						ShowHelp(WND_REG_EDITSCHEDULE);
					}.createDelegate(this)
				}),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					id: 'SEMW_CancelButton',
					tabIndex: TABINDEX_SEMW + 11,
					text: BTN_FRMCLOSE
				}
			],
			items: [
				new Ext.Panel({
						region: 'west',
						width: 350,
						layout: 'border',
						split: true,
						items: [
							{
								region: 'north',
								height: 240,
								layout: 'border',
								border: false,
								frame: false,
								split: true,
								items: [
									{
										region: 'north',
										height: 25,
										layout: 'form',
										border: false,
										frame: false,
										labelWidth : 30,
										style : 'padding-left: 3px;padding-top: 3px;',
										items: [
											this.SelectLpuCombo
										],
										hidden: !isCallCenterAdmin()
									},
									this.StructureTree
								]
							},
							this.MP_UCTabPanel
						]
				}),
				this.ScheduleTabPanel
			]
		});
		sw.Promed.swScheduleEditMasterWindow.superclass.initComponent.apply(this, arguments);
	},

	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swScheduleEditMasterWindow.superclass.show.apply(this, arguments);
		
		if(!arguments[0]) {
			arguments = [{}];
		}
	
		this.params = {};	
		this.params.Lpu_id = arguments[0].Lpu_id || Ext.globalOptions.globals.lpu_id;
		this.params.Lpu_Nick = arguments[0].Lpu_Nick || Ext.globalOptions.globals.lpu_nick;
		this.params.MedService_id = arguments[0].MedService_id || null;
		this.params.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick || null;
		this.params.LpuUnit_id = arguments[0].LpuUnit_id || null;
		this.params.LpuUnitType_id = arguments[0].LpuUnitType_id || null;
		this.params.LpuSection_id = arguments[0].LpuSection_id || null;
		this.params.MedPersonal_id = arguments[0].MedPersonal_id || null;
		this.params.UserLpuSection_id = arguments[0].UserLpuSection_id || null;
		this.params.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id || null;
		this.params.fromArm = arguments[0].fromArm || null;

		this.params.MedServiceOnly = arguments[0].MedServiceOnly || null;
		this.SelectLpuCombo.setValue(this.params.Lpu_id);
		this.SelectLpuCombo.setDisabled(false);
		
		var grid = this.MedPersonalGrid;
		if (grid.gFilters) {grid.gFilters.isClosed = 0;}
		grid.getTopToolbar().items.items[0].setText('Показывать: <b>Открытые</b>');
		
		this.changeLpu(this.params.Lpu_id, this.params.Lpu_Nick);
		if(this.params.LpuUnit_id) {
			this.SelectLpuCombo.setDisabled(true);
		}
	}
});

