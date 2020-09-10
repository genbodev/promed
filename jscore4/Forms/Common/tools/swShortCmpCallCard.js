/*Талон вызова*/
Ext.define('sw.tools.swShortCmpCallCard', {
	alias: 'widget.swShortCmpCallCard',
	extend: 'Ext.window.Window',
	title: 'Талон вызова',
	width: 700,
	height: 600,
	layout: 'fit',
	modal: true,

	//установка значений по умолчанию (add)
	setDefaultValues: function(form)
	{

	},

	//загрузка карты вызова (view/edit)
	loadCmpCallCardData: function(baseForm, card_id){
		
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=loadCmpCallCardEditForm',
			params: {CmpCallCard_id: card_id},
			callback: function(opt, success, response) {
				if (!success){
					return;
				}

				var me = this,
					response_obj = Ext.JSON.decode(response.responseText)[0],
					cityCombo = baseForm.findField('dCityCombo'),
					streetsCombo = baseForm.findField('dStreetsCombo'),
					acceptBtn = me.down('button[refId=markAccept]'),
					backToDubl = me.down('button[refId=backToDubl]'),
					cmpParentCard = me.down('label[refId=cmpParentCard]'),
					saveShortCardBtn = me.down('button[refId=saveShortCardBtn]'),
					showAudioCallRecordWindowBtn = me.down('button[name=showAudioCallRecordWindow]');

				if(!response_obj) return;

				cityCombo.store.getProxy().extraParams = {
					KLRgn_id: response_obj.KLRgn_id,
					KLSubRgn_id: response_obj.KLSubRgn_id,
					KLCity_id: response_obj.KLCity_id,
					KLTown_id: response_obj.KLTown_id,
					region_id : getGlobalOptions().region.number
				};

				if(response_obj.CmpCallCard_rid && response_obj.CmpCallType_Code === '14'){
					acceptBtn.show();
					cmpParentCard.show();
				}else{
					acceptBtn.hide();
					cmpParentCard.hide();
				};

				cityCombo.reset();
				cityCombo.store.removeAll();
				cityCombo.store.load({
					callback: function(rec, operation, success){
						if ( this.getCount() != 1 || !rec) {
							return;
						}
						cityCombo.setValue(rec[0].get('Town_id'));

						streetsCombo.bigStore.getProxy().extraParams = {
							town_id: rec[0].get('Town_id'),
							Lpu_id: sw.Promed.MedStaffFactByUser.current.Lpu_id
						};

						streetsCombo.bigStore.load({
							callback: function(rec, operation, success) {
								var rec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', response_obj.StreetAndUnformalizedAddressDirectory_id);
								if (rec){
									streetsCombo.store.removeAll();
									streetsCombo.store.add(rec);
									streetsCombo.setValue(rec.get('StreetAndUnformalizedAddressDirectory_id'));
								}
								else{
									streetsCombo.setRawValue(response_obj.CmpCallCard_Ulic);
								}
							}
						});
					}
				});

				showAudioCallRecordWindowBtn.setVisible(response_obj.CmpCallRecord_id)

				response_obj.CmpCallCard_prmDate = Ext.Date.parse(response_obj.CmpCallCard_prmDate, "Y-m-d H:i:s");
				response_obj.CmpCallPlaceType_id = parseInt(response_obj.CmpCallPlaceType_id);
				response_obj.Sex_id = parseInt(response_obj.Sex_id);
				response_obj.CmpCallType_id = parseInt(response_obj.CmpCallType_id);
				response_obj.CmpReason_id = parseInt(response_obj.CmpReason_id);
				response_obj.CmpCallerType_id = response_obj.CmpCallerType_id ? parseInt(response_obj.CmpCallerType_id) : response_obj.CmpCallCard_Ktov;
				response_obj.LpuBuilding_id = parseInt(response_obj.LpuBuilding_id);
				response_obj.Person_Age = parseInt(response_obj.Person_Age);
				response_obj.EmergencyTeam_id = parseInt(response_obj.EmergencyTeam_id);
				response_obj.DPMedPersonal_id = parseInt(response_obj.DPMedPersonal_id);
				response_obj.Lpu_hid = parseInt(response_obj.Lpu_hid);

				var CmpCallCard_TransTime = Ext.Date.parse(response_obj.CmpCallCard_DateTper, 'Y-m-d H:i:s'),
					CmpCallCard_GoTime = Ext.Date.parse(response_obj.CmpCallCard_DateVyez, 'Y-m-d H:i:s'),
					CmpCallCard_ArriveTime = Ext.Date.parse(response_obj.CmpCallCard_DatePrzd, 'Y-m-d H:i:s'),
					CmpCallCard_TransportTime = Ext.Date.parse(response_obj.CmpCallCard_DateTgsp, 'Y-m-d H:i:s'),
					CmpCallCard_EndTime = Ext.Date.parse(response_obj.CmpCallCard_DateTisp, 'Y-m-d H:i:s'),
					CmpCallCard_Hospitalized = Ext.Date.parse(response_obj.CmpCallCard_HospitalizedTime, 'Y-m-d H:i:s');

				if(CmpCallCard_TransTime){
					response_obj.CmpCallCard_DateTper =  CmpCallCard_TransTime;
					response_obj.CmpCallCard_DateTperTime = CmpCallCard_TransTime;
					baseForm.findField('CmpCallCard_DateTper').setReadOnly(true);
					baseForm.findField('CmpCallCard_DateTperTime').setReadOnly(true);
				}

				if(CmpCallCard_GoTime){
					response_obj.CmpCallCard_DateVyez =  CmpCallCard_GoTime;
					response_obj.CmpCallCard_DateVyezTime = CmpCallCard_GoTime;
					baseForm.findField('CmpCallCard_DateVyez').setReadOnly(true);
					baseForm.findField('CmpCallCard_DateVyezTime').setReadOnly(true);
				}

				if(CmpCallCard_ArriveTime){
					response_obj.CmpCallCard_DatePrzd =  CmpCallCard_ArriveTime;
					response_obj.CmpCallCard_DatePrzdTime = CmpCallCard_ArriveTime;
					baseForm.findField('CmpCallCard_DatePrzd').setReadOnly(true);
					baseForm.findField('CmpCallCard_DatePrzdTime').setReadOnly(true);
				}

				if(CmpCallCard_TransportTime){
					response_obj.CmpCallCard_DateTgsp =  CmpCallCard_TransportTime;
					response_obj.CmpCallCard_DateTgspTime = CmpCallCard_TransportTime;
					baseForm.findField('CmpCallCard_DateTgsp').setReadOnly(true);
					baseForm.findField('CmpCallCard_DateTgspTime').setReadOnly(true);
				}

				if(CmpCallCard_EndTime){
					response_obj.CmpCallCard_DateTisp =  CmpCallCard_EndTime;
					response_obj.CmpCallCard_TispTime = CmpCallCard_EndTime;
					baseForm.findField('CmpCallCard_DateTisp').setReadOnly(true);
					baseForm.findField('CmpCallCard_TispTime').setReadOnly(true);
				}

				if(CmpCallCard_Hospitalized){
					response_obj.CmpCallCard_HospitalizedTime =  CmpCallCard_Hospitalized;
					response_obj.CmpCallCard_HospitalizedTimeTime = CmpCallCard_Hospitalized;
					baseForm.findField('CmpCallCard_HospitalizedTime').setReadOnly(true);
					baseForm.findField('CmpCallCard_HospitalizedTimeTime').setReadOnly(true);
				}

				if(response_obj.pcCmpCallCard_Numv){
					cmpParentCard.setText('Перв. вызов №: <a href="#">'+response_obj.pcCmpCallCard_Numv+'</a>', false);
				}

				if(response_obj.CmpCallCard_IsExtra){
					response_obj.CmpCallCard_IsExtra = parseInt(response_obj.CmpCallCard_IsExtra);
				}
				if(response_obj.CmpCallCard_IsPoli){
					response_obj.CmpCallCard_IsPoli = (response_obj.CmpCallCard_IsPoli == 2);
				}
				if(response_obj.CmpCallCard_IsPassSSMP){
					response_obj.CmpCallCard_IsPassSSMP = (response_obj.CmpCallCard_IsPassSSMP == 2);
				}


				if(response_obj.EmergencyTeam_id){
					baseForm.findField('EmergencyTeam_id').setValue(response_obj.EmergencyTeam_id);
					baseForm.findField('EmergencyTeam_Num').setValue(response_obj.EmergencyTeam_Num);
					// baseForm.findField('EmergencyTeam_id').setReadOnly(true);
				}
				baseForm.reset();
				baseForm.setValues(response_obj);
				var personBirthdayYearAgeField = baseForm.findField('Person_Age');
				personBirthdayYearAgeField.setValue(me.person_age(response_obj.CmpCallCard_prmDate, response_obj.Person_Birthday, response_obj.Person_Age));
				if(response_obj.MedService_id){
					var recordIndex = baseForm.findField('selectNmpCombo').getStore().findBy(function(rec,id){
							return rec.get('MedService_id') == response_obj.MedService_id;
						}),
						record = baseForm.findField('selectNmpCombo').getStore().getAt(recordIndex);
					baseForm.findField('selectNmpCombo').select(record);
				}
				if(response_obj.Lpu_smpid){
					var recordIndex = baseForm.findField('Lpu_smpid').getStore().findBy(function(rec,id){
							return rec.get('Lpu_id') == response_obj.Lpu_smpid;
						}),
						record = baseForm.findField('Lpu_smpid').getStore().getAt(recordIndex);
					baseForm.findField('Lpu_smpid').select(record);
				}
				if(response_obj.Lpu_ppdid){
					var recordIndex = baseForm.findField('Lpu_ppdid').getStore().findBy(function(rec,id){
							return rec.get('Lpu_id') == response_obj.Lpu_ppdid;
						}),
						record = baseForm.findField('Lpu_ppdid').getStore().getAt(recordIndex);
					baseForm.findField('Lpu_ppdid').select(record);
				}
				me.setEnabledCallCardFields();

				//if(callback) callback();
				return true;

			}.bind(this)
		})
	},
	
	setValues: function(response_obj, baseForm){

		//записываем значения в поля дата/время
		baseForm.findField('CmpCallCard_prmDate').setValue(Ext.Date.parse(response_obj.CmpCallCard_prmDate, "Y-m-d H:i:s"));
		baseForm.findField('CmpCallCard_prmTime').setValue(response_obj.CmpCallCard_prmTime);
		baseForm.findField('CmpCallCard_Numv').setValue(response_obj.CmpCallCard_Numv);
		baseForm.findField('CmpCallCard_Ngod').setValue(response_obj.CmpCallCard_Ngod);

		//поля невидымки
		baseForm.findField('CmpCallCard_id').setValue(response_obj.CmpCallCard_id);
		baseForm.findField('CmpCallCard_Telf').setValue(response_obj.CmpCallCard_Telf);
		baseForm.findField('CmpCallCard_Comm').setValue(response_obj.CmpCallCard_Comm);
		baseForm.findField('CmpLpu_Name').setValue(response_obj.CmpLpu_Name);
		baseForm.findField('CmpLpu_id').setValue(response_obj.Lpu_id);
		baseForm.findField('Person_Age').setValue(response_obj.Person_Age);
		baseForm.findField('Person_id').setValue(response_obj.Person_id);
		baseForm.findField('Person_isOftenCaller').setValue(response_obj.Person_isOftenCaller);
		
		baseForm.findField('CmpCallRecord_id').setValue(response_obj.CmpCallRecord_id);

		baseForm.findField('CmpCallCard_rid').setValue(response_obj.CmpCallCard_rid);


		baseForm.findField('CmpCallCard_Dom').setValue(response_obj.CmpCallCard_Dom);
		baseForm.findField('CmpCallCard_Korp').setValue(response_obj.CmpCallCard_Korp);
		baseForm.findField('CmpCallCard_Kvar').setValue(response_obj.CmpCallCard_Kvar);
		baseForm.findField('CmpCallCard_Podz').setValue(response_obj.CmpCallCard_Podz);
		baseForm.findField('CmpCallCard_Etaj').setValue(response_obj.CmpCallCard_Etaj);
		baseForm.findField('CmpCallCard_Kodp').setValue(response_obj.CmpCallCard_Kodp);

		//пациент
		baseForm.findField('Person_SurName').setValue(response_obj.Person_SurName);
		baseForm.findField('Person_FirName').setValue(response_obj.Person_FirName);
		baseForm.findField('Person_SecName').setValue(response_obj.Person_SecName);
		//baseForm.findField('Person_Birthday_YearAge').setValue(response_obj.Person_Birthday);
		
		baseForm.findField('sexCombo').setValue( parseInt(response_obj.Sex_id));
		//baseForm.findField('Person_Age_From').setValue(response_obj.Person_Age)
		//baseForm.findField('Person_Age_To').setValue(response_obj.Person_Age)

		if ( response_obj.Polis_Num ) {
			var Polis_Num = response_obj.Polis_Num;
			if ( response_obj.Polis_Ser ) {
				Polis_Num = response_obj.Polis_Ser + ' ' + Polis_Num;
			}
			baseForm.findField('Polis_Number_fake').setValue(Polis_Num);
		} else if ( response_obj.Polis_EdNum ) {
			baseForm.findField('Polis_Number_fake').setValue(response_obj.Polis_EdNum);
		} else {
			baseForm.findField('Polis_Number_fake').setValue('');
		}

		//вызов
		baseForm.findField('CmpCallType_id').setValue(parseInt(response_obj.CmpCallType_id));
		if(parseInt(response_obj.CmpCallType_id) == 0) {
			baseForm.findField('CmpCallType_id').setValue(response_obj.CmpCallerType_Name);
		}
		//baseForm.findField('CmpCallerType_Name').setValue(response_obj.CmpCallerType_Name);
		baseForm.findField('CmpCallPlaceType_Name').setValue(response_obj.CmpCallPlaceType_Name);
		if (response_obj.CmpReason_id != null) { baseForm.findField('reasonCombo').setValue( parseInt(response_obj.CmpReason_id)); }

		baseForm.findField('lpuLocalCombo').setValue(response_obj.Lpu_ppdid);


		if (response_obj.CmpCallerType_id != null){
			baseForm.findField('CmpCallerType_id').setValue(parseInt(response_obj.CmpCallerType_id));
		}
		if (response_obj.CmpCallCard_Ktov != null){
			baseForm.findField('CmpCallCard_Ktov').setValue(response_obj.CmpCallCard_Ktov);
			baseForm.findField('CmpCallerType_id').setValue(response_obj.CmpCallCard_Ktov);
		}

		var person_age = response_obj.Person_Age + ' л.';
		if (!Ext.isEmpty(response_obj.Person_Birthday)) {
			person_age = this.getPersonAge(
				Ext.Date.parse(response_obj.Person_Birthday, 'd.m.Y'),
				baseForm.findField('CmpCallCard_prmDate').getValue()
			);
		}

		baseForm.findField('Person_Birthday_YearAge').setValue(person_age);
		baseForm.findField('CmpCallCard_Telf').setValue(response_obj.CmpCallCard_Telf);
		baseForm.findField('CmpCallCard_Comm').setValue(response_obj.CmpCallCard_Comm);
	},
	
	setDisabledFields: function(form){
		var allCmps = form.getFields();
		allCmps.filterBy(function(o, k){
			o.setReadOnly(true)
			Ext.EventManager.purgeElement(o.getEl())
		})
	},
	setEnabledCallCardFields: function(){
		var me = this,
			CmpCallCard_IsExtra = me.baseForm.findField('CmpCallCard_IsExtra').getValue(),
			LpuBuilding_id = me.baseForm.findField('LpuBuilding_id').getValue(),
			Lpu_ppdid = me.baseForm.findField('Lpu_ppdid').getValue(),
			isPoli = me.baseForm.findField('CmpCallCard_IsPoli').getValue(),
			CmpCallCard_IsPassSSMP = me.baseForm.findField('CmpCallCard_IsPassSSMP').getValue(),
			CmpCallTypeField = me.baseForm.findField('CmpCallType_id'),
			CmpCallTypeRec = CmpCallTypeField.findRecordByValue(CmpCallTypeField.getValue()),
			callTypeWithoutLpu = (CmpCallTypeRec && CmpCallTypeRec.get(CmpCallTypeField.codeField).inlist([6,15,16,17])); //Консультативное, Консультативный, Справка, Абонент отключился

		var is112 = (me.baseForm.findField('CmpCallCardStatusType_id').getValue() == 20),
			required112Fields = ['CmpCallCard_IsExtra','CmpReason_id','LpuBuilding_id','CmpCallCard_IsPoli','Lpu_ppdid',
				'selectNmpCombo','CmpCallCard_IsPassSSMP','Lpu_smpid',/*'dCityCombo','dStreetsCombo','CmpCallCard_Dom','CmpCallCard_Korp',
				 'CmpCallCard_Kvar','CmpCallCard_Podz','CmpCallCard_Etaj','CmpCallCard_Kodp','CmpCallPlaceType_id','CmpCallerType_id','CmpCallCard_Comm',*/
				'CmpCallCard_Telf'];

		required112Fields.forEach(function(item){
			var field = me.baseForm.findField(item);

			switch(item){
				case 'LpuBuilding_id':

					var smpBuildingVisible = (is112 && ((CmpCallCard_IsExtra == 1) || ((CmpCallCard_IsExtra == 2) && !Lpu_ppdid && !isPoli )));
					field.setReadOnly(!smpBuildingVisible);
					field.setDisabled(!smpBuildingVisible);


					var smpBuildingAllowBlank = (is112 && (CmpCallCard_IsExtra == 1) && !CmpCallCard_IsPassSSMP && !callTypeWithoutLpu);
					//field.allowBlank = (!smpBuildingAllowBlank);

					break;
				case 'CmpCallCard_IsPoli':

					var isPoliVisible = (is112 && (CmpCallCard_IsExtra == 2));
					field.setReadOnly(!isPoliVisible);
					field.setDisabled(!isPoliVisible);

					break;
				case 'Lpu_ppdid':

					var nmpBuildingVisible = (is112 && (CmpCallCard_IsExtra == 2)/* && !LpuBuilding_id*/ && !CmpCallCard_IsPassSSMP );
					field.setReadOnly(!nmpBuildingVisible);
					field.setDisabled(!nmpBuildingVisible);
					//field.allowBlank = !nmpBuildingVisible;

					break;
				case 'selectNmpCombo':

					var nmpServiceVisible = (is112 && CmpCallCard_IsExtra == 2 && !LpuBuilding_id && !CmpCallCard_IsPassSSMP);
					field.setReadOnly(!nmpServiceVisible);
					field.setDisabled(!nmpServiceVisible);

					break;
				case 'CmpCallCard_IsPassSSMP':

					var isPassSSMPVisible = (is112 && getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO == 1);
					field.setVisible(isPassSSMPVisible);
					field.setReadOnly(!isPassSSMPVisible);
					field.setDisabled(!isPassSSMPVisible);

					break;
				case 'Lpu_smpid':
					var Lpu_smpidVisible = (is112 && CmpCallCard_IsPassSSMP);
					//field.setVisible(Lpu_smpidVisible);
					//field.setReadOnly(!Lpu_smpidVisible);
					field.setDisabled(!Lpu_smpidVisible);
					field.allowBlank = !Lpu_smpidVisible;
					break;
				case 'CmpReason_id':
					field.setReadOnly(!is112);
					field.setDisabled(!is112);
					field.allowBlank = !is112;
					break;
				default:
					field.setReadOnly(!is112);
					field.setDisabled(!is112);
					break;

			}
			field.validate();

		});
	},
	person_age: function(date, Person_Birthday, Person_Age){
		var person_age = 'Возраст не определен';
		if ( Person_Birthday){

			if( Person_Age > 0)
				person_age = Person_Age + ' л.';
			else{
				var dateOfBirth = Ext.Date.parse( Person_Birthday, 'd.m.Y'),
					daysAge = Math.floor(Math.abs(dateOfBirth-date)/(1000*60*60*24)),
					mounthAge = Math.floor(Math.abs(date.getMonthsBetween(dateOfBirth)));

				if(mounthAge)
					person_age = mounthAge+' м.';
				else
					person_age = daysAge+ ' д.';
			}
		}
		else
		if( Person_Age > 0)
			person_age = Person_Age + ' л.';

		return person_age;
	},
	initComponent: function() {
		var me = this;


		me.on('show', function(){
			
			me.baseForm = this.CmpCallCardFormPanel.getForm();

			var config = arguments[0],
				view = config.view;

			switch (view){
				case 'view' : {
					me.setDisabledFields(me.baseForm)
					me.loadCmpCallCardData(me.baseForm, config.card_id)
					//bottomToolbar.getComponent('saveBtn').show()
					//bottomToolbar.getComponent('helpBtn').show()
					break
				}
				case 'edit' : {/*console.log('edit');*/ break}
				case 'new' : {
					me.setDefaultValues(me.baseForm)
					break
				}
			}
		});
		var smpUnitsNestedCombo = Ext.create('sw.SmpUnitsNested', {
			name: 'LpuBuilding_id',
			labelWidth: 90,
			flex: 1,
			fieldLabel: 'Подразделение СМП',
			labelAlign: 'right',
			displayTpl: '<tpl for="."> {LpuBuilding_Code}. {LpuBuilding_Name} </tpl>',
			tpl: '<tpl for="."><div class="x-boundlist-item">' +
			'<font color="red">{LpuBuilding_Code}</font> {LpuBuilding_Name}' +
			'</div></tpl>',
			listeners: {
				render: function (cmp) {
					cmp.store.proxy.url = '?c=CmpCallCard4E&m=loadSmpUnitsNestedALL';
					cmp.store.load();
				}
			}
		});

		var smpRegionUnitsCombo = Ext.create('sw.RegionSmpUnits',{
				name: 'LpuBuilding_id',
				labelWidth: 90,
				flex: 1,
				fieldLabel: 'Подразделение СМП',
				labelAlign: 'right',
				displayTpl: '<tpl for=".">{LpuBuilding_Name}/{Lpu_Nick}</tpl>',
				tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
				'{LpuBuilding_Name}/{Lpu_Nick}'+
				'</div></tpl>'
			}
		);

		var bottomToolbar = Ext.create('Ext.toolbar.Toolbar', {
			dock: 'bottom',
			items: [
				{
					xtype: 'button',
					refId: 'markAccept',
					iconCls: 'ok16',
					text: 'Ознакомлен',
					hidden: true
				},
				{
					xtype: 'label',
					refId: 'cmpParentCard',
					html: '',
					hidden: true
				},
				{
					xtype: 'button',
					refId: 'backToDubl',
					iconCls: 'arrow-previous16',
					text: 'Назад',
					hidden: true
				},
				{ xtype: 'tbfill' },
				{
					xtype: 'button',
					refId: 'saveShortCardBtn',
					iconCls: 'save16',
					text: 'Сохранить'
				}
			]
		})
		
		this.CmpCallCardFormPanel = Ext.create('sw.BaseForm', {
			refId: 'CallDetailPanel',
			id: this.id+'_CallDetailPanel',
			flex: 1,
			//autoScroll: true,
			preserveScrollOnRefresh : true,
			blockRefresh: true,
			overflowY: 'scroll',
			layout: 'auto',
			bodyPadding: '10 2',
			isLoading: false,
			//split: true,
			region: 'east',
			editable_fields: [
				//редактируемые поля (помимо полей "Дата и время")
				"CmpCallCard_Dom",
				"CmpCallCard_Korp",
				"CmpCallCard_Kvar",
				"CmpCallCard_Podz",
				"CmpCallCard_Etaj",
				"CmpCallCard_Kodp",
				"CmpCallPlaceType_id",
				"CmpCallCard_Telf",
				"dCityCombo",
				"dStreetsCombo",
				"CmpCallCard_Comm",
				"CmpCallCard_IsExtra",
				"CmpReason_id",
				"LpuBuilding_id",
				"selectNmpCombo",
				"Lpu_ppdid",
				"Lpu_smpid",
				"CmpCallCard_IsPoli",
				"CmpCallCard_IsPassSSMP"
			],
			items: [
				{
					xtype: 'container',
					layout: 'hbox',
					flex: 1,
					margin: '4 0 4 0',
					items: [
						{
							xtype: 'container',
							layout: 'vbox',
							flex: 1,
							margin: '4 0 4 0',
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Дата вызова',
									labelAlign: 'right',
									labelWidth: 90,
									format: 'd.m.Y',
									plugins: [new Ux.InputTextMask('99.99.9999')],
									name: 'CmpCallCard_prmDate',
									readOnly: true,
									disabled: true,
									flex: 1,
									maxWidth: 200
								},
								{
									xtype: 'datefield',
									name: 'CmpCallCard_prmTime',
									fieldLabel: 'Время',
									format: 'H:i:s',
									hideTrigger: true,
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ:CC',
									plugins: [new Ux.InputTextMask('99:99:99')],
									labelAlign: 'right',
									labelWidth: 90,
									readOnly: true,
									disabled: true,
									flex: 1,
									maxWidth: 200
								},
							]
						},
						{
							xtype: 'container',
							layout: 'vbox',
							flex: 1,
							margin: '4 0 4 0',
							items: [
								{
									xtype: 'button',
									text: 'Аудиозапись звонка',
									hidden: true,
									name: 'showAudioCallRecordWindow'
								}
							]
						},
					]
				},

				{
					xtype: 'container',
					layout: 'hbox',
					flex: 1,
					margin: '4 0 4 0',
					items: [
						{
							xtype: 'numberfield',
							hideTrigger: true,
							keyNavEnabled: false,
							mouseWheelEnabled: false,
							fieldLabel: '№ вызова (год):',
							labelAlign: 'right',
							labelWidth: 90,
							name: 'CmpCallCard_Ngod',
							readOnly: true,
							disabled: true,
							flex: 1,
							hidden: true,
							maxWidth: 200
						},
						{
							xtype: 'numberfield',
							hideTrigger: true,
							keyNavEnabled: false,
							mouseWheelEnabled: false,
							fieldLabel: '№ вызова (д)',
							labelAlign: 'right',
							labelWidth: 90,
							name: 'CmpCallCard_Numv',
							readOnly: true,
							disabled: true,
							flex: 1,
							maxWidth: 200
						}
					]
				},
				{
					xtype: 'fieldset',
					layout: {
						align: 'stretch',
						type: 'vbox'
					},
					title: 'Место вызова',
					items: [
						{
							xtype: 'dCityCombo',
							labelWidth: 90,
							readOnly: getRegionNick().inlist(['ufa']),
							disabled: getRegionNick().inlist(['ufa']),
							flex: 1,
							listeners:{
								'focus': function (inp, e) {
									if(!getRegionNick().inlist(['ufa'])){
										inp.store.getProxy().extraParams = {region_id : getGlobalOptions().region.number};
										inp.store.load();
									}
								},
								select: function(inp, e){
									var streetsCombo = me.down('form').getForm().findField('dStreetsCombo');
									streetsCombo.bigStore.getProxy().extraParams = {
										town_id: e[0].get('Town_id'),
										Lpu_id: sw.Promed.MedStaffFactByUser.current.Lpu_id
									};
									streetsCombo.reset();
									streetsCombo.bigStore.load();
								}
							}
						},
						{
							xtype: 'swStreetsSpeedCombo',
							name:'dStreetsCombo',
							readOnly: getRegionNick().inlist(['ufa']),
							disabled: getRegionNick().inlist(['ufa']),
							labelAlign: 'right',
							labelWidth: 90,
							fieldLabel: 'Улица',
							flex: 1,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-boundlist-item" style="font: 14px tahoma,arial,verdana,sans-serif;">&bull;&nbsp;'+
								'{[ this.addressObj(values) ]} '+
								'</div></tpl>',
								{
									addressObj: function(val){
										var city = val.Address_Name+' ';
										if(val.UnformalizedAddressDirectory_id){
											return val.AddressOfTheObject + ', ' + val.StreetAndUnformalizedAddressDirectory_Name;
										}else{
											return val.AddressOfTheObject +', ' + val.StreetAndUnformalizedAddressDirectory_Name + ' <span style="color:gray">' + val.Socr_Nick +'</span>';
										}
									}
								}
							)
						},
						{
							xtype: 'textfield',
							plugins: [new Ux.Translit(true, true)],
							fieldLabel: 'Дом',
							readOnly: getRegionNick().inlist(['ufa']),
							disabled: getRegionNick().inlist(['ufa']),
							labelAlign: 'right',
							name: 'CmpCallCard_Dom',
							enableKeyEvents : true,
							labelWidth: 90,
							flex: 1
						},
						{
							xtype: 'textfield',
							plugins: [new Ux.Translit(true, true)],
							fieldLabel: 'Корп',
							readOnly: getRegionNick().inlist(['ufa']),
							disabled: getRegionNick().inlist(['ufa']),
							enforceMaxLength: true,
							maxLength: 5,
							// hidden: (!getRegionNick().inlist(['ufa', 'krym', 'kz'])),
							labelAlign: 'right',
							name: 'CmpCallCard_Korp',
							enableKeyEvents : true,
							labelWidth: 90,
							flex: 1
						},
						{
							xtype: 'fieldcontainer',
							margin: '4 0',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'textfield',
									//maskRe: /[0-9:]/,
									enforceMaxLength: true,
									maxLength: 5,
									plugins: [new Ux.Translit(true, true)],
									fieldLabel: 'Кв.',
									readOnly: getRegionNick().inlist(['ufa']),
									disabled: getRegionNick().inlist(['ufa']),
									labelAlign: 'right',
									name: 'CmpCallCard_Kvar',
									enableKeyEvents : true,
									labelWidth: 90,
									flex: 1
								},
								{
									xtype: 'textfield',
									maskRe: /[0-9:]/,
									fieldLabel: 'Под.',
									readOnly: getRegionNick().inlist(['ufa']),
									disabled: getRegionNick().inlist(['ufa']),
									labelAlign: 'right',
									name: 'CmpCallCard_Podz',
									enableKeyEvents : true,
									labelWidth: 60,
									flex: 1
								}

							]
						},
						{
							xtype: 'fieldcontainer',
							margin: '4 0',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'textfield',
									maskRe: /[0-9:]/,
									fieldLabel: 'Этаж',
									readOnly: getRegionNick().inlist(['ufa']),
									disabled: getRegionNick().inlist(['ufa']),
									labelAlign: 'right',
									name: 'CmpCallCard_Etaj',
									enableKeyEvents : true,
									labelWidth: 90,
									flex: 1
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Код',
									readOnly: getRegionNick().inlist(['ufa']),
									disabled: getRegionNick().inlist(['ufa']),
									labelAlign: 'right',
									name: 'CmpCallCard_Kodp',
									enableKeyEvents : true,
									labelWidth: 60,
									flex: 1
								}
							]
						},
						{
							xtype: 'swCmpCallPlaceType',
							name:'CmpCallPlaceType_id',
							fieldLabel: 'Тип места',
							labelAlign: 'right',
							value: 1,
							labelWidth: 90,
							readOnly: getRegionNick().inlist(['ufa']),
							disabled: getRegionNick().inlist(['ufa']),
							triggerClear: true,
							hideTrigger:true,
							flex: 1,
							displayTpl: '<tpl for="."> {CmpCallPlaceType_Code}. {CmpCallPlaceType_Name} </tpl>',
							tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
							'<font color="red">{CmpCallPlaceType_Code}</font> {CmpCallPlaceType_Name}'+
							'</div></tpl>'
						},
						{
							xtype: 'swCmpCallerTypeCombo',
							name: 'CmpCallerType_id',
							labelWidth: 90,
							triggerClear: true,
							readOnly: getRegionNick().inlist(['ufa']),
							disabled: getRegionNick().inlist(['ufa']),
							hideTrigger:true,
							autoFilter: false,
							forceSelection: false,
							autoSelect: false,
							labelAlign: 'right',
							flex: 1,
							fieldLabel: 'Кто выз.',
							minChars:2,
							tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
							'{CmpCallerType_Name}'+
							'</div></tpl>'
						},
						{
							xtype: 'textfield',
							fieldLabel: 'Телефон',
							enableKeyEvents : true,
							maskRe: /[0-9:]/,
							readOnly: getRegionNick().inlist(['ufa']),
							disabled: getRegionNick().inlist(['ufa']),
							hidden: getRegionNick().inlist(['ufa']),
							labelAlign: 'right',
							name: 'CmpCallCard_Telf',
							labelWidth: 90,
							flex: 1,
							/*
							 //cls: 'x-form-table-div',
							 //triggerCls: 'x-form-eye-trigger-default',
							 //inputType: 'password',
							 listeners: {

							 },
							 onTriggerClick: function() {
							 var input = this.inputEl.dom;
							 if(!(input.getAttribute('disabled') == 'disabled'
							 && !getRegionNick().inlist(['ufa', 'krym', 'kz'])))
							 {
							 var toPass = (input.getAttribute('type') == 'text'),
							 val = toPass?'password':'text';
							 this.triggerEl.elements[0].dom.classList.toggle('x-form-eye-open-trigger');
							 input.setAttribute('type',val);
							 }
							 }
							 */
						}
					]
				},
				{
					xtype: 'fieldset',
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					flex: 1,
					title: 'Пациент',
					items: [
						{
							xtype: 'textfield',
							plugins: [new Ux.Translit(true, true)],
							flex: 1,
							labelWidth: 90,
							fieldLabel: 'Фамилия',
							labelAlign: 'right',
							name: 'Person_SurName',
							enableKeyEvents : true
						},
						{
							xtype: 'textfield',
							plugins: [new Ux.Translit(true, true)],
							flex: 1,
							labelWidth: 90,
							fieldLabel: 'Имя',
							labelAlign: 'right',
							name: 'Person_FirName',
							enableKeyEvents : true
						},
						{
							xtype: 'textfield',
							plugins: [new Ux.Translit(true, true)],
							flex: 1,
							labelWidth: 90,
							fieldLabel: 'Отчество',
							labelAlign: 'right',
							name: 'Person_SecName'
						},
						{
							xtype: 'textfield',
							fieldLabel: 'Возраст',
							enableKeyEvents : true,
							labelAlign: 'right',
							name: 'Person_Age',
							flex: 1,
							labelWidth: 90
						},
						{
							xtype: 'sexCombo',
							labelAlign: 'right',
							flex: 1,
							labelWidth: 90,
							name: "Sex_id"
						},
						/*{
						 xtype: 'swDSexCombo',
						 labelAlign: 'right',
						 flex: 1,
						 labelWidth: 90
						 },*/
						{
							xtype: 'textfield',
							fieldLabel: '№ полиса',
							labelAlign: 'right',
							flex: 1,
							name: 'Polis_Num',
							disabled: true,
							labelWidth: 90
						},
						/*{
						 xtype: 'container',
						 flex: 1,
						 margin: '4 5 10',
						 layout: {
						 type: 'hbox',
						 align: 'stretch'
						 },
						 items: [
						 {
						 xtype: 'button',
						 refId: 'identPersonBtn',
						 name: 'identPersonBtn',
						 text: 'Идентифицировать',
						 margin: '5 5',
						 height: 27
						 }, {
						 xtype: 'button',
						 name: 'searchPersonBtn',
						 refId: 'searchPersonBtn',
						 text: 'Поиск',
						 iconCls: 'search16',
						 margin: '5 5',
						 height: 27
						 }
						 ]
						 }*/
					]
				},
				{
					xtype: 'fieldset',
					layout: {
						align: 'stretch',
						type: 'vbox'
					},
					flex: 1,
					title: 'Вызов',
					items: [
						{
							xtype: 'swCmpCallTypeCombo',
							name: 'CmpCallType_id',
							flex: 1,
							labelWidth: 90,
							fieldLabel: 'Тип вызова'
						},
						{
							xtype: 'swCmpCallTypeIsExtraCombo',
							fieldLabel: 'Вид вызова',
							enableKeyEvents : true,
							labelWidth: 90,
							flex: 1,
							name: 'CmpCallCard_IsExtra',
							editable: false
						},
						{
							xtype: 'cmpReasonCombo',
							name: 'CmpReason_id',
							flex: 1,
							labelWidth: 90,
						},
						{
							xtype: 'textfield',
							name: 'CmpCallCard_Urgency',
							flex: 1,
							fieldLabel: 'Срочность',
							enableKeyEvents : true,
							labelAlign: 'right',
							labelWidth: 90
						},
						{
							xtype: 'textfield',
							fieldLabel: 'Профиль',
							labelAlign: 'right',
							//name: 'CmpCallCard_Profile',
							name: 'EmergencyTeamSpec_Code',
							enableKeyEvents : true,
							labelWidth: 90,
							flex: 1
						},
						{
							// xtype: 'smpDutyAmbulanceTeamCombo',
							xtype: 'textfield',
							fieldLabel: '№ бригады',
							labelAlign: 'right',
							name: 'EmergencyTeam_Num',
							labelWidth: 90,
							flex: 1,
							// displayTpl: '<tpl for="."> {EmergencyTeam_Num}. {Person_Fin} </tpl>',
							enableKeyEvents : true
						},
						{
							xtype: 'hidden',
							name: 'EmergencyTeam_id'
						},
						{
							xtype: 'swmedpersonalcombo',
							name: 'EmergencyTeam_HeadDocName',
							fieldLabel: 'Старший бригады',
							labelAlign: 'right',
							enableKeyEvents : true,
							labelWidth: 90,
							flex: 1
						},
						{
							xtype: 'swmedpersonalcombo',
							name: 'DPMedPersonal_id',
							fieldLabel: 'Диспетчер вызова',
							labelAlign: 'right',
							enableKeyEvents : true,
							labelWidth: 90,
							flex: 1,
							displayTpl: '<tpl for=".">{Person_Fin} </tpl>',
						},
						{
							xtype: 'checkbox',
							flex: 1,
							name: 'CmpCallCard_IsPoli',
							boxLabel: 'Вызов передан в поликлинику по телефону (рации)',
							margin: '0 0 0 95'
						},
						{
							xtype: 'lpuLocalCombo',
							name: 'Lpu_ppdid',
							labelWidth: 90,
							flex: 1,
							validateOnBlur: false,
							fieldLabel: 'МО передачи (НМП)'
						},
						{
							fieldLabel: 'Служба НМП',
							allowBlank: true,
							labelWidth: 90,
							flex: 1,
							xtype: 'selectNmpCombo',
							hiddenName: 'MedService_id',
							isClose: 1
						},
						{
							xtype: 'checkbox',
							flex: 1,
							margin: '0 0 0 95',
							name: 'CmpCallCard_IsPassSSMP',
							boxLabel: 'Вызов передан в другую ССМП по телефону (рации)'
						},
						{
							xtype: 'lpuAllLocalCombo',
							name: 'Lpu_smpid',
							flex: 1,
							fieldLabel: 'МО передачи (СМП)',
							labelWidth: 90
						},
						(getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) ? smpUnitsNestedCombo : smpRegionUnitsCombo,


						/*{
						 xtype: 'lpuLocalCombo',
						 name: 'Lpu_id',
						 flex: 1,
						 fieldLabel: 'ЛПУ',
						 labelWidth: 90,
						 displayTpl: '<tpl for=".">{MedService_Nick} / {Lpu_Nick}</tpl>',
						 tpl: '<tpl for=".">' +
						 '<div class="x-boundlist-item">' +
						 '{MedService_Nick}' +
						 ' / ' +
						 '{Lpu_Nick}' +
						 '</div></tpl>'
						 },*/
						{
							xtype: 'textareafield',
							flex: 1,
							margin: '4 0',
							plugins: [new Ux.Translit(true)],
							minHeight: 50,
							labelWidth: 90,
							fieldLabel: 'Доп. информация:',
							enableKeyEvents : true,
							labelAlign: 'right',
							name: 'CmpCallCard_Comm'
						}
					]
				},
				{
					xtype: 'fieldset',
					layout: {
						align: 'stretch',
						type: 'vbox'
					},
					flex: 1,
					title: 'Дата и время',
					refId: 'dateTimeFieldsetBlock',
					onTriggerClick: function(fieldcontainer, forceSet) {
						var timefield = fieldcontainer.child('[xtype="timefield"]');
						var datefield = fieldcontainer.child('[xtype="datefield"]');

						if((forceSet == true) || (timefield.getValue() == null && datefield.getValue() == null) ){
							timefield.setValue(Ext.Date.format(new Date(), 'H:i'));
							datefield.setValue(Ext.Date.format(new Date(), 'd.m.Y'));
						}
					},
					items: [
						{
							xtype: 'fieldcontainer',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Передачи выз. бриг.',
									labelAlign: 'right',
									labelWidth: 130,
									format: 'd.m.Y',
									plugins: [new Ux.InputTextMask('99.99.9999')],
									validateOnBlur: false,
									validateOnChange: false,
									name: 'CmpCallCard_DateTper',
									flex: 1,
									allowBlank: true,
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									}
								},
								{
									xtype: 'timefield',
									name: 'CmpCallCard_DateTperTime',
									format: 'H:i',
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
									plugins: [new Ux.InputTextMask('99:99')],
									validateOnBlur: false,
									validateOnChange: false,
									flex: 1,
									maxWidth: 60,
									allowBlank: true,
									alias: 'widget.timeGetCurrentTimeCombo',
									triggerCls: 'x-form-clock-trigger',
									cls: 'stateCombo',
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									},
									onTriggerClick: function(e) {
										e.stopEvent();
										this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
									}
								}
							]
						},
						{
							xtype: 'fieldcontainer',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Выезда бригады',
									labelAlign: 'right',
									labelWidth: 130,
									format: 'd.m.Y',
									plugins: [new Ux.InputTextMask('99.99.9999')],
									validateOnBlur: false,
									validateOnChange: false,
									name: 'CmpCallCard_DateVyez',
									flex: 1,
									allowBlank: true,
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									}
								},
								{
									xtype: 'timefield',
									name: 'CmpCallCard_DateVyezTime',
									format: 'H:i',
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
									plugins: [new Ux.InputTextMask('99:99')],
									validateOnBlur: false,
									validateOnChange: false,
									flex: 1,
									maxWidth: 60,
									allowBlank: true,
									alias: 'widget.timeGetCurrentTimeCombo',
									triggerCls: 'x-form-clock-trigger',
									cls: 'stateCombo',
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									},
									onTriggerClick: function(e) {
										e.stopEvent();
										this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
									}
								}
							]
						},
						{
							xtype: 'fieldcontainer',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Доезда бриг. до выз.',
									labelAlign: 'right',
									labelWidth: 130,
									format: 'd.m.Y',
									plugins: [new Ux.InputTextMask('99.99.9999')],
									validateOnBlur: false,
									validateOnChange: false,
									name: 'CmpCallCard_DatePrzd',
									flex: 1,
									allowBlank: true,
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									}
								},
								{
									xtype: 'timefield',
									name: 'CmpCallCard_DatePrzdTime',
									format: 'H:i',
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
									plugins: [new Ux.InputTextMask('99:99')],
									validateOnBlur: false,
									validateOnChange: false,
									flex: 1,
									maxWidth: 60,
									allowBlank: true,
									alias: 'widget.timeGetCurrentTimeCombo',
									triggerCls: 'x-form-clock-trigger',
									cls: 'stateCombo',
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									},
									onTriggerClick: function(e) {
										e.stopEvent();
										this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
									}
								}
							]
						},
						/*{
							xtype: 'fieldcontainer',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Отъезда бриг. с выз.',
									labelAlign: 'right',
									labelWidth: 130,
									format: 'd.m.Y',
									plugins: [new Ux.InputTextMask('99.99.9999')],
									validateOnBlur: false,
									validateOnChange: false,
									name: 'CmpCallCard_DateTgsp',
									flex: 1,
									allowBlank: true,
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									}
								},
								{
									xtype: 'timefield',
									name: 'CmpCallCard_DateTgspTime',
									format: 'H:i',
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ:CC',
									plugins: [new Ux.InputTextMask('99:99')],
									validateOnBlur: false,
									validateOnChange: false,
									flex: 1,
									maxWidth: 60,
									allowBlank: true,
									alias: 'widget.timeGetCurrentTimeCombo',
									triggerCls: 'x-form-clock-trigger',
									cls: 'stateCombo',
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									},
									onTriggerClick: function(e) {
										e.stopEvent();
										this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
									}
								}
							]
						},*/
						{
							xtype: 'fieldcontainer',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Закрытия вызова',
									labelAlign: 'right',
									labelWidth: 130,
									format: 'd.m.Y',
									plugins: [new Ux.InputTextMask('99.99.9999')],
									validateOnBlur: false,
									validateOnChange: false,
									name: 'CmpCallCard_DateTisp',
									flex: 1,
									allowBlank: true,
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									}
								},
								{
									xtype: 'timefield',
									name: 'CmpCallCard_TispTime',
									format: 'H:i',
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
									plugins: [new Ux.InputTextMask('99:99')],
									validateOnBlur: false,
									validateOnChange: false,
									flex: 1,
									maxWidth: 60,
									allowBlank: true,
									alias: 'widget.timeGetCurrentTimeCombo',
									triggerCls: 'x-form-clock-trigger',
									cls: 'stateCombo',
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									},
									onTriggerClick: function(e) {
										e.stopEvent();
										this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
									}
								}
							]
						},
						{
							xtype: 'lpuAllLocalCombo',
							flex: 1,
							//name: 'CmpCallCard_IsPoli',
							name: 'Lpu_hid',
							fieldLabel: 'МО госпитализации',
							labelWidth: 130,
							displayTpl: '<tpl for=".">{Org_Nick}</tpl>',
							tpl: '<tpl for=".">' +
							'<div class="x-boundlist-item">' +
							'{Org_Nick}' +
							'</div></tpl>'
						},
						{
							xtype: 'hidden',
							name: 'CmpCallCard_IsPoli'
						},
						{
							xtype: 'fieldcontainer',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Госпитализации',
									labelAlign: 'right',
									labelWidth: 130,
									format: 'd.m.Y',
									plugins: [new Ux.InputTextMask('99.99.9999')],
									validateOnBlur: false,
									validateOnChange: false,
									name: 'CmpCallCard_HospitalizedTime',
									flex: 1,
									allowBlank: true,
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									}
								},
								{
									xtype: 'timefield',
									//@todo потом поле будет другое
									name: 'CmpCallCard_HospitalizedTimeTime',
									format: 'H:i',
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
									plugins: [new Ux.InputTextMask('99:99')],
									validateOnBlur: false,
									validateOnChange: false,
									flex: 1,
									maxWidth: 60,
									allowBlank: true,
									alias: 'widget.timeGetCurrentTimeCombo',
									triggerCls: 'x-form-clock-trigger',
									cls: 'stateCombo',
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									},
									onTriggerClick: function(e) {
										e.stopEvent();
										this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
									}
								}
							]
						},
						{
							xtype: 'fieldcontainer',
							flex: 1,
							layout: {
								align: 'stretch',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Отмены',
									labelAlign: 'right',
									labelWidth: 130,
									format: 'd.m.Y',
									plugins: [new Ux.InputTextMask('99.99.9999')],
									validateOnBlur: false,
									validateOnChange: false,
									name: 'CmpCallCard_cancelDate',
									flex: 1,
									allowBlank: true,
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									}
								},
								{
									xtype: 'timefield',
									name: 'CmpCallCard_cancelTime',
									format: 'H:i',
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
									plugins: [new Ux.InputTextMask('99:99')],
									validateOnBlur: false,
									validateOnChange: false,
									flex: 1,
									maxWidth: 60,
									allowBlank: true,
									alias: 'widget.timeGetCurrentTimeCombo',
									triggerCls: 'x-form-clock-trigger',
									cls: 'stateCombo',
									listeners: {
										'focus': function (inp, e) {
											e.stopEvent();
											inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
										}
									},
									onTriggerClick: function(e) {
										e.stopEvent();
										this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
									}
								}
							]
						},
						{
							xtype: 'hidden',
							name: 'CmpCallCard_id'
						},
						{
							xtype: 'hidden',
							name: 'CmpCallCard_rid'
						},
						{
							xtype: 'hidden',
							name: 'pcCmpCallCard_Numv'
						},
						{
							xtype: 'hidden',
							name: 'CmpCallCardDubl_id'
						},
						{
							xtype: 'hidden',
							name: 'CmpCallRecord_id'
						},
						{
							xtype: 'hidden',
							name: 'Person_id'
						},
						{
							xtype: 'hidden',
							name: 'Person_Birthday'
						},
						{
							xtype: 'hidden',
							name: 'Person_AgeInt'
						},
						{
							xtype: 'hidden',
							name: 'CmpCallCardStatusType_id'
						}
					]
				}
			],
		});


		Ext.applyIf(me, {
			items: [
				me.CmpCallCardFormPanel
			],
			dockedItems: [
				bottomToolbar
			]
		});

		me.callParent(arguments);

	}
})