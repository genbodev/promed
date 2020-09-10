/**
* swSmpTariffEditWindow - окно редактирования/добавления тарифов СМП.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author		Dyomin Dmitry
* @version      06.12.2012
*/

sw.Promed.swSmpTariffEditWindow = Ext.extend(sw.Promed.BaseForm,{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'SmpTariffEditWindow',
	listeners: { hide: function(){ this.onHide(); } },
	Lpu_id: null,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function(){
		
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
		this.submit();
		return true;
	},
	submit: function(){
		var form = this.FormPanel;
		var current_window = this;
		var loadMask = new Ext.LoadMask( this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		form.getForm().submit({
			params: {},
			failure: function( result_form, action ){
				loadMask.hide();
				if ( action.result ){
					if ( action.result.Error_Code ){
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action){
				loadMask.hide();
				if ( action.result ){
					if ( action.result.CmpProfileTariff_id ){
						getWnd('swLpuStructureViewForm').findById('SmpTariffGrid').loadData();
						current_window.hide();
					}else{
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function(){ form.hide(); },
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_v_sluchae_povtoreniya_oshibki_obratites_k_razrabotchikam'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable){
		var form = this.findById('SmpTariffEditForm').getForm();
		if (enable) {
			form.findField('LpuSectionProfile_id').enable();
			form.findField('TariffClass_id').enable();
			form.findField('CmpProfileTariff_begDT').enable();
			form.findField('CmpProfileTariff_endDT').enable();
			form.findField('CmpProfileTariff__Value').enable();
			this.buttons[0].enable();
		} else {
			form.findField('LpuSectionProfile_id').disable();
			form.findField('TariffClass_id').disable();
			form.findField('CmpProfileTariff_begDT').disable();
			form.findField('CmpProfileTariff_endDT').disable();
			form.findField('CmpProfileTariff__Value').disable();
			this.buttons[0].disable();
		}
	},
	show: function(){
		sw.Promed.swSmpTariffEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		
		if ( !arguments[0] ){
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
		
		var loadMask = new Ext.LoadMask( this.getEl(), { msg: LOAD_WAIT } );
		loadMask.show();

		var base_form = this.findById('SmpTariffEditForm');
		
		base_form.getForm().reset();
		
		this.Lpu_id = arguments[0].Lpu_id || null;
		
		this.CmpProfileTariff_id = arguments[0].CmpProfileTariff_id || null;
		
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		} else {
			this.action = this.CmpProfileTariff_id ? 'edit' : 'add';
		}
		
		base_form.getForm().setValues( arguments[0] );
		
		var opts = getGlobalOptions();
		switch( opts.region.number ){
			// Уфа
			case 2:
				var LpuSectionProfileFilters = [1040,1041,1042,1043,1044,1045,1046];
				var TariffClassFilters = [5, 6];
			break;

			// Карелия
			case 10:
				var LpuSectionProfileFilters = [84];
				var TariffClassFilters = [14];
			break;

			// Хакассия
			case 19:
				var LpuSectionProfileFilters = [84];
				var TariffClassFilters = [15,16];
			break;
			
			// Пермь
			case 59:
			default:
				var LpuSectionProfileFilters = [1040,1041,1042,1043,1044,1045,1046];
				var TariffClassFilters = [15,16];
			break;
		}

		
		// Фильтруем значения комбобокса «Профиль»
		var LpuSectionProfile = base_form.getForm().findField('LpuSectionProfile_id');
		LpuSectionProfile.getStore().clearFilter();
		LpuSectionProfile.lastQuery = '';
		LpuSectionProfile.getStore().filterBy(function(r){
			if ( r.get('LpuSectionProfile_Code').inlist(LpuSectionProfileFilters) ) return true;
			return false;
		});
		
		// Фильтруем значения комбобокса «Класс тарифа»
		var TariffClass = base_form.getForm().findField('TariffClass_id');
		TariffClass.getStore().clearFilter();
		TariffClass.lastQuery = '';
		TariffClass.getStore().filterBy(function(r){
			if ( r.get('TariffClass_Code').inlist(TariffClassFilters) ) return true;
			return false;
		});
		
		switch( this.action ){
			case 'add':
				this.setTitle(lang['tarifyi_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				base_form.getForm().clearInvalid();
			break;
			case 'edit':
				this.setTitle(lang['tarifyi_redaktirovanie']);
				this.enableEdit(true);
			break;
			case 'view':
				this.setTitle(lang['tarifyi_prosmotr']);
				this.enableEdit(false);
			break;
		}
		
		if ( this.action != 'add' ){
			base_form.getForm().load({
				params: {
					CmpProfileTariff_id: current_window.CmpProfileTariff_id,
					Lpu_id: current_window.Lpu_id
				},
				failure: function(f, o, a){
					loadMask.hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function(){
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function(){
					loadMask.hide();
				},
				url: '/?c=LpuPassport&m=loadSmpTariff'
			});
		}
		else {
			if ( LpuSectionProfile.getStore().getCount() == 1 ) {
				LpuSectionProfile.setValue(LpuSectionProfile.getStore().getAt(0).get('LpuSectionProfile_id'));
			}

			if ( TariffClass.getStore().getCount() == 1 ) {
				TariffClass.setValue(TariffClass.getStore().getAt(0).get('TariffClass_id'));
			}
		}

		if ( this.action != 'view' ) {
			this.FormPanel.getForm().findField('LpuSectionProfile_id').focus(true, 100);
		} else {
			this.buttons[0].focus();
		}
	},
	initComponent: function(){

		var current_window = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoWidth: false,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'SmpTariffEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			
			items: [{
				xtype: 'hidden',
				name: 'CmpProfileTariff_id',
				id: 'STEW_CmpProfileTariff_id',
				value: 0
			},{
				xtype: 'hidden',
				name: 'Lpu_id',
				id: 'STEW_Lpu_id',
				value: 0
			},{
				xtype: 'swcommonsprcombo',
				fieldLabel: lang['profil'],
				comboSubject: 'LpuSectionProfile',
				hiddenName: 'LpuSectionProfile_id',
				displayField: 'LpuSectionProfile_Name',
				allowBlank: getRegionNick() == 'ufa',
				anchor: '100%',
				filters: [],
				disabledClass: 'field-disabled'
			},{
				xtype: 'swcommonsprcombo',
				fieldLabel: lang['vid_tarifa'],
				comboSubject: 'TariffClass',
				hiddenName: 'TariffClass_id',
				displayField: 'TariffClass_Name',
				allowBlank: false,
				anchor: '100%',
				disabledClass: 'field-disabled'
			},{
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['nachalo_deystviya'],
				name: 'CmpProfileTariff_begDT',
				allowBlank: false
			},{
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['okonchanie_deystviya'],
				name: 'CmpProfileTariff_endDT'
			},{
				fieldLabel: lang['znachenie'],
				xtype: 'numberfield',
				allowDecimals: true,
				allowBlank: false,
				anchor: '100%',
				decimalSeparator: ',',
				decimalPrecision: (getRegionNick() == 'ufa' ? 9 : 2),
				allowNegative: false,
				name: 'CmpProfileTariff__Value'
			}],
			reader: new Ext.data.JsonReader({},[
				{ name: 'CmpProfileTariff_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'TariffClass_id' },
				{ name: 'CmpProfileTariff_begDT' },
				{ name: 'CmpProfileTariff_endDT' },
				{ name: 'CmpProfileTariff__Value' }
			]),
			url: '/?c=LpuPassport&m=saveSmpTariff'
		});
		
		Ext.apply(this,{
			buttons: [
				{
					handler: function(){
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},{
					text: '-'
				},{
					handler: function(){
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL,
					onTabAction: function() {
						if ( !this.FormPanel.getForm().findField('LpuSectionProfile_id').disabled ) {
							this.FormPanel.getForm().findField('LpuSectionProfile_id').focus( true );
						}
					}.createDelegate(this)
				}
			],
			items: [this.FormPanel]
		});
		sw.Promed.swSmpTariffEditWindow.superclass.initComponent.apply(this, arguments);
	}
});