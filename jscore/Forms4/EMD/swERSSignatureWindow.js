/**
 * swERSSignatureWindow - Подписание ЭРС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swERSSignatureWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swERSSignatureWindow',
	autoShow: false,
	maximized: false,
	width: 550,
	autoHeight: true,
	resizable: false,
	maximizable: false,
	findWindow: false,
	closable: true,
	cls: 'arm-window-new arm-window-new-without-padding',
	title: 'ЭРС. Подписание',
	modal: true,
	header: true,
	constrain: true,
	backgroundProcessing: true,
	show: function(data) {
		var me = this;
		this.callParent(arguments);

		var base_form = me.formPanel.getForm();

		base_form.reset();
		me.action = data.action;

       	if (data.callback) {
			me.callback = data.callback;
		} else {
			me.callback = Ext6.emptyFn;
		}

		me.formPanel.enableEdit(true);
		me.EMDRegistry_ObjectName = data.EMDRegistry_ObjectName;
		
		me.setTitle(data.formTitle || 'ЭРС. Подписание');
		
		base_form.findField('Document_Name').setValue(me.EMDRegistry_ObjectName);

		me.isMOSign = data.isMOSign;

		/*if (data.isMOSign) {
			base_form.findField('EMDCertificate_id').getStore().proxy.extraParams['isMOSign'] = data.isMOSign;
		} else {
			base_form.findField('EMDCertificate_id').getStore().proxy.extraParams['isMOSign'] = null;
		}*/

		base_form.findField('EMDCertificate_id').getStore().load({
			callback: function() {
				var record = base_form.findField('EMDCertificate_id').getFirstRecord();
				if (record) {
					base_form.findField('EMDCertificate_id').setValue(record.get('EMDCertificate_id'));
				}
			}
		});
	},
	/**
	 * Формирование файла для подписи, получение хэша
	 */
	generateEMDRegistry: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}
		
		sw4.showInfoMsg({
			type: 'loading',
			hideDelay: 1000,
			text: 'Выполняется подписание документа'
		});
		
		setTimeout(function() {
			sw4.showInfoMsg({
				type: 'success',
				text: 'Подписание документа успешно завершено'
			});
			me.callback({
				preloader: true
			});
			me.hide();
		}, 1000);
	},
	initComponent: function() {
		var me = this;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			autoHeight: true,
			bodyPadding: '0 20',
			layout: 'form',
			url: '/?c=EMD&m=loadEMDSignWindow',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'Document_Name'},
						{name: 'MedStaffFact_id'},
                        {name: 'Lpu_id'},
                        {name: 'LpuBuilding_id'},
                        {name: 'LpuSection_id'},
                        {name: 'MedPersonal_id'}
					]
				})
			}),
			items: [{
				allowBlank: false,
				fieldLabel: 'Документ',
				name: 'Document_Name',
				xtype: 'displayfield'
			}, {
                name : 'MedStaffFact_id',
				xtype: 'hidden'
            }, {
				fieldLabel: 'Сертификат',
				allowBlank: false,
				name: 'EMDCertificate_id',
				xtype: 'swEMDCertificateCombo'
			}]
		});

		Ext6.apply(me, {
			items: [
				me.formPanel
			],
			buttons: ['->', {
				cls: 'buttonCancel',
				margin: 0,
				handler: function() {
					me.hide();
				},
				text: 'Отмена'
			}, {
				handler: function() {
					me.generateEMDRegistry();
				},
				cls: 'buttonAccept',
				margin: '0 19 0 0',
				text: 'Подписать'
			}]
		});

		this.callParent(arguments);
	}
});