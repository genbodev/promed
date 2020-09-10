/**
* swPersonMeasureEditWindow - окно редактирования/добавления измерения показателей здоровья 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package         DLO
* @access          public
* @copyright       Copyright (c) 2010 Swan Ltd.
* @author          Salakhov Rustam
* @version         10.08.2010
* @comment         Префикс для id компонентов PMEW (swPersonMeasureEditWindow)
*                  tabIndex: TABINDEX_PMEW (10500)
*
* Record_Status - (0 - новая запись; 1 - данные с сервера; 2 - данные отредактированны; 3 - удаление;)
*
* Использует: окно редактирования персональных данных (swPersonEditWindow)
*/

sw.Promed.swPersonMeasureEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'right',
	buttons: [{
		handler: function() {
			this.ownerCt.doSave();
		},
		iconCls: 'save16',
		id: 'PMEW_SaveButton',
		tabIndex: TABINDEX_PMEW+10,
		text: BTN_FRMSAVE
	}, {
		text: '-'
	}, {
		handler: function() {
			this.ownerCt.hide();
		},
		iconCls: 'cancel16',
		id: 'PMEW_CancelButton',
		onTabAction: function() {
			Ext.getCmp('PMEW_Person_IzmerDate_Date').focus(true, 200);
		},
		onShiftTabAction: function() {
			Ext.getCmp('PMEW_SaveButton').focus(true, 200);
		},
		tabIndex: TABINDEX_PMEW+11,
		text: BTN_FRMCANCEL
	}, 
		HelpButton(this, TABINDEX_PMEW+12)
	],
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function() {
		var current_window = Ext.getCmp('PersonMeasureEditWindow');
		var baseform = Ext.getCmp('PersonMeasureEditForm');
		var record_status = current_window.Record_Status;
		var add_flag = true;
		var err_message = '';
		
		//проверки
		if (!baseform.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					baseform.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if (this.PersonIzmerGrid.getCurrentRateCount() < 1)
			err_message = lang['doljen_byit_vveden_hotya_byi_odin_pokazatel'];
		
		if (err_message != '') {
        	Ext.Msg.alert(lang['oshibka'], err_message, function() {} );
        	return false;
        }
		
		if (record_status == 1) record_status = 2;
		if (current_window.action != 'add') add_flag = false;
		
		var lpusection_name = '';
		var medpersonal_fio = '';
		var lpusection_id = current_window.findById('lsLpuSection_id').getValue();
		var medpersonal_id = current_window.findById('msrMedPersonal_id').getValue();
		
		index = current_window.findById('lsLpuSection_id').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpusection_id; });
		if (index >= 0) {
			lpusection_name = current_window.findById('lsLpuSection_id').getStore().getAt(index).data.LpuSection_Name;
		}		
		index = current_window.findById('msrMedPersonal_id').getStore().findBy(function(rec) { return rec.get('MedPersonal_id') == medpersonal_id; });
		if (index >= 0) {
			var obj = current_window.findById('msrMedPersonal_id').getStore().getAt(index).data;
			medpersonal_fio = obj.MedPersonal_Code + ' ' + obj.MedPersonal_FIO;
		}
		
		
		var data = [{
			'PersonMeasure_id': current_window.PersonMeasure_id,
			'date': current_window.findById('PMEW_Person_IzmerDate_Date').getValue(),
			'lpusection_name': lpusection_name,
			'medpersonal_fio': medpersonal_fio,
			'PersonMeasure_setDT_Date': current_window.findById('PMEW_Person_IzmerDate_Date').getValue(),
			'PersonMeasure_setDT_Time': current_window.findById('PMEW_Person_IzmerDate_Time').getValue(),
			'LpuSection_id': lpusection_id,
			'MedPersonal_id': medpersonal_id,
			'Record_Status': record_status,
			'RateGrid_Data': this.PersonIzmerGrid.getJSONChangedData(),
			'RateGrid_DataNumber': this.action == 'add' ? this.PersonIzmerGrid.getNewDataSetNumber() : this.PersonIzmerGrid.getSavedDataSetNumber()
		}];
		current_window.callback(data, add_flag);
		current_window.hide();
    },
	draggable: true,
    enableEdit: function(enable) {
    	if (enable) {
			this.findById('PMEW_Person_IzmerDate_Date').enable();
			this.findById('PMEW_Person_IzmerDate_Time').enable();
			this.findById('lsLpuSection_id').enable();
			this.findById('msrMedPersonal_id').enable();
			this.PersonIzmerGrid.enable();
			this.buttons[0].enable();
		} else {
			this.findById('PMEW_Person_IzmerDate_Date').disable();			
			this.findById('PMEW_Person_IzmerDate_Time').disable();
			this.findById('lsLpuSection_id').disable();
			this.findById('msrMedPersonal_id').disable();
			this.PersonIzmerGrid.disable();
			this.buttons[0].disable();
		}
    },
    height: 490,
	id: 'PersonMeasureEditWindow',
    initComponent: function() {
		this.PersonIzmerGrid = new sw.Promed.RateGrid({
			title: lang['pokazateli'],
			id: 'PEW_PropertyGrid',
			height: 306,
			border: true,
			region: 'south'
		});
		
		this.PersonFrame  = new sw.Promed.PersonInformationPanelShort({
			id: 'PMEW_PersonInformationFrame',
			region: 'north'
		});
	
        Ext.apply(this, {
            items: [
				this.PersonFrame,
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'PersonMeasureEditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [							
						{ //Дата и время измерения;
							layout: 'column',								
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									allowBlank: false,
									disabled: false,
									fieldLabel: lang['data_i_vremya_izmereniya'],
									format: 'd.m.Y',									
									id: 'PMEW_Person_IzmerDate_Date',
									name: 'Person_IzmerDate_Date',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									tabIndex: TABINDEX_PMEW + 01,
									width: 200,
									value: '', //default value
									xtype: 'swdatefield',
									listeners: {
										'select': function(th, dt) { },
										'change': function(field, newValue, oldValue) {
											blockedDateAfterPersonDeath('personpanelid', 'PMEW_PersonInformationFrame', field, newValue, oldValue);
										}
									}										
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 1,
								items: [{
									allowBlank: false,
									disabled: false,
									labelSeparator: '',
									format: 'H:i',
									id: 'PMEW_Person_IzmerDate_Time',
									name: 'Person_IzmerDate_Time',
									//onTriggerClick: Ext.emptyFn,
									plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
									tabIndex: TABINDEX_PMEW + 02,
									validateOnBlur: false,
									width: 60,
									value: '', //default value
									xtype: 'swtimefield'
								}]
							}]
						}, {
							allowBlank: false,
							disabled: false,
							fieldLabel: lang['otdelenie_lpu'],
							id: 'lsLpuSection_id',
							hiddenName: 'LpuSection_id',
							lastQuery: '',
							tabIndex: TABINDEX_PMEW + 03,
							width: 400,
							xtype: 'swlpusectionglobalcombo'
						}, { 
							allowBlank: false,	
							disabled: false,
							fieldLabel: lang['vrach'],
							maxLength: 100,
							name: 'MedPersonal_id',
							id: 'msrMedPersonal_id',
							loadingText: lang['idet_poisk'],
							minLengthText: lang['pole_doljno_byit_zapolneno'],
							tabIndex: TABINDEX_PMEW + 04,
							width: 400,
							xtype: 'swmedpersonalallcombo'
						}
					],
					layout: 'form',
					/*reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'PersonMeasure_id' }
					]),*/
					region: 'center'
				}),
				this.PersonIzmerGrid
			]
        });
    	sw.Promed.swPersonMeasureEditWindow.superclass.initComponent.apply(this, arguments);
    },
    keys: [{
    	alt: true,
        fn: function(inp, e) {
            e.stopEvent();

            if (e.browserEvent.stopPropagation)
                e.browserEvent.stopPropagation();
            else
                e.browserEvent.cancelBubble = true;

            if (e.browserEvent.preventDefault)
                e.browserEvent.preventDefault();
            else
                e.browserEvent.returnValue = false;

            e.browserEvent.returnValue = false;
            e.returnValue = false;

            if (Ext.isIE) {
            	e.browserEvent.keyCode = 0;
            	e.browserEvent.which = 0;
            }

        	var current_window = Ext.getCmp('PersonMeasureEditWindow');

            if (e.getKey() == Ext.EventObject.J) {
            	current_window.hide();
            } else if (e.getKey() == Ext.EventObject.C) {
	        	if ('view' != current_window.action) {
	            	current_window.doSave();
	            }
			}
        },
        key: [ Ext.EventObject.C, Ext.EventObject.J ],
        scope: this,
        stopEvent: false
    }],
    layout: 'border',
    listeners: {
    	'hide': function() {
    		this.onHide();
    	}
    },
    maximizable: true,
    minHeight: 370,
    minWidth: 700,
    modal: true,
    onHide: Ext.emptyFn,

	ownerWindow: null,
    plain: true,
    resizable: true,
    show: function() {
		sw.Promed.swPersonMeasureEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('PersonMeasureEditForm');
		var base_form = form.getForm();
		form.getForm().reset();

       	current_window.callback = Ext.emptyFn;
       	current_window.onHide = Ext.emptyFn;
		current_window.ownerWindow = null;
		
        if (!arguments[0] || !arguments[0].owner) {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
        	return false;
        }		
        if (arguments[0].action) {
        	current_window.action = arguments[0].action;
        }
        if (arguments[0].callback) {
            current_window.callback = arguments[0].callback;
        }
        if (arguments[0].owner) {
        	current_window.ownerWindow = arguments[0].owner;
        }

       	current_window.PersonMeasure_id = arguments[0].PersonMeasure_id ? arguments[0].PersonMeasure_id : swGenTempId(current_window.ownerWindow.getGrid().getStore());
       	current_window.PersonMeasure_setDT_Date = arguments[0].PersonMeasure_setDT_Date ? arguments[0].PersonMeasure_setDT_Date : null;
       	current_window.PersonMeasure_setDT_Time = arguments[0].PersonMeasure_setDT_Time ? arguments[0].PersonMeasure_setDT_Time : null;
       	current_window.LpuSection_id = arguments[0].LpuSection_id ? arguments[0].LpuSection_id : null;
		current_window.MedPersonal_id = arguments[0].MedPersonal_id ? arguments[0].MedPersonal_id : null;
		current_window.Record_Status = arguments[0].Record_Status ? arguments[0].Record_Status : 0;
		
  		var loadMask = new Ext.LoadMask(Ext.get('PersonMeasureEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		
       // form.getForm().setValues(arguments[0]);
        form.getForm().clearInvalid();
		
		//alert(current_window.PersonMeasure_setDT_Date);
		form.findById('PMEW_Person_IzmerDate_Date').setValue(current_window.PersonMeasure_setDT_Date);
		form.findById('PMEW_Person_IzmerDate_Time').setValue(current_window.PersonMeasure_setDT_Time);
		if (form.findById('lsLpuSection_id').getStore().getCount() == 0 ) {
			setLpuSectionGlobalStoreFilter();
			form.findById('lsLpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));			
		}
		form.findById('lsLpuSection_id').setValue(current_window.LpuSection_id);
		form.findById('msrMedPersonal_id').getStore().load({
			callback: function(r,o,s) {
				form.findById('msrMedPersonal_id').setValue(current_window.MedPersonal_id);
			}
		});
		
		//загрузка таблицы(grid) с показателями		
		var dataset_num = arguments[0].RateGrid_DataNumber && arguments[0].RateGrid_DataNumber != "" ? arguments[0].RateGrid_DataNumber : 0; //проверяем есть ли номер датасета для данного измерения	
		this.PersonIzmerGrid.clear();
		if(dataset_num > 0){
			this.PersonIzmerGrid.restoreGridCopy(dataset_num);
		} else {
			if (current_window.PersonMeasure_id && current_window.action != 'add')
				this.PersonIzmerGrid.loadData({rate_type: 'person', rate_subid: current_window.PersonMeasure_id}); //загрузка показателей для пациента
		}
		
		//загрузка данных о человеке
		this.PersonFrame.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			callback: function() {
				var field = base_form.findField('Person_IzmerDate_Date');
				clearDateAfterPersonDeath('personpanelid', 'PMEW_PersonInformationFrame', field);
			}
		});
		
		
        switch (current_window.action) {
            case 'add':
			case 'view':
                current_window.setTitle(lang['izmerenie_pokazateley_zdorovya'] + (current_window.action == 'add' ? lang['_dobavlenie'] : lang['_prosmotr']));
                current_window.enableEdit(current_window.action == 'add');				
				
				
				loadMask.hide();
				current_window.findById('PMEW_Person_IzmerDate_Date').focus(false, 250);
                break;

        	case 'edit':
        	    current_window.setTitle(lang['izmerenie_pokazateley_zdorovya_redaktirovanie']);
                current_window.enableEdit(true);

				
				loadMask.hide();
				if(current_window.action == 'add')
					current_window.findById('PMEW_Person_IzmerDate_Date').focus(false, 250);
				else
					current_window.buttons[1].focus();
                break;
        }

        form.getForm().clearInvalid();
    },
    width: 700
});