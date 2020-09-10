/**
* amm_vacReport_5 - окно просмотра отчета формы №5
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author      Нигматуллин Тагир
* @version      июль 2012
* @comment      Префикс для id компонентов regv (amm_JournalViewWindow)
*/

function  initVacDateForm() {
		var dt = new Date();
		dt.setMonth(0, 1);
		var dt2 = new Date();
		dt2.setUTCFullYear(dt2.format('Y') + 1);
		Ext.getCmp('Date_Form').setValue(dt.format('d.m.Y') + ' - ' + dt2.format('d.m.Y'));
};

sw.Promed.ViewFrameVacPrn = function(config)
{
	Ext.apply(this, config);
	sw.Promed.ViewFrameVacPrn.superclass.constructor.call(this);
};

Ext.extend(sw.Promed.ViewFrameVacPrn, sw.Promed.ViewFrame, {
	/** Функция вызова печати данных грида
	*/
	printRecords: function()
	{
		var params = new Object();
		params.tableHeaderText = 
"<table class=vacheader style='width:600px;'><tbody>" +
"<tr><td>" +
"ФЕДЕРАЛЬНОЕ ГОСУДАРСТВЕННОЕ СТАТИСТИЧЕСКОЕ НАБЛЮДЕНИЕ" +
"</td></tr>" +
"</tbody></table>" +

"<table class=vacheader style='width:600px;'><tbody>" +
"<tr><td>" +
"КОНФИДЕНЦИАЛЬНОСТЬ ГАРАНТИРУЕТСЯ ПОЛУЧАТЕЛЕМ ИНФОРМАЦИИ" +
"</td></tr>" +
"</tbody></table>" +

"<table class=vacheader style='width:650px;'><tbody><tr><td>" +
'Нарушение порядка представления статистической информации, а равно представление недостоверной статистической информации влечет ответственность, установленную статьей 13.19 Кодекса Российской Федерации об административных правонарушениях ' +
'от 30.12.2001 № 195-ФЗ, а также статьей 3 Закона Российской Федерации от 13.05.92 № 2761-1 “Об ответственности за нарушение порядка представления государственной статистической отчетности”' +
"</td></tr></tbody></table>" +

"<table class=vacheader style='width:500px;'><tbody><tr><td>" +
"СВЕДЕНИЯ О ПРОФИЛАКТИЧЕСКИХ ПРИВИВКАХ<br>" +
"за  _________  20___  г.<br>" +
"         (квартал)" +
"</td></tr></tbody></table>" +

"<table class='vac prefer'>" +
"<tr>" +
"	<td class=frame>Представляют:</td>" +
"	<td class=frame style='width:110px;'>Сроки представления</td>" +
"	<td rowspan='9' style='width:120px; vertical-align:top;'><center>\n\
	 <table class=vac style='width:100px;'>\n\
		<tr><td class='frame'>Форма № 5</td></tr>\n\
		<tr><td>Утверждена постановлением Росстата от  21.09.2006 № 51</td></tr>\n\
		<tr><td class='frame'>Квартальная, годовая</td></tr>\n\
	 </table></center>\n\
	</td>" +
"</tr>" +
"<tr><td>" +
"амбулаторно-поликлинические учреждения (подразделения), оказывающие медицинскую помощь детям, подросткам и взрослым, дома ребенка, фельдшерско-акушерские пункты в сельских местностях (при отсутствии централизованных картотек в участковой или центральной районной больницах):<br>\n\
- ФГУЗ «Центр гигиены и эпидемиологии» в субъекте Российской Федерации;<br>\n\
- вышестоящей организации (ведомству) по подчиненности\n\
</td><td>10 числа после отчетного периода<br> за год – 15 января</td></tr><tr><td>\n\
\n\
амбулаторно-поликлинические учреждения (подразделения) ОАО « РЖД» дополнительно:<br>\n\
- ФГУЗ «Федеральный центр гигиены и эпидемиологии по железнодорожному транспорту»\n\
</td><td>10 числа после отчетного периода<br> за год – 15 января</td></tr><tr><td>\n\
\n\
ФГУЗ «Центры гигиены и эпидемиологии» в субъекте Российской Федерации:<br>\n\
- управлению Роспотребнадзора по субъекту Российской Федерации\n\
</td><td>20 числа после отчетного периода<br>за год – 25 января</td></tr><tr><td>\n\
\n\
ФГУЗ «Федеральный центр гигиены и эпидемиологии по железнодорожному транспорту»:<br>\n\
- Управлению Роспотребнадзора по железнодорожному транспорту\n\
</td><td>20 числа после отчетного периода<br>за год – 25 января</td></tr><tr><td>\n\
\n\
управления Роспотребнадзора по субъектам Российской Федерации:<br>\n\
- ФГУЗ ФЦГиЭ Роспотребнадзора;<br>\n\
- территориальному органу Росстата в субъекте Российской Федерации по установленному им адресу;<br>\n\
- органу управления здравоохранением субъекта Российской Федерации\n\
</td><td>за год –10 февраля</td></tr><tr><td>\n\
\n\
Управление Роспотребнадзора по железнодорожному транспорту:<br>\n\
- ФГУЗ ФЦГиЭ Роспотребнадзора\n\
</td><td>за год –10 февраля</td></tr><tr><td>\n\
\n\
ФГУЗ ФЦГиЭ Роспотребнадзора годовой отчет в целом по России и в разрезе субъектов Российской Федерации:<br>\n\
- Роспотребнадзору\n\
</td><td>25 числа после отчетного периода<br>за год - 10 февраля</td></tr><tr><td>\n\
\n\
Роспотребнадзор годовой отчет в целом по России и в разрезе субъектов Российской Федерации:<br>\n\
- Минздравсоцразвития России;<br>\n\
- Росстату\n\
</td><td>25 числа после отчетного периода<br>за год – 10 февраля<br>за год - 20 марта</td></tr>\n\
" +
"</table><br>";

		params.tableFooterText = 
"<br><table class=vac>" +
"<tr>" +
"	<td colspan='4'>Примечание:\n\
1. Здравпункты рачебные и фельдшерские, детские ясли, детские ясли-сады, школы самостоятельный отчет  не представляют, а сведения о прививках,\n\
	проведенных в указанных учреждениях, включают в отчет соответствующей больницы (поликлиники).\n\
2. В отчет включаются сведения о прививках, проведенных персоналом данного учреждения.\n\
	</td>" +
"</tr>" +
"<tr>" +
"	<td>Руководитель организации</td>" +
"	<td>____________________<br>(Ф.И.О.)</td>" +
"	<td>____________________<br>(подпись)</td>" +
"	<td></td>" +
"</tr>" +
"<tr>" +
"	<td>Должностное лицо, ответственное за составление формы</td>" +
"	<td>____________________<br>(должность)</td>" +
"	<td>____________________<br>(Ф.И.О.)</td>" +
"	<td>____________________<br>(подпись)</td>" +
"</tr>" +
"<tr>" +
"	<td></td>" +
"	<td>____________________<br>(номер контактного телефона)</td>" +
"	<td>«____» _________20__ год<br>(дата составления документа)</td>" +
"	<td></td>" +
"</tr></table>";
//		Ext.ux.GridPrinter.print(this.ViewGridPanel, params);
		Ext.ux.GridPrinterVac.print(this.ViewGridPanel, params);
	}
});

sw.Promed.amm_vacReport_5 = Ext.extend(sw.Promed.BaseForm, {
//        Ext.extend(Ext.Window, {
	id: 'amm_vacReport_5',
//  title: 'Отчет формы №5',
	title: 'Иммунопрофилактика. Отчет № 5',
        titleBase: 'Иммунопрофилактика. Отчет № 5',
        codeRefresh: true, 
	border: false,
	width: 700,
	maximizable: true,        
	height: 400,
        maximized: true,
//	codeRefresh: true,
        buttons: [
                 {
                        text: 'Сформировать отчет',
                        //                            tabIndex: TABINDEX_PEF + 54,
                        //                            iconCls: 'vac-plan16',
                        iconCls: 'inj-stream16',
                        id: 'Vac_FormPlan',
                        disabled: false,
                        tabIndex: TABINDEX_STARTVACFORMPLAN + 6,

				handler: function() {
                                        Ext.getCmp('amm_ViewReportForm5').initGrid();
//                                 }.createDelegate(this),
//						'blur': function () {
//							Ext.getCmp('amm_ViewReportForm5').initGrid();
////              Ext.getCmp('BTN_GRIDPRINT').focus(true, 50);
//						}.createDelegate(this),
//						'success': function(source, params) {
//								 alert('select!');
//						}
//
//					},
                                }
                                    
                        },
                        {
                        text: '-'
			},

			 //HelpButton(this, TABINDEX_REPORT5 + 2),
                        {      text: BTN_FRMHELP,
                                iconCls: 'help16',
                                tabIndex: TABINDEX_REPORT5 + 2,
                                handler: function(button, event)
                                {
                                        ShowHelp(this.ownerCt.titleBase);
                                }
			},
                 
                         
			 {
						text:BTN_GRIDPRINT,
						tabIndex : TABINDEX_REPORT5 + 3,
						tooltip: BTN_GRIDPRINT,
						iconCls: 'print16',
						handler: function() 
						{
								 Ext.getCmp('amm_ViewReportForm5').printRecords();                            
						}


			 },
			{
				handler   : function()
				{
								this.ownerCt.hide();
				},
				iconCls: 'close16',
				id: 'EPLSIF_CancelButton',
//        onTabAction: function () {
//          this.findById('EPLSIF_EvnVizitPL_setDate').focus(true, 100);
//        }.createDelegate(this),
				tabIndex : TABINDEX_REPORT5 + 4,
				text: '<u>З</u>акрыть',
				 onTabAction: function () {
						 Ext.getCmp('Date_Form').focus(true, 50)    
				 }     
			} ],
				
	initComponent: function() {
				 
		this.ViewReportForm5 = new sw.Promed.ViewFrameVacPrn(
		{
			id: 'amm_ViewReportForm5',
			dataUrl: '/?c=Vaccine_List&m=vacFormReport_5',
			title: 'Отчет формы №5',
			toolbar: true,
			setReadOnly: false,
			autoLoadData: true,
			cls: 'txtwrap',
			paging: false,
			totalProperty: 'totalCount', 
			layout:'form',
			region: 'center',
			autoLoadData: false,
			autoheight: true,
			autowith: true,
			tabIndex : TABINDEX_REPORT5 + 1,
			onTabAction: function () {
						 Ext.getCmp('BTN_GRIDPRINT').focus(true, 50)    
				 },
			stringfields:
			[	   							
						{ name: 'vacReportF5_id',   type: 'int', header: 'ID',  key: true },
						{ name: 'vacReportF5_Name',  type: 'string', header: 'Наименование', width: 400 },
						{ name: 'vacReportF5_NumStr', type: 'string', header: 'Номер строки', width: 150 },
						{ name: 'vacReportF5_Kol', type: 'int', header: 'Число привитых лиц', width: 150},
						{name: 'VaccineType_id',  type: 'int', header: 'Идентификатор прививки',  hidden: true}                                  
			],
						
			actions:
			[
			{ name:'action_add', hidden: true},

			{name:'action_edit', hidden: true},

			{
				name:'action_view', 
				handler: function(){
                                    var rowSelected = Ext.getCmp('amm_ViewReportForm5').getGrid().getSelectionModel().getSelected();                                   
                                            //this.findById('amm_PersonPersonMantu').getGrid().getSelectionModel().getSelected();
                                    var  params = new Object(); 
                                    params.DateStart = Ext.getCmp('Date_Form').getValue1().format('d.m.Y');
                                    params.DateEnd = Ext.getCmp('Date_Form').getValue2().format('d.m.Y');
                                    /*
                                    params.Lpu_id =getGlobalOptions().lpu_id;
                                    params.LpuBuilding_id = Ext.getCmp('rep5_LpuBuildingCombo').getValue();   
                                    params.LpuSection_id = Ext.getCmp('rep5_LpuSectionCombo').getValue();
                                     if (Ext.getCmp('rep5_LpuRegionCombo').getValue() != -1) {
                                        params.LpuRegion_id = Ext.getCmp('rep5_LpuRegionCombo').getValue();
                                    };
                                    */
                                    if (Ext.getCmp('rep5_ComboMedServiceVac').getValue() != '') {
                                                    params.MedService_id =Ext.getCmp('rep5_ComboMedServiceVac').getValue();
                                                    params.lpuMedService_id =getGlobalOptions().lpu_id;
                                                  
                                                 }
                                                 else {
                                                    params.Lpu_id =getGlobalOptions().lpu_id; 
                                                    params.LpuBuilding_id = Ext.getCmp('rep5_LpuBuildingCombo').getValue();   
                                                    params.LpuSection_id = Ext.getCmp('rep5_LpuSectionCombo').getValue();
                                                    if (Ext.getCmp('rep5_LpuRegionCombo').getValue() != -1) {
                                                        params.LpuRegion_id = Ext.getCmp('rep5_LpuRegionCombo').getValue();
                                                    };
                                                 }
                                   params.Organized = Ext.getCmp('rep5_PopulationCombo').getValue();
                                    params.Num_Str = rowSelected.data.vacReportF5_NumStr;
                                    params.title = rowSelected.data.vacReportF5_Name
                                    
                                    sw.Promed.vac.utils.consoleLog('- до вызова формы');
                                    getWnd('amm_vacRep_5DetailForm').show(params)
                                }.createDelegate(this)
                                
			},

			{
				name:'action_delete', 
				hidden: true
			}
			],
			onDblClick: function() {
                            this.getAction('action_view').execute();
                        },
			onLoadData: function() {
			},
			
			initGrid: function() {
				if ( Ext.getCmp('Date_Form').value != null) {    
//             if ( Ext.getCmp('Date_Form').getValue1().trim() != '') {  
						var  params = new Object(); 
                                                var dt = new Date();
						params.DateStart = Ext.getCmp('Date_Form').getValue1().format('d.m.Y');
						dt = Ext.getCmp('Date_Form').getValue2();
						Ext.getCmp('amm_vacReport_5').title = 'Отчет формы №5: ' + Ext.getCmp('Date_Form').value
						Ext.getCmp('amm_ViewReportForm5').title = Ext.getCmp('amm_vacReport_5').title

						dt.setDate(dt.getDate() + 1);
						//params.DateEnd = dt.format('d.m.Y');
                                                params.DateEnd = Ext.getCmp('Date_Form').getValue2().format('d.m.Y');
						
                                                 if (Ext.getCmp('rep5_ComboMedServiceVac').getValue() != '') {
                                                    params.MedService_id =Ext.getCmp('rep5_ComboMedServiceVac').getValue();
                                                    params.lpuMedService_id =getGlobalOptions().lpu_id;
                                                   
                                                 }
                                                 else {
                                                    params.Lpu_id =getGlobalOptions().lpu_id; 
                                                    params.LpuBuilding_id = Ext.getCmp('rep5_LpuBuildingCombo').getValue();   
                                                    //params.LpuUnit_id =
                                                    params.LpuSection_id = Ext.getCmp('rep5_LpuSectionCombo').getValue();
                                                    if (Ext.getCmp('rep5_LpuRegionCombo').getValue() != -1) {
                                                        params.LpuRegion_id = Ext.getCmp('rep5_LpuRegionCombo').getValue();
                                                    };  
                                                    
                                                 }
                                                     
                                                
                                                
                                                //params.lpuRegion_id = 
                                                params.Organized = Ext.getCmp('rep5_PopulationCombo').getValue();

						Ext.getCmp('amm_ViewReportForm5').ViewGridPanel.getStore().baseParams = params;
						Ext.getCmp('amm_ViewReportForm5').ViewGridPanel.getStore().reload();
							Ext.getCmp('amm_ViewReportForm5').getGrid().getSelectionModel().selectFirstRow();
		//        Ext.getCmp('BTN_GRIDPRINT').focus(true, 50);   
				 }
			}
		});
		
		this.ViewReportForm5.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (row.get('VaccineType_id') == -1)
					cls = 'x-grid-rowblue ';
//		else 
//        cls = 'x-grid-rowbold ';
				return cls;
			}
		});
				
		Ext.apply(this, {
			bodyBorder : true,
			layout : "border",
			cls: 'tg-label',
//      bodyStyle: 'padding: 5px',
			items : [{
				region: 'north',
				layout : "form",
                                bodyBorder : false,
				autoHeight: true,
				labelWidth : 180,
				labelAlign : "right",
				items : [{
					height : 10,
					border : false,
					cls: 'tg-label'
				}, {
					name : "Date_Form",
					id: 'Date_Form',
					xtype : "daterangefield",
					width : 170,
					fieldLabel : 'Период отчетности',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex : TABINDEX_REPORT5,
						onTabAction: function () {
						 Ext.getCmp('BTN_GRIDPRINT').focus(true, 50)    
				 } 
				},
                                        {
						
					//==колонки:===========================================	
						border: false,
						layout: 'column',
                                                border : false,
                                                defaults: {
							bodyBorder: false,
							anchor: '100%'
						},
//						width : 1050,
//						defaults: {
//							bodyBorder: false,
//							anchor: '100%'
						
                                                        items: [{//столбец 1
							layout: 'form',
                                                        labelWidth : 180,
							//columnWidth : 0.35,
							defaults: {
								width : 200
							},
							items: [
                                {
                                        height : 20,
                                        border : false,
                                        cls: 'tg-label'
                                },{
                                            fieldLabel: 'Служба',
                                            id: 'rep5_ComboMedServiceVac',
                                            listWidth: 600,
                                            //tabIndex: TABINDEX_VACPRPFRM + 26,
                                            width: 200,
                                           emptyText: VAC_EMPTY_TEXT,
                                            xtype: 'amm_ComboMedServiceVacExtended'
                                            , listeners: {
                                                   'select': function(combo, record, index) {
                                                        if (combo.getValue() != '') {
                                                            Ext.getCmp('rep5_paramsframe').hide();
                                                        }
                                                       //alert(combo.getValue()); }
                                                   else
                                                       //{alert ('No');};
                                                       Ext.getCmp('rep5_paramsframe').show();
                                                       //Ext.getCmp('amm_WorkPlaceVacCabinetWindow').MedService_id = combo.getValue();
                                                   }.createDelegate(this)
                                                }
                                            },
                                        {
                                    id: 'rep5_PopulationCombo',
                                    xtype : "amm_OrgUnOrgPopulationCombo",
                                    hiddenName:'OrgType_id'
                                    //  население
                                    //tabIndex : TABINDEX_VACMAINFRM + 8
                                },
//                                        {
//                                        height : 10,
//                                        border : false,
//                                        cls: 'tg-label'
//                                }
                                        ]
                                    },
                                            {layout: 'form',
                                             id: 'rep5_paramsframe',
                                             autoHeight: true,
                                             labelWidth : 150,
                                             items : [
                                                 
//                                             {
//                                                    height : 10,
//                                                    border : false,
//                                                    cls: 'tg-label'
//                                            },
                                                    
                                             {

                                            id: 'rep5_LpuBuildingCombo',
                                            //hidden: true,        
                                            //lastQuery: '',
                                            //
                                            // Скрываем комбобокс 
                                            hidden: true, 
                                            fieldLabel: '',
                                            labelSeparator: '',
                                            //************
                                    
                                            listWidth: 600,
                                            listeners: {
                                             'select': function(combo, record, index) {
                                                 Ext.getCmp('rep5_LpuSectionCombo').getStore().load ({
                                                                 params:{
                                                                                 LpuBuilding_id: combo.getValue()
                                                                 },
                                                                 callback: function() {
                                                                                 Ext.getCmp('rep5_LpuSectionCombo').reset();
                                                                            }           
                                                             })
                                                 ,Ext.getCmp('rep5_LpuRegionCombo').getStore().reload ({
                                                                 params:{
                                                                        Lpu_id: getGlobalOptions().lpu_id,         
                                                                        LpuBuilding_id: combo.getValue()
                                                                 },
                                                                 callback: function() {
                                                                                 Ext.getCmp('rep5_LpuRegionCombo').reset();
                                                                            }           
                                                             })            
                                                            }.createDelegate(this)
                                             },
                                            linkedElements: [
                                            'rep5_LpuSectionCombo'
                                            ],
                                            //tabIndex: TABINDEX_VACPRPFRM + 21,
                                            width: 260,
                                            xtype: 'swlpubuildingglobalcombo'
                                    },
                                     {
                                            id: 'rep5_LpuSectionCombo',
                                            //linkedElements: [
                                            listWidth: 600,
                                            parentElementId: 'rep5_LpuBuildingCombo',
                                            //tabIndex: TABINDEX_VACPRPFRM + 22,
                                            // Скрываем комбобокс 
                                            hidden: true, 
                                            fieldLabel: '',
                                            labelSeparator: '',
                                            //************
                                            width: 260,
                                            xtype: 'swlpusectionglobalcombo'
                                    },
                                    {
				autoLoad: true,
                                id: 'rep5_LpuRegionCombo',
                                hiddenName : "uch_id",
                                xtype : "amm_uchListCombo",
                                fieldLabel : "Участок"
//                                tabIndex : TABINDEX_VACMAINFRM + 6
							}
                                             ]}
                                                        ]}
                            
                                        ]
			},
			this.ViewReportForm5
			 
			]
			
		});

		sw.Promed.amm_vacReport_5.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
		sw.Promed.amm_vacReport_5.superclass.show.apply(this, arguments);
                var dt = new Date();
                Ext.getCmp('Date_Form').setValue(dt.format('d.m.Y') + ' - ' + dt.format('d.m.Y'));
                Ext.getCmp('Date_Form').focus(true, 50);
                     var combobuilding = Ext.getCmp('rep5_LpuBuildingCombo')
                             //amm_vacReport_5.form.findField('purp_buildingComboServiceVac');
                                    combobuilding.reset();      
                                    combobuilding.getStore().load(
                    {
                         params:{
                             Lpu_id: getGlobalOptions().lpu_id
                         }  
                    }) ;
                 //**********
                 Ext.getCmp('rep5_LpuRegionCombo').getStore().load({
                        params: {
                                lpu_id: getGlobalOptions().lpu_id
                        },
                        callback: function() {
                                 Ext.getCmp('rep5_LpuRegionCombo').setValue(-1);
                        }.createDelegate(this)
                });
                
                Ext.getCmp('rep5_ComboMedServiceVac').getStore().load ({
                                 params:{
                                                 Lpu_id: getGlobalOptions().lpu_id
                                 }
                                 ,
                                 callback: function() {
                                                 Ext.getCmp('rep5_ComboMedServiceVac').reset();
                                                 //Ext.getCmp('CabVac_ComboMedServiceVac').setValue(0)
                                                 //Ext.getCmp('amm_WorkPlaceVacCabinetWindow').doSearch('day');
                                 }           
                             });
                             
                Ext.getCmp('rep5_LpuSectionCombo').hide(true);             
                             
                 //**********
            }

    });