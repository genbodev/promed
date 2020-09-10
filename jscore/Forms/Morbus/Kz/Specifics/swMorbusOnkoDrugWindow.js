/**
* swMorbusOnkoDrugWindow - окно редактирования "Препарат"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      06.2013
* @comment      
*/

sw.Promed.swMorbusOnkoDrugWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    winTitle: lang['preparat'],
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    formMode: 'remote',
    formStatus: 'edit',
    layout: 'form',
    modal: true,
    minWidth: 520,
    width: 520,
    autoHeight: true,
    maximizable: false,
    listeners: {
        hide: function() {
            this.onHide();
        }
    },
    onHide: Ext.emptyFn,
    doSave:  function() {
        var that = this;
        if ( !this.form.isValid() )
        {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    fn: function()
                    {
                        that.findById('MorbusOnkoDrugEditForm').getFirstInvalidEl().focus(true);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }
        this.submit();
        return true;
    },
    submit: function() {
        var that = this;
        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
        loadMask.show();
        var formParams = this.form.getValues();
        formParams.DrugDictType_id = 1;
        Ext.Ajax.request({
            failure:function () {
                loadMask.hide();
            },
            params: formParams,
            method: 'POST',
            success: function (result, action) {
                loadMask.hide();
                if (result.responseText) {
                    var response = Ext.util.JSON.decode(result.responseText);
                    if (response.success) {
                        formParams.MorbusOnkoDrug_id = response.MorbusOnkoDrug_id;
                        that.callback(formParams);
                        that.hide();
                    }
                }
            },
            url:'/?c=MorbusOnkoDrug&m=save'
        });
    },
    setFieldsDisabled: function(d)
    {
        var form = this;
        this.form.items.each(function(f)
        {
            if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
            {
                f.setDisabled(d);
            }
        });
        form.buttons[0].setDisabled(d);
    },
    onLoadForm: function(formParams) {
        var accessType = formParams.accessType || 'edit';
        this.setFieldsDisabled(this.action == 'view' || accessType == 'view');

        this.form.setValues(formParams);
    },
    show: function() {
        var that = this;
        sw.Promed.swMorbusOnkoDrugWindow.superclass.show.apply(this, arguments);
        this.action = 'add';
        this.callback = Ext.emptyFn;
        if ( !arguments[0] || !arguments[0].formParams) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
            return false;
        }
        if ( arguments[0].action ) {
            this.action = arguments[0].action;
        }
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }
        this.form.reset();

        this.EvnUsluga_setDT = arguments[0].EvnUsluga_setDT || null;
        this.EvnUsluga_disDT = arguments[0].EvnUsluga_disDT || null;
        var set_dt_field = this.form.findField('MorbusOnkoDrug_begDT');
        var dis_dt_field = this.form.findField('MorbusOnkoDrug_endDT');
        set_dt_field.setMinValue(this.EvnUsluga_setDT);
        set_dt_field.setMaxValue(this.EvnUsluga_disDT);
        dis_dt_field.setMinValue(this.EvnUsluga_setDT);
        dis_dt_field.setMaxValue(this.EvnUsluga_disDT);

        switch (arguments[0].action) {
            case 'add':
                this.setTitle(this.winTitle +': ' + FRM_ACTION_ADD);
                break;
            case 'edit':
                this.setTitle(this.winTitle +': ' + FRM_ACTION_EDIT);
                break;
            case 'view':
                this.setTitle(this.winTitle +': ' + FRM_ACTION_VIEW);
                break;
        }

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();

		// https://redmine.swan.perm.ru/issues/64061
		// Только для Уфы, для остальных регионов поле невидимо
		// this.form.findField('DrugDictType_id').setContainerVisible(getRegionNick().inlist([ 'ufa' ]));

		switch (this.action) {
            case 'add':
                loadMask.hide();
				this.onLoadForm(arguments[0].formParams);
                break;
            case 'edit':
            case 'view':
                Ext.Ajax.request({
                    failure:function () {
                        sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                        loadMask.hide();
                        that.hide();
                    },
                    params:{
                        MorbusOnkoDrug_id: arguments[0].formParams.MorbusOnkoDrug_id
                    },
                    method: 'POST',
                    success: function (response) {
                        loadMask.hide();
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (!result[0]) { return false; }
                        if (result[0]['Error_Msg']) {
                            sw.swMsg.alert(lang['oshibka'], result[0]['Error_Msg']);
                            return false;
                        }
                        that.onLoadForm(result[0]);
                        return true;
                    },
                    url:'/?c=MorbusOnkoDrug&m=load'
                });
                break;
        }
        return true;
    },
    initComponent: function() {
        var that = this;
        this.formPanel = new Ext.form.FormPanel({
            autoHeight: true,
            autoScroll: true,
            bodyBorder: false,
            border: false,
            frame: false,
            id: 'MorbusOnkoDrugEditForm',
            bodyStyle:'background:#DFE8F6;padding:5px;',
            labelWidth: 120,
            labelAlign: 'right',
            region: 'center',
            items: [{
                name: 'MorbusOnkoDrug_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'MorbusOnko_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'Evn_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: 'Қабылдау басталған күн (Дата начала приема)',//lang['data_nachala'],
                name: 'MorbusOnkoDrug_begDT',
                xtype: 'swdatefield',
                allowBlank: false,
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                listeners: {
                    'change':function (field, newValue, oldValue) {
                        var dis_dt_field = that.form.findField('MorbusOnkoDrug_endDT');
                        if (newValue) {
                            dis_dt_field.setMinValue(newValue);
                        }
                        else {
                            dis_dt_field.setMinValue(null);
                        }
                    }
                }
            }, {
                fieldLabel: 'Қабылдау аяқталған күн (Дата окончания приема)',//lang['data_okonchaniya'],
                name: 'MorbusOnkoDrug_endDT',
                xtype: 'swdatefield',
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
            }, {
				anchor: null,
                fieldLabel: 'Препараттың атауы (Наименование препарата)',//lang['preparat'],
                hiddenName: 'CLSATC_id',
                allowBlank: false,
                xtype: 'swrlsclsatccombo',
                listWidth: 400,
                width: 330
            }, {
                fieldLabel: 'Өлшем бiрлiгi (Ед. измерения)',//lang['ed'],
                hiddenName: 'OnkoDrugUnitType_id',
                xtype: 'swcommonsprlikecombo',
                sortField:'OnkoDrugUnitType_Code',
                comboSubject: 'OnkoDrugUnitType',
                width: 200
            }, {
                fieldLabel: 'Қосынды доза (Суммарная доза)',//lang['summarnaya_doza'],
                name: 'MorbusOnkoDrug_SumDose',
                xtype: 'textfield',
                width: 200
            }, {
                fieldLabel: 'Енгізу түрі (Способ введения)',//lang['metod_vvedeniya'],
                hiddenName: 'PrescriptionIntroType_id',
                xtype: 'swcommonsprlikecombo',
                comboSubject: 'PrescriptionIntroType',
                width: 200
            }],
            url:'/?c=MorbusOnkoDrug&m=save',
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'MorbusOnkoDrug_id'},
                {name: 'MorbusOnko_id'},
                {name: 'MorbusOnkoDrug_begDT'},
                {name: 'MorbusOnkoDrug_endDT'},
                {name: 'CLSATC_id'},
                {name: 'OnkoDrug_id'},
                {name: 'OnkoDrugUnitType_id'},
                {name: 'MorbusOnkoDrug_SumDose'},
                {name: 'MorbusOnkoDrug_Method'},
                {name: 'Evn_id'}
            ])
        });
        Ext.apply(this, {
            buttons:
                [{
                    handler: function()
                    {
                        this.ownerCt.doSave();
                    },
                    iconCls: 'save16',
                    text: BTN_FRMSAVE
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
                        text: BTN_FRMCANCEL
                    }],
            items:[this.formPanel]
        });
        sw.Promed.swMorbusOnkoDrugWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.formPanel.getForm();
	}	
});