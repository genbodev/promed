/**
 * swNoticeModeUnitsWindow - окно редактирования режима уведомлений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.05.2014
 */

/*NO PARSE JSON*/

sw.Promed.swNoticeModeUnitsWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swNoticeModeUnitsWindow',
	width: 400,
	autoHeight: true,
	modal: true,
	titleWin: 'Режим уведомлений',
	callback: Ext.emptyFn,
	doSave: function() {
		let me = this,
			base_form = me.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function() {
						this.FormPanel.getFirstInvalidEl().focus(true);
					}.createDelegate(me),
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		var loadMask = new Ext.LoadMask(me.getEl(), { msg: LOAD_WAIT_SAVE });
		
		if (me.parentWin) {
			var parentForm = me.parentWin.FormPanel.getForm();
			var lpu = parentForm.findField('Lpu_sid');
		}

		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=NoticeModeSettings&m=checkNotifySettingsExist',
			params: {
				Lpu_sid : lpu.getValue() ? lpu.getValue() : null,
				NoticeModesType_id : base_form.findField('NoticeModesType_id').getValue() ? base_form.findField('NoticeModesType_id').getValue() : null,
				NoticeFreqUnitsType_id : base_form.findField('NoticeFreqUnitsType_id').getValue() ? base_form.findField('NoticeFreqUnitsType_id').getValue() : null,
				NoticeModeLink_Frequency : base_form.findField('NoticeModeLink_Frequency').getValue() ? base_form.findField('NoticeModeLink_Frequency').getValue() : null,
			},
			success: function(response) {
				let result = Ext.util.JSON.decode(response.responseText);
				if (result.exist) {
					loadMask.hide();
					me.parentWin.showWarning("Для " + lpu.lastSelectionText + " уже установлен данный режим");
				} else {
					base_form.submit({
						success: function(result_form, response) {
							let result = response.result;
							loadMask.hide();
							if (result.success) {
								me.callback();
								me.hide();
							} else if (result.Error_Msg) {
								sw.swMsg.alert(langs('Ошибка'), result.Error_Msg);
							}
						}
					});
				}
			}
		});
	},
	show: function() {
		let me = this;
		sw.Promed.swNoticeModeUnitsWindow.superclass.show.apply(this, arguments);
		me.action = 'view';
		me.NoticeModeSettings_id = null;
		me.callback = Ext.emptyFn;
		let base_form = me.FormPanel.getForm();
		
		base_form.reset();
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { me.hide(); });
			return false;
		}
		
		if (arguments[0].NoticeModeSettings_id) {
			me.NoticeModeSettings_id = arguments[0].NoticeModeSettings_id;
		}
		if (arguments[0].NoticeModeLink_id) {
			me.NoticeModeLink_id = arguments[0].NoticeModeLink_id;
		}
		if (arguments[0].action) {
			me.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		}
		if (arguments[0].parentWin) {
			me.parentWin = arguments[0].parentWin;
		}
		
		base_form.setValues(arguments[0]);
		
		let loadMask = new Ext.LoadMask(me.getEl(),{msg: LOAD_WAIT});
		
		loadMask.show();
		
		switch (me.action)
		{
			case 'add':
				me.setTitle(me.titleWin + lang['_dobavlenie']);
				me.enableEdit(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (me.action == 'edit') {
					me.setTitle(me.titleWin + lang['_redaktirovanie']);
					me.enableEdit(true);
				} else {
					me.setTitle(me.titleWin + lang['_prosmotr']);
					me.enableEdit(false);
				}

				Ext.Ajax.request({
					url: '/?c=NoticeModeSettings&m=loadNoticeModeLinkForm',
					params: {NoticeModeLink_id: base_form.findField('NoticeModeLink_id').getValue()},
					success: function(response) {
						let result = Ext.util.JSON.decode(response.responseText);
						loadMask.hide();
						if (!result[0]) { return false; }
						base_form.setValues(result[0]);
					},
					failure: function() {
						loadMask.hide();
					}
				});
				break;
		}
	},

	initComponent: function() {
		let me = this;
		
		me.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'NMUW_NoticeModeUnitsForm',
			bodyStyle: 'padding: 10px 5px 10px 20px;',
			labelAlign: 'right',
			url: '/?c=NoticeModeSettings&m=saveNoticeModeLink',
			items: [{
				xtype: 'hidden',
				name: 'NoticeModeSettings_id'
			}, {
				xtype: 'hidden',
				name: 'NoticeModeLink_id'
			}, {
				allowBlank: false,
				comboSubject: 'NoticeModesType',
				fieldLabel: 'Режим',
				name: 'NoticeModesType_id',
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Частота',
				maskRe: /[0-9+-.]/,
				maxValue: 30,
				minValue: 0,
				xtype: 'numberfield',
				name: 'NoticeModeLink_Frequency'
			}, {
				allowBlank: false,
				comboSubject: 'NoticeFreqUnitsType',
				fieldLabel: 'Единицы измерения',
				hiddenName: 'NoticeFreqUnitsType_id',
				xtype: 'swcommonsprcombo'
			}]
		});

		Ext.apply(me, {
			items: [ me.FormPanel ],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'NMUW_ButtonSave',
					tooltip: langs('Сохранить'),
					iconCls: 'save16',
					handler: function()
					{
						me.doSave();
					}.createDelegate(this)
				}, { text: '-' },
				{
					handler: function () {
						me.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'NMUW_CancelButton',
					text: langs('Отменить')
				}]
		});

		sw.Promed.swNoticeModeUnitsWindow.superclass.initComponent.apply(me, arguments);
	}
});