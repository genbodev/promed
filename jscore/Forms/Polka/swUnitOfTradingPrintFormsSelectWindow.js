/**
* swUnitOfTradingPrintFormsSelectWindow - окно выбора печатных форм лота
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Alexander Kurakin
* @version      03.2016
*/
/*NO PARSE JSON*/

sw.Promed.swUnitOfTradingPrintFormsSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUnitOfTradingPrintFormsSelectWindow',
	objectSrc: '/jscore/Forms/Common/swUnitOfTradingPrintFormsSelectWindow.js',

	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closeAction : 'hide',
	draggable: false,
	id: 'swUnitOfTradingPrintFormsSelectWindow',
	layout: 'form',
	modal: true,
	plain: true,
	resizable: false,
	listeners: {
		'hide': function() {
			
		}
	},
    checkSelected: function() {
        var base_form = this.FormPanel.getForm();
        var field_array = ['form1', 'form2', 'form3', 'form4', 'form5', 'form6', 'form7'];

        this.buttons[0].disable();

        for (i = 0; i < field_array.length; i++) {
            if (base_form.findField(field_array[i]).getValue() == true) {
                this.buttons[0].enable();
                break;
            }
        }
    },
	doPrint: function() {
		var base_form = this.FormPanel.getForm();

		if(false){
			sw.swMsg.alert('Ошибка', '');
			return false;
		}
		var WhsDocumentUc_id = base_form.findField('WhsDocumentUc_id').getValue();
		var DrugRequest_id = this.DrugRequest_id;
		var Org_id = getGlobalOptions().org_id;

		if(base_form.findField('form1').getValue() == true) { //Запрос на предоставление коммерческого предложения
            printBirt({
                'Report_FileName': 'WhsDocumentProcurementRequestSpec_print.rptdesign',
                'Report_Params': '&paramWhsDocumentUc=' + WhsDocumentUc_id,
                'Report_Format': 'doc'
            });
        }

        if(base_form.findField('form2').getValue() == true) { //Основные условия исполнения контракта
            printBirt({
                'Report_FileName': 'WhsDocument_conditions.rptdesign',
                'Report_Params': '&paramWhsDocumentUc=' + WhsDocumentUc_id,
                'Report_Format': 'doc'
            });
        }

        if(base_form.findField('form3').getValue() == true || base_form.findField('form6').getValue() == true) { //Описание объекта закупки
            printBirt({
                'Report_FileName': 'WhsDocument_PurchObj.rptdesign',
                'Report_Params': '&paramWhsDocumentUc=' + WhsDocumentUc_id,
                'Report_Format': 'doc'
            });
        }

        if(base_form.findField('form4').getValue() == true) { //Заявка на определение поставщика
            printBirt({
                'Report_FileName': 'pan_LLO_RequestProvider.rptdesign',
                'Report_Params': '&paramWhsDocumentUc=' + WhsDocumentUc_id + '&paramDrugRequest=' + DrugRequest_id,
                'Report_Format': 'doc'
            });
        }

        if(base_form.findField('form5').getValue() == true) { //Информационная карта заявки
            printBirt({
                'Report_FileName': 'PurchInformCard.rptdesign',
                'Report_Params': '&paramWhsDocumentUc=' + WhsDocumentUc_id + '&paramOrg=' + Org_id,
                'Report_Format': 'doc'
            });
        }

		if(base_form.findField('form7').getValue() == true) {
			var id_salt = Math.random();
            var win_id = 'print_rational' + Math.floor(id_salt * 10000);
            var win = window.open('/?c=UnitOfTrading&m=printLotRational&lot_id=' + WhsDocumentUc_id, win_id);
		}

		this.hide();

	},
	
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: '',
			labelAlign: 'right',
			labelWidth: 20,

			items: [{
				xtype: 'hidden',
				name: 'WhsDocumentUc_id'
			}, {
				layout: 'form',
				labelWidth: 10,
				items: [{
					boxLabel: 'Запрос на предоставление коммерческого предложения',
					hideLabel: true,
	                xtype: 'swcheckbox',
	                name: 'form1',
	                listeners: {
	                	'check': function (comp,newvalue) {
	                		var form = this.FormPanel.getForm();
							if(newvalue == true){
								form.findField('form2').setValue(true);
								form.findField('form3').setValue(true);
							}
                            this.checkSelected();
						}.createDelegate(this)
	                }
                }]
			}, {
				boxLabel: 'Приложение № 1. Основные условия исполнения контракта',
                labelSeparator: null,
                xtype: 'swcheckbox',
                name: 'form2',
                listeners: {
                    'check': function (comp,newvalue) {
                        this.checkSelected();
                    }.createDelegate(this)
                }
			}, {
				boxLabel: 'Приложение № 2. Описание объекта закупки',
                labelSeparator: null,
                xtype: 'swcheckbox',
                name: 'form3',
                listeners: {
                    'check': function (comp,newvalue) {
                        this.checkSelected();
                    }.createDelegate(this)
                }
			}, {
				layout: 'form',
				labelWidth: 10,
				items: [{
					boxLabel: 'Заявка на определение поставщика',
                    hideLabel: true,
		            xtype: 'swcheckbox',
		            name: 'form4',
		            listeners: {
	                	'check': function (comp,newvalue) {
	                		var form = this.FormPanel.getForm();
							if(newvalue == true){
								form.findField('form5').setValue(true);
								form.findField('form6').setValue(true);
								form.findField('form7').setValue(true);
							}
                            this.checkSelected();
						}.createDelegate(this)
	                }
                }]
			}, {
				boxLabel: 'Приложение № 1. Информационная карта заявки',
                labelSeparator: null,
                xtype: 'swcheckbox',
                name: 'form5',
                listeners: {
                    'check': function (comp,newvalue) {
                        this.checkSelected();
                    }.createDelegate(this)
                }
			}, {
				boxLabel: 'Приложение № 2. Описание объекта закупки',
                labelSeparator: null,
                xtype: 'swcheckbox',
                name: 'form6',
                listeners: {
                    'check': function (comp,newvalue) {
                        this.checkSelected();
                    }.createDelegate(this)
                }
			}, {
				boxLabel: 'Приложение № 3. Обоснование начальной (максимальной) цены контракта (лота)',
                labelSeparator: null,
                xtype: 'swcheckbox',
                name: 'form7',
                listeners: {
                    'check': function (comp,newvalue) {
                        this.checkSelected();
                    }.createDelegate(this)
                }
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doPrint();
				}.createDelegate(this),
				iconCls: 'ok16',
				text: 'Печать'
			}, {
				text: '-'
			},
            HelpButton(this, 0),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swUnitOfTradingPrintFormsSelectWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swUnitOfTradingPrintFormsSelectWindow.superclass.show.apply(this, arguments);

		if( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		if( !arguments[0].WhsDocumentUc_id ) {
			sw.swMsg.alert(lang['oshibka'], 'Не передан идентификатор лота!');
			this.hide();
			return false;
		}

		this.action = '';
		this.callback = Ext.emptyFn;
		this.fields = {};
		this.FormPanel.getForm().reset();
		this.DrugRequest_id = null;
        this.checkSelected();

		var base_form = this.FormPanel.getForm();
		
		if ( arguments[0] ) {

			if ( arguments[0].action ) {
				this.action = arguments[0].action;
			}

			if ( arguments[0].callback ) {
				this.callback = arguments[0].callback;
			}

			if ( arguments[0].onClose ) {
				this.onWinClose = arguments[0].onClose;
			}

			if ( arguments[0].DrugRequest_id > 0 ) {
				this.DrugRequest_id = arguments[0].DrugRequest_id;
			}

			base_form.setValues(arguments[0]);
		}
			
	},
	title: 'Выбор печатных форм лота',
	width: 450
});