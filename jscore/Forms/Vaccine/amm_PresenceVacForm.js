/**
* amm_PresenceVacForm - окно просмотра имеющихся вакцин, серий
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       
* @version      2012.08.15
* @comment      
*/

sw.Promed.amm_PresenceVacForm = Ext.extend(sw.Promed.BaseForm, {
	title: "Наличие вакцин",
	border: false,
	width: 720,
	height: 400,
	//        maximized: true,
	maximized: true,
        maximizable: true,        
	layout:'fit',
	codeRefresh: true,
	closeAction: 'hide',
	objectName: 'amm_PresenceVacForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_PresenceVacForm.js',
	onHide: Ext.emptyFn,
        tabIndex: TABINDEX_PRESENCEVAC + 1,
		
        initComponent: function() {
		 
                //Формирование глобального объекта Task#18011
                  getGlobalRegistryData = {
                         Lpu_id            :0,
                         RegistryType_id   :0,
                         RegistryStatus_id : 0
                  }
                    
		var params = new Object();
		var frms = this;
		frms.ammVacPresenceEditWindow = getWnd('amm_PresenceVacEditForm');
		this.ViewFrameVacPresence = new sw.Promed.ViewFrame(
		{
			id: 'amm_PresenceVacForm',
			editformclassname: 'amm_PresenceVacEditForm',
			dataUrl: '/?c=VaccineCtrl&m=GetVacPresence',
			region: 'center',
                        //autoLoadData: false,
			//                toolbar: false,
			setReadOnly: false,
			//      autoLoadData: false,
			cls: 'txtwrap',
			//      layout: 'border',
			height: 300,
			//                 paging: true,
			totalProperty: 'totalCount',  
			stringfields:
			[	   							
			{name: 'VacPresence_id', type: 'int', header: 'ID', key: true},
                        {name: 'Vaccine_id', type: 'int', header: 'Vaccine_id', hidden: true},
                        {name: 'Vaccine_Name', type: 'string', header: 'Наименование вакцины', id: 'autoexpand', width: 500},
                        {name: 'Seria', type: 'string', header: 'Серия вакцины', width: 130},
                        {name: 'Period', type: 'string', header: 'Срок годности', width: 100},
                        {name: 'Manufacturer', type: 'string', header: 'Изготовитель', width: 150},
                        {name: 'toHave', type: 'int', header: 'Наличие', hidden: true},
                        {name: 'Name_toHave', type: 'string', header: 'Наличие', width: 100}
			],
			
			actions: [
			{
				name:'action_add', 
				handler: function()
				{
					params = new Object();
					params.action = 'add';
					params.VacPresence_Period = new Date();
					getWnd('amm_PresenceVacEditForm').show(params);
				}
			},
			{
				name:'action_edit', 
				handler: function()
				{
					//                             Ext.Msg.alert('Внимание', Ext.getCmp('amm_VacPresence').getGrid().getSelectionModel().row.number());
					var record = this.findById('amm_PresenceVacForm').getGrid().getSelectionModel().getSelected();
					params = new Object();
					params.action = 'edit';
					//                             params.row = this.findById('amm_VacPresence').getGrid().getSelectionModel().get
					params.readonly = false;
					params.VacPresence_id = this.findById('amm_PresenceVacForm').getGrid().getSelectionModel().getSelected().get('VacPresence_id');
					params.Vaccine_id = record.get('Vaccine_id');
					params.VacPresence_Seria = record.get('Seria');
					params.VacPresence_Period = record.get('Period');
					params.VacPresence_Manufacturer = record.get('Manufacturer');
					params.VacPresence_toHave = record.get('toHave');
										

					//                            frms.ammVacPresenceEditWindow.show(params)
					getWnd('amm_PresenceVacEditForm').show(params);

				}.createDelegate(this)
			},   
			{
				name:'action_view', 
				handler: function()
				{
					//                             Ext.Msg.alert('Внимание', Ext.getCmp('amm_VacPresence').getGrid().getSelectionModel().row.number());
					var record = this.findById('amm_PresenceVacForm').getGrid().getSelectionModel().getSelected();
					params = new Object();
					params.action = 'view';
					//                             params.row = this.findById('amm_VacPresence').getGrid().getSelectionModel().get
					params.readonly = false;
					params.VacPresence_id = this.findById('amm_PresenceVacForm').getGrid().getSelectionModel().getSelected().get('VacPresence_id');
					params.Vaccine_id = record.get('Vaccine_id');
					params.VacPresence_Seria = record.get('Seria');
					params.VacPresence_Period = record.get('Period');
					params.VacPresence_Manufacturer = record.get('Manufacturer');
					params.VacPresence_toHave = record.get('toHave');
										

					//                            frms.ammVacPresenceEditWindow.show(params)
					getWnd('amm_PresenceVacEditForm').show(params);

				}.createDelegate(this)
								
			},

			{
				name:'action_delete', 
				hidden: true
			}
			],
			
			listeners: {
				'success': function(record_id) {
                                        Ext.getCmp('amm_PresenceVacForm').ViewGridPanel.getStore().reload(
                                             {callback: function() {
                                                var count = Ext.getCmp('amm_PresenceVacForm').getCount();
                                                if (Ext.getCmp('amm_PresenceVacForm').getCount() > 0) {
                                                          Ext.getCmp('amm_PresenceVacForm').getGrid().getSelectionModel().selectFirstRow();
                                                                var i = 0;
                                                                var row = -1;
                                                                var record = new Object();
                                                                  while  (i <= count) {
                                                                          record = Ext.getCmp('amm_PresenceVacForm').getGrid().getSelectionModel().getSelected();
                                                                          if (record.get('VacPresence_id') == record_id){
                                                                                row = i;
                                                                                  i = Ext.getCmp('amm_PresenceVacForm').getCount();
                                                                          }

                                                                          else
                                                                                 Ext.getCmp('amm_PresenceVacForm').getGrid().getSelectionModel().selectNext();
                                                                          i++;
                                                                  }
								  
							if (row >= 0){
							  Ext.getCmp('amm_PresenceVacForm').getGrid().getSelectionModel().selectRow(row);
						  }
						}
                                            }
                                        }
                                    );
                               }
                           }
		});
                
                //Интеграция фильтра к Grid
		columnsFilter = ['Vaccine_Name','Seria','Manufacturer','Name_toHave'];
		configParams = {url : '/?c=VaccineCtrlFilterGrid&m=GetVacPresenceFilter'} 
                //console.log('ViewFrameVacPresence', Ext.getCmp('amm_PresenceVacForm').getGrid());
               _addFilterToGrid(this.ViewFrameVacPresence, columnsFilter, configParams);
	
		Ext.apply(this, {
			buttons: [
			{
				text : 'Сбросить фильтр',  //BTN_FRMRESET,
				iconCls: 'resetsearch16',
				handler : function(button, event) {
                                    Ext.getCmp('amm_PresenceVacForm').ViewGridModel.grid.store.baseParams.Filter = '{}';
                                    Ext.getCmp('amm_PresenceVacForm').ViewGridPanel.getStore().reload();
                                    if (Ext.getCmp('amm_PresenceVacForm').FilterSettings != undefined) {
                                        var obj = Ext.getCmp('amm_PresenceVacForm').FilterSettings;
                                        for (var col in obj) {
                                            Ext.getCmp('amm_PresenceVacForm').FilterSettings[col] = false;
                                        }
                                    }
                                }
                        },       
                        {
				text: '-'
			},
						HelpButton(this, TABINDEX_PRESENCEVAC + 2),
						{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'PresenceVac_CancelButton',
				onTabAction: function () {
					this.findById('EPLSIF_EvnVizitPL_setDate').focus(true, 100);
				}.createDelegate(this),
				tabIndex: TABINDEX_PRESENCEVAC + 3,
				text: '<u>З</u>акрыть',
								onTabAction: function () {
								Ext.getCmp('amm_PresenceVacForm').focus();
							}.createDelegate(this)
									
			},
						
			],
			items : [
			this.ViewFrameVacPresence
			]
		}
		);
			  
		sw.Promed.amm_PresenceVacForm.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
		sw.Promed.amm_PresenceVacForm.superclass.show.apply(this, arguments);
		Ext.getCmp('amm_PresenceVacForm').getGrid().getSelectionModel().selectRow(1);
		Ext.getCmp('PresenceVac_CancelButton').focus(true, 50);
		//amm_PresenceVacForm	  
		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		this.findById('amm_PresenceVacForm').setActionDisabled('action_add', this.viewOnly);
		this.findById('amm_PresenceVacForm').setActionDisabled('action_edit', this.viewOnly);
	}          

});

