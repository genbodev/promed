/**
* swTreeSelection - модель выделения для дерева, поддерживающая обработку клавиш по keydown
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      01.09.2010
*/

/**
 * @class Ext.tree.KeyHandleTreeSelectionModel
 * @extends Ext.util.Observable
 * Модель выделения для дерева, поддерживающая обработку клавиш по keydown
 */
Ext.tree.KeyHandleTreeSelectionModel = function(config){
   this.selNode = null;
   
   this.addEvents(
       /**
        * @event selectionchange
        * Fires when the selected node changes
        * @param {KeyHandleTreeSelectionModel} this
        * @param {TreeNode} node the new selection
        */
       "selectionchange",

       /**
        * @event beforeselect
        * Fires before the selected node changes, return false to cancel the change
        * @param {KeyHandleTreeSelectionModel} this
        * @param {TreeNode} node the new selection
        * @param {TreeNode} node the old selection
        */
       "beforeselect",

       /**
		* @event keydown
		* Keydown input field event. This event only fires if enableKeyEvents is set to true.
		* @param {KeyHandleTreeSelectionModel} this
		* @param {Ext.EventObject} e
		*/ 
       "keydown"
   );

    Ext.apply(this, config);
    Ext.tree.KeyHandleTreeSelectionModel.superclass.constructor.call(this);
};

Ext.extend(Ext.tree.KeyHandleTreeSelectionModel, Ext.util.Observable, {
    init : function(tree){
        this.tree = tree;
        tree.getTreeEl().on("keydown", this.onKeyDown, this);
        tree.on("click", this.onNodeClick, this);
    },
    
    onNodeClick : function(node, e){
        this.select(node);
    },
    
    /**
     * Select a node.
     * @param {TreeNode} node The node to select
     * @return {TreeNode} The selected node
     */
    select : function(node){
        var last = this.selNode;
        if(node == last){
            if (node && node.ui) {
                node.ui.onSelectedChange(true);
            }
        }else if(this.fireEvent('beforeselect', this, node, last) !== false){
            if(last){
                last.ui.onSelectedChange(false);
            }
            this.selNode = node;
            if (node && node.ui) {
                node.ui.onSelectedChange(true);
            }
            this.fireEvent("selectionchange", this, node, last);
        }
        return node;
    },
    
    /**
     * Deselect a node.
     * @param {TreeNode} node The node to unselect
     */
    unselect : function(node){
        if(this.selNode == node){
            this.clearSelections();
        }    
    },
    
    /**
     * Clear all selections
     */
    clearSelections : function(){
        var n = this.selNode;
        if(n){
            n.ui.onSelectedChange(false);
            this.selNode = null;
            this.fireEvent("selectionchange", this, null);
        }
        return n;
    },
    
    /**
     * Get the selected node
     * @return {TreeNode} The selected node
     */
    getSelectedNode : function(){
        return this.selNode;    
    },
    
    /**
     * Returns true if the node is selected
     * @param {TreeNode} node The node to check
     * @return {Boolean}
     */
    isSelected : function(node){
        return this.selNode == node;  
    },

    /**
     * Selects the node above the selected node in the tree, intelligently walking the nodes
     * @return TreeNode The new selection
     */
    selectPrevious : function(){
        var s = this.selNode || this.lastSelNode;
        if(!s){
            return null;
        }
        var ps = s.previousSibling;
        if(ps){
            if(!ps.isExpanded() || ps.childNodes.length < 1){
                return this.select(ps);
            } else{
                var lc = ps.lastChild;
                while(lc && lc.isExpanded() && lc.childNodes.length > 0){
                    lc = lc.lastChild;
                }
                return this.select(lc);
            }
        } else if(s.parentNode && (this.tree.rootVisible || !s.parentNode.isRoot)){
            return this.select(s.parentNode);
        }
        return null;
    },

    /**
     * Selects the node above the selected node in the tree, intelligently walking the nodes
     * @return TreeNode The new selection
     */
    selectNext : function(){
        var s = this.selNode || this.lastSelNode;
        if(!s){
            return null;
        }
        if(s.firstChild && s.isExpanded()){
             return this.select(s.firstChild);
         }else if(s.nextSibling){
             return this.select(s.nextSibling);
         }else if(s.parentNode){
            var newS = null;
            s.parentNode.bubble(function(){
                if(this.nextSibling){
                    newS = this.getOwnerTree().selModel.select(this.nextSibling);
                    return false;
                }
            });
            return newS;
         }
        return null;
    },

    onKeyDown : function(e){
        var s = this.selNode || this.lastSelNode;
        // undesirable, but required
        var sm = this;
        if(!s){
            return;
        }
        var k = e.getKey();
        switch(k){
             case e.DOWN:
                 e.stopEvent();
                 this.selectNext();
             break;
             case e.UP:
                 e.stopEvent();
                 this.selectPrevious();
             break;
             case e.RIGHT:
                 e.preventDefault();
                 if(s.hasChildNodes()){
                     if(!s.isExpanded()){
                         s.expand();
                     }else if(s.firstChild){
                         this.select(s.firstChild, e);
                     }
                 }
             break;
             case e.LEFT:
                 e.preventDefault();
                 if(s.hasChildNodes() && s.isExpanded()){
                     s.collapse();
                 }else if(s.parentNode && (this.tree.rootVisible || s.parentNode != this.tree.getRootNode())){
                     this.select(s.parentNode, e);
                 }
             break;
        };
		this.fireEvent("keydown", this, e);
    }
});