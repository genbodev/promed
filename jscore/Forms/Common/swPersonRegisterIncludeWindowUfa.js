/**
* swPersonRegisterIncludeWindowUfa - Запись регистра: Добавление
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      09.2012
* @modification swPersonRegisterIncludeWindow for Ufa (Васинский Игорь, Прогресс) 
*/

sw.Promed.swPersonRegisterIncludeWindowUfa = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	//autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
        id:'swPersonRegisterIncludeWindowUfa',
        object:'swPersonRegisterIncludeWindowUfa',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 600,
	height: 200,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		console.log('SUCCESS 0');
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

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
		
		params.MedPersonal_iid = base_form.findField('MedPersonal_iid').getValue();

		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{   
			   console.log('SUCCESS 11');
               
               form.reloadSearchGrid();
               
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{   
			    console.log('SUCCESS 1');
                
				win.formStatus = 'edit';
				loadMask.hide();
                
                if (!action.result) 
				{
					return false;
				}
				else if (action.result.Alert_Msg) 
				{
					var buttons = {
						yes: (parseInt(action.result.PersonRegisterOutCause_id) == 3) ? 'Новое' : 'Да',
						no: (parseInt(action.result.PersonRegisterOutCause_id) == 3) ? 'Предыдущее' : 'Нет'
					};
					if (parseInt(action.result.PersonRegisterOutCause_id) == 3) {
						buttons.cancel = 'Отмена';
					}
					sw.swMsg.show(
					{
						buttons: buttons,
						fn: function( buttonId ) 
						{
							var mode;
							if ( buttonId == 'yes' && action.result.Yes_Mode) 
							{
								mode = action.result.Yes_Mode
							} 
							else if ( buttonId == 'no' && action.result.No_Mode) 
							{
								mode = action.result.No_Mode
							}
							if(mode)
							{
								if ( mode.inlist(['homecoming','relapse']) ) 
								{
									// Вернуть пациента в регистр, удалить дату закрытия заболевания
									sw.Promed.personRegister.back({
										PersonRegister_id: action.result.PersonRegister_id
										,Diag_id: base_form.findField('Diag_id').getValue()
										,ownerWindow: win
										,callback: function(data) {
											base_form.findField('PersonRegister_id').setValue(action.result.PersonRegister_id);
											var data = base_form.getValues();
											win.callback(data);
											win.hide();
										}
									});
								}
								else
								{
									base_form.findField('Mode').setValue(mode);
									win.doSave();
								}
							}
							else
							{
								win.hide();
							}
						},
						msg: action.result.Alert_Msg,
						title: 'Вопрос'
					});
				}
				else if (action.result.success) 
				{  
                    console.log('SUCCESS 2');
                     
					base_form.findField('PersonRegister_id').setValue(action.result.PersonRegister_id);
					var data = base_form.getValues();
					win.callback(data);
					win.hide();
				}
			}
		});	
        
        console.log('SUCCESS 3');
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.findById('PersonRegisterIncludeFormPanel').getForm();
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swPersonRegisterIncludeWindowUfa.superclass.show.apply(this, arguments);
        
        console.log('!!!!!!!!', arguments);
        
		var current_window = this;
		if (!arguments[0] || !arguments[0].MorbusType_id || !arguments[0].Person_id) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка',
				fn: function() {
					this.hide();
				}
			});
		}

        this.MorbusType_id = arguments[0].MorbusType_id;
        
		this.focus();
		this.findById('PersonRegisterIncludeFormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
        var diag_combo = base_form.findField('Diag_id');

		diag_combo.lastQuery = 'Строка, которая никогда не сможет оказаться в lastQuery';

        if (arguments[0].registryType && arguments[0].registryType != '' )
        {
            diag_combo.registryType = arguments[0].registryType;
        }

		base_form.reset();

		this.action = null;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		arguments[0].Lpu_iid = (arguments[0].Lpu_iid) ? arguments[0].Lpu_iid : getGlobalOptions().lpu_id;
		arguments[0].MedPersonal_iid = (arguments[0].MedPersonal_iid) ? arguments[0].MedPersonal_iid : getGlobalOptions().medpersonal_id;
		arguments[0].PersonRegister_setDate = (arguments[0].PersonRegister_setDate) ? arguments[0].PersonRegister_setDate : getGlobalOptions().date;
		
		base_form.setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		base_form.findField('MedPersonal_iid').getStore().load({
			callback: function()
			{
				base_form.findField('MedPersonal_iid').setValue(base_form.findField('MedPersonal_iid').getValue());
				base_form.findField('MedPersonal_iid').fireEvent('change', base_form.findField('MedPersonal_iid'), base_form.findField('MedPersonal_iid').getValue());
			}.createDelegate(this)
		});
               
		this.InformationPanel.load({
                        Person_id: base_form.findField('Person_id').getValue()
		});
		
		if(false) {
			base_form.findField('Diag_id').MorbusType_id = parseInt(base_form.findField('MorbusType_id').getValue());
		} else {
			switch(base_form.findField('MorbusType_id').getValue().toString()) {
				case '3':
					diag_combo.additQueryFilter = "(Diag_Code like 'C%' OR Diag_Code like 'D0%')";
					diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^C|D0", "i"))>=0)';
					break;
				case '4':
                    if (diag_combo.registryType == 'NarkoRegistry'){
                        diag_combo.additQueryFilter = "(Diag_Code like 'F1%' or 1=1)";
                        diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^F1", "i"))>=0)';
                    } else if (diag_combo.registryType == 'CrazyRegistry'){
                        diag_combo.additQueryFilter = "(Diag_Code like 'F0%' or Diag_Code like 'F2%' or Diag_Code like 'F3%' or Diag_Code like 'F4%'" +
                            " or Diag_Code like 'F5%' or Diag_Code like 'F6%' or Diag_Code like 'F7%' or Diag_Code like 'F8%' or Diag_Code like 'F9%')";
                        diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^F0|F[2-9]", "i"))>=0)';
                    }
					break;
				case '5':
					diag_combo.additQueryFilter = "(Diag_Code like 'B15%' OR Diag_Code like 'B16%' OR Diag_Code like 'B17%' OR Diag_Code like 'B18%' OR Diag_Code like 'B19%')";
					diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^B1[5-9]", "i"))>=0)';
					break;
				case '19':
					diag_combo.additQueryFilter = "(Diag_Code like 'I20.0')";
					diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^I2", "i"))>=0)';
					break;
				default:
					/*
					почему-то не работает
					diag_combo.additQueryFilter = 'MorbusType_id = '+ base_form.findField('MorbusType_id').getValue();
					diag_combo.additClauseFilter = 'record["MorbusType_id"] == "'+ base_form.findField('MorbusType_id').getValue() +'"';
					*/
					base_form.findField('Diag_id').MorbusType_id = parseInt(base_form.findField('MorbusType_id').getValue());
					diag_combo.additQueryFilter = '';
					diag_combo.additClauseFilter = '';
					break;
			}
		}
		
		var diag_id = arguments[0].Diag_id;
		if ( diag_id != null && diag_id.toString().length > 0 ) {
			base_form.findField('Diag_id').getStore().load({
				callback: function() {
					base_form.findField('Diag_id').getStore().each(function(record) {
						if ( record.get('Diag_id') == diag_id ) {
							base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
						}
					});
				},
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
			});
		}
		
		if ( base_form.findField('MorbusType_id').getValue() == 12 || base_form.findField('MorbusType_id').getValue().inlist([0,21,22,23,24,25])) {
			this.findById('prDiagGroupBox').hide();
		} else {
			this.findById('prDiagGroupBox').show();
		}
		
		loadMask.hide();

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
			region: 'center',
			id: 'PersonRegisterIncludeFormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			url:'/?c=BSK_RegisterData&m=saveInPersonRegister',//'/?c=PersonRegister&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'PersonRegister_id',
					xtype: 'hidden',
					value: 0
				}, {
					name: 'Mode',
					xtype: 'hidden',
					value: null
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'Person_Firname',
					xtype: 'hidden'
				}, {
					name: 'Person_Secname',
					xtype: 'hidden'
				}, {
					name: 'Person_Surname',
					xtype: 'hidden'
				}, {
					name: 'Person_Birthday',
					xtype: 'hidden'
				}, {
					name: 'MorbusType_id',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Lpu_iid',
					xtype: 'hidden'
				}, {
					allowBlank: false,
					fieldLabel: 'Дата включения в регистр',
					name: 'PersonRegister_setDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date
				}, 
                {
					changeDisabled: false,
					disabled: true,
					fieldLabel: 'Врач',
					hiddenName: 'MedPersonal_iid',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
					anchor: false
				},                              
                 {
					
                                        xtype: 'fieldset',
					autoHeight: true,
                                        id: 'prDiagGroupBox',
                                        bodyStyle: 'display:none;',
                                        hidden:true,
					style: 'padding: 0; margin: 0;',
					border: false,
					items: [{   
						allowBlank: ($.inArray(Ext.getCmp('swPersonRegisterIncludeWindowUfa').MorbusType_id, [84,88,89,50,113])) ? true : false,
						fieldLabel: 'Диагноз',
						hiddenName: 'Diag_id',
						listWidth: 620,
						valueField: 'Diag_id',
                                                registryType: '',
						additQueryFilter: "(Diag_Code like 'C%' OR Diag_Code like 'D0%')",
						additClauseFilter: '(record["Diag_Code"].search(new RegExp("^C", "i"))>=0 || record["Diag_Code"].search(new RegExp("^D0", "i"))>=0)',
						width: 350,
						xtype: 'swdiagcombo'
					}],

				}]
			}],
            reloadSearchGrid: function(){
                /**  
                 Ext.getCmp('BskRegistry').getGrid().getStore().load({
                    params:{
                        Person_Firname: '!__'
                    }
                }); 
                */    
                 Ext.getCmp('BskRegistry').getGrid().getStore().load({
                    params:{
                        Person_id :  this.getForm().findField('Person_id').getValue(),
                        Person_Firname: this.getForm().findField('Person_Firname').getValue(),
                        Person_Firname: this.getForm().findField('Person_Firname').getValue(),
                        Person_Secname: this.getForm().findField('Person_Secname').getValue(),
                        Person_Surname: this.getForm().findField('Person_Surname').getValue(),
                        Person_Birthday: this.getForm().findField('Person_Birthday').getValue(),
                        SearchFormType : 'BskRegistry'
                    }
                }); 
                              
            },
		});
        
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
					var base_form = this.FormPanel.getForm();
					if(base_form.findField("MorbusType_id").getValue() == '84'){
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
						loadMask.show();
						Ext.Ajax.request({
							url: '/?c=BSK_Register_User&m=checkBSKforScreening',
							params: {
								Person_id: base_form.findField("Person_id").getValue()
							},
							callback: function (options, success, response) {
								loadMask.hide();
								if (success === true) {   
									var responseText = Ext.util.JSON.decode(response.responseText);
									
									if(responseText.length>0){
										sw.swMsg.show(
											{
												icon: Ext.MessageBox.WARNING,
												title: langs('Внимание'),
												msg: langs('У данного пациента есть заболевания системы кровообращения!'),
												buttons: Ext.Msg.OK
											});
										return false;
									}
									else{
										this.doSave();
										console.log('=>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!MorbusType_id', Ext.getCmp('swPersonRegisterIncludeWindowUfa').MorbusType_id);
										if(Ext.getCmp('swPersonRegisterIncludeWindowUfa').MorbusType_id.inlist([84,88,89,113])){
												//console.log('Обновляем STORE');
												this.FormPanel.reloadSearchGrid();
											}                        
											
											Ext.getCmp('swPersonRegisterIncludeWindowUfa').hide();				
									}   
								}
							}.createDelegate(this)
						});
					} else {
						this.doSave();
						if(Ext.getCmp('swPersonRegisterIncludeWindowUfa').MorbusType_id.inlist([84,88,89,113])){
							this.FormPanel.reloadSearchGrid();
						}
						Ext.getCmp('swPersonRegisterIncludeWindowUfa').hide();		
					}
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
		sw.Promed.swPersonRegisterIncludeWindowUfa.superclass.initComponent.apply(this, arguments);
	},
	title: 'Запись регистра БСК: Добавление'
});