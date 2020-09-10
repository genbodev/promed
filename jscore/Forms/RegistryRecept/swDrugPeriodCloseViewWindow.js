/**
 * Справочник вакцин
 * 
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @version      06.05.2011
 */
/*NO PARSE JSON*/
sw.Promed.swDrugPeriodCloseViewWindow = Ext.extend(sw.Promed.BaseForm,
        {
            title: 'Закрытие периода',
            maximized: true,
            maximizable: true,
            shim: false,
            buttonAlign: "right",
            layout: 'border',
            codeRefresh: true,
            objectName: 'swDrugPeriodCloseViewWindow',
            id: 'swDrugPeriodCloseViewWindow',
	    region: 'north',
	    listeners: {
		'success': function(source, params) {console.log('success');
		    Ext.getCmp('PeriodCloseListGrid').ViewGridPanel.getStore().reload();
		}
	    },
            buttons:
                    [
		{
					id: this.id+'BtnSearch',
					style: "margin-left: 50px",
					text: 'Найти',
					iconCls: 'search16',
					handler: function()	{
					    Ext.getCmp('swDrugPeriodCloseViewWindow').doSearch();}
				}, {
					id: this.id+'BtnClear',
					text: 'Сбросить фильтр',
					iconCls: 'clear16',
					handler: function() {
					    Ext.getCmp('DrugPeriodCloseView_Apteka').reset();
                                            Ext.getCmp('DrugPeriodCloseView_DrugPeriodCloseType').reset();
					}
				},
                                 '-',
                        {
                            text: BTN_FRMHELP,
                            iconCls: 'help16',
                            // disabled  : true,
                            handler: function(button, event)
                            {
                                ShowHelp(this.ownerCt.title);
                            }
                        },
                        {
                            text: BTN_FRMCLOSE,
                            tabIndex: -1,
                            tooltip: 'Закрыть',
                            iconCls: 'cancel16',
                            handler: function()
                            {
                                this.ownerCt.hide();
                            }
                        }
                    ],
	    doEdit: function(action) {
		var rowSelected = Ext.getCmp('PeriodCloseListGrid').getGrid().getSelectionModel().getSelected();
		
		console.log('rowSelected ='); console.log(rowSelected);
		var  params = new Object();
		params.action = action;

		params.parent_id = 'swDrugPeriodCloseViewWindow';
		params.open_DT = rowSelected.get('DrugPeriodOpen_DT');
		params.close_DT = rowSelected.get('DrugPeriodClose_DT');
		params.DrugPeriodClose_Apteka = rowSelected.get('Org_Name');
		params.DrugPeriodClose_Sign = rowSelected.get('DrugPeriodClose_Sign');
		if (action == 'edit') {
		    params.DrugPeriodClose_id = rowSelected.get('DrugPeriodClose_id');
		}
		    
		getWnd('swDrugPeriodCloseEditWindow').show(params);
        },
	doSearch: function() {
	    var form = this;
	    var filter_form = Ext.getCmp('DrugPeriodCloseView_FilterPanel').getForm();
	    var params = filter_form.getValues();
	    params.DrugPeriodCloseType_id =  Ext.getCmp('DrugPeriodCloseView_DrugPeriodCloseType').getValue();
	    console.log(params);
	    form.PeriodCloseListGrid.getGrid().getStore().load({
                params: params
	    })
	},
            initComponent: function() {
                frms = this;
             

		this.SearchParamsPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
		    owner: frms,
		    labelWidth: 110,
		    autoHeight: true,
		    id: 'DrugPeriodCloseView_FilterPanel',
		    filter: {
			title: lang['filtryi'],
			collapsed: true,
			id: 'DrugTurnover_rr',
			layout: 'form',
			items: [  
				    {layout: 'form',
					labelWidth: 150,
					items: [
					     {
					       fieldLabel: 'Наименование аптеки', //lang['torg_naimenovanie'],
					       width: 300,
					       id: 'DrugPeriodCloseView_Apteka',
					       xtype: 'textfield'
					   },
					]
			    },
			    {
				layout: 'column',
				items: [
				    {layout: 'form',
					labelWidth: 150,
					 items: [
					      {
						xtype: 'amm_DrugPeriodCloseTypeCombo',
						fieldLabel: 'Статус', //lang['statya_rashoda'],
						name: 'DrugPeriodCloseType_id',
						id: 'DrugPeriodCloseView_DrugPeriodCloseType',
						width: 200
					    }
					 ]
				    },
				]
			    }

			]
		    }
         }),		
		this.PeriodCloseListGrid = new sw.Promed.ViewFrame(
                                {
//                        title:'Список вакцин',
                                    id: 'PeriodCloseListGrid', 
                                    object: 'PeriodCloseListGrid',
                                     dataUrl: '/?c=RegistryRecept&m=loadDrugPeriodCloseList',
                                    region: 'center',
                                    toolbar: true,
                                    autoLoadData: true,
				    root: 'data',
                                    cls: 'txtwrap',
                                    stringfields: [
                                        {name: 'DrugPeriodClose_id', type: 'int', header: 'ID', key: true},
                                        {name: 'Org_id', type: 'int', header: 'Org_id', hidden: true},
                                        {name: 'Org_Name', type: 'string', header: 'Наименование аптеки', //id: 'autoexpand', 
					    width: 150},
					 {name: 'DrugPeriodOpen_DT', type: 'date', header: 'Дата открытия периода', width: 200},
                                        {name: 'DrugPeriodClose_DT', type: 'date', header: 'Дата закрытия', width: 200},
                                        {name: 'DrugPeriodClose_Sign', type: 'int', header: 'DrugPeriodClose_Sign', hidden: true}
                                        //{name: 'DrugPeriodClose_Name', type: 'string', header: 'Статус', width: 150}
                                        
                                    ],
                                    actions: [
					{
					    name: 'action_add', 
					    text: 'Изменить все',
					    tooltip: 'Изменить все',
					    handler: function() {
						Ext.getCmp('swDrugPeriodCloseViewWindow').doEdit('add');
					    }},
					{name: 'action_view', hidden: true},
					{
					    name: 'action_edit', 
					     handler: function() {
						Ext.getCmp('swDrugPeriodCloseViewWindow').doEdit('edit');
					    }},
					{name: 'action_delete', hidden: true}
                                                                             
                                    ]                                         
                                });
				
		var params = new Object();
                Ext.apply(this, {
                    items: [
                        frms.SearchParamsPanel,
			frms.PeriodCloseListGrid			
                    ]
                });

                sw.Promed.swDrugPeriodCloseViewWindow.superclass.initComponent.apply(this, arguments);
            },
            show: function() {


                sw.Promed.swDrugPeriodCloseViewWindow.superclass.show.apply(this, arguments);

            }

        }

);

