/**
 * swOnkoConsultEditWindow - Редактирование консилиума
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.MorbusOnko.swOnkoConsultEditWindow', {
	/* свойства */
	alias: 'widget.swOnkoConsultEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'OnkoConsulteditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Сведения о проведении консилиума',
	width: 600,

	/* методы */
	save: function () {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}

		win.mask(LOAD_WAIT_SAVE);

		base_form.submit({
			success: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) ) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					return false;
				}

				win.callback();
				win.hide();
			},
			failure: function(form, action) {
				win.unmask();
			}
		});
	},
	onSprLoad: function(arguments) {

		var win = this;

		win.action = (typeof arguments[0].action == 'string' ? arguments[0].action : 'add');
		win.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext6.emptyFn);
		win.formParams = (typeof arguments[0].formParams == 'object' ? arguments[0].formParams : {});

		win.center();
		win.setTitle('Сведения о проведении консилиума');
		
		if (arguments[0]['OnkoConsult_id']) {
			this.OnkoConsult_id = arguments[0]['OnkoConsult_id'];
		} else {
			this.OnkoConsult_id = null;
		}
		
		if (arguments[0]['MorbusOnko_id']) {
			this.MorbusOnko_id = arguments[0]['MorbusOnko_id'];
		} else {
			this.MorbusOnko_id = null;
		}
		
		if (arguments[0]['MorbusOnkoVizitPLDop_id']) {
			this.MorbusOnkoVizitPLDop_id = arguments[0]['MorbusOnkoVizitPLDop_id'];
		} else {
			this.MorbusOnkoVizitPLDop_id = null;
		}
		
		if (arguments[0]['MorbusOnkoDiagPLStom_id']) {
			this.MorbusOnkoDiagPLStom_id = arguments[0]['MorbusOnkoDiagPLStom_id'];
		} else {
			this.MorbusOnkoDiagPLStom_id = null;
		}
		
		if (arguments[0]['MorbusOnkoLeave_id']) {
			this.MorbusOnkoLeave_id = arguments[0]['MorbusOnkoLeave_id'];
		} else {
			this.MorbusOnkoLeave_id = null;
		}
		
		if (arguments[0]['EvnVizitPL_id']) {
			this.EvnVizitPL_id = arguments[0]['EvnVizitPL_id'];
		}

		if(arguments[0]['EvnSection_id']) {
			this.EvnSection_id = arguments[0]['EvnSection_id'];
		} else {
			this.EvnSection_id = null;
		}

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(win.formParams);

		switch ( win.action ) {
			case 'add':
				win.setTitle(win.getTitle() + ': Добавление');
				base_form.findField('OnkoConsult_consDate').focus();
				base_form.isValid();
				break;

			case 'edit':
				win.setTitle(win.getTitle() + ': Редактирование');

				win.mask(LOAD_WAIT);

				base_form.load({
					url: '/?c=OnkoConsult&m=load',
					params: {
						OnkoConsult_id: base_form.findField('OnkoConsult_id').getValue()
					},
					success: function(form, action) {
						win.unmask();
						base_form.findField('OnkoConsult_consDate').focus();
						base_form.isValid();
					},
					failure: function() {
						win.unmask();
					}
				});
				break;
		}

		if(this.EvnSection_id) {
            var params = {
                EvnSection_id: win.EvnSection_id
            };
        } else {
            var params = {
                EvnVizitPL_id: win.EvnVizitPL_id
            };
        }
		//фильтруем поле "Тип лечения" по дате окончания случая лечения, либо по текущей дате если лечение не окончено.
        Ext6.Ajax.request({
            url: '/?c=EvnPL&m=getLastVizitDT',
            params: params,
            success: function (response) {
				if (!response.responseText) return false;
				
                var result = Ext.util.JSON.decode(response.responseText);
                var endTreatDate;

                if(!result.endTreatDate) {
                    var dateStr = getGlobalOptions().date;
                    var d = dateStr.substr(0, 2);
                    var m = dateStr.substr(3, 2) - 1;
                    var y = dateStr.substr(6); 
                    endTreatDate = new Date(y, m, d);
                } else {
                    endTreatDate = new Date(result.endTreatDate.date);
                    endTreatDate.setHours(0, 0, 0, 0); // фильтруем только по дате
                }
                base_form.findField('OnkoHealType_id').getStore().clearFilter();
                base_form.findField('OnkoHealType_id').getStore().filterBy(function(rec) {
                    var uslugaBegDate,
                        uslugaEndDate,
                        dateArr;
                    if(rec.get('OnkoHealType_begDT')) {
                		dateArr = rec.get('OnkoHealType_begDT').split('.');
                		uslugaBegDate = new Date(dateArr[2], dateArr[1] - 1, dateArr[0]);
                    	
                    }

                    if(rec.get('OnkoHealType_endDT')) {
                    	dateArr = rec.get('OnkoHealType_endDT').split('.');
                    	uslugaEndDate = new Date(dateArr[2], dateArr[1] - 1, dateArr[0]);
                    }
                    return (
                        (!uslugaEndDate || uslugaEndDate >= endTreatDate)
                        && (!uslugaBegDate || uslugaBegDate <= endTreatDate)
                    );
                });
                base_form.findField('OnkoHealType_id').lastQuery = '';
            }
        });
	},
	
	setDisabledFields: function(disable) {
		var win = this,
			form = win.FormPanel.getForm(),
			fields = ['OnkoHealType_id', 'OnkoConsult_consDate'];

		fields.forEach(function(fieldName) {
			var field = form.findField(fieldName);
			field.setDisabled(disable);
			if(disable)
				field.setValue(null);
			if(win.action != 'view')
				field.fireEvent('change', field);
		})
	},

	show: function() {
		this.callParent(arguments);
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model',
			fields: [
				{ name: 'OnkoConsult_id' },
				{ name: 'MorbusOnko_id' },
				{ name: 'MorbusOnkoVizitPLDop_id' },
				{ name: 'MorbusOnkoLeave_id' },
				{ name: 'MorbusOnkoDiagPLStom_id' },
				{ name: 'OnkoConsult_consDate' },
				{ name: 'OnkoHealType_id' },
				{ name: 'OnkoConsultResult_id' }
			]
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			bodyStyle: 'padding: 5px;',
			defaults: {
				labelAlign: 'right',
				labelWidth: 150
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=OnkoConsult&m=save',
			items: [{
				name: 'OnkoConsult_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MorbusOnko_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoVizitPLDop_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoLeave_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoDiagPLStom_id',
				value: 0,
				xtype: 'hidden'
			}, {
				fieldLabel: 'Дата проведения',
				width: 300,
				allowBlank: false,
				name: 'OnkoConsult_consDate',
				xtype: 'datefield'
			}, {
				comboSubject: 'OnkoHealType',
				fieldLabel: 'Тип лечения',
				name: 'OnkoHealType_id',
				width: 500,
				xtype: 'commonSprCombo'
			}, {
				allowBlank: false,
				comboSubject: 'OnkoConsultResult',
				fieldLabel: 'Результат проведения',
				name: 'OnkoConsultResult_id',
				lastQuery: '',
				width: 500,
				xtype: 'commonSprCombo',
				listeners: {
					change: function(cmp, rec){
						if(getRegionNick() == 'kz') return;

						var disable = cmp.getFieldValue('OnkoConsultResult_Code') === '0' || cmp.getFieldValue('OnkoConsultResult_Code') == 4;

						win.setDisabledFields(disable);
					}
				}
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