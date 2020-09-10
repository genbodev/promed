/**
 * Панель информации о пациенте (короткая версия, без раскрытия)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */
Ext6.define('common.EMK.PersonInfoPanelShort', {
	extend: 'Ext6.Panel',
	//controller: 'myview',
	userMedStaffFact: null,
	additionalFields: [],
	animCollapse: false ,
	userCls: 'PersonInfoPanel',
	border: false,
	bodyStyle: 'background: #fff;',
	readOnly: false,
	layout: 'border',
	height: 170,
	button1Callback: Ext6.emptyFn,
	button2Callback: Ext6.emptyFn,
	button3Callback: Ext6.emptyFn,
	button4Callback: Ext6.emptyFn,
	button5Callback: Ext6.emptyFn,
	button1OnHide: Ext6.emptyFn,
	button2OnHide: Ext6.emptyFn,
	button3OnHide: Ext6.emptyFn,
	button4OnHide: Ext6.emptyFn,
	button5OnHide: Ext6.emptyFn,
	collectAdditionalParams: Ext6.emptyFn,
	personId: null,
	serverId: null,
	setParams: function(params) {
		if ( typeof params != 'object' ) {
			return false;
		}

		this.personId = params.Person_id;
		this.serverId = params.Server_id;
	},
	load: function(params) {
		var callback_param = Ext6.emptyFn;

		// сразу скрываем, чтобы не мешалась
		this.PersonFrameContent.hide();
		this.setHeight(45);

		if ( params.callback ) {
			callback_param = params.callback;
		}

		var callback = function() {
			this.setPersonTitle();
			this.checkIsDead();
			callback_param();
		}.createDelegate(this);

		this.personId = params.Person_id;
		this.serverId = params.Server_id || null;
		this.PersonEvn_id = params.PersonEvn_id || null;
		this.userMedStaffFact = params.userMedStaffFact || null;
		this.DataView.getStore().removeAll();
		if (params.dataToLoad) {
			this.DataView.getStore().loadData(params.dataToLoad);
			callback();
		} else {
			this.DataView.getStore().load({
				params: {
					mode: params.mode,
					LoadShort: params.LoadShort,
					loadFromDB: params.loadFromDB,
					Person_id: params.Person_id,
					Server_id: params.Server_id,
					PersonEvn_id: params.PersonEvn_id,
					EvnDirection_id: params.EvnDirection_id,
					additionalFields: params.additionalFields
				},
				callback: callback
			});
		}

		this.setReadOnly(false);
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
			this.disable();
		else
			this.enable();
	},
	getFieldValue: function(field) {
		var result = '';
		if (this.DataView && this.DataView.getStore().getAt(0))
			result = this.DataView.getStore().getAt(0).get(field);
		return result;
	},
	panelButtonClick: function(winType) {
		var params = this.collectAdditionalParams(winType);
		var window_name = '';

		if ( typeof params != 'object' ) {
			params = new Object();
		}

		switch ( winType ) {
			case 1:
				params.callback = this.button1Callback;
				params.onHide = this.button1OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				window_name = 'swPersonCardHistoryWindow';
			break;

			case 2:
				if (!Ext6.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp'))) {
					return false;
				}
				var allow_open = 1;
				var ownerCur = this.ownerCt;
				if( typeof ownerCur != "undefined" ) {
					while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
						ownerCur = ownerCur.ownerCt;
					}
					if (typeof ownerCur.checkRole == 'function') {
						if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
							allow_open = 0;
						}
					}
				}
				if(allow_open == 1)
				{
					params.action = 'edit';
					params.callback = this.button2Callback;
					params.onClose = this.button2OnHide;
					window_name = 'swPersonEditWindow';
				}
				else
					return false;
			break;

			case 3:
				params.callback = this.button3Callback;
				params.onHide = this.button3OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonCureHistoryWindow';
			break;

			case 4:
				params.callback = this.button4Callback;
				params.onHide = this.button4OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonPrivilegeViewWindow';
			break;

			case 5:
				params.callback = this.button5Callback;
				params.onHide = this.button5OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonDispHistoryWindow';
			break;

			default:
				return false;
			break;
		}

		params.Person_id = this.personId;
		params.Server_id = this.serverId;

		if ( getWnd(window_name).isVisible() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: Ext6.emptyFn,
				icon: Ext6.Msg.WARNING,
				msg: langs('Окно уже открыто'),
				title: ERR_WND_TIT
			});

			return false;
		}

		getWnd(window_name).show(params);
	},
	setReadOnly: function (is_read_only)
	{
		if (is_read_only) {
			this.readOnly = true;
			//this.ButtonPanel.items.items[1].disable();
		} else {
			this.readOnly = false;
			//this.ButtonPanel.items.items[1].enable();
		}
	},
	setPersonTitle: function()
	{
		if (Ext6.isEmpty(this.personId)) {
			this.setTitle('...', null, '', '');
			return;
		}
		var name = this.getFieldValue('Person_Surname').charAt(0).toUpperCase() + this.getFieldValue('Person_Surname').slice(1).toLowerCase()+' '+ this.getFieldValue('Person_Firname').charAt(0).toUpperCase() + this.getFieldValue('Person_Firname').slice(1).toLowerCase()+' '+this.getFieldValue('Person_Secname').charAt(0).toUpperCase() + this.getFieldValue('Person_Secname').slice(1).toLowerCase(),
			bday = Ext6.util.Format.date(this.getFieldValue('Person_Birthday'), "d.m.Y"),
			sex = this.getFieldValue('Sex_id')=='2'?'woman':'man',
			age = this.getFieldValue('personAgeText'),
			dday = Ext6.util.Format.date(this.getFieldValue('Person_deadDT'), "d.m.Y"),
			isDead = !!this.getFieldValue('Person_IsDead'),
			labels = getRegionNick()=='vologda' ? this.getFieldValue('PersLabels') : '',
			monitoring =
				{ 	showIcon: this.getFieldValue('RemoteMonitoring_Label_id')==1 ? (this.getFieldValue('RemoteMonitoring_Chart_id') ? true : false) : false,
					active: this.getFieldValue('RemoteMonitoring_Chart_id') ? (this.getFieldValue('RemoteMonitoring_ChartEndDT') ? '' : '-active') : '',
					group_id: this.getFieldValue('RemoteMonitoring_PersonModel_id'),
					outType: this.getFieldValue('RemoteMonitoring_OutTypeName'),
					endDT: this.getFieldValue('RemoteMonitoring_ChartEndDT'),
					tip: ( (this.getFieldValue('RemoteMonitoring_Chart_id') && this.getFieldValue('RemoteMonitoring_ChartEndDT') ) ? 
						'Исключен из регистра А/Д '+this.getFieldValue('RemoteMonitoring_ChartEndDT')+' '+this.getFieldValue('RemoteMonitoring_OutTypeName')
						:'Состоит в регистре А/Д'
						)
				};
			switch(Number(monitoring.group_id)) {
				case 1: monitoring.group = 'гр. I';break;
				case 2: monitoring.group = 'гр. II';break;
				case 3: monitoring.group = 'гр. III';break;
				default: monitoring.group = '';
			}
		this.setTitle(name, bday, sex, age, dday, isDead, monitoring, labels);
	},
	setTitle: function(name, bday, sex, age, dday, isDead, monitoring, labels)
	{
		var personTpl = new Ext6.XTemplate(
			'<span class="person-frame-title-expander" style="margin-left: 12px;">',
			'<b class="personpanel_sex person_','{sex}','" style="left: 0;"></b>',
			'{name} ',
			'<span style="color: #808080; font-size: 14px;">{bday} ({age})</span>',
			
			'<tpl if="values.isDead"><span style="color: #F70707;font-size: 14px;margin-left: 4px;">Дата смерти {dday}</span></tpl>',
			'<tpl if="values.monitoring.showIcon">',//индикатор дистанц.мониторинга
			'<span data-qtip="{monitoring.tip}">',
				'<b class="dm-status-label dm-status-label-ad{monitoring.active}"></b>',
				'<span class="dm-status-name">{monitoring.group}</span>',
			'</span>',
			'</tpl>',
			'<tpl if="values.labels">',
			'<span>',
				'<b class="person-labels" id="'+this.id+'-person-labels" data-qtip="'+(Ext6.isEmpty(labels) ? '':labels)+'"></b>',
			'</span>',
			'</tpl>'
		);
		personTpl.overwrite(this.PersonFrameTitle.items.items[0].body, {name: name, bday: bday, sex: sex, age: age, dday: dday, isDead: isDead, monitoring: monitoring, labels: labels});
	},
	showLabels: function(panel_id, labels) {
		if(!Ext6.isEmpty(labels)) {
			labels = labels.split('|');
			if(!this.labeltip && labels.length>0) {
				this.labeltip = Ext6.create('Ext6.tip.ToolTip', {
					html: '',
					autoHide: true,
					closable: false
				});
			}
			this.labeltip.setHtml(labels.join('<br>'));
		}
	},
	forReceptCommonAstra: false,
	initComponent: function() {
		var form = this,
			conf = form.initialConfig;

		form.fields = [
			{name: 'Person_id'},
			{name: 'Server_pid'},
			{name: 'Document_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Document_Num'},
			{name: 'Document_Ser'},
			{name: 'KLAreaType_id'},
			{name: 'Lpu_Nick'},
			{name: 'Lpu_id'},
			{name: 'LpuRegion_Name'},
			{name: 'OrgDep_Name'},
			{name: 'OrgSmo_Name'},
			{name: 'Person_Age'},
			{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonCard_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonEvn_id'},
			{name: 'Server_id'},
			{name: 'Person_Firname'},
			{name: 'Person_Job'},
			{name: 'Person_PAddress'},
			{name: 'PAddress_Address'},
			{name: 'Person_Phone'},
			{name: 'JobOrg_id'},
			{name: 'Person_Post'},
			{name: 'Person_RAddress'},
			{name: 'RAddress_Address'},
			{name: 'Person_Secname'},
			{name: 'Person_Snils'},
			{name: 'Person_Surname'},
			{name: 'Person_EdNum'},
			{name: 'SurNameLetter'},
			{name: 'Polis_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Polis_endDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Polis_Num'},
			{name: 'Polis_Ser'},
			{name: 'OmsSprTerr_id'},
			{name: 'OmsSprTerr_Code'},
			{name: 'Sex_Code'},
			{name: 'Sex_id'},
			{name: 'SocStatus_id'},
			{name: 'Sex_Name'},
			{name: 'SocStatus_Name'},
			{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonCloseCause_id'},
			{name: 'Person_IsDead'},
			{name: 'Person_IsBDZ'},
			{name: 'PersonEncrypHIV_Encryp'},
			{name: 'Person_IsUnknown', type: 'int'},
			{name: 'Person_IsAnonym', type: 'int'},
			{name: 'RegisterAD', type: 'int'},
			{name: 'PersLabels'},
			{name: 'personAgeText'}
		];

		form.additionalFields.forEach(function(item) {
			form.fields.push({name: item});
		});

		var cur_tpl = new Ext6.XTemplate(
			'<tpl for=".">',
				'<div class="PersonFrame">',
				'<tpl if="this.allowPersonEncrypHIV() == true">',
				'<div><i>Шифр:</i> {PersonEncrypHIV_Encryp}</div>',
				'</tpl>',
				'<tpl if="this.allowPersonEncrypHIV() == false">',
				'<div data-qtip="{Person_Surname} {Person_Firname} {Person_Secname}"><i>ФИО:</i> {Person_Surname} {Person_Firname} {Person_Secname}</div>',
				'<div data-qtip="{[Ext6.util.Format.date(values.Person_Birthday, \"d.m.Y\")]}"><i>Д/р:</i> {[Ext6.util.Format.date(values.Person_Birthday, "d.m.Y")]}</div>',
				'<div data-qtip="{Sex_Name}"><i>Пол:</i> {Sex_Name}</div>',
				'{[(String(values.Person_deadDT) != "null" ? "<div>Дата смерти: <span color=red>" + String(Ext6.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</span></div>" : "")]}',
				'{[(String(values.Person_closeDT) != "null" ? "<div>Дата закрытия: <span color=red>" + String(Ext6.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</span></div>" : "")]}',
				'<div data-qtip="{SocStatus_Name}"><i>Соц. статус:</i> {SocStatus_Name}</div>',
				'<div data-qtip="{[snilsRenderer(values.Person_Snils)]}"><i>СНИЛС:</i> {[snilsRenderer(values.Person_Snils)]}</div>',
				'<div data-qtip="{Person_RAddress}"><i>Регистрация:</i> {Person_RAddress}</div>',
				'<div data-qtip="{Person_PAddress}"><i>Проживает:</i> {Person_PAddress}</div>',
				'<div data-qtip="{Person_Phone}"><i>Телефон:</i> {Person_Phone}</div>',
				"<div data-qtip='{[this.getPolisStr(values)]}'>",
					'<i>Полис:</i> {[this.getPolisStr(values)]}',
				"</div>",
				"<div data-qtip='{[this.getDocumentStr(values)]}'>",
				'<i>Документ:</i> {[this.getDocumentStr(values)]}',
				"</div>",
				'<div data-qtip="{Person_Job}"><i>Работа:</i> {Person_Job}</div>',
				'<div data-qtip="{Person_Post}"><i>Должность:</i> {Person_Post}</div>',
				'<div data-qtip="{Lpu_Nick}" class="person-info-MO"><i>МО:</i> <span style ="overflow: hidden; text-overflow: ellipsis; width: max-content; max-width: 115px; white-space: nowrap;display: inline-block;line-height: 14px; top: 2px; position: relative;">{Lpu_Nick}</span>',
					'<tpl if="this.isNotDead() == true">',
						"<a class='change-attachment' onclick='{[this.onClickMO()]}' >Изменить прикрепление</a>",
					'</tpl>',
				'</div>',
				'<div><i>Участок:</i> {LpuRegion_Name} ({[Ext6.util.Format.date(values.PersonCard_begDate, "d.m.Y")]})</div>',
				'<div data-qtip="{NewslatterAccept}"><i>Согласие на получение уведомлений:</i> {NewslatterAccept}</div>',
				'</tpl>',
				'</div>',
				'</tpl>',
				{
					allowPersonEncrypHIV: function () {
						return (!Ext6.isEmpty(form.getFieldValue('PersonEncrypHIV_Encryp')));
					},
					isNotDead: function () {
						return !form.checkIsDead();
					},
					getPolisStr: function (values) {
						var StrPolis = values.Polis_Ser+' '+values.Polis_Num;
						if(values.Polis_begDate || values.OrgSmo_Name)
							StrPolis += ' Выдан: '+Ext6.util.Format.date(values.Polis_begDate, "d.m.Y")+' '+values.OrgSmo_Name+'.';
						if(values.Polis_endDate)
							StrPolis += ' Закрыт: '+Ext6.util.Format.date(values.Polis_endDate, "d.m.Y");
						return StrPolis;
						//return StrPolis.replace(new RegExp('"','g'),"'");
					},
					getDocumentStr: function (values) {
						var StrDocument = values.Document_Ser+' '+values.Document_Num;
						if(values.Document_begDate || values.OrgDep_Name)
							StrDocument += ' Выдан: '+Ext6.util.Format.date(values.Document_begDate, "d.m.Y")+' '+values.OrgDep_Name+'.';
						return StrDocument;
					},
					onClickMO: function() {
						return 'Ext6.getCmp("' + form.getId() + '").panelButtonClick(1);'
					}
				}
		);
		if(this.forReceptCommonAstra)
		{
			cur_tpl = new Ext6.XTemplate(
				'<tpl for=".">',
				'<div class="PersonFrame">',
				'<div>&nbsp;&nbsp;&nbsp;<span style="color: blue; span-weight: bold;">{Person_Firname} {Person_Secname} {SurNameLetter} </span></div>' ,
				'<div>&nbsp;&nbsp;&nbsp;Д/р: <span style="color: blue;">{[Ext6.util.Format.date(values.Person_Birthday, "d.m.Y")]}</span> г.р. </div>' ,
				'<div>&nbsp;&nbsp;&nbsp;СНИЛС: <span style="color: blue;">{[snilsRenderer(values.Person_Snils)]}</span></div>' ,
				'<div>&nbsp;&nbsp;&nbsp;Полис: <span style="color: blue;">{Polis_Ser} {Polis_Num}</span>&nbsp; ЕНП: <span style="color: blue;">{Person_EdNum}</span></div>',
				'</div>',
				'</tpl>',
				{
					allowPersonEncrypHIV: function () {
						return (!Ext6.isEmpty(form.getFieldValue('PersonEncrypHIV_Encryp')));
					}
				}
			);
		}

		this.PersonFrameTitle = new Ext6.Panel({
			border: true,
			frame: false,
			height: 45,
			region: 'north',
			layout: 'border',
			cls: 'PersonFrameTitle',
			bodyStyle: 'border-width: 0 0 1px 0',
			items: [
				{
					height: 50,
					html: '',
					width: '100%',
					minWidth: 380,
					region: 'west',
					border: false,
					padding: 0,
					bodyStyle: 'background: #fff; font-size: 16px; padding: 12px 20px;'
				}
			]
		});

		this.DataView = new Ext6.DataView({
			border: false,
			frame: false,
			height: 132,
			itemSelector: 'div.PersonFrame',
			style: 'margin-right: 20px',
			region: 'center',
			store: {
				fields: form.fields,
				proxy: {
					type: 'ajax',
					extraParams: {
						mode: 'PersonInformationPanelShort',
						additionalFields: Ext6.util.JSON.encode(form.additionalFields)
					},
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Common&m=loadPersonData',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				}
			},
			tpl: cur_tpl
		});
		var domElButt ='</span><span class="gray_sp" style="position: absolute;left: 125px; top: 8px">';

		this.PersonFrameContent = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			cls: 'person-frame-content',
			border: false,
			frame: false,
			bodyStyle: 'background: #fff;',
			height: 100,
			items: [ this.DataView ]
		});
		
		Ext6.apply(this, {
			items: [ this.PersonFrameContent, this.PersonFrameTitle ]
		});

		form.callParent(arguments);
	},
	getPersonId: function(){
		return this.personId;
	},
	checkIsDead: function() {
		var me = this,
			isDead = !!me.getFieldValue('Person_IsDead');
		me.deadHistory = isDead;

		return isDead;
	}
});