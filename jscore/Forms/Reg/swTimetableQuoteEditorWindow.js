/**
* swTimetableQuoteEditorWindow - редактор квот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      12.11.2013
*/

/*NO PARSE JSON*/



sw.Promed.swTimetableQuoteEditorWindow = Ext.extend(sw.Promed.BaseForm,
{
	/**
	 * Настройки для отладки
	 */
	codeRefresh: true,
	objectName: 'swTimetableQuoteEditorWindow',
	objectSrc: '/jscore/Forms/Reg/swTimetableQuoteEditorWindow.js',
	/**
	 * Дерево структуры ЛПУ
	 */
	StructureTree: null,
	/**
	 * Список медперсонала
	 */
	MedPersonalGrid: null,
	/**
	 * Список ресурсов
	 */
	ResourceGrid: null,
	
	/**
	 * Табпанель для выбора списка профилей или списка врачей
	 */
	MP_LSPTabPanel: null,
	
	/**
	 * Параметры
	 */
	params: {
		Lpu_Nick: null, // Название ЛПУ, с которым идет работа
		Lpu_id: null // Идентификатор ЛПУ, с которым идет работа
	},
	
	/**
	 * Выбранное подразделение
	 */
	LpuUnit_id: null,
	
	/**
	 * Выбранное отделение
	 */
	LpuSection_id: null,
	
	/**
	 * Выбранный врач
	 */
	MedStaffFact_id: null,

	/**
	 * Выбранный ресурс
	 */
	Resource_id: null,

	/**
	 * Выбраннная служба
	 */
	MedService_id: null,
	
	/**
	 * Настройки окна
	 */
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	height: 550,
	id: 'TimetableQuoteEditorWindow',
	layout: 'border',
	maximizable: true,
	maximized: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	resizable: true,
	title: WND_REG_QUOTEEDITOR,
	width: 900,
	
	/**
	 * Смена ЛПУ, с квотами которой мы работаем
	 */
	changeLpu: function(Lpu_id, Lpu_Nick) {
		this.params.Lpu_id = Lpu_id;
		this.params.Lpu_Nick = Lpu_Nick;
		
		this.StructureTree.fireEvent('beforeload', this.StructureTree.root);
		this.StructureTree.getLoader().baseParams.Lpu_id = this.params.Lpu_id;
		this.StructureTree.getLoader().load(
			this.StructureTree.root,
			function (tl,root) {
				this.StructureTree.setTitle(lang['mo'] + ' ' + this.params.Lpu_Nick);
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
	
	/**
	 * Получение типа пользователя, работающего с окном
	 * lpu_oper - оператор ЛПУ
	 * lpu_admin - администратор ЛПУ
	 * cz_oper - оператор ЦЗ
	 * cz_admin - администратор ЦЗ
	 */
	getRole: function() {
		return 'lpu_admin';
	},
	
	/**
	 * Применить фильтры
	 */
	applyFilters: function(additional_filters){
		var params = $.extend(this.FiltersPanel.getFilters(), additional_filters ? additional_filters : {});
			
		//this.QuotesGrid.getGrid().getStore().baseParams = {};
		this.QuotesGrid.loadData({
			globalFilters: params
		});
	},
	
	/**
	 * Конструктор
	 */
	initComponent: function() {
		// По умолчанию выставляем ЛПУ из глобальных настроек
		// log(Ext.globalOptions.globals);
		this.params.Lpu_Nick = Ext.globalOptions.globals.lpu_nick;
		this.params.Lpu_id = Ext.globalOptions.globals.lpu_id;
		
		this.SelectLpuCombo = new sw.Promed.SwLpuLocalCombo({
			allowBlank: false,
			anchor : "98%",
			editable : true,
			forceSelection: true,
			id : 'QEW_Lpu_id',
			tabIndex: TABINDEX_TQE + 1,
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
				}.createDelegate(this)
			},
			fieldLabel: lang['lpu'],
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{[(values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыта " + Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate.slice(0,10), "Y-m-d"), "d.m.Y") + ")" : values.Lpu_Nick ]}&nbsp;',
				'</div></tpl>'
			),
			typeAhead: false
		});
		
		this.StructureTree = new Ext.tree.TreePanel({
			region: 'center',
			height: 200,
			autoScroll: true,
			id: 'QEW_StructureTree',
			tabIndex: TABINDEX_TQE + 2,
			/**
			 * Событие при выборе ноды
			 */
			onSelectNode: function(node) {
				log('onSelectNode', node);
				
				var object = node.attributes.object;
				var object_id = node.attributes.object_id;

				var params = new Object();
				if (object) {
					params[object+'_id'] = object_id;
					this.params[object+'_id'] = object_id;
				}
				
				params['Lpu_id'] = this.params.Lpu_id;
				
				this.QuotesGrid.getGrid().getStore().baseParams = {}

				this.MedPersonalGrid.getStore().removeAll();
				this.LpuSectionProfilesGrid.getStore().removeAll();
				this.ResourceGrid.getStore().removeAll();

				if (node == this.StructureTree.root) {
					this.MedPersonalGrid.getStore().load({
						params: params
					});
					this.LpuSectionProfilesGrid.getStore().load({
						params: params
					});
					this.ResourceGrid.getStore().load({
						params: params
					});
					this.applyFilters();

					this.LpuUnit_id = null;
					this.LpuSectionProfile_id = null;
					this.LpuSection_id = null;
					this.MedStaffFact_id = null;
					this.MedService_id = null;
					this.Resource_id = null;
				} else {
					if (node.attributes.object == 'LpuUnit') {
						this.applyFilters({
							'LpuUnit_id': node.attributes.object_id
						});
						this.LpuUnit_id = node.attributes.object_id;
						this.LpuSectionProfile_id = null;
						this.LpuSection_id = null;
						this.MedStaffFact_id = null;
						this.MedService_id = null;
						this.Resource_id = null;

						this.LpuSectionProfilesGrid.getStore().load({
							params: params
						});
						this.MedPersonalGrid.getStore().load({
							params: params
						});
						this.ResourceGrid.getStore().load({
							params: params
						});
					} else if (node.attributes.object == 'LpuSection') {
						this.applyFilters({
							'LpuSection_id': node.attributes.object_id
						});
						this.LpuUnit_id = node.parentNode.attributes.object_id;
						this.LpuSectionProfile_id = null;
						this.LpuSection_id = node.attributes.object_id;
						this.MedStaffFact_id = null;
						this.MedService_id = null;
						this.Resource_id = null;

						this.LpuSectionProfilesGrid.getStore().load({
							params: params
						});
						this.MedPersonalGrid.getStore().load({
							params: params
						});
						this.ResourceGrid.getStore().load({
							params: params
						});
					} else if (node.attributes.object == 'LpuSectionPid') {
						this.applyFilters({
							'LpuSection_id': node.attributes.object_id
						});
						this.LpuUnit_id = node.parentNode.parentNode.attributes.object_id;
						this.LpuSectionProfile_id = null;
						this.LpuSection_id = node.attributes.object_id;
						this.MedStaffFact_id = null;
						this.MedService_id = null;
						this.Resource_id = null;

						this.LpuSectionProfilesGrid.getStore().load({
							params: params
						});
						this.MedPersonalGrid.getStore().load({
							params: params
						});
						this.ResourceGrid.getStore().load({
							params: params
						});
					} else if (node.attributes.object == 'MedService') {
						this.applyFilters({
							'MedService_id': node.attributes.object_id
						});
						this.LpuUnit_id = null;
						this.LpuSectionProfile_id = null;
						this.LpuSection_id = null;
						this.MedStaffFact_id = null;
						this.MedService_id = node.attributes.object_id;
						this.Resource_id = null;

						this.MedPersonalGrid.getStore().load({
							params: params
						});
						this.ResourceGrid.getStore().load({
							params: params
						});
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
					this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter1').focus();
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
				}.createDelegate(this),
				'beforeclick': function(node) {
					this.StructureTree.onSelectNode(node);
				}.createDelegate(this)
			},
			loader: new Ext.tree.TreeLoader({
				url: '/?c=Reg&m=GetFilterTree'
			})
		});
		
		this.LpuSectionProfilesGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: false,
			region: 'center',
			width: 365,
			split: true,
			header: false,
			id: 'tqewLpuSectionProfileGrid',
			tabIndex: TABINDEX_TQE + 4,
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			title: lang['profili'],
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
								this.LpuSectionProfilesGrid.getTopToolbar().items.item('NameFilter').focus();
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
				url: '/?c=TimetableQuote&m=getLpuSectionProfileList',
				fields: [
					'LpuSectionProfile_id',
					'LpuSectionProfile_Name'
				],
				listeners: {
					'load': function(store) {
						var field = this.LpuSectionProfilesGrid.getTopToolbar().items.item('NameFilter');
						var exp = field.getValue();
						if (exp != "") {
							this.LpuSectionProfilesGrid.getStore().filter('LpuSectionProfile_Name', new RegExp(exp, "i"));
						}
						this.LpuSectionProfilesGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'LpuSectionProfile_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: lang['profil'], dataIndex: 'LpuSectionProfile_Name', sortable: true}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
				items: [{
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'NameFilter',
					tabIndex: TABINDEX_TQE + 3,
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
									this.LpuSectionProfilesGrid.getStore().clearFilter();
									var field = this.LpuSectionProfilesGrid.getTopToolbar().items.item('NameFilter');
									var exp = field.getValue();
									this.LpuSectionProfilesGrid.getStore().filter('LpuSectionProfile_Name', new RegExp(exp, "i"));
									this.LpuSectionProfilesGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + this.LpuSectionProfilesGrid.getStore().getCount();
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
									if ( this.LpuSectionProfilesGrid.getStore().getCount() > 0 )
									{
										this.LpuSectionProfilesGrid.getView().focusRow(0);
										this.LpuSectionProfilesGrid.getSelectionModel().selectFirstRow();
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
						this.applyFilters({
							'LpuSectionProfile_id' : r.data.LpuSectionProfile_id
						});
						
						this.LpuSectionProfile_id = r.data.LpuSectionProfile_id;
						this.LpuSection_id = null;
						this.MedStaffFact_id = null;
						this.MedService_id = null;
						this.Resource_id = null;
						this.LpuSectionProfilesGrid.getTopToolbar().items.items[3].el.innerHTML = (rowIdx+1)+' / ' + this.LpuSectionProfilesGrid.getStore().getCount();
					}.createDelegate(this)
				}
			})
		});
		
		this.MedPersonalGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: false,
			region: 'center',
			width: 365,
			split: true,
			header: false,
			id: 'tqewMedStaffFactGrid',
			tabIndex: TABINDEX_TQE + 4,
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			title: lang['vrachi'],
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
								this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter1').focus();
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
					'LpuUnit_id',
					'MedStaffFact_id',
					'MedPersonal_FIO'
				],
				listeners: {
					'load': function(store) {
						var field = Ext.getCmp('FIOFilter1');
						if (field) {
							var exp = field.getValue();
							exp = '^'+exp;
							if (exp != "") {
								this.MedPersonalGrid.getStore().filter('MedPersonal_FIO', new RegExp(exp, "i"));
							}
							this.MedPersonalGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();
						}
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'MedStaffFact_id', hidden: true, hideable: false},
				{dataIndex: 'LpuUnit_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: lang['fio_vracha'], dataIndex: 'MedPersonal_FIO', sortable: true}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
				items: [{
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'FIOFilter1',
					tabIndex: TABINDEX_TQE + 3,
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
									this.MedPersonalGrid.getStore().clearFilter();
									var field = Ext.getCmp('FIOFilter1');
									var exp = field.getValue();
									exp = '^'+exp;
									this.MedPersonalGrid.getStore().filter('MedPersonal_FIO', new RegExp(exp, "i"));
									this.MedPersonalGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + this.MedPersonalGrid.getStore().getCount();
									field.focus();
								}.createDelegate(this),
								1
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
						this.applyFilters({
							'MedStaffFact_id' : r.data.MedStaffFact_id
						});
						this.LpuUnit_id = r.data.LpuUnit_id;
						this.LpuSectionProfile_id = null;
						this.LpuSection_id = null;
						this.MedStaffFact_id = r.data.MedStaffFact_id;
						this.MedService_id = null;
						this.Resource_id = null;
						this.MedPersonalGrid.getTopToolbar().items.items[3].el.innerHTML = (rowIdx+1)+' / ' + this.MedPersonalGrid.getStore().getCount();
					}.createDelegate(this)
				}
			})
		});

		this.ResourceGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: false,
			region: 'center',
			width: 365,
			split: true,
			header: false,
			id: 'tqewResourceGrid',
			tabIndex: TABINDEX_TQE + 4,
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			title: 'Ресурсы',
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
								this.ResourceGrid.getTopToolbar().items.item('FIOFilter2').focus();
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
					'LpuUnit_id',
					'Resource_id',
					'Resource_Name'
				],
				listeners: {
					'load': function(store) {
						var field = Ext.getCmp('FIOFilter2');
						if (field) {
							var exp = field.getValue();
							if (exp != "") {
								this.ResourceGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
							}
							this.ResourceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();
						}
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'MedStaffFact_id', hidden: true, hideable: false},
				{dataIndex: 'LpuUnit_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: 'Ресурс', dataIndex: 'Resource_Name', sortable: true}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
				items: [{
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'FIOFilter2',
					tabIndex: TABINDEX_TQE + 3,
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
									this.ResourceGrid.getStore().clearFilter();
									var field = Ext.getCmp('FIOFilter2');
									var exp = field.getValue();
									this.ResourceGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
									this.ResourceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + this.ResourceGrid.getStore().getCount();
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
									if ( this.ResourceGrid.getStore().getCount() > 0 )
									{
										this.ResourceGrid.getView().focusRow(0);
										this.ResourceGrid.getSelectionModel().selectFirstRow();
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
						this.applyFilters({
							'Resource_id' : r.data.Resource_id
						});
						this.LpuUnit_id = r.data.LpuUnit_id;
						this.LpuSectionProfile_id = null;
						this.LpuSection_id = null;
						this.MedStaffFact_id = null;
						this.MedService_id = r.data.MedService_id;
						this.Resource_id = r.data.Resource_id;
						this.ResourceGrid.getTopToolbar().items.items[3].el.innerHTML = (rowIdx+1)+' / ' + this.ResourceGrid.getStore().getCount();
					}.createDelegate(this)
				}
			})
		});
		
		
		this.MP_LSPTabPanel = new Ext.TabPanel({
				activeTab: 0,
				defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
				layoutOnTabChange: true,
				tabWidth: 'auto',
				region: 'center',
				id: 'MP_LSPTabPanel',
				items: [
					this.LpuSectionProfilesGrid,
					this.MedPersonalGrid,
					this.ResourceGrid,
				],
				listeners: {
					tabchange: function(comp,tab){
						tab.getSelectionModel().clearSelections();
						tab.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + tab.getStore().getCount();
					}
				}
		});
		
		this.FiltersPanel = new Ext.FormPanel({
			region: 'north',
			border: false,
			frame: true,
			//defaults: {bodyStyle:'background:#DFE8F6;'},
			xtype: 'form',
			autoHeight: true,
			layout: 'column',
			//style: 'padding: 5px;',
			bbar:
			[
				{
					tabIndex: TABINDEX_TQE+11,
					xtype: 'button',
					id: 'rmwBtnMPSearch',
					text: lang['nayti'],
					iconCls: 'search16',
					handler: function()
					{
						this.applyFilters();
					}.createDelegate(this)
				},
				{
					tabIndex: TABINDEX_TQE+12,
					xtype: 'button',
					id: 'rmwBtnMPClear',
					text: lang['sbros'],
					iconCls: 'resetsearch16',
					handler: function()
					{
						// Очистка полей фильтра И перезагрузка
						this.FiltersPanel.clearFilters(true);
					}.createDelegate(this)
				},
				{
					xtype: 'tbseparator'
				}
			],
			items: [{
				layout: 'form',
				columnWidth: .5,
				labelAlign: 'right',
				labelWidth: 150,
				items: [
					{
						allowBlank: false,
						disabled: false,
						enableKeyEvents: true,
						fieldLabel: lang['data_deystviya_kvotyi'],
						format: 'd.m.Y',
						listeners: {
							'keypress': function(field, e) {
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									this.applyFilters();
								}
							}.createDelegate(this)
						},
						name: 'TimetableQuoteRule_Date',
						tabIndex: TABINDEX_TQE + 6,
						value: new Date(),
						width: 100,
						xtype: 'swdatefield'
					}
				]
			},
			{
				layout: 'form',
				columnWidth: .5,
				labelAlign: 'right',
				labelWidth: 80,
				items: [
					{
						allowBlank: true,
						disabled: false,
						enableKeyEvents: true,
						fieldLabel: lang['tip_kvotyi'],
						listeners: {
							'keypress': function(field, e) {
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									this.applyFilters();
								}
							}.createDelegate(this)
						},
						name: 'TimetableQuoteType_id',
						tabIndex: TABINDEX_TQE + 7,
						width: 200,
						comboSubject: 'TimetableQuoteType',
						xtype: 'swcommonsprcombo'
					}
				]
			}
			],
			
			/**
			 * Очистка фильтров с применением к спискам
			 */
			clearFilters: function(scheduleLoad)
			{
				this.FiltersPanel.getForm().reset();
				this.applyFilters();
				
			}.createDelegate(this),
			
			/**
			 * Получаем установленные фильтры
			 */
			getFilters: function(){
				var filter_form = this.FiltersPanel.getForm();
				return new Object({
					TimetableQuoteRule_Date: Ext.util.Format.date(filter_form.findField('TimetableQuoteRule_Date').getValue(), 'd.m.Y'),
					TimetableQuoteType_id: filter_form.findField('TimetableQuoteType_id').getValue()
				});
			}.createDelegate(this)
		});

		
		this.QuotesGrid = new sw.Promed.ViewFrame({
			actions:
			[
				{name:'action_add', handler: function() { 
						getWnd('swTimetableQuoteRuleEditWindow').show({
							LpuUnit_id: this.LpuUnit_id,
							LpuSectionProfile_id: this.LpuSectionProfile_id,
							LpuSection_id: this.LpuSection_id,
							MedStaffFact_id: this.MedStaffFact_id,
							MedService_id: this.MedService_id,
							Resource_id: this.Resource_id
						})
					}.createDelegate(this)
				},
				{name:'action_edit', handler: 
					function() {
						var sel = this.QuotesGrid.getGrid().getSelectionModel().getSelected();
						
						getWnd('swTimetableQuoteRuleEditWindow').show({
							TimetableQuoteRule_id: sel['data']['TimetableQuoteRule_id']
						});
					}.createDelegate(this)
				},
				{name:'action_view', hidden: true },
				{name:'action_delete', handler: function() {
					var viewframe = this.QuotesGrid;
					var grid = viewframe.getGrid();
					if (!grid)
					{
						Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
						return false;
					}
					else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(viewframe.jsonData['key_id']) ) {
						Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
						return false;
					}
					
					var params = { TimetableQuoteRule_id: grid.getSelectionModel().getSelected().data[viewframe.jsonData['key_id']] };
					
					sw.swMsg.show(
					{
						icon: Ext.MessageBox.QUESTION,
						msg: lang['vyi_hotite_udalit_zapis'],
						title: lang['podtverjdenie'],
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj)
						{
							if ('yes' == buttonId)
							{
								Ext.Ajax.request(
								{
									url: C_TTQUOTE_DELETE,
									params: params,
									failure: function(response, options)
									{
										Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
									},
									success: function(response, action)
									{
										//grid.getStore().removeAll();
										if (response.responseText)
										{
											var answer = Ext.util.JSON.decode(response.responseText);
											if (!answer.success)
											{
												if (answer.Error_Code && !answer.Error_Msg) //todo: Убрать в ближайшем будущем это условие
												{
													Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
												}
												else
													if (!answer.Error_Msg) // если не автоматически выводится
													{
														Ext.Msg.alert(lang['oshibka'], lang['udalenie_nevozmojno']);
													}
											}
											else
											{
												grid.getStore().reload();
												if (viewframe.afterDeleteRecord)
												{
													viewframe.afterDeleteRecord({object:viewframe.object, id:id, answer:answer});
												}
											}
										}
										else
										{
											Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
										}
									}
								});
							}
							else
							{
								if (grid.getStore().getCount()>0)
								{
									grid.getView().focusRow(0);
								}
							}
						}
					});
					}.createDelegate(this)
				}
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: C_TTQUOTES_LIST,
			//stateful: true,
			id: 'QuotesGrid',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				var sel = this.QuotesGrid.getGrid().getSelectionModel().getSelected();
				
				getWnd('swTimetableQuoteRuleEditWindow').show({
					TimetableQuoteRule_id: sel['data']['TimetableQuoteRule_id']
				});
			}.createDelegate(this),
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'TimetableQuoteRule_id', type: 'int', header: 'ID', key: true },
				{ name: 'TimetableQuoteType_id', type: 'int', hidden: true },
				{ name: 'LpuUnit_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSection_id', type: 'int', hidden: true },
				{ name: 'MedStaffFact_id', type: 'int', hidden: true },
				{ name: 'TimetableQuoteType_Name', type: 'string', header: lang['tip_kvotyi'] },
				{ name: 'LpuUnit_Name', type: 'string', header: lang['podrazdelenie'] },
				{ name: 'TimetableQuote_Object', type: 'string', header: lang['obyekt_deystviya_kvotyi'], width: 400 },
				{ name: 'TimetableQuoteRule_begDT', type: 'date', header: lang['nachalo_deystviya_kvotyi'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'TimetableQuoteRule_endDT', type: 'date', header: lang['konets_deystviya_kvotyi'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'TimetableQuoteSubjects', type: 'string', header: lang['subyektyi_deystviya_kvotyi'], id: 'autoexpand' }
			],
			title: null
			/*paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount'*/
		});
		
		this.QuoteListPanel = new Ext.Panel({
				region: 'center',
				id: 'QuoteListPanel',
				layout:'border',
				items:[
					this.FiltersPanel,
					this.QuotesGrid
				]
		});
		
		Ext.apply(this, {
			buttons: [
				new Ext.Button({
					text: BTN_FRMHELP,
					iconCls: 'help16',
					id: 'QEW_HelpButton',
					tabIndex: TABINDEX_TQE + 10,
					handler: function(button, event) {
						ShowHelp(WND_REG_QUOTEEDITOR);
					}.createDelegate(this)
				}),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					id: 'QEW_CancelButton',
					tabIndex: TABINDEX_TQE + 11,
					text: BTN_FRMCLOSE
				}
			],
			layout: 'border',
			items: [
				new Ext.Panel({
						region: 'west',
						width: 270,
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
										hidden: (this.getRole() == 'lpu_admin' || this.getRole() == 'lpu_oper')
									},
									this.StructureTree
								]
							},
							this.MP_LSPTabPanel
						]
				}),
				this.QuoteListPanel
			]
		});
		sw.Promed.swTimetableQuoteEditorWindow.superclass.initComponent.apply(this, arguments);
	},

	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swTimetableQuoteEditorWindow.superclass.show.apply(this, arguments);
		
		if(!arguments[0]) {
			arguments = [{}];
		}
		
		this.params = {};
		this.params.Lpu_id = arguments[0].Lpu_id || Ext.globalOptions.globals.lpu_id;
		this.params.Lpu_Nick = arguments[0].Lpu_Nick || Ext.globalOptions.globals.lpu_nick;
		this.params.MedService_id = arguments[0].MedService_id || null;
		this.params.LpuUnit_id = arguments[0].LpuUnit_id || null;
		this.params.LpuUnitType_id = arguments[0].LpuUnitType_id || null;
		this.params.LpuSection_id = arguments[0].LpuSection_id || null;
		this.params.MedPersonal_id = arguments[0].MedPersonal_id || null;
		this.SelectLpuCombo.setValue(this.params.Lpu_id);
		this.SelectLpuCombo.setDisabled(false);
		
		this.changeLpu(this.params.Lpu_id, this.params.Lpu_Nick);
		if(this.params.LpuUnit_id) {
			this.SelectLpuCombo.setDisabled(true);
		}
		
		if ( !this.QuotesGrid.getAction('action_copy') ) {
			this.QuotesGrid.addActions({
				name: 'action_copy',
				iconCls: 'copy16',
				tooltip: lang['kopirovat_pravilo'],
				text: lang['kopirovat'],
				handler: function() {
					var sel = this.QuotesGrid.getGrid().getSelectionModel().getSelected();
					
					getWnd('swTimetableQuoteRuleEditWindow').show({
						TimetableQuoteRule_id: sel['data']['TimetableQuoteRule_id'],
						mode: 'copy'
					});
				}.createDelegate(this)
			});
		}
	}
});

