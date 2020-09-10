/**
* swRegistryEditWindow - окно редактирования/добавления реестра (счета) for Ufa.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      18.11.2009
* @comment      Префикс для id компонентов rege (RegistryEditForm)
*               tabIndex (firstTabIndex): 15100+1 .. 15200
*
*
* @input data: action - действие (add, edit, view)
*              Registry_id - ID реестра
*/

sw.Promed.swRegistryEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	firstTabIndex: 15100,
	id: 'RegistryEditWindow',
	codeRefresh: true,
	objectName: 'swRegistryEditWindow',
	objectSrc: '/jscore/Forms/Admin/Ufa/swRegistryEditWindow.js',
	Registry_IsNew: null,
	listeners: 
	{
		hide: function() 
		{
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		var form = this.RegistryForm;
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
		var begDate = form.findById('regeRegistry_begDate').getValue();
		var endDate = form.findById('regeRegistry_endDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('regeRegistry_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: 'Дата окончания не может быть меньше даты начала.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		// а дату-то надо всетаки передать, понадобится при редактировании
		form.ownerCt.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.RegistryForm, win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."});
		loadMask.show();
		var registry_acc_date = form.findById('regeRegistry_accDate').getValue().dateFormat('d.m.Y');

		if ( form.findById('regeRegistry_IsNotInsurCheckbox').getValue() == true ) {
			form.findById('regeRegistry_IsNotInsur').setValue(2);
		}
		else {
			form.findById('regeRegistry_IsNotInsur').setValue(1);
		}

		form.getForm().submit(
		{
			params: 
			{
				RegistryType_id: form.findById('regeRegistryType_id').getValue(),
				RegistrySubType_id: form.findById('regeRegistrySubType_id').getValue(),
				Registry_accDate: registry_acc_date,
				Registry_IsNew: win.Registry_IsNew
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				/*
				Тут стандартный акшен на ошибку отрабатывает, если ошибка - поэтому не надо 
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
				*/
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					//if (action.result.Registry_id)
					if (action.result.RegistryQueue_id)
					{
						//log(form.getForm().getValues());
						var records = {RegistryQueue_id:action.result.RegistryQueue_id, RegistryQueue_Position:action.result.RegistryQueue_Position}
						form.ownerCt.callback(form.ownerCt.owner, action.result.RegistryQueue_id, records) //, form.getForm().getValues(), (form.ownerCt.action=='add'));
						
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
							msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) 
	{
		var form = this;
		if (enable) 
		{
			form.findById('regeLpuUnitSet_id').enable();
			form.findById('regeLpu_cid').enable();
			form.findById('regeRegistry_accDate').enable();
			form.findById('rege_Registry_comment').enable();
			form.findById('regeRegistry_begDate').enable();
			form.findById('regeRegistry_endDate').enable();
			form.findById('regePayType_id').enable();
			form.findById('regeDispClass_id').enable();
			form.findById('regeLpu_oid').enable();
			form.findById('regeRegistry_Num').enable();
			form.findById('regeRegistry_IsNotInsurCheckbox').enable();
			form.findById('regeRegistry_IsInoterCheckbox').enable();
			form.buttons[0].show();
		}
		else 
		{
			form.findById('regeLpuUnitSet_id').disable();
			form.findById('regeLpu_cid').disable();
			form.findById('regeRegistry_accDate').disable();
			form.findById('rege_Registry_comment').disable();
			form.findById('regeRegistry_begDate').disable();
			form.findById('regeRegistry_endDate').disable();
			form.findById('regePayType_id').disable();
			form.findById('regeDispClass_id').disable();
			form.findById('regeLpu_oid').disable();
			form.findById('regeRegistry_Num').disable();
			form.findById('regeRegistry_IsNotInsurCheckbox').disable();
			form.findById('regeRegistry_IsInoterCheckbox').disable();
			form.buttons[0].hide();
		}
	},
	loadCombo: function()
	{
		var form = this, params = {};

		if ( form.RegistryType_id == 6 ) {
			params.LpuUnitSet_IsCmp = 2;
		}

		form.findById('regeLpuUnitSet_id').getStore().load(
		{
			params: params,
			callback: function()
			{
				form.findById('regeLpuUnitSet_id').setValue(form.findById('regeLpuUnitSet_id').getValue());
			}
		});
	},
	show: function() 
	{
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0] || !arguments[0].RegistryType_id || !arguments[0].RegistrySubType_id)
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы '+form.id+'.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
		}
		form.focus();

		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].Registry_id) 
			form.Registry_id = arguments[0].Registry_id;
		else 
			form.Registry_id = null;

		if (arguments[0].PayType_SysNick)
			form.PayType_SysNick = arguments[0].PayType_SysNick;
		else
			form.PayType_SysNick = 'oms';

		if (arguments[0].RegistryStatus_id) 
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		else 
			form.RegistryStatus_id = null;
		if (arguments[0].RegistryType_id) 
			form.RegistryType_id = arguments[0].RegistryType_id;
		if (arguments[0].RegistrySubType_id)
			form.RegistrySubType_id = arguments[0].RegistrySubType_id;

		form.Registry_IsNew = (arguments[0].Registry_IsNew)?arguments[0].Registry_IsNew:null;

		if (form.RegistryType_id == 19 && form.findById('regeLpu_cid').getStore().getCount() == 0) {
			form.findById('regeLpu_cid').getStore().load({
				callback: function() {
					form.findById('regeLpu_cid').setValue(form.findById('regeLpu_cid').getValue());
				}
			});
		}

		if (form.RegistrySubType_id == 3) {
			// В поле «Начало периода» по умолчанию подставляется первый день текущего года
			var cur_dt = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			var firstDayOfYear = '01.01.' + cur_dt.format('Y');
			form.findById('regeRegistry_begDate').setValue(firstDayOfYear);
			// Скрыт флаг «Инотерриториальные»
			form.findById('regeRegistry_IsInoterCheckbox').hideContainer();
			// Скрыто поле «Код подр. ТФОМС»
			form.findById('regeLpuUnitSet_id').hideContainer();
			form.findById('regeLpuUnitSet_id').setAllowBlank(true);
			// Добавлено поле «Структурное подразделение МО». Поле отображается только для МО, у которых есть ОСП (в таблице LpuUnitSet для МО есть записи с заполненным полем Lpu_oid). Поле не обязательно для заполнения. В поле отображается наименование ОСП (наименование МО, найденной по Lpu_oid)
			form.findById('regeLpu_oid').showContainer();
			form.findById('regeLpu_oid').getStore().load({
				callback: function() {
					form.findById('regeLpu_oid').setValue(form.findById('regeLpu_oid').getValue());

					if (form.findById('regeLpu_oid').getStore().getCount() == 0) {
						form.findById('regeLpu_oid').hideContainer();
					}
				}
			});
			form.findById('regeDispClass_id').setAllowBlank(true);
			form.findById('regeDispClass_id').setContainerVisible(false);
		} else {
			if (form.PayType_SysNick == 'bud') {
				form.findById('regeRegistry_IsInoterCheckbox').hideContainer();
				form.findById('regeLpuUnitSet_id').hideContainer();
				form.findById('regeLpuUnitSet_id').setAllowBlank(true);
				form.findById('regeLpu_cid').hideContainer();
				form.findById('regeLpu_cid').setAllowBlank(true);
			} else {
				form.findById('regeRegistry_IsInoterCheckbox').showContainer();
				form.findById('regeLpuUnitSet_id').setContainerVisible(form.RegistryType_id != 19);
				form.findById('regeLpuUnitSet_id').setAllowBlank(form.RegistryType_id == 19);
				form.findById('regeLpu_cid').setContainerVisible(form.RegistryType_id == 19);
				form.findById('regeLpu_cid').setAllowBlank(form.RegistryType_id != 19);
			}
			form.findById('regeLpu_oid').hideContainer();

			form.findById('regeDispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '17' ]) || form.Registry_IsNew != 2);
			form.findById('regeDispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '17' ]) && form.Registry_IsNew == 2);

			if ( form.RegistryType_id.toString().inlist([ '7', '17' ]) && form.Registry_IsNew == 2 ) {
				var dispClassList = [];

				switch ( form.RegistryType_id ) {
					case 7: // Дисп-ция взр. населения с 2013 года
						dispClassList = [ '1', '2' ];
					break;

					case 17: // Проф.осмотры взр. населения; Профилактические осмотры несовершеннолетних 1-ый этап
						dispClassList = [ '5', '10' ];
					break;
				}

				form.findById('regeDispClass_id').getStore().clearFilter();
				form.findById('regeDispClass_id').lastQuery = '';
				form.findById('regeDispClass_id').getStore().filterBy(function(rec) {
					return (rec.get('DispClass_Code').toString().inlist(dispClassList));
				});
			}
		}
		
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
			if ((form.Registry_id) && (form.Registry_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}
		
		form.findById('regeRegistry_IsNotInsurCheckbox').setContainerVisible(false);

		form.syncSize();
		form.syncShadow();
		
		if ( form.action == 'edit' )
			this.buttons[0].setText('Переформировать');
		else
			this.buttons[0].setText('Сохранить');
		
		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if (form.RegistryStatus_id==4)
		{
			form.action = "view";
		}

		var base_form = form.findById('RegistryEditForm').getForm();
		base_form.reset();

		base_form.setValues(arguments[0]);

		base_form.findField('PayType_id').getStore().clearFilter();
		base_form.findField('PayType_id').lastQuery = '';
		if (form.PayType_SysNick == 'bud') {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['bud', 'fbud']));
			});
		} else if (form.RegistryType_id != 19) {
			base_form.findField('PayType_id').getStore().filterBy(function(rec) {
				return (rec.get('PayType_SysNick').inlist(['oms']));
			});
		}

		if ( 'add' == form.action ) {
			if (form.PayType_SysNick == 'bud') {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'bud');
			} else {
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
			}
			base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
		}
		
		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) 
		{
			//Новый action Task#18011
			case 'add_all' :
							form.hide();
							break;
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);
				form.loadCombo();
				loadMask.hide();
				//form.getForm().clearInvalid();
				form.findById('regeRegistry_begDate').focus(true, 50);
				break;
			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				break;
			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}
		
		// устанавливаем дату счета и запрещаем для редактирования
		form.findById('regeRegistry_accDate').disable();
		addToolTip(form.findById('rege_Registry_comment'), 'Обязательно для заполнения при подаче случаев, не соответствующих отчетному периоду');

		if (form.action!='add')
		{
			base_form.load(
			{
				params: 
				{
					RegistrySubType_id: form.RegistrySubType_id,
					Registry_id: form.Registry_id
				},
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				success: function() 
				{
					loadMask.hide();

					base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());

					form.loadCombo();

					if ( form.findById('regeRegistry_IsNotInsur').getValue() == 2 ) {
						form.findById('regeRegistry_IsNotInsurCheckbox').setValue(true);
					}

					if ( form.findById('regeOrgSmo_id').getValue() == 8 ) {
						form.findById('regeRegistry_IsInoterCheckbox').setValue(true);
					}

					form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
					addToolTip(form.findById('rege_Registry_comment'), 'Обязательно для заполнения при подаче случаев, не соответствующих отчетному периоду');
					if (form.action=='edit')
						form.findById('regeRegistry_begDate').focus(true, 50);
					else 
						form.focus();
				},
				url: '/?c=RegistryUfa&m=loadRegistry'
			});
		} else {
			form.findById('regeRegistry_accDate').setValue(getGlobalOptions().date);
			form.findById('regeRegistry_accDate').fireEvent('change', form.findById('regeRegistry_accDate'), form.findById('regeRegistry_accDate').getValue(), 0);
		}


	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.RegistryForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 190,
			items: 
			[{
				id: 'regeRegistry_id',
				name: 'Registry_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'regeLpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				id: 'regeOrgSmo_id',
				name: 'OrgSmo_id',
				xtype: 'hidden'
			},
			{
				id: 'regeRegistrySubType_id',
				name: 'RegistrySubType_id',
				xtype: 'hidden'
			},
			{
				xtype: 'hidden',
				name: 'RegistryStatus_id',
				id: 'regeRegistryStatus_id',
				value: 3 // По умолчанию при добавлении 
			}, 
			{
				xtype: 'hidden',
				name: 'Registry_IsActive',
				id: 'regeRegistry_IsActive',
				value: 2 // По умолчанию при добавлении 
			}, 
			{
				xtype: 'hidden',
				name: 'Registry_IsNotInsur',
				id: 'regeRegistry_IsNotInsur',
				value: 1 // По умолчанию при добавлении 
			}, 
			{
				anchor: '100%',
				disabled: true,
				name: 'RegistryType_id',
				xtype: 'swregistrytypecombo',
				id: 'regeRegistryType_id',
				tabIndex: form.firstTabIndex + 1
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Начало периода',
				id: 'regeRegistry_begDate',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = form.RegistryForm.getForm();

						if (form.RegistryType_id == 19) {
							var Lpu_cid = base_form.findField('Lpu_cid').getValue();

							base_form.findField('Lpu_cid').getStore().filterBy(function(rec) {
								return (
									Ext.isEmpty(newValue)
									|| (
										(Ext.isEmpty(rec.get('Lpu_BegDate')) || rec.get('Lpu_BegDate') <= newDate)
										&& (Ext.isEmpty(rec.get('Lpu_EndDate')) || rec.get('Lpu_EndDate') >= newDate)
										&& (Ext.isEmpty(rec.get('LpuDispContract_setDate')) || rec.get('LpuDispContract_setDate') <= newDate)
										&& (Ext.isEmpty(rec.get('LpuDispContract_disDate')) || rec.get('LpuDispContract_disDate') >= newDate)
									)
								);
							});

							if (!Ext.isEmpty(Lpu_cid)) {
								var index = base_form.findField('Lpu_cid').getStore().findBy(function(rec) {
									return (rec.get('Lpu_id') == Lpu_cid);
								});

								if (index >= 0 ) {
									base_form.findField('Lpu_cid').setValue(Lpu_cid);
								}
								else {
									base_form.findField('Lpu_cid').clearValue();
								}
							}
						}
					}
				},
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 2,
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				id: 'regeRegistry_endDate',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 3,
				width: 100,
				xtype: 'swdatefield'
			},
			{
				allowBlank: false,
				anchor: '100%',
				hiddenName: 'PayType_id',
				id: 'regePayType_id',
				listeners: {
					'change': function(combo, nv, ov) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('PayType_id') == nv);
						});

						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					}.createDelegate(this),
					'select': function(combo, record, idx) {
						var base_form  = this.RegistryForm.getForm();

						// какие то поля должны прятаться todo

						this.syncSize();
						this.syncShadow();
					}.createDelegate(this)
				},
				xtype: 'swpaytypecombo',
				tabIndex: form.firstTabIndex++
			},
			{
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип дисп-ции / медосмотра',
				id: 'regeDispClass_id',
				lastQuery: '',
				tabIndex: form.firstTabIndex + 4,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			},
			{
				allowBlank: true,
				xtype: 'swlpucombo',
				id: 'regeLpu_oid',
				hiddenName: 'Lpu_oid',
				fieldLabel: 'Структурное подразделение МО',
				tabIndex: form.firstTabIndex + 3,
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'Lpu_id', mapping: 'Lpu_id'},
						{name: 'Lpu_Name', mapping: 'Lpu_Name'},
						{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
						{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
						{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'}
					],
					key: 'Lpu_id',
					sortInfo: {field: 'Lpu_Nick'},
					tableName: 'Lpu',
					url: '/?c=Registry&m=getLpuOidList'
				}),
				anchor: '100%'
			},
			{
				fieldLabel: 'Незастрахованные лица',
				id: 'regeRegistry_IsNotInsurCheckbox',
				tabIndex: form.firstTabIndex + 4,
				xtype: 'checkbox'
			},
			{
				anchor: '100%',
				allowBlank: false,
				fieldLabel: 'Код подр. ТФОМС',
				xtype: 'swlpuunitsetcombo',
				id: 'regeLpuUnitSet_id',
				tabIndex: form.firstTabIndex + 5
			},
			{
				allowBlank: true,
				xtype: 'swlpucombo',
				id: 'regeLpu_cid',
				hiddenName: 'Lpu_cid',
				fieldLabel: 'МО-контрагент',
				tabIndex: form.firstTabIndex + 5.5,
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'Lpu_id', mapping: 'Lpu_id'},
						{name: 'Lpu_Name', mapping: 'Lpu_Name'},
						{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
						{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
						{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'},
						{name: 'LpuDispContract_setDate', mapping: 'LpuDispContract_setDate'},
						{name: 'LpuDispContract_disDate', mapping: 'LpuDispContract_disDate'}
					],
					key: 'Lpu_id',
					sortInfo: {field: 'Lpu_Nick'},
					tableName: 'Lpu',
					url: '/?c=RegistryUfa&m=getLpuCidList'
				}),
				anchor: '100%'
			},
			{
				fieldLabel: 'Инотерриториальные',
				id: 'regeRegistry_IsInoterCheckbox',
				listeners: {
					'check': function(field, value) {
						if ( value == true ) {
							form.findById('regeOrgSmo_id').setValue(8);
						}
						else {
							form.findById('regeOrgSmo_id').setValue('');
						}
					}
				},
				tabIndex: form.firstTabIndex + 5,
				xtype: 'checkbox'
			},
			{
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "10",
					autocomplete: "off"
				},
				fieldLabel: 'Номер счета',
				id: 'regeRegistry_Num',
				name: 'Registry_Num',
				tabIndex: form.firstTabIndex + 6,
				width: 100,
				xtype: 'textfield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				id: 'regeRegistry_accDate',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 7,
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Комментарий',
				id: 'rege_Registry_comment',
				name: 'Registry_Comments',
				anchor: '100%',
				maxLength: 250,
				tabIndex: TABINDEX_SPEF + 29,
				xtype: 'textarea'
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
				{ name: 'OrgSmo_id' },
				{ name: 'LpuUnitSet_id' },
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Lpu_cid' },
				{ name: 'Lpu_oid' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryType_id' },
				{ name: 'RegistrySubType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'Registry_IsActive' },
				{ name: 'Registry_IsNotInsur' },
				{ name: 'Registry_Comments' },
				{ name: 'Lpu_id' }
			]),
			timeout: 600,
			url: '/?c=RegistryUfa&m=saveRegistry'
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
				tabIndex: 15100+8
			}, 
			{
				text: '-'
			},
			HelpButton(this, 15100+9),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				tabIndex: 15100+9
			}],
			items: [form.RegistryForm]
		});
		sw.Promed.swRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
