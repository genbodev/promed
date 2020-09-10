/**
* swOrganizationListWindow - Связи МО с организациями в ЛИС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitriy Vlasenko
* @copyright    Copyright (c) 2014 Swan Ltd.
* @version      23.06.2014
*/
sw.Promed.swOrganizationListWindow = Ext.extend(sw.Promed.BaseForm, {
    convertDates: function (obj){
        for(var field_name in obj) {
            if (obj.hasOwnProperty(field_name)) {
                if (typeof(obj[field_name]) == 'object') {
                    if (obj[field_name] instanceof Date) {
                        obj[field_name] = obj[field_name].format('d.m.Y H:i');
                    }
                }
            }
        }
        return obj;
    },
    title: lang['svyazi_mo_s_organizatsiyami_v_lis'],
	//iconCls: '',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swOrganizationListWindow',
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	show: function() {
		var that = this;
		that.doSearch();
		
		sw.Promed.swOrganizationListWindow.superclass.show.apply(this, arguments);
	},
	openOrganizationEditWindow: function(action)
	{
		var win = this;
		var formParams = new Object();
		
		if (action == 'edit' || action == 'view') {
			var record = win.Grid.getGrid().getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('Organization_id'))) return;
			
			formParams.Organization_id = record.get('Organization_id');
			formParams.Org_id = record.get('Org_id');
			formParams.Organization_Code = record.get('Organization_Code');
			formParams.Organization_Name = record.get('Organization_Name');
		}
				
		getWnd('swOrganizationEditWindow').show({
			formParams: formParams,
			action: action,
			callback: function() {
				win.Grid.getGrid().getStore().reload();
			}
		});
	},
	deleteOrganization: function(action) {
		var win = this;
		var record = win.Grid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('Organization_id'))) return;
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.getLoadMask(lang['udalenie_svyazi_mo_s_organizatsiey_v_lis']).show();
					Ext.Ajax.request({
						url: '/?c=Organization&m=delete',
						params: {
							Organization_id: record.get('Organization_id')
						},
						callback: function(opt, success, response) {
							win.getLoadMask().hide();
							win.Grid.getGrid().getStore().reload();
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_svyaz_mo_s_organizatsiey_v_lis'],
			title: lang['vopros']
		});
	},
	doSearch: function() {
		this.Grid.removeAll();
		this.Grid.loadData();
	},
	initComponent: function() {
		var win = this;

		this.Grid = new sw.Promed.ViewFrame({
			selectionModel: 'multiselect',
			region: 'center',
			layout: 'fit',
			autoLoadData: false,
			object: 'Organization',
			dataUrl: '/?c=Organization&m=loadList',
			autoExpandColumn: 'autoexpand',
			stringfields:[
                {name: 'Organization_id', type: 'int', header: 'ID', key: true},
				{name: 'Organization_Code', type:'string', header: lang['kod'], width: 100},
				{name: 'Organization_Name', type:'string', header: lang['organizatsiya_v_lis'], width: 300},
				{name: 'Org_id', type:'int', hidden: true},
				{name: 'Org_Nick', type:'string', header: lang['mo'], width: 300, id: 'autoexpand'}
			],
			actions:[
				{name:'action_add', handler: function () { win.openOrganizationEditWindow('add'); } }, // 
				{name:'action_edit', handler: function () { win.openOrganizationEditWindow('edit'); } },
				{name:'action_view', handler: function () { win.openOrganizationEditWindow('view'); } },
				{name:'action_delete', handler: function () { win.deleteOrganization(); } },
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,index,record){

			},
            onLoadData: function(sm, index, record){
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
			}
		});
		
		this.CenterPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [ this.Grid ]
		});
		
		Ext.apply(this, {
			layout: 'border',
			items: [
				this.CenterPanel
			]
		});
		
		sw.Promed.swOrganizationListWindow.superclass.initComponent.apply(this, arguments);
	}
});