/**
* Форма для редактирования данных о количестве единиц учета в упаковке
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Alexander Kurakin
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      2016
*/

sw.Promed.swDrugPrepEdUcCountEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 540,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swDrugPrepEdUcCountEditWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},

    setDefaultValues: function() {
        var base_form = this.Form.getForm();
        var unit_field = base_form.findField('GoodsUnit_id');
        var count_field = base_form.findField('DrugPrepEdUcCount_Count');

        if (Ext.isEmpty(unit_field.getValue())) {
            unit_field.getStore().findBy(function(record) {
                if (record.get('GoodsUnit_Name') == 'упаковка') { //кодов в справочнике нету, поэтому вот такое странное решение
                    unit_field.setValue(record.get('GoodsUnit_id'));
                    return true;
                }
            });
        }
        if (Ext.isEmpty(count_field.getValue())) {
            count_field.setValue(1);
        }
    },
	
	show: function() {
		sw.Promed.swDrugPrepEdUcCountEditWindow.superclass.show.apply(this, arguments);

		if( !arguments[0] || !arguments[0].action ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}

		this.isSigned = null;
		
		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		
		this.action = arguments[0].action;
		
		this.setTitle('Количество единиц учета в упаковке: ' + this.getActionName(this.action));

		var bf = this.Form.getForm();
		
		//bf.findField('GoodsUnit_id').setAllowBlank(true);
		
		if(this.action != 'add'){
			bf.setValues(arguments[0].owner.getGrid().getSelectionModel().getSelected().data);
		} else {
			bf.setValues(arguments[0]);
            this.setDefaultValues();
			if(!arguments[0].Drug_id && this.owner ){
				bf.findField('Drug_id').setValue(this.owner.ViewGridStore.baseParams.Drug_id);
			}
		}

		this.disableFields(this.action == 'view');
		this.buttons[0].setDisabled( this.action == 'view' );

		this.center();
	},
	
	getActionName: function(action) {
		return {
			add: lang['dobavlenie'],
			edit: lang['redaktirovanie'],
			view: lang['prosmotr']
		}[action];
	},
	
	doSave: function() {
		var bf = this.Form.getForm();
		if( !bf.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
			return false;
		}

		var params = new Object();

		bf.submit({
			scope: this,
			params: params,
			failure: function(form,act) {
				var response_obj = Ext.util.JSON.decode(act.response.responseText);
				if(response_obj.success == false && response_obj.Error_Code == 400) {
					sw.swMsg.alert(lang['oshibka'], 'Данные не могут быть сохранены, т.к. эта единица учета уже есть в справочнике');
					return false;
				} else if(response_obj.success == false) {alert();
					sw.swMsg.alert(lang['oshibka'], 'Не удалось сохранить запись');
					return false;
				} 
			},
			success: function(form, act) {
				if(this.owner)
					this.owner.getAction('action_refresh').execute();
				this.hide();
			}
		});
	},
	
	disableFields: function(s) {
		this.Form.findBy(function(f) {
			if( f.xtype && f.xtype != 'hidden' ) {
				f.setDisabled(s);
			}
		});
	},
	
	initComponent: function() {

		this.Form = new Ext.FormPanel({
			url: '/?c=DrugNomen&m=saveDrugPrepEdUcCount',
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 150,
			items: [{
                xtype: 'hidden',
                name: 'Org_id'
            }, {
				xtype: 'hidden',
				name: 'DrugPrepEdUcCount_id'
			}, {
				xtype: 'hidden',
				name: 'DrugPrepFas_id'
			}, {
				xtype: 'hidden',
				name: 'Drug_id'
			}, {
				layout: 'form',
				items: [{
					xtype: 'swcommonsprcombo',
					editable: true,
					width: 195,
					allowBlank: false,
					comboSubject: 'GoodsUnit',
					fieldLabel: 'Ед. изм. товара'
				}]
			}, {
				layout: 'form',
				items: [{
					xtype: 'numberfield',
					allowDecimals: true,
					decimalPrecision: 3,
					maxLength: 12,
                    minValue: 0.001,
					width: 195,
					allowBlank: false,
					name: 'DrugPrepEdUcCount_Count',
					fieldLabel: 'Кол-во товара в уп.'
				}]
			}]
		});
		
		Ext.apply(this, {
			items: [this.Form],
			buttons: [{
				handler: this.doSave,
				scope: this,
				iconCls: 'save16',
				text: lang['sohranit']
			},
			'-',
			HelpButton(this),
			{
				text: lang['otmena'],
				tabIndex: -1,
				tooltip: lang['otmena'],
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}]
		});
		sw.Promed.swDrugPrepEdUcCountEditWindow.superclass.initComponent.apply(this, arguments);
	}
});