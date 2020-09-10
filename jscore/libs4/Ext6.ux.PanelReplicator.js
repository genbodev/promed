/**
 * Репликация панелей (дублирование) блока компонентов
 */
Ext6.define('Ext6.ux.PanelReplicator', {
    extend: 'Ext6.plugin.Abstract',
    alias: 'plugin.panelreplicator',

    init: function(panel) {

        if (!panel.replicatorId) {
            panel.replicatorId = Ext.id();
        }

        if (!panel.replicatorTarget) {
            panel.replicatorTarget = 'textfield';
        }

        if (!panel.replicatorTargetEvent) {
            panel.replicatorTargetEvent = 'blur';
        }

        panel.on('cloneComponent', this.onCloneComponent, this);

        var replicationActivator = Ext6.ComponentQuery.query(panel.replicatorTarget, panel);

        if (replicationActivator.length) {
            replicationActivator[0].on(panel.replicatorTargetEvent, this.replicate, this);
        }

        log('replicator injected',panel);
    },

    replicate: function(targetEl) {

        var component = targetEl.ownerCt;

        var ownerCt = component.ownerCt,
            replicatorId = component.replicatorId,
            isEmpty = Ext.isEmpty(targetEl.getRawValue()),
            siblings = ownerCt.query('[replicatorId=' + replicatorId + ']'),
            isLastInGroup = siblings[siblings.length - 1] === component;

        if (isEmpty && !isLastInGroup) {
            Ext6.defer(component.destroy, 10, component);
        } else if (!isEmpty && isLastInGroup && siblings.length < 5) {
            this.cloneComponent(component);
        }
    },
    onCloneComponent: function(component, times) {

        log('onCloneComponent', times);

        for (var i = 0; i != times; i++) {
            this.cloneComponent(component);
        }
    },
    cloneComponent: function(component) {

        log('cloneComponent');

        var ownerCt = component.ownerCt,
            clone, idx;

        clone = component.cloneConfig({replicatorId: component.replicatorId});
        idx = ownerCt.items.indexOf(component);
        ownerCt.add(idx + 1, clone);

        if (component.onReplicate) { component.onReplicate(clone); }
    }
});