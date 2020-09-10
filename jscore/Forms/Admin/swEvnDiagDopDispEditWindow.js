/**
 * swEvnDiagDopDispEditWindow - окно редактирования диагноза по диспансеризации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 */

/*NO PARSE JSON*/

sw.Promed.swEvnDiagDopDispEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnDiagDopDispEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (this.soputDiagsFirst && base_form.findField('DeseaseDispType_id').getValue() == 1 && base_form.findField('Diag_id').getValue().inlist(this.soputDiagsFirst)) {
			sw.swMsg.alert(lang['oshibka'], 'Указанный сопутствующий диагноз уже добавлен в разделе "Впервые выявленные заболевания" с другим характером заболевания.');
			this.formStatus = 'edit';
			return false;
		}

		if (this.EvnDiagDopDispGridStore) {
			// проверяем чтобы не было уже такого диагноза в store
			var index = this.EvnDiagDopDispGridStore.findBy(function(rec) {
				if (
					rec.get('Diag_id') == base_form.findField('Diag_id').getValue()
					&& rec.get('EvnDiagDopDisp_id') != base_form.findField('EvnDiagDopDisp_id').getValue()
				) {
					return true;
				}

				return false;
			});

			if (index > -1) {
				sw.swMsg.alert(lang['oshibka'], lang['ukazannyiy_diagnoz_uje_prisutstvuet_v_spiske'] );
				this.formStatus = 'edit';
				return false;
			}
		}
		// #181668 пока убрали контроль кроме Вологды
		// NGS: AN ADDITIONAL CHECK IS NOT NEEDED ANYMORE FOR VOLOGDA - #194032
		if(!(getRegionNick().inlist([/*'vologda'*/]) && win.object && win.object.inlist(['EvnPLDispDop13','EvnPLDispProf']))){
			options.ignoreCheckDiag = true;
		}
		//Проверка диагноза на наличие в EvnDiagDopDisp
		var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
		var GroupDiag_Code = Diag_Code.slice(0,3);

		if(!options.ignoreCheckDiag) {
			options.ignoreCheckDiag = win.lastArguments.formParams.parentFormName != 'EvnPLDispProf' && win.lastArguments.formParams.parentFormName != 'EvnPLDispDop13';
		}

		if (
			options.ignoreCheckDiag != true
			&& Diag_Code !='Z00.0'
		) {
			win.formStatus = 'edit';
			win.getLoadMask(langs("Подождите, идет проверка диагноза...")).show();
			
			Ext.Ajax.request({
				url: '/?c=EvnPLDispDop13&m=CheckDiag',
				params: {
					EvnPLDispDop13_id: base_form.findField('EvnPLDisp_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue()
				},
				failure: function(result_form, action) {
					win.getLoadMask().hide();
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								options.ignoreCheckDiag = true;
								win.doSave(options);
							}
							else {
								win.formStatus = 'edit';
							}
						},
						msg: langs('Ошибка при проверке на дублирование диагноза. Продолжить сохранение?'),
						title: langs('Подтверждение сохранения')
					});
				},
				success: function(response, action) {
					win.getLoadMask().hide();

					if (response.responseText != '') {
						var data = Ext.util.JSON.decode(response.responseText);
						if (data) {//иначе - проверка успешна, пересечений диагнозов нет
							var msg = '';
							
							if(data == base_form.findField('Diag_id').getValue()) {
								sw.swMsg.alert(langs('Ошибка'), langs('У пациента уже указан диагноз')+' <b>'+Diag_Code+'</b><br>'
									+langs('Проверьте правильность введенных данных.'),
									function() {
										win.formStatus = 'edit';
										base_form.findField('Diag_id').focus(true);
									}.createDelegate(this)
								);
							} else {
								sw.swMsg.show({
									buttons: {yes: langs('Продолжить'), no: langs('Отмена')},
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											options.ignoreCheckDiag = true;
											win.doSave(options);
										} else {
											win.formStatus = 'edit';
											base_form.findField('Diag_id').focus(true);
										}
									},
									msg: langs('У пациента уже указан диагноз группы')+' <b>'+GroupDiag_Code+'</b>',
									title: langs('Подтверждение сохранения'),
									width: 300
								});
							}
						} else {
							options.ignoreCheckDiag = true;
							win.doSave(options);
						}
					}
				}
			});

			win.formStatus = 'edit';
			return false;
		}

		if (this.formMode == 'remote') {
			win.getLoadMask(LOAD_WAIT_SAVE).show();

			base_form.submit({
				failure: function (result_form, action) {
					this.formStatus = 'edit';
					win.getLoadMask().hide();
				}.createDelegate(this),
				success: function (result_form, action) {
					win.getLoadMask().hide();

					if (action.result) {
						if (!Ext.isEmpty(action.result.EvnDiagDopDisp_id)) {
							base_form.findField('EvnDiagDopDisp_id').setValue(action.result.EvnDiagDopDisp_id);
						}
					}

					if (typeof this.callback == 'function') {
						this.callback();
					}
					this.formStatus = 'edit';
					this.hide();
				}.createDelegate(this)
			});
		} else {
			if (typeof this.callback == 'function') {
				if (base_form.findField('Record_Status').getValue() == 1) {
					base_form.findField('Record_Status').setValue(2);
				}
				var data = [{
					'EvnDiagDopDisp_id': base_form.findField('EvnDiagDopDisp_id').getValue(),
					'Diag_Code': base_form.findField('Diag_id').getFieldValue('Diag_Code'),
					'Diag_Name': base_form.findField('Diag_id').getFieldValue('Diag_Name'),
					'DeseaseDispType_Name': base_form.findField('DeseaseDispType_id').getFieldValue('DeseaseDispType_Name'),
					'Diag_id': base_form.findField('Diag_id').getValue(),
					'DeseaseDispType_id': base_form.findField('DeseaseDispType_id').getValue(),
					'Record_Status': base_form.findField('Record_Status').getValue()
				}];
				this.callback(data);
			}
			this.formStatus = 'edit';
			this.hide();
		}
	},
	show: function() {
		sw.Promed.swEvnDiagDopDispEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = win.FormPanel.getForm();

		base_form.reset();
		this.syncShadow();

		this.formMode = 'remote';
		if (arguments[0].formMode) {
			this.formMode = arguments[0].formMode;
		}

		this.EvnDiagDopDispGridStore = null;
		if (arguments[0].EvnDiagDopDispGridStore) {
			this.EvnDiagDopDispGridStore = arguments[0].EvnDiagDopDispGridStore;
		}

		this.action = 'view';
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		this.soputDiagsFirst = [];
		if (arguments[0].soputDiagsFirst)
		{
			this.soputDiagsFirst = arguments[0].soputDiagsFirst;
		}

		switch (this.action) {
			case 'add':
				win.getLoadMask().hide();
				win.enableEdit(true);
				win.setTitle(lang['soputstvuyuschiy_diagnoz_dobavlenie']);
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					win.enableEdit(true);
					win.setTitle(lang['soputstvuyuschiy_diagnoz_redaktirovanie']);
				} else {
					win.enableEdit(false);
					win.setTitle(lang['soputstvuyuschiy_diagnoz_prosmotr']);
				}

				if (this.formMode == 'remote') {
					win.getLoadMask(LOAD_WAIT).show();
					base_form.load({
						failure: function () {
							win.getLoadMask().hide();
							win.hide();
						},
						url: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispEditForm',
						params: {
							EvnDiagDopDisp_id: base_form.findField('EvnDiagDopDisp_id').getValue()
						},
						success: function () {
							win.getLoadMask().hide();

							var diag_combo = base_form.findField('Diag_id');
							if (!Ext.isEmpty(diag_combo.getValue())) {
								diag_combo.getStore().load({
									callback: function () {
										diag_combo.getStore().each(function (record) {
											if (record.get('Diag_id') == diag_combo.getValue()) {
												diag_combo.fireEvent('select', diag_combo, record, 0);
												diag_combo.onChange();
											}
										});
									},
									params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue()}
								});
							}
						}
					});
				} else {
					var diag_combo = base_form.findField('Diag_id');
					if (!Ext.isEmpty(diag_combo.getValue())) {
						diag_combo.getStore().load({
							callback: function () {
								diag_combo.getStore().each(function (record) {
									if (record.get('Diag_id') == diag_combo.getValue()) {
										diag_combo.fireEvent('select', diag_combo, record, 0);
										diag_combo.onChange();
									}
								});
							},
							params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue()}
						});
					}
				}

				break;
		}

		if (base_form.findField('Diag_id').disabled) {
			win.buttons[0].focus(true, '250');
		} else {
			base_form.findField('Diag_id').focus(true, '250');
		}
	},

	initComponent: function() {
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			url: '/?c=EvnDiagDopDisp&m=saveEvnDiagDopDisp',
			labelWidth: 160,
			labelAlign: 'right',

			items: [{
				name: 'EvnDiagDopDisp_id',
				xtype: 'hidden'
			}, {
				name: 'EvnPLDisp_id',
				xtype: 'hidden'
			}, {
				name: 'Record_Status',
				xtype: 'hidden'
			}, {
				name: 'EvnDiagDopDisp_pid',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'DiagSetClass_id',
				value: 3,
				xtype: 'hidden'
			}, {
				fieldLabel: lang['diagnoz'],
				hiddenName: 'Diag_id',
				allowBlank: false,
				baseFilterFn: function(rec) {
					var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
					if (Ext.isEmpty(Diag_Code)) return false;
					return (
						(Diag_Code.substr(0,1) < 'V' || Diag_Code.substr(0,1) > 'Y')
					);
				},
				onChange: function() {
					var diag_code = this.getFieldValue('Diag_Code');
					if (diag_code) {
						var base_form = win.FormPanel.getForm();
						if ( !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() == 'Z') {
							base_form.findField('DeseaseDispType_id').clearValue();
							base_form.findField('DeseaseDispType_id').disable();
							base_form.findField('DeseaseDispType_id').setAllowBlank(true);
						} else {
							if (win.action != 'view') {
								base_form.findField('DeseaseDispType_id').enable();
							}
							base_form.findField('DeseaseDispType_id').setAllowBlank(false);
						}
					}
				},
				listWidth: 600,
				tabIndex: TABINDEX_DDQEW + 11,
				anchor: '-10',
				
				xtype: 'swdiagcombo'
			}, {
				hiddenName: 'DeseaseDispType_id',
				comboSubject: 'DeseaseDispType',
				fieldLabel: lang['harakter_zabolevaniya'],
				anchor: '-10',
				tabIndex: TABINDEX_DDQEW + 12,
				xtype: 'swcommonsprcombo'
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'EvnDiagDopDisp_id'},
				{name: 'EvnDiagDopDisp_pid'},
				{name: 'DeseaseDispType_id'},
				{name: 'DiagSetClass_id'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'Diag_id'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					tabIndex: TABINDEX_DDQEW + 13,
					handler: function()
					
					{
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
					tabIndex: TABINDEX_DDQEW + 14,
					text: lang['otmenit']
				}]
		});

		sw.Promed.swEvnDiagDopDispEditWindow.superclass.initComponent.apply(this, arguments);
	}
});