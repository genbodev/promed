/**
* ufa_swGraceCalculator - окно для калькулятора Grace.
*  
*
* PromedWeb - The New Generation of Medical Statistic Software
* 
*
* @package      Admin
* @access       public
* @version      25.06.2013
* @author       Васинский Игорь (НПК "Прогресс" г.Уфа)
*/


   
sw.Promed.ufa_swGraceCalculator = Ext.extend(sw.Promed.BaseForm, {
    alwaysOnTop: true,
	id    : 'ufa_swGraceCalculator', 
	objectName    : 'ufa_swGraceCalculator',
	objectSrc     : '/jscore/Forms/Admin/Ufa/ufa_swGraceCalculator.js',    
	layout: 'form',
	buttonAlign: 'center',
	title : 'Калькулятор шкалы Grace: введите данные для расчета',
	modal : true,
	width : 500,
    fieldWidth:40,
	height:500,
    labelWidth:400,
	closable : false,
    resizable: false,
    bodyStyle:'padding:10px',
	closeAction   : 'close',
	draggable     : true,
	initComponent: function() 
	{      
		var form = this;		
		Ext.apply(this, 
		{   
			autoHeight: true,
			buttons : [
					 {
						hidden: false,
						handler: function() 
						{
                            var arrayitms = {
                                    0:  [
                                            [29,39,49,59,69,79,89,90],//Возраст (лет)
                                            [0,8,25,41,58,75,91,100]
                                        ],
                                    1:  [
                                            [49,69,89,109,149,199,200],//Частота сердечных сокращений (ударов/минуту)
                                            [0,3,9,15,24,38,46]
                                        ],
                                    2:  [
                                            [79,99,119,139,159,199,200],//Систолическое артериальное давление (мм рт. ст.)
                                            [58,53,43,34,24,10,0]],
                                    3:  [
                                            [34,70,105,140,176,353,354],//Уровень креатинина сыворотки (мкмоль/л) 
                                            [1,4,7,10,13,21,28]
                                        ]
                                    };
                            var dataValues = {
                                0: form.items.get(0).getValue(),
                                1: form.items.get(1).getValue(),
                                2: form.items.get(2).getValue(),
                                3: form.items.get(3).getValue(),
                                4: form.items.get(4).getValue(),
                                5: form.items.get(5).getValue(),
                                6: form.items.get(6).getValue(),
                                7: form.items.get(7).getValue(),
                                8: form.items.get(8).getValue(),
                                9: form.items.get(9).getValue()

                            };
                            var summ = 0;
                            for (var i in arrayitms) {
                                console.log('Индекс', i);
                                  for (var j in arrayitms[i][0]) {
                                   console.log('j', j,'arrayitms[i][0].length-1',arrayitms[i][0].length-1);
                                    if (j == arrayitms[i][0].length-1) {
                                        if (dataValues[i] >= arrayitms[i][0][j]) {
                                            summ = summ + arrayitms[i][1][j];
                                            console.log('itm', i, dataValues[i], 'и значение массива', i, arrayitms[i][0][j]);
                                            console.log('Заполнение из массива', i, arrayitms[i][1][j]);
                                            break;
                                        }
                                    }
                                    else {
                                        if (dataValues[i] <= arrayitms[i][0][j]) {
                                            console.log('itm', i, dataValues[i], 'и значение массива', i, arrayitms[i][0][j]);
                                            summ = summ + arrayitms[i][1][j];
                                            console.log('Заполнение из массива', i, arrayitms[i][1][j]);
                                            break;
                                        }
                                    }
                                }                    
                            }    
                            summ = summ + Number(dataValues[4]);
                            //console.log('Класс сердечной недостаточности (по классификации Killip) ', Number(dataValues[4]));
                            for (i = 5; i <= 7; i++) {
                                //console.log('Вопрос', i+1, 'значение',dataValues[i]);
                                if (dataValues[i]){
                                    summ = summ + Number(form.items.get(i).inputValue);
                                    //console.log('Вопросы с 6 по 8','вопрос', i+1, form.items.get(i).inputValue);
                                }
                            }
                            
                            form.items.get(8).setValue(summ);
                            
                            if (summ < 109) {
                                form.items.get(9).setValue('I');
                            } 
                            else if (summ <= 140) {
                                form.items.get(9).setValue('II');
                            }
                            else {
                                form.items.get(9).setValue('III');
                            }
                            
                            if (form.items.get(9).getValue()>'') {
                                form.buttons[1].enable();
                            }   
						},
						iconCls: 'ok16',
						text: 'Рассчитать'
					 },
                     {	
                        hidden: false,
                        disabled:true,
						handler: function() {
                            if (form.items.get(9).getValue()>'') {
                                console.log('Передача формы прошла успешно');
                                Ext.getCmp('Answer_269').setValue(form.items.get(9).getValue());
                                form.refresh();
                            }
						},
						iconCls: 'ok16',
						text: 'Использовать'
					 },                                         
					 {
						hidden: false,
						handler: function() 
						{
                            form.refresh();
						},
						iconCls: 'close16',
						text: 'Отмена'
					 }                     
			],
	 
			items : [
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Возраст (лет)',
                        name: 'age',
                        id:'age',
                        readOnly: true,
                        width:form.fieldWidth,
                        labelAlign: 'left',
                        minValue: 0,   
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'Частота сердечных сокращений (ударов/минуту)',
                        name: 'chss',
                        width:form.fieldWidth,
                        labelAlign: 'left',
                        maskRe: /[0-9]/,
                        minValue: 0,       
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'Систолическое артериальное давление (мм рт. ст.)',
                        name: 'sad',
                        id: 'sad',
                        maskRe: /[0-9]/,
                        width:form.fieldWidth,
                        labelAlign: 'left',
                        minValue: 0   
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'Уровень креатинина сыворотки (мкмоль/л)',
                        name: 'uks',
                        maskRe: /[0-9]/,
                        width:form.fieldWidth,
                        labelAlign: 'left',
                        minValue: 0,     
                    },{
                        xtype: 'combo',
                        fieldLabel: 'Класс сердечной недостаточности (по классификации Killip)',
                        name: 'ksn',
                        width:form.fieldWidth,
                        labelAlign: 'left',
                        mode:'local',
                        minValue: 0,
                        store:new Ext.data.SimpleStore(  {           
                                  fields: [{name:'classsn', type:'string'},{ name:'ball',type:'int'}],
                                  data: [
                                          ['I', 0],
                                          ['II', 20],
                                          ['III', 39],
                                          ['IV', 59]
                                          ]
                                }),
                        displayField:'classsn',
                        valueField:'ball',         
                    },{
                        xtype: 'checkbox',
                        fieldLabel: 'Остановка сердца (на момент поступления пациента)',
                        name: 'os',
                        labelWidth:form.labelWidth,
                        labelSeparator:'',
                        inputValue: '39',      
                    },{
                        xtype: 'checkbox',
                        fieldLabel: 'Девиация сегмента ST',
                        name: 'dsst',
                        labelWidth:form.labelWidth,
                        labelAlign: 'left',
                        inputValue: '28', 
                    },{
                        xtype: 'checkbox',
                        fieldLabel: 'Наличие диагностически значимого повышения уровня кардиоспецифических ферментов',
                        name: 'pukf',
                        labelAlign: 'left',
                        labelWidth:form.labelWidth,
                        inputValue: '14', 
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'Баллы по шкале GRACE',
                        name: 'calcsumm',
                        readOnly: true,
                        width:form.fieldWidth,

                    },{
                        xtype: 'textfield',
                        fieldLabel: 'Риск по шкале GRACE',
                        name: 'risk',
                        readOnly: true,
                        width:form.fieldWidth,
                    }			   
			]
		});
		sw.Promed.ufa_swGraceCalculator.superclass.initComponent.apply(this, arguments);
	},
	refresh : function(){
			      var objectClass = this.objectClass;
				  var lastArguments = this.lastArguments;
				  sw.codeInfo.lastObjectName = this.objectName;
				  sw.codeInfo.lastObjectClass = this.objectClass;
				
                  if (sw.Promed.Actions.loadLastObjectCode){
					  sw.Promed.Actions.loadLastObjectCode.setHidden(false);
				  }
				
                  this.hide();
				  this.destroy();
				  window[this.objectName] = null;
				  delete sw.Promed[this.objectName];

				  if (sw.ReopenWindowOnRefresh) {
					  getWnd(objectClass).show(lastArguments);
				  }
	},    
	show: function(params) 
	{   
            console.log(params);
            if (typeof Ext.getCmp('Answer_269') != 'undefined'){
                Ext.getCmp('Answer_269').collapse();
            }
            if (typeof params.age != 'undefined'){
                Ext.getCmp('age').setValue(params.age);
            }   
            if (params.sis > 0){
                Ext.getCmp('sad').setValue(params.sis);
                Ext.getCmp('sad').getEl().dom.setAttribute('readOnly', true);
            }    
            sw.Promed.ufa_swGraceCalculator.superclass.show.apply(this, arguments);
	}

});