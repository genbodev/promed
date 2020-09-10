/**
 * swMedicalCareKindLinkEditWindow - окно редактирование настроек кодов видов медицинской помощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.11.2013
 */

sw.Promed.swMedicalCareKindLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	id: 'swMedicalCareKindLinkEditWindow',
	width: 650,
	//height: 450,
	callback: Ext.emptyFn,
	draggable: true,
	maximizable: false,
	modal: true,
	title: lang['nastroyka_kodov_vidov_meditsinskoy_pomoschi'],

	doSave: function()
	{
		var base_form = this.FormPanel.getForm();
		var wnd = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		wnd.getLoadMask("Подождите, идет сохранение...").show();

		base_form.submit({
			failure: function(result_form, action) {
				wnd.getLoadMask().hide()
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();

				if ( action.result ) {
					wnd.callback();
					wnd.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki']);
				}
			}
		});
	},

	show: function()
	{
		sw.Promed.swMedicalCareKindLinkEditWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments && arguments[0].onHide) {
			this.action = arguments[0].action;
		}

		if (arguments && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		base_form.findField('EvnClass_id').lastQuery = '';
		base_form.findField('EvnClass_id').getStore().clearFilter();
		base_form.findField('EvnClass_id').getStore().filterBy(function(rec) {
			return rec.get('EvnClass_SysNick').inlist([
				 'EvnPLDispOrp'
				,'EvnVizitPL'
				,'EvnVizitPLStom'
				,'EvnSection'
				,'EvnPLDispDop13'
				,'EvnPLDispProf'
				,'EvnPLDispTeenInspection'
				,'EvnCmp'
			]);
		});

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['nastroyka_kodov_vidov_meditsinskoy_pomoschi_dobavlenie']);
				this.enableEdit(true);
				base_form.findField('EvnClass_id').fireEvent('change', base_form.findField('EvnClass_id'), base_form.findField('EvnClass_id').getValue());
				base_form.findField('MedicalCareKind_id').focus(true, 250);
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				var medical_care_kind_link_id = base_form.findField('MedicalCareKindLink_id').getValue();

				if ( Ext.isEmpty(medical_care_kind_link_id) ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						MedicalCareKindLink_id: medical_care_kind_link_id
					},
					success: function() {
						if ( this.action == 'edit' ) {
							this.setTitle(lang['nastroyka_kodov_vidov_meditsinskoy_pomoschi_redaktirovanie']);
							this.enableEdit(true);
						}
						else {
							this.setTitle(lang['nastroyka_kodov_vidov_meditsinskoy_pomoschi_prosmotr']);
							this.enableEdit(false);
						}

						base_form.findField('EvnClass_id').fireEvent('change', base_form.findField('EvnClass_id'), base_form.findField('EvnClass_id').getValue());

						loadMask.hide();

						if ( this.action == 'edit' ) {
							base_form.findField('MedicalCareKind_id').focus(true, 250);
						} else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=MedicalCareKindLink&m=loadMedicalCareKindLinkForm'
				});
				break;

			default:
				loadMask.hide();
				this.hide();
				break;
		}
	},

	initComponent: function()
	{
		var wnd = this;

		this.FormPanel = new Ext.form.FormPanel({
			//bodyStyle: 'padding: 5px 20px 0',
			border: false,
			frame: true,
			id: 'MCKLEW_MedicalCareKindLinkEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			region: 'center',
			url: '/?c=MedicalCareKindLink&m=saveMedicalCareKindLink',

			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{name: 'MedicalCareKindLink_id'},
				{name: 'MedicalCareKind_id'},
				{name: 'LpuSectionProfile_id'},
				{name: 'EvnClass_id'},
				{name: 'PayType_id'},
				{name: 'LpuUnitType_id'}
			]),
			items: [{
				name: 'MedicalCareKindLink_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['vid_meditsinskoy_pomoschi'],
				hiddenName: 'MedicalCareKind_id',
				id: 'MCKLEW_MedicalCareKind_id',
				xtype: 'swmedicalcarekindfedcombo',
				width: 450
			}, {
				allowBlank: false,
				fieldLabel: lang['profil_otdeleniya'],
				hiddenName: 'LpuSectionProfile_id',
				id: 'MCKLEW_LpuSectionProfile_id',
				xtype: 'swlpusectionprofilecombo',
				width: 450
			}, {
				allowBlank: false,
				allowSysNick: true,
				comboSubject: 'EvnClass',
				fieldLabel: lang['vid_dokumenta'],
				hiddenName: 'EvnClass_id',
				id: 'MCKLEW_EvnClass_id',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('EvnClass_id') == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var base_form = wnd.FormPanel.getForm();

						if ( typeof record == 'object' && record.get('EvnClass_SysNick') == 'EvnSection' ) {
							base_form.findField('LpuUnitType_id').setAllowBlank(false);
						}
						else {
							base_form.findField('LpuUnitType_id').setAllowBlank(true);
						}

						base_form.findField('LpuUnitType_id').validate();
					}
				},
				typeCode: 'int',
				xtype: 'swcommonsprcombo',
				width: 450
			}, {
				allowBlank: false,
				fieldLabel: lang['istochnik_finansirovaniya'],
				hiddenName: 'PayType_id',
				id: 'MCKLEW_PayType_id',
				xtype: 'swpaytypecombo',
				width: 450
			}, {
				fieldLabel: lang['tip_gruppyi_otdeleniy'],
				hiddenName: 'LpuUnitType_id',
				id: 'MCKLEW_LpuUnitType_id',
				xtype: 'swlpuunittypecombo',
				width: 450
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'MCKLEW_SaveButton',
				text: BTN_FRMSAVE
				},
				'-',
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'MCKLEW_CancelButton',
					tabIndex: 2409,
					text: BTN_FRMCANCEL
				}]
		});

		sw.Promed.swMedicalCareKindLinkEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
