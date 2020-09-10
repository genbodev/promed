/**
 * swComponentLibMorbusPanel - Панель для ведения заболеваний
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      libs
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       gabdushev
 * @version      25-11-2011
 */
sw.Promed.MorbusPanel = Ext.extend(sw.Promed.Panel, {
    layout: 'border',
    id: 'MorbusPanel',
    listeners:{
        'expand':function (p) {
            //p.load();//todo
        }
    },
    loadGrid: function () {
        var params = this.loadparams;
        var loadParams;
        this.Evn_id = null;
        this.Person_id = null;
        this.Diag_id = null;
        if (params.Evn_id) {
            this.Evn_id = params.Evn_id;
            loadParams = {params:{Evn_id:params.Evn_id}}
        } else {
            if (params.Person_id && params.Diag_id) {
                this.Person_id = params.Person_id;
                this.Diag_id = params.Diag_id;
                loadParams = {params:{Person_id:params.Person_id, Diag_id:params.Diag_id}}
            } else {
                sw.swMsg.alert(langs('неправильные параметры: необходимо указать либо учетный документ, либо человека и диагноз'));
                return false;
            }
        }
        this.MorbusGrid.getGrid().getStore().load(loadParams);
        return true;
    },
    load:function (params) {
        this.loadparams = params;
        if (!this.loadGrid()){
            return false;
        }
        //каллбэк вызывается после сохранения заболевания
        this.callbackAfterSave = function (){};
        if (params.callbackAfterSave) {
            this.callbackAfterSave = params.callbackAfterSave;
        }
        var thisWin = this;
        if (this.Evn_id) {
            //определение MorbusType
            //todo допилить чтобы можно было определять и по диагнозу
            Ext.Ajax.request({
                failure: function(response, options) {
                    //todo выводить нормальное окно с ошибкой
                    sw.swMsg.alert(langs('Ошибка при определении типа заболевания'));
                },
                params: {
                    Evn_id: this.Evn_id
                },
                success: function(response, options) {
                    var ob = Ext.util.JSON.decode(response.responseText);
                    thisWin.MorbusType_id = ob[0].MorbusType_id;
                },
                url: '/?c=Morbus&m=detectMorbusType'
            });
        }
    },
    callbackAfterSave: function (){},
    initComponent:function () {
        //todo
        //...
        var thisPanel = this;
        this.MorbusGrid = new sw.Promed.ViewFrame({
			border: false,
			noFocusOnLoad: this.noFocusOnLoad,
            id:this.id + 'MorbusGrid',
            actions:[
                {
                    name:'action_add',
                    handler:function () {
                        var params = new Object();
                        params.action = 'add';
                        params.Evn_pid = thisPanel.Evn_id;
                        params.Person_id = thisPanel.Person_id;
                        params.Diag_id = thisPanel.Diag_id;
                        params.MorbusType_id = thisPanel.MorbusType_id;
                        params.callbackAfterSave = function (){
                            var args = arguments;
                            thisPanel.callbackAfterSave(args);
                            thisPanel.loadGrid();
                        }
                        getWnd('swMorbusWindow').show(params);
                    }
                },
                {
                    name:'action_edit',
                    handler:function () {
                        var r = thisPanel.MorbusGrid.getGrid().getSelectionModel().getSelected();
                        if (r) {
                            var params = new Object();
                            params.action = 'edit';
                            params.Evn_pid = thisPanel.Evn_id;
                            params.MorbusType_id = thisPanel.MorbusType_id;
                            params.Morbus_id = r.data.Morbus_id;
                            params.callbackAfterSave = function (){
                                var args = arguments;
                                thisPanel.callbackAfterSave(args);
                                thisPanel.loadGrid();
                            }
                            getWnd('swMorbusWindow').show(params);
                        }
                    }
                },
                {
                    name:'action_view',
                    handler:function () {
                        var r = thisPanel.MorbusGrid.getGrid().getSelectionModel().getSelected();
                        if (r) {
                            var params = new Object();
                            params.action = 'view';
                            params.Morbus_id = r.data.Morbus_id;
                            getWnd('swMorbusWindow').show(params);
                        }
                    }
                },
                {
                    name:'action_delete',
                    handler:function () {
                        var r = thisPanel.MorbusGrid.getGrid().getSelectionModel().getSelected();
                        if (r) {
                            if (r.data.Deletable == 'true') {
                                Ext.Ajax.request({
                                    failure: function(response, options) {
                                        sw.swMsg.alert(langs('Ошибка') + response.responseText);
                                    },
                                    params: {
                                        Evn_id: thisPanel.Evn_id,
                                        Morbus_id: r.data.Morbus_id
                                    },
                                    success: function(response, options) {
                                        var json = Ext.util.JSON.decode(response.responseText);
                                        if (json.success) {
                                            thisPanel.loadGrid();
                                        } else {
                                            sw.swMsg.alert(langs('Ошибка') + response.responseText);
                                        }
                                    },
                                    url: '/?c=Morbus&m=delete'
                                });
                            } else {
                                sw.swMsg.alert(langs('Ошибка удаления заболевания'), langs('Данное заболение невозможно удалить, поскольку оно используется в других учетных документах'));
                            }
                        }
                    }
                }

            ],
            autoExpandColumn:'autoexpand',
            stringfields:[
                {
                    name:'Morbus_id',
                    type:'int',
                    header:'ID',
                    key:true
                },
                {
                    name:'diag_FullName',
                    type:'string',
                    header:langs('Диагноз'),
					width: 300
                },
                {
                    name:'Morbus_setDT',
                    type:'date',
                    header:langs('Дата начала'),
                    renderer:Ext.util.Format.dateRenderer('d.m.Y')
                },
                {
                    name:'Morbus_disDT',
                    type:'date',
                    header:langs('Дата окончания'),
                    renderer:Ext.util.Format.dateRenderer('d.m.Y')
                },
                {
                    name:'MorbusResult_Name',
                    type:'string',
                    header:langs('Исход'),
					id: 'autoexpand'
                },
                {
                    name:'Editable',
                    header:langs('Доступно для редактирования'),
                    width:150,
                    type:'checkbox'
                },
                {
                    name:'Deletable',
                    header:langs('Доступно для удаления'),
                    width:150,
                    hidden: true
                }
            ],
            toolbar:true,
			region: 'center',
            dataUrl:"/?c=Morbus&m=loadMorbusList",
            autoLoadData:false
        });
        Ext.apply(this, {
            height: 180,
            border:true,
            layout:'form',
            listeners:{
                resize:function (p, nW, nH, oW, oH) {
                    p.doLayout();
                }
            },
            title:langs('Заболевания'),
            labelWidth:200,
            items:[
                this.MorbusGrid
            ]

        });
        sw.Promed.MorbusPanel.superclass.initComponent.apply(this, arguments);
    }
});