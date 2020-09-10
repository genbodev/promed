/**
 * swMorfoHistologicCorpseRecieptWindow - форма регистрации поступления тела (АРМ патологоанатома)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Shekunov Dmitriy
 */
sw.Promed.swMorfoHistologicCorpseRecieptWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'MorfoHistologicCorpseRecieptWindow',
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
	height: 130,
	modal: true,
	formStatus: 'edit',
	CorpseGiveaway_Date: null,
	EvnMorfoHistologicProto_autopsyDate: null,
	EvnMorfoHistologicProto_deathDate: null,
	doSave: function() {

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
		var baseForm = form.getForm();

		if ( !baseForm.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
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

		if ( record ) {
			baseForm.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
		}

		var recieptDate = baseForm.findField('MorfoHistologicCorpse_recieptDate').getValue();

		// ошибки
		var corpseGiveawayDate = this.CorpseGiveaway_Date,
			autopsyDate = this.EvnMorfoHistologicProto_autopsyDate,
			deathDate = this.EvnMorfoHistologicProto_deathDate;

		if ((autopsyDate && recieptDate > autopsyDate) || (corpseGiveawayDate && recieptDate > corpseGiveawayDate)) {
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['data_postupleniya_tela_ne_mozhet_byit_pozhe_chem_data_vskryitiya_ili_data_vydachi']);
			return false;
		}

		if (deathDate && recieptDate < deathDate) {
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['data_postupleniya_tela_ne_mozhet_byit_ranshe_chem_data_smerti']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Сохранение..." });
		loadMask.show();

		baseForm.submit({
			failure: function(resultForm, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			success: function(resultForm, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.MorfoHistologicCorpseReciept_id) {
						var MorfoHistologicCorpseReciept_id = action.result.MorfoHistologicCorpseReciept_id;

						baseForm.findField('MorfoHistologicCorpseReciept_id').setValue(MorfoHistologicCorpseReciept_id);

						var data = {};

						data.MorfoHistologicCorpseRecieptData = {
							'EvnDirectionMorfoHistologic_id': baseForm.findField('EvnDirectionMorfoHistologic_id').getValue(),
							'MorfoHistologicCorpseReciept_id': baseForm.findField('MorfoHistologicCorpseReciept_id').getValue(),
							'MorfoHistologicCorpse_recieptDate': baseForm.findField('MorfoHistologicCorpse_recieptDate').getValue(),
							'MedPersonal_id': baseForm.findField('MedPersonal_id').getValue(),
						};

						this.callback(data);

						var patGrid = Ext.getCmp('WorkPlacePathoMorphologyGridPanel');
						if (patGrid && patGrid.ViewGridStore) {
							patGrid.ViewGridStore.reload();
						}
						this.hide();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'MorfoHistologicCorpseRecieptForm',
			region: 'center',
			reader: new Ext.data.JsonReader({
				success: Ext.EmptyFn
			},  [
				{ name: 'EvnDirectionMorfoHistologic_id' },
				{ name: 'MorfoHistologicCorpse_recieptDate' },
				{ name: 'MedPersonal_id' },
				{ name: 'MorfoHistologicCorpseReciept_id' },
			]),
			url: '/?c=MorfoHistologicCorpseReciept&m=saveMorfoHistologicCorpseReciept',

			items: [{
				name: 'EvnDirectionMorfoHistologic_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MorfoHistologicCorpseReciept_id',
				value: 0,
				xtype: 'hidden'
			}, {
				border: false,
				layout: 'form',
				labelWidth: 120,
				labelAlign: 'right',
				items: [{
						allowBlank: false,
						fieldLabel: lang['data_postupleniya'],
						name: 'MorfoHistologicCorpse_recieptDate',
						width: 100,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						format: 'd.m.Y',
						maxValue: getGlobalOptions().date,
						tabIndex: TABINDEX_MHCR + 1,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						fieldLabel: lang['telo_prinyal'],
						hiddenName: 'MedStaffFact_id',
						submitValue: false,
						id: 'MHCR_MedStaffFactCombo',
						listWidth: 450,
						tabIndex: TABINDEX_MHCR + 2,
						width: 400,
						xtype: 'swmedstafffactglobalcombo'
					}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var baseForm = this.FormPanel.getForm();
					if ( this.action == 'view' ) {
					this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						baseForm.findField('MedStaffFact_id').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_MHCR + 3,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						this.buttons[1].focus();
					}.createDelegate(this),
					onTabAction: function () {
						if ( this.action != 'view' ) {
							this.FormPanel.getForm().findField('MorfoHistologicCorpse_recieptDate').focus(true);
						}
						else {
							this.buttons[1].focus(true);
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_MHCR + 4,
					text: BTN_FRMCANCEL
				}],
			items: [
				this.FormPanel
			],
			layout: 'border'
		});
		sw.Promed.swMorfoHistologicCorpseRecieptWindow.superclass.initComponent.apply(this, arguments);

	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var currentWindow = Ext.getCmp('MorfoHistologicCorpseRecieptWindow');

			switch ( e.getKey() ) {
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
	
	show: function() {
		sw.Promed.swMorfoHistologicCorpseRecieptWindow.superclass.show.apply(this, arguments);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['podojdite'] });
		loadMask.show();

		this.restore();
		this.center();

		var baseForm = this.FormPanel.getForm();
		baseForm.reset();
		baseForm.findField('MedStaffFact_id').getStore().removeAll();

		var action = arguments[0].action;
		baseForm.setValues(arguments[0].formParams);
		this.CorpseGiveaway_Date = arguments[0].CorpseGiveaway_Date;
		this.EvnMorfoHistologicProto_autopsyDate = arguments[0].EvnMorfoHistologicProto_autopsyDate;
		this.EvnMorfoHistologicProto_deathDate = arguments[0].EvnMorfoHistologicProto_deathDate;
		
		baseForm.findField('MorfoHistologicCorpse_recieptDate').disable();
		baseForm.findField('MedStaffFact_id').disable();
		
		if (action === 'view') {
			this.buttons[0].disable();
			this.buttons[0].hide();
		} else {
			this.buttons[0].enable();
			this.buttons[0].show();
		}

		switch (action) {
			case 'view':
			case 'edit':

				var morfoHistologicCorpseReciept_id = baseForm.findField('MorfoHistologicCorpseReciept_id').getValue();

				if ( !morfoHistologicCorpseReciept_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				baseForm.load({
					params: {
						'MorfoHistologicCorpseReciept_id': morfoHistologicCorpseReciept_id
					},
					failure: function () {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
							this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					success: function () {
						if ( action == 'edit' ) {
							this.setTitle(WND_PATHOMORPH_MHCRFEDIT);
						}
						else {
							this.setTitle(WND_PATHOMORPH_MHCRFVIEW);
						}
						var index;
						var record;
						var medPersonal_id = baseForm.findField('MedPersonal_id').getValue();
						
						baseForm.findField('MorfoHistologicCorpse_recieptDate').setDisabled(action == 'view');

						baseForm.findField('MedStaffFact_id').getStore().load({
							callback: function () {
								index = baseForm.findField('MedStaffFact_id').getStore().findBy(function (rec) {
									if (rec.get('MedPersonal_id') == medPersonal_id) {
										return true;
									} else {
										return false;
									}
								});
								record = baseForm.findField('MedStaffFact_id').getStore().getAt(index);
								if ( record ) {
									baseForm.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
								}
								
								baseForm.findField('MedStaffFact_id').setDisabled(action == 'view');

							}.createDelegate(this),
							params: {
								LpuSection_id: getGlobalOptions().CurLpuSection_id
							}
						});
						loadMask.hide();
					}.createDelegate(this),
					url: '/?c=MorfoHistologicCorpseReciept&m=loadMorfoHistologicCorpseRecieptEditForm'
				});
				
				break;

			case 'add':
				this.setTitle(WND_PATHOMORPH_MHCRFADD);

				//загружаем врачей для выпадающего списка и устанавливаем время
				baseForm.findField('MorfoHistologicCorpse_recieptDate').setValue(getGlobalOptions().date, 'd.m.Y');
				baseForm.findField('MorfoHistologicCorpse_recieptDate').enable();

				baseForm.findField('MedStaffFact_id').getStore().load({
					callback: function() {
						baseForm.findField('MedStaffFact_id').enable();
						loadMask.hide();
					},
					params: {
						LpuSection_id: getGlobalOptions().CurLpuSection_id
					},
				});
				break;
		}
	}
});