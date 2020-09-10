/**
* Окно редактирования диагнозов для раздела беременность
* вызывается из контр.карт дисп.наблюдения (PersonDispEditWindow)
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      EMK
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.EMK.PersonDispDiagHistoryEditWindow', {
	alias: 'widget.swPersonDispDiagHistoryEditWindowExt6',
	height: 195,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 588,
	cls: 'arm-window-new emk-forms-window',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(),
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	
	title: 'Диагноз',
	
	formMode: 'remote',
    formStatus: 'edit',
	
	doSave: function(options){
        var form = this.FormPanel;
        var base_form = form.getForm();

        if ( !base_form.isValid() ) {
            Ext6.Msg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    form.getFirstInvalidEl().focus(false);
                }.createDelegate(this),
                icon: Ext6.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
        var DiagDispCard_id = this.DiagDispCard_id;
        var DiagDispCard_setDate = base_form.findField('DiagDispCard_Date').getValue();
        var Diag_id = base_form.findField('Diag_id').getValue();
        var PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
        var params = new Object();
        base_form.submit({
            failure: function(result_form, action) {
                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
                    }
                    else {
                        Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
                    }
                }
            }.createDelegate(this),
            params: params,
            success: function(result_form, action) {
                if ( action.result ) {
                    if ( action.result.DiagDispCard_id > 0 ) {
						this.callback();
                        this.hide();
                    }
                    else {
                        if ( action.result.Error_Msg ) {
                            Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
                        }
                        else {
                            Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
                        }
                    }
                }
                else {
                    Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
                }
            }.createDelegate(this)
        });
    },
	
	show: function() {
		this.callParent(arguments);
		
		var win = this;
		
		win.taskButton.hide();
		
		this.center();
        var base_form = this.FormPanel.getForm();
        base_form.reset();

        this.action = null;
        this.DiagDispCard_id = null;
        this.callback = Ext6.emptyFn;
		if (!arguments[0]) {
			Ext6.Msg.show(
			{
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Неверные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
                    win.hide();
				}
			});
		}
        if(arguments[0].action)
            this.action = arguments[0].action;
        if(arguments[0].callback)
			this.callback = arguments[0].callback;
        this.PersonDisp_id = arguments[0].PersonDisp_id;
        this.sicknessDiagStore.load();

        switch ( this.action ) {
            case 'add':
                base_form.findField('Diag_id').enable();
                base_form.findField('DiagDispCard_Date').enable();
                this.queryById('button_save').show();
                setCurrentDateTime({
                    callback: function() {
                        base_form.clearInvalid();
                        base_form.findField('DiagDispCard_Date').focus(true, 250);
                    }.createDelegate(this),
                    dateField: base_form.findField('DiagDispCard_Date'),
                    setDate: true,
                    windowId: this.id
                });
                base_form.findField('PersonDisp_id').setValue(this.PersonDisp_id);
                break;
            case 'edit':
            case 'view':
                if(this.action=='edit'){
                    base_form.findField('Diag_id').enable();
                    base_form.findField('DiagDispCard_Date').enable();
                    this.queryById('button_save').show();
                }
                else{
                    base_form.findField('Diag_id').disable();
                    base_form.findField('DiagDispCard_Date').disable();
                    this.queryById('button_save').hide();
                }
                var DiagDispCard_id = arguments[0].DiagDispCard_id;
                this.DiagDispCard_id = DiagDispCard_id;
                base_form.load({
                    failure: function() {
                        Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
                    }.createDelegate(this),
                    params: {
                        'DiagDispCard_id': DiagDispCard_id
                    },
                    success: function() {
                        
                        base_form.findField('Diag_id').getStore().load({
                            params: {
                                where:'where Diag_id = '+base_form.findField('Diag_id').getValue()
                            },
                            callback: function() {
								base_form.findField('Diag_id').setValue( base_form.findField('Diag_id').getValue() );
							}
                        });
                        base_form.clearInvalid();
                    }.createDelegate(this),
                    url: '/?c=PersonDisp&m=loadDiagDispCardEditForm'
                });
                break;
            default:
                this.hide();
                break;
        }		
	},
	initComponent: function() {
		var win = this;

		this.sicknessDiagStore = new Ext.db.AdapterStore({
            autoLoad: false,
            dbFile: 'Promed.db',
            fields: [
				{ name: 'SicknessDiag_id', type: 'int' },
				{ name: 'Sickness_id', type: 'int' },
				{ name: 'Sickness_Code', type: 'int' },
				{ name: 'PrivilegeType_id', type: 'int' },
				{ name: 'Sickness_Name', type: 'string' },
				{ name: 'Diag_id', type: 'int' },
				{ name: 'SicknessDiag_begDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'SicknessDiag_endDT', type: 'date', dateFormat: 'd.m.Y' }
            ],
            key: 'SicknessDiag_id',
            sortInfo: {
                field: 'Diag_id'
            },
            tableName: 'SicknessDiag'
        });
        
        win.FormPanel = new Ext6.form.FormPanel({
            border: false,
            bodyPadding: '25 25 25 30',
			region: 'center',
            reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{ name: 'DiagDispCard_id' },
						{ name: 'DiagDispCard_Date' },
						{ name: 'Diag_id' },
						{ name: 'PersonDisp_id' }
					]
				})
			}),
            url: '/?c=PersonDisp&m=saveDiagDispCard',

            items: [{
                name: 'PersonDisp_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'DiagDispCard_id',
                value: 0,
                xtype: 'hidden'
            }, {
                allowBlank: false,
                fieldLabel: langs('Дата установки'),
                format: 'd.m.Y',
                name: 'DiagDispCard_Date',
                plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
                selectOnFocus: true,
                tabIndex: 0,
                width: 123+123,
                labelWidth: 123,
                xtype: 'datefield'
            }, {
                allowBlank: false,
                name: 'Diag_id',
                userCls: 'diagnoz',
                tabIndex: 1,
                labelWidth: 123,
                width: 123+350,
                xtype: 'swDiagCombo',
                displayField: 'Diag_Name',
                valueField: 'Diag_id',
                listeners: {
                    'change': function(combo, newValue, oldValue) {
                        var sickness_diag_store = win.sicknessDiagStore;
                        var sickness_id = null;
                        var idx = -1;
                        if (newValue) {
                            idx = sickness_diag_store.findBy(function(record) {
                                if (record.get('Diag_id') == newValue) {
                                    sickness_id = record.get('Sickness_id');
                                    return true;
                                }
                            });
                            if(combo.getStore().getCount()>0) {
								if ((idx>=0) && (sickness_id != null)) {
									if(sickness_id.toString() != '9'){
										Ext6.Msg.alert(langs('Ошибка'), langs('Выбранный диагноз не относится к группе заболеваний по беременности и родам'));
										combo.setValue('');
									}
								}
								else{
									Ext6.Msg.alert(langs('Ошибка'), langs('Выбранный диагноз не относится к группе заболеваний по беременности и родам'));
									combo.setValue('');
								}
							}
                        }
                    }.createDelegate(this)
                }
            }]
        });
        Ext6.apply(this, {
            buttons: [{
				xtype: 'SimpleButton',
				text: langs('ОТМЕНА'),
				itemId: 'button_cancel'
			},
			{
				xtype: 'SubmitButton',
				text: langs('ПРИМЕНИТЬ'),
				itemId: 'button_save'
			}],
            items: [
                this.FormPanel
            ],
            layout: 'border'
        });

		this.callParent(arguments);
	}
});