/**
* swHeadCircumferenceEditWindow - форма "Окружность головы"
* Разработана по задаче #182939
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Person
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author
* @version      12.2010
* @comment
*/
sw.Promed.swHeadCircumferenceEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	id: 'HeadCircumferenceEditWindow',

	width: 600,
	autoHeight: true,
	closeAction: 'hide',
	buttonAlign: 'left',
	modal: true,
	plain: true,
	resizable: false,
	layout: 'form',

	formMode: 'remote',
	formStatus: 'edit',
	action: null,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,

	keys:
		[{
			key: [Ext.EventObject.C, Ext.EventObject.J],
			alt: true,

			fn: function(inp, e)
				{
					var win = Ext.getCmp('HeadCircumferenceEditWindow');

					switch (e.getKey())
					{
						case Ext.EventObject.C:
							win.doSave();
							break;

						case Ext.EventObject.J:
							win.hide();
							break;
					}
				},

			scope: this,
			stopEvent: true
		}],

	listeners:
		{
			'beforehide': function(win)
				{
					//
				},

			'hide': function(win)
				{
					win.onHide();
				}
		},

// Входные параметры:
	_hcId: undefined,
	_personId: undefined,
	_personChildId: undefined,
	_setDate: undefined,
	_pmUserId: undefined,

// Форма и компоненты:
	_formPanel: undefined,
	_form: undefined,
	_dtSetDate: undefined,
	_cbMeasureType: undefined,
	_numHead: undefined,
	_btnSave: undefined,
	_btnCancel: undefined,

	_initCompleted: undefined,

/******* initComponent ********************************************************
 *
 ******************************************************************************/
	initComponent: function()
	{
		this._formPanel = new Ext.form.FormPanel(
			{
				id: 'HeadCircumferenceEditForm',
				autoHeight: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: true,
				labelAlign: 'right',
				labelWidth: 150,

				url: '/?c=HeadCircumference&m=saveHeadCircumference',

				reader:
					new Ext.data.JsonReader(
						{
							success: Ext.emptyFn
						},
						[
							{ name: 'HeadCircumference_setDate' },
							{ name: 'HeightMeasureType_id' },
							{ name: 'HeadCircumference_Head' }
						]),

				items:
					[
						{
							xtype: 'swdatefield',
							itemId: 'dtSetDate',
							name: 'HeadCircumference_setDate',
							fieldLabel: langs('Окружность головы'),
							allowBlank: false,
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_HCEF + 1,
							width: 100
						},
						{
							xtype: 'swcommonsprcombo',
							itemId: 'cbMeasureType',
							comboSubject: 'HeightMeasureType',
							hiddenName: 'HeightMeasureType_id',
							fieldLabel: langs('vid_zamera'),
							allowBlank: false,
							disabled: true,
							autoLoad: false,
							lastQuery: '',
							tabIndex: TABINDEX_HCEF + 2,
							width: 350,

							listeners:
								{
								 'render': function(combo)
									{
										combo.getStore().load();
									}
								}
						},
						{
							xtype: 'numberfield',
							itemId: 'numHead',
							name: 'HeadCircumference_Head',
							allowBlank: false,
							allowNegative: false,
							fieldLabel: langs('Окружность головы (см)'),
							regex:new RegExp('(^[0-9]{0,3}\.[0-9]{0,2})$'),
							minValue: 1.00,
							maxValue: 299.99,
							tabIndex: TABINDEX_HCEF + 3,
							width: 100
						}
					]
			});

		this.items = [this._formPanel];

		this.buttons =
			[
				{
					itemId: 'btnSave',
					iconCls: 'save16',
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_HCEF + 6,

					onShiftTabAction:
						this._shiftTabAction.createDelegate(this),
					onTabAction:
						this._tabAction.createDelegate(this),

					handler: this.doSave,
					scope: this
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					itemId: 'btnCancel',
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_HCEF + 7,

					onShiftTabAction:
						this._shiftTabAction.createDelegate(this),
					onTabAction:
						this._tabAction.createDelegate(this),

					handler: function()
					{
						this.hide();
					},

					scope: this
				}
			];

		sw.Promed.swHeadCircumferenceEditWindow.superclass.initComponent
			.apply(this, arguments);

		this._form = this._formPanel.getForm();

		setTimeout(() => this._finishInitComponent(), 1);
	},

/******* _finishInitComponent *************************************************
 *
 ******************************************************************************/
	_finishInitComponent: function()
	{
		this.items.each(this._findComponents, this);

		this.buttons.forEach(btn =>
								{
									if (btn.itemId)
										this['_' + btn.itemId] = btn;
								});

		this._initCompleted = true;
	},

/******* _findComponents ******************************************************
 *
 ******************************************************************************/
	_findComponents: function(item)
	{
		if (item.itemId)
			this['_' + item.itemId] = item;

		if (item.items)
			item.items.each(this._findComponents, this);
	},

/******* show *****************************************************************
 *
 ******************************************************************************/
	show: function()
	{
		var arg,
			v,
			record,
			loadMask,
			actionTxt = '';

		sw.Promed.swHeadCircumferenceEditWindow.superclass.show
			.apply(this, arguments);

		this.center();
		this._form.reset();

		// Если параметры некорректные, выдаем ошибку и скрываем окно:
		if (!(arg = arguments[0]) || !this._fillFormParams(arg))
		{
			sw.swMsg.alert(langs('soobschenie'), langs('nevernyie_parametryi'),
						   this.hide, this);
			return false;
		}

		this.formStatus = 'edit';

		switch (this.action)
		{
			case 'add':
				actionTxt = 'Добавление';
				break;

			case 'edit':
				actionTxt = 'Редактирование';
				break;

			case 'view':
				actionTxt = 'Просмотр';
		}

		this.setTitle(langs('Окружность головы') + ': ' + langs(actionTxt));

		if (this.action != 'add')
			if (this.formMode == 'local')
				this._form.setValues(arg.formParams);
			else
			{
				loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();

				// Загрузим данные из БД:
				this._form.load(
					{
						url: '/?c=HeadCircumference&m=loadHeadCircumferenceEditForm',

						params:
						{
							HeadCircumference_id: this._hcId,
							loadMask: loadMask
						},

						success: this._onLoadSuccess,
						failure: this._onLoadFailure,
						scope: this
					});
			};

		this._callAfterFn(() => this._initCompleted, this._finishShow);
	},

/******* _callAfterFn *********************************************************
 *
 ******************************************************************************/
	_callAfterFn: function(flagFn, execFn, scope, params)
	{
		if (flagFn())
			execFn.apply(scope || this, params || []);
		else
			setTimeout(() => this._callAfterInit(flagFn, execFn, scope, params),
					   1);
	},

/******* _finishShow **********************************************************
 *
 ******************************************************************************/
	_finishShow()
	{
		this._cbMeasureType.getStore().clearFilter();
		this._cbMeasureType.lastQuery = '';

		// Настроим доступность полей ввода и фокус:
		if (this.action == 'view' ||
			this.action == 'edit' && this.formMode == 'local')
		{
			this._numHead.disable();
			this._btnCancel.focus();
		}
		else
		{
			this._numHead.enable();
			this._numHead.focus();
		}

		if (this.action == 'add')
		{
			this._dtSetDate.setValue(new Date());
			this._cbMeasureType.setValue(3);
		}
	},

/******* _fillFormParams ******************************************************
 * Заполнение параметров формы значениями из arg:
 *   formMode
 *   action
 *   callback
 *   onHide
 *   _hcId
 *   _personId
 *   _personChildId
 *   _setDate
 *   _pmUserId
 * Возвращает, корректны ли параметры:
 *   true - корректно
 *   false - некорректно
 ******************************************************************************/
	_fillFormParams: function(arg)
	{
		var res;

		if (!((this.formMode = arg.formMode) &&
				(typeof this.formMode == 'string') &&
				this.formMode.inlist(['local', 'remote'])))
			this.formMode = 'remote';

		if (!((this.action = arg.action) &&
				(typeof this.action == 'string') &&
				this.action.inlist(['add', 'edit', 'view'])))
			return false;

		if (!arg.formParams)
			return false;

		this._hcId = arg.formParams.HeadCircumference_id;
		this._personId = arg.formParams.Person_id;
		this._personChildId = arg.formParams.PersonChild_id;
		this._setDate = arg.formParams.HeadCircumference_setDate;

		switch (this.action)
		{
			case 'add':
				if (!(this._personId || this._personChildId))
					return false;
				break;

			case 'view':
			case 'edit':
				if (this.formMode == 'remote' && !this._hcId)
					return false;
				break;
		}

		this.callback = arg.callback ? arg.callback : Ext.emptyFn;
		this.onHide = arg.onHide ? arg.onHide : Ext.emptyFn;

		this._pmUserId = getGlobalOptions().pmuser_id;

		return true;
	},

/******* _onLoadSuccess *******************************************************
 *
 ******************************************************************************/
	_onLoadSuccess: function(form, action)
	{
		var dt = action.result.data.HeadCircumference_setDate,
			v;

		if (dt && this._dtSetDate)
			this._dtSetDate.setValue(dt);

		if (v = action.options.params.loadMask)
			v.hide();
	},

/******* _onLoadFailure *******************************************************
 *
 ******************************************************************************/
	_onLoadFailure: function(form, action)
	{
		var v;

		if (v = action.options.params.loadMask)
			v.hide();

		sw.swMsg.alert(langs('oshibka'),
						action.result.Error_Msg ||
							langs('oshibka_pri_zagruzke_dannyih_formyi'),
						form.hide, form);
	},

/******* doSave ***************************************************************
 * options @Object
 ******************************************************************************/
	doSave: function(options)
	{
		var loadMask,
			pars;

		if (this.formStatus == 'save')
			return false;

		this.formStatus = 'save';

		if (!this._form.isValid())
		{
			sw.swMsg.show(
				{
					title: ERR_INVFIELDS_TIT,
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					buttons: Ext.Msg.OK,

					fn: function()
						{
							this.formStatus = 'edit';
							this._formPanel.getFirstInvalidEl().focus(false);
						},

					scope: this
				});

			return false;
		}

		loadMask = new Ext.LoadMask(this.getEl(),
									{
										msg: langs('podojdite_idet_sohranenie')

									});
		loadMask.show();

		pars =
			{
				'HeadCircumference_id': this._hcId,
				'Person_id': this._personId,
				'PersonChild_id': this._personChildId,
				'HeadCircumference_setDate': this._dtSetDate.getValue(),
				'HeadCircumference_Head': this._numHead.getValue(),
				'HeightMeasureType_id': this._cbMeasureType.getValue(),
				'pmUser_id': this._pmUserId,
				loadMask: loadMask
			};

		if  (this.formMode == 'local')
		{
			this.callback(
				{
					headCircumferenceData: pars
				});

			this.formStatus = 'edit';
			loadMask.hide();
			this.hide();
		}
		else
			this._form.submit({
								params: pars,
								failure: this._onSaveFailure,
								success: this._onSaveSuccess,
								scope: this
							});
	},

/******* _onSaveSuccess *******************************************************
 *
 ******************************************************************************/
	_onSaveSuccess: function(form, action)
	{
		var v,
			data;

		this.formStatus = 'edit';

		if (v = action.options.params.loadMask)
			v.hide();

		if (action.result)
		{
			if (action.result.HeadCircumference_id > 0)
			{
				this._hcId = action.result.HeadCircumference_id;

				data =
				{
					headCircumferenceData:
						{
							'HeadCircumference_id': this._hcId,
							'Person_id': this._personId,
							'PersonChild_id': this._personChildId,
							'HeadCircumference_setDate': this._dtSetDate.getValue(),
							'HeadCircumference_Head': this._numHead.getValue(),
							'HeightMeasureType_id': this._cbMeasureType.getValue()
						}
				};

				this.callback(data);
				this.hide();
			}
			else
				sw.swMsg.alert(langs('oshibka'),
								action.result.Error_Msg ||
								langs('pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]'));
		}
		else
			sw.swMsg.alert(langs('oshibka'),
							langs('pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]'));
	},

/******* _onSaveFailure *******************************************************
 *
 ******************************************************************************/
	_onSaveFailure: function(form, action)
	{
		var v;

		this.formStatus = 'edit';

		if (v = action.options.params.loadMask)
			v.hide();

		if (action.result)
			sw.swMsg.alert(langs('oshibka'),
							action.result.Error_Msg ||
								langs('pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]'));
	},

/******* _shiftTabAction ******************************************************
 * Переключает фокус с кнопки btn на предыдущий компонент, доступный на форме.
 * Для кнопки "Сохранить" это поле "Окружность головы", если оно доступно, и
 * кнопка "Отмена" в противном случае.
 * Для кнопки "Отмена" это кнопка "Сохранить".
 ******************************************************************************/
	_shiftTabAction: function(btn)
	{
		if (btn == this._btnCancel) //.itemId == 'btnCancel')
			this._btnSave.focus(true);
		else
			if (btn == this._btnSave) //.itemId == 'btnSave')
				if (this._numHead && !this._numHead.disabled)
					this._numHead.focus();
				else
					this._btnCancel.focus(true);
	},

/******* _tabAction ***********************************************************
 * Переключает фокус с кнопки btn на следующий компонент, доступный на форме.
 * Для кнопки "Сохранить" это кнопка "Отмена".
 * Для кнопки "Отмена" это поле "Окружность головы", если оно доступно, и
 * кнопка "Сохранить" в противном случае.
 ******************************************************************************/
	_tabAction: function(btn)
	{
		if (btn == this._btnSave) //.itemId == 'btnSave')
			this._btnCancel.focus(true);
		else
			if (btn == this._btnCancel) //.itemId == 'btnCancel')
				if (this._numHead && !this._numHead.disabled)
					this._numHead.focus();
				else
					this._btnSave.focus(true);
	}
});
