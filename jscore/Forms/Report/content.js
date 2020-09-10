/**
* content.js - Все панели в одном файле
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      sw.reports.designer.ui.content
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       yunitsky
* @version      27.04.2010
 */

Ext.ns('sw.reports.designer.ui.content');

sw.reports.designer.ui.content.EmptyContent = Ext.extend(Ext.Panel,{
    id    : 'EmptyContent',
    title : langs('Выберете обьект в дереве'),
    setup : function(){
        //alert('!');
    }
});

sw.reports.designer.ui.content.TableView = Ext.extend(Ext.Panel,{
    isEditable   : false,
    title        : 'override',
    layout       : 'fit',
    refresh      : function(){
        this.internalGrid.store.load();
    },
    setup : function(server,id,ownerId){
        var _recordToCM = function(record,width){
            var result = [];
            record.fields.each(function(item){
                result.push({
                    header : item['name'],
                    dataIndex : item['name'],
                    sortable : 'true',
                    width : width
                });
            });
            return result;
        }
        this.removeAll();
        var store = sw.reports.stores[id + 'Table'].getInstance(server.get('id'));
        store.baseParams.ownerId = 'all';
        this.internalGrid = new Ext.grid.GridPanel({
            columns : _recordToCM(new sw.reports.records[id + 'Table'](),200),
            store   : store
        });
        store.load({
            params : {}
        });
        this.add(this.internalGrid).show();
        this.doLayout();
        this.setTitle(langs('Таблица БД - ') + id);
    }
});

sw.reports.designer.ui.content.ParamFolderView = Ext.extend(Ext.Panel,{
    iconCls      : 'rpt-folder',
    isEditable   : true,
    title        : ' ',
    layout       : 'border',
    folderStore  : null,
    paramStore   : null,
    viewName     : 'ParamFolderView',
    initComponent: function(config){
        sw.reports.designer.ui.content.ParamFolderView.superclass.initComponent.call(this);
        this.addEvents('contentchange');
    },
    refreshContent : function(id){
        this.parentName = sw.reports.designer.ui.RepositoryPanel.getSelectedNode().text;
        if(this.ownerObjectId == 'root'){
            this.setTitle(langs('Каталог параметров'));
            this.folderStore.baseParams.ownerId = null;
            this.paramStore.baseParams.ownerId = null;
        } else {
            this.setTitle(langs('Каталог параметров - ') + this.parentName);
            this.folderStore.baseParams.ownerId = this.objectId;
            this.paramStore.baseParams.ownerId = this.objectId;
        }
        this.folderStore.load({
            callback : function(){
                this.folderGrid.getSelectionModel().clearSelections();
                this.fireEvent('contentchange');
                if(id && this.currentGrid == this.folderGrid){
                    var index = this.folderGrid.getStore().indexOfId(id);
                    this.folderGrid.getSelectionModel().selectRow(index);
                    this.folderGrid.getView().focusRow(index);
                }
            },
            scope    : this
        });
        this.paramStore.load({
            callback : function(){
                this.paramGrid.getSelectionModel().clearSelections();
                this.fireEvent('contentchange');
                if(id && this.currentGrid == this.paramGrid){
                    var index = this.paramGrid.getStore().indexOfId(id);
                    this.paramGrid.getSelectionModel().selectRow(index);
                    this.paramGrid.getView().focusRow(index);
                }
            },
            scope    : this
        });
    },
    currentGrid  : null,
    typeStore    : new Ext.data.SimpleStore({
        id     : 0,
        fields : ['id','value','colored'],
        data   : [
        ['char'  , langs('Текст'),langs('Текст')],
        ['int'   , langs('Целое число'),langs('Целое число')],
        ['date'  , langs('Дата'),langs('Дата')],
        ['datetime' , langs('Дата и время'),langs('Дата и время')],
        ['time'   , langs('Время'),langs('Время')],
        ['money'  , langs('Деньги'),langs('Деньги')],
        ['bool'  ,  langs('Чекбокс'),langs('Чекбокс')],
        ['yesno'  , langs('Да/нет'),langs('Да/нет')],
        ['dataset' ,'Набор данных','<b style="color:red">Набор данных</b>'],
        ['multidata' ,'Набор данных с множественным выбором','<b style="color:blue">Набор данных с множественным выбором</b>'],
        ['person' ,langs('Выбор человека'),langs('Выбор человека')],
        ['org' ,langs('Выбор организации'),langs('Выбор организации')],
        ['diag' ,langs('Диагноз'),langs('Диагноз')],
		['usluga' ,langs('Услуга'),langs('Услуга')],
		['complex' ,langs('Комплексная услуга'),langs('Комплексная услуга')],
		['uslcat', langs('Категория компл услуги'),langs('<b>Категория комплексной услуги</b>')],
				['drug' ,langs('Медикамент старый'),langs('Медикамент старый')],
				['drugprep' ,langs('Медикамент'),langs('Медикамент')],
        ['drugpack' ,langs('Упаковка'),langs('Упаковка')]
        //        ['dataset' ,'','<b></b>']
        ]
    }),

    setup : function(server,id,ownerId){
        this.objectId = id;
        this.server = server;
        this.ownerObjectId = ownerId;
        if(this.folderStore == null){
            this.folderStore = sw.reports.stores.ReportParameterCatalogTable.getInstance(server.get('id'));
            this.paramStore = sw.reports.stores.ReportParameterTable.getInstance(server.get('id'));
            this.folderGrid = new Ext.grid.GridPanel({
                region : 'center',
                split  : true,
                columns : [
                {
                    header : ' ',
                    width : 30 ,
                    renderer : function(value){
                        return '<img src="img/icons/rpt/folder.png" border="0">';
                    }
                },
                {
                    header : langs('Наименование'),
                    dataIndex : 'ReportParameterCatalog_Name',
                    width : 400,
                    sortable : true
                }
                ],
                store   : this.folderStore,
                listeners : {
                    'click' : function(event){
                        this.currentGrid = this.folderGrid;
                    },
                    scope : this
                },
                sm : new Ext.grid.RowSelectionModel({
                    singleSelect : true,
                    listeners    : {
                        'selectionchange' : function(){
                            this.currentGrid = this.folderGrid;
                            this.fireEvent('contentchange');
                        },
                        scope       : this
                    }
                })
            });
            this.paramGrid = new Ext.grid.GridPanel({
                region : 'south',
                tableName: 'paramGrid',
                height : 400,
                split  : true,
                columns: [
                {
                    header : ' ',
                    width : 30 ,
                    renderer : function(value){
                        return '<img src="img/icons/rpt/param.png" border="0">';
                    }
                },
                {
                    header : langs('Идентификатор'),
                    dataIndex : 'ReportParameter_Name',
                    width : 200,
                    sortable : true
                },

                {
                    header : langs('Тип'),
                    dataIndex : 'ReportParameter_Type',
                    width : 100,
                    sortable : true,
                    renderer : (function(value){
                        return this.typeStore.getById(value).data.value;
                    }).createDelegate(this)
                },
                {
                    header : langs('Метка'),
                    dataIndex : 'ReportParameter_Label',
                    width : 100,
                    sortable : true,
                    renderer : function(value, attr, row) {
                        if (row && row.data && row.data.ReportParameter_RegionName) {
                            return value + ' (' + row.data.ReportParameter_RegionName + ')'; 
                        } else {
                            return value;
                        }
                    }
                },
                {
                    //header: 'Название региона',
                    dataIndex: 'ReportParameter_RegionName',
                    hidden: true
                },
				{
					header : langs('Регион'),
					dataIndex : 'Region_id',
					width : 100,
					sortable : true
				},
                {
                    header : langs('Маска для ввода'),
                    dataIndex : 'ReportParameter_Mask',
                    width : 100,
                    sortable : true
                },

                {
                    header : langs('Ширина поля (px)'),
                    dataIndex : 'ReportParameter_Length',
                    width : 100,
                    sortable : true
                },

                {
                    header : langs('Размер текста'),
                    dataIndex : 'ReportParameter_MaxLength',
                    width : 100,
                    sortable : true
                },

                {
                    header : langs('Выравнивание'),
                    dataIndex : 'ReportParameter_Align',
                    width : 100,
                    sortable : true,
                    renderer : function(value){
                        return {
                            'l' : langs('Влево'),
                            'c' : langs('По центру'),
                            'r' : langs('Вправо')
                        }
                        [value];
                    }
                },
                {
                    header : 'CSS',
                    dataIndex : 'ReportParameter_CustomStyle',
                    width : 100,
                    sortable : true
                },

                {
                    header : langs('Значение по умолчанию'),
                    dataIndex : 'ReportParameter_Default',
                    width : 200,
                    sortable : true
                },

                {
                    header : 'SQL',
                    dataIndex : 'ReportParameter_SQL',
                    width : 300,
                    sortable : true
                },

                {
                    header : langs('Поле идентификатора'),
                    dataIndex : 'ReportParameter_SQL_IdField',
                    width : 200,
                    sortable : true
                },

                {
                    header : langs('Поле для текста'),
                    dataIndex : 'ReportParameter_SQL_TextField',
                    width : 200,
                    sortable : true
                },

                {
                    header : langs('Шаблон для комбо'),
                    dataIndex : 'ReportParameter_SQL_XTemplate',
                    renderer : function(value){
                        return Ext.util.Format.htmlEncode(value);
                    },
                    width : 200,
                    sortable : true
                }
                ],
                store   : this.paramStore,
                listeners : {
                    'click' : function(event){
                        this.currentGrid = this.paramGrid;
                    },
                    scope : this
                },
                sm : new Ext.grid.RowSelectionModel({
                    singleSelect : true,
                    listeners    : {
                        'selectionchange' : function(){
                            this.currentGrid = this.paramGrid;
                            this.fireEvent('contentchange');
                        },
                        scope       : this
                    }
                })
            });
            this.add(this.folderGrid);
            this.add(this.paramGrid);
            this.currentGrid = this.folderGrid;
        }
        if(this.ownerObjectId == 'root'){
            this.paramGrid.hide();
        } else {
            this.paramGrid.show();
        }
        this.refreshContent();
        this.doLayout();
    },
    getSelected : function(){
        if(this.currentGrid == null) return null;
        return this.currentGrid.getSelectionModel().getSelected();
    },
    deleteContent : function(callback,scope){
        var selected = this.getSelected();
        var url = '';
        var params = null;
        if(selected){
            if(selected.data.ReportParameterCatalog_Name){
                url = sw.consts.url(sw.consts.actions.CRUD_REPORTPARAMETERCATALOG);
                params = {
                    __mode   : 'delete',
                    serverId : this.server.data.id,
                    ReportParameterCatalog_id : selected.data.ReportParameterCatalog_id
                };
            } else {
                url = sw.consts.url(sw.consts.actions.CRUD_REPORTPARAMETER);
                params = {
                    __mode   : 'delete',
                    serverId : this.server.data.id,
                    ReportParameter_id : selected.data.ReportParameter_id
                };
            }
            Ext.Ajax.request({
                url    : url,
                params : params,
                success : function(){
                    if(callback) callback.call(scope || this);
                }
            });
        }
    },
    getForm : function(state){
        var win = this;
        if( state == 'addParameterCatalog' || ((!state || state == 'edit') && this.currentGrid == this.folderGrid) ){
            this.currentForm = new sw.reports.designer.ui.BaseFormPanel ({
                url   : sw.consts.url(sw.consts.actions.CRUD_REPORTPARAMETERCATALOG),
                items : [
                {
                    fieldLabel  : langs('Родитель'),
                    disabled    : true,
                    value       : this.parentName
                },{
                    xtype       : 'hidden',
                    name        : 'ReportParameterCatalog_pid',
                    value       : this.objectId
                },{
                    xtype       : 'hidden',
                    name        : 'ReportParameterCatalog_id'
                },{
                    name        : 'ReportParameterCatalog_Name' ,
                    fieldLabel  : langs('Наименование')
                }]
            });
        } else {
            this.currentForm = new sw.reports.designer.ui.BaseFormPanel({
                trackResetOnLoad : true,
                url   : sw.consts.url(sw.consts.actions.CRUD_REPORTPARAMETER),
                formName: 'parameterEditForm',
                isParametersForm : true,
                listeners : {
                    clientvalidation : function(form,valid){
                       var editor = form.findById('Parameter_SQL_editor');
                       if(editor)
                           form.findById('ReportParameter_SQL').setValue(editor.getValue());
                    }
                },
                tbar   : new Ext.Toolbar({
                    items : [
                    new Ext.Button({
                        text : langs('Очистить'),
                        scope : this,
                        iconCls : 'rpt-clear',
                        handler : function(){
                            this.currentForm.getForm().reset();
                            var tab1 = this.currentForm.findById('baseParamsTab');
                            tab1.ownerCt.setActiveTab(tab1);
                        }
                    }),
                    '-',
//                    new Ext.Button({
//                        text : 'Сохранить как шаблон',
//                        iconCls : 'rpt-save',
//                        scope : this,
//                        handler : function(){
//                        }
//                    }),
//                    '-',
//                    new Ext.Button({
//                        iconCls : 'rpt-templ',
//                        text : 'Шаблоны',
//                        scope : this,
//                        handler : function(){
//                        }
//                    }),
//                    '-',
                    new Ext.Button({
                        text : langs('Скопировать из'),
                        disabled : true
                    }),
                    new sw.reports.designer.ui.DefaultCombo({
                        mode        : 'remote',
                        width       : 200,
                        triggerAction : 'all',
                        name        : 'ReportParameter_id',
                        hiddenName  : 'ReportParameter_id',
                        minChars    : 1,
                        store       : new Ext.data.JsonStore({
                            autoLoad   : true,
                            baseParams : {
                                serverId :  this.server.data.id
                            },
                            url     :  sw.consts.url(sw.consts.actions.COMBO_PARAMETERS),
                            root    : 'items',
                            fields  : [
                            'ReportParameter_id',
                            'ReportParameter_Name',
                            'ReportParameter_Label',
                            'ReportParameter_Type',
                            'ReportParameter_Mask',
							'Region_id',
                            'ReportParameter_Length',
                            'ReportParameter_MaxLength',
                            'ReportParameter_Default',
                            'ReportParameter_Align',
                            'ReportParameter_CustomStyle',
                            'ReportParameter_SQL',
                            'ReportParameter_SQL_IdField',
                            'ReportParameter_SQL_TextField',
                            'ReportParameter_SQL_XTemplate'
                            ]
                        }),
                        tpl         : '<tpl for="."><div class="x-combo-list-item">{ReportParameter_Label} <span style="color:gray">{ReportParameter_Name}</span></div></tpl>',
                        displayField : 'ReportParameter_Name',
                        valueField  : 'ReportParameter_id',
                        forceSelection : true,
                        listeners   : {
                            scope : this,
                            'select' : function(combo,rec,num){
                                var idF = this.currentForm.getForm().findField('ReportParameter_id');
                                var idC = this.currentForm.getForm().findField('ReportParameterCatalog_id');
                                var id = idF.getValue();
                                var idp = idC.getValue();
                                this.currentForm.getForm().loadRecord(rec);
                                this.currentForm.getForm().isValid();
                                idF.setValue(id);
                                idC.setValue(idp);
                            }
                        }

                    })
                    ]
                }),
                layout : 'fit',
                //width : 600,
                testComboBox : function(){
                    var that = this;
                    var data = this.findById('resultgrid').getStore();
                    var displayField = this.findById('ReportParameter_SQL_TextField').getValue();
                    var valueField = this.findById('ReportParameter_SQL_IdField').getValue();
                    var templ = this.findById('ReportParameter_SQL_XTemplate').getValue();
                    var toolbar = this.findById('sqltab').getTopToolbar();
                    if(this.testedCombo){
                        //toolbar.items.items[4].destroy();
                        this.testedCombo.destroy();
                        this.testedCombo = null;
                    };
                    this.testedCombo = new sw.reports.designer.ui.DefaultCombo({
                        mode        : 'local',
                        width       : 400,
                        triggerAction : 'all',
                        name        : 'testedCombo',
                        minChars    : 1,
                        store       : data,
                        displayField : displayField,
                        valueField  : valueField,
                        tpl         : templ
                    });
                    toolbar.add(this.testedCombo);
                },
                items : [
                new Ext.TabPanel({
                    height  : 280,
                    width   : 600,
                    layoutOnTabChange: true,
                    deferredRender : false,
                    //hideMode: 'offsets',
                    bodyStyle:'background:transparent',
                    id          : 'sw.reports.maintabpanel',
                    activeTab   : 0,
                    items       : [
                    {
                        title  : langs('Базовые параметры'),
                        layout : 'column',
                        id     : 'baseParamsTab',
                        bodyStyle:'background:transparent',
                        items  : [
                        {
                            width : 300,
                            layout : 'form',
                            labelAlign:'top',
                            defaultType: 'textfield',
                            autoHeight : true,
                            defaults: {
                                width : 280,
                                blankText  : "Поле обязательно для заполнения",
                                allowBlank : false,
                                selectOnFocus : true
                            },
                            border: false,
                            bodyStyle:'background:transparent;padding:10px;',
                            items    : [
                            {
                                xtype       : 'hidden',
                                name        : 'ReportParameterCatalog_id',
                                value       : this.objectId
                            },
                            {
                                xtype       : 'hidden',
                                name        : 'ReportParameter_id'
                            },
                            {
                                name        : 'ReportParameter_Name' ,
                                fieldLabel  : langs('Идентификатор'),
                                listeners:{
                                    'change': function(value){
                                        var ParamId = value.getValue();
                                        var field = value;
                                        var RegionId = this.ownerCt.ownerCt.items.items[1].items.items[4].getValue();
                                        if(ParamId && RegionId){  //Если есть оба параметра - делаем ajax-запрос
                                            var urlParamId = sw.consts.url(sw.consts.actions.CHECK_PARAMID_AJAX);
                                            var paramsParamid = {
                                                ParamId   : ParamId,
                                                RegionId : RegionId
                                            };
                                            Ext.Ajax.request({
                                                url    : urlParamId,
                                                params : paramsParamid,
                                                success : function(response,d){
                                                    var response_obj = Ext.util.JSON.decode(response.responseText);
                                                    if(response_obj.success){
                                                        field.clearInvalid();
                                                    }
                                                    else{
                                                        field.markInvalid();
                                                        alert(langs('Параметр с таким идентификатором уже существует в выбранном Вами регионе'));
                                                        field.setValue('');
                                                    }
                                                }
                                            });
                                        }
                                    }
                                }/*,

                                plugins     : [Ext.ux.plugins.RemoteValidator],
                                rvOptions   : {
                                    url : sw.consts.url(sw.consts.actions.CHECK_PARAM_ID),
                                    params : { serverId : 1 }
                                }*/
                            },
                            {
                                xtype  : 'combo',
                                store  : this.typeStore,
                                value       : 'char',
                                name        : 'ReportParameter_Type' ,
                                //id          : 'ReportParameter_Type' ,
                                hiddenName  : 'ReportParameter_Type' ,
                                fieldLabel  : langs('Тип'),
                                displayField: 'value',
                                valueField  : 'id',
                                triggerAction : 'all',
                                mode        : 'local',
                                editable    : false,
                                tpl         : '<tpl for="."><div class="x-combo-list-item">{colored}</div></tpl>',
                                listeners   : {
                                    scope : this,
                                    'select' : function(combo,rec,num){
                                        var sqlTab = this.currentForm.findById('sqltab');
                                        if(rec.data.id == 'dataset' || rec.data.id == 'multidata'){
                                            sqlTab.enable();
                                        } else {
                                            sqlTab.disable();
                                        }
                                    }
                                }
                            },{
                                name        : 'ReportParameter_Default' ,
                                fieldLabel  : langs('Значение по умолчанию'),
                                allowBlank  : 'true'
                            },{
                                xtype  : 'combo',
                                store  : new Ext.data.SimpleStore({
                                    fields : ['id','value'],
                                    data   : [
                                    ['l' , langs('Влево')],
                                    ['c' , langs('По центру')],
                                    ['r' , langs('Вправо')]
                                    ]
                                }),
                                value       : 'l',
                                name        : 'ReportParameter_Align' ,
                                hiddenName  : 'ReportParameter_Align' ,
                                fieldLabel  : langs('Выравнивание текста в поле'),
                                displayField: 'value',
                                valueField  : 'id',
                                triggerAction : 'all',
                                mode        : 'local',
                                editable    : false
                            },{
                                name        : 'ReportParameter_CustomStyle' ,
                                fieldLabel  : langs('Дополнительный CSS стиль'),
                                allowBlank  : 'true'
                            }
                            ]
                        },{
                            width : 300,
                            layout : 'form',
                            labelAlign:'top',
                            defaultType: 'textfield',
                            autoHeight : true,
                            defaults: {
                                width : 280,
                                blankText  : "Поле обязательно для заполнения",
                                allowBlank : false,
                                selectOnFocus : true
                            },
                            border: false,
                            bodyStyle:'background:transparent;padding:10px;',
                            items : [
                            {
                                xtype       : 'numberfield',
                                allowDecimals : false,
                                name        : 'ReportParameter_MaxLength' ,
                                fieldLabel  : langs('Максимально допустимая длинна текста'),
                                allowBlank  : 'true'
                            }
                            ,{
                                name        : 'ReportParameter_Label' ,
                                fieldLabel  : langs('Метка')
                            },{
                                name        : 'ReportParameter_Mask' ,
                                fieldLabel  : langs('Маска для ввода (регулярное выражение)'),
                                allowBlank  : 'true'
                            },{
                                xtype       : 'numberfield',
                                allowDecimals : false,
                                name        : 'ReportParameter_Length' ,
                                fieldLabel  : langs('Длинна поля в пикселах '),
                                allowBlank  : 'true'
                            },
								{
									xtype  : 'combo',
									store  : new Ext.data.SimpleStore({
										fields : ['id','value'],
										data   : [
                                            [null, 'root'],
											['10' , langs('Карелия')],
											['19' , langs('Хакасия')],
											['30' , langs('Астрахань')],
											['60' , langs('Псков')],
											['63' , langs('Самара')],
											['64' , langs('Саратов')],
											['77' , langs('Москва')],
											['101' , langs('Казахстан')],
											['66' , langs('Екатеринбург')],
											['59' , langs('Пермь')],
											['1' , langs('Адыгея')],
											['2' , langs('Уфа')],
                                            ['3' , langs('Бурятия')],
											['201' , langs('Беларусь')],
											['58' , langs('Пенза')],
											['40' , langs('Калуга')],
											['91' , langs('Крым')],
											['11' , langs('Сыктывкар')],
											['35' , langs('Вологда')],
											['50' , langs('Московская область')],
											['24' , langs('Красноярский край')],
											['26' , langs('Ставропольский край')],
											['76' , langs('Ярославль')],
											['12' , langs('Марий Эл')]
										]
									}),
									name        : 'Region_id' ,
									hiddenName  : 'Region_id' ,
									fieldLabel  : langs('Регион'),
									displayField: 'value',
									valueField  : 'id',
									triggerAction : 'all',
									mode        : 'local',
									editable    : false,
                                    listeners: {
                                        'select': (function(combo){
                                            var ParamId = this.ownerCt.ownerCt.items.items[0].items.items[2].getValue();
                                            var field = this.ownerCt.ownerCt.items.items[0].items.items[2];
                                            var RegionId = combo.getValue();
                                            if(ParamId && RegionId){ //Если есть оба параметра - делаем ajax-запрос
                                                var urlParamId = sw.consts.url(sw.consts.actions.CHECK_PARAMID_AJAX);
                                                var paramsParamid = {
                                                    ParamId   : ParamId,
                                                    RegionId : RegionId
                                                };
                                                Ext.Ajax.request({
                                                    url    : urlParamId,
                                                    params : paramsParamid,
                                                    success : function(response,d){
                                                        var response_obj = Ext.util.JSON.decode(response.responseText);
                                                        if(response_obj.success){
                                                            field.clearInvalid();
                                                        }
                                                        else{
                                                            field.markInvalid();
                                                            alert(langs('Параметр с таким идентификатором уже существует в выбранном Вами регионе'));
                                                            combo.setValue('');
                                                        }
                                                    }
                                                });
                                            }
                                        })
                                    }
								}
                            ]
                        }
                        ]
                    },
                    {
                        title    : langs('Параметры датасета'),
                        //                        disabled : true,
                        tbar   : new Ext.Toolbar({
                            items : [
                            new Ext.Button({
                                text : 'x-template',
                                id   : 'xtemplate',
                                scope : this,
                                disabled : true,
                                handler : function(){
                                }
                            }),
                            '-',
                            new Ext.Button({
                                text : langs('Проверить комбо'),
                                id   : 'combotest',
                                scope : this,
                                disabled : true,
                                handler : function(){
                                    this.currentForm.testComboBox();
                                }
                            }),
                            '-'
                            ]
                        }),
                        id       : 'sqltab',
                        layout   : 'border',
                        bodyStyle:'background:transparent',
                        items  : [
                        {
                            region : 'center',
                            layout : 'form',
                            labelAlign:'top',
                            defaultType: 'textfield',
                            autoHeight : true,
                            defaults: {
                                width : 300,
                                blankText  : "Поле обязательно для заполнения",
                                allowBlank : true,
                                selectOnFocus : true
                            },
                            border: false,
                            bodyStyle:'background:transparent;padding:10px;',
                            items    : [
                            {
                                name        : 'ReportParameter_SQL_IdField' ,
                                id          : 'ReportParameter_SQL_IdField' ,
                                fieldLabel  : langs('Имя столбца для ключа')
                            },
                            {
                                name        : 'ReportParameter_SQL_TextField' ,
                                id          : 'ReportParameter_SQL_TextField' ,
                                fieldLabel  : langs('Имя столбца для текста')
                            },
                            {
                                xtype       : 'textarea',
                                height      : 90,
                                width       : 580,
                                name        : 'ReportParameter_SQL_XTemplate' ,
                                id          : 'ReportParameter_SQL_XTemplate' ,
                                fieldLabel  : 'XTemplate',
                                allowBlank  : 'true'
                            }
                            ]
                        }
                        ]
                    },
                    {
                        title    : 'SQL',
                        id       : 'sqledittab',
                        layout   : 'border',
                        bodyStyle: 'background:transparent',
                        server   : this.server,
                        initComponent : function(){
                            this.queryEditor = new Ext.ux.form.EditArea({
                                region      : 'center',
                                xtype       : 'ux-editarea',
                                id          : 'Parameter_SQL_editor',
                                height      : 120,
                                fieldLabel  : 'SQL',
                                allowBlank  : 'true',
                                syntax      : 'sql',
                                allow_toggle: false,
                                toolbar     : ' '
                            });
                            this.toolbar = new Ext.Toolbar({
                                items : [
                                new Ext.Button({
                                    text : langs('Парсинг и проверка'),
                                    id   : 'sqltest',
                                    iconCls : 'rpt-parse',
                                    scope : this,
                                    handler : function(){
                                        var sqlField = this.queryEditor.getValue();
                                        var resultTab = this.ownerCt.findById('resulttab');
										var Region_id = this.ownerCt.findById('baseParamsTab').items.items[1].items.items[4].getValue();
                                        if(sqlField){
                                            var lm = new Ext.LoadMask(this.ownerCt.getEl(),{
                                                msg : langs('Идет проверка правильности запроса...')
                                            });
                                            lm.show();
                                            sw.ParamFactory.checkSql(this.server.data.id,Region_id,sqlField,(function(error,params,data){
                                                lm.hide();
                                                lm.destroy();
                                                if(error){
                                                    Ext.Msg.alert(langs('Ошибка проверки запроса'),error);
                                                } else {
                                                    this.findById('ReportParameter_SQL').setValue(this.queryEditor.getValue());
                                                    resultTab.setData(params,data);
                                                    resultTab.ownerCt.setActiveTab(resultTab);
                                                }
                                            }).createDelegate(this));
                                        }
                                    }
                                })
                                ]
                            });
                            Ext.apply(this,{
                                items : [
                                {
                                    layout : 'fit',
                                    region : 'center',
                                    id     : 'sqlEditorContainer',
                                    tbar   : this.toolbar,
                                    items  : [
                                //this.queryEditor
                                ]
                                },
                                {
                                    xtype : 'hidden',
                                    id    : 'ReportParameter_SQL'
                                }
                                ]
                            });
                            this.constructor.superclass.initComponent.call(this);
                        },
                        listeners:{
                            activate: function () {
                                var sqlPanel = Ext.getCmp('sqledittab');
                                var editorCont = Ext.getCmp('sqlEditorContainer');
                                if(sqlPanel && sqlPanel.queryEditor && editorCont && editorCont.items.getCount() == 0){
                                    editorCont.add(sqlPanel.queryEditor);
                                    editorCont.doLayout();
                                    sqlPanel.queryEditor.setValue(this.findById('ReportParameter_SQL').getValue());
                                }
                                sqlPanel.queryEditor.show.defer(200,sqlPanel.queryEditor);
                            },
                            beforehide: function () {
                                var sqlTab = Ext.getCmp('sqledittab');
                                if (sqlTab && sqlTab.queryEditor && typeof sqlTab.queryEditor.hide == 'function') {
                                    sqlTab.queryEditor.hide();
                                }
                                
                            }
                        }
                    },
                    {
                        title    : langs('Данные'),
                        //disabled :  true,
                        id       : 'resulttab',
                        layout   : 'border',
                        bodyStyle: 'background:transparent',
                        listeners : {
                            afterlayout : function(cont,layout){
                                var resultGrid = this.findById('resultgrid');
                                if(resultGrid.rendered && this.store && this.store != resultGrid.store){
                                    resultGrid.reconfigure(this.store,this.columnModel);
                                }
                            }
                        },
                        setXTemplate : function(data){
                            var xtemplField = this.ownerCt.findById('ReportParameter_SQL_XTemplate');
                            insertAtCursor(xtemplField.getEl().dom,data);
                        },
                        setData  : function(params,data){
                            var grid = this.findById('paramsPropertyGrid');
                            var idCombo = this.ownerCt.findById('ReportParameter_SQL_IdField');
                            var textCombo = this.ownerCt.findById('ReportParameter_SQL_TextField');
                            var xtempl = this.ownerCt.findById('sqltab').getTopToolbar().items.items[0];
                            var testButton = this.ownerCt.findById('sqltab').getTopToolbar().items.items[2];
                            xtempl.enable();
                            testButton.enable();
                            var menu = new Ext.menu.Menu({
                                items : [
                                {
                                    text : langs('Вставить стандартный шаблон'),
                                    scope : this,
                                    handler : function(){
                                        this.setXTemplate('<tpl for="."><div class="x-combo-list-item"></div></tpl>');
                                    }
                                },'-'
                                ]
                            });
                            xtempl.menu = menu;
                            var source = {};
                            for(var i=0; i<params.length;i++){
                                var param = params[i];
                                source[param.ReportParameter_Name] = param.ReportParameter_Default;
                            }
                            grid.setSource(source);
                            var cm = [];
                            var newData = [];
                            var fields = [];
                            for(i in data[0]){
                                cm.push({
                                    header    :  i,
                                    dataIndex :  i
                                });
                                fields.push(i);
                                menu.addMenuItem({
                                    text  : '{'+i+'}',
                                    scope : this,
                                    handler : function(item,e){
                                        this.setXTemplate(item.text);
                                    }
                                });
                            }
                            for(i=0; i<data.length;i++){
                                var temp = [];
                                for(var row in data[i]){
                                    temp.push(data[i][row]);
                                }
                                newData.push(temp);
                            }
                            var store = new Ext.data.SimpleStore({
                                data    : newData,
                                fields  : fields
                            });
                            this.columnModel = new Ext.grid.ColumnModel(cm);
                            this.store = store;
                            this.initialConfig.listeners.afterlayout.call(this);
                        },
                        items  : [
                        {
                            width : 180,
                            split : true,
                            region : 'west',
                            border: false,
                            layout : 'fit',
                            bodyStyle: 'background:transparent',
                            items : [
                            new Ext.grid.PropertyGrid({
                                id : 'paramsPropertyGrid'
                            })
                            ],
                            bbar : new Ext.Toolbar({
                                items : [
                                {
                                    text : langs('Обновить данные'),
                                    scope   : this,
                                    handler : function(){
                                        var sqlField = this.currentForm.findById('Parameter_SQL_editor');
                                        if(sqlField){
                                            sqlField = sqlField.getValue();
                                        } else {
                                            sqlField = this.currentForm.findById('ReportParameter_SQL').getValue();
                                        }
                                        var resultTab = this.currentForm.findById('resulttab');
                                        var params = this.currentForm.findById('paramsPropertyGrid');
                                        var Region_id = this.currentForm.findById('baseParamsTab').items.items[1].items.items[4].getValue();
                                        if(sqlField){
                                            var lm = new Ext.LoadMask(resultTab.ownerCt.getEl(),{
                                                msg : langs('Идет проверка правильности запроса...')
                                            });
                                            lm.show();
                                            // Собираем параметры
                                            var ps = {};
                                            params.store.each(function(record){
                                                ps[record.data.name] = record.data.value;
                                            },this);
                                            sw.ParamFactory.updateData(this.server.data.id,Region_id,sqlField,ps,(function(error,params,data){
                                                lm.hide();
                                                lm.destroy();
                                                if(error){
                                                    Ext.Msg.alert(langs('Ошибка проверки запроса'),error);
                                                } else {
                                                    resultTab.setData(params,data);
                                                }
                                            }).createDelegate(this));
                                        }
                                    }
                                }
                                ]
                            })
                        },
                        {
                            region : 'center',
                            border: false,
                            bodyStyle: 'background:transparent',
                            layout : 'fit',
                            items  : [
                            new Ext.grid.GridPanel({
                                id       : 'resultgrid',
                                columns  : [
                                {
                                    header : '1',
                                    dataIndex : '1'
                                }
                                ],
                                store : new Ext.data.SimpleStore({
                                    data    : [],
                                    fields  : ['1']
                                })
                            })
                            ]
                        }
                        ]
                    }
                    ]
                })
                ]
            })

        }
        return this.currentForm;
    }
});
sw.reports.designer.ui.content.ReportFolderView = Ext.extend(Ext.Panel,{
    isEditable   : true,
    title        : ' ',
    layout       : 'border',
    folderStore  : null,
    reportStore  : null,
    viewName     : 'ReportFolderView',
    initComponent: function(config){
        sw.reports.designer.ui.content.ReportFolderView.superclass.initComponent.call(this);
        this.addEvents('contentchange');
    },
    refreshContent : function(id){
        this.parentName = sw.reports.designer.ui.RepositoryPanel.getSelectedNode().text;
        if(this.ownerObjectId == 'root'){
            this.setTitle(this.parentName);
            this.folderStore.baseParams.ownerId = null;
            this.reportStore.baseParams.ownerId = null;
        } else {
            this.setTitle(langs('Каталог отчетов - ') + this.parentName);
            this.folderStore.baseParams.ownerId = this.objectId;
            this.reportStore.baseParams.ownerId = this.objectId;
        }
        this.folderStore.load({
            callback : function(){
                this.folderGrid.getSelectionModel().clearSelections();
                this.fireEvent('contentchange');
                if(id && this.currentGrid == this.folderGrid){
                    var index = this.folderGrid.getStore().indexOfId(id);
                    this.folderGrid.getSelectionModel().selectRow(index);
                    this.folderGrid.getView().focusRow(index);
                }
            },
            scope    : this
        });
        this.reportStore.load({
            callback : function(){
                this.reportGrid.getSelectionModel().clearSelections();
                this.fireEvent('contentchange');
                if(id && this.currentGrid == this.reportGrid){
                    var index = this.reportGrid.getStore().indexOfId(id);
                    this.reportGrid.getSelectionModel().selectRow(index);
                    this.reportGrid.getView().focusRow(index);
                }
            },
            scope    : this
        });
    },
    currentGrid  : null,
    setup : function(server,id,ownerId){
        this.objectId = id;
        this.server = server;
        this.ownerObjectId = ownerId;
        if(this.folderStore == null){
            this.folderStore = sw.reports.stores.ReportCatalogTable.getInstance(server.get('id'));
            this.reportStore = sw.reports.stores.ReportTable.getInstance(server.get('id'));
            this.folderGrid = new Ext.grid.GridPanel({
                region : 'center',
                split  : true,
                tableName: 'folderGrid',
                columns : [
                {
                    header : ' ',
                    width : 30 ,
                    renderer : function(value){
                        return '<img src="img/icons/rpt/folder.png" border="0">';
                    }
                },
                {
                    header : langs('Наименование'),
                    dataIndex : 'ReportCatalog_Name',
                    width : 300,
                    sortable : true
                },
				{
					header : langs('Регион'),
					dataIndex : 'Region_id',
					width : 100,
					sortable : true
				},
                {
                    header : langs('Папка сервера'),
                    dataIndex : 'ReportCatalog_Path',
                    width : 200,
                    sortable : true
                },

                {
                    header : langs('Статус'),
                    dataIndex : 'ReportCatalog_Status',
                    width : 100,
                    sortable : true,
                    renderer : function(value){
                        return [langs('Всем'),langs('Минздрав'),langs('Администратор')][value];
                    }
                },
                {
                    header : langs('Позиция'),
                    dataIndex : 'ReportCatalog_Position',
                    width : 100,
                    sortable : true
                }
                ],
                store   : this.folderStore,
                listeners : {
                    'click' : function(event){
                        this.currentGrid = this.folderGrid;
                    },
                    scope : this
                },
                sm : new Ext.grid.RowSelectionModel({
                    singleSelect : true,
                    listeners    : {
                        'selectionchange' : function(){
                            this.currentGrid = this.folderGrid;
                            this.fireEvent('contentchange');
                        },
                        scope       : this
                    }
                }),
				keys: [{
							key: [
								Ext.EventObject.F3
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

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}


								switch ( e.getKey() ) {
									case Ext.EventObject.F3:
									if(e.altKey)
									{
										var sel_record = this.folderGrid.getSelectionModel().getSelected();
										if(!Ext.isEmpty(sel_record.data) && !Ext.isEmpty(sel_record.data.ReportCatalog_id) && sel_record.data.ReportCatalog_id > 0)
										{
											var params = new Object();
											params['key_id'] = sel_record.data.ReportCatalog_id;
											params['key_field'] = 'ReportCatalog_id';
											getWnd('swAuditWindow').show(params);
										}
									}
									break;
								}
							},
							scope: this,
							stopEvent: true
						}],
            });
            this.reportGrid = new Ext.grid.GridPanel({
                region : 'south',
                split  : true,
                tableName: 'reportGrid',
                height : 400,
                columns : [
                {
                    header : ' ',
                    width : 30 ,
                    renderer : function(value){
                        return '<img src="img/icons/rpt/report.png" border="0">';
                    }
                },
                {
                    header : langs('Заголовок'),
                    dataIndex : 'Report_Caption',
                    width : 300,
                    sortable : true
                },

                {
                    header : langs('Наименование'),
                    dataIndex : 'Report_Title',
                    width : 300,
                    sortable : true
                },

                {
                    header : langs('Описание'),
                    dataIndex : 'Report_Description',
                    width : 300,
                    sortable : true
                },

                {
                    header : langs('Имя файла'),
                    dataIndex : 'Report_FileName',
                    width : 200,
                    sortable : true
                },

                {
                    header : langs('Статус'),
                    dataIndex : 'Report_Status',
                    width : 100,
                    sortable : true,
                    renderer : function(value){
                        return [langs('Всем'),langs('Минздрав'),langs('Администратор')][value];
                    }
                },
				{
					header : langs('Тип отчета'),
					dataIndex : 'ReportType_id',
					width : 100,
					sortable : true,
					renderer : function(value){
						return [langs('Эталонный'),langs('Региональный'),langs('Временный')][value-1];
					}
				},{
                    header : langs('Позиция'),
                    dataIndex : 'Report_Position',
                    width : 100,
                    sortable : true
                },{
					header : 'БД для формирования отчёта',
					dataIndex : 'DatabaseType',
					width : 100,
					sortable : true,
					renderer : function(value){
						return ['Рабочая','Отчётная','Реестровая'][value-1];
					}
				}
                ],
                store   : this.reportStore,
                listeners : {
                    'click' : function(event){
                        this.currentGrid = this.reportGrid;
                    },
                    scope : this
                },
                sm : new Ext.grid.RowSelectionModel({
                    singleSelect : true,
                    listeners    : {
                        'selectionchange' : function(){
                            this.currentGrid = this.reportGrid;
                            this.fireEvent('contentchange');
                        },
                        scope       : this
                    }
                }),
                keys: [{
                            key: [
                                Ext.EventObject.F3
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

                                e.returnValue = false;

                                if ( Ext.isIE ) {
                                    e.browserEvent.keyCode = 0;
                                    e.browserEvent.which = 0;
                                }


                                switch ( e.getKey() ) {
                                    case Ext.EventObject.F3:
                                    if(e.altKey)
                                    {
                                        var sel_record = this.reportGrid.getSelectionModel().getSelected();
                                        if(!Ext.isEmpty(sel_record.data) && !Ext.isEmpty(sel_record.data.Report_id) && sel_record.data.Report_id > 0)
                                        {
                                            var params = new Object();
                                            params['key_id'] = sel_record.data.Report_id;
                                            params['key_field'] = 'Report_id';
                                            getWnd('swAuditWindow').show(params);
                                        }
                                    }
                                    break;
                                }
                            },
                            scope: this,
                            stopEvent: true
                        }],
            });
            this.add(this.folderGrid);
            this.currentGrid = this.folderGrid;
            this.add(this.reportGrid);
        }
        if(this.ownerObjectId == 'root'){
            this.reportGrid.hide();
        } else {
            this.reportGrid.show();
        }
        this.refreshContent();
        this.doLayout();
    },
    regionCheckRenderer: function(v, p, record, table_id) {
        var name = 'checkboxRegion_'+record.get('id');
        var value = 'value="'+name+'"';
        var checked = record.get('RegionSelected') == true ? ' checked="checked"' : '';
        var onclick = 'onClick="Ext.getCmp(\'' + table_id + '\').checkOne(this.value);"';
        var disabled = '';

        return '<input type="checkbox" '+value+' '+checked+' '+onclick+' '+disabled+'>';
    },
    getSelected : function(){
        if(this.currentGrid == null) return null;
		if(Ext.isEmpty(this.currentGrid.getSelectionModel().getSelected()))
			return -1;
        var record = this.currentGrid.getSelectionModel().getSelected();
        if (this.currentGrid.tableName) {
            record.tableName = this.currentGrid.tableName;
        }
        return record;
    },
    deleteContent : function(callback,scope){
        var selected = this.getSelected();
        var url = '';
        var params = null;
        if(selected){
            if(selected.data.Report_id){
                url = sw.consts.url(sw.consts.actions.CRUD_REPORT);
                params = {
                    __mode   : 'delete',
                    serverId : this.server.data.id,
                    Report_id : selected.data.Report_id
                };
            } else {
                url = sw.consts.url(sw.consts.actions.CRUD_REPORTCATALOG);
                params = {
                    __mode   : 'delete',
                    serverId : this.server.data.id,
                    ReportCatalog_id : selected.data.ReportCatalog_id
                };
            }
            Ext.Ajax.request({
                url    : url,
                params : params,
                success : function(){
                    if(callback) callback.call(scope || this);
                }
            });
        }
    },
    getForm : function(state){
        var win = this;
        if( state == 'addReportCatalog' || ((!state || state == 'edit') && this.currentGrid == this.folderGrid) ){

            var baseTab = new Ext.Panel({
                layout : 'form',
                labelAlign:'top',
                defaultType: 'textfield',
                autoHeight : true,
                defaults: {
                    width : 450,
                    blankText  : "Поле обязательно для заполнения",
                    allowBlank : false,
                    selectOnFocus : true
                },
                border: false,
                bodyStyle:'background:transparent;padding:10px;',
                items:[
                    {
                    fieldLabel  : lang['roditel'],
                    disabled    : true,
                    value       : this.parentName
                },{
                    xtype       : 'hidden',
                    name        : 'ReportCatalog_pid',
                    value       : this.objectId
                },{
                    xtype       : 'hidden',
                    name        : 'ReportCatalog_id'
                },{
                    name        : 'ReportCatalog_Name' ,
                    fieldLabel  : langs('Наименование')
                },
                    {
                        xtype  : 'combo',
                        store  : new Ext.data.SimpleStore({
                            fields : ['id','value'],
                            data   : [
                                [null, 'root'],
                                ['10' , langs('Карелия')],
                                ['19' , langs('Хакасия')],
                                ['30' , langs('Астрахань')],
                                ['60' , langs('Псков')],
                                ['63' , langs('Самара')],
                                ['64' , langs('Саратов')],
                                ['77' , langs('Москва')],
                                ['101' , langs('Казахстан')],
                                ['66' , langs('Екатеринбург')],
                                ['59' , langs('Пермь')],
                                ['1' , langs('Адыгея')],
                                ['2' , langs('Уфа')],
                                ['3' , langs('Бурятия')],
                                ['201' , langs('Беларусь')],
                                ['58' , langs('Пенза')],
                                ['40' , langs('Калуга')],
                                ['91' , langs('Крым')],
                                ['11' , langs('Сыктывкар')],
								['35' , langs('Вологда')],
								['50' , langs('Московская область')],
								['24' , langs('Красноярский край')],
                                ['26' , langs('Ставропольский край')],
                                ['76' , langs('Ярославль')],
								['12' , langs('Марий Эл')]
							]
						}),
						name        : 'Region_id' ,
						hiddenName  : 'Region_id' ,
						fieldLabel  : langs('Регион'),
						displayField: 'value',
						valueField  : 'id',
						triggerAction : 'all',
						mode        : 'local',
						editable    : false
					},
				{
                    xtype  : 'combo',
                    store  : new Ext.data.SimpleStore({
                        fields : ['id','value'],
                        data   : [
                        [0, langs('Видна всем')],
                        [1, langs('Только Минздрав')],
                        [2, langs('Только Администратор')]
                        ]
                    }),
                    value       : 1,
                    name        : 'ReportCatalog_Status',
                    hiddenName  : 'ReportCatalog_Status',
                    fieldLabel  : langs('Статус папки'),
                    displayField: 'value',
                    valueField  : 'id',
                    triggerAction : 'all',
                    mode        : 'local',
                    editable    : false
                },{
                    name        : 'ReportCatalog_Path' ,
                    fieldLabel  : langs('Путь к файлам отчета'),
                    allowBlank  : 'true'
                },{
                    name        : 'ReportCatalog_Position' ,
                    fieldLabel  : langs('Позиция') ,
                    allowBlank  : 'true'
                }]
            });

            return new sw.reports.designer.ui.BaseFormPanel({
                url   : sw.consts.url(sw.consts.actions.CRUD_REPORTCATALOG),
                height: 550,
                width: 580,
                formName: 'catalogEditForm',
                items : [
                    new Ext.TabPanel({
                        height  : 535,
                        layoutOnTabChange: true,
                        deferredRender : false,
                        //hideMode: 'offsets',
                        bodyStyle: 'background:transparent',
                        activeTab: 0,
                        items: [{
                            title  : lang['bazovyie_parametryi'],
                            layout : 'column',
                            bodyStyle:'background:transparent',
                            items:[baseTab]
                        },
                        {
                            title  : langs('Выбор региона'),
                            layout : 'column',
                            bodyStyle:'background:transparent',
                            items:[sw.reports.designer.ui.getRegionSelectPanel('REWC_RegionGrid')]
                        }]              
                    })
                ]
            });
        } else {
            var baseTab = new Ext.Panel({
                layout : 'form',
                labelAlign:'top',
                defaultType: 'textfield',
                defaults: {
                    width : 450,
                    blankText  : "Поле обязательно для заполнения",
                    allowBlank : false,
                    selectOnFocus : true
                },
                autoHeight : true,
                border: false,
                bodyStyle:'background:transparent;padding:10px;',
                items:[{
                    fieldLabel  : lang['roditel'],
                    disabled    : true,
                    value       : this.parentName
                },{
                    xtype       : 'hidden',
                    name        : 'ReportCatalog_id',
                    value       : this.objectId
                },{
                    xtype       : 'hidden',
                    name        : 'Report_id'
                },{
                    xtype       : 'textarea',
					maxlength   : 120,
                    height      : 50,
                    name        : 'Report_Caption',
					id          : 'R_Caption',
                    fieldLabel  : langs('Заголовок'),
					plugins     : [Ext.ux.plugins.RemoteValidator],
					rvOptions   : {
						url : sw.consts.url(sw.consts.actions.CHECK_UNIQUE_REPORT_CAPTION),
						params :
								{
									serverId : 1,
									ReportCatalog_id: this.objectId,
									Report_id: this.getSelected().id
								}
					},
					enableKeyEvents:true
                },{
                    xtype       : 'textarea',
                    height      : 50,
                    maxlength   : 200,
                    name        : 'Report_Title' ,
                    fieldLabel  : langs('Наименование'),
                    allowBlank  : 'true',
                    plugins     : [Ext.ux.plugins.RemoteValidator],
                    rvOptions   : {
                        url : sw.consts.url(sw.consts.actions.CHECK_REPORT_TITLE_LENGTH),
                        params :
                        {
                            serverId : 1,
                            ReportCatalog_id: this.objectId
                        }
                    },
                    enableKeyEvents:true
                },{
                    xtype       : 'textarea',
                    height      : 50,
                    maxlength   : 500,
                    name        : 'Report_Description' ,
                    fieldLabel  : langs('Описание'),
                    allowBlank  : 'true',
                    plugins     : [Ext.ux.plugins.RemoteValidator],
                    rvOptions   : {
                        url : sw.consts.url(sw.consts.actions.CHECK_REPORT_DESCRIPTION_LENGTH),
                        params :
                        {
                            serverId : 1,
                            ReportCatalog_id: this.objectId
                        }
                    },
                    enableKeyEvents:true
                },{
                    xtype  : 'combo',
                    store  : new Ext.data.SimpleStore({
                        fields : ['id','value'],
                        data   : [
                        [0, langs('Видна всем')],
                        [1, langs('Только Минздрав')],
                        [2, langs('Только Администратор')]
                        ]
                    }),
                    value       : 1,
                    name        : 'Report_Status' ,
                    hiddenName  : 'Report_Status' ,
                    fieldLabel  : langs('Статус'),
                    displayField: 'value',
                    valueField  : 'id',
                    triggerAction : 'all',
                    mode        : 'local',
                    editable    : false
                },
				{
					xtype  : 'combo',
					store  : new Ext.data.SimpleStore({
						fields : ['id','value'],
						data   : [
							[1, langs('Эталонный')],
							[2, langs('Региональный')],
							[3, langs('Временный')]
						]
					}),
					value       : '',
					name        : 'ReportType_id',
					hiddenName  : 'ReportType_id',
					fieldLabel  : langs('Тип отчета'),
					displayField: 'value',
					valueField  : 'id',
					triggerAction : 'all',
					mode        : 'local',
					editable    : true,
					allowBlank  : true
				},
				{
                    name        : 'Report_FileName' ,
                    fieldLabel  : langs('Имя файла'),
                    allowBlank  : 'true'
                },{
                    name        : 'Report_Position' ,
                    fieldLabel  : langs('Позиция'),
                    allowBlank  : 'true'
                },{
					xtype  : 'combo',
					store  : new Ext.data.SimpleStore({
						fields : ['id','value'],
						data   : [
							[1, 'Рабочая'],
							[2, 'Отчётная'],
							[3, 'Реестровая']
						]
					}),
					value       : '',
					name        : 'DatabaseType',
					hiddenName  : 'DatabaseType',
					fieldLabel  : 'БД для формирования отчёта',
					displayField: 'value',
					valueField  : 'id',
					triggerAction : 'all',
					mode        : 'local',
					editable    : true
				}]
            });
            return new sw.reports.designer.ui.BaseFormPanel({
                url   : sw.consts.url(sw.consts.actions.CRUD_REPORT),
                height: 580,
                width: 510,
                formName: 'reportEditForm',
                items : [
                    new Ext.TabPanel({
                        width: 350,
                        height: 565,
                        layoutOnTabChange: true,
                        deferredRender : false,
                        //hideMode: 'offsets',
                        bodyStyle: 'background:transparent',
                        activeTab: 0,
                        items: [{
                            title  : lang['bazovyie_parametryi'],
                            layout : 'column',
                            bodyStyle:'background:transparent',
                            items:[baseTab]
                        },
                        {
                            title  : langs('Выбор региона'),
                            layout : 'column',
                            bodyStyle:'background:transparent',
                            items:[sw.reports.designer.ui.getRegionSelectPanel('REWR_RegionGrid')]
                        }]              
                    })
                ]
            });
        }
    }
});

sw.reports.designer.ui.content.ReportView = Ext.extend(Ext.Panel,{
    isEditable   : true,
    isTestable   : true,
    title        : ' ',
    showTitle    : true,
    layout       : 'border',
    viewName     : 'ReportView',
    initComponent: function(config){
        sw.reports.designer.ui.content.ReportView.superclass.initComponent.call(this);
        this.addEvents('contentchange');
    },
    refreshContent : function(id){
        this.parentName = sw.reports.designer.ui.RepositoryPanel.getSelectedNode().text;
        this.setTitle(langs('Каталог отчетов - ') + this.parentName);
        this.fieldsetStore.baseParams.ownerId = this.objectId;
        this.paramStore.baseParams.ownerId = this.objectId;
        this.paramStore.baseParams.isFieldset = 0;
        this.paramStore.load({
            callback : function(){
                this.paramGrid.getSelectionModel().clearSelections();
                this.fireEvent('contentchange');
                if(id && this.currentGrid == this.paramGrid){
                    var index = this.paramGrid.getStore().indexOfId(id);
                    this.paramGrid.getSelectionModel().selectRow(index);
                    this.paramGrid.getView().focusRow(index);
                }
            },
            scope : this
        });
        this.fieldsetStore.load({
            callback : function(){
                this.fieldsetGrid.getSelectionModel().clearSelections();
                this.fireEvent('contentchange');
                if(id && this.currentGrid == this.fieldsetGrid){
                    var index = this.fieldsetGrid.getStore().indexOfId(id);
                    this.fieldsetGrid.getSelectionModel().selectRow(index);
                    this.fieldsetGrid.getView().focusRow(index);
                }
            },
            scope : this
        });
    },
    currentGrid  : null,
    setup : function(server,id,ownerId){
        this.objectId = id;
        this.server = server;
        this.ownerObjectId = ownerId;
        if(this.paramStore == null){
            this.paramStore = sw.reports.stores.ReportContentParameterTable.getInstance(server.get('id'));
            this.fieldsetStore = sw.reports.stores.ReportContentTable.getInstance(server.get('id'));
            this.fieldsetGrid = new Ext.grid.GridPanel({
                region : 'north',
                split  : true,
                height : 300,
                columns : [
                {
                    header : ' ',
                    width : 30 ,
                    renderer : function(value){
                        return '<img src="img/icons/rpt/reports.png" border="0">';
                    }
                },
                {
                    header : langs('Наименование'),
                    dataIndex : 'ReportContent_Name',
                    width : 300,
                    sortable : true
                },
                {
                    header : langs('Позиция'),
                    dataIndex : 'ReportContent_Position',
                    width : 100,
                    sortable : true
                }
                ],
                store   : this.fieldsetStore,
                listeners : {
                    'click' : function(event){
                        this.currentGrid = this.fieldsetGrid;
                    },
                    scope : this
                },
                sm : new Ext.grid.RowSelectionModel({
                    singleSelect : true,
                    listeners    : {
                        'selectionchange' : function(){
                            this.currentGrid = this.fieldsetGrid;
                            this.fireEvent('contentchange');
                        },
                        scope       : this
                    }
                })
            });
            this.paramGrid = new Ext.grid.GridPanel({
                region : 'center',
                columns : [
                {
                    header : ' ',
                    width : 30 ,
                    renderer : function(value){
                        return '<img src="img/icons/rpt/param.png" border="0">';
                    }
                },
                {
                    header : langs('Идентификатор'),
                    dataIndex : 'originalId',
                    width : 200,
                    sortable : true
                },
                {
                    header : langs('Метка'),
                    dataIndex : 'originalLabel',
                    width : 200,
                    sortable : true
                },
                {
                    header : langs('По умолчанию'),
                    dataIndex : 'originalDefault',
                    width : 100,
                    sortable : true
                },
                {
                    header : langs('Обязательный'),
                    dataIndex : 'ReportContentParameter_Required',
                    width : 100,
                    sortable : true,
                    renderer : function(value){
                        return [langs('Нет'),langs('Да')][value];
                    }
                },
                {
                    header : langs('Префикс'),
                    dataIndex : 'ReportContentParameter_PrefixText',
                    width : 100,
                    sortable : true
                },

                {
                    header : langs('ID Префикса'),
                    dataIndex : 'ReportContentParameter_PrefixId',
                    width : 100,
                    sortable : true
                },

                {
                    header : langs('Позиция'),
                    dataIndex : 'ReportContentParameter_Position',
                    width : 100,
                    sortable : true
                }
                ],
                store   : this.paramStore,
                listeners : {
                    'click' : function(event){
                        this.currentGrid = this.paramGrid;
                    },
                    scope : this
                },
                sm : new Ext.grid.RowSelectionModel({
                    singleSelect : true,
                    listeners    : {
                        'selectionchange' : function(){
                            this.currentGrid = this.paramGrid;
                            this.fireEvent('contentchange');
                        },
                        scope       : this
                    }
                })
            });
            this.add(this.fieldsetGrid);
            this.add(this.paramGrid);
            this.currentGrid = this.fieldsetGrid;
        }
        this.refreshContent();
    },
    getSelected : function(){
        if(this.currentGrid == null) return null;
        return this.currentGrid.getSelectionModel().getSelected();
    },
    deleteContent : function(callback,scope){
        var selected = this.getSelected();
        var url = '';
        var params = null;
        if(selected){
            if(selected.data.ReportContentParameter_id){
                url = sw.consts.url(sw.consts.actions.CRUD_CONTENTPARAMETER);
                params = {
                    __mode   : 'delete',
                    serverId : this.server.data.id,
                    ReportContentParameter_id : selected.data.ReportContentParameter_id
                };
            } else {
                url = sw.consts.url(sw.consts.actions.CRUD_CONTENT);
                params = {
                    __mode   : 'delete',
                    serverId : this.server.data.id,
                    ReportContent_id : selected.data.ReportContent_id
                };
            }
            Ext.Ajax.request({
                url    : url,
                params : params,
                success: function(){
                    if(callback) callback.call(scope || this);
                }
            });
        }
    },
    getForm : function(state){
        var win = this;
        if( state == 'addParameterGroup' || ((!state || state == 'edit') && this.currentGrid == this.fieldsetGrid) ){
            return new sw.reports.designer.ui.BaseFormPanel({
                url   : sw.consts.url(sw.consts.actions.CRUD_CONTENT),
                items : [
                {
                    xtype       : 'hidden',
                    name        : 'ReportContent_id'
                },
                {
                    xtype       : 'hidden',
                    name        : 'Report_id',
                    value       : this.objectId
                },
                {
                    name        : 'ReportContent_Name' ,
                    fieldLabel  : langs('Наименование')
                },
                {
                    fieldLabel  : langs('Позиция'),
		    maskRe	: /[1-9]/i,
                    name        : 'ReportContent_Position'
                }
                ]
            });
        } else {
            var baseTab = new Ext.Panel({
                layout : 'form',
                labelAlign:'top',
                defaultType: 'textfield',
                autoHeight : true,
                defaults: {
                    width : 450,
                    blankText  : "Поле обязательно для заполнения",
                    allowBlank : false,
                    selectOnFocus : true
                },
                border: false,
                bodyStyle:'background:transparent;padding:10px;',
                items: [{
                    xtype       : 'hidden',
                    name        : 'ReportContentParameter_id'
                },
                {
                    xtype       : 'combo',
                    fieldLabel  : langs('Базовый параметр'),
                    mode        : 'remote',
                    triggerAction : 'all',
                    name        : 'ReportParameter_id',
                    hiddenName  : 'ReportParameter_id',
                    minChars    : 1,
                    store       : new Ext.data.JsonStore({
                        autoLoad   : true,
                        baseParams : {
                            serverId :  this.server.data.id,
                            reportId :  this.currentGrid.store.baseParams.ownerId
                        },
                        url     :  sw.consts.url(sw.consts.actions.COMBO_PARAMETERS),
                        root    : 'items',
                        fields  : ['ReportParameter_id','ReportParameter_Name','ReportParameter_Label']
                    }),
                    tpl         : '<tpl for="."><div class="x-combo-list-item">{ReportParameter_Label} <span style="color:gray">{ReportParameter_Name}</span></div></tpl>',
                    displayField : 'ReportParameter_Name',
                    valueField  : 'ReportParameter_id',
                    forceSelection : true,
                    setValue : function(v){
                        if (this.store.getCount() == 0) {
                            this.store.on('load',
                                this.setValue.createDelegate(this, [v]), null, {
                                    single: true
                                });
                            return;
                        }
                        var text = v;
                        if(this.valueField){
                            var r = this.findRecord(this.valueField, v);
                            if(r){
                                text = r.data[this.displayField];
                            }else if(this.valueNotFoundText !== undefined){
                                text = this.valueNotFoundText;
                            }
                        }
                        this.lastSelectionText = text;
                        if(this.hiddenField){
                            this.hiddenField.value = v;
                        }
                        Ext.form.ComboBox.superclass.setValue.call(this, text);
                        this.value = v;
                    }
                },
                {
                    xtype       : 'hidden',
                    name        : 'Report_id',
                    value       : this.objectId
                },
                {
                    xtype       : 'hidden',
                    name        : 'ReportContent_id'
                },
                {
                    fieldLabel  : langs('Позиция'),
		    maskRe	: /[1-9]/i,
                    name        : 'ReportContentParameter_Position'
                },
                {
                    name        : 'ReportContentParameter_ReportLabel' ,
                    fieldLabel  : langs('Метка в отчете'),
                    allowBlank  : true
                },
                {
                    name        : 'ReportContentParameter_Default' ,
                    fieldLabel  : langs('Значение по умолчанию'),
                    allowBlank  : true
                },
                {
                    name        : 'ReportContentParameter_ReportId' ,
                    fieldLabel  : langs('Идентификатор'),
                    allowBlank  : true
                },
                {
                    name        : 'ReportContentParameter_PrefixId' ,
                    fieldLabel  : langs('Значение префикса'),
                    allowBlank  : true
                },
                {
                    name        : 'ReportContentParameter_PrefixText' ,
                    fieldLabel  : langs('Текст префикса'),
                    allowBlank  : true
                },
                {
                    xtype       : 'checkbox',
                    name        : 'ReportContentParameter_Required' ,
                    fieldLabel  : langs('Обязательно для заполнения'),
                    originalValue : false
                }
                ]
            });
            var sqlTab = new Ext.Panel({               
                id       : 'REWP_sqledittab',
                layout   : 'fit',
                width    : 500,
                height   : 300,
                server   : this.server,
                initComponent : function(){

                    this.queryEditor = new Ext.ux.form.EditArea({
                        region      : 'center',
                        xtype       : 'ux-editarea',
                        id          : 'REWP_Parameter_SQL_editor',
                        height      : 120,
                        fieldLabel  : 'SQL',
                        allowBlank  : 'true',
                        syntax      : 'sql',
                        allow_toggle: false,
                        toolbar     : ' '
                    });
                    this.toolbar = new Ext.Toolbar({
                        items : [
                        new Ext.Button({
                            text : lang['parsing_i_proverka'],
                            iconCls : 'rpt-parse',
                            scope : this,
                            handler : function(){
                                var sqlField = this.queryEditor.getValue();
                                //var resultTab = this.ownerCt.findById('resulttab');
                                //var Region_id = this.ownerCt.findById('baseParamsTab').items.items[1].items.items[4].getValue();
                                var Region_id = null;
                                if(sqlField){
                                    var lm = new Ext.LoadMask(this.ownerCt.getEl(),{
                                        msg : lang['idet_proverka_pravilnosti_zaprosa']
                                    });
                                    lm.show();
                                    sw.ParamFactory.checkSql(this.server.data.id,Region_id,sqlField,(function(error,params,data){
                                        lm.hide();
                                        lm.destroy();

                                        if(error || typeof data != 'object' ){
                                            if (error) {
                                                Ext.Msg.alert(lang['oshibka_proverki_zaprosa'],error); 
                                            }
                                            
                                        } else {
                                            this.findById('REWP_ReportContentParameter_SQL').setValue(this.queryEditor.getValue());
                                            Ext.Msg.alert(langs('Сообщение'), langs('Проверка прошла успешно'));
                                            // resultTab.setData(params,data);
                                            // resultTab.ownerCt.setActiveTab(resultTab);
                                        }
                                    }).createDelegate(this));
                                }
                            }
                        })
                        ]
                    });
                    Ext.apply(this,{
                        items : [
                        {
                            layout : 'fit',
                            region : 'center',
                            id: 'REWP_sqlEditorContainer',
                            tbar   : this.toolbar,
                            items  : [
                                //this.queryEditor
                            ]
                        },
                        {
                            xtype : 'hidden',
                            id    : 'REWP_ReportContentParameter_SQL',
                            name  : 'ReportContentParameter_SQL'

                        }]
                    });
                    this.constructor.superclass.initComponent.call(this);
                }
            });

            return new sw.reports.designer.ui.BaseFormPanel({
            url   : sw.consts.url(sw.consts.actions.CRUD_CONTENTPARAMETER),
            formName: 'contentParamEditForm',
            items : [
                new Ext.TabPanel({
                    height  : 535,
                    layoutOnTabChange: true,
                    deferredRender : false,
                    //hideMode: 'offsets',
                    bodyStyle: 'background:transparent',
                    activeTab: 0,
                    items: [{
                        title  : lang['bazovyie_parametryi'],
                        layout : 'column',
                        bodyStyle:'background:transparent',
                        items:[baseTab]
                    },
                    {
                        title  : langs('Выбор регионов'),
                        layout : 'column',
                        bodyStyle:'background:transparent',
                        items:[sw.reports.designer.ui.getRegionSelectPanel('REWP_RegionGrid')]
                    },
                    {
                        title: 'SQL',
                        layout: 'column',
                        bodyStyle:'background:transparent;',
                        listeners: {
                            activate: function () {
                                var sqlPanel = Ext.getCmp('REWP_sqledittab');
                                var editorCont = Ext.getCmp('REWP_sqlEditorContainer');
                                if(sqlPanel && sqlPanel.queryEditor && editorCont && editorCont.items.getCount() == 0){
                                    editorCont.add(sqlPanel.queryEditor);
                                    editorCont.doLayout();
                                    sqlPanel.queryEditor.setValue(this.findById('REWP_ReportContentParameter_SQL').getValue());
                                }
                                sqlPanel.queryEditor.show.defer(200,sqlPanel.queryEditor);
                            },
                            beforehide: function () {
                                var sqlTab = Ext.getCmp('REWP_sqledittab');
                                if (sqlTab && sqlTab.queryEditor && typeof sqlTab.queryEditor.hide == 'function') {
                                    sqlTab.queryEditor.hide();
                                }
                            }
                        },
                        items: [{
                            xtype: 'fieldset',
                            autoHeight: true,
                            layout: 'column',
                            title: langs('Параметры'),
                            items: [{
                                region : 'center',
                                layout : 'form',
                                labelAlign:'top',
                                defaultType: 'textfield',
                                autoHeight : true,
                                defaults: {
                                    width : 300,
                                    blankText  : "Поле обязательно для заполнения",
                                    allowBlank : true,
                                    selectOnFocus : true
                                },
                                border: false,
                                bodyStyle:'background:transparent;padding:10px;',
                                items    : [{
                                    name        : 'ReportContentParameter_SQLIdField' ,
                                    id          : 'ReportContentParameter_SQLIdField' ,
                                    fieldLabel  : lang['imya_stolbtsa_dlya_klyucha']
                                },
                                {
                                    name        : 'ReportContentParameter_SQLTextField' ,
                                    id          : 'ReportContentParameter_SQLTextField' ,
                                    fieldLabel  : lang['imya_stolbtsa_dlya_teksta']
                                }]
                            }]
                        },
                        sqlTab
                        ]
                    }]
                })
            ]});       
        }
    }
});


sw.reports.designer.ui.content.FieldsetView = Ext.extend(Ext.Panel,{
    isEditable   : true,
    isTestable   : true,
    title        : ' ',
    showTitle    : true,
    layout       : 'border',
    viewName     : 'FieldsetView',
    initComponent: function(config){
        sw.reports.designer.ui.content.ReportView.superclass.initComponent.call(this);
        this.addEvents('contentchange');
    },
    refreshContent : function(id){
        var node = sw.reports.designer.ui.RepositoryPanel.getSelectedNode();
        this.parentName = node.parentNode.text + ' - ' + node.text;
        this.setTitle(langs('Каталог отчетов - ') + this.parentName);
        this.paramStore.baseParams.ownerId = this.objectId;
        this.paramStore.baseParams.isFieldset = 1;
        this.paramStore.load({
            callback : function(){
                this.paramGrid.getSelectionModel().clearSelections();
                this.fireEvent('contentchange');
                if(id && this.currentGrid == this.paramGrid){
                    var index = this.paramGrid.getStore().indexOfId(id);
                    this.paramGrid.getSelectionModel().selectRow(index);
                    this.paramGrid.getView().focusRow(index);
                }
            },
            scope : this
        });
    },
    setup : function(server,id,ownerId){
        this.objectId = id;
        this.server = server;
        this.ownerObjectId = ownerId;
        if(this.paramStore == null){
            this.paramStore = sw.reports.stores.ReportContentParameterTable.getInstance(server.get('id'));
            this.paramGrid = new Ext.grid.GridPanel({
                region : 'center',
                columns : [
                {
                    header : ' ',
                    width : 30 ,
                    renderer : function(value){
                        return '<img src="img/icons/rpt/param.png" border="0">';
                    }
                },
                {
                    header : langs('Идентификатор'),
                    dataIndex : 'originalId',
                    width : 200,
                    sortable : true
                },
                {
                    header : langs('Метка'),
                    dataIndex : 'originalLabel',
                    width : 200,
                    sortable : true
                },
                {
                    header : langs('По умолчанию'),
                    dataIndex : 'originalDefault',
                    width : 100,
                    sortable : true
                },
                {
                    header : langs('Обязательный'),
                    dataIndex : 'ReportContentParameter_Required',
                    width : 100,
                    sortable : true,
                    renderer : function(value){
                        return [langs('Нет'),langs('Да')][value];
                    }
                },
                {
                    header : langs('Префикс'),
                    dataIndex : 'ReportContentParameter_PrefixText',
                    width : 100,
                    sortable : true
                },

                {
                    header : langs('ID Префикса'),
                    dataIndex : 'ReportContentParameter_PrefixId',
                    width : 100,
                    sortable : true
                },

                {
                    header : langs('Позиция'),
                    dataIndex : 'ReportContentParameter_Position',
                    width : 100,
                    sortable : true
                }
                ],
                store   : this.paramStore,
                listeners : {
                    'click' : function(event){
                        this.currentGrid = this.paramGrid;
                    },
                    scope : this
                },
                sm : new Ext.grid.RowSelectionModel({
                    singleSelect : true,
                    listeners    : {
                        'selectionchange' : function(){
                            this.currentGrid = this.paramGrid;
                            this.fireEvent('contentchange');
                        },
                        scope       : this
                    }
                })
            });
            this.currentGrid = this.paramGrid;
            this.add(this.paramGrid);
        }
        this.refreshContent();
    },
    getSelected : function(){
        if(this.paramGrid == null) return null;
        return this.paramGrid.getSelectionModel().getSelected();
    },
    deleteContent : function(callback,scope){
        var selected = this.getSelected();
        var url = '';
        var params = null;
        if(selected){
            url = sw.consts.url(sw.consts.actions.CRUD_CONTENTPARAMETER);
            params = {
                __mode   : 'delete',
                serverId : this.server.data.id,
                ReportContentParameter_id : selected.data.ReportContentParameter_id
            };
            Ext.Ajax.request({
                url    : url,
                params : params,
                success: function(){
                    if(callback) callback.call(scope || this);
                }
            });
        }
    },
    getForm : function(state){

        var baseTab = new Ext.Panel({
            layout : 'form',
            labelAlign:'top',
            defaultType: 'textfield',
            autoHeight : true,
            defaults: {
                width : 450,
                blankText  : "Поле обязательно для заполнения",
                allowBlank : false,
                selectOnFocus : true
            },
            border: false,
            bodyStyle:'background:transparent;padding:10px;',
            items: [{
                xtype       : 'hidden',
                name        : 'ReportContentParameter_id'
            },
            {
                xtype       : 'combo',
                fieldLabel  : langs('Базовый параметр'),
                mode        : 'remote',
                triggerAction : 'all',
                name        : 'ReportParameter_id',
                hiddenName  : 'ReportParameter_id',
                minChars    : 1,
                store       : new Ext.data.JsonStore({
                    autoLoad   : true,
                    baseParams : {
                        serverId :  this.server.data.id,
                        reportContentId :  this.currentGrid.store.baseParams.ownerId
                    },
                    url     :  sw.consts.url(sw.consts.actions.COMBO_PARAMETERS),
                    root    : 'items',
                    fields  : ['ReportParameter_id','ReportParameter_Name','ReportParameter_Label']
                }),
                tpl         : '<tpl for="."><div class="x-combo-list-item">{ReportParameter_Label} <span style="color:gray">{ReportParameter_Name}</span></div></tpl>',
                displayField : 'ReportParameter_Name',
                valueField  : 'ReportParameter_id',
                forceSelection : true,
                setValue : function(v){
                    if (this.store.getCount() == 0) {
                        this.store.on('load',
                            this.setValue.createDelegate(this, [v]), null, {
                                single: true
                            });
                        return;
                    }
                    var text = v;
                    if(this.valueField){
                        var r = this.findRecord(this.valueField, v);
                        if(r){
                            text = r.data[this.displayField];
                        }else if(this.valueNotFoundText !== undefined){
                            text = this.valueNotFoundText;
                        }
                    }
                    this.lastSelectionText = text;
                    if(this.hiddenField){
                        this.hiddenField.value = v;
                    }
                    Ext.form.ComboBox.superclass.setValue.call(this, text);
                    this.value = v;
                }
            },
            {
                xtype       : 'hidden',
                name        : 'Report_id'
            },
            {
                xtype       : 'hidden',
                name        : 'ReportContent_id',
                value       : this.objectId
            },
            {
                fieldLabel  : langs('Позиция'),
		maskRe	    : /[1-9]/i,
                name        : 'ReportContentParameter_Position'
            },
            {
                name        : 'ReportContentParameter_ReportLabel' ,
                fieldLabel  : langs('Метка в отчете'),
                allowBlank  : true
            },
            {
                name        : 'ReportContentParameter_Default' ,
                fieldLabel  : langs('Значение по умолчанию'),
                allowBlank  : true
            },
            {
                name        : 'ReportContentParameter_ReportId' ,
                fieldLabel  : langs('Идентификатор'),
                allowBlank  : true
            },
            {
                name        : 'ReportContentParameter_PrefixId' ,
                fieldLabel  : langs('Значение префикса'),
                allowBlank  : true
            },
            {
                name        : 'ReportContentParameter_PrefixText' ,
                fieldLabel  : langs('Текст префикса'),
                allowBlank  : true
            },
            {
                xtype       : 'checkbox',
                name        : 'ReportContentParameter_Required' ,
                fieldLabel  : langs('Обязательно для заполнения'),
                originalValue : false
            }
            ]
        });
        var sqlTab = new Ext.Panel({               
            id       : 'REWP2_sqledittab',
            layout   : 'fit',
            width    : 500,
            height   : 300,
            server   : this.server,
            initComponent : function(){

                this.queryEditor = new Ext.ux.form.EditArea({
                    region      : 'center',
                    xtype       : 'ux-editarea',
                    id          : 'REWP2_Parameter_SQL_editor',
                    height      : 120,
                    fieldLabel  : 'SQL',
                    allowBlank  : 'true',
                    syntax      : 'sql',
                    allow_toggle: false,
                    toolbar     : ' '
                });
                this.toolbar = new Ext.Toolbar({
                    items : [
                    new Ext.Button({
                        text : lang['parsing_i_proverka'],
                        iconCls : 'rpt-parse',
                        scope : this,
                        handler : function(){
                            var sqlField = this.queryEditor.getValue();
                            //var resultTab = this.ownerCt.findById('resulttab');
                            //var Region_id = this.ownerCt.findById('baseParamsTab').items.items[1].items.items[4].getValue();
                            var Region_id = null;
                            if(sqlField){
                                var lm = new Ext.LoadMask(this.ownerCt.getEl(),{
                                    msg : lang['idet_proverka_pravilnosti_zaprosa']
                                });
                                lm.show();
                                sw.ParamFactory.checkSql(this.server.data.id,Region_id,sqlField,(function(error,params,data){
                                    lm.hide();
                                    lm.destroy();

                                    if(error || typeof data != 'object' ){
                                        if (error) {
                                            Ext.Msg.alert(lang['oshibka_proverki_zaprosa'],error); 
                                        }
                                        
                                    } else {
                                        this.findById('REWP2_ReportContentParameter_SQL').setValue(this.queryEditor.getValue());
                                        Ext.Msg.alert(langs('Сообщение'), langs('Проверка прошла успешно'));
                                        // resultTab.setData(params,data);
                                        // resultTab.ownerCt.setActiveTab(resultTab);
                                    }
                                }).createDelegate(this));
                            }
                        }
                    })
                    ]
                });
                Ext.apply(this,{
                    items : [
                    {
                        layout : 'fit',
                        region : 'center',
                        id: 'REWP2_sqlEditorContainer',
                        tbar   : this.toolbar,
                        items  : [
                            //this.queryEditor
                        ]
                    },
                    {
                        xtype : 'hidden',
                        id    : 'REWP2_ReportContentParameter_SQL',
                        name  : 'ReportContentParameter_SQL'

                    }]
                });
                this.constructor.superclass.initComponent.call(this);
            }
        });

        return new sw.reports.designer.ui.BaseFormPanel({
        url   : sw.consts.url(sw.consts.actions.CRUD_CONTENTPARAMETER),
        formName: 'contentParamEditForm2',
        items : [
            new Ext.TabPanel({
                height  : 535,
                layoutOnTabChange: true,
                deferredRender : false,
                //hideMode: 'offsets',
                bodyStyle: 'background:transparent',
                activeTab: 0,
                items: [{
                    title  : lang['bazovyie_parametryi'],
                    layout : 'column',
                    bodyStyle:'background:transparent',
                    items:[baseTab]
                },
                {
                    title  : langs('Выбор регионов'),
                    layout : 'column',
                    bodyStyle:'background:transparent',
                    items:[sw.reports.designer.ui.getRegionSelectPanel('REWP2_RegionGrid')]
                },
                {
                    title: 'SQL',
                    layout: 'column',
                    bodyStyle:'background:transparent;',
                    listeners: {
                        activate: function () {
                            var sqlPanel = Ext.getCmp('REWP2_sqledittab');
                            var editorCont = Ext.getCmp('REWP2_sqlEditorContainer');
                            if(sqlPanel && sqlPanel.queryEditor && editorCont && editorCont.items.getCount() == 0){
                                editorCont.add(sqlPanel.queryEditor);
                                editorCont.doLayout();
                                sqlPanel.queryEditor.setValue(this.findById('REWP2_ReportContentParameter_SQL').getValue());
                            }
                            sqlPanel.queryEditor.show.defer(200,sqlPanel.queryEditor);
                        },
                        beforehide: function () {
                            var sqlTab = Ext.getCmp('REWP2_sqledittab');
                            if (sqlTab && sqlTab.queryEditor && typeof sqlTab.queryEditor.hide == 'function') {
                                sqlTab.queryEditor.hide();
                            }
                        }
                    },
                    items: [{
                        xtype: 'fieldset',
                        autoHeight: true,
                        layout: 'column',
                        title: langs('Параметры'),
                        items: [{
                            region : 'center',
                            layout : 'form',
                            labelAlign:'top',
                            defaultType: 'textfield',
                            autoHeight : true,
                            defaults: {
                                width : 300,
                                blankText  : "Поле обязательно для заполнения",
                                allowBlank : true,
                                selectOnFocus : true
                            },
                            border: false,
                            bodyStyle:'background:transparent;padding:10px;',
                            items    : [{
                                name        : 'ReportContentParameter_SQLIdField' ,
                                id          : 'ReportContentParameter_SQLIdField' ,
                                fieldLabel  : lang['imya_stolbtsa_dlya_klyucha']
                            },
                            {
                                name        : 'ReportContentParameter_SQLTextField' ,
                                id          : 'ReportContentParameter_SQLTextField' ,
                                fieldLabel  : lang['imya_stolbtsa_dlya_teksta']
                            }]
                        }]
                    },
                    sqlTab
                    ]
                }]
            })
        ]});
    }
});

