/**
 * swMeasuresRehabEditWindow - окно мероприятий реабилитации или абилитации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			09.12.2016
 */
/*NO PARSE JSON*/

sw.Promed.swMeasuresRehabEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMeasuresRehabEditWindow',
	width: 600,
	autoHeight: true,
	modal: true,

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = base_form.getValues();

		if (base_form.findField('MeasuresRehabType_id').disabled) {
			params.MeasuresRehabType_id = base_form.findField('MeasuresRehabType_id').getValue();
		}
		if (base_form.findField('MeasuresRehabSubType_id').disabled) {
			params.MeasuresRehabSubType_id = base_form.findField('MeasuresRehabSubType_id').getValue();
		}

		if (Ext.isEmpty(params.MeasuresRehabResult_id)) {
			params.MeasuresRehabResult_id = 1;
		}

		if (!Ext.isEmpty(params.Org_id) && (params.Org_id > 0) == false) {
			params.MeasuresRehab_OrgName = params.Org_id;
			params.Org_id = null;
		}

		switch(this.type) {
			case 'usluga':
				var setDate = base_form.findField('EvnUsluga_id').getFieldValue('EvnUsluga_setDate');
				params.MeasuresRehab_setDate = Ext.util.Format.date(setDate, 'd.m.Y');
				//params.MeasuresRehab_Name = base_form.findField('EvnUsluga_id').getRawValue();
				params.Org_id = base_form.findField('EvnUsluga_id').getFieldValue('Org_id');
				break;
			case 'evn':
				var setDate = base_form.findField('Evn_id').getFieldValue('Evn_setDate');
				params.MeasuresRehab_setDate = Ext.util.Format.date(setDate, 'd.m.Y');
				params.MeasuresRehab_Name = base_form.findField('Evn_id').getRawValue();
				params.Org_id = base_form.findField('Evn_id').getFieldValue('Org_id');
				break;
			case 'drug':
				var setDate = base_form.findField('ReceptOtov_id').getFieldValue('EvnRecept_otpDate');
				params.MeasuresRehab_setDate = Ext.util.Format.date(setDate, 'd.m.Y');
				//params.MeasuresRehab_Name = base_form.findField('ReceptOtov_id').getRawValue();
				params.Org_id = base_form.findField('ReceptOtov_id').getFieldValue('Org_id');
				break;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		Ext.Ajax.request({
			params: params,
			url: '/?c=MeasuresRehab&m=saveMeasuresRehab',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.success) {
					base_form.findField('MeasuresRehab_id').setValue(response_obj.MeasuresRehab_id);
				}

				this.callback();
				this.hide();
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
			}
		});
	},

	genTitle: function() {
		var base_form = this.FormPanel.getForm();
		var title = '';
		if (Ext.isEmpty(this.type) || Ext.isEmpty(this.action)) {
			return '';
		}
		switch(this.type) {
			case 'usluga': title = 'Услуга'; break;
			case 'evn': title = 'Случай лечения'; break;
			case 'drug': title = 'Медикаменты'; break;
			case 'other': title = 'Прочие мероприятия'; break;
		}
		switch(this.action) {
			case 'add': title += lang['_dobavlenie']; break;
			case 'edit': title += lang['_redaktirovanie']; break;
			case 'view': title += lang['_prosmotr']; break;
		}
		return title;
	},

	filterMeasuresRehabType: function() {
		var wnd = this;
		var base_form = this.FormPanel.getForm();


		base_form.findField('MeasuresRehabType_id').lastQuery = '';
		base_form.findField('MeasuresRehabType_id').getStore().filterBy(function(rec) {
			var flag = false;
			switch(Number(rec.get('MeasuresRehabType_Code'))) {
				case 1: flag = wnd.needMedRehab; break;
				case 2: flag = wnd.needReconstructSurg; break;
				case 3: flag = wnd.needOrthotics; break;
			}
			return flag;
		});
	},

	setFormType: function(type) {
		var base_form = this.FormPanel.getForm();

		base_form.findField('MeasuresRehabType_id').enable();
		base_form.findField('MeasuresRehabSubType_id').enable();
		base_form.findField('MeasuresRehabSubType_id').hideContainer();
		base_form.findField('MeasuresRehabSubType_id').setAllowBlank(true);
		base_form.findField('MeasuresRehab_setDate').hideContainer();
		base_form.findField('MeasuresRehab_setDate').setAllowBlank(true);
		base_form.findField('Org_id').hideContainer();
		base_form.findField('Org_id').setAllowBlank(true);
		base_form.findField('MeasuresRehab_Name').hideContainer();
		base_form.findField('MeasuresRehab_Name').setAllowBlank(true);
		base_form.findField('MeasuresRehabResult_id').hideContainer();
		base_form.findField('MeasuresRehabResult_id').setAllowBlank(true);
		base_form.findField('EvnUsluga_id').hideContainer();
		base_form.findField('EvnUsluga_id').setAllowBlank(true);
		base_form.findField('Evn_id').hideContainer();
		base_form.findField('Evn_id').setAllowBlank(true);
		base_form.findField('ReceptOtov_id').hideContainer();
		base_form.findField('ReceptOtov_id').setAllowBlank(true);

		base_form.findField('MeasuresRehabSubType_id').getStore().clearFilter();

		switch(type) {
			case 'other':
				base_form.findField('MeasuresRehab_setDate').showContainer();
				//base_form.findField('MeasuresRehab_setDate').setAllowBlank(false);	//зависит от MeasuresRehabResult_id
				base_form.findField('Org_id').showContainer();
				base_form.findField('Org_id').setAllowBlank(false);
				base_form.findField('MeasuresRehab_Name').showContainer();
				base_form.findField('MeasuresRehab_Name').setAllowBlank(false);
				base_form.findField('MeasuresRehabResult_id').showContainer();
				base_form.findField('MeasuresRehabResult_id').setAllowBlank(false);

				if (!Ext.isEmpty(this.begDate)) {
					base_form.findField('MeasuresRehab_setDate').setMinValue(this.begDate);
				}
				if (!Ext.isEmpty(this.endDate)) {
					base_form.findField('MeasuresRehab_setDate').setMaxValue(this.endDate);
				}

				var Org_id = base_form.findField('Org_id').getValue();
				if (Org_id > 0) {
					base_form.findField('Org_id').getStore().load({
						params: {Org_id: Org_id},
						callback: function() {
							base_form.findField('Org_id').setValue(Org_id);
						}
					});
				}
				break;
			case 'usluga':
				base_form.findField('MeasuresRehabSubType_id').lastQuery = '';
				base_form.findField('MeasuresRehabSubType_id').getStore().filterBy(function(rec) {
					return (rec.get('MeasuresRehabSubType_id').inlist([1,3,4]));
				});

				base_form.findField('EvnUsluga_id').showContainer();
				base_form.findField('EvnUsluga_id').setAllowBlank(false);
				base_form.findField('EvnUsluga_id').getStore().load({
					params: {IPRARegistry_id: base_form.findField('IPRARegistry_id').getValue()},
					callback: function() {
						base_form.findField('EvnUsluga_id').setValue(base_form.findField('EvnUsluga_id').getValue());
					}
				});
				break;
			case 'evn':
				base_form.findField('MeasuresRehabSubType_id').lastQuery = '';
				base_form.findField('MeasuresRehabSubType_id').getStore().filterBy(function(rec) {
					return (rec.get('MeasuresRehabSubType_id').inlist([1,3,4]));
				});

				base_form.findField('Evn_id').showContainer();
				base_form.findField('Evn_id').setAllowBlank(false);
				base_form.findField('Evn_id').getStore().load({
					params: {IPRARegistry_id: base_form.findField('IPRARegistry_id').getValue()},
					callback: function() {
						base_form.findField('Evn_id').setValue(base_form.findField('Evn_id').getValue());
						base_form.findField('Evn_id').refreshDisplay();
					}
				});
				break;
			case 'drug':
				base_form.findField('MeasuresRehabType_id').setFieldValue('MeasuresRehabType_Code', 1);
				base_form.findField('MeasuresRehabType_id').disable();
				base_form.findField('MeasuresRehabSubType_id').setFieldValue('MeasuresRehabSubType_Code', 2);
				base_form.findField('MeasuresRehabSubType_id').disable();

				base_form.findField('ReceptOtov_id').showContainer();
				base_form.findField('ReceptOtov_id').setAllowBlank(false);
				base_form.findField('ReceptOtov_id').getStore().load({
					params: {IPRARegistry_id: base_form.findField('IPRARegistry_id').getValue()},
					callback: function() {
						base_form.findField('ReceptOtov_id').setValue(base_form.findField('ReceptOtov_id').getValue());
						base_form.findField('ReceptOtov_id').refreshDisplay();
					}
				});
				break;
		}

		if (base_form.findField('MeasuresRehabType_id').getFieldValue('MeasuresRehabType_Code') == 1) {
			base_form.findField('MeasuresRehabSubType_id').showContainer();
			base_form.findField('MeasuresRehabSubType_id').setAllowBlank(false);
		}

		this.syncShadow();
	},

	show: function() {
		sw.Promed.swMeasuresRehabEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		this.action = 'view';
		this.callback = Ext.emptyFn;
		this.type = null;
		this.needMedRehab = false;
		this.needReconstructSurg = false;
		this.needOrthotics = false;
		this.begDate = null;
		this.endDate = null;

		base_form.reset();
		base_form.setValues(arguments[0].formParams);

		this.type = arguments[0].type;

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0] && arguments[0].needMedRehab) {
			this.needMedRehab = arguments[0].needMedRehab;
		}
		if (arguments[0] && arguments[0].needReconstructSurg) {
			this.needReconstructSurg = arguments[0].needReconstructSurg;
		}
		if (arguments[0] && arguments[0].needOrthotics) {
			this.needOrthotics = arguments[0].needOrthotics;
		}
		if (arguments[0] && arguments[0].begDate) {
			this.begDate = arguments[0].begDate;
		}
		if (arguments[0] && arguments[0].endDate) {
			this.endDate = arguments[0].endDate;
		}

		var IPRARegistry_id = base_form.findField('IPRARegistry_id').getValue();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				loadMask.hide();
				this.setTitle(this.genTitle());
				this.filterMeasuresRehabType();
				this.setFormType(this.type);
			break;

			case 'edit':
			case 'view':
				this.enableEdit(this.action == 'edit');

				base_form.load({
					url: '/?c=MeasuresRehab&m=loadMeasuresRehabForm',
					params: {MeasuresRehab_id: base_form.findField('MeasuresRehab_id').getValue()},
					success: function() {
						loadMask.hide();

						this.type = base_form.findField('type').getValue();

						this.setTitle(this.genTitle());
						this.setFormType(this.type);

					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}.createDelegate(this)
				});
			break;
		}

	},

	initComponent: function() {
		var wnd = this;

		var EvnUslugaCombo = new sw.Promed.SwBaseLocalCombo({
			fieldLabel: 'Услуга',
			displayField: 'UslugaComplex_Name',
			valueField: 'EvnUsluga_id',
			hiddenName: 'EvnUsluga_id',
			width: 400,
			tpl: new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px; ">Дата</td>',
				'<td style="padding: 2px; ">Код</td>',
				'<td style="padding: 2px; ">Наименование</td>',
				'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
				'<td style="padding: 2px;">{[Ext.util.Format.date(values.EvnUsluga_setDate, "d.m.Y")]}&nbsp;</td>',
				'<td style="padding: 2px;">{UslugaComplex_Code}&nbsp;</td>',
				'<td style="padding: 2px;">{UslugaComplex_Name}&nbsp;</td>',
				'</tr></tpl>',
				'</table>'
			),
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id:'EvnUsluga_id'
				}, [
					{name: 'EvnUsluga_id', type: 'int'},
					{name: 'EvnUsluga_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'UslugaComplex_id', type: 'int'},
					{name: 'UslugaComplex_Code', type: 'string'},
					{name: 'UslugaComplex_Name', type: 'string'},
					{name: 'Org_id', type: 'int'}
				]),
				url:'/?c=MeasuresRehab&m=loadEvnUslugaList'
			})
		});

		var EvnCombo = new sw.Promed.SwBaseLocalCombo({
			fieldLabel: 'Случай лечения',
			displayField: 'Evn_NumCard',
			valueField: 'Evn_id',
			hiddenName: 'Evn_id',
			width: 400,
			tpl: new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px; ">Случай</td>',
				'<td style="padding: 2px; ">№</td>',
				'<td style="padding: 2px; ">Начало</td>',
				'<td style="padding: 2px; ">Закрыт</td>',
				'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
				'<td style="padding: 2px;">{EvnClass_Nick}&nbsp;</td>',
				'<td style="padding: 2px;">{Evn_NumCard}&nbsp;</td>',
				'<td style="padding: 2px;">{[Ext.util.Format.date(values.Evn_setDate, "d.m.Y")]}&nbsp;</td>',
				'<td style="padding: 2px;">{[Ext.util.Format.date(values.Evn_disDate, "d.m.Y")]}&nbsp;</td>',
				'</tr></tpl>',
				'</table>'
			),
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id:'Evn_id'
				}, [
					{name: 'Evn_id', type: 'int'},
					{name: 'EvnClass_SysNick', type: 'string'},
					{name: 'EvnClass_Nick', type: 'string'},
					{name: 'Evn_NumCard', type: 'string'},
					{name: 'Evn_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Evn_disDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Org_id', type: 'int'}
				]),
				url:'/?c=MeasuresRehab&m=loadEvnList'
			}),
			refreshDisplay: function() {
				var combo = this;
				var record = combo.getStore().getById(combo.getValue());

				if (record && !Ext.isEmpty(record.get('Evn_id'))) {
					var tpl = new Ext.Template('{EvnClass_Nick} №{Evn_NumCard}');
					combo.setRawValue(tpl.apply(record.data));
				}
			}
		});

		var ReceptCombo = new sw.Promed.SwBaseLocalCombo({
			fieldLabel: 'Медикаменты',
			displayField: 'Drug_Name',
			valueField: 'ReceptOtov_id',
			hiddenName: 'ReceptOtov_id',
			width: 400,
			tpl: new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px; ">Рецепт №</td>',
				'<td style="padding: 2px; ">Медикамент</td>',
				'<td style="padding: 2px; ">Отпущен</td>',
				'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
				'<td style="padding: 2px;">{EvnRecept_Num} {EvnRecept_Ser}&nbsp;</td>',
				'<td style="padding: 2px;">{Drug_Name}&nbsp;</td>',
				'<td style="padding: 2px;">{[Ext.util.Format.date(values.EvnRecept_otpDate, "d.m.Y")]}&nbsp;</td>',
				'</tr></tpl>',
				'</table>'
			),
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id:'ReceptOtov_id'
				}, [
					{name: 'ReceptOtov_id', type: 'int'},
					{name: 'EvnRecept_Ser', type: 'string'},
					{name: 'EvnRecept_Num', type: 'string'},
					{name: 'Drug_id', type: 'int'},
					{name: 'Drug_Name', type: 'string'},
					{name: 'EvnRecept_otpDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Org_id', type: 'int'}
				]),
				url:'/?c=MeasuresRehab&m=loadReceptOtovList'
			}),
			refreshDisplay: function() {
				var combo = this;
				var record = combo.getStore().getById(combo.getValue());

				if (record && !Ext.isEmpty(record.get('ReceptOtov_id'))) {
					var tpl = new Ext.Template('Рецепт №{EvnRecept_Num} {EvnRecept_Ser}. {Drug_Name}');
					combo.setRawValue(tpl.apply(record.data));
				}
			}
		});

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'MREW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			url: '/?c=MeasuresRehab&m=saveMeasuresRehab',
			labelWidth: 130,
			items: [
				{
					xtype: 'hidden',
					name: 'MeasuresRehab_id'
				}, {
					xtype: 'hidden',
					name: 'IPRARegistry_id'
				}, {
					xtype: 'hidden',
					name: 'type'
				}, {
					allowBlank: false,
					xtype: 'swcommonsprcombo',
					comboSubject: 'MeasuresRehabType',
					hiddenName: 'MeasuresRehabType_id',
					fieldLabel: 'Тип мероприятия',
					listeners: {
						'select': function(combo, record, index) {
							var base_form = this.FormPanel.getForm();

							if (record.get('MeasuresRehabType_Code') == 1) {
								base_form.findField('MeasuresRehabSubType_id').showContainer();
								base_form.findField('MeasuresRehabSubType_id').setAllowBlank(false);
							} else {
								base_form.findField('MeasuresRehabSubType_id').hideContainer();
								base_form.findField('MeasuresRehabSubType_id').setAllowBlank(true);
								base_form.findField('MeasuresRehabSubType_id').setValue(null);
							}
							this.syncShadow();
						}.createDelegate(this)
					},
					width: 400
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'MeasuresRehabSubType',
					hiddenName: 'MeasuresRehabSubType_id',
					fieldLabel: 'Подтип мероприятия',
					width: 400
				}, {
					xtype: 'swdatefield',
					name: 'MeasuresRehab_setDate',
					fieldLabel: 'Дата',
					width: 140
				}, {
					xtype: 'sworgcomboex',
					hiddenName: 'Org_id',
					fieldLabel: 'Организация',
					forceSelection: false,
					width: 400
				}, {
					allowBlank: false,
					xtype: 'textfield',
					maxLength: 128,
					name: 'MeasuresRehab_Name',
					fieldLabel: 'Наименование',
					width: 400
				}, {
					allowBlank: false,
					xtype: 'swcommonsprcombo',
					comboSubject: 'MeasuresRehabResult',
					hiddenName: 'MeasuresRehabResult_id',
					fieldLabel: 'Результат',
					listeners: {
						'select': function(combo, record, index) {
							var base_form = this.FormPanel.getForm();

							if (record.get('MeasuresRehabResult_Code') == 1) {
								base_form.findField('MeasuresRehab_setDate').setAllowBlank(false);
							} else {
								base_form.findField('MeasuresRehab_setDate').setAllowBlank(true);
							}
						}.createDelegate(this)
					},
					width: 400
				},
				EvnUslugaCombo,
				EvnCombo,
				ReceptCombo
			],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'MeasuresRehab_id'},
				{name: 'IPRARegistry_id'},
				{name: 'type'},
				{name: 'MeasuresRehabType_id'},
				{name: 'MeasuresRehabSubType_id'},
				{name: 'MeasuresRehab_setDate'},
				{name: 'Org_id'},
				{name: 'MeasuresRehab_Name'},
				{name: 'MeasuresRehabResult_id'},
				{name: 'EvnUsluga_id'},
				{name: 'Evn_id'},
				{name: 'ReceptOtov_id'},
			])
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'MREW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swMeasuresRehabEditWindow.superclass.initComponent.apply(this, arguments);

		EvnCombo.addListener('select', function(combo, record, index) {
			combo.refreshDisplay();
		});
		ReceptCombo.addListener('select', function(combo, record, index) {
			combo.refreshDisplay();
		});
	}
});