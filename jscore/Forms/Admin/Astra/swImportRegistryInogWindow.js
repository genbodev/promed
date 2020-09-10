/**
 * swImportRegistryInogWindow - окно испорта ответа по иногородним.
 */

sw.Promed.swImportRegistryInogWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: true,
    buttonAlign: 'left',
    modal: true,
    closable: true,
    closeAction: 'hide',
    draggable: false,
    id: 'ImportRegistryInogWindow',
    title: 'Импорт сведений по иногородним',
    width: 400,
    //layout: 'form',
    resizable: false,
    onHide: Ext.emptyFn,
    plain: true,
    initComponent: function()
    {

        this.RegistryImportTpl = new Ext.Template(
            [
                '<div>{recAll}</div><div>{recErr}</div> <div>{dates}</div>'
            ]);

        this.RegistryImportPanel = new Ext.Panel(
            {
                bodyStyle: 'padding:2px',
                layout: 'fit',
                border: true,
                frame: false,
                height: 46,
                //maxSize: 30,
                html: ''
            });

        this.TextPanel = new Ext.FormPanel(
            {
                autoHeight: true,
                bodyBorder: false,
                fileUpload: true,
                bodyStyle: 'padding: 5px 5px 0',
                frame: true,
                labelWidth: 50,
                url: '/?c=Registry&m=importRegistryInog',
                reader: new Ext.data.JsonReader(
                    {
                        success: Ext.emptyFn
                    },
                    [
                        { name: 'Registry_id' },
                        { name: 'recAll' },
                        { name: 'recErr' }
                    ]),
                defaults:
                {
                    anchor: '95%',
                    allowBlank: false,
                    msgTarget: 'side'
                },
                items:
                    [{
                        xtype: 'fileuploadfield',
                        anchor: '95%',
                        emptyText: 'Выберите файл импорта',
                        fieldLabel: 'Файл',
                        name: 'ImportFile'
                    },
                        this.RegistryImportPanel]
            });

        this.Panel = new Ext.Panel(
            {
                autoHeight: true,
                bodyBorder: false,
                border: false,
                labelAlign: 'right',
                labelWidth: 100,
                items: [this.TextPanel]
            });

        Ext.apply(this,
            {
                autoHeight: true,
                buttons: [
                    {
                        id: 'IRIW_Ok',
                        handler: function()
                        {
                            this.ownerCt.doSave();
                        },
                        iconCls: 'refresh16',
                        text: 'Загрузить'
                    },
                    {
                        text: '-'
                    },
                    HelpButton(this),
                    {
                        handler: function()
                        {
                            this.ownerCt.hide();
                        },
                        iconCls: 'cancel16',
                        onTabElement: 'IRIW_Ok',
                        text: BTN_FRMCANCEL
                    }],
                items: [this.Panel]
            });
        sw.Promed.swImportRegistryInogWindow.superclass.initComponent.apply(this, arguments);
    },
    listeners:
    {
        'hide': function()
        {
            this.onHide();
        }
    },
    doSave: function()
    {
        var form = this.TextPanel;
        if (!form.getForm().isValid())
        {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    fn: function()
                    {
                        form.getFirstInvalidEl().focus(true);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }
        form.ownerCt.ownerCt.submit();
        return true;
    },
    submit: function()
    {
        var form = this.TextPanel;
        var win = this;
        win.getLoadMask('Загрузка и анализ файла. Подождите...').show();

        form.getForm().submit(
            {
                failure: function(result_form, action)
                {
                    if ( action.result )
                    {

                        if ( action.result.Error_Msg )
                        {
                            sw.swMsg.alert('Ошибка', action.result.Error_Msg);
                        }
                        else
                        {
                            sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
                        }
                    }
                    win.getLoadMask().hide();
                },
                success: function(result_form, action)
                {
                    win.getLoadMask().hide();
                    var answer = action.result;
                    if (answer && answer.success)
                    {

                            sw.swMsg.show(
                                {
                                    buttons: Ext.Msg.OK,
                                    icon: Ext.Msg.INFO,
                                    msg: answer.Message,
                                    title: 'Сообщение'
                                });
                            win.RegistryImportTpl.overwrite(win.RegistryImportPanel.body,
                                {
                                    recAll: "Обновлено:  <b>"+answer.addRecs+"</b>",
                                    recErr: "С ошибками:  <b>"+answer.errRecs+"</b>"
                                });
                            Ext.getCmp('IRIW_Ok').disable();



                    }else{
                        sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                fn: function()
                                {
                                    form.hide();
                                },
                                icon: Ext.Msg.ERROR,
                                msg: 'Во время выполнения операции произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
                                title: 'Ошибка'
                            });
                    }
                }
            });
    },
    getLoadMask: function(MSG)
    {
        if (MSG)
        {
            delete(this.loadMask);
        }
        if (!this.loadMask)
        {
            this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
        }
        return this.loadMask;
    },
    show: function()
    {
        sw.Promed.swImportRegistryInogWindow.superclass.show.apply(this, arguments);
        var form = this;
        form.onHide = Ext.emptyFn;
        Ext.getCmp('IRIW_Ok').enable();
        form.TextPanel.getForm().reset();

        form.RegistryImportTpl.overwrite(form.RegistryImportPanel.body,
            {});

        if (arguments[0].onHide)
        {
            form.onHide = arguments[0].onHide;
        }
    }
});