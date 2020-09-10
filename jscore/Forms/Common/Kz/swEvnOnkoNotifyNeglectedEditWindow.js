/**
* swEvnOnkoNotifyNeglectedEditWindow - Протокол запущенной формы онкозаболевания
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
*/

sw.Promed.swEvnOnkoNotifyNeglectedEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	//autoHeight: true,
	buttonAlign: 'left',
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 800,
	height: 800,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

        var field = base_form.findField('EvnOnkoNotifyNeglected_setNotifyDT');
        if ( field.disabled ) {
            params.EvnOnkoNotifyNeglected_setNotifyDT = field.getRawValue();
        }
        field = base_form.findField('EvnOnkoNotifyNeglected_setDT');
        if ( field.disabled ) {
            params.EvnOnkoNotifyNeglected_setDT = field.getRawValue();
        }
        
        field = base_form.findField('EvnOnkoNotifyNeglected_ScreenDates');
        params.EvnOnkoNotifyNeglected_begScreenDate = Ext.util.Format.date(field.getValue1(),'d.m.Y');
        params.EvnOnkoNotifyNeglected_endScreenDate = Ext.util.Format.date(field.getValue2(),'d.m.Y');
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				var data = {};
				if (typeof action.result == 'object')
				{
					data = action.result;
					if (action.result.success)
					{
						win.callback(data);
						win.hide();
						showSysMsg(lang['protokol_sohranen']);
					}
				}
			}
		});
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.findById('FormPanel').getForm();
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				if(f.hiddenName == 'TumorStage_id'){
					f.setDisabled(true);
				} else {
					f.setDisabled(d);
				}
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swEvnOnkoNotifyNeglectedEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
            return false;
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].EvnOnkoNotifyNeglected_id) 
			this.EvnOnkoNotifyNeglected_id = arguments[0].EvnOnkoNotifyNeglected_id;
		else 
			this.EvnOnkoNotifyNeglected_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.EvnOnkoNotifyNeglected_id ) && ( this.EvnOnkoNotifyNeglected_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
        this.setFieldsDisabled(false);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		//погдгружаем только для просмотра
		if(base_form.findField('Diag_id').getValue() > 0){
			var diag = base_form.findField('Diag_id').getValue();
			base_form.findField('Diag_id').getStore().load({
				params:{where:" where Diag_id = "+diag},
				callback:function(store){
					if(base_form.findField('Diag_id').getStore().getById(diag)){
						base_form.findField('Diag_id').setValue(diag);
					} else {
						base_form.findField('Diag_id').setValue('');
					}
				}
			});
		}

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnOnkoNotifyNeglected_id: this.EvnOnkoNotifyNeglected_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					loadMask.hide();
                    this.InformationPanel.load({
                        Person_id: result[0].Person_id
                    });
                    base_form.findField('EvnOnkoNotifyNeglected_setNotifyDT').setDisabled(result[0].EvnOnkoNotifyNeglected_setNotifyDT && this.action == 'edit');
                    base_form.findField('EvnOnkoNotifyNeglected_setDT').setDisabled(result[0].EvnOnkoNotifyNeglected_setDT && this.action == 'edit');
                    base_form.findField('EvnOnkoNotifyNeglected_setFirstDT').setDisabled(result[0].EvnOnkoNotifyNeglected_setFirstDT);
                    base_form.findField('EvnOnkoNotifyNeglected_setFirstTreatmentDT').setDisabled(result[0].EvnOnkoNotifyNeglected_setFirstTreatmentDT);
                    base_form.findField('EvnOnkoNotifyNeglected_setFirstZODT').setDisabled(result[0].EvnOnkoNotifyNeglected_setFirstZODT);
                    base_form.findField('Lpu_zid').setDisabled(result[0].Lpu_zid);
                    base_form.findField('Lpu_fid').setDisabled(result[0].Lpu_fid);
                    base_form.findField('MedPersonal_id').setDisabled(result[0].MedPersonal_id);
                    var screenDates = "";
                    if(!Ext.isEmpty(result[0].EvnOnkoNotifyNeglected_begScreenDate)){
                    	screenDates += result[0].EvnOnkoNotifyNeglected_begScreenDate;
                    } else {
                    	screenDates += "__.__.____";
                    }
                    screenDates += " - ";
                    if(!Ext.isEmpty(result[0].EvnOnkoNotifyNeglected_endScreenDate)){
                    	screenDates += result[0].EvnOnkoNotifyNeglected_endScreenDate;
                    } else {
                    	screenDates += "__.__.____";
                    }
                    base_form.findField('EvnOnkoNotifyNeglected_ScreenDates').setValue(screenDates);
                    base_form.findField('MedPersonal_id').getStore().load({
			            callback: function()
			            {
			                base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
			                base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
			            }
			        });
				}.createDelegate(this),
				url:'/?c=EvnOnkoNotifyNeglected&m=load'
			});			
		} else {
			this.InformationPanel.load({
				Person_id: arguments[0].formParams.Person_id
			});
			base_form.findField('EvnOnkoNotifyNeglected_setNotifyDT').setValue(getGlobalOptions().date);
			base_form.findField('MedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
			base_form.findField('MedPersonal_id').getStore().load({
	            callback: function()
	            {
	                base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
	                base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
	            }
	        });
			loadMask.hide();			
		}
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['protokol_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(lang['protokol_redaktirovanie']);
				break;
		}
		
	},	
	initComponent: function() 
	{
		
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel(
		{	
			frame: true,
			layout: 'form',
			autoScroll: true,
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 350,
			url:'/?c=EvnOnkoNotifyNeglected&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnOnkoNotifyNeglected_id',
					xtype: 'hidden'
				}, {
					name: 'EvnOnkoNotify_id',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
		            fieldLabel: 'Диагноз',
		            hiddenName: 'Diag_id',
		            xtype: 'swdiagcombo',
		            disabled: true,
		            changeDisabled:false,
		            width: 350
		        }, {
					fieldLabel: 'Сатысы (Стадия)',//lang['stadiya_opuholevogo_protsessa'],
					hiddenName: 'TumorStage_id',
					xtype: 'swcommonsprlikecombo',
					sortField:'TumorStage_Code',
					comboSubject: 'TumorStage',
					width: 350
				}, {
                    allowBlank: false,
                    fieldLabel: 'Қатерлі ісіктің асқынғаны анықталған күн (Дата установления запущенности рака)',//lang['data_ustanovleniya_zapuschennosti_raka'],
                    name: 'EvnOnkoNotifyNeglected_setDT',
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
					allowBlank: false,
					fieldLabel: 'Алғашқы белгілердің пайда болу кезі (Дата появления первых признаков)',//lang['data_zapolneniya_izvescheniya'],
					name: 'EvnOnkoNotifyNeglected_setFirstDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					xtype:'fieldset',
					autoHeight: true,
					title:'Науқастың сырқаты бойынша бірінші рет медициналық көмек сұрап келуі (Первичное обращение больного за медицинской помощью по поводу заболевания)',
					items:[{
						allowBlank: false,
						fieldLabel: 'күні (дата)',//lang['data_zapolneniya_izvescheniya'],
						name: 'EvnOnkoNotifyNeglected_setFirstTreatmentDT',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}, {
						allowBlank: false,
						fieldLabel: 'қай емдеу ұйымына (в какую лечебную организацию)',//lang['naimenovanie_uchrejdeniya_gde_provedena_konferentsiya'],
						hiddenName: 'Lpu_fid',
						listWidth: 620,
						width: 350,
						xtype: 'swlpucombo'
					}]
				}, {
					allowBlank: false,
					fieldLabel: 'Алғашқы рет қатерлi iсiк диагнозы қойылған күн (Дата установления первичного диагноза злокачественного новообразования)',//lang['data_zapolneniya_izvescheniya'],
					name: 'EvnOnkoNotifyNeglected_setFirstZODT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					allowBlank: false,
					fieldLabel: 'қай ұйымда (в какой организации)',
					hiddenName: 'Lpu_zid',
					listWidth: 620,
					width: 350,
					xtype: 'swlpucombo'
				}, {
					xtype:'fieldset',
					autoHeight: true,
					title:'Науқастың медициналық көмек алуға емдеу ұйымына дер кезінде қаралмауы (Несвоевременное обращение больного за медицинской помощью в лечебную организацию)',
					items:[{
			            fieldLabel: 'Куда',
			            comboSubject: 'NeglectLpuType',
			            hiddenName: 'NeglectLpuType_id',
			            xtype: 'swcommonsprlikecombo',
			            width: 250
			        }, {
			            fieldLabel: 'Обращение',
			            comboSubject: 'NeglectLpuTime',
			            hiddenName: 'NeglectLpuTime_id',
			            xtype: 'swcommonsprlikecombo',
			            width: 250
			        }, {
						fieldLabel: 'Дата обращения впервые',//lang['data_zapolneniya_izvescheniya'],
						name: 'EvnOnkoNotifyNeglected_TreatFirstDate',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}]
				}, {
					xtype:'fieldset',
					autoHeight: true,
					title:'Науқастың медициналық көмек алуға онкологиялық ұйымға дер кезінде қаралмауы (Несвоевременное обращение больного за медицинской помощью в онкологическую организацию)',
					items:[{
			            fieldLabel: 'Куда',
			            comboSubject: 'NeglectOnkoType',
			            hiddenName: 'NeglectOnkoType_id',
			            xtype: 'swcommonsprlikecombo',
			            width: 250
			        }, {
			            fieldLabel: 'Обращение',
			            comboSubject: 'NeglectOnkoTime',
			            hiddenName: 'NeglectOnkoTime_id',
			            xtype: 'swcommonsprlikecombo',
			            width: 250
			        }, {
						fieldLabel: 'расталған күні (дата подтверждения)',//lang['data_zapolneniya_izvescheniya'],
						name: 'EvnOnkoNotifyNeglected_ConfirmDate',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: 'немесе «обыр» диагнозының жоққа шығарылған күні (дата исключения диагноза «рак»)',//lang['data_zapolneniya_izvescheniya'],
						name: 'EvnOnkoNotifyNeglected_ExceptionDate',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}]
				}, {
					xtype:'fieldset',
					autoHeight: true,
					title:'Жалпы емдеу желісінде ұзақ тексерілуі  (Длительное обследование в общей лечебной сети)',
					items:[{
			            fieldLabel: 'Где',
			            comboSubject: 'NeglectScreenLpuType',
			            hiddenName: 'NeglectScreenLpuType_id',
			            xtype: 'swcommonsprlikecombo',
			            width: 250
			        }, {
						fieldLabel: 'Сроки',//lang['data_zapolneniya_izvescheniya'],
						name: 'EvnOnkoNotifyNeglected_ScreenDates',
						xtype: 'daterangefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
					}]
				}, {
					xtype:'fieldset',
					autoHeight: true,
					title:'Онкологиялық ұйымда ұзақ тексерілуі (Длительное обследование в онкологических организациях)',
					items:[{
			            fieldLabel: 'Где',
			            comboSubject: 'NeglectScreenOnkoType',
			            hiddenName: 'NeglectScreenOnkoType_id',
			            xtype: 'swcommonsprlikecombo',
			            width: 250
			        }, {
			            fieldLabel: 'тексеру мерзімдері (сроки обследования)',
			            comboSubject: 'NeglectScreenOnkoTime',
			            hiddenName: 'NeglectScreenOnkoTime_id',
			            xtype: 'swcommonsprlikecombo',
			            width: 250
			        }]
				}, {
		            fieldLabel: 'Ауру ағымының астыртын өтуі (Скрытое течение болезни)',
		            comboSubject: 'NeglectHiddenType',
		            hiddenName: 'NeglectHiddenType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
			    }, {
		            fieldLabel: 'Диагностика қателігі (Ошибка в диагностике)',
		            comboSubject: 'NeglectDiagnosticErrType',
		            hiddenName: 'NeglectDiagnosticErrType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
			    }, {
					allowBlank: true,
					fieldLabel: 'Қосымша ескертулер (Дополнительные замечания)',//lang['dannyie_klinicheskogo_razbora_nastoyaschego_sluchaya'],
					name: 'NeglectDiagnosticErrType_SecondComment',
					autoCreate: {tag: "textarea", size: 256, maxLength: "256", autocomplete: "off"},
					xtype: 'textarea',
					width: 350,
					height: 40
				}, {
					allowBlank: true,
					fieldLabel: 'Осы жағдайды талқылау туралы деректер (Данные о разборе настоящего случая)',//lang['dannyie_klinicheskogo_razbora_nastoyaschego_sluchaya'],
					name: 'EvnOnkoNotifyNeglected_ClinicalData',
					autoCreate: {tag: "textarea", size: 256, maxLength: "256", autocomplete: "off"},
					xtype: 'textarea',
					width: 350,
					height: 40
				}, {
					fieldLabel: 'конференция өткен ұйымның атауы (наименование организации, где проведена конференция)',
					hiddenName: 'Lpu_cid',
					listWidth: 620,
					width: 350,
					xtype: 'swlpucombo'
				}, {
					fieldLabel: 'конференция өткен күні (дата проведения конференции)',//lang['data_konferentsii'],
					name: 'EvnOnkoNotifyNeglected_setConfDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
					allowBlank: true,
					fieldLabel: 'шығарылған тұжырымдар (организационные выводы)',//lang['organizatsionnyie_vyivodyi'],
					name: 'EvnOnkoNotifyNeglected_OrgDescr',
					autoCreate: {tag: "textarea", size: 256, maxLength: "256", autocomplete: "off"},
					xtype: 'textarea',
					width: 350,
					height: 40
				}, {
					allowBlank: false,
					fieldLabel: 'Врач, заполнивший протокол',//lang['vrach_zapolnivshiy_izveschenie'],
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo'
				}, {
                    allowBlank: false,
                    fieldLabel: 'Хаттаманың толтырылған күнi (Дата составления протокола)',//lang['data_zapolneniya_protokola'],
                    name: 'EvnOnkoNotifyNeglected_setNotifyDT',
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }]
			}],
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'EvnOnkoNotifyNeglected_id'},
                {name: 'EvnOnkoNotify_id'},
                {name: 'Morbus_id'},
                {name: 'Server_id'},
                {name: 'Person_id'},
                {name: 'PersonEvn_id'},
                {name: 'EvnOnkoNotifyNeglected_ClinicalData'},
                {name: 'EvnOnkoNotifyNeglected_OrgDescr'},
                {name: 'Lpu_cid'},
                {name: 'Lpu_sid'},
                {name: 'Lpu_fid'},
                {name: 'Lpu_zid'},
                {name: 'Lpu_id'},
                {name: 'OnkoLateDiagCause_id'},
                {name: 'EvnOnkoNotifyNeglected_setConfDT'},
                {name: 'EvnOnkoNotifyNeglected_setNotifyDT'},
                {name: 'EvnOnkoNotifyNeglected_setDT'},
                {name: 'EvnOnkoNotifyNeglected_setFirstDT'},
                {name: 'EvnOnkoNotifyNeglected_setFirstTreatmentDT'},
                {name: 'EvnOnkoNotifyNeglected_setFirstZODT'},
                {name: 'NeglectLpuType_id'},
                {name: 'NeglectLpuTime_id'},
                {name: 'EvnOnkoNotifyNeglected_TreatFirstDate'},
                {name: 'NeglectOnkoType_id'},
                {name: 'NeglectOnkoTime_id'},
                {name: 'EvnOnkoNotifyNeglected_ConfirmDate'},
                {name: 'EvnOnkoNotifyNeglected_ExceptionDate'},
                {name: 'NeglectScreenLpuType_id'},
                {name: 'EvnOnkoNotifyNeglected_begScreenDate'},
                {name: 'EvnOnkoNotifyNeglected_endScreenDate'},
                {name: 'NeglectScreenOnkoType_id'},
                {name: 'NeglectScreenOnkoTime_id'},
                {name: 'NeglectHiddenType_id'},
                {name: 'NeglectDiagnosticErrType_id'},
                {name: 'NeglectDiagnosticErrType_SecondComment'},
                {name: 'MedPersonal_id'}
            ])
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnOnkoNotifyNeglectedEditWindow.superclass.initComponent.apply(this, arguments);
	}
});