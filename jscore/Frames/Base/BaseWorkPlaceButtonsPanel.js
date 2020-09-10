/**
* Прототип стандартной панели с кнопками для Армов
*
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      23.03.2012
*/

sw.Promed.BaseWorkPlaceButtonsPanel = Ext.extend(Ext.Panel,
{
	title: ' ',
	region: 'west',
	bodyStyle: 'padding-left: 5px',
	border: false,
	width: 48,
	minSize: 48,
	maxSize: 120,
	collapsible: true,
	titleCollapse: true,
	floatable: false,
	animCollapse: false,
	/**	Нужно ли отображать общие экшны
	*/
	enableDefaultActions: true,

	/*listeners: {
		collapse: function() {
			return;
		},
		resize: function (p,nW, nH, oW, oH) {
			return;
		}
	},
	*/
	
	/**	Обработчики для общих экшнов
	*	Формат: 'action' + name + '_Handler'
	*
	*/
	defaultActionHandlers: {
		//Пример: action_RLS_Handler: function() {...}
	},
	
	/**
	*	addActions( Object actions, boolean isTop ) : void
	*/
	addActions: function( actions, isTop ) {
		if ( actions instanceof Object ) {
			var j = 0;
			for( var i in actions ) {
				var iconCls = actions[i].iconCls,//.replace(/16/g, '32'),
					z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: '' }, actions[i]);
				this.actions[i] = new Ext.Action(z);
				if ( isTop ) {
					this.insert(j, new Ext.Button(this.actions[i]));
					j++;
				} else {
					this.add( new Ext.Button(this.actions[i]) );
				}
			}
		}
	},
	
	initComponent: function() {
		var form=this;
		this.actions = {};
		/**
		*******************************
		*	Общие экшны
		*******************************
		*/
		var defaultActions = {
			// РЛС
			action_RLS: {
				nn: 'action_RLS',
				tooltip: lang['prosmotr_rls'],
				text: lang['prosmotr_rls'],
				//iconCls: 'rls32',
				hidden: !(document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
				handler: function() {
					if ( !getWnd('swRlsViewForm').isVisible() )
						getWnd('swRlsViewForm').show();
				}
			},
			// МЭС
			action_Mes: {
				nn: 'action_Mes',
				tooltip: lang['prosmotr'] + getMESAlias(),
				text: lang['prosmotr'] + getMESAlias(),
				iconCls: 'mes32',
				hidden: !(document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
				handler: function() {
					if ( !getWnd('swMesOldSearchWindow').isVisible() )
						getWnd('swMesOldSearchWindow').show();
				}
			}/*,
			// Отчеты //ПЕРЕНЕС В swWorkPlaceWindow.js В РАМКАХ ЗАДАЧИ http://redmine.swan.perm.ru/issues/18509
			action_Report: {
				nn: 'action_Report',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				iconCls: 'report32',
				hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
				handler: function() {
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show();
					}
					else 
					{
						getWnd('reports').load(
						{
							callback: function(success) 
							{
								sw.codeInfo.loadEngineReports = success;
								// здесь можно проверять только успешную загрузку 
								getWnd('swReportEndUserWindow').show();
							}
						});
					}
				}
			}*/
			
		};
		
		// Если при создании компонента мы передаем кнопки, то крейтим их первыми, так как они для текущего АРМа важнее, чем базовые. 
		// TODO: Пока только добавление базовых в конец
		
		var actions = [];
			// k = this.actions.length, 
		
		// Формирование списка всех акшенов 
		if (this.panelActions) {
			for(var key in this.panelActions) {
				var iconCls = this.panelActions[key].iconCls;
				var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, this.panelActions[key]);
				this.actions[key] = new Ext.Action(z);
			}
		}

		for( var i in defaultActions ) {
			var iconCls = defaultActions[i].iconCls,
				handler = ( this.defaultActionHandlers[ i + '_Handler' ] && typeof( this.defaultActionHandlers[ i + '_Handler' ] == 'function' ) )
							? this.defaultActionHandlers[ i + '_Handler' ]
							: defaultActions[i].handler,
				hidden = defaultActions[i].hidden,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
				z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: '', handler: handler, hidden: hidden}, defaultActions[i]);
			this.actions[i] = new Ext.Action(z);
		}
		
		for( var i in this.actions ) {
			actions.push( new Ext.Button(this.actions[i]) );
		}

		Ext.apply(this,	{
			items: [
				new Ext.Button(
				{	
					cls:'upbuttonArr',
					iconCls:'uparrow',
					disabled: false, 
					handler: function() 
					{
						var el = form.findById(form.id + '_hhd');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					layout:'border',
					border: false,
					id: form.id + '_slid',
					height:100,
					items:[{layout:'form',
							region:'center',
							border: false,
							id: form.id + '_hhd',
							layout:'form',
							items:actions}]
				},			
				new Ext.Button(
				{
				cls:'upbuttonArr',
				iconCls:'downarrow',
				style:{width:'48px'},
				disabled: false, 
				handler: function() 
				{
					var el = form.findById(form.id + '_hhd');
					var d = el.body.dom;
					d.scrollTop +=38;
					
				}
				})
			]
		});
		sw.Promed.BaseWorkPlaceButtonsPanel.superclass.initComponent.apply(this, arguments);
	}
});