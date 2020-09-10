/**
* swTimeJournalEditWindow - окно занесения информации в журнал учета
* рабочего времени и просмотра этой информации.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author
* @version      11.2019
*/
sw.Promed.swTimeJournalEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	id: 'TimeJournalEditWindow',
	objectName: 'swTimeJournalEditWindow',

// Заголовок устанавливается в зависимости от параметра _action:
	title: '',

	layout: 'form',
	buttonAlign: 'right',
	closeAction: 'hide',
	modal: true,
	plain: true,
	resizable: false,
	autoHeight: true,
	width: 500,

// Кнопки внизу окна:
	buttons:
	[
		{
			iconCls: 'save16',
			text: langs('sohranit'),
			
			handler: function()
			{
				this.ownerCt.doSave();
			}
		},
		'-',
		{
			iconCls: 'help16',
			text: BTN_FRMHELP,

			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			iconCls: 'cancel16',
			text: langs('otmena'),
			tooltip: langs('otmena'),
			tabIndex: -1,

			handler: function()
			{
				this.ownerCt.hide();
			}
		}
	],

// При закрытии окна обновляем состояние флага "Я на смене":
	listeners:
	{
		hide: function()
		{
			doIfOnWorkShift(this._pmUser_id, _setCbWorkShift, this);
		}
	},

// Продолжительность смены по умолчанию:
	_DEFAULT_SHIFT_DURATION: '08:00',

// Входные параметры:
//  1. Ид. пользователя, открывшего форму:
//  2. Ид. ЛПУ:
	_pmUser_id: undefined,
	_lpu_id: undefined,

// Форма и ее компоненты:
	_frmTimeJournal: undefined,
	_dtStart: undefined,
	_txtDuration: undefined,
	_dtFinish: undefined,

// Флаг, показывающий, завершена ли инициализация формы:
	_initItemsFinished: false,

// Идентификатор записи журнала, открытой на редактирование:
  _tj_id: undefined,

/******* initComponent ********************************************************
 *
 ******************************************************************************/
	initComponent: function()
	{
		this.items =
		[
			{
				itemId: 'frmTimeJournal',
				xtype: 'form',
				frame: true,
				border: false,
				labelAlign: 'top',

				items:
				[
					// Начало смены:
					{
						itemId: 'dtStart',
						xtype: 'datefield',
						name: 'TimeJournal_BegDT',
						format: 'd.m.Y H:i',
						anchor: '100%',
						fieldLabel: langs('Дата и время начала смены'),
						disabled: true
					},

					// Продолжительность смены:
					{
						itemId: 'txtDuration',
						xtype: 'textfield',
						regex: /\d{1,2}:\d\d/,
						fieldLabel: langs('Продолжительность смены'),

						listeners:
						{
							change: this._onChange_duration,
							scope: this
						}
					},

					// Завершение смены:
					{
						itemId: 'dtFinish',
						xtype: 'datefield',
						name: 'TimeJournal_EndDT',
						format: 'd.m.Y H:i',
						anchor: '100%',
						fieldLabel: langs('Дата и время завершения смены'),

						listeners:
						{
							change: this._onChange_finishDate,
							scope: this
						},

						validator: this._validateFinishDate,
						invalidText: langs('Значение должно иметь формат ДД.ММ.ГГГГ ЧЧ:ММ и не должно быть меньше даты и времени начала смены')
					}
				],

				reader: new Ext.data.JsonReader(
					{
						success:
							function()
							{
							}
					},
					[
						{ name: 'TimeJournal_BegDT' },
						{ name: 'TimeJournal_EndDT' }
					])
			}
		];

		sw.Promed.swTimeJournalEditWindow.superclass.initComponent.apply(this, arguments);

		setTimeout(() => this._finishInitComponent(), 1);
	},

/******* _finishInitComponent *************************************************
 *
 ******************************************************************************/
	_finishInitComponent: function()
	{
		this.items.each(this._findComponents, this);
		this._initItemsFinished = true;
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
		var v;

		if (!(v = arguments[0]) || !v.pmUser_id || !v.lpu_id)
		{
			Ext.Msg.alert(
				langs('oshibka_otkryitiya_formyi'),
				langs('oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'));
			return false;
		}

		sw.Promed.swTimeJournalEditWindow.superclass.show.apply(this, arguments);

		this._pmUser_id = v.pmUser_id;
		this._lpu_id = v.lpu_id;

		loadMask = new Ext.LoadMask(
						this.getEl(),
						{
							msg: langs('Получение данных о текущей рабочей смене...')
						});
		loadMask.show();

		doIfOnWorkShift(this.pmUser_id, _fillForm, this);

/******* _fillForm ************************************************************
 * Если найдена хотя бы одна подходящая смена, настраиваем форму для
 * редактирования даты и времени ее завершения, в противном случае - для
 * создания смены с текущих даты и времени с возможностью указать
 * продолжительность смены.
 */
		function _fillForm (pmUserId, curDT, records)
		{
			var v,
				dt;

			loadMask.hide();

			if (records && (records.length > 0) &&
				(v = records[0]) && v.TimeJournal_id)
			// Нашли - настраиваем форму для редактирования смены:
			{
				this.setTitle(langs('Укажите дату и время завершения смены'));
				this._tj_id = v.TimeJournal_id;

				if ((dt = v.TimeJournal_BegDT) && (dt = dt.date) &&
					(dt = new Date(dt)))
				{
					this._dtStart.setValue(dt);
					this._dtFinish.minDT = dt;
				}

				if ((dt = v.TimeJournal_EndDT) && (dt = dt.date) &&
					(dt = new Date(dt)))
					this._dtFinish.setValue(dt);

				this._dtFinish.setDisabled(false);
				this._txtDuration.setDisabled(true);
				this._onChange_finishDate();
			}
			else
			// Не нашли - настраиваем форму для создания смены:
			{
				this.setTitle(langs('Укажите продолжительность смены'));
				this._tj_id = null;
				this._dtStart.setValue(curDT);
				this._dtFinish.minDT = curDT;
				this._txtDuration.setValue(this._DEFAULT_SHIFT_DURATION);
				this._dtFinish.setDisabled(true);
				this._txtDuration.setDisabled(false);
				this._onChange_duration();
			}
		}
	},

/******* _onChange_duration ***************************************************
 *
 ******************************************************************************/
	_onChange_duration: function()
	{
		var dt = this._dtStart.getValue(),
			dur = this._txtDuration.getValue();

		if (dt && dur)
		{
			dt.setHours(dt.getHours() + Date.parse(dur).getHours());
			dt.setMinutes(dt.getMinutes() + Date.parse(dur).getMinutes());
			this._dtFinish.setValue(dt);
		}
		else
			this._dtFinish.setValue();
	},

/******* _onChange_finishDate *************************************************
 *
 ******************************************************************************/
	_onChange_finishDate: function(field, newValue, oldValue)
	{
		var startDT = this._dtStart.getValue(),
			finishDT = this._dtFinish.getValue(),
			diff,
			hours,
			minutes,
			str;

		if (startDT && finishDT)
		{
			diff = finishDT - startDT;
			hours = Math.trunc(diff / 3600000);
			minutes = Math.trunc(diff / 60000) - hours*60;

			str = (hours < 10 ? '0' : '') + hours +
				(minutes < 10 ? ':0' : ':') + minutes;

			this._txtDuration.setValue(str);
		}
		else
			this._txtDuration.setValue();
	},

/******* _validateFinishDate *************************************************
 *
 ******************************************************************************/
	_validateFinishDate: function(strValue)
	{
		var v = this.getValue();

		return (!this.minDT || (v && v > this.minDT));
	},

/******* doSave ***************************************************************
 *
 ******************************************************************************/
	doSave: function()
	{
		var win = this;

		if (!this._frmTimeJournal)
			return (false);

		if (!this._frmTimeJournal.getForm().isValid())
		{
			sw.swMsg.alert(langs('oshibka'), langs('ne_zapolneno_obyazatelnoe_pole'));
			return (false);
		}

		Ext.Ajax.request(
			{
				url: '/?c=TimeJournal&m=saveTimeJournalRecord',

				params:
				{
					TimeJournal_id: this._tj_id,
					pmUser_tid: this._pmUser_id,
					pmUser_id: this._pmUser_id,
					TimeJournal_BegDT: this._dtStart.getValue(),
					TimeJournal_EndDT: this._dtFinish.getValue(),
					Server_id: this._lpu_id,
				},

				callback: function(options, success, response)
				{
					if (success)
					{
						win.hide();

						if (win.callback)
							win.callback(win.owner, 1);
					}
				}
			});
	}
});
