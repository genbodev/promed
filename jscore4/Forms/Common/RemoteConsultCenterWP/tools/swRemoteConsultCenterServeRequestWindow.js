/*
 * Окно обслуживания заявки на удаленную консультацию
*/



Ext.define('common.RemoteConsultCenterWP.tools.swRemoteConsultCenterServeRequestWindow', {
	extend: 'Ext.window.Window',
	alias: 'widget.swRemoteConsultCenterServeRequestWindow',
	autoShow: true,
	maximized: true,
	id:  'RemoteConsultCenterServeResearchWindow',
	refId: 'RemoteConsultCenterServeResearchWindow',
	renderTo: Ext.getCmp('inPanel').body,
	closable: true,
	constrain: true,
	border: false,
	layout: {
	type: 'fit'
	},	
	initComponent: function() {
	var window = this;
		
		this.EvnXmlPanel = Ext.create('sw.Promed.EvnXmlPanel',{
			autoHeight: true,
			border: false,
			collapsible: true,
			loadMask: {},
			id: 'EUFREF_TemplPanel',
			layout: 'form',
			title: 'Протокол функциональной диагностики',
			ownerWin: this,
			options: {
				XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, // только протоколы услуг
				EvnClass_id: 47 // документы и шаблоны только категории параклинические услуги
			}
//			,
//			onAfterLoadData: function(panel){
//				var bf = this.findById('EvnUslugaFuncRequestEditForm').getForm();
//				bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
//				panel.expand();
//				this.syncSize();
//				this.doLayout();
//			}.bind(this),
//			onAfterClearViewForm: function(panel){
//				var bf = this.findById('EvnUslugaFuncRequestEditForm').getForm();
//				bf.findField('XmlTemplate_id').setValue(null);
//			}.bind(this),
//			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
//			onBeforeCreate: function (panel, method, params) {
//				if (!panel || !method || typeof panel[method] != 'function') {
//					return false;
//				}
//				var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
//				var evn_id_field = base_form.findField('EvnUslugaPar_id');
//				var evn_id = evn_id_field.getValue();
//				if (evn_id && evn_id > 0) {
//					// услуга была создана ранее
//					// все базовые параметры уже должно быть установлены
//					panel[method](params);
//				} else {
//					this.doSave({
//						openChildWindow: function() {
//							panel.setBaseParams({
//								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
//								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
//								Server_id: base_form.findField('Server_id').getValue(),
//								Evn_id: evn_id_field.getValue()
//							});
//							panel[method](params);
//						}.bind(this)
//					});
//				}
//				return true;
//			}.bind(this)
		});
		
		
		Ext.applyIf(window, {
			items: [
				{
					border: false,
					xtype: 'panel',
					id: 'RemoteConsultCenterServeResearchWindow_BaseForm',
					layout:'column',
					items: [{
						columnWidth: .5,
						items: this.EvnXmlPanel
					},{
						columnWidth: .5
					}]
						
				}
			]
		});
		
		
		window.callParent(arguments);
	}
	
	,show: function() {
		this.callParent(arguments);
				
	}
		

});

