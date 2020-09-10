/**
* swEvnPLWOWStream - окно поточного ввода талонов углубленных обследований ВОВ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей
* @version      14.03.2010
* @comment      Префикс для id компонентов evnplwst (EvnPLWOWStreamWindow)
                firstTabIndex: 17300 - начиная с 17300 tabindex полей 
*/

sw.Promed.EvnPLWOWStreamWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'EvnPLWOWStreamWindow',
	title: lang['obsledovaniya_vov_potochnyiy_vvod'], 
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 17300,
	listeners: 
	{
		beforeshow: function()
		{
			//
		}
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	setBegDateTime: function(first) 
	{
		form = this;
		Ext.Ajax.request(
		{
			callback: function(opt, success, response) 
			{
				if (success && response.responseText != '') 
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					form.begDate = response_obj.begDate;
					form.begTime = response_obj.begTime;
					form.findById('evnplwstStreamInformationForm').findById('evnplwstpmUser_Name').setValue(response_obj.pmUser_Name);
					form.findById('evnplwstStreamInformationForm').findById('evnplwstStream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					form.SearchGrid.setParam('begDate',response_obj.begDate);
					form.SearchGrid.setParam('begTime',response_obj.begTime);
					if (first)
					{
						form.SearchGrid.loadData();
					}
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	show: function() 
	{
		sw.Promed.EvnPLWOWStreamWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();

		this.center();
		this.maximize();
		this.SearchGrid.removeAll();
		this.setBegDateTime(true);
		this.getLoadMask().hide();
		
	},
	onOpenForm: function(person_data)
	{
		this.SearchGrid.setParam('Person_id',  person_data.Person_id, false);
		this.SearchGrid.setParam('PersonEvn_id',  person_data.PersonEvn_id, false);
		this.SearchGrid.setParam('Server_id',  person_data.Server_id, false);
		getWnd('swPersonSearchWindow').hide();
		this.SearchGrid.run_function_add = false;
		this.SearchGrid.runAction('action_add');
		this.getLoadMask().hide();
	},
	onCheckPerson: function (person_data)
	{
		var form = this;
		form.getLoadMask().show();
		Ext.Ajax.request(
		{
			url: '/?c=EvnPLWOW&m=checkDoublePerson',
			params: 
			{
				Person_id: person_data.Person_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.Error_Msg)
					{
						if (result.success==true)
						{
							sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								fn: function() 
								{
									form.onOpenForm(person_data);
								},
								icon: Ext.Msg.WARNING,
								title: lang['vnimanie'],
								msg: result.Error_Msg
							});
						}
						else
						{
							sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.ERROR,
								title: lang['oshibka'],
								msg: result.Error_Msg
							});
							form.getLoadMask().hide();
						}
					}
					else 
					{
						form.onOpenForm(person_data);
					}
				}
			}
		});
	},
	addEvnPLWOW: function()
	{
		var win = this;
		getWnd('swPersonSearchWindow').show(
		{
			onClose: function() 
			{
				if (win.SearchGrid.getGrid().getSelectionModel().getSelected()) 
				{
					win.SearchGrid.getGrid().getView().focusRow(win.SearchGrid.getGrid().getStore().indexOf(win.SearchGrid.getGrid().getSelectionModel().getSelected()));
				}
				else 
				{
					win.SearchGrid.focus();
				}
			}.createDelegate(this),
			onSelect: function(person_data) 
			{
				win.onCheckPerson(person_data);
			},
			searchMode: 'wow'
		});
	},
	keys: 
	[{
		fn: function(inp, e) 
		{
			var win = Ext.getCmp('EvnPLWOWStreamWindow');
			switch (e.getKey()) 
			{
				case Ext.EventObject.INSERT:
					win.addEvnPLWOW();
					break;
			}
		},
		key: [Ext.EventObject.INSERT],
		stopEvent: true
	}],
	initComponent: function() 
	{
		var form = this;
		this.UserPanel = new Ext.form.FormPanel(
		{
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			id: 'evnplwstStreamInformationForm',
			items: 
			[{
				disabled: true,
				fieldLabel: lang['polzovatel'],
				id: 'evnplwstpmUser_Name',
				width: 380,
				xtype: 'textfield'
			}, 
			{
				disabled: true,
				fieldLabel: lang['data_nachala_vvoda'],
				id: 'evnplwstStream_begDateTime',
				width: 130,
				xtype: 'textfield'
			}],
			labelAlign: 'right',
			labelWidth: 120
		});
	
		this.SearchGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'SearchGrid',
			region: 'center',
			height: 203,
			title:lang['obsledovaniya_vov_spisok'],
			object: 'EvnPLWOW',
			editformclassname: 'EvnPLWOWEditWindow',
			dataUrl: '/?c=EvnPLWOW&m=loadEvnPLWOWStreamList',
			/*
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			*/
			autoLoadData: false,
			stringfields:
			[
				{ name: 'EvnPLWOW_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ id: 'autoexpand',  name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 120 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'] },
				{ name: 'PrivilegeTypeWOW_Name', header: lang['kategoriya'], width: 250 },
				{ name: 'EvnPLWOW_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'] },
				{ name: 'EvnPLWOW_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'] },
				{ name: 'EvnPLWOW_VizitCount', type: 'int', header: lang['posescheniy'] },
				{ name: 'EvnPLWOW_IsFinish', type: 'string', header: lang['zakonch'], width:50 }
				
			],
			actions:
			[
				{name:'action_add', func:  function() {this.addEvnPLWOW();}.createDelegate(this)},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'}
			],
			afterSaveEditForm: function(RegistryQueue_id, records)
			{
				var form = Ext.getCmp('EvnPLWOWStreamWindow');
			},
			onLoadData: function()
			{
				// Ну вот 
			},
			onRowSelect: function(sm,index,record)
			{
				//log(this.id+'.onRowSelect');
				var form = Ext.getCmp('EvnPLWOWStreamWindow');
			}
		});
		
		Ext.apply(this, 
		{
			layout:'border',
			defaults: {split: true},
			buttons: 
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: 
			[form.UserPanel,
			{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout:'border',
				id: 'evnplwstRightPanel',
				items: [form.SearchGrid]
			}]
		});
		sw.Promed.EvnPLWOWStreamWindow.superclass.initComponent.apply(this, arguments);
	}
});