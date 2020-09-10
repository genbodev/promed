/* 
 * Шаблон рабочего места БСМЭ
 */

Ext.define('common.BSME.DefaultWP.BSMEDefaultWP.swBSMEDefaultWorkPlace', {
	extend: 'Ext.window.Window',
    autoShow: true,
	maximized: true,
	width: 1000,
	refId: 'bsmedefaultwindow',
	baseCls: 'arm-window',
    header: false,
	renderTo: Ext.getCmp('inPanel').body,
	id: 'BSMEDefaultWorkPlace',
	layout: {
        type: 'fit'
    },
	constrain: true,
	requestViewPanelButtons: [],
	RequestTemplate: new Ext.XTemplate(''),
	splitterWidth: 8,
	createRequestButton: null,
	createRequestButtonHandler: Ext.emptyFn, // Обработчик нажатия кнопки создания заявки
	printRequestButton: null,
	JournalTreeStoreChildren: [], // Массив дерева журналов для конкретного арма
	_JournalTreeStoreIdProperty: 'Journal_Type',
	additionalRequestListDataviewStoreFields: [],
	getEvnForensicRequestUrl: null, //урл для получения заявки
	archivePanelItems: [],
	archiveGrids: {},
	requestListViewItemClick: Ext.emptyFn,
	updateRequestListTimeout: null,
	ReportPanel: null, // ОТЧЕТ Деятельность бюро
	TabPanelItems: [
		{
			title: 'Все заявки <em>0</em>',
			itemId: 'All',
			iconCls: 'tab_all_icon16'
		}, {
			title: 'Новые <em>0</em>',
			itemId: 'New',
			iconCls: 'tab_new_icon16'
		}, {
			title: 'Назначенные <em>0</em>',
			itemId: 'Appoint',
			iconCls: 'tab_appoint_icon16'
		}, {
			title: 'На проверку <em>0</em>',
			itemId: 'Check',
			iconCls: 'tab_check_icon16'
		}, {
			title: 'Одобренные <em>0</em>',
			itemId: 'Approved',
			iconCls: 'tab_approved_icon16'
		}
	],
	updateRequestCountInTabsParams: {},
	updateRequestCountInTabs: function() {
		var me = this;
		
		var params = me.updateRequestCountInTabsParams?me.updateRequestCountInTabsParams:{};
		var searchValues = this.SearchForm.getValues();
		
		params['search']  =JSON.stringify(searchValues);
		
		Ext.Ajax.request({
			url: '?c=BSME&m=getRequestCount',
			params: me.updateRequestCountInTabsParams?me.updateRequestCountInTabsParams:{},
			callback: function(params,success,result) {

				if (result.status !== 200) {
					//Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
					return false;
				} 

				var resp = Ext.JSON.decode(result.responseText, true);
				if (resp === null) {
					//Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
					return false;
				}

				if (me.centerPanel.down('tabpanel')) {
					var tabs = ['All','New','Appoint','Check','Approved'];
					var tab = {};
					for (var i=0; i<tabs.length; i++) {
						tab = me.centerPanel.down('tabpanel').down('[itemId='+tabs[i]+']');
						if (tab) {
							tab.setTitle(tab.title.replace(/\d+/,(resp[tabs[i]])?resp[tabs[i]]:0));
						}

					}

				}

			}
		});	
	},
	loadRequestViewStore: function(data ) {
		
		var store = this.RequestListDataview.getStore();
		var aftercallback = Ext.emptyFn;
		var wnd = this;
		var idProperty = wnd.RequestListDataview.getStore().idProperty;
		var key;
		
		if (data) {
			if (data.params) {
				for (key in data.params) {
					if (data.params.hasOwnProperty(key)) {
						store.getProxy().setExtraParam(key,data.params[key]);
					}
				}
			}
			if (typeof data.aftercallback == 'function') {
				var aftercallback = data.aftercallback;
			}
		}
		
		var searchValues = this.SearchForm.getValues();
		
		store.getProxy().setExtraParam('search',JSON.stringify(searchValues));
		
//		
//		for (key in searchValues) {
//			if (searchValues.hasOwnProperty(key)) {
//				store.getProxy().setExtraParam(key,searchValues[key]);
//			}
//		}
		
		//если стор отфильтрован(в режиме поиска - не обновлять)
		//if(store.isFiltered()) return;
		
		
		var selection = wnd.RequestListDataview.getSelectionModel().getSelection();
		var id = null;
		if (selection && selection[0]) {
			id = selection[0].get(idProperty);
		}
		
		store.currentPage = 1;
		store.abort().load({
			callback: function(records, operation, success) {
				
				if (id && idProperty) {
					var rec = wnd.RequestListDataview.getStore().findRecord(idProperty,id);
					if (rec) {
						wnd.RequestListDataview.getSelectionModel().select([rec]);
					}
				}
				
				aftercallback();
			},
			scope: this
		});
		this.updateRequestCountInTabs();
	},
	//Функция получения записи в хранилище для текущей выбранной заявки
	getCurrentRequestRecord:  function() {
		var selection = this.RequestListDataview.getSelectionModel().getSelection();
		if (selection && (selection.length == 1) &&  selection[0]) {
			return selection[0];
		} else {
			return false;
		}
	},
	clearRequestView: function() {
		var me = this;
		me.disableToolbarButtons();
		me.RequestViewPanel.update('');
		me.RequestViewPanel.doLayout();
		me.clearExpertisePanel();
		
		if ( me.printRequestButton) {
			me.printRequestButton.setDisabled(true);
		}
		if (me.ReportPanel) {
			me.ReportPanel.hide();
		}
	},
	clearExpertisePanel: function() {
		var me = this;
		me.ExpertisePanel.down('[itemId=expertise]').update('');
	},
	disableToolbarButtons: function() {
		
		var me = this,
			tbar = me.RequestViewPanel.down('toolbar'),
			items = (Ext.isObject(tbar) && Ext.isObject(tbar.items))?tbar.items:null,
			i;
			
		if (items) {
			for (i = 0; i<items.getCount(); i++) {
				items.getAt(i).setDisabled(true);
			}
		}
	},
	updateRequestView: function(EvnForensic_id) {
		var me = this;
		
		if (!EvnForensic_id) {
			Ext.Msg.alert('Ошибка', 'Не передан идентификатор заявки');
			return false;
		}

		if (me.getEvnForensicRequestUrl) {

			var loadMask =  new Ext.LoadMask(me.centerPanel.down('[itemId=RequestView]'), {msg:"Пожалуйста, подождите, идёт получение данных о заявке..."}); 
			loadMask.show();

			Ext.Ajax.request({
				params: {
					EvnForensic_id: EvnForensic_id
				},
				url: me.getEvnForensicRequestUrl, 
				callback: function(params,success,result) {
					
					if (result.status !== 200) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
						return false;
					} 

					var resp = Ext.JSON.decode(result.responseText, true);
					if (resp === null) {
						loadMask.hide();
						Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
						return false;
					}
					
					me.RequestViewPanel.update(resp);
					me.RequestViewPanel.doLayout();
					
					me.loadExpertisePanel(resp.EvnXml_id);
					
					me.loadReportPanel();
					
					loadMask.hide();
				}
			});
		}
	},
	loadExpertisePanel: function(EvnXml_id) {
		var me = this;
		if (!EvnXml_id) {
			//Ext.Msg.alert('Ошибка', 'Не передан идентификатор заключения');
			
			if (me.printRequestButton) {
				me.printRequestButton.setDisabled(true);
			}
			
			me.ExpertisePanel.hide();
			return false;
		}
		if (me.printRequestButton) {
			me.printRequestButton.setDisabled(false);
		}
		me.ExpertisePanel.show();
		
		me.ExpertisePanel._EvnXml_id = EvnXml_id;
		var loadMask = new Ext.LoadMask(me.ExpertisePanel, {msg:"Пожалуйста, подождите, идёт получение данных об экспертизе..."}); 
			loadMask.show();
			
		Ext.Ajax.request({
			params: {
				EvnXml_id: EvnXml_id
			},
			url: '/?c=EvnXml&m=loadEvnXmlForm', 
			callback: function(params,success,result) {

				if (result.status !== 200) {
					loadMask.hide();
					Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
					return false;
				} 

				var resp = Ext.JSON.decode(result.responseText, true);
				if (resp === null) {
					loadMask.hide();
					Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
					return false;
				}
				log({resp:resp});
				me._updateExpertisePanel(resp);
				
				loadMask.hide();
			}
		});
	},
	
	// Загружает данные для формы отчета "Деятельности бюро"
	loadReportPanel: function(){					
		if ( this.ReportPanel === null ) {
			return;
		}
		
		var me = this,
			EvnForensicSub_id = this.RequestViewPanel._Evn_id,
			loadMask = new Ext.LoadMask(me.ExpertisePanel,{msg:"Получаем данные отчета деятельности бюро..."}),
			form = me.ReportPanel.getForm();

		form.reset();
		form.setValues({
			EvnForensicSub_id: EvnForensicSub_id
		});
		
		Ext.Ajax.request({
			params: {
				EvnForensicSub_id: EvnForensicSub_id
			},
			url: '/?c=BSME&m=getEvnForensicSubReportWorking',
			callback: function (params, success, result) {
				if (result.status !== 200) {
					loadMask.hide();
					Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
					return false;
				} 

				var resp = Ext.JSON.decode(result.responseText, true);
				if (resp === null) {
					loadMask.hide();
					Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
					return false;
				}
				
				// Этот метод вызывается два раза, т.к. при переходе с одной заявки
				// на другую сохраняется состояние disabled, а если кобмбо деактивирован
				// к нему нельзя применить setValues.
				me.ReportPanelComboEitherOne();
				
				if ( typeof resp[0] === 'object' ) {
					form.setValues( resp[0] );
				}

				me.ReportPanelComboEitherOne();
				me.ReportPanel.show();
				
				loadMask.hide();
			}
		});
	},
	
	// Проверяет активность комбобоксов в зависимости от заполнения
	ReportPanelComboEitherOne: function(combo){
		if ( this.ReportPanel === null ) {
			return;
		}
		
		var combos_list = ['ForensicValuationInjury_id','ForensicDefinitionSexualOffenses_id','ForensicSubDefinition_id'],
			form = this.ReportPanel.getForm();
	
		// Просто проверим все комбобоксы на заполнение?
		if ( typeof combo === 'undefined' ) {
			var one_selected = false,
				combo_disabled_cache = [];
		
			// Обходим все комбобоксы и запоминаем те у которых нет значения
			for( var i=0; i<combos_list.length; i++ ) {
				combo = form.findField(combos_list[i]);
				if ( combo.value ) {
					one_selected = true;
				} else {
					combo_disabled_cache.push(combo);
				}
			}
			
			// Есть комбобокс в котором установлено значение?
			// true - тогда деактивируем остальные
			// false - в противном случае активируем их,
			//		   т.к. они могут сохранить свое состояние при переключении формы
			for( var i=0; i<combo_disabled_cache.length; i++ ) {
				combo_disabled_cache[i].setDisabled(one_selected);
			}
		} else {
			var disable = combo.value == '' || combo.value === null ? false : true;
			for( var i=0; i<combos_list.length; i++ ) if ( combos_list[i] != combo.name ) {
				form.findField(combos_list[i]).setDisabled(disable);
			}
		}
		
		return;
	},
	
	_updateExpertisePanel: function(data) {
		if (!data || !data['html'] || !data['formData']) {
			return false;
		}
		
		var me = this;
		var panel = me.ExpertisePanel.down('[itemId=expertise]');
		var html = data['html'];
		var fieldParams = {};
		var key, 
			value, 
			i, 
			value_indexes,
			item;
		
		var formData = data['formData'];
		
		for (key in formData) {
			fieldParams = formData[key];
			value = '';
			switch (fieldParams.type) {
				/*
				 *case 'combobox': 
				case 'textareafield': 
					value = field.getValue();
					break;

				case 'checkboxgroup':
					value = (field.getValue()[field.name]) ? field.getValue()[field.name].join(',') : '' ;
					break;

				case 'radiogroup':
					value = field.getValue()[field.name] || '';
					break;

				default:
				 */
				
				case 'combobox':
				case 'checkboxgroup':
				case 'radiogroup':
					if (!fieldParams.value) {
						value = fieldParams.value;
					} else {
						
						value_indexes = (fieldParams.value+'').split(',');
						
						for (i=0; i<fieldParams.items.length; i++) {
							item = fieldParams.items[i];
							if (value_indexes.indexOf(item.id) != -1) {
								value += item.fieldLabel+'; ';
							}
						}
					}
					break;
				
				case 'textarea':
				default:
					value = fieldParams.value;
					break;	
			}
			html = html.replace('{'+ fieldParams.name + '}' , '<div class = "'+ fieldParams.name + '" > '+ value + ' </div>');
			
		}
		
		panel.update(html);
		
	},
	updateCurrentRequest: function() {
		this.updateRequestView(this.RequestViewPanel._Evn_id);
	},
    initComponent: function() {
		var key, i, me = this;
		
		var RequestListDataviewStoreFields = [
			{name: 'EvnForensic_id', type: 'int'},
			{name: 'EvnForensic_Num', type: 'string'},
			{name: 'Expert_Fin', type: 'string'},
			{name: 'EvnForensicType_Name', type: 'string'},
			{name: 'Person_Fio', type: 'string'},
			{name: 'Evn_insDT', type: 'string'},
			{name: 'EvnStatus_SysNick', type: 'string'}
		];
		
		// Добавляем дополнительные поля в стор для АРМов, если они есть
		for (i=0;i<this.additionalRequestListDataviewStoreFields.length;i++) {
			RequestListDataviewStoreFields.push(this.additionalRequestListDataviewStoreFields[i]);
		}
		
		
		this.RequestListDataviewStore = Ext.create('sw.ExtendedStore',{

			autoLoad: false,
			pageSize: 10,
			storeId: this.id+'RequestListDataviewStore',
			idProperty: 'EvnForensic_id',
			fields: RequestListDataviewStoreFields,				
			proxy: {
//				limitParam: undefined,
//				startParam: undefined,
//				paramName: undefined,
//				pageParam: undefined,
				type: 'ajax',
				url: '/?c=BSME&m=getJournalRequestList',
				reader: {
					type: 'json',
					successProperty: 'success',
					totalProperty: 'totalCount',
					root: 'data'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		});
		
		/**
		 * autoLoad: false,
				pageSize: 50,
				storeId: 'searchDrugListGridStore',
				fields: [
					
					{name: 'DrugTorg_Name', type: 'string'},
					{name: 'Drug_id', type: 'int'},
					{name: 'DrugPrepFas_id', type: 'int'},
					{name: 'Drug_Nomen', type: 'string'},
					{name: 'Drug_Name', type: 'string'},
					{name: 'DrugForm_Name', type: 'string'},					
					{name: 'Drug_Dose', type: 'int'},
					{name: 'Drug_Fas', type: 'int'},
					{name: 'Drug_PackName', type: 'string'},
					{name: 'Drug_Firm', type: 'string'},
					{name: 'Drug_Ean', type: 'int'},
					{name: 'Drug_RegNum', type: 'string'},
					{name: 'DrugMnn', type: 'string'}
				],
				sorters: {
					property: 'DrugTorg_Name',
					direction: 'ASC'
				},		 */

		// Панель отображения списка заявок
		this.RequestListDataview = Ext.create('Ext.view.View', {
			id: this.id+'_RequestView',
			cls: 'bsme-request-view',
			preserveScrollOnRefresh : true,
			overflowY: 'scroll',
			store: this.RequestListDataviewStore,
			flex: 1,
			width: '100%',
			height: '100%',
			itemSelector: 'div.request-wrap',
			preserveScrollOnRefresh: true,
			tpl:  new Ext.XTemplate(
				'<tpl for=".">',
					'<div class="request-wrap" style="cursor: pointer;">',
						'<div class="request-text">',							
							'<p><span class="number">{EvnForensic_Num}</span><span class="right">{Evn_insDT}</span><br/>',							
							'<span class="fio">{Person_Fio}</span><br/>',
							// Временно скрыли
//							'{EvnForensicType_Name}<br/>',
							'Эксперт: {Expert_Fin}</p>',
						'</div>',
					'</div>',
				'</tpl>'
			),
			listeners: {
				itemclick: function(self, rec, html_element, node_index, event){
					var /*elCls = event.target.getAttribute('class'),*/
						parentObj = this;

					me.requestListViewItemClick(rec);
					
					me.updateRequestView(rec.get('EvnForensic_id'))
				}
			}
		});
		
		// Панель отображения заявки
		
		// Данное решение использовано всвязи с необходимостью навешивать обработчики
		//  на элементы после обновления RequestTemplate. Поэтому описывать эти 
		//  обработчики будем там же в RequestTemplate
		
		var RequestViewPanelAfterlayout = Ext.emptyFn;
		
		if (me.RequestTemplate && me.RequestTemplate.afterlayout && (typeof me.RequestTemplate.afterlayout == 'function')) {
			RequestViewPanelAfterlayout = me.RequestTemplate.afterlayout;
		}
		
		this.RequestViewPanel = Ext.create('Ext.panel.Panel',{
			cls: 'descPanel',
			tpl: me.RequestTemplate.compile(),
			tbar: this.requestViewPanelButtons,
			_Evn_id: null,
			autoScroll : true,
			listeners: {
				afterlayout: RequestViewPanelAfterlayout
			}
		});
		
		me.ExpertisePanel = Ext.create('Ext.panel.Panel',{
			_EvnXml_id: null,
			border: true,
			header: false,
			onExpand: Ext.emptyFn,
			//title: '<h1>Заключение эксперта<\h1>',
			hidden: true,
			//height: 'true',
			height: 'auto',
			cls: 'descPanel',
//			minSize: 75,
//			maxSize: 250,
			cmargins: '5 0 0 0',
			tbar: {
				xtype: 'toolbar',
				items: [],
				hidden: true
			},
			items: [
				{
					xtype :'panel',
					itemId:'form',
					tbar: {
						xtype: 'toolbar',
						items: [],
						hidden: true
					}
				},
				{
					xtype: 'panel',
					tbar: {
						xtype: 'toolbar',
						items: [],
						hidden: true
					},
					heigth: '100%',
					width: '700px',
					cls: 'XMLTemplatePanel',
					itemId:'expertise'
				}
			]
		});

		// @TODO: Вынести атрибуты в создание темплейта
		me.RequestTemplate.parent = this.RequestViewPanel;
		me.RequestTemplate.callback = function() {
			me.updateCurrentRequest();
		}
		
		
//		var searchListCombobox = Ext.create('swFindBSMEDCombo', {
//			name: 'searchList',
//			forceSelection: true,
//			hideTrigger: true,
//			autoFilter: false,
//			flex: 1,
//			forceSelection: false,
//			triggerFind: true,
//			store: null,
//			onTrigger3Click: function(e) {
//				this.clearValue();
//				this.clearStoreFilter();
//				this.focus();
//			},
//			clearStoreFilter: function(){
//				this.RequestListDataviewStore.clearFilter();
//				this.RequestListPanel.down('pagingtoolbar').show();
//			}.bind(this),
//			onTrigger2Click: function(e) {
//				this.searchFunction(this, e);
//			},
//			searchFunction: function(c,e){
//				this.RequestListDataviewStore.load({
//					params:{limit: 1000}
//				});
//				this.RequestListDataviewStore.clearFilter();
//				this.RequestListDataviewStore.filter('Person_Fio', new RegExp(c.getRawValue(), "i"));
//				this.RequestListPanel.down('pagingtoolbar').hide();
//			}.bind(this),
//			listeners: {
//				keydown: function(c,e){
//					if(e.getKey()==13){
//						c.searchFunction(c,e);
//					}
//				}.bind(this),
//				change: function(c,n){
//					if(n==null||n==''){
//						this.RequestListDataviewStore.clearFilter();
//						this.RequestListPanel.down('pagingtoolbar').show();
//					}
//				}.bind(this)
//			}
//		});
		
		this.SearchForm = Ext.create('sw.BaseForm',{
			cls: 'mainFormNeptune',
			autoScroll: true,
			id: this.id+'_SearchForm',
			width: '100%',
			height: 'auto',
			layout: {
				padding: '0 0 0 0', // [top, right, bottom, left]
				align: 'stretch',
				type: 'vbox'
			},
			items: [
				{
					xtype: 'triggerfield',
					id: this.id+'_EvnForensic_Num_SearchField',
					fieldLabel: 'Номер экспертизы',
					labelAlign: 'top',
					trigger1Cls:'x-form-clear-trigger', 
					name: 'EvnForensic_Num',
					maskRe: /[0-9.]/,
					onTrigger1Click : function() 
					{ 
						this.reset();
						this.focus();
						//По какой-то причине change не вызывается, если возвращаешь 
						//заявку из готовых и очищаешь поле по тригеру.
						//Поэтому будем обновлять "насильно"
						me.loadRequestViewStore();
					}, 
					flex: 1,
					enableKeyEvents : true,
					listeners: {
						change: function(field, nV,oV, eopts) {
							Ext.Ajax.abortAll();
							me.loadRequestViewStore()
							
						}
					}
				},	{
					labelAlign: 'top',
					id: this.id+'_MedPersonal_eid_SearchField',
					name: 'MedPersonal_eid',
					fieldLabel: 'Эксперт',
					xtype: 'swMedPersonalExpertsCombo',
					listeners: {
						change: function(field, nV,oV, eopts) {
							Ext.Ajax.abortAll();
							me.loadRequestViewStore()
						}
					}
				} , {
					xtype: 'checkboxfield',
					boxLabel  : 'Только собственные заявки',
					name      : 'own',
					listeners: {
						'change':function(cb,nV,oV) {
							Ext.Ajax.abortAll();
							me.loadRequestViewStore();
						}
					}
				}
			]
		});
	
		this.RequestListPanel = Ext.create('Ext.panel.Panel',{
			region: 'west',
			collapsible: true,
			animCollapse: false,
			collapseFirst :false,
//			hideCollapseTool :true,
			collapseMode: 'mini',
			split: true,
			width: '30%',
			layout: {
				type: 'vbox'
			},
			items: [
				this.RequestListDataview
			],
			dockedItems: [
				{
					xtype: 'toolbar',
					dock: 'top',
					layout: 'hbox',
					items: [	
						/*searchListCombobox*/
						this.SearchForm
					]
				},
				{
					xtype: 'pagingtoolbar',
					store: this.RequestListDataviewStore,
					cls: 'paginator',
					dock: 'bottom',
					beforePageText : 'Стр.'
				}
			]
							
		});
		
		
		this.JournalTreeStore = Ext.create('Ext.data.TreeStore',{
			idProperty: this._JournalTreeStoreIdProperty,
			fields: [
				{name: 'Journal_Text', type: 'string'},
				{name: 'Journal_Type', type: 'string'},
				{name: 'type', type: 'string'},
				{name: 'loadStoreParams'},

				{name: 'type', type: 'string'},
				{name: 'leaf'}

			],
			root:{
				leaf: false,
				expanded: true,
				children: this.JournalTreeStoreChildren
			}
		});

		this.JournalTreePanel = Ext.create('Ext.tree.Panel', {
			id: this.id+'_JournalTreePanel',
			displayField: 'Journal_Text',
			width: '100%',
			height: 'auto',
			store: this.JournalTreeStore,
			border: false,
			rootVisible: false,
			title: 'Журналы',
			listeners: {
//				'itemclick': function( tree, record, item, index, e, eOpts ) {
				'select': function( tree, record, index, eOpts ){
					console.log( tree, record, index, eOpts );
					var jt = (record.data && record.data.loadStoreParams && record.data.loadStoreParams.params && record.data.loadStoreParams.params.JournalType)?record.data.loadStoreParams.params.JournalType:null;
					
					var tabpanel = me.centerPanel.items.findBy(function(el){
						return (typeof el.getXType == 'function') && (el.getXType() == 'tabpanel');
					});
					
					var extraParams = (record.data && record.data.loadStoreParams && record.data.loadStoreParams.params)? record.data.loadStoreParams.params : {};
									
					if (extraParams) {
						for (key in extraParams) {
							if (extraParams.hasOwnProperty(key)) {
								me.archiveGrids[jt].getStore().getProxy().setExtraParam(key,extraParams[key]);
							}
						}
					}
					
					if (record.data) {
						switch (record.data.type) {
							case 'archive':
								this.archivePanel.show();
								for (var i=0; i<me.archivePanelItems.length; i++) {
									me.archivePanelItems[i].hide();
								}
								
								this.loadRequestViewStore(record.data.loadStoreParams);
								
								tabpanel.setActiveTab('Archived');
								me.archiveGrids[jt].show();
//								me.archiveGrids[jt].getStore().load();
								me.archiveGrids[jt].datePickerRange.currentDay();
								me.archiveGrids[jt].datePickerRange.setVisible(true);
								this.RequestPanel.hide();
								break;
								
							case 'current':
								if (typeof record.data.loadStoreParams != 'object') {
									return false;
								}
								
								this.loadRequestViewStore(record.data.loadStoreParams);
								
								if (tabpanel.getActiveTab().itemId == 'Archived') {
									me.createRequestButton.setDisabled(true);
									me.archiveGrids[jt].getStore().load();
								} else {
									this.archivePanel.hide();
									this.RequestPanel.show();
								}
								
								break;
							default:
								break;
						}

					}
				}.bind(this)
			}
		});
		
		
		// Панель с тривью журналов и кнопкой создания заявки
		
		this.westPanel = Ext.create('Ext.panel.Panel',{
			region: 'west',
			collapsible: true,
			collapseFirst :false,
			animCollapse: false,
			collapseMode: 'mini',
//			hideCollapseTool :true,
			split: true,
			width: '20%',
			layout: {
				type: 'vbox',
				align: 'center'
			},
			items: [
				this.JournalTreePanel,
				this.createRequestButton,
				this.printRequestButton
			]
		});
		
		this.RequestPanel = Ext.create('Ext.panel.Panel',{
			region: 'center',
			xtype: 'panel',
			cls: 'contentPanel',
			layout: {
				type: 'border'
			},
			defaults: {
				border: false,
				header: false
			},
			items: [
				this.RequestListPanel,
				{
					height: '100%',
					autoScroll: true,
					itemId: 'RequestView',
					region: 'center',
					xtype: 'panel',
					cls: 'contentPanel',
					layout: {
						type: 'vbox',
						align:'stretch'
					},
					autoscroll: true,
					defaults: {
						border: false,
						header: false
					},
					items: [
						this.RequestViewPanel,
						this.ExpertisePanel,
						this.ReportPanel
					]
				}
			]
		})
		
		
		
		//Панель для грида с архивными заявками
		this.archivePanel =  Ext.create('Ext.panel.Panel',{
			region: 'center',
			hidden: true,
			xtype: 'panel',
			cls: 'contentPanel archivePanel',
			layout: {
				type: 'fit'
			},
			defaults: {
				border: false,
				header: false
			},
			items: this.archivePanelItems
		});
		
		// Центральная панель, содержащая в себе область отображения списка заявок и область просмотра/редактирования заявки
		this.centerPanel = Ext.create('Ext.panel.Panel',{
			region: 'center',
			xtype: 'panel',
			cls: 'contentPanel',
			layout: {
				type: 'border'
			},
			defaults: {
				border: false,
				header: false
			},
			items: [
				{
					region: 'north',
					xtype: 'tabpanel',
					plaint: true,
					listeners: {
						beforetabchange: function (tabs, newTab, oldTab){
							
							var selection = me.JournalTreePanel.getSelectionModel().getSelection();
							var record = (selection.length)?selection[0]:null;
								
							if (newTab.itemId == 'Archived') {
								
								if (record) {
									
									me.archivePanel.show();
									for (var i=0; i<me.archivePanelItems.length; i++) {
										me.archivePanelItems[i].hide();
									}
									
									var jt = (record.data && record.data.loadStoreParams && record.data.loadStoreParams.params && record.data.loadStoreParams.params.JournalType)?record.data.loadStoreParams.params.JournalType:null;
									
									if (!Ext.isString(jt)) {
										return;
									}
									
									me.archiveGrids[jt].show();
									
									var extraParams = (record.data && record.data.loadStoreParams && record.data.loadStoreParams.params)? record.data.loadStoreParams.params : {};
									
									if (extraParams) {
										for (key in extraParams) {
											if (extraParams.hasOwnProperty(key)) {
												me.archiveGrids[jt].getStore().getProxy().setExtraParam(key,extraParams[key]);
											}
										}
									}

									//me.archiveGrids[jt].getStore().load();
									me.archiveGrids[jt].datePickerRange.currentDay();
									me.archiveGrids[jt].datePickerRange.setVisible(true);
								
									me.RequestPanel.hide();
									if (me.printRequestButton) {
										me.printRequestButton.setDisabled(true);
									}
									me.createRequestButton.setDisabled(true);
								}
							} else {
								me.archivePanel.hide();
								me.RequestPanel.show();
								me.RequestListDataviewStore.getProxy().setExtraParam('EvnStatus_SysNick',newTab.itemId);
								me.RequestListDataviewStore.loadPage(1);
								
								var ForensicSubType_id = (!record.data.loadStoreParams || !record.data.loadStoreParams || !record.data.loadStoreParams.params || !record.data.loadStoreParams.params.ForensicSubType_id)?0:record.data.loadStoreParams.params.ForensicSubType_id;
								me.createRequestButton.setDisabled( !ForensicSubType_id );
							}
							
						}
					},
					// itemId берется из поля EvnStatus.EvnStatus_SysNick
					items: (!me.TabPanelItems)?[]:me.TabPanelItems	
				},
				this.RequestPanel,
				this.archivePanel
				
			]
		});
		
		dockedItems: [],
		
		this.mainPanel = Ext.create('Ext.panel.Panel',{
			layout: {
				type: 'border'
			},
			defaults: {
				border: false,
				header: false
			},
			items: [
				this.westPanel,
				this.centerPanel
			]
		});
		
		this.RequestListPanel.splitter.setWidth(me.splitterWidth);
		this.westPanel.splitter.setWidth(me.splitterWidth);
		
		Ext.applyIf(me,{
			items: [
				this.mainPanel
			]
		});
		me.callParent(arguments);
	},
	
	listeners: {
		show: function(wnd,eOpts) {
			// Загружаем дефолтный журнал
			wnd.JournalTreePanel.getRootNode().findChild('expanded',true, true).getChildAt(0)
			var parent_record = wnd.JournalTreePanel.getRootNode().getChildAt(0);
			var record = parent_record && parent_record.getChildAt(0);
			
			// Инициализация параметра статуса заявок для первой загрузки стора
			this.RequestListDataviewStore.getProxy().setExtraParam('EvnStatus_SysNick',this.TabPanelItems[0].itemId);
			wnd.JournalTreePanel.getSelectionModel().select(record);
			
			//Устанавливаем таймаут на загрузку списка заявок
			clearInterval(wnd.updateRequestListTimeout);
			wnd.updateRequestListTimeout = setInterval(function() {
				wnd.loadRequestViewStore();
			},30000);
			
		}, 
		hide: function(wnd) {
			clearInterval(wnd.updateRequestListTimeout);
		}
	}
});
