/**
 * swSURBedHistoryViewWindow - окно просмотра данных СУР
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.01.2016
 */
/*NO PARSE JSON*/

sw.Promed.swSURBedHistoryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSURBedHistoryViewWindow',
	layout: 'border',
	title: lang['istoriya_deystviy_nad_koykoy'],
	layout: 'fit',
	maximizable: true,
	maximized: false,
	modal: true,
	width: 960,

	show: function() {
		sw.Promed.swSURBedHistoryViewWindow.superclass.show.apply(this, arguments);

		this.GridPanel.removeAll();

		this.idBed = arguments[0].idBed;
		this.Lpu_oid = arguments[0].Lpu_oid;

		this.GridPanel.getGrid().getStore().load({
			params: {
				idBed: this.idBed,
				Lpu_oid: this.Lpu_oid
			}
		});
	},

	initComponent: function() {
		this.GridPanel = new sw.Promed.ViewFrame({
			dataUrl: '/?c=ServiceSUR&m=loadBedHistoryGrid',
			border: true,
			autoLoadData: false,
			paging: false,
			stringfields: [
				{name: 'ID', type: 'int', header: 'ID', key: true},
				{name: 'BedActionBase', header: lang['osnovanie_deystviya'], type: 'string', width: 220},
				{name: 'bedActionRu', header: lang['deystvie_po_formirovaniyu_koechnogo_fonda'], type: 'string', id: 'autoexpand'},
				{name: 'BegDate', header: lang['data_nachala_sostoyaniya'], type: 'date', width: 120},
				{name: 'EndDate', header: lang['data_okonchaniya_sostoyaniya'], type: 'date', width: 120},
				{name: 'Comment', header: lang['primechanie'], type: 'string', width: 220},
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			]
		});

		Ext.apply(this,{
			buttons: [
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
			items: [this.GridPanel]
		});

		sw.Promed.swSURBedHistoryViewWindow.superclass.initComponent.apply(this, arguments);
	}
});