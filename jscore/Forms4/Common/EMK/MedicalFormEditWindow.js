Ext6.define('common.EMK.MedicalFormEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swMedicalFormEditWindow',
	cls: 'arm-window-new AnketaMaker',
	title: 'Анкетирование',
	autoShow: false,
	constrain: true,
	modal: true,
	layout: 'fit',
	width: 800,
	height: 550,
	autoScroll: true,
	show: function(data) {
		var me = this;
		me.data = data;

		Ext6.Ajax.request({
			url: '/?c=MedicalForm&m=getMedicalForm',
			params: data,
			callback: function(opt, success, response){
				if (success && response && response.responseText) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if (response_obj.success) {
						me.down('#questionTitle').setTitle(response_obj.data.MedicalForm_Name);
						me.down('#questionTitle').setHtml(response_obj.data.MedicalForm_Description);
					}
				}
			}
		});


		me.store.load({
			params:data,
			callback: function(records) {
				me.refreshPanel();

				if(data.action === 'view'){
					me.storeData.load({
						params: {
							MedicalFormPerson_id: data.MedicalFormPerson_id
						},
						callback: function(records, s, r){
							records.forEach(function (rec) {
								var formQuestion = me.down('[MedicalFormQuestion_id ='+rec.get('MedicalFormQuestion_id')+']'),
									value;
								if(rec.get('MedicalFormAnswers_id')){
									if(formQuestion.allowMultiple){
										var checkValue = formQuestion.getValue();
										value = Array(rec.get('MedicalFormAnswers_id')).concat(checkValue)
									}else{
										value = rec.get('MedicalFormAnswers_id')
									}
								}else if(rec.get('MedicalFormData_ValueText')){
									value = rec.get('MedicalFormData_ValueText')
								}else if(rec.get('MedicalFormData_ValueDT')){
									value = new Date(rec.get('MedicalFormData_ValueDT').date)
								}

								formQuestion.setValue(value)
							})
						}
					});
				}
			}
		});
		me.callParent(arguments);
	},

	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.TreeStore',{
			root: {
				leaf: false,
				expanded: false
			},
			autoLoad: false,
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=MedicalForm&m=loadMedicalForm',
				reader: {
					type: 'json'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		});

		me.storeData = Ext6.create('Ext6.data.Store',{
			autoLoad: false,
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=MedicalForm&m=loadMedicalFormData',
				reader: {
					type: 'json'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		});

		this.rightPanel =  Ext6.create('Ext6.panel.Panel', {
			border: false,
			header: false,
			layout: 'fit',
			cls: 'AnketaMaker-constructor-region',
			region: 'center',
			minWidth: 540,
			height: '100%',
			padding: '0 0 0 0',
			//	maxWidth: '50%',
			flex: 1,
			items: [
				{
					xtype: 'panel',
					height: '100%',
					border: false,
					scrollable: true,
					cls: 'custom-scroll',
					layout: 'vbox',
					preventHeader: true,
					items:[
						{
							xtype: 'panel',
							//region: 'west',
							itemId:'worksheetTplHeader',
							width: '100%',
							//flex: 1,
							autoHeight: true,
							border: false,
							scrollable: false,
							cls: 'right-header-title',
							title: 'Вопросы о здоровье',
							html: 'Опрос анонимный, пожалуйста, отвечайте максимально честно'
						},
						{
							xtype: 'panel',
							itemId:'worksheetTpl',
							width: '100%',
							autoHeight: true,
							border: false,
							scrollable: false,
							preventHeader: true,
							//padding: '10 0 20 0',
							cls: 'right-preview'
						}
					]
				}
			]
		});


		me.panel =  Ext6.create('Ext6.panel.Panel', {
			border: false,
			header: false,
			layout: 'fit',
			region: 'center',
			padding: '0 0 0 0',
			//width: '100%',
			height: '100%',
			//flex: 1,
			items: [
				{
					xtype: 'panel',
					layout: 'vbox',
					//flex: 1,
					//height: '100%',
					border: false,
					scrollable: true,
					cls: 'custom-scroll',
					preventHeader: true,
					items:[
						{
							xtype: 'panel',
							itemId:'questionTitle',
							width: '100%',
							autoHeight: true,
							border: false,
							scrollable: false,
							cls: 'right-header-title'
						},
						{
							xtype: 'panel',
							itemId:'questionPanel',
							width: '100%',
							autoHeight: true,
							border: false,
							scrollable: false,
							preventHeader: true,
							padding: '10 0 20 0',
							cls: 'right-preview'
						}
					]
				}
			],
			bbar: {	padding: '7 40 7 40',

				items: [
					'->',
					{
						xtype: 'button',
						userCls: 'AnketaMaker-cancel-btn',
						margin: '0 0 0 10',
						text: 'Отмена',
						handler: function () {
							me.close()
						}
					},
					{
						xtype: 'button',
						userCls: 'AnketaMaker-save-btn',
						margin: 0,
						text: 'Сохранить',
						handler: function () {
							var questions = me.down('#questionPanel'),
								data = [];
							questions.items.getRange().forEach(function (item) {
								if(item.items && item.items.getCount()>0) {
									var field = item.items.getAt(0),
										value = field.getValue();

									if(value && value.length){
										data.push({
											'value': value,
											'MedicalFormQuestion_id' :field.MedicalFormQuestion_id,
											'xtype': field.xtype
										})
									}
								}
							});
							
							if(data.length>0){
								Ext6.Ajax.request({
									url: '/?c=MedicalForm&m=saveMedicalFormData',
									params: {
										MedicalFormData: JSON.stringify(data),
										Person_id: me.data.Person_id ,
										MedicalForm_id: me.data.MedicalForm_id
									},
									callback: function (res) {
										Ext6.data.StoreManager.lookup('loadPersonOnkoProfileList').reload();
										me.close();
									}
								});
							}else{
								Ext6.Msg.alert('Ошибка', 'Заполните форму');
							}

						}
					}
				]
			},
		});

		Ext6.apply(me, {
			items: [
				me.panel
			]
		});

		me.callParent(arguments);
	},
	childrenFormAnswer: function(el){
		var me = this,
			isReadOnly = me.data.action=='view';
		
		if(el.AnswerType_id == 11){
			return Ext6.create('Ext6.previewDateOrTime', {
				MedicalFormQuestion_id: el.MedicalFormQuestion_id,
				margin: '0 0 5 0',
				readOnly: isReadOnly
			});
		}
		else if(el.AnswerType_id == 2){
			return Ext6.create('Ext.form.field.TextArea', {
				MedicalFormQuestion_id: el.MedicalFormQuestion_id,
				margin: '0 0 5 0',
				width:'100%',
				disabled: isReadOnly,
				readOnly: isReadOnly
			});
		}
		else{
			return Ext6.create('Ext6.button.Segmented', {
				MedicalFormQuestion_id: el.MedicalFormQuestion_id,
				allowMultiple: el.AnswerType_id == 10,
				cls: 'segmentedButtonGroup segmentedButtonGroupMini possible-answer',
				items: arrAnswerPrevFn(el.children),
				style: {
					paddingRight: '3px',
					paddingTop: '10px'
				},
				disabled: isReadOnly,
				readOnly: isReadOnly
			});
		}

		function arrAnswerPrevFn(arrAnswerPrev) {
			var objRow = [];
			if(arrAnswerPrev){
				arrAnswerPrev.forEach(function (el, index) {
					var pul = {},
						styleButtonGroup = {display: 'inline-block'};

					pul['text'] = el.MedicalFormAnswers_Name;
					pul['value'] = el.MedicalFormAnswers_id;
					pul['typeQuestion'] = el.typeQuestion;
					pul['style'] = styleButtonGroup;
					objRow.push(pul);
				});
			}

			return objRow;
		}
	},

	refreshPanel: function(){
		var me = this,
			questionPanel = me.down('#questionPanel'),
			records = me.store.root.serialize().children;

		questionPanel.removeAll();

		if(records){
			records.forEach(function (el,index) {
				var questionPreview = Ext6.create('Ext6.panel.Panel', {
					cls: 'AnketaMaker-questionPreviewSegment AnketaMaker-one',
					padding: '0 40 0 35',
					margin: 0,
					style: {
						borderColor: '#E7E7E7',
						borderLeftColor: '#FFFFFF',
						borderWidth: '0px 0px 1px 5px',
						borderStyle: 'solid'
					},
					title: el.MedicalFormQuestion_Name,
					items: [
						me.childrenFormAnswer(el)
					]
				});
				questionPanel.add(questionPreview);
			});
		}
	}
});