/**
* swDiagSearchTreeWindow - окно поиска диагноза в дереве по наименованию или коду
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Andrey Bashkov aka Anderstand (a.bashkov@swan.perm.ru)
* @version      0.001-24.05.2016
*/

sw.Promed.swDiagSearchTreeWindow = Ext.extend(sw.Promed.BaseForm, {
	windowReady: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	MKB: null,
	FilterDiagCode: null,
	filterDate: '',
	//>>>> Унаследовано
	MorbusType_SysNick: '',//Тип заболевания/нозологии
	PersonRegisterType_SysNick: '',
	withGroups: '',
	baseFilterFn: '',
	//<<<< Унаследовано
	// Переменные с данными, которые использовались момент нажатия кнопки "поиск".
	// Данные каждый раз передаются на сервер при подгрузке новых поддеревьев,
	// поэтому нужны данные, которые искали, а не те, что сейчас на форме
	searchDiagCode: '',
	searchDiagName: '',
	doReset: function(notLoad) {
		notLoad = notLoad!=undefined;
		this.Tree.getSelectionModel().selNode = false;
		this.findById('DiagSearchTreeForm').getForm().reset();
		this.findById('DSW_Diag_Name').focus(true, 250);
		this.findById('DSW_Diag_Name').getStore().removeAll();
		this.findById('DSW_Diag_Code').getStore().removeAll();
		this.searchDiagCode = '';
		this.searchDiagName = '';

		if (notLoad === false) {
			var Mask = new Ext.LoadMask(Ext.get('DiagSearchTreeWindow'), { msg: LOAD_WAIT });
			Mask.show();
			var root = this.Tree.getRootNode();
			this.Tree.getLoader().load(root, function() {
				Mask.hide();
			});
			root.expand();
		}
	},
	doSearch: function() {
		var win = this;
		var Mask = new Ext.LoadMask(Ext.get('DiagSearchTreeWindow'), { msg: SEARCH_WAIT });
		// getValue иногда не успевает. Если нажать поиск сразу после ввода, форма думает, что в поле ничего нет
		var Diag_Code = this.findById('DSW_Diag_Code').getRawValue();
		var Diag_Name = this.findById('DSW_Diag_Name').getRawValue();

		if ( !Diag_Code && !Diag_Name )
		{
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { win.findById('DSW_Diag_Name').focus(true, 250); });
			return false;
		}
		
		if ( !Diag_Name && Diag_Code.length < 2 ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_ne_menee_dvuh_simvolov'], function() { win.findById('DSW_Diag_Code').focus(true, 250); });
			return false;
		}

		if ( !Diag_Code && Diag_Name.length < 2 ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_ne_menee_dvuh_simvolov'], function() { win.findById('DSW_Diag_Name').focus(true, 250); });
			return false;
		}
		if (win.FilterDiagCode) {
			this.searchDiagCode = Diag_Code;
		} else {
			this.searchDiagCode = Diag_Code;
		}

		this.searchDiagName = Diag_Name;

		Mask.show();
		var root = this.Tree.getRootNode();
		this.Tree.getLoader().load(root, function() {
			Mask.hide();
		});
		root.expand();
	},
	draggable: true,
	height: 510,
	id: 'DiagSearchTreeWindow',
	initComponent: function() {
		var wndTreePanel = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.onOkButtonClick();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'DSW_Diag_Name',
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'DiagSearchTreeForm',
				labelAlign: 'right',
				region: 'north',
				style: 'padding: 5px;',
				items: [
					{
						border: false,
						layout: 'column',
						items: [
							{
								border: false,
								layout: 'form',
								labelWidth: 40,
								items: [
									{
										enableKeyEvents: true,
										checkAccessRights: true,
										fieldLabel: lang['kod'],
										id: 'DSW_Diag_Code',
										listeners: {
											'keydown': function(inp, e) {
												if (e.getKey() == Ext.EventObject.TAB && e.shiftKey === true)
												{
													e.stopEvent();
													inp.ownerCt.ownerCt.buttons[5].focus();
												}
												if (e.getKey() == Ext.EventObject.ENTER)
												{
													e.stopEvent();
												}
											},
											'focus': function() {
												if(wndTreePanel.FilterDiagCode)
													this.FilterDiagCode = wndTreePanel.FilterDiagCode
												if (this.getValue().length >= this.minChars && this.getStore().getCount() > 0) {
													this.expand();
												}
											},
											'select': function(combo, record, value) {
												var resp = this.onDiagSelect({
													Diag_Code: $('<p></p>').html(record.get('Diag_Code')).text(),
													Diag_id: record.get('Diag_id'),
													Diag_Name: record.get('Diag_Name'),
													DiagLevel_id: record.get('DiagLevel_id'),
													DiagFinance_IsOms: record.get('DiagFinance_IsOms'),
													PersonRegisterType_List: record.get('PersonRegisterType_List'),
													MorbusType_List: record.get('MorbusType_List')
												});
												if(!resp){
													combo.setValue('');
												}
											}.createDelegate(this)
										},
										minChars: 2,
										hiddenName: 'Diag_Code',
										width: 100,
										xtype: 'swdiagautocompletecombo'
									}
								]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 100,
								items: [
									{
										enableKeyEvents: true,
										checkAccessRights: true,
										fieldLabel: lang['naimenovanie'],
										id: 'DSW_Diag_Name',
										fieldType: 'name',
										listeners: {
											'keydown': function(inp, e) {
												if (e.getKey() == Ext.EventObject.TAB && e.shiftKey === true)
												{
													e.stopEvent();
													inp.ownerCt.ownerCt.buttons[5].focus();
												}
												if (this.getValue().length >= this.minChars && this.getStore().getCount() > 0) {
													this.expand();
												}
											},
											'focus': function() {
												if(wndTreePanel.FilterDiagCode)
													this.FilterDiagCode = wndTreePanel.FilterDiagCode
												if (this.getValue().length >= this.minChars && this.getStore().getCount() > 0) {
													this.expand();
												}
											},
											'select': function(combo, record, value) {
												var resp = this.onDiagSelect({
													Diag_Code: record.get('Diag_Code'),
													Diag_id: record.get('Diag_id'),
													DiagLevel_id: record.get('DiagLevel_id'),
													Diag_Name: $('<p></p>').html(record.get('Diag_Name')).text(),
													DiagFinance_IsOms: record.get('DiagFinance_IsOms'),
													PersonRegisterType_List: record.get('PersonRegisterType_List'),
													MorbusType_List: record.get('MorbusType_List')
												});
												if(!resp){
													combo.setValue('');
												}
											}.createDelegate(this)
										},
										minChars: 2,
										hiddenName: 'Diag_Name',
										width: 350,
										xtype: 'swdiagautocompletecombo'
									}
								]
							}, {
								border: false,
								layout: 'form',
								style: 'margin-left: 20px;',
								items: [
									{
										xtype: 'button',
										iconCls: 'search16',
										id: 'MSW_SearchButton',
										text: BTN_FRMSEARCH,
										handler: function () {
											this.doSearch();
										}.createDelegate(this)
									}
								]
							}, {
								border: false,
								layout: 'form',
								items: [
									{
										xtype: 'button',
										iconCls: 'resetsearch16',
										id: 'MSW_ResetButton',
										text: BTN_FRMRESET,
										handler: function () {
											this.doReset();
										}.createDelegate(this)
									}
								]
							}
						]
					}
				]/*,
				keys: [{
					fn: function(e) {
						this.doSearch();
					}.createDelegate(this),
					key: Ext.EventObject.ENTER,
					stopEvent: true
				}]*/
			}),
			this.Tree = new Ext.tree.TreePanel({
				region: 'center',
				selectionDepth: wndTreePanel.selectionDepth,
				id: 'DSW_DiagSearchTree',
				autoScroll: true,
				loaded: false,
				border: false,
				rootVisible: false,
				lastSelectedId: 0,
				root: {
					nodeType: 'async',
					text: lang['klassyi_diagnozov'],
					id: 'root',
					expanded: false
				},
				loader: new Ext.tree.TreeLoader({
					listeners:
					{
						load: function(loader, node, response)
						{
							if (typeof wndTreePanel.baseFilterFn == 'function') {
								var treeFilter = new Ext.tree.TreeFilter(wndTreePanel.Tree);
								if(wndTreePanel.MorbusType_SysNick == 'tub'){
									treeFilter.filterBy(function(record, id) {
										return wndTreePanel.baseFilterFn.call(treeFilter, record, id);
									});
								} else {
									treeFilter.filterBy(function(record, id) {
										return record.attributes.DiagLevel_id < 3 || wndTreePanel.baseFilterFn.call(treeFilter, record, id);
									});
								}
							}
						},
						beforeload: function (tl, node)
						{
							// Отменим загрузку, если не все параметры переданы
							if (this.windowReady === false) {
								return false;
							}
						}.createDelegate(this)
					},
					// Переопределим функцию передачи параметров, т.к. нам нужны дополнительные
					getParams: function(node){
						var buf = [], bp = this.baseParams;
						for(var key in bp){
							if(typeof bp[key] != "function"){
								buf.push(encodeURIComponent(key), "=", encodeURIComponent(bp[key]), "&");
							}
						}

						// Передадим на сервер параметры поиска, уровень и id node
						var DiagLevel_id = 0;
                        switch (this.registryType) {
                            case 'BSKRegistry':
                                DiagLevel_id = 1;
                                node.id = (node.id == 'root') ? 9 : node.id; // Заменяем корневое значение на id = 9
                                break;
                        }
						if (node.attributes.DiagLevel_id !== undefined) {
							DiagLevel_id = node.attributes.DiagLevel_id;
						}

						var filterDiagCode = wndTreePanel.FilterDiagCode;//для уточненного диагноза
						if(filterDiagCode){
							buf.push("Diag_Code=", encodeURIComponent(filterDiagCode), "&");
						}else{
							buf.push("Diag_Code=", encodeURIComponent(this.searchDiagCode), "&");
						}

						if ( !Ext.isEmpty(wndTreePanel.filterDate) ) {
							buf.push("Diag_Date=", encodeURIComponent(wndTreePanel.filterDate), "&");
						}

						buf.push("Diag_Name=", encodeURIComponent(this.searchDiagName), "&");
						buf.push("DiagLevel_id=", encodeURIComponent(DiagLevel_id), "&");
						if(!(this.MorbusType_SysNick == 'tub' && typeof this.baseFilterFn == 'function')){
							buf.push("MorbusType_SysNick=", encodeURIComponent(this.MorbusType_SysNick), "&");
						}
						buf.push("PersonRegisterType_SysNick=", encodeURIComponent(this.PersonRegisterType_SysNick), "&");
						buf.push("node=", encodeURIComponent(node.id));
						return buf.join("");
					}.createDelegate(this),
					dataUrl:'/?c=Diag&m=getDiagTreeSearchData'
				}),
				selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
				listeners: {
					'click': function(node)
					{
						// Если нельзя выбирать - то не выделяем, а раскрываем или закрываем.
						// Оставил контроль проверки на лист, на случай некорректных входных параметров.
						if (node.attributes.DiagLevel_id < this.ownerCt.selectionDepth && !node.attributes.leaf) {
							node.toggle();
							return false;
						}
					}
				}
			})
			]
		});
		this.Tree.on('dblclick', function(node)
		{
			if(node.attributes.Diag_id && node.attributes.leaf)
			{
				this.ownerCt.onOkButtonClick();
			}
		});
		sw.Promed.swDiagSearchTreeWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.doReset(true);
			this.onHide();
		}
	},
	modal: true,
	onDiagSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		if (!this.Tree.getSelectionModel().selNode)
		{
			sw.swMsg.alert(lang['oshibka'], lang['vyi_nichego_ne_vyibrali']);
			return false;
		}

		var selected_record = this.findById('DSW_DiagSearchTree').getSelectionModel().selNode;

		this.onDiagSelect({
			DiagLevel_id: selected_record.attributes.DiagLevel_id,
			Diag_Code: selected_record.attributes.Diag_Code,
			Diag_id: selected_record.attributes.Diag_id,
			Diag_Name: selected_record.attributes.Diag_Name,
			DiagFinance_IsOms: selected_record.attributes.DiagFinance_IsOms,
			PersonRegisterType_List: selected_record.attributes.PersonRegisterType_List,
			MorbusType_List: selected_record.attributes.MorbusType_List
		});
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swDiagSearchTreeWindow.superclass.show.apply(this, arguments);
		this.onDiagSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.selectionDepth = 4;
		this.MorbusType_SysNick = '';
		this.PersonRegisterType_SysNick = '';
		this.filterDate = getGlobalOptions().date;
		this.withGroups = '';
		this.baseParams = {};

		var Diag_Code = this.findById('DSW_Diag_Code');
		var Diag_Name = this.findById('DSW_Diag_Name');
		var value = '';

		Diag_Code.MorbusType_SysNick = '';
		Diag_Code.PersonRegisterType_SysNick = '';
		Diag_Code.withGroups = '';
		Diag_Name.MorbusType_SysNick = '';
		Diag_Name.PersonRegisterType_SysNick = '';
		Diag_Name.withGroups = '';
		
		if ( !arguments[0] )
		{
			this.hide();
			return false;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}

		if (arguments[0].onSelect)
		{
			this.onDiagSelect = arguments[0].onSelect;
		}

		if (arguments[0].selectionDepth)
		{
			this.selectionDepth = arguments[0].selectionDepth;
		}

		if (arguments[0].MorbusType_SysNick) {
			value = arguments[0].MorbusType_SysNick;
			this.MorbusType_SysNick = value;
			Diag_Code.MorbusType_SysNick = value;
			Diag_Name.MorbusType_SysNick = value;
		}

		if (arguments[0].PersonRegisterType_SysNick) {
			value = arguments[0].PersonRegisterType_SysNick;
			this.PersonRegisterType_SysNick = value;
			Diag_Code.PersonRegisterType_SysNick = value;
			Diag_Name.PersonRegisterType_SysNick = value;
		}

		if (arguments[0].selectionDepth) {
			this.selectionDepth = arguments[0].selectionDepth;
		}

		if (arguments[0].baseFilterFn) {
			this.baseFilterFn = arguments[0].baseFilterFn;
			Diag_Code.setBaseFilter(this.baseFilterFn);
			Diag_Name.setBaseFilter(this.baseFilterFn);
		}
		else
			this.baseFilterFn = arguments[0].baseFilterFn;

		if (arguments[0].registryType) {
			value = arguments[0].registryType;
			this.registryType = value;
			Diag_Code.registryType = value;
			Diag_Name.registryType = value;

			this.baseParams.registryType = arguments[0].registryType;
		}

		if (arguments[0].withGroups) {
			value = arguments[0].withGroups;
			this.withGroups = value;
			Diag_Code.withGroups = value;
			Diag_Name.withGroups = value;
		}
		if (arguments[0].FilterDiagCode) {
			this.FilterDiagCode = arguments[0].FilterDiagCode;
		}
		if (arguments[0].filterDate) {
			this.filterDate = arguments[0].filterDate;
		}

		this.windowReady = true;
		this.doReset();
	},
	title: WND_SEARCH_DIAG,
	width: 800
});
