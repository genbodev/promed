sw.Promed.swEMDOuterRegistryForShowWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства  */
	autoHeight: true,
	modal: true,
	title: langs('Реестр внешних ЭМД'),
	width: 800,

	/* методы  */
	show: function() {
		sw.Promed.swEMDOuterRegistryForShowWindow.superclass.show.apply(this, arguments);

		var me = this,
			base_form = me.filtersPanel.getForm();

		me.Person_id = null;
		me.Person_FullName = '';

		if(!arguments || !arguments[0] || !arguments[0]['Person_id']) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указан идентификатор пациента!', function() { me.hide(); }));
			return false;
		}

		me.Person_id = arguments[0]['Person_id'];

		if(arguments[0]['Person_Surname']) me.Person_FullName +=''+arguments[0]['Person_Surname'];
		if(arguments[0]['Person_Firname']) me.Person_FullName +=' '+arguments[0]['Person_Firname'];
		if(arguments[0]['Person_Secname']) me.Person_FullName +=' '+arguments[0]['Person_Secname'];
		if(arguments[0]['Person_Birthday']) me.Person_FullName +=', '+arguments[0]['Person_Birthday'].dateFormat('d.m.Y');

		me.emdgrid.loadData({
			globalFilters: { Person_id: me.Person_id }
		});

		base_form.findField('PersonFullName').setValue(me.Person_FullName);
		base_form.findField('PersonFullName').fireEvent('change', base_form.findField('PersonFullName'), base_form.findField('PersonFullName').getValue());
	},

	/* конструктор */
	initComponent: function() {
		var me = this;
		
		me.personfield = {
			disabled: true,
			fieldLabel: langs('Пациент'),
			name: 'PersonFullName',
			width: 360,
			xtype: 'textfield'
		};
		
		me.emdgrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EMD&m=getEMDlistByPersonForShow',
			region: 'center',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'EMDRegistry_id', type: 'int', header: 'ID', key: true},
				{name: 'EMDRegistry_Num', type: 'string', header: langs('Номер'), width: 120, hidden: true},
				{name: 'EMDRegistry_EMDDate', type: 'string', header: langs('Дата документа'), width: 120, hidden: true},
				{name: 'EMDDocumentTypeLocal_Name', type: 'string', header: langs('Вид документа'), width: 120, hidden: true},
				{name: 'EMDVersion_FilePath', type: 'string', hidden: true },
				{name: 'EMDOuterRegistry_HasFile', type: 'string', header: langs('ЭМД'), id: 'autoexpand', width: 120,
					renderer: function(v, p, row) {
						if(!Ext.isEmpty(row.get('EMDVersion_FilePath'))) {
							return '<a target="_blank" href="' + row.get('EMDVersion_FilePath') + '">'
								+ row.get('EMDDocumentTypeLocal_Name') + ' номер ' + row.get('EMDRegistry_Num')
								+ ', дата документа ' + row.get('EMDRegistry_EMDDate')
								+ '</a>';
						}
						else {
							return '';
						}
					}
				}
			],
			toolbar: true
		});

		me.filtersPanel = new Ext.form.FormPanel({
			layout: 'form',
			region: 'center',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'left',
			border: false,
			frame: true,
			items: [
				me.personfield
			]
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
			buttons: [{
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

		sw.Promed.swEMDOuterRegistryForShowWindow.superclass.initComponent.apply(this, arguments);
	}
});