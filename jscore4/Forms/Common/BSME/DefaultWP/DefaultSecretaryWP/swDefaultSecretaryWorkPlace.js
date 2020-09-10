/* 
 * Шаблон рабочего места секретаря БСМЭ
 */


Ext.define('common.BSME.DefaultWP.DefaultSecretaryWP.swDefaultSecretaryWorkPlace', {
	extend: 'common.BSME.DefaultWP.BSMEDefaultWP.swBSMEDefaultWorkPlace',
	refId: 'DefaultSecretaryWorkPlace',
	baseCls: 'arm-window',
	id: 'DefaultSecretaryWorkPlace',
	createRequestButtonHandler: Ext.emptyFn,/*Определяются наследниками*/
	TabPanelItems: [
		{
			title: 'Все заявки <em>0</em>',
			itemId: 'All',
			iconCls: 'tab_all_icon16'
		}, {
			title: 'Готовые',	//<em>0</em>',
			itemId: 'Archived',
			iconCls: 'tab_check_icon16'
		}
	],
    initComponent: function() {
		var me = this;
		
		me.callParent(arguments);
		
	}
})
		