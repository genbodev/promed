/**
 * swRegistryEditWindowVE - форма постановка группы реестров в очередь на формирование для предварительных реестров
 * 
 * @copyright    Copyright (c) 2017 Emsis Ltd.
 * @author       Robert SS
 * @mail         borisworking@gmail.com
 * @version      2.0 май 2017
 *
 * Константы в ru.js
 */
sw.Promed.swRegistryEditWindowVE = Ext.extend(sw.Promed.BaseForm, {
    renderTo      : Ext.getBody(),
    title         : 'Постановка группы реестров в очередь на формирование',
    modal         : true,
    plain         : true, 
    layout        : '',
    region        : 'center',
    resizable     : false,
    height        : 530,
    width         : 720, 
    closable      : true,
	closeAction   : 'hide',
	draggable     : true,
	split         : true,
    codeRefresh   : true,
	firstTabIndex : 15102,
    waitAnswers   : 0,
    id            : 'RegistryEditWindowVE',
    objectName    : 'swRegistryEditWindowVE',
    objectSrc     : '/jscore/Forms/Admin/Ufa/swRegistryEditWindowVE.js',
    errMessageVE    : function(c,t){
        Ext.MessageBox.show({
            title    : TITLE_BOX_WARNING,
            msg      : t + c,
            width    : 400,
            height   : 50,
            closable :false,
            buttons  : Ext.MessageBox.OK,
            icon     : Ext.MessageBox.WARNING
        });
    },
    show : function(){
		this.Registry_IsNew = null;
		if (arguments[0].Registry_IsNew) {
			this.Registry_IsNew = arguments[0].Registry_IsNew;
		}

        Ext.MessageBox.getDialog().getEl().setStyle('z-index','80000');
        /*if (document.getElementById("RegistryEditWindowVE_SMO") != null) { //Удаляем форму, если она есть (устраняем глюк с появлением окна)
                document.getElementById("RegistryEditWindowVE_SMO").remove();
        }   */       
        sw.Promed.swRegistryEditWindowVE.superclass.show.apply(this, arguments);

        Ext.getCmp('SMOGrid').getGrid().getStore().load({
            params:{lpu_id : getGlobalOptions().lpu_id},
        });

        Ext.getCmp('INGrid').getGrid().getStore().load({
            params:{lpu_id : getGlobalOptions().lpu_id},
        });

        //Получение списка СМО и сортировка их по простым и инотерриальным
        Ext.Ajax.request({
            url: '/?c=Demand_VE&m=getOrgSmoUfaList',
            success: function(result,  options){
                var jData = Ext.util.JSON.decode(result.responseText); 
                //console.log('jData',jData); 
                var SMO = [];
                var INNOTER =[];
                for (var k in jData) {
                    if (typeof(jData[k]) == 'object') {
                        if (/Инотерр/.test(jData[k].Smo_Name)) {
                            INNOTER.push(jData[k]);
                        }
                        else {
                            SMO.push(jData[k]);
                        }
                    }
                    
                }
                Ext.getCmp('RegistryEditWindowVE').SMO_ID = {
                    SMO: SMO,
                    INNOTER: INNOTER
                };
            },
        });
    },
    //Формирование панелей для списков реестров, поставленных в очередь на формирование
	request: function (JsonString, RESWIN_str) {
		var win = this;
		var add_all_mask = new Ext.LoadMask(Ext.getBody(), {msg: TEXT_MASK_WAIT_QUEUE});
		add_all_mask.show();
		Ext.Ajax.request({
			url: '/?c=Demand_VE&m=PrepareData',
			success: function (result, options) {
				var jData = Ext.util.JSON.decode(result.responseText);
				var cmIN = new Ext.grid.ColumnModel(
					[
						{
							header: BUILDINGS,
							width: 100,
							DataIndex: 'UnitSet_id',
							style: {'font-weight': 'bold'},
							align: 'center'
						},
						{
							header: REGISTRY_BEGDATE,
							width: 150,
							dataIndex: 'Registry_begDate',
							style: {'font-weight': 'bold'},
							align: 'center'
						},
						{
							header: REGISTRY_ENDDATE,
							width: 150,
							dataIndex: 'Registry_endDate',
							style: {'font-weight': 'bold'},
							align: 'center'
						},
						{
							header: IN_QUEUE,
							width: 70,
							dataIndex: 'RegistryQueue_Position',
							style: {'font-weight': 'bold', 'text-align': 'center'},
							align: 'center'
						},
						{
							header: STATUS,
							flex: 1,
							dataIndex: 'Error_code',
							style: {'font-weight': 'bold'},
							align: 'center',
							renderer: function (value, metaData, record, rowIndex, colIndex, store) {
								if (record.get('Error_Message') == "" && record.get('UnitSet_id') != "") {
									return value = "<span style='color:green; font-weight:bold'>OK</span>";
								} else {
									return value = "<span style='color:red; white-space:normal;'>" + record.get('Error_Message') + "</span>";
								}
							}
						}
					]
				);
				var jstring = jData;
				var reader = new Ext.data.JsonReader({
					fields: [
						{name: 'UnitSet_id'},
						{name: 'Registry_begDate'},
						{name: 'Registry_endDate'},
						{name: 'RegistryQueue_Position'},
						{name: 'Error_code'},
						{name: 'Error_Message'}
					]
				});

				var resSTORE = new Ext.data.Store({
					reader: reader,
					data: jstring,
					listeners: {
						load: function () {
							//add_all_mask.hide();
							this.remove(this.data.items[this.data.items.length - 1]);
						}
					}
				});

				var ResGrid = new Ext.grid.GridPanel({
					//region   : 'center',
					title: RESWIN_str,
					//autoHeight : true,
					store: resSTORE,
					height: 210,
					cm: cmIN,
					border: 1
				});
				Ext.getCmp('RegistryEditWindowVE').waitAnswers++;
				Ext.getCmp('ResWin_pre').add(ResGrid);
				if (Ext.getCmp('RegistryEditWindowVE').countAnswers == Ext.getCmp('RegistryEditWindowVE').waitAnswers) {
					add_all_mask.hide();
				}
				;
				Ext.getCmp('ResWin_pre').doLayout();
				//Удаление выбранного, т.к. при повторной загрузке галочки выводятся вновь и долго не снимаются
				Ext.getCmp('SMOGrid').getGrid().getSelectionModel().clearSelections();
				Ext.getCmp('INGrid').getGrid().getSelectionModel().clearSelections();
			},
			failure: function () {
				//add_all_mask.hide();
			},
			params: {
				data: JsonString,
				Registry_IsNew: win.Registry_IsNew
			}
		});
		return true;
	},
    combineDataSMO : function(){
        //Основные данные по ЛПУ
        var global = {
           lpu_id : getGlobalOptions().lpu_id,
           RegistryType_id:  getGlobalRegistryData.RegistryType_id,
           RegistrySubType: getGlobalRegistryData.RegistrySubType_id,
           RegistryStatus_id:  getGlobalRegistryData.RegistryStatus_id,
           lpu_name: getGlobalOptions().lpu_name,
           lpu_regnomc: getGlobalOptions().lpu_regnomc,
           medpersonal_id: getGlobalOptions().medpersonal_id,
           orgtype: getGlobalOptions().orgtype,
           pmuser_id: getGlobalOptions().pmuser_id,
           Curr_date: getGlobalOptions().date
        };
        //Определить отмеченные подразделения по инотерам
        var INGrid = Ext.getCmp('INGrid').getGrid();
        var smIN = INGrid.getSelectionModel();
        var selIN = smIN.selections;
        var checkedBuildingsVE = [];
        //Определить отмеченные подразделения по СМО
        var SMOGrid = Ext.getCmp('SMOGrid').getGrid();
        var sm = SMOGrid.getSelectionModel();
        var sel = sm.selections;
        var checkedVE = [];
        var thisWin = Ext.getCmp('RegistryEditWindowVE');
        //Проверить правильность заполнения полей в гридах

        Ext.each(sel.items, function (item, index) {
            var lineGridData = sel.items[index].data;
            if(!lineGridData.datestart || !lineGridData.dateend){
                thisWin.errMessageVE(lineGridData.name, EMPTY_DATE_PERIOD); return false;
            }    
            else if(lineGridData.datestart > lineGridData.dateend){
                thisWin.errMessageVE(lineGridData.name, INVALID_INTERVAL_PERIOD); return false;
            }                                                                   
            else if(!lineGridData.number){
                thisWin.errMessageVE(lineGridData.name, EMPTY_NUMBER_MESSAGE); return false;
            } 
            else{ 
                lineGridData.datestart = typeof(lineGridData.datestart)  != 'string'  ? lineGridData.datestart.dateFormat('d.m.Y') : lineGridData.datestart;
                lineGridData.dateend   = typeof(lineGridData.dateend)    != 'string'  ? lineGridData.dateend.dateFormat('d.m.Y')   : lineGridData.dateend;
                checkedVE[lineGridData.id]  = lineGridData; 
            }
        }); 
        Ext.each(selIN.items, function (item, index) {
            var lineGridData = selIN.items[index].data;
            if(!lineGridData.datestart || !lineGridData.dateend){
                thisWin.errMessageVE(lineGridData.name, EMPTY_DATE_PERIOD); return false;
            }    
            else if(lineGridData.datestart > lineGridData.dateend){
                thisWin.errMessageVE(lineGridData.name, INVALID_INTERVAL_PERIOD); return false;
            }                                                                   
            else if(!lineGridData.number){
                thisWin.errMessageVE(lineGridData.name, EMPTY_NUMBER_MESSAGE); return false;
            } 
            else{ 
                lineGridData.datestart = typeof(lineGridData.datestart)  != 'string'  ? lineGridData.datestart.dateFormat('d.m.Y') : lineGridData.datestart;
                lineGridData.dateend   = typeof(lineGridData.dateend)    != 'string'  ? lineGridData.dateend.dateFormat('d.m.Y')   : lineGridData.dateend;
                checkedBuildingsVE[lineGridData.id]  = lineGridData; 
            }

        });

        //Проверка кол-ва выбранных подразделений
        if(checkedBuildingsVE.length == 0 && checkedVE.length == 0) {
            this.errMessageVE('', NOT_SELECTED_BUILDINGS); 
        } else {
            Ext.getCmp('RegistryEditWindowVE').waitAnswers = 0;
            var winheight;
            if (checkedBuildingsVE.length == 0 || checkedVE.length == 0) {
                winheight = 300;
                this.countAnswers = 1;            
            }
            else {
                winheight = 500;
                this.countAnswers = 2;
            }
            //Если проверка пройдена...
            //Подготовка объектов для отпавки на сервер     
            //console.log('ListSMO: ' + Object.keys(checkedVE).length);
            //console.log('ListBuildings: ' + Object.keys(checkedBuildingsVE).length);
            //--Если не выбраны СМО и выбрано хоть одно подразделение
            //--Значит необходимо собрать все данные по всем СМО выбранных подразделений

            //Результирующее окно после отправки на сервер
            var thisWin = Ext.getCmp('RegistryEditWindowVE');
            thisWin.hide();
            var ResWin_pre = new Ext.Window({
                title     : RESWIN_TITLE,
                bodyStyle : 'padding:5px;',
                modal     : true,
                resizable : true,                                            
                width     : 598,
                minHeight : 400,
                height    : winheight,
                id: 'ResWin_pre',
                onEsc     : function(){
                    ResWin_pre.close();
                },
                items     : [],
                renderTo  : Ext.getBody(),
                buttonAlign : 'left',
                buttons   : [{
                    iconCls: 'close16',
                    text: TEXT_BUTTON_CLOSE_RESWIN,
                    handler : function(){
                        ResWin_pre.close();
                    }
                }],  
            });                    
            if(Object.keys(checkedVE).length > 0){
                var resultSMO = {
                    global : global,
                    period_inoter : {
                        begDate: null,
                        endDate: null
                    },
                    LpuUnitSet: checkedVE,
                    Smo: 'null',//Ext.getCmp('RegistryEditWindowVE').SMO_ID.SMO
                };
                var JsonStringSMO = Ext.util.JSON.encode(resultSMO); 
                var panelSMO = thisWin.request(JsonStringSMO,'Реестры по СМО РБ');
            }   
            if (Object.keys(checkedBuildingsVE).length > 0){
                var resultINO = {
                    global : global,
                    period_inoter : {
                       begDate: null,
                       endDate: null                                           
                    },
                    LpuUnitSet : checkedBuildingsVE,
                    Smo : Ext.getCmp('RegistryEditWindowVE').SMO_ID.INNOTER
                };
                var JsonStringINO = Ext.util.JSON.encode(resultINO); 
                var panelINO = thisWin.request(JsonStringINO,'Реестры по инотерриальным'); 
            }
            ResWin_pre.show(); 
        }
    },
    onLoadData : function(){

    },
    initComponent : function(){

        var win = this;
        //Форматирование даты
        function formatDate(value){
            return value ? new Date(value).dateFormat('d.m.Y') : '';
        };

        Ext.apply(this, { 
            items : [
                {
                    xtype       :'fieldset',
                    style       : {margin : '10px'},
                    layout      : 'border',
                    region      : 'north',
                    height      : 210,
                    title       : 'Реестры по СМО РБ',
                    collapsible : true,
                    items       : [ 
                        new sw.Promed.ViewFrame({
                            noSelectFirstRowOnFocus: true,
                            useEmptyRecord: false,
                            showCountInTop: false,
                            checkBoxWidth: 25,
                            border: true,
                            selectionModel: 'multiselect',
                            multi: true,
                            singleSelect: false,
                            stateful: true,
                            layout: 'fit',
                            groups:false,
                            autoLoadData: false,
                            object: 'LpuStructure',
                            dataUrl: '/?c=LpuStructure&m=getLpuUnitSetCombo',
                            autoExpandColumn: 'autoexpand',
                            saveAtOnce: false,
                            saveAllParams: false,
                            clicksToEdit: 1,
                            id: 'SMOGrid',
                            stringfields:[
                                {
                                    name         : 'processing', 
                                    hidden       : true
                                },{
                                    name         : 'id', 
                                    mapping      : 'LpuUnitSet_id', 
                                    hidden       : true
                                },{
                                    name         : 'name', 
                                    mapping      : 'LpuUnitSet_Code', 
                                    header       : G1_CODE_BUILDS, 
                                    width        : 155, 
                                    dataIndex    : 'name',
                                    style        : {'font-weight':'bold'},
                                },{
                                    name         : 'datestart', 
                                    dataIndex    : 'datestart',
                                    mapping      : 'curBegDateMonth',
                                    type         : 'date',
                                    dateFormat   : 'd.m.Y', 
                                    //defaultValue : PER_START_DATE,
                                    header       : G1_START_PER, 
                                    width        : 140,
                                    editor       : new Ext.form.DateField({
                                        format  : 'd.m.Y',
                                        plugins : [ new Ext.ux.InputTextMask('99.99.9999', false) ]  
                                    }),
                                },{
                                    name         : 'dateend',
                                    dataIndex    : 'dateend',
                                    mapping      : 'curEndDateMonth',
                                    type         : 'date',
                                    dateFormat   : 'd.m.Y',
                                    //defaultValue : PER_END_DATE,
                                    header       : G1_END_PER, 
                                    width        : 140,
                                    editor       : new Ext.form.DateField({
                                        format  : 'd.m.Y',
                                        plugins : [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                                    }),
                                },{
                                    name         : 'number', 
                                    mapping      : 'LpuUnitSet_Code', 
                                    header       : G1_NUMBER,
                                    width        : 200,
                                    style        : {'font-weight' : 'bold'},
                                    dataIndex    : 'number',
                                    editor       : new Ext.form.TextField({
                                        width      : 180,
                                        allowBlank : false
                                    })
                                },{
                                    name         : 'mark',
                                    hidden       : true
                                },
                            ],
                            toolbar: false,
                            clicksToEdit:1,
                            isAllowSelectionChange: function(g, rowIndex, e) {
                                var el = new Ext.Element(e.getTarget()),
                                    cellEl = el && el.findParent('td[class*=x-grid3-cell]', 7, true);

                                if (cellEl) {
                                    return (!cellEl.hasClass('x-grid3-td-4') && !cellEl.hasClass('x-grid3-td-5') && !cellEl.hasClass('x-grid3-td-6'));
                                }
                                return true;
                            },
                        }) 
                    ]
                },				  
                {
                    xtype       :'fieldset',
                    layout      : 'border',
                    collapsible : true,
                    autoWidth   : true,
                    height      : 220,
                    style       : {margin : '10px'},
                    title       : 'Реестры по инотерриториальным',
                    region      : 'north', 
                    scroll      : false,       
                    items       : [ 
                        new sw.Promed.ViewFrame({
                            height : 200,
                            noSelectFirstRowOnFocus: true,
                            useEmptyRecord: false,
                            showCountInTop: false,
                            checkBoxWidth: 25,
                            border: true,
                            selectionModel: 'multiselect',
                            multi: true,
                            singleSelect: false,
                            stateful: true,
                            layout: 'fit',
                            groups:false,
                            autoLoadData: false,
                            object: 'LpuStructure',
                            dataUrl: '/?c=LpuStructure&m=getLpuUnitSetCombo',
                            autoExpandColumn: 'autoexpand',
                            saveAtOnce: false,
                            saveAllParams: false,
                            clicksToEdit: 1,
                            id: 'INGrid',
                            stringfields:[
                                {
                                    name         : 'id',
                                    mapping      : 'LpuUnitSet_id',
                                    hidden       : true
                                },{
                                    name         : 'name',
                                    mapping      : 'LpuUnitSet_Code',
                                    header       : G1_CODE_BUILDS,
                                    width        : 155,
                                    dataIndex    : 'name',
                                    style        : {'font-weight':'bold'}
                                },{
                                    name         : 'datestart', 
                                    dataIndex    : 'datestart',
                                    mapping      : 'curBegDateMonth',
                                    header       : G1_START_PER, 
                                    type         : 'date',
                                    dateFormat   : 'd.m.Y', 
                                    //defaultValue : PER_START_DATE,  
                                    width        : 140,
                                    editor       : new Ext.form.DateField({
                                        format  : 'd.m.Y',
                                        plugins : [ new Ext.ux.InputTextMask('99.99.9999', false) ] 
                                    }),
                                  renderer: Ext.util.Format.dateRenderer('d.m.Y')
                                },{
                                    name         : 'dateend', 
                                    dataIndex    : 'dateend',
                                    mapping      : 'curEndDateMonth',
                                    header       : G1_END_PER,
                                    type         : 'date',
                                    dateFormat   : 'd.m.Y', 
                                    //defaultValue : PER_END_DATE,
                                    width        : 140,
                                    editor       : new Ext.form.DateField({
                                        format  : 'd.m.Y',
                                        plugins : [ new Ext.ux.InputTextMask('99.99.9999', false) ]  
                                    }),
                                },{
                                    name         : 'number', 
                                    dataIndex    : 'number',
                                    mapping      : 'LpuUnitSet_Code',
                                    header       : G1_NUMBER, 
                                    width        : 200,
                                    style        : {'font-weight' : 'bold'},
                                    editor: new Ext.form.TextField({
                                        width      : 180,
                                        allowBlank : false                  
                                    })
                                }
                            ],
                            toolbar: false,
                            clicksToEdit:1,
                            isAllowSelectionChange: function(g, rowIndex, e) {
                                var el = new Ext.Element(e.getTarget()),
                                    cellEl = el && el.findParent('td[class*=x-grid3-cell]', 7, true);

                                if (cellEl) {
                                    return (!cellEl.hasClass('x-grid3-td-3') && !cellEl.hasClass('x-grid3-td-4') && !cellEl.hasClass('x-grid3-td-5'));
                                }
                                return true;
                            },
                        })
                    ]          
                }
            ],
            buttons: [
                {
                    handler: function(){ 
                        this.ownerCt.combineDataSMO();
                    },
                    text       : B_FORM_SAVE, 
                    autoHeight : true,
                    bodyStyle  : 'padding: 5px',
                    border     : false,
                    buttonAlign: 'left',
                    frame      : true,
                    id         : 'RegistryEditForm_VE',
                    labelAlign : 'left',
                    labelWidth : 150,
                    iconCls    : 'save16',
                    border     : 1,
                    style: {
                        'float':'left',
                        borderColor: 'gray',
                        borderStyle: 'solid'
                    }
                },
                {
                    handler: function(){
                        ShowHelp(Ext.getCmp('RegistryEditWindowVE').title);
                    },
                    text:'Справка', 
                    iconCls: 'help16',
                    frame      : true ,
                    style : {
                        marginLeft: '448px'
                    }
                },
                {
                    handler: function() { 
                        this.ownerCt.hide();
                    },
                    text       : B_FORM_CANCEL, 
                    autoHeight : true,
                    bodyStyle  : 'padding: 5px',
                    border     : false,
                    buttonAlign: 'right',
                    frame      : true,
                    id         : 'RegistryCancelForm_VE',
                    labelAlign : 'right',
                    labelWidth : 150,
                    iconCls: 'cancel16',
                    border : 1,
                    style: {
                        borderColor: 'gray',
                        borderStyle: 'solid'
                    }
                }
            ]       
       });
       sw.Promed.swRegistryEditWindowVE.superclass.initComponent.apply(this, arguments);           
    }        
});
    
