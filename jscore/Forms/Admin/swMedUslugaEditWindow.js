/**
 * swMedUslugaEditWindow - окно редактирования/добавления медицинской услуги.
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

sw.Promed.swMedUslugaEditWindow = Ext.extend(sw.Promed.BaseForm,
	{
		action: null,
		autoHeight: true,
		buttonAlign: 'left',
		callback: Ext.emptyFn,
		closable: true,
		closeAction: 'hide',
		draggable: true,
		split: true,
		resizable: false,
		width: 700,
		layout: 'form',
		id: 'MedUslugaEditWindow',
		listeners:
		{
			hide: function()
			{
				this.onHide();
			},
			'resize': function (win, nW, nH, oW, oH) {
				this.syncShadow();
			}
		},
		modal: true,
		onHide: Ext.emptyFn,
		plain: true,
		doSave: function()
		{
			var form = this.findById('MedUslugaEditForm');
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
			var form = this.findById('MedUslugaEditForm');
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
							if (action.result.MedUsluga_id || action.result.MedUslugaPacs_id)
							{
								current_window.hide();
								Ext.getCmp('LpuPassportEditWindow').findById('LPEW_MedUslugaGrid').loadData();
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
		show: function()
		{
			sw.Promed.swMedUslugaEditWindow.superclass.show.apply(this, arguments);
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
			this.findById('MedUslugaEditForm').getForm().reset();
			this.callback = Ext.emptyFn;
			this.onHide = Ext.emptyFn;

			if (arguments[0].MedUsluga_id)
				this.MedUsluga_id = arguments[0].MedUsluga_id;
			else
				this.MedUsluga_id = null;

			if (arguments[0].Lpu_id)
				this.Lpu_id = arguments[0].Lpu_id;
			else
				this.Lpu_id = null;

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
				if ( ( this.MedUsluga_id ) && ( this.MedUsluga_id > 0 ) )
					this.action = "edit";
				else
					this.action = "add";
			}

			var form = this.findById('MedUslugaEditForm');
			form.getForm().setValues(arguments[0]);
			//form.getForm().findField('DUslugi_Name').setHeight(200);
			this.syncShadow();
			var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
			loadMask.show();
			switch (this.action)
			{
				case 'add':
					this.setTitle(lang['meditsinskaya_usluga_dobavlenie']);
					this.enableEdit(true);
					loadMask.hide();
					form.getForm().clearInvalid();
					break;
				case 'edit':
					this.setTitle(lang['meditsinskaya_usluga_redaktirovanie']);
					this.enableEdit(true);
					break;
				case 'view':
					this.setTitle(lang['meditsinskaya_usluga_prosmotr']);
					this.enableEdit(false);
					break;
			}

			if (this.action != 'add')
			{
				form.getForm().load(
					{
						params:
						{
							MedUsluga_id: current_window.MedUsluga_id,
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
						success: function()
						{
							loadMask.hide();
							current_window.findById('LPEW_Lpu_id').setValue(current_window.Lpu_id);

							if ( !Ext.isEmpty(current_window.findById('MUEW_DUslugi_id').getValue()) && current_window.findById('MUEW_MedUsluga_Name').useNameWithPath == true ) {
								// Тянем полное наименование ОКТМО
								//form.findById('MUEW_MedUsluga_Name').setNameWithPath();
							}
						},
						url: '/?c=LpuPassport&m=loadMedUsluga'
					});
			}
			if ( this.action != 'view' )
				Ext.getCmp('MUEW_MedUsluga_Name').focus(true, 100);
			else
				this.buttons[3].focus();
		},
		initComponent: function()
		{
			var _this = this;
			// Форма с полями
			this.MedUslugaEditForm = new Ext.form.FormPanel(
				{
					autoHeight: true,
					bodyStyle: 'padding: 5px',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'MedUslugaEditForm',
					labelAlign: 'right',
					labelWidth: 130,
					items:
						[{
							id: 'LPEW_Lpu_id',
							name: 'Lpu_id',
							value: 0,
							xtype: 'hidden'
						},{
							name: 'MedUsluga_id',
							value: 0,
							xtype: 'hidden'
						},{
							name: 'DUslugi_id',
							id: 'MUEW_DUslugi_id',
							xtype: 'hidden'
						}, { //Колхоз конечно страшный, но время жмёт, textarea используем для отобржения, swtreeselectionfield - сжимаем в ноль и используем для хранения
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: false,
									anchor: '100%',
									autoHeight: true,
									grow: true,
									fieldLabel: lang['naimenovanie_uslugi'],
									name: 'DUslugi_Name',
									id: 'MUEW_DUslugi_NameFull',
									readOnly: true,
									width: 495,
									xtype: 'textarea'
								}]
							}, {
								labelWidth: 0,
								width: 0,
								style: "padding: 0;margin: -1px;",
								labelSeparator: '',
								id: 'MUEW_MedUsluga_Name',
								name: 'DUslugi_Name1',
								callback: function(){
									_this.MedUslugaEditForm.getForm().findField('DUslugi_Name').setValue(this.getValue());
								},
								onTrigger2Click: function() {
									this.clearValue();
									_this.MedUslugaEditForm.getForm().findField('DUslugi_Name').setValue('');
									_this.syncShadow();
								},
								scheme: 'fed',
								object: 'DUslugi',
								selectionWindowParams: {
									height: 500,
									separator: ' ',
									title: lang['naimenovanie_uslugi'],
									width: 600
								},
								valueFieldId: 'MUEW_DUslugi_id',
								xtype: 'swtreeselectionfield'
							}]
						},{
							fieldLabel: lang['nomer_litsenzii'],
							allowBlank:false,
							xtype: 'textfield',
							autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
							//anchor: '100%',
							width: 529,
							name: 'MedUsluga_LicenseNum',
							tabIndex: TABINDEX_LPEEW + 3
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
							{name: 'MedUsluga_id'},
							{name: 'DUslugi_id'},
							{name: 'DUslugi_Name'},
							{name: 'MedUsluga_LicenseNum'}
						]),
					url: '/?c=LpuPassport&m=saveMedUsluga'
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
					items: [this.MedUslugaEditForm]
				});
			sw.Promed.swMedUslugaEditWindow.superclass.initComponent.apply(this, arguments);
		}
	});