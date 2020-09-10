sw.Promed.swSmpEmergencyTeamProposalLogicWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSmpEmergencyTeamProposalLogicWindow',
	title: lang['logika_predlojeniya_brigad_na_vyizov'],
	modal: true,
	width: 750,
	height: 500,
	layout: 'border',
	resizable: false,
	plain: false,
	closable: false,
	callback: Ext.emptyFn,
	onDoCancel: Ext.emptyFn,
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
		}
	},
	onCancel: function() {
		this.onDoCancel();
		this.hide();
	},
	deleteRule: function() {
		var grid = this.GridPanel;		
		if ( !grid.getGrid().getSelectionModel().getSelected() ) {
				return false;
			}
		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		if ( !selected_record.get('CmpUrgencyAndProfileStandart_id') ) {
			return false;
		}
		var params = {};
		params.CmpUrgencyAndProfileStandart_id = selected_record.get('CmpUrgencyAndProfileStandart_id');
		Ext.Ajax.request({
			method: 'POST',
			url: '?c=CmpCallCard&m=deleteCmpUrgencyAndProfileStandartRule',
			params: params,
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_pravila']);
					}
					else {
						grid.getGrid().getStore().reload();
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_pravila_voznikli_oshibki']);
				}
			}
		})
	},
	openRuleEditWindow: function(action){
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}
		var wnd = 'swSmpEmergencyTeamProposalLogicRuleEditWindow';
		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_pravila_otkryito']);
			return false;
		}
		var formParams = new Object();
		var grid = this.GridPanel;
		var filterForm = this.filterPanel.getForm();
		var lpuField = filterForm.findField('Lpu_id');
		var params = {
			action: action,
			Lpu_id: lpuField.getValue(),
			callback: function(data){
				grid.getGrid().getStore().load();
			}
		};

		if (Object.keys(this.lastArguments).length !== 0 && this.lastArguments.Lpu_id) {
			params.Lpu_id = this.lastArguments.Lpu_id
		}

		if ( action == 'add' ) {
			formParams.CmpUrgencyAndProfileStandart_id = 0;
		} else {

			if ( !grid.getGrid().getSelectionModel().getSelected() ) {
				return false;
			}
				
			var selected_record = grid.getGrid().getSelectionModel().getSelected();

			if ( !selected_record.get('CmpUrgencyAndProfileStandart_id') ) {
				return false;
			}

			formParams.CmpUrgencyAndProfileStandart_id = selected_record.get('CmpUrgencyAndProfileStandart_id');
			
			if (selected_record.get('CmpReason_id') ) {
				formParams.CmpReason_id = selected_record.get('CmpReason_id');
			}
						
			if (selected_record.get('CmpUrgencyAndProfileStandart_UntilAgeOf') ) {
				formParams.CmpUrgencyAndProfileStandart_UntilAgeOf = selected_record.get('CmpUrgencyAndProfileStandart_UntilAgeOf');
			}
			
			if (selected_record.get('CmpUrgencyAndProfileStandart_Urgency') ) {
				formParams.CmpUrgencyAndProfileStandart_Urgency = selected_record.get('CmpUrgencyAndProfileStandart_Urgency');
			}
			
			if (selected_record.get('CmpCallCardAcceptor_id') ) {
				formParams.CmpCallCardAcceptor_id = selected_record.get('CmpCallCardAcceptor_id');
			}
			
			if (selected_record.get('CmpUrgencyAndProfileStandart_HeadDoctorObserv') ) {
				formParams.CmpUrgencyAndProfileStandart_HeadDoctorObserv = selected_record.get('CmpUrgencyAndProfileStandart_HeadDoctorObserv');
			}
			
			if (selected_record.get('CmpUrgencyAndProfileStandart_MultiVictims') ) {
				formParams.CmpUrgencyAndProfileStandart_MultiVictims = selected_record.get('CmpUrgencyAndProfileStandart_MultiVictims');
			}

		}

		formParams.ARMType = this.ARMType;
		params.formParams = formParams;

		getWnd(wnd).show(params);
	},
	
	initComponent: function() {
		var win = this;

		this.filterPanel = new Ext.FormPanel({
			title: 'Фильтры',
			bodyPadding: 5,
			xtype: 'form',
			autoHeight: true,
			header: false,
			hidden: !isUserGroup('smpAdminRegion'),
			region: 'north',
			items: [
				{
					xtype: 'fieldset',
					style: 'margin: 5px 5px 5px 5px',
					title: langs('Фильтры'),
					autoHeight: true,
					collapsible: true,
					expanded: true,
					labelWidth: 200,
					anchor: '-10',
					layout: 'form',
					listeners: {
						collapse: function (p) {
							win.doLayout();
						},
						expand: function (p) {
							win.doLayout();
						}
					},
					items: [
						{
							valueField: 'Lpu_id',
							name: 'Lpu_id',
							hiddenName: 'Lpu_id',
							autoLoad: true,
							width: 350,
							listWidth: 350,
							allowBlank: true,
							fieldLabel: langs('МО'),
							displayField: 'Lpu_Nick',
							xtype: 'swlpuwithopersmpcombo'
						},
						{
							comboSubject: 'CmpCallCardAcceptor',
							fieldLabel: langs('Тип приема вызова'),
							hiddenName: 'CmpCallCardAcceptor_id',
							name: 'CmpCallCardAcceptor_id',
							xtype: 'swcommonsprcombo',
							width: 250,
							listWidth: 250
						},
						{
							border: false,
							layout: 'table',
							style: 'padding-left: 10px;',
							items: [
								{
									iconCls: 'save16',
									text: langs('Установить'),
									xtype: 'button',
									handler: function() {
										win.loadGridPanel(win.filterPanel.getForm().getValues());
									}
								},
								{
									iconCls: 'reset16',
									text: langs('Сброс'),
									xtype: 'button',
									handler: function() {
										win.filterPanel.getForm().reset();
									}
								}
							]
						}
					]
				}
			]
		});


		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id+'_Grid',
			paging: true,
			height: 430,
			region: 'center',
//			anchor:'-0, 40%',
			dataUrl: '/?c=CmpCallCard&m=getCmpUrgencyAndProfileStandart',
			toolbar: true,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			root: 'data',
			pageSize: 25,
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields: [
				{name: 'CmpUrgencyAndProfileStandart_id', type: 'int', header: 'ID', key: true},
				{name: 'CmpReason_Code', header: lang['povod'], width: 50},
				{name: 'CmpReason_id', hidden: true, hideble: false},
				{name: 'CmpUrgencyAndProfileStandart_UntilAgeOf', header: lang['vozrast'], width: 50},
				{name: 'CmpUrgencyAndProfileStandart_Urgency', header: lang['srochnost'], width: 50},
				{name: 'CmpCallCardAcceptor_id', header: lang['id_tip_priema'], hidden: true},
				{name: 'CmpCallCardAcceptor_Code', header: lang['tip_priema'], width: 70},
				{name: 'CmpUrgencyAndProfileStandart_HeadDoctorObserv', header: lang['id_nablyudenie_sv'], hidden: true},
				{name: 'CmpUrgencyAndProfileStandart_HeadDoctorObserv_YesNo', header: lang['nablyudenie_sv'], width: 100},
				{name: 'CmpUrgencyAndProfileStandart_MultiVictims', header: lang['id_nablyudenie_sv'], hidden: true},
				{name: 'CmpUrgencyAndProfileStandart_MultiVictims_YesNo', header: lang['mn_postradavshih'], width: 100},
				{name: 'CmpUrgencyAndProfileStandart_PlaceSequence', header: lang['mesto'], width: 100},
				{name: 'CmpUrgencyAndProfileStandart_ProfileSequence', header: lang['posledovatelnost_profiley'], width: 150, id:'autoexpand'},
			],
			actions:
			[
				{name: 'action_add', iconCls: 'add16', text: lang['dobavit'], tooltip: lang['dobavit_pravilo'], handler: this.openRuleEditWindow.createDelegate(this, ['add'])},
				{name: 'action_edit', iconCls: 'edit16', text: lang['izmenit'], tooltip: lang['izmenit_pravilo'], handler: this.openRuleEditWindow.createDelegate(this, ['edit'])},
				{name: 'action_delete', iconCls: 'delete16', text: lang['udalit'], tooltip: lang['udalit_pravilo'], handler: this.deleteRule.createDelegate(this)}
			]
		});

		Ext.apply(this, {
			items: [
				this.filterPanel,
				this.GridPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});
		
		sw.Promed.swSmpEmergencyTeamProposalLogicWindow.superclass.initComponent.apply(this, arguments);
	},
	//Если справочник открывается в ЛПУ первый раз, отправляем запрос на создание дефолтного справочника для этого конкретного ЛПУ
	_initiateLpuProposalLogic: function(cb) {

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Пожалуйста, подождите. Идет инициализация правил для МО. <br /> Это может занять продолжительное время..."});
		loadMask.show();
		Ext.Ajax.timeout = 30000000;

		var filterForm = this.filterPanel.getForm(),
			lpuField = filterForm.findField('Lpu_id');

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=initiateProposalLogicForLpu',
			params: {
				Lpu_id: lpuField.getValue()
			},
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка проверке инициализации'));
					} else if  ( typeof cb === 'function' ) {
						cb();
					}
				}
				loadMask.hide();
			}.createDelegate(this),
			method: 'POST'
		});
	},
	show: function() {
		sw.Promed.swSmpEmergencyTeamProposalLogicWindow.superclass.show.apply(this, arguments);

		this.doLayout();
		this.restore();
		this.center();

		var params = arguments[0] ? arguments[0] : null,
			filterForm = this.filterPanel.getForm(),
			lpuField = filterForm.findField('Lpu_id');

		if(!this.filterPanel.hidden){
			filterForm.findField('CmpCallCardAcceptor_id').setValue(1);
		}

		if(params && params.Lpu_id){
			//пришла lpu_id - значит из админа цод
			lpuField.setFieldValue('Lpu_id', params.Lpu_id);

			this.loadGridPanel(this.filterPanel.getForm().getValues());
		}
		else{
			Ext.Ajax.request({
				url: '/?c=CmpCallCard4E&m=getOperDepartamentOptions',
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						lpuField.setFieldValue('Lpu_id', response_obj.Lpu_id);

						this.loadGridPanel(this.filterPanel.getForm().getValues());
					}
				}.createDelegate(this),
				method: 'POST'
			});
		}

		this.GridPanel.getAction('action_view').hide();
	},

	loadGridPanel: function(inputParams){
		inputParams.start = 0;
		inputParams.limit = 100;

		this.GridPanel.loadData({globalFilters: inputParams});
	}
	
});
