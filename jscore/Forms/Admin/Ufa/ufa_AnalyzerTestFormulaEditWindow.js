/**
 * ufa_AnalyzerTestFormulaEditWindow - форма создания формул для теста экземпляра анализатора
 * @package      Admin/Ufa
 * @access       public
 * @copyright    Copyright (c) 2016 LTD Progress
 * @author       Vasinsky Igor
 * @version      17.06.2016
 * @comment
 */

sw.Promed.ufa_AnalyzerTestFormulaEditWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'ufa_AnalyzerTestFormulaEditWindow',
    //MorbusType_id: false,
    modal: true,
    title: '',
    height: 500,
    width: 1000,
	arrayEl: [],
    action : false,
	labelWidth:40,
    closeAction: 'hide',
    bodyStyle: 'padding:10px;border:0px',
    initComponent: function () {
        //document.styleSheets[document.styleSheets.length - 1].addRule(".x-window-dlg","z-index: 9100 !important"); //Т.к окно иногда бывает на заднем фоне
		this.AnalyzerParams = new sw.Promed.ViewFrame(
			{
				object: 'AnalyzerParams',
				toolbar: false,
				id:'AnalyzerParams',
				//dataUrl:'/?c=AnalyzerTest&m=loadAnalyzerTestGrid',
				dataUrl:'/?c=ufa_AnalyzerTestFormula&m=loadAnalyzerTestGrid',
				autoLoadData: false,
				contextmenu: false,	
				height: 350,
				root: 'data',
				stringfields:[
					{name:'AnalyzerTest_id', type:'int', header:'ID', key:true},
					{name:'AnalyzerTest_pid', type:'int', hidden:true, isparams:true},
					{name:'Analyzer_id', type:'int', hidden:true, isparams:true},
					{name:'AnalyzerTest_Code', header:'Код теста', width:120, renderer: function (v,m,r) {
									if (r.get('AnalyzerTestType_id') != '1') {
										m.attr = 'style="color:#A6A4A4"';
									}
									return v;
								}},
					{name:'AnalyzerTest_Name', header:'Наименование теста', width:120, id: 'autoexpand', renderer: function (v,m,r) {
									if (r.get('AnalyzerTestType_id') != '1') {
										m.attr = 'style="color:#A6A4A4"';
									}
									return v;
								}},
					{name:'AnalyzerTest_begDT', type: 'date', header: 'Дата начала', width: 80, hidden:true},
					{name:'AnalyzerTest_endDT', type: 'date', header: 'Дата окончания', width: 80, hidden:true},
					{name:'AnalyzerTest_SortCode', type:'int', header:'Код сортировки', width: 80, hidden:true},
					{name:'AnalyzerTest_SysNick', type:'string', header:'Мнемоника', width:80, hidden:true},
					{name:'AnalyzerTestType_id_Name', type:'string', header:'Тип теста', width:120, hidden:true},
					{name:'Unit_Name', type:'string', header:'Единица измерения', width:120, hidden:true},
					{name:'AnalyzerTest_IsNotActive', type: 'checkcolumnedit', header: 'Неактивный', width: 40, hidden:true},
					{name:'AnalyzerTest_HasLisLink', type: 'checkcolumn', header: 'Связь с ЛИС', width: 40, hidden:true},
					{name:'AnalyzerTestType_id', type:'int', hidden:true},
					{name:'AnalyzerTest_isTest', type:'int', hidden:true},
					{name:'Unit_id', type:'int', hidden:true}
				],
				focusOnFirstLoad: false,
		});

		this.AnalyzerParams.getGrid().getSelectionModel().on('rowselect', function( sm, rowIndex, r) {
			if (r.get('AnalyzerTestType_id') == '1') {
				Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd('{'+r.get('AnalyzerTest_Code')+'}');
			} else {
				Ext.Msg.alert('Ошибка', 'В формуле можно использовать только количественный тест!');				
			}
			
			sm.deselectRow(rowIndex);
		})
        Ext.apply(this,
                {
                    items: [
						{
							xtype: 'textfield',
							id: 'formula',
                            fieldLabel: '',
							anchor: '100%',
							readOnly: true,
                            hideLabel: true
						},
						{
							xtype: 'panel',
							bodyStyle: 'background-color:transparent; border:none; margin-bottom:5px',
								items: [
									{
										xtype: 'button',
										text:'+',
										id: 'btn+',
										//minWidth :40,
										style: 'float: left; margin-right: 10px;',
										tooltip:'+',
										listeners: {
											'click': function() {
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd('+');
											}
										}
									},
									{
										xtype: 'button',
										text:'-',
										style: 'float: left; margin-right: 10px',
										tooltip:'-',
										id: 'btn-',
										listeners: {
											'click': function() {
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd('-');
											}
										}
									},
									{
										xtype: 'button',
										text:'/',
										style: 'float: left; margin-right: 10px',
										tooltip:'/',
										id: 'btn/',
										listeners: {
											'click': function() {
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd('/');
											}
										}
									},
									{
										xtype: 'button',
										text:'*',
										style: 'float: left; margin-right: 10px',
										tooltip:'*',
										id: 'btn*',
										listeners: {
											'click': function() {
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd('*');
											}
										}
									},
									/*{
										xtype: 'button',
										text:'&#8730;',
										style: 'float: left; margin-right: 10px',
										tooltip:'Ctrl+0',
										id: 'btnsqrt',
										listeners: {
											'click': function() {
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd('sqrt');
											}
										}
									},
									{
										xtype: 'button',
										text:'^',
										style: 'float: left; margin-right: 10px',
										tooltip:'^',
										id: 'btn^',
										listeners: {
											'click': function() {
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd('^');
											}
										}
									},*/
									{
										xtype: 'button',
										text:'(',
										style: 'float: left; margin-right: 10px',
										tooltip:'Shift+9',
										id: 'btn(',
										listeners: {
											'click': function() {
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd('(');
											}
										}
									},
									{
										xtype: 'button',
										text:')',
										style: 'float: left; margin-right: 10px',
										tooltip:'Shift+0',
										id: 'btn)',
										listeners: {
											'click': function() {
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd(')');
											}
										}
									},
									{
										xtype: 'button',
										text:'&#8592;',
										style: 'float: right',
										tooltip:'Backspace',
										id: 'btncl',
										listeners: {
											'click': function() {
												Ext.getCmp('editform').setDisabled(false);
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl.pop();
												/*if($_action == 'edit') {
													//Ext.getCmp('formula').setValue(Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl.join(''));
													//console.log(Ext.getCmp('formula').getValue(Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl.join('')));
													//Ext.getCmp('formula').getValue(Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl.join(''));
												} else {*/
													Ext.getCmp('formula').setValue(Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl.join(''));													
												//}
											}
										}
									},
									{
										xtype: 'button',
										text:'Вставить',
										id: 'inpt',
										style: 'float: right; margin-right: 10px',
										listeners: {
											'click': function() {
												Ext.getCmp('number').setValue(Ext.getCmp('number').getValue().replace(/,/, '.'));
												if (!Number(Ext.getCmp('number').getValue())) {
													Ext.Msg.alert('Ошибка', 'Неверный формат числа');
													return false;
												}
												if (/^\..*/.test(Ext.getCmp('number').getValue())) {
													Ext.Msg.alert('Ошибка', 'Неверный формат числа');
													return false;
												}												
												if (Ext.getCmp('number').getValue().charAt(0) == '+' || Ext.getCmp('number').getValue().charAt(0) == '-') {
													Ext.Msg.alert('Ошибка', 'Установка знаков "+" или "-" при задании чисел недопустима');
													return false;
												}
												if(Ext.getCmp('number').getValue().length > 10){
													Ext.Msg.alert('Ошибка', 'Число больше 10 знаков');
													return false;
												}
												Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayAdd(Ext.getCmp('number').getValue());
											}
										}
									},
									{
										xtype: 'textfield',
										id:'number',	
										style: 'float: right; margin-right: 10px',
									},									
									{
										xtype: 'label',
										text:'Константа',
										style: 'float: right; margin-right: 10px'
									}	
								]
						},
						this.AnalyzerParams,
						{
							xtype: 'label',
							text:'Комментарий',
							style: 'float: left; margin-right: 10px; margin-top: 3px'
						},							
						{
							xtype: 'textfield',
							hideLabel: true, 
							id:'comment',
							width: 890,
							enableKeyEvents: true,
							style: 'margin-top: 3px',
							listeners: {
								keyup: function() {
									Ext.getCmp('editform').setDisabled(false);
								}
							}
						}									
						
                    ], buttons:
                            [
                                {
                                    text: 'Сохранить',
									id: 'saveform',
                                    handler: function () {
										Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').saveform();										
                                    }
                                },
                                {
                                    text: 'Сохранить',
									id: 'editform',
                                    handler: function () {
										Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').editform();
                                    }
                                },	
								{
									text: '-'
								},								
                                {
                                    text: 'Очистить',
                                    handler: function () {
										Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl = [];
										Ext.getCmp('formula').reset();
                                    },
                                },
                                {
                                    text: 'Отмена',
                                    handler: function () {
										Ext.getCmp('formula').reset();
										Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clearform();
										/*this.destroy();
										window[this.objectName] = null;
										delete sw.Promed[this.objectName];*/
                                    }
                                }							
                            ]
                }
        );
        sw.Promed.ufa_AnalyzerTestFormulaEditWindow.superclass.initComponent.apply(this, arguments);
	},
	arrayAdd: function(v) {
		//document.styleSheets[document.styleSheets.length - 1].addRule(".x-window-dlg","z-index: 9100 !important"); //Т.к окно иногда бывает на заднем фоне
		var arraymath = ['+','/','*','^'];
		var arraymath2 = ['-','/','*','^'];
		//Если формула редактируется, то старый ее вариант удаляем
		if (Ext.getCmp('formula').getValue() != '' &&  Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl.length == 0) {
			Ext.getCmp('editform').setDisabled(false);
			Ext.getCmp('formula').setValue('');
		}
		Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl.push(v);
		if (this.arrayEl[0].inlist(['/','*','^',')','+']) ) {
			this.arrayEl.splice(-1,1);
			Ext.Msg.alert('Ошибка', 'Формула не может начинаться со знака математического действия или ")"');
			return false;
		} else if (this.arrayEl[this.arrayEl.length-1] == '(' && this.arrayEl[this.arrayEl.length-2] == ')') {
			this.arrayEl.splice(-1,1);			
			Ext.Msg.alert('Ошибка', 'Недопустима последовательность ")(", между скобками должен стоять знак математического действия');
			return false;
		} else if (this.arrayEl[this.arrayEl.length-1] == ')' && this.arrayEl[this.arrayEl.length-2] == '(') {
			this.arrayEl.splice(-1,1);		
			Ext.Msg.alert('Ошибка', 'Недопустима последовательность "()", между скобками должно стоять числовое значение');
			return false;
		} else if (this.arrayEl[this.arrayEl.length-1] != '(' && this.arrayEl[this.arrayEl.length-2] == 'sqrt') {
			this.arrayEl.splice(-1,1);			
			Ext.Msg.alert('Ошибка', 'После "sqrt" должна стоять скобка "("');
			return false;
		} else if (typeof this.arrayEl[1] != 'undefined' && this.arrayEl[1].inlist(arraymath) && this.arrayEl[0].inlist(['+','-'])) {
			this.arrayEl.splice(-1,1);		
			Ext.Msg.alert('Ошибка', 'Знаки математических действий не должны идти друг за другом');
			return false;
		} else if (this.arrayEl[this.arrayEl.length-2] == '(' && this.arrayEl[this.arrayEl.length-1].inlist(arraymath)
				|| this.arrayEl[this.arrayEl.length-3] == '(' && this.arrayEl[this.arrayEl.length-2].inlist(['+','-']) && this.arrayEl[this.arrayEl.length-1].inlist(arraymath)) {
			this.arrayEl.splice(-1,1);			
			Ext.Msg.alert('Ошибка', 'После скобки "(" не должно быть знаков "/","*","^","+"');
			return false;
		} else if (this.arrayEl[this.arrayEl.length-1] == ')' && this.arrayEl[this.arrayEl.length-2].inlist(["/","*","^","sqrt","+","-"])) {
			this.arrayEl.splice(-1,1);						
			Ext.Msg.alert('Ошибка', 'Перед скобкой ")" не должно быть знаков "/","*","^","sqrt","+","-"');
			return false;
		} else if (!this.arrayEl[this.arrayEl.length-1].inlist(['-','/','*','^',')']) && this.arrayEl[this.arrayEl.length-2] == ')') {
			this.arrayEl.splice(-1,1);						
			Ext.Msg.alert('Ошибка', 'После скобки ")" должен стоять знак математического действия');
			return false;
		}
		//Если матеметическое действие стоит после математичееского действия, то предыдущее действие удаляем
		//Если вставляются два числа подряд, то предыдущее число удаляется
		//Если sqrt следует за числом, то число удаляется
		//Если '(' следует за числом, то число удаляется
		if (this.arrayEl.length > 1 && this.arrayEl[this.arrayEl.length-1].inlist(arraymath2) && this.arrayEl[this.arrayEl.length-2].inlist(arraymath2)
			|| this.arrayEl.length > 1 && /(^\{.*\}$)|(^\d*\.?\d*$)/.test(this.arrayEl[this.arrayEl.length-1]) && /(^\{.*\}$)|(^\d*\.?\d*$)/.test(this.arrayEl[this.arrayEl.length-2])
			|| this.arrayEl.length > 1 && this.arrayEl[this.arrayEl.length-1] == 'sqrt' && /(^\{.*\}$)|(^\d*\.?\d*$)/.test(this.arrayEl[this.arrayEl.length-2])
			//|| this.arrayEl.length > 1 && this.arrayEl[this.arrayEl.length-2] == 'sqrt' && /(^\{.*\}$)|(^\d*\.?\d*$)/.test(this.arrayEl[this.arrayEl.length-1])
			|| this.arrayEl.length > 1 && this.arrayEl[this.arrayEl.length-1] == '(' && /(^\{.*\}$)|(^\d*\.?\d*$)/.test(this.arrayEl[this.arrayEl.length-2])
			) {
			this.arrayEl.splice(-2,1);
		}
		Ext.getCmp('formula').setValue(Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').arrayEl.join(''));
	},
	clickbtn: function(idbtn) {
		var myBtn = Ext.getCmp(idbtn);
		myBtn.fireEvent('click', myBtn);		
	},
	$_action: '',
    show: function () {
        sw.Promed.ufa_AnalyzerTestFormulaEditWindow.superclass.show.apply(this, arguments);        
		document.getElementById('ufa_AnalyzerTestFormulaEditWindow').addEventListener(
			'keyup',  
			function(e){
				var x;
				if (e.ctrlKey == true && e.keyCode == 96) {
					x = 'sqrt';
				} else {
					x = e.key;
				}
				switch(x) {
					case '+':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btn+');
						break;
					case '-':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btn-');
						break;		
					case '/':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btn/');
						break;	
					case '*':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btn*');
						break;		
					case 'sqrt':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btnsqrt');
						break;	
					case '^':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btn^');
						break;	
					case '(':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btn(');
						break;	
					case ')':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btn)');
						break;		
					/*case 'Backspace':
						Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clickbtn('btncl');
						break;	*/					
				}
				//Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').focus();
			}
		);	
		//для предотвращения перехода на предыдущую страницу
		document.onkeydown = function(e) {
			//if (e.keyCode == 8) { return false; }
		}

        if(arguments[0]){
            if(arguments[0].params.action == 'add'){
				Ext.getCmp('saveform').setVisible(true);
				Ext.getCmp('editform').setVisible(false);
            } else if (arguments[0].params.action == 'edit') {
				Ext.getCmp('saveform').setVisible(false);
				Ext.getCmp('editform').setVisible(true);
				Ext.getCmp('editform').setDisabled(false);
				Ext.getCmp('formula').setValue(arguments[0].params.recformula.get('AnalyzerTestFormula_Formula'));
				this.AnalyzerTestFormula_id = arguments[0].params.recformula.get('AnalyzerTestFormula_id');
				Ext.getCmp('comment').setValue(arguments[0].params.recformula.get('AnalyzerTestFormula_Comment'));
			}
			this.arrayEl = [];
			this.action = arguments[0].params.action;
			$_action = arguments[0].params.action;
			this.AnalyzerTest_id = arguments[0].params.AnalyzerTest_id; //id теста
			this.Analyzer_id = arguments[0].params.Analyzer_id; //id анализатора
			this.AnalyzerTest_pid = arguments[0].params.AnalyzerTest_pid; //id исследования  
			this.AnalyzerTestFormula_Code = arguments[0].params.AnalyzerTestFormula_Code; // код анализатора
			this.AnalyzerTestFormula_ResultUnit = arguments[0].params.AnalyzerTestFormula_ResultUnit; // ед. изм.
			this.AnalyzerTestFormula_UslugaName = arguments[0].params.AnalyzerTestFormula_UslugaName; // имя услуги
			this.title = arguments[0].params.title;
			var store = this.AnalyzerParams.getGrid().getStore();
			store.load({
				params: {
					AnalyzerTest_id: Ext.getCmp('AnalyzerTestEditWindow').form.findField('AnalyzerTest_id').getValue(),
					AnalyzerTest_pid: this.AnalyzerTest_pid,
				}
			});

			// заголовок окна
			Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').setTitle(Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').title + ' для услуги ' 
			+this.AnalyzerTestFormula_Code + ' (' + this.AnalyzerTestFormula_UslugaName + ')');
        }
    },
	//Подготовка функции к сохранению
	beforesave: function() {
		if (Ext.getCmp('formula').getValue() == '') {
			Ext.Msg.alert('Ошибка', 'Попытка сохранить пустое значение');
			return false;
		}
		var form = this;
		var kf = form.arrayEl.length -1;

		// Проверка на правильность использования sqrt
		if( form.arrayEl[kf] == 'sqrt' )
		{
			if(form.arrayEl[kf+1] != '(')
			{
				Ext.Msg.alert('Ошибка', 'После sqrt должна стоять скобка (');
				return false;
			}
		}

		var op = 0;//счетчик открывающихся скобок
		var cl = 0;//счетчик закрывающихся скобок
		for (var k in form.arrayEl) {
			if (form.arrayEl[k] == '(') {
				op++;
			}
			if (form.arrayEl[k] == ')') {
				if (op == 0) {
					Ext.Msg.alert('Ошибка', 'Проверьте правильность установки скобок в формуле');
					return false;
				}
				cl++;												
			}													
		}
		if (op != cl) {
			Ext.Msg.alert('Ошибка', 'Проверьте правильность установки скобок в формуле');
			return false;
		}

		//Проверка что после математических знаков есть второе значение
		if( form.arrayEl[kf] == '+' || 
			form.arrayEl[kf] == '-' || 
			form.arrayEl[kf] == '*' ||
		    form.arrayEl[kf] == '/' ||
		    form.arrayEl[kf] == '^'
		  )
		{
			Ext.Msg.alert('Ошибка', 'Неправильное математическое выражение');
			return false;
		}

		//Выделение кодов тестов, используемых в формуле, в отдельный массив
		form.arrtest = [].concat(form.arrayEl);
		var arrn =  true;
		while (arrn) {
			for (var k in form.arrtest) {
				if (!/^\{.*\}$/.test(form.arrtest[k]) && typeof(form.arrtest[k]) == 'string') {
					form.arrtest.splice(k,1);
					break;
				}

				if (k == form.arrtest.length - 1) {
					arrn = false;
					return true;
				} else {
					return true;
				}
			}											
		}
		//Удаление повторяющихся элементов массива
		var i = form.arrtest.length;
		form.arrtest.sort();
		while (i--) {
			if (form.arrtest[i] == form.arrtest[i-1]) {
				form.arrtest.splice(i, 1);
			}
		}
		//Удаление фигурных скобок
		for (var k in form.arrtest) {
			if (typeof(form.arrtest[k]) == 'string') {
				form.arrtest[k] = form.arrtest[k].replace(/^\{(.*)\}$/, '$1');
			}

		}

		return true;
	},
	reloadFormulaGrid: function() {
		//Обновление грида формул
		var params = 
			{
				Analyzer_id: this.Analyzer_id,
				AnalyzerTest_id: this.AnalyzerTest_id,
				AnalyzerTest_pid: this.AnalyzerTest_pid
			};
		Ext.getCmp('AnalyzerTestFormulaGrid').getGrid().getStore().load({params:params});
	},
	saveform: function() {
		if (!this.beforesave()) return false;

		var win = this;
		var Usluga_id = [];
		Usluga_id.push(this.AnalyzerTestFormula_Code);
		var params = 
		{
			AnalyzerTestFormula_Comment: Ext.getCmp('comment').getValue(),
			AnalyzerTestFormula_Formula: Ext.getCmp('formula').getValue(),
			Analyzer_id: this.Analyzer_id,
			AnalyzerTest_id: this.AnalyzerTest_id,
			AnalyzerTest_pid: this.AnalyzerTest_pid,
			Usluga_ids: Ext.encode(Usluga_id),//Ext.encode(this.arrtest),
			AnalyzerTestFormula_Code : this.AnalyzerTestFormula_Code,
			AnalyzerTestFormula_ResultUnit : this.AnalyzerTestFormula_ResultUnit
		};
		Ext.Ajax.request({
			url: '/?c=ufa_AnalyzerTestFormula&m=AnalyzerTestFormula_ins',
			params: params,
			callback: function (options, success, response) {
				if (success === true) {
					Ext.getCmp('formula').reset();
					Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clearform();
					win.reloadFormulaGrid();
				} else {
					Ext.Msg.alert('Ошибка', 'Неудачное сохранение формулы');
				}
			}
		});
		Ext.getCmp('AnalyzerTestFormulaGrid').setActionDisabled('action_add', true);
	},
	editform: function() {
		if (!this.beforesave()) return false;

		var win = this;
		var Usluga_id = [];
		Usluga_id.push(this.AnalyzerTestFormula_Code);
		var params = 
		{
			AnalyzerTestFormula_Comment: Ext.getCmp('comment').getValue(),
			AnalyzerTestFormula_Formula: Ext.getCmp('formula').getValue(),
			Analyzer_id: this.Analyzer_id,
			AnalyzerTest_id: this.AnalyzerTest_id,
			AnalyzerTest_pid: this.AnalyzerTest_pid,
			Usluga_ids: Ext.encode(Usluga_id),//Ext.encode(this.arrtest),
			AnalyzerTestFormula_id: this.AnalyzerTestFormula_id,
			AnalyzerTestFormula_Code: this.AnalyzerTestFormula_Code,
			AnalyzerTestFormula_ResultUnit : this.AnalyzerTestFormula_ResultUnit
	   	};			
		Ext.Ajax.request({
			url: '/?c=ufa_AnalyzerTestFormula&m=AnalyzerTestFormula_upd',
			params: params,
			callback: function (options, success, response) {
				if (success === true) {
					Ext.getCmp('formula').reset();
					Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').clearform();
					win.reloadFormulaGrid();
				} else {
					Ext.Msg.alert('Ошибка', 'Неудачное сохранение формулы');
				}
			}
		});
	},
	//Удаление формы
	clearform: function() {
		Ext.getCmp('ufa_AnalyzerTestFormulaEditWindow').hide();
		this.destroy();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];				
	}
}
);