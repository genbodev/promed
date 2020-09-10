/**
 * sw.Promed.ViewFrame. Класс базового вьюфрейма (grid с плюшками)
 *
 * @author  Марков Андрей
 *
 * @class sw.Promed.ViewFrame
 * @extends sw.Promed.BaseFrame
 */

/* Фикс Ext'а - проблема с работой чекбоксов в мультиселекте при активированном драг енд дропе
* http://redmine.swan.perm.ru/issues/74790#note-35 пункт 11
*/
// Начало фикса по 74790 

Ext.grid.RowSelectionModel.override(
{
    // FIX: added this function so it could be overrided in CheckboxSelectionModel
    handleDDRowClick: function(grid, rowIndex, e)
    {
        if(e.button === 0 && !e.shiftKey && !e.ctrlKey) {
            this.selectRow(rowIndex, false);
            grid.view.focusRow(rowIndex);
        }
    },
    
    initEvents: function ()
    {
        if(!this.grid.enableDragDrop && !this.grid.enableDrag){
            this.grid.on("rowmousedown", this.handleMouseDown, this);
        }else{ // allow click to work like normal
            // FIX: made this handler function overrideable
            this.grid.on("rowclick", this.handleDDRowClick, this);
        }

        this.rowNav = new Ext.KeyNav(this.grid.getGridEl(), {
            "up" : function(e){
                if(!e.shiftKey){
                    this.selectPrevious(e.shiftKey);
                }else if(this.last !== false && this.lastActive !== false){
                    var last = this.last;
                    this.selectRange(this.last,  this.lastActive-1);
                    this.grid.getView().focusRow(this.lastActive);
                    if(last !== false){
                        this.last = last;
                    }
                }else{
                    this.selectFirstRow();
                }
            },
            "down" : function(e){
                if(!e.shiftKey){
                    this.selectNext(e.shiftKey);
                }else if(this.last !== false && this.lastActive !== false){
                    var last = this.last;
                    this.selectRange(this.last,  this.lastActive+1);
                    this.grid.getView().focusRow(this.lastActive);
                    if(last !== false){
                        this.last = last;
                    }
                }else{
                    this.selectFirstRow();
                }
            },
            scope: this
        });

        var view = this.grid.view;
        view.on("refresh", this.onRefresh, this);
        view.on("rowupdated", this.onRowUpdated, this);
        view.on("rowremoved", this.onRemove, this);
    }
});

Ext.grid.CheckboxSelectionModel.override(
{
    // FIX: added this function to check if the click occured on the checkbox.
    //      If so, then this handler should do nothing...
    handleDDRowClick: function(grid, rowIndex, e)
    {
        var t = Ext.lib.Event.getTarget(e);
        if (t.className != "x-grid3-row-checker") {
            Ext.grid.CheckboxSelectionModel.superclass.handleDDRowClick.apply(this, arguments);
        }
    }
});

Ext.grid.GridDragZone.override(
{
    getDragData: function (e)
    {
        var t = Ext.lib.Event.getTarget(e);
        var rowIndex = this.view.findRowIndex(t);
        if(rowIndex !== false){
            var sm = this.grid.selModel;
            // FIX: Added additional check (t.className != "x-grid3-row-checker"). It may not
            //      be beautiful solution but it solves my problem at the moment.
            if ( (t.className != "x-grid3-row-checker") && (!sm.isSelected(rowIndex) || e.hasModifier()) ){
                sm.handleMouseDown(this.grid, rowIndex, e);
            }
            return {grid: this.grid, ddel: this.ddel, rowIndex: rowIndex, selections:sm.getSelections()};
        }

        return false;
    }
});

// Конец фикса по 74790

Ext.grid.CheckColumnEdit = function(config){
	Ext.apply(this, config);
	if(!this.id){
		this.id = Ext.id();
	}
	this.renderer = this.renderer.createDelegate(this);
};

Ext.grid.CheckColumnEdit.prototype ={
	init : function(grid){
		if (!this.grid) {
			if (grid.getGrid) {
				this.grid = grid.getGrid();
			} else {
				this.grid = grid;
			}
		}
		this.grid.on('render', function(){
			var view = this.grid.getView();
			view.mainBody.on('mousedown', this.onMouseDown, this);
		}, this);
	},

	onMouseDown : function(e, t){
		if (this.grid.getViewFrame().readOnly) {
			return false;
		}
		if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1 && t.classList.contains("x-item-disabled")!=true ){
			if(this.grid && this.grid.panel  && this.grid.panel.getCount()>0){
				e.stopEvent();
				var index = this.grid.getView().findRowIndex(t);
				var record = this.grid.store.getAt(index);

				var v = record.data[this.dataIndex];
				if ( String(v) == 'true' || String(v) == '1') {
					v = false;
				} else {
					v = true;
				}

				var editEvent = {
					grid: this.grid,
					record: this.grid.store.getAt(index),
					field: this.dataIndex,
					value: v,
					originalValue: record.data[this.dataIndex],
					row: index,
					column: this.grid.getColumnModel().findColumnIndex(this.dataIndex)
				};
				record.set(this.dataIndex, editEvent.value);
				this.grid.fireEvent('afteredit',editEvent);
			}
		}
	},
	renderer : function(v, p, record,rowIndex,colIndex){
		/* TODO: при отображении пустой ничего не выводить 
		*/
		if(this.grid && this.grid.panel && this.grid.panel.isEmpty()==true){
			return "";
		}

		var style ='';

		if ( v == 'hidden' ) {
			return '';
		}
		
		if ( !p ) {
			if ( String(v) == 'true' || String(v) == '1')
				return lang['da']
			else	
				return lang['net']
		}

		if ( String(v) == 'true' || String(v) == '1') {
			v = true;
		} else {
			v = false;
		}
		p.css += ' x-grid3-check-col-td';
		if (this.grid.getViewFrame().readOnly) {
			p.css += ' x-item-disabled';
		}
		if ( v == 'gray' )
			style = 'x-grid3-check-col-on-non-border-gray';
		else
			if ( v == 'red' )
				style = 'x-grid3-check-col-on-non-border-red';
			else if ( v == 'yellow' )
				style = 'x-grid3-check-col-on-non-border-yellow';
			else if ( v == 'blue' )
				style = 'x-grid3-check-col-on-non-border-blue';
			else if ( v == 'orange' )
				style = 'x-grid3-check-col-on-non-border-orange';
			else
				style = 'x-grid3-check-col'+(v?'-on':'');

		if(this.dataIndex && record.get(this.dataIndex+'_disabled') && record.get(this.dataIndex+'_disabled')  == 'disabled'){
			style += ' x-item-disabled';
		}

			return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
	}
};

Ext.grid.MultiSelectColumnEdit = function(config){
	Ext.apply(this, config);
	if(!this.id){
		this.id = Ext.id();
	}
	this.renderer = this.renderer.createDelegate(this);
};

Ext.grid.MultiSelectColumnEdit.prototype ={
	init : function(grid){
		if (!this.grid) {
			if (grid.getGrid) {
				this.grid = grid.getGrid();
			} else {
				this.grid = grid;
			}
		}
		this.grid.on('render', function(){
			var view = this.grid.getView();
			view.mainBody.on('mousedown', this.onMouseDown, this);
		}, this);
	},

	onMouseDown : function(e, t){
		var vf = this.grid.getViewFrame();
		if (vf && vf.readOnly || vf.disabledCheckBox) {
			return false;
		}
		if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
			if(this.grid && this.grid.panel  && this.grid.panel.getCount()>0){
				e.stopEvent();
				var index = this.grid.getView().findRowIndex(t);
				var record = this.grid.store.getAt(index);
				record.set(this.dataIndex, !record.data[this.dataIndex]);

				var viewframe = this.grid.getViewFrame();
				if (record.data[this.dataIndex]) {
					// ищем запись, если нет её, то засовываем
					var founded = -1;
					var key = viewframe.getKey();
					var checkedArr = viewframe.checkedArray;
					for(var k in checkedArr) {
						if (checkedArr[k].get && checkedArr[k].get(key) == record.get(key)) { // сравниаем по ключу.
							founded = k;
						}
					}
					if (founded == -1) {
						viewframe.checkedArray.push(record);
					}
				} else {
					// ищем запись и убираем из массива помеченных записей
					var founded = -1;
					var key = viewframe.getKey();
					var checkedArr = viewframe.checkedArray;
					for(var k in checkedArr) {
						if (checkedArr[k].get && checkedArr[k].get(key) == record.get(key)) { // сравниаем по ключу.
							founded = k;
						}
					}
					if (founded > -1) {
						viewframe.checkedArray.splice(founded, 1);
					}
				}
				record.commit();
				if (viewframe.onSetMultiSelectValue) {
					viewframe.onSetMultiSelectValue(record,record.data[this.dataIndex]);
				}
				if (viewframe.onMultiSelectChange) {
					viewframe.onMultiSelectChange();
				}
				viewframe.syncMultiSelectHeader();
			}
		}
	},
	renderer : function(v, p, record){
		/* TODO: при отображении пустой ничего не выводить
		 */
		if(this.grid && this.grid.panel && this.grid.panel.isEmpty()==true){
			return "";
		}

		if ( v == 'hidden' ) {
			return '';
		}

		if ( !p ) {
			if ( v == 'true' || String(v) == '1')
				return 'Да'
			else
				return 'Нет'
		}
		p.css += ' x-grid3-check-col-td';
		var vf = this.grid.getViewFrame();
		if(vf && vf.disabledCheckBox)
			p.css += ' x-item-disabled';
		return '<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
	}
};

sw.Promed.PagingToolbar = Ext.extend(Ext.PagingToolbar, {
	viewframe: null,
	updateInfo: function(){
		if(this.displayEl){
			var count = this.store.getCount();

			var endMsg = this.store.getTotalCount();
			if (this.store.overLimit) {
				endMsg = "<span class='link getCount'>всех</span>";
			}

			var msg = count == 0 ?
				this.emptyMsg :
				String.format(
					this.displayMsg,
					this.cursor+1, this.cursor+count, endMsg
				);
			this.displayEl.update(msg);
		}
	},
	doLoad : function(start){
		var o = {}, pn = this.paramNames;
		o[pn.start] = start;
		o[pn.limit] = this.pageSize;
		if(this.fireEvent('beforechange', this, o) !== false){
			this.store.load({params:o, saveTotalLength: !this.store.overLimit}); // saveTotalLength: true - не обновлять totalLength при загрузке сторе
		}
	},
	onLoad: function(store, r, o){
		if(!this.rendered){
			this.dsLoaded = [store, r, o];
			return;
		}
		this.lastOptions = [store, r, o];
		if (!this.cursor || !o.add) {
			this.cursor = o.params ? o.params[this.paramNames.start] : 0;

			// если есть в базовых параметрах берём от туда.
			if (!o.params && store.baseParams[this.paramNames.start]) {
				this.cursor = store.baseParams[this.paramNames.start];
			}
		}
		var d = this.getPageData(), ap = d.activePage, ps = d.pages;

		if (this.store.overLimit) {
			this.afterTextEl.el.innerHTML = String.format(this.afterPageText, "<span class='link getCount'>всех</span>");
		} else {
			this.afterTextEl.el.innerHTML = String.format(this.afterPageText, d.pages);
		}
		this.field.dom.value = ap;
		this.first.setDisabled(ap == 1);
		this.prev.setDisabled(ap == 1);
		if (this.store.overLimit) {
			this.next.setDisabled(false);
			this.last.setDisabled(true);
		} else {
			this.next.setDisabled(ap == ps);
			this.last.setDisabled(ap == ps);
		}
		this.loading.enable();
		this.updateInfo();
		this.fireEvent('change', this, d);
	},
	onRender: function (ct, position) {
		sw.Promed.PagingToolbar.superclass.onRender.call(this, ct, position);

		var pgtoolbar = this;
		var viewframe = this.viewframe;

		this.getEl().on('click', function(e, t) {
			var el = e.getTarget('.getCount', 5);
			if (el) { // если кликнули по ссылочке на загрузку каунта, то каунт и загружаем :)
				if (viewframe) {
					viewframe.getLoadMask("Загрузка количества записей").show();

					var params = swCloneObject(viewframe.getGrid().getStore().baseParams); // те же параметры
					params.getCountOnly = 1; // но грузим только каунт

					Ext.Ajax.request({
						url: viewframe.dataUrl,
						params: params,
						callback: function (opt, success, response) {
							viewframe.getLoadMask().hide();

							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.totalCount) {
									// теперь мы знаем количество записей
									viewframe.getGrid().getStore().overLimit = false;
									viewframe.getGrid().getStore().totalLength = response_obj.totalCount;
									if (pgtoolbar.lastOptions) {
										pgtoolbar.onLoad(pgtoolbar.lastOptions[0], pgtoolbar.lastOptions[1], pgtoolbar.lastOptions[2]);
									}
								}
							}
						}
					});
				}
			}
		});
	}
});

sw.Promed.ViewFrame = function(config)
{
	Ext.apply(this, config);
	sw.Promed.ViewFrame.superclass.constructor.call(this);
};

Ext.extend(sw.Promed.ViewFrame, sw.Promed.BaseFrame,
{
	/**
	 * При загрузке грида скроллироваться на начало
	 */
	isScrollToTopOnLoad: true,
	enableAudit: true,
	region: 'center',
	layout: 'fit',
	id: 'viewframe',
	sortInfo: {},
	clearSelectionsOnTab: true,
	collapsible: false,
	height: 203,
	/**
	*
	*/
	tbActions:false,
	stringfields: [],
	noFocusOnLoadOneTime: false,
	groupSortInfo: false,
	blockLoadArchive: false,
	notArchRecords: 0,
	archRecords: 0,
	preToolbarItems: [],
	showCountInTop: true,
	checkBoxWidth: 20,
	groups:true,
	object: '',
	obj_isEvn: true, // если true, то object реально является Evn (событием)
	titleGrid: '',
	dataUrl: '',
	actions:'',
	ownerWindow: null,
	toolbar: true,
	disableActions:true,
	editformclassname:'',
	autoLoadData: true,
	focusOnFirstLoad: true, // если указать false то фокус после обновления ставиться не будет
	readOnly: false,
	denyEdit: false,
	remoteSort: false,
	disField: 'Person_deadDT',
	denyActions: function() {
		if(this.disableActions||this.disableActions==null){
		
			// дисаблим всё, кроме просмотр, печать, обновить
			for (var k in this.ViewActions) {
				comp = this.ViewActions[k];
				if (typeof comp == 'object' && typeof comp.setDisabled == 'function' && typeof comp.getText == 'function' && comp.getText() && !comp.getText().inlist([BTN_GRIDVIEW,BTN_GRIDREFR,BTN_GRIDPRINT,'Открыть отчет'])) {
					comp.setDisabled(true);
					comp.initialConfig.initialDisabled = true;
					comp.setDisabled = function() { // не даём пользоваться больше этой функцией для этого компонента.
						return false;
					}
				}
			}
		}
		
	},
	useEmptyRecord: true,
	// Настройки редактируемого грида
	clicksToEdit: 'auto',
	gridHeight: '',
	saveAtOnce: true, // Немедленное сохранение
	saveAllParams: false, // Передавать парамсы при сохранении
	processSave: false, // Признак процесса сохранения, используется только в самом фрейме
	ViewContextMenu: null,
	selectionModel: 'row',
	multi:false,
	singleSelect: true,
	hideHeaders: false,
	plugins: [],
	gridplugins: [],
	headergrouping: false,
	forceFit: false,
	enableDragDrop: false,
	enableColumnMove: true,
	passPersonEvn: false,
	contextmenu: true,
	scheme: 'dbo',
	linkedTables: '', // Строка, связанные таблицы через запятую. Используется для удаления записей из связанных таблиц.
	stateful: false,
	enableColumnHide:true,
	disableCheckRole: false,
	printWithNumberColumn: false,
	forcePrintMenu: false,
	notApplyStringFields: false, // Не добавлять в запрос поля из грида
	disabledCheckBox: false, // Заблокировать нажатие на чекбоксы при multiselect2
	/**
	 * Параметр для определения доступности горячих клавиш
	 * Доступны F6, F10, F11, F12 и "производные": Alt+F6, Ctrl+F12
	 * Примеры: 
	 *		allowedPersonKeys: [] - запрещаем все горячие клавиши
	 *		allowedPersonKeys: ['F6', 'F11', 'F12'] - разрешаем только указанные горячие клавиши
	 */
	allowedPersonKeys: null,
	/**
	 * Перенос строк в гриде, по умолчанию - не переносит
	 **/
	transferLine: false,
	// Блокируем чекбоксы и общий выделитель в header-e
	// только для модели multiselect2
	setDisabledCheckBox: function(ch){
		var vf = this;
		vf.disabledCheckBox = ch;
		if ( this.selectionModel == 'multiselect2' ) {
			var g = vf.getGrid(),
				v = g.getView(),
				cont = g.container;

			if (v) v.refresh();
			var i = cont.child('input#' + vf.id + 'MultiArrayCheckBox');
			if (i) i.dom.disabled = ch;
		}
	},
	/**
	 * Проверяет доступность горячей клавиши по переданному параметру
	 */
	checkPersonKey: function(key) {
		/*
		https://redmine.swan.perm.ru/issues/19454
		- при создании грида с параметром allowedPersonKeys: ['F6', 'F11', 'F12'] - разрешать доступ только по указанным горячим клавишам
		- при передаче пустого параметра allowedPersonKeys: [] - не разрешать использовать горячие клавиши, предоставляющие допинформацию о пациенте
		- при отсутствии параметра allowedPersonKeys - разрешать использовать hotkeys, предоставляющие допинформацию о пациенте
		*/
		if (this.allowedPersonKeys == null) {
			return true;
		}
		if (typeof this.allowedPersonKeys == 'object') {
			if (key.inlist(this.allowedPersonKeys)) {
				return true;
			} else {
				return false;
			}
		}
	},
	addActions: function (action, pos)
	{
		var x = this.ViewToolbar.items.getCount();

		var check = true;
		if ((!action.name) || (action.name.inlist(['action_add', 'action_edit', 'action_view', 'action_delete', 'action_refresh', 'action_print', 'action_save', 'action_resetfilter'])))
		{
			alert(lang['ne_ukazano_name_u_akshena_dobavlyaemogo_v_actions_grid_ili_name_perekryivaet_imya_standartnogo_akshena']);
			return false;
		}
		if (this.ViewActions[action.name])
		{
			check = false;
		}

		if (check)
		{
			if (!action.id)
			{
				action.id = 'id_'+action.name;
			}
			if (this.denyEdit) {
				action.hidden = true;
				action.disabled = true;
			}
			this.ViewActions[action.name] = new Ext.Action(action);
			// определяем позицию для вставки кнопки
			var position = 5;
			if (!this.ViewActions.action_refresh.isHidden()) {
				position = position+2;
			}
			if (!this.ViewActions.action_print.isHidden()) {
				position = position+2;
			}

			if (!Ext.isEmpty(pos)) {
				position = pos;
			}

			this.ViewToolbar.insertButton(position,this.ViewActions[action.name]);

			if (Ext.isEmpty(pos)) {
				if (this.ViewContextMenu.items.item(position-1)!='-')
					this.ViewContextMenu.add('-');
			}

			this.ViewContextMenu.add(this.ViewActions[action.name]);
		}
		return check;
	},
	setReadOnly: function (m)
	{
		// как бы можно ставить режим в любой момент
		this.readOnly = m;
		if (m==true)
		{
			if (!this.ViewActions.action_add.initialConfig.initialDisabled)
				this.ViewActions.action_add.setDisabled(true);
			if (!this.ViewActions.action_edit.initialConfig.initialDisabled)
				this.ViewActions.action_edit.setDisabled(true);
			if (!this.ViewActions.action_view.initialConfig.initialDisabled)
				this.ViewActions.action_view.setDisabled(false);
			if (!this.ViewActions.action_delete.initialConfig.initialDisabled)
				this.ViewActions.action_delete.setDisabled(true);
		}
		else
		{
			if (!this.ViewActions.action_add.initialConfig.initialDisabled)
				this.ViewActions.action_add.setDisabled(false);
			if (this.getCount()>0)
			{
				if (!this.ViewActions.action_edit.initialConfig.initialDisabled)
					this.ViewActions.action_edit.setDisabled(false);
				if (!this.ViewActions.action_view.initialConfig.initialDisabled)
					this.ViewActions.action_view.setDisabled(false);
				if (!this.ViewActions.action_delete.initialConfig.initialDisabled)
					this.ViewActions.action_delete.setDisabled(false);
			}
			else
			{
				if (!this.ViewActions.action_edit.initialConfig.initialDisabled)
					this.ViewActions.action_edit.setDisabled(true);
				if (!this.ViewActions.action_view.initialConfig.initialDisabled)
					this.ViewActions.action_view.setDisabled(true);
				if (!this.ViewActions.action_delete.initialConfig.initialDisabled)
					this.ViewActions.action_delete.setDisabled(true);
			}
		}
	},
	setEditFormClassName: function (newform)
	{
	this.editformclassname = newform;
	},
	hasPersonData: function() // в гриде есть данные о человеке
	{
		return this.ViewGridPanel.getStore().fields.containsKey('Person_id') && this.ViewGridPanel.getStore().fields.containsKey('Server_id');
	},
	hasDrugData: function() // в гриде есть данные о медикаменте
	{
		return this.ViewGridPanel.getStore().fields.containsKey('Drug_id');
	},
	topTextCounter: null,
	clearTopTextCounter: function() {
		if (this.topTextCounter) {
			this.topTextCounter.el.innerHTML = '0 / 0';
		}
	},
	removeAll: function(options)
	{
		if ( !options ) { options = new Object(); }

		// Значения опций по умолчанию
		if ( options.addEmptyRecord == undefined || options.addEmptyRecord == null ) {
			options.addEmptyRecord = true;
		}

		if ( options.clearAll == undefined || options.clearAll == null ) {
			options.clearAll = false;
		}

		if ( options.clearAll )
		{
			this.loadCount = -1;
			this.params = null;
			this.gFilters = null;
			if (this.getEl())
			{
				this.ViewGridPanel.getStore().baseParams =  Ext.apply({}, this.jsonData['base']);
			}
		}
		if (this.getEl())
		{
			this.clearTopTextCounter();
			
			if (this.useEmptyRecord && options.addEmptyRecord)
			{
				this.addEmptyRecord(this.ViewGridPanel.getStore());
			}
			else 
			{
				this.ViewGridPanel.getStore().removeAll();
			}
		}
		// обновление количества отображаемых строк / приватный метод
		if (this.ViewGridPanel.getBottomToolbar())
			this.ViewGridPanel.getBottomToolbar().updateInfo();

		if(this.pagingBBar && this.pagingBBar.store) {// если есть пейджер
			this.pagingBBar.store.overLimit = false;// чтобы не выводило "всех" вместо 1
			this.pagingBBar.onLoad(this.pagingBBar.store, null, {});// обновление элементов управления
		}
	},
	// Проверка на пустую запись 
	isEmpty: function() {
		if (!this.getGrid().getStore().data.items[0].data[this.getGrid().getStore().idProperty]) {
			return true;
		}
		return false;
	},
	getCount: function()
	{
		if (this.rowCount)
			return this.rowCount;
		else
			return 0;
	},
	getGrid: function ()
	{
		return this.ViewGridPanel;
	},
	addEmptyRecord: function(store)
	{
		if (this.useEmptyRecord) {
			// Создаем пустую строку
			var viewframe = this;
			var EmptyRecord = Ext.data.Record.create(viewframe.jsonData['store']);
			store.removeAll();
			store.insert(0, new EmptyRecord(viewframe.jsonData['emply']));
			// для того чтобы не было 1-1 из 0 в перйджинге:
			store.totalLength = 1;
		}
	},
	removeEmptyRecord: function(store) {
		var store = this.getGrid().getStore();
		index = store.findBy(function(record) {
			if (record.get('archiveRecord') == null) {
				return true;
			} else {
				return false;
			}
		});
		store.removeAt(index);
	},
	loadData: function(prms)
	{
		//log('loadData')

		if (!this.params) this.params = null;
		if (!this.gFilters) this.gFilters = null;

		this.noFocusOnLoad = false;
			
		if (prms) {

			if (prms.params) this.params = prms.params;
			if (prms.globalFilters) this.gFilters = prms.globalFilters;
			if (prms.noFocusOnLoad) this.noFocusOnLoad = true;
			if (prms.url) this.setDataUrl(prms.url); // В параметрах можно задать url

			// В параметрах можно передать valueOnFocus (значение ключевого поля в гриде для того, чтобы установить фокус на эту запись)
			if (prms.valueOnFocus) this.setValueOnFocus(prms.valueOnFocus);
			}

		this.ViewGridPanel.getStore().removeAll();
		this.ViewGridPanel.getStore().load(
			{
				params: this.gFilters,
				callback: (prms && prms.callback)?prms.callback:null || null
			}
		);
	},
	//Всё о чём вы так давно мечтали, смена URL грида на лету
	setDataUrl: function(url)
	{
		this.ViewGridPanel.dataUrl = url;
		this.ViewGridPanel.getStore().proxy = new Ext.data.HttpProxy({
			url: this.ViewGridPanel.dataUrl
		});
	},
	/** Функция получает значение параметра по его наименованию
	*	по умолчанию global
	*/
	getParam: function(param, global)
	{
		if ((global==undefined) || global)
		{
			if (this.ViewGridPanel.getStore().baseParams[param])
				return this.ViewGridPanel.getStore().baseParams[param];
			else
				return null;
		}
		else
		{
			if (this.params[param])
				return this.params[param];
			else
				return null;
		}
	},
	setParam: function(param, value, global)
	{
		if ((global==undefined) || global)
		{
			if (!this.gFilters)
				this.gFilters = new Object();
			this.gFilters[param] = value;
			this.ViewGridPanel.getStore().baseParams[param] = value;
			return this.ViewGridPanel.getStore().baseParams[param];
		}
		else
		{
			if (!this.params)
				this.params = new Object();
			this.params[param] = value;
			return this.params[param];
		}
		return null;
	},
	getAction: function(action)
	{
		if (this.ViewActions[action])
			return this.ViewActions[action];
		else
			return null;
	},
	runAction: function(action)
	{
		if (this.ViewActions[action])
		{
			this.ViewActions[action].execute();
			return true;
		}
		else
			return false;
	},
	setActionHidden: function(action, flag)
	{
		if (this.ViewActions[action])
		{
			this.ViewActions[action].setHidden(flag);
		}
	},
	setActionText: function(action, text)
	{
		if (this.ViewActions[action])
		{
			this.ViewActions[action].setText(text);
		}
	},
	setActionDisabled: function(action, flag)
	{
		if (this.ViewActions[action])
		{
			this.ViewActions[action].initialConfig.initialDisabled = flag;
			this.ViewActions[action].setDisabled(flag);
		}
	},
	noSelectFirstRowOnFocus: false,
	focus: function () // Устанавливаем фокус принудительно, возможно применение только после лоад
	{
		if (!this.noSelectFirstRowOnFocus && this.ViewGridPanel.getStore().getCount()>0)
		{
			var index = this.getIndexOnFocus();
			this.getGrid().getView().focusRow(index);
			this.ViewGridPanel.getView().focusRow(0);
			if (this.selectionModel!='cell')
				this.getGrid().getSelectionModel().selectRow(index);
		}
	},
	addListenersFocusOnFields: function()
	{
		// Обычное поле и фокус OnShiftTab
		if (this.focusPrev && (this.focusPrev.type == 'field'))
		{
			var d = this;
			Ext.getCmp(this.focusPrev.name).enableKeyEvents = true;
			Ext.getCmp(this.focusPrev.name).addListener('keydown', function(f,e)
			{
				if (!e.shiftKey && e.getKey() == e.TAB)
				{
					e.stopEvent();
					e.browserEvent.returnValue = false;
					e.returnValue = false;
					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					d.focus();
				}
			});
		}
		if (this.focusPrev && (this.focusPrev.type == 'button'))
		{
			Ext.getCmp(this.focusPrev.name).enableKeyEvents = true;
			var d = this;
			Ext.getCmp(this.focusPrev.name).onTabAction = function () {d.focus()}
		}
		if (this.focusOn && (this.focusOn.type == 'field'))
		{
			var d = this;
			Ext.getCmp(this.focusOn.name).enableKeyEvents = true;
			Ext.getCmp(this.focusOn.name).addListener('keydown', function(f,e)
			{
				if (e.shiftKey && e.getKey() == e.TAB)
				{
					e.stopEvent();
					e.browserEvent.returnValue = false;
					e.returnValue = false;
					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					d.focus();
				}
			});
		}
		if (this.focusOn && (this.focusOn.type == 'button'))
		{
			//if (Ext.getCmp(this.focusOn.name))
			Ext.getCmp(this.focusOn.name).enableKeyEvents = true;
			var d = this;
			Ext.getCmp(this.focusOn.name).onShiftTabAction = function () {d.focus()}
		}
	},
	/** Функция ищет первую редактируемую ячейку текущей записи и устанавливает режим редактирования
	*
	*/
	setFirstEditRecord: function ()
	{
		var viewframe = this;
		var grid = viewframe.ViewGridPanel;
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		var indexRow = grid.getStore().indexOf(record);

		if (grid.getColumnModel())
		{
			for (i=0; i < grid.getColumnModel().config.length; i++)
			{
				if (grid.getColumnModel().isCellEditable(i, indexRow))
				{
					//record.beginEdit();
					grid.startEditing(indexRow, i);
					break;
				}
			}
		}
	},
	/** Функция скрывает колонку грида
	* 
	*/
	setColumnHidden: function (dataIndex, hidden)
	{
		var grid = this.ViewGridPanel;
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
			return false;
		}
		hidden == (!hidden)?false:hidden;
		if (grid.getColumnModel())
		{
			var cm = grid.getColumnModel();
			var index = cm.findColumnIndex(dataIndex);
			if (index>=0)
			{
				cm.setHidden(index, hidden);
			}
		}
		return null;
	},
	setColumnWidth: function (dataIndex, width)
	{
		var grid = this.ViewGridPanel;
		if (!grid) {
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
			return false;
		}
		if (grid.getColumnModel()) {
			var cm = grid.getColumnModel();
			var index = cm.findColumnIndex(dataIndex);
			if (index>=0) {
				cm.setColumnWidth(index, width);
			}
		}
		return null;
	},
	/** Устанавливаем заголовок колонки 
	* 
	*/
	setColumnHeader: function (dataIndex, header)
	{
		var grid = this.ViewGridPanel;
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
			return false;
		}
		header == (!header)?'':header;
		if (grid.getColumnModel())
		{
			var cm = grid.getColumnModel();
			var index = cm.findColumnIndex(dataIndex);
			if (index>=0)
			{
				cm.setColumnHeader(index, header);
			}
		}
		return null;
	},
	/**
	 * Возвращает наименование ключа для объекта БД
	 */
	getKey: function() {
		return this.jsonData['key_id'];
	},
	/**
	 * Возвращает индекс записи по Id записи
	 * @param params
	 */
	getIndexByValue: function(value) {
		var key, val, index = -1;
		if (typeof value == 'object') { // если значение является объектом, значит оно содержит наименование поля (по которому нужно выполнять поиск) и его значение
			key = getFirstKey(value);
			val = getFirstValue(value);
			if (key && val) {
				index = this.getGrid().getStore().findBy(function(record) {
					if (record.get(key) == val) {
						return true;
					} else {
						return false;
					}
				});
			}
		} else { // иначе это просто значение и искать будем по ключевому полю
			key = this.getKey();
			val = value;
			index = this.getGrid().getStore().indexOfId(val);
		}
		return index;
	},
	/**
	 * Сохраняет значение индекса по значению параметра
	 */
	setIndexByValue: function(value) {
		this.setSelectedIndex(this.getIndexByValue(value));
	},
	
	/**
	 * Сохраняет текущее значение индекса выделенной записи
	 */
	saveSelectedIndex: function() {
		var record = this.getGrid().getSelectionModel().getSelected();
		if (record) {
			this.selectedIndex = this.getGrid().getStore().indexOf(record);
		} else {
			this.selectedIndex = null;
		}
	},
	/**
	 * Возвращает значение индекса, если запись реально существует
	 */
	getIndexOnFocus: function() {
		var index = 0;
		if (this.selectedIndex>0) {
			index = (this.getGrid().getStore().getAt(this.selectedIndex))?this.selectedIndex:0;
		}
		return index;
	},
	getSelectedIndex: function() {
		return this.selectedIndex;
	},
	setSelectedIndex: function(index) {
		this.selectedIndex = index;
	},
	setValueOnFocus: function(v) {
		this.valueOnFocus = v;
	},
	/** Функция возвращает  записи из базового грида и таблицы
	*
	*/
	getColumnModel: function ()
	{
		var grid = this.getGrid();
		if (grid) {
			return grid.getColumnModel();
		} else {
			return null;
		}
	},
	/**
	 * Проверка - разрешено ли удалять записи
	 */
	deniedDeleteRecord: function() {
		return false
	},
	/** Функция удаления записи из базового грида и таблицы
	*
	*/
	deleteRecord: function ()
	{
		var viewframe = this;
		var grid = viewframe.ViewGridPanel;
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(viewframe.jsonData['key_id']) ) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected(),
			RegistryType_id = selected_record.data['RegistryType_id'];


		var params = {object:viewframe.object, obj_isEvn: viewframe.obj_isEvn, scheme: viewframe.scheme, linkedTables: viewframe.linkedTables, RegistryType_id: RegistryType_id };
		
		if (this.selectionModel == 'multiselect') {
			var selections = grid.getSelectionModel().getSelections();
			var ids = [];
			for	(var key in selections) {
				if (selections[key].data) {
					ids.push(selections[key].data[viewframe.jsonData['key_id']]);
				}
			}
			params.ids = Ext.util.JSON.encode(ids);
		} else {
			var keyField = viewframe.jsonData['key_id'];
			var id = grid.getSelectionModel().getSelected().data[keyField];
			params.id = id;
			if(viewframe.deleteType == 'object') {
				params[keyField] = id;
			}
		}
		var delMessage = lang['vyi_hotite_udalit_zapis'];
		if (grid.getSelectionModel().getCount() > 1) {
			delMessage = lang['vyi_hotite_udalit_zapisi'];
		}

		if(viewframe.deniedDeleteRecord())
			return false;

		sw.swMsg.show(
		{
			icon: Ext.MessageBox.QUESTION,
			msg: (viewframe.ViewActions.action_delete.initialConfig.msg!='')?viewframe.ViewActions.action_delete.initialConfig.msg:delMessage,
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					var loadMask = new Ext.LoadMask(viewframe.getEl(), {msg:lang['udalenie']});
					loadMask.show();

					var url = (viewframe.ViewActions.action_delete.initialConfig.url!='')?viewframe.ViewActions.action_delete.initialConfig.url: C_RECORD_DEL;

					if(viewframe.deleteType == 'object') {
						url = '/?c=' + viewframe.object + '&m=delete';
					}

					Ext.Ajax.request(
					{
						url: url,
						params: params,
						failure: function(response, options)
						{
							loadMask.hide();
							Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
						},
						success: function(response, action)
						{
							loadMask.hide();
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
	},
	/**
	* Функция вызывается по клику на ячейке
	* @param {Grid} GridPanel
	* @param {Number} rowIndex
    * @param {Number} columnIndex
	* @param {Ext.EventObject} e
	*/
	onCellMouseDown : function(grid, rowInd, colInd, e) {
		if (this.filterByFieldEnabled) {
			// получаем значение в этой ячейке и название столбца, сохраняем для возможной фильтрации по этой ячейке
			var fieldName = grid.getColumnModel().getDataIndex(colInd);
			var record = grid.getStore().getAt(rowInd);
			var data = record.get(fieldName);
			this.filterData = {};
			this.filterData[fieldName] = data;
		}
	},
	filterByFieldEnabled: false,
	// Фильтрация по ячейке
	filterByField: function() {
		var viewframe = this;
		if (viewframe.filterByFieldEnabled && !Ext.isEmpty(viewframe.filterData)) {
			var grid = viewframe.getGrid();
			grid.getStore().reload({
				params: {
					filterData: Ext.util.JSON.encode(viewframe.filterData)
				}
			});
			viewframe.ViewActions.action_resetfilter.setHidden(false);
		}
	},
	// сброс фильтрации
	filterReset: function() {
		var viewframe = this;
		var grid = viewframe.getGrid();
		if (viewframe.filterByFieldEnabled && !Ext.isEmpty(grid.getStore().baseParams.filterData)) {
			grid.getStore().reload({
				params: {
					filterData: null
				}
			});
		}
		viewframe.ViewActions.action_resetfilter.setHidden(true);
	},
	filterData: null,
	/**
	 * Функции вызова печати данных грида
	 */
	printRecords: function()
	{
		Ext.ux.GridPrinter.print(this.ViewGridPanel);
	},
	printObject: function()
	{
		var record = this.getGrid().getSelectionModel().getSelected();
		if (record && !Ext.isEmpty(record.id)) {
			Ext.ux.GridPrinter.print(this.ViewGridPanel, {rowId: record.id, addNumberColumn: this.printWithNumberColumn});
		}
	},
	printObjectList: function()
	{
		Ext.ux.GridPrinter.print(this.ViewGridPanel, {addNumberColumn: this.printWithNumberColumn});
	},
	printObjectListSelected: function()
	{
		var viewframe = this;
		var grid = viewframe.ViewGridPanel;
		var selections = grid.getSelectionModel().getSelections();
		Ext.ux.GridPrinter.print(grid, {
			selections: selections, addNumberColumn: this.printWithNumberColumn
		});
	},
	printObjectListFull: function(ignoreGridData, additionalParams, maskTarget, fields)
	{
		var viewframe = this;
		var store = viewframe.getGrid().getStore();

		if ( typeof additionalParams != 'object' ) {
			additionalParams = new Object();
		}

		if (
			ignoreGridData == true
			|| (
				viewframe.paging && (store.getCount() < store.getTotalCount() || store.overLimit == true)
			)
		) {
			var loadMask = new Ext.LoadMask(maskTarget || viewframe.getEl(), {msg:lang['poluchenie_dannyih_dlya_pechati']});
			loadMask.show();

			var
				callback = Ext.emptyFn,
				fieldsToShow = new Array(),
				tmpGrid = viewframe.getGrid().cloneConfig();

			if ( typeof fields == 'object' && tmpGrid.getColumnModel() ) {
				var cm = tmpGrid.getColumnModel();

				for ( var i in cm.config ) {
					if ( !Ext.isEmpty(cm.config[i]['dataIndex']) && !cm.config[i]['dataIndex'].inlist(fields) && !cm.isHidden(i) ) {
						fieldsToShow.push(cm.config[i]['dataIndex']);
						cm.setHidden(i, true);
					}
				}
			}

			if ( fieldsToShow.length > 0 ) {
				callback = function() {
					var i, index;

					for ( i in fieldsToShow ) {
						if ( typeof fieldsToShow[i] == 'string' ) {
							index = cm.findColumnIndex(fieldsToShow[i]);

							if ( index >= 0 ) {
								cm.setHidden(index, false);
							}
						}
					}
				}
			}

			tmpGrid.store = new sw['Promed'][(viewframe.grouping)?'GroupingStore':'Store']({
				autoLoad: false,
				reader: (viewframe.grouping)?viewframe.reader:null,
				fields: (!viewframe.grouping)?viewframe.jsonData['store']:null,
				baseParams: Ext.apply(store.baseParams, additionalParams),
				sortInfo: (viewframe.groupSortInfo)?viewframe.groupSortInfo:null,
				url: (viewframe.ViewGridPanel.dataUrl ? viewframe.ViewGridPanel.dataUrl : viewframe.dataUrl),
				idProperty: viewframe.jsonData['key_id'],
				root: (viewframe.root)?viewframe.root:null,
				totalProperty: (viewframe.totalProperty)?viewframe.totalProperty:null,
				groupField: (viewframe.grouping)?viewframe.jsonData['groupField']:null,
				pruneModifiedRecords: (viewframe.pruneModifiedRecords) ? this.pruneModifiedRecords : false, //false по умолчанию 
				useArchive: null
			});

			if ( typeof viewframe.sortInfo == 'object' && !Ext.isEmpty(viewframe.sortInfo.field) ) {
				tmpGrid.store.sortInfo = viewframe.sortInfo;
			}

			tmpGrid.getStore().baseParams.start = 0;
			tmpGrid.getStore().baseParams.limit = 100000;
			tmpGrid.getStore().load({callback: function(){
				loadMask.hide();

				if ( viewframe.showArchive == true ) {
					tmpGrid.getStore().baseParams.useArchive = 1;

					//viewframe.getLoadMask(lang['zagruzka_arhivnyih_zapisey']).show();
					var loadMaskTmp = new Ext.LoadMask(maskTarget || viewframe.getEl(), {msg:lang['zagruzka_arhivnyih_zapisey']});
					loadMaskTmp.show();

					Ext.Ajax.request({
						url: viewframe.dataUrl,
						params: tmpGrid.getStore().baseParams,
						callback: function (opt, success, response) {
							//viewframe.getLoadMask().hide();
							loadMaskTmp.hide();

							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								tmpGrid.getStore().loadData(response_obj, true);
							}

							Ext.ux.GridPrinter.print(tmpGrid, {addNumberColumn: viewframe.printWithNumberColumn, excel: viewframe.EXCEL});
							callback();
						}
					});
				}
				else {
					Ext.ux.GridPrinter.print(tmpGrid, {addNumberColumn: viewframe.printWithNumberColumn, excel: viewframe.EXCEL});
					callback();
				}
			}});
		} else {
			Ext.ux.GridPrinter.print(viewframe.ViewGridPanel, {addNumberColumn: viewframe.printWithNumberColumn, excel: viewframe.EXCEL});
		}
	},
	/** Функция обновления данных, может работать в двух режимах:
	*  1. Просто установить/вернуть фокус на грид
	*  2. Обновить данные и поставить фокус на первую запись
	* Параметры:
	* @viewframe : объект
	* @kid : значение id записи таблицы. Если значение больше или равно нулю, то данные обновляются.
	*/
	refreshRecords: function(viewframe, kid, records, mode)
	{
		//log(viewframe);
		if ((viewframe!=null) && (viewframe.id!=this.id))
		{
			this.hide();
		}
		viewframe = viewframe || this;
		if (!viewframe)
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_pri_vyibore_obyekta_viewframe']);
			return false;
		}
		viewframe.ViewActions.action_refresh.setDisabled(true);
		var grid = viewframe.ViewGridPanel;
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_pri_vyibore_obyekta_grid']);
			return false;
		}
		if (kid>=0)
		{
			if ((records) && (mode!=undefined))
			{
				if (viewframe.getCount()==0)
				{
					grid.getStore().removeAll();
				}
				// Доделать пересчет кол-ва при добавлении и все что может потребоваться
				setGridRecord(grid,records,mode);
				if (!viewframe.ViewActions.action_refresh.initialConfig.initialDisabled)
					viewframe.ViewActions.action_refresh.setDisabled(false);
			}
			else
			{
				viewframe.loadData();
			}
			if ((kid>0) && (viewframe.afterSaveEditForm))
			{
				viewframe.afterSaveEditForm(kid, records);
			}
		}
		else
		{
			if (grid.getStore().getCount()>0)
			{
				grid.getView().focusRow(0);
			}
			if (!viewframe.ViewActions.action_refresh.initialConfig.initialDisabled)
				viewframe.ViewActions.action_refresh.setDisabled(false);
		}
		if (viewframe.onRefresh)
		{
			viewframe.onRefresh();
		}
	},
	toggleArchiveRecords: function(button) {
		
		var viewframe = this;
		if (viewframe.showArchive) {
			if (viewframe.ownerWindow && viewframe.ownerWindow.getFilterForm) {
				viewframe.ownerWindow.getFilterForm().getForm().findField('autoLoadArchiveRecords').setValue(false);
			}
			viewframe.showArchive = false;
			// обнуляем
			viewframe.notArchRecords = 0;

			/*
				 // удалить все архивные записи
				 viewframe.getGrid().getStore().each(function(rec) {
				 if (rec.get('archiveRecord') == 1) {
				 viewframe.getGrid().getStore().remove(rec);
				 }
				 });

				 viewframe.getGrid().getStore().totalCount = viewframe.notArchRecords;
				 // viewframe.pagingBBar.onLoad();
			 */

			viewframe.getGrid().getStore().reload();

			button.innerHTML = lang['pokazat'];
		} else {
			if (viewframe.ownerWindow && viewframe.ownerWindow.getFilterForm) {
				viewframe.ownerWindow.getFilterForm().getForm().findField('autoLoadArchiveRecords').setValue(true);
			}
			viewframe.showArchive = true;
			viewframe.noFocusOnLoadOneTime = true; // в эту загрузку не нужно будет переставлять фокус
			viewframe.loadArchiveRecords();
			button.innerHTML = lang['skryit'];
		}
	},
	/**
	 *	Загрузка архивных записей
	 */
	loadArchiveRecords: function() {
		var viewframe = this;

		viewframe.ViewGridPanel.getView().fireEvent('refresh');

		if (viewframe.showArchive) {
			// 1. проверяем есть ли на данной странице не архивные записи
			var notArchRecCount = 0;
			var archRecCount = 0;
			viewframe.getGrid().getStore().each(function (rec) {
				if (rec.get('archiveRecord') == 1) {
					archRecCount++;
				} else if (rec.get('archiveRecord') == 0) {
					notArchRecCount++;
				}
			});

			if (viewframe.paging) {
				// с пейджингом грузим только если неархивных меньше чем количество записей на странице, либо если последняя страница
				var pageData = viewframe.pagingBBar.getPageData();
			}

			// 2. проверяем кол-во записей, запоминаем точку отсчёта для пейджинга по архивным записям
			var loadArchiveRecords = false;
			if (archRecCount == 0) {
				if (viewframe.paging) {
					if (pageData.activePage == pageData.pages || notArchRecCount < viewframe.pageSize) {
						loadArchiveRecords = true;
					}
				} else {
					log(lang['peydjing_ne_vklyuchen']);
					// без пейджинга грузим записи точно
					loadArchiveRecords = true;
				}
			}

			// 3. грузим недостающее для данной страницы
			if (!viewframe.getGrid().getStore().overLimit && !viewframe.blockLoadArchive) {
				if (loadArchiveRecords) {
					viewframe.blockLoadArchive = true;
					log('Loading Archive Records...');
					if (viewframe.paging) {
						var params = swCloneObject(viewframe.getGrid().getStore().baseParams);
						params.useArchive = 1;
						params.start = 0;
						params.limit = viewframe.pageSize - notArchRecCount;
						// 3.1 загружаем недостающие записи для данной страницы (если пейджинг)
						viewframe.getLoadMask(lang['zagruzka_arhivnyih_zapisey']).show();
						Ext.Ajax.request({
							url: viewframe.dataUrl,
							params: params,
							callback: function (opt, success, response) {
								viewframe.getLoadMask().hide();
								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (response_obj.totalCount > 0) {
										viewframe.removeEmptyRecord();
									}
									viewframe.notArchRecords = (pageData.pages - 1) * viewframe.pageSize + notArchRecCount;
									viewframe.archRecords = response_obj.totalCount;
									response_obj.totalCount = viewframe.archRecords + viewframe.notArchRecords; // общее количество = архивные + неархивные
									viewframe.getGrid().getStore().loadData(response_obj, true);
								}
							}
						});
					} else {
						// 3. если не пейджинг просто загружаем все архивные записи
						var params = swCloneObject(viewframe.getGrid().getStore().baseParams);
						params.useArchive = 1;
						viewframe.getLoadMask(lang['zagruzka_arhivnyih_zapisey']).show();
						Ext.Ajax.request({
							url: viewframe.dataUrl,
							params: params,
							callback: function (opt, success, response) {
								viewframe.getLoadMask().hide();
								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									viewframe.getGrid().getStore().loadData(response_obj, true);
								}
							}
						});
					}
				}
			} else {
				viewframe.blockLoadArchive = false;
			}
		}
	},
	/** Функция формирует парамсы и открывает форму редактирования данных
	* Параметры:
	* @mode : режим открытия. Может принимать значения 'add','edit','view'.
	*/
	editRecord: function (mode)
	{
		var viewframe = this;
		var grid = viewframe.ViewGridPanel;
		if (viewframe.function_action_add && (mode=='add') && ((viewframe.run_function_add==undefined) || viewframe.run_function_add))
		{
			if (!viewframe.function_action_add())
				return;
		}
		else
		{
			viewframe.run_function_add = undefined;
		}
		if (viewframe.function_action_edit && (mode=='edit') && ((viewframe.run_function_edit==undefined) || viewframe.run_function_edit))
		{
			if (!viewframe.function_action_edit())
				return;
		}
		else
		{
			viewframe.run_function_edit = undefined;
		}
		if (viewframe.function_action_view && (mode=='view') && ((viewframe.run_function_view==undefined) || viewframe.run_function_view))
			if (!viewframe.function_action_view())
				return;
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
			return false;
		}
		else if (!grid.getSelectionModel().getSelected() && (mode!='add'))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		//var id = grid.getSelectionModel().getSelected().data[viewframe.jsonData['key_id']];
		// Формирование парамсов для передачи из текущей записи грида в форму
		if ((viewframe.params) && (viewframe.params.callback))
		{
			// Если в параметрах указаны callback и onHide - то берем их 
			var params = {callback: viewframe.params.callback, owner: viewframe, action: mode};
			if (viewframe.params.onHide)
			{
				params.onHide = viewframe.params.onHide;
			}
		}
		else
		{
			var params = { callback: viewframe.refreshRecords, owner: viewframe, action: mode };
		}
		
		if (mode!='add')
		{
			for (i=0; i < viewframe.jsonData['params'].length; i++)
			{
				params[viewframe.jsonData['params'][i].name] = grid.getSelectionModel().getSelected().data[viewframe.jsonData['params'][i].name];
			}
		}
		else
		{
			viewframe.focus();
		}
		// Формирование парамсов для передачи из "родителя" грида
		// получаем их в функции loadData
		var p = Ext.apply(params || {}, viewframe.params || {});

		if (viewframe.getMoreParamsForEdit && typeof viewframe.getMoreParamsForEdit == 'function') {
			p = Ext.apply(p, viewframe.getMoreParamsForEdit(mode));
		}

		// Открытие формы
		if (viewframe.editformclassname)
		{
			if (typeof viewframe.editformclassname=="string")
			{
				getWnd(viewframe.editformclassname).show(p);
			}
			else if (typeof viewframe.editformclassname=="object")
			{
				/*
				if (viewframe.objectName && window[this.objectName]==null)
				{
					window[viewframe.objectName] =  new sw.Promed[viewframe.objectName]();
				}
				*/
				/* хотя такой вариант не предпочтителен */
				viewframe.editformclassname.show(p);
			}
		}
	},
	/** Функция для сохранения измененных данных в гриде
	* Параметры:
	* @o : Объект. Имеет следующие свойства:
	* grid - Этот грид
	* record - Редиктируемая запись грида
	* field - Наименование редактируемого поля
	* value - Новое значение поля
	* originalValue - Старое значение поля
	* row - Номер строки
	* column - Номер колонки
	* cancel - признак отмены изменения.
	*/
	saveRecord: function (o)
	{
		var viewframe = this;
		var grid = viewframe.ViewGridPanel;
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
			return false;
		}
		if ((!viewframe.ViewActions.action_save.initialConfig.url) || (viewframe.ViewActions.action_save.initialConfig.url==''))
		{
			Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_ne_ukazan_url_dlya_sohraneniya']);
			return false;
		}
		if (viewframe.processSave)
		{
			return false;
		}
		viewframe.processSave = true;
		
		var params = new Object();
		if (viewframe.saveAtOnce)
		{
			// Сохранение сразу одной записи
			if (o.record.get(viewframe.jsonData['key_id']))
			{
				params['object'] = viewframe.object; // Объект
				params[viewframe.jsonData['key_id']] = o.record.get(viewframe.jsonData['key_id']); // Значение ID объекта
				params[o.field] = o.value; // Значение
				if (viewframe.saveAllParams) // При данной настройке передаем все парамсы
				{
					for (i=0; i < viewframe.jsonData['params'].length; i++)
					{
						params[viewframe.jsonData['params'][i].name] = o.record.get([viewframe.jsonData['params'][i].name]);
					}
				}
			}
		}
		else
		{
			// Сохранение отложенно всех записей
			params['object'] = viewframe.object; // Объект
			params['data'] = new Array();
			if(viewframe.saveBaseParams) {
				Ext.apply(params, viewframe.ViewGridStore.baseParams);
			}
			var k = -1;
			grid.getStore().each(function(record)
			{
				if (record.get(viewframe.jsonData['key_id']))
				{
					if (record.dirty)
					{
						k++;
						params['data'][k] = new Object();
						params['data'][k][viewframe.jsonData['key_id']] = record.get(viewframe.jsonData['key_id']); // Значение ID объекта
						for(var key in record.getChanges())
						{
							params['data'][k][key] = record.get(key);
						}
						if (viewframe.saveAllParams) // При данной настройке передаем все парамсы
						{
							for (i=0; i < viewframe.jsonData['params'].length; i++)
							{
								params['data'][k][viewframe.jsonData['params'][i].name] = record.get([viewframe.jsonData['params'][i].name]);
							}
						}
					}
				}
			});
			params['data'] = Ext.util.JSON.encode(params['data']);
			if(!params['data'].length) {
				return false;
			}
			viewframe.ViewActions.action_save.setDisabled(true);
		}
		// Сам запрос
		Ext.Ajax.request(
		{
			url: viewframe.ViewActions.action_save.initialConfig.url,
			params: params,
			failure: function(response, options)
			{
				//Ext.Msg.alert('Ошибка', 'При сохранении произошла ошибка!');
				viewframe.processSave = false;
			},
			success: function(response, action)
			{
				if (response.responseText)
				{
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success)
					{
						if (viewframe.onRecordSave)
						{
							viewframe.onRecordSave(o);
						}

						//Обработка возвращаемых данных
						if(answer.data) {
							viewframe.setRecordValuesAfterSave(answer.data);
						}

						if (viewframe.saveAtOnce)
						{
							o.record.commit();
						}
						else
						{
							grid.getStore().each(function(record)
							{
								// 1) это ограничение только для уже существующих строк
								// 2) если сохранять и новые строки то здесь должен быть возврат ID-шников
								if (record.get(viewframe.jsonData['key_id']))
								{
									if (record.dirty)
									{
										record.commit();
									}
								}
							});
						}
					}
					else
					{
						if (answer.Error_Code)
						{
							Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
						}
					}
				}
				else
				{
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_sohranenii_otsutstvuet_otvet_servera']);
				}
				viewframe.processSave = false;
			}
		});
		viewframe.focus();
	},
	/**
	 * Обработка возвращаемых данных при сохранении
	 * @param {*} data 
	 */
	setRecordValuesAfterSave: function (data) {
		if(typeof(data) != 'object') return;
		var store = this.getGrid().getStore(),
			keyField = this.jsonData["key_id"];

		data.forEach(function(obj) {
			if(!obj[keyField]) return;
				

			var keyValue = obj[keyField],
				idx = store.find(keyField,keyValue),
				rec = store.getAt(idx);

			if(!rec) return;

			for(var field in rec.data) {
				if(typeof(field) != 'string')
					continue;
				if(obj[field] !== undefined) {
					rec.set(field, obj[field]);
				}
			}
		});
	},
	checkBeforeLoadData: function(store, options) {
		return true;
	},
	openUecPersonOnLoad: null,
	uniqueId: false,
	getLoadMask: function(MSG) {
		if (MSG) {
			delete(this.loadMask);
		}
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},
	getMultiSelections: function() {
		var viewframe = this;

		if ( this.selectionModel == 'multiselect2' ) {
			// надо вернуть массив помеченных записей
			return viewframe.checkedArray;
		} else {
			return viewframe.getGrid().getSelectionModel().getSelections();
		}
	},
	clearMultiSelections: function() {
		var viewframe = this;

		if ( this.selectionModel == 'multiselect2' ) {
			viewframe.checkedArray = [];
			viewframe.getGrid().getStore().each(function (record) {
				record.set('MultiSelectValue', false);
				record.commit();
			});
			// снимаем галочку с хеадера
			Ext.get(viewframe.multiCheckBoxId).dom.checked = false;
		} else {
			viewframe.getGrid().getSelectionModel().clearSelections();
		}

		if (viewframe.onMultiSelectChange) {
			viewframe.onMultiSelectChange();
		}
	},
	checkedArray: [],
	multiCheckBoxId: null,
	multiSelectHeaderClick: function(checkbox) {
		var viewframe = this;
		if (viewframe.readOnly || viewframe.disabledCheckBox) {
			return false;
		}
		var key = viewframe.getKey();
		if (checkbox.checked) {
			// проставляем всем записям
			viewframe.getGrid().getStore().each(function (record) {
				if (record.get(key)) {
					record.set('MultiSelectValue', true);
					// ищем запись, если нет её, то засовываем
					var founded = -1;
					var checkedArr = viewframe.checkedArray;
					for(var k in checkedArr) {
						if (checkedArr[k].get && checkedArr[k].get(key) == record.get(key)) { // сравниаем по ключу.
							founded = k;
						}
					}
					if (founded == -1) {
						viewframe.checkedArray.push(record);
					}
					record.commit();
				}
			});
		} else {
			// снимаем у всех записей
			viewframe.getGrid().getStore().each(function (record) {
				if (record.get(key)) {
					record.set('MultiSelectValue', false);
					// ищем запись и убираем из массива помеченных записей
					var founded = -1;
					var checkedArr = viewframe.checkedArray;
					for(var k in checkedArr) {
						if (checkedArr[k].get && checkedArr[k].get(key) == record.get(key)) { // сравниаем по ключу.
							founded = k;
						}
					}
					if (founded > -1) {
						viewframe.checkedArray.splice(founded, 1);
					}
					record.commit();
				}
			});
		}

		if (viewframe.onMultiSelectChange) {
			viewframe.onMultiSelectChange();
		}
		if (viewframe.onSetAllMultiSelectValue) {
			viewframe.onSetAllMultiSelectValue(checkbox.checked);
		}
	},
	syncMultiSelectHeader: function() {
		var viewframe = this;
		var checkedAll = true;
		var key = viewframe.getKey();
		var count = 0;
		// идём по store
		viewframe.getGrid().getStore().each(function (record) {
			if (record.get(key)) {
				count++;
				if (!record.get('MultiSelectValue')) {
					checkedAll = false;
				}
			}
		});
		// ставим/снимаем галочку с хеадера
		Ext.get(viewframe.multiCheckBoxId).dom.checked = checkedAll && count > 0;
	},
	initComponent: function()
	{
		var viewframe = this;

		if (this.id == 'viewframe')
		{
			if (this.uniqueId) {
				this.id = Ext.id();
			} else {
				this.id = this.object; //'Frame';
			}
		}
		this.titleGrid = this.title;
		this.title='';
		this.loadCount = -1;

		if ( this.selectionModel == 'multiselect2' ) {
			// 1. включаем колонку с чекбоксами
			this.multiCheckBoxId = this.id + 'MultiArrayCheckBox';
			var disabled = this.disabledCheckBox?'disabled':'';
			var multiArray = [
				{name: 'MultiSelectValue', header:'<input onclick="Ext.getCmp(\''+this.id+'\').multiSelectHeaderClick(this);" id="'+this.multiCheckBoxId+'" '+disabled+' type="checkbox" />', type:'multiselectcolumnedit', fixed: true, hideable: true, menuDisabled: true, sortable: false, width: 30}
			];
			this.stringfields = multiArray.concat(this.stringfields);
		}

		if ( getGlobalOptions().archive_database_enable && this.useArchive ) {
			// 1. включаем группировку
			this.stringfields = this.stringfields.concat([
				{name: 'archiveRecord', type: 'int', isparams: true, hidden: true, group: true, sort: true}
			]);

			// 2. особый конфиг для группировки
			this.groupTextTpl = '{[values.gvalue == 0 ? "Актуальные записи" : "Архивные записи"]}';
		}

		this.jsonData = swGetJSONData(this.stringfields,this.object);
		//log(this.jsonData);
		this.editing = this.jsonData['editing'];
		this.grouping = this.jsonData['grouping'];
		this.gridplugins = this.gridplugins.concat(this.plugins.concat(this.jsonData['plugins']));

		var base = Ext.apply({}, this.jsonData['base']);
		this.rowCount = 0;

		// Вообще можно с fields все переделать на reader, тогда пропадает актуальность изменения meta.id для 'Store'
		if (this.grouping)
		{
			this.readerInitObject = {
				id: this.jsonData['key_id'],
				root: (this.root)?this.root:null,
				totalProperty: (this.totalProperty)?this.totalProperty:null,
				groupField: (this.grouping)?this.jsonData['groupField']:null
			};

			this.reader = new Ext.data.JsonReader(this.readerInitObject,this.jsonData['store']);
		}
		//this.ViewGridStore = new sw.Promed.Store(

		this.storeInitObject = {
			id: viewframe.id+'GridStore',
			autoLoad: false,
			reader: (this.grouping)?this.reader:null,
			fields: (!this.grouping)?this.jsonData['store']:null,
			baseParams: base,
			sortInfo: (this.groupSortInfo)?this.groupSortInfo:null,
			remoteSort: this.remoteSort,
			url: this.dataUrl,
			idProperty: this.jsonData['key_id'],
			root: (this.root)?this.root:null,
			pruneModifiedRecords: (this.pruneModifiedRecords) ? this.pruneModifiedRecords : false,
			totalProperty: (this.totalProperty)?this.totalProperty:null,
			groupField: (this.grouping)?this.jsonData['groupField']:null,
			enableColumnMove: this.enableColumnMove,
			itemSelector: '',
			notApplyStringFields: this.notApplyStringFields,
			listeners:
			{
				load: function(store, record, options)
				{
					viewframe.loadCount++;
					var count_old = store.getCount();
					callback:
					{
						var count = store.getCount();
						viewframe.rowCount = count;
						if (viewframe.editing && !viewframe.saveAtOnce)
							viewframe.ViewActions.action_save.setDisabled(true);
						if (!viewframe.ViewActions.action_refresh.initialConfig.initialDisabled)
							viewframe.ViewActions.action_refresh.setDisabled(false);

						// Если значение по которому можно найти индекс есть, то ищем индекс и обнуляем значение
						if (viewframe.valueOnFocus) {
							viewframe.setIndexByValue(viewframe.valueOnFocus);
							viewframe.setValueOnFocus(null);
						}

						viewframe.initActionPrint();

						if (count>0)
						{
							// Инициализация доступности акшенов - cтавим акшены в enabled
							//if (viewframe.editformclassname)
							{
								if (!viewframe.readOnly)
								{
									if (!viewframe.ViewActions.action_add.initialConfig.initialDisabled)
										viewframe.ViewActions.action_add.setDisabled(false);
									if (!viewframe.ViewActions.action_edit.initialConfig.initialDisabled)
										viewframe.ViewActions.action_edit.setDisabled(false);
									if (!viewframe.ViewActions.action_delete.initialConfig.initialDisabled)
										viewframe.ViewActions.action_delete.setDisabled(false);
								}
								if (!viewframe.ViewActions.action_view.initialConfig.initialDisabled)
									viewframe.ViewActions.action_view.setDisabled(false);
								if (!viewframe.ViewActions.action_print.initialConfig.initialDisabled)
									viewframe.ViewActions.action_view.setDisabled(false);
							}

							// Если ставится фокус при первом чтении или количество чтений больше 0
							if ((viewframe.focusOnFirstLoad || viewframe.loadCount>0) && (!viewframe.noFocusOnLoad) && (!viewframe.noFocusOnLoadOneTime))
							{
								viewframe.focus();
							}
							else
							{
								viewframe.noFocusOnLoadOneTime = false;
								// Если фокус не ставим то количество все равно указываем
								if (viewframe.topTextCounter) {
									viewframe.topTextCounter.el.innerHTML = '0 / ' + count;
								}
							}
							// При изменении кол-ва записей в гриде на лоад / релоад
							if ((count_old!=count) && (viewframe.changeCountRecords))
								viewframe.changeCountRecords();
							if (viewframe.onLoadData)
							{
								viewframe.onLoadData(true);
							}

							// если поиск по УЭК то запускаем action_edit если человек найден
							if (!Ext.isEmpty(viewframe.openUecPersonOnLoad)) {
								// ищем в гриде строку по Person_id
								var pers_id = viewframe.openUecPersonOnLoad;
								viewframe.openUecPersonOnLoad = null;
								index = viewframe.getGrid().getStore().findBy(function(record) {
									if (record.get('Person_id') == pers_id) {
										return true;
									} else {
										return false;
									}
								});
								if (index >= 0) {
									viewframe.ViewGridPanel.focus();
									viewframe.ViewGridPanel.getView().focusRow(index);
									viewframe.ViewGridPanel.getSelectionModel().selectRow(index);
									if (!viewframe.ViewActions.action_edit.isDisabled()) {
										viewframe.ViewActions.action_edit.execute();
									} else if (!viewframe.ViewActions.action_view.isDisabled()) {
										viewframe.ViewActions.action_view.execute();
									}
								}
							}
						}
						else
						{
							// Создаем пустую строку
							viewframe.rowCount = 0;
							viewframe.clearTopTextCounter();
							viewframe.addEmptyRecord(store);
							if ((viewframe.focusOnFirstLoad || viewframe.loadCount>0) && (!viewframe.noFocusOnLoad))
							{
								viewframe.focus();
							}
							/*
							 var EmptyRecord = Ext.data.Record.create(viewframe.jsonData['store']);
							 store.insert(0, new EmptyRecord(viewframe.jsonData['emply']));
							 if ((viewframe.focusOnFirstLoad || viewframe.loadCount>0) && (!viewframe.noFocusOnLoad))
							 {
							 viewframe.focus();
							 }
							 */
							if (viewframe.onLoadData)
							{
								viewframe.onLoadData(false);
							}
						}

						if (viewframe.selectionModel == 'multiselect2') {
							if (!viewframe.paging || !(options && options.params && options.params.loadFromPaging)) { // если не пейджинг или не переход с другой страницы
								// очищаем список отмеченных
								viewframe.checkedArray = [];
							} else {
								// иначе восстанавливаем отметки из checkedArray
								var founded = -1;
								var key = viewframe.getKey();
								var checkedArr = viewframe.checkedArray;
								// идём по store
								viewframe.getGrid().getStore().each(function (record) {
									founded = -1;
									for (var k in checkedArr) {
										if (checkedArr[k].get && checkedArr[k].get(key) == record.get(key)) { // сравниаем по ключу.
											founded = k;
										}
									}
									if (founded > -1) {
										// отмечаем
										record.set('MultiSelectValue', true);
										record.commit();
									}
								});

								if (viewframe.onMultiSelectChange) {
									viewframe.onMultiSelectChange();
								}
							}
							viewframe.syncMultiSelectHeader();
						}

						if ( getGlobalOptions().archive_database_enable && viewframe.useArchive ) {
							if (!viewframe.paging) {
								viewframe.loadArchiveRecords();
							}
						}

						if (viewframe.onMultiSelectionChange) {
							viewframe.onMultiSelectionChange();
						}
					}
				},
				clear: function()
				{
					if (viewframe.selectionModel == 'multiselect2') {
						// очищаем список отмеченных
						viewframe.checkedArray = [];
						viewframe.syncMultiSelectHeader();
					}

					// Ставим акшены в disabled
					//log(viewframe.id+' clear');
					if (!viewframe.ViewActions.action_add.initialConfig.initialDisabled)
						viewframe.ViewActions.action_add.setDisabled(viewframe.readOnly);
					if (!viewframe.ViewActions.action_edit.initialConfig.initialDisabled)
						viewframe.ViewActions.action_edit.setDisabled(true);
					if (!viewframe.ViewActions.action_view.initialConfig.initialDisabled)
						viewframe.ViewActions.action_view.setDisabled(true);
					if (!viewframe.ViewActions.action_delete.initialConfig.initialDisabled)
						viewframe.ViewActions.action_delete.setDisabled(true);
					if (!viewframe.ViewActions.action_print.initialConfig.initialDisabled)
						viewframe.ViewActions.action_print.setDisabled(true);
					if (viewframe.editing && !viewframe.saveAtOnce)
						viewframe.ViewActions.action_save.setDisabled(true);
					viewframe.rowCount = 0;
				},
				beforeload: function(store, options)
				{
					if (viewframe.ownerWindow && viewframe.ownerWindow.archiveRecord) {
						options.params.archiveRecord = viewframe.ownerWindow.archiveRecord;
					}
					if ( getGlobalOptions().archive_database_enable && viewframe.useArchive ) {
						// здесь надо определить грузим ли мы обычные записи или архивные, еслли архивные то передаём корректную отправную точку archiveStart
						if (options.params) {
							if (viewframe.showArchive && options.params.start && options.params.start >= viewframe.notArchRecords && viewframe.notArchRecords > -1) {
								options.params.archiveStart = viewframe.notArchRecords;
								options.params.useArchive = 1;
							} else {
								options.params.archiveStart = 0;
								options.params.useArchive = 0;
							}
						}
					}

					if (viewframe.onBeforeLoadData)
					{
						viewframe.onBeforeLoadData();
					}

					if (!viewframe.checkBeforeLoadData(store, options)) {
						return false;
					}
				}
			}
		};

		this.ViewGridStore = new sw['Promed'][(this.grouping)?'GroupingStore':'Store'](this.storeInitObject);

		// Для перезаписи данных
		if (!this.grouping)
		{
			this.ViewGridStore.reader.meta.id = this.ViewGridStore.idProperty;
		}

		// Для начальной сортировки
		if ((this.jsonData['sortInfo']) && (this.jsonData['sortInfo'].field))
		{
			this.ViewGridStore.sortInfo = this.jsonData['sortInfo'];
		}
		// Формирование Actions на гриде. Стандартные события 'add', 'edit', 'view', 'delete', 'refresh', 'print' - перекрываются
		var OptionActions = new Array(); // Стандартные свойства для кнопок (расширяемо!)
		OptionActions =
		[
			{name:'action_add', text:BTN_GRIDADD, tooltip: BTN_GRIDADD_TIP, icon: 'img/icons/add16.png', handler: function() {viewframe.editRecord('add')}},
			{name:'action_edit', text:BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT_TIP, icon: 'img/icons/edit16.png', handler: function() {viewframe.editRecord('edit')}},
			{name:'action_view', text:BTN_GRIDVIEW, tooltip: BTN_GRIDVIEW_TIP, icon: 'img/icons/view16.png', handler: function() {viewframe.editRecord('view')}},
			{name:'action_delete', text:BTN_GRIDDEL, tooltip: BTN_GRIDDEL_TIP, icon: 'img/icons/delete16.png', handler: function() {viewframe.deleteRecord()}},
			{name:'action_refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR_TIP, icon: 'img/icons/refresh16.png', handler: function() {viewframe.refreshRecords(null,0)}},
			{name:'action_print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT_TIP, icon: 'img/icons/print16.png', /*handler: function() {viewframe.printObjectList()}*/
				menuConfig: {
					printObject: {name: 'printObject', text: lang['pechat'], handler: function(){viewframe.printObject()}},
					printObjectList: {name: 'printObjectList', text: lang['pechat_tekuschey_stranitsyi'], handler: function(){viewframe.printObjectList()}},
					printObjectListFull: {name: 'printObjectListFull', text: lang['pechat_vsego_spiska'], handler: function(){viewframe.printObjectListFull()}},
					printObjectListSelected: {name: 'printObjectListSelected', text: lang['pechat_spiska_vyibrannyih'], handler: function(){viewframe.printObjectListSelected()}}
				}
			},
			{name:'action_resetfilter', text:lang['sbros'], tooltip: lang['sbrosit_filtr'], icon: 'img/icons/reset16.png', hidden: true, disabled: false, handler: function(o) {viewframe.filterReset();}},
			{name:'action_save', text:lang['sohranit'], tooltip: lang['sohranit_izmeneniya'], icon: 'img/icons/save16.png', hidden: (viewframe.saveAtOnce || !viewframe.editing), disabled: true, handler: function(o) {viewframe.saveRecord(o)}}
		];

		this.replaceActionMenuWithHandler = function(action, handler) {
			var id = action.initialConfig.id;
			action.setHandler(handler);
			action.initialConfig.menu = null;

			var btn = this.ViewToolbar.items.get(id);
			if (btn && btn.menu) {
				btn.menu = null;
				btn.initComponent();
				var oldEl = btn.el;
				btn.rendered = false;
				btn.render();
				oldEl.remove();
			}

			btn = this.ViewContextMenu.items.get(id);
			if (btn) {
				if (btn.menu) {
					btn.menu.destroy();
					btn.menu = null;
					btn.initialConfig.menu = null;
				}
				btn.removeClass('x-menu-item-arrow');
				btn.initComponent();
			}

			action.mode = 'handler';
		};

		this.restoreActionMenu = function(action) {
			var id = action.initialConfig.id;

			var btnMenu = new Ext.menu.Menu();
			for (key in action.menu) {
				btnMenu.add(action.menu[key]);
			}
			action.initialConfig.menu = btnMenu;
			action.setHandler(Ext.emptyFn);

			var btn = this.ViewToolbar.items.get(id);
			if (btn) {
				btn.menu = btnMenu;
				btn.initComponent();
				var oldEl = btn.el;
				btn.rendered = false;
				btn.render();
				oldEl.remove();
			}

			btn = this.ViewContextMenu.items.get(id);
			if (btn) {
				btn.menu = btnMenu
				btn.addClass('x-menu-item-arrow');
				btn.initComponent();
			}

			action.mode = 'menu';
		};

		this.initActionPrint = function() {
			if (!viewframe.ViewActions.action_print.menu) { return; }

			var action = viewframe.ViewActions.action_print;
			var store = viewframe.getGrid().getStore();
			var count = store.getCount();
			var totalCount = store.getTotalCount();
			var overLimit = store.overLimit;

			if (viewframe.useEmptyRecord && count == 1 && !Ext.isEmpty(store.idProperty)) {
				var record = store.getAt(0);
				if (Ext.isEmpty(record.get(store.idProperty))) {
					count = totalCount = 0;
				}
			}

			if (count > 0) {
				viewframe.ViewActions.action_print.setDisabled(false);
			} else {
				viewframe.ViewActions.action_print.setDisabled(true);
			}

			if (!action.menu.printObjectListSelected.initialConfig.initialDisabled && !action.menu.printObjectListFull.isHidden()) {
				if (viewframe.selectionModel != 'multiselect') {
					action.menu.printObjectListSelected.setHidden(true);
				}
			}

			if (totalCount > 0 || overLimit == true || viewframe.forcePrintMenu) {
				//Если загружена всего одна страница, то запрещаем "Печать текущей страницы"
				//Также должен быть виден пункт "Печать всего списка"
				if (!action.menu.printObjectList.initialConfig.initialDisabled && !action.menu.printObjectListFull.isHidden()) {
					//log([this.id, count, totalCount]);
					if (!this.paging) {
						action.menu.printObjectList.setHidden(true);
					} else {
						if (count < totalCount || overLimit == true) {
							action.menu.printObjectList.setDisabled(false);
						} else {
							action.menu.printObjectList.setDisabled(true);
						}
					}
				}

				//Если активен и отображен только 1 пункт меню, то handler этого
				//пункта навешиваентся на кнопку, само меню скрывается
				var menuItemsCount = 0;
				var menuItem = null;
				for (key in action.menu) {
					if (!action.menu[key].isHidden() && !action.menu[key].isDisabled()) {
						menuItem = key;
						menuItemsCount++;
					}
				}
				action.menuActiveItemsCount = menuItemsCount;
				if (menuItemsCount == 1 && menuItem && viewframe.selectionModel != 'multiselect') {
					viewframe.replaceActionMenuWithHandler(action, action.menu[menuItem].initialConfig.handler);
				} else if (menuItemsCount == 0) {
					viewframe.replaceActionMenuWithHandler(action, Ext.emptyFn);
				} else {
					viewframe.restoreActionMenu(action);
				}

				/*if (!action.initialConfig.initialDisabled) {
					if (menuItemsCount > 0) {
						action.setDisabled(false);
					} else {
						action.setDisabled(true);
					}
				}*/
			} else {
				viewframe.replaceActionMenuWithHandler(action, Ext.emptyFn);
			}
		};

		// Формирование массива свойств событий в зависимости от переданных свойств событий
		this.ViewActions = new Array();
		var actions = new Array();
		var menuConfig = {};
		for (var i=0; i<OptionActions.length; i++)
		{
			for (var j=0; j<this.actions.length; j++)
			{
				if ((this.actions[j]) && (OptionActions[i]['name'] == this.actions[j]['name']))
				{
					if (OptionActions[i]['menuConfig']) {
						menuConfig = Ext.apply({}, this.actions[j]['menuConfig'] || {}, OptionActions[i]['menuConfig']);
						for (key in menuConfig) {
							menuConfig[key] = Ext.apply(OptionActions[i]['menuConfig'][key] || {}, menuConfig[key]);
						}
						actions[i] = Ext.apply(OptionActions[i] || {}, this.actions[j] || {});
						actions[i]['menuConfig'] = menuConfig;
					} else {
						actions[i] = Ext.apply(OptionActions[i] || {}, this.actions[j] || {});
					}
					this.actions[j]['set'] = 1;
					continue;
				}
			}
			if (!actions[i])
			{
				actions[i] = OptionActions[i];
			}
		}
		for (i=0; i<this.actions.length; i++)
		{
			if (!this.actions[i]['set'])
			{
				actions.push(this.actions[i]);
			}
		}
		this.actions = actions;
		delete(actions);
		// Создание массива событий грида
		for (i=0; i < this.actions.length; i++)
		{
			if (this.actions[i]['name']!=undefined)
			{
				switch (this.actions[i]['name'])
				{
				case 'action_add': case 'action_edit': case 'action_view': case 'action_delete': case 'action_refresh': case 'action_save': case 'action_resetfilter':
					var disable = !this.actions[i]['name'].inlist(['action_add','action_refresh','action_print']);
					this.ViewActions[this.actions[i]['name']] = new Ext.Action(
					{
						id: this.actions[i]['id'] == undefined ? 'id_'+this.actions[i]['name'] : 'id_'+this.actions[i]['id'],
						text: this.actions[i]['text'] == undefined ? OptionActions[i].text : this.actions[i]['text'],
						disabled: this.actions[i]['disabled'] == undefined ? disable : this.actions[i]['disabled'],
						initialDisabled: this.actions[i]['disabled'] == undefined ? false : this.actions[i]['disabled'],
						hidden: this.actions[i]['hidden'] == undefined ? false : this.actions[i]['hidden'],
						tooltip: this.actions[i]['tooltip'] == undefined ? OptionActions[i].tooltip : this.actions[i]['tooltip'],
						url: this.actions[i]['url'] == undefined ? '' : this.actions[i]['url'],
						msg: this.actions[i]['msg'] == undefined ? '' : this.actions[i]['msg'],
						iconCls : 'x-btn-text',
						icon: this.actions[i]['icon'] == undefined ? OptionActions[i].icon : this.actions[i]['icon'],
						menu: this.actions[i]['menu'] == undefined ? '' : this.actions[i]['menu'],
						handler: this.actions[i]['handler'] == undefined ? OptionActions[i].handler : this.actions[i]['handler']
					});
					if (this.actions[i]['func'])
					{
						viewframe['function_'+this.actions[i]['name']] = this.actions[i]['func'];
					}
					break;
				case 'action_print':
					var disable = false, hide = false,
						menuConfig = null, menuActions = {},
						handler = null, menu = null;
					if ( this.actions[i]['handler'] && typeof this.actions[i]['handler'] == "function" ) {
						handler = this.actions[i]['handler'];
					} else if ( this.actions[i]['menu'] ) {
						menu = this.actions[i]['menu'];
					} else if ( this.actions[i]['menuConfig'] ) {
						menu = [];
						menuConfig = this.actions[i]['menuConfig'];
						for (var key in menuConfig) {
							var addThis = true;
							if (
								key == 'printCost'
								&& (
									!getRegionNick().inlist(['ufa', 'perm', 'kareliya', 'ekb', 'khak', 'krym', 'buryatiya', 'pskov', 'astra', 'penza', 'kaluga'])
									|| (getRegionNick() == 'krym' && this.object != 'EvnPL')
									|| (getRegionNick() == 'kaluga' && this.object != 'EvnPL' && this.object != 'EvnPS')
								)
							) {
								addThis = false;
							}
							if (addThis) {
								//disable = (key.inslist(['printObject']));
								//hide = (key.inslist(['printObject']));
								menuConfig[key]['initialDisabled'] = menuConfig[key]['disabled'] == undefined ? false : menuConfig[key]['disabled'];
								menuConfig[key]['disabled'] = menuConfig[key]['disabled'] == undefined ? disable : menuConfig[key]['disabled'];
								menuConfig[key]['hidden'] = menuConfig[key]['hidden'] == undefined ? hide : menuConfig[key]['hidden'];
								if(menuConfig[key]['id'] == undefined) {
									if(menuConfig[key]['name'] == undefined) {
										menuConfig[key]['id'] = viewframe.id + '_' + (Math.random() * 1000);
									} else {
										menuConfig[key]['id'] = viewframe.id + '_' + menuConfig[key]['name'];
									}
								} else { menuConfig[key]['id'] = menuConfig[key]['id']; }
								menuActions[key] = new Ext.Action(menuConfig[key]);
								menu.push(menuActions[key]);
							}
						}
					}
					this.ViewActions[this.actions[i]['name']] = new Ext.Action(
						{
							id: this.actions[i]['id'] == undefined ? 'id_'+this.actions[i]['name']+'_'+viewframe.id : 'id_'+this.actions[i]['id'],
							text: this.actions[i]['text'] == undefined ? OptionActions[i].text : this.actions[i]['text'],
							disabled: this.actions[i]['disabled'] == undefined ? false : this.actions[i]['disabled'],
							initialDisabled: this.actions[i]['disabled'] == undefined ? false : this.actions[i]['disabled'],
							hidden: this.actions[i]['hidden'] == undefined ? false : this.actions[i]['hidden'],
							tooltip: this.actions[i]['tooltip'] == undefined ? OptionActions[i].tooltip : this.actions[i]['tooltip'],
							url: this.actions[i]['url'] == undefined ? '' : this.actions[i]['url'],
							msg: this.actions[i]['msg'] == undefined ? '' : this.actions[i]['msg'],
							iconCls : 'x-btn-text',
							icon: this.actions[i]['icon'] == undefined ? OptionActions[i].icon : this.actions[i]['icon'],
							menu: !menu ? '' : menu,
							handler: !handler ? '' : handler
						});
					this.ViewActions[this.actions[i]['name']]['mode'] = 'handler';
					if (menu && menu.length > 0) {
						this.ViewActions[this.actions[i]['name']]['mode'] = 'menu';
						this.ViewActions[this.actions[i]['name']]['menu'] = menuActions;
						this.ViewActions[this.actions[i]['name']]['menuActiveItemsCount'] = 0;
					}
					break;
				default:
					if (this.actions[i]['name'] != '-')
					{
						this.ViewActions[this.actions[i]['name']] = new Ext.Action(
						{
							id: this.actions[i]['id'] == undefined ? 'id_'+this.actions[i]['name']+'_'+viewframe.id : 'id_'+this.actions[i]['id'], // для уникальности добавил +viewframe.id
							text: this.actions[i]['text'] == undefined ? lang['tekst_sobyitiya_ne_opredelen'] : this.actions[i]['text'],
							disabled: this.actions[i]['disabled'] == undefined ? false : this.actions[i]['disabled'],
							initialDisabled: this.actions[i]['disabled'] == undefined ? false : this.actions[i]['disabled'],
							hidden: this.actions[i]['hidden'] == undefined ? false : this.actions[i]['hidden'],
							tooltip: this.actions[i]['tooltip'] == undefined ? '' : this.actions[i]['tooltip'],
							iconCls: 'x-btn-text',
							icon: this.actions[i]['icon'] == undefined ? '' : this.actions[i]['icon'],
							menu: this.actions[i]['menu'] == undefined ? '' : this.actions[i]['menu'],
							handler: this.actions[i]['handler'] == undefined ? function() {alert(lang['dlya_sobyitiya_ne_ukazano_deystvie']);} : this.actions[i]['handler'],
							position: this.actions[i]['position'] == undefined ? undefined : this.actions[i]['position']
						});
					}
					else
					{
						this.ViewActions[this.actions[i]['name']] = '-';
					}
					break;
				}
			}
		}
		// Массив событий подготовлен
		// Создание popup - меню и кнопок в ToolBar. Формирование коллекции акшенов
		this.ViewContextMenu = new Ext.menu.Menu();
		this.toolItems = new Ext.util.MixedCollection(true);
		var i = 0;

		for (key in this.ViewActions)
		{
			if (key!='remove')
			{
				this.toolItems.add(this.ViewActions[key],key);
				if (this.contextmenu) {
					if ((i>=4) && (i<=5) && (this.ViewActions[key].initialConfig && !this.ViewActions[key].initialConfig.hidden)) { // после 4 действия, если текущее действие не скрыто
						this.ViewContextMenu.add('-'); // добавляем разделитель
					}
					this.ViewContextMenu.add(this.ViewActions[key]);
				}
				i++;
			}
		}

		/*
		Разорбраться с этим гавном - пока оставил ... проблема на самом деле не побеждена.
		var tb = new Ext.Toolbar();
		tb.render('toolbar');
		*/

		if(this.tbActions){
			var toolbarItems = []
			for (key in this.ViewActions)
			{
				if (key == 'action_refresh' && !this.ViewActions[key].isHidden()) {
					toolbarItems.push({xtype: "tbseparator"}, this.ViewActions[key]);
				} else if (key == 'action_print' && !this.ViewActions[key].isHidden()) {
					toolbarItems.push({xtype: "tbseparator"}, this.ViewActions[key]);
				} else {
					if (this.ViewActions[key].initialConfig && typeof this.ViewActions[key].initialConfig.position != 'undefined') {
						toolbarItems.splice(this.ViewActions[key].initialConfig.position, 0, this.ViewActions[key]);
					} else {
						toolbarItems.push(this.ViewActions[key]);
					}
				}
			}
		}else{
			var toolbarItems = [
				this.ViewActions.action_add,
				this.ViewActions.action_edit,
				this.ViewActions.action_view,
				this.ViewActions.action_delete
			]
			if (!this.ViewActions.action_refresh.isHidden()) {
					toolbarItems.push({xtype : "tbseparator"},this.ViewActions.action_refresh);
			}
			if (!this.ViewActions.action_print.isHidden()) {
					toolbarItems.push({xtype : "tbseparator"},this.ViewActions.action_print);
			}
		}

		toolbarItems.push(
			{xtype : "tbseparator"},
			{xtype : "tbfill"},
			this.ViewActions.action_resetfilter,
			this.ViewActions.action_save,
			{xtype : "tbseparator"}
		);

		if (viewframe.showCountInTop) {
			viewframe.topTextCounter = new Ext.Toolbar.TextItem({text: '0 / 0', xtype: 'tbtext'});
			toolbarItems.push(viewframe.topTextCounter);
		}

		toolbarItems = this.preToolbarItems.concat(toolbarItems);

		// Создаем Toolbar. Вот собственно и проблема видна.
		this.ViewToolbar = new Ext.Toolbar({
			id : viewframe.id+'Toolbar',
			items: toolbarItems
		});
		if (this.sm) { // можно передать свой selectionModel
			this.ViewGridModel = this.sm;
		} else {
			switch (this.selectionModel)
			{
				case 'cell':
					this.ViewGridModel = new Ext.grid.CellSelectionModel(
					{
						singleSelect: this.singleSelect,
						listeners:
						{
							cellselect: function (sm, rowIdx, colIdx)
							{
								var printAction = viewframe.ViewActions.action_print;
								if (!printAction.initialConfig.initialDisabled) {
									if (viewframe.getGrid().getStore().getTotalCount() > 0) {
										viewframe.ViewActions.action_print.setDisabled(false);
									} else {
										viewframe.ViewActions.action_print.setDisabled(true);
									}
								}
								if (viewframe.ViewActions.action_print.menu) {
									var menuPrint = viewframe.ViewActions.action_print.menu;
									if (!menuPrint.printObject.initialConfig.initialDisabled) {
										menuPrint.printObject.setDisabled(false);
									}
								}

								if (viewframe.onCellSelect)
								{
									viewframe.onCellSelect(sm,rowIdx,colIdx);
								}
							},
							selectionchange: function (sm, o)
							{
								if (viewframe.onSelectionChange)
								{
									viewframe.onSelectionChange(sm, o);
								}
							}
						}
					});
					break;
				case 'multiselect':
					this.onMultiSelectionChange = function() {
						var sm = viewframe.getGrid().getSelectionModel();

						if (!viewframe.ViewActions.action_print.initialConfig.initialDisabled) {
							if (viewframe.getGrid().getStore().getTotalCount() > 0) {
								viewframe.ViewActions.action_print.setDisabled(false);
							} else {
								viewframe.ViewActions.action_print.setDisabled(true);
							}
						}

						if (viewframe.ViewActions.action_print.menu) {
							var menuPrint = viewframe.ViewActions.action_print.menu;
							if (!menuPrint.printObject.initialConfig.initialDisabled && sm.getCount() == 1) {
								menuPrint.printObject.setDisabled(false);
							} else {
								menuPrint.printObject.setDisabled(true);
							}

							if (!menuPrint.printObjectListSelected.initialConfig.initialDisabled && viewframe.getCount() != sm.getCount()) {
								menuPrint.printObjectListSelected.setDisabled(false);
							} else {
								menuPrint.printObjectListSelected.setDisabled(true);
							}
						}

						// енаблим action_edit если выделено не более 1 записи и ViewFrame не только для чтения
						if (sm.getCount() == 1 && !viewframe.readOnly) {
							if (!viewframe.ViewActions.action_edit.initialConfig.initialDisabled) {
								viewframe.ViewActions.action_edit.setDisabled(false);
							}
						} else {
							viewframe.ViewActions.action_edit.setDisabled(true);
						}

						// енаблим action_view если выделено не более 1 записи
						if (sm.getCount() == 1) {
							if (!viewframe.ViewActions.action_view.initialConfig.initialDisabled) {
								viewframe.ViewActions.action_view.setDisabled(false);
							}
						} else {
							viewframe.ViewActions.action_view.setDisabled(true);
						}

						// дисаблим action_delete если выделно менее 1 записи или ViewFrame только для чтения
						if (sm.getCount() < 1 || viewframe.readOnly) {
							viewframe.ViewActions.action_delete.setDisabled(true);
						} else {
							if (!viewframe.ViewActions.action_delete.initialConfig.initialDisabled) {
								viewframe.ViewActions.action_delete.setDisabled(false);
							}
						}

						if (viewframe.ViewActions.action_print.menu && viewframe.ViewActions.action_print.menu['printObjectListSelected']) {
							viewframe.ViewActions.action_print.menu['printObjectListSelected'].setDisabled(sm.getCount() < 1);
						}

						if (viewframe.onMultiSelectionChangeAdvanced) {
							viewframe.onMultiSelectionChangeAdvanced(sm);
						}
					};

					this.ViewGridModel = new Ext.grid.CheckboxSelectionModel(
					{
						width: viewframe.checkBoxWidth,
						singleSelect: false,
						multi:this.multi,
						dataIndex: 'MultiSelectValue',
						listeners:
						{
							'rowselect': function(sm, rowIdx, record)
							{
								if (viewframe.onMultiSelectionChange) {
									viewframe.onMultiSelectionChange();
								}

								var count = this.grid.getStore().getCount();
								var rowNum = rowIdx + 1;
								// Alert: Хотя отсутствие второго условия можно заюзать при добавлении записей в гриде, в дальнейшем
								if ((record.data[viewframe.jsonData['key_id']]==null) || (record.data[viewframe.jsonData['key_id']]==''))
								{
									count = 0;
									rowNum = 0;
								}
								if (viewframe.topTextCounter) {
									viewframe.topTextCounter.el.innerHTML = rowNum + ' / ' + count;
								}
								// Сохраняем индекс выбранной записи
								viewframe.saveSelectedIndex();
								// Дополнительно свои действия на выбор грида
								if (viewframe.onRowSelect) //&& (count>0))
								{
									viewframe.onRowSelect(sm,rowIdx,record);
								}
							},
							'selectionchange': function()
							{
								// чекбокс в шапке должен сниматься или проставлялся в зависимости от выбранных записей :)
								var recLen = viewframe.getGrid().getStore().getRange().length;
								var selectedLen = this.selections.items.length;
								var view = viewframe.getGrid().getView();
								var chkdiv = Ext.fly(view.innerHd).child(".x-grid3-hd-checker span")
								if (!chkdiv) {
									chkdiv = Ext.fly(view.innerHd).child(".x-grid3-hd-checker")
								}
								if (chkdiv) {
									if (selectedLen == recLen && recLen > 0) {
										chkdiv.addClass("x-grid3-hd-checker-on");
									} else {
										chkdiv.removeClass("x-grid3-hd-checker-on");
									}
								}

								// чекбокс в шапках групп тоже должен сниматься или проставлялся в зависимости от выбранных записей :)
								var checkbox = null;
								if (viewframe.grouping) {
									recLen = 0;
									selectedLen = 0;

									viewframe.getGrid().getEl().query(".x-grid-group").forEach(function(el) {
										checkbox = Ext.get(el).child('.x-grid-group-checkbox');
										if (checkbox) {
											recLen = Ext.get(el).query('.x-grid3-row-checker').length;
											selectedLen = Ext.get(el).query('.x-grid3-row-selected').length;

											if (selectedLen == recLen && recLen > 0) {
												checkbox.set({checked: true}, false);
											} else {
												checkbox.set({checked: false}, false);
											}
										}
									});
								}
							},
							'rowdeselect': function(sm, rowIdx, record)
							{
								if (viewframe.onMultiSelectionChange) {
									viewframe.onMultiSelectionChange();
								}

								if (viewframe.onRowDeSelect)
								{
									viewframe.onRowDeSelect(sm,rowIdx,record);
								}
							}
						},
						handleMouseDown : function(g, rowIndex, e){
							if(e.button !== 0 || this.isLocked()){
								return;
							};
							if (typeof viewframe.isAllowSelectionChange != 'function'
								|| viewframe.isAllowSelectionChange(g, rowIndex, e)
							){
								var view = this.grid.getView();
								if(e.shiftKey && !this.singleSelect && this.last !== false){
									var last = this.last;
									this.selectRange(last, rowIndex, (this.multi||e.ctrlKey));
									this.last = last; // reset the last
									view.focusRow(rowIndex);
								}else{
									var isSelected = this.isSelected(rowIndex);
									if((this.multi||e.ctrlKey) && isSelected){
										this.deselectRow(rowIndex);
									}else if(!isSelected || this.getCount() > 1){
										this.selectRow(rowIndex, (this.multi||e.ctrlKey) || e.shiftKey);
										view.focusRow(rowIndex);
									}
								}
							};
						}
					});
					this.jsonData['grid'].unshift(this.ViewGridModel);
					break;
				default:
					this.ViewGridModel = new Ext.grid.RowSelectionModel(
					{
						singleSelect: this.singleSelect,
						listeners:
						{
							'rowselect': function(sm, rowIdx, record)
							{
								var count = this.grid.getStore().getCount();
								var rowNum = rowIdx + 1;
								// Alert: Хотя отсутствие второго условия можно заюзать при добавлении записей в гриде, в дальнейшем
								if ((record.data[viewframe.jsonData['key_id']]==null) || (record.data[viewframe.jsonData['key_id']]==''))
								{
									count = 0;
									rowNum = 0;
								}
								if (viewframe.topTextCounter) {
									viewframe.topTextCounter.el.innerHTML = rowNum + ' / ' + count;
								}

								var printAction = viewframe.ViewActions.action_print;
								if (!printAction.initialConfig.initialDisabled /*&& printAction.menuActiveItemsCount > 0*/) {
									if (viewframe.getGrid().getStore().getTotalCount() > 0) {
										viewframe.ViewActions.action_print.setDisabled(false);
									} else {
										viewframe.ViewActions.action_print.setDisabled(true);
									}
								}

								if (viewframe.ViewActions.action_print.menu) {
									var menuPrint = viewframe.ViewActions.action_print.menu;
									if (!menuPrint.printObject.initialConfig.initialDisabled) {
										menuPrint.printObject.setDisabled(false);
									}
								}

								// Сохраняем индекс выбранной записи
								viewframe.saveSelectedIndex();

								// Дополнительно свои действия на выбор грида
								if (viewframe.onRowSelect) //&& (count>0))
								{
									viewframe.onRowSelect(sm,rowIdx,record);
								}
							},
							'rowdeselect': function(sm, rowIdx, record)
							{
								if (viewframe.onRowDeSelect)
								{
									viewframe.onRowDeSelect(sm,rowIdx,record);
								}
							}
						}
					});
					break;
			}
		}

		
		//this.ColumnModel = new Ext.grid.ColumnModel(this.jsonData['grid']);

		var groupViewCfg = {
			enableRowBody: true,
			forceFit: (this.groupingView && this.groupingView.forceFit!=undefined)?this.groupingView.forceFit:false,
			enableGroupingMenu: (this.groupingView && this.groupingView.enableGroupingMenu!=undefined)?this.groupingView.enableGroupingMenu:false,
			enableNoGroups: (this.groupingView && this.groupingView.enableNoGroups!=undefined)?this.groupingView.enableNoGroups:false,
			hideGroupedColumn: (this.groupingView && this.groupingView.hideGroupedColumn!=undefined)?this.groupingView.hideGroupedColumn:true,
			showGroupName: (this.groupingView && this.groupingView.showGroupName!=undefined)?this.groupingView.showGroupName:true,
			showGroupsText: (this.groupingView && this.groupingView.showGroupsText!=undefined)?this.groupingView.showGroupsText:true,
			groupTextTpl: (this.groupTextTpl)?this.groupTextTpl:'{text} ({[values.rs.length]} {[values.rs.length == 1 ? "запись": ( values.rs.length.inlist([2,3,4]) ? "записи" : "записей")]})'
		};

		if (viewframe.startGroup) {
			groupViewCfg.startGroup = viewframe.startGroup;
		}

		if (viewframe.doGroupStart) {
			groupViewCfg.doGroupStart = viewframe.doGroupStart;
		}

		if (viewframe.doGroupEnd) {
			groupViewCfg.doGroupEnd = viewframe.doGroupEnd;
		}

		if (viewframe.interceptMouse) {
			groupViewCfg.interceptMouse = viewframe.interceptMouse;
		}

		if ( getGlobalOptions().archive_database_enable && this.useArchive ) {
			// 3. Кастомное отображение групп, можно вынести отдельным компонентом
			groupViewCfg.initTemplates = function(){
				Ext.grid.GroupingView.superclass.initTemplates.call(this);
				this.state = {};

				var sm = this.grid.getSelectionModel();
				sm.on(sm.selectRow ? 'beforerowselect' : 'beforecellselect',
					this.onBeforeRowSelect, this);

				if(!this.startGroup){
					this.startGroup = new Ext.XTemplate( // начало группы отображать не надо
						'<div id="{groupId}" class="x-grid-group {cls}">',
						'<div id="{groupId}-hd" class="x-grid-group-hd" style="display:none;"><div>', this.groupTextTpl ,'</div></div>',
						'<div id="{groupId}-bd" class="x-grid-group-body">'
					);
				}
				this.startGroup.compile();
				this.endGroup = '</div></div>';
			};

			groupViewCfg.getRows = function(){
				if(!this.enableGrouping){
					return Ext.grid.GroupingView.superclass.getRows.call(this);
				}
				var r = [];
				var g, gs = this.getGroups();
				for(var i = 0, len = gs.length; i < len; i++){
					if (gs[i].childNodes[1]) {
						g = gs[i].childNodes[1].childNodes;
						for(var j = 0, jlen = g.length; j < jlen; j++){
							r[r.length] = g[j];
						}
					}
				}
				return r;
			};

			groupViewCfg.listeners = {
				'refresh': function() {
					if (viewframe.showArchiveElement) {
						viewframe.showArchiveElement.remove(); // удаляем элемент, если уже есть
					}

					// 1. проверяем есть ли на данной странице не архивные записи
					var notArchRecCount = 0;
					var archRecCount = 0;
					viewframe.getGrid().getStore().each(function(rec) {
						if (rec.get('archiveRecord') == 1) {
							archRecCount++;
						} else {
							notArchRecCount++;
						}
					});

					// 2. проверяем кол-во записей
					var showArchiveRecordsButton = false;
					if (viewframe.paging) {
						// с пейджингом показываем только если неархивных меньше чем количество записей на странице, либо если последняя страница
						var pageData = viewframe.pagingBBar.getPageData();
						if (!viewframe.getGrid().getStore().overLimit && ((notArchRecCount > 0) && (pageData.activePage == pageData.pages || notArchRecCount < viewframe.pageSize)) || (notArchRecCount == 0 && pageData.activePage == 1)) {
							showArchiveRecordsButton = true;
						}
					} else {
						// без пейджинга показываем точно
						showArchiveRecordsButton = true;
					}

					if (showArchiveRecordsButton) {
						var addAfter = viewframe.getEl().child('.x-grid-group');
						if (addAfter) {
							if (!viewframe.showArchiveElement) {
								viewframe.showArchiveElement = new Ext.Element(document.createElement('div'));
							}
							var buttonText = lang['pokazat'];
							if (viewframe.showArchive) {
								buttonText = lang['skryit'];
							}
							viewframe.showArchiveElement.update('<div style="padding: 10px 0px 5px 5px; border-bottom: 2px solid #99bbe8;"><div id="'+viewframe.id+'-archiveText"><b>Архивные данные</b><img id="'+viewframe.id+'-archiveImg" src="img/grid/info.png"/></div>&nbsp;&nbsp;&nbsp;<a id="'+viewframe.id+'-archiveBtn" class="archiveBtn" href="#" onClick="Ext.getCmp(\''+viewframe.id+'\').toggleArchiveRecords(this);">'+buttonText+'</a></div>');
							if (addAfter.id.indexOf('archiveRecord-1') > 0) {
								viewframe.showArchiveElement.insertBefore(addAfter);
							} else {
								viewframe.showArchiveElement.insertAfter(addAfter);
							}

							var archivBtn = Ext.get(viewframe.id+'-archiveBtn');
							var archivText = Ext.get(viewframe.id+'-archiveText');
							var archiveImg = Ext.get(viewframe.id+'-archiveImg');
							var scroller = viewframe.getGrid().getView().scroller
							var info = new Ext.ToolTip({html:lang['arhivnyie_dannyie_dostupnyi_v_rejime_prosmotra'],target:archiveImg});
							archivBtn.setStyle('position','absolute');
							archivText.setStyle('position','absolute');
							archivBtn.setStyle('left',(scroller.getWidth()-95)+'px');

							viewframe.getGrid().getView().scroller.dom.onscroll = function(){
								var left = (scroller.getWidth()-95+scroller.getScroll().left)+'px';
								archivBtn.setStyle('left',left);
								archivText.setStyle('left',scroller.getScroll().left+'px');
							}
						}
					}
				}
			};
		}

		viewframe.pageSize = (viewframe.pageSize)?viewframe.pageSize:100;
		viewframe.pagingBBar = null;		
		if (this.paging) {
			viewframe.pagingBBar = new sw.Promed.PagingToolbar ({
				viewframe: viewframe,
				store: viewframe.ViewGridStore,
				pageSize: viewframe.pageSize,
				displayInfo: true,
				displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
				emptyMsg: "Нет записей для отображения"
			});

			if ( getGlobalOptions().archive_database_enable && viewframe.useArchive ) {
				viewframe.pagingBBar.on("change", function () {
					viewframe.loadArchiveRecords();
				});
			}
		}
		
		// Формируем сам грид
		this.ViewGridPanel = new Ext['grid'][(this.editing)?'EditorGridPanel':'GridPanel'](
		{
			//Добавляем пейджинг
			bbar: viewframe.pagingBBar,
			getViewFrame: function() {
				return viewframe;
			},
			panel: this,
			enableDragDrop: this.enableDragDrop,
			ddGroup: (this.ddGroup)?this.ddGroup:null,
			clicksToEdit: this.clicksToEdit,
			id: viewframe.id+'Grid',
			region:'center',
			gridHeight: this.gridHeight,
			//height: this.height,//this.toolbar ? this.height-1 : this.height-1,
			title: this.titleGrid,
			loadMask: true,
			stripeRows: true,
			store: this.ViewGridStore,
			columns: this.jsonData['grid'],
			//cm: this.ColumnModel,
			border : false,
			tbar: this.ViewToolbar,
			hideHeaders: this.hideHeaders,
			plugins: this.gridplugins,
			stateful: this.stateful,
			enableColumnHide: this.enableColumnHide,
			viewConfig:
			{
				isScrollToTopOnLoad: this.isScrollToTopOnLoad,
				forceFit: this.forceFit
			}, 
			listeners:
			{
				render: function()
				{
					if (viewframe.onRenderGrid)
					{
						viewframe.onRenderGrid();
					}

					if (!viewframe.disableCheckRole) {
						// ищем базовую форму по ownerCt и проверяем права на редактирование
						var ownerCur = viewframe.ownerCt;
						if (ownerCur != undefined) {
							while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
								ownerCur = ownerCur.ownerCt;
							}
							viewframe.initActionPrint();
							if (typeof ownerCur.checkRole == 'function') {
								viewframe.ownerWindow = ownerCur;
							}
							if (typeof ownerCur.checkRole == 'function' && !ownerCur.checkRole('edit')) {
								viewframe.denyEdit = true;
							}

							if (viewframe.denyEdit) {
								viewframe.denyActions();
							}
						}
					}
					
					// листенер
					this.on("cellmousedown", viewframe.onCellMouseDown, viewframe);
				},
				celldblclick: function (grid, rowIdx, colIdx, event)
				{
					if (viewframe.onCellDblClick)
					{
						viewframe.onCellDblClick(grid, rowIdx, colIdx, event);
					}
					//wnd.viewPersonCardStateDetails();
				},
				cellclick: function (grid, rowIdx, colIdx, event)
				{
					if (viewframe.onCellClick)
					{
						viewframe.onCellClick(grid, rowIdx, colIdx, event);
					}
					//wnd.viewPersonCardStateDetails();
				},
				beforeedit: function (o)
				{
					// До начала редактирования
					if ((viewframe.getCount()==0) || viewframe.readOnly)
					{
						o.cancel = true;
					}
					if (viewframe.onBeforeEdit)
					{
						return viewframe.onBeforeEdit(o);
					}
					else
					{
						return o;
					}
				},
				afteredit: function (o)
				{
					// После редактирования
					if (viewframe.onAfterEditSelf) // можно перекрыть полностью
					{
						viewframe.onAfterEditSelf(o);
					}
					else
					{
						if (viewframe.saveAtOnce)
						{
							viewframe.ViewActions.action_save.execute(o); // или использовать свою функцию
						}
						else
						{
							if ((!viewframe.readOnly) && viewframe.ViewActions.action_save.isDisabled())
								viewframe.ViewActions.action_save.setDisabled(false);
						}
					}
					if (viewframe.onAfterEdit) // использовать свою функцию после всех
					{
						viewframe.onAfterEdit(o);
					}
				},
				validateedit: function (o)
				{
					// После редактирования
					if (viewframe.onValidateEditSelf) // можно перекрыть полностью
					{
						return viewframe.onValidateEditSelf(o);
					}
				}
			},
			sm: this.ViewGridModel,
			view: (this.grouping&&this.groups)?new Ext.grid.GroupingView(groupViewCfg):new Ext.grid.GridView(
			{
				enableRowBody: true
			})
		});

		// console.log('grouping:' + this.grouping);

		if(!this.grouping && !this.headergrouping){
			this.ViewGridPanel.view = new Ext.grid.GridView(
			{
				enableRowBody: true,
				getRowClass : function (row, index)
				{
					var cls = '';

					if(row.get(viewframe.disField)){
						cls = cls+'x-grid-rowgray ';
					}
					return cls;
				},
				listeners:
				{
					rowupdated: function(view, first, record)
					{
						//log('update');
						view.getRowClass(record);
					}
				}
			});
		}
		this.ViewGridPanel.doLayout();
		// Скрываем построенный ToolBar, если свойством не указано обратное
		if (!this.toolbar)
		{
			// ToolBar тем не менее строится, что не очень верно
			this.ViewGridPanel.getTopToolbar().hide();
			// 20.11.2009 Т- ут была панель кнопок
		}

		if (this.transferLine){
			Ext.getCmp(this.id).addClass('transferLine');
		}
		// Проверяем на autoexpand
		if (this.jsonData['autoexpand'])
		{
			this.ViewGridPanel.autoExpandMax = 2000;
			this.ViewGridPanel.autoExpandMin = (this.jsonData['autoExpandMin'])?this.jsonData['autoExpandMin']:200;
			this.ViewGridPanel.autoExpandColumn = 'autoexpand';
		}

		// Добавляем созданное popup-меню к гриду
		if (this.selectionModel!='cell')
		{
			this.ViewGridPanel.addListener('rowcontextmenu', onRowContextMenu,this);
			this.ViewGridPanel.on('rowcontextmenu', function(grid, rowIndex, event)
			{
				// На правый клик переходим на выделяемую запись, если она не выделена
				var record = grid.store.getAt(rowIndex)
				
				if (!grid.getSelectionModel().isSelected(record)) {
					grid.getSelectionModel().selectRow(rowIndex);
				}
			});
		}
		else 
		{
			this.ViewGridPanel.addListener('cellcontextmenu', onCellContextMenu,this);
			this.ViewGridPanel.on('cellcontextmenu', function(grid, rowIndex, colIndex, event)
			{
				// На правый клик переходим на выделяемую запись
				grid.getSelectionModel().select(rowIndex, colIndex);
			});
		}
		
		// Функция вывода меню по клику правой клавиши
		function onRowContextMenu(grid, rowIndex, e)
		{
			e.stopEvent();
			var coords = e.getXY();
			if (grid.panel.contextmenu)
				this.ViewContextMenu.showAt([coords[0], coords[1]]);
		}
		function onCellContextMenu(grid, rowIndex, colIndex, e)
		{
			e.stopEvent();
			var coords = e.getXY();
			if (grid.panel.contextmenu)
				this.ViewContextMenu.showAt([coords[0], coords[1]]);
		}
		// Даблклик на редактирование
		this.ViewGridPanel.on('rowdblclick', function(grid, number, object)
		{
			var viewframe = grid.ownerCt.ownerCt;
			if (viewframe.onDblClick)
			{
				if (viewframe.getCount()>0)
				{
					viewframe.onDblClick(grid, number, object);
				}
			}
			else
			{
				if (!viewframe.ViewActions.action_edit.isDisabled()) {
					viewframe.ViewActions.action_edit.execute();
				} else if (!viewframe.ViewActions.action_view.isDisabled()) {
					viewframe.ViewActions.action_view.execute();
				}
			}
		});

		// Изменение сортировки
		this.ViewGridPanel.on('sortchange', function(grid, info)
		{
			this.sortInfo = info;
		}.createDelegate(this));

		var that = this;
		// Добавляем события на keydown // то есть обработка горячих клавиш
		this.ViewGridPanel.on('keydown', function(e)
		{
			if (that.getGrid() && that.getGrid().blockKeyEvents) {
				that.getGrid().blockKeyEvents = false;
				return;
			}
			//log('-->ViewGridPanel.on');
			//log('global e');
			//global_e = e;
			//log(e);
			//log(that.onKeyDown1);
			//log(typeof(that.onKeyDown1));
			if ((undefined != that.onKeyDown1) && (typeof(that.onKeyDown1) == 'function')) {
				that.onKeyDown1(arguments);
			}
			var viewframe = this.ownerCt.ownerCt;
			if (e.getKey().inlist([e.INSERT, e.F3, e.F4, e.F5, e.F9, e.ENTER, e.DELETE, e.END, e.HOME, e.PAGE_DOWN, e.PAGE_UP, e.TAB])
				|| (viewframe.hasPersonData() && e.getKey().inlist([e.F6, e.F10, e.F11, e.F12]))
				|| (viewframe.hasDrugData() && (e.altKey) && e.getKey()== e.D)
				|| ((e.getKey()==e.S) && (e.altKey) && (viewframe.editing) && (!viewframe.saveAtOnce))
				|| ((e.getKey()==e.F2) && viewframe.editing))
			{
				e.stopEvent();
				if ( e.browserEvent.stopPropagation )
					e.browserEvent.stopPropagation();
				else
					e.browserEvent.cancelBubble = true;

				if ( e.browserEvent.preventDefault )
					e.browserEvent.preventDefault();
				else
					e.browserEvent.returnValue = false;

				e.returnValue = false;

				if (Ext.isIE)
				{
					e.browserEvent.keyCode = 0;
					e.browserEvent.which = 0;
				}
			}
			var countRecords = this.getStore().getCount();
			var selected_record = viewframe.ViewGridPanel.getSelectionModel().getSelected();
			var params = new Object();
			
			// Собираем информацию о человеке в случае, если в гриде есть поля по человеку
			if (viewframe.hasPersonData() && selected_record != undefined)
			{
				//log('hasPersonData selected record');
				params.Person_id = selected_record.get('Person_id');
				params.Server_id = selected_record.get('Server_id');
				if (selected_record.get('PersonEvn_id') && viewframe.passPersonEvn)
				{
					params.PersonEvn_id = selected_record.get('PersonEvn_id');
					params.usePersonEvn = true;
				}
				// некоторые именуют как в базе, но почему-то изначально выбрано не такое именование
				// но так-то надо в гридах переделать
				if ( selected_record.get('Person_Birthday') )
					params.Person_Birthday = selected_record.get('Person_Birthday');
				else
					params.Person_Birthday = selected_record.get('Person_BirthDay');
				if ( selected_record.get('Person_Surname') )
					params.Person_Surname = selected_record.get('Person_Surname');
				else
					params.Person_Surname = selected_record.get('Person_SurName');
				if ( selected_record.get('Person_Firname') )
					params.Person_Firname = selected_record.get('Person_Firname');
				else
					params.Person_Firname = selected_record.get('Person_FirName');
				if ( selected_record.get('Person_Secname') )
					params.Person_Secname = selected_record.get('Person_Secname');
				else
					params.Person_Secname = selected_record.get('Person_SecName');
				params.onHide = function()
				{
					var index = viewframe.ViewGridPanel.getStore().findBy(function(rec) { return rec.get(viewframe.jsonData['key_id']) == selected_record.data[viewframe.jsonData['key_id']]; });
					viewframe.ViewGridPanel.focus();
					if(index >= 0)
					{
						viewframe.ViewGridPanel.getView().focusRow(index);
						viewframe.ViewGridPanel.getSelectionModel().selectRow(index);
					}
				}
				if (viewframe.callbackPersonEdit)
				{
					viewframe.selectedRecord = selected_record;
					params.callback = function() {this.callbackPersonEdit()}.createDelegate(viewframe);
				}
			}

			// Собираем информацию о человеке в случае, если в гриде есть поля по человеку
			if (viewframe.hasDrugData() && selected_record != undefined)
			{
				params.Drug_id = selected_record.get('Drug_id');
			}

			//log('e.getKey()', e.getKey());
			switch (e.getKey())
			{
				case e.F2:
					if (this.getXType() == 'editorgrid')
					{
						viewframe.setFirstEditRecord();
					}
					break;
				case e.ENTER:
					if (this.getXType() == 'editorgrid')
					{
						viewframe.setFirstEditRecord();
					}
					else
					{
						if (viewframe.onEnter)
						{
						if (viewframe.getCount()>0)
							{
								viewframe.onEnter();
							}
						}
						else
						{
							if (!viewframe.ViewActions.action_edit.isDisabled()) {
								viewframe.ViewActions.action_edit.execute();
							} else if (!viewframe.ViewActions.action_view.isDisabled()) {
								viewframe.ViewActions.action_view.execute();
							}
						}
					}
					break;
				case e.F3:
					if ( !e.altKey ) {
						if (!viewframe.ViewActions.action_view.isDisabled())
						{
							viewframe.ViewActions.action_view.execute();
						}
					} else if ( viewframe.enableAudit == true ) {
						params['deleted'] = (typeof viewframe.auditOptions == 'object' && viewframe.auditOptions.deleted == true ? 1 : 0);
						params['schema'] = (typeof viewframe.auditOptions == 'object' && !Ext.isEmpty(viewframe.auditOptions.schema) ? viewframe.auditOptions.schema : '');

						if ( typeof viewframe.auditOptions == 'object' ) {
							if (!Ext.isEmpty(viewframe.auditOptions.field) && !Ext.isEmpty(viewframe.auditOptions.key)) {
								// viewframe.auditOptions.field - поле в таблице БД
								// viewframe.auditOptions.key - поле в сторе грида
								params['key_id'] = selected_record.get(viewframe.auditOptions.key);
								params['key_field'] = viewframe.auditOptions.field;
							} else
							if (!Ext.isEmpty(viewframe.auditOptions.maskRe) && !Ext.isEmpty(viewframe.auditOptions.maskParams)) {
								//Получение параметров для аудита из идентификатора выбранной записи по маске
								var regexp = viewframe.auditOptions.maskRe;
								var mp = viewframe.auditOptions.maskParams;
								var key_id = selected_record.data[viewframe.jsonData['key_id']];

								if (!regexp.test(key_id)) { return false; }

								var arr = regexp.exec(key_id);
								for(var i=0; i<arr.length; i++) {
									params[mp[i]] = arr[i+1];
								}
								if (viewframe.auditOptions.needIdSuffix === true) {
									params['key_field'] += '_id';
								}
							}
						}
						else {
							if(viewframe.jsonData['key_id']=='RegistryError_id') //В этом поле может оказаться громадное число, котороые слишком большое для типа int; https://redmine.swan.perm.ru/issues/59687
							{
								if(getRegionNick().inlist(['ekb','perm']))
									params['registry_id'] = selected_record.data['Registry_id'];
								params['key_id'] = selected_record.data['Registry_id'] + selected_record.data['Evn_id'] + selected_record.data['RegistryErrorType_id'];
							}
							else
							{
								params['key_id'] = selected_record.data[viewframe.jsonData['key_id']];
							}
							params['key_field'] = viewframe.jsonData['key_id'];
							if(viewframe.jsonData['key_id']=='Evn_id') // http://redmine.swan.perm.ru/issues/79697
							{
								if(!Ext.isEmpty(selected_record.data['RegistryType_id']) && selected_record.data['RegistryType_id'] == 6 && getRegionNick()=='perm'){
									params['key_field'] = 'CmpCallCard_id';
								}
							}
						}

						if ( selected_record.data['Server_id'] ) {
							params['Server_id'] = selected_record.data['Server_id'];
						}

						if ( !Ext.isEmpty(params['key_id']) ) {
							getWnd('swAuditWindow').show(params);
						}
					}
					break;
				case e.F4:
					if (!viewframe.ViewActions.action_edit.isDisabled())
					{
						viewframe.ViewActions.action_edit.execute();
					}
					break;
				case e.F5:
					if (!viewframe.ViewActions.action_refresh.isDisabled())
					{
						viewframe.ViewActions.action_refresh.execute();
					}
					break;
				case e.INSERT:
					if (!viewframe.ViewActions.action_add.isDisabled())
					{
						viewframe.ViewActions.action_add.execute();
					}
					break;
				case e.DELETE:
					if (!viewframe.ViewActions.action_delete.isDisabled())
					{
						viewframe.ViewActions.action_delete.execute();
					}
					break;
				case e.S:
					if ((e.altKey) && (viewframe.editing) && (!viewframe.saveAtOnce))
					{
						if ((!viewframe.ViewActions.action_save.isDisabled()) && (!viewframe.ViewActions.action_save.isHidden()))
						{
							viewframe.ViewActions.action_save.execute();
						}
					}
					break;
				case e.D:
					if ((e.altKey) && (viewframe.hasDrugData()))
					{
						//просмотр информации о местонахождении и статусе ЛС
						ShowWindow('swConsolidatedDrugInformationViewWindow', params);
					}
					break;

				case e.END:
					GridEnd(this);
				break;
				case e.HOME:
					GridHome(this);
				break;
				case e.PAGE_DOWN:
					GridPageDown(this);
				break;
					case e.PAGE_UP:
					GridPageUp(this);
				break;

				case e.TAB:
					//log('case e.TAB');
					viewframe.focusTo = '';
					if (e.shiftKey)
					{
						if (typeof(viewframe.onShiftTabAction) == 'function') {
							viewframe.onShiftTabAction();
						}
						if (viewframe.focusPrev)
							viewframe.focusTo = viewframe.focusPrev;
					}
					else
					{
						if (typeof(viewframe.onTabAction) == 'function') {
							viewframe.onTabAction();
						}
						if (viewframe.focusOn)
							viewframe.focusTo = viewframe.focusOn;
					}
					//log('viewframe.focusTo', viewframe.focusTo);
					if (viewframe.focusTo!='')
					{
						if ( viewframe.clearSelectionsOnTab == true ) {
							this.getSelectionModel().clearSelections();
						}

						//log('viewframe.focusTo.type', viewframe.focusTo.type);
						// Куда переходим по табу с грида (это может быть поле или другой грид) с шифтом или без
						if (viewframe.focusTo.type=='grid')
						{
							var grid = Ext.getCmp(viewframe.focusTo.name+'Grid');
							if (grid.getStore().getCount()>0)
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}
						else
							if ((viewframe.focusTo.type=='field') || (viewframe.focusTo.type=='button'))
							{
								var o = viewframe.focusTo;//Ext.getCmp(viewframe.focusTo.name);
								//log('o', o);
								if (o)
								{
									if(o.focus !== undefined){
										o.focus(true, 100);
									} else {
										Ext.getCmp(viewframe.focusTo.name).focus(true, 100);
									}
								}
								else
								{
									Ext.Msg.alert(lang['oshibka_koda'], lang['ne_nayden_obyekt']+viewframe.focusTo.name+'!');
									return false;
								}
							}
							else if ((viewframe.focusTo.type=='other')){
								if(Ext.getCmp(viewframe.focusTo.name))
								{
									Ext.getCmp(viewframe.focusTo.name).focus(true, 100);
								}
								else
								{
									Ext.Msg.alert(lang['oshibka_koda'], lang['ne_nayden_obyekt']+viewframe.focusTo.name+'!');
									return false;
								}
							}
					}
					//alert(viewframe.ownerCt.ownerCt.findById(viewframe.focusOn).id);
				break;
				case e.F6: // Прикрепление и объединение
					if (viewframe.hasPersonData() && params.Person_id) {
						if (!e.altKey && !e.ctrlKey && !e.shiftKey && viewframe.checkPersonKey('F6'))
						{ // прикрепление
                            if(!Ext.isEmpty(viewframe.getGrid()) && !Ext.isEmpty(viewframe.getGrid().getStore()))
							    params.onHide = function(){viewframe.getGrid().getStore().reload()};
							ShowWindow('swPersonCardHistoryWindow', params);
						}
						else if (e.altKey && viewframe.checkPersonKey('Alt+F6'))
						{ // объединение
							var personSurName_SurName = (params['Person_Surname'] == undefined) ? selected_record.get('PersonSurName_SurName') : params['Person_Surname'];
							var personFirName_FirName = (params['Person_Firname'] == undefined) ? selected_record.get('PersonFirName_FirName') : params['Person_Firname'];
							var personSecName_SecName = (params['Person_Secname'] == undefined) ? selected_record.get('PersonSecName_SecName') : params['Person_Secname'];
							var personBirthDay_BirthDay = (params['Person_Birthday'] == undefined) ? selected_record.get('PersonBirthDay_BirthDay') : params['Person_Birthday'];
							// TO-DO: сделать процедуру объединения человека, когда в этом возникнет нужда
							// сделана в рамках задачи http://redmine.swan.perm.ru/issues/78665
							AddPersonToUnion(
								{
									data: {
										Person_id: params['Person_id'],
										PersonSurName_SurName: personSurName_SurName,
										PersonFirName_FirName: personFirName_FirName,
										PersonSecName_SecName: personSecName_SecName,
										PersonBirthDay_BirthDay: personBirthDay_BirthDay,
										Server_id: params['Server_id']
									}
								},
								params.onHide
							); 
						}
						return false;
					}
				break;

				case e.F9:
					var action = null;
					var actionPrint = viewframe.ViewActions.action_print;
					if (actionPrint.menu && actionPrint.mode == 'menu') {
						if (e.ctrlKey) {
							action = actionPrint.menu.printObjectList
						} else if (e.shiftKey) {
							action = actionPrint.menu.printObjectListFull;
						} else {
							action = actionPrint.menu.printObject;
						}
					} else if (actionPrint.mode == 'handler') {
						action = actionPrint;
					}
					if (action && !action.isDisabled() && !action.isHidden()) {
						action.execute();
					}
				break;

				case e.F10: // Редактирование
					if (viewframe.hasPersonData() && params.Person_id && !e.altKey && !e.ctrlKey && !e.shiftKey && viewframe.checkPersonKey('F10'))
					{
						if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
							params.readOnly = true;
						ShowWindow('swPersonEditWindow', params);
						return false;
					}
				break;
				case e.F11: // История лечения
					if (viewframe.hasPersonData() && params.Person_id && !e.altKey && !e.ctrlKey && !e.shiftKey && viewframe.checkPersonKey('F11'))
					{
						ShowWindow('swPersonCureHistoryWindow', params);
						return false;
					}
					/* для отображения электронной карты нужно знать тип АРМ врача и добавить возможность при открытии 
					   ЭМК в данном случае отображение только своих посещений по данному пациенту.
						 Поэтому пока просто закрыл отображение по Ctrl+F11
					if (viewframe.hasPersonData() && params.Person_id && !e.altKey && e.ctrlKey && !e.shiftKey)
					{
						ShowWindow('swPersonEPHForm', params);
						return false;
					}
					*/ 
				break;
				case e.F12: // Льготы и диспансеризация
					if (viewframe.hasPersonData() && params.Person_id)
					{
						if (e.ctrlKey && viewframe.checkPersonKey('Ctrl+F12'))
						{ // Диспансеризация
							ShowWindow('swPersonDispHistoryWindow', params);
						}
						else if (!e.altKey && !e.shiftKey && viewframe.checkPersonKey('F12'))
						{ // Льготы
							ShowWindow('swPersonPrivilegeViewWindow', params);
						}
						return false;
					}
				break;
				case e.F:
					if (e.shiftKey) {
						viewframe.filterByField();
					}
				break;
				case e.R:
					if (e.shiftKey) {
						viewframe.filterReset();
					}
				break;
			}
		return false;
		});

		// Читаем и переходим на первую запись в гриде
		if (this.autoLoadData)
		{
			this.ViewGridPanel.getStore().removeAll();
			this.ViewGridPanel.getStore().load();
		}

		Ext.apply(this,
		{
			layout:'border',
			items:
			[
				{
				region:'center',
				listeners:
				{
					resize: function (p,nW, nH, oW, oH)
					{
						nH = nH || viewframe.ViewGridPanel.gridHeight;
						viewframe.ViewGridPanel.setWidth(nW);
						viewframe.ViewGridPanel.setHeight(nH);
						viewframe.ViewGridPanel.setSize(nW, nH);
					}
				},
				border:false,
				items: this.ViewGridPanel
				}
			]
		});
		//this.ViewGridPanel.getBottomToolbar().add(this.ViewActions.action_add);
		sw.Promed.ViewFrame.superclass.initComponent.apply(this, arguments);
	}
});
