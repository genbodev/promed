/**
 * swPersonEvnJournalWindow - окно журнала событий пациента
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			24.03.2014
 */

/*NO PARSE JSON*/

sw.Promed.swPersonEvnJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['jurnal_sobyitiy_patsienta'],
	id: 'swPersonEvnJournalWindow',
	height: 750,
	width: 600,
	maximizable: true,
	modal: true,

	Person_id: null,
	ARMType: null,
	userMedStaffFact: null,

	emptyevndoc: function()
	{
		var tp = [];
		this.EvnJournalPanel.tpl = new Ext.Template(tp);
		this.EvnJournalPanel.tpl.overwrite(this.EvnJournalPanel.body, tp);
	},

	show: function() {
		sw.Promed.swPersonEvnJournalWindow.superclass.show.apply(this, arguments);
		if ((!arguments[0]) || (!arguments[0].Person_id))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны необходимые входные параметры.');
		}
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		this.emptyevndoc();

		this.Person_id = arguments[0].Person_id;

		this.EvnJournalPanel.setActionParams('openEmk', {
			ARMType: this.ARMType,
			userMedStaffFact: this.userMedStaffFact,
			afterOpenEmk: function() { this.hide();	}.createDelegate(this)
		});
		this.EvnJournalPanel.loadPage({
			Person_id: this.Person_id,
			reset: true,
			callback: function() {
				var count = this.EvnJournalPanel.totalCount;
				if (count < 10) {
					var wndHeightOffset = this.getSize().height - this.EvnJournalPanel.getSize().height;
					var journalHeight = this.EvnJournalPanel.body.dom.children.item(0).clientHeight;
					this.setHeight(wndHeightOffset+journalHeight+30);
				} else {
					this.setHeight(this.height);
				}
			}.createDelegate(this)
		});
	},

	initComponent: function() {
		this.EvnJournalPanel = new sw.Promed.EvnJournalFrame({
			id: 'PEJW_EvnJournalPanel',
			region: 'center'
		});

		Ext.apply(this, {
			layout: 'border',
			items: [this.EvnJournalPanel],
			buttons:
				[{
					text: '-'
				},
				HelpButton(this, TABINDEX_MPSCHED + 98),
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function() {this.hide();}.createDelegate(this)
				}]
		});

		sw.Promed.swPersonEvnJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});