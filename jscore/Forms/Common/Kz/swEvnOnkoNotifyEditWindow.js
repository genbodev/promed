/**
* swEvnOnkoNotifyEditWindow - Извещение на онкобольного
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
*/

sw.Promed.swEvnOnkoNotifyEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	//autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 850,
	height: 650,
	doSave: function(options)
	{
		if ( this.formStatus == 'save' || this.action != 'add' ) {
			return false;
		}
		if ( !options || typeof options != 'object' ) {
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
                    win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		var date = base_form.findField('EvnOnkoNotify_setDT').getValue();
		params.EvnOnkoNotify_setDT = Ext.util.Format.date(date,'d.m.Y');
		
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
						if (options.print) {
							win.action = 'view';
							win.setFieldsDisabled(true);
							win.EvnOnkoNotify_id = data.EvnOnkoNotify_id;
							win.printNotification(win.EvnOnkoNotify_id);
						} else {
							win.hide();
						}
						showSysMsg(lang['izveschenie_sozdano']);
					}
				}
			}
		});
		return true;
	},
	doPrint: function() {
		if (this.action == 'add') {
			this.doSave({print: true});
		} else {
			this.printNotification(this.EvnOnkoNotify_id);
		}
	},
	printNotification: function(EvnOnkoNotify_id) {
		if ( !EvnOnkoNotify_id ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'OnkoNotify.rptdesign',
			'Report_Params': '&paramEvnOnkoNotify=' + EvnOnkoNotify_id,
			'Report_Format': 'pdf'
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
				if(f.hiddenName && f.hiddenName.inlist(['Ethnos_id','SocStatus_id','Post_id','CitizenType'])){
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
		sw.Promed.swEvnOnkoNotifyEditWindow.superclass.show.apply(this, arguments);
        var thas = this;

		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
                    thas.hide();
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
		
		if (arguments[0].EvnOnkoNotify_id) 
			this.EvnOnkoNotify_id = arguments[0].EvnOnkoNotify_id;
		else 
			this.EvnOnkoNotify_id = null;
			
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
			if ( ( this.EvnOnkoNotify_id ) && ( this.EvnOnkoNotify_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		base_form.findField('Lpu_sid').getStore().load({
			params: { object: 'Lpu' },
			callback: function () {
				var combo = base_form.findField('Lpu_sid');
				combo.getStore().clearFilter();
				combo.lastQuery = '';
				combo.getStore().filterBy(function(record) 
				{
					// #15891
					var is_onko = true;
					/*switch (getGlobalOptions().region && getGlobalOptions().region.nick) {
						case 'ufa':
							is_onko = ( record.get('LpuType_Code') == 43 );
							break;
						case 'tambov': // тамбов - уфимский справочник
							is_onko = ( record.get('LpuType_Code') == 71 );
							break;
						default: // пермский справочник
							is_onko = ( record.get('LpuType_Code').toString().inlist(['30','43']) );
							break;
					}*/
					return is_onko;
				});
				if ( base_form.findField('Lpu_sid').getStore().getCount() == 1 && thas.action == 'add' ) {
					combo.setValue(combo.getStore().getAt(0).get('Lpu_id'))
				}
			}.createDelegate(this)
		});

		if (this.action != 'add') {
            loadMask.hide();
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                icon: Ext.Msg.ERROR,
                msg: lang['oshibka_otkryitiya_formyi_forma_mojet_byit_otkryita_tolko_dlya_sozdaniya_izvescheniya'],
                title: lang['oshibka'],
                fn: function() {
                    thas.hide();
                }
            });
            return false;
		}
        base_form.findField('MedPersonal_id').getStore().load({
            callback: function()
            {
                base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
                base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
            }
        });
        this.InformationPanel.load({
            Person_id: arguments[0].formParams.Person_id
        });
        base_form.findField('EvnOnkoNotify_setDT').setValue(getGlobalOptions().date);

        Ext.Ajax.request({
			url: '/?c=Person&m=getPersonEditWindow',
			params:
			{
				person_id: arguments[0].formParams.Person_id
			},
			success: function (response)
			{
				var result = Ext.util.JSON.decode(response.responseText);
				if(result && result[0]) {
					this.FormPanel.getForm().setValues(result[0]);
				}
			}.createDelegate(this)
		});	
        loadMask.hide();
				
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['izveschenie_dobavlenie']);
				this.setFieldsDisabled(false);
				base_form.findField('EvnOnkoNotify_setDT').disable();
				break;
			/*case 'view':
				this.setTitle(lang['izveschenie_prosmotr']);
				this.setFieldsDisabled(true);
				break;*/
		}
        return true;
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
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 350,
			url:'/?c=EvnOnkoNotify&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnOnkoNotify_id',
					xtype: 'hidden'
				}, {
					name: 'EvnOnkoNotify_pid',
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
					name: 'Diag_id',
					xtype: 'hidden'
				}, {
					displayField: 'Lpu_Nick',
					allowBlank: false,
					editable: false,
					enableKeyEvents: true,
					fieldLabel: 'Мәлiмдеме (Извещение направлено в)',//lang['napravit_izveschenie'],
					hiddenName: 'Lpu_sid',
					listeners: {
						'keydown': function( inp, e ) {
							if ( inp.disabled )
								return false;

							if ( e.F4 == e.getKey() ) {
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								inp.onTrigger1Click();

								return false;
							}
                            return true;
						},
						'keyup': function(inp, e) {
							if ( e.F4 == e.getKey() ) {
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								return false;
							}
                            return true;
						}
					},
					mode: 'local',
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'Lpu_id', type: 'int' },
							{ name: 'Lpu_Nick', type: 'string' },
							{ name: 'LpuType_Code', type: 'int' }
						],
						key: 'Lpu_id',
						sortInfo: {
							field: 'Lpu_Nick'
						},
						url: C_GETOBJECTLIST
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{Lpu_Nick}',
						'</div></tpl>'
					),
					listWidth: 620,
					width: 350,
					valueField: 'Lpu_id',
					xtype: 'swbaselocalcombo'
				}, {
				    comboSubject: 'Ethnos',
					fieldLabel: 'Ұлты (Национальность)',//lang['natsionalnost'],
					hiddenName: 'Ethnos_id',
					typeCode: 'int',
					width: 250,
					xtype: 'swcommonsprcombo'
				}, {
					xtype:'swсitizentypecombo',
					hiddenName:'CitizenType',
					width: 250,
					fieldLabel:'Тұрғыны (Житель)'//lang['jitel']
				}, {
					xtype: 'swpostcombo',
					hiddenName: 'Post_id',
					width: 250,
					fieldLabel: 'Кәсiбi (Профессия)'//lang['professiya']
				}, {
					codeField: 'SocStatus_Code',
					fieldLabel: 'Әлеуметтiк жағдайы (Социальное положение)',//lang['sots_status'],
					xtype: 'swsocstatuscombo',
					hiddenName: 'SocStatus_id',
					width : 250
				}, {
					allowBlank: false,
					fieldLabel: 'Алғашқы қаралған күнi (Дата первичного обращения)',//lang['data_zapolneniya_izvescheniya'],
					name: 'EvnOnkoNotify_setFirstDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					allowBlank: false,
					fieldLabel: 'Диагнозы қойылған күн (Дата установления диагноза)',//lang['data_zapolneniya_izvescheniya'],
					name: 'EvnOnkoNotify_setDiagDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
		            fieldLabel: 'Қатерлi iсiктiң анықталу жағдайлары (Обстоятельства выявления опухоли)',//'Обстоятельства выявления опухоли',
		            comboSubject: 'TumorCircumIdentType',
		            hiddenName: 'TumorCircumIdentType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
					fieldLabel: 'Iсiк процесiнiң кезеңi (Стадия опухолевого процесса)',//lang['stadiya_opuholevogo_protsessa'],
					hiddenName: 'TumorStage_id',
					xtype: 'swcommonsprlikecombo',
					sortField:'TumorStage_Code',
					comboSubject: 'TumorStage',
					width: 250
				},
				{
					layout:'column',
					label: 'Стадия опухолевого процесса по системе TNM.',
					labelWidth: 50,
					defaults:{
						labelWidth: 20
					},
					items:[
						{
							layout:'form',
							width: 350,
							items:[{
								html: '<div style="text-align: right; height: 40px;padding-top:3px;font-size:12px;width:350px;">Диагнозы (кезеңi мен TNM бойынша таралу дәрежесi) (стадия и степень распространенности  по TNM)</div>',
								width: 350,
								xtype: 'label'
							}]
						},
						{
							layout:'form',
							items:[{ // енд
					            fieldLabel: 'T',
					            comboSubject: 'OnkoT',
					            hiddenName: 'OnkoT_id',
					            xtype: 'swcommonsprlikecombo',
					            width: 50
					        }]
						},
						{
							layout:'form',
							items:[{
					            fieldLabel: 'N',
					            comboSubject: 'OnkoN',
					            hiddenName: 'OnkoN_id',
					            xtype: 'swcommonsprlikecombo',
					            width: 50
					        }]
						},
						{
							layout:'form',
							items:[{
					            fieldLabel: 'M',
					            comboSubject: 'OnkoM',
					            hiddenName: 'OnkoM_id',
					            xtype: 'swcommonsprlikecombo',
					            width: 50
					        }]
						}
					]
				}, {
		            fieldLabel: 'Диагноз',
		            hiddenName: 'Diag_id',
		            xtype: 'swdiagcombo',
		            width: 250
		        }, {
		            fieldLabel: 'Диагнозды растау әдiстерi (тек бiр негiзгi әдiстi көрсетiңiз) (Метод подтверждения диагноза)',
		            comboSubject: 'OnkoDiagConfType',
		            hiddenName: 'OnkoDiagConfType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Мәлiмдеме толтырылды (Извещение заполнено в)',
		            comboSubject: 'NotifyFillPlace',
		            hiddenName: 'NotifyFillPlace_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Науқас қайда жіберілді (Куда направлен больной)',
		            comboSubject: 'NotifyDirectType',
		            hiddenName: 'NotifyDirectType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
					allowBlank: false,
					fieldLabel: 'Мәлiмдеме толтырыған күн (Дата заполнения извещения)',//lang['data_zapolneniya_izvescheniya'],
					name: 'EvnOnkoNotify_setDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['vrach_zapolnivshiy_izveschenie'],
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
					anchor: false
				}]
			}]
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
					this.doSave({print: false});
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.doPrint();
				}.createDelegate(this),
				iconCls: 'print16',
				text: lang['pechat']
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
		sw.Promed.swEvnOnkoNotifyEditWindow.superclass.initComponent.apply(this, arguments);
	}
});