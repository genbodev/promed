/**
* swReceptInCorrectSearchWindow - окно поиска рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      14.05.2009
* @comment      Префикс для id компонентов ERIS (EvnReceptInvalidSearch)
* @comment      tabIndex от 1301 до 1400
*/

var EvnReceptIncorrectSearchFilterForm;
var EvnReceptIncorrectSearchViewGrid;
var EvnReceptIncorrectSearchGridStore;

function SearchIncorrectRecept()
{
	if ( EvnReceptIncorrectSearchFilterForm.isEmpty() )
	{
		sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
		function () { EvnReceptIncorrectSearchFilterForm.getForm().findField(0).focus()});
		return false;
	}

	if (EvnReceptIncorrectSearchFilterForm.getForm().isValid() ) {
		var post = EvnReceptIncorrectSearchFilterForm.getForm().getValues();
		if(post.Lpu_id){
			post.receptLpuId = post.Lpu_id;
			delete post.Lpu_id;
		}
		EvnReceptIncorrectSearchViewGrid.store.removeAll();

		EvnReceptIncorrectSearchViewGrid.getStore().baseParams = EvnReceptIncorrectSearchFilterForm.getForm().getValues();

		post.limit = 100;
		post.start = 0;

		EvnReceptIncorrectSearchViewGrid.store.load({
			params: post,
			callback: function(r, opt ) {
				var len = r.length;
				if ( len > 100 ) { // опа! 101 запись!
					new Ext.ux.window.MessageWindow({
						title: langs('Журнал отсрочки'),
						autoDestroy: true,//default = true
						autoHeight: true,
						autoHide: true,//default = true
						help: false,
						bodyStyle: 'text-align:center',
						closable: false,
						//pinState: null,
						//pinOnClick: false,
						hideFx: {
							delay: 2000,
							//duration: 0.25,
							mode: 'standard',//null,'standard','custom',or default ghost
							useProxy: false //default is false to hide window instead
						},
						html: langs('<br/><b>Найдено больше 100 записей.</b><br/>Показаны первые 100 записей.<br/>Пожалуйста уточните параметры запроса.<br/><br/>'),
						iconCls: 'info16',
						showFx: {
							delay: 0,
							//duration: 0.5, //defaults to 1 second
							mode: 'standard',//null,'standard','custom',or default ghost
							useProxy: false //default is false to hide window instead
						},
						width: 250 //optional (can also set minWidth which = 200 by default)
					}).show(Ext.getDoc());
					EvnReceptIncorrectSearchViewGrid.getStore().removeAt(len - 1);
					len--;
				}
				if ( len > 0 )
				{
					EvnReceptIncorrectSearchViewGrid.focus();
					EvnReceptIncorrectSearchViewGrid.getView().focusRow(0);
					EvnReceptIncorrectSearchViewGrid.getSelectionModel().selectFirstRow();
					// Элементы типа tbtext не берутся по id? o_O
					EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.items[11].el.innerHTML = '1 / ' + len;
				}
				else {
					EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_DeleteReceptBtn').disable();
					EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_EditReceptBtn').disable();
					EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_ViewReceptBtn').disable();
					EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.items[11].el.innerHTML = '0 / 0';
				}
			}
		});
	}
	else {
		Ext.MessageBox.show({
			title: "Проверка данных формы",
			msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.WARNING},
			function () { EvnReceptIncorrectSearchFilterForm.getForm().findField(0).focus()}
		);
	}
}

sw.Promed.swReceptInCorrectSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	getLpuUnitPolkaCount: function(params){
		params = Ext.applyIf(params, {callback: Ext.emptyFn});
		log(params);
		Ext.Ajax.request({
			params: {Lpu_id: getGlobalOptions().lpu_id, LpuUnitType_SysNick: 'polka'},
			url: '/?c=LpuStructure&m=getLpuUnitCountByType',
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					params.callback(response_obj);
				}
			}.createDelegate(this)
		});
	},
	getRecordsCount: function() {
		var current_window = this;
		
		if ( EvnReceptIncorrectSearchFilterForm.isEmpty() )
		{
			sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
			function () { EvnReceptIncorrectSearchFilterForm.getForm().findField(0).focus()});
			return false;
		}

		if (EvnReceptIncorrectSearchFilterForm.getForm().isValid() ) {
		
			var post = EvnReceptIncorrectSearchFilterForm.getForm().getValues();

			var loadMask = new Ext.LoadMask(Ext.get('EvnReceptInCorrectSearchWindow'), { msg: "Подождите, идет подсчет записей..." });
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Records_Count != undefined ) {
							sw.swMsg.alert(langs('Подсчет записей'), langs('Найдено записей: ') + response_obj.Records_Count);
						}
						else {
							sw.swMsg.alert(langs('Подсчет записей'), response_obj.Error_Msg);
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При подсчете количества записей произошли ошибки'));
					}
				},
				params: post,
				url: C_SEARCH_RECINCCNT
			});
		}	
	},
	closeAction : "hide",
	id : "EvnReceptInCorrectSearchWindow",
	modal: false,
	maximizable: true,
	printEvnRecept: function(evn_recept_id,evn_recept_set_date,ReceptForm_id){
		var that = this;
		if (Ext.globalOptions.recepts.print_extension == 3) {
			if(ReceptForm_id != 2)
				window.open(C_EVNREC_PRINT_DS, '_blank');
			window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id, '_blank');
		} else {
			Ext.Ajax.request({
				url: '/?c=EvnRecept&m=getPrintType',
				callback: function(options, success, response) {
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						var PrintType = '';
						switch(result.PrintType) {
							case '1':
								PrintType = 2;
								break;
							case '2':
								PrintType = 3;
								break;
							case '3':
								PrintType = '';
								break;
						}

                        switch (ReceptForm_id*1) {
                            case 2: //1-МИ
                                if(PrintType=='') {
                                    printBirt({
                                        'Report_FileName': 'EvnReceptPrint1_1MI.rptdesign',
                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                        'Report_Format': 'pdf'
                                    });
                                } else {
                                    printBirt({
                                        'Report_FileName': 'EvnReceptPrint' + PrintType + '_1MI.rptdesign',
                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                        'Report_Format': 'pdf'
                                    });
                                }
                                break;
                            case 9: //148-1/у-04(л)
                                if (getRegionNick() == 'msk') {
                                    printBirt({
                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2020.rptdesign',
                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                        'Report_Format': 'pdf'
                                    });
                                } else {
                                    //игнорируем настройки и печатаем сразу обе стороны
                                    printBirt({
                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019.rptdesign',
                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                        'Report_Format': 'pdf'
                                    });
                                }
                                printBirt({
                                    'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                    'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                    'Report_Format': 'pdf'
                                });
                                break;
                            default:
                                var ReportName = 'EvnReceptPrint' + PrintType;
                                var ReportNameOb = 'EvnReceptPrintOb' + PrintType;
                                if(result.CopiesCount == 1) {
                                    if(evn_recept_set_date >= '2016-07-30') {
                                        ReportName = 'EvnReceptPrint4_2016_new';
                                    } else if(evn_recept_set_date >= '2016-01-01') {
                                        ReportName = 'EvnReceptPrint4_2016';
                                    } else {
                                        ReportName = 'EvnReceptPrint2_2015';
                                    }
                                    ReportNameOb = 'EvnReceptPrintOb2_2015';
                                } else {
                                    if (evn_recept_set_date >= '2016-07-30') {
                                        ReportName = ReportName + '_2016_new';
									} else if(evn_recept_set_date >= '2016-01-01') {
                                        ReportName = ReportName + '_2016';
									}
                                }
                                if (Ext.globalOptions.recepts.print_extension == 1) {
                                    printBirt({
                                        'Report_FileName': ReportNameOb + '.rptdesign',
                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
                                        'Report_Format': 'pdf'
                                    });
                                }
                                if (result.server_port != null) {
                                    printBirt({
                                        'Report_FileName': ReportName + '.rptdesign',
                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
                                        'Report_Format': 'pdf'
                                    });
                                } else {
                                    printBirt({
                                        'Report_FileName': ReportName + '.rptdesign',
                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedProto=' + result.server_http,
                                        'Report_Format': 'pdf'
                                    });
                                }
                                break;
                        }
					}
				}.createDelegate(that)
			});
		}
	},
	prepareprintEvnRecept: function() {
		var grid = this.findById('EvnReceptIncorrectSearchViewGrid');

		if (!grid.getSelectionModel().getSelected())
		{
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		if(Ext.isEmpty(record.get('EvnRecept_id'))){
			return false;
		}
		if(record.get('ReceptType_Code') == '1'){
			Ext.Msg.alert(langs('Ошибка'), 'Для рецептов, выписанных на бланке, печатная форма не предусмотрена');
			return false;
		}
		if(record.get('ReceptDelayType_Name') == 'Удалённый МО'){
			Ext.Msg.alert(langs('Ошибка'), 'Рецепт удален и не может быть распечатан');
			return false;
		}
		var evn_recept_id = grid.getSelectionModel().getSelected().get('EvnRecept_id');
		var evn_recept_set_date = grid.getSelectionModel().getSelected().get('EvnRecept_setDate').format('Y-m-d');
		var ReceptForm_id = grid.getSelectionModel().getSelected().get('ReceptForm_id');
		if(record.get('EvnRecept_IsSigned')=='НЕТ') //Если не подписан - сначала подписываем
		{
			var that = this;
			signedDocument({
				allowQuestion: false
				,callback: function(success) {
					if ( success == true ) {
						that.printEvnRecept(evn_recept_id,evn_recept_set_date,ReceptForm_id);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Ошибка при выполнении процедуры подписания рецепта');
					}
				}.createDelegate(this)
				,Evn_id: evn_recept_id
			});
		}
		else
			this.printEvnRecept(evn_recept_id,evn_recept_set_date,ReceptForm_id);
		//window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id + '&Server_id=' + server_id, '_blank');
	},
	refreshReceptList: function()
	{
		if(EvnReceptIncorrectSearchViewGrid.getStore().getCount()!=0) {
			EvnReceptIncorrectSearchViewGrid.getStore().reload();
			EvnReceptIncorrectSearchViewGrid.getView().focusRow(0);
			EvnReceptIncorrectSearchViewGrid.getSelectionModel().selectFirstRow();
		}
	},
	getLastFieldOnCurrentTab: function() {
		return getLastFieldOnForm(EvnReceptIncorrectSearchFilterForm.findById('ERISTabPanel').getActiveTab());
	},
	printEvnReceptList: function() {
		var grid = this.findById('EvnReceptIncorrectSearchViewGrid');
		Ext.ux.GridPrinter.print(grid);
	},
	printEvnReceptKard: function() {
	
	    $data = EvnReceptIncorrectSearchViewGrid.getSelectionModel().grid.store.data;
	    Cnt = $data.items.length;
	    var params = new Object();
	    params.list = '';
	     for (var r = 0; r <= Cnt - 1; r++) {
		 params.list += $data.items[r].data.EvnRecept_id + ', '
	     }
             console.log('params.list = ' + params.list);

	     //  Даты для шапки
	      params.BegDate = '';
	      params.EndDate = '';
	     //params.BegDate = '01.10.2016';
	     // params.EndDate = '25.10.2016';

	      var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'/run?__report=report/printSmallEvnReceptInCorrect_list.rptdesign&paramEvnReceptList=' + params.list + '&paramBegDate='+ params.BegDate+'&paramEndDate='+ params.EndDate+'&__format=xls';	
	    window.open(url, '_blank');
			    
	},
	listeners : {
		'beforeshow' : function() {
			EvnReceptIncorrectSearchFilterForm.getForm().reset();
            EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_IsKEK').setVKProtocolFieldsVisible();
			EvnReceptIncorrectSearchViewGrid.store.removeAll();
		},
		'show' : function() {
			var form = this;
			this.restore();
			this.center();
			this.maximize();
			//
			EvnReceptIncorrectSearchFilterForm.findById('ERIS_Person_Surname').focus(true, 500);
			loadComboOnce(EvnReceptIncorrectSearchFilterForm.findById('ERIS_MedPersonalCombo'), langs('Медицинский персонал'));
			// если минздрав и не добавлена вкладка для минздрава, то добавляем вкладку
			if ( getGlobalOptions().isMinZdrav )
			{
				this.findById('ERISTabPanel').unhideTabStripItem('lpu_tab');
				// Переключение на нужный таб
				this.findById('ERISTabPanel').setActiveTab(6);
				Ext.getCmp('ERIS_SearchedLpuCombo').getStore().load();
				Ext.getCmp('ERIS_SearchedLpuCombo').getStore().filterBy(function(record) {
					if ( 
						record.get('Lpu_DloBegDate') != '' && 
						(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
						(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) 
					) 
						return true;					
				});
				// копируем в сторе
				if ( !this.terrAdded )
				{
					var terr_store = new Ext.db.AdapterStore({
						autoLoad: true,
						dbFile: 'Promed.db',
						fields: [
							{ name: 'OMSSprTerr_Code', type: 'int' },
							{ name: 'OMSSprTerr_id', type: 'int' },
							{ name: 'OMSSprTerr_Name', type: 'string' }
						],
						key: 'OMSSprTerr_id',
						sortInfo: {
							field: 'OMSSprTerr_Code',
							direction: 'ASC'
						},
						tableName: 'OMSSprTerr'
					});
					terr_store.load();
					var records = getStoreRecords(terr_store);
					Ext.getCmp('ERIS_SearchedOMSSprTerrCombo').getStore().loadData([{OMSSprTerr_Code: -1, OMSSprTerr_id: -1, OMSSprTerr_Name: langs('Пермский край')}]);
					Ext.getCmp('ERIS_SearchedOMSSprTerrCombo').getStore().loadData(records, true);
					//Ext.getCmp('ERIS_LpuAreaCombo').getStore().loadData([{LpuArea_id: 0, LpuArea_Name: ''}], true);
					Ext.getCmp('ERIS_LpuAreaCombo').getStore().loadData([{Lpu_IsOblast_id: 2, Lpu_IsOblast_Name: langs('Краевые')}], true);
					Ext.getCmp('ERIS_LpuAreaCombo').getStore().loadData([{Lpu_IsOblast_id: 1, Lpu_IsOblast_Name: langs('Не краевые')}], true);
					this.terrAdded = true;
				}
				Ext.getCmp('ERIS_SearchedOMSSprTerrCombo').getStore().clearFilter();
				Ext.getCmp('ERIS_SearchedOMSSprTerrCombo').getStore().filterBy(function(record) {
					if ( record.get('OMSSprTerr_Code') < 62 && record.get('OMSSprTerr_Code') != 0 ) 
						return true;
				});
				Ext.getCmp('ERIS_SearchedOMSSprTerrCombo').setValue(-1);
				/*if ( !this.terrAdded )
				{
					var record = new Ext.data.Record({ 
						'OMSSprTerr_Code': -2,
						'OMSSprTerr_id': 100500,
						'OMSSprTerr_Name': langs('Весь край')
					});
					Ext.getCmp('ERIS_SearchedOMSSprTerrCombo').getStore().add(record, true)
					var record = new Ext.data.Record({ 
						'OMSSprTerr_Code': -1,
						'OMSSprTerr_id': 100501,
						'OMSSprTerr_Name': langs('Только краевые ЛПУ')
					});
					Ext.getCmp('ERIS_SearchedOMSSprTerrCombo').getStore().add(record, true)
					this.terrAdded = true;
				}*/
				
			}
			else
			{
				this.findById('ERISTabPanel').hideTabStripItem('lpu_tab');
			}

			if ( getGlobalOptions().region.nick.inlist(['saratov','pskov']) )
			{
				this.findById('ERISTabPanel').unhideTabStripItem('expertise_tab');
				this.findById('ERISTabPanel').setActiveTab(7);
				loadComboOnce(EvnReceptIncorrectSearchFilterForm.findById('ERIS_ReceptStatusFLKMEKCombo'), langs('Результат экспертизы'));
				loadComboOnce(EvnReceptIncorrectSearchFilterForm.findById('ERIS_RegistryReceptErrorTypeCombo'), langs('Причина отказа'),
					function() {
						EvnReceptIncorrectSearchFilterForm.findById('ERIS_ReceptStatusTypeCombo').fireEvent('change', EvnReceptIncorrectSearchFilterForm.findById('ERIS_ReceptStatusTypeCombo'), '');
					}
				);
			}
			else
			{
				this.findById('ERISTabPanel').hideTabStripItem('expertise_tab');
			}
			
			// Переключение на нужный таб
			this.findById('ERISTabPanel').setActiveTab(4);
			
			EvnReceptIncorrectSearchFilterForm.getForm().getEl().dom.action = C_EVNRECINC_PRINTSEARCH;
			EvnReceptIncorrectSearchFilterForm.getForm().getEl().dom.method = "post";
			EvnReceptIncorrectSearchFilterForm.getForm().getEl().dom.target = "_blank";
			EvnReceptIncorrectSearchFilterForm.getForm().standardSubmit = true;
                        
                        if (getGlobalOptions().region.nick != 'ufa')
                            EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_Is7Noz').setValue(1);
                        else    
                            EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
                        

			loadComboOnce(EvnReceptIncorrectSearchFilterForm.findById('ERIS_OrgFarmacy'), langs('Аптеки'));

			this.hasPolka = false;
			this.getLpuUnitPolkaCount({callback: function(data){
				form.hasPolka = (data && data.LpuUnitCount > 0);
				EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_NewReceptBtn').setDisabled(form.closeActions || !form.hasPolka);
				EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_printReceptMenu').menu.items.itemAt(1).setVisible(!form.closeActions && form.hasPolka);
			}});
			//Добавим еще одно значение (т.к. в таблице ReceptResult его нет)
			var record = new Ext.data.Record({
				'ReceptResult_id': 13,
				'ReceptResult_Code': '13',
				'ReceptResult_Name': 'Снят с обслуживания'
			});
			EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getStore().add(record, true);
			var searchForm = this.EvnReceptIncorrectSearchFilterForm.getForm();
			searchForm.findField('Lpu_id').getStore().load({
				params: {where:" where Lpu_DloBegDate is not null "},
				callback: function(){
					if(getGlobalOptions().orgtype == 'lpu'){
						searchForm.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
						searchForm.findField('Lpu_id').fireEvent('change',searchForm.findField('Lpu_id'),getGlobalOptions().lpu_id);
						if(
							isSuperAdmin() 
							|| haveArmType('adminllo') || haveArmType('minzdravdlo') 
							|| haveArmType('spesexpertllo') || haveArmType('mzchieffreelancer')
						) {
							searchForm.findField('Lpu_id').enable();
						} else {
							searchForm.findField('Lpu_id').disable();
						}
					} else {
						searchForm.findField('Lpu_id').enable();
					}
				}
			});
			if(getRegionNick() == 'perm'){
				this.findById('ERIS_EvnRecept_IsNotOstatForm').show();
			} else {
				this.findById('ERIS_EvnRecept_IsNotOstatForm').hide();
			}
		},
		'hide' : function() {
			EvnReceptIncorrectSearchViewGrid.store.removeAll();
		}
	},
	initComponent : function() {
		this.EvnReceptIncorrectSearchGridStore = new Ext.data.Store({
			id: 'EvnReceptIncorrectSearchGridStore',
			url: C_EVNRECINC_SEARCH,
			reader : new Ext.data.JsonReader({
				id : "EvnRecept_id",
				root : "data",
				totalProperty : "totalCount"
			}, [{
				name : "EvnRecept_id",
				mapping : "EvnRecept_id",
				type : "int"
			}, {
				name : "Lpu_Nick",
				mapping : "Lpu_Nick",
				type : "string"
			}, {
				name : "ReceptDelayType_id",
				mapping : "ReceptDelayType_id",
				type : "int"
			}, {
				name : "ReceptDelayType_Name",
				mapping : "ReceptDelayType_Name",
				type : "string"
			},
			{
				name: 'EvnRecept_IsSigned',
				mapping: 'EvnRecept_IsSigned',
				type : "string"
			},
			{
				name: 'ReceptType_Code',
				mapping: 'ReceptType_Code',
				type : "string"
			},
			{
				name: 'ReceptType_Name',
				mapping: 'ReceptType_Name',
				type : "string"
			},
			{
				name: 'ReceptForm_id',
				mapping: 'ReceptForm_id',
				type: 'int'
			},
			{
				name : "Person_id",
				mapping : "Person_id",
				type : "int"
			}, {
				name : "PersonEvn_id",
				mapping : "PersonEvn_id",
				type : "int"
			}, {
				name : "Server_id",
				mapping : "Server_id",
				type : "int"
			}, {
				name : "Person_Surname",
				mapping : "Person_Surname",
				type : "string"
			}, {
				name : "Person_Firname",
				mapping : "Person_Firname",
				type : "string"
			}, {
				name : "Person_Secname",
				mapping : "Person_Secname",
				type : "string"
			}, {
				name : "Person_Birthday",
				mapping : "Person_Birthday",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "Person_Snils",
				mapping : "Person_Snils",
				type : "string"
			},{
                name: "ReceptForm_Code",
                mapping: "ReceptForm_Code",
                type: "string"
            },{
				name : "ReceptFinance_Name",
				mapping : "ReceptFinance_Name",
				type : "string"
			}, {
				name : "WhsDocumentCostItemType_Name",
				mapping : "WhsDocumentCostItemType_Name",
				type : "string"
			}, {
				name : "EvnRecept_Ser",
				mapping : "EvnRecept_Ser",
				type : "string"
			}, {
				name : "EvnRecept_Num",
				mapping : "EvnRecept_Num",
				type : "string"
			}, {
				name : "EvnRecept_Kolvo",   
				mapping : "EvnRecept_Kolvo",
				type : "string"
			}, {
				name : "EvnRecept_firKolvo",   
				mapping : "EvnRecept_firKolvo",
				type : "string"
			}, {
				name : "EvnRecept_Suma",
				mapping : "EvnRecept_Suma",
				type : "string"
			}, {
				name : "MedPersonal_Fio",
				mapping : "MedPersonal_Fio",
				type : "string"
			}, {
				name : "EvnRecept_IsMnn", 
				mapping : "EvnRecept_IsMnn",
				type : "string"
			}, {
				name : "DrugMnn_Name", 
				mapping : "DrugMnn_Name",
				type : "string"
			},{
				name : "OrgFarmacy_Name",
				mapping : "OrgFarmacy_Name",
				type : "string"
			}, {
				name : "Drug_Name",
				mapping : "Drug_Name", 
				type : "string"
			}, {
				name : "Drug_Code",
				mapping : "Drug_Code", 
				type : "string"
			},  {
                                name : "Drug_id",
				mapping : "Drug_id",
				type : "int"
			}, {
				name : "ReceptFinance_id",
				mapping : "ReceptFinance_id",
				type : "int"
			}, {
				name : "OrgFarmacy_id",
				mapping : "OrgFarmacy_id",
				type : "int"
			}, {
				name : "OrgFarmacy_oid",
				mapping : "OrgFarmacy_oid",
				type : "int"
			}, {
				name : "EvnRecept_setDate",
				mapping : "EvnRecept_setDate",
				type : "date",
				dateFormat:'d.m.Y'
			},{
				name : "EvnRecept_Godn",
				mapping : "EvnRecept_Godn",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "EvnRecept_InRequest",
				mapping : "EvnRecept_InRequest",
				type : "string"
			}, {
				name : "EvnRecept_obrDate",
				mapping : "EvnRecept_obrDate",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "EvnRecept_otpDate",
				mapping : "EvnRecept_otpDate",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "EvnRecept_obrDay",
				mapping : "EvnRecept_obrDay",
				type : "int"
			}, {
				name : "EvnRecept_otsDay",
				mapping : "EvnRecept_otsDay",
				type : "int"
			}, {
				name : "EvnRecept_otovDay",
				mapping : "EvnRecept_otovDay",
				type : "int"
			},
			{
				name : "Drug_rlsid",
				mapping : "Drug_rlsid",
				type : "int"
			},
			{
				name : "DrugComplexMnn_id",
				mapping : "DrugComplexMnn_id",
				type : "int"
			}
			/*, {
				name : "EvnRecept_otpDate",
				mapping : "EvnRecept_otpDate",
				type : "date",
				dateFormat:'d.m.Y'
			}*/])
		});

		this.EvnReceptIncorrectSearchViewGrid = new Ext.grid.GridPanel({
			region: 'center',
			//height: 500,
			
			bbar: new Ext.PagingToolbar ({
				store: this.EvnReceptIncorrectSearchGridStore,
				pageSize: 100,
				displayInfo: true,
				displayMsg: langs('Отображаемые строки {0} - {1} из {2}'),
		        emptyMsg: "Нет записей для отображения"
			}),
			id : 'EvnReceptIncorrectSearchViewGrid',
			//autoExpandColumn: 'autoexpand_drug',
			//autoExpandMin: 100,
			tabIndex : 13,
			store : this.EvnReceptIncorrectSearchGridStore,
			loadMask : true,
			columns : [{
				hidden : true,
				sortable : true,
				dataIndex : "ReceptDelayType_id",
				header : langs('Статус')
			},{
				hidden : true,
				dataIndex : "Drug_id"
			},{
				hidden : true,
				dataIndex : "ReceptFinance_id"
			},{
				hidden : true,
				dataIndex : "OrgFarmacy_id"
			},{
				hidden : true,
				dataIndex : "OrgFarmacy_oid"
			},{
				hidden : false,
				sortable : true,
				dataIndex : "ReceptDelayType_Name",
				header : langs('Статус')
			},
			{
				hidden : false,
				sortable : true,
				dataIndex: "EvnRecept_IsSigned",
				header: langs('Подписан')
			},
			{
				hidden : true,
				sortable : false,
				dataIndex: "ReceptType_Code"
			},
			{
				hidden: true,
				sortable: false,
				dataIndex: 'ReceptForm_id',
				header: ''
			},
			{
				//hidden : !getGlobalOptions().isMinZdrav,
				sortable : true,
				dataIndex : "Lpu_Nick",
				header : "МО выписки рецепта"
			},{
				hidden : false,
				sortable : true,
				dataIndex : "Person_Surname",
				header : langs('Фамилия')
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "Person_Firname",
				header : langs('Имя')
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "Person_Secname",
				header : langs('Отчество')
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "Person_Birthday",
				header : "Дата рождения",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 90
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "Person_Snils",
				header : langs('СНИЛС')
			}, {
                hidden:false,
                sortable: true,
                dataIndex: "ReceptForm_Code",
                header: "Форма рецепта",
                width: 90
            },{
				hidden : getRegionNick().inlist(['kz']),
				sortable : true,
				dataIndex : "EvnRecept_Ser",
				header : langs('Серия'),
				width: 70
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_Num",
				header : langs('Номер'),
				width: 70
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "ReceptType_Name",
				header : langs('Тип рецепта'),
				width: 70
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "ReceptFinance_Name",
				header : langs('Финансирование'),
				width: 90
			}, {
				hidden : getGlobalOptions().region.nick == 'perm',
				sortable : true,
				dataIndex : "WhsDocumentCostItemType_Name",
				header : langs('Статья расхода'),
				width: 90
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "MedPersonal_Fio",
				header : langs('Врач'),
				width: 200
			}, {
				hidden : true,//getGlobalOptions().region.nick != 'ufa',
				sortable : true,
				dataIndex : "EvnRecept_IsMnn",
				header : '',
				width: 50
                            }, {
				hidden : false,
				sortable : true,
				dataIndex : "DrugMnn_Name",
				header : getGlobalOptions().region.nick != 'ufa' ? langs('МНН'): 'Медикамент: выписано',
				width: 200
                            }, {
				css: 'text-align: right;',
				hidden : getGlobalOptions().region.nick != 'ufa',
				sortable : true,
				dataIndex : "EvnRecept_firKolvo", 
				header : langs('Количество'),
				width: 100
			}, {
				hidden : false,
				//id: 'autoexpand_drug',
				sortable : true,
				dataIndex : "Drug_Name",  
                                header : getGlobalOptions().region.nick != 'ufa' ? "Торговое наименование": 'Медикамент: выдано',
				width: 400
			}, {
				hidden : getGlobalOptions().region.nick != 'ufa',
				//id: 'autoexpand_drug',
				sortable : true,
				dataIndex : "Drug_Code",  
                                header :  "Код препарата",
				width: 100
			}, {
                                css: 'text-align: right;',
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_Kolvo", 
				header : langs('Количество'),
				width: 100
			}, {
				css: 'text-align: right;',
				//hidden : false,
				hidden : !(getGlobalOptions().region.nick == 'saratov'),
                                sortable : true,
				dataIndex : "EvnRecept_Suma",
				header : langs('Сумма'),
				width: 100
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "OrgFarmacy_Name",
				header : langs('Аптека'),
				width: 300
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_setDate",
				header : "Дата выписки",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 90
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_Godn",
				header : "Действителен до",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 90
			}, {
				hidden : !(getGlobalOptions().region.nick =='perm'),
				sortable : true,
				dataIndex : "EvnRecept_InRequest",
				header : "Вкл. в заявку",
				width: 60
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_obrDate",
				header : "Дата обращения",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 90
			},{
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_otpDate",
				header : "Дата обеспечения",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 90
			}, {
				align : "right",
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_obrDay",
				header : "Срок обращения",
				width:90
			}, {
				align : "right",
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_otsDay",
				header : "Срок отсроченного обслуживания",
				width: 90
			}, {
				align : "right",
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_otovDay",
				header : "Срок обеспечения",
				width: 90
			}],
			tbar : new Ext.Toolbar(
			[{
				disabled: false,
				hidden: true,//getRegionNick().inlist(['saratov']),
				text : BTN_GRIDADD,
				iconCls: 'add16',
				handler : function(button, event) {
					EvnReceptIncorrectSearchViewGrid.addRecept();
				}.createDelegate(this),
				id: 'ERIS_NewReceptBtn',
				tooltip : "Ввод нового рецепта <b>(INS)</b>"
			}, {
				disabled: true,
				hidden: true,
				handler : function(button, event) {
					EvnReceptIncorrectSearchViewGrid.openRecept('edit');
				}.createDelegate(this),
				id: 'ERIS_EditReceptBtn',
				iconCls: 'edit16',
				text : BTN_GRIDEDIT,
				tooltip : "Редактирование выбранного рецепта <b>(F4)</b>"
			}, {
				disabled: true,
				handler : function(button, event) {
					EvnReceptIncorrectSearchViewGrid.openRecept('view');
				}.createDelegate(this),
				id: 'ERIS_ViewReceptBtn',
				iconCls: 'view16',
				text : BTN_GRIDVIEW,
				tooltip : "Просмотр выбранного рецепта <b>(F3)</b>"
			}, {
				disabled: true,
				handler : function(button, event) {
					getWnd('swDrugOstatViewWindow').show({
						mode: 'DrugOstatView',
						Drug_Name: EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected().get('Drug_Name'),
						ReceptFinance_id: EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected().get('ReceptFinance_id'),
						OrgFarmacy_oid: EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected().get('OrgFarmacy_oid'),
						OrgFarmacy_id: EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected().get('OrgFarmacy_id'),
						DrugMnn_Name: EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected().get('DrugMnn_Name'),
						Drug_id: EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected().get('Drug_id')
					});
				}.createDelegate(this),
				id: 'ERIS_ViewOstatBtn',
				hidden: !getRegionNick().inlist(['perm']),
				iconCls: 'view16',
				text : BTN_DRGOST,
				tooltip : "Просмотреть остатки"
			}, {
				disabled: true,
				hidden: true,
				handler : function(button, event) {
					EvnReceptIncorrectSearchViewGrid.deleteRecept();
				}.createDelegate(this),
				id: 'ERIS_DeleteReceptBtn',
				iconCls: 'delete16',
				text : BTN_GRIDDEL,
				tooltip : "Удаление выбранного рецепта <b>(DEL)</b>"
			}, {
				xtype : "tbseparator"
			}, {
				disabled: true,
				iconCls: 'actions16',
				text : langs('Действия'),
				handler : function(button, event) {
					alert('');
				}.createDelegate(this),
				id: 'ERIS_ActionBtn'
			}, {
				xtype : "tbseparator"
			}, {
				disabled: false,
				iconCls: 'refresh16',
				text : BTN_GRIDREFR,
				handler : function(button, event) {
					Ext.getCmp('EvnReceptInCorrectSearchWindow').refreshReceptList();
				}.createDelegate(this),
				id: 'ERIS_RefreshBtn',
				tooltip : "Обновление списка с сервера <b>(F5)</b>"
			}, {
				iconCls: 'print16',
				id: 'ERIS_printReceptMenu',
				menu: [
					{
						handler: function() {
							Ext.getCmp('EvnReceptInCorrectSearchWindow').prepareprintEvnRecept();
						},
						text: langs('Печать'),
						hidden: getWnd('swWorkPlaceMZSpecWindow').isVisible(),
						xtype: 'tbbutton'
					},
					{
						handler: function() {
							Ext.getCmp('EvnReceptInCorrectSearchWindow').printEvnReceptList();
						},
						text: langs('Печать текущей страницы'),
						xtype: 'tbbutton'
					},
					{
						handler: function(){
							EvnReceptIncorrectSearchFilterForm.getForm().submit();
						},
						text: langs('Печать всего списка'),
						xtype: 'tbbutton'
					},
										{
						handler: function() {
							Ext.getCmp('EvnReceptInCorrectSearchWindow').printEvnReceptKard();
						},
						text: 'Печать карточки',
						hidden : !getGlobalOptions().region.nick.inlist(['ufa']),
						xtype: 'tbbutton'
					}
				],
				text: BTN_GRIDPRINT,
				xtype: 'tbbutton'
			}, {
				xtype : "tbfill"
			}, {
				id: 'ERIS_GridCounter',
				text: '0 / 0',
				xtype: 'tbtext'
			}]),
			enableKeyEvents: true,
			listeners : {
				'rowdblclick' : function (grd, rowIndex, e) {
					grd.openRecept('view');
				}
			},
			keys: [
				{
				key: [
					Ext.EventObject.DELETE,
					Ext.EventObject.ENTER,
					Ext.EventObject.F3,
					Ext.EventObject.F4,
					Ext.EventObject.F5,
					Ext.EventObject.F6,
					Ext.EventObject.F9,
					Ext.EventObject.F10,
					Ext.EventObject.F11,
					Ext.EventObject.F12,
					Ext.EventObject.INSERT,
					Ext.EventObject.TAB,
					Ext.EventObject.PAGE_UP,
					Ext.EventObject.PAGE_DOWN,
					Ext.EventObject.HOME,
					Ext.EventObject.END
				],
				fn: function(inp, e) {
					e.stopEvent();
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var grd = EvnReceptIncorrectSearchViewGrid;

					var selected_record = grd.getSelectionModel().getSelected();
					var params = new Object();
					params.Person_id = selected_record.get('Person_id');
					params.Server_id = selected_record.get('Server_id');
					params.Person_Birthday = selected_record.get('Person_Birthday');
					params.Person_Firname = selected_record.get('Person_Firname');
					params.Person_Secname = selected_record.get('Person_Secname');
					params.Person_Surname = selected_record.get('Person_Surname');

					switch (e.getKey())
					{
						case Ext.EventObject.ENTER:
							grd.openRecept('view');
						break;
						
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
							if (!grd.getSelectionModel().getSelected())
							{
								return false;
							}

							action = 'view';

							grd.openRecept(action);

						break;

						case Ext.EventObject.F5:
							Ext.getCmp('EvnReceptInCorrectSearchWindow').refreshReceptList();
						break;

						case Ext.EventObject.F9:
							if (e.ctrlKey == true)
							{
								Ext.getCmp('EvnReceptInCorrectSearchWindow').prepareprintEvnRecept();
							}
							else
							{
								Ext.getCmp('EvnReceptInCorrectSearchWindow').printEvnReceptList();
							}
						break;
				
						case Ext.EventObject.F6:
							ShowWindow('swPersonCardHistoryWindow', params);
							return false;
						break;

						case Ext.EventObject.F10:
							ShowWindow('swPersonEditWindow', params);
							return false;
						break;

						case Ext.EventObject.F11:
							ShowWindow('swPersonCureHistoryWindow', params);
							return false;
						break;

						case Ext.EventObject.F12:
							if (e.ctrlKey)
							{
								ShowWindow('swPersonDispHistoryWindow', params);
							}
							else
							{
								ShowWindow('swPersonPrivilegeViewWindow', params);
							}
							return false;
						break;
							
						case Ext.EventObject.INSERT:
							// grd.addRecept();
						break;

						case Ext.EventObject.DELETE:
							// grd.deleteRecept();
						break;

						case Ext.EventObject.TAB:
							if (e.shiftKey == false) {
								Ext.getCmp('ERIS_BottomButtons').buttons[0].focus(false, 100);
							}
							else {
								Ext.getCmp('EvnReceptInCorrectSearchWindow').getLastFieldOnCurrentTab().focus(true);
							}
						break;
						
						case Ext.EventObject.END:
							GridEnd(grd);
						break;
						
						case Ext.EventObject.HOME:
							GridHome(grd);
						break;
						
						case Ext.EventObject.PAGE_DOWN:
							GridPageDown(grd);
						break;
						
						case Ext.EventObject.PAGE_UP:
							GridPageUp(grd);
						break;
					}
				},
				stopEvent: true
			}],
			sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
						'rowselect': function(sm, rowIndex, record) {
							var evn_recept_id = sm.getSelected().data.EvnRecept_id;
							var person_id = sm.getSelected().data.Person_id;
							var server_id = sm.getSelected().data.Server_id;

							if (evn_recept_id && person_id && server_id >= 0)
							{
								this.grid.getTopToolbar().items.item('ERIS_DeleteReceptBtn').disable();
								this.grid.getTopToolbar().items.item('ERIS_EditReceptBtn').disable();
								this.grid.getTopToolbar().items.item('ERIS_ViewReceptBtn').enable();
							}
							else
							{
								this.grid.getTopToolbar().items.item('ERIS_DeleteReceptBtn').disable();
								this.grid.getTopToolbar().items.item('ERIS_EditReceptBtn').disable();
								this.grid.getTopToolbar().items.item('ERIS_ViewReceptBtn').disable();
							}

							if (sm.getSelected().data.ReceptDelayType_Name.inlist([langs('Выписан'), langs('Отсрочен')])) {
								this.grid.getTopToolbar().items.item('ERIS_ViewOstatBtn').enable();
							} else {
								this.grid.getTopToolbar().items.item('ERIS_ViewOstatBtn').disable();
							}
							this.grid.getTopToolbar().items.items[11].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();
							
							record.set('set', 1);
							record.commit();
							//EvnReceptIncorrectSearchViewGrid.getView().focusRow(index);
						},
						'rowdeselect': function(sm, rowIndex, record) {
							record.set('set', 0);
							record.commit();
						}
					}
				}),
			openRecept: function(action) {
				if (!EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected())
				{
					Ext.Msg.alert(langs('Ошибка'), langs('Не выбран рецепт из списка'));
					return false;
				}
				
				var wndReceptEdit = 'swEvnReceptEditWindow';
                if ( getRegionNick().inlist([ 'khak', 'pskov', 'saratov', 'krym' ]) ) {
                    wndReceptEdit = 'swEvnReceptRlsEditWindow';
                }

				var selected_record = EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected();
				var evn_recept_id = selected_record.data.EvnRecept_id;
				var person_id = selected_record.data.Person_id;
				var person_evn_id = selected_record.data.PersonEvn_id;
				var server_id = selected_record.data.Server_id;

				if (!Ext.isEmpty(selected_record.get('Drug_id'))) {
					wndReceptEdit = 'swEvnReceptEditWindow'; // для Перми
				} else if (!Ext.isEmpty(selected_record.get('Drug_rlsid')) || !Ext.isEmpty(selected_record.get('DrugComplexMnn_id'))) {
					wndReceptEdit = 'swEvnReceptRlsEditWindow'; // для Уфы
				} else {
					sw.swMsg.alert("Ошибка", "Не выбран медикамент в рецепте"); // так не может быть
					return false;
				}

				if (evn_recept_id && person_id && person_evn_id && server_id >= 0)
				{
					getWnd(wndReceptEdit).show({
						action: action,
						callback: function(data) {
							setGridRecord(EvnReceptIncorrectSearchViewGrid, data.EvnReceptData);
						},
						EvnRecept_id: evn_recept_id,
						onHide: function() {
							EvnReceptIncorrectSearchViewGrid.getView().focusRow(EvnReceptIncorrectSearchViewGrid.getStore().indexOf(selected_record));
							EvnReceptIncorrectSearchViewGrid.getSelectionModel().selectRow(EvnReceptIncorrectSearchViewGrid.getStore().indexOf(selected_record));
						},
						Person_id: person_id,
						PersonEvn_id: person_evn_id,
						Server_id: server_id,
						viewOnly: !this.hasPolka
					});
				}
				else
				{
					Ext.Msg.alert(langs('Ошибка'), langs('Данных по этому рецепту нет в базе'));
				}
			}.createDelegate(this),
			deleteRecept: function() {
				return false;
			},
			addRecept: function() {
				var current_window = this.ownerCt;
				var wndReceptEdit = 'swEvnReceptEditWindow';
				if ( getRegionNick().inlist([ 'khak', 'pskov', 'saratov', 'krym' ]) ) {
					wndReceptEdit = 'swEvnReceptRlsEditWindow';
				}
				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd(wndReceptEdit).show({
							action: 'add',
							Person_id: person_data.Person_id,
							PersonEvn_id: person_data.PersonEvn_id,
							// ReceptType_id: 2,
							Server_id: person_data.Server_id,
							onHide: person_data.onHide
						});
					},
					searchMode: 'all'
				});
			}
		});
		
		
		this.EvnReceptIncorrectSearchViewGrid.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index, rowParams)
			{
				var cls = '';
				if (row.get('set') == 0 || row.get('set') == undefined) {
					if (row.get('ReceptDelayType_Name') == langs('Отоварен') )
						cls = cls+'x-grid-rowbackgreen ';
					if (row.get('ReceptDelayType_Name') == langs('Отсрочен') )
						cls = cls+'x-grid-rowbackyellow ';
					if (row.get('ReceptDelayType_Name') == langs('Отказ') )
						cls = cls+'x-grid-rowbackdarkgray ';
					if (row.get('ReceptDelayType_Name') == langs('Просрочен') )
						cls = cls+'x-grid-rowbackred ';
					if (row.get('ReceptDelayType_Name') == 'Удалённый МО' )
						cls = cls+'x-grid-rowbacklightgray ';
					if(row.get('ReceptDelayType_Name') == 'Снят с обслуживания')
						cls = cls+'x-grid-rowbackorange ';
					if (cls.length == 0)
						cls = 'x-grid-panel';
				}
				return cls;
			}
		});

		this.EvnReceptIncorrectSearchFilterForm = new Ext.form.FormPanel({
			id : "EvnSearchForm",
			labelWidth : 100,
			frame: false,
			
			title: langs('Нажмите на заголовок чтобы свернуть/развернуть панель фильтров'),
			titleCollapse: true,
			animCollapse: false,
			collapsible: true,
			floatable: false,
			autoHeight: true,
			labelAlign: 'right',
			
			region: 'north',
			listeners: {
				collapse: function(p) {
					p.doLayout();
					this.doLayout();
				}.createDelegate(this),
				expand: function(p) {
					p.doLayout();
					this.doLayout();
				}.createDelegate(this)
			},
			items : [{
				id: 'ERISTabPanel',				
				items : [{
					title : "<u>1</u>. Основной фильтр",
					frame: false,
					border: false,
					autoHeight: true,
					//height : 220,
					items : [{
						height : 5,
						border : false
					}, {
						id : "ERIS_Person_Surname",
						listeners: {
							'keydown': function (inp, e) {
								if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
								{
									e.stopEvent();
									swReceptInCorrectSearchWindow.buttons[5].focus();
								}
							}
						},
						name : "Person_Surname",
						xtype : "textfieldpmw",
						width : 520,
						fieldLabel : langs('Фамилия'),
						tabIndex : 1301
					}, {
						name : "Person_Firname",
						xtype : "textfieldpmw",
						width : 520,
						fieldLabel : langs('Имя'),
						tabIndex : 1302
					}, {
						name : "Person_Secname",
						xtype : "textfieldpmw",
						width : 520,
						fieldLabel : langs('Отчество'),
						tabIndex : 1303
					}, {
						items : [{
							items : [{
								name : "Person_BirthDay",
								xtype : "daterangefield",
								width : 220,
								fieldLabel : "Дата рождения",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : 1304
							}],
							layout : "form",
							width : 327,
							border : false
						}],
						layout : "column",
						//height: 240,
						autoHeight : true,
						border : false
					}, {
						items : [{
							items : [{
								autoCreate: {tag: "input", type: "text", size: "11", maxLength: "11", autocomplete: "off"},
								fieldLabel : langs('СНИЛС'),
								maskRe: /\d/,
								maxLength: 11,
								minLength: 11,
								name : "Person_SNILS",
								width : 220,
								xtype : "textfield",
								tabIndex : 1305
							}],
							layout : "form",
							border : false,
							hidden: getRegionNick().inlist(['kz'])
						}, {
							layout: 'form',
							border: false,
							hidden: !getRegionNick().inlist(['kz']),
							items: [{
								allowBlank: true,
								autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
								fieldLabel: langs('ИИН'),
								maskRe: /\d/,
								maxLength: 12,
								minLength: 12,
								name: 'Person_Inn',
								width: 220,
								xtype: 'textfield'
							}]
						}, {
							items : [{
								name : "PersonCard_NumCard",
								xtype : "textfield",
								width : 195,
								fieldLabel : "Номер карты",
								tabIndex : 1306
							}],
							layout : "form",
							border : false,
						}],
						layout : "column",
						autoHeight : true,
						border : false
					}, {
						items : [/*{
							items : [{
								name : "EvnRecept_otpDate",
								width : 170,
								xtype : "daterangefield",
								fieldLabel : "Дата отпуска",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : 306
							}],
							width : 300,
							layout : "form",
							border : false
						}, */{
							items : [{
								displayField: 'PrivilegeType_Name',
								codeField: 'PrivilegeType_VCode',
								editable: false,
								fieldLabel: getRegionNick().inlist(['kz'])?langs('Категория / Нозология'):langs('Категория'),
								forceSelection : true,
								hiddenName: 'PrivilegeType_id',
								id: 'RSF_PrivilegeTypeCombo',
								listWidth: 250,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'PrivilegeType_id', type: 'int'},
										{ name: 'PrivilegeType_Code', type: 'int'},
										{ name: 'PrivilegeType_VCode', type: 'string'},
										{ name: 'PrivilegeType_Name', type: 'string'}
									],
									key: 'PrivilegeType_id',
									sortInfo: { field: 'PrivilegeType_VCode' },
									tableName: 'PrivilegeType',
                                    listeners: {
                                        load: function(s) {
                                            s.sortData('RlsClsntfr_Name');
                                        }
                                    },
                                    sortData: function() {
                                        var f = 'PrivilegeType_VCode';
                                        var direction = 'ASC';
                                        var f_type = 'int';

                                        this.each(function(r) {
                                            var val = r.get(f);
                                            if (!Ext.isEmpty(val) && val != val*1) {
                                                f_type = 'string';
                                                return false;
                                            }
                                        });

                                        var fn = function(r1, r2){
                                            var v1 = r1.data[f], v2 = r2.data[f];

                                            if (f_type == 'int') {
                                                v1 = v1*1;
                                                v2 = v2*1;
                                            } else {
                                                v1 = v1.toLowerCase();
                                                v2 = v2.toLowerCase();
                                            }

                                            var ret = v1 > v2 ? 1 : (v1 < v2 ? -1 : 0);
                                            return ret;
                                        };
                                        this.data.sort(direction, fn);
                                        if(this.snapshot && this.snapshot != this.data){
                                            this.snapshot.sort(direction, fn);
                                        }
                                    }
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{PrivilegeType_VCode}</font>&nbsp;{PrivilegeType_Name}',
									'</div></tpl>'
								),
								valueField: 'PrivilegeType_id',
								width: 220,
								xtype: 'swbaselocalcombo',
								tabIndex : 1308
							}],
							layout : "form",
							width : 330,
							border : false
						}, {
							layout: 'form',
							border: false,
							labelWidth : 95,
							hidden: !getRegionNick().inlist(['kz']),
							items: [{
								xtype: 'swcommonsprcombo',
								comboSubject: 'SubCategoryPrivType',
								hiddenName: 'SubCategoryPrivType_id',
								fieldLabel: 'Подкатегория',
								width: 195
							}]
						}/*, {
							labelWidth : 70,
							items : [{
								displayField: 'YesNo_Name',
								codeField: 'YesNo_Code',
								editable: false,
								hiddenName: 'Person_IsRefuse',
								xtype : "swyesnocombo",
								width : 220,
								fieldLabel : langs('Отказ'),
								tabIndex : 1309
							}],
							layout : "form",
							border : false
						}*/],
						layout : "column",
						autoHeight : true,
						border : false
					}, {
						items : [{
							layout: 'form',
							autoHeight: true,
							border : false,
							items: [{
								codeField: 'Sex_Code',
								editable: false,
								fieldLabel: langs('Пол'),
								xtype: 'swpersonsexcombo',
								hiddenName: 'PersonSex_id',
								width : 220,
								tabIndex : 1310
							}]
						}, {
							layout: 'form',
							autoHeight: true,
							border : false,
							items: [{
								codeField: 'SocStatus_Code',
								editable: false,
								fieldLabel: langs('Соц. статус'),
								listWidth: 250,
								xtype: 'swsocstatuscombo',
								hiddenName: 'SocStatus_id',
								width : 195,
								tabIndex : 1311
								}]
						}],
						layout : "column",
						//height: 240,
						autoHeight : true,
						border : false
					}, {
						items : [{
							labelWidth : 100,
							items : [{
								allowBlank: true,
								displayField: 'Diag_Name',
								emptyText: langs('Введите код диагноза...'),
								fieldLabel: langs('Код диагноза с'),
								hiddenName: 'ER_Diag_Code_From',
								hideTrigger: false,
								id: 'ERIS_DiagComboFrom',
								valueField: 'Diag_Code',
								width: 220,
								xtype: 'swdiagcombo',
								tabIndex : 1313,
								listeners: {
									'keydown': function (inp, e) {
									}
								}
							}],
							layout : "form",
							width : 330,
							border : false
						}, {
							labelWidth : 95,
							items : [{
								allowBlank: true,
								displayField: 'Diag_Name',
								emptyText: langs('Введите код диагноза...'),
								fieldLabel: langs('по'),
								hiddenName: 'ER_Diag_Code_To',
								hideTrigger: false,
								id: 'ERIS_DiagComboTo',
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptIncorrectSearchViewGrid);
											}
										}
									}
								},
								valueField: 'Diag_Code',
								width: 195,
								xtype: 'swdiagcombo',
								tabIndex : 1314
							}],
							layout : "form",
							width : 300,
							border : false
						}],
						layout : "column",
						autoHeight : true,
						border : false
					}
					],
					bodyBorder : true,
					layout : "form",
					autoHeight : true
				}, {
					title : "<u>2</u>. Пациент",
					frame: false,
					border: false,
					autoHeight: true,
					//height : 220,
					style: 'padding: 0px; margin-bottom: 5px',
					border : false,
					items: [{
						layout: 'form',
						border : false,
						style: 'padding-top: 3px',
						labelWidth : 160,
						items: [{
							codeField: 'DocumentType_Code',
							editable: false,
							fieldLabel: langs('Тип документа'),
							xtype: 'swdocumenttypecombo',
							hiddenName: 'DocumentType_id',
							id: 'ERIS_DocumentType_id',
							tabIndex : 1316,
							width: 350,
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										swReceptInCorrectSearchWindow.buttons[5].focus();
									}
								}
							}
						}]
					},
					{
						layout: 'form',
						width: 520,
						border : false,
						labelWidth : 160,
						items:[{
							allowBlank: true,
							xtype: 'sworgdepcombo',
							validateOnBlur: false,
							validationEvent: false,
							hiddenName: 'OrgDep_id',
							editable: false,
							triggerAction: 'none',
							listWidth: '300',
							onTrigger1Click: function() {
								if ( this.disabled )
									return;
								var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
								var combo = this;
								getWnd('swOrgSearchWindow').show({
									onSelect: function(orgData) {
										if ( orgData.Org_id > 0 )
										{
											combo.getStore().load({
												params: {
													Object:'OrgDep',
													OrgDep_id: orgData.Org_id,
													OrgDep_Name: ''
												},
												callback: function()
												{
													combo.setValue(orgData.Org_id);
													combo.focus(true, 500);
													combo.fireEvent('change', combo);
												}
											});
										}
										getWnd('swOrgSearchWindow').hide();
									},
									object: 'OrgDep'
								});
							},
							enableKeyEvents: true,
							listeners: {
								'keydown': function( inp, e ) {
									if ( inp.disabled )
										return;
									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;
										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;
										e.browserEvent.returnValue = false;
										e.returnValue = false;
										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										inp.onTrigger1Click();
										return false;
									}
									if ( e.DELETE == e.getKey() && e.altKey) {
										inp.onTrigger2Click();
										return false;
									}
								},
								'keyup': function(inp, e) {
									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;
										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;
										e.browserEvent.returnValue = false;
										e.returnValue = false;
										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
									if ( e.DELETE == e.getKey() && e.altKey) {
										inp.onTrigger2Click();
										return false;
									}
								}
							},
							width: 350,
							tabIndex : 1317
						}]
					}, {
						layout: 'form',
						border : false,
						width : 520,
						labelWidth : 160,
						items:[{
							editable: false,
							codeField: 'OMSSprTerr_Code',
							hiddenName: 'OMSSprTerr_id',
							width : 350,
							xtype: 'swomssprterrcombo',
							tabIndex : 1318,
							fieldLabel: langs('Территория страхования')
						}]
					}, {
						layout: 'form',
						border : false,
						width : 520,
						labelWidth : 160,
						items: [{
							codeField: 'PolisType_Code',
							editable: false,
							border : false,
							width : 350,
							hiddenName: 'PolisType_id',
							xtype: 'swpolistypecombo',
							fieldLabel: langs('Тип полиса'),
							tabIndex : 1319
						}]
					},
					{
						layout: 'form',
						border : false,
						width : 520,
						labelWidth : 160,
						items: [{
							allowBlank: true,
							id: 'ERIS_OrgSMO_id',
							validateOnBlur: false,
							validationEvent: false,
							editable: false,
							xtype: 'sworgsmocombo',
							hiddenName: 'OrgSMO_id',
							listWidth: '300',
							onTrigger2Click: function() {
								if ( this.disabled )
										return;
								var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
								var combo = this;
								getWnd('swOrgSearchWindow').show({
									onSelect: function(orgData) {
										if ( orgData.Org_id > 0 )
										{
											combo.getStore().load({
												params: {
													Object:'OrgSMO',
													OrgSMO_id: orgData.Org_id,
													OrgSMO_Name: ''
												},
												callback: function()
												{
													combo.setValue(orgData.Org_id);
													combo.focus(true, 500);
													combo.fireEvent('change', combo);
												}
											});
										}
										getWnd('swOrgSearchWindow').hide();
									},
									object: 'smo'
								});
							},
							enableKeyEvents: true,
							listeners: {
								'keydown': function( inp, e ) {
									if ( e.F4 == e.getKey() )
									{
										if ( inp.disabled )
											return;
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;
										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;
										e.browserEvent.returnValue = false;
										e.returnValue = false;
										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										inp.onTrigger1Click();
										return false;
									}
									if ( e.DELETE == e.getKey() && e.altKey) {
										inp.onTrigger2Click();
										return false;
									}
								},
								'keyup': function(inp, e) {
									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;
										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;
										e.browserEvent.returnValue = false;
										e.returnValue = false;
										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
									if ( e.DELETE == e.getKey() && e.altKey) {
										inp.onTrigger2Click();
										return false;
									}
								}
							},
							width : 350,
							tabIndex : 1320
						}]
					}, {
						layout: 'form',
						width: 520,
						border : false,
						labelWidth : 160,
						items:[{
							allowBlank: true,
							xtype: 'sworgcombo',
							hiddenName: 'Org_id',
							editable: false,
							fieldLabel: langs('Место работы, учебы'),
							triggerAction: 'none',
							onTrigger1Click: function() {
								var ownerWindow = swReceptInCorrectSearchWindow;
								var combo = this;
								getWnd('swOrgSearchWindow').show({
									onSelect: function(orgData) {
										if ( orgData.Org_id > 0 )
										{
											combo.getStore().load({
												params: {
													Object:'Org',
													Org_id: orgData.Org_id,
													Org_Name:''
												},
												callback: function()
												{
													combo.setValue(orgData.Org_id);
													combo.focus(true, 500);
													combo.fireEvent('change', combo);
												}
											});
										}
										getWnd('swOrgSearchWindow').hide();
									}
								});
							},
							enableKeyEvents: true,
							listeners: {
								'keydown': function( inp, e ) {
									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;
										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;
										e.browserEvent.returnValue = false;
										e.returnValue = false;
										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										inp.onTrigger1Click();
										return false;
									}
									if ( e.DELETE == e.getKey() && e.altKey) {
										inp.onTrigger2Click();
										return false;
									}
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
										if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() > 0) {
											e.stopEvent();
											TabToGrid(EvnReceptIncorrectSearchViewGrid);
										}
									}
								},
								'keyup': function(inp, e) {
									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;
										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;
										e.browserEvent.returnValue = false;
										e.returnValue = false;
										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
									if ( e.DELETE == e.getKey() && e.altKey) {
										inp.onTrigger2Click();
										return false;
									}
								}
							},
							width : 350,
							tabIndex : 1321
						}]
					}]
				}, {
					autoHeight: true,
					labelWidth: 120,
					layout:'form',
					style: 'padding: 2px',
					title: '<u>3</u>. Адрес',
					items: [{
						codeField: 'KLAreaStat_Code',
						displayField: 'KLArea_Name',
						editable: true,
						enableKeyEvents: true,
						fieldLabel: langs('Территория'),
						hiddenName: 'KLAreaStat_id',
						id: 'ERIS_KLAreaStatCombo',
						listeners: {
							'keydown': function (inp, e) {
								if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
								{
									e.stopEvent();
									swReceptInCorrectSearchWindow.buttons[5].focus();
								}
							},
							'change': function(combo, newValue, oldValue) {
								var current_window = swReceptInCorrectSearchWindow;
								var index = combo.getStore().findBy(function(rec) { return rec.get('KLAreaStat_id') == newValue; });

								current_window.findById('ERIS_CountryCombo').enable();
								current_window.findById('ERIS_RegionCombo').enable();
								current_window.findById('ERIS_SubRegionCombo').enable();
								current_window.findById('ERIS_CityCombo').enable();
								current_window.findById('ERIS_TownCombo').enable();
								current_window.findById('ERIS_StreetCombo').enable();

								if (index == -1)
								{
									return false;
								}

								var current_record = combo.getStore().getAt(index);

								var country_id = current_record.data.KLCountry_id;
								var region_id = current_record.data.KLRGN_id;
								var subregion_id = current_record.data.KLSubRGN_id;
								var city_id = current_record.data.KLCity_id;
								var town_id = current_record.data.KLTown_id;
								var klarea_pid = 0;
								var level = 0;

								clearAddressCombo(
									current_window.findById('ERIS_CountryCombo').areaLevel, 
									{'Country': current_window.findById('ERIS_CountryCombo'),
									'Region': current_window.findById('ERIS_RegionCombo'),
									'SubRegion': current_window.findById('ERIS_SubRegionCombo'),
									'City': current_window.findById('ERIS_CityCombo'),
									'Town': current_window.findById('ERIS_TownCombo'),
									'Street': current_window.findById('ERIS_StreetCombo')
									}
								);

								if (country_id != null)
								{
									current_window.findById('ERIS_CountryCombo').setValue(country_id);
									current_window.findById('ERIS_CountryCombo').disable();
								}
								else
								{
									return false;
								}

								current_window.findById('ERIS_RegionCombo').getStore().load({
									callback: function() {
										current_window.findById('ERIS_RegionCombo').setValue(region_id);
									},
									params: {
										country_id: country_id,
										level: 1,
										value: 0
									}
								});

								if (region_id.toString().length > 0)
								{
									klarea_pid = region_id;
									level = 1;
								}

								current_window.findById('ERIS_SubRegionCombo').getStore().load({
									callback: function() {
										current_window.findById('ERIS_SubRegionCombo').setValue(subregion_id);
									},
									params: {
										country_id: 0,
										level: 2,
										value: klarea_pid
									}
								});

								if (subregion_id.toString().length > 0)
								{
									klarea_pid = subregion_id;
									level = 2;
								}

								current_window.findById('ERIS_CityCombo').getStore().load({
									callback: function() {
										current_window.findById('ERIS_CityCombo').setValue(city_id);
									},
									params: {
										country_id: 0,
										level: 3,
										value: klarea_pid
									}
								});

								if (city_id.toString().length > 0)
								{
									klarea_pid = city_id;
									level = 3;
								}

								current_window.findById('ERIS_TownCombo').getStore().load({
									callback: function() {
										current_window.findById('ERIS_TownCombo').setValue(town_id);
									},
									params: {
										country_id: 0,
										level: 4,
										value: klarea_pid
									}
								});

								if (town_id.toString().length > 0)
								{
									klarea_pid = town_id;
									level = 4;
								}

								current_window.findById('ERIS_StreetCombo').getStore().load({
									params: {
										country_id: 0,
										level: 5,
										value: klarea_pid
									}
								});

								switch (level)
								{
									case 1:
										current_window.findById('ERIS_RegionCombo').disable();
										break;

									case 2:
										current_window.findById('ERIS_RegionCombo').disable();
										current_window.findById('ERIS_SubRegionCombo').disable();
										break;

									case 3:
										current_window.findById('ERIS_RegionCombo').disable();
										current_window.findById('ERIS_SubRegionCombo').disable();
										current_window.findById('ERIS_CityCombo').disable();
										break;

									case 4:
										current_window.findById('ERIS_RegionCombo').disable();
										current_window.findById('ERIS_SubRegionCombo').disable();
										current_window.findById('ERIS_CityCombo').disable();
										current_window.findById('ERIS_TownCombo').disable();
										break;
								}
							}
						},
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'KLAreaStat_id', type: 'int' },
								{ name: 'KLAreaStat_Code', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' },
								{ name: 'KLCountry_id', type: 'int' },
								{ name: 'KLRGN_id', type: 'int' },
								{ name: 'KLSubRGN_id', type: 'int' },
								{ name: 'KLCity_id', type: 'int' },
								{ name: 'KLTown_id', type: 'int' }
							],
							key: 'KLAreaStat_id',
							sortInfo: {
								field: 'KLAreaStat_Code',
								direction: 'ASC'
							},
							tableName: 'KLAreaStat'
						}),
						tabIndex: 1323,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}' +
							'</div></tpl>',
						valueField: 'KLAreaStat_id',
						width: 620,
						xtype: 'swbaselocalcombo'
					}, {
						areaLevel: 0,
						codeField: 'KLCountry_Code',
						displayField: 'KLCountry_Name',
						editable: true,
						fieldLabel: langs('Страна'),
						hiddenName: 'KLCountry_id',
						id: 'ERIS_CountryCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptInCorrectSearchWindow.findById('ERIS_CountryCombo'),
										'Region': swReceptInCorrectSearchWindow.findById('ERIS_RegionCombo'),
										'SubRegion': swReceptInCorrectSearchWindow.findById('ERIS_SubRegionCombo'),
										'City': swReceptInCorrectSearchWindow.findById('ERIS_CityCombo'),
										'Town': swReceptInCorrectSearchWindow.findById('ERIS_TownCombo'),
										'Street': swReceptInCorrectSearchWindow.findById('ERIS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
								else
								{
									clearAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptInCorrectSearchWindow.findById('ERIS_CountryCombo'),
										'Region': swReceptInCorrectSearchWindow.findById('ERIS_RegionCombo'),
										'SubRegion': swReceptInCorrectSearchWindow.findById('ERIS_SubRegionCombo'),
										'City': swReceptInCorrectSearchWindow.findById('ERIS_CityCombo'),
										'Town': swReceptInCorrectSearchWindow.findById('ERIS_TownCombo'),
										'Street': swReceptInCorrectSearchWindow.findById('ERIS_StreetCombo')
										}
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE)
								{
									if (combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
									{
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								}
							},
							'select': function(combo, record, index) {
								if (record.data.KLCountry_id == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.data.KLArea_id, null);
							}
						},
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'KLCountry_id', type: 'int' },
								{ name: 'KLCountry_Code', type: 'int' },
								{ name: 'KLCountry_Name', type: 'string' }
							],
							key: 'KLCountry_id',
							sortInfo: {
								field: 'KLCountry_Name'
							},
							tableName: 'KLCountry'
						}),
						tabIndex: 1324,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}' +
							'</div></tpl>',
						valueField: 'KLCountry_id',
						width: 620,
						xtype: 'swbaselocalcombo'
					}, {
						areaLevel: 1,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Регион'),
						hiddenName: 'KLRgn_id',
						id: 'ERIS_RegionCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptInCorrectSearchWindow.findById('ERIS_CountryCombo'),
										'Region': swReceptInCorrectSearchWindow.findById('ERIS_RegionCombo'),
										'SubRegion': swReceptInCorrectSearchWindow.findById('ERIS_SubRegionCombo'),
										'City': swReceptInCorrectSearchWindow.findById('ERIS_CityCombo'),
										'Town': swReceptInCorrectSearchWindow.findById('ERIS_TownCombo'),
										'Street': swReceptInCorrectSearchWindow.findById('ERIS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
								else
								{
									clearAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptInCorrectSearchWindow.findById('ERIS_CountryCombo'),
										'Region': swReceptInCorrectSearchWindow.findById('ERIS_RegionCombo'),
										'SubRegion': swReceptInCorrectSearchWindow.findById('ERIS_SubRegionCombo'),
										'City': swReceptInCorrectSearchWindow.findById('ERIS_CityCombo'),
										'Town': swReceptInCorrectSearchWindow.findById('ERIS_TownCombo'),
										'Street': swReceptInCorrectSearchWindow.findById('ERIS_StreetCombo')
										}
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if (record.data.KLArea_id == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.data.KLArea_id);
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 1325,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'{KLArea_Name}' +
							'</div></tpl>',
						triggerAction: 'all',
						valueField: 'KLArea_id',
						xtype: 'combo'
					}, {
						areaLevel: 2,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Район'),
						hiddenName: 'KLSubRgn_id',
						id: 'ERIS_SubRegionCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptInCorrectSearchWindow.findById('ERIS_CountryCombo'),
										'Region': swReceptInCorrectSearchWindow.findById('ERIS_RegionCombo'),
										'SubRegion': swReceptInCorrectSearchWindow.findById('ERIS_SubRegionCombo'),
										'City': swReceptInCorrectSearchWindow.findById('ERIS_CityCombo'),
										'Town': swReceptInCorrectSearchWindow.findById('ERIS_TownCombo'),
										'Street': swReceptInCorrectSearchWindow.findById('ERIS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
								else
								{
									clearAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptInCorrectSearchWindow.findById('ERIS_CountryCombo'),
										'Region': swReceptInCorrectSearchWindow.findById('ERIS_RegionCombo'),
										'SubRegion': swReceptInCorrectSearchWindow.findById('ERIS_SubRegionCombo'),
										'City': swReceptInCorrectSearchWindow.findById('ERIS_CityCombo'),
										'Town': swReceptInCorrectSearchWindow.findById('ERIS_TownCombo'),
										'Street': swReceptInCorrectSearchWindow.findById('ERIS_StreetCombo')
										}
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if (record.data.KLArea_id == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.data.KLArea_id);
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 1326,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'{KLArea_Name}' +
							'</div></tpl>',
						triggerAction: 'all',
						valueField: 'KLArea_id',
						xtype: 'combo'
					}, {
						areaLevel: 3,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Город'),
						hiddenName: 'KLCity_id',
						id: 'ERIS_CityCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptInCorrectSearchWindow.findById('ERIS_CountryCombo'),
										'Region': swReceptInCorrectSearchWindow.findById('ERIS_RegionCombo'),
										'SubRegion': swReceptInCorrectSearchWindow.findById('ERIS_SubRegionCombo'),
										'City': swReceptInCorrectSearchWindow.findById('ERIS_CityCombo'),
										'Town': swReceptInCorrectSearchWindow.findById('ERIS_TownCombo'),
										'Street': swReceptInCorrectSearchWindow.findById('ERIS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if (record.data.KLArea_id == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.data.KLArea_id);
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 1327,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'{KLArea_Name}' +
							'</div></tpl>',
						triggerAction: 'all',
						valueField: 'KLArea_id',
						xtype: 'combo'
					}, {
						areaLevel: 4,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Населенный пункт'),
						hiddenName: 'KLTown_id',
						id: 'ERIS_TownCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptInCorrectSearchWindow.findById('ERIS_CountryCombo'),
										'Region': swReceptInCorrectSearchWindow.findById('ERIS_RegionCombo'),
										'SubRegion': swReceptInCorrectSearchWindow.findById('ERIS_SubRegionCombo'),
										'City': swReceptInCorrectSearchWindow.findById('ERIS_CityCombo'),
										'Town': swReceptInCorrectSearchWindow.findById('ERIS_TownCombo'),
										'Street': swReceptInCorrectSearchWindow.findById('ERIS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if (record.data.KLArea_id == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.data.KLArea_id);
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 1328,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'{KLArea_Name}' +
							'</div></tpl>',
						triggerAction: 'all',
						valueField: 'KLArea_id',
						xtype: 'combo'
					}, {
						displayField: 'KLStreet_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Улица'),
						hiddenName: 'KLStreet_id',
						id: 'ERIS_StreetCombo',
						listeners: {
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.clearValue();
								}
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLStreet_id', type: 'int' },
								{ name: 'KLStreet_Name', type: 'string' }
							],
							key: 'KLStreet_id',
							sortInfo: {
								field: 'KLStreet_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 1329,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'{KLStreet_Name}' +
							'</div></tpl>',
						triggerAction: 'all',
						valueField: 'KLStreet_id',
						xtype: 'combo'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							width: 300,
							layout: 'form',
							items: [{
								fieldLabel: langs('Дом'),
								id: 'ERIS_Address_House',
								listeners: {
									'keydown': function(inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptIncorrectSearchViewGrid);
											}
										}
									}
								},
								name: 'Address_House',
								tabIndex: 1330,
								width: 156,
								xtype: 'textfield'
							}]
						}]
					}]
				}, {
					title : "<u>4</u>. Рецепт",
					border: false,
					frame: false,
					autoHeight: true,
					//height : 300,
					style: 'padding: 0px; margin-bottom: 5px;',
					items : [{
						layout: 'column',
						border : false,
						items: [{
							layout: 'column',
							border : false,
							style: 'margin-top: 3px',
							items: [{
									layout: 'form',
									border: false,
									hidden: getRegionNick().inlist(['kz']),
									items: [{
										fieldLabel: langs('Рецепт серия'),
										name: 'EvnRecept_Ser',
										id: 'ERIS_EvnRecept_Ser',
										xtype : "textfield",
										anchor: '100%',
										tabIndex : 1334,
										listeners: {
											'keydown': function (inp, e) {
												if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
												{
													e.stopEvent();
													swReceptInCorrectSearchWindow.buttons[5].focus();
												}
											}
										}
									}],
									width: 250
								},
								{
									layout: 'form',
									border: false,
									labelWidth: getRegionNick().inlist(['kz'])?100:30,
									items:[{
										fieldLabel: '№',
										name: 'EvnRecept_Num',
										xtype : "textfield",
										width: 140,
										tabIndex : 1335
									}]/*,
									width: 175*/
								}
							]
						}, {
							labelWidth: getRegionNick().inlist(['kz'])?300:120,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: [{
								displayField: 'ReceptDiscount_Name',
								codeField: 'ReceptDiscount_Code',
								editable: false,
								fieldLabel: langs('Скидка'),
								hiddenName: 'ReceptDiscount_id',
								id: 'ERIS_ReceptDiscountCombo',
								listWidth: 100,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'ReceptDiscount_Name', mapping: 'ReceptDiscount_Name' },
										{ name: 'ReceptDiscount_Code', mapping: 'ReceptDiscount_Code' },
										{ name: 'ReceptDiscount_id', mapping: 'ReceptDiscount_id' }
									],
									key: 'ReceptDiscount_id',
									sortInfo: { field: 'ReceptDiscount_Code' },
									tableName: 'ReceptDiscount'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td style="width: 15px;"><font color="red">{ReceptDiscount_Code}</font></td><td><h3>{ReceptDiscount_Name}</h3></td></tr></table>',
									'</div></tpl>'
								),
								//trigger2Class: 'hideTrigger',
								valueField: 'ReceptDiscount_id',
								width: 300,
								xtype: 'swbaselocalcombo',
								tabIndex : 1342
							}]
						}]
					}, {
						layout: 'column',
						border : false,
						items: [{
							layout: 'form',
							border : false,
							items:[{
								xtype: 'swlpucombo',
								name: 'Lpu_id',
								width: 320,
								tabIndex : 1336,
								listeners: {
									'change': function(combo,newValue){
										var searchForm = this.EvnReceptIncorrectSearchFilterForm.getForm();
										if(!Ext.isEmpty(newValue) && newValue != 0){
											var LpuBuilding_id = searchForm.findField('LpuBuilding_id').getValue();
											searchForm.findField('LpuBuilding_id').getStore().removeAll();
											searchForm.findField('LpuBuilding_id').setValue('');
											searchForm.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = newValue;
											searchForm.findField('LpuBuilding_id').getStore().baseParams.AptLpu_id = newValue;
											searchForm.findField('LpuBuilding_id').getStore().load();
											searchForm.findField('LpuBuilding_id').enable();
											var MedPersonal_id = searchForm.findField('ER_MedPersonal_id').getValue();
											searchForm.findField('ER_MedPersonal_id').getStore().removeAll();
											searchForm.findField('ER_MedPersonal_id').getStore().load({
												params: {Lpu_id: newValue},
												callback: function () {
													if(MedPersonal_id){
														if(searchForm.findField('ER_MedPersonal_id').getStore().getById(MedPersonal_id)){
															searchForm.findField('ER_MedPersonal_id').setValue(MedPersonal_id);
														} else {
															searchForm.findField('ER_MedPersonal_id').setValue('');
														}
													}
												}
											});
										} else {
											searchForm.findField('LpuBuilding_id').setValue('');
											searchForm.findField('LpuBuilding_id').disable();
											searchForm.findField('ER_MedPersonal_id').getStore().removeAll();
											searchForm.findField('ER_MedPersonal_id').setValue('');
										}
									}.createDelegate(this)
								}
							}]
						}, {
							labelWidth : 120,
							layout: 'form',
							autoHeight: true,
							border : false,
							//style: 'margin-top: 3px',
							items: [{
								displayField: 'ReceptValid_Name',
								codeField: 'ReceptValid_Code',
								editable: false,
								fieldLabel: langs('Срок действия'),
								hiddenName: 'ReceptValid_id',
								id: 'ERIS_ReceptValidCombo',
								listWidth: 100,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'ReceptValid_Name', mapping: 'ReceptValid_Name', type: 'string' },
										{ name: 'ReceptValid_Code', mapping: 'ReceptValid_Code', type: 'int' },
										{ name: 'ReceptValid_id', mapping: 'ReceptValid_id', type: 'int' }
									],
									key: 'ReceptValid_id',
									sortInfo: { field: 'ReceptValid_Code' },
									tableName: 'ReceptValid'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td><h3><font style="color: #f00;">{ReceptValid_Code}</font> {ReceptValid_Name}</h3></td></tr></table>',
									'</div></tpl>'
								),
								//trigger2Class: 'hideTrigger',
								value: 2,
								valueField: 'ReceptValid_id',
								width: 300,
								xtype: 'swbaselocalcombo',
								tabIndex : 1343
							}]
						}]
					}, {
						layout: 'column',
						border : false,
						items: [{
							layout: 'form',
							border : false,
							items:[{
								xtype: 'swlpubuildingcombo',
								disabled: true,
								editable: true,
								name: 'LpuBuilding_id',
								width: 320,
								tabIndex : 1337,
								listeners: {
									'change': function(combo,newValue){
										var searchForm = this.EvnReceptIncorrectSearchFilterForm.getForm();
										var Lpu_id = searchForm.findField('Lpu_id').getValue();
										var paramsMP = {
											Lpu_id: Lpu_id
										};
										if(!Ext.isEmpty(newValue) && newValue != 0){
											var LpuSection_id = searchForm.findField('LpuSection_id').getValue();
											searchForm.findField('LpuSection_id').getStore().removeAll();
											searchForm.findField('LpuSection_id').getStore().load({
												params: {LpuBuilding_id: newValue},
												callback: function () {
													if(LpuSection_id){
														if(searchForm.findField('LpuSection_id').getStore().getById(LpuSection_id)){
															searchForm.findField('LpuSection_id').setValue(LpuSection_id);
														} else {
															searchForm.findField('LpuSection_id').setValue('');
														}
													}
													searchForm.findField('LpuSection_id').enable();
												}
											});
											paramsMP.LpuBuilding_id = newValue;
										} else {
											searchForm.findField('LpuSection_id').setValue('');
											searchForm.findField('LpuSection_id').disable();
										}
										var MedPersonal_id = searchForm.findField('ER_MedPersonal_id').getValue();
										searchForm.findField('ER_MedPersonal_id').getStore().removeAll();
										searchForm.findField('ER_MedPersonal_id').getStore().load({
											params: paramsMP,
											callback: function () {
												if(MedPersonal_id){
													if(searchForm.findField('ER_MedPersonal_id').getStore().getById(MedPersonal_id)){
														searchForm.findField('ER_MedPersonal_id').setValue(MedPersonal_id);
													} else {
														searchForm.findField('ER_MedPersonal_id').setValue('');
													}
												}
											}
										});
									}.createDelegate(this)
								}
							}]
						}, {
							labelWidth : 120,
							layout: 'form',
							autoHeight: true,
							border : false,
							items: [{
								allowBlank: true,
								fieldLabel: langs('Тип рецепта'),
								id: 'ERIS_ReceptTypeCombo',
								hiddenName: 'ReceptType_id',
								tabIndex: 1344,
								width: 300,
								xtype: 'swrecepttypecombo'
							}]
						}]
					}, {
						layout: 'column',
						border : false,
						items: [{
							layout: 'form',
							border : false,
							items:[{
								xtype: 'swlpusectioncombo',
								name: 'LpuSection_id',
								disabled: true,
								width: 320,
								tabIndex : 1338,
								listeners: {
									'change': function(combo,newValue){
										var searchForm = this.EvnReceptIncorrectSearchFilterForm.getForm();
										var MedPersonal_id = searchForm.findField('ER_MedPersonal_id').getValue();
										var Lpu_id = searchForm.findField('Lpu_id').getValue();
										var LpuBuilding_id = searchForm.findField('LpuBuilding_id').getValue();
										var paramsMP = {
											Lpu_id: Lpu_id,
											LpuBuilding_id: LpuBuilding_id 
										};
										if(!Ext.isEmpty(newValue) && LpuSection_id != 0){
											paramsMP.LpuSection_id = newValue;
										}
										searchForm.findField('ER_MedPersonal_id').getStore().removeAll();
										searchForm.findField('ER_MedPersonal_id').getStore().load({
											params: paramsMP,
											callback: function () {
												if(MedPersonal_id){
													if(searchForm.findField('ER_MedPersonal_id').getStore().getById(MedPersonal_id)){
														searchForm.findField('ER_MedPersonal_id').setValue(MedPersonal_id);
													} else {
														searchForm.findField('ER_MedPersonal_id').setValue('');
													}
												}
											}
										});
									}.createDelegate(this)
								}
							}]
						}, {
							layout: 'form',
							autoHeight: true,
							border : false,
							items: [{
	                            labelWidth : 120,
	                            layout: 'form',
	                            autoHeight: true,
	                            border : false,
	                            items:[{
	                                codeField: 'ReceptForm_Code',
	                                displayField: 'ReceptForm_Name',
	                                editable: false,
	                                tabIndex : 1345,
	                                fieldLabel: langs('Форма рецепта'),
	                                hiddenName: 'ReceptForm_id',
	                                lastQuery: '',
	                                store: new Ext.data.Store({
	                                    autoLoad: true,
	                                    reader: new Ext.data.JsonReader({
	                                        id: 'ReceptForm_id'
	                                    }, [
	                                        { name: 'ReceptForm_id', mapping: 'ReceptForm_id', type: 'int', hidden: 'true'},
	                                        { name: 'ReceptForm_Code', mapping: 'ReceptForm_Code'},
	                                        { name: 'ReceptForm_Name', mapping: 'ReceptForm_Name' }
	                                    ]),
	                                    url: C_RECEPTFORM_GET_LIST
	                                }),
	                                tpl: new Ext.XTemplate(
	                                    '<tpl for="."><div class="x-combo-list-item">',
	                                    '<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{ReceptForm_Code}</font></td><td style="font-weight: normal;">{ReceptForm_Name}</td></tr></table>',
	                                    '</div></tpl>'
	                                ),
	                                validateOnBlur: true,
	                                valueField: 'ReceptForm_id',
	                                width: 300,
	                                xtype: 'swbaselocalcombo'
	                            }]
	                        }]
                        }]
					}, {
						layout: 'column',
						border : false,
						items:[{
							layout: 'form',
							border : false,
							items:[{
								allowBlank: true,
								codeField: 'MedPersonal_Code',
								editable: true,
								displayField: 'MedPersonal_Fio',
								fieldLabel: langs('Врач'),
								hiddenName: 'ER_MedPersonal_id',
								id: 'ERIS_MedPersonalCombo',
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'MedPersonal_id'
									}, [
										{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
										{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
										{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' }
									]),
									url: C_MP_DLO_LOADLIST
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
									'</div></tpl>'
								),
								triggerAction: 'all',
								hideTrigger: false,
								valueField: 'MedPersonal_id',
								width: 320,
								xtype: 'swbaselocalcombo',
								tabIndex : 1339
							}]
						}, {
							layout: 'form',
							border : false,
							labelWidth : 120,
							items: [{
								fieldLabel: langs('Аптека'),
								id: 'ERIS_OrgFarmacy',
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptIncorrectSearchViewGrid);
											}
										}
									}
								},
								name: 'OrgFarmacy_id',
								tabIndex: 1346,
								xtype : "sworgfarmacycombo",
								width: 300
							}],
							width: 430
						}]
					}, {
						layout: 'column',
						border : false,
						items:[{
							layout: 'form',
							border : false,
							items:[{
								allowBlank: true,
								id: 'ERIS_DrugMnnCombo',
								emptyText: langs('Начните вводить МНН...'),
								onTrigger2Click: function() {
									var drug_mnn_combo = this;
									var current_window = Ext.getCmp('EvnReceptInCorrectSearchWindow');

									getWnd('swDrugMnnSearchWindow').show({
										onClose: function() {
											drug_mnn_combo.focus(false);
										},
										onSelect: function(drugMnnData) {
											drug_mnn_combo.setValue(drugMnnData.DrugMnn_id);
											var index = drug_mnn_combo.getStore().findBy(function(rec) { return rec.get('DrugMnn_id') == drugMnnData.DrugMnn_id; });
											var record = drug_mnn_combo.getStore().getAt(index);

											if (record)
											{
												drug_mnn_combo.fireEvent('change', drug_mnn_combo, drugMnnData.DrugMnn_id, 0);
											}

											getWnd('swDrugMnnSearchWindow').hide();
											drug_mnn_combo.focus(false);
										}
									});
								},
								lastQuery: '',
								listeners: {
									'keydown': function(inp, e) {
										if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
										{
											e.stopEvent();

											if (e.browserEvent.stopPropagation)
											{
												e.browserEvent.stopPropagation();
											}
											else
											{
												e.browserEvent.cancelBubble = true;
											}

											if (e.browserEvent.preventDefault)
											{
												e.browserEvent.preventDefault();
											}
											else
											{
												e.browserEvent.returnValue = false;
											}

											e.browserEvent.returnValue = false;
											e.returnValue = false;

											if (Ext.isIE)
											{
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											switch (e.getKey())
											{
												case Ext.EventObject.DELETE:
													inp.setValue('');
													inp.setRawValue('');
													break;

												case Ext.EventObject.F4:
													inp.onTrigger2Click();
													break;
											}
										}
									},
									'beforeselect': function() {
										Ext.getCmp('ERIS_DrugCombo').lastQuery = '';
									},
									'change': function(combo, newValue, oldValue) {
										var drug_combo = Ext.getCmp('ERIS_DrugCombo');

										drug_combo.clearValue();
										drug_combo.getStore().removeAll();
										drug_combo.lastQuery = '';

										var ReceptFinance = drug_combo.getStore().baseParams.ReceptFinance_Code;
										drug_combo.getStore().baseParams = {};
										drug_combo.getStore().baseParams.ReceptFinance_Code = ReceptFinance;
										drug_combo.getStore().baseParams.DrugMnn_id = newValue;
										drug_combo.getStore().baseParams.query = '';
										drug_combo.getStore().baseParams.searchFull = 1;

										if (newValue > 0)
										{
											drug_combo.getStore().load();
										}
									},
									'blur': function(combo)
									{
										if (combo.getRawValue() == '')
										{
											combo.setValue('');
										}
										else
										{
											return false;
										}
									}
								},
								listWidth: 800,
								minChars: 0,
								minLength: 1,
								minLengthText: langs('Поле должно быть заполнено'),
								plugins: [ new Ext.ux.translit(true) ],
								queryDelay: 250,
								tabIndex: 1340,
								trigger2Class: 'hideTrigger',
								validateOnBlur: false,
								width: 337,
								xtype: 'swdrugmnncombo'
							}]
						}, {
							layout: 'form',
							border : false,
							labelWidth : 333,
							tabIndex : 1347,
							id: 'ERIS_EvnRecept_IsNotOstatForm',
							items: [
								new sw.Promed.SwYesNoCombo({
									fieldLabel: langs('Выписка без наличия в аптеке'),
									hiddenName: 'EvnRecept_IsNotOstat',
									id: 'ERIS_EvnRecept_IsNotOstat',
									tabIndex: 1338,
									width: 70
								})
							],
							width: 430
						}]
					}, {
						layout: 'form',
						border : false,
						items:[{
							allowBlank: true,
							id: 'ERIS_DrugCombo',
							listeners: {
								'beforeselect': function(combo, record, index) {
									combo.setValue(record.get('Drug_id'));

									var drug_mnn_combo = Ext.getCmp('ERIS_DrugMnnCombo');
									var drug_mnn_record = drug_mnn_combo.getStore().getById(record.get('DrugMnn_id'));

									if (drug_mnn_record)
									{
										drug_mnn_combo.setValue(record.get('DrugMnn_id'));
									}
									else
									{
										if (combo.getRawValue()!='') {
											var ReceptFinance = drug_mnn_combo.getStore().baseParams.ReceptFinance_Code;
											drug_mnn_combo.getStore().baseParams = {};
											drug_mnn_combo.getStore().baseParams.ReceptFinance_Code = ReceptFinance;
											drug_mnn_combo.getStore().baseParams.searchFull = 1;
											drug_mnn_combo.getStore().load({
												callback: function() {
													drug_mnn_combo.setValue(record.get('DrugMnn_id'));
												},
												params: {
													DrugMnn_id: record.get('DrugMnn_id')
												}
											})
										}
									}
								},
								'keydown': function(inp, e) {
									if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
									{
										e.stopEvent();

										if (e.browserEvent.stopPropagation)
										{
											e.browserEvent.stopPropagation();
										}
										else
										{
											e.browserEvent.cancelBubble = true;
										}

										if (e.browserEvent.preventDefault)
										{
											e.browserEvent.preventDefault();
										}
										else
										{
											e.browserEvent.returnValue = false;
										}

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if (Ext.isIE)
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										switch (e.getKey())
										{
											case Ext.EventObject.DELETE:
												inp.setValue('');
												inp.setRawValue('');
												break;

											case Ext.EventObject.F4:
												inp.onTrigger2Click();
												break;
										}
									}
								}
							},
							listWidth: 800,
							loadingText: langs('Идет поиск...'),
							minLengthText: langs('Поле должно быть заполнено'),
							onTrigger2Click: function() {
								var drug_combo = this;
								var current_window = Ext.getCmp('EvnReceptInCorrectSearchWindow');

								getWnd('swDrugTorgSearchWindow').show({
									onHide: function() {
										drug_combo.focus(false);
									},
									onSelect: function(drugTorgData) {
										drug_combo.getStore().removeAll();

										drug_combo.getStore().loadData([{
											Drug_Code: drugTorgData.Drug_Code,
											Drug_id: drugTorgData.Drug_id,
											Drug_Name: drugTorgData.Drug_Name,
											Drug_Price: drugTorgData.Drug_Price,
											DrugMnn_id: drugTorgData.DrugMnn_id
										}]);

										drug_combo.setValue(drugTorgData.Drug_id);
										drug_combo.getStore().baseParams.Drug_id = drugTorgData.Drug_id;
										drug_combo.getStore().baseParams.DrugMnn_id = 0;
										drug_combo.getStore().baseParams.searchFull = 1;
										index = drug_combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == drugTorgData.Drug_id; });
										record = drug_combo.getStore().getAt(index);

										if (record)
										{
											drug_combo.fireEvent('beforeselect', drug_combo, record);
										}

										getWnd('swDrugTorgSearchWindow').hide();
									}
								});
							},
							tabIndex: 1341,
							trigger2Class: 'hideTrigger',
							validateOnBlur: false,
							width: 337,
							xtype: 'swdrugcombo'
						}]
					} /*{
						xtype: 'fieldset',
						autoHeight: true,
						title: langs('Удостоверение'),
						style: 'padding: 3px; margin-bottom: 2px; display:block;',
						labelWidth : 103,
						items: [{
							layout: 'column',
							border : false,
							items: [{
									layout: 'form',
									border : false,
									items: [{
										fieldLabel: langs('Серия'),
										name: 'EvnUdost_Ser',
										xtype : "textfield",
										tabIndex : 1328
									}],
									width: 300
								},
								{
									layout: 'form',
									border : false,
									labelWidth : 50,
									items:[{
										fieldLabel: langs('Номер'),
										name: 'EvnUdost_Num',
										xtype : "textfield",
										tabIndex : 1329
									}],
									width: 300
								}
							]
						}]
					},*/]
				}, {
					title : "<u>5</u>. Рецепт (доп.)",
					border: false,
					frame: false, 
					autoHeight: true,
					//height : 200,
					style: 'padding: 0px; margin-bottom: 5px;',
					items : 
					[{
						layout: 'column',
						border : false,
						items: 
						[{
							labelWidth : 200,
							columnWidth: .5,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: 
							[{
								displayField: 'YesNo_Name',
								codeField: 'YesNo_Code',
								editable: false,
								fieldLabel: getGlobalOptions().region.nick != 'ufa' ? langs('Рецепт выписан в Промед'): 'Рецепт выписан в РМИАС',
								forceSelection : true,
								hiddenName: 'ReceptYes_id',
								id: 'ERIS_ReceptYes_id',
								tabIndex: 1331,
								value: 2,
								valueField: 'YesNo_id',
								width: 70,
								xtype: 'swyesnocombo',
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											swReceptInCorrectSearchWindow.buttons[5].focus();
										}
									}
								}	
							},
							{
								//anchor: '100%',
								width: 300,
								fieldLabel: langs('Результат'),
								hiddenName: 'ReceptResult_id',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										// 2. Не было обращения;  – при выборе устанавливать Выписан рецепт = Да
										if (newValue == 2) {
											EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
										}
										//9. Рецепт просрочен без обращения;  – при выборе устанавливать Выписан рецепт = Да
										if (newValue == 2) {
											EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
										}
										
										/*При изменении в поле Результат и если нарушается условие
										Результат ! = Не было обращения 
										langs('и') 
										Результат ! = Рецепт просрочен без обращения,
										то поля Время обращ… очищать.*/
										if ( !(newValue != 2 && newValue != 9) ) {
											EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_obrTimeFrom').setValue('');
											EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_obrTimeTo').setValue('');
										}
										
										/*	При изменении в поле Результат и если нарушается условие
											Результат = Рецепт отоварен
											langs('или')
											Результат = Рецепт отоварен после отсрочки
											langs('или')
											Результат = Рецепт отсрочен,
											то поля "Отсрочка отоваривания рецепта, от ... до ..." очищать.
										*/
										if ( !(newValue == 4 || newValue == 6 || newValue == 7) )
										{
											EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_otsTimeFrom').setValue('');
											EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_otsTimeTo').setValue('');
										}		
										
										/*При изменении в поле Результат и если нарушается условие
										Результат = Рецепт отоварен
										langs('или') 
										Результат = Рецепт отоварен без отсрочки
										langs('или')
										Результат = Рецепт отоварен после отсрочки,
										то поля Время отоваривания очищать.*/
										if ( !(newValue == 4 || newValue == 5 || newValue == 6) ) {
											EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_otovTimeFrom').setValue('');
											EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_otovTimeTo').setValue('');
										}
										
										/*При изменении в поле Результат и если нарушается условие
										Результат =  Рецепт отоварен после отсрочки
										langs('или')
										Результат =  Рецепт отсрочен
										langs('или')
										Результат =  Рецепт просрочен после отсрочки,
										то поле Дата актуальности отсрочки очищать.
										*/
										if ( !(newValue == 6 || newValue == 7 || newValue == 10) ) {
											EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_otsDate').setValue('');
										}
										
										/* При установке Результат = Рецепт отсрочен, 
										   если поле Дата актуальности отсрочки пустое, 
										   то в поле Дата актуальности отсрочки ставить текущую дату.
										*/
										if ( newValue == 7 && EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_otsDate').getValue() == "" )
										{
										 	EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_otsDate').setValue(Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
										}								
									}
								},
								tabIndex: 1332,
								valueField: 'ReceptResult_id',
								xtype: 'swreceptresultcombo'
							},
							{
								//anchor: '100%',
								width: 300,
								fieldLabel: langs('Несовпадения в рецептах'),
								hiddenName: 'ReceptMismatch_id',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										// При выборе в поле Несовпадения… любого значения кроме «пусто» устанавливать Выписан рецепт = Да
										if (newValue != null && newValue != '') {
											EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
										}
										/*и если нарушается условие 
										(Результат = Рецепт отоварен
										langs('или')
										Результат = Рецепт отоварен без отсрочки
										langs('или')
										Результат = Рецепт отоварен после отсрочки),
										то устанавливать Результат = Рецепт отоварен.*/
										var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
										if ( !(ReceptResult == 4 || ReceptResult == 5 || ReceptResult == 6) ) {
											EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
										}
									}
								},
								tabIndex: 1333,
								valueField: 'ReceptMismatch_id',
								xtype: 'swreceptmismatchcombo'
							},
							{
								layout: 'column',
								labelWidth : 310,
								border : false,
								items: 
								[{
									labelWidth : 310,
									layout: 'form',
									autoHeight: true,
									border : false,
									items:
									[{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_obrTimeFrom',
										id:  'ERIS_Recept_obrTimeFrom',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время обращ… любого значения и если нарушается условие 
												Результат ! = Не было обращения 
												langs('и') 
												Результат ! = Рецепт просрочен без обращения,
												то устанавливать Результат = Было обращение.
												*/
												if (newValue != '') {
													var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( ReceptResult == 2 || ReceptResult == 9 ) {
														EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(1);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: langs('Срок обращения в аптеку с момента выписки, от'),
										tabIndex: 1335
									},
									{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_otsTimeFrom',
										id:  'ERIS_Recept_otsTimeFrom',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время отсроч… любого значения и если нарушается условие 
												Результат = Рецепт отоварен
												langs('или') 
												Результат = Рецепт отоварен после отсрочки,
												то устанавливать Результат = Рецепт отоварен*/
												if (newValue != '') {
													var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( !(ReceptResult == 4 || ReceptResult == 6 || ReceptResult == 7) ) {
														EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: langs('Отсрочка отоваривания рецепта, от'),
										tabIndex: 1337
									},
									{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_otovTimeFrom',
										id:  'ERIS_Recept_otovTimeFrom',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время отовар… любого значения и если нарушается условие 
												Результат = Рецепт отоварен
												langs('или') 
												Результат = Рецепт отоварен без отсрочки
												langs('или') 
												Результат = Рецепт отоварен после отсрочки,
												то устанавливать Результат = Рецепт отоварен.
												*/
												if (newValue != '') {
													var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( !(ReceptResult == 4 || ReceptResult == 5 || ReceptResult == 6) ) {
														EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: langs('Срок отов. рецепта с момента выписки, от'),
										tabIndex: 1339
									}
									]
								},
								{
									labelWidth : 30,
									layout: 'form',
									autoHeight: true,
									border : false,
									items:
									[{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_obrTimeTo',
										id:  'ERIS_Recept_obrTimeTo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время обращ… любого значения и если нарушается условие 
												Результат ! = Не было обращения 
												langs('и') 
												Результат ! = Рецепт просрочен без обращения,
												то устанавливать Результат = Было обращение.
												*/
												if (newValue != '') {
													var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( ReceptResult == 2 || ReceptResult == 9 ) {
														EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(1);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: langs('До'),
										tabIndex: 1336
									},
									{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_otsTimeTo',
										id:  'ERIS_Recept_otsTimeTo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время отсроч… любого значения и если нарушается условие 
												Результат = Рецепт отоварен
												langs('или') 
												Результат = Рецепт отоварен после отсрочки,
												то устанавливать Результат = Рецепт отоварен*/
												if (newValue != '') {
													var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( !(ReceptResult == 4 || ReceptResult == 6 || ReceptResult == 7) ) {
														EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: langs('До'),
										tabIndex: 1338
									},
									{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_otovTimeTo',
										id:  'ERIS_Recept_otovTimeTo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время отовар… любого значения и если нарушается условие 
												Результат = Рецепт отоварен
												langs('или') 
												Результат = Рецепт отоварен без отсрочки
												langs('или') 
												Результат = Рецепт отоварен после отсрочки,
												то устанавливать Результат = Рецепт отоварен.
												*/
												if (newValue != '') {
													var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( !(ReceptResult == 4 || ReceptResult == 5 || ReceptResult == 6) ) {
														EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: langs('До'),
										tabIndex: 1340
									}]
								}, {
									labelWidth : 30,
									layout: 'form',
									autoHeight: true,
									border : false,
									items:
									[{
										ancor: '100%',
										fieldLabel: langs('дн.'),
										labelSeparator: '',
										hidden: true,
										xtype: 'textfield'
									},
									{
										ancor: '100%',
										fieldLabel: langs('дн.'),
										labelSeparator: '',
										hidden: true,
										xtype: 'textfield'
									},
									{
										ancor: '100%',
										fieldLabel: langs('дн.'),
										labelSeparator: '',
										hidden: true,
										xtype: 'textfield'
									}]
								}]
							},
							{
								layout: 'column',
								border: false,
								items: [{
                                    layout: 'form',
                                    autoHeight: true,
                                    border: false,
                                    items: [{
                                        fieldLabel: 'Выписка по решению ВК',
                                        hiddenName: 'EvnRecept_IsKEK',
                                        tabIndex: TABINDEX_ERREF + 14,
                                        width: 80,
                                        xtype: 'swyesnocombo',
                                        listeners: {
                                            'select': function(combo, newValue, oldValue) {
                                                combo.setVKProtocolFieldsVisible();
                                            }.createDelegate(this)
                                        },
                                        clearValue: function() {
                                            sw.Promed.SwYesNoCombo.superclass.clearValue.apply(this, arguments);
                                            this.setVKProtocolFieldsVisible();
                                        },
                                        setVKProtocolFieldsVisible: function() {
                                            var base_form = EvnReceptIncorrectSearchFilterForm.getForm();
                                            var vk_combo = base_form.findField('EvnRecept_IsKEK');
                                            var num_field = base_form.findField('EvnRecept_VKProtocolNum');
                                            var date_field = base_form.findField('EvnRecept_VKProtocolDT');
                                            var is_vk = (vk_combo.getValue() == 2);
                                            var is_visible = (getRegionNick() == 'msk' && is_vk);

                                            if (is_visible) {
                                                num_field.ownerCt.show();
                                                date_field.ownerCt.show();
                                            } else {
                                                num_field.ownerCt.hide();
                                                date_field.ownerCt.hide();
                                                num_field.setValue(null);
                                                date_field.setValue(null);
                                            }
                                        }
                                    }]
                                }, {
                                    layout: 'form',
                                    autoHeight: true,
                                    border: false,
                                    labelWidth: 150,
                                    items: [{
                                        fieldLabel: 'Номер протокола ВК',
                                        name: 'EvnRecept_VKProtocolNum',
                                        width: 80,
                                        xtype: 'textfield'
                                    }]
                                }, {
                                    layout: 'form',
                                    autoHeight: true,
                                    border: false,
                                    labelWidth: 150,
									width: 265,
                                    items: [{
                                        fieldLabel: 'Номер протокола ВК',
                                        name: 'EvnRecept_VKProtocolDT',
                                        xtype: 'swdatefield',
                                        format: 'd.m.Y',
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                        validateOnBlur: true
                                    }]
                                }]
							}
                            ]
						}, {
							labelWidth : 180,
							columnWidth: .5,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: 
							[{
								name : "EvnRecept_setDate",
								id : "ERIS_Recept_setDate",
								xtype : "daterangefield",
								width : 170,
								fieldLabel : "Выписка рецепта",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : 1346
							},
							{
								name : "EvnRecept_obrDate",
								id : "ERIS_Recept_obrDate",
								listeners: {
									'change': function(combo, newValue, oldValue) {
										/*При вводе в поле Диапазон дат обращения в аптеку любого значения и если нарушается условие 
										Результат ! = Не было обращения 
										langs('и') 
										Результат ! = Рецепт просрочен без обращения,
										то устанавливать Результат = Было обращение.*/
                                                                                if (newValue != '') {
											var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
											if ( ReceptResult == 2 || ReceptResult == 9 ) {
												EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(1);
											}
                                                                                }
									}
								},
								xtype : "daterangefield",
								width : 170,
								fieldLabel : "Обращение в аптеку",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : 1347
							},
							{
								name : "EvnRecept_otpDate",
								id : "ERIS_Recept_otpDate",
								listeners: {
									'change': function(combo, newValue, oldValue) {
										/*При вводе в поле Диапазон дат отоваривания рецепта любого значения и если нарушается условие 
										Результат = Рецепт отоварен
										langs('или') 
										Результат = Рецепт отоварен без отсрочки
										langs('или') 
										Результат = Рецепт отоварен после отсрочки,
										то устанавливать Результат = Рецепт отоварен.
										*/
										if (newValue != '') {
											var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
											if ( !(ReceptResult == 4 || ReceptResult == 7 || ReceptResult == 10) ) {
												EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
											}
										}
									}
								},
								xtype : "daterangefield",
								width : 170,
								fieldLabel : "Отоваривание рецепта",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : 1348
							},
							{
								allowBlank: true,
								name : "EvnRecept_otsDate",
								id : "ERIS_Recept_otsDate",
								format: 'd.m.Y',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										/*При вводе в поле Дата актуальности отсрочки любого значения и если нарушается условие 
										Результат =  Рецепт отоварен после отсрочки
										langs('или') 
										Результат =  Рецепт отсрочен
										langs('или') 
										Результат =  Рецепт просрочен после отсрочки,
										то устанавливать Результат = Рецепт отсрочен.*/
										if (newValue != '') {
											var ReceptResult = EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').getValue();
											if ( !(ReceptResult == 6 || ReceptResult == 7 || ReceptResult == 10) ) {
												EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptResult_id').setValue(7);
											}
										}
									}
								},					
								xtype : "swdatefield",
								width : 100,
								fieldLabel : "Актуальность отсрочки",
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex : 1349
							}, {
								autoLoad: false,
								comboSubject: 'ReceptFinance',
								fieldLabel: langs('Финансирование'),
								hiddenName: 'ReceptFinance_id',
								id: 'ERIS_ReceptFinanceCombo',
								lastQuery: '',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										Ext.getCmp('ERIS_DrugMnnCombo').getStore().baseParams.ReceptFinance_Code = newValue;
										Ext.getCmp('ERIS_DrugCombo').getStore().baseParams.ReceptFinance_Code = newValue;
									},
									'render': function(combo) {
										combo.getStore().load({
											params: {
												where: 'where ReceptFinance_Code in (1, 2)'
											}
										});
									}.createDelegate(this)
								},
								listWidth: 200,
								tabIndex: 1349,
								validateOnBlur: true,
								width: 170,
								xtype: 'swcommonsprcombo'

							}, 
							{ layout: 'form',
							    border: false,
							    hidden : !getGlobalOptions().region.nick.inlist(['perm','ufa']), // Задача #88371
							    items: [ 
								{
									comboSubject: 'YesNo',
									fieldLabel: langs('7 Нозологий'),
									hiddenName: 'EvnRecept_Is7Noz',
									lastQuery: '',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											Ext.getCmp('ERIS_DrugMnnCombo').getStore().baseParams.EvnRecept_Is7Noz = newValue;
											Ext.getCmp('ERIS_DrugCombo').getStore().baseParams.EvnRecept_Is7Noz = newValue;
										},
										'keydown': function (inp, e) {
											if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
												if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() > 0) {
													e.stopEvent();
													TabToGrid(EvnReceptIncorrectSearchViewGrid);
												}
											}
										}
									},
									tabIndex: 1350,
									validateOnBlur: true,
									width: 100,
									xtype: 'swcommonsprcombo'
							    }]
							},
							{
							    layout: 'form',
							    border: false,
							    labelAlign: 'right',
							    //labelWidth: 120,
							    hidden : getGlobalOptions().region.nick == 'perm', // Задача #88371
							    items: [{
								    xtype: 'swwhsdocumentcostitemtypecombo',
								    tabIndex: 1351,
								    fieldLabel: 'Статья расхода',
								    name: 'WhsDocumentCostItemType_id',
								    width: 170,
								    'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptIncorrectSearchViewGrid);
											}
										}
									}
								    //allowBlank: false
							    }]
							}
						    ]
						}]
					}]
				}, {
					title : "<u>6</u>. Пользователь",
					autoHeight: true,
					//height : 200,
					style: 'padding: 5px; margin-bottom: 5px',
					items: [{
						xtype: 'fieldset',
						autoHeight: true,
						title: langs('Изменение рецепта в базе данных'),
						style: 'padding: 5px; margin-bottom: 5px',
						items: [
							new sw.Promed.SwProMedUserCombo({
								id : 'ERIS_pmUser_updID',
								hiddenName : "pmUser_updID",
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											swReceptInCorrectSearchWindow.buttons[5].focus();
										}
									}
								},
								name : "pmUser_updID",
								width : 300,
								fieldLabel : langs('Пользователь'),
								tabIndex: 1338
							}),
							{
								name : "EvnRecept_updDT",
								xtype : "daterangefield",
								width : 170,
								fieldLabel : langs('Период'),
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: 1339
							}
						]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: langs('Добавление рецепта в базе данных'),
						style: 'padding: 5px; margin-bottom: 5px',
						items: [
							new sw.Promed.SwProMedUserCombo({
								id : 'ERIS_pmUser_insID',
								hiddenName : "pmUser_insID",
								name : "pmUser_insID",
								width : 300,
								fieldLabel : langs('Пользователь'),
								tabIndex: 1340
							}),
							{
								name : "EvnRecept_insDT",
								xtype : "daterangefield",
								width : 170,
								fieldLabel : langs('Период'),
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptIncorrectSearchViewGrid);
											}
										}
									}
								},
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: 1341
							}		
						]
					}]
				}, {
					title : "<u>7</u>. ЛПУ",
					border: false,
					frame: false,
					autoHeight: true,
					//height : 300,
					id: 'lpu_tab',
					labelWidth : 140,
					layout: 'form',
					style: 'padding: 5px; margin-bottom: 5px;',
					items : [{
						fieldLabel: langs('Территория'),
						hiddenName: 'SearchedOMSSprTerr_Code',
						hideEmptyRow: true,
						id: 'ERIS_SearchedOMSSprTerrCombo',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue) {
								var lpu_combo = Ext.getCmp('ERIS_SearchedLpuCombo');
								var obl_combo = Ext.getCmp('ERIS_LpuAreaCombo');
								var obl_val = obl_combo.getValue();
								lpu_combo.getStore().clearFilter();
								lpu_combo.clearValue();
								lpu_combo.getStore().filterBy(function(record) {
									if (
										( obl_val == '' || obl_val == record.get('Lpu_IsOblast') ) &&																		
										((record.get('Lpu_RegNomC2') == newValue) || (newValue == 1 && record.get('Lpu_RegNomC2') <= 7)) && 
										record.get('Lpu_DloBegDate') != '' && 
										(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
										(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) 
									)
										return true;
									if (
										( obl_val == '' || obl_val == record.get('Lpu_IsOblast') ) &&																		
										((newValue == 0) || (newValue == null) || (newValue == -1)) && 
										record.get('Lpu_DloBegDate') != '' && 
										(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
										(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y'))
									)
										return true;
								});															
							}
						},
						tabIndex: 1342,
						valueField: 'OMSSprTerr_Code',
						width : 300,
						xtype: 'swomssprterrsimplecombo'
					},
					new sw.Promed.SwBaseLocalCombo ({
						allowBlank: true,
						displayField: 'Lpu_IsOblast_Name',
						editable: true,
						fieldLabel: langs('Принадлежность ЛПУ'),
						hiddenName: 'Lpu_IsOblast_id',
						id: 'ERIS_LpuAreaCombo',
						listeners: {
							'change': function(combo, newValue) 
							{
								var lpu_combo = Ext.getCmp('ERIS_SearchedLpuCombo');
								var terr_combo = Ext.getCmp('ERIS_SearchedOMSSprTerrCombo');
								var terr_val = terr_combo.getValue();
								lpu_combo.getStore().clearFilter();
								lpu_combo.clearValue();
								lpu_combo.getStore().filterBy(function(record) {
									if ( 
									    ( newValue == '' || newValue == record.get('Lpu_IsOblast') ) &&
										((record.get('Lpu_RegNomC2') == terr_val) || (terr_val == 1 && record.get('Lpu_RegNomC2') <= 7)) && 
										record.get('Lpu_DloBegDate') != '' && 
										(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
										(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) 
									)
										return true;
									if (
										( newValue == '' || newValue == record.get('Lpu_IsOblast') ) &&									
										((terr_val == 0) || (terr_val == null) || (terr_val == -1)) && 
										record.get('Lpu_DloBegDate') != '' && 
										(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
										(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y'))
									)
										return true;
								});					
							}
						},
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Lpu_IsOblast_Name}&nbsp;',
							'</div></tpl>'
						),
						valueField: 'Lpu_IsOblast_id',
						store: new Ext.data.SimpleStore({
							autoLoad: false,
							fields: [
								{ name: 'Lpu_IsOblast_id', mapping: 'Lpu_IsOblast_id' },
								{ name: 'Lpu_IsOblast_Name', mapping: 'Lpu_IsOblast_Name' }								
							],
							key: 'Lpu_IsOblast_id',
							sortInfo: { field: 'Lpu_IsOblast_Name' }							
						}),
						tabIndex: 1343,
						width : 300
					}),
					{
						fieldLabel: langs('ЛПУ'),
						hiddenName: 'SearchedLpu_id',
						id: 'ERIS_SearchedLpuCombo',
						lastQuery: '',
						tabIndex: 1343,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{[(values.Lpu_EndDate != "" && Date.parseDate(values.Lpu_EndDate, "Y-m-d") < Date.parseDate(getGlobalOptions().date, "d.m.Y")) ? values.Lpu_Nick + " (закрыта " + Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate, "Y-m-d"), "d.m.Y") + ")" : values.Lpu_Nick ]}&nbsp;',
							'</div></tpl>'
						),
						width : 300,
						xtype: 'swlpucombo'
					}]
				}, {
					title : (getGlobalOptions().isMinZdrav)?"<u>8</u>. Экспертиза":"<u>7</u>. Экспертиза",
					border: false,
					frame: false,
					autoHeight: true,
					//height : 300,
					id: 'expertise_tab',
					labelWidth : 140,
					layout: 'form',
					style: 'padding: 5px; margin-bottom: 5px;',
					items : [{
						fieldLabel: langs('Прошел экспертизу'),
						hiddenName: 'ReceptStatusType_id',
						id: 'ERIS_ReceptStatusTypeCombo',
						tabIndex: 1344,
						width : 80,
						xtype: 'swyesnocombo',
						listeners: {
							'change': function(combo, nv, ov)
							{

								this.findById('ERIS_RegistryReceptErrorTypeCombo').disable();
								if (nv == 2) {
									this.findById('ERIS_RegistryReceptErrorTypeCombo').enable();
								}
							}.createDelegate(this)
						}
					}, {
						editable: false,
						enableKeyEvents: true,
						fieldLabel: langs('Результат экспертизы'),
						hiddenName: 'ReceptStatusFLKMEK_id',
						id: 'ERIS_ReceptStatusFLKMEKCombo',
						displayField: 'ReceptStatusFLKMEK_Name',
						mode:'local',
						store:new Ext.data.Store({
							autoLoad:false,
							reader:new Ext.data.JsonReader({
								id: 'ReceptStatusFLKMEK_id'
							}, [
								{name: 'ReceptStatusFLKMEK_id'},
								{name: 'ReceptStatusFLKMEK_Code'},
								{name: 'ReceptStatusFLKMEK_Name'}
							]),
							url:'/?c=SvodRegistry&m=loadReceptStatusFLKMEKList'
						}),
						tabIndex: 1345,
						triggerAction: 'all',
						width : 400,
						valueField: 'ReceptStatusFLKMEK_id',
						xtype: 'swcombo'
					}, {
						enableKeyEvents: true,
						fieldLabel: langs('Причина отказа'),
						hiddenName: 'RegistryReceptErrorType_id',
						id: 'ERIS_RegistryReceptErrorTypeCombo',
						displayField: 'RegistryReceptErrorType_Name',
						mode:'local',
						store:new Ext.data.Store({
							autoLoad:false,
							reader:new Ext.data.JsonReader({
								id: 'RegistryReceptErrorType_id'
							}, [
								{name: 'RegistryReceptErrorType_id'},
								{name: 'RegistryReceptErrorType_Code'},
								{name: 'RegistryReceptErrorType_Name'}
							]),
							url:'/?c=RegistryRecept&m=loadRegistryReceptErrorTypeList'
						}),
						tabIndex: 1346,
						triggerAction: 'all',
						width : 400,
						valueField: 'RegistryReceptErrorType_id',
						xtype: 'swcombo'
					}, {
						fieldLabel: langs('Передан на оплату'),
						hiddenName: 'AllowRegistryDataRecept',
						id: 'ERIS_AllowRegistryDataReceptCombo',
						tabIndex: 1347,
						width : 80,
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Принят на хранение'),
						hiddenName: 'RegistryDataRecept_IsReceived',
						id: 'ERIS_RegistryDataReceptIsReceivedCombo',
						tabIndex: 1348,
						width : 80,
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Оплачен'),
						hiddenName: 'RegistryDataRecept_IsPaid',
						id: 'ERIS_RegistryDataReceptIsPaidCombo',
						tabIndex: 1349,
						width : 80,
						xtype: 'swyesnocombo'
					}
					]
				}
				],
				listeners: {
					'tabchange': function(tab, panel) {
						this.EvnReceptIncorrectSearchViewGrid.doLayout();
						
						var els=panel.findByType('textfield', false);
						if (els=='undefined')
							els=panel.findByType('combo', false);
						var el=els[0];
						if (el!='undefined' && el.focus)
							el.focus(true, 200);
						this.syncSize();
					}.createDelegate(this)
				},
				xtype : "tabpanel",
				activeTab : 0,
				border : false,
				layoutOnTabChange: true
			}],
			keys: [{
				key: 13,
				fn: function() {
					SearchIncorrectRecept();
				},
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			layout: 'border',
			//height : 500,
			buttons : [{
				text : BTN_FRMSEARCH,
				iconCls: 'search16',
				handler: function() {
					SearchIncorrectRecept();
				}.createDelegate(this),
				onTabAction : function () {
					this.ownerCt.buttons[1].focus(false, 0);
				},
				onShiftTabAction : function () {
					if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() == 0) {
						var tab = EvnReceptIncorrectSearchFilterForm.findById('ERISTabPanel').getActiveTab();
						if(tab.title.indexOf('4') != -1){
							if(!EvnReceptIncorrectSearchFilterForm.findById('ERIS_EvnRecept_IsNotOstatForm').hidden){
								EvnReceptIncorrectSearchFilterForm.findById('ERIS_EvnRecept_IsNotOstatForm').focus(true, 0);
							} else {
								EvnReceptIncorrectSearchFilterForm.findById('ERIS_OrgFarmacy').focus(true, 0);
							}
						} else {
							Ext.getCmp('EvnReceptInCorrectSearchWindow').getLastFieldOnCurrentTab().focus(true);
						}
						return;
					}
					var selected_record = EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected();
					if (selected_record != -1) {
						var index = EvnReceptIncorrectSearchViewGrid.getStore().indexOf(selected_record);
					}
					else {
						var index = 0;
					}
					EvnReceptIncorrectSearchViewGrid.getView().focusRow(index);
    				EvnReceptIncorrectSearchViewGrid.getSelectionModel().selectRow(index);
				},
				tabIndex : 1391
				}, {
					text : BTN_FRMRESET,
					iconCls: 'resetsearch16',
					handler : function(button, event) {
						EvnReceptIncorrectSearchFilterForm.getForm().reset();
                        EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_IsKEK').setVKProtocolFieldsVisible();

						if (getGlobalOptions().region.nick != 'ufa') 
							EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_Is7Noz').setValue(1);
						else
							EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
                                                
						EvnReceptIncorrectSearchViewGrid.store.removeAll();
						EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_DeleteReceptBtn').disable();
						EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_EditReceptBtn').disable();
						EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_ViewReceptBtn').disable();
					}.createDelegate(this),
					tabIndex : 1392
				}, {
					text : BTN_FRMCOUNT,
					iconCls: 'search16',
					handler : function(button, event) {
						Ext.getCmp('EvnReceptInCorrectSearchWindow').getRecordsCount();
					}.createDelegate(this),
					tabIndex : 1393
				},{
					text : '-'
				},
				HelpButton(this),
				{
					text: BTN_FRMCLOSE,
					iconCls: 'cancel16',
					handler : function(button, event) {
						this.hide();
					}.createDelegate(this),
					onTabAction : function () {
						var tab = EvnReceptIncorrectSearchFilterForm.findById('ERISTabPanel').getActiveTab();
						if(tab.title.indexOf('1') != -1){
							EvnReceptIncorrectSearchFilterForm.findById('ERIS_Person_Surname').focus(true, 0);
						} else if(tab.title.indexOf('2') != -1){
							EvnReceptIncorrectSearchFilterForm.findById('ERIS_DocumentType_id').focus(true, 0);
						} else if(tab.title.indexOf('3') != -1){
							EvnReceptIncorrectSearchFilterForm.findById('ERIS_KLAreaStatCombo').focus(true, 0);
						} else if(tab.title.indexOf('4') != -1){
							EvnReceptIncorrectSearchFilterForm.findById('ERIS_EvnRecept_Ser').focus(true, 0);
						} else if(tab.title.indexOf('5') != -1){
							EvnReceptIncorrectSearchFilterForm.findById('ERIS_ReceptYes_id').focus(true, 0);
						} else if(tab.title.indexOf('6') != -1){
							EvnReceptIncorrectSearchFilterForm.findById('ERIS_pmUser_updID').focus(true, 0);
						}
					},
					onShiftTabAction : function () {
						this.ownerCt.buttons[2].focus(false, 0);
					},
					tabIndex : 1394
				}
			],
			items : [
				//this.EvnReceptIncorrectSearchFilterForm,
				{
					autoHeight: true,
					region: 'north',
					layout: 'fit',
					border: false,
					items: [this.EvnReceptIncorrectSearchFilterForm]
				},
				this.EvnReceptIncorrectSearchViewGrid
				/*, {
				id : "ERIS_BottomButtons",
				region : "south",
				height : 40,
				autoHeight: true
				}*/
			],
			keys: [{
				key: Ext.EventObject.INSERT,
				fn: function(e) {
					EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_NewReceptBtn').handler();
				},
				stopEvent: true
			}, {
				key: "123456789",
				alt: true,
				fn: function(e) {
					Ext.getCmp("ERISTabPanel").setActiveTab(Ext.getCmp("ERISTabPanel").items.items[ e - 49 ]);
				},
				stopEvent: true
			}, {
				key: Ext.EventObject.F5,
				fn: function(e) {
					// тупо чтобы по F5 не перезагружалась страница
					return false;
				},
				stopEvent: true
			},	{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;
					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J)
					{
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C)
					{
						EvnReceptIncorrectSearchFilterForm.getForm().reset();
                        EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_IsKEK').setVKProtocolFieldsVisible();
						
						if (getGlobalOptions().region.nick != 'ufa')
							EvnReceptIncorrectSearchFilterForm.getForm().findField('EvnRecept_Is7Noz').setValue(1);
						else
							EvnReceptIncorrectSearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
                        
						EvnReceptIncorrectSearchViewGrid.store.removeAll();
						EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_DeleteReceptBtn').disable();
						EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_EditReceptBtn').disable();
						EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_ViewReceptBtn').disable();
						return false;
					}

					if (e.getKey() == Ext.EventObject.G)
					{
						SearchIncorrectRecept();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C, Ext.EventObject.G ],
				scope: this,
				stopEvent: false
			}],
			title : langs('Журнал отсрочки')
			//,width : 670
		});
		EvnReceptIncorrectSearchFilterForm = this.EvnReceptIncorrectSearchFilterForm;
		EvnReceptIncorrectSearchViewGrid = this.EvnReceptIncorrectSearchViewGrid;
		sw.Promed.swReceptInCorrectSearchWindow.superclass.initComponent.apply(this, arguments);
        this.closeActions = false;
        if(arguments[0])
        {
            if(arguments[0].onlyView){
                this.closeActions = true;
                EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_DeleteReceptBtn').disable();
                EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_EditReceptBtn').disable();
                EvnReceptIncorrectSearchViewGrid.getTopToolbar().items.item('ERIS_NewReceptBtn').disable();
            }
        }
		Ext.getCmp('ERIS_DrugCombo').getStore().baseParams.searchFull = 1;
		Ext.getCmp('ERIS_DrugMnnCombo').getStore().baseParams.searchFull = 1;
	}
});