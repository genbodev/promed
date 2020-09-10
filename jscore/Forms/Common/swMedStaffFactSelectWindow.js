/**
* swMedStaffFactSelectWindow - окно выбора рабочего места врача. Не путать с выбором АРМа!
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A. Permyakov
* @version      20.01.2010
*
* @class        sw.Promed.swMedStaffFactSelectWindow
* @extends      sw.Promed.BaseForm
*/
sw.Promed.swMedStaffFactSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,
	/**
	 * Обработчик выбора записи рабочего места
	 * @param selectedParams.medStaffFactRecord {Ext.data.Record} Запись из swMedStaffFactGlobalStore
	 * @param selectedParams.lpuSectionRecord {Ext.data.Record} Запись из swLpuSectionGlobalStore
	 *
	 * override
	 */
	onSelect: Ext.emptyFn,
	/**
	 * Опции для фильтрации записей из swMedStaffFactGlobalStore
	 *
	 * override
	 */
	medStaffFactGlobalStoreFilters: {},
	/**
	 * Обработчик клика по кнопке "Выбрать"
	 */
	doSelect: function() {
		var selectedParams = {};
		if ( this.medStaffFactCombo.getValue() > 0 ) {
			var msf_record = false;
			this.medStaffFactCombo.getStore().each(function(record){
				if ( record.get('MedStaffFact_id') == parseInt(this.medStaffFactCombo.getValue()) )
				{
					msf_record = record;
				}
			}.createDelegate(this));
			selectedParams.medStaffFactRecord = msf_record;
			
			var ls_record = false;
			if (msf_record) {
				swLpuSectionGlobalStore.clearFilter();
				swLpuSectionGlobalStore.each(function(record){
					if ( record.get('LpuSection_id') == parseInt(msf_record.get('LpuSection_id')) )
					{
						ls_record = record;
					}
				}.createDelegate(this));
			}
			selectedParams.lpuSectionRecord = ls_record;
			
			this.hide();
			this.onSelect(selectedParams);
			return true;
		}
		sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_vrach'], function() { this.medStaffFactCombo.focus(true); }.createDelegate(this) );
		return false;
	},
	/**
	 * Конструктор
	 */
	initComponent: function() {
		
		this.medStaffFactCombo = new sw.Promed.SwMedStaffFactGlobalCombo({
			allowBlank: false,
			lastQuery: '',
			listWidth: 700,
			validateOnBlur: true,
			width: 480
		});
		
		this.formPanel = new sw.Promed.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			labelWidth: 100,
			layout: 'form',
			style: 'padding: 3px',
			items: [
				this.medStaffFactCombo
			]
		});
		
		Ext.apply(this, {
			buttons: [{
				handler : function(button, event) {
					this.doSelect();
				}.createDelegate(this),
				iconCls : 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function(button, event) {
					this.hide();
				}.createDelegate(this),
				iconCls : 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.medStaffFactCombo.focus(true);
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swMedStaffFactSelectWindow.superclass.initComponent.apply(this, arguments);
	}, //end initComponent()
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swMedStaffFactSelectWindow.superclass.show.apply(this, arguments);

		var base_form = this.formPanel.getForm();
		base_form.reset();

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;
		this.medStaffFactGlobalStoreFilters = (typeof arguments[0].medStaffFactGlobalStoreFilters == 'object') ? arguments[0].medStaffFactGlobalStoreFilters : {};
		
		swMedStaffFactGlobalStore.clearFilter();
		this.medStaffFactCombo.getStore().removeAll();
		setMedStaffFactGlobalStoreFilter(this.medStaffFactGlobalStoreFilters);

		this.medStaffFactCombo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		this.medStaffFactCombo.focus(true, 100);

		this.doLayout();
		this.syncSize();
	}, //end show()
	title: lang['vyibor_vracha'],
	width: 650
});