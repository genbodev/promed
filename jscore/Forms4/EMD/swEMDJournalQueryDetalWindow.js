/**
 * swEMDJournalQueryDetalWindow - Форма показа деталей записи в журнале РЭМД
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      EMD
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 */
Ext6.define('emd.swEMDJournalQueryDetalWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEMDJournalQueryDetalWindow',
	autoShow: false,
	maximized: false,
	cls: 'arm-window-new',
	title: 'Просмотр записи РЭМД ЕГИСЗ',
	constrain: true,
	header: true,
	layout: 'border',
	width: 900,
	height: 650,
	autoHeight:true,
	modal: true,
	header: true,
	constrain: true,
	backgroundProcessing: true,
	show: function(data) {
		var me = this;
		this.callParent(arguments);
		if (!data || !data.EMDJournalQuery_id ) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
				me.hide();
			});
			return false;
		}
		var base_form = me.formPanel.getForm();

		base_form.reset();
		base_form.load({
			params: {
				EMDJournalQuery_id: data.EMDJournalQuery_id,
			}
		});
	},
	//удалим значек помощи
	addHelpButton: function() {},
	initComponent: function() {
		var me = this;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			width:900,
			autoHeight: true,
			bodyPadding: '0 20',
			layout: 'form',
			url: '/?c=EMD&m=loadEMDJournalQueryDetal',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
					{name: 'EMDJournalQuery_id', type: 'int'},
					{name: 'EMDRegistry_id'},
					{name: 'EMDVersion_FilePath'},
					{name: 'EMDRegistry_Num'},	
					{name: 'EMDDocumentTypeLocal_id'},
					{name: 'EMDQueryType_id'},
					{name: 'EMDJournalQuery_OutDT'},
					{name: 'EMDQueryStatus_id'},
					{name: 'EMDJournalQuery_OutParam'},
					{name: 'EMDJournalQuery_InParam'},
					{name: 'EMDJournalQuery_InDT'},
					{name: 'Lpu_Name'}
					]
				})
			}),
			items: [{
				fieldLabel: 'ID запроса',
				name: 'EMDJournalQuery_id',
				xtype: 'textfield',
				disabled: true
			}, {
				fieldLabel: 'UUID ЭМД',
				name: 'EMDRegistry_id',
				xtype: 'textfield',
				disabled: true
			},{
                xtype: 'displayfield',
                name : 'EMDVersion_FilePath',
                fieldLabel: 'ЭМД',
                allowBlank: false,
				autoload: false,
				renderer: function(value){
					if (value){
						return '<a href='+value+' target="_blank">'+value+'</a>';
					}
					return "";
				}
            },{
                xtype: 'textfield',
                name : 'EMDRegistry_Num',
                fieldLabel: 'Рег №',
                disabled: true
            },{
                xtype: 'swEMDDocumentTypeLocalRemote',
                name : 'EMDDocumentTypeLocal_id',
                disabled: true
            },{
                xtype: 'commonSprCombo',
                name : 'EMDQueryType_id',
				comboSubject: 'EMDQueryType',
                fieldLabel: 'Тип запроса',
                disabled: true
            },{
                xtype: 'textfield',
                name : 'EMDJournalQuery_OutDT_RU',
                fieldLabel: 'Дата и время запроса',
                disabled: true
            },{
                xtype: 'swEMDQueryStatus',
                name : 'EMDQueryStatus_id',
                fieldLabel: 'Статус запроса',
				disabled:true
            },{
                xtype: 'textareafield',
                name : 'EMDJournalQuery_OutParam',
                fieldLabel: 'Данные запроса'
            },{
                xtype: 'textareafield',
                name : 'EMDJournalQuery_InParam',
                fieldLabel: 'Данные ответа'
            },{
                xtype: 'swLpuCombo',
                name : 'Lpu_id',
				displayField:'Lpu_Name',
				disabled:true

            }
		]
	});

		Ext6.apply(me, {
			items: [
				me.formPanel
			],
			buttons: [
				'->', {
				cls: 'buttonCancel',
				margin: 0,
				handler: function() {
					me.hide();
				},
				text: 'Закрыть'
			}]
		});


		this.callParent(arguments);
	}
});
