/**
 * sw.Promed.ViewFrame. Класс базового вьюфрейма (grid с плюшками)
 *
 * @author  Марков Андрей
 *
 * @class sw.Promed.ViewFrame
 * @extends sw.Promed.BaseFrame
 */
 
Ext.grid.CheckColumnEdit = function(config){
	Ext.apply(this, config);
	if(!this.id){
		this.id = Ext.id();
	}
	this.renderer = this.renderer.bind(this);
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
		if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
			if(this.grid && this.grid.panel  && this.grid.panel.getCount()>0){
				e.stopEvent();
				var index = this.grid.getView().findRowIndex(t);
				var record = this.grid.store.getAt(index);
				var editEvent = {
					grid: this.grid,
					record: this.grid.store.getAt(index),
					field: this.dataIndex,
					value: !record.data[this.dataIndex],
					originalValue: record.data[this.dataIndex],
					row: index,
					column: this.grid.getColumnModel().findColumnIndex(this.dataIndex)
				};
				record.set(this.dataIndex, editEvent.value);
				this.grid.fireEvent('afteredit',editEvent);
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
		return '<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
	}
};
 
//sw.Promed.ViewFrame = function(config)
//{
//	Ext.apply(this, config);
//	sw.Promed.ViewFrame.superclass.constructor.call(this);
//};

Ext.define('swPromedViewFrame',
{
	extend: 'swPromedBaseFrame',
	/**
	 * При загрузке грида скроллироваться на начало
	 */
	isScrollToTopOnLoad: true,
	region: 'center',
	layout: 'fit',
	id: 'viewframe',
	clearSelectionsOnTab: true,
	collapsible: false,
	height: 203,
	/**
	*
	*/
	stringfields: [],
	object: '',
	obj_isEvn: true, // если true, то object реально является Evn (событием)
	titleGrid: '',
	dataUrl: '',
	actions:'',
	toolbar: true,
	disableActions:true,
	editformclassname:'',
	autoLoadData: true,
	focusOnFirstLoad: true, // если указать false то фокус после обновления ставиться не будет
	readOnly: false,
	denyEdit: false,
	remoteSort: false,
	denyActions: function() {
		if(this.disableActions||this.disableActions==null){
		
			// дисаблим всё, кроме просмотр, печать, обновить
			for (var k in this.ViewActions) {
				var comp = this.ViewActions[k];
				if (typeof comp == 'object' && typeof comp.setDisabled == 'function' && typeof comp.getText == 'function' && comp.getText() && !comp.getText().inlist([BTN_GRIDVIEW,BTN_GRIDREFR,BTN_GRIDPRINT])) {
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
	saveAtOnce: true, // Немедленное сохранение
	saveAllParams: false, // Передавать парамсы при сохранении
	ViewContextMenu: null,
	selectionModel: 'row',
	multi:false,
	singleSelect: true,
	hideHeaders: false,
	plugins: [],
	gridplugins: [],
	forceFit: false,
	enableDragDrop: false,
	enableColumnMove: true,
	passPersonEvn: false,
	contextmenu: true,
	scheme: 'dbo',
	stateful: false,
	enableColumnHide:true,
	/**
	 * Параметр для определения доступности горячих клавиш
	 * Доступны F6, F10, F11, F12 и "производные": Alt+F6, Ctrl+F12
	 * Примеры: 
	 *		allowedPersonKeys: [] - запрещаем все горячие клавиши
	 *		allowedPersonKeys: ['F6', 'F11', 'F12'] - разрешаем только указанные горячие клавиши
	 */
	allowedPersonKeys: null,
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
		if ((!action.name) || (action.name.inlist(['action_add', 'action_edit', 'action_view', 'action_delete', 'action_sign', 'action_refresh', 'action_print', 'action_save', 'action_resetfilter'])))
		{
			alert('Не указано name у акшена, добавляемого в Actions Grid или name перекрывает имя стандартного акшена!');
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
			if (!this.ViewActions.action_sign.initialConfig.initialDisabled)
				this.ViewActions.action_sign.setDisabled(true);
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
				if (!this.ViewActions.action_sign.initialConfig.initialDisabled)
					this.ViewActions.action_sign.setDisabled(false);
			}
			else
			{
				if (!this.ViewActions.action_edit.initialConfig.initialDisabled)
					this.ViewActions.action_edit.setDisabled(true);
				if (!this.ViewActions.action_view.initialConfig.initialDisabled)
					this.ViewActions.action_view.setDisabled(true);
				if (!this.ViewActions.action_delete.initialConfig.initialDisabled)
					this.ViewActions.action_delete.setDisabled(true);
				if (!this.ViewActions.action_sign.initialConfig.initialDisabled)
					this.ViewActions.action_sign.setDisabled(true);
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
	removeAll: function(options)
	{
		if ( !options ) {
			options = new Object();
		}

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
			if (this.ViewGridPanel.getTopToolbar().items.last())
				this.ViewGridPanel.getTopToolbar().items.last().el.innerHTML = '0 / 0';
			
			if (options.addEmptyRecord) 
			{
				this.addEmptyRecord(this.ViewGridPanel.getStore());
			}
			else 
			{
				this.ViewGridPanel.getStore().removeAll();
			}
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
		}
	},
	loadData: function(prms)
	{
		if (!this.params)
			this.params = null;
		if (!this.gFilters)
			this.gFilters = null;
		this.noFocusOnLoad = false;
		if (prms)
		{
			if (prms.params)
				this.params = prms.params;
			if (prms.globalFilters)
				this.gFilters = prms.globalFilters;
			if (prms.noFocusOnLoad)
			{
				this.noFocusOnLoad = true;
			}
			
			// В параметрах можно задать url
			if (prms.url)
			{
				this.setDataUrl(prms.url);
			}
			// В параметрах можно передать valueOnFocus (значение ключевого поля в гриде для того, чтобы установить фокус на эту запись)
			if (prms.valueOnFocus)
			{
				this.setValueOnFocus(prms.valueOnFocus);
			}
		}

		this.ViewGridPanel.getStore().removeAll();
		this.ViewGridPanel.getStore().load({params: this.gFilters, callback: (prms && prms.callback)?prms.callback:null || null});
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
	focus: function () // Устанавливаем фокус принудительно, возможно применение только после лоад
	{
		if (this.getGrid().getStore().getCount()>0)
		{
			var index = this.getIndexOnFocus();
			this.getGrid().getView().focusRow(index);
			if (this.selectionModel!='cell') {
				this.getGrid().getSelectionModel().selectRow(index);
				//this.getGrid().getSelectionModel().selectFirstRow();
				this.getGrid().getSelectionModel().fireEvent('rowselect', this.getGrid().getSelectionModel(), index, this.getGrid().getSelectionModel().getSelected());
			}
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
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка выбора грида!');
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
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка выбора грида!');
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
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка выбора грида!');
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
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка выбора грида!');
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

	/** Функция удаления записи из базового грида и таблицы
	*
	*/
	deleteRecord: function ()
	{
		var viewframe = this;
		var grid = viewframe.ViewGridPanel;
		if (!grid)
		{
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка выбора грида!');
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(viewframe.jsonData['key_id']) ) {
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		
		var params = {object:viewframe.object, obj_isEvn: viewframe.obj_isEvn, scheme: viewframe.scheme };
		
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
			var id = grid.getSelectionModel().getSelected().data[viewframe.jsonData['key_id']];
			params.id = id;
		}
		var delMessage = 'Вы хотите удалить запись?';
		if (grid.getSelectionModel().getCount() > 1) {
			delMessage = 'Вы хотите удалить записи?';
		}
		sw.swMsg.show(
		{
			icon: Ext.MessageBox.QUESTION,
			msg: (viewframe.ViewActions.action_delete.initialConfig.msg!='')?viewframe.ViewActions.action_delete.initialConfig.msg:delMessage,
			title: 'Подтверждение',
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					Ext.Ajax.request(
					{
						url: (viewframe.ViewActions.action_delete.initialConfig.url!='')?viewframe.ViewActions.action_delete.initialConfig.url: C_RECORD_DEL,
						params: params,
						failure: function(response, options)
						{
							Ext.Msg.alert('Ошибка', 'При удалении произошла ошибка!');
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
										Ext.Msg.alert('Ошибка #'+answer.Error_Code, answer.Error_Message);
									}
									else
										if (!answer.Error_Msg) // если не автоматически выводится
										{
											Ext.Msg.alert('Ошибка', 'Удаление невозможно!');
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
								Ext.Msg.alert('Ошибка', 'Ошибка при удалении! Отсутствует ответ сервера.');
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
		if (record && record.id > 0) {
			Ext.ux.GridPrinter.print(this.ViewGridPanel, {rowId: record.id});
		}
	},
	printObjectList: function()
	{
		Ext.ux.GridPrinter.print(this.ViewGridPanel);
	},
	printObjectListFull: function()
	{
		var store = this.getGrid().getStore();
		if (this.paging && store.getCount() < store.getTotalCount()) {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg:'Получение данных для печати'});
			loadMask.show();

			var tmpGrid = this.getGrid().cloneConfig();

			tmpGrid.store = Ext.create('swPromed'+(this.grouping)?'GroupingStore':'Store',{ //new sw['Promed'][(this.grouping)?'GroupingStore':'Store']({
				autoLoad: false,
				reader: (this.grouping)?this.reader:null,
				fields: (!this.grouping)?this.jsonData['store']:null,
				baseParams: store.baseParams,
				proxy:{
					url: this.dataUrl
				},
				idProperty: this.jsonData['key_id'],
				root: (this.root)?this.root:null,
				totalProperty: (this.totalProperty)?this.totalProperty:null,
				groupField: (this.grouping)?this.jsonData['groupField']:null
			});

			tmpGrid.getStore().baseParams.start = 0;
			tmpGrid.getStore().baseParams.limit = 10000;
			tmpGrid.getStore().load({callback: function(){
				loadMask.hide();
				Ext.ux.GridPrinter.print(tmpGrid);
			}});
		} else {
			Ext.ux.GridPrinter.print(this.ViewGridPanel);
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
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка при выборе объекта ViewFrame!');
			return false;
		}
		viewframe.ViewActions.action_refresh.setDisabled(true);
		grid = viewframe.ViewGridPanel;
		if (!grid)
		{
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка при выборе объекта Grid!');
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
	/** Функция формирует парамсы и открывает форму редактирования данных
	* Параметры:
	* @mode : режим открытия. Может принимать значения 'add','edit','view'.
	*/
	editRecord: function (mode)
	{
		viewframe = this;
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
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка выбора грида!');
			return false;
		}
		else if (!grid.getSelectionModel().getSelected() && (mode!='add'))
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
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
			var params = {callback: viewframe.refreshRecords, owner: viewframe, action: mode};
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
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Ошибка выбора грида!');
			return false;
		}
		if ((!viewframe.ViewActions.action_save.initialConfig.url) || (viewframe.ViewActions.action_save.initialConfig.url==''))
		{
			Ext.Msg.alert('Ошибка', 'Системная ошибка: Не указан Url для сохранения!');
			return false;
		}
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
							Ext.Msg.alert('Ошибка #'+answer.Error_Code, answer.Error_Message);
						}
					}
				}
				else
				{
					Ext.Msg.alert('Ошибка', 'Ошибка при сохранении! Отсутствует ответ сервера.');
				}
			}
		});
		viewframe.focus();
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
		
		// подписание пока только для Пскова
		if (getRegionNick() != 'pskov') {
			viewframe.signObject = null;
		}
		
		if (!Ext.isEmpty(viewframe.signObject)) {
			this.stringfields = this.stringfields.concat([
				{name: viewframe.signObject + '_IsSigned', renderer:  function(v, p, r){
					if (Ext.isEmpty(r.get(viewframe.signObject+'_id'))) {
						return '';
					}
					var val = '<span style="color: #000;">Не подписан</span>';
					if (!Ext.isEmpty(v)) {
						switch(parseInt(v)) {
							case 1:
								val = '<span style="color: #800;">Не актуален</span>';
							break;
							case 2:
								val = '<span style="color: #080;">Подписан</span>';
							break;					
						}
					}
					return val;
				}, header: 'ЭЦП', width: 100}
			]);
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
			this.reader = new Ext.data.JsonReader(
			{
				id: this.jsonData['key_id']
			},
			this.jsonData['store']);
		}
		//this.ViewGridStore = new sw.Promed.Store(

		this.ViewGridStore = Ext.create('swPromed'+(this.grouping)?'GroupingStore':'Store',{
			id: viewframe.id+'GridStore',
			autoLoad: false,
			reader: (this.grouping)?this.reader:null,
			fields: (!this.grouping)?this.jsonData['store']:null,
			baseParams: base,
			remoteSort: this.remoteSort,
			proxy: {
				url: this.dataUrl
			},
			idProperty: this.jsonData['key_id'],
			root: (this.root)?this.root:null,
			totalProperty: (this.totalProperty)?this.totalProperty:null,
			groupField: (this.grouping)?this.jsonData['groupField']:null,
			enableColumnMove: this.enableColumnMove,
			itemSelector: '',
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
									if (!viewframe.ViewActions.action_sign.initialConfig.initialDisabled)
										viewframe.ViewActions.action_sign.setDisabled(false);
								}
								if (!viewframe.ViewActions.action_view.initialConfig.initialDisabled)
									viewframe.ViewActions.action_view.setDisabled(false);
								if (!viewframe.ViewActions.action_print.initialConfig.initialDisabled)
									viewframe.ViewActions.action_view.setDisabled(false);
							}
							
							// Если ставится фокус при первом чтении или количество чтений больше 0
							if ((viewframe.focusOnFirstLoad || viewframe.loadCount>0) && (!viewframe.noFocusOnLoad))
							{
								viewframe.focus();
							}
							else
							{
								// Если фокус не ставим то количество все равно указываем
								if (!viewframe.ViewGridPanel.getTopToolbar().hidden)
								{
									viewframe.ViewGridPanel.getTopToolbar().items.last().el.innerHTML = '0 / '+count;
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
							if (viewframe.ViewGridPanel.getTopToolbar().items.last())
								viewframe.ViewGridPanel.getTopToolbar().items.last().el.innerHTML = '0 / 0';
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
					}
				},
				clear: function()
				{
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
					if (!viewframe.ViewActions.action_sign.initialConfig.initialDisabled)
						viewframe.ViewActions.action_sign.setDisabled(true);
					if (!viewframe.ViewActions.action_print.initialConfig.initialDisabled)
						viewframe.ViewActions.action_print.setDisabled(true);
					if (viewframe.editing && !viewframe.saveAtOnce)
						viewframe.ViewActions.action_save.setDisabled(true);
					viewframe.rowCount = 0;
				},
				beforeload: function()
				{
					if (viewframe.onBeforeLoadData)
					{
						viewframe.onBeforeLoadData();
					}
				}
			}
		});
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
			{name:'action_sign', id: 'action_sign' + Ext.id(), text:BTN_GRIDSIGN, tooltip: BTN_GRIDSIGN_TIP, icon: 'img/icons/digital-sign16.png', menu: new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: 'Подписать документ'
						,scope: this
						,handler: function() {
							if (!viewframe.signObject) {
								return false;
							}
							
							var selected_record = viewframe.ViewGridPanel.getSelectionModel().getSelected();
							if (selected_record && selected_record.get(viewframe.signObject+'_id')) {
								if (promedCardAPI != undefined && promedCardAPI.currProMedPlug != undefined && promedCardAPI.currProMedPlug.getBrowserPlugin() && promedCardAPI.currProMedPlug.getBrowserPlugin().valid) {
									viewframe.getLoadMask('Идёт подписание документа с помощью ЭЦП...').show();
									// подпись эцп
									signDocEcp({
										Doc_Type: viewframe.signObject,
										Doc_id: selected_record.get(viewframe.signObject+'_id'), 
										callback: function(result) {
											viewframe.getLoadMask().hide();
											
											if (result.success) {
												sw.swMsg.alert('Информация', 'Документ успешно подписан');
												selected_record.set(viewframe.signObject + '_IsSigned', 2);
												selected_record.commit();
											}
										}
									});
								} else {
									var links = 
										'<br><a href="/plugins/AuthApplet.msi">AuthApplet.msi</a>';
									if (navigator && navigator.appVersion && navigator.appVersion.indexOf("Linux")!=-1) {
										links = 
											'<br><a href="/plugins/promed-cardapi_i386.deb">promed-cardapi_i386.deb</a>'+
											'<br><a href="/plugins/promed-cardapi_amd64.deb">promed-cardapi_amd64.deb</a>';
									}
									sw.swMsg.alert('Ошибка', 'Не установлен плагин для подписания документов с помощью ЭЦП:<br>Плагин можно скачать по ссылке: ' + links);
								}
							} else {
								sw.swMsg.alert('Ошибка', 'Документ не заполнен или не готов к подписанию');
							}
						}
					}),
					new Ext.Action({
						text: 'Список версий документа'
						,scope: this
						,handler: function() {
							if (!viewframe.signObject) {
								return false;
							}
							
							var selected_record = viewframe.ViewGridPanel.getSelectionModel().getSelected();
							if (selected_record && selected_record.get(viewframe.signObject+'_id')) {
								getWnd('swDocVersionListWindow').show({
									Doc_id: selected_record.get(viewframe.signObject+'_id'),
									Doc_Type: viewframe.signObject
								});
							} else {
								sw.swMsg.alert('Ошибка', 'Документ не заполнен или не готов к подписанию');
							}
						}
					}),
					new Ext.Action({
						text: 'Верификация'
						,scope: this
						,handler: function() {
							if (!viewframe.signObject) {
								return false;
							}
							
							var selected_record = viewframe.ViewGridPanel.getSelectionModel().getSelected();
							if (selected_record && selected_record.get(viewframe.signObject+'_id')) {
								// делаем верификацию
								viewframe.getLoadMask('Идёт верификация документа...').show();
								Ext.Ajax.request({
									url: '/?c=ElectronicDigitalSign&m=documentVerification',
									params: {
										Doc_id: viewframe.signObject + '_' + selected_record.get(viewframe.signObject+'_id')
									},
									callback: function(options, success, response) {
										viewframe.getLoadMask().hide();
										var result = Ext.util.JSON.decode(response.responseText);
										if (result.success)
										{
											if (!Ext.isEmpty(result.valid)) {
												if (result.valid == 2) {
													sw.swMsg.alert('Сообщение', 'Документ попдисан и не изменялся с момента подписания');
													selected_record.set(viewframe.signObject + '_IsSigned', 2);
													selected_record.commit();
												} else if (result.valid == 1) {
													sw.swMsg.alert('Сообщение', 'Документ не актуален');
													selected_record.set(viewframe.signObject + '_IsSigned', 1);
													selected_record.commit();
												}
											} else {
												sw.swMsg.alert('Сообщение', 'Документ не подписан');
												selected_record.set(viewframe.signObject + '_IsSigned', null);
												selected_record.commit();
											}
										}
									}
								});
							} else {
								sw.swMsg.alert('Ошибка', 'Документ не заполнен и не был подписан');
							}
						}
					})
				]
			})},
			{name:'action_refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR_TIP, icon: 'img/icons/refresh16.png', handler: function() {viewframe.refreshRecords(null,0)}},
			{name:'action_print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT_TIP, icon: 'img/icons/print16.png', /*handler: function() {viewframe.printObjectList()}*/
				menuConfig: {
					printObject: {name: 'printObject', text: 'Печать', handler: function(){viewframe.printObject()}},
					printObjectList: {name: 'printObjectList', text: 'Печать текущей страницы', handler: function(){viewframe.printObjectList()}},
					printObjectListFull: {name: 'printObjectListFull', text: 'Печать всего списка', handler: function(){viewframe.printObjectListFull()}}
				}
			},
			{name:'action_resetfilter', text:'Сброс', tooltip: 'Сбросить фильтр', icon: 'img/icons/reset16.png', hidden: true, disabled: false, handler: function(o) {viewframe.filterReset();}},
			{name:'action_save', text:'Сохранить', tooltip: 'Сохранить изменения', icon: 'img/icons/save16.png', hidden: (viewframe.saveAtOnce || !viewframe.editing), disabled: true, handler: function(o) {viewframe.saveRecord(o)}}
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

			if (totalCount > 0) {
				//Если загружена всего одна страница, то запрещаем "Печать текущей страницы"
				//Также должен быть виден пункт "Печать всего списка"
				if (!action.menu.printObjectList.initialConfig.initialDisabled && !action.menu.printObjectListFull.isHidden()) {
					//log([this.id, count, totalCount]);
					if (!this.paging) {
						action.menu.printObjectList.setHidden(true);
					} else {
						if (count < totalCount) {
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
				if (menuItemsCount == 1 && menuItem) {
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
				case 'action_add': case 'action_edit': case 'action_view': case 'action_delete': case 'action_sign': case 'action_refresh': case 'action_save': case 'action_resetfilter':
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
							//disable = (key.inslist(['printObject']));
							//hide = (key.inslist(['printObject']));
							menuConfig[key]['initialDisabled'] = menuConfig[key]['disabled'] == undefined ? false : menuConfig[key]['disabled'];
							menuConfig[key]['disabled'] = menuConfig[key]['disabled'] == undefined ? disable : menuConfig[key]['disabled'];
							menuConfig[key]['hidden'] = menuConfig[key]['hidden'] == undefined ? hide : menuConfig[key]['hidden'];
							menuConfig[key]['id'] = menuConfig[key]['id'] == undefined ? viewframe.id+'_'+menuConfig[key]['name'] : menuConfig[key]['id'];
							menuActions[key] = new Ext.Action(menuConfig[key]);
							menu.push(menuActions[key]);
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
							id: 'id_'+this.actions[i]['name'],
							text: this.actions[i]['text'] == undefined ? 'Текст события не определен!' : this.actions[i]['text'],
							disabled: this.actions[i]['disabled'] == undefined ? false : this.actions[i]['disabled'],
							initialDisabled: this.actions[i]['disabled'] == undefined ? false : this.actions[i]['disabled'],
							hidden: this.actions[i]['hidden'] == undefined ? false : this.actions[i]['hidden'],
							tooltip: this.actions[i]['tooltip'] == undefined ? 'Подсказка события не определена!' : this.actions[i]['tooltip'],
							iconCls: 'x-btn-text',
							icon: this.actions[i]['icon'] == undefined ? '' : this.actions[i]['icon'],
							menu: this.actions[i]['menu'] == undefined ? '' : this.actions[i]['menu'],
							handler: this.actions[i]['handler'] == undefined ? function() {alert('Для события не указано действие!');} : this.actions[i]['handler']
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

		if (Ext.isEmpty(viewframe.signObject)) {
			this.ViewActions['action_sign'].initialConfig.disabled = true;
			this.ViewActions['action_sign'].initialConfig.hidden = true;
		}
		
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

		var toolbarItems = [
			this.ViewActions.action_add,
			this.ViewActions.action_edit,
			this.ViewActions.action_view,
			this.ViewActions.action_delete,
			this.ViewActions.action_sign
		]
		if (!this.ViewActions.action_refresh.isHidden()) {
			toolbarItems.push({xtype : "tbseparator"},this.ViewActions.action_refresh);
		}
		if (!this.ViewActions.action_print.isHidden()) {
			toolbarItems.push({xtype : "tbseparator"},this.ViewActions.action_print);
		}
		toolbarItems.push(
			{xtype : "tbseparator"},
			{xtype : "tbfill"},
			this.ViewActions.action_resetfilter,
			this.ViewActions.action_save,
			{xtype : "tbseparator"},
			{text: '0 / 0',xtype: 'tbtext'}
		);


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
					this.ViewGridModel = new Ext.grid.CheckboxSelectionModel(
					{
						singleSelect: false,
						multi:this.multi,
						listeners:
						{
							'rowselect': function(sm, rowIdx, record)
							{
								// дисаблим action_edit и action_view если выделено более 1 записи
								if (sm.getCount() > 1) {
									viewframe.ViewActions.action_edit.setDisabled(true);
									viewframe.ViewActions.action_view.setDisabled(true);
								}
								
								var count = this.grid.getStore().getCount();
								var rowNum = rowIdx + 1;
								// Alert: Хотя отсутствие второго условия можно заюзать при добавлении записей в гриде, в дальнейшем
								if ((record.data[viewframe.jsonData['key_id']]==null) || (record.data[viewframe.jsonData['key_id']]==''))
								{
									count = 0;
									rowNum = 0;
								}
								if (!this.grid.getTopToolbar().hidden)
								{
									this.grid.getTopToolbar().items.last().el.innerHTML = rowNum+' / '+count;
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
								// енаблим action_edit и action_view если выделено не более 1 записи
								if (sm.getCount() == 1) {
									viewframe.ViewActions.action_edit.setDisabled(false);
									viewframe.ViewActions.action_view.setDisabled(false);
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
								if (!this.grid.getTopToolbar().hidden)
								{
									this.grid.getTopToolbar().items.last().el.innerHTML = rowNum+' / '+count;
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
								//viewframe.initActionPrint();

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
		
		// Формируем сам грид
		this.ViewGridPanel = new Ext['grid'][(this.editing)?'EditorGridPanel':'GridPanel'](
		{
			//Добавляем пейджинг
			bbar: (this.paging)?(new Ext.PagingToolbar (
			{
				store: viewframe.ViewGridStore,
				pageSize: (viewframe.pageSize)?viewframe.pageSize:100,
				displayInfo: true,
				displayMsg: 'Отображаемые строки {0} - {1} из {2}',
				emptyMsg: "Нет записей для отображения"
			})):null,
			panel: this,
			enableDragDrop: this.enableDragDrop,
			ddGroup: (this.ddGroup)?this.ddGroup:null,
			clicksToEdit: this.clicksToEdit,
			id: viewframe.id+'Grid',
			region:'center',
			//height: this.height,//this.toolbar ? this.height-1 : this.height-1,
			title: this.titleGrid,
			loadMask: (Ext.getCmp('EvnPLSearchWindow'))?false:true,
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
					// ищем базовую форму по ownerCt и проверяем права на редактирование
					var ownerCur = viewframe.ownerCt;
					while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
						ownerCur = ownerCur.ownerCt;
					}
					viewframe.initActionPrint();
					if (typeof ownerCur.checkRole == 'function' && !ownerCur.checkRole('edit')) {
						viewframe.denyEdit = true;
					}
					
					if (viewframe.denyEdit) {
						viewframe.denyActions();
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
			view: (this.grouping)?new Ext.grid.GroupingView(
			{
				forceFit: (this.groupingView && this.groupingView.forceFit!=undefined)?this.groupingView.forceFit:false,
				enableGroupingMenu: (this.groupingView && this.groupingView.enableGroupingMenu!=undefined)?this.groupingView.enableGroupingMenu:false,
				enableNoGroups: (this.groupingView && this.groupingView.enableNoGroups!=undefined)?this.groupingView.enableNoGroups:false,
                hideGroupedColumn: (this.groupingView && this.groupingView.hideGroupedColumn!=undefined)?this.groupingView.hideGroupedColumn:true,
				showGroupName: (this.groupingView && this.groupingView.showGroupName!=undefined)?this.groupingView.showGroupName:true,
				showGroupsText: (this.groupingView && this.groupingView.showGroupsText!=undefined)?this.groupingView.showGroupsText:true,
				groupTextTpl: (this.groupTextTpl)?this.groupTextTpl:'{text} ({[values.rs.length]} {[values.rs.length == 1 ? "запись": ( values.rs.length.inlist([2,3,4]) ? "записи" : "записей")]})'
			}):null
		});

		if(!this.grouping){
		this.ViewGridPanel.view = new Ext.grid.GridView(
			{
				getRowClass : function (row, index)
				{
					var cls = '';

					if(row.get('Person_deadDT')){
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
				if (!viewframe.ViewActions.action_edit.isDisabled())
					viewframe.ViewActions.action_edit.execute();
			}
		});

		var that = this;
		// Добавляем события на keydown // то есть обработка горячих клавиш
		this.ViewGridPanel.on('keydown', function(e)
		{
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
			if (e.getKey().inlist([e.INSERT, e.F3, e.F4, e.F5, e.F9, e.ENTER, e.DELETE, e.S, e.END, e.HOME, e.PAGE_DOWN, e.PAGE_UP, e.TAB])
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
					var index = viewframe.ViewGridPanel.getStore().find(viewframe.jsonData['key_id'], selected_record.data[viewframe.jsonData['key_id']])
					viewframe.ViewGridPanel.focus();
					viewframe.ViewGridPanel.getView().focusRow(index);
					viewframe.ViewGridPanel.getSelectionModel().selectRow(index);
				}
				if (viewframe.callbackPersonEdit)
				{
					viewframe.selectedRecord = selected_record;
					params.callback = function() {this.callbackPersonEdit()}.bind(viewframe);
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
							if (!viewframe.ViewActions.action_edit.isDisabled())
							{
								viewframe.ViewActions.action_edit.execute();
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
					} else {
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
							params['key_id'] = selected_record.data[viewframe.jsonData['key_id']];
							params['key_field'] = viewframe.jsonData['key_id'];
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
									o.focus(true, 100);//Ext.getCmp(viewframe.focusTo.name).focus(true, 100);
								}
								else
								{
									Ext.Msg.alert('Ошибка кода', 'Не найден объект '+viewframe.focusTo.name+'!');
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
									Ext.Msg.alert('Ошибка кода', 'Не найден объект '+viewframe.focusTo.name+'!');
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
							ShowWindow('swPersonCardHistoryWindow', params);
						}
						else if (e.altKey && viewframe.checkPersonKey('Alt+F6'))
						{ // объединение
							// TO-DO: сделать процедуру объединения человека, когда в этом возникнет нужда
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

		Ext.applyIf(this,
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
//		sw.Promed.ViewFrame.superclass.initComponent.apply(this, arguments);
		this.callParent(arguments);
	}
});
