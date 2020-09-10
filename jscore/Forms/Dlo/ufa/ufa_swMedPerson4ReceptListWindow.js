/**
* 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Нигматуллин Тагир
* @version      ноябрь 2016  swMedPerson4ReceptListWindow

*/

sw.Promed.swMedPerson4ReceptListWindow = Ext.extend(sw.Promed.BaseForm, {

        title: "Регистр врачей ЛЛО",
        id: 'MedPerson4ReceptListWindow',
        border: false,
        width: 800,
        height: 500,
        maximized: true,
	maximizable: true,        
        layout:'border',
        resizable: true,
        codeRefresh: true,
	adminLLO: isUserGroup(['DLOAccess']),
        listeners: {
		hide: function() {
			this.onHide();
		}
	    },
	showLink: function (resp_obj){
	     sw.swMsg.alert('Завершено', 'Экспорт успешно завершен. <a href="'+resp_obj.filename+'" target="blank" title="Щелкните, чтобы сохранить результаты на локальный диск">Скачать</a>');
	 },
        doSearch: function() {
            var form = this;
            var params = new Object();

            params.Search_Fio = Ext.getCmp('MedPersDLO_SearchFio').getValue();
            params.PostMed_id =  Ext.getCmp('MedPersDLO_PostMed').getValue();
            params.WorkType_id = Ext.getCmp('MedPersDLO_WorkType').getValue();
            params.StatusType_id =  Ext.getCmp('MedPersDLO_StatusType').getValue();
            params.TabCode =  Ext.getCmp('MedPersDLO_SearchTab').getValue(); 
	    params.CodeDLO =  Ext.getCmp('MedPersDLO_SearchCode').getValue();
	    if (!form.adminLLO) {
		params.Lpu_id = getGlobalOptions().lpu_id;
		} 
	    else {
		params.Lpu_id = Ext.getCmp('MedPersDLO_Lpu').getValue();
	    }
	    
	    params.RegistryDloON = '';
	    params.WorkPlace4DloApplyStatus_id = '';
	    params.WorkPlace4DloApplyTYpe_id = '';
	    
	    
	     $StatusType_id =  Ext.getCmp('MedPersDLO_StatusType').getValue();
		
		if (!form.adminLLO) { 
			var flag =  $StatusType_id != 20;
			if (!flag && (params.Search_Fio != '' || params.TabCode != '' || params.CodeDLO != '' || params.PostMed_id != '' || params.WorkType_id))
				flag = true;
			Ext.getCmp('MedPersDLO_Print').setDisabled(flag);
		}
		switch($StatusType_id) {
			case 10: // 'Имеющие право на выписку рецептов ЛЛО
				 params.RegistryDloON = 2;
			break;
			 
			case 20: // Заявленные на изменения в регистре врачей ЛЛО
				params.WorkPlace4DloApplyStatus_id = 0;
				//alert(params.WorkPlace4DloApplyStatus_id);
			break;
			 
			case 21: // Заявленные на включение в регистр врачей ЛЛО
				params.WorkPlace4DloApplyStatus_id = 0;
				params.WorkPlace4DloApplyTYpe_id = 1;
			break;
				
			case 22: // Заявленные на исключение из регистра врачей ЛЛО
				params.WorkPlace4DloApplyStatus_id = 0;
				params.WorkPlace4DloApplyTYpe_id = 2;
			break;
			
			case 30:// Не имеющие право выписку рецептов ЛЛО
				params.RegistryDloON = 1;
				// params.Query = ' and (msf.WorkData_dlobegDate is null or msf.WorkData_dlobegDate > getDate() or msf.WorkData_dloendDate < getDate()) ';    
			break;
	     }
	     
	    params.start = 0;
	    params.limit = Ext.getCmp('MedPerson4rec_Grid').pageSize;
	     

	    console.log('params = '); console.log(params);
	    
	    form.StaffPanel.getGrid().getStore().load({
		    params: params,
		    callback: function(){
			form.StaffPanel.updateContextMenu();
			Ext.getCmp('MedPersDLO_SearchFio').focus(true, 100);
		    }
		});
		form.StaffPanel.getGrid().on(
		    'cellclick',
		    form.StaffPanel.updateContextMenu
		);
		form.StaffPanel.getGrid().on(
		    'cellcontextmenu',
		    form.StaffPanel.updateContextMenu
		);
    },
    doSave: function(params, record) {
	var form = this;
	if (form.adminLLO) { // Для МИАЦ (утверждение заявок врачей ЛЛО)
	    Ext.Ajax.request({
		url: '/?c=MedPersonal&m=treatmentWorkPlace4DloApply',
		method: 'POST',
		params:params,
		success: function(response, opts) {		
		    record.set('recStatus_id', params.recStatus_id); 
		    record.set('WorkPlace4DloApplyTYpe_id', params.WorkPlace4DloApplyTYpe_id); 
		    record.set('WorkPlace4DloApplyTYpe_Name', params.WorkPlace4DloApplyTYpe_Name);
		    record.commit();
		    Ext.getCmp('MedPerson4rec_Grid').getGrid().getSelectionModel().selectRecords(record);
		}
	    })
	    
	}
	else {  // Для ЛПУ (подача заявки)
	    Ext.Ajax.request({
		url: '/?c=MedPersonal&m=saveWorkPlace4DloApply',
		method: 'POST',
		params:params,
		success: function(response, opts) {		
		    record.set('recStatus_id', params.recStatus_id); 
		    record.set('WorkPlace4DloApplyTYpe_id', params.WorkPlace4DloApplyTYpe_id); 
		    record.set('WorkPlace4DloApplyTYpe_Name', params.WorkPlace4DloApplyTYpe_Name);
		    record.commit();
		    Ext.getCmp('MedPerson4rec_Grid').getGrid().getSelectionModel().selectRecords(record);
		}
	    })
	}						
    },

     initComponent: function() {
        var form = this;
           
         this.SearchParamsPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
            owner: form,
            labelWidth: 110,
            autoHeight: true,
            id: 'MedPersDLO_FilterPanel',
            filter: {
                title: lang['filtryi'],
                collapsed: false,
                id: 'DrugTurnover_rr',
                layout: 'form',
                items: [  
		    {
                        layout: 'column',
			items: [
			    
			    {
				width: 450,
				layout: 'form',
				labelWidth: 160,
				items:
					[{
						xtype: 'textfieldpmw',
						width: 270,
						name: 'Search_Fio',
						id: 'MedPersDLO_SearchFio',
						fieldLabel: lang['fio'],
						tabIndex: TABINDEX_MedPerson4ReceptListWindow + 1,
					}]
			    },
			    {
				layout: 'form',
				width: 390,
				labelWidth: 160,
				items:
					[{
						fieldLabel: lang['doljnost'],
						hiddenName: 'PostMed_id',
						id: 'MedPersDLO_PostMed', 
						width: 210,
						listWidth: 320,
						xtype: 'swpostmedlocalcombo',
						tabIndex: TABINDEX_MedPerson4ReceptListWindow + 2,
					}]
			    },
			    {
				layout: 'form',
				width: 360,
				labelWidth: 160,
				items: [{
					hiddenName: 'WorkType_id',
					id: 'MedPersDLO_WorkType', 
					valueField: 'WorkType_id',
					displayField: 'WorkType_Name',
					fieldLabel: lang['tip_zanyatiya_doljnosti'],
					store: new Ext.data.SimpleStore({
						autoLoad: true,
						data: [
							[ 1, 1, lang['osnovnoe_mesto_rabotyi'] ],
							[ 2, 2, lang['sovmestitelstvo'] ],
							[ 3, 3, lang['sovmeschenie'] ]
						],
						fields: [
							{ name: 'WorkType_id', type: 'int'},
							{ name: 'WorkType_Code', type: 'int'},
							{ name: 'WorkType_Name', type: 'string'}
						],
						key: 'WorkType_id',
						sortInfo: { field: 'WorkType_Code' }
					}),
					editable: false,
					xtype: 'swbaselocalcombo',
					tabIndex: TABINDEX_MedPerson4ReceptListWindow + 3,
				}]
			    }
						
			  
			]
		    },
                    
                    {
                        layout: 'column',
                        items: [
			    
			    {
				layout: 'form',
				width: 450,
				labelWidth: 160,
				items: [{
					hiddenName: 'StatusType_id', 
					id: 'MedPersDLO_StatusType',
					valueField: 'StatusType_id',
					displayField: 'StatusType_Name',
					fieldLabel: 'Статус врачей в регистре',
					width: 270,
					listWidth: 320,
					store: new Ext.data.SimpleStore({
						autoLoad: true,
						data: [
							[ 10, 10, 'Имеющие право на выписку рецептов ЛЛО'],
							[ 20, 20, 'Заявленные на изменения в регистре врачей ЛЛО' ],
							[ 21, 21, 'Заявленные на включение в регистр врачей ЛЛО' ],
							[ 22, 22, 'Заявленные на исключение из регистра врачей ЛЛО' ],
							[ 30, 30, 'Не имеющие право на выписку рецептов ЛЛО']
						],
						fields: [
							{ name: 'StatusType_id', type: 'int'},
							{ name: 'StatusType_Code', type: 'int'},
							{ name: 'StatusType_Name', type: 'string'}
						],
						key: 'StatusType_id',
						sortInfo: { field: 'StatusType_id' }
					}),
					editable: false,
					xtype: 'swbaselocalcombo',
					/*
					listeners: {
					'change': function( cmb, newValue, oldValue ) { 
					    if (!form.adminLLO) 
						Ext.getCmp('MedPersDLO_Print').setDisabled( newValue != 20 )
					}		
				    },
					*/
				    tabIndex: TABINDEX_MedPerson4ReceptListWindow + 4
				}]
			    },	
			   {
				width: 390,
				layout: 'form',
				labelWidth: 160,
				items:[{
					    xtype: 'textfieldpmw',
					    width: 210,
					    name: 'Search_Tab',
					    id: 'MedPersDLO_SearchTab',  
					    fieldLabel: 'Табельный номер',
					    tabIndex: TABINDEX_MedPerson4ReceptListWindow + 5
					}]
			    },
			     {
				width: 360,
				layout: 'form',
				labelWidth: 160,
				items:[{
					    xtype: 'textfieldpmw',
					    width: 190,
					    name: 'Search_Code',
					    id: 'MedPersDLO_SearchCode',  
					    fieldLabel: 'Код ЛЛО',
					    tabIndex: TABINDEX_MedPerson4ReceptListWindow + 6
					}]
			    }
			]},
		     {
                        layout: 'column',
                        items: [
			    {layout: 'form',
				width: 450,
				labelWidth: 160,
				hidden: !form.adminLLO,
                                items: [{
					autoLoad: false,
					fieldLabel: 'ЛПУ',
					hiddenName: 'Lpu_id',
					id: 'MedPersDLO_Lpu',
					xtype: 'amm_LpuListCombo',
					width: 270,
					tabIndex: TABINDEX_MedPerson4ReceptListWindow + 7
			    }
				]},
			    {layout: 'form',
				style: "padding-left: 180px",
                                items: [{
					 xtype: 'button',
					text: 'Найти',
					iconCls: 'pill16',
					id: 'MedPersDLO_BtnSearch',
					disabled: false,
					tabIndex: TABINDEX_MedPerson4ReceptListWindow + 8,

						handler: function() {
						    Ext.getCmp('MedPerson4ReceptListWindow').doSearch();
						}
				}]
                        },
			     {
                                layout: 'form',
                                items: [{
                                        style: "padding-left: 25px",
                                        xtype: 'button',
                                        id: 'MedPersDLO_BtnClear',
                                        text: lang['sbros'],
                                        iconCls: 'reset16',
                                        tabIndex: TABINDEX_MedPerson4ReceptListWindow + 9,
                                        handler: function() {
                                            //  Очищаем фильтр на панеле фильтров
											Ext.getCmp('MedPersDLO_SearchFio').reset();
											Ext.getCmp('MedPersDLO_PostMed').reset();
											Ext.getCmp('MedPersDLO_WorkType').reset();
											Ext.getCmp('MedPersDLO_StatusType').reset();
											Ext.getCmp('MedPersDLO_SearchTab').reset(); 
											Ext.getCmp('MedPersDLO_SearchCode').reset();
											Ext.getCmp('MedPersDLO_Print').setDisabled(true)
                                        }
                                    }]
                            }
                        ]
                    }
                ]
            }
         }),
                 this.StaffPanel = new sw.Promed.ViewFrame(
		{
			title:lang['mesto_rabotyi_sotrudnika'],
			id: 'MedPerson4rec_Grid',
			object: 'MedStaffFact',
			editformclassname: 'swMedStaffFactEditWindow',
			dataUrl: '/?c=MedPersonal&m=ufa_getMedPersonalGridPaged',
			height:303,
			pageSize: 500,
			paging: true,
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			allowedPersonKeys: (getGlobalOptions().region.nick == 'kareliya')?(['F10']):(null),
			remoteSort: true,
			stringfields:
			[
				{name: 'MedStaffFact_id', type: 'int', header: 'ID', key: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true, isparams: true},
				{name: 'MedPersonal_TabCode', type: 'string', header: lang['tab_№'], width: 50}, 
				{name: 'MedPersonal_Code', type: 'string', header: 'Код<br />ЛЛО', width: 50},
				{id: 'autoexpand', name: 'MedPersonal_FIO',  type: 'string', header: lang['fio_vracha']},
				{name: 'LpuSection_Name',  type: 'string', header: 'Подразделение', width: 200},
				{name: 'PostMed_Name',  type: 'string', header: lang['doljnost'], width: 150},
				{name: 'MedStaffFact_Stavka',  type: 'float', header: lang['stavka'], width: 55},
				{name: 'MedStaffFact_setDate',  type: 'date', header: 'Начало<br />работы', width: 75},
				{name: 'MedStaffFact_disDate',  type: 'date', header: 'Окончание<br />работы', width: 75},
				{name: 'WorkData_dlobegDate',  type: 'date', header: 'Дата<br />включения в<br />регистр ЛЛО', width: 80},
				{name: 'WorkData_dloendDate',  type: 'date', header: 'Дата<br />исключсения<br />из регистра<br />ЛЛО', width: 85},
				{name: 'WorkPlace4DloApplyTYpe_Name',  type: 'string', header: 'Статус',width: 100},
				{name: 'ctrl_tabCode',  type: 'int', header: 'ctrl_tabCode', hidden: true},
				{name: 'WorkPlace4DloApplyTYpe_id',  type: 'int', header: 'WorkPlace4DloApplyTYpe_id', hidden: true},
				{name: 'recStatus_id',  type: 'int', header: 'recStatus_id', hidden: true}, 
				{name: 'WorkPlace4DloApply_id',  type: 'int', header: 'WorkPlace4DloApply_id', hidden: true},
				{name: 'lpu_id',  type: 'int', header: 'lpu_id', hidden: true},
				{name: 'lpu_nick',  type: 'string', header: 'Наименование МО', width: 150},
				{name: 'lpu_ogrn',  type: 'string', header: 'ОГРН МО', width: 150}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true, handler: function() {}},
				{name:'action_edit', 
				    text: (form.adminLLO) ? 'Утвердить изменение' : 'Включить в регистр врачей ЛЛО',
				    handler: function()
					{
						if ( form.StaffPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
						    var row = form.StaffPanel.ViewGridPanel.getSelectionModel().getSelected();
						    var params = new Array();
						    if (form.adminLLO) {
							params.WorkPlace4DloApply_id = row.data.WorkPlace4DloApply_id;
							params.WorkPlace4DloApplyStatus_id = 0;
							params.WorkPlace4DloApplyTYpe_Name = '';
							if (row.data.recStatus_id == 21)
							    params.recStatus_id = 10;
							else if (row.data.recStatus_id == 22)
							    params.recStatus_id = 30;
						    }
						    else {
							params.MedStaffFact_id = row.data.MedStaffFact_id;
							params.WorkPlace4DloApplyTYpe_id = 1;
							
							params.WorkPlace4DloApply_id = row.data.WorkPlace4DloApply_id;
							params.WorkPlace4DloApplyStatus_id = 0;
							if (row.data.recStatus_id == 21 ) {
							    params.WorkPlace4DloApplyStatus_id = 10;
							    params.recStatus_id = 30;
							    params.WorkPlace4DloApplyTYpe_id = 0;
							}
							else if (row.data.recStatus_id == 22 ) {
							    params.WorkPlace4DloApplyStatus_id = 10;
							    params.recStatus_id = 10;
							    params.WorkPlace4DloApplyTYpe_id = 0;
							}
							else {
							    params.recStatus_id = 21;
							    params.WorkPlace4DloApplyTYpe_Name = 'На включение в регистр'
							}
							
						    }
						    console.log('params = '); console.log(params);
						    form.doSave(params, row);
						}
					}
				},
				{name:'action_view', disabled: true, hidden: true, handler: function() {}},
				{name:'action_delete', 
				    text: 'Исключить из регистра врачей ЛЛО',
				    hidden: form.adminLLO,
				    handler: function(b) {
						if ( form.StaffPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
						    var row = form.StaffPanel.ViewGridPanel.getSelectionModel().getSelected();
						    var params = new Array();
						    //if (row.data.ctrl_tabCode == 1)
						    params.MedStaffFact_id = row.data.MedStaffFact_id;
						    params.WorkPlace4DloApplyTYpe_id = 2;
						    params.recStatus_id = 22;
						    params.WorkPlace4DloApply_id = row.data.WorkPlace4DloApply_id;
						    if (row.data.recStatus_id == 21 ) {
							params.WorkPlace4DloApplyStatus_id = 10;
							params.recStatus_id = 30;
						    }
						    else {
							params.WorkPlace4DloApplyStatus_id = 0;
							params.WorkPlace4DloApplyTYpe_Name = 'На исключение из регистра'
						    }
						    form.doSave(params, row);
						}
					}
				},
				
				{name:'action_refresh'},
				{name:'action_print'}, //hidden: isMedPersView()},
				
			],
			
			updateContextMenu: function() {
				var grid = Ext.getCmp('MedPerson4rec_Grid');
					//this.StaffPanel;
				
				var rowSelected = grid.getGrid().getSelectionModel().getSelected();
				
				var actionObj = new Object();
				actionObj.deleteIsHidden = 0;
				actionObj.editIsHidden = 0;
				 if (form.adminLLO) {
				     // Уровень МИАЦ
				    console.log('rowSelected.data.recStatus_id = ' + rowSelected.data.recStatus_id);
				      switch(rowSelected.data.recStatus_id) {
					    case 21:
					    case 22:
						//actionObj.editIsHidden = 1;
						actionObj.deleteIsHidden = 1;
					    break;
					    case 0:
						actionObj.editIsHidden = 1;
						actionObj.deleteIsHidden = 1;
					    break;
					    default:
						actionObj.editIsHidden = 1;
					    break;
				      }
				      
				     
				 }
				else {
				    // Уровень МО
				    actionObj.text = 'Включить в регистр врачей ЛЛО';
				    switch(rowSelected.data.recStatus_id) {
					case 10://
						actionObj.editIsHidden = 1;
						//actionObj.text = "Исполнить";
					break;
					case 30:
					    if (rowSelected.data.ctrl_tabCode == 1) //Если табельный номер не уникальный
						    actionObj.editIsHidden = 1;	    //Скрываем кнопку включения в регистр
					    actionObj.deleteIsHidden = 1;
					break;
					case 21:
					case 22:
					    actionObj.deleteIsHidden = 1;
					    actionObj.text = "Отменить изменения";
					    break;
					case 0:
					    actionObj.editIsHidden = 1;
					    actionObj.deleteIsHidden = 1;
					break;
					    
				    }
				    grid.getAction('action_edit').setText(actionObj.text);
				}
				
				 grid.getAction('action_edit').setDisabled(actionObj.editIsHidden)
				 grid.getAction('action_delete').setDisabled(actionObj.deleteIsHidden);

			}
			
		});
            
            this.StaffPanel.getGrid().view = new Ext.grid.GridView({
		getRowClass : function (row, index) {
		    var cls = '';

		    if (row.get('ctrl_tabCode') == 1) {//  Табельный номер не уникальный
			    cls = cls+'x-grid-rowbold '; 
			    cls = cls+'x-grid-rowbackyellow ';
		    }
		    if (row.get('WorkPlace4DloApplyTYpe_id') == 1 || row.get('WorkPlace4DloApplyTYpe_id') == 2) {//  Заявки на включение
			    cls = cls+'x-grid-rowblue ';
		    }
		    
		    if (row.get('WorkPlace4DloApplyTYpe_id') == 2) {//  Заявки на исключение
			    cls = cls+'x-grid-rowblue' ;
		    }
		     if (row.get('recStatus_id') == 10) {//  Имеют право на ЛЛО
			     cls = cls+'x-grid-rowbold '; 
			    cls = cls+'x-grid-rowgreen' ;
		    }

		    return cls;
			},
		});


            Ext.apply(this, {
                 lbodyBorder: true,
                 layout: "border",
                  cls: 'tg-label',
			items: 
			[ 
                            form.SearchParamsPanel,
			    //--------
			     form.StaffPanel,
			   
			],
                                buttons: [
               {
			iconCls: 'print16',
			text: BTN_GRIDPRINT,
                        id: 'MedPersDLO_Print',
			hidden: form.adminLLO,
                        disabled: false,
                        tabIndex: TABINDEX_MedPerson4ReceptListWindow + 11,

			handler: function() {
			    //Ext.getCmp('MedPerson4ReceptListWindow').doSearch();
			    var object_value = '';
			    $data = Ext.getCmp('MedPerson4rec_Grid').getGrid().getSelectionModel().grid.store.data;
			    Cnt = $data.items.length;
			     for (var r = 0; r <= Cnt - 1; r++) {
				 object_value += $data.items[r].data.MedStaffFact_id + ', '
			     }
			    console.log('object_value = ' + object_value);
			    var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'/run?__report=report/printMedPerson4ReceptList.rptdesign&paramList=' + object_value + '&paramLpu=' +  + getGlobalOptions().lpu_id + '&__format=pdf';	
					    window.open(url, '_blank');
//                                                }
			}
                                    
                        },
			
			{
			iconCls: 'print16',
			text: 'Выгрузить в ДБФ',
                        id: 'MedPersDLO_Exp2DBF',
			hidden: !form.adminLLO,
                        disabled: false,
                        tabIndex: TABINDEX_MedPerson4ReceptListWindow + 12,

			handler: function() {
			    
			    Ext.Ajax.request({
				url: '/?c=RegistryRecept&m=ufaExportCVF2dbf',
				method: 'POST',
				callback: function (options, success, response){
				    if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					console.log('response_obj');
					console.log(response_obj);
					if (response_obj.success){
					    form.getLoadMask().hide();
					    form.showLink(response_obj);
					} else {
					    var err = '';
					    if (response_obj.Error_Msg) {
						err = ' (' + response_obj.Error_Msg + ')';
					    }
					    sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_arhivatsii'] + err);
					    form.getLoadMask().hide();
					}
				    } else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_arhivatsii_nepravilnyiy_otvet_servera']);
					form.getLoadMask().hide();
				    }
				},
//				params:params,
//				success: function(response, opts) {		
//				    record.set('recStatus_id', params.recStatus_id); 
//				    record.set('WorkPlace4DloApplyTYpe_id', params.WorkPlace4DloApplyTYpe_id); 
//				    record.set('WorkPlace4DloApplyTYpe_Name', params.WorkPlace4DloApplyTYpe_Name);
//				    record.commit();
//				    Ext.getCmp('MedPerson4rec_Grid').getGrid().getSelectionModel().selectRecords(record);
//				}
			    })
			}
                                    
                        },
                {
                    text: '-'
                },
                HelpButton(this, TABINDEX_MedPerson4ReceptListWindow + 13),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'close16',
                    id: 'DrugTurnover_CancelButton',
                    text: lang['zakryit'],
		    tabIndex: TABINDEX_MedPerson4ReceptListWindow + 13,
		    onTabAction : function () {
			Ext.getCmp('MedPersDLO_SearchFio').focus(true, 0);
		    },
                }
            ]
            });      
            
                
         sw.Promed.swMedPerson4ReceptListWindow.superclass.initComponent.apply(this, arguments);
     },
      show: function() {
		sw.Promed.swMedPerson4ReceptListWindow.superclass.show.apply(this, arguments);
		
		this.StaffPanel.setParam('start', 0, true);
		this.StaffPanel.setParam('limit', 100, true);
		
		Ext.getCmp('MedPersDLO_Lpu').getStore().load();
		
		Ext.getCmp('MedPersDLO_FilterPanel').fieldSet.expand();
		 Ext.getCmp('MedPersDLO_StatusType').setValue(20);
		Ext.getCmp('MedPerson4ReceptListWindow').doSearch();
		
		//Ext.getCmp('MedPersDLO_SearchFio').focus(true, 100);

      }          

 });

