/**
* swOrgContactEditWindow - форма редактирования контактного лица
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      OrgStruct
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      09.12.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swOrgContactEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: false,
	autoHeight: true,
	width: 600,
	id: 'OrgContactEditWindow',
	title: WND_ORGSTRUCT_ORGCONTACT_ADD, 
	layout: 'form',
	resizable: true,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this;
		var base_form = this.formPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.formPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		form.getLoadMask(LOAD_WAIT_SAVE).show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				form.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				form.getLoadMask().hide();
				
				if ( action.result && action.result.OrgHead_id > 0 ) {
					this.callback(this.owner, action.result.OrgHead_id);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	formStatus: 'edit',
	initComponent: function() 
	{
		var form = this;
		
		this.formPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			labelAlign: 'right',
			labelWidth: 180,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'OrgHead_id' },
				{ name: 'Person_id' },
				{ name: 'OrgHeadPost_id' },
				{ name: 'OrgHead_Phone' },
				{ name: 'OrgHead_Mobile' },
				{ name: 'OrgHead_Fax' },
				{ name: 'OrgHead_Email' },
				{ name: 'OrgHead_CommissDate' },
				{ name: 'OrgHead_CommissNum' },
				{ name: 'OrgHead_Address' },
				{ name: 'Org_id' }
			]),
			url: '/?c=OrgStruct&m=saveOrgHead',
			items: [{
				name: 'OrgHead_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Org_id',
				value: 0,
				xtype: 'hidden'
			}, {
				hiddenName: 'Person_id',
				allowBlank: false,
				tabIndex: TABINDEX_OCEW + 0,
				fieldLabel: lang['fio'],
				width: 350,
				onTrigger1Click: function() {
					if (this.disabled) return false;
					var combo = this;
					getWnd('swPersonSearchWindow').show({
						onSelect: function(personData) {
							if ( personData.Person_id > 0 )
							{
								combo.getStore().loadData([{
									Person_id: personData.Person_id,
									Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
								}]);												
								combo.setValue(personData.Person_id);
								combo.collapse();
								combo.focus(true, 500);
								combo.fireEvent('change', combo);
							}
							getWnd('swPersonSearchWindow').hide();
						},
						onClose: function() {combo.focus(true, 500)}
					});
				},
				xtype: 'swpersoncombo'
			}, {
				hiddenName: 'OrgHeadPost_id',
				allowBlank: false,
				tabIndex: TABINDEX_OCEW + 1,
				width: 300,
				fieldLabel: lang['doljnost'],
				xtype: 'sworgheadpostcombo'
			}, 
			{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					labelWidth: 180,
					layout: 'form',
					items: [{
						name: 'OrgHead_CommissNum',
						tabIndex: TABINDEX_OCEW + 2,
						fieldLabel: lang['№_prikaza_o_naznachenii'],
						xtype: 'textfield'
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 100,
					items: [{
						xtype: 'swdatefield',
						fieldLabel: lang['data_prikaza'],
						format: 'd.m.Y',
						tabIndex: TABINDEX_OCEW + 3,
						name: 'OrgHead_CommissDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
					}]
				}]
			}, {
				name: 'OrgHead_Phone',
				allowBlank: true,
				tabIndex: TABINDEX_OCEW + 4,
				width: 300,
				fieldLabel: lang['telefon_yi'],
				xtype: 'textfield'
			}, {
				name: 'OrgHead_Fax',
				allowBlank: true,
				tabIndex: TABINDEX_OCEW + 5,
				width: 300,
				fieldLabel: lang['faks_yi'],
				xtype: 'textfield'
			}, {
				name: 'OrgHead_Email',
				allowBlank: true,
				tabIndex: TABINDEX_OCEW + 6,
				width: 300,
				fieldLabel: 'e-mail',
				xtype: 'textfield'
			}, {
				name: 'OrgHead_Mobile',
				allowBlank: true,
				tabIndex: TABINDEX_OCEW + 7,
				width: 300,
				fieldLabel: lang['mobilnyiy_telefon'],
				xtype: 'textfield'
			}, {
				name: 'OrgHead_Address',
				allowBlank: true,
				tabIndex: TABINDEX_OCEW + 8,
				width: 300,
				fieldLabel: lang['adres_№_rabochego_kabineta'],
				xtype: 'textfield'
			}]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				form.formPanel
			],
			buttons:
			[{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = form.formPanel.getForm();

					if ( !base_form.findField('OrgHead_Address').disabled ) {
						base_form.findField('OrgHead_Address').focus();
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 1].focus();
				},
				tabIndex: TABINDEX_OCEW + 10,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_OCEW + 11),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_OCEW + 12,
				onTabAction: function()
				{
					var base_form = form.formPanel.getForm();
					if ( !base_form.findField('Person_id').disabled ) {
						base_form.findField('Person_id').focus(true);
					}
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swOrgContactEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOrgContactEditWindow.superclass.show.apply(this, arguments);
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.owner = null;
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		var base_form = this.formPanel.getForm();
		base_form.reset();
		
		if ( arguments[0].Org_id ) {
			base_form.findField('Org_id').setValue(arguments[0].Org_id);
		}
		
		if ( arguments[0].OrgHead_id ) {
			base_form.findField('OrgHead_id').setValue(arguments[0].OrgHead_id);
		}
		
		this.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_ORGSTRUCT_ORGCONTACT_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_ORGSTRUCT_ORGCONTACT_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_ORGSTRUCT_ORGCONTACT_VIEW);
					this.enableEdit(false);
				}
				base_form.clearInvalid();
				
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						this.getLoadMask().hide();
					}.createDelegate(this),
					params: {
						OrgHead_id: base_form.findField('OrgHead_id').getValue()
					},
					success: function() {						
						this.getLoadMask().hide();
					}.createDelegate(this),
					url: '/?c=OrgStruct&m=loadOrgHeadEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('Person_id').disabled ) {
			base_form.findField('Person_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	}
});