/**
* swDokNakEditWindow - окно редактирования/добавления документа ввода остатков.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      
* @comment      Префикс для id компонентов dned (DokNakEditForm)
*               tabIndex (firstTabIndex): 15500+1 .. 15600
*
*
* @input data: action - действие (add, edit, view)
*              DocumentUc_id - документа
*/

sw.Promed.swDokNakEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	//autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	maximizable: false,
	split: true,
	width: 700,
	height: 500,
	layout: 'form',
	firstTabIndex: 15500,
	id: 'DokNakEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.callback(this.owner, -1);
		},
		beforeshow: function()
		{
			// Никого не жалко, никого!!!
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	saveWaybill: function(td)
	{
		var form = this;
		var DocumentUc_id = form.findById('dnedDocumentUc_id').getValue();
		var DrugFinance_id = form.findById('dnedDrugFinance_id').getValue();
		if (td == 'take') 
		{
			//var txt = 'Принять приходную накладную?';
			getWnd('swSelectOtdelWindow').show({
				DocumentUc_id:DocumentUc_id,
				DrugFinance_id:DrugFinance_id,
				callback: function() 
				{
					this.callback(this.owner, 0);
				}.createDelegate(this),
				onHide: function() 
				{
					Ext.getCmp('dnedDenyButton').focus();
				}
			});
		}
		else 
		{
			var txt = lang['otklonit_prihodnuyu_nakladnuyu'];
			sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: txt,
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						Ext.Ajax.request(
						{
							url: '/?c=Farmacy&m=save&method=DokNak',
							params: 
							{	
								DocumentUc_id: DocumentUc_id,
								action: td
							},
							callback: function(options, success, response) 
							{
								if (success)
								{
									form.callback(form.owner, DocumentUc_id);
								}
							}
						});
					}
				}
			});
		}
		
	},

	/*enableEdit: function(enable) 
	{
		var form = this;
		if (enable) 
		{
			form.findById('dnedDocumentUc_Num').enable();
			form.findById('dnedDocumentUc_didDate').enable();
			form.DocumentUcStrPanel.setReadOnly(false);
			form.buttons[0].enable();
		}
		else 
		{
			form.findById('dnedDocumentUc_Num').disable();
			form.findById('dnedDocumentUc_didDate').disable();
			form.DocumentUcStrPanel.setReadOnly(true);
			form.buttons[0].disable();
		}
	},*/
	loadContragent: function(comboName) 
	{
		var combo = this.findById(comboName);
		var value = combo.getValue();
		if (value)
		{
			combo.getStore().load(
			{
				callback: function() 
				{
					combo.setValue(value);
					//combo.fireEvent('change', combo);
				}
			});
		}
	},
	show: function() 
	{
		sw.Promed.swDokNakEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		if ((!arguments[0]) || (!arguments[0].DocumentUc_id)) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		form.focus();
		form.findById('DokNakEditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].DocumentUc_id) 
			form.DocumentUc_id = arguments[0].DocumentUc_id;
		else 
			form.DocumentUc_id = null;
			
		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			form.action = arguments[0].action;
		}
		else 
		{
			form.action = "edit";
		}
		form.findById('DokNakEditForm').getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
			form.action = 'view';
		switch (form.action) 
		{
			case 'edit': case 'view':
				form.setTitle(lang['prihodnaya_nakladnaya']);
				break;
		}
		form.DocumentUcStrPanel.removeAll(true);
		
		form.findById('DokNakEditForm').getForm().load(
		{
			params: 
			{
				DocumentUc_id: form.DocumentUc_id
			},
			failure: function() 
			{
				loadMask.hide();
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function() 
					{
						form.hide();
					},
					icon: Ext.Msg.ERROR,
					msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
					title: lang['oshibka']
				});
			},
			success: function() 
			{
				loadMask.hide();
				if (form.findById('dnedDrugDocumentStatus_id').getValue()!=1 || form.action == 'view')
				{
					Ext.getCmp('dnedTakeButton').disable();
					Ext.getCmp('dnedDenyButton').disable();
					Ext.getCmp('dnedCancelButton').focus();
				}
				else 
				{
					Ext.getCmp('dnedTakeButton').enable();
					Ext.getCmp('dnedDenyButton').enable();
					Ext.getCmp('dnedTakeButton').focus();
				}
				form.setTitle('Приходная накладная / <span style="color:red;">'+form.findById('dnedDrugDocumentStatus_Name').getValue()+'</span>');
				form.loadContragent('dnedContragent_sid');
				form.loadContragent('dnedContragent_tid');
				form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:form.findById('dnedDocumentUc_id').getValue()}, globalFilters:{DocumentUc_id:form.findById('dnedDocumentUc_id').getValue()}, noFocusOnLoad:true});
				form.DocumentUcStrPanel.setReadOnly(true);
			},
			url: '/?c=Farmacy&m=edit&method=DokNak'
		});
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		/*
		this.MainRecordAdd = function()
		{
			var tf = Ext.getCmp('DokNakEditWindow');
			if (tf.doSave())
			{
				tf.submit('add',1);
			}
			return false;
		}
		this.MainRecordEdit = function()
		{
			var tf = Ext.getCmp('DokNakEditWindow');
			if (tf.doSave())
			{
				tf.submit('edit',1);
			}
			return false;
		}
		
		this.AddRecordMol = function ()
		{
			var tf = Ext.getCmp('DokNakEditWindow');
			if (tf.doSave())
			{
				tf.submit('add',1);
			}
			return false;
		}
		*/
		
		this.DocumentUcStrPanel = new sw.Promed.ViewFrame(
		{
			title:lang['medikamentyi'],
			id: 'DocumentUcStrGrid',
			border: true,
			region: 'center',
			//height: 203,
			object: 'DocumentUcStr',
			editformclassname: '',
			dataUrl: '/?c=Farmacy&m=loadDocumentUcStrView',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'DocumentUcStr_id', type: 'int', header: 'ID', key: true},
				{name: 'DocumentUc_id', hidden: true, isparams: true},
				{name: 'DocumentUcStr_oid', hidden: true, isparams: true},
				{name: 'DocumentUcStr_NZU', width: 60, header: lang['nzu']}, 
				{name: 'Drug_Name', id: 'autoexpand', header: lang['naimenovanie']},
				{name: 'Drug_Code', header: lang['kod_ges'], width: 100},
				{name: 'DocumentUcStr_Count', width: 80, header: lang['kol-vo'], type: 'float'}, // +lang['ed_uch']+
				{name: 'DocumentUcStr_Price', width: 120, header: lang['tsena_opt_bez_nds'], type: 'money', align: 'right'},
				{name: 'DocumentUcStr_Sum', width: 120, header: lang['summa_opt_bez_nds'], type: 'money', align: 'right'},
				//{name: 'DocumentUcStr_SumNds', width: 110, header: 'НДС (опт)', type: 'money', align: 'right'},
				{name: 'DocumentUcStr_PriceR', width: 120, header: lang['tsena_rozn_s_nds'], type: 'money', align: 'right'},
				{name: 'DocumentUcStr_SumR', width: 120, header: lang['summa_rozn_s_nds'], type: 'money', align: 'right'},
				//{name: 'DocumentUcStr_SumNdsR', width: 110, header: 'НДС (розница)', type: 'money', align: 'right'},
				{name: 'DocumentUcStr_Nds', width: 70, header: lang['nds_%'], type: 'string', align: 'left'},
				{name: 'DocumentUcStr_Ser', width: 110, header: lang['seriya']},
				{name: 'DocumentUcStr_godnDate', width: 110, header: lang['srok_godnosti'], type: 'date'},
				{name: 'DrugProducer_Code', width: 110, header: lang['kod_proizv_ges'], type: 'int'},
				{name: 'DrugProducer_Name', width: 130, header: lang['proizv_tovara']},
				{name: 'DrugProducer_Country', width: 130, header: lang['strana']},
				{name: 'DocumentUcStr_CertNum', width: 130, header: lang['№_sert']},
				{name: 'DocumentUcStr_CertDate', type: 'date', width: 70, header: lang['data_sert']},
				{name: 'DocumentUcStr_CertGodnDate', type: 'date', width: 70, header: lang['data_godn_sert']},
				{name: 'DocumentUcStr_CertOrg', width: 200, header: lang['org_vyidavshaya_sertifikat']},
				{name: 'DocumentUcStr_Decl', width: 200, header: lang['dannyie_po_tamojennoy_deklaratsii']},
				{name: 'DocumentUcStr_Barcod', width: 200, header: lang['shtrih-kod']},
				{name: 'DocumentUcStr_RegDate', type: 'date', width: 70, header: lang['data_reestra_zareg-nyih_tsen_dlya_jv']},
				{name: 'DocumentUcStr_RegPrice', type: 'money', align: 'right', width: 70, header: lang['zareg-naya_dlya_jv']},	
				{name: 'DocumentUcStr_CertNM', type: 'int', width: 70, header: lang['reg_nomer_v_m_lab']},
				{name: 'DocumentUcStr_CertDM', type: 'date', width: 70, header: lang['data_registratsii_v_m_lab']},
				{name: 'DocumentUcStr_NTU', type: 'int', width: 70, header: lang['unikalnyiy_nomer_tovara_v_sisteme']}
				
			],
			actions:
			[
				//{name:'action_add', handler: function() {this.AddRecordMol();}.createDelegate(this), func: form.MainRecordAdd},
				//{name:'action_edit', func: form.MainRecordEdit},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true}
			],
			onLoadData: function()
			{
				var win = Ext.getCmp('DokNakEditForm');
				
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('DokNakEditForm');
			},
			//focusPrev: {name:'dnedDocumentUc_Name',type:'field'},
			focusOn: {name:'dnedTakeButton',type:'button'}
		});
		
		this.DokNakEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DokNakEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: 
			[
			{
				id: 'dnedDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			},
			{
				id: 'dnedDrugDocumentStatus_id',
				name: 'DrugDocumentStatus_id',
				value: null,
				xtype: 'hidden'
			},
			{
				id: 'dnedDrugDocumentStatus_Name',
				name: 'DrugDocumentStatus_Name',
				value: null,
				xtype: 'hidden'
			},
			{
				tabIndex: form.firstTabIndex + 1,
				xtype: 'textfield',
				disabled: true,
				fieldLabel : lang['nomer_dokumenta'],
				name: 'DocumentUc_Num',
				id: 'dnedDocumentUc_Num',
				allowBlank:false
			},
			{
				fieldLabel : lang['data_dokumenta'],
				tabIndex: form.firstTabIndex + 2,
				disabled: true,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_didDate',
				id: 'dnedDocumentUc_didDate'
			},
			{
				//anchor: '100%',
				width:450,
				fieldLabel: lang['postavschik'],
				disabled: true,
				xtype: 'swcontragentcombo',
				tabIndex: form.firstTabIndex + 3,
				id: 'dnedContragent_sid',
				name: 'Contragent_sid',
				hiddenName:'Contragent_sid'
			},
			{
				//anchor: '100%',
				width:450,
				fieldLabel: lang['poluchatel'],
				disabled: true,
				xtype: 'swcontragentcombo',
				tabIndex: form.firstTabIndex + 4,
				id: 'dnedContragent_tid',
				name: 'Contragent_tid',
				hiddenName:'Contragent_tid'
			},
			{
				fieldLabel: lang['otdel'],
				width:450,
				id : 'dnedDrugFinance_id',
				name: 'DrugFinance_id',
				disabled: true,
				hiddenName: 'DrugFinance_id',
				tabIndex : form.firstTabIndex + 5,
				xtype: 'swdrugfinancecombo'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'DocumentUc_id' },
				{ name: 'DocumentUc_Num' },
				{ name: 'DocumentUc_didDate' },
				{ name: 'Contragent_sid' },
				{ name: 'Contragent_tid' },
				{ name: 'DrugFinance_id' },
				{ name: 'DrugDocumentStatus_id' },
				{ name: 'DrugDocumentStatus_Name' }
			]),
			url: '/?c=Farmacy&m=save&method=DokNak'
		});
		Ext.apply(this, 
		{
			border: false,
			xtype: 'panel',
			region: 'center',
			layout:'border',
			buttons: 
			[{
				id: 'dnedTakeButton',
				disabled: true,
				handler: function() 
				{
					this.ownerCt.saveWaybill('take');
				},
				iconCls: 'ok16',
				text: lang['prinyat']
			}, 
			{
				id: 'dnedDenyButton',
				disabled: true,
				handler: function() 
				{
					this.ownerCt.saveWaybill('deny');
				},
				iconCls: 'delete16',
				text: lang['otklonit']
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				id: 'dnedCancelButton'
			}],
			//items: [form.DokNakEditForm, this.DocumentUcStrPanel]
			items:
			[
				form.DokNakEditForm,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items: 
					[
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [form.DocumentUcStrPanel]
						}
					]
				}
			]
		});
		sw.Promed.swDokNakEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});