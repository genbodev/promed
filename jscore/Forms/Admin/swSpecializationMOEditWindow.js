/**
 * swSpecializationMOEditWindow - окно редактирования/добавления cпециализации организации.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @version      05.10.2011
 */

sw.Promed.swSpecializationMOEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 400,
	layout: 'form',
	id: 'SpecializationMOEditWindow',
	listeners:
	{
		hide: function()
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	doSave: function()
	{
		var form = this.findById('SpecializationMOEditForm');
		if ( !form.getForm().isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						form.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function()
	{
		var form = this.findById('SpecializationMOEditForm');
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit(
			{
				params:
				{
					action: current_window.action
				},
				failure: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
						}
					}
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.SpecializationMO_id || action.result.SpecializationMOPacs_id)
						{
							current_window.hide();
							Ext.getCmp('LpuPassportEditWindow').findById('LPEW_SpecializationMOGrid').loadData();
						}
						else
						{
							sw.swMsg.show(
								{
									buttons: Ext.Msg.OK,
									fn: function()
									{
										form.hide();
									},
									icon: Ext.Msg.ERROR,
									msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
									title: lang['oshibka']
								});
						}
					}
				}
			});
	},
	/*Mkb10CodeStoreFilter: function(value) {
	   var form = Ext.getCmp('SpecializationMOEditForm').getForm();
		//log(form.findField('Mkb10Code_id').getStore());
		//Фильтруем сторе
		var Mkb10Code_id = form.findField('Mkb10Code_id').getValue();
		if (!Ext.isEmpty(value) && (this.action != 'view')) {
			form.findField('Mkb10Code_id').enable();
			form.findField('Mkb10Code_id').getStore().filterBy(function (rec) {
				if (value == rec.get('Mkb10Code_pid')) {
					log('Яростно фильтруем сторе');
					return true;
				} else {
					return false;
				}
			});
		} else {
			form.findField('Mkb10Code_id').disable();
		}
		//Проверяем наличие старого значения в новой выборке
		var index = form.findField('Mkb10Code_id').getStore().findBy(function(rec) {
			if (rec.get('Mkb10Code_id') == Mkb10Code_id) {
				return true;
			}
			else {
				return false;
			}
		});

		if (index >=0) {
			return true;
		} else {
			form.findField('Mkb10Code_id').setValue('');
		}
	},*/
	/*Mkb10CodeSubclassStoreFilter: function(value) {
		var form = Ext.getCmp('SpecializationMOEditForm').getForm();
		//Фильтруем сторе
		var Mkb10CodeSubClass_id = form.findField('Mkb10CodeSubClass_id').getValue();
		if (!Ext.isEmpty(value) && (this.action != 'view')) {
			form.findField('Mkb10CodeSubClass_id').enable();
			form.findField('Mkb10CodeSubClass_id').getStore().filterBy(function (rec) {
				if (value == rec.get('Mkb10CodeSubClass_pid')) {
					return true;
				} else {
					return false;
				}
			});
		} else {
			form.findField('Mkb10CodeSubClass_id').disable();
		}
		//Проверяем наличие старого значения в новой выборке
		var index = form.findField('Mkb10CodeSubClass_id').getStore().findBy(function(rec) {
			if (rec.get('Mkb10CodeSubClass_id') == Mkb10CodeSubClass_id) {
				return true;
			}
			else {
				return false;
			}
		});

		if (index >= 0) {
			return true;
		} else {
			form.findField('Mkb10CodeSubClass_id').setValue('');
		}
	},*/
	show: function()
	{
		sw.Promed.swSpecializationMOEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		if (!arguments[0])
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}

		this.focus();
		this.findById('SpecializationMOEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].SpecializationMO_id)
			this.SpecializationMO_id = arguments[0].SpecializationMO_id;
		else
			this.SpecializationMO_id = null;

		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else
			this.Lpu_id = null;

		/*if (arguments[0].Mkb10Code_id)
			this.Mkb10Code_id = arguments[0].Mkb10Code_id;
		else
			this.Mkb10Code_id = null;*/

		if (arguments[0].Mkb10CodeClass_id)
			this.Mkb10CodeClass_id = arguments[0].Mkb10CodeClass_id;
		else
			this.Mkb10CodeClass_id = null;

		if (arguments[0].SpecializationMO_MedProfile)
			this.SpecializationMO_MedProfile = arguments[0].SpecializationMO_MedProfile;
		else
			this.SpecializationMO_MedProfile = null;

		if (arguments[0].LpuLicence_id)
			this.LpuLicence_id = arguments[0].LpuLicence_id;
		else
			this.LpuLicence_id = null;
/*
		if (arguments[0].SpecializationMO_IsDepAftercare)
			this.SpecializationMO_IsDepAftercare = arguments[0].SpecializationMO_IsDepAftercare;
		else
			this.SpecializationMO_IsDepAftercare = null;
*/
		if (arguments[0].callback)
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner)
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		}
		else
		{
			if ( ( this.SpecializationMO_id ) && ( this.SpecializationMO_id > 0 ) )
				this.action = "edit";
			else
				this.action = "add";
		}
		var form = this.findById('SpecializationMOEditForm');
		var base_form = form.getForm();

		//base_form.findField('Mkb10CodeSubClass_id').getStore().clearFilter();
		//base_form.findField('Mkb10Code_id').getStore().clearFilter();

		base_form.setValues(arguments[0]);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['spetsializatsiya_organizatsii_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['spetsializatsiya_organizatsii_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['spetsializatsiya_organizatsii_prosmotr']);
				this.enableEdit(false);
				break;
		}

		if (this.action != 'add')
		{
			form.getForm().load({
				params:
				{
					SpecializationMO_id: current_window.SpecializationMO_id,
					//Mkb10Code_id: current_window.Mkb10Code_id,
					Mkb10CodeClass_id: current_window.Mkb10CodeClass_id,
					SpecializationMO_MedProfile: current_window.SpecializationMO_MedProfile,
					LpuLicence_id: current_window.LpuLicence_id,
					//SpecializationMO_IsDepAftercare: current_window.SpecializationMO_IsDepAftercare,
					Lpu_id: current_window.Lpu_id
				},
				failure: function(f, o, a)
				{
					loadMask.hide();
					sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function()
							{
								current_window.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
							title: lang['oshibka']
						});
				},
				success: function(cmp, resp)
				{
					loadMask.hide();
					var
						index,
						Mkb10CodeClass_id = base_form.findField('Mkb10CodeClass_id').getValue(),
						Mkb10CodeSubClass_id,
						//Mkb10Code_id = base_form.findField('Mkb10Code_id').getValue(),
						LpuLicence_id = base_form.findField('LpuLicence_id').getValue();

					base_form.findField('Lpu_id').setValue(current_window.Lpu_id);

					/*index = base_form.findField('Mkb10Code_id').getStore().findBy(function(rec) {
						return (rec.get('Mkb10Code_id') == Mkb10Code_id);
					});*/

					/*if ( index >= 0 ) {
						Mkb10CodeSubClass_id = base_form.findField('Mkb10Code_id').getStore().getAt(index).get('Mkb10Code_pid');
					}*/

					base_form.findField('Mkb10CodeClass_id').fireEvent('change', base_form.findField('Mkb10CodeClass_id'), Mkb10CodeClass_id);
					/*base_form.findField('Mkb10CodeSubClass_id').fireEvent('change', base_form.findField('Mkb10CodeSubClass_id'), Mkb10CodeSubClass_id);

					index = base_form.findField('Mkb10CodeSubClass_id').getStore().findBy(function(rec) {
						return (rec.get('Mkb10CodeSubClass_id') == Mkb10CodeSubClass_id);
					});

					if ( index >= 0 ) {
						base_form.findField('Mkb10CodeSubClass_id').setValue(Mkb10CodeSubClass_id);
					}
					else {
						base_form.findField('Mkb10CodeSubClass_id').clearValue();
					}*/

					/*index = base_form.findField('Mkb10Code_id').getStore().findBy(function(rec) {
						return (rec.get('Mkb10Code_id') == Mkb10Code_id);
					});

					if ( index >= 0 ) {
						base_form.findField('Mkb10Code_id').setValue(Mkb10Code_id);
					}
					else {
						base_form.findField('Mkb10Code_id').clearValue();
					}*/

					base_form.findField('LpuLicence_id').getStore().load({
						callback: function() {
							index = base_form.findField('LpuLicence_id').getStore().findBy(function(rec) {
								return (rec.get('LpuLicence_id') == LpuLicence_id);
							});

							if ( index >= 0 ) {
								base_form.findField('LpuLicence_id').setValue(LpuLicence_id);
							}
							else {
								base_form.findField('LpuLicence_id').clearValue();
							}
						},
						params: {
							Lpu_id: current_window.Lpu_id
						}
					});

					if ( current_window.action != 'view' ) {
						base_form.findField('SpecializationMO_MedProfile').focus(true, 100);
					}
					else {
						current_window.buttons[3].focus();
					}
				},
				url: '/?c=LpuPassport&m=loadSpecializationMO'
			});
		}
		else {
			base_form.findField('LpuLicence_id').getStore().load({params: {Lpu_id: this.Lpu_id}});

			/*if (Ext.isEmpty(form.getForm().findField('Mkb10Code_id').getValue())) {
				form.getForm().findField('Mkb10Code_id').disable();
			} else {
				form.getForm().findField('Mkb10Code_id').enable();
			}*/

			/*if (Ext.isEmpty(form.getForm().findField('Mkb10CodeSubClass_id').getValue())) {
				form.getForm().findField('Mkb10CodeSubClass_id').disable();
			} else {
				form.getForm().findField('Mkb10CodeSubClass_id').enable();
			}*/

			base_form.findField('Mkb10CodeClass_id').focus(true, 100);
		}
	},
	initComponent: function()
	{
		// Форма с полями
		var current_window = this;

		this.SpecializationMOEditForm = new Ext.form.FormPanel(
			{
				autoHeight: true,
				bodyStyle: 'padding: 5px',
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'SpecializationMOEditForm',
				labelAlign: 'right',
				labelWidth: 180,
				items:
					[{
						id: 'LPEW_Lpu_id',
						name: 'Lpu_id',
						value: 0,
						xtype: 'hidden'
					},{
						name: 'SpecializationMO_id',
						value: 0,
						xtype: 'hidden'
					},{
						fieldLabel: lang['nalichie_otdeleniya_dolechivaniya'],
						xtype: 'swcheckbox',
						anchor: '100%',
						name: 'SpecializationMO_IsDepAftercare',
						tabIndex: TABINDEX_LPEEW + 1
					},{
						anchor: '100%',
						allowBlank: false,
						comboSubject: 'Mkb10CodeClass',
						typeCode: 'int',
						fieldLabel: lang['klass_mkb-10'],
						hiddenName: 'Mkb10CodeClass_id',//Mkb10Code_pid
						lastQuery: '',
						listeners: {
							'change':function (field, newValue, combo) {
								//current_window.Mkb10CodeSubclassStoreFilter(newValue);
								//this.select;
							}
						},
						listWidth: 400,
						tabIndex: TABINDEX_LPEEW + 2,
						valueField: 'Mkb10CodeClass_id',
						xtype: 'swcommonsprcombo'
					}/*,{
						anchor: '100%',
						allowBlank: false,
						fieldLabel: lang['sab-klass_mkb-10'],
						hiddenName: 'Mkb10CodeSubClass_id',//Mkb10Code_pid
						id: 'LPEW_SpecializationMO_SubClass',
						comboSubject: 'Mkb10CodeSubClass',
						orderBy: 'id',
						lastQuery: '',
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{Mkb10CodeSubClass_id}</font>&nbsp;{Mkb10CodeSubClass_Name}',
							'</div></tpl>'
						),
						moreFields: [{name: 'Mkb10CodeSubClass_pid', mapping: 'Mkb10CodeSubClass_pid'}],
						listWidth: 400,
						tabIndex: TABINDEX_LPEEW + 2,
						listeners: {
							'change':function (field, newValue, combo) {
								current_window.Mkb10CodeStoreFilter(newValue);
								this.select;
							}
						},
						mode: 'local',
						resizable: true,
						width : 181,
						xtype: 'swcommonsprcombo'
					},{
						anchor: '100%',
						allowBlank: false,
						codeField: 'Mkb10Code_StateCode',
						comboSubject: 'Mkb10Code',
						fieldLabel: lang['kod_mkb-10'],
						listWidth: 520,
						lastQuery: '',
						hiddenName: 'Mkb10Code_id',
						id: 'LPEW_Mkb10Code_Name',
						tabIndex: TABINDEX_LPEEW + 2,
						moreFields: [
							{ name: 'Mkb10Code_pid', mapping: 'Mkb10Code_pid' },
							{ name: 'Mkb10Code_StateCode', mapping: 'Mkb10Code_StateCode' }
						],
						xtype: 'swcommonsprcombo'
					}*/,{
						fieldLabel: lang['meditsinskiy_profil'],
						allowBlank: false,
						xtype: 'textfield',
						autoCreate: {tag: "input", maxLength: "100", autocomplete: "off"},
						anchor: '100%',
						name: 'SpecializationMO_MedProfile',
						tabIndex: TABINDEX_LPEEW + 3
					},{
						displayField: 'LpuLicence_Num',
						fieldLabel: lang['nomer_litsenzii'],
						codeField: 'LpuLicence_id',
						hiddenName: 'LpuLicence_id',
						id: 'LPEW_LpuLicence_id',
						anchor: '100%',
						editable: false,
						mode: 'local',
						resizable: true,
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'LpuLicence_id'
							}, [
								{ name: 'Lpu_id', mapping: 'Lpu_id' },
								{ name: 'LpuLicence_id', mapping: 'LpuLicence_id' },
								{ name: 'LpuLicence_Ser', mapping: 'LpuLicence_Ser' },
								{ name: 'LpuLicence_Num', mapping: 'LpuLicence_Num' }
							]),
							url:'/?c=LpuPassport&m=loadLpuLicenceSpecializationMO'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{LpuLicence_id}</font>&nbsp; {LpuLicence_Num}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'LpuLicence_id',
						width : 181,
						tabIndex: TABINDEX_LPEEW + 2,
						xtype: 'swbaselocalcombo'
					}],
			//},
				reader: new Ext.data.JsonReader(
					{
						success: function()
						{
							//
						}
					},
					[
						{name: 'Lpu_id'},
						{name: 'SpecializationMO_id'},
						//{name: 'Mkb10Code_id'},
						{name: 'Mkb10CodeClass_id'},
						//{name: 'Mkb10CodeSubClass_id'},
						{name: 'SpecializationMO_MedProfile'},
						{name: 'LpuLicence_id'},
						{name: 'SpecializationMO_IsDepAftercare'}
					]),
				url: '/?c=LpuPassport&m=saveSpecializationMO'
			});
		Ext.apply(this,
			{
				buttons:
					[{
						handler: function()
						{
							this.ownerCt.doSave();
						},
						iconCls: 'save16',
						tabIndex: TABINDEX_LPEEW + 16,
						text: BTN_FRMSAVE
					},
					{
						text: '-'
					},
					HelpButton(this),
					{
						handler: function()
						{
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						tabIndex: TABINDEX_LPEEW + 17,
						text: BTN_FRMCANCEL
					}],
				items: [this.SpecializationMOEditForm]
			});
		sw.Promed.swSpecializationMOEditWindow.superclass.initComponent.apply(this, arguments);
	}
});