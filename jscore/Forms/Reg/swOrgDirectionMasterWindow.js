
sw.Promed.swOrgDirectionMasterWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swOrgDirectionMasterWindow',
	objectSrc: '/jscore/Forms/Reg/swOrgDirectionMasterWindow.js',
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_ORGREC,
	iconCls: 'workplace-mp16',
	id: 'swOrgDirectionMasterWindow',
	readOnly: false,
	onDirection: Ext.emptyFn,
	onHide: Ext.emptyFn,
	listeners: 
	{
		hide: function()
		{
			this.onHide();
		}
	},

	/**
	 * Панель фильтров
	 */
	Filters: null,
	
	/**
	 * Мастер записи, содержит в себе все формы и выбранные данные
	 */
	Wizard: null,
	
	/**
	 * Открытие списка записанных на выбранный день для службы/услуги
	 */
	openDayListTTMSO: function(date)
	{
		var id_salt = Math.random();
		var win_id = 'print_TTMSO_edit' + Math.floor(id_salt * 10000);
		window.open(C_TTMSO_LISTONEDAYFORRECPRINT + '&StartDay=' + date + '&MedService_id=' + this.MedService_id, win_id);
	},
	
    show: function()
    {
		sw.Promed.swOrgDirectionMasterWindow.superclass.show.apply(this, arguments);
	
        this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		if (  arguments[0] && arguments[0].userMedStaffFact )
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		if(arguments[0] && arguments[0].TimetableData){
			var ttdata = arguments[0].TimetableData;
		}else{
			this.hide()
		}
		
		this.MedService_id = ttdata.MedService_id;
		this.UslugaComplexMedService_id = ttdata.UslugaComplexMedService_id;
		
		this.Wizard.TTMSODirectionPanel.calendar.reset();
		this.onDirection = (arguments[0] && typeof arguments[0].onDirection == 'function') ? arguments[0].onDirection : Ext.emptyFn;
        this.onHide =  (arguments[0] && typeof arguments[0].onHide == 'function') ? arguments[0].onHide : Ext.emptyFn;

		this.Wizard.Panel.layout.setActiveItem('TTMSODirectionPanel');
		this.doLayout();
		//this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
		this.Wizard.TTMSODirectionPanel.MedService_id = ttdata.MedService_id;
		this.Wizard.TTMSODirectionPanel.UslugaComplexMedService_id = ttdata.UslugaComplexMedService_id;
		this.Wizard.TTMSODirectionPanel.loadSchedule(this.Wizard.TTMSODirectionPanel.calendar.value);

		this.Wizard.TTMSODirectionPanel.topToolbar.items.items[6].hide();

	},
	initComponent: function()
	{
		var win = this;
		
		this.Wizard = new Object({
			params: null,
			Panel: null,
			TTMSODirectionPanel: null
		});
		var win = this;
		// Панель расписания для записи на службу/услугу
		this.Wizard.TTMSODirectionPanel = new sw.Promed.swTTMSODirectionPanel({
			id:'TTMSODirectionPanel',
			frame: false,
			border: false,
			region: 'center',
			onDirection: function(data) {
				this.Wizard.TTMSODirectionPanel.loadSchedule(this.Wizard.TTMSODirectionPanel.calendar.value);
                this.onDirection(data);
				if (data) {
					win.hide();
				}
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this)
		});
		this.Wizard.Panel = new Ext.Panel(
		{
			region: 'center',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[
				
				this.Wizard.TTMSODirectionPanel
			]
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.Wizard.Panel
			],
			buttons: 
			[
			{
				text: '-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this),
				tabIndex: TABINDEX_DMW + 18
			}, 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		
		//this.Wizard.Panel.doLayout();
		
		sw.Promed.swOrgDirectionMasterWindow.superclass.initComponent.apply(this, arguments);
	}
});