/**
* swMorbusOnkoTumorStatusWindow - окно редактирования "Состояние опухолевого процесса"
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

sw.Promed.swMorbusOnkoTumorStatusWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    winTitle: lang['sostoyanie_opuholevogo_protsessa'],
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    formMode: 'remote',
    formStatus: 'edit',
    layout: 'border',
    modal: true,
    width: 550,
    height: 180,
    maximizable: true,
    autoScroll: true,
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
                        that.findById('MorbusOnkoTumorStatusEditForm').getFirstInvalidEl().focus(true);
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
        Ext.Ajax.request({
            failure:function () {
                loadMask.hide();
            },
            params: this.form.getValues(),
            method: 'POST',
            success: function (result, action) {
                loadMask.hide();
                var combo = that.form.findField('OnkoTumorStatusType_id');
                var rec = combo.getStore().getById(combo.getValue());
                if (rec) {
                    that.formParams.OnkoTumorStatusType_id = combo.getValue();
                    that.formParams.OnkoTumorStatusType_Name = rec.get('OnkoTumorStatusType_Name');
                    that.callback(that.formParams);
                    that.hide();
                }
            },
            url:'/?c=MorbusOnkoTumorStatus&m=save'
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
        this.formParams = formParams;
    },
    show: function() {
        var that = this;
        sw.Promed.swMorbusOnkoTumorStatusWindow.superclass.show.apply(this, arguments);
        this.action = 'edit';
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

        switch (arguments[0].action) {
            case 'edit':
                this.setTitle(this.winTitle +lang['_redaktirovanie']);
                break;
            case 'view':
                this.setTitle(this.winTitle +lang['_prosmotr']);
                break;
        }
        if(getRegionNick() == 'kz'){
            this.form.findField('MorbusOnkoTumorStatus_NumTumor').hideContainer();
        }
        that.onLoadForm(arguments[0].formParams);

        return true;
    },
    initComponent: function() {
        var that = this;

        this.formPanel = new Ext.form.FormPanel({
            autoHeight: true,
            bodyBorder: false,
            bodyStyle:'background:#DFE8F6;padding:5px;',
            labelWidth: 180,
            labelAlign: 'right',
            border: false,
            frame: false,
            region: 'center',
            id: 'MorbusOnkoTumorStatusEditForm',
            items: [{
                name: 'MorbusOnkoTumorStatus_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'MorbusOnkoBasePersonState_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: lang['poryadkovyiy_nomer_opuholi'],
                name: 'MorbusOnkoTumorStatus_NumTumor',
                xtype: 'textfield',
                value: '',
                readOnly: true,
                width: 50
            }, {
                name: 'Diag_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: getRegionNick() == 'kz'?lang['diagnoz']:lang['topografiya'],
                name: 'Diag_Name',
                xtype: 'textfield',
                value: '',
                readOnly: true,
                width: 300
            }, {
                fieldLabel: getRegionNick() == 'kz'?'Iсiк процесiнiң жағдайы (Состояние опухолевого процесса)':lang['sostoyanie'],
                allowBlank: false,
                hiddenName: 'OnkoTumorStatusType_id',
                xtype: 'swcommonsprlikecombo',
                sortField:'OnkoTumorStatusType_Code',
                comboSubject: 'OnkoTumorStatusType',
                width: 300
            }],
            url:'/?c=MorbusOnkoTumorStatus&m=save',
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'MorbusOnkoTumorStatus_id'},
                {name: 'MorbusOnkoBasePersonState_id'},
                {name: 'Diag_id'},
                {name: 'MorbusOnkoTumorStatus_NumTumor'},
                {name: 'Diag_Name'},
                {name: 'OnkoTumorStatusType_id'}
            ])
        });

        Ext.apply(this, {
            layout: 'border',
            buttons:
                [{
                    handler: function()
                    {
                        that.doSave();
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
                            that.hide();
                        },
                        iconCls: 'cancel16',
                        text: BTN_FRMCANCEL
                    }],
            items: [this.formPanel]
        });
        sw.Promed.swMorbusOnkoTumorStatusWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.formPanel.getForm();
	}	
});