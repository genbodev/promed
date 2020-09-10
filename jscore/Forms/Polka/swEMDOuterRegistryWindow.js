
sw.Promed.swEMDOuterRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: "Реестр внешних ЭМД",
	width: 600,
	autoHeight: true,
	modal: true,
	show: function() {
		var me = this,
			base_form = me.filtersPanel.getForm();
		sw.Promed.swEMDOuterRegistryWindow.superclass.show.apply(this, arguments);
		me.Default_Person_id = null;
		me.Person_Snils = '';

		if(arguments && arguments[0]) {
			if(arguments[0]['Person_id']) me.Default_Person_id = arguments[0]['Person_id'];
			me.Default_PersonFullName = '';
			if(arguments[0]['Person_Surname']) me.Default_PersonFullName +=''+arguments[0]['Person_Surname'];
			if(arguments[0]['Person_Firname']) me.Default_PersonFullName +=' '+arguments[0]['Person_Firname'];
			if(arguments[0]['Person_Secname']) me.Default_PersonFullName +=' '+arguments[0]['Person_Secname'];
			if(arguments[0]['Person_Birthday']) me.Default_PersonFullName +=', '+arguments[0]['Person_Birthday'].dateFormat('d.m.Y');
			
			if(arguments[0]['Person_Snils']) me.Person_Snils = arguments[0]['Person_Snils'];
		}
		
		me.Person_id = me.Default_Person_id;
		if(me.Person_id) {
			base_form.findField('PersonFullName').setValue(me.Default_PersonFullName);
			me.emdgrid.loadData({
				globalFilters: { Person_id: me.Person_id }
			});
		} else base_form.findField('PersonFullName').setValue('');
		base_form.findField('PersonFullName').fireEvent('change', base_form.findField('PersonFullName'), base_form.findField('PersonFullName').getValue());
		
		var disablePersonField = false;
		if(arguments[0]['parentform'] == 'EMK') {
			disablePersonField = true;
		}
		me.personfield.setDisabled(disablePersonField);
	},
	initComponent: function()
	{
		var me = this;
		
		me.personfield = new Ext.form.TriggerField({
			name: 'PersonFullName',
			width: 360,
			fieldLabel: 'Пациент',
			onTriggerClick: function() {
				var me = this,
					base_form = this.filtersPanel.getForm();
				if(base_form.findField('PersonFullName').disabled) return;
				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						console.log(person_data);
						me.Person_id = person_data.Person_id;
						var bdt = person_data.Person_Birthday ? ', '+person_data.Person_Birthday.dateFormat('d.m.Y') : '';
						var newValuePersonFullName = person_data.PersonSurName_SurName + ' ' + person_data.PersonFirName_FirName + ' ' + person_data.PersonSecName_SecName+bdt;
						base_form.findField('PersonFullName').setValue(newValuePersonFullName);
						
						getWnd('swPersonSearchWindow').hide();
					}.createDelegate(this),
					searchMode: 'all'
				});
			}.createDelegate(this),
			triggerClass: 'x-form-search-trigger',
			readOnly: true,
			tabIndex: TABINDEX_ESTEF + 1,
			
			listeners: {
				'change': function(field, newVal, oldVal) {
					me.searchBtn.setDisabled(Ext.isEmpty(me.Person_id));
				}
			}
		});
		
		me.emdgrid = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add', text: langs('Запросить файл ЭМД'), tooltip: langs('Запросить файл ЭМД'), icon: 'img/icons/actions16.png',
					handler: function () {
						var record = me.emdgrid.getGrid().getSelectionModel().getSelected();
						if (record && record.get('EMDOuterRegistry_emdrId')) {
							me.getLoadMask('Запрос файла ЭМД...').show();
							Ext.Ajax.request({
								url: '/?c=EMD&m=demandContent',
								params: {
									EMDOuterRegistry_emdrId: record.get('EMDOuterRegistry_emdrId')
								},
								callback: function(options, success, response) {
									me.getLoadMask().hide();

									if (response && response.responseText) {
										var responseObj = Ext.util.JSON.decode(response.responseText);
										if (responseObj.success) {
											sw.swMsg.alert('Внимание', 'Запрос файла ЭМД из РЭМД ЕГИСЗ выполнен.');
										}
									}
								}
							});
						}
					}
				},
				{name: 'action_edit', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_view', text: langs('Просмотреть'), handler: function () {}},
				{name: 'action_print'}
			],
			onRowSelect: function (sm, index, record) {
				me.emdgrid.setActionDisabled('action_add',Ext.isEmpty(record.get()));
				me.emdgrid.setActionDisabled('action_view',Ext.isEmpty(record.get()));
				me.emdgrid.setActionDisabled('action_print',Ext.isEmpty(record.get()));
			},
			onDblClick: function () {},
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EMD&m=getEMDlist',
			region: 'center',
			editformclassname: 'swEMDOuterRegistryWindow',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'EMDOuterRegistry_id', type: 'int', header: 'ID', key: true},
				{name: 'EMDOuterRegistry_emdrId', type: 'string', header: langs('Номер в регистре'), width: 120},
				{name: 'EMDOuterRegistry_regDate', type: 'string', header: langs('Дата регистрации'), width: 120},
				{name: 'EMDDocumentType_Name', type: 'string', header: langs('Вид документа'), id: 'autoexpand', width: 120},
				{name: 'EMDJournalQuery_insDT', type: 'string', hidden: true },
				{name: 'EMDOuterRegistry_HasFile', type: 'string', header: langs('ЭМД'), width: 120,
					renderer: function(v, p, row) {
						if(!Ext.isEmpty(row.get('EMDJournalQuery_insDT'))) {
							if(row.get('EMDOuterRegistry_HasFile')==2) {
								return '<a target="_blank" download="" href="/?c=EMD&m=getEMDOuterRegistry_File&EMDOuterRegistry_id='+row.get('EMDOuterRegistry_id')+'">Скачать файл</a>';
							} else return row.get('EMDJournalQuery_insDT');
						} else return '';
					}
				},
			],
			toolbar: true
		});

		this.filtersPanel = new Ext.form.FormPanel({
			layout: 'form',
			region: 'center',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'left',
			border: false,
			frame: true,
			items: [
				{
					layout: 'column',
					items: [ {
						layout: 'form',
						region: 'center',
						labelWidth: 50,
						items: [me.personfield]
					},
					me.searchBtn = new Ext.Button({
						disabled: false,
						allowBlank: false,
						text: langs('Найти'),
						xtype: 'button',
						width: 100,
						style: 'padding-left: 5px;',
						handler: function() {
							var base_form = me.filtersPanel.getForm();
							if(!Ext.isEmpty(me.Person_id)) {
								me.emdgrid.loadData({
									globalFilters: { Person_id: me.Person_id }
								});
							}
						}
					}), me.clearBtn = new Ext.Button({
						xtype: 'button',
						text: langs('Очистить'),
						style: 'padding-left: 5px;',
						handler: function() {
							var base_form = me.filtersPanel.getForm();
							
							if(!Ext.isEmpty(me.Person_id)) {
								me.Person_id = me.Default_Person_id;
								base_form.findField('PersonFullName').setValue(me.Default_PersonFullName);
							} else {
								me.Person_id = null;
								base_form.findField('PersonFullName').setValue('');
							}
							base_form.findField('PersonFullName').fireEvent('change', base_form.findField('PersonFullName'), base_form.findField('PersonFullName').getValue());
						}
					}), {
						xtype: 'hidden',
						name: 'Person_id'
					}]
			}, {
				text: 'Запросить сведения об ЭМД ',
				xtype: 'button',
				handler: function() {
					var base_form = me.filtersPanel.getForm();
					getWnd('swEMDRQueryWindow').show({
						Person_id: me.Person_id,
						PersonFullName: base_form.findField('PersonFullName').getValue(),
						Person_Snils: me.Person_Snils
					});
				}
			}]
		});
		me.MainPanel = new Ext.Panel({
			region: 'center',
			items: [
				me.filtersPanel,
				me.emdgrid
			]
		});
		Ext.apply(this, {
			xtype: 'panel',
			items: [
				me.MainPanel
			],
			buttons: [
			{
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					me.hide();
				},
				text: langs('Отмена')
			}]
		});

		sw.Promed.swEMDOuterRegistryWindow.superclass.initComponent.apply(this, arguments);
	}
});