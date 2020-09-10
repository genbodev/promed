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
* @author       Марков Андрей
* @version      июль 2010
* @comment      Префикс для id компонентов regv (amm_JournalViewWindow)
*/

sw.Promed.swEvnReceptWrongEditWindow =  Ext.extend(sw.Promed.BaseForm, {
	title: "Неправильно выписанный рецепт",
	text: "Неправильно выписанный рецепт",
	id: 'swEvnReceptWrongEditWindow',
	border: false,
	codeRefresh: true,
	width: 700,
	height: 400,
	maximizable: false,
	maximized: false,
	modal:true,
	callback:Ext.emptyFn,
	//layout:'fit',
	closeAction: 'hide',
	onHide: Ext.emptyFn,
   
    saveRecord: function ($action) {
		var $formParams = Ext.getCmp('swEvnReceptWrongEditWindow').formParams;
		//  || !$formParams.EvnRecept_id
		if ($formParams.action == 'view') {
			alert('Запись невозможна!');
			return false;
		}

		var params = {};

		//alert(form.formParams.EvnRecept_id);

		if ($formParams.action == 'edit') {
			params.ReceptWrong_id = $formParams.ReceptWrong_id;
		}
		//alert($formParams.EvnRecept_id);
		//return false;
		params.ReceptWrong_id = $formParams.ReceptWrong_id;
		params.EvnRecept_id = $formParams.EvnRecept_id;
		params.OrgFarmacy_id = getGlobalOptions().OrgFarmacy_id;
		params.Org_id = getGlobalOptions().org_id;
		params.ReceptWrong_decr = Ext.getCmp('ReceptWrong_Reason').getValue();

		Ext.Ajax.request({
			url: '/?c=Drug&m=saveReceptWrong',
			method: 'POST',
			params: params,
			success: function(response, opts) {
				if (response.responseText.length > 0) {
					var result = Ext.util.JSON.decode(response.responseText);
					//sw.Promed.vac.utils.consoleLog(result.rows[0]);
				}
				/*
				if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
					Ext.getCmp(Ext.getCmp('amm_SprNacCalEditWindow').formParams.parent_id).fireEvent('success', 'amm_SprNacCalEditWindow', {});
				}
				*/
				if (this.formParams.parent_id == 'swWorkPlaceDistributionPointWindow') {
					var $params = {};
					$params.Delay_info = 'Отказ ' + getGlobalOptions().OrgFarmacy_Nick + ' (' + params.ReceptWrong_decr + ')';
					//alert($params.Delay_info);
					Ext.getCmp(this.formParams.parent_id).fireEvent('success', this.formParams.parent_id, $params);
				}
				this.callback();
				Ext.getCmp('swEvnReceptWrongEditWindow').hide();
			}.createDelegate(this)
		});
    },
     initComponent: function() {
         
                /*
                * хранилище для доп сведений
                */

               this.formStore = new Ext.data.JsonStore({
                   fields: ['ReceptWrong_id', 'EvnRecept_id', 'Org_id', 'ReceptWrong_Decr'],
                   url: '/?c=Drug&m=loadReceptWrongInfo',
                   key: 'EvnRecept_id',
                   root: 'data'
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
				id: 'ReceptWrong_FormPlanPanel', 
                                //layout: 'form',
                                labelWidth: 150,
				items : [
				{
		   
					height : 10,
					border : false,
					cls: 'tg-label'
				}, 
                                {
                                    autoLoad: false,
                                    fieldLabel: lang['naimenovanie_apteki'],
                                    
                                    id: 'ReceptWrong_Apteka',
                                    width: 300,
                                    //tabIndex: TABINDEX_MANTUPURPFRM + 22,
                                    xtype: 'textfield'
                                },
                                 {
                                    autoLoad: false,
                                    fieldLabel: lang['prichina_otkaza'],
                                    id: 'ReceptWrong_Reason',
                                    allowBlank: false,
                                    width: 500,
                                    //tabIndex: TABINDEX_MANTUPURPFRM + 22,
                                    xtype: 'textfield'
                                },
				

				{
					height : 20,
					border : false
				//            style: 'padding: 0px;'
				}, 
			 
				{
					height : 20,
					border : false
				//            style: 'padding: 0px;'
				}
		   //     /*
				// *ВРЕММЕНО УБРАЛ
				/*
                         ,
		
				{
					id: 'vacFormCriterion', 
					autoHeight: true,
					autoScroll: true,
					//                style: 'padding: 0px;',
					title: lang['kriterii_formirovaniya'],
					//                style: 'background-color: #fff;',
//                    height: 100,
					labelWidth: 90,
					xtype: 'fieldset',
					items: [
					this.myRadioGroupPatient
					]
				},
				*/
//                 {
//                    height : 20,
//                    border : false
//                },
				/* {
					id: 'vacGroupOrgJob2Lpu', 
					autoHeight: true,
					autoScroll: true,
					//                style: 'padding: 0px;',
					title: lang['obslujivaemyie_organizatsii'],
					//                style: 'background-color: #fff;',
//                    height: 100,
//                    width: 380,
					labelWidth: 90,
					xtype: 'fieldset'
					, items: [
					{
					//       Org_id, Org_Nick
					id: 'Vac_OrgJob2LpuCombo',
//					autoLoad: true,
					editable: false,
//					fieldLabel: 'Вакцина',
//                                        hidden
					valueField: 'Org_id',
					displayField: 'Org_Nick',
					tabIndex: TABINDEX_STARTVACFORMPLAN + 10,
					//                                hiddenName: 'Vaccine_id',
					width: 230,
					allowBlank: false,
					xtype: 'amm_VacOrgJob2LpuCombo'
										, listeners: {
											'select': function(combo)  {
//                                              paramsMode = new Object();
											  paramsMode.Org_id = combo.getValue();
//                                               alert ('paramsMode.Org_id = ' + paramsMode.Org_id); //paramsMode.Org_id);

											}}  //  !!!
				}
					]
				  

				}
				*/
			 //   */
				]
			})
			//             }
			],
			buttons: [
                            {
                            text : lang['sohranit'],
                            id: 'ReceptWrong_Save',
                            conCls: 'save16',
                            handler: function(b) { 
                            if (!Ext.getCmp('ReceptWrong_Reason').isValid())  {
                                sw.swMsg.show( {
                                    buttons: Ext.Msg.OK,
                                    fn: function() {
                                            //wnd.form.getFirstInvalidEl().focus(true);
                                    },
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
			HelpButton(this, TABINDEX_STARTVACFORMPLAN + 7),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EPLSIF_CancelButton',
//				onTabAction: function () {
//					Ext.getCmp('Date_Plan').focus();
//				}.createDelegate(this),
				//				onTabAction: function () {
				//+					Ext.getCmp('Date_Plan').focus();
				//+				}.createDelegate(this),

				text: '<u>З</u>акрыть',
				tabIndex: TABINDEX_STARTVACFORMPLAN + 8
			} ]
		}
		);   
		          
         sw.Promed.swEvnReceptWrongEditWindow.superclass.initComponent.apply(this, arguments);
     },

	show: function(record) {
		sw.Promed.swEvnReceptWrongEditWindow.superclass.show.apply(this, arguments);

		this.formParams = record;
		this.formParams.readOnly = false;
		this.callback = Ext.emptyFn;
		if (record.callback && typeof record.callback == 'function') {
			this.callback = record.callback;
		}

		Ext.getCmp('ReceptWrong_Reason').setValue('');
		if (record.action == 'view') {
			this.setTitle(this.text + ': Просмотр');
			this.formParams.readOnly = true;
		} else if (record.action == 'add') {
			this.setTitle(this.text + ': Добавление');
		} else if (record.action == 'edit') {
			this.setTitle(this.text + ': Редактирование');

			this.formStore.load({
				params: {
					EvnRecept_id: record.EvnRecept_id
				},
				callback: function() {
					var formStoreCount = this.formStore.getCount() > 0;
					if (formStoreCount) {
						var formStoreRecord = this.formStore.getAt(0);
						 var $formParams = Ext.getCmp('swEvnReceptWrongEditWindow').formParams;
						$formParams.ReceptWrong_Decr = formStoreRecord.get('ReceptWrong_Decr');
						$formParams.ReceptWrong_id = formStoreRecord.get('ReceptWrong_id');
						$formParams.OrgFarmacy_id = formStoreRecord.get('OrgFarmacy_id');

						$formParams.formStoreRecord = formStoreRecord;
						Ext.getCmp('ReceptWrong_Reason').setValue($formParams.ReceptWrong_Decr);
					}

				}.createDelegate(this)
			});
		}

		//Ext.getCmp('ReceptWrong_Apteka').value =
		Ext.getCmp('ReceptWrong_Apteka').setValue(getGlobalOptions().OrgFarmacy_Nick)
		Ext.getCmp('ReceptWrong_Apteka').setDisabled(true);
		Ext.getCmp('ReceptWrong_Save').setDisabled( this.formParams.readOnly);
		//Ext.getCmp('swEvnReceptWrongEditWindow').formParams;
	}

});

