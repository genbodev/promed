/**
* swMedSvidSelectSvidType - окно выбора типа свидетельства, для печати пустого бланка
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Salakhov Rustam
* @version      28.06.2010
*/

/**
 * swMedSvidSelectSvidType - окно выбора типа свидетельства, для печати пустого бланка
 *
 * @class sw.Promed.swMedSvidSelectSvidType
 * @extends Ext.Window
 */
sw.Promed.swMedSvidSelectSvidType = Ext.extend(sw.Promed.BaseForm, {
	closable: false,
	width : 500,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['vyibor_tipa_svidetelstva'],

	params: null,

	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swMedSvidSelectSvidType.superclass.show.apply(this, arguments);

		var win = this;
		win.lastNumerator_id = null;
		this.buttons[0].enable();

		this.fromPatMorphArm = false;
		if (arguments[0] && arguments[0].fromPatMorphArm) {
			this.fromPatMorphArm = arguments[0].fromPatMorphArm;
		}
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (this.fromPatMorphArm){
			base_form.findField('SvidType').getStore().filterBy(function(rec){
				return rec.id !== 'birth';
			});
			base_form.findField('SvidType').setValue('death');
		} else {
			base_form.findField('SvidType').getStore().clearFilter();
			base_form.findField('SvidType').setValue('birth');
		}

		base_form.findField('SvidType').fireEvent('change', base_form.findField('SvidType'), base_form.findField('SvidType').getValue());

		win.generateMedSvidNumSample();
	},
	generateMedSvidNumSample: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var num = '';
		var curValue = '';

		if (!Ext.isEmpty(base_form.findField('Numerator_id').getFieldValue('Numerator_Num'))) {
			curValue = base_form.findField('Numerator_id').getFieldValue('Numerator_Num').toString();
		}

		if (!Ext.isEmpty(base_form.findField('Numerator_IntNum').getValue())) {
			curValue = base_form.findField('Numerator_IntNum').getValue().toString();
		}

		if (!Ext.isEmpty(base_form.findField('Numerator_NumLen').getValue())) {
			while(curValue.length < parseInt(base_form.findField('Numerator_NumLen').getValue())) {
				curValue = '0' + curValue;
			}
		}

		num = base_form.findField('Numerator_Ser').getValue() + ' ' + base_form.findField('Numerator_PreNum').getValue() + curValue + base_form.findField('Numerator_PostNum').getValue();
		this.findById('MedSvidNumSample').setText('<b>'+num+'</b>', false);
	},
	doPrint: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();

		var svid_type = base_form.findField('SvidType').getValue();
		if (getRegionNick() == 'ufa' && base_form.findField('Numerator_id').getStore().getCount() == 0) {
			var svid_type_txt = '';
			switch(svid_type) {
				case 'birth':
					svid_type_txt = lang['o_rojdenii'];
					break;
				case 'death':
					svid_type_txt = lang['o_smerti'];
					break;
				case 'deathpnt':
					svid_type_txt = lang['o_perinatalnoy_smerti'];
					break;
			}
			sw.swMsg.alert(lang['oshibka'], lang['dlya_svditelstv']+svid_type_txt+lang['ne_zadan_aktivnyiy_numerator_obratites_k_administratoru_sistemyi']);
			return false;
		}

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var BlankCount = base_form.findField('BlankCount').getValue();
		var DoubleSide = 0;
		if (base_form.findField('DoubleSide').checked) {
			DoubleSide = 1;
		}

		var paramNumStart = base_form.findField('Numerator_IntNum').getValue();
		var numeratorId = base_form.findField('Numerator_id').getValue();

		if (!Ext.isEmpty(numeratorId)) {
			// Получаем текст бланка
			win.getLoadMask(lang['podojdite_idet_rezervirovanie_nomerov']).show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					var error = lang['oshibka_rezervirovaniya_nomerov'];
					win.getLoadMask().hide();
					if ( success ) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.Numerator_Nums) {
							var Numerator_Nums = Ext.util.JSON.encode(result.Numerator_Nums);
							if ( Ext.isEmpty(result.Numerator_Ser) ) {
								result.Numerator_Ser = '';
							}
							if (BlankCount > 1) {
								window.open('/?c=MedSvid&m=printBlanks&Numerator_Nums='+Numerator_Nums+'&svid_type='+svid_type+'&Numerator_PreNum=' + encodeURIComponent(base_form.findField('Numerator_PreNum').getValue()) + '&Numerator_PostNum=' + encodeURIComponent(base_form.findField('Numerator_PostNum').getValue()) + '&Numerator_IntNum=' + result.Numerator_Num + '&Numerator_Ser=' + result.Numerator_Ser + '&BlankCount=' + BlankCount + '&DoubleSide=' + DoubleSide, '_blank');
							} else {
								result.Numerator_Num = base_form.findField('Numerator_PreNum').getValue()+result.Numerator_Num+base_form.findField('Numerator_PostNum').getValue();
								switch (svid_type) {
									case 'death':
										//@task https://jira.is-mis.ru/browse/PROMEDWEB-3273
										//При проставленной галке на печать должен выводиться шаблон DeathSvid_empt_dbl_pnt.rtpdesign
										if(DoubleSide > 0){
											printBirt({
												'Report_FileName': 'DeathSvid_empt_dbl_pnt.rtpdesign',
												'Report_Params': 'paramNumStart=' + result.Numerator_Num + '&paramSer=' + result.Numerator_Ser + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
												'Report_Format': 'pdf'
											});
										}
										else{
											printBirt({
												'Report_FileName': 'DeathSvid_empt.rptdesign',
												'Report_Params': 'paramNumStart=' + result.Numerator_Num + '&paramSer=' + result.Numerator_Ser + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
												'Report_Format': 'pdf'
											});
											printBirt({
												'Report_FileName': 'DeathSvid_Oborot_empt.rptdesign',
												'Report_Params': 'paramNumStart=' + result.Numerator_Num + '&paramSer=' + result.Numerator_Ser + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
												'Report_Format': 'pdf'
											});
										}
										break;
									case 'birth':
										printBirt({
											'Report_FileName': 'BirthSvid.rptdesign',
											'Report_Params': 'paramNumStart=' + result.Numerator_Num + '&amp;paramSer=' + result.Numerator_Ser + '&amp;paramCount=' + BlankCount + '&amp;DoubleSide=' + DoubleSide,
											'Report_Format': 'pdf'
										});
										if(getRegionNick() == 'kz'){
											printBirt({
												'Report_FileName': 'BirthSvid_check.rptdesign',// шаблон только для Казахстана
												'Report_Params': 'paramNumStart=' + result.Numerator_Num + '&paramSer=' + result.Numerator_Ser + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
												'Report_Format': 'pdf'
											});
										}
										break;
									case 'pntdeath':
										printBirt({
											'Report_FileName': 'PntDeathSvid_empt.rptdesign',
											'Report_Params': 'paramNumStart=' + result.Numerator_Num + '&paramSer=' + result.Numerator_Ser + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
											'Report_Format': 'pdf'
										});
										printBirt({
											'Report_FileName': 'PntDeathSvid_Oborot_empt.rptdesign',
											'Report_Params': 'paramNumStart=' + result.Numerator_Num + '&paramSer=' + result.Numerator_Ser + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
											'Report_Format': 'pdf'
										});
										break;
								}
							}
							win.hide();
						} else {
							sw.swMsg.alert(lang['oshibka'], result.Error_Msg ? result.Error_Msg : error);
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], error);
					}
				},
				params: {
					Numerator_id: numeratorId,
					svid_type: svid_type,
					Blank_Count: BlankCount,
					Blank_FirstNum: paramNumStart
				},
				url: '/?c=MedSvid&m=reserveNums'
			});
		}
		else {// нумератор не выбран
			if (BlankCount > 1) {
				window.open('/?c=MedSvid&m=printBlanks&svid_type='+svid_type+'&Numerator_PreNum=&Numerator_PostNum=&Numerator_IntNum=' + paramNumStart + '&Numerator_Ser=' + base_form.findField('Numerator_Ser').getValue() + '&BlankCount=' + BlankCount + '&DoubleSide=' + DoubleSide, '_blank');
			} else {
				switch (svid_type) {
					case 'death':
						printBirt({
							'Report_FileName': 'DeathSvid_empt.rptdesign',
							'Report_Params': 'paramNumStart=' + paramNumStart + '&paramSer=' + base_form.findField('Numerator_Ser').getValue() + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
							'Report_Format': 'pdf'
						});
						printBirt({
							'Report_FileName': 'DeathSvid_Oborot_empt.rptdesign',
							'Report_Params': 'paramNumStart=' + paramNumStart + '&paramSer=' + base_form.findField('Numerator_Ser').getValue() + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
							'Report_Format': 'pdf'
						});
						break;
					case 'birth':
						printBirt({
							'Report_FileName': 'BirthSvid.rptdesign',
							'Report_Params': 'paramNumStart=' + paramNumStart + '&paramSer=' + base_form.findField('Numerator_Ser').getValue() + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
							'Report_Format': 'pdf'
						});
						if(getRegionNick() == 'kz'){
							printBirt({
								'Report_FileName': 'BirthSvid_check.rptdesign',// шаблон только для Казахстана
								'Report_Params': 'paramNumStart=' + paramNumStart + '&paramSer=' + base_form.findField('Numerator_Ser').getValue() + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
								'Report_Format': 'pdf'
							});
						}
						break;
					case 'pntdeath':
						printBirt({
							'Report_FileName': 'PntDeathSvid_empt.rptdesign',
							'Report_Params': 'paramNumStart=' + paramNumStart + '&paramSer=' + base_form.findField('Numerator_Ser').getValue() + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
							'Report_Format': 'pdf'
						});
						printBirt({
							'Report_FileName': 'PntDeathSvid_Oborot_empt.rptdesign',
							'Report_Params': 'paramNumStart=' + paramNumStart + '&paramSer=' + base_form.findField('Numerator_Ser').getValue() + '&paramCount=' + BlankCount + '&DoubleSide=' + DoubleSide,
							'Report_Format': 'pdf'
						});
						break;
				}
			}
		}
	},
	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			id : 'SelectSvidTypeForm',
			autoHeight: true,
			layout : 'form',
			border : false,
			frame : true,
			style : 'padding: 10px',
			labelWidth : 140,
			labelAlign: 'right',
			items : [{
				name: 'Numerator_NumLen',
				xtype: 'hidden'
			}, {
				name: 'Numerator_PreNum',
				xtype: 'hidden'
			}, {
				name: 'Numerator_PostNum',
				xtype: 'hidden'
			}, {
				xtype:'combo',
				hiddenName: 'SvidType',
				store: new Ext.data.SimpleStore({
					id: 0,
					fields: [
						'code',
						'name'
					],
					data: [
						['birth', lang['svidetelstvo_o_rojdenii']],
						['death', lang['svidetelstvo_o_smerti']],
						['pntdeath', lang['svidetelstvo_o_perinatalnoy_smerti']]
					]
				}),
				displayField: 'name',
				valueField: 'code',
				editable: false,
				allowBlank: false,
				mode: 'local',
				lastQuery: '',
				listeners: {
					'select': function(combo, record, index) {
						combo.fireEvent('change', combo, record.get('code'));
					},
					'change': function(combo, newValue) {
						var base_form = win.FormPanel.getForm();
						var NumeratorObject_SysName = "";
						switch(newValue) {
							case 'birth':
								NumeratorObject_SysName = 'BirthSvid';
								break;
							case 'death':
								NumeratorObject_SysName = 'DeathSvid';
								break;
							case 'pntdeath':
								NumeratorObject_SysName = 'PntDeathSvid';
								break;
						}

						if (base_form.findField('Numerator_id').getStore().baseParams.NumeratorObject_SysName != NumeratorObject_SysName) {
							base_form.findField('Numerator_id').clearValue();
							base_form.findField('Numerator_id').fireEvent('change', base_form.findField('Numerator_id'), base_form.findField('Numerator_id').getValue());
							base_form.findField('Numerator_id').getStore().removeAll();
							base_form.findField('Numerator_id').getStore().baseParams.NumeratorObject_SysName = NumeratorObject_SysName;
							win.getLoadMask(lang['poluchenie_spiska_numeratorov']).show();
							base_form.findField('Numerator_id').getStore().load({
								params: {
									NumeratorObject_SysName: NumeratorObject_SysName,
									allowFuture: 1
								},
								callback: function () {
									win.getLoadMask().hide();

									if (base_form.findField('Numerator_id').getStore().getCount() == 1) {
										base_form.findField('Numerator_id').setValue(base_form.findField('Numerator_id').getStore().getAt(0).get('Numerator_id'));
										base_form.findField('Numerator_id').fireEvent('change', base_form.findField('Numerator_id'), base_form.findField('Numerator_id').getValue());
									}
								}
							});
						}
					}
				},
				forceSelection: true,
				triggerAction: 'all',
				fieldLabel: lang['tip_svidetelstva'],
				width:  300,
				value: 'birth',
				selectOnFocus: true
			}, {
				hiddenName: 'Numerator_id',
				fieldLabel: lang['numerator'],
				allowBlank: getRegionNick() != 'ufa',
				mode: 'local',
				listeners: {
					'select': function(combo, record, index) {
						combo.fireEvent('change', combo, record.get('Numerator_id'));
					},
					'change': function (combo, newValue, oldValue) {
						var base_form = win.FormPanel.getForm();

						if (newValue != win.lastNumerator_id) {
							win.lastNumerator_id = newValue;
							base_form.findField('Numerator_Ser').getEl().dom.removeAttribute('readOnly');
							if (Ext.isEmpty(newValue)) {
								base_form.findField('Numerator_Ser').setValue(null);
								base_form.findField('Numerator_IntNum').setValue(null);
								base_form.findField('Numerator_NumLen').setValue(null);
								base_form.findField('Numerator_PreNum').setValue(null);
								base_form.findField('Numerator_PostNum').setValue(null);
							} else {
								base_form.findField('Numerator_Ser').setValue(combo.getFieldValue('Numerator_Ser'));
								base_form.findField('Numerator_NumLen').setValue(combo.getFieldValue('Numerator_NumLen'));
								base_form.findField('Numerator_PreNum').setValue(combo.getFieldValue('Numerator_PreNum'));
								base_form.findField('Numerator_PostNum').setValue(combo.getFieldValue('Numerator_PostNum'));
								base_form.findField('Numerator_Ser').getEl().dom.setAttribute('readOnly', true);

								var NumeratorObject_SysName = "";
								switch (base_form.findField('SvidType').getValue()) {
									case 'birth':
										NumeratorObject_SysName = 'BirthSvid';
										break;
									case 'death':
										NumeratorObject_SysName = 'DeathSvid';
										break;
									case 'pntdeath':
										NumeratorObject_SysName = 'PntDeathSvid';
										break;
								}

								win.getLoadMask(lang['poluchenie_nachalnogo_nomera']).show();
								Ext.Ajax.request({
									params: {
										Numerator_id: newValue,
										NumeratorObject_SysName: NumeratorObject_SysName,
										showOnly: 1
									},
									url: '/?c=Numerator&m=getNumeratorNum',
									callback: function (options, success, response) {
										win.getLoadMask().hide();

										var response_obj = Ext.util.JSON.decode(response.responseText);
										base_form.findField('Numerator_IntNum').setValue(response_obj.Numerator_IntNum);
										win.generateMedSvidNumSample();
									}
								});
							}
						}

						win.generateMedSvidNumSample();
					}
				},
				store: new Ext.data.JsonStore({
					autoLoad: false,
					url: '/?c=Numerator&m=getActiveNumeratorList',
					fields: [
						{ name: 'Numerator_id', type: 'int' },
						{ name: 'Numerator_Name', type: 'string' },
						{ name: 'Numerator_Num', type: 'int' },
						{ name: 'Numerator_Ser', type: 'string' },
						{ name: 'Numerator_NumLen', type: 'int' },
						{ name: 'Numerator_PreNum', type: 'string' },
						{ name: 'Numerator_PostNum', type: 'string' }
					],
					key: 'Numerator_id'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{Numerator_Name}&nbsp;',
					'</div></tpl>'
				),
				triggerAction: 'all',
				valueField: 'Numerator_id',
				displayField: 'Numerator_Name',
				width:  300,
				xtype: 'combo'
			}, {
				layout : 'column',
				items : [{
					layout: 'form',
					columnWidth: 0.7,
					items: [{
						name: 'Numerator_Ser',
						listeners: {
							'keyup': function(inp, e) {
								win.generateMedSvidNumSample();
							}
						},
						enableKeyEvents: true,
						fieldLabel: lang['seriya'],
						xtype: 'textfield',
						anchor: '-10'
					}, {
						name: 'Numerator_IntNum',
						listeners: {
							'keyup': function(inp, e) {
								win.generateMedSvidNumSample();
							}
						},
						enableKeyEvents: true,
						minValue: 1,
						allowBlank: false,
						allowDecimals: false,
						allowLeadingZeroes: true,
						allowNegative: false,
						fieldLabel: lang['nomer_pervogo_blanka'],
						xtype: 'numberfield',
						anchor: '-10'
					}, {
						name: 'BlankCount',
						minValue: 1,
						allowBlank: false,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: lang['kolichestvo_blankov'],
						xtype: 'textfield',
						anchor: '-10'
					}]
				}, {
					layout: 'form',
					columnWidth: 0.3,
					items: [{
						title: lang['nomer_blanka'],
						autoHeight: true,
						xtype: 'fieldset',
						items: [{
							xtype: 'label',
							id: 'MedSvidNumSample',
							html: ''
						}]
					}]
				}]
			}, {
				name: 'DoubleSide',
				fieldLabel: lang['dvustoronnyaya_pechat'],
				xtype: 'checkbox',
				width: 180
			}]
		});

    	Ext.apply(this, {
			items : [
				win.FormPanel
			],
			buttons : [{
				text : lang['vyibrat'],
				iconCls : 'ok16',
				handler : function(button, event) {
					win.doPrint();
				}
			}, {
				text: '-'
			}, {
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			buttonAlign : "right"
		});

		sw.Promed.swMedSvidSelectSvidType.superclass.initComponent.apply(this, arguments);
	}
});