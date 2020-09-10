Ext.define('sw.tools.swDesigionTreeWindow', {
	alias: 'widget.swDesigionTreeWindow',
	extend: 'Ext.window.Window',
	title: 'Дерево решений',
	width: '80%',
	height: '80%',
	//id:'DesigionTreeWindow',
    cls: 'desigionTreeWindow',
	//maximizable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	ReturnToPrevQuest: false,
	SelectedCmpReason_id: null,
	SelectedCmpReason_Name: '',
	initComponent: function() {

		this.addEvents({
			selectReason: true
		});

		this.TreeStore = this.treestore1;

		this.TreePanel = Ext.create('Ext.tree.Panel',{
			height: 500,
			border: false,
			cls:'larger-text',
			rootVisible: false,
			id: this.id+'_TreePanel',
			title: 'Дерево решений',
			displayField: 'AmbulanceDecigionTree_Text',
			store: Ext.create('Ext.data.TreeStore',{
				idProperty: 'AmbulanceDecigionTree_id',
				fields: [
					{name: 'AmbulanceDecigionTree_id', type: 'int'},
					{name: 'AmbulanceDecigionTree_nodeid', type: 'int'},
					{name: 'AmbulanceDecigionTree_nodepid', type: 'int'},
					{name: 'AmbulanceDecigionTree_Type', type: 'int'}, //1 - вопрос, 2 - ответ
					{name: 'AmbulanceDecigionTree_Text', type: 'string'},
					{name: 'internalId', type: 'string'},
					{name: 'CmpReason_id', type: 'int'},
					{name: 'leaf'}

				],
				root:{
					leaf: false,
					expanded: true
				},
				autoLoad:false,
				listeners: {
					'beforeappend': function( store, node, eOpts ) {
						if (node&&node['data']&&node['data']['AmbulanceDecigionTree_Type']) {
							node['data']['iconCls']='decigiontreeeditwindow-tree-icon-'+((node['data']['AmbulanceDecigionTree_Type'].toString()==='1')?'question':'answer');
						}
					}
				}
			}),
			getLastNode: function() {
				var getLastChild = function(node) {
					if (node.lastChild != null) {
						return getLastChild(node.lastChild)
					} else {
						return node
					}
				}

				return getLastChild(this.getRootNode());
			}

		});

		this.DecigionRadioGroup = Ext.create('Ext.form.RadioGroup',{
			id: this.id+'_RadioGroup',
			anchor: 'none',
			layout: {
				autoFlex: true
			},
			columns: 1,
			vertical: true,
			defaults: {
				name: 'ccType',
				width: '100%',
				overCls: 'overRadioBtn',
				style: 'border: solid 1px white'
			},
			items: []
		});

		this.DecigionFieldSet = Ext.create('Ext.form.FieldSet',{
			id: this.id+'_FieldSet',
			title: 'Вопрос',
			layout: 'anchor',
			height: '100%',
			defaults: {
				anchor: '100%'
			},
			items: [
				this.DecigionRadioGroup
			]
		});

//		this.continueBtn = Ext.create('Ext.button.Button',{
//			handler: function(){
//
//			}.bind(this)
//		});

		this.returnBtn = Ext.create('Ext.button.Button',{
			handler: function(){

			}.bind(this)
		});
		this.cmpReasonCombo = Ext.create('sw.cmpReasonCombo',{
			id: this.id+'_CmpReason_id',//DesigionTreeWindow
			name: 'CmpReason_id',
			bigFont: true,
			tpl: Ext.create('Ext.XTemplate',
				'<tpl for=".">' +
				'<div class="enlarged-font x-boundlist-item">' +
				'<font color="red">{CmpReason_Code}</font> {CmpReason_Name}'+
			'</div></tpl>'),
			listeners: {
				expand: function(combo){

					if(getRegionNick().inlist(['ufa'])){
						combo.store.sort([
							{
								sorterFn: function(v1,v2){
									var num1 =  v1.get('CmpReason_Code').match(/\d{1,}/);
									var num2 =  v2.get('CmpReason_Code').match(/\d{1,}/);
									var str1 =  v1.get('CmpReason_Code').match(/[А-Я]{1,}/g);
									var str2 =  v2.get('CmpReason_Code').match(/[А-Я]{1,}/g);

									return (str1 && str2 && str1[0] != str2[0]) ? 0 : num1 - num2;

								}
							}
						])
					}

				},
				specialkey: function(field, e){
					switch (e.getKey()) {
						case e.TAB:
							e.stopEvent();
							this.delClsSelItemDesigionRadioGroup();
							this.DecigionRadioGroup.items.items[0].focus().addCls('selectedRadio');
							break;
					}
					return;

				}.bind(this),
				select: function(combo, recs){
					if (recs[0]) {
						this.SelectedCmpReason_id = recs[0].data[combo.valueField];
						this.SelectedCmpReason_Name = recs[0].get('CmpReason_Name').trim();

						this.fireEvent('selectReason', this.SelectedCmpReason_id, this.SelectedCmpReason_Name, null);
					}
				}.bind(this)
			}
		});

		this.previousQuestionButton = Ext.create('Ext.button.Button',{
			text: 'К предыдущему вопросу [BACKSPACE]',
			iconCls: 'back16',
			disabled: true,
			handler: function( ) {
				this.returnToThePreviousQuestion()
			}.bind(this)
		});

		this.selectCmpReasonForm =
			[
				{
					xtype: 'panel',
					flex: 9,
					layout: {
						type: 'hbox',
						pack: 'start',
						align: 'stretch'
					},
					items:[
						{
							flex: 1,
							xtype: 'panel',
							items:[
								this.DecigionFieldSet
							],
							bbar: {
								xtype: 'toolbar',
								items: [
									this.previousQuestionButton
								]
							}
						},
						{
							flex: 1,
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: this.TreePanel
						}
					]
				}
			]

		// Обычно поле ручного ввода повода вызова находится снизу формы
		var paddingBaseForm = '5 20',
			indexSelectCmpReason = 1;

		// но в Уфе не стандартно, поле ручного ввода повода вызова находится первым среди объектов формы
		if('ufa' == getGlobalOptions().region.nick || getGlobalOptions().region.nick == 'krym')
		{
			indexSelectCmpReason = 0;
			paddingBaseForm = '10 15';
		}

		var selectCmpReasonComboBox =
				{
					xtype:'BaseForm',
					id: this.id+'_BaseForm',
					flex: 1,
					width: '100%',
					layout: {
						padding: paddingBaseForm,
						align: 'stretch',
						type: 'vbox'
					},
					items:[
						this.cmpReasonCombo
					]
				}

		// вставляем поле ручного ввода повода вызова по индексу (сверху или снизу формы)
		this.selectCmpReasonForm.splice(indexSelectCmpReason, 0, selectCmpReasonComboBox);


		Ext.applyIf(this,{
				layout: {
					type: 'vbox',
					align: 'stretch'
				},
				defaults: {
					border: false
				},
				items: this.selectCmpReasonForm

		});


		this.callParent(arguments);

		if(getRegionNick().inlist(['perm'])){
			var data = this.treedata1;
			if (!data||!data[0]) {
				// Непонятно зачем убрали загрузку данных в хранилище и зачем-то их передают теперь в ручную.
				// Добавлю в лог сообщение на случай если кто-то с этим столкнется.
				log('При вызове дерева решений не передан параметр treedata1. См. примечание в коде.');
				return false;
			}
			//loadMask.hide();

			this._addChildNode(this._getNodeDataWithInternalId(data[0]));
			this._setQuestion(data[0]);
		}



	},
	//Получение данных ноды с внутренним идентификатором тристора
	_getNodeDataWithInternalId: function(node)  {
		if (!node) {
			return false;
		}
		var data = node.data;
		data['internalId'] = node['internalId'];
		return data;
	},
	show: function() {
		this.callParent(arguments);

		/*this.on('close',function(wnd){
			this.callback(this.SelectedCmpReason_id, this.SelectedCmpReason_Name);
		}.bind(this))
		*/
		//var loadMask = new Ext.LoadMask(this, {msg:"Пожалуйста, подождите..."});


//		loadMask.show();
//		this.TreeStore.load({callback:function(data,oper,success){
//
		if(!getRegionNick().inlist(['perm'])) {
			var data = this.treedata1;
			if (!data || !data[0]) {
				// Непонятно зачем убрали загрузку данных в хранилище и зачем-то их передают теперь в ручную.
				// Добавлю в лог сообщение на случай если кто-то с этим столкнется.
				log('При вызове дерева решений не передан параметр treedata1. См. примечание в коде.');
				return false;
			}
			//loadMask.hide();

			this._addChildNode(this._getNodeDataWithInternalId(data[0]));
			this._setQuestion(data[0]);
		}

//		}.bind(this)});
	},

	_setQuestion: function(node) {
		if (!node||
			!node.data||
			//(node.data.AmbulanceDecigionTree_Type == 2)||
			//(node.data.AmbulanceDecigionTree_Type != 1)||
			(typeof node.childNodes != 'object')
			)
		{
			return false;
		}

		var curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp', 'nmpgranddoc']);

		this.previousQuestionButton.setDisabled((this.TreePanel.getLastNode().parentNode == this.TreePanel.getRootNode()));

		/*
		if (this.TreePanel.getLastNode().parentNode.data.AmbulanceDecigionTree_Text != '') {
			this.DecigionFieldSet.setTitle(this.TreePanel.getLastNode().parentNode.data.AmbulanceDecigionTree_Text+'<br/>'+node.data.AmbulanceDecigionTree_Text);
		} else {
			this.DecigionFieldSet.setTitle(node.data.AmbulanceDecigionTree_Text);
		}
		*/
		this.DecigionFieldSet.setTitle(node.data.AmbulanceDecigionTree_Text);

		this.DecigionRadioGroup.removeAll();

		for (var key in node.childNodes) {
			this.DecigionRadioGroup.add({
				boxLabelCls: 'box-label-raised-font-size',
				inputValue: key,
				boxLabel: node.childNodes[key].data.AmbulanceDecigionTree_Text,
				checked:false,
				listeners: {
					specialkey: function(field, e){
						e.stopEvent();
						switch (e.getKey()) {
							case e.ENTER:
								field.setValue(true);
								break;
							case e.UP:
								var pos = (field.inputValue == 0)?(this.DecigionRadioGroup.items.length-1):((field.inputValue*1 - 1) % this.DecigionRadioGroup.items.length );
								this.delClsSelItemDesigionRadioGroup();
								this.DecigionRadioGroup.items.items[ pos ].focus().addCls('selectedRadio');
								break;
							case e.DOWN:
								this.delClsSelItemDesigionRadioGroup();
								this.DecigionRadioGroup.items.items[ (field.inputValue*1 + 1) % this.DecigionRadioGroup.items.length ].focus().addCls('selectedRadio');
								break;
							case e.BACKSPACE:
								this.returnToThePreviousQuestion();
								break;
							default:
								break;
						}
						return;

					}.bind(this),
					change: function( radio, newValue, oldValue, eOpts ) {
						var cmpReasonVal = node.childNodes[radio.inputValue].data.CmpReason_id;
						//var cmpReasonVal = (node.childNodes[radio.inputValue].data.CmpReason_id>0)?(node.childNodes[radio.inputValue].data.CmpReason_id):(node.childNodes[radio.inputValue].data.children[0].CmpReason_id);

						if (cmpReasonVal>0) {
							//debugger;
							//@TODO: Закрыть окно, вернуть результат
							//this.SelectedCmpReason_id = node.childNodes[radio.inputValue].data.CmpReason_id;
							this.SelectedCmpReason_id = cmpReasonVal;

							var rec = this.cmpReasonCombo.findRecord(this.cmpReasonCombo.valueField, parseInt(this.SelectedCmpReason_id));

							if (!rec) {
								this.SelectedCmpReason_id = null;

							} else {
								this.SelectedCmpReason_Name = rec.getData().CmpReason_Code+'. '+rec.getData().CmpReason_Name;
							};
							//this.SelectedCmpReason_Name = this.cmpReasonCombo.findRecord(this.cmpReasonCombo.valueField,this.SelectedCmpReason_id).getDisplayValue().trim();
							//this.close();
							//Ext.defer(function(){this.close();}.bind(this), 500);

							this.fireEvent('selectReason', this.SelectedCmpReason_id, this.SelectedCmpReason_Name, node.childNodes[radio.inputValue].get('AmbulanceDecigionTree_id'));

							return true;
						}
						if (newValue === true ) {
							this._addChildNode(this._getNodeDataWithInternalId(node.childNodes[radio.inputValue]));
							if(!isNmpArm)this._addChildNode(this._getNodeDataWithInternalId(node.childNodes[radio.inputValue].childNodes[0]));

							// https://redmine.swan.perm.ru/issues/59728#note-18
							// Т.к. нижеуказанная функция вызывается раньше, чем срабатывает внутренний экстовский метод смены класса
							// для выбранного радиобаттона, получается, что элемент удаленный при помощи this.DecigionRadioGroup.removeAll();
							// уже не существует и в фаербаге выводится ошибка.
							// @todo Необходима оптимизация данного участка кода.


							setTimeout(function(){
								//У нмп своя структура дерева
								if(isNmpArm){
									if(this._setQuestion(node.childNodes[radio.inputValue]) == false){
										//reset form
										this._setQuestion(this.treedata1[0]);
									}
								} else {
									if (this._setQuestion(node.childNodes[radio.inputValue].childNodes[0]) == false) {
										if (this._setQuestion(node.childNodes[radio.inputValue]) == false) {
											//reset form
											this._setQuestion(this.treedata1[0]);
										}
									}
								}

							}.bind(this),100);
							//setTimeout(function(){this._setQuestion(node.childNodes[radio.inputValue].childNodes[0]);}.bind(this),100);
						}
					}.bind(this),
					render: function() {
						Ext.defer(function(){
							this.DecigionRadioGroup.items.items[0].focus();
						}.bind(this), 200);
						this.DecigionRadioGroup.items.items[0].focus().addCls('selectedRadio');
					}.bind(this)
				}
			});

//			this.DecigionRadioGroup.doLayout();
		}



		if (this.TreePanel.getLastNode().parentNode != this.TreePanel.getRootNode() || this.ReturnToPrevQuest || ("ufa" != getGlobalOptions().region.nick && getGlobalOptions().region.nick != 'astra'))
		{
			(this.DecigionRadioGroup.items.items[0])?this.DecigionRadioGroup.items.items[0].focus().addCls('selectedRadio'):null;
		}
		else
		{
			Ext.defer(function(){
				this.cmpReasonCombo.focus();
			}.bind(this), 300);
			this.delClsSelItemDesigionRadioGroup();
			(this.DecigionRadioGroup.items.items[0])?this.DecigionRadioGroup.items.items[0].focus().removeCls('selectedRadio'):null;
		}



		//this.DecigionRadioGroup.items.items[0].focus().addCls('selectedRadio');
	},
	_addChildNode: function(data) {
		data.leaf = true;
		this.TreePanel.getLastNode().appendChild(data);
	},
	returnToThePreviousQuestion:function(){
		var curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp', 'nmpgranddoc']);

		if (this.TreePanel.getLastNode().parentNode == this.TreePanel.getRootNode()) {
			return false;
		}

		this.TreePanel.getLastNode().remove();
		//У НМП другая структура дерева
		if(!isNmpArm)this.TreePanel.getLastNode().remove();

		if("ufa" == getGlobalOptions().region.nick || getGlobalOptions().region.nick == 'krym')
			this.ReturnToPrevQuest 	= true;
		this._setQuestion(this.TreeStore.getNodeById(this.TreePanel.getLastNode().data.internalId));
	},
	delClsSelItemDesigionRadioGroup: function(){
		this.DecigionRadioGroup.items.items.forEach(function(item,i){
			item.removeCls('selectedRadio')
		});

	}
})
