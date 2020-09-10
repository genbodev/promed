/**
* swEgissoReceptExportWindow - Экспорт МСЗ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
*/

sw.Promed.swEgissoReceptExportWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Экспорт МСЗ',
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	modal: true,
	width: 500,
	height: 200,
	doExport: function() {
	
		var win = this,
			base_form = this.FormPanel.getForm();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = base_form.getValues();
        params.EGISSOReceptExport_isNew = params.EGISSOReceptExport_isNew ? 2 : 1;
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		Ext.Ajax.request({
			url: '/?c=EGISSOReceptExport&m=save',
            method: 'POST',
			params: params,
			failure: function() {
				loadMask.hide();
			},
			success: function(result) {
				loadMask.hide();
				
				var obj = Ext.util.JSON.decode(result.responseText);
				if( obj.length && obj[0].EGISSOReceptExport_id ) {
					win.callback();
					win.hide();
				}
			}
		});
	},
	show: function() {
		sw.Promed.swEgissoReceptExportWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = this.FormPanel.getForm();
		
		win.callback = arguments[0].callback || Ext.emptyFn;
		
		base_form.reset();
		
		var yDate = new Date();
		yDate.setDate(yDate.getDate() - 1);
		
		base_form.findField('EGISSOReceptExport_begDT').setValue( yDate );
		base_form.findField('EGISSOReceptExport_endDT').setValue( yDate );
		
		base_form.findField('EGISSOReceptExport_begDT').setMaxValue( yDate );
		base_form.findField('EGISSOReceptExport_endDT').setMaxValue( yDate );
	},
	initComponent: function() {
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({	
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 200,
			items: [{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					fieldLabel: 'Начало периода экспорта',
					name: 'EGISSOReceptExport_begDT',
					allowBlank: false,
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					width: 120,
					xtype: 'swdatefield'
				}, {
					fieldLabel: 'Окончание периода экспорта',
					name: 'EGISSOReceptExport_endDT',
					allowBlank: false,
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					width: 120,
					xtype: 'swdatefield'
				}, {
					xtype: 'checkbox',
					labelSeparator: '',
					name: 'EGISSOReceptExport_isNew',
					boxLabel: 'Только новые'
				}]
			}]
		});
		
		Ext.apply(this, {	
			buttons: [{
				handler: function() {
					this.doExport();
				}.createDelegate(this),
				iconCls: 'database-export16',
				text: 'Экспорт'
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swEgissoReceptExportWindow.superclass.initComponent.apply(this, arguments);
	}
});