/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 11.02.15
 * Time: 10:24
 * To change this template use File | Settings | File Templates.
 */
/**
 * swFilterGridPlugin - окно фильтра грида.
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 *
 * @package      Admin
 * @access       public
 * @version      25.06.2013
 * @author       Васинский Игорь (НПК "Прогресс" г.Уфа)
 */

// Пример
// Task#21709 Разработка интерфейса фильтрации на вкладке «1. Данные»
//-- В глобальную область, т.к. использую в нескольких местах
// DataIndex (array) колонок к которым внедрены кнопки фильтров
//--- columnsFilter = ['EvnPL_NumCard','Person_FIO','Usluga_Code','Diag_Code','LpuSection_name','LpuBuilding_Name','MedPersonal_Fio'];
// BaseParams для Store (зависит от модели на серверной стороне)
// Конфиг параметров для серверной стороны :
//-- url для store  - урл к моделе работы с БД
//-- Redistry_id, RegistryType_Id - инициализированы уже (если доступны), можно переининиализировать
//--- configParams = {url : '/?c=RegistryUfaVE&m=loadRegistryDataFilter'}

// ИНТЕГРАЦИЯ
// Для подключения фильтра гриду 3 строки
/*
 columnsFilter = ['RegistryErrorType_Code','RegistryErrorType_Name','Person_FIO','Usluga_Code','Diag_Code','LpuSection_name','LpuBuilding_Name','MedPersonal_Fio'];
 configParams = {url : '/?c=RegistryUfaFilterGrid&m=loadRegistryDataFilter'}
 _addFilterToGrid(this.ErrorGrid,columnsFilter,configParams);
 */
var $loadMask;
sw.Promed.swFilterGridPlugin = Ext.extend(sw.Promed.BaseForm, {
	id    : 'swFilterGridPlugin',
	objectName    : 'swFilterGridPlugin',
	objectSrc     : '/jscore/Forms/Admin/swFilterGridPlugin.js',
	layout: 'form',
	valueInputSearch : '',
	plain: true,
	buttonAlign: 'center',
	title : lang['ispolzovanie_rasshirennogo_filtra'],
	modal : true,
	width : 500,
	height:500,
	closable : false,
	closeAction   : 'close',
	draggable     : true,
	initComponent: function()
	{
		if(!Ext.getCmp(this.gridID).FilterSettings)
			Ext.getCmp(this.gridID).FilterSettings = {};
		this.targetCellName = this.paramCell.cell;

		var paramString = Ext.util.JSON.encode(this.paramCell);

		var TargetGrid = Ext.getCmp(this.gridID);

		var baseParams = TargetGrid.ViewGridModel.grid.store.baseParams;
		baseParams.object = 'RegistryDataFilterUnicCell';

		delete(baseParams.limit);
		delete(baseParams.start);

		var postfix = '';
		var proxyUrl = TargetGrid.ViewGridModel.grid.store.proxy.conn.url;
		proxyUrl = ((proxyUrl).replace(postfix, '')).replace('&m=',postfix+'&m=');

		TargetGrid.ViewGridModel.grid.store.proxy.conn.url = proxyUrl;

		paramString = paramString.replace(/\"/g, '"');
		this.targetFilterParam = paramString;

		this.TG = TargetGrid;

		this.store = new Ext.data.JsonStore({
			url: this.url,
			root: 'data',
			id : 'storeFiltered',
			totalProperty: 'totalCount',
			disableSelection: true,
			loadMask: true,
			baseParams : baseParams,
			fields: ['checked', 'field'],

			listeners : {
				beforeload: function () {
					if (typeof Ext.getCmp('swFilterGridPlugin').unicGrid != 'undefined') {
						var $loadMask = new Ext.LoadMask(Ext.getCmp('swFilterGridPlugin').unicGrid.getEl(), {msg: LOAD_WAIT});
						$loadMask.show();
					}
				},
				load : function(){
					var curSetFilter = Ext.getCmp(Ext.getCmp('swFilterGridPlugin').gridID);

					if(curSetFilter.FilterSettings[column]){
						var arrStrings = curSetFilter.FilterSettings[column]['data'];
						var keys = [];

						if(this.data.items.length>0){
							for(k in arrStrings){
								for(key in this.data.items){
									if(this.data.items[key].data){
										if(this.data.items[key].data.field == arrStrings[k]){
											keys.push(key)
										}
									}
								}
							}

							Ext.getCmp('swFilterGridPlugin').unicGrid.getSelectionModel().selectRows(keys);
						}
					}
					$loadMask.hide();
				}
			},
			viewConfig: {
				forceFit: true,
				enableRowBody: true,
				showPreview: true,
				getRowClass: function(record, rowIndex, p, store) {
					if (this.showPreview) {
						p.body = '<p>' + record.data.excerpt + '</p>';
						return 'x-grid3-row-expanded';
					}
					return 'x-grid3-row-collapsed';
				}
			}
		});

		if(Ext.getCmp(this.gridID).gFilters != null && typeof(Ext.getCmp(this.gridID).gFilters) != 'undefined')
		{
			var type_id =  (typeof(Ext.getCmp(this.gridID).gFilters.RegistryType_id) != 'undefined') ? Ext.getCmp(this.gridID).gFilters.RegistryType_id : null;
		}
		var targetGrid = Ext.getCmp(this.gridID);

		if(typeof(targetGrid.type_id) == 'undefined'){
			targetGrid.type_id = type_id;
		}

		if(Ext.getCmp(this.gridID)){
			Ext.getCmp(this.gridID).thisCell = this.paramCell.cell;
		}
		//Уникализируем записи по нужному столбцу
		this.getDataFilter(this.paramCell);

		//Проверка состояния фильтра к данной колонке грида
		var column = this.targetCellName;
		var targetGrid = Ext.getCmp(this.gridID);


		if(targetGrid.FilterSettings[column]){
			//Если фильтр был установлен - восстановим его для данной колонки визуально
			var fs_of_tc = targetGrid.FilterSettings[column];

			this.getDataFilter(fs_of_tc['params']);

			var getValue = Ext.util.JSON.decode(fs_of_tc['params']);

			this.valueInputSearch = getValue.value;
		}
		else {
			//console.log('нет настроек фильтра по данной колонке');
		}

		this.pagingBar = new Ext.PagingToolbar({
			id : this.id + 'paginator',
			pageSize: 100,
			store: this.store,
			displayInfo: true,
			displayMsg: 'Записи {0} - {1} из {2}', // из {2} - серверная сторона странно считает - решить,
			emptyMsg: "Записей нет"
		});

		this.unicGrid =  new Ext.grid.GridPanel({
			id : 'unicGrid',
			region : 'center',
			height: 300,
			store  : this.store,
			sm: new Ext.grid.CheckboxSelectionModel(),
			columns  : [new Ext.grid.CheckboxSelectionModel(), {header : '', width :380, DataIndex : 'field'}],
			border   : 1,
			iconCls:'search16',
			paging: true,
			bbar: this.pagingBar
		});

		Ext.apply(this,{
			autoHeight: true,
			buttons : [{
				text: lang['primenit_filtr'],
				iconCls: 'search16',
				handler: function(){
					var checked_items =  Ext.getCmp('swFilterGridPlugin').unicGrid.getSelectionModel().selections.items;

					var filterString = [];

					if(checked_items.length > 0){
						for(key in checked_items){
							if(checked_items[key].data){
								filterString.push(checked_items[key].data.field);
							}
						}
						//Сохраняем настройки фильтра к target Grid
						Ext.getCmp('swFilterGridPlugin').setHeader('active');
						targetGrid.FilterSettings[column] = [];
						targetGrid.FilterSettings[column]['data'] = filterString;
						targetGrid.FilterSettings[column]['params'] = Ext.getCmp('swFilterGridPlugin').targetFilterParam;
					}
					else{
						//Если нет отмеченных - сбрасываем фильтр
						Ext.getCmp(Ext.getCmp('swFilterGridPlugin').gridID).FilterSettings[column] = false;
					}

					Ext.getCmp('swFilterGridPlugin').refreshDataTargetGrid();
					Ext.getCmp('swFilterGridPlugin').refresh();
				}
			},
			{
				text:lang['zakryit'],
				iconCls: 'close16',
				handler: function(){
					Ext.getCmp('swFilterGridPlugin').refresh();
				}
			},
			{
				text:lang['sbrosit_filtr'],
				iconCls: 'close16',
				handler: function(){
					Ext.getCmp('swFilterGridPlugin').setHeader('passive');
					targetGrid.FilterSettings[column] = false;
					Ext.getCmp('swFilterGridPlugin').refreshDataTargetGrid();
					Ext.getCmp('swFilterGridPlugin').refresh();
				}
			},
			{
				text:lang['spravka'],
				iconCls: 'help16',
				handler: function(){
					ShowHelp(Ext.getCmp('swFilterGridPlugin').title);
				}
			}],
			items : [{
				xtype : 'fieldset',
				title : lang['poisk'],
				region: 'north',
				height: 120,
				style : {margin : '10px'},
				width : 470,
				enableKeyEvents: true,
				keys: [{
					key: Ext.EventObject.ENTER,
					fn: function(e)
					{
						var value = Ext.getCmp('textFilterUnicalCell').getValue();
						var thisWin = Ext.getCmp('swFilterGridPlugin');

						if(value.length > 0){
							thisWin.paramCell.specific = true;
							thisWin.paramCell.value = value;
							var paramCellSpec = thisWin.paramCell;
							thisWin.getDataFilter(paramCellSpec);
						}
					},
					stopEvent: true
				}],
				items : [{
					xtype  : 'textfield',
					id     : 'textFilterUnicalCell',
					startValue : Ext.getCmp('swFilterGridPlugin').valueInputSearch,
					value : Ext.getCmp('swFilterGridPlugin').valueInputSearch,
					hideLabel : true,
					width: 440
				},
				{
					border: false,
					bodyStyle:'background:#C7D6E9;',
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							xtype : 'button',
							text  : lang['iskat'],
							handler : function (){
								var value = Ext.getCmp('textFilterUnicalCell').getValue();
								var thisWin = Ext.getCmp('swFilterGridPlugin');

								if(value.length > 0){
									thisWin.paramCell.specific = true;
									thisWin.paramCell.value = value;
									var paramCellSpec = thisWin.paramCell;
									thisWin.getDataFilter(paramCellSpec);
								}
							}
						}]
					}, {
						border: false,
						labelWidth: 200,
						layout: 'form',
						style : {marginLeft : '10px'},
						items: [{
							xtype: 'button',
							text: lang['sbros'],
							handler: function(){
								var thisWin = Ext.getCmp('swFilterGridPlugin');
								Ext.getCmp('textFilterUnicalCell').setValue('');
								thisWin.paramCell.specific = true;
								thisWin.paramCell.value = '';
								var paramCellSpec = thisWin.paramCell;
								thisWin.getDataFilter(paramCellSpec);
							}
						}]
					}]
				}]
			},
			{
				xtype : 'fieldset',
				title : lang['filtr_unikalnyih_znacheniy'],
				id    : 'forGrid',
				autoWidth   : true,
				height      : 340,
				style : {margin : '10px'},
				items : [
					this.unicGrid
				]
			}]
		});

		sw.Promed.swFilterGridPlugin.superclass.initComponent.apply(this, arguments);
	},
	/*
	 * Подсветка заголовка колонки при установке фильтра
	 * tag (string) - html тег подсветки: b> - фильтр установлен ,span> - фильтр снят
	 */
	setHeader: function(tag){
		var column = this.targetCellName;
		var targetGrid = Ext.getCmp(this.gridID);
		var colModelTargetGrid = targetGrid.getGrid().colModel;
		var indexColumn = colModelTargetGrid.findColumnIndex(column);
		var iHtml = colModelTargetGrid.getColumnHeader(indexColumn);

		if(tag == 'active'){
			targetGrid.getGrid().colModel.setColumnHeader(indexColumn, iHtml.replace('span>', 'b>'));
		}
		if(tag == 'passive'){
			targetGrid.getGrid().colModel.setColumnHeader(indexColumn, iHtml.replace('b>', 'span>'));
		}
	},

	/**
	 * Применение фильта к target гриду
	 */
	refreshDataTargetGrid : function(){
		var targetGrid = Ext.getCmp(this.gridID);
		if(Object.keys(targetGrid.FilterSettings).length > 0){
			var generalParams = {};
			for(cell in targetGrid.FilterSettings){
				if(cell != 'undefined'){
					for(data in targetGrid.FilterSettings[cell]){
						generalParams[cell] = targetGrid.FilterSettings[cell]['data'];
						break;
					}
				}
			}

			//Если вместо grid - viewframe
			var targetGridStore =  (targetGrid.store) ? targetGrid.store : targetGrid.getGrid().store;
			Ext.getCmp(this.gridID).ViewGridModel.grid.store.baseParams.Filter = Ext.util.JSON.encode(generalParams);
			var postfix = 'FilterGrid';
			targetGridStore.url = ((targetGridStore.url).replace(postfix, '')).replace('&m=',postfix+'&m=');
			targetGridStore.load();
		}
	},
	refresh : function(){
		var type_id = (Ext.getCmp(this.gridID).gFilters && typeof(Ext.getCmp(this.gridID).gFilters.RegistryType_id) != 'undefined') ? Ext.getCmp(this.gridID).gFilters.RegistryType_id : null;
		var targetGrid = Ext.getCmp(this.gridID);

		if(typeof(targetGrid.type_id) == 'undefined'){
			targetGrid.type_id = type_id;
		}

		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText(lang['obnovit']+this.objectName+' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
	},
	/**
	 * Непосредственная фильтрация колонки
	 * paramCell - (obj)
	 type - unicFilter (пока единственный вариант),
	 cell - dataIndex +lang['kolonki']+,
	 specific - (bool) true - поиск по совподению из input, false - игнорировать
	 value - значение из инпута поля формы фильтра
	 */
	getDataFilter : function(paramCell){
		//this.clearParams();
		filter = {};
		filter.type = paramCell.type;
		filter.cell = paramCell.cell;
		filter.specific = paramCell.specific;
		filter.value = paramCell.value;

		if(typeof(paramCell) == 'object'){
			paramString = Ext.util.JSON.encode(filter);
		}
		else{
			paramString = paramCell;
			paramString = paramString.replace(/\"/g, '"');
		}

		this.targetFilterParam = paramString;

		Ext.getCmp(Ext.getCmp('swFilterGridPlugin').gridID).ViewGridModel.grid.store.baseParams.Filter = paramString;

		this.store.load({
			params: {
				limit: 100,
				start: 0
			}
		});
	},
	listeners:
	{
		'hide': function()
		{
			if (this.refresh)
				this.onHide();
		}
	},

	show: function()
	{
		$loadMask = new Ext.LoadMask(Ext.getCmp('unicGrid').getEl(), {msg: LOAD_WAIT});
		if(this.TG.getGrid().getStore().data.length==0)
		{
			alert(lang['otsutstvuyut_dannyie']);
			$loadMask.hide();
			this.refresh();
			this.hide();
		}
		else
		{
			$loadMask.show();
			sw.Promed.swFilterGridPlugin.superclass.show.apply(this, arguments);
		}
	}
});