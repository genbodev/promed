/**
 * swPersonDispSelect - окно выбора диспансерной карты
 *
 * Kukuzapa forever!
 */

sw.Promed.swPersonDispSelect =  Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPersonDispSelect',
	objectSrc: '/jscore/Forms/Polka/kz/swPersonDispSelect.js',
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	collapsible: false,
	draggable: true,
	title:'Выбор карты диспансерного учета',
	modal: true,
	
	openPersonDispEditWindow: function() {
		var record = this.findById('PersonDispList').getGrid().selModel.getSelected();
		
		if (!record) {
			sw.swMsg.alert(lang['soobschenie'], 'Карта не выбрана.');
			return false;
		}
		
		var params = {};
		params.isERDB = 1;
		params.HumanUID = this.HumanUID;
		params.formParams = this.formParams;
		params.action = this.DispCards[record.id].action;
		if (this.DispCards[record.id].action != 'add') params.formParams.PersonDisp_id = this.DispCards[record.id].PersonDisp_id;
		params.Nomkart = this.DispCards[record.id].Nomkart;
		params.Dt_beg = this.DispCards[record.id].Dt_beg;
		params.Dt_end = this.DispCards[record.id].Dt_end;
		params.Diag_id = this.DispCards[record.id].Diag_id;
		params.Dgroup_kod = (this.DispCards[record.id].Dgroup_kod)?this.DispCards[record.id].Dgroup_kod.Kod:null;
		params.Prich_End_ID = (this.DispCards[record.id].Prich_End_ID)?this.DispCards[record.id].Prich_End_ID.ID:null;
		params.Vra_UID_MedStaffFact_id = (this.DispCards[record.id].Vra_UID_MedStaffFact_id)?this.DispCards[record.id].Vra_UID_MedStaffFact_id:null;
		params.Vra_UID_LpuSection_id = (this.DispCards[record.id].Vra_UID_LpuSection_id)?this.DispCards[record.id].Vra_UID_LpuSection_id:null;
		params.PersonDispHist_MedPersonalFio = (this.DispCards[record.id].PersonDispHist_MedPersonalFio)?this.DispCards[record.id].PersonDispHist_MedPersonalFio:null;
		getWnd('swPersonDispEditWindow').show(params);
	},
	
	initComponent: function () {
		this.buttons = [{
			handler: function() {
				this.openPersonDispEditWindow();
			}.createDelegate(this),
			iconCls: 'x-btn-text',
			icon: 'img/icons/ok16.png',
			text: 'Выбрать'
		}, {
			text: '-'
		}, {
			handler: function() {
				this.hide();
			}.createDelegate(this),
			iconCls: 'cancel16',
			text: 'Закрыть'
		}];
		
		this.ViewFrame = new sw.Promed.ViewFrame({
			id: 'PersonDispList',
			height:200,
			autoLoadData: false,
			style: 'margin-bottom: 0.5em;',
			onDblClick: function() {
				this.openPersonDispEditWindow();
			}.createDelegate(this),
			stringfields:
				[
					{name: 'ID', type: 'int', header: 'ID', key: true },
					{name: 'PersonDisp_NumCard', type:'int', header: '№ карты диспансерного учета', width: 100},
					{name: 'Diag_Name',  type: 'string', header: 'Диагноз', width: 480},
					{name: 'Status',  type: 'string', header: 'Статус карты', width: 100}
				],
			actions:
				[
					{name:'action_add', handler: function() {}, hidden: true},
					{name:'action_edit', handler: function() {}, hidden: true},
					{name:'action_view', handler: function() {}, hidden: true},
					{name:'action_delete', handler: function() {}, hidden: true},
					{name:'action_refresh', hidden: true},
					{name:'action_print', hidden: true}
				]
		});
		
		Ext.apply(this,{items:[this.ViewFrame,this.buttons]});
		sw.Promed.swPersonDispSelect.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
		sw.Promed.swPersonDispSelect.superclass.show.apply(this, arguments);
		var that = this;
		
		this.DispCards = arguments[0].DispCards;
		this.formParams = arguments[0].formParams;
		this.HumanUID = arguments[0].HumanUID;
		
		var view_frame = this.findById('PersonDispList').getGrid().getStore();
		view_frame.removeAll();
		
		this.DispCards.forEach(function (card,index) {
			var status = (!card.Dt_end)?'Открыта':'Закрыта';
			view_frame.loadData([{
				'ID': index,
				'PersonDisp_NumCard': card.Nomkart,
				'Diag_Name': card.Icd10.ID + ', ' + card.Icd10.Rus_name,
				'Status': status
			}], true);

			that.findById('PersonDispList').getGrid().selModel.deselectRow(0);
		})
	}
});
