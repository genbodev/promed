/**
* swEMDSignatureInfoWindow - окно просмотра информации о подписи
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      EMD
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      12.2019
*
*/
Ext6.define('emd.swEMDSignatureInfoWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEMDSignatureInfoWindow',
	autoHeight: true,
	layout: 'form',
	width: 500,
	title: langs('Информация о подписи'),
	show: function() 
	{
		this.callParent(arguments);
		
		var me = this;
		var base_form = me.FormPanel.getForm();
		
		this.EMDSignatures_id = null;
		if (arguments[0].EMDSignatures_id) {
			this.EMDSignatures_id = arguments[0].EMDSignatures_id;
		}
		base_form.reset();
		if (this.EMDSignatures_id) {
			me.getLoadMask(LOAD_WAIT).show();
			base_form.load({
				params: {
					EMDSignatures_id: me.EMDSignatures_id
				},
				success: function() {
					me.getLoadMask().hide();
				},
				failure: function() {
					me.getLoadMask().hide();
				}
			});
		}
	},
	initComponent: function() 
	{
		var me = this;

		this.FormPanel = Ext6.create('Ext6.form.Panel', {
			bodyPadding: '0 20',
			border: false,
			labelAlign: 'right',
			url: '/?c=EMD&m=getEMDSignaturesInfo',
			layout: 'anchor',
			items: [{
				xtype: 'textfield',
				name: 'EMDCertificate_CommonName',
				anchor: '100%',
				readOnly: true,
				fieldLabel: langs('Пользователь')
			}, {
				xtype: 'textfield',
				name: 'EMDVersion_VersionNum',
				anchor: '100%',
				readOnly: true,
				fieldLabel: langs('Версия')
			}, {
				xtype: 'textfield',
				name: 'Signatures_insDT',
				anchor: '100%',
				readOnly: true,
				fieldLabel: langs('Дата')
			}, {
				xtype: 'textarea',
				name: 'Signatures_Hash',
				anchor: '100%',
				readOnly: true,
				fieldLabel: langs('Хэш')
			}],
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'EMDSignatures_id' },
						{ name: 'EMDCertificate_CommonName' },
						{ name: 'EMDVersion_VersionNum' },
						{ name: 'Signatures_insDT' },
						{ name: 'Signatures_Hash' }
					]
				})
			})
		});

		Ext6.apply(this, {
			autoHeight: true,
			items: [this.FormPanel],
			buttons: ['->', {
				cls: 'button-primary',
				margin: '0 5',
				handler: function() {
					me.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		this.callParent(arguments);
	}
});