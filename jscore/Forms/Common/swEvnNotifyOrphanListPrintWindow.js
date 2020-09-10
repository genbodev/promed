/**
* swEvnNotifyOrphanListPrintWindow - Печать журнала Извещений/Направлений об орфанных заболеваниях
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Chebukin
* @version      
* @comment      Префикс для id компонентов ENOLPW (EvnNotifyOrphanListPrintWindow)
*
*/
sw.Promed.swEvnNotifyOrphanListPrintWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	height: 180,
	initComponent: function() {
	
		Ext.apply(Ext.form.VTypes, {
			daterange: function(val, field) {
				var date = field.parseDate(val);

				if(!date){
					return;
				}
				if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
					var start = Ext.getCmp(field.startDateField);
					start.setMaxValue(date);
					start.validate();
					this.dateRangeMax = date;
				} 
				else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
					var end = Ext.getCmp(field.endDateField);
					end.setMinValue(date);
					end.validate();
					this.dateRangeMin = date;
				}
				return true;
			}
		});
		
		Ext.apply(this, {
			buttons: [{
				handler : function(button, event) {
					var base_form = this.findById('EvnNotifyOrphanListPrintForm').getForm();

					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}

					printBirt({
						'Report_FileName': 'han_EvnOrphan_journal.rptdesign',
						'Report_Params': '&paramLpu=' + base_form.findField('Lpu_id').getValue() + '&paramBegDate=' + base_form.findField('begDT').getValue().dateFormat('d.m.Y') + '&paramEndDate=' + base_form.findField('endDT').getValue().dateFormat('d.m.Y'),
						'Report_Format': 'pdf'
					});
				}.createDelegate(this),
				iconCls : 'print16',
				text: lang['pechat']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('ENOLPW_begDT').focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [ new sw.Promed.FormPanel({
				autoHeight: true,
				border: false,
				frame: true,
				id: 'EvnNotifyOrphanListPrintForm',
				labelWidth: 100,
				layout: 'form',
				style: 'padding: 3px',
				items: [{
					allowBlank: false,
					fieldLabel: lang['mo'],
					id: 'ENOLPW_Lpu_id',
					name: 'Lpu_id',
					xtype: 'swlpucombo',
					disabled: !isAdmin,
					width: 350
				}, {
					fieldLabel: lang['nachalo_perioda'],
					id: 'ENOLPW_begDT',
					name: 'begDT',
					allowBlank: false,
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield',
					vtype: 'daterange',
					endDateField: 'ENOLPW_endDT'
				}, {
					fieldLabel: lang['konets_perioda'],
					id: 'ENOLPW_endDT',
					name: 'endDT',
					allowBlank: false,
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield',
					vtype: 'daterange',
					startDateField: 'ENOLPW_begDT'
				}]
			})]
		});
		
		sw.Promed.swEvnNotifyOrphanListPrintWindow.superclass.initComponent.apply(this, arguments);
		
	},
	maximizable: false,
	modal: false,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnNotifyOrphanListPrintWindow.superclass.show.apply(this, arguments);
		
		var base_form = this.findById('EvnNotifyOrphanListPrintForm').getForm();
		this.restore();
		this.center();
		
		base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		base_form.findField('begDT').setValue('01.01.'+String(getGlobalOptions().date).substr(6, 4));
		base_form.findField('endDT').setValue(getGlobalOptions().date);
		
		this.doLayout();
	},
	title: lang['pechat_spiska'],
	width: 500
});