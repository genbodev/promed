/* 
 * Шаблон рабочего места руководителя БСМЭ
 */


Ext.define('common.BSME.DefaultWP.DefaultHeadWP.swDefaultHeadWorkPlace', {
	extend: 'common.BSME.DefaultWP.BSMEDefaultWP.swBSMEDefaultWorkPlace',
	alias: 'widget.swDefaultHeadWorkPlace',
    autoShow: true,
	maximized: true,
	width: 1000,
	refId: 'DefaultHeadWorkPlace',
	closable: true,
	baseCls: 'arm-window',
    header: false,
	renderTo: Ext.getCmp('inPanel').body,
	callback:Ext.emptyFn,
	id: 'DefaultHeadWorkPlace',
	layout: {
        type: 'fit'
    },
	constrain: true,
    initComponent: function() {
		var me = this;
//		Ext.applyIf(me,{
//			
//		})
		me.callParent(arguments);
	}
})
		