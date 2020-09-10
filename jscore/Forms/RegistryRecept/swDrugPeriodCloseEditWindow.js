/**
* amm_JournalViewWindow - окно hредактирования Закрытия отчетного периода
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Нигматуллин Тагир
* @version      июнь 2016

*/

sw.Promed.swDrugPeriodCloseEditWindow =  Ext.extend(sw.Promed.BaseForm, {
	title: "Закрытие отчетного периода",
	text: "Закрытие отчетного периода",
	id: 'swDrugPeriodCloseEditWindow',
	border: false,
	codeRefresh: true,
	width: 500,
	height: 400,
	maximizable: false,
	maximized: false,
	modal:true,
	callback:Ext.emptyFn,
	//layout:'fit',
	closeAction: 'hide',
	onHide: Ext.emptyFn,
	
    initBorderDatePeriod:  function () {
	 // Обработка даты открытия/закрытия
	    var $d = new Date;
	    var $d_min = new Date($d.getFullYear(), $d.getMonth(), $d.getDate());
	    Ext.getCmp('DrugPeriodOpen_Date').setMaxValue ($d);
	     var Open_dt = Ext.getCmp('DrugPeriodOpen_Date').getValue();
	    var $d_min = new Date(Open_dt.getFullYear(), Open_dt.getMonth(), Open_dt.getDate());
	    //` Увеличиваем дату на 1 день
	    $d_min.setDate($d_min.getDate() + 1);
	    $d = new Date($d.getFullYear(), $d.getMonth(), 1);  

	    Ext.getCmp('DrugPeriodClose_Date').setMinValue ($d_min);
    },
    saveRecord: function () {
		var $formParams = Ext.getCmp('swDrugPeriodCloseEditWindow').formParams;
		console.log($formParams);
		//return false;

		
		var params = {};
		params.DrugPeriodClose_DT = Ext.getCmp('DrugPeriodClose_Date').value;
		params.DrugPeriodOpen_DT = Ext.getCmp('DrugPeriodOpen_Date').value;
		/*
		if ($formParams.action == 'add') {	    
		}
		else 
		*/
		if ($formParams.action == 'edit') {
		    params.DrugPeriodClose_id = $formParams.DrugPeriodClose_id;
		    //params.DrugPeriodOpen_DT = Ext.getCmp('DrugPeriodOpen_Date').value;
		    //params.Org_id = $formParams.Org_id;
		    //params.DrugPeriodClose_Sign = Ext.getCmp('DrugPeriodClose_CloseTypeCombo').getValue();
		}
		
		
		
		Ext.Ajax.request({
			url: '/?c=RegistryRecept&m=saveDrugPeriodClose',
			method: 'POST',
			params: params,
			success: function(response, opts) {
				if (this.formParams.parent_id == 'swDrugPeriodCloseViewWindow') {
					var $params = {};
					Ext.getCmp(this.formParams.parent_id).fireEvent('success', this.formParams.parent_id, $params);
				}
				this.callback();
				Ext.getCmp('swDrugPeriodCloseEditWindow').hide();
			}.createDelegate(this)
			
		});
		
    },
     initComponent: function() {
		form = this;
		Ext.apply(this, {
			frame: true,           
			labelWidth : 150,  
			bodyBorder : true,
			layout : "form",
			cls: 'tg-label',
			autoHeight: true,

			items : [
			
			new Ext.form.FormPanel({
				frame: true,
				id: 'DrugPeriodClose_FormPlanPanel', 
                                //layout: 'form',
                                labelWidth: 150,
				items : [
				{
		   
					height : 10,
					border : false,
					cls: 'tg-label'
				}, 
				{layout: 'form',
				    labelWidth: 150,
				     items: [
					{
					    autoLoad: false,
					    fieldLabel: lang['naimenovanie_apteki'],
					    id: 'DrugPeriodClose_Apteka',
					    width: 300,
					    disabled: true,
					    tabIndex: TABINDEX_DRUGPERIODCLOSE + 1,
					    xtype: 'textfield'
					}]
                                },
				{
                                    fieldLabel: 'Дата открытия периода',
                                    style: 'padding: 0px 5px;',
                                    tabIndex: TABINDEX_DRUGPERIODCLOSE + 2,
                                    allowBlank: false,
                                    labelWidth: 130,
                                    width: 200,
                                    xtype: 'swdatefield',
                                    format: 'd.m.Y',
                                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				    id: 'DrugPeriodOpen_Date',
				    listeners: {
					
					'change': function(c) {
					    form.initBorderDatePeriod();
					}
				    }
                                },
				{
                                    fieldLabel: 'Дата закрытия периода',
                                    style: 'padding: 0px 5px;',
                                    tabIndex: TABINDEX_DRUGPERIODCLOSE + 3,
                                    allowBlank: false,
                                    labelWidth: 130,
                                    width: 200,
                                    xtype: 'swdatefield',
                                    format: 'd.m.Y',
                                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                                    //name: 'vacMantuDateImpl',
                                    id: 'DrugPeriodClose_Date'

                                },
                                /*
				{layout: 'form',
					labelWidth: 150,
					 items: [
					      {
						xtype: 'amm_DrugPeriodCloseTypeCombo',
						fieldLabel: 'Статус периода', 
						tabIndex: TABINDEX_DRUGPERIODCLOSE + 4,
						id: 'DrugPeriodClose_CloseTypeCombo',
						width: 200
					    }
					 ]
				    },
				    */
				{
					height : 20,
					border : false
				}
			    ]
			})
			//             }
			],
			buttons: [
                            {
                            text : lang['sohranit'],
                            id: 'DrugPeriodClose_Save',
                            conCls: 'save16',
			    tabIndex: TABINDEX_DRUGPERIODCLOSE + 4,
                            handler: function(b) { 
				
                            if (!Ext.getCmp('DrugPeriodClose_FormPlanPanel').getForm().isValid())  {
                                sw.swMsg.show( {
                                    buttons: Ext.Msg.OK,
                                    icon: Ext.Msg.WARNING,
                                    msg: ERR_INVFIELDS_MSG,
                                    title: ERR_INVFIELDS_TIT
                                });
                                return false;
                            }
			   
                            this.saveRecord();
                        }.createDelegate(this)                     

			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_DRUGPERIODCLOSE + 5),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'DrugPeriodClose_CancelButton',
				text: '<u>З</u>акрыть',
				tabIndex: TABINDEX_DRUGPERIODCLOSE + 6
				/*,
				onTabAction: function () {
					if (this.formParams.action == 'add')
					    Ext.getCmp('DrugPeriodClose_Date').focus();
					else
					     Ext.getCmp('DrugPeriodClose_CloseTypeCombo').focus(); 
				}.createDelegate(this),
				*/
			} ]
		}
		);   
		          
         sw.Promed.swDrugPeriodCloseEditWindow.superclass.initComponent.apply(this, arguments);
     },

	show: function(record) {
		sw.Promed.swDrugPeriodCloseEditWindow.superclass.show.apply(this, arguments);
		
		this.formParams = record;
		form = this;
		
		console.log('record ='); console.log(record);
		if (record.close_DT == null)
		    record.close_DT = new Date(2016, 0, 1); 
		if (record.open_DT == null)
		    record.open_DT = new Date(2016, 0, 1); 
		Ext.getCmp('DrugPeriodOpen_Date').setValue(record.open_DT);
		Ext.getCmp('DrugPeriodClose_Date').setValue(record.close_DT);
		if (record.action == 'add') {
		    Ext.getCmp('DrugPeriodOpen_Date').focus(true, 50);
		    Ext.getCmp('swDrugPeriodCloseEditWindow').setTitle(Ext.getCmp('swDrugPeriodCloseEditWindow').text);// + ': Добавление');
			
		    // Обработка даты открытия/закрытия
//		    Ext.getCmp('DrugPeriodOpen_Date').setValue(record.close_DT);
//		    Ext.getCmp('DrugPeriodOpen_Date').setValue(record.close_DT);
//		    var $d = new Date;
//		    $d = new Date($d.getFullYear(), $d.getMonth(), 1);  
//		    Ext.getCmp('DrugPeriodClose_Date').setValue($d);
		    /*
		    Ext.getCmp('DrugPeriodClose_Date').setValue(new Date);
		    
		    var $d = new Date;
		    var $d_min = new Date(record.close_DT.getFullYear(), record.close_DT.getMonth(), record.close_DT.getDate());
		    //` Увеличиваем дату на 1 день
		    $d_min.setDate($d_min.getDate() + 1);
		    
		  
		    Ext.getCmp('DrugPeriodClose_Date').setMinValue ($d_min);
		    Ext.getCmp('DrugPeriodClose_Date').setMaxValue (new Date);
		    Ext.getCmp('DrugPeriodClose_Date').setValue($d);
		    //Ext.getCmp('DrugPeriodClose_CloseTypeCombo').setValue(2);
		    */
		    form.initBorderDatePeriod();
		    
		    // Скрываем / раскрываем объекты
		    //Ext.getCmp('DrugPeriodClose_Date').enable();
		    //Ext.getCmp('DrugPeriodClose_CloseTypeCombo').disable();
		    Ext.getCmp('DrugPeriodClose_Apteka').ownerCt.hide();
		} 
		else  if (record.action == 'edit') {
		    //Ext.getCmp('DrugPeriodClose_CloseTypeCombo').focus(true, 50); 
		    Ext.getCmp('swDrugPeriodCloseEditWindow').setTitle(Ext.getCmp('swDrugPeriodCloseEditWindow').text + ': Редактирование');
//		    Ext.getCmp('DrugPeriodOpen_Date').setValue(record.open_DT);
//		    Ext.getCmp('DrugPeriodClose_Date').setValue(record.close_DT);
		    Ext.getCmp('DrugPeriodClose_Apteka').setValue(record.DrugPeriodClose_Apteka);
		    //Ext.getCmp('DrugPeriodClose_CloseTypeCombo').setValue(record.DrugPeriodClose_Sign);
		    
		    // Скрываем / раскрываем объекты
		    //Ext.getCmp('DrugPeriodClose_Date').disable();
		    //Ext.getCmp('DrugPeriodClose_CloseTypeCombo').enable();
		    Ext.getCmp('DrugPeriodClose_Apteka').ownerCt.show();
		};
		Ext.getCmp('swDrugPeriodCloseEditWindow').syncShadow();//перерисовка тени под изменившееся окно
	    }

});

