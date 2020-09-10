/**
 * @package
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Melentiev
 * @version      22.04.2010
 */

sw.Promed.swTransfusionMediaJournalForm  = Ext.extend(sw.Promed.BaseForm, {
	id 		: 'swTransfusionMediaJournalForm',
	title      : 'Печать журнала регистрации переливания трансфузионных сред (009/у)',
	autoWidth  : true,
	autoHeight  : true,
	resizable  : false,
	plain      : true,
	modal      : true,
	autoScroll : false,
	closeAction: 'hide',
	show: function() {
		sw.Promed.swTransfusionMediaJournalForm.superclass.show.apply(this, arguments);
		this.center();
	},
	initComponent: function() {
		Ext.apply(this, {
			items      : [
				new Ext.form.FormPanel({
					id: 'TransfusionJournal_begDate_endDate',
					frame: true,
					items: {
						fieldLabel: '<b>Период дат журнала регистрации <span style="color:red">*</span></b>',
						labelStyle: 'width:240px',
						allowBlank: false,
						name: 'TransfusionJournal_begDate_endDate',
						plugins: [
							new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
						],
						value: new Date().format('d.m.Y') + ' - ' + new Date().format('d.m.Y'),
						width  : 180,
						xtype: 'daterangefield'
					}
				})],
			buttons    : [
				{
					text    : 'Принять',
					iconCls : 'reception-accept16',
					handler : function(){
						var lpu_id = getGlobalOptions().lpu_id;
						var date_period = this.findById('TransfusionJournal_begDate_endDate').getForm().findField('TransfusionJournal_begDate_endDate').value.split(' - ');
						printBirt({
							'Report_FileName': 'f009_TransfusionJournal.rptdesign',
							'Report_Params': '&paramLpu='+lpu_id+'&paramBegDate='+date_period[0]+'&paramEndDate='+date_period[1],
							'Report_Format': 'pdf'
						});
						getWnd('swTransfusionMediaJournalForm').hide();
					},
					scope   : this
				},
				{
					text    : 'Помощь',
					iconCls:'help16',
					handler : function(){
						ShowHelp(langs('Журнал регистрации переливания трансфузионных сред (009/у)'));
					}
				},
				{
					text    : 'Отмена',
					iconCls   : 'cancel16',
					handler : function(){
						getWnd('swTransfusionMediaJournalForm').hide();
					}
				}
			]
		});
		sw.Promed.swTransfusionMediaJournalForm.superclass.initComponent.apply(this, arguments);
	}
});





