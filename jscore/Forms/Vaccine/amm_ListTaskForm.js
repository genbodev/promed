/**
* amm_ListTaskForm - окно просмотра Списка заданий
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       
* @version       
* @comment      Префикс для id компонентов regv (amm_JournalViewWindow)
*/

sw.Promed.amm_ListTaskForm = Ext.extend(sw.Promed.BaseForm, {
	title: "Список заданий",
	border: false,
	width: 725,
	height: 500,
	maximized: true,
	maximizable: true,        
	codeRefresh: true,
	closeAction: 'hide',
	objectName: 'amm_ListTaskForm',
	id: 'amm_ListTaskForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_ListTaskForm.js',
	onHide: Ext.emptyFn,
	listeners: {
		'success': function(source, params) {
				Ext.getCmp('amm_ViewFrameVacListTasks').initGrid();
			}
	},
	initComponent: function() {
		this.vacSearchForm = new Ext.form.FormPanel({
			id : "SearchTaskForm",
			labelWidth : 150,
			frame : false,
			border: false,
			bodyStyle:'border-bottom-width: 1px;',
			region: 'north',
			layout: 'form',
			autoHeight : true,
			items : [{
				region: 'north',
				layout : "form",
				autoHeight: true,
				labelWidth : 180,
				labelAlign : "right",
				items : [{
					height : 10,
					border : false,
					cls: 'tg-label'
				},
				{
					name : "Date_View",
					id: 'Date_View',
					xtype : "daterangefield",
					layout: 'form',
					width : 170,
					Height : 70,
					fieldLabel : '   Дата постановки задания',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex : TABINDEX_LISTTASKFORMVAC + 1,
                    listeners: {
						'select': function ()
						{
							Ext.getCmp('amm_ViewFrameVacListTasks').initGrid();
						}.createDelegate(this),
						'blur': function ()
						{
							Ext.getCmp('amm_ViewFrameVacListTasks').initGrid();
						}.createDelegate(this)
					}
                }]
			}],
			keys: [{
				key: 13,
				fn: function() {
					sw.Promed.vac.utils.consoleLog('key 13');
					},
				stopEvent: true
			}]
		})
                      
        this.ViewFrameVacListTasks = new sw.Promed.ViewFrame(
		{
			id: 'amm_ViewFrameVacListTasks',
			dataUrl: '/?c=Vaccine_List&m=GetVacListTasks',
			toolbar: true,
			setReadOnly: false,
			cls: 'txtwrap',
			paging: true,
			totalProperty: 'totalCount',  
			layout:'form',
			region: 'center',
			buttonAlign : "right",
			autoLoadData: false,
			height: 500,
			autowith: true,
			tabIndex: TABINDEX_LISTTASKFORMVAC + 2,
			stringfields:
			[	   							
			{
				name: 'vacFormPlanRun_id',  
				type: 'int', 
				header: 'ID', 
				key: true
			},
			{
				name: 'Plan_begDT',  
				type: 'string', 
				header: 'Дата начала <br>периода', 
				width: 90
			},
			{
				name: 'Plan_endDT',  
				type: 'string', 
				header: 'Дата <br>окончания <br>периода', 
				width: 90
			},
			{
				name: 'FormPlan_runDT',  
				type: 'string', 
				header: 'Дата постановки задания', 
				width: 150
			},
			{
				name: 'FormPlan_begDT',  
				type: 'string', 
				header: 'Начало обработки', 
				width: 120
			},
			{
				name: 'FormPlan_endDT',  
				type: 'string', 
				header: 'Окончание обработки', 
				width: 130
			},         
						{
				name: 'Lpu_Nick',  
				type: 'string', 
				header: 'ЛПУ', 
				width: 70
			},   
						{
				name: 'Mode_Name',  
				type: 'string', 
				header: 'Параметры', 
				width: 200
			},
			{
				name: 'RecStatus',  
				type: 'int', 
				header: 'Идентификатор статуса', 
				width: 50, 
				hidden: true
			},
			{
				name: 'RecStatus_Name',  
				type: 'string', 
				header: 'Статус', 
				width: 120
			},
			{
				name: 'Kol',  
				type: 'string', 
				header: 'Коли-<br>чество', 
				width: 60
			},
			{
				name: 'Comment',  
				type: 'string', 
				header: 'Комментарий',  
				id: 'autoexpand'
			}
			],
			onRowSelect: function(sm, index, record) {
				var tabObj = this.ViewFrameVacListTasks;

				if ( typeof record == 'object' && record.get('RecStatus') == 0 ) {
					tabObj.getAction('action_delete').setDisabled(false);
					sw.Promed.vac.utils.consoleLog('owSelected.data.RecStatus = 2');
				}
				else {
					tabObj.getAction('action_delete').setDisabled(true);
					 sw.Promed.vac.utils.consoleLog('owSelected.data.RecStatus = 1');
				}
			}.createDelegate(this),
			actions:
			[
			{
				name:'action_add', 
				hidden: true
			},
			{
				name:'action_edit', 
				hidden: true
			},
			{
				name:'action_view', 
				hidden: true
			},
			{
				name:'action_refresh',
				handler: function()
					{
						var viewframe = this.findById('amm_ViewFrameVacListTasks');
						viewframe.initGrid();
						viewframe.getAction('action_delete').setDisabled (1);
					}.createDelegate(this)
			},
			{
				name:'action_delete',
				text: 'Снять задание',
				tooltip: 'Снять задание',
				handler: function()
					{
						var rowSelected = this.findById('amm_ViewFrameVacListTasks').getGrid().getSelectionModel().getSelected();
						if (rowSelected.data.RecStatus == 0) {
							sw.swMsg.show({
								icon: Ext.MessageBox.QUESTION,
								msg: 'Вы хотите снять задание?',
								title: 'Подтверждение',
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj)
								{
									if ('yes' == buttonId)
									{
										var params = {
											'vacFormPlanRun_id': rowSelected.data.vacFormPlanRun_id
										};
										Ext.Ajax.request({
											url: '/?c=Vaccine_List&m=DelVacRecTasks',
											method: 'POST',
											params: params,
											success: function(response, opts) {
												sw.Promed.vac.utils.consoleLog(response);
												if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
												Ext.getCmp('amm_ListTaskForm').fireEvent('success', '', { } );
												}
																							}
										});
										Ext.Msg.alert('Сообщение',  'Задание снято!');
									}
								}.createDelegate(this)
							});
						}
						else {
							Ext.getCmp('amm_ViewFrameVacListTasks').getAction('action_delete').setDisabled (1);
						}
					}.createDelegate(this)
			}
			],
			
			onLoadData: function()
			{

			},
			
			initGrid: function()
			{
				var  params = new Object();
				var vacSearchForm = Ext.getCmp('SearchTaskForm');
				if (vacSearchForm.getForm().isValid() ) {
					var post = vacSearchForm.getForm().getValues();
					if (post.Date_View == undefined) {
						post.Date_View = '';
                    }
                    sw.Promed.vac.utils.consoleLog('post');
                    sw.Promed.vac.utils.consoleLog(post);
                    sw.Promed.vac.utils.consoleLog(Ext.getCmp('Date_View'));
                }
                params.Date_View = post.Date_View;
                params.Lpu_id =getGlobalOptions().lpu_id;
				Ext.getCmp('amm_ViewFrameVacListTasks').ViewGridPanel.getStore().baseParams = params;
				Ext.getCmp('amm_ViewFrameVacListTasks').ViewGridPanel.getStore().reload({
                	callback: function() {
						Ext.getCmp('ListTaskForm_CancelButton').focus(true, 50);
                    }.createDelegate(this)
				});
            }
        });
		
		this.ViewFrameVacListTasks.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				switch(row.get('RecStatus')){
					case 1:
						cls = 'x-grid-rowbold ';
					break;
					case 2:
						cls = 'x-grid-panel';
					break;
					case 3:
						cls = cls+'x-grid-rowred ';
					break;
					case 4:
						cls =  'x-grid-rowdeleted';
					break;
					default:
						cls = 'x-grid-rowblue ';
					break;
				}
				/*if (row.get('RecStatus') == 3)
					cls = cls+'x-grid-rowred ';
				else if (row.get('RecStatus') == 1)
					cls = 'x-grid-rowbold ';
				else if (row.get('RecStatus') == 2)
                	cls = 'x-grid-panel';
                else if (row.get('RecStatus') == 4)
					cls =  'x-grid-rowdeleted';
				else    
					cls = 'x-grid-rowblue ';*/
				return cls;
			}
		});

		Ext.apply(this, {
			lbodyBorder : true,
			layout : "border",
			cls: 'tg-label',
			buttons: [
			{
				text : BTN_FRMSEARCH,
				iconCls: 'search16',
				id: 'ListTaskForm_Search',
				handler: function() {
					Ext.getCmp('amm_ViewFrameVacListTasks').initGrid();
				}.createDelegate(this),
				tabIndex : TABINDEX_LISTTASKFORMVAC + 4
			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_LISTTASKFORMVAC + 3),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'ListTaskForm_CancelButton',
				onTabAction: function () {
					Ext.getCmp('Date_View').focus(true, 50);
				}.createDelegate(this),
				tabIndex: TABINDEX_LISTTASKFORMVAC + 5,
				text: '<u>З</u>акрыть'
			}],
			items : [
				this.vacSearchForm,
				this.ViewFrameVacListTasks
			]
		});
		 
		sw.Promed.amm_ListTaskForm.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
		sw.Promed.amm_ListTaskForm.superclass.show.apply(this, arguments);
		var dt = new Date();
		var dt2 = new Date();
		Ext.getCmp('Date_View').setValue(dt.format('d.m.Y') + ' - ' + dt2.format('d.m.Y'));
		Ext.getCmp('amm_ViewFrameVacListTasks').initGrid();
		Ext.getCmp('Date_View').focus(true, 50);
		//Ext.getCmp('amm_ViewFrameVacListTasks').getGrid().on('cellclick',Ext.getCmp('amm_ViewFrameVacListTasks').updateContextMenu);
	}
});

