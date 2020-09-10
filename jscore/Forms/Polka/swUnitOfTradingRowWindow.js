/**
* Строка лота
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      26.11.2012
*/

sw.Promed.swUnitOfTradingRowWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 800,
	onHide: Ext.emptyFn,
	onCancel: Ext.emptyFn,
	callback: Ext.emptyFn,
	shim: false,
	buttonAlign: 'right',
	id: 'swUnitOfTradingRowWindow',
	
	listeners: {
		hide: function() {
			this.Grid.getGrid().getStore().removeAll();
		}
	},
	
	show: function()
	{
		sw.Promed.swUnitOfTradingRowWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		if( !arguments[0].DrugRequest_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_svodnaya_zayavka']);
			this.hide();
			return false;
		}
		if( !arguments[0].WhsDocumentUc_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_lot']);
			this.hide();
			return false;
		}

		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if( arguments[0].onCancel ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.Form.getForm().setValues(arguments[0]);
		
		this.setTitle(lang['stroka_lota'] + lang['dobavlenie']);
		
		var wnd = this;
		this.Grid.loadData({globalFilters: {
			start: 0,
			DrugRequest_id: arguments[0].DrugRequest_id
		}, callback: function() {
			if( !this.getCount() ) {
				sw.swMsg.alert(lang['soobschenie'], lang['net_medikamentov_dlya_dobavleniya_vse_medikamentyi_iz_zayavki_dobavlenyi_v_lotyi']);
				wnd.hide();
			}
		}});
		this.center();
	},
	
	doSave: function() {
		var bf = this.Form.getForm();
		if( !bf.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
			return false;
		}
		var selRecords = this.Grid.getGrid().getSelectionModel().getSelections();
		if( selRecords.length == 0 ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_niodin_medikament']);
			return false;
		}
		this.buttons[0].disable();
		var loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {},
			drugList = [],
			i = 0;
		for(; i<selRecords.length; i++) {
			drugList.push(selRecords[i].get('DrugRequestPurchaseSpec_id'));
		}
		params['DrugList'] = escape(drugList.join('|'));
		bf.submit({
			params: params,
			scope: this,
			failure: function() {
				loadMask.hide();
			},
			success: function(form, act) {
				this.callback();
				loadMask.hide();
				this.buttons[0].enable();
				this.hide();
			}
		});
	},
	
	initComponent: function() {

		this.Grid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			height: 400,
			autoExpandMin: 100,
			id: this.id + '_Grid',
			pageSize: 50,
			selectionModel: 'multiselect',
			paging: true,
			autoScroll: true,
			toolbar: false,
			useEmptyRecord: false,
			autoLoadData: false,
			root: 'data',
			stringfields: [
				{ name: 'DrugRequestPurchaseSpec_id', type: 'int', hidden: true, key: true },
				{ name: 'Drug_Name', type: 'string', id: 'autoexpand', header: lang['naimenovanie'] },
				{ name: 'DrugRequestPurchaseSpec_Kolvo', type: 'string', header: lang['kol-vo'], align:'right' },
				{ name: 'Okei_Name', type: 'string', header: lang['ed_izmereniya'] },
				{ name: 'DrugRequestPurchaseSpec_Price', type: 'string', header: lang['tsena'], align:'right' },
				{ name: 'DrugRequestPurchaseSpec_Sum', type: 'string', header: lang['summa'], align:'right' }
			],
			dataUrl: '/?c=UnitOfTrading&m=loadDrugList',
			totalProperty: 'totalCount'
		});
		
		
		this.Form = new Ext.FormPanel({
			border: false,
			url: '/?c=UnitOfTrading&m=addDrugListInUnitOfTrading',
			items: [{
				xtype: 'hidden',
				name: 'WhsDocumentUc_id'
			}]
		});
		
		Ext.apply(this, {
			items: [this.Form, this.Grid],
			buttons: [{
				handler: this.doSave,
				scope: this,
				iconCls: 'ok16',
				text: lang['vyibrat']
			},
			'-',
			HelpButton(this, TABINDEX_MPSCHED + 98),
			{
				text: lang['otmena'],
				tabIndex: -1,
				tooltip: lang['otmena'],
				iconCls: 'cancel16',
				handler: function(){
					this.onCancel();
					this.hide();
				}.createDelegate(this)
			}]
		});
		sw.Promed.swUnitOfTradingRowWindow.superclass.initComponent.apply(this, arguments);
	}
});