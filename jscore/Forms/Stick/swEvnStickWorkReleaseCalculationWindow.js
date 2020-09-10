/**
 * swEvnStickWorkReleaseCalculationWindow - окно расчета дней нетрудоспособности в году
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Stick
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			14.01.2015
 */
/*NO PARSE JSON*/

sw.Promed.swEvnStickWorkReleaseCalculationWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnStickWorkReleaseCalculationWindow',
	width: 800,
	height: 500,
	modal: true,
	maximizable: true,
	maximized: false,
	layout: 'border',
	title: lang['chislo_dney_netrudosposobnosti_v_tekuschem_godu'],

	show: function() {
		sw.Promed.swEvnStickWorkReleaseCalculationWindow.superclass.show.apply(this, arguments);

		var grid = this.GridPanel.getGrid();
		grid.getStore().removeAll();

		if (!arguments[0] && !arguments[0].Person_id && !arguments[0].StickCause_id) {
			this.hide();
			sw.swMsg.alert(lang['oshibka_otkryitiya_formyi'], lang['ne_byili_peredanyi_neobhodimyie_parametryi']);
			return;
		}

		this.Person_id = arguments[0].Person_id;
		this.StickCause_id = arguments[0].StickCause_id;

		Ext.Ajax.request({
			url: '/?c=Stick&m=getEvnStickWorkReleaseCalculation',
			params: {
				Person_id: this.Person_id,
				StickCause_id: this.StickCause_id
			},
			success: function(response, options){
				var response_obj = Ext.util.JSON.decode(response.responseText);

				this.SummaryTpl.overwrite(this.TextPanel.body, response_obj);
				grid.getStore().load({params: {Person_id: this.Person_id}});
			}.createDelegate(this)
		});
	},

	initComponent: function() {
		this.SummaryTpl = new Ext.XTemplate(
			lang['chislo_kalendarnyih_dney_v_tekuschem_kalendarnom_godu'] +
			lang['po_vsem_zavershennyim_sluchayam_uhoda_za_patsientom_{person_fio}_{person_birthday}'] +
			lang['sostavlyaet_{sumdayscount}_dney_v_sootvetstvii_s_p_35_prikaza_mz_rf_ot_29_06_2011_№624n'] +
			' "Об утверждении порядка выдачи листков нетрудоспособности" число дней не должно превышать {LimitDaysCount}.'
		);
		this.TextPanel = new Ext.Panel({
			height: 60,
			frame: true,
			bodyBorder: false,
			border: false,
			region: 'north',
			id: 'ESWRCW_XmlTextPanel',
			html: ''
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			title: lang['spisok_zakryityih_lvn_po_uhodu_za_patsientom_v_tekuschem_kalendarnom_godu'],
			id: 'ESWRCW_InvoicePositionGrid',
			dataUrl: '/?c=Stick&m=loadClosedEvnStickGrid',
			border: true,
			autoLoadData: false,
			toolbar: false,
			region: 'center',
			stringfields: [
				{name: 'EvnStick_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnStick_Num', header: lang['nomer_lvn'], type: 'int', width: 80},
				{name: 'Lpu_Nick', header: lang['lpu'], type: 'string', id: 'autoexpand'},
				{name: 'MedPersonalFirst_Fio', header: lang['vrach_vyidavshiy_lvn'], type: 'string', width: 160},
				{name: 'MedPersonalLast_Fio', header: lang['vrach_zakonchivshiy_lvn'], type: 'string', width: 160},
				{name: 'EvnStickWorkRelease_begDate', header: lang['osvobojdenie_ot_rabotyi_s_kakogo_chisla'], type: 'date', width: 100},
				{name: 'EvnStickWorkRelease_endDate', header: lang['osvobojdenie_ot_rabotyi_po_kakoe_chislo'], type: 'date', width: 100},
				{name: 'EvnStickWorkRelease_DaysCount', header: lang['chislo_kalendarnyih_dney_osvobojdeniya_ot_rabotyi'], type: 'int', width: 100 },
				{name: 'CardType', type: 'string', header: lang['tap_kvs'], width: 100},
				{name: 'NumCard', type: 'string', header: lang['nomer_tap_kvs'], width: 100}
			]
		});

		Ext.apply(this,
			{
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
				items: [this.TextPanel, this.GridPanel]
			});

		sw.Promed.swEvnStickWorkReleaseCalculationWindow.superclass.initComponent.apply(this, arguments);
	}
});