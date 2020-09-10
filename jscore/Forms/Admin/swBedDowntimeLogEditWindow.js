/**
 * swBedDowntimeLogEditWindow - фора реадактирования Простоя коек
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Borisov Igor
 * @version			18.04.2020
 */

sw.Promed.swBedDowntimeLogEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swBedDowntimeLogEditWindow',
	width: 640,
	autoHeight: true,
	modal: true,
	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,
	resizable: false,
	doSave: function () {
		if (this.formStatus === 'save') {
			return false;
		}
		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();
		var form = this;
		var reg = /\d$/;

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					log(this);
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var bedDowntimeLog_RepairCount = form.findById('BedDowntimeLog_RepairCount').getValue();
		var plainBeds = form.findById('plainBeds').getValue();
		if (bedDowntimeLog_RepairCount < 0 || !reg.test(bedDowntimeLog_RepairCount)) {
			var msg = 'Поле "Из них на ремонте" не соответствует формату';
			var field = 'BedDowntimeLog_RepairCount';

			return this.printWarning(msg, field);
		}

		if (parseInt(bedDowntimeLog_RepairCount) > parseInt(plainBeds)) {
			var msg = 'Поле "Из них на ремонте" не может быть больше, чем поле "Простой коек, КД"';
			var field = 'BedDowntimeLog_RepairCount';

			return this.printWarning(msg, field);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		var params = {};
		params.BedDowntimeLog_Count = form.findById('BedDowntimeLog_Count').getValue();
		params.BedDowntimeLog_Reasons = form.findById('BedDowntimeLog_Reasons').getValue();
		params.BedDowntimeLog_ReasonsCount = form.findById('BedDowntimeLog_ReasonsCount').getValue();
		params.begDate = Ext.util.Format.date(base_form.findField('BedDowntime_PeriodDate').getValue1(), 'Y-m-d');
		params.endDate = Ext.util.Format.date(base_form.findField('BedDowntime_PeriodDate').getValue2(), 'Y-m-d');
		params.action = this.action;
		loadMask.show();

		base_form.submit({
			params: params,
			failure: function (result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this),
			success: function (result_form, action) {
				loadMask.hide();
				if (typeof this.callback == 'function') {
					this.callback();
				}
				this.formStatus = 'edit';
				this.hide();
			}.createDelegate(this)
		});
	},
	printWarning: function (msg, field) {
		var form = this;

		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.WARNING,
			fn: function () {
				this.formStatus = 'edit';
				form.findById(field).focus(true);
			}.createDelegate(this),
			msg: msg,
			title: ERR_INVFIELDS_TIT
		});

		return false;
	},
	show: function () {
		sw.Promed.swBedDowntimeLogEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.FormPanel.getForm();

		this.restore();
		this.center();
		base_form.reset();
	
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		this.hideDBObject = false;
		if (arguments[0].hideDBObject) {
			this.hideDBObject = arguments[0].hideDBObject;
		}
		if (arguments[0].BedDowntimeLog_id) {
			form.findById('BedDowntimeLog_id').setValue(arguments[0].BedDowntimeLog_id);
		}
		if (arguments[0].LpuSection_id) {
			form.findById('LpuSection_id').setValue(arguments[0].LpuSection_id);
		}
		if (arguments[0].LpuSection) {
			form.findById('LpuSection').setValue(arguments[0].LpuSection);
		}
		form.findById('pmuser_id').setValue(getGlobalOptions().pmuser_id);

		this.syncShadow();

		var loadMask = new Ext.LoadMask(form.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		switch (this.action) {
			case 'add':
				form.enableEdit(true);
				form.setTitle(langs('Простой коек'));
				loadMask.hide();

				form.findById('BedDowntime_PeriodDate').setValue(new Date().format('d.m.Y') + ' - ' + new Date().format('d.m.Y'));
				form.findById('durationOfPeriod').setValue(1);
				break;
			case 'edit':
			case 'view':
				if (this.action === 'edit') {
					form.enableEdit(true);
					form.setTitle(langs('Простой коек: Редактирование'));
				} else {
					form.enableEdit(false);
					form.setTitle(langs('Простой коек: Просмотр'));
					form.findById('BedDowntimeLog_Count').hideContainer();
					form.findById('durationOfPeriod').showContainer();
				}

				base_form.load({
					failure: function () {
						loadMask.hide();
					},
					url: '/?c=BedDowntimeLog&m=loadBedDowntimeJournalForm',
					params: {BedDowntimeLog_id: base_form.findField('BedDowntimeLog_id').getValue()},
					success: function () {
						loadMask.hide();
						if (this.action === 'edit' || this.action === 'add') {
							this.LoadEnvPs();
						}
					}.createDelegate(this)
				});

				break;
		}
		if (this.action === 'add' || this.action === 'edit') {
			// form.findById('durationOfPeriod').hideContainer();
			form.findById('BedDowntimeLog_Count').showContainer();
			form.findById('BedDowntimeLog_Count').setDisabled(true);
			this.filterProfileBed();
		}
		this.syncShadow();
	},
	filterProfileBed: function () {
		var form = this;
		var base_form = form.FormPanel.getForm();

		var begDate = Ext.util.Format.date(base_form.findField('BedDowntime_PeriodDate').getValue1(), 'd.m.Y');
		var endDate = Ext.util.Format.date(base_form.findField('BedDowntime_PeriodDate').getValue2(), 'd.m.Y');
		base_form.findField('LpuSectionBedProfile_id').getStore().clearFilter();
		base_form.findField('LpuSectionBedProfile_id').lastQuery = '';
		base_form.findField('LpuSectionBedProfile_id').getStore().filterBy(function (rec) {
			return (
				(Ext.isEmpty(rec.get('LpuSectionBedProfile_begDate')) || rec.get('LpuSectionBedProfile_begDate') <= begDate)
				&& (Ext.isEmpty(rec.get('LpuSectionBedProfile_endDate')) || rec.get('LpuSectionBedProfile_endDate') >= endDate)
			);
		});
	},
	LoadEnvPs: function () {
		var form = this;
		var win = this;
		var base_form = form.FormPanel.getForm();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		var LpuSection_id = form.findById('LpuSection_id').getValue();
		var bedProfile_id = form.findById('BedProfile_id').getValue();

		var begDate = Ext.util.Format.date(base_form.findField('BedDowntime_PeriodDate').getValue1(), 'd.m.Y');
		var endDate = Ext.util.Format.date(base_form.findField('BedDowntime_PeriodDate').getValue2(), 'd.m.Y');

		Ext.Ajax.request({
			url: '/?c=BedDowntimeLog&m=getSumEnvPS',
			params: {
				LpuSection_id: LpuSection_id,
				LpuSectionBedProfile_id: bedProfile_id,
				begDate: begDate,
				endDate: endDate,
			},
			callback: function (opt, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					var sumEnvPS = 0;
					if (result[0].sumEnvPS) {
						sumEnvPS = result[0].sumEnvPS;
					}

					form.findById('sumEnvPs').setValue(sumEnvPS);
					win.countingPlainBeds();
					win.loadReasons();
					loadMask.hide();
				}
			},
		});
	},
	countingPlainBeds: function () {
		var form = this;
		var durationOfPeriod = form.findById('durationOfPeriod').getValue();
		var bedDowntimeLog_Count = form.findById('BedDowntimeLog_Count').getValue();
		var sumEnvPs = form.findById('sumEnvPs').getValue();

		var plainBeds = durationOfPeriod * bedDowntimeLog_Count - sumEnvPs;
		if (plainBeds < 0) {
			plainBeds = 0;
		}

		form.findById('plainBeds').setValue(plainBeds);
	},
	loadReasons: function () {
		var form = this;
		var plainBeds = form.findById('plainBeds').getValue();
		var bedDowntimeLog_RepairCount = form.findById('BedDowntimeLog_RepairCount').getValue();
		var reasonsCount = plainBeds - bedDowntimeLog_RepairCount;

		if (bedDowntimeLog_RepairCount >= 0 && reasonsCount >= 0) {
			form.findById('BedDowntimeLog_ReasonsCount').setValue(plainBeds - bedDowntimeLog_RepairCount);
		}
		if (reasonsCount > 0) {
			form.findById('BedDowntimeLog_Reasons').setDisabled(false);
		} else {
			form.findById('BedDowntimeLog_ReasonsCount').setValue(0);
			form.findById('BedDowntimeLog_Reasons').setValue('');
			form.findById('BedDowntimeLog_Reasons').setDisabled(true);
		}
	},
	initComponent: function () {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'BedDowntimeLogEditForm',
			labelWidth: 150,
			labelAlign: 'right',
			url: '/?c=BedDowntimeLog&m=saveBedDowntimeLog',
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			}, [
				{name: 'BedDowntimeLog_id'},
				{name: 'LpuSection_id'},
				{name: 'BedProfile_id'},
				{name: 'BedDowntimeLog_Count'},
				{name: 'BedDowntime_PeriodDate'},
				{name: 'durationOfPeriod'},
				{name: 'plainBeds'},
				{name: 'BedDowntimeLog_RepairCount'},
				{name: 'BedDowntimeLog_ReasonsCount'},
				{name: 'BedDowntimeLog_Reasons'},
			]),
			items: [
				{
					xtype: 'hidden',
					name: 'pmuser_id',
					id: 'pmuser_id'
				},
				{
					xtype: 'hidden',
					name: 'BedDowntimeLog_id',
					id: 'BedDowntimeLog_id'
				},
				{
					xtype: 'hidden',
					name: 'LpuSection_id',
					id: 'LpuSection_id'
				},
				{
					xtype: 'hidden',
					name: 'sumEnvPs',
					id: 'sumEnvPs'
				},
				{
					xtype: 'swcommonsprcombo',
					disabled: true,
					name: 'LpuSection',
					fieldLabel: lang['otdelenie'],
					id: 'LpuSection'
				}, {
					allowBlank: false,
					xtype: 'swcommonsprcombo',
					fieldLabel: lang['profil_koek'],
					lastQuery: '',
					moreFields: [
						{
							name: 'LpuSectionBedProfile_begDate',
							mapping: 'LpuSectionBedProfile_begDate',
							type: 'date',
							dateFormat: 'd.m.Y',
						},
						{
							name: 'LpuSectionBedProfile_endDate',
							mapping: 'LpuSectionBedProfile_endDate',
							type: 'date',
							dateFormat: 'd.m.Y'
						}
					],
					listeners: {
						'select': function () {
							var form = this;
							var win = this;
							var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
							var LpuSection_id = form.findById('LpuSection_id').getValue();
							var bedProfile_id = form.findById('BedProfile_id').getValue();

							loadMask.show();
							Ext.Ajax.request({
								url: '/?c=BedDowntimeLog&m=getBedDowntimeLog_Count',
								params: {
									LpuSection_id: LpuSection_id,
									LpuSectionBedProfile_id: bedProfile_id
								},
								callback: function (opt, success, response) {
									if (success) {
										var result = Ext.util.JSON.decode(response.responseText);
										var bedCount = 0;
										if (result[0].bedCount) {
											bedCount = result[0].bedCount;
										}

										form.findById('BedDowntimeLog_Count').setValue(bedCount);
										win.LoadEnvPs();
									}
								},
							});
						}.createDelegate(this)
					},
					name: 'BedProfile_id',
					anchor: '99%',
					id: 'BedProfile_id',
					comboSubject: 'LpuSectionBedProfile'
				},
				{
					xtype: 'textfield',
					maskRe: /[0-9]/,
					fieldLabel: lang['kolichestvo_koek'],
					name: 'BedDowntimeLog_Count',
					hiddenName: 'BedDowntimeLog_Count',
					anchor: '99%',
					id: 'BedDowntimeLog_Count'
				},
				{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [
							{
								allowBlank: false,
								xtype: 'daterangefield',
								name: 'BedDowntime_PeriodDate',
								id: 'BedDowntime_PeriodDate',
								fieldLabel: langs('Период простоя'),
								width: 180,
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								listeners: {
									'select': function () {
										var form = this;
										var base_form = form.FormPanel.getForm();

										var begDate = Ext.util.Format.date(base_form.findField('BedDowntime_PeriodDate').getValue1(), 'Y-m-d');
										var endDate = Ext.util.Format.date(base_form.findField('BedDowntime_PeriodDate').getValue2(), 'Y-m-d');

										var date1 = new Date(begDate);
										var date2 = new Date(endDate);
										var diffTime = Math.abs(date2 - date1);
										var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

										form.findById('durationOfPeriod').setValue(diffDays);

										this.filterProfileBed();
										this.LoadEnvPs();
									}.createDelegate(this)
								},
								maxValue: new Date(),
							},
						]
					}]
				}, {
					xtype: 'textfield',
					disabled: true,
					name: 'durationOfPeriod',
					fieldLabel: langs('Длительность периода'),
					anchor: '99%',
					id: 'durationOfPeriod'
				},
				{
					xtype: 'textfield',
					disabled: true,
					name: 'plainBeds',
					fieldLabel: langs('Простой коек, КД'),
					anchor: '99%',
					id: 'plainBeds'
				},
				{
					allowBlank: false,
					xtype: 'textfield',
					name: 'BedDowntimeLog_RepairCount',
					fieldLabel: langs('Из них на ремонте, КД'),
					anchor: '99%',
					maskRe: /[0-9]/,
					value: 0,
					id: 'BedDowntimeLog_RepairCount',
					listeners: {
						'change': function (field, newValue, oldValue) {
							this.loadReasons();
						}.createDelegate(this)
					}
				},
				{
					disabled: true,
					xtype: 'textfield',
					name: 'BedDowntimeLog_ReasonsCount',
					fieldLabel: langs('По другим причинам, КД'),
					anchor: '99%',
					id: 'BedDowntimeLog_ReasonsCount'
				},
				{
					disabled: true,
					xtype: 'textfield',
					name: 'BedDowntimeLog_Reasons',
					fieldLabel: langs('Причины'),
					anchor: '99%',
					id: 'BedDowntimeLog_Reasons'
				},
			],
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'AVEW_ButtonSave',
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function () {
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'AVEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swBedDowntimeLogEditWindow.superclass.initComponent.apply(this, arguments);
	}
});