/*function TreeBeforeLoad(TreeLoader, node)
{
TreeLoader.baseParams.level = node.getDepth();
TreeLoader.baseParams.level_two = 'All';
}
*/
sw.Promed.LpuStructure = function(config) 
{
	Ext.apply(this, config);
	
	this.loader.on("beforeload", function(TreeLoader, node) {this.loader.onBeforeLoad(TreeLoader, node);}, this);
	sw.Promed.LpuStructure.superclass.constructor.call(this);
	//tree.on('click', function(node, e) {LpuStructureTreeClick(node, e)} );
	//this.getRootNode().expand();
};

Ext.extend(sw.Promed.LpuStructure, Ext.tree.TreePanel, 
{
	title: '',
	id: 'lpustructureframe',
	region: 'west',
	animate:false,
	autoLoad:false,
	width: 300,
	height: '100%',
	enableDD: false,
	autoScroll: true,
	border: true,
	rootVisible: false,
	split: true,
	minSize: 300,
	maxSize: 350,
	root: 
	{
		nodeType: 'async',
		text:lang['mo'],
		id:'root',
		draggable:false,
		expandable:true
	},
	
	contextMenu: new Ext.menu.Menu(
	{
	items: 
	[{
		id: 'lpustructure-refresh',
		text: lang['otkryit_pereotkryit'],
		icon: 'img/icons/refresh16.png',
		iconCls : 'x-btn-text'
	}],
	listeners: 
	{
		itemclick: function(item) 
		{
			switch (item.id) 
			{
				case 'lpustructure-refresh':
					var n = item.parentMenu.contextNode;
					if (n.isExpanded())
					{
						n.getOwnerTree().loader.load(n);
					}
					//n.select();
					n.expand();
					/*
					if (n.parentNode) 
					{
						n.expand();
					}
					*/
				break;
			}
		}
	}
	}),
	listeners: 
	{
		contextmenu: function(node, e) 
		{
			//node.select();
			if (!node.isLeaf())
			{
				var c = node.getOwnerTree().contextMenu;
				c.contextNode = node;
				c.showAt(e.getXY());
			}
		}
	},
	loader: new Ext.tree.TreeLoader(
	{
		onBeforeLoad: function(TreeLoader, node) 
		{
			TreeLoader.baseParams.level = node.getDepth();
			TreeLoader.baseParams.level_two = 'All';
		},
		dataUrl: C_LPUSTRUCTURE_LOAD
		//baseParams: {method:'GetLpuStructure'}
		//beforeload: function(treeLoader, node) {this.baseParams.method='GetLpuStructure'; this.baseParams.level=2; }
	}),
	initComponent: function()
	{
		sw.Promed.LpuStructure.superclass.initComponent.apply(this, arguments);
	}
});