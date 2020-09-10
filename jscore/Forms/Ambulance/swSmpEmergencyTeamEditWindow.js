/**
* swSmpEmergencyTeamEditWindow - окно редактирования бригад СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author		Dyomin Dmitry
* @since      09.2012
*/

sw.Promed.swSmpEmergencyTeamEditWindow = Ext.extend(sw.Promed.BaseForm, {
	
	codeRefresh: true,
	objectName: 'swSmpEmergencyTeamEditWindow',
	objectSrc: '/jscore/Forms/Ambulance/swSmpEmergencyTeamEditWindow.js',
	action: null,
	buttonAlign: 'left',
	layout: 'form',
	callback: Ext.emptyFn,
	closable: true,
	collapsible: false,
	draggable: true,
	width: 900,
	maximizable: false,
	maximized: false,	
	minHeight: 550,
	minWidth: 800,
	modal: true,
	onCancelAction: Ext.emptyFn,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,

	saveForm: function( base_form, params_out ){
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение данных бригады СМП."});
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params_out,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EmergencyTeam_id > 0 ) {
						
						base_form.findField('EmergencyTeam_id').setValue( action.result.EmergencyTeam_id );
						
						var data = new Object();

						data.SmpEmergencyTeamData = {
							accessType: 'edit',
							EmergencyTeam_id: base_form.findField('EmergencyTeam_id').getValue(),
							EmergencyTeam_Num: base_form.findField('EmergencyTeam_Num').getValue(),
							EmergencyTeam_CarNum: base_form.findField('EmergencyTeam_CarNum').getValue(),
							EmergencyTeam_CarBrand: base_form.findField('EmergencyTeam_CarBrand').getValue(),
							EmergencyTeam_CarModel: base_form.findField('EmergencyTeam_CarModel').getValue(),
							EmergencyTeam_PortRadioNum: base_form.findField('EmergencyTeam_PortRadioNum').getValue(),
							EmergencyTeam_GpsNum: base_form.findField('EmergencyTeam_GpsNum').getValue(),
							EmergencyTeam_BaseStationNum: base_form.findField('EmergencyTeam_BaseStationNum').getValue()
						};
						this.callback(data);
						this.hide();
					} else {
						if ( action.result.Error_Msg ) {
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

	doSave: function(){
		if ( this.formStatus == 'save' ) {
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
		
		var params = new Object();
		
		this.saveForm( base_form, params );
	},
	
	enableEdit: function( enable ) {

		if ( !enable ) {
			this.buttons[0].hide();
		}
		
		var base_form = this.FormPanel.getForm();
		
		var form_fields = new Array(
			'EmergencyTeam_id',
			'EmergencyTeam_Num',
			'EmergencyTeam_CarNum',
			'EmergencyTeam_CarBrand',
			'EmergencyTeam_CarModel',
			'EmergencyTeam_PortRadioNum',
			'EmergencyTeam_GpsNum',
			'EmergencyTeam_BaseStationNum',
			'EmergencyTeam_HeadShift',
			'EmergencyTeam_Assistant1',
			'EmergencyTeam_Assistant2',
			'EmergencyTeam_Driver',
			'EmergencyTeamSpec_id'
		);
		for( var i=0,cnt=form_fields.length; i<cnt; i++ ){
			if ( enable ) {
				base_form.findField( form_fields[i] ).enable();
			} else {
				base_form.findField( form_fields[i] ).disable();
			}
		}
	},
	
	wialonMergeEmergencyTeam: function( emergency_team_id ){
		if ( typeof emergency_team_id == 'undefined' || parseInt( emergency_team_id ) < 1 ) {
			sw.swMsg.alert(lang['oshibka'],lang['ne_ukazan_identifikator_brigadyi']);
			return;
		}

		var form = new sw.Promed.FormPanel({
			baseCls: 'x-plain',
//			labelWidth: 150,
			url: '/?c=Wialon&m=mergeEmergencyTeam',
			reader: new Ext.data.JsonReader({},[ // Почему-то без этого не работает
				{name: 'EmergencyTeam_id'},
				{name: 'WialonEmergencyTeamId'}
			]),
			items: [{
				name: 'EmergencyTeam_id',
				value: emergency_team_id,
				xtype: 'hidden'
			},{
				fieldLabel: lang['brigada_iz_wialon'],
				xtype: 'swbaselocalcombo',
				hiddenName: 'WialonEmergencyTeamId',
				displayField: 'nm',
				valueField: 'id',
				forceSelection: true,
				triggerAction: 'all',
				store: new Ext.data.JsonStore({
					url: '/?c=Wialon&m=getAllAvlUnitsForMerge',
					editable: false,
					key: 'id',
					autoLoad: true,
					fields: [
						{name: 'id', type: 'int'},
						{name: 'nm', type: 'string'}
					],
					sortInfo: {
						field: 'nm'
					}
				})
			}]
		});
		
		var window = new Ext.Window({
			title: lang['wialon_privyazka_brigadyi'],
			width: 500,
			autoHeight: true,
			modal: true,
			layout: 'fit',
			plain: true,
			bodyStyle: 'padding:5px;',
			items: form,

			buttons: [{
				text: lang['privyazat'],
				handler: function(){
					var base_form = form.getForm();
					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function(){},
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return;
					}
					base_form.submit({
						clientValidation: true,
						success: function(form, action) {
							sw.swMsg.alert(lang['soobschenie'],lang['dannyie_sohranenyi']);
							window.close();
						},
						failure: function(form, action) {
							switch ( action.failureType ) {
								case Ext.form.Action.CLIENT_INVALID:
									sw.swMsg.alert("Ошибка", "Данные формы не могут быть отправлены, пока вы не исправите ошибки.");
								break;
								case Ext.form.Action.CONNECT_FAILURE:
									sw.swMsg.alert("Ошибка", "Ошибка отправки данных на сервер.");
								break;
								case Ext.form.Action.SERVER_INVALID:
									sw.swMsg.alert("Ошибка", action.result.Error_Msg);
								break;
								default:
									if ( action.result ) {
										if ( action.result.Error_Msg ) {
											sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
										} else {
											sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_dannyih_proizoshla_nepredvidennaya_oshibka']);
										}
									}
								break;
							}
						}
					});					
				}
			},{
				text: lang['otmena'],
				handler: function(){
					window.close();
				}
			}],

			onEsc: function(){
				window.close();
			}
		});
		
		window.show('',function(){
			// Загружаем данны формы после загрузки окна
			form.getForm().load({
				url: '/?c=Wialon&m=loadEmergencyTeamWialonRel',
				params: {
					EmergencyTeam_id: emergency_team_id
				},
				failure: function(form, action){
					// @todo Если сервер возвращает пустой набор данных: [], то вызывается этот метод
					if ( action.result.success === true ) {
						return;
					}
					sw.swMsg.show({
						title: lang['oshibka'],
						msg: lang['oshibka_zaprosa_k_serveru'],
						buttons: Ext.Msg.OK,
						fn: function(){
							window.close();
						},
						icon: Ext.Msg.ERROR
					});
				},
				success: function(){}
			});
		});
	},

	initComponent: function(){
		
		var obj = this;
		
		this.regionNumber = 60;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			id: 'SmpEmergencyTeamEditForm',
			labelAlign: 'right',
			labelWidth: 220,
			reader: new Ext.data.JsonReader(
				{success: Ext.emptyFn},
				[
					{name: 'accessType'},
					{name: 'EmergencyTeam_Num'},
					{name: 'EmergencyTeam_CarNum'},
					{name: 'EmergencyTeam_CarBrand'},
					{name: 'EmergencyTeam_CarModel'},
					{name: 'EmergencyTeam_PortRadioNum'},
					{name: 'EmergencyTeam_GpsNum'},
					{name: 'EmergencyTeam_BaseStationNum'},
					{name: 'EmergencyTeam_HeadShift'},
					{name: 'EmergencyTeam_Assistant1'},
					{name: 'EmergencyTeam_Assistant2'},
					{name: 'EmergencyTeam_Driver'},
					{name: 'EmergencyTeamSpec_id'},
					{name: 'ARMType'}
				]
			),
			region: 'center',
			url: '/?c=EmergencyTeam&m=saveEmergencyTeam',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			},{
				name: 'EmergencyTeam_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'ARMType',
				value: '',
				xtype: 'hidden'
			},{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						fieldLabel: lang['nomer_brigadyi'],
						name: 'EmergencyTeam_Num',
						width: 100,
						allowBlank: false,
						regex: new RegExp(/^[0-9]+$/),
						disabledClass: 'field-disabled'
					},{
						xtype: 'swcommonsprcombo',
						fieldLabel: lang['profil'],
						comboSubject: 'EmergencyTeamSpec',
						hiddenName: 'EmergencyTeamSpec_id',
						displayField: 'EmergencyTeamSpec_Name',
						disabledClass: 'field-disabled'
					},{
						xtype: 'textfield',
						fieldLabel: lang['nomer_mashinyi'],
						name: 'EmergencyTeam_CarNum',
						width: 100,
						disabledClass: 'field-disabled'
					},{
						xtype: 'textfield',
						fieldLabel: lang['marka_mashinyi'],
						name: 'EmergencyTeam_CarBrand',
						width: 100,
						disabledClass: 'field-disabled'
					}]
				},{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'textfield',
						fieldLabel: lang['nomer_ratsii'],
						name: 'EmergencyTeam_PortRadioNum',
						width: 100,
						disabledClass: 'field-disabled'
					},{
						xtype: 'textfield',
						fieldLabel: lang['nomer_gps_glonass'],
						name: 'EmergencyTeam_GpsNum',
						width: 100,
						disabledClass: 'field-disabled'
					},{
						xtype: 'textfield',
						fieldLabel: lang['nomer_bazovoy_podstantsii'],
						name: 'EmergencyTeam_BaseStationNum',
						width: 100,
						disabledClass: 'field-disabled'
					},{
						xtype: 'textfield',
						fieldLabel: lang['model_mashinyi'],
						name: 'EmergencyTeam_CarModel',
						width: 100,
						disabledClass: 'field-disabled'
					}]
				}]
			},{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['sostav'],
				items: [{
					border: false,
					layout: 'column',
					width: 750,
					items: [{
						border: false,
						layout: 'form',
						items: [{
							xtype: 'swmedpersonalcombo',
							editable: true,
							width: 500,
							typeAhead: true,
							fieldLabel: lang['starshiy_brigadyi'],
							hiddenName: 'EmergencyTeam_HeadShift',
							id: 'swetheadshift',
							allowBlank: false
						},{
							xtype: 'swmedpersonalcombo',
							editable: true,
							width: 500,
							typeAhead: true,
							fieldLabel: lang['pervyiy_pomoschnik'],
							hiddenName: 'EmergencyTeam_Assistant1',
							id: 'swetassistant1'
						},{
							xtype: 'swmedpersonalcombo',
							editable: true,
							width: 500,
							typeAhead: true,
							fieldLabel: lang['vtoroy_pomoschnik'],
							hiddenName: 'EmergencyTeam_Assistant2',
							id: 'swetassistant2'
						},{
							xtype: 'swmedpersonalcombo',
							editable: true,
							width: 500,
							typeAhead: true,
							fieldLabel: lang['voditel'],
							hiddenName: 'EmergencyTeam_Driver',
							id: 'swetdriver'
						}]
					}]
				}]
			},{
				xtype: 'button',
				text: lang['privyazka_brigadyi_k_wialon'],
				iconCls: 'search16',
				hidden: this.regionNumber == 60 ? true : false,
				handler: function(c,r,i){
					obj.wialonMergeEmergencyTeam( obj.FormPanel.getForm().findField('EmergencyTeam_id').getValue() );
				}
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function(){
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},{
				text: '-'
			},
			{text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [ this.FormPanel ],
			layout: 'border'
		});

		sw.Promed.swSmpEmergencyTeamEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swSmpEmergencyTeamEditWindow.superclass.show.apply(this, arguments);
		
		this.doLayout();
		this.restore();
		this.center();
		
		var base_form = this.FormPanel.getForm();		
		base_form.reset();
		
		this.formStatus = 'edit';

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function(){this.hide();}.createDelegate(this));
			return false;
		}
		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		
		this.FormPanel.findById('swetheadshift').getStore().load();
		this.FormPanel.findById('swetassistant1').getStore().load();
		this.FormPanel.findById('swetassistant2').getStore().load();
		this.FormPanel.findById('swetdriver').getStore().load();	
	
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_SMP_EMRGTEAMADD);
				loadMask.hide();
				base_form.clearInvalid();
				base_form.findField('ARMType').setValue( arguments[0].formParams.ARMType );
			break;

			case 'edit':
			case 'view':
				
				var EmergencyTeam_id = base_form.findField('EmergencyTeam_id').getValue();
				
				if ( !EmergencyTeam_id ) {
					base_form.setTitle( WND_SMP_EMRGTEAMEDIT );
					base_form.enableEdit( true );
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EmergencyTeam_id: EmergencyTeam_id
					},
					success: function(){
						loadMask.hide();
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}
						if ( this.action == 'edit' ) {
							this.setTitle( WND_SMP_EMRGTEAMEDIT );
							this.enableEdit( true );
						} else {
							this.setTitle( WND_SMP_EMRGTEAMVIEW );
							this.enableEdit( false );
						}
					
					}.createDelegate(this),
					url: '/?c=EmergencyTeam&m=loadEmergencyTeam'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	}
});
