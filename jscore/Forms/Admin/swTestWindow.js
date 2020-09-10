sw.Promed.swTestWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: 'Подождите... ' });
		}
		return this.loadMask;
	},
	height: 500,
	id: 'TestWindow',
	initComponent: function() {
		var thisWindow = this;

		this.MorbusPanel = new sw.Promed.MorbusPanel({});
		Ext.apply(this, {
			keys:[],
			buttons: [
                {
                                    text: 'text111',
                                    xtype: 'button',
                                    width: 100,
                                    height: 20,
                                    onClick: function() {
                                        Ext.Ajax.request({
                                            failure: function(response, options) {
                                                sw.swMsg.alert('Ошибка');
                                            },
                                            params: {},
                                            success: function(response, options) {
                                                alert(response.responseText);
                                            },
                                            url: '/?c=MorbusOnkoSpecifics&m=test'
                                        });
                                    }
                                }
            ],
			items:[
				this.MorbusPanel


			]
		});
		sw.Promed.swTestWindow.superclass.initComponent.apply(this, arguments);
	},
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swTestWindow.superclass.show.apply(this, arguments);
        this.MorbusPanel.load({/*Person_id: 57790, Diag_id: 4467,*/ Evn_id: 73002309618718});
	},
	title: 'Тестовая форма',
	width: 800
});

/*
				{
					text: 'text111',
					xtype: 'button',
					width: 100,
					height: 20,
					onClick: function() {
						Ext.Ajax.request({
							failure: function(response, options) {
								sw.swMsg.alert('Ошибка');
							},
							//params: params,
							success: function(response, options) {
								alert(response.responseText);
							},
							url: '/?c=Test&m=DummySelect'
						});
					}
				},
				{
					allowBlank: false,
					comboSubject: 'AutopsyPerformType',
					fieldLabel: 'Аутопсия',
					hiddenName: 'AutopsyPerformType_id',
					width: 288,
					xtype: 'swcommonsprcombo'
				},
				{comboSubject: 'OnkoRegType'                 ,fieldLabel: 'Взят на учет в ОД'                                ,hiddenName: 'OnkoRegType_id'                 ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'OnkoRegOutType'              ,fieldLabel: 'Причина снятия с учета'                           ,hiddenName: 'OnkoRegOutType_id'              ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'OnkoLesionSide'              ,fieldLabel: 'Сторона поражения'                                ,hiddenName: 'OnkoLesionSide_id'              ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'OnkoT'                       ,fieldLabel: 'T'                                                ,hiddenName: 'OnkoT_id'                       ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'OnkoN'                       ,fieldLabel: 'N'                                                ,hiddenName: 'OnkoN_id'                       ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'OnkoM'                       ,fieldLabel: 'M'                                                ,hiddenName: 'OnkoM_id'                       ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'TumorStage'                  ,fieldLabel: 'Стадия опухолевого процесса'                      ,hiddenName: 'TumorStage_id'                  ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'OnkoMetastasesLocalType'     ,fieldLabel: 'Локализация отдаленных метастазов'                ,hiddenName: 'OnkoMetastasesLocalType_id'     ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'OnkoDiagConfirmMethodType'   ,fieldLabel: 'Метод подтверждения диагноза'                     ,hiddenName: 'OnkoDiagConfirmMethodType_id'   ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'TumorCircumIdentType'        ,fieldLabel: 'Обстоятельства выявления опухоли'                 ,hiddenName: 'TumorCircumIdentType_id'        ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'OnkoLateDiagCause'           ,fieldLabel: 'Причины поздней диагностики'                      ,hiddenName: 'OnkoLateDiagCause_id'           ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'TumorAutopsyResultType'      ,fieldLabel: 'Результат аутопсии применительно к данной опухоли',hiddenName: 'TumorAutopsyResultType_id'      ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'TumorPrimaryTreatType'       ,fieldLabel: 'Проведенное лечение первичной опухоли'            ,hiddenName: 'TumorPrimaryTreatType_id'       ,width: 288,xtype: 'swcommonsprcombo'},
				{comboSubject: 'TumorRadicalTreatIncomplType',fieldLabel: 'Причины незавершенности радикального лечения'     ,hiddenName: 'TumorRadicalTreatIncomplType_id',width: 288,xtype: 'swcommonsprcombo'}





 */