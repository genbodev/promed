/**
* swLisSelectBioMaterialWindow - форма выбора биоматериала
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko (вынес из формы swLabServicesWindow) // original by Ilnoor
* @version      08.09.2013
*/

sw.Promed.swLisSelectBioMaterialWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 400,
	height: 200,
	modal: true,
	resizable: false,
	autoHeight: true,
	title: lang['vyibor_biomateriala'],
	plain: false,
	onCancel: function() {},	
	show: function() {
        sw.Promed.swLisSelectBioMaterialWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		var base_form = win.FormPanel.getForm();
		base_form.reset();

		if(arguments[0].formParams.UslugaComplex_Code){
			base_form.findField('UslugaComplex_Code').setContainerVisible(true);
		}else{
			base_form.findField('UslugaComplex_Code').setContainerVisible(false);
		}
		if(arguments[0].formParams.UslugaComplex_Name){
			base_form.findField('UslugaComplex_Name').setContainerVisible(true);
		}else{
			base_form.findField('UslugaComplex_Name').setContainerVisible(false);
		}

		if ( arguments[0].formParams ) {
			base_form.setValues(arguments[0].formParams);
		}

		var curDate = getValidDT(getGlobalOptions().date, '') || new Date();
		base_form.findField('RefMaterial_id').setBaseFilter(function (rec) {
			return (rec.get('RefMaterial_begDate') <= curDate && (Ext.isEmpty(rec.get('RefMaterial_endDate')) || (rec.get('RefMaterial_endDate') >= curDate)));
		});
	
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	doSave: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();
		
		if ( !base_form.isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					win.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		win.getLoadMask(lang['sozdanie_probyi']).show();
        base_form.submit({
            success:function (response, options) {
				win.getLoadMask().hide();
				win.callback();
				win.hide();
            },
			failure:function() {
				win.getLoadMask().hide();
			},
            url:'/?c=MedService&m=createMedServiceRefSample'
        });
	},
	
	initComponent: function() {
    	
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			xtype:'panel',
			layout:'form',
			frame: true,
			autoHeight: true,
			id:'bioMatSelForm',
			labelWidth: 120,
			items:[
				{
					name: 'MedService_id',
					xtype: 'hidden'					
				},
				{
					name: 'Usluga_ids',
					xtype: 'hidden'
				},
				{
					disabled: true,
					fieldLabel:lang['kod'],
					allowBlank: true,
					name:'UslugaComplex_Code',
					anchor: '100%',
					xtype:'textfield'
				},
				{
					disabled: true,
					fieldLabel:lang['naimenovanie'],
					allowBlank: true,
					name:'UslugaComplex_Name',
					anchor: '100%',
					xtype:'textfield'
				},
				{
					comboSubject: 'RefMaterial',
					allowBlank: false,
					editable: true,
					moreFields: [
						{name: 'RefMaterial_begDate', type: 'date', dateFormat: 'd.m.Y'},
						{name: 'RefMaterial_endDate', type: 'date', dateFormat: 'd.m.Y'}
					],
					fieldLabel: lang['biomaterial'],
					hiddenName: 'RefMaterial_id',
					anchor: '100%',
					xtype: 'swcommonsprcombo'
				},
				{
					comboSubject: 'ContainerType',
					allowBlank: true,
					editable: true,
					moreFields: [
						{name: 'Region'},
						{name: 'ContainerTypeColor_id'},
						{name: 'ContainerKind_id'},
					],
                    loadParams: {
                        params: {
                            where: 'where Region_id ='+getRegionNumber()
                        }
                    },
					fieldLabel: lang['container_type'],
					hiddenName: 'ContainerType_id',
					anchor: '100%',
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel:lang['proba'],
					allowBlank: true,
					name:'RefSample_Name',
					anchor: '100%',
					xtype:'textfield'
				},
				{
					fieldLabel:lang['isSeparateSample'],
					allowBlank: false,
					name:'UslugaComplexMedService_IsSeparateSample',
					anchor: '100%',
					xtype:'checkbox'
				}
			]
		});
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: this.doSave.createDelegate(this)
			}, 
			'-',
			{
				text: BTN_FRMCANCEL,
				iconCls: 'close16',
				handler: function(button, event) {
					win.onCancel();
					win.hide();
				}
			}],
			items: [this.FormPanel]

		});
		
		sw.Promed.swLisSelectBioMaterialWindow.superclass.initComponent.apply(this, arguments);
	}
});