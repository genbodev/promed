/**
* swMedicalCareCasesViewWindow - окно просмотра случаев оказания МП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2017 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      02.08.2017
*/

/*NO PARSE JSON*/

sw.Promed.swMedicalCareCasesViewWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedicalCareCasesViewWindow',
	objectSrc: '/jscore/Forms/Common/swMedicalCareCasesViewWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swMedicalCareCasesViewWindow',
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	action: null,

	initComponent: function() {
		var win = this;

		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=Common&m=loadMedicalCareCases',
			actions:
			[
				{name:'action_add', hidden: true },
				{name:'action_edit', hidden: true },
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			],
			paging: false,
			root: 'data',
			region: 'center',
			stringfields: [
				{ header: 'Evn_id', type: 'int', name: 'Evn_id', key: true },
				{ header: 'Дата и время',  type: 'string', name: 'Evn_Date'},
				{ header: 'Цель',  type: 'string', name: 'Evn_Type', width: 300},
				{ header: 'МО', type: 'string', name: 'Evn_MO_Link', width: 450},
				{ header: 'Врач',  type: 'string', name: 'Evn_Doctor_Link', width: 250 },
				{ header: 'Направление',  type: 'string', name: 'Evn_Direction'},
				{ header: 'Способ записи',  type: 'string', name: 'Evn_RecType'}
			],
			toolbar: false
		});

		Ext.apply(this, {
			layout: 'fit',
			buttons: [{
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
			items: [
				new Ext.form.FormPanel({
					bodyStyle: 'padding: 5px',
					border: true,
					region: 'center',
					layout: 'border',
					items:[				
						new sw.Promed.PersonInformationPanel({
							id: 'MCC_PersonInformationFrame',
							region: 'north'
						}),
						this.viewFrame
					]
				})
			]
		});
		sw.Promed.swMedicalCareCasesViewWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swMedicalCareCasesViewWindow.superclass.show.apply(this, arguments);
		this.Person_id = arguments[0].Person_id;
		var Person_id = this.Person_id;
		var wnd = this;
		wnd.findById('MCC_PersonInformationFrame').load({
			Person_id: Person_id
		});
		var params = new Object();;
		params.Person_id = Person_id; 
		wnd.viewFrame.loadData({globalFilters:params});
		wnd.findById('MCC_PersonInformationFrame').setDisabled(false);
		this.syncSize();
		this.doLayout();
	}
});