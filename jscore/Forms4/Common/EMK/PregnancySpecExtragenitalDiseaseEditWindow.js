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
Ext6.define('common.EMK.PregnancySpecExtragenitalDiseaseEditWindow', {
	alias: 'widget.swPregnancySpecExtragenitalDiseaseEditWindowExt6',
	height: 195,
	closeToolText: 'Закрыть',
	closable: true,
	width: 588,
	cls: 'arm-window-new emk-forms-window',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(),
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	
	title: 'Экстрагенитальные заболевания',
	
	formMode: 'remote',
    formStatus: 'edit',
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array('PSEDEF_PSED_id', 'PSEDEF_PregnancySpec_id', 'PSEDEF_PSED_setDT', 'Diag_id');
		var i = 0;
		for (i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			} else {
				base_form.findField(form_fields[i]).disable();
			}
		}
		if (enable) {
			this.queryById('button_save').show();
		} else {
			this.queryById('button_save').hide();
		}
	},
	doSave: function(options){
        // options @Object
		// options.ignoreHeightIsIncorrect @Boolean Признак игнорирования проверки правильности ввода длины (роста)
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';
		var form = this.FormPanel;
		var base_form = form.getForm();
		if (!base_form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		for (var k in this.gridRecords) {
			if (!Ext6.isEmpty(this.gridRecords[k].Diag_id) && this.gridRecords[k].Diag_id == base_form.findField('Diag_id').getValue() && this.gridRecords[k].PSC_setDT == Ext.util.Format.date(base_form.findField('PSCEF_PSC_setDT').getValue(), 'd.m.Y') && this.gridRecords[k].PregnancySpecComplication_id != base_form.findField('PSCEF_PregnancySpecComplication_id').getValue()) {
				Ext6.Msg.alert(langs('Ошибка'), langs('В списке уже есть равнозначная запись'), function() { this.formStatus = 'edit'; }.createDelegate(this) );
				return false;
			}
		}
		var data = new Object();
		
		data.ExtragenitalDiseaseData = {
			'PSED_id'   : base_form.findField('PSEDEF_PSED_id').getValue(),
			'PregnancySpec_id'                      : base_form.findField('PSEDEF_PregnancySpec_id').getValue(),
			'PSED_setDT': base_form.findField('PSEDEF_PSED_setDT').getValue(),
			'Diag_id'                               : base_form.findField('Diag_id').getValue(),
			'Diag_Name'                             : base_form.findField('Diag_id').getRawValue()
		};
		this.callback(data);
		this.hide();
		return true;
    },
	
	show: function() {
		this.callParent(arguments);
		
		var win = this;
		
		win.taskButton.hide();
		
		this.center();
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.action = null;
		this.callback = Ext6.emptyFn;
		this.formStatus = 'edit';
		this.measureTypeExceptions = new Array();
		this.gridRecords = [];
		
		if (!arguments[0] || !arguments[0].formParams) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
				this.hide();
			}.createDelegate(this));
			return false;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].gridRecords) {
			this.gridRecords = arguments[0].gridRecords;
		}
		switch (this.action) {
			case 'add':				
				this.enableEdit(true);
				setCurrentDateTime({
					callback: function() {
						base_form.findField('PSEDEF_PSED_setDT').focus(true, 250);
					}.createDelegate(this),
					dateField: base_form.findField('PSEDEF_PSED_setDT'),
					loadMask: true,
					setDate: true,
					setDateMaxValue: true,
					windowId: this.id
				});
				break;
			case 'edit':
			case 'view':
				base_form.findField('PSEDEF_PSED_id').setValue(arguments[0].formParams['PSED_id']),
				base_form.findField('PSEDEF_PregnancySpec_id').setValue(arguments[0].formParams['PregnancySpec_id']);
				base_form.findField('PSEDEF_PSED_setDT').setValue(arguments[0].formParams['PSED_setDT']);
				var diag_id = arguments[0].formParams['Diag_id'];
				if (diag_id != null && diag_id.toString().length > 0) {
					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(record) {
								if (record.get('Diag_id') == diag_id) {
									base_form.findField('Diag_id').setValue(record.get('Diag_id'));
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}
				
				if (this.action == 'edit') {
					this.enableEdit(true);
					base_form.findField('PSEDEF_PSED_setDT').focus(true, 250);
				} else {
					this.enableEdit(false);
					this.buttons[this.buttons.length - 1].focus();
				}
				break;
			default:
				this.hide();
				break;
		}
	},
	initComponent: function() {
		var win = this;

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
				name: 'PSEDEF_PSED_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'PSEDEF_PregnancySpec_id',
				value: 0,
				xtype: 'hidden'
			},{
                allowBlank: false,
                fieldLabel: langs('Дата установки'),
                format: 'd.m.Y',
                name: 'PSEDEF_PSED_setDT',
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
                xtype: 'swDiagCombo'
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