/**
 * swMedStaffFactProfileEditWindow - выбор профиля для сотрудника
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Usluga
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      22.04.2014
 * @comment      Префикс для id компонентов EUPSEF (EvnUslugaParSimpleEditForm)
 *
 */

sw.Promed.swMedStaffFactProfileEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	title: lang['sotrudnik_vyibor_profilya'],
	doSave: function() {
		var win = this;

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

		win.getLoadMask(lang['sohranenie_profilya_sotrudnika']).show();

		base_form.submit({
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();
			},
			success: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();

				if ( action.result ) {
					win.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	draggable: true,
	id: 'MedStaffFactProfileEditWindow',
	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnUslugaParSimpleEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MedStaffFact_id'},
				{name: 'Person_Fio'},
				{name: 'LpuSection_Name'},
				{name: 'MedSpecOms_Name'},
				{name: 'LpuSectionProfile_id'}
				,{name: 'mso_id'},
				,{name: 'MedSpecOmsExt_id'}
			]),
			region: 'center',
			url: '/?c=MedPersonal&m=saveMedStaffFactProfileEditForm',
			items: [{
				name: 'MedStaffFact_id',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'Person_Fio',
				anchor: '100%',
				fieldLabel: lang['sotrudnik'],
				readOnly: true,
				xtype: 'textfield'
			}, {
				name: 'LpuSection_Name',
				anchor: '100%',
				fieldLabel: lang['otdelenie'],
				readOnly: true,
				xtype: 'textfield'
			}, {
				name: 'MedSpecOms_Name',
				anchor: '100%',
				fieldLabel: lang['spetsialnost'],
				readOnly: true,
				xtype: 'textfield'
			}, {
				name: 'mso_id',
				xtype: 'hidden'
			}, {
				hiddenName: 'LpuSectionProfile_id',
				url: '/?c=MedPersonal&m=loadLpuSectionProfileForMedStaffFact',
				fields: [
					{name: 'LpuSectionProfile_id',    type:'int'},
					{name: 'LpuSectionProfile_Code',  type:'string'},
					{name: 'LpuSectionProfile_Name',  type:'string'},
					{name: 'defaultValue',  type:'int'}
				],
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['profil'],
				listWidth: 700,
				xtype: 'swlpusectionprofileremotecombo'
			}, {
				fieldLabel: 'Доп. специальность для рег. портала',// #137959
				hiddenName: 'MedSpecOmsExt_id',
				comboSubject: 'MedSpecOms',
				anchor: '100%',
				xtype: 'swcommonsprcombo',
				lastQuery: '',
				onLoadStore: function(){
					win.filterMedSpecOmsStore();
				},
				listeners: {
					render: function(c){
						Ext.QuickTips.register({
							target: c.getEl(),
							text: 'Укажите специальность для группировки на портале, если специалист временно выполняет обязанности узкого специалиста',
							enabled: true,
							showDelay: 5,
							trackMouse: true,
							autoShow: true
						});
					}.createDelegate(this)
				}
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
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
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					text: BTN_FRMCANCEL
				}],
			items: [ this.FormPanel ]
		});

		sw.Promed.swMedStaffFactProfileEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: true,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	filterMedSpecOmsStore: function(){// #137959 в доп. специальности все, кроме основной специальности врача
		var base_form = this.FormPanel.getForm();
		var mso_id_val = parseInt(base_form.findField('mso_id').getValue());
		if(! mso_id_val) return;
		base_form.findField('MedSpecOmsExt_id').getStore().filterBy(function(rec){
			return (rec.get('MedSpecOms_id') != mso_id_val);
		});
	},
	show: function() {
		sw.Promed.swMedStaffFactProfileEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.restore();
		this.center();

		if ( !arguments[0] || !arguments[0].MedStaffFact_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		var MedStaffFact_id = arguments[0].MedStaffFact_id;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		base_form.findField('MedSpecOmsExt_id').setContainerVisible(getRegionNick() == 'ekb');

		this.syncSize();
		this.syncShadow();

		win.getLoadMask(LOAD_WAIT).show();
		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
			}.createDelegate(this),
			params: {
				'MedStaffFact_id': MedStaffFact_id
			},
			success: function() {
				win.getLoadMask().hide();

				var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
				base_form.findField('LpuSectionProfile_id').getStore().load({
					params: {
						'MedStaffFact_id': MedStaffFact_id
					},
					callback: function() {
						var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
						});

						if ( index >= 0 ) {
							base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
						} else if ( base_form.findField('LpuSectionProfile_id').getStore().getCount() > 0 ) {
							var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
								return (rec.get('defaultValue') == 1);
							});

							if ( index >= 0 ) {
								base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(index).get('LpuSectionProfile_id'));
							} else {
								base_form.findField('LpuSectionProfile_id').clearValue();
							}
						} else {
							base_form.findField('LpuSectionProfile_id').clearValue();
						}

						win.filterMedSpecOmsStore();
					}
				});
			}.createDelegate(this),
			url: '/?c=MedPersonal&m=loadMedStaffFactProfileEditForm'
		});
	},
	width: 500
});
