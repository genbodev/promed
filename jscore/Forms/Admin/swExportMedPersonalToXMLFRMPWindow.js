/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 27.05.15
 * Time: 10:28
 * To change this template use File | Settings | File Templates.
 */

/*NO PARSE JSON*/

sw.Promed.swExportMedPersonalToXMLFRMPWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swExportMedPersonalToXMLFRMPWindow',
	width: 1024,
	height: 200,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	title: langs('Выгрузка регистра медработников для ФРМР'),

	createXML: function() {
		if ( this.formMode == 'export' ) {
			return false;
		}

		this.formMode = 'export';

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if (!base_form.isValid())
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						wnd.formMode = 'iddle';
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return;
		}

		var params = new Object();

		if ( !Ext.isEmpty(base_form.findField('date_from').getValue()) ) {
			params.date_from = base_form.findField('date_from').getValue().format('d.m.Y');
		};

		if ( !Ext.isEmpty(base_form.findField('on_date').getValue()) ) {
			params.on_date = base_form.findField('on_date').getValue().format('d.m.Y');
		};

		params.Lpu_id = base_form.findField('Lpu_id').getValue();

		if (isUserGroup(['SuperAdmin', 'MIACSuperAdmin', 'MIACAdminFRMR'])) {
			params.Lpu_ids = params.Lpu_id;
			params.Lpu_id = '';
		}

		params.ARMType = this.ARMType;

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите, идет формирование данных..." });
		loadMask.show();

		Ext.Ajax.request({
			timeout: 3600000,
			failure: function(response, options) {
				wnd.formMode = 'iddle';
				loadMask.hide();
			},
			params: params,
			success: function(response, action) {
				wnd.formMode = 'iddle';
				loadMask.hide();

				if ( response.responseText ) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if ( answer.success ) {
						wnd.TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="'+answer.Link+'">Скачать и сохранить файл</a>';
						wnd.TextPanel.render();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], !Ext.isEmpty(answer.Error_Msg) ? answer.Error_Msg : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			url: '/?c=MedPersonal&m=exportMedPersonalToXMLFRMP'
		});
	},

	show: function()
	{
		sw.Promed.swExportMedPersonalToXMLFRMPWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		this.ARMType = null;
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		wnd.formMode = 'iddle';

		base_form.reset();
		base_form.clearInvalid();
		base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		var new_date = new Date();
		if(getGlobalOptions().region.nick != 'kareliya')
			var date_from = '01.' + ("0" + (new_date.getMonth() + 1)).slice(-2) + '.'+ new_date.getFullYear();
		else
			var date_from = '01.01.1900';
		base_form.findField('date_from').setValue(date_from);
		base_form.findField('on_date').setValue(new_date);
		if (!isUserGroup(['SuperAdmin', 'MIACSuperAdmin', 'MIACAdminFRMR'])) {
			wnd.FormPanel.getForm().findField('Lpu_id').disable();
		}
		wnd.TextPanel.getEl().dom.innerHTML = '';
		wnd.TextPanel.render();
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			html: ''
		});

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			items: [new Ext.ux.Andrie.Select(
				{
					multiSelect: true,
					mode: 'local',
					allowBlank: true,
					emptyText: lang['vse'],
					fieldLabel: lang['mo'],
					hiddenName: 'Lpu_id',
					displayField: 'Lpu_Nick',
					valueField: 'Lpu_id',
					xtype:'swlpucombo',
					name: 'Lpu_id',
					width: 880,
					store: new Ext.db.AdapterStore({
						
						dbFile: 'Promed.db',
						tableName: 'LpuSearch',
						key: 'Lpu_id',
						sortInfo: {field: 'Lpu_Nick'},
						autoLoad: false,
						fields: [
							{name: 'Lpu_id', mapping: 'Lpu_id'},
							{name: 'Lpu_IsOblast', mapping: 'Lpu_IsOblast'},
							{name: 'Lpu_Name', mapping: 'Lpu_Name'},
							{name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'},
							{name: 'Lpu_Ouz', mapping: 'Lpu_Ouz'},
							{name: 'Lpu_RegNomC', mapping: 'Lpu_RegNomC'},
							{name: 'Lpu_RegNomC2', mapping: 'Lpu_RegNomC2'},
							{name: 'Lpu_RegNomN2', mapping: 'Lpu_RegNomN2'},
							{name: 'Lpu_DloBegDate', mapping: 'Lpu_DloBegDate'},
							{name: 'Lpu_DloEndDate', mapping: 'Lpu_DloEndDate'},
							{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
							{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate', type: 'string'},
							{name: 'Lpu_IsAccess', mapping: 'Lpu_IsAccess'}
						],
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{[(values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыта " + values.Lpu_EndDate + ")" : values.Lpu_Nick ]}&nbsp;',
						'</div></tpl>'
					)
				}),
				{
					allowBlank: false,
					fieldLabel: 'Начиная с',
					format: 'd.m.Y',
					name: 'date_from',
					xtype: 'swdatefield'
				},
				{
					border: false,
					hidden: (getRegionNick() != 'kareliya'),
					layout: 'form',
					items: [{
						allowBlank: (getRegionNick() != 'kareliya'),
						fieldLabel: 'Дата',
						format: 'd.m.Y',
						name: 'on_date',
						xtype: 'swdatefield'
					}]
				},
				wnd.TextPanel
			]
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function () {
						wnd.createXML();
					}.createDelegate(this),
					iconCls: 'refresh16',
					text: lang['sformirovat']
				},{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swExportMedPersonalToXMLFRMPWindow.superclass.initComponent.apply(this, arguments);
	}
});
