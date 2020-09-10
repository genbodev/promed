/**
 * Выгрузка результатов SQL-запросов в DBF
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @autor        Dmitry Storozhev aka nekto_O
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @version      16.12.2011
 */
sw.Promed.swQueryToDbfExporterWindow = Ext.extend(sw.Promed.BaseForm, {
    title:lang['eksport_dannyih_v_dbase_*_dbf'],
    modal:true,
    height: 430,
    width:600,
    shim:false,
    plain:true,
    resizable:false,
    onSelect:Ext.emptyFn,
    layout:'fit',
    buttonAlign:"right",
    objectName:'swQueryToDbfExporterWindow',
    closeAction:'hide',
    id:'swQueryToDbfExporterWindow',
    objectSrc:'/jscore/Forms/Hospital/swQueryToDbfExporterWindow.js',
    onQueryChecked: function (){
        var anyChecked = false;
        var queries = this.findById('swQueryToDbfExporterForm').items.items;
        for(var i = 0; i<queries.length;i++) {
            if (queries[i].checked) {
                anyChecked = true;
                break;
            }
        }
        if (anyChecked) {
            this.buttons[0].enable();
        } else {
            this.buttons[0].disable();
        }
    },
    prepareExport: function (callback){
        var that = this;
        that.getLoadMask(lang['podgotovka_k_eksportu']).show();
        Ext.Ajax.request({
            callback: function (options, success, response){
                if (success) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (response_obj.success){
                        that.getLoadMask().hide();
                        callback(response_obj);
                    } else {
                        var err = '';
                        if (response_obj.Error_Msg) {
                            err = ' (' + response_obj.Error_Msg + ')';
                        }
                        sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_podgotovke_k_eksportu'] + err);
                        that.getLoadMask().hide();
                    }
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_podgotovke_k_eksportu_nepravilnyiy_otvet_servera']);
                    that.getLoadMask().hide();
                }
            },
            url:'/?c=QueryToDbfExporter&m=reset',
            method: 'post'
        });
    },
    runExport: function (queryNumber, callback){
        if (undefined == queryNumber) {
            queryNumber = 0;
        }
        var that = this;
        var queries = this.findById('swQueryToDbfExporterForm').items.items;
        if ((queries.length) > queryNumber) {
            if (queries[queryNumber].checked) {
                var query = {
                    query_name: queries[queryNumber].boxLabel,
                    query_nick: queries[queryNumber].name
                };
                that.getLoadMask(lang['vyigruzka_obyekta'] + query.query_name).show();
                Ext.Ajax.request({
                    callback:function (options, success, response) {
                        if (success) {
                            that.getLoadMask().hide();
                            if (response.responseText.length > 0){
                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                if (response_obj.success){
                                    that.runExport(queryNumber + 1, callback);
                                } else {
                                    var err = '';
                                    if (response_obj.Error_Msg) {
                                        err = ' (' + response_obj.Error_Msg + ')';
                                    }
                                    sw.swMsg.alert('Ошибка', 'Ошибка выполнения выгрузки объекта "' + query.query_name + '"' + err);
                                }
                            } else {
                                sw.swMsg.alert(lang['oshibka'], lang['pustoy_otvet_servera_obratites_k_razrabotchikam']);
                            }
                        }
                        else {
                            sw.swMsg.alert('Ошибка', 'Ошибка выполнения выгрузки объекта "' + query.query_name + '" (неправильный ответ сервера)');
                            that.getLoadMask().hide();
                        }
                    },
                    params: {
                        query_nick: query.query_nick
                    },
                    url:'/?c=QueryToDbfExporter&m=exportQuery'
                });
            } else {
                that.runExport(queryNumber + 1, callback);
            }
        } else {
            that.getLoadMask().hide();
            callback();
        }
    },
    packResult: function (callback){
        var that = this;
        that.getLoadMask(lang['arhivatsiya_eskportirovannyih_dannyih']).show();
        Ext.Ajax.request({
            callback: function (options, success, response){
                if (success) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (response_obj.success){
                        that.getLoadMask().hide();
                        callback(response_obj);
                    } else {
                        var err = '';
                        if (response_obj.Error_Msg) {
                            err = ' (' + response_obj.Error_Msg + ')';
                        }
                        sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_arhivatsii'] + err);
                        that.getLoadMask().hide();
                    }
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_arhivatsii_nepravilnyiy_otvet_servera']);
                    that.getLoadMask().hide();
                }
            },
            url:'/?c=QueryToDbfExporter&m=packResult',
            method: 'post'
        });
    },
    showLink: function (resp_obj){
        sw.swMsg.alert('Завершено', 'Экспорт успешно завершен. <a href="'+resp_obj.filename+'" target="blank" title="Щелкните, чтобы сохранить результаты на локальный диск">Скачать</a>');
    },
    buttons:[
        {
            handler:function () {
                var that = this.ownerCt;
                that.prepareExport(function () {
                    that.runExport(0, function (){
                        that.packResult(function (resp_obj){
                            that.showLink(resp_obj);
                        });
                    });
                });
            },
            iconCls:'ok16',
            text:lang['vyigruzit_v_dbf']
        },
        '-',
        {
            text:BTN_FRMCANCEL,
            tabIndex:-1,
            tooltip:BTN_FRMCANCEL,
            iconCls:'cancel16',
            handler:function () {
                this.ownerCt.hide();
            }
        }
    ],
    show:function () {
        var that = this;
        sw.Promed.swQueryToDbfExporterWindow.superclass.show.apply(this, arguments);
        this.getLoadMask(lang['poluchenie_spiska_dostupnyih_zaprosov']).show();
        var form = that.findById('swQueryToDbfExporterForm');
        form.removeAll();
        Ext.Ajax.request({
            callback:function (options, success, response) {
                that.getLoadMask().hide();
                if (success) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    for (var i = 0; i < response_obj.length; i++) {
                        form.add(new Ext.form.Checkbox({
                            labelSeparator: '',
                            boxLabel: response_obj[i].query_name,
                            name: response_obj[i].query_nick,
                            checked: true,
                            handler: function () {
                                that.onQueryChecked();
                            }
                        }))
                    }
	                form.doLayout();
                }
                else {
                    sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poluchenii_spiska_dostupnyih_dlya_vyigruzki_obyektov']);
                }
            },
            url:'/?c=QueryToDbfExporter&m=getQueryList'
        });
        this.syncSize();
        this.center();
    },
    initComponent:function () {
        Ext.apply(this, {
            bodyStyle:'padding: 5px;',
            items:[
                {
                    xtype: 'label',
                    text: lang['vyiberite_obyektyi_dlya_eksporta'],
                    style: 'font-size: 12px; padding-bottom:10px;'
                },
                {
                    id: 'swQueryToDbfExporterForm',
                    xtype: 'form',
                    overflow: 'scroll',
                    scroll: 'auto',
                    layout: 'form',
                    frame: true,
                    labelWidth: 1,
                    items: [
                    ]
                }
            ]
        });
        sw.Promed.swQueryToDbfExporterWindow.superclass.initComponent.apply(this, arguments);
    }
});