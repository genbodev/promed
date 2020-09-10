/**
* swReagentNormRateEditWindow - окно просмотра, добавления и редактирования нормы расхода реактива
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package	  common
* @access	   public
* @author	   Arslanov Azat
*/

sw.Promed.swReagentNormRateEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swReagentNormRateEditWindow',
	objectSrc: '/jscore/Forms/Common/swReagentNormRateEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	title: 'Реактив, норма расхода',
	draggable: true,
	id: 'swReagentNormRateEditWindow',
	width: 800,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,
	doSave: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}
		win.getLoadMask('Подождите, сохраняется запись...').show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();				
				win.action = 'edit';
				var id = action.result.ReagentNormRate_id;
				form.findField('ReagentNormRate_id').setValue(id);
				win.owner.refreshRecords(win.owner, id);
				win.hide();
			}
		});
	},
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north',
			//id: '',
			items: [{
				layout: 'form',
				xtype: 'fieldset',
				title: 'Реактив',
				id: 'ReagentNormRatePanel',
				height: 100,
				border: true,
				items: 
				[{
						layout: 'form',
						border: false,
						items: 
						[{
							name: 'ReagentNormRate_id',
							xtype: 'hidden'
						}, {
							xtype: 'swdrugsimplecombo',
							fieldLabel : 'Реактив',
							width: 600,
							listWidth: 600,
							hiddenName: 'DrugNomen_id',
							value: '',
							allowBlank: false,
							triggerAction: 'all',
							trigger2Class: 'hideTrigger',
							//hideTrigger: true,
							listeners: {
								'render': function() {
									this.getStore().proxy.conn.url = '/?c=ReagentNormRate&m=loadReagentList';
								}
							}
						}]
				}, {
					layout: 'column',
					height: 90,
					width: 800,
					items: [
						{
							layout: 'form',
							border: false,
							columnWidth: .5,
							items: 
							[{
								fieldLabel: 'Норма расхода',
								minValue: 0,
								name: 'ReagentNormRate_RateValue',
								width: 200,
								xtype: 'numberfield',
								//allowBlank:false,
							}]
						}, {
							layout: 'form',
							border: false,
							columnWidth: .5,
							items: 
							[{
								fieldLabel: 'Единицы измерения',
								hiddenName: 'unit_id',
								xtype: 'swcommonsprcombo',
								editable: true,
								prefix:'lis_',
								//allowBlank:false,
								sortField:'Unit_Name',
								comboSubject: 'unit',
								width: 200
							}]
						}
					]

				}]
				
			}, {
				width: 400,
				comboSubject:'RefMaterial',
				allowBlank: true,
				editable: true,
				fieldLabel:lang['biomaterial'],
				hiddenName:'RefMaterial_id',
				xtype:'swcommonsprcombo'
			}, {
				name: 'UslugaComplex_Code',
				xtype: 'hidden'
			}, {
				name: 'AnalyzerModel_id',
				xtype: 'hidden'
			}],
			reader: new Ext.data.JsonReader({
				success: function() { 
					//
				}
			}, 
			[
				{ name: 'UslugaComplex_Code' },
				{ name: 'DrugNomen_id' },
				{ name: 'ReagentNormRate_RateValue' },
				{ name: 'RefMaterial_id' },
				{ name: 'unit_id' },
				{ name: 'AnalyzerModel_id' }
			]),
			timeout: 600,
			url: '/?c=ReagentNormRate&m=saveReagentNormRate'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_GL + 29,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onTabElement: 'GREW_Marker_Word',
				tabIndex: TABINDEX_GL + 31,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swReagentNormRateEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function(params) {
		//console.log('params:'); console.log(params);
		sw.Promed.swReagentNormRateEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;	

		this.AnalyzerModel_id = arguments[0].AnalyzerModel_id || null;
		this.UslugaComplex_Code = arguments[0].UslugaComplex_Code || null;
		this.ReagentNormRate_id = arguments[0].ReagentNormRate_id || null;
		//this.center();

		var win = this,
			base_form = this.formPanel.getForm(); 

		base_form.reset();
		base_form.findField('ReagentNormRate_id').setValue(this.ReagentNormRate_id);
		base_form.findField('AnalyzerModel_id').setValue(this.AnalyzerModel_id);
		base_form.findField('UslugaComplex_Code').setValue(this.UslugaComplex_Code);
		//console.log('UslugaComplex_Code:' + base_form.findField('UslugaComplex_Code').getStringValue());
		
		switch (this.action) {
			case 'view':
				this.setTitle('Реактив: Просмотр');
				break;
			case 'edit':
				this.setTitle('Реактив: Редактирование');
				break;
			case 'add':
				this.setTitle('Реактив: Добавление');
				break;
			break;
		}

		if (this.action == 'add') {
			win.enableEdit(true);
			this.syncSize();
			this.doLayout();
		} else {
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() { win.hide(); } );
				},
				params: {
					ReagentNormRate_id: base_form.findField('ReagentNormRate_id').getValue()
				},
				success: function(form, action) {					
					//console.log('action.result.ReagentNormRate_RateValue:' + action.result.toString());
					//for (key in action.result) { console.log(action.result[key]); }
					win.getLoadMask().hide();
					if(win.action == 'edit') {
						win.enableEdit(true);
					}
				},
				url: '/?c=ReagentNormRate&m=loadReagentNormRate'
			});
		}
	}
});
