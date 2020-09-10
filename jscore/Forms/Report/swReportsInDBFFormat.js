

sw.Promed.swReportsInDBFFormat = Ext.extend(sw.Promed.BaseForm, {
	id: 'swReportsInDBFFormat',
	height: 240,
	width: 340,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	title: langs('Отчеты в DBF формате'),

	show: function()
	{
		sw.Promed.swReportsInDBFFormat.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		this.ARMType = null;
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		wnd.formMode = 'iddle';

		base_form.reset();
		base_form.clearInvalid();
		wnd.TextPanel.getEl().dom.innerHTML = '';
		wnd.TextPanel.render();
	},
	createDBF: function() {
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

		params.Lpu_id = base_form.findField('Lpu_id').getValue();
		params.NumRep = base_form.findField('Form_id').getValue();
		var NumRep = base_form.findField('Table_id').getValue();
		params.NumTab = (NumRep instanceof Number || typeof NumRep === 'number') ? NumRep : '';
		params.YearRep = base_form.findField('YearRep').getValue();

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите, идет формирование отчета..." });
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
						var filename = answer.Link.replace("export/reports/", "");
						wnd.TextPanel.getEl().dom.innerHTML = '<div style="text-align:center;padding:12px;font-size:16px;"><a target="_blank" href="'+answer.Link+'">Скачать файл '+filename+'</a></div>';
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
			url: '/?c=ReportEngine&m=RunDBF'
		});
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
			autoWidth: true,
			autoheight: true,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			items: [{
					mode: 'local',
					allowBlank: false,
					fieldLabel: lang['mo'],
					hiddenName: 'Lpu_id',
					displayField: 'Lpu_Nick',
					valueField: 'Lpu_id',
					xtype:'swlpucombo',
					name: 'Lpu_id',
					width: 200,
					autoheight: true,
					store: new Ext.db.AdapterStore({
						dbFile: 'Promed.db',
						tableName: 'LpuSearch',
						key: 'Lpu_id',
						sortInfo: {field: 'Lpu_Nick'},
						autoLoad: false,
						fields: [{name: 'Lpu_id', mapping: 'Lpu_id'},{name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'}],
					}),
					listeners: {
						render: function (cmp) {
							cmp.setValue(getGlobalOptions().lpu_id);
						}
					},
				},
				{
					allowBlank: false,
					fieldLabel: 'Выбор формы',
					name: 'NumRep',
					queryMode: 'local',
					hiddenName: 'Form_id',
					displayField: 'Form_Name',
					valueField: 'Form_id',
					width: 200,
					store: new Ext.data.SimpleStore({
						autoLoad: false,
						key: 'Form_id',
						editable: false,
						fields: [
							{name:'Form_id',	type:'int'},
							{name:'Form_Name',	type:'string'}
						],
						data: [
							['12', 	'Форма №12'],
							['14', 	'Форма №14'],
							['141',	'Форма №14-ДС'],
							['16', 	'Форма №16-ВН'],
							['30',	'Форма №30'],
							['57', 	'Форма №57']
						]
					}),
					listeners: {
						select: function(cmp,value){
							var base_form = cmp.findForm().getForm();
							var NumTab = base_form.findField('Table_id');
							var NumRep = base_form.findField('Form_id').getValue();
							NumTab.allowBlank = (NumRep.inlist(['141','16','57'])) ? true : false;
							NumTab.clearValue();

							switch (NumRep){
								case 12:
									NumTab.getStore().loadData([['12', '1000', '1000'],['12', '2000', '2000'],['12', '3000', '3000'],['12', '4000', '4000']]);
									break;
								case 14:
									NumTab.getStore().loadData([['14', '2000', '2000'],['14', '4000', '4000'],['14', '4001', '4001']]);
									break;
								case 141:
									NumTab.getStore().loadData([]);
									break;
								case 16:
									NumTab.getStore().loadData([]);
									break;
								case 30:
									NumTab.getStore().loadData([['30', '2100', '2100'],['30', '3100', '3100'],['30', '306', '5300-5301']]);
									break;
								case 57:
									NumTab.getStore().loadData([['57', '57', 'Все']]);
									NumTab.setValue('Все');
									break;
							}

						}
					},
					xtype: 'swbaselocalcombo'
				},
				{
					allowBlank: false,
					fieldLabel: 'Выбор таблицы',
					name: 'NumTab',
					queryMode: 'local',
					hiddenName: 'Table_id',
					displayField: 'Table_Name',
					valueField: 'Table_id',
					width: 200,
					store: new Ext.data.SimpleStore({
						autoLoad: false,
						key: 'Table_id',
						editable: false,
						fields: [
							{name:'Form_id',	type:'int'},
							{name:'Table_id',	type:'int'},
							{name:'Table_Name',	type:'string'}
						],
						data: []
					}),
					xtype: 'swbaselocalcombo'
				},
				{
					allowBlank: false,
					fieldLabel: 'Год',
					name: 'YearRep',
					width: 200,
					listeners: {
						render: function (cmp) {
							var today = new Date();
							cmp.setValue(today.getFullYear()-1);
						}
					},
					xtype: 'swyearscombo'
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
						wnd.createDBF();
					}.createDelegate(this),
					iconCls: 'refresh16',
					text: lang['sformirovat']
				},{
					text: '-'
				},
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swReportsInDBFFormat.superclass.initComponent.apply(this, arguments);
	}
});
