/**
* swDBLocalVersionWindow - окно просмотра и редактирования версий.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A. A. Markoff <markov@swan.perm.ru>
* @version      июнь.2012
*/

sw.Promed.swDBLocalVersionWindow = Ext.extend(sw.Promed.BaseForm, {
	title:lang['versii_lokalnyih_spravochnikov'],
	layout: 'border',
	id: 'DBLocalVersionWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	buttons: [{
			text: '-'
		}, {
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],
	/** Данный метод вызывается при открытии формы.
	 * @param - {Object} массив содержащий входные функции и переменные
	 */
	show: function() {
		sw.Promed.swDBLocalVersionWindow.superclass.show.apply(this, arguments);
		var win = this;
		//this.loadLpuSection();
		//this.Tree.getRootNode().select();
		this.GridVersion.loadData();
		this.GridDbList.loadData();
		this.checkMongo();
		
		if (!this.GridVersion.getAction('action_check_version')) {
			this.GridVersion.addActions({
				name: 'action_check_version',
				tooltip: langs('Проверка версии MongoDB'),
				iconCls: 'usluga-notok16',
				handler: function () {
					Ext.Ajax.request({
						url: '/?c=MongoDBWork&m=checkVersion',
						callback: function(options, success, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj && response_obj.Message) {
								Ext.Msg.alert(langs('Сообщение'), response_obj.Message);
							}
						}
					});
				}
			});
		}
		
		var firstGridDropTargetEl =  this.GridTables.getGrid().getView().el.dom.childNodes[0].childNodes[1];
		var firstGridDropTarget = new Ext.dd.DropTarget(firstGridDropTargetEl, {
			ddGroup    : 'gridDDGroup',
			copy       : true,
			notifyDrop : function(ddSource, e, data){
				// Generic function to add records.
				function addRow(record, index, allItems) {
					if(
						record
						&& !Ext.isEmpty(record.get('LocalDbList_schema'))
						&& !record.get('LocalDbList_schema').inlist(['rls', 'rpt'])
						&& record.get('LocalDbList_schema').indexOf('r') == 0
					) {
						var sch = record.data.LocalDbList_schema.replace('r','');
						sch = sch.trim();
						if(sch != getRegionNumber()){
							sw.swMsg.alert('Сообщение', 'Схема справочника '+record.get('LocalDbList_name')+' не соответствует текущему региону. Добавление справочника в сборку невозможно.');
            				return true;
						}
					}
					// Search for duplicates
					var firstGridStore = win.GridTables.getGrid().getStore();

					if (win.GridTables.getCount()==0) { // удаление пустой строки при добавлении
						var foundItem = firstGridStore.findBy(function(r,id) {
							if(r.get('LocalDBTables_Name') == null)
								return true;
						});
						if (foundItem!=-1) {
							firstGridStore.removeAt(foundItem);
						}
					}

					var foundItem = firstGridStore.findBy(function(rec) { return rec.get('LocalDBTables_Name') == record.data.LocalDbList_name; });
					// if not found
					if (foundItem  == -1) {
						// здесь можно создавать строку для добавления
						var data = {'LocalDBTables_Name': record.data.LocalDbList_name, 'LocalDBTables_id': null };
						var NewRecord = Ext.data.Record.create(win.GridTables.jsonData['store']);
						firstGridStore.add(new NewRecord(data));
						// todo: setGridRecord(win.GridTables.getGrid(),data,'add'); // это тоже самое
						if (win.GridTables.getAction('action_delete').isDisabled()) {
							win.GridTables.getAction('action_delete').setDisabled(false);
						}

						// Call a sort dynamically
						firstGridStore.sort('LocalDBTables_name', 'ASC');
						// Remove Record from the source
						// ddSource.grid.store.remove(record);
					}
				}
				// Loop through the selections
				Ext.each(ddSource.dragData.selections ,addRow);
				return(true);
			}.createDelegate(this)
		});
	},
	checkMongo: function() {
		var w = this;
		//w.getLoadMask('Проверка МонгоДБ').show();
		Ext.Ajax.request({
			url: '/?c=MongoDBWork&m=checkMongo',
			params: {'post':true},
			callback: function(options, success, response) {
				//w.getLoadMask().hide();
				if (success) {
					var r = Ext.util.JSON.decode(response.responseText);
					if (!r.success) {
						w.GridVersion.addActions({
							name:'action_new',
							tooltip: lang['otsutstvuet_biblioteka_mongodb'],
							iconCls: 'usluga-notok16',
							handler: function() {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function() {
										//
									},
									icon: Ext.Msg.WARNING,
									msg: r.Message,
									title: lang['vnimanie']
								});
							}
						});
						// Сообщение
						/*
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//
							},
							icon: Ext.Msg.WARNING,
							msg: r.Error_Msg,
							title: lang['vnimanie']
						});
						*/
					}
				}
			}
		});
	},
	isUserFace: function() {
		return (!isAdmin && getGlobalOptions().CurMedStaffFact_id && getGlobalOptions().CurLpuSection_id && getGlobalOptions().CurLpuSectionProfile_id);
	},
	deleteGridTables: function () {
		var record = this.GridTables.getGrid().getSelectionModel().getSelected();
		this.GridTables.getGrid().getStore().remove(record);
	},
	loadLocalDbList: function () {
		var params = {};
		params['LocalDbList_name'] = this.GridDbPanel.getForm().findField('LocalDbList_name').getValue();
		if (params['LocalDbList_name'].length>0) {
			this.GridDbList.loadData({globalFilters: params});
		} else {
			this.GridDbList.removeAll({clearAll: true});
			this.GridDbList.loadData();
		}
	},
	/**
	 * Создание новой версии
	 */
	createVersion: function() {
		// Запрос на создание новой версии
		var win = this;
		win.getLoadMask(lang['podojdite_sozdaetsya_novaya_sborochnaya_versiya']).show();
		Ext.Ajax.request({
			url: '/?c=MongoDBWork&m=createVersion',
			params: {'post':true},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success) {
					var r = Ext.util.JSON.decode(response.responseText);
					if (r.success)
						win.GridVersion.loadData();
				}
			}
		});
	},
	/**
	 * Фиксация сборочной версии
	 */
	fixedVersion: function() {
		// Запрос на фиксацию версии
		var win = this;

		// Предварительно надо собрать справочники которые мы должны включить в новую версию
		var tables = [];
		this.GridTables.getGrid().getStore().each(
			function(r) {
				if (r.get('LocalDBTables_Name')) { // если наименование справочника есть, то мы его фиксируем в массив
					tables.push(r.get('LocalDBTables_Name'));
				}
			}
		)

		var record = this.GridVersion.getGrid().getSelectionModel().getSelected();
		var params = {'LocalDBVersion_id':record.get('LocalDBVersion_id')};
		if (tables.length>0) { // Если справочники есть то выполняем запрос

			params.tables = tables.join('|');
			win.getLoadMask().show(lang['podojdite_fiksiruyutsya_izmeneniya_v_novoy_versii']);
			Ext.Ajax.request({
				url: '/?c=MongoDBWork&m=fixedVersion',
				params: params,
				timeout: 3600000,
				callback: function(options, success, response) {
					win.getLoadMask().hide();
					if (success) {
						var r = Ext.util.JSON.decode(response.responseText);
						//if (r.success)
						var prms = {callback: function(data){
							Ext.Ajax.request({
								url: '/?c=MongoDBWork&m=sendVersionMQmessage',
								params: params,
								failure: function(response, options) {
									Ext.Msg.alert(lang['oshibka'], lang['pri_otpravke_MQ_proizoshla_oshibka']);
								},
								success: function(response, action) {
									//showSysMsg(lang['kollektsii_v_mongodb_uspeshno_udalenyi_ne_zabudte_sozdat_novuyu_versiyu']);
								}
							});
							
						}};
						win.GridVersion.loadData(prms);
					}
				}
			});
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				},
				icon: Ext.Msg.WARNING,
				msg: lang['sborochnaya_versiya_ne_soderjit_dannyih'],
				title: lang['vnimanie']
			});
		}
	},
	initComponent: function()
	{
		var form = this;
		/*
		var configActions = {
			action_NoAction: {
				tooltip: lang['dannyiy_element_ne_imeet_sobyitiy'],
				text: lang['ne_imeet_sobyitiya'],
				iconCls : 'x-btn-text',
				disabled: true,
				handler: function()
				{
					alert(lang['net_sobyitiya']);
				}
			},
			refresh: {
				//id: 'uctwRefreshBtn',
				iconCls: 'refresh16',
				//text : "Обновить список услуг",
				tooltip : +lang['obnovlenie']+ +lang['spiska']+ +lang['uslug']+ <b>(F5)</b>",
				disabled: false,
				handler : function(button, event)
				{
					this.reloadTree(true);
				}.createDelegate(this)
			}
		};
		this.Actions = {};
		for(var key in configActions)
		{
			this.Actions[key] = new Ext.Action(configActions[key]);
		}
		*/

		this.GridVersion = new sw.Promed.ViewFrame({
			id: form.id+'Version',
			region: 'west',
			height: 203,
			width: 350,
			title:lang['versii'],
			contextmenu: false,
			object: 'LocalDBVersion',
			dataUrl: '/?c=MongoDBWork&m=getLocalDBVersion',
			autoLoadData: false,
			stringfields: [
				{name: 'LocalDBVersion_id', type: 'int', header: 'ID', key: true},
				{name: 'LocalDBVersion_Ver', header: lang['versiya'], autoexpand: true},
				{name: 'LocalDBVersion_setDate', type: 'datetime', header: lang['data_vremya']}
			],
			actions: [
				{name:'action_add', text: lang['sozdat'], handler: function() { this.createVersion(); }.createDelegate(this) },
				{name:'action_edit', text: lang['zafiksirovat'],  handler: function() { this.fixedVersion(); }.createDelegate(this), icon:'img/icons/ok16.png'},
				{name:'action_view', hidden: true},
				{name:'action_delete', text: '', url: '/?c=MongoDBWork&m=deleteVersion'},
				{name:'action_refresh', text: ''},
				{name:'action_print', text: '', hidden: true}
			],
			onLoadData: function() {
				//
			},
			onKeyDown1: function (){
				var e = arguments[0][0];
				if (e.getKey() == e.DELETE && e.altKey & e.shiftKey) {
					e.stopEvent();
					e.returnValue = false;
					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					sw.swMsg.show({
						icon: Ext.MessageBox.QUESTION,
						msg: '<span style="font-weight: bold;">Вы хотите удалить все коллекции в MongoDB, находясь в трезвом уме и здравой памяти, <br/>и понимаете все ответственность данного шага?</span><br/><span style="font-style: italic;">При этом не будут удалены коллецкия сессий, а также sysgen и syscache.</span>',
						title: lang['vnimanie'],
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ('yes' == buttonId) {
								Ext.Ajax.request({
									url: '/?c=MongoDBWork&m=getLocalDbTablesDrop',
									failure: function(response, options) {
										Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_kollektsiy_v_mongodb_proizoshla_oshibka']);
									},
									success: function(response, action) {
										showSysMsg(lang['kollektsii_v_mongodb_uspeshno_udalenyi_ne_zabudte_sozdat_novuyu_versiyu']);
									}
								});
							}
						}
					});
				};
				return false;
			},
			onRowSelect: function(sm, index, record) {
				var record = sm.getSelected();
				if (record.get('LocalDBVersion_id')>0) {
					//this.GridTables.getAction('action_add').setDisabled(false);
					this.GridTables.loadData({
						globalFilters: {
							limit: 100,
							start: 0,
							LocalDBVersion_id: record.get('LocalDBVersion_id')
						},
						noFocusOnLoad: true
					});
				}
				else {
					//this.GridTables.getAction('action_add').setDisabled(true);
					this.GridTables.removeAll({
						addEmptyRecord: true
					});
				}

				var enable = (record.get('LocalDBVersion_Ver')==0);
				this.GridVersion.getAction('action_edit').setDisabled(!enable);
				this.GridVersion.getAction('action_delete').setDisabled(!enable);
			}.createDelegate(this)
		});

		this.GridTables = new sw.Promed.ViewFrame({
			id: form.id+'Tables',
			region: 'center',
			height: 203,
			width: 350,
			//toolbar: false,
			//ddGroup: 'secondGridDDGroup',
			//enableDragDrop: true,
			title:lang['spravochniki_versii'],
			object: 'LocalDBTables',
			dataUrl: '/?c=MongoDBWork&m=getLocalDBFiles',
			autoLoadData: false,
			contextmenu: false,
			stringfields: [
				{name: 'LocalDBTables_id', type: 'int', header: 'ID', key: true},
				{name: 'LocalDBVersion_id', type: 'int', hidden: true},
				{name: 'LocalDBTables_Name', header: lang['spravochnik'], autoexpand: true}
			],
			actions: [
				{name:'action_add', text: '', disabled:true, handler: function() {sw.swMsg.alert('Внимание','Для формирования версии просто перетащите необходимые справочники<br/> с панели "Все справочники"');}},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', text: '', handler: function() { this.deleteGridTables(); }.createDelegate(this)},
				{name:'action_refresh', text: ''},
				{name:'action_print', text: ''}
			],
			onLoadData: function() {
				//
			}/*,
			onRowSelect: function(sm, index, record) {
				log('record',record);
			}*/
		});
		/*
		var filters = new Ext.grid.GridFilters({
			filters:[
				{type: 'string',  dataIndex: 'LocalDbList_schema'},
				{type: 'string',  dataIndex: 'LocalDbList_prefix'},
				{type: 'string', dataIndex: 'LocalDbList_nick'},
				{type: 'string',  dataIndex: 'LocalDbList_name'}
			]
		});
		*/
		this.GridDbList = new sw.Promed.ViewFrame({
			id: form.id+'DbList',
			region: 'center',
			ddGroup: 'gridDDGroup',
			enableDragDrop: true,
			object: 'LocalDBTables',
			singleSelect:false,
			editformclassname: 'swDBLocalDbListEditWindow',
			dataUrl: '/?c=MongoDBWork&m=getLocalDbList',
			autoLoadData: false,
			stringfields: [
				{name: 'LocalDbList_id', type: 'int', header: 'ID', key: true},
				{name: 'LocalDbList_schema', header: lang['shema'], width: 100, isparams: true},
				{name: 'LocalDbList_prefix', header: lang['prefiks'], width: 140, isparams: true},
				{name: 'LocalDbList_nick', header: lang['alias'], width: 140, isparams: true},
				{name: 'LocalDbList_name', header: lang['spravochnik'], width: 140, isparams: true},
				{name: 'LocalDbList_Descr', header: lang['spravochnik_russkoe_naimenovanie'], autoexpand: true, isparams: true},
				{name: 'LocalDbList_sql', header: 'SQL', hidden: true, isparams: true},
				{name: 'LocalDbList_module', header: lang['modul'], hidden: true, isparams: true},
				{name: 'LocalDbList_key', header: lang['klyuch'], hidden: true, isparams: true}
			],
			actions: [
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view', hidden: true},
				{name:'action_delete', url: '/?c=MongoDBWork&m=deleteLocalDbList'}
			],
			onLoadData: function() {
				//
			}
		});
		/*
		this.textSearch = new Ext.form.TextField({
			width: 175,
			fieldLabel : lang['naimenovanie'],
			name: 'LocalDbList_name',
			id: 'dblvwLocalDbList_name',
			allowBlank: true,
			lazyRender: true
		});
		*/

		this.GridDbPanel = new Ext.FormPanel({
			frame: true,
			height: 58,
			border: false,
			title:lang['vse_spravochniki'],
			region: 'north',
			labelWidth: 160,
			items: [{
				//anchor: '100%',
				width: 240,
				fieldLabel : lang['filtr_po_naimenovaniyu'],
				name: 'LocalDbList_name',
				xtype: 'textfield',
				allowBlank:true
			}],
			keys: [{
				alt: false,
				fn: function(inp, e) {
					this.loadLocalDbList();
				}.createDelegate(this),
				key: [ Ext.EventObject.ENTER ],
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			//region: 'center',
			layout: 'border',
			defaults: {
				split: true
			},
			items: [{
				region: 'west',
				width: 700,
				border: false,
				/*
					minSize: 700,
					maxSize: 700,
				*/
				defaults: {
					split: true
				},
				layout: 'border',
				items: [
					form.GridVersion, form.GridTables
				]
			}, {
				region: 'center',
				width: 700,
				border: false,
				/*
				 minSize: 700,
				 maxSize: 700,
				 */
				defaults: {
					split: true
				},
				layout: 'border',
				items: [
					form.GridDbList, form.GridDbPanel
				]
			}
			]
		});
		sw.Promed.swDBLocalVersionWindow.superclass.initComponent.apply(this, arguments);

	}
});
