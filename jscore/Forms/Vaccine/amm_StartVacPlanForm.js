/**
* amm_JournalViewWindow - окно просмотра журналов вакцинации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Нигматуллин Тагир
* @version      июнь 2012
* @comment      Префикс для id компонентов regv (amm_JournalViewWindow)
*/

function  initVacDatePlan() {
	var dt = new Date();
	var dt2 = new Date();
	dt2.setUTCFullYear(dt2.format('Y') + 1);
	var dt_max = new Date(dt2.format('Y'), 11, 31); 
	Ext.getCmp('Date_Plan').setValue(dt.format('d.m.Y') + ' - ' + dt2.format('d.m.Y'));
	Ext.getCmp('Date_Plan').setMinValue (  new Date( new Date().format('Y'), new Date().getMonth(), new Date().getDate() ) );
	Ext.getCmp('Date_Plan').setMaxValue (dt_max);
}; 
	 
sw.Promed.amm_StartVacPlanForm = Ext.extend(sw.Promed.BaseForm, {

	title: "Планирование вакцинации",
	border: false,
	width: 400,
	id: 'ammStartVacFormPlan',
	codeRefresh: true,   
	objectName: 'amm_StartVacPlanForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_StartVacPlanForm.js',
	onHide: Ext.emptyFn,
	
	initComponent: function() { 
		var paramsMode = new Object();
		paramsMode.Org_id = 0;
		
		this.myRadioGroup = new Ext.form.RadioGroup({
			id:'myGroupR',
			xtype: 'radiogroup',
			fieldLabel: 'Способ прикрепления',

			columns: 1,
			items: [
			{
				boxLabel: 'По месту жительства', 
				name: 'AttributePatient',  
				checked: true, 
				tabIndex: TABINDEX_STARTVACFORMPLAN + 1
			},
	
			{
				boxLabel: 'По месту работы/учебы ', 
				name: 'AttributePatient', 
//                checked: true, 
				tabIndex: TABINDEX_STARTVACFORMPLAN + 2
			}
	],
	 listeners: {
		 'change': function() {
					//             Ext.Msg.alert('Info', 'check');
			 if (Ext.getCmp('myGroupR').items.items[0].checked)
					{
						Ext.getCmp('vacFormCriterion').show();
						Ext.getCmp('vacGroupOrgJob2Lpu').hide();
					}
			else
					{
						Ext.getCmp('vacFormCriterion').hide();
						Ext.getCmp('vacGroupOrgJob2Lpu').show();
					}
						Ext.getCmp('ammStartVacFormPlan').syncShadow();//перерисовка тени под изменившееся окно
		 }
	 }
		});   

		this.myRadioGroupPatient = new Ext.form.RadioGroup({
			id:'myGroupPatient',
//            id:'myGroupR2',
			xtype: 'fieldset',
			layout : "form",
			width: 200,
			labelSeparator: '',
			columns: 1,
			items: [
			{
				boxLabel: 'Все пациенты ', 
				name: 'OrgPatient',  
				checked: true, 
				tabIndex: TABINDEX_STARTVACFORMPLAN + 3
			},

			{
				boxLabel: 'Организованные', 
				name: 'OrgPatient', 
				tabIndex: TABINDEX_STARTVACFORMPLAN + 4
			},

			{
				boxLabel: 'Неорганизованные', 
				name: 'OrgPatient', 
				tabIndex: TABINDEX_STARTVACFORMPLAN + 5
			}

			]
		}); 


		Ext.apply(this, {
			//     new Ext.form.FormPanel({
			frame: true,           
			labelWidth : 150,  
			bodyBorder : true,
			layout : "form",
			//         style: 'background-color: #fff;',
			cls: 'tg-label',
			autoHeight: true,

			items : [
			
			new Ext.form.FormPanel({
				//style: 'margin: 5px; padding: 0px;',
				frame: true,
				id: 'ammStartVacFormPlanPanel',  
				items : [
				{
		   
					height : 10,
					border : false,
					cls: 'tg-label'
				}, 
				{
					name : 'Date_Plan',
					id: 'Date_Plan',
					xtype : "daterangefield",
                                        allowBlank: true,
					width : 170,
					fieldLabel : '   Период планирования',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: TABINDEX_STARTVACFORMPLAN + 0
				},

				{
					height : 20,
					border : false
				//            style: 'padding: 0px;'
				}, 
  
				//  ВРЕММЕНО УБРАЛ 
				this.myRadioGroup,
			 
				{
					height : 20,
					border : false
				//            style: 'padding: 0px;'
				},
		
				{
					id: 'vacFormCriterion', 
					autoHeight: true,
					autoScroll: true,
					//                style: 'padding: 0px;',
					title: 'Критерии формирования',
					//                style: 'background-color: #fff;',
					labelWidth: 90,
					xtype: 'fieldset',
					items: [
					this.myRadioGroupPatient
					]
				},
				
				 {
					id: 'vacGroupOrgJob2Lpu', 
					autoHeight: true,
					autoScroll: true,
					//                style: 'padding: 0px;',
					title: 'Обслуживаемые организации',
					//                style: 'background-color: #fff;',
					labelWidth: 90,
					xtype: 'fieldset'
					, items: [
					{
					//       Org_id, Org_Nick
					id: 'Vac_OrgJob2LpuCombo',
					editable: false,
					valueField: 'Org_id',
					displayField: 'Org_Nick',
					tabIndex: TABINDEX_STARTVACFORMPLAN + 10,
					//                                hiddenName: 'Vaccine_id',
					width: 230,
					allowBlank: false,
					xtype: 'amm_VacOrgJob2LpuCombo'
										, listeners: {
											'select': function(combo)  {
											  paramsMode.Org_id = combo.getValue();

											}}  //  !!!
				}
					]
				  

				}
				
			 //   */
				]
			})
			//             }
			],
			buttons: [
						 
			{
				text: 'Сформировать план',
				//                            tabIndex: TABINDEX_PEF + 54,
				//                            iconCls: 'vac-plan16',
				iconCls: 'inj-stream16',
				id: 'Vac_FormPlan',
				disabled: false,
				tabIndex: TABINDEX_STARTVACFORMPLAN + 6,
						  
				handler: function() {
					var $DatePlan = Ext.getCmp('Date_Plan');
					var $str = '';
					var dt1 = $DatePlan.getValue1();
					var dt2 = $DatePlan.getValue2();
					if ($DatePlan.getValue1() < $DatePlan.minValue) {
						$str = 'Дата начала планирования  меньше минимального значения!';
						console.log($DatePlan.getValue1(), $DatePlan.minValue);
						//$DatePlan.setValue1($DatePlan.minValue);
						$DatePlan.setValue($DatePlan.minValue.format('d.m.Y') + ' - ' + dt2.format('d.m.Y'));
					} else if ($DatePlan.getValue2().format('d.m.Y') > $DatePlan.maxValue.format('d.m.Y')) {
						$str = 'Дата окончания планирования  превышает максимальное значение!';
						 $DatePlan.setValue(dt1.format('d.m.Y') + ' - ' + $DatePlan.maxValue.format('d.m.Y'));
					};
					if ($str != '') {
						Ext.Msg.alert ('Внимание', $str);
						 return false;
					}
					var PlanForm = Ext.getCmp('ammStartVacFormPlanPanel');
					if (!PlanForm.form.isValid()) {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						return false;
					}

				  if ( Ext.getCmp('Date_Plan').getValue1().format('d.m.Y') + Ext.getCmp('Date_Plan').getValue1().format('d.m.Y') == '' )
				  {
					  Ext.MessageBox.show({
							  title: "Проверка данных формы",
							  msg: "Не введен период планирования!",
							  buttons: Ext.Msg.OK,
							  icon: Ext.Msg.WARNING
							});
							Ext.getCmp('Date_Plan').focus();
					  }
					else {
					  
						var vMode; 
						if //(!paramsMode.Org_id)
							(paramsMode != undefined){
						  
							paramsMode.Org_id = paramsMode.Org_id;
							   }
						else
							paramsMode.Org_id = 0;
						if (Ext.getCmp('myGroupR').items.items[0].checked)
						{
							if (Ext.getCmp('myGroupPatient').items.items[0].checked)
								vMode = 1
							else if (Ext.getCmp('myGroupPatient').items.items[1].checked)
								vMode = 2
							if (Ext.getCmp('myGroupPatient').items.items[2].checked)
								vMode = 3
						}
						else 
							if (paramsMode.Org_id == 0)
								 vMode = 4;
							else
								vMode = 5; 
					 
						Ext.Ajax.request({
							url: '/?c=Vaccine_List&m=RunformPlanVac',
							method: 'POST',
							params: {
								'Lpu_id' : getGlobalOptions().lpu_id,
								//                                        'pmUser_id' : getGlobalOptions().pmuser_id,        
								'Plan_begDT': Ext.getCmp('Date_Plan').getValue1().format('d.m.Y'),
								'Plan_endDT': Ext.getCmp('Date_Plan').getValue2().format('d.m.Y'),
								'Mode': vMode,
								'Org_id': paramsMode.Org_id
							},
							success: function(){
								Ext.Msg.alert('Сообщение',  'Задание поставлено!');
								Ext.getCmp('ammStartVacFormPlan').hide();

							}
						}) 
					}
				}            
			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_STARTVACFORMPLAN + 7),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EPLSIF_CancelButton',
				onTabAction: function () {
					Ext.getCmp('Date_Plan').focus();
				}.createDelegate(this),
				//				onTabAction: function () {
				//+					Ext.getCmp('Date_Plan').focus();
				//+				}.createDelegate(this),

				text: '<u>З</u>акрыть',
				tabIndex: TABINDEX_STARTVACFORMPLAN + 8
			} ]
		}
		);   
							
		sw.Promed.amm_StartVacPlanForm.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.amm_StartVacPlanForm.superclass.show.apply(this, arguments);
				
		initVacDatePlan ();
		Ext.getCmp('Date_Plan').focus(true, 50);
		
		Ext.getCmp('Vac_OrgJob2LpuCombo').store.load({
			  params: {
		lpu_id: Ext.globalOptions.globals.lpu_id
	  }
		});
		
		if (Ext.getCmp('myGroupR').items.items[0].checked)
			{
				Ext.getCmp('vacFormCriterion').show();
				Ext.getCmp('vacGroupOrgJob2Lpu').hide();
			}
		else
		{
			Ext.getCmp('vacFormCriterion').hide();
			Ext.getCmp('vacGroupOrgJob2Lpu').show();
		}
		Ext.getCmp('ammStartVacFormPlan').syncShadow();//перерисовка тени под изменившееся окно
			   
	}          

});

