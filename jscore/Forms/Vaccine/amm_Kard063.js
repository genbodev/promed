/**
* amm_Kard063 - окно просмотра карты профилактических прививок №063
*
* PromedWeb - The New Generation of Medical Statistic Softwareа
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Нигматуллин Тагир
* @version      май 2012
* @comment      ??Префикс для id компонентов regv?? (amm_JournalViewWindow)
*/

//sw.Promed.amm_Kard063 = Ext.extend(Ext.Window, {

var Person_deadDT;
var Control_Gepatity_DT;
var DD;
var DDD;
var Person_kard063;

sw.Promed.amm_Kard063 = Ext.extend(sw.Promed.BaseForm, {
	id: "amm_Kard063", 
	title: "Карта профилактических прививок",
	border: false,
	width: 800,
	height: 400,
	modal:true,
	maximizable: true,
	maximized: true,
	closeAction: 'hide',
	codeRefresh: true,
	objectName: 'amm_Kard063',
	objectSrc: '/jscore/Forms/Vaccine/amm_Kard063.js',
	onHide: Ext.emptyFn,
	
	listeners: {
		'success': function(source, params) {
																		
			/* source - string - источник события (например форма)
			 * params - object - объект со свойствами в завис-ти от источника
			 */
			sw.Promed.vac.utils.consoleLog('amm_Kard063-success-source:');
			sw.Promed.vac.utils.consoleLog(source);
			//console.log('params =');
			//console.log(params);
			switch(source){
				case 'amm_PurposeVacForm':
					sw.Promed.vac.utils.consoleLog('Назначено');
					Ext.getCmp('amm_PersonVacPlan').ViewGridPanel.getStore().reload();
					Ext.getCmp('amm_PersonVacFixed').ViewGridPanel.getStore().reload();
					Ext.getCmp('amm_PersonVacAccount').ViewGridPanel.getStore().reload();
					Ext.getCmp('amm_Kard063').ammPerson063Update(params);
					break;
				case 'amm_ImplVacForm':
					sw.Promed.vac.utils.consoleLog('Исполнено!');
					Ext.getCmp('amm_PersonVacFixed').ViewGridPanel.getStore().reload();
					Ext.getCmp('amm_PersonVacAccount').ViewGridPanel.getStore().reload();
					Ext.getCmp('amm_Person063').ViewGridPanel.getStore().reload();
					Ext.getCmp('amm_PersonVacOther').ViewGridPanel.getStore().reload();
					break;  
				case 'amm_RefuseVacForm':
					Ext.getCmp('amm_PersonMedTapRefusal').ViewGridPanel.getStore().reload();
					break;
				case 'amm_QuikImplVacOtherForm':
					Ext.getCmp('amm_PersonVacOther').ViewGridPanel.getStore().reload();
					break;
				case 'TubPlan':
				case 'TubAssigned':
				case 'TubReaction':
					Ext.getCmp('amm_PersonPersonMantu').ViewGridPanel.getStore().reload();
					break;
			}
		}
	},
                
        ammPerson063Update   : function(params) {
			if (params == undefined) {
				console.log('params = null');
				Ext.getCmp('amm_Person063').ViewGridPanel.getStore().reload();
			}
			else if (params.key_list == undefined) {
                  console.log('key_list = null');
                Ext.getCmp('amm_Person063').ViewGridPanel.getStore().reload();
            }
             else if (params.key_list.indexOf('_1.')>=0) {
               //  console.log('key_list = true');
                Ext.getCmp('amm_Person063').ViewGridPanel.getStore().reload();
             }
            else {
                //console.log('key_list = false');
                vac_arr = params.vac_arr;
                for (var i = 0; i <= vac_arr.length - 1; i++) {
                     //console.log('vac_arr = ' + params.vac_arr);
                     Cnt = Ext.getCmp('amm_Person063').ViewGridPanel.getStore().data.items.length;
                     for (var r = 0; r <= Cnt - 1; r++) {
                        
                       record = Ext.getCmp('amm_Person063').ViewGridPanel.getStore().data.items[r].data;
                        if (vac_arr [i] == record.PersonPlan_id) {
                            Ext.getCmp('amm_Person063').getGrid().getSelectionModel().selectRow(r);
                            record = Ext.getCmp('amm_Person063').getGrid().getSelectionModel().getSelected();
                            record.set('StatusType_id', 1); 
                            record.set('StatusSrok_id', 0);  
                            record.set('StatusType_Name', 'Исполнено');
                            record.set('vac_name', params.vaccine_Name);
                            record.set('date_vac', params.vacImplementDate);
                            record.set('vacJournalAccount_Seria', params.vac_seria);
                            record.set('vacJournalAccount_Dose', params.vac_doze);
                            

                            record.set('idInCurrentTable', params.vacJournalAccount_id);
                            

                            //console.log('record');
                            //console.log(record);
                            record.commit();
                            break;
                        }
                     }
                }

            }
        },   

                
	initComponent: function() {
		var wnd = this;
		/**
		 *  Персональные данные
		 */
		this.PersonInfoPanel  = new sw.Promed.PersonInfoPanel({
			region: 'north',
			//autoHeight: true,
			id: 'Kart063_PersonInfoFramea',
			titleCollapse: true,
			floatable: false,
			   collapsible: true,
			collapsed: true,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			border: true
		});

			/**
			 *  Карта 063
			 */

			this.ViewFrame063 = new sw.Promed.ViewFrame({
			id: 'amm_Person063',
			dataUrl: '/?c=VaccineCtrl&m=getPersonVac063',
			region: 'center',
			toolbar: true,
			setReadOnly: false,
			autoLoadData: false,
			cls: 'txtwrap',
			grouping: true,
			groupSortInfo: {field: 'row_num', direction: "ASC"},
			stringfields:
			[
				{name: 'row_num', type: 'int', hidden: true/*, header: 'row_num', key: true*/},
				{name: 'idInCurrentTable', type: 'int',header: 'id записи соответствующей таблицы',hidden: true},
				{name: 'PersonPlan_id', type: 'string', header: 'id записи таблицы планирования', hidden: true},
				{name: 'Inoculation_id', type: 'int', header: 'id записи Inoculation', hidden: true},
				{name: 'Inoculation_StatusType_id', type: 'int', header: 'Статус записи Inoculation (превышение интервала)', hidden: true},
				{name: 'Lpu_id', type: 'int',header: 'id ЛПУ',hidden: true},
				{name: 'Scheme_num', type: 'int', header: 'номер схемы вакцинации', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
				{name: 'Scheme_id', type: 'string', header: 'Scheme_id', hidden: true},
				{name: 'vaccineType_id', type: 'int', header: 'vaccineType_id', hidden: true},
				{name: 'StatusType_Name', type: 'string', header: 'Статус', width: 130, sortable: true},
				{name: 'VaccineType_Name', type: 'string', header: VAC_TIT_INFECT_TYPE, group: true, hidden: true/*, sort: true, direction: 'ASC'*/},
				{name: 'VaccineType_FullName', type: 'string', header: VAC_TIT_INFECT_TYPE, width: 200},
				{name: 'typeName', type: 'string', header: VAC_TIT_VACTYPE_NAME, width: 70, sortable: false},
				{name: 'date_plan', type: 'date', header: 'Плановая дата', width: 100, sortable: true},
				{name: 'date_purpose', type: 'date', header: 'Назначенная дата', width: 110, sortable: true},
				{name: 'date_vac', type: 'date', header: 'Дата выполнения', width: 110, sortable: true},
				{name: 'Age', type: 'string', header: 'Возраст', width: 70, sortable: true},
				{name: 'vac_name', id: 'autoexpand', type: 'string', header: VAC_TIT_VAC_NAME, width: 200, sortable: true},
				{name: 'vacJournalAccount_Dose', type: 'string', header: 'Доза', width: 70, sortable: true},
				{name: 'vacJournalAccount_Seria', type: 'string', header: 'Серия', width: 70, sortable: true},
				{name: 'ReactGeneralDescription', type: 'string', header: 'Общ. реакция', width: 70, sortable: false},
				{name: 'ReactlocaDescription', type: 'string', header: 'Местн. реакция', width: 70, sortable: false},
				{name: 'StatusType_id', type: 'int', header: 'StatusType_id', width: 40, sortable: false, hidden: true},
				{name: 'StatusSrok_id', type: 'int', header: 'StatusSrok_id', width: 40, ortable: false, hidden: true},
				{name: 'Tap_DateBeg', type: 'date', header: 'Начало', sortable: true},
				{name: 'Tap_DateEnd', type: 'date', header: 'Окончание', sortable: true},
				{name: 'vacData4Card063_id', type: 'int', hidden: true, key: true}
			],
			
			onLoadData: function(sm, index, record)
			{
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
				if( Ext.get(this.getGrid().getView().getGroupId('(Пусто)')) != null ) {
					this.getGrid().getView().toggleGroup(this.getGrid().getView().getGroupId('(Пусто)'), false);
				}
			},
			onDblClick: function() {
				var tabDigest = Ext.getCmp('amm_Person063');
				var rowSelected = tabDigest.getGrid().getSelectionModel().getSelected();
				var actionName;
				switch(rowSelected.data.StatusType_id) {
					case -1://инфо отсутствует // Назначение прививки
						actionName = 'action_edit';
					break;
					
					case 0:// Назначено // Исполнение прививки
						actionName = 'action_edit';
					break;
					
					case 1://Исполнено //редактирование
						actionName = 'action_edit';
					break;
					
					case 2://Запланировано // Назначение прививки
						actionName = 'action_edit';
					break;
					
					default:
						actionName = '0';
					break;
				}
				
				if (tabDigest.getAction(actionName) != undefined && wnd.viewOnly == false) {
					tabDigest.getAction(actionName).execute();
				}
			}.createDelegate(this),
			updateContextMenu: function() {
				var tabDigest = Ext.getCmp('amm_Person063');
				var rowSelected = tabDigest.getGrid().getSelectionModel().getSelected();
				var actionObj = new Object();
				actionObj.isHidden = 1;
				actionObj.editIsHidden = 0;
				actionObj['action_view'] = {isHidden: 1};
				switch(rowSelected.data.StatusType_id) {
					case -1://инфо отсутствует // Назначение прививки
						actionObj.isHidden = 0;
						actionObj.text = "Исполнить";
					break;

					case 0:// Назначено // Исполнение прививки
						actionObj.text = "Исполнить";
					break;

					case 1://Исполнено //редактирование
						actionObj.text = VAC_MENU_EDIT;
//                                                alert(rowSelected.data.date_vac);
//                                                alert(Control_Gepatity_DT);
                                                DD = rowSelected.data.date_vac;
                                                //DDD = new Date (Control_Gepatity_DT.date.substring(0, 10).replace(/-/g, ','))
                                        if ((rowSelected.data.vaccineType_id == 1)&(rowSelected.data.typeName == 'V1' )) {  //  Если Гепатит В
                                            //var d1 = new Date (1961, 8, 13);
                                            //var d2 = new Date (2014, 5, 19);
                                            //  & (rowSelected.data.date_vac < Control_Gepatity_DT.date ) & (rowSelected.data.StatusType_id == 1)
                                            //alert ((d2 - d1) / (1000*60*60*24*365));
                                            var vCount = Ext.getCmp('amm_Person063').getGrid().store.getCount();
                                            var rec;
                                            var flag = 1;
                                            if (rowSelected.data.date_vac > Control_Gepatity_DT ) {
                                                flag = 0;}
                                           else { 
                                               for (var i=0; i < vCount; i++){
                                                    rec = Ext.getCmp('amm_Person063').getGrid().getSelectionModel().grid.store.data.item (i).data;
                                                     //alert(rec.VaccineType_Name + ' / ' + rec.typeName)
                                                    if ((rec.vaccineType_id == 1) & (rec.StatusType_id == 1)& 
                                                                ((rec.typeName == 'V2' )| (rec.typeName == 'V3' ))) 
                                                                //& (rec.data.date_vac >= Control_Gepatity_DT.date )) 
                                                        {
                                                        flag = 0;
                                                        i = vCount;
                                                    }
                                                }
                                            }
                                            if (flag == 1) {
                                                 actionObj['action_view'].isHidden = 0;
                                                } 
                                        }         
                                       break;

					case 2://Запланировано // Назначение прививки
						actionObj.text = "Назначить/Исполнить";
					break;

					default:
						actionObj.editIsHidden = 1;
					break;
				}
				if(wnd.viewOnly == true)
					tabDigest.getAction('action_edit').setHidden(true);
				else
					tabDigest.getAction('action_edit').setHidden(actionObj.editIsHidden);
				tabDigest.getAction('action_edit').setText(actionObj.text);
                tabDigest.getAction('action_view').setHidden(actionObj['action_view'].isHidden);
			},
			actions:[
					{name:'action_add',  hidden: true},
					{name:'action_edit',
//						hidden: true
						handler: function() {
							var rowSelected = this.findById('amm_Person063').getGrid().getSelectionModel().getSelected();
							if(wnd.viewOnly == false)
								switch(rowSelected.data.StatusType_id) {
									
									case -1://инфо отсутствует
										//Исполнение прививки (история):
										sw.Promed.vac.utils.callVacWindow({
											record: rowSelected,
											gridType: 'VacNoInfo'
										}, Ext.getCmp('amm_Person063'));
									break;
									
									case 0://Назначено
										// Исполнение прививки:
										sw.Promed.vac.utils.callVacWindow({
											record: rowSelected,
											gridType: 'VacAssigned'
										}, this);
									break;
									
									case 1://Исполнено
	//									record.Vaccine_id = rowSelected.data.Vaccine_id;
										sw.Promed.vac.utils.callVacWindow({
											record: rowSelected,
											gridType: 'VacRegistr'
										}, this);
									break;
									
									case 2://Запланировано
										//  Назначение прививки
										sw.Promed.vac.utils.callVacWindow({
											record: rowSelected,
											gridType: 'VacPlan'
										}, this);
									break;
									
									default:
										sw.Promed.vac.utils.consoleLog('rowSelected:');
										sw.Promed.vac.utils.consoleLog(rowSelected);
										sw.Promed.vac.utils.consoleLog(rowSelected.get('PersonPlan_id'));
	//									rowSelected.set('idInCurrentTable', rowSelected.get('PersonPlan_id'));
	//									rowSelected.set('person_plan_id', rowSelected.get('PersonPlan_id'));
	//									rowSelected.commit();
										sw.Promed.vac.utils.callVacWindow({
											record: rowSelected,
											gridType: 'xxx'
										}, this);
									break;
								}

						}.createDelegate(this)
					},
					//{name:'action_view',  hidden: true},
					{
						name:'action_view',
						text: 'Превышен интервал',
						tooltip: 'Превышен интервал',
						icon: 'img/icons/hand-red16.png',
						handler: function()
						{
                                    sw.swMsg.show({
                                            buttons: Ext.Msg.YESNO,
                                            fn: function(buttonId, text, obj) {
                                                    if ( buttonId === 'yes' ) {
                                                            var record = this.findById('amm_Person063').getGrid().getSelectionModel().getSelected();
                                                            var params = {
                                                                    'parent_id': 'amm_Kard063',
                                                                    'Inoculation_id': record.get('Inoculation_id')
                                                            };

                                                            Ext.Ajax.request({
                                                                    url: '/?c=VaccineCtrl&m=vac_interval_exceeded',
                                                                    method: 'POST',
                                                                    params: params,
                                                                    success: function(response, opts) {
                                                                            sw.Promed.vac.utils.consoleLog(response);
                                                                            if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                                                                                    Ext.getCmp(params.parent_id).fireEvent('success', 'amm_PurposeVacForm',
                                                                                    { }
                                                                                    );
                                                                            }
            //                form.hide();
                                                                    }
                                                            });
                                                    }
                                            }.createDelegate(this),
                                            icon: Ext.MessageBox.QUESTION,
                                            msg: 'Установить статус "Превышен интервал"?<br/>\n\
                                            После проведения операции необходимо переформировать персональный план!',
                                            title: 'Превышен интервал'
                                    });

                                                }.createDelegate(this)
                                                        
                                                        


                    //                        hidden: true
                                        },
					{name:'action_delete',  hidden: true},
					{name:'action_save',  hidden: true}
			]
		});
		
		this.ViewFrame063.getGrid().view = new Ext.grid.GroupingView(
		{
			//Далее два свойства (enableGroupingMenu и enableNoGroups) - пока что скрываем в меню столбца 
			//пункты, связанные с группировкой (на будущее - можно с ними поиграться (смена столбца группировки,
			//включение/выключение группировки...)):
			enableGroupingMenu: false,
			enableNoGroups: false,
			
			showGroupName: false,
			showGroupsText: true,
			groupByText: 'NationalCalendarVac_vaccineTypeName',
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? (values.rs.length>4 ? "записей" : "записи") : "запись"]})',
			getRowClass : function (row, index){
				var arrCls = [];
				switch (row.get('StatusType_id')){
					case 0:// назначено
						arrCls.push('x-grid-rowbold');
						break;
					case 1:// исполнено
						if (row.get('Inoculation_StatusType_id') == 2) {
							arrCls.push('x-grid-rowitalic');
							arrCls.push('x-grid-rowgray');
						}
						else {
							arrCls.push('x-grid-rowgreen');
							arrCls.push('x-grid-rowbold');
						}
						break;
					case 2:// запланировано
						arrCls.push('x-grid-rowblue');
						break;
					case 3:
					case 4:
						break;
					case 11:// медотвод/отказ
					case 12:
						arrCls.push('x-grid-rowgray');
						break;
					default:
						arrCls.push('x-grid-rowdeleted');
						break;
				}
				
				switch (row.get('StatusSrok_id')){
					case -1:// просрочено
						if( 
							!( 
							row.get('StatusType_id') == 2 
							&& ( typeof row.get('date_plan') == 'object' && getGlobalOptions().date == Ext.util.Format.date(row.get('date_plan'),'d.m.Y') ) 
							) 
						) {
							arrCls.length = 0;
							arrCls.push('x-grid-rowred');
						}
						break;
					case 0:// норм
						break;
					case 1:// меньше месяца
						arrCls.push('x-grid-rowbackyellow');
						break;
					case 2:// больше месяца
						break;
					default:
						break;
				}
				//Записи с нулевой плановой датой означают, что соответствующая записи в НК удалены. Выделяем такие записи курсивом
				if(!row.get('date_plan')) arrCls.push('x-grid-rowitalic');

				return arrCls.join(' ');
			}
		});

                
                /**
                 *  Манту 
                 */
                	
		this.ViewFramePersonMantu = new sw.Promed.ViewFrame({
			id: 'amm_PersonPersonMantu',
			dataUrl: '/?c=Vaccine_List&m=getPersonVacMantuAll',
			region: 'center',
			toolbar: true,
			setReadOnly: false,
			autoLoadData: false,
			cls: 'txtwrap',
                        height: 300,
			stringfields:
			[	   							
			{name: 'id', type: 'int', header: 'ID', key: true},
                        {name: 'Person_id',  type: 'int', header: 'Person_id', hidden: true},
                        {name: 'BirthDay', header: 'Дата рождения', width: 70, hidden: true},
                        {name: 'idInCurrentTable', type: 'int', header: 'Id записи текущей таблицы', width: 100, hidden: true},
                        {name: 'Status_Name', type: 'string', header: 'Статус', width: 100 },
                        {name: 'DatePlan',  type: 'date', header: 'Дата планирования', width: 80}, 
                        {name: 'DatePurpose', type: 'date', header: 'Дата назначения', width: 80},
                        {name: 'DateVac', type: 'date', header: 'Дата вакцинации', width: 80},
                        {name: 'TubDiagnosisType_Name', type: 'string', header: 'Метод диагностики', width: 120},
                        {name: 'Seria', type: 'string', header: 'Серия вакцины', width: 100},
                        {name: 'Period', type: 'date', header: 'Срок годности ', width: 100},
                        {name: 'Manufacturer', type: 'string', header: 'Изготовитель', width: 100},
                        {name: 'StatusType_id', type: 'int', header: 'Идентификатор статуса', width: 100, hidden: true},
                        {name: 'MantuReactionType_name', type: 'string', header: 'Тип реакции', width: 100}, 
                        {name: 'ReactDescription', type: 'string', header: 'Описание реакции', width: 200},
                        {name: 'ReactionSize', type: 'string', header: 'Реакция, [мм]', width: 90},
                        {name: 'DateReact', type: 'date', header: 'Дата описания реакции', width: 90},
                        {name: 'Lpu_Name', type: 'string', header: 'Наименование ЛПУ', width: 200, id: 'autoexpand'}
			],
			
			onDblClick: function() {
				var rowSelected = this.getGrid().getSelectionModel().getSelected();
				var actionName;
				switch(rowSelected.data.StatusType_id) {
					case -1://инфо отсутствует // Назначение
						actionName = 'action_edit';
					break;
					
					case 0:// Назначено // Исполнение
						actionName = 'action_edit';
					break;
					
					case 1://Исполнено //редактирование
						actionName = 'action_edit';
					break;
					
					case 2://Запланировано // Назначение
						actionName = 'action_edit';
					break;
					
					default:
						actionName = '0';
					break;
				}
				
				if (this.getAction(actionName) != undefined && wnd.viewOnly == false) {
					this.getAction(actionName).execute();
				}
			},
//			}.createDelegate(this),
			
			updateContextMenu: function() {
				var tabObj = Ext.getCmp('amm_PersonPersonMantu');
				var rowSelected = tabObj.getGrid().getSelectionModel().getSelected();
	//							alert('rowSelected.data.StatusType_id:');
	//							alert(rowSelected.data.StatusType_id);
	//			var actionIsHidden = 1;
				var actionObj = new Object();
				actionObj.isHidden = 1;
				actionObj.editIsHidden = 0;
				actionObj['action_add'] = {isHidden: 0}; //TODO!!! - переделать остальные по аналогии с этой строкой
				actionObj['action_delete'] = {isHidden: 0};
				switch(rowSelected.data.StatusType_id) {
					case -1://инфо отсутствует // Назначение манту
						actionObj.isHidden = 0;
						actionObj.text = "Назначить/Исполнить";
						actionObj.editIsHidden = 1;
//						tabObj.getAction('action_my').setText("Исполнить");
					break;

					case 0:// Назначено // Исполнение манту
						actionObj.text = "Исполнить";
					break;

					case 1://Исполнено //редактирование
						actionObj.text = VAC_MENU_EDIT;
					break;

					case 2://Запланировано // Назначение манту
						actionObj.text = "Назначить/Исполнить";
//						actionObj['action_add'].isHidden = 1;
						actionObj['action_delete'].isHidden = 1;
					break;

					default:
						actionObj.editIsHidden = 1;
					break;
				}
				tabObj.getAction('action_edit').setHidden(actionObj.editIsHidden);
				tabObj.getAction('action_add').setHidden(actionObj['action_add'].isHidden);
				tabObj.getAction('action_delete').setHidden(actionObj['action_delete'].isHidden);
//				tabObj.getAction('action_my').setHidden(actionObj.isHidden); newValue.format('d.m.Y')
				tabObj.getAction('action_edit').setText(actionObj.text);
			},
			
			actions:[
					{name:'action_add', // ,hidden: true
						handler: function()
						{
                                                    var rowSelected = this.findById('amm_PersonPersonMantu').getGrid().getSelectionModel().getSelected();
                                                    sw.Promed.vac.utils.consoleLog('rowSelected');
                                                    sw.Promed.vac.utils.consoleLog(rowSelected);
                                                    sw.Promed.vac.utils.consoleLog(rowSelected.data);
                                                    sw.Promed.vac.utils.consoleLog(rowSelected.store);
                                                    if (rowSelected.person_id == undefined) {
                                                        rowSelected.person_id = Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id');
                                                        sw.Promed.vac.utils.consoleLog('rowSelected2');
                                                        sw.Promed.vac.utils.consoleLog(rowSelected);
                                                    }
                                                    rowSelected.data.BirthDay = Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_Birthday').format('d.m.Y');
                                                    rowSelected.addNewMantu = 1;
                                                    // Исполнение:
                                                     sw.Promed.vac.utils.callVacWindow({
                                                         record: rowSelected,
                                                         gridType: 'TubReaction'
                                                     }, this,
											{
												action: 'add'
											});

						}.createDelegate(this)
					},
					{
						name: 'action_edit',
						handler: function()
						{
							var rowSelected = this.findById('amm_PersonPersonMantu').getGrid().getSelectionModel().getSelected(),
								gridType;

							if (wnd.viewOnly == false)
							{
								switch (rowSelected.data.StatusType_id)
								{
									// инфо отсутствует, назначение прививки
									case -1:
										gridType = 'TubPlan';
										break;
								
									// Назначено, исполнение:
									case 0:
										gridType = 'TubAssigned';
										break;
								
									// Исполнено
									case 1:
										rowSelected.addNewMantu = 0;
										sw.Promed.vac.utils.consoleLog('rowSelected:');
										sw.Promed.vac.utils.consoleLog(rowSelected);
										gridType = 'TubReaction';
										break;
								
									// Запланировано, назначение
									case 2:
										gridType = 'TubPlan';
										break;
								
									default:
										sw.Promed.vac.utils.consoleLog('rowSelected:');
										sw.Promed.vac.utils.consoleLog(rowSelected);
										sw.Promed.vac.utils.consoleLog(rowSelected.get('PersonPlan_id'));
										gridType = 'xxx';
										break;
								};

								sw.Promed.vac.utils.callVacWindow(
									{
										record: rowSelected,
										gridType: gridType
									},
									this,
									{
										action: 'edit'
									});
							}
						}.createDelegate(this)
					},
					{name:'action_view',  hidden: true},
//          {name:'action_delete',  hidden: true},
					
					{ // Удаление исполненной прививки манту
						name:'action_delete',
						handler: function()
						{
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId === 'yes' ) {
										var record = this.findById('amm_PersonPersonMantu').getGrid().getSelectionModel().getSelected();
										var params = {
											'parent_id': 'amm_Kard063',
											'JournalMantu_id': record.get('idInCurrentTable')
										};

										Ext.Ajax.request({
											url: '/?c=VaccineCtrl&m=deleteMantu',
											method: 'POST',
											params: params,
											success: function(response, opts) {
												sw.Promed.vac.utils.consoleLog(response);
												if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
													Ext.getCmp(params.parent_id).fireEvent('success', 'TubReaction', { } );
												}
				//                form.hide();
											}
										});
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: 'Удалить манту?',
								title: 'Удаление прививки'
							});

						}.createDelegate(this)
					},
					
					{name:'action_save',  hidden: true}
			]
		});  
		
		this.ViewFramePersonMantu.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
                            var cls = '';
                            if (row.get('StatusType_id') == 2)
                                cls = cls+'x-grid-rowblue';
                            else if (row.get('StatusType_id') == 1)
                                cls = 'x-grid-rowbold x-grid-rowgreen';
                            else if (row.get('StatusType_id') == 0)
                                cls = 'x-grid-rowbold';
                            
                            return cls;
			}
		});


		/**
		 *  Запланировано 
		 */	 
		this.ViewFramePersonVacPlan = new sw.Promed.ViewFrame({
			id: 'amm_PersonVacPlan',
			dataUrl: '/?c=VaccineCtrl&m=searchVacPlan',
			toolbar: true,
			setReadOnly: false,
			autoLoadData: false,
			cls: 'txtwrap',
			setActionDisabled: ('action_add', true),
			root: 'data',
			stringfields:
			[
				{name: 'planTmp_id', type: 'int', header: 'ID', key: true}, 
				{name: 'Scheme_num', type: 'int', header: 'Номер схемы', width: 70, hidden: true},
				{name: 'Date_Plan', type: 'date', header: 'Дата планирования', width: 90},
				{name: 'Person_id', type: 'int', header: 'Person_id', width: 70, hidden: true},
				{name: 'Age', type: 'string', header: 'Возраст', width: 90},
				{name: 'BirthDay', type: 'date', header: 'Дата рождения', width: 70, hidden: true},
				{name: 'type_name', type: 'string', header: 'Вид иммунизации', width: 160},
				{name: 'VaccineType_id', type: 'int', header: 'Прививка', width: 200, hidden: true},
				{name: 'Name', type: 'string', header: VAC_TIT_INFECT_TYPE, id: 'autoexpand'},
				{name: 'SequenceVac', type: 'string', header: 'Очередность прививки', width: 70, hidden: true},
				{name: 'date_S',  type: 'date', header: 'Дата начала', width: 90},
				{name: 'date_E', type: 'date', header: 'Дата окончания', width: 90}, 
				{name: 'Lpu_Name', type: 'string', header: 'Наименование ЛПУ', width: 200}
			],													
			actions:
			[
				{
					name:'action_edit', 
					text: (getRegionNick().inlist(['perm','penza','krym','astra'])) ? VAC_MENU_PURPOSE : VAC_MENU_PURPOSE_IMPL,
					handler: function()
					{
						params = new Object();   
						//  Назначение прививки
						var record = this.findById('amm_PersonVacPlan').getGrid().getSelectionModel().getSelected();
						if(wnd.viewOnly == false)
							sw.Promed.vac.utils.callVacWindow({
								record: record,
								gridType: 'VacPlan'
								}, 
								this
							);
						
					}.createDelegate(this)							
				},
				{name:'action_add',  hidden: true},
				{name:'action_view',  hidden: true},
				{name:'action_delete',  hidden: true},
				{name:'action_save',  hidden: true}
			]																														
		});
		
		/**
                 *  Назначено /назначенные прививки (вкл. Назначено)/
                 */
                		 
		//назначенные прививки (вкл. Назначено)
		this.ViewFramePersonVacFixed = new sw.Promed.ViewFrame(
		{
			id: 'amm_PersonVacFixed',
                        dataUrl: '/?c=VaccineCtrl&m=searchVacAssigned',
			region: 'center',
			toolbar: true,
			setReadOnly: false,
			autoLoadData: false,
			cls: 'txtwrap',
                        height: 300,
			root: 'data', //добавлен, т.к. в searchVacAssigned исп-ся root='data'
			stringfields:
			[	   							
			{name: 'JournalVacFixed_id', type: 'int', header: 'ID', key: true},
                        {name: 'Date_Purpose', type: 'date', header: 'Дата назначения', width: 70},
                        {name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
                        {name: 'age', type: 'string', header: 'Возраст', width: 50},
                        {name: 'vac_name', type: 'string', header: 'Наименование вакцины', width: 200},
                        {name: 'NAME_TYPE_VAC', type: 'string',  header: VAC_TIT_INFECT_TYPE, id: 'autoexpand'},
                        {name: 'VACCINE_DOZA', type: 'string', header: 'Доза', width: 100}
			,{name: 'Lpu_Name', type: 'string', header: 'Наименование ЛПУ', width: 200}
                        //,{name: 'WAY_PLACE', type: 'string', header: 'Способ и место введения', width: 160}

			],
			listeners: {
				'success': function(source, params) {
					/* source - string - источник события (например форма)
					* params - object - объект со свойствами в завис-ти от источника
					*/
					log('success | ' + source);
					switch(source){
						case 'amm_ImplVacForm':
//              alert('Испонено!');
							Ext.getCmp('amm_PersonVacFixed').ViewGridPanel.getStore().reload();
							Ext.getCmp('amm_PersonVacAccount').ViewGridPanel.getStore().reload();
							break; 
					}
				}
			},
			actions:
			[
			{
				name:'action_edit', 
                                text: VAC_MENU_IMPL,
				handler: function()
				{ 
					//  Исполнение прививки
				 var record = this.findById('amm_PersonVacFixed').getGrid().getSelectionModel().getSelected();
				 if(wnd.viewOnly == false)
					sw.Promed.vac.utils.callVacWindow({
						record: record,
						gridType: 'VacAssigned'
					}, this.findById('amm_PersonVacFixed'));
																													
				}.createDelegate(this)
			},
                            {name:'action_add',  hidden: true},
                            {name:'action_view',  hidden: true},
                            {name:'action_delete',  hidden: true},
                            {name:'action_save',  hidden: true} 
			]
		});      
		
                /**
                 *  исполненные прививки (вкл. Исполнено)
                 */
                
		this.ViewFramePersonVacAccount = new sw.Promed.ViewFrame(
		{
			id: 'amm_PersonVacAccount',
			dataUrl: '/?c=VaccineCtrl&m=searchVacRegistr',
			region: 'center',
			toolbar: true,
			setReadOnly: false,
			autoLoadData: false,
			cls: 'txtwrap',
			height: 300,
			root: 'data',
			stringfields:
			[	   							
				{name: 'vacJournalAccount_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', width: 70, hidden: true},
				{name: 'Date_Vac', type: 'date', header: 'Дата вакцинации', width: 70},
				{name: 'age', type: 'string', header: 'Возраст', width: 50},
				{name: 'vac_name', type: 'string', header: 'Наименование вакцины', width: 180},
				{name: 'NAME_TYPE_VAC', type: 'string', header: VAC_TIT_INFECT_TYPE, id: 'autoexpand'},
				{name: 'Seria', type: 'string', header: 'Серия', width: 100}, //, group: true, sort: true, direction: 'ASC'},
				{name: 'VACCINE_DOZA', type: 'string', header: 'Доза', width: 70},
				{name: 'WAY_PLACE', type: 'string', header: 'Способ и место введения', width: 210},
				{name: 'Lpu_Name', type: 'string', header: 'Наименование ЛПУ', width: 200},
				{name: 'NotifyReaction_createDate', type: 'string', hidden: true},
				{name: 'NotifyReaction_id', type: 'string', header: 'Извещение', width: 200, hidden: !getRegionNick().inlist(['perm', 'astra', 'penza', 'krym']), renderer: function(v, p, record){
					var id = record.get('NotifyReaction_id');
					var NotifyReaction_createDate = record.get('NotifyReaction_createDate');
					var str = '';
					if(id && NotifyReaction_createDate){
						str = '<a href="#" onclick="getWnd(\'swVaccinationNoticeWindow\').show({vacJournalAccount_id: '+record.get('vacJournalAccount_id')+', NotifyReaction_id: '+id+'});">'+NotifyReaction_createDate+'</a>';
					}
					return str;
				}.createDelegate()}
			],
			onRowSelect: function(sm,rowIdx,record) {
				if(getRegionNick().inlist(['perm', 'astra', 'penza', 'krym'])) {
					if (record.get('NotifyReaction_id')) {
						this.ViewFramePersonVacAccount.getAction('notice_add').disable();
						this.ViewFramePersonVacAccount.getAction('notice_del').enable();
					} else {
						this.ViewFramePersonVacAccount.getAction('notice_add').enable();
						this.ViewFramePersonVacAccount.getAction('notice_del').disable();
					}
				}
			}.createDelegate(this),
			actions:
			[
				{
					name:'action_edit', 
					text: VAC_MENU_EDIT,
					handler: function()
					{ // Редактирование исполненной прививки 
						var record = this.findById('amm_PersonVacAccount').getGrid().getSelectionModel().getSelected();
						if(wnd.viewOnly == false)
							sw.Promed.vac.utils.callVacWindow({
								record: record,
								gridType: 'VacRegistr'
							}, this);
					}.createDelegate(this)
				},			
				{name:'action_add',  hidden: true},
				{ // Удаление исполненной прививки
                            name:'action_delete',
                            handler: function()
                            {
                                    sw.swMsg.show({
                                            buttons: Ext.Msg.YESNO,
                                            fn: function(buttonId, text, obj) {
                                                    if ( buttonId === 'yes' ) {
                                                            var record = this.findById('amm_PersonVacAccount').getGrid().getSelectionModel().getSelected();
                                                            var params = {
                                                                    // 'person_id': record.get('Person_id'),
                                                                    'parent_id': 'amm_Kard063',
                                                                    'vacJournalAccount_id': record.get('vacJournalAccount_id')
                                                            };

                                                            Ext.Ajax.request({
                                                                    url: '/?c=VaccineCtrl&m=deletePrivivImplement',
                                                                    method: 'POST',
                                                                    params: params,
                                                                    success: function(response, opts) {
                                                                            sw.Promed.vac.utils.consoleLog(response);
                                                                            if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                                                                                    Ext.getCmp(params.parent_id).fireEvent('success', 'amm_PurposeVacForm',
                                                                                    { }
                                                                                    );
                                                                            }
            //                form.hide();
                                                                    }
                                                            });
                                                    }
                                            }.createDelegate(this),
                                            icon: Ext.MessageBox.QUESTION,
                                            msg: 'Удалить прививку? После удаления необходимо переформировать персональный план!',
                                            title: 'Удаление прививки'
                                    });

                            }.createDelegate(this)
                    },
                    {name:'action_save',  hidden: true},
                    {name:'action_view',  hidden: true}
]
		});
                
                 /**
                 *  Прочие прививки
                 */
               
		this.ViewFramePersonVacOther = new sw.Promed.ViewFrame(
		{
                    id: 'amm_PersonVacOther',
                    dataUrl: '/?c=VaccineCtrl&m=getPersonVacOther',
                    region: 'center',
                    toolbar: true,
                    setReadOnly: false,
                    autoLoadData: false,
                    cls: 'txtwrap',
                    grouping: true,
                    height: 300,
                    //root: 'data',
                    stringfields:
                    [	   							
                        {name: 'Inoculation_id', type: 'int', header: 'ID', key: true},
                        {name: 'vacJournalAccount_id', type: 'int', header: 'ID', hidden: true},
                        {name: 'StatusType_id', type: 'int', header: 'ИД статуса', hidden: true},
                        {name: 'StatusType_Name', type: 'string', header: 'Статус', width: 130},
                        {name: 'VaccineType_Name', type: 'string', header: VAC_TIT_INFECT_TYPE, width: 180,  group: true, sort: true, direction: 'ASC'},
                        {name: 'typeName', type: 'string', header: VAC_TIT_VACTYPE_NAME, width: 70, sortable: false},
                        {name: 'Person_id', type: 'int', header: 'Person_id', width: 70, hidden: true},
			{name: 'date_vac', type: 'date', header: 'Дата', width: 90},
                        {name: 'age', type: 'string', header: 'Возраст', width: 70},
                        {name: 'typeName', type: 'string', header: VAC_TIT_VACTYPE_NAME, width: 70},
                        {name: 'Vaccine_Name', type: 'string', header: 'Наименование вакцины',id: 'autoexpand'},
                        {name: 'VaccineType_id', type: 'int', header: 'ID', hidden: true},
                        {name: 'Seria', type: 'string', header: 'Серия', width: 100},
                        {name: 'VACCINE_DOZA', type: 'string', header: 'Доза', width: 70},
                        {name: 'WAY_PLACE', type: 'string', header: 'Способ и место введения', width: 300}
                        //,{name: 'Lpu_Name', type: 'string', header: 'Наименование ЛПУ', width: 200}
                    ]
                    ,
                    onLoadData: function(sm, index, record)
			{
				
                                if ((!this.getGrid().getStore().totalLength) || (!this.getGrid().getSelectionModel().selections.items [0].data.VaccineType_id))
                                {
					this.getGrid().getStore().removeAll();
				}
                               // alert((Ext.get(this.getGrid().getView().getGroupId('(Пусто)'))).length);
                               
				if( Ext.get(this.getGrid().getView().getGroupId('(Пусто)')) != null ) {
					this.getGrid().getView().toggleGroup(this.getGrid().getView().getGroupId('(Пусто)'), false);
				}
			},
                    actions:
                    [
                    
                
                    {
                            name:'action_add',
                                    //'action_edit', 
                            //text: VAC_MENU_EDIT,
                            handler: function()
                            { // Редактирование исполненной прививки 
                                var record = {
						'person_id': Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id')
					};
                                /*        
                                var record =
                                            this.findById('amm_PersonVacOther').getGrid().getSelectionModel().getSelected();
                                
                                var params = new Object();
                                params.status_type_id = record.get('StatusType_id');
          // params.row_plan_parent = 1; //пойдет в проц-ру исполнения
          // if (record.get('idInCurrentTable') != undefined) {
            params.plan_id = record.get('idInCurrentTable');
            // if (record.get('Vac_Scheme_id') != undefined && params.plan_id == -1) {
//              params.vac_scheme_id = record.get('Vac_Scheme_id');
							params.vac_scheme_id = record.get('Scheme_id');
              params.row_plan_parent = 0; //пойдет в проц-ру исполнения
            // }
          // } else {
          //   params.plan_id = record.get('planTmp_id');
          // }
					params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_plan'));
					if (record.get('Scheme_num') != undefined) {
						params.scheme_num = record.get('Scheme_num');
					}
					sw.Promed.vac.utils.consoleLog(record.get('Date_plan'));
          sw.Promed.vac.utils.consoleLog('params:');
          sw.Promed.vac.utils.consoleLog(params);
          
          // if (sw.Promed.vac.utils.isValInArray(obj.processedRecords, params.plan_id)) {
          //   return sw.Promed.vac.utils.msgBoxProcessedRecords();
          // }
        
                               // Ext.apply(params, 'VacNoInfo');
                                  //  getWnd('amm_QuikImplVacOtherForm').show();
                                 */   
                                    sw.Promed.vac.utils.callVacWindow({
                                            record: record,
                                            type1: 'btnForm',
                                            gridType: 'VacOther'
                                                    //'VacRegistr'
                                    }, this);

                            }.createDelegate(this)
                    }
                    ,			
                    {
                            name:'action_edit', 
                            text: VAC_MENU_EDIT,
                            handler: function()
                            { // Редактирование исполненной прививки 
                                    var record = this.findById('amm_PersonVacOther').getGrid().getSelectionModel().getSelected();
                                    if(wnd.viewOnly == false)
	                                    sw.Promed.vac.utils.callVacWindow({
	                                            record: record,
	                                            gridType: 'VacRegistr'
	                                    }, this);

                            }.createDelegate(this)
                    },	
                            
                    { // Удаление  прививки
                            name:'action_delete',
                            handler: function()
                            {
                                    sw.swMsg.show({
                                            buttons: Ext.Msg.YESNO,
                                            fn: function(buttonId, text, obj) {
                                                    if ( buttonId === 'yes' ) {
                                                            var record = this.findById('amm_PersonVacOther').getGrid().getSelectionModel().getSelected();
                                                            var params = {
                                                                    // 'person_id': record.get('Person_id'),
                                                                    'parent_id': 'amm_Kard063',
                                                                    'vacJournalAccount_id': record.get('vacJournalAccount_id')
                                                            };

                                                            Ext.Ajax.request({
                                                                    url: '/?c=VaccineCtrl&m=deletePrivivImplement',
                                                                    method: 'POST',
                                                                    params: params,
                                                                    success: function(response, opts) {
                                                                            sw.Promed.vac.utils.consoleLog(response);
                                                                            if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                                                                                    //Ext.getCmp('amm_Kard063').fireEvent('success', 'amm_QuikImplVacOtherForm');
                                                                                    Ext.getCmp(params.parent_id).fireEvent('success', 'amm_QuikImplVacOtherForm');
                                                                            }
            //                form.hide();
                                                                    }
                                                            });
                                                    }
                                            }.createDelegate(this),
                                            icon: Ext.MessageBox.QUESTION,
                                            msg: 'Удалить прививку? ',
                                            title: 'Удаление прививки'
                                    });

                            }.createDelegate(this)
                    },
                    {name:'action_save',  hidden: true},
                    {name:'action_view',  hidden: true} 
                   
]
		});
                
                //**************
             
                this.ViewFramePersonVacOther.getGrid().view = new Ext.grid.GroupingView(

                     
		{
			//Далее два свойства (enableGroupingMenu и enableNoGroups) - пока что скрываем в меню столбца 
			//пункты, связанные с группировкой (на будущее - можно с ними поиграться (смена столбца группировки,
			//включение/выключение группировки...)):
			enableGroupingMenu: false,
			enableNoGroups: false,
			
			showGroupName: false,
			showGroupsText: true,
			groupByText: 'NationalCalendarVac_vaccineTypeName',
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? (values.rs.length>4 ? "записей" : "записи") : "запись"]})',

			getRowClass : function (row, index){
                            var arrCls = [];
//                            arrCls.push('x-grid-rowgreen');
//                            arrCls.push('x-grid-rowbold');
//                            return arrCls.join(' ');
//                        }

				
				//var arrCls = [];
                                //sw.Promed.vac.utils.consoleLog(row.get('StatusType_id'));
				switch (row.get('StatusType_id')){
					case 0:// назначено
                                    //arrCls.push('x-grid-rowblue');		
                                    arrCls.push('x-grid-rowbold');
						break;
					case 1:// исполнено
                                                    arrCls.push('x-grid-rowgreen');
                                                    arrCls.push('x-grid-rowbold');
						break; 
 
				
			}
                       return arrCls.join(' ');
		}
                });


                //********************
                
                /**
                 *  Отказы, отводы, согласия
                 */
                		
		this.ViewFramePersonMedTapRefusal = new sw.Promed.ViewFrame(
		{
			id: 'amm_PersonMedTapRefusal',
			dataUrl: '/?c=VaccineCtrl&m=searchVacRefuse',
			region: 'center',
			toolbar: true,
			setReadOnly: false,
			autoLoadData: false,
			cls: 'txtwrap',
			height: 300,
			root: 'data',
			stringfields: [	   							
				{name: 'vacJournalMedTapRefusal_id', type: 'int', header: 'ID', key: true},
				{name: 'DateBegin', type: 'date', header: 'Начало периода', width: 90},
				{name: 'DateEnd', type: 'date', header: 'Окончание периода', width: 90},
				{name: 'VaccineType_Name', type: 'string', header: VAC_TIT_INFECT_TYPE, width: 100},
				{name: 'Reason', type: 'string', header: 'Причина отвода/отказа', id: 'autoexpand'},
				{name: 'type_rec', type: 'string', header: 'Тип записи', width: 200},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
				{name: 'Lpu_Name', type: 'string', header: 'ЛПУ', width: 200},
				{name: 'DateRefusalSave', type: 'date', header: 'Дата записи', width: 90}
			],
			actions : [
				{
				name:'action_add',
				handler: function() {
					var params  = new Object();
					params.Person_id =  Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id');
					sw.Promed.vac.utils.consoleLog('Сформировать отказ/отвод/согласие');
					var record = {
						'person_id': Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id')
					};
					sw.Promed.vac.utils.consoleLog(record);
					sw.Promed.vac.utils.consoleLog('- до вызова формы');
					sw.Promed.vac.utils.callVacWindow({
						record: record,
						type1: 'btnForm',
						type2: 'btnFormRefuse'
					}, this);
					Ext.getCmp('amm_PersonMedTapRefusal').ViewGridPanel.getStore().reload();
				}.createDelegate(this)
				},
				{
				name:'action_edit',
				text: VAC_MENU_EDIT,
				handler: function() {
					var record = this.findById('amm_PersonMedTapRefusal').getGrid().getSelectionModel().getSelected();
					var params = {
						'person_id': record.get('Person_id'),
						'refuse_id': record.get('vacJournalMedTapRefusal_id')
					};
					sw.Promed.vac.utils.consoleLog('редактирование отказа:');
					sw.Promed.vac.utils.consoleLog(record.data);
					sw.Promed.vac.utils.consoleLog('- до вызова формы');
					if(wnd.viewOnly == false)
						sw.Promed.vac.utils.callVacWindow({
							record: params,
							type1: 'btnForm',
							type2: 'btnFormRefuse'
						}, this);
				}.createDelegate(this)
				},
				{
					name:'action_delete',
					handler: function() {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									
									var record = this.findById('amm_PersonMedTapRefusal').getGrid().getSelectionModel().getSelected();
									var params = {
										'refuse_id': record.get('vacJournalMedTapRefusal_id')
									};
									Ext.Ajax.request({
										url: '/?c=VaccineCtrl&m=deletePrivivRefuse',
										method: 'POST',
										params: params,

										success: function(response, opts) {
											sw.Promed.vac.utils.consoleLog(response);
											if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
												Ext.getCmp('amm_Kard063').fireEvent('success', 'amm_RefuseVacForm');
											}
										}.createDelegate(this),
										failure: function(response, opts) {
											sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
										}
									});
									
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: 'Удалить медотвод?',
							title: 'Удаление медотвода'
						});
					}.createDelegate(this)
				},
				{name:'action_view',  hidden: true},
				{name:'action_save',  hidden: true} 			
			]
		}); 
		
                
                //********************
                				
		/*
		 * вкладка "Группа риска"
		 */
		this.FormPanelPersonRiskGroup = new Ext.form.FormPanel({
			id: 'FormPanelPersonRiskGroup',

					autoHeight: true,
//					style: 'padding-left: 120px; font-size: 12px',
//					style: 'padding: 0px 50% 0px 10px; font-size: 12px',
					style: 'padding: 0px 10px; font-size: 12px',
					border: false,
					region: 'center',
					//cls: 'x-panel-mc',
//					layout: 'form',
						
						items: [
						{
							height : 20,
							border : false,
							cls: 'tg-label'
						},
						{
							autoHeight: true,
							autoScroll: true,
							style: 'padding: 0px 5px;',
							title: 'Имеющиеся группы риска',
							height: 80,
							labelWidth: 30,
							xtype: 'fieldset',
							layout: 'form',
//							id: 'autoexpand',
							items: [
									
								{
									xtype: 'checkbox',
									height:24,
//									tabIndex: 12,//TABINDEX_PRESVACEDITFRM + 1,
									checked: false,
									labelSeparator: '',
									vacType: 1,
									name: 'vacType1',
									cls: 'vacTypeCheckbox',
									boxLabel: 'Риск по Гепатиту'
								},
								{
									xtype: 'checkbox',
									height:24,
//									tabIndex: 15,//TABINDEX_PRESVACEDITFRM + 2,
									checked: false,
									labelSeparator: '',
									vacType: 2,
									name: 'vacType2',
									cls: 'vacTypeCheckbox',
									boxLabel: 'Риск по Туберкулезу'
								},
								{
									xtype: 'checkbox',
									height:24,
//									tabIndex: 15,//TABINDEX_PRESVACEDITFRM + 2,
									checked: false,
									labelSeparator: '',
									vacType: 10,
									name: 'vacType10',
									cls: 'vacTypeCheckbox',
									boxLabel: 'Риск по Гемофилии'
								}
									
							]
						}]

					,buttons : [{
						text : "Сохранить",
						iconCls: 'save16',
						handler: function() {
//									Ext.each(Ext.query('input.vacTypeCheckbox'), function( item ) {
//										alert(item.vacType);
//									});
							var item = {};
							var itemArr = [];
							item.personId = Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id');
							item.vaccineTypeId = 1;
							var riskGroupPanel = Ext.getCmp('FormPanelPersonRiskGroup');
							var findedField = riskGroupPanel.form.findField('vacType'+item.vaccineTypeId);
							item.value = findedField.getValue();
							item.vaccineRiskId = findedField.initialConfig.vaccineRiskId;
							sw.Promed.vac.utils.consoleLog('item.vaccineRiskId:');
							sw.Promed.vac.utils.consoleLog(item.vaccineRiskId);
							riskGroupPanel.vacType1.setChanged({val: findedField.getValue()});
							if (riskGroupPanel.vacType1.getIsChanged())	
								itemArr.push(item);

							item = {};
							item.personId = Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id');
							item.vaccineTypeId = 2;
							findedField = riskGroupPanel.form.findField('vacType'+item.vaccineTypeId);
							item.value = findedField.getValue();
							item.vaccineRiskId = findedField.initialConfig.vaccineRiskId;
							sw.Promed.vac.utils.consoleLog('item.vaccineRiskId:');
							sw.Promed.vac.utils.consoleLog(item.vaccineRiskId);
							riskGroupPanel.vacType2.setChanged({val: findedField.getValue()});
							if (riskGroupPanel.vacType2.getIsChanged())	
								itemArr.push(item);

							item = {};
							item.personId = Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id');
							item.vaccineTypeId = 10;
							findedField = riskGroupPanel.form.findField('vacType'+item.vaccineTypeId);
							item.value = findedField.getValue();
							item.vaccineRiskId = findedField.initialConfig.vaccineRiskId;
							sw.Promed.vac.utils.consoleLog('item.vaccineRiskId:');
							sw.Promed.vac.utils.consoleLog(item.vaccineRiskId);
							riskGroupPanel.vacType10.setChanged({val: findedField.getValue()});
							if (riskGroupPanel.vacType10.getIsChanged())	
								itemArr.push(item);

							sw.Promed.vac.utils.consoleLog('itemArr:');
							sw.Promed.vac.utils.consoleLog(itemArr[0]);
							sw.Promed.vac.utils.consoleLog(itemArr[1]);
							sw.Promed.vac.utils.consoleLog(itemArr[2]);
							this.FormPanelPersonRiskGroup.saveVaccineRisk(itemArr);
							riskGroupPanel.vacType1.setFiltr();
							riskGroupPanel.vacType2.setFiltr();
							riskGroupPanel.vacType10.setFiltr();


						}.createDelegate(this),
						onTabAction : function () {
							//Ext.getCmp('Journals_BottomButtons').buttons[1].focus(false, 0);
							//this.findForm().getForm().findField('vacType1').focus(false, 0);
							Ext.getCmp('FormPanelPersonRiskGroup').getForm().findField('vacType1').focus(false, 0);
						},
						//}.createDelegate(this),
						onShiftTabAction : function () {
						},
						tabIndex : TABINDEX_VACMAINFRM + 30
					}, {
						text : '-'
					}]
		});
		
		/*
		* метод для записи изменений в таблицу "Группы рисков"
		* arr - массив объектов [{value, personId, vaccineTypeId, vaccineRiskId}, ...]
		*/
		this.FormPanelPersonRiskGroup.saveVaccineRisk = function(arr){
			Ext.each(arr,
				function( item ) {
					if (item.value == 'undefined') return;
					
					var panelPersonRiskGroup = Ext.getCmp('FormPanelPersonRiskGroup');
					if (item.value == 1) {
						
						if ((item.value == 'undefined')||
								(item.personId == 'undefined')||
								(item.vaccineTypeId == 'undefined')) return;
							
						panelPersonRiskGroup.LoadMaskObj.loadMaskShow(panelPersonRiskGroup);
							
						Ext.Ajax.request({
							url: '/?c=VaccineCtrl&m=saveVaccineRisk',
							method: 'POST',
							params: {
									'person_id': item.personId,
									'vaccine_type_id': item.vaccineTypeId
//									'person_id': Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id'),
							},
							success: function(response, opts) {
								sw.Promed.vac.utils.consoleLog(response);
								var result = Ext.util.JSON.decode(response.responseText);
								var panelPersonRiskGroup = Ext.getCmp('FormPanelPersonRiskGroup');
								
								panelPersonRiskGroup.LoadMaskObj.loadMaskHide(panelPersonRiskGroup);

								if ((result.success)&&(sw.Promed.vac.utils.msgBoxErrorBd(response) == 0)) {
									//var findedField = Ext.getCmp('FormPanelPersonRiskGroup').form.findField('vacType'+item.vaccineTypeId);
									var findedField = panelPersonRiskGroup.form.findField('vacType'+item.vaccineTypeId);
									findedField.initialConfig.vaccineRiskId = result.rows[0].VaccineRisk_id;
								}
							}.createDelegate(this),
							failure: function(response, opts) {
								panelPersonRiskGroup.LoadMaskObj.loadMaskHide(panelPersonRiskGroup);
								sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
							}
						});

					} else {
						
						if (item.vaccineRiskId == 'undefined') return;
						panelPersonRiskGroup.LoadMaskObj.loadMaskShow(panelPersonRiskGroup);
						Ext.Ajax.request({
							url: '/?c=VaccineCtrl&m=deleteVaccineRisk',
							method: 'POST',
							params: {
								'vaccine_risk_id': item.vaccineRiskId
							},
							success: function(response, opts) {
								panelPersonRiskGroup.LoadMaskObj.loadMaskHide(panelPersonRiskGroup);
								sw.Promed.vac.utils.consoleLog(response);
								var result = Ext.util.JSON.decode(response.responseText);
								if ((result.success)&&(sw.Promed.vac.utils.msgBoxErrorBd(response) == 0)) {
									var findedField = Ext.getCmp('FormPanelPersonRiskGroup').form.findField('vacType'+item.vaccineTypeId);
									if (findedField != undefined) {
										findedField.initialConfig.vaccineRiskId = "";
									}
								}
							}.createDelegate(this),
							failure: function(response, opts) {
								panelPersonRiskGroup.LoadMaskObj.loadMaskHide(panelPersonRiskGroup);
								sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
							}
						});
						
					}
			});
		}
		
		/*
		* хранилище для доп сведений
		*/
		this.FormPanelPersonRiskGroup.formStore = new Ext.data.JsonStore({
			fields: [
				'VaccineRisk_id',
				'Person_id',
				'VaccineType_id'
			],
			url: '/?c=VaccineCtrl&m=loadVaccineRiskInfo',
			key: 'VaccineRisk_id',
			root: 'data'
		});
                
               
                this.date4gepatiteStore = new Ext.data.JsonStore({
                fields: ['Result_Date'],
                //stringfields: [{Name:'Result_Date', type:'date'}],
                        
                    //{Name:'Result_Date', type:'date'}
                
			url: '/?c=VaccineCtrl&m=VacDateAdd',
			//key: 'vacJournalAccount_id',
			root: 'data'
		});
                
               
		
		this.FormPanelPersonRiskGroup.formParams = {};
				
		this.FormPanelPersonRisk =
		new Ext.form.FormPanel({
			height: 300,
			style: 'padding-left: 120px; font-size: 12px',
			border: false,
			region: 'center',
			items: [{
				height:24,
				border: false
			},
			{
				layout: 'column',
				border: false,
				height:30,
				items: [
				{
					xtype: 'checkbox',
//          height:24,
					tabIndex: TABINDEX_EPLSIF + 1,
					width: 250,
					fieldLabel: ' ',
					name: 'amm_vac_DefeatCNS',
					id: 'amm_vac_DefeatCNS',
					checked: true,
					boxLabel: 'Возможное поражение ЦНС',
					labelSeparator: '  '
				}
				,
				{
					xtype: 'checkbox',
					tabIndex: TABINDEX_EPLSIF + 1,
					width: 200,
					name: 'amm_vac_Allergy',
					id: 'amm_vac_Allergy',
					checked: true,
					boxLabel: 'Аллергические проявления',
					labelSeparator: ''
				}
				]
				} ,
{
				layout: 'column',
				border: false,
				height:30,
				items: [
				{
					xtype: 'checkbox',
					width: 250,
					tabIndex: TABINDEX_EPLSIF + 1,
					name: 'amm_vac_Often',
					id: 'amm_vac_Often',
					checked: true,
					boxLabel: 'Часто болеющий',
					labelSeparator: ''
				},
				{
					xtype: 'checkbox',
					width: 250,
					tabIndex: TABINDEX_EPLSIF + 1,
					name: 'amm_vac_UnusualReactions',
					id: 'amm_vac_UnusualReactions',
					checked: true,
					boxLabel: 'Необычные реакции на прививки',
					labelSeparator: ''
				}
				]
				} ,
{
				layout: 'column',
				border: false,
				height:30,
				items: [
				{
					xtype: 'checkbox',
					width: 250,
					tabIndex: TABINDEX_EPLSIF + 1,
					name: 'amm_vac_RiskTuber',
					id: 'amm_vac_RiskTuber',
					checked: true,
					boxLabel: 'Риск по туберкулезу',
					labelSeparator: ''
				},
				{
					xtype: 'checkbox',
					width: 200,
					tabIndex: TABINDEX_EPLSIF + 1,
					name: 'amm_vac_RiskSpid',
					id: 'amm_vac_RiskSpid',
					checked: true,
					boxLabel: 'Риск ВИЧ',
					labelSeparator: ''
				}
				]
				},  

				{
				layout: 'column',
				border: false,
				height:30,
				items: [
				{
					xtype: 'checkbox',
					//                                        height:24,
					width: 250,
					tabIndex: TABINDEX_EPLSIF + 1,
					name: 'amm_vac_DeficitСondition',
					id: 'amm_vac_DeficitСondition',
					checked: true,
					boxLabel: 'Иммунодефицитное состояние',
					labelSeparator: ''
				},
				{
					xtype: 'checkbox',
					//                                        height:24,
					width: 200,
					tabIndex: TABINDEX_EPLSIF + 1,
					name: 'amm_vac_Dependencies',
					id: 'amm_vac_Dependencies',
					checked: true,
					boxLabel: 'Наличие зависимостей', 
					labelSeparator: ''
				} 
				]
				},                                    

				{
				layout: 'column',
				border: false,
				height:30,
				items: [
				{
					xtype: 'checkbox',
					//                                            height:24,
					width: 250,
					tabIndex: TABINDEX_EPLSIF + 1,
					name: 'amm_vac_Invalid',
					id: 'amm_vac_Invalid',
					checked: true,
					boxLabel: 'Оформлена инвалидность',
					labelSeparator: ''
				} ,
{
					xtype: 'checkbox',
					//                                            height:24,
					width: 200,
					tabIndex: TABINDEX_EPLSIF + 1,
					name: 'amm_vac_HepetitisB',
					id: 'amm_vac_HepetitisB',
					checked: true,
					boxLabel: 'группа риска по гепатиту В',
					labelSeparator: ''
				}   
				]
				}                                                     
			]
		});
	
	
                //*****************
               
                this.tabpanel =  new Ext.TabPanel({
			id: 'amm_kard063_tabpanel', 
			activeTab: 1,
			autoWidth: true,
			autoScroll: true,
                        layoutOnTabChange: true,
                        items: [
                            {
				title: '<u>1</u>. Карта 063',
				layout:'fit',
				items: [
					this.ViewFrame063
				]
				,listeners: {
					'activate': function(p)  {
						this.ViewFrame063.focus();
					}.createDelegate(this)
				}
                            },
                            {
				title: '<u>2</u>. Манту/Диаскинтест',
				Id: 'amm_TabPersonPersonMantu',
				layout:'fit',
				items: [                          
					this.ViewFramePersonMantu
				]
				,listeners: {
					'activate': function(p)  {
//						sw.Promed.vac.utils.consoleLog('activate Реакция манту!');
						this.ViewFramePersonMantu.focus();
					}.createDelegate(this)
				}
                            },
                            {
				title: '<u>3</u>. Запланировано',
				labelWidth : 150,
				layout:'fit',
				items: [
					this.ViewFramePersonVacPlan 
				]
				,listeners: {
					'activate': function(p)  {
//						sw.Promed.vac.utils.consoleLog('activate Планирование!');
						this.ViewFramePersonVacPlan.focus();
					}.createDelegate(this)
				}
                            },
                            { 
				title: '<u>4</u>. Назначено',
				layout:'fit',
				items: [                          
					this.ViewFramePersonVacFixed
				]
				,listeners: {
					'activate': function(p)  {
//						sw.Promed.vac.utils.consoleLog('activate Назначено!');
						this.ViewFramePersonVacFixed.focus();
					}.createDelegate(this)
				}
                            },
                            {                       
				title: '<u>5</u>. Исполнено',
				layout:'fit',
				items: [                          
					this.ViewFramePersonVacAccount
				]
				,listeners: {
					'activate': function(p)  {
//						sw.Promed.vac.utils.consoleLog('activate Исполнено!');
						this.ViewFramePersonVacAccount.focus();
					}.createDelegate(this)
				}
                            },
                            {
				title: '<u>6</u>. Отказы, отводы, согласия',
				layout:'fit',
				items: [                          
					this.ViewFramePersonMedTapRefusal
				]
				,listeners: {
					'activate': function(p)  {
//						sw.Promed.vac.utils.consoleLog('activate Отказы, отводы, согласия!');
						this.ViewFramePersonMedTapRefusal.focus();
					}.createDelegate(this)
				}
                            },
                            {
				title: '<u>7</u>. Группы риска',     
				layout:'fit',
				cls: 'x-panel-mc',
				items: [
					this.FormPanelPersonRiskGroup
				]
                            },
                            {
				title: '<u>8</u>. Прочие прививки',     
				layout:'fit',
				cls: 'x-panel-mc',
				items: [
					this.ViewFramePersonVacOther
				]
                            }
                            
                        ]
		});

		Ext.apply(this, {

			buttons: [
			{
				text: 'Сформировать план',
				iconCls: 'inj-stream16',
				id: 'Vac_FormPlan',
				disabled: false,
				handler: function() {
					/*
					*  параметры формирования: текущая дата, текущая дата + год
					**/
					var dt = new Date();
					var dt2 = new Date();
					dt2.setUTCFullYear(dt2.format('Y') + 1);

					sw.Promed.vac.utils.callVacWindow({
						record: {
							dt: dt.format('d.m.Y'),
							dt2: dt2.format('d.m.Y'),
							person_id: Ext.getCmp('Kart063_PersonInfoFramea').getFieldValue('Person_id')
						},
						type1: 'btnForm',
						type2: 'btnFormPlanParams'
					}, this);

				}
			},

			{
				text: '-'
			},
                                HelpButton(this, TABINDEX_LISTTASKFORMVAC + 3),

				{
					iconCls: 'print16',
					text: 'Печать карты 063',
					handler: function() {
						var paramPerson = wnd.PersonInfoPanel.getFieldValue('Person_id');
						printBirt({
							'Report_FileName': 'ProfInoculationsCard063.rptdesign',
							'Report_Params': '&paramPerson=' + paramPerson,
							'Report_Format': 'pdf'
						});
					}
				}, {
					iconCls: 'print16',
					hidden: getRegionNick() != 'vologda',
					text: 'Сертификат о профилактических прививках',
					handler: function() {
						var paramPerson = wnd.PersonInfoPanel.getFieldValue('Person_id');
						printBirt({
							'Report_FileName': 'CertificatePrint_Vaccinations.rptdesign',
							'Report_Params': '&paramPerson=' + paramPerson,
							'Report_Format': 'pdf'
						});
					}
				}, {
					iconCls: 'print16',
					text: 'Печать плана прививок',
					handler: function() {
						var paramPerson = wnd.PersonInfoPanel.getFieldValue('Person_id');
						printBirt({
							'Report_FileName': 'vac_Plan4Person.rptdesign',
							'Report_Params': '&paramPerson=' + paramPerson,
							'Report_Format': 'pdf'
						});
					}
				},

			{
				handler: function() {
					this.hide();
                                        Ext.getCmp('amm_kard063_tabpanel').setActiveTab(0);
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EPLSIF_CancelButton',
				onTabAction: function () {
					//this.findById('EPLSIF_EvnVizitPL_setDate').focus(true, 100);
					Ext.getCmp('amm_kard063_tabpanel').getActiveTab().setFocus();
				}.createDelegate(this),
                                tabIndex: TABINDEX_EPLSIF + 15,
//				onTabAction: function () {
//					Ext.getCmp('Kard063_SprInoculation').focus();
//				}.createDelegate(this),
				text: '<u>З</u>акрыть'
			} ],

			layout: 'border',
			items: [
                            this.PersonInfoPanel,
                            {
                                layout: 'fit',
				region: 'center',
				items: [
					this.tabpanel
				]

			}]
		}) ;
								
		sw.Promed.amm_Kard063.superclass.initComponent.apply(this, arguments);},
	
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var tabbar063 = Ext.getCmp('amm_kard063_tabpanel');

			switch ( e.getKey() ) {
				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					tabbar063.setActiveTab(0);
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					tabbar063.setActiveTab(1);
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					tabbar063.setActiveTab(2);
//					tabbar063.getActiveTab().fireEvent('activate', tabbar063.getActiveTab());
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					tabbar063.setActiveTab(3);
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					tabbar063.setActiveTab(4);
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					tabbar063.setActiveTab(5);
				break;
			}
		},
		key: [
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.SIX,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.FOUR,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.THREE,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.TWO,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.ONE
		],
		stopEvent: true
	}],
	
	show: function(record) {
		var flagShow = true;	
		
		sw.Promed.amm_Kard063.superclass.show.apply(this, arguments);
		/*
		 * Пока убрал
		if (Person_kard063 != undefined) {
		    if (Person_kard063 == arguments[0].person_id)
			flagShow = false;
		}
		*/
		
		Person_kard063 = arguments[0].person_id;
		var params = new Object();
		params.Person_id = arguments[0].person_id;
		if (!params.Person_id) {
			Ext.Msg.alert('Ошибка', 'Не идентифицирован пациент!');
			this.hide();
			return false;
		}
		this.viewOnly = false;
		if(!Ext.isEmpty(record.viewOnly))
			this.viewOnly = record.viewOnly;
		params.SearchFormType = 'Card63';
		if (!arguments[0].age)
			params.Age = 17;
		else
			params.Age = arguments[0].age;
                 
                // Инициализируем дату для контроля интервала между первой и второй вакцинацией для гепатита
                 var date4gepatite  = this.date4gepatiteStore;   
                date4gepatite.load({
			params: {
				BaseDate:  getGlobalOptions().date,
				Type: 2,
                                Add_Num: -6                               
			}
                        ,
			callback: function(){   
                                var obj = date4gepatite.getAt(0);
				//Control_Gepatity_DT = obj.get('Result_Date');
                                Control_Gepatity_DT = new Date (obj.get('Result_Date').date.substring(0, 10).replace(/-/g, ','))
                                sw.Promed.vac.utils.consoleLog('Result_Date='+obj.get('Result_Date'));   
                        }  
               });
               
		var wnd = this;
		Ext.getCmp('Kart063_PersonInfoFramea').load({
			callback: function() {
				Ext.getCmp('Kart063_PersonInfoFramea').setPersonTitle();
                                 Person_deadDT = Ext.getCmp('amm_Kard063').PersonInfoPanel.getFieldValue('Person_deadDT'); 
//                                
                                if ((wnd.viewOnly==true) || (Person_deadDT != undefined && Person_deadDT != '')) {
                                    Ext.getCmp('Vac_FormPlan').setDisabled(true);
                                    //alert('1');
                                    Ext.getCmp('amm_Person063').ViewActions.action_edit.setHidden(true);
                                    Ext.getCmp('amm_PersonPersonMantu').ViewActions.action_add.setHidden(true)
                                    Ext.getCmp('amm_PersonPersonMantu').ViewActions.action_edit.setHidden(true);
                                    Ext.getCmp('amm_PersonPersonMantu').ViewActions.action_delete.setHidden(true);
                                    Ext.getCmp('amm_PersonVacAccount').ViewActions.action_edit.setHidden(wnd.viewOnly);
                                    Ext.getCmp('amm_PersonVacAccount').ViewActions.action_delete.setHidden(wnd.viewOnly);
                                    Ext.getCmp('amm_PersonMedTapRefusal').ViewActions.action_add.setHidden(wnd.viewOnly);
                                    Ext.getCmp('amm_PersonMedTapRefusal').ViewActions.action_edit.setHidden(wnd.viewOnly);
                                    Ext.getCmp('amm_PersonMedTapRefusal').ViewActions.action_delete.setHidden(wnd.viewOnly);
                                    Ext.getCmp('amm_PersonVacOther').ViewActions.action_add.setHidden(wnd.viewOnly);
                                    Ext.getCmp('amm_PersonVacOther').ViewActions.action_edit.setHidden(wnd.viewOnly);
                                    Ext.getCmp('amm_PersonVacOther').ViewActions.action_delete.setHidden(wnd.viewOnly);
                                    Ext.getCmp('FormPanelPersonRiskGroup').setDisabled(wnd.viewOnly);
                                    //amm_PersonVacOther
                                    //Ext.getCmp('amm_PersonVacPlan').ViewActions.action_add.setHidden(true)
                                    Ext.getCmp('amm_PersonVacPlan').ViewActions.action_edit.setHidden(true);
                                    Ext.getCmp('amm_PersonVacPlan').ViewActions.action_delete.setHidden(true);
                                    
                                    //Ext.getCmp('amm_PersonVacFixed').ViewActions.action_add.setHidden(true)
                                    Ext.getCmp('amm_PersonVacFixed').ViewActions.action_edit.setHidden(true);
//                                    
                                }
                                else {
                                    Ext.getCmp('Vac_FormPlan').setDisabled(false);
                                    Ext.getCmp('amm_PersonPersonMantu').ViewActions.action_add.show();
                                    Ext.getCmp('amm_PersonPersonMantu').ViewActions.action_edit.show();
                                    
                                    //Ext.getCmp('amm_PersonVacPlan').ViewActions.action_add.show();
                                    Ext.getCmp('amm_PersonVacPlan').ViewActions.action_edit.show();
                                    
                                    //Ext.getCmp('amm_PersonVacFixed').ViewActions.action_add.show();
                                    Ext.getCmp('amm_PersonVacFixed').ViewActions.action_edit.show();
//                                    Ext.getCmp('amm_PersonPersonMantu').ViewActions.action_add.setDisabled(false);
//                                    sw.Promed.vac.utils.consoleLog('Person_dead = 0');
                                }
			}.createDelegate(this),
			loadFromDB: true,
			Person_id: params.Person_id    
			,Server_id: record.Server_id
		});
		
		Ext.getCmp('amm_kard063_tabpanel').setActiveTab(7);
		Ext.getCmp('amm_kard063_tabpanel').setActiveTab(6);
		Ext.getCmp('amm_kard063_tabpanel').setActiveTab(5);
		Ext.getCmp('amm_kard063_tabpanel').setActiveTab(4);
		Ext.getCmp('amm_kard063_tabpanel').setActiveTab(3);
		Ext.getCmp('amm_kard063_tabpanel').setActiveTab(2);
		Ext.getCmp('amm_kard063_tabpanel').setActiveTab(1);
		Ext.getCmp('amm_kard063_tabpanel').setActiveTab(0);

		if(getRegionNick().inlist(['perm', 'astra', 'penza', 'krym'])){
			this.ViewFramePersonVacAccount.addActions({
				name:'notice_add', 
				id:'id_notice_add', 
				handler: function() {
					var record =this.ViewFramePersonVacAccount.getGrid().getSelectionModel().getSelected();
					var params = {
						'vacJournalAccount_id': record.get('vacJournalAccount_id')
					};
					getWnd('swVaccinationNoticeWindow').show({'vacJournalAccount_id': record.get('vacJournalAccount_id'), cbFn: function(){
						this.ViewFramePersonVacAccount.getGrid().getStore().reload();
					}.bind(this)});
				}.createDelegate(this),
				hidden: false, 
				disabled: false, 
				text:'Извещение', 
				tooltip: 'добавить извещение'
			});
			this.ViewFramePersonVacAccount.addActions({
				name:'notice_del', 
				id: 'id_notice_del', 
				handler: function() {
					var record =this.ViewFramePersonVacAccount.getGrid().getSelectionModel().getSelected();
					if(!record.get('NotifyReaction_id')) return false;
					var params = {
						'NotifyReaction_id': record.get('NotifyReaction_id')
					};
					Ext.Ajax.request({
						params: params,
						url: '/?c=VaccineCtrl&m=deleteVaccinationNotice',
						success: function(response) {
							this.ViewFramePersonVacAccount.getGrid().getStore().reload();
							Ext.Msg.alert('Информация', 'Извещение удалено');
						}.createDelegate(this),
						failure: function() {
							Ext.Msg.alert('Ошибка', 'При сохранении извещения');
						}
					});
				}.createDelegate(this),
				hidden: false,
				disabled: false, 
				text:'Удалить извещение', 
				tooltip: 'Удалить извещение'
			});
		}
		
		
		if (flagShow) {
			//   Группа риска 
			var riskGroupPanel = Ext.getCmp('FormPanelPersonRiskGroup');
			
			riskGroupPanel.formStore.load({
				params: {
					person_id: params.Person_id
				},
				callback: function(){
	//                               
					//Сброс значений чекбоксов:
					Ext.getCmp('FormPanelPersonRiskGroup').form.reset();
					Ext.getCmp('FormPanelPersonRiskGroup').form.findField('vacType1').initialConfig.vaccineRiskId = "";
					Ext.getCmp('FormPanelPersonRiskGroup').form.findField('vacType2').initialConfig.vaccineRiskId = "";
					Ext.getCmp('FormPanelPersonRiskGroup').form.findField('vacType10').initialConfig.vaccineRiskId = "";

					var formStoreRecord;
					var formStoreCnt = Ext.getCmp('FormPanelPersonRiskGroup').formStore.getCount();
					for (var i=0; i<formStoreCnt; i++){
						formStoreRecord = Ext.getCmp('FormPanelPersonRiskGroup').formStore.getAt(i);
											var vaccineTypeId = formStoreRecord.get('VaccineType_id');
						var findedField = Ext.getCmp('FormPanelPersonRiskGroup').form.findField('vacType'+vaccineTypeId);
						if (findedField != undefined) {
							sw.Promed.vac.utils.consoleLog('Ext.getCmp(FormPanelPersonRiskGroup).form.findField(vacType+vaccineTypeId):');
							sw.Promed.vac.utils.consoleLog(Ext.getCmp('FormPanelPersonRiskGroup').form.findField('vacType'+vaccineTypeId));
							findedField.initialConfig.vaccineRiskId = formStoreRecord.get('VaccineRisk_id');
							findedField.setValue(1);
												} else {
							
						}
										}
					
					if ((riskGroupPanel.vacType1 == undefined)||(riskGroupPanel.vacType2 == undefined)||(riskGroupPanel.vacType10 == undefined)) {
						riskGroupPanel.vacType1 = sw.Promed.vac.utils.getFiltrObj();
						riskGroupPanel.vacType2 = sw.Promed.vac.utils.getFiltrObj();
						riskGroupPanel.vacType10 = sw.Promed.vac.utils.getFiltrObj();
					}
					riskGroupPanel.vacType1.setFiltr({val: riskGroupPanel.form.findField('vacType1').getValue()});
					riskGroupPanel.vacType2.setFiltr({val: riskGroupPanel.form.findField('vacType2').getValue()});
					riskGroupPanel.vacType10.setFiltr({val: riskGroupPanel.form.findField('vacType10').getValue()});

				}.createDelegate(this)
			});
					//Ext.getCmp('amm_kard063_tabpanel').setActiveTab(6);
			 riskGroupPanel.LoadMaskObj = sw.Promed.vac.utils.getLoadMaskObj();
		
			
			var tab063 = Ext.getCmp('amm_Person063');
			tab063.ViewGridPanel.getStore().baseParams = params;
			tab063.ViewGridPanel.getStore().reload({
						callback: function(){
							tab063.updateContextMenu();
						}
					});
					
					
					Ext.getCmp('amm_PersonPersonMantu').ViewGridPanel.getStore().baseParams = params;
			Ext.getCmp('amm_PersonPersonMantu').ViewGridPanel.getStore().reload();
					
					
					Ext.getCmp('amm_PersonVacPlan').ViewGridPanel.getStore().baseParams = params;
			Ext.getCmp('amm_PersonVacPlan').ViewGridPanel.getStore().reload();
					
					Ext.getCmp('amm_PersonVacFixed').ViewGridPanel.getStore().baseParams = params;
			Ext.getCmp('amm_PersonVacFixed').ViewGridPanel.getStore().reload();
					
					Ext.getCmp('amm_PersonVacAccount').ViewGridPanel.getStore().baseParams = params;
			Ext.getCmp('amm_PersonVacAccount').ViewGridPanel.getStore().reload();
								 
					Ext.getCmp('amm_PersonMedTapRefusal').ViewGridPanel.getStore().baseParams = params;
			Ext.getCmp('amm_PersonMedTapRefusal').ViewGridPanel.getStore().reload();
					
					Ext.getCmp('amm_PersonVacOther').ViewGridPanel.getStore().baseParams = params;
			Ext.getCmp('amm_PersonVacOther').ViewGridPanel.getStore().reload();

								 
			tab063.getGrid().on(
				'cellclick',
							tab063.updateContextMenu
			);
			tab063.getGrid().on(
				'cellcontextmenu',
				tab063.updateContextMenu
			);
		};
                    //tab063.updateContextMenu();
//                Ext.getCmp('amm_kard063_tabpanel').setActiveTab(6);
//                Ext.getCmp('amm_kard063_tabpanel').setActiveTab(5);
//                Ext.getCmp('amm_kard063_tabpanel').setActiveTab(4);
//                Ext.getCmp('amm_kard063_tabpanel').setActiveTab(3);
//                Ext.getCmp('amm_kard063_tabpanel').setActiveTab(2);
//                Ext.getCmp('amm_kard063_tabpanel').setActiveTab(1);
//                Ext.getCmp('amm_kard063_tabpanel').setActiveTab(0); 
	}
//}
});

