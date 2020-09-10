/**
 * swPrescDirectionIncludeWindow - окно включения назначения в сущетсвующее направление
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      tools
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @version      06.12.2019
 * @comment      ..
 */
Ext6.define('common.EMK.tools.swPrescDirectionIncludeWindow', {
    /* свойства */
    extend: 'base.BaseForm',
    alias: 'widget.swPrescDirectionIncludeWindow',
    action: null,
    autoShow: false,
    closable: true,
    autoHeight: true,
    buttonAlign: 'left',
    callback: Ext6.emptyFn,
    closeAction: 'hide',
    collapsible: false,
    draggable: true,
    formStatus: 'edit',
    title: 'Объединение услуг',
    refId: 'DirectionIncludeWindow',
    initComponent: function() {
        var me = this;
        this.QuestionLabel = Ext6.create('Ext6.form.Label',{
			html: "<span style='font-size:12px;'>Включить услугу в существующее направление?</span>",
		});
        this.FormPanel = Ext6.create('Ext6.form.Panel', {
            layout: 'anchor',
            bodyPadding: '20 30',
            border: false,
            defaults: {
                border: false,
                padding: '15 0 0 0',
                anchor: '100%',
                width: 615,
                maxWidth: 615 + 145,
                labelWidth: 100
            },
            items: [
            	me.QuestionLabel, {
                refId: 'DIW_RadioButtonGroupContainer',
                bodyStyle: "padding-top: 5px;",
                items: []
            }]
        });

        Ext6.apply(me, {
            layout: 'fit',
            bodyPadding: 0,
            margin: 0,
            border: false,
            items: [
                this.FormPanel
            ],
            buttons: ['->', {
                handler: function () {
                	if(me.onHideED && typeof me.onHideED == 'function') {
                		me.onHideED();
	                }
                    me.hide();
                },
                cls: 'buttonCancel',
                text: 'Отмена'
            }, {
                handler: function () {
                    me.doSave();
                },
                cls: 'buttonAccept',
                text: 'Продолжить',
                margin: '0 20 0 0'
            }]
        });

        this.callParent(arguments);
    },
    layout: 'form',
    listeners: {
        'beforehide': function(win) {
            //
        },
        // 'hide': function(win) {
            // win.onHide();
        // }
    },
    loadMask: null,
    maximizable: false,
    maximized: false,
    modal: true,
    // onHide: function() {
        // if (this.formStatus != 'save') {
        //     this.callback({
        //         include: 'cancel'
        //     });
        // }
    // },
    plain: true,
    resizable: false,
    doSave: function() {
        this.formStatus = 'save';
        var buttonGroup = Ext6.ComponentQuery.query('[refId=DIW_RadioButtonGroup]');
        if (buttonGroup[0]) {
            var EvnDirection_id = buttonGroup[0].items.items[0].getGroupValue();
            if (EvnDirection_id > 0) {
                // если выбрали направление
                this.callback({
                    include: 'yes',
                    EvnDirection_id: EvnDirection_id
                });
            } else {
                this.callback({
                    include: 'no'
                });
            }
        }

        this.hide();
    },
    show: function() {
        this.callParent(arguments);
        this.center();

        var me = this,
			base_form = me.FormPanel.getForm(),
			labelText = "<span style='font-size:12px;'>Включить услугу в существующее направление?</span>";
        base_form.reset();

        this.parentEvnClass_SysNick = null;
        this.action = null;
        this.callback = Ext.emptyFn;
        this.formStatus = 'edit';

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
            return false;
        }

        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }

        if ( arguments[0].EvnDirections ) {
            this.EvnDirections = arguments[0].EvnDirections;
        }
        if ( arguments[0].addingMsg ) {
			labelText = arguments[0].addingMsg + ' Включить данные услуги в направление?';
        }
        if ( arguments[0].onHideED && typeof arguments[0].onHideED == 'function') {
        	this.onHideED = arguments[0].onHideED;
        }
        
		if ( arguments[0].EvnDirection_id ) {
			this.EvnDirection_id = arguments[0].EvnDirection_id;
		}
		me.QuestionLabel.setHtml(labelText);

        var items = [{
            boxLabel: 'Не включать',
            inputValue: 0,
            name: 'AttributePatient',
            checked: true
        }];
        this.EvnDirections.forEach(function(item) {
            var at_time = ' в очередь';
            if(item.EvnDirection_setDate){
                at_time = ' на '+item.EvnDirection_setDate;
            }
            items.push({
                boxLabel: 'Направление № '+item.EvnDirection_Num + at_time + ' в службу '+item.MedService_Nick+'',
                inputValue: item.EvnDirection_id || me.EvnDirection_id,
                name: 'AttributePatient'
            });
        });

        var radio = Ext6.ComponentQuery.query('[refId=DIW_RadioButtonGroupContainer]');
        if(radio[0]) {
            radio[0].removeAll();
            radio[0].add(
                new Ext6.form.RadioGroup({
                    refId:'DIW_RadioButtonGroup',
                    xtype: 'radiogroup',
                    columns: 1,
                    items: items
                })
            );
        }
    },
    width: 550
});