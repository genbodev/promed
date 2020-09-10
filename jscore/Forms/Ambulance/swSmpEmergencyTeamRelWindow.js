/**
* swSmpEmergencyTeamRelWindow - форма выбора бригады
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dyomin Dmitry
* @version      18.08.2013
*/

sw.Promed.swSmpEmergencyTeamRelWindow = Ext.extend(sw.Promed.BaseForm,{
	
	id: 'swSmpEmergencyTeamRelWindow',
	
	title: lang['svyazyivanie_brigad'],
	
	api: null, // API которая будет привязана
	
	modal: true,
	
	width: 700,
	
	autoHeight: true,
	
	resizable: true,
	
	plain: false,
	
	closable: false,
	
	callback: Ext.emptyFn,
	
	regionNumber: null,

	onCancel: function() {
		this.hide();
	},
	
	fkCombo: null,
	
	etWialon: function(){
		var remoteEmergencyTeamStore = new Ext.data.JsonStore({
			url: '?c=Wialon&m=getAllAvlUnitsForMerge',
			autoLoad: false,
			root: 'data',
			key: 'id',
			fields: [{name:'id',type:'int'},{name:'nm',type:'string'}]
		});
		
		var combo = new Ext.form.ComboBox({
			mode: 'local',
			valueField: 'id',
			displayField: 'nm',
			lazyRender: true,
			editable: false,
			forceSelection: true,
			triggerAction: 'all',
			store: remoteEmergencyTeamStore
		});
		
		return combo;
	},
	
	getLoadMask: function(){
		if ( !this.loadMask ){
			this.loadMask = new Ext.LoadMask(Ext.get(this.id),{
				msg: lang['zagruzka_dannyih']
			});
		}
		return this.loadMask;
	},
	
	initComponent: function() {
		
		this.fkCombo = this.etWialon();
		
		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			border: false,
			autoLoadData: false,
			dataUrl: '?c=Wialon&m=loadEmergencyTeamRelList',
			saveAtOnce: false, // отключаем сохранение после выбора значения в гриде
			stringfields: [
				{ header: 'ID', name: 'EmergencyTeam_id', key: true },
				{ header: lang['nomer_brigadyi_promed'], name: 'EmergencyTeam_Num', type: 'string', width: 150 },
				{ header: lang['nomer_avto_promed'], name: 'EmergencyTeam_CarNum', type: 'string', width: 150 },
				{ header: lang['brigada_wialon'], name: 'WialonEmergencyTeamId', editor: this.fkCombo, renderer: Ext.util.Format.comboRenderer( this.fkCombo ), autoexpand: true }
			],
			actions: [
				{name: 'action_save', url: '?c=Wialon&m=saveEmergencyTeamRel'}
			],
			onRecordSave: function(){
				Ext.Msg.alert(lang['soobschenie'], lang['dannyie_sohranenyi']);
				this.hide();
			}.createDelegate(this)
		});

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['sohranit'],
				iconCls: 'ok16',
				handler: function(){
					this.GridPanel.saveRecord();
				},
				scope: this
			},
			'-',
			{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: function(){
					this.ownerCt.hide();
				}
			}],
			items: [{
				layout: 'column',
				autoHeight: true,
				items: [ this.GridPanel ]
			}]
		});

		sw.Promed.swSmpEmergencyTeamRelWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
		
		// First of all we loading a combobox store, cause the grid store data
		// may loading before it and we doesn't see a values.
		this.getLoadMask().show();
		this.fkCombo.getStore().load({
			callback: function(){
				this.GridPanel.getGrid().getStore().load({
					callback: function(){
						this.getLoadMask().hide();
					},
					scope: this
				});
			},
			scope: this
		});
		
        sw.Promed.swSmpEmergencyTeamRelWindow.superclass.show.apply(this, arguments);
	}
});
