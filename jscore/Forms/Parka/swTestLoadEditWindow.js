/**
* swTestLoadEditWindow - форма ввода комплексных услуг
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      декабрь 2010
* @prefix       euoew
* @comment      
*
*
* @input        params (object) 
*/
/*NO PARSE JSON*/

sw.Promed.swTestLoadEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	codeRefresh: true,
	objectName: 'swTestLoadEditWindow',
	objectSrc: '/jscore/Forms/Parka/swTestLoadEditWindow.js',
	title: lang['test'],//WND_EUOEW,
	split: true,
	width: 700,
	layout: 'form',
	id: 'EvnUslugaOrderEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		},
		beforeshow: function()
		{
			//
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	/* Проверка которая выполняется до сохранения данных
	*/
	doSave: function() 
	{
		var form = this.EvnUslugaForm;
		if (!form.getForm().isValid()) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
              fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		frm = form.getForm();
		form.ownerCt.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.EvnUslugaForm;
		/*
		form.ownerCt.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.getForm().submit(
		{
			//params: {},
			failure: function(result_form, action) 
			{
				form.ownerCt.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				form.ownerCt.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.EvnUsluga_id)
					{
						var records = form.getForm().getValues();
						records['EvnUsluga_id'] = action.result.EvnUsluga_id;
						form.ownerCt.callback(form.ownerCt.owner, action.result.EvnUsluga_id, records, (form.ownerCt.action=='add')) //, form.getForm().getValues(), (form.ownerCt.action=='add'));
						form.ownerCt.hide();
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
		*/
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.EvnUslugaForm.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	setPanelVisible: function(panel,flag)
	{
		this.findById(panel).setVisible(flag);
		this.syncSize();
	},
	getDate: function()
	{
		this.getLoadMask(lang['opredelenie_tekuschego_vremeni']).show();
		getCurrentDateTime({
			callback: function(r) 
			{
				if (r.success) {this.loadLpuSection(r.date);}
				this.getLoadMask().hide();
			}.createDelegate(this)
		});
	},
	/** Функция относительно универсальной загрузки справочников выбор в которых осуществляется при вводе букв (цифр)
	 * Пример загрузки Usluga:
	 * loadSpr('Usluga_id', { where: "where UslugaType_id = 2 and Usluga_id = " + Usluga_id });
	 */
	loadSpr: function(field_name, params, callback)
	{
		var bf = this.EvnUslugaForm.getForm();
		var combo = bf.findField(field_name);
		var value = combo.getValue();
		log(value);
		combo.getStore().removeAll();
		combo.getStore().load(
		{
			callback: function() 
			{
				combo.getStore().each(function(record) 
				{
					if (record.data[field_name] == value)
					{
						combo.setValue(value);
						combo.fireEvent('select', combo, record, 0);
					}
				});
				if (callback)
				{
					callback();
				}
			},
			params: params 
		});
	},
	show: function() 
	{
		sw.Promed.swTestLoadEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		/*if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		*
		
		/*
		frm.findField('EvnUsluga_isGenXml').fireEvent('check', frm.findField('EvnUsluga_isGenXml'), true);
		*/
		var frm = form.EvnUslugaForm.getForm();
		form.focus();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		/*
		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			form.action = arguments[0].action;
		}
		else 
		{
			if ((form.EvnUsluga_id) && (form.EvnUsluga_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}
		*/
		
		frm.reset();
		
		//frm.setValues(arguments[0]);
		
		form.getLoadMask(LOAD_WAIT).show();
		form.getLoadMask().hide();
		switch (form.action)
		{
			case 'add':
				form.setTitle(WND_EUOEW_ADD);
				
				form.setFieldsDisabled(false);
				break;
			case 'edit':
				form.setTitle(WND_EUOEW_EDIT);
				form.setFieldsDisabled(false);
				break;
			case 'view':
				form.setTitle(WND_EUOEW_VIEW);
				form.setFieldsDisabled(true);
				break;
		}
		form.center();
		
		
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.EvnUslugaForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'EvnUslugaEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				width: 65,
				style: 'padding: 5px;',
				layout: 'form',
				items: [{
					xtype: 'button',
					hidden: false,
					cls: 'x-btn-large',
					iconCls: 'idcard32',
					tooltip: lang['ustanovit_soedinenie'],
					handler: function() 
					{
						log(document.swMedLab);
						var response = document.swMedLab.setPortName();
						response = document.swMedLab.connect();
						if ( response.success == true )
						{
							//var r = sw.Applets.MedLab.connect();
							//log(r);
							if ( response.success == true )
							{
								Ext.Msg.alert("Внимание, внимание", 'Соединение успешно установлено :)');
							}
							else
							{
								Ext.Msg.alert("Ошибка", r.ErrorMessage);
							}
						}
						else
						{
							Ext.Msg.alert("Ошибка", response.ErrorMessage);
						}
						
					}.createDelegate(this)
				}]
			},
			{
				html:'<applet name="swMedLab" archive="applets/swMedLab.jar" code="swan/MedLab/swMedLabApplet.class" width=400 height=100>No Java!</applet>'
			},
			{
				width: 65,
				style: 'padding: 5px;',
				layout: 'form',
				items: [{
					xtype: 'button',
					hidden: false,
					cls: 'x-btn-large',
					iconCls: 'idcard32',
					tooltip: lang['poluchit_dannyie'],
					handler: function() 
					{
						var response = document.swMedLab.getResult();
						if ( response.success == true )
							this.EvnUslugaForm.findField('editor').setValue(response.json); // json - это поле-ответ, как оно будет называться (?)
						else
							Ext.Msg.alert("Ошибка", response.ErrorMessage);
					}.createDelegate(this)
				},
				{
					xtype: 'button',
					hidden: false,
					title: lang['spisok_vseh_okon_promed'],
					tooltip: lang['spisok_vseh_okon_promed'],
					handler: function() 
					{
						//log(sw.Promed);
						// Инициализация всех окон промед
						for(var key in sw.Promed)
						{
							//log(key);
							if ((key.indexOf('Form') == -1) && (key.indexOf('Window') == -1))
							{
								// Не форма и не окно 100%
							}
							else 
							{
								try 
								{
									var win = getWnd(key); // :)
									if (win!=null)
									{
										log(key, ';', win.title);
									}
								}
								catch (e)
								{
									//log('Это не форма: ', e);
								}
							}
							//log(key);
						};
						//sw.Promed.swTestLoadEditWindow
					}.createDelegate(this)
				}]
			},
			
			{
				fieldLabel: lang['otvet'],
				xtype: 'htmleditor',
				name: 'editor',
				height: 250
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'EvnUsluga_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSection_id' },
				{ name: 'UslugaComplex_id' }
				
			]),
			timeout: 600,
			url: '/?c=Usluga&m=saveEvnUsluga'
		});
		
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tabIndex: TABINDEX_EUOEW + 91
			}, 
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_EUOEW + 92),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				tabIndex: TABINDEX_EUOEW + 93
			}],
			items: [form.EvnUslugaForm]
		});
		sw.Promed.swTestLoadEditWindow.superclass.initComponent.apply(this, arguments);
	}
});