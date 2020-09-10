/**
 * Окно поиска ниже, сначала вспомогательные компоненты
 */


/**
 * Компонент-виджет для использования в ячейке таблицы
 * Кнопка с иконкой звезды для отметки диагнозов как избранных
 * Преимущество перед actioncolumn - есть доступ к записи строки, можно привязать состояние иконки через bind
 */
Ext6.define('FavouriteButtonForGrid', {
	extend: 'Ext6.grid.column.Widget',
	alias: 'widget.FavouriteButtonForGrid',
	text: 'Favourite',
	context: '',
	flex: 1,
	// onWidgetAttach: function(col, widget, rec) {
	widget: {
		xtype: 'button',
		enableToggle: true,
		name: 'Diag_IsFavourite',
		ui: 'plain',
		width: 16,
		tooltip: 'Добавить в избранное',
		bind: {
			iconCls: '{record.Diag_IsFavourite ? "full-star" : "empty-star"}',
			hidden: '{record.DiagLevel_id != 4}'
		},
		handler: function (btn, event)
		{
			var record = btn.getWidgetRecord(),
				toggled = record.get(btn.name);
			// TODO неизвестно как будет релизовано хранение избранных диагнозов, пока кнопка просто меняет заливку
			record.set(btn.name, ! toggled);
			return true;
		}
	}
});


Ext6.define('DiagTreeModel', {
	extend: 'Ext6.data.TreeModel',
	alias: 'widget.DiagTreeModel',
	idProperty: 'Diag_id',
	fields: [
		{name: 'Diag_id'},
		{name: 'DiagFinance_IsOms', type: 'int'},
		{name: 'DiagLevel_id', type: 'int'},
		{name: 'Diag_Name', type: 'string'},
		{name: 'text', type: 'string'},
		{name: 'Diag_Code', type: 'string'},
		{name: 'leaf', type: 'boolean'},
		{name: 'PersonRegisterType_List'},
		{name: 'MorbusType_List'},
		{name: 'DeathDiag_IsLowChance', type: 'int'},
		{name: 'IsFavourite', type: 'boolean'} // этого еще нет, добавил на будущее
	]
});



Ext6.define('DiagLiveSearchField', { // обычное текстовое поле для ввода. Но основании ввода происходит обновление стора в гриде
	extend: 'Ext6.form.Text',
	alias: 'widget.DiagLiveSearchField',
	emptyText: 'Введите код или наименование диагноза',
	for: 'diag',
	submitEmptyText: false,
	enableKeyEvents: true,
	plugins: [new Ext6.ux.Translit(false, false)],
	triggers: {
		search: {
			cls: 'x6-form-search-trigger' // просто значок лупы для вида, не работает
		},
		clear: {
			cls: 'clear-icon',
			hidden: true,
			handler: function ()
			{
				this.setValue('');
			}
		}
	},

	initComponent: function ()
	{
		var me = this;

		this.callParent(arguments);

		if (this.up('window') && this.up('window').getViewModel())
		{
			this.up('window').getViewModel().bind('{diagSearchQuery}',
				function (v)
				{
					if (v)
					{
						me.getTrigger('search').hide();
						me.getTrigger('clear').show();
					} else
					{
						me.getTrigger('search').show();
						me.getTrigger('clear').hide();
					}

					return;
				} );
		}

		return;
	}

});

Ext6.define('DiagSearchController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.DiagSearchController',

	bindings: {
		searchDiag: '{diagSearchQuery}',
		loadMode: '{mode.value}'
	},

	renderDiagGrid: function (val, metaData, rec, rowIndex, colIndex, view)
	{
		var wnd = this.getView(),
			vm = wnd.getViewModel(),
			q = vm.get('diagSearchQuery') || '',
			q = q.trim(),

			code = rec.get('Diag_Code'),
			name = rec.get('Diag_Name');


		if (q.length > 0)
		{
			var arr = q.split(' '),
				isCode = ( arr[0].match(new RegExp('[a-z0-9.]{1,6}', 'i')) && code.match(new RegExp( arr[0], 'i')) ) || false;

			if (isCode)
			{
				code = code.replace(new RegExp( '(' + arr[0] + ')', 'i'), '<span class="search-result-diag">$1</span>'); // подкрашивание результатов поиска
				arr[0] = '';

				q = arr.join(' ').trim();
			}

			name = name.replace(new RegExp( '(' + q + ')', 'i'), '<span class="search-result-diag">$1</span>');
		}

		return '<span class="search-result-diag-code">' + code + '</span> ' + '<span data-qtip="' + rec.get('Diag_Name') +'">' +  name + '</span>';
	},

	loadMode: function (mode, none, binding)
	{
		if ( ! mode || mode !== 'last')
		{
			return;
		}


		var wnd= this.getView(),
			grid = wnd.down('grid');

		grid.getStore().load({
			params: {mode: mode},
			callback: function (a,b,c,d,e)
			{

			}
		});

		return;
	},

	searchDiag: function (value, none, binding)
	{
		var wnd = this.getView(),
			vm = this.getViewModel(),

			grid = wnd.down('grid');


		if (value)
		{
			value = value.trim();

			if (grid.delaySearchId)
			{
				clearTimeout(grid.delaySearchId);
			}

			grid.delaySearchId = setTimeout(function()
			{
				if (grid.filterDate) {
					grid.getStore().getProxy().setExtraParam( 'Diag_Date', grid.filterDate);
				}
				grid.getStore().load({params:{query: value}});
				grid.delaySearchId = null;

				return;
			}, 500);

			//yl:выключить кнопки если поиск по фразе
			if ((dock = wnd.getDockedItems("toolbar[dock='top']")) && dock.length && (sm_btn = dock[0].down("[xtype=segmentedbutton]"))) {
				if (fav_btn = sm_btn.down("[value='favourites']")) fav_btn.setPressed(false);
				if (last_btn = sm_btn.down("[value='last']")) last_btn.setPressed(false);
			}
		} else
		{
			if (grid.delaySearchId)
			{
				clearTimeout(grid.delaySearchId);
			}

			grid.getStore().removeAll();
		}

		return;
	},

	selectDiag: function (row, record, item)
	{
		var wnd = this.getView(),
			grid = wnd.down('grid');


		if ( ! record || record.isModel !== true)
		{
			record = grid.getSelection()[0]; // если через кнопку выбрать
		}

		if( ! record || ! record.data || ! record.data.Diag_id )
		{
			Ext6.Msg.alert('Ошибка','Вы ничего не выбрали');
			return false;
		}

		if( record.data.DiagLevel_id != 4 )
		{
			Ext6.Msg.alert('Ошибка','Выберите диагноз 4 уровня');
			return false;
		}

		var data = record.data;

		wnd.onSelect(data);
		wnd.close();
	}
});




Ext6.define('common.DiagSearchTreeWindow', {
	extend: 'base.BaseForm',
	noTaskBarButton: true,
	alias: 'widget.DiagSearchTreeWindow',
	controller: 'DiagSearchController',
	cls: 'diag-search-tree-window',
	title: 'Справочник МКБ-10',
	closeAction: 'destroy',
	closeToolText: 'Закрыть окно поиска',
	viewModel: {
		data: {
			diagSearchQuery: '',
			mode: ''
		}
	},
	resizable: true,
	width: 900,
	filterDate: '',
	height: 600,
	border: false,
	modal: true,
	dockedItems:[
		{
			xtype: 'toolbar',
			dock: 'top',
			cls: 'sw-toolbar-grey',
			items: [
				{
					xtype: 'DiagLiveSearchField',
					width: '50%',
					fieldStyle: 'min-height: 25px; max-height: 25px; height: 100%',
					bind: '{diagSearchQuery}'
				},
				'->',
				{
					xtype: 'segmentedbutton',
					userCls: 'template-search-button-without-border',
					allowMultiple: false,
					allowDepress: false,//yl:не выключать при повторном клике
					reference: 'mode',  // component's name in the ViewModel
					publishes: 'value',
					items: [
						{
							text: 'Избранные',
							handler: function () {},
							enableToggle: true,
							value: 'favourites',
							iconCls: 'favTemp-btn-icon',
							tooltip: 'Избранные диагнозы',
							cls: 'button-without-border btn-grey-blue'
						},
						{
							text: 'Последние',
							handler: function () {},
							enableToggle: true,
							value: 'last',
							iconCls: 'lastStand-btn-icon',
							tooltip: 'Показать последние диагнозы',
							cls: 'button-without-border btn-grey-blue'
						}
					]
				}
			]
		},
		{
			xtype: 'toolbar',
			dock: 'bottom',
			cls: 'sw-toolbar-grey',
			items: [
				'->',
				{
					xtype: 'button',
					refId: 'selectBtn',
					userCls: 'buttonCancel',
					text: 'ОТМЕНА',
					margin: 0,
					cls: 'button-without-border mid-font',
					handler: function(){
						var wnd = this.up('window');
						wnd.close();
					}
				},
				{
					xtype: 'button',
					refId: 'cancelBtn',
					userCls: 'buttonAccept',
					text: 'ВЫБРАТЬ',
					margin: '0 20 0 0',
					cls: 'button-without-border blue mid-font',
					handler: 'selectDiag'
				}
			]
		}
	],
	onSelect: Ext.emptyFn(),

	baseFilterFn: null,

	layout: {
		type: 'vbox',
		align: 'stretch'
	},

	items: [
		{
			flex: 1,
			layout: 'border',
			border: false,
			items: [{
				header: false,
				region: 'west',
				border: false,
				floatable: false,
				title: {
					text: 'КЛАССЫ МКБ-10',
					style:{'fontSize':'14px', 'fontWeight':'500'},
					rotation: 2,
					textAlign: 'right'
				},
				collapsible: true,
				width: '40%',
				split: true,
				layout: 'fit',
				items: [
					{
						xtype: 'TreePanelCustomIcons',
						displayField: 'text',
						padding: '0 0 0 0',
						scrollable: 'y',

						store: {
							model: 'DiagTreeModel',
							maskDefaults: {
								focusCls: 'no-border',
								msg: 'Загружаем дерево диагнозов'
							},
							rootVisible: true,
							root:{ // root - обязательный элемент дерева, корень, содержит в себе все остальное

								nodeType: 'async',
								text: langs('Классы диагнозов'),
								id: 'root',
								expanded: true
							},
							autoLoad:true,
							proxy: {
								type: 'ajax',
								url: '/?c=Diag&m=getDiagTreeData',
								reader: {
									type: 'json'
								}
							}
						},
						listeners: {
							load: function (loader, node, response)
							{
								var wnd = this.up('window'),
									tree = wnd.down('TreePanelCustomIcons'),
									grid = wnd.down('grid');

								if (typeof(wnd.baseFilterFn) == 'function') {
									tree.getStore().filterBy(wnd.baseFilterFn);
									grid.getStore().filterBy(wnd.baseFilterFn);
								}
							},
							beforecellclick: function (cell, td, cellIndex, record)
							{ // грузим грид

								var wnd = this.up('window'),
									grid = wnd.down('grid'),
									Diag_id = record.get('Diag_id'),

									params = {
										Diag_pid: Diag_id
									};

								if (grid.filterDate) {
									params.Diag_Date = grid.filterDate;
								}

								grid.getStore().load({
									params: params,
									callback: function (a,b,c,d,e)
									{

									}
								});
							}
						}
					}

				]
			}, {

				border: false,
				region: 'center',
				layout: 'fit',
				items: [
					{
						hideHeaders: true,
						userCls: 'diag-grid-items',
						padding: '0 0 0 0',
						xtype: 'grid',
						//striped: true,
						filterDate: '',
						rowLines: false,
						border: false,
						scrollable: 'y',
						viewConfig: {
							markDirty: false
						},

						store: {
							model: 'swDiagModel',
							autoLoad: false,
							proxy: {
								type: 'ajax',
								url: '/?c=Diag&m=loadDiagGrid',
								reader: {
									type: 'json',
									rootProperty: 'data'
								}
							},
							remoteSort: true
						},

						columns: [
							{
								xtype: 'FavouriteButtonForGrid',
								minWidth: 30,
								maxWidth: 30,
								hidable: false
							},
							{
								text: 'DiagCodeName', tdCls: 'diag-name-td', flex: 1,

								renderer: 'renderDiagGrid'
							}

						],
						listeners: {
							itemdblclick: 'selectDiag'
						}
					}]
			}]
		}
	],

	show: function(windowArgs){

		if (windowArgs)
		{
			if (windowArgs.filterDate) {
				this.down('TreePanelCustomIcons').getStore().getProxy().setExtraParam( 'Diag_Date', windowArgs.filterDate);
				this.down('grid').filterDate = windowArgs.filterDate;
			}
			Ext6.apply(this, windowArgs); // onSelect
		}

		this.callParent(arguments);

		//yl: переопределение кнопок Избранные и Последние для ДВН-Заболевания
		if (this.EvnPLDispDop13Desease) {
			this.getController().loadMode = function (mode) {
				if (!mode) return;
				if (mode == "last") mode = "lastPersonDiags";
				if ((view = this.getView()) && (grid = view.down("grid")) && (store = grid.getStore())) {
					if(vm = view.getViewModel()){
						vm.set("diagSearchQuery");//очистить поле поиска
					}
					store.load(
						{
							params: {
								mode: mode,
								Person_id: this.getView().Person_id
							}
						}
					)
				}
			};
			//yl: автостарт Последних по пациенту за 5 лет
			if ((dock = this.getDockedItems("toolbar[dock='top']")) && dock.length && (sm_btn = dock[0].down("[xtype=segmentedbutton]"))) {
				if (fav_btn = sm_btn.down("[value='favourites']")) fav_btn.setTooltip("Избранные диагнозы у врача");
				if (last_btn = sm_btn.down("[value='last']")) last_btn.setTooltip("Показать последние диагнозы у пациента за 5 лет");last_btn.click();
			}
		}

	}
});
