/**
 * swEvnPLFinishWindow - Форма зарвешения случая АПЛ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EvnPL.swEvnPLFinishWindow', {
	/* свойства */
	alias: 'widget.swEvnPLFinishWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	maxHeight: 700,
	swMaximized: true,
	scrollable: 'y',
	modal: true,
	layout: 'border',
	refId: 'swEvnPLFinishWindow',
	resizable: false,
	title: 'Завершение случая лечения',
	width: 800,
	//autoHeight: true,
	show: function (data) {
		var win = this;

		if (!data) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		if (data.callback) {
			win.callback = data.callback;
		} else {
			win.callback = Ext6.emptyFn;
		}

		win.EvnPL_id = data.EvnPL_id;
		win.Person_id = data.Person_id;
		win.PersonEvn_id = data.PersonEvn_id;
		win.Server_id = data.Server_id;
		win.lastEvnVizitPLDate = data.lastEvnVizitPLDate;
		win.edit = data.edit ? data.edit : false;

		win.callParent(arguments);

		var base_form = win.formPanel.getForm();
		let textOnSaveButton = 'Сохранить';

		if (!win.edit) {
			textOnSaveButton = 'Завершить случай лечения';
		}

		win.queryById('EvnPLFinishButton').setText(textOnSaveButton);

		base_form.findField('ResultDeseaseType_id').setContainerVisible(getRegionNick().inlist(['adygeya', 'vologda', 'buryatiya', 'ekb', 'kaluga', 'kareliya', 'krasnoyarsk', 'krym', 'penza', 'pskov', 'yakutiya', 'yaroslavl']));
		base_form.findField('ResultDeseaseType_id').setAllowBlank(!getRegionNick().inlist(['adygeya', 'vologda', 'buryatiya', 'kaluga', 'kareliya', 'krasnoyarsk', 'krym', 'ekb', 'penza', 'pskov', 'yakutiya', 'yaroslavl']));

		if ( getRegionNick().inlist(['krasnoyarsk', 'adygeya', 'yakutiya', 'yaroslavl'])) {
			base_form.findField('ResultDeseaseType_id').getStore().filterBy(function(rec) {
				return (!Ext6.isEmpty(rec.get('ResultDeseaseType_Code')) && rec.get('ResultDeseaseType_Code').toString().substr(0, 1) == '3');
			});
		}

		win.filterLpuSectionOidCombo();

		base_form.findField('Lpu_oid').disable();
		base_form.findField('LpuSection_oid').disable();

		var xdate = new Date(2016, 10, 1); // Поле видимо (если дата посещения 01-11-2016 или позже)

		if ( !Ext6.isEmpty(win.lastEvnVizitPLDate) ) {
			base_form.findField('ResultClass_id').getStore().filterBy(function (rec) {
				return (
					(Ext6.isEmpty(rec.get('ResultClass_begDT')) || rec.get('ResultClass_begDT') <= win.lastEvnVizitPLDate)
					&& (Ext6.isEmpty(rec.get('ResultClass_endDT')) || rec.get('ResultClass_endDT') >= win.lastEvnVizitPLDate)
					&& (!rec.get('ResultClass_Code') || !rec.get('ResultClass_Code').inlist(['6','7']) || getRegionNick() != 'perm' || win.lastEvnVizitPLDate < xdate)
				);
			});
		}

		if ( (!Ext.isEmpty(win.lastEvnVizitPLDate) && win.lastEvnVizitPLDate < xdate) && getRegionNick() == 'kareliya' ) {
			base_form.findField('EvnPL_IsFirstDisable').setContainerVisible(true);
			base_form.findField('PrivilegeType_id').setContainerVisible(false);
			base_form.findField('PrivilegeType_id').clearValue();
		} else {
			base_form.findField('PrivilegeType_id').setContainerVisible(!getRegionNick() == 'ufa');
			base_form.findField('EvnPL_IsFirstDisable').setContainerVisible(false);
			base_form.findField('EvnPL_IsFirstDisable').clearValue();
		}
		if( getRegionNick() == 'krym' ) {
			base_form.findField('PrivilegeType_id').getStore().filterBy(function (rec) {
				return (rec.get('PrivilegeType_Code').inlist([81,82,83,84]) && rec.get('ReceptFinance_id') == 1)
			});
		} else {
			base_form.findField('PrivilegeType_id').getStore().filterBy(function (rec) {
				return (rec.get('PrivilegeType_Code').inlist([81,82,83,84]));
			});
		}
		
	},
	onSprLoad: function(args) {
		var me = this;
		var base_form = me.formPanel.getForm();
		base_form.reset();

		me.mask('Загрузка данных...');
		base_form.load({
			params: {
				EvnPL_id: me.EvnPL_id
			},
			success: function (form, action) {
				// good
				me.unmask();

				// значения по умолчанию
				base_form.findField('EvnPL_IsFinish').setValue(2); // закончен
				if (Ext6.isEmpty(base_form.findField('ResultClass_id').getValue())) {
					base_form.findField('ResultClass_id').setFieldValue('ResultClass_Code', 1); // выздоровление
					base_form.findField('EvnPL_UKL').setValue(1); // уровень качества лечения
					me.calcFedLeaveType();
					me.calcFedResultDeseaseType();
				}

				me.formPanel.getForm().isValid(); // чтобы подсветить зеленым обязательные поля
			},
			failure: function (form, action) {
				// not good
			}
		});
	},
	filterLpuSectionOidCombo: function () {
		var me = this;
		var base_form = me.formPanel.getForm(),
			LpuSectionOidCombo = base_form.findField('LpuSection_oid'),
			LpuSection_oid = base_form.findField('LpuSection_oid').getValue();

		if (!LpuSectionOidCombo.isVisible()) {
			return false;
		}

		LpuSectionOidCombo.getStore().clearFilter();
		LpuSectionOidCombo.lastQuery = '';

		var setComboValue = function (combo, id) {
			if (Ext6.isEmpty(id)) {
				return false;
			}

			var index = combo.getStore().findBy(function (rec) {
				return (rec.get('LpuSection_id') == id);
			});

			if (index == -1 && combo.isVisible()) {
				combo.clearValue();
			}
			else {
				combo.setValue(id);
			}

			return true;
		}

		setLpuSectionGlobalStoreFilter({
			onDate: getGlobalOptions().date
		});
		LpuSectionOidCombo.getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
		setComboValue(LpuSectionOidCombo, LpuSection_oid);
	},
	save: function(options) {
		options = options || {};

		var me = this;
		var base_form = me.formPanel.getForm();

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var params = {};
		if (base_form.findField('EvnPL_IsFinish').disabled) {
			params.EvnPL_IsFinish = base_form.findField('EvnPL_IsFinish').getValue();
		}

		if (options.params) {
			for (var param in options.params) {
				params[param] = options.params[param];
			}
		} else {
			options.params = {};
		}

		me.mask('Сохранение...');
		base_form.submit({
			url: '/?c=EvnPL&m=saveEvnPLFinishForm',
			params: params,
			success: function(result_form, action) {
				me.unmask();
				me.callback();
				me.hide();
			},
			failure: function(result_form, action) {
				me.unmask();
				if (action.result.Alert_Msg) {
					var msg = action.result.Alert_Msg;

					if (action.result.Error_Code == 212) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function(buttonId, text, obj) {
								getWnd('swMorbusOnkoEditWindow').show({
									action: 'edit',
									MorbusOnko_pid: action.result.EvnVizitPL_id,
									EvnVizitPL_id: action.result.EvnVizitPL_id,
									Person_id: me.Person_id,
									PersonEvn_id: me.PersonEvn_id,
									Server_id: me.Server_id
								});
							},
							msg: msg,
							icon: Ext6.Msg.WARNING,
							title: ERR_WND_TIT

						});
					} else {
						Ext6.Msg.show({
							buttons: Ext6.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if (buttonId == 'yes') {
									options.params[action.result.ignoreParam] = 1;
									if (action.result.ignoreParam == 'ignoreDiagDispCheck') {
										var formParams = new Object();
										var params_disp = new Object();

										formParams.Person_id = me.Person_id;
										formParams.Server_id = me.Server_id;
										formParams.PersonDisp_begDate = getGlobalOptions().date;
										formParams.PersonDisp_DiagDate = getGlobalOptions().date;
										formParams.Diag_id = base_form.findField('Diag_lid').getValue();

										params_disp.action = 'add';
										params_disp.callback = Ext.emptyFn;
										params_disp.formParams = formParams;
										params_disp.onHide = Ext.emptyFn;

										getWnd('swPersonDispEditWindow').show(params_disp);
									}

									me.save(options);
								}
								else {
									if (action.result.ignoreParam == 'ignoreDiagDispCheck') {
										options.params[action.result.ignoreParam] = 1;
										me.save(options);
									}
								}
							},
							icon: Ext6.MessageBox.QUESTION,
							msg: msg,
							title: langs('Продолжить сохранение?')
						});
					}
				}
			}
		});
	},
	calcFedLeaveType: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		if (getRegionNick() == 'khak') base_form.findField('LeaveType_fedid').clearValue();
		sw.Promed.EvnPL.calcFedLeaveType({
			is2016: true,
			disableToogleContainer: false,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			LeaveType_fedid: base_form.findField('ResultClass_id').getFieldValue('LeaveType_fedid'),
			ResultClass_id: base_form.findField('ResultClass_id').getValue(),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code'),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			DirectClass_Code: base_form.findField('DirectClass_id').getFieldValue('DirectClass_Code'),
			IsFinish: base_form.findField('EvnPL_IsFinish').getValue(),
			fieldFedLeaveType: base_form.findField('LeaveType_fedid')
		});
	},
	calcFedResultDeseaseType: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		if (getRegionNick() == 'khak') base_form.findField('ResultDeseaseType_fedid').clearValue();
		sw.Promed.EvnPL.calcFedResultDeseaseType({
			is2016: true,
			disableToogleContainer: false,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code') || null,
			IsFinish: base_form.findField('EvnPL_IsFinish').getValue(),
			fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
		});
	},
	initComponent: function() {
		var win = this;

		this.formPanel = Ext6.create('Ext6.form.Panel', {
			autoHeight: true,
			border: false,
			url: '/?c=EvnPL&m=loadEvnPLFinishForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'EvnPL_id'},
						{name: 'EvnPL_IsFinish'},
						{name: 'EvnPL_IsSurveyRefuse'},
						{name: 'ResultClass_id'},
						{name: 'InterruptLeaveType_id'},
						{name: 'ResultDeseaseType_id'},
						{name: 'EvnPL_UKL'},
						{name: 'EvnPL_IsFirstDisable'},
						{name: 'PrivilegeType_id'},
						{name: 'DirectType_id'},
						{name: 'DirectClass_id'},
						{name: 'LpuSection_oid'},
						{name: 'Lpu_oid'},
						{name: 'Diag_lid'},
						{name: 'Diag_concid'},
						{name: 'PrehospTrauma_id'},
						{name: 'EvnPL_IsUnlaw'},
						{name: 'EvnPL_IsUnport'},
						{name: 'LeaveType_fedid'},
						{name: 'ResultDeseaseType_fedid'}
					]
				})
			}),
			defaults: {
				anchor: '90%',
				labelWidth: 200
			},
			userCls: 'case-completion',
			items: [{
				xtype: 'hidden',
				name: 'EvnPL_id'
			}, {
				xtype: 'commonSprCombo',
				allowBlank: false,
				comboSubject: 'YesNo',
				value: 2,
				maxWidth: 300,
				disabled: true,
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				fieldLabel: 'Случай закончен',
				name: 'EvnPL_IsFinish'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				maxWidth: 300,
				fieldLabel: 'Отказ от прохождения медицинских обследований',
				name: 'EvnPL_IsSurveyRefuse',
				hidden: getRegionNick() == 'kz'
			}, {
				xtype: 'commonSprCombo',
				typeCode: 'int',
				allowBlank: false,
				comboSubject: 'ResultClass',
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				moreFields: [
					{name: 'LeaveType_fedid', type: 'int'}
				],
				fieldLabel: 'Результат лечения',
				moreFields: [
					{name: 'ResultClass_begDT', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'ResultClass_endDT', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'LeaveType_fedid', type: 'int'}
				],
				name: 'ResultClass_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'InterruptLeaveType',
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				fieldLabel: 'Случай прерван',
				name: 'InterruptLeaveType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'ResultDeseaseType',
				moreFields: [
					{ name: 'ResultDeseaseType_fedid', type: 'int' }
				],
				fieldLabel: langs('Исход'),
				name: 'ResultDeseaseType_id'
			}, {
				xtype: 'numberfield',
				allowBlank: false,
				allowDecimals: true,
				allowNegative: false,
				minValue: 0,
				maxValue: 1,
				maxWidth: 300,
				fieldLabel: 'УКЛ',
				name: 'EvnPL_UKL'
			}, {
				hidden: getRegionNick() != 'kareliya' || getRegionNick() == 'ufa',
				fieldLabel: 'Впервые выявленная инвалидность',
				name: 'EvnPL_IsFirstDisable',
				xtype: 'commonSprCombo',
				maxWidth: 300,
				comboSubject: 'YesNo'
			}, {
				hidden: !getRegionNick().inlist(['kareliya', 'astra', 'buryatiya','krym']),
				fieldLabel: 'Впервые выявленная инвалидность',
				name: 'PrivilegeType_id',
				xtype: 'commonSprCombo',
				comboSubject: 'PrivilegeType',
				moreFields: [
					{ name: 'ReceptFinance_id', type: 'int' }
				]
			}, {
				xtype: 'commonSprCombo',
				typeCode: 'int',
				comboSubject: 'DirectType',
				listeners: {
					'select': function() {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();
					}
				},
				fieldLabel: 'Направление',
				name: 'DirectType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'DirectClass',
				listeners: {
					'select': function(combo, record, idx) {
						win.calcFedLeaveType();
						win.calcFedResultDeseaseType();

						var base_form = win.formPanel.getForm();

						var lpu_combo = base_form.findField('Lpu_oid');
						var lpu_section_combo = base_form.findField('LpuSection_oid');

						lpu_combo.clearValue();
						lpu_section_combo.clearValue();

						if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
							if ( record.get('DirectClass_Code') == 1 ) {
								lpu_section_combo.enable();
								lpu_combo.disable();
							}
							else if ( record.get('DirectClass_Code') == 2 ) {
								lpu_section_combo.disable();
								lpu_combo.enable();
							}
						}
						else {
							lpu_section_combo.disable();
							lpu_combo.disable();
						}
					}
				},
				fieldLabel: 'Куда направлен',
				name: 'DirectClass_id',
			}, {
				fieldLabel: 'Отделение',
				hiddenName: 'LpuSection_oid',
				itemId: 'LpuSectionOidCombo',
				queryMode: 'local',
				name: 'LpuSection_oid',
				xtype: 'SwLpuSectionGlobalCombo'
			}, {
				fieldLabel: 'МО',
				hiddenName: 'Lpu_oid',
				name: 'Lpu_oid',
				xtype: 'swOrgCombo',
				onlyFromDictionary: true,
				orgType: 'lpu',
				editable: false,
				triggers: {
					clear: {
						hidden: false
					}
				}
			}, {
				xtype: 'swDiagCombo',
				cls: 'trigger-outside',
				fieldLabel: 'Закл. диагноз',
				disabled: getRegionNick().inlist(['ufa']),
				listeners: {
					'change': function() {
						var base_form = win.formPanel.getForm();
						var Diag_lid_Code = base_form.findField('Diag_lid').getFieldValue('Diag_Code');
						if (!Ext6.isEmpty(Diag_lid_Code) && Diag_lid_Code.toString().substr(0, 1).inlist(['S', 'T'])) {
							base_form.findField('Diag_concid').setContainerVisible(true);
							base_form.findField('Diag_concid').setAllowBlank(false);
						}
						else {
							base_form.findField('Diag_concid').clearValue();
							base_form.findField('Diag_concid').setContainerVisible(false);
							base_form.findField('Diag_concid').setAllowBlank(true);
						}
					}
				},
				triggers: {
					search: {
						extraCls: 'search-icon-out'
					}
				},
				name: 'Diag_lid'
			}, {
				xtype: 'swDiagCombo',
				cls: 'trigger-outside',
				fieldLabel: 'Закл. внешняя причина',
				name: 'Diag_concid',
				triggers: {
					search: {
						// cls: 'x6-form-search-trigger',
						extraCls: 'search-icon-out'
					}
				},
				baseFilterFn: function(rec){
					if (typeof rec.get == 'function') {
						return (rec.get('Diag_Code').substr(0,1).inlist(['V', 'W', 'X', 'Y']));
					} else if (rec.attributes && rec.attributes.Diag_Code) {
						return (rec.attributes.Diag_Code.substr(0,1).inlist(['V', 'W', 'X', 'Y']));
					} else {
						return true;
					}
				}
			}, {
				xtype: 'commonSprCombo',
				typeCode: 'int',
				comboSubject: 'PrehospTrauma',
				fieldLabel: 'Вид травмы (внеш. возд)',
				name: 'PrehospTrauma_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				maxWidth: 300,
				fieldLabel: 'Противоправная',
				name: 'EvnPL_IsUnlaw'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				maxWidth: 300,
				fieldLabel: 'Нетранспортабельность',
				name: 'EvnPL_IsUnport'
			}, {
				xtype: 'commonSprCombo',
				allowBlank: false,
				suffix: 'Fed',
				comboSubject: 'LeaveType',
				USLOV: 3,
				moreFields: [
					{ name: 'LeaveType_USLOV', mapping: 'LeaveType_USLOV' }
				],
				listeners: {
					'change': function() {
						var base_form = win.formPanel.getForm();
						sw.Promed.EvnPL.filterFedResultDeseaseType({
							fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
							fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
						})
					}
				},
				fieldLabel: 'Фед. результат',
				name: 'LeaveType_fedid'
			}, {
				xtype: 'commonSprCombo',
				allowBlank: false,
				suffix: 'Fed',
				comboSubject: 'ResultDeseaseType',
				listeners: {
					'change': function() {
						var base_form = win.formPanel.getForm();
						sw.Promed.EvnPL.filterFedLeaveType({
							fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
							fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
						});
					}
				},
				fieldLabel: 'Фед. исход',
				name: 'ResultDeseaseType_fedid'
			}]
		});

		Ext6.apply(win, {
			layout: 'form',
			items: [
				win.formPanel
			],
			buttons: {
				style:{
					left: '0px',
					right: '0px'
				},
				items: ['->',
					{
						handler: function () {
							win.hide();
						},
						text: 'Отмена'
					}, {
						handler: function () {
							win.save();
						},
						id: 'EvnPLFinishButton',
						cls: 'flat-button-primary',
						text: 'Завершить случай лечения'
					}
				]
			}
		});

		this.callParent(arguments);
	}
});