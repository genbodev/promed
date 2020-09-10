/**
 * swPersonPregnancyRegisterPrintWindow - окно печати реестра беременных и родильниц
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			22.09.2016
 */
/*NO PARSE JSON*/

sw.Promed.swPersonPregnancyRegisterPrintWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonPregnancyRegisterPrintWindow',
	width: 500,
	autoHeight: true,
	modal: true,
	title: 'Печать реестра беременных и родильниц',

	doPrint: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var Lpu_id = base_form.findField('Lpu_id').getValue();
		var begDate = Ext.util.Format.date(base_form.findField('dateRange').getValue1(), 'd.m.Y');
		var endDate = Ext.util.Format.date(base_form.findField('dateRange').getValue2(), 'd.m.Y');

		var Report_Params = [
			'paramBegDate='+begDate,
			'paramEndDate='+endDate
		];
		if (Lpu_id > 0) {
			Report_Params.push('paramLpu='+Lpu_id);
		}

		printBirt({
			'Report_FileName': 'Pregnancy_Reestr.rptdesign',
			'Report_Params': '&'+Report_Params.join('&'),
			'Report_Format': 'pdf'
		});
	},

	show: function() {
		sw.Promed.swPersonPregnancyRegisterPrintWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		var lpu_combo = base_form.findField('Lpu_id');

		if (lpu_combo.getStore().find('Lpu_id', -1) < 0) {
			var allLpuRecord = new Ext.data.Record({
				'Lpu_id': -1, 'Lpu_Name': '', 'Lpu_Nick': lang['vse']
			});
			lpu_combo.getStore().insert(0, [allLpuRecord]);
			lpu_combo.getStore().commitChanges();
		}

		var BirthMesLevel_Code = getGlobalOptions().birth_mes_level_code;

		lpu_combo.setValue(getGlobalOptions().lpu_id);
		lpu_combo.setDisabled(Ext.isEmpty(BirthMesLevel_Code) || BirthMesLevel_Code == 1);

		var date = new Date();
		var begDateStr = new Date(date.getFullYear(), date.getMonth(), 1).format('d.m.Y');
		var endDateStr = new Date(date.getFullYear(), date.getMonth() + 1, 0).format('d.m.Y');

		base_form.findField('dateRange').setValue(begDateStr+' - '+endDateStr);

		base_form.items.each(function(f){f.validate()});
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'PPRPW_FormPanel',
			labelAlign: 'right',
			labelWidth: 80,
			items: [{
				allowBlank: false,
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_id',
				fieldLabel: 'МО',
				width: 340
			}, {
				allowBlank: false,
				xtype: 'daterangefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				name: 'dateRange',
				fieldLabel: 'Период',
				width: 340
			}]
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doPrint();
					}.createDelegate(this),
					iconCls: 'print16',
					id: 'PPRPW_SaveButton',
					text: 'Печать'
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swPersonPregnancyRegisterPrintWindow.superclass.initComponent.apply(this, arguments);
	}
});