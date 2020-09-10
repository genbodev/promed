/**
 * swDirectionOnHTMEditForm - окно редактирвания направления на ВМП.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      16.07.2014
 */

sw.Promed.swDirectionOnHTMEditForm = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: true,
	maximizable: true,
	modal: false,
    onClose: Ext.emptyFn,
    onSave: Ext.emptyFn,
	//layout: 'border',
	autoScroll: true,
	buttonAlign: "right",
	objectName: 'swDirectionOnHTMEditForm',
	closeAction: 'hide',
	id: 'swDirectionOnHTMEditForm',
	objectSrc: '/jscore/Forms/Htm/swDirectionOnHTMEditForm.js',
	buttons: [
		{
			iconCls: 'save16',
			text: lang['sohranit'],
			handler: function() {
				this.ownerCt._ignoreTalonNumDateWrn = false;
				this.ownerCt.doSave();
			}
		},
		{
		 itemId: 'HTME_btnPrint',
			iconCls: 'print16',
			text: lang['pechat'],

			handler: function()
			{
				this.ownerCt.printDirection();
			}
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['otmena'],
			tabIndex  : -1,
			tooltip   : lang['otmena'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
    listeners: {
        hide: function(win){
            win.onClose(win);
        }
    },

	_curHTMedicalCareType_id: undefined,
	_curHTMFinance_Code: undefined,
	_curHTMedicalCareClass_ids: undefined,
	_curEvnDirectionHTM_directDate: undefined,
	_htmcclRequestId: undefined,  // High Technology Medical Care Class Link Request Id

	enableEdit: function(enable)
	{
		console.log('enable',enable);
		var base_form = this.FormPanel.getForm();

		base_form.items.each(function(field) {
			if (field.xtype && !field.xtype.inlist(['hidden'])) {
				field.disable();
				field.validate();
			}
		});
		base_form.findField('Person_Snils').disable();

		if (enable)
		{
			base_form.findField('PrivilegeType_id').enable();
			base_form.findField('HTMSocGroup_id').enable();
			base_form.findField('EvnDirectionHTM_setDate').enable();
			base_form.findField('EvnDirectionHTM_VKProtocolNum').enable();
			base_form.findField('EvnDirectionHTM_VKProtocolDate').enable();
			base_form.findField('EvnDirectionHTM_IsHTM').enable();
			base_form.findField('HTMOrgDirect_id').enable();			
			base_form.findField('EvnDirectionHTM_Num_KD').enable();
			base_form.findField('EvnDirectionHTM_directDate').enable();
			base_form.findField('MedStaffFact_id').enable();
			base_form.findField('EvnDirectionHTM_TalonNum').enable();
			base_form.findField('Region_id').enable();
			base_form.findField('LpuHTM_id').enable();
			base_form.findField('EvnDirectionHTM_planDate').enable();
			base_form.findField('Diag_id').enable();
			base_form.findField('LpuSectionProfile_id').enable();
			base_form.findField('HTMedicalCareClass_id').enable();
			base_form.findField('TreatmentType_id').enable();
			base_form.findField('PrehospType_did').enable();
			base_form.findField('HTMedicalCareType_id').enable();
			base_form.findField('PersonInfo_Email').enable();

			if (this.ARMType == 'spec_mz' || getRegionNick().inlist(['ufa', 'kz']))
				base_form.findField('HTMFinance_id').enable();

			this.FileListPanel.FileGrid.setReadOnly(false);
			this.buttons[0].enable();
		}
		else
		{
			base_form.findField('PrivilegeType_id').disable();
			base_form.findField('HTMSocGroup_id').disable();
			base_form.findField('EvnDirectionHTM_setDate').disable();
			base_form.findField('EvnDirectionHTM_VKProtocolNum').disable();
			base_form.findField('EvnDirectionHTM_VKProtocolDate').disable();
			base_form.findField('EvnDirectionHTM_IsHTM').disable();
			base_form.findField('HTMFinance_id').disable();
			base_form.findField('HTMOrgDirect_id').disable();
			base_form.findField('EvnDirectionHTM_Num_KD').disable();
            base_form.findField('EvnDirectionHTM_directDate').disable();
            base_form.findField('MedStaffFact_id').disable();
            base_form.findField('EvnDirectionHTM_TalonNum').disable();
			base_form.findField('Region_id').disable();
			base_form.findField('LpuHTM_id').disable();
			base_form.findField('EvnDirectionHTM_planDate').disable();
			base_form.findField('Diag_id').disable();
			base_form.findField('LpuSectionProfile_id').disable();
			base_form.findField('HTMedicalCareClass_id').disable();
			base_form.findField('TreatmentType_id').disable();
			base_form.findField('PrehospType_did').disable();
			base_form.findField('HTMedicalCareType_id').disable();
			base_form.findField('PersonInfo_Email').disable();
			this.FileListPanel.FileGrid.setReadOnly(true);
			this.buttons[0].disable();
		}
	},
	HTMedicalCareDiagStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'HTMedicalCareDiag_id', type: 'int' },
			{ name: 'HTMedicalCareClass_id', type: 'int' },
			{ name: 'HTMedicalCareType_id', type: 'int' },
			{ name: 'Diag_id', type: 'int' },
			{ name: 'HTMedicalCareDiag_begDate', type: 'date', dateFormat: 'd.m.Y' },
			{ name: 'HTMedicalCareDiag_endDate', type: 'date', dateFormat: 'd.m.Y' }
		],
		key: 'HTMedicalCareDiag_id',
		tableName: 'HTMedicalCareDiag'
	}),
	filterHTMedicalCareTypeCombo: function() {
		var base_form = this.FormPanel.getForm();

		if ( this.HTMedicalCareDiagStore.getCount() == 0 ) {
			this.HTMedicalCareDiagStore.load({
				callback: function() {
					this.filterHTMedicalCareTypeCombo();
				}.createDelegate(this)
			});
			return false;
		}

		var
			Diag_id = base_form.findField('Diag_id').getValue(),
			EvnDirectionHTM_setDate = base_form.findField('EvnDirectionHTM_setDate').getValue(),
			HTMedicalCareTypeCombo = base_form.findField('HTMedicalCareType_id'),
			HTMedicalCareType_id = HTMedicalCareTypeCombo.getValue(),
			HTMedicalCareTypeIdList = new Array();

		HTMedicalCareTypeCombo.clearValue();

		if ( !Ext.isEmpty(Diag_id) ) {
			this.HTMedicalCareDiagStore.each(function(rec) {
				if (
					rec.get('Diag_id') == Diag_id
					&& (
						Ext.isEmpty(EvnDirectionHTM_setDate)
						|| (
							(Ext.isEmpty(rec.get('HTMedicalCareDiag_begDate')) || rec.get('HTMedicalCareDiag_begDate') <= EvnDirectionHTM_setDate)
							&& (Ext.isEmpty(rec.get('HTMedicalCareDiag_endDate')) || rec.get('HTMedicalCareDiag_endDate') >= EvnDirectionHTM_setDate)
						)
					)
				) {
					HTMedicalCareTypeIdList.push(rec.get('HTMedicalCareType_id'));
				}
			});
		}

		HTMedicalCareTypeCombo.getStore().clearFilter();
		HTMedicalCareTypeCombo.lastQuery = '';
		HTMedicalCareTypeCombo.getStore().filterBy(function(rec) {
			return (
				rec.get('HTMedicalCareType_id').inlist(HTMedicalCareTypeIdList)
				&& (
					Ext.isEmpty(EvnDirectionHTM_setDate)
					|| (
						(Ext.isEmpty(rec.get('HTMedicalCareType_begDate')) || rec.get('HTMedicalCareType_begDate') <= EvnDirectionHTM_setDate)
						&& (Ext.isEmpty(rec.get('HTMedicalCareType_endDate')) || rec.get('HTMedicalCareType_endDate') >= EvnDirectionHTM_setDate)
					)
				)
			);
		});

		var index = HTMedicalCareTypeCombo.getStore().findBy(function(rec) {
			return (rec.get('HTMedicalCareType_id') == HTMedicalCareType_id);
		});

		if ( index >= 0 ) {
			HTMedicalCareTypeCombo.setValue(HTMedicalCareType_id);
		}
		HTMedicalCareTypeCombo.fireEvent('change', HTMedicalCareTypeCombo, HTMedicalCareTypeCombo.getValue());
	},
	checkKareliyaTFOMSFields: function () {
		var base_form = this.FormPanel.getForm(),
			efieds = [],
			tfieds = [
				'EvnDirectionHTM_Num_KD',
				'LpuHTM_id',
				'EvnDirectionHTM_planDate',
				'Diag_id',
				'LpuSectionProfile_id',
				'HTMedicalCareClass_id',
				'HTMedicalCareType_id'
			];
		for (var i = 0; i < tfieds.length; i++) {
			if (Ext.isEmpty(base_form.findField(tfieds[i]).getValue())) {
				efieds.push(base_form.findField(tfieds[i]).fieldLabel);
			}
		}
		if (efieds.length) {
			return efieds;
		} 
		return false;
	},
	loadInfoPanels: function() {
		var base_form = this.FormPanel.getForm();
		Ext.Ajax.request({
			url: '/?c=EvnDirectionHTM&m=loadInfoForEvnDirectionHTM',
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				Lpu_id: base_form.findField('Lpu_id').getValue()
			},
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(response_obj.data);

					base_form.findField('OrgDep_id').getStore().load({
						params: {
							Object:'OrgDep',
							OrgDep_id: base_form.findField('OrgDep_id').getValue(),
							OrgDep_Name: ''
						},
						callback: function() {
							base_form.findField('OrgDep_id').setValue(base_form.findField('OrgDep_id').getValue());
						}
					});
					var Org_id = (this.action == 'add') ? sw.Promed.MedStaffFactByUser.current.Org_id : base_form.findField('Org_id').getValue();
					base_form.findField('Org_id').getStore().load({
						params: {Org_id: Org_id},
						callback: function() {
							base_form.findField('Org_id').setValue(Org_id);
							this.setAllowBlankFields();
						}.createDelegate(this)
					});
				}
			}.createDelegate(this)
		});
	},
    generateNewNumber: function() {
	    var win = this;
        var base_form = this.FormPanel.getForm();
        var EvnDirectionHTM_setDate = base_form.findField('EvnDirectionHTM_setDate').getValue(),
            year = typeof EvnDirectionHTM_setDate == 'object' ? EvnDirectionHTM_setDate.format('Y') : new Date().format('Y');
        win.getLoadMask('Получение номера направления').show();

        Ext.Ajax.request({
            params: {
                year: year
            },
            callback: function (options, success, response) {
                win.getLoadMask().hide();
                if (success) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (response_obj.EvnDirection_Num) {
                        base_form.findField('EvnDirectionHTM_Num').setValue(response_obj.EvnDirection_Num);
                        base_form.findField('EvnDirectionHTM_Num_KD').setValue(response_obj.EvnDirection_Num);
                    }
                }
            }.createDelegate(this),
            url: '/?c=EvnDirection&m=getEvnDirectionNumber'
        });
    },
	show: function()
	{
		sw.Promed.swDirectionOnHTMEditForm.superclass.show.apply(this, arguments);

		var win = this;
		this.ARMType = null;
		this.dataSaved = false;

        this.onClose = (arguments[0] && arguments[0].onClose) || Ext.emptyFn;
        this.onSave = (arguments[0] && arguments[0].onSave) || Ext.emptyFn;
        this.withCreateDirection = (arguments[0] && arguments[0].withCreateDirection) || false;
		this.LpuSectionProfile_id = (arguments[0] && arguments[0].LpuSectionProfile_id) || null;

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.FileListPanel.FileGrid.removeAll();
		this.enableEdit(false);

		
		this.KDopInfoPanel.setVisible( !getRegionNick().inlist(['kz']) );

		if( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}

		if(arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		this.EvnDirection_pid = (arguments[0] && arguments[0].EvnDirection_pid) ? arguments[0].EvnDirection_pid : null;

		this.action = null;

		base_form.setValues(arguments[0]);

		base_form.findField('EvnDirectionHTM_directDate').setContainerVisible( getRegionNick() != 'kz' );
		base_form.findField('MedStaffFact_id').setAllowBlank(getRegionNick() == 'kz');
		base_form.findField('MedStaffFact_id').setContainerVisible( getRegionNick() != 'kz' );
		base_form.findField('EvnDirectionHTM_TalonNum').setContainerVisible( getRegionNick() != 'kz' );

		base_form.findField('MedStaffFact_id').setAllowBlank(this.ARMType == 'spec_mz' && getRegionNick() != 'ufa');
		base_form.findField('MedStaffFact_id').setContainerVisible(!(this.ARMType == 'spec_mz' && getRegionNick() != 'ufa'));

        base_form.findField('EvnDirectionHTM_planDate').setMinValue(undefined);

		if (arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			this.action = Ext.isEmpty(base_form.findField('EvnDirectionHTM_id').getValue())?'add':'edit';
		}
		
		this.EvnVK_setDT = null;
		if (arguments[0].EvnVK_setDT) {
			this.EvnVK_setDT = arguments[0].EvnVK_setDT;
		}

		base_form.findField('PrivilegeType_id').getStore().filterBy(function(rec){
			return (rec.get('PrivilegeType_Code').inlist([10,11,20,30,40,50,60,81,82,83,84,85,120]));
		});

		this.getLoadMask().show();

		this.buttons.find((item) => item.itemId == 'HTME_btnPrint').setVisible(this.action != 'add');

		switch(this.action) {
			case 'add':
				this.setTitle(lang['napravlenie_na_vmp_dobavlenie']);
				this.enableEdit(true);
				base_form.findField('EvnDirectionHTM_setDate').setValue(this.EvnVK_setDT); // по умолчанию дата экспертизы
                base_form.findField('EvnDirectionHTM_setDate').fireEvent('change', base_form.findField('EvnDirectionHTM_setDate'), base_form.findField('EvnDirectionHTM_setDate').getValue());
                base_form.findField('EvnDirectionHTM_directDate').fireEvent('change', base_form.findField('EvnDirectionHTM_directDate'), base_form.findField('EvnDirectionHTM_directDate').getValue());
				base_form.findField('EvnDirectionHTM_IsHTM').setValue(2); // по умолчанию "первично"

				if (!getRegionNick().inlist(['kz']))
					if (this.ARMType == 'spec_mz')
						base_form.findField('HTMOrgDirect_id').setFieldValue('HTMOrgDirect_Code', 2);   // по умолчанию "Минздравсоцразвития России"
					else
					{
						base_form.findField('HTMFinance_id').setFieldValue('HTMFinance_Code', 3);      // по умолчанию "ОМС"
						base_form.findField('HTMOrgDirect_id').setFieldValue('HTMOrgDirect_Code', 1);  // по умолчанию "ОУЗ"
					}
				
				base_form.findField('EvnDirectionHTM_Num_KD').setValue(base_form.findField('EvnDirectionHTM_Num').getValue());		
				if (getRegionNick().inlist(['kareliya', 'penza', 'ufa', 'perm', 'vologda'])) {
					base_form.findField('Region_id').store.load({params: {country_id: 643, level: 1, value: 0}, callback: function(){
						var region = getRegionNumber();
						base_form.findField('Region_id').setValue(region);
						base_form.findField('Region_id').fireEvent('change', base_form.findField('Region_id'), region);
					}});
				}
				
				var diag_id = base_form.findField('Diag_id').getValue();
				if ( diag_id ) {
					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(rec) {
								if ( rec.get('Diag_id') == diag_id ) {
									base_form.findField('Diag_id').setValue(diag_id);
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
									win.filterHTMedicalCareTypeCombo();
								}
							});

							var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
							if(
								diag_code >= 'C00' && diag_code <= 'C97'
								|| diag_code >= 'D00' && diag_code <= 'D09'
							) {
								base_form.findField('TreatmentType_id').clearValue();
							} else {
								base_form.findField('TreatmentType_id').setValue(5);
							}
						},
						params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
					});
				} else {
					this.filterHTMedicalCareTypeCombo();
				}

				if (getRegionNick() != 'penza') {
                    Ext.Ajax.request({
                        callback: function (options, success, response) {
                            if (success) {
                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                base_form.findField('EvnDirectionHTM_Num').setValue(response_obj.EvnDirectionHTM_Num);
                                base_form.findField('EvnDirectionHTM_Num_KD').setValue(response_obj.EvnDirectionHTM_Num);
                            }
                        }.createDelegate(this),
                        url: '/?c=EvnDirectionHTM&m=getEvnDirectionHTMNumber'
                    });
                }

				this.disableEvnDirectionFields();

				this.loadInfoPanels();

				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.enableEdit(true);
					this.setTitle(lang['napravlenie_na_vmp_redaktirovanie']);
				} else {
					this.setTitle(lang['napravlenie_na_vmp_prosmotr']);
				}

				base_form.load({
					failure:function () {
						//sw.swMsg.alert('Ошибка', 'Не удалось получить данные');
						this.getLoadMask().hide();
					}.createDelegate(this),
					url: '/?c=EvnDirectionHTM&m=loadEvnDirectionHTMForm',
					params: {EvnDirectionHTM_id: base_form.findField('EvnDirectionHTM_id').getValue()},
					success: function() {
						this.getLoadMask().hide();

						if (!getRegionNick().inlist(['kz']))
							this.filterHTMedicalCareClassCombo({ HTMFinance_Code: base_form.findField('HTMFinance_id').getFieldValue('HTMFinance_Code') });

						this.loadInfoPanels();

						this.FileListPanel.listParams = { Evn_id: base_form.findField('EvnDirectionHTM_id').getValue() };
						this.FileListPanel.loadData({ Evn_id: base_form.findField('EvnDirectionHTM_id').getValue() });

                        base_form.findField('EvnDirectionHTM_setDate').fireEvent('change', base_form.findField('EvnDirectionHTM_setDate'), base_form.findField('EvnDirectionHTM_setDate').getValue());
                        base_form.findField('EvnDirectionHTM_directDate').fireEvent('change', base_form.findField('EvnDirectionHTM_directDate'), base_form.findField('EvnDirectionHTM_directDate').getValue());

						base_form.findField('EvnDirectionHTM_Num_KD').setValue(base_form.findField('EvnDirectionHTM_Num').getValue());
                        if (getRegionNick().inlist(['kareliya', 'penza', 'ufa', 'perm', 'vologda'])) {
							var Region_id = base_form.findField('Region_id').getValue();
							base_form.findField('Region_id').store.load({params: {country_id: 643, level: 1, value: 0}, callback: function() {
								base_form.findField('Region_id').setValue(Region_id);
								base_form.findField('Region_id').fireEvent('change', base_form.findField('Region_id'), Region_id);
							}});
						}
						
						var diag_id = base_form.findField('Diag_id').getValue();
						if ( diag_id ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(rec) {
										if ( rec.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').setValue(diag_id);
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
											win.filterHTMedicalCareTypeCombo();
										}
									});
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
							});
						} else {
							this.filterHTMedicalCareTypeCombo();
						}

					}.createDelegate(this)
				});
			break;
		}
	},
	
	disableEvnDirectionFields: function(){
		var base_form = this.FormPanel.getForm();
		if (getRegionNick()!='kz'){
			if( !Ext.isEmpty( base_form.findField('EvnDirectionHTM_VKProtocolNum').getValue() ) ){
				base_form.findField('EvnDirectionHTM_VKProtocolNum').disable();
				base_form.findField('EvnDirectionHTM_VKProtocolDate').disable();
			}
		}
	},

	doSave: function(cb, options)
	{
		var win = this;
		var base_form = this.FormPanel.getForm();
		if(!base_form.isValid()) {
			if (getRegionNick() == 'penza') {
                var el = this.FormPanel.getFirstInvalidEl();
                if (el && el.fieldLabel) {
                    sw.swMsg.alert(lang['oshibka'], 'Поле "' + el.fieldLabel + '" обязательное для заполнения.');
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['zapolnenyi_ne_vse_obyazatelnyie_polya_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']);
                }
                return false;
			} else {
                sw.swMsg.alert(lang['oshibka'], lang['zapolnenyi_ne_vse_obyazatelnyie_polya_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']);
                return false;
            }
		}
		
		if ( typeof options != 'object' ) {
			options = {};
		}
		
		if (getRegionNick() == 'kareliya') {
			var cktf = this.checkKareliyaTFOMSFields()
			if (cktf !== false && !options.ignoreKareliyaTFOMS) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreKareliyaTFOMS = true;
							this.doSave(cb, options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Не указаны обязательные для выгрузки в ТФОМС поля:<br>' + cktf.join(', ') + '.<br><br>Продолжить сохранение?',
					title: 'Продолжить сохранение?'
				});
				return false;		
			}
		}

		if (getRegionNick().inlist(['kz']) && !this._ignoreTalonNumDateWrn &&
						(!base_form.findField('EvnDirectionHTM_TalonNum').getValue() ||
							!base_form.findField('EvnDirectionHTM_setDate').getValue()))
		{
			sw.swMsg.show({
				title: lang['vnimanie'],
				msg: lang['htm_num_date_warning'],
				icon: Ext.MessageBox.INFORMATION,
				buttons: Ext.Msg.OK,

				fn: function(buttonId, text, obj)
				{
					win._ignoreTalonNumDateWrn = true;
					win.doSave(cb, options);
				}
			});

			return (false);
		}

		var params = {};
		if (!Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue())) {
            params.MedPersonal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');
        } else {
            params.MedPersonal_id = getGlobalOptions().medpersonal_id || null;
        }
		if (this.withCreateDirection) {
			params.withCreateDirection = 1;
			params.LpuSectionProfile_id = this.LpuSectionProfile_id || null;
		}
		if (base_form.findField('HTMFinance_id').disabled) {
			params.HTMFinance_id = base_form.findField('HTMFinance_id').getValue();
		}
		var lm = this.getLoadMask(langs('Сохранение данных...'));
		lm.show();

		if (this.action == "add" && !getRegionNick().inlist(['kz']))
			base_form.findField('EvnStatus_id').setValue(36);  // Новое

		var fieldEditing = [
			'EvnDirectionHTM_VKProtocolNum', //Номер протокола ВК
			'EvnDirectionHTM_VKProtocolDate', //Дата протокола ВК
			'EvnDirectionHTM_Num_KD', //номер направления
			'EvnDirectionHTM_directDate', // Дата направления
			'Region_id', //Регион - Блок «МО, куда направляется пациент»
			'LpuHTM_id', //МО - Блок «МО, куда направляется пациент»
			'Diag_id', //Диагноз направления
			'LpuSectionProfile_id', //Профиль направления
			'HTMedicalCareType_id', //Вид ВМП
			'HTMedicalCareClass_id', //Метод ВМП
			'HTMOrgDirect_id', //Направление на ВМП
			'EvnDirectionHTM_IsHTM', //Обращение пациента за ВМП
			'MedStaffFact_id'
		]
		fieldEditing.forEach(function(item, i, arr){
			var el = base_form.findField(item);
			if(el && el.disabled) el.enable();
		});

		base_form.submit({
			params: params,
			success: function(frm,action){
				lm.hide();
				if(action.result.success) {
					this.FileListPanel.listParams = {Evn_id: action.result.EvnDirectionHTM_id};
					this.FileListPanel.saveChanges();

					if( cb ) {
						base_form.findField('EvnDirectionHTM_id').setValue(action.result.EvnDirectionHTM_id);
						this.withCreateDirection = false;
						cb();
					} else {
						var data = Ext.apply(frm.getValues(false),action.result);
						data.Evn_id = action.result.EvnDirectionHTM_id;
						win.onSave(data);
						win.dataSaved = true;
                        win.hide();
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], (action.result.Error_Msg)?action.result.Error_Msg:lang['ne_udalos_sohranit_dannyie']);
				}
			}.createDelegate(this),
			failure: function(){
				lm.hide();
				//sw.swMsg.alert('Ошибка', 'При сохранении данных произошла ошибка!');
			}
		});

	},
	loadMedStaffactCombo: function(){
		var win = this;
		var base_form = win.FormPanel.getForm();
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		
		if(med_staff_fact_id){
			swMedStaffFactGlobalStore.load({
				params: {MedStaffFact_id: med_staff_fact_id, Lpu_id: base_form.findField('Lpu_id').getValue()},
				callback: function(r, o, success) {
					if ( success === false || !r || r.length == 0 )
					{
						if ( iteration >= 3 ) {
							setPromedInfo(langs('Справочник врачей не загружен или пуст'), 'medpersonal-info');
						}
						else
							doLoadMedStaffFactGlobalStore(iteration + 1);
					} else {
						setPromedInfo('', 'medpersonal-info');
						this.base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						this.base_form.findField('MedStaffFact_id').setValue(this.med_staff_fact_id);
					}
				}.createDelegate({base_form: base_form, med_staff_fact_id: med_staff_fact_id})
			});
			return false;
		}
	},
	setAllowBlankFields: function(){
		if(getRegionNick() == 'kz') return false;
		var win = this;
		var base_form = win.FormPanel.getForm();
		var Org_id = base_form.findField('Org_id').getValue();
		if(!Org_id) return false;
		var userOrg_id = getGlobalOptions().org_id;
		var own_organization = (Org_id == userOrg_id) ? true : false;
		// own_organization=true - «Наименование ОУЗ» совпадает с наименованием МО, в которой авторизовался пользователь (направляющая сторона)
		// иначе принимающая сторона
		var allowBlankFieldsArr = ['EvnDirectionHTM_setDate','EvnDirectionHTM_TalonNum']; //дата оформления талона, номер талона
		var fieldEditing = [
			'EvnDirectionHTM_VKProtocolNum', //Номер протокола ВК
			'EvnDirectionHTM_VKProtocolDate', //Дата протокола ВК
			'EvnDirectionHTM_Num_KD', //номер направления
			'EvnDirectionHTM_directDate', // Дата направления
			'Region_id', //Регион - Блок «МО, куда направляется пациент»
			'LpuHTM_id', //МО - Блок «МО, куда направляется пациент»
			'Diag_id', //Диагноз направления
			'LpuSectionProfile_id', //Профиль направления
			'HTMedicalCareType_id', //Вид ВМП
			'HTMedicalCareClass_id', //Метод ВМП
			'HTMOrgDirect_id', //Направление на ВМП
			'EvnDirectionHTM_IsHTM'//Обращение пациента за ВМП
		]
		allowBlankFieldsArr.forEach(function(item, i, arr){
			var el = base_form.findField(item);
			if(el){
				el.setAllowBlank(own_organization);
			}
		});
		fieldEditing.forEach(function(item, i, arr){
			var el = base_form.findField(item);
			if(el){
				el.setAllowBlank(!own_organization);
				if(own_organization){
					el.enable();
				}else{
					el.disable();
				}
			}
		});
		base_form.findField('MedStaffFact_id').setAllowBlank(!own_organization);
		base_form.findField('MedStaffFact_id').setAllowBlank(this.ARMType == 'spec_mz' && getRegionNick() != 'ufa');
		base_form.findField('MedStaffFact_id').setContainerVisible(!(this.ARMType == 'spec_mz' && getRegionNick() != 'ufa'));
		
		if(!own_organization) {
			base_form.findField('MedStaffFact_id').disable();
			this.loadMedStaffactCombo();
		}else{
			base_form.findField('MedStaffFact_id').enable();
		}
		if(this.action == 'add'){
			base_form.findField('EvnDirectionHTM_setDate').setValue();
			
			this.disableEvnDirectionFields();
			
			// Если была выбрана служба #101026
			if( win.lastArguments.Lpu_f003mcod && own_organization ){
				var Lpu_f003mcod = win.lastArguments.Lpu_f003mcod;
				var LpuHTMcombo = base_form.findField('LpuHTM_id');

				var index = LpuHTMcombo.getStore().findBy((function(rec) {
					return (rec.get('LpuHTM_f003mcod') == Lpu_f003mcod);
				}))
				if( index >= 0){
					LpuHTMcombo.setValue(LpuHTMcombo.getStore().getAt(index).get(LpuHTMcombo.valueField));
				}else{
					LpuHTMcombo.setAllowBlank(true);
					base_form.findField('Region_id').setAllowBlank(true);
				}
				
			}

			
		} 

	},
	initComponent: function()
	{
		var win = this;
		this.PersonInfoPanel = new sw.Promed.Panel({
			title: lang['spravochnyie_svedeniya_o_patsiente'],
			id: 'DOHEF_PersonInfoPanel',
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			labelWidth: 130,
			items: [{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SurName',
						fieldLabel: lang['familiya'],
						width: 140
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_FirName',
						fieldLabel: lang['imya'],
						width: 140
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SecName',
						fieldLabel: lang['otchestvo'],
						width: 140
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'swpersonsexcombo',
						hiddenName: 'Sex_id',
						fieldLabel: lang['pol'],
						width: 140
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'swdatefield',
						name: 'Person_BirthDay',
						fieldLabel: lang['data_rojdeniya'],
						width: 140
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'swsnilsfield',
						name: 'Person_Snils',
						fieldLabel: lang['snils'],
						fieldWidth: 140
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'sworgsmocombo',
						hiddenName: 'OrgSMO_id',
						fieldLabel: lang['naimenovanie_smo'],
						width: 415
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						name: 'Polis_Num',
						fieldLabel: lang['nomer_polisa_oms'],
						width: 140
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					labelWidth: 225,
					items: [{
						xtype: 'swdocumenttypecombo',
						hiddenName: 'DocumentType_id',
						fieldLabel: lang['dokument_udostoveryayuschiy_lichnost'],
						width: 595
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						name: 'Document_Ser',
						fieldLabel: lang['seriya_dokumenta'],
						width: 140
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						name: 'Document_Num',
						fieldLabel: lang['nomer_dokumenta'],
						width: 140
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'sworgdepcombo',
						hiddenName: 'OrgDep_id',
						fieldLabel: lang['kem_i_kogda_vyidan_dokument'],
						width: 415
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 1,
					items: [{
						xtype: 'swdatefield',
						name: 'Document_begDate',
						fieldLabel: '',
						labelSeparator: ''
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'PersAddress_AddressText',
						fieldLabel: lang['adres_projivaniya'],
						width: 690
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_Phone',
						fieldLabel: lang['kontaktnyiy_telefon'],
						width: 140
					}]
				}, {
					border: false,
					layout: 'form',
					items: {
						xtype: 'swbaselocalcombo',
						hiddenName: 'PlaceKind_id',
						valueField: 'PlaceKind_id',
						displayField: 'PlaceKind_Name',
						fieldLabel: lang['jitel_gorod_selo'],
						store: new Ext.data.SimpleStore({
							autoLoad: false,
							fields: [
								{name: 'PlaceKind_id', type: 'int'},
								{name: 'PlaceKind_Name', type: 'string'}
							],
							key: 'PlaceKind_id',
							data: [
								[1, lang['gorod']], [2, lang['selo']]
							]
						}),
						width: 140
					}
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'swprivilegetypecombo',
						hiddenName: 'PrivilegeType_id',
						fieldLabel: lang['kategoriya_lgotyi'],
						lastQuery: '',
						width: 270
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 145,
					items: [{
						xtype: 'swhtmsocgroupcombo',
						hiddenName: 'HTMSocGroup_id',
						fieldLabel: lang['sotsialnaya_gruppa'],
						width: 270
					}]
				}]
			}]
		});

		this.OrgInfoPanel = new sw.Promed.Panel({
			title: lang['pasportnaya_chast_talona'],
			id: 'DOHEF_OrgInfoPanel',
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			labelWidth: 130,
			items: [{
				border: false,
				layout: 'form',
				items: [{
					xtype: 'sworgcombo',
					hiddenName: 'Org_id',
					fieldLabel: lang['naimenovanie_ouz'],
					width: 415
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						name: 'Org_OKPO',
						fieldLabel: lang['okpo_ouz'],
						width: 140
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						name: 'Org_OKATO',
						fieldLabel: lang['okato_ouz'],
						width: 140
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'OrgAddress_Index',
						fieldLabel: lang['pochtovyiy_indeks_ouz'],
						width: 140
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'OrgAddress_AddressText',
						fieldLabel: lang['pochtovyiy_adres_ouz'],
						width: 415
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Org_Email',
						fieldLabel: lang['adres_elektronnoy_pochtyi_ouz'],
						width: 270
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
// 						allowBlank: false,
						xtype: 'swdatefield',
						name: 'EvnDirectionHTM_setDate',
						fieldLabel: lang['data_oformleniya_talona'],
						width: 140,
						listeners: {
							'change': function(combo, newValue, oldValue) {
							    var base_form = win.FormPanel.getForm();
								var RegionCombo = base_form.findField('Region_id');
								RegionCombo.fireEvent('change', RegionCombo, RegionCombo.getValue());
								win.filterHTMedicalCareTypeCombo();

								var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
								
								if(getRegionNick() != 'kz' && base_form.findField('Org_id').getValue() && base_form.findField('Org_id').getValue() != getGlobalOptions().org_id){
									win.loadMedStaffactCombo();
									return false;
								}
                                //base_form.findField('MedStaffFact_id').clearValue();
                                if (!newValue) {
                                    setMedStaffFactGlobalStoreFilter({
                                        allowDuplacateMSF: true
                                    });
                                } else {
                                    setMedStaffFactGlobalStoreFilter({
                                        allowDuplacateMSF: true,
                                        onDate: Ext.util.Format.date(newValue, 'd.m.Y')
                                    });
                                }
                                base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
                                if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
                                    base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
                                }
							}
						}
					}]
				}, {
					hidden: getRegionNick().inlist(['kz']),
					border: false,
					layout: 'form',
					labelWidth: 140,
					items: [{
						allowBlank: getRegionNick().inlist(['kz']),
						xtype: 'textfield',
						name: 'EvnDirectionHTM_VKProtocolNum',
						fieldLabel: 'Номер протокола ВК',
						maxLength: 20,
						width: 150
					}]
				}, {
					hidden: getRegionNick().inlist(['kz']),
					border: false,
					layout: 'form',
					labelWidth: 140,
					items: [{
						allowBlank: getRegionNick().inlist(['kz']),
						xtype: 'swdatefield',
						name: 'EvnDirectionHTM_VKProtocolDate',
						fieldLabel: 'Дата протокола ВК',
						width: 100
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 200,
					items: [{
						allowBlank: false,
						xtype: 'swbaselocalcombo',
						editable: false,
						hiddenName: 'EvnDirectionHTM_IsHTM',
						valueField: 'YesNo_id',
						displayField: 'YesNo_Name',
						fieldLabel: lang['obraschenie_patsienta_za_vmp'],
						store: new Ext.data.SimpleStore({
							autoLoad: false,
							fields: [
								{name: 'YesNo_id', type: 'int'},
								{name: 'YesNo_Name', type: 'string'},
								{name: 'YesNo_Code', type: 'int'}
							],
							key: 'YesNo_id',
							data: [
								[2, lang['pervichnoe'], 1], [1, lang['vtorichnoe'], 0]
							]
						}),
						width: 140
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMFinance',
						hiddenName: 'HTMFinance_id',
						fieldLabel: lang['istochnik_finansirovaniya'],
						width: 270,
						listeners:
						{
							change: function(combo, newValue, oldValue) {
								if (!getRegionNick().inlist(['ufa', 'kz']))
									win.filterHTMedicalCareClassCombo({ HTMFinance_Code: combo.getFieldValue('HTMFinance_Code') });
							}
						}
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 145,
					items: [{
						allowBlank: false,
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMOrgDirect',
						hiddenName: 'HTMOrgDirect_id',
						fieldLabel: lang['napravlenie_na_vmp'],
						width: 270
					}]
				}]
			}]
		});

		var Num_KD_MaxLength = getRegionNick().inlist(['kareliya', 'ufa']) ? 5 : 6;

		this.KDopInfoPanel = new sw.Promed.Panel({
			id: 'DOHEF_KDopInfoPanel',
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			labelWidth: 130,
			items: [{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
                        layout: 'column',
                        border: false,
                        autoHeight: true,
                        items: [{
                            layout: 'form',
                            width: 250,
                            border: false,
                            items: [{
                                allowBlank: getRegionNick() == 'kz',
                                name: 'EvnDirectionHTM_Num_KD',
                                xtype: 'numberfield',
                                fieldLabel: 'Номер направления',
                                anchor: '100%',
                                maxLength: Num_KD_MaxLength,
                                autoCreate: {
                                    tag: "input",
                                    type: "text",
                                    maxLength: Num_KD_MaxLength,
                                    autocomplete: "off"
                                },
                                listeners: {
                                    'change': function(combo, newValue, oldValue) {
                                        win.FormPanel.getForm().findField('EvnDirectionHTM_Num').setValue(newValue);
                                    }
                                }
                            }]
                        }, {
                            layout: 'form',
                            hidden: getRegionNick() == 'kz',
                            border: false,
                            style: 'float: none',
                            items: [{
                                text: '+',
                                id: win.id + 'gennewnumber',
                                xtype: 'button',
                                handler: function() {
                                    win.generateNewNumber();
                                }
                            }]
                        }]
                    }, {
						border: false,
						layout: 'form',
						hidden: (getRegionNick() == 'kz'),
						items:
							[{
								allowBlank: false,
								xtype: 'swdatefield',
								name: 'EvnDirectionHTM_directDate',
								fieldLabel: 'Дата направления',
								listeners:
								{
									change: function(combo, newValue, oldValue) {
										if (!getRegionNick().inlist(['kz']))
											win.filterHTMedicalCareClassCombo({ EvnDirectionHTM_directDate: newValue });
									}
								}
							}]
                    },
                    {
                     border: false,
                     layout: 'form',
                     hidden: (getRegionNick() == 'kz'),
                     items:
                      [{
                        allowBlank: (this.ARMType == 'spec_mz' && getRegionNick() != 'ufa'),
                        xtype: 'swmedstafffactglobalcombo',
                        hidden: (this.ARMType == 'spec_mz' && getRegionNick() != 'ufa'),
                        hiddenName: 'MedStaffFact_id',
                        fieldLabel: 'Врач, выписавший направление',
                        listWidth: 400,
                        width: 250
                       }]
                    }, {
                     border: false,
                     layout: 'form',
                     hidden: (getRegionNick() == 'kz'),
                     items:
                      [{
//                         allowBlank: false,
                        name: 'EvnDirectionHTM_TalonNum',
                        xtype: 'textfield',
                        fieldLabel: 'Номер талона',
                        width: 250,
                        maxLength: 17,
                        autoCreate: {
                            tag: "input",
                            type: "text",
                            maxLength: 17,
                            autocomplete: "off"
                        }
                       }]
                    }, {
						xtype: 'fieldset',
						autoHeight: true,
						style: 'padding: 10px; margin: 0 0 10px',
						title: 'МО, куда направляется пациент',
						labelWidth: 120,
						border: true,
						items: [{
							minChars: 0,
							queryDelay: 1,
							xtype: 'swregioncombo',
							hiddenName: 'Region_id',
							fieldLabel: 'Регион',
                            allowBlank: getRegionNick().inlist(['kz']),
							width: 250,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.FormPanel.getForm(),
										LpuHTMcombo = base_form.findField('LpuHTM_id'),
										LpuHTM_id = LpuHTMcombo.getValue();
									LpuHTMcombo.clearValue();
									LpuHTMcombo.getStore().removeAll();
									if (newValue > 0) {

										var filterOnDate = Ext.util.Format.date(base_form.findField('EvnDirectionHTM_setDate').getValue(),'d.m.Y');

										// 2)	Регион: Пенза, Карелия. Доработать поле «МО» в блоке «МО, куда направляется пациент»
										// формы «Направление на ВМП» в соответствии с п. 2.7. В выпадающем списке должны выводиться
										// только те МО, у которых на Дату направления действует лицензия (поля LpuHTM_begDate, LpuHTM_endDate).
										if (getRegionNick() == 'penza' || getRegionNick() == 'kareliya') {

											filterOnDate = Ext.util.Format.date(base_form.findField('EvnDirectionHTM_directDate').getValue(),'d.m.Y');
										}

										LpuHTMcombo.getStore().load({
											params: {
												Region_id: newValue,
												onDate: filterOnDate
											},
											callback: function() {
												var index = LpuHTMcombo.getStore().findBy(function(rec) {
													return (rec.get('LpuHTM_id') == LpuHTM_id);
												});										
												if (index >= 0) {
													LpuHTMcombo.setValue(LpuHTM_id);
													LpuHTMcombo.fireEvent('select', LpuHTMcombo, LpuHTMcombo.getStore().getAt(index), index);
												}
											}
										});
									}
								}
							}
						}, {
							hiddenName: 'LpuHTM_id',
                            allowBlank: getRegionNick().inlist(['kz']),
							xtype: 'swlpuhtmcombo',
							width: 250,
							listeners: {
								'change': function(combo, newValue, oldValue){
									var base_form = win.FormPanel.getForm();
								}
							}
						}]
					}, {
						name: 'EvnDirectionHTM_planDate',
						xtype: 'swdatefield',
                        allowBlank: (['penza', 'ufa'].indexOf(getRegionNick()) < 0),
						fieldLabel: 'Планируемая дата поступления / госпитализации'
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 100,
     				hidden: (getRegionNick() == 'kz'),
					items: [{
						name: 'Diag_id',
                        allowBlank: getRegionNick().inlist(['kz']),
						xtype: 'swdiagcombo',
						fieldLabel: 'Диагноз направления',
						onChange: function (combo, value) {
							this.filterHTMedicalCareTypeCombo();
							
							var base_form = win.FormPanel.getForm();
							var diag_code = combo.getFieldValue('Diag_Code');

							if(
								diag_code >= 'C00' && diag_code <= 'C97'
								|| diag_code >= 'D00' && diag_code <= 'D09'
							) {
								base_form.findField('TreatmentType_id').clearValue();
							} else {
								base_form.findField('TreatmentType_id').setValue(5);
							}

							base_form.findField('TreatmentType_id')
						}.createDelegate(this),
						width: 340
					}, {
						name: 'LpuSectionProfile_id',
						allowBlank: getRegionNick().inlist(['kz']),
						hidden: (getRegionNick() == 'kz'),
						xtype: 'swlpusectionprofilecombo',
						fieldLabel: 'Профиль направления',
						listWidth: 500,
						width: 340
					}, {
						hiddenName: 'HTMedicalCareType_id',
						allowBlank: getRegionNick().inlist(['kz']),
						hidden: (getRegionNick() == 'kz'),
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMedicalCareType',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								win.filterHTMedicalCareClassCombo({ HTMedicalCareType_id: newValue });
							},
							'select': function(combo, record, index) {
								combo.fireEvent('change', combo, record.get(combo.valueField));
							}
						},
						moreFields: [
							{ name: 'HTMedicalCareType_begDate', type: 'date', dateFormat: 'd.m.Y' },
							{ name: 'HTMedicalCareType_endDate', type: 'date', dateFormat: 'd.m.Y' },
							{ name: 'HTMedicalCareType_id', type: 'int' }
						],
						fieldLabel: 'Вид ВМП',
						listWidth: 600,
						width: 340
					}, {
						hiddenName: 'HTMedicalCareClass_id',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMedicalCareClass',
						allowBlank: getRegionNick().inlist(['kz']),
						hidden: (getRegionNick() == 'kz'),
						moreFields: [
							{ name: 'HTMedicalCareClass_begDate', type: 'date', dateFormat: 'd.m.Y' },
							{ name: 'HTMedicalCareClass_endDate', type: 'date', dateFormat: 'd.m.Y' },
							{ name: 'HTMedicalCareClass_fid', type: 'int' },
							{ name: 'HTMedicalCareType_id', type: 'int' }
						],
						fieldLabel: 'Метод ВМП',
						listWidth: 500,
						width: 340
					}, {
						comboSubject: 'TreatmentType',
						prefix: 'r58_',
						allowBlank: getRegionNick() != 'penza',
						hidden: getRegionNick() != 'penza',
						hideLabel: getRegionNick() != 'penza',
						hiddenName: 'TreatmentType_id',
						fieldLabel: 'Тип предстоящего лечения',
						xtype: 'swcommonsprcombo'
					}, {
                        hiddenName: 'PrehospType_did',
                        loadParams: {params: {where: ' where PrehospType_id IN (1,2)'}},
                        allowBlank: (['penza', 'ufa'].indexOf(getRegionNick()) < 0),
						hidden: (getRegionNick() == 'kz'),
						comboSubject: 'PrehospType',
                        xtype: 'swcommonsprcombo',
                        fieldLabel: 'Вид госпитализации',
                        width: 340
					}, {
						name: 'PersonInfo_Email',
						width: 340,
						fieldLabel: 'Адрес электронной почты пациента',
						xtype: 'textfield',
						listeners: {
							render: function(field) {
								if (getRegionNick().inlist(['perm'])) {
									field.hideContainer();
								}
							}
						},
						maxLength: 40
					}]
				}]
			}]
		});

		this.FormPanel = new Ext.form.FormPanel({
			//region: 'center',
			labelAlign: 'right',
			border: false,
			id: 'DOHEF_FormPanel',
			url: '/?c=EvnDirectionHTM&m=saveEvnDirectionHTM',
			items: [
				{
					xtype: 'hidden',
					name: 'EvnDirectionHTM_id'
				}, {
					xtype: 'hidden',
					name: 'EvnDirectionHTM_pid'
				}, {
					xtype: 'hidden',
					name: 'EvnDirection_pid'
				}, {
					xtype: 'hidden',
					name: 'EvnLink_id'
				}, {
					xtype: 'hidden',
					name: 'Person_id'
				}, {
					xtype: 'hidden',
					name: 'PersonEvn_id'
				}, {
					xtype: 'hidden',
					name: 'Server_id'
				}, {
					xtype: 'hidden',
					name: 'Lpu_did'
				}, {
					xtype: 'hidden',
					name: 'LpuUnit_did'
				}, {
					xtype: 'hidden',
					name: 'LpuSection_did'
				}, {
					xtype: 'hidden',
					name: 'MedService_id'
				}, {
					xtype: 'hidden',
					name: 'EvnDirectionHTM_Num'
				}, {
					xtype: 'hidden',
					name: 'LpuSection_id'
				}, {
					xtype: 'hidden',
					name: 'MedPersonal_id'
				}, {
					xtype: 'hidden',
					name: 'Lpu_id'
				}, {
					xtype: 'hidden',
					name: 'TimetableMedService_id'
				},
				{
					xtype: 'hidden',
					name: 'EvnStatus_id'
				},
				this.PersonInfoPanel,
				this.OrgInfoPanel,
				this.KDopInfoPanel
			],
			reader: new Ext.data.JsonReader({
				success: function(){}
			},
			[
				{ name: 'EvnDirectionHTM_id' },
				{ name: 'EvnDirectionHTM_pid' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'Lpu_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'LpuSection_id' },
				{ name: 'Lpu_did' },
				{ name: 'LpuUnit_did' },
				{ name: 'LpuSection_did' },
				{ name: 'MedService_id' },
				{ name: 'TimetableMedService_id' },
				{ name: 'PrivilegeType_id' },
				{ name: 'HTMSocGroup_id' },
				{ name: 'EvnDirectionHTM_setDate' },
				{ name: 'EvnDirectionHTM_VKProtocolNum' },
				{ name: 'EvnDirectionHTM_VKProtocolDate' },
				{ name: 'EvnDirectionHTM_IsHTM' },
				{ name: 'EvnDirectionHTM_Num' },
				{ name: 'HTMFinance_id' },
				{ name: 'HTMOrgDirect_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'EvnDirectionHTM_planDate' },
				{ name: 'Diag_id' },
				{ name: 'Region_id' },
				{ name: 'LpuHTM_id' },
				{ name: 'EvnDirectionHTM_directDate' },
				{ name: 'MedStaffFact_id' },
				{ name: 'EvnDirectionHTM_TalonNum' },
				{ name: 'PrehospType_did' },
				{ name: 'TreatmentType_id' },
				{ name: 'HTMedicalCareType_id' },
				{ name: 'HTMedicalCareClass_id' },
				{ name: 'PersonInfo_Email' },
				{ name: 'EvnStatus_id' },
				{ name: 'EvnDirection_pid' },
				{ name: 'EvnLink_id' }
			])
		});

		this.FileListPanel = new sw.Promed.FileList({
			saveOnce: false,
			id: 'DOHEF_FileList',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});

		this.FilePanel = new sw.Promed.Panel({
			title: lang['elektronnyie_kopii_dokumentov'],
			id: 'DOHEF_FilePanel',
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			items: [this.FileListPanel],
			listeners: {
				'expand':function(panel){
					this.FileListPanel.doLayout();
				}.createDelegate(this)
			}
		});

		Ext.apply(this,
		{
			items: [this.FormPanel, this.FilePanel]
		});
		sw.Promed.swDirectionOnHTMEditForm.superclass.initComponent.apply(this, arguments);
	},

/******* filterHTMedicalCareClassCombo ****************************************
	*
	******************************************************************************/
	filterHTMedicalCareClassCombo: function(params)
	{
		if (this.action == 'view')
			return;

		var me = this,
			baseForm = this.FormPanel.getForm(),
			HTMedicalCareClassCombo = baseForm.findField('HTMedicalCareClass_id'),
			maskEl,
			doFilterStore = false,
			doRequest = false;

		if (params.hasOwnProperty('HTMedicalCareType_id') &&
			params.HTMedicalCareType_id != this._curHTMedicalCareType_id)
		{
			this._curHTMedicalCareType_id = params.HTMedicalCareType_id;
			doFilterStore = true;
		}

		if (params.hasOwnProperty('HTMFinance_Code') &&
			params.HTMFinance_Code != this._curHTMFinance_Code)
		{
			this._curHTMFinance_Code = params.HTMFinance_Code;
			doRequest = true;
		}

		if (params.hasOwnProperty('EvnDirectionHTM_directDate') &&
			params.EvnDirectionHTM_directDate != this._curEvnDirectionHTM_directDate &&
			(!params.EvnDirectionHTM_directDate || !this._curEvnDirectionHTM_directDate ||
			params.EvnDirectionHTM_directDate.valueOf() != this._curEvnDirectionHTM_directDate.valueOf()))
		{
			this._curEvnDirectionHTM_directDate = params.EvnDirectionHTM_directDate;
			doRequest = true;
		}

		if (doRequest)
		{
			(maskEl = HTMedicalCareClassCombo.ownerCt) && (maskEl = maskEl.getEl());

			if (maskEl)
				maskEl.mask().applyStyles({
					'background-image': 'url(/css/themes/blue/images/grid/loading.gif)',
					'background-repeat': 'no-repeat',
					'background-position': 'center'
				});

			if (this._htmcclRequestId)
				Ext.Ajax.abort(this._htmcclRequestId);

			this._htmcclRequestId =
				Ext.Ajax.request({
					url: '/?c=HTMedicalCare&m=loadHTMedicalCareClassListByHTFinance',

					params: {
						HTMFinance_Code: this._curHTMFinance_Code,

						endDate: this._curEvnDirectionHTM_directDate ?
							Ext.util.Format.date(this._curEvnDirectionHTM_directDate, 'd.m.Y') : undefined
					},

					callback: onLoad_medicalCareClassLink,
					scope: this
				});
		}
		else
			if (doFilterStore && !this._htmcclRequestId)
				filterStore();

/******* filterStore **********************************************************
*
*/
		function filterStore()
		{
			var HTMedicalCareClass_id = HTMedicalCareClassCombo.getValue();

			HTMedicalCareClassCombo.getStore().clearFilter();
			HTMedicalCareClassCombo.lastQuery = '';
			HTMedicalCareClassCombo.getStore().filterBy(filterFn);

			if (HTMedicalCareClass_id &&
				HTMedicalCareClassCombo.getStore().find('HTMedicalCareClass_id', HTMedicalCareClass_id) == -1)
				HTMedicalCareClass_id = null;

			HTMedicalCareClassCombo.setValue(HTMedicalCareClass_id);
		}

/******* filterFn *************************************************************
*
*/
		function filterFn(record)
		{
			return ((!me._curHTMedicalCareType_id ||
				me._curHTMedicalCareType_id == record.get('HTMedicalCareType_id')) &&
				(!me._htMedicalCareClassLink_ids ||
				me._htMedicalCareClassLink_ids.includes(record.get('HTMedicalCareClass_id'))));
		}

/******* onLoad_medicalCareClassLink ******************************************
*
*/
		function onLoad_medicalCareClassLink(options, success, response)
		{
			me._htmcclRequestId = null;

			if (success)
			{
				var responseObj = Ext.util.JSON.decode(response.responseText);

				if (Array.isArray(responseObj))
					me._htMedicalCareClassLink_ids = responseObj.map(item => Number.parseInt(item.HTMedicalCareClass_id));

				filterStore();
			}

			if (maskEl)
				maskEl.unmask();
		}
	},

/******* filterHTMedicalCareClassCombo ****************************************
 *
 ******************************************************************************/
 printDirection: function(params)
  {
   printBirt({
              'Report_FileName': 'printEvnDirection.rptdesign',
              'Report_Params': '&paramEvnDirection=' + this.FormPanel.getForm().findField('EvnDirectionHTM_id').getValue(),
              'Report_Format': 'pdf'
             });
  }
});
