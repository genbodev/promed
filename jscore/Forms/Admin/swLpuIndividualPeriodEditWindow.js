/**
 * swLpuIndividualPeriodEditWindow - окно добавления МО в список МО, имеющих доступ к индивидуальной настройке периодов записи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Mikhail Timofeev
 * @version			30.05.2019
 */

sw.Promed.swLpuIndividualPeriodEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'swLpuIndividualPeriodEditWindow',
    action: null,
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    split: true,
    title: 'Добавление МО',
    autoHeight: true,
    width: 500,
    layout: 'form',
    modal: true,
    plain: true,
    resizable: false,

    // дальше переменные только внутри этого окна

    // сама сущность
    entity: 'LpuIndividualPeriod',
    // имя основной формы, чтобы не задавать внутри
    formName: 'LpuIndividualPeriodEditWindow',
    // поле id главной сущности, чтобы не задавать внутри
    mainIdField: 'LpuIndividualPeriod_id',
    // флаг, если хранилище профилей загружено первый раз
    profileStoreInit: true,
    // флаг, если хранилище специальностей загружено первый раз
    specStoreInit: true,
    // флаг, определяющий загрузили данные в форму через функцию loadData или нет
    isLoadData: false,

    getMainForm: function()
    {
        return this[this.formName].getForm();
    },

    doSave: function() {

        var wnd = this,
            form = wnd.getMainForm(),
            loadMask = new Ext.LoadMask(

                wnd.getEl(), {
                    msg: langs('Подождите, идет сохранение...')
                }
            );

        if (!form.isValid()) {

            sw.swMsg.show({

                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT,
                icon: Ext.Msg.WARNING,
                buttons: Ext.Msg.OK,

                fn: function() {
                    wnd[wnd.formName].getFirstInvalidEl().focus(true);
                }
            });

            return false;
        }

        loadMask.show();

        form.submit({
            params: {
                action: wnd.action,
            },
            failure: function(result_form, action) {

                loadMask.hide();

                
            },
            success: function(result_form, action) {

                loadMask.hide();

                if ( action.result ) {
                    wnd.callback();
                    wnd.hide();
                } else
                    sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
            }
        });
    },  

    show: function() {

        sw.Promed.swLpuIndividualPeriodEditWindow.superclass.show.apply(this, arguments);

        var wnd = this,
            base_form = wnd.getMainForm();
          

        // обновляем грид после сохранения
        if (arguments[0].callback)
            wnd.callback = arguments[0].callback;

        var args = arguments[0].formParams;
        wnd.action = arguments[0].action ? arguments[0].action : null;

        wnd.focus();

        base_form.reset();
        
    },
   
    initComponent: function() {

        var wnd = this,
            formName = wnd.formName;

        wnd[formName] = new Ext.form.FormPanel(
            {

            bodyStyle: '{padding-top: 0.5em;}',
            border: false,
            frame: true,
            labelAlign: 'right',
            labelWidth: 50,
            layout: 'form',
            id: formName,
            url: '/?c=LpuIndividualPeriod&m=saveLpuIndividualPeriod',
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                { name: 'Lpu_id' }
            ]),
            items: [
                {
                    allowBlank: false,
                    hiddenName: 'Lpu_id',
                    xtype: 'swlpulocalcombo',
                    width: 350,
                }
            ]
        });

        Ext.apply(this, {
            items: [
                this[this.formName]
            ],
            buttons: [{
                handler: function() {
                    this.doSave();
                }.createDelegate(this),
                id: 'CIEW_SaveButton',
                text: BTN_FRMADD
            },
                '-',
                HelpButton(this, -1),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    id: 'CIEW_CancelButton',
                    text: BTN_FRMCANCEL
                }]
        });

        sw.Promed.swLpuIndividualPeriodEditWindow.superclass.initComponent.apply(this, arguments);
    }
});
