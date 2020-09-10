/**
 * swMorfoHistologicCorpseGiveawayWindow - форма регистрации выдачи тела умершего (АРМ патологоанатома)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Shekunov Dmitriy
 */
sw.Promed.swMorfoHistologicCorpseGiveawayWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'MorfoHistologicCorpseGiveawayWindow',
	codeRefresh: true,
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	maximizable: false,
	maximized: false,
	width: 600,
	height: 150,
	modal: true,
	formStatus: 'edit',
	CorpseReciept_Date: null,
	EvnMorfoHistologicProto_autopsyDate: null,
	doSave: function () {

		if (this.formStatus == 'save') {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
		var baseForm = form.getForm();

		if (!baseForm.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var record = baseForm.findField('MedStaffFact_id').getStore().getById(baseForm.findField('MedStaffFact_id').getValue());

		if (record) {
			baseForm.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
		}

		var awayDate = baseForm.findField('MorfoHistologicCorpse_giveawayDate').getValue();

		// ошибки
		var recieptDate = this.CorpseReciept_Date,
			autopsyDate = this.EvnMorfoHistologicProto_autopsyDate;

		if ((autopsyDate && awayDate < autopsyDate) || (recieptDate && awayDate < recieptDate)) {
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['data_vydachi_tela_ne_mozhet_byit_ranshe_chem_data_vskryitiya']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Сохранение..."});
		loadMask.show();

		baseForm.submit({
			failure: function (resultForm, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			// params: params,
			success: function (resultForm, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if (action.result) {
					if (action.result.MorfoHistologicCorpseGiveaway_id) {
						var MorfoHistologicCorpseGiveaway_id = action.result.MorfoHistologicCorpseGiveaway_id;

						baseForm.findField('MorfoHistologicCorpseGiveaway_id').setValue(MorfoHistologicCorpseGiveaway_id);

						var data = {};

						data.MorfoHistologicCorpseGiveawayData = {
							'EvnDirectionMorfoHistologic_id': baseForm.findField('EvnDirectionMorfoHistologic_id').getValue(),
							'MorfoHistologicCorpseGiveaway_id': baseForm.findField('MorfoHistologicCorpseGiveaway_id').getValue(),
							'MorfoHistologicCorpse_giveawayDate': baseForm.findField('MorfoHistologicCorpse_giveawayDate').getValue(),
							'MedPersonal_id': baseForm.findField('MedPersonal_id').getValue(),
							'Person_id': baseForm.findField('Person_id').getValue(),
						};

						this.callback(data);

						var patGrid = Ext.getCmp('WorkPlacePathoMorphologyGridPanel');
						if (patGrid && patGrid.ViewGridStore) {
							patGrid.ViewGridStore.reload();
						}
						this.hide();
					} else {
						if (action.result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},

	setPersonField: function (combo, personData) {
		combo.getStore().loadData([{
			Person_id: personData.Person_id,
			Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
		}]);
		combo.setValue(personData.Person_id);
		combo.collapse();
		combo.focus(true, 500);
		combo.fireEvent('change', combo, personData.Person_id);
		getWnd('swPersonSearchWindow').hide();
	},

	initComponent: function () {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'MorfoHistologicCorpseGiveawayForm',
			region: 'center',
			reader: new Ext.data.JsonReader({
				success: Ext.EmptyFn
			}, [
				{name: 'EvnDirectionMorfoHistologic_id'},
				{name: 'MorfoHistologicCorpse_giveawayDate'},
				{name: 'MedPersonal_id'},
				{name: 'Person_id'},
				{name: 'MorfoHistologicCorpseGiveaway_id'}
			]),
			url: '/?c=MorfoHistologicCorpseGiveaway&m=saveMorfoHistologicCorpseGiveaway',

			items: [{
				name: 'EvnDirectionMorfoHistologic_id',
				value: 0,
				xtype: 'hidden',
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MorfoHistologicCorpseGiveaway_id',
				value: 0,
				xtype: 'hidden'
			}, {
				border: false,
				layout: 'form',
				labelWidth: 120,
				labelAlign: 'right',
				items: [{
					allowBlank: false,
					fieldLabel: lang['data_vydachi_tela'],
					name: 'MorfoHistologicCorpse_giveawayDate',
					width: 100,
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					format: 'd.m.Y',
					maxValue: getGlobalOptions().date,
					tabIndex: TABINDEX_MHCG + 1,
					xtype: 'swdatefield'
				}, {
					allowBlank: false,
					fieldLabel: lang['telo_vydal'],
					hiddenNname: 'MedStaffFact_id',
					submitValue: false,
					id: 'MHCG_MedStaffFactCombo',
					listWidth: 450,
					tabIndex: TABINDEX_MHCG + 2,
					width: 400,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['telo_poluchil'],
					hiddenName: 'Person_id',
					submitValue: true,
					id: 'MHCG_PersonCombo',
					tabIndex: TABINDEX_MHCG + 3,
					width: 400,
					xtype: 'swpersoncombo',
					onTrigger1Click: function () {
						var combo = this;
						var ownerWindow = Ext.getCmp('MorfoHistologicCorpseGiveawayWindow');

						getWnd('swPersonSearchWindow').show({
							searchMode: 'older14notdead',
							onSelect: function (personData) {
								if (!personData.Document_Ser && !personData.Document_Num) {
									sw.swMsg.show(
										{
											buttons: Ext.Msg.YESNO,
											fn: function (buttonId) {
												if (buttonId == 'yes') {
													ownerWindow.setPersonField(combo, personData);
												}
											}.createDelegate(this),
											icon: Ext.Msg.QUESTION,
											msg: lang['u_poluchatelya_umershego_otsutstvuiyt_dannye_o_documente']
										});
								} else {
									ownerWindow.setPersonField(combo, personData);
								}
							},
							onClose: function () {
								combo.focus(true, 500)
							}
						});
					}
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var baseForm = this.FormPanel.getForm();
					if (this.action == 'view') {
						this.buttons[this.buttons.length - 1].focus(true);
					} else {
						baseForm.findField('Person_id').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_MHCG + 4,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this, -1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						this.buttons[0].focus();
					}.createDelegate(this),
					onTabAction: function () {
						if (this.action != 'view') {
							this.FormPanel.getForm().findField('MorfoHistologicCorpse_giveawayDate').focus(true);
						} else {
							this.buttons[1].focus(true);
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_MHCG + 5,
					text: BTN_FRMCANCEL
				}],
			items: [
				this.FormPanel
			],
			layout: 'border'
		});
		sw.Promed.swMorfoHistologicCorpseGiveawayWindow.superclass.initComponent.apply(this, arguments);

	},
	keys: [{
		alt: true,
		fn: function (inp, e) {
			var currentWindow = Ext.getCmp('MorfoHistologicCorpseGiveawayWindow');

			switch (e.getKey()) {
				case Ext.EventObject.C:
					currentWindow.doSave();
					break;

				case Ext.EventObject.J:
					currentWindow.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],

	show: function () {
		sw.Promed.swMorfoHistologicCorpseGiveawayWindow.superclass.show.apply(this, arguments);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		loadMask.show();

		this.restore();
		this.center();

		var baseForm = this.FormPanel.getForm();
		baseForm.reset();
		baseForm.findField('MedStaffFact_id').getStore().removeAll();

		var action = arguments[0].action;
		baseForm.setValues(arguments[0].formParams);
		this.CorpseReciept_Date = arguments[0].CorpseReciept_Date;
		this.EvnMorfoHistologicProto_autopsyDate = arguments[0].EvnMorfoHistologicProto_autopsyDate;

		if (action === 'view') {
			this.buttons[0].disable();
			this.buttons[0].hide();

			baseForm.findField('MorfoHistologicCorpse_giveawayDate').disable();
			baseForm.findField('MedStaffFact_id').disable();
			baseForm.findField('Person_id').disable();

		} else {
			this.buttons[0].enable();
			this.buttons[0].show();

			baseForm.findField('MorfoHistologicCorpse_giveawayDate').enable();
			baseForm.findField('MedStaffFact_id').enable();
			baseForm.findField('Person_id').enable();
		}

		switch (action) {
			case 'view':
			case 'edit':

				var morfoHistologicCorpseGiveaway_id = baseForm.findField('MorfoHistologicCorpseGiveaway_id').getValue();

				if (!morfoHistologicCorpseGiveaway_id) {
					loadMask.hide();
					this.hide();
					return false;
				}

				baseForm.load({
					params: {
						'MorfoHistologicCorpseGiveaway_id': morfoHistologicCorpseGiveaway_id
					},
					failure: function () {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
							this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					success: function () {
						if (action === 'edit') {
							this.setTitle(WND_PATHOMORPH_MHCGFEDIT);
						} else {
							this.setTitle(WND_PATHOMORPH_MHCGFVIEW);
						}
						var index;
						var record;
						var medPersonal_id = baseForm.findField('MedPersonal_id').getValue();
						var person_id = baseForm.findField('Person_id').getValue();

						baseForm.findField('MedStaffFact_id').getStore().load({
							callback: function () {
								index = baseForm.findField('MedStaffFact_id').getStore().findBy(function (rec) {
									if (rec.get('MedPersonal_id') === medPersonal_id) {
										return true;
									} else {
										return false;
									}
								});
								record = baseForm.findField('MedStaffFact_id').getStore().getAt(index);

								if (record) {
									baseForm.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
								}

							}.createDelegate(this),
							params: {
								LpuSection_id: getGlobalOptions().CurLpuSection_id
							}
						});

						baseForm.findField('Person_id').getStore().load({
							params: {
								Person_id: person_id
							},
							callback: function () {
								baseForm.findField('Person_id').setValue(person_id);
							}.createDelegate(this)
						});
						loadMask.hide();
					}.createDelegate(this),
					url: '/?c=MorfoHistologicCorpseGiveaway&m=loadMorfoHistologicCorpseGiveawayEditForm'
				});
				break;

			case 'add':
				this.setTitle(WND_PATHOMORPH_MHCGFADD);

				//загружаем врачей для выпадающего списка и устанавливаем время
				baseForm.findField('MorfoHistologicCorpse_giveawayDate').setValue(getGlobalOptions().date, 'd.m.Y');
				baseForm.findField('MorfoHistologicCorpse_giveawayDate').enable();

				baseForm.findField('MedStaffFact_id').getStore().load({
					callback: function () {
						baseForm.findField('MedStaffFact_id').enable();
						loadMask.hide();
					},
					params: {
						LpuSection_id: getGlobalOptions().CurLpuSection_id
					}
				});
				break;
		}
	}
});