/**
 * swMorfoHistologicCorpseGiveawayWindow - форма отказа от вскрытия тела умершего (АРМ патологоанатома)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Shekunov Dmitriy
 */
sw.Promed.swMorfoHistologicRefuseWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'MorfoHistologicRefuseWindow',
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
					if (action.result.MorfoHistologicRefuse_id) {
						var MorfoHistologicRefuse_id = action.result.MorfoHistologicRefuse_id;

						baseForm.findField('MorfoHistologicRefuse_id').setValue(MorfoHistologicRefuse_id);

						var data = {};

						data.MorfoHistologicRefuse_id = {
							'EvnDirectionMorfoHistologic_id': baseForm.findField('EvnDirectionMorfoHistologic_id').getValue(),
							'MorfoHistologicRefuse_id': baseForm.findField('MorfoHistologicRefuse_id').getValue(),
							'MorfoHistologic_refuseDate': baseForm.findField('MorfoHistologic_refuseDate').getValue(),
							'RefuseType_id': baseForm.findField('RefuseType_id').getValue(),
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
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'MorfoHistologicRefuseForm',
			region: 'center',
			reader: new Ext.data.JsonReader({
				success: Ext.EmptyFn
			}, [
				{ name: 'EvnDirectionMorfoHistologic_id' },
				{ name: 'MorfoHistologicRefuse_id' },
				{ name: 'MorfoHistologic_refuseDate' },
				{ name: 'RefuseType_id' },
				{ name: 'Person_id' }
			]),
			url: '/?c=MorfoHistologicRefuse&m=saveMorfoHistologicRefuse',

			items: [{
				name: 'EvnDirectionMorfoHistologic_id',
				value: 0,
				xtype: 'hidden',
			}, {
				name: 'MorfoHistologicRefuse_id',
				value: 0,
				xtype: 'hidden'
			}, {
				border: false,
				layout: 'form',
				labelWidth: 150,
				labelAlign: 'right',
				items: [{
					allowBlank: false,
					fieldLabel: lang['data_otkaza_ot_vskrytiya'],
					name: 'MorfoHistologic_refuseDate',
					width: 100,
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					format: 'd.m.Y',
					maxValue: getGlobalOptions().date,
					tabIndex: TABINDEX_MHR + 1,
					xtype: 'swdatefield'
				}, {
					xtype: 'combo',
					allowBlank: false,
					fieldLabel: lang['osnovanie_dlya_otkaza'],
					hiddenName: 'RefuseType_id',
					displayField: 'MorfoHistologicRefuseType_name',
					valueField: 'MorfoHistologicRefuseType_id',
					submitValue: false,
					id: 'MHR_RefuseTypeCombo',
					listWidth: 450,
					tabIndex: TABINDEX_MHR + 2,
					width: 400,
					triggerAction: 'all',
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{MorfoHistologicRefuseType_code}. ' ,
						'{MorfoHistologicRefuseType_name}',
						'</div></tpl>'
					),
					store: new Ext.data.JsonStore({
						autoLoad: true,
						url: '/?c=MorfoHistologicRefuse&m=getMorfoHistologicRefuseTypeList',
						fields: [
							{ name: 'MorfoHistologicRefuseType_id', type: 'int' },
							{ name: 'MorfoHistologicRefuseType_code', type: 'int' },
							{ name: 'MorfoHistologicRefuseType_name', type: 'string' }
						]
					}),
					listeners: {
						'select': function (combo, record) {
							if (record.get('MorfoHistologicRefuseType_code') != 4) {
								win.FormPanel.getForm().findField('Person_id').setContainerVisible(true);
								win.FormPanel.getForm().findField('Person_id').setAllowBlank(false);
							}
							else {
								win.FormPanel.getForm().findField('Person_id').setContainerVisible(false);
								win.FormPanel.getForm().findField('Person_id').setAllowBlank(true);
								win.FormPanel.getForm().findField('Person_id').clearValue();
							}
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					fieldLabel: lang['otkaz_podpisal'],
					hiddenName: 'Person_id',
					submitValue: true,
					id: 'MHR_PersonCombo',
					tabIndex: TABINDEX_MHR + 3,
					width: 400,
					hidden: true,
					xtype: 'swpersoncombo',
					trackLabels: true,
					onTrigger1Click: function () {
						var combo = this;
						
						getWnd('swPersonSearchWindow').show({
							searchMode: 'older14notdead',
							onSelect: function (personData) {
								if (!personData.Document_Ser && !personData.Document_Num) {
									sw.swMsg.show(
										{
											buttons: Ext.Msg.YESNO,
											fn: function (buttonId) {
												if (buttonId == 'yes') {
													win.setPersonField(combo, personData);
												}
											}.createDelegate(this),
											icon: Ext.Msg.QUESTION,
											msg: lang['u_rodstvennika_umershego_otsutstvuiyt_dannye_o_documente'],
											title: lang['vopros']
										});
								}
								else {
									win.setPersonField(combo, personData);
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
				tabIndex: TABINDEX_MHR + 4,
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
						this.buttons[1].focus();
					}.createDelegate(this),
					onTabAction: function () {
						if (this.action != 'view') {
							this.FormPanel.getForm().findField('MorfoHistologicCorpse_recieptDate').focus(true);
						} else {
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
		sw.Promed.swMorfoHistologicRefuseWindow.superclass.initComponent.apply(this, arguments);

	},
	keys: [{
		alt: true,
		fn: function (inp, e) {
			var current_window = Ext.getCmp('MorfoHistologicRefuseWindow');

			switch (e.getKey()) {
				case Ext.EventObject.C:
					current_window.doSave();
					break;

				case Ext.EventObject.J:
					current_window.hide();
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
		sw.Promed.swMorfoHistologicRefuseWindow.superclass.show.apply(this, arguments);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		loadMask.show();

		this.restore();
		this.center();

		var baseForm = this.FormPanel.getForm();
		baseForm.reset();
		baseForm.findField('Person_id').getStore().removeAll();
		baseForm.findField('RefuseType_id').getStore().removeAll();
		
		baseForm.findField('Person_id').clearValue();
		baseForm.findField('Person_id').setContainerVisible(false);
		baseForm.findField('Person_id').setAllowBlank(true);

		var action = arguments[0].action;
		baseForm.setValues(arguments[0].formParams);

		if (action === 'view') {
			baseForm.findField('MorfoHistologic_refuseDate').disable();
			baseForm.findField('RefuseType_id').disable();
			this.buttons[0].disable();
			this.buttons[0].hide();
		} else {
			baseForm.findField('MorfoHistologic_refuseDate').enable();
			baseForm.findField('RefuseType_id').enable();
			this.buttons[0].enable();
			this.buttons[0].show();
		}

		switch (action) {
			case 'view':
			case 'edit':

				var morfoHistologicRefuse_id = baseForm.findField('MorfoHistologicRefuse_id').getValue();

				if (!morfoHistologicRefuse_id) {
					loadMask.hide();
					this.hide();
					return false;
				}

				baseForm.load({
					params: {
						'MorfoHistologicRefuse_id': morfoHistologicRefuse_id
					},
					failure: function () {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
							this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					success: function () {
						if (action == 'edit') {
							this.setTitle(WND_PATHOMORPH_MHRFEDIT);
						} else {
							this.setTitle(WND_PATHOMORPH_MHRFVIEW);
						}
						
						var refuseType_id = baseForm.findField('RefuseType_id').getValue();
						var person_id = baseForm.findField('Person_id').getValue();
						
						baseForm.findField('RefuseType_id').getStore().load({
							params: refuseType_id,
							callback: function () {
								baseForm.findField('RefuseType_id').setValue(refuseType_id);
							}.createDelegate(this),
						});

						if (refuseType_id != 4 && person_id) {
							baseForm.findField('Person_id').getStore().load({
								params: {
									Person_id: person_id
								},
								callback: function() {
									baseForm.findField('Person_id').setValue(person_id);
								}.createDelegate(this)
							});
							baseForm.findField('Person_id').setContainerVisible(true);
							baseForm.findField('Person_id').setAllowBlank(false);
							baseForm.findField('Person_id').setDisabled(action === 'view');
						}
						else {
							baseForm.findField('Person_id').clearValue();
							baseForm.findField('Person_id').setContainerVisible(false);
							baseForm.findField('Person_id').setAllowBlank(true);
							baseForm.findField('Person_id').setDisabled(action === 'view');
						}
						
						loadMask.hide();
					}.createDelegate(this),
					url: '/?c=MorfoHistologicRefuse&m=loadMorfoHistologicRefuseEditForm'
				});
				break;

			case 'add':
				this.setTitle(WND_PATHOMORPH_MHRFADD);
				
				baseForm.findField('MorfoHistologic_refuseDate').setValue(getGlobalOptions().date, 'd.m.Y');
				baseForm.findField('MorfoHistologic_refuseDate').enable();

				baseForm.findField('RefuseType_id').getStore().load();
				baseForm.findField('RefuseType_id').enable();
				
				loadMask.hide();
				break;
		}
	}
});