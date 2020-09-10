/**
* Журнал учета клинико-экспертной работы МУ - форма выбора врача-эксперта
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      01.08.2011
*/

sw.Promed.swClinExWorkSelectExpertWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Выбор врача-эксперта',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 500,
	onHide: Ext.emptyFn,
	shim: false,
	buttonAlign: "right",
	objectName: 'swClinExWorkSelectExpertWindow',
	closeAction: 'hide',
	id: 'swClinExWorkSelectExpertWindow',
	objectSrc: '/jscore/Forms/clinework/swClinExWorkSelectExpertWindow.js',
	buttons: [
		{
			handler: function() {
				this.ownerCt.save();
			},
			iconCls: 'save16',
			text: 'Сохранить'
		},
		'-',
		{
			text      : 'Отмена',
			tabIndex  : -1,
			tooltip   : 'Отмена',
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		'hide': function(p) {
			p.findById('EvnVKExpertSelectForm').getForm().reset();
		}
	},
	
	show: function()
	{
		sw.Promed.swClinExWorkSelectExpertWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].action || !arguments[0].params ) {
			sw.swMsg.alert('Ошибка', 'Неверные параметры!');
			this.hide();
			return false;
		}

		this.action = arguments[0].action;
		this.formMode = 'edit';
		
		if(arguments[0].onHide)
			this.onHide = arguments[0].onHide;
		
		var params = arguments[0].params;
		
		var b_f = this.findById('EvnVKExpertSelectForm').getForm();
		b_f.findField('EvnVKExpert_IsChairman').setValue(1);
		b_f.setValues(params);

		this.isInternal = (getRegionNick() == 'vologda' && params.fromEvnVK);
		this.isExternal = (getRegionNick() == 'vologda' && !params.fromEvnVK);
		b_f.findField('VoteExpertVK_VoteDate').setContainerVisible(this.isExternal);
		b_f.findField('EvnVKExpert_Descr').setContainerVisible(this.isInternal);
		b_f.findField('EvnVKExpert_isApproved').setContainerVisible(this.isInternal);
		b_f.findField('EvnVKExpert_isApproved').setAllowBlank(!this.isInternal);
		b_f.findField('EvnVKExpert_isApproved').fireEvent('change', b_f.findField('EvnVKExpert_isApproved'), params.EvnVKExpert_isApproved);
		
		var msmp_combo = b_f.findField('MedServiceMedPersonal_id');
		msmp_combo.getStore().baseParams = {
			MedService_id: params.MedService_id,
			MedServiceType_id: 1 // Только ВК
		};	
		msmp_combo.getStore().load({
			callback: function() {
				if( params.MedServiceMedPersonal_id ) {
					msmp_combo.setValue(params.MedServiceMedPersonal_id);
				}
				b_f.findField('MedServiceMedPersonal_id').fireEvent('change', b_f.findField('MedServiceMedPersonal_id'), b_f.findField('MedServiceMedPersonal_id').getValue());
			}
		});

		this.prevExpertMedStaffType_id = null;
		if (this.action != 'add' && params.ExpertMedStaffType_id) {
			this.prevExpertMedStaffType_id = params.ExpertMedStaffType_id;
		}
		this.center();
	},
	
	save: function()
	{
		if ( this.formMode != 'edit' ) {
			return false;
		}

		this.formMode = 'save';

		var form = this.findById('EvnVKExpertSelectForm').getForm();
		if(!form.isValid()) {
			this.formMode = 'edit';
			sw.swMsg.alert('Ошибка', 'Не все обязательные поля заполнены!');
			return false;
		}

		if ( form.findField('EvnVKExpert_IsChairman').getValue() == 2 ) {
			form.findField('ExpertMedStaffType_id').setValue(1);
		}
		else {
			form.findField('ExpertMedStaffType_id').setValue(2);
		}
		
		if (this.isExternal) {
			var params = form.getValues();
			params.MF_Person_FIO = form.findField('MedServiceMedPersonal_id').getFieldValue('MedPersonal_Fio');
			params.VoteExpertVK_VoteDate = Ext.util.Format.date(form.findField('VoteExpertVK_VoteDate').getValue(), 'd.m.Y');
			this.hide();
			this.onHide(params);
			return;
		}

		var lm = this.getLoadMask('Сохранение...');
		lm.show();
		form.submit({
			failure: function(r, action) {
				lm.hide();
				this.formMode = 'edit';
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					} else {
						sw.swMsg.alert('Ошибка', 'При сохранении эксперта произошла ошибка!');
					}
				}
			}.createDelegate(this),
			success: function() {
				lm.hide();
				this.formMode = 'edit';
				this.hide();
				this.onHide();
			}.createDelegate(this)
		});
	},
	
	initComponent: function()
	{
		var win = this;

		Ext.apply(this,
		{
			layout: 'fit',
			defaults: {
				border: false,
				bodyStyle: 'padding: 5px;'
			},
			items: [
				{
					//layout: 'form',
					region: 'center',
					labelAlign: 'right',
					xtype: 'form',
					autoHeight: true,
					frame: true,
					border: false,
					id: 'EvnVKExpertSelectForm',
					url: '/?c=ClinExWork&m=saveEvnVKExpert',
					labelWidth: 120,
					items: [
						{
							xtype: 'hidden',
							name: 'EvnVKExpert_id'
						}, {
							xtype: 'hidden',
							name: 'VoteExpertVK_id'
						}, {
							xtype: 'hidden',
							name: 'EvnVK_id'
						}, {
							xtype: 'hidden',
							name: 'MedService_id'
						}, {
							xtype: 'hidden',
							name: 'ExpertMedStaffType_id'
						},
						{
							allowBlank: false,
							xtype: 'swmedservicemedpersonalcombo',
							fieldLabel: 'Врач службы ВК',
							anchor: '100%',
							hiddenName: 'MedServiceMedPersonal_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.findById('EvnVKExpertSelectForm').getForm();
									var medPersonalIdList = [];
									var msfCombo = base_form.findField('MedStaffFact_id');
									var MedStaffFact_id = msfCombo.getValue();
									var MedPersonal_id = combo.getFieldValue('MedPersonal_id');
									if (!Ext.isEmpty(MedPersonal_id)) {
										medPersonalIdList.push(MedPersonal_id);
									}
									setMedStaffFactGlobalStoreFilter({
										medPersonalIdList: medPersonalIdList
									});
									msfCombo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
									var index = msfCombo.getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == MedStaffFact_id);
									});
									if (index >= 0) {
										msfCombo.setValue(MedStaffFact_id);
									} else if (msfCombo.getStore().getCount() > 0) {
										msfCombo.setValue(msfCombo.getStore().getAt(0).get('MedStaffFact_id'));
									} else {
										msfCombo.clearValue();
									}
								}
							}
						}, {
							allowBlank: false,
							anchor: '100%',
							fieldLabel: 'Место работы',
							listWidth: 600,
							hiddenName: 'MedStaffFact_id',
							xtype: 'swmedstafffactglobalcombo'
						}, {
							xtype: 'swcommonsprcombo',
							comboSubject: 'YesNo',
							allowBlank: false,
							hiddenName: 'EvnVKExpert_IsChairman',
							fieldLabel: 'Председатель ВК'
						}, {
							xtype: 'swdatefield',
							name: 'VoteExpertVK_VoteDate',
							fieldLabel: 'Срок вынесению решения'
						}, {
							xtype: 'swyesnocombo',
							hiddenName: 'EvnVKExpert_isApproved',
							fieldLabel: 'Решение эксперта',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('YesNo_id') == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index));
								},
								'select': function(combo, record, index) {
									if (getRegionNick() != 'vologda') return false;
									var newValue = record && record.get('YesNo_id') ? record.get('YesNo_id') : null;
									var form = this.findById('EvnVKExpertSelectForm').getForm();
									form.findField('EvnVKExpert_Descr').setAllowBlank(newValue != 1);
								}.createDelegate(this)
							}
						}, {
							xtype: 'textarea',
							name: 'EvnVKExpert_Descr',
							anchor: '100%',
							fieldLabel: 'Комментарий эксперта'
						}
					]
				}
			]
		});
		sw.Promed.swClinExWorkSelectExpertWindow.superclass.initComponent.apply(this, arguments);
	}
});