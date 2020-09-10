/* 
 * Форма экспорта журнала в DBF из АРМ Секретаря службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */


Ext.define('common.BSME.ForenPers.SecretaryWP.tools.swExportJournalWindow', {
	extend: 'Ext.window.Window',
	autoShow: true,
	modal: true,
	width: '50%',
	//height: '80%',
	refId: 'forenpersexportjournalwnd',
	closable: true,
	title: 'Экспорт журнала в DBF',
	id: 'ForenPersExportJournalWindow',
	border: false,
	layout: {
		align: 'stretch',
		type: 'vbox'
	},
	callback: Ext.emptyFn,
	MedService_id: null,
	JournalType: null,

	initComponent: function() {
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
			items: [
				{
					id: me.id+'_datePickerRange',
					xtype: 'datePickerRange',
					name: 'exportDateRange',
					dateFields: ['dateFinish', 'dateStart'],
					dateFrom: Ext.Date.add(new Date(), Ext.Date.MONTH, -1)
				},
				{
					id: me.id+'_TextPanel',
					padding: '10 0 20 0',
					html: ''
				}
			]
		});

		Ext.apply(me,{
			items: [
				this.BaseForm
			],
			buttons: [{
				xtype: 'button',
				text: 'Сформировать',
				handler: function(btn,evnt) {
					var params = {},
						BaseForm = me.BaseForm.getForm(),
						TextPanel = Ext.getCmp(me.id+'_TextPanel');

					params.MedService_id = me.MedService_id;
					params.JournalType = me.JournalType;
					params.begDate = Ext.Date.format(BaseForm.findField('exportDateRange').dateFrom, 'd.m.Y');
					params.endDate = Ext.Date.format(BaseForm.findField('exportDateRange').dateTo, 'd.m.Y');

					TextPanel.getEl().dom.innerHTML = '';

					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт формирование файла..."});
					loadMask.show();
					BaseForm.submit({
						params: params,
						url: '/?c=BSME&m=exportJournalRequestToDbf',
						success: function(form,action) {
							loadMask.hide();
							if (action.result.Link) {
								TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="'+action.result.Link+'">Скачать и сохранить файл</a>';
							}
						},
						failure: function(form,action) {
							loadMask.hide();
							switch (action.failureType) {
								case Ext.form.action.Action.CLIENT_INVALID:
									Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
									break;
								case Ext.form.action.Action.CONNECT_FAILURE:
									Ext.Msg.alert('Ошибка', 'Ошибка соединения с сервером');
									break;
								case Ext.form.action.Action.SERVER_INVALID:
									Ext.Msg.alert('Ошибка', action.result.Error_Msg);
							}
						}
					});
				}.bind(this)
			}]
		});

		me.callParent(arguments);
	},
	listeners: {
		show: function(wnd,eOpts) {
			var BaseForm = this.BaseForm.getForm();
			if (this.dateFrom && this.dateTo) {
				BaseForm.findField('exportDateRange').setValue(this.dateFrom+' _ '+this.dateTo);
			}
		},
		close: function(wnd) {
			if (typeof wnd.callback == 'function') {
				wnd.callback();
			}
		}
	},

	show: function() {
		this.callParent();
		this.center();
	}
})