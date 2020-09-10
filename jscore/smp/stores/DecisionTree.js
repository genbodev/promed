/**
 * Хранилище дерева решений
 */
Ext6.define('smp.stores.DecisionTree', {
	extend: 'Ext6.data.TreeStore',
	alias: 'store.smp.DecisionTree',
	storeId: 'DecisionTree',
	requires: [
		'smp.models.DecisionTree'
	],
	model: 'smp.models.DecisionTree',
	root: {
		leaf: false,
		expanded: true
	},
	sorters: [{
		property: 'AmbulanceDecigionTree_Text',
		direction: 'ACS'
	}],
	proxy: {
		type: 'ajax',
		url: '/?c=CmpCallCard&m=getDecigionTree',
		idParam: 'AmbulanceDecigionTree_id', // работает аналогично idProperty (но в модели ругается). сообщает прокси-серверу, какое поле и имя использовать при работе с идентификаторами.
		reader: {
			type: 'json'
		},
		actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined
	}
});

/*
Перенес из функционала 4-ки
Ext.create('Ext.data.TreeStore', {
	autoLoad: false,
	root: {
		leaf: false,
		expanded: true
	},
	listeners: {
		'load': function (store, node, records, successful, eOpts) {
			this.TreePanel.setRootNode(this.TreePanel.getRootNode().childNodes[0]);
			this.TreePanel.getSelectionModel().select(this.TreePanel.getRootNode());

		}.bind(this),
		'beforeappend': function (store, node, eOpts) {
			if (node && node['data'] && node['data']['AmbulanceDecigionTree_Type']) {
				node['data']['iconCls'] = 'decigiontreeeditwindow-tree-icon-' + ((node['data']['AmbulanceDecigionTree_Type'].toString() === '1') ? 'question' : 'answer');
			}
		}
	}
})
*/