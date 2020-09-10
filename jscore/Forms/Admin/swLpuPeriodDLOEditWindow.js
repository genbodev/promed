/**
* swLpuPeriodDLOEditWindow - окно редактирования/добавления периода ЛЛО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      05.10.2011
*/

sw.Promed.swLpuPeriodDLOEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 400,
	layout: 'form',
	id: 'LpuPeriodDLOEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		var form = this.findById('LpuPeriodDLOEditForm');
		if ( !form.getForm().isValid() ) 
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
		this.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.findById('LpuPeriodDLOEditForm');
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit(
		{
			params: 
			{
				action: current_window.action
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result)
				{
					if (action.result.LpuPeriodDLO_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_DLOGrid').loadData();
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
							msg: langs('При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.'),
							title: langs('Ошибка')
						});
					}
				}
			}
		});
	},
	show: function() 
	{
		sw.Promed.swLpuPeriodDLOEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('LpuPeriodDLOEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].LpuPeriodDLO_id) 
			this.LpuPeriodDLO_id = arguments[0].LpuPeriodDLO_id;
		else 
			this.LpuPeriodDLO_id = null;
		if (arguments[0].Lpu_id) 
			this.Lpu_id = arguments[0].Lpu_id;
		else 
			this.Lpu_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.LpuPeriodDLO_id ) && ( this.LpuPeriodDLO_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('LpuPeriodDLOEditForm');
		form.getForm().findField('LpuPeriodDLO_Code').setContainerVisible(getRegionNick() == 'ufa');
		form.getForm().findField('LpuPeriodType_id').setContainerVisible(false);
		form.getForm().findField('LpuUnit_id').setContainerVisible(false);

		this.syncShadow();
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(langs('Период по ЛЛО: Добавление'));
				this.enableEdit(true);
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(langs('Период по ЛЛО: Редактирование'));
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(langs('Период по ЛЛО: Просмотр'));
				this.enableEdit(false);
				break;
		}

		form.getForm().load(
		{
			params:
			{
				LpuPeriodDLO_id: current_window.LpuPeriodDLO_id,
				Lpu_id: current_window.Lpu_id
			},
			failure: function(f, o, a)
			{
				log(o);
				log(a);
				loadMask.hide();
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						current_window.hide();
					},
					icon: Ext.Msg.ERROR,
					msg: langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'),
					title: langs('Ошибка')
				});
			},
			success: function(form, action)
			{
				loadMask.hide();

				if (getRegionNick() == 'msk') {
					form.findField('LpuPeriodType_id').setContainerVisible(true);
					radioGroup = form.findField('LpuPeriodType_id').items;
					lpuUnitField = form.findField('LpuUnit_id');
					lpuPeriodField = form.findField('LpuPeriodDLO_Code');
					response = Ext.decode(action.response.responseText)[0];

					radioGroup.each(function (rec) {
						switch (rec.inputValue) {
							case 'mo':
								rec.setValue(!response.LpuPeriodTypeValue && Ext.isEmpty(lpuUnitField.getValue())
											|| (response.LpuPeriodTypeValue && response.LpuPeriodTypeValue == 'mo'));
								if (rec.getValue()) {
									lpuUnitField.setValue(null);
									lpuUnitField.setContainerVisible(false);
									lpuUnitField.setAllowBlank(true);

									lpuPeriodField.setValue(null);
									lpuPeriodField.setContainerVisible(false);
									lpuPeriodField.setAllowBlank(true);
								}
								break;
							case 'groups':
								rec.setValue(!response.LpuPeriodTypeValue && !Ext.isEmpty(lpuUnitField.getValue())
											|| (response.LpuPeriodTypeValue && response.LpuPeriodTypeValue == 'groups'));
								if (rec.getValue()) {
									lpuUnitField.getStore().load({
										callback: function () {
											lpuUnitField.setBaseFilter(function(rec) {
												return rec.get('LpuUnitType_id').inlist([2,10,12]);
											});
										}
									});
									lpuUnitField.setContainerVisible(true);
									lpuUnitField.setAllowBlank(false);

									lpuPeriodField.setContainerVisible(true);
									lpuPeriodField.setAllowBlank(false);
								}
								break;
						}
					});
				}
			},
			url: '/?c=LpuPassport&m=loadLpuPeriodDLO'
		});
		if ( this.action != 'view' )
			Ext.getCmp('LPEW_LpuPeriodDLO_begDate').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.LpuPeriodDLOEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuPeriodDLOEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'LPEW_Lpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: 'LPEW_LpuPeriodDLO_id',
				name: 'LpuPeriodDLO_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: 'LPEW_LpuPeriodType_id',
				name: 'LpuPeriodType_id',
				fieldLabel: langs('Период ЛЛО указан для'),
				columns: 1,
				vartical: true,
				xtype: 'radiogroup',
				items: [{
					boxLabel: 'MO',
					name: 'LpuPeriodTypeValue',
					inputValue: 'mo'
				}, {
					boxLabel: 'Группы отделений',
					name: 'LpuPeriodTypeValue',
					inputValue: 'groups'
				}],
				listeners: {
					'change': function (radioGroup, radioBtn) {
						if (radioBtn) {
							lpuUnitField = current_window.findById('LpuPeriodDLOEditForm').getForm().findField('LpuUnit_id');
							switch (radioBtn.inputValue) {
								case 'mo':
									lpuUnitField.setValue(null);
									lpuUnitField.setContainerVisible(false);
									lpuUnitField.setAllowBlank(true);

									lpuPeriodField.setValue(null);
									lpuPeriodField.setContainerVisible(false);
									lpuPeriodField.setAllowBlank(true);
									break;
								case 'groups':
									lpuUnitField.getStore().load({
										callback: function () {
											lpuUnitField.setBaseFilter(function(rec) {
												return rec.get('LpuUnitType_id').inlist([2,10,12]);
											});
										}
									});
									lpuUnitField.setContainerVisible(true);
									lpuUnitField.setAllowBlank(false);

									lpuPeriodField.setContainerVisible(true);
									lpuPeriodField.setAllowBlank(false);
									break;
							}
						}
					}
				}
			}, {
				fieldLabel: langs('Группа отделений'),
				width: 200,
				id: 'LPEW_LpuUnitCombo',
				xtype: 'swlpuunitglobalcombo',
				name: 'LpuUnit_id'
			}, {
				allowBlank: getRegionNick() != 'ufa',
				fieldLabel: langs('Код ЛЛО'),
				id: 'LPEW_LpuPeriodDLO_Code',
				name: 'LpuPeriodDLO_Code',
				width: 100,
				tabIndex: TABINDEX_SPEF,
				allowDecimals:false,
				xtype: 'numberfield',
				autoCreate: {tag: "input", size: 14, autocomplete: "off"}
			}, {
				id: 'LPEW_LpuPeriodDLO_begDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPDLOEW + 1,
				format: 'd.m.Y',
				fieldLabel: langs('Дата включения'),
				allowBlank: false,
				name: 'LpuPeriodDLO_begDate'
			},
			{
				id: 'LPEW_LpuPeriodDLO_endDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPDLOEW + 2,
				format: 'd.m.Y',
				fieldLabel: langs('Дата исключения'),
				name: 'LpuPeriodDLO_endDate'
			}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'Lpu_id' },
				{ name: 'LpuPeriodDLO_id' },
				{ name: 'LpuPeriodDLO_begDate' },
				{ name: 'LpuPeriodDLO_endDate' },
				{ name: 'LpuPeriodDLO_Code' },
				{ name: 'LpuUnit_id' }
			]),
			url: '/?c=LpuPassport&m=saveLpuPeriodDLO'
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
				tabIndex: TABINDEX_LPDLOEW + 3,
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
				tabIndex: TABINDEX_LPDLOEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuPeriodDLOEditForm]
		});
		sw.Promed.swLpuPeriodDLOEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});