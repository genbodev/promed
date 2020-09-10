/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 13.05.15
 * Time: 16:03
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.swDrugStateEditWindow = Ext.extend(sw.Promed.BaseForm,{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'DrugStateEditWindow',
	listeners: { hide: function(){ this.onHide(); } },
	Lpu_id: null,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function(){

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function(){
		var form = this.FormPanel;
		var current_window = this;
		var loadMask = new Ext.LoadMask( this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		form.getForm().submit({
			params: {},
			failure: function( result_form, action ){
				loadMask.hide();
				if ( action.result ){
					if ( action.result.Error_Code ){
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action){
				loadMask.hide();
				if ( action.result ){
					if ( action.result.DrugState_id ){
						//getWnd('swDrugStateSearchWindow').findById('DSSW_Grid').loadData();
						var DrugStateSearchForm = getWnd('swDrugStateSearchWindow');
						var filters = DrugStateSearchForm.FilterPanel.getForm().getValues();
						var DrugStateGrid = DrugStateSearchForm.findById('DSSW_Grid').ViewGridPanel;
						filters.limit = 100;
						filters.start = 0;

						DrugStateGrid.getStore().load({
							params: filters,
							callback: function() {
								if ( DrugStateGrid.getStore().getCount() > 0 )
								{
									DrugStateGrid.getView().focusRow(0);
								}
							}
						});
						current_window.hide();
					}else{
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function(){ form.hide(); },
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_v_sluchae_povtoreniya_oshibki_obratites_k_razrabotchikam'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable){
		var base_form = Ext.getCmp('DrugStateEditWindow');
		if (enable) {
			base_form.findById('DSEW_DrugRequestPeriod_id').enable();
			base_form.findById('DSEW_ReceptFinance_id').enable();
			base_form.findById('DSEW_DrugProto_id').enable();
			base_form.findById('DSEW_DrugProtoMnn_id').enable();
			base_form.findById('DSEW_DrugMnn_id').enable();
			base_form.findById('DrugState_Price').enable();
			this.buttons[0].enable();
		} else {
			base_form.findById('DSEW_DrugRequestPeriod_id').disable();
			base_form.findById('DSEW_ReceptFinance_id').disable();
			base_form.findById('DSEW_DrugProto_id').disable();
			base_form.findById('DSEW_DrugProtoMnn_id').disable();
			base_form.findById('DSEW_DrugMnn_id').disable();
			base_form.findById('DrugState_Price').disable();
			this.buttons[0].disable();
		}
	},
	show: function(){
		sw.Promed.swDrugStateEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		if ( !arguments[0] ){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}

		this.focus();

		var loadMask = new Ext.LoadMask( this.getEl(), { msg: LOAD_WAIT } );
		loadMask.show();

		var base_form = this.findById('DrugStateEditForm');

		base_form.getForm().reset();

		this.Lpu_id = arguments[0].Lpu_id || null;

		this.DrugState_id = arguments[0].DrugState_id || null;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		} else {
			this.action = this.DrugState_id ? 'edit' : 'add';
		}

		base_form.getForm().setValues( arguments[0] );

		switch( this.action ){
			case 'add':
				this.setTitle(lang['medikament_po_zayavke_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				base_form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['medikament_po_zayavke_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['medikament_po_zayavke_prosmotr']);
				this.enableEdit(false);
				break;
		}
		var drug_combo =  current_window.findById('DSEW_DrugMnn_id');
		drug_combo.getStore().baseParams.mode = 'all';
		if ( this.action != 'add' ){
			base_form.getForm().load({
				params: {
					DrugState_id: current_window.DrugState_id
				},
				failure: function(f, o, a){
					loadMask.hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function(){
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function(a,result){
					var result = Ext.util.JSON.decode(result.response.responseText);
					drug_combo.getStore().baseParams.Drug_id = result[0].Drug_id;
					drug_combo.getStore().baseParams.ReceptFinance_Code = result[0].ReceptFinance_id;
					drug_combo.getStore().baseParams.mode = 'all';
					drug_combo.getStore().load({
						callback: function(){
							drug_combo.setValue(result[0].Drug_id);
						}
					});
					var drugprotomnncombo = current_window.findById('DSEW_DrugProtoMnn_id');
					drugprotomnncombo.clearValue();
					drugprotomnncombo.getStore().removeAll();
					drugprotomnncombo.lastQuery = '';
					drugprotomnncombo.getStore().baseParams.DrugProtoMnn_id = '';
					drugprotomnncombo.getStore().baseParams.DrugRequestPeriod_id = result[0].DrugRequestPeriod_id;
					drugprotomnncombo.getStore().baseParams.query = '';
					drugprotomnncombo.getStore().baseParams.ignoreOstat = '1';

						drugprotomnncombo.getStore().load({
							callback: function(){
								drugprotomnncombo.setValue(result[0].DrugProtoMnn_id);
								current_window.findById('DSEW_ReceptFinance_id').setValue(result[0].ReceptFinance_id);
								var drugproto_combo = current_window.findById('DSEW_DrugProto_id');
								drugproto_combo.getStore().load({
									callback: function(){
										drugproto_combo.setValue(result[0].DrugProto_id);
									}
								});
								loadMask.hide();
							}
						});
				},
				url: '/?c=Drug&m=loadDrugState'
			});
		}
		this.buttons[0].focus();
	},
	initComponent: function(){

		var current_window = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoWidth: false,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DrugStateEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			items: [{
				xtype: 'hidden',
				name: 'DrugState_id',
				id: 'DSEW_DrugState_id',
				value: 0
			},{
				xtype: 'hidden',
				name: 'Lpu_id',
				id: 'DSEW_Lpu_id',
				value: 0
			},{
				allowBlank: false,
				disabled: false,
				id: 'DSEW_DrugRequestPeriod_id',
				xtype: 'swdrugrequestperiodcombo',
				listeners:
				{
					change:
						function(combo,newValue)
						{
							var loadMask = new Ext.LoadMask( current_window.getEl(), { msg: LOAD_WAIT });
							loadMask.show();
							var drugprotomnncombo = current_window.findById('DSEW_DrugProtoMnn_id');
							drugprotomnncombo.clearValue();
							drugprotomnncombo.getStore().removeAll();
							drugprotomnncombo.lastQuery = '';
							//drugprotomnncombo.getStore().baseParams.ReceptFinance_id = form.recept_finance_id;
							drugprotomnncombo.getStore().baseParams.DrugProtoMnn_id = '';
							drugprotomnncombo.getStore().baseParams.DrugRequestPeriod_id = newValue;
							drugprotomnncombo.getStore().baseParams.query = '';
							drugprotomnncombo.getStore().baseParams.ignoreOstat = '1';
							if (newValue > 0)
							{
								drugprotomnncombo.getStore().load({
									callback: function(){
										//alert('111');
										loadMask.hide();
									}
								});
							}

							var drugprotocombo = current_window.findById('DSEW_DrugProto_id');
							drugprotocombo.clearValue();
							drugprotocombo.getStore().removeAll();
							drugprotocombo.lastQuery = '';
							drugprotocombo.getStore().baseParams.DrugRequestPeriod_id = newValue;
							drugprotocombo.getStore().baseParams.query = '';
							if (newValue > 0)
							{
								drugprotocombo.getStore().load({
									callback: function(){
										//alert('111');
										//loadMask.hide();
									}
								});
							}
							//loadMask.hide();
						}
				}
			},
				{
					allowBlank: true,
					autoLoad: false,
					comboSubject: 'ReceptFinance',
					fieldLabel: lang['tip_finansirovaniya'],
					id: 'DSEW_ReceptFinance_id',
					hiddenName: 'ReceptFinance_id',
					lastQuery: '',
					listWidth: 200,
					validateOnBlur: true,
					width: 200,
					xtype: 'swcommonsprcombo',
					listeners:{
						change: function(combo,newValue){
							var drug_combo =  current_window.findById('DSEW_DrugMnn_id');
							drug_combo.getStore().baseParams.ReceptFinance_Code = newValue;
							var drugprotocombo = current_window.findById('DSEW_DrugProto_id');
							drugprotocombo.clearValue();
							drugprotocombo.getStore().removeAll();
							drugprotocombo.lastQuery = '';
							//drugprotomnncombo.getStore().baseParams.ReceptFinance_id = form.recept_finance_id;
							drugprotocombo.getStore().baseParams.ReceptFinance_id = newValue;
							drugprotocombo.getStore().baseParams.query = '';
							if (newValue > 0)
							{
								drugprotocombo.getStore().load({
									callback: function(){
									}
								});
							}
						}
					}
				},
				{
					allowBlank: false,
					disabled: false,
					id: 'DSEW_DrugProto_id',
					xtype: 'swdrugprotocombo',
					listeners:{
						change: function(combo,newValue){
							var selected_record = combo.getStore().getById(newValue);
							var index = current_window.findById('DSEW_ReceptFinance_id').getStore().findBy(function(rec) {
								return (rec.get('ReceptFinance_id') == selected_record.get('ReceptFinance_id'));
							});
							if ( index >= 0 ) {
								current_window.findById('DSEW_ReceptFinance_id').setValue(current_window.findById('DSEW_ReceptFinance_id').getStore().getAt(index).get('ReceptFinance_id'));
								//current_window.findById('DSEW_ReceptFinance_id').fireEvent('change',current_window.findById('DSEW_ReceptFinance_id'),current_window.findById('DSEW_ReceptFinance_id').getStore().getAt(index).get('ReceptFinance_id'));
							}
						}
					}
				},
				{
					allowBlank: false,
					disabled: false,
					id: 'DSEW_DrugProtoMnn_id',
					xtype: 'swdrugprotomnnlistcombo',
					fieldLabel: lang['mnn_v_zayavke'],
					listeners:{
						change: function(combo, newValue){
							var selected_record = combo.getStore().getById(newValue);
							var drug_combo =  current_window.findById('DSEW_DrugMnn_id');
							drug_combo.getStore().baseParams.DrugMnn_id = selected_record.get('DrugMnn_id');
							var index = current_window.findById('DSEW_ReceptFinance_id').getStore().findBy(function(rec) {
								return (rec.get('ReceptFinance_id') == selected_record.get('ReceptFinance_id'));
							});
							if ( index >= 0 ) {
								current_window.findById('DSEW_ReceptFinance_id').setValue(current_window.findById('DSEW_ReceptFinance_id').getStore().getAt(index).get('ReceptFinance_id'));
								current_window.findById('DSEW_ReceptFinance_id').fireEvent('change',current_window.findById('DSEW_ReceptFinance_id'),current_window.findById('DSEW_ReceptFinance_id').getStore().getAt(index).get('ReceptFinance_id'));
							}
						}
					},
					listWidth: 500,
					anchor: '100%'
				},
				{
					allowBlank: false,
					id: 'DSEW_DrugMnn_id',
					fieldLabel: lang['naimenovanie'],
					listeners: {
						'beforeselect': function(combo, record, index) {
							//var base_form = this.findById('EvnReceptEditForm').getForm();
							combo.setValue(record.get('Drug_id'));
							return true;
						}.createDelegate(this),
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4 ) {
								e.stopEvent();

								if ( e.browserEvent.stopPropagation ) {
									e.browserEvent.stopPropagation();
								}
								else {
									e.browserEvent.cancelBubble = true;
								}

								if ( e.browserEvent.preventDefault ) {
									e.browserEvent.preventDefault();
								}
								else {
									e.browserEvent.returnValue = false;
								}

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								switch ( e.getKey() ) {
									case Ext.EventObject.F4:
										inp.onTrigger2Click();
										break;
								}
							}

							return true;
						}.createDelegate(this)
					},
					listWidth: 800,
					loadingText: lang['idet_poisk'],
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					onTrigger2Click: function() {
						var base_form = current_window;

						var drug_combo = base_form.findById('DSEW_DrugMnn_id');
						var recept_finance_combo = base_form.findById('DSEW_ReceptFinance_id');

						var recept_finance_code = 0;

						var record = recept_finance_combo.getStore().getById(recept_finance_combo.getValue());

						if ( record ) {
							recept_finance_code = record.get('ReceptFinance_Code');
						}

						if ( recept_finance_code == 0 ) {
							sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_tip_finansirovaniya_lgotyi'], function() { base_form.findById('DSEW_ReceptFinance_id').focus(true); });
							return false;
						}

						getWnd('swDrugTorgSearchWindow').show({
							onHide: function() {
								drug_combo.focus(false);
							},
							onSelect: function(drugTorgData) {
								drug_combo.getStore().removeAll();
								drug_combo.getStore().loadData([ drugTorgData ]);

								drug_combo.getStore().baseParams.DrugMnn_id = 0;
								record = drug_combo.getStore().getById(drugTorgData.Drug_id);

								if ( record ) {
									drug_combo.fireEvent('beforeselect', drug_combo, record);
								}

								getWnd('swDrugTorgSearchWindow').hide();
							},
							ReceptFinance_Code: recept_finance_code
						});
					}.createDelegate(this),
					tabIndex: TABINDEX_EREF + 15,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="width: 100%;"><tr style=\'font-weight: bold; color: #{[values.DrugOstat_Flag == 2 ? "f00" : (values.DrugOstat_Flag == 1 ? "00f" : "000" )]};\'>',
						'<td style="width: 70%;">{Drug_Name}&nbsp;</td>',
						'<td style="width: 30%; text-align: right;">{[values.DrugOstat_Flag == 2 ? "остатков нет" : (values.DrugOstat_Flag == 1 ? "остатки на РАС" : "&nbsp;" )]}</td>',
						'</tr></table>',
						'</div></tpl>'
					),
					// triggerAction: 'all',
					validateOnBlur: true,
					anchor: '100%',
					xtype: 'swdrugcombo'
				},
				{
					allowBlank: false,
					disabled: false,
					fieldLabel: lang['tsena'],
					id: 'DrugState_Price',
					xtype: 'textfield'
				}],
			reader: new Ext.data.JsonReader({},[
				{ name: 'DrugState_id' },
				{ name: 'DrugRequestPeriod_id' },
				{ name: 'DrugProto_id' },
				{ name: 'DrugProtoMnn_id'},
				{ name: 'Drug_id'},
				{ name: 'DrugState_Price'}
			]),
			url: '/?c=Drug&m=saveDrugState'
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function(){
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},{
					text: '-'
				},{
					handler: function(){
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [this.FormPanel]
		});
		sw.Promed.swDrugStateEditWindow.superclass.initComponent.apply(this, arguments);
	}
});