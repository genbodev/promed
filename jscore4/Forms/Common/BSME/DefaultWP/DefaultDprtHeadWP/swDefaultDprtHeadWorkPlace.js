/* 
 * Шаблон рабочего места заведующего отделением БСМЭ
 */


Ext.define('common.BSME.DefaultWP.DefaultDprtHeadWP.swDefaultDprtHeadWorkPlace', {
	extend: 'common.BSME.DefaultWP.BSMEDefaultWP.swBSMEDefaultWorkPlace',
	alias: 'widget.swDefaultDprtWorkPlace',
    autoShow: true,
	maximized: true,
	width: 1000,
	refId: 'DefaultDprtHeadWorkPlace',
	closable: true,
	baseCls: 'arm-window',
    header: false,
	renderTo: Ext.getCmp('inPanel').body,
	callback:Ext.emptyFn,
	id: 'DefaultDprtHeadWorkPlace',
	layout: {
        type: 'fit'
    },
	constrain: true,
	additionalRequestViewPanelButtons: [],
	TabPanelItems: [
		{
			title: 'Все заявки <em>0</em>',
			itemId: 'All',
			iconCls: 'tab_all_icon16'
		},{
			title: 'В работе <em>0</em>',
			itemId: 'Appoint',
			iconCls: 'tab_appoint_icon16'
		}, {
			title: 'Готовые',	//<em>0</em>',
			itemId: 'Archived',
			iconCls: 'tab_check_icon16'
		}
	],
	changeButtonActivityByRequestStatus: function( status ){
		this.disableToolbarButtons();
		log({status:status});
		switch( status ){
			case '':
			case 'New':
				log('new');
//				this.RequestViewPanel.down('button#btnCreateDirectionForensic').enable();
				this.RequestViewPanel.down('button#delete_request_button').enable();
				this.RequestViewPanel.down('button#edit_request_button').enable();
				this.RequestViewPanel.down('button#xml_versions_button').enable();
//				this.RequestViewPanel.down('button#edit_request_button').enable();
//				this.RequestViewPanel.down('button#print_direction_button').disable();
			break;
			
			case 'Check':
//				this.RequestViewPanel.down('button#btnForensicApprove').enable();
//				this.RequestViewPanel.down('button#btnForensicRevision').enable();
//				this.RequestViewPanel.down('button#print_direction_button').enable();
				this.RequestViewPanel.down('button#delete_request_button').disable();
				this.RequestViewPanel.down('button#edit_request_button').disable();
				this.RequestViewPanel.down('button#xml_versions_button').enable();
			break;
			case 'Appoint':
//				this.RequestViewPanel.down('button#print_direction_button').enable();
				this.RequestViewPanel.down('button#delete_request_button').enable();
				this.RequestViewPanel.down('button#edit_request_button').enable();
				this.RequestViewPanel.down('button#xml_versions_button').enable();
//				this.RequestViewPanel.down('button#edit_request_button').enable();
			break;
			case 'Approved':
//				this.RequestViewPanel.down('button#print_direction_button').enable();
				this.RequestViewPanel.down('button#delete_request_button').disable();
				this.RequestViewPanel.down('button#edit_request_button').disable();
				this.RequestViewPanel.down('button#xml_versions_button').enable();
			break;
			default:
//				this.RequestViewPanel.down('button#print_direction_button').disable();
				this.RequestViewPanel.down('button#delete_request_button').disable();
				this.RequestViewPanel.down('button#edit_request_button').disable();
				this.RequestViewPanel.down('button#xml_versions_button').enable();
				break;
		}
	},
	
	// Возвращает выбранную заявку из списка
	retriveSelectedEvnForensic: function(){
		var s = this.RequestListDataview.getSelectionModel();
		if ( !s.hasSelection() ) {
			return false;
		}

		// Т.к. выбрать можно всего одну заявку, получим последнюю отмеченную
		return s.getLastSelected();
	},
	
	// Возвращает значение EvnForensic_id по выбранной заявке из списка
	retrieveEvnForensicId: function(){
		var request = this.retriveSelectedEvnForensic();

		// Т.к. выбрать можно всего одну заявку, получим последнюю отмеченную
		var value = request.get('EvnForensic_id');
		if ( typeof value == 'undefined' ) {
			log('В списке заявок отсутствует поле EvnForensic_id.');
			return false;
		}
		
		return value;
	},
	
	requestListViewItemClick: function(rec) {
//		var sendToCheckButton = this.RequestViewPanel.down('[refId=CheckExpertiseProtocolButton]');
//		sendToCheckButton.setDisabled(!rec.get('ActVersionForensic_id'));
		
		this.RequestViewPanel._Evn_id = rec.get('EvnForensic_id');
		
		//Пока вкладки всего две: "все заявки" и "в работе", любую заявку из этих вкладок можно редактировать
		this.RequestViewPanel.down('button#edit_request_button').enable();
		this.changeButtonActivityByRequestStatus(rec.get('EvnStatus_SysNick'))
	},
	initComponent: function() {
		var me = this;

		this.requestViewPanelButtons = [
//		{
//			text: 'Создать поручение',
//			disabled: true,
//			iconCls: '',
//			xtype: 'button',
//			itemId: 'btnCreateDirectionForensic',
//			handler: function(){
//				
//				if (!me.RequestViewPanel._Evn_id) {
//					return false;
//				}
//				// Окно создания поручения
//				var win = Ext.create('common.BSME.DefaultWP.DefaultDprtHeadWP.tools.swCreateEvnDirectionForensicWindow');
//				win.setBaseFormEvnForensicId( me.RequestViewPanel._Evn_id /*request.get('EvnForensic_id')*/ );
//				win.BaseForm.addListener('success',function(obj,form,action){
//					me.RequestListDataview.getStore().reload();
//				});
//				win.show();
//			}
//		}, {
//			text: 'Готово к выдаче',
//			disabled: true,
//			xtype: 'button',
//			itemId: 'btnForensicApprove',
//			handler: function(){
//				
//				if (!me.RequestViewPanel._Evn_id) {
//					return false;
//				}
//				
//				// Выполняем запрос
//				Ext.Ajax.request({
//					url: '/?c=BSME&m=approveEvnForensic',
//					params: {
//						EvnForensic_id: me.RequestViewPanel._Evn_id /*request.get('EvnForensic_id')*/
//					},
//					callback: function(opt, success, response) {
//						if ( !success ) {
//							Ext.Msg.alert('Ошибка','Во время выполнения запроса произошла ошибка.');
//							return;
//						}
//
//						var result = Ext.JSON.decode(response.responseText);
//						if (!Ext.isEmpty(result.Error_Msg)) {
//							Ext.Msg.alert('Ошибка', result.Error_Msg);
//							return;
//						}
//						
//						Ext.Msg.alert('Сообщение','Заявка одобрена.');
//						me.RequestListDataview.getStore().reload();
//					}
//				});
//			}
//		}, {
//			text: 'На доработку',
//			disabled: true,
//			iconCls: '',
//			xtype: 'button',
//			itemId: 'btnForensicRevision',
//			handler: function(){
//				if (!me.RequestViewPanel._Evn_id) {
//					return false;
//				}
//
//				Ext.Msg.prompt('Введите комментарий', 'Пожалуйста, введите причину отправки на доработку:', function(btn,text){
//					if ( btn != 'ok' ) {
//						return;
//					}
//					// Выполняем запрос
//					Ext.Ajax.request({
//						url: '/?c=BSME&m=revisionEvnForensic',
//						params: {
//							EvnForensic_id: me.RequestViewPanel._Evn_id,
//							EvnStatusHistory_Cause: text
//						},
//						callback: function(opt, success, response) {
//							if ( !success ) {
//								Ext.Msg.alert('Ошибка','Во время выполнения запроса произошла ошибка.');
//								return;
//							}
//							var result = Ext.JSON.decode(response.responseText);
//							if (!Ext.isEmpty(result.Error_Msg)) {
//								Ext.Msg.alert('Ошибка', result.Error_Msg);
//								return;
//							}
//
//							Ext.Msg.alert('Сообщение','Заявка отправлена на доработку.');
//							me.disableAllButtons();
//							me.RequestViewPanel.update('');
//							me.RequestListDataview.getStore().reload();
//						}
//					});
//				}, '', 60);
//			}
//		},{
//			text: 'Печать поручения',
//			disabled: true,
//			iconCls: 'print16',
//			xtype: 'button',
//			itemId: 'print_direction_button',
//			handler: function(){
//				if (!me.RequestViewPanel._Evn_id) {
//					return false;
//				}
//				
//				
//				Ext.Ajax.request({
//					url: '/?c=BSME&m=printEvnDirectionForensic',
//					params: {
//						EvnForensic_id: me.RequestViewPanel._Evn_id,
//						armName: sw.Promed.MedStaffFactByUser.last.ARMName
//					},
//					callback: function(opt, success, response){
//						if ( !success ) {
//							Ext.Msg.alert('Ошибка','Во время загрузки печатной формы произошла непредвиденная ошибка.');
//							return;
//						}
//						var win = window.open();
//						win.document.write(response.responseText);
//						win.print();
//					}
//				});
//			}
//		},
		{
			
			text: 'Удалить заявку',
			disabled: true,
			iconCls: 'delete16',
			xtype: 'button',
			itemId: 'delete_request_button',
			handler: function(){

				if (!me.RequestViewPanel._Evn_id) {
					return false;
				}

				var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт удаление заявки..."}); 
				loadMask.show();
				Ext.Ajax.request({
					url: '/?c=BSME&m=deleteEvnForensic',
					params: {
						EvnForensic_id: me.RequestViewPanel._Evn_id
					},
					callback: function(opt, success, response){
						loadMask.hide();
						if ( !success ) {
							Ext.Msg.alert('Ошибка','Во время удаления заявки произошла непредвиденная ошибка.');
							return;
						}
						//me.disableAllButtons();
						me.RequestViewPanel.update('');
						me.loadRequestViewStore();
					}
				});
			}
		},{
			text: 'Редактировать',
			itemId: 'edit_request_button',
			xtype: 'button',
			iconCls: 'edit16',
			disabled: true,
			handler: function () {
				var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."}); 
				loadMask.show();
				
				setTimeout(function() {
					Ext.create('common.BSME.ForenPers.SecretaryWP.tools.swCreateRequestWindow',{
						EvnForensicSub_id:me.RequestViewPanel._Evn_id,
						callback: function() {
							me.loadRequestViewStore();
							loadMask.hide();
						},
						disbleEvnForensicSub_pid: false
					});
				},1)
			}
		},{
			text: 'Версии документа',
			itemId: 'xml_versions_button',
			xtype: 'button',
			disabled: true,
			handler: function() {
				Ext.create('common.BSME.tools.swBSMEXmlVersionListWindow',{
					EvnForensic_id: me.RequestViewPanel._Evn_id
				});
			}
		}];
	
		me.requestViewPanelButtons = me.requestViewPanelButtons.concat(me.additionalRequestViewPanelButtons);

		me.callParent(arguments);
		
		
	}
})
		