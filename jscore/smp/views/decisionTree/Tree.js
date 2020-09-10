/**
 * Структура дерева решений
 */
Ext6.define('smp.views.decisionTree.Tree', {
	extend: 'Ext6.tree.Panel',
	alias: 'widget.decisionTree.tree',
	displayField: 'AmbulanceDecigionTree_Text',
	requires: [
		'smp.stores.DecisionTree'
	],
	initComponent: function () {

		var extraParams = {};

		if(this.up('window').params){
			extraParams = this.up('window').params;
			extraParams.concreteTree = true;
		}


		Ext6.applyIf(this, {
			store: Ext6.create('smp.stores.DecisionTree',{
				proxy: {
					extraParams
				}
			})
		});
		this.callParent(arguments);
	},
	
	/**
	 * Возвращает выбранный элемент дерева
	 * 
	 * @returns object или null если ничего не выбрано
	 */
	getSelectedTreeNode: function(){
		var selection = this.getSelectionModel();
		if (!selection.hasSelection()) {
			return null;
		}
		
		return selection.getSelection()[0];
	},
	
	
	getLastTreeNodeId: function(){
		var last_id = 0;
	
		this.getRootNode().cascadeBy(function(node){
			last_id = Math.max(last_id, node.get('AmbulanceDecigionTree_nodeid'));
		});
		
		return last_id;
	},
	
	/**
	 * Собирает дерево в одномерного массива, т.е. без вложенных потомков
	 * 
	 * @returns array
	 */
	collectData: function(){
		var data = [];
		
		this.getRootNode().cascadeBy(function(node){
			data.push(node.data);
		});
		
		return data;
	}
	
});