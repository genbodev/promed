/**
* swLpuSearchTreeWindow - окно поиска ЛПУ в дереве по наименованию
*
* @package      ONMK
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       gilmiyarov
* @version      01042019
*/

sw.Promed.swLpuSearchTreeWindow = Ext.extend(sw.Promed.BaseForm, {
	windowReady: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	doReset: function(notLoad) {

		this.Tree.getSelectionModel().selNode = false;		

		var Mask = new Ext.LoadMask(Ext.get('LpuSearchTreeWindow'), { msg: LOAD_WAIT });
		Mask.show();
		var root = this.Tree.getRootNode();
		this.Tree.getLoader().load(root, function() {
			Mask.hide();
		});
		root.expand();

	},
	draggable: true,
	height: 510,
	id: 'LpuSearchTreeWindow',
	initComponent: function() {
		var wndTreePanel = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.onOkButtonClick();
				},
				iconCls: 'ok16',
				text: langs('Выбрать')
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
			items: [
			this.Tree = new Ext.tree.TreePanel({
				region: 'center',
				selectionDepth: wndTreePanel.selectionDepth,
				id: 'DSW_LpuSearchTree',
				autoScroll: true,
				loaded: false,
				border: false,
				rootVisible: false,
				lastSelectedId: 0,
				root: {
					nodeType: 'async',
					text: langs('Классы МО'),
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
							//if (this.windowReady === false) {
							//	return false;
							//}
						}.createDelegate(this)
					},
					dataUrl:'/?c=Lpu&m=getLpuTreeSearchData'
				}),
				selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
				listeners: {
					'click': function(node)
					{
						// Если нельзя выбирать - то не выделяем, а раскрываем или закрываем.
						// Оставил контроль проверки на лист, на случай некорректных входных параметров.
						
						/*
						if (node.attributes.DiagLevel_id < this.ownerCt.selectionDepth && !node.attributes.leaf) {
							node.toggle();
							return false;
						}
						*/
					}
				}
			})
			]
		});
		this.Tree.on('dblclick', function(node)
		{
			//if(node.attributes.Lpu_id && node.attributes.leaf)
			//{
				this.ownerCt.onOkButtonClick();
			//}
		});
		sw.Promed.swLpuSearchTreeWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.doReset(true);
			this.onHide();
		}
	},
	modal: true,
	onLpuSelect: Ext.emptyFn,
	onOkButtonClick: function() {

		if (!this.Tree.getSelectionModel().selNode)
		{
			sw.swMsg.alert(langs('Ошибка'), langs('Вы ничего не выбрали.'));
			return false;
		}

		var selected_record = this.findById('DSW_LpuSearchTree').getSelectionModel().selNode;

		this.onLpuSelect({
			Lpu_id: selected_record.attributes.Lpu_id,
			Lpu_Nick: selected_record.attributes.Lpu_Nick,
			Lpu_Name: selected_record.attributes.Lpu_Name
		});
	},	
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swLpuSearchTreeWindow.superclass.show.apply(this, arguments);
				
		this.onLpuSelect = Ext.emptyFn;		
		
		this.windowReady = true;
		
		if ( !arguments[0] )
		{
			this.hide();
			return false;
		}
		if (arguments[0].onSelect)
		{
			this.onLpuSelect = arguments[0].onSelect;
		}		
		
	},
	title: "Выбор РСЦ/ПСО/МО госпитализации",
	width: 800
});
