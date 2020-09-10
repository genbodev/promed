/**
* swSmpEmergencyTeamSetStatusWindow - установка статуса выбранной бригаде
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Storozhev
* @version      27.06.2012
*/

sw.Promed.swSmpEmergencyTeamSetStatusWindow = Ext.extend(sw.Promed.BaseForm,{
	
	width: 500,
	height: 120,
	modal: true,
	callback: Ext.emptyFn,
	id: 'swSmpEmergencyTeamSetStatusWindow',
	title: lang['status_brigadyi_smp'],
	
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			id: 'smpEmergencyTeamSetStatusForm',
			url: '/?c=EmergencyTeam&m=setEmergencyTeamStatus',
			frame: true,
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'center',
			autoHeight: true,
			autoWidth: false,
			items: [{
				name: 'EmergencyTeam_id',
				value: 0,
				xtype: 'hidden'
			},{
				allowBlank: false,
				forceSelection: true,
				selectIndex: -1,
				selectOnFocus: true,

				
				id: 'EmergencyTeamStatus_combobox',
				xtype: 'swcommonsprcombo',
				fieldLabel: lang['status'],
				comboSubject: 'EmergencyTeamStatus',
				hiddenName: 'EmergencyTeamStatus_id',
				displayField: 'EmergencyTeamStatus_Name',
				disabledClass: 'field-disabled',
				anchor: '100%'
			}]
		});
		
		Ext.apply(this,{
			buttons:[{
				text: BTN_FRMSAVE,
				handler: function(){ this.setStatus(); }.createDelegate(this),
				iconCls: 'save16'
			},{
				text: '-'
			},
			{	text: BTN_FRMHELP,
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
		
		sw.Promed.swSmpEmergencyTeamSetStatusWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
        sw.Promed.swSmpEmergencyTeamSetStatusWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_peredanyi_parametryi'], function() {
				this.hide();
			}.createDelegate(this));
			return false;
		}
		
		this.FormPanel.getForm().reset();
		
		if ( !arguments[0].EmergencyTeam_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazana_brigada'], function() {
				this.hide();
			}.createDelegate(this));
		} else {
			this.FormPanel.getForm().findField('EmergencyTeam_id').setValue( arguments[0].EmergencyTeam_id );
		}

		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		var index = this.FormPanel.getForm().findField('EmergencyTeamStatus_id').getStore().findBy(function(rec) {
			if ( rec.get('EmergencyTeamStatus_id') == '' ) {
				return true;
			}
			else {
				return false;
			}
		});
		//Не нашел, где как можно по-другому убрать пустой элемент
		this.FormPanel.getForm().findField('EmergencyTeamStatus_id').getStore().removeAt(index);
		this.center();
	},
	
	setStatus: function() {

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение данных бригады СМП."});
		
		var base_form = this.FormPanel.getForm();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;	
		}
		loadMask.show();
		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_statusa_brigadyi_proizoshla_oshibka']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				if ( !action.result ) {
					sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_statusa_brigadyi_proizoshla_oshibka_poluchen_ne_vernyiy_otvet']);
					return false;
				}
				
				if ( action.result.EmergencyTeamStatus_id ) {
					base_form.findField('EmergencyTeamStatus_id').setValue( action.result.EmergencyTeamStatus_id );
					this.callback({
						EmergencyTeam_id: base_form.findField('EmergencyTeam_id').getValue(),
						EmergencyTeamStatus_id: base_form.findField('EmergencyTeamStatus_id').getValue()
					});
					this.hide();
					return true;
				}
				
				if ( action.result.Error_Msg ) {
					sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_statusa_brigadyi_proizoshla_oshibka_neobhodimyie_dannyie_otsutstvuyut']);
				}
				
				return false;
			}.createDelegate(this)
		});
	}
	
});