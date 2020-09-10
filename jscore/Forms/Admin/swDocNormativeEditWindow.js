/**
 * swDocNormativeEditWindow - окно редактирования нормативного документа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.02.2016
 */

/*NO PARSE JSON*/

sw.Promed.swDocNormativeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDocNormativeEditWindow',
	width: 600,
	minWidth: 600,
	autoHeight: true,
	modal: true,

	addFileToFilesPanel: function(text, link, fieldName, deletable) {
		var base_form = this.FormPanel.getForm();
		var FilesPanel = this.findById('DNEW_FilesPanel');

		var FileEl = new Ext.Panel({
			layout: 'column',
			bodyStyle: 'margin-bottom: 5px;',
			items: [{
				layout: 'form',
				items: [{
					style: 'font: 12px;',
					html: '<a target="_blank" href="'+link+'">'+text+'</a>'
				}]
			}, {
				layout: 'form',
				items: [{
					id: 'DNEW_Delete_'+fieldName,
					style: 'margin-left: 10px;',
					hidden: !deletable,
					xtype: 'button',
					iconCls: 'delete16',
					handler: function() {
						FilesPanel.remove(FileEl.id);
						base_form.findField(fieldName).setValue(null);
						if (Ext.isEmpty(base_form.findField('DocNormative_File').getValue())) {
							Ext.getCmp('DNEW_AddFileBtn').show();
						}
						FilesPanel.doLayout();
						this.syncShadow();
					}.createDelegate(this)
				}]
			}]
		});

		FilesPanel.add(FileEl);
		FilesPanel.doLayout();
	},

	openDocNormativeFileUploadWindow: function() {
		var wnd = this;
		var base_form = this.FormPanel.getForm();

		var params = {needType: false};

		params.callback = function(data) {
			if (data.DocNormativeData && data.DocNormativeData.DocNormative_File) {
				base_form.findField('DocNormative_File').setValue(data.DocNormativeData.DocNormative_File);
				var arr = data.DocNormativeData.DocNormative_File.split('/');
				var filename = arr[arr.length-1];
				wnd.addFileToFilesPanel(filename, data.DocNormativeData.DocNormative_File, 'DocNormative_File', true);
			}
			if (!Ext.isEmpty(base_form.findField('DocNormative_File').getValue())) {
				Ext.getCmp('DNEW_AddFileBtn').hide();
			}
		};

		getWnd('swDocNormativeFileUploadWindow').show(params);
	},

	doSave: function() {
		var wnd = this;

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action)
			{
				loadMask.hide();

			}.createDelegate(this),
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result){
					if (action.result.DocNormative_id){
						base_form.findField('DocNormative_id').setValue(action.result.DocNormative_id);

						this.callback();
						this.hide();
					}
				}
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swDocNormativeEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.findById('DNEW_FilesPanel').removeAll();
		this.findById('DNEW_AddFileBtn').show();
		this.syncShadow();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		base_form.items.each(function(f){f.validate()});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Нормативный документ: Добавление');
				this.enableEdit(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Нормативный документ: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Нормативный документ: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						DocNormative_id: base_form.findField('DocNormative_id').getValue()
					},
					url: '/?c=DocNormative&m=loadDocNormativeForm',
					success: function() {
						loadMask.hide();

						var file = base_form.findField('DocNormative_File').getValue();
						if (!Ext.isEmpty(file)) {
							var arr = file.split('/');
							var filename = arr[arr.length-1];
							this.addFileToFilesPanel(filename, file, 'DocNormative_File', (this.action=='edit'));
							Ext.getCmp('DNEW_AddFileBtn').hide();
						}

					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'DNEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 100,
			url: '/?c=DocNormative&m=saveDocNormative',
			items: [{
				xtype: 'hidden',
				name: 'DocNormative_id'
			}, {
				xtype: 'hidden',
				name: 'DocNormative_File'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'DocNormative_Num',
				fieldLabel: 'Номер',
				width: 440
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'DocNormative_Name',
				fieldLabel: 'Наименование',
				width: 440
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'DocNormative_Editor',
				fieldLabel: 'Издатель',
				width: 440
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'DocNormativeType',
				hiddenName: 'DocNormativeType_id',
				fieldLabel: 'Тип документа',
				width: 440
			}, {
				layout: 'column',
				width: 440,
				items: [{
					layout: 'form',
					items: [{
						allowBlank: false,
						xtype: 'swdatefield',
						name: 'DocNormative_begDate',
						fieldLabel: 'Начало'
					}]
				}, {
					layout: 'form',
					labelWidth: 78,
					items: [{
						xtype: 'swdatefield',
						name: 'DocNormative_endDate',
						fieldLabel: 'Окончание'
					}]
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: 'Прикрепленный файл',
				items: [{
					xtype: 'panel',
					fieldWidth: 135,
					id: 'DNEW_FilesPanel'
				}, {
					xtype: 'button',
					//style: 'margin-bottom: 10px;',
					id: 'DNEW_AddFileBtn',
					iconCls: 'add16',
					text: 'Добавить файл',
					handler: function(){
						this.openDocNormativeFileUploadWindow();
					}.createDelegate(this)
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'DocNormative_id'},
				{name: 'DocNormative_File'},
				{name: 'DocNormative_Num'},
				{name: 'DocNormative_Name'},
				{name: 'DocNormative_Editor'},
				{name: 'DocNormative_begDate'},
				{name: 'DocNormative_endDate'},
				{name: 'DocNormativeType_id'}
			])
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'DNEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swDocNormativeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});