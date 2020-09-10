/* 
 * Форма АРМ Секретаря службы cудебно-медицинской экспертизы трупов с судебно-гистологическим отделением
 */


Ext.define('common.BSME.ForenCorp.SecretaryWP.swSecretaryWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultSecretaryWP.swDefaultSecretaryWorkPlace',
	refId: 'ForenCorpSecretaryWorkPlace',
	id: 'ForenCorpSecretaryWorkPlace',
	//Обработчик кнопки "Создать заявку"
	createRequestButtonHandler: function() {
		Ext.create('common.BSME.ForenCorp.SecretaryWP.tools.swCreateRequestWindow').show();
	},
	//Элементы дерева журналов
	JournalTreeStoreChildren:[
		{ Journal_Text: 'Все заявки',Journal_Type: '', expanded: true, children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicCorpHist'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
	],
    initComponent: function() {
		var me = this;
		
		var createDirectionWindow = function(name) {
			if (!name.length) {
				return false;
			}
			
			var rec = me.getCurrentRequestRecord();
			if (!rec || (typeof rec.get != 'function') || !rec.get('EvnForensic_id')) {
				Ext.Msg.alert('Ошибка', 'Не указан идентификатор родительской заявки');
				return false;
			}
			Ext.create(name,{
				Evn_pid : rec.get('EvnForensic_id')
			});
			
		}.bind(this);
		//Кнопки тулбара для панели просмотре заявок
		//Пришлось вынести в initComponent для определения нужной области видимости хэндлеров
		this.requestViewPanelButtons = [{
			text: 'Редактировать',
			iconCls: 'edit16',
			xtype: 'button',
			handler: function() {
			}

		},{
			text: 'Печать',
			xtype: 'button',
			iconCls: 'print16',
			handler: function () {
			}
		},{
			xtype: 'splitbutton',
			iconCls: 'add16',
			text: 'Создать',
			menu: {
				xtype: 'menu',
				items: [
					{
						xtype: 'menuitem',
						text: 'Исследование трупной крови в судебно-биологическом отделении',
						handler: function () {
							createDirectionWindow('common.BSME.ForenCorp.SecretaryWP.tools.swCreateCorpBludDirection');
						}
					},
					{
						xtype: 'menuitem',
						text: 'Медико-криминалистическое исследование',
						handler: function () {
							createDirectionWindow('common.BSME.ForenCorp.SecretaryWP.tools.swCreateMedCrimDirection');
						}
					},{
						xtype: 'menuitem',
						text: 'Микроскопическое исследование на наличие диатомового планктона',
						handler: function () {
							createDirectionWindow('common.BSME.ForenCorp.SecretaryWP.tools.swCreateDiamPlanktDirection');
						}
					},{
						xtype: 'menuitem',
						text: 'Судебно-химическое исследование',
						handler: function () {
							createDirectionWindow('common.BSME.ForenCorp.SecretaryWP.tools.swCreateForenChemDirection');
						}
					},{
						xtype: 'menuitem',
						text: 'Биохимическое исследование',
						handler: function () {
							createDirectionWindow('common.BSME.ForenCorp.SecretaryWP.tools.swCreateBioChemDirection');
						}
					},{
						xtype: 'menuitem',
						text: 'Исследование образцов крови в ИФА на антитела к ВИЧ',
						handler: function () {
							createDirectionWindow('common.BSME.ForenCorp.SecretaryWP.tools.swCreateBludSampleDirection');
						}	
					},{
						xtype: 'menuitem',
						text: 'Вирусологическое исследование',
						handler: function () {
							createDirectionWindow('common.BSME.ForenCorp.SecretaryWP.tools.swCreateVirusologicDirection');
						}	
					}

				]
			},
			listeners: {
				click: function(){
					this.showMenu();
				}
			}
			
		}];
		
		
		me.callParent(arguments);
		
	}
})
		