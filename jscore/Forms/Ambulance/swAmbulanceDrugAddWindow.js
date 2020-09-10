/**
* swAmbulanceDrugAddWindow - окно просмотра, добавления и редактирования медикаментов для карты вызова
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd
* @author       Pshenitcyn Ivan aka IVP (ipshon@rambler.ru)
* @version      14.01.2010
* @comment      Префикс для id компонентов ACDAW (AmbulanceDrugAddWindow)
*/

sw.Promed.swAmbulanceDrugAddWindow = Ext.extend(sw.Promed.BaseForm, {
	layout      : 'fit',
	width       : 600,
	modal: true,
	resizable: false,
	draggable: false,
	bodyStyle:'padding:2px',
	buttonAlign: 'left',
	autoHeight: true,
	closeAction : 'hide',
	plain       : true,
	id: 'ambulance_drug_add_window',
	callback: function(owner) {},
	enableEdit: function( enable ) {
		var form = this.findById('ambulance_drug_add_form');
		if (!enable)
		{
			form.disable();
			this.buttons[1].enable();
			this.buttons[2].enable();
		}
		else
		{
			form.enable();
			Ext.getCmp('ACDAF_Price').disable();
			Ext.getCmp('ACDAF_Summ').disable();
		}
	},
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	onHide: function() {},
	doSave: function() {
		var form_panel = this.findById('ambulance_drug_add_form');
		var form = form_panel.getForm();
		var current_window = this;

		if ( !form.isValid() )
		{
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi'], function() {
				var first_invalid_el = form_panel.getFirstInvalidEl();
				if ( first_invalid_el != null )
					first_invalid_el.focus();
			});
			return;
		}

		// если нам не передали AmbulanceCard_id, то нам самим надо сохранить форму
		if ( this.params.AmbulanceCard_id > 0 )
		{
			var loadMask = new Ext.LoadMask(Ext.get('ambulance_drug_add_window'), { msg: "Подождите, идет сохранение..." });
    		loadMask.show();
        	var params = {action: this.params.action};
			params.AmbulanceCard_id = this.params.AmbulanceCard_id;
           	form.submit({
           		params: params,
				success: function() {
					loadMask.hide();
					current_window.hide();
					current_window.callback( {medicamentWasSaved: true} );
				},
				failure: function(result_form, action) {
					loadMask.hide();
                },
				url: C_PERSONAMBMED_SAVE
			});
		}
		else
		{
			Ext.getCmp('PDDAF_Price').enable();
			Ext.getCmp('PDDAF_Summ').enable();

			this.callback( form.getValues() );

			Ext.getCmp('PDDAF_Price').disable();
			Ext.getCmp('PDDAF_Summ').disable();
			this.hide();
		}
	},
	title: lang['naznachenie_medikamentyi'],
	show: function() {
		sw.Promed.swAmbulanceDrugAddWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		/*
		this.drugMnnSearchWindow = getWnd('swDrugMnnSearchWindow');
		this.drugOstatReceptForm = getWnd('swDrugOstatReceptWindow');
		this.drugTorgSearchWindow = getWnd('swDrugTorgSearchWindow');
		*/
		if ( arguments[0] )
		{
			if ( arguments[0].callback )
				this.callback = arguments[0].callback;
			else
				this.callback = function() {};
			if (arguments[0].onHide)
				this.onHide = arguments[0].onHide;
			else
				this.onHide = function() {};
			if ( arguments[0].params )
				this.params = arguments[0].params;
		}
		var form = this.findById('ambulance_drug_add_form').getForm();
		form.reset();
//		this.findById('person_disp_drug_add_form').enable();

		drug_mnn_combo = Ext.getCmp('PDDAF_DrugMnnCombo');

		drug_mnn_combo.getStore().proxy.table = drug_mnn_combo.getStore().proxy.conn.getTable('DrugDisp', '');
		drug_mnn_combo.getStore().tableName = 'DrugDisp';

		drug_mnn_combo.clearBaseFilter();
		drug_mnn_combo.lastQuery = '';
		drug_mnn_combo.getStore().load();
    	drug_mnn_combo.setBaseFilter(function(record, id) {
			if ( record.data.PrivilegeType_id == current_window.params.PrivilegeType_id )
			{
				return true
			}
			else
			{
				return false
			}
	    });

		if ( this.params && this.params.action )
			switch (this.params.action)
			{
				case 'add':
					Ext.getCmp('PDDAF_DrugMnnCombo').focus(100, true);
					this.setTitle(lang['medikamentyi_dobavlenie']);
					this.enableEdit(true);
				break;
				case 'edit':
					Ext.getCmp('PDDAF_DrugMnnCombo').focus(100, true);
					this.setTitle(lang['medikamentyi_redaktirovanie']);
					this.enableEdit(true);
					this.initEditingData();
				break;
				case 'view':
					this.setTitle(lang['medikamentyi_redaktirovanie']);
					this.initEditingData();
					this.enableEdit(false);
				break;
			}
	},
	initEditingData: function()
	{
		this.findById('PDDAF_PersonDispMedicament_id').setValue(this.params.medicament_data.PersonDispMedicament_id);
		var form = this.findById('person_disp_drug_add_form').getForm();
		var drug_mnn_combo = form.findField('DrugMnn_id');
		var drug_mnn_id = this.params.medicament_data.DrugMnn_id
		drug_mnn_combo.setValue(drug_mnn_id);
		/*
			передается дополнительный последний параметр,
			который указывает на то, что не требуется загрузка связанного комбобокса
			мы его будем загружать сами
		*/
		drug_mnn_combo.fireEvent('change', drug_mnn_combo, drug_mnn_id, 0, true);
		// загружаем сторе у комбобокса и в каллбэке задаем значение
		var drug_combo = form.findField('Drug_id');
		var drug_id = this.params.medicament_data.Drug_id
		var course_combo = form.findField('Course');
		var drug_count = this.params.medicament_data.Drug_Count;
		drug_combo.getStore().proxy.conn.url = C_PERSONDISPMED_DRUGLIST;
		drug_combo.getStore().load({
              	callback: function() {
				drug_combo.setValue(drug_id);
				var idx = drug_combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == drug_id; });
				var record = drug_combo.getStore().getAt(idx);
				drug_combo.fireEvent('beforeselect', drug_combo, record, idx);
				course_combo.setValue(drug_count);
  					var summ_price_field = Ext.getCmp('PDDAF_Summ');
				summ_price_field.setValue(drug_count*record.data.Drug_Price);
              	}
		});
		form.findField('Course_begDate').setValue(this.params.medicament_data.PersonDispMedicament_begDate);
		form.findField('Course_endDate').setValue(this.params.medicament_data.PersonDispMedicament_endDate);
	},
	initComponent: function() {
		Ext.apply(this, {
			buttons: [
				{
					id: 'PDDAF_SaveButton',
					text: BTN_FRMSAVE,
					iconCls: 'save16',
					handler: this.doSave.createDelegate(this),
                    tabIndex: 5002
				},
				{
					text: '-'
				},
				HelpButton(this, 5003),
				{
					id: 'PDDAF_CancelButton',
					text: BTN_FRMCANCEL,
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, []),
					tabIndex: 5004
				}
			],
			items: [ new Ext.form.FormPanel({
				frame: true,
				height: 150,
				labelAlign: 'right',
				id: 'ambulance_drug_add_form',
				labelWidth: 125,
				buttonAlign: 'left',
				bodyStyle:'padding:2px',
				url: C_AMB_DRUG_SAVE,
				reader : new Ext.data.JsonReader({
					success: function() {alert('All Right!')}
				}, [
						{name: 'AmbulanceCard_id'},
						{name: 'RJON'},
						{name: 'CITY'}
				]),
				items: [{
					xtype: 'swcmpdrugcombo',
					width: 250							
				}],
					buttonAlign : "left"
				})
			],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;
					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J)
					{
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C)
					{
						this.submit();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swAmbulanceDrugAddWindow.superclass.initComponent.apply(this, arguments);
	}
});