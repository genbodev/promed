/**
 * swLpuBuildingOfficeAssign - назначение кабинетов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       brotherhood of swan developers
 * @version      2019
 */
sw.Promed.swLpuBuildingOfficeAssign = Ext.extend(sw.Promed.BaseForm, {

    id: 'LpuBuildingOfficeAssign',
    autoHeight: false,
    layout: 'form',
    modal: true,
    resizable: false,
    width: 530,
    height: 185,
    // имя основной формы
    formName: 'LpuBuildingOfficeAssignEditForm',
    // краткое имя формы (для айдишников)
    formPrefix: 'LBOAEF_',

    getMainForm: function(){ return this[this.formName].getForm(); },
    setDisabled: function(disable) {

        var wnd = this,
            form = wnd.getMainForm();

        form.items.each(function(field){
            field.setDisabled(disable);
        });

        if (disable) {
            wnd.buttons[0].disable();
            wnd.buttons[1].disable();
        } else {
            wnd.buttons[0].enable();
            wnd.buttons[1].enable();
        }
    },
    completeFn: function(){

        var wnd = this;

        if (this.panelCompleteFn && typeof this.panelCompleteFn === 'function') {

            this.panelCompleteFn({
                bypassCheckRedirection: true,
                callback: function() {
                    wnd.hide();
                }
            });

        } else { log('no redirect completeFn defined');}
    },
    doSave: function() {

        var wnd = this,
            form = wnd.getMainForm();

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

        var submitUrl = "";
        var params = {};

        if (wnd.ElectronicInfomat_id) {
            params.ElectronicInfomat_id = wnd.ElectronicInfomat_id;
            submitUrl = '/?c=LpuBuildingOffice&m=saveLpuBuildingOfficeInfomat';
        }

        if (wnd.ElectronicScoreboard_id) {
            params.ElectronicScoreboard_id = wnd.ElectronicScoreboard_id;
            submitUrl = '/?c=LpuBuildingOffice&m=saveLpuBuildingOfficeScoreboard';
        }

        if (!submitUrl) {

            sw.swMsg.show({

                msg: "Не определен объект назначения кабинета",
                title: ERR_INVFIELDS_TIT,
                icon: Ext.Msg.WARNING,
                buttons: Ext.Msg.OK,

                fn: function() {}
            });

            return false;
        }

        //wnd.getLoadMask('Сохраняю...').show();
        form.submit({
            params: params,
            url: submitUrl,
            success: function(form, action) {
                wnd.onSave();
                wnd.hide();
                //wnd.getLoadMask().hide();
            }
        });
    },

    show: function() {

        sw.Promed['sw'+this.id].superclass.show.apply(this, arguments);

        var wnd = this,
            form = wnd.getMainForm(),
            loadMask = new Ext.LoadMask(
                wnd.getEl(),{
                    msg: LOAD_WAIT
                }
            );

        wnd.action = null;

        if (!arguments[0]){

            sw.swMsg.show({

                buttons: Ext.Msg.OK,
                icon: Ext.Msg.ERROR,
                title: langs('Ошибка'),
                msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),

                fn: function() { wnd.hide(); }
            });
        }

        var args = arguments[0];
        wnd.focus();
        form.reset();

        this.setTitle("Назначение кабинета");

        for (var field_name in args) {
            log(field_name +':'+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

        loadMask.show();

        var combo = form.findField('LpuBuildingOffice_id'),
            comboStore = combo.getStore();

        comboStore.baseParams.Lpu_id = wnd.Lpu_id;
        comboStore.baseParams.LpuBuilding_id = wnd.LpuBuilding_id;

        loadMask.hide();

        switch (wnd.action) {
            case 'add':
                wnd.loadCombo(combo, {
                    Lpu_id: wnd.Lpu_id,
                    LpuBuilding_id: wnd.LpuBuilding_id
                });
                wnd.setDisabled(false);
                break;
            case 'edit':
                wnd.loadFormData();
                wnd.setDisabled(false);
                break;
            case 'view':
                wnd.setDisabled(true);
                break;
        }
    },
    loadCombo: function(combo, params, callbackFN){

        var comboStore = combo.getStore();

        comboStore.load({
            params: params,
            callback: function(){
                if (callbackFN && typeof callbackFN === 'function') {
                    callbackFN();
                }
            }
        });
    },
    loadFormData: function() {

        var wnd = this,
            form = wnd.getMainForm();

        var params = {};

        if (wnd.LpuBuildingOfficeInfomat_id) {
            params.assign_id = wnd.LpuBuildingOfficeInfomat_id;
            params.object = 'infomat';
        }

        if (wnd.LpuBuildingOfficeScoreboard_id) {
            params.assign_id = wnd.LpuBuildingOfficeScoreboard_id;
            params.object = 'scoreboard';
        }

        Ext.Ajax.request({
            url: '/?c=LpuBuildingOffice&m=loadLpuBuildingOfficeAssignData',
            params: params,
            success: function (resp) {

                var combo = form.findField('LpuBuildingOffice_id');

                var objectData = Ext.util.JSON.decode(resp.responseText);

                if (objectData.length > 0) {

                    var item = objectData[0];

                    wnd.loadCombo(combo, {
                        Lpu_id: wnd.Lpu_id,
                        LpuBuilding_id: wnd.LpuBuilding_id
                    }, function(){
                        combo.setValue(item.LpuBuildingOffice_id);
                    });

                    if (item.LpuBuildingOfficeAssign_begDate) {
                        var begDT = form.findField('LpuBuildingOfficeAssign_begDate');
                        log('breg', begDT);
                        begDT.setValue(item.LpuBuildingOfficeAssign_begDate);
                    }

                    if (item.LpuBuildingOfficeAssign_endDate) {
                        var endDT = form.findField('LpuBuildingOfficeAssign_endDate');
                        endDT.setValue(item.LpuBuildingOfficeAssign_endDate);
                    }

                    if (params.object == 'scoreboard') {
                        form.findField('LpuBuildingOfficeScoreboard_id').setValue(item.LpuBuildingOfficeScoreboard_id);
                    }

                    if (params.object == 'infomat') {
                        form.findField('LpuBuildingOfficeInfomat_id').setValue(item.LpuBuildingOfficeInfomat_id);
                    }
                }
            },
            error: function (elem, resp) {

                if (!resp.result.success) {
                    Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
                    this.hide();
                }
            }
        });
    },
    initComponent: function() {

        var wnd = this,
            formName = wnd.formName;

        wnd[formName] = new Ext.form.FormPanel({

            bodyStyle: '{padding-top: 15px;}',
            border: false,
            bodyBorder: false,
            height: 350,
            frame: true,
            layout: 'form',
            id: formName,
            url:'/?c=LpuBuildingOffice&m=saveLpuBuildingOfficeScoreboard',
            labelWidth: 140,
            labelAlign: 'right',

            items: [
                {
                    xtype: 'hidden',
                    name: 'LpuBuildingOfficeScoreboard_id'
                },
                {
                    xtype: 'hidden',
                    name: 'LpuBuildingOfficeInfomat_id'
                },
                {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Кабинет',
                    hiddenName: 'LpuBuildingOffice_id',
                    displayField: 'LpuBuildingOffice_Name',
                    valueField: 'LpuBuildingOffice_id',
                    width: 330,
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'LpuBuildingOffice_id', mapping: 'LpuBuildingOffice_id' },
                            { name: 'LpuBuildingOffice_Name', mapping: 'LpuBuildingOffice_Name' },
                            { name: 'LpuBuildingOffice_Number', mapping: 'LpuBuildingOffice_Number' }
                        ],
                        remoteSort: true,
                        key: 'LpuBuildingOffice_id',
                        url:'/?c=LpuBuildingOffice&m=loadLpuBuildingOfficeCombo'
                    }),
                    ownerWindow: wnd,
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<table><tr><td style="width: 40px; color: red;">{LpuBuildingOffice_Number}&nbsp;</td><td>{LpuBuildingOffice_Name}&nbsp;</td></tr></table>',
                        '</div></tpl>'
                    )
                },
                {
                    name: 'LpuBuildingOfficeAssign_begDate',
                    fieldLabel: 'Дата начала',
                    xtype: 'swdatefield',
                    allowBlank: false,
                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
                    width: 100
                }, {
                    name: 'LpuBuildingOfficeAssign_endDate',
                    fieldLabel: 'Дата окончания',
                    xtype: 'swdatefield',
                    allowBlank: true,
                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
                    width: 100
                }
            ]
        });

        Ext.apply(this, {
            buttons:
                [{
                    text: 'Добавить',
                    iconCls: 'save16',
                    handler: function() {  wnd.doSave();  }
                },
                { text: '-' },
                HelpButton(this, TABINDEX_RRLW + 13),
                {
                    iconCls: 'close16',
                    tabIndex: TABINDEX_RRLW + 14,
                    handler: function() { wnd.hide(); },
                    text: BTN_FRMCLOSE
                }],
            items: [this[this.formName]]
        });

        sw.Promed['sw'+this.id].superclass.initComponent.apply(this, arguments);
    }
});