/**
* swPersonDispDrugAddWindow - окно просмотра, добавления и редактирования медикаментов для человека в ДУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd
* @author       Pshenitcyn Ivan aka IVP (ipshon@rambler.ru)
* @version      03.05.2009
* @comment      Префикс для id компонентов PDDAF (PersonDispDrugAddForm)
*               tabIndex: 601
*/

sw.Promed.swPersonDispDrugAddWindow = Ext.extend(sw.Promed.BaseForm, {
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
	id: 'person_disp_drug_add_window',
	callback: function(owner) {},
	enableEdit: function( enable ) {
		var form = this.findById('person_disp_drug_add_form');
		if (!enable)
		{
			form.disable();
			this.findById('person_disp_drug_add_form').buttons[1].enable();
			this.findById('person_disp_drug_add_form').buttons[2].enable();
		}
		else
		{
			form.enable();
			Ext.getCmp('PDDAF_Price').disable();
			Ext.getCmp('PDDAF_Summ').disable();
		}
	},
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	onHide: function() {},
	doSave: function() {
		var form_panel = this.findById('person_disp_drug_add_form');
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

		if ( (form.findField('Course_endDate').getValue() != '') && (form.findField('Course_begDate').getValue() > form.findField('Course_endDate').getValue()) )
		{
			Ext.Msg.alert(lang['oshibka'], lang['data_nachala_kursa_doljna_byit_ne_pozje_datyi_okonchaniya_kursa'], function() {
				form.findField('Course_begDate').focus();
			});
			return;
		}
			
		if ( (form.findField('Course_begDate').getValue() != '') && (this.PersonDisp_begDate != '') && (form.findField('Course_begDate').getValue() < this.PersonDisp_begDate) )
		{
			Ext.Msg.alert(lang['oshibka'], lang['data_nachala_kursa_lecheniya_ne_mojet_byit_ranshe_datyi_vzyatiya_na_uchet'], function() {
				form.findField('Course_begDate').focus();
			});
			return;
		}

		// если нам передали PersonDisp_id, то нам самим надо сохранить форму
		if ( this.params.PersonDisp_id > 0 )
		{
			var loadMask = new Ext.LoadMask(Ext.get('person_disp_drug_add_window'), { msg: "Подождите, идет сохранение..." });
    		loadMask.show();
        	var params = {action: this.params.action};
			params.PersonDisp_id = this.params.PersonDisp_id;
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
				url: C_PERSONDISPMED_SAVE
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
		sw.Promed.swPersonDispDrugAddWindow.superclass.show.apply(this, arguments);

		var current_window = this;

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
		var form = this.findById('person_disp_drug_add_form').getForm();
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
		
		this.PersonDisp_begDate = null;

		if ( this.params && this.params.action )
			switch (this.params.action)
			{
				case 'add':
					Ext.getCmp('PDDAF_DrugMnnCombo').focus(100, true);
					this.setTitle(lang['medikamentyi_dobavlenie']);
					// дата начала льготы
					this.PersonDisp_begDate = this.params.PersonDisp_begDate;
					this.enableEdit(true);
				break;
				case 'edit':
					Ext.getCmp('PDDAF_DrugMnnCombo').focus(100, true);
					this.setTitle(lang['medikamentyi_redaktirovanie']);
					// дата начала льготы
					this.PersonDisp_begDate = this.params.PersonDisp_begDate;
					this.enableEdit(true);
					this.initEditingData();
				break;
				case 'view':
					this.setTitle(lang['medikamentyi_prosmotr']);
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
			items: [
				new Ext.form.FormPanel({
					frame: true,
					autoHeight: true,
					labelAlign: 'right',
					id: 'person_disp_drug_add_form',
					labelWidth: 145,
					buttonAlign: 'left',
					bodyStyle:'padding: 5px',
					items: [{
							id: 'PDDAF_PersonDispMedicament_id',
							name: 'PersonDispMedicament_id',
							value: 0,
                    		xtype: 'hidden'
                    	}, {
						hiddenName: 'DrugMnn_id',
                        id: 'PDDAF_DrugMnnCombo',
						onTrigger2Click: function() {
/*							var drug_mnn_combo = this;
							var current_window = Ext.getCmp('person_disp_drug_add_window');

							getWnd('swDrugMnnSearchWindow').show({
								onClose: function() {
									drug_mnn_combo.focus(false);
								},
								onSelect: function(drugMnnData) {
									drug_mnn_combo.setValue(drugMnnData.DrugMnn_id);
									var index = drug_mnn_combo.getStore().findBy(function(rec) { return rec.get('DrugMnn_id') == drugMnnData.DrugMnn_id; });
									var record = drug_mnn_combo.getStore().getAt(index);

									if ( record )
									{
										drug_mnn_combo.fireEvent('change', drug_mnn_combo, drugMnnData.DrugMnn_id, 0);
									}

									getWnd('swDrugMnnSearchWindow').hide();
									drug_mnn_combo.focus(false);
                   				},
								PrivilegeType_Code: current_window.params.PrivilegeType_id
                			});*/
						},
						lastQuery: '',
                        listeners: {
							'keydown': function(inp, e) {
								if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                               	{
									e.stopEvent();
									Ext.getCmp("PDDAF_DrugCombo").focus();
								}
								if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
								{
									e.stopEvent();

									if (e.browserEvent.stopPropagation)
									{
										e.browserEvent.stopPropagation();
									}
									else
									{
										e.browserEvent.cancelBubble = true;
									}

									if (e.browserEvent.preventDefault)
									{
										e.browserEvent.preventDefault();
									}
									else
									{
										e.browserEvent.returnValue = false;
									}

									e.browserEvent.returnValue = false;
									e.returnValue = false;

									if (Ext.isIE)
									{
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									switch (e.getKey())
									{
										case Ext.EventObject.DELETE:
											inp.setValue('');
											inp.setRawValue('');
											break;

										case Ext.EventObject.F4:
//											inp.onTrigger2Click();
											break;
									}
								}
							},
							'change': function(combo, newValue, oldValue, cancelLoadSlaveCombo) {
                        		var drug_combo = Ext.getCmp('PDDAF_DrugCombo');

                        		drug_combo.clearValue();
                        		drug_combo.getStore().removeAll();
                          		drug_combo.lastQuery = '';
                                Ext.getCmp('PDDAF_Price').setRawValue(null);

                        		drug_combo.getStore().baseParams = {
                        			Drug_id: 0,
                        			DrugMnn_id: newValue
                        		};

								if ( newValue > 0 && !cancelLoadSlaveCombo )
								{
									drug_combo.getStore().proxy.conn.url = C_PERSONDISPMED_DRUGLIST;
                            		drug_combo.getStore().load();
                                }
                        	}
                        },
                        listWidth: 400,
						minChars: 0,
                        minLength: 1,
                        minLengthText: lang['pole_doljno_byit_zapolneno'],
                        queryDelay: 250,
                        tabIndex: 5005,
						trigger2Class: 'x-form-clear-trigger',
                        validateOnBlur: false,
                        width: 400,
                        xtype: 'swdrugmnndispcombo'
                    }, {
						id: 'PDDAF_DrugName',
						name: 'Drug_Name',
						value: '',
                    	xtype: 'hidden'
                    }, {
						allowBlank: false,
						hiddenName: 'Drug_id',
                        id: 'PDDAF_DrugCombo',
                        listeners: {
                        	'beforeselect': function(combo, record, index) {
                        		Ext.getCmp('PDDAF_Price').setValue(record.data.Drug_Price);
                        		Ext.getCmp('PDDAF_DrugMnnCombo').setValue(record.data.DrugMnn_id);
								Ext.getCmp('PDDAF_DrugName').setValue(record.data.Drug_Name);
                        	},
							'keydown': function(inp, e) {
								if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                               	{
									e.stopEvent();
									Ext.getCmp("PDDAF_DrugMnnCombo").focus();
								}
								if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
								{
									e.stopEvent();

									if (e.browserEvent.stopPropagation)
									{
										e.browserEvent.stopPropagation();
									}
									else
									{
										e.browserEvent.cancelBubble = true;
									}

									if (e.browserEvent.preventDefault)
									{
										e.browserEvent.preventDefault();
									}
									else
									{
										e.browserEvent.returnValue = false;
									}

									e.browserEvent.returnValue = false;
									e.returnValue = false;

									if (Ext.isIE)
									{
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									switch (e.getKey())
									{
										case Ext.EventObject.DELETE:
											inp.setValue('');
											inp.setRawValue('');
											break;

										case Ext.EventObject.F4:
//											inp.onTrigger2Click();
											break;
									}
								}
							}
                        },
                        listWidth: 400,
                        loadingText: lang['idet_poisk'],
                        minLengthText: lang['pole_doljno_byit_zapolneno'],
						onTrigger2Click: function() {
/*							var drug_combo = this;
							var current_window = Ext.getCmp('EvnReceptEditWindow');

							getWnd('swDrugTorgSearchWindow').show({
								onHide: function() {
									drug_combo.focus(false);
								},
								onSelect: function(drugTorgData) {
									drug_combo.getStore().removeAll();

									drug_combo.getStore().loadData([{
										Drug_Code: drugTorgData.Drug_Code,
										Drug_id: drugTorgData.Drug_id,
										Drug_Name: drugTorgData.Drug_Name,
										Drug_Price: drugTorgData.Drug_Price,
										DrugMnn_id: drugTorgData.DrugMnn_id
									}]);

									drug_combo.setValue(drugTorgData.Drug_id);
									drug_combo.getStore().baseParams.Drug_id = drugTorgData.Drug_id;
									drug_combo.getStore().baseParams.DrugMnn_id = 0;

									if (record)
									{
										drug_combo.fireEvent('beforeselect', drug_combo, record);
									}

									getWnd('swDrugTorgSearchWindow').hide();
                   				}
                			});
*/
						},
                        tabIndex: 5001,
						trigger2Class: 'x-form-clear-trigger',
                        validateOnBlur: false,
                        width: 400,
                        xtype: 'swdrugcombo'
                    }, {
						disabled: true,
						fieldLabel: lang['tsena'],
						id: 'PDDAF_Price',
						name: 'DrugPrice',
						tabIndex: 5001,
                    	xtype: 'textfield'
                    }, {
						allowBlank: false,
						allowDecimals: true,
						allowNegative: false,
						disabled: false,
						fieldLabel: lang['mesyachnyiy_kurs'],
						id: 'PDDAF_Course',
						name: 'Course',
						tabIndex: 5001,
                    	xtype: 'numberfield',
						listeners: {
							'change': function(field, newValue, oldValue)
							{
        						var summ_price_field = Ext.getCmp('PDDAF_Summ');
								summ_price_field.setValue(newValue*Ext.getCmp('PDDAF_Price').getValue());
							}
						}
	                }, {
						disabled: true,
						fieldLabel: lang['summa'],
						id: 'PDDAF_Summ',
						name: 'SummPrice',
						tabIndex: 5001,
                    	xtype: 'numberfield'
                    }, {
						allowBlank: false,
						fieldLabel: lang['data_nachala'],
	                    format: 'd.m.Y',
						name: 'Course_begDate',
	                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 5001,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: true,
						fieldLabel: lang['data_okonchaniya'],
	                    format: 'd.m.Y',
						name: 'Course_endDate',
	                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 5001,
						width: 100,
						xtype: 'swdatefield'
					}]
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
			}],
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
			]
		});
		sw.Promed.swPersonDispDrugAddWindow.superclass.initComponent.apply(this, arguments);
	}
});