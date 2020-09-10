Ext6.define('common.XmlTemplate.OpenTextWindow', {
    extend: 'base.BaseForm',
    alias: 'widget.swXmlTemplateOpenTextWindow',
    requires: [],
    renderTo: main_center_panel.body.dom,
    autoShow: false,
    cls: 'arm-window-new open-text-template-window arm-window-new-without-padding',
    title: 'Помощник',
    width: 1200,
    height: 800,
    modal: true,

    save: function () {
        let data = [];
        let me = this;
        let baseForm = me.formPanel.getForm();
        let formFields = baseForm.getFields();
        formFields.items.forEach(function(item){
            if(item.value !== false && item.value !== '' && item.xtype !== 'checkboxgroup') {
                let value = item.value;
                let name = item.name;
                if(name.indexOf('radioButton', 0) === 0) {
                    name = 'radioButton' + item.initialConfig.value;
                }
                data.push({
                    'id': name,
                    'value': value
                });
            }
        });
        Ext.Ajax.request({
            url: '/?c=StructuredParams&m=sendStructuredParamData',
            params: {
                data: JSON.stringify(data)
            },
            callback: function(opt, success, response) {
                me.callback(response);
                me.hide();
            }
        });
    },

    loadSubTree: function (owner, id) {
        let me = this;
        me.mask('Загрузка...');
        Ext6.Ajax.request({
            url: '/?c=StructuredParams&m=getStructuredParamsGridBranch',
            params: {
                StructuredParams_pid: id
            },
            success: function (response) {
                let result = Ext6.decode(response.responseText);
                me.blocksRendering = true;
                result.data.forEach(function(item){
                    let panel = {};
                    if(parseInt(item.StructuredParamsType_id) === 4) {
                        panel = {
                            xtype: 'fieldcontainer',
                            layout: 'vbox',
                            padding: '0 0 0 50',
                            defaults: {
                                flex: 1,
                                hideLabel: true
                            },
                            items: []
                        };
                        item.controls.forEach(function(controlItem){
                            if(controlItem.type === 'checkbox') {
                                panel.items.push({
                                    name: 'checkbox_' + item.StructuredParams_id,
                                    refId: item.StructuredParams_id,
                                    boxLabel: controlItem.value,
                                    xtype: 'checkbox',
                                    hideLabel: false,
                                    flex: 1,
                                    listeners: {
                                        change: function (component, newVal, oldVal) {
                                            //console.log(component);
                                        }
                                    }
                                });
                            }
                            if(controlItem.type === 'edit'){
                                panel.items.push({
                                    xtype: 'textfield',
                                    name: 'textfield_' + item.StructuredParams_id,
                                    padding: '0 0 0 10',
                                    hideLabel: false,
                                    flex: 1
                                });
                                panel.items.push({
                                    xtype: 'component',
                                    padding: '12 0 0 5',
                                    html: controlItem.value
                                });
                            }
                        });
                    }
                    if(parseInt(item.StructuredParamsType_id) === 2) {
                        //Одиночный выбор
                        panel = {
                            xtype: 'fieldcontainer',
                            layout: 'vbox',
                            padding: '0 0 0 50',
                            defaults: {
                                flex: 1,
                                hideLabel: true
                            },
                            items: [{
                                xtype: 'checkboxfield',
                                name: 'checkbox_' + item.StructuredParams_id,
                                refId: item.StructuredParams_id,
                                boxLabel: item.StructuredParams_Name,
                                hideLabel: false,
                                flex: 1,
                                listeners: {
                                    change: function (component, newVal) {
                                        let ownerCt = component.ownerCt;
                                        if(newVal === true && ownerCt.items.length === 1) {
                                            Ext6.Ajax.request({
                                                url: '/?c=StructuredParams&m=getStructuredParamsGridBranch',
                                                params: {
                                                    StructuredParams_pid: component.refId
                                                },
                                                success: function (response) {
                                                    let result = Ext6.decode(response.responseText);
                                                    let buttons = [];
                                                    result.data.forEach(function (item) {
                                                        buttons.push({
                                                            xtype: 'radiofield',
                                                            name: 'radioButton' + component.refId,
                                                            value: item.StructuredParams_id,
                                                            boxLabel: item.StructuredParams_Name,
                                                            handler: function (item) {
                                                                console.log(item);
                                                            }
                                                        })
                                                    });
                                                    ownerCt.add({
                                                        xtype: 'fieldcontainer',
                                                        layout: 'vbox',
                                                        padding: '0 0 0 50',
                                                        defaults: {
                                                            flex: 1,
                                                            hideLabel: true
                                                        },
                                                        items: buttons
                                                    });
                                                }
                                            });
                                        }
                                    }
                                }
                            }]
                        };
                    }
                    if(parseInt(item.StructuredParamsType_id) === 1) {
                        //Множественный выбор
                        panel = {
                            xtype: 'fieldcontainer',
                            layout: 'vbox',
                            padding: '0 0 0 50',
                            defaults: {
                                flex: 1,
                                hideLabel: true
                            },
                            items: [{
                                name: 'checkbox_' + item.StructuredParams_id,
                                refId: item.StructuredParams_id,
                                boxLabel: item.StructuredParams_Name,
                                xtype: 'checkbox',
                                hideLabel: false,
                                flex: 1,
                                listeners: {
                                    change: function (component, newVal) {
                                        let ownerCt = component.ownerCt;
                                        if(newVal === true && ownerCt.items.length === 1) {
                                            Ext6.Ajax.request({
                                                url: '/?c=StructuredParams&m=getStructuredParamsGridBranch',
                                                params: {
                                                    StructuredParams_pid: component.refId
                                                },
                                                success: function (response) {
                                                    let result = Ext6.decode(response.responseText);
                                                    let buttons = [];
                                                    result.data.forEach(function (item) {
                                                        buttons.push({
                                                            boxLabel: item.StructuredParams_Name,
                                                            name: 'radioButton' + item.StructuredParams_id,
                                                            value: item.StructuredParams_id,
                                                            handler: function (item) {
                                                                console.log(item);
                                                            }
                                                        })
                                                    });
                                                    ownerCt.add({
                                                        xtype: 'fieldcontainer',
                                                        layout: 'vbox',
                                                        padding: '0 0 0 50',
                                                        defaults: {
                                                            flex: 1,
                                                            hideLabel: true
                                                        },
                                                        items: [{
                                                            xtype: 'checkboxgroup',
                                                            items: buttons
                                                        }]
                                                    });
                                                }
                                            });
                                        }
                                    }
                                }
                            }]
                        };
                    }
                    owner.add(panel);
                });
                me.blocksRendering = false;
                me.unmask();
            }
        });
    },

    loadTree: function (node) {
        let me = this;
        let formPanel = me.formPanel;
        Ext6.Ajax.request({
            url: '/?c=StructuredParams&m=getStructuredParamsTreeBranch',
            params: {
                node: node
            },
            success: function (response) {
                let responseObj = Ext6.decode(response.responseText);
                formPanel.removeAll();
                responseObj.forEach(function(item) {
                    formPanel.add({
                        xtype: 'fieldcontainer',
                        layout: 'vbox',
                        defaults: {
                            flex: 1,
                            hideLabel: true
                        },
                        items: [{
                            name: 'checkbox_' + item.id,
                            refId: item.id,
                            boxLabel: item.text,
                            xtype: 'checkbox',
                            hideLabel: false,
                            flex: 1,
                            listeners: {
                                change: function (component, newVal) {
                                    let ownerCt = component.ownerCt;
                                    if (newVal === true && ownerCt.items.length === 1) {
                                        me.loadSubTree(ownerCt, component.refId);
                                    }
                                }
                            }
                        }]
                    });
                });
            }
        });
    },

    onSprLoad: function (args) {
        let me = this;
        let baseForm = me.formPanel.getForm();
        if (!args[0] || !args[0].params) {
            return;
        }
        let params = args[0].params;
        baseForm.setValues(params);
        me.loadTree('root');
    },

    show: function () {
        let me = this;
        me.formPanel.reset();
        me.callback = Ext6.emptyFn;
        me.callParent(arguments);
        if (arguments[0].callback) {
            me.callback = arguments[0].callback;
        }
    },

    initComponent: function () {
        let me = this;

        me.formPanel = Ext6.create('Ext6.form.Panel', {
            scrollable: true,
            bodyPadding: '20 20 0 20',
            border: false,
            flex: 1,
            height: '100%',
            bodyStyle: 'border-width: 0 0 0 1px',
            layout: 'vbox',
            defaults: {
                border: false,
                allowBlank: false,
                matchFieldWidth: false,
                width: '100%',
            },
            items: []
        });

        Ext6.apply(me, {
            layout: 'vbox',
            defaults: {
                width: '100%'
            },
            items: [me.formPanel],
            buttons: [
                {
                    text: 'Очистить',
                    userCls: 'buttonClear',
                    margin: 0,
                    handler: function () {
                        me.loadTree('root');
                    }
                }, '->', {
                    text: 'Отмена',
                    userCls: 'buttonCancel',
                    margin: 0,
                    handler: function () {
                        me.hide();
                    }
                }, {
                    id: me.getId() + '-save-btn',
                    cls: 'buttonAccept',
                    text: 'Применить',
                    margin: '0 19 0 0',
                    handler: function () {
                        me.save();
                    }
                }
            ]
        });
        me.callParent(arguments);
    }
});