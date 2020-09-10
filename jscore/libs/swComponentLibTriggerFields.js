/**
* swComponentLibTriggerFields - классы полей с триггером.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      17.10.2012
*/

// Класс для выбора значения из дерева
/*
	Тут надо создавать окно с деревом, в которое передавать object, id и признак необходимости
	использовать фильтр

	В открывающуюся форму передаются: object, id (значение поля с id = object + '_id')

	С формы возвращаются: id, name, nameWithPath
	id подставляется в поле object + '_id'
	В текстовое поле триггера подставляется name, если useNameWithPath == false, и nameWithPath,
	если useNameWithPath == true

	[update] [2014-02-19]: добавляем таб-панель. на первой вкладке дерево, на второй - стандартная форма поиска (фильтры + грид)

	Реализовал возможность добавлять к наименованиям код записи, параметр поля - showCodeMode
	Варианты:
	1 - <КОД>. <НАИМЕНОВАНИЕ>
	2 - <НАИМЕНОВАНИЕ> (<КОД>)
*/
sw.Promed.TreeSelectionField = Ext.extend(Ext.form.TriggerField, {
	allowBlank: true,
	/**
	 * Признак возможности выбирать только записи нижнего уровня
	 */
	allowLowLevelRecordsOnly: true,
	callback: Ext.emptyFn,
	clearValue: function() {
		if ( !this.disabled ) {
			this.parentNodeArray = new Array();
			this.setValue('');

			var valueField = Ext.getCmp(this.valueFieldId);

			if ( typeof valueField == 'object' ) {
				valueField.setValue('');
			}
		}
	},
	disabled: false,
	enableKeyEvents: true,
	fieldLabel: null,
	hiddenName: null,
    initComponent: function() {
		Ext.form.TriggerField.superclass.initComponent.apply(this, arguments);
    },
	listeners: {
		'keydown': function(inp, e) {
			if ( e.getKey() == e.DELETE ) {
				inp.clearValue();
			}
		}
	},
	object: null,
	onTrigger1Click: function(e, img) {
		if ( Ext.isEmpty(this.valueFieldId) ) {
			// log('Не указан идентификатор поля для хранения выбранного значения');
			return false;
		}
		else if ( this.disabled == true ) {
			// log('Поле задисаблено же');
			return false;
		}

		var triggerField = this;
		var valueField = Ext.getCmp(this.valueFieldId);

		if ( typeof valueField != 'object' ) {
			// log('Не определено поле для хранения выбранного значения');
			return false;
		}

		var treeSelectionWindow = new Ext.Window({
			buttonAlign: 'right',
			buttons: [{
				iconCls: 'ok16',
				handler: function () {
					treeSelectionWindow.select();
				},
				text: BTN_FRMSELECT
			}, {
				text: '-'
			}, {
				iconCls:'cancel16',
				handler: function () {
					treeSelectionWindow.close();
				},
				text: BTN_FRMCANCEL
			}],
			callback: function(data) {
				if ( triggerField.useNameWithPath == true ) {
					triggerField.setValue(data.nameWithPath);
				}
				else if ( triggerField.useCodeOnly == true ) {
					triggerField.setValue(data.code);
				}
				else {
					triggerField.setValue(data.name);
				}

				triggerField.parentNodeArray = data.parentNodeArray;

				valueField.setValue(data.id);
                triggerField.Sub_SysNick = data.Sub_SysNick;
                triggerField.code = data.code;
                log(data.name);
                triggerField.nameValue = data.name;

				// Вызов пользовательского callback
				triggerField.callback(data);
			},
			closeAction: 'destroy',
			doSearch: function() {
				var base_form = treeSelectionWindow.searchFilters.getForm();

				treeSelectionWindow.searchGrid.getGrid().getStore().load({
					callback: function() {
						//
					},
					params: {
						 code: base_form.findField('Object_Code').getValue()
						,name: base_form.findField('Object_Name').getValue()
						,object: triggerField.object
						,onlyActual: (triggerField.selectionWindowParams.onlyActual === true ? 1 : 0)
						,scheme: triggerField.scheme
					}
				});
			},
			firstLoad: true,
			height: (triggerField.selectionWindowParams.height ? triggerField.selectionWindowParams.height : 300),
			initComponent: function() {
				var form = this;

				// Тут надо посадить дерево
				form.tree = new Ext.tree.TreePanel({
					animate: false,
					autoLoad: false,
					autoScroll: true,
					border: false,
					enableDD: false,
					expandRecursive: function(value, parentNodeArray) {
						if ( !value || typeof parentNodeArray != 'object' || parentNodeArray.length == 0 ) {
							treeSelectionWindow.firstLoad = false;
							return false;
						}

						var i;
						var parentNode;
						var tree = this;

						for ( i in parentNodeArray ) {
							parentNode = tree.getNodeById(parentNodeArray[i]);

							if ( parentNode ) {
								parentNodeArray[i] = null;
								break;
							}
						}

						if ( parentNode ) {
							parentNode.expand(false, false, function(n) {
								var currentNode = tree.getNodeById(value);

								if ( currentNode ) {
									tree.getSelectionModel().select(currentNode);
									treeSelectionWindow.firstLoad = false;
								}
								else {
									tree.expandRecursive(value, parentNodeArray);
								}
							});
						}
						else {
							treeSelectionWindow.firstLoad = false;
						}
					},
					getLoadTreeMask: function(MSG) {
						if ( MSG )  {
							delete(this.loadMask);
						}

						if ( !this.loadMask ) {
							this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
						}

						return this.loadMask;
					},
					loader: new Ext.tree.TreeLoader({
						dataUrl: '/?c=Utils&m=getSelectionTreeData',
						listeners: {
							'beforeload': function (tl, node) {
								treeSelectionWindow.tree.getLoadTreeMask(langs('Загрузка...')).show();

								tl.baseParams.pid = null;
								tl.baseParams.Lpu_id = triggerField.Lpu_id;
								tl.baseParams.Sub_SysNick = null;
								tl.baseParams.nameValue = null;
								tl.baseParams.object = triggerField.object;
								tl.baseParams.onlyActual = (triggerField.selectionWindowParams.onlyActual === true ? 1 : 0);

								if ( !Ext.isEmpty(triggerField.scheme) ) {
									tl.baseParams.scheme = triggerField.scheme;
								}

								if ( !Ext.isEmpty(triggerField.showCodeMode) ) {
									tl.baseParams.showCodeMode = triggerField.showCodeMode;
								}

								if ( !Ext.isEmpty(triggerField.selectionWindowParams.treeSortMode) ) {
									tl.baseParams.treeSortMode = triggerField.selectionWindowParams.treeSortMode;
								}

								if ( node.getDepth() > 0 ) {
									tl.baseParams.pid = node.attributes.id;
									tl.baseParams.Sub_SysNick = !Ext.isEmpty(node.attributes.Sub_SysNick)?node.attributes.Sub_SysNick:null;
								}
							},
							'load': function(node) {
								callback: {
									treeSelectionWindow.tree.getLoadTreeMask().hide();

									if ( treeSelectionWindow.firstLoad == true ) {
										var currentValue = parseInt(valueField.getValue());

										// Если для поля уже установлено какое-то значение, то получаем список идентификаторов родительских записей
										if ( currentValue && currentValue != 0 ) {
											// log('Сначала ищем указанное значение в загруженных ветках первого уровня');
											// log('Если найдено, то выбираем в дереве нужный узел, устанавливаем treeSelectionWindow.firstLoad = false и прерываем выполнение метода');

											var currentNode = treeSelectionWindow.tree.getNodeById(currentValue);

											if ( currentNode ) {
												treeSelectionWindow.tree.getSelectionModel().select(currentNode);
												treeSelectionWindow.firstLoad = false;
												return false;
											}

											// log('Иначе получаем список идентификаторов родительских записей с помощью AJAX-запроса');
											// log('Затем рекурсивно открываем нужные ветки дерева и устанавливаем текущее значение');
											// log('При обнаружении нужного значения, устанавливаем treeSelectionWindow.firstLoad = false');

											if ( typeof triggerField.parentNodeArray == 'object' && triggerField.parentNodeArray.length > 0 ) {
												// Работаем со списком triggerField.parentNodeArray

												var i;
												var parentNodeArray = new Array();

												for ( i in triggerField.parentNodeArray ) {
													parentNodeArray.push(triggerField.parentNodeArray[i]);
												}

												treeSelectionWindow.tree.expandRecursive(currentValue, parentNodeArray);
											}
											else {
												// Тянем список с сервера
												var loadMask = new Ext.LoadMask(treeSelectionWindow.getEl(), { msg: langs('Получение идентификаторов родительских записей... ') });
												loadMask.show();

												Ext.Ajax.request({
													failure: function(response, options) {
														loadMask.hide();
														sw.swMsg.alert(langs('Ошибка'), langs('При получении списка идентификаторов родительских записей произошла ошибка: ') + response.responseText);
													},
													params: {
														id: currentValue,
														object: triggerField.object,
														Sub_SysNick: triggerField.Sub_SysNick,
														scheme: triggerField.scheme,
                                                        Lpu_id: triggerField.Lpu_id
													},
													success: function(response, options) {
														loadMask.hide();

														var i;
														var response_obj = Ext.util.JSON.decode(response.responseText);

														triggerField.parentNodeArray = new Array();

														for ( i in response_obj ) {
															triggerField.parentNodeArray.push(response_obj[i]);
														}

														treeSelectionWindow.tree.expandRecursive(currentValue, response_obj);
													},
													url: '/?c=Utils&m=getParentNodeList'
												});
											}
										}
										else {
											treeSelectionWindow.firstLoad = false;
										}
									}
								}
							}
						}
					}),
					region: 'center',
					root: {
						expanded: true,
						id: 'root',
						nodeType: 'async',
						text: triggerField.object
					},
					rootVisible: false,
					selModel: new Ext.tree.KeyHandleTreeSelectionModel()
					// title: 'Услуги'
				});

				// Двойной клик на ноде выполняет соответствующий акшен
				form.tree.on('dblclick', function(node, event) {

                    if ( node.attributes.leaf != true && triggerField.object != 'SubDivision') {
						node.expand();
					}
					else {
						treeSelectionWindow.select();
					}
				});

				form.searchFilters = new Ext.form.FormPanel({
					//autoHeight: true,
					height: 70,
					frame: true,
					//labelAlign: 'top',
					region: 'north',
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							columnWidth: 0.8,
							layout: 'form',
							items: [{
								anchor: '100%',
								enableKeyEvents: true,
								fieldLabel: langs('Код'),
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											form.searchGrid.focus();
										}
									}
								},
								name: 'Object_Code',
								xtype: 'textfield'
							}, {
								anchor: '100%',
								enableKeyEvents: true,
								fieldLabel: langs('Наименование'),
								name: 'Object_Name',
								xtype: 'textfield'
							}]
						}, {
							bodyStyle: 'padding-left: 5px;',
							border: false,
							columnWidth: 0.2,
							layout: 'form',
							items: [{
								handler: function () {
									treeSelectionWindow.doSearch();
								}.createDelegate(this),
								text: langs('Поиск'),
								topLevel: true,
								xtype: 'button'
							}]
						}]
					}],
					keys: [{
						fn: function(e) {
							treeSelectionWindow.doSearch();
						},
						key: Ext.EventObject.ENTER,
						stopEvent: true
					}]
				});

				form.searchGrid = new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', disabled: true, hidden: true },
						{ name: 'action_edit', disabled: true, hidden: true },
						{ name: 'action_view', disabled: true, hidden: true },
						{ name: 'action_delete', disabled: true, hidden: true },
						{ name: 'action_refresh', disabled: true, hidden: true }
					],
					autoLoadData: false,
					border: true,
					dataUrl: '/?c=Utils&m=getObjectSearchData',
					onDblClick: function() {
						treeSelectionWindow.select();
					},
					onEnter: function() {
						treeSelectionWindow.select();
					},
					onLoadData: function() {
						//
					},
					onRowSelect: function(sm, index, record) {
						//
					},
					paging: false,
					region: 'center',
					stringfields: [
						{ name: 'id', type: 'int', header: 'ID', key: true },
						{ name: 'code', type: 'string', header: langs('Код'), width: 150 },
						{ name: 'name', type: 'string', header: langs('Наименование'), id: 'autoexpand' },
						{ name: 'Sub_SysNick', type: 'string', hidden: true },
						{ name: 'nameValue', type: 'string', hidden: true },
						{ name: 'childCnt', type: 'int', hidden: true }
					],
					toolbar: false
				});

				form.tabPanel = new Ext.TabPanel({
					activeTab: 0,
					//autoHeight: true,
					autoScroll: true,
					//autoWidth: true,
					border: false,
					defaults: { bodyStyle: 'padding: 0px;' },
					layoutOnTabChange: true,
					listeners: {
						'tabchange': function(panel, tab) {
							//
						}
					},
					plain: true,
					region: 'center',
					items: [{
						autoScroll: true,
						border: false,
						layout: 'border',
						listeners: {
							'activate': function(panel) {
								//
							}
						},
						region: 'center',
						tabNum: 1,
						title: langs('Структура'),

						items: [
							form.tree
						]
					}, {
						//autoHeight: true,
						autoScroll: true,
						border: false,
						layout: 'border',
						listeners: {
							'activate': function(panel) {
								//
							}
						},
						region: 'center',
						tabNum: 2,
						title: langs('Поиск'),

						items: [
							 form.searchFilters
							,form.searchGrid
						]
					}]
				});

				Ext.apply(form, {
					items: [
						form.tabPanel
					]
				});

				sw.Promed.BaseForm.superclass.initComponent.apply(form, arguments);
			},
			layout: 'border',
			modal: true,
			object: triggerField.object,
			select: function() {
				// Тянем данные в зависимости от активной вкладки
				switch ( treeSelectionWindow.tabPanel.getActiveTab().tabNum ) {
                    case 1:
						// Из дерева
						var treeData = {},
						    selectedNode = treeSelectionWindow.tree.getSelectionModel().getSelectedNode(),
						    parentNode = selectedNode.parentNode,
						    parentNodeArray = [];

						if ( !selectedNode || !selectedNode.attributes || (triggerField.allowLowLevelRecordsOnly == true && !selectedNode.attributes.leaf) ) {
							return false;
						}

						// Пока тестовые значения
						treeData.id = selectedNode.attributes.id;
						treeData.code = selectedNode.attributes.object_code;
						treeData.name = selectedNode.attributes.text;
						treeData.nameValue = null;
						treeData.Sub_SysNick = !Ext.isEmpty(selectedNode.attributes.Sub_SysNick)?selectedNode.attributes.Sub_SysNick:null;
						treeData.nameWithPath = selectedNode.attributes.text;

                        parentNodeArray.push(selectedNode.attributes.id);
						while ( parentNode.id != 'root' ) {
							treeData.nameWithPath = parentNode.text + (!!triggerField.selectionWindowParams.separator?triggerField.selectionWindowParams.separator: ' / ') + treeData.nameWithPath;
							parentNodeArray.push(parentNode.id);
							parentNode = parentNode.parentNode;
						}

						treeData.parentNodeArray = parentNodeArray;

						// Вызываем callback
						treeSelectionWindow.callback(treeData);

						// Закрываем окно
						treeSelectionWindow.close();
					break;

					case 2:
						// С формы поиска
						var data = {},
						    selectedRecord = treeSelectionWindow.searchGrid.getGrid().getSelectionModel().getSelected();

						if ( typeof selectedRecord != 'object' || Ext.isEmpty(selectedRecord.get('id')) ) {
							return false;
						}
						else if ( triggerField.allowLowLevelRecordsOnly == true && !Ext.isEmpty(selectedRecord.get('childCnt')) && selectedRecord.get('childCnt') != 0 ) {
							sw.swMsg.alert(langs('Ошибка'), langs('Выбранная запись не является объектом нижнего уровня'));
							return false;
						}

						// Пока тестовые значения
						data.id = selectedRecord.get('id');
						data.code = selectedRecord.get('code');
						data.name = selectedRecord.get('name');
						data.nameValue = selectedRecord.get('nameValue');
						data.Sub_SysNick = (!Ext.isEmpty(selectedRecord.get('Sub_SysNick')))?selectedRecord.get('Sub_SysNick'):null;
						data.nameWithPath = selectedRecord.get('name');

						if ( triggerField.useNameWithPath == true ) {
							Ext.Ajax.request({
								failure: function(response, options) {
									sw.swMsg.alert('Ошибка', '<div>При получении получении полного наименования:</div><div>' + response.responseText + '</div>');
								},
								params: {
									id: data.id,
									object: triggerField.object,
									Sub_SysNick: data.Sub_SysNick,
									scheme: triggerField.scheme
								},
								success: function(response, options) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( response_obj[0] && !Ext.isEmpty(response_obj[0].name) ) {
										data.nameWithPath = response_obj[0].name;
									}

									if ( response_obj[0] && !Ext.isEmpty(response_obj[0].parentNodeArray) ) {
										data.parentNodeArray = response_obj[0].parentNodeArray;
									}

									// Вызываем callback
									treeSelectionWindow.callback(data);

									// Закрываем окно
									treeSelectionWindow.close();
								},
								url: '/?c=Utils&m=getObjectNameWithPath'
							});
						}
						else {
							// Вызываем callback
							treeSelectionWindow.callback(data);

							// Закрываем окно
							treeSelectionWindow.close();
						}
					break;

					default:
						return false;
					break;
				}
			},
			title: (triggerField.selectionWindowParams.title ? triggerField.selectionWindowParams.title : langs('Выбор')),
			width: (triggerField.selectionWindowParams.width ? triggerField.selectionWindowParams.width : 400)
		});

		treeSelectionWindow.show();
	},
	parentNodeArray: [],
	readOnly: true,
	Sub_SysNick: '',
	Lpu_id: 0,
	selectionWindowParams: {
		height: 500,
		onlyActual: false,
		separator: ' / ', // по умолчанию
		title: langs('Выбор'),
		/**
		 * Варианты сортировки дерева:
		 *     первый разряд - сортировка по id
		 *     второй разряд - сортировка по Code
		 *     третий разряд - сортировка по Name
		 *     четвертый разряд - сортировка по наличию дочерних элементов
		 *
		 * Возможные значения:
		 *     0 - отсутствие сортировки
		 *     1 - сортировка по возрастанию
		 *     2 - сортировка по убыванию
		 */
		treeSortMode: '0000',
		width: 600
	},
	setNameWithPath: function(separator) {
		var triggerField = this;

		if ( triggerField.useNameWithPath == true ) {
			// Тянем полное наименование
			var valueField = Ext.getCmp(triggerField.valueFieldId);

			if ( typeof valueField == 'object' ) {

                if ( triggerField.object == 'SubDivision') {

                }

				Ext.Ajax.request({
					failure: function(response, options) {
						sw.swMsg.alert('Ошибка', '<div>При получении получении полного наименования:</div><div>' + response.responseText + '</div>');
					},
					params: {
						id: valueField.getValue(),
						separator: separator,
						object: triggerField.object,
						Sub_SysNick: triggerField.Sub_SysNick,
						scheme: triggerField.scheme,
						Lpu_id: triggerField.Lpu_id
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj[0] && response_obj[0].name ) {
							triggerField.setValue(response_obj[0].name);
						}
					},
					url: '/?c=Utils&m=getObjectNameWithPath'
				});
			}
		}
	},
	showCodeMode: 0,
	trigger1Class: 'x-form-search-trigger',
	useNameWithPath: true,
	useCodeOnly: false,
	valueFieldId: null
});

sw.Promed.TreeSelectionField.prototype.initComponent = Ext.form.TwinTriggerField.prototype.initComponent;
sw.Promed.TreeSelectionField.prototype.getTrigger = Ext.form.TwinTriggerField.prototype.getTrigger;
sw.Promed.TreeSelectionField.prototype.initTrigger = Ext.form.TwinTriggerField.prototype.initTrigger;
sw.Promed.TreeSelectionField.prototype.trigger2Class = 'x-form-clear-trigger';
// sw.Promed.TreeSelectionField.prototype.onTrigger1Click = Ext.form.TriggerField.prototype.onTriggerClick;
sw.Promed.TreeSelectionField.prototype.onTrigger2Click = function() { this.clearValue(); };

Ext.reg('swtreeselectionfield', sw.Promed.TreeSelectionField);
Ext.reg('swTreeSelectionField', sw.Promed.TreeSelectionField);
