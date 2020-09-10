/* 
 * Попытка реализации справочника МКФ + определители
 */
sw.Promed.ufaMkfDiagSearchTreeWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'ufaMkfDiagSearchTreeWindow',
	title: 'Оценка состояния по МКФ',
	width: 1000,
	height: 555,
	minWidth: 1000, //1500,
	minHeight: 555, //850,
	layout: 'fit',
	modal: true,
	onSelectDiag: Ext.emptyFn(),
	initComponent: function () {

		var ICFDate = new sw.Promed.SwDateField({
			id: 'ICFDate',
			//labelField: 'Дата проведения',
			fieldLabel: 'Дата проведения',
			labelWidth: 220,
			labelSeparator: ':',
			//disabled: true,
			//labelWidth: '50px',
			width: '500px',
			height: 30,
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999', false)
			],
			xtype: 'swdatefield',
			format: 'd.m.Y',
			value: new Date(),
			maxValue: getGlobalOptions().date,
			listeners: {
				'change': function () {
					//    alert('Время');
					var form = Ext.getCmp('ufa_personReabRegistryWindow');
					// Удаленная дата
					if (this.getValue() == '') {
						form.showMsg('Введите дату проведения оценки!');
						this.setValue(new Date());
						return;
					}

					if (this.getValue() > Ext.getCmp('ICFDate').maxValue)
					{
						form.showMsg('Недопустимо указывать дату позднее текущей!');
						this.setValue(new Date());
						return;
					}
					if (this.getValue() < Ext.getCmp('ICFDate').minValue)
					{
						form.showMsg('Недопустимо указывать дату меньше даты открытия этапа!');
						this.setValue(new Date());
						return;
					}
				},
				'blur': function () {}
			}
		});

		this.MKFLeftPanel = new Ext.form.FormPanel({
			region: 'west',
			layout: 'form',
			// collapsible: true,
			border: false,
			id: 'ICFPanel',
			style: 'margin: 6px 0px 0px 6px; background: #DFE8F6',
			width: 610,
//			height: 130,
			autoHeight: true,
			autoScroll: true,
			items: [
				{
					xtype: 'panel',
					layout: 'form',
					border: true,
					frame: true, //Отражение панели
					width: 610,
					items: [
						ICFDate,
						{
							xtype: 'panel',
							layout: 'column',
							id: 'ICFSearch',
							border: false,
							items: [
								{
									layout: 'form',
									border: false,
									style: 'position:relative;left:2px;',
									labelWidth: 30,
									width: 90,
									labelAlign: 'left',
									items: [
										new Ext.form.TextField({
											allowBlank: true,
											disabled: false,
											labelStyle: 'font-style:normal;font-size:1.1em;color:blue;text-align: center;font-weight: bold',
											fieldLabel: 'Код',
											id: 'ICFFilterCode',
											maskRe: /[dseb\d]/,
											//maskRe: new RegExp("^([dseb])(\\d{1,4}$)"),
											//regex:/^\w{1}(\d{1,4})?$/,
											maxLength: 5,
											minLength: 2,
											//enableKeyEvents: true,
											width: 50,
											listeners: {
												'blur': function (textf)
												{
													console.log('blur=', textf.getValue().trim());
													//var myREgExp = /^([d]{1})(\d{1,4}$)/;
													//var a = /^([d]{1})(\d{1,4}$)/.test(textf.getValue().trim());
													var myREgExp = new RegExp("^(" + Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen + ")(\\d{1,4}$)");
													if (textf.getValue().trim() != "" && myREgExp.test(textf.getValue().trim()) == false)
													{
														sw.swMsg.alert(lang['soobschenie'], 'Неверно указан код домена для поиска!');
													}
													var a = myREgExp.test(textf.getValue().trim());
													//console.log('a ==',a);
												}.createDelegate(this),

												focus: function (textf)
												{
													if (textf.getValue().trim().length == 0)
													{
														textf.setValue(Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen);
													}

												}.createDelegate(this)
											}
										})
									]
								},
								{
									layout: 'form',
									border: false,
									style: 'position:relative;left:5px;',
									//bodyStyle : 'font-style:normal;font-size:1.4em;color:blue;text-align: center;font-weight: bold',
									labelWidth: 120,
									width: 330,
									labelAlign: 'left',
									items: [
										new Ext.form.TextField({
											allowBlank: true,
											disabled: false,
											//style: 'font-style:normal;font-size:1.4em;color:blue;text-align: center;font-width: bold',
											labelStyle: 'font-style:normal;font-size:1.1em;color:blue;text-align: center;font-weight: bold',
											fieldLabel: 'Наименование',
											id: 'ICFFilterName',
											width: 200
										})
									]
								},
								{
									layout: 'form',
									border: false,
									style: 'position:relative;left:10px;',
									items: [
										{
											xtype: 'button',
											iconCls: 'search16',
											text: 'Найти',
											width: 50,
											//margin: '0 10',
											style: 'border-style:solid;',
											handler: function ()
											{
												//Запуск контектстного поиска + контроль
												Ext.getCmp('ufaMkfDiagSearchTreeWindow').doCodeFilterTree();
											}
										}
									]
								},
								{
									layout: 'form',
									border: false,
									style: 'position:relative;left:20px;',
									items: [
										{
											xtype: 'button',
											iconCls: 'reset16',
											text: 'Сброс',
											width: 50,
											handler: function ()
											{
												if (Ext.getCmp('ICFFilterCode').getValue().trim() != "" || Ext.getCmp('ICFFilterName').getValue().trim() != "")
												{
													Ext.getCmp('ICFFilterCode').setValue("");
													Ext.getCmp('ICFFilterName').setValue("");
													var tree = Ext.getCmp('SprMkf');
													var nn = tree.getRootNode().childNodes.length;
													for (j = 0; j < nn; j++)
													{
														tree.getRootNode().childNodes[0].remove(true);
													}
													var loadMask = new Ext.LoadMask(Ext.getCmp('SprMkf').getEl(), {msg: lang['idet_zagruzka']});
													loadMask.show();
													tree.getLoader().baseParams = {
														ICF_pid: null, //node.attributes.ICF_id,
														ICF_code: Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen,
														ICF_code_filter: null,
														ICF_Name_filter: null
													}
													tree.getLoader().load(tree.getRootNode(),
															function ()
															{
																loadMask.hide();
															});
												}
												;
												Ext.getCmp('ufaMkfDiagSearchTreeWindow').doClear();
											}
										}
									]
								}
							]
						}
					]
				},

//				{
//					xtype: 'tbfill'
//				},

				{
					xtype: 'panel',
					layout: 'form',
					height: 250,
					frame: true,
					id: 'SprMkfEdit',
					layout: 'form',
					items: [
						{
							layout: 'form',
							border: false,
							height: 100
						},
						{
							layout: 'column',
							border: false,
							items: [
								new Ext.form.TextField({
									allowBlank: false,
									hideLabel: true,
									disabled: true,
									border: false,
									id: 'SprMk_Code',
									style: 'text-align:center;font-weight:bold;',
									width: 80,
									height: 40
								}),
								{
									xtype: 'textarea',
									hideLabel: true,
									//border: true,
									disabled: true,
									//style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 5px 2px 2px 5px; text-align: justify ;',
									style: 'border-top: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 5px 2px 2px 5px; vertical-align: middle;',
									id: 'SprMk_Name',
									width: 510,
									height: 40
								}
							]
						}
					]
				},
				new Ext.tree.TreePanel(
						{
							height: 250,
//							width: 'auto',
//							width: 1000,
							autoScroll: true,
							rootVisible: false,
							lastSelectedId: 0,
							border: false,
							cls: 'larger-text',
							displayField: 'text',
							id: 'SprMkf',
							root: {
								id: 'root',
								expanded: false
							},

							loader: new Ext.tree.TreeLoader({
								url: '/?c=Ufa_Reab_Register_User&m=getICFTreeData',
								clearOnLoad: false,
								baseParams: {
									ICF_pid: null,
									ICF_code: null,
									ICF_code_filter: null,
									ICF_Name_filter: null
									//node: 'root'
								}
							}),

							items: [	],
							listeners: {
								'expandnode': function (node)
								{
									console.log('expandnode=', node);
									//var tree = Ext.getCmp('SprMkf');
									if (typeof Ext.getCmp('ufaMkfDiagSearchTreeWindow').Inparams == 'undefined')
									{
										//console.log('xxxxxx=',node);
										if (node.attributes.id != 'root')
										{
											var filterName = null;
											if (Ext.getCmp('ICFFilterName').getValue().trim().substr(0, 1) != "")
											{
												filterName = Ext.getCmp('ICFFilterName').getValue().trim();
											}
											Ext.getCmp('SprMkf').getLoader().baseParams = {
												ICF_pid: node.attributes.ICF_id,
												ICF_code: Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen,
												ICF_code_filter: null,
												ICF_Name_filter: filterName
											}

										};
									}
									else
									{
										var form = Ext.getCmp('ufaMkfDiagSearchTreeWindow');
										var gg = "Ext.getCmp('SprMkf').root.";
										var comand = "";

										if (form.search.length > 1)
										{
											comand = gg + form.search[0] + "expand(); "
											console.log('aaaaaaa=', node);
											form.search.shift();
											eval(comand);
										} else
										{
											console.log('Выделить запись=');
											comand = gg + form.search[0] + "fireEvent('click' , " + gg + form.search[0].substring(0, form.search[0].length - 1) + " );"
											console.log('comand=', comand);
											form.search.shift();
											eval(comand);
										}



//									for (jj = 0; jj < tt.length-1; jj++)
//									{
//										//comand += comand + gg + "findChild('ICF_Code','" + tt[jj] + "').expand(); ";
//										ss[jj] = comand + "findChild('ICF_Code','" + tt[jj] + "').";
//										comand =  ss[jj];
//									}
//									console.log('ss[jj]=',ss);
									};

								},
								'beforeload': function (node)
								{

								},
								'load': function (node) {
									console.log('загрузка=', Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen);
									//Ext.getCmp('ufaMkfDiagSearchTreeWindow').getEl().mask().hide();
									var arr = new Array();
									if (Ext.getCmp('ufaMkfDiagSearchTreeWindow').firstLoad)
									{
										switch (Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen)
										{
											case 'b':
												arr = ["s", "d", "e"];
												break;
											case 's':
												arr = ["b", "d", "e"];
												break;
											case 'd':
												arr = ["b", "s", "e"];
												break;
											case 'e':
												arr = ["b", "s", "d"];
												break;
											default :
												//sw.swMsg.alert(lang['soobschenie'], 'Косяк!!');
												arr = [];
												break;
										}

										var tree = Ext.getCmp('SprMkf');
										console.log('tree=', tree);
										for (j = 0; j < arr.length; j++)
										{
											tree.getRootNode().findChild('ICF_Code', arr[j]).remove(true);
										}
										;
									}

									Ext.getCmp('ufaMkfDiagSearchTreeWindow').firstLoad = false;
								},
								'click': function (node, e) {
									if (node.attributes.ICF_Description != null)
									{
										Ext.getCmp('ICFDescription').setValue(node.attributes.ICF_Description);
									} else
									{
										Ext.getCmp('ICFDescription').setValue('');
									}
									//Отработка определителей
									// console.log('node=',node.attributes.ICF_Code);
									if (node.attributes.ICF_Code.length >= 4 && node.attributes.ICF_Code.indexOf("-") == -1)
									{
										var rr = node.attributes.ICF_Code.substr(0, 1);
										//console.log('4444=',rr);
										switch (rr)
										{
											case 'b':
												//sw.swMsg.alert(lang['soobschenie'], 'Реализация определителя функции организма');
												Ext.getCmp('ICFSeverity_EvalRealiz_id').setTitle('Выраженность нарушения');
												Ext.getCmp('ICFSeverity_EvalRealiz_id').show();
												Ext.getCmp('ICFSeverity_TargetRealiz_id').show();
												break;
											case 's':
												Ext.getCmp('ICFSeverity_EvalRealiz_id').setTitle('Выраженность нарушения');
												Ext.getCmp('ICFSeverity_Nature_panel').show();
												Ext.getCmp('ICFSeverity_Localization_panel').show();
												Ext.getCmp('ICFSeverity_EvalRealiz_id').show();
												Ext.getCmp('ICFSeverity_TargetRealiz_id').show();
												break;
											case 'd':
												Ext.getCmp('ICFSeverity_EvalRealiz_id').setTitle('Оценка по реализации');
												Ext.getCmp('ICFSeverity_EvalRealiz_id').show();
												Ext.getCmp('ICFSeverity_TargetRealiz_id').show();
												Ext.getCmp('ICFSeverity_EvalCapasit_id').show();
												Ext.getCmp('ICFSeverity_TargetCapasit_id').show();

												break;
											case 'e':
												Ext.getCmp('ICFEnvFactors_eval').show();
												Ext.getCmp('ICFEnvFactors_Target').show();
												break;
											default :
												sw.swMsg.alert(lang['soobschenie'], 'Косяк!!');
												Ext.getCmp('ICFSeverity_EvalRealiz_id').hide();
												break;
										}
									} else
									{
										Ext.getCmp('ICFSeverity_EvalRealiz_id').hide();
										Ext.getCmp('ICFSeverity_TargetRealiz_id').hide();
										Ext.getCmp('ICFSeverity_EvalCapasit_id').hide();
										Ext.getCmp('ICFSeverity_TargetCapasit_id').hide();
										Ext.getCmp('ICFEnvFactors_Target').hide();
										Ext.getCmp('ICFEnvFactors_eval').hide();
									}

								}
							}
						}),
				{
					xtype: 'panel',
					layout: 'form',
					border: false,
					frame: true,
					width: 'auto',
					height: 160,
					autoScroll: true,
					items: [
						new Ext.form.Label({
							text: 'Комментарий ВОЗ',
							height: 10,
							style: 'font-style:italic;font-size:1.2em;color:blue; '
						}),
						{
							xtype: 'textarea',
							hideLabel: true,
							border: true,
							disabled: true,
							//style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 2px 0px 0px 0px; margin-top: 5px; margin-left: 5px;',
							style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff; padding: 5px 2px 2px 5px; text-align:justify;color: black;',
							id: 'ICFDescription',
							//height: 200,
							width: 580,
							height: 125

						}
					]
				}
			]
		});


		this.MKFRigthPanel = new Ext.form.FormPanel({
			layout: 'form',
			region: 'center',
			border: true,
			frame: true,
			//  autoScroll: true,
			// autoWidth: true,
			//  autoHeight: true,
			//id: 'scaleReabRightPan',
			height: 'auto',
			width: 'auto',
			style: 'margin: 6px 0px 0px 6px;',
			items: [
				{
					xtype: 'panel',
					layout: 'form',
					height: 350,
					items: [
						{
							layout: 'form',
							border: false,
							height: 5
						},

						new Ext.form.FieldSet(
								{
									border: true,
									autoHeight: true,
									hidden: true,
									//bodyStyle: 'color:blue;',
									width: 350,
									style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
									title: 'rrrr',
									id: 'ICFSeverity_EvalRealiz_id',
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 75,
											labelAlign: 'rigth',
											style: 'position:relative;  left:10px ',
											items: [
												{
													xtype: 'combo',
													allowBlank: false,
													fieldLabel: 'Нарушения',
													hiddenName: 'ICFSeverity_id',
													disabled: false,
													editable: false,
													id: 'ICFSeverity_EvalRealiz',
													style: 'text-align:center;',
													emptyText: 'Введите параметр',
													mode: 'local',
													listWidth: 'auto',
													width: 200,
													triggerAction: 'all',
													store: new Ext.data.SimpleStore({
														fields: [
															{name: 'ICF_id', type: 'int'},
															{name: 'ICFSeverity_id', type: 'int'},
															{name: 'Code', type: 'string'},
															{name: 'Name', type: 'string'}
														],
														data: []
													}),
													displayField: 'Name',
													valueField: 'ICFSeverity_id',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'&nbsp; «' + '{Code}»' + '  {Name} ' + '&nbsp;' +
													'</div></tpl>'
													/*
													 listeners: {
													 'select': function (combo, record, index) {
													 // alert('Сведения');
													 //Ext.getCmp('stageOff').setDisabled(false);
													 if(Ext.getCmp('choiceReabObjectWindow').inp == 2)
													 {
													 Ext.getCmp('stageOff').focus(true);
													 Ext.getCmp('stageOff').getStore().clearFilter();

													 if(record.data.StageType_id == 1 )
													 {
													 if(Ext.getCmp('stageOff').getValue().inlist(['6','8','9','10','1','3','4']) == false)
													 {
													 Ext.getCmp('stageOff').setValue('Введите параметр');
													 }
													 Ext.getCmp('stageOff').store.filterBy(function(rec) {
													 return (rec.get('OutCause_id').toString().inlist(['6','8','9','10','1','3','4']));
													 });
													 }
													 }
													 }
													 }
													 */
												}
											]
										},
										{
											layout: 'form',
											border: false,
											hidden: true,
											id: 'ICFSeverity_Nature_panel',
											items: [
												{
													layout: 'form',
													border: false,
													labelWidth: 75,
													labelAlign: 'rigth',
													style: 'position:relative;  left:10px ',
													items: [
														{
															xtype: 'combo',
															allowBlank: true,
															fieldLabel: 'Характер',
															hiddenName: 'ICFNature_id',
															disabled: false,
															editable: false,
															id: 'ICFSeverity_Nature',
															style: 'text-align:center;',
															mode: 'local',
															listWidth: 'auto',
															width: 200,
															triggerAction: 'all',
															store: new Ext.data.SimpleStore({
																fields: [
																	{name: 'ICF_id', type: 'int'},
																	{name: 'ICFNature_id', type: 'int'},
																	{name: 'Code', type: 'string'},
																	{name: 'Name', type: 'string'}
																],
																data: []
															}),
															displayField: 'Name',
															valueField: 'ICFNature_id',
															tpl: '<tpl for="."><div class="x-combo-list-item">' +
															'&nbsp; «' + '{Code}»' + '  {Name} ' + '&nbsp;' +
															'</div></tpl>'
														}
													]
												}
											]
										},
										{
											layout: 'form',
											border: false,
											hidden: true,
											id: 'ICFSeverity_Localization_panel',
											items: [
												{
													layout: 'form',
													border: false,
													labelWidth: 75,
													labelAlign: 'rigth',
													style: 'position:relative;  left:10px ',
													items: [
														{
															xtype: 'combo',
															allowBlank: false,
															fieldLabel: 'Локализация',
															hiddenName: 'ICFLocalization_id',
															disabled: false,
															editable: false,
															id: 'ICFSeverity_Localization',
															style: 'text-align:center;',
															mode: 'local',
															listWidth: 'auto',
															width: 200,
															triggerAction: 'all',
															store: new Ext.data.SimpleStore({
																fields: [
																	{name: 'ICF_id', type: 'int'},
																	{name: 'ICFLocalization_id', type: 'int'},
																	{name: 'Code', type: 'string'},
																	{name: 'Name', type: 'string'}
																],
																data: []
															}),
															displayField: 'Name',
															valueField: 'ICFLocalization_id',
															tpl: '<tpl for="."><div class="x-combo-list-item">' +
															'&nbsp; «' + '{Code}»' + '  {Name} ' + '&nbsp;' +
															'</div></tpl>'
														}
													]
												}
											]
										}
									]
								}),
						{
							layout: 'form',
							border: false,
							height: 5
						},

						new Ext.form.FieldSet(
								{
									border: true,
									autoHeight: true,
									hidden: true,
									//bodyStyle: 'color:blue;',
									width: 350,
									style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
									title: 'Цель реализации',
									id: 'ICFSeverity_TargetRealiz_id',
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 65,
											labelAlign: 'rigth',
											style: 'position:relative;  left:10px ',
											items: [
												{
													xtype: 'combo',
													allowBlank: false,
													fieldLabel: 'Нарушения',
													hiddenName: 'ICFSeverity_id',
													displayField: 'Name',
													valueField: 'ICFSeverity_id',
													disabled: false,
													id: 'ICFSeverity_TargetRealiz',
													style: 'text-align:center;',
													mode: 'local',
													editable: false,
													listWidth: 'auto',
													width: 200,
													triggerAction: 'all',
													store: new Ext.data.SimpleStore({
														//url: '?c=Ufa_Reab_Register_User&m=ICFSeverity',
														fields: [
															{name: 'ICF_id', type: 'int'},
															{name: 'ICFSeverity_id', type: 'int'},
															{name: 'Code', type: 'string'},
															{name: 'Name', type: 'string'}
														],
														data: []
													}),
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'&nbsp; «' + '{Code}»' + '  {Name} ' + '&nbsp;' +
													'</div></tpl>'
												}
											]
										}
									]
								}),
						{
							layout: 'form',
							border: false,
							height: 5
						},
						new Ext.form.FieldSet(
								{
									border: true,
									autoHeight: true,
									hidden: true,
									//bodyStyle: 'color:blue;',
									width: 350,
									style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
									title: 'Оценка по капаситету',
									id: 'ICFSeverity_EvalCapasit_id',
									items: [

										{
											layout: 'form',
											border: false,
											labelWidth: 65,
											labelAlign: 'rigth',
											style: 'position:relative;  left:10px ',
											items: [
												{
													xtype: 'combo',
													allowBlank: false,
													fieldLabel: 'Нарушения',
													hiddenName: 'ICFSeverity_id',
													disabled: false,
													emptyText: 'Введите параметр',
													editable: false,
													id: 'ICFSeverity_EvalCapasit',
													style: 'text-align:center;',
													mode: 'local',
													listWidth: 'auto',
													width: 200,
													triggerAction: 'all',
													store: new Ext.data.SimpleStore({
														fields: [
															{name: 'ICF_id', type: 'int'},
															{name: 'ICFSeverity_id', type: 'int'},
															{name: 'Code', type: 'string'},
															{name: 'Name', type: 'string'}
														],
														data: []
													}),
													displayField: 'Name',
													valueField: 'ICFSeverity_id',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'&nbsp; «' + '{Code}»' + '  {Name} ' + '&nbsp;' +
													'</div></tpl>'
												}
											]
										}
									]
								}),
						{
							layout: 'form',
							border: false,
							height: 5
						},
						new Ext.form.FieldSet(
								{
									border: true,
									autoHeight: true,
									hidden: true,
									//bodyStyle: 'color:blue;',
									width: 350,
									style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
									title: 'Цель по капаситету',
									id: 'ICFSeverity_TargetCapasit_id',
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 65,
											labelAlign: 'rigth',
											style: 'position:relative;  left:10px ',
											items: [
												{
													xtype: 'combo',
													allowBlank: false,
													fieldLabel: 'Нарушения',
													hiddenName: 'ICFSeverity_id',
													disabled: false,
													editable: false,
													id: 'ICFSeverity_TargetCapasit',
													style: 'text-align:center;',
													mode: 'local',
													listWidth: 'auto',
													width: 200,
													triggerAction: 'all',
													store: new Ext.data.SimpleStore({
														fields: [
															{name: 'ICF_id', type: 'int'},
															{name: 'ICFSeverity_id', type: 'int'},
															{name: 'Code', type: 'string'},
															{name: 'Name', type: 'string'}
														],
														data: []
													}),
													displayField: 'Name',
													valueField: 'ICFSeverity_id',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'&nbsp; «' + '{Code}»' + '  {Name} ' + '&nbsp;' +
													'</div></tpl>'
												}
											]
										}
									]
								}),

						new Ext.form.FieldSet(
								{
									border: true,
									autoHeight: true,
									hidden: true,
									width: 350,
									style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
									title: 'Степень выраженности',
									id: 'ICFEnvFactors_eval',
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 65,
											labelAlign: 'rigth',
											style: 'position:relative;  left:10px ',
											items: [
												{
													xtype: 'combo',
													allowBlank: false,
													fieldLabel: 'Оценка',
													hiddenName: 'ICFEnvFactors_id',
													disabled: false,
													emptyText: 'Введите параметр',
													editable: false,
													id: 'ICFEnvFactors_eval_id',
													style: 'text-align:center;',
													mode: 'local',
													listWidth: 'auto',
													width: 220,
													triggerAction: 'all',
													store: new Ext.data.SimpleStore({
														fields: [
															{name: 'ICF_id', type: 'int'},
															{name: 'ICFEnvFactors_id', type: 'int'},
															{name: 'Code', type: 'string'},
															{name: 'Name', type: 'string'}
														],
														data: []
													}),
													displayField: 'Name',
													valueField: 'ICFEnvFactors_id',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'&nbsp; «' + '{Code}»' + '  {Name} ' + '&nbsp;' +
													'</div></tpl>'
												}
											]
										}
									]
								}),
						{
							layout: 'form',
							border: false,
							height: 5
						},
						new Ext.form.FieldSet(
								{
									border: true,
									autoHeight: true,
									hidden: true,
									//bodyStyle: 'color:blue;',
									width: 350,
									style: 'padding: 5px 10px;font-color: #AA0000;color:blue;',
									title: 'Цель',
									id: 'ICFEnvFactors_Target',
									items: [

										{
											layout: 'form',
											border: false,
											labelWidth: 65,
											labelAlign: 'rigth',
											style: 'position:relative;  left:10px ',
											items: [
												{
													xtype: 'combo',
													allowBlank: false,
													fieldLabel: 'Оценка',
													hiddenName: 'ICFEnvFactors_id',
													disabled: false,
													editable: false,
													id: 'ICFEnvFactors_Target_id',
													style: 'text-align:center;',
													mode: 'local',
													listWidth: 'auto',
													width: 220,
													triggerAction: 'all',
													store: new Ext.data.SimpleStore({
														fields: [
															{name: 'ICF_id', type: 'int'},
															{name: 'ICFEnvFactors_id', type: 'int'},
															{name: 'Code', type: 'string'},
															{name: 'Name', type: 'string'}
														],
														data: []
													}),
													displayField: 'Name',
													valueField: 'ICFEnvFactors_id',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'&nbsp; «' + '{Code}»' + '  {Name} ' + '&nbsp;' +
													'</div></tpl>'
												}
											]
										}
									]
								}),
					]
				}
			]
		});

		Ext.apply(this,
				{
					items: [
						{
							xtype: 'panel',
							frame: false,
							split: true,
							layout: 'border',
							width: 1000,
							height: 500,
							items: [
								this.MKFLeftPanel,
								this.MKFRigthPanel
							]
						}

					],
					buttons:
							[
								{
									xtype: 'button',
									iconCls: 'ok16',
									text: 'Сохранить',
									margin: '0 10',
									handler: function () {
										Ext.getCmp('ufaMkfDiagSearchTreeWindow').doSave();
									}
								},
								{
									text: '-'
								},
								{
									iconCls: 'cancel16',
									text: 'Закрыть',
									margin: '0 10',
									id: 'MKFclose',
									handler: function () {
										//Ext.getCmp('ufaMkfDiagSearchTreeWindow').doDefault();
										//Ext.getCmp('ufaMkfDiagSearchTreeWindow').hide();
										Ext.getCmp('ufaMkfDiagSearchTreeWindow').refresh();
									}
								}
							]
				}
		);
		sw.Promed.ufaMkfDiagSearchTreeWindow.superclass.initComponent.apply(this, arguments);
	},

	// Формирование дерева по фильтру
	doCodeFilterTree: function ()
	{
		var tree = Ext.getCmp('SprMkf');
		var filterCode = null;
		var filterName = null;
		var loadMask = new Ext.LoadMask(tree.getEl(), {msg: lang['idet_zagruzka']});

		//Контроль значений фильтра
		if (Ext.getCmp('ICFFilterCode').getValue().trim() == "" && Ext.getCmp('ICFFilterName').getValue().trim() == "")
		{
			sw.swMsg.alert(lang['soobschenie'], 'Не указаны параметры для поиска домена!');
			return;
		}

		if (Ext.getCmp('ICFFilterCode').getValue().trim().substr(0, 1) != "")
		{
			if (Ext.getCmp('ICFFilterCode').getValue().trim().substr(0, 1) != Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen)
			{
				sw.swMsg.alert(lang['soobschenie'], 'Неверно указан код домена для поиска!');
				return;
			}
			filterCode = Ext.getCmp('ICFFilterCode').getValue().trim();
		}
		if (Ext.getCmp('ICFFilterName').getValue().trim().substr(0, 1) != "")
		{
			filterName = Ext.getCmp('ICFFilterName').getValue().trim();
		}

		//Загрузка по фильтру
		loadMask.show();
		var nn = tree.getRootNode().childNodes.length;
		for (j = 0; j < nn; j++)
		{
			tree.getRootNode().childNodes[0].remove(true);
		}
		;
		tree.getLoader().baseParams = {
			ICF_pid: null, //node.attributes.ICF_id,
			ICF_code: Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen,
			ICF_code_filter: filterCode,
			ICF_Name_filter: filterName
		}
		tree.getLoader().load(tree.getRootNode(),
				function ()
				{
					loadMask.hide();
				});

		return;
	},
	refresh: function ()
	{
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText('Обновить ' + this.objectName + ' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];

	},

	//Загрузка определителей
	setDeterminants: function (data, id)
	{
		var aSprOks = new Array();
		for (jj = 0; jj < data.length; jj++)
		{
			//console.log('[]=', data[jj]);
			switch (id)
			{
				case 'ICFSeverity_EvalRealiz':
				case 'ICFSeverity_TargetRealiz':
				case 'ICFSeverity_EvalCapasit':
				case 'ICFSeverity_TargetCapasit':
					aSprOks.push([jj,
						data[jj].ICFSeverity_id,
						data[jj].Code,
						data[jj].Name]);
					break;
				case 'ICFSeverity_Nature':
					aSprOks.push([jj,
						data[jj].ICFNature_id,
						data[jj].Code,
						data[jj].Name]);
					break;
				case 'ICFSeverity_Localization':
					aSprOks.push([jj,
						data[jj].ICFLocalization_id,
						data[jj].Code,
						data[jj].Name]);
					break;
				case 'ICFEnvFactors_eval_id':
				case 'ICFEnvFactors_Target_id':
					aSprOks.push([jj,
						data[jj].ICFEnvFactors_id,
						data[jj].Code,
						data[jj].Name]);
					break;
				default :
					sw.swMsg.alert(lang['soobschenie'], 'Косяк!!');
					break;
			}
			;

		}
		;
		//console.log('aSprOks=', aSprOks);
		Ext.getCmp(id).getStore().loadData(aSprOks);
	},
	show: function (params) {

		Ext.getCmp('ufaMkfDiagSearchTreeWindow').params = params;
		Ext.getCmp('ufaMkfDiagSearchTreeWindow').firstLoad = true;
		console.log('params=', params.Inparams);
//		Ext.getCmp('SprMkf').getLoader().baseParams = {ICF_pid : null,ICF_code : params.Inparams.Domen};
//		Ext.getCmp('SprMkf').getLoader().load(Ext.getCmp('SprMkf').getRootNode());
		//грузим справочники
		switch (params.Inparams.Domen)
		{
			case 'b':
				Ext.getCmp('ICFSeverity_EvalRealiz_id').setTitle('Выраженность нарушения');
				Ext.getCmp('ICFSeverity_TargetRealiz_id').setTitle('Цель');

				if (Ext.getCmp('ICFSeverity_EvalRealiz').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFSeverity, 'ICFSeverity_EvalRealiz');
				}
				if (Ext.getCmp('ICFSeverity_TargetRealiz').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFSeverity, 'ICFSeverity_TargetRealiz');
				}

				if (params.Inparams.Func == 'edit')
				{
					Ext.getCmp('ICFSeverity_TargetRealiz').setValue(params.Inparams.ICF_TargetRealiz);
					Ext.getCmp('ICFSeverity_EvalRealiz').setValue(params.Inparams.ICF_EvalRealiz);
					Ext.getCmp('ICFSeverity_EvalRealiz_id').show();
					Ext.getCmp('ICFSeverity_TargetRealiz_id').show();
				}
				break;
			case 'd':
				Ext.getCmp('ICFSeverity_EvalRealiz_id').setTitle('Оценка по реализации');
				Ext.getCmp('ICFSeverity_TargetRealiz_id').setTitle('Цель реализации');
				if (Ext.getCmp('ICFSeverity_EvalRealiz').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFSeverity, 'ICFSeverity_EvalRealiz');
				}
				if (Ext.getCmp('ICFSeverity_TargetRealiz').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFSeverity, 'ICFSeverity_TargetRealiz');
				}
				if (Ext.getCmp('ICFSeverity_EvalCapasit').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFSeverity, 'ICFSeverity_EvalCapasit');
				}
				if (Ext.getCmp('ICFSeverity_TargetCapasit').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFSeverity, 'ICFSeverity_TargetCapasit');
				}

				if (params.Inparams.Func == 'edit')
				{
					Ext.getCmp('ICFSeverity_TargetRealiz').setValue(params.Inparams.ICF_TargetRealiz);
					Ext.getCmp('ICFSeverity_EvalRealiz').setValue(params.Inparams.ICF_EvalRealiz);
					Ext.getCmp('ICFSeverity_EvalCapasit').setValue(params.Inparams.ICF_EvalCapasit);
					Ext.getCmp('ICFSeverity_TargetCapasit').setValue(params.Inparams.ICF_TargetCapasit);
					Ext.getCmp('ICFSeverity_EvalRealiz_id').show();
					Ext.getCmp('ICFSeverity_TargetRealiz_id').show();
					Ext.getCmp('ICFSeverity_EvalCapasit_id').show();
					Ext.getCmp('ICFSeverity_TargetCapasit_id').show();
				}
				break;
			case 's':
				Ext.getCmp('ICFSeverity_EvalRealiz_id').setTitle('Выраженность нарушения');
				Ext.getCmp('ICFSeverity_TargetRealiz_id').setTitle('Цель');
				Ext.getCmp('ICFSeverity_Nature_panel').show();
				Ext.getCmp('ICFSeverity_Localization_panel').show();
				if (Ext.getCmp('ICFSeverity_EvalRealiz').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFSeverity, 'ICFSeverity_EvalRealiz');
				}
				if (Ext.getCmp('ICFSeverity_TargetRealiz').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFSeverity, 'ICFSeverity_TargetRealiz');
				}
				if (Ext.getCmp('ICFSeverity_Nature').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFNature, 'ICFSeverity_Nature');
				}
				if (Ext.getCmp('ICFSeverity_Localization').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFLocalization, 'ICFSeverity_Localization');
				}
				if (params.Inparams.Func == 'edit')
				{
					Ext.getCmp('ICFSeverity_Nature_panel').show();
					Ext.getCmp('ICFSeverity_Localization_panel').show();
					Ext.getCmp('ICFSeverity_EvalRealiz_id').show();
					Ext.getCmp('ICFSeverity_TargetRealiz_id').show();
					Ext.getCmp('ICFSeverity_TargetRealiz').setValue(params.Inparams.ICF_TargetRealiz);
					Ext.getCmp('ICFSeverity_EvalRealiz').setValue(params.Inparams.ICF_EvalRealiz);
					Ext.getCmp('ICFSeverity_EvalCapasit').setValue(params.Inparams.ICF_EvalCapasit);
					Ext.getCmp('ICFSeverity_TargetCapasit').setValue(params.Inparams.ICF_TargetCapasit);
					Ext.getCmp('ICFSeverity_Nature').setValue(params.Inparams.ICFSeverity_Nature);
					Ext.getCmp('ICFSeverity_Localization').setValue(params.Inparams.CFSeverity_Localization);
				}
				break;
			case 'e':
				if (Ext.getCmp('ICFEnvFactors_eval_id').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFEnvFactors, 'ICFEnvFactors_eval_id');
				}
				if (Ext.getCmp('ICFEnvFactors_Target_id').getStore().data.items.length == 0)
				{
					this.setDeterminants(params.Inparams.ICFSpr.ICFEnvFactors, 'ICFEnvFactors_Target_id');
				}
				if (params.Inparams.Func == 'edit')
				{
					Ext.getCmp('ICFEnvFactors_eval').show();
					Ext.getCmp('ICFEnvFactors_Target').show();
					Ext.getCmp('ICFEnvFactors_eval_id').setValue(params.Inparams.ICF_EnvFactors);
					Ext.getCmp('ICFEnvFactors_Target_id').setValue(params.Inparams.ICF_FactorsTarget);
				}

//					Ext.getCmp('ICFEnvFactors_eval_id').getStore().load(
//								{ callback: function (data,callback)
//								  {
//									if(data.length > 0 && params.Inparams.Func == 'edit')
//									{
//										alert('rrrrrrr');
//									}
//								  }
//								});

				break;
			default :
				sw.swMsg.alert(lang['soobschenie'], 'Косяк!!');
				Ext.getCmp('ufaMkfDiagSearchTreeWindow').firstLoad = false;
				break;
		}




		//Отработка даты
		Ext.getCmp('ICFDate').setMinValue(params.Inparams.Event_setDate);
		//Ext.getCmp('ICFtoolbar').items.items[2].hide() // Убираем объекты из toolbar
		//Подстраиваем форму под режим
		if (params.Inparams.Func == 'add')
		{
			Ext.getCmp('SprMkfEdit').hide();
			Ext.getCmp('SprMkf').show();
			Ext.getCmp('ICFSearch').show();

		} else
		{
			Ext.getCmp('SprMkfEdit').show();
			Ext.getCmp('SprMkf').hide();
			Ext.getCmp('SprMk_Code').setValue(params.Inparams.Code);
			Ext.getCmp('SprMk_Name').setValue(params.Inparams.ICF_Name);
			Ext.getCmp('ICFDescription').setValue(params.Inparams.Description);
			Ext.getCmp('ICFDate').setValue(params.Inparams.ICFDate);
			Ext.getCmp('ICFSearch').hide();
			Ext.getCmp('ufaMkfDiagSearchTreeWindow').setTitle("Оценка состояния по МКФ: Редактирование");
		}

		//Переопределение метода
		if (arguments[0].callback1)
		{
			this.callback1 = arguments[0].callback1;
			//console.log('раз');
		} else
		{
			this.callback1 = Ext.emptyFn;
		}


		sw.Promed.ufaMkfDiagSearchTreeWindow.superclass.show.apply(this, arguments);

//		if(typeof params.Inparams.ICF_id != 'undefined' )
//		{
//			Ext.getCmp('ufaMkfDiagSearchTreeWindow').Inparams = params.Inparams;
//			var tt = ['b','b1','b110-b139','b122'];  //!!!!!!!!!!!!!!!!!подумать над форматом
//			var comand = "";
//			var ss = [];
//			for (jj = 0; jj < tt.length; jj++)
//			{
//				ss[jj] = comand + "findChild('ICF_Code','" + tt[jj] + "').";
//				comand =  ss[jj];
//			}
//			console.log('ss[jj]=',ss);
//			Ext.getCmp('ufaMkfDiagSearchTreeWindow').search = ss;
//
//		}
	},

	callback1: function (data) {
//      console.log('callback1=',params);
	},
	/**
	 * Сохранение оценки и перерисовка результата
	 */
	SaveICFRating: function (func, data)
	{
		console.log('data=', data[0]);
		console.log('data=', data[0].ICFRating_setDate);
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite_idet_sohranenie']});
		loadMask.show();

		Ext.Ajax.request({
			url: '?c=Ufa_Reab_Register_User&m=SaveICFRating', //saveRegistrScale - контроль на дату
			params: {
				Person_id: Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Person_id,
				ReabEvent_id: Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.ReabEvent_id,
				ICFRating_setDate: data[0].ICFRating_setDate,
				ICF_id: data[0].ICF_id,
				ICFSeverity_id: data[0].ICFSeverity_id,
				ICFNature_id: data[0].ICFNature_id,
				ICFLocalization_id: data[0].ICFLocalization_id,
				ReabICFRating_TargetRealiz: data[0].ICFRating_TargetRealiz,
				ReabICFRating_TargetCapasit: data[0].ICFRating_TargetCapasit,
				ICFRating_CapasitEval: data[0].ICFRating_CapasitEval,
				ICFEnvFactors_id: data[0].ICFEnvFactors_id,
				ReabICFRating_FactorsTarget: data[0].ReabICFRating_FactorsTarget,
				ReabICFRating_id: data[0].ReabICFRating_id,
				MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
				Func: func
			},
			callback: function (options, success, response)
			{
				loadMask.hide(); // Обязательно сделать

				if (success == true)
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					console.log('response_obj=', response_obj);

					if (response_obj.success == true)
					{
						if (func == 'add')
						{
							Ext.getCmp('ufaMkfDiagSearchTreeWindow').doClear();
							sw.swMsg.alert(lang['soobschenie'], 'Проведенная оценка сохранена!');
						} else
						{
							console.log('response_obj=', response_obj);
							if (func == 'edit' && response_obj.Error_Msg == 'Все в норме!')
							{
								Ext.getCmp('ufaMkfDiagSearchTreeWindow').refresh();
							}
						}

						return;
					}

				} else
				{
					loadMask.hide();
					sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
				return;
			}
		})
	},
	//Обнуление объектов
	doClear: function ()
	{
		console.log('params=', Ext.getCmp('ufaMkfDiagSearchTreeWindow').params);
		Ext.getCmp('ICFDate').setValue(new Date());
		if (Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Func == 'add')
		{
			Ext.getCmp('SprMkf').getSelectionModel().clearSelections( );
			//Убираем лишнее с экрана
			Ext.getCmp('ICFDescription').setValue("");
			switch (Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen)
			{
				case 'b':
					Ext.getCmp('ICFSeverity_EvalRealiz_id').hide();
					Ext.getCmp('ICFSeverity_TargetRealiz_id').hide();
					Ext.getCmp('ICFSeverity_EvalRealiz').setValue("Введите параметр");
					Ext.getCmp('ICFSeverity_TargetRealiz').setValue("");

					break;
				case 'd':
					Ext.getCmp('ICFSeverity_EvalRealiz_id').hide();
					Ext.getCmp('ICFSeverity_TargetRealiz_id').hide();
					Ext.getCmp('ICFSeverity_EvalRealiz').setValue("Введите параметр");
					Ext.getCmp('ICFSeverity_TargetRealiz').setValue("");
					Ext.getCmp('ICFSeverity_EvalCapasit_id').hide();
					Ext.getCmp('ICFSeverity_TargetCapasit_id').hide();
					Ext.getCmp('ICFSeverity_EvalCapasit').setValue("Введите параметр");
					Ext.getCmp('ICFSeverity_TargetCapasit').setValue("");
					break;
				case 's':
					Ext.getCmp('ICFSeverity_Nature_panel').hide();
					Ext.getCmp('ICFSeverity_Localization_panel').hide();
					Ext.getCmp('ICFSeverity_EvalRealiz_id').hide();
					Ext.getCmp('ICFSeverity_TargetRealiz_id').hide();
					Ext.getCmp('ICFSeverity_EvalRealiz').setValue("Введите параметр");
					Ext.getCmp('ICFSeverity_TargetRealiz').setValue("");
					Ext.getCmp('ICFSeverity_Nature').setValue("");
					Ext.getCmp('ICFSeverity_Localization').setValue("");
					break;
				case 'e':
					Ext.getCmp('ICFEnvFactors_eval').hide();
					Ext.getCmp('ICFEnvFactors_Target').hide();
					Ext.getCmp('ICFEnvFactors_eval_id').setValue("Введите параметр");
					Ext.getCmp('ICFEnvFactors_Target_id').setValue("");

					break;
				default :
					sw.swMsg.alert(lang['soobschenie'], 'Косяк!!');
					Ext.getCmp('ufaMkfDiagSearchTreeWindow').firstLoad = false;
					break;
			}
		}

	},
	doSave: function () {
		//Валидация
		//Дата валидируется в объекте
		var func = Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Func;
		var domen = Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.Domen;
		if (func == 'add')
		{
			// ВЫбор домена
			if (Ext.getCmp('SprMkf').getSelectionModel().selNode == null)
			{
				sw.swMsg.show(
						{icon: Ext.MessageBox.ERROR,
							title: lang['oshibka'],
							msg: 'Укажите оцениваемый домен!',
							buttons: Ext.Msg.OK
						});
				return false;
			}
			//Оценка домена (3 знака и более)
			if (Ext.getCmp('SprMkf').getSelectionModel().selNode.attributes.ICF_Code.length < 4 || Ext.getCmp('SprMkf').getSelectionModel().selNode.attributes.ICF_Code.indexOf("-") > 0)
			{
				sw.swMsg.show(
						{icon: Ext.MessageBox.ERROR,
							title: lang['oshibka'],
							msg: 'Неверно указан домен!',
							buttons: Ext.Msg.OK
						});
				return false;
			}
			var ICF_id = Ext.getCmp('SprMkf').getSelectionModel().selNode.attributes.ICF_id;
			var rr = Ext.getCmp('SprMkf').getSelectionModel().selNode.attributes.ICF_Code.substr(0, 1);
			var ReabICFRating_id = null;
		} else
		{
			var ICF_id = null;
			var rr = domen;
			var ReabICFRating_id = Ext.getCmp('ufaMkfDiagSearchTreeWindow').params.Inparams.ReabICFRating_id;
		}


		var ICFRating_setDate = ('0' + Ext.getCmp('ICFDate').getValue().getDate()).slice(-2) + '.' + ('0' + (Ext.getCmp('ICFDate').getValue().getMonth() + 1)).slice(-2) + '.' + Ext.getCmp('ICFDate').getValue().getFullYear();
		var ICFSeverity_id = null;
		var ICFNature_id = null;
		var ICFLocalization_id = null;
		var ICFRating_TargetRealiz = null;
		var ICFRating_TargetCapasit = null;
		var ICFRating_CapasitEval = null;
		var ICFEnvFactors_eval = null;
		var ICFEnvFactors_Target = null;


		if (rr == 'b' || rr == 's' || rr == 'd')
		{
			if (Ext.getCmp('ICFSeverity_EvalRealiz').getValue() == "")
			{
				sw.swMsg.show(
						{icon: Ext.MessageBox.ERROR,
							title: lang['oshibka'],
							msg: 'Введите параметр "' + Ext.getCmp('ICFSeverity_EvalRealiz_id').title + '"!',
							buttons: Ext.Msg.OK
						});
				return false;
			}
			ICFSeverity_id = Ext.getCmp('ICFSeverity_EvalRealiz').getValue();
			var index = Ext.getCmp('ICFSeverity_EvalRealiz').getStore().find('ICFSeverity_id', Ext.getCmp('ICFSeverity_EvalRealiz').getValue());
			if (Ext.getCmp('ICFSeverity_TargetRealiz').getValue() == "")
			{
				if (index == 0)
				{
					//Показатели выраженности нарушений и цели реализации одинаковы
					console.log('var1=', Ext.getCmp('ICFSeverity_EvalRealiz').getValue());
					ICFRating_TargetRealiz = Ext.getCmp('ICFSeverity_EvalRealiz').getValue();
				} else
				{
					// цель на 1 выше, чем нарушения
					if (Ext.getCmp('ICFSeverity_EvalRealiz').getStore().data.items[Ext.getCmp('ICFSeverity_EvalRealiz').getStore().find('ICFSeverity_id', Ext.getCmp('ICFSeverity_EvalRealiz').getValue())].data.Code != "8" &&
							Ext.getCmp('ICFSeverity_EvalRealiz').getStore().data.items[Ext.getCmp('ICFSeverity_EvalRealiz').getStore().find('ICFSeverity_id', Ext.getCmp('ICFSeverity_EvalRealiz').getValue())].data.Code != "9")
					{
						ICFRating_TargetRealiz = Ext.getCmp('ICFSeverity_TargetRealiz').store.data.items[index - 1].data.ICFSeverity_id;
					} else
					{
						ICFRating_TargetRealiz = Ext.getCmp('ICFSeverity_EvalRealiz').getValue();
					}
				}
			} else
			{
				var index1 = Ext.getCmp('ICFSeverity_TargetRealiz').getStore().find('ICFSeverity_id', Ext.getCmp('ICFSeverity_TargetRealiz').getValue());
				if ((index - index1) < 0)
				{
					sw.swMsg.show(
							{icon: Ext.MessageBox.ERROR,
								title: lang['oshibka'],
								msg: 'Неверно указана цель реализации!',
								buttons: Ext.Msg.OK
							});
					return false;
				} else
				{
					ICFRating_TargetRealiz = Ext.getCmp('ICFSeverity_TargetRealiz').getValue();
				}

			}
		}

		if (rr == 's')
		{
			console.log('SSSSS=');
			if (Ext.getCmp('ICFSeverity_Nature').getValue() == "")
			{
				ICFNature_id = Ext.getCmp('ICFSeverity_Nature').store.data.items[Ext.getCmp('ICFSeverity_Nature').getStore().find('Name', 'Не определено')].data.ICFNature_id;
			} else
			{
				ICFNature_id = Ext.getCmp('ICFSeverity_Nature').getValue();
			}
			if (Ext.getCmp('ICFSeverity_Localization').getValue() == "")
			{
				ICFLocalization_id = Ext.getCmp('ICFSeverity_Localization').store.data.items[Ext.getCmp('ICFSeverity_Localization').getStore().find('Name', 'Не определено')].data.ICFLocalization_id;
			} else
			{
				ICFLocalization_id = Ext.getCmp('ICFSeverity_Localization').getValue();
			}

		}
		if (rr == 'd')
		{
			if (Ext.getCmp('ICFSeverity_EvalCapasit').getValue() == "")
			{
				sw.swMsg.show(
						{icon: Ext.MessageBox.ERROR,
							title: lang['oshibka'],
							msg: 'Введите параметр "Оценка по капаситету"!',
							buttons: Ext.Msg.OK
						});
				return false;
			}
			ICFRating_CapasitEval = Ext.getCmp('ICFSeverity_EvalCapasit').getValue();
			var index2 = Ext.getCmp('ICFSeverity_EvalCapasit').getStore().find('ICFSeverity_id', Ext.getCmp('ICFSeverity_EvalCapasit').getValue());
			if (Ext.getCmp('ICFSeverity_TargetCapasit').getValue() == "")
			{
				if (index2 == 0)
				{
					//Показатели выраженности нарушений и цели реализации одинаковы
					ICFRating_TargetCapasit = Ext.getCmp('ICFSeverity_EvalCapasit').getValue();
				} else
				{
					// цель на 1 выше, чем нарушения
					if (Ext.getCmp('ICFSeverity_EvalCapasit').getStore().data.items[Ext.getCmp('ICFSeverity_EvalCapasit').getStore().find('ICFSeverity_id', Ext.getCmp('ICFSeverity_EvalCapasit').getValue())].data.Code != "8" &&
							Ext.getCmp('ICFSeverity_EvalCapasit').getStore().data.items[Ext.getCmp('ICFSeverity_EvalCapasit').getStore().find('ICFSeverity_id', Ext.getCmp('ICFSeverity_EvalCapasit').getValue())].data.Code != "9")
					{
						ICFRating_TargetCapasit = Ext.getCmp('ICFSeverity_TargetCapasit').store.data.items[index2 - 1].data.ICFSeverity_id;
					} else
					{
						ICFRating_TargetCapasit = Ext.getCmp('ICFSeverity_EvalCapasit').getValue();
					}
				}
			} else
			{
				var index3 = Ext.getCmp('ICFSeverity_TargetCapasit').getStore().find('ICFSeverity_id', Ext.getCmp('ICFSeverity_TargetCapasit').getValue());
				if ((index2 - index3) < 0)
				{
					sw.swMsg.show(
							{icon: Ext.MessageBox.ERROR,
								title: lang['oshibka'],
								msg: 'Неверно указана цель по капаситету!',
								buttons: Ext.Msg.OK
							});
					return false;
				} else
				{
					ICFRating_TargetCapasit = Ext.getCmp('ICFSeverity_TargetCapasit').getValue();
				}

			}

		}

		if (rr == 'e')
		{
			if (Ext.getCmp('ICFEnvFactors_eval_id').getValue() == "")
			{
				sw.swMsg.show(
						{icon: Ext.MessageBox.ERROR,
							title: lang['oshibka'],
							msg: 'Введите параметр "Степень выраженности среды"!',
							buttons: Ext.Msg.OK
						});
				return false;
			}
			ICFEnvFactors_eval = Ext.getCmp('ICFEnvFactors_eval_id').getValue();
			var index4 = Ext.getCmp('ICFEnvFactors_eval_id').getStore().find('ICFEnvFactors_id', Ext.getCmp('ICFEnvFactors_eval_id').getValue());
			if (Ext.getCmp('ICFEnvFactors_Target_id').getValue() == "")
			{
				if (Ext.getCmp('ICFEnvFactors_eval_id').getStore().data.items[Ext.getCmp('ICFEnvFactors_eval_id').getStore().find('ICFEnvFactors_id', Ext.getCmp('ICFEnvFactors_eval_id').getValue())].data.Code != "-8" &&
						Ext.getCmp('ICFEnvFactors_eval_id').getStore().data.items[Ext.getCmp('ICFEnvFactors_eval_id').getStore().find('ICFEnvFactors_id', Ext.getCmp('ICFEnvFactors_eval_id').getValue())].data.Code != "0" &&
						Ext.getCmp('ICFEnvFactors_eval_id').getStore().data.items[Ext.getCmp('ICFEnvFactors_eval_id').getStore().find('ICFEnvFactors_id', Ext.getCmp('ICFEnvFactors_eval_id').getValue())].data.Code != "8" &&
						Ext.getCmp('ICFEnvFactors_eval_id').getStore().data.items[Ext.getCmp('ICFEnvFactors_eval_id').getStore().find('ICFEnvFactors_id', Ext.getCmp('ICFEnvFactors_eval_id').getValue())].data.Code != "9"
				)
				{
					ICFEnvFactors_Target = Ext.getCmp('ICFEnvFactors_eval_id').store.data.items[index4 + 1].data.ICFEnvFactors_id;
				} else
				{
					ICFEnvFactors_Target = Ext.getCmp('ICFEnvFactors_eval_id').getValue();
				}
			} else
			{
				var index5 = Ext.getCmp('ICFEnvFactors_Target_id').getStore().find('ICFEnvFactors_id', Ext.getCmp('ICFEnvFactors_Target_id').getValue());
				var cEnvFactors_Target = Ext.getCmp('ICFEnvFactors_Target_id').getStore().data.items[Ext.getCmp('ICFEnvFactors_Target_id').getStore().find('ICFEnvFactors_id', Ext.getCmp('ICFEnvFactors_Target_id').getValue())].data.Code;
				var cEnvFactors_eval = Ext.getCmp('ICFEnvFactors_eval_id').getStore().data.items[Ext.getCmp('ICFEnvFactors_eval_id').getStore().find('ICFEnvFactors_id', Ext.getCmp('ICFEnvFactors_eval_id').getValue())].data.Code;

				if (cEnvFactors_eval.inlist(['-8', '0', '8', '9']) == true)
				{
					//console.log('var5555=');
					if (Ext.getCmp('ICFEnvFactors_Target_id').getValue() != Ext.getCmp('ICFSeverity_EvalCapasit').getValue())
					{
						sw.swMsg.show(
								{icon: Ext.MessageBox.ERROR,
									title: lang['oshibka'],
									msg: 'Не верно указана цель по реализации!',
									buttons: Ext.Msg.OK
								});
						return false;
					} else
					{
						ICFEnvFactors_Target = Ext.getCmp('ICFEnvFactors_Target_id').getValue();
					}
				} else
				{
					if (cEnvFactors_Target.inlist(['-4', '-3', '-2', '-1', '0', '1', '2', '3', '4']) == true)
					{
						if ((index4 - index5) > 0)
						{
							sw.swMsg.show(
									{icon: Ext.MessageBox.ERROR,
										title: lang['oshibka'],
										msg: 'Не верно указана цель по реализации!',
										buttons: Ext.Msg.OK
									});
							return false;
						} else
						{
							ICFEnvFactors_Target = Ext.getCmp('ICFEnvFactors_Target_id').getValue();
						}

					} else
					{
						//console.log('var5555=');
						if (Ext.getCmp('ICFEnvFactors_Target_id').getValue() != Ext.getCmp('ICFSeverity_EvalCapasit').getValue())
						{
							sw.swMsg.show(
									{icon: Ext.MessageBox.ERROR,
										title: lang['oshibka'],
										msg: 'Не верно указана цель по реализации!',
										buttons: Ext.Msg.OK
									});
							return false;
						} else
						{
							ICFEnvFactors_Target = Ext.getCmp('ICFEnvFactors_Target_id').getValue();
						}
					}
				}
			}
		}


		//На выход
		var form = Ext.getCmp('ufaMkfDiagSearchTreeWindow');
		var data = new Object();
		data = [{
			'ICFRating_setDate': ICFRating_setDate,
			'ICF_id': ICF_id,
			'ICFSeverity_id': ICFSeverity_id,
			'ICFNature_id': ICFNature_id,
			'ICFLocalization_id': ICFLocalization_id,
			'ICFRating_TargetRealiz': ICFRating_TargetRealiz,
			'ICFRating_TargetCapasit': ICFRating_TargetCapasit,
			'ICFRating_CapasitEval': ICFRating_CapasitEval,
			'ICFEnvFactors_id': ICFEnvFactors_eval,
			'ReabICFRating_FactorsTarget': ICFEnvFactors_Target,
			'ReabICFRating_id': ReabICFRating_id
		}];
		console.log('data=', data);
		this.SaveICFRating(form.params.Inparams.Func, data);


		//this.callback1(data);
		//this.hide();
		//this.refresh();
		return;
	},
	doDefault: function ()
	{
		var data = new Object();
		data = [{
			'Func': 'out'
		}];
		//console.log('data=',data);
		this.callback1(data);
		//this.hide();
		this.refresh();
	},
	listeners: {
		'hide': function () {
			this.doDefault();
//			if (this.refresh)
//				this.onHide();
		},
		'close': function () {
			if (this.refresh)
				this.onHide();
		}
	}
});