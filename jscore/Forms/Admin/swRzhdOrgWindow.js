/**
* swRzhdOrgWindow - Справочник по РЖД организациям
* @author       Магафуров Салават
* @version      12.2017
*/

sw.Promed.swRzhdOrgWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swRzhdOrgWindow',
	title: 'Организации РЖД',
	width: 600,
	resizable: false,
	autoHeight: true,
	onHide: Ext.emptyFn,

	addOrgRequest: function(id)
	{
		var wnd = this;
		Ext.Ajax.request({
			url: '/?c=RzhdRegistry&m=addRzhdOrg',
			params: {
				'Org_id': id,
				'pmUser_id' : getGlobalOptions().pmuser_id
			},
			success: function(response, options){
				wnd.RzhdOrgGrid.getGrid().getStore().load();
				if(window.swRzhdRegistryWindow) {			//обновляем комбобокс в окне swRzhdRegistryWindow
					window.swRzhdRegistryWindow.findById('RzhdOrgCombo_id').getStore().load();
				}
				if(window.swRzhdRegistryViewWindow)				//обновляем комбобокс в окне swRzhdRegistryViewWindow
					window.swRzhdRegistryViewWindow.findById('RzhdOrgCombo').getStore().load();
			},
			failure: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
					sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
				}
			}
		});
	},

	deleteOrgRequest: function(id)
	{
		var wnd = this;
		Ext.Ajax.request({
			url: '/?c=RzhdRegistry&m=delRzhdOrg',
			params: {
				'RzhdOrg_id': id,
				'pmUser_id' : getGlobalOptions().pmuser_id
			},
			success: function(response, options){
				wnd.RzhdOrgGrid.getGrid().getStore().load()
			},
			failure: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
					sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
				}
			}
		});
	},

	addOrg: function()
	{
		var wnd = this;
		getWnd('swOrgSearchWindow').show({
			enableOrgType: true,
			onSelect: function(orgData) {
				if ( orgData.Org_id > 0 )
				{
					wnd.addOrgRequest(orgData.Org_id);
				}
				getWnd('swOrgSearchWindow').hide();
			}
		});
	},

	deleteOrg: function()
	{
		var wnd = this;
		var record = wnd.RzhdOrgGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('RzhdOrg_id')) {
			return false;
		}
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					wnd.deleteOrgRequest(record.get('RzhdOrg_id'));
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},

	initComponent: function()
	{
		var wnd = this;

		this.RzhdOrgGrid = new sw.Promed.ViewFrame({
			id: 'RzhdOrgGrid',
			autoLoadData: true,
			height: 400,
			object: 'RzhdOrg',
			paging: true,
			dataUrl: '/?c=RzhdRegistry&m=getRzhdOrgs',
			stringfields: [
				{ name: 'RzhdOrg_id', key: true, type: 'int' },
				{ name: 'Org_Nick', header: 'Сокращенное наименование', width: 180 },
				{ name: 'Org_Name', header: 'Полное наименование', width: 400 }
			],
			actions: [
				{ name: 'action_add', handler: function(){wnd.addOrg()} },
				{ name: 'action_delete', handler: function(){wnd.deleteOrg()} },
				{ name: 'action_edit', hidden: true},
				{ name: 'action_view', hidden: true},
				{ name: 'action_print', hidden: true}
			],
		});

		this.MainPanel = new Ext.FormPanel({
			id: 'mainPanel',
			autoHeight: true,
			bodyBorder: false,
			frame: true,
			fileUpload: true,
			labelWidth: 150,
			items: this.RzhdOrgGrid
		});

		Ext.apply(this,
		{
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						wnd.hide();
					},
					iconCls: 'close16',
					onTabElement: 'rifOk',
					text: BTN_FRMCLOSE
				}
			],
			items: this.MainPanel
		});

		sw.Promed.swRzhdOrgWindow.superclass.initComponent.apply(this, arguments);
	},

});
