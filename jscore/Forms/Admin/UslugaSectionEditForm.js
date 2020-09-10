/**
* UslugaSectionEditForm - окно просмотра и редактирования услуг
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      29.06.2009
*/
/*NO PARSE JSON*/
sw.Promed.swUslugaSectionEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:'Услуга',
	id: 'UslugaSectionEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 560,
	height: 330,
	modal: true,
	codeRefresh: true,
	objectName: 'swUslugaSectionEditForm',
	objectSrc: '/jscore/Forms/Admin/UslugaSectionEditForm.js',
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	returnFunc: function(owner) {},
	show: function()
	{
		sw.Promed.swUslugaSectionEditForm.superclass.show.apply(this, arguments);
		//this.uslugaSearchWindow =
		var loadMask = new Ext.LoadMask(Ext.get('UslugaSectionEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		// Обнуление для того чтобы фокус не ставился
		this.ViewUslugaSectionTariff.loadCount = -1;
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].Usluga_id)
			this.Usluga_id = arguments[0].Usluga_id;
		else
			this.Usluga_id = null;
		if (arguments[0].UslugaPrice_ue)
			this.UslugaPrice_ue = arguments[0].UslugaPrice_ue;
		else
			this.UslugaPrice_ue = null;
		if (arguments[0].UslugaSection_id)
			this.UslugaSection_id = arguments[0].UslugaSection_id;
		else
			this.UslugaSection_id = null;
		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else
			this.LpuSection_id = null;
		if (arguments[0].LpuUnit_id)
			this.LpuUnit_id = arguments[0].LpuUnit_id;
		else
			this.LpuUnit_id = null;

		if (!arguments[0])
			{
			Ext.Msg.alert('Ошибка', 'Отсутствуют необходимые параметры');
			this.hide();
			return false;
			}
		var form = this;
		form.findById('UslugaSectionEditFormPanel').getForm().reset();
		switch (this.action)
			{
			case 'add':
				form.setTitle('Услуга в отделении: Добавление');
				break;
			case 'edit':
				form.setTitle('Услуга в отделении: Редактирование');
				break;
			case 'view':
				form.setTitle('Услуга в отделении: Просмотр');
				break;
			}

		if (this.action=='view')
		{
			form.findById('usUsluga_id').disable();
			form.findById('usUslugaPrice_ue').disable();
			form.findById('usLpuSection_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('usUsluga_id').enable();
			form.findById('usUslugaPrice_ue').enable();
			form.findById('usLpuSection_id').enable();
			form.buttons[0].enable();
		}

		// Читаем списки
		form.findById('usLpuSection_id').getStore().removeAll();
		form.findById('usLpuSection_id').getStore().load(
		{
			callback: function(r,o,s)
			{
				if (form.LpuSection_id)
				{
					form.findById('usLpuSection_id').setValue(form.LpuSection_id || form.findById('usLpuSection_id').getValue());
					form.findById('usLpuSection_id').disable();
				}
			},
			params: {LpuUnit_id: form.LpuUnit_id}
		});
		form.findById('usUslugaSection_id').setValue(this.UslugaSection_id);
		form.ViewUslugaSectionTariff.readOnly = false;
		if (this.action!='add')
		{
			form.findById('UslugaSectionEditFormPanel').getForm().load(
			{
				url: C_LPUUSLUGA_GET,
				params:
				{
					object: 'UslugaSection',
					UslugaSection_id: form.UslugaSection_id,
					Usluga_id: '',
					LpuSection_id: ''

				},
				success: function ()
				{
					if (form.action=='view')
					{
						form.ViewUslugaSectionTariff.readOnly = true;
						form.buttons[1].focus();
					}

					//form.findById('usUsluga_id').getStore().removeAll();
					//form.findById('usUsluga_id').setValue(form.findById('usUsluga_id').getValue());
					//alert();

					form.findById('usUsluga_id').getStore().load(
					{
						params: {
							where: "where UslugaType_id = 2 and Usluga_id = " + form.findById('usUsluga_id').getValue()
						},
						callback: function(r,o,s)
						{
							form.findById('usUsluga_id').getStore().each(function(record) {
								if ( record.get('Usluga_id') == form.findById('usUsluga_id').getValue() ) {
									form.findById('usUsluga_id').fireEvent('select', form.findById('usUsluga_id'), record, 0);
								}
							});

							form.findById('usUsluga_id').focus(true, 100);
						}
					});

					form.ViewUslugaSectionTariff.loadData(
					{
						params:
						{
							UslugaSection_id: this.UslugaSection_id || form.findById('usUslugaSection_id').getValue()
							//Usluga_id: form.findById('usUsluga_id').getValue()
						},
						globalFilters: {UslugaSection_id: this.UslugaSection_id || form.findById('usUslugaSection_id').getValue()}
					});
					loadMask.hide();
				},
				failure: function ()
				{
					loadMask.hide();
					Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
				}
			});
		}
		else
		{
			form.ViewUslugaSectionTariff.loadData(
			{
				params:{UslugaSection_id: this.UslugaSection_id},
				globalFilters: {UslugaSection_id: form.findById('usUslugaSection_id').getValue() || -1}
			});
			form.findById('usUsluga_id').focus(true, 100);
			loadMask.hide();
		}
	},
	doSave: function() 
	{
		var form = this.findById('UslugaSectionEditFormPanel');
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.submit();
	},
	submit: function(mode, onlySave)
	{
		var form = this.findById('UslugaSectionEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('UslugaSectionEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		form.getForm().submit(
			{
				params:
				{
				LpuSection_id: form.findById('usLpuSection_id').getValue()
				},
				failure: function(result_form, action)
				{
					if (action.result)
					{
						/*
						if (action.result.Error_Code)
						{
							Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
						}
						*/
					}
					loadMask.hide();
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.UslugaSection_id)
						{
							if (!onlySave || (onlySave!==1))
							{
								form.ownerCt.hide();
								form.ownerCt.returnFunc(form.ownerCt.owner, action.result.UslugaSection_id);
							}
							else
							{
								new Ext.ux.window.MessageWindow(
								{
									title: 'Сохранение',
									autoHeight: true,
									help: false,
									bodyStyle: 'text-align:center',
									closable: true,
									hideFx:
									{
										delay: 3000,
										mode: 'standard',
										useProxy: false
									},
									html: '<br/><b>Редактируемая услуга сохранена.</b><br/><br/>',
									iconCls: 'info16',
									width: 250
								}).show(Ext.getDoc());
								if (!form.findById('usUslugaSection_id').getValue())
								{
									form.findById('usUsluga_id').setValue(action.result.Usluga_id);
									form.findById('usLpuSection_id').setValue(action.result.LpuSection_id);
									form.findById('usUslugaSection_id').setValue(action.result.UslugaSection_id);
									form.findById('UslugaSectionTariff').params =
									{
										UslugaSection_id: form.findById('usUslugaSection_id').getValue()
									};
									form.findById('UslugaSectionTariff').gFilters =
									{
										UslugaSection_id: form.findById('usUslugaSection_id').getValue()
									};
									if (mode=='add')
									{
										form.findById('UslugaSectionTariff').run_function_add = false;
										form.findById('UslugaSectionTariff').ViewActions.action_add.execute();
									}
								}
								else
								{
									if (mode=='add')
									{
										form.findById('UslugaSectionTariff').run_function_add = false;
										form.findById('UslugaSectionTariff').ViewActions.action_add.execute();
									}
									else if (mode=='edit')
									{
										form.findById('UslugaSectionTariff').run_function_edit = false;
										form.findById('UslugaSectionTariff').ViewActions.action_edit.execute();
									}
								}
							}
						}
						else
							Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
					}
					else
						Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
				}
			});
	},
	initComponent: function()
	{
		//this.swUslugaSectionTariffEditForm = ;
		tekform = this;
		this.CheckValues = function()
		{
			if (!this.findById('usUsluga_id').getValue())
			{
				sw.swMsg.show(
				{
					icon: Ext.MessageBox.ERROR,
					title: 'Ошибка',
					msg: 'Поле "Услуга" обязательно для заполнения!',
					buttons: Ext.Msg.OK,
					fn: function(buttonId, text, obj)
					{
						tekform.findById('usUsluga_id').focus(true, 50);
					}
				});
				return false;
			}
			if (!this.findById('usLpuSection_id').getValue())
			{
				sw.swMsg.show(
				{
					icon: Ext.MessageBox.ERROR,
					title: 'Ошибка',
					msg: 'Поле "Отделение" обязательно для заполнения!',
					buttons: Ext.Msg.OK,
					fn: function(buttonId, text, obj)
					{
						tekform.findById('usLpuSection_id').focus(true, 50);
					}
				});
				return false;
			}
			return true;
		}
		this.MainRecordAdd = function()
		{
			var tf = Ext.getCmp('UslugaSectionEditForm');
			if (tf.CheckValues())
			{
				tf.submit('add',1);
			}
			return false;
		}
		this.MainRecordEdit = function()
		{
			var tf = Ext.getCmp('UslugaSectionEditForm');
			if (tf.CheckValues())
			{
				tf.submit('edit',1);
			}
			return false;
		}

		this.ViewUslugaSectionTariff = new sw.Promed.ViewFrame(
		{
			title:'Тариф на услугу в отделении',
			object: 'UslugaSectionTariff',
			editformclassname: 'swUslugaSectionTariffEditForm',
			dataUrl: C_USLUGASECTIONTARIFF_GET,
			//height:203,
			//toolbar: false,
			autoLoadData: false,
			stringfields:
			[
				{name: 'UslugaSectionTariff_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaSection_id', type: 'int', hidden: true, isparams:true},
				{id: 'autoexpand',name: 'UslugaSectionTariff_Tariff',  type: 'float', header: 'Тариф'},
				{name: 'UslugaSectionTariff_begDate', type: 'date', header: 'Начало'},
				{name: 'UslugaSectionTariff_endDate', type: 'date', header: 'Окончание'}

			],
			actions:
			[
				{name:'action_add', func: tekform.MainRecordAdd},
				{name:'action_edit', func: tekform.MainRecordEdit},
				{name:'action_view'},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			focusOn: {name:'usOk',type:'button'},
			focusPrev: {name:'usUslugaPrice_ue',type:'field'},
			focusOnFirstLoad: false
		});

		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'UslugaSectionEditFormPanel',
			region: 'center',
			items: [{
				name: 'UslugaSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'usUslugaSection_id'
			}, {
				name: 'Usluga_Code',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'usUsluga_Code'
			}, {
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Услуга',
				hiddenName: 'Usluga_id',
				id: 'usUsluga_id',
				focusOnShiftTab: 'usCancel',
				tabIndex: 1101,
				xtype: 'swuslugacombo'
			}, {
				allowBlank: false,
				anchor: '100%',
				name: 'LpuSection_id',
				tabIndex: 1102,
				xtype: 'swlpusectioncombo',
				id: 'usLpuSection_id',
				enableKeyEvents: true
			}, {
				allowDecimals: true,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: 'УЕТ',
				id: 'usUslugaPrice_ue',
				name: 'UslugaPrice_ue',
				tabIndex: 1103,
				width: 100,
				xtype: 'numberfield'
			},
			this.ViewUslugaSectionTariff
			],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
				//alert('success');
				}
			},
			[
				{ name: 'Usluga_id' },
				{ name: 'Usluga_Code' },
				{ name: 'UslugaPrice_ue' },
				{ name: 'UslugaSection_id' },
				{ name: 'LpuSection_id' }
			]
			),
			url: C_USLUGASECTION_SAVE
		});
		Ext.apply(this,
		{
			buttons: [{
				text: BTN_FRMSAVE,
				id: 'usOk',
				tabIndex: 1104,
				iconCls: 'save16',
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				handler: function() {
					this.doSave();
				}.createDelegate(this)
			}, {
				text:'-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCANCEL,
				id: 'usCancel',
				tabIndex: 1105,
				iconCls: 'cancel16',
				onTabAction: function() {
					this.findById('usUsluga_id').focus();
				}.createDelegate(this),
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				handler: function() {
					this.hide();
					this.returnFunc(this.owner, -1);
				}.createDelegate(this)
			}],
			xtype: 'panel',
			region: 'center',
			layout: 'fit',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swUslugaSectionEditForm.superclass.initComponent.apply(this, arguments);

		this.ViewUslugaSectionTariff.addListenersFocusOnFields();
	}
});