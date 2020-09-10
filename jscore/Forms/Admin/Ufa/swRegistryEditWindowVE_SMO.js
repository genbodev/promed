/**
 * swRegistryEditWindowVE - форма постановка группы реестров в очередь на формирование для реестров по СМО
 * 
 * @copyright    Copyright (c) 2017 Emsis Ltd.
 * @author       Robert SS
 * @mail         borisworking@gmail.com
 * @version      2.0 май 2017
 *
 * Константы в ru.js
 */

sw.Promed.swRegistryEditWindowVE_SMO = Ext.extend(sw.Promed.BaseForm, {
    renderTo      : Ext.getBody(),
    title         : 'Постановка группы реестров в очередь на формирование',
    modal         : true,
    plain         : true, 
    layout        : '',
    region        : 'center',
    resizable     : false,
    height        : 600,
    width         : 720, 
    closable      : true,
	closeAction   : 'hide',
	draggable     : true,
	split         : true,
    codeRefresh   : true,
	firstTabIndex : 15102,
    id            : 'RegistryEditWindowVE_SMO',
    objectName    : 'swRegistryEditWindowVE_SMO',
    objectSrc     : '/jscore/Forms/Admin/Ufa/swRegistryEditWindowVE_SMO.js',
    errMessage_SMO : function(c,t){
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
    show: function(){
        this.Registry_IsNew = null;
		if (arguments[0].Registry_IsNew) {
			this.Registry_IsNew = arguments[0].Registry_IsNew;
		}

        Ext.MessageBox.getDialog().getEl().setStyle('z-index','80000');
        /*if (document.getElementById("RegistryEditWindowVE") != null) { //Удаляем форму, если она есть (устраняем глюк с появлением окна)
                //console.log('удаляем');
                document.getElementById("RegistryEditWindowVE").remove();
        }    */    
        sw.Promed.swRegistryEditWindowVE_SMO.superclass.show.apply(this, arguments);

		Ext.getCmp('rewvesmo_Registry_IsZNO').setContainerVisible(getGlobalRegistryData.RegistryType_id.toString().inlist(['1', '2', '6']));
		Ext.getCmp('rewvesmo_Registry_IsZNO').reset();

        Ext.getCmp('BGGrid').getGrid().getStore().load({
            params:{lpu_id : getGlobalOptions().lpu_id},
        });

        Ext.getCmp('SMOGrid_fSMO').getGrid().getStore().load({
            params:{Org_id: getGlobalOptions().org_id}
        });
    },
    onLoadData: function(){

    },
	combineData: function () {
		var win = this;

		var startPeriodInoter = Ext.getCmp('startPeriodInoter').value;
		var endPeriodInoter = Ext.getCmp('endPeriodInoter').value;

		//Основные данные по ЛПУ
		var global = {
			lpu_id: getGlobalOptions().lpu_id,
			RegistryType_id: getGlobalRegistryData.RegistryType_id,
			RegistrySubType: getGlobalRegistryData.RegistrySubType_id,
			RegistryStatus_id: getGlobalRegistryData.RegistryStatus_id,
			PayType_SysNick: getGlobalRegistryData.PayType_SysNick,
			Registry_IsNotInsur: 1,
			Registry_IsZNO: Ext.getCmp('rewvesmo_Registry_IsZNO').checked ? 2 : 1,
			Registry_Comment: null,
			lpu_name: getGlobalOptions().lpu_name,
			lpu_regnomc: getGlobalOptions().lpu_regnomc,
			medpersonal_id: getGlobalOptions().medpersonal_id,
			orgtype: getGlobalOptions().orgtype,
			pmuser_id: getGlobalOptions().pmuser_id,
			Curr_date: getGlobalOptions().date
		};
		//Период для инотеров
		var period_inoter = {
			begDate: startPeriodInoter,
			endDate: endPeriodInoter
		};
		//Определить отмеченные подразделения
		var BGGrid = Ext.getCmp('BGGrid').getGrid();
		var smBG = BGGrid.getSelectionModel();
		var selBG = smBG.selections;
		var checkedBuildings = [];
		//Определить отмеченные СМО
		var SMOGrid_fSMO = Ext.getCmp('SMOGrid_fSMO').getGrid();
		var smSMO = SMOGrid_fSMO.getSelectionModel();
		var selSMO = smSMO.selections;
		var checkedSMO = [];

		Ext.each(selSMO.items, function (item, index) {

			//Если отмечено СМО для инотеров
			if (item.data.id == ID_INOTERS) {
				//Проверка дат для реестра по иннотерам
				if (typeof startPeriodInoter == 'undefined' || typeof endPeriodInoter == 'undefined') {
					this.errMessage_SMO('', EMPTY_DATE_PERIOD_INOTER);
					return false;
				}
				//Если начало срока больше или совпадает с концом срока
				else if ((startPeriodInoter >= endPeriodInoter) === true) {
					this.errMessage_SMO('', INVALID_INTERVAL_PERIOD_INOTER);
					return false;
				}
			}
			checkedSMO[item.data.id] = item.json;
		});
		Ext.each(selBG.items, function (item, index) {
			var lineGridData = selBG.items[index].data;
			if (!lineGridData.datestart || !lineGridData.dateend) {
				this.errMessage_SMO(lineGridData.name, EMPTY_DATE_PERIOD);
				return false;
			}
			else if (lineGridData.datestart > lineGridData.dateend) {
				this.errMessage_SMO(lineGridData.name, INVALID_INTERVAL_PERIOD);
				return false;
			}
			else if (!lineGridData.number) {
				this.errMessage_SMO(lineGridData.name, EMPTY_NUMBER_MESSAGE);
				return false;
			}
			else {
				lineGridData.datestart = typeof(lineGridData.datestart) != 'string' ? lineGridData.datestart.dateFormat('d.m.Y') : lineGridData.datestart;

				lineGridData.dateend = typeof(lineGridData.dateend) != 'string' ? lineGridData.dateend.dateFormat('d.m.Y') : lineGridData.dateend;

				checkedBuildings[lineGridData.id] = lineGridData;
			}
		});
		//Проверка кол-ва выбранных подразделений
		if (checkedBuildings.length == 0 || checkedSMO.length == 0) {
			this.errMessage_SMO('', 'Должна быть выбрана хотя бы одна СМО и одно подразделение');
		}

		//Подготовка объектов для отпавки на сервер
		//console.log('ListSMO: ' + Object.keys(checkedSMO).length);
		//console.log('ListBuildings: ' + Object.keys(checkedBuildings).length);
		//--Если не выбраны СМО и выбрано хоть одно подразделение
		//--Значит необходимо собрать все данные по всем СМО выбранных подразделений
		//--В том числе и инторев (УФОМС)
		if (Object.keys(checkedBuildings).length > 0 && Object.keys(checkedSMO).length > 0) {

			var result_fSMO = {
				global: global,
				period_inoter: period_inoter,
				LpuUnitSet: checkedBuildings,
				Smo: checkedSMO
			}

			var JsonString = Ext.util.JSON.encode(result_fSMO);
			var thisWin = Ext.getCmp('RegistryEditWindowVE_SMO');
			thisWin.hide();
			var add_all_mask = new Ext.LoadMask(Ext.getBody(), {msg: TEXT_MASK_WAIT_QUEUE});
			add_all_mask.show();


			Ext.Ajax.request({
				url: '/?c=Demand_VE&m=PrepareDataSMO',
				success: function (result_fSMO, options) {
					var jData = Ext.util.JSON.decode(result_fSMO.responseText);
					//--Результирующий грид
					var cmRG = new Ext.grid.ColumnModel(
						[
							{
								header: 'Registry_id',
								width: 70,
								DataIndex: 'Registry_id',
								style: {'font-weight': 'bold'},
								align: 'center'
							},
							{
								header: 'Номер счета',
								width: 80,
								DataIndex: 'Registry_Num',
								style: {'font-weight': 'bold'},
								align: 'center'
							},
							{
								header: BUILDINGS,
								width: 100,
								DataIndex: 'UnitSet_id',
								style: {'font-weight': 'bold'},
								align: 'center'
							},
							{header: NAME_SMO, width: 420, dataIndex: 'Smo_Name', style: {'font-weight': 'bold'}},
							{
								header: STATUS,
								flex: 1,
								dataIndex: 'Error_code',
								style: {'font-weight': 'bold'},
								align: 'center',
								renderer: function (value, metaData, record, rowIndex, colIndex, store) {
									if (record.get('Error_Message') == "" && record.get('UnitSet_id') != "") {
										return value = "<span style='color:green; font-weight:bold'>OK</span>";
									}
									else {
										return value = "<span style='color:red; white-space:normal;'>" + record.get('Error_Message') + "</span>";
									}
								}
							}
						]
					);

					var jstring = jData;
					var reader = new Ext.data.JsonReader({
						fields: [
							{name: 'Registry_id'},
							{name: 'UnitSet_id'},
							{name: 'Registry_Num'},
							{name: 'Smo_Name'},
							{name: 'Error_code'},
							{name: 'Error_Message'}
						]
					});

					var resSTORE = new Ext.data.Store({
						reader: reader,
						data: jstring,
						listeners: {
							load: function () {
								add_all_mask.hide();
								this.remove(this.data.items[this.data.items.length - 1]);
							}
						}
					});

					var ResGrid = new Ext.grid.GridPanel({
						id: 'ResGrid',
						region: 'center',
						store: resSTORE,
						cm: cmRG,
						border: 1
					});

					var ResWin = new Ext.Window({
						title: RESWIN_TITLE,
						bodyStyle: 'padding:5px;',
						layout: 'border',
						modal: true,
						resizable: true,
						width: 800,
						minHeight: 350,
						height: 350,
						onEsc: function () {
							ResWin.close();
						},
						items: [ResGrid],
						renderTo: Ext.getBody(),
						buttonAlign: 'left',
						buttons: [{
							iconCls: 'close16',
							text: TEXT_BUTTON_CLOSE_RESWIN,
							style: {
								marginLeft: '-8px'
							},
							handler: function () {
								ResWin.close();
							}
						}]
					});

					ResWin.show();
					//Обновление таблицы с реестрами по СМО
					Ext.getCmp('RegistryViewWindow').UnionRegistryGrid.getGrid().getStore().load();
					//Удаление выбранного, т.к. при повторной загрузке галочки выводятся вновь и долго не снимаются
					Ext.getCmp('BGGrid').getGrid().getSelectionModel().clearSelections();
					Ext.getCmp('SMOGrid_fSMO').getGrid().getSelectionModel().clearSelections();
				},
				failure: function () {
					//add_all_mask.hide();
				},
				params: {
					data: JsonString,
					Registry_IsNew: win.Registry_IsNew
				}
			});
		}
	},
    initComponent : function(){
        //Форматирование даты
        function formatDate(value){
            return value ? new Date(value).dateFormat('d.m.Y') : '';
            /*
            var d = new Date(2011, 01, 07); // yyyy, mm-1, dd  
var d = new Date(2011, 01, 07, 11, 05, 00); // yyyy, mm-1, dd, hh, mm, ss  
var d = new Date("02/07/2011"); // "mm/dd/yyyy"  
var d = new Date("02/07/2011 11:05:00"); // "mm/dd/yyyy hh:mm:ss"  
var d = new Date(1297076700000); // milliseconds  
var d = new Date("Mon Feb 07 2011 11:05:00 GMT"); // ""Day Mon dd yyyy hh:mm:ss GMT/UTC  
             */
        };

        Ext.apply(this, { 
            items : [{
                        xtype       : 'fieldset',
                        layout      : 'border',
                        collapsible : true,
                        autoWidth   : true,
                        height      : 220,
                        style       : {margin : '10px'},
                        title       : G1_TITLE,
                        region      : 'north', 
                        scroll      : false,       
                        items       : [ 
                            new sw.Promed.ViewFrame({
                                region: 'center',
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
                                id: 'BGGrid',
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
                                        dataIndex    : 'name', 
                                        mapping      : 'LpuUnitSet_Code',
                                        header       : G1_CODE_BUILDS,  
                                        width        : 155,
                                        style        : {'font-weight':'bold'}
                                    },{ 
                                        name         : 'datestart',
                                        dataIndex    : 'datestart',
                                        mapping      : 'curBegDateMonth',
                                        header       : G1_START_PER,  
                                        type         : 'date',
                                        dateFormat   : 'd.m.Y',
                                        width        : 140,
                                        //defaultValue : PER_START_DATE,
                                        editor       : new Ext.form.DateField({
                                            format  : 'd.m.Y',
                                            plugins : [ new Ext.ux.InputTextMask('99.99.9999', false) ] 
                                        }),
                                        //renderer     : function(){return PER_START_DATE}
                                     },{ 
                                        name         : 'dateend',
                                        dataIndex    : 'dateend', 
                                        mapping      : 'curEndDateMonth',
                                        header       : G1_END_PER,  
                                        type         : 'date',
                                        dateFormat   : 'd.m.Y',
                                        width        : 140,
                                        //defaultValue : PER_END_DATE,
                                        editor: new Ext.form.DateField({
                                            format  : 'd.m.Y',
                                            plugins : [ new Ext.ux.InputTextMask('99.99.9999', false) ]  
                                        }),
                                        //renderer     : function(){return PER_END_DATE}
                                    },{ 
                                        name         : 'number',
                                        header       : G1_NUMBER, 
                                        width        : 200,
                                        dataIndex    : 'number',
                                        mapping      : 'LpuUnitSet_Code',
                                        style        : {'font-weight' : 'bold'},
                                        editor       : new Ext.form.TextField({
                                            width      : 180,
                                            allowBlank : false
                                        })
                                    },{
                                        name         : 'mark',
                                        hidden       : true
                                    }
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
                                }
                            })
                        ]
                    }, {
                        xtype       : 'fieldset',
                        region      : 'north',
                        style       : {margin : '10px'},
                        columnWidth : 60,
                        height      : 60,
                        title       : G2_TITLE,
                        collapsible : true,
                        layout      : 'column',
                        items : [{
                                id         : 'startPeriodInoter',
                                fieldLabel : G2_START_PER,  
                                xtype      : 'datefield',
                                region     : 'center',
                                name       : 'startPeriodInnoter',
                                format     : 'd.m.Y',
                                plugins    : [ new Ext.ux.InputTextMask('99.99.9999', false) ], 
                                value      : DEFAULT_START_INOTER,
                                width      : 220
                            },{
                                id         : 'endPeriodInoter',   
                                fieldLabel : G2_END_PER,
                                margins    : {top : 50, left :50, right : 50, bottom : 50},
                                xtype      : 'datefield',
                                name       : 'endPeriodInnoter',
                                format     : 'd.m.Y',
                                plugins    : [ new Ext.ux.InputTextMask('99.99.9999', false) ] ,
                                value      : DEFAULT_END_INOTER,
                                width      : 220
                              }
                        ]
			}, {
				xtype: 'panel',
				layout: 'form',
				labelWidth: 30,
				border: false,
				bodyStyle: 'padding-left: 20px; background: transparent;',
				items: [{
					xtype: 'checkbox',
					id: 'rewvesmo_Registry_IsZNO',
					name: 'Registry_IsZNO',
					fieldLabel: 'ЗНО'
				}]
			}, {
                        xtype       : 'fieldset',
                        style       : {margin : '10px'},
                        layout      : 'border',
                        region      : 'north',
                        height      : 210,
                        title       : G3_TITLE,
                        collapsible : true,
                        items       : [ 
                            new sw.Promed.ViewFrame({
                                region: 'center',
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
                                object: 'Demand_VE',
                                dataUrl: '/?c=Demand_VE&m=getOrgSmoUfaList',
                                autoExpandColumn: 'autoexpand',
                                saveAtOnce: false,
                                saveAllParams: false,
                                clicksToEdit: 1,
                                id: 'SMOGrid_fSMO',
                                stringfields:[
                                    {name : 'processing', hidden: true},
                                    {name : 'id', mapping: 'Smo_id', hidden: true},
                                    {name : 'name', mapping: 'Smo_Nick', header: G3_NAME_SMO, width: 619, flex: 1, dataIndex: 'name', style: {'font-weight':'bold'}}
                                ],
                                toolbar: false
                            }) 
                        ]
            }],
            buttons: [
                {
                    handler: function(){ 
                        this.ownerCt.combineData();

                    },
                    text       : B_FORM_SAVE, 
                    autoHeight : true,
                    bodyStyle  : 'padding: 5px',
                    border     : false,
                    buttonAlign: 'left',
                    frame      : true,
                    id         : 'RegistryEditForm',
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
                        ShowHelp(Ext.getCmp('RegistryEditWindowVE_SMO').title);
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
                    id         : 'RegistryCancelFormSMO',
                    labelAlign : 'right',
                    labelWidth : 150,
                    iconCls: 'cancel16',
                    border : 1,
                    style: {
                            borderColor: 'gray',
                            borderStyle: 'solid'
                           }
            }]       
        });
        sw.Promed.swRegistryEditWindowVE_SMO.superclass.initComponent.apply(this, arguments);           
   }        
});