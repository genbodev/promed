/**
 * swPersonAmbulatCardWindow - окно.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 */
sw.Promed.swPersonAmbulatCardWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	title:"Амбулаторная карта",
	width: 800,
	minHeight:435,
	height:435,
	maxHeight:400,
	id: 'swPersonAmbulatCardWindow',
	selectedParams: {},
	listeners: {
		hide: function () {
			this.winHide();
		}
	},
	layout: 'border',
	maximizable: true,
	minHeight: 435,
	minWidth: 700,
	modal: false,
	winHide: function(){
		this.callback(this.selectedParams, this.TimetableGraf_id);
	},
	doSave: function (callback,ignoreUniq) {
		//debugger;
	},
	submit: function (callback,ignoreUniq) {
		//debugger;
	},
	show: function () {
		sw.Promed.swPersonAmbulatCardWindow.superclass.show.apply(this, arguments);
		var that = this;
		if (!arguments[0] || !arguments[0].Person_id) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
					title: langs('Ошибка'),
					fn: function () {
						this.hide();
					}.createDelegate(this)
				}
			);
		}
		this.selectedParams = {};
		this.Person_id = arguments[0].Person_id;
		this.Person_FIO = (arguments[0].Person_FIO) ? arguments[0].Person_FIO : '';
		this.Person_Birthday =  (arguments[0].Person_Birthday) ? arguments[0].Person_Birthday : '';
		this.TimetableGraf_id = (arguments[0].TimetableGraf_id) ? arguments[0].TimetableGraf_id : null;
		this.setTitle("Амбулаторная карта: " + this.Person_FIO + ' ' + this.Person_Birthday);
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.focus();
		
		if ( !this.AmbulatCardsGrid.getAction('actions') ) {
			this.AmbulatCardsGrid.addActions({
				name: 'choose_a_card_to_receive_a_patient',
				text:'Выбрать',
				tooltip: 'Выбрать карту для приёма пациента',
				iconCls : 'x-btn-text',
				icon: 'img/icons/emk/steps-icon-accepted.png',
				handler: function() {
					this.choose_card();
				}.createDelegate(this)
			}, 1);
		}

		this.loadAmbulatCardsGrid();
	},

	loadAmbulatCardsGrid:function(){	
	    var win = this;
	    if(!win.Person_id) return false;

	    var grid = this.AmbulatCardsGrid.getGrid();
	    var baseParams = {Person_id: this.Person_id, Lpu_id: getGlobalOptions().lpu_id};
		var params = 0;
		params.limit = 5;
	    grid.getStore().removeAll();
	    grid.getStore().baseParams = baseParams;
	   
		grid.getStore().load({
			callback: function(){
				var grid = this.AmbulatCardsGrid.getGrid();
				var store = grid.getStore();
				if ( store.getCount() == 1 && !store.getAt(0).get('PersonAmbulatCard_id') ) {
					store.removeAll();
				}
			}.createDelegate(this)
		});
		
	},
	openAmbulatCardLocatWindow:function(action){
		var action = 'view';
	    var  params = {};
	    var grid = this.findById('AmbulatCardsGrid').getGrid();
	    
		if(!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonAmbulatCard_id')){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: langs('Ошибка открытия формы.<br/>Не выбрана карта для просмотра.'),
					title: langs('Ошибка'),
					fn: function () {
						this.hide();
					}.createDelegate(this)
				}
			);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		params ={
			action: action,
			PersonAmbulatCard_id: record.get('PersonAmbulatCard_id')
		}
	    getWnd('swPersonAmbulatCardEditWindow').show(params);
	},
	choose_card: function(){
		var win = this;
		var params = {};
		var grid = this.findById('AmbulatCardsGrid').getGrid();
		if(!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonAmbulatCard_id')){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: langs('Ошибка.<br/>Не выбрана карта.'),
					title: langs('Ошибка'),
					fn: function () {
						//this.hide();
					}.createDelegate(this)
				}
			);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		params.PersonAmbulatCard_id = record.get('PersonAmbulatCard_id');
		params.PersonAmbulatCard_Num = record.get('PersonAmbulatCard_Num');
		params.AttachmentLpuBuilding_Name = record.get('AttachmentLpuBuilding_Name');
		params.MapLocation = record.get('MapLocation');
		params.CardLocationMedStaffFact_id = record.get('CardLocationMedStaffFact_id');
		this.selectedParams = params;
		//this.callback(params, this.TimetableGraf_id);
		this.hide();
	},
	initComponent: function () {
		var that = this;
		this.AmbulatCardsGrid = new sw.Promed.ViewFrame({
			//tbActions: true,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_print', disabled: false},
				{ name: 'action_refresh', disabled: false},
				{ name: 'action_view', disabled: false, handler:function(){that.openAmbulatCardLocatWindow('edit')}}
				/*{
					name: 'choose_a_card_to_receive_a_patient',
					tooltip: 'Выбрать карту для приёма пациента',
					text: 'Выбрать',
					handler: function() {
						debugger;
					}.createDelegate(this)
				}*/
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=PersonAmbulatCard&m=getPersonAmbulatCardList',
			region: 'center',
			object: 'PersonAmbulatCardLocat',
			editformclassname: 'swPersonAmbulatCardWindow',
			id: 'AmbulatCardsGrid',
			paging: false,
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'PersonAmbulatCard_id', type: 'int', header: 'ID', key: true},
				{name: 'AmbulatCardLocatType_id', type: 'int', hidden:true},
				{name: 'PersonCard_id', type: 'int', hidden: true },
				{name: 'CardLocationMedStaffFact_id', type: 'int', hidden: true },
				{name: 'PersonAmbulatCard_Num', type: 'string', header: langs('Номер карты')},
				{name: 'AttachmentLpuBuilding_Name', type: 'string', header: langs('Подразделение прикрепления карты')},
				{name: 'PersonAmbulatCard_begDate', type: 'date', header: langs('Дата закрытия карты')},
				{name: 'PersonAmbulatCard_CloseCause', type: 'string', header: langs('Причина закрытия карты')},
				{name: 'PersonAmbulatCardLocat_begDate', type: 'date', header: langs('Дата последнего движения')},
				{name: 'MapLocation', type: 'string', header: langs('Местонахождение карты'), id: 'autoexpand'}
			],
			onDblClick: function() {
				Ext.getCmp('swPersonAmbulatCardWindow').choose_card();
			}
		});
		
		Ext.apply(this, {
			buttons: [
				{
					text: '-'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event) {
						ShowHelp(WND_AMBULATORY_CARDS);
					}.createDelegate(this)
				},
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function() {
						this.hide(); 
					}.createDelegate(this)
				}
			],
			items: [
				this.AmbulatCardsGrid
			]
		});
		sw.Promed.swPersonAmbulatCardWindow.superclass.initComponent.apply(this, arguments);
	}
});