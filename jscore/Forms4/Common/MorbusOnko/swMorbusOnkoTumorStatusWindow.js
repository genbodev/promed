/**
* swMorbusOnkoTumorStatusWindow - окно редактирования "Состояние опухолевого процесса"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @comment      
*/

Ext6.define('common.MorbusOnko.swMorbusOnkoTumorStatusWindow', {
	/* свойства */
	alias: 'widget.swMorbusOnkoTumorStatusWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'MorbusOnkoTumorStatuseditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: langs('Состояние опухолевого процесса'),
	winTitle: langs('Состояние опухолевого процесса'),
	width: 700,
	
	/* методы */
	save: function () {
        var win = this;
        if ( !this.form.isValid() ) {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    fn: function()
                    {
                        win.findById('MorbusOnkoTumorStatusEditForm').getFirstInvalidEl().focus(true);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }
	
		win.mask(LOAD_WAIT_SAVE);
        Ext.Ajax.request({
            failure:function () {
                win.unmask();
            },
            params: this.form.getValues(),
            method: 'POST',
            success: function (result, action) {
                win.unmask();
                var combo = win.form.findField('OnkoTumorStatusType_id');
                var rec = combo.getStore().getById(combo.getValue());
                if (rec) {
                    win.formParams.OnkoTumorStatusType_id = combo.getValue();
                    win.formParams.OnkoTumorStatusType_Name = rec.get('OnkoTumorStatusType_Name');
                    win.callback(win.formParams);
                    win.hide();
                }
            },
            url:'/?c=MorbusOnkoTumorStatus&m=save'
        });
	},
	
    setFieldsDisabled: function(d) {
        var form = this;
        this.FormPanel.items.each(function(f){
            if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
                f.setDisabled(d);
            }
        });
       // form.MorbusOnkoTumorStatusFrame.setReadOnly(d);
        //form.buttons[0].setDisabled(d);
    },
	
    onLoadForm: function(formParams) {
        var accessType = formParams.accessType || 'edit';
        this.setFieldsDisabled(this.action == 'view' || accessType == 'view');

        this.form.setValues(formParams);
        this.formParams = formParams;
    },
	
	onSprLoad: function(arguments) {
        var win = this;
        this.action = 'edit';
        this.callback = Ext.emptyFn;
		this.form = this.FormPanel.getForm();
        if ( !arguments[0] || !arguments[0].formParams) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { win.hide(); });
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
                this.setTitle(this.winTitle +langs(': Редактирование'));
                break;
            case 'view':
                this.setTitle(this.winTitle +langs(': Просмотр'));
                break;
        }
        if(getRegionNick() == 'kz'){
            this.form.findField('MorbusOnkoTumorStatus_NumTumor').hideContainer();
        }
        win.onLoadForm(arguments[0].formParams);
	},

	show: function() {
		this.callParent(arguments);
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model'
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			cls: 'emk_forms accordion-panel-window',
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=MorbusOnkoTumorStatus&m=save',
			items: [{
                name: 'MorbusOnkoTumorStatus_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'MorbusOnkoBasePersonState_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: langs('Порядковый номер опухоли'),
                name: 'MorbusOnkoTumorStatus_NumTumor',
                xtype: 'textfield',
                value: '',
                readOnly: true,
                width: 250
            }, {
                name: 'Diag_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: getRegionNick() == 'kz'?langs('Диагноз'):langs('Топография'),
                name: 'Diag_Name',
                xtype: 'textfield',
                value: '',
                readOnly: true,
				anchor:'100%'
            }, {
                fieldLabel: getRegionNick() == 'kz'?'Iсiк процесiнiң жағдайы (Состояние опухолевого процесса)':langs('Состояние'),
                allowBlank: false,
                name: 'OnkoTumorStatusType_id',
                xtype: 'commonSprCombo',
                sortField: 'OnkoTumorStatusType_Code',
                comboSubject: 'OnkoTumorStatusType',
				typeCode: 'int',
				anchor:'100%'
            }]
		});
	

        Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			},{
				xtype: 'SubmitButton',
				handler:function () {
					win.save();
				}
			}]
		});

		this.callParent(arguments);
    }
});