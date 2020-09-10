/**
* swEvnSectionDrugPSLinkEditWindow - окно редактирования/добавления медикамента/мероприятия в стационаре.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @comment      Префикс для id компонентов ESDPLEF (EvnSectionDrugPSLinkEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnSectionDrugPSLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnSectionDrugPSLinkEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnSectionDrugPSLinkEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();
		var drugps_name = '';
		var DrugPS_id = base_form.findField('DrugPS_id').getValue();
		var drugpsform_name = '';
		var DrugPSForm_id = base_form.findField('DrugPSForm_id').getValue();
		var index;
		var params = new Object();

		index = base_form.findField('DrugPS_id').getStore().findBy(function(rec) {
			if ( rec.get('DrugPS_id') == DrugPS_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			drugps_name = base_form.findField('DrugPS_id').getStore().getAt(index).get('DrugPS_Name');
		}

		index = base_form.findField('DrugPSForm_id').getStore().findBy(function(rec) {
			if ( rec.get('DrugPSForm_id') == DrugPSForm_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			drugpsform_name = base_form.findField('DrugPSForm_id').getStore().getAt(index).get('DrugPSForm_Name');
		}

		data.EvnSectionDrugPSLinkData = [{
			'DrugPS_Name': drugps_name,
			'DrugPSForm_Name': drugpsform_name,
			'EvnSectionDrugPSLink_id': base_form.findField('EvnSectionDrugPSLink_id').getValue(),
			'EvnSection_id': base_form.findField('EvnSection_id').getValue(),
			'DrugPS_id': base_form.findField('DrugPS_id').getValue(),
			'DrugPSForm_id': base_form.findField('DrugPSForm_id').getValue(),
			'EvnSectionDrugPSLink_Dose': base_form.findField('EvnSectionDrugPSLink_Dose').getValue()
		}];

		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();

				data.EvnSectionDrugPSLinkData[0].EvnSectionDrugPSLink_id = base_form.findField('EvnSectionDrugPSLink_id').getValue();

				this.callback(data);
				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.EvnSectionDrugPSLink_id > 0 ) {
							base_form.findField('EvnSectionDrugPSLink_id').setValue(action.result.EvnSectionDrugPSLink_id);

							data.EvnSectionDrugPSLinkData[0].EvnSectionDrugPSLink_id = base_form.findField('EvnSectionDrugPSLink_id').getValue();

							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	id: 'EvnSectionDrugPSLinkEditWindow',
	initComponent: function() {
		var curwin = this;
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnSectionDrugPSLinkEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{ name: 'EvnSectionDrugPSLink_id' }
			]),
			url: '/?c=EvnSectionDrugPSLink&m=saveEvnSectionDrugPSLink',

			items: [{
				name: 'EvnSectionDrugPSLink_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnSection_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				autoLoad: false,
				valueField: 'DrugPS_id',
				codeField: 'DrugPS_Code',
				displayField: 'DrugPS_Name',
				fieldLabel: 'Медикамент/мероприятие',
				hiddenName: 'DrugPS_id',
				tabIndex: this.tabIndex + 3,
				typeCode: 'int',
				anchor: '100%',
				minChars: 0,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{DrugPS_Code}</font>&nbsp',
					'{DrugPS_Name}',
					'</div></tpl>'
				),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'DrugPS_id', type: 'int'},
						{name: 'DrugPS_Code', type: 'string'},
						{name: 'DrugPS_Name', type: 'string'}
					],
					key: 'DrugPS_id',
					sortInfo: {
						field: 'DrugPS_Code'
					},
					url: '/?c=EvnSectionDrugPSLink&m=loadDrugPSList'
				}),
				xtype: 'swbaseremotecombo'
			}, {
				allowBlank: true,
				autoLoad: false,
				comboSubject: 'DrugPSForm',
				fieldLabel: 'Форма',
				hiddenName: 'DrugPSForm_id',
				tabIndex: this.tabIndex + 4,
				typeCode: 'int',
				anchor: '100%',
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				allowDecimals: true,
				allowNegative: false,
				fieldLabel: 'Дозировка (курсовая)',
				name: 'EvnSectionDrugPSLink_Dose',
				width: 90,
				decimalPrecision: 2,
				xtype: 'numberfield'
			}]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'ESDPLEF_PersonInformationFrame'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: this.tabIndex + 6,
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
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						if ( !this.FormPanel.getForm().findField('DrugPS_id').disabled ) {
							this.FormPanel.getForm().findField('DrugPS_id').focus(true);
						}
					}
				}.createDelegate(this),
				tabIndex: this.tabIndex + 7,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if ( this.action != 'view' ) {
								this.doSave();
							}
						break;

						case Ext.EventObject.J:
							this.hide();
						break;
					}
				}.createDelegate(this),
				key: [
					 Ext.EventObject.C
					,Ext.EventObject.J
				],
				scope: this,
				stopEvent: true
			}]
		});

		sw.Promed.swEvnSectionDrugPSLinkEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnSectionDrugPSLinkEditWindow.superclass.show.apply(this, arguments);
		var curwin = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.center();
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'local' ) {
			this.formMode = arguments[0].formMode;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.MesTariff_id = null;
		if ( arguments[0].MesTariff_id ) {
			this.MesTariff_id = arguments[0].MesTariff_id;
		}
		base_form.findField('DrugPS_id').getStore().baseParams.MesTariff_id = this.MesTariff_id;

		this.EvnSection_setDate = null;
		if ( arguments[0].EvnSection_setDate ) {
			this.EvnSection_setDate = arguments[0].EvnSection_setDate;
		}
		base_form.findField('DrugPS_id').getStore().baseParams.onDate = (typeof this.EvnSection_setDate == 'object' ? Ext.util.Format.date(this.EvnSection_setDate, 'd.m.Y') : this.EvnSection_setDate);

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_HOSP_ESDPLADD);
				this.enableEdit(true);
				loadMask.hide();

				if ( !base_form.findField('DrugPS_id').disabled ) {
					base_form.findField('DrugPS_id').focus(false, 250);
				}
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_HOSP_ESDPLEDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_HOSP_ESDPLVIEW);
					this.enableEdit(false);
				}
				loadMask.hide();

				var DrugPS_id = base_form.findField('DrugPS_id').getValue();
				if (!Ext.isEmpty(DrugPS_id)) {
					base_form.findField('DrugPS_id').getStore().load({
						params: {
							DrugPS_id: DrugPS_id
						},
						callback: function() {
							base_form.findField('DrugPS_id').setValue(DrugPS_id);
						}
					});
				}

				if ( !base_form.findField('DrugPS_id').disabled ) {
					base_form.findField('DrugPS_id').focus(false, 250);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
			break;

			default:
				this.hide();
			break;
		}
	},
	tabIndex: TABINDEX_ESDPLEF,
	width: 650
});