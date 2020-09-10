/* 
 * Форма создания поручения для заявки
 */


Ext.define('common.BSME.DefaultWP.DefaultDprtHeadWP.tools.swCreateEvnDirectionForensicWindow', {
	extend: 'Ext.window.Window',
	modal: true,
	width: '600px',
	//height: '360px',
	refId: 'createevndirectionforensicwnd',
	closable: true,
	title: 'Поручение',
	id: 'CreateEvnDirectionForensicWindow',
	border: false,
	layout: {
		align: 'stretch',
		type: 'vbox'
	},
	
	showEvnDirectionForensicPrintWindow: function(forId){
		Ext.Ajax.request({
			url: '/?c=BSME&m=printEvnDirectionForensic',
			params: {
				EvnForensic_id: forId,
				armName: sw.Promed.MedStaffFactByUser.last.ARMName
			},
			callback: function(opt, success, response){
				if ( !success ) {
					Ext.Msg.alert('Ошибка','Во время загрузки печатной формы произошла непредвиденная ошибка.');
					return;
				}
				var win = window.open();
				win.document.write(response.responseText);
				win.print();
			}
		});
	},
	
	initComponent: function(){
		var me = this;

		this.BaseForm = Ext.create('sw.BaseForm',{
			xtype:'BaseForm',
			cls: 'mainFormNeptune',
			autoScroll: true,
			id: this.id+'_BaseForm',
			flex: 1,
			width: '100%',
			height: '100%',
			layout: {
				padding: '0 0 0 0', // [top, right, bottom, left]
				align: 'stretch',
				type: 'vbox'
			},
			listeners: {
				success: function(obj,form,action){
					var evnForensic_id = me.BaseForm.getForm().findField('EvnForensic_id').getValue();
					Ext.MessageBox.confirm('Сообщение', 'Поручение успешно сохранено. Вывести поручение на печать?', function(btn){
						if ( btn !== 'yes' ) {
							return;
						}

						me.showEvnDirectionForensicPrintWindow(evnForensic_id);
					});
					me.destroy();
				}
			},
			items: [{
				xtype: 'container',
				bodyPadding: 10,
				autoHeight: true,
				width: '80%',
				layout: {
					padding: '0 10 0 0', // [top, right, bottom, left]
					align: 'stretch',
					type: 'vbox'
				},
				defaults: {
					labelAlign: 'rightувеличить в размере окно создания поручений + при первоначальном запуске арма все кнопки должны быть disabledувеличить в размере окно создания поручений + при первоначальном запуске арма все кнопки должны быть disabled',
					labelWidth: 200,
					left: 0,
				},
				items: [{
					fieldLabel: 'ID заявки',
					xtype: 'hidden',
					name: 'EvnForensic_id'
				}, {
					xtype: 'textfield',
					fieldLabel: 'Номер поручения',
					readOnly: true,
					name: 'EvnDirectionForensic_Num',
					allowBlank: false
				}, {
					xtype: 'swdatefield',
					fieldLabel: 'Дата начала экспертизы',
					name: 'EvnDirectionForensic_begDate',
					allowBlank: false
				}, {
					xtype: 'swdatefield',
					fieldLabel: 'Дата окончания экспертизы',
					name: 'EvnDirectionForensic_endDate',
					allowBlank: false
				}, {
					name: 'MedPersonal_id',
					fieldLabel: 'ФИО эксперта',
					xtype: 'swmedpersonalcombo',
					allowBlank: false
				}, {
					xtype: 'combobox',
					fieldLabel: 'Тип экспертизы',
					anchor: '-10',
					name: 'EvnForensicType_id',
					valueField: 'EvnForensicType_id',
					displayField: 'EvnForensicType_Name',
					codeField: 'EvnForensicType_Code',
					editable: false,
//					value: 1,
					// @todo Заменить загрузкой данных из /?c=BSME&m=loadEvnForensicTypeList
					store: new Ext.data.Store({
						autoLoad: false,
						queryMode: 'local',
						fields: [
							{name: 'EvnForensicType_id', type: 'int'},
							{name: 'EvnForensicType_Name', type: 'string'},
							{name: 'EvnForensicType_Code', type: 'int'}
						],
						data: [
							{EvnForensicType_id: 1, EvnForensicType_Name: 'Биологическое', EvnForensicType_Code: '1'},
							{EvnForensicType_id: 3, EvnForensicType_Name: 'Цитологическое', EvnForensicType_Code: '2'},
							{EvnForensicType_id: 4, EvnForensicType_Name: 'Молекулярно-генетическое', EvnForensicType_Code: '3'}
						]
					}),
					allowBlank: false
				}, {
					name: 'EvnDirectionForensic_Goal',
					allowBlank:false,
					xtype: 'textareafield',
					plugins: [new Ux.Translit(true)],
					minHeight: 10,
					fieldLabel: 'Цель экспертизы',
					allowBlank: false
				}]
			}]
		});
		// Событие при успешном сохранении формы
		this.BaseForm.addEvents('success');

		Ext.apply(me,{
			items: [
				this.BaseForm
			],
			buttons: [{
				xtype: 'button',
				text: 'Готово',
				handler: function(btn,evnt) {
					var params = {};

					if (!this.BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					
					this.BaseForm.submit({
						waitMsg: 'Идет сохранение формы...',
						params : params,
						url: '/?c=EvnDirectionForensic&m=saveEvnDirectionForensic',
						success: function(form, action){
							me.BaseForm.fireEvent('success', this, form, action );
						},
						failure: function(form, action) {
							switch (action.failureType) {
								case Ext.form.action.Action.CLIENT_INVALID:
									Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
									break;
								case Ext.form.action.Action.CONNECT_FAILURE:
									Ext.Msg.alert('Ошибка', 'Ошибка соединения с сервером');
									break;
								case Ext.form.action.Action.SERVER_INVALID:
									Ext.Msg.alert('Ошибка', action.result.Error_Msg);
									break;
							}
						}
					});
				}.bind(this)
			}]
		})

		me.callParent(arguments);
	},
	
	setBaseFormEvnForensicId: function( EvnForensic_id ){
		//this.BaseForm.getForm().setValues({EvnForensic_id: EvnForensic_id});
		this.BaseForm.getForm().findField('EvnForensic_id').setValue(EvnForensic_id);
	},
	
	show: function(){
		this.callParent();
		
		var BaseForm = this.BaseForm.getForm();
		
		var EvnForensic_id = BaseForm.findField("EvnForensic_id").value;
		if ( typeof EvnForensic_id == 'undefined' || !EvnForensic_id ) {
			Ext.Msg.alert('Ошибка открытия формы', 'Не передан идентификатор заявки для которой необходимо создать поручение.');
			this.destroy();
			return false;
		}

		// Врачи той же службы
		BaseForm.findField('MedPersonal_id').getStore().load({
			params: {
				MedService_id: getGlobalOptions().CurMedService_id
			}
		});

		Ext.Ajax.request({
			url: '/?c=EvnDirectionForensic&m=getNextNumber',
			success: function(response) {
				var response_obj = Ext.JSON.decode(response.responseText);
				if (response_obj.EvnDirectionForensic_Num) {
					BaseForm.findField('EvnDirectionForensic_Num').setValue(response_obj.EvnDirectionForensic_Num);
				} else {
					Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');
				}
			},
			failure: function() {
				Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');
			}
		});
		
		//нет такого поля
		//BaseForm.findField('EvnDirectionForensic_setDT').setValue(getGlobalOptions().date)
	}
})