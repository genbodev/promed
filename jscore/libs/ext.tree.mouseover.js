NodeMouseoverPlugin = Ext.extend(Object, {
    init: function(tree) {
        if (!tree.rendered) {
            tree.on('render', function() {this.init(tree)}, this);
            return;
        }
        this.tree = tree;
        tree.body.on('mouseover', this.onTreeMouseover, this, {delegate: 'a.x-tree-node-anchor'});
    },

    onTreeMouseover: function(e, t) {
        var nodeEl = Ext.fly(t).up('div.x-tree-node-el');
        if (nodeEl) {
            var nodeId = nodeEl.getAttributeNS('ext', 'tree-node-id');
            if (nodeId) {
                this.tree.fireEvent('mouseover', this.tree.getNodeById(nodeId), e);
            }
        }
    }
});
