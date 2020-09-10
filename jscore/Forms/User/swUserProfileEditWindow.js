/**
* swUserProfileEditWindow - окно редактирования профиля пользователя.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Dmitry Storozhev
* @version      24.08.2011
*
*/

sw.Promed.swUserProfileEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	shim: false,
	height: 500,
	width: 1000,
	closeAction: 'hide',
	id: 'swUserProfileEditWindow',
	objectName: 'swUserProfileEditWindow',
	title: lang['profil_polzovatelya'],
	plain: true,
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.saveUserData();
			},
			iconCls: 'save16',
			text: lang['sohranit']
		},
		{
			handler: function()
			{
				getWnd('swUserPasswordChangeWindow').show();
			},
			iconCls: 'edit16',
			text: 'Сменить пароль'
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
	listeners:
	{
		'hide': function(win)
		{
			if(win.form_fields)
			{
				for(i=0; i<win.form_fields.length; i++)
				{
					win.form_fields[i].enable();
				}
			}
			this.buttons[0].setVisible(true);
			win.FileUploadField.setVisible(true);
			win.overwriteTpl(false);
			win.MedStaffactGrid.getStore().removeAll();
			win.UserDataPanel.getForm().reset();
		}
	},
	openUserPhoneActivateWindow: function ()
	{
		var base_form = this.UserDataPanel.getForm();
		var params = new Object();

		params.pmUser_Login = this.pmUser_Login;
		params.user_phone = base_form.findField('user_phone').getValue();

		params.callback = function(data) {
			if (data && data.activatedPhone) {
				this.activatedPhone = data.activatedPhone;
				base_form.findField('user_phone').setValue(this.activatedPhone);
				this.findById('phone_act_status_label').show();
				this.findById('phone_act_button').hide();
			}
		}.createDelegate(this);

		getWnd('swUserPhoneActivateWindow').show(params);
	},
	setAccess: function () 
	{
		var base_form = this.UserDataPanel.getForm();
		var enable = ((this.action == 'edit') && (this.pmUser_id == getGlobalOptions().pmuser_id));
		var med = (getGlobalOptions().medpersonal_id==null);
		var sur = (base_form.findField('user_surname').getValue()=='');
		var sec = (base_form.findField('user_secname').getValue()=='');
		var fir = (base_form.findField('user_firname').getValue()=='');
		
		base_form.findField('user_surname').setDisabled(!enable || (!med && !sur));
		base_form.findField('user_secname').setDisabled(!enable || (!med && !sec));
		base_form.findField('user_firname').setDisabled(!enable || (!med && !fir));
		base_form.findField('user_email').setDisabled(!enable);
		base_form.findField('user_phone').setDisabled(!enable);
		base_form.findField('user_about').setDisabled(!enable);
		this.buttons[0].setVisible(enable);
		this.FileUploadField.setVisible(enable);
	},
	loadMedStaffFactGrid: function(){

		var win = this,
			base_form = win.UserDataPanel.getForm();

		// Чтение мест работы
		win.MedStaffactGrid.getStore().load({
			params:
			{
				user_id: win.pmUser_id
			},
			callback: function(r)
			{
				// Подмена на ФИО врача из МедПерсонал
				if (r.length>0) {
					userData = r[0].json;
					if ((base_form.findField('user_surname').getValue() != userData.Person_SurName) ||
						(base_form.findField('user_secname').getValue() != userData.Person_SecName) ||
						(base_form.findField('user_firname').getValue() != userData.Person_FirName)) {
						win.InfoPanel.body.update(langs('Данные вашей учетной записи (')
							+base_form.findField('user_surname').getValue()+' '+base_form.findField('user_firname').getValue()
							+' '+base_form.findField('user_secname').getValue()
							+langs(') отличаются от данных врача, связанного с вашим аккаунтом. Сохраните данные вашего профиля для изменения ФИО аккаунта на ФИО врача.')/*+((isSuperAdmin())?langs('(чтобы это сообщение больше не появлялось, отправьте СМС на номер: 4444).'):'.')*/);
						base_form.findField('user_surname').setValue(userData.Person_SurName);
						base_form.findField('user_secname').setValue(userData.Person_SecName);
						base_form.findField('user_firname').setValue(userData.Person_FirName);
						win.InfoPanel.setVisible(true);
					} else {
						win.InfoPanel.body.update('');
						win.InfoPanel.setVisible(false);
					}
					win.doLayout();
					win.syncSize();
					base_form.findField('user_email').focus(true, 100);
				} else {
					base_form.findField('user_surname').focus(true, 100);
				}
			}
		});
	},
	show: function() 
	{
		sw.Promed.swUserProfileEditWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		if(!arguments[0])
		{
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			win.hide();
			return false;
		}
		// Аргументы 
		this.pmUser_id = (arguments[0].pmUser_id)?arguments[0].pmUser_id:getGlobalOptions().pmuser_id;
		this.pmUser_Login = arguments[0].pmUser_Login || null;
		this.Lpu_Nick = arguments[0].Lpu_Nick || null;
		win.action = arguments[0].action;
		win.activatedPhone = null;

		var base_form = this.UserDataPanel.getForm();
		
		var lm = win.getLoadMask(lang['zagruzka_dannyih']);
		lm.show();
		var params = {};
		if (this.pmUser_Login) {
			params = {
				pmUser_Login: this.pmUser_Login,
				Lpu_Nick: this.Lpu_Nick
			}
		}
		// Чтение инфы о юзере
		base_form.load({
			params: params,
			url: '/?c=Messages&m=getUserDataProfile',
			success: function(form, resp)
			{
				lm.hide();
				var obj = Ext.util.JSON.decode(resp.response.responseText)[0];
				if(obj.user_avatar == '')
					obj.file_url = '/img/default_user.jpg';
				else
					obj.file_url = '/uploads/users/'+obj.pmuser_id+'/'+obj.user_avatar;
				win.overwriteTpl(obj);
				win.setAccess();

				if (obj.user_phone_act == 2) {
					win.findById('phone_act_status_label').show();
					win.findById('phone_act_button').hide();
					win.activatedPhone = base_form.findField('user_phone').getValue();
				} else {
					win.findById('phone_act_status_label').hide();
					win.findById('phone_act_button').show();
					win.activatedPhone = null;
				}

				win.loadMedStaffFactGrid();
					},
			failure: function(form, resp)
			{
				lm.hide();
				win.hide();
			}
		});
	},
	
	saveUserData: function()
	{
		var win = this;
		var frm = win.UserDataPanel.getForm();
		if(!frm.isValid())
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya_polya_obyazatelnyie_k_zapolneniyu_vyidelenyi_osobo']);
			return false;
		}
		var user_email = frm.findField('user_email').getValue();
		var re = /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/;
		if(!re.test(user_email) && user_email != '')
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_korrektno_zapolneno_pole_e-mail']);
			return false;
		}
		var user_phone = frm.findField('user_phone').getValue();
		if (!Ext.isEmpty(this.activatedPhone) && user_phone != this.activatedPhone) {
			Ext.Msg.alert(lang['nomer_izmenen'], lang['dlya_polucheniya_sms-uvedomleniy_neobhodimo_aktivirovat_nomer_telefona']);
		}
		var params = {}
		params.user_login = frm.findField('user_login').getValue();
		params.user_surname = frm.findField('user_surname').getValue();
		params.user_firname = frm.findField('user_firname').getValue();
		params.user_secname = frm.findField('user_secname').getValue();
		
		win.getLoadMask().show();
		frm.submit({
			params: params,
			success: function(f, r)
			{
				win.getLoadMask().hide();
				win.hide();
			},
			failure: function()
			{
				win.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_vashego_profilya_proizoshla_oshibka']);
			}
		});
	},
	
	overwriteTpl: function(obj)
	{
		if(!obj){
			var obj = {};
			obj.file_url = '';
		}
		this.findById('user_photo').tpl = new Ext.Template(this.PhotoTpl);
		this.findById('user_photo').tpl.overwrite(this.findById('user_photo').body, obj);
	},
	
	initComponent: function() 
	{
		var wnd = this;

		this.PhotoTpl = [
			'<div><img style="text-align: center; " height="200" width="200" src="{file_url}" /></div>'
		];
		/*
		this.linkChangePhoto = new Ext.Panel({
			width: 190,
			border: false,
			frame: true,
			html: '<div style="text-align: center;"><a href="#" onClick="Ext.getCmp(&quot;'+this.id+'&quot;).FileUploadField.onSelect();">Обновить изображение</a></div>'
		});
		*/
		this.FileUploadField = new Ext.form.FileUploadField({
			hideLabel: true,
			buttonOnly: true,
			//link: {linkId:'fu_link', html:'<div style="text-align: center;"><a id="fu_link" href="#">Обновить изображение</a></div>'},
			name: 'user_ava_uploader',
			id: 'user_ava_uploader',
			buttonText: lang['obnovit'],
			//input: {style:'display:none'},
			listeners:
			{
				fileselected: function(elem, fname)
				{
					var win = this;
					var frm = this.UserPhotoPanel.getForm();
					var re = /\.[jgp][pin][gf]/i;
					var access = re.test(fname);
					if(!access)
					{
						sw.swMsg.alert(lang['oshibka'], lang['dannyiy_tip_zagrujaemogo_fayla_ne_podderjivaetsya_podderjivaemyie_tipyi_*jpg_*gif_*png']);
						elem.reset();
						return false;
					}
					frm.submit({
						success: function(form, resp)
						{
							var obj = Ext.util.JSON.decode(resp.response.responseText);
							win.overwriteTpl(obj);
						}
					});
				}.createDelegate(this)
			},
			width: 130
		});
		this.UserPhotoPanel = new Ext.form.FormPanel({
			region: 'west',
			width: 200,
			id: 'ava_upload_panel',
			url: '/?c=Messages&m=uploadUserPhoto',
			fileUpload: true,
			items: [
				{
					height: 200,
					xtype: 'panel',
					bodyStyle: 'background: #DFE8F6;',
					id: 'user_photo',
					name: 'user_photo',
					tpl: ''
				},
				this.FileUploadField 
			]
		});
		this.InfoPanel = new Ext.Panel({
			xtype: 'panel',
			anchor: '100%',
			border: false,
			bodyStyle: 'font: 12px Tahoma; padding: 5px; background-color: #fadadd',
			autoHeight: true,
			hidden: true,
			html: ''
		});
		this.UserDataMainPanel = new sw.Promed.Panel({
			region: 'north',
			border: false,
			autoHeight: true,
			layout: 'form',
			items: [
				{
					xtype: 'textfield',
					anchor: '100%',
					disabled: true,
					name: 'user_login',
					fieldLabel: lang['login']
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'user_surname',
					fieldLabel: lang['familiya']
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'user_firname',
					fieldLabel: lang['imya']
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'user_secname',
					fieldLabel: lang['otchestvo']
				}, this.InfoPanel, {
					xtype: 'textfield',
					anchor: '100%',
					maskRe: /[a-zA-Z0-9@._-]/,
					name: 'user_email',
					fieldLabel: 'E-mail'
				}, {
					layout: 'column',
					border: false,
					bodyStyle: 'background: #DFE8F6;',
					defaults: {
						border: false,
						bodyStyle: 'background: #DFE8F6;'
					},
					items: [{
						layout: 'form',
						items: [{
							xtype: 'textfield',
							maskRe: /[0-9]/,
							name: 'user_phone',
							fieldLabel: lang['telefon'],
							width: 120,
							listeners: {
								'change': function(field, newValue, oldValue) {
									if (this.activatedPhone != newValue) {
										this.findById('phone_act_status_label').hide();
										this.findById('phone_act_button').show();
									} else {
										this.findById('phone_act_status_label').show();
										this.findById('phone_act_button').hide();
									}
								}.createDelegate(this)
							}
						}]
					}, {
						id: 'phone_act_button',
						layout: 'form',
						border: false,
						hidden: true,
						items: [{
							xtype: 'button',
							style: 'margin-left: 10px;',
							handler: function(){
								this.openUserPhoneActivateWindow();
							}.createDelegate(this),
							text: lang['aktivirovat']
						}]
					}, {
						id: 'phone_act_status_label',
						layout: 'form',
						border: false,
						hidden: true,
						style: 'padding-top: 3px;',
						items: [{
							xtype: 'label',
							style: 'margin-left: 10px; color: green;',
							text: lang['aktivirovan']
						}]
					}]
				}, {
					xtype: 'textarea',
					anchor: '100%',
					name: 'user_about',
					fieldLabel: lang['o_sebe']
				}, {
					xtype: 'textfield',
					name: 'user_Lpu',
					disabled: true,
					anchor: '100%',
					fieldLabel: lang['lpu']
				}
			]
		});
		
		this.MedStaffactGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
			border: false,
			loadMask: true,
			title: lang['mesta_rabotyi'],
			pageSize: 20,
			autoLoadData: false,
			stripeRows: true,
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true
			}),
			tbar: new Ext.Toolbar({
				id: 'MedStaffactGridToolbar',
				items:
					[
						new Ext.Action({
								name:'refresh',
								text:BTN_GRIDREFR,
								tooltip: BTN_GRIDREFR,
								iconCls : 'x-btn-text',
								icon: 'img/icons/refresh16.png',
								handler: function() { wnd.loadMedStaffFactGrid();}
							}
						),
						new Ext.Action({
								name:'edit',
								text:langs('Редактировать роли'),
								tooltip: langs('Редактировать роли'),
								iconCls : 'x-btn-text',
								icon : 'img/icons/edit16.png',
								handler: function() {

									var selected = wnd.MedStaffactGrid.getSelectionModel().getSelected();

									if (selected) {
										getWnd('swEMDMedStaffFactRoleWindow').show({
											MedStaffFact_id: selected.get('MedStaffFact_id'),
											LpuSection_FullName: selected.get('LpuSection_FullName'),
											MedSpec_Name: selected.get('MedSpec_Name'),
											Lpu_Name: selected.get('Lpu_Name')
										});
									}
								}
							}
						)
					]
			}),
			columns: [
				{key: true, hidden: true, dataIndex: 'MedStaffFact_id'},
				{header: lang['otdelenie'], id: 'autoexpand', sortable: true, dataIndex: 'LpuSection_FullName'},
				{header: lang['doljnost'], width: 200, sortable: true, dataIndex: 'MedSpec_Name'},
				{header: lang['mo'], width: 200, sortable: true, dataIndex: 'Lpu_Name'},
				{header: lang['data_nachala'], width: 100, sortable: true, dataIndex: 'WorkData_begDate', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
				{header: lang['data_okonchaniya'], width: 100, sortable: true, dataIndex: 'WorkData_endDate', renderer: Ext.util.Format.dateRenderer('d.m.Y')}
			],
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'MedStaffFact_id'
				}, [{
					mapping: 'MedStaffFact_id',
					name: 'MedStaffFact_id',
					type: 'int'
				},{
					mapping: 'LpuSection_FullName',
					name: 'LpuSection_FullName',
					type: 'string'
				},{
					mapping: 'MedSpec_Name',
					name: 'MedSpec_Name',
					type: 'string'
				},{
					mapping: 'Lpu_Name',
					name: 'Lpu_Name',
					type: 'string'
				},{
					mapping: 'WorkData_begDate',
					name: 'WorkData_begDate',
					dateFormat: 'd.m.Y',
					type: 'date'
				},{
					mapping: 'WorkData_endDate',
					name: 'WorkData_endDate',
					dateFormat: 'd.m.Y',
					type: 'date'
				}]),
				url: '/?c=Messages&m=getMedStaffactsforUser'
			}),
			paging: true,
			totalProperty: 'totalCount'
		});
		
		this.UserDataPanel = new Ext.form.FormPanel({
			region: 'center',
			url: '/?c=Messages&m=saveUserDataProfile',
			layout: 'border',
			defaults:
			{
				bodyStyle: 'padding: 5px; background: #DFE8F6;'
			},
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[	
				{ name: 'pmuser_id' },
				{ name: 'user_login' },
				{ name: 'user_surname' },
				{ name: 'user_secname' },
				{ name: 'user_firname' },
				{ name: 'user_email' },
				{ name: 'user_phone' },
				{ name: 'user_phone_act' },
				{ name: 'user_about' },
				{ name: 'user_Lpu' }
			]),
			items: [this.UserDataMainPanel, this.MedStaffactGrid]
		});
	
		
		Ext.apply(this, 
		{
			layout: 'border',
			defaults:
			{
				bodyStyle: 'padding: 3px; background: #DFE8F6;'
			},
			items: [this.UserPhotoPanel, this.UserDataPanel]
		});
		sw.Promed.swUserProfileEditWindow.superclass.initComponent.apply(this, arguments);
	}
});