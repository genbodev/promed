/**
* Обмен сообщениями - Форма "Сообщения"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      23.08.2011
*/

sw.Promed.swMessagesViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['soobscheniya'],
	iconCls: 'messages16',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	objectName: 'swMessagesViewWindow',
	closeAction: 'hide',
	id: 'swMessagesViewWindow',
	objectSrc: '/jscore/Forms/Messages/swMessagesViewWindow.js',
	show: function()
	{
		sw.Promed.swMessagesViewWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var node = this.FolderTypeView.getRootNode();
		if (node.isExpanded())
		{
			this.reloadTree();
		}
		
		if(arguments[0] && (arguments[0].mode == 'newMessages' || arguments[0].mode == 'openMessage' || arguments[0].mode == 'newMessage'))
			win.mode = arguments[0].mode;
		else
			win.mode = 'allMessages';

		if(win.mode == 'newMessage'){
			win.params = arguments[0].params;
			win.PersonRegister_id = arguments[0].params.PersonRegister_id;
			win.GroupPregnancy = arguments[0].params.GroupPregnancy;
		}
			
		if(win.mode == 'newMessages')
		{
			win.FiltersPanel.getForm().findField('Message_isRead').setValue(1);
			win.doSearch();
		}
		win.setTitleFieldset();
		var userssendcombo = win.FiltersPanel.getForm().findField('UserSend_id');
		userssendcombo.getStore().load();
		if (win.mode == 'openMessage' && arguments[0] && arguments[0].message_data) {
			var message_data = arguments[0].message_data;

			//указываем идентификатор вкладки, которую надо открыть после выбора папки с сообощениемями
			win.set_tab = 'tab'+message_data.Message_id;

			if(win.checktab(message_data.Message_id))
				win.openMessageTabforEdit(message_data);
			else {
				win.CenterViewMessagesPanel.setActiveTab(message_data.Message_id);
			}
		} else {
			win.set_tab = null;
		}
	},
	
	setTitleFieldset: function()
	{
		var fieldset = this.FiltersPanel.find('xtype', 'fieldset')[0];
		var flag = false;
		fieldset.findBy(function(field){
			if(typeof field.xtype != 'undefined' && field.xtype.inlist(['combo','daterangefield','swnoticetypecombo']))
			{
				if(field.getRawValue() != '')
					flag = true;
			}
		});
		fieldset.setTitle((flag)?lang['filtr_ustanovlen']:lang['filtr']);
	},
	
	doSearch: function()
	{
		params = this.FiltersPanel.getForm().getValues();
		params.FolderType_id = 1;
		if(this.mode == 'newMessages')
			params.Message_isRead = 1;
		
		var grid = this.MessageGridPanel.getGrid();
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load();
		this.mode = 'allMessages';
	},
	
	doReset: function()
	{
		this.FiltersPanel.getForm().reset();
		this.FiltersPanel.getForm().findField('Message_isRead').setValue(0);
		this.setTitleFieldset();
		this.MessageGridPanel.getGrid().getStore().baseParams = {}
		var node = this.FolderTypeView.getNodeById(1);
		node.fireEvent('click', node);
	},
	
	printMessage: function(data)
	{
		var Message_Text = data.Message_Text;
		var tab = this.findById('message_'+data.Message_id);
		if (tab && tab.Message_Text) {
			Message_Text = tab.Message_Text;
		}

		var html = '<div style="background-color: #eee; padding: 10px;">'+
			'<p style="font-size: 10pt; font-weight: bold; float: left;">'+data.Message_Subject+
			'</p><p style="font-weight: bold; text-align: right;">'+Ext.util.Format.date(data.Message_setDT, 'd.m.Y H:i:s')+'</p>'+
			'<br />от: '+data.PMUser_Name+' ('+data.Lpu_Nick+ ((data.Dolgnost_Name != '')?', '+data.Dolgnost_Name:'')+')'+
			'</div>'+
			'<div style="font-size: 12pt; padding: 10px;">'+Message_Text+'</div>';
		openNewWindow(html);
	},
	
	saveMessage: function(newTab, filterpanel, MessagePanel, isSend)
	{
		var frm = this;
		var params = {}
		if(!filterpanel.getForm().findField('Message_Subject').isValid())
		{
			//sw.swMsg.alert('Ошибка', 'Не указан заголовок сообщения!');
			filterpanel.getForm().findField('Message_Subject').focus(true, 100);
			return false;
		}
		
		params.Message_Subject = filterpanel.getForm().findField('Message_Subject').getValue();
		var recipients_type = parseInt(filterpanel.getForm().findField('RecipientType_id').getValue());
		if(recipients_type == '' || isNaN(recipients_type))
			return false;
		
		switch(recipients_type)
		{
			case 1: // Если адресаты: пользователи
				var usersGrid = filterpanel.findById('smvwusersGrid');
				if(usersGrid)
				{
					if(usersGrid.getStore().getCount() == 0)
						return false;
					params.Users_id = '';
					var j = 1;
					usersGrid.getStore().each(function(rec){
						params.Users_id += rec.get('pmUser_id');
						if(j < usersGrid.getStore().getCount())
							params.Users_id += '|';
						j++;
					});
				}
				else // это если отвечаем на сообщение
				{
					params.Users_id = filterpanel.getForm().findField('PMUser_id').getValue();
				}
			break;
			
			case 2: // Если адресаты: группа (из адр. книги)
				var combo = filterpanel.getForm().findField('Group_id');
				var rec = combo.getStore().getById(combo.getValue());
				if(!rec)
					return false;
				
				// Собираем параметры выбранной группы
				params.group_id = rec.get('id');
				params.dn = rec.get('dn');
				params.group_name = rec.get('text');
				params.group_type = rec.get('type');
			break;
			
			case 4: // Если адресаты: ЛПУ
				var lpuGrid = filterpanel.findById('smvwLpuGrid');
				if(lpuGrid.getStore().getCount() == 0)
					return false;
				params.Lpus = '';
				var j = 1;
				lpuGrid.getStore().each(function(rec){
					params.Lpus += rec.get('Lpu_id');
					if(j < lpuGrid.getStore().getCount())
						params.Lpus += '|';
					j++;
				});
				var combo = filterpanel.getForm().findField('pmUser_Group');
				params.pmUser_Group = combo.getValue();
			break;

			case 6: // Если адресаты: все ЛПУ
				var combo = filterpanel.getForm().findField('pmUser_Group');
				params.pmUser_Group = combo.getValue();
			break;
		}
		
		// Собираем атрибуты прикрепленных файлов (если есть)
		var files = new Array();
		this.FilesPanel.findBy(function(file){
			files.push(file.settings.name+'::'+file.settings.tmp_name);
		}, this.FilesPanel);
		
		if(files.length > 0)
			params.Files = files.join('|');
		
		if(isSend)
			params.Message_isSent = 1;
		
		
		if(filterpanel.getForm().findField('action'))
		{
			params.action = filterpanel.getForm().findField('action').getValue();
			params.Message_id = filterpanel.getForm().findField('Message_id').getValue();
			params.Message_pid = filterpanel.getForm().findField('Message_pid').getValue();
		}
		else
		{
			params.action = 'ins';
			params.Message_pid = filterpanel.getForm().findField('Message_id').getValue();
		}
		params.RecipientType_id = recipients_type;
		
		if(isSend)
			var msgword = lang['otpravka'];
		else
			var msgword = lang['sohranenie'];
		
		var lm = this.getLoadMask(lang['podojdite_idet']+msgword+lang['soobscheniya']);
		lm.show();

		if (!Ext.isEmpty(this.PersonRegister_id)){
			params.Message_Subject = params.Message_Subject + "|" + this.PersonRegister_id;
		}
		MessagePanel.getForm().submit({
			params: params,
			success: function()
			{
				lm.hide();
				frm.closeTab(newTab, true);
				frm.reloadTree();
			},
			failure: function()
			{
				lm.hide();
				//sw.swMsg.alert('Ошибка', 'Ошибка БД!');
			}
		});
	},
	
	reloadMessageGrid: function(node)
	{
		var grid = this.MessageGridPanel.getGrid();
		
		var flag_idx = grid.getColumnModel().findColumnIndex('Message_isFlag');
		if(node.attributes.FolderType != 1)
		{
			grid.getColumnModel().setHidden(flag_idx, true);
			grid.getColumnModel().getColumnById(flag_idx).hideable = false;
		}
		else
		{
			grid.getColumnModel().setHidden(flag_idx, false);
			grid.getColumnModel().getColumnById(flag_idx).hideable = true;
		}
		
		grid.getStore().baseParams.FolderType_id = node.attributes.FolderType;
		grid.getStore().load();
	},
	
	reloadTree: function(sAt, selnode) // sAt - параметр, отвечающий за установку 0-го таба активным (false - нет)
	{
		var tree = this.FolderTypeView;
		var root = tree.getRootNode();
		root.sAt = sAt;
		if(selnode)
			root.selnode = selnode;
		tree.getLoader().load(root);
	},
	
	reloadMessageGridUnderFolderType: function(node)
	{
		var title = node.attributes.text;
		if(typeof node.sAt == 'undefined' || node.sAt == true)
			this.CenterViewMessagesPanel.setActiveTab(0);
		if (this.set_tab != null) {
			this.CenterViewMessagesPanel.setActiveTab(this.set_tab);
			this.set_tab = null;
		}
		this.CenterViewMessagesPanel.getItem(0).setIconClass(node.attributes.iconCls);
		this.CenterViewMessagesPanel.getItem(0).setTitle(title);
		node.sAt = true;
		this.reloadMessageGrid(node);
		if(this.mode == 'newMessage'){
			this.createNewTab();
			var panel = this.CenterViewMessagesPanel.activeTab.find('region', 'north');
			if (panel && panel[0]) {
				var form = panel[0].getForm();
				var message_field = form.findField('Message_Subject');
				if (message_field) {
					message_field.setValue(this.params.Message_Subject);
				}
				var recipienttype = form.formPanel.findByType('swrecipienttypecombo');
				if (recipienttype){
					recipienttype = recipienttype[0];
					recipienttype.setValue(1);
					recipienttype.fireEvent('select', recipienttype, 1, 1);
				}
				var grid = panel[0].getComponent("smvwusersGrid");
				if (grid){
					grid.getStore().removeAll();
					grid.getStore().add(new Ext.data.Record({pmUser_id:this.params.pmUser_id, pmUser_Login:this.params.pmUser_Login}))
				}
			}

			panel = this.CenterViewMessagesPanel.activeTab.find('region', 'center');
			if (panel && panel[0]) {
				var form = panel[0].getForm();
				if (form.formPanel){
					var comp = form.formPanel.find('name', 'Message_Text');
					if (comp && comp[0])
						comp[0].setValue(this.params.Message_Text);
				}
			}
			this.mode = 'newMessages';
		}
	},
	
	checktab: function(tab_id)
	{
		/** Проверяем существует ли вкладка с таким ид, если нет, возвращаем true, иначе переходим на нее и возвращаем false
		*	входящие параметры: tab_id - идентификатор новой вкдадки, как правило Message_id
		*	на выходе: boolean
		*/
		if(tab_id)
			var tab = this.CenterViewMessagesPanel.getItem('tab'+tab_id);
		else
			var tab = this.CenterViewMessagesPanel.getItem('tabnew');
		
		if(!tab)
			return true;
		else
		{
			this.CenterViewMessagesPanel.unhideTabStripItem(tab);
			this.CenterViewMessagesPanel.setActiveTab(tab);
			return false;
		}
	},
	
	closeTab: function(tab, saved)
	{
		if(saved)
		{
			this.CenterViewMessagesPanel.setActiveTab(0);
			this.CenterViewMessagesPanel.hideTabStripItem(tab);
			this.CenterViewMessagesPanel.remove(tab, true);
		}
		else
		{
			Ext.Msg.show({
				title: lang['vnimanie'],
				msg: lang['soobschenie_ne_sohraneno_zakryit_soobschenie_bez_sohraneniya'],
				buttons: Ext.Msg.YESNO,
				fn: function(btn)
				{
					if (btn === 'yes')
					{
						this.CenterViewMessagesPanel.setActiveTab(0);
						this.CenterViewMessagesPanel.hideTabStripItem(tab);
						this.CenterViewMessagesPanel.remove(tab, true);
					}
					else if (btn === 'no')
					{
						return false;
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION
			});
		}
	},
	/*createGroupMenu: function(params, callback) 
	{
		Ext.Ajax.request({
			params: params,
			url: '/?c=Messages&m=getGroupsNoUser',
			callback: function(opt, success, response)
			{
				var win = this;
				var result = Ext.util.JSON.decode(response.responseText);
				var menuArr = new Array();
				var menu = new Ext.menu.Menu({id:'GroupsNoUserMenu'});
				if (result.length>0)
				{
					// формирование меню
					for(i=0; i<result.length; i++)
					{
						menuArr.push({text: result[i]['name'], num: result[i]['id'], handler: function() {win.addUserInAddressBook(this);}});
					}
					for (key in menuArr)
					{
						if (key!='remove')
							this.menu.add(menuArr[key]);
					}
				}
				callback({menu: menu, count: result.length});
			}.createDelegate(this)
		});
	},*/
	addUserInAddressBook: function(point)
	{
		var win = this;
		var lm = this.getLoadMask();
		lm.show();
		Ext.Ajax.request(
		{
			url: '/?c=Messages&m=addGroupUser',
			params: 
			{
				// все параметры относящиеся к группе 
				group_name: point.text,
				group_type: point.group_type,
				group_id: point.num,
				dn: point.dn,
				// и выбранный пользователь 
				user_id: point.UserSend_ID
			},
			callback: function(opt, success, resp) 
			{
				lm.hide();
				var menuArr = win.groupMenu.items.items;
				var enableItems = new Array();
				for(var i=0; i<menuArr.length; i++)
				{
					if(menuArr[i].id == point.id)
						menuArr[i].disable();
					if(menuArr[i].disabled == false)
						enableItems[i] = menuArr[i];
				}
				if(enableItems.length == 0)
				{
					var link = document.getElementById('message_link_'+point.Message_id);
					if (link) {
						link.parentNode.removeChild(link);
					}
				}
				sw.swMsg.alert(lang['uvedomlenie'], lang['polzovatel_sohranen_v_gruppu']+point.text+'!');
			}
		});
	},
	
	createGroupMenu: function(data, MessageData) 
	{
		var win = this;
		var menuArr = new Array();
		var menu = new Ext.menu.Menu({id:'GroupsNoUserMenu'});
		if (data.length>0)
		{
			// формирование меню
			for(i=0; i<data.length; i++)
			{
				menuArr.push({text: data[i]['name'], num: data[i]['id'], dn: data[i]['dn'], group_type: data[i]['type'], Message_id: MessageData.Message_id, iconCls: data[i]['iconCls'], UserSend_ID: MessageData.UserSend_ID, handler: function() {win.addUserInAddressBook(this);}});
			}
			for (key in menuArr)
			{
				if (key!='remove')
					menu.add(menuArr[key]);
			}
		}
		return {menu: menu, count: data.length};
	},
	showGroupMenu: function(id) 
	{
		if (this.groupMenu) {
			this.groupMenu.show(Ext.fly(id));
		}
	},
	openMessageTab: function()
	{	
		var node = this.FolderTypeView.getSelectionModel().getSelectedNode();
		var foldertype = node.attributes.FolderType;
		var record = this.MessageGridPanel.getGrid().getSelectionModel().getSelected();
		if(!record)
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrano_soobschenie']);
			return false;
		}
		var data = record.data;
		
		var ct = this.checktab(data.Message_id);
		if(!ct)
			return false;
		
		if(foldertype == 3)
			this.openMessageTabforEdit(data);
		else
			this.openMessageTabforView(data, foldertype);
	},
	
	openMessageTabforEdit: function(data)
	{
		this.createNewTab(data);
	},
	
	openMessageTabforView: function(data, foldertype)
	{
		var win = this;
		var node = this.FolderTypeView.getSelectionModel().getSelectedNode();
		var actions = [];
		
		actions.responseAction = new Ext.Action({
			text: lang['otvetit'],
			iconCls: 'sent16',
			xtype: 'button',
			hidden: (foldertype == 1)?false:true,
			handler: function()
			{
				this.createResponseTab(data);
			}.createDelegate(this)
		});
		
		actions.deleteAction = new Ext.Action({
			text: lang['udalit'],
			iconCls: 'delete16',
			xtype: 'button',
			handler: function()
			{
				var win = this;
				var node = this.FolderTypeView.getSelectionModel().getSelectedNode();
				var params = {};
				sw.swMsg.show(
				{
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) 
					{
						if ( buttonId == 'yes' )
						{
							params.FolderType_id = foldertype;
							params.Message_id = data.Message_id;
							params.MessageRecipient_id = data.MessageRecipient_id;
							Ext.Ajax.request({
								params: params,
								url: '/?c=Messages&m=deleteMessage',
								callback: function(opt, success, resp)
								{
									if (success)
									{
										win.reloadTree();
									}
								}
							});
							win.closeTab(MessageTab, true);
						}
					},
					icon: Ext.Msg.QUESTION,
					msg: lang['udalit_dannoe_soobschenie'],
					title: lang['vopros']
				});
			}.createDelegate(this)
		});
		
		actions.printAction = new Ext.Action({
			text: lang['pechat'],
			iconCls: 'print16',
			xtype: 'button',
			handler: function()
			{
				this.printMessage(data);
			}.createDelegate(this)
		});
		
		actions.closeAction = new Ext.Action({
			text: lang['zakryit'],
			iconCls: 'close16',
			xtype: 'button',
			handler: function()
			{
				this.closeTab(MessageTab, true);
			}.createDelegate(this)
		});
		
		
		var tbar = new Ext.Toolbar({
			items: [
				actions.responseAction,
				actions.deleteAction,
				actions.printAction,
				actions.closeAction
			]
		});
		
		this.FilesPanel = new Ext.Panel({
			layout: 'form',
			frame: true,
			hidden: false,
			autoHeight: true,
			border: false,
			region: 'south',
			action: 'view',
			title: lang['prikreplennyie_faylyi'],
			titleCollapse: true,
			collapsible: true,
			collapsed: true,
			animCollapse: false,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			items: []
		});
		
		/** При открытии проверяем если сообщение не прочитано, 
		*   то после открытием помечаем как прочитанное
		*/
		
		var html = 
			'<div style="background-color: #eee; padding: 10px;">'+
			'<p style="font-size: 10pt; font-weight: bold; float: left;">'+data.Message_Subject+
			'</p><p style="font-weight: bold; text-align: right;">'+data.Message_setDT/*Ext.util.Format.date(data.Message_setDT, 'd.m.Y H:i:s')*/+'</p>'+
			'<br />от: '+data.PMUser_Name+((data.Lpu_Nick!='')?' ('+data.Lpu_Nick+ ((data.Dolgnost_Name != '')?', '+data.Dolgnost_Name:'')+')':'')+
			'<!-- menu_group -->'+
			'</div>'+
			'<div style="font-size: 10pt; padding: 10px;background-color: white;"><!-- message --></div>'
			
		var tmphtml = html.replace('<!-- message -->', data.Message_Text);
					
		var MessageTab = this.CenterViewMessagesPanel.add({	
			title: data.Message_Subject,
			iconCls: 'message16',
			id: 'tab'+data.Message_id,
			tbar: tbar,
			listeners: {
				render: function(tab)
				{
					var lm = win.getLoadMask(lang['zagruzka_dannyih']);
					lm.show();
					if(data.Message_isRead != 1 && node.attributes.FolderType == 1)
					{
						Ext.Ajax.request({
							url: '/?c=Messages&m=setMessageIsRead',
							params: data,
							callback: function(opt, success, response)
							{
								if(success)
								{
									win.reloadTree(false);
								}
							}
						});
					}

					MessageTab.findById('message_'+data.Message_id).Message_Text = null;

					Ext.Ajax.request({
						url: '/?c=Messages&m=getMessage',
						params: {Message_id: data.Message_id},
						failure: function()
						{
							lm.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_bd']);
						},
						callback: function(opt, success, response)
						{
							if(success)
							{
								lm.hide();
								var record = Ext.util.JSON.decode(response.responseText)[0];
								// Создание всплывающего меню 
								var setmenu = win.createGroupMenu(record.groupsMenu, data);
								win.groupMenu = null;
								if (setmenu.count>0) 
								{
									var linkpanel = ' <a id="message_link_'+data.Message_id+'" href="#" onClick="Ext.getCmp(&quot;swMessagesViewWindow&quot;).showGroupMenu(&quot;'+'message_link_'+data.Message_id+'&quot;);">сохранить в адресную книгу</a>';
									win.groupMenu = setmenu.menu;
								}
								var h = html.replace('<!-- menu_group -->', (linkpanel)?linkpanel:'');
								h = h.replace('<!-- message -->', (record.Message_Text == null)?'':record.Message_Text);
								MessageTab.findById('message_'+data.Message_id).body.update(h);
								//Отдельно запоминаем текст сообщения для вывода в печать
								MessageTab.findById('message_'+data.Message_id).Message_Text = record.Message_Text;
								if(record.Files && record.Files_cnt > 0)
								{
									for(j=0; j<record.Files.length; j++)
									{
										win.addFileInPanel(record.Files[j]);
									}
								} else {
									win.setTitleFilesPanel();
								}
							}
						}
					});
				}
			},
			closable: true,
			layout: 'border',
			items: [
				{
					id: 'message_'+data.Message_id,
					html: tmphtml,
					border: false,
					autoScroll: true,
					region: 'center'
				},
				this.FilesPanel
			]
		});
		this.CenterViewMessagesPanel.setActiveTab(MessageTab);
		MessageTab.syncSize();
	},
	
	createResponseTab: function(data)
	{
		// data.Message_id
		
		var ct = this.checktab('resp'+data.Message_id);
		if(!ct)
			return false;
		
		var actions = [];
		actions.sendAction = new Ext.Action({
			text: lang['otpravit'],
			iconCls: 'message-send16',
			xtype: 'button',
			handler: function()
			{
				this.saveMessage(ResponseTab, filterpanel, MessagePanel, true);
			}.createDelegate(this)
		});
		actions.saveAction = new Ext.Action({
			text: lang['sohranit'],
			iconCls: 'save16',
			xtype: 'button',
			handler: function()
			{
				this.saveMessage(ResponseTab, filterpanel, MessagePanel, false);				
			}.createDelegate(this)
		});
		actions.addfileAction = new Ext.Action({
			text: lang['prikrepit_faylyi'],
			iconCls: 'ext-ux-uploaddialog-addbtn',
			xtype: 'button',
			handler: function()
			{
				this.openUploadDialog();
			}.createDelegate(this)
		});
		actions.closeAction = new Ext.Action({
			text: lang['zakryit'],
			iconCls: 'close16',
			xtype: 'button',
			handler: function()
			{
				this.closeTab(ResponseTab, false);
			}.createDelegate(this)
		});
		
		var tbar = new Ext.Toolbar({
			region: 'north',
			autoHeight: true,
			items: [
				actions.sendAction,
				actions.saveAction,
				actions.addfileAction,
				actions.closeAction
			]
		});
		
		this.FilesPanel = new Ext.Panel({
			layout: 'form',
			frame: true,
			hidden: false,
			autoHeight: true,
			border: false,
			region: 'south',
			action: 'add',
			title: lang['prikreplennyie_faylyi'],
			titleCollapse: true,
			collapsible: true,
			collapsed: true,
			animCollapse: false,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			items: []
		});
		
		
		var filterpanel = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			listeners: {
				'render': function()
				{
					this.getForm().setValues(data);
					this.getForm().findField('Message_Subject').setValue(lang['otvet_na']+data.Message_Subject);
				}
			},
			region: 'north',
			//url: '/?c=Messages&m=',
			bodyStyle: 'padding: 3px;',
			labelAlign: 'right',
			labelWidth: 70,
			items: [
				{
					xtype: 'hidden',
					name: 'Message_id'
				}, {
					xtype: 'hidden',
					name: 'RecipientType_id',
					value: 1
				}, {
					xtype: 'hidden',
					name: 'PMUser_id'
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'Message_Subject',
					fieldLabel: lang['zagolovok']
				}, {
					xtype: 'textfieldpmw',
					anchor: '100%',
					disabled: true,
					name: 'PMUser_Login',
					fieldLabel: lang['komu']
				}
			]
		});
		
		var MessagePanel = new Ext.form.FormPanel({
			layout: 'fit',
			border: false,
			url: '/?c=Messages&m=saveMessage',
			region: 'center',
			items: [
				new Ext.form.HtmlEditor({
					hideLabel: true,
					name: 'Message_Text',
					defaultValue: ''
				})
			]
		});
		
		var ResponseTab = this.CenterViewMessagesPanel.add({	
			title: lang['novoe_soobschenie'],
			iconCls: 'message-new16',
			id: 'tabresp'+data.Message_id,
			tbar: tbar,
			closable: true,
			layout: 'border',
			items: [filterpanel, MessagePanel, this.FilesPanel]
		});
		this.CenterViewMessagesPanel.setActiveTab(ResponseTab);
		ResponseTab.syncSize();
	},
	
	createNewTab: function(data)
	{
		var ct = this.checktab(), w = this;
		if(!ct)
			return false;

		var actions = [];
		actions.sendAction = new Ext.Action({
			text: lang['otpravit'],
			iconCls: 'message-send16',
			xtype: 'button',
			handler: function()
			{
				var sfilterpanel = w.CenterViewMessagesPanel.findBy(function(t){ return t.region == 'north'});
				vMessage_Subject = '';
				if (sfilterpanel){
					vMessage_Subject = sfilterpanel[0].getForm().findField('Message_Subject').getValue();
				}
				if (vMessage_Subject == 'Регистр беременных' && this.GroupPregnancy != 'undefined' && this.GroupPregnancy != 1)
					sw.swMsg.show({
						title: 'Внимание',
						msg: "Данный пользователь не имеет доступа к Регистру беременных",
						width: 450,
						buttons: Ext.MessageBox.OK
					});
				this.saveMessage(newTab, filterpanel, MessagePanel, true);
			}.createDelegate(this)
		});
		actions.saveAction = new Ext.Action({
			text: lang['sohranit'],
			iconCls: 'save16',
			xtype: 'button',
			handler: function()
			{
				this.saveMessage(newTab, filterpanel, MessagePanel, false);				
			}.createDelegate(this)
		});
		actions.addfileAction = new Ext.Action({
			text: lang['prikrepit_faylyi'],
			iconCls: 'ext-ux-uploaddialog-addbtn',
			xtype: 'button',
			handler: function()
			{
				this.openUploadDialog();
			}.createDelegate(this)
		});
		actions.closeAction = new Ext.Action({
			text: lang['zakryit'],
			iconCls: 'close16',
			xtype: 'button',
			handler: function()
			{
				if(data)
					this.closeTab(newTab, true);
				else
					this.closeTab(newTab, false);
			}.createDelegate(this)
		});

		var tbar = new Ext.Toolbar({
			region: 'north',
			autoHeight: true,
			items: [
				actions.sendAction,
				actions.saveAction,
				actions.addfileAction,
				actions.closeAction
			]
		});
		
		var filterpanel = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			bodyStyle: 'padding: 3px;',
			labelAlign: 'right',
			labelWidth: 120,
			listeners: {
				render: function(p)
				{
					p.getForm().findField('Message_Subject').focus(true, 100);
				}
			},
			region: 'north',
			items: [
				{
					xtype: 'hidden',
					name: 'Message_id'
				}, {
					xtype: 'hidden',
					name: 'Message_pid'
				}, {
					xtype: 'hidden',
					name: 'action',
					value: (data)?'upd':'ins'
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'Message_Subject',
					fieldLabel: lang['zagolovok']
				}, {
					xtype: 'swrecipienttypecombo',
					readOnly: true,
					enableKeyEvents: true,
					lastQuery: '',
					onLoadStore: function(store) {
						store.filterBy(function(rec){
							var filter_flag = true;
							if (rec.get('RecipientType_id') == 4 && !isSuperAdmin()) {
								filter_flag = false;
							}
							return filter_flag;
						});
					},
					listeners:
					{
						render: function(combo)
						{
							// справочник прогрузить
							var lists = w.getComboLists(filterpanel); // получаем список комбиков
							w.loadDataLists({}, lists, true); // прогружаем все справочники (третий параметр noclose - без операций над формой)
							filterpanel.getForm().findField('pmUser_Group').getStore().load();
						},
						select: function(combo, rec, idx)
						{
							var Lpucombo = filterpanel.getForm().findField('Lpucombo');
							var groupscombo = filterpanel.getForm().findField('Group_id');
							var lpugroupscombo = filterpanel.getForm().findField('pmUser_Group');
							var fields = [
								this.findById('smvwLpuGrid'),
								this.findById('smvw_lpucolumn'),
								this.findById('smvw_userscolumn'),
								this.findById('smvwusersGrid'),
								this.findById('smvw_groupscolumn'),
								this.findById('smvw_lpugroupscolumn')
							];
							
							for(i=0; i<fields.length; i++)
							{
								if(fields[i].isVisible() == true)
									fields[i].setVisible(false);
							}
							
							switch(idx)
							{
								case 1:
									fields[2].setVisible(true);
									fields[3].setVisible(true);
								break;
								
								case 2:
									fields[4].setVisible(true);
									newTab.doLayout();
									groupscombo.syncSize();
									groupscombo.focus(true, 100);
								break;
								
								case 4:
									fields[0].setVisible(true);
									fields[1].setVisible(true);
									fields[5].setVisible(true);
									newTab.doLayout();
									Lpucombo.getStore().load({callback:function(){
										if(!isSuperAdmin()){
											Lpucombo.setValue(getGlobalOptions().lpu_id);
											Lpucombo.disable();
										}else{
											Lpucombo.setValue('');
											Lpucombo.enable();
										}
										Lpucombo.syncSize();
										Lpucombo.focus(true, 100);
									}});
									
									
								break;
								
								case 6:
									fields[5].setVisible(true);
									newTab.doLayout();
									lpugroupscombo.syncSize();
									lpugroupscombo.focus(true, 100);
								break;

								default:
									for(i=0; i<fields.length; i++)
									{
										if(fields[i].isVisible() == true)
											fields[i].setVisible(false);
									}
									MessagePanel.getForm().findField('Message_Text').focus(true);
								break;
							}
							newTab.doLayout();
							this.doLayout();
						}.createDelegate(this)
					}
				}, {
					layout: 'column',
					hidden: true,
					border: false,
					defaults: {
						border: false
					},
					id: 'smvw_userscolumn',
					items: [
						{
							layout: 'form',
							items: [
								{
									xtype: 'button',
									style: 'margin-left: 75px;',
									text: lang['adresnaya_kniga'],
									handler: function()
									{
										var frm = this;
										var args = {};
										args.onSelect = function(userData)
										{
											var grid = frm.findById('smvwusersGrid');
											if (grid.getStore().findBy(function(rec) { return rec.get('pmUser_id') == userData.pmUser_id; }) > -1)
											{
												sw.swMsg.alert(lang['oshibka'], lang['etot_polzovatel_uje_vklyuchen_v_spisok_adresatov']);
												return false;
											}
											if(userData.pmUser_id == parseInt(getGlobalOptions().pmuser_id))
											{
												sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_mojete_byit_adresatom_sobstvennogo_soobscheniya']);
												return false;
											}
											var pmUser_Login = userData.pmUser_surName+' '+userData.pmUser_firName+' '+userData.pmUser_secName+' ('+userData.pmUser_Login+')';
											grid.getStore().loadData([{
												pmUser_id: userData.pmUser_id,
												pmUser_Login: pmUser_Login
											}], true);
											grid.getView().refresh();
											getWnd('swUserSearchWindow').hide();
										}
										getWnd('swAddressBookEditWindow').show(args);
									}.createDelegate(this)
									
								}
							]
						}, {
							layout: 'form',
							items: [
								{
									xtype: 'button',
									style: 'margin-left: 3px;',
									text: lang['poisk_polzovateley'],
									handler: function()
									{
										var args = {}
										var frm = this;
										args.selectUser = function(userData)
										{
											var grid = frm.findById('smvwusersGrid');
											if (grid.getStore().findBy(function(rec) { return rec.get('pmUser_id') == userData.pmUser_id; }) > -1)
											{
												sw.swMsg.alert(lang['oshibka'], lang['etot_polzovatel_uje_vklyuchen_v_spisok_adresatov']);
												return false;
											}
											if(userData.pmUser_id == parseInt(getGlobalOptions().pmuser_id))
											{
												sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_mojete_byit_adresatom_sobstvennogo_soobscheniya']);
												return false;
											}
											var pmUser_Login = userData.pmUser_surName+' '+userData.pmUser_firName+' '+userData.pmUser_secName+' ('+userData.pmUser_Login+')';
											grid.getStore().loadData([{
												pmUser_id: userData.pmUser_id,
												pmUser_Login: pmUser_Login
											}], true);
											grid.getView().refresh();
											getWnd('swUserSearchWindow').hide();
										}
										getWnd('swUserSearchWindow').show(args);
									}.createDelegate(this)
								}
							]
						}
					]
				},
				new Ext.grid.GridPanel({
					id: 'smvwusersGrid',
					hidden: true,
					style: 'margin: 3px 0px 5px 0px;',
					loadMask: true,
					autoExpandColumn: 'autoexpand',
					autoExpandMax: 2000,
					height: 200,
					store: new Ext.data.JsonStore({
						fields: [{
							name: 'pmUser_id'
						}, {
							name: 'pmUser_Login'
						}],
						autoLoad: false,
						url: '/?c=Messages&m=getDestinations_users'
					}),
					columns: [{
						dataIndex: 'pmUser_id',
						hidden: true,
						hideable: false
					}, {
						id: 'autoexpand',
						header: lang['polzovatel'],
						sortable: true,
						dataIndex: 'pmUser_Login'
					}],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true
					}),
					enableKeyEvents: true,
					listeners: {
						'keydown': function(e) {
							if (e.getKey() == Ext.EventObject.DELETE) {
								e.stopEvent();
								
								var grid = this.findById('smvwusersGrid');
								if (!grid.getSelectionModel().getSelected())
									return;
											
								var delIdx = grid.getStore().findBy(function(rec) { return rec.get('pmUser_id') == grid.getSelectionModel().getSelected().get('pmUser_id'); });
								
								if (delIdx >= 0)
									grid.getStore().removeAt(delIdx);
								grid.getView().refresh();
							}
						}.createDelegate(this)
					}
				}), {
					layout: 'column',
					border: false,
					defaults: {
						border: false
					},
					id: 'smvw_groupscolumn',
					hidden: true,
					items: [
						{
							layout: 'form',
							items: [
								{
									xtype: 'combo',
									name: 'Group_id',
									displayField: 'text',
									valueField: 'id',
									width: 400,
									triggerAction: 'all',
									mode: 'local',
									store: new Ext.data.Store({
										autoLoad: true,
										reader: new Ext.data.JsonReader({
											id: 'id'
										}, [{
											mapping: 'id',
											name: 'id',
											type: 'int'
										}, {
											mapping: 'text',
											name: 'text',
											type: 'string'
										}, {
											mapping: 'dn',
											name: 'dn',
											type: 'string'
										}, {
											mapping: 'type',
											name: 'type',
											type: 'string'
										}]),
										url: '/?c=Messages&m=getGroups'
									}),
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'{text}',
										'</div></tpl>'
									),
									listeners: {
										render: function(combo)
										{
											//combo.syncSize();
										}
									},
									fieldLabel: lang['gruppyi']
								}
							]
						}
					]
				}, {
					border: false,
					defaults: {
						border: false
					},
					xtype: 'panel',
					items: [
						{
							layout: 'form',
							id: 'smvw_lpugroupscolumn',
							hidden: true,
							items: [
								{
									width: 300,
									xtype: 'swusersgroupscombo',
									fieldLabel: lang['gr_polzovateley'],
									valueField: 'Group_Name',
									hiddenName: 'pmUser_Group'
								}
							]
						}, {
							layout: 'column',
							id: 'smvw_lpucolumn',
							hidden: true,
							border: false,
							defaults: {
								border: false
							},
							xtype: 'panel',
							items: [
								{
									layout: 'form',
									items: [
										{
											xtype: 'swlpulocalcombo',
											name: 'Lpucombo'
										}
									]
								}, {
									layout: 'form',
									style: 'margin-left: 3px;',
									items: [
										{
											xtype: 'button',
											iconCls: 'add16',
											text: BTN_GRIDADD,
											name: '',
											handler: function()
											{
												var lpucombo = filterpanel.getForm().findField('Lpucombo');
												var grid = this.findById('smvwLpuGrid');
												if(lpucombo.getValue() == '' || lpucombo.getValue() == null)
													return false;
												
												var store = lpucombo.getStore();
												var rec = store.getAt(store.findBy(function(rec) { return rec.get('Lpu_id') == lpucombo.getValue(); }));
												var lpu_id = rec.get('Lpu_id');
												var lpu_nick = rec.get('Lpu_Nick');
												if (grid.getStore().findBy(function(rec) { return rec.get('Lpu_id') == lpu_id; }) > -1)
													return false;
													
												grid.getStore().loadData([{
													Lpu_id: lpu_id,
													Lpu_Nick: lpu_nick
													}], true);
												grid.getView().refresh();
											}.createDelegate(this)
										}
									]
								}, {
									layout: 'form',
									style: 'margin-left: 3px;',
									items: [
										{
											xtype: 'button',
											iconCls: 'delete16',
											text: BTN_GRIDDEL,
											id: 'smvwButtonDel',
											handler: function()
											{
												var grid = this.findById('smvwLpuGrid');
												
												if (!grid.getSelectionModel().getSelected())
													return;
													
												var delIdx = grid.getStore().findBy(function(rec) { return rec.get('Lpu_id') == grid.getSelectionModel().getSelected().get('Lpu_id'); });
												
												if (delIdx >= 0)
													grid.getStore().removeAt(delIdx);
												grid.getView().refresh();
											}.createDelegate(this)
										}
									]
								}
							]
						}
					]
				},
				new Ext.grid.GridPanel({
					id: 'smvwLpuGrid',
					hidden: true,
					style: 'margin: 0px 0px 5px 0px;',
					loadMask: true,
					autoExpandColumn: 'autoexpand',
					autoExpandMax: 2000,
					height: 200,
					store: new Ext.data.JsonStore({
						fields: [{
							name: 'Lpu_id'
						}, {
							name: 'Lpu_Nick'
						}],
						autoLoad: false,
						url: '/?c=Messages&m=getDestinations_lpus'
					}),
					columns: [{
						dataIndex: 'Lpu_id',
						hidden: true,
						hideable: false
					}, {
						id: 'autoexpand',
						header: "Наименование ЛПУ",
						sortable: true,
						dataIndex: 'Lpu_Nick'
					}],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true
					}),
					enableKeyEvents: true,
					listeners: {
						'keydown': function(e) {
							if (e.getKey() == Ext.EventObject.DELETE) {
								e.stopEvent();
								this.findById('smvwButtonDel').handler();
							}
						}.createDelegate(this)
					}
				})
			],
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[	
				{ name: 'Message_id' },
				{ name: 'Message_pid' },
				{ name: 'Group_id' },
				{ name: 'Message_Subject' },
				{ name: 'RecipientType_id' }
			])
		});
		
		var win = this;
		
		this.FilesPanel = new Ext.Panel({
			layout: 'form',
			frame: true,
			hidden: false,
			autoHeight: true,
			border: false,
			region: 'south',
			action: (data)?'edit':'add',
			title: lang['prikreplennyie_faylyi'],
			titleCollapse: true,
			collapsible: true,
			collapsed: true,
			animCollapse: false,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			items: []
		});
		
		var MessagePanel = new Ext.form.FormPanel({
			layout: 'fit',
			border: false,
			url: '/?c=Messages&m=saveMessage',
			region: 'center',
			items: [
				new Ext.form.HtmlEditor({
					hideLabel: true,
					name: 'Message_Text',
					defaultValue: ''
				})
			]
		});

		var newTab = this.CenterViewMessagesPanel.add({	
			title: lang['novoe_soobschenie'],
			id: 'tab'+((data)?data.Message_id:'new'),
			iconCls: 'message-new16',
			tbar: tbar,
			listeners: {
				render: function()
				{
					if(data) // когда открываем на редактирование
					{
						this.setTitle(data.Message_Subject);
						filterpanel.getForm().load({
							params: {
								Message_id: data.Message_id
							},
							url: '/?c=Messages&m=getMessage',
							success: function(f, r)
							{
								var obj = Ext.util.JSON.decode(r.response.responseText);
								var RecipientTypeCombo = filterpanel.getForm().findField('RecipientType_id');
								var record = RecipientTypeCombo.getStore().getById(RecipientTypeCombo.getValue());
								var index = parseInt(RecipientTypeCombo.getValue(), 10);
								RecipientTypeCombo.fireEvent('select', RecipientTypeCombo, record, index);
								switch(index)
								{
									case 1:
										var grid = filterpanel.findById('smvwusersGrid');
										grid.getStore().baseParams = {
											Message_id: data.Message_id
										}
										grid.getStore().load();
									break;
																		
									case 4:
										var grid = filterpanel.findById('smvwLpuGrid');
										grid.getStore().baseParams = {
											Message_id: data.Message_id
										}
										grid.getStore().load();
									break;
								}
								var MessageTextField = MessagePanel.getForm().findField('Message_Text');
								MessageTextField.setValue(obj[0].Message_Text);
								if(obj[0].Files_cnt && obj[0].Files && obj[0].Files_cnt > 0)
								{
									for(j=0; j<obj[0].Files.length; j++)
									{
										win.addFileInPanel(obj[0].Files[j]);
									}
								}
								filterpanel.getForm().findField('Message_Subject').focus(true, 100);
							}
						});
					}
				}
			},
			closable: true,
			layout: 'border',
			items: [filterpanel, MessagePanel, win.FilesPanel]
		});
		this.CenterViewMessagesPanel.setActiveTab(newTab);
		newTab.syncSize();
	},
	
	deleteMessages: function()
	{
		var win = this;
		var node = this.FolderTypeView.getSelectionModel().getSelectedNode();
		var record = this.MessageGridPanel.getGrid().getSelectionModel().getSelected();
		if(!record)
			return false;
			
		var selections = this.MessageGridPanel.getGrid().getSelectionModel().getSelections();
		var Message_ids = [];
		var MessageRecipient_ids = [];

		for	(var key in selections) {
			if (selections[key].data) {
				Message_ids.push(selections[key].data['Message_id']);
				MessageRecipient_ids.push(selections[key].data['MessageRecipient_id']);
			}
		}
		
		var params = {}
		params.FolderType_id = node.attributes.FolderType;
		params.Message_ids = Ext.util.JSON.encode(Message_ids);
		params.MessageRecipient_ids = Ext.util.JSON.encode(MessageRecipient_ids);
		
		var delMessage = lang['udalit_dannoe_soobschenie'];
		if (this.MessageGridPanel.getGrid().getSelectionModel().getCount() > 1) {
			delMessage = lang['udalit_dannyie_soobscheniya'];
		}
		
		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) 
			{
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						params: params,
						url: '/?c=Messages&m=deleteMessages',
						callback: function(opt, success, resp)
						{
							if (success)
							{
								win.reloadTree();
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: delMessage,
			title: lang['vopros']
		});
	},
	
	setMessageActive: function(rIndex)
	{
		var record = this.MessageGridPanel.getGrid().getStore().getAt(rIndex);
		var form = this;
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrano_soobschenie']);
			return false;
		}
		var Message_id = record.get('Message_id');
		var MessageRecipient_id = record.get('MessageRecipient_id');
		if(record.get('UserRecipient_id') != parseInt(getGlobalOptions().pmuser_id))
			return false;
		
		if(record.get('Message_isFlag') == 1)
			var Message_isFlag = null;
		else
			var Message_isFlag = 1;
		
		Ext.Ajax.request(
		{
			url: '/?c=Messages&m=setMessageActive',
			params: 
			{ 
				Message_id: Message_id,
				Message_isFlag: Message_isFlag,
				MessageRecipient_id: MessageRecipient_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0].Message_isFlag == Message_isFlag)
					{
						record.set('Message_isFlag', Message_isFlag);
						record.commit();
					}
				}
			}
		});
	},
	
	openUploadDialog: function()
	{		
		var ud = this.UploadDialog;
		ud.show();
	},
	
	// Вызывается при успешном прикреплении файла
	uploadSuccess: function(dialog, data)
	{
		this.addFileInPanel(data);
	},
	
	/** Добавление файла в панель просмотра файлов 
	 *
	 */
	addFileInPanel: function (file) 
	{
		
		if (file && file.name && file.size)
		{
			file.id = file.name.replace(/\./ig,'_');
			var html = '<div style="float:left;height:18px;">';
			// вот эта часть должна добавляться только к создаваемому письму
			if(this.FilesPanel.action.inlist(['add','edit']))
			{
				html += '<b>'+file.name+'</b> ['+(file.size/1024).toFixed(2)+'Кб]';
				html = html + ' <a href="#" onClick="Ext.getCmp(&quot;swMessagesViewWindow&quot;).deleteFile(&quot;'+file.id+'&quot;, &quot;'+this.FilesPanel.action+'&quot;);">'+
				'<img title="Удалить" style="height: 12px; width: 12px; vertical-align: bottom;" src="/img/icons/delete16.png" /></a>';
			}
			else
			{
				html += '<a target="_blank" style="color: black; font-weight: bold;" href="'+file.url+'">'+file.name+'</a> ['+(file.size/1024).toFixed(2)+'Кб]';
			}
			html = html + '</div>';
			if(this.FilesPanel.findById(file.id) != null) // Проверяем существует ли элемент с таким ид=)
				return false;
			this.FilesPanel.add({id: ''+file.id, html: html, settings: file});
			if(this.FilesPanel.collapsed)
				this.FilesPanel.expand();
			this.setTitleFilesPanel();
			this.FilesPanel.syncSize();
			this.FilesPanel.ownerCt.syncSize();
			this.doLayout();
		}
	},
	/** Возвращает количество прикрепленных файлов 
	 *
	 */
	countFiles: function () 
	{
		return this.FilesPanel.items.items.length;
	},
	/** Изменение заголовка панели "Прикрепленные файлы"
	 *
	 */
	setTitleFilesPanel: function () 
	{
		var c = this.countFiles();
		if (c == 0) 
		{
			var title = '<span style="color: gray;">нет прикрепленных файлов</span>';
		} 
		else 
		{
			var tc = c.toString(), l = tc.length;
			var title = tc + ((tc.substring(l-1,1)=='1')?' файл':((tc.substring(l-1,1).inlist(['2','3','4']))?' файла': ' файлов'));
		}
		this.FilesPanel.setTitle(lang['prikreplennyie_faylyi']+title);
	},
	
	/** Удаление файла из панели просмотра файлов + удаление из прикрепленных
	 *
	 */
	deleteFile: function (id, action)
	{
		var win = this;
		var extItem = this.findById(''+id);
		if (extItem)
		{
			// фактическое удаление с диска (на стороне вебсервера надо проверять, может ли пользователь удалять эти файлы)
			if(action == 'edit')
			{
				Ext.Ajax.request({
					url: '/?c=Messages&m=deleteFile',
					params: extItem.settings,
					success: function(response, opts)
					{
						var obj = Ext.util.JSON.decode(response.responseText);
						if(!obj.success)
							return false;
						win.FilesPanel.remove(extItem, true);
						if (win.countFiles()==0) {
							win.FilesPanel.collapse();
						}
						win.setTitleFilesPanel();
						win.FilesPanel.syncSize();
						win.FilesPanel.ownerCt.syncSize();
						win.doLayout();
					}
				});
				return false;
			}
			
			// а потом уже удаление из панели
			this.FilesPanel.remove(extItem, true);
			if (this.countFiles()==0) {
				this.FilesPanel.collapse();
			}
			this.setTitleFilesPanel();
			this.FilesPanel.syncSize();
			this.FilesPanel.ownerCt.syncSize();
			this.doLayout();
		}
	},
	initComponent: function()
	{
		var cur_win = this;
		
		this.UploadDialog = new Ext.ux.UploadDialog.Dialog({
			modal: true,
			title: lang['prikreplenie_faylov'],
			url: '/?c=Messages&m=uploadMessageFiles',
			reset_on_hide: true,
			allow_close_on_upload: true,
			listeners: {
				uploadsuccess: function(dialog, filename, data)
				{
					cur_win.uploadSuccess(dialog, data);
				}
			},
			upload_autostart: false
		});
		
		
		this.FiltersPanel = new Ext.form.FormPanel({
			//title: 'Поиск сообщений',
			//collapsible: true,
			//collapsed: true,
			//titleCollapse: true,
			floatable: false,
			autoHeight: true,
			animCollapse: false,
			labelAlign: 'right',
			//plugins: [ Ext.ux.PanelCollapsedTitle ],
			defaults:
			{
				bodyStyle: 'background: #DFE8F6;'
			},
			region: 'north',
			frame: true,
			buttonAlign: 'left',
			/*buttons: [
				{
					handler: function()
					{
						this.ownerCt.doSearch();
					},
					iconCls: 'search16',
					text: BTN_FRMSEARCH
				}, {
					handler: function()
					{
						cur_win.FiltersPanel.getForm().reset();
					},
					iconCls: 'resetsearch16',
					text: BTN_FRMRESET
				}
			],*/
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					cur_win.doSearch();
				},
				stopEvent: true
			}],
			items: [
				{
					xtype: 'fieldset',
					style:'padding: 0px 3px 3px 6px;',
					autoHeight: true,
					listeners: {
						expand: function() {
							this.ownerCt.doLayout();
							cur_win.syncSize();
						},
						collapse: function() {
							cur_win.syncSize();
						}
					},
					collapsible: true,
					collapsed: true,
					title: lang['filtr'],
					bodyStyle: 'background: #DFE8F6;',
					items: [
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									bodyStyle: 'background: #DFE8F6;',
									labelWidth: 80,
									border: false,
									items: [
										{
											xtype: 'daterangefield',
											width: 170,
											listeners: {
												select: function(field, date)
												{
													this.setTitleFieldset();
												}.createDelegate(this)
											},
											name: 'MessagePeriodDate',
											fieldLabel: lang['period']
										}, {
											xtype: 'combo',
											width: 300,
											valueField: 'UserSend_id',
											enableKeyEvents: true,
											triggerAction: 'all',
											displayField: 'UserSend_Name',
											store: new Ext.data.Store({
												autoLoad: false,
												reader: new Ext.data.JsonReader({
													root: '',
													id: 'UserSend_id'
												}, [{
													mapping: 'UserSend_id',
													name: 'UserSend_id',
													type: 'int'
												},{
													mapping: 'UserSend_Name',
													name: 'UserSend_Name',
													type: 'string'
												}]),
												url: '/?c=Messages&m=getUsersSend'
											}),
											hiddenName: 'UserSend_id',
											editable: false,
											listeners: {
												select: function(c, r, i)
												{
													this.setTitleFieldset();
												}.createDelegate(this)
											},
											fieldLabel: lang['otpravitel']
										}
									]
								},
								{
									layout: 'form',
									bodyStyle: 'background: #DFE8F6;',
									labelWidth: 130,
									border: false,
									items: [
										{
											xtype: 'swnoticetypecombo',
											width: 300,
											listWidth: 320,
											listeners: {
												select: function(c, r, i)
												{
													this.setTitleFieldset();
												}.createDelegate(this)
											},
											fieldLabel: lang['vid_uvedomleniya']
										}, {
											xtype: 'combo',
											mode: 'local',
											hiddenName: 'Message_isRead',
											anchor: '100%',
											store: new Ext.data.SimpleStore(
											{
												key: 'Message_isRead',
												autoLoad: true,
												fields:
												[
													{name:'Message_isRead', type:'int'},
													{name:'Message_isRead_Name', type:'string'}
												],
												data: [[0, ''], [1,'Не прочитано'], [2,'Прочитано']]
											}),
											triggerAction: 'all',
											editable: false,
											listeners: {
												select: function(c, r, i)
												{
													this.setTitleFieldset();
												}.createDelegate(this)
											},
											displayField: 'Message_isRead_Name',
											valueField: 'Message_isRead',
											fieldLabel: lang['status_soobscheniya']
										}
									]
								}
							]
						},
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									items: [
										{
											xtype: 'button',
											handler: function()
											{
												this.doSearch();
											}.createDelegate(this),
											iconCls: 'search16',
											text: BTN_FRMSEARCH
										}
									]
								},
								{
									layout: 'form',
									style: 'margin-left: 10px;',
									items: [
										{
											xtype: 'button',
											handler: function()
											{
												cur_win.doReset();
											},
											iconCls: 'resetsearch16',
											text: BTN_FRMRESET
										}
									]
								}								
							]
						}
					]
				}
			]
		});
		
		this.FolderTypeView = new Ext.tree.TreePanel({
			autoHeight: true,
			rootVisible: false,
			autoLoad: false,
			enableDD: false,
			border: false,
			autoScroll: true,
			animate: false,
			root:
			{
				nodeType: 'async',
				text: lang['papki'],
				id: 'all',
				sAt: true,
				draggable: false,
				expandable: true
			},
			listeners:
			{
				click: function(node)
				{
					this.reloadMessageGridUnderFolderType(node);
				}.createDelegate(this)
			},
			loader: new Ext.tree.TreeLoader({								
				listeners:
				{
					/*beforeload: function(TreeLoader, node) 
					{
						log(node);
					},
					*/
					load: function(tree, node)
					{
						if (node.id = 'root' && node.hasChildNodes() == true) {
							node.expand();
							var sAt = node.sAt;
							if (node.firstChild) {
								if(node.selnode)
									node = node.getOwnerTree().getNodeById(node.selnode);
								else
									node = node.firstChild;
								node.sAt = sAt;
								node.select();
								node.getOwnerTree().fireEvent('click', node);
								changeCountMessages(node.attributes.count);
							}
						}
					}
				},
				dataUrl: '/?c=Messages&m=getMessagesFolder'
			})
		});
		
		this.LeftMessagesPanel = new Ext.Panel({
			region: 'west',
			title: lang['papki'],
			floatable: false,
			titleCollapse: true,
			animCollapse: false,
			split: true,
			collapsible: true,
			width: 300,
			maxWidth: 200,
			maxWidth: 400,
			items: [this.FolderTypeView]
		});
		
		this.MessageGridPanel = new sw.Promed.ViewFrame(
		{
			id: this.id + '_MessageGridPanel',
			enableAudit: false,
			region: 'center',
			selectionModel: 'multiselect',
			dataUrl: '/?c=Messages&m=loadMessagesGrid',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			onCellClick: function(grid, rIndex, cIndex){
				var flag_idx = grid.getColumnModel().findColumnIndex('Message_isFlag');
				if(cIndex == flag_idx)
				{
					cur_win.setMessageActive(rIndex);
				}
			},
			onRowSelect: function(sm) {
				if (sm.getCount() > 1) {
					this.ViewActions.action_openmessage.setDisabled(true);
				}
			},
			onRowDeSelect: function(sm) {
				if (sm.getCount() == 1) {
					this.ViewActions.action_openmessage.setDisabled(false);
				}
			},
			stringfields:
			[
				{name: 'Message_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'Message_pid', type: 'int', hidden: true},
				{name: 'MessageRecipient_id', type: 'int', hidden: true},
				{name: 'UserRecipient_id', type: 'int', hidden: true},
				{name: 'Message_Text', type: 'string', hidden: true},
				{name: 'UserSend_ID', header: '<img src="/img/icons/mail16.png" />', width: 30,
					renderer: function(v, p, rec)
					{
						var value = '';
						if(v != 0)
						{
							if(rec.get('Message_isRead') != 1 && rec.get('UserRecipient_id') == parseInt(getGlobalOptions().pmuser_id))
								value += '<img src="/img/icons/mail-unread16.png" />';
							else
								value += '<img src="/img/icons/mail16.png" />';
						}
						else
						{
							if(rec.get('Message_isRead') != 1 && rec.get('UserRecipient_id') == parseInt(getGlobalOptions().pmuser_id))
								value += '<img src="/img/icons/mail-auto-unread16.png" />';
							else
								value += '<img src="/img/icons/mail-auto16.png" />';
						}
						return value;
					}
				},
				{name: 'Message_Subject', header: lang['zagolovok'], id: 'autoexpand',
					renderer: function(v, p, rec){
						var Message_Text = rec.get('Message_Text');
						Message_Text = Message_Text.replace(/<\/?[^>]+>/g, '');
						if(Message_Text.length>100)
							Message_Text = Message_Text.substr(0, 100)+'...';
						
						var value = '<a style="color: black" onClick="Ext.getCmp(&quot;swMessagesViewWindow&quot;).openMessageTab();" href="#">'+v+'</a>';
						value += '<p style="font-weight: normal; font-size: 8pt;">'+Message_Text+'</p>';
						return value;
					}
				},
				{name: 'Message_isFlag', width: 30, tooltip: lang['otmetit_soobschenie'], header: '<img src="/img/icons/star16.png" />',
					renderer: function(v, p, rec)
					{
						if(v == 1)
							return '<img src="/img/icons/star16.png" />';
						else
							return '<img src="/img/icons/star-empty16.png" />';
					}
				},
				{name: 'Message_isRead', hidden: true},
				{name: 'Message_setDT', header: lang['data_i_vremya'], width: 150/*, renderer: Ext.util.Format.dateRenderer('m.d.Y H:i:s')*/},
				{name: 'PMUser_Login', header: lang['avtor'], width: 200, 
					renderer: function(v, p, rec){
						var pmUserName = rec.get('PMUser_Name');
						var dolgnost = rec.get('Dolgnost_Name');
						var LpuNick = rec.get('Lpu_Nick');
						var value = pmUserName+'<br />'+LpuNick+'<br />'+dolgnost;
						return value;
					}
				},
				{name: 'PMUser_id', hidden: true},
				{name: 'Lpu_Nick', hidden: true},
				{name: 'Dolgnost_Name', hidden: true},
				{name: 'PMUser_Name', hidden: true}
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', hidden: true, disabled: true}
			]
		});
		
		this.MessageGridPanel.ViewToolbar.on('render', function(vt){
			cur_win.MessageGridPanel.addActions({
				text: lang['novoe_soobschenie'],
				iconCls: 'message-new16',
				name: 'action_newmessage',
				tooltip: lang['novoe_soobschenie'],
				handler: function()
				{
					cur_win.createNewTab();
				}
			});
			cur_win.MessageGridPanel.addActions({
				text: lang['otkryit'],
				iconCls: 'message-open16',
				name: 'action_openmessage',
				tooltip: lang['otkryit'],
				handler: function()
				{
					cur_win.openMessageTab();
				}
			});
			cur_win.MessageGridPanel.addActions({
				text: lang['udalit'],
				iconCls: 'delete16',
				name: 'action_deletemessage',
				tooltip: lang['udalit'],
				handler: function()
				{
					cur_win.deleteMessages();
				}
			});
			cur_win.MessageGridPanel.addActions({
				text: lang['obnovit'],
				iconCls: 'refresh16',
				name: 'action_refreshmessages',
				tooltip: lang['obnovit'],
				handler: function()
				{
					var node = cur_win.FolderTypeView.getSelectionModel().getSelectedNode();
					//cur_win.reloadMessageGrid(node);
					cur_win.reloadTree(false, node.id);
				}
			});
			cur_win.MessageGridPanel.addActions({
				text: lang['adresnaya_kniga'],
				iconCls: 'address-book16',
				name: 'action_addressbook',
				tooltip: lang['adresnaya_kniga'],
				handler: function()
				{
					getWnd('swAddressBookEditWindow').show();
				}
			});
		});
		
		this.MessageGridPanel.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass: function (row, index)
			{
				var cls = '';
				if (
					row.get('Message_isRead') == '' &&
					row.get('UserRecipient_id') == parseInt(getGlobalOptions().pmuser_id)
				)
					cls = cls+'x-grid-rowselect ';
				return cls;
			}
		});
		
		this.CenterViewMessagesPanel = new Ext.TabPanel({
			region: 'center',
			headerCfg: {border: false},
			activeTab: 0,
			items: [
				{
					title: lang['vhodyaschie'],
					iconCls: 'inbox16',
					layout: 'border',
					id: 'chieftab',
					items: [this.MessageGridPanel]
				}
			]
		});
		
		this.CenterMessagesPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [ this.LeftMessagesPanel, this.CenterViewMessagesPanel]
		});
	
		Ext.apply(this,
		{
			layout: 'border',
			items: [
				this.FiltersPanel,
				this.CenterMessagesPanel
			],
			keys: [{
				key: Ext.EventObject.ESC,
				scope: cur_win.CenterViewMessagesPanel,
				fn: function(e) {
					var tab = cur_win.CenterViewMessagesPanel.getActiveTab();
					if(tab.id != 'chieftab')
						cur_win.closeTab(tab, true);
				},
				stopEvent: true
			}],
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
					handler   : function() {
						this.hide();
					}.createDelegate(this)
				}
			],
			defaults:
			{
				bodyStyle: 'background: #DFE8F6;'
			},
			listeners:
			{
				'hide': function(w)
				{
					w.doReset();
				}
			}
		});
		sw.Promed.swMessagesViewWindow.superclass.initComponent.apply(this, arguments);
	}
});