/**
 * swExportAttachmentsApplicationsWindow - окно Экспорт прикреплений / заявлений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 */
/*NO PARSE JSON*/
//swExportAttachmentsApplicationsWindow EAAW

sw.Promed.swExportAttachmentsApplicationsWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swExportAttachmentsApplicationsWindow',
	width: 470,
	autoHeight: true,
	modal: true,
	title: langs('Экспорт прикреплений / заявлений'),
	ARMType: getGlobalOptions().curARMType,
	doExport: function() {
		var wnd = this;

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

		var params = {};

		params.ARMType = this.ARMType;
		params.Lpu_id = base_form.findField('Lpu_id').getValue();
		params.begDate = base_form.findField('ExportDateRange').getValue1();
		params.endDate = base_form.findField('ExportDateRange').getValue2();
		params.PackageNumber = base_form.findField('PackageNumber').getValue();
		params.OrgSMO_id = base_form.findField('OrgSMO_id').getValue();

		params.begDate = Ext.util.Format.date(params.begDate, 'd.m.Y');
		params.endDate = Ext.util.Format.date(params.endDate, 'd.m.Y');

		this.LinkPanel.setLink(null);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result && !Ext.isEmpty(action.result.link)) {
					if(!action.result.link){
						Ext.Msg.alert('Внимание','По Вашему запросу записей не найдено');
					}else{
						this.LinkPanel.setLink(action.result.link);
					}
				}
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	resetFormParams: function() {
		var base_form = this.FormPanel.getForm();

		base_form.reset();
		this.LinkPanel.setLink(null);

		var ExportDateRange = base_form.findField('ExportDateRange');
		var LpuCombo = base_form.findField('Lpu_id');

		var date = new Date().format('d.m.Y');
		//var dateMin = new Date(new Date().setDate(new Date().getDate() - 25)).format('d.m.Y');
		var dateMin = new Date(new Date().getFullYear(), new Date().getMonth() - 1, 25).format('d.m.Y');
		ExportDateRange.setValue(dateMin+' - '+date);
		LpuCombo.setValue(getGlobalOptions().lpu_id);
		LpuCombo.setDisabled( (this.ARMType != 'superadmin') );
	},
	filterOrgSMOCombo: function() {
		var base_form = this.FormPanel.getForm();
		var OrgSMOCombo = base_form.findField('OrgSMO_id');
		var Region_id = getRegionNumber();
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == Region_id);
		});
	},
	show: function() {
		sw.Promed.swExportAttachmentsApplicationsWindow.superclass.show.apply(this, arguments);
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		this.resetFormParams();
		this.filterOrgSMOCombo();
	},

	initComponent: function() {
		this.LinkPanel = new Ext.Panel({
			id: 'EAAW_TextPanel',
			border: false,
			style: 'margin: 5px 0 10px 125px; font-size: 12px;',
			tpl: new Ext.XTemplate([
				'<a target="_blank" href="{link}">',
				'Ссылка на скачивание файла',
				'</a>'
			]),
			setLink: function(link) {
				var panel = this.LinkPanel;
				if (Ext.isEmpty(link)) {
					panel.body.dom.innerHTML = '';
				} else {
					panel.tpl.overwrite(panel.body, {link: link});
				}
				this.syncShadow();
			}.createDelegate(this)
		});

		this.FormPanel = new Ext.FormPanel({
			id: 'EAAW_FormPanel',
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			bodyStyle: 'margin-top: 10px;',
			url: '/?c=AttachmentCheck&m=exportAttachmentsApplications',
			items: [
				{
					xtype: 'swlpucombo',
					hiddenName: 'Lpu_id',
					fieldLabel: 'МО',
					allowBlank: false,
					width: 280
				}, {
					allowBlank: false,
					xtype: 'daterangefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					name: 'ExportDateRange',
					fieldLabel: 'Период',
					width: 180
				}, {
					width: 280,
					fieldLabel: 'СМО',
					hiddenName: 'OrgSMO_id',
					allowBlank: false,
					listWidth: 450,
					lastQuery: '',
					minChars: 1,
					withoutTrigger: true,
					xtype: 'sworgsmocombo'
				}, {
					allowBlank: false,
					fieldLabel: langs('Номер пакета'),
					//plugins: [ new Ext.ux.InputTextMask('99', false) ],
					name: 'PackageNumber',
					width: 180,
					xtype: 'textfield'
				},
				this.LinkPanel
			]
		});

		Ext.apply(this,
			{
				buttons: [
					{
						handler: function () {
							this.doExport();
						}.createDelegate(this),
						iconCls: 'database-export16',
						id: 'EPMEW_ExportButton',
						text: langs('Сформировать')
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
				items: [
					this.FormPanel
				]
			});

		sw.Promed.swExportAttachmentsApplicationsWindow.superclass.initComponent.apply(this, arguments);
	}
});