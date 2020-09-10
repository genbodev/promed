Ext6.define('common.Worksheet.worksheetConstructor', {
	extend: 'base.BaseForm',
//	closeAction: 'destroy',
	alias: 'widget.worksheetConstructor',
	autoShow: false,
	maximized: true,
	cls: 'arm-window-new AnketaMaker',
	title: 'Конструктор форм',
	constrain: true,
	layout: 'border',
	header: true,
	//autoHeight: true,
	//itemId: 'common',
	/*extend: 'Ext6.window.Window',

	itemId: 'common',
	callback: Ext6.emptyFn,
	isLoading: false,
	closable: false,
	listeners: {
		'hide': 'onHide'
	},
	//alias: 'swAutoSelectDateTimeWindow',
	autoShow: true,
	cls: 'arm-window-new AnketaMaker',
	constrain: true,
	scrollable: false,
	autoHeight: true,
	findWindow: false,
	resizable: false,
	modal: false,
	layout: 'border',
	renderTo: Ext6.getBody(),
	selectedColumn: null,
	selectedDescriptionRow: null,
	selectedCellsCount: null,
*/
	show: function(data){
		var me = this;

		Ext6.Ajax.request({
			url: '/?c=MedicalForm&m=getMedicalForm',
			params: data,
			callback: function(opt, success, response){
				if (success && response && response.responseText) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if(response_obj.success) {
						me.down('#MedicalForm_Name').setValue(response_obj.data.MedicalForm_Name);
						me.down('#MedicalForm_Description').setValue(response_obj.data.MedicalForm_Description);
					}
				}
			}
		});

		me.data = data;

		me.TreeStore.load({
			params:data,
			callback: function (store, records, successful) {

				me.refreshLeftPanel();
				me.refreshRightPanel();
				me.selectLeftPanel(0)
			}
		});

		this.callParent(arguments);
	},

	close:function(){
		Ext6.data.StoreManager.lookup('allMedicalForms').reload();
		this.callParent(arguments);
	},
	initComponent: function () {
		var me = this;

		this.previewWorksheetNavigation = Ext6.create('Ext6.panel.Panel', {
			border: false,
			header: false,
			autoHeight: true,
			scrollable: true,
			preventHeader: true,
			cls: 'previewWorksheetNavigation',
			items: [

			]
		});

		this.TreeStore = Ext6.create('Ext6.data.TreeStore',{
			root: {
				leaf: false,
				expanded: false
			},
			fields: [{
				name:'AnswerType_id'
			},{
				name: 'typeQuestion',
				calculate: function (data){
					switch (parseInt(data.AnswerType_id)) {
						case 2:
							return 'textfield';
						case 9:
							return 'radioButton';
						case 10:
							return  'checkbox';
						case 11:
							return  'datetime';
					}
				}
			}],
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

		this.questionPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			header: false,
			padding: '36 0 40 0',
			flex: 1,
			autoHeight: true,
			scrollable: false,
			preventHeader: true,
			cls: 'questionPanel'
		});

		this.leftPanel =  Ext6.create('Ext6.panel.Panel', {
			header: false,
			//~ layout: 'hbox',
			layout: {
				type: 'absolute'
			},
			cls: 'AnketaMaker-constructor-region AnketaMaker-leftPanel',
			region: 'west',
			minWidth: 540,
			maxWidth: '50%',
			height: '100%',
			flex: 1,
			style: {
				borderWidth: '0px 1px 0px 0px'
			},
			items: [
				{
					anchor: '100% 100%',
					xtype: 'form',
					tabPosition: 'left',
					userCls: 'tabPanelConstructor',
					cls: 'custom-scroll',
					border: false,
					scrollable: true,
					tabRotation: 0,
					lbar: {
						width: 60,
						style: {
							paddingTop: '20px',
							backgroundColor: '#FFFFFF'
						},
						defaults: {
							scrollable: true
						},
						items: [
							me.previewWorksheetNavigation
						]
					},
					items: [
						this.questionPanel
					]
				},
				{
					xtype: 'button',
					itemId: 'addAbsButton',
					iconCls: 'addQuestionAbsolute-icon',
					cls: 'addQuestionAbsolute',
					style:{
						right: '20px',
						bottom: '20px',
						'z-index': '100500'
					},
					handler: function () {
						me.TreeStore.root.appendChild({
							MedicalForm_id: me.data.MedicalForm_id,
							AnswerType_id: 9,
							typeQuestion: 'radioButton',
							type: 'Question'
						});

						me.refreshLeftPanel();
						me.refreshRightPanel();
						me.selectLeftPanel(me.TreeStore.root.childNodes.length-1)
					}
				}
			]
		});
/* не используется? :
		this.previewWorksheet = Ext6.create('Ext6.panel.Panel', {
			border: false,
			header: false,
			flex: 1,
			autoHeight: true,
			scrollable: false,
			preventHeader: true,
			cls: 'AnketMaker-preview'
		});

		this.previewSegmentWorksheet = Ext6.create('Ext6.panel.Panel', {
			border: false,
			header: false,
			flex: 1,
			autoHeight: true,
			scrollable: false,
			preventHeader: true,
			cls: 'AnketaMaker-previewSegmentWorksheet'
		});*/

		this.rightPanel =  Ext6.create('Ext6.panel.Panel', {
			border: false,
			header: false,
			layout: 'fit',
			cls: 'AnketaMaker-constructor-region',
			region: 'center',
			minWidth: 540,
			height: '100%',
			padding: 0,
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
							/*items: [
								//this.rightTreePanel
								this.previewWorksheet,
								this.previewSegmentWorksheet
							]*/
						}
					]
				}
			]
		});

		this.inputConstructorName = Ext6.create('Ext6.form.Panel', {
			border: false,
			items: [{
				itemId: 'MedicalForm_Name',
				xtype: 'textfield',
				type: 'inputConstructor-tbar-name',
				margin: '0 0 0 50',
				height: 27,
				userCls: 'inputConstructor-tbar-name',
				grow: true,
				growMin: 160,
				tooltip: 'Переименовать',
				emptyText: 'Название анкеты',
				listeners:{
					change:function (cmp,newValue) {
						me.rightPanel.down('#worksheetTplHeader').setTitle(newValue)
					}
				}
			}]
		});

		this.inputConstructorNote = Ext6.create('Ext6.form.Panel', {
			border: false,
			items: [{
				itemId: 'MedicalForm_Description',
				xtype: 'textfield',
				margin: '3 0 0 50',
				height: 20,
				userCls: 'inputConstructor-tbar-note',
				grow: true,
				growMin: 150,
				emptyText: 'Описание',
				listeners:{
					change:function (cmp,newValue) {
						me.rightPanel.down('#worksheetTplHeader').setHtml(newValue)
					}
				}
			}]
		});

		this.constructorPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			header: false,
			layout: 'hbox',
			region: 'center',
			padding: 0,
			width: '100%',
			height: '100%',
			bodyStyle: {
				background: '#EDEDED'
			},
			tbar: {
				padding: 0,
				margin: 0,
				border: false,
				userCls: 'tbarConstructor',
				overflowHandler: 'menu',
				minHeight: 67,
				items: [
					{
						xtype: 'form',
						width: '70%',
						border: false,
						items: [
							this.inputConstructorName,
							this.inputConstructorNote
						]
					},
					{
						xtype: 'form',
						width: '29%',
						border: false,
						padding: '5 0 10 0',
						style: {
							textAlign: 'right'
						},
						items: [
							/*{
								xtype: 'checkbox',
								margin: 0,
								id: 'activateSegment',
								cls: 'activateSegment',
								checked: true,
								boxLabel: 'Сегментированные кнопки',
								style: {
									'padding-left': '6px',
									'padding-right': '20px',
									cursor: 'pointer',
									display: 'inline'
								},
								listeners: {
									change: function( el, newValue, oldValue, eOpts ) {
										var p = Ext6.get('previewWorksheet');
										if (el.checked) {
											Ext6.getCmp('previewWorksheet').setHidden(true);
											Ext6.getCmp('previewSegmentWorksheet').setHidden(false);
										} else {
											Ext6.getCmp('previewWorksheet').setHidden(false);
											Ext6.getCmp('previewSegmentWorksheet').setHidden(true);
										}
									}
								}
							},
							{
								xtype: 'segmentedbutton',
								cls: 'segmentedButtonGroup mobileOrDesktop segmentedButtonGroupMini',
								margin: '0 10 0 0',
								allowMultiple: true,
								style: {
									display: 'inline-block'
								},
								items: [
									{
										text: '',
										itemId: 'card-next',
										iconCls: 'mobileIcon',
										handler: function () {
											if (this.pressed) {
												Ext6.getCmp('previewSegmentWorksheet').addCls('mobilePreview');
												Ext6.getCmp('previewWorksheet').hide();
												Ext6.getCmp('previewSegmentWorksheet').show();
												Ext6.getCmp('activateSegment').setStyle({opacity: '0'});
											} else {
												Ext6.getCmp('previewSegmentWorksheet').removeCls('mobilePreview');
												Ext6.getCmp('previewSegmentWorksheet').show();
												Ext6.getCmp('activateSegment').setStyle({opacity: '1'});
											}
										}
									}

								]
							},*/
							{
								xtype: 'button',
								margin: '0 40 0 0',
								iconCls: 'settingsIcon',
								cls: 'AnketaMaker-settings-btn',
								text: '',
								handler: function () {
									getWnd('accessParamsWorksheet').show(me.data);
								}
							}
						]
					}
				]
			},
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
							Ext6.Ajax.request({
								url: '/?c=MedicalForm&m=updateMedicalForm',
								params: {
									MedicalForm_id: me.data.MedicalForm_id,
									MedicalFormTree: Ext6.util.JSON.encode(me.TreeStore.getRoot().serialize()),
									MedicalForm_Name: me.down('#MedicalForm_Name').getValue(),
									MedicalForm_Description:  me.down('#MedicalForm_Description').getValue()
								},
								callback: function (res) {
									Ext6.Msg.alert("Сообщение", "Анкета успешно сохранена");
								}
							});
						}
					}
					/*{
						xtype: 'button',
						userCls: 'AnketaMaker-save-btn',
						margin: 0,
						text: 'Опубликовать',
						listeners: {

						}
					}*/
				]
			},
			items: [
				this.leftPanel,
				this.rightPanel
			]
		});

		Ext6.apply(this, {
			items: [
				this.constructorPanel
			]
		});
		this.callParent(arguments);
	},

	refreshLeftPanel: function() {
		var me = this,
			updateLayout = me.questionPanel,
			records = me.TreeStore.root.childNodes,
			navigation = me.previewWorksheetNavigation;

		me.previewWorksheetNavigation.removeAll();
		me.questionPanel.removeAll();

		records.forEach(function (el,index) {
			if(el.data.MedicalFormQuestion_deleted === 2){
				return
			}
			var btnWorksheetNavigation = Ext6.create('Ext6.button.Button', {
				xtype: 'button',
				margin: '0 0 3 10',
				iconCls: 'leftTabControl-icon',
				userCls: 'leftTabControl',
				idx: index,
				tooltip: el.data.MedicalFormQuestion_Name,
				width: 30,
				handler: function (btn) {
					me.selectLeftPanel(index)
				}
			});

			navigation.add(btnWorksheetNavigation);


			this.panelVariableAnswer = Ext6.create('Ext6.panel.Panel', {
				cls: 'panelVariableAnswer',
				style: {
					paddingRight: '3px',
					paddingTop: '0px',
					marginLeft: '28px'
				},
				items: getChildrenStore(el)
			});

			var questionPanelTextfield = Ext6.create('Ext.questionPanel', {
				userCls: 'lavel-one',
				maxWidthUpdate: updateLayout.getWidth()-120,
				disableAddAnswerButton: el.data.AnswerType_id && el.data.AnswerType_id.inlist([2,11]),
				typeQuestion: el.data.typeQuestion,
				question: el.data.MedicalFormQuestion_Name,
				//updateLayoutCustom: updateLayout,
				listeners: {
					datachange:function (value) {
						el.data.MedicalFormQuestion_Name = value;
						me.refreshRightPanel();
					},
					typechange:function (type) {
						//Добавить предупрежедние что мы меняем тип
						el.data.AnswerType_id = type.getValue();
						el.data.typeQuestion = type.type;

						if(el.data.AnswerType_id.inlist([2,11]) && el.data.children){
							el.data.children.forEach(function (element) {
								element.MedicalFormAnswers_deleted = 2;
							});
						}

						me.refreshLeftPanel();
						me.refreshRightPanel();
						me.selectLeftPanel(index)
					},
					deletequest:function(){
						el.data.MedicalFormQuestion_deleted = 2;

						el.data.children.forEach(function (element) {
							element.MedicalFormAnswers_deleted = 2;
						});

						me.refreshLeftPanel();

						me.refreshRightPanel();
						me.selectLeftPanel(0)
					},
					addanswer: function (){
						var newChild = el.appendChild({
							MedicalFormQuestion_id: el.MedicalFormQuestion_id? el.MedicalFormQuestion_id : '',
							MedicalForm_id: el.MedicalForm_id,
							text: '',
							type:'Answers'
						});

						if(el.data.children){
							el.data.children.push(newChild.data);
						}else{
							el.data.children = [newChild.data]
						}

						me.refreshLeftPanel();

						me.refreshRightPanel();
						me.selectLeftPanel(index)
					}
				}
			});

			this.questionPreview = Ext6.create('Ext6.panel.Panel', {
				cls: 'AnketaMaker-Panel AnketaMaker-one',
				padding: '0 40 0 35',
				idx: index,
				collapsible: true,
				hidden: true,
				collapseToolText: '',
				expandToolText: '',
				title: questionPanelTextfield,
				items: this.panelVariableAnswer,
				listeners: {
					render: function () {
						var me = this;
						var header = me.getHeader();
						if (me.collapseTool && me.collapsible) {
							header.remove(me.collapseTool, false);
							header.insert(0, me.collapseTool);
						}
					}
				}
			});

			updateLayout.add(this.questionPreview);

			function getChildrenStore(el) {
				var objRow = [];

				if(el.data.children){
					el.data.children.forEach(function (element, elementIndex) {
						if(element.MedicalFormAnswers_deleted === 2){
							return
						}
						var answerPanelAdd = Ext6.create('Ext6.panel.Panel', {
							cls: 'panelVariableAnswer '  + el.data.typeQuestion,
							collapsible: !!element.children,
							collapseToolText: '',
							expandToolText: '',
							/*style: {
								paddingRight: '3px'
							},*/
							items:Ext6.create('Ext.answerPanel', {
								//maxWidthUpdate: updateLayout.getWidth()-200,
								updateLayoutCustom: updateLayout,
								answer: element.MedicalFormAnswers_Name,
								typeQuestion: el.data.typeQuestion,
								listeners:{
									datachange:function (value) {
										element.MedicalFormAnswers_Name = value;
										me.refreshRightPanel();
									},
									deleteanswer:function () {
										element.MedicalFormAnswers_deleted = 2;

										me.refreshLeftPanel();
										me.refreshRightPanel();
										me.selectLeftPanel(index)
									}
								}
							})
						});

						objRow.push(answerPanelAdd);
					});
				}

				return objRow;
			}
		});
	},

	childrenFormAnswer: function(el){
		if(el.AnswerType_id == 11){
			return Ext6.create('Ext6.previewDateOrTime', {
				margin: '0 0 5 0'
			});
		}
		else if(el.AnswerType_id == 2){
			return Ext6.create('Ext.form.field.TextArea', {
				margin: '0 0 5 0',
				width:'100%'
			});
		}
		else{
			return Ext6.create('Ext6.button.Segmented', {
				allowMultiple: el.AnswerType_id == 10,
				cls: 'segmentedButtonGroup segmentedButtonGroupMini possible-answer',
				items: arrAnswerPrevFn(el.children),
				style: {
					paddingRight: '3px',
					paddingTop: '10px'
				}
			});
		}

		function arrAnswerPrevFn(arrAnswerPrev) {
			var objRow = [];
			if(arrAnswerPrev){
				arrAnswerPrev.forEach(function (el, index) {
					var pul = {},
						styleButtonGroup = {display: 'inline-block'};

					if(el.MedicalFormAnswers_deleted === 2){
						return;
					}
					pul['text'] = el.MedicalFormAnswers_Name;
					pul['value'] = el.MedicalFormAnswers_id;
					//pul['typeQuestion'] = el.typeQuestion;
					pul['style'] = styleButtonGroup;
					objRow.push(pul);
				});
			}


			return objRow;
		}
	},

	refreshRightPanel: function(){
		var me = this,
			questionPanel = me.down('#worksheetTpl'),
			records = me.TreeStore.root.serialize().children;

		questionPanel.removeAll();

		if(records){
			records.forEach(function (el,index) {
				if(el.MedicalFormQuestion_deleted === 2){
					return;
				}
				this.questionPreview = Ext6.create('Ext6.panel.Panel', {
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
				questionPanel.add(this.questionPreview);
			});
		}
	},

	selectLeftPanel: function (idx) {
		var me = this;

		me.previewWorksheetNavigation.items.getRange().forEach(function (el) {
			if (el.hasCls('select')) {
				el.removeCls('select');
			}
		});
		let pwn = me.previewWorksheetNavigation.down('button[idx='+idx+']');
		if(!Ext6.isEmpty(pwn)) pwn.addCls('select');

		me.questionPanel.items.getRange().forEach(function (el) {
			if (el.isVisible()) {
				el.hide();
			}
		});
		let panel = me.questionPanel.down('panel[idx='+idx+']');
		if(!Ext6.isEmpty(panel)) panel.show();

	}
});